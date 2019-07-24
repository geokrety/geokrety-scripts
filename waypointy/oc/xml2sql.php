#!/usr/bin/env php
<?php

include_once('../../konfig-tools.php');
require_once "$geokrety_www/__sentry.php";

function prepareBindExecute(string $action, string $sql, string $bindParams = null, array $bindValues = null)
{
    return \GKDB::prepareBindExecute($action, $sql, $bindParams, $bindValues);
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
    $dir = realpath($dir);
    $pathLength = strlen($dir);
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $file_ = realpath("$dir/$file");
        if (strncmp($file_, $dir, $pathLength) !== 0) {
            throw new Exception("Deleting file '$file' would have gone out of base directory ('$dir') => '$file_'.");
        }
        (is_dir($file_)) ? deleteTree($file_) : unlink($file_);
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

function performIncrementalUpdate($changes)
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
            $sql = 'DELETE FROM `gk-waypointy` WHERE waypoint = ?';
            $stmt = prepareBindExecute('deleteWaypoint', $sql, 's', array($id));
            $stmt->close();
            $nDeleted++;
            continue;
        }

        $sql = 'SELECT lat, lon, alt, country, name, owner, typ, kraj, link, status FROM `gk-waypointy` WHERE waypoint = ?';
        $stmt = prepareBindExecute('selectWaypoint', $sql, 's', array($id));
        $stmt->store_result();
        $stmt->bind_result(
            $wptLat,
            $wptLon,
            $wptAlt,
            $wptCountry,
            $wptName,
            $wptOwner,
            $wptTyp,
            $wptKraj,
            $wptLink,
            $wptStatus
        );
        $stmt->fetch();
        $stmt->close();


        // Check for needed fields and make an update
        $sqlInsert = array();
        $sqlTypes = array();
        $sqlValues = array();
        $sqlUpdate = array();

        if (isset($change->data->names)) {
            $name = implode(' | ', (array)$change->data->names);
            if ($wptName != $name) {
                $sqlInsert [] = 'name';
                $sqlTypes [] = 's';
                $sqlValues [] = $name;
                $sqlUpdate [] = 'name = ?';
            }
        }
        if (isset($change->data->owner->username)) {
            $owner = (string)$change->data->owner->username;
            if ($wptOwner != $owner) {
                $sqlInsert [] = 'owner';
                $sqlTypes [] = 's';
                $sqlValues [] = $owner;
                $sqlUpdate [] = 'owner = ?';
            }
        }
        if (isset($change->data->location)) {
            $location = explode('|', $change->data->location);
            $lat = number_format(floatval($location[0]), 5, '.', '');
            $lon = number_format(floatval($location[1]), 5, '.', '');

            $coordDiffer = false;
            if (strval($wptLat) != strval($lat) or strval($wptLon) != strval($lon)) {
                $coordDiffer = true;

                $sqlInsert [] = 'lon';
                $sqlTypes [] = 'd';
                $sqlValues [] = $lon;
                $sqlUpdate [] = 'lon = ?';

                $sqlInsert [] = 'lat';
                $sqlTypes [] = 'd';
                $sqlValues [] = $lat;
                $sqlUpdate [] = 'lat = ?';
            }

            if ($coordDiffer or is_null($wptCountry) or $wptCountry === '' or $wptCountry == 'xyz' or $wptCountry == 'err') {
                $countryCode = \Geokrety\Service\CountryService::getCountryCode(array('lat' => $lat, 'lon' => $lon));
                $sqlInsert [] = 'country';
                $sqlTypes [] = 's';
                $sqlValues [] = $countryCode;
                $sqlUpdate [] = 'country = ?';
            }

            if ($coordDiffer or is_null($wptAlt) or $wptAlt === '' or $wptAlt < -2000) { // TODO replace with class constant
                $elevation = \Geokrety\Service\ElevationService::getElevation(array('lat' => $lat, 'lon' => $lon));
                $sqlInsert [] = 'alt';
                $sqlTypes [] = 'i';
                $sqlValues [] = $elevation;
                $sqlUpdate [] = 'alt = ?';
            }
        }
        if (isset($change->data->type)) {
            $type = (string)$change->data->type;
            if ($wptTyp != $type) {
                $sqlInsert [] = 'typ';
                $sqlTypes [] = 's';
                $sqlValues [] = $type;
                $sqlUpdate [] = 'typ = ?';
            }
        }
        if (isset($change->data->country)) {
            $country = (string)$change->data->country;
            if ($wptKraj != $country) {
                $sqlInsert [] = 'kraj';
                $sqlTypes [] = 's';
                $sqlValues [] = $country;
                $sqlUpdate [] = 'kraj = ?';
            }
        }
        if (isset($change->data->url)) {
            $url = (string)$change->data->url;
            if ($wptLink != $url) {
                $sqlInsert [] = 'link';
                $sqlTypes [] = 's';
                $sqlValues [] = $url;
                $sqlUpdate [] = 'link = ?';
            }
        }
        if (isset($change->data->status)) {
            switch ((string)$change->data->status) {
                case 'Available':
                    $status = 1;
                    break;
                case 'Temporarily unavailable':
                    $status = 2;
                    break;
                case 'Archived':
                    $status = 3;
                    break;
                default:
                    $status = 0;
            }
            if ($wptStatus != $status) {
                $sqlInsert [] = 'status';
                $sqlTypes [] = 'i';
                $sqlValues [] = $status;
                $sqlUpdate [] = 'status = ?';
            }
        }

        if (sizeof($sqlInsert) > 0) {
            // It can happen that changelog contains useless fields like "founds" which we are not interested in.
            // So we need to trigger actual update only if at least one of our fields was changes.

            $sqlInsert [] = 'waypoint';
            $sqlTypes [] = 's';
            $sqlValues [] = $id;
            $sqlUpdate [] = 'waypoint = ?';

            $questionMarks = array_fill(0, count($sqlValues), '?');

            $insertPart = implode(', ', $sqlInsert);
            $valuesPart = implode(', ', $questionMarks);
            $onDupPart = implode(', ', $sqlUpdate);
            $sql = "INSERT INTO `gk-waypointy` ($insertPart) VALUES ($valuesPart) ON DUPLICATE KEY UPDATE $onDupPart";
            $stmt = prepareBindExecute('insertOrUpdateWaypoint', $sql, str_repeat(implode('', $sqlTypes), 2), array_merge($sqlValues, $sqlValues));
            $stmt->close();
            $nUpdated++;
        }
    }

    echo " *    updated:" . $nUpdated . "\n";
    echo " *    deleted:" . $nDeleted . "\n";
}

function insertFromFullDump($folder)
{
    $index = json_decode(file_get_contents($folder . '/index.json'));
    $revision = $index->revision;

    foreach ($index->data_files as $piece) {
        $changes = json_decode(file_get_contents($folder . '/' . $piece));
        performIncrementalUpdate($changes);
    }
    return $revision;
}

function getLastUpdate($service)
{
    $sql = 'SELECT last_update FROM `gk-waypointy-sync` WHERE service_id = ?';
    $stmt = prepareBindExecute('getLastUpdate', $sql, 's', array($service));
    $stmt->store_result();
    if ($stmt->num_rows !== 0) {
        $stmt->bind_result($last_update);
        $stmt->fetch();
        $stmt->close();
        return $last_update;
    }

    // Seems like no such key is present. Let's add it
    $sql = 'INSERT INTO `gk-waypointy-sync` (service_id) VALUES (?)';
    $stmt = prepareBindExecute('initLastUpdate', $sql, 's', array($service));
    $stmt->close();
    return null;
}

function setLastUpdate($service, $lastUpdate)
{
    echo " *    new rev:" . $lastUpdate . "\n";
    $sql = 'UPDATE `gk-waypointy-sync` SET last_update = ? WHERE service_id = ?';
    $stmt = prepareBindExecute('setLastUpdate', $sql, 'ss', array($lastUpdate, $service));
    $stmt->close();
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
    // Maybe use exec on linux?
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
    if (isset($GLOBALS['argv'][1]) && $GLOBALS['argv'][1] == 'full') {
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

    if ($fullResync) {
        $fullDumpPath = 'oc_dump_extracted';
        try {
            saveAndExtractDumpFile($downloadUrl, $fullDumpPath);
        } catch (Exception $e) {
            print("Cannot get full dump for " . $key . "\n");
            print($e->getMessage());
            continue;
        }

        $revision = insertFromFullDump($fullDumpPath);
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
                performIncrementalUpdate($changes);
            }
            $revision = $json->revision;
            $more = $json->more;
            setLastUpdate($key, $revision);
        }
    }
}
