<?php
/*
 * Version: $Id$
 * Created: Dec 04, 2018
 * Available global variables
 *  $sms_csp            pointer to csp context to send response to user
 *  $sms_sd_ctx         pointer to sd_ctx context to retreive usefull field(s)
 *  $sms_sd_info        pointer to sd_info structure
 *  $SMS_RETURN_BUF     string buffer containing the result
 */

// Device adaptor

require_once 'smserror/sms_error.php';
require_once 'smsd/sms_common.php';
require_once load_once('nec_ix', 'nec_ix_connect.php');
require_once load_once('nec_ix', 'nec_ix_apply_conf.php');
require_once "$db_objects";

/**
 * Connect to device
 * @param  $login
 * @param  $passwd
 * @param  $adminpasswd
 * @throws SmsException
 */
function sd_connect($login = null, $passwd = null, $adminpasswd = null)
{
  device_connect($login, $passwd, $adminpasswd);
}

/**
 * Disconnect from device
 * @param $clean_exit
 * @throws SmsException
 */
function sd_disconnect($clean_exit = false)
{
  device_disconnect($clean_exit);
}

/**
 * Apply a configuration buffer to a device
 * @param  $configuration
 * @param  $need_sd_connection
 * @throws SmsException
 */
function sd_apply_conf($configuration, $need_sd_connection = false)
{
  if ($need_sd_connection)
  {
    sd_connect();
  }

  $ret = nec_ix_apply_conf($configuration, false);

  if ($need_sd_connection)
  {
    sd_disconnect();
  }
  
  return $ret;

}

?>
