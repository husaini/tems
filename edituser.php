<?php
    require_once(dirname(__FILE__).'/includes/checklogged.php');
    require_once(dirname(__FILE__).'/includes/checkadmin.php');
    require_once(dirname(__FILE__).'/includes/conn.php');
    require_once(dirname(__FILE__).'/includes/sharedfunc.php');

    $uid    =   isset($_GET['id'])? $_GET['id'] : $_SESSION['uid'];

    if (!$uid || !is_numeric($uid)) {
        die();
    }
    $uid    =   intval($uid, 10);
    $sql    =   'SELECT '.
                    'u.* '.
                'FROM '.
                    '`user` u '.
                'WHERE '.
                    'u.id = '.$uid;
    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli));
    $user   =   null;

    if($result) {
        $user   =   $result->fetch_object();

        if ($user->authlevel < 50 && $uid != $_SESSION['uid']) {
            die("Fatal Error: Your eyes are getting privy. Please consult a medical doctor, not the mechanic.");
        }

        $user->access   =   getUserAccessList($uid);
    }
    tabletoarray("site", $sites);
    tabletoarray("site_department", $deps);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Edit User</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.multiselect.css" type="text/css" media="screen">

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.multiselect.min.js"></script>
</head>
<body>
    <div id="body_content">
        <h1 class="page-title full-width">User Accounts Management</h1>
        <p class="clear">&nbsp;</p>
        <?php if(!$user): ?>
            <p class="alert alert-error">
                User not found!
            </p>
        <?php else: ?>
            <?php if($error = getSession('error', true)): ?>
                <p class="alert alert-error"><?php echo $error;?></p>
            <?php endif;?>
            <?php if(getSession('user_deleted', true)): ?>
                <p class="alert alert-success">
                    User <em>"<?php echo getSession('deleted_user', true);?>"</em> was successfully deleted.
                </p>
            <?php endif; ?>
            <?php if($error = getSession('user_updated', true)): ?>
                <p class="alert alert-success">
                    Changes saved.
                </p>
            <?php endif;?>
            <form id="frmUser" method="post" action="mod.php">
                <input type="hidden" name="func" value="edit_user" />
                <input type="hidden" name="id" value="<?php echo $user->id;?>">
                <table class="full-width no-border tems-table">
                    <tr>
                        <td>Username</td>
                        <td class="no-input"><?php echo $user->username; ?></td>
                    </tr>
                    <tr>
                        <td>Fullname</td>
                        <td>
                            <input required="required" type="text" name="ufname" size="50" value="<?php echo stripslashes($user->name); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>Post</td>
                        <td>
                            <input type="text" name="upost" size="50" value="<?php echo stripslashes($user->post);?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>
                            <input  type="text" name="uphone" size="50" value="<?php echo stripslashes($user->phone);?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>
                            <input type="email" name="uemail" size="50" value="<?php echo stripslashes($user->email);?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>Site</td>
                        <td>
                            <select multiple="multiple" name="sites[]" id="siteid" class="select-multi">
                                <?php if (!$user->access['sites']): ?>
                                    <option value="">All</option>
                                    <?php optionize($sites, $user->siteid);?>
                                <?php else: ?>
                                    <?php $usite_id = array(); foreach ($sites as $site_id => $site_name): ?>
                                        <?php foreach ($user->access['sites'] as $usite): ?>
                                            <?php if ($usite['id'] == $site_id): ?>
                                                <?php $usite_id[] =   $site_id; ?>
                                                <option value="<?php echo $site_id;?>" selected="selected"><?php echo $site_name;?></option>
                                            <?php break; endif; ?>
                                        <?php endforeach; ?>

                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>
                            <select multiple="multiple" name="departments[]" id="departmentid" class="select-multi">
                                <?php if (!$user->access['departments']): ?>
                                    <option value="">All</option>
                                    <?php optionize($deps);?>
                                <?php else: ?>
                                    <?php $usite_id = array(); foreach ($deps as $site_id => $site_name): ?>
                                        <?php foreach ($user->access['departments'] as $usite): ?>
                                            <?php if ($usite['id'] == $site_id): ?>
                                                <?php $usite_id[] =   $site_id; ?>
                                                <option value="<?php echo $site_id;?>" selected="selected"><?php echo $site_name;?></option>
                                            <?php break; endif; ?>
                                        <?php endforeach; ?>
                                        <?php if($site_id != $usite_id[count($usite_id)-1]): ?>
                                            <option value="<?php echo $site_id;?>"><?php echo $site_name;?></option>
                                        <?php endif; ?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Location</td>
                        <td>
                            <select multiple="multiple" name="locations[]" id="locationid" class="select-multi">
                                <?php /*if($user->access['locations']): ?>
                                <?php
                                    //$usiteid = 17;
                                    sqltoarray("select id, name from site_location where siteid in " . $usiteid . " order by name", $loc);
                                    optionize($loc, 29);
                                ?>
                                <?php endif; */?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Group</td>
                        <td>
                            <select required="required" name="ugid">
                                <option value="10"<?php echo ($user->authlevel == 10) ? ' selected="selected"':'';?>>Admin</option>
                                <option value="60"<?php echo ($user->authlevel == 60) ? ' selected="selected"':'';?>>Work Order Only</option>
                                <option value="120"<?php echo ($user->authlevel == 120) ? ' selected="selected"':'';?>>View Only</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="vtop">Enabled</td>
                        <td class="radio vtop">
                            <label>
                                <input type="radio" name="uactive" <?php echo ($user->enabled == '1') ? ' checked="checked"':'';?> value="1"> Yes
                            </label>
                            <label>
                                <input type="radio" name="uactive" <?php echo ($user->enabled == '0' || !$user->enabled) ? ' checked="checked"':'';?> value="0"> No
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td class="vtop">Remarks (if any)</td>
                        <td>
                            <textarea name="urem" rows="2" cols="55"><?php echo stripslashes($user->remarks); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" name="update" value="Update" class="btn btn-primary">
                            <a href="user.php" class="btn">Cancel</a>
                        </td>
                    </tr>
                </table>
            </form>

            <h3>Delete User</h3>
                <p>Please exercise discretion before you proceed to use this function. Data deletion is irreversible.</p>
                <p>All related data for this user will be deleted as well.</p>
                <form action="mod.php" method="post">
                    <input type="hidden" name="func" value="edit_user" />
                    <input type="hidden" name="delete_user" value="1" />
                    <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                    <input type="submit" value="Delete User" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">
                </form>
        <?php endif;?>
    </div>
    <?php
        if(!function_exists('google_analytics'))
        {
            require_once(dirname(__FILE__).'/sharedfunc.php');
        }
        google_analytics('uitm');
    ?>
    <?php if($user): ?>
    <script type="text/javascript" src="js/tems.js"></script>
    <script type="text/javascript">
    $(function() {
        function getSiteLocation(depids){
            //once departments are initilized or changed, this function will be called
            //console.log('getSiteLocation')
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = depids;
            locationOpt.f = 'location';
            locationOpt.groupOptions = true;
            locationOpt.get();
            locationOpt.callback = function(d) {
                try {
                    $("#locationid").multiselect('destroy');
                } catch(err) {

                }

                $("#locationid").attr('multiple','multiple').multiselect({
                    checkAllText: 'All Locations',
                    selectedText: "# of # Locations Selected",
                    noneSelectedText: 'Select Location',
                    classes: 'site-multi',
                    minWidth: 335
                });

                //pre-select locations
                $("#locationid").multiselect('uncheckAll');
                <?php if($user->access['locations']): ?>
                $("#locationid").multiselect("widget").find(":checkbox").each(function(){
                    <?php foreach($user->access['locations'] as $loc): ?>
                        if(this.value == <?php echo $loc['id'];?>) {
                            this.click();
                        }
                    <?php endforeach;?>
                });
                <?php endif; ?>

            };
            return false;
        }
        function getDepartmentLocation(depids){
            //once departments are initilized or changed, this function will be called
            //console.log('getSiteLocation')
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = depids;
            locationOpt.f = 'location';
            locationOpt.groupOptions = true;
            locationOpt.get();
            locationOpt.callback = function(d) {
                try {
                    $("#locationid").multiselect('destroy');
                } catch(err) {

                }

                $("#locationid").attr('multiple','multiple').multiselect({
                    checkAllText: 'All Locations',
                    selectedText: "# of # Locations Selected",
                    noneSelectedText: 'Select Location',
                    classes: 'site-multi',
                    minWidth: 335
                });

                //pre-select locations
                $("#locationid").multiselect('uncheckAll');
                <?php if($user->access['locations']): ?>
                $("#locationid").multiselect("widget").find(":checkbox").each(function(){
                    <?php foreach($user->access['locations'] as $loc): ?>
                        if(this.value == <?php echo $loc['id'];?>) {
                            this.click();
                        }
                    <?php endforeach;?>
                });
                <?php endif; ?>

            };
            return false;
        }
        function getSiteDepartment(siteids) {
            //once sites are initilizes or changed this function will be called
            //console.log('getSiteDepartment')
            var deptOpt = new SelectOption();
            deptOpt.url = 'selectget.php';
            deptOpt.targetId = 'departmentid';
            deptOpt.id = siteids;
            deptOpt.f = 'department';
            deptOpt.groupOptions = true;
            deptOpt.get();
            deptOpt.callback = function(d) {
                try {
                    $("#departmentid").multiselect('destroy');
                } catch(err) {

                }

                $("#departmentid").attr('multiple','multiple').multiselect({
                    checkAllText: 'All Departments',
                    selectedText: "# of # Departments Selected",
                    noneSelectedText: 'Select Department',
                    classes: 'site-multi',
                    minWidth: 335,
                    checkAll: function(){
                        var depids = [];
                        $('#departmentid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                            depids.push(this.value);
                        });
                        if(depids.length > 0) {
                            //getSiteLocation(depids);
                            getDepartmentLocation(depids);
                        } else {
                            disableMultiselect('#locationid');
                        }
                    },
                    uncheckAll: function() {
                        disableMultiselect('#locationid');
                    }
                });

                //pre-select departments
                $("#departmentid").multiselect('uncheckAll');
                <?php if($user->access['departments']): ?>
                $("#departmentid").multiselect("widget").find(":checkbox").each(function(){
                    <?php foreach($user->access['departments'] as $dept): ?>
                        if(this.value == <?php echo $dept['id'];?>) {
                            this.click();
                        }
                    <?php endforeach;?>
                });
                <?php endif; ?>

                //initilize locations
                var depids = [];
                $('#departmentid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                    depids.push(this.value);
                });
                if(depids.length > 0) {
                    //getSiteLocation(depids);
                    getDepartmentLocation(depids);
                } else {
                    disableMultiselect('#locationid');
                }

                //delegating the click handler to end of initilization to prevent nested callbacks
                $("#departmentid").bind("multiselectclick", function(event, ui){
                    var depids = [];
                    $('#departmentid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                        depids.push(this.value);
                    });
                    if(depids.length > 0) {
                        //getSiteLocation(depids);
                        getDepartmentLocation(depids);
                    } else {
                        disableMultiselect('#locationid');
                    }
                });
            };
            return false;
        }

        function disableMultiselect(target) {
            if($(target).length > 0) {
                $(target).empty().removeAttr('multiple');
                try {
                    $(target).multiselect('destroy');
                } catch(err) {

                }
            }
        }

        $("#siteid").multiselect({
            checkAllText: 'All Sites',
            selectedText: "# of # Sites Selected",
            noneSelectedText: 'Select Sites',
            classes: 'site-multi',
            minWidth: 335,
            checkAll: function(){
                var siteids = [];
                $('#siteid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                    siteids.push(this.value);
                });
                if(siteids.length > 0) {
                    getSiteDepartment(siteids);
                } else {
                    disableMultiselect('#locationid');
                    disableMultiselect('#departmentid');
                }
            },
            uncheckAll: function() {
                disableMultiselect('#locationid');
                disableMultiselect('#departmentid');
            }
        });

        //initilize departments
        var siteids = [];
        $('#siteid').attr('multiple','multiple').multiselect('getChecked').map(function() {
            siteids.push(this.value);
        });
        if(siteids.length > 0) {
            getSiteDepartment(siteids);
        } else {
            disableMultiselect('#locationid');
            disableMultiselect('#departmentid');
        }

        //delegating the click handler to end of initilization to prevent nested callbacks
        $('#siteid').bind("multiselectclick", function(event, ui){
            var siteids = [];
            $('#siteid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                siteids.push(this.value);
            });
            if(siteids.length > 0) {
                getSiteDepartment(siteids);
            } else {
                disableMultiselect('#locationid');
                disableMultiselect('#departmentid');
            }
        });

        if($('.alert').length > 0) {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 2000);
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
<?php
$mysqli->close();
