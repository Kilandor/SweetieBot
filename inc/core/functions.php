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
		file_put_contents($cfg['log_dir'].'/bot_debug.log', $output, FILE_APPEND);
	else if($label == 'SECURITY')
			file_put_contents($cfg['log_dir'].'/bot_security.log', $output, FILE_APPEND);
	else if($label == 'FATAL')
		file_put_contents($cfg['log_dir'].'/bot_console_fatal.log', $output, FILE_APPEND);
	else if($cfg['log'])
		file_put_contents($cfg['log_dir'].'/bot_console.log', $output, FILE_APPEND);

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

function debug_string_backtrace()
{
	ob_start();
	debug_print_backtrace();
	$trace = ob_get_contents();
	ob_end_clean();

	// Remove first item from backtrace as it's this function which
	// is redundant.
	$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

	// Renumber backtrace items.
	$trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

	return $trace;
}