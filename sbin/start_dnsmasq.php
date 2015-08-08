#!/usr/local/bin/php-cgi -f
<?php
require_once ("config.inc");
if (empty($config['dnsmasq']['rootfolder'])) {echo "DNSMASQ not installed\n"; exit;}

if ( !is_file( "/etc/rc.d/dnsmasq" )) {$cmd = "install -c -o root -g wheel -m 755 ".$config['dnsmasq']['rootfolder']."sbin/dnsmasq.d /etc/rc.d/dnsmasq";  exec($cmd);} else {}
if ( !is_link ( "/usr/local/sbin/dnsmasq") ) symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq","/usr/local/sbin/dnsmasq"); else {}
require_once ($config['dnsmasq']['rootfolder']."www/function.inc");
dnsmasq_config();
if ( ! is_dir ( "/usr/local/www/ext") ) mkdir ("/usr/local/www/ext") ; else {}
if (!is_link( "/usr/local/www/ext/dnsmasq" )) symlink ( $config['dnsmasq']['rootfolder']."www","/usr/local/www/ext/dnsmasq"); else {}

// create links to webroot
if (!is_link( "/usr/local/www/extensions_dnsmasq_clients.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_clients.php","/usr/local/www/extensions_dnsmasq_clients.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_log.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_log.php","/usr/local/www/extensions_dnsmasq_log.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_server.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_server.php","/usr/local/www/extensions_dnsmasq_server.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_hosts.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_hosts.php","/usr/local/www/extensions_dnsmasq_hosts.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_conf.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_conf.php","/usr/local/www/extensions_dnsmasq_conf.php"); else {}

if (isset($config['dnsmasq']['enable']) ) {rc_update_rcconf ("dnsmasq","enable"); rc_start_service("dnsmasq");} else {rc_update_rcconf ("dnsmasq","disable");} 

?>
