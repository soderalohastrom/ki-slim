<?php
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.users.php");
$RECORD = new Record($DB);
$USER = new Users($DB);
$ENC = new encryption();

$PERSON_ID = $pageParamaters['params'][0];
$FORMID = $pageParamaters['params'][1];
$userPerms = $USER->get_userPermissions($_SESSION['system_user_id']);

echo "FORM ID: ".$FORMID;
if($FORMID == '') {
	$FORMID = 0;
	
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
	//
	//$PDATA['Contract_rep'] = array($_SESSION['system_user_id']);
	$PDATA['Contract_dateEntered'] = date("m/d/Y");
	$PDATA['PaymentInfo_NameFirst'] = $ENC->encrypt($PDATA['FirstName']);
	$PDATA['PaymentInfo_NameLast'] = $ENC->encrypt($PDATA['LastName']);
	$PDATA['PaymentInfo_Execute'] = time();
	//print_r($PDATA);
} else {
	$cSQL = "SELECT * FROM PersonsPaymentInfo WHERE PaymentInfo_ID='".$FORMID."'";
	//echo $cSQL."<br>";
	$PDATA = $DB->get_single_result($cSQL);
}

?>
<div class="m-content">
	<div class="m-portlet">
    	<div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        <i class="flaticon-file-1"></i> CC Form Generator
                    </h3>
                </div>
            </div>
        </div>
        <div class="m-portlet__body" id="contract_body_capture">
        	<form action="javascript:;">
            <input type="hidden" name="CCForm_id" id="CCForm_id" value="<?php echo $FORMID?>" />
            <input type="hidden" name="Person_id" id="Person_id" value="<?php echo $PERSON_ID?>" />
        	<div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><h4>CREDIT CARD INFO</h4></div>
            
            <table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
                <tr>
                    <td colspan="2">
        	            <div style="padding-top:10px;">&nbsp;Name on Card:</div>
            	        <div class="input-group">
	                        <input type="text" class="form-control form-control-sm m-input m-input--solid" name="NameOnCard_First" id="NameOnCard_First" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_NameFirst'])?>" /> 
    	                    <span class="input-group-addon">&nbsp;</span>
        	                <input type="text" class="form-control form-control-sm m-input m-input--solid" name="NameOnCard_Last" id="NameOnCard_Last" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_NameLast'])?>" /> 
                        </div>  
                    </td>
                    <td width="168">
	                    <div style="padding-top:10px;">&nbsp;Amount:</div>
    	                <input type="text" class="form-control form-control-sm m-input m-input--solid" name="CardAmount" id="CardAmount" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Amount'])?>" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div style="padding-top:10px;">&nbsp;Card Type:</div>
                        <div class="m-radio-inline" style="margin-left:10px;">
                            <label class="m-radio">
                                <input type="radio" name="CardType" value="VISA">
                                Visa
                                <span></span>
                            </label>
                            <label class="m-radio">
                                <input type="radio" name="CardType" value="MASTERCARD">
                                Mastercard
                                <span></span>
                            </label>
                            <label class="m-radio">
                                <input type="radio" name="CardType" value="AMEX">
                                American Express
                                <span></span>
                            </label>
                        </div>                
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div style="padding-top:10px;">&nbsp;Credit Card Number:</div>
                        <input type="text" name="CardNum" id="CardNum" value="<?php echo substr($ENC->decrypt($PDATA['card_num']), -4)?>" class="form-control form-control-sm m-input m-input--solid"/>
                    </td>
                </tr>
                <tr>
                  	<td width="208">
                    	<div style="padding-top:10px;">&nbsp;Expiration:</div>
                  		<div class="input-group">
                            <select name="CardExp_MM" id="CardExp_MM" class="form-control form-control-sm m-input m-input--solid">
                            	<option>MM</option>
                                <option value="01">Jan (01)</option>
                                <option value="02">Feb (02)</option>
                                <option value="03">Mar (03)</option>
                                <option value="04">Apr (04)</option>
                                <option value="05">May (05)</option>
                                <option value="06">Jun (06)</option>
                                <option value="07">Jul (07)</option>
                                <option value="08">Aug (08)</option>
                                <option value="09">Sep (09)</option>
                                <option value="10">Oct (10)</option>
                                <option value="11">Nov (11)</option>
                                <option value="12">Dec (12)</option>
                            </select>
                            <span class="input-group-addon">&nbsp;</span>
                            <select name="CardExp_YYYY" id="CardExp_YYYY" class="form-control form-control-sm m-input m-input--solid">
                            	<option>YYYY</option>
                                <?php for($i=date("Y"); $i < (date("Y") + 25); $i++): ?>
                                <option value="<?php echo $i?>"><?php echo $i?></option>
                                <?php endfor; ?>
                            </select>
						</div>                  	
                  	</td>
                  	<td width="266">
                  		<div style="padding-top:10px;">&nbsp;Security Code:</div>
                        <div class="input-group">                
	                        <input type="text" name="CardSVN" id="CardSVN" value="<?php echo $PDATA['card_svn']?>" class="form-control form-control-sm m-input m-input--solid"/>
    	                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                        </div>
                  	</td>
                  	<td>                    	
                  		<div style="padding-top:10px;">&nbsp;Date to Run:</div>
                        <div class="input-group">
		               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="CardRunDate" id="CardRunDate" value="<?php echo date("m/d/Y", $PDATA['PaymentInfo_Execute'])?>" />
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>                            
                  	</td>
                </tr>
                <tr>
                  	<td colspan="3">
                    	<div style="padding-top:10px;">&nbsp;Billing Address:</div>
                  		<input type="text" name="card_address" id="card_address" class="form-control form-control-sm m-input m-input--solid" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Street'])?>" />                  
                  	</td>
                </tr>
                <tr>
                  	<td>
                    	<div style="padding-top:10px;">&nbsp;City:</div>
                  		<input type="text" name="card_city" id="card_city" class="form-control form-control-sm m-input m-input--solid" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_City'])?>" />
                    </td>
                  	<td>
                  		<div style="padding-top:10px;">&nbsp;State:</div>
                        <input type="text" name="card_state" id="card_state" class="form-control form-control-sm m-input m-input--solid" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_State'])?>" />
                  	</td>
                  	<td>
                    	<div style="padding-top:10px;">&nbsp;Zip:</div>
                        <input type="text" name="card_postal" id="card_postal" class="form-control form-control-sm m-input m-input--solid" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Postal'])?>" />
					</td>
                </tr>
                <tr>
                  	<td colspan="3">
                    	<div style="padding:5px">
                            <div class="m-checkbox-list">
                                <label class="m-checkbox">
                                    <input type="checkbox">
                                    I authorize the above amount to be charged to the credit card entered above on the above date. 
                                    <span></span>
                                </label>
                            </div>
                        </div>
                  	</td>
                </tr>
			</table>
       	  

          </form>
		</div>
        <div class="m-portlet__foot">
            <div class="row align-items-center">
                <div class="col-lg-6 m--valign-middle">&nbsp;</div>
                <div class="col-lg-6 m--align-right">
                    <button type="button" class="btn btn-brand" onclick="saveCCForm()">
                        Save CC Form
                    </button>
                    <span class="m--margin-left-10">
                        or
                        <a href="#" class="m-link m--font-bold">
                            Cancel
                        </a>
                    </span>
                </div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="contractInfoModal" role="dialog" data-backdrop="static" aria-labelledby="sourceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="sourceModalLabel"><i class="flaticon-file-1"></i> Payment Form Information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">  
            	<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-exclamation-2"></i>
                        <span></span>
                    </div>
                    <div class="m-alert__text">
                    	<?php 
						$contractURL = 'https://'.$_SERVER['SERVER_NAME'].'/payment.php?id='.$PDATA['PaymentInfo_Hash'];
						$contractTinyURL = $ENC->get_tiny_url($contractURL); 
						?>
                        <strong>This contract can be accessed via <a href="<?php echo $contractTinyURL?>" target="_blank">the following URL</a>:</strong><br />
                        <textarea class="form-control m-input" id="embedCode"><?php echo $contractTinyURL?></textarea>
                    </div>
                </div> 
    

			</div>
			<div class="modal-footer">
            	<a href="/profile/<?php echo $PERSON_ID?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
				<button type="button" class="btn btn-danger" onclick="removePayment()">Delete Payment Form</button>
				<button type="button" class="btn btn-info" onclick="clearPayment()">Clear Payment Information</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit Payment Form</button>
                <button type="button" class="btn btn-secondary" onclick="copyToClipboard()">Copy URL to Clipboard</button>                
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(e) {
    $("#CardRunDate").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#CardRunDate').val(e.format('mm/dd/yyyy'))
	});	
	<?php if($FORMID != 0): ?>
	$('#contractInfoModal').modal('show');
	<?php endif; ?>	
});
function removePayment() {
	var choice = confirm('Are you sure you want to delete payment form? This action cannot be undone.');
	if(choice) {
		$.post('/ajax/ccform_generation.php?action=deleteContract', {
			cid		:'<?php echo $FORMID?>'
		}, function(data) {
			alert('Form Deleted');
			document.location.href='/profile/<?php echo $PERSON_ID?>';
		});
	}	
}
function clearPayment() {
	var choice = confirm('Are you sure you want to clear this payment information? This action cannot be undone.');
	if(choice) {
		$.post('/ajax/ccform_generation.php?action=clearPayment', {
			cid		:'<?php echo $FORMID?>'
		}, function(data) {
			alert('Form Deleted');
			document.location.href='/profile/<?php echo $PERSON_ID?>';
		});
	}	
}
function copyToClipboard() {
	$("#embedCode").select();
    document.execCommand('copy');
}
function saveCCForm() {
	var formData = $('#contract_body_capture form').serializeArray();	

	var formError = 0;
	var formErrorText = '';
	if($('#CardAmount').val() == '') {
		formError = 1;
		formErrorText += 'Must enter in an amount for the card to be charged \n';
	}

	if(formError == 1) {
		alert(formErrorText);
	} else {	
		//console.log(formData);
		$.post('/ajax/ccform_generation.php?action=createForm', formData, function(data) {
			console.log(data);
			document.location.href='/ccformgen/<?php echo $PERSON_ID?>/'+data.cid;
		}, "json");
	}
}
</script>
