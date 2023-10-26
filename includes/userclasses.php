<?php
include_once("class.users.php");
$USER = new Users($DB);

$uc_sql = "SELECT userClass_name, userClass_desc, userClass_id FROM UserClasses WHERE 1";
$uc_snd = $DB->get_multi_result($uc_sql);

foreach($uc_snd as $uc_dta):
	$UC_DATA_ARRAY[] = array(
		'userClass_id'	=>	$uc_dta['userClass_id'],
		'userClass_name'	=>	htmlspecialchars($uc_dta['userClass_name'], ENT_QUOTES),
		'userClass_desc'	=>	htmlspecialchars($uc_dta['userClass_desc'], ENT_QUOTES)
	);
endforeach;
$UC_DATA = json_encode($UC_DATA_ARRAY);
?>


<div class="m-content">
	<div class="m-portlet m-portlet--mobile">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<h3 class="m-portlet__head-text">User Classes <small>Defined permisisons for specific user classes</small></h3>
				</div>
			</div>
			<div class="m-portlet__head-tools">&nbsp;</div>
		</div>
        <div class="m-portlet__body">
            <!--begin: Search Form -->
            <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
                <div class="row align-items-center">
                    <div class="col-xl-8 order-2 order-xl-1">
                        <div class="form-group m-form__group row align-items-center">                          
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
                    </div>
                    <div class="col-xl-4 order-1 order-xl-2 m--align-right">
                        <a href="javascript:openucModal(0)" class="btn btn-accent m-btn m-btn--custom m-btn--icon m-btn--pill">
                            <span>
                                <i class="la la-user"></i>
                                <span>
                                    New User Class
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
<div class="modal fade" id="ucModal" tabindex="-1" role="dialog" aria-labelledby="ucModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ucModalLabel">
                    User Class Add/Edit
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        &times;
                    </span>
                </button>
            </div>
            <div class="modal-body">
<form id="ucForm" action="javascript:;">
<input type="hidden" name="userClass_id" id="userClass_id" value="" />

<div class="form-group m-form__group row">
    <div class="col-lg-12">
        <label>
            User Class:
        </label>
        <input type="text" name="userClass_name"id="userClass_name" class="form-control m-input" value="" />
        <div class="form-control-feedback" style="display:none;">
            Name of the user class
        </div>
    </div>
    <div class="col-lg-12">
        <label class="">
            Description:
        </label>
        <textarea name="userClass_desc" id="userClass_desc" class="form-control m-input"></textarea>
    </div>
</div>                

<div class="form-group m-form__group row">
    <div class="col-lg-6">
        <label>
            <strong>System</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('System_1', array())?>
    </div>
    <div class="col-lg-6">
        <label>
            <strong>System</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('System_2', array())?>
    </div>
</div>
<div class="form-group m-form__group row">    
    <div class="col-lg-4">
        <label>
            <strong>Access</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('Access', array())?>
        <p>&nbsp;</p>
        <label>
            <strong>Dashboard</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('Dashboard', array())?>
    </div>
    
    <div class="col-lg-4">
        <label>
            <strong>Actions</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('Actions', array())?>
    </div>
    <div class="col-lg-4">
    	<label>
            <strong>Make Record Types</strong>
        </label>
        <?php echo $USER->render_userPermissionForm('Records', array())?>        
    </div>                
</div>

</form>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="button-remove-uc" onclick="deleteUserClass()">Delete User Class</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="button-save-uc" onclick="saveucModal()">Save</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="ucUpdateModal" tabindex="-1" role="dialog" aria-labelledby="ucUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ucUpdateModalLabel">
                    Update All Users of Class
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        &times;
                    </span>
                </button>
            </div>
            <div class="modal-body">
            
            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="button-save-uc" onclick="updateWholeClass()">Update Selected (<span id="users-selected-count">0</span>) Users</button>
            </div>
		</div>
	</div>
</div>                        
<!--end::Modal-->


<script>
var DatatableDataLocalDemo = function() {
    var e = function() {
        var e = JSON.parse('<?php echo $UC_DATA?>'),
            a = $(".m_datatable").mDatatable({
                data: {
                    type: "local",
                    source: e,
                    pageSize: 10
                },
                layout: {
                    theme: "default",
                    class: "",
                    scroll: !1,
                    footer: !1
                },
                sortable: !0,
                pagination: !0,
                search: {
                    input: $("#generalSearch")
                },
                columns: [{
                    field: "userClass_id",
                    title: "#",
                    width: 50,
                    sortable: !1,
                    selector: !1,
                    textAlign: "center"
                }, {
                    field: "userClass_name",
                    title: "User Class",
					width: 100
                }, {
                    field: "userClass_desc",
                    title: "Description"             
                }, {
                    field: "Actions",
                    width: 110,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="javascript:openucModal('+e.userClass_id+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t<a href="javascript:openucUpdateModal('+e.userClass_id+')" class="m-portlet__nav-link btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill" title="Update Class"><i class="la la-list-alt"></i></a>\t\t\t\t'
                    }
                }]
            }),
            i = a.getDataSourceQuery();
        $("#m_form_status").on("change", function() {
            a.search($(this).val(), "userStatus")
        }).val(void 0 !== i.Status ? i.Status : ""), $("#m_form_type").on("change", function() {
            a.search($(this).val(), "userClass_id")
        }).val(void 0 !== i.Type ? i.Type : ""), $("#m_form_status, #m_form_type").selectpicker()
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableDataLocalDemo.init();
	
	$(document).on('change', '.userIDs', function() {
		//alert('checkbox changed');
		calculateTotalChanged();
	});
});
function updateWholeClass() {
	var ucid = $('#userClass_idValue').val();
	//alert(ucid);
	var uids = new Array();
	var index = 0;
	$('.userIDs').each(function() {
		if($(this).is(':checked')) {
			uids[index] = $(this).val();
			index++;
		}
	});
	console.log(uids);
	$.post('/ajax/userMgt.php?action=userClassAllUpdate', {
		classID: ucid,
		userIDs: uids
	}, function(data) {
		//console.log(data);
		alert('User Class Users Updated');
		$('#ucUpdateModal').modal('hide');
		$('#ucUpdateModal .modal-body').html('');		
	});
}
function calculateTotalChanged() {
	var count = 0;
	$('.userIDs').each(function() {
		if($(this).is(':checked')) {
			count++;
		}
	});
	$('#users-selected-count').html(count);	
}
function checkNone() {
	$('.userIDs').each(function() {
		$(this).attr('checked', false);
	});
	calculateTotalChanged();
}
function checkAll() {
	$('.userIDs').each(function() {
		$(this).attr('checked', true);
	});
	calculateTotalChanged();
}
function openucUpdateModal(id) {
	$('#ucUpdateModal').modal('show');
	mApp.block("#ucUpdateModal .modal-content", {
		overlayColor: "#000000",
		type: "loader",
		state: "success",
		message: "Verifying User Class Members..."	
	});
	$.post('/ajax/userMgt.php?action=userClassListVerify', {
		classID: id		
	}, function(data) {
		$('#ucUpdateModal .modal-body').html(data.html);
		calculateTotalChanged();
		mApp.unblock("#ucUpdateModal .modal-content");		
	}, "json");	
}
function openucModal(id) {
	$('#ucModal').modal('show');
	$('#userClass_id').val(id);
	if(id == 0) {
		$('#userClass_name').val('');
		$('#userClass_desc').val('');
		$('#button-remove-uc').hide();
	} else {
		$.post('/ajax/userMgt.php?action=getuserClass', {
			id: id
		}, function(data) {
			$('#userClass_name').val(data.userClass_name);
			$('#userClass_desc').val(data.userClass_desc);
			$('#button-remove-uc').show();
			console.log(data);
							
				
			$('.permissionChoice').each(function() {
				for(l=0; l<data.Permissions.length; l++) {
					console.log(data.Permissions[l]);
					if($(this).val() == data.Permissions[l]) {
						$(this).prop('checked', true);
					}
				}
			});
		}, "json");
	}
}
function saveucModal() {
	var formData = $('#ucForm').serializeArray();
	$.post('/ajax/userMgt.php?action=saveuserClass', formData, function(data) {
		document.location.reload(true);		
	});
}
function deleteUserClass() {
	var choice = confirm('Are you sure you want to remove this user class?');
	if(choice) {
		$.post('/ajax/userMgt.php?action=removeClass', {
			class_id: $('#userClass_id').val()
		}, function(data) {
			if (data.success) {
				document.location.reload(true);
			} else {
				alert(data.message);
			}			
		}, "json");
	}
}
</script>