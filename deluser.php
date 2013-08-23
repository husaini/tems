<?php
    require(dirname(__FILE__).'/includes/checklogged.php');
    require(dirname(__FILE__).'/includes/checkadmin.php');
    require(dirname(__FILE__).'/includes/conn.php');
    require(dirname(__FILE__).'/includes/sharedfunc.php');

    $redirect_url   =   'user.php?tab=tablist#tablist';

    if(!$_POST) {
        header("Location: $redirect_url");
        exit();
    }

    $id     =   (isset($_POST['id']) && is_numeric($_POST['id'])) ? intval($_POST['id'], 10) : null;

    if ($id == 0)
    {
        header("Location: $redirect_url");
        exit();
    }

    if(!$id) {
        setSession('error', 'No user to delete.');
        header("Location: $redirect_url");
        exit();
    }

    if($_SESSION['uid'] == $id) {
        setSession('error', 'Sorry, you can\'t delete your own account.');
        header("Location: $redirect_url");
        exit();
    }



    //Make sure user exist
    $result =   $mysqli->query('SELECT * FROM `user` WHERE id = '.$id);

    if($result) {
        $user   =   $result->fetch_assoc();
        mysqli_free_result($result);

        $result =   $mysqli->query('DELETE FROM `user` WHERE id='.$id) or die(mysqli_error($mysqli));
        $num    =   mysqli_affected_rows($mysqli);
        if($num) {
            // delete user access
            $mysqli->query('DELETE FROM user_access WHERE uid = '.$id);
            setSession('user_deleted', 1);
            setSession('deleted_user', $user['name']);
        }
        header("Location: $redirect_url");
        exit();
    }
