<?php
// // Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

// // Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// // 👆 We're continuing from the steps above. Append this to your index.php file.
// // Now instantiate the Auth0 class with our configuration:
$auth0 = new \Auth0\SDK\Auth0([
    'domain' => $_ENV['AUTH0_DOMAIN'],
    'clientId' => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
    'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
]);

// // Define route constants:
define('ROUTE_URL_INDEX', rtrim($_ENV['AUTH0_BASE_URL'], '/'));
define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/securelogin.php');
define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/securecallback.php');
define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/securelogout.php');
// // 👆 We're continuing from the steps above. Append this to your index.php file.

$session = $auth0->getCredentials();

if ($session === null) {
	
	//  // It's a good idea to reset user sessions each time they go to login to avoid "invalid state" errors, should they hit network issues or other problems that interrupt a previous login process:
	$auth0->clear();

	//  // Finally, set up the local application session, and redirect the user to the Auth0 Universal Login Page to authenticate.
	header("Location: " . $auth0->login(ROUTE_URL_CALLBACK));

}

// 	// // The user is logged in.
// 	// echo '<pre>';
// 	// print_r($session->user);
// 	// echo '</pre>';
	
// 	// echo '<p>You can now <a href="/logout">log out</a>.</p>';

// //echo '<h1>here -> <pre>'. print_r($auth0,true). '</pre></h1>';	
error_reporting(0);
session_set_cookie_params(0, '/', '.'.$_SERVER['SERVER_NAME'], false, true);
session_start();
if($_SESSION['session_remember']) {
	setcookie('kims_username', $_SESSION['user'], time()+((3600*24)*60), '/', '.'.$_SERVER['SERVER_NAME'], false, true);
	setcookie('kims_password', $_SESSION['pswd'], time()+((3600*24)*60), '/', '.'.$_SERVER['SERVER_NAME'], false, true);
	//setcookie("kimspassword", "testPassword", 9000);
	//echo "ADDING COOKIE";
} else {
	setcookie('kims_username', '', time()+((3600*24)*60), '/', false, true);
	setcookie('kims_password', '', time()+((3600*24)*60), '/', false, true);
}
include_once("class.db.php");
include_once("class.page.php");
include_once("class.users.php");
include_once("class.encryption.php");
$DB = new database();
$DB->connect();
$DB->setTimeZone();
$USERS = new Users($DB);
$PAGE = new Page($DB, $USERS);
$ENC = new encryption();
//print_r($_GET);

//TODO: Reset when Auth0 is enabled
// //print_r($_GET);
// $sql = "SELECT * FROM Users WHERE email='".$DB->mysqli->escape_string($session->user['email'])."'";
// print_r($sql);
// $snd = $DB->get_single_result($sql);
// 	if($snd['userStatus'] == 1) {
// 		$return['user_id'] = $snd['user_id'];
// 		$return['success'] = true;

// 		$_SESSION['system_user_id'] = $snd['user_id'];
// 		$_SESSION['user'] = $snd['username'];
// 		//$_SESSION['pswd'] = $snd['password'];
// 		$DB->log_user_action('Log in');		
	
// 	} else {
// 		echo 'This account is currently disabled';
// 		exit;	
// 	}


//print_r($pageParamaters);
//print_r($_SESSION);

if(!isset($_SESSION['system_user_id'])) {
	include_once("login.php");
 } else {
	$device_verified = true;
	if($_SESSION['spoofed_user'] == 1):
		$device_verified = true;
	else:	
		if(isset($_COOKIE['kissauth'])) {
			$cookie_decrypted = $ENC->decrypt($_COOKIE['kissauth']);
			$cookie_arr = explode('|', $cookie_decrypted);
			if(is_array($cookie_arr)) {
				foreach($cookie_arr as $cookie_usr) {
					if($cookie_usr == $_SESSION['system_user_id']) {
						$device_verified = true;
					}
				}
			}
		}
	endif; 
	
	/*if(!$device_verified) {
		include("validate-device-2.php");
	} else {
		*/
		include("page.php");
		//include_once("test.php");
	//}
}
?>