<?php

# for example Q79915

function nozemanImages($taxonId) {
    $birds = json_decode(file_get_contents('../../data/voogelen.json'), true);
//var_dump($birds);
    $filtered = array_filter(
        $birds,
        function($t) use ($taxonId) {
            return $t['depicted'] == "http://www.wikidata.org/entity/$taxonId";
        }
    );
    //print_r($filtered);
    return array_map(
        function($row) {
            return array(
                "image" => $row['image'],
                'taxonName' => $row['taxonName'],
                'taxonLabel' => $row['depictedLabelNL'],
                'uri' => $row['depicted'],
                'from' => 'nozeman'
            );
        },
        $filtered
    );
}
