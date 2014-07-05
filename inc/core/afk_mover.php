<?php

/* Hook Configuration */
$cfg['hooks']['onTimeout'][] = 'afk_mover';

/**
 * Monitors Channels to rename them to defaults
 *
 * @return void
 */
function afk_mover($event = null)
{
	global $cfg, $ts3;

	if(!$cfg['modules']['afk_mover']['enabled'])
		return;

	$start_time = microtime();
	debug_message('Start AFK Mover');

	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$client_list = $ts3->clientList();
	foreach($client_list as $client)
	{
		//We skip the bot itself to keep it in the channel it wants to be in.
		if($ts3->getParent()->whoamiGet('client_nickname') == $client->getProperty('client_nickname'))
			continue;
		if(floor($client->getProperty('client_idle_time') / 1000) > $cfg['modules']['afk_mover']['cfg']['time'] && $client->getProperty('cid') != $cfg['modules']['afk_mover']['cfg']['chan_id'])
		{
			if($cfg['debug']['enabled'])
				print_message('DEBUG', 'AFK Client Nickname '.$client->getProperty('client_nickname');
			$client->move($cfg['modules']['afk_mover']['cfg']['chan_id']);
			$client->message('[INFO] You have been moved to the AFK channel due to being idle for a long period of time');
		}
	}

	$end_time = microtime();
	debug_message('AFK Monitor Took '.($end_time - $start_time).' seconds');
}