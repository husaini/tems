<?php
require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

$sid = $_SESSION['sid'];
$maxrecord = 500;

function ms_escape_string($data) {
    if (is_numeric($data)) return $data;
    if (!isset($data) or empty($data)) return '';

    $non_displayables = array(
        '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/',             // url encoded 16-31
        '/[\x00-\x08]/',            // 00-08
        '/\x0b/',                   // 11
        '/\x0c/',                   // 12
        '/[\x0e-\x1f]/'             // 14-31
    );
    foreach ($non_displayables as $regex) {
        $data = preg_replace($regex, '', $data);
    }
    $data = str_replace("'", "''", $data);
    return $data;
}
tabletoarray("asset_status", $assetstatus);
tabletoarray("site", $site);
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
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-1.6.4.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-en-1.6.4.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Asset</h1>
            <ul>
                <li><a href="#tablist">Result</a></li>
                <li><a href="#tabsearch">Search</a></li>
            </ul>
            <div id="tablist">
                <h3>Search Result</h3>
                <?php if ($_POST): ?>
                <?php
                    $sWhere = $sid ? "WHERE asset.siteid = $sid" : '';

                    if ($_POST['sassetno'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "assetno LIKE '%" . ms_escape_string($_POST['sassetno']) . "%'";
                    }

                    if (is_numeric($_POST['sclassid']) && $_POST['sclassid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.classid = " . $_POST['sclassid'];
                    }

                    if (is_numeric($_POST['stypeid']) && $_POST['stypeid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.typeid = " . $_POST['stypeid'];
                    }

                    if (is_numeric($_POST['smanuid']) && $_POST['smanuid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.manuid = " . $_POST['smanuid'];
                    }

                    if (is_numeric($_POST['smodelid']) && $_POST['smodelid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.modelid = " . $_POST['smodelid'];
                    }

                    if ($_POST['sserialno'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "serialno LIKE '%" . ms_escape_string($_POST['sserialno']) . "%'";
                    }

                    if ($_POST['srefno'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "refno LIKE '%" . ms_escape_string($_POST['srefno']) . "%'";
                    }

                    if ($_POST['sorderno'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "orderno LIKE '%" . ms_escape_string($_POST['sorderno']) . "%'";
                    }

                    if ($_POST['sserialno'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "serialno LIKE '%" . ms_escape_string($_POST['sserialno']) . "%'";
                    }

                    if (!empty($_POST['ssiteid']) && $_POST['ssiteid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.siteid = " . $_POST['ssiteid'];
                    }

                    if (is_numeric($_POST['slocationid']) && $_POST['slocationid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.locationid = " . $_POST['slocationid'];
                    }

                    if (is_numeric($_POST['sdepartment_id']) && $_POST['sdepartment_id'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.department_id = " . $_POST['sdepartment_id'];
                    }

                    if (is_numeric($_POST['sstatusid']) && $_POST['sstatusid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.status = " . $_POST['sstatusid'];
                    }

                    if (is_numeric($_POST['ssupplid']) && $_POST['ssupplid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.supplierid = " . $_POST['ssupplid'];
                    }

                    if (is_numeric($_POST['ssupplid']) && $_POST['ssupplid'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.supplierid = " . $_POST['ssupplid'];
                    }

                    if (is_numeric($_POST['spricemin']) && $_POST['spricemin'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "price >= " . ms_escape_string($_POST['spricemin']);
                    }

                    if (is_numeric($_POST['spricemax']) && $_POST['spricemax'] != 0) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "price <= " . ms_escape_string($_POST['spricemax']);
                    }

                    if ($_POST['dtpurchasemin'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "purchasedate >= '" . ms_escape_string($_POST['dtpurchasemin']) . "'";
                    }

                    if ($_POST['dtpurchasemax'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "purchasedate <= '" . ms_escape_string($_POST['dtpurchasemax']) . "'";
                    }

                    if ($_POST['dtwrntstartmin'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "warrantystart >= '" . ms_escape_string($_POST['dtwrntstartmin']) . "'";
                    }

                    if ($_POST['dtwrntstartmax'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "warrantystart <= '" . ms_escape_string($_POST['dtwrntstartmax']) . "'";
                    }

                    if ($_POST['dtwrntendmin'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "warrantyend >= '" . ms_escape_string($_POST['dtwrntendmin']) . "'";
                    }

                    if ($_POST['dtwrntendmax'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "warrantyend <= '" . ms_escape_string($_POST['dtwrntendmax']) . "'";
                    }

                    if ($_POST['lastsvcmin'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "lastsvc >= '" . ms_escape_string($_POST['lastsvcmin']) . "'";
                    }

                    if ($_POST['lastsvcmax'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "lastsvc <= '" . ms_escape_string($_POST['lastsvcmax']) . "'";
                    }

                    if ($_POST['nextsvcmin'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "IF(IFNULL(asset.ppmstart, '') > IFNULL(asset.lastsvc, ''), asset.ppmstart, asset.lastsvc) + INTERVAL (12/asset.ppmfreq) MONTH >= '" . ms_escape_string($_POST['dtwrntendmin']) . "'";
                    }

                    if ($_POST['nextsvcmax'] != "") {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "IF(IFNULL(asset.ppmstart, '') > IFNULL(asset.lastsvc, ''), asset.ppmstart, asset.lastsvc) + INTERVAL (12/asset.ppmfreq) MONTH <= '" . ms_escape_string($_POST['dtwrntendmax']) . "'";
                    }

                    if (isset($_POST['wopending'])) {
                        $sWhere .= ($sWhere == "")? "WHERE " : " AND ";
                        $sWhere .= "asset.id IN (SELECT DISTINCT assetid FROM workorder WHERE status = 1)";
                    }

                    $sQuery =   'SELECT '.
                                    'asset.id, '.
                                    'assetno, '.
                                    'asset_class.name classname, '.
                                    'asset_type.name typename, '.
                                    'asset_manufacturer.name manuname, '.
                                    'asset_model.name modelname, '.
                                    'serialno, '.
                                    'site.name sitename, '.
                                    'department_location.name locname, '.
                                    'site_department.name department, '.
                                    'asset.lastsvc,'.
                                    "IF(IFNULL(asset.ppmstart, '') > IFNULL(asset.lastsvc, ''), asset.ppmstart, asset.lastsvc) + INTERVAL (12/asset.ppmfreq) MONTH nextsvc ".
                                    //'(SELECT MAX(required) FROM workorder WHERE workorder.assetid = asset.id and workorder.status = 1) latestwo '.
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
                                'LEFT JOIN '.
                                    'site ON asset.siteid = site.id '.
                                'LEFT JOIN '.
                                    'department_location ON asset.locationid = department_location.id '.
                                    'AND department_location.depid = asset.department_id '.
                                'LEFT JOIN '.
                                    'site_department ON asset.department_id = site_department.id '.
                                $sWhere;

                    $stmt = $mysqli->prepare($sQuery) or die(mysqli_error($mysqli));
                    $stmt->execute();
                    $stmt->store_result();



                    if ($stmt->num_rows() > 0 && $stmt->num_rows() <= $maxrecord): ?>
                        <table id="go" class="tlist3">
                            <tr>
                                <td align="right">
                                    <!-- <b>Quick Action</b> -->
                                    <form id="qact" method="get" style="display:inline">
                                        <input type="hidden" id="listid" name="listid">
                                        <select id="act" name="act">
                                            <option value="na">Selected: 0</option>
                                            <?php if (isworker()): ?>
                                                <option value="wo">Create Work Order</option>
                                            <?php endif; ?>
                                            <option value="pn">Print</option>
                                        </select>
                                        <input type="submit" value="Go" />
                                    </form>
                                </td>
                            </tr>
                        </table>

                        <table id="tblasset" class="tlist3">
                            <thead>
                                <tr>
                                    <th>Select All</th>
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
                            <tbody>
                                <?php
                                    $meta = $stmt->result_metadata();

                                    while ($column = $meta->fetch_field()) {
                                        $bindvars[] = &$results[$column->name];
                                    }

                                    call_user_func_array(array($stmt, 'bind_result'), $bindvars);

                                    while ($stmt->fetch()): ?>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['assetno'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['typename'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['manuname'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['modelname'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['serialno'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['sitename'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['department'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['locname'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['lastsvc'];?></a>
                                            </td>
                                            <td>
                                                <a href="editasset.php?id=<?php echo $results['id'];?>"><?php echo $results['nextsvc'];?></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                            </tbody>
                        </table>

                    <?php elseif ($stmt->num_rows() > $maxrecord): ?>
                        <p><i>Result set is too big (&gt; <?php echo $maxrecord; ?> records). Please refine your search and try again.</i></p>
                    <?php else: ?>
                        <p><i>Your search returns no result. Please refine your search and try again.</i></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Nothing to search</p>
                <?php endif; ?>
            </div>

            <div id="tabsearch">
                <h3>Search Asset</h3>
                <form method="post" action="searchasset.php">
                    <table class="full-width no-border">
                        <tr>
                            <td>Asset No</td>
                            <td>
                                <input type="text" name="sassetno" size="30" maxlength="15" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Category</td>
                            <td>
                                <select id="sclassid" name="sclassid">
                                    <option value="0">ANY</option>
                                </select>
                                <select id="stypeid" name="stypeid">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Manufacturer/Model</td>
                            <td>
                                <select id="smanuid" name="smanuid">
                                    <option value="0">ANY</option>
                                </select>
                                <select id="smodelid" name="smodelid">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Serial No</td>
                            <td>
                                <input type="text" name="sserialno" size="30" maxlength="30" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Reference No</td>
                            <td>
                                <input type="text" name="srefno" size="30" maxlength="30" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Order No</td>
                            <td>
                                <input type="text" name="sorderno" size="30" maxlength="30" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <select name="sstatusid">
                                    <option value="0">ANY</option>
                                    <?php optionize($assetstatus); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Location</td>
                            <td>
                                <?php if ($sid): ?>
                                    <select name="slocationid" id="slocationid">
                                        <?php
                                            sqltoarray("select id, name from site_location where siteid = " . $sid . " order by name", $loc);
                                            optionize($loc);
                                        ?>
                                    </select>
                                <?php else: ?>
                                    <select name="ssiteid" id="ssiteid">
                                        <option value="0">ANY</option>
                                        <?php optionize($site);?>
                                    </select>
                                    <select name="slocationid" id="slocationid">
                                        <option value="0">ANY</option>
                                    </select>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>
                                <select name="sdepartment_id" id="sdepartment_id">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Purchase Date Range</td>
                            <td>
                                <input type="text" name="dtpurchasemin" id="dtpurchasemin" size="10" maxlength="15" value="" class="dp" />
                                to
                                <input type="text" name="dtpurchasemax" id="dtpurchasemax" size="10" maxlength="15" value="" class="dp" />
                            </td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>
                                <select name="ssupplid">
                                    <option value="0">Any</option>
                                    <?php optionize($supplier); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Price Range (RM)</td>
                            <td>
                                <input type="text" name="spricemin" size="30" maxlength="20" value="" />
                                to
                                <input type="text" name="spricemax" size="30" maxlength="20" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>Warranty Start Range</td>
                            <td>
                                <input type="text" name="dtwrntstartmin" id="dtwrntstartmin" size="10" maxlength="15" value="" class="dp" />
                                to
                                <input type="text" name="dtwrntstartmax" id="dtwrntstartmax" size="10" maxlength="15" value="" class="dp" />
                            </td>
                        </tr>
                        <tr>
                            <td>Warranty End Range</td>
                            <td>
                                <input type="text" name="dtwrntendmin" id="dtwrntendmin" size="10" maxlength="15" value="" class="dp" />
                                to
                                <input type="text" name="dtwrntendmax" id="dtwrntendmax" size="10" maxlength="15" value="" class="dp" />
                            </td>
                        </tr>
                        <tr>
                            <td>Last Service Range</td>
                            <td>
                                <input type="text" name="lastsvcmin" id="lastsvcmin" size="10" maxlength="15" value="" class="dp" />
                                to
                                <input type="text" name="lastsvcmax" id="lastsvcmax" size="10" maxlength="15" value="" class="dp" />
                            </td>
                        </tr>
                        <tr>
                            <td>Next Service Range</td>
                            <td>
                                <input type="text" name="nextsvcmin" id="nextsvcmin" size="10" maxlength="15" value="" class="dp" />
                                to
                                <input type="text" name="nextsvcmax" id="nextsvcmax" size="10" maxlength="15" value="" class="dp" />
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2">Miscellaneous</td>
                            <td>
                                <input type="checkbox" name="wopending"> Pending Work Order
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Search">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    var oTable;
    var rowSelected = [];
    var tick = '<center><div class="checked">&nbsp;</div></center>';
    var untick = '<center><div class="checkbox">&nbsp;</div></center>';
    var xall = 0;

    $(document).ready(function() {
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
            'bJQueryUI': true,
            'iDisplayLength': 25,
            'sPaginationType': 'full_numbers',
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                console.log(aData);
                if (jQuery.inArray(parseInt($(aData[1]).text()), rowSelected) != -1) {
                    $(nRow).children('td').first().html(tick);
                } else {
                    $(nRow).children('td').first().html(untick);
                }
                $(nRow).children('td').first().click(clicktotick);
                $(nRow).children('td:not(:first-child)').click(clicktoedit);
                return nRow;
            },
            //'aoColumns': [{ "bSortable": false },null,null,null,null,null,null,null,null,null,null,null]
            'aoColumns': [{ "bSortable": false },null,null,null,null,null,null,null,null,null,null]
        });

        $('#tblasset thead th:first').click(function() {
            var iId=0;
            if (xall) {
                xall = 0;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    iId = parseInt($(oTable.fnGetNodes(a)).find('td:eq(1)').text());
                    if (jQuery.inArray(iId, rowSelected) > -1) {
                        rowSelected = jQuery.grep(rowSelected, function(value) { return value != iId; });
                    }
                    $('#tblasset tbody tr td:first-child').html(untick);
                }
            } else {
                xall = 1;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    iId = parseInt($(oTable.fnGetNodes(a)).find('td:eq(1)').text());
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
            var id = $(this).next('td').text();
            var aid = parseInt(id,10);
            if (!isNaN(aid)) {
                window.location = "editasset.php?id=" + aid;
            }
        }

        function clicktotick() {
            //var iId = parseInt($(this).parent().children("td").first().text());
            var iId = parseInt($(this).next('td').text(),10);
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
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = $(this).val();
            locationOpt.f = 'location';
            locationOpt.get();

            var deptOpt = new SelectOption();
            deptOpt.url = 'selectget.php';
            deptOpt.targetId = 'department_id';
            deptOpt.id = $(this).val();
            deptOpt.f = 'department';
            deptOpt.get();
            return false;
        });

        $('#ssiteid').change(function() {
            var slocationOpt = new SelectOption();
            slocationOpt.url = 'selectget.php';
            slocationOpt.targetId = 'slocationid';
            slocationOpt.id = $(this).val();
            slocationOpt.f = 'location';
            slocationOpt.defaultOpt = '<option value="0">ANY</option>';
            slocationOpt.addEmpty = true;
            slocationOpt.get();

            var sDeptOpt = new SelectOption();
            sDeptOpt.url = 'selectget.php';
            sDeptOpt.targetId = 'sdepartment_id';
            sDeptOpt.id = $(this).val();
            sDeptOpt.f = 'department';
            sDeptOpt.defaultOpt = '<option value="0">ANY</option>';
            sDeptOpt.addEmpty = true;
            sDeptOpt.get();
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
    });

    </script>
</body>
</html>
<?php $mysqli->close();?>
