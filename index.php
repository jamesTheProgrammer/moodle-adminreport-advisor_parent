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
 * Site wide advisor 
 *
 * @package    advisor report
 * @author     James Mergenthaler <jmergenthaler@edutech.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

echo '<LINK REL="StyleSheet" HREF="styles.css" TYPE="text/css" MEDIA="screen">';

require('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('DAO.php'); // db calls

$stype = "a";
$sorder = "asc";

if (isset($_REQUEST['sorder']) ) {
    $sorder = $_REQUEST['sorder'];
} else {
    $sorder= "a";
}

if (isset($_REQUEST['stype']) ) {
    $stype = $_REQUEST['stype'];
} else {
    $stype= "name";
}

if (empty($host_course)) {
    $hostid = $CFG->mnet_localhost_id;
    if (empty($id)) {
        $site = get_site();
        $id = $site->id;
    }
} else {
      list($hostid, $id) = explode('/', $host_course);
}

$PAGE->set_pagelayout('report');

if ($hostid == $CFG->mnet_localhost_id) {
    $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
} else {
     $course_stub       = $DB->get_record('mnet_log', array('hostid'=>$hostid, 'course'=>$id), '*', true);
     $course->id        = $id;
     $course->shortname = $course_stub->coursename;
     $course->fullname  = $course_stub->coursename;
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);

if ($hostid != $CFG->mnet_localhost_id || $course->id == SITEID) {
    admin_externalpage_setup('reportadvisor', '', null, '', array('pagelayout'=>'report'));
    echo $OUTPUT->header();
} else {
    $PAGE->set_title($course->shortname .': '. $strlogs);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
}

echo "<div id='div1'>";

echo $OUTPUT->heading(get_string('pageHeader', 'report_advisor'));

echo
'<table class="generaltable "  cellspacing="1" cellpadding="3" summary="Advisor report">'.
 '<tr>'.
   '<th class="header c1"  >' . get_string('sname', 'report_advisor').
   '</br> <a href="index.php?stype=n&sorder=a" title="sort a-z">a-z</a> | <a href="index.php?stype=n&sorder=d" title="sort z-a">z-a</a> '.
   '</th>'.
   '<th class="header c2"  > Student District'.
   '</br> <a href="index.php?stype=d&sorder=a" title="sort a-z">a-z</a> | <a href="index.php?stype=d&sorder=d" title="sort z-a">z-a</a>'.
  '</th>'.
  '<th class="header c3"  >' . get_string('advisor', 'report_advisor') . '</th>'.
  '<th class="header c4"  >' . get_string('district', 'report_advisor') . '</th>'.
  '</tr>';

$rowcnt = 0;
$students = getStudents($stype, $sorder);

foreach ($students as $rec) {
    $slname = $rec->lname;
    $sfname = $rec->fname;
    $uid = $rec->uid;
    $district = $rec->city;
    $profilelink = $CFG->wwwroot.'/user/profile.php?id='.$rec->uid;

    if ($rowcnt % 2 == 0) {
        $rtype = "rows";
    } else {
        $rtype = "rows2";
    }

    $rowcnt++;

    // see if there is an advisor account registered for the studentid
    $advisor = getAssignedAdvisor($uid);

    if (empty($advisor)) {
        $advisorname = "not assigned";
        $advisorcity = "...";

        echo
        '<td> <a href='.$profilelink.' target="_blank" title="view/assign role to user profile">'.$slname. ', ' .$sfname. '</a>	</td>'.
        '<td>'.$district.'</td>'.
        '<td>'.$advisorname.'</td>'.
        '<td>'.$advisorcity.'</td>';

    } else {
        foreach ($advisor as $i) {
            $advisorname = $i->afname .' '.$i->alname;
            $advisorcity = $i->acity;
            echo
            '<tr>'.
            '<td><a href='.$profilelink.' target="_blank" title="view/assign role to user profile">'.$slname.', '.$sfname.'</a></td>'.
            '<td>'.$district.'</td>'.
            '<td>'.$advisorname.'</td>'.
            '<td>'.$advisorcity.'</td>'.
            '</tr>';
        }// end foreach
    }// end if empty($advisor))

    echo '</tr>';
}// end foreach

echo '</table>';
echo "</div>";

echo "<div id='div2'>";
  echo "<h2>Things to do on this page.</h2>";

  echo "<span id='emph'>Assign an advisor or parent to a student</span>";
    echo "<p>";
    echo "<ol>";
    echo "<li>Click the students name. A new tab will open showing their profile.</li>";
    echo "<li>From the Settings menu, choose Roles|Assign roles relative to this user.</li>";
    echo "<li>Click the Advisor Role, search for the advisor account then assign it in the context of the user selected.</li>";
    echo "<li>Close this tab, return to the Student Advisor Report and refresh the browser.";
    echo "The advisor will now appear in the Assigned Advisor column and in the list below.</li>";
    echo "</ol>";
    echo "</p>";

    echo "<span id='emph'>Ensure the advisor account(s) can see their students </span>";
    echo "<p>";
    echo "<ol>";
    echo "<li>Click the name in the <span class='title'>Current accounts in Advisor role </span>list below and choose Settings";
    echo "| Login as <span class='smaller'>(note, you will need to log back in as yourself to verify changes.)</span></li>";
    echo "<li>From the account home page, ensure the Mentees block is visible. If the block is not visible,";
    echo "turn it on by clicking the Customize this page button, then choose Add a block | Mentees </li>";
    echo "<li>Edit the name of the block to something like 'District x or Mr. Jones' students </li>";
    echo "<li>If the advisor wishes to see the course content, enrole the account in the course in the Advisor in course role.";
    echo "This way the teacher will know the account is not a student, but an advisor.  You may need to create the Advisor in course role, base it on the student role.</li>";
    echo "<li>To see the students data in the course, click the students name, then the course name on the student profile screen,";
    echo "then Activity Reports from the Navigation block.</li>";
    echo "</ol>";
    echo "<a href='http://james-moodle.blogspot.com/2013/04/steps-to-setting-up-account-in-advisor.html' target='_blank'>More detailed instructions with pictures</a>";
    echo "</p>";

$advisoraccounts = getalladvisoraccounts();

echo "<h3 class='title'>Current accounts in Advisor role</h3>";

foreach ($advisoraccounts as $i) {
    $alname = $i->alname;
    $afname = $i->afname;
    $uid = $i->id;
    $aprofilelink = $CFG->wwwroot.'/user/profile.php?id='.$i->id;
    echo "<div id='advisorList'><a href=".$aprofilelink." target='_blank'>".$afname. " " .$alname."</a>, ".$i->acity."</br></div>";
}

echo "<span id='emph'>Remove an assigned advisor or parent from a student</span>";
echo "<p>";
echo "<ol>";
echo "<li>Click the students name. A new tab will open showing their profile.</li>";
echo "<li>From the Settings menu, choose Roles | Assign roles relative to this user.</li>";
echo "<li>Click the Advisor Role link, highlight the name of the advisor in the Existing users column </li>";
echo "<li>Click the Remove button.  Return to your original tab and refresh the browser.  The advisor will no longer appear in the Assigned Advisor column.</li>";
echo "</ol>";
echo "</p>";
echo "</div>";

echo $OUTPUT->footer();
