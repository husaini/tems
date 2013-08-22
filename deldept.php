<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');

    $eid    =   (isset($_GET['id'])) ? $_GET["id"] : null;
    $sid    =   (isset($_GET['sid'])) ? $_GET["sid"] : null;
    $sid    =   mysql_real_escape_string($sid);

    if(!$eid) {
        header('location: editsite.php?id='.$sid.'&tab=tabdept');
        exit();
    }

    $eid    =   mysql_real_escape_string($eid);
    $do     =   mysql_query("delete from site_department where id = '$eid'");
    header('location: editsite.php?id='.$sid.'&tab=tabdept#tabdept');
    exit();
