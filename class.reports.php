<?php
class Reports {
	function __construct($DB, $RECORD) {
		$this->db						= $DB;	
		$this->record					= $RECORD;		
		$this->coreFields['Person'] 	= $this->getCoreFields();
		$this->coreFields['Profile'] 	= $this->getCustomQuestionsColumnIndex();
		$this->universalReports			= array(97);
		$this->exportData				= array();				
	}
	
	function getCoreFields() {
		$return = array(
			array(
				"title"	=>	"First Name",
				"field"	=> 	"Persons.FirstName",
				"type"	=>	"TEXT",
			),
			array(
				"title"	=>	"Last Name",
				"field"	=> 	"Persons.LastName",
				"type"	=>	"TEXT",
			),
			array(
				"title"	=>	"Email",
				"field"	=> 	"Persons.Email",
				"type"	=>	"TEXT",
			),
			array(
				"title"	=>	"Date Created",
				"field"	=>	"Persons.DateCreated",
				"type"	=>	"DATE"
			),
			array(
				"title"	=>	"Age",
				"field"	=>	"Persons.DateOfBirth",
				"type"	=>	"TEXT"
			),
			array(
				"title"	=>	"Gender",
				"field"	=>	"Persons.Gender",
				"type"	=>	"SELECT",
				"opt"	=>	array(
					array(
						"text"	=>	"Female",
						"value"	=>	"F"
					),
					array(
						"text"	=> "Male",
						"value"	=>	"M"
					)
				)
			),
			array(
				"title"	=>	"Record Type",
				"field"	=>	"Persons.PersonsTypes_id",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions('PersonTypes', 'PersonsTypes_text', 'PersonsTypes_id', 'WHERE PersonsTypes_id IN (3, 4, 5, 6, 7, 8, 10, 11, 12)')
			),
			array(
				"title"	=>	"Marital Status",
				"field"	=>	"Persons.MaritalStatus",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions('DropDown_MaritalStat', 'Mstat', 'Mstat')
			),
			array(
				"title"	=>	"Last Edit",
				"field"	=>	"Persons.DateUpdated",
				"type"	=>	"DATE"
			),
			array(
				"title"	=>	"Last Action|Note",
				"field"	=>	"LastNoteAction",
				"type"	=>	"TEXT"
			),
			array(
				"title"	=>	"Market Director",
				"field"	=>	"Persons.Assigned_userID",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("Users", "Name", "user_id","","SELECT CONCAT(firstName,' ',lastName) as Name, user_id FROM Users WHERE 1 ORDER BY lastName ASC")				
			),
			array(
				"title"	=>	"Relationships Manager",
				"field"	=>	"Persons.Matchmaker_id",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("Users", "Name", "user_id","","SELECT CONCAT(firstName,' ',lastName) as Name, user_id FROM Users WHERE 1 ORDER BY lastName ASC")				
			),
			array(
				"title"	=>	"Network Developer",
				"field"	=>	"Persons.Matchmaker2_id",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("Users", "Name", "user_id","","SELECT CONCAT(firstName,' ',lastName) as Name, user_id FROM Users WHERE 1 ORDER BY lastName ASC")				
			),
			array(
				"title"	=>	"Last Intro",
				"field"	=>	"Persons.LastIntroDate",
				"type"	=>	"DATE"
			),			
			array(
				"title"	=>	"Location",
				"field"	=>	"Persons.Offices_id",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions('Offices', 'office_Name', 'Offices_id')
			),
			
			array(
				"title"	=>	"Status",
				"field"	=>	"Persons_Color_Span",
				"type"  =>  "SPECIAL"
			),
			
			array(
				"title"	=>	"Lead Status",
				"field"	=>	"Persons.LeadStages_id",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("LeadStages", "LeadStages_name", "LeadStages_id")				
			),
			array(
				"title"	=>	"Lead Stage",
				"field"	=>	"LeadStages.LeadStages_name",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("LeadStages", "LeadStages_name", "LeadStages_id")				
			),
			array(
				"title"	=>	"Photo",
				"field"	=>	"PersonsImages_path",
				"type"	=>	"SPECIAL"				
			),
			array(
				"title"	=>	"Phone",
				"field"	=>	"Phone_number",
				"type"	=>	"SPECIAL"				
			),
			array(
				"title"	=>	"Source",
				"field"	=>	"HearAboutUs",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("DropDown_LeadSource", "Source_name", "Source_name", " WHERE Source_status='1' order by Source_name")
			),
			array(
				"title"	=>	"Form",
				"field"	=>	"FormName",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_coreOptions("CompanyForms", "FormName", "FormCallString", " WHERE 1")
			),
			array(
				"title"	=>	"Paid To Date",
				"field"	=>	"TotalPaid",
				"type"	=>	"TEXT"
			),
			array(
				"title"	=> "Sales Commissions",
				"field"	=> "SalesCommissionsPaid",
				"type"	=> "TEXT"
			),			
			array(
				"title"	=>	"City",
				"field"	=>	"Addresses.City",
				"type"	=>	"TEXT"
			),
			array(
				"title"	=>	"State/Prov",
				"field"	=>	"Addresses.State",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_distinctValues('Addresses', 'State')
			),
			array(
				"title"	=>	"Postal",
				"field"	=>	"Addresses.Postal",
				"type"	=>	"TEXT"
			),			
			array(
				"title"	=>	"Country",
				"field"	=>	"Addresses.Country",
				"type"	=>	"SELECT",
				"opt"	=>	$this->get_distinctValues('Addresses', 'Country')
			)
					
		);
		return $return;		
	}
	
	function get_distinctValues($table, $field) {
		$sql = "SELECT DISTINCT(".$field.") as distValue FROM ".$table." ORDER BY ".$field." ASC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$optionArray[] = array(
				"text" 	=> 	$dta['distValue'],
				"value"	=>	$dta['distValue']
			);
		endforeach;	
		return $optionArray;
	}
	
	function getCustomQuestionsColumnIndex() {
		$sql = "
		SELECT 
			* 
		FROM 
			Questions 
			INNER JOIN QuestionsCategories ON QuestionsCategories.QuestionsCategories_id=Questions.QuestionsCategories_id 
			INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions_active='1' 
		AND 
			QuestionTypes_name IN ('CHECKBOX','SELECT','RADIO','DATE','TEXTAREA','TEXT') 
		AND 
			Questions.QuestionsCategories_id != '15' 
		ORDER BY 
			Questions.QuestionsCategories_id ASC, 
			Questions_order ASC
		";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta) {
			$profileFields[] = array(
				"title"	=>	$dta['Questions_text'],
				"field"	=>	'PersonsProfile.'.$dta['MappedField'],
				"type"	=>	$dta['QuestionTypes_name'],
				"opt"	=>	$this->get_customOptions($dta['Questions_id'])
			);
		}
		return $profileFields;		
	}
	
	function getCustomQuestionsIndex() {
		$sql = "
		SELECT 
			* 
		FROM 
			Questions 
			INNER JOIN QuestionsCategories ON QuestionsCategories.QuestionsCategories_id=Questions.QuestionsCategories_id 
			INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions_active='1' 
		AND 
			QuestionTypes_name IN ('CHECKBOX','SELECT','RADIO','DATE') 
		AND 
			Questions.QuestionsCategories_id != '15' 
		ORDER BY 
			Questions.QuestionsCategories_id ASC, 
			Questions_order ASC
		";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta) {
			$profileFields[] = array(
				"title"	=>	$dta['Questions_text'],
				"field"	=>	'PersonsProfile.'.$dta['MappedField'],
				"type"	=>	$dta['QuestionTypes_name'],
				"opt"	=>	$this->get_customOptions($dta['Questions_id'])
			);
		}
		return $profileFields;		
	}
	
	function get_customOptions($qid) {
		$sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='".$qid."' ORDER BY QuestionsAnswers_order ASC";
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			return array();
		} else {
			foreach($snd as $dta) {
				$optionArray[] = array(
					"text" 	=> $dta['QuestionsAnswers_value'],
					"value"	=>	$dta['QuestionsAnswers_value']
				);	
			}
			return $optionArray;	
		}
	}
	
	function get_coreOptions($table, $txt_field, $val_field, $filter='', $query_override='') {
		if($query_override != '') {
			$sql = $query_override;
		} else {
			$sql = "SELECT * FROM ".$table." ".$filter;
		}
		$snd = $this->db->get_multi_result($sql);
		if(isset($snd['empty_result'])) {
			return array();
		} else {
			foreach($snd as $dta) {
				$optionArray[] = array(
					"text" 	=> $dta[$txt_field],
					"value"	=>	$dta[$val_field]
				);	
			}
			return $optionArray;
		}		
	}
	
	function render_qformColumns($chosen) {
		//$this->coreColumns['Persons']
		//print_r($chosen);
		ob_start();
		?>
        <div class="m-form__group form-group">
            <label>Core Record Fields</label>
            <div class="m-checkbox-list">
            <?php foreach($this->coreFields['Person'] as $field): ?>
                <label class="m-checkbox m-checkbox--state-success">
                    <input type="checkbox" class="checkColumn" value="col_<?php echo $field['field']?>"  data-label="<?php echo $field['title']?>" name="column[]" <?php echo (in_array('col_'.$field['field'], $chosen)? 'checked':'')?>>
                    <?php echo $field['title']?>
                    <span></span>
                </label>
			<?php endforeach; ?>
            </div>
        </div>
		<div class="m-form__group form-group">
            <label>Profile Fields</label>
            <div class="m-checkbox-list">
            <?php foreach($this->coreFields['Profile'] as $field): ?>
                <label class="m-checkbox m-checkbox--state-success">
                    <input type="checkbox" class="checkColumn" value="col_<?php echo $field['field']?>"  data-label="<?php echo $field['title']?>" name="column[]" <?php echo (in_array('col_'.$field['field'], $chosen)? 'checked':'')?>>
                    <?php echo $field['title']?>
                    <span></span>
                </label>
			<?php endforeach; ?>
            </div>
        </div>
		<?php
		return ob_get_clean();	
		
	}
	
	function render_qSelect($chosen, $selected=false, $includeArray=array('SELECT', 'RADIO', 'CHECKBOX', 'DATE')) {
		ob_start();
		if($selected) {
			$myChoice = $selected;
			$mySelection = true;	
		} else {
			$mySelection = false;
		}
		?><optgroup label="Core Fields"><?php
		foreach($this->coreFields['Person'] as $field) {
			if(@in_array($field['type'], $includeArray)):
				if($mySelection):				
				?><option value="<?php echo $field['field']?>" <?php echo (@in_array($field['field'], $chosen)? 'disabled':'')?> <?php echo (($myChoice == $field['field'])? 'selected':'')?>><?php echo $field['title']?></option><?php
				else:
				?><option value="<?php echo $field['field']?>" <?php echo (@in_array($field['field'], $chosen)? 'disabled':'')?>><?php echo $field['title']?></option><?php
				endif;
			endif;
		}
		?></optgroup><?php
		?><optgroup label="Profile Fields"><?php
		foreach($this->coreFields['Profile'] as $field) {
			if(@in_array($field['type'], $includeArray)):
				if($mySelection):
				?><option value="<?php echo $field['field']?>" <?php echo (@in_array($field['field'], $chosen)? 'disabled':'')?> <?php echo (($myChoice == $field['field'])? 'selected':'')?>><?php echo $field['title']?></option><?php
				else:
				?><option value="<?php echo $field['field']?>" <?php echo (@in_array($field['field'], $chosen)? 'disabled':'')?>><?php echo $field['title']?></option><?php
				endif;
			endif;			
		}
		?></optgroup><?php
		
		return ob_get_clean();
	}
	
	function find_qSelect($fieldName) {
		if($fieldName == 'AGE') {
			//echo "AGE RANGE QUERY";
			$field = array(
				'title'		=>	'Age',
				'field'		=>	'DateOfBirth',
				'type'		=>	'SELECT',
				'opt'		=>	array(
					array(
						'text'	=>	'Under 25',
						'value'	=>	'18-25'
					),
					array(
						'text'	=>	'26-35',
						'value'	=>	'26-35'
					),
					array(
						'text'	=>	'36-45',
						'value'	=>	'36-45'
					),
					array(
						'text'	=>	'46-55',
						'value'	=>	'46-55'
					),
					array(
						'text'	=>	'56-65',
						'value'	=>	'56-65'
					),
					array(
						'text'	=>	'66-75',
						'value'	=>	'66-75'
					),
					array(
						'text'	=>	'Over 75',
						'value'	=>	'76-99'
					)
				)
			);
			return $field;			
		} else {		
			foreach($this->coreFields['Person'] as $field) {
				if($fieldName == $field['field']) {
					return $field;
				}
			}
			foreach($this->coreFields['Profile'] as $field) {
				if($fieldName == $field['field']) {
					return $field;
				}		
			}
		}
	}
	
	function render_qFormFilterPreview($field, $label, $operand, $fieldsText, $fieldValues) {
		ob_start();
		?>
        <div class="m-stack__item" id="<?php echo str_replace(".", "_", $field)?>">
            <div class="m-stack__demo-item">
            	<a href="javascript:dropFilter('<?php echo str_replace(".", "_", $field)?>');" class="pull-right"><i class="fa fa-close"></i></a>
                <strong><?php echo $label?>&nbsp;<?php echo $operand?>&nbsp;</strong>(<?php echo $fieldsText?>)
            </div>
            <input type="hidden" name="filter_label[]" value="<?php echo $label?>" />
            <input type="hidden" name="filter_field[]" class="filter_field_fieldname" value="<?php echo $field?>" />            
            <input type="hidden" name="filter_operand[]" value="<?php echo $operand?>" />
            <input type="hidden" name="filter_option_labels[]" value="<?php echo implode("|", explode(", ", $fieldsText))?>" />
            <input type="hidden" name="filter_option_values[]" value="<?php echo implode("|", explode(", ", $fieldValues))?>" />
        </div>
        <?php	
		return ob_get_clean();
	}
	
	function render_qformSelect($fieldLabel, $field, $options) {
		ob_start();
		?>
        <div class="row">
        	<div class="col-4 text-center">
            	<div style="margin-top:10px; font-size:1.25em;"><strong><?php echo $fieldLabel?></strong></div>
                <button type="button" class="btn btn-primary m-btn m-btn--icon" id="button-select-add" data-label="<?php echo $fieldLabel?>" data-id="<?php echo $field?>">
                    <span>
                        <i class="la la-plus"></i>
                        <span>
                            Add Filter
                        </span>
                    </span>
                </button>				
            </div>
            <div class="col-8">
            	<div class="m-form__group form-group">
                    <div class="m-checkbox-list">
                        <?php foreach($options as $option): ?>
                        <label class="m-checkbox m-checkbox--success">
                            <input type="checkbox" value="<?php echo $option['value']?>" data-label="<?php echo $option['text']?>" class="reportFilterOption" name="options[]">
                            <?php echo $option['text']?>
                            <span></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <span class="m-form__help">
                        check the values you want to include in the filter
                    </span>
                </div>                
            </div>        
        </div>
        <?php	
		return ob_get_clean();
	}
	
	function render_qformDate($fieldLabel, $field) {
		ob_start();
		?>
        <div class="row">
        	<div class="col-4 text-center">
            	<div style="margin-top:10px; font-size:1.25em;"><strong><?php echo $fieldLabel?></strong></div>				
            </div>
            <div class="col-4">
            	<select class="form-control m-input" id="fieldOperand">
                    <!-- <option value="=">= (Equals)</option> -->
					<!-- <option value="!=">!= (Not Equals)</option> -->                    
                    <option value=">">&gt; (Greater Than)</option>
                    <!-- <option value="<">&lt; (Less Than)</option> -->
                    <!-- <option value=">=">&gt;= (Greater Than or Equal To)</option> -->
                    <!-- <option value="<=">&lt;= (Greater than or Equal To)</option> -->
                    <!-- <option value="LIKE">LIKE (Contains)</option> -->
                    <!-- <option value="NOT LIKE">NOT LIKE (Does Not Contain)</option> -->
                    <!-- <option value="START_W">Starts With X)</option> -->
                </select>

            </div>
            <div class="col-4">
            	<select class="form-control m-input" id="fieldTimeDays">
                    <option value="EPOCH(7)">7 Days Ago</option>
                    <option value="EPOCH(14)">14 Days Ago</option>
                    <option value="EPOCH(30)">30 Days Ago</option>
                    <option value="EPOCH(60)">60 Days Ago</option>
                    <option value="EPOCH(90)">90 Days Ago</option>                   
                </select>                
            </div>            	
        </div>
        <div class="row">
        	<div class="col-lg-6">
            	<span class="m-form__help">date parameter will be adjustable when the report is displayed.</span>
            </div>
            <div class="col-lg-6 text-right">
            	<button type="button" class="btn btn-primary m-btn m-btn--icon" id="button-date-add" data-label="<?php echo $fieldLabel?>" data-field="<?php echo $field?>">
                    <span>
                        <i class="la la-plus"></i>
                        <span>
                            Add Filter
                        </span>
                    </span>
                </button>
                <!--
                <button type="button" class="btn btn-info m-btn m-btn--icon" id="button-expand" data-type="DATE">
                    <span>
                        <i class="la la-plus-circle"></i>
                        <span>
                            Expand Filter
                        </span>
                    </span>
                </button>
                -->
            </div>            
        </div>
        <?php
		return ob_get_clean();	
	}
	
	
	function get_myReports($user_id) {
		$my_reports_sql = "
		SELECT
			*
		FROM
			Reports
			INNER JOIN ReportsAccess ON ReportsAccess.Report_id=Reports.Report_id
		WHERE
			ReportsAccess.user_id='".$user_id."'
		";
		$my_reports_snd = $this->db->get_multi_result($my_reports_sql);
		if(isset($my_reports_snd['empty_result'])):
			return false;
		else:
			foreach($my_reports_snd as $report):
				$reportArray[] = array(
					'title'	=>	$report['Report_name'],
					'id'	=>	$report['Report_id']				
				);
			endforeach;
			return $reportArray;
		endif;
	}
	
	function can_see_report($user_id, $report_id) {
		if ($report_id === "270" || $report_id === "62") {
			return true;
		}
		$my_reports_sql = "
		SELECT
			count(*) as count
		FROM
			Reports
			INNER JOIN ReportsAccess ON ReportsAccess.Report_id=Reports.Report_id
		WHERE
			ReportsAccess.user_id='".$user_id."'
		AND
			ReportsAccess.Report_id='".$report_id."'
		";
		$my_reports_snd = $this->db->get_single_result($my_reports_sql);
		if($my_reports_snd['count'] == 0):			
			if(in_array($report_id, $this->universalReports)) {
				//return true;
				$access = true;
			} else {
				//return false;
				$access = false;
			}
		else:
			//return true;
			$access = true;
		endif;
		
		if(!$access) {
			$perm_sql = "SELECT count(*) as count FROM UsersPermissions WHERE user_id='".$user_id."' AND Permissions_id='85'";
			$perm_snd = $this->db->get_single_result($perm_sql);
			if($perm_snd['count'] == 0) {
				$access = false;	
			} else {
				$access = true;
			}			
		}
		return $access;				
	}
	
	function is_report_superuser($user_id) {
		$perm_sql = "SELECT count(*) as count FROM UsersPermissions WHERE user_id='".$user_id."' AND Permissions_id='85'";
		$perm_snd = $this->db->get_single_result($perm_sql);
		if($perm_snd['count'] == 0) {
			$access = false;	
		} else {
			$access = true;
		}	
		return $access;
	}
	
	function render_THEAD($labels) {
		ob_start();
		?>
        <tr>
            <th>&nbsp;</th>
			<?php foreach($labels as $label): ?>
            <th><?php echo $label?></th>
            <?php $thead[] = $label; ?>
            <?php endforeach; ?>
        </tr>
        <?php				
		$this->exportData[] = $thead;
		return ob_get_clean();	
	}
	
	function render_THEADTopper($labels, $field, $barTitle, $count=false) {
		ob_start();		
		?>
        <tr>
        	<th colspan="<?php echo (count($labels) + 1)?>">
            <?php echo $barTitle?> <?php echo (($count)? ' | '.$count.' Records':'')?>
            </th>
        </tr>
        <?php
		$this->exportData[] = array($barTitle, $count.' Records');
		return ob_get_clean();	
	}
	
	function render_TFOOTFooter($labels, $field, $barTitle, $count=false, $REPORT_FILENAME) {		
		$this->exportData[] = array('TOTAL', $count);				
		//print_r($this->exportData);
		ob_start();		
		?>
        <table class="table">
		<thead class="thead-inverse">
        <tr>
        	<th colspan="<?php echo (count($labels) - 3)?>">
            <?php echo (($count)? 'TOTAL: &nbsp;&nbsp;'.$count.' Records':'')?>
            </th>
            <th colspan="4" class="text-right">
            <form id="report-export-matrix" action="/export_table_to_csv.php" method="post" target="_blank">
                <input type="hidden" name="export-data" value="<?php echo urlencode(serialize($this->exportData))?>" />
                <input type="hidden" name="export-filename" value="<?php echo $REPORT_FILENAME?>" />
                <button type="submit" class="btn btn-default btn-sm">Export to CSV <i class="fa fa-bar-chart"></i></button>
	        </form> 
            </th>
        </tr>
        </thead>
        </table>
        <?php		
		return ob_get_clean();	
	}
	
	function parseFieldforValue($field, $value) {
		$found = 0;
		$s_field = str_replace("col_", "", $field);
		//echo "FIELD FOR LABEL".$field."|".$s_field;
		
		foreach($this->coreFields['Person'] as $fieldBlock):
			if($fieldBlock['field'] == $s_field) {
				$found = 1;
				$label_start = $fieldBlock['title'];
				if(isset($fieldBlock['opt'])) {
					foreach($fieldBlock['opt'] as $option):
						if($option['value'] == $value) {
							$label_end = $option['text'];	
						}
					endforeach;						
				}
			}		
		endforeach;
		
		if($found == 0) {
			foreach($this->coreFields['Profile'] as $fieldBlock):
				if($fieldBlock['field'] == $s_field) {
					$found = 1;
					$label_start = $fieldBlock['title'];
					if(isset($fieldBlock['opt'])) {
						foreach($fieldBlock['opt'] as $option):
							if($option['value'] == $value) {
								$label_end = $option['text'];	
							}
						endforeach;						
					}
				}		
			endforeach;
			
			if(!isset($label_end)) {
				$label_end = '<span class="m--font-danger">'.$value.'</span>';
			}
		}
		return $label_end;		
	}
	
	function parseFieldforLabel($field) {
		$found = 0;		
		$s_field = str_replace("col_", "", $field);
		//echo "FIELD FOR LABEL".$field."|".$s_field;
		foreach($this->coreFields['Person'] as $fieldBlock):
			if($fieldBlock['field'] == $s_field) {
				$found = 1;
				$label_start = $fieldBlock['title'];
			}		
		endforeach;
		
		if($found == 0) {
			foreach($this->coreFields['Profile'] as $fieldBlock):
				if($fieldBlock['field'] == $field) {
					$found = 1;
					$label_start = $fieldBlock['title'];
				}		
			endforeach;
		}
		return $label_start;		
	}
	
	function render_TBODY($fields, $sqlResults) {
		ob_start();
		//print_r($fields);
		foreach($sqlResults as $result):
		?><tr><?php
		?><td>
        	<a href="/profile/<?php echo $result['Person_id']?>" class="btn btn-sm btn-primary"><i class="fa fa-user"></i></a>
        	<sup><a href="/profile/<?php echo $result['Person_id']?>" target="_blank"><i class="fa fa-external-link"></i></a></sup>
        </td><?php			
		foreach($fields as $field):
			$fieldParts = explode(".", $field);
			//print_r($fieldParts);
			if(count($fieldParts) > 1):
				switch(str_replace("col_", "", $fieldParts[1])) {
					case 'DateCreated':
					$display = date("m/d/Y", $result[$fieldParts[1]]);
					break;
					
					case 'DateUpdated':
					if($result[$fieldParts[1]] == 0) {
						$display = '&nbsp;';
					} else {
						$display = date("m/d/Y", $result[$fieldParts[1]]);
					}
					break;
					
					case 'LastIntroDate':
					if($result[$fieldParts[1]] == 0) {
						$display = '&nbsp;';
					} else {
						$display = date("m/d/Y", $result[$fieldParts[1]]);
					}
					break;
					
					case 'DateOfBirth':
					$display = $this->record->get_personAge($result['DateOfBirth'], true);					
					break;
					
					case 'prQuestion_676':
					if($result[$fieldParts[1]] == 0) {
						$display = '&nbsp;';
					} else {
						$display = date("m/d/Y", $result[$fieldParts[1]]);
					}
					break;
					
					case 'prQuestion_677':
					if($result[$fieldParts[1]] == 0) {
						$display = '&nbsp;';
					} else {
						$display = date("m/d/Y", $result[$fieldParts[1]]);
					}
					break;
					
					case 'PersonsTypes_id':
					$display = $this->record->get_personType($result['Person_id']);
					break;
					
					case 'Assigned_userID':
					$display = $this->record->get_FulluserName($result[$fieldParts[1]]);
					break;
					
					case 'Matchmaker_id':
					$display = $this->record->get_FulluserName($result[$fieldParts[1]]);
					break;

					case 'Matchmaker2_id':
						$display = $this->record->get_FulluserName($result[$fieldParts[1]]);
					break;

					case 'Persons_Color_Span':
						$display = 'Persons_Color_Span:'.$result['Persons_Color_Span'];
					break;
					
					case 'Offices_id':
					$o_sql = "SELECT * FROM Offices WHERE Offices_id='".$result[$fieldParts[1]]."'";
					$o_snd = $this->db->get_single_result($o_sql);
					$display = $o_snd['office_Name'];
					break;
					
					case 'LeadStages_id':
					$o_sql = "SELECT * FROM LeadStages WHERE LeadStages_id='".$result[$fieldParts[1]]."'";
					$o_snd = $this->db->get_single_result($o_sql);
					$display = $o_snd['LeadStages_name'];
					break;
					
					case 'HearAboutUs':
					$display = 'Hear About Us:'.$result['HearAboutUs'];
					break;
					
					default:
					$display = $result[$fieldParts[1]];
					break;	
				}
				?><td><?php echo $display?></td><?php
			else:
				switch(str_replace("col_", "", $fieldParts[0])) {
					case 'col_LastNoteAction':
					$display = $result[$fieldParts[0]];
					break;
					
					case 'col_PersonsImages_path':
					$display = '<img src="'.$this->record->get_PrimaryImage($result['Person_id'], false).'" style="max-width:50px; max-height:50px;" />';
					//$display = '<span class="m-list-search__result-item-pic" style="background-image:url(\''.$this->record->get_PrimaryImage($result['Person_id'], false).'\'); background-size:cover;"><img class="m--img-rounded" src="/assets/app/media/img/users/filler.png" title=""></span>';
					break;

					case 'Persons_Color_Span':
						$display = 'Persons_Color_Span:'.$result['Persons_Color_Span'];
					break;
					

					case 'Phone_number':
					$display = $this->record->get_primaryPhone($result['Person_id'], false, true);
					break;
					
					case 'HearAboutUs':
					$display = $result['HearAboutUs'];
					break;										
					
					default:
					$fieldNameField = str_replace("col_", "", $fieldParts[1]);
					$display = $result[$fieldNameField];
					break;	
				}
				?><td><?php echo $display?></td><?php				
			endif;
			$tbody[] = $display;				
		endforeach;
		$this->exportData[] = $tbody;
		unset($tbody);
		?><tr><?php
		endforeach;	
		return ob_get_clean();
	}
	
	function genReport($reportID, $dateParam=array()) {
		ob_start();
		if($reportID == 0):
			?>
            <div class="m-alert m-alert--icon m-alert--outline alert alert-danger" role="alert">
                <div class="m-alert__icon">
                    <i class="la la-warning"></i>
                </div>
                <div class="m-alert__text">
                    <strong>
                        Invalid Report ID
                    </strong><br />
                    You must first save this report before it can be viewed.
                </div>
            </div>
            
            <?php
		else:
			$r_sql = "SELECT * FROM Reports WHERE Report_id='".$reportID."'";
			$r_snd = $this->db->get_single_result($r_sql);
			$R_DATA = $r_snd;			
			$REPORT_FILENAME = $R_DATA['Report_name'].'.'.time().'.csv';
			
			if($R_DATA['Report_type'] == 1) {
				$rCONFIG = json_decode($R_DATA['Report_config'], true);
				//print_r($rCONFIG);
				//echo "<hr>";

				?><div class="row"><?php
				if(count($rCONFIG['graphs']) > 0):
					?><div class="col-lg-9"><?php
				else:
					?><div class="col-lg-12"><?php
				endif;							
					if($rCONFIG['groupBy'] != ''):
						if(count($dateParam) == 0) {
							$distSQL = $rCONFIG['sql'];
						} else {
							//echo "CREATE DYNAMIC QUERY";
							$distSQL = $this->runDynamicQuery($rCONFIG, $dateParam[0], $dateParam[1]);
						}
						//echo $distSQL."<br><br>\n";
						$selectEnd = strrpos($distSQL, 'FROM');
						$sql_sub = substr($distSQL, $selectEnd, strlen($distSQL));					
						//echo $sql_sub."<br><br>\n";

						$distinctSQL = "SELECT DISTINCT(".str_replace("col_", "", $rCONFIG['groupBy']).") as DistValue ".$sql_sub;	
						//echo $distinctSQL."<br>";
						
						$distinctSND = $this->db->get_multi_result($distinctSQL);
						if(isset($distinctSND['empty_result'])) {
								
						} else {
							foreach($distinctSND as $distinct):
								if(count($dateParam) == 0) {
									$newSQL = $rCONFIG['sql'];
								} else {
									$newSQL = $this->runDynamicQuery($rCONFIG, $dateParam[0], $dateParam[1]);
								}
								$tmpSQL = str_replace('GROUP BY Persons.Person_id', " AND ".str_replace("col_", "", $rCONFIG['groupBy'])." = '".$distinct['DistValue']."' GROUP BY Persons.Person_id", $newSQL);						
								//echo $tmpSQL."<br><br>\n";
								$tmpSND = $this->db->get_multi_result($tmpSQL);
								if(!isset($tmpSND['empty_result'])):
									$groupByCount[] = count($tmpSND);
								?>
								<table class="table">
									<thead class="thead-inverse">
										<?php echo $this->render_THEADTopper($rCONFIG['columns'], $distinct['DistValue'], $this->parseFieldforValue($rCONFIG['groupBy'], $distinct['DistValue']), count($tmpSND))?>
										<?php echo $this->render_THEAD($rCONFIG['columns'])?>
									</thead>
									<tbody>
										<?php echo $this->render_TBODY($rCONFIG['fields'], $tmpSND)?>
									</tbody>                                   
								</table>
								<?php
								endif;
							endforeach;
							echo $this->render_TFOOTFooter($rCONFIG['columns'], $distinct['DistValue'], $this->parseFieldforValue($rCONFIG['groupBy'], $distinct['DistValue']), @array_sum($groupByCount), $REPORT_FILENAME);							
						}										
					else:
						if(count($dateParam) == 0) {
							//echo "DB GENERATED";	
							$report_sql = $rCONFIG['sql']." ORDER BY ".$rCONFIG['sortBy']." ".$rCONFIG['sortDir'];
						} else {
							//echo "DYNAMICLY GENERATED<br>\n";	
							$report_sql = $this->runDynamicQuery($rCONFIG, $dateParam[0], $dateParam[1]);
						}
						//echo $report_sql;
						$report_snd = $this->db->get_multi_result($report_sql);
						?>
						<table class="table">
							<thead class="thead-inverse">
								<?php echo $this->render_THEAD($rCONFIG['columns'])?>
							</thead>
							<tbody>
								<?php echo $this->render_TBODY($rCONFIG['fields'], $report_snd)?>
							</tbody>
						</table>
						<?php
						echo $this->render_TFOOTFooter($rCONFIG['columns'], $distinct['DistValue'], $this->parseFieldforValue($rCONFIG['groupBy'], $distinct['DistValue']), false, $REPORT_FILENAME);
					endif;
					?>
					</div>
					<?php if(count($rCONFIG['graphs']) > 0): ?>
					<div class="col-lg-3">
						<?php foreach($rCONFIG['graphs'] as $graph): ?>
						<?php echo $this->drawGraph($graph, $reportID, $dateParam)?>
						<?php endforeach; ?>                	
					</div>
					<?php endif; ?>
				</div>			                    
				<?php
			} else {
				$include_file = $R_DATA['Report_config'];
				include("./custom_reports/".$include_file);
			}
		endif;
	}
	
	function drawGraph($field, $reportID, $dateParam=array()) {		
		$r_sql = "SELECT * FROM Reports WHERE Report_id='".$reportID."'";
		$r_snd = $this->db->get_single_result($r_sql);
		$R_DATA = $r_snd;
		$rCONFIG = json_decode($R_DATA['Report_config'], true);
		//echo "DATE PARAMETERS:".$dateParam;
		if(count($dateParam) == 0) {
			$newSQL = $rCONFIG['sql'];
		} else {
			$newSQL = $this->runDynamicQuery($rCONFIG, $dateParam[0], $dateParam[1]);
		}
			
		$selectEnd = strrpos($newSQL, 'FROM');
		$sql_sub = substr($newSQL, $selectEnd);
		//echo $sql_sub."<br>\n";
		$distinctSQL = "SELECT DISTINCT(".str_replace("col_", "", $field).") as DistValue ".$sql_sub;
		//echo $distinctSQL."<br><br>\n";
		$distinctSND = $this->db->get_multi_result($distinctSQL);
		$chartID = 'Chart_'.str_replace(".", "_", $field);
		
		if(isset($distinctSND['empty_result'])) {
							
		} else {
			foreach($distinctSND as $distinct):
				//$newSQL = $rCONFIG['sql'];
				$tmpSQL = str_replace('GROUP BY Persons.Person_id', " AND ".str_replace("col_", "", $field)." = '".$distinct['DistValue']."' GROUP BY Persons.Person_id", $newSQL);				
				//echo $tmpSQL."<br><br>\n";
				$tmpSND_found = $this->db->get_multi_result($tmpSQL, true);
				$dataArray[] = array(
					'title'	=>	$this->parseFieldforValue($field, $distinct['DistValue']),
					'total'	=>	$tmpSND_found
				);
			endforeach;
		}
		ob_start();
		?>
		<div id="<?php echo $chartID?>" style="height: 500px;"></div>
        <script>
		AmCharts.makeChart("<?php echo $chartID?>", {
			type: "pie",
			theme: "light",
			dataProvider: <?php echo json_encode($dataArray)?>,
			valueField: "total",
			titleField: "title",
			pullOutRadius: 0,
			labelRadius: -22,
			labelText: "[[percents]]%",
			percentPrecision: 1,
			titles: [{
    			text: "By <?php echo $this->parseFieldforLabel($field)?>"
  			}],			
			balloon: {
				fixedPosition: !0
			},
			export: {
				enabled: !0
			}
		})
		</script>
        <?php	
		
	}
	
	function runDynamicQuery($report, $dateStart='', $dateEnder='') {
		//echo $dateStart."|".$dateEnder."<br>\n";
		for($i=0; $i<count($report['filters']['fields']); $i++) {
			if($report['filters']['operand'][$i] == 'IN') {
				$SQL['filters'][] = $report['filters']['fields'][$i]." ".$report['filters']['operand'][$i]." ('".str_replace("|", "', '", $report['filters']['option_values'][$i])."')";
			} else {
				if (substr($report['filters']['option_values'][$i], 0, 5) == 'EPOCH') {
					$epochBreak_part = str_replace("EPOCH(", "", $report['filters']['option_values'][$i]);
					$epochBreak = str_replace(")", "", $epochBreak_part);
					$epochTimestamp = time() - ($epochBreak * 86400);
					if(($dateStart == '') && ($dateEnder == '')) {
						$SQL['filters'][] = $report['filters']['fields'][$i]." ".$report['filters']['operand'][$i]." '".$epochTimestamp."'";
					} else {
						$SQL['filters'][] = "(".$report['filters']['fields'][$i]." >= '".$dateStart."' AND ".$report['filters']['fields'][$i]." <= '".$dateEnder."')";
					}
				} else {
					$SQL['filters'][] = $report['filters']['fields'][$i]." ".$report['filters']['operand'][$i]." '".$report['filters']['option_values'][$i]."'";
				}
			}		
		}
		
		// GENERATE SELECT //
		//print_r($report['fields']);
		for($i=0; $i<count($report['fields']); $i++) {
			//echo "FIELD:|".$report['fields'][$i]."<br>\n";
			switch(trim($report['fields'][$i])):
				case 'LastNoteAction':
				$selectArray[] = "(SELECT CONCAT(PersonsNotes_type,' &gt; ',PersonsNotes_header) FROM PersonsNotes WHERE PersonsNotes_personID=Persons.Person_id ORDER BY PersonsNotes_dateCreated DESC LIMIT 1) as LastNoteAction";
				break;
				
				case 'Phone_number':
				$selectArray[] = "(SELECT Phone_number FROM Phones WHERE Phones.Person_id=Persons.Person_id AND isPrimary='1' LIMIT 1) as Phone_number";
				break;
				
				case 'col_Phone_number':
				$selectArray[] = "(SELECT Phone_number FROM Phones WHERE Phones.Person_id=Persons.Person_id AND isPrimary='1' LIMIT 1) as Phone_number";
				break;
				
				default:
				$selectArray[] = str_replace("col_", "", $report['fields'][$i]);
				break;
			endswitch;
		}
		//print_r($selectArray);
		switch($report['type']) {
			case 'Persons':
			$BASE_SQL = "
			SELECT
				Persons.Person_id,
				".implode(",\n", $selectArray)."
			FROM
				Persons
				LEFT JOIN PersonsProfile ON PersonsProfile.Person_id=Persons.Person_id
				INNER JOIN Offices ON Offices.Offices_id=Persons.Offices_id
				LEFT JOIN PersonsImages ON PersonsImages.Person_id=Persons.Person_id AND PersonsImages_status='2'
				LEFT JOIN Addresses ON Addresses.Person_id=Persons.Person_id
				LEFT JOIN PersonsPrefs ON PersonsPrefs.Person_id=Persons.Person_id
			WHERE
				1	
			";
			break;
		}
	
		foreach($SQL['filters'] as $filter):
			$BASE_SQL .= " AND ".$filter;
		endforeach;
		$BASE_SQL .= " GROUP BY Persons.Person_id";
		$BASE_SQL .= " ORDER BY ".$report['sortBy']." ".$report['sortDir']." ";	
		//echo "<br><br><hr>".$BASE_SQL;
		return $BASE_SQL;
	}
	
	
	
}
?>