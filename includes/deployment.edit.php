<?php 
switch($alert_mode) {
	case 'copied':
	?>
	<div id="form_success" class="alert alert-success alert-dismissible fade show" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<i class="la la-check"></i> A copy of the deployment has been created. You may edit the deployment and included marketing lists below.
	</div>
	<?php
	break;
	case 'cancelled':
	?>
	<div id="form_success" class="alert alert-success alert-dismissible fade show" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<i class="la la-check"></i> The deployment has been cancelled.
		<?php echo $back_btn?>
	</div>
	<?php
	break;
}
if(count($form_errors) > 0): ?>
<div id="form_errors" class="alert alert-danger alert-dismissible fade show" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
	<h4 class="alert-heading">Please correct the following:</h4>
    <?php foreach($form_errors as $err): ?>
    <div><?php echo $err?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php if(strlen($success) > 0): ?>
<div id="form_success" class="alert alert-success alert-dismissible fade show" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
	<i class="la la-check"></i> <?php echo $success?>
	<?php echo $back_btn?>
</div>
<?php endif; ?>
<?php
	$whitelist = explode(',', $SETTINGS->setting['DOMAIN_WHITELIST']);
	if($_POST['MarketingDeployments_fromEmail'] != '' || $data['MarketingDeployments_fromEmail'] != '') {
		if($_POST['MarketingDeployments_fromEmail'] != '') {
			$from_addr = $_POST['MarketingDeployments_fromEmail'].'@'.$_POST['MarketingDeployments_fromEmailDomain'];
		} else {
			$from_addr = $data['MarketingDeployments_fromEmail'];
		}
		$at_pos = strpos($from_addr, '@');
		$fromaddr_left = substr($from_addr, 0, $at_pos);
		$fromaddr_right = substr($from_addr, ($at_pos+1));
	}
?>
<form action="/mkg-deployment/<?php echo $deploy_id?>" method="post" enctype="multipart/form-data" name="deployment_form" id="deployment_form" class="m-form m-form--fit m-form--label-align-right">
	<input type="hidden" id="page_id" value="deployments_detail" />
    <input type="hidden" name="submitted" value="1" />
    <input type="hidden" name="deploy_id" id="deploy_id" value="<?php echo $deploy_id?>" />
    <input type="hidden" name="readonly" id="readonly" value="<?php echo ($readonly) ? 1 : 0 ?>" />
	<div class="nice-tabs">	
	<ul class="nav nav-tabs" role="tablist">
		<li class="nav-item"><a class="nav-link active" data-toggle="tab" role="tab" href="#deploy_basic">Basic Information</a></li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#deploy_html">HTML Version</a></li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#deploy_text">Text Version</a></li>
		<li class="nav-item"><a class="nav-link" data-toggle="tab" role="tab" href="#deploy_actions" id="a_preview">Preview & Actions</a></li>
	</ul>
	<div class="tab-content">
		<div id="deploy_basic" class="tab-pane active">
			<?php include('deployment.tab.basic.php'); ?>
		</div>
		<div id="deploy_html" class="tab-pane">
			<?php include('deployment.tab.html.php'); ?>
		</div>
		<div id="deploy_text" class="tab-pane">
			<?php include('deployment.tab.text.php'); ?>
		</div>
		<div id="deploy_actions" class="tab-pane">
			<?php include('deployment.tab.actions.php'); ?>
		</div>
		<div class="tab-footer m-form__actions m-form__actions--solid">
			<button type="button" class="btn btn-default" style="margin-right:5px;" onclick="document.location='/mkg-deployments'"><i class="la la-arrow-left"></i>&nbsp;Back to Deployments</button>
			<?php if(!$readonly) { ?>
			<button type="button" class="btn btn-success" onclick="submit_deployment('deploy_basic');"><i class="la la-save"></i>&nbsp;Save Deployment</button>
			<?php } ?>
		</div>
	</div>
	</div>
</form>

<!-- DIALOG/OVERLAY -->
<div class="modal fade" id="sched_dialog" role="dialog" aria-labelledby="sched_dialog_label" aria-hidden="true">
	<form class="m-form">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="sched_dialog_label">Schedule Deployment</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger" id="sched_error" style="display:none;">
					<strong>Error:</strong> <span id="sched_error_body"></span>
				</div>
				<div class="form-group m-form__group row">
					<label class="col-lg-3 col-form-label">Scheduled Date</label>
					<div class="col-lg-6">
						<?php $tomorrow = mktime(0, 0, 0, date("n"), (date("j")+1)); ?>
						<input type="text" class="form-control m-input date-picker" id="sched_field_date" value="<?php echo date('m/d/Y', $tomorrow)?>" data-date-start-date="+0d" />
					</div>
				</div>
				<div class="form-group m-form__group row">
					<label class="col-lg-3 col-form-label">Scheduled Time</label>
					<div class="col-lg-6">
						<input type="text" class="form-control m-input" id="sched_field_time" />
					</div>
				</div>
				<div class="m-alert m-alert--outline alert alert-primary">
					The date and time must be entered in the <?php echo $SETTINGS->setting['DEFAULT_TIMEZONE']?> timezone and must occur at least 2 hours in the future.
				</div>
			</div>
			<div class="modal-footer">
			   <span id="sched_spinner" style="display:none;"><div class="m-loader m-loader--brand"></div></span>
			   <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			   <button type="button" class="btn btn-primary" id="btn_sched_go">Schedule</button>
			</div>
		</div>
	</div>
	</form>
</div>
<!-- END DIALOG/OVERLAY -->