<?php
/*! \class Tasks class.tasks.php "class.forms.php"
 *  \brief This class is used to render the task elements.
 */
class Tasks {
	/*! \fn obj __constructor($DB)
		\brief Tasks class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $RECORD) {
		$this->db 		= 	$DB;
		$this->record	=	$RECORD;
	}
	
	function render_taskModal($PERSON_ID) {
		$psql = "SELECT FirstName, LastName FROM Persons WHERE Person_id='".$PERSON_ID."'";
		$psnd = $this->db->get_single_result($psql);
		
		$asql = "SELECT * FROM PersonActionTypes WHERE 1 ORDER BY ActionOrder";
		$asend = $this->db->get_multi_result($asql);
		ob_start();
		?><option value="">--- select ---</option><?php
		foreach($asend as $adata):
			?><option value="<?php echo $adata['ActionTypeID']?>"><?php echo $adata['ActionType']?></option><?php	
		endforeach;
		$action_select = ob_get_clean();
		
		$user_select = $this->record->options_userSelect(array($_SESSION['system_user_id']));
		?>
<!-- ACTIONS MODAL: actions-modal -->
<div class="modal fade" id="actions-modal" role="dialog" aria-labelledby="linksModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
    	<form class="m-form m-form--fit m-form--label-align-right" id="actions-form" action="javascript:submitAction()">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="linksModalLabel">Add/Edit Task</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

                <div id="action-loading-block">
                    <div style="margin:5% 0px; text-align:center;"><i class="icon-refresh icon-spin"></i> Loading Action Information</div>
                </div>        
                
                <div id="action-form-block">            
                    <input type="hidden" name="action_id" id="action_id" value="0" />                    
                    <div class="form-group m-form__group row">
	                    <label class="col-3 col-form-label">Record/Client</label>
                    	<div class="col-9">
                            <input type="text" name="ActionRecord" id="ActionRecord" class="form-control m-input m-input--solid" value="<?php echo $psnd['FirstName']?> <?php echo $psnd['LastName']?>" readonly/>
                            <input type="hidden" name="ActionRecordPID" id="ActionRecordPID" value="<?php echo $PERSON_ID?>" />
                    	</div>
                    </div>
                                        
                    <div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Task Date/Time</label>
                        <div class="col-sm-5">
                            <input type="text" name="ActionDate" id="ActionDate" class="form-control m-input" value=""/>
                        </div>
                        <div class="col-sm-4">            
                            <input type="text" name="ActionTime" id="ActionTime" class="form-control m-input" value="" readonly placeholder="Select time"/>	
                        </div>
                    </div>
                    
                    <div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Complete Task</label>
                        <div class="col-sm-9">                
                            <select name="ActionConditions" id="ActionConditions" class="form-control m-input" required>
                                <option value="BEFORE">Before Specific Date/Time</option>
                                <option value="ON">On the Specific Date/Time</option>
                            </select>
                        </div>	    
                    </div>
                                
                    <div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Task Type</label>
                        <div class="col-sm-9">
                            <select name="ActionTypeID" id="ActionTypeID" class="form-control input-sm" required>
                            <?php echo $action_select?>
                            </select>
                        </div>            
                    </div>
                    
                    <div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Action</label>
                        <div class="col-sm-9">
                            <textarea name="ActionNote" id="ActionNote" class="form-control input-sm" style="height:100px;" required></textarea>
                        </div>
                    </div> 
                    
                    <div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Priority</label>
                        <div class="col-sm-9">
                            <div class="m-radio-inline">
                            <label class="m-radio m-radio--state-danger">
                                <input type="radio" name="ActionPriority" value="High" id="priorityHigh">
                                High
                                <span></span>
                            </label>
                            <label class="m-radio m-radio--state-success">
                                <input type="radio" name="ActionPriority" value="Normal" id="priorityNormal">
                                Normal
                                <span></span>
                            </label>
                            <label class="m-radio m-radio--state-default">
                                <input type="radio" name="ActionPriority" value="Low" id="priorityLow">
                                Low
                                <span></span>
                            </label>
                        	</div>                            
                        </div>            
                    </div>
                       
                   	<div class="form-group m-form__group row">
                        <label class="col-form-label col-sm-3">Assign To</label>
                        <div class="col-sm-9">
                            <select name="ActionAssignedTo" id="ActionAssignedTo" class="form-control input-sm" required style="width:100%;">
                            <?php echo $user_select?>
                            </select>
                        </div>            
                    </div>                  
                    
                    <div class="m-form__group form-group row">
                        <label class="col-3 col-form-label">&nbsp;</label>
                        <div class="col-4">
                            <div class="m-checkbox-list">
                                <label class="m-checkbox">
                                    <input type="checkbox" name="ActionNotify" id="ActionNotify">
                                    Notify user via email
                                    <span></span>
                                </label>                                
                            </div>
                        </div>
                        <div class="col-5">            
                            <input type="text" name="ActionNotifyCC" id="ActionNotifyCC" class="form-control input-sm" value=""/>
                            <small>comma separated list of emails to be CCed</small>			
                        </div>
                    </div> 
                       
                    <div class="form-group m-form__group row">
                        <label class="col-3 col-form-label">&nbsp;</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label><input type="checkbox" name="ActionCompleted" id="ActionCompleted"></label>
                            </div>
                        </div>
                    </div>
                    
                </div>
    
                <div id="action-display-block">
                	<div class="row">
                    	<div class="col-4">
                        	<dt>Client/Record</dt>
                        	<dd id="action-display-name">{CLIENT_NAME}({ID})</dd>
						</div>
                        <div class="col-5">                                                    
                            <dt>Date</dt>
                            <dd id="action-display-date">on or before {ACTION_DATE}</dd>
						</div>
                        <div class="col-3">                                                    
                            <dt>Priority</dt>
                            <dd id="action-display-priority"></dd>
						</div>
					</div>
                    <div class="row">
                    	<div class="col-4">                                                                            
                            <dt>Assigned To</dt>
                            <dd id="action-display-assigned">{ASSIGNED_USER}</dd>
						</div>
                        <div class="col-8">
                            <dt>Type</dt>
                            <dd id="action-display-type">{ACTION_TYPE}</dd>
                        </div>
					</div>
                    <div class="row">
                    	<div class="col-12">                                                   
                            <dt>Action</dt>
                            <dd id="action-display-notes">{ACTION_NOTES}</dd>
						</div>
					</div> 
                    <div class="row">
                    	<div class="col-6">&nbsp;</div>
                        <div class="col-6">                                                                          
                    		<div class="form-group">
                            	<div class="checkbox">
                                	<label><input type="checkbox" name="MarkActionCompleted" id="MarkActionCompleted"></label>
                            	</div>
                        	</div>
                    	</div>
					</div>
                    <dd id="action-display-created">{ASSIGNED_USER}</dd>
                        
                    <div id="action-note-form" style="display:none;">
                        <textarea name="ActionNoteBody" id="ActionNoteBody" class="form-control input-sm" style="height:125px;"></textarea>
                        <div class="text-right" style="margin:4px 0px;">            	
                            <button type="button" class="btn btn-xs btn-default" onclick="hideNewNote()">Cancel Note</button>
                            <button type="button" class="btn btn-xs btn-primary" id="save-tasknote-button" onclick="saveTaskNote()" data-loading-text="<i class='icon-refresh icon-spin'></i> Saving...">Save Note</button>
                        </div>
                    </div>
                    <div id="action-note-list"></div>
                </div>
                
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger pull-left" id="action-deletenote-button" onclick="removeTask()">Delete Task</button>
                
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="action-save-button" data-loading-text="<i class='icon-refresh icon-spin'></i> Saving...">Save</button>
				
                <button type="button" class="btn btn-default" id="action-addnote-button" onclick="addNewNote()">Add Note</button>
                <button type="button" class="btn btn-primary" id="action-loadform-button" onclick="jumpToActionForm()">Edit Task</button>                
                <button type="button" class="btn btn-warning" id="action-hideform-button" onclick="jumpToActionView()">Cancel Edit</button>
			</div>
		</div>
        </form>
	</div>
</div>
<script type="text/javascript">
var taskCompletedSwitch;
$(document).ready(function(e) {
	$('#ActionDate').datepicker({
		format: 'mm/dd/yyyy',
		todayHighlight: true
	});
	/*
	$('#ActionTime').datetimepicker({
		format: 'LT'
	});
	*/
	$("#ActionTime").timepicker({
		minuteStep: 5
	});
	$('#ActionTime').timepicker('setTime', '<?php echo date("h:i a")?>');
	
	$("#ActionAssignedTo").select2({ theme: "classic" });
	
	$("#ActionCompleted").bootstrapSwitch({
		onText: 'Completed',
		offText: 'Not Completed',
		onColor: 'success',
		labelWidth: 115,
		onSwitchChange: function(event, state) {
			//var payID = $(this).attr('data-id');
			//var state;
			//console.log(payID+'|'+state);
			
			/*
			$.post('./ajax/ajax.sales.php', {
				'action':'mark_received',
				'pay_id':payID,
				'mark':state
			}, function(data_response) {
				//console.log(data_response);
				openSalesInfo(data_response.sale_id, ap_id);
			}, "json");
			*/
		}
	});
	
	$('#MarkActionCompleted').bootstrapSwitch({
		onText: 'Completed',
		offText: 'Not Completed',
		onColor: 'success',
		labelWidth: 115,
		onSwitchChange: function(event, state) {
			var payID = $('#action_id').val();
			var comState = $('#MarkActionCompleted').bootstrapSwitch('state');
			$.post('/ajax/actions.php?action=completed', {
				id:payID,
				state: comState
			}, function(data_response) {
				//console.log(data_response);
				getActionList('live');
				if(comState) {
					toastr.success('Task Marked as Completed', '', {timeOut: 5000});
				} else {
					toastr.error('Task Marked as Open', '', {timeOut: 5000});
				}
			});
		}
	});
	getActionList('live');    
});

function openAction(id) {
	$('#action-loading-block').show();
	
	$('#action-display-block').hide();
	$('#action-form-block').hide();
	
	$('#action-loadform-button').hide();
	$('#action-save-button').hide();
	$('#action-hideform-button').hide()
	$('#action-addnote-button').hide();	
	hideNewNote();
	
	$('#actions-modal').modal('show');
	$('#action_id').val(id);
	if(id == 0) {
		$('#ActionDate').val('<?php echo date("m/d/Y")?>');
		$('#ActionTime').val('<?php echo date("h:i")?> <?php echo date("A")?>');
	
		$('#action-loading-block').hide();
		$('#action-display-block').hide();
		$('#action-form-block').show();
		
		$('#action-save-button').show();
		$('#action-deletenote-button').hide();	
	} else {
		$.post('/ajax/actions.php?action=task', {
			id: id
		}, function(response) {					
			$('#ActionRecord').val(response.personName);
			$('#action-display-name').html(response.personName);			
			$('#ActionRecordPID').val(response.personID);
			
			$('#ActionDate').val(response.date_display);
			$('#ActionTime').val(response.date_time);
			$('#action-display-date').html(response.action_display_date);
			
			$('#ActionTypeID').val(response.ActionTypeID);
			$('#action-display-type').html(response.ActionTypeText);
			
			$('#ActionNote').val(response.ActionNote);
			$('#action-display-notes').html(response.ActionNoteHTML);			
			
			$('#ActionAssignedTo').val(response.AssignedTo);
			$('#action-display-assigned').html(response.AssignedToName);
			
			$('#action-display-priority').html(response.ActionPriority);
			if(response.ActionPriority == 'High') {
				$('#priorityHigh').attr('checked', true);
				$('#priorityNormal').attr('checked', false);
				$('#priorityLow').attr('checked', false);
			} else if(response.ActionPriority == 'Normal') {
				$('#priorityHigh').attr('checked', false);
				$('#priorityNormal').attr('checked', true);
				$('#priorityLow').attr('checked', false);
			} else if(response.ActionPriority == 'Low') {
				$('#priorityHigh').attr('checked', false);
				$('#priorityNormal').attr('checked', false);
				$('#priorityLow').attr('checked', true);	
			}
						
			var tagline = '<small>'+response.TaskCreationBlock;
			if(eval(response.TaskCompleted) == 1) {
				tagline += '<br>'+response.TaskDateCompleted;
			}
			tagline += '</small>';
			$('#action-display-created').html(tagline);
			
			
			$('#action-save-button').hide();
			if(eval(response.TaskCompleted) == 1) {
				$('#MarkActionCompleted').attr('checked', true);
				$('#MarkActionCompleted').bootstrapSwitch('state', true);
				$('#ActionCompleted').attr('checked', true);
				$('#ActionCompleted').bootstrapSwitch('state', true);
			} else {
				$('#MarkActionCompleted').attr('checked', false);
				$('#MarkActionCompleted').bootstrapSwitch('state', false);
				$('#ActionCompleted').attr('checked', false);
				$('#ActionCompleted').bootstrapSwitch('state', false);
			}
			
			if(eval(response.TaskNotify) == 1) {
				$('#ActionNotify').attr('checked', true);				
			} else {
				$('#ActionNotify').attr('checked', false);				
			}
			
			$('#ActionNotifyCC').val(response.TaskNotifyCC);
			$('#action-note-list').html(response.notes);
			
			$('#action-loading-block').hide();
			
			$('#action-display-block').show();
			$('#action-form-block').hide();
	
			$('#action-loadform-button').show();
			$('#action-save-button').hide();
			$('#action-addnote-button').show();
			
			var sessionID = '<?php echo $_SESSION['system_user_id']?>';
			//console.log(sessionID+'|'+response.AssignedTo);
			if(response.CreatedBy == sessionID) {
				$('#action-deletenote-button').show();
			} else {
				$('#action-deletenote-button').hide();
			}
			
		}, "json");	
			
	}
	//$('#ActionTypeID').focus();
	setTimeout(function () { $('#ActionTypeID').focus(); }, 500);
}
function submitAction() {
	var btn = $('#action-save-button');
	var formData = $('#actions-form').serializeArray();
	$('#action-save-button').prop('disabled', true);
	$('#action-save-button').html('Saving...');
	$.post('/ajax/actions.php?action=save', formData, function(response) {
		//getActionList('live');
		$('#actions-modal').modal('hide');
		$('#action-save-button').prop('disabled', false);
		$('#action-save-button').html('Save');
		toastr.success('Task Saved', '', {timeOut: 5000});
		getActionList('live'); 		
	}, "json");
	/*
	setTimeout(function() { 
		$('#action-save-button').prop('disabled', false);
		$('#action-save-button').html('Save'); 
	}, 10000);
	*/
}
function getActionList(filter) {
	$('#action-list-display').html('<div style="margin:5% 0px; text-align:center;"><i class="icon-refresh icon-spin"></i> Loading Tasks....</div>');
	$.post('/ajax/actions.php?action=list', {
		pid: '<?php echo $PERSON_ID?>',
		filter: filter
	}, function(data) {
		$('#action-list-display').html(data);		
	});	
}
function jumpToActionForm() {
	$('#action-display-block').fadeOut('fast', function() {
		$('#action-form-block').fadeIn('fast');
	});
	
	$('#action-loadform-button').hide();
	$('#action-save-button').show();
	$('#action-hideform-button').show();
	$('#action-addnote-button').hide();
}
function jumpToActionView() {
	$('#action-form-block').fadeOut('fast', function() {
		$('#action-display-block').fadeIn('fast');
	});
	
	$('#action-loadform-button').show();
	$('#action-save-button').hide();
	$('#action-hideform-button').hide();
	$('#action-addnote-button').show();
}

function openActionForm(id) {
	$('#action-display-block').hide();
	$('#action-form-block').show();
	$('#actions-modal').modal('show');
	//alert(id);	
	$('#action_id').val(id);
	if(id == 0) {
		$('#ActionDate').val('<?php echo date("m/d/Y")?>');
		$('#ActionTime').val('<?php echo date("h")?>:00 <?php echo date("A")?>');		
	} else {
		$.post('/ajax/actions.php?action=task', {
			id: id
		}, function(response) {
			$('#ActionRecord').val(response.personName);
			$('#action-display-name').html(response.personName);
			
			$('#ActionDate').val(response.date_display);
			$('#action-display-date').html(response.action_display_date);
			
			$('#ActionTypeID').val(response.ActionTypeID);
			console.log(response.ActionTypeText);
			$('#action-display-type').html(response.ActionTypeText);
			
		}, "json");			
	}
	
	$('#action-loading-block').hide();			
	$('#action-display-block').hide();
	$('#action-form-block').show();
	$('#action-loadform-button').hide();
	$('#action-save-button').show();
	$('#action-addnote-button').hide();
	$('#action-hideform-button').hide();
	//$('#ActionTypeID').focus();
	setTimeout(function () { $('#ActionTypeID').focus(); }, 500);
}
function addNewNote() {
	$('#action-note-form').fadeIn('fast', function() {
		$('#ActionNoteBody').focus();		
	});	
}
function hideNewNote() {
	$('#action-note-form').fadeOut('fast');	
	$('#ActionNoteBody').val('');
}
function saveTaskNote() {
	$('#save-tasknote-button').prop('disabled', true);
	$('#save-tasknote-button').html('Saving...');
	
	var id = $('#action_id').val();
	var note = $('#ActionNoteBody').val();
	var user = '<?php echo $_SESSION['system_user_id']?>';
	$.post('/ajax/actions.php?action=addnote', {
		id: id,
		body: note,
		user: user
	}, function(data) {
		//console.log(data);
		//openAction(id);
		$('#save-tasknote-button').prop('disabled', false);
		$('#save-tasknote-button').html('Save');
		toastr.success('Task Note Added', '', {timeOut: 5000});	
		hideNewNote();
		$.post('/ajax/actions.php?action=task', {
			id: id
		}, function(response) {					
			$('#ActionRecord').val(response.personName);
			$('#action-display-name').html(response.personName);
			
			$('#ActionDate').val(response.date_display);
			$('#ActionTime').val(response.date_time);
			$('#action-display-date').html(response.action_display_date);
			
			$('#ActionTypeID').val(response.ActionTypeID);
			$('#action-display-type').html(response.ActionTypeText);
			
			$('#ActionNote').val(response.ActionNote);
			$('#action-display-notes').html(response.ActionNoteHTML);
			
			$('#ActionAssignedTo').val(response.AssignedTo);
			$('#action-display-assigned').html(response.AssignedToName);
			
			var tagline = '<small>'+response.TaskCreationBlock;
			if(eval(response.TaskCompleted) == 1) {
				tagline += '<br>'+response.TaskDateCompleted;
			}
			tagline += '</small>';
			$('#action-display-created').html(tagline);
			
			
			$('#action-save-button').hide();
			if(eval(response.TaskCompleted) == 1) {
				$('#MarkActionCompleted').attr('checked', true);
				$('#MarkActionCompleted').bootstrapSwitch('state', true);
				$('#ActionCompleted').attr('checked', true);
				$('#ActionCompleted').bootstrapSwitch('state', true);
			} else {
				$('#MarkActionCompleted').attr('checked', false);
				$('#MarkActionCompleted').bootstrapSwitch('state', false);
				$('#ActionCompleted').attr('checked', false);
				$('#ActionCompleted').bootstrapSwitch('state', false);
			}
			
			if(eval(response.TaskNotify) == 1) {
				$('#ActionNotify').attr('checked', true);				
			} else {
				$('#ActionNotify').attr('checked', false);				
			}
			
			$('#ActionNotifyCC').val(response.TaskNotifyCC);
			$('#action-note-list').html(response.notes);
			
			$('#action-loading-block').hide();
			
			$('#action-display-block').show();
			$('#action-form-block').hide();
	
			$('#action-loadform-button').show();
			$('#action-save-button').hide();
			$('#action-addnote-button').show();
			
			var sessionID = '<?php echo $_SESSION['system_user_id']?>';
			//console.log(sessionID+'|'+response.AssignedTo);
			if(response.CreatedBy == sessionID) {
				$('#action-deletenote-button').show();
			} else {
				$('#action-deletenote-button').hide();
			}
			
		}, "json");	
		
	});
}
function deleteNote(noteID, id) {
	var choice = confirm('Are you sure you want to remove this task note?');
	if(choice) {
		$.post('/ajax/actions.php?action=removenote', {
			id:noteID
		}, function(data) {
			//openAction(id);
			$.post('/ajax/actions.php?action=task', {
				id: id
			}, function(response) {					
				$('#ActionRecord').val(response.personName);
				$('#action-display-name').html(response.personName);
				
				$('#ActionDate').val(response.date_display);
				$('#ActionTime').val(response.date_time);
				$('#action-display-date').html(response.action_display_date);
				
				$('#ActionTypeID').val(response.ActionTypeID);
				$('#action-display-type').html(response.ActionTypeText);
				
				$('#ActionNote').val(response.ActionNote);
				$('#action-display-notes').html(response.ActionNoteHTML);
				
				$('#ActionAssignedTo').val(response.AssignedTo);
				$('#action-display-assigned').html(response.AssignedToName);
				
				var tagline = '<small>'+response.TaskCreationBlock;
				if(eval(response.TaskCompleted) == 1) {
					tagline += '<br>'+response.TaskDateCompleted;
				}
				tagline += '</small>';
				$('#action-display-created').html(tagline);
				
				
				$('#action-save-button').hide();
				if(eval(response.TaskCompleted) == 1) {
					$('#MarkActionCompleted').attr('checked', true);
					$('#MarkActionCompleted').bootstrapSwitch('state', true);
					$('#ActionCompleted').attr('checked', true);
					$('#ActionCompleted').bootstrapSwitch('state', true);
				} else {
					$('#MarkActionCompleted').attr('checked', false);
					$('#MarkActionCompleted').bootstrapSwitch('state', false);
					$('#ActionCompleted').attr('checked', false);
					$('#ActionCompleted').bootstrapSwitch('state', false);
				}
				
				if(eval(response.TaskNotify) == 1) {
					$('#ActionNotify').attr('checked', true);				
				} else {
					$('#ActionNotify').attr('checked', false);				
				}
				
				$('#ActionNotifyCC').val(response.TaskNotifyCC);
				$('#action-note-list').html(response.notes);
				
				$('#action-loading-block').hide();
				
				$('#action-display-block').show();
				$('#action-form-block').hide();
		
				$('#action-loadform-button').show();
				$('#action-save-button').hide();
				$('#action-addnote-button').show();
				
				var sessionID = '<?php echo $_SESSION['system_user_id']?>';
				//console.log(sessionID+'|'+response.AssignedTo);
				if(response.CreatedBy == sessionID) {
					$('#action-deletenote-button').show();
				} else {
					$('#action-deletenote-button').hide();
				}
				
			}, "json");	
		});
	}
}
function removeTask() {
	var choice = confirm('Are you sure you want to remove this task?');
	if(choice) {		
		var id = $('#action_id').val();
		$.post('/ajax/actions.php?action=removetask', {
			id:id
		}, function(data) {
			//document.location.reload(true);
			$('#actions-modal').modal('hide');
			getActionList('live');	
		});
	}
}
</script>
        <?php	
		
	}
	
	function getActionType($id) {
		$sql = "SELECT * FROM PersonActionTypes WHERE ActionTypeID='".$id."'";
		//$send = mysql_query($sql, $this->db);
		//$data = mysql_fetch_assoc($send);
		$snd = $this->db->get_single_result($sql);		
		return $snd['ActionType'];		
	}
	
	public function getLastNote($id) {
		$sql = "SELECT * FROM PersonActionsNotes WHERE ActionID='".$id."' ORDER BY ActionNoteCreated DESC LIMIT 1";
		//$send = mysql_query($sql, $this->db);
		$send = $this->db->get_single_result($sql);
		//$found = mysql_num_rows($send);
		if (isset($send['empty_result'])):
			return '&nbsp;';
		else:
			return date('m/d/y h:ia', $send['ActionNoteCreated']);
		endif;
	}
	
	
	
	
}
?>