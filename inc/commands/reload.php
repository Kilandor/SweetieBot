<?php
/*
 * SweetieBot
 * https://github.com/Kilandor/SweetieBot
 *
 * @author Jason Booth (Kilandor)
 * @copyright Copyright (c) 2015 Jason Booth (Kilandor)
 *
 * @license GPL v3

 This file is part of SweetieBot.

    SweetieBot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    SweetieBot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with SweetieBot.  If not, see <http://www.gnu.org/licenses/>.
 */
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