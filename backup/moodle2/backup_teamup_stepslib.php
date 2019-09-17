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
 * @author     Adam Olley (UNSW)
 * @author of modification  Palumbo Dominique (UCLouvain)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete teamup structure for backup, with file and id annotations
 */
class backup_teamup_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $teamup = new backup_nested_element('teamup', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'opened', 'closed', 'groupid', 'allowupdate',
        ));

        $questions = new backup_nested_element('questions');
        $question = new backup_nested_element('question', array('id'), array(
            'builder', 'question', 'type', 'display', 'ordinal',
        ));

        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', array('id'), array(
            'question', 'answer', 'ordinal',
        ));

        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', array('id'), array(
            'userid', 'answerid',
        ));

        // Build the tree.

        $teamup->add_child($questions);
        $questions->add_child($question);
        $question->add_child($answers);
        $answers->add_child($answer);
        $answer->add_child($responses);
        $responses->add_child($response);

        // Define sources.
        $teamup->set_source_table('teamup', array('id' => backup::VAR_ACTIVITYID));
        $question->set_source_table('teamup_question', array('builder' => backup::VAR_PARENTID));
        $answer->set_source_table('teamup_answer', array('question' => backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $response->set_source_table('teamup_response', array('answerid' => backup::VAR_PARENTID));
        }

        $teamup->annotate_ids('group', 'groupid');
        $response->annotate_ids('user', 'userid');

        // Define file annotations.
        $teamup->annotate_files('mod_teamup', 'intro', null);

        // Return the root element (teamup), wrapped into standard activity structure.
        return $this->prepare_activity_structure($teamup);
    }
}
