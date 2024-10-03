<?php
// Copyright 1999-2021. Plesk International GmbH.
class Modules_LsBadBotBlocker_CustomButtons extends pm_Hook_CustomButtons
{

    public function getButtons()
    {
        $buttons = [[
            'place' => self::PLACE_ADMIN_TOOLS_AND_SETTINGS,
            'section' => self::SECTION_ADMIN_TOOLS_SECURITY,
            'title' => 'Bad Bot Blocker',
            'order' => 1,
            'description' => '',
            'link' => pm_Context::getActionUrl('index'),
        ]];

        return $buttons;
    }

}