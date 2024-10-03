Bad Bot Blocker will install the mitchellkrogza / nginx-ultimate-bad-bot-blocker rules set and will allow you to enable it per website.

Features:

* Enable/Disable
    
    Each website, with hosting enabled, will have a button to load or unload the protection rules from websites Nginx virtual hosts

* Enable All

    Will load the protection rules for all websites Nginx virtual hosts

* Disable All

    Will unload the protection rules from all websites Nginx virtual hosts

matching requests will be blocked with HTTP 444:
```
curl https://example.com -ILk -A "80legs"
```
> x.x.x.x - - [02/Oct/2024:13:14:46 +0000] "HEAD / HTTP/2.0" 444 0 "-" "80legs"
