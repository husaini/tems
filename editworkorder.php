<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

if (empty($_GET['id'])) {
    die("<b>Fatal Error:</b> \"Relax,\" said the night man, \"We are programmed to receive. You can check-out any time you like, but you can never leave!\"");
}

function cleanfilename($filename) {
    $reserved = preg_quote('\/:*?"<>|', '/'); //characters that are  illegal on any of the 3 major OS's
    //replaces all characters up through space and all past ~ along with the above reserved characters
    return @preg_replace("/([\\x00-\\x20\\x7f-\\xff{$reserved}])/e", "_", $filename);
}

function checkworkscope($catbit, $bit) {
    return ($catbit & $bit);
}

$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id'], 10) : null;
$generate_pdf   =   getSession('generate_pdf', true);

if(!$id)
{
    die();
}


if($generate_pdf && !function_exists('output_workorder_pdf'))
{
    require(dirname(__FILE__).'/lib/lib.output.php');
    output_workorder_pdf($id);
}

date_default_timezone_set("Asia/Kuala_Lumpur");

$sql    =   'select workorder.*, validation, calibration '.
            'from workorder '.
            'join asset on assetid = asset.id '.
            'left join asset_type on asset_type.id = asset.typeid '.
            'where workorder.id = ?';
$stmt = $mysqli->prepare($sql);
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
    echo "<b>Fatal Error:</b> Work Order not found.";
    exit();
}
tabletoarray("user", $user);
sqltoarray("select id, name from vendor where (type = 0 or type = 2) and status = 1 order by name", $vendor);

$wsvld = $results['validation'];
$wsclb = $results['calibration'];

$wostatus[1] = "Scheduled";
$wostatus[2] = "Completed";
$wostatus[3] = "Cancelled";

$output =   (isset($_GET['output'])) ? trim(strtolower($_GET['output'])) : null;

switch ($output)
{
    case 'pdf':
        require(dirname(__FILE__).'/lib/lib.output.php');
        output_workorder_pdf($id);
    break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: View Work Order</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.validationengine.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validationengine.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-en.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 1500);
    }
    $('#dtrequire').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcomplete').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
<?php if (!isworker()): ?>
    $('#frmMod').find('input').attr('disabled', 'disabled');
    $('#frmMod').find('select').attr('disabled', 'disabled');
    $('#frmMod').find('textarea').attr('disabled', 'disabled');
});
<?php else: ?>
    $('#frmMod').validationEngine();
    $('#frmMod').submit(function(e) {
        var checked = $('input.chk-required:checked').length;
        if (!checked) {
            alert('Please check at least one work category.');
            return false;
        }
        return 1;
    });
});

function wocompleted() {
    if ($("select[name='wostatus']").val() == "2") {
        return true;
    } else {
        return false;
    }
}
<?php endif; ?>
</script>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">View Work Order (#<?php echo $id; ?>)</h1>
        <p class="clear">&nbsp;</p>
        <?php if(getSession('workorder_updated', true)): ?>
            <p class="alert alert-success">Workorder was successfully updated.</p>
        <?php endif; ?>
        <form id="frmMod" method="post" action="mod.php">
            <input type="hidden" name="func" value="edit_workorder" />
            <input type="hidden" name="woid" value="<?php echo $id; ?>" />
            <input type="hidden" name="assetid" value="<?php echo $results['assetid']; ?>" />
            <table class="full-width no-border">
                <tr>
                    <td>Work Category <span class="required">*</span></td>
                    <td>
                        <label><input type="checkbox" name="woprv"<?php echo (checkworkscope($results['category'], 1)) ? ' checked="checked"' : ''; ?> class="chk-required"> Preventive</label>
                        <label><input type="checkbox" name="wocrt"<?php echo (checkworkscope($results['category'], 2)) ? ' checked="checked"' : ''; ?> class="chk-required"> Corrective</label>
                        <?php
                        /*
                        <?php if ($wsvld): ?>
                            <input type="checkbox" name="wovld"<?php echo (checkworkscope($results['category'], 4)) ? 'checked="checked" ': '';?> class="chk-required">Validation
                        <?php endif; ?>
                        <?php if ($wsclb): ?>
                            <input type="checkbox" name="woclb"<?php echo (checkworkscope($results['category'], 8)) ? 'checked="checked" ': '';?> class="chk-required">Calibration
                        <?php endif; ?>
                        */
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Asset ID</td>
                    <td>
                        <a href="editasset.php?id=<?php echo $results['assetid']; ?>"><?php echo $results['assetid']; ?></a>
                    </td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>
                        <textarea name="wodesc" rows="2" cols="55"><?php echo $results['description']; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select name="wostatus" id="wostatus">
                            <?php optionize($wostatus, $results['status']); ?>
                        </select>
                    </td>
                </tr>
                <?php if($results['status'] == '2' && $results['completed']): ?>
                    <tr>
                        <td>Completed Date</td>
                        <td>
                            <?php echo date('Y/m/d', strtotime($results['completed']));?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php
                /*
                <tr>
                    <td>Vendor</td>
                    <td>
                        <select name="vendorid" id="vendorid">
                            <?php optionize($vendor, $results['vendorid']); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Date Required <span class="required">*</span></td>
                    <td>
                        <input required="required" type="text" name="dtrequire" id="dtrequire" size="10" maxlength="15" value="<?php echo $results['required']; ?>" class="validate[required,custom[date]]" />
                    </td>
                </tr>
                <tr>
                    <td>Date Completed</td>
                    <td>
                        <input type="text" name="dtcomplete" id="dtcomplete" size="10" maxlength="15" value="<?php echo $results['completed']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>Order No</td>
                    <td>
                        <input type="text" name="orderno" size="30" maxlength="20" value="<?php echo $results['orderno']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>Cost (RM)</td>
                    <td>
                        <input type="text" name="wocost" size="30" maxlength="20" value="<?php echo $results['cost']; ?>" />
                    </td>
                </tr>
                */
                ?>
                <tr>
                    <td>Creator</td>
                    <td><?php echo $user[$results['author']]; ?></td>
                </tr>
                <tr>
                    <td>Date Created</td>
                    <td><?php echo $results['created']; ?></td>
                </tr>

                <?php if (isworker()):?>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" value="Submit Data" class="btn btn-primary">
                            <input name="submit_pdf" type="submit" value="Submit And View PDF" class="btn btn-info">
                            <a href="workorder.php" class="btn"> Cancel</a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <a href="<?php echo $_SERVER['PHP_SELF'];?>?id=<?php echo $results['id'];?>&amp;output=pdf" id="excel" class="btn btn-primary">View PDF</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </form>

        <?php if (isworker()): ?>
            <h3>Delete Work Order</h3>
            <p>
                Please exercise discretion before you proceed to use this function. Data deletion is irreversible.
            </p>
            <form id="frmhidden" action="mod.php" method="post">
                <input type="hidden" name="func" value="del_workorder">
                <input type="hidden" name="assetid" value="<?php echo $results['assetid']; ?>">
                <?php if (isset($_GET['a'])): ?>
                    <input type="hidden" name="aid" value="<?php echo $results['assetid'];?>">
                <?php endif; ?>
                <input type="hidden" name="woid" value="<?php echo $results['id']; ?>">
                <input type="submit" value="Delete Work Order" class="btn btn-danger">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php

$stmt->close();
$mysqli->close();
