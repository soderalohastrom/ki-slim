<?php
/*! \class APIs class.APIs.php "class.APIs.php"
 *  \brief Methods used by all external APIs, such as connection log and CURL functions
 */
class APIs {
	public $last_status;	//This holds the http status code of the last API call.
	private $db;

/*! \fn obj __constructor($DB)
		\brief APIs class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db = $DB;
	}
	
	function send_post_curl($url, $postdata = '', $header=array(), $decode_response=true) {
		if (is_array($postdata)){
			$postdata = http_build_query($postdata);
		}
		$curl_error = false;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		$data = curl_exec($ch);
		if(curl_errno($ch)) {
			$curl_error = true;
		}
		//$curl_info = curl_getinfo($ch);
		//return $curl_info;
		$this->last_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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

	function send_put_curl($url, $postdata = '', $header=array(), $decode_response=true) {
		$curl_error = false;
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		$data = curl_exec($ch);
		if(curl_errno($ch)) {
			$curl_error = true;
		}
		//print_r($postdata);
		//print_r($data);
		//$curl_info = curl_getinfo($ch);
		//return $curl_info;
		
		$this->last_status = curl_getinfo($ch);
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
	
	function send_get_curl($url, $header=array(), $decode_response=true, $response_headers=false) {
		$curl_error = false;
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		if($response_headers) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		}
		$data = curl_exec($ch);
		if(curl_errno($ch)) {
			$curl_error = true;
		}
		$this->last_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
	
	function send_delete_curl($url, $header=array(), $decode_response=true, $response_headers=false) {
		$curl_error = false;
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(count($header) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		if($response_headers) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		}
		$data = curl_exec($ch);
		if(curl_errno($ch)) {
			$curl_error = true;
		}
		$this->last_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
	
	function log_api_connection($user_id, $service, $result, $data, $error) {
		$conn_query = "
		INSERT INTO APIConnectionLog (
			Log_userId, 
			Log_service, 
			Log_result, 
			Log_data, 
			Log_error,
			Log_timestamp
		) VALUES (
			'".$this->db->mysqli->escape_string($user_id)."',
			'".$this->db->mysqli->escape_string($service)."',
			'".$this->db->mysqli->escape_string($result)."',
			'".$this->db->mysqli->escape_string($data)."',
			'".$this->db->mysqli->escape_string($error)."',
			'".time()."'
		)
		";
		$conn_result = $this->db->mysqli->query($conn_query);
		if($conn_result !== false) {
			return $this->db->mysqli->insert_id;
		} else {
			return false;
		}
	}
	
	function get_api_log_entry($user_id, $log_id) {
		$log_result = $this->db->get_single_result("SELECT * FROM APIConnectionLog WHERE Log_userId = '".$user_id."' AND Log_id = '".$log_id."'");
		if(!array_key_exists('empty_result', $log_result) && !array_key_exists('error', $log_result)) {
			return $log_result;
		} else {
			return false;
		}
	}
	
	function save_message_attachments($message_id, $attachments) {
		if(!empty($message_id) && is_array($attachments) && count($attachments) > 0) {
			$m_sql = "SELECT Messages_commId FROM Messages WHERE Messages_messageId = '".$message_id."'";
			$m_result = $this->db->get_multi_result($m_sql);
			if(!array_key_exists('error', $m_result) && !array_key_exists('empty_result', $m_result)) {
				foreach($m_result as $comm) {
					$upd_sql = "UPDATE PersonsCommHistory SET MessageAttachments = '".json_encode($attachments)."' WHERE PersonsCommHistory_id = '".$comm['Messages_commId']."'";
					$upd_result = $this->db->mysqli->query($upd_sql);
				}
			}
		}
	}
}