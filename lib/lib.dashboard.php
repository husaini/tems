<?php
function dashboard_get_pending($sum_total=false)
{
    global $mysqli;

    $sid        =   get_flash('sid');
    $rem        =   get_flash('rem');
    $siteada    =   '';

    if ($sid > 0 && $sid != 65535)
    {
        $siteada = ' AND asset.siteid = ' . $sid;
    }
    elseif ($sid == 65535)
    {
        $siteada = ' AND asset.siteid IN ("'.$rem.'")';
    }

    $base_sql   =   'SELECT '.
                        'COUNT(1) '.
                    'FROM '.
                        '`workorder` '.
                    'JOIN `asset` '.
                        'ON asset.id = workorder.assetid '.
                    'WHERE '.
                        'workorder.status = 1 ';

    $base_sql2  =   'SELECT '.
                        'COUNT(1) '.
                    'FROM '.
                        '`asset` '.
                    'WHERE '.
                        '`status` <> 4 ';

    $sql        =   'SELECT '.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 0 '.
                            'AND '.
                                'DATEDIFF(NOW(), required) <= 7 '.
                            $siteada.' '.
                        ') AS workorder_week_1, '.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 7 '.
                            'AND '.
                                'DATEDIFF(NOW(), required) <= 14 '.
                            $siteada.' '.
                        ') AS workorder_week_2, '.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 14 '.
                            'AND '.
                                'DATEDIFF(NOW(), required) <= 30 '.
                            $siteada.' '.
                        ') AS workorder_week_3, '.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 30 '.
                            'AND '.
                                'DATEDIFF(NOW(), required) <= 60 '.
                            $siteada.' '.
                        ') AS workorder_month_1,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 60 '.
                            'AND '.
                                'DATEDIFF(NOW(), required) <= 120 '.
                            $siteada.' '.
                        ') AS workorder_month_2,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(NOW(), required) > 120 '.
                            $siteada.' '.
                        ') AS workorder_month_3,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 0 '.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) <= 7 '.
                            $siteada.' '.
                        ') AS ppm_week_1, '.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 7 '.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) <= 14 '.
                            $siteada.' '.
                        ') AS ppm_week_2, '.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 14 '.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) <= 30 '.
                            $siteada.' '.
                        ') AS ppm_week_3,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 30 '.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) <= 60 '.
                            $siteada.' '.
                        ') AS ppm_month_1,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 60 '.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) <= 120 '.
                            $siteada.' '.
                        ') AS ppm_month_2,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(NOW(), ('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH)) > 120 '.
                            $siteada.' '.
                        ') AS ppm_month_3';

    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli) .'<hr>'.$sql);
    $data   =   null;

    if ($result)
    {
        $data  =   $result->fetch_assoc();
        mysqli_free_result($result);

        if ($sum_total === true)
        {
            for ($x=2; $x <= 3; $x++)
            {
                // TEST DATA
                /*
                if(!$data['workorder_week_'.$x])
                {
                    $data['workorder_week_'.$x] =   100;
                }

                if(!$data['ppm_week_'.$x])
                {
                    $data['ppm_week_'.$x] =   400;
                }
                */

                $data['workorder_week_'.$x] +=  $data['workorder_week_'.($x-1)];
                $data['ppm_week_'.$x]       +=  $data['ppm_week_'.($x-1)];
            }

            for ($x=3; $x >= 1; $x--)
            {
                // TEST DATA
                /*
                if(!$data['ppm_month_'.$x])
                {
                    $data['ppm_month_'.$x] =   300;
                }

                if(!$data['workorder_month_'.$x])
                {
                    $data['workorder_month_'.$x] =   160;
                }
                */

                if ($x == 1)
                {
                    $data['workorder_month_1']  +=  $data['workorder_week_3'];
                    $data['ppm_month_1']        +=  $data['ppm_week_3'];
                }
                else
                {
                    $data['workorder_month_'.$x]    +=  $data['workorder_month_'.($x-1)];
                    $data['ppm_month_'.$x]          +=  $data['ppm_month_'.($x-1)];
                }
            }
        }
    }

    //debug($data);

    return $data;
}

function dashboard_get_upcoming($sum_total=false)
{
    global $mysqli;

    $sid        =   get_flash('sid');
    $rem        =   get_flash('rem');
    $siteada    =   '';

    if ($sid > 0 && $sid != 65535)
    {
        $siteada = ' AND asset.siteid = ' . $sid;
    }
    elseif ($sid == 65535)
    {
        $siteada = ' AND asset.siteid IN ("'.$rem.'")';
    }

    $base_sql   =   'SELECT '.
                        'COUNT(1) '.
                    'FROM '.
                        '`workorder` '.
                    'JOIN `asset` '.
                        'ON asset.id = workorder.assetid '.
                    'WHERE '.
                        'workorder.status = 1 ';

    $base_sql2  =   'SELECT '.
                        'COUNT(1) '.
                    'FROM '.
                        '`asset` '.
                    'WHERE '.
                        '`status` <> 4 ';

    $sql        =   'SELECT '.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(required, NOW()) > 0 '.
                            'AND '.
                                'DATEDIFF(required, NOW()) <= 7 '.
                                $siteada.' '.
                        ') AS workorder_week_1,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(required, NOW()) > 7 '.
                            'AND '.
                                'DATEDIFF(required, NOW()) <= 14 '.
                            $siteada. ' '.
                        ') AS workorder_week_2,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(required, NOW()) > 14 '.
                            'AND '.
                                'DATEDIFF(required, NOW()) <= 30 '.
                            $siteada.' '.
                        ') AS workorder_week_3,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(required, NOW()) > 30 '.
                            'AND '.
                                'DATEDIFF(required, NOW()) <= 60 '.
                            $siteada.
                        ') AS workorder_month_1,'.
                        '('.
                            $base_sql.
                            'AND '.
                                'DATEDIFF(required, NOW()) > 60 '.
                            'AND '.
                                'DATEDIFF(required, NOW()) <= 120 '.
                            $siteada.' '.
                        ') AS workorder_month_2,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) > 0 '.
                            'AND '.
                                'DATEDIFF(('.
                                'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) <= 7 '.
                            $siteada.' '.
                        ') AS ppm_week_1,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) > 7 '.
                            'AND '.
                                'DATEDIFF(('.
                                'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) <= 14 '.
                            $siteada.' '.
                        ') AS ppm_week_2,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) > 14 '.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) <= 30 '.
                            $siteada.' '.
                        ') AS ppm_week_3,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) > 30 '.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) <= 60 '.
                            $siteada.' '.
                        ') AS ppm_month_1,'.
                        '('.
                            $base_sql2.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) > 60 '.
                            'AND '.
                                'DATEDIFF(('.
                                    'IF(IFNULL(asset.ppmstart, "") > IFNULL(asset.lastsvc, ""), asset.ppmstart, asset.lastsvc) '.
                                    '+ INTERVAL (12/asset.ppmfreq) MONTH), NOW()) <= 120 '.
                            $siteada.' '.
                        ') AS ppm_month_2';

    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli) .'<hr>'.$sql);
    $data   =   null;

    if ($result)
    {
        $data  =   $result->fetch_assoc();
        mysqli_free_result($result);

        if ($sum_total === true)
        {
            for ($x=2; $x <= 3; $x++)
            {
                // TEST DATA
                /*
                if(!$data['workorder_week_'.$x])
                {
                    $data['workorder_week_'.$x] =   5;
                }

                if(!$data['ppm_week_'.$x])
                {
                    $data['ppm_week_'.$x] =   5;
                }
                */

                $data['workorder_week_'.$x] +=  $data['workorder_week_'.($x-1)];
                $data['ppm_week_'.$x]       +=  $data['ppm_week_'.($x-1)];
            }

            for ($x=2; $x >= 1; $x--)
            {
                // TEST DATA
                /*
                if(!$data['ppm_month_'.$x])
                {
                    $data['ppm_month_'.$x] =   5;
                }

                if(!$data['workorder_month_'.$x])
                {
                    $data['workorder_month_'.$x] =   9;
                }
                */

                if ($x == 1)
                {
                    $data['workorder_month_1']  +=  $data['workorder_week_3'];
                    $data['ppm_month_1']        +=  $data['ppm_week_3'];
                }
                else
                {
                    $data['workorder_month_'.$x]    +=  $data['workorder_month_'.($x-1)];
                    $data['ppm_month_'.$x]          +=  $data['ppm_month_'.($x-1)];
                }
            }
        }
    }
    return $data;
}
