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
 * @package ncmreport
 * @subpackage
 *
 * @copyright Dasu Gunathunga
 * @license
 *
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
// Depend on ncm Zoom plugin.
require_once($CFG->dirroot . '/mod/ncmzoom/classes/webservice.php');
/*
 *  Get All Recordings
 */
function getallzoomrecordings($category, $courseid, $groupid, $meetingname,
                              $meetingnumber, $meetinghost, $recordingstatus,
                              $recordfrom, $recordto, $page, $perpage, $sort, $dir) {
    global $CFG, $DB;
    $where = array ();
    if (!empty($category)) {
        $where [] = "{course}.category = '" . $category . "' ";
    }
    if (!empty($courseid)) {
        $where [] = "{ncmzoom}.course = '" . $courseid . "' ";
    }
    if (!empty($meetingname)) {
        $where [] = "{ncmzoom}.name like '%" . $meetingname . "%' ";
    }
    if (!empty($meetingnumber)) {
        $where [] = "{ncmzoom}.meeting_id = '" . $meetingnumber . "' ";
    }
    if (!empty($groupid)) {
        $where [] = "{course_modules}.availability like '%{\"type\":\"group\",\"id\":" . $groupid . "}%' ";
    }
    if (!empty($meetinghost)) {
        $where [] = "({user}.firstname like '%" . $meetinghost . "%' OR {user}.lastname like '%" . $meetinghost . "%') ";
    }
    if (!empty($recordingstatus)) {
        $where [] = "{ncmzoom_recordings}.status = '" . $recordingstatus . "' ";
    }
    if (!empty($recordfrom)) {
        $recordfrom .= " 0:00";
        $where [] = "{ncmzoom_recordings}.recording_start >= '" . strtotime ($recordfrom) . "' ";
    }
    if (!empty($recordto)) {
        $where [] = "{ncmzoom_recordings}.recording_start <= '" . strtotime('+1 day', strtotime ($recordto)) . "' ";
    }
    $sql = "SELECT {ncmzoom_recordings}.id, {ncmzoom}.name as zoomname, {course}.category,
               {ncmzoom}.course,
               {ncmzoom}.host_id, {ncmzoom}.option_alternative_hosts, {ncmzoom}.userid, {course}.shortname as coursename,
               {course_modules}.id as cmid, {course_modules}.availability, {user}.firstname, {user}.lastname,
               {ncmzoom_recordings}.meeting_id, {ncmzoom_recordings}.created_at, {ncmzoom_recordings}.updated_at,
               {ncmzoom_recordings}.file_id, {ncmzoom_recordings}.uuid,
               {ncmzoom_recordings}.file_type, {ncmzoom_recordings}.display, {ncmzoom_recordings}.status,
    		   {ncmzoom_recordings}.recording_start, {ncmzoom_recordings}.recording_end, {course_categories}.name as categoryname,
               {ncmzoom_recordings}.requested_at, {ncmzoom_recordings}.available_at
    		   FROM {ncmzoom_recordings}
    		   INNER JOIN {course} ON {course}.id = {ncmzoom_recordings}.course
    		   INNER JOIN {course_categories} ON {course}.category = {course_categories}.id
               INNER JOIN {course_modules} ON {course_modules}.course = {ncmzoom_recordings}.course AND
               {course_modules}.instance = {ncmzoom_recordings}.zoom_id
               INNER JOIN {modules} ON {modules}.id = {course_modules}.module AND {modules}.name = 'ncmzoom'
               LEFT JOIN {ncmzoom} ON {ncmzoom}.id = {ncmzoom_recordings}.zoom_id
               INNER JOIN {user} ON {user}.id = {ncmzoom}.userid";
    if (isset($where[0])) {
        $sql .= " WHERE ";
        $sql .= implode(' AND ', $where);
    }
    // Match table column names with sort.
    switch ($sort) {
        case 'category':
            $sort = "{course_categories}.name";
            break;
        case 'course':
            $sort = "{course}.fullname";
            break;
        case 'name':
            $sort = "{ncmzoom}.name";
            break;
        case 'meetingid':
            $sort = "{ncmzoom}.meeting_id";
            break;
        case 'host':
            $sort = "{user}.firstname";
            break;
        case 'starttime':
            $sort = "recording_start";
            break;
        case 'availableat':
            $sort = "available_at";
            break;
        case 'requestedat':
            $sort = "requested_at";
            break;
        case 'filetype':
            $sort = "file_type";
            break;
        default:
            break;
    }
    if ( !empty($sort) ) {
        $sql .= " ORDER BY ".$sort." " . $dir;
    } else {
        $sql .= " ORDER BY {ncmzoom_recordings}.available_at DESC";
    }
    $start = $page * $perpage;
    $sql .= " LIMIT " . $start . " , " . $perpage;
    $recordings = $DB->get_records_sql ( $sql );
    $recordingsarray = array ();
    foreach ($recordings as $index => $records) {
        $rec = new stdClass ();
        $caturl = new moodle_url ( '/course/index.php?categoryid=' . $records->category );
        if (!empty($records->category)) {
            $rec->category = '<a href="' . $caturl . '">' . $records->categoryname . '</a>';
        } else {
            $rec->category = "Unknown";
        }
        $courseurl = new moodle_url ( '/course/view.php?id=' . $records->course );
        if (!empty($records->coursename)) {
            $rec->course = '<a href="' . $courseurl . '">' . $records->coursename . "</a>";
        } else {
            $rec->course = "Unknown";
        }
        $availabilityarray = json_decode ( $records->availability );
        if ($availabilityarray) {
            $group = $DB->get_record ( 'groups', array ('id' => $availabilityarray->c [0]->id) );
            $rec->group = $group->name;
        } else {
            $rec->group = "";
        }
        $zoomurl = new moodle_url ( '/mod/ncmzoom/view.php?id=' . $records->cmid );
        if (!empty($records->zoomname)) {
            $rec->name = '<a href="' . $zoomurl . '">' . $records->zoomname . "</a><br/>"
                       .date ( 'd/m/Y H:i', $records->recording_start );
        } else {
            $rec->name = "Activity Not Found<br/>"
              .date ( 'd/m/Y H:i', $records->recording_start );
            $rec->group = "Unknown";
        }
        $rec->meetingnumber = join('-', str_split($records->meeting_id, 3));
        if (!empty($records->requested_at)) {
            $rec->request = date ( 'd/m/Y H:i', $records->requested_at );
        } else {
            $rec->request = "-";
        }
        if (!empty($records->available_at)) {
            $rec->available = date ( 'd/m/Y H:i', $records->available_at );
        } else {
            $rec->available = "-";
        }
        $rec->type = $records->file_type;
        if ($records->status == "R") {
            $rec->status = "Requested";
        } else {
            if ($records->status == "A") {
                $rec->status = "Available ";
            } else {
                $rec->status = $records->status;
            }
        }
        if ($records->display) {
            $rec->Visibility = "Shown";
        } else {
            $rec->Visibility = "Hidden";
        }
        $rec->moredetails = "<a href=\"#myModal".$records->id."\" role=\"button\" class=\"\" data-toggle=\"modal\">
                             More Details</a>";
        $rec->moredetails .= "<div id=\"myModal".$records->id."\" class=\"modal hide fade\" tabindex=\"-1\" role=\"dialog\"
                                   aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
                             <div class=\"modal-header\">
                             <h3 id=\"myModalLabel\">".($records->zoomname ? $records->zoomname : 'Unknown')."</h3>
                             </div>
                             <div class=\"modal-body\">
                             <table style = \"width:100%;\">
                             <tr>
                               <td>Name:</td>
                               <td>".($records->zoomname ? $records->zoomname : 'Unknown')."</td>
                             </tr>
                             <tr>
                               <td>Course:</td>
                               <td>".($records->coursename ? $records->coursename : 'Unknown')."</td>
                             </tr>
                             <tr>
                               <td>Category:</td>
                               <td>".(isset($cat->name) ? $cat->name : 'Unknown')."</td>
                             </tr>
                             <tr>
                               <td>Host Id:</td>
                               <td>".$records->host_id."</td>
                             </tr>
                             <tr>
                               <td>Meeting Id:</td>
                               <td>".$records->meeting_id."</td>
                             </tr>
                             <tr>
                               <td>Requested At:</td>
                               <td>";
        if (!empty($records->requested_at)) {
            $rec->moredetails .= date ( 'd/m/Y H:i', $records->requested_at );
        } else {
            $rec->moredetails .= "-";
        }
        $rec->moredetails .= "</td>
                             </tr>
                             <tr>
                               <td>Available Since:</td><td>";
        if (!empty($records->available_at)) {
            $rec->moredetails .= date ( 'd/m/Y H:i', $records->available_at );
        } else {
            $rec->moredetails .= "-";
        }
        $rec->moredetails .= "</td>
                             </tr>
                             <tr>
                               <td>File Id:</td>
                               <td>".$records->file_id."</td>
                             </tr>
                             <tr>
                               <td>File Type:</td>
                               <td>".$records->file_type."</td>
                             </tr>
                             <tr>
                               <td>UUID:</td>
                               <td>".$records->uuid."</td>
                             </tr>
                             <tr>
                               <td>Status:</td>
                               <td>".($records->status == "R" ? "Requested" : "Available")."</td>
                             </tr>
                             <tr>
                               <td>Display:</td>
                               <td>".($records->display ? "Shown" : "Hidden")."</td>
                             </tr>
                             <tr>
                               <td>Recording Start:</td>
                               <td>".($records->recording_start ? date ( 'd/m/Y H:i', $records->recording_start ) : '')."</td>
                             </tr>
                             <tr>
                               <td>Recording End:</td>
                               <td>".($records->recording_end ? date ( 'd/m/Y H:i', $records->recording_end ) : '')."</td>
                             </tr>
                             <tr>
                               <td>Created At:</td>
                               <td>".($records->created_at ? date ( 'd/m/Y H:i', $records->created_at ) : '')."</td>
                             </tr>
                             <tr>
                               <td>Updated At:</td>
                               <td>".($records->updated_at ? date ( 'd/m/Y H:i', $records->updated_at ) : '')."</td>
                             </tr>
                             </table>
                             </div>
                             <div class=\"modal-footer\">
                             <button class=\"btn\" data-dismiss=\"modal\" aria-hidden=\"true\">Close</button>
                             </div></div>";
        $recordingsarray [] = $rec;
    }
    return $recordingsarray;
}
/*
 * Get Total Number of Recordings
 */
function gettotalcountzoomrecordings($category, $courseid, $groupid, $meetingname, $meetingnumber, $meetinghost,
                                     $recordingstatus, $recordfrom, $recordto) {
    global $CFG, $DB;
    $where = array ();
    if (!empty($category)) {
        $where [] = "{course}.category = '" . $category . "' ";
    }
    if (!empty($courseid)) {
        $where [] = "{ncmzoom}.course = '" . $courseid . "' ";
    }
    if (!empty($meetingname)) {
        $where [] = "{ncmzoom}.name like '%" . $meetingname . "%' ";
    }
    if (!empty($meetingnumber)) {
        $where [] = "{ncmzoom}.meeting_id = '" . $meetingnumber . "' ";
    }
    if (!empty($groupid)) {
        $where [] = "{course_modules}.availability like '%{\"type\":\"group\",\"id\":" . $groupid . "}%' ";
    }
    if (!empty($meetinghost)) {
        $where [] = "{user}.firstname like '%" . $meetinghost . "%' OR {user}.lastname like '%" . $meetinghost . "%'";
    }
    if (!empty($recordingstatus)) {
        $where [] = "{ncmzoom_recordings}.status = '" . $recordingstatus . "' ";
    }
    if (!empty($recordfrom)) {
        $recordfrom = $recordfrom." 0:00";
        $where [] = "{ncmzoom_recordings}.recording_start >= '" . strtotime ($recordfrom) . "' ";
    }
    if (!empty($recordto)) {
        $recordto = $recordto." 23:55";
        $where [] = "{ncmzoom_recordings}.recording_end <= '" . strtotime($recordto) . "' ";
    }
    $sql = "SELECT count({ncmzoom_recordings}.id) as count FROM {ncmzoom_recordings}
    		   INNER JOIN {course} ON {course}.id = {ncmzoom_recordings}.course
			   INNER JOIN {course_categories} ON {course}.category = {course_categories}.id
               INNER JOIN {course_modules} ON {course_modules}.course = {ncmzoom_recordings}.course AND
               {course_modules}.instance = {ncmzoom_recordings}.zoom_id
               INNER JOIN {modules} ON {modules}.id = {course_modules}.module AND {modules}.name = 'ncmzoom'
               LEFT JOIN {ncmzoom} ON {ncmzoom}.id = {ncmzoom_recordings}.zoom_id
               INNER JOIN {user} ON {user}.id = {ncmzoom}.userid";
    if (isset($where[0])) {
        $sql .= " WHERE ";
        $sql .= implode(" AND ", $where);
    }
    $recordings = $DB->get_records_sql ( $sql );
    $result = array_shift($recordings);
    return $result->count;
}

function getusers($name, $username, $type, $page, $perpage, $sort, $dir) {
    global $CFG, $DB;
    $users = getmoodleeducatorsv2( $name, $username, $page, $perpage, $sort, $dir);
    $zoomusers = getallzoomusers();
    $data = array ();
    $count = 0;
    foreach ($users as $user) {
        $dataobj = new stdClass ();
        $dataobj->name = $user->firstname . " " . $user->lastname;
        $userprofile = new moodle_url ( '/user/profile.php?id=' . $user->userid );
        $dataobj->username = '<a href = "' . $userprofile . '">' . $user->username . '</a>';
        $ncmzoomid = $user->ncmzoomid;
        if (! empty ( $ncmzoomid )) {
            $key = strtolower( $ncmzoomid );
            if (isset($zoomusers[$key])) {
                $dataobj->zoomid = $ncmzoomid;
                $dataobj->meetingid = $zoomusers[$key]['pmi'];
                if ($zoomusers[$key]['type'] == 1) {
                    $dataobj->type = "Basic Account";
                    if ($zoomusers[$key]['verified'] == 1) {
                        $dataobj->verified = "Verified";
                    } else {
                        $dataobj->verified = "Not Verified";
                    }
                } else {
                    if ($zoomusers[$key]['type'] == 2) {
                        $dataobj->type = "Pro Account";
                        if ($zoomusers[$key]['verified'] == 1) {
                            $dataobj->verified = "Verified";
                        } else {
                            $dataobj->verified = "Not Verified";
                        }
                    } else {
                        if ($zoomusers[$key]['type'] == 3) {
                            $dataobj->type = "Corp user";
                            if ($zoomusers[$key]['verified'] == 1) {
                                $dataobj->verified = "Verified";
                            } else {
                                $dataobj->verified = "Not Verified";
                            }
                        }
                    }
                }
                $data [] = $dataobj;
            } else {
                $dataobj->zoomid = "<span style = 'color:red;'>No Zoom Account</span>";
                $dataobj->meetingid = "";
                $dataobj->type = "";
                $dataobj->verified = "";
                $data [] = $dataobj;
            }
        } else {
            $dataobj->ncmzoomid = "<span style = 'color:red;'>No Zoom Account</span>";
            $dataobj->meetingid = "";
            $dataobj->type = "";
            $dataobj->verified = "";
            $data [] = $dataobj;
        }
        $count++;
    }
    $result['total'] = $count;
    $result['data'] = $data;
    return $result;
}

/*
 * Get All Zoom activities
 */
function getallzoommeetings($category, $courseid, $groupid, $meetingname,
                            $meetingnumber, $meetinghost, $page, $perpage, $sort, $dir) {
    global $DB;
    $where = array ();
    if (!empty($category)) {
        $where [] = "{course}.category = '" . $category . "' ";
    }
    if (!empty($courseid)) {
        $where [] = "{ncmzoom}.course = '" . $courseid . "' ";
    }
    if (!empty($meetingname)) {
        $where [] = "{ncmzoom}.name like '%" . $meetingname . "%' ";
    }
    if (!empty($meetingnumber)) {
        $where [] = "{ncmzoom}.meeting_id = '" . $meetingnumber . "' ";
    }
    if (!empty($groupid)) {
        $where [] = "{course_modules}.availability like '%{\"type\":\"group\",\"id\":" . $groupid . "}%' ";
    }
    if (!empty($meetinghost)) {
        $meetinghost = trim($meetinghost);
        $wherestr = "{user}.firstname like '%" . $meetinghost . "%' OR {user}.lastname like '%" . $meetinghost . "%'";
        $wherestr .= " OR CONCAT({user}.firstname, ' ', {user}.lastname) like '%" . $meetinghost . "%' OR {user}.email
                       like '%" . $meetinghost . "%'";
        $where [] = $wherestr;
    }
    $module = $DB->get_record ( 'modules', array ('name' => 'ncmzoom') );
    $sql = "SELECT {ncmzoom}.id, {ncmzoom}.name as zoomname, {course}.category,
            {ncmzoom}.course, {ncmzoom}.host_id, {ncmzoom}.option_alternative_hosts,
            {ncmzoom}.userid, {course}.fullname as coursename, {course_modules}.id as cmid, {course_modules}.availability,
			{user}.firstname, {user}.lastname, {ncmzoom}.meeting_id, {course_categories}.name as categoryname
			FROM {ncmzoom}
			INNER JOIN {course} ON {course}.id = {ncmzoom}.course
			INNER JOIN {course_categories} ON {course}.category = {course_categories}.id
            INNER JOIN {course_modules} ON {course_modules}.course = {ncmzoom}.course
            AND {course_modules}.instance = {ncmzoom}.id
            INNER JOIN {modules} ON {modules}.id = {course_modules}.module AND {modules}.name = 'ncmzoom'
            LEFT JOIN {user} ON {user}.id = {ncmzoom}.userid";
    if (isset($where[0])) {
         $sql .= " WHERE ";
         $sql .= implode(" AND ", $where);
    }
    $sql .= " GROUP BY {ncmzoom}.id";
    // Match table column names with sort.
    switch ($sort) {
        case 'category':
            $sort = "{course_categories}.name";
            break;
        case 'course':
            $sort = "{course}.fullname";
            break;
        case 'name':
            $sort = "{ncmzoom}.name";
            break;
        case 'meetingid':
            $sort = "{ncmzoom}.meeting_id";
            break;
        case 'host':
            $sort = "{user}.firstname";
            break;
        default:
            break;
    }
    if ( !empty($sort) ) {
        $sql .= " ORDER BY ".$sort." " . $dir;
    }
    $start = $page * $perpage;
    $sql .= " LIMIT " . $start . " , " . $perpage;
    $zoommeetings = $DB->get_records_sql ( $sql );
    $meetings = array ();
    foreach ($zoommeetings as $index => $meeting) {
        $meetingobj = new stdClass ();
        $caturl = new moodle_url ( '/course/index.php?categoryid=' . $meeting->category );
        $meetingobj->category = '<a href="' . $caturl . '">' . $meeting->categoryname . '</a>';
        $courseurl = new moodle_url ( '/course/view.php?id=' . $meeting->course );
        $meetingobj->course = '<a href="' . $courseurl . '">' . $meeting->coursename . '</a>';
        $availabilityarray = json_decode ( $meeting->availability );
        if ($availabilityarray) {
            $group = $DB->get_record ( 'groups', array ('id' => $availabilityarray->c [0]->id) );
            $meetingobj->group = $group->name;
        } else {
            $meetingobj->group = "";
        }
        $zoomurl = new moodle_url ( '/mod/ncmzoom/view.php?id=' . $meeting->cmid );
        $meetingobj->name = '<a href="' . $zoomurl . '">' . $meeting->zoomname . '</a>';
        if ($meeting->meeting_id == 0 || $meeting->meeting_id == - 1) {
            $meetingobj->meeting_id = "";
        } else {
            $meetingobj->meeting_id = join('-', str_split($meeting->meeting_id, 3));
        }
        if ($meeting->userid) {
            $userprofile = new moodle_url ( '/user/profile.php?id=' . $meeting->userid );
            $meetingobj->host = '<a href = "' . $userprofile . '">' . $meeting->firstname . ' ' . $meeting->lastname . '</a>';
        } else {
            $meetingobj->host = "<span style=\"color:red;\">No Host</span>";
        }
        if (! empty ( trim ( $meeting->option_alternative_hosts ) )) {
            $meetingobj->alternative_hosts = getalternativehostsnames ( $meeting->option_alternative_hosts );
        } else {
            $meetingobj->alternative_hosts = "";
        }
        $meetings [] = $meetingobj;
    }
    return $meetings;
}

/*
 *  Get Total Number of Zoom Meetings
 */
function gettotalcountallzoommeetings($category, $courseid, $groupid, $meetingname,
                                         $meetingnumber, $meetinghost) {
    global $DB;
    $where = array ();
    if (!empty($category)) {
        $where [] = "{course}.category = '" . $category . "' ";
    }
    if (!empty($courseid)) {
        $where [] = "{ncmzoom}.course = '" . $courseid . "' ";
    }
    if (!empty($meetingname)) {
        $where [] = "{ncmzoom}.name like '%" . $meetingname . "%' ";
    }
    if (!empty($meetingnumber)) {
        $where [] = "{ncmzoom}.meeting_id = '" . $meetingnumber . "' ";
    }
    if (!empty($groupid)) {
        $where [] = "{course_modules}.availability like '%{\"type\":\"group\",\"id\":" . $groupid . "}%' ";
    }
    if (!empty($meetinghost)) {
        $where [] = "{user}.firstname like '%" . $meetinghost . "%' OR {user}.lastname like '%" . $meetinghost . "%'";
    }
    $module = $DB->get_record ( 'modules', array ('name' => 'ncmzoom') );
    $sql = "SELECT count({ncmzoom}.id) as count
			FROM {ncmzoom}
			INNER JOIN {course} ON {course}.id = {ncmzoom}.course
            INNER JOIN {course_categories} ON {course}.category = {course_categories}.id
            INNER JOIN {course_modules} ON {course_modules}.course = {ncmzoom}.course
            AND {course_modules}.instance = {ncmzoom}.id
            INNER JOIN {modules} ON {modules}.id = {course_modules}.module AND {modules}.name = 'ncmzoom'
            INNER JOIN {user} ON {user}.id = {ncmzoom}.userid";
    if (isset($where[0])) {
        $sql .= " WHERE ";
        $sql .= implode(" AND ", $where);
    }
    $zoommeetings = $DB->get_records_sql ( $sql );
    $result = array_shift($zoommeetings);
    return $result->count;
}

/*
 *  Get alternative host's name
 */
function getalternativehostsnames($alternavitehosts) {
    global $DB;
    $sql = "SELECT {user}.id, firstname, lastname FROM {user_info_data}
            INNER JOIN {user} ON {user}.id = {user_info_data}.userid
            WHERE data IN (?) LIMIT 1";
    $users = $DB->get_records_sql ( $sql, array ($alternavitehosts) );
    $alternavitehostsarray = array();
    foreach ($users as $user) {
        $url = new moodle_url ( '/user/profile.php?id=' . $user->id );
        $alternavitehostsarray [] = "<a href = '" . $url . "'>" . $user->firstname . " " . $user->lastname . "</a>";
    }
    return implode ( ", ", $alternavitehostsarray );
}

/*
 * Get number of Zoom meetings in the course
 */
function gettotalzoommeetings($courseid) {
    global $DB;
    $zoom  = $DB->get_records_sql ( "SELECT count(id) as count FROM {ncmzoom}" );
    $result = array_shift($zoom);
    $numberofzoommeetings = $result->count;
    $rec = new stdClass ();
    $rec->name = "Scheduled Zoom meetings:";
    $rec->val = $numberofzoommeetings;
    return $rec;
}

/*
 * Get Number of recorded Zoom meetings
 */
function gettotalrecordedmeetings($courseid) {
    global $DB;
    $sql = "SELECT count({ncmzoom_recordings}.id) as count
            FROM {ncmzoom_recordings}
            INNER JOIN {ncmzoom} ON {ncmzoom}.id = {ncmzoom_recordings}.zoom_id
            WHERE course = ?";
    $records = $DB->get_records_sql ( $sql, array ( $courseid ) );
    $results = array_shift($records);
    $numberofrecordings = $results->count;
    $rec = new stdClass ();
    $rec->name = "Recorded Zoom meetings:";
    $rec->val = $numberofrecordings;
    return $rec;
}

/*
 * Get all the groups in a course
 */
function getallgroups($courseid) {
    global $DB;
    $groups = $DB->get_records ( 'groups', array ( 'courseid' => $courseid ) );
    $options = array ();
    $options [''] = 'All';
    foreach ($groups as $index => $group) {
        $options [$group->id] = $group->name;
    }
    return $options;
}

function getcategory($course) {
    global $DB;
    $cat = $DB->get_record ( 'course', array ('id' => $course ), 'category' );
    if (isset ( $cat->category )) {
        return $cat->category;
    } else {
        return "";
    }
}

/*
 * Get all meetings in a course
 */
function getallmeetings($courseid) {
    global $DB;
    $zoommeetings = $DB->get_records ( 'ncmzoom', array ('course' => $courseid ) );
    $meetingobjs = array ();
    foreach ($zoommeetings as $index => $zoom) {
        $meeting = new stdClass ();
        // Get group name.
        // Get zoom instance id.
        $module = $DB->get_record ( 'modules', array ( 'name' => 'ncmzoom' ) );
        $sql = "SELECT availability FROM {course_modules} WHERE
				  course = ? AND instance = ? AND module = ?";
        $availabilityobj = $DB->get_record_sql ( $sql, array ( $courseid, $zoom->id, $module->id ) );
        $availabilityarray = json_decode ( $availabilityobj->availability );
        if ($availabilityarray) {
            $group = $DB->get_record ( 'groups', array ( 'id' => $availabilityarray->c [0]->id ) );
            $meeting->group = $group->name;
        } else {
            $meeting->group = "";
        }
        $meeting->name = $zoom->name;
        $meeting->meeting_id = join('-', str_split($zoom->meeting_id, 3));
        if ($zoom->userid) {
            $userfullname = $DB->get_record ( 'user', array ( 'id' => $zoom->userid ), 'firstname, lastname' );
            $meeting->host = $userfullname->firstname . ' ' . $userfullname->lastname;
        } else {
            $meeting->host = "<span style=\"color:red;\">No Host</span>";
        }
        $meeting->option_alternative_hosts = $zoom->option_alternative_hosts;
        $meetingobjs [] = $meeting;
    }
    return $meetingobjs;
}

function getallzoomusers() {
    $service = new mod_ncmzoom_webservice ();
    $pagesize = 300;
    $url = 'user/list';
    $pagenumber = 1;
    $firstiteration = 1;
    $pagecount = 1;
    $zoomusers = array();
    for ($i = 0; $i < $pagecount; $i ++) {
        $parameters = array ('page_size' => $pagesize, 'page_number' => $pagenumber);
        $pagenumber ++;
        try {
            $service->make_call ( $url, $parameters );
        } catch ( moodle_exception $e ) {
            global $CFG;
            require_once($CFG->dirroot . '/mod/ncmzoom/lib.php');
            if (! ncmzoom_is_user_not_found_error ( $e->getMessage () )) {
                return false;
            }
        }
        $zoom = $service->lastresponse;
        $totalnumberofrecords = $zoom->total_records;
        $pagecount = $zoom->page_count;
        foreach ($zoom->users as $user) {
            $zoomusers[strtolower($user->email)] = array(
            'pmi' => $user->pmi,
            'type' => $user->type,
            'verified' => $user->verified );
        }
    }
    // Get all the pending user.
    $url = 'user/pending';
    $pagesize = 300;
    $pagenumber = 1;
    $firstiteration = 1;
    $pagecount = 1;
    for ($i = 0; $i < $pagecount; $i ++) {
        $parameters = array ('page_size' => $pagesize, 'page_number' => $pagenumber );
        $pagenumber ++;
        try {
            $service->make_call ( $url, $parameters );
        } catch ( moodle_exception $e ) {
            global $CFG;
            require_once($CFG->dirroot . '/mod/ncmzoom/lib.php');
            if (! ncmzoom_is_user_not_found_error ( $e->getMessage () )) {
                return false;
            }
        }
        $zoom2 = $service->lastresponse;
        $pagecount = $zoom2->page_count;
        foreach ($zoom2->users as $user) {
            $zoomusers[strtolower($user->email)] = array('pmi' => $user->pmi, 'type' => $user->type,
                                                         'verified' => $user->verified );
        }
    }
    return $zoomusers;
}

function getallcategories() {
    global $DB;
    $sql = "SELECT p1.id as child,
                   p1.name,
                   p2.parent as parent2_id,
                   p2.name as parent2_name,
                   p3.parent as parent3_id,
                   p3.name as parent3_name,
                   p4.parent as parent4_id,
                   p4.name as parent4_name
                   FROM mdl_course_categories p1
                   LEFT JOIN mdl_course_categories p2 on p2.id = p1.parent
                   LEFT JOIN mdl_course_categories p3 on p3.id = p2.parent
                   LEFT JOIN mdl_course_categories p4 on p4.id = p3.parent
                   WHERE p1.visible = '1'
                   ORDER BY parent4_name, parent3_name, parent2_name";
    $results = $DB->get_records_sql ( $sql );
    $catarray = array ();
    $catarray [''] = 'All';
    foreach ($results as $cat) {
        $name = array();
        if (!empty ( $cat->parent4_name )) {
            $name[] = $cat->parent4_name;
        }
        if (!empty($cat->parent3_name)) {
            $name[] = $cat->parent3_name;
        }
        if (!empty($cat->parent2_name)) {
            $name[] = $cat->parent2_name;
        }
        if (!empty($cat->name)) {
            $name[] = $cat->name;
        }
        $name = implode( $name, ' / ' );
        $catarray [$cat->child] = $name;
    }
    return $catarray;
}

function getallcourses( $cat = 0 ) {
    global $DB;
    if ($cat == 0) {
        $courses = $DB->get_records ( 'course', array ('visible' => '1') );
    } else {
        $subcat = getallsubcategories($cat);
        $subcat = trim($subcat, ', ');
        $sql = "SELECT id, shortname FROM {course} WHERE visible = '1'";
        if (!empty($subcat)) {
            $sql .= " AND category IN ( ".trim($subcat, ', ').")";
        } else {
            $sql .= " AND category = ".$cat;
        }
        $courses = $DB->get_records_sql ( $sql );
    }
    $coursesarray = array ();
    $coursesarray [''] = 'All';
    foreach ($courses as $course) {
        $coursesarray [$course->id] = $course->shortname;
    }
    return $coursesarray;
}

function gettotalshownrecordings($cat) {
    global $DB;
    $sql = "SELECT count(distinct {ncmzoom_recordings}.id) as count, {ncmzoom_recordings}.display FROM {ncmzoom_recordings}
			   INNER JOIN {ncmzoom} ON {ncmzoom}.id={ncmzoom_recordings}.zoom_id
			   INNER JOIN {course} ON {ncmzoom}.course = {course}.id WHERE {ncmzoom_recordings}.file_type ='MP4'";
    if ($cat) {
        $sql .= " AND {course}.category = " . $cat;
    }
    $sql .= " GROUP BY {ncmzoom_recordings}.display";
    $results = $DB->get_records_sql ( $sql );
    $formatarray['display'] = 0;
    $formatarray['hidden'] = 0;
    foreach ($results as $r) {
        if ($r->display == 1) {
            $formatarray['display'] = $r->count;
        } else {
            $formatarray['hidden'] = $r->count;
        }
    }
    return $formatarray;
}

function getrecordings($cat = "", $course = array()) {
    global $DB;
    $sql = "SELECT {course}.id, COUNT(Distinct {ncmzoom_recordings}.id) As countrecordings,
              {course}.shortname FROM {ncmzoom_recordings}
			  LEFT JOIN {ncmzoom} ON {ncmzoom}.id = {ncmzoom_recordings}.zoom_id
			  LEFT JOIN {course} ON {course}.id = {ncmzoom}.course
			  WHERE {ncmzoom_recordings}.file_type = 'MP4' AND {course}.visible = '1'";
    if ($cat) {
        $sql .= " AND {course}.category = " . $cat;
    }
    $sql .= " GROUP BY {ncmzoom}.course ORDER BY {course}.id";
    $recordings = $DB->get_records_sql ( $sql );
    $recordingsarray = array_fill_keys ( $course, 0 );
    foreach ($recordings as $record) {
        $recordingsarray [$record->shortname] = $record->countrecordings;
    }
    return $recordingsarray;
}

function getalltopcategories() {
    global $DB;
    $categories = $DB->get_records ( 'course_categories', array ('visible' => '1', 'parent' => '0' ) );
    $catarray = array ();
    foreach ($categories as $cat) {
        $catarray [$cat->id] = $cat->name;
    }
    return $catarray;
}

function getallsubcategories($cat) {
    global $DB;
    $sql = "SELECT p1.id as child,
                   p1.name,
                   p2.parent as parent2_id,
                   p2.name as parent2_name,
                   p3.parent as parent3_id,
                   p3.name as parent3_name,
                   p4.parent as parent4_id,
                   p4.name as parent4_name
                   FROM mdl_course_categories p1
                   LEFT JOIN mdl_course_categories p2 on p2.id = p1.parent
                   LEFT JOIN mdl_course_categories p3 on p3.id = p2.parent
                   LEFT JOIN mdl_course_categories p4 on p4.id = p3.parent
                   WHERE p1.visible = '1' AND  ( p1.id = ".$cat." OR p2.id = ".$cat." OR p4.id = ".$cat." OR p3.id = ".$cat." ) ";
    $results = $DB->get_records_sql ( $sql );
    $subcategories = array_shift( $results );
    $ids = array();
    if (!empty($subcategories->child)) {
        $ids[] = $subcategories->child;
    }
    if (!empty($subcategories->parent2_id)) {
        $ids[] = $subcategories->parent2_id;
    }
    if (!empty($subcategories->parent3_id)) {
        $ids[] = $subcategories->parent3_id;
    }
    if (!empty($subcategories->parent3_id)) {
        $ids[] = $subcategories->parent4_id;
    }
    return implode(',', $ids);
}

function getmeetings($cat = "") {
    global $DB;
    $sql = "SELECT {course}.id, COUNT(Distinct {ncmzoom}.id) As countmeetings,
              {course}.shortname FROM {ncmzoom}
			  LEFT JOIN {course} ON {course}.id = {ncmzoom}.course
			  WHERE {course}.visible = '1'";
    if (!empty($cat)) {
        $sql .= " AND {course}.category = " . $cat;
    }
    $sql .= " GROUP BY {ncmzoom}.course ORDER BY {course}.id";
    $meetings = $DB->get_records_sql ( $sql );
    $meetingsarray = array ();
    foreach ($meetings as $meeting) {
        $meetingsarray [$meeting->shortname] = $meeting->countmeetings;
    }
    return $meetingsarray;
}

function getmeetingsincat() {
    global $DB;
    $topcat = getAllTopCategories ();
    $meetingsarray = array ();
    foreach ($topcat as $index => $cat) {
        $string = "";
        $totalcount = 0;
        $sub = getAllSubCategories ( $index );
        $string = $index . ", " . $sub;
        $sql = "SELECT COUNT(Distinct {ncmzoom}.id) As countmeetings, {course_categories}.name
                  FROM {ncmzoom}
			      LEFT JOIN {course} ON {course}.id = {ncmzoom}.course
			      LEFT JOIN {course_categories} ON {course_categories}.id = {course}.category
			      WHERE {course_categories}.visible = '1' AND {course_categories}.id
                  IN (" . rtrim ( $string, ', ' ) . ")";
        $sql .= " GROUP BY {course_categories}.name";
        $meetings = $DB->get_records_sql ( $sql );
        foreach ($meetings as $i => $m) {
            $totalcount = $totalcount + $m->countmeetings;
        }
        $meetingsarray [$cat] = $totalcount;
    }
    return $meetingsarray;
}

function getrecordingsincat() {
    global $DB;
    $topcat = getAllTopCategories ();
    $meetingsarray = array ();
    foreach ($topcat as $index => $cat) {
        $string = "";
        $totalcount = 0;
        $sub = getAllSubCategories ( $index );
        $string = $index . ", " . $sub;
        $sql = "SELECT COUNT(Distinct {ncmzoom_recordings}.id) As countmeetings, {course_categories}.name
              FROM {ncmzoom_recordings}
	          LEFT JOIN {ncmzoom} ON {ncmzoom}.id = {ncmzoom_recordings}.zoom_id
			  LEFT JOIN {course} ON {course}.id = {ncmzoom}.course
			  LEFT JOIN {course_categories} ON {course_categories}.id = {course}.category
			  WHERE {course_categories}.visible = '1' AND {course_categories}.id IN (" . rtrim ( $string, ', ' ) . ")
			  AND {ncmzoom_recordings}.file_type = 'MP4'";
        $sql .= " GROUP BY {course_categories}.name";
        $meetings = $DB->get_records_sql ( $sql );
        foreach ($meetings as $i => $m) {
            $totalcount = $totalcount + $m->countmeetings;
        }
        $meetingsarray [$cat] = $totalcount;
    }
    return $meetingsarray;
}

function getnavigation($page, $context, $courseid = '', $category = '') {
    if (empty($courseid)) {
        if (!empty($category)) {
            $link1 = new moodle_url ( 'index.php?category='.$category );
            $link2 = new moodle_url ( 'activityreport.php?category='.$category );
            $link3 = new moodle_url ( 'moodleusers.php?category='.$category );
            $link4 = new moodle_url ( 'recorddetails.php?category='.$category );
        } else {
            $link1 = new moodle_url ( 'index.php' );
            $link2 = new moodle_url ( 'activityreport.php');
            $link3 = new moodle_url ( 'moodleusers.php' );
            $link4 = new moodle_url ( 'recorddetails.php' );
        }
    } else {
        $link1 = new moodle_url ( 'index.php?course='.$courseid );
        $link2 = new moodle_url ( 'activityreport.php?course='.$courseid );
        $link3 = new moodle_url ( 'moodleusers.php?course='.$courseid );
        $link4 = new moodle_url ( 'recorddetails.php?course='.$courseid );
    }
    $html = "";
    $html .= html_writer::start_tag('ul', array('class' => 'nav nav-pills'));
    if (has_capability ( 'report/ncmzoom:viewstatistics', $context )) {
        if ($page == "index") {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item active'));
        } else {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item'));
        }
        $html .= "<a href = '" . $link1 . "'>Statistics</a>";
        $html .= html_writer::end_tag('li');
    }
    if (has_capability ( 'report/ncmzoom:viewzoomactivities', $context )) {
        if ($page == "activity") {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item active'));
        } else {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item'));
        }
        $html .= "<a href = '" . $link2 . "'>Activities</a>";
        $html .= html_writer::end_tag('li');
    }
    if (has_capability ( 'report/ncmzoom:viewzoomusers', $context )) {
        if ($page == "users") {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item active'));
        } else {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item'));
        }
        $html .= "<a href = '" . $link3 . "'>Zoom Accounts</a>";
        $html .= html_writer::end_tag('li');
    }
    if (has_capability ( 'report/ncmzoom:viewrecordings', $context )) {
        if ($page == "recording") {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item active'));
        } else {
            $html .= html_writer::start_tag('li', array('class' => 'nav-item'));
        }
        $html .= "<a href = '" . $link4 . "'>Recordings</a>";
        $html .= html_writer::end_tag('li');
    }
    $html .= html_writer::end_tag('ul');
    return $html;
}

function getrecordstatus() {
    global $DB;
    $sql = "SELECT DISTINCT status as status FROM {ncmzoom_recordings}";
    $results = $DB->get_records_sql ( $sql );
    $val = array_keys ( $results );
    $dropdown = array ();
    $dropdown [''] = "All";
    foreach ($val as $v) {
        if ($v == "A") {
            $dropdown [$v] = "Available";
        }
        if ($v == "R") {
            $dropdown [$v] = "Requested";
        }
    }
    return $dropdown;
}

function getmoodleeducatorsv2( $name = "", $username = "", $page, $perpage, $sort, $dir ) {
    global $DB;
    $config = get_config('mod_ncmzoom');
    $where = array ();
    $where [] = "rc.capability = ?";
    if (!empty($name)) {
        $where [] = "({user}.firstname like '%" . $name . "%' OR  {user}.lastname like '%" . $name . "%')";
    }
    if (!empty($username)) {
        $where [] = "{user}.username like '%" . $username . "%' ";
    }
    // Get all the context ids with capability.
    $sql = "SELECT DISTINCT rs.userid, {user}.username, {user}.firstname, {user}.lastname, extdata.data
            as ncmzoomid FROM {role_capabilities} rc
            INNER JOIN {role_assignments} rs on rs.roleid = rc.roleid
            INNER JOIN {user} ON {user}.id = rs.userid
            LEFT OUTER JOIN (
	        SELECT d.userid, d.data
            FROM {user_info_data} d
            INNER JOIN {user_info_field} f ON f.id = d.fieldid
            WHERE f.shortname = '". $config->cfzoomid."')
            AS extdata ON extdata.userid = rs.userid ";
    $addwhere = implode("AND ", $where);
    $sql .= "WHERE ". $addwhere;
    switch ($sort) {
        case 'name':
            $sort = "{user}.firstname";
            break;
        case 'username':
            $sort = "{user}.username";
            break;
    }
    if ( !empty($sort) ) {
        $sql .= " ORDER BY ".$sort." " . $dir;
    } else {
        $sql .= " ORDER BY {user}.firstname";
    }
    $start = $page * $perpage;
    $sql .= " LIMIT " . $start . " , " . $perpage;
    $results = $DB->get_records_sql ($sql, array('mod/ncmzoom:hostzoommeeting'));
    return $results;
}