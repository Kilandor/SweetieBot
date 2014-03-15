<?php

$client->message("[INFO] Restarting in 10 seconds\n");
$ts3->getParent()->logout();
shell_exec('~/elp-bot/startscript.sh');
exit;
