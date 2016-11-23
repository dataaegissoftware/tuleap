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

namespace Tuleap\Statistics;

class ServiceUsagePresenter
{
    public $title;
    public $frequencies_tab_label;
    public $disk_usage_tab_label;
    public $project_quota_tab_label;
    public $service_usage_tab_label;
    public $start_date_label;
    public $end_date_label;
    public $csv_export_button;
    public $start_date;
    public $end_date;

    public function __construct($title, $start_date, $end_date)
    {
        $this->title = $title;

        $this->frequencies_tab_label   = $GLOBALS['Language']->getText('plugin_statistics', 'frequencies_title');
        $this->disk_usage_tab_label    = $GLOBALS['Language']->getText('plugin_statistics_admin_page', 'show_statistics');
        $this->project_quota_tab_label = $GLOBALS['Language']->getText('plugin_statistics', 'quota_title');
        $this->service_usage_tab_label = $GLOBALS['Language']->getText('plugin_statistics', 'services_usage');
        $this->start_date_label        = $GLOBALS['Language']->getText('plugin_statistics', 'start_date');
        $this->end_date_label          = $GLOBALS['Language']->getText('plugin_statistics', 'end_date');
        $this->csv_export_button       = $GLOBALS['Language']->getText('plugin_statistics', 'csv_export_button');

        $this->start_date = $start_date;
        $this->end_date   = $end_date;
    }
}
