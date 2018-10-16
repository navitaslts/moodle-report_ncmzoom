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
require_once($CFG->dirroot . '/report/ncmzoom/classes/forms/user_filter.php');
$PAGE->requires->js_call_amd ( 'report_ncmzoom/report', 'initialise' );
$sort = optional_param ( 'sort', 'name', PARAM_ALPHANUM );
$name = optional_param ( 'name', null, PARAM_TEXT );
$username = optional_param ( 'username', null, PARAM_TEXT );
$zoomemail = optional_param ( 'zoomemail', null, PARAM_TEXT );
$zoomtype = optional_param ( 'zoomtype', null, PARAM_TEXT );
$page = optional_param ( 'page', 0, PARAM_INT );
$sort = optional_param ( 'sort', '', PARAM_ALPHANUM );
$dir = optional_param ( 'dir', 'ASC', PARAM_ALPHA );
$courseid = optional_param ( 'course', null, PARAM_INT );
$category = optional_param ( 'category', 0, PARAM_INT );
if (!empty($courseid)) {
    $context = context_course::instance ( $courseid );
    require_capability ( 'report/ncmzoom:viewzoomusers', $context );
    $course = $DB->get_record ( 'course', array ( 'id' => $courseid ), '*', MUST_EXIST );
    require_login ( $course, false );
    $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/moodleusers.php', array ( 'course' => $courseid ) ) );
    if ( !empty($course0) ) {
        $catobj = $DB->get_record ( 'course_categories', array ( 'id' => $course->category ), '*', MUST_EXIST );
        $category = $catobj->id;
    }
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
        $PAGE->set_url ( new moodle_url ( '/report/ncmzoom/moodleusers.php', array ( 'category' => $category ) ) );
    } else {
        require_login ();
        $context = context_system::instance ();
        $PAGE->set_context ( $context );
        admin_externalpage_setup ( 'reportncmzoom', '', null, '', array ( 'pagelayout' => 'report' ) );
    }
}

// Create the filter form for the table.
$filtersection = new report_ncmzoom_user_filter ( null, array ('name' => $name,
                                                               'username' => $username,
                                                               'zoomtype' => $zoomtype ) );
// Output.
$renderer = $PAGE->get_renderer ( 'report_ncmzoom' );
echo $renderer->render_ncmzoom_users ( $filtersection, $name, $username,
                                       $zoomemail, $zoomtype, $courseid, $category, $page, $sort, $dir);