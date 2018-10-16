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
class report_ncmzoom_user_filter extends moodleform {
    /**
     * Form definition method.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker ();
        $zoomtypes = array (
                             "" => "All",
                             "1" => "Basic Accounts",
                             "2" => "Pro Accounts"
                           );
        $mform->addElement ( 'header', 'displayinfo', get_string ( 'filter', 'report_ncmzoom' ) );
        $mform->addElement ( 'text', 'name', get_string ( 'ncmzoom_user', 'report_ncmzoom' ), '' );
        $mform->setType ( 'name', PARAM_TEXT );
        $mform->setDefault('name', $this->_customdata ['name']);
        $mform->addElement ( 'text', 'username', get_string ( 'ncmzoom_username', 'report_ncmzoom' ), '' );
        $mform->setType ( 'username', PARAM_TEXT );
        $mform->setDefault('username', $this->_customdata ['username']);
        // Comment $mform->addElement ( 'select', 'zoomtype', get_string ( 'ncmzoom_zoomtype', 'report_ncmzoom' ), $zoomtypes );
        // Comment$mform->setDefault('zoomtype', $this->_customdata ['zoomtype']);
        $buttonarray = array ();
        $buttonarray [] = $mform->createElement ( 'submit', 'filterbutton', get_string ( 'filter', 'report_ncmzoom' ) );
        $buttonarray [] = $mform->createElement ( 'button', 'clearbutton', get_string ( 'clear', 'report_ncmzoom' ) );
        $mform->addGroup ( $buttonarray, 'filterbuttons', '', array (
                                                                      ' '
                                                                    ), false );
        $mform->setType('course', PARAM_ALPHANUMEXT);
    }
}