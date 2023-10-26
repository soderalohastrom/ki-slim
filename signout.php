<?php
session_start();
include_once("class.db.php");
include_once("class.page.php");
include_once("class.users.php");
include_once("class.sessions.php");
include_once("class.encryption.php");
$DB = new database();
$DB->connect();
$USERS = new Users($DB);
$PAGE = new Page($DB, $USERS);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

$DB->log_user_action('Log out');
$SESSION->killSession();
session_destroy();
session_regenerate_id();


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

    header("Location: " . $auth0->logout(ROUTE_URL_LOGIN));
    exit;
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta http-equiv="refresh" content="0;url=./">
</head>
<body class="m--skin- m-page--loading m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--fixed m-footer--push m-aside--offcanvas-default"  >
</body>
</html>