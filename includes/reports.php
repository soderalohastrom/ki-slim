<?php
include_once("class.record.php");
include_once("class.reports.php");
$RECORD = new Record($DB);
$REPORT = new Reports($DB, $RECORD);

$REPORT_ID = $pageParamaters['params'][0];
if($REPORT_ID == '') {
	$REPORT_ID = 0;
	$R_DATA['Report_name'] = '';
	$rCONFIG['type'] = 'Persons';
	$fieldsPreLoad = '';
	$filterPreLoad = '';
	$pickedFilterFields = array();
	$chosenColumns = array();
	//$groupBy = '';
	$groupBySelect = '';
	$graphLists = '';
	$sortDir = 'ASC';
	$preSelected = array($_SESSION['system_user_id']);
	$customReport = false;
} else {
	$r_sql = "SELECT * FROM Reports WHERE Report_id='".$REPORT_ID."'";
	$r_snd = $DB->get_single_result($r_sql);
	$R_DATA = $r_snd;
	
	if($R_DATA['Report_type'] == 1) {
		$rCONFIG = json_decode($R_DATA['Report_config'], true);
		$groupBy = $rCONFIG['groupBy'];
		$sortBy = $rCONFIG['sortBy'];
		$sortDir = $rCONFIG['sortDir'];
		
		ob_start();
		for($i=0; $i<count($rCONFIG['fields']); $i++) {
			$chosenColumns[] = $rCONFIG['fields'][$i];
			$chosenLabels[] = $rCONFIG['columns'][$i];
			?><div class="m-stack__item column-sorter" id="col_<?php echo str_replace(".", "_", $rCONFIG['fields'][$i])?>"><div class="m-stack__demo-item"><?php echo $rCONFIG['columns'][$i]?></div><input type="hidden" name="column_label[]" value="<?php echo $rCONFIG['columns'][$i]?>" /><input type="hidden" name="column_field[]" value="<?php echo $rCONFIG['fields'][$i]?>" /></div><?php
			
			$groupByOptions[] = '<option value="'.$rCONFIG['fields'][$i].'" '.(($rCONFIG['fields'][$i] == $groupBy)? 'selected':'').'>'.$rCONFIG['columns'][$i].'</option>';
			$sortByOptions[] = '<option value="'.$rCONFIG['fields'][$i].'" '.(($rCONFIG['fields'][$i] == 'col_'.$sortBy)? 'selected':'').'>'.$rCONFIG['columns'][$i].'</option>';
		}
		$groupBySelect = implode("\n", $groupByOptions);
		$sortBySelect = implode("\n", $sortByOptions);
		$fieldsPreLoad = ob_get_clean();
		
		ob_start();
		for($idx=0; $idx<count($rCONFIG['filters']['fields']); $idx++) {		
			$field = $rCONFIG['filters']['fields'][$idx];
			$pickedFilterFields[] = $field;
			$label = $rCONFIG['filters']['labels'][$idx];
			$operand = $rCONFIG['filters']['operand'][$idx];
			$fieldsText = $rCONFIG['filters']['option_labels'][$idx];
			$fieldValues = $rCONFIG['filters']['option_values'][$idx];
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
		}
		$filterPreLoad = ob_get_clean();
		
		ob_start();
		$id = 0;
		if (is_array($rCONFIG['graphs'])):
		foreach($rCONFIG['graphs'] as $graph) {
			$divID = time()."_".$id;
			?>
			<div class="row" style="margin-top:15px;" id="<?php echo $divID?>">
				<div class="col-lg-4">Base Graph on:</div>
				<div class="col-lg-7">
					<select class="form-control m-input" name="graphyByField[]" style="width:100%;">
					<?php for($i=0; $i<count($chosenLabels); $i++): ?>
						<option value="<?php echo $chosenColumns[$i]?>" <?php echo (($graph == $chosenColumns[$i])? 'selected':'')?>><?php echo $chosenLabels[$i]?></option>
					<?php endfor; ?>
					</select>
				</div>
				<div class="col-lg-1">
					<button type="button" class="btn btn-default btn-sm" onclick="$('#<?php echo $divID?>').remove();"><i class="fa fa-minus"></i></button>
				</div>
			</div>
			<?php
		}
		endif;
		$graphLists = ob_get_clean();
				
		$customReport = false;		
	} else {
		$include_file = $R_DATA['Report_config'];
		$customReport = true;
	}
	$access_sql = "SELECT * FROM ReportsAccess WHERE Report_id='".$REPORT_ID."'";
	$access_snd = $DB->get_multi_result($access_sql);
	
	foreach($access_snd as $access_dta):	
		$preSelected[] = $access_dta['user_id'];
	endforeach;
}



if(in_array(82, $PARENT_USER_PERMISSIONS)):
?>
<link href="//www.amcharts.com/lib/3/plugins/export/export.css" rel="stylesheet" type="text/css" />
<script src="//www.amcharts.com/lib/3/amcharts.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/serial.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/radar.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/pie.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/tools/polarScatter/polarScatter.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/animate/animate.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/plugins/export/export.min.js" type="text/javascript"></script>
<script src="//www.amcharts.com/lib/3/themes/light.js" type="text/javascript"></script>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<?php

?>
<div class="m-content">
<div class="m-portlet m-portlet--tabs">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">Reports</h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="nav nav-tabs m-tabs-line m-tabs-line--right" role="tablist">                
                <li class="nav-item m-tabs__item">
                    <a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_tabs_6_2" role="tab" aria-expanded="false">
                        <i class="flaticon-cogwheel-2"></i>
                        Report Generator
                    </a>
                </li>
                <li class="nav-item m-tabs__item">
                    <a class="nav-link m-tabs__link" data-toggle="tab" href="#m_tabs_6_3" role="tab" aria-expanded="true">
                        <i class="flaticon-graphic"></i>
                        Preview
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="m-portlet__body">
        <div class="tab-content">		
            <div class="tab-pane active" id="m_tabs_6_2" role="tabpanel">
<form id="reportGrandForm" action="javascript:;">
<input type="hidden" name="Report_id" id="Report_id" value="<?php echo $REPORT_ID?>" />
<?php echo $SESSIONS->renderToken()?>
<div class="row">
	<div class="col-lg-6">
    	<div class="form-group m-form__group row">
            <label class="col-lg-3 col-form-label">
                Report Name:
            </label>
            <div class="col-lg-6">
                <input type="text" name="Report_name" id="Report_name" class="form-control m-input" value="<?php echo $R_DATA['Report_name']?>">
            </div>
        </div>
	</div>
    <div class="col-lg-6">
    	<?php if(!$customReport):?>
		<div class="form-group m-form__group row">
            <label class="col-lg-3 col-form-label">
                Report Type:
            </label>
            <div class="col-lg-6">
                <select name="Report_type" id="Report_type" class="form-control m-input">
                	<option value="Persons" <?php echo (($rCONFIG['type'] == 'Persons')? 'selected':'')?>>Leads/Marketing</option>
                </select>
            </div>
        </div>
		<input type="hidden" name="Report_type_id" value="1">
		<?php else: ?>
			<input type="hidden" name="Report_type" value="Custom">
			<input type="hidden" name="Report_type_id" value="2">
			<div class="alert alert-danger">This is a custom report and can only be configured to appear in certain users lists of reports. All other configuration is diabled for a custom report.</div>
		<?php endif; ?>
	</div>
</div>

<div class="row">
	<div class="col-lg-4">
    	<?php if(!$customReport):?>
		<h5>
        	<a href="javascript:$('#add-filter-form').toggle();" class="pull-right"><i class="fa fa-plus"></i></a>
            Report Filter(s) 
            <i class="la la-exclamation-triangle" data-toggle="m-popover" title="Report" data-content="Select the field in which you intend to filter on. After selecting ou can enter in further paramaters."></i>
        </h5>
        <div class="row" id="add-filter-form" style="display:none; margin-top:15px;">
        	<div class="col-lg-3">Add Filter Field:</div>
            <div class="col-lg-9">
            	<select class="form-control m-input" id="qSelectField" name="qSelectField" style="width:100%;">
                <option value=""></option>
				<?php echo $REPORT->render_qSelect($pickedFilterFields);?>
                </select>
            </div>
		</div>
        <div id="fieldOptionForm" style="margin-top:15px; margin-bottom:15px;"></div>
        <hr />
        <div>
	        <div class="m-stack m-stack--hor m-stack--general m-stack--demo" id="filterListing"><?php echo $filterPreLoad?></div>
		</div>
		<?php endif; ?>
        
        <hr />
        <h5>
            Permissions 
            <i class="la la-exclamation-triangle" data-toggle="m-popover" title="Permissions" data-content="Decide who will have access to this report."></i>
        </h5>	
        <div class="form-group m-form__group">
            <label>Users</label>       
            <select class="form-control m-select2" id="Assigned_users" name="Assigned_users[]" multiple="multiple">
                <?php echo $RECORD->options_userSelect($preSelected)?>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <?php if(!$customReport):?>
		<h5>
        	<a href="javascript:$('#add-column-form').toggle();" class="pull-right"><i class="fa fa-plus"></i></a>
            Report Columns 
            <i class="la la-exclamation-triangle" data-toggle="m-popover" title="Report Columns" data-content="Select the field that you want to appear as columns displayed. You will be able to sort and group based on these fields."></i>
		</h5>
        <div id="add-column-form" style="display:none; margin-top:15px;">
            <div class="row" style="margin-bottom:10px;">
				<div class="col-4">&nbsp;</div>
				<div class="col-8">
					<div class="input-group m-input-group m-input-group--pill">
						<span class="input-group-addon" id="basic-addon1" title="search available fields">
							<i class="la la-gear"></i>
						</span>
						<input type="text" class="form-control m-input" id="fieldSearch" autocomplete="off">
					</div>
				</div>
			</div>
			<div id="report-field-options" class="m-scrollable" data-scrollable="true" data-max-height="400" data-scrollbar-shown="true">
                <?php echo $REPORT->render_qformColumns($chosenColumns)?>
            </div>
		</div>            
        <hr />
        <div>
	        <div class="m-stack m-stack--hor m-stack--general m-stack--demo" id="filterColumns">
			<?php echo $fieldsPreLoad?>
            </div>
		</div>
		<?php endif; ?>
	</div>
    <div class="col-lg-4">
    	<?php if(!$customReport):?>
		<h5>
        	<a href="javascript:$('#add-customization-form').toggle();" class="pull-right"><i class="fa fa-plus"></i></a>
            Report Customization 
            <i class="la la-exclamation-triangle" data-toggle="m-popover" title="Report Customization" data-content="Here is where you can decide what field you wnat to group and add any graphs or totals columns."></i>
		</h5>
        <div id="add-customization-form" style="margin-top:15px;">       
            <div class="row">
            	<div class="col-lg-4">Sort By Field:</div>
                <div class="col-lg-8">
                	<div class="input-group m-input-group m-input-group--square">
                        <select class="form-control m-input" id="fieldSortBy" name="fieldSortBy" style="width:100%;">
                        <option value=""></option>
                        <?php echo $sortBySelect?>                    
                        </select>	
						<span class="input-group-addon" id="basic-addon1">&nbsp;</span>
                        <select class="form-control m-input" id="fieldSortByDir" name="fieldSortByDir" style="width:100%;">
                        <option value="ASC" <?php echo (($sortDir == 'ASC')? 'selected':'')?>>Ascending</option>
                        <option value="DESC" <?php echo (($sortDir == 'DESC')? 'selected':'')?>>Descending</option>
                        </select>						
					</div>
                </div>
                
                <div class="col-lg-4">Group By Field:</div>
                <div class="col-lg-8">
                    <select class="form-control m-input" id="fieldGroupBy" name="fieldGroupBy" style="width:100%;">
                    <option value=""></option>
                    <?php echo $groupBySelect?>                    
                    </select>
                </div>
                <div class="col-lg-12">
                    <div style="margin-top:10px;">
                        <button type="button" class="btn btn-default btn-sm" onclick="addGraph()">Add Graph <i class="fa fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div id="graphFormArea"><?php echo $graphLists?></div>
        </div>
		<?php endif; ?>
        <hr />
        <button class="btn btn-primary btn-block" class="button" onclick="saveReportConfig()">Save Report Configuration</button>
        <?php if($REPORT_ID != 0): ?>
        <button class="btn btn-danger btn-block" class="button" onclick="deleteReportConfig()">Delete Report</button>
        <?php endif; ?>
	</div>
</div> 
</form>
            </div>
            <div class="tab-pane" id="m_tabs_6_3" role="tabpanel">
				<?php echo $REPORT->genReport($REPORT_ID)?>
            </div>
        </div>
    </div>
</div>
</div>
<script>
$(document).ready(function(e) {
	$('#Assigned_users').select2({
		theme: "classic",
		placeholder: "Select Users",
		allowClear: !0
	});
	
	$('#fieldSearch').keypress(function (e) {
		var key = e.which;
		if(key == 13)  // the enter key code
		{
			//alert('Execute Search');
			var query = $('#fieldSearch').val();
			$( "#report-field-options label" ).css( "text-decoration", "none" );
			$( "#report-field-options label" ).css( "color", "black" );
			//$( "#report-field-options label" ).removeClass("m--font-danger");
			if(query != '') {
				$( "#report-field-options label:contains('"+query+"')" ).css( "text-decoration", "underline" );
				$( "#report-field-options label:contains('"+query+"')" ).css( "color", "red" );
				//$( "#report-field-options label:contains('"+query+"')" ).addclass("m--font-danger");
			}
			return false;  
		}	
	});
	
	$(document).on('change', '#qSelectField', function() {
		var newVal = $(this).val();
		$.post('/ajax/reportMGR.php?action=reportQuestionFilter_field', {
			field: newVal
		}, function(data) {
			//console.log(data);
			$('#fieldOptionForm').html(data);
		});		
	});
	
	$(document).on('click', '#button-expand', function(e) {
		var type = $(this).attr('data-type');
		$.post('/ajax/reportMGR.php?action=reportQuestionSubFilter_field', {
			type: type	
		}, function(data) {
			console.log(data);
		});
	});
	
	$(document).on('click','#button-select-add', function() {
		var field_id = $(this).attr('data-id');
		var field_label = $(this).attr('data-label');
		var opt = new Array();
		var idx = 0;
		$('.reportFilterOption').each(function() {
			if($(this).is(':checked')) {
				opt[idx] = {
					'text'	: 	$(this).attr('data-label'),
					'val'	:	$(this).val()
				}
				idx++;	
			}
		});
		console.log(opt);
		$.post('/ajax/reportMGR.php?action=reportQuestionSubFilter_add', {
			field: 	field_id,
			label:	field_label,
			option:	opt,
			oper:	'IN'
		}, function(data) {
			console.log(data);
			$('#filterListing').append(data);
			cleanSelect();						
		});		
	});
	
	$(document).on('click', '#button-date-add', function() {
		var field_id = $(this).attr('data-field');
		var field_label = $(this).attr('data-label');
		var opt = $('#fieldTimeDays').val();
		var oper = $('#fieldOperand').val();
		$.post('/ajax/reportMGR.php?action=reportQuestionSubFilter_add', {
			field: 	field_id,
			label:	field_label,
			option:	[{
				text:	opt,
				val	:	opt
			}],
			oper:	oper
		}, function(data) {
			console.log(data);
			$('#filterListing').append(data);
			cleanSelect();						
		});
		
	});
	
	$(document).on('click', '.checkColumn', function() {
		var label = $(this).attr('data-label');
		var value = $(this).val();
		var divID = value.replace(".", "_");
		if($(this).is(':checked')) {			
			$('#filterColumns').append('<div class="m-stack__item column-sorter" id="col_'+divID+'"><div class="m-stack__demo-item">'+label+'</div><input type="hidden" name="column_label[]" value="'+label+'" /><input type="hidden" name="column_field[]" value="'+value+'" /></div>');
			var el = document.getElementById('filterColumns');
			var sortable = Sortable.create(el, {
				draggable: ".column-sorter"	
			});
			$('#fieldGroupBy').append('<option value="'+value+'">'+label+'</option>');
			$('#fieldSortBy').append('<option value="'+value+'">'+label+'</option>');
		} else {
			$('#col_'+divID).remove();
			$('#fieldGroupBy option').each(function() {
				if($(this).val() == value) {
					$(this).remove();
				}
			});
		}
	});
	
	var el = document.getElementById('filterColumns');
	var sortable = Sortable.create(el, {
		draggable: ".column-sorter"	
	});
});
function addGraph() {
	var idx = 0;
	var optionList = new Array();
	var labelList = new Array();
	$('#fieldGroupBy option').each(function() {
		optionList[idx] = $(this).val();
		labelList[idx] = $(this).text();
		idx++;
	});
	$.post('/ajax/reportMGR.php?action=reportGraphForm', {
		option: optionList,
		labels: labelList
	}, function(data) {
		$('#graphFormArea').append(data);		
	});
}
function cleanSelect() {
	$('#qSelectField').val('');
	$('#fieldOptionForm').html('');
	var activeFilters = new Array();
	var id = 0;
	$('.filter_field_fieldname').each(function() {
		activeFilters[id] = $(this).val();
		id++;
	});
	
	$('#qSelectField option').each(function() {
		var optVal = $(this).val();
		console.log(optVal);
		console.log(activeFilters);
		var isFound = $.inArray(optVal, activeFilters);
		console.log(isFound);
		if(isFound == '-1') {
			$(this).prop('disabled', false);	
		} else {
			$(this).prop('disabled', true);	
		}
	});
	$('#add-filter-form').hide();	
}
function dropFilter(divID) {
	$('#'+divID).remove();
	cleanSelect();	
}
function deleteReportConfig() {
	var choice = confirm('Are you sure you want to delete this report?');
	if(choice) {
		var choice2 = confirm('Are you sure you want to delete this report?\nThis action cannot be undone.');
		if(choice2) {			
			var formData = $('#reportGrandForm').serializeArray();	
			$.post('/ajax/reportMGR.php?action=reportRemove', formData, function(data) {
				//console.log(data);
				document.location.href='/home';
			});
		}
	}
}
function saveReportConfig() {
	var error = 0;
	var errorTXT = '';

	var formData = $('#reportGrandForm').serializeArray();
	$.post('/ajax/reportMGR.php?action=reportGrandForm', formData, function(data) {
		console.log(data);
		document.location.href='/reports/'+data.reportID;
	}, "json");
		
}
</script>
<?php
else:
?><div class="alert alert-danger">You do not have access to edit reports</div><?php
endif;