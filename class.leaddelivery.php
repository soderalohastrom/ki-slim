<?php
class LDELIVERY {
	/*! \fn obj __constructor($DB)
		\brief LeadDelivery class constructor.
		\param	$DB db class object
		\return null
	*/
	public function __construct($DB, $RECORD, $MAILER) {
		$this->db 		= 	$DB;
		$this->record	=	$RECORD;
		$this->mailer	=	$MAILER;		
	}
	
	
	function deliverLeadToLocation($pid) {
		//$sql = "SELECT * FROM Addresses WHERE GeoLocationStatus='200' AND Person_id='".$pid."' ORDER BY isPrimary DESC LIMIT 1";
		$sql = "
		SELECT
			*
		FROM
			Persons 
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
			LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
		WHERE 
			GeoLocationStatus='200' 
		AND 
			Persons.Person_id='".$pid."'
		ORDER BY 
			Addresses.isPrimary DESC 
		LIMIT 1	
		";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		if(! empty($snd['empty_result'])  && $snd['empty_result']):
			$assignedTo = 0;
		else:
			//print_r($snd);
			$lat = $snd['Lattitude'];
			$lng = $snd['Longitude'];
	
			$sql2 = "
			SELECT
				Offices.*,
				(((acos(sin((".$lat."*pi()/180)) * sin((Offices.office_lat*pi()/180)) + cos((".$lat."*pi()/180)) * cos((Offices.office_lat*pi()/180)) * cos(((".$lng." - Offices.office_lng)*pi()/180))))*180/pi())*60*1.1515) as distance
			FROM
				Offices
			ORDER BY
				distance
			LIMIT 1
			";
			//echo $sql;
			$snd2 = $this->db->get_single_result($sql2);
			//print_r($snd2);
			//echo "<hr>";
			$json['id'] = $snd2['Offices_id'];
			$json['text'] = $snd2['office_Name'];
			$json['distance'] = round($snd2['distance'], 1);
			//echo "LOCATION:".$snd2['office_Name']."<br>\n";
		
		
			settype($snd2['distance'], "int");
			settype($snd2['DefaultAssignedMinDistance'], "int");
			
			if($snd2['DefaultAssignedMinDistance'] == 0):
				$distanceCheck = true;
			else:
				if($snd2['distance'] < $snd2['DefaultAssignedMinDistance']):				
					$distanceCheck = true;			
				else:
					$distanceCheck = false;
				endif;				
			endif;
			
			if($distanceCheck):
				//echo "DISTANCE CHECK PASSED (".$snd2['distance']."/".$snd2['DefaultAssignedMinDistance'].")<br>\n";
			endif;
			
			
			if($snd2['DefaultIncomeRanges'] == ''):
				$incomeCheck = true;
			else:
				$rangeArray = json_decode($snd2['DefaultIncomeRanges'], true);
				//print_r($rangeArray);
				if(in_array($snd['prQuestion_631'], $rangeArray)):
					$incomeCheck = true;
				else:
					$incomeCheck = false;
				endif;
			endif;
			if($incomeCheck):
				//echo "INCOME CHECK PASSED (".$snd['prQuestion_631'].")<br>\n";
			endif;
			
			if(($distanceCheck == true) && ($incomeCheck == true)):
				$assignedTo = $snd2['DefaultAssignedUser'];

				$upd_sql = "UPDATE Persons SET Offices_id='".$json['id']."', Assigned_userID='".$assignedTo."' WHERE Person_id='".$pid."'";
				//echo $upd_sql."<br>\n";
				$upd_snd = $this->db->mysqli->query($upd_sql);
			
				if($assignedTo != 0):
				// INTERNAL NOTIFICATION //
			$notifySubject = $this->record->get_personType($pid)." Assigned: ".$this->record->get_personName($pid);
$notifyBody = "
This is an automated message to notify you that a ".$this->record->get_personType($pid)." has been assigned to you (".$this->record->get_personName($pid)."). 
A record of this action has been added to the record's log.

You may view their record here:
https://kiss.kelleher-international.com/profile/".$pid."

KISS Contract Manager

";
			$mail = $this->mailer;
			//$mail = new phpmailer();
			$mail->IsHTML(false);
			$mail->From = 'no-reply@kelleher-international.com';
			$mail->FromName = 'Kelleher International KISS System';
			$mail->Subject = $notifySubject;
			$mail->Body = $notifyBody;
			//$mail->AddAddress('matt@kelleher-international.com');
			$mail->AddAddress($this->record->get_userEmail($assignedTo));
			//$mail->AddAddress('rich@kelleher-international.com');
			$mail->Send();		
			$this->record->log_action($pid, 'Rep Assigned', 'Assigned_userID', $this->record->get_userName($assignedTo), $this->record->get_userName($assignedTo), 0);
			endif;
			
			$upd_sql_2 = "UPDATE Persons SET AssignedDate='".time()."' WHERE Person_id='".$pid."'";
			$upd_snd_2 = $this->db->mysqli->query($upd_sql_2);
			endif;
		endif;
		//echo json_encode($json);
	}
	
	
}