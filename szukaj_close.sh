#!/bin/bash

for f in `find -name "*.php"`
do

  if grep -q "connect" $f
  then



    if grep -q "close" $f
    then

      echo ""

    else

      echo $f


    fi




  fi

done
