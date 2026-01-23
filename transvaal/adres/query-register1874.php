<?php


$sparql = '
PREFIX saadata: <https://id.amsterdamtimemachine.nl/ark:/81741/dataset/saa/> 
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX al: <https://adamlink.nl/geo/address/>
PREFIX pnv: <https://w3id.org/pnv#>

SELECT ?aladr ?birth ?litname ?streetname ?huisnr ?deed ?po 
  FROM saadata:bevolkingsregister-1874-1893 
  WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?deed saa:isAssociatedWithModernAddress ?saaadr .
  ?saaadr saa:streetTextualValue ?streetname .
  ?saaadr saa:houseNumber ?huisnr .
  ?saaadr owl:sameAs ?aladr .
  ?deed rico:hasOrHadSubject ?po . 
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

		$registratie = array();

		$registratie['label'] = "Register 1874-93: " . $rec['litname']['value'];

		if(isset($rec['birth']) && isset($rec['streetname'])){
			$registratie['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']) . ", wonende " . $rec['streetname']['value'] . " " . $rec['huisnr']['value'];
		}elseif(isset($rec['streetname'])){
			$registratie['persondescription'] = "Wonende " . $rec['streetname']['value'] . " " . $rec['huisnr']['value'];
		}

		$registratie['link'] = str_replace("https://ams-migrate.memorix.io/resources/records/temp-","https://archief.amsterdam/indexen/deeds/",$rec['deed']['value']);
		$registratie['link'] .= "?person=" . str_replace("https://ams-migrate.memorix.io/resources/records/","",$rec['po']['value']);
		// https://archief.amsterdam/indexen/deeds/e7cf77d5-1fdc-41d9-b2e8-08c2eb1de351?person=962316bc-2057-1f3a-e053-b784100aab65
		$registratie['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $registratie;
	}
}

//print_r($points);


?>