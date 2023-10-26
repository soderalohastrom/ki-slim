<form id="notes-filter-form" action="javascript:filterNoteHistory();">
<input type="hidden" name="p_name" value="<?php echo str_replace('"', '\"', ($PDATA['FirstName'].' '.$PDATA['LastName']))?>" />
<div class="row">
	<div class="col-12">
	    <div class="pull-right" id="found-notes"></div>
        <div class="m-checkbox-inline">
            <label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterTasks" value="TASK" checked="checked">
                Tasks
                <span></span>
            </label>
            <label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterActions" value="Lead Action" checked="checked">
                Actions
                <span></span>
            </label>
            <label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterNotes" value="Client Note" checked="checked">
                Notes
                <span></span>
            </label>
            <label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterCalls" value="Call Note" checked="checked">
                Calls
                <span></span>
            </label>
            <label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterEmails" value="EMAIL" checked="checked">
                Emails
                <span></span>
            </label>
			<label class="m-checkbox">
                <input type="checkbox" name="filterType[]" id="filterSMS" value="SMS" checked="checked">
                SMS
                <span></span>
            </label>
        </div>
    </div>
</div>    
<div class="row">
	<div class="col-12">
    	<div class="input-group m-input-group">
            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-calendar"></i></span>
            <input type="text" class="form-control m-input input-sm" id="filterDates" name="filterDates" placeholder="dates" autocomplete="off">
		</div>
        <div class="input-group m-input-group">            
            <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control m-input input-sm" id="filterString" name="filterString" placeholder="search" autocomplete="off">
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit">
                    Filter <i class="fa fa-filter"></i>
                </button>
            </span>
            <span class="input-group-btn">
                <button class="btn btn-secondary" onclick="clearNoteFilters()">
                    Clear <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
        <span class="m-form__help">
        	<div class="row" style="margin-top:4px;">
            	<div class="col-6">Leave filter field blank to skip</div>
                <div class="col-6 text-right">
                	<label class="m-checkbox">
                        <input type="checkbox" name="fullView" id="fullView" value="1">
                        Show Full View
                        <span></span>
                    </label>
                </div>
			</div>                
        </span>
    </div>
</div>
<input type="hidden" name="pid" value="<?php echo $PERSON_ID?>" />
<input type="hidden" name="offset" value="0" />
</form>
<div>&nbsp;</div>
<ul id="notes-pagination" class="pagination pagination-sm"></ul>
<div class="m-list-timeline">
    <div class="m-list-timeline__items" id="note-items-list">
    </div>
</div>

<script>
$(document).ready(function(e) {
	$(document).on('click', '#fullView', function() {
		filterNoteHistory();	
	});    
});
function openDataHistory() {
	$('#historyModal .modal-title').html('Record Data History');	
	$('#historyModal .modal-body').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Data History...');
	$('#historyModal').modal('show');
	$.post('/ajax/notes.php?action=datahistory', {
		pid: '<?php echo $PERSON_ID?>'
	}, function(data) {
		$('#historyModal .modal-body').html(data);	
		mApp.init();	
	});
}
function openFormHistory() {
	$('#historyModal .modal-title').html('Record Form History');
	$('#historyModal .modal-body').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Data History...');
	$('#historyModal').modal('show');
	$.post('/ajax/notes.php?action=formhistory', {
		pid: '<?php echo $PERSON_ID?>'
	}, function(data) {		
		$('#historyModal .modal-body').html(data);	
		mApp.init();	
	});
}
</script>

