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
 *
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
require_once($CFG->dirroot . '/report/ncmzoom/classes/forms/activity_filter.php');

$PAGE->requires->js_call_amd ( 'report_ncmzoom/report', 'initialise' );
// Get filter data.
$courseid = optional_param ( 'course', null, PARAM_INT );
$category = optional_param ( 'category', null, PARAM_INT );
$groupid = optional_param ( 'ncmzoomgroup', null, PARAM_INT );
$meetingname = optional_param ( 'ncmzoommeetingname', null, PARAM_TEXT );
$meetingnumber = optional_param ( 'ncmzoommeetingnumber', null, PARAM_TEXT );
$meetingnumber = str_replace('-', '', $meetingnumber);
$meetinghost = optional_param ( 'ncmzoommeetinghost', null, PARAM_TEXT );
$page = optional_param ( 'page', 0, PARAM_INT );
$filter = optional_param ( 'filter', null, PARAM_INT );
$sort = optional_param ( 'sort', '', PARAM_ALPHANUM );
$dir = optional_param ( 'dir', 'ASC', PARAM_ALPHA );

if (empty ( $courseid ) && empty ($category)) {
    // Site level reports.
    admin_externalpage_setup ( 'reportncmzoom', '', null, '', array (
      'pagelayout' => 'report'
    ) );

} else {
    if (!empty($category)) {
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
        $context = context_course::instance ( $courseid );
        $PAGE->set_context ( $context );
        require_capability ( 'report/ncmzoom:viewzoomactivities', $context );
        $course = $DB->get_record ( 'course', array (
                                                      'id' => $courseid
                                                    ), '*', MUST_EXIST );
        require_login ( $course, false );
        $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/activityreport.php', array (
                                                                                'course' => $courseid
                                                                                ) ) );
        $PAGE->set_pagelayout ( 'report' );
        $PAGE->set_title ( $course->shortname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
        $PAGE->set_heading ( $course->fullname . ' - ' . get_string ( 'pluginname', 'report_ncmzoom' ) );
    }
}
$components = $edulevel = $crud = null;
// Create the filter form for the table.
$filtersection = new report_ncmzoom_activity_filter ( null, array (
  'courseid' => $courseid,
  'group' => $groupid,
  'category' => $category,
  'meetingname' => $meetingname,
  'meetingnumber' => $meetingnumber,
  'meetinghost' => $meetinghost
) );

// Output.
$renderer = $PAGE->get_renderer ( 'report_ncmzoom' );
echo $renderer->render_ncmzoom_activities ( $filtersection, $category, $courseid,
                                            $groupid, $meetingname, $meetingnumber,
                                            $meetinghost, $page, $sort, $dir );
