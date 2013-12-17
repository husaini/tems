<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');
    $base_url = getBaseUrl();
    function limitname($var) {
        if (strlen($var) > 16) {
            return trim(substr($var, 0, 13)) . "...";
        }
        return $var;
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
<body class="nav-left">
    <div class="table-block">
        <table class="header" width="150" cellpadding="2" cellspacing="0">
            <tr>
                <td class="trigger"><?php echo limitname($_SESSION['uname']); ?> <i class="icon icon-collapse"></i></td>
            </tr>
        </table>
        <span class="kandungan" id="boxUser">
            <table class="content" width="150" cellpadding="2" cellspacing="0">
                <tr>
                    <td style="padding-left: 4px">
                        <a href="<?php echo $base_url?>/logout.php" target="_top"><img src="images/shutdown.gif" border="0">Logout</a>
                        <a href="<?php echo $base_url?>/chpwd.php" target="frame_right"><img src="images/modify.gif" border="0">Change Password</a>
                    </td>
                </tr>
            </table>
        </span>
    </div>
    <div class="table-block">
        <table class="header" width="150" cellpadding="2" cellspacing="0">
            <tr>
                <td class="trigger">Modules <i class="icon icon-collapse"></i></td>
            </tr>
        </table>
        <span class="kandungan" id="boxSearch">
            <table class="content" width="150" cellpadding="2" cellspacing="0">
                <tr>
                    <td style="padding-left: 4px">
                        <a href="<?php echo $base_url?>/dashboard.php" target="frame_right"><img src="images/docgraph.gif" border="0">Dashboard</a>
                        <a href="<?php echo $base_url?>/asset.php" target="frame_right"><img src="images/browse.gif" border="0">Asset</a>
                        <a href="<?php echo $base_url?>/workorder.php" target="frame_right"><img src="images/modify.gif" border="0">Work Order</a>
                        <a href="<?php echo $base_url?>/printoption.php" target="frame_right"><img src="images/browse.gif" border="0">Report</a>
                        <!--<a href="<?php echo $base_url?>/compare2.php" target="frame_right"><img src="images/reports.gif" border="0">Compare</a>-->
                        <a href="<?php echo $base_url?>/vendor.php" target="frame_right"><img src="images/user.gif" border="0">Vendor</a>
                    </td>
                </tr>
            </table>
        </span>
    </div>
    <?php if ($_SESSION['gid'] <= 10) : ?>
        <div class="table-block">
            <table class="header" width="150" cellpadding="2" cellspacing="0">
                <tr>
                    <td class="trigger">Administrative <i class="icon icon-collapse"></i></td>
                </tr>
            </table>
            <span class="kandungan" id="boxAdmin">
                <table class="content" width="150" cellpadding="2" cellspacing="0">
                    <tr>
                        <td style="padding-left: 4px">
                            <a href="<?php echo $base_url?>/library.php#tabnew" target="frame_right"><img src="images/reports.gif" border="0">Equipment Library</a>
                            <a href="<?php echo $base_url?>/site.php" target="frame_right"><img src="images/reports.gif" border="0">Sites & Locations</a>
                            <?php
                            /*
                            <a href="<?php echo $base_url?>/hist.php" target="frame_right"><img src="images/reports.gif" border="0">SMS & Email History</a>
                            */
                            ?>
                            <a href="<?php echo $base_url?>/user.php" target="frame_right"><img src="images/user.gif" border="0">User Accounts</a>
                        </td>
                    </tr>
                </table>
            </span>
        </div>
    <?php endif; ?>
    <script type="text/javascript">
    $(function() {
        $(".trigger").click(function() {
            if ($(this).parents("table").next("span").css("display") == "inline") {
                $(this).parents("table").next("span").css("display", "none");
                $(this).find('.icon').removeClass('icon-collapse').addClass('icon-expand');
            } else {
                $(this).parents("table").next("span").css("display", "inline");
                $(this).find('.icon').removeClass('icon-expand').addClass('icon-collapse');
            }
        });
    });
    </script>
</body>
</html>
