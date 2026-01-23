<?php


$sparql = '
PREFIX schema: <https://schema.org/>
PREFIX lp: <https://adamlink.nl/geo/lp/>

SELECT DISTINCT ?adres WHERE {
  VALUES ?lp { lp:' . implode(' lp:',$lps) . ' }
  ?adres schema:geoContains ?lp .
  FILTER (!regex(?adres, "bag","i")) .
}';
  
//echo $sparql;
//die;

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$adressen[] = str_replace("https://adamlink.nl/geo/address/","",$rec['adres']['value']);

	}
}

$adressen = array_unique($adressen);


?>