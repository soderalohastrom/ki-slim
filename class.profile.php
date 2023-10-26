<?php
class Profile{
	function __construct($DB, $iEdit) {
		$this->db 			= 	$DB;
		$this->edit			=	$iEdit;
		$this->exceptions	=	array(621, 622, 653, 657, 676, 677, 631, 1719, 1713, 637, 678, 632, 634, 1727, 1728, 1729, 664);	
	}
	
	function render_FullProfile($personID, $editable=true, $preview=false) {
		$exeptions = $this->exceptions;
		$p_sql = "SELECT * FROM PersonsProfile WHERE Person_id='".$personID."'";
		$p_snd = $this->db->get_single_result($p_sql);
		//print_r($p_snd);		
		if($preview) {
			$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id NOT IN (12, 13, 14, 15) ORDER BY QuestionsCategories_id ASC";
		} else {
			$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id NOT IN (12, 15) ORDER BY QuestionsCategories_id ASC";
		}
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$catID = $dta['QuestionsCategories_id'];
			if($preview) {
				$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' AND Questions_inPortal='1' ORDER BY Questions_order ASC";
			} else {
				$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' ORDER BY Questions_order ASC";
			}
			$q_found = $this->db->get_multi_result($q_sql, true);
			if($q_found):
				?>
                <h5 style="margin-top:10px;"><?php echo $dta['QuestionsCategories_name']?></h5>
                <?php
				$q_snd = $this->db->get_multi_result($q_sql);
				?><div class="row"><?php
				$colCount = 0;
				foreach($q_snd as $q_dta):
					if(!in_array($q_dta['Questions_id'], $exeptions)) {
						$fieldName = 'prQuestion_'.$q_dta['Questions_id'];
						$fieldValue = $p_snd[$fieldName];
						if(($q_dta['QuestionTypes_id'] == 2) || ($q_dta['QuestionTypes_id'] == 5)):
							$colWidth = 12;
						else:
							$colWidth = 6;
						endif;
						if ($editable) {
							?><div class="col-md-<?php echo $colWidth?>" style="margin-bottom:4px;"><?php echo $q_dta['Questions_text']?>: <?php echo (($colWidth == 12)? '<br>':'')?><strong><?php echo $this->edit->render_customField($q_dta['Questions_id'], $fieldValue, $fieldValue, $personID)?></strong></div><?php
						} else {
							?><div class="col-md-<?php echo $colWidth?>" style="margin-bottom:4px;"><?php echo $q_dta['Questions_text']?>: <?php echo (($colWidth == 12)? '<br>':'')?><strong><?php echo str_replace("|", ", ", $fieldValue)?></strong></div><?php
						}
						$colCount++;
						if($colCount == 2):
							?></div><div class="row"><?php
						endif;
					}
				endforeach;
				?></div><?php
			endif;				
		endforeach;		
	}
	
	function render_FullPrefs($personID, $editable=true, $preview=false) {
		$exeptions = $this->exceptions;
		$p_sql = "SELECT * FROM PersonsPrefs WHERE Person_id='".$personID."'";
		$p_snd = $this->db->get_single_result($p_sql);
		
		$catArray = array('Basic','Geographic','Profile');
		foreach($catArray as $cat):
			?><h6 style="margin-top:20px;"><?php echo $cat?> Prefs</h6><?php
			if($preview) {
				$sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_active='1' AND PrefQuestions_cat='".$cat."' AND PrefQuestions_inPortal='1' ORDER BY PrefQuestions_order ASC";
			} else {
				$sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_active='1' AND PrefQuestions_cat='".$cat."' ORDER BY PrefQuestions_order ASC";
			}
			//echo $sql;
			$fnd = $this->db->get_multi_result($sql, true);
			
			if($fnd != 0):
				$snd = $this->db->get_multi_result($sql);			
				?><div class="row align-items-start"><?php
				foreach($snd as $dta):
					$fieldName = $dta['PrefQuestion_mappedField'];
					$labelField = $dta['PrefQuestions_mappedMatchField'];
					switch($labelField) {
						case 'age_floor':
						$title = "Age Range Seeking";
						$value = str_replace("|", " to ", $p_snd[$fieldName]);
						$split = 6;
						if($preview):
							$showItem = true;
						else:
							$showItem = true;
						endif;
						break;
						
						case 'Gender':
						$title = "Gender Seeking";
						$value = str_replace("|", " ", $p_snd[$fieldName]);
						$split = 6;
						if($preview):
							$showItem = true;
						else:
							$showItem = true;
						endif;
						
						break;
						
						case 'Pref_Offices':
						$title = $dta['PrefQuestion_text'];
						$array = explode("|", $p_snd[$fieldName]);
						for($i=0; $i<count($array); $i++) {
							$sql2 = "SELECT * FROM Offices WHERE Offices_id='".$array[$i]."'";
							$snd2 = $this->db->get_single_result($sql2);
							$preVal[] = $snd2['office_Name'];
						}
						$value = implode(", ", $preVal);
						//echo "VALUE: ".$value;
						$split = 6;
						if($preview):
							$showItem = false;
						else:
							$showItem = true;
						endif;
						break;
						
						case 'Pref_MemberTypes':
						$title = $dta['PrefQuestion_text'];
						$array2 = explode("|", $p_snd[$fieldName]);
						for($i=0; $i<count($array2); $i++) {
							$sql2 = "SELECT * FROM PersonTypes WHERE PersonsTypes_id='".$array2[$i]."'";
							$snd2 = $this->db->get_single_result($sql2);
							$preVal[] = $snd2['PersonsTypes_text'];
						}
						$value = implode(", ", $preVal);
						//echo "VALUE: ".$value;
						$split = 12;
						if($preview):
							$showItem = false;
						else:
							$showItem = true;
						endif;
						break;	
						
						default:
						$title = $dta['PrefQuestion_text'];
						$value = str_replace("|", ", ", $p_snd[$fieldName]);
						$split = 6;
						if($preview):
							$showItem = true;
						else:
							$showItem = true;
						endif;
						break;
					}
					if($showItem) {
						if($value == ''):
							$value = '[NO PREFERENCE]';
						endif;
						if($editable) {
							?><div class="col-md-<?php echo $split?>" style="margin-bottom:4px;"><?php echo $title?>: <?php echo $this->edit->render_customPref($dta['PrefQuestion_id'], $dta['Questions_id'], $value, $p_snd[$fieldName], $personID)?></div><?php
						} else {
							?><div class="col-md-<?php echo $split?>" style="margin-bottom:4px;"><?php echo $title?>: <strong><?php echo $value?></strong></div><?php
						}
					}
					unset($preVal);
				endforeach;
				?></div><?php
			endif;
		endforeach;
		
		$prof_sql = "SELECT * FROM PersonsProfile WHERE Person_id='".$personID."'";
		$prof_snd = $this->db->get_single_result($prof_sql);
			
		$catID = 12;
		if($preview) {
			$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' AND Questions_inPortal='1' ORDER BY Questions_order ASC";
		} else {
			$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' ORDER BY Questions_order ASC";
		}
		$q_found = $this->db->get_multi_result($q_sql, true);
		if($q_found):
			?>
			<h6 style="margin-top:20px;">Looking For</h6>
			<?php
			$q_snd = $this->db->get_multi_result($q_sql);
			?><div class="row"><?php
			$colCount = 0;
			foreach($q_snd as $q_dta):
				if(!in_array($q_dta['Questions_id'], $exeptions)) {
					$fieldName = 'prQuestion_'.$q_dta['Questions_id'];
					$fieldValue = $prof_snd[$fieldName];
					if(($q_dta['QuestionTypes_id'] == 2) || ($q_dta['QuestionTypes_id'] == 5)):
						$colWidth = 12;
						$colCount++;
					else:
						$colWidth = 6;
					endif;
					if ($editable) {
						?><div class="col-md-<?php echo $colWidth?>" style="margin-bottom:4px;"><?php echo $q_dta['Questions_text']?>: <?php echo (($colWidth == 12)? '<br>':'')?><strong><?php echo $this->edit->render_customField($q_dta['Questions_id'], $fieldValue, $fieldValue, $personID)?></strong></div><?php
					} else {
						?><div class="col-md-<?php echo $colWidth?>" style="margin-bottom:4px;"><?php echo $q_dta['Questions_text']?>: <?php echo (($colWidth == 12)? '<br>':'')?><strong><?php echo str_replace("|", ", ", $fieldValue)?></strong></div><?php
					}
					$colCount++;
					if($colCount == 2):
						?></div><div class="row"><?php
					endif;
				}
			endforeach;
			?></div><?php
		endif;		
	}
	
	
	function renderProfileSidebarSelect($questionID, $currentValue, $icon_class, $listStyle='') {
		$q_sql = "SELECT * FROM Questions INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions.Questions_id='".$questionID."'";
		$q_snd = $this->db->get_single_result($q_sql);
		?>
        <li class="m-nav__item" style="<?php echo $listStyle?>">
            <a href="javascript:customSidebarQuestion('<?php echo $questionID?>')" class="m-nav__link">
                <?php if($icon_class != ''): ?>
                <i class="m-nav__link-icon <?php echo $icon_class?>"></i>
                <?php endif; ?>
                <span class="m-nav__link-title">
                    <span class="m-nav__link-wrap">
                        <span class="m-nav__link-text"><?php echo $q_snd['Questions_text']?><?php echo (($q_snd['QuestionTypes_name'] == 'TEXTAREA')? '<br>':':')?> <strong id="displayVal_<?php echo $questionID?>"><?php echo $currentValue?></strong></span>
                    </span>
                </span>
            </a>
        </li>        
        <?php
	}
	
	function renderProfileSidebarModal() {
		?>	
		<div class="modal fade" id="customQuestionModal" tabindex="-1" role="dialog" aria-labelledby="customQuestionModalLabel" aria-hidden="true">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="customQuestionModalLabel">&nbsp;</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">   

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="submitCustomQuestionModal()">Save</button>
                    </div>
                </div>
            </div>
        </div>        
        <?php
	}

	
}
?>