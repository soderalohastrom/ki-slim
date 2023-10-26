<?php
class Session {
	public function __construct($DB, $ENC) {
		$this->db 		= 	$DB;
		$this->timeout	=	840;	// 14 MINUTES //
		$this->enc 		=	$ENC;
	}
	
	
	public function createSession($user_id) {
		$ut_fields = "SessionID, UserID, SessionBegin, SessionExpire";
		$ut_values = "'".session_id()."','".$user_id."','".time()."','".(time() + $this->timeout)."'";
		$ut_sql = "INSERT INTO UserSessionTracking (".$ut_fields.") VALUES(".$ut_values.")";
		$ut_snd = $this->db->mysqli->query($ut_sql);			
	}
	
	public function pushSessionExpire() {
		$newExpire = (time() + $this->timeout);
		$upd_sql = "UPDATE UserSessionTracking SET SessionExpire='".$newExpire."' WHERE SessionID='".session_id()."' AND UserID='".$_SESSION['system_user_id']."'";
		$upd_snd = $this->db->mysqli->query($upd_sql);		
	}
	
	public function garbageCleanUp() {
		// GARBAGE CLEAN UP //
		$del_sql = "DELETE FROM UserSessionTracking WHERE SessionExpire < '".time()."'";
		$del_snd = $this->db->mysqli->query($del_sql);			
	}
	
	public function checkSession() {
		$s_sql = "SELECT * FROM UserSessionTracking WHERE SessionID='".session_id()."' AND UserID='".$_SESSION['system_user_id']."'";
		$s_snd = $this->db->get_single_result($s_sql);
		if(isset($s_snd['empty_result'])):
			return false;
		else:
			//print_r($s_snd);
			$epoch = time();
			//echo $s_snd['SessionExpire']."|".$epoch;
			if($s_snd['SessionExpire'] >= time()):
				return true;
			else:
				return false;
			endif;
		endif;			
	}
	
	public function killSession() {
		$upd_sql = "DELETE FROM UserSessionTracking WHERE SessionID='".session_id()."' AND UserID='".$_SESSION['system_user_id']."'";
		$this->db->mysqli->query($upd_sql);	
	}
	
	public function createToken() {
		$newToken = session_id().'|'.date("Y-m-d").'|'.$_SESSION['system_user_id'];
		$tokenValue = $this->enc->encrypt($newToken);
		return $tokenValue;
	}
	
	public function renderToken() {
		?>
		<input type="hidden" name="kiss_token" id="kiss_token" value="<?php echo $this->createToken()?>" />
		<?php
	}
	
	public function validToken($token, $uid=true) {
		$cleanToken = $this->enc->decrypt($token);
		$tparts = explode("|", $cleanToken);
		$passed = true;
		if($tparts[0] != session_id()):
			$passed = false;
		endif;
		
		$baseEpoch = mktime(0,0,0, date("m"), date("d"), date("Y"));
		$baseEpoch2 = mktime(0,0,0, date("m"), date("d") - 1, date("Y"));
		
		
		if(($tparts[1] != date("Y-m-d")) && ($tparts[1] != date("Y-m-d", $baseEpoch2))):
			$passed = false;
		endif;
		//echo "Checking Part 3 ";
		if($uid) {
			//echo "Validating part 3";
			if($tparts[2] != $_SESSION['system_user_id']):
				$passsed = false;
			endif;
		}
		
		return $passed;
	}
	
}

?>