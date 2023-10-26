<?php
include_once("class.users.php");
$USER = new Users($DB);

$u_sql = "
SELECT
	user_id,
	firstName,
	lastName,
	email,
	Users.userClass_id,
	userClass_name,
	userGender,
	userDOB,
	userStatus,
	IF(userStatus = '0', 'Inactive', 'Active') as userStatusDisplay,
	userClass_class,
	userClass_dotclass
FROM
	Users
	LEFT JOIN UserClasses ON UserClasses.userClass_id=Users.userClass_id
WHERE
	1
ORDER BY
	lastName ASC
";
$u_snd = $DB->get_multi_result($u_sql);
$U_DATA = json_encode($u_snd);
?>
<div class="m-portlet m-portlet--mobile">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
                    Users
                    <small>
                        system user accounts
                    </small>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="/users/0" class="m-portlet__nav-link btn btn-accent m-btn m-btn--pill">
						<i class="la la-user-plus"></i>
						<span>
							New User
						</span>
					</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="m-portlet__body">
        <!--begin: Search Form -->
        <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
            <div class="row align-items-center">
                <div class="col-xl-8 order-2 order-xl-1">
                    <div class="form-group m-form__group row align-items-center">
                        <div class="col-md-4">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label>
                                        Status:
                                    </label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_status">
                                        <option value="1" selected="selected">Active</option>
                                        <option value="0">Inactive</option>
                                        <!--<option value="3">Locked</option>-->
                                        <option value="">All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label class="m-label m-label--single">Class:</label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_type">
                                        <?php echo $USER->select_getUserClasses()?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
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
                </div>
				<!--
                <div class="col-xl-4 order-1 order-xl-2 m--align-right">
                    <a href="/users/0" class="btn btn-accent m-btn m-btn--custom m-btn--icon m-btn--pill">
                        <span>
                            <i class="la la-user-plus"></i>
                            <span>
                                New User
                            </span>
                        </span>
                    </a>
                    <div class="m-separator m-separator--dashed d-xl-none"></div>
                </div>
				-->
            </div>
        </div>
        <!--end: Search Form -->
<!--begin: Datatable -->
        <div class="m_datatable" id="local_data"></div>
        <!--end: Datatable -->
    </div>
</div>
<script>
var a, e;
var DatatableDataLocalDemo = function() {
    var e = function() {
        var e = JSON.parse('<?php echo $U_DATA?>'),
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
                    field: "user_id",
                    title: "#",
                    width: 50,
                    sortable: !1,
                    selector: !1,
                    textAlign: "center"
                }, {
                    field: "firstName",
                    title: "First Name"
                }, {
                    field: "lastName",
                    title: "Last Name"
                }, {
                    field: "userStatusDisplay",
                    title: "Status"
                }, {
                    field: "userClass_name",
                    title: "Type",
                    template: '<span class="m-badge {{userClass_dotclass}} m-badge--dot"></span>&nbsp;<span class="m--font-bold {{userClass_class}}">{{userClass_name}}</span>'
                }, {
                    field: "Actions",
                    width: 110,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="/users/'+e.user_id+'" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
                    }
                }],				
            }),
            i = a.getDataSourceQuery();
        	$("#m_form_status").on("change", function() {
            	a.search($(this).val(), "userStatus")
        	}).val(void 0 !== i.Status ? i.Status : "1"), 
			
			$("#m_form_type").on("change", function() {
            	a.search($(this).val(), "userClass_id")
        	}).val(void 0 !== i.Type ? i.Type : ""), 
			
			$("#m_form_status, #m_form_type").selectpicker()
			a.search(1, "userStatus")
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableDataLocalDemo.init();
	e.reload();
});
</script>