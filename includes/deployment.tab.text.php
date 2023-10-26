<div class="row">
	<div class="col-lg-6">
	<div class="form-group m-form__group row">
    	<label for="MergeFieldText" class="col-sm-4 col-form-label">Merge Fields:</label>
        <div class="col-sm-8">
        <select name="MergeFieldText" id="MergeFieldText" class="form-control m-input" onchange="javascript:setMergeField('Text');">
        	<option value="">-- select --</option>
			<?php foreach($SETTINGS->setting['MERGE_FIELDS'] as $merge_field_id=>$merge_field) {?>
            <option value="<?php echo $merge_field_id?>"><?php echo $merge_field['display']?></option>
			<?php } ?>
        </select>
        </div> 
	</div>
	</div>
	<div class="col-lg-6"></div>
</div>
<div class="form-group m-form__group row">
	<div class="col-12">
		<textarea id="msg_body_plain" name="msg_body_plain" class="form-control m-input" rows="25"><?php echo ((isset($_POST['msg_body_plain']))?$_POST['msg_body_plain']:$data['MarketingDeployments_bodyText'])?></textarea>
	</div>
</div>