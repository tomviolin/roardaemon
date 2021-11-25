
builddockerimage:
	docker build -t roardaemon .

rundockerimage:
	docker run -it --rm -v /tmp/roarcalendars:/calendars roardaemon

