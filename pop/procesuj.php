#!/usr/bin/env php
<?php

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
require('pop3-klasa/pobierz.php');

//	print_r($wiadomosci);

//	$wiadomosc['data'] = date("r");
//	$wiadomosc['temat'] = 'V75W45';
//	$wiadomosc['tresc'] = '52 2.5#21 17.2#to jest testowy opis mojej przygody';

    // ------------------------------------------------------------------------------------- //

//print_r($wiadomosci); die();

if (isset($wiadomosci) && $wiadomosci) foreach ($wiadomosci as $wiadomosc) {
    $tresc = explode("#", $wiadomosc['tresc']);

    $kret_nr =$wiadomosc['temat'];

    $kret_lat=$tresc[0];
    $kret_lon=$tresc[1];
    $kret_comment = "[e-mail] " .  $tresc[2];
    $data = date("Y-m-d H:i:s", strtotime($wiadomosc['data']));

    if ((empty($kret_lat) and empty($kret_lon))) {
        $kret_logtype = 2;
    } // komentarz
    else {
        $kret_logtype = 0;
    } // in

    $link = DBPConnect();

    // ------ kretonumer ---------- //

    $kret_nr=trim(strtoupper($kret_nr));
    $result = mysqli_query($link, "SELECT * FROM `gk-geokrety` WHERE `nr`='$kret_nr' LIMIT 1");
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    if (empty($row)) {
        $err_kret=1;
    } else {
        list($kretid, $kretnr, $kretnazwa, $kretopis, $kretowner, $kretdata) = $row;
    }

    // ------- data ostatniej modyfikacji ----- //

    include_once("$geokrety_www/whoiskret.php");

    list($whoiskret_nazwa, $whoiskret_owner, $whoiskret_data) = whoiskret($kretid);


    // ------- lat/lon ----------- //

    if (empty($lat) and !empty($kret_lat) and !empty($kret_lon)) {
        $tocheck[]="lat";
        $tocheck[]="lon";        // lat and lon to check
        foreach ($tocheck as $check) {
            $wspl_tmp = explode(" ", ${"kret_$check"});
            list($wspl_d[$check], $wspl_m[$check]) = $wspl_tmp;
            if ((!is_numeric($wspl_d[$check])) or (!is_numeric($wspl_m[$check]))) {
                $err_wspl=1;
            }
            $$check=$wspl_d[$check]+($wspl_d[$check]/abs($wspl_d[$check]))*$wspl_m[$check]/60;
        }
        if ((abs($lat)>90) or (abs($lon)>180)) {
            $err_wspl=1;
        } // lat - Szeroko�� geograficzna NS
    }

    if ($err_kret==1) {
        $TRESC = _("No such GeoKret! $kret_nr");
    } elseif ($err_wspl==1) {
        $TRESC = _("Wrong lat/lon");
    } else {        // ALL all really all is correct.
        include_once("$geokrety_www/czysc.php");

        $kret_comment = czysc($kret_comment);
        $kret_username = $whoiskret_owner;


        $sql = "INSERT INTO `gk-ruchy` (`id`, `lat`, `lon`, `waypoint`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`) 	VALUES ('$kretid', '$lat', '$lon', '', '$data', '$kret_username', '$kret_comment', '$kret_logtype', '', NOW())";

        $result = mysqli_query($link, $sql);
    }

    // errory:
    echo "$TRESC\n\n";


    unset($kret_nr, $kret_lat, $kret_lon, $data, $kret_comment, $err_kret, $err_wspl, $TRESC, $tresc);
} //foreach
//include_once("aktualizuj.php"); aktualizuj_obrazek_statystyki($whoiskret_owner); aktualizuj_obrazek_statystyki($user);

?>
