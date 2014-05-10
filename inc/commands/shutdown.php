<?php

// Authenticates the user for access to this command
if(!command_auth($client, $args, 'shutdown'))
	return false;

exit();