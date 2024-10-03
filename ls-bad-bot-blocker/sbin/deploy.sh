#!/bin/bash

runTime=$(date +%s)

#STEP 1:
wget -O /etc/nginx/conf.d/globalblacklist.conf              "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/conf.d/globalblacklist.conf"

#STEP 2:
mkdir /etc/nginx/bots.d 
wget -O /etc/nginx/bots.d/blockbots.conf                    "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/blockbots.conf"
wget -O /etc/nginx/bots.d/ddos.conf                         "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/ddos.conf"

#STEP 3:
wget -O /etc/nginx/bots.d/whitelist-ips.conf                "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/whitelist-ips.conf"
wget -O /etc/nginx/bots.d/whitelist-domains.conf            "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/whitelist-domains.conf"
## to do
# populate 
# /etc/nginx/bots.d/whitelist-ips.conf
# /etc/nginx/bots.d/whitelist-domains.conf

#STEP 4: BLACKLIST USING YOUR OWN CUSTOM USER-AGENT BLACKLIST
if [ ! -f /etc/nginx/bots.d/blacklist-user-agents.conf ]; then
wget -O /etc/nginx/bots.d/blacklist-user-agents.conf        "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/blacklist-user-agents.conf"
fi

#STEP 5: BLACKLIST USING YOUR OWN CUSTOM BAD REFERRERS
if [ ! -f /etc/nginx/bots.d/custom-bad-referrers.conf ]; then
wget -O /etc/nginx/bots.d/custom-bad-referrers.conf         "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/custom-bad-referrers.conf"
fi

#STEP 6: BLACKLIST IPS AND IP RANGES USING YOUR OWN CUSTOM LIST
if [ ! -f /etc/nginx/bots.d/blacklist-ips.conf ]; then
wget -O /etc/nginx/bots.d/blacklist-ips.conf                "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/blacklist-ips.conf"
fi

#STEP 7: DOWNLOAD CUSTOM BAD REFERRER WORDS INCLUDE FILE FOR CUSTOMIZED SCANNING OF BAD WORDS
if [ ! -f /etc/nginx/bots.d/bad-referrer-words.conf ]; then
wget -O /etc/nginx/bots.d/bad-referrer-words.conf           "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/bots.d/bad-referrer-words.conf"
fi

#STEP 8: INCLUDE IMPORTANT SETTINGS IN NGINX.CONF 
wget -O /etc/nginx/conf.d/botblocker-nginx-settings.conf    "https://raw.githubusercontent.com/layershift/nginx-ultimate-bad-bot-blocker/master/conf.d/botblocker-nginx-settings.conf"

#STEP 9: /etc/nginx/conf.d is always included in Plesk environments
