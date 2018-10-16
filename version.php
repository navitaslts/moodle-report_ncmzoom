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
 * @copyright Nicolas Jourdain
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

$plugin->version = 2017103100; // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires = 2014051200; // Requires this Moodle version
$plugin->component = 'report_ncmzoom'; // Full name of the plugin (used for diagnostics).
$plugin->release = 'v3.1.0';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = array (
  'mod_ncmzoom' => ANY_VERSION
);