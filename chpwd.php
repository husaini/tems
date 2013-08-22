<?php
require(dirname(__FILE__).'/includes/checklogged.php');
if (isset($_POST['func'])) {
    require(dirname(__FILE__).'/includes/conn.php');

    $stmt = $mysqli->prepare("SELECT MD5(CONCAT(password,?)) FROM user WHERE id = ?");
    $stmt->bind_param('si', $_SESSION['challenge'], $_SESSION['uid']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($pwd);
    if (!$stmt->fetch()) {
        echo "Fatal Error: What happens to the roses that wither away in springtime?";
        $stmt->close();
        $mysqli->close();
        exit;
    }

    $post_curpwd    =   md5(md5($_SESSION['uin'].$_POST['curpwd']).$_SESSION['challenge']);

    if ($pwd != $post_curpwd) {
        $stmt->close();
        $mysqli->close();
        header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/chpwd.php?err=1");
        //echo $pwd . "<br>" . $_POST['curpwd'];
        exit;
    }
    if ($_POST['newpwd'] != $_POST['newpwd2']) {
        $stmt->close();
        $mysqli->close();
        header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/chpwd.php?err=2");
        exit;
    }

    $new_password   =   md5($_SESSION['uin'].$_POST['newpwd']);

    $stmt->free_result();
    $stmt->close();
    $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $new_password, $_SESSION['uid']);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/chpwd.php?err=0");
    exit;
}

$_SESSION['challenge'] = md5(mt_rand());

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Change Password</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">User Password Change</h1>
        <p class="clear">&nbsp;</p>
        <?php if (isset($_GET['err'])): ?>
            <p class="alert <?php echo ($_GET['err'] > 0) ? 'alert-error': 'alert-success';?>">
                <?php if($_GET['err'] == 1): ?>
                    Your entered current password is wrong.
                <?php elseif ($_GET['err'] == 2): ?>
                    Your new password and the confirmation are mismatched.
                <?php elseif($_GET['err'] == 0): ?>
                    Your password was successfully changed.
                <?php endif;?>
            </p>
        <?php endif; ?>

        <form id="frmchpwd" method="post">
            <table class="no-border full-width">
                <tr>
                    <td>Current Password</td>
                    <td>
                        <input required="required" type="password" name="curpwd" id="curpwd" size="30" maxlength="50" />
                    </td>
                </tr>
                <tr>
                    <td>New Password</td>
                    <td>
                        <input required="required" type="password" name="newpwd" id="newpwd" size="30" maxlength="50" /> (6-20 characters)
                    </td>
                </tr>
                <tr>
                    <td>Confirm New Password</td>
                    <td>
                        <input required="required" type="password" name="newpwd2" id="newpwd2" size="30" maxlength="50" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" name="func" value="Change" class="btn btn-primary">
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#frmchpwd").submit(function() {
            var currentPwd  =   $.trim($("#curpwd").val());
            var newPwd1     =   $.trim($("#newpwd").val());
            var newPwd2     =   $.trim($("#newpwd2").val());

            if (!currentPwd || !newPwd1 || !newPwd2) {
                alert("Passwords cannot be empty");
                return false;
            } else if (newPwd1.length < 6 || newPwd1.length > 20) {
                alert("Password length must be between 6 to 20\nPlease key them in again");
                $("#newpwd").val("");
                $("#newpwd2").val("");
                $("#newpwd").focus();
                return false;
            } else if (currentPwd.toLowerCase() == newPwd1.toLowerCase()) {
                alert("New password must be different than your old password.");
                $("#newpwd").val("");
                $("#newpwd2").val("");
                $("#newpwd").focus();
                return false;
            } else if (newPwd1 != newPwd2) {
                alert("New Password and Confirm New Password values are different\nPlease key them in again");
                $("#newpwd").val("");
                $("#newpwd2").val("");
                $("#newpwd").focus();
                return false;
            }
        });
        if($('.alert-success').length > 0) {
            setTimeout(function() {
                $('.alert-success').fadeOut('slow', function() {
                    window.location.href = 'chpwd.php';
                });
            },1500);
        }
    });
    </script>
</body>
</html>
