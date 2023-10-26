<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.kissphpmailer.php");
include_once("class.marketing.php");

require_once(dirname(__FILE__).'/assets/vendors/modules/dompdf/autoload.inc.php');
require_once(dirname(__FILE__).'/assets/vendors/modules/html_to_doc.inc.php');
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$htmltodoc= new HTML_TO_DOC();

$NoticeTemplateID = 36;
//print_r($_GET);

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption(); 
$MARKETING = new Marketing();
$DB->setTimeZone();

if($_GET['send-blank'] == 1):
	$test = true;
	$blank = true;
else:
	$test = false;
	$blank = false;
endif;

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

$cFields[] = "Contract_name";
$cFields[] = "Contract_AddressPrimary";
$cFields[] = "Contract_CityPrimary";
$cFields[] = "Contract_StatePrimary";
$cFields[] = "Contract_PostalPrimary";
$cFields[] = "Contract_CountryPrimary";	
$cFields[] = "Contract_AddressBilling";
$cFields[] = "Contract_CityBilling";
$cFields[] = "Contract_StateBilling";
$cFields[] = "Contract_PostalBilling";
$cFields[] = "Contract_Phone";
$cFields[] = "Contract_DOB";
$cFields[] = "Contract_DLN";
$cFields[] = "Contract_PaymentAccountName";
$cFields[] = "Contract_PaymentAmount";
$cFields[] = "Contract_PaymentDate";
$cFields[] = "Contract_PaymentRouting";
$cFields[] = "Contract_PaymentAccountNum";


$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_name'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_AddressPrimary'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_CityPrimary'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_StatePrimary'])."'";	
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_PostalPrimary'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_CountryPrimary'])."'";	
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_AddressBilling'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_CityBilling'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_StateBilling'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_PostalBilling'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_Phone'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_DOB'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($_POST['Contract_DLN']))."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_PaymentAccountName'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_PaymentAmount'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($_POST['Contract_PaymentDate'])."'";
$cValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($_POST['Contract_PaymentRouting']))."'";
$cValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($_POST['Contract_PaymentAccountNum']))."'";

for($i=0; $i<count($cFields); $i++) {
	$setArray[] = $cFields[$i]."=".$cValues[$i];
}
$contract_sql = "UPDATE PersonsContract SET ".implode(",", $setArray)." WHERE Contract_Hash='".$_POST['contract-hash']."'";
//echo $contract_sql;
$DB->mysqli->query($contract_sql);	
$json['cid'] = $_POST['Contract_id'];

$cSQL = "SELECT * FROM PersonsContract WHERE Contract_Hash='".$_POST['contract-hash']."'";
$cDATA = $DB->get_single_result($cSQL);
if(!$blank):
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
$PDATA['Contract_PaymentAccountName'] = $cDATA['Contract_PaymentAccountName'];
$PDATA['Contract_PaymentAmount'] = $cDATA['Contract_PaymentAmount'];	
$PDATA['Contract_PaymentDate'] = $cDATA['Contract_PaymentDate'];
$PDATA['Contract_PaymentRouting'] = $ENC->decrypt($cDATA['Contract_PaymentRouting']);
$PDATA['Contract_PaymentAccountNum'] = $ENC->decrypt($cDATA['Contract_PaymentAccountNum']);
$PDATA['Contract_Adendum'] = $cDATA['Contract_Adendum'];
$PDATA['Terms'] = json_decode($cDATA['Contract_TermsBody']);

$Repame = $RECORD->get_FulluserName($PDATA['Contract_rep']);
$p_sql = "SELECT * FROM Persons WHERE Person_id='".$cDATA['Person_id']."'";
$p_snd = $DB->get_single_result($p_sql);
else:
$PDATA['Contract_dateEntered'] = '&nbsp;';
$PDATA['Contract_rep'] = '&nbsp;';
$PDATA['Contract_name'] = '&nbsp;';
$PDATA['Contract_AddressPrimary'] = '&nbsp;';
$PDATA['Contract_CityPrimary'] = '&nbsp;';
$PDATA['Contract_StatePrimary'] = '&nbsp;';
$PDATA['Contract_PostalPrimary'] = '&nbsp;';
$PDATA['Contract_CountryPrimary'] = '&nbsp;';		
$PDATA['Contract_AddressBilling'] = '&nbsp;';
$PDATA['Contract_CityBilling'] = '&nbsp;';
$PDATA['Contract_StateBilling'] = '&nbsp;';
$PDATA['Contract_PostalBilling'] = '&nbsp;';
$PDATA['Contract_Phone'] = '&nbsp;';
$PDATA['Contract_DOB'] = '&nbsp;';
$PDATA['Contract_DLN'] = '&nbsp;';
$PDATA['Contract_MembershipType'] = '&nbsp;';
$PDATA['Contract_Start'] = '&nbsp;';
$PDATA['Contract_RetainerFee'] = '&nbsp;';
$PDATA['Contract_Term'] = '&nbsp;';
$PDATA['Contract_SpecialInst'] = '&nbsp;';
$PDATA['Contract_PaymentAccountName'] = '&nbsp;';
$PDATA['Contract_PaymentAmount'] = '&nbsp;';	
$PDATA['Contract_PaymentDate'] = '&nbsp;';
$PDATA['Contract_PaymentRouting'] = '&nbsp;';
$PDATA['Contract_PaymentAccountNum'] = '&nbsp;';
$PDATA['Contract_Adendum'] = '&nbsp;';
$PDATA['Terms'] = json_decode($cDATA['Contract_TermsBody']);
endif;

// GENERATE SALE RECORD //
$s_fields[] = "Offices_Offices_id";
$s_fields[] = "Persons_Person_id";
$s_fields[] = "PersonsSales_payment";
$s_fields[] = "PersonsSales_balance";
$s_fields[] = "PersonsSales_packageID";
$s_fields[] = "PersonsSales_createdBy";
$s_fields[] = "PersonsSales_dateCreated";
$s_fields[] = "PersonsSales_active";
$s_fields[] = "PersonsSales_basePrice";
$s_fields[]	= "PersonsSales_taxes";
$s_fields[] = "PersonsSales_saleCommission";
$s_fields[] = "PersonsSales_teleCommission";
$s_fields[]	= "PersonsSales_MaxCommission";
$s_fields[] = "PersonsSales_ContractID";

$s_values[] = "'".$p_snd['Offices_id']."'";
$s_values[] = "'".$cDATA['Person_id']."'";
$s_values[] = "'".$cDATA['Contract_RetainerFee']."'";
$s_values[] = "'".$cDATA['Contract_RetainerFee']."'";
$s_values[] = "'".$cDATA['Contract_package']."'";
$s_values[] = "'".$cDATA['Contract_rep']."'";
$s_values[] = "'".time()."'";
$s_values[] = "'1'";
$s_values[] = "'".$cDATA['Contract_RetainerFee']."'";
$s_values[] = "'0.00'";
$s_values[] = "'".round(($cDATA['Contract_RetainerFee'] * .1), 2)."'";
$s_values[] = "'0.00'";
$s_values[] = "'".round(($cDATA['Contract_RetainerFee'] * .1), 2)."'";
$s_values[] = "'".$cDATA['Contract_id']."'";

if(!$test):
$ins_s_sql = "INSERT INTO PersonsSales (".implode(",", $s_fields).") VALUES(".implode(", ", $s_values).")";
//echo $ins_s_sql;
$DB->mysqli->query($ins_s_sql);
$SALE_ID = $DB->mysqli->insert_id;

// GENERATE COMMISSIONS //
$commission = round(($cDATA['Contract_RetainerFee'] * .1), 2);
$ins_psc_sql = "INSERT INTO PersonsSalesCommissions (Users_user_id, PersonsSales_PersonsSales_id, CommissionAMT) VALUES('".$PDATA['Contract_rep']."','".$SALE_ID."','".$commission."')";
$ins_psc_snd = $DB->mysqli->query($ins_psc_sql);


if (($_POST['Contract_PaymentRouting'] != '') && ($_POST['Contract_PaymentAccountNum'] != '')) {
	// CREATE PAYMENT OBJECT //
	$uFields[] = "Person_id";
	$uFields[] = "PaymentInfo_dateCreated";
	$uFields[] = "PaymentInfo_paymentType";
	$uFields[] = "Contract_id";
	$uFields[] = "PaymentInfo_accountName";
	$uFields[] = "PaymentInfo_routingNumber";
	$uFields[] = "PaymentInfo_checkAccountNumber";
	$uFields[] = "PaymentInfo_Execute";
	$uFields[] = "PaymentInfo_Status";
	$uFields[] = "PaymentInfo_Amount";
	$uFields[] = "PaymentInfo_Hash";
	
	$uValues[] = "'".$cDATA['Person_id']."'";
	$uValues[] = "'".time()."'";
	$uValues[] = "'2'";
	$uValues[] = "'".$cDATA['Contract_id']."'";
	$uValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($PDATA['Contract_PaymentAccountName']))."'";
	$uValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($PDATA['Contract_PaymentRouting']))."'";
	$uValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($PDATA['Contract_PaymentAccountNum']))."'";
	$uValues[] = "'".time()."'";
	$uValues[] = "'2'";
	$uValues[] = "'".$DB->mysqli->escape_string($ENC->encrypt($_POST['Contract_PaymentAmount']))."'";
	
	$hashSeed = time()."-".$p_dta['FirstName']."-".$cDATA['Person_id']."-".time();
	$cHash = md5($hashSeed);
	$uValues[] = "'".$DB->mysqli->escape_string($cHash)."'";

	
	for($l=0; $l<count($uFields); $l++) {
		$setArray[] = $uFields[$l]."=".$uValues[$l];
	}
	//$upd_sql = "UPDATE PersonsPaymentInfo SET ".implode(",", $setArray)." WHERE PaymentInfo_ID='".$_POST['CCForm_id']."'";
	$upd_sql = "INSERT INTO PersonsPaymentInfo (".implode(", ", $uFields).") VALUES(".implode(", ", $uValues).")";
	//echo $upd_sql;
	$upd_snd = $DB->mysqli->query($upd_sql);
}
endif;

// SET CONTRACT EXPIRATION DATES //
$StartEpoch = mktime(0,0,0,date("m"), date("d") + 1, date("Y"));
$EndEpoch = mktime(0,0,0,date("m") + $cDATA['Contract_termsMonths'] , date("d"), date("Y"));
$upd_pp_sql = "UPDATE PersonsProfile SET prQuestion_676='".$StartEpoch."', prQuestion_676='".$EndEpoch."' WHERE Person_id='".$cDATA['Person_id']."'";
$upd_pp_snd = $DB->mysqli->query($upd_pp_sql);


ob_start();
?>
<style>
    @page { margin: 20px 50px; }
    #header { position: fixed; left: 0px; top: -180px; right: 0px; height: 150px; background-color: orange; text-align: center; }
    #footer { position: fixed; left: 0px; bottom: 0px; right: 0px; height: 20px; background-color: white; text-align: right; }
    #footer .page:after { content: counter(page, decimal); }
	#footer { font-size:11px; color:#999; }
</style>
<div align="center"><img src="https://kelleher-international.com/inventory/images/header_logo10_14.jpg" /><br /><span style="font-size:16px;">CONTRACT</span></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="45%">
    	<?php if(!$blank): ?>
        Date: <strong><?php echo $PDATA['Contract_dateEntered']?></strong>
        <?php endif; ?>                    
    </td>
    <td width="10%">&nbsp;</td>                        
    <td align="right" width="45%">
    	<?php if(!$blank): ?>
    	Rep: <strong><?php echo $RECORD->get_userName($PDATA['Contract_rep'])?></strong>
        <?php endif; ?>
    </td>
  </tr>
</table>
<table width="100%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td colspan="6">
        <div style="color:#666;">&nbsp;Name <span style="font-size:10px;">(Hereinafter "Client")</span>:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_name']?></div>
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div style="color:#666;">&nbsp;Primary Address:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_AddressPrimary']?></div>               
    </td>
    <td width="50%" colspan="3">
        <div style="color:#666;">&nbsp;Billing Address <span style="font-size:10px;">(If Different)</span>:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_AddressBilling']?></div>               
    </td>
  </tr>
  <tr>
    <td width="26%">
        <div style="color:#666;">&nbsp;City:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_CityPrimary']?></div>
    </td>
    <td width="12%">
        <div style="color:#666;">&nbsp;State:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_StatePrimary']?></div> 
    </td>
    <td width="12%">
        <div style="color:#666;">&nbsp;Zip:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_PostalPrimary']?></div>
    </td>
    <td width="26%">
        <div style="color:#666;">&nbsp;City:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_CityBilling']?></div>
    </td>
    <td width="12%">
        <div style="color:#666;">&nbsp;State:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_StateBilling']?></div>
    </td>
    <td width="12%">
        <div style="color:#666;">&nbsp;Zip:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_PostalBilling']?></div>
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div><span style="color:#666;">&nbsp;Phone:</span> <strong><?php echo $PDATA['Contract_Phone']?></strong></div>
    </td>
    <td colspan="3">
        <div><span style="color:#666;">&nbsp;Country:</span> <strong><?php echo $PDATA['Contract_CountryPrimary']?></strong></div>
    </td>
  </tr>
  <tr>
    <td colspan="3">
        <div><span style="color:#666;">&nbsp;Date of Birth:</span> <strong><?php echo $PDATA['Contract_DOB']?></strong></div>
    </td>
    <td colspan="3">
        <div><span style="color:#666;">&nbsp;Driver's License #:</span> <strong><?php echo $PDATA['Contract_DLN']?></strong></div>
    </td>
  </tr>
</table>
<h3>Membership Specifications</h3>
<table width="100%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div><span style="color:#666;">&nbsp;Membership Type:</span> <?php echo $PDATA['Contract_MembershipType']?></div>
    </td>
    <td>
        <div><span style="color:#666;">&nbsp;Active Term:</span> <?php echo $PDATA['Contract_Term']?></div>
    </td>
  </tr>
  <tr>    
    <td width="50%">
        <div style="color:#666;">&nbsp;Contract Start Date <span style="font-size:10px;">(Hereinafter ​"Effective Date​")</span>​:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_Start']?></div>
    </td>  
    <td>
        <div style="color:#666;">&nbsp;Retainer Fee <span style="font-size:10px;">(Hereinafter ​"Retainer Fee​")</span>:</div>
        <div>&nbsp;$<?php echo @number_format($PDATA['Contract_RetainerFee'], 0)?></div>
    </td>    
  </tr>
  <tr>
    <td colspan="2">
        <div style="padding:5px;"><span style="font-size:12px;">If at any time while this Agreement is in force, the Client wishes to be placed on inactive status, Client may do so for the specified additional, cumulative 12-month period, after providing written notice to Kelleher International.</span></div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        <div style="color:#666;">&nbsp;SPECIAL INSTRUCTIONS:</div>
      	<div style="font-size:12px;"><?php echo $PDATA['Contract_SpecialInst']?></div>
    </td>
  </tr>
</table>
<h3>Payment Information – Electronic Check</h3>
<table width="100%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="50%">
        <div style="color:#666;">&nbsp;Name on Account:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_PaymentAccountName']?></div>
    </td>
    <td width="25%">
        <div style="color:#666;">&nbsp;Payment Amount:</div>
        <div>&nbsp;$<?php echo @number_format($PDATA['Contract_PaymentAmount'], 0)?></div>
    </td>
    <td width="25%">
        <div style="color:#666;">&nbsp;Date to Deposit:</div>
        <div>&nbsp;<?php echo $PDATA['Contract_PaymentDate']?></div>
    </td>
  </tr>
  <tr>
    <td>
        <div style="color:#666;">&nbsp;Bank ACH Routing Number:</div>
      	<div>&nbsp;<?php echo $PDATA['Contract_PaymentRouting']?></div>
    </td>
    <td colspan="2">
        <div style="color:#666;">&nbsp;Checking Account Number:</div>
      	<div>&nbsp;<?php echo $PDATA['Contract_PaymentAccountNum']?></div>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:10px;">
  <tr>
    <td>
<div style="font-size:11px;">Client acknowledges having read and understood this Master Contract with the attached two-page Kelleher
International Terms and Conditions. Your signature on this Agreement guarantees your comprehension and acceptance
of all its terms, covenants and conditions. An executed copy transmitted by email or facsimile shall have the same effect
as the original. <strong style="font-weight:900; font-size:13.5px;">Client may cancel this membership agreement without penalty or obligation at any time prior to midnight of the third business day following the date of Client signature (the "Effective Date") excluding Sundays and holidays. To cancel this Agreement, mail or deliver a signed and dated notice or send a telegram which states that you, the Client, are canceling this agreement, or words of similar effect. </strong></div>
<div style="margin-top:10px; font-weight:900; font-size:13.5px;"> 
Said notice shall be sent to:<br />Kelleher International, LLC. | 145 Corte Madera Town Ctr. #422 | Corte Madera, CA | 94925-1209
</div>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:10px;">
  <tr>
    <td width="75%">
        <div>&nbsp;Client Signature:</div>
        <div style="border-bottom:dashed 1px #333333;">
        <?php if(!$blank): ?>
        <img src="https://<?php echo $_SERVER['SERVER_NAME']?>/signature.image.php?s=<?php echo urlencode($_POST['signature_one'])?>">
        <?php else: ?>
        &nbsp;
        <?php endif; ?>
        </div>
    </td>
    <td width="25%" valign="top">
        <div>&nbsp;Date:</div>
        <?php if(!$blank): ?>
        <?php echo date("m/d/Y")?>
        <?php else: ?>
        &nbsp;
        <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>                
<tr>
    <td>
        <div>&nbsp;Kelleher Signature:</div>
        <div style="border-bottom:dashed 1px #333333;">&nbsp;</div>
    </td>
    <td>
        <div>&nbsp;Date:</div>
        &nbsp;
    </td>
  </tr>
</table>
<div id="footer">
	<p class="page">Kelleher International, LLC. Master Contract KI  REV 21 - Jan 2020 DCM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page </p>
</div>
<div style="page-break-after: always;"></div>
<div align="center"><strong>
    KELLEHER INTERNATIONAL LLC MASTER<br />
    CONTRACT TERMS & CONDITIONS
</strong></div> 
<?php 
$i=0;
for($i=0; $i<count($PDATA['Terms']); $i++): ?>
<p style="font-size:12px; margin-bottom:7px;"><?php echo $PDATA['Terms'][$i]?></p>
<?php endfor; ?>
<div style="color:#666;">&nbsp;ADDENDUM:</div>
<div style="font-size:12px;"><?php echo $PDATA['Contract_Adendum']?></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">              
  <tr>
    <td colspan="2">
      
    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
    </tr>                
  <tr>
    <td width="40%" valign="top">
        <div>&nbsp;Client Name:</div>
        <?php if(!$blank): ?>
        <strong><?php echo $PDATA['Contract_name']?></strong>
        <?php else: ?>
        ______________________________________
        <?php endif; ?>
    </td>
    <td width="60%">
        <div>&nbsp;Client Signature:</div>
        <div>
        
        <?php if(!$blank): ?>
        <img src="https://<?php echo $_SERVER['SERVER_NAME']?>/signature.image.php?s=<?php echo urlencode($_POST['signature_two'])?>">
        <?php else: ?>
        &nbsp;&nbsp;&nbsp;______________________________________
        <?php endif; ?>
        </div>
        
    </td>
  </tr>
</table>
<div style="font-size:10px;">Once received, this Agreement will be executed by Kelleher International, LLC. and a fully executed copy will be provided upon request.</div>
<p style="font-size:10px;">
    <strong>Domestic Transfers:</strong><br />
    Routing #: 026009593 | Account #: 000106240003<br />
    Bank Information: Bank of America 89 Broadway Blvd., Fairfax, CA 94930 USA | +1 (415) 453-5830<br />

    <strong>Bank Wire Instructions:</strong><br />
    Kelleher International 145 Corte Madera Town Ctr #422 Corte Madera, CA 94925-1209 +1 (415) 332-4111<br />
    <strong>International Transfers:</strong><br />
    SWIFT code/International Bank Account Number (IBAN): BOFAUS3N Account #: 000106240003<br />
    Bank Information: Bank of America, 89 Broadway Blvd., Fairfax, CA 94930 USA +1 (415) 45
</p>
<div id="footer">
	<p class="page">Kelleher International, LLC. Master Contract KI  REV 21 - Jan 2020 DCM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Page </p>
</div>
<?php
$CONTRACT_BODY = ob_get_clean();

//echo $CONTRACT_BODY;

$dompdf->loadHtml($CONTRACT_BODY);
$dompdf->render();
$output = $dompdf->output();
$finalFile = 'contract.'.time().'.pdf';
file_put_contents('./ajax/temp/'.$finalFile, $output);

if(!$test):
	$upd_c_sql = "UPDATE PersonsContract SET Contract_status='2' WHERE Contract_id='".$cDATA['Contract_id']."'";
	//echo $upd_c_sql."<br>\n";
	$DB->mysqli->query($upd_c_sql);	
endif;
$ImageDir = $RECORD->get_image_directory($cDATA['Person_id']);

// ADD FILE TO RECORD //
$rootDir = "./client_media/".$ImageDir."/";
if (!file_exists($rootDir)) {
	mkdir ($rootDir, 0777);
}
$personDIR = "./client_media/".$ImageDir."/".$cDATA['Person_id']."/";
//echo $personDIR;
if (!file_exists($personDIR)) {
	mkdir ($personDIR, 0777);
}

if($_GET['send-file'] == 1):
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($cDATA['Person_id'].'_blank_contract.pdf') . "\""); 
	readfile('./ajax/temp/'.$finalFile);
else:
	copy('./ajax/temp/'.$finalFile, "./client_media/".$ImageDir."/".$cDATA['Person_id']."/".$finalFile);
	$fields = "Persons_Person_id, PersonsDocuments_path, PersonsDocuments_name, PersonsDocuments_dateCreated, PersonsDocuments_createdBy";
	$values = "'".$cDATA['Person_id']."', '".$DB->mysqli->escape_string($finalFile)."', '".$DB->mysqli->escape_string('Signed Contract')."', '".time()."', '0'";
	$ins_query = "INSERT INTO PersonsDocuments ($fields) VALUES($values)";
	//echo "$ins_query<br>\n";
	$ins_send = $DB->mysqli->query($ins_query);
	$file_id = $DB->mysqli->insert_id;
	unlink('./ajax/temp/'.$finalFile);
endif;

if(!$test):
$upd_c_sql = "UPDATE PersonsContract SET Contract_fileID='".$file_id."' WHERE Contract_id='".$cDATA['Contract_id']."'";
//echo $upd_c_sql."<br>\n";
$DB->mysqli->query($upd_c_sql);
$ImageDir = $RECORD->get_image_directory($cDATA['Person_id']);

//Create a new PHPMailer instance
$tmp_sql = "SELECT * FROM EmailTemplates WHERE EmailTemplates_id='".$NoticeTemplateID."'";
$tmp_dta = $DB->get_single_result($tmp_sql);
$tmp_html_body = $tmp_dta['EmailTemplates_bodyHTML'];
$tmp_text_body = $tmp_dta['EmailTemplates_bodyText'];

$tmp_html_merged = $MARKETING->merge_data($tmp_html_body, $cDATA['Person_id']);
$tmp_text_merged = $MARKETING->merge_data($tmp_text_body, $cDATA['Person_id']);

$mail = new KissPHPMailer();
$mail->IsHTML(true);
$mail->From = 'no-reply@kelleher-international.com';
$mail->FromName = 'Kelleher International Contract Manager';
$mail->Subject = $tmp_dta['EmailTemplates_subject'];
$mail->Body = $tmp_html_merged;
$mail->AddAddress($p_snd['Email']);
$mail->AddBCC('matt@kelleher-international.com');
$mail->addAttachment("./client_media/".$ImageDir."/".$cDATA['Person_id']."/".$finalFile);
$mail->Send();

$ipINFO = json_decode(get_ip_info($_SERVER['REMOTE_ADDR']), true);
//print_r($ipINFO);

$ch_fields = "Contract_id, ContractHistory_date, ContractHistory_ip, ContractHistory_city, ContractHistory_region, ContractHistory_postal, ContractHistory_country, ContractHistory_location, ContractHistory_userID, ContractHistory_action";
$ch_values = "'".$cDATA['Contract_id']."','".time()."','".$_SERVER['REMOTE_ADDR']."','".$DB->mysqli->escape_string($ipINFO['city'])."','".$DB->mysqli->escape_string($ipINFO['region'])."','".$DB->mysqli->escape_string($ipINFO['postal'])."','".$DB->mysqli->escape_string($ipINFO['country'])."','".$DB->mysqli->escape_string($ipINFO['loc'])."','".$_SESSION['system_user_id']."','SIGNED'";
$ins_ch_sql = "INSERT INTO PersonsContractsHistory (".$ch_fields.") VALUES(".$ch_values.")";
//echo $ins_ch_sql;
$DB->mysqli->query($ins_ch_sql);

// INTERNAL NOTIFICATION //
$notifySubject = "Contract Signed: ".$PDATA['Contract_name'];
$notifyBody = "
			
This is an automated message to notify you that a contract signature has been submitted (".$PDATA['Contract_name']."). 

You may view their record here:
https://kiss.kelleher-international.com/profile/".$cDATA['Person_id']."

KISS Contract Manager

";
			$mail = new KissPHPMailer();
			$mail->IsHTML(false);
			$mail->From = 'no-reply@kelleher-international.com';
			$mail->FromName = 'Kelleher International Contract Manager';
			$mail->Subject = $notifySubject;
			$mail->Body = $notifyBody;
			//$mail->AddAddress('rich@kelleher-international.com');			
			$mail->AddAddress($RECORD->get_userEmail($PDATA['Contract_rep']));
			$mail->AddAddress('matt@kelleher-international.com');
			$mail->AddAddress('mikki@kelleher-international.com');
		  $mail->AddAddress('jen@kelleher-international.com');
		  $mail->Send();			
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


</body>
</html>
<?php
endif;
?>

