<?php

include("functions.php");


// in: gebied Qid
if(!isset($_GET['gebied'])){
	$gebied = "Q3183833"; 			// Eilandspolder
	$gebied = "Q13742779"; 			// Kennemerduinen
	$gebied = "Q1702950"; 			// Nationaal Park De Maasduinen
}else{
	$gebied = $_GET['gebied'];
}





// STAP 1: BOUNDS BEREKENEN
// STAP 1 OPTIE 1: haal polygoon gebied van naturalis api
$localityname = "Eilandspolder%20(Natura%202000)"; // moet je maar net weten, we hebben koppeling naturalis gebieden met qids nodig!
$localityname = "Kennemerduinen%20(PWN)"; // moet je maar net weten, we hebben koppeling naturalis gebieden met qids nodig!
$localityname = "Duinen%20Wijk%20aan%20Zee%20(Landschap%20Noord-Holland)"; // moet je maar net weten, we hebben koppeling naturalis gebieden met qids nodig!
$localityname = "Maasduinen%20(Natura%202000)"; // moet je maar net weten, we hebben koppeling naturalis gebieden met qids nodig!
$localityname = "Ulvenhoutse%20bos%20(Staatsbosbeheer)"; // met grove den!



$apiurl = "https://api.biodiversitydata.nl/v2/geo/getGeoJsonForLocality/" . $localityname;
$json = file_get_contents($apiurl);
$data = json_decode($json,true);

$n = null;
$e = null;
$s = null;
$w = null;

foreach($data['coordinates'] as $polygon){
	foreach($polygon[0] as $outercoord){
		if($outercoord[0] < $w || $w == null){
			$w = $outercoord[0];
		}
		if($outercoord[0] > $e || $e == null){
			$e = $outercoord[0];
		}
		if($outercoord[1] > $s || $s == null){
			$s = $outercoord[1];
		}
		if($outercoord[1] < $n || $n == null){
			$n = $outercoord[1];
		}
	}
}

$square = array(
	array($w,$s),
	array($e,$s),
	array($e,$n),
	array($w,$n),
	array($w,$s)
);

// nu wkt maken
$coords = array();
foreach($square as $coord){
	$coords[] = $coord[0] . " " . $coord[1];
}
$wkt = "POLYGON((";
$wkt .= implode(",", $coords);
$wkt .= "))";

// STAP 1 OPTIE 2: haal centroid + oppervlakte gebied in vierkante meters van wikidata
$sparql = 	'SELECT ?item ?itemLabel ?opp ?oppnorm ?m2 WHERE {
			  VALUES ?item { wd:' . $gebied . ' }
			  ?item p:P2046 ?opp .
			    ?opp psn:P2046 ?oppnorm .
			    ?oppnorm wikibase:quantityAmount ?m2 .
			    SERVICE wikibase:label { bd:serviceParam wikibase:language "nl,en". }
			}';
// de wortel van m2 is lengte en breedte gebied, bij benadering
// zet dit op de 1 of ander manier om naar bounds (probleem is omrekenen meters naar WGS84 coords - via RD?)







// STAP 2: MET BOUNDS POLYGOON OCCURENCES UIT GBIF HALEN

// als je ook foto's wilt: &media_type=StillImage toevoegen
$gbifurl = "https://api.gbif.org/v1/occurrence/search?has_coordinate=true&limit=100&";
$gbifurl .= "has_geospatial_issue=false&geometry=" . str_replace(" ","%20",$wkt);

//echo $gbifurl;
$json = file_get_contents($gbifurl);
$data = json_decode($json,true);

//print_r($data);

// alle afzonderlijke GBIF soort ids in array
$speciesids = array();
foreach ($data['results'] as $rec) {
	if(!in_array($rec['speciesKey'],$speciesids)){
		$speciesids[] = $rec['speciesKey'];
	}
}
//Pettemerduinen (Staatsbosbeheer)print_r($speciesids);







// STAP 3: aan wikidata vragen wat de wikidata ids zijn bij deze gbif ids:

$sparql = "
SELECT ?item ?itemLabel WHERE {
  VALUES ?gbif { \"";
$sparql .= implode("\" \"", $speciesids);
$sparql .= "\" }
  ?item wdt:P846 ?gbif .
    SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\".}
}";

//echo $sparql;
$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$wdtaxons = array();
foreach ($data['results']['bindings'] as $rec) {
	if(!in_array($rec['item']['value'],$wdtaxons)){
		$wdtaxons[] = str_replace("http://www.wikidata.org/entity/","",$rec['item']['value']);
	}
}
//print_r($wdtaxons);







// STAP 4: PRENTEN ZOEKEN WAAR WIKIDATA TAXONS OP STAAN

$sparql = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX mrel: <http://id.loc.gov/vocabulary/relators/>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wd: <http://www.wikidata.org/entity/>

SELECT ?depictsURI ?botanic ?taxonName ?image ?widget 
WHERE {
  VALUES ?depictsURI { wd:";

$sparql .= implode(" wd:", $wdtaxons);

$sparql .= "} .
  
  
  ?botanic dc:subject ?subject ;
         dc:title ?title ;
         mrel:dpc ?depictsURI ;
         foaf:depiction ?imageURL .
  
  FILTER ( REGEX(?subject,\"^botanie\") ) .  # botanic images only
  BIND(REPLACE(STR(?imageURL), \"full/full\", \"full/200,\") AS ?image) .
  
  BIND ('''<div style=\"max-height:unset; width:275px;\">
              <a href=\"{{botanic}}\"><img src=\"{{image}}\"></a><br />
			  <a href=\"{{depictsURI}}\">{{taxonName}}</a>
            </div>'''^^rdf:HTML as ?widget ) .
}";

echo $sparql;





?>