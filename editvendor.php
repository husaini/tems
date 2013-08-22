<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

if (empty($_GET['id'])) {
    echo "<b>Fatal Error:</b> \"Relax,\" said the night man, \"We are programmed to receive. You can check-out any time you like, but you can never leave!\"";
    exit();
}

$id = $_GET['id'];

date_default_timezone_set("Asia/Kuala_Lumpur");

$stmt = $mysqli->prepare("select * from vendor where id = ?");
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
$vtype[0] = "Any";
$vtype[1] = "Supplier";
$vtype[2] = "Maintenance";
$vstatus[0] = "Inactive";
$vstatus[1] = "Active";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Edit Vendor</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<?php if (!isworker()) { ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#dtrequire').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcomplete').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#frm_vendor').find('input').attr('disabled', 'disabled');
    $('#frm_vendor').find('select').attr('disabled', 'disabled');
    $('#frm_vendor').find('textarea').attr('disabled', 'disabled');
});
</script>
<?php } ?>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">View Vendor</h1>
        <p class="clear">&nbsp;</p>
        <form id="frm_vendor" method="post" action="mod.php" onsubmit="return verifyform()">
            <input type="hidden" name="func" value="edit_vendor" />
            <input type="hidden" name="vid" value="<?php echo $results['id']; ?>" />
            <table class="full-width no-border">
                <tr>
                    <td>Company Name <span class="required">*</span></td>
                    <td><input required="required" type="text" name="vname" size="60" maxlength="100" value="<?php echo $results['name']; ?>" /></td>
                </tr>
                <tr>
                    <td>Contact Person</td>
                    <td>
                        <input type="text" name="vperson" size="30" maxlength="100" value="<?php echo $results['person']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>Phone No</td><td><input type="text" name="vphone" size="30" maxlength="20" value="<?php echo $results['phone']; ?>" /></td>
                </tr>
                <tr>
                    <td>Fax No</td><td><input type="text" name="vfax" size="30" maxlength="20" value="<?php echo $results['fax']; ?>" /></td>
                </tr>
                <tr>
                    <td>Address</td><td><textarea name="vaddr" rows="2" cols="55"><?php echo $results['address']; ?></textarea></td>
                </tr>
                <tr>
                    <td>Email</td><td><input type="text" name="vemail" size="30" maxlength="20" value="<?php echo $results['email']; ?>" /></td>
                </tr>
                <tr>
                    <td>Type</td><td><select name="vtype"><?php optionize($vtype, $results['type']); ?></select></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select name="vstatus">
                            <?php optionize($vstatus, $results['status']); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Remarks (if any)</td>
                    <td>
                        <textarea name="vrem" rows="2" cols="55"><?php echo $results['remarks']; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">&nbsp;
                        <?php if (isworker()): ?>
                            <input type="submit" value="Update Data" class="btn btn-primary">
                        <?php endif; ?>
                        <a href="vendor.php" class="btn">Cancel</a>
                    </td>
                </tr>
            </table>
        </form>
        <?php if (isworker()): ?>
            <p><span class="required">*</span> Mandatory Field</p>
            <h3>Delete Vendor</h3>
            <p>Please exercise discretion before you proceed to use this function. Data deletion is irreversible.</p>
            <p>However, also note that you can never delete data already in used or referred to in other areas of information (i.e. assets, work order). Change the status instead.</p>
            <form id="frmhidden" action="mod.php" method="post">
                <input type="hidden" name="func" value="del_vendor">
                <input type="hidden" name="vid" value="<?php echo $results['id']; ?>">
                <input type="submit" value="Delete Vendor" class="btn btn-danger">
            </form>
        <?php endif; ?>
        <?php
            $stmt->close();
            $mysqli->close();
        ?>
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
