<?php
$rc_msg = "RingCentral Token Refresh Started. ";
include_once("class.settings.php");
include_once("class.ringcentral.php");
$SETTINGS = new Settings();
$halt = false;

if(empty($_SESSION['system_user_id'])) { // || $SETTINGS->setting['RINGCENTRAL_MASTER_USER'] != $_SESSION['system_user_id']) {
	$rc_msg .= "Invalid User or Logged Out. ";
	$halt = true;
}
/*if($SETTINGS->setting['ENV'] != 'prod') {
	$rc_msg .= "Not in Production Env. ";
	$halt = true;
}*/

if(!$halt) {
	$RC = new RingCentral($DB);
	$rc_token = $RC->get_token($SETTINGS->setting['RINGCENTRAL_MASTER_USER']);

	if(!is_array($rc_token)) {
		$rc_msg .= "No RingCentral User Token. ";
		$halt = true;
	}
	$current_time = time();
	$last_refresh_time = $RC->get_token_refresh_time($SETTINGS->setting['RINGCENTRAL_MASTER_USER']);
	$time_since = $current_time - $last_refresh_time;
	if($time_since < 2700) {
		$rc_msg .= "Token refreshed within 45 min. ";
		$halt = true;
	}
}

if(!$halt) {
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/es6-promise/3.2.2/es6-promise.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fetch/0.11.1/fetch.js"></script>
<script type="text/javascript" src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.20.1.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/ringcentral@3.2.2/build/ringcentral.min.js"></script>
<script type="text/javascript">
var debugAuth = false;
var debugCalls = false;
var debugSMS = false;
var debugVM = false;
var rpp = 100;
var rc_server;
<?php if($SETTINGS->setting['RINGCENTRAL_SANDBOX']) { ?>
	rc_server = RingCentral.SDK.server.sandbox;
<?php } else { ?>
	rc_server = RingCentral.SDK.server.production;
<?php } ?>
var rcsdk = new RingCentral.SDK({
	server: rc_server, 
	appKey: '<?php echo $SETTINGS->setting['RINGCENTRAL_APP_KEY']?>',
	appSecret: '<?php echo $SETTINGS->setting['RINGCENTRAL_APP_SECRET']?>',
	redirectUri: '<?php echo $SETTINGS->setting['RINGCENTRAL_CALLBACK_URL']?>'
});
var rc_platform = rcsdk.platform();
<?php if(is_array($rc_token)) {?>
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
	
	rc_platform.refresh()
		.then(function(){
			authData = rc_platform.auth().data();
			if(debugAuth) {
				console.log(authData);
			}
			$.post('../ajax/ajax.ringcentral.php', { action:'refresh', user_id:'<?php echo $SETTINGS->setting['RINGCENTRAL_MASTER_USER']?>', token_data:authData }, function(data) {
				if(debugAuth) {
					console.log(data);
				}
			}, 'json');
		})
		.catch(function(e){
			console.log(e.message);
		});
<?php } ?>
</script>
<?php
$rc_msg .= "RingCentral Token Refresh Completed. ";
} else {
	$rc_msg .= "RingCentral Token Refresh Skipped. ";
}
?>
<script>
console.log('<?php echo $rc_msg?>');
</script>