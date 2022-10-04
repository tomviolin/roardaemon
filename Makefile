
all: build run

build:
	cp ~/secrets/secrets.sh .
	docker build -t roardaemon .

run:	stop start

stop:
	docker kill readrooms || echo ""

start:
	docker rm readrooms || echo ""
	docker run -d --name readrooms --restart always -v /tmp/roarcalendars:/calendars roardaemon

