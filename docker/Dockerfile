# docker build -t fatihustundag/centos-php7.1 -f Dockerfile .
FROM centos:latest
MAINTAINER Fatih Üstündağ <fatih.ustundag@gmail.com>

RUN yum install -y --nogpgcheck epel-release && \
    rpm -qa | grep -q remi-release || rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm && \
    sed -i "s|enabled=1|enabled=0|" /etc/yum/pluginconf.d/fastestmirror.conf && \
    yum --enablerepo=remi-php71,remi install -y --nogpgcheck \
    gcc \
    git-core \
    make \
    nginx \
    nc \
    unzip \
    mysql \
    netstat \
    openssh \
    openssh-clients \
    openssh-server \
    rsyslog \
    cronie \
    php \
    php-opcache \
    php-apc \
    php-devel \
    pcre-devel \
    php-pear \
    php-pecl-xdebug \
    php-mysqlnd \
    php-pecl-memcached \
    php-pecl-gearman \
    php-pecl-imagick \
    php-xml \
    php-gd \
    php-mbstring \
    php-mcrypt \
    php-fpm \
    php-soap \
    php-json \
    php-intl \
    php-bcmath \
    php-pecl-redis \
    php-pgsql \
    php-pecl-zip \
    libgearman \
    libgearman-devel \
    npm \
    wget \
    telnet \
    gdb \
    htop \
    tcpdump \
    gearmand \
    memcached \
    bind-utils \
    vim

# Install Memcached
RUN yum install -y memcached

# Install gearman
RUN yum install -y gearmand

# Configure composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN TMPDIR=/tmp yum clean metadata
RUN TMPDIR=/tmp yum clean all