<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.marketing.php");

$DB = new database();
$DB->connect();

$SETTINGS = new Settings();
$MKG = new Marketing();

$ver = ($_GET['ver'] == 'text' ? 'text' : 'html');
$web_content = $MKG->get_web_content($_GET['id'], $_GET['pid'], $ver);
$deploy_id = $_GET['id'];
?>
<html>
<head>
<title><?php echo $web_content['subject']?></title>
<style type="text/css">
body {
	margin:0;
	padding:20px 16px;
	font-family: sans-serif, Arial, Verdana, "Trebuchet MS";
	font-size: 12px;
	line-height: 16px;
	color: #333;
}

a,
a:focus,
a:hover,
a:active,
a:visited {
	color: #0782C1;
	text-decoration:underline;
}

hr {
	border: 0px;
	border-top: 1px solid #ccc;
}
.hidetr { 
	display: none;
}
</style>
<?php echo $web_content['css']?>
</head>
<body>
<?php echo $web_content['body']?>
</body>
</html>