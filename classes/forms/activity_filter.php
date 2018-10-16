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

defined ( 'MOODLE_INTERNAL' ) || die ();
require_once($CFG->libdir . '/formslib.php');
/**
 * Event list filter form.
 *
 * @package report_ncmzoom
 * @copyright Dasu Gunathunga
 * @license
 *
 */
class report_ncmzoom_activity_filter extends moodleform {
    /**
     * Form definition method.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker ();
        $componentarray = getAllCategories ();
        $course = $this->_customdata ['courseid'];
        if (empty ( $course )) {
            $cat = $this->_customdata ['category'];
        } else {
            $cat = getCategory ( $course );
        }
        $edulevelarray = getAllCourses ( $cat );
        $groupsarray = getallgroups ( $course );
        $mform->addElement ( 'header', 'displayinfo', get_string ( 'filter', 'report_ncmzoom' ) );
        $catselect = $mform->addElement ( 'select', 'category',
                                           get_string ( 'ncmzoom_category', 'report_ncmzoom' ),
                                           $componentarray );
        $catselect->setSelected ( $cat );
        $select = $mform->addElement ( 'select', 'course', get_string ( 'ncmzoom_course', 'report_ncmzoom' ), $edulevelarray );
        $select->setSelected ( $course );
        $mform->addElement ( 'select', 'ncmzoomgroup', get_string ( 'ncmzoom_group', 'report_ncmzoom' ), $groupsarray );
        $mform->addElement ( 'text', 'ncmzoommeetingname', get_string ( 'ncmzoom_meetingname', 'report_ncmzoom' ), '' );
        $mform->setType ( 'ncmzoommeetingname', PARAM_TEXT );
        $mform->setDefault('ncmzoommeetingname', $this->_customdata ['meetingname']);
        $mform->addElement ( 'text', 'ncmzoommeetingnumber', get_string ( 'ncmzoom_meetingnumber', 'report_ncmzoom' ), '' );
        $mform->setType ( 'ncmzoommeetingnumber', PARAM_ALPHANUMEXT );
        $mform->setDefault('ncmzoommeetingnumber', $this->_customdata ['meetingnumber']);
        $mform->addElement ( 'text', 'ncmzoommeetinghost', get_string ( 'ncmzoom_meetinghost', 'report_ncmzoom' ), '' );
        $mform->setType ( 'ncmzoommeetinghost', PARAM_TEXT );
        $mform->setDefault('ncmzoommeetinghost', $this->_customdata ['meetinghost']);
        $mform->addElement ( 'hidden', 'filter', 1 );
        $mform->setType ( 'filter', PARAM_ALPHANUMEXT );
        $buttonarray = array ();
        $buttonarray [] = $mform->createElement ( 'submit', 'filterbutton', get_string ( 'filter', 'report_ncmzoom' ) );
        $buttonarray [] = $mform->createElement ( 'button', 'clearbutton', get_string ( 'clear', 'report_ncmzoom' ) );
        $mform->addGroup ( $buttonarray, 'filterbuttons', '', array (' ' ), false );
    }
}