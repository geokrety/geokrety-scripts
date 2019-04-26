#!/usr/bin/env php
<?php

echo "Generate missing secret id for users\n\n";


function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


include_once("konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT `userid` FROM `gk-users` WHERE `secid` = ''";

$result = mysqli_query($link, $sql);

while ($row=mysqli_fetch_array($result)) {
    echo $row[0] . "\t\t";
    $secid=generateRandomString(128);
    echo "$secid\n";

    $result2 = mysqli_query($link, "UPDATE `gk-users` SET `secid` = '$secid' WHERE `gk-users`.`userid` = $row[0];");
    //exit;
}

?>
