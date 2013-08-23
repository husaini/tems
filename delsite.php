<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $site_id    =   (isset($_POST['id']) && is_numeric($_POST['id'])) ? intval($_POST['id'], 10) : null;

    if(!$site_id)
    {
        header('location: site.php');
        exit();
    }

    $result         =   mysql_query("SELECT `name` FROM `site` WHERE id='$site_id'", $link);
    $item_name      =   null;

    if ($result)
    {
        list($item_name)  =   mysql_fetch_row($result);
        mysql_free_result($result);
    }

    if(!$item_name)
    {
        //probably was deleted somewhere
        header('location: site.php');
        exit();
    }

    $do     =   mysql_query("delete from site where id = '$site_id'");

    if(mysql_affected_rows($link)) {
        setSession('site_deleted', "Site <em>\"$item_name\"</em> was successfully deleted.");
        setSession('deleted_site', stripslashes($item_name));

        //Update user access session
        updateUserAccess();
    }
    header('location: site.php');
    exit();
