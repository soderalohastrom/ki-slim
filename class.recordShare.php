<?php

class recordShare extends Record {
	function check_recordAccess($person_id, $user_id) {
		$sql = "SELECT * FROM UsersPermissions WHERE user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			$return = array();
		} else {
			foreach($snd as $dta):
				$return[] = $dta['Permissions_id'];
			endforeach;
		}
		$MASTER_CHECK = false;
		
		// CHECK IF PARENT PERMISSION IS SET //
		$pType = $this->get_personType($person_id);
		//echo "TYPE:".$pType;
		if($pType == 'Lead') {
			$masterPermID = 1;
		} elseif($pType == 'Participating') {
			$masterPermID = 4;
		} elseif(($pType == 'Resource') || ($pType == 'Inactive Resource')) {
			$masterPermID = 3;
		} elseif($pType == 'Free Member') {
			$masterPermID = 5;
		} else {
			$masterPermID = 2;
		}
		
		if(in_array($masterPermID, $return)) {
			$MASTER_CHECK = true;
		}
		
		// CHECK IF SHARED //
		if(!$MASTER_CHECK) {
			$sql = "SELECT * FROM PersonRecordShares WHERE Person_id='".$person_id."' AND user_id='".$user_id."'";
			$snd = $this->db->get_multi_result($sql);
			if(isset($snd['empty_result'])) {
				$MASTER_CHECK = false;				
			} else {
				$MASTER_CHECK = true;
			}
		}
		
		// CHECK IF OPEN RECORD //
		$op_sql = "SELECT OpenRecord FROM Persons WHERE Person_id='".$person_id."'";
		$op_snd = $this->db->get_single_result($op_sql);
		if($op_snd['OpenRecord'] == 1):
			$MASTER_CHECK = true;
		endif;
		
		if(!$MASTER_CHECK) {
			$sql = "SELECT count(*) as count FROM Persons WHERE Person_id='".$person_id."' AND (Assigned_userID='".$user_id."' OR Matchmaker_id='".$user_id."' OR  Matchmaker2_id='".$user_id."')";
			//echo $sql;
			$snd = $this->db->get_single_result($sql);
			if($snd['count'] > 0) {
				$MASTER_CHECK = true;
			}			
		}
		return $MASTER_CHECK;
	}
	
	function render_leftShareLink($person_id) {
		ob_start();
		$sql = "SELECT * FROM PersonRecordShares WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			?>
			<span class="m-nav__link-text">
                Shared w/: 
                <strong id="display_sharedWith">none</strong>
            </span>			
			<?php
		} else {
			?>
            <span class="m-nav__link-text">
                    Shared w/: 
                    <strong id="display_sharedWith">
                        <ul>
                        <?php
						foreach($snd as $dta):
						?><li><?php echo $this->get_userName($dta['user_id'])?></li><?php
						endforeach;	
						?>
                       	</ul>
                </strong>
            </span>
            <?php             
		}
		$shareLinks = ob_get_clean();
		
		ob_start();
		?>
        <li class="m-nav__item">        	
            <a href="#" data-toggle="modal" data-target="#shareRecordModal" class="m-nav__link">            	
                <i class="m-nav__link-icon flaticon-map"></i>
                <?php echo $shareLinks?>                
            </a>
        </li>
        <?php
		return ob_get_clean();
	}
	
	function render_leftShareModal($person_id) {
		ob_start();
		?>
        <div class="modal fade" id="shareRecordModal" role="dialog" aria-labelledby="shareRecordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareRecordModalLabel">Share Record With</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
						<div>
							<?php echo $this->render_userSelect('', 'personShareSelect')?>
                            <span class="m-form__help">
                                Select the user you want to share this record with
                            </span>
                        </div>
                        <hr />
                        <h5>Record is Shared with:</h5>
                        <div id="shareList">
                        	<?php echo $this->render_sharedPersonBadges($person_id)?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-secondary"><i class="la la-archive"></i></button>	-->		                               
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <!-- <button type="button" class="btn btn-primary" onclick="saveMemShareType()">Save</button> -->
                    </div>
                </div>
            </div>
        </div>
        <script>
		$(document).ready(function(e) {
            $('#personShareSelect').select2({ theme: "classic" });
			$('#personShareSelect').on('select2:select', function (e) {
				var data = e.params.data;
			    console.log(data);
				$.post('/ajax/recordShare.php?action=addSharePerson', {
					id:		data.id,
					text:	data.text,
					pid:	<?php echo $person_id?>,
					type:	'S'
				}, function(data) {
					console.log(data);
					if(data.success) {
						$('#shareList').append(data.html);
					} else {
						alert(data.error);
					}
					$('#personShareSelect').select2('data', null).trigger('change');
					//$('#personShareSelect').val('0');
				}, "json");
			});
			
			$('#shareRecordModal').on('hidden.bs.modal', function (e) {
				document.location.reload(true);	
			});
        });
		function removeShare(uid, pid) {
			var choice = confirm('Are yo usure you want to remove this share?');
			if(choice) {
				$.post('/ajax/recordShare.php?action=removeShare', {
					pid: 	pid,
					uid:	uid
				}, function(data) {
					var divID = uid+"_"+pid+"_div";
					$('#'+divID).remove();
					//document.location.reload(true);	
				});
			}
		}
		</script>
        <?php
		return ob_get_clean();
	}
	
	function render_sharedPersonBadges($person_id) {
		ob_start();
		$sql = "SELECT * FROM PersonRecordShares WHERE Person_id='".$person_id."'";
		$snd = $this->db->get_multi_result($sql);
		if(!isset($snd['empty_result'])):
			foreach($snd as $dta):
			$divID = $dta['user_id']."_".$person_id."_div";
			?>
            <div style="float:left; margin-right:5px; margin-bottom:5px;" id="<?php echo $divID?>">
            <span class="m-badge m-badge--success m-badge--wide">
                <?php echo $this->get_userName($dta['user_id'])?>&nbsp;&nbsp;&nbsp;
                <a href="javascript:removeShare('<?php echo $dta['user_id']?>','<?php echo $person_id?>')" style="color:#FFF;"><i class="la la-times" style="font-size:.9em;"></i></a>
            </span>
            </div>
            <?php
			endforeach;	
		endif;           
		$shareLinks = ob_get_clean();
		return $shareLinks;
	}
	
		
	
	
	
	
}
?>