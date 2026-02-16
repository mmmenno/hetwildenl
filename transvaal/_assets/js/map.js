$(document).ready(function() {

    createMap();

});



function createMap(){

    $('#subheader').css("padding-top","0");
    center = [52.3933, 4.6440];
    zoomlevel = 16;
    
    map = L.map('map', {
      center: center,
      zoom: zoomlevel,
      minZoom: 1,
      maxZoom: 20,
      scrollWheelZoom: true,
      zoomControl: false
    });

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    overviewLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OSM</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
      maxZoom: 20,
      minZoom: 0
    }).addTo(map);
    

    //baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //    minZoom: 19,
    //    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    //}).addTo(map);

    get_observations();
    
}

function get_observations(){

    var dt = new Date();
    var dayOfMonth = dt.getDate();

    $.ajax({
        type: 'GET',
        url: 'geojson/geojson-inaturalist.php?day=' + dayOfMonth,
        dataType: 'json',
        data: '',
        success: function(jsonData) {
            if (typeof lps !== 'undefined') {
                map.removeLayer(lps);
            }

            lps = L.geoJson(null, {
                pointToLayer: function (feature, latlng) { 

                    var markertitle = feature.properties.label
                    if(feature.properties.thumb !== undefined){
                        markertitle += "<br /><img style=\"width:40px;\" src=\"" + feature.properties.thumb + "\" />";
                    }

                    return new L.CircleMarker(latlng, {
                        color: "#fff",
                        fillColor: "#c70030",
                        radius:8,
                        weight: 0,
                        opacity: 0.7,
                        fillOpacity: 0.7,
                        clickable: true,
                        title: markertitle
                    });
                },
                style: function(feature) {
                    return {
                        radius: 10,
                        fillColor: getColor(feature.properties),
                        clickable: true
                    };
                },
                onEachFeature: function(feature, layer) {
                    layer.on({
                        mouseover: rollover,
                        click: whenClicked
                    });
                }
            }).addTo(map);

            lps.addData(jsonData).bringToFront();

            map.fitBounds(lps.getBounds());

            var geojsonprops = jsonData['properties'];
            //console.log(geojsonprops);


            
        },
        error: function() {
            console.log('Error loading data');
        }
    });
}


function rollover() {
    var props = $(this)[0].feature.properties;
    //console.log(props)
    this.bindPopup($(this)[0].options.title)
    this.openPopup();
    var self = this;
    setTimeout(function() {
        self.closePopup();
    },1500);
}
  

function whenClicked(){
    
    var keys = Object.keys(lps._layers)
    keys.forEach(function(key){
        lps._layers[key].setStyle({ 
            weight: 0,
            opacity: 0.7,
            fillOpacity: 0.7
        })
    })

    $(this)[0].setStyle({
        weight: 4,
        opacity: 1,
        fillOpacity: 1
    });

    $('#searchresults').show();

    $("#info-waarneming").html('<em>gegevens waarneming ophalen...</em>');

    var props = $(this)[0].feature.properties;
    //console.log(props);
    

    var infoimg = '';
    if(props['thumb'] !== undefined){
        props['mediumimg'] = props['thumb'].replace("square", "medium");
        infoimg += '<img src="' + props['mediumimg'] + '" />';
    }

    var infocontent = '';
    infocontent += '<strong>' + props['label'] + ' (' + props['cat'] + ')' + '</strong><br />';
    infocontent += '<em>waargenomen op ' + props['datum'] + '</em><br />';
    infocontent += '<a href="' + props['uri'] + '">bekijk waarneming op iNaturalist</a>';

    $("#info-waarneming").html(infocontent);
    $("#info-img").html(infoimg);

    $('#searchinfo').hide();

    //$("#info-with-address").load('adres/index.php?adressen=' + JSON.stringify(props['adressen']) + '&params=' +  encodeURIComponent(JSON.stringify(urlparams)));


}


function getColor(props) {

    
    if(props['cat'] == null){
        return '#838385';
    }
    if(props['cat'] == "Insecta"){
        return '#ff8551';
    }
    if(props['cat'] == "Plantae"){
        return '#00ff69';
    }
    if(props['cat'] == "Aves"){
        return '#ff68e9';
    }

    return '#43a0ff';
}
