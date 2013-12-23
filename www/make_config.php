#!/usr/local/bin/php-cgi -f
<?php
require_once ("config.inc");
$rootfolder = $config['dnsmasq']['rootfolder'];
$dnsmasqhosts = array();
$dhcphost="";
// copy part of config in temporary array
$dnsmasqhosts = $config['dnsmasq']['hosts'];
$file = "/mnt/disk2/app/dnsmasq/dnsmasq.conf";
$handle=fopen($file, "w");
// defaults
fwrite ($handle, "log-facility=/var/log/dnsmasq.log\ndhcp-leasefile=/var/db/dnsmasq.leases\nuser=nobody\ngroup=nobody\ndomain-needed\nbogus-priv\ndomain=local\nexpand-hosts\nlocal=/local/\ndhcp-option=23,50\ndhcp-authoritative\n");
// defauls from Nas4free config
fwrite ($handle, "listen-address=".$config['interfaces']['lan']['ipaddr']."\n" );
fwrite ($handle, "interface=".$config['interfaces']['lan']['if']."\n" );
fwrite ($handle, "dhcp-option=option:router,".$config['interfaces']['lan']['gateway']."\n" );
fwrite ($handle, "dhcp-option=option:ntp-server,".$config['system']['ntp']['timeservers']."\n" );

//settings
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
if (isset( $config['dnsmasq']['enabletftp'])) { fwrite ($handle, "enable-tftp\n" );  fwrite ($handle, "tftp-root=".$config['dnsmasq']['rootfolder']."tftproot\n" ); fwrite ($handle, "dhcp-boot=".$config['dnsmasq']['tftpboot']."\n" );} else { }
fwrite ($handle, "dhcp-lease-max=".$config['dnsmasq']['leasecount']."\n" );
 // Hosts
array_sort_key($dnsmasqhosts, "hostno");
foreach ($dnsmasqhosts as $out_hosts ) {
	  if (!empty ($out_hosts['macaddr']) ) { 
	      if ( empty($out_hosts['ipadress']) && empty($out_hosts['hostname'])) { mwexec ("logger  dhcp entry wrong"); exit;}
		      else { $dhcphost = "dhcp-host=".$out_hosts['macaddr']; 
			      if (!empty($out_hosts['ipadress'])) $dhcphost = $dhcphost.",".$out_hosts['ipadress']; else $dhcphost = $dhcphost;
			      if (!empty($out_hosts['hostname'])) $dhcphost = $dhcphost.",".$out_hosts['hostname']; else $dhcphost = $dhcphost;
			      $dhcphost = $dhcphost.",".$out_hosts['leasetime']; } }
	  else  $dhcphost = $out_hosts['hostname']; 
fwrite($handle,$dhcphost."\n");
}
fclose($handle);
$result = rc_getenv("dnsmasq_enable");
echo $result."\n";
?>