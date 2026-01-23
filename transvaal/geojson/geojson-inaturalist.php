<?php


include("../_infra/functions.php");


// cached geojon or newly created?




// create new geojon
// get data from inaturalist api

// build query
$url = "https://www.inaturalist.org/observations.json?per_page=200";
$url .= "&swlat=52.38668471";
$url .= "&swlng=4.633698006";
$url .= "&nelat=52.39636799";
$url .= "&nelng=4.650949527";
$url .= "&year=2026";


//echo $url;

$json = getInaturalistResults($url);
$data = json_decode($json,true);







$colprops = array(
);

$fc = array("type"=>"FeatureCollection", "properties"=>$colprops, "features"=>array());
//$fc = array("type"=>"FeatureCollection", "features"=>array());


foreach ($data as $key => $obs) {
 

    //print_r($obs);
    
    $observation = array("type"=>"Feature");

    $observation['geometry'] = array(
        "type" => "Point",
        "coordinates" => array($obs['longitude'],$obs['latitude'])
    );
    $props = array(
        "cat" => $obs['iconic_taxon_name'],
        "label" => $obs['species_guess'],
        "datum" => date_format(date_create($obs['time_observed_at']),"d-m-Y"),
        "uri" => $obs['uri']
    );
    if(isset($obs['photos'][0]['thumb_url'])){
        $props['thumb'] = $obs['photos'][0]['square_url'];
    }
    $observation['properties'] = $props;
    $fc['features'][] = $observation;
    
}


//echo $i;
//print_r($streetlist);
//die;

$geojson = json_encode($fc);

header('Content-Type: application/json');
echo $geojson;










?>