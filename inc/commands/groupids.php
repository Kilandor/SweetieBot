<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'groupids'))
	return false;

$ts3->serverGroupListReset(); // We need to call this to reload/refresh incase server groups was added

foreach($ts3->serverGroupList() as $group)
	$group_list[] = $group->getProperty('sgid')."\t\t\t\t".$group->getProperty('name')->toString();

$client->message("\nID\t\t\t\tGroup\n".implode($group_list, "\n"));

$client_groups = $client->getProperty('client_servergroups');
$client->message("You are part of group".((is_object($client_groups)) ? 's: '.$client_groups->toString() : ': '.$client_groups));