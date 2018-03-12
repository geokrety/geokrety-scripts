<?php

function rss_geokrety($userid, $TRESC)
{
    $rss = '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0">
	<channel>
		<generator>filips geokret</generator>
		<title>GeoKrety watchlist</title>
		<link>https://geokrety.org/mypage.php?userid='. $userid .'</link>
		<description><![CDATA[   GeoKrety watchlist   ]]></description>
		<language>pl</language>
		<item>
			<title>GeoKrety watchlist ' .strftime("%a, %d %b %Y") .'</title>
			<link>https://geokrety.org/mypage.php?userid='. $userid .'</link>
			<pubDate>' . strftime("%a, %d %b %Y %H:%M:%S %z") . '</pubDate>
			<description><![CDATA[   '. $TRESC . '   ]]></description>
			<author>geokrety@gmail.com</author>
		</item>
	</channel>
</rss>
';

    return $rss;
}

?>
