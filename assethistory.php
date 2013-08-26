<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

if (empty($_GET['id'])) {
    echo "<b>Fatal Error:</b> \"Relax,\" said the night man, \"We are programmed to receive. You can check-out any time you like, but you can never leave!\"";
    exit();
}
$id = $_GET['id'];
$sid = $_SESSION['sid'];

date_default_timezone_set("Asia/Kuala_Lumpur");

$stmt = $mysqli->prepare("select assetno, asset_class.name classname, asset_type.name typename, asset_manufacturer.name manuname,
        asset_model.name modelname, serialno, refno, orderno, asset_status.name statusname,
        history_asset.remarks, site.name sitename, site_location.name locname, purchasedate, vendor.name vendorname,
        price, warrantystart, warrantyend, ppmstart, ppmfreq,
        history_asset.created, user.name username
    from history_asset
    join user on history_asset.author = user.id
    join asset_class on history_asset.classid = asset_class.id
    join asset_type on history_asset.typeid = asset_type.id
    join asset_manufacturer on history_asset.manuid = asset_manufacturer.id
    join asset_model on history_asset.modelid = asset_model.id
    join asset_status on history_asset.status = asset_status.id
    join site on history_asset.siteid = site.id
    join site_location on history_asset.locationid = site_location.id
    join vendor on history_asset.supplierid = vendor.id
    where history_asset.id = ?
    order by history_asset.created");

$stmt->bind_param('i', $id);

$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows() > 0) {
    $meta = $stmt->result_metadata();
    while ($column = $meta->fetch_field()) {
        $bindvars[] = &$results[$column->name];
        $temp[$column->name] = "";
    }
    call_user_func_array(array($stmt, 'bind_result'), $bindvars);

    echo "<table class=\"tlist\"><tr><th>Date/Time</th><th>Modification</th><th>Author</th></tr>";
    while ($stmt->fetch()) {
        $changes = "";
        foreach($results as $k => $v) {
            if ($k != "username" && $k != "created") {
                if ($temp[$k] != $results[$k]) {
                    $changes .= "<b>" . strtoupper(str_replace("name", "", $k)) . ":</b> <i>" . $temp[$k] . "</i> -> " . $results[$k] . "<br />";
                    $temp[$k] = $results[$k];
                }
            }
        }
        echo "<tr><td>" . $results['created'] . "</td><td>" . $changes . "</td><td>" . $results['username'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<i>History record is empty.</i>";
}

$stmt->close();
$mysqli->close();
?>
