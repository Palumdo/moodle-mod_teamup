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
 * Controller for creating excel export
 * the Excel export have all student of the course and their groups
 *
 * @package    mod_teamup
 * @copyright  UCLouvain
 * @author     Palumbo Dominique 
**/



global $CFG, $SESSION, $DB;

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');
require_once $CFG->dirroot.'/user/profile/lib.php';

$id = optional_param('id', 0, PARAM_INT); // The course_module ID, or...

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'teamup');
    $teamup = $DB->get_record('teamup', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    die;
}    


require_login($course, true, $cm);
$ctxt = context_module::instance($cm->id);

$mode = '';
if (has_capability('mod/teamup:create', $ctxt)) {
    $mode = 'teacher';
}

if($mode == '') {
  redirect(new moodle_url('/my'));
  die();
}  

$reportname = "teamup_export";
$workbook = new MoodleExcelWorkbook('-');

$workbook->send($reportname);
$worksheet = array();
$worksheet[0] = $workbook->add_worksheet('');

$col = 0;

$worksheet[0]->write(0, $col, get_string('group'));
$col++;
$worksheet[0]->write(0, $col, get_string('date'));
$col++;
$worksheet[0]->write(0, $col, get_string('firstname'));
$col++;
$worksheet[0]->write(0, $col, get_string('lastname'));
$col++;
$worksheet[0]->write(0, $col, 'NOMA');
$col++;

$worksheet[0]->write(0, $col, get_string('email'));
$col++;
$worksheet[0]->write(0, $col, get_string('answer'));
$row = 1;

$users = get_enrolled_users($ctxt);
foreach($users as $user) {
    $grp = teamup_get_groups($user->id, $course->id);
    $asw = teamup_get_user_answers($cm->instance, $user->id);

    $worksheet[0]->write($row, 0, $grp);
    $worksheet[0]->write($row, 1, date('m/d/Y',$user->timecreated));
    $worksheet[0]->write($row, 2, $user->firstname);
    $worksheet[0]->write($row, 3, $user->lastname);
    $worksheet[0]->write($row, 4, $user->idnumber);
    $worksheet[0]->write($row, 5, $user->email);
    $worksheet[0]->write($row, 6, $asw);
    $row++;
}    

$workbook->close();
die;
