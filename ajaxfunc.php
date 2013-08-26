<?php
require_once(dirname(__FILE__).'/includes/checklogged.php');
require_once(dirname(__FILE__).'/includes/conn.php');
require_once(dirname(__FILE__).'/includes/sharedfunc.php');

$op     =   isset($_GET['op']) ? $_GET['op'] : '';
$value  =   isset($_REQUEST['value']) ? $_REQUEST['value'] : '';

switch ($op)
{
    case 'assetno':
        $response   =   array('exist' => 0);

        if (isAssetExist($value))
        {
            $response['exist']  =   1;
        }
        exit(json_encode($response));
        break;
}

exit(json_encode(0));
