<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');

    $eid = (isset($_GET['id'])) ? $_GET["id"] : null;

    if(!$eid) {
        header('location: site.php');
        exit();
    }

    $eid    =   mysql_real_escape_string($eid);
    $do     =   mysql_query("delete from site where id = '$eid'");

    if(mysql_affected_rows($link)) {
        $do = mysql_query("delete from site_department where siteid = '$eid'");
    }
    header('location: site.php');
    exit();
?>
