<?php
if(!class_exists('APIs'))           require_once('class.APIs.php');

/*! \class Nylas class.nylas.php "class.nylas.php"
 *  \brief A collection of methods that manage account connections to the Nylas API
 */
class Nylas {
	public $APIs;
	public $account_name;
	public $calendar_id;
	public $calendar_name;
	public $calendars;
	private $account_id;
	private $provider;
	private $api_id;
	private $api_secret;
	private $callback_url;
	private $db;
	private $token;
	
	/*! \fn obj __constructor($DB)
		\brief Nylas class constructor.
		\param	$API_ID string
		\param	$API_SECRET string
		\param	$DB db class object
		\param	$callback_url string
		\return null
	*/
	function __construct($API_ID, $API_SECRET, $DB, $callback_url='') {
		$this->api_id 	  		= $API_ID;
		$this->api_secret 		= $API_SECRET;
		$this->callback_url 	= $callback_url;
		$this->db 		  		= $DB;
		$this->APIs 			= new APIs($DB);
		$this->token 			= '';
		$this->account_id 		= '';
		$this->account_name 	= '';
		$this->provider 		= '';
		$this->calendar_id 		= '';
		$this->calendar_name 	= '';
		$this->calendars 		= array();
	}
	
	function send_post_curl($url, $postdata = '', $header=array(), $decode_response=true) {
		return $this->APIs->send_post_curl($url, $postdata, $header, $decode_response);
	}

	function send_put_curl($url, $postdata = '', $header=array(), $decode_response=true) {
		return $this->APIs->send_put_curl($url, $postdata, $header, $decode_response);
	}

	function send_get_curl($url, $header=array(), $decode_response=true) {
		return $this->APIs->send_get_curl($url, $header, $decode_response);
	}
	
	function send_delete_curl($url, $header=array(), $decode_response=true) {
		return $this->APIs->send_delete_curl($url, $header, $decode_response);
	}
	
	function get_last_status() {
		return $this->APIs->last_status;
	}
	
	function log_api_connection($user_id, $service, $result, $data, $error) {
		return $this->APIs->log_api_connection($user_id, $service, $result, $data, $error);
	}
	
	function get_api_log_entry($user_id, $log_id) {
		return $this->APIs->get_api_log_entry($user_id, $log_id);
	}
	
	function get_token($user_id) {
		$conn_result = $this->db->get_single_result("SELECT tokenNylas FROM Users WHERE user_id = '".$user_id."'");
		if(array_key_exists('tokenNylas', $conn_result) && strlen($conn_result['tokenNylas']) > 0) {
			$token_array = json_decode($conn_result['tokenNylas'], true);
			$this->token = $token_array['access_token'];
			$this->account_id = $token_array['account_id'];
			$this->account_name = $token_array['email_address'];
			$this->provider = $token_array['provider'];
			if(array_key_exists('calendar_id', $token_array)) {
				$this->calendar_id = $token_array['calendar_id'];
				$this->calendar_name = $token_array['calendar_name'];
			}
			return true;
		} else {
			return false;
		}
	}
	
	function save_token($user_id, $token) {
		return $this->db->mysqli->query("UPDATE Users SET tokenNylas = '".$this->db->mysqli->escape_string($token)."' WHERE user_id = '".$user_id."'");
	}
	
	function revoke_token() {
		$body = $this->send_post_curl('https://api.nylas.com/oauth/revoke', '', array('Authorization: Basic '.$this->encode_token($this->token)));
		$status = $this->get_last_status();
		if($status == 200) {
			return 'SUCCESS';
		} else {
			return $body['message'];
		}
		//return 'TOKEN: '.$this->token.' STATUS: '.$status.' BODY: '.print_r($body, true);
	}
	
	function build_connect_string($email) {
		return 'https://api.nylas.com/oauth/authorize?client_id='.$this->api_id.'&response_type=code&scope=email&login_hint='.$email.'&redirect_uri='.$this->callback_url;
	}
	
	function encode_token($token) {
		return base64_encode($token.':');
	}
	
	function get_message($message_id, $token='') {
		if(strlen($token) > 0) {
			$token_encoded = $token;
		} else {
			$token_encoded = $this->encode_token($this->token);
		}
		echo 'https://api.nylas.com/messages/'.$message_id;
		echo print_r(array('Authorization: Basic '.$token_encoded), true);
		return $this->send_get_curl('https://api.nylas.com/messages/'.$message_id, array('Authorization: Basic '.$token_encoded));
	}

	function get_files($message_id, $token='') {
		if(strlen($token) > 0) {
			$token_encoded = $token;
		} else {
			$token_encoded = $this->encode_token($this->token);
		}
		return $this->send_get_curl('https://api.nylas.com/files?message_id='.$message_id, array('Authorization: Basic '.$token_encoded));
	}
	
	function download_file($file_id, $token='') {
		if(strlen($token) > 0) {
			$token_encoded = $token;
		} else {
			$token_encoded = $this->encode_token($this->token);
		}
		$result = $this->send_get_curl('https://api.nylas.com/files/'.$file_id.'/download', array('Authorization: Basic '.$token_encoded), false);
		$status = $this->get_last_status();
		echo "FILE DL STATUS: $status<br>";
		//echo "RESULT: $result<br>";
		if($status != 200) {
			return false;
		} else {
			return $result;
		}
	}
	
	function update_notification($notification_id, $processed_flag, $notification_message = 'Unknown' ) {
		$this->db->mysqli->query("UPDATE NylasNotifications SET NylasNotifications_processed = '".$processed_flag."', NylasNotifications_message = '".$notification_message."' WHERE NylasNotifications_id = '".$notification_id."'");
	}
	
	function update_files_processed($notification_id, $processed_flag) {
		$this->db->mysqli->query("UPDATE NylasNotifications SET NylasNotifications_filesProc = '".$processed_flag."' WHERE NylasNotifications_id = '".$notification_id."'");
	}
	
	function save_message_attachments($message_id, $attachments) {
		$this->APIs->save_message_attachments($message_id, $attachments);
	}
	
	//find records in the communication history matching the given Nylas Message ID
	function find_comms($message_id) {
		$sql = "SELECT Person_id 
		FROM Messages
		JOIN PersonsCommHistory ON Messages_commId = PersonsCommHistory_id
		WHERE Messages_service = 'NYLAS'
		AND Messages_messageId = '".$message_id."'";
		$result = $this->db->get_multi_result($sql);
		if(array_key_exists('error', $result) || array_key_exists('empty_result', $result)) {
			return array();
		} else {
			return $result;
		}
	}
	
	//Returns flag for whether this a Kelleher staff email address
	function is_kiss_staff($email_addr) {
		if(strpos($email_addr, '@kelleher-associates.com') === false && strpos($email_addr, '@kelleher-international.com') === false ) {
			return false;
		} else {
			return true;
		}
	}
	
	//get all of a user's calendars
	function get_calendars($token='') {
		if(strlen($token) > 0) {
			$token_encoded = $token;
		} else {
			$token_encoded = $this->encode_token($this->token);
		}
		return $this->send_get_curl('https://api.nylas.com/calendars', array('Authorization: Basic '.$token_encoded));
	}
	
	//save user's default calendar to the database
	function save_calendar($calendar_id, $calendar_name) {
		$result = $this->get_token($_SESSION['system_user_id']);
		if(!$result) {
			return 0;
		}
		$new_token_array = array('access_token' => $this->token, 'account_id' => $this->account_id, 'email_address' => $this->account_name, 'provider' => $this->provider, 'calendar_id' => $calendar_id, 'calendar_name' => $calendar_name);
		$saved = $this->save_token($_SESSION['system_user_id'], json_encode($new_token_array));
		if($saved) {
			return 1;
		} else {
			return 0;
		}
	}
	
	//get calendar events matching the specified filter criteria
	function get_calendar_events($filters=array()) {
		$token_encoded = $this->encode_token($this->token);
		$query_params = '';
		foreach($filters as $filter_param=>$filter_val) {
			$query_params .= "&$filter_param=$filter_val";
		}
		if(strlen($query_params) > 0) {
			$query_params = substr($query_params, 1);
		}
		return $this->send_get_curl('https://api.nylas.com/events?'.$query_params, array('Authorization: Basic '.$token_encoded));
	}
	
	//add an appointment to the user's default calendar
	function upload_appointment($salesperson_id, $person_id, $ap_time, $ap_type, $ap_length) {
		$has_token = $this->get_token($salesperson_id);
		$salesperson_data = $this->db->get_single_result("SELECT firstName, lastName, email FROM Users WHERE user_id = '".$salesperson_id."'");
		$person_data = $this->db->get_single_result("SELECT FirstName, LastName, Email FROM Persons WHERE Person_id = '".$person_id."'");
		$event_data = array(
			'calendar_id' => $this->calendar_id, 
			'when' => array('start_time' => $ap_time, 'end_time' => ($ap_time+$ap_length)),
			'title' => $ap_type,
			'description' => $ap_type.' with '.$person_data['FirstName'].' '.$person_data['LastName'],
			'busy' => true,
			'participants' => array(
								array('email' => $salesperson_data['email'], 'name' => $salesperson_data['firstName'].' '.$salesperson_data['lastName'], 'status' => 'noreply', 'comment' => ''),
								array('email' => $person_data['Email'], 'name' => $person_data['FirstName'].' '.$person_data['LastName'], 'status' => 'noreply', 'comment' => '')
							)
		);
		$token_encoded = $this->encode_token($this->token);
		return $this->send_post_curl('https://api.nylas.com/events?notify_participants=true', json_encode($event_data), array('Authorization: Basic '.$token_encoded), true);
	}
	
	function upload_appointment_TEST() {
		$start_time = time();
		$end_time = $start_time + 3600;
		//$postdata = '{"calendar_id":"d2cpi43qryhfkevaon07dkb4u","when":{"start_time":'.$start_time.',"end_time":'.$end_time.'},"title":"Sales Appointment","description":"Sales Appointment with Jen Smith","busy":true,"participants":[{"email":"ajwildertech@gmail.com","name":"Andrew Wilder","status":"noreply","comment":""},{"email":"rich@kelleher-international.com","name":"Jen Smith","status":"noreply","comment":""}]}';
		/*$params = array(
			"calendar_id"=>"d2cpi43qryhfkevaon07dkb4u",
			"when"=>"1522159348",
			"title"=>"Sales Appointment",
			"description"=>"Sales Appointment with Andrew Wilder"
			);*/
		$token_encoded = $this->encode_token($this->token);
		return $this->send_post_curl('https://api.nylas.com/events?notify_participants=true', $postdata, array('Authorization: Basic '.$token_encoded), true);
	}
	
	function cancel_appointment($ap_id) {
		$token_encoded = $this->encode_token($this->token);
		return $this->send_delete_curl('https://api.nylas.com/events/'.$ap_id.'?notify_participants=true', array('Authorization: Basic '.$token_encoded));
	}

	
	function get_clean_conversation($message_id, $token='') {
		// if(strlen($token) > 0) {
		// 	$token_encoded = $token;
		// } else {
		// 	$token_encoded = $this->encode_token($this->token);
		// }
		//echo 'https://api.nylas.com/neural/conversation';
		$message_data = array(
			'message_id' => array($message_id)
		);
		
		$conversation =  $this->send_put_curl('https://api.nylas.com/neural/conversation', json_encode($message_data), array('Authorization: Bearer '.$token, 'Accept: application/json','Content-Type: application/json'), true);
	
		return $conversation;
	}

}
?>