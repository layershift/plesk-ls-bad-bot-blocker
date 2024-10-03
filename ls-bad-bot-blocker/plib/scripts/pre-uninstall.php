<?php
// Copyright 1999-2018. Plesk International GmbH.
pm_Loader::registerAutoload();
pm_Context::init('ls-bad-bot-blocker');

if (!class_exists('pm_ApiCli')) {
    // NOTE: echoing will cause us to not be able to install!
    echo 'pm_ApiCli not available!' . "\n";
    exit(1);
}
else{
    echo 'pm_ApiCli available!' . "\n";
    //return TRUE;
}

/// undeploy.sh begin
file_put_contents(pm_Context::getVarDir()."debug.log", 'executing undeploy.sh ...'."\n", FILE_APPEND);

// call install script
try {
    pm_ApiCli::callSbin('undeploy.sh' , array());
} catch (pm_Exception $e) {
    print "failed to deploy Bad Bot Blocker global rules: " . $e->getMessage() . "\n";
}

file_put_contents(pm_Context::getVarDir()."debug.log", 'done'."\n", FILE_APPEND);
/// undeploy.sh end


exit(0);
