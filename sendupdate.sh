#!/bin/bash
/usr/bin/sleep 30
SRC=/calendars/GLRF_ALL.ics
OLD=/tmp/GLRF_OLD.ics
[ -f $OLD ] && diff -q $SRC $OLD && exit 0

curl --url 'smtp://smtprelay.uwm.edu:25' --mail-from 'tomh@uwm.edu' --mail-rcpt 'tomh@uwm.edu' -F '=Upload attachment to https://sites.uwm.edu/sfs-reservations/wp-admin/edit.php?post_type=calendar_event&page=calendar-plus-settings&tab=import ;type=text/plain' -F attachment=@$SRC -H 'Subject: ROAR calendar update' && cp $SRC $OLD

