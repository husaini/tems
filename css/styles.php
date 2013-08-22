<?php

    function compress($buffer) {
        /* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        return $buffer;
    }

    $css_array  =   array (
        'bootstrap.min.css',
        'style.css',
    );


    $modified   =   0;
    foreach ($css_array as $css)
    {
        $file    =    dirname(__FILE__)."/$css";
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

    $offset     =   60 * 60 * 24 * 7; // Cache for 1 weeks
    header ('Expires: ' . gmdate ("D, d M Y H:i:s", time() + $offset) . ' GMT');

    #Dev
    $modified = true;

    #if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified)
    if(!$modified)
    {
        header("HTTP/1.0 304 Not Modified");
        header ('Cache-Control:');
    }
    else
    {
        header ('Cache-Control: max-age=' . $offset);
        header ('Content-type: text/css; charset=UTF-8');
        header ('Pragma:');
        header ("Last-Modified: ".gmdate("D, d M Y H:i:s", $modified )." GMT");

        if(extension_loaded('zlib'))
        {
            ob_start('ob_gzhandler');
        }

        foreach ($css_array as $css)
        {
            $file    =    dirname(__FILE__).'/'.$css;
            if (file_exists($file))
            {
                if(strpos(basename($file),'.min.')===false)
                {
                    //compress files that aren't minified
                    ob_start("compress");
                    include($file);
                    ob_end_flush();
                }
                else
                {
                    include($file);
                }
            }
        }
        if(extension_loaded('zlib'))
        {
            ob_end_flush();
        }
    }
