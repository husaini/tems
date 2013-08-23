<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;
    $man    =   (isset($_POST['man'])) ? $_POST['man'] : null;

    if($_POST && !$man) {
        header('location: library.php#man');
        exit();
    }

    if($tap){
        $man    =   mysql_real_escape_string($man);
        $result =   mysql_query("insert into asset_manufacturer (`name`) value ('$man')");
        if($result)
        {
            setSession('asset_item_added', 'Manufacturer <em>"'.$man.'"/em> was successfully added.');
        }
        header('location: library.php#man');
        exit();
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>TEMS: Add Manufacturer</title>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Add New Manufacturer</h1>
        <p class="clear">&nbsp;</p>
        <form method="post" action="addman.php">
            <p>
                <label class="auto">Manufacturer Name</label>
                <input required="required" type="text" name="man" />
            </p>
            <p>
                <input type="submit" value="Add" class="btn btn-primary" />
            </p>
            <input type="hidden" name="tap" value="1" />
        </form>
        <p>
            <a href="library.php#man" class="btn btn-danger">&laquo; Cancel &amp; return to manufacturer list</a>
        </p>
    </div>
</body>
</html>
