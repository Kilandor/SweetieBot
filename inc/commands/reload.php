<?php

//We are inside a function but need to clear the arrays and set new values
//This is not SUPERGLOBALS
$GLOBALS['cfg'] = array();
$GLOBALS['channel_mon'] = array();
$GLOBALS['afk_mover'] = array();

include 'inc/config.php';

$GLOBALS['cfg'] = $cfg;
$GLOBALS['channel_mon'] = $channel_mon;
$GLOBALS['afk_mover'] = $afk_mover;

print_message('INFO', 'Bot config.php reloaded');
$client->message("[INFO] Bot config.php reloaded\n");
