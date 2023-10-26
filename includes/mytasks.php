<?php
include_once("class.record.php");
include_once("class.tasks.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$TASKS = new Tasks($DB, $RECORD);
$ENC = new encryption();
$DB->setTimeZone();

$t_sql = "SELECT Persons.FirstName, Persons.LastName, Persons.Person_id, Persons.Person_id as Person_id2, PersonActionTypes.ActionType, PersonActions.ActionTypeID, PersonTypes.PersonsTypes_text, PersonActions.ActionPriority, PersonActions.ActionConditions, PersonActions.ActionID, FROM_UNIXTIME(ActionDateTime, '%Y-%m-%d %h:%i%p') as ActionDateTime, PersonActions.ActionCompleted, IF (PersonActions.ActionCompleted = 0 AND PersonActions.ActionDateTime < UNIX_TIMESTAMP(), 2, PersonActions.ActionCompleted) as ActionCompletedStatus, PersonActions.ActionCompletedDate, FROM_UNIXTIME((SELECT ActionNoteCreated FROM PersonActionsNotes WHERE ActionID=PersonActions.ActionID ORDER BY ActionNoteCreated DESC LIMIT 1), '%Y-%m-%d %h:%i%p') as LastNoteDate FROM PersonActions INNER JOIN PersonActionTypes ON PersonActionTypes.ActionTypeID=PersonActions.ActionTypeID INNER JOIN Persons ON Persons.Person_id=PersonActions.ActionPersonID INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id WHERE PersonActions.ActionAssignedTo='".$_SESSION['system_user_id']."' AND PersonActions.ActionCompleted='0'";
//$t_sql = "SELECT Persons.FirstName, Persons.LastName, Persons.Person_id, PersonActionTypes.ActionType, PersonActions.ActionTypeID, PersonTypes.PersonsTypes_text, PersonActions.ActionConditions, PersonActions.ActionID, FROM_UNIXTIME(ActionDateTime, '%Y-%m-%d %h:%i%p') as ActionDateTime, PersonActions.ActionCompleted, PersonActions.ActionCompletedDate, FROM_UNIXTIME((SELECT ActionNoteCreated FROM PersonActionsNotes WHERE ActionID=PersonActions.ActionID ORDER BY ActionNoteCreated DESC LIMIT 1), '%Y-%m-%d %h:%i%p') as LastNoteDate FROM PersonActions INNER JOIN PersonActionTypes ON PersonActionTypes.ActionTypeID=PersonActions.ActionTypeID INNER JOIN Persons ON Persons.Person_id=PersonActions.ActionPersonID INNER JOIN PersonTypes ON PersonTypes.PersonsTypes_id=Persons.PersonsTypes_id WHERE PersonActions.ActionCompleted='0'";
//echo $t_sql;
$t_snd = $DB->get_multi_result($t_sql);
$T_DATA = json_encode($t_snd);
?>
<div class="m-content">
	<div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                        <i class="flaticon-attachment"></i> My Open Tasks
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
                                        <option value="1">Completed Taks</option>
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
									?><option value="<?php echo $tt_dta['ActionTypeID']?>"><?php echo $tt_dta['ActionType']?></option><?php
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
                                    <label class="m-label m-label--single">Record:</label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_rtype">
                                    <option value="" selected>No filter</option>
									<?php
									$tt_sql = "SELECT * FROM PersonTypes WHERE PersonsTypes_id NOT IN (1,2,9)";
									$tt_snd = $DB->get_multi_result($tt_sql);
									foreach($tt_snd as $tt_dta):
									?><option value="<?php echo $tt_dta['PersonsTypes_id']?>"><?php echo $tt_dta['PersonsTypes_text']?></option><?php
									endforeach;
									?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-input-icon m-input-icon--left">
                                <input type="text" class="form-control m-input m-input--solid" placeholder="Table Search..." id="generalSearch">
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
var datatable;
var table_options = {
	data: {
		type: 'remote',
		source: {
			read: {
				url: '/ajax/actions.php?action=myTasksDatatable',
				method: 'POST',
				params: {
					// custom query params
					query: {
						SQL: "<?php echo $ENC->encrypt(str_replace('\n', ' ', $t_sql))?>",
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
		field: "LastName",
		title: "Name",
		template: '<a href="/profile/{{Person_id}}" class="m-link">{{FirstName}} {{LastName}}</a>&nbsp;<a href="/profile/{{Person_id2}}" target="_new" class="m-link"><i class="la la-external-link"></i></a>',
		width: 200
	}, {                
		field: "PersonsTypes_text",
		title: "Record Type",
		width: 125
	}, {
		field: "ActionType",
		title: "Task Type",
		width: 125
	}, {
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
	}, {
		field: "ActionConditions",
		title: "Conditions",
		width: 65
	},{
		field: "ActionDateTime",
		title: "Due By/On"
	},{
		field: "LastNoteDate",
		title: "Last Note"
	},{
		field: "ActionCompletedStatus",
		title: "Status",
		template: function(e) {
			var a = {
				1: {
					title: "COMPLETED",
					state: "success"
				},
				0: {
					title: "OPEN",
					state: "primary"
				},
				2: {
					title: "PAST DUE",
					state: "danger"
				}
			};
			return '<span class="m-badge m-badge--' + a[e.ActionCompletedStatus].state + ' m-badge--dot"></span>&nbsp;<span class="m--font-bold m--font-' + a[e.ActionCompletedStatus].state + '">' + a[e.ActionCompletedStatus].title + "</span>"
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
};
<?php if(!$onDone): ?>
datatable = $('#local_data').mDatatable(table_options);
<?php else: ?>
datatable = $('#local_data').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {
	<?php echo $onDone?>
});
<?php endif; ?>
var query = datatable.getDataSourceQuery();

$('#m_form_status').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.ActionCompleted = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	$('#loadingTableBlock').show();
}).val(typeof query.ActionCompleted !== 'undefined' ? query.ActionCompleted  : '0');

$('#m_form_type').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.ActionTypeID = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	$('#loadingTableBlock').show();
}).val(typeof query.ActionTypeID !== 'undefined' ? query.ActionTypeID : '');

$('#m_form_rtype').on('change', function() {
	// shortcode to datatable.getDataSourceParam('query');
	var query = datatable.getDataSourceQuery();
	query.PersonsTypes_id = $(this).val().toLowerCase();
	// shortcode to datatable.setDataSourceParam('query', query);
	datatable.setDataSourceQuery(query);
	datatable.load();
	$('#loadingTableBlock').show();
}).val(typeof query.PersonsTypes_id !== 'undefined' ? query.PersonsTypes_id : '');

/*
var DatatableDataLocalDemo = function() {
    var e = function() {
        var e = JSON.parse('<?php echo $T_DATA?>'),
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
                
            }),
            i = a.getDataSourceQuery();
        $("#m_form_status").on("change", function() {
            a.search($(this).val(), "ActionCompleted")
        }).val(void 0 !== i.ActionCompleted ? i.ActionCompleted : ""), 
		$("#m_form_type").on("change", function() {
            a.search($(this).val(), "ActionType")
        }).val(void 0 !== i.ActionType ? i.ActionType : ""),
		$("#m_form_rtype").on("change", function() {
            a.search($(this).val(), "PersonsTypes_text")
        }).val(void 0 !== i.PersonsTypes_text ? i.PersonsTypes_text : ""),
		$("#m_form_status, #m_form_type, #m_form_rtype").selectpicker()
    };
    return {
        init: function() {
            e()
        }
    }
}();
*/
jQuery(document).ready(function() {
	 $("#m_form_status").selectpicker();
	 $('#m_form_type').selectpicker();
	 $('#m_form_rtype').selectpicker();	
	 document.title = <?php echo json_encode("MY TASKS - (KISS) Kelleher International Software System")?>; 
});
</script>
<?php $TASKS->render_taskModal(0); ?>