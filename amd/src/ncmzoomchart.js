/*
 * @package    report_ncmzoom
 * @copyright  2017 Dasu Gunathunga
 * @license    Navitas
 */
/**
 * @module report_ncmzoom
 */
/* jshint unused:false */

define(
        [ 'jquery',
                'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js' ],
        function($, videojs) {
            return {
                initialise : function(course, meetings, recordings, categories,
                        meetingincat, recordingsincat, color, reccategories) {
                    $(document)
                            .ready(
                                    function() {
                                        var ctx = document.getElementById(
                                                "myChart").getContext('2d');
                                        var myChart = new Chart(
                                                ctx,
                                                {
                                                    type : 'bar',
                                                    data : {
                                                        labels : course,
                                                        datasets : [
                                                                {
                                                                    label : 'Zoom Meetings',
                                                                    data : meetings,
                                                                    backgroundColor : "rgba(53, 114, 176, 1)",
                                                                    fillColor : "rgba(53, 114, 176, 1)"
                                                        },
                                                                {
                                                                    label : 'Recordings',
                                                                    data : recordings,
                                                                    backgroundColor : "rgba(255,153,0, 1)",
                                                                    fillColor : "rgba(255,153,0, 1)"
                                                        } ]
                                                    },
                                                    options : {
                                                        title : {
                                                            display : true,
                                                            text : 'Zoom Meetings'
                                                        },
                                                        barValueSpacing: 20,
                                                        scales : {
                                                            yAxes: [{
                                                                ticks: {
                                                                    min: 0,
                                                                }
                                                            }]
                                                        }
                                                    }
                                                });

                                        var ctx2 = document.getElementById(
                                                "meetings").getContext('2d');
                                        var myChart2 = new Chart(ctx2, {
                                            type : 'pie',
                                            data : {
                                                labels : categories,
                                                datasets : [ {
                                                    backgroundColor : color,
                                                    data : meetingincat
                                                } ]
                                            },
                                            options : {
                                                responsive : true,
                                                maintainAspectRatio : false,
                                                title : {
                                                    display : true,
                                                    text : 'Zoom Meetings'
                                                }
                                            }
                                        });

                                        var ctx3 = document.getElementById(
                                                "recordings").getContext('2d');
                                        var myChart3 = new Chart(
                                                ctx3,
                                                {
                                                    type : 'pie',
                                                    data : {
                                                        labels : reccategories,
                                                        datasets : [ {
                                                            backgroundColor : color,
                                                            data : recordingsincat
                                                        } ]
                                                    },
                                                    options : {
                                                        responsive : true,
                                                        maintainAspectRatio : false,
                                                        title : {
                                                            display : true,
                                                            text : 'Zoom Video Recordings'
                                                        }
                                                    }
                                                });
                                    });
                }
            };
        });
