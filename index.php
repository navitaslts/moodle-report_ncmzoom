<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * Version info
 *
 * @package report
 * @subpackage ncmzoom
 * @copyright Dasu Gunathunga
 * @license
 *
 */
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/report/ncmzoom/locallib.php');
require_once($CFG->dirroot . '/report/ncmzoom/classes/forms/cat_filter.php');
$PAGE->requires->js_call_amd ( 'report_ncmzoom/report', 'initialise' );
$courseid = optional_param ( 'course', null, PARAM_INT );
$cat = optional_param ( 'indexcategory', 0, PARAM_INT );
$category = optional_param ( 'category', 0, PARAM_INT );
// $stat = compareZoomToMoodle ();
require_login ();
if (! is_null ( $courseid ) && $courseid != 0 ) {
    $context = context_course::instance ( $courseid );
    require_capability ( 'report/ncmzoom:viewstatistics', $context );
    $course = $DB->get_record ( 'course', array ( 'id' => $courseid ), '*', MUST_EXIST );
    require_login ( $course, false );
    $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/index.php', array ( 'course' => $courseid ) ) );
    $catobj = $DB->get_record ( 'course_categories', array ( 'id' => $course->category), '*', MUST_EXIST );
    $category = $catobj->id;
    $PAGE->set_pagelayout ( 'report' );
    $PAGE->set_title ( $course->shortname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
    $PAGE->set_heading ( $course->fullname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
} else {
    if ($category != 0) {
        $context = context_coursecat::instance ($category);
        $categoryobj = $DB->get_record ( 'course_categories', array ( 'id' => $category ), '*', MUST_EXIST );
        $PAGE->set_context ( $context );
        $PAGE->set_pagelayout ( 'report' );
        $PAGE->set_title ( $categoryobj->name . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
        $PAGE->set_heading ( $categoryobj->name . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
        $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/index.php', array ( 'category' => $category ) ) );
        $cat = $category;
    } else {
        $context = context_system::instance ();
        $PAGE->set_context ( $context );
        admin_externalpage_setup ( 'reportncmzoom', '', null, '', array ( 'pagelayout' => 'report' ) );
    }
}
echo $OUTPUT->header ();
echo $OUTPUT->box_start ( 'generalbox boxwidthwide' );
echo $OUTPUT->heading ( get_string ( 'pluginname', 'report_ncmzoom' ) );
echo '<p id="intro">', get_string ( 'intro', 'report_ncmzoom' ), '</p>';
echo $OUTPUT->box_end ();

// Link to the other page.
echo getNavigation ('index', $context, $courseid, $category);
echo "<h4 style = \"margin: 30px 0px 10px 0px;\">Zoom Users</h4>";
$tablezoomusers = new html_table ();
$stdzoomuser = new stdClass ();
$stdzoomuser->name = "Moodle users with capability 'mod/ncmzoom:hostzoommeeting' with valid Zoom account:";
$stdzoomuser->val = "<span class='badge'>N\A</span>";
$valueszoomuser [] = $stdzoomuser;
$stdzoomuser2 = new stdClass ();
$stdzoomuser2->name = "Moodle users with capability 'mod/ncmzoom:hostzoommeeting' with NO matching Zoom account:";
$stdzoomuser2->val = "<span class='badge'>N\A</span>";
$valueszoomuser [] = $stdzoomuser2;
$tablezoomusers->data = $valueszoomuser;
echo html_writer::table ( $tablezoomusers );
echo '<hr/>';
echo "<h4 style = \"margin: 30px 0px 10px 0px;\">Zoom meetings and recordings - all</h4>";
echo "<div style='float:left; width:50%;'>";
echo "<canvas id=\"meetings\"></canvas>";
echo "</div>";
echo "<div style='float:left; width:50%;'>";
echo "<canvas id=\"recordings\"></canvas>";
echo "</div>";
echo "<hr/>";
echo "<div id=\"catdiv\">";
echo "<h4 style = \"margin: 30px 0px 10px 0px;\">Category Statistics</h4>";
$filtersection = new report_ncmzoom_cat_filter ( null, array ( 'courseid' => $courseid, 'cat' => $category) );
$filtersection->display ();
$catstat = new html_table ();
$statobj1 = new stdClass ();
$statobj1->name = "Scheduled Zoom meetings:";
$statobj1->val = "<span class='badge'>".gettotalcountallzoommeetings ( $cat, "", "", "", "", "" )."</span>";
$valuesstat [] = $statobj1;
$statobj2 = new stdClass ();
$rec = gettotalshownrecordings ( $cat );
$statobj2->name = "Recorded Zoom meetings:";
$statobj2->val = "<span class='badge'>".($rec['display'] + $rec['hidden'])."</span>";
$valuesstat [] = $statobj2;
$statobj3 = new stdClass ();
$statobj3->name = "Shown Recordings:";
$statobj3->val = "<span class='badge'>".$rec['display']."</span>";
$valuesstat [] = $statobj3;
$statobj4 = new stdClass ();
$statobj4->name = "Hidden Recordings:";
$statobj4->val = "<span class='badge'>".$rec['hidden']."</span>";
$valuesstat [] = $statobj4;
$catstat->data = $valuesstat;
echo html_writer::table ( $catstat );
echo '<hr/>';
$meetings = getmeetings ( $cat );
$recordings = getrecordings ( $cat, array_keys ( $meetings ) );
echo "<canvas id=\"myChart\"></canvas>";
$colorarray = array('#A5D6A7', '#C5E1A5', '#E6EE9C', '#FFF59D', '#FFE082',
                    '#FFCC80', '#FFAB91', '#FFCDD2', '#F8BBD0', '#E1BEE7',
                    '#D1C4E9', '#C5CAE9', '#BBDEFB', '#B3E5FC', '#81C784',
                    '#AED581', '#DCE775', '#FFF176', '#FFD54F', '#FFB74D',
                    '#FF8A65', '#E57373', '#F06292', '#BA68C8', '#9575CD',
                    '#7986CB', '#64B5F6', '#4FC3F7', '#4DD0E1', '#4DB6AC',
                    '#43A047', '#7CB342', '#C0CA33', '#FDD835', '#FFB300',
                    '#FB8C00', '#F4511E', '#E53935', '#D81B60', '#8E24AA',
                    '#5E35B1', '#3949AB', '#1E88E5', '#039BE5', '#00ACC1',
                    '##00897B', '#388E3C', '#689F38', '#AFB42B', '#FBC02D');
$meetingincat = getmeetingsincat ();
$recordingsincat = getrecordingsincat ();
$PAGE->requires->js_call_amd ( 'report_ncmzoom/ncmzoomchart', 'initialise', array (
  array_keys ( $meetings ),
  array_values ( $meetings ),
  array_values ( $recordings ),
  array_keys ( $meetingincat ),
  array_values ( $meetingincat ),
  array_values ( $recordingsincat ),
  $colorarray,
  array_keys ( $recordingsincat )
) );
echo "<div>";
echo $OUTPUT->footer ();