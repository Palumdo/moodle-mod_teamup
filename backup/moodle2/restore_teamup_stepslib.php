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
 * @package    mod_teamup fork of mod_teambuilder
 * @copyright  UNSW
 * @author     Adam Olley
 * @package fork   mod_teamup
 * @author of modification  Palumbo Dominique (UCLouvain)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_teamup_activity_task
 */

/**
 * Structure step to restore one teamup activity
 */
class restore_teamup_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $teamup = new restore_path_element('teamup', '/activity/teamup');
        $paths[] = $teamup;

        $question = new restore_path_element('teamup_question', '/activity/teamup/questions/question');
        $paths[] = $question;

        $answer = new restore_path_element('teamup_answer', '/activity/teamup/questions/question/answers/answer');
        $paths[] = $answer;

        if ($userinfo) {
            $response = new restore_path_element('teamup_response',
                '/activity/teamup/questions/question/answers/answer/responses/response');
            $paths[] = $response;
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_teamup($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course   = $this->get_courseid();
        $data->groupid  = $this->get_mappingid('group', $data->groupid);
        $defaulttime    = strtotime('12:00:00');
        $opentime       = strtotime('+2 days', $defaulttime);
        $closetime      = strtotime('+9 days', $defaulttime);
        $data->open     = $opentime;
        $data->close    = $closetime;

        // Insert the teamup record.
        $newid = $DB->insert_record('teamup', $data);

        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newid);
    }

    protected function process_teamup_response($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->answerid = $this->get_new_parentid('teamup_answer');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $DB->insert_record('teamup_response', $data);
    }

    protected function process_teamup_question($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->builder = $this->get_new_parentid('teamup');
        $newquestionid = $DB->insert_record('teamup_question', $data);
        $this->set_mapping('teamup_question', $oldid, $newquestionid);
    }

    protected function process_teamup_answer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->question = $this->get_new_parentid('teamup_question');
        $newanswerid = $DB->insert_record('teamup_answer', $data);
        $this->set_mapping('teamup_answer', $oldid, $newanswerid, true);
    }

    protected function after_execute() {
        global $DB;

        $this->add_related_files('mod_teamup', 'intro', null);
    }
}
