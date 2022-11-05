<?php

include("../../app3/functions.php");

# Alle natuurgebieden, kies gebied, etc
if(!isset($_GET['gebied'])){
    $gebiedid = "Q2800398";
}else{
    $gebieden_data = "../../data/natura2000-met-wikidata.csv";
    if (($handle = fopen($gebieden_data, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
              if ($row[0] == $_GET['gebied']){
                $locality = $row[2];
                $gebieds_naam = $row[1];
              }
        }
        fclose($handle);
    }
    $gebiedid = $_GET['gebied'];
}

#echo $locality;


// STAP 1: BOUNDS BEREKENEN
$apiurl = "https://api.biodiversitydata.nl/v2/geo/getGeoJsonForLocality/" . $locality;
$json = file_get_contents($apiurl);
$data = json_decode($json,true);

$n = null;
$e = null;
$s = null;
$w = null;

foreach($data['coordinates'] as $polygon){
    foreach($polygon[0] as $outercoord){
        if($outercoord[0] < $w || $w == null){
            $w = $outercoord[0];
        }
        if($outercoord[0] > $e || $e == null){
            $e = $outercoord[0];
        }
        if($outercoord[1] > $s || $s == null){
            $s = $outercoord[1];
        }
        if($outercoord[1] < $n || $n == null){
            $n = $outercoord[1];
        }
    }
}

$square = array(
    array($w,$s),
    array($e,$s),
    array($e,$n),
    array($w,$n),
    array($w,$s)
);

// nu wkt maken
$coords = array();
foreach($square as $coord){
    $coords[] = $coord[0] . " " . $coord[1];
}
$wkt = "POLYGON((";
$wkt .= implode(",", $coords);
$wkt .= "))";



// STAP 2: MET BOUNDS POLYGOON OCCURENCES UIT GBIF HALEN

// als je ook foto's wilt: &media_type=StillImage toevoegen
$gbifurl = "https://api.gbif.org/v1/occurrence/search?has_coordinate=true&limit=100&";
$gbifurl .= "has_geospatial_issue=false&geometry=" . str_replace(" ","%20",$wkt);

//echo $gbifurl;
$json = file_get_contents($gbifurl);
$data = json_decode($json,true);

// alle afzonderlijke GBIF soort ids in array
$speciesids = array();
$occurrences = array();

foreach ($data['results'] as $rec) {
    if (isset($rec['speciesKey'])) {
        $occurrences[] = array(
            "lat" => $rec['decimalLatitude'],
            "lon" => $rec['decimalLongitude'],
            "speciesKey" => $rec['speciesKey']
        );
        if(!in_array($rec['speciesKey'],$speciesids)){
            $speciesids[] = $rec['speciesKey'];
        }
    }
}

//print_r($occurrences);
//die;


// STAP 3: aan wikidata vragen wat de wikidata ids zijn bij deze gbif ids:

$sparql = "
SELECT ?item ?itemLabel ?gbif WHERE {
  VALUES ?gbif { \"";
$sparql .= implode("\" \"", $speciesids);
$sparql .= "\" }
  ?item wdt:P846 ?gbif .
    SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\".}
}";

//echo $sparql;
$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);
//die;

include("../soorten/dijkshoorn.php");
include("../soorten/uva.php");
$taxon = 'Q25403';
function uvaHasHeritage($taxonId) {
    $gebieden_data = "../../data/QTaxonLabels.csv";
    if (($handle = fopen($gebieden_data, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (strstr($row[0], $taxonId)){
                return true;
            }
        }
        fclose($handle);
    }
}

foreach ($occurrences as $ockey => $ocvalue) {
    foreach ($data['results']['bindings'] as $wdkey => $wdvalue) {
        if($ocvalue['speciesKey'] == $wdvalue['gbif']['value']){
            $taxonId = str_replace("http://www.wikidata.org/entity/","",$wdvalue['item']['value']);
            $occurrences[$ockey]['wikidata'] = $taxonId;
            $occurrences[$ockey]['label'] = $wdvalue['itemLabel']['value'];
            $occurrences[$ockey]['has_heritage'] = 
              #!empty(dijkshoornImages($taxonId));
              !empty(dijkshoornImages($taxonId)) || !empty(uvaHasHeritage($taxonId));
        }
    }
}

//print_r($occurrences);
//die;

$occurrences_json = json_encode($occurrences);
//echo $occurrences_json;
//die;

$gebieds_json_url = "";
$gebieden_data = "../../data/natura2000-met-wikidata.csv";
$options = "";
if (($handle = fopen($gebieden_data, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if ($row[0] == $gebiedid){
            $gebieds_json_url = "https://api.biodiversitydata.nl/v2/geo/getGeoJsonForLocality/" . $row[2];
            $gebieds_naam = $row[1];
            $options .= "<option selected=\"true\" value=\"" . $row[0] ."\">" . $row[1] . "</option>\n";
          }
          else{
            $options .= "<option value=\"" . $row[0] ."\">" . $row[1] . "</option>\n";
          }
    }
    fclose($handle);
}

$gebied_json = file_get_contents($gebieds_json_url);

?>
<html>
<head>
    <title>HetWildeNL - Collectie flora en fauna</title>
    <link href="/style.css" rel="stylesheet">

  <script
    src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>

    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

    <!-- Esri Leaflet -->
    <script src="https://unpkg.com/esri-leaflet@2.2.4/dist/esri-leaflet.js"></script>

    <!-- Proj4 and Proj4Leaflet -->
    <script src="https://unpkg.com/proj4@2.5.0/dist/proj4-src.js"></script>
    <script src="https://unpkg.com/proj4leaflet@1.0.1"></script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<!--    <link rel="stylesheet" href="../../assets/styles.css" />-->

    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            background-image: url('../../assets/bg.png');
            background-position: center; /* Center the image */
            background-repeat: no-repeat; /* Do not repeat the image */
            background-size: cover; /* Resize the background image to cover the entire container */
        }
        h2{
            color: #ba4fbe;
            font-weight: bold;
            font-size: 44px;
            margin-top: 32px;
            text-align: center;
        }
    </style>
</head>
<body>
<h2>Natuurgebied <?= $gebieds_naam ?></h2>

<div id="map" style="height: 100%; margin-bottom:0px; width: 100%; position:fixed;"></div>
<script>
    let map = L.map("map", {center: [31.262218, 34.801472], zoom: 17});
    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", 
        {attribution: '&copy; <a href="http://' + 
        'www.openstreetmap.org/copyright">OpenStreetMap</a>'}
    ).addTo(map);

    var gebied = L.geoJson(<?= $gebied_json ?>, 
      {
        style: { 
          color: 'red',
          fillColor: 'white',
          opacity: 1,
          fillOpacity: 0.3
        }
      }
    ).addTo(map);
    map.fitBounds(gebied.getBounds());

    customCircleMarker = L.CircleMarker.extend({
      options: { 
          wikidata: 'wikidata',
          speciesKey: 'speciesKey',
          label: 'label',
          has_heritage: 'has_heritage'
      }
    });

    var group = L.featureGroup().addTo(map);
    var occurrences = JSON.parse('<?= $occurrences_json ?>');
    occurrences.forEach(function (item, index) {
      console.log(item, index);
      new customCircleMarker(
        [item['lat'], item['lon']], 
        { radius: 15, 
          color: "black", 
          fillColor: item['has_heritage']?"red":"white",
          fillOpacity: item['has_heritage']?1:0.4,
          wikidata: item["wikidata"],
          speciesKey: item['speciesKey'],
          label: item['label'],
          has_heritage: item['has_heritage']
        }
      ).addTo(group);
    });
    group.on("mouseover", function (e) {
      var c = e.layer; // e.target is the group itself.
      var taxon = c.options.label[0].toUpperCase() + c.options.label.substring(1) 
      c.bindPopup(
        //'<img src=plaatje?taxonid="'+c.options.wikidata+'" height=150/>'+
        '<a href="../soorten/soort.php?taxonId='+c.options.wikidata+'">'+taxon+'</a>').openPopup();
    });

</script>
</body>
</html>
