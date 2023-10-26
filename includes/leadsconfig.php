<?php
$u_sql = "SELECT * FROM LeadStages WHERE 1";
?>

<script type="text/javascript" src="/assets/global/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js"></script>
<link href="/assets/global/plugins/bootstrap-colorpicker/css/colorpicker.css" rel="stylesheet" type="text/css" />

<div class="m-content">
	<div class="m-portlet m-portlet--mobile">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        Lead Stages/Status Management
                        <small>
                            list of all available stage for a lead to be associated with
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
        	<div class="m_datatable" id="local_data"></div>
        </div>
	</div>
</div>

<div class="modal fade" id="stageModal" role="dialog" aria-labelledby="stageModalLabel" aria-hidden="true">
	<form class="m-form" name="sourceForm" id="sourceForm" action="javascript:saveStage()">
    <input type="hidden" name="SourceID" id="SourceID" value="" />
    <div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="stageModalLabel">Edit Lead Stage Color</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

<div class="m-portlet__body">
    <div class="m-form__section m-form__section--first">
        <div class="form-group m-form__group row">
            <label class="col-lg-3 col-form-label">
                Stage:
            </label>
            <div class="col-lg-9">
                <input type="text" class="form-control m-input" name="LeadStages_name" id="LeadStages_name" readonly="readonly">
            </div>
        </div>
        <div class="form-group m-form__group row">
            <label class="col-lg-3 col-form-label">
                Stage Color:
            </label>
            <div class="col-lg-3">
                <input type="color" id="colorpicker" name="color" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="#bada55" style="width:100%; height:20px;"> 
            </div>
            <div class="col-lg-3">
            	<input type="text" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" class="form-control m-input" value="#bada55" id="LeadStage_hex" name="LeadStage_hex"></input>
            </div>
        </div>        
    </div>
</div>          
            
            </div>
            <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="saveStage()">Save</button>
			</div>
		</div>
	</div>
    </form>
</div>

<script>
$('#colorpicker').on('change', function() {
	$('#LeadStage_hex').val(this.value);
});
$('#LeadStage_hex').on('change', function() {
  $('#colorpicker').val(this.value);
});

var datatable;
var table_options = {
	data: {
		type: 'remote',
		source: {
			read: {
				url: '/ajax/getStagesTableData.php',
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
		pageSize: 20,
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
		field: "LeadStages_id",
		title: "#",
		width: 50,
		selector: !1,
		textAlign: "center"
	}, {
		field: "LeadStages_name",
		title: "Stage"
	}, {
		field: "LeadStage_hex",
		title: "Color",
		template: function(e) {
			return '<span class="m-badge m-badge--metal m-badge--wide" style="background-color:'+e.LeadStage_hex+';">'+e.LeadStage_hex+'</span>';
		}
	}, {
		field: "Actions",
		width: 110,
		title: "Actions",
		sortable: !1,
		overflow: "visible",
		template: function(e) {
			return '<button type="button" onclick="openStage('+e.LeadStages_id+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></button>';
		}
	}]		
};
datatable = $('#local_data').mDatatable(table_options);

function openStage(id) {	
	$('#SourceID').val(id);
	$.post('/ajax/otherStuff.php?action=leadStage_get', {
		stageID: id
	}, function(data) {
		$('#LeadStages_name').val(data.LeadStages_name);
		$('#LeadStage_hex').val(data.LeadStage_hex);
		$('#colorpicker').val(data.LeadStage_hex);
		$('#stageModal').modal('show');
	}, "json");	
}
function saveStage() {
	var formData = $('#sourceForm').serializeArray();
	console.log(formData);
	$.post('/ajax/otherStuff.php?action=leadStage_put', formData, function(data) {
		toastr.success(data.message);
		$('#stageModal').modal('hide');
		datatable.reload();		
	}, "json");
	
}

</script>