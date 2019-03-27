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

$params = array(
    'context' => $ctxt,
    'objectid' => $teamup->id
);

$PAGE->set_url('/mod/teamup/bank.php', array('id' => $cm->id));
$PAGE->set_title(format_string($teamup->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_cm($cm);
$PAGE->set_context($ctxt);
$output = $PAGE->get_renderer('mod_teamup');
echo $output->header();

echo $output->navigation_tabs($id, "bank");
$allQuestion = [];
$displaytypes = [
  "one"         => get_string('oneOption',        'mod_teamup'),
  "any"         => get_string('anyOption',        'mod_teamup'),
  "atleastone"  => get_string('atleastoneOption', 'mod_teamup'),
  "two"         => get_string('twoOption',        'mod_teamup'),
  "three"       => get_string('threeOption',      'mod_teamup'),
  "four"        => get_string('fourOption',       'mod_teamup'),
  "five"        => get_string('fiveOption',       'mod_teamup'),
];

// Set up initial questions.
$questions = teamup_get_all_questions();

echo '<script type="text/javascript"> var init_questions = ' . json_encode($questions) . '</script>';
echo '<div id="questions">';

foreach ($questions as $q) {
  
    if(array_search($q->question,$allQuestion) === false) {
    array_push($allQuestion, $q->question);

  
  echo <<<HTML
<div class="question" id="question-{$q->id}"><table>
<tr>
    <td><span class="questionText">$q->question</span> <span class="type">{$displaytypes[$q->type]}</span></td>
</tr>
<tr>
    <td class="answers" colspan="2">
        <div style="visibility:;">
HTML;
  foreach ($q->answers as $a) {
    if ($q->type == "one") {
      $type = "radio";
      $name = '';
    } else {
      $type = "checkbox";
      $name = "[]";
    }
    
    $class = $q->type == "atleastone" ? "atleastone" : "";
    $inputarr = ['type' => $type, 'name' => "question-{$q->id}{$name}", 'value' => $a->id, 'class' => $class];
    $input = html_writer::empty_tag('input', $inputarr);
    echo html_writer::label($input.$a->answer, null);
  }
  echo <<<HTML
        </div>
    </td>
</tr>
</table></div>
HTML;

}
}        
echo $OUTPUT->footer();        