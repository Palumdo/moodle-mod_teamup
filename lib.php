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
 * Modified by Dominique Palumbo (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
    add of function teamup_get_questions($id, $userid = null)
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
 * @author Dominique Palumbo (Added by UCLouvain)
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
 * Get groups for a particular course and particular student
 *
 * @author Dominique Palumbo (Added by UCLouvain)
 * @param int $usrid (user id), int $courseid (course id)
 * @return a string with all group name concataned
 */
function teamup_get_groups($usrid, $courseid) {
    global $DB;

    $sql = "SELECT distinct name
             FROM  mdl_user t1
             LEFT JOIN (SELECT ta.* FROM mdl_groups_members ta, mdl_groups tb WHERE tb.id = ta.groupid AND tb.courseid = :param1) t2
                   ON t2.userid = t1.id
             LEFT JOIN (SELECT * FROM mdl_groups WHERE courseid = :param2)  t3 ON t3.id = t2.groupid
            WHERE t1.id = :param3
            ORDER BY name";

    $params = array('param1' => $courseid, 'param2' => $courseid, 'param3' => $usrid);
    $groups = $DB->get_records_sql($sql, $params);

    $aret = array_keys($groups);

    $ret = '';
    for ($i = 0; $i < count($aret); $i++) {
        $ret = $ret.','.$aret[$i];
    }

    return ltrim($ret, ',');
}

/**
 * Get answer for a particular teamup and a particular student
 *
 * @author Dominique Palumbo (Added by UCLouvain)
 * @param int $usrid (user id), int $id (builder id)
 * @return a string with all group name concataned
 */
function teamup_get_user_answers($id, $usrid) {
    global $DB;

    $sql = "SELECT t2.answer
              FROM {teamup}_response t1
                  ,{teamup}_answer   t2
             WHERE t1.userid = :userid
               AND t1.answerid IN (SELECT id FROM {teamup_answer}
                                    WHERE question IN (SELECT id FROM {teamup_question}
                                                        WHERE builder = :builder
                                                      )
                                  )
               AND t1.answerid = t2.id
           ";

    $params = array('userid' => $usrid, 'builder' => $id);
    $rslt = array_keys($DB->get_records_sql($sql, $params));

    $ret = '';
    for ($i = 0; $i < count($rslt); $i++) {
        $ret = $ret.','.$rslt[$i];
    }

    return ltrim($ret, ',');
}
