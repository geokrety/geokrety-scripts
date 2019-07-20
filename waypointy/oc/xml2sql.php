#!/usr/bin/env php
<?php

require_once "$geokrety_www/__sentry.php";

function DBPConnect()
{
    //return mysqli_connect('localhost', 'root', '', 'geokrety-db');
    return GKDB::getLink();
}

/**
 * Deletes whole folder tree including files inside
 *
 * @param $dir string path to delete
 * @return void
 */
function deleteTree($dir)
{
    if (empty($dir) or !$dir) {
        return;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteTree("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}

/**
 *
 * Copies the file from $url to $output, supports both file paths and urls
 *
 * @param $url string path to source file or URL
 * @param $output string path to output file or stream
 * @throws Exception if something goes wrong
 */
function downloadFile($url, $output)
{
    $readableStream = fopen($url, 'rb');
    if ($readableStream === false) {
        throw new Exception("Something went wrong while fetching " . $url);
    }
    $writableStream = fopen($output, 'wb');

    stream_copy_to_stream($readableStream, $writableStream);

    fclose($writableStream);
}

function performIncrementalUpdate($link, $changes)
{
    echo " *      total:" . sizeof($changes) . "\n";
    $nUpdated = 0;
    $nDeleted = 0;

    foreach ($changes as $change) {

        if ($change->object_type != 'geocache') {
            continue;
        }

        $id = $change->object_key->code;

        if ($change->change_type == 'delete') {
            //delete from DB

            $sql = 'DELETE FROM `gk-waypointy` WHERE waypoint= ?';
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, 's', $id);

            if ($stmt->execute() === false) { // ooooPs we got an import error !
                print("error sql : id:$id - query:$sql - mysqli_error:" . mysqli_error($link) . "\n");
            }
            $nDeleted++;
            continue;
        }

        // Check for needed fields and make an update
        $sqlInsert = array();
        $sqlValues = array();
        $sqlUpdate = array();

        if (isset($change->data->names)) {
            $name = mysqli_real_escape_string($link, implode(' | ', (array)$change->data->names));
            $sqlInsert [] = 'name';
            $sqlValues [] = "'$name'";
            $sqlUpdate [] = "name='$name'";
        }
        if (isset($change->data->owner->username)) {
            $owner = mysqli_real_escape_string($link, (string)$change->data->owner->username);
            $sqlInsert [] = 'owner';
            $sqlValues [] = "'$owner'";
            $sqlUpdate [] = "owner='$owner'";
        }
        if (isset($change->data->location)) {
            $location = explode('|', $change->data->location);
            $lon = floatval($location[0]);
            $lat = floatval($location[1]);

            $sqlInsert [] = 'lon';
            $sqlValues [] = "$lon";
            $sqlUpdate [] = "lon=$lon";

            $sqlInsert [] = 'lat';
            $sqlValues [] = "$lat";
            $sqlUpdate [] = "lat=$lat";
        }
        if (isset($change->data->type)) {
            $type = mysqli_real_escape_string($link, (string)$change->data->type);
            $sqlInsert [] = 'typ';
            $sqlValues [] = "'$type'";
            $sqlUpdate [] = "typ='$type'";
        }
        if (isset($change->data->country)) {
            $country = mysqli_real_escape_string($link, (string)$change->data->country);
            $sqlInsert [] = 'kraj';
            $sqlValues [] = "'$country'";
            $sqlUpdate [] = "kraj='$country'";
        }
        if (isset($change->data->url)) {
            $url = mysqli_real_escape_string($link, (string)$change->data->url);
            $sqlInsert [] = 'link';
            $sqlValues [] = "'$url'";
            $sqlUpdate [] = "link='$url'";
        }

        if (sizeof($sqlInsert) > 0) {
            // It can happen that changelog contains useless fields like "founds" which we are not interested in.
            // So we need to trigger actual update only if at least one of our fields was changes.

            $sqlInsert [] = 'waypoint';
            $sqlValues [] = "'$id'";
            $sqlUpdate [] = "waypoint='$id'";

            $insertPart = '(' . implode(',', $sqlInsert) . ')';
            $valuesPart = '(' . implode(',', $sqlValues) . ')';
            $onDupPart = implode(',', $sqlUpdate);
            $sql = 'INSERT INTO `gk-waypointy` ' . $insertPart . ' VALUES ' . $valuesPart . ' ON DUPLICATE KEY UPDATE ' . $onDupPart;

            $result = mysqli_query($link, $sql);

            if ($result === false) { // ooooPs we got an import error !
                print("error sql : id:$id - query:$sql - mysqli_error:" . mysqli_error($link) . "\n");
            }
            $nUpdated++;
        }
    }

    echo " *    updated:" . $nUpdated . "\n";
    echo " *    deleted:" . $nDeleted . "\n";
}


function insertFromFullDump($link, $folder)
{
    $index = json_decode(file_get_contents($folder . '/index.json'));
    $revision = $index->revision;

    foreach ($index->data_files as $piece) {
        $changes = json_decode(file_get_contents($folder . '/' . $piece));
        performIncrementalUpdate($link, $changes);
    }
    return $revision;
}

function getLastUpdate($service)
{
    $link = DBPConnect();
    $sql = "SELECT `last_update` FROM `gk-waypointy-sync` WHERE `service_id` = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $service);
    $stmt->execute();
    $stmt->bind_result($row);
    $stmt->fetch();

    if ($row === null) {
        // Seems like no such key is present. Let's add it
        $sql = "INSERT INTO `gk-waypointy-sync` (service_id) VALUES (?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 's', $service);
        mysqli_stmt_execute($stmt);
    }
    return $row;
}

function setLastUpdate($service, $lastUpdate)
{
    echo " *    new rev:" . $lastUpdate . "\n";
    $link = DBPConnect();
    $sql = "UPDATE `gk-waypointy-sync` SET `last_update`=? WHERE `service_id` = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $lastUpdate, $service);
    $stmt->execute();
}

/**
 * Downloads .tar.bz2 archive from $url and extracts it to $output
 *
 * @param $url string source URL
 * @param $output string folder path where to extract
 * @throws Exception if something goes wrong
 */
function saveAndExtractDumpFile($url, $output)
{
    $temp = "tempfile.tar.bz2";
    downloadFile($url, $temp);
    // full dump gives .tar.bz2 file...

    // Save bz2 file
    $bz = bzopen($temp, "r");
    $res = fopen("tempfile.tar", "w");
    $copied = stream_copy_to_stream($bz, $res);
    fclose($res);
    fclose($bz);

    // Extract tar
    //For at least .pl Phar complains that archive is corrupted :(
    // Maybe use exec on lin?
    //exec('mkdir TestBlable && tar -C TestBlable -xvf tempfile.tar');

    $phar = new PharData('tempfile.tar');
    $phar->extractTo($output); // extract all files

    // Finally we got 2+ Gb of random json files. Let's parse
}


if (getenv("OC_PL_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_PL']['key'] = getenv("OC_PL_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_PL']['url'] = "https://opencaching.pl/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_PL']['key'] . "&since=";
    $BAZY_OC['OC_PL']['full_url'] = "https://opencaching.pl/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_PL']['key'];
// Also can be local path
#$BAZY_OC['OC_PL']['full_url'] = "C:\Users\Downloads\okapi-dump-r7198164.tar.bz2";
}

if (getenv("OC_DE_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_DE']['key'] = getenv("OC_DE_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_DE']['url'] = "https://www.opencaching.de/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_DE']['key'] . "&since=";
    $BAZY_OC['OC_DE']['full_url'] = "https://www.opencaching.de/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_DE']['key'];
}

if (getenv("OC_UK_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_UK']['key'] = getenv("OC_UK_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_UK']['url'] = "https://www.opencache.uk/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_UK']['key'] . "&since=";
    $BAZY_OC['OC_UK']['full_url'] = "https://www.opencache.uk/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_UK']['key'];
}

if (getenv("OC_US_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_US']['key'] = getenv("OC_US_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_US']['url'] = "http://www.opencaching.us/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_US']['key'] . "&since=";
    $BAZY_OC['OC_US']['full_url'] = "http://www.opencaching.us/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_US']['key'];
}

if (getenv("OC_NL_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_NL']['key'] = getenv("OC_NL_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_NL']['url'] = "https://www.opencaching.nl/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_NL']['key'] . "&since=";
    $BAZY_OC['OC_NL']['full_url'] = "https://www.opencaching.nl/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_NL']['key'];
}

if (getenv("OC_RO_OKAPI_CONSUMER_KEY")) {
    $BAZY_OC['OC_RO']['key'] = getenv("OC_RO_OKAPI_CONSUMER_KEY");
    $BAZY_OC['OC_RO']['url'] = "https://www.opencaching.ro/okapi/services/replicate/changelog?consumer_key=" . $BAZY_OC['OC_RO']['key'] . "&since=";
    $BAZY_OC['OC_RO']['full_url'] = "https://www.opencaching.ro/okapi/services/replicate/fulldump?pleeaase=true&consumer_key=" . $BAZY_OC['OC_RO']['key'];
}

/*
// Czech OC is super old :(
$BAZY_OC['cz']['prefix'] = 'OZ';
$BAZY_OC['cz']['url'] = "/home/geokrety/public_html/tools/oc-cz.xml";
$BAZY_OC['cz']['szukaj'] = 'http://www.opencaching.cz/searchplugin.php?userinput=';
*/


$totalUpdated = 0;
$totalErrors = 0;

foreach ($BAZY_OC as $key => $baza) {
    $nbImported = 0;
    $nbInsertOrUpdate = 0;
    $nbError = 0;

    $downloadUrl = $baza['full_url'];
    if (isset ($GLOBALS['argv'][1]) && $GLOBALS['argv'][1] == 'full') {
        $fullResync = true;
    } else {
        $lastUpdate = getLastUpdate($key);
        if (!empty($lastUpdate)) {
            $downloadUrl = $baza['url'] . $lastUpdate;
            $fullResync = false;
        } else {
            $fullResync = true;
        }
    }

    echo " * processing " . $key . "\n";
    if ($fullResync) {
        echo " *  FULL SYNC\n";
        echo " *        url:" . $downloadUrl . "\n";
    }

    $success = true;
    if ($fullResync) {
        $fullDumpPath = 'oc_dump_extracted';
        try {
            saveAndExtractDumpFile($downloadUrl, $fullDumpPath);
        } catch (Exception $e) {
            print("Cannot get full dump for " . $key . "\n");
            print($e->getMessage());
            continue;
        }

        // Connect to DB only when dump is ready, because downloading and extracting takes a lot of time
        $link = DBPConnect();
        $revision = insertFromFullDump($link, $fullDumpPath);
        mysqli_close($link);
        deleteTree($fullDumpPath);
        setLastUpdate($key, $revision);
    } else {
        $more = true;
        $revision = $lastUpdate;

        // Since amount of results per revision is limited, need to iterate quite a lot of times for big OC websites.
        while ($more) {
            $downloadUrl = $baza['url'] . $revision;
            echo " *        url:" . $downloadUrl . "\n";
            $raw = file_get_contents($downloadUrl);
            if ($raw === false) {
                print("Cannot get incremental dump for " . $key . "\n");
                print("Maybe `since` is too old, check full resync or try again\n");
                break;
            }
            $json = json_decode($raw);
            $changes = $json->changelog;

            if (sizeof($changes) > 0) {
                $link = DBPConnect();
                performIncrementalUpdate($link, $changes);
                mysqli_close($link);
            }
            $revision = $json->revision;
            $more = $json->more;
            setLastUpdate($key, $revision);
        }
    }
}

?>
