<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.encryption.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption();

//print_r($_GET);
$ApptData = $ENC->decrypt($_GET['id']);
//echo "DATA:".$ApptData;
$ApptDataParts = explode("|", $ApptData);

$UID = 1;

for($mn=1; $mn<120; $mn++) {
	$preDate = mktime(0,0,0,date("m"), (date("d") - $mn), date("Y"));
	$invalidDates[] = date("m/d/Y", $preDate);
}

function generate_timezone_list()
{
    static $regions = array(
        DateTimeZone::AFRICA,
        DateTimeZone::AMERICA,
        DateTimeZone::ANTARCTICA,
        DateTimeZone::ASIA,
        DateTimeZone::ATLANTIC,
        DateTimeZone::AUSTRALIA,
        DateTimeZone::EUROPE,
        DateTimeZone::INDIAN,
        DateTimeZone::PACIFIC,
    );

    $timezones = array();
    foreach( $regions as $region )
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets = array();
    foreach( $timezones as $timezone )
    {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    // sort timezone by offset
    asort($timezone_offsets);

    $timezone_list = array();
    foreach( $timezone_offsets as $timezone => $offset )
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

        $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
    }

    return $timezone_list;
}

$defaultTimeZone = 'America/Los_Angeles';
$tzlist = generate_timezone_list();
//print_r($tzlist);
$tzKeys = array_keys($tzlist);
ob_start();
foreach($tzKeys as $key):
	?><option value="<?php echo $key?>" <?php echo (($defaultTimeZone == $key)? 'selected':'')?>><?php echo $tzlist[$key]?></option><?php
endforeach;
$tzSelect = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="utf-8" />
    <title>Kelleher International Matchmaking Set Appointment</title>
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!--begin::Base Scripts -->
    <script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="/assets/demo/default/base/scripts.bundle.full.js" type="text/javascript"></script>
    <!--end::Base Scripts -->   
    <!--begin::Page Vendors -->
    <script src="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript"></script>
    <!--<script src="/assets/vendors/custom/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
    <script type="text/javascript" src="/assets/vendors/custom/twbs-pagination/jquery.twbsPagination.min.js"></script>
    <script type="text/javascript" src="/assets/vendors/custom/moment-timezone/builds/moment-timezone-with-data.min.js"></script>
    <!-- <script type="text/javascript" src="https://momentjs.com/downloads/moment-timezone-with-data.js"></script> -->
    <!--end::Page Vendors -->  
    <!--begin::Page Snippets -->
    
    <!--end::Page Snippets -->   
    <!-- begin::Page Loader -->
    
    <!--begin::Web font -->
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>    
    
    <script>
      WebFont.load({
        google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
            sessionStorage.fonts = true;
        }
      });
    </script>
    <!--end::Web font -->
    <!--begin::Base Styles -->  
    <!--begin::Page Vendors -->
    <link href="/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Page Vendors -->
    <link href="/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="/assets/app/css/kelleher.css" rel="stylesheet" type="text/css" />
    <!--end::Base Styles -->
    <link rel="shortcut icon" href="/favicon.ico" />
    <style>
	.disabled-date {
		opacity:0.4;	
	}
	</style>
		    
</head>
<body>
<div class="container">
	<div class="text-center" style="margin-bottom:20px;">
    	<img src="/assets/app/media/img/logos/kelleher-contract-logo.jpg" class="img-fluid">
	</div>
    
    <div class="m-portlet">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <span class="m-portlet__head-icon m--hide">
                        <i class="la la-gear"></i>
                    </span>
                    <h3 class="m-portlet__head-text">
                        Schedule Appointment
                    </h3>
                </div>
            </div>
        </div>
        <!--begin::Form-->
        <form class="m-form" id="apptForm">
        	<input type="hidden" name="client_id" id="client_id" value="<?php echo $ApptDataParts[0]?>">
            <input type="hidden" name="user_id" id="user_id" value="<?php echo $ApptDataParts[1]?>">
            <div class="m-portlet__body">
            	
                <div id="step_1">
                    <div class="m-stack m-stack--ver m-stack--general m-stack--demo" style="margin-bottom:20px;">
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 1:<br>
                            Select Timezone
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-close pull-right" style="font-size:3em;"></i>
                            Step 2:<br>
                            Select Date &amp; Time
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-close pull-right" style="font-size:3em;"></i>
                            Step 3:<br>
                            Confirm Appointment
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group m-form__group">
                                <label for="apptTimezone">
                                    Select Timezone:
                                </label>
                                <select name="apptTimezone" id="apptTimezone" class="form-control m-input">
                                    <?php echo $tzSelect?>
                                </select>
                                <span class="m-form__help">
                                    Select your timezone
                                </span>
                            </div>
                        </div>
                    </div>
				</div>
                
                <div id="step_2" style="display:none;">
                	<div class="m-stack m-stack--ver m-stack--general m-stack--demo" style="margin-bottom:20px;">
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 1:<br>
                            Select Timezone
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 2:<br>
                            Select Date &amp; Time
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-close pull-right" style="font-size:3em;"></i>
                            Step 3:<br>
                            Confirm Appointment
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
							<label for="apptTimezone">
                                    Select Date:
                            </label>
                            <div class id="m_datepicker_6"></div>
                            <input type="hidden" name="apptDate" id="apptDate" value="" />
                        </div>
                        <div class="col-lg-6">
                        	<div class="form-group m-form__group">
                                <label for="apptTime">
                                    Select Appointment Time:
                                </label>
                                <select name="apptTime" id="apptTime" class="form-control m-input">
                                	<option value=""></option>
                                </select>
                                <span class="m-form__help" id="apTime_loader">
                                    Select time of your appointment from available timeslots.
                                </span>
                            </div>
                            
                            <div class="form-group m-form__group">
                                <label for="apptComments">
                                    Comments/Notes:
                                </label>
                                <textarea name="apptComments" id="apptComments" class="form-control m-input" style="height:150px;"></textarea>
                                <span class="m-form__help">
                                    Enter any comments or quesitons here.
                                </span>
                            </div>
                        
                        </div>
                    </div>
                </div>
                
                <div id="step_3" style="display:none;">
                	<div class="m-stack m-stack--ver m-stack--general m-stack--demo" style="margin-bottom:20px;">
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 1:<br>
                            Select Timezone
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 2:<br>
                            Select Date &amp; Time
                        </div>
                        <div class="m-stack__item m-stack__item--left">
                            <i class="fa fa-check pull-right m--font-success" style="font-size:3em;"></i>
                            Step 3:<br>
                            Confirm Appointment
                        </div>
                    </div>                    
					<div class="row">
                    	<div class="col-lg-6">
                            <dl class="row">
                                <dt class="col-sm-4">Timezone:</dt>
                                <dd class="col-sm-8" id="display_timeZone"></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-4">Date/Time:</dt>
                                <dd class="col-sm-8" id="display_dateTime"></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-4">Notes:</dt>
                                <dd class="col-sm-8" id="display_dateNote"></dd>
                            </dl>
						</div>
                        <div class="col-lg-6">
                            <dl class="row">
                                <dt class="col-sm-4">Appointment With:</dt>
                                <dd class="col-sm-8" id="display_apWith"></dd>
                            </dl>
                            <dl class="row">
                                <dt class="col-sm-4">Appointment Type:</dt>
                                <dd class="col-sm-8" id="display_apType"></dd>
                            </dl>
						</div>                                                      
					</div>
				</div>
                
                <div id="step_4" style="display:none;">
                	<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
                        <div class="m-alert__icon">
                            <i class="flaticon-event-calendar-symbol"></i>
                            <span></span>
                        </div>
                        <div class="m-alert__text">
                            <strong>
                                Well done!
                            </strong>
                            Your appointment has been scheduled. You should recieve a confirmation in your email.
                        </div>
                    </div>                
                </div>                    
                               
            </div>
            <div class="m-portlet__foot m-portlet__foot--fit">
                <div class="m-form__actions m-form__actions--right">
                    <div class="row">
                        <div class="col m--align-left">
                            <button type="button" class="btn btn-brand" id="button_backStep1" onclick="backto_Step1()" style="display:none;"><i class="fa fa-chevron-left"></i> Back</button>
                            <button type="button" class="btn btn-brand" id="button_backStep2" onclick="backto_Step2()" style="display:none;"><i class="fa fa-chevron-left"></i> Back</button>
                        </div>
                        <div class="col m--align-right">
                            <button type="button" class="btn btn-brand" id="button_toStep2" onclick="goto_Step2()">Next <i class="fa fa-chevron-right"></i></button>
                            <button type="button" class="btn btn-brand" id="button_toStep3" onclick="goto_Step3()" style="display:none;">Next <i class="fa fa-chevron-right"></i></button>
                            <button type="button" class="btn btn-success" id="button_toStep4" onclick="goto_Step4()" style="display:none;">Confirm Appointment Info</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--end::Form-->
    </div>        
	



</div>
</body>
<script>
Date.prototype.getUnixTime = function() { return this.getTime()/1000|0 };
if(!Date.now) Date.now = function() { return new Date(); }
Date.time = function() { return Date.now().getUnixTime(); }

$(document).ready(function(e) {
    $("#m_datepicker_6").datepicker({
		//todayHighlight: !0,
		daysOfWeekDisabled: [0,6],
		datesDisabled: <?php echo json_encode($invalidDates)?>,
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	});
	$('#m_datepicker_6').datepicker().on('changeDate', function(e) {
        // `e` here contains the extra attributes
		//console.log(e);
		var dateObject = e.date;
		var dateString = getFormattedDate(dateObject);
		$('#apptDate').val(dateString);
		$('#apTime_loader').html('<div class="m-loader m-loader--primary" style="width: 30px; display: inline-block;"></div> Loading Available Times...');
		$.get('/ajax/ajax.nylas.php', {
			action		: 	'get_schedule',
			user_id		: 	'<?php echo $UID?>',
			day			:	dateString,
			external	:	1
		}, function(data) {
			//console.log(data);
			//console.log(dateObject);
			//$('#apptTime').html(drawTimeSelect(dateObject, data.events));
			$.post('/ajax/appointments.php?action=apt_time_select', {
				tz		:	$('#apptTimezone').val(),
				user	:	'<?php echo $UID?>',
				date	:	dateString,
				events	:	data.events
			}, function(data2) {
				console.log(data2);
				$('#apptTime').html(data2);
				$('#apTime_loader').html('Select time of your appointment from available timeslots.');
			});
		}, "json");
		
    });
	
	var myTimezone = moment.tz.guess();
	//alert(myTimezone);
	$('#apptTimezone').val(myTimezone);
});
function setAptPreviewTime(epoch) {
	$.post('/ajax/appointments.php?action=apt_time_preview', {
		tz		:	$('#apptTimezone').val(),
		epoch	: 	epoch,
		uid		:	$('#user_id').val(),
		cid		:	$('#client_id').val()
	}, function(data) {
		$('#display_dateTime').html(data.dateTime);
		$('#display_apWith').html(data.apptWith);
		$('#display_apType').html(data.apptType);		
	}, "json");
}
function goto_Step4() {
	var formData = $('#apptForm').serializeArray();
	$.post('/ajax/appointments.php?action=write_appointment', formData, function(data) {
		console.log(data);
		if(data.success) {
			$('#step_4').show();
			$('#step_3').hide();
			$('#button_toStep4').hide();
			$('#button_backStep2').hide();	
		} else {
			alert(data.message);
		}
	}, "json");
}
function goto_Step3() {
	if($('#apptTime').val() == '') {
		alert('You must select an available time');
	} else {
		$('#step_2').hide();
		$('#step_3').show();
		
		$('#display_timeZone').html($('#apptTimezone').val());
		$('#display_dateNote').html($('#apptComments').val());
		setAptPreviewTime($('#apptTime').val());
	
		$('#button_toStep3').hide();
		$('#button_toStep4').show();
		$('#button_backStep1').hide();
		$('#button_backStep2').show();
	}
}
function goto_Step2() {
	$('#step_1').hide();
	$('#step_2').show();
	$('#button_toStep2').hide();
	$('#button_backStep1').show();
	$('#button_toStep3').show();
}
function backto_Step2() {
	$('#step_2').show();
	$('#step_3').hide();
	
	$('#button_toStep4').hide();
	$('#button_backStep1').show();
	
	$('#button_backStep2').hide();
	$('#button_toStep3').show();
}
function backto_Step1() {
	$('#step_1').show();
	$('#step_2').hide();
	
	$('#button_toStep2').show();
	$('#button_toStep3').hide();
	
	$('#button_backStep1').hide();	
}
 

function getFormattedDate(date) {
  var year = date.getFullYear();

  var month = (1 + date.getMonth()).toString();
  month = month.length > 1 ? month : '0' + month;

  var day = date.getDate().toString();
  day = day.length > 1 ? day : '0' + day;
  
  return month + '/' + day + '/' + year;
}
function drawTimeSelect(dateObject, eventObject) {
	console.log(eventObject);
	var optionTime;
	var selectHTML = '<option value="">-- select time --</option>';
	for(t=7; t<21; t++) {
		optionTime = new Date(dateObject.getFullYear(), dateObject.getMonth(), dateObject.getDate(), t, 0, 0, 0);
		selectHTML += '<option value="'+optionTime.getUnixTime()+'">'+optionTime.toLocaleString()+'</option>';
		optionTime = new Date(dateObject.getFullYear(), dateObject.getMonth(), dateObject.getDate(), t, 30, 0, 0);
		selectHTML += '<option value="'+optionTime.getUnixTime()+'">'+optionTime.toLocaleString()+'</option>';
	}
	return selectHTML;
}
</script>


</html>