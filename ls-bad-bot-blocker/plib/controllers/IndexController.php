<?php
// Copyright 2006-2024. Layershift Limited.

class IndexController extends pm_Controller_Action
{
    #protected $_accessLevel = 'admin';

    private function _getTabs($setActiveTab=-1) {
        return[ [
            'title' => $this->lmsg('lsBadBotBlockerList'),
            'action' => 'index',
            'active' => ( $setActiveTab==1 ? true : false),
        ], [
            'title' => $this->lmsg('lsBadBotBlockerEnableAll'),
            'action' => 'enableall',
            'active' => false,
        ], [
            'title' => $this->lmsg('lsBadBotBlockerDisableAll'),
            'action' => 'disableall',
            'active' => false,
        ], [
            'title' => $this->lmsg('lsBadBotBlockerTools'),
            'action' => 'tools',
            'active' => ( $setActiveTab==4 ? true : false),
        ]  ];

    }

    public function indexAction() {
        if (!pm_Session::getClient()->isAdmin()) {
            throw new pm_Exception('Permission denied');
        }

        $this->view->list = $this->getList();
        $this->view->tabs = $this->_getTabs(1);
    }

    public function indexDataAction() {
        if (!pm_Session::getClient()->isAdmin()) {
            throw new pm_Exception('Permission denied');
        }
        $this->_helper->json($this->getList()->fetchData());
    }

    private function getList() {
        $data = [];
        foreach (pm_Domain::getAllDomains() as $pmDomain) {
            $domain = new Modules_LsBadBotBlocker_Domain($pmDomain->getId());
            if ($pmDomain->getProperty('htype')=="vrt_hst") {
                if ($domain->isEnabled()) {
                    $statusCheck="";
                    try {
                        $res=pm_ApiCli::callSbin('checkBlock.sh' , array($pmDomain->getDisplayName()));
                        $data_=json_decode($res['stdout'],true);
                        if (!is_null($data_)) {
                            if ($data_['blockStatus']=="true") {
                                $statusCheck=' <img src="/cp/theme/icons/16/plesk/on.png" alt="loaded">';
                            }
                        }
                    } catch (pm_Exception $e) {
                        file_put_contents(pm_Context::getVarDir()."debug.log", "failed to check Bad Bot Blocker rules for ".$pmDomain->getDisplayName().": " . $e->getMessage() . "\n", FILE_APPEND);
                        $statusCheck=' <img src="/cp/theme/icons/16/plesk/wp-secure-unknown.png" alt="unknown">';
                    }

                    $status = $this->lmsg('lsBadBotBlockerStatusEnabled').$statusCheck;
                    $link = pm_Context::getActionUrl('index', 'disable') . '/id/' . $pmDomain->getId();
                    $linkTitle = $this->lmsg('lsBadBotBlockerStatusDisable');
                } else {
                    $status = $this->lmsg('lsBadBotBlockerStatusDisabled');
                    $link = pm_Context::getActionUrl('index', 'enable') . '/id/' . $pmDomain->getId();
                    $linkTitle = $this->lmsg('lsBadBotBlockerStatusEnable');
                }
                $link_="<a href='{$link}'>{$linkTitle}</a>";
            } else {
                $status = $this->lmsg('lsBadBotBlockerStatusNoHosting');
                $link_ = "";
            }
            $data[] = [
                'name' => '<a href="'.pm_Context::getActionUrl('index', 'domain').'?dom_id='.$pmDomain->getProperty('webspace_id').'&site_id='.$pmDomain->getId().'">'.$pmDomain->getDisplayName().'</a>',
                'status' => $status,
                'link' => $link_,
            ];
        }

        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns([
            'name' => [
                'title' => $this->lmsg('lsBadBotBlockerDomainName'),
                'noEscape' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'status' => [
                'title' => $this->lmsg('lsBadBotBlockerStatus'),
                'noEscape' => true,
                'searchable' => false,
                'sortable' => true,
            ],
            'link' => [
                'title' => $this->lmsg('lsBadBotBlockerAction'),
                'noEscape' => true,
                'searchable' => false,
                'sortable' => true,
            ]
        ]);
        $list->setDataUrl(['action' => 'index-data']);
        return $list;
    }

    public function enableAction() {
        $this->setState(true);
    }

    public function disableAction() {
        $this->setState(false);
    }

    private function setState($enable = true) {
        $domainId = $this->_request->getParam('id');
        $dom_id = $this->_request->getParam('dom_id');
        $site_id = $this->_request->getParam('site_id');
        $domainParam = $this->_request->getParam('domain');

        if (empty($domainId)) {
            throw new pm_Exception('Domain is not specified');
        }
        if (!pm_Session::getClient()->hasAccessToDomain($domainId)) {
            throw new pm_Exception('Client doesn\'t have access to this domainId '.$domainId);
        }
        $domain = new Modules_LsBadBotBlocker_Domain($domainId);
        $enable ? $domain->setEnabled() : $domain->setDisabled();

        $webServer = new pm_WebServer();
        $webServer->updateDomainConfiguration(new pm_Domain($domainId));

        $domainName="";
        foreach (pm_Domain::getAllDomains() as $pmDomain) {
            if ($pmDomain->getId()==$domainId) {
                $domainName=$pmDomain->getDisplayName();       
                break;
            }
        }

        $status = $enable ? 'Enabled' : 'Disabled';
        $this->_status->addInfo("$domainName status was changed to: \"{$status}\".");
        if ($domainParam=="yes") {
            $this->_redirect('index/domain?dom_id='.$dom_id.'&site_id='.$domainId);
        } else {
            $this->_redirect('index');
        }
    }

    public function enableallAction() {
        if (!pm_Session::getClient()->isAdmin()) {
            throw new pm_Exception('Permission denied');
        }

        $domainNames=[];
        foreach (pm_Domain::getAllDomains() as $pmDomain) {
            $domain = new Modules_LsBadBotBlocker_Domain($pmDomain->getId());
            if ($pmDomain->getProperty('htype')=="vrt_hst") {
                if ($domain->isEnabled()===false) {
                    $domainNames[]=$pmDomain->getDisplayName();
                    $domain->setEnabled();
                    $webServer = new pm_WebServer();
                    $webServer->updateDomainConfiguration(new pm_Domain($pmDomain->getId()));
                }
            }
        }
        $domainNames_=implode(",",$domainNames);
        $this->_status->addInfo("$domainNames_ status was changed to: \"{Enabled}\".");
        $this->_redirect('index');
    }

    public function disableallAction() {
        if (!pm_Session::getClient()->isAdmin()) {
            throw new pm_Exception('Permission denied');
        }

        $domainNames=[];
        foreach (pm_Domain::getAllDomains() as $pmDomain) {
            echo "<pre>";var_dump($pmDomain);echo "</pre>";
            $domain = new Modules_LsBadBotBlocker_Domain($pmDomain->getId());
            if ($pmDomain->getProperty('htype')=="vrt_hst") {
                if ($domain->isEnabled()===true) {
                    $domainNames[]=$pmDomain->getDisplayName();
                    $domain->setDisabled();
                    $webServer = new pm_WebServer();
                    $webServer->updateDomainConfiguration(new pm_Domain($pmDomain->getId()));
                }
            }
        }
        $domainNames_=implode(",",$domainNames);
        $this->_status->addInfo("$domainNames_ status was changed to: \"{Disabled}\".");
        $this->_redirect('index');
    }

    public function domainAction() {
        $badBotBlockerEnabled=false;
        $dom_id=$this->_request->getQuery('dom_id');
        $site_id=$this->_request->getQuery('site_id');

        $pmDomain = pm_Domain::getByDomainId($site_id);

        $this->view->domainName=$pmDomain->getDisplayName();
        $this->view->backLink="/smb/web/overview/id/".$site_id."/type/domain";
        if (pm_Session::getClient()->isAdmin()) {
            $this->view->backLink="/admin/subscription/login/id/".$dom_id."?pageUrl=/smb/web/overview/id/".$site_id."/type/domain";
            $this->view->domainList='<a href="/modules/ls-bad-bot-blocker/index.php/index/index" title="Back to domain list"><</a> ';
        }

        if ($pmDomain->getProperty('htype')=="vrt_hst") {
            $domain = new Modules_LsBadBotBlocker_Domain($pmDomain->getId());
            if ($domain->isEnabled()) {
                $badBotBlockerEnabled=true;
                $statusCheck="";
                try {
                    $res=pm_ApiCli::callSbin('checkBlock.sh' , array($pmDomain->getDisplayName()));
                    $data_=json_decode($res['stdout'],true);
                    if (!is_null($data_)) {
                        if ($data_['blockStatus']=="true") {
                            $statusCheck=' <img src="/cp/theme/icons/16/plesk/on.png" alt="loaded">';
                        }
                    }
                } catch (pm_Exception $e) {
                    file_put_contents(pm_Context::getVarDir()."debug.log", "failed to check Bad Bot Blocker rules for ".$pmDomain->getDisplayName().": " . $e->getMessage() . "\n", FILE_APPEND);
                    $statusCheck=' <img src="/cp/theme/icons/16/plesk/wp-secure-unknown.png" alt="unknown">';
                }

                $status = $this->lmsg('lsBadBotBlockerStatusEnabled').$statusCheck;
                $link = pm_Context::getActionUrl('index', 'disable') . '/id/' . $pmDomain->getId() . '/dom_id/'.$dom_id.'/domain/yes';
                $linkTitle = $this->lmsg('lsBadBotBlockerStatusDisable');
            } else {
                $status = $this->lmsg('lsBadBotBlockerStatusDisabled');
                $link = pm_Context::getActionUrl('index', 'enable') . '/id/' . $pmDomain->getId() . '/dom_id/'.$dom_id.'/domain/yes';
                $linkTitle = $this->lmsg('lsBadBotBlockerStatusEnable');
            }
            $link_="<a href='{$link}'>{$linkTitle}</a>";
        } else {
            $status = $this->lmsg('lsBadBotBlockerStatusNoHosting');
            $link_ = "";
        }
        
        $this->view->currentStatus=$status;
        $this->view->link_=$link_;
        $this->view->dom_id=$dom_id;
        $this->view->site_id=$site_id;
    }

    public function checkDomainAction() {
        $dom_id=$this->_request->getQuery('dom_id');
        $site_id=$this->_request->getQuery('site_id');

        if (!pm_Session::getClient()->hasAccessToDomain($dom_id) and !pm_Session::getClient()->isAdmin()) {
            throw new pm_Exception('Client doesn\'t have access to this domainId '.$dom_id);
        }

        $pmDomain = pm_Domain::getByDomainId($site_id);

        $domainName = $pmDomain->getName($site_id);

        try {
            $result = json_decode(pm_ApiCli::callSbin('summaryBuilder.sh', [$domainName], pm_ApiCli::RESULT_STDOUT));
        } catch (pm_Exception $e) {
            echo "Error: can't decode data";
            exit;
        }
        if ($result->blocked>0) {
            echo "<h1>".$this->lmsg('domain.blocked')." ".$result->blocked." ".$this->lmsg('domain.requests')."</h1>";
            echo "<h2>".$this->lmsg('domain.between')." ".$result->from." ".$this->lmsg('domain.and')." ".$result->to."</h2>";
            echo "<table><tr><th>".$this->lmsg('domain.numBlockedRequests')."</th><th>".$this->lmsg('domain.userAgent')."</th></tr>";
            foreach ($result->{'User-Agents'} as $UserAgent) {
                echo "<tr><td>";
                echo $UserAgent->num;
                echo "</td><td>";
                echo $UserAgent->{'User-Agent'};
                echo "</td><tr>";
            }
            echo "</table>";

        } else {
            echo "<p>".$this->lmsg('domain.noBlockedRequestsToday')." ".date('d/M/Y')."</p>";
        }
        exit;
    }

}
