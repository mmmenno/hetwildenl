<?php


include("../_infra/functions.php");

//print_r($_GET);
$_GET['straat'] = str_replace("*","",$_GET['straat']); // 'begins with' fucks up endpoint when big bounding box







/* 
    $points is de array waar alle gevonden adressen inkomen alvorens er geojson van te maken.
    Want anders vallen er adamlink adressen uit verschillende bronnen / jaren precies over elkaar heen
    Bovendien wil je misschien iets met het aantal resultaten per punt
*/
$points = array();


/*
    limieten instellen is wat lastig omdat er allemaal verschillende queries zijn
*/
$limit = 5000;
$bronnen = 0;

if(isset($_GET['marktkaarten'])){
    $bronnen++;
}
if(isset($_GET['joodsmonument'])){
    $bronnen++;
}
if(isset($_GET['beeldbank'])){
    $bronnen++;
}
if(isset($_GET['diamantwerkers'])){
    $bronnen++;
}
if(isset($_GET['errformulieren'])){
    $bronnen++;
}
if(isset($_GET['register1874'])){
    $bronnen++;
}
if(isset($_GET['verpondingskohier1802'])){
    $bronnen++;
}
if(isset($_GET['woningkaarten'])){
    $bronnen++;
}
if(isset($_GET['blauweknoop'])){
    $bronnen++;
}

if($bronnen > 0){
    $limitperbron = floor($limit/$bronnen);
}

$limitbereikt = false;


if(isset($_GET['marktkaarten'])){
    include("query-marktkaarten.php");
}

if(isset($_GET['beeldbank'])){
    include("query-beeldbank.php");
}

if(isset($_GET['errformulieren'])){
    include("query-err.php");
}

if(isset($_GET['joodsmonument'])){
    include("query-jm.php");
}

if(isset($_GET['register1874'])){
    include("query-register1874.php");
}

if(isset($_GET['diamantwerkers'])){
    include("query-diamantwerkers.php");
}
if(isset($_GET['verpondingskohier1802'])){
    include("query-verpondingskohier1802.php");
}
if(isset($_GET['woningkaarten'])){
    include("query-woningkaarten.php");
}
if(isset($_GET['blauweknoop'])){
    include("query-blauweknoop.php");
}



$colprops = array(
    "limited" => $limitbereikt,
    "nrfound" => count($points)
);

$fc = array("type"=>"FeatureCollection", "properties"=>$colprops, "features"=>array());
//$fc = array("type"=>"FeatureCollection", "features"=>array());

foreach ($points as $key => $value) {

    //print_r($value);
    
    $adres = array("type"=>"Feature");

    $wkt = $value['wkt'];
    $ll = explode(" ",str_replace(array("POINT(",")"),"",$wkt));
    $adres['geometry'] = array(
        "type" => "Point",
        "coordinates" => array((float)$ll[0],(float)$ll[1])
    );
    $props = array(
        "cnt" => $value['cnt'],
        "labels" => $value['labels'],
        "adressen" => $value['adressen']
    );
    $adres['properties'] = $props;
    $fc['features'][] = $adres;
    
}


//echo $i;
//print_r($streetlist);
//die;

$geojson = json_encode($fc);

header('Content-Type: application/json');
echo $geojson;










?>