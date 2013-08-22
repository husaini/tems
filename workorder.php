<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

date_default_timezone_set("Asia/Kuala_Lumpur");

$aid    =   empty($_GET['aid'])? 0: $_GET['aid'];
$sid    =   $_SESSION['sid'];
$rem    =   $_SESSION['rem'];

$page   =   isset($_GET['p'])? (is_numeric($_GET['p'])? $_GET['p']: 0): 0;
$limit  =   50;
$offset =   $page * $limit;

// Access level restriction to sites/departments and locations
$usite_ids      =   array();
$uloc_ids       =   array();
$udept_ids      =   array();
$uaccess        =   getSession('access');

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

if($rem) {
    $temp   =   explode(',', $rem);
    foreach ($temp as $key => $value) {
        if(!is_numeric($value)) {
            unset($temp[$key]);
        }
    }
    $rem    =   array_merge($temp, $usite_ids);
    $rem    =   implode(',', $rem);
}

function workscoping($catbit) {
    $wscope = "";
    if ($catbit & 1) $wscope .= "Preventive<br />";
    if ($catbit & 2) $wscope .= "Corrective<br />";
    if ($catbit & 4) $wscope .= "Validation<br />";
    if ($catbit & 8) $wscope .= "Calibration<br />";
    return $wscope;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Work Order</title>

<link rel="stylesheet" href="css/jquery.multiselect.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.validationengine.css" type="text/css" media="screen">

<?php if (!isguest()): ?>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine.js"></script>
<script type="text/javascript" src="js/jquery.validationEngine-en.js"></script>
<script type="text/javascript" src="js/jquery.multiselect.min.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 1500);
    }
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
    $('#new_workorder').validationEngine('attach');
    $('#dtrequire').datepicker({
        changeMonth:true,
        changeYear:true,
        dateFormat:'yy-mm-dd',
        yearRange:'1980:2020',
        beforeShow: function() {
            $(this).validationEngine('hide');
        },
        onSelect: function(dateText, inst) {
            $(this).validationEngine('hide');
        }
    });
    $('#dtcomplete').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtrequiremin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtrequiremax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcompletemin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $('#dtcompletemax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
    $("#assetid").multiselect({'minWidth':550});

    $('#tblwo tr').hover(function(){$(this).addClass("hl");},function(){$(this).removeClass("hl");});
    $('#tblwo tr').click(function(e){
        e.preventDefault();
        var woID = $(this).data('workorderId') || 0;
        if(woID)
        {
            window.location = "editworkorder.php?id=" + woID;
        }
    });

    if ($('#tblwo tbody.wo-list').length > 0) {
        var oTable = $('#tblwo').dataTable({
            bJQueryUI: true,
            iDisplayLength: 25,
            sPaginationType: 'full_numbers'
        });
        oTable.fnSort( [[1,'desc'] ] );//sort by date created
    }

    <?php if ($aid) echo "$('#tabs').tabs('select', '#tabnew');\n"; ?>


    $('#new_workorder').find('input[class^="validate"]').each(function() {
        $(this).blur(function() {
            if($.trim(this.value).length > 0) {
                $(this).validationEngine('hide');
            }
        });

    });
    $('#new_workorder').submit(function(e) {
        var assets = $('#assetid').multiselect("getChecked").length;
        if(!assets) {
            alert('No asset selected');
            return false;
        }
        return 1;
    });
});

function confdel(obj, sid) {
    if (confirm("Are you sure you want to delete this?\nPress OK to proceed or CANCEL to abort.")) {
        document.getElementById('frmhidden').wid.value = sid;
        document.getElementById('frmhidden').submit();
    }
}
</script>

<?php endif; ?>

</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Work Order</h1>
            <ul>
                <li><a href="#tablist">List</a></li>
                <li><a href="#tabsearch">Search</a></li>
                <li><a href="#tabnew">New</a></li>
            </ul>
            <div id="tablist">
                <h3>Work Order List</h3>
                <table id="tblwo" class="tlist">
                    <thead>
                        <tr>
                            <th class="ui-state-default">WO ID</th>
                            <th class="ui-state-default">Date Created</th>
                            <th class="ui-state-default">Equipment</th>
                            <th class="ui-state-default">Manufacturer</th>
                            <th class="ui-state-default">Model</th>
                            <th class="ui-state-default">Asset S/N</th>
                            <th class="ui-state-default">Category</th>
                            <?php /*<th class="ui-state-default">Vendor</th>*/?>
                            <th class="ui-state-default">Status</th>
                            <th class="ui-state-default">Required</th>
                            <th class="ui-state-default">Completed</th>
                        </tr>
                    </thead>
                    <?php
                        $wostatus[1] = "Scheduled";
                        $wostatus[2] = "Completed";
                        $wostatus[3] = "Cancelled";

                        sqltoarray("select id, name from vendor where (type = 0 or type = 2) and status = 1 order by name", $vendor);

                        if ($sid > 0 && $sid != 65535) {
                            $stmt = $mysqli->prepare("SELECT SQL_CALC_FOUND_ROWS workorder.id, workorder.orderno, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno, category, vendor.name, workorder.status, required, completed
                                FROM workorder
                                LEFT JOIN asset on workorder.assetid = asset.id
                                LEFT JOIN vendor on workorder.vendorid = vendor.id
                                LEFT JOIN asset_type ON asset_type.id = asset.typeid
                                LEFT JOIN asset_manufacturer ON asset_manufacturer.id = asset.manuid
                                LEFT JOIN asset_model ON asset_model.id = asset.modelid
                                WHERE asset.siteid = ?
                                ORDER BY workorder.modified DESC
                                LIMIT ? OFFSET ?");
                            $stmt->bind_param('iii', $sid, $limit, $offset);
                        } else if ($sid == 65535) {
                            $stmt = $mysqli->prepare("SELECT SQL_CALC_FOUND_ROWS workorder.id, workorder.orderno, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno, category, vendor.name, workorder.status, required, completed
                                FROM workorder
                                LEFT JOIN asset on workorder.assetid = asset.id
                                LEFT JOIN vendor on workorder.vendorid = vendor.id
                                LEFT JOIN asset_type ON asset_type.id = asset.typeid
                                LEFT JOIN asset_manufacturer ON asset_manufacturer.id = asset.manuid
                                LEFT JOIN asset_model ON asset_model.id = asset.modelid
                                WHERE asset.siteid IN (" . $rem . ")
                                ORDER BY workorder.modified DESC
                                LIMIT ? OFFSET ?");
                            $stmt->bind_param('ii', $limit, $offset);
                        } else {
                            $clause =   '';
                            if ($usite_ids) {
                                $clause .=  'AND asset.siteid IN('.implode(',', $usite_ids).') ';
                            }
                            $sql    =   'SELECT SQL_CALC_FOUND_ROWS '.
                                            'workorder.id, '.
                                            'workorder.orderno, '.
                                            'workorder.created AS workorder_date,'.
                                            'asset.assetno AS tems_no,'.
                                            'asset.siteid AS asset_site_id,'.
                                            'asset_type.name AS typename, '.
                                            'asset_manufacturer.name AS manuname, '.
                                            'asset_model.name AS modelname, '.
                                            'serialno, '.
                                            'category, '.
                                            'vendor.name, '.
                                            'workorder.status, '.
                                            'required, '.
                                            'completed '.
                                        'FROM '.
                                            'workorder '.
                                        'INNER JOIN '.
                                            'asset on workorder.assetid = asset.id '.
                                        'LEFT JOIN '.
                                            'vendor ON workorder.vendorid = vendor.id '.
                                        'LEFT JOIN '.
                                            'asset_type ON asset_type.id = asset.typeid '.
                                        'LEFT JOIN '.
                                            'asset_manufacturer ON asset_manufacturer.id = asset.manuid '.
                                        'LEFT JOIN '.
                                            'asset_model ON asset_model.id = asset.modelid '.
                                        'WHERE 1 '.
                                        $clause.' '.
                                        'ORDER BY '.
                                            'workorder.created DESC ';
                                        //'LIMIT ? OFFSET ?';
                            //echo $sql;
                            $stmt   =   $mysqli->prepare($sql);
                            //$stmt->bind_param('ii', $limit, $offset);
                        }

                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows() > 0): ?>

                                <tbody class="wo-list">
                                <?php
                                    $meta = $stmt->result_metadata();
                                    while ($column = $meta->fetch_field()) {
                                        $bindvars[] = &$results[$column->name];
                                    }
                                    call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                                    while ($stmt->fetch()) :
                                    ?>
                                        <tr data-workorder-id="<?php echo $results['id']?>">
                                            <td>
                                                <?php
                                                    $site_id    =   $results['asset_site_id'];
                                                    if ($site_id < 10)
                                                    {
                                                        $site_id    =   '0'.$site_id;
                                                    }
                                                    // Concatenate strings for workorder id as in PDF format
                                                    // TEMS no
                                                    $wotems_no  =   array_pop(explode('-', $results['tems_no']));
                                                    $wotems_no  =   (String)$wotems_no;
                                                    // case when tems no not using correct format, it should be 5 chars as we we pad it on asset upload
                                                    if (strlen($wotems_no) < 5)
                                                    {
                                                        $wotems_no   =   str_pad($wotems_no,5,'0', STR_PAD_LEFT);
                                                    }

                                                    //Workorder year
                                                    $woyear     =   date('y', strtotime($results['workorder_date']));

                                                    //Workorder no.
                                                    $wonum      =   str_pad($results['id'],5,'0', STR_PAD_LEFT);
                                                    $woid       =   "$site_id$wotems_no-$woyear-BEM-$wonum";
                                                ?>
                                                <a href="editworkorder.php?id=<?php echo $results['id'];?>"><?php echo $woid;?></a>
                                            </td>
                                            <td><?php echo date('Y/m/d', strtotime($results['workorder_date']));?></td>
                                            <td><?php echo $results['typename'];?></td>
                                            <td><?php echo $results['manuname']; ?></td>
                                            <td><?php echo $results['modelname']; ?></td>
                                            <td><?php echo $results['serialno'];?></td>
                                            <td><?php echo workscoping($results['category']);?></td>
                                            <?php /*<td><?php echo $results['name'];?></td>*/?>
                                            <td><?php echo $wostatus[$results['status']];?></td>
                                            <td><?php echo $results['required'];?></td>
                                            <td><?php echo $results['completed'];?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                                <tbody>
                                    <tr>
                                        <td colspan="11" align="center">No work orders found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>

                <?php
                    sqltoarray("select id, name from vendor where (type = 0 or type = 2) and status = 1 order by name", $vendor);
                    $listid = (isset($_GET['listid']))? explode(",", $_GET['listid']) : array(-1, 0);
                ?>
            </div>

            <div id="tabsearch">
                <h3>Search Work Order</h3>
                <form method="post" action="searchworkorder.php">
                    <table class="full-width no-border">
                        <tr>
                            <td>Work Category</td>
                            <td>
                                <input type="checkbox" name="woprv">Preventive
                                <input type="checkbox" name="wocrt">Corrective
                                <input type="checkbox" name="wovld">Validation
                                <input type="checkbox" name="swoclb">Calibration
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <select name="wostatus" id="wostatus">
                                    <option value="0">ANY</option>
                                    <option value="1">Scheduled</option>
                                    <option value="2">Completed</option>
                                    <option value="3">Cancelled</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Vendor</td>
                            <td>
                                <select name="vendorid" id="vendorid">
                                    <option value="0">ANY</option>
                                    <?php optionize($vendor); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Date Required</td>
                            <td>
                                <input type="text" name="dtrequiremin" id="dtrequiremin" size="10" maxlength="15" value="" />
                                to
                                <input type="text" name="dtrequiremax" id="dtrequiremax" size="10" maxlength="15" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Date Completed</td>
                            <td>
                                <input type="text" name="dtcompletemin" id="dtcompletemin" size="10" maxlength="15" value="" />
                                to
                                <input type="text" name="dtcompletemax" id="dtcompletemax" size="10" maxlength="15" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Report No</td>
                            <td>
                                <input type="text" name="orderno" size="30" maxlength="20" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Cost (RM)</td>
                            <td>
                                <input type="text" name="wocostmin" size="30" maxlength="20" value="" />
                                to
                                <input type="text" name="wocostmax" size="30" maxlength="20" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Submit Data" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="tabnew">
                <h3>Create New Work Order</h3>
                <?php if (isworker()): ?>
                    <form id="new_workorder" method="post" action="mod.php">
                        <input type="hidden" name="func" value="add_workorder_bulk" />
                        <table class="full-width no-border">
                        <?php if ($aid): ?>
                            <tr>
                                <td>Asset ID</td>
                                <td>
                                    <input type="text" name="assetid" size="30" maxlength="15" value="<?php echo $aid;?>" readonly />
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td>Asset(s) <span class="required">*</span></td>
                                <td>
                                    <select type="text" name="assetid[]" id="assetid" class="multiselect" multiple="multiple"  class="validate[required]">
                                    <?php
                                        $stmt->free_result();
                                        $stmt->close();
                                        unset($bindvars);
                                        unset($results);

                                        if ($sid > 0 && $sid != 65535) {
                                            $stmt = $mysqli->prepare("select asset.id, assetno, asset_class.name classname, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno from asset
                                                left join asset_class on asset.classid = asset_class.id
                                                left join asset_type on asset.typeid = asset_type.id
                                                left join asset_manufacturer on asset.manuid = asset_manufacturer.id
                                                left join asset_model on asset.modelid = asset_model.id
                                                where asset.siteid = ?
                                                order by asset_class.name, asset_type.name, asset_manufacturer.name, asset_model.name, serialno");
                                            $stmt->bind_param('i', $sid);
                                        } else if ($sid == 65535) {
                                            $stmt = $mysqli->prepare("select asset.id, assetno, asset_class.name classname, asset_type.name typename, asset_manufacturer.name manuname, asset_model.name modelname, serialno from asset
                                                left join asset_class on asset.classid = asset_class.id
                                                left join asset_type on asset.typeid = asset_type.id
                                                left join asset_manufacturer on asset.manuid = asset_manufacturer.id
                                                left join asset_model on asset.modelid = asset_model.id
                                                where asset.siteid in (" . $rem . ")
                                                order by asset_class.name, asset_type.name, asset_manufacturer.name, asset_model.name, serialno");
                                        } else {
                                            $clause =   '';
                                            if ($usite_ids) {
                                                $clause .=  'AND asset.siteid IN('.implode(',', $usite_ids).') ';
                                            }

                                            $sql    =   'SELECT '.
                                                            'asset.id, '.
                                                            'assetno, '.
                                                            'asset_class.name AS classname, '.
                                                            'asset_type.name AS typename, '.
                                                            'asset_manufacturer.name AS manuname, '.
                                                            'asset_model.name AS modelname, '.
                                                            'serialno '.
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
                                                        'WHERE 1 '.
                                                            $clause.' '.
                                                        'ORDER BY '.
                                                            'asset_class.name, '.
                                                            'asset_type.name, '.
                                                            'asset_manufacturer.name, '.
                                                            'asset_model.name, '.
                                                            'serialno';

                                            $stmt   =   $mysqli->prepare($sql);
                                        }
                                        $stmt->execute();
                                        $stmt->store_result();

                                        if ($stmt->num_rows() > 0) {
                                            $meta = $stmt->result_metadata();
                                            while ($column = $meta->fetch_field()) {
                                                $bindvars[] = &$results[$column->name];
                                            }
                                            call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                                            while ($stmt->fetch()) :?>
                                                <?php
                                                    $selected = '';
                                                    if (in_array($results['id'], $listid)) {
                                                        $selected = ' selected="selected"';
                                                    }
                                                ?>
                                                <option value="<?php echo $results['id'];?>"<?php echo $selected;?>>
                                                    <?php echo $results['classname'] . " " . $results['manuname'] . " " . $results['modelname'] . " (" . $results['serialno'] . ")";?>
                                                </option>
                                            <?php endwhile;
                                        }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <tr>
                                <td>Description</td>
                                <td>
                                    <textarea name="wodesc" rows="2" cols="55"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>Date Required <span class="required">*</span></td>
                                <td>
                                    <input type="text" name="dtrequire" id="dtrequire" size="10" maxlength="15" value=""  class="validate[required,custom[date]]" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Submit Data" class="btn btn-primary">
                                </td>
                            </tr>
                        </table>
                    </form>
                    <form id="frmhidden" action="mod.php" method="post">
                        <input type="hidden" name="func" value="del_workorder">
                        <input type="hidden" name="woid">
                    </form>

                <?php else: ?>
                    <p><i>You are not authorized to use this function.</i></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    $stmt->close();
    $mysqli->close();
?>
