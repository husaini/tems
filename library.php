<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/cons.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

$eid    =   (isset($_GET['eid'])) ? $_GET["eid"] : null;
$del    =   (isset($_GET['del'])) ? $_GET["del"] : null;

if($del == 1){
  $do = mysql_query("delete from asset_class where id = '$eid'");
}elseif($del == 2){
  $do = mysql_query("delete from asset_type where id = '$eid'");
}elseif($del == 3){
  $do = mysql_query("delete from asset_manufacturer where id = '$eid'");
}elseif($del == 4){
  $do = mysql_query("delete from asset_model where id = '$eid'");
}else{
  $do = "nothing";
}

// Access level restriction to sites/departments and locations
$usite_ids      =   array();
$uloc_ids       =   array();
$udept_ids      =   array();
$uaccess        =   getSession('access');
$asset_class_opt=   '';
$asset_model_opt=   '';
$asset_type_opt =   '';
$asset_man_opt  =   '';
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
    $uclass_ids =   array();
    $umodel_ids =   array();
    $utype_ids  =   array();
    $umanu_ids  =   array();

    $result     =   $mysqli->query('SELECT `classid`,`manuid`,`modelid`,`typeid` FROM `asset` WHERE siteid IN('.implode(',', $usite_ids).')') or die(mysqli_error($mysqli));
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $uclass_ids[]   =   $row['classid'];
            $umodel_ids[]   =   $row['modelid'];
            $utype_ids[]    =   $row['typeid'];
            $umanu_ids[]    =   $row['manuid'];
        }
        mysqli_free_result($result);
    }

    if($uclass_ids) {
        $uclass_ids         =   array_unique($uclass_ids);
        $asset_class_opt    =  'AND asset_class.id IN('.implode(',', $uclass_ids).') ';
    }
    if($umodel_ids) {
        $umodel_ids         =   array_unique($umodel_ids);
        $asset_model_opt    =  'AND asset_model.id IN('.implode(',', $umodel_ids).') ';
    }
    if($utype_ids) {
        $utype_ids          =   array_unique($utype_ids);
        $asset_type_opt     =  'AND asset_type.id IN('.implode(',', $utype_ids).') ';
    }
    if($umanu_ids) {
        $umanu_ids          =   array_unique($umanu_ids);
        $asset_man_opt      =  'AND asset_manufacturer.id IN('.implode(',', $umanu_ids).') ';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Equipment Library</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    var hash = window.location.hash;
    if(!hash || location.href.indexOf("#") == -1) {
        hash = '#<?php echo (isset($_GET['tab'])) ? $_GET['tab'] : "";?>';
    }
    var activeTabIndex = 0;
    if (hash) {
        hash = hash.substring(1);
        $('#tabs li a').each(function(i) {
            var thisHash = this.href.split('#')[1];
            if(thisHash == hash) {
                activeTabIndex = i;
            }
        });
    }
    $('#tabs').tabs({active: activeTabIndex});
});

function confdel(obj, sid) {
    if (confirm("Are you sure you want to delete this?\nPress OK to proceed or CANCEL to abort.")) {
        document.getElementById('frmhidden').wid.value = sid;
        document.getElementById('frmhidden').submit();
    }
}
</script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Equipment Library</h1>
            <ul>
                <li><a href="#tablist">All</a></li>
                <li><a href="#tabnew">Class</a></li>
                <li><a href="#editol">Category</a></li>
                <li><a href="#man">Manufacturer</a></li>
                <li><a href="#mod">Model</a></li>
            </ul>
            <div id="tablist">
                <h3>List All</h3>
                <table id="liblist" class="full-width">
                    <tr>
                        <th>Class</th>
                        <th>Category</th>
                        <th>Manufacturer</th>
                        <th>Model</th>
                    </tr>
                    <?php
                    $sql    =   'SELECT '.
                                    'asset_class.name AS classname, '.
                                    'asset_type.name AS typename, '.
                                    'asset_manufacturer.name AS manuname, '.
                                    'asset_model.name AS modelname '.
                                'FROM '.
                                    'asset_class '.
                                'JOIN '.
                                    'asset_type ON asset_type.classid = asset_class.id '.
                                'JOIN '.
                                    'asset_model ON asset_model.typeid = asset_type.id '.
                                'JOIN '.
                                    'asset_manufacturer ON asset_model.manuid = asset_manufacturer.id '.
                                $asset_class_opt.
                                'ORDER BY '.
                                    'classname, typename, manuname, modelname';
                    $stmt   =   $mysqli->prepare($sql);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows() > 0):
                        $meta = $stmt->result_metadata();
                        while ($column = $meta->fetch_field()) {
                            $bindvars[] = &$results[$column->name];
                        }
                        call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                        while ($stmt->fetch()): ?>
                            <tr>
                                <td><?php echo $results['classname'];?></td>
                                <td><?php echo $results['typename'];?></td>
                                <td><?php echo $results['manuname'];?></td>
                                <td><?php echo $results['modelname'];?></td>
                            <tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td align="center" colspan="4">No equipments found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php
                        $stmt->close();
                        $mysqli->close();
                    ?>
                </table>
            </div>

            <div id="tabnew">
                <form method="post" action="addclass.php">
                    <input type="submit" value="Add New Class" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width">
                    <tr>
                        <th colspan="2">Class</th>
                    </tr>
                    <?php
                        $sql    =   'SELECT '.
                                        '* '.
                                    'FROM '.
                                        'asset_class '.
                                    'WHERE 1 '.
                                        $asset_class_opt.
                                    'ORDER BY '.
                                        '`name`';

                        $result =   mysql_query($sql) or die(mysql_error($link));

                        if ($result) :?>
                            <?php while ($row = mysql_fetch_assoc($result)) :?>
                                <tr>
                                    <td>
                                        <b><a href="editclass.php?eid=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></b>
                                    </td>
                                    <td>
                                        <a href="library.php?eid=<?php echo $row['id']; ?>&amp;del=1&amp;tab=tabnew">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="editol">
                <form method="post" action="addcat.php">
                    <input type="submit" value="Add New Category" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width">
                    <tr>
                        <th colspan="2">Category</th>
                    </tr>
                    <?php
                        $result =   mysql_query("select * from asset_type WHERE 1 $asset_type_opt order by name") or die(mysql_error($link));;
                        if ($result) :?>
                            <?php while ($row = mysql_fetch_assoc($result)) :?>
                                <tr>
                                    <td>
                                        <b><a href="editcat.php?eid=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></b>
                                    </td>
                                    <td>
                                        <a href="library.php?eid=<?php echo $row['id']; ?>&amp;del=2&amp;tab=editol">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="man">
                <form method="post" action="addman.php">
                    <input type="submit" value="Add New Manufacturer" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width">
                    <tr>
                        <th colspan="2">Manufacturer</th>
                    </tr>
                    <?php
                        $result =   mysql_query("select * from asset_manufacturer WHERE 1 $asset_man_opt order by name") or die(mysql_error($link));;
                        if ($result) :?>
                            <?php while ($row = mysql_fetch_assoc($result)) :?>
                                <tr>
                                    <td>
                                        <b><a href="editman.php?eid=<?php echo $row['id'];?>"><?php echo $row['name'];?></a></b>
                                    </td>
                                    <td>
                                        <a href="library.php?eid=<?php echo $row['id'] ?>&amp;del=3&amp;tab=man">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="mod">
                <form method="post" action="addmodel.php">
                    <input type="submit" value="Add New Model" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width">
                    <tr>
                        <th colspan="2">Model</th>
                    </tr>
                    <?php
                        $result =   mysql_query("select * from asset_model WHERE 1 $asset_model_opt order by name") or die(mysql_error($link));;
                        if ($result) :?>
                            <?php while ($row = mysql_fetch_assoc($result)) :?>
                                <tr>
                                    <td>
                                        <b><a href="editmodel.php?eid=<?php echo $row['id'];?>"><?php echo $row['name'];?></a></b>
                                    </td>
                                    <td>
                                        <a href="library.php?eid=<?php echo $row['id'];?>&amp;del=4&amp;tab=mod">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
