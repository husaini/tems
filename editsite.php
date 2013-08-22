<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

if (!isset($_GET['id']) || !$_GET['id']) {
    echo "<b>Fatal Error:</b> \"Relax,\" said the night man, \"We are programmed to receive. You can check-out any time you like, but you can never leave!\"";
    exit();
}

date_default_timezone_set("Asia/Kuala_Lumpur");

$id     =   (isset($_GET['id'])) ? $_GET['id'] : null;

if(!$id) {
    header("Location: site.php");
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

// Check if user has access to sites
if(!$usite_ids || !in_array($id, $usite_ids)) {
    die('You are not allowed to edit this site.');
}

$stmt   =   $mysqli->prepare("select * from site where id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->store_result();
$meta = $stmt->result_metadata();
while ($column = $meta->fetch_field()) {
  $bindvars[] = &$results[$column->name];
}
call_user_func_array(array($stmt, 'bind_result'), $bindvars);
if (!$stmt->fetch()) {
    $stmt->close();
    $mysqli->close();
    echo "<b>Fatal Error:</b> Site not found.";
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Edit Site</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<?php if (!isguest()): ?>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    var hash = window.location.hash;
    if(!hash || location.href.indexOf("#") == -1) {
        hash = '#<?php echo (isset($_GET['tab'])) ? $_GET['tab'] : "";?>';
    }
    var activeTabIndex = 0;
    if (hash) {
        hash = hash.substring(1);
        $('#tabs li a').each(function(i) {
            var thisHash = this.href.split('#')[1];
            if(thisHash == hash) {
                activeTabIndex = i;
            }
        });
    }
    $('#tabs').tabs({active: activeTabIndex});
    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 1500);
    }
});

function confdel(obj, sid) {
    if (confirm("Are you sure you want to delete this?\nPress OK to proceed or CANCEL to abort.")) {
        document.getElementById('frmhidden').wid.value = sid;
        document.getElementById('frmhidden').submit();
    }
}
</script>
<?php endif; ?>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">View Site</h1>
            <ul>
                <li><a href="#tabdetails">Site Details</a></li>
                <li><a href="#tabdept">Departments</a></li>
            </ul>
            <div id="tabdetails">
                <h3>Site Details</h3>
                <?php if(getSession('site_updated', true)): ?>
                    <p class="alert alert-success">Site was successfully updated.</p>
                <?php endif; ?>
                <?php if(getSession('site_deleted', true)): ?>
                    <p class="alert alert-success">Site <em>"<?php echo getSession('deleted_site', true);?>"</em> was successfully deleted.</p>
                <?php endif; ?>
                <form method="post" action="mod.php">
                    <input type="hidden" name="func" value="edit_site" />
                    <input type="hidden" name="sid" value="<?php echo $id; ?>" />
                    <p class="clear">&nbsp;</p>
                    <p>
                        <label class="auto">Site Name</label>
                        <input required="required" type="text" name="sname" size="60" maxlength="100" value="<?php echo $results['name']; ?>" />
                    </p>
                    <p>
                        <label class="auto">Phone No</label>
                        <input type="text" name="sphone" size="30" maxlength="20" value="<?php echo $results['phone']; ?>" />
                    </p>
                    <p>
                        <label class="auto">Fax No</label>
                        <input type="text" name="sfax" size="30" maxlength="20" value="<?php echo $results['fax']; ?>" />
                    </p>
                    <p>
                        <label class="auto">Address</label>
                        <textarea name="saddr" rows="2" cols="55"><?php echo $results['address']; ?></textarea>
                    </p>
                    <p>
                        <input type="submit" value="Submit Data" class="btn btn-primary">
                        <a href="site.php" class="btn"> Cancel</a>
                    </p>
                </form>
            </div>
            <div id="tabdept">
                <h3>Department List</h3>
                <?php if(getSession('department_updated', true)): ?>
                    <p class="alert alert-success">Department was successfully updated.</p>
                <?php endif; ?>
                <?php if(getSession('department_added', true)): ?>
                    <p class="alert alert-success">Department was successfully added.</p>
                <?php endif; ?>
                <?php if(getSession('department_deleted', true)): ?>
                    <p class="alert alert-success">Department <em>"<?php echo getSession('deleted_department', true);?>"</em> was successfully deleted.</p>
                <?php endif; ?>
                <table class="tlist2 full-width">
                    <thead>
                        <tr>
                            <th colspan="2">Department Name</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $stmt->free_result();
                        $stmt->close();
                        unset($bindvars);
                        unset($results);

                        if($udept_ids):
                            //You might wana disable the following line for admin so added deps will show up in list
                            $dept_opt    =  'AND id IN('.implode(',', $udept_ids).')';

                            $stmt = $mysqli->prepare("select * from site_department where siteid = ? $dept_opt");
                            $stmt->bind_param('i', $id);
                            $stmt->execute();
                            $stmt->store_result();

                            if ($stmt->num_rows() > 0):
                                $meta = $stmt->result_metadata();
                                while ($column = $meta->fetch_field()) {
                                    $bindvars[] = &$results[$column->name];
                                }
                                call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                                $departments = array();//department list buffer
                                while ($stmt->fetch()):
                                    $departments[] = array('id'=>$results['id'],'name'=>$results['name']);

                                ?>
                                    <tr>
                                        <td>
                                            <a href="editdept.php?id=<?php echo $results['id'];?>&amp;sid=<?php echo $id;?>&amp;tab=tabdept"><?php echo $results['name'];?></a>
                                        </td>
                                        <td>
                                            <a href="deldept.php?id=<?php echo $results['id'];?>&amp;sid=<?php echo $id;?>&amp;tab=tabdept">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" align="center">No departments found.</td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <h3>Add New Department</h3>
                <form method="post" action="mod.php">
                    <input type="hidden" name="func" value="add_dept" />
                    <input type="hidden" name="sid" value="<?php echo $id; ?>" />
                    <p>
                        <label class="auto">Department Name</label>
                        <input required="required" type="text" name="name" size="60" maxlength="100" value="" />
                    </p>
                    <p>
                        <input type="submit" value="Submit Data" class="btn btn-primary">
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
