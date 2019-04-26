#!/bin/bash

# Store environment variables to be used in cron
printenv | sed 's/^\(.*\)$/export \1/g' > /etc/environment

# launch cron daemon
cron -f
