<?php
$u_sql = "SELECT * FROM SellableEntities WHERE 1";
$u_snd = $DB->get_multi_result($u_sql);
$U_DATA = json_encode($u_snd, JSON_HEX_APOS);
?>
<div class="m-content">

<div class="m-portlet m-portlet--mobile">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
                    Sales Admin
                    <small>
                        management of sellable entities
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
						<!--
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
						-->
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
                    <a href="javascript:openPackage(0);" class="btn btn-primary m-btn m-btn--custom m-btn--icon m-btn--pill">
                        <span>
                            <i class="la la-plus-circle"></i>
                            <span>
                                New Package
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

<!--begin::Modal-->
<div class="modal fade" id="packageModal" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
	<form class="m-form" name="packageForm" id="packageForm" action="javascript:savePackage()">
    <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="packageModalLabel">Sales Package Management</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<input type="hidden" name="PackageID" id="PackageID" value="" />
				<div class="m-portlet__body">
					 <div class="m-form__section m-form__section--first">
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Name:</label>
							<div class="col-lg-6">
								<input type="text" class="form-control m-input" name="Package_name" id="Package_name" placeholder="Sales Package Name" required="required">
								<span class="m-form__help">
									Enter name as it will appear in the drop down menus
								</span>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Description:</label>
							<div class="col-lg-6">
								<textarea class="form-control m-input" name="Package_desc" id="Package_desc"></textarea>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Price:</label>
							<div class="col-lg-6">
							<div class="input-group m-input-group">
								<span class="input-group-addon">$</span>
								<input type="text" class="form-control m-input" name="Package_price" id="Package_price" placeholder="0.00">
							</div>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Introductions:</label>
							<div class="col-lg-6">
								<input type="number" class="form-control m-input" name="Package_quantity" id="Package_quantity">
								<span class="m-form__help">
									Number of introductions/dates
								</span>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Hold Time:</label>
							<div class="col-lg-6">
							<div class="input-group">
								<input type="number" class="form-control m-input" name="Package_holdTime" id="Package_holdTime">
								<span class="input-group-addon">months</span>
							</div>
							</div>
						</div>
						<div class="form-group m-form__group row">
							<label class="col-lg-3 col-form-label">Term:</label>
							<div class="col-lg-6">
							<div class="input-group">
								<input type="number" class="form-control m-input" name="Package_term" id="Package_term">
								<span class="input-group-addon">months</span>
							</div>
							</div>
						</div>
						<div class="m-form__group form-group row">
							<label class="col-lg-3 col-form-label">
								Transferable:
							</label>
							<div class="col-lg-6">
								<div class="m-radio-list-inline">
									<label class="m-radio m-radio--bold">
										<input type="radio" name="Package_transfer" id="package_transferable" class="genderRadio" value="1">
										Yes           	
										<span></span>
									</label>
									&nbsp;&nbsp;&nbsp;
									<label class="m-radio m-radio--bold">
										<input type="radio" name="Package_transfer" id="package_nontransferable" class="genderRadio" value="0">
										No
										<span></span>
									</label>
								</div>
							</div>
						</div>
						<div class="m-form__group form-group row">
							<label class="col-lg-3 col-form-label">
								Status:
							</label>
							<div class="col-lg-6">
								<div class="m-radio-list-inline">
									<label class="m-radio m-radio--bold">
										<input type="radio" name="Package_status" id="package_active" class="genderRadio" value="1">
										Active            	
										<span></span>
									</label>
									&nbsp;&nbsp;&nbsp;
									<label class="m-radio m-radio--bold">
										<input type="radio" name="Package_status" id="package_inactive" class="genderRadio" value="0">
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
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
    </form>
</div>
<!--end::Modal-->

<script>
var datatable;
var table_options = {
	data: {
		type: 'remote',
		source: {
			read: {
				url: '/ajax/getSaleEntityData.php',
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
		order: [[ 1, 'asc' ]],
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
		field: "Actions",
		width: 50,
		title: "&nbsp;",
		sortable: !1,
		overflow: "visible",
		template: function(e) {
			return '<button type="button" onclick="openPackage('+e.SellableEntities_id+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></button>';
		}
	}, {
		field: "SellableEntities_name",
		title: "Name",
		width: 200
	}, {
		field: "SellableEntities_description",
		title: "Description",
		width: 280,
		template: function(e) {
			return '<div class="truncate" title="'+e.SellableEntities_description+'">'+e.SellableEntities_description+'</div>';
		}
	}, {
		field: "SellableEntities_price",
		title: "Price",
		width:75
	}, {
		field: "SellableEntities_quantity",
		title: "Intros",
		width:75
	}, {
		field: "SellableEntities_holdTime",
		title: "Hold Time",
		width:75,
		template: function(e) {
			return e.SellableEntities_holdTime + ' mo.';
		}
	}, {
		field: "SellableEntities_term",
		title: "Term",
		width:75,
		template: function(e) {
			return e.SellableEntities_term + ' mo.';
		}
	}, {
		field: "SellableEntities_transfer",
		title: "Transferable",
		template: function(e) {
			var a;
			if(e.SellableEntities_transfer == 1) {
				a = 'Yes';
			} else {
				a = 'No';
			}
			return a;
		}
	}, {
		field: "SellableEntities_active",
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
			return '<span class="m-badge ' + a[e.SellableEntities_active].class + ' m-badge--wide">' + a[e.SellableEntities_active].title + '</span>'
		}
	}]		
};
datatable = $('#local_data').mDatatable(table_options);

var query = datatable.getDataSourceQuery();

$('#m_form_status').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.SellableEntities_active = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	//$('#loadingTableBlock').show();
}).val(typeof query.SellableEntities_active !== 'undefined' ? query.SellableEntities_active : '1');

$('#m_form_status').selectpicker();

var query = datatable.getDataSourceQuery();
query.SellableEntities_active = 1;
datatable.setDataSourceQuery(query);
datatable.load();

function openPackage(id) {
	$('#packageModal').modal('show');
	$('#PackageID').val(id);
	if(id == 0)	{
		$('#package_transferable').prop('checked', false);
		$('#package_nontransferable').prop('checked', false);
		$('#package_active').prop('checked', true);
		$('#package_inactive').prop('checked', false);
		$('#Package_name').val('');
		$('#Package_desc').val('');
		$('#Package_price').val('');
		$('#Package_quantity').val('');
		$('#Package_holdTime').val('');
		$('#Package_term').val('');
		$('#button-remove-package').attr('disabled', true);		
	} else {
		$('#button-remove-package').attr('disabled', false);
		$.post('/ajax/otherStuff.php?action=saleEntity_get', {
			packageID: id
		}, function(data) {
			if(data.SellableEntities_transfer == 1) {
				$('#package_transferable').prop('checked', true);
				$('#package_nontransferable').prop('checked', false);
			} else {
				$('#package_transferable').prop('checked', false);
				$('#package_nontransferable').prop('checked', true);
			}
			if(data.SellableEntities_active == 1) {
				$('#package_active').prop('checked', true);
				$('#package_inactive').prop('checked', false);
			} else {
				$('#package_active').prop('checked', false);
				$('#package_inactive').prop('checked', true);
			}
			$('#Package_name').val(data.SellableEntities_name);
			$('#Package_desc').val(data.SellableEntities_description);
			$('#Package_price').val(data.SellableEntities_price);
			$('#Package_quantity').val(data.SellableEntities_quantity);
			$('#Package_holdTime').val(data.SellableEntities_holdTime);
			$('#Package_term').val(data.SellableEntities_term);
		}, "json");		
	}
}

function savePackage() {
	var formData = $('#packageForm').serializeArray();
	console.log(formData);
	$.post('/ajax/otherStuff.php?action=saleEntity_put', formData, function(data) {
		toastr.success(data.message);
		$('#packageModal').modal('hide');
		datatable.reload();		
	}, "json");
}
</script>
