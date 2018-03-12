#! /usr/bin/php
<?php

include_once("spam_search.fn.php");

// -------------------------------------- ruchy ------------------------------- //

$koment="śćńół terefere dupa sex test tereferere kuku";

    echo "$ruch_id: ";
    $maches = checkwordblock($koment);
    if ($maches != null) {
        $log = date("r") . " (ruch_id: $ruch_id) user: $user | $maches[0]\n$koment\n";
        echo $log . "\n";
    } else {
        echo "... clear\n";
    }

?>
