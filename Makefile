
all: build run

build:
	docker build -t roardaemon .

run:
	docker kill readrooms || echo ""
	docker rm readrooms || echo ""
	docker run -d --name readrooms --restart always -v /tmp/roarcalendars:/calendars roardaemon

