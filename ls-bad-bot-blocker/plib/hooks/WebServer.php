<?php
// Copyright 1999-2017. Plesk International GmbH.

class Modules_LsBadBotBlocker_WebServer extends pm_Hook_WebServer
{
    // public function getDomainApacheConfig(pm_Domain $domain) {
    //     return '';
    //     if (!$this->isEnabled($domain)) {
    //         return '';
    //     }
    //     return '# Apache config for domain: ' . $domain->getName();
    // }

    public function getDomainNginxConfig(pm_Domain $domain) {
        if (!$this->isEnabled($domain)) {
            return '';
        }
        $include="include /etc/nginx/bots.d/blockbots[.]conf;\n";
        $include.="include /etc/nginx/bots.d/ddos[.]conf;";
        return $include;
    }

    public function getDomainNginxProxyConfig(pm_Domain $domain) {
        if (!$this->isEnabled($domain)) {
            return '';
        }
        $include="include /etc/nginx/bots.d/blockbots[.]conf;\n";
        $include.="include /etc/nginx/bots.d/ddos[.]conf;";
        return $include;
    }

    // public function getWebmailApacheConfig(pm_Domain $domain, $type) {
    //     return '';
    //     if (!$this->isEnabled($domain)) {
    //         return '';
    //     }
    //     return "Header add X-Custom-Ext-Web-Server {$type}";
    // }

    public function getWebmailNginxConfig(pm_Domain $domain, $type) {
        if (!$this->isEnabled($domain)) {
            return '';
        }
        $include="include /etc/nginx/bots.d/blockbots[.]conf;\n";
        $include.="include /etc/nginx/bots.d/ddos[.]conf;";
        return $include;
    }

    private function isEnabled(pm_Domain $pmDomain) {
        $domain = new Modules_LsBadBotBlocker_Domain($pmDomain->getId());
        return $domain->isEnabled();
    }
}
