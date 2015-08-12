<?php
/* 
extensions_dnsmasq_clients.php
Version 0.1
*/
require("auth.inc");
require("guiconfig.inc");
include_once ($config['dnsmasq']['rootfolder']."dnsmasq/function.inc");
$pgtitle = array(gettext("Extensions"),gettext("DHCP clients table"));
include("fbegin.inc");?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
			
			<li class="tabinact"><a href="extensions_dnsmasq_conf.php"><span>config</span></a></li>
			<li class="tabact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
			<!--<li class="tabinact"><a href="extensions_thebrig_log.php"><span><?=gettext("Log");?></span></a></li> -->
					
		</ul>
	</td></tr>
	<tr>
		<td class="tabcont">
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
			 <?php if (is_file( "/var/db/dnsmasq.leases")) $leasefile = file("/var/db/dnsmasq.leases");
					if ( (count ( $leasefile )) < 1) print "No leases"; else { ?>
				<tr>
					<td width="33%" class="listhdrlr">&nbsp;MAC address</td>
					<td width="33%" class="listhdrlr">&nbsp;IP address</td>
					<td width="33%" class="listhdrlr">&nbsp;Host name</td>
				</tr>
				<tr>
				 <?php
					for ($i=0;  ($i < count ( $leasefile )) ; $i++) {
					$value = explode (" ",$leasefile[$i]) ;
					echo "<td width=\"33%\" class=\"listr\">&nbsp;".$value[1]."</td>";
					echo "<td width=\"33%\" class=\"listr\">&nbsp;".$value[2]."</td>";
					echo "<td width=\"33%\" class=\"listr\">&nbsp;".$value[3]."</td></tr>";
}
}
?>
			
			</table>
		</td>
	</tr>
</table>
<?php include("fend.inc"); ?>
