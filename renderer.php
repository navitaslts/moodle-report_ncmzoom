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
 * Event report renderer.
 *
 * @package report_ncmzoom
 * @copyright Dasu Gunathunga
 * @license
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
class report_ncmzoom_renderer extends plugin_renderer_base {
    public $perpage = 20;
    public function render_ncmzoom_activities($form, $category, $courseid,
                                           $groupid, $meetingname, $meetingnumber, $meetinghost, $page, $sort, $dir) {
        global $PAGE;
        if ($sort == "") {
            $columndir = "ASC";
        }
        $columns = array ('sortcategory', 'sortcourse', 'sortgroup', 'sortname', 'sortmeetingid',
                          'sorthost', 'sortalternativehost');
        foreach ($columns as $column) {
            $prefix = 'sort';
            if ($sort == strtolower ( substr ( $column, strlen ( $prefix ) ) )) {
                $columndir = $dir == "ASC" ? "DESC" : "ASC";
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
                $columnicon = "<img class='iconsort' src=\"" . $this->output->pix_url ( 't/' . $columnicon ) . "\" alt=\"\" />";
            } else {
                $columndir = "ASC";
                $columnicon = "";
            }
            $heading = substr ( $column, strlen ( $prefix ) );
            if ($heading == "name") {
                $heading = "Activity Name";
            }
            if ($heading == "meetingid") {
                $heading = "Meeting ID";
            }
            if ($heading == "host") {
                $heading = "Host Name";
            }
            $colurl = new moodle_url ( 'activityreport.php', array ('category' => $category,
                          'course' => $courseid, 'ncmzoommeetingname' => $meetingname,
                          'ncmzoommeetingnumber' => $meetingnumber, 'ncmzoommeetinghost' => $meetinghost,
                          'sort' => strtolower ( substr (
                        $column, strlen ( $prefix ) ) ), 'dir' => $columndir ) );
            $$column = "<a href=\"".$colurl."\">" . ucfirst($heading) . "</a>$columnicon";
        }
        $title = get_string ( 'pluginname', 'report_ncmzoom' );
        // Header.
        $html = $this->output->header ();
        $html .= $this->output->heading ( $title );
        $baseurl = new moodle_url ( 'activityreport.php', array ('category' => $category,
                                        'course' => $courseid, 'ncmzoommeetingname' => $meetingname,
                                        'ncmzoommeetingnumber' => $meetingnumber,
                                        'ncmzoommeetinghost' => $meetinghost, 'sort' => $sort, 'dir' => $dir ) );
        if ($courseid != 0) {
            $context = context_course::instance ( $courseid );
        } else {
            if ($category != 0) {
                $context = context_coursecat::instance ($category);
            } else {
                $context = context_system::instance ();
            }
        }
        $html .= getNavigation ('activity', $context, $courseid, $category);
        $html .= "<h4 style = \"margin: 30px 0px 10px 0px;\">Activity Report</h4>";
        // Form.
        ob_start ();
        if (has_capability ( 'report/ncmzoom:viewfilters', $context )) {
            $form->display ();
        }
        $html .= ob_get_contents ();
        ob_end_clean ();
        $tabledata = array ();
        $PAGE->requires->strings_for_js ( array (), 'report_ncmzoom' );
        $html .= html_writer::start_div ( 'report-ncmzoom-data-table', array ('id' => 'report-ncmzoom-table') );
        $html .= html_writer::end_div ();
        $table = new html_table ();
        $table->head = array ($sortcategory, $sortcourse, "Group", $sortname, $sortmeetingid, $sorthost, "Alternative Hosts");
        $values = getallzoommeetings ( $category, $courseid, $groupid, $meetingname, $meetingnumber,
                                       $meetinghost, $page, $this->perpage, $sort, $dir );
        $activitycount = gettotalcountallzoommeetings ( $category, $courseid,
                                                        $groupid, $meetingname, $meetingnumber, $meetinghost );
        $table->data = $values;
        $html .= html_writer::table ( $table );
        $html .= '<hr/>';
        $html .= $this->output->paging_bar ( $activitycount, $page, $this->perpage, $baseurl );
        $html .= $this->output->footer ();
        return $html;
    }
    /*
     *
     */
    public function render_ncmzoom_recordings($form, $category, $courseid, $groupid,
                                           $meetingname, $meetingnumber, $meetinghost, $recordingstatus,
                                           $enabledatefilter, $recordfrom, $recordto, $page, $sort, $dir) {
        global $PAGE;
        if ($sort == "") {
            $columndir = "ASC";
        }
        $columns = array ('sortcategory', 'sortcourse', 'sortgroup', 'sortname', 'sortmeetingid', 'sorthost',
                          'sortalternativehost', 'sortrequestedat', 'sortavailableat', 'sortfiletype', 'sortstatus', 'sortdisplay'
        );
        foreach ($columns as $column) {
            $prefix = 'sort';
            if ($sort == strtolower ( substr ( $column, strlen ( $prefix ) ) )) {
                $columndir = $dir == "ASC" ? "DESC" : "ASC";
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
                $columnicon = "<img class='iconsort' src=\"" . $this->output->pix_url ( 't/' . $columnicon ) . "\" alt=\"\" />";
            } else {
                $columndir = "ASC";
                $columnicon = "";
            }
            $heading = substr ( $column, strlen ( $prefix ) );
            if ($heading == "name") {
                $heading = "Activity Name";
            }
            if ($heading == "meetingid") {
                $heading = "Meeting ID";
            }
            if ($heading == "host") {
                $heading = "Host Name";
            }
            if ($heading == "starttime") {
                $heading = "Start Time";
            }
            if ($heading == "filetype") {
                $heading = "File Type";
            }
            if ($heading == "requestedat") {
                $heading = "Requested At";
            }
            if ($heading == "availableat") {
                $heading = "Available Since";
            }
            $colurl = new moodle_url ( 'recorddetails.php', array ('category' => $category,
              'course' => $courseid, 'ncmzoommeetingname' => $meetingname,
              'ncmzoommeetingnumber' => $meetingnumber, 'ncmzoommeetinghost' => $meetinghost,
              'recordstatus' => $recordingstatus, 'enabledatefilter' => $enabledatefilter, 'recordfrom' => $recordfrom,
              'recordto' => $recordto, 'sort' => strtolower ( substr (
                $column, strlen ( $prefix ) ) ), 'dir' => $columndir ) );
            $$column = "<a href=\"".$colurl."\">" . ucfirst($heading) . "</a>$columnicon";
        }
        $baseurl = new moodle_url ( 'recorddetails.php', array ('category' => $category,
              'course' => $courseid, 'ncmzoommeetingname' => $meetingname,
              'ncmzoommeetingnumber' => $meetingnumber, 'ncmzoommeetinghost' => $meetinghost,
              'recordstatus' => $recordingstatus, 'enabledatefilter' => $enabledatefilter, 'recordfrom' => $recordfrom,
              'recordto' => $recordto, 'sort' => $sort, 'dir' => $dir ) );
        $title = get_string ( 'pluginname', 'report_ncmzoom' );
        // Header.
        $html = $this->output->header ();
        $html .= $this->output->heading ( $title );
        if ($courseid) {
            $context = context_course::instance ( $courseid );
        } else {
            if ($category != 0) {
                $context = context_coursecat::instance ($category);
            } else {
                $context = context_system::instance ();
            }
        }
        $html .= getNavigation ('recording', $context, $courseid, $category);
        $html .= "<h4 style = \"margin: 30px 0px 10px 0px;\">Zoom Recordings</h4>";
        // Form.
        ob_start ();
        if (has_capability ( 'report/ncmzoom:viewfilters', $context )) {
            $form->display ();
        }
        $html .= ob_get_contents ();
        ob_end_clean ();
        $PAGE->requires->strings_for_js ( array (), 'report_ncmzoom' );
        $html .= html_writer::start_div ( 'report-ncmzoom-data-table', array ('id' => 'report-ncmzoom-table') );
        $html .= html_writer::end_div ();
        $table = new html_table ();
        $table->head = array ($sortcategory, $sortcourse, "Group", $sortname,
                              $sortmeetingid, $sortrequestedat,
                               $sortavailableat, $sortfiletype, $sortstatus, $sortdisplay, 'Details' );
        $totalrecordings = gettotalcountzoomrecordings ( $category, $courseid, $groupid,
                                                         $meetingname, $meetingnumber, $meetinghost,
                                                         $recordingstatus, $recordfrom, $recordto );
        $values = getallzoomrecordings ( $category, $courseid, $groupid,
                                         $meetingname, $meetingnumber,
                                         $meetinghost, $recordingstatus, $recordfrom, $recordto,
                                         $page, $this->perpage, $sort, $dir );
        $table->data = $values;
        $html .= html_writer::table ( $table );
        $html .= '<hr/>';
        $html .= $this->output->paging_bar ( $totalrecordings, $page, $this->perpage, $baseurl );
        $html .= $this->output->footer ();
        return $html;
    }
    /*
     *
    */
    public function render_ncmzoom_users($form, $name, $username, $zoomemail, $zoomtype, $courseid, $category, $page, $sort, $dir) {
        global $PAGE;
        if ($courseid) {
            $context = context_course::instance ( $courseid );
        } else {
            if ($category != 0) {
                $context = context_coursecat::instance ($category);
            } else {
                $context = context_system::instance ();
            }
        }
        $title = get_string ( 'pluginname', 'report_ncmzoom' );
        $columns = array ("sortname", "sortusername", "sortemail", "sortmeetingid", "sorttype", "sortstatus");
        foreach ($columns as $column) {
            $prefix = 'sort';
            if ($sort == strtolower ( substr ( $column, strlen ( $prefix ) ) )) {
                $columndir = $dir == "ASC" ? "DESC" : "ASC";
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
                $columnicon = "<img class='iconsort' src=\"" . $this->output->pix_url ( 't/' . $columnicon ) . "\" alt=\"\" />";
            } else {
                $columndir = "ASC";
                $columnicon = "";
            }
            $heading = substr ( $column, strlen ( $prefix ) );
            if ($heading == "name") {
                $heading = "Name";
            }
            if ($heading == "meetingid") {
                $heading = "Personal Meeting ID";
            }
            if ($heading == "host") {
                $heading = "Host Name";
            }
            if ($heading == "username") {
                $heading = "Moodle Username";
            }
            if ($heading == "email") {
                $heading = "Zoom Email";
            }
            $colurl = new moodle_url ( 'moodleusers.php', array ('name' => $name, 'username' => $username,
                                       'zoomemail' => $zoomemail,
                                       'zoomtype' => $zoomtype,
                                       'sort' => strtolower ( substr (
                $column, strlen ( $prefix ) ) ), 'dir' => $columndir ) );
            $$column = "<a href=\"".$colurl."\">" . ucfirst($heading) . "</a>$columnicon";
        }
        // Header.
        $html = $this->output->header ();
        $html .= $this->output->heading ( $title );
        $html .= getNavigation ('users', $context, $courseid, $category);
        $html .= "<h4 style = \"margin: 30px 0px 10px 0px;\">Zoom Account Details</h4>";
        // Form.
        ob_start ();
        $form->display ();
        $html .= ob_get_contents ();
        ob_end_clean ();
        $PAGE->requires->strings_for_js ( array (), 'report_ncmzoom' );
        $html .= html_writer::start_div ( 'report-ncmzoom-data-table', array ('id' => 'report-ncmzoom-table') );
        $baseurl = new moodle_url ( 'moodleusers.php', array ('sort' => $sort, 'dir' => $dir,
                                                            'name' => $name,
                                                            'username' => $username,
                                                            'zoomemail' => $zoomemail, 'zoomtype' => $zoomtype ) );
        $html .= html_writer::end_div ();
        $table = new html_table ();
        $table->head = array ($sortname, $sortusername, "Zoom Email", "Personal Meeting ID", "Type", "Status");
        $data = getusers ( $name, $username, $zoomtype, $page, $this->perpage, $sort, $dir );
        $values = getusers ( $name, $username, $zoomtype, $page, $this->perpage, $sort, $dir );
        $table->data = $data['data'];
        $html .= html_writer::table ( $table );
        $html .= '<hr/>';
        $html .= $this->output->paging_bar ( $data['total'], $page, $this->perpage, $baseurl );
        $html .= $this->output->footer ();
        return $html;
    }
}
