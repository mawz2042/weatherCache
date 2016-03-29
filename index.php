<?php

function cacheWeather(){
	
	$apiURL = 'http://meteorology.gov.mv/fetchweather/';
	$locations = array("Male","Hanimadhoo","Kaadehdhoo","Kahdhoo","Gan");
	$cacheTime = 15; //in minutes
	$cacheFile = dirname(__FILE__) . '/weather.data';

	if (file_exists($cacheFile)) {
		$result = file_get_contents($cacheFile);
		$data = json_decode($result,1);

		if ($data['timestamp'] > time() - $cacheTime * 60) {
			$weather_result = $result;
		}
	}
	
	if (!isset($weather_result)) { // cache doesn't exist or is older than 10 mins
	
		if (file_exists($cacheFile)){
			$moreData = json_decode(file_get_contents($cacheFile));
			if($moreData['timestamp'] < time() - $cacheTime * 60) unset($moreData); // Clear cache after 10 min
		}
		$data = array();
		foreach ($locations as $key => $value) {
			$result = file_get_contents($apiURL.$value); // API call
			$result = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $result);
			$data['weather_'.$value] = json_decode($result);
		}
		$data['timestamp'] = time();
			
		$weather_result = json_encode($data);
		
		try {
			$numChars = file_put_contents($cacheFile, $weather_result);
			if(!$numChars)die("file put failed :". $cacheFile);
		}
		catch (Exception $e){
			die('Caught exception: '.$e->getMessage());
		}
	}
	return $weather_result;
}

$weather = cacheWeather();
header('Content-Type: application/json');
echo $weather;

?>