<?php

# Pagina voor alle soorten (vogels - bomen )

$vogelen = json_decode(file_get_contents('../../data/voogelen.json'), true);

?>
<html>
<head>
    <title>HetWildeNL - Collectie flora en fauna</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="/style.css" rel="stylesheet">
</head>
<body>
<div id="hellothere" class="container">

    <h1>Erfgoedcollecties en de natuurlijke wereld</h1>
    <p>
        <a href="soort.php?taxonId=Q14683">Huismus</a>
        <a href="soort.php?taxonId=Q133128">Grove den</a>
    </p>

    <div class="row">
        <div class="col-md-4">
            <h2><a href="https://www.wikidata.org/wiki/Q19361289">Nederlandsche voogelen</a></h2>
            <ul>
                <?php
                $dummy = '';
                foreach ($vogelen as $vogel):
                    if ($dummy === $vogel['depicted']) {
                        continue;
                    }
                    $dummy = $vogel['depicted'];

                    $label = $vogel['depictedLabelNL'];
                    $wikiID = trim($vogel['depicted'], 'http://www.wikidata.org/entity/');
                    ?>
                    <li><a href="soort.php?taxonId=<?= $wikiID ?>"><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-4">
            <h2><a href="https://www.wikidata.org/wiki/Q19361289">UVA UB collectie</a></h2>
        </div>
        <div class="col-md-4">
            <h2><a href="https://www.wikidata.org/wiki/Q19361289">Rijksmuseum vogels</a></h2>
        </div>
    </div>

</body>
</html>
