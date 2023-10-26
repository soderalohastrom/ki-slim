<?php
include_once("class.users.php");
$USER = new Users($DB);

$uc_sql = "SELECT * FROM DropDown_SourceType WHERE 1";
$uc_snd = $DB->get_multi_result($uc_sql);
$UC_DATA = json_encode($uc_snd);
?>


<div class="m-content">
	<div class="m-portlet m-portlet--mobile">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<h3 class="m-portlet__head-text">Lead Source Type Management</h3>
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
                        <a href="javascript:openLST_Modal(0)" class="btn btn-accent m-btn m-btn--custom m-btn--icon m-btn--pill">
                            <span>
                                <i class="la la-user"></i>
                                <span>
                                    New Source Type
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
                    Lead Source Type Add/Edit
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">
                        &times;
                    </span>
                </button>
            </div>
            <div class="modal-body">
<form id="ucForm" action="javascript:;">
<input type="hidden" name="SourceType_id" id="SourceType_id" value="" />

<div class="form-group m-form__group row">
    <div class="col-lg-12">
        <label>
            Source Type:
        </label>
        <input type="text" name="SourceType_text"id="SourceType_text" class="form-control m-input" value="" />
    </div>
    <div class="col-lg-12">
        <label class="">
            Description:
        </label>
        <textarea name="SourceType_desc" id="SourceType_desc" class="form-control m-input"></textarea>
    </div>
</div>
</form>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="button-remove-uc" onclick="deleteLST()">Delete Lead Source Type</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="button-save-uc" onclick="saveLST_Modal()">Save</button>
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
                    field: "SourceType_id",
                    title: "#",
                    width: 50,
                    sortable: !1,
                    selector: !1,
                    textAlign: "center"
                }, {
                    field: "SourceType_text",
                    title: "Source Type",
					width: 100
                }, {
                    field: "SourceType_desc",
                    title: "Description"             
                }, {
                    field: "Actions",
                    width: 110,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="javascript:openLST_Modal('+e.SourceType_id+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
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
    DatatableDataLocalDemo.init()
});
function openLST_Modal(id) {
	$('#ucModal').modal('show');
	$('#SourceType_id').val(id);
	if(id == 0) {
		$('#SourceType_text').val('');
		$('#SourceType_desc').val('');
		$('#button-remove-uc').hide();
	} else {
		$.post('/ajax/otherStuff.php?action=getSourceType', {
			id: id
		}, function(data) {
			$('#SourceType_text').val(data.SourceType_text);
			$('#SourceType_desc').val(data.SourceType_desc);
			$('#button-remove-uc').show();
			console.log(data);
		}, "json");
	}
}
function saveLST_Modal() {
	var formData = $('#ucForm').serializeArray();
	$.post('/ajax/otherStuff.php?action=saveSourceType', formData, function(data) {
		document.location.reload(true);		
	});
}
function deleteLST() {
	var choice = confirm('Are you sure you want to remove this lead source?');
	if(choice) {
		$.post('/ajax/otherStuff.php?action=removeSourceType', {
			class_id: $('#SourceType_id').val()
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