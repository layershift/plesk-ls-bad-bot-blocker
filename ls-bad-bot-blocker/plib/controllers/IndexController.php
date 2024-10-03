<?php
// Copyright 2006-2024. Layershift Limited.

class IndexController extends pm_Controller_Action
{
    protected $_accessLevel = 'admin';

    public function indexAction() {
        $this->view->list = $this->getList();
        $this->view->tabs = [ [
            'title' => $this->lmsg('lsBadBotBlockerList'),
            'action' => 'index',
            'active' => true,
        ], [
            'title' => $this->lmsg('lsBadBotBlockerEnableAll'),
            'action' => 'enableall',
            'active' => false,
        ], [
            'title' => $this->lmsg('lsBadBotBlockerDisableAll'),
            'action' => 'disableall',
            'active' => false,
        ] ];
    }

    public function indexDataAction() {
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
                'name' => $pmDomain->getDisplayName(),
                'status' => $status,
                'link' => $link_,
            ];
        }

        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns([
            'name' => [
                'title' => $this->lmsg('lsBadBotBlockerDomainName'),
            ],
            'status' => [
                'title' => $this->lmsg('lsBadBotBlockerStatus'),
                'noEscape' => true,
            ],
            'link' => [
                'title' => $this->lmsg('lsBadBotBlockerAction'),
                'noEscape' => true,
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

        if (empty($domainId)) {
            throw new pm_Exception('Domain is not specified');
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
        $this->_redirect('index');
    }

    public function enableallAction() {
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

}
