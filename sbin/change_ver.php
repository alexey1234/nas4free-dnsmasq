#!/usr/local/bin/php-cgi -f
<?php
include ("config.inc");
$dnsmasqversion=0;
$workdir_1 = file("/tmp/dnsmasqinstaller");
$workdir = trim($workdir_1[0]);
$mainfile = file("{$workdir}/temporary/www/extensions_dnsmasq_server.php");
$version_1 = preg_split ( "/Version/", $mainfile[3]);
$currentversion=substr($version_1[1],0,3);
if (is_array($config['dnsmasq'])) {
		if ($config['dnsmasq']['rootfolder']) { 
			$dnsmasqrootfolder = $config['dnsmasq']['rootfolder'];
			$installed_file = file($config['dnsmasq']['rootfolder']."www/extensions_dnsmasq_server.php");
			$version_2 = preg_split ( "/Version/", $installed_file[3]);
			$installed_version=substr($version_2[1],0,3);
			if ($installed_version == $currentversion) {
				$message = "No need updates \n"; 
				if (is_file("/tmp/thebrigversion") ) unlink ("/tmp/thebrigversion");
				goto met1;
				}
			else {
				$message = "You use old dnsmasq version, we update it to current\n";
				file_put_contents("/tmp/dnsmasqversion", "updated");
				}
			}
		else { $message = "You cannot have Dnsmasq installed"; 
		file_put_contents("/tmp/dnsmasqversion", "installed");
			}
		}
	else { $message = "Hello new user, We will install Dnsmasq now \n";
	file_put_contents("/tmp/dnsmasqversion", "installed"); }
met1 : echo $message."\n";
print_r ($version_2);
?>
