#!/usr/bin/env php
<?php
//śćńółźć

$BAZY['gd']['plik'] = 'dashpoints__all.csv';
$BAZY['gd']['szukaj'] = 'http://geodashing.gpsgames.org/cgi-bin/dp.pl?dp=';


include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


// skasowanie satrych waypointów
$sql="DELETE FROM `gk-waypointy` WHERE `waypoint` LIKE 'GD-%'";
$result = mysqli_query($link, $sql) or die("error 2: $id $sql");


foreach ($BAZY as $baza) {
    $linie = file($baza['plik']);

    foreach ($linie as $linia) {
        /*
        -14.0268,-171.7943,GDAL-ABAC
        052.5913,-170.7784,GDAL-ABAD
        053.0775,-168.6823,GDAL-ABAF
        */
        $dane = explode(",", $linia);
        if ($dane[1] != '') {
            $waypoint = trim($dane[2]);
            $lat = $dane[0];
            $lon = $dane[1];

            $name=trim("GeoDashing game: $waypoint");

            $linka = $baza['szukaj'] . $waypoint;

            $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$linka', '1')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `link`='$linka', `status`='1'";

            //echo $sql; die();
            $result = mysqli_query($link, $sql) or die("error 1: $id $sql");
        }
    }
}

file_put_contents("data-xml.txt", "geodashing;" . date("Y-m-d H:i:s"));


$result = mysqli_query($link, "OPTIMIZE TABLE `gk-waypointy`");
mysqli_close($link);
?>
