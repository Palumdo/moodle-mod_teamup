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
 * Privacy Subsystem implementation for mod_teamup fork of teambuilder (mod_teambuilder)
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by Dominique Palumbo (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same tables structure)
    no other changes !
 */

namespace mod_teamup\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the teamup activity module.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin stores personal data.
    \core_privacy\local\metadata\provider,

    // This plugin is a core_user_data_provider.
    \core_privacy\local\request\plugin\provider {

    // This trait must be included.
    use \core_privacy\local\legacy_polyfill;

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function _get_metadata(collection $items) {
        $items->add_database_table(
            'teamup_response',
            [
                'answerid' => 'privacy:metadata:teamup_response:answerid',
                'userid' => 'privacy:metadata:teamup_response:userid',
            ],
            'privacy:metadata:teamup_response'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function _get_contexts_for_userid($userid) {
        // Fetch all teamup responses.
        $sql = "SELECT c.id
                FROM {context} c
                JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                JOIN {teamup} t ON t.id = cm.instance
                JOIN {teamup_question} q ON q.builder = t.id
                JOIN {teamup_answer} a ON a.question = q.id
                JOIN {teamup_response} r ON r.answerid = a.id
                WHERE r.userid = :userid";

        $params = [
            'modname'       => 'teamup',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid,
                       q.question,
                       a.answer
                FROM {context} c
                JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                JOIN {modules} m ON m.id = cm.module
                JOIN {teamup} t ON t.id = cm.instance
                JOIN {teamup_question} q ON q.builder = t.id
                JOIN {teamup_answer} a ON a.question = q.id
                JOIN {teamup_response} r ON r.answerid = a.id
                WHERE c.id {$contextsql}
                  AND r.userid = :userid
                ORDER BY cm.id";

        $params = ['userid' => $user->id, 'contextlevel' => CONTEXT_MODULE] + $contextparams;

        $lastcmid = null;

        $responses = $DB->get_recordset_sql($sql, $params);
        foreach ($responses as $response) {
            if ($lastcmid != $response->cmid) {
                if (!empty($responsedata)) {
                    $context = \context_module::instance($lastcmid);
                    self::export_teamup_data_for_user($responsedata, $context, $user);
                }
                $responsedata = [];
            }
            $responsedata['responses'][] = [
                'questiontext' => $response->question,
                'answertext' => $response->answer,
            ];
            $lastcmid = $response->cmid;
        }
        $responses->close();

        // The data for the last activity won't have been written yet, so make sure to write it now!
        if (!empty($responsedata)) {
            $context = \context_module::instance($lastcmid);
            self::export_teamup_data_for_user($responsedata, $context, $user);
        }
    }

    /**
     * Export the supplied personal data for a single teamup activity, along with any generic data or area files.
     *
     * @param array $responsedata the personal data to export for the teamup.
     * @param \context_module $context the context of the teamup.
     * @param \stdClass $user the user record
     */
    protected static function export_teamup_data_for_user(array $responsedata, \context_module $context, \stdClass $user) {
        // Fetch the generic module data for the teamup.
        $contextdata = helper::get_context_data($context, $user);

        // Merge with teamup data and write it.
        $contextdata = (object)array_merge((array)$contextdata, $responsedata);
        writer::with_context($context)->export_data([], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (empty($context)) {
            return;
        }

        if (!$context instanceof \context_module) {
            return;
        }

        $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
        $DB->delete_records_select('teamup_response',
            "id IN (
                SELECT r.id
                FROM {teamup_response} r
                JOIN {teamup_answer} a ON r.answerid = a.id
                JOIN {teamup_question} q ON a.question = q.id
                WHERE q.builder = :instanceid
            )", ['instanceid' => $instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records_select('teamup_response',
                "id IN (
                    SELECT r.id
                    FROM {teamup_response} r
                    JOIN {teamup_answer} a ON r.answerid = a.id
                    JOIN {teamup_question} q ON a.question = q.id
                    WHERE q.builder = :instanceid AND r.userid = :userid
                )", ['instanceid' => $instanceid, 'userid' => $userid]);
        }
    }
}
