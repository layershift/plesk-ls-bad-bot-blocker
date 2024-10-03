<?php
// Copyright 1999-2018. Plesk International GmbH.
pm_Loader::registerAutoload();
pm_Context::init('ls-bad-bot-blocker');
$isUpgrade=0;
$isUpgradeVersion="1.0";

    file_put_contents(pm_Context::getVarDir()."debug.log", date(DATE_RFC2822)."\n", FILE_APPEND);


if (isset($argv)) {
    file_put_contents(pm_Context::getVarDir()."debug.log", '$argv='.implode(',',$argv)."\n", FILE_APPEND);
    foreach ($argv as $_key=>$_arg) {
        if ($_arg=="upgrade") {
            $isUpgrade=1;
            $isUpgradeVersion=$argv[$_key+1];
        }
    }
}

    file_put_contents(pm_Context::getVarDir()."debug.log", '$isUpgrade='.$isUpgrade."\n", FILE_APPEND);
    file_put_contents(pm_Context::getVarDir()."debug.log", '$isUpgradeVersion='.$isUpgradeVersion."\n", FILE_APPEND);

if (!class_exists('pm_ApiCli')) {
    // NOTE: echoing will cause us to not be able to install!
    echo 'pm_ApiCli not available!' . "\n";
    exit(1);
}
else{
    echo 'pm_ApiCli available!' . "\n";
    //return TRUE;
}

/// deploy.sh begin
file_put_contents(pm_Context::getVarDir()."debug.log", 'executing deploy.sh ...'."\n", FILE_APPEND);

// call install script
try {
    pm_ApiCli::callSbin('deploy.sh' , array());
} catch (pm_Exception $e) {
    print "failed to deploy Bad Bot Blocker global rules: " . $e->getMessage() . "\n";
}

file_put_contents(pm_Context::getVarDir()."debug.log", 'done'."\n", FILE_APPEND);
/// deploy.sh end

/// update.sh begin
file_put_contents(pm_Context::getVarDir()."debug.log", 'executing update.sh ...'."\n", FILE_APPEND);

// call install script
try {
    pm_ApiCli::callSbin('update.sh' , array());
} catch (pm_Exception $e) {
    print "failed to update Bad Bot Blocker global rules: " . $e->getMessage() . "\n";
}

file_put_contents(pm_Context::getVarDir()."debug.log", 'done'."\n", FILE_APPEND);
/// update.sh end


exit(0);
