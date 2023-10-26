<?php
$apptString = $PDATA['Person_id']."|".$_SESSION['system_user_id'];
$encApptString = $ENC->encrypt($apptString);
?>
<style type="text/css">
#ap-list-area {
	margin-bottom:30px;
}
#ap-salesperson-feedback, #ap-date-feedback, #ap-time-feedback {
	display:none;
}
</style>
<h5>Current and Previous Appointments</h5>
<div id="ap-list-area">
</div>
<div id="ap-alert-area">
</div>
<button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-target="#apptInfoModal">Set Appointment Link</button>
<h5>Add New Appointment</h5>
<form class="m-form" id="AddAppointmentForm" name="AddAppointmentForm">
	<input type="hidden" id="AddAppointmentForm_action" name="action" value="submit_appointment" />
	<input type="hidden" id="AddAppointmentForm_person_id" name="person_id" value="<?php echo $PERSON_ID?>" />
    <div style="margin-bottom:30px;">
		<div class="form-group m-form__group">
			<label>Appointment For:</label>
			<div class="input-group m-input-group">
				<span class="input-group-addon"><i class="flaticon-user-ok"></i></span>
				<input type="text" class="form-control m-input" value="<?php echo $PDATA['FirstName']?> <?php echo $PDATA['LastName']?>" readonly="readonly">
			</div>
		</div>
		<div class="form-group m-form__group" id="ap-salesperson-group">
			<label>Staff Member:</label>
			<div class="input-group m-input-group">
				<span class="input-group-addon"><i class="flaticon-users"></i></span>
				<select name="ap_salesperson" id="ap_salesperson" class="form-control m-form" style="width:100%;">
					<?php echo $RECORD->options_userSelect($values=array($_SESSION['system_user_id']))?>
				</select>
			</div>
			<div class="form-control-feedback" id="ap-salesperson-feedback">
				You must select a user with a calendar connected to the system.
			</div>  
			<span class="m-form__help">
				select the telemarketer or matchmaker assigned to this appointment
			</span>
		</div>
		<div id="ap-day-sched">
		</div>
		<div class="form-group m-form__group" id="ap-date-group">
			<label>Appointment Date:</label>
			<div class="input-group m-input-group">
				<span class="input-group-addon"><i class="flaticon-time-3"></i></span>
				<input type="text" class="form-control m-input" id="ap_date" name="ap_date">
			</div>   
			<div class="form-control-feedback" id="ap-date-feedback">
				Please select an appointment date.
			</div>				
		</div>
		<div class="form-group m-form__group" id="ap-time-group">
			<label>Appointment Time:</label>
			<div class="input-group m-input-group">
				<span class="input-group-addon"><i class="la la-clock-o"></i></span>
				<input type="text" class="form-control m-input" id="ap_time" name="ap_time">
			</div>
			<div class="form-control-feedback" id="ap-time-feedback">
				Please select an appointment time.
			</div>
			<span class="m-form__help">
				select an appointment time in the staff member's timezone
			</span>			
		</div>
		<!--
		<div class="m-form__group form-group">
			<label for=""></label>
			<div class="m-checkbox-list">
				<label class="m-checkbox">
					<input type="checkbox">Add to my Google Calendar
					<span></span>
				</label>                    
			</div>
		</div>
		-->
    </div>
    <div class="m-portlet__foot m-portlet__foot--fit">
        <div class="m-form__actions m-form__actions">
            <button type="button" class="btn btn-primary" id="btnSubmitAppt">
                Submit
            </button>
            <button type="reset" class="btn btn-secondary">
                Cancel
            </button>
        </div>
    </div>
</form>

<div class="modal fade" id="apptInfoModal" role="dialog" data-backdrop="static" aria-labelledby="apptInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="apptInfoModalLabel"><i class="flaticon-file-1"></i> Appointment Information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">  
            	<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="m-alert__icon">
                        <i class="flaticon-exclamation-2"></i>
                        <span></span>
                    </div>
                    <div class="m-alert__text">
                        <?php 
						$appointmentURL = 'https://'.$_SERVER['SERVER_NAME'].'/setAppointment.php?id='.urlencode($encApptString);
						$appointmentTinyURL = $ENC->get_tiny_url($appointmentURL); 
						?>
                        <strong>This appointment form can be accessed via <a href="<?php echo $appointmentTinyURL?>" target="_blank">the following URL</a>:</strong><br />
                        <textarea class="form-control m-input" id="AptEmbedCode" style="height:85px;"><?php echo $appointmentTinyURL?></textarea>
                    </div>
                </div>
                <div class="row">
                	<div class="col-lg-12">This link is exclusive to scheduling an appointment for this person with this user</div>
				</div>
                <div class="row">
                	<div class="col-lg-6 text-center">
                    	<strong><?php echo $PDATA['FirstName']?> <?php echo $PDATA['LastName']?></strong>
                    </div>
                    <div class="col-lg-6 text-center">
                    	<strong><?php echo $RECORD->get_FulluserName($_SESSION['system_user_id'])?></strong>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-secondary" onclick="copyApptToClipboard()">Copy URL to Clipboard</button>                
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(e) {
	getAppointments();
    $('#ap_salesperson').select2()
	.on('select2:select', function (e) {
		var apDate = $("#ap_date").val();
		if(apDate != '') {
			$.get("/ajax/ajax.nylas.php", { 'action':'get_schedule', 'day':apDate, 'user_id':e.params.data.id }, function(data) {
				console.log(data);
				displayEvents(data);
			}, 'json');
		}
	});
	
	$("#ap_date").datepicker({
		todayHighlight:!0,
		orientation:"bottom left",
		templates:{leftArrow:'<i class="la la-angle-left"></i>',rightArrow:'<i class="la la-angle-right"></i>'}
	})
	.on('changeDate', function(e) {
		//alert(e.format('mm/dd/yyyy'));
		$.get("/ajax/ajax.nylas.php", { 'action':'get_schedule', 'day':e.format('mm/dd/yyyy'), 'user_id':$('#ap_salesperson').val() }, function(data) {
			console.log(data);
			displayEvents(data);
		}, 'json');
	});
	
	$("#ap_time").timepicker();
	
	$('#btnSubmitAppt').on('click', function() {
		$('#ap-alert-area').html('');
		$('#ap-salesperson-group').removeClass('has-danger');
		$('#ap-date-group').removeClass('has-danger');
		$('#ap-time-group').removeClass('has-danger');
		$('#ap-salesperson-feedback').hide();
		$('#ap-date-feedback').hide();
		$('#ap-time-feedback').hide();
		var apDate = $("#ap_date").val();
		var apTime = $("#ap_time").val();
		var apDaySched = $('#ap-day-sched').html();
		var errors = false;
		
		if(apDaySched == '<div class="alert alert-danger">This Staff Member does not have a calendar connected to the system.</div>') {
			$('#ap-salesperson-group').addClass('has-danger');
			$('#ap-salesperson-feedback').show();
			errors = true;
		} else if(apDaySched.search('<div class="alert alert-danger">') != -1) {
			errors = true;
		}
		if(apDate == '') {
			$('#ap-date-group').addClass('has-danger');
			$('#ap-date-feedback').show();
			errors = true;
		}
		if(apTime == '') {
			$('#ap-time-group').addClass('has-danger');
			$('#ap-time-feedback').show();
			errors = true;
		}
		
		if(!errors) {
			$.get("/ajax/ajax.nylas.php", $('#AddAppointmentForm').serializeArray(), function(data) {
				if(data.success == 1) {
					$('#ap-alert-area').html('<div id="ap_add_success" class="alert alert-success" role="alert"><i class="la la-check"></i> '+data.message+'</div>');
					$('#ap_add_success').fadeIn().delay(3000).fadeOut();
					$('#ap-day-sched').html('');
					document.AddAppointmentForm.reset();
					getAppointments();
				} else {
					$('#ap-alert-area').html('<div class="alert alert-danger fade show" role="alert"><i class="la la-exclamation"></i> '+data.message+'</div>');
				}
			}, 'json');
		}
	});
		
	/*
	$("#ap_date").datetimepicker({
		todayHighlight: !0,
		autoclose: !0,
		pickerPosition: "bottom-left",
		format: "yyyy/mm/dd HH:iiP",
		showMeridian: true
	});
	*/
});
function copyApptToClipboard() {
	$("#AptEmbedCode").select();
    document.execCommand('copy');
}
function getAppointments() {
	$.get("/ajax/ajax.nylas.php", { 'action':'get_appointments', 'person_id':'<?php echo $PERSON_ID?>' }, function(data) {
		if(data.success == 1) {
			$('#ap-list-area').html(data.html);
		}
	}, 'json');
}

function cancelAppointment(apId) {
	if(confirm('Are you sure you want to cancel this appointment?')) {
		$.get("/ajax/ajax.nylas.php", { 'action':'cancel_appointment', 'ap_id':apId }, function(data) {
			if(data.success == 1) {
				console.log(data.debug);
				$('#ap_row_'+apId).remove();
				$('#ap-alert-area').html('<div id="ap_add_success" class="alert alert-success" role="alert"><i class="la la-check"></i> Appointment has been cancelled.</div>');
				$('#ap_add_success').fadeIn().delay(3000).fadeOut();
			} else if(data.success == 2) {
				$('#ap_row_'+apId).remove();
				$('#ap-alert-area').html('<div class="alert alert-warning fade show" role="alert"><i class="la la-exclamation"></i> '+data.message+'</div>');
			} else {
				$('#ap-alert-area').html('<div class="alert alert-danger fade show" role="alert"><i class="la la-exclamation"></i> '+data.message+'</div>');
			}
		}, 'json');
	}
}

function displayEvents(data) {
	if(data.success) {
		if(data.events.length == 0) {
			$('#ap-day-sched').html('<div class="alert alert-info">This Staff Member has no events scheduled on the date selected.</div>');
		} else {
			var eventsHtml = '<div class="alert alert-info">Schedule on '+$("#ap_date").val()+'</div><table class="table"><thead><tr><th>Event</th><th>Starts</th><th>Ends</th></tr></thead><tbody>';
			//$('#ap-day-sched').html('<div class="alert alert-info">'+data.events+'</div>');
			for(var i = 0; i < data.events.length; i++) {
				eventsHtml += '<tr><td>'+data.events[i].title+'</td><td>'+data.events[i].start_time+'</td><td>'+data.events[i].end_time+'</td></tr>';
			}
			eventsHtml += '</tbody></table>';
			$('#ap-day-sched').html(eventsHtml);
		}
	} else {
		$('#ap-day-sched').html('<div class="alert alert-danger">'+data.message+'</div>');
	}
}
</script>