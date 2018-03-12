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




// ---------------------------------------------------------- by system in DATABASE---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "hist-prefix-wpt.png";


$prefiksy["GD"]="GD";
$prefiksy["OC"]="OC";
$prefiksy["OX"]="OX";
$prefiksy["OP"]="OP";
$prefiksy["TP"]="TP";
$prefiksy["OB"]="OB";
$prefiksy["TR"]=".su";
$prefiksy["EV"]=".su";
$prefiksy["GA"]="GA";
$prefiksy["vi"]=".su";
$prefiksy["VI"]=".su";
$prefiksy["GC"]="GC";
$prefiksy["MS"]=".su";
$prefiksy["EX"]=".su";
$prefiksy["WPG"]="WP";
$prefiksy["OU"]="OU";
$prefiksy["OK"]="OK";
$prefiksy["GR"]="GR";
$prefiksy["MV"]=".su";
$prefiksy["RH"]="RH";
$prefiksy["OZ"]="OZ";
$prefiksy["GE"]="GE";
$prefiksy["OS"]="OS";


$sql = "SELECT LEFT( waypoint, 2 ) AS prefix, count(distinct waypoint ) AS ile, MAX(timestamp) as last_updated
FROM `gk-waypointy`
GROUP BY prefix
ORDER BY ile DESC
LIMIT 30";

$result = mysqli_query($link, $sql);

$TRESC = "<table><tr><td>prefix</td><td>counts</td><td>last updated</td></tr>\n";

while ($row = mysqli_fetch_row($result)) {
    list($prefix, $ile, $last_updated) = $row;

    echo "*** $prefix $ile " . $systems[$prefiksy["$prefix"]]['ile'] . "\n";
    $systems[$prefiksy["$prefix"]]['ile']=$systems[$prefiksy["$prefix"]]['ile']+$ile;
    //    $systems[$prefiksy["$prefix"]]['last_updated']=max($last_updated, $systems[$prefiksy["$prefix"]]['last_updated']);
    $systems[$prefiksy["$prefix"]]['last_updated']=max($last_updated, $systems[$prefiksy["$prefix"]]['last_updated']);
}
mysqli_free_result($result);

foreach ($systems as $system => $value) {
    $datay[]=$value['ile'];
    $datax[]=$system;
    $TRESC .= "<tr><td>$system</td><td>" . $value['ile'] . "</td><td>" . $value['last_updated'] . "</td></tr>\n";
}


$TRESC .= "</table>";
file_put_contents("$out.html", $TRESC);

$graph = new Graph(500, 200);
$graph->SetScale('textlin');
$graph->title->Set('Waypoints in wpt database');
$graph->xaxis->SetTickLabels($datax);
$graph->SetMargin(50, 20, 20, 20);
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$graph->Add($bplot);

$graph->Stroke($out);

unset($datax, $datay);


// ---------------------------------------- zgrupowane po waypointach ----------------- //
// ---------------------------------------------------------- by system in DATABASE---------------------------------- //


mysqli_close($link);


?>
