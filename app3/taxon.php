<?php

include("functions.php");

include("options.php");

include("individuals_query.php");

//print_r($data);

$imgs = array();
$data = queryIndividuals($_GET['taxonid']);
foreach ($data as $k => $row) {
	if(isset($row['afb']['value'])){
		$imgs[] = array(
			"img" => $row['afb']['value'] . "?width=100",
			"qid" => str_replace("http://www.wikidata.org/entity/","",$row['item']['value']),
			"label" => $row['itemLabel']['value']
		);
	}
	$score = 0;
	if(isset($row['afb']['value'])){
		$score++;
	}
	if(isset($row['dob']['value'])){
		$score++;
	}
	if(isset($row['wpen']['value'])){
		$score++;
	}
	if(isset($row['wpnl']['value'])){
		$score++;
	}
	$data[$k]['score'] = $score;
}

function cmp($a, $b)
{
    if ($a["score"] == $b["score"]) {
        return 0;
    }
    return ($a["score"] < $b["score"]) ? 1 : -1;
}

usort($data,"cmp");
//print_r($data);
?>
<html>
<head>

<link rel="stylesheet" href="styles.css" />


</head>
<body id="taxon">

<form action="taxon.php" method="get">

<select name="taxonid" onchange="this.form.submit()">
	<option value=""> -- kies een taxon -- </option>
	<?= $options ?>
</select>

</form>


<h1>"We are all individuals"</h1>

<h2>van het taxon <?= $data[0]['taxonLabel']['value'] ?></h2>


<br />

<?php foreach ($data as $row) { ?>
	<div class="individual">
		<?php if(isset($row['afb']['value'])){ ?>
				<div class="circle" style="background-image: url(<?= $row['afb']['value'] ?>?width=150);"></div>
		<?php }else{ ?>
				<div class="circle"></div>
		<?php } ?>
		<div class="content">
			<a href="individu.php?individu=<?= str_replace("http://www.wikidata.org/entity/","",$row['item']['value']) ?>"><?= $row['itemLabel']['value'] ?></a><br />
			<?php if(isset($row['dob']['value']) && preg_match("/^[0-9]{4}/",$row['dob']['value'])){ ?>
				<?= substr($row['dob']['value'],0,4) ?>
			<?php }else{ ?>
				?
			<?php } ?>
			-
			<?php if(isset($row['dod']['value']) && preg_match("/^[0-9]{4}/",$row['dod']['value'])){ ?>
				<?= substr($row['dod']['value'],0,4) ?>
			<?php } ?>
			<?php if(isset($row['wpen']['value'])){ ?>
				<a href="<?= $row['wpen']['value'] ?>">🇬🇧</a>
			<?php } ?>
			<?php if(isset($row['wpnl']['value'])){ ?>
				<a href="<?= $row['wpnl']['value'] ?>">🇳🇱</a>
			<?php } ?>
		</div>
	</div>
<?php } ?>
</body>

