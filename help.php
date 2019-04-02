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
 * This page is the tab with the online help to understand and use the plugin (only for the teacher).
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @copyright  UCLouvain
 * @author     UCLouvain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js("/mod/teamup/js/json2.js");
$PAGE->requires->css('/mod/teamup/styles.css');

$id = optional_param('id', 0, PARAM_INT); // The course_module ID, or...

if ($id) {
  list ($course, $cm) = get_course_and_cm_from_cmid($id, 'teamup');
  $teamup = $DB->get_record('teamup', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
  if (!$teamup = $DB->get_record('teamup', array('id' => $a), '*', MUST_EXIST)) {
    print_error('You must specify a course_module ID or an instance ID');
  }
  list ($course, $cm) = get_course_and_cm_from_instance($teamup, 'teamup');
  $id = $cm->id;
}

require_login($course, true, $cm);

$ctxt = context_module::instance($cm->id);
require_capability("mod/teamup:build", $ctxt);

$strteamups = get_string('modulenameplural', 'teamup');
$strteamup  = get_string('modulename', 'teamup');

$PAGE->navbar->add($strteamups);
$PAGE->set_url('/mod/teamup/build.php', array('id' => $cm->id));
$PAGE->set_cm($cm);
$PAGE->set_context($ctxt);
$PAGE->set_title($teamup->name);
$PAGE->set_heading($course->fullname);

$output = $PAGE->get_renderer('mod_teamup');

echo $output->header();
echo $output->navigation_tabs($id, "help");
echo(get_string('presentation', 'mod_teamup'));
echo $output->footer();