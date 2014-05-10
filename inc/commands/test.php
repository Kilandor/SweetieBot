<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'test'))
	return false;

$client->message("Or maybe there is nothing to test");