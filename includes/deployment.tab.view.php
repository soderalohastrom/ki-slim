			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Deployment Name</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo $data['MarketingDeployments_name']?></span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">E-Mail Subject</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo $data['MarketingDeployments_subject']?></span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">From Address</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo $data['MarketingDeployments_fromEmail']?></span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">From Name</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo $data['MarketingDeployments_fromName']?></span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Reply To Address</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo $data['MarketingDeployments_replyTo']?></span>
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Deployment Status</label>
				<div class="col-sm-8 info_label">
					<span class="m-badge m-badge--wide <?php echo $deploy_status_badge?>"><?php echo $deploy_status?></span>
				</div>
			</div>
			<?php if($is_scheduled && $data['MarketingDeployments_dateSched'] > 0) { ?>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Scheduled Date/Time</label>
				<div class="col-sm-8 info_label">
					<?php echo $value_span?><?php echo date('n/j/Y g:ia', $data['MarketingDeployments_dateSched'])?></span>
					<?php if($deploy_status == 'Scheduled') { ?>
						&nbsp;<span id="sched_spinner" style="display:none;">
							<div class="m-loader m-loader--brand"></div>
						</span>
						<button type="button" class="btn btn-sm btn-danger" id="btn_sched_cancel"><i class="la la-close"></i> Cancel</button>
					<?php } ?>
				</div>
			</div>
			<?php }?>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Recipients</label>
				<div class="col-sm-8 info_label">
				<?php foreach($assoc_lists as $list) { ?>
					<?php echo $value_span?><?php echo $list['name']?></span><br />
				<?php } ?>
				</div>
			</div>
		
			<div class="row" style="padding:0 2.2rem;">
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
								<ul class="m-portlet__nav">
									<li class="m-portlet__nav-item">
										<a href="/view-email.php?id=<?php echo $deploy_id?>&pid=0" target="_blank" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="view HTML email in new window">
											<i class="la la-external-link"></i>
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div class="m-portlet__body">
							<?php echo $data['MarketingDeployments_bodyHTML']?>
						</div>
					</div>
				</div>
			</div>
			<div class="row" style="padding:0 2.2rem;">
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
								<ul class="m-portlet__nav">
									<li class="m-portlet__nav-item">
										<a href="/view-email.php?id=<?php echo $deploy_id?>&pid=0&ver=text" target="_blank" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="view plain text email in new window">
											<i class="la la-external-link"></i>
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div class="m-portlet__body">
							<div id="prev_msg_body_plain">
								<?php echo nl2br($data['MarketingDeployments_bodyText'])?>
							</div>
						</div>
					</div>
				</div>
			</div>