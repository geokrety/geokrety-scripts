#!/usr/bin/env php

<?php
// Send emails with important information to all who want to read emails


$biuletyn = 1; // numer biuletynu
$wersje_jezykowe = array("pl", "en", "de");

$godzina = date("G");

include_once("konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");

include_once("$geokrety_www/waypoint_info.php");
include_once("$geokrety_www/recent_moves.php");

foreach ($wersje_jezykowe as $jezyk) {
    $tresc_biuletynu['$jezyk'] = file_get_contents("../rzeczy/biuletyny/$biuletyn/$jezyk.txt");
}


// email headers
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
$headers .= 'From: GeoKrety <geokrety@gmail.com>' . "\r\n";
$headers .= 'Reply-To: GeoKrety <geokrety@gmail.com>' . "\r\n";
$headers .= 'X-Face: V3fa4jl.GK^s&Ys;p98\'/noEd86&Q1du*q\'g%#mX&le1VV9hM%sl}3-{{$wfp2k/!_+s+%a
 "D:xKi3qZGsmIvQ&OS!^Vyr:P;y:Ycm`EL!,&\Zx}yWI0pKG?hPYib8=m:g?&x7nS$CX)@jj"Y(nJ[
  i-+B@@O|hTUBvIl:@gOOnhJ^d^tMK]$.+j)^pU[\_,a;(iPi*|"-I/^}o4b' . "\r\n";

$data = date("Y-m-d");
$datarfc=date("r");


$link = DBPConnect();


// -------------------------------------- news ------------------------------- //

$sql = "SELECT userid, user, email, lang FROM `gk-users` WHERE godzina=$godzina and wysylacmaile=1 and email!=''";
$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($userid, $user, $email, $lang) = $row;


    if (!in_array($lang, $wersje_jezykowe) or $lang='') {    // jesli jezyk niesupportowany
        $lang='en';
    }



    $TRESC = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head><meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />
<style type="text/css">body, td, bardzomale {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt; color: #000000;text-decoration: none;}  td {border-bottom: 1px solid #ffffff;vertical-align: top;background-color: #eeeeee;} A:link, A:visited, A:hover { text-decoration: none;} A:hover { color: White; background-color: #a7d940;} .bardzomale {font-size: 8pt;} </style><title>Geokrety watchlist</title></head><body>';

    $TRESC .=  "Dear $user ($email)\n\n";
    $TRESC .= $tresc_biuletynu['lang'];
    $TRESC .= "-- \n<br>" .  file_get_contents("$geokrety_www/files/statystyczka-s.html") . "";
    $TRESC .= "</body></html>";


    //mail($email, '[GeoKrety] Watchlist ' . $data, $TRESC, $headers);
    //mail("stefaniak@gmail.com", '[GeoKrety] Watchlist ' . $data, $TRESC, $headers);
    //mail("stefaniak@gmail.com", '[GeoKrety] Watchlist ' . $data, $TRESC, $headers);
    file_put_contents("../tymczas/biul/$email.html", $TRESC);
    die();

    sleep(15);
}// niepusty email


?>
