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
 * This page lists all the instances of teamup in a particular course
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @copyright  UNSW
 * @author     UNSW
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by Dominique Palumbo (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
    no other changes !
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_teamup\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strteamups = get_string('modulenameplural', 'teamup');

$PAGE->requires->css('/mod/teamup/styles.css');
$PAGE->set_url('/mod/teamup/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add($strteamups);
$PAGE->set_title($strteamups);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

if (! $teamups = get_all_instances_in_course('teamup', $course)) {
    echo $OUTPUT->heading(get_string('thereareno', 'moodle', $strteamups), 2);
    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', ['id' => $course->id]));
    echo $OUTPUT->footer();
    die;
}

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

$table = new html_table();
if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($teamups as $teamup) {
    if (!$teamup->visible) {
        // Show dimmed if the mod is hidden.
        $link = '<a class="dimmed" href="view.php?id='.$teamup->coursemodule.'">'.format_string($teamup->name).'</a>';
    } else {
        // Show normal if the mod is visible.
        $link = '<a href="view.php?id='.$teamup->coursemodule.'">'.format_string($teamup->name).'</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($teamup->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo $OUTPUT->heading($strteamups, 2);
echo html_writer::table($table);

echo $OUTPUT->footer();
