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
 * Upgrade steps for teamup.
 *
 * @package    mod_teamup fork of mod_teambuilder
 * @copyright  UNSW
 * @author     UNSW
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by  Palumbo Dominique (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
    if ($oldversion < 2011051702) {
     ...
     }
     was removed because already include in the instal.xml
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_teamup_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019091700) {
        // Rename field 'open' on table 'teamup' as it is a reserved word in MySQL.
        $table = new xmldb_table('teamup');
        $field = new xmldb_field('open');
        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, 'introformat');
            // Extend the execution time limit of the script to 2 hours.
            upgrade_set_timeout(7200);
            // Rename it to 'opened'.
            $dbman->rename_field($table, $field, 'opened');
        }

        // Rename field 'close' on table 'teamup' as it is a reserved word in MySQL.
        $table = new xmldb_table('teamup');
        $field = new xmldb_field('close');
        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, null, null, null, 'opened');
            // Extend the execution time limit of the script to 5 minutes.
            upgrade_set_timeout(300);
            // Rename it to 'closed'.
            $dbman->rename_field($table, $field, 'closed');
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2019091700, 'teamup');
    }
    

    return true;
}