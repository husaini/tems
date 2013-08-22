<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');
date_default_timezone_set("Asia/Kuala_Lumpur");

$sid            =   $_SESSION['sid'];
$rem            =   $_SESSION['rem'];
$listid         =   (isset($_GET['listid']))? explode(",", $_GET['listid']) : array(-1, 0);

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

if($rem) {
    $temp   =   explode(',', $rem);
    foreach ($temp as $key => $value) {
        if(!is_numeric($value)) {
            unset($temp[$key]);
        }
    }
    $rem    =   array_merge($temp, $usite_ids);
    $rem    =   implode(',', $rem);
}

function get_sorting_options() {
    $html       =   '';
    $options    =   array(
        '0'     =>  'None',
        '2'     =>  'TEMS No.',
        '3'     =>  'Serial No.',
        '4'     =>  'Category',
        '5'     =>  'Subcategory',
        '6'     =>  'Manufacturer',
        '7'     =>  'Model',
        '8'     =>  'Site',
        '9'     =>  'Department',
        '10'    =>  'Location',
        '11'    =>  'Price',
        '12'    =>  'Purchase Date',
        '13'    =>  'Supplier',
        '14'    =>  'Warranty Start',
        '15'    =>  'Warranty End',
        '16'    =>  'PPM Start',
        '17'    =>  'PPM Frequency',
        '18'    =>  'Last Service',
        '19'    =>  'Next Service',
        '20'    =>  'Latest WO',
        '21'    =>  'Total Maintenance Cost',
        '22'    =>  'Total No of WO',
        '23'    =>  'Total Pending WO',
        '24'    =>  'Total Completed WO',
        '25'    =>  'Total Cancelled WO',
        '26'    =>  'Asset Status',
    );

    foreach ($options as $key => $value) {
        $html   .=  "<option value='$key'>$value</option>";
    }
    return $html;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Report</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.multiselect.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.multiselect.min.js"></script>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">Print Asset List</h1>
        <p class="clear">&nbsp;</p>
        <form id="frmReport" method="post" action="print.php" target="_blank">
            <input type="hidden" name="list" value="" />
            <table class="full-width no-border">
                <tr>
                    <td>Asset(s)</td>
                    <td>
                        <select type="text" name="assetid[]" id="assetid" class="multiselect" multiple="multiple">
                        <?php
                            if ($sid > 0 && $sid != 65535) {
                                $stmt = $mysqli->prepare("select asset.id, assetno, asset_class.name classname, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno from asset
                                    left join asset_class on asset.classid = asset_class.id
                                    left join asset_type on asset.typeid = asset_type.id
                                    left join asset_manufacturer on asset.manuid = asset_manufacturer.id
                                    left join asset_model on asset.modelid = asset_model.id
                                    where asset.siteid = ?
                                    order by asset_class.name, asset_type.name, asset_manufacturer.name, asset_model.name, serialno");
                                $stmt->bind_param('i', $sid);
                            } else if ($sid == 65535) {
                                $stmt = $mysqli->prepare("select asset.id, assetno, asset_class.name classname, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno from asset
                                    left join asset_class on asset.classid = asset_class.id
                                    left join asset_type on asset.typeid = asset_type.id
                                    left join asset_manufacturer on asset.manuid = asset_manufacturer.id
                                    left join asset_model on asset.modelid = asset_model.id
                                    where asset.siteid in (" . $rem . ")
                                    order by asset_class.name, asset_type.name, asset_manufacturer.name, asset_model.name, serialno");
                            } else {
                                $clause =   '';
                                if ($usite_ids) {
                                    $clause .=  'AND asset.siteid IN('.implode(',', $usite_ids).') ';
                                }

                                $sql    =   'SELECT '.
                                                'asset.id, '.
                                                'assetno, '.
                                                'asset_class.name AS classname, '.
                                                'asset_type.name AS typename, '.
                                                'asset_manufacturer.name AS manuname, '.
                                                'asset_model.name AS modelname, '.
                                                'serialno '.
                                            'FROM '.
                                                'asset '.
                                            'LEFT JOIN '.
                                                'asset_class ON asset.classid = asset_class.id '.
                                            'LEFT JOIN '.
                                                'asset_type on asset.typeid = asset_type.id '.
                                            'LEFT JOIN '.
                                                'asset_manufacturer on asset.manuid = asset_manufacturer.id '.
                                            'LEFT JOIN '.
                                                'asset_model on asset.modelid = asset_model.id '.
                                            'WHERE 1 '.
                                                $clause.' '.
                                            'ORDER BY '.
                                                'asset_class.name, '.
                                                'asset_type.name, '.
                                                'asset_manufacturer.name, '.
                                                'asset_model.name, '.
                                                'serialno';
                                $stmt   =   $mysqli->prepare($sql);
                            }
                            $stmt->execute();
                            $stmt->store_result();

                            if ($stmt->num_rows() > 0) {
                                $meta = $stmt->result_metadata();
                                while ($column = $meta->fetch_field()) {
                                    $bindvars[] = &$results[$column->name];
                                }
                                call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                                while ($stmt->fetch()): ?>
                                    <?php
                                        $selected   =   (in_array($results['id'], $listid)) ? ' selected="selected" ' : '';

                                    ?>
                                    <option<?php echo $selected;?> value="<?php echo $results['id'];?>">
                                        <?php echo $results['classname'] . " " . $results['manuname'] . " " . $results['modelname'] . " (" . $results['serialno'] . ")";?>
                                    </option>
                                <?php endwhile; ?>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Column</td>
                    <td>
                        <table class="no-border">
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="ano" checked="yes" disabled> No</td>
                                <td nowrap="nowrap"><input type="checkbox" name="assetno" checked="yes"> TEMS No</td>
                                <td nowrap="nowrap"><input type="checkbox" name="serialno" checked="yes"> Serial No</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="classid" checked="yes"> Category</td>
                                <td nowrap="nowrap"><input type="checkbox" name="typeid" checked="yes"> Subcategory</td>
                                <td nowrap="nowrap"><input type="checkbox" name="manuid" checked="yes"> Manufacturer</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="modelid" checked="yes"> Model</td>
                                <td nowrap="nowrap"><input type="checkbox" name="site" checked="yes"> Site</td>
                                <td nowrap="nowrap"><input type="checkbox" name="location" checked="yes"> Location</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="departmentid"> Department</td>
                                <td nowrap="nowrap"><input type="checkbox" name="price"> Price</td>
                                <td nowrap="nowrap"><input type="checkbox" name="purchased"> Purchase Date</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="supplier"> Supplier</td>
                                <td nowrap="nowrap"><input type="checkbox" name="warranty"> Warranty Period</td>
                                <td nowrap="nowrap"><input type="checkbox" name="ppmstart"> PPM Start Date</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="ppmfreq"> PPM Frequency</td>
                                <td nowrap="nowrap"><input type="checkbox" name="lastsvc"> Last Service Date</td>
                                <td nowrap="nowrap"><input type="checkbox" name="nextsvc"> Next Service Date</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="latestwo"> Latest Work Order</td>
                                <td nowrap="nowrap"><input type="checkbox" name="tcm"> Total Maintenance Cost</td>
                                <td nowrap="nowrap"><input type="checkbox" name="wototal"> Total No Of WO</td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap"><input type="checkbox" name="wopending"> Total Pending WO</td>
                                <td nowrap="nowrap"><input type="checkbox" name="wocomplete"> Total Completed WO</td>
                                <td nowrap="nowrap"><input type="checkbox" name="wocancel"> Total Cancelled WO</td>

                            </tr>
                            <tr>
                                <td><input type="checkbox" name="astatus"> Asset Status</td>
                                <td>&nbsp; </td>
                                <td>&nbsp; </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>Sorting</td>
                    <td>
                        1: <select name="sort1"><?php echo get_sorting_options(); ?></select>
                        <select name="ord1">
                            <option value="0">ASC</option>
                            <option value="1">DESC</option>
                        </select><br />
                        2: <select name="sort2"><?php echo get_sorting_options(); ?></select>
                        <select name="ord2">
                            <option value="0">ASC</option>
                            <option value="1">DESC</option>
                        </select><br />
                        3: <select name="sort3"><?php echo get_sorting_options(); ?></select>
                        <select name="ord3">
                            <option value="0">ASC</option>
                            <option value="1">DESC</option>
                        </select><br />
                        4: <select name="sort4"><?php echo get_sorting_options(); ?></select>
                        <select name="ord4">
                            <option value="0">ASC</option>
                            <option value="1">DESC</option>
                        </select><br />
                        5: <select name="sort5"><?php echo get_sorting_options(); ?></select>
                        <select name="ord5">
                            <option value="0">ASC</option>
                            <option value="1">DESC</option>
                        </select><br />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Generate Report" class="btn btn-primary">
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script type="text/javascript">
        $(function(){
            $("#assetid").multiselect({'minWidth':550});
            $('#frmReport').submit(function() {
                try {
                    if ($('#assetid').multiselect('getChecked').length == 0) {
                        alert('No assets selected.');
                        return false;
                    }
                } catch(e) {

                }
            });
        });
    </script>
</body>
</html>
<?php
    $stmt->close();
    $mysqli->close();
?>
