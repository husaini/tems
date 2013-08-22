<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

# Include all files in lib
$libs = glob(dirname(__FILE__).'/lib/*.php');

foreach($libs as $lib)
{
    require_once($lib);
}
$base_url       =   getBaseUrl();
$pending        =   dashboard_get_pending();
$upcoming       =   dashboard_get_upcoming();
$pending_total  =   0;
$upcoming_total =   0;

/*
$pending2        =   dashboard_get_pending(true);
print_r($pending);
print_r($pending2);
die();
*/

if ($pending)
{
    for($x=1; $x <=3; $x++)
    {
        $pending_total          +=  $pending['workorder_week_'.$x] + $pending['workorder_month_'.$x] + $pending['ppm_week_'.$x] + $pending['ppm_month_'.$x];
    }
}


if ($upcoming)
{
    for($x=1; $x <=3; $x++)
    {
        $wo_month       =   (isset($upcoming['workorder_month_'.$x])) ? $upcoming['workorder_month_'.$x] : 0;
        $wo_week        =   (isset($upcoming['workorder_week_'.$x])) ? $upcoming['workorder_week_'.$x] : 0;
        $ppm_month      =   (isset($upcoming['ppm_month_'.$x])) ? $upcoming['ppm_month_'.$x] : 0;
        $ppm_week       =   (isset($upcoming['ppm_week_'.$x])) ? $upcoming['ppm_week_'.$x] : 0;
        $upcoming_total +=  $wo_week + $wo_month + $ppm_month + $ppm_week;
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
</head>
<body class="notification-body">
    <div id="notification">
        <ul>
            <li>
                <a href="<?php echo $base_url?>/workorder.php" target="frame_right">
                    You have <strong><?php echo $pending_total;?></strong> pending <?php echo ($pending_total > 1 || !$pending_total) ? 'Workorders' : 'Workorder';?>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url?>workorder.php" target="frame_right">
                    You have <strong><?php echo $upcoming_total;?></strong> upcoming <?php echo ($upcoming_total > 1 || !$upcoming_total) ? 'Workorders' : 'Workorder';?>
                </a>
            </li>
        </ul>
    </div>
</body>
</html>
