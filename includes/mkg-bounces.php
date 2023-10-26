<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("class.marketing.php");
$marketing = new Marketing();
$bb_sql = "
		SELECT 
			MarketingDeploymentEvents.*,
			ifNull(MarketingDeployments_id, 0) AS DeploymentID,
			ifNull(MarketingDeployments_name, 'n/a') AS DeploymentTitle
		FROM
			MarketingDeploymentEvents
		LEFT JOIN
			MarketingDeployments
			ON MarketingDeploymentEvents_deploymentId = MarketingDeployments_id
		WHERE
			MarketingDeploymentEvents_eventType IN(2,6)
		ORDER BY MarketingDeploymentEvents_date DESC
		";
$bb_snd = $DB->get_multi_result($bb_sql);
if(!array_key_exists('error', $bb_snd) && !array_key_exists('empty_result', $bb_snd)) {
	$bb_data = array();
	foreach($bb_snd as $bb_row) {
		$new_row = array();
		foreach($bb_row as $row_col=>$row_val) {
			$new_row[$row_col] = str_replace("\n", " ", $row_val);
			if($row_col == 'MarketingDeploymentEvents_date') {
				$new_row['MarketingDeploymentEvents_dateFormatted'] = date('n/d/Y', $row_val);
			}
		}
		$bb_data[] = $new_row;
	}
	$bb_data_json = json_encode($bb_data);
} else {
	$bb_data_json = false;
}
?>
<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<div class="m-content">
<div class="m-portlet m-portlet--mobile">	
	<div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
					<i class="flaticon-warning-2"></i>
                    Bounces and Blocks
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="javascript:void(deleteBounces())" class="m-portlet__nav-link btn btn-danger m-btn m-btn--pill">
						<i class="flaticon-cancel"></i>
						<span>
							Delete Selected
						</span>
					</a>
                </li>
            </ul>
        </div>
    </div>
	<div class="m-portlet__body">
	<?php if($bb_data_json !== false) { ?>
		<!--begin: Search Form -->
		<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
				<div class="form-group m-form__group row align-items-center">
					<div class="col-md-4">
						<div class="m-input-icon m-input-icon--left">
							<input type="text" class="form-control m-input" placeholder="Search..." id="m_form_listname">
							<span class="m-input-icon__icon m-input-icon__icon--left">
								<span>
									<i class="la la-search"></i>
								</span>
							</span>
						</div>
						<span class="m-form__help">
							search results for text
						</span>
					</div>
					<div class="col-md-4">
						<div class="m-form__control">
							<select class="form-control m-bootstrap-select" id="m_form_type">
								<option value="">
									All
								</option>
								<option value="bounce">
									Bounce
								</option>
								<option value="blocked">
									Block
								</option>
							</select>
						</div>
						<span class="m-form__help">
							filter on type
						</span>
				</div>
			</div>
		</div>
		<!--end: Search Form -->
		<!--begin: Datatable -->
		<div class="m_datatable" id="local_data"></div>
		<!--end: Datatable -->
	<?php } else { ?>
		<div class="alert alert-info">No bounces or blocks found.</div>
	<?php } ?>
	</div>
</div>
</div>
<!--begin::Delete Confirmation Modal-->
<div class="modal fade" id="delete_confirm" tabindex="-1" role="dialog" aria-labelledby="delete_confirm_title" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="delete_confirm_title">
					Delete Bounces/Blocks
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						&times;
					</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete the selected bounces and/or blocks? All bounces/blocks matching the email address and type of the selected records will also be deleted.</p>
				<p>Deleting bounces will re-enable sending to the associated email addresses, which may adversely affect your sender reputation and deliverabilty if it results in repeat bounces.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					Cancel
				</button>
				<button type="button" class="btn btn-primary" id="delete_confirm_submit">
					Continue
				</button>
			</div>
		</div>
	</div>
</div>
<!--end::Delete Confirmation Modal-->
<!--begin::Delete Result Modal-->
<div class="modal fade" id="delete_result" tabindex="-1" role="dialog" aria-labelledby="delete_result_title" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="delete_result_title">
					Delete Bounces/Blocks
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						&times;
					</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="delete_progress"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					Close
				</button>
			</div>
		</div>
	</div>
</div>
<!--end::Delete Result Modal-->
<script>
var records = Array();
function submitDelete() {
	$('#delete_progress').html('Deletion in progress...');
	$('#delete_confirm').modal('hide');
	$('#delete_result').modal('show');
	$.post('ajax/ajax.deployments.php', { 'action': 'delete_bounces', 'bounce_list': records },
		function(data){
			if(data.success) {
				$('#delete_progress').html('<div class="alert alert-success" role="alert">'+data.msg+'</div>');
			} else {
				$('#delete_progress').html('<div class="alert alert-danger" role="alert">'+data.msg+'</div>');
			}
		}, 'json');
}
function deleteBounces() {
	var index = 0;
	$('.m-datatable__table tr td input[type="checkbox"]').each(function() {
		if($(this).is(':visible') && $(this).is(':checked')) {
			records[index] = $(this).val();
			index++;
		}
	});
	if(records.length == 0) {
		alert('Please select at least one bounce or block to delete.');
	} else {
		$('#delete_confirm').modal('show'); 
	}
}
var DatatableBounces = function() {
    var e = function() {
       	var e = JSON.parse('<?php echo str_replace("'", "\'", $bb_data_json)?>'),
		a = $(".m_datatable").mDatatable({
                data: {
                    type: "local",
                    source: e,
                    pageSize: 10,
					saveState: {
						cookie: false,
						webstorage: false
					},
                },
                layout: {
                    theme: "default",
                    class: "",
                    scroll: !1,
                    // height: 450,
                    footer: !0
                },
                sortable: !0,
                pagination: !0,
                search: {
                    input: $("#m_form_listname")
                },
                columns: [{
					field: "MarketingDeploymentEvents_id",
					title: "#",
					locked:	[{left: 'xl'}],
					sortable: !1,
					width: 40,
					selector: [{'class': 'm-checkbox--solid m-checkbox--brand'}]
				 }, {
                   field: "MarketingDeploymentEvents_dateFormatted",
                    title: "Date",
					type: "date",
                    format: "MM/DD/YYYY",
					width: 70
                }, {
                    field: "MarketingDeploymentEvents_bounceType",
                    title: "Type",
					width: 60,
					template: function(e) {
						if(e.MarketingDeploymentEvents_bounceType == 'bounce') {
							return 'Bounce';
						} else if(e.MarketingDeploymentEvents_bounceType == 'blocked') {
							return 'Block';
						} else {
							return 'Unknown';
						}
					}
                }, {
                    field: "MarketingDeploymentEvents_emailAddress",
                    title: "Email Address",
					overflow: 'visible'
				 }, /*{
					field: "DeploymentTitle",
                    title: "Deployment",
					overflow: 'visible'
				},*/ {
					field: "MarketingDeploymentEvents_bounceReason",
                    title: "Reason",
					width: 2000,
					overflow: 'hidden',
					template: function(e) {
						if(e.MarketingDeploymentEvents_bounceStatusCode != '' && e.MarketingDeploymentEvents_bounceReason.search(e.MarketingDeploymentEvents_bounceStatusCode) == -1) {
							return '<div class="truncate">'+e.MarketingDeploymentEvents_bounceStatusCode+' '+e.MarketingDeploymentEvents_bounceReason+'</div>';
						} else {
							return '<div class="truncate">'+e.MarketingDeploymentEvents_bounceReason+'</div>';
						}
					}
				}/*, {
					field: "MarketingDeploymentEvents_bounceStatusCode",
                    title: "Status Code"
				}*/]
            }),
			i = a.getDataSourceQuery();
		$("#m_form_type").on("change", function() {
            a.search($(this).val(), "MarketingDeploymentEvents_bounceType")
        }),
		$("#m_form_type").selectpicker()
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableBounces.init();
	$('#delete_confirm_submit').on('click', submitDelete);
	$('#delete_result').on('hidden.bs.modal', function(e) {
		window.location.reload(true);
	});
});
</script>