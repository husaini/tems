<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/checkadmin.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

$admins             =   array();
$stmt               =   null;
$users              =   array();
$total_departments  =   0;
$total_locations    =   0;
$total_sites        =   0;

tabletoarray("site", $sites);

$sql    =   'SELECT '.
                'user.*, '.
                'site.name sname '.
            'FROM '.
                '`user` '.
            'LEFT JOIN '.
                '`site` ON site.id = siteid '.
            'WHERE '.
                '`enabled` = 1 '.
            'ORDER BY '.
                'authlevel ASC, lastaccess DESC';

$result =   $mysqli->query($sql);

while ($row = $result->fetch_assoc()) {
    $row['access']  =   getUserAccessList($row['id']);
    //if ($row['siteid'] >= $_SESSION['gid']) {
        if ($row['authlevel'] > 50) {
            $users[]    =   $row;
        } else {
            $admins[]   =   $row;
        }
    //}
}
mysqli_free_result($result);

$result =   $mysqli->query('SELECT COUNT(1) FROM site');
if($result){
    list($total_sites) = $result->fetch_row();
    mysqli_free_result($result);
}
$result =   $mysqli->query('SELECT COUNT(1) FROM site_location');
if($result){
    list($total_locations) = $result->fetch_row();
    mysqli_free_result($result);
}
$result =   $mysqli->query('SELECT COUNT(1) FROM site_department');
if($result){
    list($total_departments) = $result->fetch_row();
    mysqli_free_result($result);
}
//debug($admins);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: User Management</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jquery.multiselect.css" type="text/css" media="screen">

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.multiselect.min.js"></script>
</head>
<body>
    <div id="body_content">
        <div id="tabs" class="tems-ui-tab">
            <h1 class="page-title">User Accounts</h1>
            <ul>
                <li><a href="#tablist">List</a></li>
                <li><a href="#tabnew">New</a></li>
            </ul>
            <div id="tablist">

                <?php if($error = getSession('error', true)): ?>
                    <p class="alert alert-error"><?php echo $error;?></p>
                <?php endif;?>
                <?php if(getSession('user_deleted', true)): ?>
                    <p class="alert alert-success">
                        User <em>"<?php echo getSession('deleted_user', true);?>"</em> was successfully deleted.
                    </p>
                <?php endif; ?>
                <?php if ($admins): ?>
                    <h3>Admin Group</h3>
                    <table class="full-width tems-table">
                        <thead>
                            <tr>
                                <th width="10%">Username</th>
                                <th width="17%">Fullname</th>
                                <th width="17%">Site</th>
                                <th width="17%">Department</th>
                                <th width="17%">Location</th>
                                <th width="17%">Last Login</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($admins as $admin): ?>
                            <tr>
                                <td><?php echo $admin['username'];?></td>
                                <td><?php echo $admin['name'];?></td>
                                <td>
                                    <?php if(count($admin['access']['sites']) == $total_sites): ?>
                                        All
                                    <?php else: ?>
                                        <?php if($admin['access']['sites']): ?>
                                            <?php if(count($admin['access']['sites']) == $total_sites): ?>
                                                All
                                            <?php else: ?>
                                                <?php $count=1; foreach ($admin['access']['sites'] as $site): ?>
                                                    <a href="editsite.php?id=<?php echo $site['id'];?>&amp;tab=tabdetails"><?php echo $site['name']; ?></a>
                                                    <?php if($count < count($admin['access']['sites'])): ?>
                                                        ,
                                                    <?php endif; ?>
                                                <?php $count++; endforeach; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="editsite.php?id=<?php echo $admin['siteid'];?>&amp;tab=tabdetails">
                                                <?php echo $admin['sname'];?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($admin['access']['departments']): ?>
                                        <?php if(count($admin['access']['departments']) == $total_departments): ?>
                                            All
                                        <?php else: ?>
                                            <?php $count=1; foreach ($admin['access']['departments'] as $dept): ?>
                                                <a href="editdept.php?id=<?php echo $dept['id'];?>&amp;sid=<?php echo $dept['siteid'];?>&amp;tab=tabloc">
                                                    <?php echo $dept['name']; ?>
                                                </a>
                                                <?php if($count < count($admin['access']['departments'])): ?>
                                                    ,
                                                <?php endif; ?>
                                            <?php $count++; endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($admin['access']['locations']): ?>
                                        <?php if(count($admin['access']['locations']) == $total_locations): ?>
                                            All
                                        <?php else: ?>
                                            <?php $count=1; foreach ($admin['access']['locations'] as $loc): ?>
                                                <?php
                                                /*
                                                <a href="editloc.php?id=<?php echo $loc['id'];?>&amp;depid=<?php echo $loc['depid'];?>&amp;tab=tabloc"><?php echo $loc['name']; ?></a>
                                                */
                                                ?>
                                                <a href="editloc.php?id=<?php echo $loc['id'];?>&amp;tab=tabloc"><?php echo $loc['name']; ?></a>
                                                <?php if($count < count($admin['access']['locations'])): ?>
                                                    ,
                                                <?php endif; ?>
                                            <?php $count++; endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <td nowrap="nowrap" class="right">
                                    <?php echo date('l, jS F, Y', strtotime($admin['lastaccess']));?> at <?php echo date('g:i A', strtotime($admin['lastaccess']));?>
                                </td>
                                <td nowrap="nowrap" class="center action">
                                    <a href="edituser.php?id=<?php echo $admin['id'];?>">Edit</a> |
                                    <a href="#" name="<?php echo $admin['name'];?>" id="user_<?php echo $admin['id'];?>" class="del-user">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if ($users): ?>
                    <h3>User Group</h3>
                    <table class="full-width tems-table">
                        <thead>
                            <tr>
                                <th width="10%">Username</th>
                                <th width="17%">Fullname</th>
                                <th width="17%">Site</th>
                                <th width="17%">Location</th>
                                <th width="17%">Department</th>
                                <th width="17%">Last Login</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['username'];?></td>
                                <td><?php echo $u['name'];?></td>
                                <td>
                                    <?php if(!$u['sname']): ?>
                                        All
                                    <?php else: ?>
                                        <?php if($u['access']['sites']): ?>
                                            <?php if(count($u['access']['sites']) == $total_sites): ?>
                                                All
                                            <?php else: ?>
                                                <?php $count=1; foreach ($u['access']['sites'] as $site): ?>
                                                    <a href="editsite.php?id=<?php echo $site['id'];?>&amp;tab=tabdetails"><?php echo $site['name']; ?></a>
                                                    <?php if($count < count($u['access']['sites'])): ?>
                                                        ,
                                                    <?php endif; ?>
                                                <?php $count++; endforeach; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="editsite.php?id=<?php echo $u['siteid'];?>&amp;tab=tabdetails">
                                                <?php echo $u['sname'];?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['access']['locations']): ?>
                                        <?php if(count($u['access']['locations']) == $total_locations): ?>
                                            All
                                        <?php else: ?>
                                            <?php $count=1; foreach ($u['access']['locations'] as $loc): ?>
                                                <a href="editloc.php?id=<?php echo $loc['id'];?>&amp;sid=<?php echo $loc['siteid'];?>&amp;tab=tabloc"><?php echo $loc['name']; ?></a>
                                                <?php if($count < count($u['access']['locations'])): ?>
                                                    ,
                                                <?php endif; ?>
                                            <?php $count++; endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['access']['departments']): ?>
                                        <?php if(count($u['access']['departments']) == $total_departments): ?>
                                            All
                                        <?php else: ?>
                                            <?php $count=1; foreach ($u['access']['departments'] as $dept): ?>
                                                <a href="editdept.php?id=<?php echo $dept['id'];?>&amp;sid=<?php echo $dept['siteid'];?>&amp;tab=tabloc">
                                                    <?php echo $dept['name']; ?>
                                                </a>
                                                <?php if($count < count($u['access']['departments'])): ?>
                                                    ,
                                                <?php endif; ?>
                                            <?php $count++; endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td nowrap="nowrap" class="right">
                                    <?php echo date('l, jS F, Y', strtotime($u['lastaccess']));?> at <?php echo date('g:i A', strtotime($u['lastaccess']));?>
                                </td>
                                <td nowrap="nowrap" class="center action">
                                    <a href="edituser.php?id=<?php echo $u['id'];?>">Edit</a> |
                                    <a href="#" name="<?php echo $u['name'];?>" id="user_<?php echo $u['id'];?>" class="del-user">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div id="tabnew">
                <h3>Add New User</h3>
                <p>
                    All text input fields are mandatory unless otherwise specified. Password is initialized as 'password'.
                </p>
                <p>
                    Please advise your users to change the password immediately upon login.
                </p>
                <?php if (getSession('user_added', true)):?>
                    <p class="alert alert-success">
                        User <em>"<?php echo stripslashes(getSession('added_user', true));?>"</em> was successfull added.
                    </p>
                <?php endif; ?>

                <form id="frmnewuser" method="post" action="mod.php">
                    <input type="hidden" name="func" value="add_user" />
                    <table class="full-width no-border tems-table">
                        <tr>
                            <td>Fullname</td>
                            <td>
                                <input required="required" type="text" name="ufname" id="ufname" size="50" maxlength="50" />
                            </td>
                        </tr>
                        <tr>
                            <td>Username</td>
                            <td>
                                <input required="required" type="text" name="uname" id="uname" size="20" maxlength="50" /> (6-15 characters)
                            </td>
                        </tr>
                        <tr>
                            <td>Password</td>
                            <td class="no-input"><i>password</i></td>
                        </tr>
                        <tr>
                            <td>Post</td>
                            <td>
                                <input type="text" name="upost" size="50" maxlength="100" />
                            </td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>
                                <input  type="text" name="uphone" size="50" maxlength="40" />
                            </td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>
                                <input type="email" name="uemail" size="50" maxlength="40" />
                            </td>
                        </tr>
                        <tr>
                            <td>Site</td>
                            <td>
                                <select multiple="multiple" name="sites[]" id="siteid" class="select-multi">
                                    <?php optionize($sites); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>
                                <select multiple="multiple" name="departments[]" id="departmentid" class="select-multi"></select>
                            </td>
                        </tr>
                        <tr>
                            <td>Location</td>
                            <td>
                                <select multiple="multiple" name="locations[]" id="locationid" class="select-multi"></select>
                            </td>
                        </tr>

                        <tr>
                            <td>Group</td>
                            <td>
                                <select required="required" name="ugid">
                                    <option value="10">Admin</option>
                                    <option value="60" selected="selected">Work Order Only</option>
                                    <option value="120" selected>View Only</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks (if any)</td>
                            <td>
                                <textarea name="urem" rows="2" cols="55"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="submit" value="Add User" class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="js/tems.js"></script>
    <script type="text/javascript">
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
        $("#frmnewuser").submit(function() {
            if ($("#ufname").val() == "" || $("#uname").val() == "") {
                alert("Fullname and Username fields cannot be empty");
                return false;
            } else if ($("#uname").val().length < 6 || $("#uname").val().length > 45) {
                alert("Username length must be between 6 and 20 characters");
                $("#uname").focus();
                return false;
            } else if($('#siteid').multiselect('getChecked').length == 0) {
                alert('Please select site.');
                return false;
            }
            return 1;
        });

        function disableMultiselect(target) {
            if($(target).length > 0) {
                $(target).empty().removeAttr('multiple');
                try {
                    $(target).multiselect('destroy');
                } catch(err) {

                }
            }
        }

        ////////////////////////////////////////////////////////////////
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
                            getSiteLocation(depids);
                        } else {
                            disableMultiselect('#locationid');
                        }
                    },
                    uncheckAll: function() {
                        disableMultiselect('#locationid');
                    }
                });

                //initilize locations
                var depids = [];
                $('#departmentid').attr('multiple','multiple').multiselect('getChecked').map(function() {
                    depids.push(this.value);
                });
                if(depids.length > 0) {
                    getSiteLocation(depids);
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
                        getSiteLocation(depids);
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

        /*function getSiteLocation(ids) {
            var locationOpt = new SelectOption();
            locationOpt.url = 'selectget.php';
            locationOpt.targetId = 'locationid';
            locationOpt.id = ids;
            locationOpt.f = 'location';
            locationOpt.groupOptions = true;
            locationOpt.get();
            locationOpt.callback = function(d) {
                try {
                    $("#locationid").multiselect('destroy');
                } catch(err) {

                }

                $("#locationid").multiselect({
                    checkAllText: 'All Locations',
                    selectedText: "# of # Locations Selected",
                    noneSelectedText: 'Select Location',
                    classes: 'site-multi',
                    minWidth: 335
                });
                $("#locationid").multiselect('uncheckAll');
            };
            var deptOpt = new SelectOption();
            deptOpt.url = 'selectget.php';
            deptOpt.targetId = 'departmentid';
            deptOpt.id = ids;
            deptOpt.f = 'department';
            deptOpt.groupOptions = true;
            deptOpt.get();
            deptOpt.callback = function(d) {
                try {
                    $("#departmentid").multiselect('destroy');
                } catch(err) {

                }

                $("#departmentid").multiselect({
                    checkAllText: 'All Departments',
                    selectedText: "# of # Departments Selected",
                    noneSelectedText: 'Select Department',
                    classes: 'site-multi',
                    minWidth: 335
                });
                $("#departmentid").multiselect('uncheckAll');
            };
            return false;
        }

        $("#siteid").multiselect({
            checkAllText: 'All Sites',
            selectedText: "# of # Sites Selected",
            noneSelectedText: 'Select Sites',
            classes: 'site-multi',
            minWidth: 335,
            checkAll: function(){
                var ids = [];
                $('#siteid').multiselect('getChecked').map(function() {
                    ids.push(this.value);
                });
                if(ids.length > 0) {
                    getSiteLocation(ids);
                } else {
                    disableMultiselect('#locationid');
                    disableMultiselect('#departmentid');
                }
            },
            uncheckAll: function() {
                disableMultiselect('#locationid');
                disableMultiselect('#departmentid');
            }
        }).bind("multiselectclick", function(event, ui){
            var ids = [];
            $('#siteid').multiselect('getChecked').map(function() {
                ids.push(this.value);
            });
            if(ids.length > 0) {
                getSiteLocation(ids);
            } else {
                disableMultiselect('#locationid');
                disableMultiselect('#departmentid');
            }
        });*/
        //*****************************************************************

        $("#siteid").multiselect('uncheckAll');

        $('.del-user').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var userID = this.id.split('_').pop();
            var username = this.name;
            if(!userID) {
                return false;
            }
            if(confirm('You are about to delete user "'+username+'"\n\nProceed with action?')) {
                var form    =   document.createElement('form');
                form.action =   'deluser.php';
                form.method =   'post';
                var input   =   document.createElement('input');
                input.type  =   'hidden';
                input.name  =   'id';
                input.value =   userID;
                form.appendChild(input);
                return form.submit();
            }
            return false;
        });

        if($('.alert').length > 0) {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 2000);
        }
    });
    </script>
</body>
</html>
<?php
if($stmt) {
    $stmt->close();
}
$mysqli->close();
