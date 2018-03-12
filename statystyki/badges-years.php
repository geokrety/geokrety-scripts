#!/usr/bin/env php

# 1) zrobić listę
# 2) top 10 rzucaczy - już bez listy
# 3) top 10-100 wrzucaczy - też bez listy

<?php

$rok='2017';
$top_ile=100;
$rank = 10;

$headers = 'From: GeoKrety <geokrety@gmail.com>' . "\r\n";
$headers .= 'Return-Path: <geokrety@gmail.com>' . "\r\n";


include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$data = date("r");

$sql ="SELECT `gk-users`.`user`, `gk-users`.`email`, `gk-users`.`wysylacmaile` , `gk-ruchy`.`user` , COUNT( `gk-ruchy`.`ruch_id` ) AS ile
FROM `gk-ruchy`
LEFT JOIN `gk-users` ON `gk-ruchy`.`user` = `gk-users`.`userid`
WHERE (`gk-ruchy`.`logtype` = '0' OR `gk-ruchy`.`logtype` = '5') and year(data)  = '$rok' and `gk-users`.`userid` !=0
AND `gk-ruchy`.`id`
IN (
SELECT `id`
FROM `gk-geokrety`
WHERE `typ` != '2'
)
GROUP BY `gk-users`.`user`
ORDER BY ile DESC

LIMIT 10, 90";  // dla top 100 i wtedy top_ile=100
//LIMIT 100";
//LIMIT 10";

$result = mysqli_query($link, $sql);


while ($row = mysqli_fetch_row($result)) {
    list($nick, $email, $wysylac, $userid, $count) = $row;
    echo "$nick $userid $count\n";

    $rank++;
    $stats.="<tr><td>$rank</td><td><a href='/mypage.php?userid=$userid'>$nick</a></td><td>$count</td></tr>\n";

    /*
    $tresc="Hi $nick,

It is our pleasure to inform, that you are among
top $top_ile droppers in $rok (with $count moves, rank #$rank)
please look at this new pretty badge at your GK profile:
https://geokrety.org/mypage.php

Full list of $rok top \"droppers\":
https://geokrety.org/statystyczka-lata.php?rok=$rok

thanks!

Your GeoKrety Team :)
";



    echo $tresc;


    if($wysylac==1){
    //   $email = "stefaniak@gmail.com";
    //   $email = "mathieu@alorent.com";
       mail($email, "[GeoKrety] Top $top_ile droppers in $rok!", $tresc, $headers);
       $sleep = rand(1, 10);
       echo "($rank) Sleep $sleep\n";
       sleep($sleep);
    }


    $sql_ins="INSERT INTO `gk-badges` (
    `userid` ,
    `timestamp` ,
    `desc` ,
    `file`
    )
    VALUES (
    '$userid',
    CURRENT_TIMESTAMP , 'Top $top_ile droppers in $rok ($count, rank #$rank)', 'top${top_ile}-mover-$rok.png'
    );";

    $result2 = mysqli_query($link, $sql_ins);


    */


}
//if ($stats!='') {
//    file_put_contents("$geokrety_www/templates/stats/year/$rok.html", $stats);
//}

?>
