<?php
function geonames_country_from_coords($geonamesEndpoint, $geonamesUsername, $lat, $lon) {
    $handle = fopen("$geonamesEndpoint/countryCode?lat=$lat&lng=$lon&radius=30&username=$geonamesUsername", "rb");
    $country = strtolower(trim(fread($handle, 3)));
    echo "geonames...";
    fclose($handle);
    return $country;
}