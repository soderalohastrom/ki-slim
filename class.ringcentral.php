<?php
if(!class_exists('APIs'))           require_once('class.APIs.php');

/*! \class RingCentral class.ringcentral.php "class.ringcentral.php"
 *  \brief A collection of methods that manage account connections to the RingCentral API
 */
class RingCentral {
	public $APIs;
	private $db;
	
	/*! \fn obj __constructor($DB)
	\brief RingCentral class constructor.
	\param	$DB db class object
	\return null
	*/
	function __construct($DB) {
		$this->db 		  	= $DB;
		$this->APIs 		= new APIs($DB);
	}
	
	function log_api_connection($user_id, $service, $result, $data, $error) {
		return $this->APIs->log_api_connection($user_id, $service, $result, $data, $error);
	}
	
	function save_token($user_id, $token) {
		return $this->db->mysqli->query("UPDATE Users SET tokenRingCentral = '".$this->db->mysqli->escape_string($token)."', tokenRCUpdated = '".time()."' WHERE user_id = '".$user_id."'");
	}
	
	function get_token($user_id, $decode=true) {
		$conn_result = $this->db->get_single_result("SELECT tokenRingCentral FROM Users WHERE user_id = '".$user_id."'");
		if(array_key_exists('tokenRingCentral', $conn_result) && strlen($conn_result['tokenRingCentral']) > 0) {
			if($decode) {
				return json_decode($conn_result['tokenRingCentral'], true);
			} else {
				return $conn_result['tokenRingCentral'];
			}
		} else {
			return false;
		}
	}
	
	function get_token_refresh_time($user_id) {
		$db_result = $this->db->get_single_result("SELECT tokenRCUpdated FROM Users WHERE user_id = '".$user_id."'");
		if(array_key_exists('tokenRCUpdated', $db_result)) {
			return $db_result['tokenRCUpdated'];
		} else {
			return 0;
		}
	}
	
	/*config functions are currently not in use
	function save_config($user_id, $options) {
		return $this->db->mysqli->query("UPDATE Users SET rcRingOut = '".$this->db->mysqli->escape_string($options['RingOut'])."', rcSMS = '".$this->db->mysqli->escape_string($options['SMS'])."' WHERE user_id = '".$user_id."'");
	}
	
	function get_config($user_id) {
		$config_array = array('RingOut' => '', 'SMS' => '');
		$config_result = $this->db->get_single_result("SELECT rcRingOut, rcSMS FROM Users WHERE user_id = '".$user_id."'");
		if(!array_key_exists('error', $config_result) && !array_key_exists('empty_result', $config_result)) {
			$config_array['RingOut'] = $config_result['rcRingOut'];
			$config_array['SMS'] = $config_result['rcSMS'];
		}
		return $config_array;
	}
	*/
	
	function import_messages($messages, $time, $type) {
		$sql = "INSERT INTO RingCentralMessages (
				RingCentralMessages_data,
				RingCentralMessages_date,
				RingCentralMessages_type
			) VALUES (
				'".$this->db->mysqli->escape_string(utf8_encode(serialize($messages)))."',
				'".$time."',
				'".$type."'
			)";
		return $this->db->mysqli->query($sql);
	}
	
	function get_messages($type, $processed=0) {
		$sql = "SELECT * FROM RingCentralMessages WHERE RingCentralMessages_type = '".$type."' AND RingCentralMessages_processed = '".$processed."'";
		$messages_result = $this->db->get_multi_result($sql);
		if(!array_key_exists('error', $messages_result) && !array_key_exists('empty_result', $messages_result)) {
			return $messages_result;
		} else {
			return false;
		}
	}
	
	function update_messages($notification_id, $processed_flag) {
		$this->db->mysqli->query("UPDATE RingCentralMessages SET RingCentralMessages_processed = '".$processed_flag."' WHERE RingCentralMessages_id = '".$notification_id."'");
	}
	
	function get_last_import_time() {
		$result = $this->db->get_single_result("SELECT RingCentralMessages_date FROM RingCentralMessages WHERE RingCentralMessages_id = '1'");
		if(array_key_exists('RingCentralMessages_date', $result)) {
			return $result['RingCentralMessages_date'];
		} else {
			return 0;
		}
	}
	
	function update_last_import_time($time) {
		$upd_sql = "UPDATE RingCentralMessages SET RingCentralMessages_date = '".$time."' WHERE RingCentralMessages_id = '1'";
		$this->db->mysqli->query($upd_sql);
	}
	
	function record_phone_search($phone) {
		if(strlen($phone) == 0) {
			return 0;
		}
		$sql = "SELECT Phones.Person_id 
		FROM Phones 
		JOIN Persons on Phones.Person_id = Persons.Person_id
		WHERE Phones.Phone_raw = '".$this->db->mysqli->escape_string($phone)."'
		AND Persons.PersonsTypes_id NOT IN (1,2,9)
		LIMIT 1";
		$result = $this->db->get_single_result($sql);
		if(array_key_exists('Person_id', $result)) {
			return $result['Person_id'];
		} else {
			return 0;
		}
	}
	
	function comm_history_search($convo_id, $person_id) {
		$sql = "SELECT PersonsCommHistory_id FROM PersonsCommHistory WHERE MessageID = '".$convo_id."' AND Person_id = '".$person_id."' AND MessageType = 'SMS' AND MessageService = 'RINGCENTRAL' LIMIT 1";
		$result = $this->db->get_single_result($sql);
		if(array_key_exists('PersonsCommHistory_id', $result)) {
			return $result['PersonsCommHistory_id'];
		} else {
			return 0;
		}
	}
	
	function send_post_curl($url, $postdata = '', $header=array(), $decode_response=true) {
		return $this->APIs->send_post_curl($url, $postdata, $header, $decode_response);
	}
	
	function send_post_jwt_request($url, $postdata = '', $header=array(), $decode_response=true) {

		$jwt_token = 'eyJraWQiOiI4NzYyZjU5OGQwNTk0NGRiODZiZjVjYTk3ODA0NzYwOCIsInR5cCI6IkpXVCIsImFsZyI6IlJTMjU2In0.eyJhdWQiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbS9yZXN0YXBpL29hdXRoL3Rva2VuIiwic3ViIjoiNjg1NTI1MDA5IiwiaXNzIjoiaHR0cHM6Ly9wbGF0Zm9ybS5yaW5nY2VudHJhbC5jb20iLCJleHAiOjM4MTYyOTc3NDYsImlhdCI6MTY2ODgxNDA5OSwianRpIjoiSE1vV19qY2pSQWlIMHRIMmgxQUVldyJ9.JQAfPAeCFRpMSfTJ-5wKAd76LbIHlUNAi37uVzy0c6ZMqEhEYavhuHdXgZ6qwKy8vMSvdnCihSnBe5PX2bYAAP7Lpz-7t5KH3X6Gb8Q9LXWGSIQGCrsetQQgWI0lBWZgmvOkw1YcOAhK1Dz_Q9gC1rW2BBTQF6V-pn38WKVl7CEtt5ZHe0bB2Bw4K27f5bDgo8ShcyWJn972JQA9jz1JgiuxzYvySUjU3YhQO4MBHKHd8RvPkjehpcbpLSQwfiKRbOV9dLEV2qwZaozYtG7nB_tiLIDnaLnEwDogDpYNZhYSgTVBvGcLW2ukbIn_UxeoOC7gbp6dykwibETb_DCZ8g';
	$client_id = "a8HrNNMqSF2F-XEJNbKwRQ";
	$client_secret = "j_nH-8GbTp6jIzANqZONKQL39xpV-JTg2PJe5eBxbl1g";
	$header = [
		"Authorization: Basic Base64-Encoded($client_id:$client_secret)",
		"Content-Type: application/x-www-form-urlencode",
		 "Accept: application/json"
		];
	
	$payload = [
		"grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
		"assertion" => $jwt_token,
	];
// 	curl --location --request POST ' https://platform.ringcentral.com/restapi/oauth/token' \
//    --header 'Accept: application/json' \
//    --header 'Content-Type: application/x-www-form-urlencoded' \
//    --header 'Authorization: Basic Base64-Encoded(a8HrNNMqSF2F-XEJNbKwRQ:j_nH-8GbTp6jIzANqZONKQL39xpV-JTg2PJe5eBxbl1g)' \
//    --data-urlencode 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer' \
//    --data-urlencode 'assertion=eyJraWQiOiI4NzYyZjU5OGQwNTk0NGRiODZiZjVjYTk3ODA0NzYwOCIsInR5cCI6IkpXVCIsImFsZyI6IlJTMjU2In0.eyJhdWQiOiJodHRwczovL3BsYXRmb3JtLnJpbmdjZW50cmFsLmNvbS9yZXN0YXBpL29hdXRoL3Rva2VuIiwic3ViIjoiNjg1NTI1MDA5IiwiaXNzIjoiaHR0cHM6Ly9wbGF0Zm9ybS5yaW5nY2VudHJhbC5jb20iLCJleHAiOjM4MTYyOTc3NDYsImlhdCI6MTY2ODgxNDA5OSwianRpIjoiSE1vV19qY2pSQWlIMHRIMmgxQUVldyJ9.JQAfPAeCFRpMSfTJ-5wKAd76LbIHlUNAi37uVzy0c6ZMqEhEYavhuHdXgZ6qwKy8vMSvdnCihSnBe5PX2bYAAP7Lpz-7t5KH3X6Gb8Q9LXWGSIQGCrsetQQgWI0lBWZgmvOkw1YcOAhK1Dz_Q9gC1rW2BBTQF6V-pn38WKVl7CEtt5ZHe0bB2Bw4K27f5bDgo8ShcyWJn972JQA9jz1JgiuxzYvySUjU3YhQO4MBHKHd8RvPkjehpcbpLSQwfiKRbOV9dLEV2qwZaozYtG7nB_tiLIDnaLnEwDogDpYNZhYSgTVBvGcLW2ukbIn_UxeoOC7gbp6dykwibETb_DCZ8g'
			
		return $this->APIs->send_post_curl($url, $payload, $header, $decode_response);
	}
	

	function send_get_curl($url, $header=array(), $decode_response=true) {
		return $this->APIs->send_get_curl($url, $header, $decode_response);
	}
	
	function get_last_status() {
		return $this->APIs->last_status;
	}
	
	function save_message_attachments($message_id, $attachments) {
		$this->APIs->save_message_attachments($message_id, $attachments);
	}
	
}
?>