<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/conn.php');
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

    $uloc_ids       =   array();
    $uaccess        =   getSession('access');
    if ($uaccess) {
        if(isset($uaccess['locations'])) {
            foreach ($uaccess['locations'] as $uloc) {
                $uloc_ids[]    =   $uloc['id'];
            }
        }
    }
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
        <?php if(getSession('location_updated', true)): ?>
            <p class="alert alert-success">Location was successfully updated.</p>
        <?php endif; ?>
        <?php if(getSession('location_added', true)): ?>
            <p class="alert alert-success">Location was successfully added.</p>
        <?php endif; ?>
        <?php if(getSession('location_deleted', true)): ?>
            <p class="alert alert-success">Location <em>"<?php echo getSession('deleted_location', true);?>"</em> was successfully deleted.</p>
        <?php endif; ?>
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
            <input type="hidden" name="tap" value="1" />
            <input type="hidden" name="id" value="<?php echo $eid ?>" />
            <input type="hidden" name="sid" value="<?php echo $sid ?>" />
            <input type="hidden" name="did" value="<?php echo $eid ?>" />
        </form>
        <div class="clear">&nbsp;</div>

        <h2>Department Locations</h2>

        <h3>Add New Location</h3>
        <form method="post" action="mod.php">
            <input type="hidden" name="func" value="add_loc" />
            <input type="hidden" name="depid" value="<?php echo $eid; ?>" />
            <p>
                <label class="auto">Location Name</label>
                <input required="required" type="text" name="lname" size="60" maxlength="100" value="" />
                <button type="submit" class="btn btn-primary">Submit</button>
            </p>
        </form>
        <table id="tblloc" class="tlist2 full-width">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Location Name</th>
                    <th></th>
                </tr>
            </thead>
            <?php
                $loc_opt    =   '';
                if($uloc_ids) {
                    //$loc_opt    .=  'AND dl.id IN('.implode(',', $uloc_ids).')';
                }
                $sql    =   'SELECT '.
                                'dl.*'.
                            'FROM '.
                                'department_location dl '.
                            'INNER JOIN '.
                                'site_department sd ON dl.depid = sd.id '.
                            'WHERE '.
                                "dl.depid = ? $loc_opt ".
                            'ORDER BY '.
                                'dl.name';

                $stmt       =   $mysqli->prepare($sql);
                $stmt->bind_param('i', $eid);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows() > 0): ?>
                    <tbody class="loc-list">
                        <?php
                            $meta = $stmt->result_metadata();
                            while ($column = $meta->fetch_field()) {
                                $bindvars[] = &$results[$column->name];
                            }
                            call_user_func_array(array($stmt, 'bind_result'), $bindvars);

                            while ($stmt->fetch()): ?>

                                <tr>
                                    <td width="10" nowrap="nowrap">
                                        <?php echo $results['id'];?>
                                    </td>
                                    <td>
                                        <a href="editloc.php?id=<?php echo $results['id'];?>&amp;depid=<?php echo $eid;?>&amp;sid=<?php echo $sid;?>&amp;tab=tabdept"><?php echo $results['name'];?></a>
                                    </td>
                                    <td>
                                        <a href="delloc.php?id=<?php echo $results['id'];?>&amp;depid=<?php echo $eid;?>&amp;sid=<?php echo $sid;?>&amp;tab=tabdept">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                <?php else: ?>
                    <tbody>
                        <tr>
                            <td colspan="3" align="center">No locations found.</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
