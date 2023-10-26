<?php
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.users.php");
include_once("class.sales.php");
include_once("class.sessions.php");
include_once("class.recordMMExpectations.php");
include_once("class.forms.php");

$RECORD = new Record($DB);
$USER = new Users($DB);
$ENC = new encryption();
$DB->setTimeZone();
$SALES = new Sales($DB);
$SESSION = new Session($DB, $ENC);
$EXPECT = new recordMMExpect($DB);
$FORMS = new Forms($DB);
$INCLUDE_EMPTY_CHECKBOX = true;

$PERSON_ID = $pageParamaters['params'][0];
$EXPECT_ID = $pageParamaters['params'][1];

$formConfig = array(
	
	array(
		'title'		=>	'',
		'fields'	=>	array(
			'prQuestion_1860',
			//'prQuestion_668'
		)
	),
		
);
if($EXPECT_ID == '') {
	$EXPECT_ID = 0;
}
if($EXPECT_ID == 0) {
	$p_sql = "
	SELECT
		Persons.*,
		PersonsImages.*,
		PersonsProfile.*,
		Offices.office_Name,
		(SELECT PersonsTypes_text FROM PersonTypes WHERE PersonsTypes_id=Persons.PersonsTypes_id) as PersonTypeText,
		DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), DateOfBirth)), '%Y')+0 AS RecordAge,
		Addresses.*,
		PersonsPrefs.*,
		IFNULL(PersonsColors.Color_title,'NO FLAG') as Color_title,
		IFNULL(PersonsColors.Color_hex,'#FFFFFF') as Color_hex,
		Persons.Color_id
	FROM
		Persons
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
		LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
		LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
		LEFT JOIN PersonsColors ON PersonsColors.Color_id=Persons.Color_id
	WHERE
		Persons.Person_id='".$PERSON_ID."'
	";
	//echo $p_sql;
	$PDATA = $DB->get_single_result($p_sql);
	$PDATA['Contract_name'] = $PDATA['FirstName']." ".$PDATA['LastName'];
	
	foreach($formConfig as $ffield):
		if(!is_array($ffield)):
			if(isset($fData[$ffield])):
				$FORM_DATA[$ffield] = $PDATA[$ffield];
			endif;
		endif;
	endforeach;
	//print_r($FORM_DATA);
	$pe_dta['SubmitStatus'] = 1;
	$preload_notice = 'These parameters have been loaded from their existing profile. You must save before you can generate a link and send email.';
} else {
	$p_sql = "
	SELECT
		Persons.FirstName,
		Persons.LastName,
		Persons.Email
	FROM
		Persons
		INNER JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
		LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
		LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
		LEFT JOIN PersonsColors ON PersonsColors.Color_id=Persons.Color_id
	WHERE
		Persons.Person_id='".$PERSON_ID."'
	";
	//echo $p_sql;
	$PDATA = $DB->get_single_result($p_sql);
	$PDATA['Contract_name'] = $PDATA['FirstName']." ".$PDATA['LastName'];	
	
	$pe_sql = "SELECT * FROM PersonsExpectations WHERE ExpectID='".$EXPECT_ID."'";
	//echo $pe_sql;
	$pe_dta = $DB->get_single_result($pe_sql);
	$fData = unserialize($pe_dta['FormData']);
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
	//print_r($FORM_DATA);
	$preload_notice = 'These parameters have been loaded from saved expectations agreement record.';
}
 

?>
<div class="m-content">
	
    
<div class="m-portlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    <i class="flaticon-file-1"></i> Expectation Agreement Addendum Generator
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form class="m-form m-form--fit m-form--label-align-right" action="javascript:submit_expect_form();" name="mmexpectform" id="mmexpectform">
    <div class="m-portlet__body">
        <div class="row">
            <div class="col-4">            
                <div class="m-form__section m-form__section--first">
                    <div class="form-group m-form__group">
                        <label for="example_input_full_name">
                            Name:
                        </label>
                        <input type="text" class="form-control m-input m-input--solid" value="<?php echo $PDATA['Contract_name']?>" readonly="readonly">                            
                    </div>
                    <div class="form-group m-form__group">
                        <label>
                            Email address:
                        </label>
                        <input type="email" class="form-control m-input m-input--solid" value="<?php echo $PDATA['Email']?>" readonly="readonly">
                    </div>                      
                </div>                                
				<div class="container-fluid">
                	<?php if(($EXPECT_ID != 0) && ($pe_dta['SubmitStatus'] == 1)): ?>
                    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert" style="">
                        <div class="m-alert__icon">
                            <i class="flaticon-exclamation-2"></i>
                            <span></span>
                        </div>
                        <div class="m-alert__text">
                        	<?php
							$contractURL = 'https://'.$_SERVER['SERVER_NAME'].'/view-addendum.php?id='.$pe_dta['Expect_Hash'];
							$contractTinyURL = $ENC->get_tiny_url($contractURL);
							?>
                            <strong>This form can be accessed via the following URL:<br /><a href="<?php echo $contractTinyURL?>" target="_blank"><?php echo $contractTinyURL?></a></strong><br>
                            <textarea class="form-control m-input" id="embedCode"><?php echo $contractTinyURL?></textarea>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-bottom:20px;">
                    
                    	<div class="btn-group m-btn-group" role="group" aria-label="...">
                        <button type="button" class="btn btn-danger" onclick="deleteAgreementForm(<?php echo $EXPECT_ID?>)" <?php echo (($EXPECT_ID == 0)? 'disabled':'')?>><i class="fa fa-times"></i> Delete</button>
                        
                        <?php if(($pe_dta['SubmitStatus'] == 2) && ($pe_dta['Agreement_fileID'] != '')): ?>
                        <a href="/getFile.php?DID=<?php echo $pe_dta['Agreement_fileID']?>" class="btn btn-primary" target="_blank"><i class="fa fa-download"></i> Open Agreement</a>
                        <?php endif; ?>
                        
                        <?php if(($pe_dta['SubmitStatus'] == 1) && ($EXPECT_ID != 0)): ?>
                        <button type="button" class="btn btn-primary" onclick="javascript:sendQuickEmail('<?php echo $PERSON_ID?>', '261', 'Are you sure you want to send this record an email with a link to this form?', 'Expectations Agreement Link', '#agreementLinkURL', '<?php echo $contractTinyURL?>')"><i class="fa fa-envelope"></i> Send Email</button>
                        <button type="button" class="btn btn-secondary" onclick="copyToClipboard()" style="margin-right:5px;">Copy URL to Clipboard</button>
                		<?php endif; ?>
                        </div>        
					</div>
                    <div> 
                        <a href="/profile/<?php echo $PERSON_ID?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Profile</a> 
					</div>                                   
                    <div id="contractHistoryTable" style="display:none;">
                        <h4>Access History</h4>
                        <table class="table m-table m-table--head-no-border">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Location</th>
                                    <th>IP</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5">No History</td>                            
                                </tr>                   
                            </tbody>
                        </table>                
                    </div>
				</div>                    
            
            </div>
            <div class="col-8">
<?php if($pe_dta['SubmitStatus'] == 1): ?> 
				<div class="alert alert-info"><?php echo $preload_notice?></div>           
                <input type="hidden" name="Expect_id" id="Expect_id" value="<?php echo $EXPECT_ID?>" />
                <input type="hidden" name="Person_id" id="Person_id" value="<?php echo $PERSON_ID?>" />
                <div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br />
                <h4 style="margin-left:75px;">EXPECTATIONS ADDENDUM</h4></div>

<div class="form-group m-form__group row">
    <label class="col-md-3 col-form-label">Client:</label>
    <div class="col-md-9">
    	<p class="form-control-static" style="margin-top:9px;"><strong><?php echo $PDATA['Contract_name']?></strong></p>
    </div>
</div>                       
<?php foreach($formConfig as $formBlock): ?>
	<?php echo (($formBlock['title'] != '')? '<h4>'.$formBlock['title'].'</h4>':'')?>
	<?php foreach($formBlock['fields'] as $field): ?>
		<?php if(!is_array($field)): ?>        	
            <?php
			if(is_array($FORM_DATA[$field])):
				//echo $field."|".implode("|", $FORM_DATA[$field])."|";
				echo $FORMS->render_formElement($field, implode("|", $FORM_DATA[$field]), false, $PERSON_ID);
			else:
				//echo $field."|".$FORM_DATA[$field]."|";
	        	echo $FORMS->render_formElement($field, $FORM_DATA[$field], false, $PERSON_ID);
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
                    <input type="checkbox" name="<?php echo $field[0]['fname']?>[]" value="<?php echo $cbox?>" disabled="disabled">
                    <?php echo $cbox?> <span></span>
                </label>
                <?php endforeach; ?>                  
            </div>
        </div>
    </div>
	<?php		
endif;			
?>    
        <?php endif; ?>
	<?php endforeach; ?>
<?php endforeach; ?>


                <hr />
                <table width="650" border="0" cellspacing="0" cellpadding="0" align="center">                            
                    <tr>
                        <td colspan="2">
							<p>The addendum presented in this agreement will be the basis of what Kelleher International will use to conduct a search on my behalf through the terms of the Membership Agreement.</p>
      					</td>
                    <tr>
                        <td width="70%">
                            <div>&nbsp;Client Name:</div>
                            <strong><?php echo $PDATA['Contract_name']?></strong>
                        </td>
                        <td width="30%">
                            <div>&nbsp;Client Initials:</div>
                            {{CLIENT_INITIALS}}
                        </td>
                    </tr>                                   
                </table>
			</div>
                            
		</div>
	</div>
    <div class="m-portlet__foot">
        <div class="row align-items-center">
            <div class="col-lg-6 m--valign-middle">&nbsp;</div>
            <div class="col-lg-6 m--align-right">
                <button type="submit" class="btn btn-brand">
                    Save Addendum
                </button>
            </div>
        </div>
    </div>
	<?php else:
	// RECREATE PAGE FOR PDF //
	ob_start();
?>
<div align="center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><span style="font-size:16px; margin-left:75px;">EXPECTATIONS ADDENDUM</span></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="45%" align="right" style="text-align:right;">
        Date: <strong><?php echo date("m/d/Y h:ia", $pe_dta['SubmitDate'])?></strong>                    
    </td>
    <td width="10%">&nbsp;</td>                        
    <td align="left" width="45%" style="text-align:left;">
    	Rep: <strong><?php echo $RECORD->get_userName($pe_dta['CreatedBy'])?></strong>
    </td>
  </tr>
</table>
<div style="border:#333 solid 1px; padding-top: 1rem; margin-top: 1rem;">
<table width="100%" border="0" cellspacing="0" cellpadding="2" align="center" >
	<tbody>
	<tr>
  		<td width="40%" align="right" valign="top" style="text-align:right; width:40%; vertical-align:top;">Client:</td>
    	<td width="60%" align="left" valign="top" style="text-align:left; width:60%; vertical-align:top;"><?php echo $PDATA['Contract_name']?></td>
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
    	<td width="60%" align="left" style="text-align:left; width:60%; vertical-align:top;"><?php echo $form_element?></td>
    </tr>
    <tr>
    	<td colspan="2">&nbsp;</td>
	</tr>        
	<?php
endfor;
?>    
</tbody>
</table>  
</div>     
<?php
	$DOC_OUTPUT = ob_get_clean();
	?><div class="container"><?php
	echo $DOC_OUTPUT;
	?></div><?php
    endif; 
	?>
    </form>
</div>


<script>
$(document).ready(function(e) {
<?php 
for($l=1; $l<count($fData['specialAgreements']); $l++): 
	if($fData['specialAgreements'][$l] != ''):
		echo "dupSpecialAddon(".json_encode($fData['specialAgreements'][$l]).");\n";
	endif;
endfor;
?>
});
function dupSpecialAddon(tagLine) {
	var formPart = '<div class="input-group m-form__group">';
	formPart += '<span class="input-group-addon"><label class="m-checkbox m-checkbox--single"><input type="checkbox" disabled><span></span></label></span>';
	formPart += '<input type="text" name="specialAgreements[]" class="form-control" aria-label="Text input with checkbox" value="'+tagLine+'">';
	formPart += '</div>'; 
	$('#specialAgreements').append(formPart);	
}
function submit_expect_form() {
	var formData = $('#mmexpectform').serializeArray();
	//console.log(formData);	
	$.post('/ajax/expect_mgr.php?action=submitAddendum', formData, function(data) {
		document.location.href='/expectadd/<?php echo $PERSON_ID?>/'+data.agid;			
	},"json");
}
function copyToClipboard() {
	$("#embedCode").select();
    document.execCommand('copy');
}
function sendQuickEmail(pid, tid, alertBody, slug, mergefield, mergevalue) {
	var choice = confirm(alertBody);
	if(choice) {
		$.post('/ajax/quickmail.php', {
			p:	pid,
			t:	tid,
			mf:	mergefield,
			mv: mergevalue
		}, function(data) {
			console.log(data);
			toastr.success(slug+' Email Sent', '', {timeOut: 5000});			
		});
	}
}
function deleteAgreementForm(agid) {
	var choice = confirm('Are you sure you want to remove this Agreement Form?');
	if(choice) {
		$.post('/ajax/expect_mgr.php?action=delete', {
			agid: agid	
		}, function(data) {
			document.location.href='/profile/<?php echo $PERSON_ID?>';		
		},"json");
	}
	
	
}

</script>
