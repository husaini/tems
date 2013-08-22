<?php
$mysqli = new mysqli('localhost', 'temsuser', '3zyPzy', 'tems');
if (mysqli_connect_errno()) {
    printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
    exit;
}
?>
