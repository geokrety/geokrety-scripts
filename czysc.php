#!/usr/bin/env php

<?php
include_once("konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
// $geokrety_www.'/'.

$link = DBPConnect();

$result = mysqli_query($link, "DELETE FROM `gk-aktywnesesje` WHERE (TIMESTAMPADD(DAY,90,`timestamp`) < NOW()) AND `remember`='1'");

$result = mysqli_query($link, "DELETE FROM `gk-aktywnesesje` WHERE (TIMESTAMPADD(MINUTE,60,`timestamp`) < NOW()) AND `remember`!='1'");
$result = mysqli_query($link, "DELETE FROM `gk-aktywnekody` WHERE TIMESTAMPADD(MINUTE,60,`timestamp`) < NOW()");
$result = mysqli_query($link, "DELETE FROM `gk-aktywnemaile` WHERE TIMESTAMPADD(DAY,5,`timestamp`) < NOW()");

$result = mysqli_query($link, "OPTIMIZE TABLE `gk-aktywnekody`");
$result = mysqli_query($link, "OPTIMIZE TABLE `gk-aktywnesesje`");
$result = mysqli_query($link, "OPTIMIZE TABLE `gk-aktywnemaile`");

mysqli_close($link);
