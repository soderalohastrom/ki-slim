<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$DB->setTimeZone();

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

$pSQL = "SELECT * FROM PersonsPaymentInfo WHERE PaymentInfo_Hash='".$_GET['id']."'";
$PDATA = $DB->get_single_result($pSQL);	

$PAY_TYPE = $PDATA['PaymentInfo_paymentType'];
?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Matchmaking Payment form View</title>
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
	</style>	    
</head>
<body>
<?php if($PDATA['PaymentInfo_Status'] == 1): ?>
<div class="container">
<form class="m-form m-form--state" action="javascript:submitPayment();" id="paymentForm" method="post">
<input type="hidden" name="CCForm_id" id="CCForm_id" value="<?php echo $PDATA['PaymentInfo_ID']?>" />
<input type="hidden" name="Person_id" id="Person_id" value="<?php echo $PDATA['Person_id']?>" />
<input type="hidden" name="PaymentInfo_paymentType" id="PaymentInfo_paymentType" value="<?php echo $PDATA['PaymentInfo_paymentType']?>" />
<?php if($PAY_TYPE == 2): ?>
<!-- BEGIN: E-CHECK PAYMENT FORM -->
<div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><h4>ELECTRONIC CHECK PAYMENT</h4></div>
<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div>&nbsp;Name on Account:</div>
        <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_accountName" id="PaymentInfo_accountName" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_accountName'])?>" />
    </td>
    <td width="25%">
        <div>&nbsp;Payment Amount:</div>
        <div class="input-group m-input-group">
			<span class="input-group-addon"><i class="fa fa-usd"></i></span>
        	<input type="number" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_Amount" id="PaymentInfo_Amount" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Amount'])?>" readonly />
		</div>            
    </td>
    <td width="25%">
        <div>&nbsp;Date to Deposit:</div>
        <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_Execute" id="Contract_PaymentDate" value="<?php echo date("m/d/Y", time())?>" readonly />
    </td>
  </tr>
  <tr>
    <td>
        <div>&nbsp;Bank ACH Routing Number:</div>
      <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_routingNumber" id="PaymentInfo_routingNumber" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_routingNumber'])?>" />
    </td>
    <td colspan="2">
        <div>&nbsp;Checking Account Number:</div>
      <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_checkAccountNumber" id="PaymentInfo_checkAccountNumber" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_checkAccountNumber'])?>" />
    </td>
  </tr>
  <tr>
        <td colspan="3">
            <div style="padding:10px">
                <div class="m-checkbox-list">
                    <label class="m-checkbox">
                        <input type="checkbox" id="authorize">
                        I authorize the above amount to be charged to the account entered above on the above date. 
                        <span></span>
                    </label>
                </div>
            </div>
        </td>
    </tr> 
</table>    
<!-- END: E-CHECK PAYMENT FORM -->
<?php elseif($PAY_TYPE == 3): ?>
<!-- BEGIN: BANK TRANSFER PAYMENT FORM -->
<div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><h4>ELECTRONIC WIRE PAYMENT</h4></div>
<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div>&nbsp;Name of Bank:</div>
        <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_accountName" id="PaymentInfo_accountName" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_routingNumber'])?>" />
    </td>
    <td width="25%">
        <div>&nbsp;Payment Amount:</div>
        <div class="input-group m-input-group">
			<span class="input-group-addon"><i class="fa fa-usd"></i></span>
        	<input type="number" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_Amount" id="PaymentInfo_Amount" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Amount'])?>" readonly />
		</div>            
    </td>
    <td width="25%">
        <div>&nbsp;Date of Transfer:</div>
        <input type="text" class="form-control form-control-sm m-input m-input--solid" name="PaymentInfo_Execute" id="Contract_PaymentDate" value="<?php echo date("m/d/Y", time())?>" readonly />
    </td>
  </tr>
  <tr>
        <td colspan="3">
            <div style="padding:10px">
                <div class="m-checkbox-list">
                    <label class="m-checkbox">
                        <input type="checkbox" id="authorize">
                        I confirm the amount above will be wired to Kelleher International on the date selected.
                        <span></span>
                    </label>
                </div>
            </div>
        </td>
    </tr>
  <tr>
    <td colspan="3">
        <p style="padding:10px;">
        For wiring instructions please see the last page of your contract.
    </p></small>
    </td>
</tr> 
</table>    
<!-- END: BANK TRANSFER PAYMENT FORM -->
<?php elseif($PAY_TYPE == 1): ?>
<!-- BEGIN: CREDIT CARD PAYMENT FORM -->
<div class="text-center"><img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" /><br /><h4>CREDIT CARD PAYMENT</h4></div>
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
            <input type="text" class="form-control form-control-sm m-input m-input--solid" name="CardAmount" id="CardAmount" value="<?php echo $ENC->decrypt($PDATA['PaymentInfo_Amount'])?>" readonly />
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
                <input type="text" class="form-control form-control-sm m-input m-input--solid" name="CardRunDate" id="CardRunDate" value="<?php echo date("m/d/Y", time())?>" readonly />
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
                        <input type="checkbox" id="authorize">
                        I authorize the above amount to be charged to the credit card entered above on the above date. 
                        <span></span>
                    </label>
                </div>
            </div>
        </td>
    </tr>
</table>
<!-- END: CREDIT CARD PAYMENT FORM -->
<?php endif; ?>
<p>&nbsp;</p>
<div class="text-center">
	<button class="btn btn-lg btn-primary" type="submit" id="button-payment-submit">Submit Payment</button>
</div>

  
</form>
<script>
function submitPayment() {
	if($('#authorize').is(':checked')) {
		$('#button-payment-submit').attr('disabled', true);	
		$('#button-payment-submit').addClass('m-loader m-loader--light m-loader--right');
		var paymentFormData = $('#paymentForm').serializeArray();
		$.post('/ajax/payments.php?action=write_to_db', paymentFormData, function(data) {
			console.log(data);
			if(data.success) {
				document.location.reload(true);
			} else {
				alert(data.response);
			}
		}, "json");	
	} else {
		alert('You must click to agree to process this transaction.');
	}
}

$(document).ready(function(e) {
     $("#paymentForm").submit(function (e) {
        //disable the submit button
        $("#button-payment-submit").attr("disabled", true);
        return true;
    });
});

</script>
<?php


/*
$ipINFO = json_decode(get_ip_info($_SERVER['REMOTE_ADDR']), true);
$ch_fields = "Contract_id, ContractHistory_date, ContractHistory_ip, ContractHistory_city, ContractHistory_region, ContractHistory_postal, ContractHistory_country, ContractHistory_location, ContractHistory_userID, ContractHistory_action";
$ch_values = "'".$cDATA['Contract_id']."','".time()."','".$_SERVER['REMOTE_ADDR']."','".$DB->mysqli->escape_string($ipINFO['city'])."','".$DB->mysqli->escape_string($ipINFO['region'])."','".$DB->mysqli->escape_string($ipINFO['postal'])."','".$DB->mysqli->escape_string($ipINFO['country'])."','".$DB->mysqli->escape_string($ipINFO['loc'])."','".$_SESSION['system_user_id']."','VIEW'";
$ins_ch_sql = "INSERT INTO PersonsContractsHistory (".$ch_fields.") VALUES(".$ch_values.")";
//echo $ins_ch_sql;
$DB->mysqli->query($ins_ch_sql);
*/

?>
</form>
<?php else: ?>
<div class="container">
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
        <div class="m-alert__icon">
            <i class="flaticon-exclamation"></i>
            <span></span>
        </div>
        <div class="m-alert__text">
            <strong>Payment Complete!</strong><br>
            This payment has been submitted.
        </div>
    </div>
</div>
<?php endif; ?>
<script>
$(document).ready(function(e) {
    /*
	$("#Contract_PaymentDate").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#Contract_PaymentDate').val(e.format('mm/dd/yyyy'))
	});
	
	$("#CardRunDate").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#CardRunDate').val(e.format('mm/dd/yyyy'))
	});
	*/
});
</script>
</body>
</html>