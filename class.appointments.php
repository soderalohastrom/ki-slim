<?php
/*! \class Appointments class.appointments.php "class.appointments.php"
 *  \brief collection of methods for saving and getting appointment data
 */
class Appointments {
	/*! \fn obj __constructor($DB)
		\brief Appointments class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db 		= $DB;
	}
	
	function get_person_appointments($person_id, $inc_deleted=false) {
		if(!$inc_deleted) {
			$sql = "SELECT * FROM Appointments WHERE Appointments_personId = '".$person_id."' AND Appointments_deleted = '0' ORDER BY Appointments_time";
		} else {
			$sql = "SELECT * FROM Appointments WHERE Appointments_personId = '".$person_id."' ORDER BY Appointments_time";
		}
		return $this->db->get_multi_result($sql);
	}
	
	function get_user_appointments($user_id, $inc_deleted=false) {
		if(!$inc_deleted) {
			$sql = "SELECT * FROM Appointments WHERE Appointments_userId = '".$user_id."' AND Appointments_deleted = '0' ORDER BY Appointments_time";
		} else {
			$sql = "SELECT * FROM Appointments WHERE Appointments_userId = '".$user_id."' ORDER BY Appointments_time";
		}
		return $this->db->get_multi_result($sql);
	}
	
	function get_appointment($ap_id) {
		$sql = "SELECT * FROM Appointments WHERE Appointments_id = '".$ap_id."'";
		$data = $this->db->get_single_result($sql);
		if(array_key_exists('error', $data) || array_key_exists('empty_result', $data)) {
			return false;
		} else {
			return $data;
		}
	}
	
	function get_appointment_by_event_id($event_id) {
		$sql = "SELECT * FROM Appointments WHERE Appointments_eventId = '".$this->db->mysqli->escape_string($event_id)."'";
		$data = $this->db->get_single_result($sql);
		if(array_key_exists('error', $data) || array_key_exists('empty_result', $data)) {
			return false;
		} else {
			return $data;
		}
	}
	
	function save_appointment($salesperson_id, $person_id, $ap_time, $ap_type, $event_id) {
		$sql = "INSERT INTO Appointments ( 
				Appointments_personId, 
				Appointments_userId,
				Appointments_eventId,
				Appointments_time,
				Appointments_type
			) VALUES (
				'".$person_id."',
				'".$salesperson_id."',
				'".$this->db->mysqli->escape_string($event_id)."',
				'".$ap_time."',
				'".$ap_type."'
			)";
		$result = $this->db->mysqli->query($sql);
	}
	
	function delete_appointment($ap_id) {
		$sql = "UPDATE Appointments SET Appointments_deleted = '1' WHERE Appointments_id = '".$ap_id."'";
		$result = $this->db->mysqli->query($sql);
		if($result === false) {
			return false;
		}
		return true;
	}
}
?>