<?php

error_reporting(E_ALL ^ E_NOTICE);  

include("functions.php");


include("options.php");



// Wikidata info van dit individu ophalen

$sparql = "
SELECT ?item ?itemLabel ?itemDescription ?taxon ?taxonLabel ?dob ?wpcolen ?dod ?img ?work ?workLabel ?imbdId ?col ?colLabel ?wpen ?wpnl ?wpworken WHERE {
  VALUES ?item { wd:" . $_GET['individu'] . " }
  ?item wdt:P10241 ?taxon .
  OPTIONAL {
    ?item wdt:P569 ?dob .
  }
  OPTIONAL {
    ?item wdt:P570 ?dod .
  }
  OPTIONAL {
    ?item wdt:P18 ?img .
  }
  optional {
    ?item wdt:P1441 ?work .
    optional {
	    ?work wdt:P345 ?imbdId .
	  }
	  optional{
	    ?wpworken schema:about ?work .
	    ?wpworken schema:isPartOf <https://en.wikipedia.org/> .
	  }
  }
  optional {
    ?item wdt:P195 ?col .
	  optional{
	    ?wpcolen schema:about ?col .
	    ?wpcolen schema:isPartOf <https://en.wikipedia.org/> .
	  }
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
";

//echo wpcolenecho $sparql;
//die;
$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$individu = $data['results']['bindings'][0];
//print_r($data);

$indimgs = array();

if(isset($individu['img']['value'])){
	$indimgs[] = array(
		"image" => $individu['img']['value']
	);

}

// any imgs from commons?

$json = file_get_contents("../data/imgs-individuals.json");
$data = json_decode($json,true);


foreach ($data as $k => $v) {
	if($v['item'] == "http://www.wikidata.org/entity/" . $_GET['individu']){
		if(isset($individu['img']['value']) && $v['image']==$individu['img']['value']){
			continue;
		}
		$indimgs[] = $v;
	}
}
//print_r($indimgs);



// en is er nog een delpher quote?

$json = file_get_contents("../data/delpherquotes.json");
$delpherdata = json_decode($json,true);

$delpher = array();
foreach ($delpherdata as $key => $value) {
	if($value['individu'] == $_GET['individu']){
		$delpher = $value;
	}
}

//print_r($delpher);

?>
<html>
<head>
<link rel="stylesheet" href="styles.css" />


</head>
<body id="individu">


<div class="menu">
	<a href="../">&lt; start</a> | 
	<a href="taxon.php?taxonid=<?= str_replace("http://www.wikidata.org/entity/","",$individu['taxon']['value']) ?>">&lt; alle individuen van het taxon <?= $individu['taxonLabel']['value'] ?></a>
</div>


<div class="contentcircle">

<?php if(isset($individu['wpnl']['value'])){ ?>
	<a href="<?= $individu['wpnl']['value'] ?>">🇳🇱</a>
<?php } ?>

<?php if(isset($individu['wpen']['value'])){ ?>
	<a href="<?= $individu['wpen']['value'] ?>">🇬🇧</a>
<?php } ?>

<h1><a href="<?= $individu['item']['value'] ?>"><?= $individu['itemLabel']['value'] ?></a></h1>

<p>
	<?= $individu['itemDescription']['value'] ?><br />
	
	<?php if(isset($individu['dob']['value'])){ ?>
		🍼 <?= preg_replace("/^0/","",substr($individu['dob']['value'],0,4)) ?>
	<?php } ?>

	<?php if(isset($individu['dod']['value'])){ ?>
		 🪦 <?= preg_replace("/^0/","",substr($individu['dod']['value'],0,4)) ?>
	<?php } ?>

</p>
	
<?php if(isset($individu['col']['value'])){ ?>
in collectie van <br /><a target="_blank" href="<?= $individu['wpcolen']['value'] ?>"><?= $individu['colLabel']['value'] ?></a><br />
<?php } ?>


<?php if(isset($individu['wpworken']['value'])){ ?>
komt voor in <br /><a target="_blank" href="<?= $individu['wpworken']['value'] ?>"><?= $individu['workLabel']['value'] ?></a><br />
<?php } ?>
	
<?php if(isset($individu['imbdId']['value'])){ ?>
<a target="_blank" href="https://www.imdb.com/title/<?= $individu['imbdId']['value'] ?>"><img style="width:38px;" src="https://upload.wikimedia.org/wikipedia/commons/6/69/IMDB_Logo_2016.svg" /></a><br />
<?php } ?>
	


</div>


<?php 
	$positions = array(
		"left: 55%; top: 40%",
		"left: 35%; top: 5%",
		"left: 65%; top: -10%",
		"left: 20%; top: 45%",
		"left: -3%; top: 60%",
		"left: 75%; top: 70%",
		"left: 35%; top: 70%",
		"left: 80%; top: 20%",
		"left: 55%; top: 90%",
		"left: 15%; top: 95%"
	);
	$i = 0;
	foreach ($indimgs as $img) { 

		$pos = $positions[$i];
		$i++;

		if($i>8){
			break;
		}

	?>
	<div class="imgcircleholder" style="<?= $pos ?>">
		<div class="circle" style="background-image: url(<?= $img['image'] ?>?width=500);"></div>
	</div>
<?php } ?>



<?php if(count($delpher)){ ?>
	<div class="quotecircle">
		<p class="quote">&ldquo;<?= $delpher['tekst'] ?>&rdquo;</p>
		<p class="krant"><a href="<?= $delpher['artikel'] ?>" target="_blank"><?= $delpher['krant'] ?>, <?= $delpher['datum'] ?></a></p>
	</div>
<?php } ?>


</body>
</html>
