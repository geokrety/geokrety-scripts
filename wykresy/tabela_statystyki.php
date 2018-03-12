#!/usr/bin/env php

<?php

// generuje tabelę z danymi historycznymi statystycznymi.


include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$start_gk_data="2007-10-26 00:00:00";
$start_gk=strtotime($start_gk_data);

//$start = strtotime("2007-10-26 00:00:00");
//$dzien_start=0;

$start=strtotime("-1 weeks");
$result = mysqli_query($link, "SELECT `dzien`, `gk_`, `gk_zakopane_`, `users_`, `ruchow_` FROM `gk-statystyki-dzienne` WHERE UNIX_TIMESTAMP(`data`)>='$start'");
list($dzien_start, $gk_, $gk_zakopane, $users, $ruchow_) = mysqli_fetch_array($result); mysqli_free_result($result);


$dni = sprintf("%.0f", (time()-$start_gk)/86400);


//echo "$dzien_start, $gk_, $gk_zakopane, $users, $ruchow_ :: $dni"; die();

// ------------------------------------------------------- GK zakopane i ogółem ------------------------------- //


for ($dzien = $dzien_start; $dzien < $dni; $dzien++) {
    $data=date("Y-m-d", strtotime("+ $dzien day", $start_gk));
    $data1=date("Y-m-d", strtotime("$data + 1 day", $start_gk));

    //echo "$data $data1"; die();


    // geokrety stworzone tego dnia
    $result = mysqli_query($link, "SELECT COUNT(`nr`) FROM `gk-geokrety` WHERE DATE(`data`) between '$data' and '$data1'");
    list($gk) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    // geokrety stworzone od począ    tku
    $result = mysqli_query($link, "SELECT COUNT(`nr`) FROM `gk-geokrety` WHERE DATE(`data`) between DATE('$start_gk_data') and '$data1'");
    list($gk_) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    // zakopane w owym czasie
    $result = mysqli_query($link, "SELECT count( DISTINCT x.id ) FROM (SELECT id, max(DATA ) AS data_ost FROM `gk-ruchy` GROUP BY id) AS x LEFT JOIN `gk-ruchy` AS y ON x.
id = y.id WHERE data_ost = y.data AND y.logtype IN ('0', '3') AND data_ost <= '$data'");
    list($zakopane)= mysqli_fetch_array($result);
    mysqli_free_result($result);

    // userzy
    $result = mysqli_query($link, "SELECT COUNT(`userid`) FROM `gk-users` WHERE DATE(`joined`) between '$data' and '$data1'");
    list($users) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    // userzy od począ    tku
    $result = mysqli_query($link, "SELECT COUNT(`userid`) FROM `gk-users` WHERE DATE(`joined`) between DATE('$start_gk_data') and '$data1'");
    list($users_) = mysqli_fetch_array($result);
    mysqli_free_result($result);


    //ruchy
    $result = mysqli_query($link, "SELECT COUNT(`ruch_id`) FROM `gk-ruchy` WHERE DATE(`data_dodania`) between '$data' and '$data1' AND `logtype`!='2'");
    list($ruchow) = mysqli_fetch_array($result);
    mysqli_free_result($result);

    //ruchy od począ    tku
    $result = mysqli_query($link, "SELECT COUNT(`ruch_id`) FROM `gk-ruchy` WHERE DATE(`data_dodania`) between DATE('$start_gk_data') and '$data1' AND `logtype`!='2'");
    list($ruchow_) = mysqli_fetch_array($result);
    mysqli_free_result($result);


    $procent_zakopanych=$zakopane/$gk_*100;

    //echo "************* data: " . date("c", $data) . "\n";

    echo "\nGK: między $data a $data1\nGK: dziś $gk   total: $gk_\nUsers: $users $users_\nRuchow: $ruchow $ruchow_\nzakopanych: $zakopane\nZaopanych %$procent_zakopanych\n";

    $sql="INSERT INTO `gk-statystyki-dzienne`
(`data`, `dzien`, `gk`, `gk_`, `gk_zakopane_`, `procent_zakopanych`, `users`, `users_`, `ruchow`, `ruchow_`)
VALUES ('$data', '$dzien', '$gk', '$gk_', '$zakopane', '$procent_zakopanych', '$users', '$users_', '$ruchow', '$ruchow_')
ON DUPLICATE KEY UPDATE
`data`='$data', `dzien`='$dzien' ,gk='$gk', `gk_`='$gk_', `gk_zakopane_`='$zakopane', `procent_zakopanych`='$procent_zakopanych',
`users`='$users', `users_`='$users_', `ruchow`='$ruchow', `ruchow_`='$ruchow_'
";

    // $result = mysqli_query($link, $sql);
    if (!mysqli_query($link, $sql)) {
        printf("Erreur : %s\n", mysqli_error($link));
    }
}

mysqli_close($link);
?>
