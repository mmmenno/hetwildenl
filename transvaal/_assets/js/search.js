$(document).ready(function() {

    $("#searchform").on( "submit", function( event ) {
        event.preventDefault();
        $('#searchresults').hide();
        refreshMap();
        $(".btn-primary").blur();
    });

});




function getParams(){

    var parameters = {};

    var fields = $('#searchform').serializeArray();
    $.each( fields, function( key, value ) {
        console.log(value);
        parameters[value['name']] = value['value'];
    });

    var bounds = map.getBounds();
    parameters['bbox'] = bounds['_southWest']['lng'] + ' ' + bounds['_northEast']['lat'];
    parameters['bbox'] += ',' + bounds['_northEast']['lng'] + ' ' + bounds['_northEast']['lat'];
    parameters['bbox'] += ',' + bounds['_northEast']['lng'] + ' ' + bounds['_southWest']['lat'];
    parameters['bbox'] += ',' + bounds['_southWest']['lng'] + ' ' + bounds['_southWest']['lat'];
    parameters['bbox'] += ',' + bounds['_southWest']['lng'] + ' ' + bounds['_northEast']['lat'];

    return parameters;
}

  
function refreshMap(){

    // tell them we're trying
    $("#info-with-address").html('');
    $("#searchinfo").html('<em>adressen binnen dit gebied zoeken...</em>');
    $('#searchinfo').show();
    $('#searchresults').show();

    //var urlparams = $.param(getParams());
    var urlparams = getParams();
    console.log(urlparams);

    $.ajax({
        type: 'GET',
        url: 'geojson/geojson.php',
        dataType: 'json',
        data: urlparams,
        success: function(jsonData) {
            if (typeof lps !== 'undefined') {
                map.removeLayer(lps);
            }

            lps = L.geoJson(null, {
                pointToLayer: function (feature, latlng) { 

                    var markertitle = feature.properties.cnt + ' resultaten bij'
                    if(feature.properties.cnt == 1){
                        var markertitle = feature.properties.cnt + ' resultaat bij'
                    }
                    $.each(feature.properties.labels,function(index,value){
                        markertitle += "<br />" + value;

                    });    

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
                        radius: getSize(feature.properties.cnt),
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

            //map.fitBounds(lps.getBounds());

            var geojsonprops = jsonData['properties'];
            console.log(geojsonprops);


            var infotext = "<strong>" + geojsonprops['nrfound'] + " resultaten</strong>";
            if(geojsonprops['limited']){
                infotext += ', <strong class="warning">zoom in voor meer</strong>';
            }
            infotext += ', klik op een adres om de zoekresultaten daarbij te bekijken.';
            $('#info-with-address').html('');
            $('#searchinfo').html(infotext);
            $('#searchresults').show();
            $('#searchinfo').show();
        },
        error: function() {
            console.log('Error loading data');
        }
    });
}

function getColor(props) {
    return '#9b289c';
}

function showBron() {
    //$('#main').load('bronnen/marktkaarten/over.php');
}

function getSize(d) {
    return  d > 20 ? 8 :
            d > 15 ? 7 :
            d > 10  ? 6 :
            d > 5  ? 5 :
            d > 1 ? 4 :
                    3 ; 
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

    $("#info-with-address").html('<em>gegevens bij adres ophalen...</em>');

    var props = $(this)[0].feature.properties;
    
    urlparams = getParams();
    delete urlparams["bbox"];
    console.log(encodeURIComponent(JSON.stringify(urlparams)));

    $('#searchinfo').hide();

    $("#info-with-address").load('adres/index.php?adressen=' + JSON.stringify(props['adressen']) + '&params=' +  encodeURIComponent(JSON.stringify(urlparams)));


}


