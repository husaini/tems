<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $clas   =   (isset($_POST['clas'])) ? $_POST["clas"] : null;
    $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;

    if($_POST && !$clas) {
        header('location: library.php#tabnew');
        exit();
    }

    if($tap){
        $clas   =   mysql_real_escape_string($clas);
        $q      =   mysql_query("insert into asset_class (`name`) value ('$clas')");
        if ($q)
        {
            setSession('asset_item_added', 'Class <em>"'.$clas.'"/em> was successfully added.');
        }
        header('location: library.php#tabnew');
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
<title>TEMS: Add Class</title>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Add New Class</h1>
        <p class="clear">&nbsp;</p>
        <form method="post" action="addclass.php">
            <p>
                <label class="auto">Class Name</label>
                 <input required="required" type="text" name="clas" />
            </p>
            <p>
                <input type="submit" value="Add" class="btn btn-primary" />
            </p>
            <input type="hidden" name="tap" value="1" />
        </form>
        <p>
            <a href="library.php#tabnew" class="btn btn-danger">&laquo; Cancel &amp; return to class list</a>
        </p>
    </div>
</body>
</html>
