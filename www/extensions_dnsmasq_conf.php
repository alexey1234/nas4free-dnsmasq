<?php
/*
extensions_dnsmasq_conf.php
*/
ob_start();
require("auth.inc");
require("guiconfig.inc");
require_once("XML/Serializer.php");
require_once("XML/Unserializer.php");
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
	if (isset($_POST['export']) && $_POST['export']) {
	$options = array(
			XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
			XML_SERIALIZER_OPTION_INDENT           => "\t",
			XML_SERIALIZER_OPTION_LINEBREAKS       => "\n",
			XML_SERIALIZER_OPTION_XML_ENCODING     => "UTF-8",
			XML_SERIALIZER_OPTION_ROOT_NAME        => get_product_name(),
			XML_SERIALIZER_OPTION_ROOT_ATTRIBS     => array("version" => get_product_version(), "revision" => get_product_revision()),
			XML_SERIALIZER_OPTION_DEFAULT_TAG      => "hosts",
			XML_SERIALIZER_OPTION_MODE             => XML_SERIALIZER_MODE_DEFAULT,
			XML_SERIALIZER_OPTION_IGNORE_FALSE     => true,
			XML_SERIALIZER_OPTION_CONDENSE_BOOLS   => true,
	);

	$serializer = new XML_Serializer($options);
	$status = $serializer->serialize($config['dnsmasq']);

	if (@PEAR::isError($status)) {
		$errormsg = $status->getMessage();
	} else {
		$ts = date("YmdHis");
		$fn = "dnsmasq-{$config['system']['hostname']}.{$config['system']['domain']}-{$ts}.dnsmasq";
		$data = $serializer->getSerializedData();
		$fs = strlen($data);

		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename={$fn}");
		header("Content-Length: {$fs}");
		header("Pragma: hack");
		echo $data;

		exit;
	}
} else if (isset($_POST['import']) && $_POST['import']) {
	if (is_uploaded_file($_FILES['jailsfile']['tmp_name'])) {
		$options = array(
				XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'array',
				XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => true,
				XML_UNSERIALIZER_OPTION_FORCE_ENUM  => $listtags,
		);

		$unserializer = new XML_Unserializer($options);
		$status = $unserializer->unserialize($_FILES['jailsfile']['tmp_name'], true);

		if (@PEAR::isError($status)) {
			$errormsg = $status->getMessage();
		} else {
			// Take care array already exists.
			if (!isset($config['dnsmasq']) || !is_array($config['dnsmasq']))
				$config['dnsmasq'] = array();

			$data = $unserializer->getUnserializedData();

			
			write_config();

			header("Location: extensions_dnsmasq.php");
			exit;
		}
	} else {
		$errormsg = sprintf("%s %s", gettext("Failed to upload file."),
				$g_file_upload_error[$_FILES['jailsfile']['error']]);
	}
} 
	if ( $pconfig['remove'] ) {
		
		// we want to remove dnsmasq
		
		$i = 0;
 		if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
 			for ($i; $i < count($config['rc']['postinit']['cmd']); $i++) {
 			if (true == ($cnid = preg_match('/start_dnsmasq/', $config['rc']['postinit']['cmd'][$i]))) unset($config['rc']['postinit']['cmd'][$i]);	else {}
				} 
		}
		foreach ( glob( "{$config['dnsmasq']['rootfolder']}conf/ext/dnsmasq/*.php" ) as $file ) {
 			$file = str_replace("{$config['dnsmasq']['rootfolder']}conf/ext/dnsmasq", "/usr/local/www", $file);
 			if ( is_link( $file ) ) { unlink( $file ); 	} 
 		} 
		foreach ( glob( "/usr/local/www/ext/dnsmasq/*" ) as $file ) { unlink( $file ); 	}
		if ( is_dir( "/usr/local/www/ext/dnsmasq/" ) ) {  rmdir( "/usr/local/www/ext/dnsmasq/" );  	}
		if ( is_link( "/usr/local/sbin/dnsmasq" ) ) unlink( "/usr/local/sbin/dnsmasq" ); else {}	
		if ( is_file( "/etc/rc.d/dnsmasq" ) ) unlink( "/etc/rc.d/dnsmasq" ); else {}
		unset ($config['dnsmasq']);
		write_config();
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
	// Add startup command
	$i = 0;
	if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		for ($i; $i < count($config['rc']['postinit']['cmd']); $i++) {
			if (false == ($cnid = preg_match('/start_dnsmasq/', $config['rc']['postinit']['cmd'][$i]))) {} else { break;}			
		 	}
			$config['rc']['postinit']['cmd'][$i] = "/usr/local/bin/php-cgi {$config['dnsmasq']['rootfolder']}sbin/start_dnsmasq.php";
		} 
	write_config();
	unlink_if_exists("/tmp/dnsmasq.tmp");
	if ( !is_file( "/etc/rc.d/dnsmasq" )) {$cmd = "install -c -o root -g wheel -m 755 ".$config['dnsmasq']['rootfolder']."sbin/dnsmasq.d /etc/rc.d/dnsmasq";  exec($cmd);} else {}
	if ( !is_link ( "/usr/local/sbin/dnsmasq") ) symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq","/usr/local/sbin/dnsmasq"); else {}
	if (!is_dir ( $config['dnsmasq']['rootfolder']."tftproot") ) mkdir ( $config['dnsmasq']['rootfolder']."tftproot", 0777); else {}
	if (!is_dir ( $config['dnsmasq']['rootfolder']."conf") ) mkdir ( $config['dnsmasq']['rootfolder']."conf", 0777); else {}
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
		<?php html_separator();?>
		<?php html_titleline(gettext("Configuration Backup/Restore"));?>
			 	<tr>
						<td width="22%" valign="top" class="vncell">Backup Existing Config&nbsp;</td>
						<td width="78%" class="vtable">
							<?=gettext("Make a backup of the existing configuration. Usefull way send downloaded config to forum for help ");?><br />
							<div id="submit">
								<input name="export" type="submit" class="formbtn" value="<?=gettext("Export");?>" /><br />
							</div>
						</td>
					</tr>
			<!---		<tr>
						<td width="22%" valign="top" class="vncell">Restore&nbsp;</td>
						<td width="78%" class="vtable">
							<?=gettext("Restore jails config from XML.");?><br />
							<div id="submit">
								<input name="jailsfile" type="file" class="formfld" id="jailsfile" size="40" accept="*.jails" />&nbsp;
								<input name="import" type="submit" class="formbtn" id="import" value="<?=gettext("Import");?>" /><br />
							</div>
						</td>
					</tr> -->
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
