<?php
require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

function fnColumnToField($i) {
    $i = $i - 2;
    switch ($i) {
        case 0:
            return 'assetno';
            break;
        case 1:
            return 'typename';
            break;
        case 2:
            return 'manuname';
            break;
        case 3:
            return 'modelname';
            break;
        case 4:
            return 'serialno';
            break;
        case 5:
            return 'sitename';
            break;
        case 6:
            return 'department';
            break;
        case 7:
            return 'locname';
            break;
        case 8:
            return 'lastsvc';
            break;
        case 9:
            return 'nextsvc';
            break;
        default:
            return false;
    }
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

$sid        =   $_SESSION['sid'];
$rem        =   $_SESSION['rem'];
$sLimit     =   "";
$sOutput    =   "";

if (isset($_GET['iDisplayStart'])) {
    $sLimit = "LIMIT " . ms_escape_string($_GET['iDisplayStart']) . ", " . ms_escape_string($_GET['iDisplayLength']);
}

if (isset($_GET['iSortCol_0'])) {
    $sOrder = '';
    $col    =   intval(ms_escape_string($_GET['iSortingCols']),10);
    for ($i=0; $i < $col; $i++) {
        $sort_col   =   intval(ms_escape_string($_GET['iSortCol_'.$i]),10);
        if($sort_col > 0) {
            //$sort_col   -=  1;
        }
        $sort_dir   =   ms_escape_string($_GET['sSortDir_'.$i]);
        //$sOrder .= fnColumnToField(ms_escape_string($_GET['iSortCol_'.$i])) . " " . ms_escape_string($_GET['sSortDir_'.$i]) . ", ";

        $field_name =   fnColumnToField($sort_col);

        if($field_name) {
            $sOrder     .=  $field_name . " " . $sort_dir . ", ";
        }
    }

    if($sOrder) {
        $sOrder = "ORDER BY  $sOrder";
        $sOrder = substr_replace($sOrder, "", -2);
    }
    //echo $sOrder;
}

$sWhere = "WHERE 1 ";
if ($_GET['sSearch'] != "") {
    $sWhere .= "AND (serialno LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "assetno LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "asset_class.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "asset_type.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "asset_manufacturer.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "asset_model.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "site.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "mote.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%' OR " .
        "site_location.name LIKE '%" . ms_escape_string($_GET['sSearch']) . "%') ";
}

if (isset($_GET['search_temsno']) && $_GET['search_temsno']) {
    $str    =   trim($_GET['search_temsno']);
    if ($str) {
        $sWhere .= 'AND '.
                    "`assetno` LIKE '%" . ms_escape_string($str)."%' ";
    }
}

if ($sid > 0 && $sid != 65535) {
    if ($sWhere != "")
        $sWhere .= " AND asset.siteid = $sid";
    else
        $sWhere .= "WHERE asset.siteid = $sid";
}

if ($sid == 65535) {
    if ($sWhere != "")
        $sWhere .= " AND asset.siteid in ($rem)";
    else
        $sWhere .= "WHERE asset.siteid in ($rem)";
}

// Access level restriction to sites/departments and locations
$usite_ids          =   array();
$uloc_ids           =   array();
$udept_ids          =   array();
$loc_join_clause    =   '';
$dept_join_clause   =   '';
$site_join_clause   =   '';
$uaccess            =   getSession('access');

if ($uaccess) {
    if(isset($uaccess['sites'])) {
        foreach ($uaccess['sites'] as $usite) {
            $usite_ids[]    =   $usite['id'];
        }
        if($usite_ids) {
            $sWhere .=  ' AND asset.siteid IN('.implode(',', $usite_ids).') ';
        }
    }
    if(isset($uaccess['locations'])) {
        foreach ($uaccess['locations'] as $uloc) {
            $uloc_ids[]    =   $uloc['id'];
        }
        if($uloc_ids) {
            $sWhere .=  ' AND asset.locationid IN('.implode(',', $uloc_ids).') ';
        }
    }
    if(isset($uaccess['departments'])) {
        foreach ($uaccess['departments'] as $udept) {
            $udept_ids[]    =   $udept['id'];
        }
        if($udept_ids) {
            //$sWhere .=  ' AND asset.department_id IN('.implode(',', $udept_ids).') ';
            $dept_join_clause .=  ' AND site_department.id IN('.implode(',', $udept_ids).') ';
        }
    }
}


$sQuery =   'SELECT SQL_CALC_FOUND_ROWS '.
                'asset.id, '.
                'assetno, '.
                'asset_type.name AS typename, '.
                'asset_manufacturer.name AS manuname, '.
                'asset_model.name AS modelname, '.
                'serialno, '.
                'site.id AS site_id,'.
                'site.name AS sitename, '.
                'site_department.id AS site_department_id,'.
                'site_department.name AS department, '.
                'site_location.id AS site_location_id,'.
                'site_location.name AS locname, '.
                'lastsvc, '.
                "IF(IFNULL(asset.ppmstart, '') > IFNULL(asset.lastsvc, ''), asset.ppmstart, asset.lastsvc) + INTERVAL (12/asset.ppmfreq) MONTH AS nextsvc ".
            'FROM '.
                'asset '.
            'LEFT JOIN '.
                'asset_class ON asset.classid = asset_class.id '.
            'LEFT JOIN '.
                'asset_type ON asset.typeid = asset_type.id '.
            'LEFT JOIN '.
                'asset_manufacturer ON asset.manuid = asset_manufacturer.id '.
            'LEFT JOIN '.
                'asset_model ON asset.modelid = asset_model.id '.
            'LEFT JOIN '.
                'mote ON asset.moteid = mote.id '.
            'INNER JOIN '.
                'site ON asset.siteid = site.id '.
            'LEFT JOIN '.
                'site_location ON site_location.id = asset.locationid '.
            'LEFT JOIN '.
                'site_department ON site_department.id = asset.department_id '.
            $sWhere.' '.
            $sOrder.' '.
            $sLimit;

#echo $sQuery;
$result =   $mysqli->query($sQuery) or die(mysqli_error($mysqli));

$result_count   =   $mysqli->query('SELECT FOUND_ROWS()');
list($total)    =   $result_count->fetch_row();
mysqli_free_result($result_count);

$assets =   array();

while ($row = $result->fetch_assoc()) {
    $assets[]   =   array(
        '',
        $row['id'],
        $row['assetno'],
        $row['typename'],
        $row['manuname'],
        $row['modelname'],
        $row['serialno'],
        $row['sitename'],
        $row['department'],
        $row['locname'],
        $row['lastsvc'],
        $row['nextsvc'],
    );

}

$iFilteredTotal =   $iTotal = $total; //$total[0]

$response   =   array(
    'aaData'                =>  $assets,
    'iTotalDisplayRecords'  =>  (int)$iFilteredTotal,
    'iTotalRecords'         =>  (int)$iTotal,
    'sEcho'                 =>  intval($_GET['sEcho'], 10),
    'total' => $total,
);

mysqli_free_result($result);
$mysqli->close();

exit(json_encode($response));
