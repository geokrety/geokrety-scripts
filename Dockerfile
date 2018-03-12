FROM geokrety:1.1.0

MAINTAINER Mathieu Alorent <contact@geokretymap.org>

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


# Install site
COPY . /opt/geokrety/

# Add crontab file in the cron directory
ADD geokrety-crontab /etc/cron.d/geokrety-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/geokrety-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

WORKDIR /opt/geokrety

# Run the command on container startup
CMD cron && tail -f /var/log/cron.log
