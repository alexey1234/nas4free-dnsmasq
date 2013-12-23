<?php 
require("auth.inc"); 
require("guiconfig.inc"); 
if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"; 
?>

