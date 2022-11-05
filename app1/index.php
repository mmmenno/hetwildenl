<?php

$zoomlevel = 10;
$lat = 52;
$lon = 5;
$radius = 10;
$qid = "Q13742779";
$year = 1900;


if(!isset($_GET['gebied'])){
  $gebied = "Q2800398";
}else{
  $gebied = $_GET['gebied'];
}

$gebieds_json_url = "";
$gebieden_data = "../data/natura2000-met-wikidata.csv";
$options = "";
if (($handle = fopen($gebieden_data, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
          if ($row[0] == $gebied){
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

  <link rel="stylesheet" href="assets/css/styles.css" />

</script>

<style>
.leaflet-control-layers {
  display:none;
}

</style>

<!--<p>
Selected: <?= $gebieds_naam ?> <?= $gebied ?>
</p>-->

<form action="index.php" method="get" style="position: absolute; z-index: 10000;">
<select name="gebied" onchange="this.form.submit()">
    <?= $options ?>
</select>
</form>

<!--<div id="map" style="height: 400px; margin-bottom: 24px; width: 98%;"></div>-->

<div id="maptt" style="height: 100%; margin-bottom: 24px; width: 100%; position:fixed;"></div>

<script>
  $(document).ready(function() {
    createTopoTijdReisMap();
  });

  function createTopoTijdReisMap(){
    center = [<?= $lat ?>, <?= $lon ?>];

    var RD = new L.Proj.CRS(
        'EPSG:28992',
        '+proj=sterea +lat_0=52.15616055555555 +lon_0=5.38763888888889 +k=0.9999079 +x_0=155000 +y_0=463000 +ellps=bessel +units=m +towgs84=565.2369,50.0087,465.658,-0.406857330322398,0.350732676542563,-1.8703473836068,4.0812 +no_defs', {
        origin: [-3.05155E7,3.1112399999999993E7],
        resolutions: [3251.206502413005,1625.6032512065026,812.8016256032513,406.40081280162565,203.20040640081282,101.60020320040641, 50.800101600203206,25.400050800101603,12.700025400050801,6.350012700025401,3.1750063500127004,1.5875031750063502,0.7937515875031751,0.39687579375158755,0.19843789687579377,0.09921894843789689,0.04960947421894844]
    });

    var topotijdreislayer = L.tileLayer('https://tiles.arcgis.com/tiles/nSZVuSZjHpEZZbRo/arcgis/rest/services/Historische_tijdreis_<?= $year ?>/MapServer/WMTS/tile/1.0.0/Historische_tijdreis_<?= $year ?>/default/default028mm/{z}/{y}/{x}',
    { WMTS: false, attribution: 'Kadaster (TopoTijdReis <?= $year ?>)' });

    var topotijdreislayer2019 = L.tileLayer('https://tiles.arcgis.com/tiles/nSZVuSZjHpEZZbRo/arcgis/rest/services/Historische_tijdreis_2019/MapServer/WMTS/tile/1.0.0/Historische_tijdreis_2019/default/default028mm/{z}/{y}/{x}',
    { WMTS: false, attribution: 'Kadaster (TopoTijdReis 2019)' });

    maptt = L.map('maptt', {
        crs: RD,
        scrollWheelZoom: true,
        zoomControl: false,
        minZoom: 1,
        maxZoom: 11,
        layers: [topotijdreislayer, topotijdreislayer2019]
    });
    L.control.zoom({
        position: 'bottomright'
    }).addTo(maptt);

    var layerControl = L.control.layers().addTo(maptt);
    layerControl.addBaseLayer(topotijdreislayer2019, "Topotijdreis NU")
    layerControl.addOverlay(topotijdreislayer, "Topotijdreis TOEN")

    //map view still gets set with Latitude/Longitude,
    //BUT the zoomlevel is now different (it uses the resolutions defined in our projection tileset above)
    maptt.setView(center, 11);
    // OR use RD coordinates (28992), and reproject it to LatLon (4326)
    //maptt.setView(RD.projection.unproject(center), 10);

    var gebied = L.geoJson(<?= $gebied_json ?>, 
      {
        style: { 
          color: '#ed62f3',
          weight: 5,
          fillColor: 'white',
          opacity: 1,
          fillOpacity: 1
        }
      }
    ).addTo(maptt);
    maptt.fitBounds(gebied.getBounds());

    function whenClicked(){
      //console.log('click')
      window.location.assign("../app2/gebieden/index.php?gebied=<?= $gebied ?>")
    }

    gebied.on({
        click: whenClicked
      });

    document.body.onkeydown = function(e) {
      if (e.key == " " || e.code == "Space" || e.keyCode == 32) {
        //console.log('down')
        maptt.removeLayer(topotijdreislayer)
      }
    }
    document.body.onkeyup = function(e) {
      if (e.key == " " || e.code == "Space" || e.keyCode == 32) {
        //console.log('up')
        maptt.addLayer(topotijdreislayer)
      }
    }

  }

</script>

