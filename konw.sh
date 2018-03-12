#!/bin/bash

for FILE in "1/"*.php; do
  echo $FILE
  iconv -f ISO-8859-2 -t UTF-8 -o "2/$FILE" "1/$FILE"
done
