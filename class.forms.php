<?php
/*! \class Record class.forms.php "class.forms.php"
 *  \brief This class is used to render the form elements.
 */
class Forms {
	/*! \fn obj __constructor($DB)
		\brief Forms class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db 	= 	$DB;		
	}
	
	function get_fieldInfo($fieldName) {
		switch($fieldName) {
			case 'FirstName':
			$label = 'First Name';
			break;
			
			case 'LastName':
			$label = 'Last Name';
			break;
			
			case 'DateOfBirth':
			$label = 'Date of Birth';
			break;
			
			case 'Gender':
			$label = 'Gender';
			break;
			
			case 'Email':
			$label = 'Email Address';
			break;
			
			case 'MaritalStatus':
			$label = 'Marital Status';
			break;
			
			case 'Education':
			$label = 'Education';
			break;
			
			case 'Occupation':
			$label = 'Occupation';
			break;
			
			case 'Employer':
			$label = 'Employer';
			break;
			
			case 'Phone':
			$label = 'Phone';
			break;
			
			case 'Street_1':
			$label = 'Address';
			break;
			
			case 'City':
			$label = 'City';
			break;
			
			case 'State':
			$label = 'State';
			break;
			
			case 'Postal':
			$label = 'Postal Code';
			break;
			
			case 'Country':
			$label = 'Country';
			break;
			
			case 'Photos':
			$label = 'Photos';
			break;
			
			case 'HearAboutUs':
			$label = 'Source';
			break;
			
			default:
			$sql = "SELECT * FROM Questions WHERE MappedField='".$fieldName."'";
			$snd = $this->db->get_single_result($sql);
			if(isset($snd['empty_result'])) {
				$pr_sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_mappedField='".$fieldName."'";
				$pr_snd = $this->db->get_single_result($pr_sql);
				$label = $pr_snd['PrefQuestion_text'];
			} else {
				$label = $snd['Questions_text'];
			}
			break;
			
		}
		return $label;
	}
	
	function updateFormViews($FormID) {
		$sql = "SELECT * FROM CompanyForms WHERE FormID='".$FormID."'";
		$snd = $this->db->get_single_result($sql);
		$cViews = $snd['FormViews'];
		$nViews = $cViews + 1;
		
		$upd_sql = "UPDATE CompanyForms SET FormViews='".$nViews."' WHERE FormID='".$FormID."'";
		//echo $upd_sql;
		$this->db->mysqli->query($upd_sql);		
	}
	
	function updateFormSubmits($FormID) {
		$sql = "SELECT * FROM CompanyForms WHERE FormID='".$FormID."'";
		$snd = $this->db->get_single_result($sql);
		$cViews = $snd['FormSubmits'];
		$nViews = $cViews + 1;
		
		$upd_sql = "UPDATE CompanyForms SET FormSubmits='".$nViews."' WHERE FormID='".$FormID."'";
		//echo $upd_sql;
		$this->db->mysqli->query($upd_sql);		
	}
	
	function render_fullForm($form_id, $PERSON_ID) {
		$sql = "SELECT * FROM CompanyForms_Fields WHERE FormID='".$form_id."' ORDER BY QuestionOrder ASC";
		$dta = $this->db->get_multi_result($sql);
		foreach($dta as $field) {
			//echo $field['QuestionID']."<br>\n";						
			$this->render_formElement($field['QuestionID'], $field['DefaultValue'], $field['isRequired'], $PERSON_ID, $field['isHidden']);
		}
	}
	
	
	function render_formElement($fieldName, $value, $required, $person_id, $hidden=0) {
		$label = $this->get_fieldInfo($fieldName);
		if($person_id != 0) {
			$psql = "SELECT * FROM Persons WHERE Person_id='".$person_id."'";
			//echo $psql;
			$psnd = $this->db->get_single_result($psql);
			$readonly = true;
		} else {
			$readonly = false;
		}
		
		if($hidden == 0):
			switch($fieldName) {
				case 'FirstName':			
				$value = $psnd['FirstName'];
				$this->form_textField($fieldName, $label, $value, $required, $readonly);
				break;
				
				case 'LastName':
				$value = $psnd['LastName'];
				$this->form_textField($fieldName, $label, $value, $required, $readonly);
				break;
				
				case 'Employer':
				$value = $psnd['Employer'];
				$this->form_textField($fieldName, $label, $value, $required);
				break;
				
				case 'Gender':
				$value = $psnd['Gender'];
				$options = array(
					array(
						'text'	=>	'Male',
						'value'	=>	'M'
					),
					array(
						'text'	=>	'Female',
						'value'	=>	'F'
					)				
				);
				$this->form_radioField($fieldName, $label, $value, $required, $options);
				break;
				
				case 'DateOfBirth':
				if($person_id != 0) {
					$from 	= new DateTime($psnd['DateOfBirth']);
					$to   	= new DateTime('today');
					$age 	= $from->diff($to)->y;
					$value = $age;
				} else {
					$value = '';
				}
				$this->form_dobField($fieldName, $label, $value, $required);
				//$this->form_ageField($fieldName, $label, $value, $required);
				break;
				
				case 'Email':
				$value = $psnd['Email'];
				$this->form_emailField($fieldName, $label, $value, $required, $readonly);
				break;
				
				case 'Education':
				$value = $psnd['Education'];
				$sql = "SELECT * FROM DropDown_Education";
				$snd = $this->db->get_multi_result($sql);
				foreach($snd as $dta):
					$options[] = array(
						'text'	=> 	$dta['Education'],
						'value'	=>	$dta['Education']
					);
				endforeach;
				$this->form_selectField($fieldName, $label, $value, $required, $options);			
				break;
				
				case 'MaritalStatus':
				if($value == ''):
					$value = $psnd['MaritalStatus'];
				endif;
				$sql = "SELECT * FROM DropDown_MaritalStat";
				$snd = $this->db->get_multi_result($sql);
				foreach($snd as $dta):
					$options[] = array(
						'text'	=> 	$dta['Mstat'],
						'value'	=>	$dta['Mstat']
					);
				endforeach;	
				$this->form_radioField($fieldName, $label, $value, $required, $options);		
				break;
				
				case 'Phone':
				if($person_id != 0) {
					$pSQL = "SELECT * FROM Phones WHERE Person_id='".$person_id."' AND isPrimary='1' LIMIT 1";
					//echo $pSQL;
					$pSND = $this->db->get_single_result($pSQL);
					$value = $pSND['Phone_number'];
				}
				$this->form_phoneField($fieldName, $label, $value, $required);
				break;
				
				case 'Photos':
				$this->form_photoUpload();
				break;	
				
				case 'Postal':
				if($person_id != 0) {
					$aSQL = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' LIMIT 1";
					$aSND = $this->db->get_single_result($aSQL);
					$value = $aSND['Postal'];
				}
				$this->form_textField($fieldName, 'Postal Code', $value, $required);
				break;	
				
				case 'HearAboutUs':
				if (isset($_GET['SID'])) {
					$ls_sql = "SELECT * FROM DropDown_LeadSource WHERE SourceID='".$_GET['SID']."' order by Source_name";
					$ls_snd = $this->db->get_single_result($ls_sql);
					if(isset($ls_snd['empty_result'])) {
						$currentSource = $psnd['HearAboutUs'];
						$readonly = false;
						$this->form_sourceSelect($currentSource, $readonly, $required);
					} else {
						$currentSource = $ls_snd['Source_name'];
						$readonly = true;
						$this->form_hiddenField($fieldName, $label, $currentSource, $required);
					}
				} else {
					$currentSource = $psnd['HearAboutUs'];
					$readonly = false;
					$this->form_sourceSelect($currentSource, $readonly, $required);
				}
				//$this->form_sourceSelect($currentSource, $readonly);
				break;
				
				case 'Country':
				if($person_id != 0) {
					$aSQL = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' LIMIT 1";
					$aSND = $this->db->get_single_result($aSQL);
					$value = $aSND['Country'];
				} else {
					$value = 'US';
				}
				$sql = "SELECT * FROM SOURCE_Contries";
				$snd = $this->db->get_multi_result($sql);
				foreach($snd as $dta):
					$options[] = array(
						'text'	=> 	$dta['Country'],
						'value'	=>	$dta['CountryCode']
					);				
				endforeach;
				$this->form_selectField($fieldName, $label, $value, $required, $options);			
				break;	
				
				case 'State':
				if($person_id != 0) {
					$aSQL = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' LIMIT 1";
					$aSND = $this->db->get_single_result($aSQL);
					$value = $aSND['State'];
				}			
				$sql = "SELECT * FROM SOURCE_States WHERE CountryCode='US'";
				$snd = $this->db->get_multi_result($sql);
				foreach($snd as $dta):
					$options[] = array(
						'text'	=> 	$dta['State'],
						'value'	=>	$dta['StateCode']
					);				
				endforeach;
				$this->form_selectField($fieldName, $label, $value, $required, $options);			
				break;
				
				case 'City':
				if($person_id != 0) {
					$aSQL = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' LIMIT 1";
					$aSND = $this->db->get_single_result($aSQL);
					$value = $aSND['City'];
				}
				$this->form_textField($fieldName, 'City', $value, $required);
				break;		
				
				default:
				//$this->form_textField($fieldName, $label, $value, $required);
				$sql = "SELECT * FROM Questions WHERE MappedField='".$fieldName."'";
				//echo $sql."<br>\n";
				$snd = $this->db->get_single_result($sql);
				//print_r($snd);
				if(isset($snd['empty_result'])) {
					$pr_sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_mappedField='".$fieldName."'";
					$pr_snd = $this->db->get_single_result($pr_sql);
					$label = $pr_snd['PrefQuestion_text'];
					
					if($person_id != 0) {
						$prefSQL = "SELECT * FROM PersonsPrefs WHERE Person_id='".$person_id."'";
						//echo $prefSQL;
						$prefSND = $this->db->get_single_result($prefSQL);
					} else {
						$prefSND = array();
					}
					if ($pr_snd['PrefQuestions_mappedMatchField'] == 'age_floor') {
						if($person_id != 0) {
							if($value == ''):
								$value = $prefSND['prefQuestion_age_floor'];
							endif;
						} else {
							$value = '';
						}
						$this->form_ageRangeField($field, $label, $value, $required);					
					} else {
						//$value = explode("|", $prefSND[$fieldName]);
						//print_r($value);
						//echo "FIELD:".$fieldName."<br>\n";
						/*
						if($person_id != 0) {
							if($value == ''):
								$value = $prefSND[$fieldName];
							endif;
						} else {
							$value = '';
						}
						*/
						//echo "FORM DATA THROUGH:".$value."<br>\n";
						$this->form_checkboxField($fieldName, $label, $value, $required, $this->get_fieldOptions($pr_snd['Questions_id']));
					}
				} else {
					if($person_id != 0) {
						if($value == ''):
							$prSQL = "SELECT * FROM PersonsProfile WHERE Person_id='".$person_id."'";
							$prSND = $this->db->get_single_result($prSQL);
							$value = $prSND[$fieldName];
						endif;
					} else {
						$value = '';
					}
									
					switch($snd['QuestionTypes_id']) {
						case '1':
						$this->form_textField($fieldName, $label, $value, $required);
						break;
						
						case '2':
						$this->form_textAreaField($fieldName, $label, $value, $required);
						break;
						
						case '4':				
						$this->form_radioField($fieldName, $label, $value, $required, $this->get_fieldOptions($snd['Questions_id']));
						break;
						
						case '3':				
						$this->form_selectField($fieldName, $label, $value, $required, $this->get_fieldOptions($snd['Questions_id']));
						break;
						
						case '5':
						$this->form_checkboxField($fieldName, $label, $value, $required, $this->get_fieldOptions($snd['Questions_id']));
						break;
						
						case '6':
						$this->form_dateField($fieldName, $label, $value, $required);
						break;
							
						default:
						$this->form_textField($fieldName, $label, $value, $required);
						break;
					}
				}
				break;				
			}
		else:
			//echo "FIELD:".$fieldName."<br>\n";
			$this->form_hiddenField($fieldName, $label, $value, $required);
		endif;
	}
	
	function get_fieldOptions($question_id) {
		$sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='".$question_id."' ORDER BY QuestionsAnswers_order ASC";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$options[] = array(
				'text'	=> 	$dta['QuestionsAnswers_value'],
				'value'	=>	$dta['QuestionsAnswers_value']
			);			
		endforeach;
		return $options;		
	}
	
	function form_sourceSelect($currentSource, $readonly, $required) {
		global $RECORD;
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-form-label col-3">
               	<?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
                Where did you hear about us?
            </label>
            <div class="col-9">
            	<?php if($readonly): ?>
                <div style="padding-top:5px; font-size:1.25em;">
	                <strong><?php echo str_replace("|", " - ", $currentSource)?></strong>
                    <input type="hidden" name="HearAboutUs" id="HearAboutUs" value="<?php echo $currentSource?>" />
                </div>
                <?php else: ?>
                <select class="form-control m-select2" id="HearAboutUs" name="HearAboutUs" style="width:100%;" <?php echo (($required == 1)? 'required':'')?>>
                 <?php echo $RECORD->options_leadSources($values=array($currentSource));?>  
                </select>
                <?php endif; ?>
            </div>
        </div>
        <script>
		<?php if(!$readonly): ?>
		$(document).ready(function(e) {
            $('#HearAboutUs').select2({ theme: "classic" });
        });
		<?php endif; ?>
		</script>
        <?php
	}
	
	function form_photoUpload() {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">Photos</label>
            <div class="col-md-9">
            	<div class="m-dropzone m-dropzone--success" action="/ajax/uploadImage.php" id="m-dropzone-four">
                    <div class="m-dropzone__msg dz-message">
                        <h3 class="m-dropzone__msg-title">
                            Drop files here or click to upload.
                        </h3>
                        <span class="m-dropzone__msg-desc">
                            Only image files are allowed for upload
                        </span>
                    </div>
                </div>
            </div>            
        </div>        
        <script>
		$(document).ready(function(e) {
			setTimeout(function() {
				$('#m-dropzone-four').addClass('dropzone');
				FileDropZone = new Dropzone('#m-dropzone-four', {
					paramName: "file",
					maxFiles: 10,
					maxFilesize: 10,
					acceptedFiles: "image/*,application/pdf,.psd",
					accept: function(e, o) {
						console.log(e);
						console.log(o);
						"justinbieber.jpg" == e.name ? o("Naha, you don't.") : o()
					},
					 init: function() {
						this.on("success", function(file, response) {
							var obj = jQuery.parseJSON(response)
							console.log(obj);
							$('#imagesList').append('<input type="hidden" name="uploadedImages[]" value="'+obj.filename+'" />');							
						})
					}
				});		
			}, 500);
        });
		</script>
        <span id="imagesList"></span>
        <?php
	}
	
	function form_ageRangeField($fieldName, $fieldLabel, $currentValue, $required) {
		//echo "CURRENT:".$currentValue;
		$ageParts = explode("|", $currentValue);
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-4">
                <div class="input-group m-input-group">
	                <span class="input-group-addon">From:</span>
                    <select name="ageRange_value_1" class="form-control m-input" <?php echo (($required == 1)? 'required':'')?>>
                    	<option value=""></option>
                    <?php for($i=25; $i<=99; $i++) {?>                                        
                    	<option value="<?php echo $i?>" <?php echo (($ageParts[0] == $i)?'selected':'')?>><?php echo $i?></option>                        
                    <?php } ?>                    
                    </select>
				</div>                 
            </div>
            <div class="col-md-4">
            	<div class="input-group m-input-group">
	                <span class="input-group-addon">To:</span>
                    <select name="ageRange_value_2" class="form-control m-input" <?php echo (($required == 1)? 'required':'')?>>
                    	<option value=""></option>
					<?php for($i=25; $i<=99; $i++) {?>                                        
                    	<option value="<?php echo $i?>" <?php echo (($ageParts[1] == $i)?'selected':'')?>><?php echo $i?></option>                        
                    <?php } ?>                    
                    </select>
				</div>
            </div>
		</div>		
        <?php
	}
	
	function form_phoneField($fieldName, $fieldLabel, $currentValue, $required) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-9">
            	<div class="input-group m-input-group">
                <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
                <input class="form-control m-input" type="text" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" value="<?php echo $currentValue?>" <?php echo (($required == 1)? 'required':'')?>>
                </div>
            </div>
        </div>
        <?php
	}
	
	function form_emailField($fieldName, $fieldLabel, $currentValue, $required, $readonly) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-9">
            	<div class="input-group m-input-group">
                <span class="input-group-addon">@</span>
                <input class="form-control m-input <?php echo (($readonly)? 'm-input--solid':'')?>" type="email" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" value="<?php echo $currentValue?>" <?php echo (($required == 1)? 'required':'')?> <?php echo (($readonly)? 'readonly="readonly"':'')?>>
                </div>
            </div>
        </div>
        <?php
	}
	
	function form_ageField($fieldName, $fieldLabel, $currentValue, $required) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
                Age
            </label>
            <div class="col-md-3">
            	<div class="input-group">
	            	<input class="form-control m-input <?php echo (($readonly)? 'm-input--solid':'')?>" type="number" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" value="<?php echo $currentValue?>" <?php echo (($required == 1)? 'required':'')?> <?php echo (($readonly)? 'readonly="readonly"':'')?>>
					<span class="input-group-addon">years old</span>
				</div>                                        
            </div>
            <div class="col-md-6">&nbsp;</div>
		</div>            	
		<?php		
	}
	
	function form_dobField($fieldName, $fieldLabel, $currentValue, $required) {
		$cmDate = date("m", $currentValue);
		$cdDate = date("d", $currentValue);
		$cyDate = date("Y", $currentValue);
		?>
        <div class="form-group m-form__group row kiss-row">
            <label style="width:100%" class="col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
			<div class="" style="padding-right:10px">
                <select class="form-control m-input" name="<?php echo $fieldName?>_MM" id="<?php echo $fieldName?>_MM" <?php echo (($required == 1)? 'required':'')?>>
                	<option value=""></option>
                    <option value="01" <?php echo (($cmDate == 1)? 'selected':'')?>>Jan</option>
                    <option value="02" <?php echo (($cmDate == 2)? 'selected':'')?>>Feb</option>
                    <option value="03" <?php echo (($cmDate == 3)? 'selected':'')?>>Mar</option>
                    <option value="04" <?php echo (($cmDate == 4)? 'selected':'')?>>Apr</option>
                    <option value="05" <?php echo (($cmDate == 5)? 'selected':'')?>>May</option>
                    <option value="06" <?php echo (($cmDate == 6)? 'selected':'')?>>Jun</option>
                    <option value="07" <?php echo (($cmDate == 7)? 'selected':'')?>>July</option>
                    <option value="08" <?php echo (($cmDate == 8)? 'selected':'')?>>Aug</option>
                    <option value="09" <?php echo (($cmDate == 9)? 'selected':'')?>>Sept</option>
                    <option value="10" <?php echo (($cmDate == 10)? 'selected':'')?>>Oct</option>
                    <option value="11" <?php echo (($cmDate == 11)? 'selected':'')?>>Nov</option>
                    <option value="12" <?php echo (($cmDate == 12)? 'selected':'')?>>Dec</option>
                </select>
                <span class="m-form__help">Month</span>
            </div>
            <div class="" style="padding-right:10px">
                <select class="form-control m-input" name="<?php echo $fieldName?>_DD" id="<?php echo $fieldName?>_DD" <?php echo (($required == 1)? 'required':'')?>>
                <option value=""></option>
				<?php for($d=1; $d<31; $d++): ?>                	
                    <option value="<?php echo $d?>" <?php echo (($cdDate == $d)? 'selected':'')?>><?php echo $d?></option>                
                <?php endfor; ?>
                </select>
                <span class="m-form__help">Day</span>
            </div>
            <div class="" style="padding-right:10px">
                <select class="form-control m-input" name="<?php echo $fieldName?>_YYYY" id="<?php echo $fieldName?>_YYYY" <?php echo (($required == 1)? 'required':'')?>>
                <option value=""></option>
				<?php for($y=(date("Y") - 18); $y>(date("Y") - 99); $y--): ?>                	
                    <option value="<?php echo $y?>" <?php echo (($cyDate == $y)? 'selected':'')?>><?php echo $y?></option>
                <?php endfor; ?>
                </select>
                <span class="m-form__help">Year</span>
            </div>
        </div>
        <?php		
	}
	
	function form_dateField($fieldName, $fieldLabel, $currentValue, $required) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-3 col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-3">
                <select class="form-control m-input" name="<?php echo $fieldName?>_MM" id="<?php echo $fieldName?>_MM" <?php echo (($required == 1)? 'required':'')?>>
                	<option value=""></option>
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">Mar</option>
                    <option value="04">Apr</option>
                    <option value="05">May</option>
                    <option value="06">Jun</option>
                    <option value="07">July</option>
                    <option value="08">Aug</option>
                    <option value="09">Sept</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
                <span class="m-form__help">Month</span>
            </div>
            <div class="col-md-3">
                <select class="form-control m-input" name="<?php echo $fieldName?>_DD" id="<?php echo $fieldName?>_DD" <?php echo (($required == 1)? 'required':'')?>>
                <option value=""></option>
				<?php for($d=1; $d<31; $d++): ?>                	
                    <option value="<?php echo $d?>"><?php echo $d?></option>                
                <?php endfor; ?>
                </select>
                <span class="m-form__help">Day</span>
            </div>
            <div class="col-md-3">
                <select class="form-control m-input" name="<?php echo $fieldName?>_YYYY" id="<?php echo $fieldName?>_YYYY" <?php echo (($required == 1)? 'required':'')?>>
                <option value=""></option>
				<?php for($y=date("Y"); $y>(date("Y") + 10); $y++): ?>                	
                    <option value="<?php echo $y?>"><?php echo $y?></option>                
                <?php endfor; ?>
                </select>
                <span class="m-form__help">Year</span>
            </div>
        </div>
        <?php
		
	}
	
	function form_hiddenField($fieldName, $fieldLabel, $currentValue, $required, $readonly=false) {
		?><input class="form-control m-input <?php echo (($readonly)? 'm-input--solid':'')?>" type="hidden" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" value="<?php echo $currentValue?>" <?php echo (($required == 1)? 'required':'')?> <?php echo (($readonly)? 'readonly="readonly"':'')?> ><?php			
	}
	
	function form_textField($fieldName, $fieldLabel, $currentValue, $required, $readonly=false, $break=array(3, 9)) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-<?php echo $break[0]?> col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-<?php echo $break[1]?>">
                <input class="form-control m-input <?php echo (($readonly)? 'm-input--solid':'')?>" type="text" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" value="<?php echo $currentValue?>" <?php echo (($required == 1)? 'required':'')?> <?php echo (($readonly)? 'readonly="readonly"':'')?> >
            </div>
        </div>
        <?php	
	}
	
	function form_textAreaField($fieldName, $fieldLabel, $currentValue, $required, $break=array(3, 9)) {
		?>
        <div class="form-group m-form__group row kiss-row">
            <label class="col-md-<?php echo $break[0]?> col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-<?php echo $break[1]?>">
            	<textarea class="form-control m-input" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" style="height:88px;" <?php echo (($required == 1)? 'required':'')?>><?php echo $currentValue?></textarea>
            </div>
        </div>
        <?php	
	}
	
	function form_radioField($fieldName, $fieldLabel, $currentValue, $required, $options=array(), $break=array(3, 9)) {		
		?>
        <div class="m-form__group form-group row kiss-row">
            <label class="col-md-<?php echo $break[0]?> col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-<?php echo $break[1]?>">
                <div class="m-radio-list">
                	<?php foreach($options as $option): ?>
                    <label class="m-radio">
                        <input type="radio" name="<?php echo $fieldName?>" value="<?php echo $option['value']?>" <?php echo (($currentValue == $option['value'])? 'checked':'')?> />
                        <?php echo $option['text']?>
                        <span></span>
                    </label>
                    <?php endforeach; ?>                    
                </div>
            </div>
        </div>
        <?php
	}
	
	function form_checkboxField($fieldName, $fieldLabel, $currentValue, $required, $options=array(), $break=array(3, 9)) {
		global $INCLUDE_EMPTY_CHECKBOX;
		//echo "CURRENT VALUE: ".$currentValue."<br>\n";
		?>
        <div class="m-form__group form-group row kiss-row">
            <label class="col-md-<?php echo $break[0]?> col-form-label">
               	<?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
                <?php echo $fieldLabel?>
            </label>
            <div class="col-md-<?php echo $break[1]?>">
            	<div class="m-checkbox-list">
                	<?php foreach($options as $option): ?>
                    <label class="m-checkbox">
                        <input type="checkbox" name="<?php echo $fieldName?>[]" value="<?php echo $option['value']?>" <?php echo ((in_array($option['value'], explode("|", $currentValue))? 'checked':''))?> >
                        <?php echo $option['text']?>
                        <span></span>
                    </label>
                    <?php endforeach; ?>  
                    <?php if($INCLUDE_EMPTY_CHECKBOX):?>
                    <label class="m-checkbox">
                        <input type="checkbox" name="<?php echo $fieldName?>[]" value="" <?php echo ((in_array("", explode("|", $currentValue))? 'checked':''))?> >
                        No Preference
                        <span></span>
                    </label>
                    <?php endif; ?>                  
                </div>
            </div>
        </div>
        <?php
	}
	
	function form_selectField($fieldName, $fieldLabel, $currentValue, $required, $options=array(), $break=array(3, 9)) {
		?>
        <div class="m-form__group form-group row kiss-row">
            <label class="col-md-<?php echo $break[0]?> col-form-label">
                <?php if($required):?>
                <span class="m--font-danger">*</span>
                <?php endif; ?>
				<?php echo $fieldLabel?>
            </label>
            <div class="col-md-<?php echo $break[1]?>">
                <select class="form-control m-input" name="<?php echo $fieldName?>" id="<?php echo $fieldName?>" <?php echo (($required == 1)? 'required':'')?>>
                <option value=""></option>
				<?php foreach($options as $option): ?>                	
                    <option value="<?php echo $option['value']?>" <?php echo (($currentValue == $option['value'])? 'selected':'')?>><?php echo $option['text']?></option>                
                <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
	}
	
	function updateFullFormViews($form_id) {
		$ip = $this->getUserIP();
		$sql = "SELECT count(*) as count FROM CompanyFormsViews	WHERE Form_id='".$form_id."' AND ViewIP='".$ip."' AND (ViewDate >= '".(time() - 86400)."' AND ViewDate <= '".time()."')";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		$found = $snd['count'];
		if($found == 0) {
			$i_sql = "INSERT INTO CompanyFormsViews (Form_id, ViewDate, ViewIP) VALUES('".$form_id."','".time()."','".$this->getUserIP()."')";
			$i_snd = $this->db->mysqli->query($i_sql);
		}
	}
	
	function getUserIP()
	{
		// Get real visitor IP behind CloudFlare network
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
				  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
				  $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
	
		if(filter_var($client, FILTER_VALIDATE_IP))
		{
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
		{
			$ip = $forward;
		}
		else
		{
			$ip = $remote;
		}
		return $ip;
	}
	
	function get_formViews($form_id, $startEpoch=0, $endEpoch=99) {
		if($endEpoch == 99) {
			$endEpoch = time();
		}
		$fv_sql = "SELECT count(*) as count FROM CompanyFormsViews WHERE Form_id='".$form_id."' AND (ViewDate >= '".$startEpoch."' AND ViewDate <= '".$endEpoch."')";
		//echo $fv_sql."<br>\n";
		$fv_snd = $this->db->get_single_result($fv_sql);
		$form_views = $fv_snd['count'];
		return $form_views;		
	}
	
	function get_formSubmits($form_id, $startEpoch=0, $endEpoch=99) {
		$form_sql 		= "SELECT * FROM CompanyForms WHERE FormID='".$form_id."'";
		$form_data 		= $this->db->get_single_result($form_sql);
		//$FORM_KEYS[]	= $form_data['FormCallString'];
	
		if($endEpoch == 99) {
			$endEpoch = time();
		}
		$fs_sql = "SELECT Persons.Person_id FROM Persons INNER JOIN PersonForms ON PersonForms.Person_id=Persons.Person_id AND PersonForms.Form_id='".$form_data['FormCallString']."' AND (PersonForms.FormSubmitted >= '".date("Y-m-d", $startEpoch)." 00:00:00' AND PersonForms.FormSubmitted <= '".date("Y-m-d", $endEpoch)." 23:59:59')  GROUP BY Persons.Person_id";
		//echo $fs_sql."<br>\n";
		$fs_snd = $this->db->get_multi_result($fs_sql, true);
		$form_submits = $fs_snd;
		return $form_submits;		
	}
	
}