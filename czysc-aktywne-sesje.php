#!/usr/bin/env php

<?php
include_once("konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$result = mysqli_query($link, "TRUNCATE TABLE `gk-aktywnesesje`");

$result = mysqli_query($link, "OPTIMIZE TABLE `gk-aktywnesesje`");

mysqli_close($link);

?>
