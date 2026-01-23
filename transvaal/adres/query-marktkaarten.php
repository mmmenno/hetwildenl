<?php


$sparql = '
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
SELECT ?aladr ?birth ?litname ?adrstr ?deed ?vergunningdatum ?standplaats WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?deed saa:isOrWasAlsoIncludedIn <https://ams-migrate.memorix.io/resources/records/7a89c50a-63cc-083a-e053-b784100a07cb> .
  ?deed saa:isAssociatedWithModernAddress ?adr .
  ?adr a saa:Address .
  ?adr dcterms:title ?adrstr .
  ?adr owl:sameAs ?aladr .
  ?deed rico:hasOrHadSubject ?po . 
  optional{
    ?po saa:relatedPersonObservation/schema:birthDate ?birth . 
  }
  optional{
  	?deed saa:hasSpecification/saa:specificationTextualValue ?standplaats .
  }
  optional{
    ?deed rico:normalizedDateValue ?vergunningdatum .
  }
  ?po saa:relatedPersonObservation/pnv:hasName ?pnvname .
  ?pnvname pnv:literalName ?litname . ';

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

		$marktkaart = array();

		$marktkaart['label'] = "Marktkaart " . $rec['litname']['value'];

		if(isset($rec['vergunningdatum']) && isset($rec['standplaats'])){
			$marktkaart['deeddescription'] = "Vergunning afgegeven op " . dutchdate($rec['vergunningdatum']['value']) . " voor standplaats " . $rec['standplaats']['value'];
		}elseif(isset($rec['vergunningdatum'])){
			$marktkaart['deeddescription'] = "Vergunning afgegeven op " . dutchdate($rec['vergunningdatum']['value']);
		}if(isset($rec['standplaats'])){
			$marktkaart['deeddescription'] = "Vergunning voor standplaats " . $rec['standplaats']['value'];
		}

		if(isset($rec['birth']) && isset($rec['adrstr'])){
			$marktkaart['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']) . ", wonende " . $rec['adrstr']['value'];
		}elseif(isset($rec['adrstr'])){
			$marktkaart['persondescription'] = "Wonende " . $rec['adrstr']['value'];
		}

		$marktkaart['link'] = str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/indexen/deeds/",$rec['deed']['value']);
		
		$marktkaart['adresuri'] = $rec['aladr']['value'];

	  $addressresults[] = $marktkaart;
	}
}

//print_r($points);


?>