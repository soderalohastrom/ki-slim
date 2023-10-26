<?php
/*! \class inlineEdit class.inline.edit.php "class.inline.edit.php"
 *  \brief used to render all of the inline editable form elements.
 */
class inlineEdit {
	/*! \fn obj __constructor($DB)
		\brief Datatable class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db = $DB;
		$this->nodata = '----------';
	}
	
	function render_basicField($type, $fieldName, $personID, $fieldValue, $fieldDisplayValue, $color, $url='/ajax/inline.basic.php') {		
		?><span id="<?php echo $fieldName?>"><a href="javascript:;" data-type="<?php echo $type?>" data-field="<?php echo $fieldName?>" data-key="<?php echo $personID?>" data-value="<?php echo $fieldValue?>" data-url="<?php echo $url?>" style="<?php echo (($color != '')? 'color:'.$color:'')?>" class="inline-edit-display basic-inline-edit m-link"><?php echo (($fieldDisplayValue == '')? '<span class="editable-empty">'.$this->nodata.'</span>':$fieldDisplayValue)?></a><span class="inline-edit-form"></span></span><?php		
	}
	
	
	function render_customField($qid, $displayValue, $dbValue, $PERSON_ID) {
		$sql = "SELECT * FROM Questions INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions.Questions_id='".$qid."'";	
		$snd = $this->db->get_single_result($sql);
		
		$uniqueID = 'prQuestion_'.$qid;
		ob_start();
		if($snd['QuestionTypes_name'] == 'SELECT') {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="select" class="inline-edit-display inline-edit-select m-link"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':$displayValue)?></a><span class="inline-edit-form"></span></span><?php
		} elseif($snd['QuestionTypes_name'] == 'RADIO') {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="radio" class="inline-edit-display inline-edit-radio m-link"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':$displayValue)?></a><span class="inline-edit-form"></span></span><?php		
		} elseif($snd['QuestionTypes_name'] == 'CHECKBOX') {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="checkbox" class="inline-edit-display inline-edit-checkbox m-link"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':str_replace("|", ", ", $displayValue))?></a><span class="inline-edit-form"></span></span><?php
		} elseif($snd['QuestionTypes_name'] == 'DATE') {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo date("m/d/Y", $dbValue)?>" data-type="date" class="inline-edit-display inline-edit-date m-link"><?php echo (((trim($displayValue) == '') || ($displayValue == 0))? '<span class="editable-empty">'.$this->nodata.'</span>':date("m/d/Y", $displayValue))?></a><span class="inline-edit-form"></span></span><?php
		} elseif($snd['QuestionTypes_name'] == 'TEXTAREA') {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="textarea" class="inline-edit-display inline-edit-textarea m-link"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':nl2br($displayValue))?></a><span class="inline-edit-form"></span></span><?php
		} else {
			?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-url="/ajax/inline.profile.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="text" class="inline-edit-display inline-edit-text m-link"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':$displayValue)?></a><span class="inline-edit-form"></span></span><?php
		}
		return ob_get_clean();
	}
	
	function render_customPref($qid, $matchID, $displayValue, $dbValue, $PERSON_ID) {
		$sql = "SELECT * FROM PrefQuestions WHERE PrefQuestions.PrefQuestion_id='".$qid."'";
		$snd = $this->db->get_single_result($sql);
		$uniqueID = 'prefQuestion_'.$qid;
		$labelField = $snd['PrefQuestions_mappedMatchField'];
		switch($labelField) {
			case 'Gender':
			if($displayValue == 'F') {
				$display = 'Female';
			} elseif($displayValue == 'M') {
				$display = 'Male';
			} else {
				$display = $this->nodata;
			}
			break;
			
			default:
			$display = str_replace("|", ", ", $displayValue);
			break;
		}
		ob_start();					
		?><span id="<?php echo $uniqueID?>"><a href="javascript:;" data-field="<?php echo $qid?>" data-match="<?php echo $matchID?>" data-url="/ajax/inline.prefs.php" data-key="<?php echo $PERSON_ID?>" data-value="<?php echo $dbValue?>" data-type="checkbox" class="inline-edit-display inline-edit-prefs m-link" style="display:block;"><?php echo ((trim($displayValue) == '')? '<span class="editable-empty">'.$this->nodata.'</span>':$display)?></a><span class="inline-edit-form"></span></span><?php
		return ob_get_clean();		
	}
	
	
	
	
	
}