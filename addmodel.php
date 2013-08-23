<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $cat    =   (isset($_POST['cat'])) ? $_POST['cat'] : null;
    $man    =   (isset($_POST['man'])) ? $_POST['man'] : null;
    $mod    =   (isset($_POST['mod'])) ? $_POST['mod'] : null;
    $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;

    if($_POST && (!$cat || !$mod || !$man)) {
        header('location: library.php#mod');
        exit();
    }

    if($tap) {
        $cat    =   mysql_real_escape_string($cat);
        $man    =   mysql_real_escape_string($man);
        $mod    =   mysql_real_escape_string($mod);
        $q      =   mysql_query("insert into asset_model (name,manuid,typeid) values ('$mod','$man','$cat')");
        if ($q)
        {
            setSession('asset_item_added', 'Model <em>"'.$mod.'"/em> was successfully added.');
        }
        header('location: library.php#mod');
        exit();
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>TEMS: Add Model</title>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Add New Model</h1>
        <p class="clear">&nbsp;</p>
        <form method="post" action="addmodel.php">
            <p>
                <label class="auto">Model Name</label>
                <input required="required" type="text" name="mod" />
            </p>
            <p>
                <label class="auto">Select Manufacturer</label>
                <select required="required" name="man">
                <?php
                    $result =   mysql_query("select * from asset_manufacturer");
                    if ($result): ?>
                        <?php while ($row = mysql_fetch_assoc($result)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                        <?php mysql_free_result($result);?>
                    <?php endif; ?>
                </select>
            </p>
            <p>
                <label class="auto">Select Category</label>
                <select required="required" name="cat">
                    <?php
                    $result =   mysql_query("select * from asset_type");
                    if ($result): ?>
                        <?php while ($row = mysql_fetch_assoc($result)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
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
            <a href="library.php#mod" class="btn btn-danger">&laquo; Cancel &amp; return to model list</a>
        </p>
    </div>
</body>
</html>
