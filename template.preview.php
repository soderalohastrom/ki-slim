<?php
session_start();
include_once("class.db.php");
include_once("class.templates.php");
$DB = new database();
$DB->connect();
$template_obj = new Templates();
$template_data = $template_obj->get_template_data($_GET['TemplateID']);
$thumbnail_mode = ( $_GET['thumbnail'] == '1' ? true : false );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<style type="text/css">
	<?php if($thumbnail_mode) { ?>
		body {
		width:100%;
		margin:0;
		padding:0;
		cursor:pointer;
		
		-moz-transform: scale(0.30, 0.30); 
		-webkit-transform: scale(0.30, 0.30); 
		-o-transform: scale(0.30, 0.30);
		-ms-transform: scale(0.30, 0.30);
		transform: scale(0.30, 0.30); 
		-moz-transform-origin: top left;
		-webkit-transform-origin: top left;
		-o-transform-origin: top left;
		-ms-transform-origin: top left;
		transform-origin: top left;
		
		 /* IE8+ - must be on one line, unfortunately */ 
	   -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.30, M12=0, M21=0, M22=0.30, SizingMethod='auto expand')";
	   
	   /* IE6 and 7 */ 
	   filter: progid:DXImageTransform.Microsoft.Matrix(
				M11=0.30,
				M12=0,
				M21=0,
				M22=0.30,
				SizingMethod='auto expand');
	   }
	<?php } ?>
	</style>
  </head>
  <body<?php echo ( $thumbnail_mode ? ' onclick="window.open(\'./template.preview.php?TemplateID='.$_GET['TemplateID'].'\',\'Preview\',\'height=800,width=800,scrollbars=yes\');"' : '' )?>>
	<?php if($thumbnail_mode) { ?>
	<div style="margin-left:30px;">
		<?php echo $template_data['EmailTemplates_bodyHTML']?>
	</div>
	<?php } else {
		echo $template_data['EmailTemplates_bodyHTML'];
	} ?>
  </body>
</html>