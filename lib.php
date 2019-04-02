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
 * teamup library.
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @copyright  UNSW
 * @author     UNSW
 * @author     Morgan Harris
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @modified by Dominique Palumbo (UCLouvain)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $teamup An object from the form in mod_form.php
 * @return int The id of the newly inserted teamup record
 */
function teamup_add_instance($teamup) {
    global $DB;
    return $DB->insert_record('teamup', $teamup);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $teamup An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function teamup_update_instance($teamup) {
    global $DB;
    $teamup->timemodified = time();
    $teamup->id = $teamup->instance;

    if (isset($teamup->opendt)) {
        $teamup->open = $teamup->opendt;
    }
    if (!isset($teamup->allowupdate)) {
        $teamup->allowupdate = 0;
    }

    return $DB->update_record('teamup', $teamup);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function teamup_delete_instance($id) {
    global $DB;

    if (!$teamup = $DB->get_record('teamup', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (!$DB->delete_records('teamup', array('id' => $teamup->id))) {
        $result = false;
    }

    return $result;
}

function teamup_user_outline($course, $user, $mod, $teamup) {
    return false;
}

function teamup_user_complete($course, $user, $mod, $teamup) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in teamup activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function teamup_print_recent_activity($course, $isteacher, $timestart) {
    return false;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of teamup. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $teamupid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function teamup_get_participants($teamupid) {
    return false;
}

/**
 * This function returns if a scale is being used by one teamup
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $teamupid ID of an instance of this module
 * @return mixed
 */
function teamup_scale_used($teamupid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of teamup.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any teamup
 */
function teamup_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Get questions for a particular team builder questionnaire
 *
 * @author Dominique Palumbo 
 * @param int $id (builder), int $userid (if specified return the data for this user only if not for all)
 * @return recordsets of questions
 */
function teamup_get_questions($id, $userid = null) {
    global $DB;
    if ($questions = $DB->get_records("teamup_question", array("builder" => $id), "ordinal ASC")) {
        foreach ($questions as &$q) {
            $q->answers = $DB->get_records("teamup_answer", array("question" => $q->id), "ordinal ASC");
            if ($userid) {
                foreach ($q->answers as &$a) {
                    if ($DB->get_record("teamup_response", array("userid" => $userid, "answerid" => $a->id))) {
                        $a->selected = true;
                    } else {
                        $a->selected = false;
                    }
                }
            }
        }
    }

    return $questions;
}


/**
 * Get responses for a particular team builder questionnaire.
 *
 * @author Morgan Harris
 * @param int $id Team Builder id
 * @return array List of of student ids => array of answers they selected
 */
function teamup_get_responses($id, $student = null) {
    global $DB;
    $teamup = $DB->get_record("teamup", array("id" => $id));
    if ($student == null) {
        if ($teamup->groupid) {
            $students = groups_get_members($teamup->groupid, "u.id");
        } else {
            $ctxt = context_course::instance($teamup->course);
            $students = get_users_by_capability($ctxt, 'mod/teamup:respond', 'u.id');
        }
        $responses = array();
        foreach ($students as $s) {
            $responses[$s->id] = teamup_get_responses($id, $s->id);
        }
        return $responses;
    }
    $sql = "SELECT answerid
            FROM {teamup}_response
            WHERE userid = :userid AND answerid IN (
                SELECT id FROM {teamup_answer}
                WHERE question IN (
                    SELECT id FROM {teamup_question}
                    WHERE builder = :builder
                )
            )";

    $params = array('userid' => $student, 'builder' => $id);
    $rslt = $DB->get_records_sql($sql, $params);
    $ret = false;
    if (!empty($rslt)) {
        $ret = array_keys($rslt);
    }
    return $ret;
}

function teamup_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}


/**
 * add an icon after the activities to allow teacher to down load an export of the students answers
 *
 * @author Dominique Palumbo 
 * @param cm_info $cm context info
 * @return nothing
 */
function teamup_cm_info_view(cm_info $cm) {
   global $DB,$USER;
   
  $role     = $DB->get_record('role', array('shortname' => 'editingteacher'));
  $context  = context_module::instance($cm->id);
  $isTeatcher = false;
  if (has_capability('mod/teamup:create', $context)) {
    $isTeatcher = true;
  }

  if(!$isTeatcher) return false;
   
  if (!$teamup = $DB->get_record('teamup', array('id'=>$cm->instance))) {
    return false;
  }
  $cm->set_after_link(' <a alt="Export Excel" title="Export Excel" href="/mod/teamup/export.php?id='.$cm->id.'&instance='.$cm->instance.'&course='.$cm->course.'"><img class="icon navicon" alt="Export" src="/theme/image.php/uclouvain/core/1539865978/i/report" tabindex="-1"></a>');
}      
