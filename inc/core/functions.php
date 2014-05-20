<?php

/**
 * Outputs parsed server config options for URI
 *
 * @return string
 */
function parse_server_options()
{
	global $server;

	if(!empty($server['options']))
	{
		foreach($server['options'] as $key => $value)
			$options[] = $key.'='.$value;

		return '?'.implode($options, '&');
	}
}

/**
 * Parses a string into arguments
 *
 * @param  string $string
 * @return array
 */
function parse_args($string)
{
	preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $string, $args);
	return $args;
}
/**
 * Outputs Messages
 *
 * @param  string $label
 * @param  string $message
 * @return void
 */
function print_message($label, $message)
{
	global $cfg;
	$timestamp = date($cfg['date_format'], time());
	$output = "[".$timestamp."] [".$label."] ".$message."\n";

	if($cfg['debug']['console'] && $label == 'DEBUG')
		echo $output;
	elseif($label != 'DEBUG')
		echo $output;

	if($label == 'DEBUG')
		file_put_contents('bot_debug.log', $output, FILE_APPEND);
	else if($label == 'SECURITY')
			file_put_contents('bot_security.log', $output, FILE_APPEND);
	else if($cfg['log'])
		file_put_contents('bot_console.log', $output, FILE_APPEND);

}

/**
 * Wrapper for Debug messages
 *
 * @return void
 */
function debug_message($message)
{
	global $cfg;

	if($cfg['debug']['enabled'])
		print_message('DEBUG', $message);
}