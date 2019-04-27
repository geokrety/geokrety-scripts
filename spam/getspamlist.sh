#!/bin/bash

adresy=(
"http://meta.wikimedia.org/wiki/Spam_blacklist?action=raw"
"http://en.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
"http://pl.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
"http://de.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
"http://ru.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
)

echo -n "# Lista spamu wygenerowana: " > spamlist.txt
date >> spamlist.txt

for adres in "${adresy[@]}"
do
	curl -L -s -S "$adres" | grep -f spam.grep -v >> spamlist.txt
done

cat mojalista.txt >> spamlist.txt
cat cluebot.txt >> spamlist.txt
