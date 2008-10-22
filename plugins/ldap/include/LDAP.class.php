<?php
//
// CodeX: Breaking Down the Barriers to Source Code Sharing inside Xerox
// Copyright (c) Xerox Corporation, CodeX, 2001-2004. All Rights Reserved
// http://codex.xerox.com
//
// 
//


require_once('common/include/Error.class.php');
require_once('LDAPResult.class.php');

/**
 * LDAP class definition
 * Provides LDAP facilities to CodeX:
 * - directory search
 * - user authentication
 * The ldap object is initialized with global parameters (from local.inc):
 * servers, query templates, etc.
 */
class LDAP extends Error {
    var $serverList; /** Array of LDAP server */
    var $ds;         /** LDAP link identifier */
    var $default_bind_dn;     /** Needed for searching servers that don't support anonymous bind */
    var $default_bind_passwd; /** Needed for searching servers that don't support anonymous bind */
    var $auth_dn_template; /** Needed to compute authentication DN from login name */

    // LDAP object constructor. Use gloabals for initialization.
    function LDAP() {
        global $Language;

        $this->Error(); 
        $this->serverList   = explode(",",$GLOBALS['sys_ldap_server']);
        if(array_key_exists('sys_ldap_bind_dn', $GLOBALS)) {
            $this->default_bind_dn    = $GLOBALS['sys_ldap_bind_dn'];
        }
        if(array_key_exists('sys_ldap_bind_passwd', $GLOBALS)) {
            $this->default_bind_passwd= $GLOBALS['sys_ldap_bind_passwd'];
        }
        $this->auth_dn_template   = $GLOBALS['sys_ldap_auth_dn'];
    }

    function &instance() {
        static $_ldap_instance;
        if (!$_ldap_instance) {
            $_ldap_instance = new LDAP();
        }
        else {
            $_ldap_instance->error_state=false;
            $_ldap_instance->error_message=null;
        }
        return $_ldap_instance;
    }

    //
    // Private methods: should not be called directly
    //

    // Connect to LDAP server.
    // If several servers are listed, try first server first, then second, etc.
    // This funtion should not be called directly: it is always called
    // by a public function: authenticate() or search().
    // @return true if connect was successful, false otherwise.
    function connect() {
        if (!$this->ds) {
            foreach ($this->serverList as $ldap_server) {  	    
                $this->ds = ldap_connect($ldap_server);
                if($this->ds) {
                    // MV:
                    // Since ldap_connect allways return a resource with
                    // OpenLdap 2.2.x, we have to check that this ressource is
                    // valid with a bind, If bind success: that's great, if
                    // not, this is a connexion failure.
                    if(@ldap_bind($this->ds)) {
                        return true;
                    }
                }
            }
            return false;
        }
        else {
            return true;
        }
    }
    

    // Perform LDAP binding.
    // - Some servers allow anonymous bindings for searching. Otherwise, set
    //  sys_ldap_bind_dn and sys_ldap_bind_passwd in local.inc
    // - binding is also used for user authentication. A successful bind
    //   means that the user/password is valid.
    // @return true if bind was successful, false otherwise.
    function bind($binddn=null, $bindpw=null) {
        global $Language;

        // For active directory...
        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);

        if (!$binddn) {
            $binddn=$this->default_bind_dn;
            $bindpw=$this->default_bind_passwd;
        }
        if ($binddn && (!$bindpw)) {
            // Prevent successful binding if a username is given and the server
            // accepts anonymous connections
            $this->setError($Language->getText('ldap_class','err_bind_nopasswd',$binddn));
            return false;
        }

        if ($bind_result = @ldap_bind($this->ds, $binddn, $bindpw)) {
            return true;
        } else {
            $this->setError($Language->getText('ldap_class','err_bind_invpasswd',$binddn));
            return false;
        }
    }


    //
    // Public methods
    //


    // LDAP authentication:
    // Both direct and two-step authentication are possible.
    // If 'auth_dn_template' contains '%ldap_name%', then it is a direct authentication.
    // Else, make a search for the right DN before binding.
    // @return true if the login and password match, false otherwise
    function authenticate($login, $passwd) {
        global $Language;
        if (!$passwd) {
            // avoid a successful bind on LDAP servers accepting anonymous connections
            $this->setError($Language->getText('ldap_class','err_nopasswd'));
            return false;;
        }
        
        if (!$this->connect()) {
            $this->setError($Language->getText('ldap_class','err_cant_connect'));
            return false;
        }

        // Do we have the right DN?
        $pos = strpos($this->auth_dn_template, "%ldap_name%");

        if ($pos === false) {
            // The auth DN does not contain any %ldap_name% -> two-step authentication
            // Do a search to recover the right DN.
            // Now run the ldap search

            if ($GLOBALS['sys_ldap_auth_filter']) {
                $ldap_filter = $GLOBALS['sys_ldap_auth_filter'];
            } else {
                $ldap_filter = "uid=%ldap_name%";
            }
            $ldap_filter = str_replace("%ldap_name%", $login, $ldap_filter);
            $sr=ldap_search($this->ds,$this->auth_dn_template,$ldap_filter);
            if (!$sr) {
                $this->setError($Language->getText('ldap_class','err_auth_search'));
                return false;
            }
            // Ideally we should only have one reply from the LDAP server
            $info = ldap_get_entries($this->ds, $sr);
            if ($info['count'] < 1) {
                $this->setError($Language->getText('ldap_class','err_nouser'));
                return false;
            }
            $auth_dn = $info[0]["dn"];

        } else {

            // One step authentication: compute DN for this user
            $auth_dn = str_replace("%ldap_name%", $login, $this->auth_dn_template);
        }
        // Now bind with DN/password to check authentication
        if (!$this->bind($auth_dn,$passwd)) {
            $this->setError($Language->getText('ldap_class','err_badpasswd'));
            return false;
        }
        return true;
    }


    // Search the LDAP directory with the given DN and filter.
    // @return array of result entries
    function &search($dn,$filter,$args=null) {
        global $Language;
        if (!$this->connect()) {
            $this->setError($Language->getText('ldap_class','err_cant_connect'));
            return false;
        }
        if (!$this->bind()) {
            $this->setError($Language->getText('ldap_class','err_cant_bind'));
            return false;
        }
        // Now run the ldap search
	if(is_array($args)) {
	  $sr=ldap_search($this->ds,$dn,$filter,$args);
	}
	else {
        $sr=ldap_search($this->ds,$dn,$filter);
	}
        if (!$sr) {
            $this->setError($Language->getText('ldap_class','err_search'));
            return false;
        }
        // Ideally we should only have one reply from the LDAP server
        $info = ldap_get_entries($this->ds, $sr);
        if ($info['count'] < 1) {
            $this->setError($Language->getText('ldap_class','err_nores'));
            return false;
        }
        return $info;
    }


    /**
     * aseach return a Iterator on the result of the LDAP search made on the
     * given argument (filter)
     *
     * @param $filter string LDAP filter
     * @return LDAPResultIterator
     */
    function &asearch($filter, $args=null) {
        return new LDAPResultIterator($this->search($GLOBALS['sys_ldap_dn'], $filter, $args));    
    }


    /**
     * Search if given argument correspond to a LDAP login (generally this
     * correspond to ldap 'uid' field).
     *
     * @param $name string login
     * @return LDAPResultIterator
     */    
    function &searchLogin($name) {
        if ($GLOBALS['sys_ldap_auth_filter']) {
            $ldap_filter = $GLOBALS['sys_ldap_auth_filter'];
        } else {
            $ldap_filter = "uid=%ldap_name%";
        }
        $ldap_filter = str_replace("%ldap_name%", $name, $ldap_filter);
    
        return $this->asearch($ldap_filter);
    }

    
    /**
     * Search if given argument correspond to a LDAP Identifier. This is the
     * uniq number that represent a user.
     *
     * @param $name string LDAP Id
     * @return LDAPResultIterator
     */  
    function &searchEdUid($name) {
        if ($GLOBALS['sys_ldap_eduid_filter']) {
            $ldap_filter = $GLOBALS['sys_ldap_eduid_filter'];
        } else {
            $ldap_filter = "eduid=%eduid%";
        }
        $ldap_filter = str_replace("%eduid%", $name, $ldap_filter);

        return $this->asearch($ldap_filter);
    }


    /**
     * Search if a LDAP user match a filter defined in local conf.
     *
     * @param $name string
     * @return LDAPResultIterator
     */  
    function &searchUser($words) {
        if(array_key_exists('sys_ldap_search_user', $GLOBALS)) {
            $ldap_filter = $GLOBALS['sys_ldap_search_user'];
        }
        else {
            $ldap_filter = 'cn=%words%';
        }
        $ldap_filter = str_replace("%words%", $words, $ldap_filter);

        return $this->asearch($ldap_filter);
    }       
}

?>
