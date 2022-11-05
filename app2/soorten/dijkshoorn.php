<?php

# for example Q79915
function dijkshoornImages($taxonId) {
    $sparql = "
    PREFIX owl: <http://www.w3.org/2002/07/owl#>
    PREFIX oa: <http://www.w3.org/ns/oa#>
    SELECT DISTINCT ?taxon ?image ?taxonName
    WHERE {
      ?cho oa:hasBody ?taxonOrg .
      ?taxonOrg owl:sameAs ?taxon .
      ?taxonOrg <http://lod.taxonconcept.org/ontology/txn.owl#scientificName> ?taxonName .
      ?cho oa:hasTarget/oa:hasSource ?image .
      FILTER(regex(?image, 'ggpht.com', 'i'))
    }
    ";
    $endpoint = "https://api.data.netwerkdigitaalerfgoed.nl/datasets/ivo/rma-dijkshoorn/services/rma-dijkshoorn/sparql";
    $json = getSparqlResults($endpoint,$sparql);
    #print($json);
    $filtered = array_filter(
        json_decode($json,true)['results']['bindings'],
        function($t) use ($taxonId) {
            return $t['taxon']['value'] == "http://www.wikidata.org/entity/$taxonId";
        }
    );
    #print($filtered);
    return array_map(
        function($row) {
            return array(
                "image" => $row['image']['value'],
                "uri" => $row['image']['value'],
                'from' => 'dijkshoorn'
            );
        },
        $filtered
    );
}
