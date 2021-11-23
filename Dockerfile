FROM php:7.4-cli

MAINTAINER Tom Hansen "tomh@uwm.edu"

COPY . /opt/roardaemon

WORKDIR /opt/roardaemon

CMD [ "php" , "./readrooms.php" ]


