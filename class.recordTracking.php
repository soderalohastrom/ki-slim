<?php
class recordTracking extends Record {
	function updateRecordViewLog($PersonID, $UserID) {
		$timeout = 15;
		$updateRecord = true;
		$sql = "SELECT * FROM PersonsViewLog WHERE Person_id='".$PersonID."' ORDER BY ViewDate DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		if(!isset($snd['empty_result'])) {
			if($snd['User_id'] == $UserID) {
				if($snd['ViewDate']	> (time() - ($timeout * 60))) {
					$updateRecord = false;
				}
			}			
		}
		if($updateRecord) {
			$ins_sql = "INSERT INTO PersonsViewLog (Person_id, ViewDate, User_id) VALUE('".$PersonID."','".time()."','".$UserID."')";
			$ins_snd = $this->db->mysqli->query($ins_sql);			
		}
	}
	
	function get_lastRecordView($PersonID) {
		$sql = "SELECT * FROM PersonsViewLog WHERE Person_id='".$PersonID."' ORDER BY ViewDate DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		if (($snd['ViewDate'] != 0) || ($snd['ViewDate'] != '')):
		return date("m/d/y h:ia", $snd['ViewDate']).'&nbsp;<sup>By: '.$this->get_userName($snd['User_id']).'</sup>';
		else:
		return '&nbsp;';
		endif;
	}
	
	function updateIntroRecordViewLog($DateID, $UserID) {
		$timeout = 15;
		$updateRecord = true;
		$sql = "SELECT * FROM PersonsDatesViewLog WHERE PersonsDates_id='".$DateID."' ORDER BY ViewDate DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		if(!isset($snd['empty_result'])) {
			if($snd['User_id'] == $UserID) {
				if($snd['ViewDate']	> (time() - ($timeout * 60))) {
					$updateRecord = false;
				}
			}			
		}
		if($updateRecord) {
			$ins_sql = "INSERT INTO PersonsDatesViewLog (PersonsDates_id, ViewDate, User_id) VALUE('".$DateID."','".time()."','".$UserID."')";
			$ins_snd = $this->db->mysqli->query($ins_sql);			
		}
	}
	
	function get_lastIntroRecordView($DateID) {
		$sql = "SELECT * FROM PersonsDatesViewLog WHERE PersonsDates_id='".$PersonID."' ORDER BY ViewDate DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		if (($snd['ViewDate'] != 0) || ($snd['ViewDate'] != '')):
		return date("m/d/y h:ia", $snd['ViewDate']).'&nbsp;<sup>By: '.$this->get_userName($snd['User_id']).'</sup>';
		else:
		return '&nbsp;';
		endif;
	}
	
	
	
	
}
?>