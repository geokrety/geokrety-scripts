#!/usr/bin/env php

<?php

require_once("jpgraph-3.5.0b1/src/jpgraph.php");
require_once("jpgraph-3.5.0b1/src/jpgraph_line.php");

//require_once("./jpgraph/jpgraph.php");
//require_once("./jpgraph/jpgraph_line.php");

//include "./jpgraph/jpgraph_scatter.php";
//include "./jpgraph/jpgraph_regstat.php";

$interwal = 15; // co ile, w minutach

// Create the graph. These two calls are always required śćńółżź

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


// selekcja kretow, ktore si� zmieni�y od ostatniego interwa�u



$result0 = mysqli_query(
    $link,
    "SELECT `id`
FROM `gk-ruchy`
WHERE TIMESTAMPDIFF(
MINUTE, timestamp, now( )
) <$interwal
GROUP BY id"
);


// selekcja kretow, ktore si� zmieni�y od ostatniego interwa�u


/*
$result0 = mysqli_query($link, "SELECT `id`
FROM `gk-ruchy`
WHERE `id` > '250'
GROUP BY id"
);
*/

while ($row0 = mysqli_fetch_array($result0)) {
    $droga_=0;
    $id = $row0[0];
    echo "$id\n";

    //$id = 1483;

    $result = mysqli_query($link, "SELECT `droga`, `alt` FROM `gk-ruchy` WHERE (`id`='$id' AND `logtype`!='1' AND `logtype`!='2') ORDER BY `data` ASC");

    while ($row = mysqli_fetch_array($result)) {
        if (($row[1] != '') and ($row[1] > -2000)) {
            $droga_ += $row[0];

            $droga[] = $droga_;
            $alt[] = $row[1];
            //    echo "$row[0]) $droga_ - $row[1]\n";
        }
    }
    mysqli_free_result($result);

    if (empty($droga)) {
        $droga[] = 0;
        $alt[] = "0";
    }

    // -------------- Duzy wykres

    $out = "$geokrety_www/templates/wykresy/$id.png";

    $graph  = new Graph(580, 380, $out);

    $graph->SetMargin(50, 20, 20, 20);
    $graph->SetScale("linlin");


    $graph->SetMarginColor('white');
    $graph->SetBox(true, 'black', 1);

    $graph->xgrid->Show();

    $graph->xaxis->SetTitle("distance [km]", 'middle');
    $graph->yaxis->SetTitle("alt [m]", 'middle');

    // Create the linear plot
    $lineplot =new LinePlot($alt, $droga);
    //$lineplot ->SetColor("#a7d940");
    //$lineplot ->SetColor("#000000");
    //$lineplot ->SetFillColor("#c0c0c0");
    //$lineplot ->SetWeight(1);

    $lineplot->mark->SetType(MARK_SQUARE, 'red', 0.7);

    $graph->Add($lineplot);

    $graph->Stroke($out);



    // -------------- Maly wykres

    $out = "$geokrety_www/templates/wykresy/$id-m.png";



    $graph  = new Graph(100, 100, $out);
    $graph->SetMargin(5, 5, 5, 23);
    $graph->SetScale("linlin");
    $graph->SetMarginColor('white');
    //$graph->SetBox(true,'black',1);

    // Create the linear plot
    $lineplot =new LinePlot($alt, $droga);
    //$lineplot ->SetColor("#a7d940");
    //$lineplot ->SetColor("#000000");
    //$lineplot ->SetFillColor("#c0c0c0");
    $lineplot ->SetWeight(1);

    $graph->Add($lineplot);



    $graph->Stroke($out);



    unset($droga_, $droga, $alt);
}

mysqli_close($link);
?>
