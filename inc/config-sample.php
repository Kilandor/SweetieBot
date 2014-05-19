<?php

/*
 * General Configuration
 * You probally don't need to touch these
 */
error_reporting(E_ALL ^ E_NOTICE);

$cfg['cmd_dir'] = './inc/commands/';
$cfg['core_dir'] = './inc/core/';
$cfg['date_format'] = 'm-d G:i:s'; //Log Timestamp format - see php.net/date for formatting
$cfg['monitor_delay'] = 10; //Delay between checking for monitor activities (Excludes on empty channel monitor checks)
$cfg['log'] = false;  // Enable outputting all console messages to log file

/*
 * Debug Configuration
 *
 */
$cfg['debug']['enabled'] = true; //Enable Debugging code
$cfg['debug']['console'] = false; //Enable showing all 'DEBUG' messages in console - requires debugging enabled

/*
 * Server Configuration
 * This handles setup and login systems
 */
$server['ip']		= '127.0.0.1';	//Teamspeak Server IP
$server['port']		= '10011';	//Teamspeak Server Port
$server['user']		= 'SweetieBot';	//ServerQuery Login Username
$server['pass']		= '';	//ServerQuery Login Password
$server['options']	= array(	//Additional Framework Login Options
	'server_port'	=> 9987, // Client connection port, not port of server? works fine with or without
	'blocking'		=> 0, // Dunno
	'nickname'		=> 'SweetieBot' // Nickname for bot to use. You can't seem to hide the join, it Joins as ServerQuery Login, then changes to this
	);

/*
 * Module Including
 * While you may not need configuration some modules may need to be included this can be done here
 */

/*
 * Authentication can happen as detailed or limiting as needed
 * Full groups or users can simply have access to full commands
 * or limited to specific commands
 * the first value is that of a group ID or a UniqueID hash for a user
 */
$cfg['modules']['authentication']['enabled'] = true;

/*
 * This is a quicky config which gets merged with every authentication
 * to allow for simple single edit global access
 * You can still allow specific full access to commands and use this
 */
/*
$quick_config['full'] = array(
	6 => true
	);
*/
$cfg['modules']['authentication']['cfg']['channelids'] = array();
$cfg['modules']['authentication']['cfg']['groupids'] = array();
$cfg['modules']['authentication']['cfg']['reload'] = array();
$cfg['modules']['authentication']['cfg']['restart'] = array();
$cfg['modules']['authentication']['cfg']['shutdown'] = array();
$cfg['modules']['authentication']['cfg']['test'] = array();

//Here we load in our quick config, it will replace any config with the values set in it
if(is_array($quick_config))
	foreach($cfg['modules']['authentication']['cfg'] as $area => $tmp_cfg)
		$cfg['modules']['authentication']['cfg'][$area] = array_replace_recursive($tmp_cfg, $quick_config);

/*
 * AFK Mover Configurations
 * This handles auto-moving users to the AFK channel
 */
$cfg['modules']['afk_mover']['enabled'] = false; //Disables or Enables afk mover - false disables
$cfg['modules']['afk_mover']['cfg']['chan_id'] = 0; //Channel to move AFK users to
$cfg['modules']['afk_mover']['cfg']['bot_chan_id'] = 0; //Channel to move/keep the bot in
$cfg['modules']['afk_mover']['cfg']['time'] = 3600; //Time in seconds to move a user to AFK channel if they have been idle

/*
 * Channel Watch Configurations
 * This handles various channel monitoring systems
 * This will require a previous run through attempt
 * to gather chanelid's which are static ID's which
 * are required to monitor channels even though renames
 * use command channelids
 */
$cfg['modules']['channel_monitor']['enabled'] = false; //Disables or Enables channel monitor - false disables
/*
$cfg['modules']['channel_monitor']['cfg'][] = array(
	'id' => 0, //Channel ID get this from the 'list_chan_info' command
	'name' => '', //Default Name to rename the channel to
	//If you choose both options on empty or after X time the channel will be reset
	'reset_time' => 0, //Force reset channel after this time in seconds - 0 disables
	'on_empty' => true //Force reset channel when empty - false disables
	);
*/

/*
 * Temporary Password Configurations
 * This handles setting auto-renewing passwords
 * Periodically the bot will check to see if the password
 * exists if it does not it will create it
 */
$cfg['modules']['temporary_passwords']['enabled'] = false; //Disables or Enables temporary passwords system - false disables
/*
$cfg['modules']['temporary_passwords']['cfg'][] = array(
	'desc' => '', //Description of password
	'pass' => '', //The Password
	'duration' => 0, //Duration of password(in seconds)
	'chan_id' => 0, //Default channel to join (0 = default server channel)
	'chan_pass' => '' //Password for default channel to join (if needed)
	);
*/