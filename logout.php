<?php
session_start();
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
   setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();
?>
<html>
<head>
<title>TEMS: Logout</title>
<link rel="stylesheet" href="default.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript">
function redir(strUrl, iTime) {
	var version = parseInt(navigator.appVersion)
	if (version >= 4 || window.location.replace)
		setTimeout("top.location.replace('" + strUrl + "')", iTime);
	else
		setTimeout("top.location.href = '" + strUrl + "'", iTime);
}
</script>
</head>
<body onload="redir('login.php', 500)">
<b>Session Expired</b><br><br>
The browser will transfer you to the main screen.
</body>
</html>
