<?php
if(!class_exists('Marketing'))           require_once('class.marketing.php');

/**
 * class.conversica.php
 * 
 * Handles data transfer between Conversica and KISS
 * 
 *
 * @package API
 * @author  Jen Skibitsky <dev@kelleher-international.com>
 */
class Conversica {
	private $marketing;
	private $db;
	
	public function Conversica() {
		date_default_timezone_set('America/Los_Angeles');
		$this->marketing = new Marketing();
		global $DB;
		if(!$DB) {
			$this->db = new database();
			$this->db->connect();
		} else {
			$this->db = $DB;
		}
	}
	
	public function add_to_comm_history($person_id, $subject, $body, $date_created, $action) {
		$result = array('success' => false, 'message' => '');
		if(empty($person_id) || !is_numeric($person_id)) {
			$result['message'] = 'Person ID is missing or invalid.';
			return $result;
		}
		$date_epoch = strtotime($date_created);
		if($date_epoch === false) {
			$result['message'] = 'Unable to convert Date Created into epoch.';
			return $result;
		}
		$email_sql = "SELECT Email FROM Persons WHERE Person_id = '".$person_id."'";
		$email_result = $this->db->get_single_result($email_sql);
		if(!array_key_exists('Email', $email_result)) {
			$result['message'] = 'Person ID is invalid.';
			return $result;
		}
		if($action == 'sent') {
			$from_email = 'conversica@kelleher-international.com';
			$to_email = $email_result['Email'];
		} else {
			$from_email = $email_result['Email'];
			$to_email = 'conversica@kelleher-international.com';
		}
		$this->marketing->log_communication($person_id, nl2br($body), $subject, $from_email, $to_email, 0, $date_epoch, 'EMAIL', 'SENT', 'CONVERSICA');
		$result['success'] = true;
		return $result;
	}

	
       /**
         * This function sends the lead to conversica
         * 
         * @param int $personid PersonId from KISS to update.  If creating for first time, conversation will be created and then updated.
         * @param bool $stopMessaging Default false If true, the lead is no longer in the market or has been disqualified. Conversica will stop listening for responses and will no longer engage with the lead.  
         * @param bool $skipToFollowup If true, you have already made contact with the lead, and Conversica should follow-up in a few days to identify if they have any remaining questions. bool false*
         * @param mixed $optOut If true, the lead opted out of contact
         * @return mixed response message or false if error 
         */
        public function send_to_conversica($personid,  $repName = '',$conversationId = '' , $stopMessaging = false, $skipToFollowup = false, $optOut = false )
        {
            // Conversica settings can be moved to environment variable if needed:
			$url = 'https://integrations-api.conversica.com/json/';
			$header = ['Content-type: application/json'];
			$user = 'kelleherinternationalapi';
			$pass = 'LpwXdPOhRSu0i3xpfeW8';
            $conversicaapi = '7.1';
            
            //NOTE: Hardcode to Default Conversation Id when empty
            if (empty($conversationId)) { 
                $conversationId = 'Inbound Leads: Drive Action';
            }

			//NOTE: Lead source can be sent for tracking
            $leadSource = 'KISS';
            
            // NOTE: All of these items will probably come from the Conversica API in a call at this point.
            // Date of created
            $created = date('Y-m-d');

            // Initial Status will come from Conversica API
			$status = 'Conversation Created';
			
            // Creates a UniqueID for the conversation which may come from Conversica API
            
            try {
                // Gets the user into the database
                /* The following fields will not be passed to Conversica they are 
                 <missing:> repName,
                   altEmail ,
                    homeEmail,
                    workEmail ,
                    cellPhone ,
                    leadType,
                   </missing:>
                   */
                $sql = @"SELECT 
                Persons.Person_id as id,
                    `Persons`.`FirstName` as firstName,
                    `Persons`.`LastName` as lastName,
                    `Persons`.`Email` as email,
                    HomePhones.Phone_number as homePhone ,
                    WorkPhones.Phone_number as workPhone,
                    Addresses.Street_1 as address ,
                    Addresses.City as city ,
                    Addresses.State as state,
                    Addresses.Postal as zip,
                    PersonsStatus.PersonsStatus_name as leadStatus
                    FROM `Persons`
                        left join PersonsStatus on Persons.PersonsStatus_id=PersonsStatus.PersonsStatus_id
                        left join Phones as HomePhones on HomePhones.PhoneType=2 and Persons.Person_id=HomePhones.Person_id
                        left join Phones as WorkPhones on WorkPhones.PhoneType=3 and Persons.Person_id=HomePhones.Person_id
                        left join Addresses on Addresses.Person_id = Persons.Person_id
                        where Persons.Person_id=$personid";
				$query = $this->db->get_single_result($sql);
                
            // If user exist
                if ($query) {
                    $id = $query['id']; 
                    $firstName = $query['firstName'];
                    $lastName = $query['lastName'];
                    $email = $query['email'];
                    $homePhone = $query['homePhone'];
                    $workPhone = $query['workPhone'];
                    $address = $query['address'];
                    $city = $query['city'];
                    $state = $query['state']; 
                    $zip = $query['zip'];
                    $leadStatus =$query['leadStatus'];
                } else {
                    throw new Exception("Person Not found.");
                }

                //Set payload and other params for Conversica API Request
                $data =
                [
                'apiVersion' => $conversicaapi,
                'id' =>  $id,
                'conversationId' => $conversationId,
                'firstName' =>$firstName,
                'lastName' =>$lastName,
                'email' =>$email,
                'homePhone' => $homePhone,
                'workPhone' =>$workPhone,
                'address' =>$address,
                'city' => $city,
                'state' =>$state,
                'zip' =>$zip,
                'leadSource' =>$leadSource,
                'leadStatus' =>$leadStatus,
                'stopMessaging' =>$stopMessaging,
                'skipToFollowup' =>$skipToFollowup,
                'optOut' =>$optOut,
                'repName' => $repName,
                ];

                // Cleanup Data
                $cleandata = array();
                foreach ($data as $key => $value){
                    if (isset($value)){
                        $cleandata[$key] = $value;
                    }
                }
                $data = json_encode($cleandata);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);

                //Execute request
                $result_string = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //Return true if success
                return ($status_code==200);
            } catch (Exception $e) {
                return false;
            } finally {
                // Destroy the database connection
                $conn = null;
            }
   
        }
}
?>