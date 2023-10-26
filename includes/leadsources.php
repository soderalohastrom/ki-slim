<?php
include_once("class.users.php");
$USER = new Users($DB);

$u_sql = "SELECT * FROM DropDown_LeadSource WHERE 1";
$u_snd = $DB->get_multi_result($u_sql);
$U_DATA = json_encode($u_snd, JSON_HEX_APOS);


$so_sql = "SELECT * FROM DropDown_SourceType ORDER BY SourceType_text";
$so_snd = $DB->get_multi_result($so_sql);
ob_start();
?><option value="">All</option><?php
foreach($so_snd as $so_dta):
	?><option value="<?php echo $so_dta['SourceType_text']?>"><?php echo $so_dta['SourceType_text']?></option><?php
endforeach;
$so_select = ob_get_clean();
?>
<div class="m-content">

<div class="m-portlet m-portlet--mobile">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
                    Lead Sources
                    <small>
                        list of all available sources for a lead to be associated with
                    </small>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    &nbsp;
                </li>
            </ul>
        </div>
    </div>
    <div class="m-portlet__body">
        <!--begin: Search Form -->
        <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
            <div class="row align-items-center">
                <div class="col-xl-9 order-2 order-xl-1">
                    <div class="form-group m-form__group row align-items-center">
                        
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label>
                                        Status:
                                    </label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_status">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label>
                                        Display:
                                    </label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_display">
                                        <option value="">All</option>
                                        <option value="1">Displayed</option>
                                        <option value="0">Not Displayed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label class="m-label m-label--single">Type:</label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_type">
                                        <?php echo $so_select?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
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
                </div>
                <div class="col-xl-3 order-1 order-xl-2 m--align-right">
                    <a href="javascript:openSource(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon m-btn--pill">
                        <span>
                            <i class="la la-plus-circle"></i>
                            <span>
                                New Source
                            </span>
                        </span>
                    </a>
                    <div class="m-separator m-separator--dashed d-xl-none"></div>
                </div>
            </div>
        </div>
        <!--end: Search Form -->
<!--begin: Datatable -->
        <div class="m_datatable" id="local_data"></div>
        <!--end: Datatable -->
    </div>
</div>
</div>

<div class="modal fade" id="sourceModal" role="dialog" aria-labelledby="sourceModalLabel" aria-hidden="true">
	<form class="m-form" name="sourceForm" id="sourceForm" action="javascript:saveSource()">
    <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="sourceModalLabel">Lead Source Management</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            

	<input type="hidden" name="SourceID" id="SourceID" value="" />
    <div class="m-portlet__body">
        <div class="m-form__section m-form__section--first">
            <div class="form-group m-form__group row">
                <label class="col-lg-3 col-form-label">Source:</label>
                <div class="col-lg-6">
                    <input type="text" class="form-control m-input" name="Source_name" id="Source_name" placeholder="Source Name" required="required">
                    <span class="m-form__help">
                        Enter source name as it will appear in the drop down menus
                    </span>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label class="col-lg-3 col-form-label">
                    Type:
                </label>
                <div class="col-lg-6">
                	<select class="form-control m-input" name="Source_type" id="Source_type">
						<?php echo $so_select?>
                    </select>
                    <span class="m-form__help">
                        Select the type of source <a href="/leadsourcetypes">click here to add source type</a>
                    </span>
                </div>
            </div>
            <div class="m-form__group form-group row">
                <label class="col-lg-3 col-form-label">
                    Display Source:
                </label>
                <div class="col-lg-6">
                    <div class="m-radio-list">
                    	<label class="m-radio m-radio--bold">
                        	<input type="radio" name="Source_display" id="source_display" class="genderRadio" value="1">
                            Shown            	
                            <span></span>
        				</label>
                        <label class="m-radio m-radio--bold">
                        	<input type="radio" name="Source_display" id="source_notdisplay" class="genderRadio" value="0">
                            Not Shown
                            <span></span>
        				</label>
                    </div>
                    <span class="m-form__help">
                        If the source will apear in public drop down menu
                    </span>
                </div>
            </div>
            <div class="m-form__group form-group row">
                <label class="col-lg-3 col-form-label">
                    Status:
                </label>
                <div class="col-lg-6">
                    <div class="m-radio-list-inline">
                    	<label class="m-radio m-radio--bold">
                        	<input type="radio" name="Source_status" id="source_active" class="genderRadio" value="1">
                            Active            	
                            <span></span>
        				</label>
                        <label class="m-radio m-radio--bold">
                        	<input type="radio" name="Source_status" id="source_inactive" class="genderRadio" value="0">
                            Inactive
                            <span></span>
        				</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
          
            
            </div>
			<div class="modal-footer">
            	<button type="button" class="btn btn-danger pull-left" id="button-remove-source" onclick="removeSource()" disabled="disabled">Delete Source</button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
    </form>
</div>



<!--begin::Modal-->
<div class="modal fade" id="leadSourceTypeModal" tabindex="-1" role="dialog" aria-labelledby="leadSourceTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadSourceTypeModalLabel">
                    Lead Source Types
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        &times;
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <form>

<div id="m_repeater_2">
    <div class="form-group  m-form__group row">
        <label  class="col-lg-3 col-form-label">
            Source Types:
        </label>
        <div data-repeater-list="" class="col-lg-9">
            <?php
			$st_sql = "SELECT* FROM DropDown_SourceType WHERE 1 ORDER BY SourceType_text ASC";
			$st_snd = $DB->get_multi_result($st_sql);
			foreach($st_snd as $st_dta):
			?>
            <div data-repeater-item class="m--margin-bottom-10">
                <div class="input-group">
                    <input type="text" class="form-control form-control-danger" name="SourceType" value="<?php echo $st_dta['SourceType_text']?>" />
                    <span class="input-group-btn">
                        <a href="#" data-repeater-delete="" class="btn btn-danger m-btn m-btn--icon">
                            <i class="la la-close"></i>
                        </a>
                    </span>
                </div>
            </div>
            <?php
			endforeach;
			?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3"></div>
        <div class="col">
            <div data-repeater-create="" class="btn btn btn-warning m-btn m-btn--icon">
                <span>
                    <i class="la la-plus"></i>
                    <span>
                        Add
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

<script>
var datatable;
var table_options = {
	data: {
		type: 'remote',
		source: {
			read: {
				url: '/ajax/getSourceTableData.php',
				method: 'POST',
				params: {
					// custom query params
					query: {
						SQL: "<?php echo str_replace('\n', ' ', $u_sql)?>",
						EmployeeID: <?php echo $_SESSION['system_user_id']?>
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
		pageSize: 10,
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
		input: $('#generalSearch'),
		delay: 500,
	},
	columns: [{
		field: "SourceID",
		title: "#",
		width: 50,
		sortable: !1,
		selector: !1,
		textAlign: "center"
	}, {
		field: "Source_name",
		title: "Source"
	}, {
		field: "Source_type",
		title: "Type"
	}, {
		field: "Source_display",
		title: "Display",
		template: function(e) {
			var b = {
				1: {
					title: "Shown",
					class: "m-badge--danger"
				},
				0: {
					title: "Not Shown",
					class: "m-badge--info"
				}
			};
			return '<span class="m-badge ' + b[e.Source_display].class + ' m-badge--wide">' + b[e.Source_display].title + '</span>'
		}
	}, {
		field: "Source_status",
		title: "Status",
		template: function(e) {
			var a = {
				1: {
					title: "Active",
					class: "m-badge--primary"
				},
				0: {
					title: "Inactive",
					class: " m-badge--metal"
				}
			};
			return '<span class="m-badge ' + a[e.Source_status].class + ' m-badge--wide">' + a[e.Source_status].title + '</span>'
		}
	}, {
		field: "Actions",
		width: 110,
		title: "Actions",
		sortable: !1,
		overflow: "visible",
		template: function(e) {
			return '<button type="button" onclick="openSource('+e.SourceID+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></button>';
		}
	}]		
};
datatable = $('#local_data').mDatatable(table_options);

var query = datatable.getDataSourceQuery();
$('#m_form_status').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.Source_status = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	//$('#loadingTableBlock').show();
}).val(typeof query.Source_status !== 'undefined' ? query.Source_status : '1');

$('#m_form_type').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.Source_type = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	//$('#loadingTableBlock').show();
}).val(typeof query.Source_type !== 'undefined' ? query.Source_type : '');


$('#m_form_display').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.Source_display = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	//$('#loadingTableBlock').show();
}).val(typeof query.Source_display !== 'undefined' ? query.Source_display : '');	
$('#m_form_status, #m_form_type, #m_form_display').selectpicker();
var query = datatable.getDataSourceQuery();
query.Source_status = 1;
datatable.setDataSourceQuery(query);
datatable.load();

$("#m_repeater_2").repeater({
	initEmpty: !1,
	defaultValues: {
		"text-input": "foo"
	},
	show: function() {
		$(this).slideDown()
	},
	hide: function(e) {
		confirm("Are you sure you want to delete this element?") && $(this).slideUp(e)
	}
});


function openSource(id) {
	$('#sourceModal').modal('show');
	$('#SourceID').val(id);
	if(id == 0)	{
		$('#source_active').prop('checked', true);
		$('#source_inactive').prop('checked', false);
		$('#Source_type').val('');
		$('#Source_name').val('');
		$('#button-remove-source').attr('disabled', true);		
	} else {
		$('#button-remove-source').attr('disabled', false);
		$.post('/ajax/otherStuff.php?action=leadSource_get', {
			sourceID: id
		}, function(data) {
			if(data.Source_status == 1) {
				$('#source_active').prop('checked', true);
				$('#source_inactive').prop('checked', false);
			} else {
				$('#source_active').prop('checked', false);
				$('#source_inactive').prop('checked', true);
			}
			if(data.Source_display == 1) {
				$('#source_display').prop('checked', true);
				$('#source_notdisplay').prop('checked', false);	
			} else {
				$('#source_display').prop('checked', false);
				$('#source_notdisplay').prop('checked', true);					
			}
			$('#Source_type').val(data.Source_type);
			$('#Source_name').val(data.Source_name);
		}, "json");		
	}
}
function saveSource() {
	var formData = $('#sourceForm').serializeArray();
	console.log(formData);
	$.post('/ajax/otherStuff.php?action=leadSource_put', formData, function(data) {
		toastr.success(data.message);
		$('#sourceModal').modal('hide');
		datatable.reload();		
	}, "json");
	
}
function removeSource() {
	var choice = confirm('Are you sure you want to remove this source? This action cannot be undone.');
	if(choice) {
		var id = $('#SourceID').val();
		$.post('/ajax/otherStuff.php?action=leadSource_drop', {
			sourceID: id
		}, function(data) {
			if(data.found == 0) {
				toastr.warning('Source Removed');
				$('#sourceModal').modal('hide');
				datatable.reload();	
			} else {
				alert('Unable to remove this source because existing records have this listed as their source. Make this source inactive to remove from drop downs.');
			}
		}, "json");
	}
}
</script>