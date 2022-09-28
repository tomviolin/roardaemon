#!/bin/bash

/bin/sleep 10

for f in /calendars/GLRF_ALL.ics; do
	SRC=$f
	# OLD=/tmp/`basename $f .ics`.old
	# [ -f $OLD ] && diff $SRC $OLD && exit 0

	/usr/bin/curl --url 'smtp://smtprelay.uwm.edu:25' --mail-from 'tomh@uwm.edu' --mail-rcpt 'tomh@uwm.edu' -F '=Upload attachment to https://sites.uwm.edu/sfs-reservations/wp-admin/edit.php?post_type=calendar_event&page=calendar-plus-settings&tab=import ;type=text/plain' -F attachment=@$SRC -H 'Subject: ROAR calendar update'  #  && cp $SRC $OLD
	/bin/sleep 0.1
done
