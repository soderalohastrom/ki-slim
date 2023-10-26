<?php
/*! \class Matching class.matching.php "class.matching.php"
 *  \brief used to work with record notes.
 */
class Matching {
	/*! \fn obj __constructor($DB)
		\brief maatching class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD) {
		$this->db 		= $DB;
		$this->record	= $RECORD;
	}
	
	
	public function render_dateStatus_checkboxes($setStatusArray=array()) {
		$sql = "SELECT * FROM DropDown_DateStatus ORDER BY StatusOrder ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		?><div class="m-checkbox-list"><?php
		foreach($snd as $dta):
		?>
		<label class="m-checkbox m-checkbox--state-<?php echo $dta['kimsClass']?>">
            <input type="checkbox" class="introStatus" value="<?php echo $dta['Date_status']?>" <?php echo (in_array($dta['Date_status'], $setStatusArray)? 'checked':'')?>>
            <?php echo $dta['Date_statusText']?>
            <span></span>
        </label>
        <?php													
		endforeach;
		?></div><?php
		return ob_get_clean();			
	}
	
	public function render_dateStatus_radios($setStatusArray=array()) {
		$sql = "SELECT * FROM DropDown_DateStatus ORDER BY StatusOrder ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		?><div class="m-radio-list"><?php
		foreach($snd as $dta):
		?>
        <label class="m-radio m-radio--solid m-radio--state-<?php echo $dta['kimsClass']?>">
	        <input type="radio" name="introStatus" value="<?php echo $dta['Date_status']?>" <?php echo (in_array($dta['Date_status'], $setStatusArray)? 'checked':'')?>>
    	    <?php echo $dta['Date_statusText']?>
        	<span></span>
        </label>
        <?php													
		endforeach;
		?></div><?php
		return ob_get_clean();			
	}
	
	public function render_dateDispo_radios($setDispo=array()) {
		$sql = "SELECT * FROM DropDown_DateDisposition WHERE Disposition_active='1' ORDER BY Disposition_order ASC";	
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		?><div class="m-radio-list"><?php
		foreach($snd as $dta):
		?>
		<label class="m-radio m-radio--solid m-radio--state-metal">
	        <input type="radio" name="Disposition_id" value="<?php echo $dta['Disposition_id']?>" <?php echo (in_array($dta['Disposition_id'], $setDispo)? 'checked':'')?>>
    	    <?php echo $dta['Disposition_name']?>
        	<span></span>
        </label>
        <?php
		endforeach;
		?></div><?php
		
	}
	
	public function alreadyMatched($p1, $p2) {
		$sql = "SELECT pd.PersonsDates_status FROM PersonsDates pd WHERE (pd.PersonsDates_participant_1='".$p1."' AND pd.PersonsDates_participant_2='".$p2."') OR (pd.PersonsDates_participant_1='".$p2."' AND pd.PersonsDates_participant_2='".$p1."')";
		//echo $sql."\n";
		$snd = $this->db->get_single_result($sql);
		if(isset($snd['empty_result'])) {
			return -1;
		} else {
			return $snd['PersonsDates_status'];
		}
	}
	
	public function get_dateStatusText($statusID, $valueOnly=false, $badgeOnly=false) {
		$sql = "SELECT * FROM DropDown_DateStatus WHERE Date_status='".$statusID."'";
		$snd = $this->db->get_single_result($sql);
		if($valueOnly) {
			return $snd['Date_statusText'];
		} elseif ($badgeOnly) {
			ob_start();
			?><span class="m-badge m-badge--<?php echo $snd['kimsClass']?> m-badge--wide" title="<?php echo $snd['Date_statusText']?>"></span><?php
			return ob_get_clean();
		} else {
			ob_start();
			?><span class="m-badge m-badge--<?php echo $snd['kimsClass']?> m-badge--wide"><?php echo $snd['Date_statusText']?></span><?php
			return ob_get_clean();
		}
	}
	
	public function get_dateDisposition($dispoID) {
		$sql = "SELECT * FROM DropDown_DateDisposition WHERE Disposition_id='".$dispoID."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['Disposition_name'];
	}
	
	public function get_dateLocationText($locationID) {
		if($locationID != 0):
			$sql = "SELECT * FROM PersonsDatesLocations WHERE LocationID='".$locationID."'";
			$snd = $this->db->get_single_result($sql);
			return $snd['Location_name'];
		else:
			return '&nbsp;';
		endif;
	}
	
	public function render_myDateStats($personID) {
		$sql = "SELECT * FROM DropDown_DateStatus ORDER BY StatusOrder ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		foreach($snd as $dta):
			?><span class="m-badge m-badge--<?php echo $dta['kimsClass']?>" title="<?php echo $dta['Date_statusText']?>">&nbsp;<?php echo $this->count_myDates($personID, $dta['Date_status'])?>&nbsp;</span>&nbsp;<?php
		endforeach;
		return ob_get_clean();		
	}
	
	public function get_myLastDate($personID) {
		$csql = "
		SELECT 
			PersonsDates_dateExecuted, PersonsDates_id
		FROM 
			PersonsDates pd 
		WHERE 
			(pd.PersonsDates_participant_1='".$personID."' OR pd.PersonsDates_participant_2='".$personID."') 
		    AND
			pd.PersonsDates_isComplete='1'
		ORDER BY
			PersonsDates_dateExecuted DESC
		LIMIT 1
		";
		$csnd = $this->db->get_single_result($csql);
		return $csnd;
	}
	
	public function count_myDates($personID, $dateStatus) {
		$csql = "
		SELECT 
			count(*) as count
		FROM 
			PersonsDates pd 
		WHERE 
			(pd.PersonsDates_participant_1='".$personID."' OR pd.PersonsDates_participant_2='".$personID."') 
		AND 
			pd.PersonsDates_status='".$dateStatus."'
		";
		$csnd = $this->db->get_single_result($csql);
		$TOTAL = $csnd['count'];
		return $TOTAL;			
	}
	
	public function count_myCompletedDates($personID) {
		$csql = "
		SELECT 
			count(*) as count
		FROM 
			PersonsDates pd 
		WHERE 
			(pd.PersonsDates_participant_1='".$personID."' OR pd.PersonsDates_participant_2='".$personID."') 
		AND 
			pd.PersonsDates_isComplete='1'
		";
		$csnd = $this->db->get_single_result($csql);
		$TOTAL = $csnd['count'];
		return $TOTAL;			
	}
	
	public function render_DateNotes($dateID, $personID=0) {
		$nsql = "SELECT* FROM PersonsDatesNotes WHERE PersonsDates_PersonsDates_id='".$dateID."'";
		if($personID != 0) {
			$nsql .= " AND PersonsDatesNotes_personID='".$personID."'";
		}
		$nsql .= " ORDER BY PersonsDatesNotes_dateCreated DESC";
		$nsnd = $this->db->get_multi_result($nsql);
		if(isset($nsnd['empty_result'])) {
			
		} else {
			?><div class="m-stack m-stack--hor m-stack--general m-stack--demo"><?php
			foreach($nsnd as $ndta):
				?>            
                <div class="m-stack__item">
                    <div class="m-stack__demo-item">
                        <h6><?php echo $ndta['PersonsDatesNotes_type']?> <?php echo (($ndta['PersonsDatesNotes_personID'] != 0)? 'for '.$this->record->get_personName($ndta['PersonsDatesNotes_personID']):'')?> <small><?php echo date("m/d/y h:ia", $ndta['PersonsDatesNotes_dateCreated'])?> | <?php echo $this->record->get_userName($ndta['PersonsDatesNotes_createdBy'])?></small></h6>
                        <div style="font-size:.85em;"><?php echo $ndta['PersonsDatesNotes_body']?></div>
                    </div>
                </div>
                <?php			
			endforeach;	
			?></div><?php
		}
	}
	
	public function get_introScore($dateID) {
		$sql = "SELECT * FROM PersonsDates WHERE PersonsDates_id='".$dateID."'";
		$snd = $this->db->get_single_result($sql);
		$score_1 = $snd['PersonsDates_participant_1_rank'];
		$score_2 = $snd['PersonsDates_participant_2_rank'];
		$preScore = ($score_1 + $score_2) / 2;
		if(($score_1 != 0) && ($score_2 != 0)) {
			return round($preScore, 1);
		} else {
			return '&nbsp;';
		}		
	}
	
	public function searching_render_personCard($personID, $thumbSize=100) {
		$P1_SQL = "
		SELECT 
		*
		FROM
			Persons
			LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
			LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
			LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
		WHERE 
			Persons.Person_id='".$personID."'
		";
		$P1_DTA = $this->db->get_single_result($P1_SQL);
		
		ob_start();
		?>
        <div class="row">
            <!-- BEGIN - RECORD IMAGE -->
            <div class="col-2">
                <div style="float:left; margin-right:5px; width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px; background-image:url('<?php echo $this->record->get_PrimaryImage($personID)?>'); background-size:cover;">
                    <img src="/assets/app/media/img/users/filler.png" alt="" style="width:<?php echo $thumbSize?>px; height:<?php echo $thumbSize?>px;">
                </div>
            </div>
            <!-- END - RECORD IMAGE -->
            
            
            <div class="col-10">                
                <div class="row">
                    <div class="col-6">           
                        <div class="row" style="margin-bottom:0px;">
                            <div class="col-5 text-right">Age:</div>
                            <div class="col-7"><strong><?php echo $this->record->get_personAge($P1_DTA['DateOfBirth'])?></strong></div>
                        </div>
                        <div class="row" style="margin-bottom:0px;">
                            <div class="col-5 text-right">Gender:</div>
                            <div class="col-7"><strong><?php echo $P1_DTA['Gender']?></strong></div>
                        </div>
                        <div class="row" style="margin-bottom:0px;">
                            <div class="col-5 text-right">Occupation:</div>
                            <div class="col-7"><strong><?php echo $P1_DTA['Occupation']?></strong></div>
                        </div>
                        <div class="row" style="margin-bottom:0px;">
                            <div class="col-5 text-right">From:</div>
                            <div class="col-7"><strong><?php echo $P1_DTA['City']?> <?php echo $P1_DTA['State']?> <?php echo $P1_DTA['Country']?></strong></div>
                        </div>
                        <div class="row" style="margin-bottom:0px;">
                            <div class="col-5 text-right">Seeking:</div>
                            <div class="col-7"><strong><?php echo $P1_DTA['prefQuestion_Gender']?> <?php echo str_replace("|" , ' to ', $P1_DTA['prefQuestion_age_floor'])?></strong></div>
                        </div>                        
                    </div>
                    <div class="col-6">                    	
                        <div class="row" style="margin-bottom:-5px;">
                            <div class="col-5 text-right"><small>Height/Weight:</small></div>
                            <div class="col-7"><small><?php echo $P1_DTA['prQuestion_621']?> | <?php echo $P1_DTA['prQuestion_622']?> <?php echo (($P1_DTA['prQuestion_664'] != '')? '| Rank:'.$P1_DTA['prQuestion_664']:'')?></small></div>
                        </div>
                        <div class="row" style="margin-bottom:-5px;">
                            <div class="col-5 text-right"><small>Have Children:</small></div>
                            <div class="col-7"><small><?php echo $P1_DTA['prQuestion_632']?></small></div>
                        </div>
                        <div class="row" style="margin-bottom:-5px;">
                            <div class="col-5 text-right"><small>Want Children:</small></div>
                            <div class="col-7"><small><?php echo $P1_DTA['prQuestion_634']?></small></div>
                        </div>
                        <div class="row" style="margin-bottom:-5px;">
                            <div class="col-5 text-right"><small>Will Travel:</small></div>
                            <div class="col-7"><small><?php echo $P1_DTA['prQuestion_653']?></small></div>
                        </div>
                        <div class="row" style="margin-bottom:5px;">
                            <div class="col-5 text-right"><small>Location Pref:</small></div>
                            <div class="col-7"><small><?php echo $P1_DTA['prQuestion_678']?></small></div>
                        </div> 

                    </div>
                </div>
                <div class="text-right">
                    <div class="btn-group m-btn-group m-btn-group--pill" role="group" aria-label="...">
                        <a href="/profile/<?php echo $personID?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-chevron-left"></i> Back to Profile
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="">
                            <i class="fa fa-search"></i> Quickview
                        </button>
                    </div>
				</div>                    
                                                
            </div>
        </div>
        <?php
		return ob_get_clean();		
	}
	
	function log_date_action($DateID, $Action, $UserID) {
		/*NOTE: THIS FIELD IS NAMED PERSON ID BUT IS REALLY THE DATE ID.*/
        $fields = "DateID, ActionDate, ActionTaken, UserID";
        $values = "'$DateID', '".time()."', '".addslashes($Action)."', '$UserID'";
        $ins_query = "INSERT INTO DateLogs ($fields) VALUES ($values)";
       // echo "$ins_query<br>";
        //$ins_send = mysql_query($ins_query, $db_link);
		$ins_send = $this->db->mysqli->query($ins_query);
	}
	
	
	function showDateLogs($DateID) {
		?><div class="m-list-timeline"><?php
		?><div class="m-list-timeline__items"><?php
		$sql = "SELECT * FROM DateLogs WHERE DateID='".$DateID."' ORDER BY ActionDate DESC";
		$snd = $this->db->get_multi_result($sql);
		if(!isset($snd['emtpty_result'])) {
			foreach($snd as $dta) {
				?>
                <div class="m-list-timeline__item">
                    <span class="m-list-timeline__badge"></span>
                    <span class="m-list-timeline__text">
                        <?php echo $dta['ActionTaken']?>&nbsp;
                        <small>by <?php echo (($dta['UserID'] != 0)? $this->record->get_userName($dta['UserID']):'Client')?></small>
                    </span>
                    <span class="m-list-timeline__time">
                    	<?php echo date("m/d/y", $dta['ActionDate'])?>
                    </span>
                </div>
                <?php				
			}
		}
		?></div><?php
		?></div><?php
	}
	
	function updateLastIntro($person_id, $intro_id=false, $intro_epoch=false) {
		if(!$intro_epoch):
			$pd_sql = "SELECT * FROM PersonsDates WHERE (PersonsDates_participant_1='".$person_id."' OR PersonsDates_participant_2='".$person_id."') AND PersonsDates_isComplete='1' ORDER BY PersonsDates_dateCompleted DESC LIMIT 1";
			//echo $pd_sql."\n";
			$pd_snd = $this->db->get_single_result($pd_sql);
			//print_r($pd_snd);
			$intro_id = $pd_snd['PersonsDates_id'];		
			$intro_epoch = $pd_snd['PersonsDates_dateCompleted'];
		endif;			
		$upd_sql = "UPDATE Persons SET LastIntroDate='".$intro_epoch."', LastIntroID='".$intro_id."' WHERE Person_id='".$person_id."'";
		//echo $upd_sql."<br>\n";
		$upd_snd = $this->db->mysqli->query($upd_sql);	
	}
	
	
	
	
	
	
	
}
?>