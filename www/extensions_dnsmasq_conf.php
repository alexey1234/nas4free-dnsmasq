<?php
/*
extensions_dnsmasq_conf.php
*/
ob_start();
require("auth.inc");
require("guiconfig.inc");
if (!isset($config['dnsmasq']) || !is_array($config['dnsmasq'])) $config['dnsmasq']=array();
if (!isset($config['dnsmasq']['rootfolder']) ) {
		if(file_exists( '/tmp/dnsmasq.tmp' )  ) {
			$config['dnsmasq']['rootfolder'] = rtrim( file_get_contents('/tmp/dnsmasq.tmp') );
	
			if ( $config['dnsmasq']['rootfolder'][strlen($config['dnsmasq']['rootfolder'])-1] != "/")  { $config['dnsmasq']['rootfolder'] = $config['dnsmasq']['rootfolder'] . "/";	}
			$pconfig['rootfolder'] = $config['dnsmasq']['rootfolder'];
	
			}
		else { 		$input_errors[] = "Dnsmasq not installed"; 	} 
		}
	$pconfig['rootfolder'] = $config['dnsmasq']['rootfolder'];	
if ($_POST) {
	
	unset($input_errors);
	$pconfig = $_POST;
	 
	if ( $pconfig['remove'] ) {
		
		// we want to remove dnsmasq
		// dnsmasq_unregister();
		// Browse back to the main page
		header("Location: /");
		exit;
		}
	else { 
		if ( $pconfig['rootfolder'][strlen($pconfig['rootfolder'])-1] != "/")  { $pconfig['rootfolder'] = $pconfig['rootfolder'] . "/"; } else {}
		if ( !is_dir( $pconfig['rootfolder'] ) && !isset($pconfig['remove']) ) {  $input_errors[] = "Not existent folder"; 	}
		else if ( !is_writable( $pconfig['rootfolder'] ) && !isset($pconfig['remove']) ){ $input_errors[] = "Not writible folder"; 	}
	}
	
	if ( !$input_errors ){ 
	$config['dnsmasq']['rootfolder'] = $pconfig['rootfolder'];
	write_config();
	unlink_if_exists("/tmp/dnsmasq.tmp");
	if ( !is_file( "/etc/rc.d/dnsmasq" )) {$cmd = "install -c -o root -g wheel -m 755 ".$config['dnsmasq']['rootfolder']."sbin/dnsmasq.d /etc/rc.d/dnsmasq";  exec($cmd);} else {}
	if ( !is_link ( "/usr/local/sbin/dnsmasq") ) symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq","/usr/local/sbin/dnsmasq"); else {}
	// Add startup command
	$i = 0;
	if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		for ($i; $i < count($config['rc']['postinit']['cmd']); $i++) {
			if (preg_match('/dnsmasq_start\.php/', $config['rc']['postinit']['cmd'][$i]))
				unset($config['rc']['postinit']['cmd'][$i]);	// Disable the old startup
		 	}	
		} 
	// update the value of the postinit command.
	$config['rc']['postinit']['cmd'][$i] = "/usr/local/bin/php-cgi {$config[''dnsmasq']['rootfolder']}sbin/dnsmasq_start.php";
	header ("Location: /extensions_dnsmasq_server.php");
	}
}
$pgtitle = array(gettext("Extensions"),gettext("DNSMASQ"), gettext("config"));
include("fbegin.inc"); ?>
<script language="JavaScript">
function disable_buttons() {
	document.iform.Submit.disabled = true;
	document.iform.submit();}
function message(obj) {
	if (obj.checked) {
		alert('If you want to uninstall the DNSMASQ, please make sure that all files have been removed');
	}
		return true;
}
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">

	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
			
			<li class="tabact"><a href="extensions_dnsmasq_conf.php"><span>config</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
		</ul>
	</td></tr>
	<tr><td class="tabcont">
	<?php
if ( $input_errors ) { 	print_input_errors( $input_errors ); }
// This will alert the user to unsaved changes, and prompt the changes to be saved.
else if ($savemsg) { print_info_box($savemsg); }
?>	
		<form action="extensions_dnsmasq_conf.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php html_titleline(gettext("Extension dnsmasq basic setting"));?>
		<?php html_inputbox("rootfolder", gettext("Extension working folder"), $pconfig['rootfolder'], gettext("folder where extension sets"), true, 50);?>
	 	<?php //html_filechooser("rootfolder", gettext("Media Directory"), $pconfig['rootfolder'], gettext("Directory that contains our jails (e.g /mnt/Mount_Point/Folder). We will create folder /mnt/Mount_Point/Folder/dnsmasq/"), $g['media_path'], true);?>
		<?php html_separator();?>		
		<?php html_titleline(gettext(" remove extension"));?>
		
		<!-- This is the row beneath the title -->
		<tr><td width="22%" valign="top" class="vncellreq">&nbsp;</td>
			<td width="78%" class="vtable">
				<input type="checkbox" name="remove" value="1" onclick="return message(this);"> check  for remove extension <b>Need attension</B></input>
			</td>
		</tr>
			
		<!-- This is the Save button -->
		<tr><td width="22%" valign="top">&nbsp;</td>
			<td width="78%">
			 	<input name="Submit" type="submit" class="formbtn" value="Action" onClick="disable_buttons();">
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>


</td></tr>
</table>


<?php 	include("fend.inc"); ?>
