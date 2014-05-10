<?php

/* Hook Configuration */
$cfg['hooks']['onPrivateMessage'][] = 'command_init';

/**
 * Checks command auth to verify access to commands
 *
 * @param TeamSpeak3_Node_client $client
 * @param string $command Command recieved
 * @param string $area Config Area
 * @param string $command Level of Auth
 */
function command_auth($client, $command, $area, $level = 'full')
{
	global $cfg;

	if(!is_array($cfg['modules']['authentication']['cfg'][$area][$level]))
	{
		print_message('ERROR', 'Unable to find authentication for Area: '.$area.' - Level: '.$level);
		$client->message('[ERROR] Unable to find authentication for command. Please inform your administrator');
		return false;
	}

	$client_groups = $client->getProperty('client_servergroups');
	$groups = (is_object($client_groups)) ? explode(',', $client_groups->toString()) : array( 0 => $client_groups);

	// First we check to see if the user has full access to bypass specific command checks
	foreach($cfg['modules']['authentication']['cfg'][$area]['full'] as $id => $enabled)
	{
		//Checks to see if the ID is a group id or a UniqueID hash
		if(preg_matcH('/=/', $id))
		{
			if($id == $client->client_unique_identifier && $enabled)
			{
				$pass = true;
				break;
			}
		}
		else
		{
			if(array_search($id, $groups) !== false && $enabled)
			{
				$pass = true;
				break;
			}
		}
	}
	//If they do not then we check down to the specific detailed command
	if($level != 'full')
		foreach($cfg['modules']['authentication']['cfg'][$area][$level] as $id => $enabled)
		{
			//Checks to see if the ID is a group id or a UniqueID hash
			if(preg_matcH('/=/', $id))
			{
				if($id == $client->client_unique_identifier && $enabled)
				{
					$pass = true;
					break;
				}
			}
			else
			{
				if(array_search($id, $groups) !== false && $enabled)
				{
					$pass = true;
					break;
				}
			}
		}
	if(!$pass)
	{
		$client->message('[ERROR] User access denied.');
		print_message('SECURITY', 'User '.$client->toString().'('.$client->client_unique_identifier.') attempted to access Area: '.$area.' - Level: '.$level.' - Command: '.implode(' ', $command));
		return false;
	}
	else
		return true;
}

function command_init($event = null)
{
	global $cfg, $ts3;

	if(is_null($event))
		return;

	$ts3->clientListReset(); // We need to call this to reload/refresh clients
	$data = $event->getData();

	 //Get the client object of who sent the message
	$client = $ts3->clientGetByUid($data['invokeruid']->toString()); //Get the client object of who sent the message

	$args = parse_args($data['msg']->toString()); //parse the message into arguments
	$args = $args[0];
	$command = $args[0];

	//This handles for dynamic command loading/fixing to prevent having to restart bot for command changes/additions/fixes
	if(file_exists($cfg['cmd_dir'].$command.'.php'))
		eval(trim(file_get_contents($cfg['cmd_dir'].$command.'.php', false, null, 7)));
	else
		$client->message('[ERROR] Invalid Command. Use help to list commands');
}