<?php
session_start();
if (isset($_SESSION['uid']))
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/main.php");
    exit();
}
$_SESSION['challenge'] = md5(mt_rand());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>TEMS: Login</title>
    <link rel="stylesheet" href="css/login.css" type="text/css">
    <link rel="apple-touch-icon" href="apple-touch-icon.png" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <!--[if lt IE 7]>
    <script type="text/javascript">window.location = "unsupported.php";</script>
    <![endif]-->
    <script type="text/javascript">
    $(document).ready(function() {
      $("#frmlogin").submit(function() {
        if ($("#loginname").val() == "" || $("#loginpass").val() == "") {
          alert("Username and Password cannot be empty");
          return false;
        }
      });
      setTimeout(function() {
          $('#loginname').focus();
      },200);
    });
    </script>
</head>
<body class="radial-glow">
    <div id="loginall">
        <div id="loginheaderbox">
            <div class="logo-content">
                <span class="logo"></span>
            </div>
            <span class="box-shadow"></span>
        </div>
        <div id="logininputbox">
            <h3 class="box-header">Login</h3>
            <div class="content">
                <form id="frmlogin" method="post" action="logincheck.php">
                    <p>
                        <input required="required" type="text" id="loginname" name="loginname" placeholder="Username" />
                    </p>
                    <p>
                        <input autocomplete="off" required="required" type="password" id="loginpass" name="loginpass" placeholder="Password" />
                    </p>
                    <p>
                        <input class="loginbtn" type="submit" value="Submit" />
                    </p>
                </form>
            </div>
            <span class="box-shadow"></span>
        </div>
        <?php if (isset($_GET['err'])): ?>
            <div id="loginerror" class="round">ERROR: Username/Password Mismatch.</div>
        <?php endif; ?>
    </div>
</body>
</html>
