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
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX al: <https://adamlink.nl/geo/address/>
SELECT ?aladr ?loclabel ?rooms ?boedel ?deed (GROUP_CONCAT(DISTINCT ?pname;SEPARATOR=", ") as ?pnames)  WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?loc roar:documentedIn ?deed .
  ?loc a roar:LocationObservation .
  ?deed schema:isPartOf <https://data.niod.nl/temp-archiefid/093a>.
  ?loc schema:address  ?aladr .
  ?loc rdfs:label ?loclabel .
	OPTIONAL{
		?loc schema:numberOfRooms/rdfs:label ?rooms .
	}
	OPTIONAL{
		?loc schema:description ?boedel .
	}
	OPTIONAL{
	  ?p roar:documentedIn ?deed .
	  ?p a roar:PersonObservation .
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

	  if(strlen($params['geboortedatum'])){ // no people with birthdate in ERR, so just to fail when looked for
	  	$parts = explode("-",$params['geboortedatum']);
	  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	  	$sparql .= '?p schema:birthDate ?birth .
	  							FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
					';
	  }
					 
	$sparql .= '}
	';
$sparql .= '}
GROUP BY ?aladr ?loclabel ?rooms ?boedel ?deed ';
  
//echo $sparql;
//die;

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$erraddress = array("persondescription"=>"");

		if(isset($rec['pnames']['value']) && strlen($rec['pnames']['value'])){
			$erraddress['label'] = "ERR: " . $rec['pnames']['value'];
			$erraddress['deeddescription'] = "Adres: " . $rec['loclabel']['value'] . "";
		}else{
			$erraddress['label'] = "ERR: " . $rec['loclabel']['value'];
			$erraddress['deeddescription'] = "";
		}

		if(isset($rec['rooms']['value'])){
			if(strlen($erraddress['deeddescription'])){
				$erraddress['deeddescription'] .= "<br />";
			}
			$erraddress['deeddescription'] .= "Kamers: " . $rec['rooms']['value'] . "";
		}


		if(isset($rec['boedel']['value'])){
			if(strlen($erraddress['deeddescription'])){
				$erraddress['deeddescription'] .= "<br />";
			}
			$erraddress['deeddescription'] .= "Inboedel: " . $rec['boedel']['value'];
		}


		$erraddress['link'] = $rec['deed']['value'];
		
		$erraddress['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $erraddress;
	}
}

//print_r($points);


?>