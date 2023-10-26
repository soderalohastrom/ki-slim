<?php
include_once("class.record.php");
include_once("class.datatables.php");
include_once("class.encryption.php");
include_once("class.reports.php");

$RECORD = new Record($DB);
$REPORTS = new Reports($DB, $RECORD);
$ENC = new encryption();
$DATATABLE = new Datatable($DB, $RECORD, $REPORTS, $ENC);
if(isset($_SESSION['fullSearch'])) {
	$preLoad = unserialize($_SESSION['fullSearch']);
	$runPreload = true;	
} else {
	$preLoad = array();
	$runPreload = false;	
}
?>
<div class="m-content">
<style>
    .select2-search__field, .select2-search--inline, .select2-selection__rendered {
        width: 100% !important;
    }
</style>
<!-- START: SEARCH FILTER PORTLET -->
<div class="m-portlet m-portlet--head-sm" data-portlet="true" id="searchFilterportlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-coins"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    Search Filters <small>Basic</small>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="#" data-portlet-tool="toggle" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Collapse">
                        <i class="la la-angle-down"></i>
                    </a>
                </li>
                <li class="m-portlet__nav-item">
                    <a href="#" data-portlet-tool="fullscreen" class="m-portlet__nav-link m-portlet__nav-link--icon" title="" data-original-title="Fullscreen">
                        <i class="la la-expand"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <form id="filterSearhForm" class="m-form m-form--fit m-form--label-align-right">
    <div class="m-portlet__body" style="">

		<div class="row">
			<div class="col-lg-6">
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
                    <div class="row">
                    	<div class="col-6">
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
                        <div class="col-6">                            
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
					</div>
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
                        <select class="form-control m-select2" id="prQuestion_657" name="prQuestion_657[]" multiple="multiple">
                        <?php
						if(isset($preLoad['prQuestion_657'])) {
							$preSelected = $preLoad['prQuestion_657'];
						} else {
							$preSelected = array();	
						}						
                        $qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='657' ORDER BY QuestionsAnswers_order ASC";
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
                        <label>Record Status</label>        
                        <select class="form-control m-select2" id="Color_id" name="Color_id[]" multiple="multiple">
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
                        <label>Assigned Relationship Manager</label>
                        <?php
						if(isset($preLoad['Matchmaker_id'])) {
							$preSelected = $preLoad['Matchmaker_id'];
						} else {
							$preSelected = array();	
						}
						?>        
                        <select class="form-control m-select2" id="Matchmaker_id" name="Matchmaker_id[]" multiple="multiple">
                            <?php echo $RECORD->options_userSelect($preSelected)?>
                        </select>
                    </div>
                    <div class="form-group m-form__group">
                        <label>Assigned Network Developer</label>
                        <?php
						if(isset($preLoad['Matchmaker2_id'])) {
							$preSelected = $preLoad['Matchmaker2_id'];
						} else {
							$preSelected = array();	
						}
						?>        
                        <select class="form-control m-select2" id="Matchmaker2_id" name="Matchmaker2_id[]" multiple="multiple">
                            <?php echo $RECORD->options_userSelect($preSelected)?>
                        </select>
                    </div>
                    <div class="form-group m-form__group">
                        <label>Assigned Market Director</label>
                        <?php
						if(isset($preLoad['Assigned_userID'])) {
							$preSelected = $preLoad['Assigned_userID'];
						} else {
							$preSelected = array();	
						}
						?>        
                        <select class="form-control m-select2" id="Assigned_userID" name="Assigned_userID[]" multiple="multiple">
                            <?php echo $RECORD->options_userSelect($preSelected)?>
                        </select>
                    </div>
                     
                    <div class="form-group m-form__group">
                        <label>Record ID:</label>
                        <input type="text" name="Person_id" id="Person_id" class="form-control m-input" placeholder="ID" value="<?php echo (isset($preLoad['Person_id'])? $preLoad['Person_id']:'')?>">
                    </div>
                        
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
    		<div class="col-lg-6">
                <div class="m-form__section m-form__section--first">    
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
                    <div class="row">
                    	<div class="col-6">
                        	<div class="form-group m-form__group">
                                <label>Profile Text/Keyword</label>
                                <div class="input-group">                            	
                                    <input type="text" name="ProfileText" id="ProfileText" class="form-control" placeholder="search text..." value="<?php echo (isset($preLoad['ProfileText'])? $preLoad['ProfileText']:'')?>">
                                    <span class="input-group-addon"><i class="flaticon-info"  data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="This will search all of the text responses from records trying to find a string match." data-original-title="Profile Text Search"></i></span>
                                </div>                                
                            </div> 
                        </div>
                        <div class="col-6">
                            <div class="m-form__group form-group">
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
                                ob_start();
                                foreach($qa_snd as $qa_dta):
                                    ?><option value="<?php echo $qa_dta['QuestionsAnswers_value']?>" <?php echo ((in_array($qa_dta['QuestionsAnswers_value'], $preSelected))? 'selected':'')?>><?php echo $qa_dta['QuestionsAnswers_value']?></option><?php
                                endforeach;
                                ?>
                                </select>
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
						?>
                        <div class="input-group m-form__group">            
                            <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
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
						?>
                        <div class="input-group m-form__group">            
                            <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                            <input type="text" name="prQuestion_677" id="prQuestion_677" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                            <span class="input-group-addon">
                                <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand">
                                    <input type="checkbox" id="contractEnd_activate" <?php echo (($buttonOn)? 'checked':'')?>>
                                    <span></span>
                                </label>
                            </span>
                        </div>
                        <span class="m-form__help">
                            Must check box to include this filter.
                        </span>
                    </div>
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
						?>
                        <div class="input-group m-form__group">            
                            <span class="input-group-addon"><i class="flaticon-time-3"></i></span>
                            <input type="text" name="DateCreated" id="DateCreated" class="form-control m-input" value="<?php echo $preSelected?>" <?php echo (($buttonOn)? '':'disabled="disabled"')?> />
                            <span class="input-group-addon">
                                <label class="m-checkbox m-checkbox--single m-checkbox--state m-checkbox--state-brand">
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
		    </div>
		</div>    
    </div>
    
    <div class="m-portlet__foot m-portlet__foot--fit">
        <div class="m-form__actions m-form__actions--right">
            <div class="row">
                <div class="col m--align-left">
                    <button type="button" onclick="genSearchQuery()" id="button-submitSearch" class="btn btn-brand m-btn">
                        Submit Search <i class="fa fa-search"></i>
                    </button>
                    <button type="button" onclick="clearSearch()" class="btn btn-secondary">
                        Clear Search <i class="fa fa-remove"></i>
                    </button>
                </div>
                <div class="col m--align-right m--hide">
                    <button type="reset" class="btn btn-success" disabled="disabled">
                        Save Search <i class="fa fa-save"></i>
                    </button>
                    <button type="reset" class="btn btn-metal" disabled="disabled">
                        Load Search <i class="fa fa-folder-open-o"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
<!-- END: SEARCH FILTER PORTLET -->

<!-- START: HIDDEN QUERY FORM -->
<div class="row m--hide">
	<div class="col-2">&nbsp;</div>
	<div class="col-8">
		<textarea name="queryInput" id="queryInput" class="form-control m-input" style="height:250px;"></textarea>
	</div>
	<div class="col-2">&nbsp;</div>
</div>    


<!-- START: RESULTS TABLE -->
<?php
//$tableFields = $DATATABLE->getCustomSearchFields();
$tableFields = $DATATABLE->makeCustomLeadFields($_SESSION['system_user_id'], 'mySearchTable');
//print_r($tableFields);
if(!$tableFields) {
	$tableFields = $DATATABLE->getCustomSearchFields();
}
echo $DATATABLE->render_datatable("mySearchTable", '<i class="flaticon-users"></i> Search Results - <small>filtered from above</small><div class="pull-right" id="searchSpinner"></div>', "/ajax/getTableData.php", '', $tableFields, 'Person_id', 'LastName', 'asc', 10, "$('#button-submitSearch').removeClass('m-btn--custom m-loader m-loader--light m-loader--left'); $('#searchSpinner').html('');", false, 'false', 'false');
?>
</div>
<script>
function genSearchQuery() {
	$('#button-submitSearch').addClass('m-btn--custom m-loader m-loader--light m-loader--left');
	$('#searchSpinner').html('<div class="m-loader m-loader--primary" style="width: 30px; display: inline-block;"></div> Loading Search Results...');
	var searchFormData = $('#filterSearhForm').serializeArray();
	console.log(searchFormData);
	$.post('/ajax/fullSearch.php?action=query', searchFormData, function(data) {
		$('#queryInput').val(data.sql);
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
	$('#Offices_id').val(null).trigger('change');
	$('#Pods_id').val(null).trigger('change');
	$('#PersonsTypes_id').val(null).trigger('change');
	$('#prQuestion_657').val(null).trigger('change');
	$('.genderRadio').each(function() {
		$(this).prop('checked', false);
	});
	$('#validRadio').prop('checked', false);	
	$('#prQuestion_664').val(null).trigger('change');
	$('#prQuestion_631').val(null).trigger('change');
	$('#Matchmaker_id').val(null).trigger('change');
	
	$('#prQuestion_676').val();
	$('#prQuestion_676').prop('disabled', true);
	$('#contractStart_activate').prop('checked', false);
	
	$('#prQuestion_677').val();
	$('#prQuestion_677').prop('disabled', true);
	$('#contractEnd_activate').prop('checked', false);
	
	$('#DateCreated').val();
	$('#DateCreated').prop('disabled', true);
	$('#DateCreated_activate').prop('checked', false);
	
	$('#Assigned_userID').val(null).trigger('change');
	$('#State').val(null).trigger('change');
	$('#Matchmaker_id').val(null).trigger('change');
	$('#Matchmaker2_id').val(null).trigger('change');
	$.post('/ajax/fullSearch.php?action=clear', function(data) {
		var port = $("#searchFilterportlet").mPortlet();
		port.collapse();
		//datatable.setDataSourceParam("query", { SQL:'', EmployeeID:<?php echo $_SESSION['system_user_id']?> });
		//datatable.load();	
		genSearchQuery();
	});
}

/*
var PortletTools = function() {
	var e = function() {
			toastr.options.showDuration = 1e3
		},
		t = function() {
			var e = $("#searchFilterportlet").mPortlet();
			e.on("beforeCollapse", function(e) {
				setTimeout(function() {
					toastr.info("Before collapse event fired!")
				}, 100)
			}), e.on("afterCollapse", function(e) {
				setTimeout(function() {
					toastr.warning("Before collapse event fired!")
				}, 2e3)
			}), e.on("beforeExpand", function(e) {
				setTimeout(function() {
					toastr.info("Before expand event fired!")
				}, 100)
			}), e.on("afterExpand", function(e) {
				setTimeout(function() {
					toastr.warning("After expand event fired!")
				}, 2e3)
			}), e.on("afterFullscreenOn", function(e) {
				var t = e.getBody().find("> .m-scrollable");
				t.data("original-height", t.data("max-height")), t.css("height", "100%"), t.css("max-height", "100%"), mApp.initScroller(t, {})
			}), e.on("afterFullscreenOff", function(e) {
				toastr.warning("After fullscreen off event fired!");
				var t = e.getBody().find("> .m-scrollable");
				t.css("height", t.data("original-height")), t.data("max-height", t.data("original-height")), mApp.initScroller(t, {})
			})
		};
	return {
		init: function() {
			e(), t()
		}
	}
}();
*/
$(document).ready(function(e) {	
    //PortletTools.init();
	$("#Offices_id").select2({
        theme: "classic",
		placeholder: "Select location(s)",
		allowClear: !0
	});
     //PortletTools.init();
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
		allowClear: !0
	});
	$('#Matchmaker2_id').select2({
        theme: "classic",
		placeholder: "Select Network Developer(s)",
		allowClear: !0
	});
	$('#Assigned_userID').select2({
        theme: "classic",
		placeholder: "Select Market Director(s)",
		allowClear: !0
	});
	$('#State').select2({
        theme: "classic",
		placeholder: "Select State(s)",
		allowClear: !0
	});
	$('#Color_id').select2({
        theme: "classic",
		placeholder: "Select Flag(s)",
		allowClear: !0
	});	
	$('#Country').select2({
        theme: "classic",
		placeholder: "Select Countries",
		allowClear: !0
	});
	
	
	var start = moment();
    var end = moment().add(6, 'days');	
	$('#prQuestion_676').daterangepicker({
		buttonClasses: 'm-btn btn',
		applyClass: 'btn-primary',
		cancelClass: 'btn-secondary',
		startDate: start,
        endDate: end,
		ranges: {		   
		   'Next 90 Days': [ moment(), moment().add(59, 'days')],
		   //'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment(), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   //'Last 60 Days': [moment().subtract(59, 'days'), moment()],
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
		   //'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment(), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   //'Last 60 Days': [moment().subtract(59, 'days'), moment()],
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
		   'Next 90 Days': [ moment(), moment().add(59, 'days')],
		   //'Next 60 Days': [ moment(), moment().add(59, 'days')],
		   'Next 30 Days': [ moment(), moment().add(29, 'days')],
		   'Next 7 Days': [ moment(), moment().add(6, 'days')],
		   'Today':[moment(), moment()],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   //'Last 60 Days': [moment().subtract(59, 'days'), moment()],
		   'Last 90 Days': [moment().subtract(89, 'days'), moment()],
		   'Last 6 Months': [moment().subtract(6, 'months'), moment()],
		   'Last 12 Months': [moment().subtract(12, 'months'), moment()],
		}
	});
	$("#filterSearhForm").get(0).reset()
	
	
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
	
	<?php if($runPreload): ?>
	genSearchQuery();
	var port = $("#searchFilterportlet").mPortlet();
	port.expand();
	<?php endif; ?>
	document.title = <?php echo json_encode("SEARCH - (KISS) Kelleher International Software System")?>;	
});
</script>



