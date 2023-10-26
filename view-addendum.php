<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.recordMMExpectations.php");
include_once("class.encryption.php");
include_once("class.forms.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$DB->setTimeZone();
$FORMS = new Forms($DB);
$INCLUDE_EMPTY_CHECKBOX = true;

//$_GET['id'] = 'f9209aa7fb69bac1ecc72e66bda1ee2e';

function get_ip_info($ip) {
	$url = "https://ipinfo.io/" . $ip;
	$curl_error = false;
	$ch = curl_init($url);
	//curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	$data = curl_exec($ch);
	if(curl_errno($ch)) {
		$curl_error = true;
	}
	curl_close($ch);
	if($curl_error) {
		return false;
	}
	if($decode_response) {
		return json_decode($data, true);
	} else {
		return $data;
	}
}
function ifNullValue($data) {
	if($data == ''):
		return "[NO PREFERENCE]";
	else:
		return $data;
	endif;		
}

$formConfig = array(
	array(
		'title' 	=> '',
		'fields'	=>	array(
			'prQuestion_1860',
			//'prQuestion_668'
		)
	),
	
);

$cSQL = "SELECT * FROM PersonsExpectations WHERE Expect_Hash='".$_GET['id']."'";
//echo $cSQL;
$cDATA = $DB->get_single_result($cSQL);
$fData = unserialize($cDATA['FormData']);
$PERSON_ID = $cDATA['PersonID'];
$cDATA['Contract_name'] = $RECORD->get_personName($PERSON_ID);
//print_r($fData);
foreach($formConfig as $ffield):
	//echo $ffield."<br>";
	foreach($ffield['fields'] as $sfield):
		if(!is_array($sfield)):
			if(isset($fData[$sfield])):
				$FORM_DATA[$sfield] = $fData[$sfield];
			endif;
		endif;
	endforeach;
endforeach;
//echo "PERSON:".$PERSON_ID."<br>\n";
//print_r($FORM_DATA);
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Expectations Addendum View</title>
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
	
    <style>
	.entry-field {
		background-color:#deffe0;
	}
	.kiss-row {
		display:none;
	}
	</style>	    
</head>
<body>
<div class="container">
<?php
if($cDATA['empty_result'] == 1):
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
else:
if($cDATA['SubmitStatus'] == 1): ?>
<form class="m-form m-form--state" action="submit-addendum.php" method="post">
<input type="hidden" name="agreement-hash" value="<?php echo $_GET['id']?>" />
<input type="hidden" name="config" value="<?php echo base64_encode(serialize($formConfig))?>" />
<div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><h4 style="margin-left:75px;"> Expectation Addendum</h4></div>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-bottom:10px;">
  <tr>
    <td width="45%">
        Date: <strong><?php echo date("m/d/y h:ia", $cDATA['CreateDate'])?></strong>                    
    </td>
    <td width="10%">&nbsp;</td>                        
    <td align="right" width="45%">
    	Rep: <strong><?php echo $RECORD->get_userName($cDATA['CreatedBy'])?></strong>
    </td>
  </tr>
</table>

<div class="form-group m-form__group row">
    <label class="col-md-5 col-form-label text-right">Client:</label>
    <div class="col-md-7">
    	<p class="form-control-static" style="margin-top:9px;"><strong><?php echo $cDATA['Contract_name']?></strong></p>
    </div>
</div>                       
<?php foreach($formConfig as $formBlock): ?>
	<?php echo (($formBlock['title'] != '')? '<h4>'.$formBlock['title'].'</h4>':'')?>
	<?php foreach($formBlock['fields'] as $field): ?>
		<?php if(!is_array($field)): ?>        	
            <?php
			if(is_array($FORM_DATA[$field])):
				//echo $field.": ".implode("|", $FORM_DATA[$field])."<br>\n";
				echo '<div class="row" style="margin-bottom:15px;"><div class="col-5 text-right">'.$FORMS->get_fieldInfo($field).'</div><div class="col-7"><strong>'.ifNullValue(implode(", ", $FORM_DATA[$field])).'</strong></div></div>';
				//echo "FORM DATA IN:".implode("|", $FORM_DATA[$field])."<br>\n";
				echo $FORMS->render_formElement($field, implode("|", $FORM_DATA[$field]), true, $PERSON_ID);
			elseif($field == 'prefQuestion_age_floor'):
				//echo $field.": ".$FORM_DATA[$field]."<br>\n";
				$ageParts = explode("|", $FORM_DATA[$field]);
				$display = $ageParts[0]." to ".$ageParts[1];
				echo '<div class="row" style="margin-bottom:20px;"><div class="col-5 text-right">'.$FORMS->get_fieldInfo($field).'</div><div class="col-7"><strong>'.$display.'</strong></div></div>';
	        	echo $FORMS->render_formElement($field, $FORM_DATA[$field], true, $PERSON_ID);
			else:
				//echo $field.": ".$FORM_DATA[$field]."<br>\n";
				echo '<div class="row" style="margin-bottom:20px;"><div class="col-5 text-right">'.$FORMS->get_fieldInfo($field).'</div><div class="col-7"><strong>'.ifNullValue($FORM_DATA[$field]).'</strong></div></div>';
	        	echo $FORMS->render_formElement($field, $FORM_DATA[$field], true, $PERSON_ID);
			endif;
			?>
        <?php else: ?>
    		<?php 
//print_r($field);
if($field[0]['type'] == 'checkbox'):
	?>
    <div class="m-form__group form-group row">
    	<label class="col-md-3 col-form-label"><?php echo $field[0]['label']?></label>
        <div class="col-md-9">
            <div class="m-checkbox-list">
                <?php foreach($field[0]['values'] as $cbox): ?>
                <label class="m-checkbox">
                    <input type="checkbox" name="<?php echo $field[0]['fname']?>[]" value="<?php echo $cbox?>" required>
                    <?php echo $cbox?> <span></span>
                </label>
                <?php endforeach; ?> 
                <?php 
				for($l=0; $l<count($fData['specialAgreements']); $l++): 
					if($fData['specialAgreements'][$l] != ''):
						?>
                        <label class="m-checkbox">
                    		<input type="checkbox" name="<?php echo $field[0]['fname']?>[]" value="<?php echo $fData['specialAgreements'][$l]?>" required>
                    		<?php echo $fData['specialAgreements'][$l]?> <span></span>
                		</label>
                        <?php
					endif;
				endfor;                     
				?>
            </div>
        </div>
    </div>
	<?php		
endif;			
			?>    
        <?php endif; ?>
	<?php endforeach; ?>
<?php endforeach; ?>
<hr class="m--margin-top-50" >
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">                            
	<tr>
    	<td colspan="2">
        	<p>The addendum presented in this agreement will be the basis of what Kelleher International will use to conduct a search on my behalf through the terms of the Membership Agreement.</p>
        </td>
    <tr>
		<td width="50%" valign="top">
			<div>&nbsp;Client Name:</div>
			<strong><?php echo $cDATA['Contract_name']?></strong>
		</td>
		<td width="50%" valign="top">
			<div>&nbsp;Client Signature:</div>
			<div class="form-group m-form__group" style="padding-top:0px;">
            	<input type="text" class="form-control input-sm m-input entry-field" id="signature-one" name="signature_one" required>
            	<div class="form-control-feedback">
                	<small>Enter email address to confirm acceptance.</small>
            	</div>
        	</div>
		</td>
	</tr>                                   
</table>

<p>&nbsp;</p>
<p>&nbsp;</p> 
  
<div class="text-center">
	<div><button id="btnSubmit" class="btn btn-lg btn-primary" type="submit">Submit Expectations Addendum</button></div>
    <div style="margin-top:5px;"><small>After submitting, you will recieve a PDF version of the agreement sent directly to your email on file.</small></div>
</div>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</form>
<?php else: ?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
    <div class="m-alert__icon">
        <i class="flaticon-exclamation"></i>
        <span></span>
    </div>
    <div class="m-alert__text">
        <strong>Agreement Complete!</strong><br>
        A signed copy of your agreement has been sent to your email address.
    </div>
</div>
<?php endif; 

endif;
?>
</div>

<script>
$(document).ready(function(e) {
    $("#<?php echo $_GET['id']?>").submit(function (e) {
        //disable the submit button
        $("#btnSubmit").attr("disabled", true);
        return true;
    });
});
</script>
</body>
</html>





























