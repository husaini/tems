<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/cons.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $eid    =   (isset($_REQUEST['id'])) ? $_REQUEST["id"] : null;
    $depid    =   (isset($_REQUEST['depid'])) ? $_REQUEST["depid"] : null;

    if(!is_numeric($eid) || !$eid) {
        header('location: site.php');
        exit();
    }

    // Access level restriction to sites/departments and locations
    $usite_ids      =   array();
    $uloc_ids       =   array();
    $udept_ids      =   array();
    $uaccess        =   getSession('access');
    if ($uaccess) {
        if(isset($uaccess['sites'])) {
            foreach ($uaccess['sites'] as $usite) {
                $usite_ids[]    =   $usite['id'];
            }
        }
        if(isset($uaccess['locations'])) {
            foreach ($uaccess['locations'] as $uloc) {
                $uloc_ids[]    =   $uloc['id'];
            }
        }
        if(isset($uaccess['departments'])) {
            foreach ($uaccess['departments'] as $udept) {
                $udept_ids[]    =   $udept['id'];
            }
        }
    }

    // Check if user has access to location
    if(!$uloc_ids || !in_array((int)$eid, $uloc_ids)) {
        die('You are not allowed to edit this location.');
    }

    if ($_POST) {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string($value);
        }

        $did    =   (isset($_POST['did'])) ? $_POST["did"] : null;
        $clas   =   (isset($_POST['clas'])) ? $_POST["clas"] : null;
        $man    =   (isset($_POST['man'])) ? $_POST["man"] : null;
        $mod    =   (isset($_POST['mod'])) ? $_POST["mod"] : null;
        $tap    =   (isset($_POST['tap'])) ? $_POST["tap"] : null;


        if($tap && $did && $clas) {
            $clas   =   mysql_real_escape_string($clas);
            $q      =   mysql_query("update site_location set `name` = '$clas' where id = '$did'");
            if(is_resource($q)) {
                mysql_free_result($q);
            }
            setSession('location_updated', 1);
            header('location: editdept.php?id='.$depid.'&tab=tabloc#tabloc');
            exit();
        }
    }

    $eid    =   mysql_real_escape_string($eid);
    $q      =   mysql_query("select * from site_location where id = '$eid'");

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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<title>TEMS: Edit Location</title>
</head>
<body>
    <div id="body_content">
        <form method="post" action="editloc.php">
            <input type="hidden" name="tap" value="1" />
            <input type="hidden" name="did" value="<?php echo $eid ?>" />
            <input type="hidden" name="id" value="<?php echo $eid ?>" />
            <input type="hidden" name="depid" value="<?php echo $depid ?>" />
            <h1 class="page-title full-width">Edit Location</h1>
            <p class="clear">&nbsp;</p>
            <p>
                <label class="auto">Location Name</label>
                <input required="required" type="text" name="clas" value="<?php echo $d['name']; ?>" class="auto-width" />
            </p>
            <p>&nbsp;</th>
            <p>
                <input type="submit" value="Update" class="btn btn-primary" />
                <a href="editdept.php?id=<?php echo $depid;?>&amp;tab=tabloc" name="cancel" class="btn">Cancel</a>
            </p>
          </tr>
        </table>

        </form>
    </div>
    <script type="text/javascript">
        $(function() {
            if($('.alert-success').length > 0) {
                setTimeout(function() {
                    $('.alert-success').fadeOut('slow');
                }, 1500);
            }
        });
    </script>
</body>
</html>
