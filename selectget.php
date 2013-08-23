<?php
//header('Cache-Control: no-cache, must-revalidate');
//header('Content-type: application/json');

require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

$id             =   empty($_GET['id'])? 0 : $_GET['id'];
$ie             =   empty($_GET['ie'])? 0 : $_GET['ie'];
$f              =   empty($_GET['f'])? 0 : $_GET['f'];
$group_result   =   (isset($_GET['group_options'])) ? true : false;

if(!is_array($id)) {
    $id = array($id);
}
foreach ($id as $key => $value) {
    if(!is_numeric($value)) {
        unset($id[$key]);
        continue;
    }
    $id[$key] = intval($value, 10);
}

if(!$id) {
    exit(0);
}

// Access level restriction to sites/departments and locations
$usite_ids      =   array();
$uloc_ids       =   array();
$udept_ids      =   array();
$uaccess        =   getSession('access');
$asset_class_opt=   '';
$site_opt       =   '';
$loc_opt        =   '';
$dept_opt       =   '';

if ($uaccess) {
    if(isset($uaccess['sites'])) {
        foreach ($uaccess['sites'] as $usite) {
            $usite_ids[]    =   $usite['id'];
        }
        if ($usite_ids) {
            $site_opt   =   ' AND site.id IN('.implode(',', $usite_ids).') ';
        }
    }
    if(isset($uaccess['locations'])) {
        foreach ($uaccess['locations'] as $uloc) {
            $uloc_ids[]    =   $uloc['id'];
        }
        if ($uloc_ids) {
            $loc_opt   =   ' AND dl.id IN('.implode(',', $uloc_ids).') ';
        }
    }
    if(isset($uaccess['departments'])) {
        foreach ($uaccess['departments'] as $udept) {
            $udept_ids[]    =   $udept['id'];
        }
        if ($udept_ids) {
            $dept_opt   =   ' AND sd.id IN('.implode(',', $udept_ids).') ';
        }
    }
}

if ($usite_ids) {
    // get classid from asset table
    $uclass_ids = array();
    $result     =   $mysqli->query('SELECT `classid` FROM `asset` WHERE siteid IN('.implode(',', $usite_ids).')') or die(mysqli_error($mysqli));
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $uclass_ids[]   =   $row['classid'];
        }
        mysqli_free_result($result);
    }

    if($uclass_ids) {
        $uclass_ids         =   array_unique($uclass_ids);
        $asset_class_opt    =  'AND asset_class.id IN('.implode(',', $uclass_ids).') ';
    }
}

$ie     =   (!is_numeric($ie)) ? 0 : intval($ie, 10);
$ids    =   implode(',',$id);

switch($f) {
    case "manutype":

        $sql    =   'SELECT DISTINCT '.
                        'asset_manufacturer.id, '.
                        'asset_manufacturer.name '.
                    'FROM '.
                        'asset_class '.
                    'JOIN '.
                        'asset_type ON asset_type.classid = asset_class.id '.
                    'JOIN '.
                        'asset_model ON asset_model.typeid = asset_type.id '.
                    'JOIN '.
                        'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id '.
                    'WHERE '.
                        "asset_model.typeid IN($ids) ".
                    'AND '.
                        "asset_manufacturer.id <> '$ie' ".
                        $asset_class_opt.' '.
                    'ORDER BY '.
                        'asset_manufacturer.name';

        break;

    case "modeltype":

        $sql    =   'SELECT DISTINCT '.
                        'asset_model.id, '.
                        'asset_model.name '.
                    'FROM '.
                        'asset_class '.
                    'JOIN '.
                        'asset_type ON asset_type.classid = asset_class.id '.
                    'JOIN '.
                        'asset_model ON asset_model.typeid = asset_type.id '.
                    'JOIN '.
                        'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id '.
                    'WHERE '.
                        "asset_model.typeid IN($ids) ".
                    'AND '.
                        "asset_model.manuid = '$ie' ".
                        $asset_class_opt.' '.
                    'ORDER BY '.
                        'asset_model.name';

        break;

    case "typeclass":

        $sql    =   'SELECT DISTINCT '.
                        'asset_type.id, '.
                        'asset_type.name '.
                    'FROM '.
                        'asset_class '.
                    'JOIN '.
                        'asset_type ON asset_type.classid = asset_class.id '.
                    'JOIN '.
                        'asset_model ON asset_model.typeid = asset_type.id '.
                    'JOIN '.
                        'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id '.
                    'WHERE '.
                        "asset_type.classid IN($ids) ".
                    'AND '.
                        "asset_type.id <> '$ie' ".
                        $asset_class_opt.' '.
                    'ORDER BY '.
                        'asset_type.name';
        break;

    case "location":
        $sql    =   'SELECT '.
                        'dl.id,'.
                        'dl.name, '.
                        'sd.name AS department,'.
                        'sd.id AS department_id '.
                    'FROM '.
                        'department_location dl '.
                    'INNER JOIN '.
                        'site_department sd ON sd.id = dl.depid '.
                    'WHERE 1 '.
                    'AND '.
                        "sd.id IN($ids) ".
                    'AND '.
                        "dl.id <>  '$ie' ".
                        $loc_opt.
                    'ORDER BY '.
                        'dl.name';
        break;
    case "department":

        $sql    =   'SELECT '.
                        's.name AS sitename,'.
                        'sd.siteid,'.
                        'sd.id, '.
                        'sd.name '.
                    'FROM '.
                        'site_department sd '.
                    'INNER JOIN '.
                        'site s ON s.id = sd.siteid '.
                    'WHERE '.
                        "sd.siteid IN($ids) ".
                    'AND '.
                        "sd.id <> '$ie' ".
                        $dept_opt.
                    'ORDER BY '.
                        'sd.name';
        break;

    default:

        $sql    =   'SELECT DISTINCT '.
                        'asset_class.id, '.
                        'asset_class.name '.
                    'FROM '.
                        'asset_class '.
                    'JOIN '.
                        'asset_type ON asset_type.classid = asset_class.id '.
                    'JOIN '.
                        'asset_model ON asset_model.typeid = asset_type.id '.
                    'JOIN '.
                        'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id '.
                    'WHERE '.
                        "asset_class.id NOT IN($ids) ".
                    'AND '.
                        "asset_class.id <> '$ie' ".
                        $asset_class_opt.' '.
                    'ORDER BY '.
                        'asset_class.name';
        break;
}

//echo $sql;

$result     =   $mysqli->query($sql) or die(mysqli_error($mysqli).' '.$sql);
$response   =   array();

if ($result) {
    if ($group_result && $f=='department') {
        $sites  =   array();
        $rows   =   array();

        while ($row = $result->fetch_assoc()) {
            if(!isset($sites[$row['siteid']])) {
                $sites[$row['siteid']]['site']    =   $row['sitename'];
            }
            $rows[] =   $row;
        }
        asort($sites);

        foreach ($sites as $site_id => $s) {
            foreach ($rows as $row) {
                if($row['siteid'] == $site_id) {
                    $s['options'][] =   array(
                        'optionDisplay' =>  $row['name'],
                        'optionValue'   =>  $row['id'],
                    );
                }
            }
            $sites[$site_id] = $s;
        }
        //print_r($sites);

        $response   =   $sites;
    }elseif ($group_result && $f=='location') {
        $locs  =   array();
        $rows   =   array();

        while ($row = $result->fetch_assoc()) {
            if(!isset($locs[$row['department_id']])) {
                $locs[$row['department_id']]['site']    =   $row['department'];
            }
            $rows[] =   $row;
        }
        asort($locs);

        //print_r($rows);

        foreach ($locs as $department_id => $l) {
            foreach ($rows as $row) {
                if($row['department_id'] == $department_id) {
                    $l['options'][] =   array(
                        'optionDisplay' =>  $row['name'],
                        'optionValue'   =>  $row['id'],
                    );
                }
            }
            $locs[$department_id] = $l;
        }

        $response   =   $locs;

    } else {
        while ($row = $result->fetch_assoc()) {
            $response[] =   array(
                'optionDisplay' =>  $row['name'],
                'optionValue'   =>  $row['id'],
            );
        }
    }
    mysqli_free_result($result);
}

$mysqli->close();

exit(json_encode($response));
