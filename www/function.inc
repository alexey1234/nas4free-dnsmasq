<?php

function dnsmasq_config() {
global $config;
$rootfolder = $config['dnsmasq']['rootfolder'];
// $dnsmasqhosts = array();
$dhcphost="";
// copy part of config in temporary array
$dnsmasqhosts = $config['dnsmasq']['hosts'];
$file = "/etc/dnsmasq.conf";
$handle=fopen($file, "w+");
// defaults
fwrite ($handle, "#Defaults\nlog-facility=/var/log/dnsmasq.log\ndhcp-leasefile=/var/db/dnsmasq.leases\nuser=nobody\ngroup=nobody\ndomain-needed\nbogus-priv\ndomain=local\nexpand-hosts\nlocal=/local/\ndhcp-option=23,50\ndhcp-authoritative\n");
// defauls from Nas4free main config
fwrite ($handle, "#Build from main config\n");
fwrite ($handle, "listen-address=".$config['interfaces']['lan']['ipaddr']."\n" );
fwrite ($handle, "interface=".$config['interfaces']['lan']['if']."\n" );
fwrite ($handle, "dhcp-option=option:router,".$config['interfaces']['lan']['gateway']."\n" );
fwrite ($handle, "dhcp-option=42,0.0.0.0\n" );

//settings
fwrite ($handle, "#Setting cver GUI\n");
if (isset ($config['dnsmasq']['extconfig'])) fwrite ($handle, "conf-dir=".$config['dnsmasq']['rootfolder']."conf\n"); else {}
switch ($config['dnsmasq']['logging']) {
		case "all": 
		      fwrite ($handle, "log-queries\n");
		      fwrite ($handle, "log-dhcp\n");
			break;
		case "dhcp":
		      fwrite ($handle, "log-dhcp\n");
			break;
	}
fwrite ($handle, "dhcp-range=".$config['dnsmasq']['startadr'].",".$config['dnsmasq']['endadr'].",".$config['dnsmasq']['leasetime']."m\n" );
if (isset( $config['dnsmasq']['enabletftp'])) { fwrite ($handle, "enable-tftp\n" );  fwrite ($handle, "tftp-root=".$config['dnsmasq']['rootfolder']."tftproot\n" ); } else { }
if (isset( $config['dnsmasq']['tftpboot'])) { fwrite ($handle, "dhcp-boot=".$config['dnsmasq']['tftpboot']."\n" );} else { }
fwrite ($handle, "dhcp-lease-max=".$config['dnsmasq']['leasecount']."\n" );
 // Hosts
 If (is_array($dnsmasqhosts)) { 
array_sort_key($dnsmasqhosts, "hostno");
foreach ($dnsmasqhosts as $out_hosts ) {
	  if (!empty ($out_hosts['macaddr']) ) { 
	      if ( empty($out_hosts['ipadress']) && empty($out_hosts['hostname'])) { mwexec ("logger  dhcp entry wrong"); exit;}
		      else { $dhcphost = "dhcp-host=".$out_hosts['macaddr']; 
			      if (!empty($out_hosts['ipadress'])) $dhcphost = $dhcphost.",".$out_hosts['ipadress']; else $dhcphost = $dhcphost;
			      if (!empty($out_hosts['hostname'])) $dhcphost = $dhcphost.",".$out_hosts['hostname']; else $dhcphost = $dhcphost;
			      $dhcphost = $dhcphost.",".$out_hosts['leasetime']; } }
	  else  $dhcphost = "dhcp-host=".$out_hosts['hostname']; 
fwrite($handle,$dhcphost."\n");
}
}
fclose($handle);
}
function dnsmasq_warning_box($msg) {
	
	echo "<div id='warningbox'>\n";
	echo "  <table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
	echo "    <tr>\n";
	echo "      <td class=\"icon\" align=\"center\" valign=\"center\"><img src=\"/ext/dnsmasq/warn.png\" alt=\"\" /></td>\n";
	echo "      <td class=\"message\">{$msg}</td>\n";
	echo "    </tr>\n";
	echo "  </table>\n";
	echo "</div>\n";
}
function is_ip_in_lan ($ipaddr) {
global $config;
$subnet = $config['interfaces']['lan']['ipaddr']."/".$config['interfaces']['lan']['subnet'];
if (TRUE == ($cnid=ip_in_subnet($ipaddr,$subnet)) ) return TRUE; else return FALSE;
}

