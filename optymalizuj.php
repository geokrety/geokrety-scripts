#!/usr/bin/env php

<?php
include_once("konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$result = mysqli_query($link, "OPTIMIZE TABLE `gk-aktywnekody` , `gk-aktywnemaile` , `gk-aktywnesesje` , `gk-geokrety` , `gk-grupy` , `gk-grupy-desc` , `gk-licznik` , `gk-maile` , `gk-miasta` , `gk-news` , `gk-obrazki` , `gk-obrazki-2` , `gk-obserwable` , `gk-ostatnieruchy` , `gk-ruchy` , `gk-users` , `gk-waypointy`");

mysqli_close($link);

?>
