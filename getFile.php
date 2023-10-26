<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");

$DB = new database();
$DB->connect();
//print_r($_GET);
$RECORD = new Record($DB);

if (isset($_SESSION['system_user_id'])) {
	$f_query = "SELECT * FROM PersonsDocuments WHERE PersonsDocuments_id='".$_GET['DID']."'";
	$f_data = $DB->get_single_result($f_query);
	$FileName = $f_data['PersonsDocuments_path'];
	$ImageDir = $RECORD->get_image_directory($f_data['Persons_Person_id']);
	$filePath = "./client_media/".$ImageDir."/".$f_data['Persons_Person_id']."/".$FileName;
	header("Cache-Control: cache, must-revalidate");
	header("Pragma: public");
	header("Expires: 0");
	header("Content-type: application/octet-stream; name=\"" . $FileName . "\"");
	header("Content-Disposition: attachment; filename=\"" . str_replace("\"", "\\\"", $FileName). "\"");
	//header("Content-Length: " . filesize($f_data[FilePath]));
	readfile($filePath);
}
else {
	$Message = "Unable to to load file... your session has expitred.";
	include ("notice.html");
}