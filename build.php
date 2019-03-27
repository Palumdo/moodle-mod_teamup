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
 * @package fork   mod_teamup
 * @author of modification  Palumbo Dominique (UCLouvain)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
echo ('<div id="protectAll" class="modal" style="position:absolute;top:50%;left:25%;width:50%;margin:0;padding:0;background:white;display:none;">
         <div class="modal-dialog">
           <div class="modal-content">
             <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
               <h4 class="modal-title">'.get_string('pleasewait', 'mod_teamup').'</h4>
             </div>
             <div id="" class="modal-body"></div>
             <div class="modal-footer"></div>
           </div><!-- /.modal-content -->
         </div><!-- /.modal-dialog -->
       </div><!-- /.modal -->');

if (!is_null($action) && $action == "create-groups") {
/*  if (!$nogrouping) { */
//    if (strlen($groupingname) > 0) {
      $data           = new stdClass();
      $data->courseid = $course->id;
      $data->name     = $groupingname;
      $grouping       = groups_create_grouping($data);
/*
    } else {
      $grouping       = groups_get_grouping($groupingid);
      $groupingname   = $grouping->name;
      $grouping       = $grouping->id;
    }
*/    
    
/*  } */

  foreach ($teams as $k => $teamstr) {
    $name                 = $teamnames[$k];
    $team                 = explode(",", $teamstr);
//    $oname                = !$nogrouping && $inheritgroupingname ? "$groupingname $name" : $name;
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
//    if (!$nogrouping) {
      groups_assign_grouping($grouping, $group);
//    }
  }

  $feedback = get_string('groupcreationsuccess', 'mod_teamup');
} else {

/*
  $group = '';
  if ($teamup->groupid) {
    $group = $teamup->groupid;
  }
*/
  
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
  echo '  <div style="display:inline-block; padding-left:10px; padding-right:10px;width:100%;" class="ui-state-highlight ui-corner-all">';
  echo '    <p id="feedback">'.$feedback.'</p>';
  echo '  </div>';
  echo '</div>';
} else { 


  echo '<fieldset>
          <legend class="myShow">'.get_string('groupName', 'mod_teamup').'</legend>
          <div style="display:;">
           '.get_string('groupSchemaName', 'mod_teamup').'             
           <span class="helptooltip" id="yui_3_17_2_1_1531814373153_308">
              <a href="'.$CFG->httpswwwroot.'/help.php?component=teamup&amp;identifier=namingscheme&amp;lang='.current_language().'" title="'.get_string('help').'" aria-haspopup="true" target="_blank" id="yui_3_17_2_1_1531814373153_307"><img style="width:32px;" class="icon iconhelp" alt="'.get_string('help').'" title="'.get_string('help').'" src="/theme/image.php/uclouvain/core/1528214972/help" id="yui_3_17_2_1_1531814373153_310"></a>
            </span>: <input name="namingscheme" value="'.get_string('groupTitle', 'mod_teamup').' #" id="id_namingscheme" type="text" style="height:26px;margin-top:10px;">
          </div>
        </fieldset>';
        
  echo html_writer::div(null, '', ['id' => 'predicate']);

  $buttons = [
        html_writer::tag('span',get_string('numberofteams', 'mod_teamup'). ' :',['id' => 'placeithere']),
        html_writer::tag('button', html_writer::tag('strong', get_string('preview', 'mod_teamup')),['type' => 'button', 'id' => 'buildteams', 'class' => '']),
        html_writer::tag('button', get_string('resetteams', 'mod_teamup'), ['type' => 'button', 'id' => 'resetteams', 'class' => '']),
        html_writer::tag('button', get_string('prettify',   'mod_teamup'), ['type' => 'button', 'id' => 'prettify', 'style' => '', 'title' => get_string('prettifyHelp', 'mod_teamup')]),
        html_writer::tag('button', get_string('equalize',   'mod_teamup'), ['type' => 'button', 'id' => 'equalize', 'style' => '', 'title' => get_string('equalizeHelp', 'mod_teamup')]),
/*        
        html_writer::tag('button', get_string('save'), ['type' => 'button', 'id' => 'hist_save', 'style' => '']),
        html_writer::tag('button', get_string('load'), ['type' => 'button', 'id' => 'hist_go', 'style' => '']),
*/        
  ];
  echo '<div style="width:100%;text-align:right;margin-top:5px;padding-right:250px;"><button id="addnewcriterion">'.get_string('addnewcriterion', 'mod_teamup').'</button></div>';

  echo '<div class="ui-widget" style="text-align:center;margin-top:5px;">';
  echo '  <div style="display:inline-block; padding-left:10px; padding-right:10px;width:100%;" class="ui-state-highlight ui-corner-all">';
  echo '    <p id="feedback"></p>';
  echo '  </div>';
  echo '</div>';
  
  
  echo html_writer::div(implode('&nbsp;', $buttons), 'centered padded');
  
  
  $stepper = html_writer::span(1, 'stepper');
  echo html_writer::div($stepper, 'centered padded');
/*
  echo '<fieldset>
      <legend class="myHide">'.get_string('abc', 'mod_teamup').'</legend>
      <div style="display:none">

      <span class="helptooltip" id="yui_3_17_2_1_1531814373153_990">
        <a href="'.$CFG->httpswwwroot.'/help.php?component=mod_teamup&amp;identifier=helpserie&amp;lang='.current_language().'" title="'.get_string('help').'" aria-haspopup="true" target="_blank" id="yui_3_17_2_1_1531814373153_307"><img style="width:32px;" class="icon iconhelp" alt="'.get_string('help').'" title="'.get_string('help').'" src="/theme/image.php/uclouvain/core/1528214972/help" id="yui_3_17_2_1_1531814373153_991"></a>
      </span> 
      '.html_writer::tag('button', get_string('abc', 'mod_teamup'), ['type' => 'button', 'id' => 'serie', 'style' => '']).'
      </div>
    </fieldset>';
*/
  
  echo '<fieldset>
		<legend class="myShow">'.get_string('preview', 'mod_teamup').'</legend>
		<div style="">';

  $groups = "<option value='0'>".get_string('allstudents', 'mod_teamup')."</option>";
  foreach (groups_get_all_groups($course->id) as $grp) {
//    if(strpos($grp->name, get_string('abc', 'mod_teamup')) !== false ) {
    $selected = "";
    if($group == $grp->id) {
      $selected = "selected";
    }
      $groups .= "  <option value=\"$grp->id\" $selected>$grp->name</option>";
//    }
  }
    
  echo get_string('aggFilter', 'mod_teamup') . ' : <select id="series">';
  echo $groups;
  echo '</select>';  
  echo html_writer::tag('button', get_string('deleteAllRed',   'mod_teamup'), ['type' => 'button', 'id' => 'deleteallred', 'style' => '']);
  echo html_writer::tag('button', get_string('keepAllRed',   'mod_teamup'), ['type' => 'button', 'id' => 'keepallred', 'style' => '']);
  
  $unassignedheading = html_writer::tag('h2', get_string('unassignedtoteams', 'mod_teamup'));
  echo html_writer::start_div('', ['id' => 'unassigned']);
  echo $unassignedheading.$unassignedbutton;
  echo html_writer::start_div('sortable');

  foreach ($students as $s) {
    $answeredstate = !isset($responses[$s->id]) || empty($responses[$s->id]) ? 'unanswered' : 'answered';
    echo "<div id=\"student-$s->id\" class=\"student studentui $answeredstate\">$s->lastname&nbsp;$s->firstname<div id=\"studentdel-$s->id\" class=\"studentdel\" style=\"\">X</div></div>";
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
  $strAggFilter                 = get_string('aggFilter'                  , 'mod_teamup');

  echo <<<HTML
</div></div>
  <!--- <div id="excluded" style="background-color:#AACCFF;min-height:100px;width:100%;"></div> --->
  <div id="teams">
  </div>
</div>
</fieldset>


<fieldset>
  <legend id="legendsum" class="myHide">$strsummary</legend>
  <div style="display:none;">
    <span id="aggListTitle">$strAggFilter</span><select id="aggList" style="display:none;margin-bottom: 5px;"></select>
    <ul class="nav nav-tabs">
      <li id="smnu1" class="active"><a href="#legendsum" onclick="$('.box_ok').show();$('#smnu1').addClass('active'); $('#smnu2').removeClass('active');return false;">$strreportdetail </a></li>
      <li id="smnu2" ><a href="#legendsum" onclick="$('.box_ok').hide();$('#smnu2').addClass('active'); $('#smnu1').removeClass('active');return false;">$strgroupnooptimal</a></li>
    </ul>    
    <div class=""container-fluid" style="padding-left:20px;display:;">
      <div class="row inline-block-row" id="summary"></div>
    </div>
  </div>
</fieldset>

    
<div style="text-align:center;margin:15px 50px 0px;border-top:1px solid black;padding-top:15px;">
  <button type="button" onclick="$('#createGroupsForm').slideDown(300);" class="creategroups">$strcreategroups</button>
  <div style="display:none" id="createGroupsForm"><p>$strconfirmgroupbuilding</p>
    <table class="mod-teamup-table" style="margin:auto;width:100%;background:transparent;margin-bottom:5px;">
      <tr id="nameofgroup">
        <td style="width:50%;text-align:left;padding-left:3px;"><label for="groupingName">$strgroupingname</label></td>
        <td style="padding-right:5px;"><input style="height:26px;text-align:left;padding-left:3px;margin-top:8px;" type="text" class="form-control" id="groupingName"></td>
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
    <button type="button" onclick="$('#createGroupsForm').slideUp(300);">$strcancel</button>&nbsp
    <button type="button" id="creategroups">$strok</button>
  </div>
</div>
HTML;
}

echo $OUTPUT->footer();