<?php
	//AIzaSyDaE-CPE34vIEf7DNTmW1ywlfvZqjLHPg8
	$query = 'Nikita Platonenko';
	$url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".urlencode($query);
	$body = file_get_contents($url);
	$json = json_decode($body);
	var_dump($json);
?>