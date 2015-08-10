FROM centos:centos7
MAINTAINER Fatih Üstündağ <fatih.ustundag@gmail.com>

RUN yum install -y epel-release
RUN rpm -qa | grep -q remi-release || rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm

RUN sed -i "s|enabled=1|enabled=0|" /etc/yum/pluginconf.d/fastestmirror.conf

# Configure PHP
RUN yum --enablerepo=remi-php56,remi install -y \
    gcc \
    make \
    php \
    php-opcache \
    php-apc \
    php-devel \
    pcre-devel \
    php-pear \
    php-pecl-xdebug \
    php-mysql \
    php-pecl-xhprof \
    php-pecl-memcached \
    php-xml \
    php-gd \
    php-mbstring \
    php-mcrypt \
    php-fpm \
    php-gearman \
    php-soap \
    php-json

# Install Memcached
RUN yum --enablerepo=remi install -y memcached

# Install gearman
RUN yum --enablerepo=remi install -y gearmand

# Configure composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN TMPDIR=/tmp yum clean metadata
RUN TMPDIR=/tmp yum clean all