<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_GET['id'])) ? $_GET["id"] : null;
    $sid    =   (isset($_GET['sid'])) ? $_GET["sid"] : null;
    $sid    =   mysql_real_escape_string($sid);

    if(!$eid) {
        header('location: editsite.php?id='.$sid.'&tab=tabloc');
        exit();
    }

    $eid            =   mysql_real_escape_string($eid);
    $result         =   mysql_query("SELECT `name` FROM `site_location` WHERE id='$eid'", $link) or die(mysql_error($link));
    list($locname)  =   mysql_fetch_row($result);
    mysql_free_result($result);
    $do     =   mysql_query("delete from site_location where id = '$eid'",$link);
    if(mysql_affected_rows($link)) {
        setSession('location_deleted',1);
        setSession('deleted_location', stripslashes($locname));
    }
    header('location: editsite.php?id='.$sid.'&tab=tabloc#tabloc');
    exit();