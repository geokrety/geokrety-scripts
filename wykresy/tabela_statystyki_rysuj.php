#!/usr/bin/env php

<?php

require_once("jpgraph-3.5.0b1/src/jpgraph.php");
require_once("jpgraph-3.5.0b1/src/jpgraph_line.php");
require_once("jpgraph-3.5.0b1/src/jpgraph_date.php");



include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$start=strtotime("2007-10-26");
$stop = time()+86400;



// ------------------------------------------------------- funkcja rysowania wykresu ------------------------------- //

function wykresuj($plik, $x, $y, $tyt_x, $tyt_y, $tytul)
{
    global $config, $geokrety_www;

    $data_human = date("Y-m-d H:i");
    $out = $geokrety_www.'/'.$config['wykresy'] . "new/$plik.png";

    $graph  = new Graph(590, 350, $out);
    // SetMargin($lm, $rm, $tm, $bm)
    $graph->SetMargin(55, 10, 10, 78);
    $graph->SetScale("datlin");
    $graph->SetMarginColor('white');
    $graph->SetBox(true, 'black', 1);

    $graph->xgrid->Show();

    $graph->xaxis->SetLabelAngle(45);
    //$graph->xaxis->SetTitle($tyt_x,'middle');
    $graph->yaxis->SetTitle($tyt_y, 'middle');
    $graph->title->Set("geokrety.org :: $data_human");

    // Create the linear plot
    $lineplot =new LinePlot($y, $x);
    $lineplot ->SetColor("#a7d940");
    $lineplot ->SetWeight(3);

    //$graph->legend->Pos(0.15,0.15,"left","top");

    // Add the plot to the graph
    $graph->Add($lineplot);

    // Display the graph
    $graph->Stroke($out);
}


// ------------------------------------------------------- kolejne zapytania ------------------------------- //

// --------------- od początku --------------- //
$result = mysqli_query($link, "SELECT UNIX_TIMESTAMP(`data`), `gk_`, `procent_zakopanych`, `users_`, `ruchow_`, `gk`, `users`, `ruchow` FROM `gk-statystyki-dzienne`
ORDER BY `data` asc");
while ($row = mysqli_fetch_row($result)) {
    //    print_r($row); die();
    $data[]=$row[0];
    $gk_[]=$row[1];
    $procent_zakopanych[]=$row[2];
    $users_[]=$row[3];
    $ruchow_[]=$row[4];
    $gk[]=$row[5];
    $users[]=$row[6];
    $ruchow[]=$row[7];
}

if ($data) {
    // print_r($data); die();
    wykresuj("all_gk_", $data, $gk_, "Date", "Geokretów total", "");
    wykresuj("all_gk_zakopane", $data, $procent_zakopanych, "Date", "%Geokretów in caches", "");
    wykresuj("all_users_", $data, $users_, "Date", "Users, total", "");
    wykresuj("all_ruchow_", $data, $ruchow_, "Date", "Moves, total", "");
    wykresuj("all_gk", $data, $gk, "Date", "New Geokrets, by day", "");
    wykresuj("all_users", $data, $users, "Date", "New users, by day", "");
    wykresuj("all_ruchow", $data, $ruchow, "Date", "Moves, by day", "");
}
unset($data, $gk_, $procent_zakopanych, $users_, $ruchow_, $gk, $users, $ruchow);
// --------------- od początku --------------- //

$od=strtotime("-1 year");

$result = mysqli_query($link, "SELECT UNIX_TIMESTAMP(`data`), `gk_`, `procent_zakopanych`, `users_`, `ruchow_`, `gk`, `users`, `ruchow` FROM `gk-statystyki-dzienne`
WHERE UNIX_TIMESTAMP(`data`) > '$od' ORDER BY `data` asc");
while ($row = mysqli_fetch_row($result)) {
    //    print_r($row); die();
    $data[]=$row[0];
    $gk_[]=$row[1];
    $procent_zakopanych[]=$row[2];
    $users_[]=$row[3];
    $ruchow_[]=$row[4];
    $gk[]=$row[5];
    $users[]=$row[6];
    $ruchow[]=$row[7];
}

if ($data) {
    wykresuj("y_gk_", $data, $gk_, "Date", "Geokretów total", "");
    wykresuj("y_gk_zakopane", $data, $procent_zakopanych, "Date", "%Geokretów in caches", "");
    wykresuj("y_users_", $data, $users_, "Date", "Users, total", "");
    wykresuj("y_ruchow_", $data, $ruchow_, "Date", "Moves, total", "");
    wykresuj("y_gk", $data, $gk, "Date", "New Geokrets, by day", "");
    wykresuj("y_users", $data, $users, "Date", "New users, by day", "");
    wykresuj("y_ruchow", $data, $ruchow, "Date", "Moves, by day", "");
}
unset($data, $gk_, $procent_zakopanych, $users_, $ruchow_, $gk, $users, $ruchow);
// --------------- od miesiąca --------------- //

$od=strtotime("-1 month");

$result = mysqli_query($link, "SELECT UNIX_TIMESTAMP(`data`), `gk_`, `procent_zakopanych`, `users_`, `ruchow_`, `gk`, `users`, `ruchow` FROM `gk-statystyki-dzienne`
WHERE UNIX_TIMESTAMP(`data`) > '$od' ORDER BY `data` asc");
while ($row = mysqli_fetch_row($result)) {
    //    print_r($row); die();
    $data[]=$row[0];
    $gk_[]=$row[1];
    $procent_zakopanych[]=$row[2];
    $users_[]=$row[3];
    $ruchow_[]=$row[4];
    $gk[]=$row[5];
    $users[]=$row[6];
    $ruchow[]=$row[7];
}

if ($data) {
    wykresuj("m_gk_", $data, $gk_, "Date", "Geokretów total", "");
    wykresuj("m_gk_zakopane", $data, $procent_zakopanych, "Date", "%Geokretów in caches", "");
    wykresuj("m_users_", $data, $users_, "Date", "Users, total", "");
    wykresuj("m_ruchow_", $data, $ruchow_, "Date", "Moves, total", "");
    wykresuj("m_gk", $data, $gk, "Date", "New Geokrets, by day", "");
    wykresuj("m_users", $data, $users, "Date", "New users, by day", "");
    wykresuj("m_ruchow", $data, $ruchow, "Date", "Moves, by day", "");
}
unset($data, $gk_, $procent_zakopanych, $users_, $ruchow_, $gk, $users, $ruchow);

/*
// --------------- od tygodnia --------------- //

$od=strtotime("-1 week");

$result = mysqli_query($link, "SELECT UNIX_TIMESTAMP(`data`), `gk_`, `procent_zakopanych`, `users_`, `ruchow_`, `gk`, `users`, `ruchow` FROM `gk-statystyki-dzienne`
WHERE UNIX_TIMESTAMP(`data`) > '$od' ORDER BY `data` asc");
while ($row = mysqli_fetch_row($result)) {
//    print_r($row); die();
    $data[]=$row[0];
    $gk_[]=$row[1];
    $procent_zakopanych[]=$row[2];
    $users_[]=$row[3];
    $ruchow_[]=$row[4];
    $gk[]=$row[5];
    $users[]=$row[6];
    $ruchow[]=$row[7];
}


wykresuj("w_gk_", $data, $gk_, "Date", "Geokretów total", "");
wykresuj("w_gk_zakopane", $data, $procent_zakopanych, "Date", "%Geokretów in caches", "");
wykresuj("w_users_", $data, $users_, "Date", "Users, total", "");
wykresuj("w_ruchow_", $data, $ruchow_, "Date", "Moves, total", "");
wykresuj("w_gk", $data, $gk, "Date", "New Geokrets, by day", "");
wykresuj("w_users", $data, $users, "Date", "New users, by day", "");
wykresuj("w_ruchow", $data, $ruchow, "Date", "Moves, by day", "");

unset($data, $gk_, $procent_zakopanych, $users_, $ruchow_, $gk, $users, $ruchow);
*/

mysqli_close($link);
