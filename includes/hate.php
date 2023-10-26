<!DOCTYPE html>
<html>
<head>
  <title>Full-Screen iFrame Example</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
    }

    #iframe-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }

    #fullscreen-iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
  </style>
</head>
<body>
  <div id="iframe-container">
    <iframe id="fullscreen-iframe" src="amore.php"></iframe>
  </div>
</body>
</html>
