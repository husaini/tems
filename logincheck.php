<?php
// verify login information

if (empty($_POST['loginname']) || empty($_POST['loginpass'])) {
    // transfer back to login page (error: fields cannot be empty)
    header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/login.php?err=2");
    exit();
}

session_start();
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

$_POST['loginpass'] =   md5(strtolower($_POST['loginname']).$_POST['loginpass']);
$_POST['loginpass'] =   md5($_POST['loginpass'].$_SESSION['challenge']);


$stmt = $mysqli->prepare("SELECT * FROM user WHERE username = ? AND MD5(CONCAT(password, ?)) = ?");
$stmt->bind_param('sss', $_POST['loginname'], $_SESSION['challenge'], $_POST['loginpass']);
//echo $_SESSION['challenge'] . " " . $_POST['loginpass'];
$stmt->execute();
$stmt->store_result();
$meta = $stmt->result_metadata();
while ($column = $meta->fetch_field()) {
    $bindvars[] = &$results[$column->name];
}
call_user_func_array(array($stmt, 'bind_result'), $bindvars);
if (!$stmt->fetch()) {
    $stmt->free_result();
    $stmt->close();
    $mysqli->close();
    header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/login.php?err=1");
    exit();
} else {
  // register session variable
    if ($results['enabled'] == 1) {
        $_SESSION['uid']        =   $results['id'];
        $_SESSION['uname']      =   $results['name'];
        $_SESSION['uin']        =   $results['username'];
        $_SESSION['gid']        =   $results['authlevel'];
        $_SESSION['sid']        =   $results['siteid'];
        $_SESSION['rem']        =   $results['remarks'];
        $_SESSION['last_login'] =   $results['lastaccess'];

        //user access
        $_SESSION['access'] =   getUserAccessList($results['id']);
        session_write_close();
    }

    $stmt->free_result();
    $stmt->close();
    $stmt = $mysqli->prepare("UPDATE user SET lastaccess = NOW() WHERE id = ?");
    $stmt->bind_param('i', $results['id']);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    if ($results['enabled'] == 1) {
       header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/main.php");
    } else {
       header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/login.php?err=3");
    }
    exit();
}
