<?php

/*
 * Version: $Id: do_get_sd_conf.php 23900 2014-11-19 13:40:40Z tmt $
 * Created: Jun 11, 2014
 * Available global variables
 * 	$sms_sd_ctx    pointer to sd_ctx context to retreive usefull field(s)
 *  $sms_sd_info   sd_info structure
 *  $sdid
 *  $sms_module    module name (for patterns)
 *  $SMS_RETURN_BUF     string buffer containing the result
 */

// Get router configuration, not JSON response format
require_once 'smsd/sms_common.php';

require_once load_once('rancher_cmp', 'rancher_cmp_connect.php');
require_once load_once('rancher_cmp', 'rancher_cmp_configuration.php');

try
{
  $ret = rancher_cmp_connect();
  if ($ret !== SMS_OK)
  {
  	throw new SmsException("", ERR_SD_CONNREFUSED);
  }

  // Get the conf on the router
  $conf = new rancher_cmp_configuration($sdid);
  $SMS_RETURN_BUF = $conf->get_running_conf();
  rancher_cmp_disconnect();
}
catch (Exception $e)
{
  rancher_cmp_disconnect();
  return $e->getCode();
}

return SMS_OK;
?>
