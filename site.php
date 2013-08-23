<?php
require(dirname(__FILE__).'/includes/checklogged.php');
require(dirname(__FILE__).'/includes/conn.php');
require(dirname(__FILE__).'/includes/sharedfunc.php');

date_default_timezone_set("Asia/Kuala_Lumpur");

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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Sites & Locations</title>
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<link rel="stylesheet" href="datatables/css/demo_table_jui.css" type="text/css" media="screen">
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
    if($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 1500);
    }
    $('.del-link').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id') || null;
        if(id) {
            var message = 'You are about to delete this item and all its related data. Are you sure you want to do this?';
            if(confirm(message)) {
                var form = document.createElement('form');
                form.name = 'frmDeleteSite';
                form.method='post';
                form.action = 'delsite.php?id='+id;
                var i = document.createElement('input');
                i.name = 'id';
                i.value = id;
                i.type='hidden';
                form.appendChild(i);
                document.body.appendChild(form);
                frmDeleteSite.submit();
            }
        }
        return false;
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
            <h1 class="page-title">Sites</h1>
            <ul>
                <li><a href="#tablist">List</a></li>
                <li><a href="#tabnew">New</a></li>
            </ul>
            <div class="clear">&nbsp;</div>
            <?php if(getSession('site_added', true)): ?>
                <p class="alert alert-success">Site was successfully added.</p>
            <?php endif; ?>
            <?php if(getSession('site_deleted')): ?>
                <p class="alert alert-success"><?php echo getSession('site_deleted', true);?></p>
            <?php endif; ?>
            <div id="tablist">
                <h3>Site List</h3>
                <table class="tlist full-width">
                    <thead>
                        <tr>
                            <th>Site Name</th>
                            <th>Address</th>
                            <th>Phone No</th>
                            <th>Fax No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $clause =   '';
                            if ($usite_ids) {
                                $clause .=  'AND `id` IN('.implode(',', $usite_ids).') ';
                            }
                            $sql    =   'SELECT '.
                                            '`id`, '.
                                            '`name`, '.
                                            '`address`, '.
                                            '`phone`, '.
                                            '`fax` '.
                                        'FROM '.
                                            '`site` '.
                                        'WHERE 1 '.
                                            $clause.
                                        'ORDER BY'.
                                            '`name`';

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
                                    <?php if (!isguest()): ?>
                                        <tr>
                                            <td>
                                                <a href="editsite.php?id=<?php echo $results['id'];?>"><?php echo $results['name'];?></a>
                                            </td>
                                    <?php else: ?>
                                        <tr>
                                            <td><?php echo $results['name'];?></td>
                                    <?php endif; ?>
                                        <td><?php echo $results['address'];?></td>
                                        <td><?php echo $results['phone'];?></td>
                                        <td><?php echo $results['fax'];?></td>
                                        <td class="center"><a data-id="<?php echo $results['id'];?>" href="delsite.php?id=<?php echo $results['id'];?>" class="del-link">Delete</a></td>
                                    </tr>
                                <?php endwhile; ?>

                            <?php else: ?>
                                <tr>
                                    <td align="center" colspan="5">No sites found.</td>
                                </tr>
                            <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!isguest()): ?>
                <div id="tabnew">
                    <h3>Add New Site</h3>
                    <form method="post" action="mod.php">
                        <input type="hidden" name="func" value="add_site" />
                        <table class="full-width no-border">
                            <tr>
                                <td>Site Name <span class="required">*</span></td>
                                <td>
                                    <input required="required" type="text" name="sname" size="60" maxlength="100" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td>Phone No</td>
                                <td>
                                    <input type="text" name="sphone" size="30" maxlength="20" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td>Fax No</td>
                                <td>
                                    <input type="text" name="sfax" size="30" maxlength="20" value="" />
                                </td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td>
                                    <textarea name="saddr" rows="2" cols="55"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center">
                                    <input type="submit" value="Submit Data" class="btn btn-primary" />
                                </td>
                            </tr>
                        </table>
                    </form>
                    <form id="frmhidden" action="mod.php" method="post">
                        <input type="hidden" name="func" value="del_site">
                        <input type="hidden" name="sid">
                    </form>
                    <p>
                        <span class="required">*</span> Mandatory Field
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
