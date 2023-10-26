<?php
session_start();
//session_set_cookie_params(9000, '/', $_SERVER['SERVER_NAME'], false, false);
//error_reporting(E_ALL);
if(empty($_SESSION['system_user_id'])) {
	header('Location: /');
	exit();
}
include_once("class.db.php");
include_once("class.sessions.php");
include_once("class.encryption.php");
include_once("class.kissphpmailer.php");
include_once("class.record.php");
include_once("class.users.php");
include_once 'assets/vendors/modules/spyc/Spyc.php';
include_once 'assets/vendors/modules/device-detector/autoload.php';
use DeviceDetector\DeviceDetector;

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);
$USERS = new Users($DB);
$badInput = false;
$isCodeValid = false;

$userAgent = $_SERVER['HTTP_USER_AGENT'];
$dd = new DeviceDetector($userAgent);
$dd->skipBotDetection();
$dd->parse();
$clientInfo = $dd->getClient();
$osInfo = $dd->getOs();
$device = $dd->getDeviceName();
$brand = $dd->getBrandName();
$model = $dd->getModel();
//$device_info = array('CLIENT' => $clientInfo, 'OS' => $osInfo, 'DEVICE' => $device, 'BRAND' => $brand, 'MODEL' => $model);
//echo '<pre>'.print_r($device_info, true).'</pre>';
//print_r($_SESSION);
//echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";

$BrowserType = $clientInfo['name'];
$BrowserVersion = $clientInfo['version'];
$OS = $osInfo['name'].' '.$osInfo['version'];
$UserDevice = $device;

$HostDomain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
//echo "Domain:".$HostDomain;

$DeviceData = array(
	'USERNAME' => $_SESSION['user'], 
	'IP_ADDRESS' => $_SERVER['REMOTE_ADDR'],
	'REVERSE_IP_LOOKUP' => $HostDomain,
	'DEVICE' => $UserDevice,
	'BROWSER_TYPE' => $BrowserType,
	'BROWSER_VERSION' => $BrowserVersion,
	'OS' => $OS
);

if(isset($_POST['validate-code'])) {
	if(!$SESSION->validToken($_POST['kiss_token'])) {
		echo "INVALID TOKEN!!!";
		exit();
	}
	include_once("assets/vendors/modules/htmlpurifier-4.10.0/library/HTMLPurifier.auto.php");
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);

	$submittedCode = $purifier->purify($_POST['validate-code']);
	$isCodeValid = $USERS->check_userDeviceCode($_SESSION['system_user_id'], $submittedCode, true);
	if($isCodeValid) {
		//write cookie
		$cookie_arr_new = array();
		if(isset($_COOKIE['kissauth'])) {
			$cookie_decrypted = $ENC->decrypt($_COOKIE['kissauth']);
			$cookie_arr = explode('|', $cookie_decrypted);
			if(is_array($cookie_arr)) {
				foreach($cookie_arr as $cookie_usr) {
					if($cookie_usr != $_SESSION['system_user_id']) {
						$cookie_arr_new[] = $cookie_usr;
					}
				}
			}
		}
		$cookie_arr_new[] = $_SESSION['system_user_id'];
		$cookie_new = $ENC->encrypt(implode('|', $cookie_arr_new));
		setcookie('kissauth', $cookie_new, time()+((3600*24)*180), '/', '.'.$_SERVER['SERVER_NAME'], true, true);
		
		//add to authenticated devices
		$USERS->write_userDevice($_SESSION['system_user_id'], json_encode($DeviceData));
	
		//redirect
		header('Location: /');
		exit();
	} else {
		$badInput = true;
	}
} else {
	do {
		$VerificationCode = rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9).rand(0, 9);
	} while($USERS->check_userDeviceCode($_SESSION['system_user_id'], $VerificationCode) > 0);

	// MAKE SURE CODE HAS NOT BEEN ISSUED WITHIN LAST 30 SECONDS //
	$between_end = (time()+1800);
	$between_start = $between_end - 90;
	$sql = "SELECT count(*) as count FROM UsersDeviceCodes WHERE DeviceCode_userId='".$_SESSION['system_user_id']."' AND (DeviceCode_expires > '".$between_start."' AND DeviceCode_expires <= '".$between_end."')";
	$ck_snd = $DB->get_single_result($sql);
	if($ck_snd['count'] == 0) {
		// STORE DEVICE INFO AND CODE //
		$USERS->write_userDeviceCode($_SESSION['system_user_id'], $VerificationCode, (time()+1800), json_encode($DeviceData));
		// GENERATE EMAIL //
		$emBody = file_get_contents('template.mail.deviceverify.html');
		$emValues[] = array(
			'field'	=>	'{{USERNAME}}',
			'value'	=>	$_SESSION['user']
		);
		$emValues[] = array(
			'field'	=>	'{{IP_ADDRESS}}',
			'value'	=>	$_SERVER['REMOTE_ADDR']
		);
		$emValues[] = array(
			'field'	=>	'{{DEVICE_TYPE}}',
			'value'	=>	$BrowserType.' '.$BrowserVersion.' | '.$OS.' | '.$UserDevice
		);
		$emValues[] = array(
			'field'	=>	'{{REVERSE_IP_LOOKUP}}',
			'value'	=>	$HostDomain
		);
		$emValues[] = array(
			'field'	=>	'{{REQUEST_TIME}}',
			'value'	=>	date("m/d/Y h:ia e")
		);
		$emValues[] = array(
			'field'	=>	'{{VERIFICATION_CODE}}',
			'value'	=>	$VerificationCode
		);
		//print_r($emValues);
		foreach($emValues as $emMerge):
			$tmp_body = str_replace($emMerge['field'], $emMerge['value'], $emBody);
			$emBody = $tmp_body;
		endforeach;
	
		$damail = new KissPHPMailer();
		$damail->IsHTML(true);
		$damail->From = 'no-reply@kelleher-international.com';
		$damail->FromName = 'Kelleher International KISS System';
		$damail->Subject = 'KISS Verification Code';
		$damail->Body = $emBody;
		//$mail->AddAddress('matt@kelleher-international.com');
		$damail->AddAddress($RECORD->get_userEmail($_SESSION['system_user_id']));
		//$mail->AddAddress('rich@kelleher-international.com');
		$damail->Send();
		//ob_start();
		//echo "Sending Email...<br>\n";
		//$debug = ob_get_clean();
	}
}
?>
<!DOCTYPE html>
<html lang="en" >
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>
			KISS - Validate Device
		</title>
		<meta name="description" content="Latest updates and statistic charts">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
		<link href="/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
		<link href="/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css" />
		<!--end::Base Styles -->
		<link rel="shortcut icon" href="favicon.ico" />
        
        <style>
#page-header {
	font-size: 5.0rem;
}
.page-message {
	margin-top:10px; 
	font-size:1.5rem; 
	color:#FFF; 
	margin-bottom:10px;
}
.page-input {
	font-size: 3.0rem;	
}

@media only screen 
and (min-device-width : 375px) 
and (max-device-width : 667px) {
#page-header {
	font-size: 2.5rem;
}
.page-message {
	margin-top:10px; 
	font-size:1.2rem; 
	color:#FFF; 
	margin-bottom:10px;
}
.page-input {
	font-size: 1.0rem;	
}

}

@media only screen 
and (min-device-width : 320px) 
and (max-device-width : 568px) { 
#page-header {
	font-size: 2.5rem;
}
.page-message {
	margin-top:10px; 
	font-size:1.2rem; 
	color:#FFF; 
	margin-bottom:10px;
}
.page-input {
	font-size: 1.0rem;	
}
}
		</style>        
	</head>
    <body>

<div class="m-grid m-grid--hor m-grid--root m-page">
    <div class="m-grid__item m-grid__item--fluid m-grid m-error-6" style="background-image: url(../../../assets/app/media/img//error/bg6.jpg);">
        <div class="m-error_container">
            <div class="m--font-light">						
                <h1 id="page-header" style="margin-top:5%;">                        	
                    Validate New Device
                </h1>
            </div>
            
            <div class="container-fluid">
                <div class="page-message">We've detected this is the first time you are signing on with this device. In order to allow you to proceed, you must first validate this device. We've sent an email with a code to enter into the form below to the email associated with this user account.</div>
                <form id="validate-form" action="validate-device.php" method="post">
                <div class="form-group m-form__group<?php echo $badInput ? ' has-danger' : ''?> row">
                    <div class="col-8">
                        <input type="text" class="form-control<?php echo ( $badInput ? ' form-control-danger' : '' )?> page-input" value="" id="validate-code" name="validate-code" placeholder="Enter Code" style="">
					</div>
                    <div class="col-4">                                                    
                        <button class="btn btn-danger page-input" type="submit">
                            Validate
                        </button>
                    </div>
					<?php if($badInput) { ?>
                    <div class="form-control-feedback" style="font-size:1.5rem;">The code you entered is incorrect or expired.</div>
                    <?php } ?>
                    <div class="page-message">
                        Enter in the code provided in the email that has been sent to the email account associated with this user.
                    </div>
                </div>
                <?php echo $SESSION->renderToken()?>
                </form>
            </div>
            
        </div>
    </div>
</div>
<!-- end:: Page -->
<!--begin::Base Scripts -->
<script src="/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
<script src="/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
<!--end::Base Scripts -->    
    
    </body>
</html>
