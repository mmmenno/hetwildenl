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
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
PREFIX dct: <http://purl.org/dc/terms/>


SELECT ?aladr (MAX(?lpoint) as ?lp) (MAX(?wktspatial) as ?wkt) 
	(GROUP_CONCAT(DISTINCT ?adrstr;SEPARATOR=", ") as ?labels) 
	(count(DISTINCT ?deed) as ?cards)
  FROM saadata:woningkaarten 
  FROM adamlink:addresses 
  FROM adamlink:streets where {
	  ?deed a rt:Deed .
	  ?deed saa:isAssociatedWithModernAddress ?saaadr .
	  ?saaadr dct:title ?adrstr .
	  ?saaadr owl:sameAs ?aladr .
	  ?aladr schema:geoContains ?lpoint .
	  ?lpoint geo:asWKT ?wktspatial .
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

	  // alle queryparameters hieronder zijn onzin, maar je mag ook niks vinden als je op naam zoekt
	  if(strlen($_GET['voornaam']) || strlen($_GET['achternaam']) || strlen($_GET['tussenvoegsel'])){
	  	$sparql .= '?aladr pnv:hasName ?pnvname . 
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
					#FILTER contains(LCASE(?basesurname), "' . strtolower($_GET['achternaam']) . '") . 
					FILTER (bif:contains (?basesurname, "\'' . $_GET['achternaam'] . '\'")) .
					';
	  }

	  if(strlen($_GET['geboortedatum'])){
	  	$parts = explode("-",$_GET['geboortedatum']);
	  	$geboortedatum = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	  	$sparql .= '?aladr schema:birthDate ?birth . 
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
	      "cnt" => $value['cards']['value'],
	      "labels" => explode(",",$value['labels']['value']),
	      "adressen" => array($adr),
	      "wkt" => $wkt
	    );
	  }else{
	    $points[$lp]['cnt'] = $points[$lp]['cnt'] + $value['cards']['value'];
	    
	    $points[$lp]['labels'][] = $value['labels']['value'];
	    $points[$lp]['labels'] = array_unique($points[$lp]['labels']);

	    $points[$lp]['adressen'][] = $adr;
	    $points[$lp]['adressen'] = array_unique($points[$lp]['adressen']);

	  }
	}
}

//print_r($points);


?>