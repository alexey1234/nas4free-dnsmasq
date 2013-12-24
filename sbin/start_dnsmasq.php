#!/usr/local/bin/php-cgi -f
<?php
require_once ("config.inc");
if (empty($config['dnsmasq']['rootfolder'])) {echo "DNSMASQ not installed\n"; exit;}

if ( !is_file( "/etc/rc.d/dnsmasq" )) {$cmd = "install -c -o root -g wheel -m 755 ".$config['dnsmasq']['rootfolder']."sbin/dnsmasq.d /etc/rc.d/dnsmasq";  exec($cmd);} else {}
if ( !is_link ( "/usr/local/sbin/dnsmasq") ) symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq","/usr/local/sbin/dnsmasq"); else {}
require_once ($config['dnsmasq']['rootfolder']."www/function.inc");
dnsmasq_config();
if ( ! is_dir ( "/usr/local/www/ext") ) mkdir ("/usr/local/www/ext") ; else {}
if ( ! is_dir ( "/usr/local/www/ext/dnsmasq") ) mkdir ("/usr/local/www/ext/dnsmasq") ; else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_server.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_server.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_server.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_hosts.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_hosts.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_hosts.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_conf.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_conf.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_conf.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/menu.inc") ) copy ($config['dnsmasq']['rootfolder']."www/menu.inc","/usr/local/www/ext/dnsmasq/menu.inc");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_log.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_log.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_log.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_clients.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_clients.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_clients.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_check.php") ) copy ($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_check.php","/usr/local/www/ext/dnsmasq/extensions_dnsmasq_check.php");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/warn.png") ) copy ($config['dnsmasq']['rootfolder']."www/warn.png","/usr/local/www/ext/dnsmasq/warn.png");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/gradient_listhdr1_good.png") ) copy ($config['dnsmasq']['rootfolder']."www/gradient_listhdr1_good.png","/usr/local/www/ext/dnsmasq/gradient_listhdr1_good.png");else {}
if ( ! is_file ( "/usr/local/www/ext/dnsmasq/gradient_listhdr1_bad.png") ) copy ($config['dnsmasq']['rootfolder']."www/gradient_listhdr1_bad.png","/usr/local/www/ext/dnsmasq/gradient_listhdr1_bad.png");else {}
// create links to webroot
if (!is_link( "/usr/local/www/extensions_dnsmasq_clients.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_clients.php","/usr/local/www/extensions_dnsmasq_clients.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_log.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_log.php","/usr/local/www/extensions_dnsmasq_log.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_server.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_server.php","/usr/local/www/extensions_dnsmasq_server.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_hosts.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_hosts.php","/usr/local/www/extensions_dnsmasq_hosts.php");else {}
if (!is_link( "/usr/local/www/extensions_dnsmasq_conf.php") ) symlink ( "/usr/local/www/ext/dnsmasq/extensions_dnsmasq_conf.php","/usr/local/www/extensions_dnsmasq_conf.php"); else {}

if (isset($config['dnsmasq']['enable']) ) {rc_update_rcconf ("dnsmasq","enable"); rc_start_service("dnsmasq");} else {rc_update_rcconf ("dnsmasq","disable");} 

?>
