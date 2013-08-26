<?php
function addUserAccess($type,$id,$uid=null)
{
    global $mysqli;

    if(!isset($mysqli) || !$mysqli)
    {
        require_once(dirname(__FILE__).'/conn.php');
    }
    $current_user   =   false;

    if($uid === null)
    {
        $uid    =   getSession('uid') || null;
        $current_user   =   true;
    }

    if($uid === null || !$id || !is_numeric($id))
    {
        return;
    }

    $uid    =   intval($uid, 10);
    $id     =   intval($id, 10);

    if ($uid == getSession('uid'))
    {
        $current_user   =   true;
    }

    switch ($type)
    {
        case 'site':
            $table  =   'user_site';
            $col    =   'siteid';
            $clause =   "AND siteid = $id ";
            break;

        case 'department':
            $table  =   'user_department';
            $col    =   'depid';
            $clause =   "AND depid = $id ";
            break;

        case 'location':
            $table  =   'user_location';
            $col    =   'locid';
            $clause =   "AND locid = $id ";
            break;
        default:
            $table = null;
            $clause =   '';
            $col    =   null;
    }

    if ($table)
    {
        //get current access
        $uaccess    =   getSession('access');
        $cur_data   =   array();

        if (isset($uaccess[$col]))
        {
            $cur_data   =   $uaccess[$col];
        }

        $cur_data[] =   $id;

        //check if user has access set
        $result         =   $mysqli->query("SELECT COUNT(1) FROM `$table` WHERE uid = $uid $clause") or die(mysqli_error($mysqli));
        list($count)    =   $result->fetch_row();
        mysqli_free_result($result);

        if (!$count)
        {
            $sql    =   "INSERT INTO `$table`(`$col`,uid) VALUES(?,?)";

            $stmt   =   $mysqli->prepare($sql) or die(mysqli_error($mysqli).$sql);
            $stmt->bind_param('si',$id, $uid);

            if(!$stmt->execute())
            {
                $errormsg = $mysqli->error;
                echo "Error in user access data insert: " . $errormsg;
                exit;
            }

            //update user session if uid is current user
            if ($current_user)
            {
                setSession('access', getUserAccessList($uid));
            }
        }

        //auto add item to admins
        addAdminAccess($type,$id);
    }
}

function addAdminAccess($type,$id)
{
    global $mysqli;

    if(!isset($mysqli) || !$mysqli)
    {
        require_once(dirname(__FILE__).'/conn.php');
    }
    $current_user   =   getSession('uid');

    // Get all admins
    $admins =   getAdmins();

    if (!$admins)
    {
        return;
    }
    $id     =   intval($id, 10);

    switch ($type)
    {
        case 'site':
            $table  =   'user_site';
            $col    =   'siteid';
            $clause =   "AND siteid = $id ";
            break;

        case 'department':
            $table  =   'user_department';
            $col    =   'depid';
            $clause =   "AND depid = $id ";
            break;

        case 'location':
            $table  =   'user_location';
            $col    =   'locid';
            $clause =   "AND locid = $id ";
            break;
        default:
            $table = null;
            $clause =   '';
            $col    =   null;
    }

    if ($table)
    {
        // Loop admins
        foreach ($admins as $uid)
        {
            //add if not yet exist
            $result         =   $mysqli->query("SELECT COUNT(1) FROM `$table` WHERE uid = $uid $clause") or die(mysqli_error($mysqli));
            list($count)    =   $result->fetch_row();
            mysqli_free_result($result);

            if (!$count)
            {
                $sql    =   "INSERT INTO `$table`(`$col`,uid) VALUES(?,?)";

                $stmt   =   $mysqli->prepare($sql) or die(mysqli_error($mysqli).$sql);
                $stmt->bind_param('si',$id, $uid);

                if(!$stmt->execute())
                {
                    $errormsg = $mysqli->error;
                    echo "Error in user access data insert: " . $errormsg;
                    exit;
                }

                //update user session if uid is current user
                if ($current_user == $uid)
                {
                    updateUserAccess($uid);
                }
            }
        }
    }
}


function deleteUserAccess($uid)
{
    global $mysqli;

    if(!isset($mysqli) || !$mysqli)
    {
        require_once(dirname(__FILE__).'/conn.php');
    }

    $stmt    =   $mysqli->prepare('DELETE FROM user_site WHERE uid=?');
    $stmt->bind_param('i', $uid);

    $stmt->execute();

    $stmt    =   $mysqli->prepare('DELETE FROM user_department WHERE uid=?');
    $stmt->bind_param('i', $uid);

    $stmt->execute();

    $stmt    =   $mysqli->prepare('DELETE FROM user_location WHERE uid=?');
    $stmt->bind_param('i', $uid);

    $stmt->execute();


}
function getBaseUrl()
{
    /* First we need to get the protocol the website is using */
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https://' : 'http://';

    /* returns /myproject/index.php */
    $path = $_SERVER['PHP_SELF'];

    /*
     * returns an array with:
     * Array (
     *  [dirname] => /myproject/
     *  [basename] => index.php
     *  [extension] => php
     *  [filename] => index
     * )
     */
    $path_parts = pathinfo($path);
    $directory = $path_parts['dirname'];
    /*
     * If we are visiting a page off the base URL, the dirname would just be a "/",
     * If it is, we would want to remove this
     */
    $directory = ($directory == "/") ? "" : $directory;

    /* Returns localhost OR mysite.com */
    $host = $_SERVER['HTTP_HOST'];

    /*
     * Returns:
     * http://localhost/mysite
     * OR
     * https://mysite.com
     */
    return $protocol . $host . $directory;
}

function google_analytics($site='uitm') {
    $script = '';
    switch ($site)
    {
        case 'uitm':
            $script =   '<script type="text/javascript">'.
                        'var _gaq = _gaq || []; '.
                        "_gaq.push(['_setAccount', 'UA-39494737-1']); ".
                        "_gaq.push(['_setDomainName', 'uitm.edu.my']); ".
                        "_gaq.push(['_trackPageview']); ".
                        '(function() { '.
                        "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ".
                        "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; ".
                        "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); ".
                        '})();'.
                        '</script>';
        break;
    }

    echo $script;
}

function tabletoarray($tablename, &$var) {
    require('conn.php');
    $result = $mysqli->query("select * from " . $tablename . " order by name");
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $var[$row['id']] = $row['name'];
    }
    $mysqli->close();
}

function tabletoarray2($tablename, &$var) {
    require('conn.php');
    $result = $mysqli->query("select * from " . $tablename . " order by name");
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $var[] = $row;
    }
    $mysqli->close();
}

function sqltoarray($sql, &$var) {
    require('conn.php');
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $var[$row['id']] = $row['name'];
    }
    $mysqli->close();
}

function sqltoarray2($sql, &$var) {
    require('conn.php');
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $var[] = $row;
    }
    $mysqli->close();
}

function getLocationDB(){
    //rather than deligating the query by using ajax, all data are compiled into a three level array for better user experience
    function deps_for_site($siteid){
        function locs_dir_dep($depid){
            $location_db = array();
            sqltoarray2('SELECT * from site_location WHERE depid='.$depid,$locs);
            foreach($locs as $loc):
                $location_db[] = array('location_id'=>$loc['id'],'location_name'=>$loc['name'],'select'=>false);
            endforeach;
        }
        $department_db = array();
        sqltoarray2('SELECT * from site_department WHERE siteid='.$siteid,$deps);
        foreach($deps as $dep):
            $department_db[] = array('department_id'=>$dep['id'],'department_name'=>$dep['name'],'select'=>false,'locations'=>locs_dir_dep($dep['id']));
        endforeach;
        return $department_db;
    }
    tabletoarray("site", $sites);
    $location_db = array();
    foreach($sites as $siteid=>$sitename):
        $location_db[] = array('site_id'=>$siteid,'site_name'=>$sitename,'select'=>false,'departments'=>deps_for_site($siteid));
    endforeach;
    return $location_db;
}

function optionize(&$var) {
    if(!$var || !is_array($var))
    {
        return;
    }
    if (func_num_args() == 2) {
        foreach ($var as $i => $val) {
            if (func_get_arg(1) == $i) {
                echo "<option value=\"" . $i . "\" selected>" . $val . "</option>";
            } else {
                echo "<option value=\"" . $i . "\">" . $val . "</option>";
            }
        }
    } else {
        foreach ($var as $i => $val) {
            echo "<option value=\"" . $i . "\">" . $val . "</option>";
        }
    }
}

function truncate($var) {
    if (strlen($var) > 30) {
        return trim(substr($var, 0, 27)) . "...";
    }
    return $var;
}

function isguest() {
    return ($_SESSION['gid'] % 10 == 7) ? true : false;
}

function isadmin() {
    return ($_SESSION['gid'] < 50) ? true : false;
}

function isworker() {
    return ($_SESSION['gid'] < 100) ? true : false;
}

function urlize($fn) {
    if ($fn == "") {
        return "None";
    }
    return "<a href=\"upload/" . $fn . "\">" . $fn . "</a>";
}

function getSession($key, $delete=false) {
    if(isset($_SESSION[$key])) {
        $data   =   $_SESSION[$key];
        if ($delete === true) {
            unset($_SESSION[$key]);
        }
        return $data;
    }
}

function setSession($key, $value) {
    if($key) {
        $_SESSION[$key] = $value;
    }
}

function getAdmins()
{
    global $mysqli;

    $result =   $mysqli->query('SELECT id FROM user WHERE authlevel <= 10');
    if ($result)
    {
        $rows   =   array();

        while ($row = $result->fetch_object())
        {
            $rows[] =   $row->id;
        }
        mysqli_free_result($result);
        return $rows;
    }
}

function getUserAccessList($uid) {

    if($uid === null || !is_numeric($uid)) {
        return;
    }

    $uid    =   intval($uid, 10);
    $access_departments =   array();
    $access_locations   =   array();
    $access_sites       =   array();

    require('conn.php');

    //sites
    $result     =   $mysqli->query('SELECT s.id,s.name FROM user_site us INNER JOIN site s ON s.id = us.siteid WHERE us.uid = '.$uid);
    if ($result)
    {
        while ($row = $result->fetch_object())
        {
            $access_sites[] =   array(
                'id'    =>  $row->id,
                'name'  =>  $row->name,
            );
        }
        mysqli_free_result($result);
    }

    //departments
    $result     =   $mysqli->query('SELECT sd.id,sd.name FROM user_department ud INNER JOIN site_department sd ON sd.id = ud.depid WHERE ud.uid = '.$uid);
    if ($result)
    {
        while ($row = $result->fetch_object())
        {
            $access_departments[] =   array(
                'id'    =>  $row->id,
                'name'  =>  $row->name,
            );
        }
        mysqli_free_result($result);
    }

    //locations
    $result     =   $mysqli->query('SELECT dl.id, dl.name FROM user_location ul INNER JOIN department_location dl ON dl.id = ul.locid WHERE ul.uid = '.$uid);
    if ($result)
    {
        while ($row = $result->fetch_object())
        {
            $access_locations[] =   array(
                'id'    =>  $row->id,
                'name'  =>  $row->name,
            );
        }
        mysqli_free_result($result);
    }
    return array(
        'departments'   =>  $access_departments,
        'locations'     =>  $access_locations,
        'sites'         =>  $access_sites,
    );
}

if (!function_exists('debug'))
{
    function debug($args,$title='') {
        if($title) {
            echo "<h1>$title</h1>";
        }
        echo '<pre>';
        print_r($args);
        echo '</pre>';
    }
}
function getNextWorkorderNumber()
{
    global $mysqli;

    if(!$mysqli)
    {
        require('conn.php');
    }
    $sql    =   'SELECT COUNT(1) + 1 FROM `workorder`';
    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $wo_num =   1;

    if($result)
    {
        list($wo_num)   =   $result->fetch_row();
        mysqli_free_result($result);

        if(!$wo_num)
        {
            //set default
            $wo_num =   1;
        }
    }
    return $wo_num;
}

function isAssetExist($tems_no) {

    if(!$tems_no)
    {
        return;
    }
    require('conn.php');

    $site       =   mysqli_real_escape_string($mysqli, $tems_no);
    $sql        =   "SELECT id, assetno FROM `asset` WHERE TRIM(LOWER(`assetno`)) = TRIM(LOWER('$tems_no'));";
    //echo $sql.'<hr>';
    $result     =   $mysqli->query($sql) or die(mysqli_error($msqli));
    list($id,$assetno)   =   $result->fetch_row();
    mysqli_free_result($result);
    return $id;
}

function isDepartmentExist($dept, $site_id=null) {

    if(!$dept || !is_string($dept))
    {
        return;
    }
    require_once('conn.php');

    $dept       =   mysqli_real_escape_string($mysqli, $dept);
    $site_id    =   mysqli_real_escape_string($mysqli, $site_id);

    $sql        =   "SELECT id FROM `site_department` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$dept')) ";

    if ($site_id)
    {
        $sql    .=  "AND `siteid` = '$site_id'";
    }

    $result     =   $mysqli->query($sql) or die(mysqli_error($msqli));
    list($id)   =   $result->fetch_row();
    mysqli_free_result($result);
    return $id;
}

function isLocationExist($loc, $site_id=null) {

    if(!$loc || !is_string($loc))
    {
        return;
    }
    require_once('conn.php');

    $loc        =   mysqli_real_escape_string($mysqli, $loc);
    $site_id    =   mysqli_real_escape_string($mysqli, $site_id);

    $sql        =   "SELECT id FROM `site_location` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$loc')) ";

    if ($site_id)
    {
        $sql    .=  "AND `siteid` = '$site_id'";
    }

    $result     =   $mysqli->query($sql) or die(mysqli_error($msqli));
    list($id)   =   $result->fetch_row();
    mysqli_free_result($result);
    return $id;
}

function isAssetSerialExist($serialno,$site_id=null)
{
    if(!$serialno)
    {
        return false;
    }

    global $mysqli;

    if(!$mysqli)
    {
        require('conn.php');
    }

    $serialno   =   mysqli_real_escape_string($mysqli, $serialno);
    $site_id    =   mysqli_real_escape_string($mysqli, $site_id);

    $sql        =   "SELECT `assetno` FROM `asset` WHERE TRIM(LOWER(`serialno`)) = TRIM(LOWER('$serialno')) ";

    if ($site_id)
    {
        $sql    .=  "AND `siteid` = '$site_id'";
    }

    $result     =   $mysqli->query($sql) or die(mysqli_error($msqli));
    list($assetno)   =   $result->fetch_row();
    mysqli_free_result($result);
    return $assetno;
}

function isSiteExist($site) {

    if(!$site || !is_string($site))
    {
        return;
    }
    require('conn.php');

    $site       =   mysqli_real_escape_string($mysqli, $site);
    $result     =   $mysqli->query("SELECT id FROM `site` WHERE TRIM(LOWER(`name`)) = TRIM(LOWER('$site'));") or die(mysqli_error($msqli));
    list($id)   =   $result->fetch_row();
    mysqli_free_result($result);
    return $id;
}

if(!function_exists('recursive_array_search'))
{
function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}
}

function updateUserAccess($uid=null)
{
    if(!$uid)
    {
        $uid    =   getSession('uid');
    }
    setSession('access', getUserAccessList($uid));
}
