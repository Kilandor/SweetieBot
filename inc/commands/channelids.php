<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'channelids'))
	return false;

$ts3->channelListReset(); // We need to call this to reload/refresh incase channels was added
foreach($ts3->channelList() as $channel)
{
	if($channel->isSpacer()) // We don't need these do we?
		continue;
	if((int)$channel->getProperty('channel_flag_permanent') != 1) // We don't need non perm channels do we?
		continue;
	$chan_list[] = $channel->getProperty('cid')."\t\t\t\t".$channel->getProperty('channel_name')->toString();
}

$client->message("\nID\t\t\t\tChannel\n".implode($chan_list, "\n"));