<?php
function array_to_object($array)
{
    if(!is_array($array))
    {
        return $array;
    }

    $object =   new stdClass();
    if (is_array($array) && count($array) > 0)
    {
      foreach ($array as $name => $value)
      {
         $name = strtolower(trim($name));
         if (!empty($name))
         {
            if(!is_array($value))
            {
                $object->$name = array_to_object($value);
            }
            else
            {
                foreach ($value as $k => $v)
                {
                     $k = strtolower(trim($k));
                     $object->$name->$k =   array_to_object($v);
                }
            }
         }
      }
      return $object;
    }
    else
    {
      return false;
    }
}

# Class autoload, this should be supported by PHP 5++
function __autoload($classname)
{
    $class_path =   (!defined('CLASS_PATH')) ? dirname(__FILE__).'/../class/' : CLASS_PATH;
    $file       =   $class_path.'class.'.strtolower($classname).'.php';

    if(file_exists($file) && is_file($file))
    {
        if(!class_exists($classname))
        {
            require_once($file);
        }
        else
        {
            //throw new Exception('Class "' . $classname . '" could not be autoloaded.');
        }
    }
    else
    {
        //throw new Exception('File not found "' . $file . '"');
    }
}

#Autoloading class in case __autoload is not functioning
function autoload_class($classname)
{
    $class_path =   (!defined('CLASS_PATH')) ? dirname(__FILE__).'/../class/' : CLASS_PATH;
    $file       =   $class_path.'class.'.strtolower($classname).'.php';

    if(file_exists($file) && is_file($file))
    {
        if(!class_exists($classname))
        {
            require_once($file);
        }
        else
        {
            //throw new Exception('Class "' . $classname . '" could not be autoloaded.');
        }
    }
    else
    {
        //throw new Exception('File not found "' . $file . '"');
    }
}

function data_uri($file, $mime)
{
    $contents   =   file_get_contents($file);
    $base64     =   base64_encode($contents);
    return "data:$mime;base64,$base64";
}

function debug($data,$title='')
{
    if(!empty($title)) {
        echo "<h2>".$title."</h2><hr>";
    }
    echo "<div align='left' style='text-align:left;width:auto;float:none;clear:both;white-space:pre;'>";
    print_r($data);
    echo "</div>";
}

function file_extension($filename)
{
    return pathinfo($filename,PATHINFO_EXTENSION);
}

function format_filesize($size)
{
    $filesizename   = array(" Bytes"," KB"," MB"," GB"," TB"," PB"," EB"," ZB"," YB");
    return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}

function get_filename($filepath)
{
    preg_match('/[^?]*/', $filepath, $matches);
    $string = $matches[0];
    #split the string by the literal dot in the filename
    $pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE);
    #get the last dot position
    $lastdot = $pattern[count($pattern)-1][1];
    #now extract the filename using the basename function
    $filename = basename(substr($string, 0, $lastdot-1));
    #return the filename part
    return $filename;
}

function get_flash($key,$delete=false)
{
    if(isset($_SESSION[$key]))
    {
        $data   =   $_SESSION[$key];
        if($delete === true)
        {
            unset($_SESSION[$key]);
        }
        return $data;
    }
}

function is_ajax()
{
    return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_GET['jspost']) && $_GET['jspost'] == 1));
}

function object_to_array($obj)
{
    if(!is_object($obj))
    {
        return $obj;
    }

    $array  =   array();

    if (is_object($obj) && $obj)
    {
        foreach ($obj as $name => $value)
        {
            if(!is_numeric($name))
            {
                $name = strtolower(trim($name));
                 if (!empty($name))
                 {
                    if(!is_object($value))
                    {
                        $array[$name]   =   $value;
                    }
                    else
                    {
                        $array[$name]   =   object_to_array($value);
                    }
                 }
            }
            else
            {
                if(!is_object($value))
                {
                    $array[]   =   $value;
                }
                else
                {
                    $array[]   =   object_to_array($value);
                }
            }
        }
        return $array;
    }
    else
    {
      return false;
    }
}

function paginate($page = 1, $total_items, $limit = 20, $adjacents = 1, $targetpage = '/', $pagestring = '?page=', $prev_str = '&#60;', $next_str = '&#62;')
{
    $adjacents  =   ($adjacents && is_numeric($adjacents))  ? intval($adjacents,10) : 3; // How many adjacent pages should be shown on each side?
    $limit      =   ($limit && is_numeric($limit))          ? intval($limit,10)     : 20;
    $page       =   ($page && is_numeric($page))            ? intval($page,10)      : 1;
    $targetpage =   ($targetpage) ? $targetpage : '/';

    #previous page is page - 1
    $prev       =   $page - 1;

    #next page is page + 1
    $next       =   $page + 1;

    #lastpage is = total items / items per page, rounded up.
    $lastpage   =   ceil($total_items / $limit);

    #last page minus 1
    $lpm1       =   $lastpage - 1;

    $pagination = '';

    if($lastpage > 1)
    {
        $pagination .= "<div class=\"pagination\">";

        //previous button
        if ($page > 1)
        {
            $pagination .= "<a href=\"$targetpage$pagestring$prev\" class=\"prev\">$prev_str</a>";
        }
        else
        {
            $pagination .= "<span class=\"disabled\">$prev_str</span>";
        }

        //pages

        if ($lastpage < 7 + ($adjacents * 2))
        {
            //not enough pages to bother breaking it up
            for ($counter = 1; $counter <= $lastpage; $counter++)
            {
                if ($counter == $page)
                {
                    $pagination .= "<span class=\"current\">$counter</span>";
                }
                else
                {
                    $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . "\" class=\"page-num\">$counter</a>";
                }
            }
        }
        elseif($lastpage >= 7 + ($adjacents * 2))   //enough pages to hide some
        {
            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 3))
            {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                {
                    if ($counter == $page)
                    {
                        $pagination .= "<span class=\"current\">$counter</span>";
                    }
                    else
                    {
                        $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . "\" class=\"page-num\">$counter</a>";
                    }
                }
                $pagination .= "<span class=\"elipses\">...</span>";
                $pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\" class=\"page-num\">$lpm1</a>";
                $pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\" class=\"page-num\">$lastpage</a>";
            }
            //in middle; hide some front and some back
            elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
            {
                $pagination .= "<a href=\"" . $targetpage . $pagestring . "1\" class=\"page-num\">1</a>";
                $pagination .= "<a href=\"" . $targetpage . $pagestring . "2\" class=\"page-num\">2</a>";
                $pagination .= "<span class=\"elipses\">...</span>";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                {
                    if ($counter == $page)
                    {
                        $pagination .= "<span class=\"current\">$counter</span>";
                    }
                    else
                    {
                        $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . "\" class=\"page-num\">$counter</a>";
                    }
                }
                $pagination .= '<span class="elipses">...</span>';
                $pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\" class=\"page-num\">$lpm1</a>";
                $pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\" class=\"page-num\">$lastpage</a>";
            }
            //close to end; only hide early pages
            else
            {
                $pagination .= "<a href=\"" . $targetpage . $pagestring . "1\" class=\"page-num\">1</a>";
                $pagination .= "<a href=\"" . $targetpage . $pagestring . "2\" class=\"page-num\">2</a>";
                $pagination .= "<span class=\"elipses\">...</span>";
                for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++)
                {
                    if ($counter == $page)
                    {
                        $pagination .= "<span class=\"current\">$counter</span>";
                    }
                    else
                    {
                        $pagination .= "<a href=\"" . $targetpage . $pagestring . $counter . "\" class=\"page-num\">$counter</a>";
                    }
                }
            }
        }

        //next button
        if ($page < $counter - 1)
        {
            $pagination .= "<a href=\"" . $targetpage . $pagestring . $next . "\" class=\"next\">$next_str</a>";
        }
        else
        {
            $pagination .= "<span class=\"disabled\">$next_str</span>";
        }
        $pagination .= "</div>\n";
    }
    return $pagination;
}

function recursive_array_search($haystack, $needle, $index = null)
{
    $array_iterator = new RecursiveArrayIterator($haystack);
    $iterator       = new RecursiveIteratorIterator($array_iterator);

    while($iterator->valid())
    {
        if (((isset($index) && ($iterator->key() == $index)) || (!isset($index))) && ($iterator->current() == $needle))
        {
            return $array_iterator->key();
        }

        $iterator->next();
    }

    return false;
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function set_flash($key, $value)
{
    $_SESSION[$key] =   $value;
}

function spaceless($str)
{
    return trim(preg_replace('/>\s+</', '><', $str));
}

function remove_slashes($str) {
    return stripslashes($str);
}

function urlsafe_b64decode($string)
{
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4)
    {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

function urlsafe_b64encode($string)
{
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}
