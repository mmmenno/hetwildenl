<?php


$sparql = '
PREFIX mem: <http://memorix.io/ontology#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX bif: <http://www.openlinksw.com/schemas/bif#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX schema: <https://schema.org/>
PREFIX schemahttp: <http://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX al: <https://adamlink.nl/geo/address/>
PREFIX rt: <https://ams-migrate.memorix.io/resources/recordtypes/>

SELECT ?aladr ?bbrec ?thumb ?doctype ?creationdate ?title ?agentrectitle WHERE {
  VALUES ?aladr { al:' . implode(' al:',$adressen) . ' }
  ?bbrec a rt:Image .
  ?bbrec rico:title ?title .
  ?bbrec rico:creationDate/rico:textualValue ?creationdate .
  ?bbrec rico:hasDocumentaryFormType/skos:prefLabel ?doctype .
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

  	$sparql .= '?bbrec saa:hasCreator ?creator .
							  ?creator saa:hasAgent ?agent .
							  ?agentcontext mem:hasRecord ?agent .
							  ?agentcontext dct:title ?agentrectitle .
							  FILTER (bif:contains (?agentrectitle, "\'' . $searchname . '\'")) .
				';
  }else{
  	$sparql .= 'optional{
  							?bbrec saa:hasCreator ?creator .
							  ?creator saa:hasAgent ?agent .
							  ?agentcontext mem:hasRecord ?agent .
							  ?agentcontext dct:title ?agentrectitle .
				}
				';
  }
  
  $sparql .= ' ?bbrec schemahttp:thumbnailUrl ?thumb .
  ?bbrec saa:hasOrHadSubjectAddress ?aladr .
}';
  
//echo $sparql;
//die;

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);


if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $key => $rec) {

		$afb = array();

		$afb['label'] = $rec['title']['value'];
		$afb['img'] = $rec['thumb']['value'];

		if(isset($rec['creationdate'])){
			$afb['deeddescription'] = $rec['creationdate']['value'];
		}

		if(isset($rec['agentrectitle']) && isset($rec['doctype'])){
			$afb['persondescription'] = $rec['doctype']['value'] . " van " . $rec['agentrectitle']['value'];
		}elseif(isset($rec['agentrectitle'])){
			$afb['persondescription'] = "vervaardiger: " . $rec['agentrectitle']['value'];
		}elseif(isset($rec['doctype'])){
			$afb['persondescription'] = $rec['doctype']['value'];
		}

		$afb['link'] = str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/beeldbank/detail/",$rec['bbrec']['value']);
		
		$afb['adresuri'] = $rec['aladr']['value'];

		//print_r($afb);

	  $addressresults[] = $afb;
	}
}



?>