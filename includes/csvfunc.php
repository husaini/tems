<?php
/** Include PHPExcel_IOFactory */
require_once (dirname(__FILE__).'/../lib/phpexcel/Classes/PHPExcel/IOFactory.php');
require_once(dirname(__FILE__).'/sharedfunc.php');

// Limit column to read for excel files
class MyReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow  =   0;
    private $_endRow    =   0;
    private $_columns   =   array();

    public function __construct($startRow, $endRow, $columns)
    {
        $this->_startRow    =   $startRow;
        $this->_endRow      =   $endRow;
        $this->_columns     =   $columns;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if($this->_endRow)
        {
            if ($row >= $this->_startRow && $row <= $this->_endRow)
            {
                if (in_array($column,$this->_columns))
                {
                    return true;
                }
            }
        }
        else
        {
            //no limit
            if (in_array($column,$this->_columns))
            {
                return true;
            }
        }
        return false;
    }
}

class chunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow  =   0;
    private $_endRow    =   0;

    /** Set the list of rows that we want to read */
    public function setRows($startRow, $chunkSize)
    {
        $this->_startRow    =   $startRow;
        $this->_endRow      =   $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        // Only read the heading row, and the configured rows
        if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow))
        {
            return true;
        }
        return false;
    }
}

if(!isset($mysqli))
{
    require_once(dirname(__FILE__).'/conn.php');
}

//$upload_dir =   dirname(__FILE__).'/upload/';
//$xcel       =   PHPExcel_IOFactory::load($upload_dir.'asset.xlsx');
$data       =   array();

function csv_add_asset($args)
{
    //die(debug($args,'asset to add'));

    if(!$args || !is_array($args))
    {
        return;
    }

    $required_fields    =   array(
        'assetno',
        //'serialno',
        'siteid',
        //'locationid'
    );

    $missing_field  =   false;

    foreach ($required_fields as $rf)
    {
        if (!isset($args[$rf]) || empty($args[$rf]))
        {
            $missing_field  =   true;
            //$missing[]  =   $rf;
            break;
        }
    }

    if($missing_field)
    {
        return false;
    }

    global $mysqli;

    // serialno is unique field in aseet table, so make sure no duplicate entry, skip if already exist!
    /*
    if(isAssetSerialExist($args['serialno']))
    {
        return;
    }
    */

    $fields =   array(
        'assetno',
        'classid',
        'typeid',
        'manuid',
        'modelid',
        'serialno',
        'refno',
        'orderno',
        'purchasedate',
        'warrantystart',
        'warrantyend',
        'ppmstart',
        'ppmfreq',
        'supplierid',
        'price',
        'status',
        'siteid',
        'locationid',
        'department_id',
        'remarks',
        'author',
        'created',
    );

    $vars           =   '';
    $binds          =   '';
    $data           =   array();
    $numeric_fields =   array(
        'classid',
        'typeid',
        'manuid',
        'modelid',
        'supplierid',
        'siteid',
        'locationid',
        'department_id',
        'author',
    );

    foreach ($fields as $f)
    {
        if(isset($args[$f]))
        {
            $data[$f]   =   $args[$f];
            $vars       .=  '?,';

            if(in_array($f, $numeric_fields))
            {
                $data[$f]   =   intval($args[$f], 10);
                $binds  .=  'i';
            }
            else
            {
                $data[$f]   =   (string)$args[$f];
                $binds  .=  's';
            }
        }
    }

    if(!$data)
    {
        return; //nothing to insert??
    }

    foreach ($data as $key => $value)
    {
        $data[$key] =   "'".mysqli_real_escape_string($mysqli, $value)."'";
    }

    $vars   =   rtrim($vars, ',');
    $cols   =   '`'.implode('`,`', array_keys($data)).'`';
    $values =   implode(',', $data);

    $sql    =   'INSERT INTO `asset` ('.
                    $cols.
                    ',created '.
                ') VALUES  ('.
                    $values.', NOW())';

    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli));

    if (!$result) {
        echo "<b>Error in database operation:</b> " . $mysqli->error;
        exit;
    }

    $id =   $mysqli->insert_id;

    if(is_resource($result))
    {
        mysqli_free_result($result);
    }
    return $id;
}

function csv_add_class($name)
{
    global $mysqli;

    if(!is_string($name) || !$name)
    {
        return;
    }

    // We already set unknown class with id 0. this is for unknow class
    // just in case user deleted the record, we re-insert it!
    $sql            =   'SELECT COUNT(1) FROM asset_class WHERE id = 0';
    $result         =   $mysqli->query($sql);
    list($count)    =   $result->fetch_row();

    if($count && stripos('unknown', $name) !== false)
    {
        //found it. so skip this;
        return 0;
    }
    elseif (!$count)
    {
        //user delete this, re-add it!
        $sql    =   'INSERT INTO `asset_class`(`id`, `name`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('is', 0, 'UNKNOWN CLASS');
        $stmt->execute();
        return 0; //default type id for unknown model
    }

    $name       =   mysqli_real_escape_string($mysqli, $name);
    $sql        =   "SELECT `id` FROM `asset_class` WHERE TRIM(LOWER(`name`)) = '$name'";
    $result     =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $class_id   =   null;

    if ($result)
    {
        list($class_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$class_id)
    {
        // New class
        $sql    =   'INSERT INTO `asset_class`(`name`) VALUES(?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('s', $name);

        if ($stmt->execute())
        {
            $class_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }
    return $class_id;
}

function csv_add_department($dept, $site_id)
{
    global $mysqli;

    if(!is_string($dept) || !$dept || !is_numeric($site_id) || !$site_id)
    {
        return;
    }

    $dept       =   mysqli_real_escape_string($mysqli, $dept);
    $site_id    =   mysqli_real_escape_string($mysqli, $site_id);
    $sql        =   "SELECT `id` FROM `site_department` WHERE TRIM(LOWER(`name`)) = '$dept' AND `siteid`='$site_id'";
    $result     =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $dept_id    =   null;

    if ($result)
    {
        list($dept_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$dept_id)
    {
        // New department
        $sql    =   'INSERT INTO `site_department`(`name`, `siteid`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('si', $dept, $site_id);

        if ($stmt->execute())
        {
            $dept_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }

    return $dept_id;
}

function csv_add_location($loc, $dep_id)
{
    global $mysqli;

    if(!is_string($loc) || !$loc || !is_numeric($dep_id) || !$dep_id)
    {
        return;
    }

    $loc        =   mysqli_real_escape_string($mysqli, $loc);
    $dep_id    =   mysqli_real_escape_string($mysqli, $dep_id);
    $sql        =   "SELECT `id` FROM `department_location` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$loc')) AND `depid`='$dep_id'";
    $result     =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $loc_id     =   null;

    if ($result)
    {
        list($loc_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$loc_id)
    {
        // New location
        $sql    =   'INSERT INTO `department_location`(`name`, `depid`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('si', $loc, $dep_id);

        if ($stmt->execute())
        {
            $loc_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }
    return $loc_id;
}

function csv_add_manufacturer($name, $origin=null)
{
    global $mysqli;

    if(!is_string($name) || !$name)
    {
        return;
    }

    // We already set unknown manufacturer with id 0. this is for unknow manufacturer
    // just in case user deleted the record, we re-insert it!
    $sql            =   'SELECT COUNT(1) FROM asset_manufacturer WHERE id = 0';
    $result         =   $mysqli->query($sql);
    list($count)    =   $result->fetch_row();

    if($count && stripos('unknown', $name) !== false)
    {
        //found it. so skip this;
        $type_id   =   0;
        return $type_id;
    }
    elseif (!$count)
    {
        //user delete this, re-add it!
        $sql    =   'INSERT INTO `asset_manufacturer`(`id`, `name`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('is', 0, 'UNKNOWN MANUFACTURER');
        $stmt->execute();
        return 0; //default type id for unknown manufacturer
    }

    $name       =   mysqli_real_escape_string($mysqli, $name);
    $origin     =   mysqli_real_escape_string($mysqli, $origin);
    $sql        =   "SELECT `id` FROM `asset_manufacturer` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$name'))";
    $result     =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $man_id     =   null;

    if ($result)
    {
        list($man_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$man_id)
    {
        // New location
        $sql    =   'INSERT INTO `asset_manufacturer`(`name`, `origin`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('ss', $name, $origin);

        if ($stmt->execute())
        {
            $man_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }
    return $man_id;
}

function csv_add_model($name)
{
    global $mysqli;

    // changes 20130313 - allow model saving with just name
    //if(!is_string($name) || !$name || !is_numeric($man_id) || !$man_id || !is_numeric($type_id) || !$type_id)
    if(!is_string($name) || !$name )
    {
        return;
    }

    // We already set unknown model with id 0. this is for unknow model
    // just in case user deleted the record, we re-insert it!
    $sql            =   'SELECT COUNT(1) FROM asset_model WHERE id = 0';
    $result         =   $mysqli->query($sql);
    list($count)    =   $result->fetch_row();

    if($count && stripos('unknown', $name) !== false)
    {
        //found it. so skip this;
        $model_id   =   0;
        return $model_id;
    }
    elseif (!$count)
    {
        //user delete this, re-add it!
        $sql    =   'INSERT INTO `asset_model`(`id`, `name`, `manuid`, `typeid`) VALUES(?,?,?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('isii', 0, 'UNKNOWN MODEL', 0, 0);
        $stmt->execute();
        return 0; //default type id for unknown model
    }

    $name           =   mysqli_real_escape_string($mysqli, $name);
    $sql            =   "SELECT `id` FROM `asset_model` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$name')) ";

    $result         =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $model_id       =   null;

    if ($result)
    {
        list($model_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$model_id)
    {
        // New location
        $sql    =   'INSERT INTO `asset_model`(`name`) VALUES(?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('s', $name);

        if ($stmt->execute())
        {
            $model_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }
    return $model_id;
}

function csv_add_type($name, $class_id)
{
    global $mysqli;

    if(!is_string($name) || !$name || !is_numeric($class_id) || !$class_id)
    {
        return;
    }

    // We already set unknown type with id 0. this is for unknow type
    // just in case user deleted the record, we re-insert it!
    $sql            =   'SELECT COUNT(1) FROM asset_type WHERE id = 0';
    $result         =   $mysqli->query($sql);
    list($count)    =   $result->fetch_row();

    if($count && stripos('unknown', $name) !== false)
    {
        //found it. so skip this;
        $type_id   =   0;
        return $type_id;
    }
    elseif (!$count)
    {
        //user delete this, re-add it!
        $sql    =   'INSERT INTO `asset_type`(`id`, `name`, `classid`) VALUES(?,?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('isi', 0, 'UNKNOWN TYPE', 0);
        $stmt->execute();
        return 0; //default type id for unknown type
    }

    $name       =   mysqli_real_escape_string($mysqli, $name);
    $site_id    =   mysqli_real_escape_string($mysqli, $class_id);
    $sql        =   "SELECT `id` FROM `asset_type` WHERE TRIM(LOWER(`name`)) = '$name' AND `classid`='$class_id'";
    $result     =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $type_id    =   null;

    if ($result)
    {
        list($type_id)  =   $result->fetch_row();
        mysqli_free_result($result);
    }

    if(!$type_id)
    {
        // New type
        $sql    =   'INSERT INTO `asset_type`(`name`, `classid`) VALUES(?,?)';
        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('si', $type_id, $class_id);

        if ($stmt->execute())
        {
            $type_id    =   $mysqli->insert_id;
        }
        $stmt->close();
    }
    return $type_id;
}

function csv_add_user_department($dept_id)
{
    global $mysqli;
    $uid    =   getSession('uid');

    if($uid === null || !$dept_id)
    {
        return;
    }
    addUserAccess('department', $dept_id, $uid);
}

function csv_add_user_location($loc_id)
{
    global $mysqli;
    $uid    =   getSession('uid');

    if($uid === null  || !$loc_id)
    {
        return;
    }

    addUserAccess('location', $loc_id, $uid);
}

function csv_add_user_site($site_id)
{
    global $mysqli;
    $uid    =   getSession('uid');

    if($uid === null || !$site_id)
    {
        return;
    }

    addUserAccess('site', $site_id, $uid);
}

function csv_clean_value($str)
{
    $str    =   trim($str);
    if(strlen($str) <= 1)
    {
        $str    =   null;
    }
    return $str;
}


function csv_get_assetno($id)
{
    global $mysqli;

    if(!$id)
    {
        return;
    }
    $id     =   mysqli_real_escape_string($mysqli, $id);
    $sql    =   "SELECT `assetno` FROM `asset` WHERE id = '".$id."'";
    $result =   $mysqli->query($sql);

    if($result)
    {
        list($assetno)  =   $result->fetch_row();
        mysqli_free_result($result);
        return $assetno;
    }
}

function csv_get_asset_serial($id)
{
    global $mysqli;

    if(!$id)
    {
        return;
    }
    $id     =   mysqli_real_escape_string($mysqli, $id);
    $sql    =   "SELECT `serialno` FROM `asset` WHERE id = '".$id."'";
    $result =   $mysqli->query($sql);

    if($result)
    {
        list($serialno)  =   $result->fetch_row();
        mysqli_free_result($result);
        return $serialno;
    }
}

function csv_load($fileinput, $show_empty_serial=false, $is_temp_file=false, $autosave=true)
{
    global $mysqli;

    define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
    date_default_timezone_set('Asia/Kuala_Lumpur');
    ini_set('memory_limit','1024M');
    ini_set('max_execution_time', 0);

    $rows               =   array();
    $valid_extensions   =   array('xls', 'xlsx', 'csv', 'ods');
    $max_rows           =   0; //300;
    $saved_asset        =   0;

    if(!$is_temp_file)
    {
        $file   =   (isset($fileinput['tmp_name'])) ? $fileinput['tmp_name'] : null;

        if(!$file || !is_string($file))
        {
            return;
        }

        $pathinfo           =   pathinfo($fileinput['name']);
    }
    else
    {
        $pathinfo['extension']  =   array_pop(explode('.', $fileinput));
        $file                   =   $fileinput;
    }

    if(!in_array($pathinfo['extension'], $valid_extensions))
    {
        die('Not a valid file.');
    }

    //ini_set('memory_limit', '1024M');

    $filter_subset  =   new MyReadFilter(0, $max_rows, range('A', 'H'));

    if($pathinfo['extension'] == 'csv')
    {
        $input_filetype =   'CSV';
        $reader         =   PHPExcel_IOFactory::createReader($input_filetype);
        $reader->setReadFilter($filter_subset);
        //$reader->setReadDataOnly(true);

        try
        {
            $xcel           =   $reader->load($file);
        }
        catch(Exception $e)
        {
            die('Error loading file:'.$e->getMessage());
        }
    }
    elseif($pathinfo['extension'] == 'xls')
    {
        $reader         =   PHPExcel_IOFactory::createReader('Excel2005');
        $reader->setReadFilter($filter_subset);
        //$reader->setReadDataOnly(true);
        try
        {
            $xcel           =   $reader->load($file);
        }
        catch(Exception $e)
        {
            die('Error loading file:'.$e->getMessage());
        }
    }
    elseif($pathinfo['extension'] == 'xlsx')
    {
        $reader         =   PHPExcel_IOFactory::createReader('Excel2007');
        $reader->setReadFilter($filter_subset);
        //$reader->setReadDataOnly(true);

        try
        {
            $xcel           =   $reader->load($file);
        }
        catch(Exception $e)
        {
            die('Error loading file:'.$e->getMessage());
        }
    }
    else
    {
        try
        {
            $xcel   =   PHPExcel_IOFactory::load($file);
        }
        catch(Exception $e)
        {
            die('Error loading file:'.$e->getMessage());
        }
    }

    $rows           =   $xcel->getActiveSheet()->toArray(null, false, false, true);
    $data           =   array();
    $already_added  =   array();

    //die(debug($rows));

    if ($rows)
    {
        //serial number is set to unique in DB, if somehow we got duplicated serial no in csv
        // just add remark its duplicated

        $serials            =   array();
        $dummy_serial_count =   1;
        $seq_start          =   1;

        foreach ($rows as $col)
        {
            $col            =   array_map('csv_clean_value', $col);
            $site           =   trim($col['A']);
            $site_code      =   trim($col['B']);
            $serialno       =   trim($col['H']); //serial no is unique in DB, cannot let this empty!!
            $is_duplicated  =   false;
            $site_exist     =   false;

            if(!$site || stripos($col['A'], 'site') !== false)
            {
                continue; //probably some useless heading
            }

            if(!$show_empty_serial)
            {
                if(!$serialno)
                {
                    continue;
                }
            }

            $serialno   =   trim(preg_replace('/\s+/', '', $serialno));

            // Get site id
            $asite          =   mysqli_real_escape_string($mysqli, $site);
            $sql            =   "SELECT id FROM `site` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$asite'));";
            $result         =   $mysqli->query($sql) or die(mysqli_error($mysqli));
            list($site_id)  =   $result->fetch_row();
            mysqli_free_result($result);

            if (!$site_id)
            {
                // Insert new site
                $sql    =   'INSERT INTO `site`(`id`, `name`, `created`) VALUES(?, ?, NOW())';
                $stmt   =   $mysqli->prepare($sql) or die(mysqli_error($mysqli));
                $stmt->bind_param('is', $site_code, $site);
                if (!$stmt->execute())
                {
                    die("Failed to add site \"$site\"" . $mysqli->error);
                }
                $site_id    =   mysqli_insert_id($mysqli);
                $site_exist =   true;

            }
            else
            {
                $site_exist =   true;
            }

            // Since this is new site, give current user access to this site
            csv_add_user_site($site_id);

            if($is_duplicated)
            {
                //get other asset which duplicate this
                foreach ($data as $site_name => $site_assets)
                {
                    $key    =   recursive_array_search($serialno, $data[$site_name]);
                    if ($key !== false)
                    {
                        $data[$site_name][$key]['duplicated']    =   true;
                    }
                }
            }

            $site_code      =   ($site_id < 10) ? '0'.$site_id : "$site_id";
            $curr_assetno   =   null;

            $tems_no    =   null;

            if($seq_start === 1)
            {
                if(!$curr_assetno)
                {
                    //get sequence number to start
                    $sql            =   "SELECT MAX(assetno) FROM asset WHERE assetno LIKE('".$site_code."-%')";
                    $result         =   $mysqli->query($sql) or die(mysqli_error($mysqli));
                    list($last_seq)  =   $result->fetch_row();
                    mysqli_free_result($result);

                    $seq_start      =   intval(substr($last_seq, 3, strlen($last_seq)), 10) + 1;
                }
                else
                {
                    //OK serial number found in DB. how do we get the sequence start?
                    $tems_no    =   $curr_assetno;

                }
            }

            /*
            $is_new             =   false;
            $tems_no_exist      =   null;

            if(!$tems_no)
            {
                $is_new         =   true;
                $tems_no        =   $site_code.'-'.str_pad($seq_start,6,'0', STR_PAD_LEFT);
                $tems_no_exist  =   isAssetExist($tems_no);//this will return asset id if found
            }
            */
            $tems_no        =   $site_code.'-'.str_pad($seq_start,5,'0', STR_PAD_LEFT);
            $tems_no_exist  =   0;//isAssetExist($tems_no);

            $arg =   array(
                'curr_asset_no' =>  $curr_assetno,
                'site_exist'    =>  $site_exist,
                'tems_no'       =>  $tems_no,
                'tems_no_exist' =>  $tems_no_exist ? true : false,
                'site'          =>  $site,
                'siteid'        =>  $site_id,
                'department'    =>  $col['D'],
                'duplicated'    =>  $is_duplicated,
                'location'      =>  $col['E'],
                'item'          =>  $col['F'],
                'manufacturer'  =>  null,
                'model'         =>  (empty($col['G'])) ? 'Unknown' : $col['G'],
                'serial_no'     =>  $serialno,
                'last_service'  =>  null,
                'next_service'  =>  null,
            );

            if($curr_assetno || $tems_no_exist)
            {
                if($tems_no_exist)
                {
                    if(!$is_new && $a_an = csv_get_assetno($tems_no_exist))
                    {
                        $arg['tems_no'] =   $a_anl;
                    }
                    elseif($curr_assetno)
                    {
                        $arg['tems_no'] =   $curr_assetno;
                    }

                    if(!trim($col['H']))
                    {
                        $arg['serial_no']       =   csv_get_asset_serial($tems_no_exist);
                    }
                }
                //$already_added[$site][] =   $arg;

                if($curr_assetno == $arg['tems_no'])
                {
                    $arg['tems_no_exist']   =   true;
                }
            }
            else
            {
                if ($dept_id = csv_add_department($arg['department'], $arg['siteid']))
                {
                    $arg['department_id']  =   $dept_id;
                }

                if ($loc_id = csv_add_location($arg['location'], $dept_id))
                {
                    $arg['locationid']  =   $loc_id;
                }

                //add user to this location
                csv_add_user_location($loc_id);
                //add user to this department
                csv_add_user_department($dept_id);

                // add model
                $arg['modelid']     =   csv_add_model($arg['model']);

                $arg['remarks']     =   $arg['item'];
                $arg['assetno']     =   (string)$arg['tems_no'];
                $arg['serialno']    =   (string)$arg['serial_no'];
                $arg['author']      =   getSession('uid');
                $arg['status']      =   1;

                if($autosave === true)
                {
                    if(csv_add_asset($arg))
                    {
                        $arg['tems_no_exist']   =   true;
                        $saved_asset++;
                    }
                }
                else
                {
                    $arg['tems_no_exist']   =   true;
                }

            }

            $data[$site][]  =   $arg;
            $seq_start++;
        }
    }

    //debug($already_added, 'alreadyadeed');

    $data   =   array_merge_recursive($data, $already_added);
    //just show only new asset to prevent duplicate

    //debug($data);

    if($saved_asset)
    {
        setSession('success', $saved_asset. ' assets were successfully added.');
    }

    if($autosave === true)
    {
        //prevent user refresh page
        header('location: csv.php');
        exit();
    }
    return $data;
}
