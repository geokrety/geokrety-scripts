#!/usr/bin/env php
<?php


$logfile = "logfile.txt";

$godzina = date("G");
include_once("spam_search.fn.php");
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php"); // $geokrety_www
require_once "$geokrety_www/__sentry.php";

$wysylaj=0;    // znacznik który mówi, czy wysyłać maile
$ODKIEDY = 10;

$link = DBPConnect();

$headers = 'From: geokrety.org <geokrety@gmail.com>' . "\r\n" .
'X-Mailer: krety zawijaja w sreberka';

// -------------------------------------- spam ------------------------------- //

$log = "Detector and meat detector, alert information.
launched " . date('c') . ". Checks the last n minutes

----------------------- [ spam i bluzgi ] ------------\n";


$sqle['geokrety'] = "SELECT `nazwa`, `id`, `owner`, `opis`, `timestamp` FROM `gk-geokrety` WHERE (TIMESTAMPDIFF(MINUTE , `timestamp`, NOW( ))<$ODKIEDY)";
$sqle['ruchy'] = "SELECT `ruch_id`, `id`, `user`, `koment`, `timestamp` FROM `gk-ruchy` WHERE (TIMESTAMPDIFF(MINUTE , `timestamp`, NOW( ))<$ODKIEDY)";
$sqle['ruchy-comments'] = "SELECT `ruch_id`, `kret_id`, `user_id`, `comment`, `timestamp` FROM `gk-ruchy-comments` WHERE (TIMESTAMPDIFF(MINUTE , `timestamp`, NOW( ))<$ODKIEDY)";
$sqle['news-comments'] = "SELECT `news_id`, `comment_id`, `user_id`, `comment`, `date` FROM `gk-news-comments` WHERE (TIMESTAMPDIFF(MINUTE , `date`, NOW( ))<$ODKIEDY)";

foreach ($sqle as $key => $sql) {
    echo "[ $key ]\n";
    $result = mysqli_query($link, $sql);
    if (!$result) {
        // Try to reconnect, as it runs for a long time, and connexion may be closed
        $link = DBPConnect() or die('Failed to re-connect');
        $result = mysqli_query($link, $sql) or die('Failed to re-play the query');
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $opisy=array_keys($row);
        //     list($ruch_id, $id, $user, $koment) = $row;
        echo $row[$opisy[0]] . ": ";
        $maches=checkwordblock($row[$opisy[3]]);
        if ($maches != null) {
            $log .= date("r") . "\nZnaleziono w: $key\n$opisy[0]: " . $row[$opisy[0]] . "\n$opisy[1]: " . $row[$opisy[1]]. "\nid: $opisy[2]: ". $row[$opisy[2]]. "\nbluzg: $maches[0]\n$opisy[3]: " . $row[$opisy[3]]. "\n\n";
            $wysylaj=1;
            $spam=1;
        } else {
            echo "... clear\n";
            $spam=0;
        }
    }
}


// -------------------------------------- szymon ------------------------------- //

include("$geokrety_www/email_errors.php");

/*
$ostatnifullraport=file_get_contents("/home/geokrety/tools/spam/ostatnifullraport.txt");
$full_raport_co=12*60; // pelen raport co... godzin (w minutach)

if((time() - $ostatnifullraport) >= $full_raport_co*60){		// jeśli ostatni full raport wysłany > 12 h temu
    file_put_contents("/home/geokrety/tools/spam/ostatnifullraport.txt", time());
    $email_severity=1;
    $ODKIEDY = $full_raport_co; // jak stare niusy i dane (ile minut) - pelen raport

}
*/

//else{   // te mniej poważne
        $email_severity=7;
        $ODKIEDY = 10; // jak stare niusy i dane (ile minut)
//    }

$bledy=email_errors(date("YmdHis", strtotime("-$ODKIEDY minutes")), $email_severity);  // co 10 minut, ciezkosc bledu >= 7 ;P

if ($bledy != '') {
    $log .= "----------------------- [ email errors ] ------------\nGroznosc errorow: $email_severity \n\n";
    $log .= $bledy;
    $wysylaj = 1;
}


// -------------------------------------- wysylanie finalne ------------------------------- //


if ($wysylaj == 1) {
    echo "jest coś na rzeczy\n";
    file_put_contents($logfile, $log, FILE_APPEND);
    // mail("stefaniak@gmail.com, sirsimor@gmail.com", "[GeoKrety] Znaleziono errory ($email_severity) lub spam ($spam) ($ODKIEDY min)", $log, $headers);
}
?>
