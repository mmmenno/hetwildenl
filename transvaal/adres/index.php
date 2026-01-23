<?php

include("../_infra/functions.php");

$adressen = json_decode($_GET['adressen'],true);
$params = json_decode($_GET['params'],true);


//print_r($params);

// sometimes (only in diamantwerkers, really), we need to translate locationpoints to addressid
$lps = array();
foreach($adressen as $adres){
    if(substr($adres, 0,1) != "A"){
        $lps[] = $adres;
        if (($key = array_search($adres, $adressen)) !== false) {
            unset($adressen[$key]);
        }
    }
}
if(count($lps) > 0){
  include("query-adresids-with-lps.php");
}

//print_r($adressen);

$addressresults = array(); 

if(isset($params['marktkaarten'])){
    include("query-marktkaarten.php");
}

if(isset($params['beeldbank'])){
    include("query-beeldbank.php");
}

if(isset($params['joodsmonument'])){
    include("query-jm.php");
}

if(isset($params['errformulieren'])){
    include("query-err.php");
}

if(isset($params['register1874'])){
    include("query-register1874.php");
}

if(isset($params['diamantwerkers'])){
    include("query-lps-with-adresids.php");
    include("query-diamantwerkers.php");
}

if(isset($params['verpondingskohier1802'])){
    include("query-verpondingskohier1802.php");
}

if(isset($params['woningkaarten'])){
    include("query-woningkaarten.php");
}

if(isset($params['blauweknoop'])){
    include("query-blauweknoop.php");
}



//print_r($addressresults);





//print_r($addressresults);

echo '<h3>Bij dit adres:</h3>';

foreach ($addressresults as $k => $v) {
  echo '<div class="item">';
  echo '<h3>' . $v['label'] . '</h3>';
  if(isset($v['img']) && strlen($v['img'])){
    echo '<img src="' . $v['img'] . '" />';
  }
  if(isset($v['persondescription']) && strlen($v['persondescription'])){
    echo '<span class="pd-desc">' . $v['persondescription'] . '</span><br />';
  }
  if(isset($v['deeddescription']) && strlen($v['deeddescription'])){
    echo '<span class="dd-desc">' . $v['deeddescription'] . '</span><br />';
  }
  echo '<a target="_blank" class="resourcelink" href="' . $v['link'] . '">link</a>';
  echo '<a target="_blank" class="adreslink" href="' . $v['adresuri'] . '">' . str_replace('https://adamlink.nl/geo/address/','',$v['adresuri']) . '</a>';
  echo "</div>\n";
}

