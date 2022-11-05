<?php

include("functions.php");


// STAP 3: aan wikidata vragen wat de wikidata ids zijn bij deze gbif ids:

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

$options = "";

foreach ($data['results']['bindings'] as $row) {
	$options .= "<option value=\"" . str_replace("http://www.wikidata.org/entity/","",$row['taxon']['value']) ."\">";
	$options .=  $row['taxonLabel']['value']  . " (" . $row['aantal']['value'] . ")</option>\n";
}

?>


<form action="taxon.php" method="get">

<select name="taxonid">
	<?= $options ?>
</select>

<button type="submit">GO</button>

</form>