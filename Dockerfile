ARG IMAGE=geokrety/website-legacy:prod
FROM ${IMAGE}

MAINTAINER GeoKrety Team <contact@geokrety.org>

WORKDIR /opt/geokrety

# Add extension to php
RUN apt-get update \
    && apt-get install -y \
        wget \
        cron \
        less \
        bc \
        unzip \
        gnuplot \
    && apt-get clean \
    && rm -r /var/lib/apt/lists/*

# Install scripts and cron
COPY . /opt/geokrety/

# Install cron job
RUN mv /opt/geokrety/geokrety-crontab /etc/cron.d/geokrety-cron \
  && chmod 0644 /etc/cron.d/geokrety-cron \
  && chmod 755 /opt/geokrety/init.sh

CMD ["/opt/geokrety/init.sh"]
HEALTHCHECK NONE
