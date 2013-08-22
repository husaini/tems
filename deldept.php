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

    $eid            =   mysql_real_escape_string($eid);
    $result         =   mysql_query("SELECT `name` FROM `site_department` WHERE id='$eid'", $link);
    $item_name      =   null;
    if ($result)
    {
        list($item_name)  =   mysql_fetch_row($result);
        mysql_free_result($result);
    }
    $do     =   mysql_query("delete from site_department where id = '$eid'");

    if(mysql_affected_rows($link)) {
        setSession('department_deleted',1);
        if($item_name)
        {
            setSession('deleted_department', stripslashes($item_name));
        }
        //Update user access
        updateUserAccess();
    }
    header('location: editsite.php?id='.$sid.'&tab=tabdept#tabdept');
    exit();
