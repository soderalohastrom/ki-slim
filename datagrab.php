<?php
include_once("class.db.php");

// Initialize the database class
$DB = new database();

// Connect to the database
$DB->connect();

if(isset($_POST['profile_id'])){
    $person_id = $_POST['profile_id'];  
} else {
    //Handle case when profile_id is not set, for example redirect to a specific page or show an error message
}

$P1_SQL = "
    SELECT 
        Persons.FirstName,
        Persons.LastName,
        Persons.Gender,
        FLOOR(DATEDIFF(CURDATE(), Persons.DateOfBirth) / 365) AS Age,
        Addresses.City,
        PersonsImages.PersonsImages_path, 
        PersonsImages.PersonsImages_status
    FROM
        Persons
        LEFT JOIN PersonsProfile ON PersonsProfile.Person_id = Persons.Person_id
        LEFT JOIN PersonsImages ON PersonsImages.Person_id = Persons.Person_id
        LEFT JOIN Addresses ON Addresses.Person_id = Persons.Person_id
    WHERE 
        Persons.Person_id = " . $person_id . "
        AND PersonsImages.PersonsImages_status = 2";

$P1_DTA = $DB->get_single_result($P1_SQL);

// Determine the appropriate range for the person_id
$ranges = [
    '100001-120000',
    '120001-140000',
    '140001-160000',
    '160001-180000',
    '180001-200000',
    '200001-220000',
    '220001-240000',
    '240001-260000',
];

foreach ($ranges as $range) {
    $bounds = explode('-', $range);
    if ($person_id >= $bounds[0] && $person_id <= $bounds[1]) {
        $selected_range = $range;
        break;
    }
}

// Construct the URL
$url = "https://kiss.kelleher-international.com/client_media/" . $selected_range . "/" . $person_id . "/" . $P1_DTA['PersonsImages_path'];

// Print the HTML table row with the constructed URL and specified height
echo "<tr style='width: 380px;'>";
echo "<td style='width: 60px;'><a href='https://kiss.kelleher-international.com/profile/{$person_id}'><img src='{$url}' height='50'></a></td>";
echo "<td style='width: 200px; text-align: center; text-decoration: none'><a href='https://kiss.kelleher-international.com/profile/{$person_id}'>{$P1_DTA['FirstName']} {$P1_DTA['LastName']}</a></td>";
echo "<td style='width: 60px; text-align: center'>{$P1_DTA['Age']}</td>"; // adjusted the width to 60px
echo "<td style='width: 100px; text-align: center'>{$P1_DTA['City']}</td>";
echo "</tr>"; 

?>
