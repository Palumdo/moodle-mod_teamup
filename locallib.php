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
 * Internal library of functions for teamup module.
 *
 * All the teamup specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_teamup
 * @copyright 2019 Palumbo Dominique
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * This creates new calendar events given close by $teamup.
 *
 * @param stdClass $teamup
 * @return void
 */
function teamup_set_events($teamup) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $teamup.
    if (!isset($teamup->coursemodule)) {
        $cm = get_coursemodule_from_instance('teamup', $teamup->id, $teamup->course);
        $teamup->coursemodule = $cm->id;
    }

    // teamup start calendar events.
    $event = new stdClass();
    $event->eventtype = TEAMUP_EVENT_TYPE_OPEN;
    // The teamup_EVENT_TYPE_OPEN event should only be an action event if no close time is specified.
    $event->type = empty($teamup->closed) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'teamup', 'instance' => $teamup->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($teamup->opened)) && ($teamup->opened > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarstart', 'teamup', $teamup->name);
            $event->description  = format_module_intro('teamup', $teamup, $teamup->coursemodule);
            $event->timestart    = $teamup->opened;
            $event->timesort     = $teamup->opened;
            $event->visible      = instance_is_visible('teamup', $teamup);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($teamup->opened)) && ($teamup->opened > 0)) {
            $event->name         = get_string('calendarstart', 'teamup', $teamup->name);
            $event->description  = format_module_intro('teamup', $teamup, $teamup->coursemodule);
            $event->courseid     = $teamup->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'teamup';
            $event->instance     = $teamup->id;
            $event->timestart    = $teamup->opened;
            $event->timesort     = $teamup->opened;
            $event->visible      = instance_is_visible('teamup', $teamup);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }

    // teamup end calendar events.
    $event = new stdClass();
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = TEAMUP_EVENT_TYPE_CLOSE;
    if ($event->id = $DB->get_field('event', 'id',
            array('modulename' => 'teamup', 'instance' => $teamup->id, 'eventtype' => $event->eventtype))) {
        if ((!empty($teamup->closed)) && ($teamup->closed > 0)) {
            // Calendar event exists so update it.
            $event->name         = get_string('calendarend', 'teamup', $teamup->name);
            $event->description  = format_module_intro('teamup', $teamup, $teamup->coursemodule);
            $event->timestart    = $teamup->closed;
            $event->timesort     = $teamup->closed;
            $event->visible      = instance_is_visible('teamup', $teamup);
            $event->timeduration = 0;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event, false);
        } else {
            // Calendar event is on longer needed.
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->delete();
        }
    } else {
        // Event doesn't exist so create one.
        if ((!empty($teamup->closed)) && ($teamup->closed > 0)) {
            $event->name         = get_string('calendarend', 'teamup', $teamup->name);
            $event->description  = format_module_intro('teamup', $teamup, $teamup->coursemodule);
            $event->courseid     = $teamup->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'teamup';
            $event->instance     = $teamup->id;
            $event->timestart    = $teamup->closed;
            $event->timesort     = $teamup->closed;
            $event->visible      = instance_is_visible('teamup', $teamup);
            $event->timeduration = 0;
            calendar_event::create($event, false);
        }
    }
}
