<?php


$sparql = '
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX roar: <https://w3id.org/roar#>
PREFIX schema: <https://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX al: <https://adamlink.nl/geo/address/>
SELECT ?aladr ?loclabel ?p ?pname ?birth ?birthplace ?death ?deathplace WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?loc roar:documentedIn <https://www.joodsmonument.nl/> .
  ?loc schema:address  ?aladr .
  ?loc rdfs:label ?loclabel .
  ?p roar:hasLocation/rdf:value ?loc .
  ?p rdfs:label ?pname .
	';
	if(strlen($params['voornaam']) || strlen($params['achternaam']) || strlen($params['tussenvoegsel'])){

  	$naam = array();
  	if(strlen($params['voornaam'])){ 
  		$naam[] = $params['voornaam'];
  	}
  	if(strlen($params['tussenvoegsel'])){ 
  		$naam[] = $params['tussenvoegsel'];
  	}
  	if(strlen($params['achternaam'])){ 
  		$naam[] = $params['achternaam'];
  	}
  	$searchname = implode(" ",$naam);

  	$sparql .= 'FILTER (bif:contains (?pname, "\'' . $searchname . '\'")) .
				';
  }

  $sparql .= '?p schema:birthDate ?birth .
	?p schema:birthPlace ?birthplace .
	?p schema:deathDate ?death .
	?p schema:deathPlace ?deathplace .
	';

  if(strlen($params['geboortedatum'])){
  	$parts = explode("-",$params['geboortedatum']);
  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
  	$sparql .= 'FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
				';
  }
				 
$sparql .= '}';
  
//echo $sparql;
//die;

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$jmpersoon = array("persondescription"=>"");

		$jmpersoon['label'] = "Joods Monument: " . $rec['pname']['value'];

		$jmpersoon['deeddescription'] = "Laatst bekende adres " . $rec['loclabel']['value'];

		if(isset($rec['birth']) && isset($rec['birthplace'])){
			$jmpersoon['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']) . " in " . $rec['birthplace']['value'];
		}elseif(isset($rec['birth'])){
			$jmpersoon['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']);
		}elseif(isset($rec['birthplace'])){
			$jmpersoon['persondescription'] = "Geboren in " . $rec['birthplace']['value'];
		}

		if(isset($rec['death']) && isset($rec['deathplace'])){
			$jmpersoon['persondescription'] .= ", overleden op " . dutchdate($rec['death']['value']) . " in " . $rec['deathplace']['value'];
		}elseif(isset($rec['death'])){
			$jmpersoon['persondescription'] .= ", overleden op " . dutchdate($rec['death']['value']);
		}elseif(isset($rec['deathplace'])){
			$jmpersoon['persondescription'] .= ", overleden in " . $rec['deathplace']['value'];
		}
		
		$jmpersoon['link'] = $rec['p']['value'];
		
		$jmpersoon['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $jmpersoon;
	}
}

//print_r($points);


?>