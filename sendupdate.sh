#!/bin/bash
/usr/bin/sleep 30
SRC=/calendars/GLRF_ALL.ics
OLD=/tmp/GLRF_OLD.ics
[ -f $OLD ] && diff $SRC $OLD > /tmp/diff$$.txt && exit 0

curl --url 'smtp://smtprelay.uwm.edu:25' --mail-from 'tomh@uwm.edu' --mail-rcpt 'tomh@uwm.edu' -F "=@/tmp/diff$$.txt;type=text/plain" -F "attachment=@$SRC" -H 'Subject: ROAR calendar update' && cp $SRC $OLD

