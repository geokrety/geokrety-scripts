#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


/* ----------------------------------------------- kraj ------------------------------------------------------- */

include("$geokrety_www/get_country_from_coords.php");



//ok:
//$result = mysqli_query("SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (`lat`!='0' AND `lon`!='0') AND (`country` IS NULL or `country`='' or `country`='?' or `country`='{')");

// uzupełnianie starych zrobić od 650400
$result = mysqli_query($link, "SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (`lat`!='0' AND `lon`!='0') AND (`country` IS NULL or `country`='' or `country`='xyz' or `country`='err') AND `ruch_id`>730000");
//$result = mysqli_query($link, "SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (`lat`!='0' AND `lon`!='0') AND (`country` IS NULL or `country`='' or `country`='XYZ' or `country`='{') AND `ruch_id`>632000");

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $lat, $lon) = $row;


    $country='';

    echo "$ruch_id, $lat, $lon::  ";

    //file_put_contents("/home/geokrety/geonamess.info", "$ruch_id, $lat, $lon::");

    $country=get_country_from_coords($lat, $lon);
    //print_r($country);
    echo "[ $country] ";

    //file_put_contents("/home/geokrety/geonamess.info", "local search: $country ;;");


    /*
    if($country == '' OR $country=='xyz' or $country==NULL or $country=='?'){
    $handle = fopen("http://ws.geonames.org/countryCode?lat=$lat&lng=$lon&radius=30", "rb");
     $country = strtolower(trim(fread($handle, 3)));
     echo "geonames...";
     if(($country == '<!d') OR ($country == '')) { $country = 'xyz'; echo "bach!\n";}
     //file_put_contents("/home/geokrety/geonamess.info", "geonames: $country ;;");
     fclose($handle);
      sleep(1);
      }
    */

    echo " google...";
    if ($country == '' or $country=='xyz' or $country==null or $country=='?') {
        //$url="http://maps.google.com/maps/geo?q=$lat,$lon&output=json&sensor=false";
        $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);
        //if(is_array($jsondata )&& $jsondata ['Status']['code']==200)  $country = $jsondata ['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'];
        echo "status: "  . $jsondata['status'] . "\t";

        if (is_array($jsondata) and $jsondata['status']=="OK") {
            $data = array();
            foreach ($jsondata['results']['0']['address_components'] as $element) {
                $data[ implode(' ', $element['types']) ] = $element['short_name'];
            }
            //print_r($data);

            $country = $data['country political'];
        }

        sleep(1);
    }

    $country = strtolower($country);
    if ($country == '') {
        $country ='xyz';
    }

    echo "$country\n";

    $sql = "UPDATE `gk-ruchy` SET  `country`='$country' WHERE `ruch_id` = '$ruch_id' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
}


mysqli_close($link);

?>
