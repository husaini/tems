<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_GET['id'])) ? $_GET["id"] : null;
    $depid    =   (isset($_GET['depid'])) ? $_GET["depid"] : null;
    $sid    =   (isset($_GET['sid'])) ? $_GET["sid"] : null;
    $sid    =   mysql_real_escape_string($sid);

    if(!$eid) {
        header('location: editsite.php?id='.$sid.'&tab=tabloc');
        exit();
    }

    $eid            =   mysql_real_escape_string($eid);
    $result         =   mysql_query("SELECT `name` FROM `department_location` WHERE id='$eid'", $link) or die(mysql_error($link));
    $locname        =   null;

    if ($result)
    {
        list($locname)  =   mysql_fetch_row($result);
        mysql_free_result($result);
    }
    $do     =   mysql_query("delete from department_location where id = '$eid'",$link);
    if(mysql_affected_rows($link)) {
        setSession('location_deleted',1);
        if ($locname)
        {
            setSession('deleted_location', stripslashes($locname));
        }
        //Update user access
        updateUserAccess();
    }
    header('location: editdept.php?id='.$depid.'&sid='.$sid.'&tab=tabdept#tabdept');
    exit();
