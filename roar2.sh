#!/bin/bash
wget -O rooms2.xml --user tomh --password 25LiveSFSPublisher --no-check-certificate 'https://webservices.collegenet.com/r25ws/wrd/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list'

# php readrooms2.php
