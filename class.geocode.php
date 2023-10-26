<?php
/*! \class GeoCode class.geocode.php "class.geocode.php"
 *  \brief used to render all of the inline editable form elements.
 */
class GeoCode {
	/*! \fn obj __constructor($DB)
		\brief Geocode class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB) {
		$this->db = $DB;
	}
	
	function get_LatLon_Google($street, $city, $state, $zip, $country) {
		$key = 'AIzaSyDdLulIcwNNXGMrqR3jDG604b_AlbTdC_Q';
		//$key = 'AIzaSyDaNa2Zm2bZ-oQ1EJIDdULyLD5CLEosXu8';
		$address = $street.' '.$city.', '.$state.' '.$zip.' '.$country;
		$url = "https://maps.google.com/maps/api/geocode/json?key=".$key."&sensor=false" . 
			"&address=" . trim(urlencode($address));
		//echo $url;			
		$json = file_get_contents($url);
		$data = json_decode($json, TRUE);
		//print_r($data);
		if($data['status']=="OK"){
			//return $data['results'];
			$return['lat'] 	= $data['results'][0]['geometry']['location']['lat'];
			$return['lng'] 	= $data['results'][0]['geometry']['location']['lng'];
			$return['code']	= 200;
			//
			//
			foreach($data['results'][0]['address_components'] as $addressBlock):
				if(in_array('administrative_area_level_1', $addressBlock['types'])) {
					$return['state'] = $addressBlock['short_name'];		
				}
				if(in_array('locality', $addressBlock['types'])) {
					$return['city'] = $addressBlock['long_name'];	
				}
				if(in_array('country', $addressBlock['types'])) {
					$return['country'] = $addressBlock['short_name'];	
				}
			endforeach;
		} else {
			$return['lat'] 	= $data['results'][0]['geometry']['location']['lat'];
			$return['lng'] 	= $data['results'][0]['geometry']['location']['lng'];
			$return['code']	= 401;
			$return['debug'] = $data;
		}
		return $return;
	}
	function get_LatLon_TAM($street, $city, $state, $zip, $country) {
		$url = 'https://geoservices.tamu.edu/Services/Geocode/WebService/GeocoderService_V04_01.asmx';
		$api_key = '984fa4a0976c4572afabade12413e8a8';
		
		$postData = http_build_query(array(
			'apiKey'	=>	$api_key,
			'version'	=>	'4.01',
			'streetAddress'	=>	$street,
			'city'			=>	$city,
			'state'			=>	$state,
			'zip'			=>	$zip,
			'notStore'		=>	true		
		));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		curl_close ($ch);
		$resArray = explode(",", $response);
		if($resArray[6] == 'Unmatchable') {
			$json['code'] = 401;
			$json['lat'] = $resArray[3];
			$json['lng'] = $resArray[4];
		} else {
			$json['code'] = 200;
			$json['lat'] = $resArray[3];
			$json['lng'] = $resArray[4];
		}
		print_r($json);		
	}
	
	
	
	
	
}
?>