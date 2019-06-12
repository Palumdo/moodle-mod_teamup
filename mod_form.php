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
 * teamup course module editing form.
 *
 * @package    mod_teamup fork of mod_teambuilder
 * @copyright  UNSW
 * @author     UNSW
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by Dominique Palumbo (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
    modification done when the activities is created
    by default we change the start date  and the end date
    $mform->addElement('date_time_selector', 'open', 'Open Date'
      ,array('startyear' => 2018,'stopyear'  => 2050,'timezone'=> 99,'step'=> 5));
    ...
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/lib/grouplib.php');

class mod_teamup_mod_form extends moodleform_mod {

    public function definition() {

        global $COURSE;
        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('name', 'teamup'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the required "intro" field to hold the description of the instance.
        $this->standard_intro_elements(get_string('intro', 'teamup'));
        $mform->addHelpButton('introeditor', 'intro', 'teamup');

        // Adding the rest of teamup settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic.

        $groups = groups_get_all_groups($COURSE->id);
        $options[0] = get_string('allstudents', 'mod_teamup');
        foreach ($groups as $group) {
            $options[$group->id] = $group->name;
        }
        $mform->addElement('select', 'groupid', get_string('group'), $options);

        // Added by UCLouvain.
        $mform->addElement('date_time_selector', 'open', get_string('opendate', 'mod_teamup'), array('startyear' => 2018,
                            'stopyear'  => 2050, 'timezone' => 99, 'step'=> 5));
        $defaulttime = strtotime('12:00:00');
        $defaulttime = strtotime('+2 days', $defaulttime);
        $mform->setDefault('open',  $defaulttime);
        $mform->addElement('static', 'openInfo', '', get_string('afterdate', 'mod_teamup'));
        $mform->addElement('date_time_selector', 'close', get_string('closedate', 'mod_teamup'), array('startyear' => 2018,
                            'stopyear' => 2050, 'timezone' => 99, 'step'=> 5));
        $defaulttime = strtotime('12:00:00');
        $defaulttime = strtotime('+9 days', $defaulttime);
        $mform->setDefault('close', $defaulttime);
        $mform->addElement('checkbox', 'allowupdate', get_string('updateanswer', 'mod_teamup'));
        // END Added by UCLouvain.

        // Add standard elements, common to all modules.
        $features = new stdClass;
        $features->groups           = false;
        $features->groupings        = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;
        if ($id = $mform->getElementValue('update')) {
            $dta = $mform->getElementValue('open');
            $dt = mktime($dta['hour'][0], $dta['minute'][0], 0, $dta['month'][0], $dta['day'][0], $dta['year'][0]);
            if ($dt < time()) {
                $el = $mform->createElement('static', 'openlabel', get_string('opendate', 'mod_teamup'), date("D d/m/Y H:i", $dt));
                $mform->insertElementBefore($el, 'open');
                $mform->removeElement('open');
                $mform->addElement('hidden', 'opendt', $dt);
            }
        }
    }
}
