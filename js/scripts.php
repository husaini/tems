<?php
    $js_array    =    array (
        'bootstrap.min.js',
        'FusionCharts.js',
        'dashboard.js'
    );
    $modified   =   0;

    foreach ($js_array as $js)
    {
        $file    =    dirname(__FILE__)."$js.js";
        if (file_exists($file))
        {
            $age = filemtime($file);
            if($age > $modified)
            {
                $modified = $age;
                break;
            }
        }
    }

    $offset = 60 * 60 * 24 * 7; // Cache for 1 weeks
    header ('Expires: ' . gmdate ("D, d M Y H:i:s", time() + $offset) . ' GMT');

    //dev mode set true
    $modified   =   true;

    //if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified)
    if(!$modified)
    {
        header("HTTP/1.0 304 Not Modified");
        header ('Cache-Control:');
    }
    else
    {
        header ('Cache-Control: max-age=' . $offset);
        header ('Content-type: text/javascript; charset=UTF-8');
        header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $modified )." GMT");

        if(extension_loaded('zlib'))
        {
            ob_start('ob_gzhandler');
        }

        foreach ($js_array as $js)
        {
            $file    =    dirname(__FILE__).'/'.$js;
            if (file_exists($file))
            {
                include($file);
            }
        }

        if(extension_loaded('zlib'))
        {
            ob_end_flush();
        }
    }
