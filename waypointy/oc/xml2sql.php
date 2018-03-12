#!/usr/bin/env php
<?php
//śćńółźć



$BAZY_OC['de']['prefix'] = 'OC';
$BAZY_OC['de']['url'] = "https://www.opencaching.de/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
$BAZY_OC['de']['szukaj'] = 'https://www.opencaching.de/searchplugin.php?userinput=';


$BAZY_OC['pl']['prefix'] = 'OP';
$BAZY_OC['pl']['url'] = "https://www.opencaching.pl/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
$BAZY_OC['pl']['szukaj'] = 'https://www.opencaching.pl/searchplugin.php?userinput=';

$BAZY_OC['uk']['prefix'] = 'OK';
$BAZY_OC['uk']['url'] = "https://www.opencache.uk/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
$BAZY_OC['uk']['szukaj'] = 'https://www.opencache.uk/searchplugin.php?userinput=';


//$BAZY_OC['se']['prefix'] = 'OS';
//$BAZY_OC['se']['url'] = "http://94.255.245.234/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
//$BAZY_OC['se']['szukaj'] = 'http://www.opencaching.se/searchplugin.php?userinput=';

$BAZY_OC['us']['prefix'] = 'OU';
$BAZY_OC['us']['url'] = "http://www.opencaching.us/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
$BAZY_OC['us']['szukaj'] = 'http://www.opencaching.us/searchplugin.php?userinput=';

// $BAZY_OC['jp']['prefix'] = 'OJ';
// $BAZY_OC['jp']['url'] = "http://www.opencaching.jp/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
// $BAZY_OC['jp']['szukaj'] = 'http://www.opencaching.jp/searchplugin.php?userinput=';


// $BAZY_OC['no']['prefix'] = 'OS';
// $BAZY_OC['no']['url'] = "http://www.opencaching.no/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
// $BAZY_OC['no']['szukaj'] = 'http://www.opencaching.no/searchplugin.php?userinput=';


$BAZY_OC['nl']['prefix'] = 'OB';
$BAZY_OC['nl']['url'] = "http://www.opencaching.nl/xml/ocxml11.php?cache=1&session=0&charset=utf-8&cdata=1&xmldecl=1&ocxmltag=1&doctype=0&zip=0&modifiedsince=";
$BAZY_OC['nl']['szukaj'] = 'http://www.opencaching.nl/searchplugin.php?userinput=';


/*
// tylko odkomentowa� //
$BAZY_OC['cz']['prefix'] = 'OZ';
$BAZY_OC['cz']['url'] = "/home/geokrety/public_html/tools/oc-cz.xml";
$BAZY_OC['cz']['szukaj'] = 'http://www.opencaching.cz/searchplugin.php?userinput=';
*/


include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();
$zbior_baz = '';

foreach ($BAZY_OC as $key => $baza) {
    $zbior_baz.=" $key";

    $sql = "SELECT `timestamp` FROM `gk-waypointy` WHERE `waypoint` LIKE '" . $baza['prefix'] . "%' ORDER BY `timestamp` DESC LIMIT 1";
    $result = mysqli_query($link, $sql) or die("error 1: $id $sql");
    $row = mysqli_fetch_array($result);
    if (!empty($row)) {
        $modifiedsince = date("YmdHis", strtotime($row[0]));
    } else {
        $modifiedsince = "20030101000000";
    }

    if (isset ($GLOBALS['argv'][1]) && $GLOBALS['argv'][1] == 'full') {
        $modifiedsince = "20030101000000";
    }
    //$modifiedsince = "20160801000000";

    echo $baza['prefix'] . "\n\n";
    if ($baza['prefix'] != 'OZ') {
        $xml_raw = @file_get_contents($baza['url']. $modifiedsince);
        if ($xml_raw === FALSE) {
            continue;
        }
    } else {
        $xml_raw = file_get_contents($baza['url']);
    }

    //if(!empty($bazy['encoding'])) $xml_raw = iconv($bazy['encoding'], "UTF-8", $xml_raw);
    $xml = simplexml_load_string($xml_raw);

    //print_r($xml); die();

    if ($xml->cache) foreach ($xml->cache as $cache) {
        $id = (real) $cache->id['id'];

        $name = trim(mysqli_real_escape_string($link, strtr((string) $cache->name, array('"' => ''))));
        $owner = mysqli_real_escape_string($link, (string) $cache->userid);
        $waypoint = (string) $cache->waypoints['oc'];
        $lon = (string) $cache->longitude;
        $lat = (string) $cache->latitude;
        $typ = (string) $cache->type;
        $kraj = (string) $cache->country;
        $status = (int) $cache->status['id'];

        $linka = $baza['szukaj'] . $waypoint;

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `owner`,  `typ`, `kraj` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$owner', '$typ', '$kraj', '$linka', '$status')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `owner`='$owner', `typ`='$typ', `kraj`='$kraj', `link`='$linka', `status`='$status'";

        //echo $sql; die();
        $result = mysqli_query($link, $sql) or die("error 1: $id $sql");

        echo "$name $owner $waypoint $lon $lat $typ $kraj\n";
    }
}

file_put_contents("data-xml.txt", $zbior_baz . ";" . date("Y-m-d H:i:s"));

mysqli_close($link);
?>
