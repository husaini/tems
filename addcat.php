<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $clas   =   (isset($_POST['clas'])) ? $_POST["clas"] : null;
    $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;
    $cat    =   (isset($_POST['cat'])) ? $_POST['cat'] : null;

    if($_POST && (!$cat || !$clas)) {
        header('location: library.php#editol');
        exit();
    }

    if($tap) {
        $cat    =   mysql_real_escape_string($cat);
        $clas   =   mysql_real_escape_string($clas);
        $q      =   mysql_query("insert into asset_type (name,classid) values ('$cat','$clas')");
        if ($q)
        {
            setSession('asset_item_added', 'Category <em>"'.$cat.'"/em> was successfully added.');
        }
        header('location: library.php#editol');
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
<title>TEMS: Add Category</title>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Add New Category</h1>
        <p class="clear">&nbsp;</p>
        <form method="post" action="addcat.php">
            <p>
                <label class="auto">Category Name </label>
                <input required="required" type="text" name="cat" />
            </p>
            <p>
                <label class="auto">Select Class </label>
                <select required="required" name="clas">
                    <?php
                    $result   =   mysql_query("select * from asset_class");
                    if ($result): ?>
                        <?php while ($row = mysql_fetch_assoc($result)): ?>
                            <option value="<?php echo $row['id'];?>"><?php echo $row['name'];?></option>
                        <?php endwhile; ?>
                        <?php mysql_free_result($result);?>
                    <?php endif; ?>
                </select>
            </p>
            <p>
                <input type="submit" value="Add" class="btn btn-primary" />
            </p>
            <input type="hidden" name="tap" value="1" />
        </form>
        <p>
            <a href="library.php#editol" class="btn btn-danger">&laquo; Cancel &amp; return to category list</a>
        </p>
    </div>
</body>
</html>
