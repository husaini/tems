<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid = (isset($_GET['id'])) ? $_GET["id"] : null;

    if(!$eid) {
        header('location: site.php');
        exit();
    }

    $eid            =   mysql_real_escape_string($eid);
    $result         =   mysql_query("SELECT `name` FROM `site` WHERE id='$eid'", $link);
    $item_name      =   null;
    if ($result)
    {
        list($item_name)  =   mysql_fetch_row($result);
        mysql_free_result($result);
    }
    $do     =   mysql_query("delete from site where id = '$eid'");

    if(mysql_affected_rows($link)) {
        setSession('site_deleted',1);
        if($item_name)
        {
            setSession('deleted_site', stripslashes($item_name));
        }
        //Update user access
        updateUserAccess();
    }
    header('location: site.php');
    exit();
?>
