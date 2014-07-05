<?php
require 'inc/config.php';

if($cfg['debug']['enabled'])
{
	ini_set("display_errors", "On");
	ini_set("log_errors", 1);
	ini_set("error_log", "php-error.log");
}

require_once($cfg['core_dir'].'functions.php');
require_once 'libraries/TeamSpeak3/TeamSpeak3.php';

/* Include Core files */
foreach($cfg['modules'] as $name => $modulescfg)
	require_once($cfg['core_dir'].$name.'.php');
/* initialize */
TeamSpeak3::init();
$last_info;
$last_check = time();
try
{
	check_duplicate_passwords(); //Check for duplicate passwords on startup
	/* subscribe to various events */
	TeamSpeak3_Helper_Signal::getInstance()->subscribe('serverqueryConnected', 'onConnect');
	TeamSpeak3_Helper_Signal::getInstance()->subscribe('serverqueryWaitTimeout', 'onTimeout');
	TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyLogin', 'onLogin');
	TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyEvent', 'onEvent');
	TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyServerselected', 'onSelect');

	/* connect to server, login and get TeamSpeak3_Node_Host object by URI */
	$ts3 = TeamSpeak3::factory('serverquery://'.$server['user'].':'.$server['pass'].'@'.$server['ip'].':'.$server['port'].'/'.parse_server_options());
	if($cfg['modules']['afk_mover']['enabled'] && $cfg['modules']['afk_mover']['cfg']['chan_id'] > 0)
		$ts3->clientGetByName($ts3->getParent()->whoamiGet('client_login_name'))->move($cfg['modules']['afk_mover']['cfg']['bot_chan_id']); //Selects the bot and moves him to the specified channel

	/* wait for events */
	while(1) $ts3->getAdapter()->wait();
}
catch(Exception $e)
{
print_message("FATAL", $e->getMessage() . "\n");
exit;

	die("[ERROR]  " . $e->getMessage() . "\n");
}

// ================= [ BEGIN OF CALLBACK FUNCTION DEFINITIONS ] =================

/**
 * Callback method for 'serverqueryConnected' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery $adapter
 * @return void
 */
function onConnect(TeamSpeak3_Adapter_ServerQuery $adapter)
{
	print_message('SIG', 'connected to TeamSpeak 3 Server on '.$adapter->getHost());
	print_message('INFO', 'server is running with version '.$adapter->getHost()->version('version').' on '.$adapter->getHost()->version('platform'));
}

/**
 * Callback method for 'serverqueryWaitTimeout' signals.
 *
 * @param  integer $seconds
 * @return void
 */
function onTimeout($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) //This triggers every 10 seconds
{
	global $cfg, $ts3, $last_check;
	if(floor(time() - $last_check) >= $cfg['monitor_delay'])
	{
		$last_check = time();
		/* Hook System to call specified function */
		if(!empty($cfg['hooks']['onTimeout']))
			foreach($cfg['hooks']['onTimeout'] as $hook)
				$hook();
	}
	//print_message('SIG', 'no reply from the server for '.$seconds.' seconds');
	if($adapter->getQueryLastTimestamp() < time()-300)
	{
		print_message('INFO', 'sending keep-alive command');
		$adapter->request('clientupdate');
	}
}

/**
 * Callback method for 'notifyLogin' signals.
 *
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onLogin(TeamSpeak3_Node_Host $host)
{
	print_message('SIG', 'authenticated as user '.$host->whoamiGet('client_login_name'));
}

/**
 * Callback method for 'notifyEvent' signals.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @param  TeamSpeak3_Node_Host $host
 * @return void
 */
function onEvent(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
{
	global $cfg, $ts3;
	$type = $event->getType();
	$data = $event->getData();

	if($type == 'textmessage' && $data['targetmode'] == 1) //We only handle Private Messages
	{
		//We have to do this because you get notified about your own message...we need to ignore it
		if($host->whoamiGet('client_login_name') != $data['invokername']->toString())
			onPrivateMessage($event);
	}
	elseif($type == 'channeledited')
		onChannelEdited($event);
	elseif($type == 'clientmoved' || $type == 'clientleftview')
		onClientMoved($event);
	else
	{
		/* Hook System to call specified function */
		if(!empty($cfg['hooks']['onEvent']))
			foreach($cfg['hooks']['onEvent'] as $hook)
				$hook($event);
	}
	//else // Debugging only
		//print_message('SIG', "received notification ".$event->getType()."\n\t".$event->getMessage());
}

/**
 * Callback method for 'notifyServerselected' signals.
 *
 * @param  string $cmd
 * @return void
 */
function onSelect(TeamSpeak3_Node_Host $host)
{
	print_message('SIG', 'selected virtual server with ID '.$host->serverSelectedId());

	$host->serverGetSelected()->notifyRegister('server');
	$host->serverGetSelected()->notifyRegister('channel');
	$host->serverGetSelected()->notifyRegister('textserver');
	$host->serverGetSelected()->notifyRegister('textchannel');
	$host->serverGetSelected()->notifyRegister('textprivate');

}

/**
 * Handler for 'channeledited' event.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @return void
 */
function onChannelEdited(TeamSpeak3_Adapter_ServerQuery_Event $event)
{
	global $cfg, $ts3;

	/* Hook System to call specified function */
	if(!empty($cfg['hooks']['onChannelEdited']))
		foreach($cfg['hooks']['onChannelEdited'] as $hook)
			$hook($event);
}

/**
 * Handler for 'textmessage' event.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @return void
 */
function onPrivateMessage(TeamSpeak3_Adapter_ServerQuery_Event $event)
{
	global $cfg, $ts3;

	/* Hook System to call specified function */
	if(!empty($cfg['hooks']['onPrivateMessage']))
		foreach($cfg['hooks']['onPrivateMessage'] as $hook)
			$hook($event);
}

/**
 * Handler for 'clientmoved' or 'clientleftview' event.
 * This is a catch all for channel monitoring on any movement or logout
 * it will check and see if a monitored channel is edited and empty and fix it
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @return void
 */
function onClientMoved(TeamSpeak3_Adapter_ServerQuery_Event $event)
{
	global $cfg, $ts3, $last_info;

	$data = $event->getData();
	//We have to do this whole mess to save/prevent running the event twice
	//Because the server sends the event twice
	if($data['reasonid'] == REASON_DISCONNECT) //User disconnected from server
	{
		//if it matches the last event skip it
		if($last_info['client_moved']['client_id'] == $data['clid'] && $last_info['client_moved']['channel_id'] == $data['cfid'])
			return;
		$last_info['client_moved'] = array(
			'client_id' => $data['clid'],
			'channel_id' => $data['cfid']
			);
	}
	else //User moved channels
	{
		//if it matches the last event skip it
		if($last_info['client_moved']['client_id'] == $data['clid'] && $last_info['client_moved']['channel_id'] == $data['ctid'])
			return;
		$last_info['client_moved'] = array(
			'client_id' => $data['clid'],
			'channel_id' => $data['ctid']
			);
	}

	/* Hook System to call specified function */
	if(!empty($cfg['hooks']['onClientMoved']))
		foreach($cfg['hooks']['onClientMoved'] as $hook)
			$hook($event);
}