<?php
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.users.php");
include_once("class.sales.php");
include_once("class.sessions.php");

$RECORD = new Record($DB);
$USER = new Users($DB);
$ENC = new encryption();
$DB->setTimeZone();
$SALES = new Sales($DB);
$SESSION = new Session($DB, $ENC);

$PERSON_ID = $pageParamaters['params'][0];
$CONTRACT = $pageParamaters['params'][1];

$userPerms = $USER->get_userPermissions($_SESSION['system_user_id']);

$TermsArray = array(
	"Together with the Master Contract, this agreement (the \"Agreement\") is entered into as of the Effective Date, by and between Client and Kelleher International, LLC (hereinafter \"Kelleher\") who each agree to the following terms and conditions. Kelleher's intent is to introduce Client to members of the opposite sex, whom Kelleher has determined through in-depth interviews and limited screening to be potentially compatible with Client. This process is referred to as facilitating an Introduction.",
	"Because of the complexities and uncertainties involved with any personal relationship, Kelleher cannot guarantee that Client will find a partner for marriage, companionship or other relationship who will satisfy all of Client's subjective criteria. Kelleher will use its experience and judgment to work with Client to provide Introductions to individuals who also desire to form a long-term or compatible relationship. Client understands that the resources created by Kelleher include qualified persons who have both paid and not paid a fee.",
	"Kelleher has sole discretion to terminate this Agreement in the event that Kelleher determines: (a) Client is attempting to exploit, abuse, or use Kelleher's services or information for any immoral, illegal or harassment purposes or activities; or (b) Client displays inappropriate behavior, is disrespectful, discrediting or depreciative in any manner to other members or Kelleher representatives; or (c) there are any material inaccuracies in any information provided by Client. In the event that Kelleher terminates this Agreement pursuant to this paragraph, any fees paid by Client shall not be refunded.",	
	"If Client desires any personal information to remain confidential, Client shall explicitly outline in writing the information to stay confidential and Kelleher will, to the best of its ability, maintain in confidence such information. Client agrees that all other information provided by Client to Kelleher and on the Introduction Profile may be shared with other clients and members beyond Kelleher's control, accordingly, Client agrees that Kelleher will not be liable for any loss or damage resulting from the release of any confidential information. Kelleher does not guarantee or warrant the accuracy of the personal information provided to Kelleher by any client or another member, and Client agrees that Kelleher shall not be responsible or liable for any false or misleading information provided by Kelleher to anyone including Client.",
	"Kelleher interviews and administers limited screening of applicants for membership; however, Kelleher neither conducts nor requires its clients or members to submit to any medical, legal or psychological testing or evaluation. Client agrees that Kelleher shall have no legal liability for any injury or damage to Client resulting from assault, sexually transmitted diseases, or any other harm or damage alleged by Client arising from any Introduction by Kelleher. Client shall indemnify and hold harmless Kelleher, its officers, directors, contractors and employees for any action, claim or damage arising out of Client's dealings with Kelleher.",
	"Retainer fee: Client agrees to pay the Retainer Fee specified in the Master Contract. Client agrees and understands that the Retainer Fee is distributed directly toward the matchmaking process of, but not limited to, facilitating introductions, time spent working directly or indirectly on Client's files and/or on behalf of Client, general advertising, networking, scouting and marketing necessary to attract suitable individuals for successful matches, research checking, personal consulting/coaching, various relationship management and dating counseling services, dating etiquette and other self-improvement consulting, and for time and effort to maintain the Client's file over the term of Agreement.",
	"Client agrees to matches with certain persons who are potentially compatible. This fee is payable in full at the time of execution of this Agreement and all fees paid to Kelleher are non-refundable.",
	"Client may not assign or otherwise transfer this Agreement or any rights or obligations under it.",
	"For New York residents and New York Clients only, the Parties agree that $1,000 of the fee paid is applicable to New York referrals and Client also agrees to travel within the Tri-State area for social referrals. Any member may place their membership on hold for a period of up to one-year and agrees to a minimum of six referrals per year.",
	"Kelleher Feedback Form: Client agrees to complete and return Kelleher's Feedback Form after each introduction. Client agrees and understands that the receipt by Kelleher of the Feedback Form is critical to this Agreement and receipt of the Feedback Form is required before Kelleher will make any additional introductions.",
	"Non-Disparagement: Client agrees to not disparage or make negative or derogatory remarks about Kelleher or any of its principals, agents, members, clients, and employees publicly at any time during membership or for five (5) years subsequent to contract period without first contacting Kelleher to engage in a good faith effort to resolve Client's concerns. The parties expressly understand that public disparagement includes, but is not limited to, posts to blogs, social media, and consumer review websites and apps.",
	"Death, Disability: If because of death or disability Client is unable to receive all services for which Client has contracted Client and the Client's estate may elect to be relieved of the obligation to make payments for services other than those received before death or the onset of disability. In the limited event of death or disability, if the Client has prepaid any amount of services, so much of the amount prepaid that is allocable to services that the Client has not received shall be promptly refunded to the Client or his or her representative. \"Disability\" means a condition which precludes Client from physically using the services specified in the contract during the term of disability and the condition is verified in writing by a physician designated and remunerated by Client. The written certification of the physician shall be presented to Kelleher. If the physician determines that the duration of the disability will be less than six months, Kelleher may extend the term of the contract for a period of six months at no additional charge to Client in lieu of cancellations.",
	"Pursuant to California legislation, Client agrees that Kelleher provides its services nationally and internationally. In the event of Client's relocation, the Parties will work in good faith to maintain this Agreement. If Client relocates his or her primary residence further than 50 miles from the geographic area defined in this Agreement and Kelleher is unable to transfer the contract and provide comparable services, then Client may elect to be relieved of the obligation to make payment for service other than those received prior to that relocation, and if Client has prepaid any amount for dating services, so much of the amount prepaid that is allocable to services that the Client has not received shall be promptly refunded to Client. Any Client who elects to be relieved of further obligation according to this subdivision may be charged a predetermined fee of one hundred dollars ($100), or if more than half of the life of the contract has expired, a predetermined fee of ($50). This term is limited to Clients who reside in California at the commencement of services.",		
	"Additional Provisions: In the event that any litigation arises in connection with this Agreement, the prevailing party shall be entitled to reasonable attorney's fees, costs and expenses. This agreement was accepted and entered into in the State of California. The parties agree to Marin County, California state court, jurisdiction, venue, and choice of law. Prior to instituting any litigation all disputes concerning this Agreement or between parties shall be submitted in good faith to Mediation in the County of Marin, costs shared equally. A party who commences litigation prior to the termination of Mediation shall be responsible for all attorney's fees and costs required to stay the litigation and to compel Mediation.",
	"Each provision of this Agreement shall be deemed severable and if for any reason, any provision hereof is invalid or contrary to any existing or future law, such invalidity shall not affect the applicability or validity of any other provision of this Agreement.",
	"Previous Misconduct: By executing this Agreement, Client represents that he or she has never been charged with a felony or a sexually related assault. Client also represents that he or she has never been charged with domestic violence nor has a Temporary Restraining Order, Restraining Order (Order of Protection) ever been requested or issued against him or her.",
	"Client Authorization: The Client hereby authorizes Kelleher International, LLC and/or its agents to investigate and secure any reports on the Client or obtain any information that is deemed by Kelleher to be relevant.",
	"Non-Refundable Contract: The Client has read and understands that the retainer fees paid are completely non-refundable and are distributed as described in this Agreement.",
);

if($CONTRACT == '') {
	$CONTRACT = 0;
	
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
	//print_r($PDATA);
	$PDATA['Contract_rep'] = array($_SESSION['system_user_id']);
	$PDATA['Contract_dateEntered'] = date("m/d/Y");
	$PDATA['Contract_name'] = $PDATA['FirstName']." ".$PDATA['LastName'];
	$PDATA['Contract_AddressPrimary'] = $PDATA['Street_1'];
	$PDATA['Contract_CityPrimary'] = $PDATA['City'];
	$PDATA['Contract_StatePrimary'] = $PDATA['State'];
	$PDATA['Contract_PostalPrimary'] = $PDATA['Postal'];
	$PDATA['Contract_CountryPrimary'] = $PDATA['Country'];		
	$PDATA['Contract_AddressBilling'] = $PDATA['Street_1'];
	$PDATA['Contract_CityBilling'] = $PDATA['City'];
	$PDATA['Contract_StateBilling'] = $PDATA['State'];
	$PDATA['Contract_PostalBilling'] = $PDATA['Postal'];	
	$PDATA['Contract_Phone'] = $RECORD->get_primaryPhone($PERSON_ID, false, true);
	$PDATA['Contract_DOB'] =  date("m/d/Y", $PDATA['DateOfBirth']);
	$PDATA['Contract_DLN'] = '';
	$PDATA['Contract_MembershipType'] = '';
	$PDATA['Contract_Start'] = date("m/d/Y");	
	$PDATA['Contract_RetainerFee'] = '';
	$PDATA['Contract_Term'] = '';
	$PDATA['Contract_SpecialInst'] = '';
	$PDATA['Contract_PaymentAmount'] = '';
	$PDATA['Contract_PaymentDate'] = date("m/d/Y");
	$PDATA['Contract_PaymentRouting'] = '';
	$PDATA['Contract_PaymentAccountNum'] = '';
	$PDATA['Contract_Adendum'] = '';
	$PDATA['Terms'] = $TermsArray;
	$PDATA['Contract_memTypeSelect'] = '';
	$contractStatus = 1;	
	$PDATA['Contract_package'] = '';
	$PDATA['Contract_termsMonths'] = 12;
} else {
	$cSQL = "SELECT * FROM PersonsContract WHERE Contract_id='".$CONTRACT."'";
	$cDATA = $DB->get_single_result($cSQL);	
	$PDATA['Contract_dateEntered'] = date("m/d/Y", $cDATA['Contract_dateEntered']);
	$PDATA['Contract_rep'] = array($cDATA['Contract_rep']);
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
	$PDATA['Contract_memTypeSelect'] = $cDATA['Contract_memTypeSelect'];
	//$PDATA['Contract_PostalPrimary'] = $cDATA['Contract_PostalPrimary'];
	$PDATA['Contract_fileID'] = $cDATA['Contract_fileID'];
	$contractStatus = $cDATA['Contract_status'];	
	$PDATA['Contract_package'] = $cDATA['Contract_package'];
	$PDATA['Contract_termsMonths'] = $cDATA['Contract_termsMonths'];
	
}
$packageOptions = $SALES->options_packages($PDATA['Contract_package']);

?>
<div class="m-content">
	<div class="m-portlet">
    	<div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        <i class="flaticon-file-1"></i> Agreement Generator
                    </h3>
                </div>
            </div>
        </div>
        <div class="m-portlet__body" id="contract_body_capture">
        	<form action="javascript:;">
            <input type="hidden" name="Contract_id" id="Contract_id" value="<?php echo $CONTRACT?>" />
            <input type="hidden" name="Person_id" id="Person_id" value="<?php echo $PERSON_ID?>" />
        	<div class="text-center"><img src="https://kelleher-international.com/inventory/images/header_logo10_14.jpg" /><br /><h4>MASTER CONTRACT</h4></div>
       	  <table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-bottom:10px;">
              <tr>
                <td width="33%">
                	<div class="input-group">
                    	<span class="input-group-addon">Date:</span>
                		<input type="text" class="form-control form-control-sm m-input" name="Contract_dateEntered" id="Contract_dateEntered" value="<?php echo $PDATA['Contract_dateEntered']?>" />
					</div>                        
				</td>
                <td width="34%">
                	<div class="input-group">
                    	<span class="input-group-addon">Type:</span>
                        <select class="form-control form-control-sm m-input" name="Contract_memTypeSelect" id="Contract_memTypeSelect">
                          <option value=""></option>
                            <option value="Full Client" <?php echo (($PDATA['Contract_memTypeSelect'] == 'Full Client')? 'selected':'')?>>Full Client</option>
                            <option value="Participating Client" <?php echo (($PDATA['Contract_memTypeSelect'] == 'Participating Client')? 'selected':'')?>>Participating Client</option>
                          <option value="Resource Client" <?php echo (($PDATA['Contract_memTypeSelect'] == 'Resource Client')? 'selected':'')?>>Resource Client</option>
                            <option value="Free Client" <?php echo (($PDATA['Contract_memTypeSelect'] == 'Free Client')? 'selected':'')?>>Free Client</option>
                        </select>
					</div>
                </td>                        
                <td align="right" width="33%">
                	<div class="input-group">
                    	<span class="input-group-addon">Rep:</span>
                		<select class="form-control form-control-sm m-input" name="Contract_rep" id="Contract_rep">
                        <?php echo $RECORD->options_userSelect($PDATA['Contract_rep'])?>
                        </select>
					</div> 
                </td>
              </tr>
              
              <tr>
                <td colspan="2">
                	<div class="input-group">
                    	<span class="input-group-addon">Package:</span>
                        <select class="form-control form-control-sm m-input" name="Contract_package" id="Contract_package">
                          <?php echo $packageOptions?>
                        </select>
					</div>                        
				</td>                  
                <td align="right" width="33%">
                	<div class="input-group">
                    	<span class="input-group-addon">Term (in months):</span>
                        <input type="number"  class="form-control form-control-sm m-input" name="Contract_termsMonths" id="Contract_termsMonths" value="<?php echo $PDATA['Contract_termsMonths']?>" /> 
					</div>
                </td>
              </tr>
            </table>
			<table width="650" border="1" cellspacing="0" cellpadding="0" align="center">
              <tr>
                <td colspan="6">
                	<div style="padding-top:10px;">&nbsp;Name <small>(Hereinafter "Client")</small>:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_name" id="Contract_name" value="<?php echo $PDATA['Contract_name']?>" />
                </td>
              </tr>
              <tr>
                <td colspan="3">
                    <div style="padding-top:10px;">&nbsp;Primary Address:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_AddressPrimary" id="Contract_AddressPrimary" value="<?php echo $PDATA['Contract_AddressPrimary']?>" />               
                </td>
                <td width="50%" colspan="3">
                	<div style="padding-top:10px;">&nbsp;Billing Address <small>(If Different)</small>:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_AddressBilling" id="Contract_AddressBilling" value="<?php echo $PDATA['Contract_AddressBilling']?>" />                
                </td>
              </tr>
              <tr>
                <td width="26%">
                	<div>&nbsp;City:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_CityPrimary" id="Contract_CityPrimary" value="<?php echo $PDATA['Contract_CityPrimary']?>" /> 
                </td>
                <td width="12%">
                	<div>&nbsp;State:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_StatePrimary" id="Contract_StatePrimary" value="<?php echo $PDATA['Contract_StatePrimary']?>" /> 
                </td>
                <td width="12%">
                	<div>&nbsp;Zip:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PostalPrimary" id="Contract_PostalPrimary" value="<?php echo $PDATA['Contract_PostalPrimary']?>" /> 
                </td>
                <td width="26%">
                	<div>&nbsp;City:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_CityBilling" id="Contract_CityBilling" value="<?php echo $PDATA['Contract_CityBilling']?>" /> 
                </td>
                <td width="12%">
                	<div>&nbsp;State:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_StateBilling" id="Contract_StateBilling" value="<?php echo $PDATA['Contract_StateBilling']?>" /> 
                </td>
                <td width="12%">
                	<div>&nbsp;Zip:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PostalBilling" id="Contract_PostalBilling" value="<?php echo $PDATA['Contract_PostalBilling']?>" /> 
                </td>
              </tr>
              <tr>
                <td colspan="3">
                	<div>&nbsp;Phone:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_Phone" id="Contract_Phone" value="<?php echo $PDATA['Contract_Phone']?>" />
                </td>
                <td colspan="3">
                	<div>&nbsp;Country:</div>
                	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_CountryPrimary" id="Contract_CountryPrimary" value="<?php echo $PDATA['Contract_CountryPrimary']?>" />
                </td>
              </tr>
              <tr>
                <td colspan="3">
                	<div>&nbsp;Date of Birth:</div>
               	  <input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_DOB" id="Contract_DOB" value="<?php echo $PDATA['Contract_DOB']?>" />
                </td>
                <td colspan="3">
                	<div>&nbsp;Driver's License #:</div>
               	  <input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_DLN" id="Contract_DLN" value="<?php echo $PDATA['Contract_DLN']?>" />
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
               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_MembershipType" id="Contract_MembershipType" value="<?php echo $PDATA['Contract_MembershipType']?>" />
                </td>
                <td width="50%">
                	<div>&nbsp;Contract Start Date <small>(Hereinafter ​"Effective Date​")</small>​:</div>
               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_Start" id="Contract_Start" value="<?php echo $PDATA['Contract_Start']?>" />
                </td>
              </tr>
              <tr>
                <td>
                	<div>&nbsp;Retainer Fee <small>(Hereinafter ​"Retainer Fee​")</small>:</div>
                    <div class="input-group m-input-group">
                    	<span class="input-group-addon"><i class="fa fa-usd"></i></span>
	               	  	<input type="number" class="form-control form-control-sm m-input m-input--solid" name="Contract_RetainerFee" id="Contract_RetainerFee" value="<?php echo $PDATA['Contract_RetainerFee']?>" />
					</div>                        
                </td>
                <td>
                	<div>&nbsp;Active Term:</div>
               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_Term" id="Contract_Term" value="<?php echo $PDATA['Contract_Term']?>" />
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
           	  	  <textarea class="form-control form-control-sm m-input m-input--solid" name="Contract_SpecialInst" id="Contract_SpecialInst"><?php echo $PDATA['Contract_SpecialInst']?></textarea>
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
               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PaymentAccountName" id="Contract_PaymentAccountName" value="<?php echo $PDATA['Contract_name']?>" />
                </td>
                <td width="25%">
                	<div>&nbsp;Payment Amount:</div>
                    <div class="input-group m-input-group">
                    	<span class="input-group-addon"><i class="fa fa-usd"></i></span>
               	  		<input type="number" class="form-control form-control-sm m-input m-input--solid" name="Contract_PaymentAmount" id="Contract_PaymentAmount" value="<?php echo $PDATA['Contract_PaymentAmount']?>" />
					</div>                        
                </td>
                <td width="25%">
                	<div>&nbsp;Date to Deposit:</div>
               	  	<input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PaymentDate" id="Contract_PaymentDate" value="<?php echo $PDATA['Contract_PaymentDate']?>" />
                </td>
              </tr>
              <tr>
                <td>
                	<div>&nbsp;Bank ACH Routing Number:</div>
           	  	  <input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PaymentRouting" id="Contract_PaymentRouting" value="<?php echo $PDATA['Contract_PaymentRouting']?>" />
                </td>
                <td colspan="2">
                	<div>&nbsp;Checking Account Number:</div>
           	  	  <input type="text" class="form-control form-control-sm m-input m-input--solid" name="Contract_PaymentAccountNum" id="Contract_PaymentAccountNum" value="<?php echo $PDATA['Contract_PaymentAccountNum']?>" />
                </td>
              </tr>
            </table>
          <table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:10px;">
              <tr>
                <td>
                	<div style="font-size:10px;">Client acknowledges having read and understood this Master Contract with the attached two-page Kelleher
International Terms and Conditions. Your signature on this Agreement guarantees your comprehension and acceptance
of all its terms, covenants and conditions. An executed copy transmitted by email or facsimile shall have the same effect
as the original. <strong>Client may cancel this membership agreement without penalty or obligation at any time prior to midnight
of the third business day following the date of Client signature (the "Effective Date") excluding Sundays and holidays. To
cancel this Agreement, mail or deliver a signed and dated notice or send a telegram with a simple statement of your
cancellation of the membership.</strong><br /><br />
Said notice shall be sent to:<br />Kelleher International, LLC. | ​145 Corte Madera Town Ctr. #422 | Corte Madera, CA | 94925-1209</div>
                </td>
              </tr>
            </table>
          <table width="650" border="0" cellspacing="0" cellpadding="0" align="center" style="margin-top:30px;">
              <tr>
                <td width="75%" style="border-bottom:#333 dashed 1px;">
                	<div>&nbsp;Client Signature:</div>
                	{{CLIENT_SIGNATURE}}
				</td>
                <td width="25%">
                	<div>&nbsp;Date:</div>
                    {{SIGNATURE_DATE}}
				</td>
              </tr>
              <tr>
              	<td colspan="2">&nbsp;</td>
			  </tr>                
            <tr>
                <td style="border-bottom: #333 dashed 1px;">
                	<div>&nbsp;Kelleher Signature:</div>
                	{{KH_SIGNATURE}}
				</td>
                <td>
                	<div>&nbsp;Date:</div>
                    {{SIGNATURE_DATE}}
				</td>
              </tr>
            </table>
            
            <hr />
            
            <div class="text-center"><strong>
            	KELLEHER INTERNATIONAL LLC MASTER<br />
            	CONTRACT TERMS & CONDITIONS
			</strong></div> 
            
          <table width="750" border="0" cellspacing="0" cellpadding="0" align="center">
              <?php 
			  $i=0;
			  for($i=0; $i<count($PDATA['Terms']); $i++): ?>
              <tr>
              	<?php if(in_array(54, $userPerms)): ?>
              	<td width="50" valign="top">
                	<label class="m-checkbox">
                        <input type="checkbox" class="includeTerm" checked="checked" id="term_text_<?php echo $i?>_check" value="term_text_<?php echo $i?>">
                        <span></span>
                    </label>
				</td>
                <?php else: ?>
                <td width="50">
                	<label class="m-checkbox" style="display:none;">
                        <input type="checkbox" class="includeTerm" checked="checked" id="term_text_<?php echo $i?>_check" value="term_text_<?php echo $i?>">
                        <span></span>
                    </label>                    
                </td>
                <?php endif; ?>                    
                <td width="650"><p class="term_text" data-id="<?php echo $i?>" id="term_text_<?php echo $i?>" style="font-size:11px;"><?php echo $PDATA['Terms'][$i]?></p></td>
                <?php if(in_array(54, $userPerms)): ?>
                <td width="50" valign="top" align="right">
                	<a href="javascript:editTermSpan('term_text_<?php echo $i?>', '<?php echo $i?>');"><i class="fa fa-edit"></i></a>
				</td>
                <?php endif; ?>
              </tr>
              <?php endfor; ?>
            </table>
            
            <hr />
            <table width="650" border="0" cellspacing="0" cellpadding="0" align="center">              
              <tr>
                <td colspan="2">
                	<div>&nbsp;ADDENDUM:</div>
           	  	  <textarea class="form-control form-control-sm m-input m-input--solid" name="Contract_Adendum" id="Contract_Adendum"><?php echo $PDATA['Contract_Adendum']?></textarea>
                </td>
              </tr>
              <tr>
              	<td colspan="2">&nbsp;</td>
				</tr>                
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
          </form>
		</div>
        <div class="m-portlet__foot">
            <div class="row align-items-center">
                <div class="col-lg-6 m--valign-middle">&nbsp;</div>
                <div class="col-lg-6 m--align-right">
                    <button type="button" class="btn btn-brand" onclick="saveContract()">
                        Save Agreement
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
				<h5 class="modal-title" id="sourceModalLabel"><i class="flaticon-file-1"></i> Contract Information</h5>
				<!--
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
                -->
			</div>
			<div class="modal-body">  
            	<?php if($contractStatus == 1): ?>
                <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-exclamation-2"></i>
                        <span></span>
                    </div>
                    <div class="m-alert__text">
                    	<?php 
						$contractURL = 'https://'.$_SERVER['SERVER_NAME'].'/view-contract.php?id='.$cDATA['Contract_Hash'];
						$contractTinyURL = $ENC->get_tiny_url($contractURL); 
						?>
                        <strong>This contract can be accessed via <a href="<?php echo $contractTinyURL?>" target="_blank">the following URL</a>:</strong><br />
                        <textarea class="form-control m-input" id="embedCode"><?php echo $contractTinyURL?></textarea>
                    </div>
                </div> 
                <?php else: ?>
                <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-success alert-dismissible fade show" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-exclamation-2"></i>
                        <span></span>
                    </div>
                    <div class="m-alert__text">
                    	<?php if($PDATA['Contract_fileID'] == 0): ?>
                        This contract has been signed and completed. See the profile's document section to download and view the PDF version of this contract.
                        <?php else: ?>
                    	This contract has been signed and completed. <a href="/getFile.php?DID=<?php echo $PDATA['Contract_fileID']?>" class="m-link" target="_blank">Click here</a> to view the signed PDF contract
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?> 
                
				<?php if($contractStatus == 1): ?>
                <div class="text-center">          	
                	<button type="button" class="btn btn-success" onclick="$('#blank-form').submit();">Download Blank Contract <i class="fa fa-download"></i></button>
                </div>
                <?php endif; ?> 
                              
                <div id="contractHistoryTable">
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
                        <?php
						$ch_sql = "SELECT * FROM PersonsContractsHistory WHERE Contract_id='".$CONTRACT."' ORDER BY ContractHistory_date DESC";
						$ch_snd = $DB->get_multi_result($ch_sql);
						if(!isset($ch_snd['empty_result'])):
							foreach($ch_snd as $ch_dta):
							?>
                            <tr>
                            	<td><?php echo date("m/d/y h:ia", $ch_dta['ContractHistory_date'])?></td>
                                <td><?php echo $ch_dta['ContractHistory_action']?></td>
                                <td><?php echo $ch_dta['ContractHistory_city']?> <?php echo $ch_dta['ContractHistory_region']?></td>
                                <td><?php echo $ch_dta['ContractHistory_ip']?></td>
                                <td><?php echo (($ch_dta['ContractHistory_userID'] == 0)? '&nbsp;':$RECORD->get_userName($ch_dta['ContractHistory_userID']))?></td>
							</tr>
                            <?php
							endforeach;
						else:
							?>
                            <tr>
                            	<td colspan="5">No History</td>                            
                            </tr>
                            <?php
						endif;
						?>                       
                        </tbody>
                    </table>                
                </div>
    

			</div>
			<div class="modal-footer">
            	<a href="/profile/<?php echo $PERSON_ID?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
				<?php if($contractStatus == 1): ?>
                <button type="button" class="btn btn-danger" onclick="removeContract()">Delete Agreement</button>
                <button type="button" class="btn btn-primary" onclick="javascript:sendQuickEmail('<?php echo $PERSON_ID?>', '269', 'Are you sure you want to send this record an email with a link to this contract?', 'Contract Agreement Link', '#agreementLinkURL', '<?php echo $contractTinyURL?>')"><i class="fa fa-envelope"></i> Send Email</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Edit Agreement</button>
                <button type="button" class="btn btn-secondary" onclick="copyToClipboard()">Copy URL to Clipboard</button>
                <?php endif; ?>                
			</div>
		</div>
	</div>
</div>

<form action="/submit-contract.php" target="_blank" id="blank-form">  
<input type="hidden" name="contract-hash" value="<?php echo $cDATA['Contract_Hash']?>" />             	
<input type="hidden" name="send-blank" value="1" />
<input type="hidden" name="send-file" value="1" />
</form>
<script>
$(document).ready(function(e) {
    $("#Contract_dateEntered").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#Contract_dateEntered').val(e.format('mm/dd/yyyy'))
	});

	$("#Contract_Start").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#Contract_Start').val(e.format('mm/dd/yyyy'))
	});
	
	$("#Contract_PaymentDate").datepicker({
		todayHighlight: !0,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	}).on('changeDate', function(e){
		$('#Contract_PaymentDate').val(e.format('mm/dd/yyyy'))
	});
	autosize($('#Contract_Adendum'));
	<?php if($CONTRACT != 0): ?>
	$('#contractInfoModal').modal('show');
	<?php endif; ?>
	
	$("#m_inputmask_7").inputmask("â‚¬ 999.999.999,99", {
            numericInput: !0
        })	
});
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
function genBlankContract() {
		
	
}
function removeContract() {
	var choice = confirm('Are you sure you want to delete contract? This action cannot be undone.');
	if(choice) {
		$.post('/ajax/contract_generation.php?action=deleteContract', {
			cid		:'<?php echo $CONTRACT?>',
			kiss_token: '<?php echo $SESSION->createToken()?>'
		}, function(data) {
			alert('Contract Deleted');
			document.location.href='/profile/<?php echo $PERSON_ID?>';
		});
	}	
}
function copyToClipboard() {
	$("#embedCode").select();
    document.execCommand('copy');
}
function saveContract() {
	var formData = $('#contract_body_capture form').serializeArray();	
	$('.term_text').each(function() {
		var id = $(this).attr('data-id');
		if($('#term_text_'+id+'_check').is(':checked')) {	
			formData.push({name: 'terms[]', value: $(this).html()});
		}
	});
	
	var formError = 0;
	var formErrorText = '';
	if($('#Contract_memTypeSelect').val() == '') {
		formError = 1;
		formErrorText += 'No Membership Type Selected \n';
	}
	if($('#Contract_MembershipType').val() == '') {
		formError = 1;
		formErrorText += 'No Membership Type entered \n';
	}
	if($('#Contract_Start').val() == '') {
		formError = 1;
		formErrorText += 'No Contract Start Date entered \n';
	}	
	if($('#Contract_RetainerFee').val() == '') {
		formError = 1;
		formErrorText += 'No Retainer Fee entered \n';
	}	
	if($('#Contract_Term').val() == '') {
		formError = 1;
		formErrorText += 'No Contract Term entered \n';
	}
	if($('#Contract_package').val() == '') {
		formError = 1;
		formErrorText += 'No Package Selected \n';
	}
	
	if(formError == 1) {
		alert(formErrorText);
	} else {	
		//console.log(formData);
		$.post('/ajax/contract_generation.php?action=createContract', formData, function(data) {
			console.log(data);
			document.location.href='/contractgen/<?php echo $PERSON_ID?>/'+data.cid;
		}, "json");
	}
}
function editTermSpan(text_div, id) {
	var currentText = $('#'+text_div).html();
	var divFormID = 'term_text_form_'+id;
	$('#'+text_div).html('<textarea class="form-control form-control-sm m-input m-input--solid" name="'+divFormID+'" id="'+divFormID+'">'+currentText+'</textarea><div class="text-right"><button type="button" class="btn btn-sm btn-default" onclick="saveTermSpan(\''+divFormID+'\', \''+id+'\')">Save</button></div>');
	t = $('#'+divFormID);
	autosize(t);
}
function saveTermSpan(form_div, id) {
	var currentText = $('#'+form_div).val();
	//alert(currentText);
	var divTextID = 'term_text_'+id;
	$('#'+divTextID).html(currentText);	
}
</script>
