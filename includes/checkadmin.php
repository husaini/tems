<?php
// this module, well, is to check whether the group accessing the page is an admin or not
// if he/she isn't, it'll throw him/her out of the garden.
// this requires checklogged.php to be loaded first (because this one assumes a session has been started)

if ($_SESSION['gid'] > 50) { 
	header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/search.php");
	exit;
}
?>
