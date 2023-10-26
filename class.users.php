<?php
/*! \class Users class.users.php "class.notes.php"
 *  \brief used to work with users.
 */
class Users {
	/*! \fn obj __constructor($DB)
		\brief users class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db 		= $DB;
	}
	
	function select_getUserClasses($class_id='') {
		$sql = "SELECT * FROM UserClasses ORDER BY userClass_id ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		?><option value="">All</option><?php 
		if(!isset($snd['empty_result'])):
			foreach($snd as $dta):
				?><option value="<?php echo $dta['userClass_id']?>" <?php echo (($dta['userClass_id'] == $class_id)? 'selected':'')?>><?php echo $dta['userClass_name']?></option>
				<?php
			endforeach;
		endif;
		return ob_get_clean();
	}
	
	function get_userName($user_id) {
		$sql = "SELECT * FROM Users WHERE user_id='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return substr($snd['firstName'], 0, 1).' '.$snd['lastName'];		
	}
	
	function get_userFullName($user_id) {
		$sql = "SELECT * FROM Users WHERE user_id='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['firstName'].' '.$snd['lastName'];		
	}
	
	function get_userEmail($user_id) {
		$sql = "SELECT * FROM Users WHERE user_id='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['email'];	
	}
	
	function get_userTimezone($user_id) {
		$sql = "SELECT userTimezone FROM Users WHERE user_id='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['userTimezone'];	
	}
	
	function get_userImage($user_id) {
		$sql = "SELECT * FROM Users WHERE user_id='".$user_id."'";
		$snd = $this->db->get_single_result($sql);
		if($snd['userAvatar'] == '') {
			if($snd['userGender'] == 'M') {
				$img_path = '/assets/app/media/img/users/sample-m.jpg';
			} else {
				$img_path = '/assets/app/media/img/users/sample-f.jpg';
			}
		} else {
			$img_path = $snd['userAvatar'];
		}
		return $img_path;
	}
	
	function get_userClass($user_id) {
		$sql = "select userClass_name from Users a join UserClasses b on a.userClass_id=b.userClass_id and a.user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		return $snd['userClass_name'];
	}
	function get_userPermissions($user_id) {
		$sql = "SELECT * FROM UsersPermissions WHERE user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			return array();
		} else {
			foreach($snd as $dta):
				$return[] = $dta['Permissions_id'];
			endforeach;
			return $return;
		}		
	}
	
	function render_userPermissionForm($node, $currentVals=array()) {
		$sql = "SELECT * FROM Permissions WHERE Permissions_node='".$this->db->mysqli->escape_string($node)."' ORDER BY Permissions_order ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		$USR = new Users($this->db);
		$userClass = $USR->get_userClass($_SESSION['system_user_id']);
		$is_superuser = in_array($userClass, array( 'Admin', 'Admin Programmer'));
		
		if(isset($snd['empty_result'])) {
			?>&nbsp;
			<?php
		} else {
			?><div class="m-checkbox-list">
				<?php
			foreach($snd as $dta):
				if (!$is_superuser):
					if($dta['Permissions_superuser'] == 1):
					?>         
					<label class="m-checkbox m-checkbox--state-primary" style="<?php echo (($dta['Permissions_subnodeID'] != '0')? 'margin-left:20px;':'')?>">					
						<input type="checkbox" name="PermissionsBlank[]" class="permissionChoice" value="<?php echo $dta['Permissions_id']?>" <?php echo ((in_array($dta['Permissions_id'], $currentVals))? 'checked':'')?> disabled/>
						<?php if(in_array($dta['Permissions_id'], $currentVals)): ?>
						<input type="hidden" name="Permissions[]" class="permissionChoice" value="<?php echo $dta['Permissions_id']?>"/>
						<?php endif; ?>
						<?php echo $dta['Permissions_name']?> <?php echo (($dta['Permissions_desc'] != '')? '<i class="fa fa-info-circle" data-container="body" data-toggle="m-popover" data-placement="top" data-content="SUPER USER PERMISSION - '.$dta['Permissions_desc'].'">':'')?></i>
						<span></span>
					</label>
					<?php 
					else:
					?>         
					<label class="m-checkbox m-checkbox--state-primary" style="<?php echo (($dta['Permissions_subnodeID'] != '0')? 'margin-left:20px;':'')?>">					
						<input type="checkbox" name="Permissions[]" class="permissionChoice" value="<?php echo $dta['Permissions_id']?>" <?php echo ((in_array($dta['Permissions_id'], $currentVals))? 'checked':'')?>/>
						<?php echo $dta['Permissions_name']?> <?php echo (($dta['Permissions_desc'] != '')? '<i class="fa fa-info-circle" data-container="body" data-toggle="m-popover" data-placement="top" data-content="'.$dta['Permissions_desc'].'">':'')?></i>
						<span></span>
					</label>
					<?php 
					endif;
				else:
					?>         
					<label class="m-checkbox m-checkbox--state-primary" style="<?php echo (($dta['Permissions_subnodeID'] != '0')? 'margin-left:20px;':'')?>">					
						<input type="checkbox" name="Permissions[]" class="permissionChoice" value="<?php echo $dta['Permissions_id']?>" <?php echo ((in_array($dta['Permissions_id'], $currentVals))? 'checked':'')?>/>
						<?php echo $dta['Permissions_name']?> <?php echo (($dta['Permissions_desc'] != '')? '<i class="fa fa-info-circle" data-container="body" data-toggle="m-popover" data-placement="top" data-content="'.$dta['Permissions_desc'].'">':'')?></i>
						<span></span>
					</label>
					<?php
				endif;
				
			endforeach;
			?></div><?php
		}
		return ob_get_clean();		
	}
	
	function time_elapsed_string($epoch, $full = false) {
		$now = new DateTime;
		$dt_string = date("c", $epoch);
		$ago = new DateTime($dt_string);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => 'yr',
			'm' => 'mon',
			'w' => 'wk',
			'd' => 'd',
			'h' => 'hr',
			'i' => 'min',
			's' => 'sec',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . '' : 'just now';
	}
	
	function render_userLogs($user_id) {
		$sql = "SELECT * FROM PersonsLogs WHERE PersonsLogs_updatedBy='".$user_id."' ORDER BY PersonsLogs_updateTime DESC Limit 20";
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			?>
            <div class="m-stack m-stack--ver m-stack--general" style="min-height: 180px;">
                <div class="m-stack__item m-stack__item--center m-stack__item--middle">
                    <span class="">No Logs Found</span>
                </div>
            </div>             
            <?php
		} else {
			?>
            <div class="m-scrollable" data-scrollable="true" data-max-height="250" data-mobile-max-height="200">
				<div class="m-list-timeline m-list-timeline--skin-light">
                	<div class="m-list-timeline__items">	
            <?php
			foreach($snd as $dta):
			?>
            <div class="m-list-timeline__item">
                <span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>
                <span class="m-list-timeline__text truncate">
                    <?php echo strip_tags($dta['PersonsLogs_action'])?>
                </span>
                <span class="m-list-timeline__time">
                	<?php echo $this->time_elapsed_string($dta['PersonsLogs_updateTime'])?>    
                </span>
            </div>
            <?php
			endforeach;
			?>
            		</div>
				</div>
			</div>
            <?php                                    				
		}
		
	}

	function write_userDevice($user_id, $device) {
		$sql = "INSERT INTO UsersDevices ( UsersDevices_userId, UsersDevices_device, UsersDevices_approveDate )
				VALUES ( '".$user_id."', '".$this->db->mysqli->escape_string($device)."', '".time()."' )";
		$query = $this->db->mysqli->query($sql);
	}
	
	function write_userDeviceCode($user_id, $code, $expires, $device) {
		$del_sql = "DELETE FROM UsersDeviceCodes WHERE DeviceCode_userId = '".$user_id."'";
		$del_query = $this->db->mysqli->query($del_sql);
		
		$ins_sql = "INSERT INTO UsersDeviceCodes ( DeviceCode_userId, DeviceCode_code, DeviceCode_expires, DeviceCode_device )
				VALUES ( '".$user_id."', '".$this->db->mysqli->escape_string($code)."', '".$expires."', '".$this->db->mysqli->escape_string($device)."' )";
		$ins_query = $this->db->mysqli->query($ins_sql);
	}
	
	function check_userDeviceCode($user_id, $code, $check_if_expired=false) {
		$chk_sql = "SELECT COUNT(*) AS Exist FROM UsersDeviceCodes WHERE DeviceCode_userId = '".$user_id."' AND DeviceCode_code = '".$this->db->mysqli->escape_string($code)."'";
		if($check_if_expired) {
			$chk_sql .= " AND DeviceCode_expires > '".time()."'";
		}
		$chk_result = $this->db->get_single_result($chk_sql);
		return $chk_result['Exist'];
	}
	
	
}

?>
