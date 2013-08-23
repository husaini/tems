<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_GET['eid'])) ? $_GET["eid"] : null;
    $tab    =   isset($_GET['tab']) ? $_GET['tab'] : 'mod';

    if(!$eid) {
        header('location: library.php?tab=mod');
        exit();
    }

    if ($_POST) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string(trim($value));
        }

        $cat    =   (isset($_POST['cat'])) ? $_POST["cat"] : null;
        $did    =   (isset($_POST['did'])) ? $_POST["did"] : null;
        $clas   =   (isset($_POST['clas'])) ? $_POST["clas"] : null;
        $man    =   (isset($_POST['man'])) ? $_POST["man"] : null;
        $mod    =   (isset($_POST['mod'])) ? $_POST["mod"] : null;
        $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;
        $desc   =   isset($_POST['description']) ? $_POST['description'] : null;

        if($tap && $cat && $did && $man && $mod) {
            mysql_query("update asset_model set name = '$mod', manuid = '$man', typeid = '$cat', description='$desc' where id = '$did'", $link) or die(mysql_error($link));
            if(mysql_affected_rows($link))
            {
                setSession('asset_item_updated', 'Model was successfully updated.');
            }

            header('location: library.php?tab='.$tab);
            exit();
        }
    }

    $eid    =   mysql_real_escape_string($eid);
    $q3     =   mysql_query("select * from asset_model where id = '$eid'");
    $d3     =   mysql_fetch_array($q3);
    $m3     =   mysql_real_escape_string($d3['manuid']);
    $t3     =   mysql_real_escape_string($d3['typeid']);
    $q4     =   mysql_query("select * from asset_manufacturer where id = '$m3'");
    $d4     =   mysql_fetch_array($q4);
    $q5     =   mysql_query("select * from asset_type where id = '$t3'");
    $d5     =   mysql_fetch_array($q5);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>TEMS: Edit Model</title>
</head>
<body>
    <div id="body_content">
        <form method="post">
            <h1 class="page-title full-width">Edit Model</h1>
            <p class="clear">&nbsp;</p>
            <p>
                <label class="auto">Model Name</label>
                <input required="required" type="text" name="mod" value="<?php echo $d3['name']; ?>" />
            </p>
            <p>
                <label class="auto">Select Manufacturer</label>
                <select required="required" name="man">
                    <option value="<?php echo $d4['id']; ?>"><?php echo $d4['name'] ?></option>
                    <?php
                        $q  =   mysql_query("select * from asset_manufacturer");
                        while ($row = mysql_fetch_assoc($q)): ?>
                            <option value="<?php echo $row['id'];?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                        <?php mysql_free_result($q);?>
              </select>
            </p>
            <p>
                <label class="auto">Select Category</label>
                <select required="required" name="cat">
                    <option value="<?php echo $d5['id'];?>"><?php echo $d5['name']; ?></option>
                    <?php
                        $q =   mysql_query("select * from asset_type");
                        while ($row = mysql_fetch_assoc($q)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name'] ?></option>
                        <?php endwhile; ?>
                        <?php mysql_free_result($q);?>
                </select>
            </p>
            <p>&nbsp;</p>
            <p>
                <input type="submit" value="Update" class="btn btn-primary" />
                <a href="library.php?tab=mod" name="cancel" class="btn">Cancel</a>
            </p>
            <input type="hidden" name="tap" value="1" />
            <input type="hidden" name="did" value="<?php echo $eid; ?>" />
        </form>
    </div>
</body>
</html>
