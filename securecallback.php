<?php 

// Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// 👆 We're continuing from the steps above. Append this to your index.php file.
// Now instantiate the Auth0 class with our configuration:
$auth0 = new \Auth0\SDK\Auth0([
    'domain' => $_ENV['AUTH0_DOMAIN'],
    'clientId' => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
    'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
]);

// Define route constants:
define('ROUTE_URL_INDEX', rtrim($_ENV['AUTH0_BASE_URL'], '/'));
define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/securelogin.php');
define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/securecallback.php');
define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/securelogout.php');
// Have the SDK complete the authentication flow:
$auth0->exchange(ROUTE_URL_CALLBACK);

include_once("class.db.php");
include_once("class.sessions.php");
include_once("class.users.php");
session_start();
$DB = new database();
$DB->connect();
$DB->setTimeZone();
$USERS = new Users($DB);
//print_r($_GET)

//TODO: Reset when Auth0 is enabled
$session = $auth0->getCredentials();
//print_r($_GET);
$sql = "SELECT * FROM Users WHERE email='".$DB->mysqli->escape_string($session->user['email'])."'";
//print_r($sql);
$snd = $DB->get_single_result($sql);
	if($snd['userStatus'] == 1) {
		$return['user_id'] = $snd['user_id'];
		$return['success'] = true;

		$_SESSION['system_user_id'] = $snd['user_id'];
		$_SESSION['user'] = $snd['username'];
		//$_SESSION['pswd'] = $snd['password'];
		$DB->log_user_action('Log in');		
	
	} else {
		echo 'This account is currently disabled.  Please contact support and give htem your email you used to login.';
		exit;	
	}


//print_r($pageParamaters);
//print_r($_SESSION);

// Finally, redirect our end user back to the / index route, to display their user profile:
header("Location: " . ROUTE_URL_INDEX);
exit;
?>
