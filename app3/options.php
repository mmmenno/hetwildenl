<?php

$sparql = "
SELECT ?taxon ?taxonLabel (count(?item) as ?aantal) WHERE {
  ?item wdt:P10241 ?taxon .
  #minus { ?item wdt:P625 ?coords }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
group by ?taxon ?taxonLabel
order by ?taxonLabel
limit 1000";

//echo $sparql;
$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$taxons = array();
foreach ($data['results']['bindings'] as $row) {
  $taxons[$row['taxonLabel']['value']] = array(
    "wd" => str_replace("http://www.wikidata.org/entity/","",$row['taxon']['value']),
    "nr" => $row['aantal']['value']
  );
}

ksort($taxons,SORT_NATURAL|SORT_FLAG_CASE);

$options = "";

foreach ($taxons as $k => $v) {
  if(preg_match("/^(http|Q6)/",$k)){
    continue;
  }
	$options .= "<option value=\"" . $v['wd'] ."\">";
	$options .=  $k  . " (" . $v['nr'] . ")</option>\n";
}



?>
