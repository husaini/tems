<?php
if (empty($_GET['id'])) {
    echo "<b>Fatal Error:</b> \"Relax,\" said the night man, \"We are programmed to receive. You can check-out any time you like, but you can never leave!\"";
    exit();
}

require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/cons.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');
require_once(dirname(__FILE__).'/calendar/classes/tc_calendar.php');

function cleanfilename($filename) {
    $reserved = preg_quote('\/:*?"<>|', '/'); //characters that are  illegal on any of the 3 major OS's
    //replaces all characters up through space and all past ~ along with the above reserved characters
    return @preg_replace("/([\\x00-\\x20\\x7f-\\xff{$reserved}])/e", "_", $filename);
}

function workscoping($catbit) {
    $wscope = "";
    if ($catbit & 1) $wscope .= "Preventive<br />";
    if ($catbit & 2) $wscope .= "Corrective<br />";
    if ($catbit & 4) $wscope .= "Validation<br />";
    if ($catbit & 8) $wscope .= "Calibration<br />";
    return $wscope;
}

$filelist   =   "";
$id         =   $_GET['id'];
$sid        =   $_SESSION['sid'];
$rem        =   $_SESSION['rem'];

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

date_default_timezone_set("Asia/Kuala_Lumpur");

$sql    =   'SELECT '.
                'asset.*, '.
                'calibration, '.
                'validation, '.
                'ai.roomno,'.
                'ai.under_contract '.
            'FROM '.
                'asset '.
            'LEFT JOIN '.
                'asset_type ON asset.typeid = asset_type.id '.
            'LEFT JOIN '.
                'asset_info ai ON ai.assetid = asset.id '.
            'WHERE '.
                'asset.id = ?';

$stmt = $mysqli->prepare($sql) or die(mysqli_error($mysqli));
$stmt->bind_param('i', $id);

$stmt->execute();
$stmt->store_result();
$meta = $stmt->result_metadata();
while ($column = $meta->fetch_field()) {
  $bindvars[] = &$results[$column->name];
}
call_user_func_array(array($stmt, 'bind_result'), $bindvars);
if (!$stmt->fetch()) {
    $stmt->close();
    $mysqli->close();
    echo "<b>Fatal Error:</b> Asset not found.";
    exit();
}

$asid = $results['siteid'];

// Check if user has access to this asset by siteid
if(!$usite_ids) {
    die('You are not allowed to edit this asset.');
} elseif(!in_array($asid, $usite_ids)) {
    die('You are not allowed to edit this asset.');
}


tabletoarray("asset_status", $assetstatus);
if ($sid == 65535) {
    sqltoarray("select id, name from site where id in (" . $rem . ")", $site);
} else {
    tabletoarray("site", $site);
}
tabletoarray("user", $user);
sqltoarray("select id, name from vendor where (type = 0 or type = 1) and status = 1 order by name", $supplier);
sqltoarray("select id, email as name from vendor where (type = 0 or type = 1) and status = 1 order by name", $supplieremail);
sqltoarray("select id, name from vendor where (type = 0 or type = 2) and status = 1 order by name", $vendor);

$wsvld          =   $results['validation'];
$wsclb          =   $results['calibration'];

$appmfreq[0]    =   "None";
$appmfreq[1]    =   "Once A Year";
$appmfreq[2]    =   "Twice A Year";
$appmfreq[3]    =   "3 Times A Year";
$appmfreq[4]    =   "Once Every Quarter";
$appmfreq[6]    =   "Once Every 2 Months";
$appmfreq[12]   =   "Once A Month";

$doccat[0]      =   "Miscellaneous";
$doccat[1]      =   "PPM / Validation / Calibration";
$doccat[2]      =   "Repairs";
$doccat[3]      =   "Manuals";
$doccat[4]      =   "Financial";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Edit Asset</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="fancybox/jquery.fancybox.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.validationengine.css" type="text/css" media="screen">

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="fancybox/jquery.fancybox.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-1.6.4.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-en-1.6.4.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
<script type="text/javascript">
    $(function() {
        $("#datepicker").datepicker();
        $("#datepicker2").datepicker();

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
        $('#dtpurchase').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntstart').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntend').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtppmstart').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtrequire').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtcomplete').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#unta').validationEngine();
        $('#tblassetwo tr').hover(function(){$(this).addClass("hl");},function(){$(this).removeClass("hl");});
        $('#tblassetwo tr').click(function(e){
            e.preventDefault();
            var woID = $(this).data('workorderId') || 0;
            if(woID)
            {
                window.location = "editworkorder.php?id=" + woID+'&mod=editasset';
            }
        });
        if ($('#tblassetwo tbody.wo-list').length > 0) {
            var oTable = $('#tblassetwo').dataTable({
                bJQueryUI: true,
                iDisplayLength: 25,
                sPaginationType: 'full_numbers'
            });
            oTable.fnSort( [[1,'desc'] ] );//sort by date created
        }
        $('#imglist a').fancybox({
            'titlePosition': 'inside',
            'overlayColor': '#977',
            'transitionIn': 'none',
            'transitionOut': 'none',
            'titleFormat': function(title, currentArray, currentIndex, currentOpts) {
                imghref = currentArray[currentIndex].href;
                imghrel = currentArray[currentIndex].name;
                imgpart = imghrel.split("/");
                imgfile = imgpart[imgpart.length - 2] + "/" + imgpart[imgpart.length - 1];
                return '<span id="doctitle">' + title + ' <nobr>[<a href="' + imghrel + '" target="_blank">Full Size</a>]</nobr> <nobr>[<a href="#" onclick="confdel(\'del_doc\',\'' + imgfile + '\')">Delete</a>]</nobr> <nobr>[<a href="#" onclick="switchcat(\'' + imgfile + '\')">Change Category</a>]</nobr></span>';
            }
        });

        $("html").ajaxError(function(xhr, s, e){
            var msg;
            switch (s.error_status) {
                case "parsererror":
                    msg = "A JSON parsing error occurred.";
                    break;
                case "timeout":
                    msg = "An ajax request timed out.";
                    break;
                default:
                    msg = "An ajax error occurred.";
                break;
            }
            alert(msg + "\n\n" + e.url);
        });

        $.getJSON("selectget.php",{id: 0, ie: 0, f: "init"}, function(j) {
            var options = '<option></option>';
            for (var i = 0; i < j.length; i++) {
                options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
            }
            $('#classid').html(options);
            <?php if(isset($results['classid'])): ?>
                $('#classid option[value=<?php echo $results['classid']; ?>]').attr('selected', 'selected');
            <?php endif;?>
            $.getJSON("selectget.php",{id: $('#classid').val(), ie: 0, f: "typeclass"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#typeid').html(options);
                $('#typeid option[value=<?php echo $results['typeid']; ?>]').attr('selected', 'selected');

                $.getJSON("selectget.php",{id: $('#typeid').val(), ie: 0, f: "manutype"}, function(j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $('#manuid').html(options);
                    $('#manuid option[value=<?php echo $results['manuid']; ?>]').attr('selected', 'selected');

                    $.getJSON("selectget.php",{id: $('#typeid').val(), ie: $('#manuid').val(), f: "modeltype"}, function(j) {
                        var options = '';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                        }
                        $('#modelid').html(options);
                        $('#modelid option[value=<?php echo $results['modelid']; ?>]').attr('selected', 'selected');
                    });
                });
            });
        });

        $('#classid').change(function() {
            $.getJSON("selectget.php",{id: $(this).val(), ie: 0, f: "typeclass"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#typeid').html(options);
                $('#typeid option:first').attr('selected', 'selected');

                $.getJSON("selectget.php",{id: $('#typeid').val(), ie: 0, f: "manutype"}, function(j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $('#manuid').html(options);
                    $('#manuid option:first').attr('selected', 'selected');

                    $.getJSON("selectget.php",{id: $('#typeid').val(), ie: $('#manuid').val(), f: "modeltype"}, function(j) {
                        var options = '';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                        }
                        $('#modelid').html(options);
                        $('#modelid option:first').attr('selected', 'selected');
                    });
                });
            });
        });

        $('#typeid').change(function() {
            $.getJSON("selectget.php",{id: $(this).val(), ie: 0, f: "manutype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#manuid').html(options);
                $('#manuid option:first').attr('selected', 'selected');

                $.getJSON("selectget.php",{id: $('#typeid').val(), ie: $('#manuid').val(), f: "modeltype"}, function(j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $('#modelid').html(options);
                    $('#modelid option:first').attr('selected', 'selected');
                });
            });
        });

        $('#manuid').change(function() {
            $.getJSON("selectget.php",{id: $('#typeid').val(), ie: $(this).val(), f: "modeltype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#modelid').html(options);
                $('#modelid option:first').attr('selected', 'selected');
            });
        });

        var SelectOption = function() {
            this.id = 0;
            this.ie = 0;
            this.f = 'init';
            this.selectId = 0;
            this.callback = null;
            this.targetId = null;
            this.addEmpty = false;
            this.url = 'selectget.php';
            this.defaultOpt = '<option></option>';

            var self = this;

            this.get = function() {

                if(!self.targetId || $('#' + self.targetId).length == 0) {
                    return;
                }
                var target = $('#'+self.targetId);
                var options = '';
                var selected = false;
                $.getJSON(self.url,{id: self.id, ie: self.ie, f: self.f}, function(j) {
                    if(j == 'session_expired') {
                        return window.top.location.href = window.top.location.href;
                    }
                    if (self.addEmpty && typeof(self.addEmpty) == 'boolean') {
                        options +=  self.defaultOpt;
                    }
                    for (var i = 0; i < j.length; i++) {
                        if(self.selectId==j[i].optionValue){
                            selected = true;
                            options += '<option value="' + j[i].optionValue + '" selected="selected">' + j[i].optionDisplay + '</option>';
                        }else{
                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                        }
                    }
                    target.html(options);
                    if(!selected) $('#'+this.targetId+' option:first').attr('selected', 'selected');
                    else $('#'+this.targetId+' option[value='+this.selectId+']').attr('selected', 'selected');
                    if ($.isFunction(self.callback)) {
                        self.callback(j);
                    }
                });
                return this;
            };
        };
        //console.log('depid=<?=$results['department_id'];?> , locationId=<?=$results['locationid'];?>')
        $('#siteid').change(function() {
            var deptOpt = new SelectOption();
            deptOpt.url = 'selectget.php';
            deptOpt.targetId = 'department_id';
            deptOpt.id = $(this).val();
            deptOpt.selectId='<?php echo $results['department_id'];?>';
            deptOpt.f = 'department';
            deptOpt.callback = function(){$('#department_id').change();}
            //console.log('fetch departments in site id='+deptOpt.id+' and pre-select departmentId='+deptOpt.selectId);
            deptOpt.get();
            return false;
        });
        $('#department_id').change(function() {
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = $(this).val();
            locationOpt.selectId='<?php echo $results['locationid'];?>';
            locationOpt.f = 'location';
            //console.log('fetch locations in dep id='+locationOpt.id+' and pre-select loc Id='+locationOpt.selectId);
            locationOpt.get();
            return false;
        });
        $('#siteid').change();


        $('#edit').click(function() {
            $('#frmtabdetails').find('input').removeAttr('disabled');
            $('#frmtabdetails').find('select').removeAttr('disabled');
            $('#frmtabdetails').find('textarea').removeAttr('disabled');
            $('#frmedit').css('display', 'none');
            $('#frmsubmit').css('display', 'inline');
            return false;
        });

        $('#cancel').click(function() {
            location.reload();
        });

        $('#frmtabdetails').submit(function(e) {
            if (e.originalEvent.explicitOriginalTarget.id == "edit" || e.originalEvent.explicitOriginalTarget.id == "cancel") {
                e.preventDefault();
                return false;
            }
        });

        if($('.alert-success').length > 0) {
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 1500);
        }
    });

    function wocompleted() {
        if ($("select[name='wostatus']").val() == "2") {
            return true;
        } else {
            return false;
        }
    }

    function confdel(func, oid) {
        if (confirm("Are you sure you want to delete this?\nPress OK to proceed or CANCEL to abort.")) {
            $('#frmhidden input[name=func]').val(func);
            $('#frmhidden input[name=id]').val(oid);
            $('#frmhidden').submit();
        }
        return false;
    }

    function switchcat(oid) {
        $('#doctitle').html('<form method="post" action="mod.php"><input type="hidden" name="func" value="edit_doc"><input type="hidden" name="id" value="' + oid + '"><select name="doccat"><?php for($a = 0; $a < count($doccat); $a++) echo "<option value=\"" . $a . "\">" . $doccat[$a] . "</option>"; ?></select><input type="submit" value="Change">');
    }
</script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title full-width">View Asset</h1>
            <ul>
                <li><a href="#tabdetails">Asset Details</a></li>
                <li><a href="#tabparts">Accessories/Parts</a></li>
                <?php /* <li><a href="#tabares">ARES</a></li>*/?>
                <li><a href="#tabdocs">Documents</a></li>
                <li><a href="#tabservice">Work List</a></li>
                <li><a href="#tabnewwo">New Work Order</a></li>
                <li><a href="assethistory.php?id=<?php echo $id; ?>">History</a></li>
            </ul>
            <div id="tabdetails">
                <h3>Asset Details</h3>
                <?php if(getSession('asset_updated', true)): ?>
                    <p class="alert alert-success">Asset was successfully updated.</p>
                <?php endif; ?>
                <?php if(getSession('asset_added', true)): ?>
                    <p class="alert alert-success">Asset was successfully added.</p>
                <?php endif; ?>
                <div class="clear">&nbsp;</div>
                <form id="frmtabdetails" method="post" action="mod.php" onsubmit="return verifyform()">
                    <input type="hidden" name="func" value="edit_asset" />
                    <input type="hidden" name="aid" value="<?php echo $results['id']; ?>" />
                    <table class="full-width no-border">
                        <?php //print_r($results); ?>
                        <tr>
                            <td>Record ID</td>
                            <td><?php echo $results['id']; ?></td>
                            <input type="hidden" name="classid" value="<?php echo $results['classid']; ?>">
                            <input type="hidden" name="typeid" value="<?php echo $results['typeid']; ?>">
                            <input type="hidden" name="manuid" value="<?php echo $results['manuid']; ?>">
                        </tr>
                        <tr>
                            <td>TEMS No</td>
                            <td>
                                <input type="text" name="assetno" size="30" maxlength="30" value="<?php echo $results['assetno']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Room Number</td>
                            <td>
                                <input type="text" name="roomno" size="30" maxlength="30" value="<?php echo $results['roomno']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Equipment under contract</td>
                            <td>
                                <label><input disabled="disabled" type="radio" name="under_contract" value="yes" <?php echo ($results['under_contract'] == 'yes') ? 'checked="checked"':''?>> YES</label>
                                <label><input disabled="disabled" type="radio" name="under_contract" value="no" <?php echo ($results['under_contract'] == 'no' || !$results['under_contract']) ? 'checked="checked"':''?>> NO</label>
                            </td>
                        </tr>
                        <tr>
                            <td>Model</td>
                            <td>
                                <?php
                                    sqltoarray("select `id`, `name` from `asset_model` order by `name`", $amodel);
                                ?>
                                <select name="modelid" id="modelidX" disabled="disabled">
                                    <?php optionize($amodel, $results['modelid']); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Serial No <span class="required">*</span></td>
                            <td>
                                <input required="required" type="text" name="serialno" size="30" maxlength="30" value="<?php $serialno = $results['serialno']; echo $serialno; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Reference No</td>
                            <td>
                                <input type="text" name="refno" size="30" maxlength="30" value="<?php echo $results['refno']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Order No</td>
                            <td>
                                <input type="text" name="orderno" size="30" maxlength="30" value="<?php echo $results['orderno']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <select name="statusid" disabled><?php optionize($assetstatus, $results['status']); ?></select>
                            </td>
                        </tr>
                        <!-- -->
                        <?php if (!($sid > 0 && $sid != 65535)): ?>
                        <tr>
                            <td> site <span class="required">*</span> </td>
                            <td>
                                <select required="required" name="siteid" id="siteid" disabled>
                                    <option></option>
                                    <?php optionize($site, $results['siteid']); ?>
                                </select>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Department <span class="required">*</span> </td>
                            <td><select name="department_id" id="department_id" disabled="disabled"></select></td>
                        </tr>
                        <tr>
                            <td> Location <span class="required">*</span> </td>
                            <td>
                                <?php if ($sid > 0 && $sid != 65535): ?>
                                    <input type="hidden" name="siteid" value="<?php echo $sid?>" />
                                    <select required="required" name="locationid" disabled="disabled">
                                        <?php optionize($locs, $results['locationid']);?>
                                    </select>
                                <?php else: ?>
                                    <select required="required" name="locationid" id="locationid" disabled="disabled"></select>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Purchase Date</td>
                            <td>
                                <input type="text" name="dtpurchase" id="dtpurchase" size="10" maxlength="15" value="<?php echo $results['purchasedate']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>
                                <select name="supplid" id="supplid" disabled>
                                    <?php optionize($supplier, $results['supplierid']); ?>
                                </select>
                                <?php if(isset($results['supplierid']) && $results['supplierid']): ?>
                                    <a href="editvendor.php?id=<?php echo $results['supplierid']; ?>">VIEW</a>
                                    <?php if ($supplieremail[$results['supplierid']] != ""): ?>
                                         | <a href="mailto:<?php echo $supplieremail[$results['supplierid']];?>">EMAIL</a>
                                    <?php endif; ?>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr>
                            <td>Price (RM)</td>
                            <td>
                                <input type="text" name="price" size="30" maxlength="20" value="<?php echo $results['price']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Warranty Start</td>
                            <td>
                                <input type="text" name="dtwrntstart" id="dtwrntstart" size="10" maxlength="15" value="<?php echo $results['warrantystart']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>Warranty End</td>
                            <td>
                                <input type="text" name="dtwrntend" id="dtwrntend" size="10" maxlength="15" value="<?php echo $results['warrantyend']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>PPM Start Date</td>
                            <td>
                                <input type="text" name="dtppmstart" id="dtppmstart" size="10" maxlength="15" value="<?php echo $results['ppmstart']; ?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>PPM Frequency</td>
                            <td>
                                <select name="ppmfreq" disabled>
                                    <?php optionize($appmfreq, $results['ppmfreq']); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks (if any)</td>
                            <td>
                                <textarea id="arem" name="arem" rows="2" cols="55" disabled><?php echo $results['remarks']; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>Creator</td>
                            <td><?php echo $user[$results['author']]; ?></td>
                        </tr>
                        <tr>
                            <td>Date Created</td>
                            <td><?php echo $results['created']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">&nbsp;
                                <?php if (isadmin()): ?>
                                    <span id="frmedit">
                                        <button id="edit" class="btn btn-primary">Edit</button>
                                    </span>
                                    <span id="frmsubmit">
                                        <button id="cancel" class="btn">Cancel</button>
                                        <input type="submit" value="Update" class="btn btn-primary">
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </form>

                <?php if (isadmin()): ?>
                    <p> <span class="required">*</span> Mandatory Field</p>
                    <h3>Delete Asset</h3>
                    <p>Please exercise discretion before you proceed to use this function. Data deletion is irreversible.</p>
                    <p>All work order records for this particular asset will be deleted as well.</p>
                    <form action="mod.php" method="post">
                        <input type="hidden" name="func" value="del_asset">
                        <input type="hidden" name="aid" value="<?php echo $results['id']; ?>">
                        <input type="submit" value="Delete Asset" class="btn btn-danger">
                    </form>
                <?php endif; ?>
            </div>
            <div id="tabparts">
                <h3>Accessories & Parts</h3>
                <table class="tlist2">
                    <tr>
                        <th>Accesories/Parts Name</th>
                        <th>Action</th>
                    </tr>
                    <?php
                        $stmt->free_result();
                        $stmt->close();
                        unset($bindvars);
                        unset($results);
                        $stmt = $mysqli->prepare("select * from asset_part where assetid = ?");
                        $stmt->bind_param('i', $id);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows() > 0) : ?>
                            <?php
                            $meta = $stmt->result_metadata();
                            while ($column = $meta->fetch_field()) {
                                $bindvars[] = &$results[$column->name];
                            }
                            call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                            while ($stmt->fetch()): ?>

                                <tr>
                                    <td><?php echo $results['name'];?></td>
                                    <?php if (($asid == $_SESSION['sid'] || $_SESSION['gid'] < 51) && !isguest()): ?>
                                        <td align="center">
                                            <img class="clicky" src="theme/default/x.gif" alt="Delete" title="Delete" onClick="confdel('del_part', '<?php echo $results['id'];?>');">
                                        </td>
                                    <?php else: ?>
                                        <td>&nbsp;</td>
                                    <?php endif; ?>
                                </tr>

                            <?php endwhile;?>

                        <?php else: ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                </table>

                <?php if (isadmin()): ?>
                    <h3>Add New Accessories/Parts</h3>
                    <form method="post" action="mod.php">
                        <input type="hidden" name="func" value="add_part" />
                        <input type="hidden" name="aid" value="<?php echo $id; ?>" />
                        <table class="full-width no-border">
                            <tr>
                                <td>Name <span class="required">*</span></td>
                                <td>
                                    <input required="required" type="text" name="pname" size="90" maxlength="100" value="" />
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
                        <input type="hidden" name="func">
                        <input type="hidden" name="id">
                    </form>
                <?php endif; ?>
            </div>

            <div id="tabdocs">
                <h3>Documents & Attachments</h3>
                <h4>Miscellaneous</h4>
                <?php
                    $extallowed =   array("jpg", "jpeg", "gif", "pdf");
                    $dir        =   "upload/asset/" . cleanfilename($serialno);
                    $afile      =   array();
                    if ($handle = @opendir($dir)) {
                        while (false !== ($file = readdir($handle))) {
                            if ($file != "." && $file != "..") {
                                $afile[] = $file;
                            }
                        }
                        closedir($handle);
                    }
                    $filecount = count($afile);

                    if ($filecount > 0) {
                        sort($afile);
                        $doctitle = "";
                        for($a = 0; $a < $filecount; $a++) {
                            $thisfileext = strtolower(end(explode(".", $afile[$a])));
                            if (in_array($thisfileext, $extallowed)) {
                                $piece = explode("-", $afile[$a]);
                                if ($piece[0] != $doctitle) {
                                    switch($piece[0]) {
                                        case "t1":
                                        case "t2":
                                        case "t3":
                                        case "t4":
                                            $filelist .= "<h4>" . $doccat[(int)substr($piece[0], 1, 1)] . "</h4>";
                                        default:

                                        break;
                                    }
                                    $doctitle = $piece[0];
                                }
                                //$imgtitle = "Uploaded on " . substr($piece[0], 0, 4) . "-" . substr($piece[0], 4, 2) . "-" . substr($piece[0], 6, 2) . " by " . $user[$piece[1]];
                                $imgtitle   =   "";
                                $imgfile    =   $dir . "/" . $afile[$a];
                                $thumb      =   ($thisfileext == "pdf")? "theme/default/icopdf.jpg" : $imgfile;
                                $filelist   .=  "<a rel=\"frl\" href=\"" . $thumb . "\" name=\"" . $imgfile . "\"title=\"" . $imgtitle . "\">".
                                                    "<img src=\"" . $thumb . "\" width=\"*\" height=\"100\">".
                                                "</a>";
                            }
                        }
                        if ($filelist != "") {
                            echo "<div id=\"imglist\">$filelist</div>";
                        } else {
                            echo "<i>No document found.</i>";
                        }
                    } else {
                        echo "<i>No document found.</i>";
                    }

                    if (isadmin()): ?>
                        <h3>Upload Document</h2>
                        <p>Only files with the extension .jpg, .gif and .pdf are allowed.</p>
                        <form id="fupload" method="post" action="mod.php" enctype="multipart/form-data">
                            <input type="hidden" name="func" value="add_doc" />
                            <input type="hidden" name="sno" value="<?php echo $serialno; ?>" />
                            <table class="full-width no-border">
                                <tr>
                                    <td>Category</td>
                                    <td>
                                        <select name="adoccat">
                                            <?php for($a = 0; $a < count($doccat); $a++): ?>
                                                <option value="<?php echo $a;?>"><?php echo $doccat[$a];?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>File <span class="required">*</span></td>
                                    <td>
                                        <input required="required" type="file" id="adoc" name="adoc" size="80" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <input type="submit" value="Upload" class="btn btn-primary">
                                    </td>
                                </tr>
                            </table>
                        </form>
                    <?php endif; ?>
            </div>

            <div id="tabservice">
                <h3>Work Order List</h3>
                <table id="tblassetwo" class="tlist">
                    <thead>
                        <tr>
                            <th>WO ID</th>
                            <th>Date Created</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Required</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <?php
                    $wostatus[1] = "Scheduled";
                    $wostatus[2] = "Completed";
                    $wostatus[3] = "Cancelled";

                    $stmt->free_result();
                    $stmt->close();
                    unset($bindvars);
                    unset($results);

                    $sql    =   'SELECT '.
                                    'workorder.id, '.
                                    'workorder.orderno, '.
                                    'workorder.created AS workorder_date,'.
                                    'asset.assetno AS tems_no,'.
                                    'asset.siteid AS asset_site_id,'.
                                    'category, '.
                                    'vendor.name, '.
                                    'workorder.status, '.
                                    'workorder.required, '.
                                    'workorder.completed '.
                                'FROM '.
                                    'workorder '.
                                'INNER JOIN '.
                                    'asset on workorder.assetid = asset.id '.
                                'LEFT JOIN '.
                                    'vendor ON workorder.vendorid = vendor.id '.
                                'WHERE '.
                                    'workorder.assetid = ? '.
                                'ORDER BY '.
                                    'workorder.modified DESC';

                    $stmt   =   $mysqli->prepare($sql);
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows() > 0):
                        $meta = $stmt->result_metadata();
                        while ($column = $meta->fetch_field()) {
                            $bindvars[] = &$results[$column->name];
                        }
                        call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                        ?>
                        <tbody class="wo-list">
                            <?php
                                while ($stmt->fetch()): ?>
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
                                    <tr data-workorder-id="<?php echo $results['id']?>">
                                        <td>
                                            <a href="editworkorder.php?a=1&id=<?php echo $results['id']?> "><?php echo $woid;?></a>
                                        </td>
                                        <td><?php echo date('Y/m/d', strtotime($results['workorder_date']));?></td>
                                        <td><?php echo workscoping($results['category']); ?></td>
                                        <td><?php echo $results['name'];?></td>
                                        <td><?php echo $wostatus[$results['status']]; ?></td>
                                        <td><?php echo $results['required']; ?></td>
                                        <td><?php echo $results['completed']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                        </tbody>
                    <?php else: ?>
                        <tbody>
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>

            <div id="tabnewwo">
                <h3>Create New Work Order</h3>
                <?php if (isworker()): ?>
                    <form id="frm_new_wo" method="post" action="mod.php">
                        <input type="hidden" name="func" value="add_workorder" />
                        <input type="hidden" name="assetid" value="<?php echo $id; ?>" />
                        <table class="full-width no-border">
                            <tr>
                                <td>Work Category <span class="required">*</span></td>
                                <td>
                                    <input type="checkbox" name="woprv" class="chk-wo-cat">Preventive
                                    <input type="checkbox" name="wocrt" class="chk-wo-cat">Corrective

                                </td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>
                                    <textarea name="wodesc" rows="2" cols="55"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    <select name="wostatus" id="wostatus">
                                        <option value="1">Scheduled</option>
                                        <option value="2">Completed</option>
                                        <option value="3">Cancelled</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Submit Data" class="btn btn-primary">
                                </td>
                            </tr>
                        </table>
                    </form>
                    <p> <span class="required">*</span> Mandatory Field</p>
                    <script type="text/javascript">
                    $(function() {
                        $('#frm_new_wo').submit(function() {
                            if($('.chk-wo-cat:checked').length == 0) {
                                alert('Please select workorder category.');
                                return false;
                            }
                            return true;
                        });
                    });
                    </script>
                <?php else:  ?>
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
