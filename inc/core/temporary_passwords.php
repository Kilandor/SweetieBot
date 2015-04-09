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
/* Hook Configuration */
$cfg['hooks']['onTimeout'][] = 'temporary_passwords';

/* Global Arrays */
$pass_last_run = 0;

/**
 * Handle automated temporary password creation
 *
 * @return void;
 */
function temporary_passwords($event = null)
{
	global $cfg, $ts3, $pass_last_run;

	$cur_psws = array();

	if(!$cfg['modules']['temporary_passwords']['enabled'] || empty($cfg['modules']['temporary_passwords']['cfg']))
		return;

	// Enforces timelimit to prevent running to often
	if($pass_last_run > time())
	{
		debug_message('Temporary Passwords attempted to run '.($pass_last_run - time()).' seconds early');
		return;
	}

	$start_time = microtime(true);
	debug_message('Start Temporary Passwords');

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
		foreach($cfg['modules']['temporary_passwords']['cfg'] as $tmp_pass)
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
		if(!empty($cfg['modules']['temporary_passwords']['cfg']))
			foreach($cfg['modules']['temporary_passwords']['cfg'] as $tmp_pass)
			{
				if(!in_array($tmp_pass['pass'], $cur_psws))
				{
					$cur_psws[] = $tmp_pass['pass']; //Adds any newely added passwords to the duplication array to prevent duplicates in config
					$ts3->tempPasswordCreate($tmp_pass['pass'], $tmp_pass['duration'], $tmp_pass['chan_id'], $tmp_pass['chan_pass'], $tmp_pass['desc']);
				}
			}

	}

	$pass_last_run = time() + $cfg['monitor_delay']; //Sets a timelimit

	$end_time = microtime(true);
	debug_message('Temporary Passwords Took '.($end_time - $start_time).' seconds');

}

/**
 * Checks for duplicate passswords
 *
 * @return void;
 */
function check_duplicate_passwords()
{
	global $cfg;

	if(!$cfg['modules']['temporary_passwords']['enabled'] || empty($cfg['modules']['temporary_passwords']['cfg']))
		return;

	$cfg_psws = array();

	foreach($cfg['modules']['temporary_passwords']['cfg'] as $tmp_pass)
	{
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