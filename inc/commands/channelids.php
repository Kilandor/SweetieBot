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