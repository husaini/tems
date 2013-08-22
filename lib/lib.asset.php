<?php

function asset_get_asset_status()
{
    global $mysqli;
    $sql    =   'SELECT '.
                    'a.* '.
                'FROM '.
                    '`asset_status` '.
                'ORDER BY `name`';

    $result =   $mysqli->query($sql) or die(mysqli_error($mysqli) .'<hr>'.$sql);
    $rows   =   array();

    if ($result)
    {
        while ($row = $result->fetch_assoc())
        {
            $rows[] =   $row;
        }
        mysqli_free_result($result);
    }
    return $rows;
}
