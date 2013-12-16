<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_GET['eid'])) ? $_GET["eid"] : null;
    $tab    =   isset($_GET['tab']) ? $_GET['tab'] : 'editol';

    if(!$eid) {
        header('location: library.php?tab=editol');
        exit();
    }

    if ($_POST) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string(trim($value));
        }

        $cat    =   (isset($_POST['cat'])) ? $_POST["cat"] : null;
        $did    =   (isset($_POST['did'])) ? $_POST["did"] : null;
        $clas   =   (isset($_POST['clas'])) ? $_POST["clas"] : null;
        $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;

        if($tap && $cat && $clas && $did) {
            $q = mysql_query("update asset_type set name ='$cat', classid = '$clas' where id = '$did'", $link);
            if(mysql_affected_rows($link))
            {
                setSession('asset_item_updated', 'Category was successfully updated.');
            }
            header('location: library.php?tab='.$tab);
            exit();
        }
    }

    $eid    =   mysql_real_escape_string($eid);
    $q1     =   mysql_query("select * from asset_type where id = '$eid'");
    $d1     =   mysql_fetch_array($q1);
    $t1     =   mysql_real_escape_string($d1['classid']);
    $q2     =   mysql_query("select * from asset_class where id = '$t1'");
    $d2     =   mysql_fetch_array($q2);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>TEMS: Edit Category</title>
</head>
<body>
    <div id="body_content">
        <form method="post">
            <h1 class="page-title full-width">Edit Category</h1>
            <p class="clear">&nbsp;</p>
            <p>
                <label class="auto">Category Name</label>
                <input required="required" type="text" name="cat" value="<?php echo $d1['name'];?>" />
            </p>
            <p>
                <label class="auto">Select Class</label>
                <select required="required" name="clas">
                    <?php
                        $q = mysql_query("select * from asset_class");
                        while ($row = mysql_fetch_assoc($q)) : ?>
                            <option value="<?php echo $row['id'];?>" <?php echo ($row['id'] == $t1) ? 'selected="selected"':'';?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                </select>
            </p>
            <p>&nbsp;</p>
            <p>
                <input type="submit" value="Update" class="btn btn-primary" />
                <a href="library.php?tab=editol" name="cancel" class="btn">Cancel</a>
            </p>

            <input type="hidden" name="tap" value="1" />
            <input type="hidden" name="did" value="<?php echo $eid; ?>" />
        </form>
    </div>
    <?php
        if(!function_exists('google_analytics'))
        {
            require_once(dirname(__FILE__).'/sharedfunc.php');
        }
        google_analytics('uitm');
    ?>
</body>
</html>
