<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');
date_default_timezone_set("Asia/Kuala_Lumpur");

if ($_SESSION['gid'] % 10 == 7) // guest
{
    die();
}
?>
<html><head><title>TEMS: Database Update</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" title="default">
<script language="javascript">
function redir(strUrl, iTime) {
    var version = parseInt(navigator.appVersion)
    if (version >= 4 || window.location.replace)
        setTimeout("window.location.replace('" + strUrl + "')", iTime);
    else
        setTimeout("window.location.href = '" + strUrl + "'", iTime);
}
</script>
</head>
<?php
$now            =   date("Y-n-j H:i:s");
$autoredirect   =   true;
if (!isset($_POST['func'])) die();

$sUrl           =   $_SERVER['HTTP_REFERER'];
$sMsg           =   "Processing...";
$iDuration      =   0;
$func           =   $_POST['func'];



switch($func) {

    case "add_asset":
        if (($_POST['serialno'] != "") && ($_POST['classid'] != "") && ($_POST['typeid'] != "") && ($_POST['manuid'] != "") && ($_POST['modelid'] != "") && ($_POST['locationid'] != "") && ($_POST['siteid'] != "")) {
            $ano = empty($_POST['assetno'])? null : strtoupper($_POST['assetno']);
            $acid = empty($_POST['classid'])? null : $_POST['classid'];
            $atid = empty($_POST['typeid'])? null : $_POST['typeid'];
            $amid = empty($_POST['manuid'])? null : $_POST['manuid'];
            $amdid = empty($_POST['modelid'])? null : $_POST['modelid'];
            $asno = empty($_POST['serialno'])? null : strtoupper($_POST['serialno']);
            $arno = empty($_POST['refno'])? null : strtoupper($_POST['refno']);
            $aono = empty($_POST['orderno'])? null : strtoupper($_POST['orderno']);
            $apdt = empty($_POST['dtpurchase'])? null : $_POST['dtpurchase'];
            $awst = empty($_POST['dtwrntstart'])? null : $_POST['dtwrntstart'];
            $awnd = empty($_POST['dtwrntend'])? null : $_POST['dtwrntend'];
            $aspid = empty($_POST['supplid'])? null : $_POST['supplid'];
            $asid = empty($_POST['siteid'])? null : $_POST['siteid'];
            $aprc = empty($_POST['price'])? null : $_POST['price'];
            $astat = empty($_POST['statusid'])? null : $_POST['statusid'];
            $alid = empty($_POST['locationid'])? null : $_POST['locationid'];
            $adept = empty($_POST['department_id'])? null : $_POST['department_id'];
            $appmst = empty($_POST['dtppmstart'])? null : $_POST['dtppmstart'];
            $appmfq = empty($_POST['dtppmfreq'])? null : $_POST['ppmfreq'];
            $arem = empty($_POST['arem'])? null : $_POST['arem'];
            $uid = $_SESSION['uid'];

            $stmt = $mysqli->prepare("INSERT INTO asset (assetno, classid, typeid, manuid, modelid, serialno, refno, orderno, purchasedate, warrantystart, warrantyend, ppmstart, ppmfreq, supplierid, price, status, siteid, locationid, department_id, remarks, author, created, modified) VALUES  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('siiiissssssssidiiiisi', $ano, $acid, $atid, $amid, $amdid, $asno, $arno, $aono, $apdt, $awst, $awnd, $appmst, $appmfq, $aspid, $aprc, $astat, $asid, $alid, $adept, $arem, $uid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            setSession('asset_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl = "asset.php#tabnew";
            $iDuration = 3000;
        }
        break;

    case "edit_asset":
        if (($_POST['aid'] != "")
            && ($_POST['serialno'] != "")
            //&& ($_POST['classid'] != "")
            //&& ($_POST['typeid'] != "")
            //&& ($_POST['manuid'] != "")
            //&& ($_POST['modelid'] != "")
            && ($_POST['locationid'] != "")
            && ($_POST['siteid'] != "")
            //&& ($_POST['roomno'] != "")
            ) {
            $ano = empty($_POST['assetno'])? null : strtoupper($_POST['assetno']);
            $acid = empty($_POST['classid'])? null : $_POST['classid'];
            $atid = empty($_POST['typeid'])? null : $_POST['typeid'];
            $amid = empty($_POST['manuid'])? null : $_POST['manuid'];
            $amdid = empty($_POST['modelid'])? null : $_POST['modelid'];
            $asno = empty($_POST['serialno'])? null : strtoupper($_POST['serialno']);
            $arno = empty($_POST['refno'])? null : strtoupper($_POST['refno']);
            $aono = empty($_POST['orderno'])? null : strtoupper($_POST['orderno']);
            $apdt = empty($_POST['dtpurchase'])? null : $_POST['dtpurchase'];
            $awst = empty($_POST['dtwrntstart'])? null : $_POST['dtwrntstart'];
            $awnd = empty($_POST['dtwrntend'])? null : $_POST['dtwrntend'];
            $aspid = empty($_POST['supplid'])? null : $_POST['supplid'];
            $asid = empty($_POST['siteid'])? null : $_POST['siteid'];
            $aprc = empty($_POST['price'])? null : $_POST['price'];
            $astat = empty($_POST['statusid'])? null : $_POST['statusid'];
            $alid = empty($_POST['locationid'])? null : $_POST['locationid'];
            $adept = empty($_POST['department_id'])? null : $_POST['department_id'];
            $appmst = empty($_POST['dtppmstart'])? null : $_POST['dtppmstart'];
            $appmfq = empty($_POST['ppmfreq'])? null : $_POST['ppmfreq'];
            $arem = empty($_POST['arem'])? null : $_POST['arem'];
            $uid = $_SESSION['uid'];
            $aid = $_POST['aid'];
            $stmt = $mysqli->prepare("INSERT INTO history_asset(id, assetno, classid, typeid, manuid, modelid, serialno, refno, orderno, status, remarks, siteid, locationid, department_id, department_id, purchasedate, supplierid, price, warrantystart, warrantyend, ppmstart, ppmfreq, author, created)
                SELECT id, assetno, classid, typeid, manuid, modelid, serialno, refno, orderno, status, remarks, siteid, locationid, department_id, purchasedate, supplierid, price, warrantystart, warrantyend, ppmstart, ppmfreq, ?, ? FROM asset WHERE id = ?");
            $stmt->bind_param('isi', $uid, $now, $aid);
            $stmt->execute();

            $stmt = $mysqli->prepare("UPDATE asset SET assetno = ?, classid = ?, typeid = ?, manuid = ?, modelid = ?, serialno = ?, refno = ?, orderno = ?, purchasedate = ?, warrantystart = ?, warrantyend = ?, ppmstart = ?, ppmfreq = ?, supplierid = ?, price = ?, status = ?, siteid = ?, locationid = ?, department_id=?, remarks = ?, modified = ? WHERE id = ?");
            $stmt->bind_param('siiiissssssssidiiiissi', $ano, $acid, $atid, $amid, $amdid, $asno, $arno, $aono, $apdt, $awst, $awnd, $appmst, $appmfq, $aspid, $aprc, $astat, $asid, $alid, $adept, $arem, $now, $aid);
            $stmt->execute();

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            if(!empty($_POST['roomno'])) {
                $room_no    =   $mysqli->real_escape_string($_POST['roomno']);
                $asset_id   =   $mysqli->real_escape_string($aid);
                $site_id    =   $mysqli->real_escape_string($asid);
                $loc_id     =   $mysqli->real_escape_string($alid);
                $under_contract =   $_POST['under_contract'];
                $sqli_check =   'SELECT '.
                                    'COUNT(1) '.
                                'FROM '.
                                    '`asset_info` '.
                                'WHERE '.
                                    "`assetid`='$asset_id' ".
                                'AND '.
                                    "`roomno`= '$room_no' ".
                                'AND '.
                                    "`siteid`= '$site_id' ".
                                'AND '.
                                    "`locationid` = '$loc_id' ";

                $result     =   $mysqli->query($sqli_check);
                list($count)=   $result->fetch_row();

                if (!$count) {
                    $sql    =   'INSERT INTO asset_info(assetid,roomno,siteid,locationid,under_contract) VALUES(?,?,?,?,?)';
                    $stmt   =   $mysqli->prepare($sql);
                    $stmt->bind_param('isiis', $aid, $room_no, $asid, $alid, $under_contract);
                    $stmt->execute();
                } else {
                    $sql    =   'UPDATE asset_info SET roomno = ?, siteid = ?, locationid = ?, under_contract=?  WHERE assetid = ?';
                    $stmt   =   $mysqli->prepare($sql);
                    $stmt->bind_param('siisi', $room_no, $asid, $alid, $under_contract,$aid);
                    $stmt->execute();
                }
            }

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            setSession('asset_updated', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }

        break;

    case "del_asset":
        if (isset($_POST['aid'])) {
            $uid = $_SESSION['uid'];
            $aid = $_POST['aid'];

            $stmt = $mysqli->prepare("INSERT INTO history_workorder (id, assetid, category, description, status, vendorid, required, completed, cost, orderno, author, created)
                    SELECT id, assetid, category, description, status, vendorid, required, completed, cost, orderno, ?, ? FROM workorder WHERE assetid = ?");
                $stmt->bind_param('isi', $uid, $now, $aid);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM workorder WHERE assetid = ?");
                $stmt->bind_param('i', $aid);
            $stmt->execute();

            $stmt = $mysqli->prepare("INSERT INTO history_asset(id, assetno, classid, typeid, manuid, modelid, serialno, refno, orderno, status, remarks, siteid, locationid, purchasedate, supplierid, price, warrantystart, warrantyend, ppmstart, ppmfreq, author, created)
                SELECT id, assetno, classid, typeid, manuid, modelid, serialno, refno, orderno, status, remarks, siteid, locationid, purchasedate, supplierid, price, warrantystart, warrantyend, ppmstart, ppmfreq, ?, ? FROM asset WHERE id = ?");
            $stmt->bind_param('isi', $uid, $now, $aid);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM asset WHERE id = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM asset_info WHERE assetid = ?");
            $stmt->bind_param('i', $aid);
            $stmt->execute();

            $sUrl = "asset.php";

            setSession('asset_deleted', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "add_workorder":
        if ($_POST['assetid'] != "" && (isset($_POST['woprv']) || isset($_POST['wocrt']) || isset($_POST['wovld']) || isset($_POST['woclb']))) {
            $assetid = $_POST['assetid'];
            $wocat = 0;
            if (isset($_POST['woprv'])) $wocat += 1;
            if (isset($_POST['wocrt'])) $wocat += 2;
            if (isset($_POST['wovld'])) $wocat += 4;
            if (isset($_POST['woclb'])) $wocat += 8;
            $wodesc = empty($_POST['wodesc'])? null : $_POST['wodesc'];
            $vendorid = empty($_POST['vendorid'])? null : $_POST['vendorid'];
            $dtrequire = empty($_POST['dtrequire'])? null : $_POST['dtrequire'];
            $dtcomplete = empty($_POST['dtcomplete'])? null : $_POST['dtcomplete'];
            $wostatus = empty($_POST['wostatus'])? null : $_POST['wostatus'];
            $orderno = empty($_POST['orderno'])? null : $_POST['orderno'];
            $wocost = empty($_POST['wocost'])? null : $_POST['wocost'];
            $uid = $_SESSION['uid'];

            $stmt = $mysqli->prepare("INSERT INTO workorder (assetid, category, description, status, vendorid, required, completed, cost, orderno, author, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param('iisiissdsi', $assetid, $wocat, $wodesc, $wostatus, $vendorid, $dtrequire, $dtcomplete, $wocost, $orderno, $uid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $stmt = $mysqli->prepare("UPDATE asset SET lastsvc = (select max(completed) from workorder where assetid = ?) WHERE id = ?");
                $stmt->bind_param('ii', $assetid, $assetid);
            $stmt->execute();

            $sUrl = "editasset.php?id=" . $assetid . "#tabservice";

            setSession('workorder_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl = "editasset.php?id=" . $_POST['assetid'] . "#tabservice";
            $iDuration = 3000;
        }
        break;

    case "edit_workorder":
        if ($_POST['assetid'] != "" && $_POST['woid'] != "" && (isset($_POST['woprv']) || isset($_POST['wocrt']) || isset($_POST['wovld']) || isset($_POST['woclb']))) {
            $woid = $_POST['woid'];
            $assetid = $_POST['assetid'];
            $wocat = 0;
            if (isset($_POST['woprv'])) $wocat += 1;
            if (isset($_POST['wocrt'])) $wocat += 2;
            if (isset($_POST['wovld'])) $wocat += 4;
            if (isset($_POST['woclb'])) $wocat += 8;
            $wodesc = empty($_POST['wodesc'])? null : $_POST['wodesc'];
            $vendorid = empty($_POST['vendorid'])? null : $_POST['vendorid'];
            $dtrequire = empty($_POST['dtrequire'])? null : $_POST['dtrequire'];
            $dtcomplete = empty($_POST['dtcomplete'])? null : $_POST['dtcomplete'];
            $wostatus = empty($_POST['wostatus'])? null : $_POST['wostatus'];
            $orderno = empty($_POST['orderno'])? null : $_POST['orderno'];
            $wocost = empty($_POST['wocost'])? null : $_POST['wocost'];
            $uid = $_SESSION['uid'];

            $stmt = $mysqli->prepare("INSERT INTO history_workorder (id, assetid, category, description, status, vendorid, required, completed, cost, orderno, author, created)
                    SELECT id, assetid, category, description, status, vendorid, required, completed, cost, orderno, ?, ? FROM workorder WHERE id = ?");
                $stmt->bind_param('isi', $uid, $now, $woid);
            $stmt->execute();

            /* Changes: 20130822 - Husaini
             * If status = completed, change completed date to current
            */
            /*
             *  $wostatus[1] = "Scheduled";
                $wostatus[2] = "Completed";
                $wostatus[3] = "Cancelled";
            */

            if ($wostatus == '2')
            {
                $dtcomplete =   date('Y-m-d', time());
                $stmt       =   $mysqli->prepare("UPDATE workorder SET category = ?, description = ?, status = ?, vendorid = ?, required = ?, completed = ?, cost = ?, orderno = ?, modified = ? WHERE id = ?");
                $stmt->bind_param('isiissdssi', $wocat, $wodesc, $wostatus, $vendorid, $dtrequire, $dtcomplete, $wocost, $orderno, $now, $woid);
            }
            else
            {
                $stmt = $mysqli->prepare("UPDATE workorder SET category = ?, description = ?, status = ?, vendorid = ?, required = ?, completed = ?, cost = ?, orderno = ?, modified = ? WHERE id = ?");
                $stmt->bind_param('isiissdssi', $wocat, $wodesc, $wostatus, $vendorid, $dtrequire, $dtcomplete, $wocost, $orderno, $now, $woid);
            }

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $stmt = $mysqli->prepare("UPDATE asset SET lastsvc = (select max(completed) from workorder where assetid = ?) WHERE id = ?");
                $stmt->bind_param('ii', $assetid, $assetid);
            $stmt->execute();

            if(isset($_POST['submit_pdf']))
            {
                setSession('generate_pdf', 1);
            }
            setSession('workorder_updated', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "del_workorder":
        if ($_POST['assetid'] != "" && isset($_POST['woid'])) {
            $woid = $_POST['woid'];
            $uid = $_SESSION['uid'];
            $assetid = $_POST['assetid'];

            $stmt = $mysqli->prepare("INSERT INTO history_workorder (id, assetid, category, description, status, vendorid, required, completed, cost, orderno, author, created)
                    SELECT id, assetid, category, description, status, vendorid, required, completed, cost, orderno, ?, ? FROM workorder WHERE id = ?");
                $stmt->bind_param('isi', $uid, $now, $woid);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM workorder WHERE id = ?");
                $stmt->bind_param('i', $woid);
            $stmt->execute();

            $stmt = $mysqli->prepare("UPDATE asset SET lastsvc = (select max(completed) from workorder where assetid = ?) WHERE id = ?");
                $stmt->bind_param('ii', $assetid, $assetid);
            $stmt->execute();

            $sUrl = (isset($_POST['aid']))? "editasset.php?id=" . $_POST['aid'] . "#tabservice" : "workorder.php";

            setSession('workorder_deleted', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "add_workorder_bulk":

        if (isset($_POST['assetid']) && $_POST['dtrequire'] != "") {
            $assetid = $_POST['assetid'];
            $wodesc = empty($_POST['wodesc'])? null : $_POST['wodesc'];
            $dtrequire = empty($_POST['dtrequire'])? null : $_POST['dtrequire'];
            $uid = $_SESSION['uid'];

            foreach($assetid as $aid) {
                $stmt = $mysqli->prepare("INSERT INTO workorder (assetid, category, description, status, required, author, created, modified) VALUES (?, 0, ?, 1, ?, ?, ?, ?)");
                $stmt->bind_param('ississ', $aid, $wodesc, $dtrequire, $uid, $now, $now);
                $stmt->execute();
            }

            setSession('workorder_added', 1);
        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "add_vendor":
        if ($_POST['vname'] != "") {
            $vname = empty($_POST['vname'])? null : strtoupper($_POST['vname']);
            $vperson = empty($_POST['vperson'])? null : $_POST['vperson'];
            $vphone = empty($_POST['vphone'])? null : $_POST['vphone'];
            $vfax = empty($_POST['vfax'])? null : $_POST['vfax'];
            $vaddr = empty($_POST['vaddr'])? null : $_POST['vaddr'];
            $vemail = empty($_POST['vemail'])? null : $_POST['vemail'];
            $vtype = empty($_POST['vtype'])? 0 : $_POST['vtype'];
            $vstatus = empty($_POST['vstatus'])? 0 : $_POST['vstatus'];
            $vrem = empty($_POST['vrem'])? null : $_POST['vrem'];
            $uid = $_SESSION['uid'];

            $stmt = $mysqli->prepare("INSERT INTO vendor (name, person, phone, fax, address, email, type, status, remarks, author, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param('ssssssiisi', $vname, $vperson, $vphone, $vfax, $vaddr, $vemail, $vtype, $vstatus, $vrem, $uid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            setSession('vendor_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl = "vendor.php#tabnew";
            $iDuration = 3000;
        }
        break;

    case "edit_vendor":
        if ($_POST['vname'] != "" && $_POST['vid']) {
            $vname = empty($_POST['vname'])? null : strtoupper($_POST['vname']);
            $vperson = empty($_POST['vperson'])? null : $_POST['vperson'];
            $vphone = empty($_POST['vphone'])? null : $_POST['vphone'];
            $vfax = empty($_POST['vfax'])? null : $_POST['vfax'];
            $vaddr = empty($_POST['vaddr'])? null : $_POST['vaddr'];
            $vemail = empty($_POST['vemail'])? null : $_POST['vemail'];
            $vtype = empty($_POST['vtype'])? 0 : $_POST['vtype'];
            $vstatus = empty($_POST['vstatus'])? 0 : $_POST['vstatus'];
            $vrem = empty($_POST['vrem'])? null : $_POST['vrem'];
            $vid = $_POST['vid'];
            $uid = $_SESSION['uid'];

            $stmt = $mysqli->prepare("UPDATE vendor SET name = ?, person = ?, phone = ?, fax = ?, address = ?, email = ?, type = ?, status = ?, remarks = ?, modified = NOW() WHERE id = ?");
            $stmt->bind_param('ssssssiisi', $vname, $vperson, $vphone, $vfax, $vaddr, $vemail, $vtype, $vstatus, $vrem, $vid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            setSession('vendor_updated', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "del_vendor":
        if ($_POST['vid']) {
            $vid = $_POST['vid'];

            $stmt = $mysqli->prepare("DELETE FROM vendor WHERE id = ?");
                $stmt->bind_param('i', $vid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> Most probably the data you're trying to delete is already in used or reference by another set of data.";
                exit;
            }
            $sUrl = "vendor.php";

            setSession('vendor_deleted', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $iDuration = 3000;
        }
        break;

    case "add_site":
        if ($_POST['sname'] != "") {
            $sname = strtoupper($_POST['sname']);
            $sphone = empty($_POST['sphone'])? null : $_POST['sphone'];
            $sfax = empty($_POST['sfax'])? null : $_POST['sfax'];
            $saddr = empty($_POST['saddr'])? null : $_POST['saddr'];

            $stmt = $mysqli->prepare("INSERT INTO site (name, phone, fax, address, created, modified) VALUES (?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param('ssss', $sname, $sphone, $sfax, $saddr);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }

            setSession('site_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl = "site.php#tabnew";
            $iDuration = 3000;
        }
        break;

    case 'edit_site':
        if($_POST) {
            if(isset($_POST['sname']) && $_POST['sname'] && $_POST['sid']) {
                $sid    =   $_POST['sid'];
                $sname  =   strtoupper($_POST['sname']);
                $sphone =   empty($_POST['sphone'])? null : $_POST['sphone'];
                $sfax   =   empty($_POST['sfax'])? null : $_POST['sfax'];
                $saddr  =   empty($_POST['saddr'])? null : $_POST['saddr'];

                $sql    =   'UPDATE '.
                                '`site` '.
                            'SET '.
                                '`name` = ?, '.
                                '`phone` = ?, '.
                                '`fax` = ?, '.
                                '`address` = ?, '.
                                '`modified` = NOW() '.
                            'WHERE '.
                                'id = ?';
                $stmt   =   $mysqli->prepare($sql);
                $stmt->bind_param('ssssi', $sname, $sphone, $sfax, $saddr, $sid);

                if (!$stmt->execute()) {
                    echo "<b>Error in database operation:</b> " . $mysqli->error;
                    exit;
                }

                setSession('site_updated', 1);
                $sUrl = "editsite.php?id=$sid&tab=tabdetails#tabdetails";
            }
        }
        break;

    case "add_loc":
        if ($_POST['lname'] != "" && $_POST['sid'] != "") {
            $lname = strtoupper($_POST['lname']);
            $sid = $_POST['sid'];
            $depid = $_POST['depid'];

            $stmt = $mysqli->prepare("INSERT INTO site_location (name, depid) VALUES (?, ?)");
                $stmt->bind_param('si', $lname, $depid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $sUrl .= "#tabloc";

            setSession('location_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabloc";
            $iDuration = 3000;
        }
        break;

    case "add_dept":
        if ($_POST['name'] != "" && $_POST['sid'] != "") {
            $name = strtoupper($_POST['name']);
            $sid = $_POST['sid'];

            $stmt = $mysqli->prepare("INSERT INTO site_department (name, siteid) VALUES (?, ?)");
                $stmt->bind_param('si', $name, $sid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $sUrl .= "#tabdept";

            setSession('department_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabloc";
            $iDuration = 3000;
        }
        break;

    case "add_doc":
        if ($_FILES['adoc']['size'] != 0 && $_POST['sno'] != "" && $_POST['adoccat'] != "") {
            $doccat = $_POST['adoccat'];
            $extallowed = array("jpg", "jpeg", "gif", "pdf");
            $thisfileext = end(explode(".", strtolower($_FILES['adoc']['name'])));
            if (in_array($thisfileext, $extallowed)) {
                $uploaddir = dirname(__FILE__) . "/upload/asset/" . cleanfilename($_POST['sno']);
                if (!file_exists($uploaddir)) mkdir($uploaddir);
                if ($doccat > 0)
                    $uploadfile = "t" . $doccat . "-" . date("YmdHis") . "-" . $_SESSION['uid'] . "-" . strtolower(basename($_FILES['adoc']['name']));
                else
                    $uploadfile = date("YmdHis") . "-" . $_SESSION['uid'] . "-" . strtolower(basename($_FILES['adoc']['name']));

                if (!move_uploaded_file($_FILES['adoc']['tmp_name'], $uploaddir . "/" . $uploadfile))
                    die("aborting file upload. possible file upload attack detected. or developer_id = 10t.");
            } else {
                $sMsg = "File with <b>.$thisfileext</b> extension is not allowed to be uploaded.";
                $iDuration = 3000;
            }
            $sUrl .= "#tabdocs";

            setSession('document_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabdocs";
            $iDuration = 3000;
        }
        break;

    case "del_doc":
        if ($_POST['id'] != "") {
            $file = dirname(__FILE__) . "/upload/asset/" . $_POST['id'];
            rename($file, $file . "." . date("YmdHis") . ".bak");
            $sUrl .= "#tabdocs";

            setSession('document_deleted', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabdocs";
            $iDuration = 3000;
        }
        break;

    case "edit_doc":
        if ($_POST['id'] != "" && $_POST['doccat'] != "") {
            $doccat = $_POST['doccat'];
            $fileid = $_POST['id'];
            $filepath = explode("/", $fileid);
            $filename = end($filepath);
            $piece = explode("-", $filename);
            if (substr($piece[0], 0, 1) == "t" && strlen($piece[0]) == 2) {
                $piece[0] = "";
                $filename = implode("-", $piece);
                $filename = trim($filename, " -");
            }
            if ((int)$doccat > 0)
            $filename = "t" . $doccat . "-" . $filename;
            $file1 = dirname(__FILE__) . "/upload/asset/" . $fileid;
            $file2 = dirname(__FILE__) . "/upload/asset/" . $filepath[0] . "/" . $filename;
            rename($file1, $file2);
            echo "rename($file1, $file2)";
            $sUrl .= "#tabdocs";

            setSession('document_updated', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabdocs";
            $iDuration = 3000;
        }
        break;

    case "add_part":
        if ($_POST['pname'] != "" && $_POST['aid'] != "") {
            $pname = $_POST['pname'];
            $aid = $_POST['aid'];

            $stmt = $mysqli->prepare("INSERT INTO asset_part (name, assetid) VALUES (?, ?)");
                $stmt->bind_param('si', $pname, $aid);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $sUrl .= "#tabparts";

            setSession('part_added', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabparts";
            $iDuration = 3000;
        }
        break;

    case "del_part":
        if ($_POST['id'] != "") {
            $stmt = $mysqli->prepare("DELETE FROM asset_part WHERE id = ?");
                $stmt->bind_param('i', $_POST['id']);

            if (!$stmt->execute()) {
                echo "<b>Error in database operation:</b> " . $mysqli->error;
                exit;
            }
            $sUrl .= "#tabparts";

            setSession('part_deleted', 1);

        } else {
            $sMsg = "Required fields are empty.";
            $sUrl .= "#tabparts";
            $iDuration = 3000;
        }
        break;

    case 'add_user':
    case 'edit_user':

        if(isset($_POST['delete_user'])) {
            if (!isadmin()) {
                setSession('error', 'You are not allowed to delete this user.');
            } else {
                $redirect_url   =   'user.php';
                $id             =   (isset($_POST['id']) && is_numeric($_POST['id'])) ? intval($_POST['id'], 10) : null;

                if(!$id) {
                    setSession('error', 'No user to delete.');
                    header("Location: $redirect_url");
                    exit();
                }

                if($_SESSION['uid'] == $id) {
                    $redirect_url   =  'edituser.php?id='.$id;
                    setSession('error', 'Sorry, you can\'t delete your own account.');
                    header("Location: $redirect_url");
                    exit();
                }

                //Make sure user exist
                $result =   $mysqli->query('SELECT * FROM `user` WHERE id = '.$id);

                if($result) {
                    $user   =   $result->fetch_assoc();
                    mysqli_free_result($result);

                    $result =   $mysqli->query('DELETE FROM `user` WHERE id='.$id) or die(mysqli_error($mysqli));
                    $num    =   mysqli_affected_rows($mysqli);
                    if($num) {
                        // delete user access
                        $mysqli->query('DELETE FROM user_access WHERE uid = '.$id);
                        setSession('user_deleted', 1);
                        setSession('deleted_user', $user['name']);
                    }
                    header("Location: $redirect_url");
                    exit();
                }
            }
        } else {
            /*
            echo '<pre>';
            print_r($_POST);
            echo '</pre>';
            die();
            */

            $required_fields    =   array(
                'ufname'    =>  1,
                'uname'     =>  1,
                'ugid'      =>  1,
            );

            $missing_field  =   false;

            if($func == 'edit_user') {
                unset($required_fields['uname']);
            }

            foreach ($required_fields as $field => $value) {
                if(!isset($_POST[$field]) || !$_POST[$field]) {
                    $missing_field  =   true;
                    echo $field . ' is required';
                    break;
                }
            }

            if (!$missing_field) {

                $uid        =   (isset($_POST['id']) && is_numeric($_POST['id'])) ? intval($_POST['id'], 10) : null;
                $ufname     =   $_POST['ufname'];
                $uname      =   (isset($_POST['uname'])) ? trim(strtolower($_POST['uname'])) : null;
                $uemail     =   (isset($_POST['uemail'])) ? strtolower($_POST['uemail']) : null;
                $ugid       =   $_POST['ugid'];
                $sid        =   (isset($_POST['sid'])) ? $_POST['sid'] : 0;
                $uphone     =   (isset($_POST['uphone'])) ? $_POST['uphone'] : null;
                $urem       =   empty($_POST['urem'])? null: $_POST['urem'];
                $uauthor    =   $_SESSION['uid'];
                $pwd        =   $uname ? md5($uname.'password') : '';
                $sites      =   (isset($_POST['sites']) && $_POST['sites']) ? $_POST['sites'] : array();
                $departments=   (isset($_POST['departments']) && $_POST['departments']) ? $_POST['departments'] : array();
                $locations  =   (isset($_POST['locations']) && $_POST['locations']) ? $_POST['locations'] : array();
                $upost      =   (isset($_POST['upost'])) ? $_POST['upost'] : null;
                $uenabled   =   (isset($_POST['uactive']) && in_array($_POST['uactive'], array(0,1,'0','1',true,false))) ? $_POST['uactive'] : 0;

                if(is_bool($uenabled)) {
                    $uenabled   =   $uenabled ? 1 : 0;
                }

                if ($uid) {
                    // UPDATE
                    $sql    =   'UPDATE '.
                                    '`user` '.
                                'SET '.
                                    '`name`  = ?, '.
                                    '`email` = ?, '.
                                    '`phone` = ?, '.
                                    '`authlevel` = ?,'.
                                    '`enabled` = ?,'.
                                    '`remarks` = ?, '.
                                    '`author` = ?,'.
                                    '`post` = ? '.
                                'WHERE '.
                                    'id = ?';

                    $stmt   =   $mysqli->prepare($sql);
                    $stmt->bind_param('sssiisisi',
                        $ufname,
                        $uemail,
                        $uphone,
                        $ugid,
                        $uenabled,
                        $urem,
                        $uauthor,
                        $upost,
                        $uid
                    );
                    if (!$stmt->execute()) {
                        $errormsg = $mysqli->error;
                        setSession('error', "Error in user data insert: " . $errormsg);
                        header("Location: edituser.php?id=$uid");
                        exit();
                    }

                    //filter values
                    foreach ($sites as $key => $value) {
                        if(!is_numeric($value) || !$value) {
                            unset($sites[$key]);
                        }
                    }
                    foreach ($departments as $key => $value) {
                        if(!is_numeric($value) || !$value) {
                            unset($departments[$key]);
                        }
                    }
                    foreach ($locations as $key => $value) {
                        if(!is_numeric($value) || !$value) {
                            unset($locations[$key]);
                        }
                    }
                    $sites          =   serialize($sites);
                    $departments    =   serialize($departments);
                    $locations      =   serialize($locations);

                    $sql            =   'INSERT INTO `user_access`('.
                                            '`uid`,'.
                                            '`sites`,'.
                                            '`locations`,'.
                                            '`departments`) '.
                                        'VALUES(?,?,?,?) '.
                                        'ON DUPLICATE KEY UPDATE '.
                                            '`sites` = ?,'.
                                            '`locations` = ?, '.
                                            '`departments` = ? ';

                    $stmt           =   $mysqli->prepare($sql);
                    $stmt->bind_param('issssss', $uid, $sites, $locations, $departments, $sites, $locations, $departments);
                    $stmt->execute();

                    if (!$stmt->execute()) {
                        $errormsg = $mysqli->error;
                        setSession('error', "Error updating user access: " . $errormsg);
                        header("Location: edituser.php?id=$uid");
                        exit();
                    }

                    setSession('user_updated', 1);
                    $sUrl = 'edituser.php?id='.$uid;

                } else {
                    // NEW USER
                    if (withinbound($uname, 6, 50)) {
                        $sql    =   'INSERT INTO user ('.
                                        'name, '.
                                        'username, '.
                                        'password, '.
                                        'email, '.
                                        'phone, '.
                                        'siteid, '.
                                        'authlevel, '.
                                        'created, '.
                                        'enabled, '.
                                        'remarks, '.
                                        'author, '.
                                        'post '.
                                    ') VALUES ('.
                                        '?,'.
                                        '?,'.
                                        '?,'.
                                        '?,'.
                                        '?,'.
                                        '?,'.
                                        '?,'.
                                        'NOW(), '.
                                        '1, '.
                                        '?, '.
                                        '?,'.
                                        '? '.
                                    ')';

                        $stmt   =   $mysqli->prepare($sql);
                        $stmt->bind_param('sssssiisis', $ufname, $uname, $pwd, $uemail, $uphone, $sid, $ugid, $urem, $uauthor, $upost);

                        if (!$stmt->execute()) {
                            $errormsg = $mysqli->error;
                            echo "Error in user data insert: " . $errormsg;
                            if (strpos($errormsg, "Duplicate") == 0) echo "<br>Possibly, the username has been taken. Please choose another.";
                            exit;
                        }

                        $uid            =   mysqli_insert_id($mysqli);

                        if ($uid) {
                            $sites          =   serialize($sites);
                            $departments    =   serialize($departments);
                            $locations      =   serialize($locations);

                            $stmt           =   $mysqli->prepare('INSERT INTO user_access(`uid`,`sites`,`locations`, `departments`) VALUES(?,?,?,?)');
                            $stmt->bind_param('isss', $uid, $sites, $locations, $departments);
                            $stmt->execute();
                        }

                        setSession('added_user', $ufname);
                        setSession('user_added', 1);
                        $sUrl = 'user.php?tab=tabnew#tabnew';

                    } else {
                        $sMsg = "<h3>Error:</h3>\n";
                        if (!withinbound($uname, 6, 50)) $sMsg .= "Username length must be between 6-15<br>\n";
                        $autoredirect = false;
                    }
                }
            } else {
                 $sMsg = "Required fields are empty.";
                 $iDuration = 3000;
            }
        }
        break;
}

$mysqli->close();

if ($autoredirect) {
    echo "<body onload=\"redir('" . $sUrl . "', " . $iDuration . ")\">\n" . $sMsg . "\n</body>\n</html>";
} else {
    echo "<body>\n" . $sMsg . "\n</body>\n</html>";
}

function withinbound($var, $llimit, $ulimit) {
    if (strlen($var) >= $llimit && strlen($var) <= $ulimit) {
        return true;
    }
    return false;
}

function cleanfilename($filename) {
    $reserved = preg_quote('\/:*?"<>|', '/'); //characters that are  illegal on any of the 3 major OS's
    //replaces all characters up through space and all past ~ along with the above reserved characters
    return @preg_replace("/([\\x00-\\x20\\x7f-\\xff{$reserved}])/e", "_", $filename);
}
