<div style="padding:0 2.2rem;">
	<div id="DeploySaveInfo" class="m-alert m-alert--outline alert alert-primary" style="display:<?php echo ( $deploy_id == 0 ? 'block' : 'none' )?>;">
		Please review your deployment's details below. If any revisions are needed, return to the applicable section(s) and make edits. When done, press 'Save Deployment'.
	</div>
	<div id="DeploySaveWarning" class="m-alert m-alert--outline alert alert-primary" style="display:<?php echo ( $deploy_id == 0 ? 'none' : 'block' )?>;">
		<strong>Note:</strong> If you have just made any revisions to this deployment, please save them prior to starting, scheduling or sending a test e-mail.
	</div>
	<div class="row">
		<div class="col-md-9">
			<div class="form-group m-form__group row">
				<label for="prev_MarketingDeployments_name" class="col-sm-3 col-form-label">Deployment Name</label>
				<div class="col-sm-9 info_label" id="prev_MarketingDeployments_name">
					<?php echo $loading_span?>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="prev_MarketingDeployments_subject" class="col-sm-3 col-form-label">Email Subject</label>
				<div class="col-sm-9 info_label" id="prev_MarketingDeployments_subject">
					<?php echo $loading_span?>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="prev_MarketingDeployments_fromEmail" class="col-sm-3 col-form-label">From Address</label>
				<div class="col-sm-9 info_label" id="prev_MarketingDeployments_fromEmail">
					<?php echo $loading_span?>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="prev_MarketingDeployments_fromName" class="col-sm-3 col-form-label">From Name</label>
				<div class="col-sm-9 info_label" id="prev_MarketingDeployments_fromName">
					<?php echo $loading_span?>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="prev_MarketingDeployments_replyTo" class="col-sm-3 col-form-label">Reply To Address</label>
				<div class="col-sm-9 info_label" id="prev_MarketingDeployments_replyTo">
					<?php echo $loading_span?>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="prev_Status" class="col-sm-3 col-form-label">Deployment Status</label>
				<div class="col-sm-9 info_label" id="prev_Status">
					<?php echo $value_span?><?php echo $deploy_status?></span>
				</div>
			</div>
			<?php if($is_scheduled && $data['ScheduledDate'] > 0) { ?>
			<div class="form-group m-form__group row">
				<label for="prev_SchedDate" class="col-sm-3 col-form-label">Deployment Scheduled Date</label>
				<div class="col-sm-9 info_label" id="prev_SchedDate">
					<?php echo $value_span?><?php echo date('n/j/Y g:ia', $data['ScheduledDate'])?></span>
				</div>
			</div>
			<?php }?>
			<div class="form-group m-form__group row" style="padding-bottom:15px;">
				<label for="prev_Recipients" class="col-sm-3 col-form-label">Recipients</label>
				<div class="col-sm-9 info_label" id="prev_Recipients">
					<?php echo $loading_span?>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<button type="button" class="btn btn-block btn-<?php echo ($readonly) ? 'disabled' : 'success';?>" onclick="<?php echo ($readonly) ? '' : 'submit_deployment(\'deploy_actions\');';?>"><i class="la la-save" aria-hidden="true"></i>&nbsp;Save Deployment</button>
			<?php if(!empty($esp) && $esp !== 'local'): ?>
				<?php if($is_scheduled && !empty($data['JobID'])): ?>
				<button type="button" class="btn btn-block btn-danger" onclick="cancel_deployment(<?php echo $deploy_id?>);"><i class="la la-remove" aria-hidden="true"></i>&nbsp;Cancel Deployment</button>
				<?php endif; ?>
				<?php if(!$is_scheduled): ?>
				<button type="button" class="btn btn-block btn-<?php echo ($is_copied || $is_processing || $is_finished || $is_scheduled || $deploy_id == 0) ? 'disabled' : 'info';?>"<?php echo ($is_copied || $is_processing || $is_finished || $is_scheduled || $deploy_id == 0) ? '' : ' data-toggle="modal" data-target="#sched_dialog" data-backdrop="static"'?>><i class="la la-calendar" aria-hidden="true"></i>&nbsp;Schedule Deployment</button>
				<?php endif; ?> 
			<?php endif; ?>
			<button type="button" class="btn btn-block btn-<?php echo ($is_copied || $is_processing || $is_finished || $is_scheduled || $deploy_id == 0) ? 'disabled' : 'primary';?>" onclick="<?php echo ($is_copied || $is_processing || $is_finished || $is_scheduled || $deploy_id == 0) ? '' : 'start_deployment('.$deploy_id.');';?>"><i class="la la-send" aria-hidden="true"></i>&nbsp;Start Deployment</button>
			<button type="button" class="btn btn-block btn-<?php echo ($deploy_id == 0 || $is_copied) ? 'disabled' : 'default';?>" onclick="<?php echo ($deploy_id == 0 || $is_copied) ? '' : 'send_test('.$deploy_id.');';?>"><i class="la la-envelope" aria-hidden="true"></i>&nbsp;Send Test E-Mail</button>
			<button type="button" class="btn btn-block btn-<?php echo ($deploy_id == 0) ? 'disabled' : 'default';?>" onclick="<?php echo ($deploy_id == 0) ? '' : 'copy_deployment('.$deploy_id.');';?>"><i class="la la-copy" aria-hidden="true"></i>&nbsp;Duplicate Deployment</button>
			<button type="button" class="btn btn-block btn-<?php echo ($deploy_id == 0 ||  $is_scheduled || $is_processing) ? 'disabled' : 'default';?>" onclick="<?php echo ($deploy_id == 0 ||  $is_scheduled || $is_processing) ? '' : 'delete_deployment('.$deploy_id.');';?>"><i class="la la-trash" aria-hidden="true"></i>&nbsp;Delete Deployment</button>
			<button type="button" class="btn btn-block btn-default" style="margin-bottom:5px;" onclick="document.location='/mkg-deployments'"><i class="la la-arrow-left"></i>&nbsp;Back to Deployments</button>
		</div>
	</div>
	<div class="row" style="padding-top:2.2rem;">
		<div class="col-12">
			<div class="m-portlet m-portlet--brand m-portlet--head-solid-bg">
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<span class="m-portlet__head-icon">
								<i class="la la-file-code-o"></i>
							</span>
							<h3 class="m-portlet__head-text">
								HTML Message Body
							</h3>
						</div>
					</div>
					<div class="m-portlet__head-tools">
						<?php if($deploy_id != 0) { ?>
						<ul class="m-portlet__nav">
							<li class="m-portlet__nav-item">
								<a href="/view-email.php?id=<?php echo $deploy_id?>&pid=0" target="_blank" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="view saved HTML email in new window">
									<i class="la la-external-link"></i>
								</a>
							</li>
						</ul>
						<?php } ?>
					</div>
				</div>
				<div class="m-portlet__body">
					<div id="prev_msg_body_html">
						<?php echo $loading_span?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
			<div class="m-portlet m-portlet--brand m-portlet--head-solid-bg">
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<span class="m-portlet__head-icon">
								<i class="la la-file-text-o"></i>
							</span>
							<h3 class="m-portlet__head-text">
								Plain Text Message Body
							</h3>
						</div>
					</div>
					<div class="m-portlet__head-tools">
						<?php if($deploy_id != 0) { ?>
						<ul class="m-portlet__nav">
							<li class="m-portlet__nav-item">
								<a href="/view-email.php?id=<?php echo $deploy_id?>&pid=0&ver=text" target="_blank" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="view saved plain text email in new window">
									<i class="la la-external-link"></i>
								</a>
							</li>
						</ul>
						<?php } ?>
					</div>
				</div>
				<div class="m-portlet__body">
					<div id="prev_msg_body_plain">
						<?php echo $loading_span?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>