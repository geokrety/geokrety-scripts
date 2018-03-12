#!/usr/bin/env php
<?php
//śćńółźć

include_once("../../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");
$link = mysql_pconnect($config['host'], $config['username'], $config['pass']);
mysql_select_db ($config['db']);


$IN = file("cities15000.txt");

foreach($IN as $linia){
list($id, $name, $asciiname,$alternatenames, $lat, $lon, , , $country)  = explode("\t", $linia);

//echo "$id, $name, $asciiname, $alternatenames, $lat, $lon, $country\n";

$name=mysql_real_escape_string($name);
$asciiname=mysql_real_escape_string($asciiname);
$alternatenames=mysql_real_escape_string($alternatenames);

$sql = "INSERT INTO `gk-miasta` ( `id`, `name` , `asciiname`, `alternatenames`, `lat`, `lon` , `country`)
VALUES ('$id', '$name', '$asciiname', '$alternatenames', '$lat', '$lon', '$country')";

$result = mysql_query ($sql) or die("error!\n\n" . wordwrap($sql));



}







?>