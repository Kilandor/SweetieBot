<?php

$raw_commands = scandir($cfg['cmd_dir']);
$raw_commands = array_splice($raw_commands, 2);

foreach($raw_commands as $command)
{
	$commands[] = str_replace('.php', '', $command);
}

$client->message("[INFO] \nCommands\n".implode($commands, ', '));