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
 * Prints a particular instance of wikipediasnippet
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage wikipediasnippet
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace wikipediasnippet with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
//require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/lib/wikipediasnippet/wikipediasnippet.inc.php');
require_once(dirname(__FILE__).'/lib/PhpCache.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // wikipediasnippet instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('wikipediasnippet', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $wikipediasnippet  = $DB->get_record('wikipediasnippet', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $wikipediasnippet  = $DB->get_record('wikipediasnippet', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $wikipediasnippet->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('wikipediasnippet', $wikipediasnippet->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'wikipediasnippet', 'view', "view.php?id={$cm->id}", $wikipediasnippet->name, $cm->id);

/// header info
$PAGE->set_url('/mod/wikipediasnippet/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($wikipediasnippet->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

// if do not want the intro on the page at all
if ($wikipediasnippet->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('wikipediasnippet', $wikipediasnippet, $cm->id), 'generalbox mod_introbox', 'wikipediasnippetintro');
}

$type = 'html';                         //other option would be raw

//get configuation
$mycfg = get_config('wikipediasnippet');

$wikisnippetObj = new WikipediaSnippet();

if (isset($mycfg->debug_on)) {
    $wikisnippetObj->setdebugging(true);
}

if (isset($mycfg->proxy)) {
    $wikisnippetObj->setProxy($mycfg->proxy);
}

$ctime = 0;
if (isset($mycfg->cachetime)) {
    if ($mycfg->cachetime) {
        $ctime = (int) $mycfg->cachetime;
        $ctime = $ctime * (60*60);
    }
}else{
    $ctime = (24 * (60 *60));
}

//empty content to start with
$content = '';

echo $OUTPUT->heading($wikipediasnippet->name);
if ($ctime) {
    // make up a caching url - this means rapid return of formatted html but alos
    // means the potential of 5 cached objects for each url#fragment
    list($url,$fragment) = explode('#', $wikipediasnippet->wikiurl);

    if (($wikipediasnippet->nolinks) || ($wikipediasnippet->noimages)) {
        $cacheparams = array();
        if ($wikipediasnippet->nolinks) {
            $cacheparams[] = 'nolinks=1';
        }
        if ($wikipediasnippet->noimages) {
            $cacheparams[] = 'noimages=1';
        }
        $cacheurl = $url. '?' . implode('&',$cacheparams) . '#' .$fragment;
    }else{
        $cacheurl = $url. '#' .$fragment;
    }

    $content=wikipediasnippet_cache_get($cacheurl,$ctime, $type);
}

if (!$content) {      //if it is not in the cache
    $content = $wikisnippetObj->getWikiContent($wikipediasnippet->wikiurl,$wikipediasnippet->nolinks,$wikipediasnippet->noimages);
    // new content - do some caching here
    if (!$wikisnippetObj->error) {
        if ($ctime) {           //if we want caching
            wikipediasnippet_cache_page($cacheurl,$ctime,$type,$content);
        }
    }else{
        print_error($wikisnippetObj->error);        //exception raised
    }
}

// the content
echo $OUTPUT->container($content,'wikicontent','wcontent');         //could be used with style sheet  //<div id="wcontent" class="wikicontent">

// Finish the page
echo $OUTPUT->footer();


/*
    Subroutines here
*/

// function to retrieve wikipedia responses
// $type can be html,toc, or raw
function wikipediasnippet_cache_get($url,$cachetime,$type) {
    $result = '';

    $cache = new PhpCache( $url, $cachetime, $type );
    if ( $cache->check() ) {
        $result = $cache->get();
        $result = $result['data'];
    }

    return $result;
}

//
// function will cache an object on disk so that http subsequent calls do not
// need to go back to wikipedia - only call this for new/replacement objects
//
function wikipediasnippet_cache_page($url,$cachetime,$type,$content) {
    $noerror = true;

    $cache = new PhpCache( $url, $cachetime, $type );
    $cache->set(
        array(
            'url'=>$url,
            'data'=>$content
        )
    );

    return $noerror;
}

/* ?> */
