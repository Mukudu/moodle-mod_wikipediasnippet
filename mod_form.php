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
 * The main wikipediasnippet configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage wikipediasnippet
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_wikipediasnippet_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('wikis_Name', 'wikipediasnippet'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'wikipediasnippetname', 'wikipediasnippet');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

        //-------------------------------------------------------------------------------
        // Adding the rest of wikipediasnippet settings
        $mform->addElement('header', 'specific', get_string('ws_formsection', 'wikipediasnippet'));
        
// 		$mform->addElement('hidden', 'ws_id', $this->_customdata['ws_id']);	    // must have a courseid so we can return to this page
// 		$mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);	    // must have a courseid so we can return to this page
        
		//wikipedia url
        $mform->addElement('text', 'wikiurl', get_string('wikis_url', 'wikipediasnippet'), array('size'=>'80'));			//wikipedia url
	    $mform->setDefault('wikiurl', $this->_customdata['wikiurl']);
	    $mform->setType('wikiurl', PARAM_URL);
        $mform->addRule('wikiurl', null, 'required', null, 'client');
        $mform->addElement('static', 'label1', '', get_string('wikis_url_help', 'wikipediasnippet'));
        
        //include images???
        $mform->addElement('checkbox', 'noimages', get_string('wikis_excludeImages', 'wikipediasnippet'));			//wikipedia url
	    $mform->setDefault('noimages', $this->_customdata['noimages']);
	    $mform->setType('noimages', PARAM_INT);
        $mform->addElement('static', 'label2', '', get_string('wikis_excludeImages_help', 'wikipediasnippet'));        
        
        //include links???
        $mform->addElement('checkbox', 'nolinks', get_string('wikis_excludeLinks', 'wikipediasnippet'));			//wikipedia url
	    $mform->setDefault('nolinks', $this->_customdata['nolinks']);
	    $mform->setType('nolinks', PARAM_INT);
        $mform->addElement('static', 'label3', '', get_string('wikis_excludeLinks_help', 'wikipediasnippet'));        
        
        
        //a div for a preview???
        
        
        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
