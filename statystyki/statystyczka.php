#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$data = date("r");
$TRESC='';
// -------------------------------------- nowe rzeczy co...  ------------------------------- //

$result = mysqli_query($link, "SELECT COUNT( * )  FROM `gk-geokrety` WHERE DATE(`data`) = DATE(NOW())");
list($stat_geokretow_dzis) = mysqli_fetch_array($result);
mysqli_free_result($result);


$result = mysqli_query($link, "SELECT COUNT( * ) , SUM( `droga` ) FROM `gk-geokrety` ");
list($stat_geokretow, $stat_droga) = mysqli_fetch_array($result);
mysqli_free_result($result);

$stat_proc_tc=sprintf("%.4f", $stat_geokretow/15448044.16);
$stat_proc_rn4=sprintf("%.4f", 100*$stat_geokretow/65536);
$stat_proc_rn5=sprintf("%.4f", 100*$stat_geokretow/1048576);


$result = mysqli_query($link, "SELECT COUNT( * ) FROM `gk-users` ");
list($stat_userow) = mysqli_fetch_array($result);
mysqli_free_result($result);

$result = mysqli_query($link, "SELECT COUNT( * ) FROM `gk-ruchy` ");
list($stat_ruchow) = mysqli_fetch_array($result);
mysqli_free_result($result);


$result = mysqli_query($link, "SELECT COUNT( waypoint ) FROM `gk-waypointy` WHERE waypoint NOT LIKE '%GD'");
list($stat_waypointow) = mysqli_fetch_array($result);
mysqli_free_result($result);



$result = mysqli_query($link, "SELECT count( DISTINCT x.id ) FROM (SELECT id, max(DATA ) AS data_ost FROM `gk-ruchy` GROUP BY id) AS x LEFT JOIN `gk-ruchy` AS y ON x.id = y.id WHERE data_ost = y.data AND y.logtype IN ('0', '3')");
list($stat_geokretow_zakopanych) = mysqli_fetch_array($result);
mysqli_free_result($result);

$stat_USD=sprintf("%.2f", $stat_geokretow*4.99);

$stat_droga_ksiezyc = $stat_droga / 384403;
$stat_droga_slonce = $stat_droga / 149600000;
$stat_droga_obwod = $stat_droga / 40041.455;


$start = strtotime("2007-10-26 20:36");
$gk_uptime = time()-$start;
$gk_uptime_d = sprintf("%.1f", $gk_uptime/86400);
$gk_uptime_h = sprintf("%.1f", $gk_uptime/3600);
$gk_uptime_m = sprintf("%.1f", $gk_uptime/60);



$newuser = sprintf("%.1f", $gk_uptime_h/$stat_userow);
$newgk = sprintf("%.1f", $gk_uptime_h/$stat_geokretow);
$newlog = sprintf("%.1f", $gk_uptime_m/$stat_ruchow);
$stat_liczbowe=sprintf(_("<strong>%d km</strong> done by all GeoKrets (it is %.2f x distance from the Earth to the Moon, %.2f x the Earth equatorial circumference and %.5f x the distance from the Earth to the Sun)."), $stat_droga, $stat_droga_ksiezyc, $stat_droga_obwod, $stat_droga_slonce);

$TRESC .="
<table>
<tbody>
  <tr>
    <td>" . _("geokrety.org uptime") . ":</td>
    <td><strong>$gk_uptime_d days</strong> (start: 2007-10-26 20:36)</td>
  </tr>
  <tr>
    <td>" . _("New user every") . ":</td>
    <td><strong>$newuser h</strong></td>
  </tr>
  <tr>
    <td>" . _("New geokret every") . ":</td>
    <td><strong>$newgk h</strong></td>
  </tr>
  <tr>
    <td>" . _("New log entry every") . ":</td>
    <td><strong>$newlog min</strong></td>
  </tr>
  <tr>
    <td>" . _("Waypoints in the database") . ":</td>
    <td><strong>$stat_waypointow</strong></td>
  </tr>
  <tr>
    <td>" . _("Registered GeoKrets") . ":</td>
    <td><strong>$stat_geokretow</strong></td>
  </tr>

  <tr>
    <td>" . _("Registered GeoKrets today") . ":</td>
    <td><strong>$stat_geokretow_dzis</strong></td>
  </tr>

  <tr>
    <td>" . _("Percentage of used tracking codes") . ":</td>
    <td><strong>$stat_proc_tc%</strong></td>
  </tr>
  <tr>
    <td>% of used reference numbers GKxxxx:</td>
    <td><strong>$stat_proc_rn4%</strong></td>
  </tr>

  <tr>
    <td>% of used reference numbers GKxxxxx:</td>
    <td><strong>$stat_proc_rn5%</strong></td>
  </tr>


  <tr>
    <td>" . _("Geokrety.org users saved, by not buying TB (4.99USD/item)") . ":</td>
    <td><strong>$stat_USD USD</strong> (cool!:)</td>
  </tr>
  <tr>
    <td>" . _("GeoKrets hidden") . ":</td>
    <td><strong>$stat_geokretow_zakopanych</strong></td>
  </tr>
  <tr>
    <td>" . _("Users") .":</td>
    <td><strong>$stat_userow</strong></td>
  </tr>
  <tr>
    <td>" . _("Total logs") . "</td>
    <td><strong>$stat_ruchow</strong></td>
  </tr>
  <tr>
    <td>" . _("Total distance") . "</td>
    <td>$stat_liczbowe</td>
  </tr>
</tbody>
</table><p><i>Generared: $data</i></p>
";

// skrócona wersja
$TRESC_SMALL = sprintf("All GeoKrets travelled %.2f x distance from the Earth to the Moon! <a href=\"https://geokrety.org/statystyczka.php\">Click here</a> for more stats.", $stat_droga_ksiezyc);

file_put_contents("$geokrety_www/files/statystyczka.html", $TRESC);
file_put_contents("$geokrety_www/files/statystyczka-s.html", $TRESC_SMALL);
file_put_contents("$geokrety_www/files/statystyczka-s2.html", $stat_liczbowe);
file_put_contents("$geokrety_www/files/statystyczka-kretow.txt", "$stat_geokretow\n$stat_geokretow_zakopanych\n$stat_userow\n$stat_ruchow\n$stat_droga\n$stat_geokretow_dzis");


// --------------- sql ---------------//

//$stat_droga, $stat_droga_ksiezyc, $stat_droga_obwod,$stat_droga_slonce

$sql = "UPDATE `gk-wartosci` SET `value` = '$stat_geokretow' WHERE `gk-wartosci`.`name` = 'stat_geokretow';
UPDATE `gk-wartosci` SET `value` = '$stat_geokretow_zakopanych' WHERE `gk-wartosci`.`name` = 'stat_geokretow_zakopanych';
UPDATE `gk-wartosci` SET `value` = '$stat_userow' WHERE `gk-wartosci`.`name` = 'stat_users';
UPDATE `gk-wartosci` SET `value` = '$stat_ruchow' WHERE `gk-wartosci`.`name` = 'stat_ruchow';
UPDATE `gk-wartosci` SET `value` = '$stat_droga' WHERE `gk-wartosci`.`name` = 'stat_droga';
UPDATE `gk-wartosci` SET `value` = '$stat_droga_ksiezyc' WHERE `gk-wartosci`.`name` = 'stat_droga_ksiezyc';
UPDATE `gk-wartosci` SET `value` = '$stat_droga_obwod' WHERE `gk-wartosci`.`name` = 'stat_droga_obwod';
UPDATE `gk-wartosci` SET `value` = '$stat_droga_slonce' WHERE `gk-wartosci`.`name` = 'stat_droga_slonce';
UPDATE `gk-wartosci` SET `value` = '$stat_userow' WHERE `gk-wartosci`.`name` = 'stat_userow';

";
$result = mysqli_multi_query($link, $sql);
//echo $sql;
mysqli_close($link);

?>
