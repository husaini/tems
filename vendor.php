<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

date_default_timezone_set("Asia/Kuala_Lumpur");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Vendor</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/table_jui.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<?php if (!isguest()): ?>
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
</script>
<?php endif; ?>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">Vendor</h1>
            <ul>
                <li><a href="#tablist">List</a></li>
                <li><a href="#tabnew">New</a></li>
            </ul>
            <div id="tablist">
                <h3>Vendor List</h3>
                <table class="tlist full-width">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Contact Person</th>
                            <th>Phone No</th>
                            <th>Fax No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vtype[0] = "Any";
                        $vtype[1] = "Supplier";
                        $vtype[2] = "Maintenance";

                        $stmt = $mysqli->prepare("select id, name, type, person, phone, fax, author from vendor order by name");
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows() > 0): ?>
                            <?php
                            $meta = $stmt->result_metadata();
                            while ($column = $meta->fetch_field()) {
                                $bindvars[] = &$results[$column->name];
                            }
                            call_user_func_array(array($stmt, 'bind_result'), $bindvars);
                            while ($stmt->fetch()):
                            ?>
                            <?php if (!isguest()): ?>
                                <tr>
                                    <td>
                                        <a href="editvendor.php?id=<?php echo $results['id'];?>"><?php echo $results['name'];?></a>
                                    </td>

                            <?php else: ?>
                                <tr>
                                    <td><?php echo $results['name'];?></td>

                            <?php endif; ?>

                                    <td><?php echo $vtype[$results['type']];?></td>
                                    <td><?php echo $results['person'];?></td>
                                    <td><?php echo $results['phone'];?></td>
                                    <td><?php echo  $results['fax'];?></td>
                                </tr>
                            <?php endwhile; ?>


                        <?php else: ?>
                            <tr>
                                <td colspan="5">No vendors found.</td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!isguest()): ?>

                <div id="tabnew">
                    <h3>Add New Vendor</h3>
                    <?php if (isworker()): ?>
                        <form  method="post" action="mod.php" onsubmit="return verifyform()">
                            <input type="hidden" name="func" value="add_vendor" />
                            <table class="full-width no-border">
                                <tr>
                                    <td>Company Name <span class="required">*</span></td>
                                    <td>
                                        <input required="required" type="text" name="vname" size="60" maxlength="100" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Contact Person</td>
                                    <td>
                                        <input type="text" name="vperson" size="30" maxlength="100" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Phone No</td>
                                    <td>
                                        <input type="text" name="vphone" size="30" maxlength="20" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Fax No</td>
                                    <td>
                                        <input type="text" name="vfax" size="30" maxlength="20" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>
                                        <textarea name="vaddr" rows="2" cols="55"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>
                                        <input type="text" name="vemail" size="30" maxlength="20" value="" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Type</td>
                                    <td>
                                        <select name="vtype">
                                            <option value="0">Any</option>
                                            <option value="1">Supplier</option>
                                            <option value="2">Maintenance</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td>
                                        <select name="vstatus">
                                            <option value="0">Inactive</option>
                                            <option value="1" selected>Active</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Remarks (if any)</td>
                                    <td>
                                        <textarea name="vrem" rows="2" cols="55"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <input type="submit" value="Submit Data" class="btn btn-primary">
                                        <a href="vendor.php" class="btn">Cancel</a>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        <p> <span class="required">*</span> Mandatory Field</p>

                    <?php else: ?>

                        <p><i>You are not authorized to use this function.</i></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>
