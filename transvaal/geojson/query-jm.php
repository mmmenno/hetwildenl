<?php


$sparql = '
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
PREFIX roar: <https://w3id.org/roar#>
SELECT ?adres ?huisnr ?huisletter (SAMPLE(?loclabel) AS ?label) (COUNT(DISTINCT(?loc)) AS ?nr) (MAX(?wkt) AS ?wkt) (MAX(?lp) as ?lp) WHERE {
  ?loc roar:documentedIn <https://www.joodsmonument.nl/> .
  ?loc schema:address ?adres .
  ?adres bag:huisnummer ?huisnr .
  optional {
    ?adres bag:huisletter ?huisletter .
  }
  ';

  if(strlen($_GET['straat'])){
  	$sparql .= '?adres hg:liesIn ?alstreet .
  			?alstreet skos:altLabel ?streetname . 
				FILTER (bif:contains (?streetname, "\'' . $_GET['straat'] . '\'")) .
				';
  }

  
  if(strlen($_GET['huisnr'])){
  	$sparql .= '?adres bag:huisnummer "' . $_GET['huisnr'] . '"^^xsd:integer .
				';
  }

  $sparql .= '?adres schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt .
  ?loc rdfs:label ?loclabel .
  ?p roar:hasLocation/rdf:value ?loc .
  ';

  if(strlen($_GET['voornaam']) || strlen($_GET['achternaam']) || strlen($_GET['tussenvoegsel'])){

  	$naam = array();
  	if(strlen($_GET['voornaam'])){ 
  		$naam[] = $_GET['voornaam'];
  	}
  	if(strlen($_GET['tussenvoegsel'])){ 
  		$naam[] = $_GET['tussenvoegsel'];
  	}
  	if(strlen($_GET['achternaam'])){ 
  		$naam[] = $_GET['achternaam'];
  	}
  	$searchname = implode(" ",$naam);

  	$sparql .= '?p rdfs:label ?pname .
  							FILTER (bif:contains (?pname, "\'' . $searchname . '\'")) .
				';
  }

  if(strlen($_GET['geboortedatum'])){
  	$parts = explode("-",$_GET['geboortedatum']);
  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
  	$sparql .= '?p schema:birthDate ?birth . 
				FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
				';
  }

  $sparql .= ' bind (bif:st_geomfromtext(xsd:string(?wkt)) as ?x)
  bind (bif:st_geomfromtext("POLYGON((' . $_GET['bbox'] . '))") as ?y)
  FILTER (bif:st_intersects(?x, ?y)) 
} 
GROUP BY ?adres ?huisnr ?huisletter limit ' . ($limitperbron + 1) . '
';

/*
  if(strlen($_GET['voornaam']) || strlen($_GET['achternaam']) || strlen($_GET['tussenvoegsel'])){

  	$naam = array();
  	if(strlen($_GET['voornaam'])){ 
  		$naam[] = $_GET['voornaam'];
  	}
  	if(strlen($_GET['tussenvoegsel'])){ 
  		$naam[] = $_GET['tussenvoegsel'];
  	}
  	if(strlen($_GET['achternaam'])){ 
  		$naam[] = $_GET['achternaam'];
  	}
  	$searchname = implode(" ",$naam);

  	$sparql .= '?bbrec saa:hasCreator ?creator .
							  ?creator saa:hasAgent ?agent .
							  ?agentcontext mem:hasRecord ?agent .
							  ?agentcontext dct:title ?agentrectitle .
							  FILTER (bif:contains (?agentrectitle, "\'' . $searchname . '\'")) .
				';
  }
  
/*
  $sparql .= ' ?bbrec schemahttp:thumbnailUrl ?thumb .
  ?bbrec saa:hasOrHadSubjectAddress ?aladr .
  ';

  if(strlen($_GET['straat'])){
  	$sparql .= '?aladr hg:liesIn ?alstreet .
  			?alstreet skos:altLabel ?streetname . 
				FILTER (bif:contains (?streetname, "\'' . $_GET['straat'] . '\'")) .
				';
  }

  if(strlen($_GET['huisnr'])){
  	$sparql .= '?aladr bag:huisnummer "' . $_GET['huisnr'] . '"^^xsd:integer .
				';
  }
  
  $sparql .= '?aladr rdfs:label ?label .
  ?aladr schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt .
  ';

  $sparql .= ' bind (bif:st_geomfromtext(xsd:string(?wkt)) as ?x)
  bind (bif:st_geomfromtext("POLYGON((' . $_GET['bbox'] . '))") as ?y)
  FILTER (bif:st_intersects(?x, ?y)) 
} 
GROUP BY ?aladr ?label limit ' . ($limitperbron + 1) . '
';
*/

//echo $sparql;
//die;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);
//echo count($data['results']['bindings']);

if(isset($data['results']['bindings']) && count($data['results']['bindings']) > $limitperbron){
	$limitbereikt = true;
}


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $value) {

	  $adr = str_replace("https://adamlink.nl/geo/address/","",$value['adres']['value']);

	  $wkt = $value['wkt']['value'];

	  $lp = str_replace("https://adamlink.nl/geo/lp/","",$value['lp']['value']);
	  if(!isset($points[$lp])){
	    $points[$lp] = array(
	      "cnt" => $value['nr']['value'],
	      "labels" => array($value['label']['value']),
	      "adressen" => array($adr),
	      "wkt" => $wkt
	    );
	  }else{
	    $points[$lp]['cnt'] = $points[$lp]['cnt'] + $value['nr']['value'];
	    
	    $points[$lp]['labels'][] = $value['label']['value'];
	    $points[$lp]['labels'] = array_unique($points[$lp]['labels']);

	    $points[$lp]['adressen'][] = $adr;
	    $points[$lp]['adressen'] = array_unique($points[$lp]['adressen']);

	  }
	}
}

//print_r($points);


?>