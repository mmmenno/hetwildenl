<?php

include("_parts/header.php");

if(!isset($_GET['straat'])){
	$_GET['straat'] = "https://adamlink.nl/geo/street/jodenbreestraat/2158";
}
if(!isset($_GET['bron'])){
	//$bron = "diamantbewerkersbond";
}else{
	$bron = $_GET['bron'];
}

?>





<div id="map"></div>
<div id="layerlinks">
	toon historische kaart van  
	<a id="layer1876" href="">1876</a> | 
	<a id="layer1909" href="">1909</a> | 
	<a id="layer1943" href="">1943</a> | 
	<a id="layer1985" href="">1985</a>
	en klik op [spatiebalk] om onderliggende huidige kaart te tonen
</div>


<div id="contentbox">
	
	<h1>De woestijn leeft</h1>

	<p>De Haarlemse Transvaalwijk is bepaald geen groene wijk. De Generaalsbuurt staat zelfs in de top drie van meest versteende buurten van Nederland.</p>

	<p>Toch blijkt: ook hier vinden planten tussen stoeptegels een huis, weten vlinders de bloemen tussen al het steen te vinden. De woestijn leeft!</p>

	<p>In 2026 proberen we een jaar lang zoveel mogelijk soorten in de wijk te ontdekken. Onze waarnemingen verschijnen vanzelf op deze kaart. Zolang het nog 2025 is draaien we proef en zie je waarnemingen van dat jaar.</p>
		
</div>


<div id="searchresults">
	<div id="searchinfo"></div>
	<div id="info-with-address"></div>
</div>


<script src="_assets/js/map.js"></script>

<?php

include("_parts/footer.php");

?>
