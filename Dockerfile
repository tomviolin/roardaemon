FROM php:7.4-cli

MAINTAINER Tom Hansen "tomh@uwm.edu"

RUN apt-get update
RUN apt-get install wget

COPY . /opt/roardaemon

WORKDIR /opt/roardaemon

CMD [ "php" , "./readrooms.php" ]


