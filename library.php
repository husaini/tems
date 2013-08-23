<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/cons.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

$eid    =   (isset($_GET['eid'])) ? $_GET["eid"] : null;
$del    =   (isset($_POST['del']) && is_numeric($_POST['del'])) ? intval($_POST["del"],10) : null;

if($del && (isset($_POST['id']) && is_numeric($_POST['id'])))
{
    $id     =   intval($_POST['id'], 10);
    switch ($del)
    {
        case 1:
            $table  =   'asset_class';
            $type   =   'Class';
            break;

        case 2:
            $table  =   'asset_type';
            $type   =   'Category';
            break;

        case 3:
            $table  =   'asset_manufacturer';
            $type   =   'Manufacturer';
            break;

        case 4:
            $table  =   'asset_model';
            $type   =   'Model';
            break;
        default:
            $table  =   null;
            $type   =   '';
    }

    if ($table)
    {
        // get this asset name first
        $result =   $mysqli->query("SELECT `name` FROM `$table` WHERE id = '$id'") or die(mysqli_error($mysqli));
        $asset_name =   null;
        if($result)
        {
            list($asset_name)   =   $result->fetch_row();
            mysqli_free_result($result);
        }

        $sql    =   "DELETE FROM `$table` WHERE id = ?";

        $stmt   =   $mysqli->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute())
        {
            if($asset_name)
            {
                setSession('item_deleted', $type.'<em>"'.$asset_name.'"</em> was successfully deleted.');
                $tab    =   (isset($_POST['tab'])) ? $_POST['tab']:'';
                header('location: library.php?tab='.$tab);
                exit();
            }
            else
            {
                header('location: '.$_SERVER['REQUEST_URI']);
                exit();
            }
        }
        else
        {

        }
    }
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

// Changes: 20130823 Husaini
// Limit by sites assigned to user not admin
if ($usite_ids && !isadmin()) {
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
        if($uclass_ids)
        {
            foreach ($uclass_ids as $key => $value)
            {
                if(!$value)
                {
                    unset($uclass_ids[$key]);
                }
            }
        }

        if($uclass_ids)
        {
            $asset_class_opt    =  'AND asset_class.id IN('.implode(',', $uclass_ids).') ';
        }
    }
    if($umodel_ids) {
        $umodel_ids         =   array_unique($umodel_ids);
        if($umodel_ids)
        {
            foreach ($umodel_ids as $key => $value)
            {
                if(!$value)
                {
                    unset($umodel_ids[$key]);
                }
            }
        }
        if($umodel_ids)
        {
            $asset_model_opt    =  'AND asset_model.id IN('.implode(',', $umodel_ids).') ';
        }
    }
    if($utype_ids) {
        $utype_ids          =   array_unique($utype_ids);
        if($utype_ids)
        {
            foreach ($utype_ids as $key => $value)
            {
                if(!$value)
                {
                    unset($utype_ids[$key]);
                }
            }
        }
        if($utype_ids)
        {
            $asset_type_opt     =  'AND asset_type.id IN('.implode(',', $utype_ids).') ';
        }
    }
    if($umanu_ids) {
        $umanu_ids          =   array_unique($umanu_ids);
        if($umanu_ids)
        {
            foreach ($umanu_ids as $key => $value)
            {
                if(!$value)
                {
                    unset($umanu_ids[$key]);
                }
            }
        }
        if($umanu_ids)
        {
            $asset_man_opt      =  'AND asset_manufacturer.id IN('.implode(',', $umanu_ids).') ';
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Equipment Library</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
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

    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 1500);
    }
    $('table.eq-list tr').hover(function(){
        $(this).addClass("hl");
    },function(){
        $(this).removeClass("hl");
    });


    if ($('table.eq-list tbody.item-list').length > 0) {
        $('table.eq-list tbody.item-list').each(function() {
            var table = $(this).closest('table');
            var oTable  =   table.dataTable({
                                bJQueryUI: true,
                                bCaseInsensitive: true,
                                iDisplayLength: 25,
                                sPaginationType: 'full_numbers',
                                aoColumnDefs: [
                                    {
                                        bSortable: false,
                                        aTargets: [ -1 ]
                                    },
                                    {
                                        "sTitle": "Action",
                                        "aTargets": [ -1 ]
                                    },
                                    {
                                        "sClass": "center",
                                        "aTargets": [ -1 ]
                                    }
                                ]
                            });

            oTable.fnSort([[0,'desc']]);//sort by first col
        });
    }
    $('table#all-eq').dataTable({
        bJQueryUI: true,
        bCaseInsensitive: true,
        iDisplayLength: 25,
        sPaginationType: 'full_numbers'
    });
    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 2500);
    }
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
            <div class="clear">&nbsp;</div>
            <?php if(getSession('item_deleted')): ?>
                <p class="alert alert-success"><?php echo getSession('item_deleted', true);?></p>
            <?php endif; ?>

            <?php if(getSession('asset_item_added')): ?>
                <p class="alert alert-success"><?php echo stripslashes(getSession('asset_item_added', true));?></p>
            <?php endif; ?>

            <?php if(getSession('asset_item_updated')): ?>
                <p class="alert alert-success"><?php echo stripslashes(getSession('asset_item_updated', true));?></p>
            <?php endif; ?>

            <div id="tablist">
                <h3>List All</h3>
                <table id="all-eq" class="full-width">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Category</th>
                            <th>Manufacturer</th>
                            <th>Model</th>
                        </tr>
                    </thead>
                    <?php
                    $sql    =   'SELECT '.
                                    'asset_class.name AS classname, '.
                                    'asset_class.id AS class_id,'.
                                    'asset_type.name AS typename, '.
                                    'asset_type.id AS type_id,'.
                                    'asset_manufacturer.name AS manuname, '.
                                    'asset_manufacturer.id AS manufacturer_id,'.
                                    'asset_model.name AS modelname, '.
                                    'asset_model.id AS model_id '.
                                'FROM '.
                                    'asset_class '.
                                'LEFT JOIN '.
                                    'asset_type ON asset_type.classid = asset_class.id '.
                                'LEFT JOIN '.
                                    'asset_model ON asset_model.typeid = asset_type.id '.
                                'LEFT JOIN '.
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
                        ?>

                        <tbody>
                            <?php while ($stmt->fetch()): ?>
                                <tr>
                                    <td>
                                        <?php if($results['class_id']): ?>
                                            <a href="editclass.php?eid=<?php echo $results['class_id'];?>&amp;tab=tablist"><?php echo $results['classname'];?></a>
                                        <?php else: ?>
                                            <?php echo $results['classname'];?>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if($results['type_id']): ?>
                                            <a href="editcat.php?eid=<?php echo $results['type_id'];?>&amp;tab=tablist"><?php echo $results['typename'];?></a>
                                        <?php else: ?>
                                            <?php echo $results['typename'];?>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if($results['manufacturer_id']): ?>
                                            <a href="editman.php?eid=<?php echo $results['manufacturer_id'];?>&amp;tab=tablist"><?php echo $results['manuname'];?></a>
                                        <?php else: ?>
                                            <?php echo $results['manuname'];?>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if($results['model_id']): ?>
                                            <a href="editmodel.php?eid=<?php echo $results['model_id'];?>&amp;tab=tablist"><?php echo $results['modelname'];?></a>
                                        <?php else: ?>
                                            <?php echo $results['modelname'];?>
                                        <?php endif;?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    <?php else: ?>
                        <tbody>
                            <tr>
                                <td colspan="4" class="center">No equipments found.</td>
                            </tr>
                        </tbody>
                    <?php endif; ?>
                    <?php
                        $stmt->close();
                        $mysqli->close();
                    ?>
                </table>
            </div>

            <div id="tabnew">
                <?php if(getSession('class_added', true)): ?>
                    <p class="alert alert-success">Asset class was successfully added.</p>
                <?php endif; ?>
                <form method="post" action="addclass.php">
                    <input type="submit" value="Add New Class" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width eq-list">
                    <thead>
                        <tr>
                            <th width="10" nowrap="nowrap">ID</th>
                            <th>Class</th>
                            <th></th>
                        </tr>
                    </thead>
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

                        if ($result) : ?>
                            <?php if (mysql_num_rows($result)): ?>
                                <tbody class="item-list">
                                    <?php while ($row = mysql_fetch_assoc($result)) :?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editclass.php?eid=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                                <?php else: ?>
                                                    <?php echo $row['name']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editclass.php?eid=<?php echo $row['id']; ?>">Edit </a> |
                                                    <a data-tab="tabnew" data-type="1" data-id="<?php echo $row['id']?>" href="library.php?eid=<?php echo $row['id']; ?>&amp;del=1&amp;tab=tabnew" class="delete-item"> Delete</a>
                                                <?php endif;?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>

                                </tbody>
                            <?php else: ?>
                                <tbody>
                                    <tr>
                                        <td align="center" colspan="3">No classes found.</td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="editol">
                <form method="post" action="addcat.php">
                    <input type="submit" value="Add New Category" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width eq-list">
                    <thead>
                        <tr>
                            <th width="10" nowrap="nowrap">ID</th>
                            <th>Category</th>
                            <th></th>
                        </tr>
                    </thead>
                    <?php
                        $result =   mysql_query("select * from asset_type WHERE 1 $asset_type_opt order by name") or die(mysql_error($link));;
                        if ($result) :?>
                            <?php if(mysql_num_rows($result)): ?>
                                <tbody class="item-list">
                                    <?php while ($row = mysql_fetch_assoc($result)) :?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editcat.php?eid=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                                <?php else: ?>
                                                    <?php echo $row['name']; ?>
                                                <?php endif;?>
                                            </td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editcat.php?eid=<?php echo $row['id']; ?>">Edit </a> |
                                                    <a data-tab="editol" data-type="2" data-id="<?php echo $row['id'];?>" href="library.php?eid=<?php echo $row['id']; ?>&amp;del=2&amp;tab=editol" class="delete-item"> Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            <?php else: ?>
                                <tbody>
                                    <tr>
                                        <td align="center" colspan="3">No categories found.</td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="man">
                <form method="post" action="addman.php">
                    <input type="submit" value="Add New Manufacturer" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width eq-list">
                    <thead>
                        <tr>
                            <th width="10" nowrap="nowrap">ID</th>
                            <th>Manufacturer</th>
                            <th></th>
                        </tr>
                    </thead>
                    <?php
                        $result =   mysql_query("select * from asset_manufacturer WHERE 1 $asset_man_opt order by name") or die(mysql_error($link));;
                        if ($result) :?>
                            <?php if (mysql_num_rows($result)): ?>
                                <tbody class="item-list">
                                    <?php while ($row = mysql_fetch_assoc($result)) :?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editman.php?eid=<?php echo $row['id'];?>"><?php echo $row['name'];?></a>
                                                <?php else: ?>
                                                    <?php echo $row['name'];?>
                                                <?php endif?>
                                            </td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editman.php?eid=<?php echo $row['id'];?>">Edit </a> |
                                                    <a data-tab="man" data-type="3"  data-id="<?php echo $row['id'];?>" href="library.php?eid=<?php echo $row['id'] ?>&amp;del=3&amp;tab=man" class="delete-item"> Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            <?php else: ?>
                                <tbody>
                                    <tr>
                                        <td align="center" colspan="3">No manufacturers found.</td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>

            <div id="mod">
                <form method="post" action="addmodel.php">
                    <input type="submit" value="Add New Model" class="btn btn-primary" />
                </form>
                <p class="clear"></p>
                <table class="full-width eq-list">
                    <thead>
                        <tr>
                            <th width="10" nowrap="nowrap">ID</th>
                            <th>Model</th>
                            <th></th>
                        </tr>
                    </thead>
                    <?php
                        $result =   mysql_query("select * from asset_model WHERE 1 $asset_model_opt order by name") or die(mysql_error($link));

                        if ($result) :?>
                            <?php if (mysql_num_rows($result)): ?>
                                <tbody class="item-list">
                                    <?php while ($row = mysql_fetch_assoc($result)) :?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editmodel.php?eid=<?php echo $row['id'];?>"><?php echo $row['name'];?></a>
                                                <?php else: ?>
                                                    <?php echo $row['name']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['id'] != 0): ?>
                                                    <a href="editmodel.php?eid=<?php echo $row['id'];?>">Edit </a> |
                                                    <a data-tab="mod" data-type="4" data-id="<?php echo $row['id'];?>" href="library.php?eid=<?php echo $row['id'];?>&amp;del=4&amp;tab=mod" class="delete-item"> Delete</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            <?php else: ?>
                                <tbody>
                                    <tr>
                                        <td align="center" colspan="3">No models found.</td>
                                    </tr>
                                </tbody>
                            <?php endif; ?>
                            <?php mysql_free_result($result); ?>
                        <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    $(function() {
        $('.delete-item').click(function(e) {
            e.preventDefault;
            var me = $(this);
            var meTab = me.data('tab') || null;
            var meID = me.data('id') || null;
            var meType = me.data('type') || null;
            if (!meTab || !meID || !meType) {
                return false;
            }
            var message = 'You are about to delete this item. Are you sure you want to do this ?';
            if(confirm(message)) {
                var form = document.createElement('form');
                form.method = 'post';
                form.name='frmDeleteItem';
                var i1 = document.createElement('input');
                i1.type='hidden';
                i1.name = 'tab';
                i1.value = meTab;
                form.appendChild(i1);
                var i2 = document.createElement('input');
                i2.type='hidden';
                i2.name = 'id';
                i2.value = meID;
                form.appendChild(i2);
                var i3 = document.createElement('input');
                i3.type='hidden';
                i3.name = 'del';
                i3.value = meType;
                form.appendChild(i3);
                document.body.appendChild(form);
                frmDeleteItem.submit();
            }

            return false;
        });
    });
    </script>
</body>
</html>
