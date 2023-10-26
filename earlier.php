<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Pinecone API key
$api_key = '12ad5297-ff6e-40a5-a92f-e80b8fd52e8e';

// Include the necessary class file
include_once("class.db.php");

// Initialize the database class
$DB = new database();
// Connect to the database
$DB->connect();

// Get the person_id from the form input, or default to '230126'
$person_id = isset($_GET['person_id']) ? htmlspecialchars($_GET['person_id']) : '230126';

// Fetch vector from 'kelleher-guys' index
$ch = curl_init('https://kelleher-guys-eccc957.svc.us-east-1-aws.pinecone.io/vectors/fetch?ids=' . $person_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Accept: application/json',
  'Content-Type: application/json',
  'Api-Key: ' . $api_key
]);
$fetch_response = json_decode(curl_exec($ch), true);
curl_close($ch);

// Check if the vector was fetched successfully
if (isset($fetch_response['vectors'][$person_id])) {
  $vector = $fetch_response['vectors'][$person_id]['values'];

  // Add the alert for the vector
  echo '<script type="text/javascript">alert("Vector: ' . json_encode($vector) . '");</script>';

  // Query 'kelleher-dolls' index with fetched vector
  $ch = curl_init('https://kelleher-dolls-eccc957.svc.us-east-1-aws.pinecone.io/query');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Api-Key: ' . $api_key
  ]);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'namespace' => '',
    'topK' => 10,
    'includeValues' => false,
    'includeMetadata' => true,
    'vector' => $vector
  ]));

  $query_response = json_decode(curl_exec($ch), true);
  curl_close($ch);
  $man_name = $fetch_response['vectors'][$person_id]['metadata']['First name'];

  // Output message
  echo '<h1 style="text-align:center;">Top Men matches for ' . htmlspecialchars($man_name) . '</h1>';

  // Check if there are any matches
  if (isset($query_response['matches']) && count($query_response['matches']) > 0) {
    // Display the matches in a table

    echo '<style>
        table {
            border-collapse: collapse;
            border: 1px solid #ddd;
            margin: auto;
            font-family: Arial, sans-serif;
            font-size: 14px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }
        body {
            font-family: \'Poppins\', sans-serif;
        }
        th, td {
            text-align: left;
            padding: 8px !important;
        }
        th {
            background-color: #0077c2;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #e6f7ff;
        }
        tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .results {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            margin: 0 auto;
        }
        .matches {
            width: 80%;
            margin: 20px auto 40px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        th, td {
            padding: 5px;
        }

        /* Flex media query for switching to column layout */
        @media (max-width: 900px) {
            .results {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>';

    function convertFreshnessToYearsAndMonths($freshness) {
      // Assuming $freshness is in days, convert it to years and months
      $years = floor($freshness / 365);
      $remainingDays = $freshness % 365;
      $months = floor($remainingDays / 30);
      // Construct the years and months string
      $formattedFreshness = '';
      if ($years > 0) {
          $formattedFreshness .= $years . ' year';
          if ($years > 1) {
              $formattedFreshness .= 's';
          }
      }
      if ($months > 0) {
          if ($formattedFreshness !== '') {
              $formattedFreshness .= ' ';
          }
          $formattedFreshness .= $months . ' month';
          if ($months > 1) {
              $formattedFreshness .= 's';
          }
      }
      return $formattedFreshness;
    }

    // Function to get field value if it exists, otherwise return an empty string
    function getFieldIfExists($array, $fieldName) {
      return isset($array[$fieldName]) ? $array[$fieldName] : '';
    }

    // Retrieve the image path
    $P1_SQL = "
        SELECT 
            Persons.Gender,
            PersonsImages.PersonsImages_path, 
            PersonsImages.PersonsImages_status
        FROM
            Persons
            LEFT JOIN PersonsImages ON PersonsImages.Person_id = Persons.Person_id
        WHERE 
            Persons.Person_id = " . $person_id . "
            AND PersonsImages.PersonsImages_status = 2";  // Only include results where PersonsImages_status is 2

    $P1_DTA = $DB->get_single_result($P1_SQL);

    // Ensure $P1_DTA is not null before using it
    if ($P1_DTA !== null) {
      $selected_range = null;
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

      $image_path = $P1_DTA['PersonsImages_path']; // Replace with your actual logic to retrieve the image path
      $image_url = "https://kiss.kelleher-international.com/client_media/" . $selected_range . "/" . $person_id . "/" . $image_path;

      echo '<div class="results">';
      foreach ($query_response['matches'] as $match) {
        $metadata = $match['metadata'];
        echo '<div class="matches">';
        // Output the results in a table
        echo '<table>';
        echo '<thead><tr><th>ID</th><th>First Name</th><th>Age</th><th>KI Type</th><th>City</th><th>State</th><th>Zip Code</th><th>Income</th><th>Ethnicity</th><th>Religion</th><th>Freshness</th><th>Score</th></tr></thead>';
        echo '<tbody>';
      
        echo '<tr>';
        echo '<td rowspan="2"><a href="https://kiss.kelleher-international.com/profile/' . $person_id . '"><img src="' . $image_url . '" alt="Profile Image" height="50px"></a></td>';
        echo '<td>' . $metadata['First name'] . '</td>';
        echo '<td>' . $metadata['Age'] . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'KI type') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'City') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'State') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'Zip code') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'Income') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'Ethnicity') . '</td>';
        echo '<td>' . getFieldIfExists($metadata, 'Religion') . '</td>';
        echo '<td>' . convertFreshnessToYearsAndMonths($metadata['Freshness']) . '</td>';
        echo '<td>' . round($match['score'] * 10) / 10 . '%</td>';
        echo '</tr>';
      
        echo '<tr>';
        echo '<td colspan="11" style="text-align: left;">Politics: ' . getFieldIfExists($metadata, 'Politics') . '</td>';
        echo '</tr>';
      
        echo '<tr>';
        echo '<td>Travel for a match? ' . getFieldIfExists($metadata, 'Travel for match') . '</td>';
        echo '<td colspan="11" rowspan="2">' . getFieldIfExists($metadata, 'Summary') . '</td>';
        echo '</tr>';
      
        echo '<tr>';
        echo '<td>Relocate? ' . getFieldIfExists($metadata, 'Relocation') . '</td>';
        echo '</tr>';
      
        echo '<tr style="height: 25px; background-color: #0077c2;">';
        echo '<td colspan="12"></td>';
        echo '</tr>';
      
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
      }
      echo '</div>';

      $top_result_id = $query_response['matches'][0]['id'];

      echo '<div style="text-align: center; margin-top: 30px;">';
      echo '<form method="GET" action="">';
      echo '<label for="person_id"><strong>Enter Person ID:</strong></label>';
      echo '<input type="text" id="person_id" name="person_id" required>';
      echo '<input type="submit" value="Find matches">';
      echo '</form>';
      echo '</div>';

      echo '<br><br>';
      echo '<iframe name="results" style="width:100%; height:600px;" src="https://kiss.kelleher-international.com/profile/' . $top_result_id . '"></iframe>';

    } else {
      // No image path found
      echo "<p>No image path found for person ID \"" . $person_id . "\"</p>";
    }

  } else {
    // No matches found
    echo "<p>No matches found.</p>";
  }
} else {
  // Vector not found
  echo "<p>No vector found for person ID \"" . $person_id . "\"</p>";
}

?>
