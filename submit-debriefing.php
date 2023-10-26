<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.kissphpmailer.php");
include_once("class.marketing.php");
include_once("class.matching.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$MARKETING = new Marketing();
$MATCH = new Matching($DB, $RECORD);

//print_r($_POST);

$d_query = "SELECT * FROM PersonsDates WHERE PersonsDates_id='".$_POST['DateID']."'";
$d_data = $DB->get_single_result($d_query);

if($d_data['PersonsDates_participant_1'] == $_POST['PersonID']) {
	$introWith = $d_data['PersonsDates_participant_2'];
	$myStatus = $d_data['PersonsDates_participant_1_status'];
	$debriefField = 'PersonsDates_participant_1_debrief';
	$scoreField = 'PersonsDates_participant_1_rank';
	$statusField = 'PersonsDates_participant_1_status';
} else {
	$introWith = $d_data['PersonsDates_participant_1'];
	$myStatus = $d_data['PersonsDates_participant_2_status'];
	$debriefField = 'PersonsDates_participant_2_debrief';
	$scoreField = 'PersonsDates_participant_2_rank';
	$statusField = 'PersonsDates_participant_2_status';
}

ob_start();
$debriefTextVersion = "Date: ".date("m/d/y")."\n";
$debriefTextVersion .= "Name: ".$RECORD->get_personName($_POST['PersonID'])."\n";
$debriefTextVersion .= "Intro to: ".$RECORD->get_personName($introWith)."\n";
?>
<div style="margin-bottom:15px;"><strong>Date:</strong>&nbsp;<?php echo date("m/d/y")?></div>
<div style="margin-bottom:15px;"><strong>Name:</strong>&nbsp;<?php echo $RECORD->get_personName($_POST['PersonID'])?></div>
<div style="margin-bottom:15px;"><strong>Intro To:</strong>&nbsp;<?php echo $RECORD->get_personName($introWith)?></div>
<?php
$deb_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='15' AND Questions_active='1' ORDER BY Questions_order ASC";
//echo $deb_sql;
$deb_snd = $DB->get_multi_result($deb_sql);
foreach($deb_snd as $deb_dta):
	$responseField = 'debrief_'.$deb_dta['Questions_id'];
	?><div style="margin-bottom:10px;"><strong><?php echo $deb_dta['Questions_text']?></strong>&nbsp;<?php echo ((is_array($_POST[$responseField])? implode("|", $_POST[$responseField]):$_POST[$responseField]))?></div><?php
	$debriefTextVersion .= $deb_dta['Questions_text'].": ".((is_array($_POST[$responseField])? implode("|", $_POST[$responseField]):$_POST[$responseField]))."\n";
endforeach;
$debriefBody = ob_get_clean();

$upd_sql = "UPDATE PersonsDates SET $debriefField='".$DB->mysqli->escape_string($debriefBody)."', $scoreField='".$_POST['debrief_1733']."', $statusField='99' WHERE PersonsDates_id='".$_POST['DateID']."'";
//echo "\n".$upd_sql."\n";
$upd_send = $DB->mysqli->query($upd_sql);
//log_date_action($_POST['DateID'], "Person Status Change &gt; ".get_date_status(4), $_POST['PersonID']);
$LoggedAction = $RECORD->get_personName($_POST['PersonID'])." Status Change &gt; Completed";
$MATCH->log_date_action($_POST['DateID'], $LoggedAction, 0);

$d_query = "SELECT * FROM PersonsDates WHERE PersonsDates_id='".$_POST['DateID']."'";
$d_data = $DB->get_single_result($d_query);

if(($d_data['PersonsDates_participant_1_status'] == 99) && ($d_data['PersonsDates_participant_2_status'] == 99)) {
	$upd_d_sql = "UPDATE PersonsDates SET PersonsDates_status='99' WHERE PersonsDates_id='".$_POST['DateID']."'";
	$upd_d_snd = $DB->mysqli->query($upd_d_sql);	
}


// EMAIL NOTIFICATION //
$email_body = "
------------------------------- THIS IS AN AUTOMATE EMAIL ------------------------

Matchmaker,
This is a notification that one of your daters ".$RECORD->get_personName($_POST['PersonID'])."(".$_POST['PersonID'].") has completed his/her debriefing for one of their introductions.

".$debriefTextVersion."

Person Record: http://".$_SERVER['HTTP_HOST']."/profile/".$_POST['PersonID']."
Date Record: http://".$_SERVER['HTTP_HOST']."/intro/".$_POST['DateID']."

KISS System

";	
//echo "MAILTO:".$mm_email."\n";
$mail = new KissPHPMailer();
$mail->IsHTML(false);
$mail->From = 'no-reply@kelleher-international.com';
$mail->FromName = 'Kelleher International KISS System';
$mail->Subject = 'Introduction Debriefing Submitted ('.$_POST['DateID'].')';
$mail->Body = $email_body;
//$mail->AddAddress('matt@kelleher-international.com');
$mail->AddAddress($RECORD->get_userEmail($d_data['PersonsDates_assignedTo']));
$mail->Send();

$LoggedAction = $RECORD->get_personName($_POST['PersonID'])." Submitted Debriefing";
$MATCH->log_date_action($_POST['DateID'], $LoggedAction, 0);	

// CLEAN OUT OLD LINKS //
$del_sql = "DELETE FROM DateLinks WHERE Expiration < '".(time() - 604800)."'";
//$del_send = $DB->mysqli->query($del_sql);

?>
<meta http-equiv="refresh" content="0;url=view-debriefing.php?id=<?php echo $_POST['formID']?>" />