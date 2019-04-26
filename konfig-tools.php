<?php

$geokrety_www = "/var/www/html/";

// export bypass
$kocham_kaczynskiego = isset($_ENV['EXPORT_BYPASS_TOKEN']) ? $_ENV['EXPORT_BYPASS_TOKEN'] : 'xxx';

// geonames
$geonamesEndpoint = 'http://api.geonames.org';
$geonamesUsername = isset($_ENV['GEONAMES_USERNAME']) ? $_ENV['GEONAMES_USERNAME'] : 'xxx';

# Override configs from file
is_file($geokrety_www.'/konfig-tools.local.php') and require $geokrety_www.'/konfig-tools.local.php';
