#!/bin/bash
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
cd /opt/roardaemon
. ./secrets.sh
./readrooms.php $*
if [ "$1" == "" ]; then
	./sendupdate.sh
fi
