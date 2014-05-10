<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'reload'))
	return false;

//We need to store hook configuration because it is not in the config file
$hooks = $GLOBALS['cfg']['hooks'];

//We are inside a function but need to clear the arrays and set new values
//This is not SUPERGLOBALS
$GLOBALS['cfg'] = array();
$GLOBALS['channel_mon'] = array();
$GLOBALS['afk_mover'] = array();
$GLOBALS['tmp_pswds'] = array();

include 'inc/config.php';

//Reset and load configruations
$cfg['hooks'] = $hooks;
$GLOBALS['cfg'] = $cfg;
$GLOBALS['channel_mon'] = $channel_mon;
$GLOBALS['afk_mover'] = $afk_mover;
$GLOBALS['tmp_pswds'] = $tmp_pswds;

print_message('INFO', 'Bot config.php reloaded');
$client->message("[INFO] Bot config.php reloaded\n");

check_duplicate_passwords(); //Check for duplicate passwords on reload