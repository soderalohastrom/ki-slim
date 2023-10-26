<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("class.marketing.php");
$marketing = new Marketing();
$moo_sql = "
		SELECT *
		FROM MarketingOptOuts
		ORDER BY Optouts_date DESC
		";
$moo_snd = $DB->get_multi_result($moo_sql);
if(!array_key_exists('error', $moo_snd) && !array_key_exists('empty_result', $moo_snd)) {
	$moo_data = array();
	foreach($moo_snd as $moo_row) {
		$new_row = array();
		foreach($moo_row as $row_col=>$row_val) {
			$new_row[$row_col] = str_replace("\n", " ", $row_val);
			if($row_col == 'Optouts_date') {
				$new_row['Optouts_dateFormatted'] = date('n/d/Y', $row_val);
			}
		}
		$moo_data[] = $new_row;
	}
	$moo_data_json = json_encode($moo_data);
} else {
	$moo_data_json = false;
}
?>
<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<div class="m-content">

<div class="row">
<div class="col-md-6">
<div class="m-portlet m-portlet--mobile">	
	<div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
					<i class="la la-ban"></i>
                    Opt-Outs
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
        </div>
    </div>
	<div class="m-portlet__body">
		<?php if($moo_data_json !== false) { ?>
		<!--begin: Search Form -->
		<div class="m-form m-form--label-align-right m--margin-top-10 m--margin-bottom-30">
			<div class="form-group m-form__group row align-items-center">
				<div class="col-12">
					<div class="m-input-icon m-input-icon--left">
						<input type="text" class="form-control m-input" placeholder="Search..." id="m_form_listname">
						<span class="m-input-icon__icon m-input-icon__icon--left">
							<span>
								<i class="la la-search"></i>
							</span>
						</span>
					</div>
				</div>
			</div>
		</div>
		<!--end: Search Form -->
		<!--begin: Datatable -->
		<div class="m_datatable" id="optouts_tbl"></div>
		<!--end: Datatable -->
		<?php } else { ?>
			<div class="alert alert-info">No opt-outs found.</div>
		<?php } ?>
	</div>
</div>
</div>

<div class="col-md-6">
<div class="m-form">
<div class="m-portlet m-portlet--mobile">
	<div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
					<i class="la la-plus-circle"></i>
                    Add Opt-Out
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
        </div>
    </div>
	<div class="m-portlet__body">
		<div class="form-group m-form__group">
			<label for="optout_email">
				Email Address
			</label>
			<input type="email" class="form-control m-input" id="optout_email" name="optout_email" placeholder="Enter email">
		</div>
	</div>
	<div class="m-portlet__foot m-portlet__foot--fit">
		<div class="m-form__actions">
			<button type="button" class="btn btn-primary" id="btn_optout_submit">
				Submit
			</button>
			<button type="reset" class="btn btn-secondary" id="btn_optout_reset">
				Cancel
			</button>
		</div>
	</div>
</div>
</div>
</div>
</div>

</div>

<!--begin::Optout Result Modal-->
<div class="modal fade" id="delete_result" tabindex="-1" role="dialog" aria-labelledby="delete_result_title" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="delete_result_title">
					Add Opt-Out
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
<!--end::Optout Result Modal-->

<script>
function submitOptout() {
	var optoutEmail = $('#optout_email').val();
	$('#delete_progress').html('Adding opt-out...');
	$('#delete_result').modal('show');
	$.post('ajax/ajax.deployments.php', { 'action': 'add_optout', 'optout_email': optoutEmail },
		function(data){
			if(data.success) {
				$('#delete_progress').html('<div class="alert alert-success" role="alert">'+data.msg+'</div>');
				window.location.reload(true);
			} else {
				$('#delete_progress').html('<div class="alert alert-danger" role="alert">'+data.msg+'</div>');
			}
		}, 'json');
}

jQuery(document).ready(function() {
	<?php if(strlen($moo_data_json) > 0) { ?>
    	var e = JSON.parse('<?php echo str_replace("'", "\'", $moo_data_json)?>');
		$("#optouts_tbl").mDatatable({
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
                   field: "Optouts_dateFormatted",
                    title: "Date",
					type: "date",
                    format: "MM/DD/YYYY",
					width: 70
                }, {
                    field: "Optouts_email",
                    title: "Email Address",
					overflow: 'visible'
				 }]
            });
			//var i = a.getDataSourceQuery();
	<?php } ?>
	$('#btn_optout_submit').on('click', submitOptout);
	$('#btn_optout_reset').on('click', function() { $('#optout_email').val(''); });
	/*$('#delete_result').on('hidden.bs.modal', function(e) {
		window.location.reload(true);
	});*/
});
</script>