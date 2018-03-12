<?php

$d = dir("./");
while (false !== ($entry = $d->read())) {
echo $entry . " ..... ";

exec('c:\progs\gpsmap\cgpsmapper.exe -ieq ' . $entry);

echo "ok.\n";
}

$d->close();

?>