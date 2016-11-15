<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\User\Admin;

use Event;
use EventManager;
use ForgeConfig;
use PFUser;

class UserDetailsPresenter
{
    const ADDITIONAL_DETAILS = 'additional_details';

    public $name;
    public $login;
    public $id;
    public $email;
    public $has_avatar;
    public $purpose;
    public $display_purpose;
    public $access;
    public $change_password;
    public $access_title;
    public $account_details;
    public $current_projects;
    public $change_passwd;
    public $projects;
    public $has_projects;
    public $is_admin;
    public $no_project;
    public $shell;
    public $shells;
    public $unix_status_label;
    public $unix_status;
    public $status_label;
    public $email_label;
    public $status;
    public $name_label;
    public $login_label;
    public $password_label;
    public $expiry_date_label;
    public $more_title;
    public $has_additional_details;
    public $additional_details;
    public $expiry;

    public function __construct(
        PFUser $user,
        array $projects,
        UserDetailsAccessPresenter $access,
        UserChangePasswordPresenter $change_password,
        array $additional_details,
        array $more,
        array $shells,
        array $status,
        array $unix_status
    ) {
        $this->id     = $user->getId();
        $this->name   = $user->getRealName();
        $this->login  = $user->getUnixName();
        $this->email  = $user->getEmail();
        $this->expiry = format_date($GLOBALS['Language']->getText('system', 'datefmt'), $user->getExpiryDate());

        $this->has_avatar = $user->hasAvatar();

        $this->access             = $access;
        $this->change_password    = $change_password;
        $this->additional_details = $additional_details;
        $this->shells             = $shells;
        $this->unix_status        = $unix_status;
        $this->status             = $status;
        $this->more               = $more;

        $this->projects     = $projects;
        $this->has_projects = count($projects) > 0;

        $this->display_purpose = ForgeConfig::get('sys_user_approval') == 1;
        $this->purpose         = $user->getRegisterPurpose();
        if (! $this->purpose) {
            $this->purpose = false;
        }

        $this->account_details    = $GLOBALS['Language']->getText('admin_usergroup', 'account_details');
        $this->access_title       = $GLOBALS['Language']->getText('admin_usergroup', 'access');
        $this->current_projects   = $GLOBALS['Language']->getText('admin_usergroup', 'current_projects');
        $this->change_passwd      = $GLOBALS['Language']->getText('admin_usergroup', 'change_passwd');
        $this->administrator      = $GLOBALS['Language']->getText('admin_usergroup', 'is_admin');
        $this->no_project         = $GLOBALS['Language']->getText('admin_usergroup', 'no_project');
        $this->shell              = $GLOBALS['Language']->getText('admin_usergroup', 'shell');
        $this->unix_status_label  = $GLOBALS['Language']->getText('admin_usergroup', 'unix_status');
        $this->status_label       = $GLOBALS['Language']->getText('admin_usergroup', 'status');
        $this->email_label        = $GLOBALS['Language']->getText('admin_usergroup', 'email');
        $this->name_label         = $GLOBALS['Language']->getText('account_options', 'realname');
        $this->login_label        = $GLOBALS['Language']->getText('account_options', 'tuleap_login');
        $this->password_label     = $GLOBALS['Language']->getText('account_login', 'password');
        $this->expiry_date_label  = $GLOBALS['Language']->getText('admin_usergroup', 'expiry_date');
        $this->more_title         = $GLOBALS['Language']->getText('admin_usergroup', 'more_info');
        $this->update_information = $GLOBALS['Language']->getText('admin_usergroup', 'update_information');
        $this->purpose_label      = $GLOBALS['Language']->getText('admin_usergroup', 'purpose_label');
        $this->empty_purpose      = $GLOBALS['Language']->getText('admin_usergroup', 'empty_purpose');

        $this->has_additional_details = count($this->additional_details) > 0;
    }
}