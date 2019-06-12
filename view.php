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
 * teamup view.
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @copyright  UNSW
 * @author     UNSW
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by Dominique Palumbo (UCLouvain)
 * Modification
    the string teambuilder was replaced by teampup (same structure)
    The main difference is the management of the new types (two, three, four, five)
    and also the type in multi-language
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js("/mod/teamup/js/json2.js");
$PAGE->requires->css('/mod/teamup/styles.css');


$id           = optional_param('id', 0, PARAM_INT); // The course_module ID, or...
$a            = optional_param('a', 0, PARAM_INT); // Teamup instance ID.
$preview      = optional_param('preview', 0, PARAM_INT);
$action       = optional_param('action', null, PARAM_TEXT);

// Modification by UCLouvain.
$displaytypes = [
  "one"         => get_string('oneOption',        'mod_teamup'),
  "any"         => get_string('anyOption',        'mod_teamup'),
  "atleastone"  => get_string('atleastoneOption', 'mod_teamup'),
  "two"         => get_string('twoOption',        'mod_teamup'),
  "three"       => get_string('threeOption',      'mod_teamup'),
  "four"        => get_string('fourOption',       'mod_teamup'),
  "five"        => get_string('fiveOption',       'mod_teamup'),
];
// END UCLouvain modification.

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

$event = \mod_teamup\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('teamup', $teamup);
$event->trigger();

// Check out if we've got any submitted data.
if ($action == "submit-questionnaire") {
    $questions = teamup_get_questions($teamup->id, $USER->id);
    if (has_capability('mod/teamup:respond', $ctxt)) {
        foreach ($questions as $q) {
            if ($q->type === 'one') {
                $response = optional_param('question-'.$q->id, 0, PARAM_RAW);
            } else {
                $response = optional_param_array('question-'.$q->id, 0, PARAM_RAW);
            }
            // Delete all their old answers.
            foreach ($q->answers as $a) {
                if ($a->selected) {
                    $DB->delete_records("teamup_response", array("userid" => $USER->id, "answerid" => $a->id));
                }
            }
            // Now insert their new answers.
            if (is_array($response)) {
                foreach ($response as $r) {
                    $record = new stdClass();
                    $record->userid = $USER->id;
                    $record->answerid = $r;
                    $DB->insert_record("teamup_response", $record);
                }
            } else {
                $record = new stdClass();
                $record->userid = $USER->id;
                $record->answerid = $response;
                $DB->insert_record("teamup_response", $record);
            }
        }
        $feedback = get_string('answersSubmitted', 'mod_teamup');
    }
}

$mode = 'student';

if (has_capability('mod/teamup:create', $ctxt)) {
    if ($preview) {
        $mode = 'preview';
        $PAGE->requires->js("/mod/teamup/js/view.js");
    } else {
        $mode = 'teacher';
        $PAGE->requires->js("/mod/teamup/js/editview.js");
    }
} else {
    require_capability('mod/teamup:respond', $ctxt);
    $mode = 'student';
    $PAGE->requires->js("/mod/teamup/js/view.js");
}

if (($mode == 'teacher') && ($teamup->open < time()) && !isset($_GET['f'])) {
    redirect(new moodle_url('/mod/teamup/build.php', ['id' => $id]));
}

$PAGE->set_url('/mod/teamup/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($teamup->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_cm($cm);
$PAGE->set_context($ctxt);
$output = $PAGE->get_renderer('mod_teamup');
echo $output->header();

// First things first: if it's not open, don't show it to students.
if (($mode == "student") && $teamup->groupid && !groups_is_member($teamup->groupid)) {
    echo '<div class="ui-widget" style="text-align:center;">';
    echo '<div style="display:inline-block; padding-left:10px; padding-right:10px;" class="ui-state-highlight ui-corner-all">';
    echo '<p>'.get_string('noneedtocomplete', 'mod_teamup').'</p>';
    echo '</div></div>';
} else if (($mode == "student") && (($teamup->open > time()) || $teamup->close < time())) {
    echo '<div class="ui-widget" style="text-align:center;">';
    echo '<div style="display:inline-block; padding-left:10px; padding-right:10px;" class="ui-state-highlight ui-corner-all">';
    echo '<p>'.get_string('notopen', 'mod_teamup').'</p>';
    echo '</div></div>';
} else {
    if ($mode == 'teacher') {
        // Before we start - import the questions.
        $import = optional_param('import', 0, PARAM_INT);
        if ($import) {
            $questions = teamup_get_questions($import);
            foreach ($questions as $q) {
                unset($q->id);
                $q->builder = $teamup->id;
                $newid = $DB->insert_record('teamup_question', $q);
                foreach ($q->answers as $a) {
                    unset($a->id);
                    $a->question = $newid;
                    $DB->insert_record('teamup_answer', $a);
                }
            }
        }

        echo $output->navigation_tabs($id, "questionnaire");

        if ($teamup->open < time()) {
            echo '<div class="ui-widget" style="text-align:center;">';
            $style = "display:inline-block; padding-left:10px; padding-right:10px;";
            echo '<div style="'.$style.'" class="ui-state-highlight ui-corner-all">';
            echo '<p>'.get_string('noeditingafteropentime', 'mod_teamup').'</p>';
            echo '</div></div>';
            echo '<script type="text/javascript">var interaction_disabled = true;</script>';
        }

        // Set up initial questions.
        $questions = teamup_get_questions($teamup->id);
        echo '<script type="text/javascript"> var initQuestions = ' . json_encode($questions) . '</script>';

        echo '<div id="questions">';
        $strdelete = get_string('delete');
        foreach ($questions as $q) {
            $strtype = $displaytypes[$q->type];
            echo <<<HTML
<div class="question" id="question-{$q->id}"><table class="mod-teamup-table">
<tr>
    <td rowspan="2" class="handle">&nbsp;</td>
    <td><span class="questionText" data-id="$q->id">$q->question</span> <span class="type">$strtype</span></td>
    <td class="edit">
        <a onclick="deleteQuestion(this)">$strdelete</a>
        <div class="qobject" style="display:none;"/></div>
    </td>
</tr>
<tr>
    <td class="answers" colspan="2"><ul>
HTML;
            foreach ($q->answers as $a) {
                echo "<li class='answerText' data-id='".$a->id."'>$a->answer</li>";
            }
            echo  '</ul></td></tr></table></div>';
        }

        echo '</div>';

        if ($teamup->open > time()) {

            // New question form.
            $onclick = "saveQuestionnaire('{$CFG->wwwroot}/mod/teamup/ajax.php', {$id})";
            echo '<div style="display:none;text-align:center;" id="savingIndicator"></div>';
            echo '<div style="text-align:center;"><button class="btn btn-default" type="button" id="saveQuestionnaire" onclick="'
                    .$onclick.'">';
            echo get_string('savequestionnaire', 'mod_teamup').'</button></div>';

            if (empty($questions)) {
                $otherbuilders = $DB->get_records('teamup', array('course' => $course->id));
                $strimportfrom = get_string('importquestionsfrom', 'mod_teamup');

                echo '<div style="text-align:center;margin:10px;font-weight:bold;" id="importContainer">';
                echo $strimportfrom.': <select id="importer">';
                foreach ($otherbuilders as $o) {
                    if($teamup->id != $o->id) {
                        echo "<option value=\"$o->id\">$o->name</option>";
                    }
                }
                $strimport = get_string('import', 'mod_teamup');
                $stror = get_string('or', 'mod_teamup');
                echo '</select><button class="btn btn-default" type="button" id="importButton">'.$strimport.'</button><br/>'.$stror
                        .'</div>';

            }
            // Modification by UCLouvain
            $straddanewquestion   = get_string('addanewquestion',   'mod_teamup');
            $straddnewquestion    = get_string('addnewquestion',    'mod_teamup');
            $strquestion          = get_string('question');
            $stranswertype        = get_string('answertype',        'mod_teamup');
            $stranswers           = get_string('answers',           'mod_teamup');
            $strselectone         = get_string('selectone',         'mod_teamup');
            $strselectany         = get_string('selectany',         'mod_teamup');
            $strselectatleastone  = get_string('selectatleastone',  'mod_teamup');
            $strselecttwo         = get_string('selecttwo',         'mod_teamup');
            $strselectthree       = get_string('selectthree',       'mod_teamup');
            $strselectfour        = get_string('selectfour',        'mod_teamup');
            $strselectfive        = get_string('selectfive',        'mod_teamup');

            echo <<<HTML
<div style="text-align:center;font-weight:bold;margin:10px;">$straddanewquestion</div>
<div style="text-align:center;">
<div id="newQuestionForm">
    <table class="mod-teamup-table">
        <tr>
            <th scope="row">$strquestion</th>
            <td><input name="question" type="text" class="text" /></td>
        </tr>
        <tr>
            <th scope="row">$stranswertype</th>
            <td><select>
                <option value="any">$strselectany</option>
                <option value="atleastone">$strselectatleastone</option>
                <option value="one">$strselectone</option>
                <option value="two">$strselecttwo</option>
                <option value="three">$strselectthree</option>
                <option value="four">$strselectfour</option>
                <option value="five">$strselectfive</option>
            </select></td>
        </tr>
        <tr>
            <th scope="row">$stranswers</th>
            <td id="answerSection"><input type="text" name="answers[]" class="text" maxlength="250" /><br/>
                <button class="btn btn-default" onclick="addNewAnswer();" type="button">+</button>
                <button class="btn btn-default" onclick="removeLastAnswer();" type="button">-</button>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><button class="btn btn-default" id="addNewQuestion" type="button" onclick="addNewQuestion();">$straddnewquestion
                </button></td>
        </tr>
    </table>
</div>
</div>
HTML;
            // END Modification by UCLouvain
        }
    } else if (($mode == "preview") || ($mode == "student")) {
        $questions = teamup_get_questions($teamup->id, $USER->id);
        $responses = teamup_get_responses($teamup->id, $USER->id);

        if ($mode == "preview") {
            echo $output->navigation_tabs($id, "preview");
        }

        if (($mode == "student") && empty($feedback)) {
            if ($responses !== false && !$teamup->allowupdate) {
                $feedback = "You have already completed this questionnaire.";
            }
        }

        if (isset($feedback) && $feedback) {
            echo '<div class="ui-widget centered">';
            $style = 'display:inline-block; padding-left:10px; padding-right:10px;';
            echo '<div style="'.$style.'" class="ui-state-highlight ui-corner-all">';
            echo '<p>'.$feedback.'</p>';
            echo '</div></div>';
        }

        if (!empty($teamup->intro)) {
            echo '<div class="description">' . format_module_intro('teamup', $teamup, $cm->id) . '</div>';
        }

        if (!$responses || $teamup->allowupdate) {
            $preview = $mode == "preview" ? "&preview=1" : "";
            echo '<form onsubmit="return validateForm(this)" action="view.php?id='.$id.$preview.'" method="POST">';

            foreach ($questions as $q) {
                echo <<<HTML
<div class="question" id="question-{$q->id}"><table class="mod-teamup-table">
<tr>
    <td><span class="questionText">$q->question</span> <span class="type">{$displaytypes[$q->type]}</span></td>
</tr>
<tr>
    <td class="answers" colspan="2">
        <div style="visibility:hidden;">
HTML;
                foreach ($q->answers as $a) {
                    if ($q->type == "one") {
                        $type = "radio";
                        $name = '';
                    } else {
                        $type = "checkbox";
                        $name = "[]";
                    }
                    // Modification by UCLouvain
                    $class = $q->type == "atleastone" ? "atleastone" : "";
                    if($q->type == "two") {
                        $class = "two";
                    }
                    if($q->type == "three") {
                        $class = "three";
                    }
                    if($q->type == "four") {
                        $class = "four";
                    }
                    if($q->type == "five")  {
                        $class = "five";
                    }
                    // END Modification by UCLouvain.
                    $inputarr = ['type' => $type, 'name' => "question-{$q->id}{$name}", 'value' => $a->id, 'class' => $class
                                , 'style' => 'margin-right:5px;'];
                    if ($a->selected) {
                        $inputarr['checked'] = 'checked';
                    }
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
            $submit = get_string('submit');
            echo <<<HTML
    <input type="hidden" name="action" value="submit-questionnaire" />
    <div style="text-align:center;"><input type="submit" value="$submit" /></div>
</form>
HTML;

        }
    }
}
echo $OUTPUT->footer();
