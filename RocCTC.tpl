<html>
<head>
    <meta charset="utf-8">
    
    <!-- Configure Android webapp -->
    <meta name="mobile-web-app-capable" content="yes">
 	<link rel="icon" sizes="192x192" href="img/switch-left.png">    
   
    <!-- Configure IOS webapp -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="RoCTC">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" href="img/switch-left.png"/>
    
    <title>##PANEL_TITLE##</title>
    
    <link rel="stylesheet" href="jquery/jquery.mobile-1.4.4.min.css" />
    <link rel="stylesheet" href="RocCTC.css" />
    
    <script src="jquery/jquery-2.1.1.min.js"></script>
    <script src="jquery/jquery.mobile-1.4.4.min.js"></script>
    
    <script>var defaultPanelID= '##DEFAULT_PANEL_ID##';</script>
    <script src="RocCTC.js"></script>   

</head>
<body>

<div data-role="page" id="index" data-theme="b" data-cache="never">
   <!--<div data-role="header"><h1>##PANEL_TITLE##</h1></div>-->
    <div data-role="navbar">
    ##PANELS_LIST##
    </div>

    
    <div data-role="content">
        <div id="panelPicture"><img src="img/##PANEL_PICTURE##"></div>
        <div id="switchesGrid">##SWITCHES_GRID##</div>
    </div>
    <div class="info">info</div>
 
</div>    
</body>
</html>