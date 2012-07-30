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
 * English strings for wikipediasnippet
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage wikipediasnippet
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Wikipedia Snippet';
$string['pluginadministration'] = $string['pluginname'] . ' Administration';
$string['modulename'] = 'Wikipedia Snippet';
$string['modulenameplural'] = 'Wikipedia Snippets';
$string['modulename_help'] = 'The wikipediasnippet module allows course managers to include fragments of Wikipedia pages';
$string['wikipediasnippet'] = 'wikipediasnippet';
$string['nowikipediasnippets'] = 'No Wikipedia snippets found';

$string['ws_proxyprompt'] = 'Enter the proxy address';
$string['ws_proxyhelp'] = 'In format ipaddress:port ot host:port e.g. proxy.mydomain.com:8080';
$string['ws_debugon'] = 'Turn debug reporting on';
$string['ws_debugon_help'] = 'Not recommended on live servers - writes debugging information to the server\'s logs';
$string['ws_cachetime'] = 'Enter time in hours that a snippet must be cached for';
$string['ws_cachetime_help'] = 'Time to live for caching items - setting 0 will turn off caching - not recommended';

//form stuff
$string['wikis_Name'] = 'Name';
$string['wikis_Desc'] = 'Description';

$string['ws_formsection'] = 'Wikipedia Snippet Settings';
$string['wikis_url'] = 'Wikipedia URL';
$string['wikis_url_help'] = 'including fragment #anchor <br/> enter #toc for Table of Contents, #preamble for the introductory text and #infobox for the information table on the wikipedia page';
$string['wikis_excludeImages'] = 'No Images?';
$string['wikis_excludeImages_help'] = 'Select so that images on the wikipedia page are stripped out';
$string['wikis_excludeLinks'] = 'No Links?';
$string['wikis_excludeLinks_help'] = 'Select so that any internal links on the wikipedia page are stripped out - leaving the text';
$string['wikis_preview_title'] = 'Snippet Preview';
$string['wikis_submit'] = 'Grab Snippet';
$string['wikis_error'] = 'Errors';

