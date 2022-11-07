#!/bin/bash

cd /opt/roardaemon
. ./secrets.sh
./readrooms.php $*
if [ "$1" == "" ]; then
	./sendupdate.sh
fi
