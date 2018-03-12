#!/usr/bin/env php

<?php
// stat by current country

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

include("jpgraph-3.5.0b1/src/jpgraph.php");
include("jpgraph-3.5.0b1/src/jpgraph_bar.php");

$NOW = date("Y-m-d");


// ---------------------------------------------------------- by year ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-lata.png";

$result = mysqli_query($link, "SELECT YEAR(`data_dodania`), count(`ruch_id`) ile FROM `gk-ruchy`
group by YEAR(`data_dodania`)");
while ($row = mysqli_fetch_array($result)) {
    $datay[]=$row[1];
    $datax[]=$row[0];
}
mysqli_free_result($result);



$graph = new Graph(300, 200);
$graph->SetScale('textlin');
$graph->title->Set('Number of moves , by year');
$graph->xaxis->SetTickLabels($datax);
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);
// ---------------------------------------------------------- by year ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-miesiace.png";

$result = mysqli_query($link, "SELECT MONTH(`data_dodania`), count(`ruch_id`) ile FROM `gk-ruchy`
group by MONTH(`data_dodania`)");
while ($row = mysqli_fetch_array($result)) {
    $datay[]=$row[1];
}
mysqli_free_result($result);



$graph = new Graph(500, 200);
$graph->SetScale('textlin');
$graph->title->Set('Number of moves , by month');
$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);

// ---------------------------------------------------------- by weekday ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-dayofweek.png";

$dnitygodnia[null] = '?';
$dnitygodnia[1] = 'Sun';
$dnitygodnia[2] = 'Mon';
$dnitygodnia[3] = 'Tue';
$dnitygodnia[4] = 'Wed';
$dnitygodnia[5] = 'Thu';
$dnitygodnia[6] = 'Fri';
$dnitygodnia[7] = 'Sat';



$result = mysqli_query($link, "SELECT DAYOFWEEK(`data`), count(`ruch_id`) ile FROM `gk-ruchy`
group by DAYOFWEEK(`data`)");

while ($row = mysqli_fetch_array($result)) {
    $datax[]=$dnitygodnia[$row[0]];
    $datay[]=$row[1];
}
mysqli_free_result($result);



$graph = new Graph(500, 200);
$graph->SetScale('textlin');
$graph->title->Set('Number of moves, by day of week');
//$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
$graph->xaxis->SetTickLabels($datax);
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);


// ---------------------------------------------------------- by system ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-prefix.png";

$sql = "SELECT LEFT(waypoint, 2) as prefix, count(distinct ruch_id) AS ile_typow
FROM `gk-ruchy`
GROUP BY prefix
ORDER BY ile_typow DESC LIMIT 15";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($typ, $ile) = $row;
    if ($typ == "") {
        $typ = "?";
    }
    $datay[]=$ile;
    $datax[]=$typ;
}
mysqli_free_result($result);


$graph = new Graph(500, 200);
$graph->SetScale('textlin');
$graph->title->Set('Moves, by waypoint');
$graph->xaxis->SetTickLabels($datax);
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);




// ---------------------------------------------------------- by lang ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-lang.png";

$sql = "SELECT lang, count( lang ) AS ile_jezykow
FROM `gk-users`
GROUP BY lang
ORDER BY ile_jezykow DESC
LIMIT 25";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($lang, $ile) = $row;
    if ($lang == "") {
        $lang = "?";
    }
    $datay[]=$ile;
    $datax[]=$lang;
}
mysqli_free_result($result);


$graph = new Graph(500, 200);
$graph->SetScale('textlin');
$graph->title->Set('Interface language');
$graph->xaxis->SetTickLabels($datax);
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);

mysqli_close($link);

?>
