#!/usr/bin/env php

<?php

$t1=microtime(true);

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


// -------------------------------------------------------- MEDIANA ----------------------------------- //
$medianaSQL = "SELECT t1.droga as median_val FROM (
SELECT @rownum:=@rownum+1 as `row_number`, d.droga
  FROM `gk-ruchy` as d,  (SELECT @rownum:=0) r
  WHERE d.droga > 0 and d.logtype in ('0', '5')
  ORDER BY d.droga
) as t1,
(
  SELECT count(*) as total_rows
  FROM `gk-ruchy` as d
  WHERE d.droga > 0 and d.logtype in ('0', '5')
) as t2
WHERE t1.row_number=floor(total_rows/2)+1;";

$result = mysqli_query($link, $medianaSQL);

$row = mysqli_fetch_row($result); $mediana = $row[0];
echo "Mediana: $mediana\n";
// -------------------------------------------------------- SREDNIA ----------------------------------- //

$sredniaSQL="SELECT round(avg(droga)) FROM `gk-ruchy` WHERE droga > 0 and logtype in ('0', '5')";
$result = mysqli_query($link, $sredniaSQL);

$row = mysqli_fetch_row($result); $srednia = $row[0];
echo "Średnia: $srednia\n";

// ============================================ update SQL ================================== //

$sql = "UPDATE `gk-wartosci` SET `value` = '$mediana' WHERE `gk-wartosci`.`name` = 'droga_mediana';";
$result = mysqli_query($link, $sql);

$sql = "UPDATE `gk-wartosci` SET `value` = '$srednia' WHERE `gk-wartosci`.`name` = 'droga_srednia';";
$result = mysqli_query($link, $sql);

$t2 = microtime(true);
echo "Czas wykonania: " . ($t2 - $t1) . "\n";
mysqli_close($link);
?>
