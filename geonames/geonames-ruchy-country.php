#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
require_once "$geokrety_www/__sentry.php";

const START_RUCHY_ID = 1809223; // TODO move this in general konfig

$nbUpdated=0;
// ruchy.logtype   	0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip

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

$stmt = \GKDB::prepareBindExecute('selectRuchyWithoutCountry', $selectRuchyWithoutCountry, 'i', array(START_RUCHY_ID));
$stmt->store_result();
$stmt->bind_result(
    $ruch_id,
    $lat,
    $lon
);
while ($stmt->fetch()) {
    echo "$ruch_id, $lat, $lon \t→ ";

    $country = Geokrety\Service\CountryService::getCountryCode(array('lat' => $lat, 'lon' => $lon));

    echo "[$country]".PHP_EOL;

    $sql = 'UPDATE `gk-ruchy` SET country = ? WHERE ruch_id = ? LIMIT 1';
    $stmtUpdate = \GKDB::prepareBindExecute('updateRuchyCountry', $sql, 'si', array($country, $ruch_id));
    $stmtUpdate->close();
    $nbUpdated++;
}
$stmt->close();

echo "nbUpdated: $nbUpdated\n";
