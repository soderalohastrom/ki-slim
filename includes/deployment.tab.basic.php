			<div class="form-group m-form__group row">
				<label for="TemplateSelect" class="col-sm-4 col-form-label">Load a Template</label>
				<div class="col-sm-8">
					<select class="form-control" id="TemplateSelect" name="TemplateSelect">
						<option value="">Select Template</option>
						<optgroup label="My Templates"></optgroup>
						<?php 
						$t_data = $TEMPLATES->get_templates(0, $_SESSION['system_user_id']);
						if(!array_key_exists('empty_result', $t_data) && !array_key_exists('error', $t_data)) {
							foreach($t_data as $t_row) {
								?><option value="<?php echo $t_row['EmailTemplates_id']?>">
									<?php echo $t_row['EmailTemplates_title']?>
								</option><?php 
							}
						}
						?>
						<optgroup label="Global Templates"></optgroup>
						<?php 
						$t_data = $TEMPLATES->get_templates(0);
						if(!array_key_exists('empty_result', $t_data) && !array_key_exists('error', $t_data)) {
							foreach($t_data as $t_row) {
								?><option value="<?php echo $t_row['EmailTemplates_id']?>">
									<?php echo $t_row['EmailTemplates_title']?>
								</option><?php 
							}
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group m-form__group row<?php echo (array_key_exists('MarketingDeployments_name', $form_errors))?' input_error':'';?>">
				<label for="MarketingDeployments_name" class="col-sm-4 col-form-label">Deployment Name</label>
				<div class="col-sm-8">
					<input type="text" name="MarketingDeployments_name" id="MarketingDeployments_name" class="form-control m-input" value="<?php echo ((isset($_POST['MarketingDeployments_name']))?$_POST['MarketingDeployments_name']:$data['MarketingDeployments_name']);?>" <?php echo ($readonly ? 'readonly="readonly"' : '')?> />
				</div>
			</div>
			<div class="form-group m-form__group row<?php echo (array_key_exists('MarketingDeployments_subject', $form_errors))?' input_error':'';?>">
				<label for="MarketingDeployments_subject" class="col-sm-4 col-form-label">Email Subject</label>
				<div class="col-sm-8">
					<div class="input-group">
						<input type="text" name="MarketingDeployments_subject" id="MarketingDeployments_subject" class="form-control m-input" value="<?php echo ((isset($_POST['MarketingDeployments_subject']))?$_POST['MarketingDeployments_subject']:$data['MarketingDeployments_subject']);?>" <?php echo ($readonly ? 'readonly="readonly"' : '')?> />
						<div class="dropdown">
							<button id="button-emoji" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false" title="Subject Line Emoji">
								Emoji ☺
							</button>
							<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
								<?php 
								$em_sql = "SELECT * FROM MarketingEmojis ORDER BY Emoji_title ASC";
								$em_snd = $DB->get_multi_result($em_sql);
								foreach($em_snd as $em_dta):
								?><a class="dropdown-item" href="javascript:addEmojiToSubject('<?php echo $em_dta['Emoji_code']?>')"><?php echo $em_dta['Emoji_code']?> | <?php echo $em_dta['Emoji_title']?></a><?php 
								endforeach;
								?>                                                    
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group m-form__group row<?php echo (array_key_exists('MarketingDeployments_fromEmail', $form_errors))?' input_error':'';?>">
				<label for="MarketingDeployments_fromEmail" class="col-sm-4 col-form-label">From Address</label>
				<div class="col-sm-8">
					<div class="input-group">
						<input type="text" name="MarketingDeployments_fromEmail" id="MarketingDeployments_fromEmail" class="form-control m-input" value="<?php echo $fromaddr_left?>" <?php echo ($readonly ? 'readonly="readonly"' : '')?> />
						<span class="input-group-addon" id="sizing-addon1">@</span>
						<?php if(is_array($whitelist) && count($whitelist) > 0) { ?>
						<select name="MarketingDeployments_fromEmailDomain" class="form-control m-input" id="MarketingDeployments_fromEmailDomain">
							<?php foreach($whitelist as $domain) { ?>
							<option value="<?php echo $domain?>"<?php echo ( $fromaddr_right == $domain ? ' selected' : '' )?>><?php echo $domain?></option>
							<?php } ?>
						</select>
						<?php }  else {?>
							<input name="MarketingDeployments_fromEmailDomain" type="text" class="form-control m-input" id="MarketingDeployments_fromEmailDomain" value="<?php echo $fromaddr_right?>">
						<?php } ?>
						<input type="hidden" name="MarketingDeployments_fromEmailServer" id="MarketingDeployments_fromEmailServer" value="" />
					</div>
				</div>
			</div>
			<div class="form-group m-form__group row<?php echo (array_key_exists('MarketingDeployments_fromName', $form_errors))?' input_error':'';?>">
				<label for="MarketingDeployments_fromName" class="col-sm-4 col-form-label">From Name</label>
				<div class="col-sm-8">
					<input type="text" name="MarketingDeployments_fromName" id="MarketingDeployments_fromName" class="form-control m-input" value="<?php echo ((isset($_POST['MarketingDeployments_fromName']))?$_POST['MarketingDeployments_fromName']:$data['MarketingDeployments_fromName']);?>" <?php echo ($readonly ? 'readonly="readonly"' : '')?> />
				</div>
			</div>
			<div class="form-group m-form__group row<?php echo (array_key_exists('MarketingDeployments_replyTo', $form_errors))?' input_error':'';?>">
				<label for="MarketingDeployments_replyTo" class="col-sm-4 col-form-label">Reply To Address</label>
				<div class="col-sm-8">
					<input type="text" name="MarketingDeployments_replyTo" id="MarketingDeployments_replyTo" class="form-control m-input" value="<?php echo ((isset($_POST['MarketingDeployments_replyTo']))?$_POST['MarketingDeployments_replyTo']:$data['MarketingDeployments_replyTo']);?>" <?php echo ($readonly ? 'readonly="readonly"' : '')?> />
				</div>
			</div>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Deployment Status</label>
				<div class="col-sm-8 info_label">
					<span class="m-badge m-badge--wide <?php echo $deploy_status_badge?>"><?php echo $deploy_status?></span>
				</div>
			</div>
			<?php if($is_scheduled && $data['MarketingDeployments_dateSched'] > 0): ?>
				<div class="form-group m-form__group row">
					<label for="" class="col-sm-4 col-form-label">Deployment Scheduled Date</label>
					<div class="col-sm-8 info_label">
						<span id="deploy_scheddate_text"><?php echo date('n/j/Y g:ia', $data['MarketingDeployments_dateSched'])?></span>
					</div>
				</div>
			<?php endif; ?>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Estimated Recipient Count</label>
				<div class="col-sm-8 info_label">
					<span id="recip_count_text" class="m-badge m-badge--success"><?php echo $recip_count?></span>
					<span id="count_spinner" style="display:none;">
						<div class="m-loader m-loader--brand"></div>
					</span>
				</div>
			</div>
			 
			<!-- MARKETING LIST ASSOCIATIONS -->
			<?php if(!$readonly): ?>
			<div style="padding:2.2rem 2.2rem">
			<input type="hidden" id="deploy_lists" name="deploy_lists" value="<?php echo implode('|', array_keys($assoc_lists))?>" />
			<div class="row">
			<div class="col-sm-6">
				<div class="m-portlet m-portlet--success m-portlet--head-solid-bg">
					<div class="m-portlet__head">
						<div class="m-portlet__head-caption">
							<div class="m-portlet__head-title">
								<span class="m-portlet__head-icon">
									<i class="la la-list-alt"></i>
								</span>
								<h3 class="m-portlet__head-text">
									Included Marketing Lists
								</h3>
							</div>
						</div>
						<div class="m-portlet__head-tools">
							<ul class="m-portlet__nav">
								<li class="m-portlet__nav-item">
									<a href="javascript:void(clear_lists())" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="remove all">
										<i class="la la-remove"></i>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="m-portlet__body">
						<div class="list-group" id="assoc_lists">
							<?php foreach($assoc_lists as $lid => $info): ?>
								<?php $mkg_list_name = (strlen($info['name']) > 0 ? $info['name'] : 'Marketing List ID: '.$lid); ?>
								<a class="list-group-item assoc_item" data-gid="<?php echo $lid?>" data-name="<?php echo str_replace('"', '&quot;', $mkg_list_name)?>" title="Click to remove this marketing list from the deployment">
									<i class="la la-remove"></i>&nbsp;<?php echo $mkg_list_name?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				</div>
				
				<div class="col-sm-6">
				<div class="m-portlet m-portlet--info m-portlet--head-solid-bg">
					<div class="m-portlet__head">
						<div class="m-portlet__head-caption">
							<div class="m-portlet__head-title">
								<span class="m-portlet__head-icon">
									<i class="la la-list"></i>
								</span>
								<h3 class="m-portlet__head-text">
									Available Marketing Lists
								</h3>
							</div>
						</div>
					</div>
					<div class="m-portlet__body">
						
						<div class="accordion" id="accordionMkgLists">
						
						<?php 
						$mkg_list_ctr = 0;
						while($list = $list_query->fetch_assoc()) {
							$mkg_list_ctr += 1;
							$cur_category = $list['MarketingLists_category'];
							if($cur_category != $prev_category) {
								if($mkg_list_ctr > 1) { ?>
								</ul></div></div></div>
								<?php }?>
								<div class="card">
								<div class="card-header" id="mkgCategoryHeader_<?php echo $cur_category?>">
								  <h5 class="mb-0" data-toggle="collapse" data-target="#mkgCategory_<?php echo $cur_category?>" aria-expanded="false" aria-controls="mkgCategory_<?php echo $cur_category?>" style="color:#000; cursor:pointer; padding:.65 rem 1.25 rem; font-size:1rem; font-weight:400; line-height:1.25;">
									  <?php echo strtoupper($list['MarketingListCategories_name'])?>
								  </h5>
								</div>

								<div id="mkgCategory_<?php echo $cur_category?>" class="collapse" aria-labelledby="mkgCategoryHeader_<?php echo $cur_category?>" data-parent="#accordionMkgLists">
								  <div class="card-body" style="padding:0rem;">
								  <ul class="list-group group_list_category">
							<?php } 
?>							
							<?php $mkg_list_name = (strlen($list['MarketingLists_name']) > 0 ? $list['MarketingLists_name'] : 'Marketing List ID: '.$list['MarketingLists_id']); ?>
							<li class="list-group-item">
							<a href="javascript:;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only group_row" data-lid="<?php echo $list['MarketingLists_id']?>" data-name="<?php echo str_replace('"', '&quot;', $mkg_list_name)?>" title="Click to add this marketing list to the deployment">
								<i class="la la-plus"></i>
							</a>
							&nbsp;<?php echo $mkg_list_name?>
							</li>
						<?php $prev_category = $cur_category;
						} 
						if($mkg_list_ctr > 0) { ?>
							</ul></div></div></div>
						<?php }?>
						</div>
						
						

					</div>
				</div>
				</div>
			</div>
			</div>
			<?php else: ?>
			<div class="form-group m-form__group row">
				<label for="" class="col-sm-4 col-form-label">Included Marketing Lists</label>
				<div class="col-sm-8 info_label">
					<?php foreach($assoc_lists as $lid => $info): ?>
						<?php $mkg_list_name = (strlen($info['name']) > 0 ? $info['name'] : 'Marketing List ID: '.$lid); ?>
						<?php echo $mkg_list_name?><br />
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>