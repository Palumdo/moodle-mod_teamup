<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
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
 * This page export all the student answers in an HTML file compatible with Excel
 *
 * @package    mod_teamup fork of teambuilder (mod_teambuilder)
 * @copyright  UCLouvain
 * @author     UCLouvain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$id           = optional_param('id', 0, PARAM_INT);         // The course_module ID, or...
$instance     = optional_param('instance', 0, PARAM_INT);   // teamup instance ID.
$courseid       = optional_param('course', 0, PARAM_INT);   // teamup instance ID.

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'teamup');
    $teamup = $DB->get_record('teamup', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
  exit();
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


$sql = " 
SELECT * FROM (
(SELECT RAND() id,
       t11.name groupement,
       t9.name  groupe,
       FROM_UNIXTIME(t9.timecreated , '%Y-%m-%d %h:%i:%s') groupe_date,
       nom,
       lastname,
       firstname,
       t7.idnumber,
       email,
       responses 
 FROM (SELECT distinct concat(t4.lastname, ' ', t4.firstname, ' (', t4.email, ')') nom,
              GROUP_CONCAT(concat('\"', t5.answer, '\"')) AS responses ,
              t3.userid,
              t4.lastname, 
              t4.firstname, 
              t4.email,
              t4.idnumber
         FROM mdl_teamup_response t3,
              mdl_user            t4,
              mdl_teamup_answer   t5,
              mdl_teamup_question t6
        WHERE( t3.answerid IN (SELECT t2.id
                                 FROM mdl_teamup_answer t2
                                WHERE t2.question IN (SELECT id
                                                        FROM mdl_teamup_question t1
                                                       WHERE t1.builder = (SELECT id
                                                                             FROM mdl_teamup tx
                                                                            WHERE     course = :param1
                                                                              AND tx.id = (SELECT cm.instance
                                                                                             FROM mdl_course_modules cm
                                                                                              JOIN mdl_course c ON c.id = cm.course
                                                                                             WHERE cm.id = :param2
                                                                                           )
                                                                           )
                                                      )
                              )
              )
          AND t3.userid = t4.id
          AND t5.id     = t3.answerid
          AND t6.id     = t5.question
        GROUP BY nom
        ORDER BY t4.lastname, t4.firstname, t6.question)                      t7 
            LEFT JOIN (SELECT ta.* FROM mdl_groups_members ta, mdl_groups tb WHERE tb.id = ta.groupid AND tb.courseid = :param3) t10 ON t10.userid = t7.userid 
            LEFT JOIN (SELECT * FROM mdl_groups    WHERE courseid = :param4)  t9  ON t9.id            = t10.groupid 
            LEFT JOIN mdl_groupings_groups                                    t12 ON t12.groupid      = t10.groupid
            LEFT JOIN (SELECT * FROM mdl_groupings WHERE courseid = :param5)  t11 ON t12.groupingid   = t11.id 
  GROUP BY nom, groupement, groupe, groupe_date
  ORDER BY groupement, groupe, groupe_date, nom) 
UNION
(SELECT RAND() id,
       t11.name groupement,
       t9.name  groupe,
       FROM_UNIXTIME(t9.timecreated , '%Y-%m-%d %h:%i:%s') groupe_date,
       nom,
       lastname, 
       firstname, 
       t7.idnumber,
       email,
       responses 
 FROM (SELECT distinct concat(t4.lastname, ' ', t4.firstname, ' (', t4.email, ')') nom,
              '' AS responses ,
              t4.id userid,
              t4.lastname, 
              t4.firstname, 
              t4.email,
              t4.idnumber
         FROM (SELECT t3.* FROM mdl_user t3, mdl_role_assignments t1,mdl_context t2 WHERE t1.contextid = t2.id AND t1.roleid = 5 AND t3.id = t1.userid AND t2.instanceid  = :param6) t4
        WHERE t4.id NOT IN (SELECT DISTINCT t3.userid
                           FROM mdl_teamup_response t3,
                                mdl_teamup_answer t5,
                                mdl_teamup_question t6
                          WHERE t6.builder = (SELECT id
                                                FROM mdl_teamup tx
                                               WHERE course = :param7
                                                 AND tx.id = (SELECT cm.instance
                                                                FROM mdl_course_modules cm
                                                                JOIN mdl_course c ON c.id = cm.course
                                                                WHERE cm.id = :param8
                                                              )
                                              )
                            AND t6.id = t5.question
                            AND t3.answerid = t5.id
                         )
        GROUP BY nom
        ORDER BY t4.lastname, t4.firstname)                    t7 
            LEFT JOIN (SELECT ta.* FROM mdl_groups_members ta, mdl_groups tb WHERE tb.id = ta.groupid AND tb.courseid = :param9) t10 ON t10.userid = t7.userid 
            LEFT JOIN (SELECT * FROM mdl_groups    WHERE courseid = :param10)  t9  ON t9.id            = t10.groupid 
            LEFT JOIN mdl_groupings_groups                                     t12 ON t12.groupid      = t10.groupid
            LEFT JOIN (SELECT * FROM mdl_groupings WHERE courseid = :param11)  t11 ON t12.groupingid   = t11.id 
  GROUP BY nom, groupement, groupe, groupe_date
  ORDER BY groupement, groupe, groupe_date, nom) 
) aaa ORDER BY groupement,groupe,groupe_date,nom
";  

$params = array('param1' => $courseid, 'param2' => $id, 'param3' => $courseid, 'param4' => $courseid, 'param5' => $courseid, 'param6' => $courseid, 'param7' => $courseid, 'param8' => $id, 'param9' => $courseid, 'param10' => $courseid, 'param11' => $courseid);
$result = $DB->get_records_sql($sql, $params);
    
$output = '<table class="table table-bordered">
      <tr>  
        <th>Groupement</th>  
        <th>Groupe</th>  
        <th>Creation</th>  
        <th>Prénom</th>  
        <th>Nom</th> 
        <th>NOMA</th>
        <th>Email</th>  
        <th>Response</th>  
      </tr>';
     
foreach ($result as $row) {
   $output .= '
      <tr>  
      <td>'.$row->groupement.'</td>  
        <td>'.$row->groupe.'</td>  
        <td>'.$row->groupe_date.'</td>  
        <td>'.$row->firstname.'</td> 
        <td>'.$row->lastname.'</td>
        <td>'.$row->idnumber.'</td>
        <td>'.$row->email.'</td>';  
        $array = explode(',', $row->responses);
        foreach($array as $value) {
          $output .= '<td>'.trim($value,"\"").'</td>';
        }         
        $output .= '</tr>';  
}
$output .= '</table>';

header('Content-Type: application/xls; charset=utf-8');
header('Content-Disposition: attachment; filename=Rapport-'.$courseid.'_'.$id.'-'.date("d-m-Y").'.xls');
echo $output;    
?>

