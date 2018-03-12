#!/usr/bin/env php

<?php

// list of GK in archived/destroyed caches

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$data = date("r");

$sql = "DROP TABLE `gk-ostatnieruchy`";
$result = mysqli_query($link, $sql);

$sql = "CREATE TABLE `gk-ostatnieruchy` AS
(SELECT y.* FROM
(SELECT id, max(DATA) AS data_ost FROM `gk-ruchy`
WHERE logtype != '2' GROUP BY id) AS x
LEFT JOIN `gk-ruchy` AS y ON x.id = y.id
WHERE data_ost = y.data)";

$result = mysqli_query($link, $sql);

?>
