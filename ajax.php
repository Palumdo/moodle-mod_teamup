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

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // The course_module ID.
$action = required_param('action', PARAM_TEXT);
$input = required_param('input', PARAM_RAW);
$input = json_decode(stripslashes($input));

$output = array();

try {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'teamup');
    $teamup = $DB->get_record('teamup', array('id' => $cm->instance), '*', MUST_EXIST);
} catch (Exception $e) {
    $output['status'] = 'fail';
    $output['message'] = get_string('idincorrect', 'mod_teamup');
}

require_login($course, true, $cm);

// Alright.

if (!isset($output['status']) || $output['status'] != 'fail') {
    $ctxt = context_module::instance($cm->id);
    switch($action) {
        case "saveQuestionnaire":
        {
            require_capability('mod/teamup:create', $ctxt);
            if ($teamup->open < time()) {
                $output['status'] = 'fail';
                $output['message'] = get_string('cannotupdate', 'mod_teamup');
                break;
            }
            $questionids = array();
            foreach ($input as $ord => $q) {
                $q->ordinal = $ord;
                if (isset($q->id)) {
                    $DB->update_record("teamup_question", $q);
                } else {
                    $q->display = 'field';
                    $q->builder = $teamup->id;
                    $q->id = $DB->insert_record("teamup_question", $q);
                }
                $questionids[] = $q->id;

                // Since we didn't keep references, we need to rebuild the answer base every time.
                $DB->delete_records("teamup_answer", array("question" => $q->id));
                foreach ($q->answers as $aord => $atext) {
                    $a = new stdClass();
                    $a->answer = $atext;
                    $a->ordinal = $aord;
                    $a->question = $q->id;
                    $DB->insert_record("teamup_answer", $a);
                }
            }

            // Find deleted questions.
            foreach ($DB->get_records("teamup_question", array("builder" => $teamup->id)) as $k => $v) {
                if (!in_array($k, $questionids)) {
                    $DB->delete_records("teamup_question", array("id" => $k));
                    $DB->delete_records("teamup_answer", array("question" => $k));
                }
            }
            $output['status'] = 'success';
            $questionnaire = $DB->get_records("teamup_question", ["builder" => $teamup->id], "", "id,question,ordinal");
            $output['questionnaire'] = $questionnaire;
        }
        break;
    }
}

echo json_encode($output);
