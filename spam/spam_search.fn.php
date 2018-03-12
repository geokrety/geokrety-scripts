<?php

function checkwordblock($text='')
{
    $spamlist="./spamlist.txt";

    // we prepare the text a tiny bit to prevent spammers circumventing URL checks
    $text = preg_replace('!(\b)(www\.[\w.:?\-;,]+?\.[\w.:?\-;,]+?[\w/\#~:.?+=&%@\!\-.:?\-;,]+?)([.:?\-;,]*[^\w/\#~:.?+=&%@\!\-.:?\-;,])!i', '\1http://\2 \2\3', $text);
    $wordblocks = file($spamlist);

    // how many lines to read at once (to work around some PCRE limits)
    if (version_compare(phpversion(), '4.3.0', '<')) {
        // old versions of PCRE define a maximum of parenthesises even if no
        // backreferences are used - the maximum is 99
        // this is very bad performancewise and may even be too high still
        $chunksize = 40;
    } else {
        // read file in chunks of 200 - this should work around the
        // MAX_PATTERN_SIZE in modern PCRE
        $chunksize = 200;
    }
    while ($blocks = array_splice($wordblocks, 0, $chunksize)) {

    # echo "*";

        $re = array();

        // build regexp from blocks
        foreach ($blocks as $block) {
            $block = preg_replace('/#.*$/', '', $block);
            $block = trim($block);
            if (empty($block)) {
                continue;
            }
            $re[]  = $block;
        }
        if (count($re) && preg_match('#('.join('|', $re).')#si', $text, $matches)) {
            // prepare event data
            return $matches;
        }
    }
    return false;
}
