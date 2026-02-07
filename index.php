<?php

include("app3/functions.php");

include("app3/options.php");

$gebieden_data = "data/natura2000-met-wikidata.csv";
$geboptions = "";
if (($handle = fopen($gebieden_data, "r")) !== FALSE) {
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $geboptions .= "<option value=\"" . $row[0] ."\">" . $row[1] . "</option>\n";
    }
    fclose($handle);
}


// nozeman
$vogelen = json_decode(file_get_contents('data/voogelen.json'), true);
$taxonoptions = array();
foreach ($vogelen as $vogel){
	$wikiID = trim($vogel['depicted'], 'http://www.wikidata.org/entity/');
	$label = $vogel['depictedLabelNL'];
	$taxonoptions[$wikiID] = $label;

}

if (($handle = fopen("data/QTaxonLabels.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $wikiID = str_replace("http://www.wikidata.org/entity/", "", $data[0]);
		$label = $data[1];
		$taxonoptions[$wikiID] = $label;
    }
    fclose($handle);
}

asort($taxonoptions);

$taxoptions = "";
foreach ($taxonoptions as $k => $v){
	$taxoptions .= "<option value=\"" . $k ."\">" . $v . "</option>\n";

}


?>
<html>
<head>
	
	<title>Het Wilde NL</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

     <link rel="stylesheet" href="assets/styles.css" />

     <!-- Privacy-friendly analytics by Plausible -->
	<script async src="https://plausible.io/js/pa-eOF38_hcvjNb2uiPVBUuu.js"></script>
	<script>
	  window.plausible=window.plausible||function(){(plausible.q=plausible.q||[]).push(arguments)},plausible.init=plausible.init||function(i){plausible.o=i||{}};
	  plausible.init()
	</script>


	
	
</head>
<body>


<div id="hellothere" class="container">

	<h1>Natuur in onze cultuur</h1>

	<p class="lead">weergaven van <span class="bigger">landschappen</span>, gegevens over <span class="bigger">soorten</span> en niet-menselijke <span class="bigger">individuen</span><br /> in collecties</p>

	<div class="row">
		<div class="col-md-12">
			
			<p>

			</p>

		</div>
	</div>

	<div class="row">
		<div class="col-md-4">

			<img src="assets/tegennatuur.jpg" />
			<p class="sublead">Historische cartografie laat je voorbij je <em>shifting baseline syndrome</em> kijken. Wat is er de afgelopen eeuw rondom natuurgebieden veranderd?</p>

			ga rechtstreeks naar 
			<a href="app1/index.php?gebied=Q1910627">Binnenveld</a> |
			<a href="app1/index.php?gebied=Q2648552">Ulvenhoutse Bos</a> |
			<a href="app1/index.php?gebied=Q13731828">Ilperveld, Varkensland, Oostzanerveld & Twiske</a> |
			<a href="app1/index.php?gebied=Q5317510">Voornes Duin</a>

			<br /><br />
			of kies een gebied uit de lijst
			<form action="app1/" method="get">

			<select name="gebied" onchange="this.form.submit()">
				<option value=""> -- kies een gebied -- </option>
				<?= $geboptions ?>
			</select>

			</form>

			gemaakt met:
			
			<ul>
				<li><a href="https://topotijdreis.nl/">Topotijdreis kaartlagen</a></li>
				<li>Polygonen van <a href="https://api.biodiversitydata.nl/">Naturalis API</a></li>
			</ul>

		</div>
		<div class="col-md-4">

			<img src="assets/soorten.jpg" />

			<p class="sublead">Een systematischer ordening dan de <em>biologische taxonomie</em> is nauwelijks te vinden, maar wordt weinig gebruikt.</p>

			ga rechtstreeks naar
			<a href="app2/soorten/soort.php?taxonId=Q133128">Grove Den</a> |
            <!-- kingfisher, heeft dijkshoorn-images -->
			<a href="app2/soorten/soort.php?taxonId=Q79915">IJsvogel</a> |
			<a href="app2/soorten/soort.php?taxonId=Q25418">Putter</a> |
			<a href="app2/soorten/soort.php?taxonId=Q7224565">Radijs</a>  |
			<a href="app2/soorten/soort.php?taxonId=Q168514">Zomertortel</a> 

			<br /><br />
			of kies een taxon uit de lijst
			<form action="app2/soorten/soort.php" method="get">

			<select name="taxonId" onchange="this.form.submit()">
				<option value=""> -- kies een taxon -- </option>
				<?= $taxoptions ?>
			</select>

			</form>
			

			ga rechtstreeks naar 
			<a href="app2/gebieden/?gebied=Q2796795">Boetelerveld</a> |
			<a href="app2/gebieden/?gebied=Q2114591">Bakkeveense Duinen</a>

			<br /><br />
			of kies een gebied uit de lijst
			<form action="app2/gebieden/" method="get">

			<select name="gebied" onchange="this.form.submit()">
				<option value=""> -- kies een gebied -- </option>
				<?= $geboptions ?>
			</select>

			</form>
			
			

			gemaakt met:
			
			<ul>
				<li><a href="https://www.uvaerfgoed.nl/beeldbank/nl/xsearch?metadata=botanie">Botanische prenten Bijzondere Collecties UvA</a></li>
				<li><a href="https://www.kb.nl/ontdekken-bewonderen/topstukken/nederlandsche-vogelen">Nederlandsche vogelen van Nozeman en Sepp</a></li>
				<li>Waarnemingen via <a href="https://www.gbif.org/">GBIF</a> API</li>
			</ul>

		</div>
		<div class="col-md-4">

			<img src="assets/individuals.jpg" />

			<p class="sublead">Kunnen we loskomen uit onze <em>antropocentrische</em> blik en ook individuen van andere soorten zien?</p>

			<p class="sublead">Brians volgelingen weten: "<em>We are all individuals</em>"!</p>


			ga rechtstreeks naar
			<a href="app3/individu.php?individu=Q115004786">Tanja</a> |
			<a href="app3/individu.php?individu=Q115003515">Herman jr.</a> |
			<a href="app3/individu.php?individu=Q107120526">Wonderboom Elswout</a> |
			<a href="app3/individu.php?individu=Q15943299">Duizendjarige Den</a> |
			<a href="app3/individu.php?individu=Q335860">Abul-Abbas</a> |
			<a href="app3/individu.php?individu=Q2679876">Crystal the Monkey</a>

			<br /><br />
			of bekijk individuen van een taxon uit de lijst
			<form action="app3/taxon.php" method="get">

			<select name="taxonid" onchange="this.form.submit()">
				<option value=""> -- kies een taxon -- </option>
				<?= $options ?>
			</select>

			</form>

			gemaakt met:

			<ul>
				<li>Wikidata's <a href="https://www.wikidata.org/wiki/Property:P10241">P10241</a></li>
				<li><a href="https://commons.wikimedia.org/wiki/Commons:SPARQL_query_service">Wikimedia Commons Query Service</a></li>
				<li>Quotes uit <a href="https://www.delpher.nl/nl/kranten">Delpher</a></li>
			</ul>

		</div>
	</div>

	<div class="row">
		<div class="col-md-6">

			

			
		</div>
		<div class="col-md-6">

			

		</div>
	</div>


</div>

	



</body>
</html>
