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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/report/ncmzoom/locallib.php');
require_once($CFG->dirroot . '/report/ncmzoom/classes/forms/record_filter.php');
$PAGE->requires->js_call_amd('report_ncmzoom/report', 'initialise');
$courseid = optional_param ( 'course', null, PARAM_INT );
$category = optional_param ( 'category', null, PARAM_INT );
$groupid = optional_param ( 'ncmzoomgroup', null, PARAM_INT );
$meetingname = optional_param ( 'ncmzoommeetingname', null, PARAM_TEXT );
$meetingnumber = optional_param ( 'ncmzoommeetingnumber', null, PARAM_TEXT );
$meetingnumber = str_replace("-", "", $meetingnumber);
$meetinghost = optional_param ( 'ncmzoommeetinghost', null, PARAM_TEXT );
$recordingstatus = optional_param ( 'recordstatus', 0, PARAM_TEXT );
$page = optional_param ( 'page', 0, PARAM_INT );
$sort = optional_param ( 'sort', '', PARAM_ALPHANUM );
$dir = optional_param ( 'dir', 'ASC', PARAM_ALPHA );
$formsubmit = optional_param ( 'formsubmit', 0, PARAM_INT );
$enabledatefilter = optional_param ( 'enabledatefilter', 0, PARAM_INT );
if ($formsubmit == 1 && $enabledatefilter == 1) {
    $recordfrom = required_param_array( 'recordfrom', PARAM_TEXT );
    $recordto = required_param_array ( 'recordto', PARAM_TEXT );
    $recordfrom = $recordfrom['day'].'-'.$recordfrom['month'].'-'.$recordfrom['year'];
    $recordto = $recordto['day'].'-'.$recordto['month'].'-'.$recordto['year'];
} else {
    $recordfrom = optional_param ( 'recordfrom', '', PARAM_TEXT );
    $recordto = optional_param ( 'recordto', '', PARAM_TEXT );
}
if (empty ( $courseid ) && empty ($category)) {
    admin_externalpage_setup ( 'reportncmzoom', '', null, '', array ( 'pagelayout' => 'report' ) );
} else {
    if (!empty ($category)) {
        $context = context_course::instance ( $category );
        $PAGE->set_context ( $context );
        require_capability ( 'report/ncmzoom:viewzoomactivities', $context );
        $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/activityreport.php', array (
                                                                                'category' => $category
                                                                                ) ) );
        $catobj = $DB->get_record ( 'course_categories', array (
                                                                  'id' => $category
                                                                ), '*', MUST_EXIST );
        $PAGE->set_pagelayout ( 'report' );
        $PAGE->set_title ( $catobj->name . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
        $PAGE->set_heading ( $catobj->name . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
    } else {
        if (!empty ($courseid)) {
            $context = context_course::instance ( $courseid );
            require_capability ( 'report/ncmzoom:viewrecordings', $context );
            $course = $DB->get_record ( 'course', array ( 'id' => $courseid ), '*', MUST_EXIST );
            require_login ( $course, false );
            $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/recorddetails.php', array ( 'course' => $courseid ) ) );
            if ( !empty( $course->category) &&  $course->category != 0 ) {
                $catobj = $DB->get_record ( 'course_categories', array ( 'id' => $course->category ), '*', MUST_EXIST );
                $category = $catobj->id;
            }
            $PAGE->set_pagelayout ( 'report' );
            $PAGE->set_title ( $course->shortname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
            $PAGE->set_heading ( $course->fullname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
        }
    }
}
// Create the filter form for the table.
$filtersection = new report_ncmzoom_filter_record_form ( null, array (
  'courseid' => $courseid,
  'category' => $category,
  'meetingname' => $meetingname,
  'meetingnumber' => $meetingnumber,
  'meetinghost' => $meetinghost,
  'recordingstatus' => $recordingstatus,
  'recordfrom' => $recordfrom,
  'recordto' => $recordto,
  'enabledatefilter' => $enabledatefilter
) );


// Output.
$renderer = $PAGE->get_renderer ( 'report_ncmzoom' );
echo $renderer->render_ncmzoom_recordings ( $filtersection, $category, $courseid, $groupid,
                                            $meetingname, $meetingnumber, $meetinghost,
                                            $recordingstatus, $enabledatefilter, $recordfrom, $recordto, $page, $sort, $dir );