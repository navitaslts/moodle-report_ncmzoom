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
 * Provides the report plugin interface for the core
 *
 * @package report_overviewstats
 * @copyright Dasu Gunathunga
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation
 *         The navigation node to extend
 * @param stdClass $course
 *         The course to object for the report
 * @param stdClass $context
 *         The context of the course
 */
function report_ncmzoom_extend_navigation_course($navigation, $course, $context) {
    global $CFG;
    if (has_capability ( 'report/ncmzoom:viewzoomactivities', $context )) {
        $url = new moodle_url ( '/report/ncmzoom/activityreport.php', array ('course' => $course->id ) );
        $navigation->add ( get_string ( 'pluginname', 'report_ncmzoom' ), $url,
                           navigation_node::TYPE_SETTING, null, null, new pix_icon ( 'i/report', '' ) );
    }
}

function report_ncmzoom_extend_navigation_category_settings(navigation_node $parentnode, context_coursecat $context) {
    if (has_capability ( 'report/ncmzoom:viewzoomactivities', $context )) {
        $url = new moodle_url ( '/report/ncmzoom/activityreport.php', array ('category' => $context->instanceid) );
        $parentnode->add ( get_string ( 'pluginname', 'report_ncmzoom' ), $url,
        navigation_node::TYPE_SETTING, null, null, new pix_icon ( 'i/report', '' ) );
    }
}