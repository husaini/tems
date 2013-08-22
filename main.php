<html>
<head>
<title>TEMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="css/style.css" type="text/css">
<script type="text/javascript">
    function resizeFrame() {
        var frame = document.getElementById('frame');
        var sizerFrame = document.getElementById('sizer');
        var frameTopPercent = (86/window.innerHeight) * 100;
        var bottomFramePercent = (60/window.innerHeight) * 100;
        var sizerWidthPercent = (194/window.innerWidth) * 100;
        frame.rows = frameTopPercent + ',' + (100 - frameTopPercent - bottomFramePercent) + ',' + bottomFramePercent;
        sizerFrame.cols = sizerWidthPercent + ','+ (100 - sizerWidthPercent);
    }
    window.onload = resizeFrame;
    window.onresize = resizeFrame;
</script>
</head>
<frameset id="frame" rows="18,*,100" border="0">
    <frame allowTransparency="true" name="frame_top" id="top_frame" scrolling="no" noresize target="right-frame" src="top.php">
    <frameset id="sizer" cols="194, *">
        <frame allowTransparency="true" name="frame_left" id="left_frame" target="frame_right" src="navbar.php" scrolling="auto" noresize>
        <frame allowTransparency="true" name="frame_right" id="right_frame" src="dashboard.php">
    </frameset>
    <frame allowTransparency="true" name="frame_bottom" id="bottom_frame" scrolling="no" framescrolling="no" noresize target="right-frame" src="notification.php">
    <noframes>
        <body>
            <p>This page uses frames, but your browser, well, is not human.</p>
        </body>
    </noframes>
</frameset>

</html>
