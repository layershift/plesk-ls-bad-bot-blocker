#!/bin/bash

if [ -f /etc/psa/psa.conf ]; then
    HTTPD_VHOSTS_D="$(grep HTTPD_VHOSTS_D /etc/psa/psa.conf | awk '{print $NF}')"
else
    echo "Error: can't find Plesk configuration file!";
    exit 1;
fi

if [ ! -z $1 ]; then
    if [ -d ${HTTPD_VHOSTS_D}/system/$1 ]; then
        grep -nqE "extension ls-bad-bot-blocker begin|extension ls-bad-bot-blocker end" ${HTTPD_VHOSTS_D}/system/$1/conf/last_nginx.conf
        if [ $? -eq 0 ]; then
            echo -n '{"domain": "'$1'", "blockStatus": "true"}'
        else
            echo -n '{"domain": "'$1'", "blockStatus": "false"}'
        fi
    else
        echo "Error: can't find domain $1!"
        exit 3;
    fi
else
    echo "Error: domain was not specified!"
    exit 2;
fi
