<?php

require_once 'smsd/sms_common.php';

require_once load_once('smsd', 'generic_command.php');
require_once load_once('smsd', 'cmd_create_xml.php');
require_once load_once('smsd', 'cmd_update_xml.php');
require_once load_once('smsd', 'cmd_delete_xml.php');
//require_once load_once('smsd', 'cmd_import_xml.php');
//require_once load_once('smsd', 'cmd_import_json.php');
require_once load_once('nec_pflow_pfcscapi', 'cmd_import_json_assoc.php');
require_once load_once('smsd', 'cmd_list.php');
require_once load_once('smsd', 'cmd_read.php');

require_once load_once('nec_pflow_pfcscapi', 'adaptor.php');

class nec_pflow_pfcscapi_command extends generic_command
{
  var $parser_list;
  var $parsed_objects;
  var $create_list;
  var $delete_list;
  var $list_list;
  var $read_list;
  var $update_list;
  var $configuration;
  var $import_file_list;

  function __construct()
  {
    parent::__construct();
    $this->parser_list = array();
    $this->create_list = array();
    $this->delete_list = array();
    $this->list_list = array();
    $this->read_list = array();
    $this->update_list = array();
    $this->import_file_list = array();
  }

  /*
   * #####################################################################################
  * IMPORT
  * #####################################################################################
  */

  function decode_IMPORT($object, $json_params, $element)
  {
    $parser = new cmd_import_associative($object, $element, $json_params);
    $this->parser_list[] = &$parser;
  }

  /**
   * IMPORT configuration from router
   * @param object $json_params                 JSON parameters of the command
   * @param domElement $element                 XML DOM element of the definition of the command
   */
  function eval_IMPORT()
  {
    global $sms_sd_ctx;
    global $SMS_RETURN_BUF;
    //Add by Mer ---
    global $apply_conf;
    $apply_conf = 1;
    //-------------
    try
    {
      $ret = sd_connect();
      if ($ret != SMS_OK)
      {
        return $ret;
      }

      if (!empty($this->parser_list))
      {
        $objects = array();
        $parser_list = array();

        foreach ($this->parser_list as $parser)
        {
          $op_eval = $parser->evaluate_internal('IMPORT', 'operation');
          $xpath_eval = $parser->evaluate_internal('IMPORT', 'xpath');
          $cmd = trim($op_eval);

          sms_log_debug(15, "Operation: " . $cmd);
          sms_log_debug(15, "Xpath_eval: " . $xpath_eval);

          $running_conf = $sms_sd_ctx->curl($cmd, $xpath_eval, null);
          
          sms_log_info("Running configuration: " . $running_conf);
          $parser->parse($running_conf, $objects);
        }

        $this->parsed_objects = $objects;
        //debug_dump($objects, "## OBJECTS");
        //debug_object_conf($objects);
        $SMS_RETURN_BUF = json_encode($objects);
      }

      sd_disconnect();
    }
    catch (Exception $e)
    {
      return $e->getCode();
    }

    return SMS_OK;
  }


  /*
   * #####################################################################################
  * CREATE
  * #####################################################################################
  */
  function eval_CREATE()
  {
    global $SMS_RETURN_BUF;

    foreach ($this->create_list as $create)
    {
      $conf = trim($create->evaluate_operation());
      if (!empty($conf))
      {
        $xpath = trim($create->evaluate_xpath());
        $conf .= '&xpath=' . urlencode(trim($xpath));
        $conf .= '&element=';
        $xml_conf = trim($create->evaluate_xml());
        $conf_lines = preg_split("/\n/", $xml_conf);
        foreach ($conf_lines as $conf_line)
        {
          $conf .= urlencode(trim($conf_line));
        }
        $this->configuration .= "{$conf}\n";
        $SMS_RETURN_BUF .= "{$conf}\n";
      }
    }
    return SMS_OK;
  }

  /**
   * Apply created object to device and if OK add object to the database.
   */
  function apply_device_CREATE($params)
  {
    $ret = SMS_OK;
    if (!empty($this->configuration))
    {
      debug_dump($this->configuration, "CONFIGURATION TO SEND TO THE DEVICE");
      $ret = sd_apply_conf($this->configuration, true);
    }

    return $ret;
  }

  /*
   * #####################################################################################
  * UPDATE
  * #####################################################################################
  */
  function eval_UPDATE()
  {
    global $SMS_RETURN_BUF;

    foreach ($this->update_list as $update)
    {
      $conf = trim($update->evaluate_operation());
      if (!empty($conf))
      {
        $xpath = trim($update->evaluate_xpath());
        $conf .= '&xpath=' . urlencode(trim($xpath));
        $conf .= '&element=';
        $xml_conf = trim($update->evaluate_xml());
        $conf_lines = preg_split("/\n/", $xml_conf);
        foreach ($conf_lines as $conf_line)
        {
          $conf .= urlencode(trim($conf_line));
        }
        $this->configuration .= "{$conf}\n";
        //$SMS_RETURN_BUF .= "{$conf}\n0";
        $SMS_RETURN_BUF .= "{$conf}\n";
      }
    }
    return SMS_OK;
  }

  /**
   * Apply updated object to device and if OK add object to the database.
   */
  function apply_device_UPDATE($params)
  {
    $ret = SMS_OK;
    if (!empty($this->configuration))
    {
      debug_dump($this->configuration, "CONFIGURATION TO SEND TO THE DEVICE");
      $ret = sd_apply_conf($this->configuration, true);
    }
    return $ret;
  }

  /*
   * #####################################################################################
  * DELETE
  * #####################################################################################
  */
  function eval_DELETE()
  {
    global $SMS_RETURN_BUF;

    foreach ($this->delete_list as $delete)
    {
      $conf = trim($delete->evaluate_operation());
      if (!empty($conf))
      {
        $xpath = trim($delete->evaluate_xpath());
        $conf .= '&xpath=' . urlencode(trim($xpath));
        $conf .= '&element=';
        $xml_conf = trim($delete->evaluate_xml());
        $conf_lines = preg_split("/\n/", $xml_conf);
        foreach ($conf_lines as $conf_line)
        {
          $conf .= urlencode(trim($conf_line));
        }
        $this->configuration .= "{$conf}\n";
        $SMS_RETURN_BUF .= "{$conf}\n";
      }
    }
    return SMS_OK;
  }

  /**
   * Apply deleted object to device and if OK add object to the database.
   */
  function apply_device_DELETE($params)
  {
    debug_dump($this->configuration, "CONFIGURATION TO SEND TO THE DEVICE");

    $ret = sd_apply_conf($this->configuration, true);

    return $ret;
  }
}

?>
