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
 * Lang strings for the teamup module.
 *
 * @package    mod_teamup
 * @copyright  UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addnewcriterion']            = '+ Add criterion';
$string['addanewquestion']            = 'Add a new question';
$string['addnewquestion']             = 'Add new Question';
$string['addnewsubcriterion']         = 'Add new subcriterion';
$string['addtogrouping']              = 'Add to grouping';
$string['all']                        = 'all';
$string['allstudents']                = 'All students';
$string['and']                        = 'AND';
$string['answertype']                 = 'Answer type';
$string['answers']                    = 'Answers';
$string['any']                        = 'any';
$string['assignrandomly']             = 'Assign Randomly';
$string['buildteams']                 = 'Groups Creation';
$string['confirmgroupbuilding']       = 'Are you sure you want to create your groups now ?';
$string['criterionquestion']          = 'At least one student who answered {question} :';
$string['creategroups']               = 'Create Groups inside Moodle';
$string['dontassigngrouptogrouping']  = 'Don\'t assign groups to a Grouping';
$string['import']                     = 'Import';
$string['importquestionsfrom']        = 'Import questions from';
$string['intro']                      = 'Introduction';
$string['intro_help']                 = 'The introduction to your Team up instance.';
$string['modulename']                 = 'TeamUp';
$string['modulenameplural']           = 'TeamUp';
$string['modulename_help']            = 'TeamUp is a tool to assign students to Moodle groups based on their answers to a series of questions that you specify.
The idea is to write some multiple choice questions eventually with restrictions, and to use the students answers to compose groups with 4 approaches :
* Aggregate : Group students with some attribute together in the same groups
* Distribute : Spread students with some attribute across all groups so that each group has about the same number of them
* Cluster : ensure that students with some attribute are not isolated in a group
* Balance : Ensure equal strength of groups based on some numeric score
The algorithm fills the groups so that the have all the same number of members.

Team Up is a fork of the Moodle plugin <a href=\"https://moodle.org/plugins/mod_teambuilder\" target=\"_blank\"> Team Builder </a> that we used as model to design our interface.
The Team Up repartition algorithm and his options is inspired of  the Open Source project <a href=\"https://www.groupeng.org/GroupENG\" target=\"_blank\"> GroupEng </a>. ';
$string['name']                       = 'Name';
$string['noeditingafteropentime']     = 'You cannot edit the questionnaire if it has already been opened.';
$string['none']                       = 'none';
$string['noneedtocomplete']           = 'You do not need to complete questionnaire.';
$string['notopen']                    = 'This questionnaire is not open.';
$string['numberofteams']              = 'Number of teams';
$string['or']                         = 'OR';
$string['pluginadministration']       = 'TeamUp administration';
$string['pluginname']                 = 'TeamUp';
$string['prefixteamnames']            = 'Prefix group names with Grouping Name';
$string['prioritize']                 = 'Prioritize';
$string['prioritizeequal']            = 'equal group numbers';
$string['prioritizemostcriteria']     = 'most criteria met';
$string['preview']                    = 'Preview';
$string['questionnaire']              = 'Questionnaire';
$string['resetteams']                 = 'Reset';
$string['savequestionnaire']          = 'Save Questionnaire';
$string['selectone']                  = 'Select one option';
$string['selectany']                  = 'Select any (or none) option';
$string['selectatleastone']           = 'Select one or more options';
$string['selecttwo']                  = 'Select two options';
$string['selectthree']                = 'Select three options';
$string['selectfour']                 = 'Select four options';
$string['selectfive']                 = 'Select five options';
$string['teamup']                     = 'Team Up';
$string['teamup:addinstance']         = 'Add a new Team Up module';
$string['teamup:build']               = 'Build groups from survey response';
$string['teamup:create']              = 'Create survey';
$string['teamup:respond']             = 'Respond to Questionnaire';
$string['unassignedtoteams']          = 'Unassigned to groups';
$string['distribute']                 = 'Distribute';
$string['aggregate']                  = 'Aggregate';
$string['cluster']                    = 'Cluster';
$string['balance']                    = 'Balance';
$string['replay']                     = 'Replay without reset';
$string['bidon']                      = 'fake';
$string['presentation']               = '<h3> Module overview </h3>
<p>
The purpose of the Team Up module is to allow the creation of groups, based on a multiple answer questions survey, with possible restrictions.

<p>
The first tab, <b>Questionnaire</b>, enables you to create the questions for students.<br>
<b>Preview questions</b>, the second tab, presents the questionnaire for students to answer. <br>
The last tab, <b>Groups creation</b>, enables you to launch the groups creation.

</p>

<p>
The groups creation takes place in two steps. The first one is a simulation.
During simulation, it is possible to modify criterias to reorder them and to move students manually from one group to another.
On the next step; the groups are actually created in Moodle. <br>
Do not forget to click the <button type=\"button\" class=\"creategroups\" style=\"font-size: 1.0em;\" id=\"\">Create Groups inside Moodle</button> to finalize the group creation.<br>
</p>

<p>
There are 4 operators to create groupes. <br>
There are four basic operators to create the groups. <br>
<table class="mod-teamup-table">
  <tr><td>Group similar individuals</td><td> = Form groups whose members are similar to defined criteria. Creation of homogeneous groups. Applied to discrete values, with no obligation whatsoever numerical.</td></tr>
  <tr><td>Disperse similar individuals</td><td> = Distribute qualifying students across groups. Applied to discrete values, with no obligation whatsoever numerical.</td></tr>
  <tr><td>Avoid minorities</td><td> = Divide students so that at least two students sharing a criterion are in the same group (especially for minorities). Applied to discrete values, with no obligation whatsoever numerical.</td></tr>
  <tr><td>Balancing Level</td><td> = Create groups that are \"right\", whose total forces are similar in all groups (usually based on academic results). Applied to numerical values ​​(continuous and discrete).</td></tr>
</table>
</p>

<p>
When you preview a group distribution, you may click on a student block to see its informations and answers in a tooltip. <br>
If one student does not have to enter in the group distribution, you may click on the X in regard of its name to remove him from the group preview.
</p>

<u>The action bar :</u><br>
Number of teams :<input id="nbteam" min="1" style="width:40px;height:21px;margin-top:5px;margin-right:5px;" value="31" type="number" disabled="">31 / 123(4)</span>&nbsp;<button type=\"button\" id=\"buildteams\" class="">
<strong>Preview</strong></button>&nbsp;<button type=\"button\" id=\"resetteams\" class=\"\">Reset</button>&nbsp;<button type=\"button\" id=\"prettify\" style=\"\">Optimize</button>&nbsp;<button type=\"button\" id=\"equalize\" style=\"\">Equalize</button>
<ul>
  <li> The number of teams fixes the number of students in each group. For example, 123 students in 31 groupes makes 4 individuals in groups. </li>
  <li>Preview : This button launches the group creation with the selected criterias. </li>
  <li>Reset : This button empties all groups and sets students in the part <b> non affected to groups</b>.</li>
  <li>Optimize : This button makes some switches in students distribution to improve the criterias. Succes is not guaranteed but you may repeat optimization several times.</li>
  <li>Equalize : Force to equalize the number of student in each groups. Sometimes necessary after optimization.</li>
</ul>
</p>
';
$string['createteams']                = 'Create the groups';
$string['save']                       = 'Save';
$string['prettify']                   = 'Optimize';
$string['abc']                        = 'Serie #';
$string['groupcreationsuccess']       = 'The groups were created with success.';
$string['analyzeclustercriterion']    = 'The number of students meeting these criteria is <b> {nbstudent} </b> distributed in <b> {nbteam} </b> teams';
$string['analyzeclusterwarning']      = '<br><span style="color:red;">Attention, it can not have two students in all groups with these criteria.</span>';
$string['analyzeclustersuccess']      = '<br>There may be two students in all groups with these criteria.';
$string['analyzeaggregatewarning']    = '<br><span style="color:{color};"> Criterion {answer}: <b> {nbstudent} </b> => They\'ll have probably {nbgroup} groups of {nbstudentgroup} students with {reste} students spread</span>';
$string['analyzeaggregatewarningOK']  = '<br><span style="color:{color};"> Criterion {answer}: <b> {nbstudent} </b> => They\'ll have probably {nbgroup} groups of {nbstudentgroup} students</span>';
$string['noanswer']                   = 'This student has not responded.';
$string['analyzedistributesuccess']   = 'No problem to distibute it in <b> {nbteam} </b> teams';
$string['analyzedistributewarning']   = '<span style=\"color:red;\">Attention problem to distibute it in <b> {nbteam} </b> teams</span>';
$string['analyzedistributecriterion'] = '<br> Criterion {answer} : <b>{nbstudent}</b>=> <b>{status}</b>';
$string['analyzebalancewarning']      = '<span style=\"color:red;\">The result is not numeric, choose an appropriate question, please.</span>';
$string['total']                      = 'Total';
$string['average']                    = 'Average';
$string['standarddeviation']          = 'Standard deviation';
$string['teamupsuccess']              = 'Successful groups distribution';
$string['teamupwarning']              = 'Distribution fail';
$string['averagewarning']             = 'The average is too far from the global average';
$string['averagesuccess']             = 'The average is quite close to the overall average';
$string['bornes']                     = 'Bounds';
$string['teamupsuccessnbr']           = 'Number of groups successfully completed';
$string['summary']                    = 'Summary';
$string['distributionmode']           = 'Distribution mode';
$string['distributelabel']            = 'Disperse similar individual';
$string['aggregatelabel']             = 'Group similar individuals';
$string['clusterlabel']               = 'Avoid minorities';
$string['balancelabel']               = 'Balance level';
$string['question']                   = 'Question';
$string['pleasewait']                 = 'Thank you for your patience';
$string['groupTitle']                 = 'Group';
$string['previewQuestion']            = 'Preview questions';
$string['nbGroupSuccess']             = 'Number of groups successfully completed';
$string['nbStudent']                  = 'Number of students';
$string['reportDetail']               = 'Detailed report';
$string['groupNoOptimal']             = 'Non-optimal groups';
$string['aggFilter']                  = 'Filtre on ';
$string['bankQuestion']               = 'Question bank';
$string['equalize']                   = 'Equalize';
$string['groupName']                  = 'Group names';
$string['groupSchemaName']            = 'Naming scheme for the groups';
$string['helpserie']                  = '<p>
                                          The group creation module can be used to create series. <br>
                                          Series are groups of students created with alphabetical order as the sole criterion. <br>
                                          These groups are prefixed by the term \"Series\" eg: \"Series 01\" <br>
                                          The usefulness of series groups is that they are used as filters on the list of students. <br>
                                          By default, you create groups on all students in the class. <br>
                                          Series allow to create them on a subgroup of particular students. <br>
                                          It\'s useful for big classes, among others. <br>
                                          It\'s possible to create series yourself regardless of the serial button. <br>
                                          When creating the group in Moodle, when the name of this group is requested. Start the group name by Series ex: \"Real Class Series\". <br>
                                          Cella allows you to reduce the number of participants in the course taking into account assistants or students who are actually not present. <br>
                                          Once the series is created, the serial button disappears. To make it reappear if necessary, you must delete all the series groups. <br>
                                          But, it\'s still possible to create them by prefixing the group name by Series. <br>
                                        </p>';
$string['helpserie_help']             = 'Series Concep';
$string['answersSubmitted']           = 'Your answers were submitted.';

$string['oneOption']                  = 'Select <strong>one</strong> of the following:';
$string['anyOption']                  = 'Select any (or none) of the following:';
$string['atleastoneOption']           = 'Select <strong>at least one</strong> of the following:';
$string['twoOption']                  = 'Select two of the following:';
$string['threeOption']                = 'Select three of the following:';
$string['fourOption']                 = 'Select four of the following:';
$string['fiveOption']                 = 'Select five of the following:';

$string['deleteAllRed']               = 'Delete all students without answer';
$string['keepAllRed']                 = 'Keep only students without answer';
$string['equalizeHelp']               = 'Force to equalize the number of students in each group. Sometimes necessary after optimization';
$string['prettifyHelp']               = 'This button makes some switches in students distribution to improve the criterias. Succes is not guaranteed but you may repeat optimization several times.';

$string['namingscheme_help']          = 'The at symbol (@) may be used to create groups with names containing letters. For example Group @ will generate groups named Group A, Group B, Group C, ...

The hash symbol (#) may be used to create groups with names containing numbers. For example Group # will generate groups named Group 1, Group 2, Group 3, ...

If you use a criterion to group similar individuals, you can bring up the associated option in the group name by using the "*" character, example of recommended group naming scheme: "Group # - *"
(Remember to set quite short options for this criterion to avoid too long group names.)';

$string['afterdate']                  = 'You will not be able to modify your questionnaire after this date.';
$string['closedate']                  = 'Close Date';
$string['opendate']                   = 'Open Date';
$string['updateanswer']               = 'Allow updating of answers';
$string['helperror']                  = 'You must specify a course_module ID or an instance ID';
$string['cannotupdate']               = 'You cannot update a teamup instance once it has opened.';
$string['idincorrect']                = 'Course Module ID was incorrect or is misconfigured';
$string['pleasenever']                = 'Please NEVER refresh this page with F5(similar) or the navigator refresh button. Instead, please, click on the activity name on the breadcrumb';

$string['privacy:metadata:teamup_response']           = 'Information about the user\'s responses on a given teamup activity';
$string['privacy:metadata:teamup_response:answerid']  = 'The answer the user chose.';
$string['privacy:metadata:teamup_response:userid']    = 'The user who made the response.';

$string['pleasequestion']              = 'Enter a question, please';
$string['pleaseatleastonequestion']    = 'Enter at least a question, please';
$string['jserror01']                   = 'Enter at least two answers, please';
$string['jserror02']                   = 'Enter at least four answers, please';
$string['jserror03']                   = 'Enter at least five answers, please';
$string['jserror04']                   = 'Enter at least six answers, please';
$string['saving']                      = 'Saving...';
$string['saved']                       = 'Saved!';
$string['exportexcel']                 = 'Download all answers in Excel';
