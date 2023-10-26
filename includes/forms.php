<?php
include_once("class.templates.php");
include_once("class.forms.php");
include_once("class.record.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
$template_obj = new Templates();
$FORMS = new Forms($DB);
$RECORD = new Record($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

$FORM_ID = $pageParamaters['params'][0];
$TAB = $pageParamaters['params'][1];
//print_r($_POST);
?>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<div class="m-subheader ">
    <div class="d-flex align-items-center">
        <div class="mr-auto">
            <h3 class="m-subheader__title m-subheader__title--separator">Forms Manager</h3>
            <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">                
                <li class="m-nav__item">
                    <a href="" class="m-nav__link">
                        <span class="m-nav__link-text">
                            Tool for managing your forms
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <div>
        	<?php if($FORM_ID == ''): ?>
        	<a href="/page.php?path=forms/0" class="btn btn-secondary">New Form <i class="fa fa-plus"></i></a>
            <?php else: ?>
            <a href="/page.php?path=forms" class="btn btn-secondary">Back to Forms <i class="fa fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="m-content">
<?php if($FORM_ID == ''): 
if(!isset($_POST['form_status'])) {
	$_POST['form_status'] = 1;
}

?>
<div id="formsList">
<div class="m-portlet m-portlet--full-height  ">
	<div class="m-portlet__body">
    
<!--begin: Search Form -->
<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
    <div class="row align-items-center">
        <div class="col-xl-12">
            <form id="formListFilter" action="/forms" method="post" enctype="multipart/form-data">
            <div class="form-group m-form__group row align-items-center">
                <div class="col-md-4">
                    <div class="m-form__group m-form__group--inline">
                        <div class="m-form__label">
                            <label>
                                Status:
                            </label>
                        </div>
                        <div class="m-form__control">
                            <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_status" name="form_status">
                                <option value="1" <?php echo (($_POST['form_status'] == 1)? 'selected':'')?>>Active</option>
                                <option value="0" <?php echo (($_POST['form_status'] == 0)? 'selected':'')?>>Inactive</option>
                                <!--<option value="3">Locked</option>-->
                                <option value="All" <?php echo (($_POST['form_status'] == 'All')? 'selected':'')?>>All</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-md-none m--margin-bottom-10"></div>
                </div>
                
                <div class="col-md-4">
                	<button type="submit" class="btn btn-secondary">Apply Filters</button>
                </div>
                
                <div class="col-md-4">
                    <div class="m-input-icon m-input-icon--left">
                        <input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="generalSearch">
                        <span class="m-input-icon__icon m-input-icon__icon--left">
                            <span>
                                <i class="la la-search"></i>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
<!--end: Search Form -->    
    
    
<table class="table table-condensed table-striped" id="formTable">
<thead>
	<tr>
    	<th>Form</th>
        <th>Date Created</th>
        <th><span data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Total Views over the last 30 days" data-skin="dark">Views</span></th>
        <th><span data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Total Submits over the last 30 days" data-skin="dark">Submits</span></th>
        <th><span data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Views vs Submits last 30 days" data-skin="dark">Success</span></th>
        <th>Status</th>
        <th>Actions</th>
	</tr>
</thead>
<tbody>            
<?php
$sql = "SELECT * FROM CompanyForms WHERE 1 ";
if($_POST['form_status'] == "0") {
	$sql .= "AND FormActive='0' ";
} elseif($_POST['form_status'] == "1") {
	$sql .= "AND FormActive='1' ";
} elseif($_POST['form_status'] == "All") {
	$sql .= "";
}

$sql .= "ORDER BY FormName ASC";
//echo $sql."<br>\n";
$snd = $DB->get_multi_result($sql);



foreach($snd as $dta):
	$FORM_VIEWS = $FORMS->get_formViews($dta['FormID'], (time() - 2592000), time());
	$FORM_SUBMITS = ($FORM_VIEWS > 0) ? $FORMS->get_formSubmits($dta['FormID'], (time() - 2592000), time()) : 0;
    $FORM_SUCCESS = ($FORM_VIEWS > 0) ? @round((($FORM_SUBMITS / $FORM_VIEWS) * 100), 1) : 0;
	?>
    <tr>
    	<td><?php echo $dta['FormName']?></td>
        <td><?php echo date("m/d/y", $dta['DateCreated'])?></td>
        <td><?php echo $FORM_VIEWS?></td>
        <td><?php echo $FORM_SUBMITS?></td>
        <td><?php echo $FORM_SUCCESS?>%</td>
        <td><?php echo (($dta['FormActive'] == 1)? '<span class="m-badge m-badge--success m-badge--wide">Active</span>':'<span class="m-badge m-badge--metal m-badge--wide">Inactive</span>')?></td>
        <td>
        	<form action="/viewreport/169" method="post" target="_blank">                      
            <input type="hidden" name="reportDateRange" value="<?php echo date("m/d/Y", (time() - 2592000))?> - <?php echo date("m/d/y")?>" />
            <input type="hidden" name="reportsToInclude" value="<?php echo $dta['FormID']?>" />
            <input type="hidden" name="mapView" value="heat" />
            <a href="/page.php?path=forms/<?php echo $dta['FormID']?>" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Edit Form" data-skin="dark"><i class="la la-edit"></i></a>
            <button class="m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="View Form Report" data-skin="dark"><i class="la la-bar-chart"></i></button>
            <!-- <a href="/page.php?path=formsReport/<?php echo $dta['FormID']?>" class="m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="View Form Report" data-skin="dark"><i class="la la-bar-chart"></i></a> -->
            <a href="javascript:removeForm(<?php echo $dta['FormID']?>);" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Delete Form" data-skin="dark"><i class="la la-remove"></i></a>
			</form>            
        </td>
	</tr>    
    <?php
endforeach;
?>
</tbody>
</table>
	</div>
</div>    
</div>
<?php else: ?>
<?php
$sql = "SELECT * FROM CompanyForms WHERE FormID='".$FORM_ID."'";
$fdta = $DB->get_single_result($sql);

$qsql = "SELECT * FROM CompanyForms_Fields WHERE FormID='".$FORM_ID."' ORDER BY QuestionOrder ASC";
$qsnd = $DB->get_multi_result($qsql);
ob_start();
foreach($qsnd as $qdta):
	$block_id = 'f_'.$qdta['QuestionID'];
	?>
    <li class="list-group-item" id="<?php echo $block_id?>">
        <div class="row">
	        <div class="col-6"><i class="fa fa	fa-navicon m--font-metal"></i>&nbsp;<?php echo $FORMS->get_fieldInfo($qdta['QuestionID'])?></div>
    	    <div class="col-6">        		
                <input type="hidden" name="FormFields[]" class="select-fields-source" value="<?php echo $qdta['QuestionID']?>">
	        	<input type="hidden" name="FormFieldRequired[]" value="<?php echo $qdta['isRequired']?>">
                <input type="hidden" name="FormFieldsHidden[]" value="<?php echo $qdta['isHidden']?>">
                
                <span style="float:left;"><input type="text" name="FormFieldHiddenDefaultValue[]" value="<?php echo $qdta['DefaultValue']?>" class="form-control form-control-sm m-input" style="width:100px; display:<?php echo (($qdta['isHidden'] == 1)? 'default':'none')?>;"/></span>
    	    	<a href="javascript:removeFormItem('<?php echo $qdta['QuestionID']?>');" style="margin-right:5px;" class="pull-right btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-remove"><i class="fa fa-minus"></i></a>
				<a href="javascript:requiredFormItem('<?php echo $qdta['QuestionID']?>');" style="margin-right:5px;" class="pull-right btn <?php echo (($qdta['isRequired'] == 1)? 'btn-outline-danger':'btn-outline-metal')?> m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-required" title="Required Field"><i class="fa fa-check"></i></a>
                <a href="javascript:hiddenFormItem('<?php echo $qdta['QuestionID']?>');" style="margin-right:5px;" class="pull-right btn <?php echo (($qdta['isHidden'] == 1)? 'btn-outline-danger':'btn-outline-metal')?> m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-hidden" title="Hidden Field"><i class="fa fa-eye-slash"></i></a>                        		        		
	        </div>
        </div>
    </li>        
    <?php
	$chosenFieldsArray[] = $qdta['QuestionID'];
endforeach;
$chosenFields = ob_get_clean();



?>
<form id="makeForm_form" action="javascript:;">
<input type="hidden" name="FormID" value="<?php echo $FORM_ID?>" />
<div id="formMGRform">
    <div class="row">
        <div class="col-4" id="avail-fields"></div>
        <div class="col-4">
            <h5>Fields on Form</h5>
            <ul class="list-group" id="form-fields">
            <?php echo $chosenFields?>
            </ul>                            
        </div>
        <div class="col-4">

<div class="form-group m-form__group">
	<label for="FormName">Form Name</label>
	<input type="text" class="form-control m-input" id="FormName" name="FormName" value="<?php echo $fdta['FormName']?>" />
</div>

<div class="form-group m-form__group row">
	<label class="col-form-label col-lg-3 col-sm-12">Active</label>
	<div class="col-lg-9 col-md-9 col-sm-12">
		<input data-switch="true" data-size="small" type="checkbox" <?php echo (($fdta['FormActive'] == 1)? 'checked':'')?> id="form-active" name="FormActive" />
	</div>
</div>

<!--
<hr />
<div class="row">
	<div class="col-12">
        <div class="form-group m-form__group">
            <label for="FormCampaign_name">Campaign Name</label>
            <input type="text" class="form-control m-input" id="FormCampaign_name" name="FormCampaign_name" value="<?php echo $fdta['FormCampaign_name']?>" />
        </div>
	</div>
    <div class="col-5">        
        <div class="form-group m-form__group">        
            <label for="FormCampaign_cost">Campaign Cost</label>
            <div class="input-group">
				<span class="input-group-addon">$</span>
                <input type="text" class="form-control m-input" id="FormCampaign_cost" name="FormCampaign_cost" value="<?php echo $fdta['FormCampaign_cost']?>" />
            </div>            
        </div>   
	</div>
    <div class="col-7">
    	<div class="form-group m-form__group">
            <label for="FormCampaign_name">Campaign Source</label>
            <select class="form-control m-select2" id="SourceID" name="SourceID">
				<?php echo $RECORD->options_leadSources(array(), true)?>
            </select>
		</div>
	</div>
</div>
-->
<hr />

<div class="form-group m-form__group">
    <label  data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Place any scripts or code that will be placed in head of the form. Include any <script> tags that are needed" data-original-title="Form Header">Form Header (Optional)</label>
    <textarea class="form-control m-input" id="FormHeader" name="FormHeader" rows="5"><?php echo $fdta['FormHeader']?></textarea>
</div>

<div class="form-group m-form__group">
    <label  data-trigger1="focus" data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="Place any scripts or code that will be placed in the body of the form. Include any <script> tags that are needed. This will be placed at the TOP of the form." data-original-title="Form Body">Form Body (Optional)</label>
    <textarea class="form-control m-input" id="FormBody" name="FormBody" rows="5"><?php echo $fdta['FormBody']?></textarea>
</div>

<div class="form-group m-form__group">
    <label data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="This is what will be presented to the client/lead after they submit the form. If not sub-form is selected, this is the final message they see after submitting." data-original-title="Response">Response</label>
    <div class="summernote"><?php echo $fdta['FormSuccessPage']?></div>
</div>
<textarea name="FormSuccessPage" id="FormSuccessPage" style="display:none;"><?php echo $fdta['FormSuccessPage']?></textarea>                   

<div class="form-group m-form__group">
    <label for="exampleSelect1">
        Response Template
    </label>
    <select class="form-control m-select2" id="TemplateSelect" name="TemplateSelect">
        <option value="">Insert Template</option>
        <?php
        $tmpl_categories = array();
        $tc_data = $template_obj->get_template_categories();
        if(!array_key_exists('empty_result', $tc_data) && !array_key_exists('error', $tc_data)) {
            foreach($tc_data as $tc_row) {
                $tmpl_categories[] = $tc_row['TemplateCategories_id'];
            }
        }
        $tmpl_categories[] = 0;
        $cat_count = 0;
        foreach($tmpl_categories as $tmpl_category) {
            $cat_count++;
            $t_data = $template_obj->get_templates($tmpl_category);
            $count = 0;
            if(!array_key_exists('empty_result', $t_data) && !array_key_exists('error', $t_data)) {
                foreach($t_data as $t_row) {
                    $count++;
                    if($count == 1) {
                        if($cat_count != 1) {
                            ?></optgroup><?php
                        }
                        ?><optgroup label="<?php echo $t_row['TemplateCategories_name']?>"></optgroup><?php
                    }
                    ?>
                    <option value="<?php echo $t_row['EmailTemplates_id']?>" <?php echo (($fdta['FormResponseTemplate'] == $t_row['EmailTemplates_id'])? 'selected':'')?>>
                        <?php echo $t_row['EmailTemplates_title']?>
                    </option>
                    <?php
                }
            }
        }
        ?>
            </optgroup>
	</select>
</div>



<div class="form-group m-form__group">
    <label>Sub Form</label>
    <select class="form-control" id="Form_nextForm" name="Form_nextForm">
    	<option value="0" <?php echo (($fdta['Form_nextForm'] == 0)? 'selected':'')?>>NONE</option>
<?php
$fsql = "SELECT * FROM CompanyForms WHERE FormID != '".$FORM_ID."'";
//echo $fsql;
$fsnd = $DB->get_multi_result($fsql);
foreach($fsnd as $fdta_option):
	?><option value="<?php echo $fdta_option['FormID']?>" <?php echo (($fdta['Form_nextForm'] == $fdta_option['FormID'])? 'selected':'')?>><?php echo $fdta_option['FormName']?></option><?php
endforeach;
?>
    </select>
    <span class="m-form__help"><small>This form will be presented at the end of this form</small></span>
</div>

<div class="form-group m-form__group" id="nextFormMessage" style="display:<?php echo (($fdta['Form_nextForm'] == 0)? 'none':'default')?>;">
    <label data-skin="dark" data-toggle="m-popover" data-placement="top" title="" data-content="This is what will be presented to the client/lead when they go to the form. This will appear at the top of the form and is ideal for SUB-FORMS" data-original-title="Next Form Message">Next Form Message</label>
    <div class="summernote2"><?php echo $fdta['FormNextFormMessage']?></div>
</div>
<textarea name="FormNextFormMessage" id="FormNextFormMessage" style="display:none;"><?php echo $fdta['FormNextFormMessage']?></textarea> 
<hr />
<button type="button" onclick="updateForm()" class="btn btn-danger btn-block">Save Form Configuration</button>
<div>&nbsp;</div>
<div class="card">
	<div class="card-body">
    	<h5 class="card-title">Form URL</h5>
<?php if($FORM_ID == 0): ?>
	<div class="alert alert-info">Preview available only after saving a refreshing the page</div>
<?php else: ?>
	<p class="card-text">https://<?php echo $_SERVER['SERVER_NAME']?>/view-form.php?id=<?php echo $fdta['FormCallString']?></p>
	<a href="https://<?php echo $_SERVER['SERVER_NAME']?>/view-form.php?id=<?php echo $fdta['FormCallString']?>" class="btn btn-primary" target="_blank">preview form</a>
    <a href="" class="btn btn-brand" data-toggle="modal" data-target="#embedModal" >embed code</a>
<?php endif; ?>        
		
	</div>	
</div>
<div>&nbsp;</div>
<div class="m-form__group form-group">
	<div class="m-checkbox-list">
		<label class="m-checkbox">
			<input type="checkbox" name="Form_excludeIndex" id="Form_excludeIndex" value="Y" <?php echo (($fdta['Form_excludeIndex'] == 1)? 'checked':'')?>>
			Exclude from Webcrawlers
			<span></span>
		</label>
	</div>
</div>

<div class="modal fade" id="embedModal" role="dialog" aria-labelledby="embedModalLabel" aria-hidden="true">
	<div class="modal-dialog " role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="embedModalLabel"><i class="flaticon-interface-9"></i> Form Embed Code</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">   

<div class="m-demo__preview">
<textarea class="form-control m-input" id="embedCode" style="height:250px;">
<iframe name="KIMSForm-ID" frameborder="0" width="500" height="<?php echo (count($chosenFieldsArray) * 40)?>" src="https://<?php echo $_SERVER['SERVER_NAME']?>/view-form.php?id=<?php echo $fdta['FormCallString']?>"></iframe>
</textarea>
</div> 

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyToClipboard()">Copy to Clipboard</button>
			</div>
		</div>
	</div>
</div>

        </div>
    </div>    
</div>

</form>
<?php //print_r($_SERVER); ?>
<?php endif; ?>
</div>


<script>
var $search = $('#generalSearch');
var $table = $('#formTable');
var i=1;
function uuid(){
   i++;
   return "A"+i;
};
$search.keyup(function() {
    var value = this.value.toLowerCase().trim();
    
    for (row in cache) {
            document.getElementById(row).style.display = (cache[row].indexOf(value) === -1) ?     "none" : "table-row";
    }
});
function getTableData() {
    var cache = {};
    $table.find('tbody').each(function (rowIndex, r) {
        $(this).find("tr").each(function(rowIndex,r){
        var cols = [], id = uuid();
        r.id=id;
            $(this).find('td').each(function (colIndex, c) {
            cols.push(c.textContent);
            });
        cache[id]=cols.join(" ").toLowerCase();
        });    
        
        
    });
    return cache;
};
var cache = getTableData();
var BootstrapSelect = function() {
    var t = function() {
        $("#m_form_status").selectpicker()
    };
    return {
        init: function() {
            t()
        }
    }
}();
jQuery(document).ready(function() {
    BootstrapSelect.init();
	$("#FormCampaign_cost").inputmask("decimal", {
    	rightAlignNumerics: !1
   	});
});
$(document).ready(function(e) {	
	$('#SourceID').select2({
        theme: "classic",
		placeholder: "Select Source",
		allowClear: !0
	});
	
	getAvailableFields(); 
	$('#form-active').bootstrapSwitch({
		onText: 	'YES',
		offText:	'NO',
		onSwitchChange: function(event, state) {
			console.log(state);
		}
	});
	$('#TemplateSelect').select2({ theme: "classic" });
	$(".summernote").summernote({
    	height: 200
    });
	$(".summernote2").summernote({
    	height: 150
    });
	
	var el = document.getElementById('form-fields');
	var sortable = Sortable.create(el);
	
	//$("#m_form_status").selectpicker();
});
function getAvailableFields() {
	var fields = [ '652','FirstName','LastName' ];
	var c_fields = new Array();
	var c_index = 0;
	$('.select-fields-source').each(function() {
		c_fields[c_index] = $(this).val();
		c_index++;
	});
	$.post('/ajax/formMGR.php?action=availableFields', {
		currentFields: c_fields		
	}, function(data) {
		$('#avail-fields').html(data);
	});	
}
function addItemToForm(fieldName, label) {
	var sourceBlockID = 'q_'+fieldName;
	$('#'+sourceBlockID).hide();
	var block_id = 'f_'+fieldName;
	var html = '';
	html += '<li class="list-group-item" id="'+block_id+'">';
	html += '<div class="row">';
	
	html += '<div class="col-9"><i class="fa fa	fa-navicon m--font-metal"></i> '+label+'</div>';
	html += '<div class="col-3">';
	html += '<input type="hidden" name="FormFields[]" class="select-fields-source" value="'+fieldName+'">';
	html += '<input type="hidden" name="FormFieldRequired[]" value="0">';
	html += '<a href="javascript:requiredFormItem(\''+fieldName+'\');" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-required"><i class="fa fa-check"></i></a>&nbsp;';
	//html += '<a href="javascript:editFormItem(\''+fieldName+'\');" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-edit"><i class="fa fa-edit"></i></a>&nbsp;';
	html += '<a href="javascript:removeFormItem(\''+fieldName+'\');" class="btn btn-outline-metal m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill field-remove"><i class="fa fa-minus"></i></a>';
	html += '</div>';
	html += '</div>';
	html += '</li>';
	
	$('#form-fields').append(html);	
	var el = document.getElementById('form-fields');
	var sortable = Sortable.create(el);
}
function removeFormItem(fieldName) {
	var block_id = 'f_'+fieldName;
	var reserve_id = 'q_'+fieldName;
	$('#'+block_id).remove();
	$('#'+reserve_id).show();
}
function hiddenFormItem(fieldName) {
	var block_id = 'f_'+fieldName;
	var current = $('#'+block_id+' input[name="FormFieldsHidden[]"]').val();
	if (current == 1) {
		$('#'+block_id+' input[name="FormFieldsHidden[]"]').val('0');
		$('#'+block_id+' .field-hidden').removeClass('btn-outline-danger');
		$('#'+block_id+' .field-hidden').addClass('btn-outline-metal');
		$('#'+block_id+' input[name="FormFieldHiddenDefaultValue[]"]').val('');
		$('#'+block_id+' input[name="FormFieldHiddenDefaultValue[]"]').hide();
	} else {
		$('#'+block_id+' input[name="FormFieldsHidden[]"]').val('1');
		$('#'+block_id+' .field-hidden').removeClass('btn-outline-metal');
		$('#'+block_id+' .field-hidden').addClass('btn-outline-danger');
		$('#'+block_id+' input[name="FormFieldHiddenDefaultValue[]"]').show();
	}
}
function requiredFormItem(fieldName) {
	var block_id = 'f_'+fieldName;
	var current = $('#'+block_id+' input[name="FormFieldRequired[]"]').val();
	if (current == 1) {
		$('#'+block_id+' input[name="FormFieldRequired[]"]').val('0');
		$('#'+block_id+' .field-required').removeClass('btn-outline-danger');
		$('#'+block_id+' .field-required').addClass('btn-outline-metal');
	} else {
		$('#'+block_id+' input[name="FormFieldRequired[]"]').val('1');
		$('#'+block_id+' .field-required').removeClass('btn-outline-metal');
		$('#'+block_id+' .field-required').addClass('btn-outline-danger');
	}
}
function updateForm() {
	var markupStr = $('.summernote').summernote('code');
	$('#FormSuccessPage').val(markupStr);
	
	var markupStr2 = $('.summernote2').summernote('code');
	$('#FormNextFormMessage').val(markupStr2);
	
	var formData = $('#makeForm_form').serializeArray();
	$.post('/ajax/formMGR.php?action=submitForm', formData, function(data) {
		console.log(data);
		toastr.success('Form Saved', '', {timeOut: 5000});
		
	});
}
function removeForm(form_id) {
	var choice = confirm('Are you sure you want to do this? This action will permanently remove this form from the database. This aciton cannot be undone!!!');
	if(choice) {
		var confirmText = prompt('Please confirm this action by typoing the word "DELETE" (all caps) to confirm');
		if(confirmText == 'DELETE') {
			//alert('do the delete');
			$.post('/ajax/formMGR.php?action=removeForm', {
				fif: form_id,
				kiss_token: '<?php echo $SESSION->createToken()?>'				
			}, function(data) {	
				document.location.reload(true);
			});
		}
	}
}
function copyToClipboard() {
	$("#embedCode").select();
    document.execCommand('copy');
}

</script>