<?php 
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("class.settings.php");
include_once("class.nylas.php");
include_once("class.ringcentral.php");
$SETTINGS = new Settings();
$NYLAS = new Nylas($SETTINGS->setting['NYLAS_API_ID'], $SETTINGS->setting['NYLAS_API_SECRET'], $DB, $SETTINGS->setting['NYLAS_CALLBACK_URL']);
$RC = new RingCentral($DB);
$nylas_connect_url = $NYLAS->build_connect_string('X@Y.COM');
if(strlen($SETTINGS->setting['NYLAS_API_ID']) > 0 && strlen($SETTINGS->setting['NYLAS_API_SECRET']) > 0) {
	$nylas_active = true;
	$nylas_connected = $NYLAS->get_token($_SESSION['system_user_id']);
} else {
	$nylas_active = false;
	$nylas_connected = false;
}
if(array_key_exists('params', $pageParamaters) || count($pageParamaters['params']) > 0) {
	$conn_result = $NYLAS->get_api_log_entry($_SESSION['system_user_id'], $pageParamaters['params'][0]);
}
if($SETTINGS->setting['RINGCENTRAL_MASTER_USER'] == $_SESSION['system_user_id']) {
	$rc_active = true;
	$rc_token = $RC->get_token($_SESSION['system_user_id']);
	//$rc_config = $RC->get_config($_SESSION['system_user_id']);
} else {
	$rc_active = false;
}
$rc_connected = false;
?>
<!--
<script type="text/javascript" src="https://unpkg.com/es6-promise@latest/dist/es6-promise.auto.js"></script>
<script type="text/javascript" src="https://unpkg.com/whatwg-fetch@latest/dist/fetch.umd.js"></script>
<script type="text/javascript" src="https://unpkg.com/@ringcentral/sdk@latest/dist/ringcentral.js"></script>
-->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/es6-promise/3.2.2/es6-promise.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fetch/0.11.1/fetch.js"></script>
<script type="text/javascript" src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.20.1.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/ringcentral@3.2.2/build/ringcentral.min.js"></script>
<script type="text/javascript">
var rc_connected = false;
var rc_server;
<?php if($SETTINGS->setting['RINGCENTRAL_SANDBOX']) { ?>
	rc_server = RingCentral.SDK.server.sandbox;
<?php } else { ?>
	rc_server = RingCentral.SDK.server.production;
<?php } ?>
//var rc_ringout = '<?php echo $rc_config['RingOut']?>';
//var rc_sms = '<?php echo $rc_config['SMS']?>';
var rcsdk = new RingCentral.SDK({
	server: rc_server, 
	appKey: '<?php echo $SETTINGS->setting['RINGCENTRAL_APP_KEY']?>',
	appSecret: '<?php echo $SETTINGS->setting['RINGCENTRAL_APP_SECRET']?>',
	redirectUri: '<?php echo $SETTINGS->setting['RINGCENTRAL_CALLBACK_URL']?>'
});
var rc_platform = rcsdk.platform();
</script>
<style type="text/css">
#rc_save_success, #rc_save_error {
	display:none;
}
</style>
<div class="m-subheader">
	<h4><i class="flaticon-map"></i> API Connections</h4>
</div>

<div class="m-content">
<?php if(is_array($conn_result)) { ?>
<div class="row">
<div class="col-12">
	<?php if($conn_result['Log_result'] == 1) { ?>
	<div class="alert alert-success alert-dismissible fade show">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<span class="la la-check"></span> Your API connection to <?php echo ucwords($conn_result['Log_service'])?> has been set up.
	</div>
	<?php } else { ?>
	<div class="alert alert-danger alert-dismissible fade show">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
		<span class="la la-exclamation-triangle"></span> An error occurred when trying to establish a connection to <?php echo ucwords($conn_result['Log_service'])?>: <?php echo $conn_result['Log_error']?>
	</div>
	<?php } ?>
</div>
</div>
<?php } ?>
<div class="row">
<?php if($nylas_active) { ?>
<div class="col-md-6">
	<!--begin::Portlet-->
	<div class="m-portlet">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="la la-calendar"></i>
					</span>
					<h3 class="m-portlet__head-text">
						Nylas <small>Email and Calendar</small>
					</h3>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<div class="row" style="margin-bottom:10px;">
				<div class="col-lg-2">Status:</div>
				<div class="col-lg-10" id="nylas_status">
					<?php if($nylas_connected) { ?>
					<span class="m-badge m-badge--success m-badge--wide">CONNECTED</span>
					<?php } else { ?>
					<span class="m-badge m-badge--metal m-badge--wide">NOT CONNECTED</span>
					<?php } ?>
				</div>
			</div>
			<div class="row" style="margin-bottom:10px;">
				<div class="col-lg-2">Email:</div>
				<div class="col-lg-10" id="nylas_account" style="font-weight:400;">
					<?php echo ( $nylas_connected ? $NYLAS->account_name : 'N/A' )?>
				</div>
			</div>
			<?php if($nylas_connected) { ?>
			<div class="row form-group" id="nylas_calendar_div">
				<div class="col-lg-2">Calendar:</div>
				<div class="col-lg-10" id="nylas_calendar" style="font-weight:400;">
					<span id="nylas_calendar_name"><?php echo ( strlen($NYLAS->calendar_name) > 0 ? $NYLAS->calendar_name : 'Not Selected' )?></span>&nbsp;
					<a href="javascript:void(editCalendar())" class="btn btn-secondary m-btn m-btn--icon btn-sm m-btn--icon-only" id="btn_nylas_calendar"><i class="la la-edit"></i></a>
				</div>
				<div class="col-lg-10" id="nylas_calendar_edit" style="display:none;">
					<div class="input-group input-group-sm">
						<select class="form-control" id="nylas_calendar_sel">
							<option value="">--select--</option>
						</select>
						<span class="input-group-btn">
							<button class="btn btn-primary" id="nylas_save_calendar"><i class="la la-check"></i></button>
						</span>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="m-portlet__foot">
			<div class="row align-items-center">
				<div class="col-lg-12" id="nylas_btns">
					<button type="button" class="btn btn-primary" id="nylas_change" data-toggle="modal" data-target="#nylas_email" style="<?php echo $nylas_connected ? '' : 'display:none;'?>">
						<i class="la la-chain"></i> Change
					</button>
					<button type="button" class="btn btn-secondary" id="nylas_disconnect" style="<?php echo $nylas_connected ? '' : 'display:none;'?>">
						<i class="la la-chain-broken"></i> Disconnect
					</button>
					<button type="button" class="btn btn-primary" id="nylas_connect" data-toggle="modal" data-target="#nylas_email" style="<?php echo $nylas_connected ? 'display:none;' : ''?>">
						<i class="la la-chain"></i> Connect
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--end::Portlet-->
</div>
<?php } ?>
<?php if($rc_active) { ?>
<div class="col-md-6">
	<!--begin::Portlet-->
	<div class="m-portlet">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="la la-phone"></i>
					</span>
					<h3 class="m-portlet__head-text">
						RingCentral <small>Calls and SMS</small>
					</h3>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<div class="row" style="margin-bottom:10px;">
				<div class="col-lg-2">Status:</div>
				<div class="col-lg-10" id="rc_status">
					<?php if($rc_connected) { ?>
					<span class="m-badge m-badge--success m-badge--wide">CONNECTED</span>
					<?php } else { ?>
					<span class="m-badge m-badge--metal m-badge--wide">NOT CONNECTED</span>
					<?php } ?>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-2">User:</div>
				<div class="col-lg-10" id="rc_account" style="font-weight:400;">
					N/A
				</div>
			</div>
		</div>
		<div class="m-portlet__foot">
			<div class="row align-items-center">
				<div class="col-lg-12" id="rc_btns">
					<!--<button type="button" class="btn btn-info" id="rc_config" data-toggle="modal" data-target="#rc_config_modal" style="<?php echo $rc_connected ? '' : 'display:none;'?>">
						<i class="la la-cogs"></i> Config
					</button>-->
					<button type="button" class="btn btn-info" id="rc_sync" style="<?php echo $rc_connected ? '' : 'display:none;'?>">
						<i class="la la-download"></i> Sync
					</button>
					<button type="button" class="btn btn-primary" id="rc_change" style="<?php echo $rc_connected ? '' : 'display:none;'?>">
						<i class="la la-chain"></i> Change
					</button>
					<button type="button" class="btn btn-secondary" id="rc_disconnect" style="<?php echo $rc_connected ? '' : 'display:none;'?>">
						<i class="la la-chain-broken"></i> Disconnect
					</button>
					<button type="button" class="btn btn-primary" id="rc_connect" style="<?php echo $rc_connected ? 'display:none;' : ''?>">
						<i class="la la-chain"></i> Connect
					</button>
				</div>
			</div>
		</div>
	</div>
	<!--end::Portlet-->
</div>
<?php } ?>
</div>
</div>

<!--begin::Nylas Connection Modal-->
<div class="modal fade" id="nylas_email" tabindex="-1" role="dialog" aria-labelledby="nylas_email_title" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="nylas_email_title">
					Connect to Nylas
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						&times;
					</span>
				</button>
			</div>
			<div class="modal-body">
				<form>
					<div class="form-group">
						<label for="nylas_email_account" class="form-control-label">
							Please enter your email address:
						</label>
						<input type="email" class="form-control" id="nylas_email_account">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					Close
				</button>
				<button type="button" class="btn btn-primary" id="nylas_email_submit">
					Continue
				</button>
			</div>
		</div>
	</div>
</div>
<!--end::Nylas Connection Modal-->
<!--begin::RingCentral Config Modal **CURRENTLY NOT IN USE**
<div class="modal fade" id="rc_config_modal" tabindex="-1" role="dialog" aria-labelledby="rc_config_title" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="rc_config_title">
					RingCentral User Config
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						&times;
					</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="alert alert-success alert-dismissible show" id="rc_save_success" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
					Config saved successfully.
				</div>
				<div class="alert alert-warning alert-dismissible show" id="rc_save_error" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
					Error saving config.
				</div>
				<form>
					<div class="form-group">
						<label for="rc_ringout_number" class="form-control-label">
							RingOut Number
						</label>
						<select class="form-control" id="rc_ringout_number">
							<option value="">--select--</option>
						</select>
					</div>
					<div class="form-group">
						<label for="rc_sms_number" class="form-control-label">
							SMS Number
						</label>
						<select class="form-control" id="rc_sms_number">
							<option value="">--select--</option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					Cancel
				</button>
				<button type="button" class="btn btn-primary" id="rc_config_submit">
					Save
				</button>
			</div>
		</div>
	</div>
</div>
<!--end::RingCentral Config Modal-->
<script type="text/javascript">
$(document).ready(function() {
	$('#nylas_email_submit').on('click', nylas_connect);
	$('#nylas_disconnect').on('click', nylas_disconnect);
	$('#nylas_save_calendar').on('click', saveCalendar);
	$('#rc_connect').on('click', rc_connect);
	$('#rc_change').on('click', rc_connect);
	$('#rc_disconnect').on('click', rc_disconnect);
	$('#rc_sync').on('click', rc_sync);
	//$('#rc_config_submit').on('click', rc_save_config);
	<?php if($rc_active && is_array($rc_token)) {?>
	var tokenType = '<?php echo $rc_token['token_type']?>';
	var accessToken = '<?php echo $rc_token['access_token']?>';
	var expiresIn = '<?php echo $rc_token['expires_in']?>';
	var refreshToken = '<?php echo $rc_token['refresh_token']?>';
	var refreshTokenExpiresIn = '<?php echo $rc_token['refresh_token_expires_in']?>';
	var authData = rc_platform.auth().data();
	authData.token_type = tokenType;
	authData.access_token = accessToken;
	authData.expires_in = expiresIn;
	authData.refresh_token = refreshToken;
	authData.refresh_token_expires_in = refreshTokenExpiresIn;
	rc_platform.auth().setData(authData);
	
	//RingCentral: check if user is authenticated. If so, get user details to display.
	rc_platform.refresh()
		.then(function(){
			authData = rc_platform.auth().data();
			//console.log(authData);
			$.post('../../ajax/ajax.ringcentral.php', { action:'refresh', user_id:'<?php echo $_SESSION['system_user_id']?>', token_data:authData }, function(data) {
				//console.log(data);
			}, 'json');
			rc_platform.send({
				method: 'GET',
				url: '/account/~/extension/~',
				query: {},
				headers: {},
				body: {}
			})
			.then(function(apiResponse){
				//console.log(apiResponse.json());
				$('#rc_status').html('<span class="m-badge m-badge--success m-badge--wide">CONNECTED</span>');	// <a href="javascript:void(test_sms())">SMS ME</a>');
				$('#rc_account').html(apiResponse.json().name+', ext. '+apiResponse.json().extensionNumber);
				$('#rc_connect').hide();
				$('#rc_sync').show();
				$('#rc_change').show();
				$('#rc_disconnect').show();
			})
			.catch(function(e){
				console.log('RingCentral Error: '+e.message);
			});
			
			/*rc_platform.send({
				method: 'GET',
				url: '/account/~/extension/~/phone-number',
				query: {},
				headers: {},
				body: {}
			})
			.then(function(apiResponse){
				console.log(apiResponse.json());
				for(var i = 0; i < apiResponse.json().records.length; i++) {
					var num = apiResponse.json().records[i].phoneNumber;
					$('#rc_ringout_number').append('<option value="'+num+'">'+num+'</option>');
					$('#rc_sms_number').append('<option value="'+num+'">'+num+'</option>');
				}
				$('#rc_ringout_number').val(rc_ringout);
				$('#rc_sms_number').val(rc_sms);
			})
			.catch(function(e){
				console.log(e.message);
			});*/
		})
		.catch(function(e){
			console.log('RingCentral Error: '+e.message);
		});
	<?php } ?>
});

//Nylas functions
function nylas_connect() {
	var email = $.trim($('#nylas_email_account').val());
	if(email == '') {	//to-do: add email format validation
		alert('Please enter a valid email address.');
	} else {
		var connect_url = '<?php echo $nylas_connect_url?>';
		var connect_url_2 = connect_url.replace('X@Y.COM', email);
		document.location = connect_url_2;
		//$('#nylas_email').modal('hide');
	}
}
function nylas_disconnect() {
	$.get("/ajax/ajax.nylas.php", { 'action':'disconnect' }, function(data) {
		//console.log(data);
		if(data.success == '1') {
			$('#nylas_status').html('<span class="m-badge m-badge--metal m-badge--wide">NOT CONNECTED</span>');
			$('#nylas_account').html('N/A');
			$('#nylas_calendar_div').hide();
			$('#nylas_change').hide();
			$('#nylas_disconnect').hide();
			$('#nylas_connect').show();
		} else {
			alert(data.message);
		}
	}, 'json');
}
function editCalendar() {
	var calendarId = '<?php echo $NYLAS->calendar_id?>';
	var selected = '';
	var optionCount = $('option', '#nylas_calendar_sel').length;
	if(optionCount == 1) {
		$('#btn_nylas_calendar').prop('disabled', true);
		$.get("/ajax/ajax.nylas.php", { 'action':'get_calendars' }, function(data) {
			//console.log(data);
			if(data.calendars.length > 0) {
				for(var i = 0; i < data.calendars.length; i++) {
					if(data.calendars[i].id == calendarId) {
						selected = ' selected';
					} else {
						selected = '';
					}
					$('#nylas_calendar_sel').append('<option value="'+data.calendars[i].id+'|'+data.calendars[i].name+'"'+selected+'>'+data.calendars[i].name+'</option>');
				}
			}
			$('#nylas_calendar').hide();
			$('#nylas_calendar_edit').show();
			$('#btn_nylas_calendar').prop('disabled', false);
		}, 'json');
	} else {
		$('#nylas_calendar').hide();
		$('#nylas_calendar_edit').show();
	}
}
function saveCalendar() {
	var calendarId = '';
	var calendarName = '';
	var calendarVars = $('#nylas_calendar_sel').val();
	if(calendarVars != '') {
		var calendarArr = calendarVars.split('|');
		calendarId = calendarArr[0];
		calendarName = calendarArr[1];
	}
	$.get("/ajax/ajax.nylas.php", { 'action':'save_calendar', 'calendar_id':calendarId, 'calendar_name':calendarName }, function(data) {
		//console.log(data);
		if(data.success == '1') {
			$('#nylas_calendar_name').html(calendarName);
			$('#nylas_calendar_edit').hide();
			$('#nylas_calendar').show();
		} else {
			alert(data.message);
		}
	}, 'json');
}

//RingCentral functions
function rc_connect() {
	var loginUrl = rc_platform.loginUrl();
	window.location.assign(loginUrl);
}
function rc_disconnect() {
	rc_platform.logout()
		.then(function(apiResponse){
			$('#rc_status').html('<span class="m-badge m-badge--metal m-badge--wide">NOT CONNECTED</span>');
			$('#rc_account').html('N/A');
			$('#rc_sync').hide();
			$('#rc_change').hide();
			$('#rc_disconnect').hide();
			$('#rc_connect').show();
			$.post("/ajax/ajax.ringcentral.php", { 'action':'disconnect' }, function(data) {
			}, 'json');
		})
		.catch(function(e){
			alert(e.message);
		});
}
function rc_sync() {
	window.open('/apis/webhooks/ringcentral.php', '', 'width=600,height=800,location=0,menubar=0,status=0,titlebar=0,toolbar=0');
}
/* These functions are not currently in use but I left them here in case I need them in the future. -Jen
function rc_save_config() {
	$('#rc_save_success').hide();
	$('#rc_save_error').hide();
	var ringout = $('#rc_ringout_number').val();
	var sms = $('#rc_sms_number').val();
	$.post("/ajax/ajax.ringcentral.php", { 'action':'save_config', 'RingOut':ringout, 'SMS':sms  }, function(data) {
		if(data.success == 1) {
			$('#rc_save_success').fadeIn().delay(5000).fadeOut();
		} else {
			$('#rc_save_error').fadeIn().delay(5000).fadeOut();
		}
	}, 'json');
}
function test_sms() {
	rc_platform.post('/account/~/extension/~/sms', {
        from: {phoneNumber:'+1'}, // Your sms-enabled phone number
        to: [
            {phoneNumber:'+1'} // Second party's phone number
        ],
        text: 'This is a test message from Kelleher app.'
    })
    .then(function(response) {
		console.log(response.json());
        alert('Success: ' + response.json().id);
    })
    .catch(function(e) {
        alert('Error: ' + e.message);
    });
}
*/
</script>