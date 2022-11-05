<?php
function wikidataImages($taxonId) {
    $endpoint = 'https://query.wikidata.org/sparql';
    $sparql = <<<EOD
        SELECT ?image ?itemLabel ?depictedLabelNL 
        WHERE 
        {
          wd:$taxonId wdt:P18 ?image .
          SERVICE wikibase:label {
              bd:serviceParam wikibase:language "nl". 
              wd:$taxonId rdfs:label ?depictedLabelNL .
          }
        }
    EOD;
    $json = getSparqlResults($endpoint,$sparql);
    return array_map(
        function($row) {
            return array(
                "image" => $row['image']['value'],
                "uri" => $row['image']['value'],
                'from' => '',
                'taxonLabel' => $row['depictedLabelNL']['value'],
            );
        },
        json_decode($json,true)['results']['bindings']
    );

}
?>
