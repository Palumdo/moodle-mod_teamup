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
 * Controller for building a teamup.
 *
 * @package    mod_teambuilder
 * @copyright  UNSW
 * @author     Adam Olley
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** 
 * Package fork   mod_teamup
 * Modified by  Palumbo Dominique (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
    The sub criterion was removed
    The and & or operations was replaced by

    -Group similar individuals  = Form groups whose members are similar to defined
    criteria. Creation of homogeneous groups. Applied to discrete values, with no
    obligation whatsoever numerical.

    -Disperse similar individuals = Distribute qualifying students across groups.
    Applied to discrete values, with no obligation whatsoever numerical.

    -Avoid minorities = Divide students so that at least two students sharing a
    criterion are in the same group (especially for minorities).
    Applied to discrete values, with no obligation whatsoever numerical.

    -Balancing Level = Create groups that are \"right\", whose total forces are
    similar in all groups (usually based on academic results). Applied to numerical
    values ​​(continuous and discrete).

    These rules to create group was definied in the GROUPENG python project. And
    apply to teambuilder to become Team Up.

    A filter to remove students with no answers was also add.
    Actions like optimize (launch again the algorithm)
    Or Equalize that try to make team with the same size.

    A report was also add with the result and the possibility to see quickly groups
    that not feet to all criterion.
    ( ***from the doc***
        The number of teams fixes the number of students in each group. For example,
        123 students in 31 groupes makes 4 individuals in groups.
        Preview : This button launches the group creation with the selected
        criterias.
        Reset : This button empties all groups and sets students in the part non
        affected to groups.
        Optimize : This button makes some switches in students distribution to
        improve the criterias. Succes is not guaranteed but you may repeat
        optimization several times.
        Equalize : Force to equalize the number of student in each groups.
        Sometimes necessary after optimization.
    )
*/
 
 

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/group/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/mod/teamup/styles.css');
$PAGE->requires->js("/mod/teamup/js/json2.js");
$PAGE->requires->js_call_amd('mod_teamup/build', 'init');


$id                   = optional_param('id',                     0, PARAM_INT);   // The course_module ID, or...
$a                    = optional_param('a',                      0, PARAM_INT);   // teamup instance ID.
$preview              = optional_param('preview',                0, PARAM_INT);
$action               = optional_param('action',              null, PARAM_TEXT);
$groupingid           = optional_param('groupingID',             0, PARAM_INT);
$groupingname         = trim(optional_param('groupingName',   null, PARAM_TEXT));
$inheritgroupingname  = optional_param('inheritGroupingName',    0, PARAM_INT);
$nogrouping           = optional_param('nogrouping',             0, PARAM_INT);
$group                = optional_param('group',                  0, PARAM_INT);

if (!$nogrouping) {
    $nogrouping = empty($groupingid) && empty($groupingname);
}

$teams                = optional_param_array('teams',     array(), PARAM_RAW);
$teamnames            = optional_param_array('teamnames', array(), PARAM_TEXT);

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
echo ('<div id="protectAll" class="modal" style="display:none;">
        <h4 class="modal-title">'.get_string('pleasewait', 'mod_teamup').'</h4>
       </div><!-- /.modal -->');

if (!is_null($action) && $action == "create-groups") {
    $data           = new stdClass();
    $data->courseid = $course->id;
    $data->name     = $groupingname;
    $grouping       = groups_create_grouping($data);
    foreach ($teams as $k => $teamstr) {
        $name                 = $teamnames[$k];
        $team                 = explode(",", $teamstr);
        $oname                = $groupingname.' '.$name;
        $groupdata            = new stdClass();
        $groupdata->courseid  = $course->id;
        $groupdata->name      = $oname;
        $group                = groups_create_group($groupdata);
        foreach ($team as $user) {
            if (!empty($user)) {
                groups_add_member($group, $user);
            }
        }
        groups_assign_grouping($grouping, $group);
    }

    $feedback = get_string('groupcreationsuccess', 'mod_teamup');
} else {
    $students  = get_enrolled_users($ctxt, 'mod/teamup:respond', $group, 'u.id,u.firstname,u.lastname', null, 0, 0, true);
    $responses = teamup_get_responses($teamup->id);
    $questions = teamup_get_questions($teamup->id);

    echo '<script type="text/javascript">';
    echo '  var students  = ' . json_encode($students) . ';';
    echo '  var responses = ' . json_encode($responses) . ';';
    echo '  var questions = ' . json_encode($questions) . ';';
    echo '</script>';
}

echo $output->navigation_tabs($id, "build");

if (!empty($feedback)) {
    echo '<div class="ui-widget" style="text-align:center;">';
    echo '  <div style="display:inline-block; padding:0 10px 0 10px;width:100%;" class="ui-state-highlight ui-corner-all">';
    echo '    <p id="feedback">'.$feedback.'</p>';
    echo '  </div>';
    echo '</div>';
} else {
    $url          = new moodle_url('/mod/teamup/export/xls/export.php?id='.$cm->id);
    $imgico       = new moodle_url('/mod/teamup/css/help.png');
    $imgicoxls    = new moodle_url('/mod/teamup/css/excel.png');
    echo '<fieldset>
          <legend class="myShow">'.get_string('groupName', 'mod_teamup').'&nbsp;<a title="'.get_string('exportexcel', 'mod_teamup').
                 '" href="'.$url.'" target="_outside"><img alt="'.get_string('exportexcel', 'mod_teamup').'"
                 src="'.$imgicoxls.'" width="24" /></a>
              </legend>
              <div>
                  '.get_string('groupSchemaName', 'mod_teamup').'
                  <span class="helptooltip">
                      <a href="'.$CFG->httpswwwroot.'/help.php?component=teamup&amp;identifier=namingscheme&amp;lang='
                      .current_language().'" title="'.get_string('help').'" aria-haspopup="true" target="_blank"
                      id="yui_3_17_2_1_1531814373153_307"><img width="32" class="icon iconhelp" alt="'.get_string('help').'"
                      title="'.get_string('help').'" src="'.$imgico.'"></a>
                  </span>: <input name="namingscheme" value="'.get_string('groupTitle', 'mod_teamup').' #" id="id_namingscheme"
                            type="text" style="height:26px;margin-top:10px;">
              </div>
          </fieldset>';

    echo html_writer::div(null, '', ['id' => 'predicate']);

    $buttons = [
        html_writer::tag('span', get_string('numberofteams', 'mod_teamup'). ' :', ['id' => 'placeithere']),
        html_writer::tag('button', html_writer::tag('strong', get_string('preview', 'mod_teamup')), ['type' => 'button', 
                            'id' => 'buildteams', 'class' => 'btn btn-default']),
        html_writer::tag('button', get_string('resetteams', 'mod_teamup'), ['type' => 'button', 'id' => 'resetteams', 
                            'class' => 'btn btn-default']),
        html_writer::tag('button', get_string('prettify',   'mod_teamup'), ['type' => 'button', 'id' => 'prettify',
                            'class' => 'btn btn-default', 'title' => get_string('prettifyHelp', 'mod_teamup')]),
        html_writer::tag('button', get_string('equalize',   'mod_teamup'), ['type' => 'button', 'id' => 'equalize',
                            'class' => 'btn btn-default', 'title' => get_string('equalizeHelp', 'mod_teamup')]),
    ];
    echo '<div style="width:100%;text-align:right;margin-top:5px;padding-right:250px;"><button class="btn btn-default"
                id="addnewcriterion">'.get_string('addnewcriterion', 'mod_teamup').'</button></div>';

    echo '<div class="ui-widget" style="text-align:center;margin-top:5px;">';
    echo '  <div style="display:inline-block; padding:0 10px 0 10px;width:100%;" class="ui-state-highlight ui-corner-all">';
    echo '    <p id="feedback"></p>';
    echo '  </div>';
    echo '</div>';


    echo html_writer::div(implode('&nbsp;', $buttons), 'centered padded');


    $stepper = html_writer::span(1, 'stepper');
    echo html_writer::div($stepper, 'centered padded');

    echo '<fieldset>
	   	  <legend class="myShow">'.get_string('preview', 'mod_teamup').'</legend>
		  <div style="">';

    $groups = "<option value='0'>".get_string('allstudents', 'mod_teamup')."</option>";
    foreach (groups_get_all_groups($course->id) as $grp) {
        $selected = "";
        if ($group == $grp->id) {
            $selected = "selected";
        }
        $groups .= "  <option value=\"$grp->id\" $selected>$grp->name</option>";
    }

    echo get_string('aggFilter', 'mod_teamup') . ' : <select id="series">';
    echo $groups;
    echo '</select>';
    echo html_writer::tag('button', get_string('deleteAllRed', 'mod_teamup'), ['type' => 'button', 'id' => 'deleteallred',
            'class' => 'btn btn-default', 'style' => 'margin-right:5px;margin-left:5px;']);
    echo html_writer::tag('button', get_string('keepAllRed',   'mod_teamup'), ['type' => 'button', 'id' => 'keepallred',
            'class' => 'btn btn-default']);
    echo html_writer::start_div('', ['id' => 'unassigned']);
    echo html_writer::tag('h2', get_string('unassignedtoteams', 'mod_teamup'));
    echo html_writer::start_div('sortable');

    foreach ($students as $s) {
        $answeredstate = !isset($responses[$s->id]) || empty($responses[$s->id]) ? 'unanswered' : 'answered';
        echo "<div id=\"student-$s->id\" class=\"student studentui $answeredstate\">
                $s->lastname&nbsp;$s->firstname <div id=\"studentdel-$s->id\" class=\"studentdel\" style=\"\">X</div></div>";
    }

    $groupings = "";
    foreach (groups_get_all_groupings($course->id) as $grping) {
        $groupings .= "<option value=\"$grping->id\">$grping->name</option>";
    }

    $strcreategroups              = get_string('creategroups'               , 'mod_teamup');
    $strgroupingname              = get_string('groupingname'               , 'group');
    $straddtogrouping             = get_string('addtogrouping'              , 'mod_teamup');
    $strconfirmgroupbuilding      = get_string('confirmgroupbuilding'       , 'mod_teamup');
    $strprefixteamnames           = get_string('prefixteamnames'            , 'mod_teamup');
    $strdontassigngrouptogrouping = get_string('dontassigngrouptogrouping'  , 'mod_teamup');
    $strcancel                    = get_string('cancel');
    $strok                        = get_string('ok');
    $strsummary                   = get_string('summary');
    $strreportdetail              = get_string('reportDetail'               , 'mod_teamup');
    $strgroupnooptimal            = get_string('groupNoOptimal'             , 'mod_teamup');
    $straggfilter                 = get_string('aggFilter'                  , 'mod_teamup');

    echo <<<HTML
</div></div>
  <div id="teams">
  </div>
</div>
</fieldset>


<fieldset>
  <legend id="legendsum" class="myHide">$strsummary</legend>
  <div style="display:none;">
    <span id="aggListTitle">$straggfilter</span><select id="aggList" style="display:none;margin-bottom: 5px;"></select>
    <ul class="nav nav-tabs">
      <li id="smnu1" class="active"><a href="#legendsum" onclick="$('.box_ok').show();$('#smnu1').addClass('active');
                                                        $('#smnu2').removeClass('active');return false;">$strreportdetail </a></li>
      <li id="smnu2" ><a href="#legendsum" onclick="$('.box_ok').hide();$('#smnu2').addClass('active'); $
        ('#smnu1').removeClass('active'); return false;">$strgroupnooptimal</a></li>
    </ul>    
    <div class=""container-fluid" style="padding-left:20px;display:;">
      <div class="row inline-block-row" id="summary"></div>
    </div>
  </div>
</fieldset>

    
<div style="text-align:center;margin:15px 50px 0px;border-top:1px solid black;padding-top:15px;">
  <button type="button" onclick="$('#createGroupsForm').slideDown(300);" class="creategroups btn btn-default">$strcreategroups
  </button>
  <div style="display:none" id="createGroupsForm"><p>$strconfirmgroupbuilding</p>
    <table class="mod-teamup-table" style="margin:auto;width:100%;background:transparent;margin-bottom:5px;">
      <tr id="nameofgroup">
        <td style="width:50%;text-align:left;padding-left:3px;"><label for="groupingName">$strgroupingname</label></td>
        <td style="padding-right:5px;"><input style="height:26px;text-align:left;padding-left:3px;margin-top:8px;" type="text" 
            class="form-control" id="groupingName"></td>
      </tr>
      <tr style="display:none;"><td colspan="2" style="text-align:center;font-size:0.8em">or...</td></tr>
      <tr style="display:none;">
        <th scope="row"><label for="groupingSelect">$straddtogrouping</label></th>
        <td><select id="groupingSelect">$groupings</select></td>
      </tr>
      <tr style="display:none;">
        <th scope="row"><label for="inheritGroupingName">$strprefixteamnames</label></th>
        <td style="text-align:left;">
          <input type="checkbox" checked="checked" name="inheritGroupingName" id="inheritGroupingName" value="1" />
        </td>
      </tr>
      <tr style="display:none;"><td colspan="2" style="text-align:center;font-size:0.8em">or...</td></tr>
      <tr style="display:none;">
        <th scope="row"><label for="nogrouping">$strdontassigngrouptogrouping</label></th>
        <td style="text-align:left;"><input type="checkbox" checked="checked" name="nogrouping" id="nogrouping" value="1" /></td>
      </tr>
    </table>
    <button class="btn btn-default" type="button" onclick="$('#createGroupsForm').slideUp(300);">$strcancel</button>&nbsp
    <button class="btn btn-default" type="button" id="creategroups">$strok</button>
  </div>
</div>
HTML;
}

echo $OUTPUT->footer();