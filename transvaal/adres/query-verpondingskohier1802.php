<?php


$sparql = '
PREFIX adamlink: <https://lod.uba.uva.nl/ATM/Adamlink/graphs/>
PREFIX saadata: <https://id.amsterdamtimemachine.nl/ark:/81741/dataset/saa/> 
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX al: <https://adamlink.nl/geo/address/>
PREFIX roar: <https://w3id.org/roar#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>

SELECT ?aladr ?birth ?litname ?adrstr ?lo ?po ?scan
  FROM saadata:verpondingskohier-1802-1805 
  FROM adamlink:addresses 
	FROM adamlink:districts 
  WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?po roar:hasLocation ?lo .
  ?lo owl:sameAs ?aladr .
  ?lo rdfs:label ?adrstr .
  ?lo roar:onScan ?scan .
  optional{
    ?po schema:birthDate ?birth . 
  }
  ?po pnv:hasName ?pnvname .
  ?pnvname pnv:literalName ?litname . 
	';

  if(strlen($params['voornaam'])){
  	$sparql .= '?pnvname pnv:givenName ?givenname . 
				FILTER (bif:contains (?givenname, "\'' . $params['voornaam'] . '\'")) .
				';
  }

  if(strlen($params['tussenvoegsel'])){
  	$sparql .= '?pnvname pnv:surnamePrefix ?prefix . 
				FILTER (bif:contains (?prefix, "\'' . $params['tussenvoegsel'] . '\'")) .
				';
  }

  if(strlen($params['achternaam'])){
  	$sparql .= '?pnvname pnv:baseSurname ?basesurname . 
				FILTER (bif:contains (?basesurname, "\'' . $params['achternaam'] . '\'")) .
				';
  }

  if(strlen($params['huisnr'])){
  	$sparql .= '?aladr bag:huisnummer "' . $params['huisnr'] . '"^^xsd:integer .
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

		$registratie = array();

		$registratie['label'] = "Verpondingskohier 1802: " . $rec['litname']['value'];

		if(isset($rec['birth']) && isset($rec['adrstr'])){
			$registratie['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']) . ", " . $rec['adrstr']['value'];
		}elseif(isset($rec['adrstr'])){
			$registratie['persondescription'] = "" . $rec['adrstr']['value'];
		}

		$registratie['link'] = $rec['scan']['value'];
		$registratie['link'] .= "?person=" . str_replace("https://ams-migrate.memorix.io/resources/records/","",$rec['po']['value']);
		// https://archief.amsterdam/indexen/deeds/e7cf77d5-1fdc-41d9-b2e8-08c2eb1de351?person=962316bc-2057-1f3a-e053-b784100aab65
		$registratie['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $registratie;
	}
}

//print_r($points);


?>