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
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('../locallib.php');

global $DB;
$cat = $_GET ['category'];
// Get All the courses under each sub category.
$courses = getallcourses ($cat);
foreach ($courses as $index => $course) {
    echo "<option value='" . $index . "'>" . $course . "</option>";
}