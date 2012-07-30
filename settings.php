<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Global settings/config for mod_wikipediasnippet module
 *
 * @package    mod_wikipediasnippet
 * @copyright  2012 Nottingham University
 * @author     Benjamin Ellis - benjamin.c.ellis@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    //proxy
    $settings->add(new admin_setting_configtext('wikipediasnippet/proxy', get_string('ws_proxyprompt', 'wikipediasnippet'),
        get_string('ws_proxyhelp', 'wikipediasnippet'), '', PARAM_URL));

    //caching time in hours
    $settings->add(new admin_setting_configtext('wikipediasnippet/cachetime',
        get_string('ws_cachetime', 'wikipediasnippet'), get_string('ws_cachetime_help', 'wikipediasnippet'), 24, PARAM_INT));

    //debug
    $settings->add(new admin_setting_configcheckbox('wikipediasnippet/debug_on',
        get_string('ws_debugon', 'wikipediasnippet'), get_string('ws_debugon_help', 'wikipediasnippet'), 0, PARAM_INT));

}

/* ?> */
