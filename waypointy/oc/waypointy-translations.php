#!/usr/bin/env php
<?php
/**
 * Goal: provide an english translation of exposed opencaching data
 *   waypointy::typ  : cache-type    : gk-waypointy-type
 *   waypointy::kraj : cache-country : gk-waypointy-country
 * Translation are not embedded into waypointy table to avoid data duplicate, and facilitate maintainability
 *
 **/
include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

include_once("OCWaypointy.class.php");

$validActions = array("report", "generate");
$action=$argv[1];
if (!isset($action) || !in_array($action, $validActions)) {
    echo "usage: php ", $argv[0], " <", join("|", $validActions),">";
    die;
}

$verbose = true;
$link = DBPConnect();
$ocw = new OCWaypointy($link, $verbose);
try {
    if ($action == "report") {
        $ocw->updateTranslations();
        $ocw->reportStateCountry();
        $ocw->reportStateType();
    } else if ($action == "generate") {
        $ocw->generateTranslations();
    }
} catch (Exception $e) {
    echo "Exception: ",  $e->getMessage(), PHP_EOL;
} finally {
    mysqli_close($link);
}
?>