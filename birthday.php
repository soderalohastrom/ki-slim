<?php
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Turn on display errors for testing

class database {
    function __construct() {
        $this->host = 'database-kelleher-1.c2tehdln7ywp.us-west-1.rds.amazonaws.com';
        $this->username = 'application_kelleher_user';
        $this->password = 'zxgS*f1WYA4v';
        $this->database = 'application_kelleher';
    }
    
    function connect() {
        $connection = $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database);
        $connection->query('SET NAMES utf8');		
        $connection->set_charset('utf8mb4');
        if (mysqli_connect_errno()) {
            print_r($this);
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        $this->connection = $connection;
        return $connection;
    }
}

// Let's use the database class
$db = new database();
$connection = $db->connect();

// Fetch clients with birthdays tomorrow
$query = "
SELECT 
    Persons.Person_id,
    Persons.FirstName,
    Persons.LastName,
    Persons.DateOfBirth,
    Users.email AS rep_email,
    Users.FirstName AS matchmaker_first_name,
    Users.LastName AS matchmaker_last_name
FROM 
    Persons
JOIN 
    Users ON Persons.Matchmaker_id = Users.user_id
WHERE 
    Persons.PersonsStatus_id = 1
AND 
    Persons.PersonsTypes_id IN ('4', '7', '8', '10', '12', '14')
AND 
    DATE_ADD(DATE(Persons.DateOfBirth), INTERVAL YEAR(CURDATE())-YEAR(DATE(Persons.DateOfBirth)) 
    + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(DATE(Persons.DateOfBirth)),1,0) YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 10 DAY)
AND 
    FROM_UNIXTIME(Persons.DateCreated) >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR);
";

$result = $connection->query($query);

if (!$result) {
    die("SQL Error: " . $connection->error);
}

$hasResults = false;
// Loop through the results and print names
while($client = $result->fetch_assoc()) {
    $hasResults = true;
    $client_name = $client['FirstName'] . ' ' . $client['LastName'];  // Construct the client's full name
    $matchmaker_name = $client['matchmaker_first_name'] . ' ' . $client['matchmaker_last_name'];  // Construct the matchmaker's full name
    
    // Print the alert message
    echo "Birthday Alert for $matchmaker_name: $client_name's birthday is tomorrow!<br>";

    // Commented out mail sending code for testing
    /*
    $rep_email = $client['rep_email'];
    $subject = "Birthday Alert: $client_name's birthday is tomorrow!";
    $body = "Hello,\n\nJust a reminder that $client_name's birthday is tomorrow. Make sure to send your best wishes!\n\nBest,\nYour Reminder System";
    $to = "$matchmaker_name <$rep_email>";
    mail($to, $subject, $body, "From: no-reply@yourdomain.com");
    */
}

if (!$hasResults) {
    echo "No one has a birthday tomorrow.";
}

?>
