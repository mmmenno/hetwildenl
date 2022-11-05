<?php

function queryIndividuals($taxonId) {
    $sparql = "
      SELECT ?item ?itemLabel ?taxon ?taxonLabel ?afb ?dob ?dod ?wpen ?wpnl WHERE {
        VALUES ?taxon { wd:" . $taxonId . " }
        ?item wdt:P10241 ?taxon .
        optional{
          ?item wdt:P18 ?afb .
        }
        optional{
          ?item wdt:P569 ?dob .
        }
        optional{
          ?item wdt:P570 ?dod .
        }
        optional{
          ?wpen schema:about ?item .
          ?wpen schema:isPartOf <https://en.wikipedia.org/> .
        }
        optional{
          ?wpnl schema:about ?item .
          ?wpnl schema:isPartOf <https://nl.wikipedia.org/> .
        }
        SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
    }";
    
    //echo $sparql;
    $endpoint = 'https://query.wikidata.org/sparql';
    
    $json = getSparqlResults($endpoint,$sparql);
    return json_decode($json,true)['results']['bindings'];
}

?>
