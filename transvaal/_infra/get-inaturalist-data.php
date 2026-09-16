<?php

$baseurl = "https://www.inaturalist.org/observations.json?per_page=200&swlat=52.38668471&swlng=4.633698006&nelat=52.39636799&nelng=4.650949527&year=2026";

$allresults = array();


for($i=1; $i<3; $i++){

	$url = $baseurl . "&page=" . $i;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch,CURLOPT_USERAGENT,'Bioblitz Transvaalwijk');
	$headers = [
	    'Accept-Language: nl,en-US'
	];

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec ($ch);
	curl_close ($ch);

	$results = json_decode($response,true);
	$allresults = array_merge($allresults,$results);

}

//print_r($allresults);


$datafile = __DIR__ . "/all.json";
$json = json_encode($allresults);
file_put_contents($datafile, $json);

?>