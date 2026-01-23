<?php


$sparql = '
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX schema: <https://schema.org/>
PREFIX adbandb: <https://iisg.amsterdam/vocab/adb-andb/>
PREFIX andb: <https://iisg.amsterdam/id/andb/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX lp: <https://iisg.amsterdam/resource/andb/lp/>
SELECT DISTINCT ?resident ?residentlabel 
	(SAMPLE(?adreslabel) as ?labels) ?begindate ?birth 
	(MIN(?membersince) AS ?membersince) 
	(GROUP_CONCAT(DISTINCT ?occlabel;SEPARATOR=", ") AS ?occlabels) 
	(MAX(?lp) AS ?lp)
	WHERE {
	VALUES ?lp { lp:' . implode(' lp:',$lps) . ' }
  ?adres owl:sameAs ?lp .
  ?adres rdfs:label ?adreslabel .
  ?adres adbandb:houseNumber ?nr .
  optional{
  	?adres adbandb:houseNumberAddition ?add .
  }
  ?residency schema:address ?adres .
  optional{
    ?residency adbandb:duration ?duration .
    ?duration <http://www.w3.org/2006/time#hasBeginning> ?begin .
    ?begin <http://www.w3.org/2006/time#inXSDDate> ?begindate .
  }
  ?resident adbandb:inhabits ?residency .
  ?resident rdfs:label ?residentlabel .
  ';

  if(strlen($params['voornaam'])){
  	$sparql .= '?resident schema:givenName ?givenname . 
				FILTER (bif:contains (?givenname, "\'' . $params['voornaam'] . '\'")) .
				';
  }

  if(strlen($params['tussenvoegsel'])){
  	$sparql .= '?resident schema:additionalName ?prefix . 
				FILTER (bif:contains (?prefix, "\'' . $params['tussenvoegsel'] . '\'")) .
				';
  }

  if(strlen($params['achternaam'])){
  	$sparql .= '?resident schema:familyName ?famname .
  			FILTER (bif:contains (?famname, "\'' . $params['achternaam'] . '\'")) . 
				';
  }

  if(strlen($params['geboortedatum'])){
  	$parts = explode("-",$params['geboortedatum']);
  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
  	$sparql .= '?resident schema:birthDate ?birth . 
				FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
				';
  }

	
	$sparql .= 'optional{
  	?resident schema:birthDate ?birth .
  }
  optional{
    ?resident <http://www.w3.org/ns/org#hasMembership> ?membership .
    ?membership <http://www.w3.org/ns/org#memberDuring> ?membershipduration .
    ?membershipduration <http://www.w3.org/2006/time#hasBeginning> ?memberbegin .
    ?memberbegin <http://www.w3.org/2006/time#inXSDDate> ?membersince .
    ?membership <http://www.w3.org/ns/org#organization> ?org .
    ?org adbandb:occupation ?occ .
    ?occ rdfs:label ?occlabel .
  }
} group by ?resident ?residentlabel ?begindate ?birth
LIMIT 500
';

  
//echo $sparql;
//die;

$endpoint = 'https://api.druid.datalegend.net/datasets/andb/ANDB-ADB-all/services/default/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$diamantwerker = array();

		$diamantwerker['label'] = "ANDB: " . $rec['residentlabel']['value'];

		if(isset($rec['membersince']) && isset($rec['occlabels'])){
			$diamantwerker['deeddescription'] =  "Lid sinds " . dutchdate($rec['membersince']['value']) . ", beroep: " . $rec['occlabels']['value'];
		}elseif(isset($rec['membersince'])){
			$diamantwerker['deeddescription'] = "Lid sinds " . dutchdate($rec['membersince']['value']);
		}elseif(isset($rec['occlabels'])){
			$diamantwerker['deeddescription'] = "Lid van de ANDB, beroep: " . $rec['occlabels']['value'];
		}else{
			$diamantwerker['deeddescription'] = "Lid van de ANDB";
		}

		if(isset($rec['birth']) && isset($rec['labels'])){
			$diamantwerker['persondescription'] = "Geboren op " . dutchdate($rec['birth']['value']) . ", wonende " . $rec['labels']['value'];
		}elseif(isset($rec['labels'])){
			$diamantwerker['persondescription'] = "Wonende " . $rec['labels']['value'];
		}

		$diamantwerker['link'] = "https://diamantbewerkers.nl/en/detail?id=" . $rec['resident']['value'];
		
		$diamantwerker['adresuri'] = str_replace("https://iisg.amsterdam/resource/andb/lp/","https://adamlink.nl/geo/lp/",$rec['lp']['value']);

	  $addressresults[] = $diamantwerker;
	}
}

//print_r($points);


?>