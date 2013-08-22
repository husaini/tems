<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

date_default_timezone_set("Asia/Kuala_Lumpur");

$sid            =   $_SESSION['sid'];
$rem            =   $_SESSION['rem'];
$assetstatus    =   array();
$supplier       =   array();
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

tabletoarray("asset_status", $assetstatus);
sqltoarray("select id, name from vendor where (type = 0 or type = 1) and status = 1 order by name", $supplier);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"

  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Asset</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.validationengine.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validationengine.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-en.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Asset</h1>
            <ul>
                <li> <a href="#tablist">List</a> </li>
                <li> <a href="#tabsearch">Search</a> </li>
                <li> <a href="#tabnew">New</a> </li>
            </ul>
            <div id="tablist">
                <table id="go" class="tlist3">
                    <tr>
                        <td align="right">
                            <form id="qact" method="get" class="c1" name="qact">
                                <input type="hidden" id="listid" name="listid">
                                <span><strong>Quick Actions </strong></span>
                                <select id="act" name="act">
                                    <option value="na">Selected: 0</option>
                                    <?php if (isworker()): ?>
                                    <option value="wo">Create Work Order</option>
                                    <?php endif; ?>
                                    <option value="pn">Print</option>
                                </select>
                                <input type="submit" value="Execute" class="btn btn-info">
                            </form>
                        </td>
                    </tr>
                </table>
                <table id="tblasset" class="tlist3">
                    <thead>
                        <tr>
                            <th>Check</th>
                            <th>ID</th>
                            <th>TEMS No</th>
                            <th>Equipment</th>
                            <th>Manufacturer</th>
                            <th>Model</th>
                            <th>Serial No</th>
                            <th>Site</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Last Serviced</th>
                            <th>Next Service</th>
                        </tr>
                    </thead>
                </table>

            </div>
            <div id="tabsearch">
                <form method="post" action="searchasset.php">
                    <table class="full-width no-border">
                        <tr>
                            <td> Asset No </td>
                            <td>
                                <input type="text" name="sassetno" size="30" maxlength="15" value="">
                            </td>
                        </tr>
                        <tr>
                            <td> Category </td>
                            <td>
                                <select id="sclassid" name="sclassid">
                                    <option value="0"> ANY </option>
                                </select>
                                <select id="stypeid" name="stypeid">
                                    <option value="0"> ANY </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td> Manufacturer/Model </td>
                            <td>
                                <select id="smanuid" name="smanuid">
                                    <option value="0"> ANY </option>
                                </select>
                                <select id="smodelid" name="smodelid">
                                    <option value="0"> ANY </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td> Serial No </td>
                            <td>
                                <input type="text" name="sserialno" size="30" maxlength="30" value="">
                            </td>
                        </tr>
                        <tr>
                            <td> Reference No </td>
                            <td>
                                <input type="text" name="srefno" size="30" maxlength="30" value="">
                            </td>
                        </tr>
                        <tr>
                            <td> Report No </td>
                            <td>
                                <input type="text" name="sorderno" size="30" maxlength="30" value="">
                            </td>
                        </tr>
                        <tr>
                            <td> Status </td>
                            <td>
                                <select name="sstatusid">
                                    <option value="0"> ANY </option>
                                    <?php optionize($assetstatus); ?>
                                </select>
                            </td>
                        </tr>
                        <?php if (!($sid > 0 && $sid != 65535)){ ?>
                        <tr>
                            <td> Site </td>
                            <td>

                                <?php if ($sid == 65535): ?>

                                    <select name="ssiteid" id="ssiteid">
                                        <option value="0">ANY</option>
                                        <?php
                                            sqltoarray("select id, name from site where id in (" . $rem . ") order by name", $sites);
                                            if ($usite_ids) {
                                                foreach ($sites as $site_id => $site_name) {
                                                    if(!in_array($site_id, $usite_ids)) {
                                                        unset($sites[$site_id]);
                                                    }
                                                }
                                            }
                                            optionize($sites);
                                        ?>
                                    </select>

                                <?php else: ?>

                                    <select name="ssiteid" id="ssiteid">
                                        <option value="0">ANY</option>
                                        <?php
                                            tabletoarray("site", $sites);
                                            if ($usite_ids) {
                                                foreach ($sites as $site_id => $site_name) {
                                                    if(!in_array($site_id, $usite_ids)) {
                                                        unset($sites[$site_id]);
                                                    }
                                                }
                                            }
                                            optionize($sites);
                                        ?>
                                    </select>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php }?>
                        <tr>
                            <td>Department</td>
                            <td><select name="sdepartment_id" id="sdepartment_id"><option value="0"> ANY </option></select></td>
                        </tr>
                        <tr>
                            <td> Location </td>
                            <td>
                                <?php if ($sid > 0 && $sid != 65535): ?>
                                    <select name="slocationid" id="slocationid">
                                        <?php
                                            sqltoarray("select id, name from site_location where siteid = " . $sid . " order by name", $locs);
                                            if ($uloc_ids) {
                                                foreach ($locs as $loc_id => $loc_name) {
                                                    if(!in_array($loc_id, $uloc_ids)) {
                                                        unset($locs[$loc_id]);
                                                    }
                                                }
                                            }
                                            optionize($locs);
                                        ?>
                                    </select>

                                <?php elseif ($sid == 65535): ?>

                                    <select name="slocationid" id="slocationid">
                                        <option value="0">ANY</option>
                                    </select>

                                <?php else: ?>
                                    <select name="slocationid" id="slocationid">
                                        <option value="0">ANY</option>
                                    </select>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td> Purchase Date Range </td>
                            <td>
                                <input type="text" name="dtpurchasemin" id="dtpurchasemin" size="10" maxlength="15" value="" class="dp">
                                to
                                <input type="text" name="dtpurchasemax" id="dtpurchasemax" size="10" maxlength="15" value="" class="dp">
                            </td>
                        </tr>
                        <tr>
                            <td> Supplier </td>
                            <td>
                                <select name="ssupplid">
                                    <option value="0"> Any </option>
                                    <?php optionize($supplier); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td> Price Range (RM) </td>
                            <td>
                                <input type="text" name="spricemin" size="30" maxlength="20" value="">
                                to
                                <input type="text" name="spricemax" size="30" maxlength="20" value="">
                            </td>
                        </tr>
                        <tr>
                            <td> Warranty Start Range </td>
                            <td>
                                <input type="text" name="dtwrntstartmin" id="dtwrntstartmin" size="10" maxlength="15" value="" class="dp">
                                to
                                <input type="text" name="dtwrntstartmax" id="dtwrntstartmax" size="10" maxlength="15" value="" class="dp">
                            </td>
                        </tr>
                        <tr>
                            <td> Warranty End Range </td>
                            <td>
                                <input type="text" name="dtwrntendmin" id="dtwrntendmin" size="10" maxlength="15" value="" class="dp">
                                to
                                <input type="text" name="dtwrntendmax" id="dtwrntendmax" size="10" maxlength="15" value="" class="dp">
                            </td>
                        </tr>
                        <tr>
                            <td> Last Service Range </td>
                            <td>
                                <input type="text" name="lastsvcmin" id="lastsvcmin" size="10" maxlength="15" value="" class="dp">
                                to
                                <input type="text" name="lastsvcmax" id="lastsvcmax" size="10" maxlength="15" value="" class="dp">
                            </td>
                        </tr>
                        <tr>
                            <td> Next Service Range </td>
                            <td>
                                <input type="text" name="nextsvcmin" id="nextsvcmin" size="10" maxlength="15" value="" class="dp">
                                to
                                <input type="text" name="nextsvcmax" id="nextsvcmax" size="10" maxlength="15" value="" class="dp">
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2"> Miscellaneous </td>
                            <td>
                                <input type="checkbox" name="wopending">
                                Pending Work Order
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Search" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div id="tabnew">
                <?php if (isadmin()): ?>

                    <p>
                        <a href="csv.php" class="btn btn-primary">or Upload CSV/Excel</a>
                    </p>
                    <form id="new_asset" name="new_asset" method="post" action="mod.php">
                        <input type="hidden" name="func" value="add_asset">
                        <table  class="full-width no-border">
                            <tr>
                                <td> Asset No </td>
                                <td>
                                    <input type="text" name="assetno" size="30" maxlength="15" value="">
                                </td>
                            </tr>
                            <tr>
                                <td> Category <span class="required">*</span> </td>
                                <td>
                                    <select required="required" name="classid" id="classid" class="validate[required]">
                                    </select>
                                    <select required="required" name="typeid" id="typeid" class="validate[required]">
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td> Manufacturer/Model <span class="required">*</span> </td>
                                <td>
                                    <select required="required" name="manuid" id="manuid" class="validate[required]">
                                    </select>
                                    <select required="required" name="modelid" id="modelid" class="validate[required]">
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td> Serial No <span class="required">*</span> </td>
                                <td>
                                    <input required="required" type="text" id="serialno" name="serialno" size="30" maxlength="30" value="" class="validate[required]">
                                </td>
                            </tr>
                            <tr>
                                <td> Reference No </td>
                                <td>
                                    <input type="text" name="refno" size="30" maxlength="30" value="">
                                </td>
                            </tr>
                            <tr>
                                <td> Report No </td>
                                <td>
                                    <input type="text" name="orderno" size="30" maxlength="30" value="">
                                </td>
                            </tr>
                            <tr>
                                <td> Status </td>
                                <td>
                                    <select name="statusid">
                                        <?php optionize($assetstatus); ?>
                                    </select>
                                </td>
                            </tr>
                            <?php if (!($sid > 0 && $sid != 65535)): ?>
                            <tr>
                                <td> site <span class="required">*</span> </td>
                                <td>
                                    <select required="required" name="siteid" id="siteid">
                                        <option></option>
                                        <?php optionize($sites);?>
                                    </select>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>Department <span class="required">*</span> </td>
                                <td><select name="department_id" id="department_id"></select></td>
                            </tr>
                            <tr>
                                <td> Location <span class="required">*</span> </td>
                                <td>
                                    <?php if ($sid > 0 && $sid != 65535): ?>
                                        <input type="hidden" name="siteid" value="<?php echo $sid?>" />
                                        <select required="required" name="locationid">
                                            <?php optionize($locs);?>
                                        </select>
                                    <?php else: ?>
                                        <select required="required" name="locationid" id="locationid" class="validate[required]"></select>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td> Purchase Date </td>
                                <td>
                                    <input type="text" name="dtpurchase" id="dtpurchase" size="10" maxlength="15" value="" class="validate[optional,custom[date]] dp">
                                </td>
                            </tr>
                            <tr>
                                <td> Supplier </td>
                                <td>
                                    <select name="supplid" id="supplid">
                                        <?php optionize($supplier); ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td> Price (RM) </td>
                                <td>
                                    <input type="text" name="price" size="30" maxlength="20" value="">
                                </td>
                            </tr>
                            <tr>
                                <td> Warranty Start </td>
                                <td>
                                    <input type="text" name="dtwrntstart" id="dtwrntstart" size="10" maxlength="15" value="" class="validate[optional,custom[date]] dp">
                                </td>
                            </tr>
                            <tr>
                                <td> Warranty End </td>
                                <td>
                                    <input type="text" name="dtwrntend" id="dtwrntend" size="10" maxlength="15" value="" class="validate[optional,custom[date]] dp">
                                </td>
                            </tr>
                            <tr>
                                <td> PPM Start Date </td>
                                <td>
                                    <input type="text" name="dtppmstart" id="dtppmstart" size="10" maxlength="15" value="" class="validate[optional,custom[date]] dp">
                                </td>
                            </tr>
                            <tr>
                                <td> PPM Frequency </td>
                                <td>
                                    <select name="ppmfreq">
                                        <option value="0"> None </option>
                                        <option value="1"> Once A Year </option>
                                        <option value="2"> Twice A Year </option>
                                        <option value="3"> 3 Times A Year </option>
                                        <option value="4"> Once Every Quarter </option>
                                        <option value="6"> Once Every 2 Months </option>
                                        <option value="12"> Once A Month </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td> Remarks (if any) </td>
                                <td>
                                    <textarea id="arem" name="arem" rows="2" cols="55"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Submit Data" class="btn btn-primary">
                                </td>
                            </tr>
                        </table>
                    </form>
                    <p>  <span class="required">*</span> Mandatory Field </p>

                <?php else: ?>
                    <p class="c2"> You are not authorized to use this function. </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
        if(!function_exists('google_analytics'))
        {
            require_once(dirname(__FILE__).'/sharedfunc.php');
        }
        google_analytics('uitm');
    ?>
    <script type="text/javascript">
    var oTable;
    var rowSelected = [];
    var tick = '<center><div class="checked">&nbsp;</div></center>';
    var untick = '<center><div class="checkbox">&nbsp;</div></center>';
    var xall = 0;

    $(document).ready(function() {
        var customSearchCol = {
            name: 'search_temsno',
            value: null
        };
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
        var now = new Date();
        thisYear = now.getFullYear();
        $('.dp').attr('readonly','readonly').datepicker({
            changeMonth:true,
            changeYear:true,
            dateFormat:'yy-mm-dd',
            minDate: "-10Y",
            maxDate: "+10Y"
        });
        $('#new_asset').validationEngine();

        oTable = $('#tblasset').dataTable({
            bProcessing: true,
            bServerSide: true,
            bStateSave: false,
            sAjaxSource: 'ajaxasset.php',
            bJQueryUI: true,
            iDisplayLength: 25,
            sPaginationType: 'full_numbers',
            fnRowCallback: function(nRow, aData, iDisplayIndex) {
                $(nRow).attr('id', 'row_' + aData[1]);

                if (jQuery.inArray(parseInt($(aData[1]).text()), rowSelected) != -1) {
                    $(nRow).children('td').first().html(tick);
                } else {
                    $(nRow).children('td').first().html(untick);
                }
                $(nRow).children('td').first().click(clicktotick);
                $(nRow).children('td:not(:first-child)').click(clicktoedit);
                return nRow;
            },
            fnServerData: function ( sSource, aoData, fnCallback) {
                /* Add some extra data to the sender */
                if(typeof(customSearchCol) == 'object' && customSearchCol) {
                    aoData.push(customSearchCol);
                }
                $.getJSON(sSource, aoData, function(json) {
                    if(json == 'session_expired') {
                        window.top.location.href = window.top.location.href;
                    }
                    /* Do whatever additional processing you want on the callback, then tell DataTables */
                    fnCallback(json);
                });
            },
            fnInitComplete: function(oSettings, json) {
                $('#tblasset_filter').append('&nbsp;&nbsp; Search TEMS No. <input type="text" name="search_tems_no" id="search_tems" placeholder="Search TEMS No." />');

            },
            aoColumns: [{ "bSortable": false },{'bVisible': false, 'bSortable': false},null,null,null,null,null,null,null,null,null,null]
        });

        $("#search_tems").live('keyup', function (e) {
            customSearchCol.value = this.value;
            oTable.fnFilter(this.value, 1);
        });

        $("#search_tems").keypress( function(e) {
            /* Prevent default */
            if ( e.keyCode == 13 ) {
                return false;
            }
        });

        $('#tblasset thead th:first').click(function() {
            var iId=0;
            if (xall) {
                xall = 0;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    //console.log($(oTable.fnGetNodes(a)), 'xall=true');
                    //iId = parseInt($(oTable.fnGetNodes(a)).find('td:eq(1)').text());
                    iId = $(oTable.fnGetNodes(a)).attr('id').split('_').pop();
                    if (jQuery.inArray(iId, rowSelected) > -1) {
                        rowSelected = jQuery.grep(rowSelected, function(value) {
                            return value != iId;
                        });
                    }
                    $('#tblasset tbody tr td:first-child').html(untick);
                }
            } else {
                xall = 1;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    //console.log($(oTable.fnGetNodes(a)), 'xall=false');
                    //iId = parseInt($(oTable.fnGetNodes(a)).find('td:eq(1)').text());
                    iId = $(oTable.fnGetNodes(a)).attr('id').split('_').pop();
                    if (jQuery.inArray(iId, rowSelected) == -1) {
                        rowSelected[rowSelected.length++] = iId;
                    }
                    $('#tblasset tbody tr td:first-child').html(tick);
                }
            }

            $('#go').find('option').first().text('Selected: ' + rowSelected.length);
        });

        $('#tblasset tbody tr').live('mouseover mouseout', function(e) {
            if (e.type == 'mouseover') {
                $(this).addClass("hl");
            } else {
                $(this).removeClass("hl");
            }
        });

        function clicktoedit() {
            //var id = $(this).parent().children("td").first().text();
            //var id = $(this).parent().find('td:eq(1)').text();
            var id = $(this).parent().attr('id').split('_').pop();
            var aid = parseInt(id,10);
            if (!isNaN(aid)) {
                window.location = "editasset.php?id=" + aid;
            }
        }

        function clicktotick() {
            //var iId = parseInt($(this).parent().children("td").first().text());
            //var iId = parseInt($(this).next('td').text(),10);
            var iId = $(this).parent().attr('id').split('_').pop();
            if (jQuery.inArray(iId, rowSelected) == -1) {
                rowSelected[rowSelected.length++] = iId;
            } else {
                rowSelected = jQuery.grep(rowSelected, function(value) { return value != iId; });
            }

            if ($(this).html() !== untick) {
                $(this).html(untick);
            } else {
                $(this).html(tick);
            }
            $('#go').find('option').first().text('Selected: ' + rowSelected.length);
        }

        $("html").ajaxError(function(xhr, s, e) {
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

        var SelectOption = function() {
            this.id = 0;
            this.ie = 0;
            this.f = 'init'
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

                $.getJSON(self.url,{id: self.id, ie: self.ie, f: self.f}, function(j) {
                    if(j == 'session_expired') {
                        return window.top.location.href = window.top.location.href;
                    }
                    if (self.addEmpty && typeof(self.addEmpty) == 'boolean') {
                        options +=  self.defaultOpt;
                    }
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    target.html(options);
                    $('#'+this.targetId+' option:first').attr('selected', 'selected');
                    if ($.isFunction(self.callback)) {
                        self.callback(j);
                    }
                });
                return this;
            };
        };

        var classOpt = new SelectOption();
        classOpt.targetId = 'classid';
        classOpt.addEmpty = true;
        classOpt.get();

        var searchOpt = new SelectOption();
        searchOpt.url = 'searchget.php';
        searchOpt.targetId = 'sclassid';
        searchOpt.get();

        $('#classid').change(function() {
            var typeOpt = new SelectOption();
            typeOpt.targetId = 'typeid';
            typeOpt.f = 'typeclass';
            typeOpt.id = $(this).val();

            typeOpt.callback = function(data) {
                if(!data || data.length == 0) {
                    $('#manuid').empty();
                    $('#modelid').empty();
                    return;
                }
                var manuOpt = new SelectOption();
                manuOpt.id = data[0].optionValue;
                manuOpt.f = 'manutype';
                manuOpt.targetId = 'manuid';
                manuOpt.callback = function(d) {
                    if(!d || d.length == 0) {
                        $('#modelid').empty();
                        return;
                    }
                    var modelidOpt = new SelectOption();
                    modelidOpt.id = $('#typeid').val();
                    modelidOpt.ie = d[0].optionValue;
                    modelidOpt.f = 'modeltype';
                    modelidOpt.targetId = 'modelid';
                    modelidOpt.get();
                };
                manuOpt.get();
            };
            typeOpt.get();
            return false;
        });

        $('#typeid').change(function() {
            var typeId = $(this).val();
            var manuOpt = new SelectOption();
            manuOpt.id = typeId;
            manuOpt.f = 'manutype';
            manuOpt.targetId = 'manuid';
            manuOpt.get();
            manuOpt.callback = function(d) {
                if(!d || d.length == 0) {
                    $('#modelid').empty();
                    return;
                }
                var modelidOpt = new SelectOption();
                modelidOpt.id = typeId;
                modelidOpt.ie = d[0].optionValue;
                modelidOpt.f = 'modeltype';
                modelidOpt.targetId = 'modelid';
                modelidOpt.get();

            };
            return false;
        });

        $('#manuid').change(function() {
            var modelidOpt = new SelectOption();
            modelidOpt.id = $('#typeid').val();
            modelidOpt.ie = $(this).val();
            modelidOpt.f = 'modeltype';
            modelidOpt.targetId = 'modelid';
            modelidOpt.get();
            return false;
        });

        $('#siteid').change(function() {
            //site is not linked to location and is only lonked to department
            /*var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = $(this).val();
            locationOpt.f = 'location';
            locationOpt.get();*/

            var deptOpt = new SelectOption();
            deptOpt.url = 'selectget.php';
            deptOpt.targetId = 'department_id';
            deptOpt.id = $(this).val();
            deptOpt.f = 'department';
            deptOpt.callback = function(){$('#department_id').change();}
            deptOpt.get();
            return false;
        });

        $('#department_id').change(function() {
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = $(this).val();
            locationOpt.f = 'location';
            locationOpt.get();
            return false;
        });

        $('#ssiteid').change(function() {
            //site is not linked to location and is only lonked to department
            /*var slocationOpt = new SelectOption();
            slocationOpt.url = 'selectget.php';
            slocationOpt.targetId = 'slocationid';
            slocationOpt.id = $(this).val();
            slocationOpt.f = 'location';
            slocationOpt.defaultOpt = '<option value="0">ANY</option>';
            slocationOpt.addEmpty = true;
            slocationOpt.get();*/

            var sDeptOpt = new SelectOption();
            sDeptOpt.url = 'selectget.php';
            sDeptOpt.targetId = 'sdepartment_id';
            sDeptOpt.id = $(this).val();
            sDeptOpt.f = 'department';
            sDeptOpt.defaultOpt = '<option value="0">ANY</option>';
            sDeptOpt.addEmpty = true;
            sDeptOpt.callback = function(){$('#sdepartment_id').change();}
            sDeptOpt.get();
            return false;
        });

        $('#sdepartment_id').change(function() {
            var slocationOpt = new SelectOption();
            slocationOpt.url = 'selectget.php';
            slocationOpt.targetId = 'slocationid';
            slocationOpt.id = $(this).val();
            slocationOpt.f = 'location';
            slocationOpt.defaultOpt = '<option value="0">ANY</option>';
            slocationOpt.addEmpty = true;
            slocationOpt.get();
            return false;
        });

        $('#sclassid').change(function() {
            var defaultOpt = '<option value="0">ANY</option>';
            var sTypeOpt = new SelectOption();
            sTypeOpt.url = 'searchget.php';
            sTypeOpt.targetId = 'stypeid';
            sTypeOpt.id = $(this).val();
            sTypeOpt.f = 'typeclass';

            sTypeOpt.callback = function(data) {
                if(!data || data.length == 0) {
                    $('#stypeid').html(defaultOpt);
                    $('#smodelid').html(defaultOpt);
                    return;
                }
                var sTypeId = data[0].optionValue;
                var sManuOpt = new SelectOption();
                sManuOpt.url = 'searchget.php';
                sManuOpt.targetId = 'smanuid';
                sManuOpt.id = sTypeId;
                sManuOpt.f = 'manutype';
                sManuOpt.callback = function(d) {
                    if(!d || d.length == 0) {
                        $('#smodelid').html(defaultOpt);
                        return;
                    }
                    var sModelOpt = new SelectOption();
                    sModelOpt.url = 'searchget.php';
                    sModelOpt.targetId = 'smodelid';
                    sModelOpt.f = 'modeltype';
                    sModelOpt.id = sTypeId;
                    sModelOpt.ie = d[0].optionValue;
                    sModelOpt.get();
                }
                sManuOpt.get();
            };
            sTypeOpt.get();

            return false;
        });

        $('#stypeid').change(function() {
            var sTypeId = $(this).val();
            var sManuOpt = new SelectOption();
            sManuOpt.url = 'searchget.php';
            sManuOpt.targetId = 'smanuid';
            sManuOpt.id = sTypeId;
            sManuOpt.f = 'manutype';
            sManuOpt.callback = function(d) {
                if(!d || d.length == 0) {
                    $('#smodelid').html(defaultOpt);
                    return;
                }
                var sModelOpt = new SelectOption();
                sModelOpt.url = 'searchget.php';
                sModelOpt.targetId = 'smodelid';
                sModelOpt.f = 'modeltype';
                sModelOpt.id = sTypeId;
                sModelOpt.ie = d[0].optionValue;
                sModelOpt.get();
            }
            sManuOpt.get();

            return false;
        });

        $('#smanuid').change(function() {
            var sModelOpt = new SelectOption();
            sModelOpt.url = 'searchget.php';
            sModelOpt.targetId = 'smodelid';
            sModelOpt.f = 'modeltype';
            sModelOpt.id = $('#stypeid').val();
            sModelOpt.ie = $(this).val();
            sModelOpt.get();
            return false;
        });


        $('#qact').submit(function() {
            if (rowSelected.length == 0 && ($('#act').val() == 'wo' || $('#act').val() == 'pn')) {
                alert('No asset selected');
                return false;
            } else if ($('#act').val() == 'na') {
                alert('Please select an action');
                return false;
            } else {
                $('#listid').val(rowSelected);
                if ($('#act').val() == 'wo') {
                    $('#qact').attr('action', 'workorder.php#tabnew');
                }
                if ($('#act').val() == 'pn') {
                    $('#qact').attr('action', 'printoption.php');
                }
            }
        });


        if($('.alert-success').length > 0) {
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 1500);
        }
    });

    </script>
    </body>
</html>
<?php $mysqli->close();?>
