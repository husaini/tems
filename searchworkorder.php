<?php
require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

function ms_escape_string($data) {
    if (is_numeric($data)) return $data;
    if (!isset($data) or empty($data)) return '';

    $non_displayables = array(
        '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/',             // url encoded 16-31
        '/[\x00-\x08]/',            // 00-08
        '/\x0b/',                   // 11
        '/\x0c/',                   // 12
        '/[\x0e-\x1f]/'             // 14-31
    );
    foreach ($non_displayables as $regex)
        $data = preg_replace($regex, '', $data);
    $data = str_replace("'", "''", $data);
    return $data;
}

function workscoping($catbit) {
    $wscope = "";
    if ($catbit & 1) $wscope .= "Preventive<br />";
    if ($catbit & 2) $wscope .= "Corrective<br />";
    if ($catbit & 4) $wscope .= "Validation<br />";
    if ($catbit & 8) $wscope .= "Calibration<br />";
    return $wscope;
}

$sid = $_SESSION['sid'];
$maxrecord = 300;

$wostatus[1] = "Scheduled";
$wostatus[2] = "Completed";
$wostatus[3] = "Cancelled";

sqltoarray("select id, name from vendor where (type = 0 or type = 2) and status = 1 order by name", $vendor);

if ($sid) {
    $sWhere = "WHERE asset.siteid = $sid";
} else {
    $sWhere = "";
}
$cat = "";

if (isset($_POST['woprv'])) {
    $cat .= "(category & 1 OR ";
}

if (isset($_POST['wocrt'])) {
    $cat .= ($cat == "")? "(" : "";
    $cat .= "category & 2 OR ";
}

if (isset($_POST['wovld'])) {
    $cat .= ($cat == "")? "(" : "";
    $cat .= "category & 4 OR ";
}

if (isset($_POST['woclb'])) {
    $cat .= ($cat == "")? "(" : "";
    $cat .= "category & 8 OR ";
}

if ($cat != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= $cat;
    $sWhere .= "category = -1)";
}

if (is_numeric($_POST['wostatus']) && $_POST['wostatus'] != 0) {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "workorder.status = " . $_POST['wostatus'];
}

if (is_numeric($_POST['vendorid']) && $_POST['vendorid'] != 0) {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "workorder.vendorid = " . $_POST['vendorid'];
}

if ($_POST['orderno'] != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "workorder.orderno LIKE '%" . ms_escape_string($_POST['orderno']) . "%'";
}

if (is_numeric($_POST['wocostmin']) && $_POST['wocostmin'] != 0) {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "cost >= " . ms_escape_string($_POST['wocostmin']);
}

if (is_numeric($_POST['wocostmax']) && $_POST['wocostmax'] != 0) {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "cost <= " . ms_escape_string($_POST['wocostmax']);
}

if ($_POST['dtrequiremin'] != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "required >= '" . ms_escape_string($_POST['dtrequiremin']) . "'";
}

if ($_POST['dtrequiremax'] != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "required <= '" . ms_escape_string($_POST['dtrequiremax']) . "'";
}

if ($_POST['dtcompletemin'] != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "completed >= '" . ms_escape_string($_POST['dtcompletemin']) . "'";
}

if ($_POST['dtcompletemax'] != "") {
    $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
    $sWhere .= "completed <= '" . ms_escape_string($_POST['dtcompletemax']) . "'";
}

$sQuery =   'SELECT '.
                'workorder.id, '.
                'workorder.orderno, '.
                'workorder.created AS workorder_date,'.
                'asset.assetno AS tems_no,'.
                'asset.siteid AS asset_site_id,'.
                'asset_type.name AS typename, '.
                'asset_manufacturer.name AS manuname, '.
                'asset_model.name AS modelname, '.
                'serialno, '.
                'category, '.
                'vendor.name, '.
                'workorder.status, '.
                'required, '.
                'completed '.
            'FROM '.
                'workorder '.
            'INNER JOIN '.
                'asset on workorder.assetid = asset.id '.
            'LEFT JOIN '.
                'vendor ON workorder.vendorid = vendor.id '.
            'LEFT JOIN '.
                'asset_type ON asset_type.id = asset.typeid '.
            'LEFT JOIN '.
                'asset_manufacturer ON asset_manufacturer.id = asset.manuid '.
            'LEFT JOIN '.
                'asset_model ON asset_model.id = asset.modelid '.
            $sWhere;
//echo $sQuery;
$stmt = $mysqli->prepare($sQuery);
$stmt->execute();
$stmt->store_result();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Work Order</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#tabs').tabs();
    $('#dtrequiremin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtrequiremax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcompletemin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcompletemax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#tblwo tr').hover(function(){$(this).addClass("hl");},function(){$(this).removeClass("hl");});
    $('#tblwo tr').click(function(e){
        e.preventDefault();
        var woID = $(this).data('workorderId') || 0;
        if(woID)
        {
            window.location = "editworkorder.php?id=" + woID;
        }
    });

    if ($('#tblwo tbody.wo-list').length > 0) {
        var oTable = $('#tblwo').dataTable({
            bJQueryUI: true,
            iDisplayLength: 25,
            sPaginationType: 'full_numbers'
        });
        oTable.fnSort( [[1,'desc'] ] );//sort by date created
    }
});
</script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Search Work Order</h1>
            <ul>
                <li><a href="#tablist">Result</a></li>
                <li><a href="#tabsearch">Search</a></li>
            </ul>
            <div id="tablist">
                <h3>Search Result</h3>
                <?php if ($stmt->num_rows() > 0 && $stmt->num_rows() <= $maxrecord): ?>
                    <table id="tblwo" class="tlist">
                        <thead>

                            <tr>
                                <th class="ui-state-default">WO ID</th>
                                <th class="ui-state-default">Date Created</th>
                                <th class="ui-state-default">Equipment</th>
                                <th class="ui-state-default">Manufacturer</th>
                                <th class="ui-state-default">Model</th>
                                <th class="ui-state-default">Asset S/N</th>
                                <th class="ui-state-default">Category</th>
                                <th class="ui-state-default">Status</th>
                                <th class="ui-state-default">Required</th>
                                <th class="ui-state-default">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="wo-list">
                            <?php
                                $meta = $stmt->result_metadata();
                                while ($column = $meta->fetch_field()) {
                                    $bindvars[] = &$results[$column->name];
                                }
                                call_user_func_array(array($stmt, 'bind_result'), $bindvars);

                            while ($stmt->fetch()): ?>
                                <tr data-workorder-id="<?php echo $results['id']?>">
                                    <td>
                                        <?php
                                            $site_id    =   $results['asset_site_id'];
                                            if ($site_id < 10)
                                            {
                                                $site_id    =   '0'.$site_id;
                                            }
                                            // Concatenate strings for workorder id as in PDF format
                                            // TEMS no
                                            $wotems_no  =   array_pop(explode('-', $results['tems_no']));
                                            $wotems_no  =   (String)$wotems_no;
                                            // case when tems no not using correct format, it should be 5 chars as we we pad it on asset upload
                                            if (strlen($wotems_no) < 5)
                                            {
                                                $wotems_no   =   str_pad($wotems_no,5,'0', STR_PAD_LEFT);
                                            }

                                            //Workorder year
                                            $woyear     =   date('y', strtotime($results['workorder_date']));

                                            //Workorder no.
                                            $wonum      =   str_pad($results['id'],5,'0', STR_PAD_LEFT);
                                            $woid       =   "$site_id$wotems_no-$woyear-BEM-$wonum";
                                        ?>
                                        <a href="editworkorder.php?id=<?php echo $results['id'];?>"><?php echo $woid;?></a>
                                    </td>
                                    <td><?php echo date('Y/m/d', strtotime($results['workorder_date']));?></td>
                                    <td><?php echo $results['typename'];?></td>
                                    <td><?php echo $results['manuname']; ?></td>
                                    <td><?php echo $results['modelname']; ?></td>
                                    <td><?php echo $results['serialno'];?></td>
                                    <td><?php echo workscoping($results['category']);?></td>
                                    <?php /*<td><?php echo $results['name'];?></td>*/?>
                                    <td><?php echo $wostatus[$results['status']];?></td>
                                    <td><?php echo $results['required'];?></td>
                                    <td><?php echo $results['completed'];?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                <?php elseif ($stmt->num_rows() > $maxrecord): ?>
                    <p><i>Result set is too big (&gt; <?php echo $maxrecord;?> records). Please refine your search and try again.</i></p>
                <?php else: ?>
                    <p><i>Your search returns no result. Please refine your search and try again.</i></p>
                <?php endif; ?>
            </div>

            <div id="tabsearch">
                <h3>Search Work Order</h3>
                <form method="post" action="searchworkorder.php">
                    <table class="full-width no-border">
                        <tr>
                            <td>Work Category</td>
                            <td>
                                <input type="checkbox" name="woprv">Preventive
                                <input type="checkbox" name="wocrt">Corrective
                                <input type="checkbox" name="wovld">Validation
                                <input type="checkbox" name="swoclb">Calibration
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <select name="wostatus" id="wostatus">
                                    <option value="0">ANY</option>
                                    <option value="1">Scheduled</option>
                                    <option value="2">Completed</option>
                                    <option value="3">Cancelled</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Vendor</td>
                            <td>
                                <select name="vendorid" id="vendorid">
                                    <option value="0">ANY</option>
                                    <?php optionize($vendor); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Date Required</td>
                            <td>
                                <input type="text" name="dtrequiremin" id="dtrequiremin" size="10" maxlength="15" value="" />
                                to
                                <input type="text" name="dtrequiremax" id="dtrequiremax" size="10" maxlength="15" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Date Completed</td>
                            <td>
                                <input type="text" name="dtcompletemin" id="dtcompletemin" size="10" maxlength="15" value="" />
                                to
                                <input type="text" name="dtcompletemax" id="dtcompletemax" size="10" maxlength="15" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Order No</td>
                            <td>
                                <input type="text" name="orderno" size="30" maxlength="20" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Cost (RM)</td>
                            <td>
                                <input type="text" name="wocostmin" size="30" maxlength="20" value="" />
                                to
                                <input type="text" name="wocostmax" size="30" maxlength="20" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Submit Data" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$mysqli->close();
