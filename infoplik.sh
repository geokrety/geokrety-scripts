#!/bin/sh

md5sum $1 > $1.md5
du -h $1 >> $1.md5
date -r $1 -R >> $1.md5
