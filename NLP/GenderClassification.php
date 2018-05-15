<?php
namespace NLP;

Class GenderClassification{
	public function detect($name,$country=null){
		// create curl resource
		$ch = curl_init();
		// set url 
		curl_setopt($ch, CURLOPT_URL, "https://api.genderize.io/?name=".$name);
		// $output contains the output json
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);
		// close curl resource to free up system resources 
		curl_close($ch);
		// {"name":"Baron","gender":"male","probability":0.88,"count":26}

		return json_decode($output, true);
	}	
}