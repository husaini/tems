<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require_once(dirname(__FILE__).'/includes/sharedfunc.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Dashboard</title>

<link type="text/css" rel="stylesheet" href="css/styles.php?v=<?php echo time()?>" media="screen, projection">
<link type="text/css" rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" media="screen">

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/scripts.php?v=<?php echo time()?>"></script>
<script type="text/javascript">
$(function() {
    $('#tabs').tabs();
    $('.subtabs').tabs();
});
</script>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Dashboard</h1>
        <div id="tabs" class="tems-ui-tab subpage-tabs">
            <div class="subpage-title">
                <ul>
                    <li><a href="#pending">Pending Work</a> </li>
                    <li><a href="#upcoming">Upcoming Work</a> </li>
                </ul>
            </div>
            <div id="pending" class="container-fluid">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="row-fluid">
                            <div class="span6">
                                <h4 class="sub-heading">Pending Work - Forecast</h4>
                                <div id="pending_forecast" class="chart"></div>
                            </div>
                            <div class="span6">
                                <div class="subtabs">
                                    <h4 class="sub-heading">Pending Work - Distribution</h4>
                                    <ul>
                                        <li><a href="#all_pending">All</a></li>
                                        <li><a href="#pending_week1">&lt; 1 Week</a></li>
                                        <li><a href="#pending_week2">1 - 2 Weeks</a></li>
                                        <li><a href="#pending_week3">2 - 4 Weeks</a></li>
                                        <li><a href="#pending_month1">1 - 2 Months</a></li>
                                        <li><a href="#pending_month2">2 - 4 Months</a></li>
                                        <li><a href="#pending_month3">&gt; 6 Months</a></li>
                                    </ul>
                                    <div id="all_pending">
                                        <div id="pending_distribution" class="chart"></div>
                                    </div>
                                    <div id="pending_week1">
                                        <div id="pendingdst_week1" class="chart"></div>
                                    </div>
                                    <div id="pending_week2">
                                        <div id="pendingdst_week2" class="chart"></div>
                                    </div>
                                    <div id="pending_week3">
                                        <div id="pendingdst_week3" class="chart"></div>
                                    </div>
                                    <div id="pending_month1">
                                        <div id="pendingdst_month1" class="chart"></div>
                                    </div>
                                    <div id="pending_month2">
                                        <div id="pendingdst_month2" class="chart"></div>
                                    </div>
                                    <div id="pending_month3">
                                        <div id="pendingdst_month3" class="chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span6">
                                <h4 class="sub-heading">All Pending Work - Forecast</h4>
                                <div id="allpending_forecast" class="chart"></div>
                            </div>
                            <div class="span6">
                                <div class="subtabs">
                                    <h4 class="sub-heading">All Pending Work - Distribution</h4>
                                    <ul>
                                        <li><a href="#all_allpending">All</a></li>
                                        <li><a href="#allpending_week1">&lt; 1 Week</a></li>
                                        <li><a href="#allpending_week2">1 - 2 Weeks</a></li>
                                        <li><a href="#allpending_week3">2 - 4 Weeks</a></li>
                                        <li><a href="#allpending_month1">1 - 2 Months</a></li>
                                        <li><a href="#allpending_month2">2 - 4 Months</a></li>
                                        <li><a href="#allpending_month3">&gt; 6 Months</a></li>
                                    </ul>
                                    <div id="all_allpending">
                                        <div id="allpending_distribution" class="chart"></div>
                                    </div>
                                    <div id="allpending_week1">
                                        <div id="allpendingdst_week1" class="chart"></div>
                                    </div>
                                    <div id="allpending_week2">
                                        <div id="allpendingdst_week2" class="chart"></div>
                                    </div>
                                    <div id="allpending_week3">
                                        <div id="allpendingdst_week3" class="chart"></div>
                                    </div>
                                    <div id="allpending_month1">
                                        <div id="allpendingdst_month1" class="chart"></div>
                                    </div>
                                    <div id="allpending_month2">
                                        <div id="allpendingdst_month2" class="chart"></div>
                                    </div>
                                    <div id="allpending_month3">
                                        <div id="allpendingdst_month3" class="chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="upcoming">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="row-fluid">
                            <div class="span6">
                                <h4 class="sub-heading">Upcoming Work - Forecast</h4>
                                <div id="upcoming_forecast" class="chart"></div>
                            </div>
                            <div class="span6">
                                <div class="subtabs">
                                    <h4 class="sub-heading">Upcoming Work - Distribution</h4>
                                    <ul>
                                        <li><a href="#all_upcoming">All</a></li>
                                        <li><a href="#upcoming_week1">&lt; 1 Week</a></li>
                                        <li><a href="#upcoming_week2">1 - 2 Weeks</a></li>
                                        <li><a href="#upcoming_week3">2 - 4 Weeks</a></li>
                                        <li><a href="#upcoming_month1">1 - 2 Months</a></li>
                                        <li><a href="#upcoming_month2">2 - 4 Months</a></li>
                                    </ul>
                                    <div id="all_upcoming">
                                        <div id="upcoming_distribution" class="chart"></div>
                                    </div>
                                    <div id="upcoming_week1">
                                        <div id="upcomingdst_week1" class="chart"></div>
                                    </div>
                                    <div id="upcoming_week2">
                                        <div id="upcomingdst_week2" class="chart"></div>
                                    </div>
                                    <div id="upcoming_week3">
                                        <div id="upcomingdst_week3" class="chart"></div>
                                    </div>
                                    <div id="upcoming_month1">
                                        <div id="upcomingdst_month1" class="chart"></div>
                                    </div>
                                    <div id="upcoming_month2">
                                        <div id="upcomingdst_month2" class="chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span6">
                                <h4 class="sub-heading">All Upcoming Work - Forecast</h4>
                                <div id="allupcoming_forecast" class="chart"></div>
                            </div>
                            <div class="span6">
                                <div class="subtabs">
                                    <h4 class="sub-heading">All Upcoming Work - Distribution</h4>
                                    <ul>
                                        <li><a href="#all_allupcoming">All</a></li>
                                        <li><a href="#allupcoming_week1">&lt; 1 Week</a></li>
                                        <li><a href="#allupcoming_week2">1 - 2 Weeks</a></li>
                                        <li><a href="#allupcoming_week3">2 - 4 Weeks</a></li>
                                        <li><a href="#allupcoming_month1">1 - 2 Months</a></li>
                                        <li><a href="#allupcoming_month2">2 - 4 Months</a></li>
                                    </ul>
                                    <div id="all_allupcoming">
                                        <div id="allupcoming_distribution" class="chart"></div>
                                    </div>
                                    <div id="allupcoming_week1">
                                        <div id="allupcomingdst_week1" class="chart"></div>
                                    </div>
                                    <div id="allupcoming_week2">
                                        <div id="allupcomingdst_week2" class="chart"></div>
                                    </div>
                                    <div id="allupcoming_week3">
                                        <div id="allupcomingdst_week3" class="chart"></div>
                                    </div>
                                    <div id="allupcoming_month1">
                                        <div id="allupcomingdst_month1" class="chart"></div>
                                    </div>
                                    <div id="allupcoming_month2">
                                        <div id="allupcomingdst_month2" class="chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
