<script type="text/javascript">
$(document).ready(function() {
	<?php if($data['MarketingDeployments_status'] != 'Pending' && $data['MarketingDeployments_status'] != 'Scheduled') {  ?>
		if($("#recipients_tbl").has("td").length > 0) {
			$("#recipients_tbl").mDatatable({data:{saveState:{cookie:false,webstorage:false}},search:{input:$("#search_recipients")},columns:[{field:"Date",type:"text",width:200},{field:"E-Mail Address",type:"text",width:500}]});
		}
		if($("#opens_tbl").has("td").length > 0) {
			$("#opens_tbl").mDatatable({data:{saveState:{cookie:false,webstorage:false}},search:{input:$("#search_opens")},columns:[{field:"Date",type:"text",width:200},{field:"E-Mail Address",type:"text",width:500}]});
		}
		if($("#opens_unique_tbl").has("td").length > 0) {
			$("#opens_unique_tbl").mDatatable({data:{saveState:{cookie:false,webstorage:false}},search:{input:$("#search_opens_unique")},columns:[{field:"E-Mail Address",type:"text",width:400},{field:"Date First Opened",type:"text",width:200},{field:"Date Last Opened",type:"text",width:200}]});
		}
		if($("#clicks_tbl").has("td").length > 0) {
			$("#clicks_tbl").mDatatable({data:{saveState:{cookie:false,webstorage:false}},search:{input:$("#search_clicks")},columns:[{field:"Date",type:"text",width:200},{field:"E-Mail Address",type:"text",width:300},{field:"Link Clicked",type:"text",width:600}]});
		}
		if($("#bounces_tbl").has("td").length > 0) {
			$("#bounces_tbl").mDatatable({data:{saveState:{cookie:false,webstorage:false}},search:{input:$("#search_bounces")},columns:[{field:"Date",type:"text",width:200},{field:"E-Mail Address",type:"text",width:500}]});
		}
		$('#a_statistics').on('click', loadDeploymentCharts_Delay);
	<?php } elseif($data['MarketingDeployments_status'] == 'Scheduled') { ?>
		$('#btn_sched_cancel').on('click', function() {
			cancel_deployment('<?php echo $deploy_id?>');
		});
	<?php } ?>
});
</script>
<?php
switch($alert_mode) {
	case 'started':
	case 'scheduled':
	case 'cancelled':
	?>
	<div id="form_success" class="alert alert-success alert-dismissible fade show" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<i class="la la-check"></i> The deployment has been <?php echo $alert_mode?>.
		<?php echo $back_btn?>
	</div>
	<?php
	break;
	default:
	break;
}
?>
<form class="m-form m-form--fit m-form--label-align-right">
<input type="hidden" name="deploy_id" id="deploy_id" value="<?php echo $deploy_id?>" />
<div class="nice-tabs">
	<ul id="tabs_ul" class="nav nav-tabs" role="tablist">
		<li class="nav-item"><a class="nav-link active" data-toggle="tab" role="tab" href="#deploy_view">Deployment</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#deploy_stats" id="a_statistics">Charts</a></li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#deploy_reports">Reports</a></li>
	</ul>
	<div class="tab-content">
		<div id="deploy_view" class="tab-pane active">
			<?php include('deployment.tab.view.php'); ?>
		</div>
		<div id="deploy_stats" class="tab-pane">
			<?php include('deployment.tab.stats.php'); ?>
		</div>
		<div id="deploy_reports" class="tab-pane">
			<?php include('deployment.tab.reports.php'); ?>
		</div>
		<div class="tab-footer m-form__actions m-form__actions--solid">
			<button type="button" class="btn btn-default" style="margin-right:5px;" onclick="document.location='/mkg-deployments'"><i class="la la-arrow-left"></i>&nbsp;Back to Deployments</button>
		</div>
	</div>
</div>
</form>