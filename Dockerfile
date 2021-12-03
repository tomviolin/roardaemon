FROM php:7.4-cli

MAINTAINER Tom Hansen "tomh@uwm.edu"

RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime
RUN dpkg-reconfigure --frontend noninteractive tzdata
COPY ./mkphptz.sh .
RUN ./mkphptz.sh

RUN apt-get update
RUN apt-get -y install wget cron

COPY . /opt/roardaemon

RUN crontab < /opt/roardaemon/crontab

WORKDIR /opt/roardaemon

CMD cron -f

