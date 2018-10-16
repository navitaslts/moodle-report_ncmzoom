/*
 * @package    report_ncmzoom
 * @copyright  2017 Dasu Gunathunga
 * @license    Navitas
 */
/**
 * @module report_ncmzoom
 */
/* jshint unused:false */
define([ 'jquery' ], function($) {
    return {
        initialise : function() {
            $(document).ready(function() {
                if($('#id_enabledatefilter').is(":checked")) {
                    $('#id_recordfrom_day').prop("disabled", false);
                    $('#id_recordto_day').prop("disabled", false);
                    $('#id_recordfrom_month').prop("disabled", false);
                    $('#id_recordto_month').prop("disabled", false);
                    $('#id_recordfrom_month').prop("disabled", false);
                    $('#id_recordfrom_year').prop("disabled", false);
                    $('#id_recordto_year').prop("disabled", false);
                    $(".visibleifjs").show();
                } else{
                    $('#id_recordfrom_day').prop("disabled", true);
                    $('#id_recordto_day').prop("disabled", true);
                    $('#id_recordfrom_month').prop("disabled", true);
                    $('#id_recordto_month').prop("disabled", true);
                    $('#id_recordfrom_month').prop("disabled", true);
                    $('#id_recordfrom_year').prop("disabled", true);
                    $('#id_recordto_year').prop("disabled", true);
                    $(".visibleifjs").hide();
                }
                if($("#id_course").val() != ""){
                    $('#id_ncmzoomgroup').prop("disabled", false);
                } else {
                    $('#id_ncmzoomgroup').prop("disabled", true);
                }
                $("#id_course").change(function() {
                    var course = $("#id_course").val();
                    $.ajax({
                        type : "GET",
                        data : 'course=' + course,
                        url : './classes/groups.php',
                        success : function(data) {
                            $("#id_ncmzoomgroup").html(data);

                        }
                    });
                    if(course != ''){
                        $('#id_ncmzoomgroup').prop("disabled", false);
                    } else{
                        $('#id_ncmzoomgroup').prop("disabled", true);
                    }
                });

                $("#id_category").change(function() {
                    var cat = $("#id_category").val();
                    $.ajax({
                        type : "GET",
                        data : 'category=' + cat,
                        url : './classes/courses.php',
                        success : function(data) {
                            $('#id_course').empty();
                            $("#id_course").html(data);

                        }
                    });

                });

                $("#id_clearbutton").click(function() {
                    $("#id_category").val("");
                    $("#id_course").val("");
                    $("#id_ncmzoomgroup").val("");
                    $("#id_ncmzoommeetingname").val("");
                    $("#id_ncmzoommeetingnumber").val("");
                    $("#id_ncmzoommeetinghost").val("");
                    $("#id_name").val("");
                    $("#id_username").val("");
                    $("#id_zoomemail").val("");
                    $('#id_enabledatefilter').attr('checked', false);
                    $('#id_zoomtype').prop('selectedIndex', "");
                    $('#id_recordfrom_day').prop("disabled", true);
                    $('#id_recordto_day').prop("disabled", true);
                    $('#id_recordfrom_month').prop("disabled", true);
                    $('#id_recordto_month').prop("disabled", true);
                    $('#id_recordfrom_month').prop("disabled", true);
                    $('#id_recordfrom_year').prop("disabled", true);
                    $('#id_recordto_year').prop("disabled", true);
                    $(".visibleifjs").hide();
                    $("#mform1").submit();
                });

                $("#id_indexcategory").change(function() {
                    $("#mform1").submit();
                });
                $("#id_enabledatefilter").change(function() {
                    if($(this).is(":checked")) {
                        $('#id_recordfrom_day').prop("disabled", false);
                        $('#id_recordto_day').prop("disabled", false);
                        $('#id_recordfrom_month').prop("disabled", false);
                        $('#id_recordto_month').prop("disabled", false);
                        $('#id_recordfrom_month').prop("disabled", false);
                        $('#id_recordfrom_year').prop("disabled", false);
                        $('#id_recordto_year').prop("disabled", false);
                        $(".visibleifjs").show();
                    } else {
                        $('#id_recordfrom_day').prop("disabled", true);
                        $('#id_recordto_day').prop("disabled", true);
                        $('#id_recordfrom_month').prop("disabled", true);
                        $('#id_recordto_month').prop("disabled", true);
                        $('#id_recordfrom_month').prop("disabled", true);
                        $('#id_recordfrom_year').prop("disabled", true);
                        $('#id_recordto_year').prop("disabled", true);
                        $(".visibleifjs").hide();
                    }
                });
            });

        }
    };
});