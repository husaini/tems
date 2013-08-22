<?php
@session_start();
if (!isset($_SESSION['uid'])) {
    if (((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_GET['jspost']) && $_GET['jspost'] == 1))) {
        //ajax
        exit(json_encode('session_expired'));
    }
    header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/logout.php");
    exit;
}
