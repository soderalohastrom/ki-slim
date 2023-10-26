<?php
/*! \class Datatable class.datatables.php "class.datatables.php"
 *  \brief This class is used to draw and render the actualy page.
 */
class Datatable {
	/*! \fn obj __constructor($DB)
		\brief Datatable class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD, $REPORTS='-1', $ENC='-1') {
		$this->db 			= $DB;
		$this->record		= $RECORD;
		$this->reports		= $REPORTS;
		$this->encryption	= $ENC;
		$this->skipFields	= array(
			'Email',
			'Phone_number',
			'LastNoteAction',
			'DateUpdated',
			'Persons.LastIntroDate',
			'Persons_Color_Span',

		);
	}
	
	public array $skipFields;
	
	/*! \fn obj getCustomLeadFields()
		\brief Gets the lead fields for a data table render.
		\return array
	*/
	function getCustomLeadFields() {		
		$return = array(
			array(
				'field'	=>	'RecordAge',
				'label'	=>	'Age',
				'width'	=>	'25'
			),
			array(
				'field'	=>	'Gender',
				'label'	=>	'Gender',
				'width'	=>	25
			),
			$this->customProfileField(621, 35),
			$this->customProfileField(622, 75),
			$this->customProfileField(631, 125),
			$this->customProfileField(1713, 75),
			$this->customProfileField(1719, 150),
			array(
				'field'	=>	'PhoneNumber',
				'label'	=>	'Phone',
				'width'	=>	150
			),
			array(
				'field'	=>	'DateUpdatedDisplay',
				'label'	=>	'Last Edit',
				'width'	=>	100
			),
			array(
				'field'	=>	'HearAboutUs',
				'label'	=>	'Source',
				'width'	=>	125
			),
			/*
			array(
				'field'	=>	'LeadAge',
				'label'	=>	'Lead Age',
				'width'	=>	50
			),
			*/
			//$this->customProfileField(1522, 75),
			//$this->customProfileField(660, 100)
		);
		return $return;
	}
	
	function makeCustomLeadFields($user_id, $configField) {
		$sql = "SELECT * FROM UsersCustomTables WHERE user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		if($snd[$configField] != ''):
			$config = json_decode($snd[$configField], true);
			foreach($config as $setting):
				$fieldParts = explode(".", $setting['field']);
				if(count($fieldParts) > 1) {
					$fieldSQL = $fieldParts[1];	
				} else {
					$fieldSQL = $fieldParts[0];
				}
				
				switch($fieldSQL) {
					case 'DateOfBirth':
					$field 	= 	'RecordAge';
					$label 	=	'Age';
					$width 	=	50;
					break;
					
					case 'Offices_id':
					$field	=	'office_Name';
					$label	=	'Location';
					$width 	=	100;
					break;
					
					case 'DateUpdated':
					$field	=	'DateUpdatedDisplay';
					$label	=	'Last Edit';
					$width 	=	150;				
					break;
					
					case 'LeadStages_id':
					$field	=	'LeadStages_name';
					$label	=	'Lead Stage';
					$width 	=	100;				
					break;
					
					case 'Phone_number':
					$field	=	'PhoneNumber';
					$label	=	'Phone';
					$width 	=	100;
					break;
					
					case 'Assigned_userID':
					$field	=	'Salesperson';
					$label	=	'Market Director';	
					$width 	=	100;			
					break;
					
					case 'Matchmaker_id':
					$field	=	'Matchmaker';
					$label	=	'Relationship Manager';
					$width	=	100;
					break;

					case 'Matchmaker2_id':
						$field	=	'NetworkDeveloper';
						$label	=	'Network Developer';
						$width	=	100;
					break;
					
					case 'DateCreated':
					$field	=	'DateCreatedDisplay';
					$label	=	'Created';
					$width	=	100;
					break;
					
					case 'PersonsTypes_id':
					$field		=	'PersonsTypes_text';
					$label		=	'Type';
					$width		=	135;
					$template	=	'<span class="m-badge m-badge--{{PersonsTypes_color}} m-badge--wide">{{PersonsTypes_text}}</span>';
					break;
					
				    case 'Persons_Color_Span':
					   $field		=	'Persons_Color_Span';
					   $label		=	'Flag';
					   $width		=	135;
					   $template	=	'{{Persons_Color_Span}}';
					 break;
	
					case 'prQuestion_1719':
					$field		=	'prQuestion_1719';
					$label		=	'Prime Note';
					$width		=	250;
					$template	=	'<div class="truncate" data-toggle="m-tooltip" title="{{PrimeNoteBody}}">{{prQuestion_1719}}</div>';
					break;
					
					default:
					$field	=	$fieldSQL;
					$label	=	$setting['title'];
					$width 	=	100;
					break;	
				}
				
				if(isset($template)) {
					$columnConfig[] = array(
						'field'		=>	$field,
						'label'		=>	$label,
						'width'		=>	$width,
						'template'	=>	$template				
					);
				} else {
					$columnConfig[] = array(
						'field'	=>	$field,
						'label'	=>	$label,
						'width'	=>	$width				
					);
				}
				
				unset($field);
				unset($label);
				unset($width);
				unset($template);
			endforeach;
			return $columnConfig;
		else:
			return false;
		endif;		
	}
	
	/*! \fn obj getLeadAssignFields()
		\brief Gets the lead assign fields for a data table render.
		\return array
	*/
	function getLeadAssignFields() {
		$return = array(
			array(
				'field'	=>	'DateCreated',
				'label'	=>	'Created',
				'width'	=>	100
			),
			array(
				'field'	=>	'Gender',
				'label'	=>	'Gender',
				'width'	=>	50
			),			
			array(
				'field'	=>	'RecordAge',
				'label'	=>	'Age',
				'width'	=>	'50'
			),
			array(
				'field'	=>	'prQuestion_631',
				'label'	=>	'Income',
				'width'	=>	120
			),
			array(
				'field'	=>	'prQuestion_621',
				'label'	=>	'Height',
				'width'	=>	75
			),
			array(
				'field'	=>	'prQuestion_622',
				'label'	=>	'Weight',
				'width'	=>	100
			),
			array(
				'field'	=>	'PhoneNumber',
				'label'	=>	'Phone',
				'width'	=>	100
			),
			array(
				'field'	=>	'Postal',
				'label'	=>	'Postal',
				'width'	=>	100
			),
			array(
				'field'	=>	'HearAboutUs',
				'label'	=>	'Source',
				'width'	=>	100
			),			
			array(
				'field'	=>	'Marketer',
				'label'	=>	'Assigned',
				'width'	=>	100
			),
			array(
				'field'		=>	'LeadStages_name',
				'label'		=>	'Lead Stage',
				'width'		=>	125,
				'template'	=>	'<span class="m-badge m-badge--metal m-badge--wide" style="background-color:{{LeadStage_hex}};">{{LeadStages_name}}</span>'
			),						
			//$this->customProfileField(664, 50),
			//$this->customProfileField(1522),
			//$this->customProfileField(660)
		);
		return $return;
	}
	
	/*! \fn obj getCustomMemberFields()
		\brief Gets the custom member/client fields for a datatable render.
		\return array
	*/
	function getCustomMemberFields() {
		$return = array(
			array(
				'field'	=>	'RecordAge',
				'label'	=>	'Age',
				'width'	=>	'50'
			),
			array(
				'field'	=>	'Gender',
				'label'	=>	'Gender',
				'width'	=>	50
			),
			$this->customProfileField(664, 50),
			array(
				'field'		=>	"PersonsTypes_text", 
				'width'		=>	135,
				'label'		=>	"Type",
				'template'	=>	'<span class="m-badge m-badge--{{PersonsTypes_color}} m-badge--wide">{{PersonsTypes_text}}</span>'
			),
		    array(
				'field'		=>	"Persons_Color_Span", 
				'width'		=>	135,
				'label'		=>	"Status",
				'template'	=>	'{{Persons_Color_Span}}'
					 
			),
			array(
				'field'	=>	'Matchmaker',
				'label'	=>	'Matchmaker',
				'width'	=>	125
			),
			array(
				'field'	=>	'LastIntro',
				'label'	=>	'Last Intro',
				'width'	=>	'100'
			),												
			$this->customProfileField(657),
			$this->customProfileField(676),
			$this->customProfileField(677),
			//$this->customProfileField(1062),
			//$this->customProfileField(1522)		
		);
		return $return;
	}
	
	/*! \fn obj getCustomClientFields()
		\brief Gets the custom member/client fields for a datatable render.
		\return array
	*/
	function getCustomClientFields() {
		$return = array(
			array(
				'field'	=>	'RecordAge',
				'label'	=>	'Age',
				'width'	=>	'50'
			),
			array(
				'field'	=>	'Gender',
				'label'	=>	'Gender',
				'width'	=>	50
			),
			$this->customProfileField(664, 50),
			array(
				'field'	=>	'PersonsTypes_text',
				'label'	=>	'Type',
				'width'	=>	125,
				'template'	=>	'<span class="m-badge m-badge--{{PersonsTypes_color}} m-badge--wide">{{PersonsTypes_text}}</span>'
			),
			array(
				'field'		=>	"Persons_Color_Span", 
				'width'		=>	135,
				'label'		=>	"Status",
				'template'	=>	'{{Persons_Color_Span}}'
			),
			array(
				'field'	=>	'Matchmaker',
				'label'	=>	'Matchmaker',
				'width'	=>	125
			),
			array(
				'field'	=>	'LastIntro',
				'label'	=>	'Last Intro',
				'width'	=>	'100'
			),												
			$this->customProfileField(657),
			$this->customProfileField(676),
			$this->customProfileField(677),
			//$this->customProfileField(1062),
			//$this->customProfileField(1522)		
		);
		return $return;
	}
	
	/*! \fn obj getCustomSearchFields()
		\brief Gets the custom search fields for a datatable render.
		\return array
	*/
	function getCustomSearchFields() {
		$return = array(
			array(
				'field'	=>	'RecordAge',
				'label'	=>	'Age',
				'width'	=>	50
			),
			array(
				'field'	=>	'Gender',
				'label'	=>	'Gender',
				'width'	=>	50
			),
			$this->customProfileField(664, 35, NULL, 'Rating'),
			array(
				'field'	=>	'PersonsTypes_text',
				'label'	=>	'Type',
				'width'	=>	125
			),
			array(
				'field'	=>	'Matchmaker',
				'label'	=>	'Matchmaker',
				'width'	=>	100
			),
			array(
				'field'	=>	'Marketer',
				'label'	=>	'Marketer',
				'width'	=>	100
			),						
			//$this->customProfileField(677, 150, 'ContractEnd', 'Contract End'),			
			array(
				'field'	=>	'City',
				'label'	=>	'City',
				'width'	=>	150
			),
			array(
				'field'	=>	'State',
				'label'	=>	'State',
				'width'	=>	75
			)		
		);
		return $return;
	}
	
	/*! \fn obj customProfileField($qid, $width, $AliasField, $alisaLabel)
		\brief gets the data table paramaters fora custom question.
		\param int $qid ID of the question 
		\param int $width width of the table column
		\param str $AliasField aliased filed name for the column
		\param str $aliasLabel aliased label for the column in the table
		\return array
	*/
	function customProfileField($qid, $width=150, $aliasedField=NULL, $aliasLabel=NULL) {
		$sql = "SELECT * FROM Questions WHERE Questions_id='".$qid."'";
		$snd = $this->db->get_single_result($sql);
		if($aliasedField != NULL) {
			$alias = $aliasedField;
		} else {
			$alias = $snd['MappedField'];
		}
		
		if($aliasLabel != NULL) {
			$label = $aliasLabel;
		} else {
			$label = $snd['Questions_text'];
		}
		$fieldConfig = array(
			'field'	=>	$alias,
			'label'	=>	$label,
			'width'	=>	$width
		);
		return $fieldConfig;
	}
	
	/*! \fn obj customProfileFieldSelect($qid)
		\brief gets the data select paramaters for a custom question.
		\param int $qid ID of the question 
		\return array
	*/
	function customProfileFieldSelect($qid) {
		$sql = "SELECT * FROM Questions INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions_id='".$qid."'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		if($snd['QuestionTypes_id'] == '6') {
			//$return = "FROM_UNIXTIME(".$snd['MappedField'].", '%Y-%m-%d') as ".$snd['MappedField'];
			//$return = "IFNULL(FROM_UNIXTIME(".$snd['MappedField'].", '%Y-%m-%d'), 'NA') as ".$snd['MappedField'];
			$return = "IF(".$snd['MappedField']." IS NULL or ".$snd['MappedField']." = '0' or ".$snd['MappedField']." = '1', '', FROM_UNIXTIME(".$snd['MappedField'].", '%Y-%m-%d')) as ".$snd['MappedField']; 
		} else {
			$return = $snd['MappedField'];
		}
		return $return;
	}
	
	/*! \fn obj render_datatable(($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false)
		\brief render and draw the datatable
		\param str 	$div_id	ID of the table when renders
		\param str 	$title 	Title to be diplayed on the table
		\param str 	$url	URL the SQL will be sent to for processing
		\param str 	$sql	SQL to bew sent to the database
		\param obj 	$fields	fields used in the columns of the table
		\param str 	$id_field	field containing the unique identifier for this list
		\param str 	$default_sort	default sort field for this table view
		\param str 	$default_sort_direction default sort direction for tabgle
		\param int 	$defaultPageSize default page size for thable view
		\param bool $onDone code to execute upon completion of the data loading
		\param bool	$showChecks if show checkboxes on he far left of each row
		\return HTML
	*/
	function render_datatable($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false, $webstorage='false', $cookies='true') {
		global $GROUP_ID;
		$default_sort_idx = '';
		$default_sort_dir = '';
		if($showChecks) {
			$fieldConfig[] = array(
				'field'		=> "Person_id",
				'title'		=> "#",
				'locked'	=> array('left' => 'xs'),
				'sortable'	=> false,
				'width'		=> 40,
				'selector'	=> array('class' => 'm-checkbox--solid m-checkbox--brand')
			);
		}
		$fieldConfig[] = array(
			'field'		=>	"LastName", 
			'width'		=>	200,
			'title'		=>	"Record", 
			'overflow'	=>	"visible",
			'locked' 	=>	array('left' => 'xs'),
			'sortable'	=> 	true,
			'textAlign'	=>	"center",			
			'template'	=>	'<div class="m-card-user m-card-user--sm" data-id="{{Person_id}}"><div class="m-card-user__pic" style="background-image:url({{PersonsImages_path}}); background-size:cover;"><a href="javascript:;" class="image-library-link"><img src="/assets/app/media/img/users/filler.png" class="m--img-rounded m--marginless" alt="photo"></a></div><div class="m-card-user__details" style="padding-left:5px;"><span class="m-card-user__name"> <i class="la la-clipboard m--font-warning {{isOpenRecord}}" title="Open Record"></i> <a href="javascript:;" class="m-link m-link--state m-link--pending person_link">{{FullName}}</a></span><span class="m-card-user__email">{{City}}, {{State}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a href="/profile/{{PID}}" target="_blank" class="m-link" style="color:#7b7e8a;"><i class="la la-external-link-square"></i></a></div></span></div>'						
		);
		foreach($fields as $field_idx=>$field):
			if (isset($field['template'])) {
				$fieldConfig[] = array(
					'field'		=> $field['field'],
					'title' 	=> $field['label'],
					'width'		=> $field['width'],
					'template'	=>	$field['template'],
					'overflow'	=> 'hidden'
				);				
			} else {
				$fieldConfig[] = array(
					'field'		=> $field['field'],
					'title' 	=> $field['label'],
					'width'		=> $field['width'],
					'overflow'	=> 'hidden'
				);
			}
			if(in_array($div_id, array('myleadsTable', 'myclientsTable')) && $field['field'] == 'DateCreatedDisplay' && $default_sort == 'DateCreated') {
				$default_sort_idx = 'DateCreatedDisplay';
				$default_sort_dir = $default_sort_direction;
			}
		endforeach;		
		ob_start();
		?>
        <div class="m-portlet m-portlet--head-sm m-portlet--mobile " id="datatable-portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text"> <?php echo $title?></h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <?php if($this->reports != '-1'): ?>
                        <li class="m-portlet__nav-item">
                            <div class="input-group" id="filterTableNotice" style="display:<?php echo (($_SESSION['filterTable'] != '')? 'block':'none')?>;">
                                <div class="m-dropdown m-dropdown--down m-dropdown--inline m-dropdown--align-left" data-dropdown-toggle="click" data-dropdown-persistent="true">
                                    <a href="#" class="m-dropdown__toggle btn btn-info dropdown-toggle">
                                        <i class="fa fa-filter"></i> Filtered Table
                                    </a>
                                    <div class="m-dropdown__wrapper">
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content" id="dropDown_filterOptions">
                                                    <em>This table is currently being filters and does not show all of the records available for view.</em>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                    
                            </div>	
                		</li>
                        <?php endif; ?>
                        
                        <?php if(in_array($div_id, array('myleadsTable', 'myclientsTable'))):?>
                        <li class="m-portlet__nav-item">
                        	<div class="m-form__group form-group" style="margin-top:15px;">
                                <div class="m-checkbox-list">
                                    <label class="m-checkbox">
                                        <input type="checkbox" id="includeShares" value="1" <?php echo (($_COOKIE['includeShares'] != '')? 'checked':'')?>>
                                        Show Shared Records
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                        <li class="m-portlet__nav-item">
                        	<div class="input-group">
                            	<span class="input-group-addon" data-toggle="m-tooltip" title="First Name, Last Name and Email Address search"><i class="flaticon-search-1"></i>&nbsp;Table Search</span>
                            	<input type="text" class="form-control m-input" id="tableSearch" />                                                                        
                           	</div>
                        </li>
                        <?php if($this->reports != '-1'): ?>
                        	<?php //echo "GROUP_ID: ".$GROUP_ID; ?>
                        	<?php if(($GROUP_ID == 0)): ?>                            
                        <li class="m-portlet__nav-item">
                    		<button class="btn btn-default" id="button-datatable-modal-fields" data-toggle="modal" data-target="#tableFieldsConfigModal" title="Configure Table Columns"><i class="fa fa-gears"></i></button>	
                            <button class="btn btn-default" id="button-datatable-modal-filters" data-toggle="modal" data-target="#tableFilterModal" title="Configure Table Filters"><i class="fa fa-filter"></i></button>		
                		</li>
                        	<?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body">
                <!--begin: Datatable -->
                <div id="SearchArea" style="display:none;">SEARCH AREA</div>
                <div class="m_datatable" id="<?php echo $div_id?>"></div>
                <!--end: Datatable -->
            </div>
        </div>
        
        <div class="modal fade" id="imageLibaryPreviewModal" role="dialog" aria-labelledby="imageLibaryPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageLibaryPreviewModalLabel">Record Photo Library</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">   

                    </div>                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>                    
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="tableFieldsConfigModal" role="dialog" aria-labelledby="tableFieldsConfigModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tableFieldsConfigModalLabel">Table Configuration</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    	<form id="customTableColums">
                        <input type="hidden" name="customTableID" value="<?php echo $div_id?>" />
                        <input type="hidden" name="customTableUser" value="<?php echo $_SESSION['system_user_id']?>" />
                    	<div class="row">
                        	<div class="col-lg-6">
                            	<h4>Available Fields</h4>
                                <div class="row" style="margin-bottom:10px;">
                                    <div class="col-4">&nbsp;</div>
                                    <div class="col-8">
                                        <div class="input-group m-input-group m-input-group--pill">
                                            <span class="input-group-addon" id="basic-addon1" title="search available fields">
                                                <i class="la la-search"></i>
                                            </span>
                                            <input type="text" class="form-control m-input" id="fieldSearch" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div id="table-field-options" class="m-scrollable" data-scrollable="true" data-max-height="500">
                            	<?php $this->renderFieldList($div_id); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                            	<h4>Current Table Columns</h4>                                                                
                            	<?php $this->renderColumnList($div_id); ?>                                                           
                            </div>
						</div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="button-save-config">Save Table Config</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="tableFilterModal" role="dialog" aria-labelledby="tableFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tableFilterModalLabel">Table Filtering</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                                            
                                <div class="form-group m-form__group">
                                    <label>Filter on Field:</label>
                                    <div class="input-group">											
                                        <select name="filterFieldSelect" id="filterFieldSelect" class="form-control m-input">
                                            <option value=""></option>
                                            <?php echo $this->renderFiltersSelect($div_id)?>
                                        </select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-secondary" type="button">
                                                Add Filter <i class="fa fa-plus"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <span class="m-form__help">
                                        Select field you want to filter.
                                    </span>
                                </div>
                                
                            </div>
                        </div>
                        <ul class="list-group" id="filterListStack">
                        <?php echo $this->preRenderFilters()?>
                        </ul>
                        
                        <?php //print_r($_COOKIE); ?>
                        <?php //print_r($_COOKIE['filterTable']); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="button-apply-filter">Save Filter Config</button>
                    </div>
                </div>
            </div>
        </div> 
        <?php
		if($this->encryption != '-1'):
			$sendSQL = $this->encryption->encrypt(str_replace('\n', ' ', $sql));
			$encoded = true;
		else:
			$sendSQL = str_replace('\n', ' ', $sql);
			$encoded = false;
		endif;
		//echo $sendSQL;
		?>      
        <script>
		var DBFieldNames = Array();
		var i = 0;
		$('.DBFieldName').each(function() {
			DBFieldNames[i] = $(this).val();
			i++;
		});
		
		var DBOptionValues = Array();
		var x = 0;
		var l = 0;
		//var emptyValue = '';
		$('.DBOptionValues').each(function() {
			emptyValue = Array();
			$(this).find('input').each(function() {						
				if($(this).is(':checked')) {							
					emptyValue[x] = $(this).val();
					//console.log(emptyValue[x]+' checked');
					x++;
				} else {
					//console.log($(this).val()+' unchecked');
				}
			});
			DBOptionValues[l] = emptyValue;
			l++;					
		});
		
		var datatable;
		var table_options = {
			data: {
				type: 'remote',
				source: {
					read: {
						url: '<?php echo $url?>',
						method: 'POST',
						params: {
							// custom query params
							query: {
								SQL: "<?php echo $sendSQL?>",
								EmployeeID: <?php echo $_SESSION['system_user_id']?>,
								filterField: DBFieldNames,
								filterValues: DBOptionValues,
								IncludeShares: 1,
								Encoded: <?php echo $encoded?>
							}
						},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								 dataSet = raw.data;
							}
							return dataSet;
						},
					}
				},
				//order: [[ <?php echo $default_sort_idx?>, '<?php echo $default_sort_dir?>' ]],
				//sort: {sort: "desc", field: "DateCreatedDisplay"},
				pageSize: <?php echo $defaultPageSize?>,
				saveState: {
					cookie: <?php echo $cookies?>,
					webstorage: <?php echo $webstorage?>
				},		
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true
			},		
			layout: {
				theme: 'default',
				class: '',
				scroll: !0,
				footer: true					
			},
			filterable: true,		
			pagination: true,
			sortable: true,
			search: {
       			input: $('#tableSearch'),
				delay: 500,
    		},
			columns: <?php echo json_encode($fieldConfig)?>			
		};						
		<?php if(!$onDone): ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {
			setTimeout(function() {
            	mApp.unblock("#datatable-portlet");
            }, 500);
		});
		<?php else: ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {
			setTimeout(function() {
            	mApp.unblock("#datatable-portlet");
            }, 500);
			<?php echo $onDone?>
		});
		<?php endif; ?>
		var query = datatable.getDataSourceQuery();		
		<?php if($default_sort_idx != '' && $default_sort_dir != '') { ?>
			datatable.setDataSourceParam('sort', {sort: "<?php echo $default_sort_dir?>", field: "<?php echo $default_sort_idx?>"});			
			datatable.setDataSourceParam('filterField', DBFieldNames);
			datatable.setDataSourceParam('filterValues', DBOptionValues);
			datatable.load();
		<?php } else { ?>
			//datatable.setDataSourceParam('filterField', DBFieldNames);
			//datatable.setDataSourceParam('filterValues', DBOptionValues);
			//datatable.load();
		<?php } ?>	
		$(document).ready(function(e) {
			<?php if($div_id != 'mySearchTable'): ?>
			mApp.block("#datatable-portlet", {
				overlayColor: "#CCCCCC",
				type: "loader",
				state: "success",
				message: "Loading Table Data..."
			});
			<?php endif; ?>
			
			$(document).on('click', '#button-clear-filter', function() {
					
			
			});
			
			$('#fieldSearch').keypress(function (e) {
				var key = e.which;
				if(key == 13)  // the enter key code
				{
					//alert('Execute Search');
					var query = $('#fieldSearch').val();
					$( "#table-field-options label" ).css( "text-decoration", "none" );
					$( "#table-field-options label" ).css( "color", "black" );
					//$( "#report-field-options label" ).removeClass("m--font-danger");
					if(query != '') {
						$( "#table-field-options label:contains('"+query+"')" ).css( "text-decoration", "underline" );
						$( "#table-field-options label:contains('"+query+"')" ).css( "color", "red" );
						//$( "#report-field-options label:contains('"+query+"')" ).addclass("m--font-danger");
					}
					return false;  
				}	
			});
			
			$(document).on('click', '#includeShares', function() {
				if($(this).is(':checked')) {
					var url = "/ajax/recordShare.php?action=includeShares";						
				} else {
					var url = "/ajax/recordShare.php?action=hideShares";	
				}
				$.post(url, {
					uid: '<?php echo $_SESSION['system_user_id']?>'
				}, function(data) {
					document.location.reload(true);
				});
							
			});
			
			$(document).on('click', '.remove-block-button', function() {
				var div = $(this).attr('data-id');
				$('#'+div).remove();
			});
			
			$(document).on('change', '#filterFieldSelect', function() {
				var field = $(this).val();
				var divID = 'filter_'+field.replace(".", "_");
				//alert(divID);
				if($('#'+divID).length) {
					alert('Field is already a filter for this table view');
					$('#filterFieldSelect').val('');
				} else {
					$.post('/ajax/datatables.php?action=getFilterFieldOptions', {
						field: field
					}, function(data) {
						//console.log(data);
						$('#filterListStack').append(data);
						$('#filterFieldSelect').val('');			
					});
				}
			});
			
			$(document).on('click', '#button-apply-filter', function() {
				//var i = datatable.getDataSourceQuery();
				//datatable.search($('#filterFieldSelect').val(), "filterFilter");				
				var DBFieldNames = Array();
				var i = 0;
				$('.DBFieldName').each(function() {
					DBFieldNames[i] = $(this).val();
					i++;
				});
				
				var DBOptionValues = Array();
				var x = 0;
				var l = 0;
				//var emptyValue = '';
				$('.DBOptionValues').each(function() {
					emptyValue = Array();
					$(this).find('input').each(function() {						
						if($(this).is(':checked')) {							
							emptyValue[x] = $(this).val();
							//console.log(emptyValue[x]+' checked');
							x++;
						} else {
							//console.log($(this).val()+' unchecked');
						}
					});
					DBOptionValues[l] = emptyValue;
					l++;					
				});
				
				$.post('/ajax/datatables.php?action=saveTableFilter', {
					dbFields: DBFieldNames,
					dbValues: DBOptionValues	
				}, function(data) {
					//console.log(data);
					//document.location.reload(true);
					console.log(DBFieldNames);
					//console.log(DBOptionValues);
					if(data.set) {
						datatable.setDataSourceParam('filterField', DBFieldNames);
						datatable.setDataSourceParam('filterValues', DBOptionValues);
						datatable.load();
						$('#tableFilterModal').modal('hide');
						mApp.block("#datatable-portlet", {
							overlayColor: "#CCCCCC",
							type: "loader",
							state: "success",
							message: "Loading Table Data..."
						});		
						if(DBFieldNames.length != 0) {					
							$('#filterTableNotice').show();
						} else {
							$('#filterTableNotice').hide();					
						}
					} else {
						document.location.reload(true);
					}
				}, "json");
			});
			
			$(document).on('click', '.person_link', function() {
				// get parent id //
				var pid = $(this).parents('.m-card-user').attr('data-id');
				//alert(pid);
				document.location.href='/profile/'+pid;			
			});
			
			$(document).on('click', '.image-library-link', function() {
				var pid = $(this).parents('.m-card-user').attr('data-id');
				$('#imageLibaryPreviewModal').modal('show');
				$('#imageLibaryPreviewModal .modal-body').html('<div class="text-center"><div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div><br><br> Loading Record\'s Photo Library...</div>');
				$.post('/ajax/images.php?action=viewLibrary', {
					pid: pid
				}, function(data) {
					$('#imageLibaryPreviewModal .modal-body').html(data);
				});
			});
			
			$(document).on('change', '.fieldListCheck', function(data) {
				var checkData = $(this).val();
				//console.log(checkData);				
				var fieldData = checkData.split("|");
				var divID = fieldData[0].replace(".", "_");
				
				if($(this).is(':checked')) {
					//alert('Checking');				
					var html = '<li class="list-group-item dragable-item" id="'+divID+'">';
					html += fieldData[1];
					html += '<input type="hidden" name="columns[]" value="'+fieldData[1]+'" />';
					html += '<input type="hidden" name="fields[]" value="'+fieldData[0]+'" />';
					html += '</li>';
					$('#currentTableFields').append(html);
					
				} else {
					//alert('Unchecking');
					$('#'+divID).remove();
				}
			});
			
			$(document).on('click', '#button-save-config', function(data) {
				var formData = $('#customTableColums').serializeArray();
				$.post('/ajax/datatables.php?action=saveTableConfig', formData, function(data) {
					//console.log(data);
					document.location.reload(true);
				});
			});
        });
		function getCurentOptions() {
			var i = 0;
			var optionList = Array();
			$('.fieldOptionValue').each(function() {
				if($(this).is(':checked')) {
					optionList[i] = $(this).val();
					i++;
				}				
			});
			return optionList;
		}
		</script>
        <?php
	}
	
	function preRenderFilters() {
		$filterConfig = json_decode($_SESSION['filterTable'], true);
		//print_r($filterConfig);
		$fullFieldList = array_merge($this->reports->coreFields['Person'], $this->reports->coreFields['Profile']);
		//print_r($fullFieldList);
		for($i=0; $i<count($filterConfig['dbFields']); $i++) {
			//echo "PICKED:".$filterConfig['dbFields'][$i]."<br>\n";
			foreach($fullFieldList as $field):
				if($field['field'] == $filterConfig['dbFields'][$i]) {
					$title = $field['title'];
					$DBfield = $field['field'];
					$options = $field['opt'];
					$divID = 'filter_'.str_replace(".", "_", $DBfield);
				}
			endforeach;			
			?>
            <li class="list-group-item" id="<?php echo $divID?>">    			
                <div class="row">
                    <div class="col-lg-4">
                        <?php echo $title?>
                        <input type="hidden" class="DBFieldName" value="<?php echo $DBfield?>" />
                    </div>
                    <div class="col-lg-7">
                        <div class="m-checkbox-list DBOptionValues">
                        <?php foreach($options as $option): ?>
                        <label class="m-checkbox">
                            <input type="checkbox" value="<?php echo $option['value']?>" class="fieldOptionValue" <?php echo ((in_array($option['value'], $filterConfig['dbValues'][$i]))? 'checked':'')?> >
                            <?php echo $option['text']?>
                            <span></span>
                        </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <button type="button" class="btn btn-sm btn-danger remove-block-button" data-id="<?php echo $divID?>"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </li>
    		<?php	
		}		
	}
	
	function get_defaultFieldsBase($tableDiv) {
		if($tableDiv == 'myleadsTable') {
			$defaultFields = array(
				'Persons.DateOfBirth',
				'Persons.Gender',
				'PersonsProfile.prQuestion_621',
				'PersonsProfile.prQuestion_622',
				'PersonsProfile.prQuestion_631',
				'PersonsProfile.prQuestion_1719',
				'PersonsProfile.prQuestion_1713',
				'Phone_number',
				'Persons.DateUpdated',
				'HearAboutUs'		
			);
		} elseif($tableDiv == 'myclientsTable') {
			$defaultFields = array(
				'Persons.DateOfBirth',
				'Persons.Gender',
				'PersonsProfile.prQuestion_664',
				'Persons.PersonsTypes_id',
				'Persons.Matchmaker_id',
				'Persons.Matchmaker2_id',
				'Persons.LastIntroDate',			
				'PersonsProfile.prQuestion_657',														
				'PersonsProfile.prQuestion_676',
				'PersonsProfile.prQuestion_677',
				'PersonsProfile.prQuestion_1026'
			);
		}  elseif($tableDiv == 'allleadsTable') {
			$defaultFields = array(
				'Persons.DateOfBirth',
				'Persons.Gender',
				'PersonsProfile.prQuestion_621',
				'PersonsProfile.prQuestion_622',
				'PersonsProfile.prQuestion_631',
				'PersonsProfile.prQuestion_1719',
				'PersonsProfile.prQuestion_1713',
				'Phone_number',
				'Persons.DateUpdated',
				'HearAboutUs'		
			);			
		} else {
			$defaultFields = array(
				'Persons.DateOfBirth',
				'Persons.Gender',
				'PersonsProfile.prQuestion_664',
				'Persons.PersonsTypes_id',
				'Persons.Matchmaker_id',
				'Persons.Matchmaker2_id',
				'Persons.Assigned_userID'	
			);
		}
		return $defaultFields;
	}
	
	function renderFieldList($tableID) {
		//global $userPerms;
		include_once("class.users.php");
		$USR = new Users($this->db);
		$userPerms = $USR->get_userPermissions($_SESSION['system_user_id']);
		//print_r($userPerms);
		//print_r($this->reports);
		$fullFieldList = array_merge($this->reports->coreFields['Person'], $this->reports->coreFields['Profile']);
		//print_r($fullFieldList);
		//print_r($this->reports->coreFields['Profile']);
		?><div class="m-checkbox-list"><?php
		$skipArray = array('LastNoteAction');
		if((!in_array(88, $userPerms)) && ($tableID == 'mySearchTable')):
			$skipArray[] = 'Persons.Email';
			$skipArray[] = 'Phone_number';
		endif;
		$presetArray = array('Persons.FirstName', 'Persons.LastName', 'PersonsImages_path');
		$customConfig = $this->get_myTableConfig($_SESSION['system_user_id'], $tableID);
		if(!$customConfig) {
			$defaultFields = $this->get_defaultFieldsBase($tableID);
		} else {
			//print_r($customConfig);
			foreach($customConfig as $configField):
				$defaultFields[] = $configField['field'];
			endforeach;
		}
		foreach($fullFieldList as $field):
			if(!in_array($field['field'], $skipArray)):
			?>            
            <label class="m-checkbox">
                <input type="checkbox" class="fieldListCheck" value="<?php echo $field['field']?>|<?php echo $field['title']?>" <?php echo ((@in_array($field['field'], $presetArray))? 'checked disabled':'')?> <?php echo ((@in_array($field['field'], $defaultFields))? 'checked':'')?>>
                <?php echo $field['title']?>
                <span></span>
            </label>
            <?php
			endif;
		endforeach;
		?></div><?php
	}
	
	function renderFiltersSelect($tableID) {
		$customConfig = $this->get_myTableConfig($_SESSION['system_user_id'], $tableID);
		if(!$customConfig) {
			$defaultFields = array(
				'Persons.DateOfBirth',
				'Persons.Gender',
				'PersonsProfile.prQuestion_621',
				'PersonsProfile.prQuestion_622',
				'PersonsProfile.prQuestion_631',
				'PersonsProfile.prQuestion_1719',
				'PersonsProfile.prQuestion_1713',
				'Phone_number',
				'Persons.DateUpdated',
				'HearAboutUs'		
			);
		} else {
			foreach($customConfig as $configField):
				$defaultFields[] = $configField['field'];
			endforeach;			
		}
		$fullFieldList = array_merge($this->reports->coreFields['Person'], $this->reports->coreFields['Profile']);	
		//print_r($defaultFields);
		//print_r($fullFieldList);
		ob_start();
		foreach($defaultFields as $field):
			foreach($fullFieldList as $fullField):
				if($fullField['field'] == $field):
					if(($fullField['type'] == 'SELECT') || ($fullField['type'] == 'RADIO') || ($fullField['type'] == 'CHECKBOX')):
					?><option value="<?php echo $fullField['field']?>"><?php echo $fullField['title']?></option><?php
					endif;
				endif;
			endforeach;
		endforeach;
		return ob_get_clean();
	}
	
	function renderColumnList($tableID) {		
		$customConfig = $this->get_myTableConfig($_SESSION['system_user_id'], $tableID);
		if(!$customConfig) {
			//if($tableID == 'myleadsTable') {
			$defaultFields = $this->get_defaultFieldsBase($tableID);
		} else {
			foreach($customConfig as $configField):
				$defaultFields[] = $configField['field'];
			endforeach;			
		}
		//echo "Default Field List:";
		//print_r($defaultFields);		
		?><ul class="list-group" id="currentTableFields"><?php
		$fullFieldList = array_merge($this->reports->coreFields['Person'], $this->reports->coreFields['Profile']);
		//echo "Full Field List:";
		//print_r($fullFieldList);
		foreach($defaultFields as $field):
			foreach($fullFieldList as $fullField):
				if($fullField['field'] == $field):
					?>
                    <li class="list-group-item dragable-item" id="<?php echo str_replace(".", "_", $fullField['field'])?>">
						<?php echo $fullField['title']?>
                        <input type="hidden" name="columns[]" value="<?php echo $fullField['title']?>" />
                        <input type="hidden" name="fields[]" value="<?php echo $fullField['field']?>" />
                    </li>
					<?php
				endif;
			endforeach;
		endforeach;
		?></ul><?php	
		
	}
	
	/*! \fn obj render_Assign_datatable(($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false)
		\brief render and draw the datatable for the Leads Assignment view
		\param str 	$div_id	ID of the table when renders
		\param str 	$title 	Title to be diplayed on the table
		\param str 	$url	URL the SQL will be sent to for processing
		\param str 	$sql	SQL to bew sent to the database
		\param obj 	$fields	fields used in the columns of the table
		\param str 	$id_field	field containing the unique identifier for this list
		\param str 	$default_sort	default sort field for this table view
		\param str 	$default_sort_direction default sort direction for tabgle
		\param int 	$defaultPageSize default page size for thable view
		\param bool $onDone code to execute upon completion of the data loading
		\param bool	$showChecks if show checkboxes on he far left of each row
		\return HTML
	*/
	function render_Assign_datatable($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false) {
		if($showChecks) {
			$fieldConfig[] = array(
				'field'		=> "Person_id",
				'title'		=> "#",
				'locked'	=> array('left' => 'xl'),
				'sortable'	=> false,
				'width'		=> 40,
				'selector'	=> array('class' => 'm-checkbox--solid m-checkbox--brand')
			);
		}
		$fieldConfig[] = array(
			'field'		=>	"LastName", 
			'width'		=>	200,
			'title'		=>	"Record", 
			'overflow'	=>	"visible",
			'locked' 	=>	array('left' => 'xl'),
			'sortable'	=> 	true,
			'textAlign'	=>	"center",			
			'template'	=>	'<div class="m-card-user m-card-user--sm"><div class="m-card-user__pic" style="background-image:url({{PersonsImages_path}}); background-size:cover;"><img src="/assets/app/media/img/users/filler.png" class="m--img-rounded m--marginless" alt="photo"></div><div class="m-card-user__details" style="padding-left:5px;"><span class="m-card-user__name"><a href="/profile/{{'.$id_field.'}}" class="m-link m-link--state m-link--pending">{{FirstName}} {{LastName}}</a></span><span class="m-card-user__email">{{City}} {{State}}  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a href="/profile/{{PID}}" target="_blank" class="m-link" style="color:#7b7e8a;"><i class="la la-external-link-square"></i></a></div></span>'
		);		
		foreach($fields as $field):
			if (isset($field['template'])) {
				$fieldConfig[] = array(
					'field'		=> $field['field'],
					'title' 	=> $field['label'],
					'width'		=> $field['width'],
					'template'	=>	$field['template']
				);				
			} else {
				$fieldConfig[] = array(
					'field'	=> $field['field'],
					'title' => $field['label'],
					'width'	=> $field['width']
				);
			}
		endforeach;	
		ob_start();
		?>
        <div class="m-portlet m-portlet--head-sm m-portlet--mobile ">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text"> <?php echo $title?></h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <!--
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                        	<div class="input-group">
                            	<span class="input-group-addon"><i class="flaticon-search-1"></i>&nbsp;Quick Search</span>
                            	<input type="text" class="form-control m-input" id="tableSearch" />                                    
                           	</div>
                        </li>
                        
                        <li class="m-portlet__nav-item">
                    		<a href="javascript:$('#SearchArea').toggle();"  data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon"><i class="la la-angle-down"></i></a>	
                		</li>                        
                    </ul>
                    -->
                </div>
            </div>
            <div class="m-portlet__body">
            	<!--begin: Search Form -->
                <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                    
                    
                    <div class="row align-items-center">
                        <div class="col-xl-12 order-2 order-xl-1">
                            
                            <div class="form-group m-form__group row align-items-center">
                                <div class="col-md-4">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label>Assigned:</label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-input--solid" id="m_form_status">
                                                <option value="">All</option>
												<?php echo $this->record->options_userSelect(array(0))?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label class="m-label m-label--single">Stage:</label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-input--solid" id="m_form_type">
                                                <option value="">All</option>
                                                <?php echo $this->record->options_stageSelect(array())?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="m-form__group m-form__group--inline">
                                        <div class="m-form__label">
                                            <label class="m-label m-label--single">Location:</label>
                                        </div>
                                        <div class="m-form__control">
                                            <select class="form-control m-input--solid" id="m_form_loc">
                                                <option value="">All</option>
                                                <?php echo $this->record->options_officeSelect(array())?>                                                                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-md-none m--margin-bottom-10"></div>
                                </div>
							</div>
                                                            
                            <div class="form-group m-form__group row align-items-center">   
                                <div class="col-md-6">
                                    <div class="m-input-icon m-input-icon--left">
                                        <input type="text" class="form-control m-input" placeholder="Search..." id="tableSearch">
                                        <span class="m-input-icon__icon m-input-icon__icon--left">
                                            <span><i class="la la-search"></i></span>
                                        </span>
                                    </div>
                                    <span class="m-form__help">
                        				search name, email address, and "Tell us about Yourself" response
                    				</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                        	<i class="la la-calendar"></i>
                                        </span>
                                        <input type="text" class="form-control m-input" id="filterDates">
                                        <span class="input-group-btn">
                                            <button class="btn btn-secondary" type="button" onclick="clearSearch()">clear</button>
                                        </span>
									</div>
                                    <span class="m-form__help">
                        				filter on the date created
                    				</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <!--end: Search Form -->
                <div class="row" id="loadingTableBlock">
                	<div class="col-3">&nbsp;</div>
                	<div class="col-6">
                    	<div class="m-alert m-alert--icon m-alert--air m-alert--square alert alert-dismissible fade show" role="alert">
                            <div class="m-alert__icon">
                            	<div class="m-loader m-loader--danger m-loader--lg" style="width: 30px; display: inline-block;"></div>
                            </div>
                            <div class="m-alert__text">
                            	 <strong style="font-size:1.5em;">Loading Table Data...</strong>
                            </div>
                        </div>
					</div>
				</div>                                            
                <!--begin: Datatable -->
                <div class="m_datatable" id="<?php echo $div_id?>"></div>
                <!--end: Datatable -->
            </div>
            
            <div class="m-portlet__foot">            
                <div class="row align-items-center">
                    <div class="col-lg-8 m--valign-left">
                        <div class="form-group m-form__group row">
                            <label class="col-lg-3 col-form-label">
                                Assign checked to:
                            </label>
                            <div class="col-lg-6">
                                <select class="form-control m-input--solid" id="m_form_assign">
                                    <option value=""></option>
                                    <option value="0" selected>Unassigned</option>
                                    <?php echo $this->record->options_userSelect(array())?>
                                </select>
                            </div>
                            <div class="col-lg-3">
                            	<button type="submit" class="btn btn-brand" onclick="assignedLeads()">Assigned Leads <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 m--align-left">&nbsp;</div>
                </div>
            </div>
        </div>
        <?php
        if($this->encryption != '-1'):
			$sendSQL = $this->encryption->encrypt(str_replace('\n', ' ', $sql));
			$encoded = true;
		else:
			$sendSQL = str_replace('\n', ' ', $sql);
			$encoded = false;
		endif;
		//echo $sendSQL; 
		?>       
        <script>
		var datatable;
		var table_options = {
			data: {
				type: 'remote',
				source: {
					read: {
						url: '<?php echo $url?>',
						method: 'POST',
						params: {
							// custom query params
							query: {
								SQL: "<?php echo $sendSQL?>",
								EmployeeID: <?php echo $_SESSION['system_user_id']?>,
								RawAssigned: 0,
								Encoded: <?php echo $encoded?>
							}
						},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								 dataSet = raw.data;
							}
							return dataSet;
						},
					}
				},
				order: [[ 0, 'desc' ]],
				pageSize: <?php echo $defaultPageSize?>,
				saveState: {
					cookie: false,
					webstorage: false
				},		
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true
			},		
			layout: {
				theme: 'default',
				class: '',
				scroll: !0,
				footer: true,
			},
			filterable: true,		
			pagination: true,
			sortable: true,
			search: {
       			input: $('#tableSearch'),
				delay: 500,
    		},
			columns: <?php echo json_encode($fieldConfig)?>			
		};
		<?php if(!$onDone): ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options);
		<?php else: ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {
			<?php echo $onDone?>
		});
		<?php endif; ?>
		var query = datatable.getDataSourceQuery();
		//query.Assigned = 0;
		// shortcode to datatable.setDataSourceParam('query', query);
		//datatable.setDataSourceQuery(query);
		//datatable.load();
		//var query = datatable.getDataSourceQuery();

		$('#m_form_status').on('change', function() {
			// shortcode to datatable.getDataSourceParam('query');
			var query = datatable.getDataSourceQuery();
			query.Assigned = $(this).val().toLowerCase();
			// shortcode to datatable.setDataSourceParam('query', query);
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();
		}).val(typeof query.Assigned !== 'undefined' ? query.Assigned : '0');
		
		$('#m_form_type').on('change', function() {
			// shortcode to datatable.getDataSourceParam('query');
			var query = datatable.getDataSourceQuery();
			query.Stage = $(this).val().toLowerCase();
			// shortcode to datatable.setDataSourceParam('query', query);
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();
		}).val(typeof query.Stage !== 'undefined' ? query.Stage : '');

		$('#m_form_loc').on('change', function() {
			// shortcode to datatable.getDataSourceParam('query');
			var query = datatable.getDataSourceQuery();
			query.Location = $(this).val().toLowerCase();
			// shortcode to datatable.setDataSourceParam('query', query);
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();
		}).val(typeof query.Location !== 'undefined' ? query.Location : '');
		
		$("#m_form_status").select2({ theme: "classic" });
		$("#m_form_type").select2({ theme: "classic" });
		$('#m_form_loc').select2({ theme: "classic" });
		$('#m_form_assign').select2({ theme: "classic" });
		
		var start = moment().subtract(11, 'months');
		var end = moment();	
		$('#filterDates').daterangepicker({
			buttonClasses: 'm-btn btn',
			applyClass: 'btn-primary',
			cancelClass: 'btn-secondary',
			startDate: start,
			endDate: end,
			ranges: {
			   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
			   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
			   'Last 6 Months': [moment().subtract(5, 'months'), moment()],
			   'Last 12 Months': [moment().subtract(11, 'months'), moment()],
			   'This Month': [moment().startOf('month'), moment().endOf('month')],
			   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			}
		});
		$('#filterDates').on('apply.daterangepicker', function(ev, picker) {
  			//do something, like clearing an input
  			//$('#daterange').val('');
			var range = $('#filterDates').val();
			//console.log(range);
			//console.log(ev);
			//console.log(picker.startDate.format("x"));
			//console.log(picker.endDate.format("x"));
			var query = datatable.getDataSourceQuery();
			query.Start = picker.startDate.format("X");
			query.Ender = picker.endDate.format("X")
			// shortcode to datatable.setDataSourceParam('query', query);
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();
		});
		$('#filterDates').val('');
		
		var clearSearch = function() {
			$('#filterDates').val('');
			var query = datatable.getDataSourceQuery();
			//console.log(query);
			delete query.Start;
			delete query.Ender;
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();				
		}
		var assignedLeads = function() {
			var assignTo = $('#m_form_assign').val();
			var index = 0;
			var records = Array();
			if(assignTo == '') {
				alert('You cannot assign leads to no-one. You must select a user or select UNASSIGNED');
			} else {
				$('.m-datatable__table tr td input[type="checkbox"]').each(function() {
					if($(this).is(':checked')) {
						records[index] = $(this).val();
						index++;
					}
				});
				console.log(records);
				if(records.length > 0) {
					$.post('/ajax/otherStuff.php?action=assignLead', {
						records: records,
						user: assignTo
					}, function(data) {
						datatable.load();
						toastr.success(data.touched+' Records Assigned');
						$('#loadingTableBlock').show();
					}, "json");
				} else {
					alert('You must select at least one record to assign');	
				}
			}
				
		}
		</script>
        <?php
	}
	
	/*! \fn obj render_Assign_datatable(($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false)
		\brief render and draw the datatable for the Leads Assignment view
		\param str 	$div_id	ID of the table when renders
		\param str 	$title 	Title to be diplayed on the table
		\param str 	$url	URL the SQL will be sent to for processing
		\param str 	$sql	SQL to bew sent to the database
		\param obj 	$fields	fields used in the columns of the table
		\param str 	$id_field	field containing the unique identifier for this list
		\param str 	$default_sort	default sort field for this table view
		\param str 	$default_sort_direction default sort direction for tabgle
		\param int 	$defaultPageSize default page size for thable view
		\param bool $onDone code to execute upon completion of the data loading
		\param bool	$showChecks if show checkboxes on he far left of each row
		\return HTML
	*/
	function render_General_datatable($div_id, $title, $url, $sql, $fields, $id_field, $default_sort, $default_sort_direction, $defaultPageSize=20, $onDone=false, $showChecks=false, $portletNav='') {
		foreach($fields as $field):
			if (isset($field['template'])) {
				$fieldConfig[] = array(
					'field'		=> $field['field'],
					'title' 	=> $field['label'],
					'width'		=> $field['width'],
					'template'	=>	$field['template']
				);				
			} else {
				$fieldConfig[] = array(
					'field'	=> $field['field'],
					'title' => $field['label'],
					'width'	=> $field['width']
				);
			}
		endforeach;	
		ob_start();
		?>
        <div class="m-portlet m-portlet--head-sm m-portlet--mobile ">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <h3 class="m-portlet__head-text"> <?php echo $title?></h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <?php echo $portletNav?>
                </div>
            </div>
            <div class="m-portlet__body">
            	<!--begin: Search Form -->
                <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                    
                    
                    <div class="row align-items-center">
                        <div class="col-xl-12 order-2 order-xl-1">                            
                            <div class="form-group m-form__group row align-items-center">   
                                <div class="col-md-6">
                                    <div class="m-input-icon m-input-icon--left">
                                        <input type="text" class="form-control m-input" placeholder="Search..." id="tableSearch">
                                        <span class="m-input-icon__icon m-input-icon__icon--left">
                                            <span><i class="la la-search"></i></span>
                                        </span>
                                    </div>
                                    <span class="m-form__help">
                        				text search
                    				</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                        	<i class="la la-calendar"></i>
                                        </span>
                                        <input type="text" class="form-control m-input" id="filterDates">
                                        <span class="input-group-btn">
                                            <button class="btn btn-secondary" type="button" onclick="clearSearch()">clear</button>
                                        </span>
									</div>
                                    <span class="m-form__help">
                        				filter on the date created
                    				</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <!--end: Search Form -->
                <div class="row" id="loadingTableBlock">
                	<div class="col-3">&nbsp;</div>
                	<div class="col-6">
                    	<div class="m-alert m-alert--icon m-alert--air m-alert--square alert alert-dismissible fade show" role="alert">
                            <div class="m-alert__icon">
                            	<div class="m-loader m-loader--danger m-loader--lg" style="width: 30px; display: inline-block;"></div>
                            </div>
                            <div class="m-alert__text">
                            	 <strong style="font-size:1.5em;">Loading Table Data...</strong>
                            </div>
                        </div>
					</div>
				</div>                                            
                <!--begin: Datatable -->
                <div class="m_datatable" id="<?php echo $div_id?>"></div>
                <!--end: Datatable -->
            </div>
        </div>
		<?php
        if($this->encryption != '-1'):
			$sendSQL = $this->encryption->encrypt(str_replace('\n', ' ', $sql));
			$encoded = true;
		else:
			$sendSQL = str_replace('\n', ' ', $sql);
			$encoded = false;
		endif;
		//echo $sendSQL; 
		?>       		
        <script>
		var datatable;
		var table_options = {
			data: {
				type: 'remote',
				source: {
					read: {
						url: '<?php echo $url?>',
						method: 'POST',
						params: {
							// custom query params
							query: {
								SQL: "<?php echo $sendSQL?>",
								EmployeeID: <?php echo $_SESSION['system_user_id']?>,
								Encoded: <?php echo $encoded?>
							}
						},
						map: function(raw) {
							// sample data mapping
							var dataSet = raw;
							if (typeof raw.data !== 'undefined') {
								 dataSet = raw.data;
							}
							return dataSet;
						},
					}
				},
				order: [[ 0, 'desc' ]],
				pageSize: <?php echo $defaultPageSize?>,
				saveState: {
					cookie: false,
					webstorage: false
				},		
				serverPaging: true,
				serverFiltering: true,
				serverSorting: true
			},		
			layout: {
				theme: 'default',
				class: '',
				scroll: !0,
				footer: true,
			},
			filterable: true,		
			pagination: true,
			sortable: true,
			search: {
       			input: $('#tableSearch'),
				delay: 500,
    		},
			columns: <?php echo json_encode($fieldConfig)?>			
		};
		<?php if(!$onDone): ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options);
		<?php else: ?>
		datatable = $('#<?php echo $div_id?>').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {
			<?php echo $onDone?>
		});
		<?php endif; ?>
		var query = datatable.getDataSourceQuery();
		<?php if($default_sort != '' && $default_sort_direction != '') { ?>
			datatable.setDataSourceParam('sort', {sort: "<?php echo $default_sort_direction?>", field: "<?php echo $default_sort?>"});
			datatable.load();
		<?php } ?>
		var start = moment().subtract(11, 'months');
		var end = moment();	
		$('#filterDates').daterangepicker({
			buttonClasses: 'm-btn btn',
			applyClass: 'btn-primary',
			cancelClass: 'btn-secondary',
			startDate: start,
			endDate: end,
			ranges: {
			   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
			   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
			   'Last 6 Months': [moment().subtract(5, 'months'), moment()],
			   'Last 12 Months': [moment().subtract(11, 'months'), moment()],
			   'This Month': [moment().startOf('month'), moment().endOf('month')],
			   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			}
		});
		$('#filterDates').on('apply.daterangepicker', function(ev, picker) {
  			//do something, like clearing an input
  			//$('#daterange').val('');
			var range = $('#filterDates').val();
			//console.log(range);
			//console.log(ev);
			//console.log(picker.startDate.format("x"));
			//console.log(picker.endDate.format("x"));
			var query = datatable.getDataSourceQuery();
			query.Start = picker.startDate.format("X");
			query.Ender = picker.endDate.format("X")
			// shortcode to datatable.setDataSourceParam('query', query);
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();
		});
		$('#filterDates').val('');
		
		var clearSearch = function() {
			$('#filterDates').val('');
			var query = datatable.getDataSourceQuery();
			//console.log(query);
			delete query.Start;
			delete query.Ender;
			datatable.setDataSourceQuery(query);
			datatable.load();
			$('#loadingTableBlock').show();				
		}
		</script>
        <?php
	}
	
	/*! \fn obj get_image_directory($PersonID)
		\brief find the media subdirectory path fopr the record
		\param str 	$PersonID	ID of the person whose images you seek
		\return STR
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
	
	function getCustomTableConfig_select($user_id, $configField) {
		$sql = "SELECT * FROM UsersCustomTables WHERE user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		//print_r($snd);
		if(isset($snd['empty_result'])) {
			return false;
		} else {
			if ($snd[$configField] != '') {
				$config = json_decode($snd[$configField], true);
				foreach($config as $setting):
					if(!in_array($setting['field'], $this->skipFields)) {
						//echo $setting['field']."<br>\n";
						$foundCustom = preg_match('/prQuestion_/', $setting['field']);
						if($foundCustom) {
							$qid = str_replace('PersonsProfile.prQuestion_', '', $setting['field']);
							$sql = "SELECT * FROM Questions INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions_id='".$qid."'";
							//echo $sql;
							$snd = $this->db->get_single_result($sql);
							if($snd['QuestionTypes_id'] == '6') {
								$return = "IF(".$snd['MappedField']." IS NULL or ".$snd['MappedField']." = '0' or ".$snd['MappedField']." = '1', '', FROM_UNIXTIME(".$snd['MappedField'].", '%Y-%m-%d')) as ".$snd['MappedField']; 
							} else {
								$return = $snd['MappedField'];
							}
							$selectArray[] = $return;
						} else {
							$selectArray[] = $setting['field'];
						}
					}
				endforeach;
				$selectReplace = implode(",\n", $selectArray);
				return $selectReplace;
			} else {
				return false;	
			}
		}	
	}
	
	function make_customSelect($array) {
		$specialSkip = array(
			'FullName',
			'PID',
			'office_Name',
			'PersonsTypes_text',
			'PersonsTypes_color',
			'PersonsImages_path',
			'Persons_Color_Span',
			'Persons_Color_Id',
			'RecordAge',
			'Marketer',
			'Matchmaker',
			'NetworkDeveloper',
			'LeadStages_id',
			'LeadStages_name',
			'LeadStage_hex',
			'PhoneNumber',
			'ContractStart',
			'ContractEnd',
			'DateCreatedDisplay',
			'DateUpdateDisplay',
			'DateUpdatedDisplay',
			'LastIntroDate',
			'PersonsTypes_text',
			'PrimeNoteBody',
			'Addresses.City',
			'Addresses.Country',
			'Addresses.State',
			'Addresses.GeoLocationStatus',
			'distance'
		);
		$exclude_array = array_merge($this->skipFields, $specialSkip);
		foreach($array as $setting):
			if(!in_array($setting['field'], $exclude_array)) {
				//echo $setting['field']."<br>\n";
				$foundCustom = preg_match('/prQuestion_/', $setting['field']);
				if($foundCustom) {
					$qid = str_replace('prQuestion_', '', $setting['field']);
					$sql = "SELECT * FROM Questions INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id WHERE Questions_id='".$qid."'";
					//echo $sql."\n";
					$snd = $this->db->get_single_result($sql);
					//print_r($snd);
					if($snd['QuestionTypes_id'] == '6') {
						$return = "IF(".$snd['MappedField']." IS NULL or ".$snd['MappedField']." = '0' or ".$snd['MappedField']." = '1', '', FROM_UNIXTIME(".$snd['MappedField'].", '%Y-%m-%d')) as ".$snd['MappedField']; 
					} else {
						$return = $snd['MappedField'];
					}
					//echo $return."\n";
					$selectArray[] = $return;
				} else {
					$selectArray[] = $setting['field'];
				}
			}
		endforeach;
		$selectReplace = implode(",\n", $selectArray);
		return $selectReplace;
	}
	
	function get_myTableConfig($user_id, $configField) {
		$sql = "SELECT * FROM UsersCustomTables WHERE user_id='".$user_id."'";
		//echo $sql;
		$snd = $this->db->get_single_result($sql);
		//print_r($snd);
		if(isset($snd['empty_result'])) {
			return false;
		} else {
			if ($snd[$configField] != '') {
				return json_decode($snd[$configField], true);
			} else {
				return false;	
			}	
		}
	}
	
	
	
}