<?php
function ares_get_alert()
{
    global $mysqli;

    $sql_reading    =   'SELECT DISTINCT `mote` FROM `reading`';
    $result_reading =   $mysqli->query($sql_reading) or die(mysqli_error($mysqli) .'<hr>'.$sql_reading);
    $rows           =   array();

    if ($result_reading)
    {
        while ($row_reading = $result_reading->fetch_assoc())
        {
            if ($row_reading['mote'] != 63)
            {
                $id             =   $mysqli->real_escape_string($row_reading['mote']);
                $sql            =   'SELECT '.
                                        'm.* '.
                                    'FROM '.
                                        '`mote` m '.
                                    'WHERE '.
                                        "m.id = '$id' ";

                $result_mote    =   $mysqli->query($sql);
                $row_mote       =   $result_mote->fetch_assoc();
                mysqli_free_result($result_mote);

                if ($row_mote)
                {
                    if (($row_mote['serial'] > $row_mote['temphi']) || ($row_mote['serial'] < $row_mote['templo']))
                    {
                        $rows[] =   $row_mote;
                    }
                }
            }
        }
        mysqli_free_result($result_reading);
    }

    return $rows;
}

function ares_get_mote($id)
{
    global $mysqli;

    if (!$id || !is_numeric($id))
    {
        return;

    }

    $sql    =   'SELECT '.
                    'm.* '.
                'FROM '.
                    '`mote` m '.
                'WHERE '.
                    "m.id = $id ";

    $result =   $mysqli->query($sql);
    $row    =   null;

    if ($result)
    {
        $row    =   $result->fetch_object();
        mysqli_free_result($result);
    }
    return $row;
}

function ares_get_oxygen_level($id,$args=array())
{
    global $mysqli;

    if (!is_array($args))
    {
        $args   =   array();
    }

    if (!$id || !is_numeric($id))
    {
        return;

    }

    $id     =   intval($id, 10);
    $limit  =   (isset($args['limit']) && is_numeric($args['limit'])) ? intval($args['limit'],10) : 10;
    $max    =   0;
    $tag    =   (isset($args['tag']) && $args['tag']) ? $args['tag'] : null;

    // Dates
    $day1   =   (isset($args['dday1']) && is_numeric($args['dday1'])) ? intval($args['dday1'],10) : null;
    $day2   =   (isset($args['dday2']) && is_numeric($args['dday2'])) ? intval($args['dday2'],10) : null;
    $month1 =   (isset($args['dmon1']) && is_numeric($args['dmon1'])) ? intval($args['dmon1'],10) : null;
    $month2 =   (isset($args['dmon2']) && is_numeric($args['dmon2'])) ? intval($args['dmon2'],10) : null;
    $year1  =   (isset($args['dyer1']) && is_numeric($args['dyer1'])) ? intval($args['dyer1'],10) : null;
    $year2  =   (isset($args['dyer2']) && is_numeric($args['dyer2'])) ? intval($args['dyer2'],10) : null;
    $date1  =   null;
    $date2  =   null;


    if ($day1 && $month1 && $year1)
    {
        if ($day1 < 10)
        {
            $day1 =   '0'.$day1;
        }
        if ($month1 < 10)
        {
            $month1 =   '0'.$month1;
        }
        $date1  =   "$year1-$month1-$day1";
    }

    if ($day2 && $month2 && $year2)
    {
        if ($day2 < 10)
        {
            $day2 =   '0'.$day2;
        }
        if ($month2 < 10)
        {
            $month2 =   '0'.$month2;
        }
        $date2  =   "$year2-$month2-$day2";
    }

    $sql   =   "SELECT COUNT(1) FROM `reading` WHERE `mote` = $id ";

    if ($tag)
    {
        if ($date1 && $date2)
        {
            $date1  =   $mysqli->real_escape_string($date1);
            $date2  =   $mysqli->real_escape_string($date2);

            $sql    .=  'AND '.
                            'DATE_FORMAT(`dates`, "Y-m-d") '.
                            "BETWEEN '$date1' AND '$date2' ";

        }
    }

    //$sql    .=   'LIMIT '.$limit;

    $result         =   $mysqli->query($sql) or die(mysqli_error($mysqli) .'<hr>'.$sql);
    list($total)    =   $result->fetch_row();

    $base_sql       =   "SELECT * FROM `reading` WHERE `mote` = $id ";

    if($total > $limit)
    {
        $max    =   $total - 10;
        $result =   $mysqli->query($base_sql."LIMIT $max,$limit");
        $total   =   mysqli_num_rows($result);
    }
    else
    {
        $result =   $mysqli->query($base_sql);
        $total   =   mysqli_num_rows($result);
    }
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
