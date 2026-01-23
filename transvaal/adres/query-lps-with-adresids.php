<?php


$sparql = '
PREFIX schema: <https://schema.org/>
PREFIX lp: <https://adamlink.nl/geo/lp/>
PREFIX adres: <https://adamlink.nl/geo/address/>

SELECT DISTINCT ?lp WHERE {
  VALUES ?adres { adres:' . implode(' adres:',$adressen) . ' }
  ?adres schema:geoContains ?lp .
}';
  
//echo $sparql;
//die;

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

if(!isset($lps)){
	$lps = array();
}

if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$lps[] = str_replace("https://adamlink.nl/geo/lp/","",$rec['lp']['value']);

	}
}

$lps = array_unique($lps);

//print_r($lps);


?>