<?php

/*
 * General Configuration
 * You probally don't need to touch these
 */
error_reporting(E_ALL ^ E_NOTICE);
$cfg['cmd_dir'] = './inc/commands/';
$cfg['date_format'] = 'm-d G:i:s'; //Log Timestamp format - see php.net/date for formatting
$cfg['monitor_delay'] = 120; //Delay between checking for monitor activities (Excludes on empty channel monitor checks)

/*
 * Debug Configuration
 *
 */
$cfg['debug'] = false;
//ini_set("log_errors", 1);
//ini_set("error_log", "php-error.log");
/* 
 *	This is the server group id that is required 
 *	to allow access to pm commands
 *	for initial setup set to 0 this will allow
 *	anyone to use limited commands
 *	use command groupids
 */
$cfg['cmd_auth_group'] = 6;

/*
 * Server Configuration
 * This handles setup and login systems
 */
$server['ip']		= '127.0.0.1';	//Teamspeak Server IP
$server['port']		= '10011';	//Teamspeak Server Port
$server['user']		= 'serveradmin';	//ServerQuery Login Username
$server['pass']		= '';	//ServerQuery Login Password
$server['options']	= array(	//Additional Framework Login Options
	'server_port'	=> 9987, // Client connection port, not port of server? works fine with or without
	'blocking'		=> 0, // Dunno
	'nickname'		=> 'Sweetiebot' // Nickname for bot to use. You can't seem to hide the join, it Joins as ServerQuery Login, then changes to this
	);

/*
 * AFK Mover Configurations
 * This handles auto-moving users to the AFK channel
 */
$cfg['afk_mover'] = true; //Disables or Enables afk mover - false disables
$afk_mover['chan_id'] = 6; //Channel to move AFK users to 
$afk_mover['bot_chan_id'] = 2; //Channel to move/keep the bot in
$afk_mover['time'] = 10; //Time in seconds to move a user to AFK channel if they have been idle

/*
 * Channel Watch Configurations
 * This handles various channel monitoring systems
 * This will require a previous run through attempt
 * to gather chanelid's which are static ID's which
 * are required to monitor channels even though renames
 * use command channelids 
 */
$cfg['channel_mon'] = true; //Disables or Enables channel monitor - false disables
$channel_mon[] = array(
	'id'		=> '2', //Channel ID get this from the 'list_chan_info' command
	'name'		=> 'Gaming 1', //Default Name to rename the channel to
	//If you choose both options on empty or after X time the channel will be reset
	'reset_time'		=> 60, //Force reset channel after this time in seconds - 0 disables
	'on_empty'		=> false //Force reset channel when empty - false disables
	);
$channel_mon[] = array(
	'id'		=> '3', //Channel ID get this from the 'list_chan_info' command
	'name'		=> 'Gaming 2', //Default Name to rename the channel to
	//If you choose both options on empty or after X time the channel will be reset
	'reset_time'		=> 0, //Force reset channel after this time in seconds - 0 disables
	'on_empty'		=> true //Force reset channel when empty - false disables
	);
$channel_mon[] = array(
	'id'		=> '5', //Channel ID get this from the 'list_chan_info' command
	'name'		=> 'Gaming 3', //Default Name to rename the channel to
	//If you choose both options on empty or after X time the channel will be reset
	'reset_time'		=> 60, //Force reset channel after this time in seconds - 0 disables
	'on_empty'		=> true //Force reset channel when empty - false disables
	);

/*
 * Temporary Password Configurations
 * This handles setting auto-renewing passwords
 * Periodically the bot will check to see if the password
 * exists if it does not it will create it
 */
$cfg['tmp_psws'] = true; //Disables or Enables temporary passwords system - false disables
$tmp_pswds[] = array(
	'desc' => 'Test 1', //Description of password
	'pass' => 'test1', //The Password
	'duration' => 160, //Duration of password(in seconds)
	'chan_id' => 0, //Default channel to join (0 = default server channel)
	'chan_pass' => '' //Password for default channel to join (if needed)
	);
$tmp_pswds[] = array(
	'desc' => 'Join AFK', //Description of password
	'pass' => 'testafk', //The Password
	'duration' => 3600, //Duration of password(in seconds)
	'chan_id' => 6, //Default channel to join (0 = default server channel)
	'chan_pass' => '' //Password for default channel to join (if needed)
	);
