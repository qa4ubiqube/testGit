<?php
/*
 * Version: $Id: do_get_report.php 22221 2009-09-30 12:46:20Z tmt $
 * Created: Jul 31, 2008
 * Available global variables
 * 	$sms_sd_ctx		pointer to sd_ctx context to retreive usefull field(s)
 *  $sms_sd_info	sd_info structure
 *  $sdid
 *  $sms_module		module name (for patterns)
 *  $fullReport		boolean false => VPN only
 */


// Enter Script description here

require_once 'smserror/sms_error.php';
require_once 'smsd/sms_common.php';

require_once load_once('cisco_nexus9000', 'cisco_nexus9000_connect.php');

if ($fullReport)
{
 $report_stages = array(
 0 => array("cmd" => "show logging", "descr" => "Log"),
 1 => array("cmd" => "show crypto isakmp sa", "descr" => "Current IKE Phase 1 SAs"),
 2 => array("cmd" => "show crypto ipsec sa", "descr" => "Current IPSEC Tunnels (IKE Phase 2 SAs)"),
 3 => array("cmd" => "show standby brief", "descr" => "HSRP status"),
 4 => array("cmd" => "show policy-map interface", "descr" => "QOS statistics"),
 5 => array("cmd" => "dir flash:", "descr" => "Files")
 );
}
else
{
 $report_stages = array(
 0 => array("cmd" => "show logging", "descr" => "Log"),
 1 => array("cmd" => "show crypto isakmp sa", "descr" => "Current IKE Phase 1 SAs"),
 2 => array("cmd" => "show crypto ipsec sa", "descr" => "Current IPSEC Tunnels (IKE Phase 2 SAs)")
 );
}

$SMS_RETURN_BUF = '';

function format_output(&$output, $descr)
{
	// see WebContent/style/style.css in SES + custo
	$SMS_RETURN_BUF .= <<<EOF
<span class="reportDescr">$descr</span>
<div class="reportOutput">
<pre>$output</pre>
</div>

EOF;
}

try
{
	cisco_nexus9000_connect();

	$result_string = '';
	foreach ($report_stages as $report_stage)
	{
		$cmd = $report_stage['cmd'];

		$buffer = sendexpectone(__FILE__.':'.__LINE__, $sms_sd_ctx, $cmd, "#");

		if (empty($buffer))
		{
			continue;
		}

		if(preg_match("/Invalid input/", $buffer) == 1)
		{
			$buffer = "";
		}

		$first = true;
		$line = get_one_line($buffer);
		while ($line !== false)
		{
			$line = trim($line);
			$result_string .= "$line\n";
			$line = get_one_line($buffer);
		}

		format_output($result_string, $report_stage['descr']);
		$result_string = '';
	}

	cisco_nexus9000_disconnect();
}
catch (Exception | Error $e)
{
	cisco_nexus9000_disconnect();
	return $e->getCode();
}

return SMS_OK;

?>