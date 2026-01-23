<?php


$sparql = '
PREFIX saadata: <https://id.amsterdamtimemachine.nl/ark:/81741/dataset/saa/> 
PREFIX adamlink: <https://lod.uba.uva.nl/ATM/Adamlink/graphs/>
PREFIX rt:      <https://ams-migrate.memorix.io/resources/recordtypes/>
PREFIX saa:          <https://data.archief.amsterdam/ontology#>
prefix owl: <http://www.w3.org/2002/07/owl#>
prefix schema: <https://schema.org/>
prefix geo: <http://www.opengis.net/ont/geosparql#>
prefix bif: <http://www.openlinksw.com/schemas/bif#>
prefix xsd: <http://www.w3.org/2001/XMLSchema#>
prefix rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX pnv: <https://w3id.org/pnv#>
PREFIX hg: <http://rdf.histograph.io/>
prefix skos: <http://www.w3.org/2004/02/skos/core#>
prefix roar:		  <https://w3id.org/roar#>
prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#>
prefix hg: <http://rdf.histograph.io/>
prefix bag: <http://bag.basisregistraties.overheid.nl/def/bag#>

SELECT ?aladr (MAX(?lp) as ?lp) (MAX(?wktspatial) as ?wkt) 
	(GROUP_CONCAT(DISTINCT ?adrstr;SEPARATOR=",") as ?labels) 
	(count(DISTINCT ?po) as ?residents)
  FROM saadata:verpondingskohier-1802-1805
  FROM adamlink:addresses 
	FROM adamlink:districts 
	where {
	  ?po a roar:PersonObservation .
	  ?po roar:hasLocation ?lo .
	  ?lo owl:sameAs ?aladr .
	  ?lo rdfs:label ?adrstr .
	  
  ';

  if(strlen($_GET['straat'])){
  	$sparql .= '
	    ?aladr hg:liesIn ?wijk .
	    ?wijk rdfs:label ?wijklabel .
				FILTER (bif:contains (?wijklabel, "\'' . $_GET['straat'] . '\'")) .
				';
  }

  
  if(strlen($_GET['huisnr'])){
  	$sparql .= '?aladr bag:huisnummer "' . $_GET['huisnr'] . '"^^xsd:integer .
				';
  }

  $sparql .= '?aladr schema:geoContains ?lp .
	?lp geo:asWKT ?wktspatial .
  ';

  if(strlen($_GET['voornaam']) || strlen($_GET['achternaam']) || strlen($_GET['tussenvoegsel'])){
  	$sparql .= '?po pnv:hasName ?pnvname .
				';
  }

  if(strlen($_GET['voornaam'])){
  	$sparql .= '?pnvname pnv:givenName ?givenname . 
				FILTER (bif:contains (?givenname, "\'' . $_GET['voornaam'] . '\'")) .
				';
  }

  if(strlen($_GET['tussenvoegsel'])){
  	$sparql .= '?pnvname pnv:surnamePrefix ?prefix . 
				FILTER (bif:contains (?prefix, "\'' . $_GET['tussenvoegsel'] . '\'")) .
				';
  }

  if(strlen($_GET['achternaam'])){
  	$sparql .= '?pnvname pnv:baseSurname ?basesurname . 
				FILTER (bif:contains (?basesurname, "\'' . $_GET['achternaam'] . '\'")) .
				';
  }


  if(strlen($_GET['geboortedatum'])){
  	$parts = explode("-",$_GET['geboortedatum']);
  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
  	$sparql .= '?po schema:birthDate ?birth . 
				FILTER (?birth = "' . $geboortedatum . '"^^xsd:date) .
				';
  }

  $sparql .= ' bind (bif:st_geomfromtext(xsd:string(?wktspatial)) as ?x)
  bind (bif:st_geomfromtext("POLYGON((' . $_GET['bbox'] . '))") as ?y)
  FILTER (bif:st_intersects(?x, ?y)) 
} 
GROUP BY ?aladr limit ' . ($limitperbron + 1) . '
';



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

	  $adr = str_replace("https://adamlink.nl/geo/address/","",$value['aladr']['value']);

	  $wkt = $value['wkt']['value'];

	  $lp = str_replace("https://adamlink.nl/geo/lp/","",$value['lp']['value']);
	  if(!isset($points[$lp])){
	    $points[$lp] = array(
	      "cnt" => $value['residents']['value'],
	      "labels" => array($value['labels']['value']),
	      "adressen" => array($adr),
	      "wkt" => $wkt
	    );
	  }else{
	    $points[$lp]['cnt'] = $points[$lp]['cnt'] + $value['residents']['value'];
	    
	    $points[$lp]['labels'][] = $value['labels']['value'];
	    $points[$lp]['labels'] = array_unique($points[$lp]['labels']);

	    $points[$lp]['adressen'][] = $adr;
	    $points[$lp]['adressen'] = array_unique($points[$lp]['adressen']);

	  }
	}
}

//print_r($points);


?>