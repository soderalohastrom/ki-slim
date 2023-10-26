<?php
ini_set('memory_limit', '1024M');
set_time_limit (900);

include_once("class.record.php");
include_once("class.tasks.php");
$RECORD = new Record($DB);
$TASKS = new Tasks($DB, $RECORD);

$t_sql = "SELECT Persons.FirstName, Persons.LastName, Persons.Person_id, PersonActionTypes.ActionType, PersonActions.ActionTypeID, PersonTypes.PersonsTypes_text, PersonActions.ActionConditions, PersonActions.ActionID, FROM_UNIXTIME(ActionDateTime, '%Y-%m-%d %h:%i%p') as ActionDateTime, PersonActions.ActionCompleted, PersonActions.ActionCompletedDate, FROM_UNIXTIME((SELECT ActionNoteCreated FROM PersonActionsNotes WHERE ActionID=PersonActions.ActionID ORDER BY ActionNoteCreated DESC LIMIT 1), '%Y-%m-%d %h:%i%p') as LastNoteDate, PersonActions.ActionAssignedTo, (SELECT CONCAT(SUBSTRING(firstName,1,1),' ',lastName) FROM Users WHERE Users.user_id=PersonActions.ActionAssignedTo) as AssignedUser FROM PersonActions INNER JOIN PersonActionTypes ON PersonActionTypes.ActionTypeID=PersonActions.ActionTypeID INNER JOIN Persons ON Persons.Person_id=PersonActions.ActionPersonID INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id WHERE PersonsTypes_text='Lead' ORDER BY ActionDateTime DESC";
//echo $t_sql;
//$t_snd = $DB->get_multi_result($t_sql);
//$T_DATA = json_encode($t_snd);
?>
<div class="m-content">
	<div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        <i class="flaticon-attachment"></i> Lead Tasks
                    </h3>
                </div>
            </div>
        </div>
        <div class="m-portlet__body">
        <!--begin: Search Form -->
        <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
            <div class="row align-items-center">
                <div class="col-xl-12 order-2 order-xl-1">
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
                                       	<option value="" selected>No filter</option>
                                       	<option value="0" checked>Open Tasks</option>
                                        <option value="1">Completed Tasks</option>
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
                                    <option value="" selected>No filter</option>
									<?php
									$tt_sql = "SELECT * FROM PersonActionTypes ORDER BY ActionOrder";
									$tt_snd = $DB->get_multi_result($tt_sql);
									foreach($tt_snd as $tt_dta):
									?><option value="<?php echo $tt_dta['ActionType']?>"><?php echo $tt_dta['ActionType']?></option><?php
									endforeach;
									?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label class="m-label m-label--single">Assigned:</label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_rtype">
                                    <option value="" selected>No filter</option>
									<?php
									$tp_sql = "SELECT DISTINCT(ActionAssignedTo) FROM PersonActions";
									$tp_snd = $DB->get_multi_result($tp_sql);									
									if(isset($tp_snd['empty_result'])) {
										$uniqueUser = array();
									} else {
										foreach($tp_snd as $tp_dta):
											$uniqueUser[] = $tp_dta['ActionAssignedTo'];
										endforeach;
									}
									$usq_sql = "SELECT * FROM Users WHERE user_id IN (".implode(",", $uniqueUser).")";
									echo $usq_sql;
									$isq_snd = $DB->get_multi_result($usq_sql);
									if(!isset($isq_snd['empty_result'])) {
										foreach($isq_snd as $isq_dta):
											?><option value="<?php echo $isq_dta['user_id']?>"><?php echo substr($isq_dta['firstName'], 0, 1)?> <?php echo $isq_dta['lastName']?></option><?php
										endforeach;
									}
									?>
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
            </div>
        </div>
        <!--end: Search Form -->
		
        <!--begin: Datatable -->
        <div class="m_datatable" id="local_data"></div>
        <!--end: Datatable -->
        
    </div>
</div>
<script>
var DatatableDataLocalDemo = function() {
    var e = function() {
        var a = $(".m_datatable").mDatatable({
                data: {
                    type: "remote",
					source: {
						read: {
							url: '/ajax/actions.php?action=leadTasksDatatable',
							method: 'POST',
							params: {
								// custom query params
								query: {
									SQL: "<?php echo str_replace('\n', ' ', $t_sql)?>",
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
                    pageSize: 10,
                    saveState: {
                        cookie: !0,
                        webstorage: !0
                    },
                    serverPaging: !0,
                    serverFiltering: !0,
                    serverSorting: !0
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
                    field: "LastName",
                    title: "Name",
					template: '<a href="/profile/{{Person_id}}">{{FirstName}} {{LastName}}</a>'
                }, {                
                    field: "PersonsTypes_text",
                    title: "Record Type",
					width: 125					
                }, {
					field: "AssignedUser",
					title: "Assigned To"
                }, {
					field: "ActionType",
					title: "Task Type",
					width: 125
				},{
					field: "ActionPriority",
					title: "Priority",
					width: 75,
					template: function(e) {
						var a = {
							'Low': {
								title: "Low",
								state: "secondary"
							},
							'Normal': {
								title: "Normal",
								state: "success"
							},
							'High': {
								title: "High",
								state: "danger"
							},
							'': {
								title: "&nbsp;",
								state: ""
							}
						};
						return '<span class="m-badge m-badge--' + a[e.ActionPriority].state + ' m-badge--wide">' + a[e.ActionPriority].title + '</span>';
					}
				},{
					field: "ActionConditions",
					title: "Conditions",
					width: 200,
					template: '{{ActionConditions}} {{ActionDateTime}}'
				},{
					field: "LastNoteDate",
					title: "Last Note"
				},{
                    field: "ActionCompleted",
                    title: "Status",
                    template: function(e) {
                        var a = {
                            1: {
                                title: "Completed",
                                state: "success"
                            },
                            0: {
                                title: "Open",
                                state: "primary"
                            }
                        };
                        return '<span class="m-badge m-badge--' + a[e.ActionCompleted].state + ' m-badge--dot"></span>&nbsp;<span class="m--font-bold m--font-' + a[e.ActionCompleted].state + '">' + a[e.ActionCompleted].title + "</span>"
                    }
                }, {
                    field: "Actions",
                    width: 110,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="javascript:openAction('+e.ActionID+')" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
                    }
                }]
            }),
            i = a.getDataSourceQuery();
        $("#m_form_status").on("change", function() {
            a.search($(this).val(), "ActionCompleted")
        }).val(void 0 !== i.ActionCompleted ? i.ActionCompleted : 0), 
		$("#m_form_type").on("change", function() {
            a.search($(this).val(), "ActionType")
        }).val(void 0 !== i.ActionType ? i.ActionType : ""),
		$("#m_form_rtype").on("change", function() {
            a.search($(this).val(), "ActionAssignedTo")
        }).val(void 0 !== i.ActionAssignedTo ? i.ActionAssignedTo : ""),
		$("#m_form_status, #m_form_type, #m_form_rtype").selectpicker()
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
</script>
<?php $TASKS->render_taskModal(0); ?>