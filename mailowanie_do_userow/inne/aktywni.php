#!/usr/bin/env php

<?php
// export danych do mapki statycznej

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

// ------------- mają

$sql = "SELECT `gk-users`.`email` , `gk-users`.`user` , COUNT( `gk-ruchy`.`ruch_id` ) AS ile
FROM `gk-ruchy`
LEFT JOIN `gk-users` ON `gk-ruchy`.`user` = `gk-users`.`userid`
WHERE `gk-users`.`email` != ''
GROUP BY `gk-users`.`user`
HAVING ile > '5'";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($email, $user, $drops) = $row;
    if ($user!=null) {
        $TRESC .= "$email	$user\n";
    }
}




// --------------- przenieśli



$sql = "SELECT `gk-users`.`email` , `gk-users`.`user` , COUNT( `gk-geokrety`.`id` ) AS ile
FROM `gk-geokrety`
LEFT JOIN `gk-users` ON `gk-geokrety`.`owner` = `gk-users`.`userid`
WHERE `gk-users`.`email` != ''
GROUP BY `gk-users`.`user`
HAVING `ile` > '5'";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    list($email, $user, $drops) = $row;
    if ($user!=null) {
        $TRESC .= "$email	$user\n";
    }
}


// ----------------------------- OUT ------------------------------//


file_put_contents("ruchy.dat", $TRESC);

?>
