#!/usr/bin/env php

<?php
// stat by current country

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


include("jpgraph-3.5.0b1/src/jpgraph.php");
include("jpgraph-3.5.0b1/src/jpgraph_pie.php");

$NOW = date("Y-m-d");



function rysuj_pie($data, $lbl, $title, $out)
{

// A new pie graph
    $graph = new PieGraph(450, 350, 'auto');
    $theme_class= new VividTheme;
    $graph->SetTheme($theme_class);

    // Setup title
    $graph->title->Set($title);
    $graph->SetBox(true);

    $p1 = new PiePlot($data);
    $p1->SetLabelType(PIE_VALUE_PER);
    $p1->SetLabels($lbl, "1");


    $graph->Add($p1);
    $p1->ShowBorder();
    $p1->SetColor('black');
    $graph->Stroke($out);
}

// ---------------------------------------------------------- by country ---------------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "country.png";

// top 7:
$ile_pozycji=8;

$sql = "SELECT country, count(distinct id) AS ile_kretow
FROM `gk-ostatnieruchy`
WHERE `logtype` = ('O' OR '3')
GROUP BY country
ORDER BY ile_kretow DESC limit $ile_pozycji";

$result = mysqli_query($link, $sql);


while ($row = mysqli_fetch_row($result)) {
    list($country, $kretow) = $row;
    if ($country=='xyz') {
        $country = '?';
    }

    $kretow_top=$kretow_top+$kretow; // wszystkich kretow w rankingu top 7

    $data[]=$kretow;

    $lbl[] = ("$country %.1f%%");
}

// wszystkie

$sql = "SELECT count(id)
FROM `gk-ostatnieruchy`
WHERE `logtype` = ('O' OR '3')";

$result = mysqli_query($link, $sql);

$row = mysqli_fetch_row($result); $total = $row[0];

$poza_statystyka=$total-$kretow_top;

$data[]=$poza_statystyka;
$lbl[] = ("other %.1f%%");


rysuj_pie($data, $lbl, "Stats by country", $out);
unset($p1, $data, $lbl);


// ---------------------------------------------------------- by country - wszedzie ---------------------------------- //

$ile_pozycji=18;

$out = $geokrety_www.'/'.$config['wykresy'] . "country-all.png";

// top 7:
$ile_pozycji=8;

$sql = "SELECT country, count(distinct id) AS ile_kretow
FROM `gk-ruchy`
WHERE `logtype` = ('O' OR '3')
GROUP BY country
ORDER BY ile_kretow DESC limit $ile_pozycji";

$result = mysqli_query($link, $sql);


while ($row = mysqli_fetch_row($result)) {
    list($country, $kretow) = $row;
    if ($country=='xyz') {
        $country = '?';
    }

    $kretow_top=$kretow_top+$kretow; // wszystkich kretow w rankingu top 7

    $data[]=$kretow;

    $lbl[] = ("$country %.1f%%");
}

// wszystkie

$sql = "SELECT count(id)
FROM `gk-ruchy`
WHERE `logtype` = ('O' OR '3')";

$result = mysqli_query($link, $sql);

$row = mysqli_fetch_row($result); $total = $row[0];

$poza_statystyka=$total-$kretow_top;

$data[]=$poza_statystyka;
$lbl[] = ("other %.1f%%");

// A new pie graph
$graph = new PieGraph(500, 400, 'auto');

// Setup title
$graph->title->Set("Stats by country");
$graph->title->SetMargin(8); // Add a little bit more margin from the top

// Create the pie plot
$p1 = new PiePlot($data);

// Set size of pie
$p1->SetSize(0.2);

$p1->SetLabels($lbl);

// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);

// Add plot to pie graph
$graph->Add($p1);

// .. and send the image on it's marry way to the browser

$graph->Stroke($out);


unset($p1, $data, $lbl);

// ---------------------------------------------------------------- typy kretow ----------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "gk_types.png";


$sql = "SELECT typ, count(distinct id) AS ile_typow
FROM `gk-geokrety`
GROUP BY typ";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($typ, $ile) = $row;
    $typ_txt=$cotozakret[$typ];

    $data[]=$ile;
    $lbl[] = ("$typ_txt %.0f%%");
}

rysuj_pie($data, $lbl, "Stats by GK type", $out);
unset($p1, $data, $lbl);
// ---------------------------------------------------------------- typy logów ----------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "log_types.png";


$sql = "SELECT logtype, count(distinct id) AS ile_typow
FROM `gk-ruchy`
GROUP BY logtype
ORDER BY ile_typow";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($typ, $ile) = $row;
    $typ_txt=$cotozalog[$typ];

    $data[]=$ile;
    $lbl[] = ("$typ_txt %.0f%%");
}


rysuj_pie($data, $lbl, "Stats by logtype", $out);
unset($p1, $data, $lbl);

// ---------------------------------------------------------------- system ----------------------------- //

$out = $geokrety_www.'/'.$config['wykresy'] . "wpt_types.png";


$sql = "SELECT LEFT(waypoint, 2) as prefix, count(distinct ruch_id) AS ile_typow
FROM `gk-ruchy`
GROUP BY prefix
ORDER BY ile_typow DESC
LIMIT 4";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($typ, $ile) = $row;

    if ($typ == "") {
        $typ = "?";
    }

    $ile_top=$ile_top+$ile; // wszystkich kretow w rankingu top 7

    $data[]=$ile;
    $lbl[] = ("$typ %.0f%%");
}


// wszystkie

$sql = "SELECT count(ruch_id)
FROM `gk-ruchy`";

$result = mysqli_query($link, $sql);

$row = mysqli_fetch_row($result); $total = $row[0];

$poza_statystyka=$total-$ile_top;

$data[]=$poza_statystyka;
$lbl[] = ("other %.1f%%");



rysuj_pie($data, $lbl, "Stats by waypoint", $out);
unset($p1, $data, $lbl);
mysqli_close($link);

?>
