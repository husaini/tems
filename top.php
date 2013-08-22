<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $base_url = getBaseUrl();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" href="css/style.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TEMS</title>
</head>
<body>
    <div id="header">
        <div class="header-logo">
            <a href="<?php echo $base_url?>" target="_top"><span class="logo"></span></a>
        </div>
        <div class="user-info">
            <span class="user-icon"></span> Hello <?php echo stripslashes($_SESSION['uname'])?> |
            <?php /*<a href="<?php echo $base_url?>/user.php" target="frame_right">Settings</a> | */?><a href="<?php echo $base_url?>/logout.php" target="frame_right">Logout</a>
        </div>
        <div class="last-login">
            Last Login: <?php echo date('d F Y', strtotime($_SESSION['last_login']));?>
        </div>
    </div>
</body>
</html>
