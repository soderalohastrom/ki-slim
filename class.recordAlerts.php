<?php
class recordAlerts extends Record {
	function checkforRecordAlerts($PersonID) {
		$alerts[] = NULL;
		//$alerts[] = $this->check_leadAge($PersonID);			
		$alerts[] = $this->checkVIPStatus($PersonID);
		foreach($alerts as $alert) {
			if($alert != NULL) {
				echo $alert;		
			}
		}
	}
	
	private function checkVIPStatus($PersonID) {
		$sql = "SELECT VIP FROM Persons WHERE Person_id='".$PersonID."'";
		$snd = $this->db->get_single_result($sql);
		$VIP = $snd['HCS'];
		if($HCS) {
			ob_start();
			?>
            <div class="alert alert-warning" role="alert" style="margin-bottom:.1rem;">
            	<strong>HIGH PRIORITY CLIENT</strong><br />
				This record is marked as a HIGH PRIORITY CLIENT. This limits access to this record and its data unless you are given permission to access HIGH PRIORITY CLIENT records.
			</div>
            <?php
			return ob_get_clean();	
		} else {
			return NULL;
		}
	}
	
	function check_leadAge($PersonID) {
		$sql = "SELECT PersonsTypes_id, DateCreated FROM Persons WHERE Person_id='".$PersonID."'";
		$snd = $this->db->get_single_result($sql);
		$typeID = $snd['PersonsTypes_id'];
		if(($typeID == 3) || ($typeID == 13)) {
			$st = time();
			$now = new DateTime("@$st");
			$then = new DateTime("@".$snd['DateCreated']);
			$interval = date_diff($then, $now);
			$daysOld = $interval->format('%a');
			
			if($daysOld < 30) {
				$aClass = 'm-badge--success';
			} elseif(($daysOld >= 30) && ($daysOld < 60)) {
				$aClass = 'm-badge--warning';	
			} else {
				$aClass = 'm-badge--danger';
			}
			ob_start();
			?><span class="m-badge <?php echo $aClass?>" data-toggle="m-popover" data-placement="top" data-content="This record is <?php echo $daysOld?> days old"></span>           
            <?php
			return ob_get_clean();
		} else {
			return '';
		}		
	}
	
	
	
}
?>