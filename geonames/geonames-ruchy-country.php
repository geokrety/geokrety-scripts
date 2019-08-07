#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
include("$geokrety_www/get_country_from_coords.php");

const START_RUCHY_ID = 1809223; // TODO move this in general konfig

// --- kraj - country - get ruchy without country but with coordinates

$nbUpdated=0;
// ruchy.logtype   	0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip

// get ruchy that are not comment or grab and without country but with lat/lon
$selectRuchyWithoutCountry = <<<EOSQL
SELECT  ruch_id, lat, lon
FROM    `gk-ruchy`
WHERE   logtype != '2'
AND     logtype != '1'
AND     lat != 0
AND     lon != 0
AND     (country = '' or country = 'xyz' or country = 'err')
AND     ruch_id > ?
EOSQL;

$sql = 'UPDATE `gk-ruchy` SET  `country` = ? WHERE ruch_id = ? LIMIT 1';
$stmt = \GKDB::prepareBindExecute('selectRuchyWithoutCountry', $selectRuchyWithoutCountry, 'i', array(START_RUCHY_ID));

$stmt->store_result();
$stmt->bind_result(
    $ruch_id,
    $lat,
    $lon
);
while ($stmt->fetch()) {
    $country='';
    echo "$ruch_id, $lat, $lon::  ";

    // first get from geo.geokrety.org
    $country=get_country_from_coords($lat, $lon);

    echo "[ $country] ";

    if ($country == '' or $country=='xyz' or $country==null or $country=='?') {
        echo " try from google…";
        $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false&key=$GOOGLE_MAP_KEY";
        $jsondata = json_decode(@file_get_contents($url), true);
        echo "status: "  . $jsondata['status'] . "\t";

        if (is_array($jsondata) and $jsondata['status']=="OK") {
            $data = array();
            foreach ($jsondata['results']['0']['address_components'] as $element) {
                $data[ implode(' ', $element['types']) ] = $element['short_name'];
            }

            $country = $data['country political'];
        }

        sleep(1);
    }

    $country = strtolower($country);
    if ($country == 'xyz' or $country == '') {
        echo " no luck, giving up!";
        $country = null;
    }

    echo "[$country]\n";

    $sql = 'UPDATE `gk-ruchy` SET  country = ? WHERE ruch_id = ? LIMIT 1';
    $stmtUpdate = \GKDB::prepareBindExecute('updateRuchyCountry', $sql, 'si', array($country, $ruch_id));
    $stmtUpdate->close();
    $nbUpdated++;
}
$stmt->close();

echo "nbUpdated: $nbUpdated\n";
