#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

const START_RUCHY_ID = 1809223; // TODO move this in general konfig

$sql = <<<EOSQL
SELECT  ruch_id, lat, lon
FROM    `gk-ruchy`
WHERE   logtype not in ('2', '1', '4')
AND     alt < -2000
AND     ruch_id > ?
EOSQL;

$stmt = \GKDB::prepareBindExecute('selectRuchyWithoutCountry', $sql, 'i', array(START_RUCHY_ID));
$stmt->store_result();
$stmt->bind_result(
    $ruch_id,
    $lat,
    $lon
);
while ($stmt->fetch()) {
    echo "$ruch_id, $lat, $lon \t→ ";

    $alt = Geokrety\Service\ElevationService::getElevation(array('lat' => $lat, 'lon' => $lon));

    echo "[$alt]".PHP_EOL;

    $sql = 'UPDATE `gk-ruchy` SET alt = ? WHERE ruch_id = ? LIMIT 1';
    $stmtUpdate = \GKDB::prepareBindExecute('updateRuchyCountry', $sql, 'di', array($alt, $ruch_id));
    $stmtUpdate->close();
    $nbUpdated++;
}
$stmt->close();

echo "nbUpdated: $nbUpdated\n";
