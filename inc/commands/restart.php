<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'restart'))
	return false;

$client->message("[INFO] Restarting in 10 seconds\n");
$ts3->getParent()->logout();
shell_exec('~/elp-bot/startscript.sh');
exit;
