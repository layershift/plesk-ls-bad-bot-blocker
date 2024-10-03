#!/bin/bash

runTime=$(date +%s)

if [ -f /etc/nginx/conf.d/globalblacklist.conf ]; then
    rm -f /etc/nginx/conf.d/globalblacklist.conf
fi

if [ -f /etc/nginx/conf.d/botblocker-nginx-settings.conf ]; then
    rm -f /etc/nginx/conf.d/botblocker-nginx-settings.conf
fi

confFile="$(grep -r "\bserver_names_hash_bucket_size\b" /etc/nginx/nginx.conf /etc/nginx/conf.d/  | grep -vE "botblocker-nginx-settings.conf" | awk -F: '{print $1}' | head -n1)"
sed 's/#server_names_hash_bucket_size/server_names_hash_bucket_size/g' -i $confFile

confFile="$(grep -r "\bserver_names_hash_max_size\b" /etc/nginx/nginx.conf /etc/nginx/conf.d/  | grep -vE "botblocker-nginx-settings.conf" | awk -F: '{print $1}' | head -n1)"
sed 's/#server_names_hash_max_size/server_names_hash_max_size/g' -i $confFile
