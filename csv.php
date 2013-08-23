<?php
require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');
require_once(dirname(__FILE__).'/includes/csvfunc.php');

$data       =   null;
$error      =   false;
$upload_dir =   dirname(__FILE__).'/upload';
$allowed_extensions =   array('xls','xlsx','csv','ods');
$success    =   getSession('success', true);

if ($_FILES)
{

    $pathinfo   =   pathinfo($_FILES['csv']['name']);
    if(!in_array($pathinfo['extension'], $allowed_extensions))
    {
        die('File is not allowed!');
    }
    $show_empty_serial  =   false;
    if(isset($_POST['show_empty_serial']))
    {
        $show_empty_serial  =   true;
    }
    $data   =   csv_load($_FILES['csv'], $show_empty_serial);

    //copy to temp file
    $file       =   'upload_temp.'.$pathinfo['extension'];
    if(file_exists($upload_dir.'/'.$file) && is_file($upload_dir.'/'.$file))
    {
        @unlink($upload_dir.'/'.$file);
    }
    //move_uploaded_file($_FILES['csv']['tmp_name'], $upload_dir.'/'.$file);
}
else
{
    //try to load from upload temp, most probably this will be used after form post
    foreach ($allowed_extensions as $ext)
    {
        $temp_file  =   $upload_dir.'/upload_temp.'.$ext;

        if(file_exists($temp_file) && is_file($temp_file))
        {
            //check last the time, if too old(more than 30 mins), do not load, instead delete it!

            if(filemtime($temp_file) < (time() - 60 * 30 ))
            {
                @unlink($temp_file);
            }
            else
            {
                $data   =   csv_load($temp_file, true, true, false);
            }
            break;
        }
    }
}

if ($_POST)
{
    if (isset($_POST['sites']) && $_POST['sites'])
    {
        $asset_to_add   =   array();
        $serials        =   array();
        $duplicated     =   array();

        //debug($_POST['sites']);

        foreach ($_POST['sites'] as $site_id => $row)
        {
            $assets =   $row['assets'];

            // Loop and check if checkbox checked
            foreach ($assets as $asset)
            {
                $is_duplicated  =   false;

                //if (!isset($asset['add']) || !$asset['add'] || !isset($asset['serialno']) || !$asset['serialno'])
                if (!isset($asset['add']) || !$asset['add'])
                {
                    continue;
                }

                $asset['serialno']  =   trim($asset['serialno']);
                $asset['serialno']  =   preg_replace('/\s+/', '', $asset['serialno']);

                if(!empty($asset['serialno']))
                {
                    // Check for duplicated serial no
                    $is_duplicated      =   (!in_array($asset['serialno'], $serials)) ? false : true;

                    // DB level check for duplicated serial no
                    if (!$is_duplicated)
                    {
                        //only if no duplicate entry in form
                        $is_duplicated  =   isAssetSerialExist($asset['serialno']) ? true : false;
                    }
                }


                //DO NOT add if duplicated, only the first will get saved
                if ($is_duplicated)
                {
                    $duplicated[]   =   $asset;
                    continue;
                }

                //debug($asset['add'], 'asset add');

                //departments && locations for separate add
                $dept   =   trim($asset['department']);
                if ($dept)
                {
                    // Get department id for this asset, add if not yet exist
                    $dept_id    =   csv_add_department($dept, $site_id);
                    if ($dept_id)
                    {
                        //assign dept id to asset
                        $asset['department_id'] =   $dept_id;
                        unset($asset['department']);
                    }
                }

                $loc    =   trim($asset['location']);
                if ($loc)
                {
                    // Get location id for this asset, add if not yet exist
                    $loc_id    =   csv_add_location($loc, $dept_id);
                    if ($loc_id)
                    {
                        //assign dept id to asset
                        $asset['locationid'] =   $loc_id;
                        unset($asset['location']);
                    }
                }

                //manufacturer and model
                // note that, model will require manufacturer!
                // changes 20130313 - ignore manufacturer, they don't have it in data, so just save the model!


                $manu   =   isset($asset['manufacturer']) ? trim($asset['manufacturer']) : null;
                $mod    =   isset($asset['model']) ? trim($asset['model']) : null;

                /*
                if ($manu)
                {
                    // Add manufaturer if needed
                    $origin     =   isset($asset['manufacturer_origin']) ? $asset['manufacturer_origin'] : null;
                    $manu_id    =   csv_add_manufacturer($manu, $origin);

                    if ($manu_id)
                    {
                        // add this value to asset
                        $asset['manuid']    =   $manu_id;
                        unset($asset['manufacturer']);


                        // model table required fields!!
                        $type_id    =   null;
                        $class_id   =   null;

                        if (isset($asset['type']) && isset($asset['class']))
                        {
                            // get asset class first
                            $class_id   =   csv_add_class($asset['class']);

                            if ($class_id)
                            {
                                // add this clas to asset
                                $asset['classid']   =   $class_id;
                                unset($asset['class']);

                                $type_id    =   csv_add_type($asset['type'], $class_id);

                                if ($type_id)
                                {
                                    //add ttype to asset
                                    $asset['typeid']    =   $type_id;
                                    unset($asset['type']);

                                    // NOw that we have the required data, only then model can be saved!

                                    if ($mod)
                                    {
                                        $model_id   =   csv_add_model($mod, $manu_id, $type_id);

                                        if ($model_id)
                                        {
                                            // add this to asset
                                            $asset['modelid']   =   $model_id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                */

                // added 20130313 to allow model save with extra pre-req, function, db structure altred
                if ($mod)
                {
                    $model_id   =   csv_add_model($mod);

                    if ($model_id)
                    {
                        // add this to asset
                        $asset['modelid']   =   $model_id;
                    }
                }

                if(isset($asset['last_service']) && $asset['last_service'])
                {
                    $asset['lastsvc']   =   $asset['last_service'];
                    unset($asset['last_service']);
                }
                if(isset($asset['next_service']) && $asset['next_service'])
                {
                    $asset['nextsvc']   =   $asset['next_service'];
                    unset($asset['next_service']);
                }

                // the user
                $asset['author']    =   getSession('uid');
                $asset['status']    =   1;

                //add to list
                $asset_to_add[] =   $asset;

                // save serial list
                $serials[]  =   $asset['serialno'];
            }
        }

        // Finally we add assets
        if ($asset_to_add)
        {
            foreach ($asset_to_add as $args)
            {
                $result =   csv_add_asset($args);
            }
        }
        else
        {
            $error  =   !$duplicated ? 'Nothing to add. Please select at least an asset to add.' : null;
        }
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>TEMS: Asset</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
<link rel="stylesheet" href="css/jqueryui/jquery-ui-1.9.2.custom.css" type="text/css" media="screen">
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
</head>
<body>
    <div id="body_content">
        <div class="center">
            <form method="post" enctype="multipart/form-data">
                <p class="required">
                    <i class="icon-warning"> </i><strong>If you are having trouble to upload assets, try reducing number of assets to add.</strong>
                </p>
                <input type="file" name="csv">
                <br>
                <label class="">
                    <input type="checkbox" name="show_empty_serial" value="1" checked="checked" /> Include assets with empty serial no.
                </label>
                <p>
                    <input type="submit" class="btn btn-primary">
                </p>

            </form>
        </div>
        <p>&nbsp;</p>
        <?php if($success): ?>
            <p class="alert alert-success">
                <?php echo $success;?>
            </p>
        <?php endif; ?>
        <?php if($data): ?>
            <p class="required">
                <strong>*** TEMS No. is auto-generated sequence base on current number of assets for each site.</strong>
            </p>
            <?php if($error): ?>
            <p class="alert alert-error">
                <?php echo $error;?>
            </p>
            <?php endif; ?>
            <form method="post" id="frmAsset">
            <?php $site_count = 0; foreach ($data as $site_name => $assets): ?>
            <?php
                $site_code    =   $assets[0]['siteid'];
                if($site_code < 10)
                {
                    $site_code  =   '0'.$site_code;
                }
            ?>
                <table class="tems-table full-width">
                    <thead>
                        <tr>
                            <th colspan="10" class="row-heading site clickable">
                                Site : <?php echo $site_name.' '. $site_code;?>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="6" class="row-control left">
                                <i class="arrow-checkbox down white"></i> With checked
                                <button type="submit" class="btn btn-inverse">Add Assets</button>
                            </th>
                            <th colspan="4" class="row-control right">
                                <label><input type="checkbox" id="filter_duplicate"> Show duplicated serial</label>
                            </th>
                        </tr>
                        <tr>
                            <th width="10"><input type="checkbox" class="chk-all" /></th>
                            <th>No.</th>
                            <th>TEMS No.</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Serial No.</th>
                            <th>Last Service</th>
                            <th>Next Service</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $row_count = 0; foreach ($assets as $asset): ?>
                        <?php
                            $zebra_class    =   ($row_count % 2 == 0) ? 'row-odd' : 'row-even';
                            $site_id        =   $asset['siteid'];
                        ?>

                        <?php if ($asset['tems_no_exist']): ?>
                            <tr class="<?php echo $zebra_class;?> row-green">
                                <td>&nbsp;</td>
                                <td><?php echo $row_count + 1;?>.</td>
                                <td nowrap="nowrap">
                                    <?php echo $asset['tems_no'];?>
                                </td>
                                <td><?php echo $asset['department'];?></td>
                                <td><?php echo $asset['location'];?></td>
                                <td><?php echo $asset['item'];?></td>
                                <td><?php echo $asset['model'];?></td>
                                <td><?php echo $asset['serial_no'];?></td>
                                <td><?php echo $asset['last_service'];?></td>
                                <td><?php echo $asset['next_service'];?></td>
                            </tr>
                        <?php else: ?>
                            <tr class="<?php echo $zebra_class;?><?php echo ($asset['duplicated']) ? ' row-error row-duplicated':'';?>">
                                <td>
                                    <input type="checkbox" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][add]" value="1" class="chk-any" />
                                    <input type="hidden" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][siteid]" value="<?php echo $site_id;?>" />
                                    <input type="hidden" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][assetno]" value="<?php echo $asset['tems_no'];?>" />
                                </td>
                                <td><?php echo $row_count + 1;?>.</td>
                                <td nowrap="nowrap">
                                    <?php //echo $site_id.'-'.array_pop(explode('-', $asset['tems_no']));?>
                                    <?php
                                        echo $asset['tems_no'];
                                    ?>
                                </td>
                                <td>
                                    <input type="text" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][department]" value="<?php echo $asset['department'];?>" />
                                </td>
                                <td>
                                    <input type="text" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][location]" value="<?php echo $asset['location'];?>" />
                                </td>
                                <td>
                                    <input type="text" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][remarks]" value="<?php echo $asset['item'];?>" class="auto-width" />
                                </td>
                                <td>
                                    <input rel="modellist" type="text" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][model]" value="<?php echo $asset['model'];?>" class="autocomplete" />
                                </td>
                                <td>
                                    <input type="text" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][serialno]" value="<?php echo $asset['serial_no'];?>" />
                                    <?php if($asset['duplicated']): ?>
                                    <span class="icon-warning" title="Duplicated or serial number already exist in database."></span>
                                    <?php endif;?>
                                </td>
                                <td>
                                    <input type="hidden" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][last_service]" id="sites_<?php echo $site_id;?>_<?php echo $row_count;?>_last_service_date_db" value="" />
                                    <input placeholder="Pick a date..." type="text" id="sites_<?php echo $site_id;?>_<?php echo $row_count;?>_last_service_date" value="<?php echo $asset['last_service'];?>" class="dp" />
                                </td>
                                <td>
                                    <input type="hidden" name="sites[<?php echo $site_id;?>][assets][<?php echo $row_count;?>][next_service]" id="sites_<?php echo $site_id;?>_<?php echo $row_count;?>_next_service_date_db" value="" />
                                    <input placeholder="Pick a date..." type="text" id="sites_<?php echo $site_id;?>_<?php echo $row_count;?>_next_service_date" value="<?php echo $asset['next_service'];?>" class="dp" />
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php $row_count++; endforeach;?>
                    </tbody>
                </table>
            <?php $site_count++; endforeach; ?>
            <table class="tems-table full-width">
                <tfoot>
                        <tr>
                            <td>
                                <i class="arrow-checkbox white"></i> With checked
                                <button type="submit" class="btn btn-inverse">Add Assets</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </table>
            </form>
            <script type="text/javascript" src="js/csv.js"></script>
            <script type="text/javascript">
            $(function() {
               $('#filter_duplicate').click(function() {
                   if($(this).is(':checked')) {
                       $('table.tems-table tbody > tr').each(function() {
                           if(!$(this).hasClass('row-duplicated')) {
                               $(this).addClass('row-hidden');
                           }
                       });
                   } else {
                       $('table.tems-table tbody > tr').removeClass('row-hidden');
                   }
               });
            });
            </script>
        <?php endif;?>
    </div>
</body>
</html>
