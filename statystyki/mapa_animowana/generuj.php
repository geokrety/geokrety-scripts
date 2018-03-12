#!/usr/bin/env php

<?php

// list of GK in archived/destroyed caches

include_once("../../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");

$link = mysql_pconnect($config['host'], $config['username'], $config['pass']);
mysql_select_db ($config['db']);

$data = date("r");

$sql="SELECT date(data) as dzien FROM `gk-ruchy` where data > '2007-10-25' group by dzien";
$result = mysql_query($sql);

while ($row = mysql_fetch_row($result)) {

$dzien = $row[0];

echo "$dzien\n";

$sql2 = "SELECT `lat`, `lon` FROM `gk-ruchy` WHERE date(`data`) = '$dzien' and `lat` != ''";
$result2 = mysql_query($sql2);

while ($row2 = mysql_fetch_row($result2)) {
    list($lat, $lon)=$row2;
    $OUT.="$lon $lat\n";
}

file_put_contents("dane/$dzien.dat", $OUT);
$OUT="";
//die();

}




$result = mysql_query($sql);

?>