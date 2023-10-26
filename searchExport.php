<?php
session_start();
include_once("class.db.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
include_once("class.record.php");
include_once("class.forms.php");

$DB = new database();
$DB->connect();
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);
$RECORD = new Record($DB);
$FORMS = new Forms($DB);
//print_r($_POST);
if($SESSION->validToken($_POST['kiss_token'])) {
	print_r($_POST);
	$sql = "SELECT * FROM `Groups` WHERE `Groups_id`='".$_POST['SID']."'";
	//echo $sql."<br>\n";
	$snd = $DB->get_single_result($sql);
	$EXPORT_FILE = 'export.'.$snd['Groups_name'].'.csv';
	
	$fields = json_decode($snd['Groups_fields'], true);
	//print_r($fields);
	$topCell[] = 'KISS_ID';
	$topCell[] = 'First';
	$topCell[] = 'Last';
	$topCell[] = 'City';
	$topCell[] = 'State';
	foreach($fields as $field):
		$topCell[] = $field['label'];
	endforeach;
	$row[] = $topCell;
	
	$base_sql = stripslashes($snd['Groups_baseQuery'].' GROUP BY Persons.Person_id');
	$base_snd = $DB->get_multi_result($base_sql);
	foreach($base_snd as $base_dta):
		$rowCell[] = $base_dta['Person_id'];
		$rowCell[] = $base_dta['FirstName'];
		$rowCell[] = $base_dta['LastName'];
		$rowCell[] = $base_dta['City'];
		$rowCell[] = $base_dta['State'];
		foreach($fields as $field):
			//$rowCell[] = $base_dta[$field['field']];
			if($field['field'] == 'PersonsImages_path') {
				if($base_dta[$field['field']] == '') {
					$rowCell[] = $RECORD->get_defaultImage($dta['Person_id']);
				} else {
					$rowCell[] = "/client_media/".$RECORD->get_image_directory($dta['Person_id'])."/".$dta['Person_id']."/".$dta[$fieldName];
				}
			} elseif($field['field'] == 'DateOfBirth') {
				$from 		= new DateTime(date("Y-m-d", $base_dta[$field['field']]));
				$to   		= new DateTime('today');
				$age 		= $from->diff($to)->y;
				$rowCell[] 	= $age;				
			} else {
				$rowCell[] = $base_dta[$field['field']];
			}
		endforeach;
		$row[] = $rowCell;
		unset($rowCell);
	endforeach;
	//print_r($row);
		
	
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment;filename='.$EXPORT_FILE);
	$fp = fopen('php://output', 'w');
	//$fp = fopen('file.csv', 'w');
	foreach ($row as $line) {
		fputcsv($fp, $line);
	}
	fclose($fp);	
}


?>