<?php
/*! \class Record class.record.php "class.record.php"
 *  \brief This class is used to render the dashboard elements.
 */
class Record {
	/*! \fn obj __constructor($DB)
		\brief Record class constructor.
		\param	$DB db class object
		\return null
	*/
	public function __construct($DB) {
		$this->db 			= 	$DB;
		$this->highIncomes	=	array('$250K - $500K', '$500K - $1M', '$1M - $5M', 'More than $5M');
	}
	
	function checkForResend($person_id, $email_subject, $durration=10) {
		$timeStamp = (time() - (60 * $durration));
		$ck_sql = "SELECT count(*) as count FROM PersonsCommHistory WHERE Person_id='".$person_id."' AND MessageSubject='".$this->db->mysqli->escape_string($email_subject)."' AND MessageSentDate >='".$timeStamp."'";
		$ck_snd = $this->db->get_single_result($ck_sql);
		if($ck_snd['count'] == 0):
			return true;
		else:
			return false;
		endif;		
	}
	
	function options_userSelect($values=array(), $hideUnassigned=false) {
		$g_sql = "SELECT * FROM UserClasses ORDER BY userClass_id DESC";
		$g_snd = $this->db->get_multi_result($g_sql);		
		ob_start();	
		echo "HIDE:".$hideUnassigned;	
		if($hideUnassigned != 1):
		?><option value="0" <?php echo ((in_array(0, $values))? 'selected':'')?>>UNASSIGNED</option><?php
		endif;
		foreach($g_snd as $g_dta):
			$groupID = $g_dta['userClass_id'];
			$u_sql = "SELECT * FROM `Users` WHERE userClass_id='".$groupID."' AND userStatus='1'";
			$u_fnd = $this->db->get_multi_result($u_sql, true);
			if($u_fnd > 0):
				$u_snd = $this->db->get_multi_result($u_sql);
				?><optgroup label="<?php echo $g_dta['userClass_name']?>"><?php
				foreach($u_snd as $u_dta):
					?><option value="<?php echo $u_dta['user_id']?>" <?php echo ((in_array($u_dta['user_id'], $values))? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option><?php
				endforeach;
				?></optgroup><?php
			endif;	
		endforeach;
		$userSelect = ob_get_clean();
		return $userSelect;
	}
	
	function options_userSelectAll($values=array(), $hideUnassigned=false) {
		$g_sql = "SELECT * FROM UserClasses ORDER BY userClass_id DESC";
		$g_snd = $this->db->get_multi_result($g_sql);		
		ob_start();	
		echo "HIDE:".$hideUnassigned;	
		if($hideUnassigned != 1):
		?><option value="0" <?php echo ((in_array(0, $values))? 'selected':'')?>>UNASSIGNED</option><?php
		endif;
		foreach($g_snd as $g_dta):
			$groupID = $g_dta['userClass_id'];
			$u_sql = "SELECT * FROM `Users` WHERE userClass_id='".$groupID."' AND userStatus='1'";
			$u_fnd = $this->db->get_multi_result($u_sql, true);
			if($u_fnd > 0):
				$u_snd = $this->db->get_multi_result($u_sql);
				?><optgroup label="<?php echo $g_dta['userClass_name']?>"><?php
				foreach($u_snd as $u_dta):
					?><option value="<?php echo $u_dta['user_id']?>" <?php echo ((in_array($u_dta['user_id'], $values))? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option><?php
				endforeach;
				?></optgroup><?php
			endif;	
		endforeach;
		?><optgroup label="Inactive Accounts"><?php
		$groupID = $g_dta['userClass_id'];
		$u_sql = "SELECT * FROM `Users` WHERE userStatus='0'";
		$u_fnd = $this->db->get_multi_result($u_sql, true);
		if($u_fnd > 0):
			$u_snd = $this->db->get_multi_result($u_sql);
			foreach($u_snd as $u_dta):
				?><option value="<?php echo $u_dta['user_id']?>" <?php echo ((in_array($u_dta['user_id'], $values))? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option><?php
			endforeach;
		endif;	
		?></optgroup><?php
		
		?><optgroup label="Archived Accounts"><?php
		$groupID = $g_dta['userClass_id'];
		$u_sql = "SELECT * FROM `Users` WHERE userStatus='2'";
		$u_fnd = $this->db->get_multi_result($u_sql, true);
		if($u_fnd > 0):
			$u_snd = $this->db->get_multi_result($u_sql);
			foreach($u_snd as $u_dta):
				?><option value="<?php echo $u_dta['user_id']?>" <?php echo ((in_array($u_dta['user_id'], $values))? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option><?php
			endforeach;
		endif;	
		?></optgroup><?php
		$userSelect = ob_get_clean();
		return $userSelect;
	}
	
	function options_leadSources($values=array(), $incudeReps=false) {
		// $c_sql = "SELECT * FROM DropDown_SourceType ORDER BY SourceType_order";
		// $c_snd = $this->db->get_multi_result($c_sql);
		// ob_start();
		?><option></option>
		<option value="Architectural Digest">Architectural Digest</option><?php
			if($incudeReps) {
				$s_sql = "SELECT * FROM DropDown_LeadSource WHERE  Source_status='1' ORDER BY Source_name";
			} else {
				$s_sql = "SELECT * FROM DropDown_LeadSource WHERE Source_status='1' AND Source_display='1' ORDER BY Source_name";
			}
			//echo $s_sql."<br>\n";
			$s_fnd = $this->db->get_multi_result($s_sql, true);
			if($s_fnd > 0):
				$s_snd = $this->db->get_multi_result($s_sql);
				
				foreach($s_snd as $s_dta):
					?><option value="<?php echo $s_dta['Source_name']?>" <?php echo ((in_array($s_dta['Source_name'], $values))? 'selected':'')?>><?php echo $s_dta['Source_name']?></option><?php
				endforeach;
				?></optgroup><?php
			endif;	
		$stateSelect = ob_get_clean();
		return $stateSelect;
	}
	
	function options_stageSelect($values=array()) {
		ob_start();
		$u_sql = "SELECT * FROM LeadStages ORDER BY LeadStages_id";
		$u_fnd = $this->db->get_multi_result($u_sql, true);
		if($u_fnd > 0):
			$u_snd = $this->db->get_multi_result($u_sql);
			foreach($u_snd as $u_dta):
				?><option value="<?php echo $u_dta['LeadStages_id']?>" <?php echo ((in_array($u_dta['LeadStages_id'], $values))? 'selected':'')?>><?php echo $u_dta['LeadStages_name']?></option><?php
			endforeach;
		endif;	
		$userSelect = ob_get_clean();
		return $userSelect;
	}
	
	function options_officeSelect($values=array()) {
		ob_start();		
		$sql = "SELECT * FROM Offices ORDER BY Offices_id";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			?><option value="<?php echo $dta['Offices_id']?>" <?php echo ((in_array($dta['Offices_id'], $values))? 'selected':'')?>><?php echo $dta['office_Name']?></option><?php
		endforeach;	
		$userSelect = ob_get_clean();
		return $userSelect;
	}
	
	function options_allStates($values=array()) {
		$c_sql = "SELECT * FROM SOURCE_Contries";
		$c_snd = $this->db->get_multi_result($c_sql);
		ob_start();
		foreach($c_snd as $c_dta):
			$countryID = $c_dta['CountryCode'];
			$s_sql = "SELECT * FROM SOURCE_States WHERE CountryCode='".$countryID."'";
			$s_fnd = $this->db->get_multi_result($s_sql, true);
			if($s_fnd > 0):
				$s_snd = $this->db->get_multi_result($s_sql);
				?><optgroup label="<?php echo $c_dta['Country']?>"><?php
				foreach($s_snd as $s_dta):
?><option value="<?php echo $c_dta['CountryCode']?>|<?php echo $s_dta['StateCode']?>" <?php echo ((in_array($c_dta['CountryCode']."|".$s_dta['StateCode'], $values))? 'selected':'')?>><?php echo $s_dta['State']?></option>
<?php
				endforeach;
				?></optgroup><?php
			endif;	
		endforeach;
		$stateSelect = ob_get_clean();
		return $stateSelect;
	}
	
	function options_allCountries($values=array()) {
		$c_sql = "SELECT * FROM SOURCE_Contries";
		$c_snd = $this->db->get_multi_result($c_sql);
		ob_start();
		foreach($c_snd as $c_dta):
			$countryID = $c_dta['CountryCode'];
			?><option value="<?php echo $c_dta['CountryCode']?>" <?php echo ((in_array($c_dta['CountryCode'], $values))? 'selected':'')?>><?php echo $c_dta['Country']?></option><?php
		endforeach;
		$stateSelect = ob_get_clean();
		return $stateSelect;
	}
	
	public function render_userSelect($user_id, $fieldID='assignedSelect') {
		$g_sql = "SELECT * FROM UserClasses ORDER BY userClass_id DESC";
		$g_snd = $this->db->get_multi_result($g_sql);
		ob_start();
		foreach($g_snd as $g_dta):
			$groupID = $g_dta['userClass_id'];
			$u_sql = "SELECT * FROM `Users` WHERE userClass_id='".$groupID."' AND userStatus='1'";
			$u_fnd = $this->db->get_multi_result($u_sql, true);
			if($u_fnd > 0):
				$u_snd = $this->db->get_multi_result($u_sql);
				?><optgroup label="<?php echo $g_dta['userClass_name']?>"><?php
				foreach($u_snd as $u_dta):
?><option value="<?php echo $u_dta['user_id']?>" <?php echo (($u_dta['user_id'] == $user_id)? 'selected':'')?>><?php echo $u_dta['firstName']?> <?php echo $u_dta['lastName']?></option>
<?php
				endforeach;
				?></optgroup><?php
			endif;	
		endforeach;
		$userSelect = ob_get_clean();
		?>
        <div class="form-group m-form__group row">
            <label class="col-form-label col-3">
                User:
            </label>
            <div class="col-9">
                <select class="form-control m-select2" id="<?php echo $fieldID?>" name="value" style="width:100%;">
                 <option value="0">&nbsp;</option>
				 <?php echo $userSelect?>   
                </select>
            </div>
        </div>
        <?php
	}
	
	public function get_userName($user_id) {
		$sql = "SELECT * FROM `Users` WHERE `user_id`='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return substr($snd['firstName'], 0, 1).' '.$snd['lastName'];		
	}
	
	public function get_FulluserName($user_id) {
		$sql = "SELECT * FROM `Users` WHERE `user_id`='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['firstName'].' '.$snd['lastName'];		
	}
	
	public function get_userEmail($user_id) {
		$sql = "SELECT * FROM `Users` WHERE `user_id`='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['email'];		
	}
	
	public function get_userRoomURL($user_id) {
		$sql = "SELECT * FROM `Users` WHERE `user_id`='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['userMeetingURL'];		
	}
	
	public function get_personName($person_id) {
		$sql = "SELECT CONCAT(COALESCE(FirstName, ''),' ',COALESCE(LastName, '')) as fullName, Person_id, HCS FROM Persons WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		if ($snd['HCS'] == 1) {
			return $snd['Person_id'].' (HPC)';
		} else {
			return $snd['fullName'];
		}
	}
	
	public function get_personFirstName($person_id) {
		$sql = "SELECT FirstName FROM Persons WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['FirstName'];		
	}

	public function get_personLastName($person_id) {
		$sql = "SELECT LastName FROM Persons WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['LastName'];		
	}

	public function get_personMiddleName($person_id) {
		$sql = "SELECT MiddleName FROM Persons WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['MiddleName'];		
	}

	public function get_personDateOfBirth($person_id) {
		$sql = "SELECT DateOfBirth FROM Persons WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		$dateofbirth = new \DateTime($snd['DateOfBirth']);
           
		return $dateofbirth;		
	}

	public function get_personType($person_id) {
		$sql = "SELECT PersonsTypes_text FROM Persons INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id WHERE Persons.Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['PersonsTypes_text'];		
	}
	
	public function get_personOffice($person_id) {
		$sql = "SELECT office_Name FROM Persons INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id WHERE Persons.Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['office_Name'];		
	}
	
	public function getOfficeName($officeID) {
		$sql = "SELECT * FROM Offices WHERE Offices_id='".$officeID."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['office_Name'];
	}
	
	public function get_personGender($person_id) {
		$sql = "SELECT Gender FROM Persons WHERE Persons.Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['Gender'];		
	}
	
	public function get_personEmail($person_id) {
		$sql = "SELECT Email FROM Persons WHERE Persons.Person_id='".$person_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['Email'];		
	}
	
	public function get_personMMcolor($person_id) {
		$sql = "SELECT B.Color_id FROM Persons A JOIN PersonsFlags B ON A.Person_Id = B.Person_Id WHERE A.Person_id='".$person_id."'";
		$snd = $this->db->get_multi_result($sql);
		$output = '';
		
		foreach ( $snd  as $color ){
			if(($color['Color_id'] != 0) && ($color['Color_id'] != 21)) {	
				$output = '';
					$mm_stat_sql = "SELECT * FROM PersonsColors WHERE Color_id='" . $color['Color_id']  . "'";
					$mm_stat_dta = $this->db->get_single_result($mm_stat_sql);
					$output .= '<span class="m-badge m-badge--metal m-badge--wide" style="background-color:'.$mm_stat_dta['Color_hex'].';">'.$mm_stat_dta['Color_title'].'</span>';
				}
			}
			
		return $output;	
	}
	
	public function get_mmcolor($color_id) {
		$sql = "SELECT * FROM PersonsColors WHERE Color_id='".$color_id."'";
		$dta = $this->db->get_single_result($sql);	
		ob_start();	
		?><span class="m-badge m-badge--success m-badge--wide" style="background-color:<?php echo $dta['Color_hex']?>; border:#EAEAEA solid 1px;"><?php echo $dta['Color_title']?></span><?php
		return ob_get_clean();
	}
	
	public function get_personLastUpdate($person_id) {
		$sql = "SELECT LastNoteAction FROM Persons WHERE Person_id='".$person_id."'";
		//echo $sql;
		$dta = $this->db->get_single_result($sql);	
		$data = json_decode($dta['LastNoteAction'], true);
		return $data;
	}
	
	function json_parse($text){
    	$parsedText = str_replace(chr(10), " ", $text);
    	return str_replace(chr(13), " ", $parsedText);
	}
	
	function render_officeRadio($office_id, $fieldName='value', $class='') {
		$sql = "SELECT * FROM Offices ORDER BY Offices_id";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			?>
            <label class="m-radio m-radio--bold">
            	<input type="radio" name="<?php echo $fieldName?>" class="<?php echo $class?>" value="<?php echo $dta['Offices_id']?>" <?php echo (($dta['Offices_id'] == $office_id)? 'checked':'')?>>
            	<?php echo $dta['office_Name']?>
            	<span></span>
        	</label>
            <?php
		endforeach;	
	}

	
	function render_podRadio($pod_id, $fieldName='value', $class='') {
		$sql = "SELECT * FROM Pods ORDER BY Pod_id";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			?>
            <label class="m-radio m-radio--bold">
            	<input type="radio" name="<?php echo $fieldName?>" class="<?php echo $class?>" value="<?php echo $dta['Pod_id']?>" <?php echo (($dta['Pod_id'] == $pod_id)? 'checked':'')?>>
            	<?php echo $dta['pod_Name']?>
            	<span></span>
        	</label>
            <?php
		endforeach;	
	}
	
	
	function render_colorRadio($person_id, $fieldName='value', $class='') {
		$sql = "SELECT A.Color_Id,A.Color_title,A.Color_hex,B.Color_Id as Checked FROM PersonsColors A
		LEFT OUTER JOIN PersonsFlags B on B.Color_Id=A.Color_Id  AND B.Person_Id = '" . $person_id . "'
		ORDER BY Color_order";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			?>
            <label class="m-checkbox m-radio--bold">
            	<div style="float:right; width:30px; height:15px; background-color:<?php echo $dta['Color_hex']?>; border:#EAEAEA solid 1px;">&nbsp;</div>
                <input type="checkbox" name="value[]" value="<?php echo $dta['Color_Id']?>" <?php echo ($dta['Checked']) ? 'checked':''?>>
            	<?php echo $dta['Color_title']?>&nbsp;
            	<span></span>
                <div style="clear:right;"></div>
        	</label>
            <?php
		endforeach;
	}
	
	function log_action($personID, $Action, $QuestionID, $OldValue, $NewValue, $UserID, $logTime='') {
		if ($logTime != "") {
			$insertTime = $logTime;
		} else {
			$insertTime = time();
		}
        $fields = "PersonsLogs_personID, PersonsLogs_updateTime, PersonsLogs_action, PersonsLogs_question, PersonsLogs_oldValue, PersonsLogs_newValue, PersonsLogs_updatedBy, PersonsLogs_IP, PersonsLogs_isMatchQ";
		$values = "'".$personID."', '".$insertTime."','".$this->db->mysqli->escape_string($Action)."','".$QuestionID."','".$this->db->mysqli->escape_string($OldValue)."','".$this->db->mysqli->escape_string($NewValue)."','".$UserID."','".$_SERVER['REMOTE_ADDR']."','0'";
    	$ins_query = "INSERT INTO PersonsLogs ($fields) VALUES ($values)";
	    //echo $ins_query;
		$ins_send = $this->db->mysqli->query($ins_query);
		
		$upd_p_sql = "UPDATE Persons SET DateUpdated='".time()."' WHERE Person_id='".$personID."'";
		$upd_p_snd = $this->db->mysqli->query($upd_p_sql);
	}

	
	function render_stagesRadio($stage_id) {
		$sql = "SELECT * FROM LeadStages ORDER BY LeadStages_id";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			?>
            <label class="m-radio m-radio--bold">
            	<input type="radio" name="value" value="<?php echo $dta['LeadStages_id']?>" <?php echo (($dta['LeadStages_id'] == $stage_id)? 'checked':'')?>>
            	<?php echo $dta['LeadStages_name']?>
            	<span></span>
        	</label>
            <?php
		endforeach;		
		
	}
	
	function render_allImages($personID) {
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' ORDER BY PersonsImages_status DESC, PersonsImages_order ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?><div class="col-2 img-sort-dragger" data-id="<?php echo $dta['PersonsImages_id']?>" onclick="previewImage(<?php echo $dta['PersonsImages_id']?>, '<?php echo $filePath?>');" style="background-image:url('<?php echo $filePath?>'); background-size:cover; margin-bottom:2px; border-right:solid 2px #FFFFFF; cursor:pointer;"><img src="<?php echo $filler?>" class="img-fluid" style="min-height:30px;" /></div><?php
			endforeach;	
		} else {
			?><div class="col-sm-12"><em>no images found</em></div><?php
		}
		return ob_get_clean();		
	}
	
	function render_ImageLibrary($personID) {
		ob_start();
		?><h4>PRIMARY PHOTO</h4><?php
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status='2'";
		$snd = $this->db->get_multi_result($sql);
		
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?><div class="text-center"><img src="<?php echo $filePath?>" class="img-fluid" /></div><?php
			endforeach;	
		} else {
			?><div class="text-center"><em>no images found</em></div><?php
		}
		
		?>
        <hr />
        <h4>APPROVED PHOTOS</h4>
		<?php
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status='1' ORDER BY PersonsImages_order ASC";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?><img src="<?php echo $filePath?>" class="img-fluid" /><?php
			endforeach;	
		} else {
			?><div class="text-center"><em>no images found</em></div><?php
		}
		?>
		<hr />
        <h4>PRIVATE PHOTOS</h4>
        <?php
		$sql = "SELECT * FROM PersonsImages WHERE Person_id='".$personID."' AND PersonsImages_status='0' ORDER BY PersonsImages_order ASC";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] != 1) {
			foreach($snd as $dta):
			$filePath = '/client_media/'.$this->get_image_directory($personID).'/'.$personID.'/'.$dta['PersonsImages_path'];
			if($dta['PersonsImages_status'] == 0) {
				$filler = '/assets/app/media/img/users/filler-large-red.png';	
			} else {
				$filler = '/assets/app/media/img/users/filler-large.png';
			}
			?><img src="<?php echo $filePath?>" class="img-fluid" /><?php
			endforeach;	
		} else {
			?><div class="text-center"><em>no images found</em></div><?php
		}
		return ob_get_clean();		
	}
	
	function render_PhonesForm($personID, $hideDelete=false) {
		$sql = "SELECT * FROM Phones WHERE Person_id='".$personID."' AND isActive='1' ORDER BY isPrimary DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] == 1) {
			return '&nbsp;';
		} else {
			ob_start();
			foreach($snd as $dta):
			$elementID = 'oldPhone_'.$dta['Phones_id'];
			?>
            <div class="form-group m-form__group row align-items-center" id="<?php echo $elementID?>">
				<div class="col-md-3">
					<div class="m-form__group m-form__group--inline">
						<input type="hidden" name="Phones_id[]" value="<?php echo $dta['Phones_id']?>" />
                        <div class="m-form__label">
							<label>Type:</label>
						</div>
						<div class="m-form__control">
							<select name="PhoneType[]" class="form-control m-input input-sm">
                            	<option value=""></option>
                                <option value="Home" <?php echo (($dta['PhoneType'] == 'Home')? 'selected':'')?>>Home</option>
                                <option value="Work" <?php echo (($dta['PhoneType'] == 'Work')? 'selected':'')?>>Work</option>
                                <option value="Cell" <?php echo (($dta['PhoneType'] == 'Cell')? 'selected':'')?>>Cell</option>
                                <option value="Main" <?php echo (($dta['PhoneType'] == 'Main')? 'selected':'')?>>Main</option>
                                <option value="Other" <?php echo (($dta['PhoneType'] == 'Other')? 'selected':'')?>>Other</option>
                                <option value="FAX" <?php echo (($dta['PhoneType'] == 'FAX')? 'selected':'')?>>FAX</option>
                            </select>
						</div>
					</div>
					<div class="d-md-none m--margin-bottom-10"></div>
				</div>
				<div class="col-md-4">
					<div class="m-form__group m-form__group--inline">
						<div class="m-form__label">
							<label class="m-label m-label--single">Number:</label>
						</div>
						<div class="m-form__control">
							<input type="text" name="Phone_number[]" class="form-control m-input input-sm" placeholder="Enter contact number" value="<?php echo $dta['Phone_number']?>" />
						</div>
					</div>
					<div class="d-md-none m--margin-bottom-10"></div>
				</div>
				<div class="col-md-2">
					<div class="m-radio-inline">
						<label class="m-radio m-radio--state-success">
							<input type="radio" name="isPrimary" value="<?php echo $dta['Phones_id']?>" <?php echo (($dta['isPrimary'] == '1')? 'checked':'')?>>Primary<span></span>
						</label>
					</div>
				</div>
				<div class="col-md-3">
                	<?php if(!$hideDelete): ?>
					<a href="javascript:removeOldPhone('<?php echo $elementID?>','<?php echo $dta['Phones_id']?>')" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill">
						<span>
							<i class="la la-trash-o"></i>
							<span>Delete</span>
						</span>
					</a>
                    <?php endif; ?>
				</div>
			</div>
            <?php
			endforeach;
		}		
	}
	
	function get_primaryPhone($personID, $sms=false, $returnString=false) {
		$sql = "SELECT * FROM Phones WHERE Person_id='".$personID."' AND isActive='1' AND isPrimary = '1'";
		$snd = $this->db->get_single_result($sql);
		
		if($snd['Phone_raw'] == '') {
			$upd_sql = "UPDATE Phones SET Phone_raw='".$this->formatPhoneForRC($snd['Phone_number'])."' WHERE Phones_id='".$snd['Phones_id']."'";
			//echo $upd_sql."<br>\n";
			$this->db->mysqli->query($upd_sql);
			
			$sql = "SELECT * FROM Phones WHERE Person_id='".$personID."' AND isActive='1' AND isPrimary = '1'";
			$snd = $this->db->get_single_result($sql);		
		}
		
		if($snd['empty_result'] == 1) {
			return '&nbsp;';
		} else {
			if($returnString) {
				$number = $snd['Phone_number']; 
			} else {
				$number = '<a href="javascript:void(launchRC(\'ringout\', \''.$snd['Phone_raw'].'\'))" class="raw_phone" data-raw="'.$snd['Phone_raw'].'">'.$snd['PhoneType'].' '.$snd['Phone_number'].'</a>';
				if($sms) {
					$number .= '&nbsp;<a href="javascript:void(launchRC(\'sms\', \''.$snd['Phone_raw'].'\'))" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only pull-right" style="margin-right:4px;"><i class="la la-envelope"></i></a>';
				}
			}
			return $number;
		}
	}
	
	function get_otherPhone($personID, $sms=false, $returnString=false) {
		$sql = "SELECT * FROM Phones WHERE Person_id='".$personID."' AND isActive='1' ORDER BY isPrimary DESC LIMIT 25 OFFSET 1";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] == 1) {
			return '&nbsp;';
		} else {
			if($returnString) {
				foreach($snd as $dta) {
					$number[] = $dta['Phone_number'];	
				}
			} else {
				foreach($snd as $dta) {
					if($dta['Phone_raw'] == '') {
						$upd_sql = "UPDATE Phones SET Phone_raw='".$this->formatPhoneForRC($dta['Phone_number'])."' WHERE Phones_id='".$dta['Phones_id']."'";
						//echo $upd_sql."<br>\n";
						$this->db->mysqli->query($upd_sql);
						
						$sql = "SELECT * FROM Phones WHERE Phones_id='".$dta['Phones_id']."'";
						$dta = $this->db->get_single_result($sql);		
					}
					
					if($sms) {
						$number[] = '<a href="javascript:void(launchRC(\'ringout\', \''.$dta['Phone_raw'].'\'))" class="raw_phone" data-raw="'.$dta['Phone_raw'].'">'.$dta['PhoneType'].' '.$dta['Phone_number'].'</a>&nbsp;<a href="javascript:void(launchRC(\'sms\', \''.$dta['Phone_raw'].'\'))" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only" style=""><i class="la la-envelope"></i></a>';
					} else {
						$number[] = '<a href="javascript:void(launchRC(\'ringout\', \''.$dta['Phone_raw'].'\'))" class="raw_phone" data-raw="'.$dta['Phone_raw'].'">'.$dta['PhoneType'].' '.$dta['Phone_number'].'</a>';
					}
				}				
			}
			return implode("<br>", $number);
		}
	}
	
	function get_rawPhones($personID) {
		$sql = "SELECT Phone_raw FROM Phones WHERE Person_id='".$personID."' AND isActive='1' ORDER BY isPrimary DESC LIMIT 25";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] == 1) {
			return array();
		} else {
			$phones = array();
			foreach($snd as $dta) {
				$phones[] = $dta['Phone_raw'];	
			}
			return $phones;
		}
	}
	
	function formatPhoneForRC($phone) {
		if(strlen($phone) > 0) {
			$number = preg_replace('/\D+/', '', $phone);
			if(strlen($number) <= 10) {
				$number = '1'.$number;
			}
		} else {
			$number = '';
		}
		return $number;
	}
	
	function render_genderStatus($gender) {
		ob_start();
		if($gender == 'F'):
			$string = 'FEMALE';
			$icon = 'fa fa-female';
		elseif($gender == 'M'):
		 	$string = 'MALE';
			$icon = 'fa fa-male';
		endif;	
		?>
        <li class="m-nav__item">
            <a href="#" class="m-nav__link">
                <i class="m-nav__link-icon <?php echo $icon?>"></i>
                <span class="m-nav__link-text">
                    <?php echo $string?>                                    
                </span>
            </a>
        </li>
       	<?php	
		return ob_get_clean();
	}
	
	function get_personAge($dob_datetime, $returnNum = false) {
		if(($dob_datetime == 0) || ($dob_datetime == '')):
			if($returnNum) {
				$string = 0;
			} else {
				$string = '<span class="m-badge m-badge--warning m-badge--wide">AGE UNKNOWN</span>';
			}
		elseif (is_numeric($dob_datetime)):
			$from   = new DateTime();
			$from->setTimestamp($dob_datetime);
			$to   	= new DateTime('today');
			$age 	= $from->diff($to)->y;
			$string = $age;
		else: 
			$from   = new DateTime($dob_datetime);
			//$from ->setTimestamp($dob_epoch);
			$to   	= new DateTime('today');
			$age 	= $from->diff($to)->y;
			$string = $age;
		endif;
		return $string;	
	}

	function get_personAgeByDOB($dob_datetime, $returnNum = false) {
		echo 'DateTime = ' . print_r($dob_datetime,true) . ' ; ';
		if(($dob_datetime == 0) || ($dob_datetime == '')):
			if($returnNum) {
				$string = 0;
			} else {
				$string = '<span class="m-badge m-badge--warning m-badge--wide">AGE UNKNOWN</span>';
			}
		elseif (is_numeric($dob_datetime)):
			$from   = new DateTime();
			$from->setTimestamp($dob_datetime);
			$to   	= new DateTime('today');
			$age 	= $from->diff($to)->y;
			$string = $age;
		else: 
			$from   = new DateTime($dob_datetime);
			//$from ->setTimestamp($dob_epoch);
			$to   	= new DateTime('today');
			$age 	= $from->diff($to)->y;
			$string = $age;
		endif;
		return $string;	
	}
	
	
	function render_ageStatus($dob_epoch) {
		ob_start();
		if(($dob_epoch == 0) || ($dob_epoch == '')):
			$string = '<span class="m-badge m-badge--warning m-badge--wide">AGE UNKNOWN</span> <small>(NO DATA)</small>';
		else:
			$from 	= new DateTime(date("Y-m-d", $dob_epoch));
			$to   	= new DateTime('today');
			$age 	= $from->diff($to)->y;
			$string = $age.' years old <small>('.date("m/d/Y", $dob_epoch).')</small>';
		endif;
		?>
        <li class="m-nav__item">
            <a href="#" class="m-nav__link">
                <i class="m-nav__link-icon flaticon-time-3"></i>
                <span class="m-nav__link-text">
                    <?php echo $string?>                                   
                </span>
            </a>
        </li>
        <?php
		return ob_get_clean();
	}
	
	function render_clientStatus($personID) {
		$status = $this->get_clientStatus($personID);
		ob_start();
		if($status['status'] == 0):
			?><span class="m-badge m-badge--warning m-badge--wide">NO STATUS DATA</span><?php
		elseif($status['status'] == 1):
			?><span class="m-badge m-badge--success m-badge--wide">VALID through <?php echo $status['end']?></span><?php
		elseif($status['status'] == 2):
            ?><span class="m-badge m-badge--danger m-badge--wide">INVALID expired <?php echo $status['end']?></span><?php
		endif;
		return ob_get_clean();		
	}
	
	function get_clientStatus($personID) {
		$sql = "SELECT prQuestion_676, prQuestion_677 FROM PersonsProfile WHERE Person_id='".$personID."'";
		$snd = $this->db->get_single_result($sql);
		if(($snd['prQuestion_676'] == '0') || ($snd['prQuestion_677'] == '0') || ($snd['prQuestion_676'] == NULL) || ($snd['prQuestion_677'] == NULL)) {
			// FAILED STATUS //
			$return['status'] = 0;
			$return['start'] = '';
			$return['end'] = '';	
		} else {
			if(time() <= $snd['prQuestion_677']) {
				$return['status'] = 1;
				$return['start'] = date("m/d/y", $snd['prQuestion_676']);
				$return['end'] = date("m/d/y", $snd['prQuestion_677']);
			} else {
				$return['status'] = 2;
				$return['start'] = date("m/d/y", $snd['prQuestion_676']);
				$return['end'] = date("m/d/y", $snd['prQuestion_677']);
			}
		}
		return $return;		
	}
	
	function get_leadStage($stage_id) {
		$sql = "SELECT * FROM LeadStages WHERE LeadStages_id='".$stage_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['LeadStages_name'];		
	}
	
	/*! \fn obj get_defaultImage($personID)
		\brief Get the default image (only if no images has been uploaded for the user)
		\param	$personID the ID of the record looking for the edefault image
		\return string
	*/
	function get_defaultImage($personID) {
		$sql = "SELECT Gender FROM Persons WHERE Person_id='".$personID."'";
		$snd = $this->db->get_single_result($sql);
		$gender = $snd['Gender'];
		if($gender == 'M') {
			$imgPath = "/assets/app/media/img/users/sample-m.jpg";
		} else {
			$imgPath = "/assets/app/media/img/users/sample-f.jpg";
		}
		return $imgPath;		
	}

	function get_personsColorSpan($personID) {
		$sql = " Select c.color_title,c.color_hex
		from PersonsFlags a 
		join PersonsColors c 
		on a.color_id=c.color_id 
		WHERE a.Person_id='".$personID."' ";
		
		$c_snd = $this->db->get_multi_result($sql);
		
		$result = '';
		if(!isset($c_snd['empty_result'])):	
			foreach($c_snd as $row):
				$result .= "<span class='m-badge m-badge--metal m-badge--wide' style='background-color:" . $row['color_hex'] . ";'>" . $row['color_title'] ."</span>";
			endforeach;
		endif;
		return $result;		
	}

	function get_PrimaryImage($personID, $cropped=false) {
		$sql = " SELECT * FROM PersonsImages WHERE PersonsImages.Person_id='".$personID."' AND PersonsImages_status='2'";
		$snd = $this->db->get_single_result($sql);
		if($snd['PersonsImages_path'] == '') {
			$PrimaryImage = $this->get_defaultImage($personID);
		} else {
			$PrimaryImage = "/client_media/".$this->get_image_directory($personID)."/".$personID."/".$snd['PersonsImages_path'];
		}
		//echo $PrimaryImage;
		if ($cropped) {
			include_once("class.images.php");
			$img = new Images($this->db, $this);
			$tmpImageName = '../client_media/thumbs/temp.image.'.$personID.'.jpg';
			$img->resize_image('force','..'.$PrimaryImage,$tmpImageName,100,100);
			$PrimaryImage = $tmpImageName;
			$PrimaryImage = $this->generateThumbnail($tmpImageName);			
		}
		return $PrimaryImage;
	}
	
	function get_introStatus($statusID) {
		$sql = "SELECT * FROM DropDown_DateStatus WHERE Date_status='".$statusID."'";	
		$snd = $this->db->get_single_result($sql);
		return '<span class="m--font-'.$snd['kimsClass'].'">'.$snd['Date_statusText'].'</span>';
		
	}
	
	function get_primaryAddress($personID) {
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$personID."' ORDER BY isPrimary DESC LIMIT 1";
		$dta = $this->db->get_single_result($sql);
		$adrString = '';
		if(isset($dta['empty_result'])) {
			$adrString .= '';
		} else {
			if($dta['Street_1'] != '') {
				$adrString .= $dta['Street_1'].'<br>';
			}
			$adrString .= $dta['City'].' '.$dta['State'].' '.$dta['Postal'].' '.$dta['Country'];
		}
		return $adrString;
	}
	
	function get_primaryTruncatedAddress($personID) {
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$personID."' ORDER BY isPrimary DESC LIMIT 1";
		$dta = $this->db->get_single_result($sql);
		$adrString = '';
		if(isset($dta['empty_result'])) {
			$adrString .= '';
		} else {
			$adrString .= $dta['City'].' '.$dta['State'];
		}
		return $adrString;
	}

	function get_primaryCityStateAddress($personID) {
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$personID."' ORDER BY isPrimary DESC LIMIT 1";
		$dta = $this->db->get_single_result($sql);
		$adrString = [];
		if(isset($dta['empty_result'])) {
			$adrString .= '';
		} else {
			$adrString = [ 
				'City' => $dta['City'],
				'State'=> $dta['State'],
			];
		}
		return $adrString;
	}
	
	function get_Address($address_id) {
		$sql = "SELECT * FROM Addresses WHERE Address_id='".$address_id."'";
		$dta = $this->db->get_single_result($sql);
		$adrString = '';
		if(isset($dta['empty_result'])) {
			$adrString .= '';
		} else {
			if($dta['Street_1'] != '') {
				$adrString .= $dta['Street_1'].'<br>';
			}
			$adrString .= $dta['City'].' '.$dta['State'].' '.$dta['Postal'].' '.$dta['Country'];
		}
		return $adrString;
	}
	
	function get_primaryGeoLocation($personID) {
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$personID."' ORDER BY isPrimary DESC LIMIT 1";
		$dta = $this->db->get_single_result($sql);
		if(isset($dta['empty_result'])) {
			$return['lat'] = '34.0617109';
			$return['lng'] = '-118.4017053';
		} else {
			if($dta['GeoLocationStatus'] == 200) {
				$return['lat'] = $dta['Lattitude'];
				$return['lng'] = $dta['Longitude'];
			} else {
				$return['lat'] = '34.0617109';
				$return['lng'] = '-118.4017053';
			}
		}
		return $return;		
	}
	
	function get_primaryAddressGeoLocation($address_id) {
		$sql = "SELECT * FROM Addresses WHERE Address_id='".$address_id."'";
		$dta = $this->db->get_single_result($sql);
		if(isset($dta['empty_result'])) {
			$return['lat'] = '34.0617109';
			$return['lng'] = '-118.4017053';
		} else {
			if($dta['GeoLocationStatus'] == 200) {
				$return['lat'] = $dta['Lattitude'];
				$return['lng'] = $dta['Longitude'];
			} else {
				$return['lat'] = '34.0617109';
				$return['lng'] = '-118.4017053';
			}
		}
		return $return;		
	}
	
	function render_ContractsTable($person_id) {
		$c_sql = "SELECT * FROM PersonsContract WHERE Person_id='".$person_id."' ORDER BY Contract_dateEntered DESC";	
		//echo $c_sql."<br>";
		$c_snd = $this->db->get_multi_result($c_sql);
		ob_start();
		?>
        <table class="table m-table m-table--head-no-border">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Rep</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
        	<?php		
			if(!isset($c_snd['empty_result'])):
				foreach($c_snd as $c_dta):
					switch($c_dta['Contract_status']) {
						case 1:
						$statusBlock = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
						break;
						
						case 2:
						$statusBlock = '<span class="m-badge m-badge--warning m-badge--wide">SIGNED</span>';
						break;
						
						case 3:
						$statusBlock = '<span class="m-badge m-badge--success m-badge--wide">PROCESSED</span>';
						break;
					}
				?>
                <tr>
                	<td><?php echo date("m/d/y h:ia", $c_dta['Contract_dateEntered'])?></td>
                    <td><?php echo $this->get_userName($c_dta['Contract_rep'])?></td>
                    <td><?php echo number_format($c_dta['Contract_RetainerFee'], 0)?></td>
                    <td><?php echo $statusBlock?></td>
                    <td><a href="/contractgen/<?php echo $person_id?>/<?php echo $c_dta['Contract_id']?>" class="btn btn-secondary btn-sm">View</a></td>
				</tr>
                <?php
				endforeach;                    			
			else:
				?>
                <tr>
                	<td colspan="4">&nbsp;</td>
                </tr>
                <?php
			endif;
			?>
		</tbody>
		</table>
        <?php
		return ob_get_clean();
	}
	
	function get_pendingPayments($person_id) {
		$sql = "SELECT count(*) as count FROM PersonsPaymentInfo WHERE Person_id='".$person_id."' AND PaymentInfo_Status='1'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];		
	}
	
	function render_ccFormTable($person_id) {
		include_once("class.encryption.php");
		$ENC = new encryption($DB);
		
		$c_sql = "SELECT * FROM PersonsPaymentInfo WHERE Person_id='".$person_id."' ORDER BY PaymentInfo_dateCreated DESC";	
		//echo $c_sql."<br>";
		$c_snd = $this->db->get_multi_result($c_sql);
		ob_start();
		?>
        <table class="table m-table m-table--head-no-border">
            <thead>
                <tr>
                    <th>Executed</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
        	<?php		
			if(!isset($c_snd['empty_result'])):
				foreach($c_snd as $c_dta):
					switch($c_dta['PaymentInfo_Status']) {
						case 1:
						$statusCode = '<span class="m-badge m-badge--info m-badge--wide"  data-toggle="m-popover" data-placement="top" data-content="A pending payment is one that has been created but has not yet been submitted by the client">PENDING</span>';
						break;
						
						case 2:
						$statusCode = '<span class="m-badge m-badge--warning m-badge--wide" data-toggle="m-popover" data-placement="top" data-content="A submitterd payment is a payment which has been submitted by the client and is awaiting processing.">SUBMITTED</span>';
						break;
						
						case 3:
						$statusCode = '<span class="m-badge m-badge--brand m-badge--wide" data-toggle="m-popover" data-placement="top" data-content="A proccessed payment means that is has been proccess by accounting and is currently awaiting recieving.">PROCESSED</span>';
						break;
						
						case 4:
						$statusCode = '<span class="m-badge m-badge--success m-badge--wide" data-toggle="m-popover" data-placement="top" data-content="a paid payment is a payment in which the monies have been recieved and deposited.">PAID</span>';
						break;
						
						case 5:
						$statusCode = '<span class="m-badge m-badge--danger m-badge--wide" data-toggle="m-popover" data-placement="top" data-content="a payment was marked as Insuficent Funds">NSF</span>';
						break;
						
						case 6:
						$statusCode = '<span class="m-badge m-badge--secondary m-badge--wide" data-toggle="m-popover" data-placement="top" data-content="this payment was voided">VOID</span>';
						break;
						
					}
					
					switch($c_dta['PaymentInfo_paymentType']) {
						case 1:
						$ptype = "CC";
						break;
						
						case 2:
						$ptype = "E-CHECK";
						break;
						
						case 3:
						$ptype = "WIRE";
						break;						
					}
				?>
                <tr>
                	<td><?php echo date("m/d/y", $c_dta['PaymentInfo_Execute'])?></td>
                   	 <td><?php echo $ptype?> <?php echo (($c_dta['PaymentInfo_isRefund'] == 1)? '<span class="m--font-danger"><strong>[REFUND]</strong></span>':'')?></td>
                    <td><?php echo $ENC->decrypt($c_dta['PaymentInfo_Amount'])?></td>
                    <td><?php echo $statusCode?></td>
                    <?php if($c_dta['PaymentInfo_Status'] == 1): ?>                                        
                    <td><a href="javascript:openPayment(<?php echo $c_dta['PaymentInfo_ID']?>);" class="btn btn-secondary btn-sm">View/Edit</a></td>
                    <?php elseif($c_dta['PaymentInfo_Status'] == 2): ?>                                        
                    <td><a href="javascript:reviewPayment(<?php echo $c_dta['PaymentInfo_ID']?>);" class="btn btn-secondary btn-sm">Review</a></td>
					<?php else: ?>
                    <td>
						<a href="javascript:reviewPayment(<?php echo $c_dta['PaymentInfo_ID']?>);" class="btn btn-secondary btn-sm">View</a>
						<!--<?php echo $c_dta['PaymentInfo_transID']?>-->
                    </td>
                    <?php endif; ?>
				</tr>
                <?php
				endforeach;                    			
			else:
				?>
                <tr>
                	<td colspan="4">&nbsp;</td>
                </tr>
                <?php
			endif;
			?>
		</tbody>
		</table>
        <?php
		return ob_get_clean();
		
		
	}
	
	/*! \fn obj get_image_directory($PersonID)
		\brief get the image subdirectory path for this particular user ID
		\param	$PersonID the ID of the record looking for the edefault image
		\return string
	*/
	function get_image_directory($PersonID) {
		switch(true) {
			case ($PersonID <= 20000):
				return '1-20000';
			break;
			case ($PersonID <= 40000):
				return '20001-40000';
			break;
			case ($PersonID <= 60000):
				return '40001-60000';
			break;
			case ($PersonID <= 80000):
				return '60001-80000';
			break;
			case ($PersonID <= 100000):
				return '80001-100000';
			break;
			case ($PersonID <= 120000):
				return '100001-120000';
			break;
			case ($PersonID <= 140000):
				return '120001-140000';
			break;
			case ($PersonID <= 160000):
				return '140001-160000';
			break;
			case ($PersonID <= 180000):
				return '160001-180000';
			break;
			case ($PersonID <= 200000):
				return '180001-200000';
			break;
			case ($PersonID <= 220000):
				return '200001-220000';
			break;
			case ($PersonID <= 240000):
				return '220001-240000';
			break;
			case ($PersonID <= 260000):
				return '240001-260000';
			break;
			case ($PersonID <= 280000):
				return '260001-280000';
			break;
			case ($PersonID <= 300000):
				return '280001-300000';
			break;
			default:
				return '';
		}
	}
	
	function generatePassword() {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789!@#$%-+";
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		
		while ($i <= 8) {
			   $num = rand() % 33;
		   $tmp = substr($chars, $num, 1);
		   $pass = $pass . $tmp;
		   $i++;
		}
		
		return $pass;
	}
	
	function generateThumbnail($imagePath) {
		$type = pathinfo($imagePath, PATHINFO_EXTENSION);
		$data = file_get_contents($imagePath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);		
		//echo "GBASEPATH".$imagePath."<br>";
		//echo "BASE64ENCODE:".$base64."<br>";
		return $base64;
	}
	
	function get_currentFreezeLength($person_id) {
		$sql = "SELECT * FROM PersonsFrozenHistory WHERE FrozenEnd='0' AND Person_id='".$person_id."' ORDER BY FrozenStart DESC LIMIT 1";
		$snd = $this->db->get_single_result($sql);
		
		$datetime1 = new DateTime(date("Y-m-d", $snd['FrozenStart']));
		$datetime2 = new DateTime(date("Y-m-d"));
		$interval = $datetime1->diff($datetime2);
		return $interval->format('%a');		
	}
	
	function get_freezeLength($start, $end) {
		$datetime1 = new DateTime(date("Y-m-d", $start));
		$datetime2 = new DateTime(date("Y-m-d", $end));
		$interval = $datetime1->diff($datetime2);
		return $interval->format('%a');		
	}
	
	function render_FreezeHisotry($person_id) {
		$sql = "SELECT * FROM PersonsFrozenHistory WHERE Person_id='".$person_id."' ORDER BY FrozenStart DESC";
		$snd = $this->db->get_multi_result($sql);
		
		ob_start();
		if(isset($snd['empty_result'])) {
			?>
            <div class="m-list-timeline__item">
                <span class="m-list-timeline__badge"></span>
                <span class="m-list-timeline__text">
                    <em>No History Found</em>
                </span>
                <span class="m-list-timeline__time">&nbsp;</span>
            </div>            
			<?php
		} else {
			foreach($snd as $dta):
			if($dta['FrozenEnd'] != 0):
            	$length = $this->get_freezeLength($dta['FrozenStart'], $dta['FrozenEnd']);
            else:
                $length = $this->get_currentFreezeLength($person_id);
            endif;
			$timeArray[] = $length;
			?>
			<div class="m-list-timeline__item">
                <span class="m-list-timeline__badge"></span>
                <span class="m-list-timeline__text">
                    <?php echo date("m/d/y", $dta['FrozenStart'])?>&nbsp;-&nbsp;
					<?php if($dta['FrozenEnd'] != 0): ?>
					<?php echo date("m/d/y", $dta['FrozenEnd'])?>
                    <?php else: ?>
					<span class="m--font-warning"><?php echo date("m/d/y")?></span>
                    <?php endif; ?>
                </span>
                <span class="m-list-timeline__time">
                	<?php echo $length?> days	
                </span>
            </div>
			<?php
			endforeach;
		}
		$freezeList = ob_get_clean();
		
		?>
        <h6>
        	<div class="pull-right"><?php echo @array_sum($timeArray);?> Days Total</div>
            Freeze History
		</h6>
		<div class="m-list-timeline">
		<div class="m-list-timeline__items">        
		<?php echo $freezeList?>
		</div>
		</div>
		<?php        	
	}
	
	function checkForDuplicateEmail($email, $pid) {
		$sql = "SELECT * FROM Persons WHERE Email='".$email."' AND Person_id != '".$pid."'";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] == 1) {
			// UNIQUE RECORD //
		} else {
			ob_start();
			?><ul><?php
			foreach($snd as $dta):
			?><li><a href="/profile/<?php echo $dta['Person_id']?>" class="m-link" target="_blank" style="color:#FFF;"><?php echo $dta['FirstName']?> <?php echo $dta['LastName']?> - <?php echo $dta['Email']?></a></li><?php
			endforeach;
			?></ul><?php
			$dupRecords = ob_get_clean();
			?>
            <div class="m-alert m-alert--icon alert alert-danger" role="alert">
                <div class="m-alert__icon">
                    <i class="flaticon-danger"></i>
                </div>
                <div class="m-alert__text">
                    <strong>DANGER</strong>
                    We have detected other records with identical email address. This record is either a duplicate or the record has a duplicate.
                    <?php echo $dupRecords?>
                </div>
                <div class="m-alert__close">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                </div>               
            </div>
            <?php	
		}
		
		$this->checkForDuplicatePhone($pid);		
	}
	
	function checkForDuplicatePhone($pid) {
		$sql = "SELECT * FROM Phones WHERE Person_id='".$pid."' AND isActive='1'";
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] == 1) {
			// NO PHONE NUMGER FOUND //
		} else {
			foreach($snd as $dta):
			if($dta['Phone_raw'] != ''):
				$ck_sql = "SELECT * FROM Phones INNER JOIN Persons ON Persons.Person_id=Phones.Person_id WHERE Phone_raw='".$dta['Phone_raw']."' AND Phones.Person_id != '".$pid."' AND Phones.isActive='1'";
				//echo $ck_sql;
				$ck_snd = $this->db->get_multi_result($ck_sql);
				if($ck_snd['empty_result'] == 1) {
					// UNIQUE PHONE NUMBER //
				} else {
					ob_start();
					?><ul><?php
					foreach($ck_snd as $ck_dta):
						?><li><a href="/profile/<?php echo $ck_dta['Person_id']?>" class="m-link" target="_blank" style="color:#FFF;"><?php echo $this->get_personName($ck_dta['Person_id'])?></a></li><?php
					endforeach;
					?></ul><?php
					$dupRecords = ob_get_clean();
					?>
					<div class="m-alert m-alert--icon alert alert-warning" role="alert">
						<div class="m-alert__icon">
							<i class="flaticon-danger"></i>
						</div>
						<div class="m-alert__text">
							<strong>WARNNING for Phone Number: <?php echo $dta['Phone_number']?></strong><br />
							We have detected other records with identical phone number.
							<?php echo $dupRecords?>
						</div>
						<div class="m-alert__close">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
						</div>               
					</div>              
					<?php
				}
			endif;
			endforeach;
		}		
	}
	
	/**
	 * Clean HTML
	 *
	 * Cleans a string of HTML, removing all potentially harmful tags so it is safe to display within the system
	 *
	 * @param	string	$html
	 * @return	string
	 */
	function cleanHtml($html) {
		$html = preg_replace('@(<\!--\[if.+?\[endif\]-->)@is', '', $html);
		$html = preg_replace('@</?html(.*?)>@is', '', $html);
		$html = preg_replace('@</?head(.*?)>@is', '', $html);
		$html = preg_replace('@</?body(.*?)>@is', '', $html);
		$html = preg_replace('@</?meta(.*?)>@is', '', $html);
		$html = preg_replace('@</?base(.*?)>@is', '', $html);
		$html = preg_replace('@<head(.*?)>(.*?)</head>@is', '', $html);
		$html = preg_replace('@<title(.*?)>(.*?)</title>@is', '', $html);
		$html = preg_replace('@<script(.*?)>(.*?)</script>@is', '', $html);
		$html = preg_replace('@<style(.*?)>(.*?)</style>@is', '', $html);
		$html = preg_replace('@<link(.*?)>@is', '', $html);
		return $html;
	}
	
	/**
	 * Get human readable time difference between 2 dates
	 *
	 * Return difference between 2 dates in year, month, hour, minute or second
	 * The $precision caps the number of time units used: for instance if
	 * $time1 - $time2 = 3 days, 4 hours, 12 minutes, 5 seconds
	 * - with precision = 1 : 3 days
	 * - with precision = 2 : 3 days, 4 hours
	 * - with precision = 3 : 3 days, 4 hours, 12 minutes
	 * 
	 * From: http://www.if-not-true-then-false.com/2010/php-calculate-real-differences-between-two-dates-or-timestamps/
	 *
	 * @param mixed $time1 a time (string or timestamp)
	 * @param mixed $time2 a time (string or timestamp)
	 * @param integer $precision Optional precision 
	 * @return string time difference
	 */
	function get_date_diff( $time1, $time2, $precision = 2 ) {
		// If not numeric then convert timestamps
		if( !is_int( $time1 ) ) {
			$time1 = strtotime( $time1 );
		}
		if( !is_int( $time2 ) ) {
			$time2 = strtotime( $time2 );
		}
		// If time1 > time2 then swap the 2 values
		if( $time1 > $time2 ) {
			list( $time1, $time2 ) = array( $time2, $time1 );
		}
		// Set up intervals and diffs arrays
		$intervals = array( 'year', 'month', 'day', 'hour', 'minute', 'second' );
		$diffs = array();
		foreach( $intervals as $interval ) {
			// Create temp time from time1 and interval
			$ttime = strtotime( '+1 ' . $interval, $time1 );
			// Set initial values
			$add = 1;
			$looped = 0;
			// Loop until temp time is smaller than time2
			while ( $time2 >= $ttime ) {
				// Create new temp time from time1 and interval
				$add++;
				$ttime = strtotime( "+" . $add . " " . $interval, $time1 );
				$looped++;
			}
			$time1 = strtotime( "+" . $looped . " " . $interval, $time1 );
			$diffs[ $interval ] = $looped;
		}
		$count = 0;
		$times = array();
		foreach( $diffs as $interval => $value ) {
			// Break if we have needed precission
			if( $count >= $precision ) {
				break;
			}
			// Add value and interval if value is bigger than 0
			if( $value > 0 ) {
				if( $value != 1 ){
					$interval .= "s";
				}
				// Add value and interval to times array
				$times[] = $value . " " . $interval;
				$count++;
			}
		}
		// Return string with times
		return implode( ", ", $times );
	}
}
?>