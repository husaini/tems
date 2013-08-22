<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');

    $eid    =   (isset($_REQUEST['eid'])) ? $_REQUEST["eid"] : null;

    if(!$eid) {
        header('location: library.php?tab=man');
        exit();
    }

    if ($_POST) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string($value);
        }

        $did    =   (isset($_POST['did'])) ? $_POST["did"] : null;
        $man    =   (isset($_POST['man'])) ? $_POST["man"] : null;
        $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;

        if($tap && $man && $did) {
            $q = mysql_query("update asset_manufacturer set name = '$man' where id = '$did'");
            setSession('manufacturer_updated', 1);
            header('location: library.php?tab=man');
            exit();
        }
    }

    $eid    =   mysql_real_escape_string($eid);
    $q      =   mysql_query("select * from asset_manufacturer where id = '$eid'");
    $d      =   mysql_fetch_array($q);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>Welcome to T.E.M.S + EMS</title>
</head>
<body>
    <div id="body_content">
        <form method="post" action="editman.php">
            <h1 class="page-title full-width">Edit Manufacturer</h1>
            <p class="clear">&nbsp;</p>
            <p>
                <label class="auto">Manufacturer Name</label>
                <input required="required" type="text" name="man" value="<?php echo $d['name']; ?>" />
            </p>
            <p>&nbsp;</p>
            <p>
                <input type="submit" value="Update" class="btn btn-primary" />
                <a href="library.php?tab=man" name="cancel" class="btn">Cancel</a>
            </p>
            <input type="hidden" name="tap" value="1" />
            <input type="hidden" name="did" value="<?php echo $eid; ?>" />
        </form>
    </div>
</body>
</html>
