#!/usr/bin/env php

<?php

#die("Disabled by kumy 201701006");


$ODKIEDY = 24; // jak stare niusy i dane (ile godzin)
$godzina = date("G");
$NOW = date('Y-m-d H:00:00');
$BEFORE = date('Y-m-d H:00:00', strtotime("-$ODKIEDY hours", strtotime($NOW)));

// ****************************************************************************
// ****************************************************************************
// ****************************************************************************
$LOCAL_DEBUG = false;  // <- wyremowac na serwerze !!!!! wartosc true jest do uruchamiania lokalnie u simora
$speedtest_obserwacje = false;
// ****************************************************************************
// ****************************************************************************
// ****************************************************************************


// ****************************************************************************
if ($LOCAL_DEBUG) {
    $ODKIEDY = 8900;//rand(5000,10000);
    $BEFORE = date('Y-m-d H:00:00', strtotime("-$ODKIEDY hours", strtotime($NOW)));
    $godzina = 8;
    echo "<br/>godzina=$godzina before=$BEFORE</br>";

    $geokrety_www = "/var/www/html/";

    $speedtest_obserwacje = false;

    if ($speedtest_obserwacje) {
        $speedtest_obserwacje_maxtime = 5; //seconds
        $debugecho_obserwacje = 1    ;

        if ($speedtest_obserwacje) {
            include_once('speedtest.php');
            $st_obserwacje=new SpeedTest;
            include_once('defektoskop.php');
        }
    }
} else {
    require_once("konfig-tools.php");  // $geokrety_www
}

require_once "$geokrety_www/__sentry.php";

// ****************************************************************************

$footer = "If you don't want to recive email alerts, edit your <a href='https://geokrety.org/edit.php?co=email'>mail preferences</a>.";

require_once("$geokrety_www/templates/konfig.php");
require_once("$geokrety_www/waypoint_info.php");
require_once("$geokrety_www/recent_moves.php");
require_once("$geokrety_www/recent_comments_fn.php");
require_once("$geokrety_www/fn_latlon.php");

$katalog_rss = "$geokrety_www/rss";

function ustaw_jezyk($lang)
{
    if ($lang=='') {
        $lang='en_US.UTF-8';
    }

    //putenv("LANG=$lang");       //for windows only

    $newLocale = setlocale(LC_MESSAGES, $lang);
    if (false === $newLocale || $newLocale != $lang) {
        # Second try
        sleep(1);
        $newLocale2 = setlocale(LC_MESSAGES, $lang);
        if (false === $newLocale2 || $newLocale2 != $lang) {
            echo "Changing locale failed for $lang | $newLocale | $newLocale2";
        }
    }

    setlocale(LC_TIME, $lang);
    setlocale(LC_NUMERIC, 'en_US');
    bindtextdomain("messages", BINDTEXTDOMAIN_PATH);
    bind_textdomain_codeset("messages", 'UTF-8');
    textdomain("messages");
}

$obserwacje_log = '';
function add_to_obserwacje_log($desc)
{
    global $obserwacje_log, $LOCAL_DEBUG;
    $a = date("H:i:s", time()) . " $desc <br/>";
    if ($LOCAL_DEBUG) {
        echo $a;
    }
    $obserwacje_log .= $a;
}

$link = DBPConnect();

add_to_obserwacje_log("START");

$regex = "<h2>([^<]+)<\/h2>"; //lang debug

// -------------------------------------- get some useful variables ------------------------------- //


$poczatkowe_ruch_id = mysqli_query($link, "select ruch_id from `gk-ruchy` where `data_dodania` >= '$BEFORE' order by ruch_id asc limit 1")->fetch_object()->ruch_id;;


// -------------------------------------- news ------------------------------- //


$sql = "SELECT DATE(`date`), `tresc`, `who` FROM `gk-news` WHERE (TIMESTAMPDIFF(HOUR , `date`, NOW( ))<168) ORDER BY `date` DESC"; // newsy niech sie pokazuja przez tydzien
$result = mysqli_query($link, $sql);
$num_rows = mysqli_num_rows($result);
$news = '';
$dane_my = [];

while ($row = mysqli_fetch_array($result)) {
    list($date, $tresc, $who) = $row;
    $news .= "<p><strong>$date</strong> ($who)<br />$tresc</p>\n";
}

if ($news!='') {
    $news = '<h2>' . _("News") . '</h2>' . $news;
}

add_to_obserwacje_log("Finished NEWS");
if ($speedtest_obserwacje) {
    $ST1='obserwacje_news';
    $ST2=$st_obserwacje->stop_show();
    $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)";
    if ($ST2>$speedtest_obserwacje_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_obserwacje) {
        echo $ST3.'<br/>';
    }
}


// ------------------------------------------ my geokrets ------------------------------- //


$result = mysqli_query($link, "SELECT DISTINCT `gk-geokrety`.owner, `gk-users`.`lang` FROM `gk-geokrety`
JOIN `gk-ruchy` ON `gk-geokrety`.id = `gk-ruchy`.id
JOIN `gk-users` ON `gk-users`.`userid`= `gk-geokrety`.owner
WHERE `gk-ruchy`.ruch_id>=$poczatkowe_ruch_id
AND `gk-users`.`godzina`='$godzina'
AND `gk-users`.`wysylacmaile`='1'
AND `gk-users`.`email_invalid`!=1
");
$num_rows = mysqli_num_rows($result);

while ($row = mysqli_fetch_row($result)) {
    list($userid, $lang) = $row;
    $gk_lang[$userid] = isset($gk_lang[$userid]) ? $gk_lang[$userid] : '';
    $lang = $lang?: 'en';
    $gk_lang[$userid] = sprintf("0:%s %s ", $lang, $config_jezyk_encoding[$lang]);
    ustaw_jezyk($config_jezyk_encoding[$lang]);
    $gk_lang[$userid] .= _('Language');
    $dane_my[$userid] = recent_moves("WHERE gk.owner='$userid' AND ru.ruch_id >= $poczatkowe_ruch_id", 50, _("Recent moves of my geokrets"), '', false, true);
    if (preg_match("#".$regex."#i", $dane_my[$userid], $matches)) {
        $gk_lang[$userid] .= sprintf(" [%s] ", $matches[1]);
    }
}

add_to_obserwacje_log("Finished MY (".count($dane_my)." users)");
if ($speedtest_obserwacje) {
    $ST1='obserwacje_my';
    $ST2=$st_obserwacje->stop_show();
    $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)";
    if ($ST2>$speedtest_obserwacje_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_obserwacje) {
        echo $ST3.'<br/>';
    }
}


// ------------------------------------------ observed geokrets ------------------------------- //


$result = mysqli_query($link, "SELECT `gk-obserwable`.userid, `gk-users`.`lang`
FROM `gk-obserwable`
JOIN `gk-ruchy` ON `gk-obserwable`.id = `gk-ruchy`.id
JOIN `gk-users` ON `gk-users`.`userid`= `gk-obserwable`.userid
WHERE `gk-ruchy`.ruch_id>=$poczatkowe_ruch_id
AND `gk-users`.`godzina`='$godzina'
AND `gk-users`.`wysylacmaile`='1'
AND `gk-users`.`email_invalid`!=1
GROUP BY `gk-obserwable`.userid");
$num_rows = mysqli_num_rows($result);

$dane_obs = array();
while ($row = mysqli_fetch_row($result)) {
    list($userid, $lang) = $row;
    $gk_lang[$userid] = isset($gk_lang[$userid]) ? $gk_lang[$userid] : '';
    $lang = $lang ?: 'en';
    $zapytanie = "SELECT ru.ruch_id, ru.id, ru.lat, ru.lon, ru.country, ru.waypoint, ru.droga, ru.data, ru.user, ru.koment, ru.logtype, ru.username, us.user, gk.nazwa, gk.typ, gk.owner, pic.plik, ru.zdjecia
				FROM `gk-ruchy` ru
				LEFT JOIN `gk-obserwable` ob ON (ru.id = ob.id)
				LEFT JOIN `gk-users` us ON (ru.user = us.userid)
				LEFT JOIN `gk-geokrety` gk ON (ru.id = gk.id)
				LEFT JOIN `gk-obrazki` AS pic ON (gk.avatarid = pic.obrazekid)
				WHERE ob.userid = '$userid' AND ru.ruch_id >= $poczatkowe_ruch_id
				ORDER BY ru.`data` DESC , ru.`data_dodania` DESC LIMIT 50";

    $gk_lang[$userid] .= sprintf(", 1:%s %s ", $lang, $config_jezyk_encoding[$lang]);
    ustaw_jezyk($config_jezyk_encoding[$lang]);
    $gk_lang[$userid] .= _('Language');
    $dane_obs[$userid] = recent_moves("", 50, _("Watched geokrets"), $zapytanie, false, true);
    if (preg_match("#".$regex."#i", $dane_obs[$userid], $matches)) {
        $gk_lang[$userid] .= sprintf(" [%s] ", $matches[1]);
    }
}

add_to_obserwacje_log("Finished WATCHED (".count($dane_obs)." users)");
if ($speedtest_obserwacje) {
    $ST1='obserwacje_watched';
    $ST2=$st_obserwacje->stop_show();
    $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)";
    if ($ST2>$speedtest_obserwacje_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_obserwacje) {
        echo $ST3.'<br/>';
    }
}


// ------------------------------------------ comments ------------------------------- //


//$result = mysqli_query($link, "SELECT userid FROM `gk-users` WHERE wysylacmaile='1' AND `gk-users`.`email_invalid`!=1 AND godzina='$godzina'");

/*
wybieramy uzytkownikow:
1. ktorzy napisali komentarz +
2. ktorych kret dostal komentarz +
3. ktorych obserwowany kret dostal komentarz
*/

$result = mysqli_query($link, "
SELECT DISTINCT(user_id), lang
FROM (
	SELECT user_id AS user_id
	FROM `gk-ruchy-comments` co

	UNION

	SELECT gk.owner AS user_id
	FROM `gk-ruchy-comments` co
	JOIN `gk-geokrety` gk ON ( gk.id = co.kret_id )

	UNION

	SELECT ob.userid AS user_id
	FROM `gk-ruchy-comments` co
	JOIN `gk-obserwable` ob ON ( ob.id = co.kret_id )
)x
JOIN `gk-users` us ON ( x.user_id = us.userid )
WHERE us.wysylacmaile='1'
AND us.godzina='$godzina'
AND us.`email_invalid`!=1
");

$num_rows = mysqli_num_rows($result);

while ($row = mysqli_fetch_row($result)) {
    list($userid, $lang) = $row;
    $gk_lang[$userid] = isset($gk_lang[$userid]) ? $gk_lang[$userid] : '';
    $lang = $lang ?: 'en';
    $gk_lang[$userid] .= sprintf(", 2:%s %s ", $lang, $config_jezyk_encoding[$lang]);
    ustaw_jezyk($config_jezyk_encoding[$lang]);
    $gk_lang[$userid] .= _('Language');

    $zapytanie="
	SELECT co.comment_id, co.type, co.comment, co.timestamp, co.kret_id, gk.nazwa, gk.owner, '', us.userid, us.user, co.ruch_id
	FROM `gk-ruchy-comments` co
	LEFT JOIN `gk-users` us ON ( us.userid = co.user_id )
	LEFT JOIN `gk-geokrety` gk ON ( gk.id = co.kret_id )
	WHERE (co.data_dodania > '$BEFORE') AND
		(
			ruch_id IN (SELECT co2.ruch_id FROM `gk-ruchy-comments` co2
			WHERE co2.user_id = '$userid')
		OR
			gk.id IN (SELECT id FROM `gk-obserwable` WHERE userid='$userid')
		OR
			gk.owner='$userid'
		)
	ORDER BY co.comment_id ASC";


    $dane_gk_comments[$userid] = recent_comments('', 100, _('Recent log comments'), $zapytanie, 0, 1);
    if (preg_match("#".$regex."#i", $dane_gk_comments[$userid], $matches)) {
        $gk_lang[$userid] .= sprintf(" [%s] ", $matches[1]);
    }
}

add_to_obserwacje_log("Finished COMMENTS (".count($dane_gk_comments)." users)");
if ($speedtest_obserwacje) {
    $ST1='obserwacje_comments';
    $ST2=$st_obserwacje->stop_show();
    $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)";
    if ($ST2>$speedtest_obserwacje_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_obserwacje) {
        echo $ST3.'<br/>';
    }
}


// ------------------------------------------ search within a radius ------------------------------- //


$result = mysqli_query($link, "SELECT `userid`, `lang`, `lat`, `lon`, `promien`
FROM `gk-users`
WHERE (`lat` IS NOT NULL)
AND (`promien`>0)
AND (`godzina`='$godzina')
AND (`wysylacmaile`='1')
AND (`email_invalid`!=1)
");
$num_rows = mysqli_num_rows($result);

while ($row = mysqli_fetch_row($result)) {
    list($userid, $lang, $lat, $lon, $promien) = $row;
    $gk_lang[$userid] = isset($gk_lang[$userid]) ? $gk_lang[$userid] : '';
    $lang = $lang ?: 'en';
    $gk_lang[$userid] .= sprintf(", 3:%s %s ", $lang, $config_jezyk_encoding[$lang]);
    ustaw_jezyk($config_jezyk_encoding[$lang]);
    $gk_lang[$userid] .= _('Language');

    //trzeba to kiedys poprawic dla rejonow przy 180 deg.
    getLatLonBox($lat, $lon, $promien*1000, $dlat, $dlon);
    $lat1 = $lat - $dlat;
    $lat2 = $lat + $dlat;
    $lon1 = $lon - $dlon;
    $lon2 = $lon + $dlon;

    $dane_radius[$userid] = recent_moves("WHERE (ru.lat IS NOT NULL) AND (ru.lat>=$lat1) AND (ru.lat<=$lat2) AND (ru.lon>=$lon1) AND (ru.lon<=$lon2) AND (ru.ruch_id > $poczatkowe_ruch_id)", 50, _("Recent logs near my home location"), '', false, true);
    //if($speedtest_obserwacje) {$ST1="user $userid";$ST2=$st_obserwacje->stop_show_start(); $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)"; if($ST2>$speedtest_obserwacje_maxtime) {errory_add($ST3,50);} 	if($debugecho_obserwacje) {echo $ST3.'<br/>';}}
    if (preg_match("#".$regex."#i", $dane_radius[$userid], $matches)) {
        $gk_lang[$userid] .= sprintf(" [%s] ", $matches[1]);
    }
}

add_to_obserwacje_log("Finished RADIUS (".count($dane_radius)." users)");
if ($speedtest_obserwacje) {
    $ST1='obserwacje_radius';
    $ST2=$st_obserwacje->stop_show();
    $ST3='ST='.$ST2.'s - '.$ST1." ($num_rows records)";
    if ($ST2>$speedtest_obserwacje_maxtime) {
        errory_add($ST3, 50);
    }
    if ($debugecho_obserwacje) {
        echo $ST3.'<br/>';
    }
}


// ------------------------------------------ concatenate all arrays into one ------------------------------- //

$dane = [];
if (isset($dane_my)) {
    foreach ($dane_my as $userid=>$value) {
        if (!empty($value)) {
            if (array_key_exists($userid, $dane)) {
                $dane[$userid] .= $value; // Technically unsed ;)
            } else {
                $dane[$userid] = $value;
            }
        }
    }
}

if (isset($dane_obs)) {
    foreach ($dane_obs as $userid=>$value) {
        if (!empty($value)) {
            if (array_key_exists($userid, $dane)) {
                $dane[$userid] .= $value;
            } else {
                $dane[$userid] = $value;
            }
        }
    }
}

if (isset($dane_radius)) {
    foreach ($dane_radius as $userid=>$value) {
        if (!empty($value)) {
            if (array_key_exists($userid, $dane)) {
                $dane[$userid] .= $value;
            } else {
                $dane[$userid] = $value;
            }
        }
    }
}

if (isset($dane_gk_comments)) {
    foreach ($dane_gk_comments as $userid=>$value) {
        if (!empty($value)) {
            if (array_key_exists($userid, $dane)) {
                $dane[$userid] .= $value;
            } else {
                $dane[$userid] = $value;
            }
        }
    }
}


add_to_obserwacje_log("Finished CONCATENATING (".count($dane)." users)");


// ------------------------------------------ sending mails ------------------------------- //


// ****************************************************************************
if (!$LOCAL_DEBUG) {
    include_once("rss_geokrety.php");
}
// ****************************************************************************

// email headers
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
$headers .= 'From: GeoKrety <geokrety@gmail.com>' . "\r\n";
$headers .= 'Reply-To: GeoKrety <geokrety@gmail.com>' . "\r\n";
$headers .= 'X-Face: V3fa4jl.GK^s&Ys;p98\'/noEd86&Q1du*q\'g%#mX&le1VV9hM%sl}3-{{$wfp2k/!_+s+%a
 "D:xKi3qZGsmIvQ&OS!^Vyr:P;y:Ycm`EL!,&\Zx}yWI0pKG?hPYib8=m:g?&x7nS$CX)@jj"Y(nJ[
  i-+B@@O|hTUBvIl:@gOOnhJ^d^tMK]$.+j)^pU[\_,a;(iPi*|"-I/^}o4b' . "\r\n";


$css = file_get_contents("$geokrety_www/templates/krety-email.css");

$data = date("Y-m-d");
$datarfc=date("r");

$licznik_wyslanych_maili = 0;
if (isset($dane)) {
    foreach ($dane as $userid => $dana) {
        $dana = "$news<br />\n\n$dana";

        $result = mysqli_query($link, "SELECT `user`, `email`, `lang` FROM `gk-users` WHERE `userid`='$userid' AND `wysylacmaile`='1' AND `email_invalid`!=1 AND `email`<>'' LIMIT 1");
        $row = mysqli_fetch_array($result);
        if (!empty($row)) {
            list($user, $email, $lang) = $row;

            $TRESC = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang.'" lang="'.$lang.'">
<head><meta http-equiv="Content-Type" content="text/xml; charset=UTF-8" />';

            $TRESC .= "$css</head>\n";
            $TRESC .= "<body>Dear $user ($email)\n\n";
            $TRESC .= $dana;
            $TRESC .= "<br>-- \n<br />" .  file_get_contents("$geokrety_www/files/statystyczka-s.html") . "\n<br />" . $footer;
            $TRESC .= "</body></html>";


            if (!$LOCAL_DEBUG) {
                //mail("stefaniak@gmail.com", '[GeoKrety] Watchlist ' . $data, $TRESC, $headers); die();
                $ret = mail($email, '[GeoKrety] Watchlist ' . $data, $TRESC, $headers. 'X-GK-Lang: ' . $lang . "\r\n");
                if ($ret) {
                    $licznik_wyslanych_maili += 1;
                }
                //file_put_contents("obserwacje/obserwacje-$userid.html", $headers. 'X-GK-Lang: ' . $lang . "\r\n" . $TRESC);
                echo "wysylam: $email\n";
            } else {
                file_put_contents("../$userid.txt", $headers. 'X-GK-Lang: ' . $lang . "\r\n" . $TRESC);
            }

            mysqli_query($link, "UPDATE `gk-users` SET `ostatni_mail` = NOW() WHERE `userid`='$userid' LIMIT 1");

            if (!$LOCAL_DEBUG) {
                sleep(4 + rand(5, 15));
            }
        }
    }
}

$lang_debug = '';
foreach ($gk_lang as $userid => $lang_info) {
    $lang_debug .= "$userid: $lang_info<br/>\r\n";
}

add_to_obserwacje_log("STOP");
add_to_obserwacje_log("Emails sent: $licznik_wyslanych_maili");

$sql="INSERT INTO `gk-errory` (`uid`, `userid`, `ip` ,`date`, `file` ,`details` ,`severity`)
		VALUES ('obserwacje', '0', '0.0.0.0', '$NOW', 'obserwacje', '$obserwacje_log<br/>$lang_debug', '0')";
$result = mysqli_query($link, $sql);

if ($LOCAL_DEBUG) {
    echo "$obserwacje_log";
}

mysqli_close($link);

?>
