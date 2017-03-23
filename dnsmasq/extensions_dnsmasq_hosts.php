<?php
/*
extensions_dnsmasq_host.php
variables:

*/
require("auth.inc");
require("guiconfig.inc");
if (isset($config['dnsmasq']['hosts']) && is_array($config['dnsmasq']['hosts']) ) {
		$a_hosts = &$config['dnsmasq']['hosts']; 
	} else { 
		$config['dnsmasq']['hosts'] =array(); 
	}

unset($host);
$etc_hosts = &array_make_branch($config,'system','hosts');
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

}
if (isset($_GET) && $_GET['act'] == "edit") {
	if (isset($_GET['uuid']) && (FALSE !== ($cnid = array_search_ex($_GET['uuid'], $config['dnsmasq']['hosts'], "uuid")))) {
	$pconfig['uuid'] = $config['dnsmasq']['hosts'][$cnid]['uuid'];
	
	$pconfig['macaddr'] = $config['dnsmasq']['hosts'][$cnid]['macaddr'];
	$pconfig['ipadress'] = $config['dnsmasq']['hosts'][$cnid]['ipadress'];
	$pconfig['hostname'] = $config['dnsmasq']['hosts'][$cnid]['hostname'];
	$pconfig['leasetime'] = $config['dnsmasq']['hosts'][$cnid]['leasetime'];
	
}
}
if (isset($_GET) && $_GET['act'] == "del") {
	unset ($configchange);
	$cnid = array_search_ex( $_GET['uuid'], $config['dnsmasq']['hosts'], "uuid");
	if (FALSE !== $cnid) {	
		unset($config['dnsmasq']['hosts'][$cnid]); 	
		$configchange="1"; 
		}
	$hostid = array_search_ex($_GET['uuid'], $config['system']['hosts'], "uuid");
	if (FALSE !== $hostid) {
		unset($config['system']['hosts'][$hostid]);
		$configchange="1";
		}
	if ($configchange) { 
	updatenotify_set("dnsmasq", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	write_config();
	}
	echo "<script>window.opener.location.reload(); window.close();</script>";
}
if ($_POST) {
	unset ($input_errors);
	file_put_contents ("/tmp/post", serialize ($_POST));
	$pconfig = $_POST;
	if ( isset($_POST['act']) && $_POST['act'] == "new") {
	//validation
	if ( is_array ($a_hosts)) {  
		if ( FALSE !==  ($index = array_search_ex($_POST['macaddr'], $a_hosts, "macaddr"))) { 
				$input_errors[] = "MAC address duplicate. It must be unique"; goto out; }
		if ( FALSE !==  ($index = array_search_ex($_POST['ipadress'], $a_hosts, "ipadress"))) { 
				$input_errors[] = "IP address duplicate. It must be unique"; goto out; }
		if ($_POST['ipadress'] == $config['interfaces']['lan']['ipaddr'] ) { 
				$input_errors[] = "IP address is LAN IP address. Wow!! "; goto out; }
		if ( FALSE !==  ($index = array_search_ex($_POST['hostname'], $a_hosts, "hostname"))) { 
				$input_errors[] = "Hostname duplicate. It must be unique"; goto out; }
			// Check add host to system
		if ( FALSE !==  ($index = array_search_ex($_POST['ipadress'], $etc_hosts, "address"))) { 
					$input_errors[] = "IP address finded into /etc/hosts. Please close this Pop-up and remove entry at /etc/hosts"; goto out; }
		if ( FALSE !==  ($index = array_search_ex($_POST['hostname'], $etc_hosts, "name"))) { 
					$input_errors[] = "Hostname finded into /etc/hosts. Please close this Pop-up and remove entry at /etc/hosts"; goto out; }
			}
    // End validation
	}
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
	$hosts['leasetime'] = $pconfig['leasetime'];
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
		
    <script type="text/javascript"> 
		
    
    function refreshParent() { window.opener.location.reload(); }

       
        $(document).ready(function() { 
            $('#myForm').submit(); 
			window.onunload = refreshParent;
        }); 
		
	</script> 
	</head>
	<body class="filechooser">
		<table cellspacing="0"><tbody><tr><td class="navbar">
			
			<form id="myForm" action="extensions_dnsmasq_hosts.php" method="post"> 
			
			<table>
				<tr>
					<td>MAC: </td>
					<td><input type="text" placeholder="MAC" name="macaddr" required pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" title="Please enter valid MAC address" value="<?=$pconfig['macaddr']; ?>" /></td>
				</tr>
				<tr>
					<td>IP adress: </td>
					<td><input type="text" name="ipadress" placeholder="IP" required pattern="^(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}$" title="Please enter valid IP address" value="<?=$pconfig['ipadress']; ?>" /></td>
				</tr>
				<tr>
					<td>Hostname: </td>
					<td><input type="text" name="hostname" placeholder="hostname" required pattern="^[a-z0-9\-]+$" title="Please enter valid hostname" value="<?=$pconfig['hostname']; ?>" /></td>
				</tr>
				<tr>
					<td>Lease time: </td>
					<td><input type="text" name="leasetime" placeholder="time, minutes or infinite" required pattern="^[0-9\-]+$" || "^infinite" title="Please enter lease time for host in minutes or infinite" value="<?=$pconfig['leasetime']; ?>" /></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" value="Save" />
						<input name="uuid" type="hidden" value="<?php echo $_GET['uuid']; ?>" />
						<input name="act" type="hidden" value="<?php echo $_GET['act']; ?>" />
					</td>
					
				</tr>
			</table>
			<?php include 'formend.inc'; ?>
			</form>	</td></tr></tbody>
		</table>
	</body>
</html>