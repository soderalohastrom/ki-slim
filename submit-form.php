<?php
include_once("class.db.php");
include_once("class.record.php");
include_once("class.forms.php");
include_once("class.settings.php");
include_once("class.marketing.php");
include_once("class.encryption.php");
include_once("class.geocode.php");
include_once("class.kissphpmailer.php");
include_once("./assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
include_once("class.leaddelivery.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$FORMS = new Forms($DB);
//$imgmgk_location = "/usr/local/bin/";
$imgmgk_location = "/usr/bin/";
$ENC = new encryption($DB);

$SETTINGS_OBJ = new Settings();
$SETTINGS = $SETTINGS_OBJ->setting;
date_default_timezone_set($SETTINGS['SERVER_TIMEZONE']);
$marketing = new Marketing();
$GEO = new GeoCode($DB);

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$LDEV = new LDELIVERY($DB, $RECORD, new KissPHPMailer());

// Check for bot
// Honeypot
if (!empty ($_POST['webaddres']) ) { die; }
// Invalid characters
if ( preg_match("/[\^<,\"@\/\{\}\(\)\*\$%\?=>:\|;#]+/i", $_POST['FirstName'])) { die; }
if ( preg_match("/[\^<,\"@\/\{\}\(\)\*\$%\?=>:\|;#]+/i", $_POST['LastName'])) { die; }
// Submission
$number = $_POST['number'];
$time_passed = strtotime(date('H:i:s'))-strtotime($number);	
if ($time_passed < 4) { die; }

/**
 * clean out special characters from a string
 * @param $str = string
*/
function clean_special_characters($str)
{

	$specialCharacters = array(
	'#' => '-',
	'$' => '-',
	'%' => '-',
	'&' => '-',
	'@' => '-',
	'€' => '-',
	'+' => '-',
	'=' => '-',
	'§' => '-',
	'\\' => '-',
	'/' => '-',
	"'" => '-',
	' ' => '_',
	"[" => '-',
	"]" => '-',
	"(" => '-',
	")" => '-'
	);

	foreach($specialCharacters as $character=>$replacement)
	{
		$str = str_replace($character, $replacement, $str);
	}

	return $str;
}

function convertImage($original, $output, $size) {
	//jpg, png, gif, bmp
	$ext = $size['mime'];

	if (preg_match('/jpg|jpeg/i', $ext))
		$imageTemp = imagecreatefromjpeg($original);
	else if (preg_match('/png/i', $ext))
		$imageTemp = imagecreatefrompng($original);
	else if (preg_match('/gif/i', $ext))
		$imageTemp = imagecreatefromgif($original);
	else if (preg_match('/bmp/i', $ext))
		$imageTemp = imagecreatefromwbmp($original);
	else
		return 0;

	$ratio = $size[0]/$size[1]; // width / height
	if ($ratio > 1 ) {
		$width  = 300;
		$heigth = 300 / $ratio;
	} else {
		$width = 300*$ratio;
		$heigth = 300;
	}    

	$resizedImg = imagecreatetruecolor($width, $heigth);
	imagecopyresampled($resizedImg, $imageTemp, 0,0,0,0,$width, $heigth, $size[0], $size[1]);
	
	imagedestroy($imageTemp);
	//starting an output buffer to get the data
	ob_start();
	imagejpeg($resizedImg);
	//here we get the data
	$output = ob_get_clean();
	imagedestroy($resizedImg);
	return 1;
}

ob_start();
$sql = "SELECT * FROM CompanyForms WHERE FormCallString='".$_POST['formID']."'";
$snd = $DB->get_single_result($sql);
if(isset($snd['empty_result'])) {
	?><div class="alert alert-danger">INVALID FORM ID</div><?php	
} else {
	$FORMS->updateFormSubmits($snd['FormID']);
	$qsql = "SELECT * FROM CompanyForms_Fields WHERE FormID='".$snd['FormID']."' ORDER BY QuestionOrder ASC";
	$qdta = $DB->get_multi_result($qsql);
	
	foreach($qdta as $qfield) {
		//print_r($qfield);
		if(strtolower(substr($qfield['QuestionID'], 0, 3)) == 'prq') {
			//echo "PROFILE QUESTION:".$qfield['QuestionID']."|".$_POST[$qfield['QuestionID']]."<br>\n";
			if(is_array($_POST[$qfield['QuestionID']])) {
				$fields['profile'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string(implode("|", $_POST[$qfield['QuestionID']]))."'";
			} else {
				$fields['profile'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string($purifier->purify($_POST[$qfield['QuestionID']]))."'";
			}
		} elseif(strtolower(substr($qfield['QuestionID'], 0, 4)) == 'pref') {
			//echo "PREF QUESTION:".$qfield['QuestionID']."|".$_POST[$qfield['QuestionID']]."<br>\n";
			switch($qfield['QuestionID']) {
				case 'prefQuestion_age_floor':
				$fields['prefs'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string(implode("|", array($_POST['ageRange_value_1'], $_POST['ageRange_value_2'])))."'";				
				break;
				
				default:
				if(is_array($_POST[$qfield['QuestionID']])) {
					$fields['prefs'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string(implode("|", $_POST[$qfield['QuestionID']]))."'";
				} else {
					$fields['prefs'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string($purifier->purify($_POST[$qfield['QuestionID']]))."'";
				}
				break;
			}
		} else {
			//echo "SPECIAL CORE:".$qfield['QuestionID']."|".$_POST[$qfield['QuestionID']]."<br>\n";
			switch($qfield['QuestionID']) {
				case 'DateOfBirth':							
				$dobValue = strtotime($_POST['DateOfBirth_DD'] . '.' . $_POST['DateOfBirth_MM'] . '.' . $_POST['DateOfBirth_YYYY']);
				$fields['core'][] = 'DateOfBirth'."='".date ('Y-m-d H:i:s', $dobValue)."'";
				break;
				
				case 'Photos':			
				$fields['images'] = $_POST['uploadedImages'];
				break;
				
				case 'Phone':
				$fields['phone'] = $purifier->purify($_POST['Phone']);
				break;
				
				case 'Street_1':
				$fields['address']['street'] = $purifier->purify($_POST['Street_1']);
				break;
				
				case 'City':
				$fields['address']['city'] = $purifier->purify($_POST['City']);
				break;
				
				case 'State':
				$fields['address']['state'] = $purifier->purify($_POST['State']);
				break;
				
				case 'Postal':
				$fields['address']['postal'] = $purifier->purify($_POST['Postal']);
				break;
				
				case 'Country':
				$fields['address']['country'] = $purifier->purify($_POST['Country']);
				break;
				
				case 'HearAboutUs':
				$sourceParths = $purifier->purify($_POST['HearAboutUs']);
				$fields['core'][] = "HearAboutUs='".$DB->mysqli->escape_string($sourceParths)."'";
				break;
				
				default;				
				$fields['core'][] = $qfield['QuestionID']."='".$DB->mysqli->escape_string($purifier->purify($_POST[$qfield['QuestionID']]))."'";
				break;
			}
		}		
	}
	
	if ($_POST['PersonID'] == 0) {
		// check if record exists //
		$ck_persons_sql = "SELECT * FROM Persons WHERE Email='".$DB->mysqli->escape_string($_POST['Email'])."'";
		$ck_persons_snd = $DB->get_single_result($ck_persons_sql);
		if(isset($ck_persons_snd['empty_result'])) {
			//$PID = 0;
			//$personCreateSQL = "INSERT INTO Persons (Email,Persons_password,PersonsTypes_id,PersonsStatus_id,LeadStages_id,DateCreated,CreatedBy,Offices_id) VALUES('".$DB->mysqli->escape_string($_POST['Email'])."','".$ENC->encrypt($RECORD->generatePassword())."','3','1','1','".time()."','0','33')";
			//echo $personCreateSQL."<br>\n";
			//$personCreateSND = $DB->mysqli->query($personCreateSQL);
			//$PID = $DB->mysqli->insert_id;
			//$PID = 0;	
		} else {
			$PID = $ck_persons_snd['Person_id'];
		}
	} else {
		$PID = $_POST['PersonID'];	
	}

	if(isset($fields['core'])) {
		foreach($fields['core'] as $pfield) {
			$setArray[] = $pfield;
		}
		if ($_POST['PersonID'] == 0) {
			// check if record exists //
			$ck_persons_sql = "SELECT * FROM Persons WHERE Email='".$DB->mysqli->escape_string($_POST['Email'])."'";
			$ck_persons_snd = $DB->get_single_result($ck_persons_sql);
			if(isset($ck_persons_snd['empty_result'])) {
				//$PID = 0;
				$personCreateSQL = "INSERT INTO Persons (Email,Persons_password,PersonsTypes_id,PersonsStatus_id,LeadStages_id,DateCreated,DateUpdated,CreatedBy,Offices_id,Gender) VALUES('".$DB->mysqli->escape_string($_POST['Email'])."','".$ENC->encrypt($RECORD->generatePassword())."','3','1','1','".time()."','".time()."','0','33','')";
				$personCreateSND = $DB->mysqli->query($personCreateSQL);
				$PID = $DB->mysqli->insert_id;	
			} else {
				$PID = $ck_persons_snd['Person_id'];
			}
		} else {
			$PID = $_POST['PersonID'];	
		}
		$personUpdateSQL = "UPDATE Persons SET ".implode(",", $setArray)." WHERE Person_id='".$PID."'";
		
		$personUpdateSND = $DB->mysqli->query($personUpdateSQL);		
	}
	
	// ADD TO FORM TRACKING //
	$pf_sql = "INSERT INTO PersonForms (Person_id, Form_id, FormSubmitted) 
	VALUES('".$PID."','".$_POST['formID']."',NOW())";
	$pf_snd = $DB->mysqli->query($pf_sql);
	
	if(isset($fields['phone'])) {
		$clearPrimaryPhones_sql = "UPDATE Phones SET isPrimary='0' WHERE Person_id='".$PID."'";
		//echo $clearPrimaryPhones_sql."<br>\n";
		$clearPrimaryPhones_snd = $DB->mysqli->query($clearPrimaryPhones_sql);
		$phFields = 'Person_id, PhoneType, Phone_number, isPrimary, isActive, Phone_raw';	
		$phValues = "'".$PID."','Main','".$DB->mysqli->escape_string($fields['phone'])."','1','1', '".$DB->mysqli->escape_string($RECORD->formatPhoneForRC($fields['phone']))."'";
		$phoneInsetSQL = "INSERT INTO Phones (".$phFields.") VALUES(".$phValues.")";
		//echo $phoneInsetSQL."<br>\n";
		$phoneInsetSND = $DB->mysqli->query($phoneInsetSQL);
	}
	if(isset($fields['address']) && ($_POST['PersonID'] == 0)) {
		$clearPrimaryAddress_sql = "UPDATE Addresses SET isPrimary='0' WHERE Person_id='".$PID."'";
		//echo $clearPrimaryAddress_sql."<br>\n";
		$clearPrimaryAddress_snd = $DB->mysqli->query($clearPrimaryAddress_sql);		
		$GEODATA = $GEO->get_LatLon_Google($fields['address']['street'], $fields['address']['city'], $fields['address']['state'], $fields['address']['postal'], $fields['address']['country']);
		$adFields = "Person_id, Street_1, City, State, Postal, Country, isPrimary, Lattitude, Longitude, GeoLocationStatus";
		$adValues = "'".$PID."','".$DB->mysqli->escape_string($fields['address']['street'])."','".(($fields['address']['city'] == '')? $DB->mysqli->escape_string($GEODATA['city']):$DB->mysqli->escape_string($fields['address']['city']))."','".(($fields['address']['state'] == '')? $DB->mysqli->escape_string($GEODATA['state']):$DB->mysqli->escape_string($fields['address']['state']))."','".$DB->mysqli->escape_string($fields['address']['postal'])."','".(($fields['address']['country'] == '')? 'US':$DB->mysqli->escape_string($fields['address']['country']))."','1','".$GEODATA['lat']."','".$GEODATA['lng']."','".$GEODATA['code']."'";
		$adInsertSQL = "INSERT INTO Addresses (".$adFields.") VALUES(".$adValues.")";
		//echo $adInsertSQL."<br>\n";
		$adInsertSND = $DB->mysqli->query($adInsertSQL);
	}
	if(isset($fields['profile'])) {
		$profileCheckSQL = "SELECT * FROM PersonsProfile WHERE Person_id='".$PID."'";
		$profileCheckSND = $DB->get_single_result($profileCheckSQL);
		if(isset($profileCheckSND['empty_result'])) {
			$profileINS_sql = "INSERT INTO PersonsProfile (Person_id) VALUE('".$PID."')";
			//echo $profileINS_sql."<br>\n";
			$profileINS_snd = $DB->mysqli->query($profileINS_sql);		
		}
		$profileUPD_sql = "UPDATE PersonsProfile SET ".implode(",", $fields['profile'])." WHERE Person_id='".$PID."'";
		//echo $profileUPD_sql."<br>\n";
		$profileUPD_snd = $DB->mysqli->query($profileUPD_sql);
	} else {
		$profileCheckSQL = "SELECT * FROM PersonsProfile WHERE Person_id='".$PID."'";
		$profileCheckSND = $DB->get_single_result($profileCheckSQL);
		if(isset($profileCheckSND['empty_result'])) {
			$profileINS_sql = "INSERT INTO PersonsProfile (Person_id) VALUE('".$PID."')";
			//echo $profileINS_sql."<br>\n";
			$profileINS_snd = $DB->mysqli->query($profileINS_sql);		
		}		
	}
	if(isset($fields['images'])) {
		foreach($fields['images'] as $image) {
			$ImageDir = $RECORD->get_image_directory($PID);		
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/client_media/".$ImageDir."/".$PID."/")) {
				mkdir ($_SERVER["DOCUMENT_ROOT"]."/client_media/".$ImageDir."/".$PID."/", 0777);
			}		
			$ImageFileName = clean_special_characters($image);
			//move_uploaded_file($_FILES['file']['tmp_name'], "./temp/".$ImageFileName);
			$NewFileName = time().".".$PID.".".$ImageFileName.".jpg";
			/*
			$convert_call = "$imgmgk_location" . "convert -resize '500x500>' ./ajax/temp/".$ImageFileName." ./ajax/temp/".$NewFileName;
			exec ($convert_call);	
			$copySuccess = copy ("./ajax/temp/".$NewFileName, "./client_media/".$ImageDir."/".$PID."/".$NewFileName);
			*/
			//echo "IMAGE LOC:'".$_SERVER["DOCUMENT_ROOT"]."/ajax/temp/".$ImageFileName."'|".$NewFileName."<br>\n";
			convertImage($_SERVER["DOCUMENT_ROOT"]."/ajax/temp/".$ImageFileName, $NewFileName, getimagesize($_SERVER["DOCUMENT_ROOT"]."/ajax/temp/".$ImageFileName));
			$copySuccess = copy ($_SERVER["DOCUMENT_ROOT"]."/ajax/temp/".$ImageFileName, $_SERVER["DOCUMENT_ROOT"]."/client_media/".$ImageDir."/".$PID."/".$NewFileName);
			
			$ckIMG_sql = "SELECT count(*) as count FROM PersonsImages WHERE Person_id='".$PID."'";
			$ckIMG_snd = $DB->get_single_result($ckIMG_sql);
			if($ckIMG_snd['count'] == 0) {
				$imgStatus = 2;
			} else {
				$imgStatus = 1;
			}
			
			if($copySuccess) {
				$ifields = "Person_id, PersonsImages_path, PersonsImages_status, PersonsImages_dateCreated, PersonsImages_createdBy";
				$ivalues = "'".$PID."', '".$NewFileName."', '".$imgStatus."', '".time()."', '0'";
				$img_query = "INSERT INTO PersonsImages (".$ifields.") VALUES(".$ivalues.")";
				//echo $img_query."<br>\n";
				$img_send = $DB->mysqli->query($img_query);	
			} else {
				// FALLBACK UPLOAD RAW IMAGE //
				if (copy($_SERVER["DOCUMENT_ROOT"]."/ajax/temp/".$ImageFileName, $_SERVER["DOCUMENT_ROOT"]."/client_media/".$ImageDir."/".$PID."/".$ImageFileName)) {
					$file_final_path = time().'.'.$ImageFileName;	
					$file_final_desc = str_replace("_", " ", $ImageFileName);		
					$fields = "Persons_Person_id, PersonsDocuments_path, PersonsDocuments_name, PersonsDocuments_dateCreated, PersonsDocuments_createdBy";
					$values = "'".$PID."', '".$DB->mysqli->escape_string($file_final_path)."', '".$DB->mysqli->escape_string($file_final_desc)." - FAILED IMAGE UPLOAD', '".time()."', '".$_SESSION['system_user_id']."'";
					$ins_query = "INSERT INTO PersonsDocuments ($fields) VALUES($values)";
					//echo "$ins_query<br>";
					$ins_send = $DB->mysqli->query($ins_query);
				} else {
					$mail = new KissPHPMailer();
					$mail->IsHTML(true);
					$mail->From = 'no-reply@kelleher-international.com';
					$mail->FromName = 'KISS FORM UPLOAD ERROR';
					$mail->Subject = 'FAILED IMAGE UPLOAD';
					$mail->Body = 'The attached image failed to copy and failed to resize on the fly via the KISS system.<br>It was being attached to RECORD ID:'.$PID.' <a href="https://kiss.kelleher-international.com/profile/'.$PID.'">link here</a>';
					$mail->AddAddress('rich@kelleher-international.com');
					//$mail->AddBCC('matt@kelleher-international.com');
					$mail->addAttachment("./ajax/temp/".$ImageFileName);
					//$mail->addAttachment("./ajax/temp/".$ImageFileName);
					$mail->Send();						
				}				
			}
			//@unlink("./ajax/temp/".$NewFileName);		//delete the renamed file in current directory
			//@unlink("./ajax/temp/".$ImageFileName);		//delete the original file in current directory
		}	
	}
	if(isset($fields['prefs'])) {
		$prefsCheckSQL = "SELECT * FROM PersonsPrefs WHERE Person_id='".$PID."'";
		$prefsCheckSND = $DB->get_single_result($prefsCheckSQL);
		if(isset($prefsCheckSND['empty_result'])) {
			$submitterGender = $RECORD->get_personGender($PID);
			if($submitterGender == 'F') {
				$seekGender = 'M';
			} else {
				$seekGender = 'F';
			}
			$prefsINS_sql = "INSERT INTO PersonsPrefs (Person_id, prefQuestion_Gender, prefQuestion_Pref_Countries, prefQuestion_Pref_MemberTypes) VALUES('".$PID."','".$seekGender."','US','3|4|7|12')";
			//echo $prefsINS_sql."<br>\n";
			$prefsINS_snd = $DB->mysqli->query($prefsINS_sql);		
		}
		$prefsUPD_sql = "UPDATE PersonsPrefs SET ".@implode(",", $fields['prefs'])." WHERE Person_id='".$PID."'";
		//echo $prefsUPD_sql."<br>\n";
		$prefsUPD_snd = $DB->mysqli->query($prefsUPD_sql);		
	} else {
		$prefsCheckSQL = "SELECT * FROM PersonsPrefs WHERE Person_id='".$PID."'";
		$prefsCheckSND = $DB->get_single_result($prefsCheckSQL);
		if(isset($prefsCheckSND['empty_result'])) {
			$submitterGender = $RECORD->get_personGender($PID);
			if($submitterGender == 'F') {
				$seekGender = 'M';
			} else {
				$seekGender = 'F';
			}
			
			$prefsINS_sql = "INSERT INTO PersonsPrefs (Person_id, prefQuestion_Gender, prefQuestion_Pref_Countries, prefQuestion_Pref_MemberTypes) VALUES('".$PID."','".$seekGender."','US','3|4|7|12')";
			//echo $prefsINS_sql."<br>\n";
			$prefsINS_snd = $DB->mysqli->query($prefsINS_sql);	
		}
	}
	$LDEV->deliverLeadToLocation($PID);
	
	
		
	// GET PERSON'S INCOME AND OPT OUT IF MALE W/ INCOME OVER 250K //
	$hvl_sql = "
	SELECT
		Persons.Gender as gender,
		PersonsProfile.prQuestion_631 as income
	FROM
		Persons
		LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
	WHERE
		Persons.Person_id='".$PID."'
	";
	$hvl_snd = $DB->get_single_result($hvl_sql);
		
	if(($hvl_snd['gender'] == 'M') && (in_array($hvl_snd['income'], $RECORD->highIncomes))):
		// HIGH INCOME MALE LEAD //
		$upd_r_sql = "UPDATE Persons SET isHighIncomeLead='1' WHERE Person_id='".$PID."'";
		$upd_r_snd = $DB->mysqli->query($upd_r_sql);
		
		// SEND NOTICE TO STAFF //
		ob_start();
		?>
		This is an automated message informing you that a HIGH INCOME MALE LEAD has been added to the KISS DATABASE at <?php echo date("m/d/Y h:ia")?> local server time(EST)<br>
		New Record: <a href="https://kiss.kelleher-international.com/profile/<?php echo $PID?>"><?php echo $RECORD->get_personName($PID)?> | <?php echo $RECORD->get_primaryTruncatedAddress($PID)?></a>
		<?php
		$notice = ob_get_clean();
				
		$mail = new KissPHPMailer();
		$mail->IsHTML(true);
		$mail->From = 'no-reply@kelleher-international.com';
		$mail->FromName = 'KISS FORM SUBMIT';
		$mail->Subject = 'HIGH INCOME MALE ADDED TO DATABASE';
		$mail->Body = $notice;
		$mail->AddAddress('matt@kelleher-international.com');			
		$mail->AddAddress('kimberly@kelleher-international.com');
		$mail->AddAddress('jen@kelleher-international.com');
		$mail->Send();			
	else:
		if($snd['FormResponseTemplate'] != 0) {	
			$sql   = "SELECT * FROM EmailTemplates WHERE EmailTemplates_id='".$DB->mysqli->escape_string($snd['FormResponseTemplate'])."' LIMIT 1";
			$data = $DB->get_single_result($sql);		
				// STANDARD LEAD //
				$doEmailSend = $RECORD->checkForResend($PID, $data['EmailTemplates_subject']);
				if($doEmailSend):		
					if(!array_key_exists('empty_result', $data)) {
						$at_pos = strpos($data['EmailTemplates_fromEmail'], '@');
						$template_html = $marketing->merge_data($data['EmailTemplates_bodyHTML'], $PID, 1, 0, false, false);
							
						$json['subject'] 	= $data['EmailTemplates_subject'];
						$json['from']     	= $data['EmailTemplates_fromEmail'];
						$json['fromacct'] 	= substr($data['EmailTemplates_fromEmail'], 0, $at_pos);
						$json['fromdomain'] = substr($data['EmailTemplates_fromEmail'], ($at_pos+1));
						$json['fromname'] 	= $data['EmailTemplates_fromName'];
						$json['html']     	= mb_convert_encoding($template_html, "HTML-ENTITIES", 'UTF-8');
						$json['sendtime']	= time();
						$email_sent = $marketing->send_oneoff_email($data['EmailTemplates_subject'], array($_POST['Email']), array(), array(), $json['fromname'], $json['from'], $json['from'], $json['html'], strip_tags($json['html']), $PID, 0, '', false, false, false, false, array(), array('sendgrid_id' => $PID.'-'.$json['sendtime']), $json['sendtime']);	
					}
				endif;
		}
	endif;
	?><div style="height:<?php echo ($_POST['FormHeight'] - 500)?>px;">&nbsp;</div><?php
	echo $snd['FormSuccessPage'];
	if($snd['Form_nextForm'] != 0):
	$nf_sql = "SELECT * FROM CompanyForms WHERE FormID='".$snd['Form_nextForm']."'";
	$nf_snd = $DB->get_single_result($nf_sql);
	?>
	<div class="m-alert m-alert--icon alert alert-primary" role="alert">
		<div class="m-alert__icon">
			<i class="la la-warning"></i>
		</div>
		<div class="m-alert__text">
			<strong>Congratulations</strong>
			You have submitted the form. You will automaticly be be forwarded to the next form.
		</div>
		<div class="m-alert__actions" style="width: 160px;">
			<!--<a href="//<?php echo $_SERVER['SERVER_NAME']?>/view-form.php?id=<?php echo $nf_snd['FormCallString']?>&p=<?php echo $PID?>" class="btn btn-warning btn-sm m-btn m-btn--pill m-btn--wide">More Info</a>-->
			<meta http-equiv="refresh" content="3;url=//<?php echo $_SERVER['SERVER_NAME']?>/view-form.php?id=<?php echo $nf_snd['FormCallString']?>&p=<?php echo $PID?>" />
		</div>
	</div>
	<?php
	endif;
}
$pageOutput = ob_get_clean();
//print_r($fields);
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>(KISS) FORM</title>
    <meta name="description" content="Latest updates and statistic charts">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
    <!--end::Page Vendors -->  
    <!--begin::Page Snippets -->
    
    <!--end::Page Snippets -->   
    <!-- begin::Page Loader -->
    
    <!--begin::Web font -->
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>    
    
    <script>
      WebFont.load({
        google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
            sessionStorage.fonts = true;
        }
      });	  
    </script>
    <!--end::Web font -->
    <!--begin::Base Styles -->  
    <!--begin::Page Vendors -->
    <link href="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendors -->
    <link href="/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/app/css/kelleher.css" rel="stylesheet" type="text/css" />
    <!--end::Base Styles -->
    <link rel="shortcut icon" href="/favicon.ico" />
		    
</head>
<body>
<div class="container">
<?php
echo $pageOutput;
?>
</div>
<script>
    try {
      var postObject = JSON.stringify({
        event: 'iframeFormSubmit', 
        'kiss_formID': '<?php echo $_POST['formID']?>',
	    'person_UID': '<?php echo $PID?>',
      });
      parent.postMessage(postObject, 'https://kelleher-international.com/');
      parent.postMessage(postObject, 'https://pages.kelleher-international.com/');
    } catch(e) {
    window.console && window.console.log(e);
    }
</script>
</body>
</html>
