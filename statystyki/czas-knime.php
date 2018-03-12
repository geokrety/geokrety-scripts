#!/usr/bin/env php

<?php

$OUT = "data;data_doy\n";


for ($y=2013; $y<=2050; $y++) {
    for ($m=1; $m<=12; $m++) {
        $data=strtotime("$y-$m-01");
        $doy=date("z", $data) + 1;
        $data_doy=$y+($doy/366);
        $OUT .= "$y-$m-01;$data_doy\n";
    }
}

file_put_contents("out/gk-predykcje-czas.csv", $OUT);



$OUT = "data;data_doy\n";
for ($y=2013; $y<=2050; $y++) {
    $data=strtotime("$y-01-01");
    $doy=date("z", $data) + 1;
    $data_doy=$y+($doy/366);
    $OUT .= "$y-01-01;$data_doy\n";
}

file_put_contents("out/gk-predykcje-czas-lata.csv", $OUT);

?>
