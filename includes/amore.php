<?php
// Include your database class
include_once("class.db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle the form submission
    $gender = $_POST["gender"];
    
    // Initialize $person_id
    $person_id = '';

    if (isset($_POST['profile_id'])) {
        $input = trim($_POST["profile_id"]);

        // Check if input is a URL or a profile ID
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            // Input is a URL, extract the profile ID
            $person_id = substr($input, strrpos($input, '/') + 1);
        } else {
            // Input is not a URL, assume it's a profile ID
            $person_id = $input;
        }
    }

    // Check if 'clear_all' was posted
    if (isset($_POST['clear_all'])) {
        // Clear all links was clicked, so clear the cookie
        setcookie($gender."links", "", time() - 3600, "/"); // Clear the cookie by setting its expiration date in the past
    } else if (!empty($person_id)) { // Continue only if $person_id is not empty
        // Initialize the database class
        $DB = new database();

        // Connect to the database
        $DB->connect();

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

        // Construct the name, profile, age, city from $P1_DTA
        $name = $P1_DTA['FirstName'] . " " . $P1_DTA['LastName'];
        $profile = "https://kiss.kelleher-international.com/profile/{$person_id}";
        $age = $P1_DTA['Age'];
        $city = $P1_DTA['City'];

        // Load existing data from the cookie, if any
        $data = isset($_COOKIE[$gender."links"]) ? json_decode($_COOKIE[$gender."links"], true) : array();

        // Add the new data
        $data[] = array(
            'url' => $url,
            'name' => $name,
            'profile' => $profile,
            'age' => $age,
            'city' => $city
        );

        // Save the updated data to the cookie
        setcookie($gender."links", json_encode($data), time() + (86400 * 90), "/"); 
    }

    // Redirect to the same page to avoid form re-submission on page refresh
    header("Location: lovelinks5.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
<style>
	
  html, body {
    height: 100%;	  
    background-color: #ecedf3;
    font-family: 'Poppins', sans-serif; 
  }


td {
  font-size: 12px;
  font-weight: normal;
  color: #333;
  height: 50px;
}

.center {
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* Adjust this property */
  align-items: center;
  text-align: center;
  margin: 50px 50px 100px 50px;  /* Adjust this property */
  background-color: white; /* Moved from media query */
  border-radius: 10px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
}


  .quote-box {
    width: 380px;
    height: 220px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border: 1px solid #444444;
	border-radius: 10px;  
    padding: 30px;
    box-sizing: border-box;
    background-color: <?php
      $colors = ['#FFD1DC', '#A2CFFE', '#B5A9D4', '#FFFBA1', '#FFABAB', '#A1FFD5', '#CEA2FD', '#FFDAC1', '#FFAAA5', '#EEEEEE'];
      echo $colors[array_rand($colors)];
    ?>;
  }
	
  .quote {
    margin-top: 30px;
	margin-bottom: 20px;
    font-weight: bold;
    font-size: 14px;
  }

  .author {
    font-weight: light;
    font-size: 12px;      
  }

  .dashboard-link {
    display: inline-block;
    padding: 10px 20px;
    font-size: 18px;
    text-decoration: none;
    background-color: #4d5064;
    color: #fff;
    border-radius: 5px;
    margin-bottom: 20px;
  }

  .kiss {
    font-size: 45px;
    font-weight: bold;
    color: #000;
  }
	
	a#dash-link:before {
		content: "DASHBOARD";
	}

	a#dash-link:hover:before {
		content: "wait for it..";
	}
	
  .radar-label {
    font-size: 12px;
  }

  .radar-field {
    width: 320px;
    height: 28px;
	margin-top: 10px;
    font-size: 14px;
	border-radius: 5px;
    text-align: center;
  }
	
  .input-label {
    font-size: 14px;
  }

  .input-field {
    width: 320px;
    height: 38px;
	margin-top: 10px;
    font-size: 18px;
	border-radius: 2px;
    text-align: center;
  }

  .submit-button {
    width: 160px;
    height: 40px;
	margin-left: 15px;
    font-size: 14px;
    color: #fff;
    border-radius: 5px;
    margin-bottom: 20px;
    background-color: #4d5064;      
  }
	
   .radar-button {
    width: 60px;
    height: 30px;
	margin-left: 10px;
    font-size: 14px;
    color: #fff;
    border-radius: 5px;
    margin-bottom: 20px;
    background-color: #4d5064;      
  }
	
.radar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    text-align: center;
    margin: 40px 50px 50px 50px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
}

.genderContainer {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;  /* Modify this */
    width: 100%;
    background-color: white;
    box-sizing: border-box;
}

@media (min-width: 768px) {
  .genderContainer {
    flex-direction: row;
	align-items: flex-start; 
  }
}

.menContainer, .womenContainer {
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
}

.menContainer {
    background-color: #F1F8FF;
    margin-right: 10px; 	
}

.womenContainer {
    background-color: #FFF1F2;
    margin-left: 10px; 	
}

.menContainer table, .womenContainer table {
    margin-left: auto;
    margin-right: auto;
}
	
.menTable td, .menTable th  {
    padding: 3px;
    border-spacing: 5px;
    border: 1px solid #fff;
}

.womenTable td, .womenTable th  {
    padding: 3px;
    border-spacing: 5px;
    border: 1px solid #fff;
}

.menTable, .womenTable {
    margin-top: 10px; /* Adjust to your liking */
    margin-bottom: 10px; /* Adjust to your liking */
}

.menContainer table a, .womenContainer table a {
    font-family: 'Poppins', sans-serif;
    font-size: small;
    text-decoration: none;
    color: #666;
}
	
.menContainer table input[type="text"], .womenContainer table input[type="text"] {
    width: 80%;  /* Adjust this value as needed */
}

	
@media only screen and (max-width: 992px) {

  /* Media Query for Mobile */
  @media only screen and (max-width: 992px) {
    .quote-box {
      /* display: none; */
    }

    .center {
      margin: 0;
      height: 100%;
      background-color: white;  /* Add this */		
    }
    .radar {
      margin: 0;
      height: 100%;
    }
    body {
      background-color: white;
    }
  }
    .menContainer {
        margin-right: 0;
        margin-bottom: 20px; /* Adjust this value as needed */
    }

    .womenContainer {
        margin-left: 0;
        margin-top: 20px; /* Adjust this value as needed */
    }
</style>	
	
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>    

<script>
  WebFont.load({
    google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
    active: function() {
        sessionStorage.fonts = true;
    }
  });

function goToProfile() {
    var id = document.getElementById('id').value;
    if (id && id.length === 6 && !isNaN(id)) {
        var a = document.createElement('a');
        a.href = 'https://kiss.kelleher-international.com/profile/' + id;
        a.target = '_blank';
        a.click(); 
    } else {
      alert('Please enter a valid 6 digit number.');
    }
  }


  function deleteShortcut(gender, index) {
    // Load the links from the cookie
    let links = JSON.parse(decodeURIComponent(document.cookie.replace(new RegExp('(?:(?:^|.*;\\s*)' + gender + 'links\\s*=\\s*([^;]*).*$)|^.*$'), "$1")));
    // Remove the link at the specified index
    links.splice(index, 1);
    // Save the updated links to the cookie
    document.cookie = gender + 'links=' + JSON.stringify(links) + '; expires=' + new Date(new Date().getTime() + 86400 * 30 * 1000).toUTCString() + '; path=/';
    // Reload the page
    location.reload();
  }
  
  window.onload = function() {
    // Display the contents of the "menlinks" and "womenlinks" cookies
    // Uncomment the lines below to display the cookies
	// alert(decodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)menlinks\s*=\s*([^;]*).*$)|^.*$/, "$1")));
    // alert(decodeURIComponent(document.cookie.replace(/(?:(?:^|.*;\s*)womenlinks\s*=\s*([^;]*).*$)|^.*$/, "$1")));
  }	

</script>
</head>
<body>
	<div class="radar">
	  <br>
	  <div>
		<span class="kiss">K&nbsp;I&nbsp;S&nbsp;S</span><br>
		<strong>K</strong>elleher <strong>I</strong>nternational <strong>S</strong>upport <strong>S</strong>ystem
	  </div>
	  <br>
	  <a href="/home" class="dashboard-link" id="dash-link"></a>
		<h3>ON MY RADAR</h3>
		<div class="genderContainer"> 
		  <!-- Men container -->
		  <div class="menContainer">
			<div style="display: flex;">
			  <div style="flex: 1;">
				<h4>Men</h4>
				<form action="lovelinks5.php" method="post">
				  <input type="hidden" name="gender" value="men">
				  <input type="text" name="profile_id" placeholder="Enter full address bar URL or profile ID#" class="radar-field" style="width: 280px;" required>
				  <input type="submit" value="ADD" class="radar-button">
				</form>

				<table class="menTable">
				  <?php
				  $menLinks = isset($_COOKIE["menlinks"]) ? json_decode($_COOKIE["menlinks"], true) : array();
				  foreach ($menLinks as $index => $menLink) {
					echo "<tr style='width: 360px;'>";
					echo "<td style='width: 60px;'><a href='{$menLink['profile']}'><img src='{$menLink['url']}' height='50' target='_blank'></a></td>";
					echo "<td style='width: 150px; text-align: center; text-decoration: none'><a href='{$menLink['profile']}' style='display: block;' target='_blank'>{$menLink['name']}</a></td>";
					echo "<td style='width: 30px; text-align: center'><a href='{$menLink['profile']}' style='display: block;' target='_blank'>{$menLink['age']}</a></td>";
					echo "<td style='width: 100px; text-align: center'><a href='{$menLink['profile']}' style='display: block;' target='_blank'>{$menLink['city']}</a></td>";
					echo "<td style='width: 20px; text-align: center'><a href='#' onclick='deleteShortcut(\"men\", {$index})'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash' viewBox='0 0 16 16'>
  <path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z'/>
  <path d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z'/>
</svg></a></td>";
					echo "</tr>"; 
				  }
				  ?>
				</table>
				<form action="lovelinks5.php" method="post">
				  <input type="hidden" name="gender" value="men">
				  <input type="hidden" name="clear_all" value="1">
				  <input type="submit" value="CLEAR" class="radar-button">
				</form>        
			  </div>
			</div>
		  </div>

		  <!-- Women container -->
		  <div class="womenContainer">
			<div style="flex: 1;">
			  <h4>Women</h4>
			  <form action="lovelinks5.php" method="post">
				<input type="hidden" name="gender" value="women">
				<input type="text" name="profile_id" placeholder="Enter full address bar URL or profile ID#" class="radar-field" style="width: 280px;" required>
				<input type="submit" value="ADD" class="radar-button">
			  </form>

			  <table class="womenTable">
				<?php
				$womenLinks = isset($_COOKIE["womenlinks"]) ? json_decode($_COOKIE["womenlinks"], true) : array();
				foreach ($womenLinks as $index => $womenLink) {
				  echo "<tr style='width: 360px;'>";
				  echo "<td style='width: 60px;'><a href='{$womenLink['profile']}'><img src='{$womenLink['url']}' height='50' target='_blank'></a></td>";
				  echo "<td style='width: 150px; text-align: center; text-decoration: none'><a href='{$womenLink['profile']}' style='display: block;' target='_blank'>{$womenLink['name']}</a></td>";
				  echo "<td style='width: 30px; text-align: center'><a href='{$womenLink['profile']}' style='display: block;' target='_blank'>{$womenLink['age']}</a></td>";
				  echo "<td style='width: 100px; text-align: center'><a href='{$womenLink['profile']}' style='display: block;' target='_blank'>{$womenLink['city']}</a></td>";
				  echo "<td style='width: 20px; text-align: center'><a href='#' onclick='deleteShortcut(\"women\", {$index})'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash' viewBox='0 0 16 16'>
  <path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z'/>
  <path d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z'/>
</svg></a></td>";
				  echo "</tr>"; 
				}
				?>
			  </table>
			  <form action="lovelinks5.php" method="post">
				<input type="hidden" name="gender" value="women">
				<input type="hidden" name="clear_all" value="1">
				<input type="submit" value="CLEAR" class="radar-button">
			  </form>      
			</div>
		  </div>
		</div>

		
	 
	</div>	

	<div class="center">	
	<br>
		<h3>QUICK LINKS</h3>
		(coming soon) <br><br>
		<h3>random love quote</h3>
 	<div class="quote-box">
		<?php
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://famous-quotes4.p.rapidapi.com/random?category=love&count=1",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"X-RapidAPI-Host: famous-quotes4.p.rapidapi.com",
			"X-RapidAPI-Key: f074265aa0msh4f16e25bd6b9c81p1db692jsn254873bbbcd3"
		  )
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		  echo 'Curl error: ' . $err;
		} else {
		  $data = json_decode($response, true); // convert response to array
		  // Display the first quote and its author
		  $quote = $data[0];
		  echo "<p class='quote'>" . htmlspecialchars($quote['text']) . "</p>";
		  echo "<p class='author'>" . htmlspecialchars($quote['author']) . "</p>";
		}
		?>
	  </div>
	  <br><br>
	  <span style="font-size: 18px;">
		Enter the 6 digit ID# of the profile you want to view:
		<div style="text-align: center; margin-top: 10px;">
		  <form onsubmit="event.preventDefault(); goToProfile();">
			<label for="id" class="input-label"></label>
			<input type="text" id="id" name="id" required placeholder="6 digits" class="input-field" maxlength="6" style="width: 80px;">
			<input type="submit" value="Go to Profile" class="submit-button">
		  </form>
		</div>
	  </span>
		<br>
	</div>	
</body>

</html>
