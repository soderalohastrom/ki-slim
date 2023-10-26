<?php
  $startTime = microtime(true); // start timer

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

  $quoteText = '';
  $quoteAuthor = '';
  if ($err) {
      $quoteText = 'Curl error: ' . $err;
  } else {
      $data = json_decode($response, true); // convert response to array
      $quote = $data[0];
      $quoteText = htmlspecialchars($quote['text']);
      $quoteAuthor = htmlspecialchars($quote['author']);
  }

  $colors = ['#FFD1DC', '#A2CFFE', '#B5A9D4', '#FFFBA1', '#FFABAB', '#A1FFD5', '#CEA2FD', '#FFDAC1', '#FFAAA5', '#EEEEEE'];
  $randomColor = $colors[array_rand($colors)];

  $endTime = microtime(true); // end timer
  $executionTime = ($endTime - $startTime); // calculate execution time
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
    a:hover {
      color: white;
      text-decoration: none;
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
    height: 240px;
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
  .kisssm {
    font-size: 14px;
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
    height: 42px;
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
  </script>
</head>
<body>
    <div class="radar">
      <br>
      <div>
        <span class="kiss">K&nbsp;I&nbsp;S&nbsp;S</span><br>
        <span class="kisssm">K</span>elleher <span class="kisssm">I</span>nternational <span class="kisssm">S</span>upport <span class="kisssm">S</span>ystem
      </div>
      <br>      <a href="/home" class="dashboard-link" id="dash-link"></a>




		<h3>random love quote</h3>
        
        <div class="quote-box">
          <span class="quote"><?php echo $quoteText; ?></span>
          <span class="author"><?php echo $quoteAuthor; ?></span>
        </div>

        
        <br>		<h4>ANNOUNCEMENTS - QUICK LINKS</h4>
		(coming soon - Ideas to Kimberly) <br><br>
        
 	<p><strong>This page loaded in <?php echo round($executionTime, 2); ?> seconds</strong><br>
   <span style="font-size: 11px;"> (Avg Dashboard Page load ~ 20 seconds)</span><br>

</body>
</html>