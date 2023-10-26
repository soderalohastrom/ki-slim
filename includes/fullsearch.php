<?php

use PHPSQLParser\builders\TruncateBuilder;

include_once("class.record.php");
include_once("class.datatables.php");
include_once("class.encryption.php");
include_once("class.reports.php");
include_once("class.sessions.php");
include_once("class.forms.php");
include_once("class.users.php");

$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, $REPORTS, $ENC);
$SESSION = new Session($DB, $ENC);
$FORMS = new Forms($DB);
$USERS = new Users($DB);
//print_r($_SESSION);

$defaultSearch['lat'] = '34.0617109';
$defaultSearch['lng'] = '-118.4017053';
$defaultSearch['name'] = 'L.A. Office';

$userPerms = $USERS->get_userPermissions($_SESSION['system_user_id']);
$myTableConfig = $DATATABLE->get_myTableConfig($_SESSION['system_user_id'], 'mySearchTable');
//print_r($myTableConfig);
if($myTableConfig) {
	foreach($myTableConfig as $tableField):
		$fieldNames[] = $tableField['field'];
	endforeach;
	
	if(in_array('Persons.Email', $fieldNames)) {
		$exportToMarketing = true;
	} else {	
		$exportToMarketing = false;
	}
} else {
	$exportToMarketing = false;	
}

$tableFields = $DATATABLE->makeCustomLeadFields($_SESSION['system_user_id'], 'mySearchTable');
//print_r($tableFields);
if(!$tableFields) {
	$tableFields = $DATATABLE->getCustomSearchFields();
}
$tableFields[] = array(
	'field'	=>	'distance',
);
$tableConfig_js = json_encode($tableFields);
//print_r($tableFields);

$GROUP_ID = $pageParamaters['params'][0];
$showSearch = true;	
$GLOBALS['search-show-edit-table'] = true;

if($GROUP_ID != ''):
    $sql = "SELECT * FROM `Groups` WHERE `Groups_id`='".$GROUP_ID."'";
	$snd = $DB->get_single_result($sql);
	$json['name'] 	= $snd['Groups_name'];
	$json['desc']	= $snd['Groups_description'];
	$json['sql']	= $snd['Groups_baseQuery'];
	$json['fields'] = json_decode($snd['Groups_fields'], true);
	$json['config']	= json_decode($snd['Groups_config'], true);
	$json['cat']	= $snd['Groups_catID'];	
	$preLoad = $json['config'];
	$tableFields = $json['fields'];
	$tableConfig_js = json_encode($tableFields);
	$runPreload = true;		
	$GLOBALS['search-show-edit-table'] = false;
	$GLOBALS['GID'] = $GROUP_ID;
	
	if($snd['Groups_source'] == 1):
	?>
    <div class="m-content">
    <div class="m-alert m-alert--icon alert alert-danger" role="alert">
        <div class="m-alert__icon">
            <i class="la la-exclamation-triangle"></i>
        </div>
        <div class="m-alert__text">
            <strong>ERROR</strong><br /><small>You are attempting to access an invalid saved search</small>
        </div>
        <div class="m-alert__actions" style="width: 160px;">
            <a href="/search" class="btn btn-warning btn-sm m-btn m-btn--pill m-btn--wide">
                Return to Search
            </a>
        </div>
	</div>
    </div>
    <?php
	$showSearch = false;
	endif;
else:
  	$GROUP_ID = 0;
	if(isset($_SESSION['searchCriteria'])) {
		$preLoad = unserialize($_SESSION['searchCriteria']);
		$runPreload = false;	
	} else {
		$preLoad = array();
		$runPreload = false;	
	}
endif;
//print_r($preLoad);
if($showSearch):
?>
<style>
.select2-search__field, .select2-search--inline, .select2-selection__rendered {
        width: 100% !important;
    }
</style>
<div class="m-content">

<!-- START: SEARCH FILTER PORTLET -->
<div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilterportlet" style="background-color:rgba(255,255,255,0); box-shadow:0 1px 15px 1px rgba(0,0,0,0); display:<?php echo (($runPreload)? 'none':'default')?>;">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-coins"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    Filters
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">                
                <li class="m-portlet__nav-item">
                    <a href="javascript:openListCats();" id="listCategory-button" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--hover-primary m-btn--icon m-btn--icon-only m-btn--pill" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Manage list categories">
                        <i class="la la-list-ol"></i>
                    </a>
                </li>
                <!--
                <li class="m-portlet__nav-item">
                    <a href="javascript:;" id="videoHelp-button" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" data-skin="dark" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Need Help? Watch the training video!">                    
                        <i class="la la-question"></i>
                    </a>
                </li>
                -->
                <li class="m-portlet__nav-item">
                    <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-skin="dark" data-original-title="Collapse/Expand">
                        <i class="la la-angle-down"></i>
                    </a>
                </li>               
                <!--
                <li class="m-portlet__nav-item">
                    <a href="#" data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Fullscreen">
                        <i class="la la-expand"></i>
                    </a>
                </li>
                -->              
            </ul>
        </div>
    </div>
    
    <form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right" action="javascript:genSearchQuery(1);">
    <div class="m-portlet__body" style="padding-top:0px; padding-bottom:0px;">
    
    	
        <div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilters_Core" style="margin-bottom:2px;">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="la la-filter"></i>
                        </span>
                        <h3 class="m-portlet__head-text">Core Filters</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse/Expand" data-skin="dark">
                                <i class="la la-angle-down"></i>
                            </a>
                        </li>              
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body" style="">
            	<div class="row">
                    <div class="col-lg-4">
                        <div class="m-form__section m-form__section--first">
                            <div class="form-group m-form__group">
                                <label>Name:</label>
                                <?php
                                if(isset($preLoad['FirstName'])) {
                                    $preSelected = $preLoad['FirstName'];
                                } else {
                                    $preSelected = '';	
                                }
                                
                                if(isset($preLoad['LastName'])) {
                                    $preSelected2 = $preLoad['LastName'];
                                } else {
                                    $preSelected2 = '';	
                                }
                                ?>
                                <div class="input-group m-input-group">
                                    <input type="text" name="FirstName" id="FirstName" class="form-control m-input" placeholder="First" value="<?php echo $preSelected?>" />
                                    <span class="input-group-addon">&nbsp;</span>
                                    <input type="text" name="LastName" id="LastName" class="form-control m-input" placeholder="Last" value="<?php echo $preSelected2?>">
                                </div>
                            </div>
						</div>
					</div>
                    <div class="col-lg-3">
                    	<div class="form-group m-form__group">
                            <label>Email address:</label>
                            <?php
                            if(isset($preLoad['Email'])) {
                                $preSelected = $preLoad['Email'];
                            } else {
                                $preSelected = '';	
                            }
                            ?>
                            <div class="m-input-icon m-input-icon--right">
                                <input type="email" name="Email" id="Email" class="form-control m-input" placeholder="user@domain.com" value="<?php echo $preSelected?>" />
                                <span class="m-input-icon__icon m-input-icon__icon--right">                    
                                    <span><i class="la 	la-envelope-o"></i></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                    	<div class="form-group m-form__group">
                            <label>Phone Number:</label>
                            <?php
                            if(isset($preLoad['Phone_raw'])) {
                                $preSelected = $preLoad['Phone_raw'];
                            } else {
                                $preSelected = '';	
                            }
                            ?>
                            <div class="m-input-icon m-input-icon--right">
                                <input type="text" name="Phone_raw" id="Phone_raw" class="form-control m-input" value="<?php echo $preSelected?>" />
                                <span class="m-input-icon__icon m-input-icon__icon--right">                    
                                    <span><i class="la la-phone"></i></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                    	<div class="form-group m-form__group">
                            <label>Record ID:</label>
                            <input type="text" name="Person_id" id="Person_id" class="form-control m-input" placeholder="ID" value="<?php echo (isset($preLoad['Person_id'])? $preLoad['Person_id']:'')?>">
                        </div>                    
                    </div>
                </div>
                <!-- END ROW ONE -->
                
            </div>
		</div>
        <!-- END CORE FILTERS -->
        
        
        <div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilters_Basic" style="margin-bottom:2px;">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="la la-filter"></i>
                        </span>
                        <h3 class="m-portlet__head-text">Basic Filters</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse/Expand" data-skin="dark">
                                <i class="la la-angle-down"></i>
                            </a>
                        </li>              
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body" style="">
            	<div class="row">
					<div class="col-lg-4">                    	
                        <div class="form-group m-form__group">
                            <label>Record Types</label>
                            
                            <select class="form-control m-select2" id="PersonsTypes_id" name="PersonsTypes_id[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['PersonsTypes_id'])) {
                                $preSelected = $preLoad['PersonsTypes_id'];
                            } else {
                                $preSelected = array();	
                            }
                            $sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1,2,9) ORDER BY PersonsTypes_order";
                            $snd = $DB->get_multi_result($sql);
                            foreach($snd as $dta):
                                ?><option value="<?php echo $dta['PersonsTypes_id']?>" <?php echo ((in_array($dta['PersonsTypes_id'], $preSelected))? 'selected':'')?>><?php echo $dta['PersonsTypes_text']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Member Types</label>        
                            <select style="width:100%" class="form-control m-select2" id="prQuestion_657" name="prQuestion_657[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['prQuestion_657'])) {
                                $preSelected = $preLoad['prQuestion_657'];
                            } else {
                                $preSelected = array();	
                            }						
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='657' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Pods</label>
                            
                            <select class="form-control m-select2" id="Pods_id" name="Pods_id[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['Pods_id'])) {
                                $preSelected = $preLoad['Pods_id'];
                            } else {
                                $preSelected = array();	
                            }
                            $sql = "SELECT * FROM Pods ORDER BY Pod_id";
                            $snd = $DB->get_multi_result($sql);
                            foreach($snd as $dta):
                                ?><option value="<?php echo $dta['Pod_id']?>" <?php echo ((in_array($dta['Pod_id'], $preSelected))? 'selected':'')?>><?php echo $dta['pod_Name']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>    
                        <div class="form-group m-form__group">
                            <label>Record Status</label>        
                            <select  style="width:100%" class="form-control m-select2" id="Color_id" name="Color_id[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['Color_id'])) {
                                $preSelected = $preLoad['Color_id'];
                            } else {
                                $preSelected = array();	
                            }						
                            $qa_sql = "SELECT * FROM ClientColors ORDER BY ColorOrder ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['Color_id']?>" <?php echo ((in_array($qa_dta['Color_id'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['ColorTitle']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>
                        
                        <div class="form-group m-form__group">
                            <label>Incoming Form(s)</label>        
                            <select style="width:100%" class="form-control select2 " id="FormID" name="FormID[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['FormID'])) {
                                $preSelected = $preLoad['FormID'];
                            } else {
                                $preSelected = array('empty_result');	
                            }
							?><optgroup label="Active Forms"><?php
                            $ls_sql = "SELECT * FROM CompanyForms WHERE FormActive='1' ORDER BY FormName ASC";
                            $ls_snd = $DB->get_multi_result($ls_sql);
                            //print_r($qa_snd);
                            foreach($ls_snd as $ls_dta):
                                ?><option value="<?php echo $ls_dta['FormCallString']?>" <?php echo ((in_array($ls_dta['FormCallString'], $preSelected))? 'selected':'')?>><?php echo $ls_dta['FormName']?></option><?php
                            endforeach;
                            ?>
                            </optgroup>
                            <optgroup label="inActive Forms">
                            <?php
							$ls_sql = "SELECT * FROM CompanyForms WHERE FormActive='0' ORDER BY FormName ASC";
                            $ls_snd = $DB->get_multi_result($ls_sql);
                            //print_r($qa_snd);
                            foreach($ls_snd as $ls_dta):
								?><option value="<?php echo $ls_dta['FormCallString']?>" <?php echo ((in_array($ls_dta['FormCallString'], $preSelected))? 'selected':'')?>><?php echo $ls_dta['FormName']?></option><?php
                            endforeach;
                            ?>
                            </optgroup>
                            </select>	
                        </div>
                    </div>
                    <div class="col-lg-4">
                    	<div class="row">
                            <div class="col-6">
                                <div class="m-form__group form-group">
                                    <label for="">Gender:</label>
                                    <?php
                                    if(isset($preLoad['Gender'])) {
                                        $preSelected = $preLoad['Gender'];
                                    } else {
                                        $preSelected = array();	
                                    }
                                    ?>
                                    <div class="m-checkbox-list">
                                        <label class="m-checkbox">
                                            <input type="checkbox" name="Gender[]" class="genderRadio" value="M" <?php echo ((in_array('M', $preSelected))? 'checked':'')?>>
                                            <i class="fa fa-male"></i> Male            	
                                            <span></span>
                                        </label>
                                        <label class="m-checkbox">
                                            <input type="checkbox" name="Gender[]" class="genderRadio" value="F" <?php echo ((in_array('F', $preSelected))? 'checked':'')?>>
                                            <i class="fa fa-female"></i> Female
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="m-form__group form-group">
                                    <label for="">Age Range:</label>                                
                                    <div class="input-group m-input-group">
                                        <span class="input-group-addon">From:</span>
                                        <?php
                                        if(isset($preLoad['age_floor'])) {
                                            $floor = $preLoad['age_floor'];
                                        } else {
                                            $floor = '';
                                        }
                                        
                                        if(isset($preLoad['age_height'])) {
                                            $height = $preLoad['age_height'];
                                        } else {
                                            $height = '';
                                        }
                                        ?>
                                        <select name="age_floor" id="age_floor" class="form-control m-input form-control-sm">
                                            <option value=""></option>
                                        <?php for($i=18; $i<=99; $i++) {?>                                        
                                            <option value="<?php echo $i?>" <?php echo (($floor == $i)?'selected':'')?>><?php echo $i?></option>                        
                                        <?php } ?>                    
                                        </select>
                                    </div>                             
                                    <div class="input-group m-input-group">
                                        <span class="input-group-addon">&nbsp;&nbsp;To:</span>
                                        <select name="age_height" id="age_height" class="form-control m-input form-control-sm">
                                            <option value=""></option>
                                        <?php for($i=18; $i<=99; $i++) {?>                                        
                                            <option value="<?php echo $i?>" <?php echo (($height == $i)?'selected':'')?>><?php echo $i?></option>
                                        <?php } ?> 
                                        </select>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Assigned Relationship Manager</label>
                            <?php if(in_array(90, $userPerms)): ?>
                            <input type="hidden" name="Matchmaker_id[]" value="<?php echo $_SESSION['system_user_id']?>" />
                            <?php $preSelected = array($_SESSION['system_user_id']); ?>
                            <select class="form-control m-select2" id="Matchmaker_id" multiple="multiple" readonly>
                                <option value="<?php echo $_SESSION['system_user_id']?>" selected><?php echo $RECORD->get_userName($_SESSION['system_user_id'])?></option>
                            </select>
                            <?php else: ?>
                            <?php
                            if(isset($preLoad['Matchmaker_id'])) {
                                $preSelected = $preLoad['Matchmaker_id'];
                            } else {
                                $preSelected = array();	
                            }
                            ?>       
                            <select class="form-control m-select2" id="Matchmaker_id" name="Matchmaker_id[]" multiple="multiple">
                                <?php echo $RECORD->options_userSelectAll($preSelected)?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Assigned Network Developer</label>
                            <?php if(in_array(90, $userPerms)): ?>
                            <input type="hidden" name="Matchmaker2_id[]" value="<?php echo $_SESSION['system_user_id']?>" />
                            <?php $preSelected = array($_SESSION['system_user_id']); ?>
                            <select class="form-control m-select2" id="Matchmaker2_id" multiple="multiple" readonly>
                                <option value="<?php echo $_SESSION['system_user_id']?>" selected><?php echo $RECORD->get_userName($_SESSION['system_user_id'])?></option>
                            </select>
                            <?php else: ?>
                            <?php
                            if(isset($preLoad['Matchmaker2_id'])) {
                                $preSelected = $preLoad['Matchmaker2_id'];
                            } else {
                                $preSelected = array();	
                            }
                            ?>       
                            <select class="form-control m-select2" id="Matchmaker2_id" name="Matchmaker2_id[]" multiple="multiple">
                                <?php echo $RECORD->options_userSelectAll($preSelected)?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Assigned Market Director</label>                            
                            <?php if(in_array(89, $userPerms)): ?>  
                            <input type="hidden" name="Assigned_userID[]" value="<?php echo $_SESSION['system_user_id']?>" />
                            <?php $preSelected = array($_SESSION['system_user_id']); ?>     
                            <select class="form-control m-select2" id="Assigned_userID" multiple="multiple" readonly>
                                <option value="<?php echo $_SESSION['system_user_id']?>" selected><?php echo $RECORD->get_userName($_SESSION['system_user_id'])?></option>
                            </select>                            
                            <?php else: ?>
                            <?php
                            if(isset($preLoad['Assigned_userID'])) {
                                $preSelected = $preLoad['Assigned_userID'];
                            } else {
                                $preSelected = array();	
                            }
                            ?> 
                            <select class="form-control m-select2" id="Assigned_userID" name="Assigned_userID[]" multiple="multiple">
                                <?php echo $RECORD->options_userSelectAll($preSelected)?>
                            </select>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group m-form__group">
                            <label>Lead Source</label>        
                            <select class="form-control select2 " id="HearAboutUs" name="HearAboutUs[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['HearAboutUs'])) {
                                $preSelected = $preLoad['HearAboutUs'];
                            } else {
                                $preSelected = array('empty_result');	
                            }
							?><optgroup label="Active Sources"><?php
                            $ls_sql = "SELECT * FROM DropDown_LeadSource WHERE Source_status='1' ORDER BY Source_name ASC";
                            $ls_snd = $DB->get_multi_result($ls_sql);
                            //print_r($qa_snd);
                            foreach($ls_snd as $ls_dta):
								print_r($ls_dta);
                                ?><option value="<?php echo $ls_dta['Source_name']?>" <?php echo ((in_array($ls_dta['Source_name'], $preSelected))? 'selected':'')?>><?php echo $ls_dta['Source_name']?></option><?php
                            endforeach;
                            ?>
                            </optgroup>
                            <optgroup label="inActive Sources">
                            <?php
							$ls_sql = "SELECT * FROM DropDown_LeadSource WHERE Source_status='0' ORDER BY Source_name ASC";
                            $ls_snd = $DB->get_multi_result($ls_sql);
                            //print_r($qa_snd);
                            foreach($ls_snd as $ls_dta):
								?><option value="<?php echo $ls_dta['Source_name']?>" <?php echo ((in_array($ls_dta['Source_name'], $preSelected))? 'selected':'')?>><?php echo $ls_dta['Source_name']?></option><?php
                            endforeach;
                            ?>
                            </optgroup>
                            </select>	
                        </div>
                    </div>
                    <div class="col-lg-4">
                    	<div class="form-group m-form__group">
                            <label>Profile Text</label>
                            <div class="input-group">                            	
	                            <input type="text" name="ProfileText" id="ProfileText" class="form-control m-input" placeholder="search text..." value="<?php echo (isset($preLoad['ProfileText'])? $preLoad['ProfileText']:'')?>">
                                <span class="input-group-addon"><i class="flaticon-info"  data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="This will search all of the text responses from records trying to find a string match." data-original-title="Profile Text Search"></i></span>
							</div>                                
                        </div>
                        <div class="m-form__group form-group">
                            <div class="row">
                            	<div class="col-6">
                                    <label>Ranking</label>        
                                    <select class="form-control m-select2" id="prQuestion_664" name="prQuestion_664[]" multiple="multiple">
                                    <?php
                                    if(isset($preLoad['prQuestion_664'])) {
                                        $preSelected = $preLoad['prQuestion_664'];
                                    } else {
                                        $preSelected = array('empty_result');	
                                    }
                                    $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='664' ORDER BY QuestionsAnswers_order ASC";
                                    $qa_snd = $DB->get_multi_result($qa_sql);
                                    //print_r($qa_snd);
                                    foreach($qa_snd as $qa_dta):
                                        ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                                    endforeach;
                                    ?>
                                    </select>
								</div>
                                <div class="col-6">
                                	<div class="m-form__group form-group">
                                        <label>Open Records</label>
                                        <?php
                                        if(isset($preLoad['OpenRecordExclusive'])) {
                                            $preSelected = $preLoad['OpenRecordExclusive'];
                                        } else {
                                            $preSelected = array();	
                                        }
                                        ?>
                                        <div class="m-checkbox-list">                                            
                                            <label class="m-checkbox">
                                                <input type="checkbox" class="openRecordsFilter" name="OpenRecordExclusive[]" id="OpenRecordExclusive_1" value="1" <?php echo ((in_array('1', $preSelected))? 'checked':'')?>>
                                                 Include           	
                                                <span></span>
                                            </label>
                                            <label class="m-checkbox">
                                                <input type="checkbox" class="openRecordsFilter" name="OpenRecordExclusive[]" id="OpenRecordExclusive_2" value="2" <?php echo ((in_array('2', $preSelected))? 'checked':'')?>>
                                                 Exclude           	
                                                <span></span>
                                            </label>
                                            <label class="m-checkbox">
                                                <input type="checkbox" class="openRecordsFilter" name="OpenRecordExclusive[]" id="OpenRecordExclusive_3" value="3" <?php echo ((in_array('3', $preSelected))? 'checked':'')?>>
                                                 Exclusive           	
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
								</div>                                
							</div>                                                                    
                        </div>
                        <div class="form-group m-form__group">
                            <label>Income</label>        
                            <select class="form-control m-select2" id="prQuestion_631" name="prQuestion_631[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['prQuestion_631'])) {
                                $preSelected = $preLoad['prQuestion_631'];
                            } else {
                                $preSelected = array();	
                            }
                            $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='631' ORDER BY QuestionsAnswers_order ASC";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            ob_start();
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div> 
                                                
                        <div class="form-group m-form__group">
                            <label>Lead Status</label>        
                            <select class="form-control m-select2" id="LeadStages_id" name="LeadStages_id[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['LeadStages_id'])) {
                                $preSelected = $preLoad['LeadStages_id'];
                            } else {
                                $preSelected = array('empty_result');	
                            }
                            $qa_sql = "SELECT * FROM LeadStages ORDER BY LeadStages_id";
                            $qa_snd = $DB->get_multi_result($qa_sql);
                            //print_r($qa_snd);
                            foreach($qa_snd as $qa_dta):
                                ?><option value="<?php echo $qa_dta['LeadStages_id']?>" <?php echo ((in_array($qa_dta['LeadStages_id'], $preSelected))? 'selected':'')?>><?php echo $RECORD->get_leadStage($qa_dta['LeadStages_id'])?></option><?php
                            endforeach;
                            ?>
                            </select>	
                        </div> 
                                                
                    </div>
				</div>                    
            
            
            </div>
		</div>
        <!-- END BASIC FILTERS -->
        
        <div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilters_Dates" style="margin-bottom:2px;">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="la la-filter"></i>
                        </span>
                        <h3 class="m-portlet__head-text">Date Filters</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse/Expand" data-skin="dark">
                                <i class="la la-angle-down"></i>
                            </a>
                        </li>              
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body" style="">
            	<div class="row">
					<div class="col-lg-4">
                    	<div class="form-group m-form__group">
                            <label>Date Created</label>
                            <?php
                            if(isset($preLoad['DateCreated'])) {
                                $preSelected = $preLoad['DateCreated'];
                                $buttonOn = true;
                            } else {
                                $preSelected = '';	
                                $buttonOn = false;
                            }
							if(isset($preLoad['DateCreated_dynamic'])) {
                                $buttonOn2 = true;
                            } else {
                                $buttonOn2 = false;
                            }
							
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-success" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Force this time frame to be dynamic and based on the date of the list viewed." data-original-title="Date Created - Dynamic">
                                        <input type="checkbox" name="DateCreated_dynamic" id="DateCreated_dynamic" <?php echo (($buttonOn2)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                                <input type="text" name="DateCreated" id="DateCreated" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Activate this field in the search. If this is not selected, the search is ignore this field." data-original-title="Date Created">
                                        <input type="checkbox" id="DateCreated_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                    	<div class="form-group m-form__group">
                            <label>Last Update</label>
                            <?php
                            if(isset($preLoad['DateUpdated'])) {
                                $preSelected = $preLoad['DateUpdated'];
                                $buttonOn = true;
                            } else {
                                $preSelected = '';	
                                $buttonOn = false;
                            }
							if(isset($preLoad['DateUpdated_dynamic'])) {
                                $buttonOn2 = true;
                            } else {
                                $buttonOn2 = false;
                            }
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-success" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Force this time frame to be dynamic and based on the date of the list viewed." data-original-title="Date Updated - Dynamic">
                                        <input type="checkbox" name="DateUpdated_dynamic" id="DateUpdated_dynamic" <?php echo (($buttonOn2)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                                <input type="text" name="DateUpdated" id="DateUpdated" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Activate this field in the search. If this is not selected, the search is ignore this field." data-original-title="Date Updated">
                                        <input type="checkbox" id="DateUpdated_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                        </div>                    
                    </div>
                    <div class="col-lg-4">
                    	<div class="form-group m-form__group">
                            <label>Last Intro</label>
                            <?php
                            if(isset($preLoad['LastIntroDate'])) {
                                $preSelected = $preLoad['LastIntroDate'];
                                $buttonOn = true;
                            } else {
                                $preSelected = '';	
                                $buttonOn = false;
                            }
							if(isset($preLoad['LastIntroDate_dynamic'])) {
                                $buttonOn2 = true;
                            } else {
                                $buttonOn2 = false;
                            }
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-success" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Force this time frame to be dynamic and based on the date of the list viewed." data-original-title="Last Intro - Dynamic">
                                        <input type="checkbox" name="LastIntroDate_dynamic" id="LastIntroDate_dynamic" <?php echo (($buttonOn2)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                                <input type="text" name="LastIntroDate" id="LastIntroDate" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Activate this field in the search. If this is not selected, the search is ignore this field." data-original-title="Last Intro">
                                        <input type="checkbox" id="LastIntroDate_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                        </div>                    
                    </div>
				</div>            
            	<div class="row">
                	<div class="col-lg-4">
                        <div class="form-group m-form__group">
                            <label>Contract Started</label>
                            <?php
                            if(isset($preLoad['prQuestion_676'])) {
                                $preSelected = $preLoad['prQuestion_676'];
                                $buttonOn = true;
                            } else {
                                $preSelected = '';	
                                $buttonOn = false;
                            }
							if(isset($preLoad['contractStart_dynamic'])) {
                                $buttonOn2 = true;
                            } else {
                                $buttonOn2 = false;
                            }
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-success" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Force this time frame to be dynamic and based on the date of the list viewed." data-original-title="Contract End Date - Dynamic">
                                        <input type="checkbox" name="contractStart_dynamic" id="contractStart_dynamic" <?php echo (($buttonOn2)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                                <input type="text" name="prQuestion_676" id="prQuestion_676" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand">
                                        <input type="checkbox" id="contractStart_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group m-form__group">
                            <label>Contract End Date</label>
                            <?php
                            if(isset($preLoad['prQuestion_677'])) {
                                $preSelected = $preLoad['prQuestion_677'];
                                $buttonOn = true;
                            } else {
                                $preSelected = '';	
                                $buttonOn = false;
                            }
							if(isset($preLoad['contractEnd_dynamic'])) {
                                $buttonOn2 = true;
                            } else {
                                $buttonOn2 = false;
                            }
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-success" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Force this time frame to be dynamic and based on the date of the list viewed." data-original-title="Contract End Date - Dynamic">
                                        <input type="checkbox" name="contractEnd_dynamic" id="contractEnd_dynamic" <?php echo (($buttonOn2)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                                <input type="text" name="prQuestion_677" id="prQuestion_677" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand" data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Activate this field in the search. If this is not selected, the search is ignore this field." data-original-title="Contract End Date">
                                        <input type="checkbox" id="contractEnd_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="m-form__group form-group">
                            <label>Valid Membership:</label>
                            <?php
                            if(isset($preLoad['ValidMembership'])) {
                                $preSelected = $preLoad['ValidMembership'];
                            } else {
                                $preSelected = array();	
                            }
                            ?>
                            <div class="m-checkbox-list">
                                <label class="m-checkbox">
                                    <input type="checkbox" name="ValidMembership[]" id="validRadio" value="Y" <?php echo ((in_array('Y', $preSelected))? 'checked':'')?>>
                                     Valid Membership <i class="fa 	fa-thumbs-o-up" data-toggle="m-tooltip" title="only include records with valid membership dates"></i>           	
                                    <span></span>
                                </label>
                            </div>
                        </div>	
                    </div>
                </div>
            </div>
		</div>
        <!-- END DATE FILTERS -->
        
        <div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilters_Location" style="margin-bottom:2px;">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="la la-filter"></i>
                        </span>
                        <h3 class="m-portlet__head-text">Location Filters</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                        <li class="m-portlet__nav-item">
                            <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse/Expand" data-skin="dark">
                                <i class="la la-angle-down"></i>
                            </a>
                        </li>              
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body" style="">
            	<div class="row">
                	<div class="col-lg-3">
                    	<div class="form-group m-form__group">
                            <label>Locations</label>
                            
                            <select class="form-control m-select2" id="Offices_id" name="Offices_id[]" multiple="multiple">
                            <?php
                            if(isset($preLoad['Offices_id'])) {
                                $preSelected = $preLoad['Offices_id'];
                            } else {
                                $preSelected = array();	
                            }
                            $sql = "SELECT * FROM Offices ORDER BY Offices_id";
                            $snd = $DB->get_multi_result($sql);
                            foreach($snd as $dta):
                                ?><option value="<?php echo $dta['Offices_id']?>" <?php echo ((in_array($dta['Offices_id'], $preSelected))? 'selected':'')?>><?php echo $dta['office_Name']?></option><?php
                            endforeach;
                            ?>
                            </select>
                        </div>                    
                    </div>
                    <div class="col-lg-3">
                    	<div class="form-group m-form__group">
                            <label>State/Province</label> 
                            <?php
                            if(isset($preLoad['State'])) {
                                $preSelected = $preLoad['State'];
                            } else {
                                $preSelected = array();	
                            }
                            ?>       
                            <select class="form-control m-select2" id="State" name="State[]" multiple="multiple">
                            <?php echo $RECORD->options_allStates($preSelected)?>
                            </select>
                        </div>
                        <div class="form-group m-form__group">
                            <label>Country</label> 
                            <?php
                            if(isset($preLoad['Country'])) {
                                $preSelected = $preLoad['Country'];
                            } else {
                                $preSelected = array();	
                            }
                            ?>       
                            <select class="form-control m-select2" id="Country" name="Country[]" multiple="multiple">
                            <?php echo $RECORD->options_allCountries($preSelected)?>
                            </select>
                        </div>
					</div>
                    <div class="col-lg-6">
                    	<div class="form-group m-form__group">
                            <label>Distance</label>
                            <?php
                            if(isset($preLoad['DistanceSearch'])) {
                                $preSelected = $preLoad['DistanceSearch'];
                                $locName = $preLoad['geo_location'];
                                $locLat = $preLoad['geo_lat'];
                                $locLng = $preLoad['geo_lng'];
                                $default = 1;
                                $buttonOn = true;
                            } else {	
                                $preSelected = '';
                                $locName = $defaultSearch['name'];
                                $locLat = $defaultSearch['lat'];
                                $locLng = $defaultSearch['lng'];
                                $default = 0;
                                $buttonOn = false;
                            }
                            ?>
                            <div class="input-group">            
                                <span class="input-group-addon"><i class="flaticon-placeholder"></i></span>
                                <input type="text" name="DistanceSearch" id="DistanceSearch" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> placeholder="X"/>
                                <span class="input-group-addon" id="default_addressDisplayArea" style="display:<?php echo (($buttonOn)? 'none':'default')?>;">Miles from <?php echo $defaultSearch['name']?></span>
                                <span class="input-group-addon" id="custom_addressDisplayArea" style="display:<?php echo (($buttonOn)? 'default':'none')?>;">Miles from <?php echo $locName?></span>                            
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-secondary active" id="customCenterEdit" style="display:<?php echo (($buttonOn)? 'none':'default')?>;" data-toggle="modal" data-target="#customCenterModal">Edit</button>
                                </span>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-secondary active" id="customCenterCancel" style="display:<?php echo (($buttonOn)? 'default':'none')?>;" onclick="cancelCustomCenter()"><i class="la la-ban"></i></button>
                                </span>
                                <span class="input-group-addon">
                                    <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand">
                                        <input type="checkbox" name="DistanceSearch_activate" id="DistanceSearch_activate" <?php echo (($buttonOn)? 'checked':'')?> value="1">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <span class="m-form__help">
                                Must check box to include this filter.
                            </span>
                            <input type="hidden" name="geo_overwrite" id="geo_overwrite" value="<?php echo $default?>">
                            <input type="hidden" name="geo_location" id="geo_location" value="<?php echo $locName?>">
                            <input type="hidden" name="geo_lat" id="geo_lat" value="<?php echo $locLat?>">
                            <input type="hidden" name="geo_lng" id="geo_lng" value="<?php echo $locLng?>">
                        </div>                    
                    </div>
				</div>
            </div>
		</div>
        <!-- END LOCATION FILTERS -->
        
        <div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilters_Custom" style="margin-bottom:2px;">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon">
                            <i class="la la-filter"></i>
                        </span>
                        <h3 class="m-portlet__head-text">Custom Filters</h3>
                    </div>
                </div>
                <div class="m-portlet__head-tools">
                    <ul class="m-portlet__nav">
                    	<li class="m-portlet__nav-item">
                            <a href="javascript:;" data-toggle="modal" data-target="#customSearchFieldModal" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--hover-primary m-btn--icon m-btn--icon-only m-btn--pill">
                                <i class="la la-plus-square-o"></i>
                            </a>                            
						</li>
                        <li class="m-portlet__nav-item">
                            <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse/Expand" data-skin="dark">
                                <i class="la la-angle-down"></i>
                            </a>
                        </li>              
                    </ul>
                </div>
            </div>
            <div class="m-portlet__body" style="">
            	<div class="row" id="customFieldFormArea">
<?php
$openCustom = false;
$exclude_ids = array(677,676,657,664,631);
$q_sql = "SELECT * FROM Questions WHERE Questions_text != '' AND Questions_active='1' AND QuestionTypes_id IN (3,4,5,6) AND Questions_id NOT IN (".implode(",", $exclude_ids).") ORDER BY Questions_order ASC";
$q_snd = $DB->get_multi_result($q_sql);
foreach($q_snd as $q_dta):
	$flname = $q_dta['MappedField'];
	if(isset($preLoad[$flname])) {
		//print_r($q_dta);
		//print_r($preLoad[$flname]);
		//$whereClause[] = "PersonsProfile.".$flname." IN ('".implode("','", $$preLoad[$flname])."')";
		?><div class="col-lg-3 customFormItemWrapper" data-id="<?php echo $q_dta['Questions_id']?>"><?php
		?><div class="pull-right"><a href="javascript:removeFormBlock(<?php echo $q_dta['Questions_id']?>);"><i class="fa fa-close"></i></a></div><?php
		$FORMS->form_checkboxField($q_dta['MappedField'], $q_dta['Questions_text'], implode("|", $preLoad[$flname]), false, $FORMS->get_fieldOptions($q_dta['Questions_id']));
		?></div><?php
		$openCustom = true;
	}
endforeach;
?>                
                
                </div>
            </div>
		</div>
        <!-- END CUSTOM FILTERS -->
             	    
    </div>    
    <div class="m-portlet__foot m-portlet__foot--fit" style="padding-top:0px; padding-bottom:0px;">
        <div class="m-form__actions m-form__actions--right">
            <div class="row">
                <div class="col m--align-left">
                    <button type="button" onclick="genSearchQuery(1)" id="button-submitSearch" class="btn btn-brand m-btn">
                        Submit Search <i class="fa fa-search"></i>
                    </button>
                    <button type="button" onclick="clearSearch()" class="btn btn-secondary">
                        Clear Search <i class="fa fa-remove"></i>
                    </button>
                </div>
                <div class="col m--align-right">
                    <button type="button" class="btn btn-success" id="button-save-search" data-toggle="modal" data-target="#saveListModal" disabled="disabled">
                        Save Search <i class="fa fa-save"></i>
                    </button>
                    <button type="button" class="btn btn-metal" id="button-load-search" data-toggle="modal" data-target="#loadSavedSearchModal">
                        Load Search <i class="fa fa-folder-open-o"></i>
                    </button>
                    <?php if($myTableConfig): ?>
                    <button type="button" class="btn btn-danger" id="button-clean-search" onclick="clearCustomColumns()" data-toggle="m-tooltip" title="" data-original-title="Clear custom columns">
                        <i class="fa fa-remove"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="fieldConfig" value='<?php echo $tableConfig_js?>' />
    <input type="hidden" name="gid" id="gid" value="<?php echo $GROUP_ID?>" />
    </form>
</div>
<!-- END: SEARCH FILTER PORTLET -->

<?php if($runPreload): ?>
<div class="m-alert m-alert--icon alert alert-primary" role="alert">
	<div class="m-alert__icon">
		<i class="la la-list-u"></i>
	</div>
	<div class="m-alert__text">
		<strong><?php echo $json['name']?></strong><br /><small><?php echo $json['desc']?></small>
	</div>
	<div class="m-alert__actions" style="width:600px;">
    	<?php if(in_array(87, $userPerms)): ?>
        <form action="/searchExport.php" method="post" target="_blank">
		<button type="button" class="btn btn-info btn-sm m-btn m-btn--pill m-btn--wide" data-toggle="modal" data-target="#saveListModal">List Info</button>
        <a href="/fullsearch" class="btn btn-secondary btn-sm m-btn m-btn--pill m-btn--wide">Expand Search</a>
        <button type="button" onclick="deleteSavedSearch(<?php echo $GROUP_ID?>)" class="btn btn-danger btn-sm m-btn m-btn--pill m-btn--wide">
			Delete List
		</button>
        <?php echo $SESSION->renderToken()?>
        <input type="hidden" name="SID" value="<?php echo $GROUP_ID?>" />
        <button type="submit" class="btn btn-secondary btn-sm m-btn m-btn--pill m-btn--wide">Export to CSV</button>
        </form>
        <?php endif; ?>      
	</div>
</div>
<?php endif; ?>

<div class="modal fade" id="listCatManager" role="dialog" aria-labelledby="listCatManagerLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="listCatManagerLabel">List Category Manger</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            
<div class="m-list-timeline">
    <div class="m-list-timeline__items" id="catList-wrapper">            
<?php
$lc_sql = "SELECT * FROM Groups_Categories ORDER BY Groups_Category_Order ASC";
$lc_snd = $DB->get_multi_result($lc_sql);
foreach($lc_snd as $lc_dta):
	$lcc_sql = "SELECT COUNT(*) as count FROM `Groups` WHERE `Groups_catID`='".$lc_dta['Groups_catID']."' AND `Groups_active`='1' AND `Groups_source` IN (2,3)";
	$lcc_snd = $DB->get_single_result($lcc_sql);
?>
		<div class="m-list-timeline__item">
            <span class="m-list-timeline__badge"></span>
            <span class="m-list-timeline__text">
                <?php echo $lc_dta['Groups_Category_Name']?>
                <input type="hidden" class="cat_id" value="<?php echo $lc_dta['Groups_catID']?>" />
            </span>
            <span class="m-list-timeline__time"><?php echo $lcc_snd['count']?></span>
        </div>
<?php
endforeach;
?>
    </div>
</div>

            </div>
            <div class="modal-footer">
            	<button type="button" onclick="addNewCategory();" class="btn btn-primary">Add Category <i class="la la-plus"></i></button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>              

<div class="modal fade" id="welcomeModal" tabindex="-1" role="dialog" aria-labelledby="welcomeModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog  modal-lg " role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="officeModalLabel">KISS HELP VIDEO</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Dismiss</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customCenterModal" role="dialog" aria-labelledby="customCenterModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customSearchFieldModalLabel">Custom Search Center </h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form m-form--fit m-form--label-align-right" id="newCenterForm" onsubmit="return setNewCenter();">
				<div class="form-group m-form__group">
					<label for="newCenter">
						Search Center Address
					</label>
					<input type="text" class="form-control m-input" id="newCenter" autocomplete="off">
					<span class="m-form__help">
						Enter a city & state to set the center of your search
					</span>
				</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="setNewCenter()">Set Search Center</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="saveListModal" role="dialog" aria-labelledby="saveListModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="saveListModalLabel">Save Search Results as List</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form m-form--fit m-form--label-align-right" id="saveListForm" onsubmit="return false();">
					<input type="hidden" name="save_ListID" id="save_ListID" value="<?php echo $GROUP_ID?>" />
                    <div class="form-group m-form__group">
                        <label for="save_ListName">
                            List Category
                        </label>
                        <select class="form-control m-input" id="save_catID" name="save_catID" autocomplete="off">
                        	
<?php
$sc_sql = "SELECT * FROM Groups_Categories ORDER BY Groups_Category_Order ASC";
$sc_snd = $DB->get_multi_result($sc_sql);
foreach($sc_snd as $sc_dta):
	?><option value="<?php echo $sc_dta['Groups_catID']?>" <?php echo (($sc_dta['Groups_catID'] == $json['cat'])? 'selected':'')?>><?php echo $sc_dta['Groups_Category_Name']?></option><?php
endforeach;
?>                        
                        </select>
                        <span class="m-form__help">
                            This is the folder/category for this list
                        </span>
                    </div>
                    <div class="form-group m-form__group">
                        <label for="save_ListName">
                            List Name
                        </label>
                        <input type="text" class="form-control m-input" id="save_ListName" name="save_ListName" autocomplete="off" value="<?php echo $json['name']?>">
                        <span class="m-form__help">
                            Enter the name of this list. This will be the title of the list when loading it into whatever module it is brogut into.
                        </span>
                    </div>
                    
                    <div class="form-group m-form__group">
                        <label for="save_ListDesc">
                            Description
                        </label>
                        <textarea class="form-control m-input" id="save_ListDesc" name="save_ListDesc" autocomplete="off"><?php echo $json['desc']?></textarea>
                        <span class="m-form__help">
                            Enter a brief description that will assist in identifying this list from others.
                        </span>
                    </div>
                    
                    <?php if($GROUP_ID == 0): ?>
                    <div class="m-form__group form-group">
                        <label for="">
                            Save list as:
                        </label>
                        <div class="m-checkbox-list">
                            <label class="m-checkbox" title="These are lists that can be recalled and loaded into the search results viewer.">
                                <input type="checkbox" class="exportType" name="exportType[]" value="standard">
                                Standard Data List
                                <span></span>
                            </label>
                            <label class="m-checkbox <?php echo ((!$exportToMarketing)? 'm-checkbox--state-danger m-checkbox--disabled m--font-danger':'')?>" title="These are lists that can be sent email deployments and are accessable via the marketing tool">
								<input type="checkbox" class="exportType" name="exportType[]" value="marketing" <?php echo ((!$exportToMarketing)? 'disabled="disabled"':'')?>>
                                Marketing List Data
                                <span></span>
                            </label>
                        </div>
                        <?php if(!$exportToMarketing): ?>
                        <span class="m-form__help m--font-danger">
							We have detected that the list you are attempting to save does not have the Email field included. Without this field included, the Marketing List save is not available
						</span>
                        <?php else: ?>
                        <span class="m-form__help">
							This will make this list available to be added to a marketing list.
						</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>				                    
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" onclick="saveCustomList()">Save</button>
			</div>
		</div>
	</div>
</div>
<?php
ob_start();
$sc_sql = "SELECT * FROM Groups_Categories ORDER BY Groups_Category_Order ASC";
$sc_snd = $DB->get_multi_result($sc_sql);
foreach($sc_snd as $sc_dta):
	?><optgroup label="<?php echo $sc_dta['Groups_Category_Name']?>"><?php
	if($sc_dta['Groups_catID'] == 0):
		$ss_sql = "SELECT * FROM `Groups` WHERE `Groups_source` IN (2,3) AND `Groups_catID`='".$sc_dta['Groups_catID']."' AND `Groups_active`='1' AND `Groups_createdBy`='".$_SESSION['system_user_id']."' ORDER BY `Groups_name` ASC";
	else:
		$ss_sql = "SELECT * FROM `Groups` WHERE `Groups_source` IN (2,3) AND `Groups_catID`='".$sc_dta['Groups_catID']."' AND `Groups_active`='1'  ORDER BY `Groups_name` ASC";
	endif;
	echo "SAVED SEARCH SQL: ".$ss_sql."<br>";
	$ss_snd = $DB->get_multi_result($ss_sql);
	if(!isset($ss_snd['empty_result'])):
		foreach($ss_snd as $ss_dta):
		?><option value="<?php echo $ss_dta['Groups_id']?>"><?php echo $ss_dta['Groups_name']?></option><?php
		endforeach;
	endif;
	?></optgroup><?php
endforeach;
$g_select_code = ob_get_clean();
?>
<div class="modal fade" id="loadSavedSearchModal" role="dialog" aria-labelledby="loadSavedSearchModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="loadSavedSearchModalLabel">Load Saved Search </h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form m-form--fit m-form--label-align-right" id="newCenterForm" onsubmit="return setNewCenter();">
				<div class="form-group m-form__group">
					<label for="savedSearch">
						Saved Search
					</label>
                    <select name="savedSearch" id="savedSearch" class="form-form-control m-select2" autocomplete="off" style="width:100%;">
                    <option value=""></option>
					<?php echo $g_select_code?>
                    </select>
				</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="loadSavedSearch()">Load Saved</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="customSearchFieldModal" role="dialog" aria-labelledby="customSearchFieldModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customSearchFieldModalLabel"><i class="flaticon-speech-bubble-1"></i> Add Custom Field <small><span id="display_FoundNumber"></span></small></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			
			<div class="form-group m-form__group">
				<label for="customQuestionSelect">
					Select Question to Add to Search Form
				</label>
				<select class="form-control m-input m-input--square" id="customQuestionSelect">
					<option value=""></option>
<?php
$exclude_ids = array(677,676,657,664,631);
$sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id NOT IN (15) ORDER BY QuestionsCategories_id ASC";
$snd = $DB->get_multi_result($sql);
foreach($snd as $dta):
	$catID = $dta['QuestionsCategories_id'];
	$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$catID."' AND Questions_text != '' AND Questions_active='1' AND QuestionTypes_id IN (3,4,5,6) AND Questions_id NOT IN (".implode(",", $exclude_ids).") ORDER BY Questions_order ASC";
	$q_found = $DB->get_multi_result($q_sql, true);
	if($q_found):
		?><optgroup title="<?php echo $dta['QuestionsCategories_name']?>" label="<?php echo $dta['QuestionsCategories_name']?>"><?php
		$q_snd = $DB->get_multi_result($q_sql);
		foreach($q_snd as $q_dta):
			if(!in_array($q_dta['Questions_id'], $exeptions)) {
				$fieldName = 'prQuestion_'.$q_dta['Questions_id'];
				$fieldValue = $p_snd[$fieldName];
				?><option value="<?php echo $q_dta['Questions_id']?>"><?php echo $q_dta['Questions_text']?></option><?php
			}
		endforeach;
		?></optgroup><?php
	endif;				
endforeach;	
?>
				</select>
			</div>
			
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="AddToSearchForm()">Add to Form</button>
			</div>
		</div>
	</div>
</div> 
<!-- START: RESULTS TABLE -->
<?php

?>
<!-- START: HIDDEN QUERY FORM -->
<div class="row m--hide">
	<div class="col-2">&nbsp;</div>
	<div class="col-8">
		<textarea name="queryInput" id="queryInput" class="form-control m-input" style="height:250px;"><?php echo $json['sql']?></textarea>
	</div>
	<div class="col-2">&nbsp;</div>
</div>
<?php
echo $DATATABLE->render_datatable("mySearchTable", '<i class="flaticon-users"></i> Search Results - <small>filtered from above</small><div class="pull-right" id="searchSpinner"></div>', "/ajax/getTableData.php", '', $tableFields, 'Person_id', 'LastName', 'asc', 10, "$('#button-submitSearch').removeClass('m-btn--custom m-loader m-loader--light m-loader--left'); $('#searchSpinner').html('');", false, 'false', 'false');
?>
</div>
<?php

// PORTLET EXPAND/COLLAPSE STATUS //
$fieldKeys = array_keys($preLoad);

$openDistance = false;
if((in_array('DistanceSearch', $fieldKeys)) 
	|| (in_array('State', $fieldKeys)) 
	|| (in_array('Offices_id', $fieldKeys))
	|| (in_array('Country', $fieldKeys))):
	$openDistance = true;
endif;

$openDates = false;
if((in_array('DateCreated', $fieldKeys)) 
	|| (in_array('DateUpdated', $fieldKeys)) 
	|| (in_array('LastIntroDate', $fieldKeys)) 
	|| (in_array('prQuestion_676', $fieldKeys)) 
	|| (in_array('prQuestion_677', $fieldKeys)) 
	|| (in_array('ValidMembership', $fieldKeys))):
		$openDates = true;
endif;

$openQuick = false;
if(
	((in_array('Person_id', $fieldKeys)) && ($preLoad['Person_id'] != '')) 
	|| ((in_array('Phone_raw', $fieldKeys)) && ($preLoad['Phone_raw'] != ''))
	|| ((in_array('Email', $fieldKeys)) && ($preLoad['Email'] != ''))
	|| ((in_array('FirstName', $fieldKeys)) && ($preLoad['FirstName'] != ''))
	|| ((in_array('LastName', $fieldKeys)) && ($preLoad['LastName'] != ''))
):
	$openQuick = true;
endif;




?>
<?php endif; ?>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<script>
function AddToSearchForm() {
	var qid = $('#customQuestionSelect').val();
	$.post('/ajax/searchForMatch.php?action=loadCustomQuestion', {
		qid: qid,
		block: true
	}, function(data) {
		$('#customFieldFormArea').append(data);
	});	
}
function removeFormBlock(qid) {
	$('.customFormItemWrapper').each(function() {
		tqid = $(this).attr("data-id");
		if(tqid == qid) {
			$(this).remove();
		}
	});	
}
function openListCats() {
	$('#listCatManager').modal('show');
}
function addNewCategory() {
	var newCat = prompt('Please enter the name of this new catgory');
	//alert(newCat);
	if(newCat.length>1) {
		$.post('/ajax/fullSearch.php?action=createnewCat', {
			catName: newCat,
			kiss_token: '<?php echo $SESSION->createToken()?>'	
		}, function(data) {
			//loadCatLists();
			document.location.reload(true);	
		});
	}
}
function loadCatLists() {
	$.post('/ajax/fullSearch.php?action=loadListCats', {
		kiss_token: '<?php echo $SESSION->createToken()?>'	
	}, function(data) {
		$('#catList-wrapper').html(data.html);		
	}, "json");
}
function deleteSavedSearch(gid) {
	var choice = confirm('Are you sure you want to delete this saved search? This action cannot be undone.');
	if(choice) {
		var conf = prompt('please type "DELETE" (all caps) to confirm');
		if(conf == 'DELETE') {
		 	$.post('/ajax/fullSearch.php?action=deleteCustomResults', {
				gid: gid,
				kiss_token: '<?php echo $SESSION->createToken()?>'	
			}, function(data) {
				alert('Saved Search Deleted');
				document.location.href='/fullsearch';				
			});
		}
	}
}
function loadSavedSearch() {
	var gid = $('#savedSearch').val();
	document.location.href='/fullsearch/'+gid;
}
function saveCustomList() {
	var error = 0;
	var errorTXT = '';
	var expType = new Array();
	var index = 0;
	$('.exportType').each(function() {
		if($(this).is(':checked')) {
			expType[index] = $(this).val();
			index++;
		}
	});
	var searchFormData = $('#filterSearhForm').serializeArray();
	searchFormData.push({name: 'name', value: $('#save_ListName').val()});
	searchFormData.push({name: 'desc', value: $('#save_ListDesc').val()});
	searchFormData.push({name: 'etype', value: expType});
	searchFormData.push({name: 'sql', value: $('#queryInput').val()});
	searchFormData.push({name: 'cat', value: $('#save_catID').val()});
	searchFormData.push({name: 'lid', value: $('#save_ListID').val()});
	<?php if($GROUP_ID == 0): ?>
	if(index == 0) {
		error = 1;
		errorTXT += 'You must select the type of save\n';
	}
	<?php endif; ?>
	var gname = $('#save_ListName').val();
	if(gname == '') {
		error = 1;
		errorTXT += 'Must enter a name for the list\n';
	}
	if(error == 1) {
		alert(errorTXT);
	} else {
		$.post('/ajax/fullSearch.php?action=saveCustomResults', searchFormData, function(data) {
			console.log(data);
			document.location.href='/fullsearch/'+data.group;
		}, "json");
	}
}

function clearCustomColumns() {
	$('#button-clean-search').addClass('m-btn--custom m-loader m-loader--light m-loader--left');
	$.post('/ajax/fullSearch.php?action=clearCols', {
		u: '<?php echo $_SESSION['system_user_id']?>',
		t: 'mySearchTable'
	}, function(data) {
		document.location.reload(true);
	});
}
function cancelCustomCenter() {
	$('#custom_addressDisplayArea').hide();
	$('#default_addressDisplayArea').show();
	$('#geo_overwrite').val(0);	
	$('#customCenterCancel').hide();
	$('#customCenterEdit').show();
	$('#geo_lat').val('<?php echo $defaultSearch['lat']?>');
	$('#geo_lng').val('<?php echo $defaultSearch['lng']?>');
	$('#geo_location').val('<?php echo $defaultSearch['name']?>');
}
function setNewCenter() {
	mApp.block("#customCenterModal .modal-content", {
		overlayColor: "#CCCCCC",
		type: "loader",
		state: "success",
		size: "lg"
	});
	//var formData = $('#newCenterForm').serializeArray();
	$.post('/ajax/searchForMatch.php?action=getNewCenter', {
		location: $('#newCenter').val()		
	}, function(data) {
		$('#customCenterModal').modal('hide');
		$('#newCenter').val('');
		if(data.code == '401') {
			alert('Unable to Geo-Locate address provided. Please make sure it is spelled correctly and if needed you can add a zip code that can greatly assist in geo-location.');
		} else {
			$('#geo_lat').val(data.lat);
			$('#geo_lng').val(data.lng);
			$('#geo_overwrite').val(1);	
			var customLocation = 'miles from '+data.city+', '+data.state+' '+data.country;
			$('#default_addressDisplayArea').hide();
			$('#custom_addressDisplayArea').html(customLocation);
			$('#geo_location').val(data.city+', '+data.state+' '+data.country);
			$('#custom_addressDisplayArea').show();
			$('#customCenterEdit').hide();
			$('#customCenterCancel').show();
		}
		mApp.unblock('#customCenterModal .modal-content');
	}, "json");
	return false;
}
function genSearchQuery(empty) {
	$('#button-submitSearch').addClass('m-btn--custom m-loader m-loader--light m-loader--left');
	$('#searchSpinner').html('<div class="m-loader m-loader--primary" style="width: 20px; display: inline-block;"></div> Loading Search Results...');
	var searchFormData = $('#filterSearhForm').serializeArray();
	console.log(searchFormData);
    
	$.post('/ajax/fullSearch.php?action=query', searchFormData, function(data) {
	//$.post('/ajax/fullSearch.php?action=loadCustomResults', searchFormData, function(data) {
		$('#queryInput').val(data.sql);
		if(empty == 1) {
			$('#button-save-search').prop('disabled', false);
			//$('#button-load-search').prop('disabled', false);
		}
		console.log(data.string);
		console.log(data.sql);
		datatable.setDataSourceParam("query", { SQL:data.sql, EmployeeID:<?php echo $_SESSION['system_user_id']?>, Encoded: 1 });			
		datatable.load();		
	}, "json");	
}
function clearSearch() {
	$('#FirstName').val('');
	$('#LastName').val('');
	$('#Email').val('');
	$('#age_height').val('');
	$('#age_floor').val('');
	$('#Person_id').val('');
	$('#ProfileText').val('');
	$('#Offices_id').val(null).trigger('change');
	$('#Pods_id').val(null).trigger('change');
	$('#PersonsTypes_id').val(null).trigger('change');
	$('#prQuestion_657').val(null).trigger('change');
	$('#Color_id').val(null).trigger('change');
	$('.genderRadio').each(function() {
		$(this).prop('checked', false);
	});
	$('#validRadio').prop('checked', false);	
	$('#prQuestion_664').val(null).trigger('change');
	$('#prQuestion_631').val(null).trigger('change');
	<?php if(in_array(90, $userPerms)): ?>
	$('#Matchmaker_id').val(null).trigger('change');
	$('#Matchmaker2_id').val(null).trigger('change');
	<?php endif; ?>

    
	$('#LeadStages_id').val(null).trigger('change');
	$('#HearAboutUs').val(null).trigger('change');
	$('#FormID').val(null).trigger('change');
	$('#Country').val(null).trigger('change');
	
	$('#prQuestion_676').val('');
	$('#prQuestion_676').prop('disabled', true);
	$('#contractStart_activate').prop('checked', false);
	$('#contractStart_dynamic').prop('checked', false);
	
	$('#prQuestion_677').val('');
	$('#prQuestion_677').prop('disabled', true);
	$('#contractEnd_activate').prop('checked', false);
	$('#contractEnd_dynamic').prop('checked', false);
	
	$('#DateCreated').val('');
	$('#DateCreated').prop('disabled', true);
	$('#DateCreated_activate').prop('checked', false);
	$('#DateCreated_dynamic').prop('checked', false);
	
	$('#DateUpdated').val('');
	$('#DateUpdated').prop('disabled', true);
	$('#DateUpdated_activate').prop('checked', false);
	$('#DateUpdated_dynamic').prop('checked', false);
	
	$('#LastIntroDate').val('');
	$('#LastIntroDate').prop('disabled', true);
	$('#LastIntroDate_activate').prop('checked', false);
	$('#LastIntroDate_dynamic').prop('checked', false);
	
	$('#customFieldFormArea').html('');
	<?php if(in_array(89, $userPerms)): ?>
	$('#Assigned_userID').val(null).trigger('change');
	<?php endif; ?>
	$('#State').val(null).trigger('change');
	$.post('/ajax/fullSearch.php?action=clear', function(data) {
		var port = $("#searchFilterportlet").mPortlet();
		port.collapse();
		//datatable.setDataSourceParam("query", { SQL:'', EmployeeID:<?php echo $_SESSION['system_user_id']?> });
		//datatable.load();	
		genSearchQuery(0);
	});
	$('#queryInput').val('');
	$('#button-save-search').prop('disabled', true);
	//$('#button-load-search').prop('disabled', true);
}
$(document).ready(function(e) {
	$(".openRecordsFilter").click(function(){
    	var group = "input:checkbox[name='"+$(this).attr("name")+"']";
		console.log(group);
    	$(group).prop("checked",false);
    	$(this).prop("checked",true);
		App.init(); // init metronic core componets		
	});
	
	$('#videoHelp-button').on('click', function(e) {
		$('#welcomeModal').modal('show');		
	});
	$('#welcomeModal').on('show.bs.modal', function (e) {
		$('#welcomeModal .modal-body').html('<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/TnHm4ro_l8s?autoplay=1" allowfullscreen></iframe></div>');
	});
	$('#welcomeModal').on('hide.bs.modal', function (e) {
		$('#welcomeModal .modal-body').html('');
	});
	if( $('#currentTableFields').length ) {
		var sortable = Sortable.create(document.getElementById('currentTableFields'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#catList-wrapper').length ) {
		var sortable = Sortable.create(document.getElementById('catList-wrapper'), {
			draggable: ".m-list-timeline__item",
			onUpdate: function (evt){ 
				console.log('onUpdate.bar:', evt.item); 
				//alert('Updated');
				var catIDs = new Array();
				var idx = 0;
				$('.cat_id').each(function() {
					catIDs[idx] = $(this).val();
					idx++;
				});
				console.log(catIDs);				
				$.post('/ajax/fullSearch.php?action=saveCatOrder', {
					kiss_token: '<?php echo $SESSION->createToken()?>',
					catIDs: catIDs	
				}, function(data) {
					toastr.success('List Category Order Saved', '', {timeOut: 5000});						
				});
			}
		});
	}
	
	$('#LeadStages_id').select2({
        theme: "classic",
		placeholder: "Select stages(s)",
		allowClear: !0
	});
	$('#HearAboutUs').select2({
        theme: "classic",
		placeholder: "Select source(s)",
		allowClear: !0
	});
	$('#FormID').select2({
        theme: "classic",
		placeholder: "Select form(s)",
		allowClear: !0
	});
	$('#Country').select2({
        theme: "classic",
		placeholder: "Country(s)",
		allowClear: !0
	});
		
    //PortletTools.init();
	$("#Offices_id").select2({
        theme: "classic",
		placeholder: "Select location(s)",
		allowClear: !0
	});
    $("#Pods_id").select2({
        theme: "classic",
		placeholder: "Select Pod(s)",
		allowClear: !0
	});
	$('#PersonsTypes_id').select2({
        theme: "classic",
		placeholder: "Select Record Type(s)",
		allowClear: !0
	});
	$('#prQuestion_657').select2({
        theme: "classic",
		placeholder: "Select Member Type(s)",
		allowClear: !0
	});
	$('#prQuestion_664').select2({
        theme: "classic",
		placeholder: "Select Ranking(s)",
		allowClear: !0
	});
	$('#prQuestion_631').select2({
        theme: "classic",
		placeholder: "Select Income(s)",
		allowClear: !0
	});
	$('#Matchmaker_id').select2({
        theme: "classic",
		placeholder: "Select Relationship Manager(s)",
		allowClear: !0,
		<?php if(in_array(90, $userPerms)): ?>
		disabled: true
		<?php endif; ?>
	});
    $('#Matchmaker2_id').select2({
        theme: "classic",
		placeholder: "Select Network Developer(s)",
		allowClear: !0,
		<?php if(in_array(90, $userPerms)): ?>
		disabled: true
		<?php endif; ?>
	});
	$('#Assigned_userID').select2({
        theme: "classic",
		placeholder: "Select Market Director(s)",
		allowClear: !0,
		<?php if(in_array(89, $userPerms)): ?>
		disabled: true
		<?php endif; ?>
	});
	$('#State').select2({
        theme: "classic",
		placeholder: "Select State(s)",
		allowClear: !0
	});
	$('#Color_id').select2({
        theme: "classic",
        placeholder: "Select Status",
        allowClear: !0
	});
	$('#savedSearch').select2({
        theme: "classic",
		placeholder: "Select Saved List",
		allowClear: !0	
	});
	
	
	
	var end = moment();
    var start = moment().subtract(6, 'days');	
	$('#prQuestion_676').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
        endDate: end,
		ranges: {		   
		   'Next 90 Days': [ moment(), moment().add(59, 'days')],
		   'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment().subtract(1, 'days'), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});
	$('#prQuestion_677').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
        endDate: end,
		ranges: {
		   'Next 90 Days': [ moment(), moment().add(59, 'days')],
		   'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment().subtract(1, 'days'), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});

	$('#DateCreated').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
       	endDate: end,
		ranges: {
		  // 'Next 90 Days': [ moment(), moment().add(59, 'days')],
		  // 'Next 60 Days': [ moment(), moment().add(59, 'days')],
		  // 'Next 30 Days': [ moment(), moment().add(29, 'days')],
		  // 'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment().subtract(1, 'days'), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});
	$('#DateUpdated').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
        endDate: end,
		ranges: {
		   //'Next 90 Days': [ moment(), moment().add(59, 'days')],
		  // 'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   //'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   //'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment().subtract(1, 'days'), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});
	$('#LastIntroDate').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
        endDate: end,
		ranges: {
		   //'Next 90 Days': [ moment(), moment().add(59, 'days')],
		  // 'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   //'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   //'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment().subtract(1, 'days'), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});
	$("#filterSearhForm").get(0).reset()
	
	$(document).on('change', '#LastIntroDate_activate', function() {
		if ($(this).is(':checked')) {
			$('#LastIntroDate').attr('disabled', false);
		} else {
			$('#LastIntroDate').attr('disabled', true);
		}			
	});
	$(document).on('change', '#contractStart_activate', function() {
		if ($(this).is(':checked')) {
			$('#prQuestion_676').attr('disabled', false);
		} else {
			$('#prQuestion_676').attr('disabled', true);
		}			
	});
	
	$(document).on('change', '#contractEnd_activate', function() {
		if ($(this).is(':checked')) {
			$('#prQuestion_677').attr('disabled', false);
		} else {
			$('#prQuestion_677').attr('disabled', true);
		}			
	});
	
	$(document).on('change', '#DateCreated_activate', function() {
		if ($(this).is(':checked')) {
			$('#DateCreated').attr('disabled', false);
		} else {
			$('#DateCreated').attr('disabled', true);
		}			
	});
	$(document).on('change', '#DateUpdated_activate', function() {
		if ($(this).is(':checked')) {
			$('#DateUpdated').attr('disabled', false);
		} else {
			$('#DateUpdated').attr('disabled', true);
		}			
	});
	
	$(document).on('change', '#DistanceSearch_activate', function() {
		if ($(this).is(':checked')) {
			$('#DistanceSearch').attr('disabled', false);
		} else {
			$('#DistanceSearch').attr('disabled', true);
		}			
	});
	$("#DistanceSearch").TouchSpin({
		buttondown_class: "btn btn-secondary",
		buttonup_class: "btn btn-secondary",
		min: 5,
		max: 500,
		step: 5,
		decimals: 0,
		boostat: 5,
		maxboostedstep: 5,
		postfix: "miles"
	});
	<?php if($runPreload): ?>
		genSearchQuery();
		//var port = $("#searchFilters_Location").mPortlet();
		//port.expand();
	<?php else: ?>
		<?php if(!$openDistance): ?>
		var port = $("#searchFilters_Location").mPortlet();
		port.expand();
		<?php endif; ?>
		
		<?php if(!$openDates): ?>
		var port2 = $("#searchFilters_Dates").mPortlet();
		port2.expand();
		<?php endif; ?>
		
		<?php if(!$openQuick): ?>
		var port3 = $("#searchFilters_Core").mPortlet();
		port3.expand();
		<?php endif; ?>
		
		<?php if(!$openCustom): ?>
		var port4 = $('#searchFilters_Custom').mPortlet();
		port4.expand();
		<?php endif; ?>
	<?php endif; ?>
	document.title = <?php echo json_encode("SEARCH - (KISS) Kelleher International Software System")?>;	
});
</script>



