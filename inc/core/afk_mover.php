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
/* Hook Configuration */
$cfg['hooks']['onTimeout'][] = 'afk_mover';

/* Global Arrays */
$afk_last_run = 0;

/**
 * Monitors Channels to rename them to defaults
 *
 * @return void
 */
function afk_mover($event = null)
{
	global $cfg, $ts3, $afk_last_run;

	if(!$cfg['modules']['afk_mover']['enabled'])
		return;

	// Enforces timelimit to prevent running to often
	if($afk_last_run > time())
	{
		debug_message('AFK Monitor attempted to run '.($afk_last_run - time()).' seconds early');
		return;
	}

	$afk_last_run = time() + $cfg['monitor_delay']; //Sets a timelimit

	$start_time = microtime(true);
	debug_message('Start AFK Mover');

	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$client_list = $ts3->clientList();
	if(empty($client_list))
		debug_message('Client List Was Empty....Exiting');
	else
	{
		foreach($client_list as $client)
		{
			//We skip the bot itself to keep it in the channel it wants to be in.
			if($ts3->getParent()->whoamiGet('client_nickname') == $client->getProperty('client_nickname'))
				continue;
			if(floor($client->getProperty('client_idle_time') / 1000) > $cfg['modules']['afk_mover']['cfg']['time'] && $client->getProperty('cid') != $cfg['modules']['afk_mover']['cfg']['chan_id'])
			{
				debug_message('AFK Client Nickname '.$client->getProperty('client_nickname'));
				$client->move($cfg['modules']['afk_mover']['cfg']['chan_id']);
				$client->message('[INFO] You have been moved to the AFK channel due to being idle for a long period of time');
			}
		}
	}

	$end_time = microtime(true);
	debug_message('AFK Monitor Took '.($end_time - $start_time).' seconds');
}