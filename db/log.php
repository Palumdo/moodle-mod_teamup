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
 * Definition of log events for the teamup module.
 *
 * @package    mod_teamup fork of mod_teambuilder
 * @copyright  2012 -
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Modified by  Palumbo Dominique (UCLouvain)
 * Modifications
    teambuilder was replaced by teamup in the file (same structure)
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'teamup', 'action' => 'view', 'mtable' => 'teamup', 'field' => 'name'),
    array('module' => 'teamup', 'action' => 'view all', 'mtable' => 'teamup', 'field' => 'name'),
    array('module' => 'teamup', 'action' => 'delete answer', 'mtable' => 'teamup_response', 'field' => 'answerid'),
    array('module' => 'teamup', 'action' => 'add answer', 'mtable' => 'teamup_response', 'field' => 'answerid'),
    array('module' => 'teamup', 'action' => 'add several answers', 'mtable' => 'teamup_response', 'field' => 'answerid'),
);