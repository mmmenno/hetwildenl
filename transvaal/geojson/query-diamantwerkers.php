<?php


// No Adamlink streets in Druid ANDB, so get that first

if(strlen($_GET['straat'])){
	$sparql = '
	PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
	PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
	PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
	PREFIX hg: <http://rdf.histograph.io/>
	PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
	prefix schema: <https://schema.org/>

	select distinct ?allp where {
	  ?aladr hg:liesIn ?alstreet .
	  ?alstreet skos:altLabel ?streetname . 
	  FILTER (bif:contains (?streetname, "\'' . $_GET['straat'] . '\'")) .
	  ';

	  if(strlen($_GET['huisnr'])){
	  	$sparql .= '?aladr bag:huisnummer "' . $_GET['huisnr'] . '"^^xsd:integer .
					';
	  }
  
	  $sparql .= '?allp schema:geoWithin ?aladr .
	  
	} limit 5000
	';

	//echo $sparql;
	$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

	$json = getSparqlResults($endpoint,$sparql);
	$data = json_decode($json,true);

	$lps = array();

	if(isset($data['results']['bindings'])){
		foreach ($data['results']['bindings'] as $key => $value) {
		  $lps[] = "lp:" . str_replace("https://adamlink.nl/geo/lp/","",$value['allp']['value']);
		}
	}

}

//https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/sparql
//https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql


$sparql = '
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX schema: <https://schema.org/>
PREFIX adbandb: <https://iisg.amsterdam/vocab/adb-andb/>
PREFIX lp: <https://iisg.amsterdam/resource/andb/lp/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?lp ?wkt (GROUP_CONCAT(DISTINCT ?adreslabel;SEPARATOR=",") as ?labels) (count(DISTINCT ?resident) as ?residents) WHERE {
	';

  if(strlen($_GET['straat'])){
  	$sparql .= 'VALUES ?lp { ' . implode(" ",$lps) . '}';
  }
  
  $sparql .= '
  ?adres adbandb:street ?andbstraat .
  ?adres rdfs:label ?adreslabel .
  ?adres owl:sameAs ?lp .
  ?lp geo:hasGeometry/geo:asWKT ?wkt .
  ?residency schema:address ?adres .
  ?resident adbandb:inhabits ?residency .
  ';

  if(strlen($_GET['voornaam'])){
  	$sparql .= '?resident schema:givenName ?givenname . 
				FILTER (bif:contains (?givenname, "\'' . $_GET['voornaam'] . '\'")) .
				';
  }

  if(strlen($_GET['tussenvoegsel'])){
  	$sparql .= '?resident schema:additionalName ?prefix . 
				FILTER (bif:contains (?prefix, "\'' . $_GET['tussenvoegsel'] . '\'")) .
				';
  }

  if(strlen($_GET['achternaam'])){
  	$sparql .= '?resident schema:familyName ?famname .
  			FILTER (bif:contains (?famname, "\'' . $_GET['achternaam'] . '\'")) . 
				';
  }

  if(strlen($_GET['geboortedatum'])){
  	$parts = explode("-",$_GET['geboortedatum']);
  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
  	$sparql .= '?resident schema:birthDate ?birth . 
				FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
				';
  }

	$sparql .= 'bind (bif:st_geomfromtext(xsd:string(?wkt)) as ?x)
  bind (bif:st_geomfromtext("POLYGON((' . $_GET['bbox'] . '))") as ?y)
  FILTER (bif:st_intersects(?x, ?y)) 
} 
GROUP BY ?lp ?wkt limit ' . ($limitperbron + 1) . '
';

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/andb/ANDB-ADB-all/services/default/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);
//echo count($data['results']['bindings']);

if(isset($data['results']['bindings']) && count($data['results']['bindings']) > $limitperbron){
	$limitbereikt = true;
}


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $value) {

	  $adr = str_replace("https://iisg.amsterdam/resource/andb/lp/","",$value['lp']['value']);

	  $labels = explode(",",$value['labels']['value']);
	  for($i=0; $i < count($labels); $i++){
	  	$labels[$i] = trim($labels[$i]);
	  }
	  $ulabels = array_unique($labels);

	  $wkt = $value['wkt']['value'];

	  $lp = str_replace("https://iisg.amsterdam/resource/andb/lp/","",$value['lp']['value']);
	  if(!isset($points[$lp])){
	    $points[$lp] = array(
	      "cnt" => $value['residents']['value'],
	      "labels" => $ulabels,
	      "adressen" => array($adr),
	      "wkt" => $wkt
	    );
	  }else{
	    $points[$lp]['cnt'] = $points[$lp]['cnt'] + $value['residents']['value'];
	    
	    $points[$lp]['labels'] = array_merge($ulabels,$points[$lp]['labels']);
	    $points[$lp]['labels'] = array_unique($points[$lp]['labels']);

	    $points[$lp]['adressen'][] = $adr;
	    $points[$lp]['adressen'] = array_unique($points[$lp]['adressen']);

	  }
	}
}

//print_r($points);


?>