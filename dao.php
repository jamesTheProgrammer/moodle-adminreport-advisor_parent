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

/*
 * @author     James Mergenthaler jmergenthaler@edutech.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 // function expects a userid
function getassignedadvisor($sid) {
    global $DB;

    // dont forget the first column in the select list MUST provide a unique id - or your query will not return all the recs!!
    return
    $DB->get_records_sql
    ('SELECT ra.id, advisor.firstname AS afname, advisor.lastname AS alname, advisor.city AS acity FROM mdl_user advisor 
	JOIN mdl_role_assignments ra ON ra.userid = advisor.id JOIN mdl_role role ON role.id = ra.roleid JOIN mdl_context ctx ON ctx.id = ra.contextid 
	AND ctx.contextlevel = 30 JOIN mdl_user student ON student.id = ctx.instanceid WHERE ctx.instanceid = '.$sid.'');
}

function getalladvisoraccounts() {
    global $DB;

    // this query will return all accounts assigned in the advisor or parent role in your system
    return
    $DB->get_records_sql
    ('SELECT DISTINCT advisor.id, advisor.firstname AS afname, advisor.lastname AS alname, advisor.city AS acity FROM mdl_user advisor JOIN mdl_role_assignments ra ON ra.userid = advisor.id JOIN mdl_role role ON role.id = ra.roleid JOIN mdl_context ctx ON ctx.id = ra.contextid AND ctx.contextlevel = 30 JOIN mdl_user student ON student.id = ctx.instanceid');
}

//  this function has   " AND u.id NOT IN (3,199) "  as part of its WHERE clause - you can remove this or modify the ids to match your own if you need to omit cerain accounts from the list.
function getstudents($stype='n', $sarg = 'a') {
    global $DB;

    if ($sarg == "d" AND $stype == "n") {
        $stype = "ORDER BY u.lastname DESC";
    } else if ($sarg == "a" AND $stype == "n") {
        $stype = "ORDER BY u.lastname ASC";
    } else if ($sarg == "a" AND $stype == "d") {
        $stype = "ORDER BY u.city ASC";
    } else {
        $stype = "ORDER BY u.city DESC";
    }

    return
    $DB->get_records_sql
    ('SELECT u.id, u.firstname AS fname, u.lastname AS lname, u.id as uid, u.email AS email, u.city FROM mdl_user u JOIN mdl_role_assignments ra on ra.userid = u.id JOIN mdl_role r on r.id = ra.roleid WHERE r.name = "student" AND u.id NOT IN (3,199) '.$stype.'');
}
