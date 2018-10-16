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

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");
class report_ncmzoom_cat_filter extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $mform->disable_form_change_checker ();
        $componentarray = getallcategories ();
        $edulevelarray = getallcourses ();
        $mform->addElement ( 'header', 'displayinfo', get_string ( 'filter', 'report_ncmzoom' ) );
        $mform->addElement('hidden', 'course', $this->_customdata ['courseid']);
        $mform->setType('course', PARAM_ALPHANUMEXT);
        $catselect = $mform->addElement ( 'select', 'indexcategory',
                                           get_string ( 'ncmzoom_category', 'report_ncmzoom' ), $componentarray );
        $catselect->setSelected ( $this->_customdata ['cat'] );
    }
}