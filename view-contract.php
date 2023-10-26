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

$cSQL = "SELECT * FROM PersonsContract WHERE Contract_Hash='".$_GET['id']."'";
$cDATA = $DB->get_single_result($cSQL);	
$PDATA['Contract_dateEntered'] = date("m/d/Y", $cDATA['Contract_dateEntered']);
$PDATA['Contract_rep'] = $cDATA['Contract_rep'];
$PDATA['Contract_name'] = $cDATA['Contract_name'];
$PDATA['Contract_AddressPrimary'] = $cDATA['Contract_AddressPrimary'];
$PDATA['Contract_CityPrimary'] = $cDATA['Contract_CityPrimary'];
$PDATA['Contract_StatePrimary'] = $cDATA['Contract_StatePrimary'];
$PDATA['Contract_PostalPrimary'] = $cDATA['Contract_PostalPrimary'];
$PDATA['Contract_CountryPrimary'] = $cDATA['Contract_CountryPrimary'];		
$PDATA['Contract_AddressBilling'] = $cDATA['Contract_AddressBilling'];
$PDATA['Contract_CityBilling'] = $cDATA['Contract_CityBilling'];
$PDATA['Contract_StateBilling'] = $cDATA['Contract_StateBilling'];
$PDATA['Contract_PostalBilling'] = $cDATA['Contract_PostalBilling'];
$PDATA['Contract_Phone'] = $cDATA['Contract_Phone'];
$PDATA['Contract_DOB'] = $cDATA['Contract_DOB'];
$PDATA['Contract_DLN'] = $ENC->decrypt($cDATA['Contract_DLN']);
$PDATA['Contract_MembershipType'] = $cDATA['Contract_MembershipType'];
$PDATA['Contract_Start'] = $cDATA['Contract_Start'];
$PDATA['Contract_RetainerFee'] = $cDATA['Contract_RetainerFee'];
$PDATA['Contract_Term'] = $cDATA['Contract_Term'];
$PDATA['Contract_SpecialInst'] = $cDATA['Contract_SpecialInst'];
$PDATA['Contract_PaymentAmount'] = $cDATA['Contract_PaymentAmount'];	
$PDATA['Contract_PaymentDate'] = $cDATA['Contract_PaymentDate'];
$PDATA['Contract_PaymentRouting'] = $ENC->decrypt($cDATA['Contract_PaymentRouting']);
$PDATA['Contract_PaymentAccountNum'] = $ENC->decrypt($cDATA['Contract_PaymentAccountNum']);
$PDATA['Contract_Adendum'] = $cDATA['Contract_Adendum'];
$PDATA['Terms'] = json_decode($cDATA['Contract_TermsBody']);
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
	
    <style>
	.entry-field {
		background-color:#deffe0;
	}	
	</style>	    
</head>
<body>
<?php if($cDATA['Contract_status'] == 1): ?>
<form id="contractForm" class="m-form m-form--state" action="submit-contract.php" method="post">
<input type="hidden" name="contract-hash" value="<?php echo $_GET['id']?>" />
<div class="text-center"><img src="https://kelleher-international.com/inventory/images/header_logo10_14.jpg" /><br /><h4>MASTER CONTRACT</h4></div>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-bottom:10px;">
  <tr>
    <td width="45%">
        Date: <strong><?php echo $PDATA['Contract_dateEntered']?></strong>                    
    </td>
    <td width="10%">&nbsp;</td>                        
    <td align="right" width="45%">
    	Rep: <strong><?php echo $RECORD->get_userName($PDATA['Contract_rep'])?></strong>
    </td>
  </tr>
</table>

<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td colspan="6">
        <div style="padding-top:10px;">&nbsp;Name <small>(Hereinafter "Client")</small>:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_name" id="Contract_name" value="<?php echo $PDATA['Contract_name']?>" />
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div style="padding-top:10px;">&nbsp;Primary Address:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_AddressPrimary" id="Contract_AddressPrimary" value="<?php echo $PDATA['Contract_AddressPrimary']?>" />               
    </td>
    <td width="50%" colspan="3">
        <div style="padding-top:10px;">&nbsp;Billing Address <small>(If Different)</small>:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_AddressBilling" id="Contract_AddressBilling" value="<?php echo $PDATA['Contract_AddressBilling']?>" />                
    </td>
  </tr>
  <tr>
    <td width="26%">
        <div>&nbsp;City:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_CityPrimary" id="Contract_CityPrimary" value="<?php echo $PDATA['Contract_CityPrimary']?>" /> 
    </td>
    <td width="12%">
        <div>&nbsp;State:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_StatePrimary" id="Contract_StatePrimary" value="<?php echo $PDATA['Contract_StatePrimary']?>" /> 
    </td>
    <td width="12%">
        <div>&nbsp;Zip:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PostalPrimary" id="Contract_PostalPrimary" value="<?php echo $PDATA['Contract_PostalPrimary']?>" /> 
    </td>
    <td width="26%">
        <div>&nbsp;City:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_CityBilling" id="Contract_CityBilling" value="<?php echo $PDATA['Contract_CityBilling']?>" /> 
    </td>
    <td width="12%">
        <div>&nbsp;State:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_StateBilling" id="Contract_StateBilling" value="<?php echo $PDATA['Contract_StateBilling']?>" /> 
    </td>
    <td width="12%">
        <div>&nbsp;Zip:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PostalBilling" id="Contract_PostalBilling" value="<?php echo $PDATA['Contract_PostalBilling']?>" /> 
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div>&nbsp;Phone:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_Phone" id="Contract_Phone" value="<?php echo $PDATA['Contract_Phone']?>" />
    </td>
    <td colspan="3">
        <div>&nbsp;Country:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_CountryPrimary" id="Contract_CountryPrimary" value="<?php echo $PDATA['Contract_CountryPrimary']?>" />
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div>&nbsp;Date of Birth:</div>
      <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_DOB" id="Contract_DOB" value="<?php echo $PDATA['Contract_DOB']?>" />
    </td>
    <td colspan="3">
        <div>&nbsp;Driver's License #:</div>
      <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_DLN" id="Contract_DLN" value="<?php echo $PDATA['Contract_DLN']?>" />
    </td>
  </tr>
</table>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td>&nbsp;<h5>Membership Specifications</h5></td>
  </tr>
</table>
<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div>&nbsp;Membership Type:</div>
        <div class="form-control form-control-sm m-input m-input--solid"><?php echo $PDATA['Contract_MembershipType']?></div>
    </td>
    <td width="50%">
        <div>&nbsp;Contract Start Date <small>(Hereinafter ​"Effective Date​")</small>​:</div>
        <div class="form-control form-control-sm m-input m-input--solid"><?php echo $PDATA['Contract_Start']?></div>
    </td>
  </tr>
  <tr>
    <td>
        <div>&nbsp;Retainer Fee <small>(Hereinafter ​"Retainer Fee​")</small>:</div>
        <div class="form-control form-control-sm m-input m-input--solid"><i class="fa fa-usd"></i> <?php echo number_format($PDATA['Contract_RetainerFee'], 0)?></div>
    </td>
    <td>
        <div>&nbsp;Active Term:</div>
        <div class="form-control form-control-sm m-input m-input--solid"><?php echo $PDATA['Contract_Term']?></div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        <div style="padding:5px;">If at any time while this Agreement is in force, the Client wishes to be placed on inactive status, Client may do so for the specified additional, cumulative 12-month period, after providing written notice to Kelleher International.</div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        <div>&nbsp;SPECIAL INSTRUCTIONS:</div>
      	<div class="form-control form-control-sm m-input m-input--solid"><?php echo $PDATA['Contract_SpecialInst']?></div>
    </td>
  </tr>
</table>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td>&nbsp;<h5>Payment Information – Electronic Check</h5></td>
  </tr>
</table>
<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div>&nbsp;Name on Account:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PaymentAccountName" id="Contract_PaymentAccountName" value="<?php echo $PDATA['Contract_name']?>" />
    </td>
    <td width="25%">
        <div>&nbsp;Payment Amount:</div>
        <div class="input-group m-input-group">
			<span class="input-group-addon" style="background-color:#deffe0;"><i class="fa fa-usd"></i></span>
        	<input type="number" class="form-control form-control-sm m-input entry-field" name="Contract_PaymentAmount" id="Contract_PaymentAmount" value="<?php echo $PDATA['Contract_PaymentAmount']?>" />
		</div>            
    </td>
    <td width="25%">
        <div>&nbsp;Date to Deposit:</div>
        <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PaymentDate" id="Contract_PaymentDate" value="<?php echo $PDATA['Contract_PaymentDate']?>" />
    </td>
  </tr>
  <tr>
    <td>
        <div>&nbsp;Bank ACH Routing Number:</div>
      <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PaymentRouting" id="Contract_PaymentRouting" value="<?php echo $PDATA['Contract_PaymentRouting']?>" />
    </td>
    <td colspan="2">
        <div>&nbsp;Checking Account Number:</div>
      <input type="text" class="form-control form-control-sm m-input entry-field" name="Contract_PaymentAccountNum" id="Contract_PaymentAccountNum" value="<?php echo $PDATA['Contract_PaymentAccountNum']?>" />
    </td>
  </tr>
</table>

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:10px;">
  <tr>
    <td>
        <div style="font-size:11px;">Client acknowledges having read and understood this Master Contract with the attached two-page Kelleher
International Terms and Conditions. Your signature on this Agreement guarantees your comprehension and acceptance
of all its terms, covenants and conditions. An executed copy transmitted by email or facsimile shall have the same effect
as the original. <strong style="font-weight:900; font-size:13.5px;">Client may cancel this membership agreement without penalty or obligation at any time prior to midnight of the third business day following the date of Client signature (the "Effective Date") excluding Sundays and holidays. To cancel this Agreement, mail or deliver a signed and dated notice or send a telegram which states that you, the Client, are canceling this agreement, or words of similar effect. </strong></div>
<div style="margin-top:10px; font-weight:900; font-size:13.5px;"> 
Said notice shall be sent to:<br />Kelleher International, LLC. | ​145 Corte Madera Town Ctr. #422 | Corte Madera, CA | 94925-1209
</div>
    </td>
  </tr>
</table>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:10px;">
  <tr>
    <td width="75%">
        <div>&nbsp;Client Signature:</div>
<div class="form-group m-form__group" style="padding-top:0px;">
    <input type="text" class="form-control input-sm m-input entry-field" id="signature-one" name="signature_one" required>
    <div class="form-control-feedback">
        Enter email address for signature
    </div>
</div>        
        
        
    </td>
    <td width="25%" valign="top">
        <div>&nbsp;Date:</div>
        <img src="/signature.image.php?s=<?php echo urlencode(date("m/d/Y"))?>&font=1">
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>                
<tr>
    <td>
        <div>&nbsp;Kelleher Signature:</div>
        <div style="border-bottom:dashed 1px #333333;"><img src="/signature.image.php?s="></div>
    </td>
    <td>
        <div>&nbsp;Date:</div>
        <img src="/signature.image.php?s=&font=1">
    </td>
  </tr>
</table>

<hr />

<div class="text-center"><strong>
    KELLEHER INTERNATIONAL LLC MASTER<br />
    CONTRACT TERMS & CONDITIONS
</strong></div> 

<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
<?php 
$i=0;
for($i=0; $i<count($PDATA['Terms']); $i++): ?>
<tr>                   
<td><p class="term_text" data-id="<?php echo $i?>" id="term_text_<?php echo $i?>" style="font-size:11px;"><?php echo $PDATA['Terms'][$i]?></p></td>
</tr>
<?php endfor; ?>
</table>
<hr />
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">              
  <tr>
    <td colspan="2">
      <div>&nbsp;ADDENDUM:</div>
      <div class="form-control form-control-sm m-input m-input--solid"><?php echo $PDATA['Contract_Adendum']?></div>
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
    </tr>                
  <tr>
    <td width="40%" valign="top">
        <div>&nbsp;Client Name:</div>
        <strong><?php echo $PDATA['Contract_name']?></strong>
    </td>
    <td width="60%">
        <div>&nbsp;Client Signature:</div>
		<div class="form-group m-form__group" style="padding-top:0px;">
            <input type="text" class="form-control input-sm m-input entry-field" id="signature-two" name="signature_two" required>
            <div class="form-control-feedback">
                Enter email address to confirm acceptance.
            </div>
        </div> 
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
    </tr>
  <tr>
    <td colspan="2">
        <small><p>Once received, this Agreement will be executed by Kelleher International, LLC. and a fully executed copy will be provided upon request.</p>
        <p>
        <strong>Domestic Transfers:</strong><br />
        Routing #: 026009593 | Account #: 000106240003<br />
        Bank Information: Bank of America 89 Broadway Blvd., Fairfax, CA 94930 USA | +1 (415) 453-5830<br />
        

        <strong>Bank Wire Instructions:</strong><br />
        Kelleher International 145 Corte Madera Town Ctr #422 Corte Madera, CA 94925-1209 +1 (415) 332-4111<br />
        <strong>International Transfers:</strong><br />
        SWIFT code/International Bank Account Number (IBAN): BOFAUS3N Account #: 000106240003<br />
        Bank Information: Bank of America, 89 Broadway Blvd., Fairfax, CA 94930 USA +1 (415) 45
    </p></small>
    </td>
</tr>                                    
</table>

<p>&nbsp;</p>
<p>&nbsp;</p> 
  
<div class="text-center">
	<button id="btnSubmit" class="btn btn-lg btn-primary" type="submit">Submit Signed Contract</button>
    <p>&nbsp;</p>
    After submitting, you will recieve a PDF version of the contract sent directly to your email on file.
</div>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
$ipINFO = json_decode(get_ip_info($_SERVER['REMOTE_ADDR']), true);
//print_r($ipINFO);

$ch_fields = "Contract_id, ContractHistory_date, ContractHistory_ip, ContractHistory_city, ContractHistory_region, ContractHistory_postal, ContractHistory_country, ContractHistory_location, ContractHistory_userID, ContractHistory_action";
$ch_values = "'".$cDATA['Contract_id']."','".time()."','".$_SERVER['REMOTE_ADDR']."','".$DB->mysqli->escape_string($ipINFO['city'])."','".$DB->mysqli->escape_string($ipINFO['region'])."','".$DB->mysqli->escape_string($ipINFO['postal'])."','".$DB->mysqli->escape_string($ipINFO['country'])."','".$DB->mysqli->escape_string($ipINFO['loc'])."','".$_SESSION['system_user_id']."','VIEW'";
$ins_ch_sql = "INSERT INTO PersonsContractsHistory (".$ch_fields.") VALUES(".$ch_values.")";
//echo $ins_ch_sql;
$DB->mysqli->query($ins_ch_sql);

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
            <strong>Contract Complete!</strong><br>
            A signed copy of your contract has been sent to your email address.
        </div>
    </div>
</div>
<?php endif; ?>
<script>
$(document).ready(function(e) {
    $("#Contract_PaymentDate").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#Contract_PaymentDate').val(e.format('mm/dd/yyyy'))
	});
  $("#contractForm").submit(function (e) {
        //disable the submit button
        $("#btnSubmit").attr("disabled", true);
        return true;
    });
});
</script>
</body>
</html>