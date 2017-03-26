<?php
/*
extensions_dnsmasq_host.php
variables:

*/
require("auth.inc");
require("guiconfig.inc");
include_once ($config['dnsmasq']['rootfolder']."dnsmasq/function.inc");
if (isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts']) ) {
		$a_hosts = &$config['dnsmasq']['hosts']; 
	} else { 
		$config['dnsmasq']['hosts'] =array(); 
	}

unset($host);
$etc_hosts = &$config['system']['hosts'];
			if(empty($etc_hosts)):
			else:
				array_sort_key($etc_hosts,'name');
			endif;	




	//GET
if (isset($_GET) && $_GET['act'] == "new") {
	$pconfig['uuid'] = uuid();
	$pconfig['macaddr'] = "";
	$pconfig['ipadress'] = "";
	$pconfig['hostname'] = "";
	$pconfig['leasetime'] = "";
	$pconfig['act'] = "new";
}
if (isset($_GET) && $_GET['act'] == "edit") {
	if (isset($_GET['uuid']) && (FALSE !== ($cnid = array_search_ex($_GET['uuid'], $config['dnsmasq']['hosts'], "uuid")))) {
	$pconfig['uuid'] = $config['dnsmasq']['hosts'][$cnid]['uuid'];
	$pconfig['act'] = "edit";
	$pconfig['macaddr'] = $config['dnsmasq']['hosts'][$cnid]['macaddr'];
	$pconfig['ipadress'] = $config['dnsmasq']['hosts'][$cnid]['ipadress'];
	$pconfig['hostname'] = $config['dnsmasq']['hosts'][$cnid]['hostname'];
	$pconfig['leasetime'] = $config['dnsmasq']['hosts'][$cnid]['leasetime'];
	
	
}
}
if (isset($_GET) && $_GET['act'] == "del") {
	if (true === delete_dnsmasqhost ($_GET['uuid'])) { 
	updatenotify_set("dnsmasq", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	write_config();
	}
	echo "<script>window.opener.location.reload(); window.close();</script>";
}
if ($_POST) {
	if ( isset($_POST['cancel']))  {
		echo "<script>window.close();</script>";
		exit;
	}
	unset ($input_errors);
	
	$pconfig = $_POST;
	if ( isset($_POST['act']) && $_POST['act'] == "edit") {
		delete_dnsmasqhost ( $_POST['uuid'] );
		$a_hosts = &$config['dnsmasq']['hosts'];
		$etc_hosts = &$config['system']['hosts'];
	}
	if ( isset($_POST['act']) ) {
	//validation
		if ( is_array ($a_hosts)) {  
			if ( FALSE !==  ($index = array_search_ex($_POST['ipadress'], $a_hosts, "ipadress"))) { 
				$input_errors[] = "IP address duplicate. It must be unique"; }
			if ($_POST['ipadress'] == $config['interfaces']['lan']['ipaddr'] ) { 
				$input_errors[] = "IP address is LAN IP address. Wow!! ";  }
			if ( FALSE !==  ($index = array_search_ex($_POST['hostname'], $a_hosts, "hostname"))) { 
				$input_errors[] = "Hostname duplicate. It must be unique";  }
			// Check add host to system
			if ( FALSE !==  ($index = array_search_ex($_POST['ipadress'], $etc_hosts, "address"))) { 
					$input_errors[] = "IP address finded into /etc/hosts. Please close this Pop-up and remove entry at /etc/hosts"; }
			if ( FALSE !==  ($index = array_search_ex($_POST['hostname'], $etc_hosts, "name"))) { 
					$input_errors[] = "Hostname finded into /etc/hosts. Please close this Pop-up and remove entry at /etc/hosts";  }
			$pconfig['macaddr']=trim($pconfig['macaddr']);
			if (1== preg_match("/,/",$pconfig['macaddr']) ){
				$macadrstring = explode(",", $pconfig['macaddr']);
				foreach ($macadrstring as $testmac){
					if (FALSE == is_macaddr ($testmac)) {
						$input_errors[] = sprintf( gtext("The attribute '%s' is not a valid MAC address."),$testmac);
					}
					if ( FALSE !==  ($index = array_search_ex($testmac, $a_hosts, "macaddr"))) { 
						$input_errors[] = sprintf( "MAC address duplicate. It must be unique",$testmac);  
					}
				}
			} else {
				if (FALSE == is_macaddr ($pconfig['macaddr'])) {
						$input_errors[] = sprintf( gtext("The attribute '%s' is not a valid MAC address."),$pconfig['macaddr']);
					}
					if ( FALSE !==  ($index = array_search_ex($testmac, $a_hosts, "macaddr"))) { 
						$input_errors[] = sprintf( "MAC address duplicate. It must be unique",$pconfig['macaddr']);  
					}
			}
			}
			
    // End validation
	
	if (empty($input_errors)) {
	$cnid = array_search_ex($pconfig['uuid'], $config['system']['hosts'], "uuid");
	
// add host to /etc/hosts
	
	$host = array();
	$host['uuid'] = $_POST['uuid'];
	$host['name'] = $_POST['hostname'];
	$host['address'] = $_POST['ipadress'];
	$host['descr'] = "dnsmasq host";
	if (isset($_POST['uuid']) && (FALSE !== $cnid)) {
			$config['system']['hosts'][$cnid] = $host;			
		} else {
		$config['system']['hosts'][] = $host;
		}
	$cnid = array_search_ex($pconfig['uuid'], $config['dnsmasq']['hosts'], "uuid");
	$hosts = array();
	
	$hosts['uuid'] = $pconfig['uuid'];
		
	$hosts['macaddr'] = $pconfig['macaddr'];
	$hosts['ipadress'] = $pconfig['ipadress'];
	$hosts['hostname'] = $pconfig['hostname'];
	$hosts['leasetime'] = "infinity";
	if (isset($_POST['uuid']) && (FALSE !== $cnid)) { 
		$config['dnsmasq']['hosts'][$cnid] = $hosts; 
		$mode = UPDATENOTIFY_MODE_MODIFIED; 
	} else {	
		$config['dnsmasq']['hosts'][] = $hosts; 
		$mode = UPDATENOTIFY_MODE_NEW;	
		}
	updatenotify_set("dnsmasq", $mode, $hosts['uuid']);
	write_config();
	echo "<script>window.opener.location.reload();window.close();</script>";
		exit;
	}
	}
}	
out:
header("Content-Type: text/html; charset=" . system_get_language_codeset());
?>
<!DOCTYPE html>
<html lang="<?=system_get_language_code();?>">
	<head>
		<meta charset="<?=system_get_language_codeset();?>"/>
		<title><?=gtext("dnsmasq host");?></title>
		<link href="css/gui.css" rel="stylesheet" type="text/css">
		<link href="css/fc.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/gui.js"></script>
		
  
	</head>
	<body class="filechooser">
		<table cellspacing="0"><tbody><tr><td class="navbar">
			<table>
			
			
			<form id="myForm" action="extensions_dnsmasq_hosts.php" method="post"> 
			
			<?php if ($input_errors) : ?>
					<tr>
					<td><img src="images/error_box.png"/></td>
					<td><?php 
						unset ($errorout);
						foreach ($input_errors as $error) {
						$errorout = "<li>" . $error ."</li>"; 
					}
					echo $errorout;?>
					</td>
					</tr>
					<?php endif; ?>
				<tr>
					<td>MAC: </td>
					<td><input type="text" placeholder="MAC" size="55" name="macaddr" required pattern=".{17,}" title="Please enter valid MAC address" value="<?=$pconfig['macaddr']; ?>" /><br /><span>allow multiple(2) comma separated MACs</span></td>
				</tr>
				<tr>
					<td>IP adress: </td>
					<td><input type="text" name="ipadress" size="20" placeholder="IP" required pattern="^(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}$" title="Please enter valid IP address" value="<?=$pconfig['ipadress']; ?>" /></td>
				</tr>
				<tr>
					<td>Hostname: </td>
					<td><input type="text" name="hostname" size="30" placeholder="hostname" required pattern="^[a-z0-9\-]+$" title="Please enter valid hostname" value="<?=$pconfig['hostname']; ?>" /></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" value="Save" />
						<input type="submit" name="cancel" value="Cancel" />
						<input name="uuid" type="hidden" value="<?php echo $pconfig['uuid']; ?>" />
						<input name="act" type="hidden" value="<?php echo $pconfig['act']; ?>" />
					</td>
					
				</tr>
			
			
			<?php include 'formend.inc'; ?>
			</form>	</td></tr></tbody>
			</table>
		</table>
	</body>
</html>