<html>
<head>
<link rel="stylesheet" href="styles.css" />
</head>
<body>
<?php

include("../../app3/functions.php");
include("uva.php");
include("commons.php");
include("dijkshoorn.php");
include("nozeman.php");
include("wikidata.php");

include("../../app3/individuals_query.php");

$taxonId = $_GET["taxonId"];

$images = array_merge(
    //commonsImages($taxonId, true),
    uvaImages($taxonId),
    nozemanImages($taxonId),
    dijkshoornImages($taxonId),
    //commonsImages($taxonId)
    wikidataImages($taxonId)
);

$taxonName = '';
$wikiURI='';
foreach($images as $img) {
    if (array_key_exists('taxonLabel', $img)) {
        $taxonName= $img['taxonLabel'];
        if (strstr($img['uri'], 'wikidata')) {
            $wikiURI = $img['uri'];
        }
    }
}

#foreach ($images as $row) {
#    print("<img src='${row['image']}' height='300'/>");
#}
$positions = array(
	"left: 28%; top: 8%",
	"left: 50%; top: 40%",
	"left: 5%; top: 70%",
	"left: 70%; top: 65%",
	"left: 58%; top: -05%",
	"left: 85%; top: 00%",
	"left: -5%; top: 30%",
	"left: 30%; top: 65%",
	"left: 85%; top: 60%",
	"left: 55%; top: 45%"
);
$i = 0;

foreach ($images as $img) { 

	$pos = $positions[$i];
	$i++;

	if($i>8){
		break;
	}
    ?>
	<a href="<?= $img['uri']?>" class="imgcircleholder" style="<?= $pos ?>" title="">
        <div class="circle <?= ($img['from']) ?? ''?>" style="background-image: url(<?= $img['image'] ?>?width=500);z-index:99"></div>
	</a>
<?php }

if (!empty(queryIndividuals($taxonId))) {
    print("<a href='../../app3/taxon.php?taxonid=$taxonId'><div class='imgcircleholder' style='left: -0%; top: 23%'><div class='circle' style='background-image: url(./individuals.png)'></div></div></a>");
}
?>
<div class="contentcircle">
    <h1><a href="<?=$wikiURI?>"><?=ucfirst($taxonName)?></a></h1>
</div>
</body>
</html>
