<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_REQUEST['id'])) ? $_REQUEST["id"] : null;
    $sid    =   (isset($_REQUEST['sid'])) ? $_REQUEST["sid"] : null;

    if(!is_numeric($eid) || !$eid) {
        header('location: site.php');
        exit();
    }

    if ($_POST) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string($value);
        }

        $did    =   (isset($_POST['did'])) ? $_POST["did"] : null;
        $dept   =   (isset($_POST['name'])) ? $_POST["name"] : null;
        $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;

        if($tap && $did && $dept) {
            $dept   =   mysql_real_escape_string($dept);
            $q = mysql_query("update site_department set `name` = '$dept' where id = '$did'");
            if(is_resource($q)) {
                mysql_free_result($q);
            }
            setSession('department_updated', 1);
            header('location: editsite.php?id='.$sid.'&tab=tabdept');
            exit();
        }
    }

    $eid    =   mysql_real_escape_string($eid);
    $q      =   mysql_query("select * from site_department where id = '$eid'");
    if(!$q) {
        header('location: site.php');
        exit();
    }

    $d      =   mysql_fetch_array($q);
    mysql_free_result($q);

    if(!$d) {
        //location not found??
        header('location: site.php');
        exit();
    }

    if(!$sid) $sid=$d['siteid'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<title>TEMS: Edit Department</title>
</head>
<body>
    <div id="body_content">
        <form method="post">
            <h1 class="page-title full-width">Edit Department</h1>
            <p class="clear">&nbsp;</p>
            <p>
                <label class="auto">Department Name</label>
                <input required="required" type="text" name="name" value="<?php echo $d['name']; ?>" class="auto-width" />
            </p>
            <p>&nbsp;</th>
            <p>
                <input type="submit" value="Update" class="btn btn-primary" />
                <a href="editsite.php?id=<?php echo $sid;?>&amp;tab=tabdept" name="cancel" class="btn">Cancel</a>
            </p>
          </tr>
        </table>
        <input type="hidden" name="tap" value="1" />
        <input type="hidden" name="id" value="<?php echo $eid ?>" />
        <input type="hidden" name="sid" value="<?php echo $sid ?>" />
        <input type="hidden" name="did" value="<?php echo $eid ?>" />
        </form>
    </div>
</body>
</html>
