<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/conn.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $sid = (isset($_SESSION['sid']) && is_numeric($_SESSION['sid'])) ? intval($_SESSION['sid'], 10) : null;

    if(!$_POST) {
        die("Fatal Error: No assets selected.");
    }

    $uaccess        =   getSession('access');
    // Access level restriction to sites/departments and locations
    $usite_ids      =   array();
    $uloc_ids       =   array();
    $udept_ids      =   array();

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

    if($sid) {
        $usite_ids  =   array_merge($usite_ids, array($sid));
    }

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

    tabletoarray("asset_status", $assetstatus);
    tabletoarray("site", $site);
    sqltoarray("select id, name from vendor where (type = 0 or type = 1) and status = 1 order by name", $supplier);

    $sOpt   =   '';
    $in     =   '';

    if($usite_ids) {
        $sOpt   .=  ' AND asset.siteid IN('.implode(',', $usite_ids).') ';
    }

    $assetid = isset($_POST['assetid'])? $_POST['assetid']: "";
    if (is_array($assetid)) {
        //numeric only
        foreach ($assetid as $key => $value) {
            if(!is_numeric($value)) {
                unset($assetid[$key]);
                continue;
            }
            $assetid[$key] = intval($value, 10);
        }
        if($assetid) {
            $in =   implode(',', $assetid);
        }
    } else {
        //echo $assetid;
    }

    if(empty($in)) {
        die("Fatal Error: No assets selected.");
    }

    $sOpt       .=  " AND asset.id IN ($in) ";
    $sOrder     =   '';
    $ord[0]     =   'ASC';
    $ord[1]     =   'DESC';

    for ($a = 1; $a < 6; $a++) {
        if ($_POST['sort' . $a] != 0) {
            $sOrder .= "," . $_POST['sort' . $a] . " " . $ord[$_POST['ord' . $a]];
        }
    }
    if ($sOrder != "") {
        $sOrder = " ORDER BY " . trim($sOrder, ",");
    }

    //echo $sOrder;

    $sQuery =   'SELECT '.
                    'asset.id, '.
                    'assetno, '.
                    'asset_class.name AS classname, '.
                    'asset_type.name AS typename, '.
                    'asset_manufacturer.name AS manuname, '.
                    'asset_model.name AS modelname, '.
                    'serialno, '.
                    'site.name AS sitename, '.
                    'site_location.name AS locname, '.
                    'site_department.name AS department,'.
                    'price, '.
                    'purchasedate, '.
                    'vendor.name AS supplier, '.
                    'warrantystart, '.
                    'warrantyend, '.
                    'asset.status AS astatus, '.
                    'ppmstart, '.
                    'ppmfreq, '.
                    'lastsvc, '.
                    "IF(IFNULL(asset.ppmstart, '') > IFNULL(asset.lastsvc, ''), asset.ppmstart, asset.lastsvc) + INTERVAL (12/asset.ppmfreq) MONTH AS nextsvc, ".
                    '(SELECT MAX(required) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 1) AS latestwo, '.
                    '(SELECT COUNT(id) FROM workorder WHERE workorder.assetid = asset.id) AS wototal, '.
                    '(SELECT COUNT(id) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 1) AS wopending, '.
                    '(SELECT COUNT(id) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 2) AS wocomplete, '.
                    '(SELECT COUNT(id) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 3) ASwocancel, '.
                    '(SELECT SUM(cost) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 2) AS tcm '.
                'FROM asset '.
                    'LEFT JOIN asset_class ON asset.classid = asset_class.id '.
                    'LEFT JOIN asset_type ON asset.typeid = asset_type.id '.
                    'LEFT JOIN asset_manufacturer ON asset.manuid = asset_manufacturer.id '.
                    'LEFT JOIN asset_model ON asset.modelid = asset_model.id '.
                    'LEFT JOIN site_location ON asset.locationid = site_location.id '.
                    'LEFT JOIN site_department ON asset.department_id = site_department.id '.
                    'LEFT JOIN site ON site_location.siteid = site.id '.
                    'LEFT JOIN vendor ON asset.supplierid = vendor.id '.
                'WHERE 1 '.
                $sOpt.$sOrder;

    //echo $sQuery;
    $stmt = $mysqli->prepare($sQuery);
    $stmt->execute();
    $stmt->store_result();

    // Cols to display, order is top bottom
    $cols   =   array(
        'assetno'       =>  array(
            'label' =>  'TEMS No',
            'dbcol' =>  'assetno'
        ),
        'classid'       =>  array(
            'label' =>  'Category',
            'dbcol' =>  'classname',
        ),
        'typeid'        =>  array(
            'label' =>  'Subcategory',
            'dbcol' =>  'typename',
        ),
        'manuid'        =>  array(
            'label' =>  'Manufacturer',
            'dbcol' =>  'manuname',
        ),
        'modelid'       =>  array(
            'label' =>  'Model',
            'dbcol' =>  'modelname',
        ),
        'serialno'      =>  array(
            'label' =>  'Serial No',
            'dbcol' =>  'serialno',
        ),
        'site'          =>  array(
            'label' =>  'Site',
            'dbcol' =>  'sitename',
        ),
        'departmentid'  =>  array(
            'label' =>  'Department',
            'dbcol' =>  'department',
        ),
        'location'      =>  array(
            'label' =>  'Location',
            'dbcol' =>  'locname',
        ),
        'price'         =>    array(
            'label' =>  'Price',
            'dbcol' =>  'price',
        ),
        'purchased'     =>    array(
            'label' =>  'Purchase Date',
            'dbcol' =>  'purchasedate',
        ),
        'supplier'      =>    array(
            'label' =>  'Supplier',
            'dbcol' =>  'supplier',
        ),
        'warranty'      =>    array(
            'label' =>  'Warranty Period',
            'dbcol' =>  'warrantystart',
        ),
        'astatus'       =>    array(
            'label' =>  'Status',
            'dbcol' =>  'astatus',
        ),
        'ppmstart'      =>    array(
            'label' =>  'PPM Start Date',
            'dbcol' =>  'ppmstart',
        ),
        'ppmfreq'       =>    array(
            'label' =>  'PPM Frequency',
            'dbcol' =>  'ppmfreq',
        ),
        'lastsvc'       =>    array(
            'label' =>  'Last Serviced',
            'dbcol' =>  'lastsvc',
        ),
        'nextsvc'       =>    array(
            'label' =>  'Next Service',
            'dbcol' =>  'nextsvc',
        ),
        'latestwo'      =>    array(
            'label' =>  'Latest WO',
            'dbcol' =>  'latestwo',
        ),
        'tcm'           =>    array(
            'label' =>  'Total Maint. Cost',
            'dbcol' =>  'tcm',
        ),
        'wototal'       =>    array(
            'label' =>  'WO Total',
            'dbcol' =>  'wototal',
        ),
        'wopending'     =>    array(
            'label' =>  'WO Pending',
            'dbcol' =>  'wopending',
        ),
        'wocomplete'    =>    array(
            'label' =>  'WO Completed',
            'dbcol' =>  'wocomplete',
        ),
        'wocancel'      =>    array(
            'label' =>  'WO Cancelled',
            'dbcol' =>  'wocancel',
        ),
    );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Report</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/print.css" type="text/css" media="print">
</head>
<body>
    <div id="body_content">
        <h3>Asset List</h3>
        <?php if ($stmt->num_rows() > 0): ?>
            <table id="tblasset">
                <thead>
                    <tr>
                        <th>No</th>
                        <?php foreach ($cols as $key => $d): ?>
                            <?php if(isset($_POST[$key])): ?>
                                <th><?php echo $d['label'];?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $meta = $stmt->result_metadata();
                while ($column = $meta->fetch_field()) {
                    $bindvars[] = &$results[$column->name];
                }
                call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                $a = 0;
                while ($stmt->fetch()):
                    $a++;
                    ?>
                    <tr>
                        <td><?php echo $a;?>.</td>
                        <?php foreach ($cols as $key => $d): ?>
                            <?php if(isset($_POST[$key])): ?>
                                <td><?php echo $results[$d['dbcol']];?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>
                <i>List is empty.</i>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$mysqli->close();
