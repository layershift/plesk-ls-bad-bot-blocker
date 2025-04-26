<?php
// Copyright 1999-2021. Plesk International GmbH.
class Modules_LsBadBotBlocker_CustomButtons extends pm_Hook_CustomButtons
{

    public function getButtons()
    {
        $buttons = [];
        $buttons[] = [
            'place' => self::PLACE_ADMIN_TOOLS_AND_SETTINGS,
            'section' => self::SECTION_ADMIN_TOOLS_SECURITY,
            'title' => 'Bad Bot Blocker',
            'order' => 1,
            'description' => '',
            'link' => pm_Context::getActionUrl('index'),
        ];
        $buttons[] = [
            'place' => static::PLACE_DOMAIN_PROPERTIES_DYNAMIC,
            'section' => static::SECTION_DOMAIN_PROPS_DYNAMIC_SECURITY,
            'title' => 'Bad Bot Blocker',
            'description' => 'Toggle the Bad Bot Blocker rule for this website',
            'icon' => '/extras/ls-bad-bot-blocker/_meta/icons/32x32.png',
            'link' => pm_Context::getActionUrl('index', 'domain'),
            'contextParams' => true,
        ];

        return $buttons;
    }


}