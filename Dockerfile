FROM phusion/baseimage:0.9.17

CMD ["/sbin/my_init"]

ENV HOME /root

# Silence debconf warnings
ENV DEBIAN_FRONTEND noninteractive
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

# Aptitude
RUN echo "deb http://nginx.org/packages/ubuntu/ trusty nginx" > /etc/apt/sources.list.d/nginx.list \
 && echo "deb-src http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list.d/nginx.list \
 && echo "deb http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu trusty main" > /etc/apt/sources.list.d/php.list \
 && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C ABF5BD827BD9BF62 \
 && apt-get update -qq \
 && apt-get install -yqq python-software-properties \
 && add-apt-repository -y ppa:nginx/stable

# Install nginx and php
RUN apt-get update \
 && apt-get install -yqqm nginx php5-fpm php5-cli php5-pgsql

# Configure nginx to spit logs to stderr/stdout
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

# Configure nginx start script
RUN mkdir -p /etc/service/nginx
ADD docker/runit/nginx.sh /etc/service/nginx/run
RUN chmod +x /etc/service/nginx/run

# Configure php5-fpm start script
RUN mkdir -p /etc/service/php5-fpm
ADD docker/runit/php5-fpm.sh /etc/service/php5-fpm/run
RUN chmod +x /etc/service/php5-fpm/run

# Run
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
