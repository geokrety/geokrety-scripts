#!/usr/bin/env php

<?php

require_once("jpgraph-3.5.0b1/src/jpgraph.php");
require_once("jpgraph-3.5.0b1/src/jpgraph_line.php");
//require_once("jpgraph/jpgraph.php");
//require_once("jpgraph/jpgraph_line.php");


// Create the graph. These two calls are always required śćńółżź

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$data_human = date("Y-m-d H:i");
$start=strtotime("2007-10-26");
$stop = time()+86400;



// ------------------------------------------------------- GK zakopane i ogółem ------------------------------- //

// geokrety
$i=0;
for ($data = $start; $data <= $stop; $data=$data+86400) {
    $result = mysqli_query($link, "SELECT COUNT(`nr`) FROM `gk-geokrety` WHERE UNIX_TIMESTAMP(`data`) < '$data'");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    $ile['x'][$i] = $i;
    $ile['y'][$i] = $row[0];

    $i++;
}


// zakopane
$i=0;
for ($data = $start; $data <= $stop; $data=$data+86400) {
    $result = mysqli_query($link, "SELECT count( DISTINCT x.id ) FROM (SELECT id, max(DATA ) AS data_ost FROM `gk-ruchy` GROUP BY id) AS x LEFT JOIN `gk-ruchy` AS y ON x.id = y.id WHERE data_ost = y.data AND y.logtype IN ('0', '3') AND UNIX_TIMESTAMP(data_ost) <= '$data'");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    $ile2['x'][$i] = $i;
    $ile2['y'][$i] = $row[0];

    // od razu stosunek zakopanych do całości

    $ile3['y'][$i] = $ile2['y'][$i] / $ile['y'][$i]*100;

    $i++;
}

$ile3['y'][$i-1] = $ile3['y'][$i-2];


$out = $geokrety_www.'/'.$config['wykresy'] . "geokretow.png";

$graph  = new Graph(550, 250, $out);
$graph->SetMargin(45, 45, 10, 10);
$graph->SetScale("linlin");

$graph->SetYScale(0, 'lin');


$graph->SetMarginColor('white');
$graph->SetBox(true, 'black', 1);

$graph->xgrid->Show();

$graph->xaxis->SetTitle("days", 'middle');
$graph->yaxis->SetTitle("geokrets", 'middle');
$graph->title->Set("geokrety.org $data_human");

// Create the linear plot
$lineplot =new LinePlot($ile['y'], $ile['x']);
$lineplot ->SetColor("#a7d940");
$lineplot ->SetWeight(3);

$lineplot2 =new LinePlot($ile2['y'], $ile2['x']);
$lineplot2 ->SetColor('orange');
$lineplot2->SetWeight(3);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->Add($lineplot2);

// dodatkowa Y-skala

$lineplot3 =new LinePlot($ile3['y'], $ile2['x']);
$lineplot3 ->SetColor('silver');
$lineplot3->SetWeight(3);
$graph->AddY(0, $lineplot3);


$lineplot->SetLegend("geokrets total");
$lineplot2 ->SetLegend("geokrets in caches");
$lineplot3 ->SetLegend("% of geokrets in caches");

$graph->legend->Pos(0.15, 0.15, "left", "top");




// Display the graph
$graph->Stroke($out);



unset($ile, $ile2, $ile3);


// ------------------------------------------------------- userzy ------------------------------- //

// userzy
$i=0;
for ($data = $start; $data <= $stop; $data=$data+86400) {
    $result = mysqli_query($link, "SELECT COUNT(`userid`) FROM `gk-users` WHERE UNIX_TIMESTAMP(`joined`) < '$data'");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    $ile['x'][] = $i++;
    $ile['y'][] = $row[0];
}


$out = $geokrety_www.'/'.$config['wykresy'] . "userow.png";

$graph  = new Graph(550, 250, $out);
$graph->SetMargin(45, 10, 10, 10);
$graph->SetScale("linlin");
$graph->SetMarginColor('white');
$graph->SetBox(true, 'black', 1);

$graph->xgrid->Show();

$graph->xaxis->SetTitle("days", 'middle');
$graph->yaxis->SetTitle("users", 'middle');
$graph->title->Set("geokrety.org $data_human");

// Create the linear plot
$lineplot =new LinePlot($ile['y'], $ile['x']);
$lineplot ->SetColor("#a7d940");
$lineplot ->SetWeight(3);
$lineplot->SetLegend("users");

$graph->legend->Pos(0.15, 0.15, "left", "top");

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke($out);

unset($ile);



// ------------------------------------------------------- ruchy ------------------------------- //
$i=0;
for ($data = $start; $data <= $stop; $data=$data+86400) {
    $result = mysqli_query($link, "SELECT COUNT(`ruch_id`) FROM `gk-ruchy` WHERE UNIX_TIMESTAMP(`data_dodania`) < '$data' AND `logtype`!='2'");
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    $ile['x'][] = $i++;
    $ile['y'][] = $row[0];
}


$out = $geokrety_www.'/'.$config['wykresy'] . "ruchow.png";

$graph  = new Graph(550, 250, $out);
$graph->SetMargin(45, 10, 10, 10);
$graph->SetScale("linlin");
$graph->SetMarginColor('white');
$graph->SetBox(true, 'black', 1);

$graph->xgrid->Show();

$graph->xaxis->SetTitle("days", 'middle');
$graph->yaxis->SetTitle("moves", 'middle');
$graph->title->Set("geokrety.org $data_human");

// Create the linear plot
$lineplot =new LinePlot($ile['y'], $ile['x']);
$lineplot ->SetColor("#a7d940");
$lineplot ->SetWeight(3);
$lineplot->SetLegend("moves");

$graph->legend->Pos(0.15, 0.15, "left", "top");

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke($out);

unset($ile);
mysqli_close($link);
?>
