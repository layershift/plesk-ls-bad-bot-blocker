#!/bin/bash

if [ ! -z "$1" ]; then
    domain="$1"
else
    domain='*'
fi

#get all log lines

#today
searchDate="$(date +%d/%b/%Y)";

result="$(grep "$searchDate" /var/www/vhosts/system/${domain}/logs/proxy_access*log | grep "\" 444 ")"

if [ $(echo "$result" | wc -c) -gt 1 ]; then

    # print number of blocked requests
    blocked="$(echo "$result" | wc -l)"
    
    from="$(echo "$result" | head -n1 | awk -F "[" '{print $2}' | awk -F "]" '{print $1}')"
    to="$(echo "$result" | tail -n1 | awk -F "[" '{print $2}' | awk -F "]" '{print $1}')"


    echo -n '{"blocked":'$blocked',"from":"'$from'","to":"'$to'","User-Agents":['

    # print requests per User-Agent
    echo "$result" | awk '{for (i = 12; i <= NF; i++) {printf "%s ", $i}; printf "\n"}' | sort | uniq -c | sort -h | awk -F'"' '{print "{\"num\":"$1",\"User-Agent\":\""$2"\"}"}' | tr "\n" "," | sed 's#,$##'

    #userAgents="$(echo "$userAgents" | sed 's#,$##')"

    echo ']}'
else
    echo '{"blocked":0,"User-Agents":[]}'
fi
