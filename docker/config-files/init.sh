#!/bin/bash

memcached -u root -d -m 64
/usr/sbin/php-fpm -R
/usr/sbin/nginx -g "daemon off;"