<?php

/* Hook Configuration */
$cfg['hooks']['onTimeout'][] = 'temporary_passwords';

/**
 * Handle automated temporary password creation
 *
 * @return void;
 */
function temporary_passwords($event = null)
{
	global $cfg, $ts3;

	$cur_psws = array();

	if(!$cfg['modules']['temporary_passwords']['enabled'] || empty($cfg['modules']['temporary_passwords']['cfg']))
		return;

	$start_time = microtime();
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

	$end_time = microtime();
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