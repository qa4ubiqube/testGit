<?php
/*
 * Version: $Id$
 * Created: May 30, 2011
 * Available global variables
 *  $sms_csp            pointer to csp context to send response to user
 *  $sms_sd_ctx         pointer to sd_ctx context to retreive usefull field(s)
 *  $sms_sd_info        pointer to sd_info structure
 *  $SMS_RETURN_BUF     string buffer containing the result
 */

// Device adaptor

require_once 'smserror/sms_error.php';
require_once 'smsd/sms_common.php';
require_once load_once('catalyst_ios', 'catalyst_ios_connect_port.php');
require_once load_once('catalyst_ios', 'catalyst_connection.php');
require_once load_once('catalyst_ios', 'catalyst_configuration.php');

require_once "$db_objects";

/**
 * Connect to device
 * @param  $login
 * @param  $passwd
 * @param  $adminpasswd
 */
function sd_connect($login = NULL, $passwd = NULL, $adminpasswd = NULL, $ts_ip = null, $ts_port = null)
{
	if(empty($ts_ip) ||empty($ts_port))
	{
		$ret = catalyst_connect();
	}
	else
	{
		$ret = catalyst_ios_connect_port($ts_ip, $ts_port, $adminpasswd);
	}
	
	return $ret;
}

/**
 * Disconnect from device
 * @param $clean_exit
 */
function sd_disconnect($clean_exit = false, $ts_ip=null)
{
	if(empty($ts_ip))
	{
		$ret = catalyst_disconnect($clean_exit);
	}
	else
	{
		$ret = catalyst_ios_disconnect_port($clean_exit);
	}
	return $ret;
}

/**
 * Apply a configuration buffer to a device
 * @param  $configuration
 * @param  $need_sd_connection
 */
function sd_apply_conf($configuration, $need_sd_connection = false, $ts_ip = null, $ts_port = null)
{
	global $sms_sd_ctx;
	global $sdid;

	if ($need_sd_connection)
	{
		$ret = sd_connect(null, null, null, $ts_ip, $ts_port);
	}
	
	if ($ret != SMS_OK)
  	{
  		throw new SmsException("", ERR_SD_CMDTMOUT);
  	}

	$ret = catalyst_ios_apply_conf($configuration);
	if(!empty($ts_ip))
	{
		sd_save_conf();
	}

	if ($need_sd_connection)
	{
		sd_disconnect($ts_ip);
	}

	return $ret;
}

function sd_save_conf()
{
	global $sdid;
	global $sms_sd_ctx;
	$running_conf = "";
	//get and save running conf
	$conf = new CatalystConfiguration($sdid);

	$running_conf = $conf->get_running_conf();

	$ret = save_result_file($running_conf, "running.conf");
	return $ret;
}

/**
 * Execute a command on a device
 * @param  $cmd
 * @param  $need_sd_connection
 */
function sd_execute_command($cmd, $need_sd_connection = false)
{
	global $sms_sd_ctx;

	if ($need_sd_connection)
	{
		$ret = sd_connect();
	}

	$ret = $sms_sd_ctx->sendexpectone(__FILE__.':'.__LINE__, $cmd);

	if ($need_sd_connection)
	{
		sd_disconnect(true);
	}

	return $ret;
}

?>
