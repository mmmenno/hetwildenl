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
PREFIX al: <https://adamlink.nl/geo/address/>

SELECT ?aladr ?adrstr ?deed 
  FROM saadata:woningkaarten 
  WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?deed saa:isAssociatedWithModernAddress ?saaadr .
  ?saaadr dcterms:title ?adrstr .
  ?saaadr owl:sameAs ?aladr . 
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

		$registratie['label'] = "Woningkaart: " . $rec['adrstr']['value'];

		$registratie['link'] = "https://archief.amsterdam/indexen/deeds/" . str_replace("https://ams-migrate.memorix.io/resources/records/","",$rec['deed']['value']);
		$registratie['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $registratie;
	}
}

//print_r($points);


?>