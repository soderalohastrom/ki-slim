<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.kissphpmailer.php");
include_once("class.marketing.php");
include_once("class.forms.php");

require_once(dirname(__FILE__).'/assets/vendors/modules/dompdf/autoload.inc.php');
require_once(dirname(__FILE__).'/assets/vendors/modules/html_to_doc.inc.php');
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$htmltodoc= new HTML_TO_DOC();

function ifNullValue($data) {
	if($data == ''):
		return "[NO PREFERENCE]";
	else:
		return $data;
	endif;		
}

function updateRecordData($PID, $field, $value) {
	global $DB, $RECORD;
	if(is_array($value)) {
		$final_value = implode("|", $value);
	} else {
		$final_value = $value;
	}
	
	//echo $field;
	//if(is_array($field)):
	//	echo "|".print_r($field);
	//endif;
	//echo "\n";
	if(!is_array($field)):
		if($field == 'MaritalStatus'):
			$upd_sql = "UPDATE Persons SET MaritalStatus='".$DB->mysqli->escape_string($final_value)."' WHERE Person_id='".$PID."'";
			$ck_sql = "SELECT * FROM Persons WHERE Person_id='".$PID."'";
			$ck_dta = $DB->get_single_result($ck_sql);
			$OldValue = $ck_dta[$field];
			$QID = $field;
			$RECORD->log_action($PID, 'Prefs Updated', $QID, $OldValue, $final_value, 0, time());		
		elseif(substr($field, 0, 4) == 'pref'):
			$upd_sql = "UPDATE PersonsPrefs SET $field='".$DB->mysqli->escape_string($final_value)."' WHERE Person_id='".$PID."'";
			$ck_sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$PID."'";
			$ck_dta = $DB->get_single_result($ck_sql);
			$OldValue = $ck_dta[$field];
			$QID = str_replace('prefQuestion_', '', $field);
			$RECORD->log_action($PID, 'Prefs Updated', $QID, $OldValue, $final_value, 0, time());		
		elseif(substr($field, 0, 2) == 'pr'):
			$upd_sql = "UPDATE PersonsProfile SET $field='".$DB->mysqli->escape_string($final_value)."' WHERE Person_id='".$PID."'";
			$ck_sql = "SELECT * FROM PersonsProfile WHERE Person_id='".$PID."'";
			$ck_dta = $DB->get_single_result($ck_sql);
			$OldValue = $ck_dta[$field];
			$QID = str_replace('prQuestion_', '', $field);
			$RECORD->log_action($PID, 'Profile Updated', $QID, $OldValue, $final_value, 0, time());
		endif;
		//echo $upd_sql;
		$upd_snd = $DB->mysqli->query($upd_sql);
	endif;
}

$NoticeTemplateID = 260;
//print_r($_POST);

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$MARKETING = new Marketing();
$DB->setTimeZone();
$FORMS = new Forms($DB);

$cSQL = "SELECT * FROM PersonsExpectations WHERE Expect_Hash='".$_POST['agreement-hash']."'";
//echo $cSQL;
$cDATA = $DB->get_single_result($cSQL);
if($cDATA['empty_result'] == 1):
	ob_start();
	?>
	<p>&nbsp;</p>
    <p>&nbsp;</p>
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
        <div class="m-alert__icon">
            <i class="flaticon-exclamation"></i>
            <span></span>
        </div>
        <div class="m-alert__text">
            <strong>INVALID REQUEST!</strong><br>
            The request you are attempting is invalid.
        </div>
    </div>
    <?php
	$PAGE_OUTPUT = ob_get_clean();
else:
	ob_start();
	//$fData = unserialize($cDATA['FormData']);
	$PERSON_ID = $cDATA['PersonID'];
	$cDATA['Contract_name'] = $RECORD->get_personName($PERSON_ID);
	//print_r($_POST);
	$formConfig = unserialize(base64_decode($_POST['config']));
	//print_r($formConfig);
	
	//print_r($fData);
	foreach($formConfig as $ffield):
		//echo $ffield."<br>";
		foreach($ffield['fields'] as $sfield):
			if(!is_array($sfield)):
				if($sfield == 'prefQuestion_age_floor'):
					$FORM_DATA[$sfield] = $_POST['ageRange_value_1']." to ".$_POST['ageRange_value_2'];
					updateRecordData($PERSON_ID, 'prefQuestion_age_floor', $_POST['ageRange_value_1']."|".$_POST['ageRange_value_2']);
				else:
					if(isset($_POST[$sfield])):
						$FORM_DATA[$sfield] = $_POST[$sfield];
						updateRecordData($PERSON_ID, $sfield, $_POST[$sfield]);
					endif;
				endif;
			else:
				//echo "<hr>";
				//print_r($sfield);
				$fieldName = $sfield[0]['fname'];
				//echo "FIELDNAME:".$fieldName;
				$FORM_DATA[$fieldName] = $_POST[$fieldName];
				updateRecordData($PERSON_ID, $sfield, $_POST[$fieldName]);
			endif;
		endforeach;
	endforeach;
	//echo "<br>\nPERSON:".$PERSON_ID."<br>\n";
	//print_r($FORM_DATA);	
	$formDatatoSave = serialize($FORM_DATA);
	
	?>
	<p>&nbsp;</p>
    <p>&nbsp;</p>
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-success alert-dismissible fade show" role="alert">
        <div class="m-alert__icon">
            <i class="flaticon-exclamation"></i>
            <span></span>
        </div>
        <div class="m-alert__text">
            <strong>Agreement Submitted</strong><br>
            You should recieve a copy of the agreeement in your email inbox.
        </div>
    </div>
    <?php
	$PAGE_OUTPUT = ob_get_clean();
	
	// RECREATE PAGE FOR PDF //
	ob_start();
?>
<style>
	body { font-family:Arial, Helvetica, sans-serif; font-size:12px; }
    @page { margin: 20px 50px; }
    #header { position: fixed; left: 0px; top: -180px; right: 0px; height: 150px; background-color: orange; text-align: center; }
    #footer { position: fixed; left: 0px; bottom: 0px; right: 0px; height: 20px; background-color: white; text-align: right; }
    #footer .page:after { content: counter(page, decimal); }
	#footer { font-size:11px; color:#999; }
</style>
<div align="center"><img src="https://<?php echo $_SERVER['SERVER_NAME']?>/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><span style="font-size:16px;margin-left:75px;">EXPECTATIONS AGREEMENT</span></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="45%">
        Date: <strong><?php echo date("m/d/Y h:ia")?></strong>                    
    </td>
    <td width="10%">&nbsp;</td>                        
    <td align="right" width="45%">
    	Rep: <strong><?php echo $RECORD->get_userName($cDATA['CreatedBy'])?></strong>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="2" align="center">
	<tr>
  		<td width="40%" align="right" valign="top" style="text-align:right; width:40%; vertical-align:top;">Client:</td>
    	<td width="60%" align="left" valign="top" style="text-align:left; width:60%; vertical-align:top;"><?php echo $cDATA['Contract_name']?></td>
	</tr>
<?php
$labels = array_keys($FORM_DATA);
for($i=0; $i<count($labels); $i++):
	$db_field_name = $labels[$i];
	$form_element = $FORM_DATA[$db_field_name];
	if(is_array($form_element)):
		$form_element = implode("<br>\n", $form_element);
	endif;
	$final_label = $FORMS->get_fieldInfo($db_field_name);
	if($final_label == ''):
		$final_label = 'I Agree that';
	endif;
	?>
    <tr>
    	<td width="40%" align="right" style="text-align:right; width:40%; vertical-align:top;"><?php echo $final_label?>:</td>
    	<td width="60%" align="left" style="text-align:left; width:60%; vertical-align:top;"><?php echo ifNullValue($form_element)?></td>
    </tr>
    <tr>
    	<td colspan="2">&nbsp;</td>
	</tr>        
	<?php
endfor;
?>    
</table> 
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">              
	<tr>
		<td colspan="3">&nbsp;</td>
        <td colspan="3">&nbsp;</td>
	</tr>                
	<tr>
    	<td width="45%" valign="top" style="text-align:center; width:45%; vertical-align:bottom;">
        	<div style="border-bottom:#000 solid 1px;"><strong><?php echo $cDATA['Contract_name']?></strong></div>
	        <div>Client Name</div>
    	</td>
        <td width="10%" style="text-align:center; width:10%; vertical-align:top;">&nbsp;</td>
	    <td width="45%" style="text-align:center; width:45%; vertical-align:bottom;">
    	    <div style="border-bottom:#000 solid 1px;"><img src="https://<?php echo $_SERVER['SERVER_NAME']?>/signature.image.php?s=<?php echo urlencode($_POST['signature_one'])?>"></div>
        	<div>Client Signature</div>
    	</td>
  	</tr>
  	<tr>
    	<td colspan="3">&nbsp;</td>
        <td colspan="3">&nbsp;</td>
    </tr>
</table>       
<?php
	$DOC_OUTPUT = ob_get_clean();

	// GENERATE PDF //
	$dompdf->loadHtml($DOC_OUTPUT);
	$dompdf->render();
	$output = $dompdf->output();
	$finalFile = 'agreeent.'.time().'.pdf';
	file_put_contents('./ajax/temp/'.$finalFile, $output);
	
	// UPDATE Agreemnt Record //
	$upd_c_sql = "UPDATE PersonsExpectations SET SubmitStatus='2', SubmitDate='".time()."', FormData='".$DB->mysqli->escape_string(serialize($_POST))."' WHERE ExpectID='".$cDATA['ExpectID']."'";
	//echo $upd_c_sql."<br>\n";
	$DB->mysqli->query($upd_c_sql);
	$ImageDir = $RECORD->get_image_directory($PERSON_ID);
	
	// ADD FILE TO RECORD //
	$rootDir = "./client_media/".$ImageDir."/";
	if (!file_exists($rootDir)) {
		mkdir ($rootDir, 0777);
	}
	$personDIR = "./client_media/".$ImageDir."/".$PERSON_ID."/";
	//echo $personDIR;
	if (!file_exists($personDIR)) {
		mkdir ($personDIR, 0777);
	}
	copy('./ajax/temp/'.$finalFile, "./client_media/".$ImageDir."/".$PERSON_ID."/".$finalFile);
	$fields = "Persons_Person_id, PersonsDocuments_path, PersonsDocuments_name, PersonsDocuments_dateCreated, PersonsDocuments_createdBy";
	$values = "'".$PERSON_ID."', '".$DB->mysqli->escape_string($finalFile)."', '".$DB->mysqli->escape_string('Signed Expectations Agreement')."', '".time()."', '0'";
	$ins_query = "INSERT INTO PersonsDocuments ($fields) VALUES($values)";
	//echo "$ins_query<br>\n";
	$ins_send = $DB->mysqli->query($ins_query);
	$file_id = $DB->mysqli->insert_id;
	unlink('./ajax/temp/'.$finalFile);
	
	$upd_c_sql = "UPDATE PersonsExpectations SET Agreement_fileID='".$file_id."' WHERE ExpectID='".$cDATA['ExpectID']."'";
	//echo $upd_c_sql."<br>\n";
	$DB->mysqli->query($upd_c_sql);
	
	//Create a new PHPMailer instance
	$tmp_sql = "SELECT * FROM EmailTemplates WHERE EmailTemplates_id='".$NoticeTemplateID."'";
	$tmp_dta = $DB->get_single_result($tmp_sql);
	$tmp_html_body = $tmp_dta['EmailTemplates_bodyHTML'];
	$tmp_text_body = $tmp_dta['EmailTemplates_bodyText'];
	
	$tmp_html_merged = $MARKETING->merge_data($tmp_html_body, $PERSON_ID);
	$tmp_text_merged = $MARKETING->merge_data($tmp_text_body, $PERSON_ID);
	
	$mail = new KissPHPMailer();
	$mail->IsHTML(true);
	$mail->From = $tmp_dta['EmailTemplates_fromEmail'];
	$mail->FromName = $tmp_dta['EmailTemplates_fromName'];
	$mail->Subject = $tmp_dta['EmailTemplates_subject'];
	$mail->Body = $tmp_html_merged;
	$mail->AddAddress($RECORD->get_personEmail($PERSON_ID));
	$mail->addAttachment("./client_media/".$ImageDir."/".$PERSON_ID."/".$finalFile);
	$mail->Send();
	
	// INTERNAL NOTIFICATION //
$notifySubject = "Expecations Agreement Signed: ".$cDATA['Contract_name'];
$notifyBody = "
			
This is an automated message to notify you that an expectatins agreement signature has been submitted (".$cDATA['Contract_name']."). 

You may view their record here:
https://kiss.kelleher-international.com/profile/".$PERSON_ID."

KISS Agreement Manager

";
	$mail = new KissPHPMailer();
	$mail->IsHTML(false);
	$mail->From = 'no-reply@kelleher-international.com';
	$mail->FromName = 'Kelleher International Expectations Agreement Manager';
	$mail->Subject = $notifySubject;
	$mail->Body = $notifyBody;
	//$mail->AddAddress('matt@kelleher-international.com');
	$mail->AddAddress($RECORD->get_userEmail($cDATA['CreatedBy']));
	$mail->Send();
	
endif;
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Matchmaking Contract View</title>
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
    <script type="text/javascript" src="/assets/vendors/custom/twbs-pagination/jquery.twbsPagination.min.js"></script>
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
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div class="container">
<?php echo $PAGE_OUTPUT?>
</div>
</body>
</html>
