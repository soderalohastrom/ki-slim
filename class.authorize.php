<?php
//include("../db.connection.php");

class Authorize {
    var $transactionKey = '';
    var $loginId = '';
	var $officeId;

    var $response;
    var $auth_net_url = '';
   	
   	// Gateway information for recurring payments
 	var $recurringHost = '';
 	var $recurringPath = '/xml/v1/request.api';
    
    var $testMode;				//set to true if using testing
	//  Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
	//var $auth_net_url = "https://secure.authorize.net/gateway/transact.dll";
	
	var $recurResultCode;		//Code signalling whether or not the transaction was successful. Returns as 'Ok' or 'Error'
	var $recurResponseCode;		//The code that represents the reason for the error in a recurring payment transaction
	var $recurResponseText;		//A text description of the error for recurring payment transactions
	var $recurInterval;			//The interval between recurring payments
	var $recurIntervalUnit;		//The unit of measurement for the recurring interval, must be 'days' or 'months'
	var $recurStartDate;		//The date of the first payment, format must be YYYY-MM-DD
	var $recurOccurences;		//The total number of payments to make
	
	var $responseCode;			//The overall status of the transaction 1 = Approved,2 = Declined,3 = Error,4 = Held for Review
	var $responseSubcode;		//A code used by the payment gateway for internal transaction tracking
	var $responseReasonCode;	//A code that represents more details about the result of the transaction
	var $responseReasonText;	//A brief description of the result, which corresponds with the response reason code
	var $authorizationCode;		//The authorization or approval code
	var $avsResponse;			//The Address Verification Service (AVS) response code
								/* 
									A = Address (Street) matches, ZIP does not
									B = Address information not provided for AVS check
									E = AVS error
									G = Non-U.S. Card Issuing Bank
									N = No Match on Address (Street) or ZIP
									P = AVS not applicable for this transaction
									R = Retry � System unavailable or timed out
									S = Service not supported by issuer
									U = Address information is unavailable
									W = Nine digit ZIP matches, Address (Street) does not
									X = Address (Street) and nine digit ZIP match
									Y = Address (Street) and five digit ZIP match
									Z = Five digit ZIP matches, Address (Street) does not
								*/
	var $transactionId;			//The payment gateway assigned identification number for the transaction
	var $subscriptionId;		//The payment gateway assigned identification number for the subscription
	var $transactionMethod;		//CC or ECHECK
	var $transactionType;		//AUTH_CAPTURE,AUTH_ONLY,CAPTURE_ONLY,CREDIT, PRIOR_AUTH_CAPTURE, VOID
	var $firstName;				//The first name associated with the customer�s billing address
	var $lastName;				//The last name associated with the customer�s billing address
	var $company;				//The company associated with the customer�s billing address
	var $address;				//The customer�s billing address
	var $city;					//The city of the customer's billing address
	var $state;					//The state of the customer�s billing address	
	var $postalCode;			//The ZIP code of the customer�s billing address
	var $country;				//The country of the customer�s billing address
	var $phone;					//The phone number associated with the customer�s billing address
	var $email;					//The customer's valid email address 
	var $amount;				//The amount of the transaction
	var $description;			//The transaction description
	
	var $cardCodeResponse;		//The card code verification (CCV) response code
								/*
									M = Match
									N = No Match
									P = Not Processed
									S = Should have been present
									U = Issuer unable to process request
								*/
	var $cardholderAuthenticationVerificationResponse; //The cardholder authentication verification response code
														/*
															Blank or not present  =  CAVV not validated
															0 = CAVV not validated because erroneous data was submitted
															1 = CAVV failed validation
															2 = CAVV passed validation
															3 = CAVV validation could not be performed; issuer attempt incomplete
															4 = CAVV validation could not be performed; issuer system error
															5 = Reserved for future use
															6 = Reserved for future use
															7 = CAVV attempt � failed validation � issuer available (U.S.-issued card/non-U.S acquirer)
															8 = CAVV attempt � passed validation � issuer available (U.S.-issued card/non-U.S. acquirer)
															9 = CAVV attempt � failed validation � issuer unavailable (U.S.-issued card/non-U.S. acquirer)
															A = CAVV attempt � passed validation � issuer unavailable (U.S.-issued card/non-U.S. acquirer)
															B = CAVV passed validation, information only, no liability shift
														*/							
	var $creditCardNumber;

	/**
	 * Authorize::__construct()
	 * 
	 * @param int $officeId - the office id
	 * @param bool $testMode - set to true for test transactions
	 * @param bool $devAccount - set to true if authorize account is a sandbox account
	 * @return void
	 */
	public function __construct($login, $key, $testMode=false, $devAccount=false){
		$this->officeId = $officeId;
		$this->personId = $person_id;
		if($testMode){
			$this->auth_net_url = "https://secure.authorize.net/gateway/transact.dll";//"https://test.authorize.net/gateway/transact.dll";
			$this->recurringHost = "apitest.authorize.net";
			$this->testMode = 1;
			
			if($devAccount){
				$this->auth_net_url = "https://test.authorize.net/gateway/transact.dll";
			}			
		}
		else {
			$this->auth_net_url = "https://secure.authorize.net/gateway/transact.dll";
			$this->recurringHost = "api.authorize.net";
			$this->testMode = 0;
		}
		$this->loginId = $login;      
		$this->transactionKey = $key;
    }
	
	
	/**
	 * Authorize::sendTransaction()
	 * 
	 * @param array $transactionInfoArray
	 * @return bool
	 */
	public function sendTransaction($transactionInfoArray, $items=array(), $subscription=0){
		$fields = '';
		$keySet = false;
		$loginSet = false;
		foreach($transactionInfoArray as $key => $value ){
			if($key == 'x_tran_key') {
				if ($value == '') {
					$value = $this->transactionKey;
				} else {
					$this->transactionKey = $value;	
				}
				$keySet = true;
			}
			
			if($key == 'x_login') {
				if($value == '') {
					$value = $this->loginId;
				} else {
					$this->loginId = $value;
				}
				$loginSet = true;
			}

            $fields .= "$key=" . urlencode( $value ) . "&";
		}

		if (!$loginSet) {
            $fields .= 'x_login='.urlencode($this->loginId).'&';
		}
		if (!$keySet) {		
			$fields .= 'x_tran_key='.urlencode($this->transactionKey);
		}
		$this->expiration = $transactionInfoArray['x_exp_date'];
		$this->svn = $transactionInfoArray['x_card_code'];

		//echo '<h1>'.$fields.'</h1>';
		//echo "Key:".$this->transactionKey."<br>\n";
		//echo "Login:".$this->loginId;
		//echo "<b>01: Post the transaction (see the code for specific information):</b><br>";
		$ch = curl_init($this->auth_net_url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "&" )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$this->response = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		
		$this->parseResponse($this->response);
		$this->logResponseString();
		
		if($this->responseCode == '1'){
			if ($_POST['base_table'] == 'Persons') {
				$person_id = $_POST['record_id'];
				$company_id = 0;
			} elseif($_POST['base_table'] == 'Companies') {
				$person_id = 0;
				$company_id = $_POST['record_id'];
			}
			$lastFour = substr($transactionInfoArray['x_card_num'],-4);
			//echo "Log Transaction\n";
			$this->logTransaction($person_id, $company_id, $transactionInfoArray['x_card_num'],$lastFour, $items, $subscription);
		}		
		return ($this->responseCode == '1') ? true : false;
	}
	
	
	/**
	 * Authorize::verifyFunds()
	 * 
	 * @param array $transactionInfoArray
	 * @return bool
	 */
	public function verifyFunds($transactionInfoArray){
		$fields = '';
		
		$transactionInfoArray["x_version"] = "3.1";
		$transactionInfoArray["x_delim_char"] = "|";
		$transactionInfoArray["x_delim_data"] = "TRUE";
		$transactionInfoArray["x_relay_response"] = "FALSE";		
		
		foreach($transactionInfoArray as $key => $value ){
                    //skip over any of the authentication info passed in this array
                    if($key == 'x_tran_key' || $key == 'x_login' || $key == 'x_type' || $key == 'x_email'){
                        continue;
                    }

                    $fields .= "$key=" . urlencode( $value ) . "&";
		}

        $fields .= 'x_login='.urlencode($this->loginId).'&';
        $fields .= 'x_tran_key='.urlencode($this->transactionKey).'&';
        $fields .= 'x_type='.urlencode('AUTH_ONLY');

		//echo '<h1>'.$fields.'</h1>';			
		//echo "<b>01: Post the transaction (see the code for specific information):</b><br>";
		$ch = curl_init($this->auth_net_url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "&" )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$this->response = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		
		$this->parseResponse($this->response);
		$this->logResponseString();
		
		return ($this->responseCode == '1') ? true : false;
	}
	
	
	
	/**
	 *  Send a recurring transaction to Authorize.net 
	 *  formatted as an XML string
	 */
	 public function sendRecurringTransaction($transactionInfoArray) {
		//$verifyFunds = $this->verifyFunds($transactionInfoArray);
		//if(!$verifyFunds){
		//	return false;
		//}		
		
		$this->description			= $transactionInfoArray['x_description'];
		$this->recurInterval		= $transactionInfoArray['x_interval_length'];
		$this->recurIntervalUnit	= $transactionInfoArray['x_interval_unit'];
		$this->recurStartDate		= $transactionInfoArray['x_start_date'];
		$this->recurOccurences		= $transactionInfoArray['x_total_occurences'];
		$this->amount				= $transactionInfoArray['x_amount'];
		$this->email				= $transactionInfoArray['x_email'];
		$this->firstName			= $transactionInfoArray['x_first_name'];
		$this->lastName				= $transactionInfoArray['x_last_name'];
		$this->address				= $transactionInfoArray['x_address'];
		$this->city					= $transactionInfoArray['x_city'];
		$this->state				= $transactionInfoArray['x_state'];
		$this->postalCode			= $transactionInfoArray['x_zip'];
		$this->country				= $transactionInfoArray['x_country'];
		$this->phone				= $transactionInfoArray['x_phone'];
		
		$lastFourDigitsOfCreditCard = '';
		
		$xmlContent =
			        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			        "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
			        "<merchantAuthentication>".
			        "<name>" . $this->loginId . "</name>".
			        "<transactionKey>" . $this->transactionKey . "</transactionKey>".
			        "</merchantAuthentication>".
					"<subscription>";
  		if($this->description != '') {
  			$xmlContent .= "<name>". $this->description ."</name>";
  		}
  		$xmlContent .=      
			        "<paymentSchedule>".
			        "<interval>".
			        "<length>". $this->recurInterval ."</length>".
			        "<unit>". $this->recurIntervalUnit ."</unit>".
			        "</interval>".
			        "<startDate>" . $this->recurStartDate . "</startDate>".
			        "<totalOccurrences>". $this->recurOccurences . "</totalOccurrences>".
			        "</paymentSchedule>".
			        "<amount>". $this->amount ."</amount>".
			        "<payment>";
  		// For credit card transactions
    	if($transactionInfoArray['x_card_num'] != '' && $transactionInfoArray['x_exp_date'] != '') {
			$xmlContent .= 
			        "<creditCard>".
			        "<cardNumber>" . $transactionInfoArray['x_card_num'] . "</cardNumber>".
			        "<expirationDate>" . $transactionInfoArray['x_exp_date'] . "</expirationDate>".
			        "</creditCard>".
			        "</payment>";
			$lastFourDigitsOfCreditCard = substr($transactionInfoArray['x_card_num'],-4);      
			        
			        
   		// For E-Check transactions
     	} elseif ($transactionInfoArray['x_account_type'] != '' && $transactionInfoArray['x_routing_num'] != '') {
     		$xmlContent .= 
     			"<bankAccount>".
     			"<accountType>" . $transactionInfoArray['x_account_type'] . "</accountType>". 			//checking, businessChecking, savings
     			"<routingNumber>" . $transactionInfoArray['x_routing_num'] . "</routingNumber>". 		//9 digits
     			"<accountNumber>" . $transactionInfoArray['x_account_num'] . "</accountNumber>". 		//5-17 digits
     			"<nameOnAccount>" . $transactionInfoArray['x_name_on_account'] . "</nameOnAccount>".	//full name associated with the account
     			"<echeckType>" . $transactionInfoArray['x_echeck_type'] . "</echeckType>".				//For checking or savings: PPD or WEB, for busChecking: CCD
     			"</bankAccount>".
     			"</payment>";
     	}
		if($this->email != '') {	        
			$xmlContent .=  "<customer><email>". $this->email ."</email></customer>";
		}
		$xmlContent .=	        
			        "<billTo>".
			        "<firstName>". $this->firstName . "</firstName>".
			        "<lastName>" . $this->lastName	 . "</lastName>".
			        "<address>" . $this->address . "</address>".
			        "<city>" . $this->city . "</city>".
			        "<state>". $this->state ."</state>".
			        "<zip>" . $this->postalCode . "</zip>".
			        "</billTo>".
			        "</subscription>".
			        "</ARBCreateSubscriptionRequest>";
		
		
		$posturl = "https://" . $this->recurringHost . $this->recurringPath;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$this->response = curl_exec($ch);
		curl_close ($ch);
		
		$this->parseRecurringResponse($this->response);
		$this->logRecurringResponseString();
		
		if($this->recurResultCode == 'Ok'){
			$this->logRecurringTransaction($lastFourDigitsOfCreditCard);
		}
		
		return ($this->recurResultCode == 'Ok') ? true : false;			        
	 }
	 
	 
	 /**
	  * Authorize::cancelRecurringPayment()
	  * 
	  * @param int $subscriptionId
	  * @return
	  */
	 public function cancelRecurringPayment($subscriptionId) {
	 	$xmlContent =
	        "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
	        "<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
	        "<merchantAuthentication>".
	        "<name>" . $this->loginId . "</name>".
	        "<transactionKey>" . $this->transactionKey . "</transactionKey>".
	        "</merchantAuthentication>" .
	        "<subscriptionId>" . $subscriptionId. "</subscriptionId>".
	        "</ARBCancelSubscriptionRequest>";
	        
   		$posturl = "https://" . $this->recurringHost . $this->recurringPath;
   		
   		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$this->response = curl_exec($ch);
		curl_close ($ch);
		
		$this->parseRecurringResponse($this->response);
		
		return ($this->recurResultCode == 'Ok') ? true : false;
	 }
	 
	 /**
	 *  Update credit card info for a recurring payment with Authorize.net
	 *  User must provide the subscriptionId, CC number and CC expiration date
	 */
	 public function updateRecurringPayment($subscriptionId, $transactionInfoArray) {
	 	
	 	$this->description			= $transactionInfoArray['x_description'];
		$this->recurInterval		= $transactionInfoArray['x_interval_length'];
		$this->recurIntervalUnit	= $transactionInfoArray['x_interval_unit'];
		$this->recurStartDate		= $transactionInfoArray['x_start_date'];
		$this->recurOccurences		= $transactionInfoArray['x_total_occurences'];
		$this->amount				= $transactionInfoArray['x_amount'];
		$this->email				= $transactionInfoArray['x_email'];
		$this->firstName			= $transactionInfoArray['x_first_name'];
		$this->lastName				= $transactionInfoArray['x_last_name'];
		$this->address				= $transactionInfoArray['x_address'];
		$this->city					= $transactionInfoArray['x_city'];
		$this->state				= $transactionInfoArray['x_state'];
		$this->postalCode			= $transactionInfoArray['x_zip'];
		$this->country				= $transactionInfoArray['x_country'];
		$this->phone				= $transactionInfoArray['x_phone'];
		
		$lastFourDigitsOfCreditCard = '';
		
		$xmlContent =
			        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			        "<ARBUpdateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				        "<merchantAuthentication>".
					        "<name>" . $this->loginId . "</name>".
					        "<transactionKey>" . $this->transactionKey . "</transactionKey>".
				        "</merchantAuthentication>".
				        "<subscriptionId>".$subscriptionId."</subscriptionId>".
						"<subscription>";
					  		if($this->description != '') {
					  			$xmlContent .= "<name>". $this->description ."</name>";
					  		}
  		$xmlContent .=      
					        "<paymentSchedule>".
						        //"<interval>".
							    //    "<length>".$this->recurInterval."</length>".
							    //    "<unit>". $this->recurIntervalUnit ."</unit>".
						        //"</interval>".
						        "<startDate>" . $this->recurStartDate . "</startDate>".
						        "<totalOccurrences>". $this->recurOccurences . "</totalOccurrences>".
						    "</paymentSchedule>".
						    "<amount>". $this->amount ."</amount>";
					        
  		// For credit card transactions
    	if($transactionInfoArray['x_card_num'] != '' && $transactionInfoArray['x_exp_date'] != '') {
			$xmlContent .= 
					"<payment>".
				        "<creditCard>".
				        "<cardNumber>" . $transactionInfoArray['x_card_num'] . "</cardNumber>".
				        "<expirationDate>" . $transactionInfoArray['x_exp_date'] . "</expirationDate>".
				        "</creditCard>".
			        "</payment>";
			$lastFourDigitsOfCreditCard = substr($transactionInfoArray['x_card_num'],-4);      
			        
			        
   		// For E-Check transactions
     	} elseif ($transactionInfoArray['x_account_type'] != '' && $transactionInfoArray['x_routing_num'] != '') {
     		$xmlContent .= 
     			"<payment>".
					"<bankAccount>".
	     			"<accountType>" . $transactionInfoArray['x_account_type'] . "</accountType>". 			//checking, businessChecking, savings
	     			"<routingNumber>" . $transactionInfoArray['x_routing_num'] . "</routingNumber>". 		//9 digits
	     			"<accountNumber>" . $transactionInfoArray['x_account_num'] . "</accountNumber>". 		//5-17 digits
	     			"<nameOnAccount>" . $transactionInfoArray['x_name_on_account'] . "</nameOnAccount>".	//full name associated with the account
	     			"<echeckType>" . $transactionInfoArray['x_echeck_type'] . "</echeckType>".				//For checking or savings: PPD or WEB, for busChecking: CCD
	     			"</bankAccount>".
     			"</payment>";
     	}
		if($this->email != '') {	        
			$xmlContent .=  "<customer><email>". $this->email ."</email></customer>";
		}
		$xmlContent .=	        
			        "<billTo>".
			        "<firstName>". $this->firstName . "</firstName>".
			        "<lastName>" . $this->lastName	 . "</lastName>".
			        "<address>" . $this->address . "</address>".
			        "<city>" . $this->city . "</city>".
			        "<state>". $this->state ."</state>".
			        "<zip>" . $this->postalCode . "</zip>".
			        "</billTo>".
			        "</subscription>".
			        "</ARBUpdateSubscriptionRequest>";
		
		
		$posturl = "https://" . $this->recurringHost . $this->recurringPath;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$this->response = curl_exec($ch);
		curl_close ($ch);
		
		$this->parseRecurringResponse($this->response);
		$this->logRecurringResponseString();
			
		return ($this->recurResultCode == 'Ok') ? true : false;
	 }
	 
	 
	 
	 /**
	  * Authorize::getSubscriptionStatus()
	  * 
	  * @param int $subscriptionId
	  * @return void
	  */
	 public function getSubscriptionStatus($subscriptionId){
	 	$xmlContent = 
	 	"<?xml version=\"1.0\" encoding=\"utf-8\"?>".
		"<ARBGetSubscriptionStatusRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
			"<merchantAuthentication>".
				"<name>" . $this->loginId . "</name>".
				"<transactionKey>".$this->transactionKey."</transactionKey>".
			"</merchantAuthentication>".
			"<refId>Sample</refId>".
			"<subscriptionId>".$subscriptionId."</subscriptionId>".
		"</ARBGetSubscriptionStatusRequest>";
		
		$posturl = "https://" . $this->recurringHost . $this->recurringPath;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$this->response = curl_exec($ch);
		curl_close ($ch);
		
		$this->parseRecurringResponse($this->response);
		if($this->recurResultCode == 'Ok'){
			return $this->substring_between($this->response,'<Status>','</Status>');
		}
		else{
			return 'Error Retrieving Status: '.$this->recurResponseCode.' - '.$this->recurResponseText;
		}		
	 }
	 
	 
	 /**
	 *  Parse the response from Authorize.net for recurring payments
	 */
	 public function parseRecurringResponse($responseString) {
		$this->recurResultCode		= $this->substring_between($responseString,'<resultCode>','</resultCode>');
		$this->recurResponseCode	= $this->substring_between($responseString,'<code>','</code>');
		$this->recurResponseText	= $this->substring_between($responseString,'<text>','</text>');
		$this->subscriptionId 		= $this->substring_between($responseString,'<subscriptionId>','</subscriptionId>');
	}
	
	
	/**
	 *  Helper function for parsing the response string for recurring payments
	 */
	private function substring_between($haystack,$start,$end) {
		if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) {
			return false;
		} else {
			$start_position = strpos($haystack,$start)+strlen($start);
			$end_position = strpos($haystack,$end);
			return substr($haystack,$start_position,$end_position-$start_position);
		}
	}
	
	
	public function parseResponse($responseString){
		$responsePieces = explode('|',$responseString);
		$this->responseCode = $responsePieces[0];
		$this->responseSubcode = $responsePieces[1];
		$this->responseReasonCode = $responsePieces[2];
		$this->responseReasonText = $responsePieces[3];
		$this->authorizationCode = $responsePieces[4];
		$this->avsResponse = $responsePieces[5];
		$this->transactionId = $responsePieces[6];
		$this->description = $responsePieces[8];
		$this->amount = $responsePieces[9];
		$this->transactionMethod = $responsePieces[10];
		$this->transactionType = $responsePieces[11];
		$this->firstName = $responsePieces[13];
		$this->lastName = $responsePieces[14];
		$this->company = $responsePieces[15];
		$this->address = $responsePieces[16];
		$this->city = $responsePieces[17];
		$this->state = $responsePieces[18];
		$this->postalCode = $responsePieces[19];
		$this->country = $responsePieces[20];
		$this->phone = $responsePieces[21];
		$this->email = $responsePieces[23];
		$this->cardCodeResponse = $responsePieces[38];
		$this->creditCardNumber = $responsePieces[50];
	}
	
	function getError(){
		//2 = Declined,3 = Error,4 = Held for Review
		switch($this->responseCode){
			case '2':
				$errorMessage = 'Transaction Declined: '.$this->responseReasonText;
				break;
			case '3':
				$errorMessage = 'Transaction Error: '.$this->responseReasonText;
				break;
			case '4':
				$errorMessage = 'Transaction Held for Review: '.$this->responseReasonText;
				break;
			default:
				$errorMessage = 'Error: '.$this->responseCode.$this->recurResponseCode.' - '.$this->responseReasonText.$this->recurResponseText;
				
		}
		return $errorMessage;
	}
	
	public function encrypt($encrypt) {
		//global $key;
		$key = "Jack and Diane";
		if (trim($encrypt) != "") {
			$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv);
			$encode = base64_encode($passcrypt);
		}
		else {
			$encode = $encrypt;
		}
		return $encode;
	}
	
	public function logTransaction($person_id=0, $company_id=0, $CardNumber='0',$lastFourDigitsOfCreditCard='', $item_array=array(), $subscription=0){
		global $DB;
		$CardType = credit_card::identify($CardNumber);
		$sql = "INSERT INTO Transactions "
					." (Transactions_transactionId,Transactions_description,Transactions_amount, Transactions_firstName,Transactions_lastName,Transactions_address,Transactions_city,Transactions_state, "
					." Transactions_postalCode, Transactions_country, Transactions_phone, Transactions_company, Transactions_email, Transactions_responseString,Transactions_cardType,Transactions_time, "
					." Transactions_type,Transactions_lastFour ) "
					." VALUES ( "
						." '".$DB->mysqli->escape_string($this->transactionId)."' " //Transactions_transactionId
						." ,'".$DB->mysqli->escape_string($this->description)."' " //Transactions_description						
						." ,'".$DB->mysqli->escape_string($this->amount)."' " //Transactions_amount						
						." ,'".$DB->mysqli->escape_string($this->firstName)."' " //Transactions_firstName
						." ,'".$DB->mysqli->escape_string($this->lastName)."' " //Transactions_lastName
						." ,'".$DB->mysqli->escape_string($this->address)."' " //Transactions_address						
						." ,'".$DB->mysqli->escape_string($this->city)."' " //Transactions_city
						." ,'".$DB->mysqli->escape_string($this->state)."' " //Transactions_state
						." ,'".$DB->mysqli->escape_string($this->postalCode)."' " //Transactions_postalCode
						." ,'".$DB->mysqli->escape_string($this->country)."' " //Transactions_country
						." ,'".$DB->mysqli->escape_string($this->phone)."' " //Transactions_phone
						." ,'".$DB->mysqli->escape_string($this->company)."' " //Transactions_company	
						." ,'".$DB->mysqli->escape_string($this->email)."' " //Transactions_email							
						." ,'".$DB->mysqli->escape_string($this->response)."' " //Transactions_responseString
						." ,'".$DB->mysqli->escape_string($CardType)."' " //Credit Card Type
						." ,'".time()."' " //Transactions_time
						." ,'1' " //Transactions_type
						." ,'".$DB->mysqli->escape_string($lastFourDigitsOfCreditCard)."' " //Transactions_lastFour
					.")";
		//mysql_query($sql) or die(mysql_error());
		$DB->mysqli->query($sql);
		
		$fields[] = 'person_id';
		$fields[] = 'company_id';
		$fields[] = 'transaction_date';
		$fields[] = 'transaction_amount';
		$fields[] = 'transaction_description';
		$fields[] = 'transaction_fname';
		$fields[] = 'transaction_lname';
		$fields[] = 'transaction_address';
		$fields[] = 'transaction_city';
		$fields[] = 'transaction_state';
		$fields[] = 'transaction_postal';
		$fields[] = 'transaction_country';
		$fields[] = 'transaction_num';
		$fields[] = 'transaction_last4';
		$fields[] = 'transaction_exp';
		$fields[] = 'transaction_svn';
		$fields[] = 'transaction_transID';
		$fields[] = 'transaction_type';
		$fields[] = 'transaction_method';
		$fields[] = 'transaction_email';
		$fields[] = 'transaction_subject';
		$fields[] = 'transaction_rcvd';
		$fields[] = 'transaction_items';
		$fields[] = 'transaction_subscription';
			
		$values[] = "'".$person_id."'";
		$values[] = "'".$company_id."'";
		$values[] = "'".time()."'";			
		$values[] = "'".$this->amount."'";
		$values[] = "'".$DB->mysqli->escape_string($this->description)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->firstName)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->lastName)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->address)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->city)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->state)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->postalCode)."'";
		$values[] = "'".$DB->mysqli->escape_string($this->country)."'";
		$values[] = "'".$this->encrypt($CardNumber)."'";
		$values[] = "'".$lastFourDigitsOfCreditCard."'";
		$values[] = "'".$this->expiration."'";
		$values[] = "'".$this->svn."'";
		$values[] = "'".$this->transactionId."'";
		$values[] = "'".$DB->mysqli->escape_string($_POST['transaction_type'])."'";
		$values[] = "'".$DB->mysqli->escape_string($_POST['transaction_method'])."'";
		$values[] = "'".$DB->mysqli->escape_string($_POST['transaction_email'])."'";
		$values[] = "'".$DB->mysqli->escape_string($_POST['transaction_subject'])."'";
		$values[] = "'1'";
		if(count($item_array) != 0) {
			$items = $item_array;
		} else {		
			for($i=0; $i<count($_POST['item_description']); $i++) {
				$items[] = array(
					'description'	=> $_POST['item_description'][$i],
					'qty'			=> $_POST['item_qty'][$i],
					'unit_price'	=> $_POST['item_unitprice'][$i],
					'sub_total'		=> $_POST['item_subtotal'][$i]
				);	
			}
		}
		$values[] = "'".$DB->mysqli->escape_string(serialize($items))."'";
		$values[] = "'".$subscription."'";
		
		$ins_sql = "INSERT INTO smpl_transactions (".implode(",", $fields).") VALUES(".implode(",", $values).")";
		//echo $ins_sql;
		$DB->mysqli->query($ins_sql);
		$trans_id = $DB->mysqli->insert_id;
		$return['sql'] = $ins_sql;
		$return['trans_id'] = $this->transactionId;
		$return['result'] = true;
		$this->dbID = $trans_id;
	}
	
	
	public function logResponseString(){
		global $DB;
		$sql = "INSERT INTO TransactionLog "
					." (TransactionLog_transactionId,TransactionLog_responseCode, TransactionLog_responseReasonCode, TransactionLog_responseReasonText, TransactionLog_authorizationCode, "
					." TransactionLog_avsResponse, TransactionLog_amount, TransactionLog_firstName,TransactionLog_lastName,TransactionLog_responseString,TransactionLog_testMode,TransactionLog_time,TransactionLog_type) "
					." VALUES ( "
						." '".$DB->mysqli->escape_string($this->transactionId)."' " //TransactionLog_transactionId						
						." ,'".$DB->mysqli->escape_string($this->responseCode)."' " //TransactionLog_responseCode
						." ,'".$DB->mysqli->escape_string($this->responseReasonCode)."' " //TransactionLog_responseReasonCode
						." ,'".$DB->mysqli->escape_string($this->responseReasonText)."' " //TransactionLog_responseReasonText
						." ,'".$DB->mysqli->escape_string($this->authorizationCode)."' " //TransactionLog_authorizationCode
						." ,'".$DB->mysqli->escape_string($this->avsResponse)."' " //TransactionLog_avsResponse
						." ,'".$DB->mysqli->escape_string($this->amount)."' " //TransactionLog_amount						
						." ,'".$DB->mysqli->escape_string($this->firstName)."' " //TransactionLog_firstName
						." ,'".$DB->mysqli->escape_string($this->lastName)."' " //TransactionLog_lastName
						." ,'".$DB->mysqli->escape_string($this->response)."' " //TransactionLog_responseString
						." ,'".$DB->mysqli->escape_string($this->testMode)."' "//TransactionLog_testMode
						." ,'".time()."' " //TransactionLog_time
						." ,'1' "					
					.")";
		//echo $sql;					
		//mysql_query($sql) or die(mysql_error());
		$DB->mysqli->query($sql);
	}	
	
	
	
	/**
	 *  Log successful recurring payment transactions
	 */
	public function logRecurringTransaction($lastFourDigitsOfCreditCard=''){
		global $DB;
		$sql = "INSERT INTO RecurringTransactions "
					." (RecurringTransactions_subscriptionId, RecurringTransactions_recurInterval, RecurringTransactions_recurIntervalUnit, RecurringTransactions_recurStartDate, RecurringTransactions_recurOccurences, RecurringTransactions_description, RecurringTransactions_amount, RecurringTransactions_firstName, RecurringTransactions_lastName, RecurringTransactions_address, RecurringTransactions_city, RecurringTransactions_state,"
					." RecurringTransactions_postalCode, RecurringTransactions_country, RecurringTransactions_phone, RecurringTransactions_company, RecurringTransactions_email, RecurringTransactions_responseString,RecurringTransactions_time,RecurringTransactions_type, RecurringTransactions_lastFour) "
					." VALUES ( "
						." '".$DB->mysqli->query($sql);($this->subscriptionId)."' " //TransactionLog_subscriptionId			
						." ,'".$DB->mysqli->query($sql);($this->recurInterval)."' "
						." ,'".$DB->mysqli->query($sql);($this->recurIntervalUnit)."' "
						." ,'".$DB->mysqli->query($sql);($this->recurStartDate)."' "
						." ,'".$DB->mysqli->query($sql);($this->recurOccurences)."' "
						." ,'".$DB->mysqli->query($sql);($this->description)."' " //RecurringTransactions_description						
						." ,'".$DB->mysqli->query($sql);($this->amount)."' " //RecurringTransactions_amount						
						." ,'".$DB->mysqli->query($sql);($this->firstName)."' " //RecurringTransactions_firstName
						." ,'".$DB->mysqli->query($sql);($this->lastName)."' " //RecurringTransactions_lastName
						." ,'".$DB->mysqli->query($sql);($this->address)."' " //RecurringTransactions_address						
						." ,'".$DB->mysqli->query($sql);($this->city)."' " //RecurringTransactions_city
						." ,'".$DB->mysqli->query($sql);($this->state)."' " //RecurringTransactions_state
						." ,'".$DB->mysqli->query($sql);($this->postalCode)."' " //RecurringTransactions_postalCode
						." ,'".$DB->mysqli->query($sql);($this->country)."' " //RecurringTransactions_country
						." ,'".$DB->mysqli->query($sql);($this->phone)."' " //RecurringTransactions_phone
						." ,'".$DB->mysqli->query($sql);($this->company)."' " //RecurringTransactions_company	
						." ,'".$DB->mysqli->query($sql);($this->email)."' " //RecurringTransactions_email							
						." ,'".$DB->mysqli->query($sql);($this->response)."' " //RecurringTransactions_responseString
						." ,'".time()."' " //RecurringTransactions_time
						." ,'1' "
						." ,'".$DB->mysqli->query($sql);($lastFourDigitsOfCreditCard)."' " //RecurringTransactions_lastFour							
					.")";
		//mysql_query($sql) or die(mysql_error());
		$DB->mysqli->query($sql);
			
	}
	
	
	/**
	 *  Create transaction log entry for recurring payment transactions
	 */
	public function logRecurringResponseString(){
		$sql = "INSERT INTO TransactionLog "
					." (TransactionLog_transactionId, TransactionLog_recurResultCode, TransactionLog_responseReasonCode, TransactionLog_responseReasonText, TransactionLog_recurInterval, TransactionLog_recurIntervalUnit, TransactionLog_recurStartDate, TransactionLog_recurOccurences,  "
					." TransactionLog_amount, TransactionLog_firstName,TransactionLog_lastName,TransactionLog_responseString,TransactionLog_testMode,TransactionLog_time,TransactionLog_type) "
					." VALUES ( "
						." '".mysql_real_escape_string($this->subcriptionId)."' " //TransactionLog_subscriptionId			
						." ,'".mysql_real_escape_string($this->recurResultCode)."' " //TransactionLog_recurResultCode
						." ,'".mysql_real_escape_string($this->recurResponseCode)."' " //TransactionLog_responseReasonCode
						." ,'".mysql_real_escape_string($this->recurResponseText)."' " //TransactionLog_responseReasonText
						." ,'".mysql_real_escape_string($this->recurInterval)."' "
						." ,'".mysql_real_escape_string($this->recurIntervalUnit)."' "
						." ,'".mysql_real_escape_string($this->recurStartDate)."' "
						." ,'".mysql_real_escape_string($this->recurOccurences)."' "
						." ,'".mysql_real_escape_string($this->amount)."' " //TransactionLog_amount						
						." ,'".mysql_real_escape_string($this->firstName)."' " //TransactionLog_firstName
						." ,'".mysql_real_escape_string($this->lastName)."' " //TransactionLog_lastName
						." ,'".mysql_real_escape_string($this->response)."' " //TransactionLog_responseString
						." ,'".mysql_real_escape_string($this->testMode)."' "//TransactionLog_testMode
						." ,'".time()."' " //TransactionLog_time
						." ,'1' "					
					.")";
		mysql_query($sql) or die(mysql_error());
	}
	
	public function logVoidedTransaction($originalTransactionId){
		$sql = "INSERT INTO TransactionRefunds "
					." (TransactionRefunds_refundTransactionId, TransactionRefunds_transactionId, TransactionRefunds_amount, TransactionRefunds_email
							, TransactionRefunds_responseString, TransactionRefunds_time,  TransactionRefunds_type ) "
					." VALUES ( "
						." '".mysql_real_escape_string($this->transactionId)."' " //TransactionRefunds_refundTransactionId			
						." ,'".mysql_real_escape_string($originalTransactionId)."' " //TransactionRefunds_transactionId
						." ,'".mysql_real_escape_string($this->amount)."' " //TransactionRefunds_amount
						." ,'".mysql_real_escape_string($this->email)."' " //TransactionRefunds_email
						." ,'".mysql_real_escape_string($this->response)."' " //TransactionRefunds_responseString
						." ,'".time()."' " //TransactionRefunds_time
						." ,'2' "//TransactionRefunds_type
					.")";
		mysql_query($sql) or die(mysql_error());
	}
	
	public function logRefundedTransaction($originalTransactionId){
		$sql = "INSERT INTO TransactionRefunds "
					." (TransactionRefunds_refundTransactionId, TransactionRefunds_transactionId, TransactionRefunds_amount, TransactionRefunds_email
							, TransactionRefunds_responseString, TransactionRefunds_time,  TransactionRefunds_type ) "
					." VALUES ( "
						." '".mysql_real_escape_string($this->transactionId)."' " //TransactionRefunds_refundTransactionId			
						." ,'".mysql_real_escape_string($originalTransactionId)."' " //TransactionRefunds_transactionId
						." ,'".mysql_real_escape_string($this->amount)."' " //TransactionRefunds_amount
						." ,'".mysql_real_escape_string($this->email)."' " //TransactionRefunds_email
						." ,'".mysql_real_escape_string($this->response)."' " //TransactionRefunds_responseString
						." ,'".time()."' " //TransactionRefunds_time
						." ,'1' "//TransactionRefunds_type
					.")";
		mysql_query($sql) or die(mysql_error());	
	}
    
    /**
     *  getTransactionDetailsResponse API
     *  @param int $transactionId
	 *  @return void
     */
     public function getTransactionDetailsResponse($transactionId){
	 	$xmlContent = 
	 	"<?xml version=\"1.0\" encoding=\"utf-8\"?>".
		"<getTransactionDetailsRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">".
			"<merchantAuthentication>".
				"<name>" . $this->loginId . "</name>".
				"<transactionKey>".$this->transactionKey."</transactionKey>".
			"</merchantAuthentication>".
			"<transId>".$transactionId."</transId>".
		"</getTransactionDetailsRequest>";
		
		$posturl = "https://" . $this->recurringHost . $this->recurringPath;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlContent);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$this->response = curl_exec($ch);
		curl_close ($ch);
		
        return $this->echeckType = substring_between($this->response,'<echeckType>','</echeckType>');

	 } 
	
	/**
	 * Authorize::refundTransaction()
	 * 
	 * @param mixed $transactionId
	 * @param mixed $amount
	 * @return void
	 */
	public function refundTransaction($transactionId,$amount,$description){
	    
        $CreditCardNumber = '';
        $CheckAcount = '';
		//look up details about the transaction
		$sql = "SELECT * FROM Transactions WHERE Transactions_transactionId = '".mysql_real_escape_string($transactionId)."' ";
		$query = mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($query)==1){
			$transactionInfo = mysql_fetch_assoc($query);
			//$this->parseResponse($transactionInfo['Transactions_responseString']);	
            //One Time Transaction
            if($transactionInfo['Transactions_isRecurringPayment'] == 0){
                   $this->parseResponse($transactionInfo['Transactions_responseString']);
                   
                   $TransactionMethod = $this->transactionMethod;
                   if($TransactionMethod == 'CC'){
                        $CreditCardNumber = $this->creditCardNumber;
                   }
                   else if($TransactionMethod == 'ECHECK'){
                        $CheckAcount = $this->creditCardNumber;
                        
                        $this->getTransactionDetailsResponse($transactionId);
                        if($this->echeckType == 'WEB'){
                              $echeckType = 'PPD';
                        }
                        else if($this->echeckType == 'CCD'){
                              $echeckType = 'CCD';   
                        }
                        
                   }
            }
            //Recurring Transaction Payment
            else{
                 $rtp_sql = "SELECT * FROM RecurringProcessedPayments 
                              WHERE RecurringProcessedPayments_transactionId = '".mysql_real_escape_string($transactionId)."' ";
                 $rtp_query = mysql_query($rtp_sql) or die(mysql_error());
                 
                 $rtp_data = mysql_fetch_assoc($rtp_query);
                 $ResponseArray = unserialize($rtp_data['RecurringProcessedPayments_responseString']);
                 if($ResponseArray['x_method'] == 'CC'){
                      $CreditCardNumber = $ResponseArray['x_account_number'];
                 }
                 else{
                      $CheckAcount = $ResponseArray['x_account_number'];
                      $this->getTransactionDetailsResponse($transactionId);
                      if($this->echeckType == 'WEB'){
                            $echeckType = 'PPD';
                      }
                      else if($this->echeckType == 'CCD'){
                            $echeckType = 'CCD';   
                      }
                 }
            }		
            
		}	
			
		$fields .= 'x_version='.urlencode('3.1').'&';
		$fields .= 'x_delim_char='.urlencode('|').'&';
		$fields .= 'x_delim_data='.urlencode('TRUE').'&';
		$fields .= 'x_type='.urlencode('CREDIT').'&';		
		$fields .= 'x_relay_response='.urlencode('FALSE').'&';
        if($CreditCardNumber != ''){
		     $fields .= 'x_card_num='.urlencode($CreditCardNumber).'&';
        }
        else if($CheckAcount != ''){
             $fields .= 'x_bank_acct_num='.urlencode($CheckAcount).'&';
             $fields .= 'x_echeck_type='.urlencode($echeckType).'&';
        }
		$fields .= 'x_trans_id='.urlencode($transactionId).'&';
		$fields .= 'x_description='.urlencode($description).'&';
		$fields .= 'x_amount='.urlencode($amount).'&';
		$fields .= 'x_first_name='.urlencode($transactionInfo['Transactions_firstName']).'&';
		$fields .= 'x_last_name='.urlencode($transactionInfo['Transactions_lastName']).'&';		
		$fields .= 'x_address='.urlencode($transactionInfo['Transactions_address']).'&';
		$fields .= 'x_city='.urlencode($transactionInfo['Transactions_city']).'&';		
		$fields .= 'x_state='.urlencode($transactionInfo['Transactions_state']).'&';
		$fields .= 'x_zip='.urlencode($transactionInfo['Transactions_postalCode']).'&';
		$fields .= 'x_company='.urlencode($transactionInfo['Transactions_company']).'&';
		$fields .= 'x_country='.urlencode($transactionInfo['Transactions_country']).'&';
		$fields .= 'x_phone='.urlencode($transactionInfo['Transactions_phone']).'&';
		$fields .= 'x_email='.urlencode($transactionInfo['Transactions_email']).'&';
		
		$fields .= 'x_login='.urlencode($this->loginId).'&';
        $fields .= 'x_tran_key='.urlencode($this->transactionKey);	
		
		$ch = curl_init($this->auth_net_url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "&" )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$this->response = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		
		$this->parseResponse($this->response);
		$this->logResponseString();
			
		if($this->responseCode == '1'){
			$this->logRefundedTransaction($transactionId);			
		}		
		return ($this->responseCode == '1') ? true : false;
	}
	
	
	public function voidTransaction($transactionId,$description){
		//look up details about the transaction
		$sql = "SELECT * FROM Transactions WHERE Transactions_transactionId = '".mysql_real_escape_string($transactionId)."' ";
		$query = mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($query)==1){
			$transactionInfo = mysql_fetch_assoc($query);
			$this->parseResponse($transactionInfo['Transactions_responseString']);			
		}		
		
		$fields .= 'x_version='.urlencode('3.1').'&';
		$fields .= 'x_delim_char='.urlencode('|').'&';
		$fields .= 'x_delim_data='.urlencode('TRUE').'&';
		$fields .= 'x_type='.urlencode('VOID').'&';		
		$fields .= 'x_relay_response='.urlencode('FALSE').'&';
		$fields .= 'x_card_num='.urlencode($this->creditCardNumber).'&';
		$fields .= 'x_trans_id='.urlencode($transactionId).'&';		
		$fields .= 'x_description='.urlencode($description).'&';
		$fields .= 'x_amount='.urlencode($amount).'&';
		$fields .= 'x_first_name='.urlencode($transactionInfo['Transactions_firstName']).'&';
		$fields .= 'x_last_name='.urlencode($transactionInfo['Transactions_lastName']).'&';		
		$fields .= 'x_address='.urlencode($transactionInfo['Transactions_address']).'&';
		$fields .= 'x_city='.urlencode($transactionInfo['Transactions_city']).'&';		
		$fields .= 'x_state='.urlencode($transactionInfo['Transactions_state']).'&';
		$fields .= 'x_zip='.urlencode($transactionInfo['Transactions_postalCode']).'&';
		$fields .= 'x_company='.urlencode($transactionInfo['Transactions_company']).'&';
		$fields .= 'x_country='.urlencode($transactionInfo['Transactions_country']).'&';
		$fields .= 'x_phone='.urlencode($transactionInfo['Transactions_phone']).'&';
		$fields .= 'x_email='.urlencode($transactionInfo['Transactions_email']).'&';
			
		$fields .= 'x_login='.urlencode($this->loginId).'&';
        $fields .= 'x_tran_key='.urlencode($this->transactionKey);	
		
		$ch = curl_init($this->auth_net_url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "&" )); // use HTTP POST to send form data
		### curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$this->response = curl_exec($ch); //execute post and get results
		curl_close ($ch);
		
		$this->parseResponse($this->response);
		$this->logResponseString();
		
		if($this->responseCode == '1'){
			$this->logVoidedTransaction($transactionId);	
		}		
		return ($this->responseCode == '1') ? true : false;
	}			
}

class credit_card 
{ 
    function clean_no ($cc_no) 
    { 
        // Remove non-numeric characters from $cc_no   
        return ereg_replace ('[^0-9]+', '', $cc_no); 
    } 

    function identify ($cc_no) 
    { 
         $cc_no = credit_card::clean_no ($cc_no); 

        // Get card type based on prefix and length of card number   
        if (ereg ('^4(.{12}|.{15})$', $cc_no)) 
            return 'Visa';   
        if (ereg ('^5[1-5].{14}$', $cc_no)) 
            return 'Mastercard'; 
        if (ereg ('^3[47].{13}$', $cc_no)) 
            return 'American Express'; 
        if (ereg ('^3(0[0-5].{11}|[68].{12})$', $cc_no)) 
            return 'Diners Club/Carte Blanche'; 
        if (ereg ('^6011.{12}$', $cc_no)) 
            return 'Discover Card'; 
        if (ereg ('^(3.{15}|(2131|1800).{11})$', $cc_no)) 
            return 'JCB'; 
        if (ereg ('^2(014|149).{11})$', $cc_no)) 
            return 'enRoute'; 

        return 'unknown'; 
    } 

    function validate ($cc_no) 
    { 
        // Reverse and clean the number 
        $cc_no = strrev (credit_card::clean_no ($cc_no)); 
          
        // VALIDATION ALGORITHM 
        // Loop through the number one digit at a time 
        // Double the value of every second digit (starting from the right) 
        // Concatenate the new values with the unaffected digits 
        for ($ndx = 0; $ndx < strlen ($cc_no); ++$ndx) 
            $digits .= ($ndx % 2) ? $cc_no[$ndx] * 2 : $cc_no[$ndx]; 
          
        // Add all of the single digits together 
        for ($ndx = 0; $ndx < strlen ($digits); ++$ndx) 
            $sum += $digits[$ndx]; 

        // Valid card numbers will be transformed into a multiple of 10 
        return ($sum % 10) ? FALSE : TRUE; 
    } 

    function check ($cc_no) 
    { 
        $valid = credit_card::validate ($cc_no); 
        $type  = credit_card::identify ($cc_no); 
        return array ($valid, $type, 'valid' => $valid, 'type' => $type); 
    } 
}
?>