<?php
/* 
extensions_dnsmasq_server.php
Version 0.2
*/
require_once("auth.inc");
require_once("guiconfig.inc");
if (!isset($config['dnsmasq']) || !is_array($config['dnsmasq'])) header("Location: extensions_dnsmasq_conf.php");
include_once ($config['dnsmasq']['rootfolder']."dnsmasq/function.inc");
if (is_file("/var/run/dnsmasq.reload")) $warnmess = file_get_contents("/var/run/dnsmasq.reload");

$pconfig['enable'] = isset($config['dnsmasq']['enable']) ? true : false;
$pconfig['extconfig'] = isset ($config['dnsmasq']['extconfig']) ? true : false;
$pconfig['logging'] = $config['dnsmasq']['logging'];
$pconfig['noresolv'] = isset ($config['dnsmasq']['noresolv']) ? true : false;
$pconfig['startadr'] = $config['dnsmasq']['startadr'];
$pconfig['endadr'] = $config['dnsmasq']['endadr'];
$pconfig['broadcast'] = $config['dnsmasq']['broadcast'];
$pconfig['leasecount'] = $config['dnsmasq']['leasecount'];
$pconfig['leasetime'] = $config['dnsmasq']['leasetime'];
$pconfig['enabletftp'] = $config['dnsmasq']['enabletftp'];
$pconfig['tftproot1'] = $config['dnsmasq']['tftproot'];
$pconfig['tftproot2'] = $config['dnsmasq']['tftproot'];
$pconfig['tftpboot'] = $config['dnsmasq']['tftpboot'];

if (is_ajax()) {
	$extentionstatus['status'] = 0;
	if (is_file("/var/run/dnsmasq.stamp")) $extentionstatus['status'] = 1;
	render_ajax($extentionstatus);
}
if ($_POST) {
	$pconfig = $_POST;
	if (isset($_POST['Submit']) && !empty($_POST['Submit'])) {  $pconfig = $_POST;
		unset($input_errors);
		if (isset($_POST['enable'])) { 	
			$config['dnsmasq']['enable'] = TRUE; 
			$pconfig['enable'] = TRUE;
			} else { 
			unset($config['dnsmasq']['enable']);
			unset($pconfig['enable']);
			}
		if (isset($_POST['extconfig']) ) { 
			$config['dnsmasq']['extconfig'] = true;
			$pconfig['extconfig'] = TRUE;
			} else { 
			unset($config['dnsmasq']['extconfig']);
			unset($pconfig['extconfig']);
			}
		$config['dnsmasq']['logging'] = $_POST['logging'];
			
		if (empty($_POST['startadr']) || empty($_POST['endadr'])) { 
			$warningmess = "Dnsmasq will work as DNS forvarder only, without DHCP and netboot"; 
			unset($config['dnsmasq']['startadr']); unset($config['dnsmasq']['endadr']); 
			} else {
			// Input validation
			$reqdfields = explode(" ", "startadr endadr broadcast leasecount");
			$reqdfieldsn = array(gettext("DHCP range - start"), gettext("DHCP range - end"), gettext("Broadcast address"), gettext("How leases allow"));
			$reqdfieldst = explode(" ", "ipaddr ipaddr ipaddr numeric");
			//do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
			do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
			$subnet = $config['interfaces']['lan']['ipaddr']."/".$config['interfaces']['lan']['subnet'];
			if (is_ipaddr ($_POST['startadr'])) { 
			if (false == ($cnif =ip_in_subnet($_POST['startadr'],$subnet))) {$input_errors[] = "Value \"DHCP range - start\" is not belongs to the subnet LAN"; goto out;} else {} }
			if (is_ipaddr ($_POST['endadr'])) { 
				if (false == ($cnif =ip_in_subnet($_POST['endadr'],$subnet))) {$input_errors[] = "Value \"DHCP range - end\" is not belongs to the subnet LAN"; goto out;} else {} }
			if (is_ipaddr ($_POST['broadcast'])) { 
				if (false == ($cnif =ip_in_subnet($_POST['broadcast'],$subnet))) {$input_errors[] = "Value \"Broadcast address\" is not belongs to the subnet LAN"; goto out;} else {} }
				}
			if (empty($input_errors)) { 
				if ( isset($_POST['startadr']) &&  ($_POST['endadr'])) {
					$config['dnsmasq']['startadr'] =$_POST['startadr']; 
					$config['dnsmasq']['endadr'] =$_POST['endadr'];
					$config['dnsmasq']['broadcast'] =$_POST['broadcast'];
					$config['dnsmasq']['leasecount'] =$_POST['leasecount'];
					$config['dnsmasq']['leasetime'] =$_POST['leasetime'];
					}
				if (isset($_POST['noresolv']) ) $config['dnsmasq']['noresolv'] = TRUE; else unset ($config['dnsmasq']['noresolv']);
				switch ($_POST['enabletftp']) {
					case "0":
							$config['dnsmasq']['enabletftp'] = "0";
							unset ($config['dnsmasq']['tftproot']);
							unset ( $config['dnsmasq']['tftpboot']);
							break;
					case "1":
							$config['dnsmasq']['enabletftp'] = "1";
							$config['dnsmasq']['tftproot'] = $_POST['tftproot1'];
							if ( !empty($_POST['tftpboot']) && strlen ($_POST['tftpboot']) > 3 ) { 
								$config['dnsmasq']['tftpboot'] =$_POST['tftpboot'];
									} else {
								unset ( $config['dnsmasq']['tftpboot']);} ;
							break;
					case "22":
							$config['dnsmasq']['enabletftp'] = "22";
							$config['dnsmasq']['tftproot'] = $_POST['tftproot2'];
							if ( !empty($_POST['tftpboot']) && strlen ($_POST['tftpboot']) > 3 ) { 
								$config['dnsmasq']['tftpboot'] =$_POST['tftpboot'];
									} else {
								unset ( $config['dnsmasq']['tftpboot']);} ;
							break;
					}
			write_config();
			$pconfig['enable'] = isset($config['dnsmasq']['enable']) ? true : false;
			$pconfig['extconfig'] = isset ($config['dnsmasq']['extconfig']) ? true : false;
			$pconfig['logging'] = $config['dnsmasq']['logging'];
			$pconfig['noresolv'] = isset ($config['dnsmasq']['noresolv']) ? true : false;
			$pconfig['startadr'] = $config['dnsmasq']['startadr'];
			$pconfig['endadr'] = $config['dnsmasq']['endadr'];
			$pconfig['broadcast'] = $config['dnsmasq']['broadcast'];
			$pconfig['leasecount'] = $config['dnsmasq']['leasecount'];
			$pconfig['leasetime'] = $config['dnsmasq']['leasetime'];
			$pconfig['enabletftp'] = $config['dnsmasq']['enabletftp'];
			$pconfig['tftproot1'] = $config['dnsmasq']['tftproot'];
			$pconfig['tftproot2'] = $config['dnsmasq']['tftproot'];
			$pconfig['tftpboot'] = $config['dnsmasq']['tftpboot'];
			  // restart dnsmasq  
			if (isset ($config['dnsmasq']['enable'])) { 
					$savemsg = ""; 
					$warnmess =""; 
					rc_update_rcconf("dnsmasq", "enable"); 
					rc_restart_service("dnsmasq");	
				}	else { 	
					$savemsg = ""; 
					$warnmess =""; 
					rc_stop_service("dnsmasq");
				}
			}
	}
	if (isset($_POST['apply']) && ($_POST['apply'] === "Apply changes")) { 
		$savemsg = "";
		$warnmess ="";
		if (isset ($config['dnsmasq']['enable'])) {
			rc_update_rcconf("dnsmasq", "enable"); 
			rc_restart_service("dnsmasq"); 
		}	else { 	 
			rc_stop_service("dnsmasq"); 
			rc_update_rcconf("dnsmasq", "disable");
		}
		updatenotify_delete("dnsmasq");
	}
}
out:
$pgtitle = array(gettext("Extensions"),gettext("DNSMASQ"));
include("fbegin.inc");?>

<script type="text/javascript">
<!--
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(500, 2000, 'extensions_dnsmasq_server.php', null, function(data) {
		if ( typeof(data) !== 'undefined' ) {
			
				var value = data.status;
				if (value == '1') { 					
					$('.optsect_s').css("color", "green"); 
				} else {
					
					$('.optsect_s').css("color", "white");
				}
	}});
});		
			
function enable_tftp() {
	switch(document.iform.enabletftp.value) {
		case "0":
			showElementById('tftproot1_tr','hide');
			showElementById('tftproot2_tr','hide');
			showElementById('tftpboot_tr','hide');
			break;
		case "1":
			showElementById('tftproot2_tr','hide');
			showElementById('tftproot1_tr','show');
			showElementById('tftpboot_tr','show');
			break;
		case "22":
			showElementById('tftproot2_tr','show');
			showElementById('tftproot1_tr','hide');
			showElementById('tftpboot_tr','show');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_hosts_static.php"><span>Hosts</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_conf.php"><span>Config</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
			<li id="tabinact" class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
		</ul>
	</td></tr>
	<tr>
	<form action="extensions_dnsmasq_server.php" method="post" name="iform" id="iform">
		<td class="tabcont">
		<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
		<?php if ($warnmess) {dnsmasq_warning_box($warnmess); } ?>
		<?php if ($savemsg) {print_info_box($savemsg); $savemsg = ""; } ?>
		<?php if (updatenotify_exists("dnsmasq")) print_config_change_box();?>
		<table width="100%" border="0" cellpadding="5" cellspacing="0">
			<?php html_titleline_checkbox("enable", gettext("Dynamic Host Configuration Protocol"), $pconfig['enable'], gettext("Enable"), "enable_change(false)");?>
			
		<?php html_inputbox("startadr", gettext("DHCP range - start"), $pconfig['startadr'], gettext("Choice start adress for DHCP hosts"), false, 16,false);?>
		<?php html_inputbox("endadr", gettext("DHCP range - end"), $pconfig['endadr'], gettext("Choice end adress for DHCP hosts"), false, 16,false);?>
		<?php html_inputbox("broadcast", gettext("Broadcast address"), $pconfig['broadcast'], gettext("Define broadcast address."), false, 16,false);?>
		<?php html_inputbox("leasecount", gettext("How leases allow"), !empty($pconfig['leasecount']) ? $pconfig['leasecount'] : "50", gettext("Set the limit on DHCP leases, the default is 50"), false, 16,false);?>
		<?php html_inputbox("leasetime", gettext("Lease time for network"), !empty($pconfig['leasetime']) ? $pconfig['leasetime'] : "30m", gettext("Set the time for DHCP leases, m or h is minutes or hours"), false, 16,false);?>
		<?php html_checkbox("extconfig", gettext("Allow external config"), $pconfig['extconfig'], gettext("Allow support for external config files. Config files may have any name, exclude *.bak and placed into <b>".$config['dnsmasq']['rootfolder']."conf</b> folder"),"","","");?>
		<?php html_checkbox("noresolv", gettext("No read /etc/resolv.conf"), $pconfig['noresolv'], gettext("No read resolver file. This option may be checked, if need AD integration or define nameservers over scripts"),"","","");?>
		<?php html_combobox("logging", gettext("Log configuration"), $pconfig['logging'], array("mini" => gettext("System only"), "dhcp" => gettext("System+DHCP queries"), "all" => gettext("DNS+DHCP+Systems")), "", false, false, "" );?>
		<?php html_separator(); ?>
		<?php html_combobox("enabletftp", gettext("tftp server"), $pconfig['enabletftp'], array("0" => gettext("Disable"), "1" => gettext("Main"), "22" => gettext("Dnsmasq built-in")), "", false, false, "enable_tftp(false)" );?>
		<?php html_filechooser("tftproot2", gettext("TFTP root folder"), $pconfig['tftproot2'], gettext("Use tftp root folder"), !empty( $pconfig['tftproot']) ? $pconfig['tftproot'] : $config['dnsmasq']['rootfolder'] . "tftproot/", true, 60);?>
		<?php 
		if (isset ($config['tftpd']['enable'])) $message = gettext("Use tftp root folder"); else $message = "<b> TFTPd must be enabled!</b>";
		html_inputbox("tftproot1", gettext("TFTP root folder"), $config['tftpd']['dir'], $message, true, 60,true );?>
		<?php html_inputbox("tftpboot", gettext("Boot kernel name"), $pconfig['tftpboot'], gettext("Define first boot kernel name"), true, 60,false);?>
		<tr>
			<td><div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" />
				</div>
			</td>
		</tr>
	</table>
	</td>
	<?php include("formend.inc");?>
	</form>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_tftp();
//-->
</script>
<?php include("fend.inc"); ?>
