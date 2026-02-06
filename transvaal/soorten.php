<?php

include("_parts/header.php");
include("_infra/functions.php");

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

//print_r($data);

$soorten = array();
$soortids = array();

foreach ($data as $key => $value) {
	$soortids[$value['taxon']['id']] = $value['taxon']['id'];

	if(!isset($soorten[$value['taxon']['id']])){
		$soorten[$value['taxon']['id']] = array(
			"id" => $value['taxon']['id'],
			"count" => 1,
			"name" => $value['taxon']['name']
		);
		if(isset($value['taxon']['common_name']['name'])){
			$soorten[$value['taxon']['id']]["common_name"] = $value['taxon']['common_name']['name'];
		}
	}else{
		$soorten[$value['taxon']['id']]['count']++;
	}
	
	foreach($value['photos'] as $photo){
		$soorten[$value['taxon']['id']]['fotoos'][] = array(
			"medium" => $photo['medium_url'],
			"attribution" => $photo['attribution']
		);
	}
}

//print_r($soorten);


$sparql = "
  SELECT ?item ?itemLabel ?itemDescription ?inatid (SAMPLE(?afb) AS ?img)  ?wpen ?wpnl WHERE {
    VALUES ?inatid { \"" . implode("\" \"", $soortids) . "\" }
    ?item wdt:P3151 ?inatid .
    optional{
      ?item wdt:P18 ?afb .
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
}
GROUP BY ?item ?itemLabel ?itemDescription ?inatid ?wpen ?wpnl
";

//echo $sparql;
$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$wdinfo = json_decode($json,true)['results']['bindings'];


foreach ($wdinfo as $k => $v) {
	$inatid = $v['inatid']['value'];
	$soorten[$inatid]['wikidata'] = $v;
}

//print_r($soorten);


?>






<div class="container-fluid" id="main">

	<div class="row">

		<div class="col-md-12">
			
			<h1>Big Year 2026: de <?= count($soorten) ?> waargenomen soorten</h1>

			<p class="lead">Dit is het overzicht van alle soorten die we in 2026 in Transvaalwijk hebben gespot. Verder lezen? Klik op de linkjes naar iNaturalist of Wikipedia (🇳🇱 en 🇬🇧)</p>


		</div>

	</div>

	<div class="row" id="soorten">

		<?php

		foreach($soorten as $soort){

			if(isset($soort['common_name'])){
				$titel = $soort['common_name'];
			}elseif (isset($soort['wikidata'])) {
				$titel = $soort['wikidata']['itemLabel']['value'];
			}else{
				$titel = $soort['name'];
			}

			if(isset($soort['wikidata']['img'])) {
				$imgurl = $soort['wikidata']['img']['value'] . "?width=500px";
			}elseif(isset($soort['fotoos'][0])){
				$imgurl = $soort['fotoos'][0]['medium'];
			}

			if(isset($soort['wikidata']['itemDescription'])) {
				$description = $soort['wikidata']['itemDescription']['value'];
			}else{
				$description = "";
			}

			$linkbar = '<a  href="https://www.inaturalist.org/taxa/' . $soort['id'] . '"><img src="_assets/img/inaturalist.png" /></a> ';
			if(isset($soort['wikidata']['wpnl'])) {
				$linkbar .= '<a class="wplink" href="' . $soort['wikidata']['wpnl']['value'] . '"><img src="_assets/img/wikipedia-logo-klein.png" />🇳🇱</a> ';
			}
			if(isset($soort['wikidata']['wpen'])) {
				$linkbar .= '<a class="wplink" href="' . $soort['wikidata']['wpen']['value'] . '"><img src="_assets/img/wikipedia-logo-klein.png" />🇬🇧</a> ';
			}

			?>

			<div class="col-md-3">
				<div class="soortcard">
					<h2><?= $titel ?></h2>

					<img src="<?= $imgurl ?>" />

					<p><?= $description ?></p>

					<em><?= $soort['count'] ?> keer waargenomen</em>

					<div class="linkbar"><?= $linkbar ?></div>
				</div>

			</div>

			<?php

		}

		?>

	</div>

</div>


<?php

include("_parts/footer.php");

?>
