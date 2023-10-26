<?php
// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// Instantiate the Auth0 class with our configuration:
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

$session = $auth0->getCredentials();

if ($session === null) {
    // Reset user sessions each time they go to login to avoid "invalid state" errors
    $auth0->clear();

    // Set up the local application session and redirect the user to the Auth0 Universal Login Page to authenticate
    header("Location: " . $auth0->login(ROUTE_URL_CALLBACK));
}

session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Permissions-Based Link Page</title>
  <!-- Include Font Awesome CSS for the lock icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <!-- Include any necessary CSS and JS files -->
  <link rel="stylesheet" href="styles.css">
  <script src="script.js"></script>
</head>
<body>
  <h1>Welcome to the Permissions-Based Link Page</h1>

  <?php
    // Retrieve the user's email from the session or any other way you're storing it
    $userEmail = $_SESSION['user_email'];

    // Define the links and their permissions
    $links = [
      ['title' => 'Link 1', 'url' => 'link1.php', 'permission' => 'user1@example.com,user2@example.com', 'clickable' => true],
      ['title' => 'Link 2', 'url' => 'link2.php', 'permission' => 'user1@example.com,user3@example.com', 'clickable' => false],
      ['title' => 'Link 3', 'url' => 'link3.php', 'permission' => 'user2@example.com,user3@example.com', 'clickable' => true]
    ];

    // Display the links based on user permissions
    foreach ($links as $link) {
      $allowedUsers = explode(',', $link['permission']);

      if (in_array($userEmail, $allowedUsers)) {
        // User has permission to view the link
        echo '<a href="' . ($link['clickable'] ? $link['url'] : '#') . '">';
        if (!$link['clickable']) {
          // Add lock icon before the link text for viewable but not clickable links
          echo '<i class="fas fa-lock"></i> ';
        }
        echo $link['title'] . '</a>';
      }
    }
  ?>

</body>
</html>
