#!/bin/bash

#STEP 13: UPDATING THE NGINX BAD BOT BLOCKER 
wget https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/conf.d/globalblacklist.conf -O /etc/nginx/conf.d/globalblacklist.conf

while IFS= read -r line; do
    while read -r ip param; do
        grep -q $ip /etc/nginx/bots.d/whitelist-ips.conf
        if [ $? -ne 0 ]; then
            echo "$line" >> /etc/nginx/bots.d/whitelist-ips.conf
        fi
    done <<<"$(echo "$line")"
done <<<"$(hostname -I | tr " " "\n" |xargs | grep -v "^$" | xargs printf "%30s    0;\n")"

while IFS= read -r domain; do
    grep -q $domain /etc/nginx/bots.d/whitelist-domains.conf
    if [ $? -ne 0 ]; then
        echo "$line" >> /etc/nginx/bots.d/whitelist-domains.conf
    fi
done <<<"$(plesk bin domain -l)"

while IFS= read -r confFile; do
    sed 's/server_names_hash_bucket_size/#server_names_hash_bucket_size/g' -i $confFile
done <<<"$(grep -r "\bserver_names_hash_bucket_size\b" /etc/nginx/nginx.conf /etc/nginx/conf.d/  | grep -vE "botblocker-nginx-settings.conf|#" | awk -F: '{print $1}')"

while IFS= read -r confFile; do
    sed 's/server_names_hash_max_size/#server_names_hash_max_size/g' -i $confFile
done <<<"$(grep -r "\bserver_names_hash_max_size\b" /etc/nginx/nginx.conf /etc/nginx/conf.d/  | grep -vE "botblocker-nginx-settings.conf|#" | awk -F: '{print $1}')"

exit 0;