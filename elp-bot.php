<?php
require 'inc/config.php';
require_once 'libraries/TeamSpeak3/TeamSpeak3.php';
 
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
	if($cfg['afk_mover'] && $afk_mover['chan_id'] > 0)
		$ts3->clientGetByName($ts3->getParent()->whoamiGet('client_login_name'))->move($afk_mover['bot_chan_id']); //Selects the bot and moves him to the specified channel
       
	/* wait for events */
	while(1) $ts3->getAdapter()->wait();
}
catch(Exception $e)
{
print_message("FATAL", $e->getMessage() . "\n");
exit;

	die("[ERROR]  " . $e->getMessage() . "\n");
}
/**
 * Outputs parsed server config options for URI
 *
 * @return string
 */
function parse_server_options()
{
	global $server;
       
	if(!empty($server['options']))
	{
		foreach($server['options'] as $key => $value)
			$options[] = $key.'='.$value;
	       
		return '?'.implode($options, '&');
	}
}
 
/**
 * Parses a string into arguments
 *
 * @param  string $string
 * @return array
 */
function parse_args($string)
{
	preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $string, $args);
	return $args;
}
/**
 * Outputs Messages
 *
 * @param  string $label
 * @param  string $message
 * @return void
 */
function print_message($label, $message)
{
	global $cfg;
	$timestamp = date($cfg['date_format'], time());
	$output = "[".$timestamp."] [".$label."] ".$message."\n";
	echo $output;
	if($cfg['debug'])
		file_put_contents('internal_bot_log.log', $output, FILE_APPEND);

}
 
/**
 * Monitors Channels to rename them to defaults
 *
 * @return void
 */
function channel_monitor()
{
	global $cfg, $ts3, $channel_mon;
	
	if(!$cfg['channel_mon'] || empty($channel_mon))
		return;
	if($cfg['debug'])
	{
		$start_time = microtime();
		print_message('DEBUG', 'Start Channel Monitor');
	}
	$ts3->channelListReset(); // We need to call this to reload/refresh incase channels was added
	$ts3->clientListReset(); // We need to call this to reload/refresh clients
       
	foreach($channel_mon as $key => $chan_mon)
	{
		$channel = $ts3->channelGetByID($chan_mon['id']);
		$client_list = $channel->clientList();
		if($channel['cid'] == $chan_mon['id'] && $channel['channel_name']->toString() != $chan_mon['name'])
		{
			if((time() - $chan_mon['editinfo']['timestamp']) > $chan_mon['reset_time'] && $chan_mon['reset_time'] != 0)
			{
				print_message('CHANMON', 'Channel '.$channel['channel_name']->toString().' time elapsed reverting to '.$chan_mon['name']);
				$channel->modify(array(
					'channel_name' => $chan_mon['name']
					));
				unset($channel_mon[$key]['editinfo']);
			}
			elseif(empty($client_list) && $chan_mon['on_empty'])
			{
				print_message('CHANMON', 'Channel '.$channel['channel_name']->toString().' was empty reverting to '.$chan_mon['name']);
				$channel->modify(array(
					'channel_name' => $chan_mon['name']
					));
				unset($channel_mon[$key]['editinfo']);
			}
		}
	}
	if($cfg['debug'])
	{
		$end_time = microtime();
		print_message('TIME', 'Channel Monitor Took '.($end_time - $start_time).' seconds');
	}
}
 
/**
 * Monitors Channels to rename them to defaults
 *
 * @return void
 */
function afk_mover()
{
	global $cfg, $ts3, $afk_mover;
       
	if(!$cfg['afk_mover'])
		return;
	if($cfg['debug'])
	{
		$start_time = microtime();
		print_message('DEBUG', 'Start AFK Mover');
	}
	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$client_list = $ts3->clientList();
	foreach($client_list as $client)
	{
		//We skip the bot itself to keep it in the channel it wants to be in.
		if($ts3->getParent()->whoamiGet('client_nickname') == $client->getProperty('client_nickname'))
			continue;
		if(floor($client->getProperty('client_idle_time') / 1000) > $afk_mover['time'] && $client->getProperty('cid') != $afk_mover['chan_id'])
		{
			if($cfg['debug'])
				print_message('DEBUG', 'AFK Client Nickname '.$client->getProperty('client_nickname').'| '.print_r($client, true));
			$client->move($afk_mover['chan_id']);
			$client->message('[INFO] You have been moved to the AFK channel due to being idle for a long period of time');
		}
	}
	if($cfg['debug'])
	{
		$end_time = microtime();
		print_message('TIME', 'AFK Monitor Took '.($end_time - $start_time).' seconds');
	}
}
 
/**
 * Checks command auth to verify access to commands
 *
 * @param  TeamSpeak3_Node_client $client
 * @return bool
 */
function command_auth($client)
{
	global $cfg;
       
	$client_groups = $client->getProperty('client_servergroups');
	$groups = (is_object($client_groups)) ? explode(',', $client_groups->toString()) : $client_groups;
       
	if(is_array($groups) && in_array($cfg['cmd_auth_group'], $groups))
		return true;
	elseif(!is_array($groups) && $cfg['cmd_auth_group'] == $groups)
		return true;
	return false;
}

/**
 * Handle automated temporary password creation
 *
 * @return void;
 */
function temporary_passwords()
{
	global $cfg, $ts3, $tmp_pswds;
	
	$cur_psws = array();
	
	if(!$cfg['tmp_psws'] || empty($tmp_pswds))
		return;
		
	if($cfg['debug'])
	{
		$start_time = microtime();
		print_message('DEBUG', 'Start Temporary Passwords');
	}
	try
	{
		$psw_list = $ts3->tempPasswordList();
	}
	catch(Exception $e)
	{
		if($e->getCode() == 1281) //no temporary passwords
			$no_passwords = true;
	}
	if(!$no_passwords)
	{
		foreach($psw_list as $psw)
			$cur_psws[] = (string)$psw['pw_clear'];
		foreach($tmp_pswds as $tmp_pass)
		{
			if(!in_array($tmp_pass['pass'], $cur_psws))
			{
				$cur_psws[] = $tmp_pass['pass']; //Adds any newely added passwords to the duplication array to prevent duplicates in config
				print_message('TMPPASS', 'Password '.$tmp_pass['pass'].' was created.');
				$ts3->tempPasswordCreate($tmp_pass['pass'], $tmp_pass['duration'], $tmp_pass['chan_id'], $tmp_pass['chan_pass'], $tmp_pass['desc']);
			}
		}
	}
	else
	{
		print_message('TMPPASS', 'No passwords exist. Creating all passwords.');
		if(!empty($tmp_pswds))
			foreach($tmp_pswds as $tmp_pass)
			{
				if(!in_array($tmp_pass['pass'], $cur_psws))
				{
					$cur_psws[] = $tmp_pass['pass']; //Adds any newely added passwords to the duplication array to prevent duplicates in config
					$ts3->tempPasswordCreate($tmp_pass['pass'], $tmp_pass['duration'], $tmp_pass['chan_id'], $tmp_pass['chan_pass'], $tmp_pass['desc']);
				}
			}
	
	}
	if($cfg['debug'])
	{
		$end_time = microtime();
		print_message('TIME', 'Temporary Passwords Took '.($end_time - $start_time).' seconds');
	}
}

function check_duplicate_passwords()
{
	global $cfg, $tmp_pswds;
	
	if(!$cfg['tmp_psws'] || empty($tmp_pswds))
		return;
	
	$cfg_psws = array();
	
	foreach($tmp_pswds as $tmp_pass)
	{
		print_r($cfg_psws);
		if(in_array($tmp_pass['pass'], $cfg_psws))
		{
			$duplicate = true;
			print_message('TMPPASS', 'Duplicate password: '.$tmp_pass['pass'].' (Description: '.$tmp_pass['desc'].') was found.');
		}
		else
			$cfg_psws[] = $tmp_pass['pass'];
	}
	if($duplicate)
		print_message('ERROR', 'Duplicate password exist in the configuration file. Please check your config.php file and issue the reload command.');
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
	global $last_check, $cfg;
	if(floor(time() - $last_check) >= $cfg['monitor_delay'])
	{
		$last_check = time();
		channel_monitor();
		afk_mover();
		temporary_passwords();
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
	global $ts3, $server;
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
	global $cfg, $channel_mon;
	$start_time = microtime();
	$data = $event->getData();
	if($cfg['channel_mon'] && !empty($channel_mon))
	{
		foreach($channel_mon as $key => $chan_mon)
		{
			if($data['cid'] == $chan_mon['id'] && $data['channel_name']->toString() != $chan_mon['name'])
			{
				print_message('CHANMON', 'Channel '.$chan_mon['name'].' was edited to '.$data['channel_name']->toString().' by '.$data['invokername']->toString().'('.$data['invokeruid']->toString().')');
				$channel_mon[$key]['editinfo'] = array(
					'timestamp' => time(),
					'client_name' => $data['invokername']->toString(),
					'client_uid' => $data['invokeruid']->toString()
					);
			}
		}
	}
	$end_time = microtime();
	if($cfg['debug'])
		print_message('TIME', 'Channel Edit Monitor Took '.($end_time - $start_time).' seconds');
}
 
/**
 * Handler for 'textmessage' event.
 *
 * @param  TeamSpeak3_Adapter_ServerQuery_Event $event
 * @return void
 */
function onPrivateMessage(TeamSpeak3_Adapter_ServerQuery_Event $event)
{
	global $cfg, $channel_mon, $ts3, $tmp_pswds;
       
	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$data = $event->getData();
       
	$client = $ts3->clientGetByUid($data['invokeruid']->toString()); //Get the client object of who sent the message
       
	$args = parse_args($data['msg']->toString()); //parse the message into arguments
	$command = $args[0][0];
       
	if(command_auth($client) || $cfg['cmd_auth_group'] == 0 && $command == 'groupids') //This is a specific setup only override
	{
		//This handles for dynamic command loading/fixing to prevent having to restart bot for command changes/additions/fixes
		if(file_exists($cfg['cmd_dir'].$command.'.php'))
			eval(trim(file_get_contents($cfg['cmd_dir'].$command.'.php', false, null, 7)));
		else
			$client->message('[ERROR] Invalid Command. Use help to list commands');
	}
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
	global $last_info;
 
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
	channel_monitor();
}