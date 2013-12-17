<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/cons.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

date_default_timezone_set("Asia/Kuala_Lumpur");

//$link = mysql_connect('localhost','root','');
//$mydb = mysql_select_db('tems',$link);

$sid        =   $_SESSION['sid'];
$rem        =   $_SESSION['rem'];

$hi         =   (isset($_POST['hi'])) ? $_POST['hi'] : '';
$sclassid   =   (isset($_POST['sclassid'])) ? $_POST['sclassid'] : '';
$stypeid    =   (isset($_POST['stypeid'])) ? $_POST['stypeid'] : '';
$smanuid    =   (isset($_POST['smanuid'])) ? $_POST['smanuid'] : '';
$smanuid2   =   (isset($_POST['smanuid2'])) ? $_POST['smanuid2'] : '';
$smanuid3   =   (isset($_POST['smanuid3'])) ? $_POST['smanuid3'] : '';
$smodelid   =   (isset($_POST['smodelid'])) ? $_POST['smodelid'] : '';
$smodelid2  =   (isset($_POST['smodelid2'])) ? $_POST['smodelid2'] : '';
$smodelid3  =   (isset($_POST['smodelid3'])) ? $_POST['smodelid3'] : '';

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


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Compare</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.validationengine.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-1.6.4.js"></script>
<script type="text/javascript" src="js/jquery.validationengine-en-1.6.4.js"></script>
<script type="text/javascript" src="datatables/jquery.datatables.js"></script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Compare</h1>
            <ul>
                <li><a href="#tabsearch">By corrective maintenance</a></li>
            </ul>
            <div id="tabsearch">
                <h3>Search Asset</h3>
                <!--form method="post" action="searchasset.php"-->
                <form method="post" action="compare2.php">
                    <table class="full-width no-border">
                        <!--tr><td>Asset No</td><td><input type="text" name="sassetno" size="30" maxlength="15" value="" /></td></tr-->
                        <tr>
                            <td>Equipment</td>
                            <td colspan="3">
                                <select id="sclassid" name="sclassid">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td colspan="3">
                                <select id="stypeid" name="stypeid">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td width="25%">Manufacturer</td>
                            <td colspan="3">
                                <select id="smanuid" name="smanuid">
                                    <option value="0">ANY</option>
                                </select>

                                <select id="smanuid2" name="smanuid2">
                                    <option value="0">ANY</option>
                                </select>
                                <select id="smanuid3" name="smanuid3">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td width="25%">Model</td>
                            <td colspan="3">
                                <select id="smodelid" name="smodelid">
                                    <option value="0">ANY</option>
                                </select>

                                <select id="smodelid2" name="smodelid2">
                                    <option value="0">ANY</option>
                                </select>

                                <select id="smodelid3" name="smodelid3">
                                    <option value="0">ANY</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" align="center">
                                <input type="submit" value="Search" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="hi" value="1" />
                </form>
                &nbsp;

                <?php if($hi) : ?>
                    <table class="full-width">
                        <?php
                            $q      =   mysql_query("select * from asset where modelid = '$smodelid'");
                            $n      =   mysql_num_rows($q);
                            $tot10  =   0;
                            $tot11  =   0;
                            $tot13  =   0;
                            $tot15  =   0;

                            for($i=0;$i<$n;$i++){
                                $d          =   mysql_fetch_array($q);
                                $start      =   $d[purchasedate];
                                $end        =   date("Y-m-d");
                                $start_ts   =   strtotime($start);
                                $end_ts     =   strtotime($end);
                                $diff       =   $end_ts - $start_ts;
                                $dif2       =   round($diff / 86400);

                                if($dif2 < 365){
                                    //$age = "less than a year";
                                    $q10    =   mysql_query("select * from history_workorder where assetid = '$d[id]' and category = '2'");
                                    $n10    =   mysql_num_rows($q10);
                                    $tot10  =   $tot10 + $n10;
                                } elseif(($dif2 > 365)&&($dif2 < 1095)){
                                    //$age = "1 to 3 years";
                                    $q11    =   mysql_query("select * from history_workorder where assetid = '$d[id]' and category = '2'");
                                    $n11    =   mysql_num_rows($q11);
                                    $tot11  =   $tot11 + $n11;
                                } elseif(($dif2 > 1095)&&($dif2 < 1825)){
                                    //$age = "3 to 5 years";
                                    $q13    =   mysql_query("select * from history_workorder where assetid = '$d[id]' and category = '2'");
                                    $n13    =   mysql_num_rows($q13);
                                    $tot13  =   $tot13 + $n13;
                                } elseif($dif2 > 1825){
                                    //$age = "more than 5 years";
                                    $q15    =   mysql_query("select * from history_workorder where assetid = '$d[id]' and category = '2'");
                                    $n15    =   mysql_num_rows($q15);
                                    $tot15  =   $tot15 + $n15;
                                } else {
                                    $age = "";
                                }
                                //echo "age $age $dif2 <br>";
                            }

                            $gtot   =   $tot10 + $tot11 + $tot13 + $tot15;

                            $q2     =   mysql_query("select * from asset where modelid = '$smodelid2'");
                            $n2     =   mysql_num_rows($q2);
                            $tot20  =   0;
                            $tot21  =   0;
                            $tot23  =   0;
                            $tot25  =   0;

                            for($i2=0;$i2<$n2;$i2++){
                                $d2 = mysql_fetch_array($q2);
                                $start2 = $d2[purchasedate];
                                $end2 = date("Y-m-d");
                                $start_ts2 = strtotime($start2);
                                $end_ts2 = strtotime($end2);
                                $diff2 = $end_ts2 - $start_ts2;
                                $dif22 = round($diff2 / 86400);

                                if($dif22 < 365){
                                    $q20 = mysql_query("select * from history_workorder where assetid = '$d2[id]' and category = '2'");
                                    $n20 = mysql_num_rows($q20);
                                    $tot20 = $tot20 + $n20;
                                }elseif(($dif22 > 365)&&($dif22 < 1095)){
                                    $q21 = mysql_query("select * from history_workorder where assetid = '$d2[id]' and category = '2'");
                                    $n21 = mysql_num_rows($q21);
                                    $tot21 = $tot21 + $n21;
                                }elseif(($dif22 > 1095)&&($dif22 < 1825)){
                                    $q23 = mysql_query("select * from history_workorder where assetid = '$d2[id]' and category = '2'");
                                    $n23 = mysql_num_rows($q23);
                                    $tot23 = $tot23 + $n23;
                                }elseif($dif22 > 1825){
                                    $q25 = mysql_query("select * from history_workorder where assetid = '$d2[id]' and category = '2'");
                                    $n25 = mysql_num_rows($q25);
                                    $tot25 = $tot25 + $n25;
                                }else{
                                    $age2 = "";
                                }
                            }

                            $gtot2 = $tot20 + $tot21 + $tot23 + $tot25;

                            $q3 = mysql_query("select * from asset where modelid = '$smodelid3'");
                            $n3 = mysql_num_rows($q3);
                            $tot30 = 0;
                            $tot31 = 0;
                            $tot33 = 0;
                            $tot35 = 0;
                            for($i3=0;$i3<$n3;$i3++){
                                $d3 = mysql_fetch_array($q3);
                                $start3 = $d3[purchasedate];
                                $end3 = date("Y-m-d");
                                $start_ts3 = strtotime($start3);
                                $end_ts3 = strtotime($end3);
                                $diff3 = $end_ts3 - $start_ts3;
                                $dif23 = round($diff3 / 86400);
                                if($dif23 < 365){
                                    $q30 = mysql_query("select * from history_workorder where assetid = '$d3[id]' and category = '2'");
                                    $n30 = mysql_num_rows($q30);
                                    $tot30 = $tot30 + $n30;
                                }elseif(($dif23 > 365)&&($dif23 < 1095)){
                                    $q31 = mysql_query("select * from history_workorder where assetid = '$d3[id]' and category = '2'");
                                    $n31 = mysql_num_rows($q31);
                                    $tot31 = $tot31 + $n31;
                                }elseif(($dif23 > 1095)&&($dif23 < 1825)){
                                    $q33 = mysql_query("select * from history_workorder where assetid = '$d3[id]' and category = '2'");
                                    $n33 = mysql_num_rows($q33);
                                    $tot33 = $tot33 + $n33;
                                }elseif($dif23 > 1825){
                                    $q35 = mysql_query("select * from history_workorder where assetid = '$d3[id]' and category = '2'");
                                    $n35 = mysql_num_rows($q35);
                                    $tot35 = $tot35 + $n35;
                                }else{
                                    $age3 = "";
                                }
                            }
                            $gtot3 = $tot30 + $tot31 + $tot33 + $tot35;


                            $sql_asset_class    =   'SELECT `name` FROM `asset_class` WHERE 1 ';
                            $sclassid           =   mysql_real_escape_string($sclassid, $link);
                            $clause             =   "AND  id = '$sclassid' ";

                            if ($usite_ids) {
                                // get classid from asset table
                                $uclass_ids = array();
                                $result =   mysql_query('SELECT `classid` FROM `asset` WHERE siteid IN('.implode(',', $usite_ids).')') or die(mysql_error($link));
                                if ($result) {
                                    while ($row = mysql_fetch_assoc($result)) {
                                        $uclass_ids[]   =   $row['classid'];
                                    }
                                    mysql_free_result($result);
                                }

                                if(is_numeric($sclassid)) {
                                    $uclass_ids = array_merge($uclass_ids, array((int)$sclassid));
                                }

                                if($uclass_ids) {
                                    $uclass_ids =   array_unique($uclass_ids);
                                    $clause =  'AND id IN('.implode(',', $uclass_ids).') ';
                                }
                            }
                            $sql_asset_class    .=  $clause;

                            $lbl1 = mysql_query($sql_asset_class);
                            $clas = mysql_fetch_array($lbl1);
                            $lbl2 = mysql_query("select name from asset_type where id = '$stypeid'");
                            $type = mysql_fetch_array($lbl2);
                            $lbl3 = mysql_query("select name from asset_manufacturer where id = '$smanuid'");
                            $man1 = mysql_fetch_array($lbl3);
                            $lbl4 = mysql_query("select name from asset_manufacturer where id = '$smanuid2'");
                            $man2 = mysql_fetch_array($lbl4);
                            $lbl5 = mysql_query("select name from asset_manufacturer where id = '$smanuid3'");
                            $man3 = mysql_fetch_array($lbl5);
                            $lbl6 = mysql_query("select name from asset_model where id = '$smodelid'");
                            $mod1 = mysql_fetch_array($lbl6);
                            $lbl7 = mysql_query("select name from asset_model where id = '$smodelid2'");
                            $mod2 = mysql_fetch_array($lbl7);
                            $lbl8 = mysql_query("select name from asset_model where id = '$smodelid3'");
                            $mod3 = mysql_fetch_array($lbl8);
                        ?>
                        <tr>
                            <th>Equipment</th>
                            <td colspan="3"><?php echo $clas['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td colspan="3"><?php echo $type['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Manufacturer</th>
                            <td><?php echo $man1['name'] ?></td>
                            <td><?php echo $man2['name'] ?></td>
                            <td><?php echo $man3['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td><?php echo $mod1['name'] ?></td>
                            <td><?php echo $mod2['name'] ?></td>
                            <td><?php echo $mod3['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Total Assets</th>
                            <td><?php echo "$n" ?></td>
                            <td><?php echo "$n2" ?></td>
                            <td><?php echo "$n3" ?></td>
                        </tr>
                        <tr>
                            <th width="25%">< 1 year old</th>
                            <td width="25%"><?php echo $tot10 ?></td>
                            <td width="25%"><?php echo $tot20 ?></td>
                            <td width="25%"><?php echo $tot30 ?></td>
                        </tr>
                        <tr>
                            <th>1 - 3 years old</th>
                            <td><?php echo $tot11 ?></td>
                            <td><?php echo $tot21 ?></td>
                            <td><?php echo $tot31 ?></td>
                        </tr>
                        <tr>
                            <th>3 - 5 years old</th>
                            <td><?php echo $tot13 ?></td>
                            <td><?php echo $tot23 ?></td>
                            <td><?php echo $tot33 ?></td>
                        </tr>
                        <tr>
                            <th>> 5 years old</th>
                            <td><?php echo $tot15 ?></td>
                            <td><?php echo $tot25 ?></td>
                            <td><?php echo $tot35 ?></td>
                        </tr>
                        <tr>
                            <th>Total Workorder</th>
                            <td><?php echo $gtot ?></td>
                            <td><?php echo $gtot2 ?></td>
                            <td><?php echo $gtot3 ?></td>
                        </tr>
                    </table>
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
        $('#tabs').tabs();
        $('#dtpurchase').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntstart').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntend').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtpurchasemin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtpurchasemax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntstartmin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntstartmax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntendmin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtwrntendmax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#dtppmstart').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#lastsvcmin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#lastsvcmax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#nextsvcmin').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#nextsvcmax').datepicker({changeMonth:true,changeYear:true,dateFormat:'yy-mm-dd',yearRange:'1980:2020'});
        $('#hantu').validationEngine();
        oTable = $('#tblasset').dataTable({
            'bProcessing': true,
            'bServerSide': true,
            "bStateSave": true,
            'sAjaxSource': 'ajaxasset.php',
            'bJQueryUI': true,
            'iDisplayLength': 25,
            'sPaginationType': 'full_numbers',
            'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                if (jQuery.inArray(parseInt($(aData[0]).text()), rowSelected) != -1)
                    $(nRow).children('td').last().html(tick);
                else
                    $(nRow).children('td').last().html(untick);
                $(nRow).children('td').last().click(clicktotick);
                $(nRow).children('td:not(:last-child)').click(clicktoedit);
                //$(nRow).children('td').next().click(clicktoedit);
                return nRow;
            },
            'aoColumns': [null,null,null,null,null,null,null,null,null,null,null,null,null,null,{ "bSortable": false }]
        });
        $('#tblasset thead th:last').click(function() {
            if (xall) {
                xall = 0;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    iId = parseInt($(oTable.fnGetNodes(a)).find('td:first').text());
                    if (jQuery.inArray(iId, rowSelected) > -1) rowSelected = jQuery.grep(rowSelected, function(value) { return value != iId; });
                    $('#tblasset tbody tr td:last-child').html(untick);
                }
            } else {
                xall = 1;
                ayam = oTable.fnGetNodes();
                for (a = 0; a < ayam.length; a++) {
                    iId = parseInt($(oTable.fnGetNodes(a)).find('td:first').text());
                    if (jQuery.inArray(iId, rowSelected) == -1) rowSelected[rowSelected.length++] = iId;
                    $('#tblasset tbody tr td:last-child').html(tick);
                }
            }

            $('#go').find('option').first().text('Selected: ' + rowSelected.length);
        });
        $('#tblasset tbody tr').live('mouseover mouseout', function(e) {
            if (e.type == 'mouseover')
                $(this).addClass("hl");
            else
                $(this).removeClass("hl");
        });

        function clicktoedit() {
             var aid = parseInt($(this).parent().children("td").first().text());
            if (!isNaN(aid)) window.location = "editasset.php?id=" + aid;
        }

        function clicktotick() {
            var iId = parseInt($(this).parent().children("td").first().text());

            if (jQuery.inArray(iId, rowSelected) == -1)
                rowSelected[rowSelected.length++] = iId;
            else
                rowSelected = jQuery.grep(rowSelected, function(value) { return value != iId; });

            if ($(this).html() !== untick) $(this).html(untick); else $(this).html(tick);
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

        $.getJSON("selectget.php",{id: 0, ie: 0, f: "init"}, function(j) {
                var options = '<option>ANY</option>';
                for (var i = 0; i < j.length; i++) {
                  options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#classid').html(options);
                $('#classid option:first').attr('selected', 'selected');
        });

        $('#classid').change(function() {
          console.log("test");
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

        $('#siteid').change(function() {
            $.getJSON("selectget.php",{id: $(this).val(), ie: 0, f: "location"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#locationid').html(options);
                $('#locationid option:first').attr('selected', 'selected');
            });
        });

        $('#ssiteid').change(function() {
            $.getJSON("selectget.php",{id: $(this).val(), ie: 0, f: "location"}, function(j) {
                var options = '<option value="0">ANY</option>';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#slocationid').html(options);
                $('#slocationid option:first').attr('selected', 'selected');
            });
        });

        $.getJSON("searchget.php",{id: 0, ie: 0, f: "init"}, function(j) {
          console.log(j);
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#sclassid').html(options);
                $('#sclassid option:first').attr('selected', 'selected');
        });

        $('#sclassid').change(function() {
            $.getJSON("searchget.php",{id: $(this).val(), ie: 0, f: "typeclass"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#stypeid').html(options);
                $('#stypeid option:first').attr('selected', 'selected');

                $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: 0, f: "manutype"}, function(j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $('#smanuid').html(options);
                    $('#smanuid option:first').attr('selected', 'selected');
                    $('#smanuid2').html(options);
                    $('#smanuid2 option:first').attr('selected', 'selected');
                    $('#smanuid3').html(options);
                    $('#smanuid3 option:first').attr('selected', 'selected');

                    $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: $('#smanuid').val(), f: "modeltype"}, function(j) {
                        var options = '';
                        for (var i = 0; i < j.length; i++) {
                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                        }
                        $('#smodelid').html(options);
                        $('#smodelid option:first').attr('selected', 'selected');
                        $('#smodelid2').html(options);
                        $('#smodelid2 option:first').attr('selected', 'selected');
                        $('#smodelid3').html(options);
                        $('#smodelid3 option:first').attr('selected', 'selected');
                    });
                });
            });
        });

        $('#stypeid').change(function() {
            $.getJSON("searchget.php",{id: $(this).val(), ie: 0, f: "manutype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#smanuid').html(options);
                $('#smanuid option:first').attr('selected', 'selected');
                $('#smanuid2').html(options);
                $('#smanuid2 option:first').attr('selected', 'selected');
                $('#smanuid3').html(options);
                $('#smanuid3 option:first').attr('selected', 'selected');

                $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: $('#smanuid').val(), f: "modeltype"}, function(j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $('#smodelid').html(options);
                    $('#smodelid option:first').attr('selected', 'selected');
                    $('#smodelid2').html(options);
                    $('#smodelid2 option:first').attr('selected', 'selected');
                    $('#smodelid3').html(options);
                    $('#smodelid3 option:first').attr('selected', 'selected');
                });
            });

        });

        $('#smanuid').change(function() {
            $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: $(this).val(), f: "modeltype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#smodelid').html(options);
                $('#smodelid option:first').attr('selected', 'selected');
            });
        });
        $('#smanuid2').change(function() {
            $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: $(this).val(), f: "modeltype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#smodelid2').html(options);
                $('#smodelid2 option:first').attr('selected', 'selected');
            });
        });
        $('#smanuid3').change(function() {
            $.getJSON("searchget.php",{id: $('#stypeid').val(), ie: $(this).val(), f: "modeltype"}, function(j) {
                var options = '';
                for (var i = 0; i < j.length; i++) {
                    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                }
                $('#smodelid3').html(options);
                $('#smodelid3 option:first').attr('selected', 'selected');
            });
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

                if ($('#act').val() == 'wo')
                    $('#qact').attr('action', 'workorder.php#tabnew');
                if ($('#act').val() == 'pn')
                    $('#qact').attr('action', 'printoption.php');
            }
        });
    });
    </script>
</body>
</html>
<?php
$mysqli->close();
