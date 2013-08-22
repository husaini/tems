<?php
//header('Cache-Control: no-cache, must-revalidate');
//header('Content-type: application/json');

require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

$id = empty($_GET['id'])? 0 : $_GET['id'];
$ie = empty($_GET['ie'])? 0 : $_GET['ie'];
$f = empty($_GET['f'])? 0 : $_GET['f'];
$type = empty($_GET['type'])? 0 : $_GET['type'];

$sql_join   =   'JOIN '.
                    'asset_type ON asset_type.classid = asset_class.id '.
                'JOIN '.
                    'asset_model ON asset_model.typeid = asset_type.id '.
                'JOIN '.
                    'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id ';

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
            $loc_opt   =   ' AND site_location.id IN('.implode(',', $uloc_ids).') ';
        }
    }
    if(isset($uaccess['departments'])) {
        foreach ($uaccess['departments'] as $udept) {
            $udept_ids[]    =   $udept['id'];
        }
        if ($udept_ids) {
            $dept_opt   =   ' AND site_department.id IN('.implode(',', $udept_ids).') ';
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

switch($f) {
    case "manutype":
        $sql    =   'SELECT DISTINCT '.
                        'asset_manufacturer.id, '.
                        'asset_manufacturer.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        'asset_model.typeid = ? '.
                    'AND '.
                        'asset_manufacturer.id <> ? '.
                    $asset_class_opt.
                    'ORDER BY '.
                        'asset_manufacturer.name';

        break;

    case "modeltype":
        $sql    =   'SELECT DISTINCT '.
                        'asset_model.id, '.
                        'asset_model.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        'asset_model.typeid = ? '.
                    'AND '.
                        'asset_model.manuid = ? '.
                    $asset_class_opt.
                    'ORDER BY '.
                        'asset_model.name';
        break;

    case "typeclass":
        $sql    =   'SELECT DISTINCT '.
                        'asset_type.id, '.
                        'asset_type.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        'asset_type.classid = ? '.
                    'AND '.
                        'asset_type.id <> ? '.
                    $asset_class_opt.
                    'ORDER BY '.
                        'asset_type.name';
        break;

    case "location":
        $sql    =   'SELECT '.
                        '`id`, '.
                        '`name` '.
                    'FROM '.
                        'site_location '.
                    'WHERE '.
                        'siteid = ? '.
                    'AND '.
                        'id <> ? '.
                    $loc_opt.
                    'ORDER BY '.
                        '`name`';
        break;

    case "department":
        $sql    =   'SELECT '.
                        '`id`, '.
                        '`name` '.
                    'FROM '.
                        'site_department '.
                    'WHERE '.
                        'siteid = ? '.
                    'AND '.
                        'id <> ? '.
                    $dept_opt.
                    'ORDER BY '.
                        '`name`';
        break;

    case "manulist":
        $sql    =   'SELECT DISTINCT '.
                        'asset_manufacturer.id, '.
                        'asset_manufacturer.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        '1 '.
                    'AND '.
                        'asset_manufacturer.name LIKE ? '.
                    'ORDER BY '.
                        'asset_manufacturer.name';

        break;

    case "modellist":
        $sql    =   'SELECT DISTINCT '.
                        'asset_model.id, '.
                        'asset_model.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        '1 '.
                    'AND '.
                        'asset_model.manuid = ? '.
                    $asset_class_opt.
                    'ORDER BY '.
                        'asset_model.name';

        break;

    default:
        $sql    =   'SELECT DISTINCT '.
                        'asset_class.id, '.
                        'asset_class.name '.
                    'FROM '.
                        'asset_class '.
                    $sql_join.
                    'WHERE '.
                        'asset_class.id <> ? '.
                    'AND '.
                        'asset_class.id <> ? '.
                    $asset_class_opt.
                    'ORDER BY '.
                        'asset_class.name';
        break;
}

$stmt = $mysqli->prepare($sql);
if ($f == 'manulist' && isset($_GET['term']) && $_GET['term'])
{
    $term = '%'.$_GET['term'].'%';
    $stmt->bind_param('s', $term);
}
elseif ($f == 'modellist')
{
    $stmt->bind_param('i', $ie);
}
else
{
    $stmt->bind_param('ii', $id, $ie);
}
$stmt->execute();
$stmt->store_result();

$response   =   array(
    array(
        'optionDisplay' =>  'ANY',
        'optionValue'   =>  0,
    )
);

if($type == 'autocomplete')
{
    //reset response
    $response   =   array();
}

$a = -1;
if ($stmt->num_rows() > 0) {
    $meta = $stmt->result_metadata();
    while ($column = $meta->fetch_field()) {
        $bindvars[] = &$results[$column->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $bindvars);

    while ($stmt->fetch()) {
        $a++;

        if($type == 'autocomplete')
        {
            $response[] =   array(
                'id'    =>  $results['id'],
                'label' =>  $results['name'],
                'value' =>  $results['name'],
            );
        }
        else
        {
            $response[] =   array(
                'optionDisplay' =>  $results['name'],
                'optionValue'   =>  $results['id']
            );
        }
    }
}
$stmt->close();
$mysqli->close();

exit(json_encode($response));
