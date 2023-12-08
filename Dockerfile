FROM php:7.4-cli

MAINTAINER Tom Hansen "tomh@uwm.edu"

# update packages needed
RUN apt-get update
RUN apt-get install --fix-missing
RUN apt-get update
# RUN apt-get upgrade -y
RUN apt-get -y install --fix-missing
RUN apt-get -y install apt-utils
RUN apt-get -y install --fix-missing
RUN apt-get -y install wget 
RUN apt-get -y install --fix-missing
RUN apt-get -y install cron
RUN apt-get -y install --fix-missing
RUN apt-get -y install coreutils 
RUN apt-get -y install --fix-missing

# copy local content to its container home
COPY . /opt/roardaemon

# fix timezone issue
RUN ln -fs /usr/share/zoneinfo/America/Chicago /etc/localtime
RUN dpkg-reconfigure --frontend noninteractive tzdata
RUN /opt/roardaemon/mkphptz.sh

# go home
WORKDIR /opt/roardaemon

# set crontab
RUN crontab < /opt/roardaemon/crontab

# launch container by running cron in foreground
CMD [ "cron" ,  "-f" ]

