<?php
include_once("class.record.php");
include_once("class.tasks.php");
include_once("class.encryption.php");
$RECORD = new Record($DB);
$TASKS = new Tasks($DB, $RECORD);
$ENC = new encryption();
$DB->setTimeZone();


$preSelArray = array(0, 2, 3, 6);
$sel_sql = "SELECT * FROM DropDown_DateStatus ORDER BY StatusOrder ASC";
$sel_snd = $DB->get_multi_result($sel_sql);
ob_start();
foreach($sel_snd as $sel_dta):
	?><option value="<?php echo $sel_dta['Date_status']?>" <?php echo (in_array($sel_dta['Date_status'], $preSelArray)? 'selected':'')?>><?php echo $sel_dta['Date_statusText']?></option><?php
endforeach;
$sel_block = ob_get_clean();

//$uid = '169003';
$uid = $_SESSION['system_user_id'];

$int_sql = "
SELECT
	PersonsDates.PersonsDates_id,
	FROM_UNIXTIME(PersonsDates.PersonsDates_dateCreated, '%Y-%m-%d') as Created,
	PersonsDates.PersonsDates_status,
	DropDown_DateStatus.Date_statusText,
	DropDown_DateStatus.kimsClass,
	PersonsDates.PersonsDates_text,
	(SELECT CONCAT(Persons.FirstName,' ',Persons.LastName) FROM Persons WHERE Person_id=PersonsDates.PersonsDates_participant_1) as Participant1,
	(SELECT DropDown_DateStatus.Date_statusText FROM DropDown_DateStatus WHERE DropDown_DateStatus.Date_status=PersonsDates.PersonsDates_participant_1_status) as Participant1_StatusText,
	PersonsDates.PersonsDates_participant_1_status,
	PersonsDates.PersonsDates_participant_1_rank,
	(SELECT CONCAT(Persons.FirstName,' ',Persons.LastName) FROM Persons WHERE Person_id=PersonsDates.PersonsDates_participant_2) as Participant2,
	(SELECT DropDown_DateStatus.Date_statusText FROM DropDown_DateStatus WHERE DropDown_DateStatus.Date_status=PersonsDates.PersonsDates_participant_2_status) as Participant2_StatusText,
	PersonsDates.PersonsDates_participant_2_status,
	PersonsDates.PersonsDates_participant_2_rank,
	FROM_UNIXTIME(PersonsDates.PersonsDates_dateExecuted, '%Y-%m-%d') as Executed
FROM
	PersonsDates
	INNER JOIN DropDown_DateStatus ON DropDown_DateStatus.Date_status=PersonsDates.PersonsDates_status
WHERE
	PersonsDates_assignedTo='".$uid."'
ORDER BY
	PersonsDates_dateCreated DESC
";
//echo $int_sql;
$methodSQL = trim(preg_replace('/\s+/', ' ', $int_sql));
?>
<div class="m-content">
	<div class="m-portlet m-portlet--head-sm m-portlet--mobile " id="datatable-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
                    	My Introductions/Dates
						<small>All introduction records that are assigned to me as the matchmaker</small>
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
                            <div class="col-md-9">
                                <div class="m-form__group m-form__group--inline">
                                    <div class="m-form__label">
                                        <label>
                                            Status:
                                        </label>
                                    </div>
                                    <div class="m-form__control">
                                        <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_status" multiple="multiple">
                                            <?php echo $sel_block?>
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
            <div class="m_datatable" id="my-intros-table"></div>
            <!--end: Datatable -->
        </div>
    </div>


</div>
<script>
	var datatable;
	var table_options = {
		data: {
			type: 'remote',
			source: {
				read: {
					url: '/ajax/getIntroTableData.php',					
					method: 'POST',
					params: {
						// custom query params
						query: {
							//SQL: "<?php echo $ENC->encrypt(str_replace('\n', ' ', $methodSQL))?>",
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
			footer: true					
		},
		filterable: true,		
		pagination: true,
		sortable: true,
		search: {
			input: $('#generalSearch'),
			delay: 500,
		},
		columns: [{
			field		:	"Created",
			title		:	"Created",
			width		:	85
		},{
			field		:	"PersonsDates_id",
			title		:	"#",
			template	:	'<a href="/intro/{{PersonsDates_id}}" class="btn btn-danger m-btn m-btn--icon btn-sm m-btn--icon-only m-btn--pill" target="_blank"><i class="la la-heart-o"></i></a>',
			width		: 	35
		},{
			field		:	"Date_statusText",
			title		:	"Intro Status",
			width		:	100,
			template	:	'<span class="m-badge m-badge--{{kimsClass}} m-badge--wide">{{Date_statusText}}</span>'
		},{
			field	:	"Participant1",
			title	:	"Participant 1",
			width	:	150,
			template:	'<span class="m-badge m-badge--{{Participant1_kimsClass}}" title="{{Participant1_StatusText}}" data-toggle="m-tooltip"></span> {{Participant1}} '
		},{
			field	:	"Participant2",
			title	:	"Participant 2",
			width	:	150,
			template:	'<span class="m-badge m-badge--{{Participant2_kimsClass}}" title="{{Participant2_StatusText}}" data-toggle="m-tooltip"></span> {{Participant2}} '
		},{
			field	:	"lastNoteDate",
			title	:	"Last Note",
			width	:	85
		},{
			field	:	"lastNote",
			title	:	"Last Note",
			//template:	'<div class="truncate">{{lastNote}}</div>'
			template:	function(e) {
				StrippedString = e.lastNote.replace(/(<([^>]+)>)/ig,"");	
				return 	'<div class="truncate" title="'+StrippedString+'" data-toggle="m-tooltip">'+StrippedString+'</div>';
			}
		},{
			field	:	"Executed",
			title	:	"Next Action",
			width	:	85,
			template:	'<div class="tuncate">{{Executed}}</div>'
		}]
	};
	datatable = $('#my-intros-table').mDatatable(table_options).on('m-datatable--on-ajax-done', function ( e, settings, json, xhr ) {		
		setTimeout(function() {
			//mApp.unblock("#datatable-portlet");
			mApp.init();
		}, 500);
	});
	
	var query = datatable.getDataSourceQuery();
	
	$('#m_form_status').on('change', function() {
		// shortcode to datatable.getDataSourceParam('query');
		var query = datatable.getDataSourceQuery();
		query.PersonsDates_status = $("#m_form_status").select2("val");
		console.log(query);
		// shortcode to datatable.setDataSourceParam('query', query);
		datatable.setDataSourceQuery(query);
		datatable.load();
	});
	
	jQuery(document).ready(function() {
		 $("#m_form_status").select2({ theme: "classic" });
		 document.title = <?php echo json_encode("MY INTROS - (KISS) Kelleher International Software System")?>;
	});
</script>

