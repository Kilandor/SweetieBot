<?php

/* Hook Configuration */
$cfg['hooks']['onTimeout'][] = 'channel_monitor';
$cfg['hooks']['onClientMoved'][] = 'channel_monitor';
$cfg['hooks']['onChannelEdited'][] = 'channel_edited';

/* Global Arrays */
$chan_edit_info = array();
$chan_last_run = 0;
/**
 * Monitors Channels to rename them to defaults
 *
 * @return void
 */
function channel_monitor($event = null)
{
	global $cfg, $ts3, $chan_edit_info, $chan_last_run;

	if(!$cfg['modules']['channel_monitor']['enabled'] || empty($cfg['modules']['channel_monitor']['cfg']))
		return;

	// Enforces timelimit to prevent running to often
	if($chan_last_run > time())
	{
		debug_message('Chanel Monitor attempted to run '.($chan_last_run - time()).' seconds early');
		return;
	}

	$start_time = microtime(true);
	debug_message('Start Channel Monitor');

	$ts3->channelListReset(); // We need to call this to reload/refresh incase channels was added
	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$channel_list = $ts3->channelList();
	if(empty($channel_list))
		debug_message('Channel List Was Empty....Exiting');
	else
	{
		foreach($cfg['modules']['channel_monitor']['cfg'] as $key => $chan_mon)
		{
			//$channel = $ts3->channelGetByID($chan_mon['id']);
			if(!array_key_exists($chan_mon['id'], $channel_list))
			{
				debug_message('Channel '.$chan_mon['id'].' was not found in server information skipping....');
				continue;
			}
			$channel = $channel_list[$chan_mon['id']];
			$client_list = $channel->clientList();
			if($channel['cid'] == $chan_mon['id'] && $channel['channel_name'] != $chan_mon['name'])
			{
				if((time() - $chan_edit_info[$chan_mon['id']]['timestamp']) > $chan_mon['reset_time'] && $chan_mon['reset_time'] != 0)
				{
					print_message('CHANMON', 'Channel '.$channel['channel_name'].' time elapsed reverting to '.$chan_mon['name']);
					debug_message('Elapsed '.(time() - $chan_edit_info[$chan_mon['id']]['timestamp']).' seconds - Reset Time '.$chan_mon['reset_time'].' seconds');
					$channel->modify(array(
						'channel_name' => $chan_mon['name']
						));
					unset($chan_edit_info[$chan_mon['id']]);
				}
				elseif(empty($client_list) && $chan_mon['on_empty'])
				{
					print_message('CHANMON', 'Channel '.$channel['channel_name'].' was empty reverting to '.$chan_mon['name']);
					$channel->modify(array(
						'channel_name' => $chan_mon['name']
						));
					unset($chan_edit_info[$chan_mon['id']]);
				}
			}
		}
	}
	$chan_last_run = time() + $cfg['monitor_delay']; //Sets a timelimit

	$end_time = microtime(true);
	debug_message('Channel Monitor Took '.($end_time - $start_time).' seconds');

}

function channel_edited($event = null)
{
	global $cfg, $chan_edit_info;
	if(!$cfg['modules']['channel_monitor']['enabled'] || empty($cfg['modules']['channel_monitor']['cfg']))
		return;

	$data = $event->getData();

	foreach($cfg['modules']['channel_monitor']['cfg'] as $key => $channel)
	{
		if($data['cid'] == $channel['id'] && $data['channel_name'] != $channel['name'])
		{
			print_message('CHANMON', 'Channel '.$channel['name'].' was edited to '.$data['channel_name'].' by '.$data['invokername']->toString().'('.$data['invokeruid']->toString().')');
			$chan_edit_info[$channel['id']]= array(
				'timestamp' => time(),
				'client_name' => $data['invokername']->toString(),
				'client_uid' => $data['invokeruid']->toString()
				);
		}
	}
}