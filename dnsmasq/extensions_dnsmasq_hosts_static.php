<?php
/*
extensions_dnsmasq_hosts_static.php
*/
require("auth.inc");
require("guiconfig.inc");
$pgtitle = array(gettext("Extensions"),gettext("DHCP static clients"));
if (FALSE === is_array($config['dnsmasq']['hosts'])) $config['dnsmasq']['hosts']=array();
array_sort_key($config['dnsmasq']['hosts'], "hostname");
$a_hosts = &$config['dnsmasq']['hosts'];
//if ($_POST)  { print_r($_FILES); }
if ($_POST['download'])  {
	$destination_filename = sprintf('dnsmasq_hosts-%s.%s-%s.config',$config['system']['hostname'],$config['system']['domain'],date('YmdHis'));
	$data = serialize($config['dnsmasq']['hosts']);
	header('Content-Type: application/octet-stream');
	header(sprintf('Content-Disposition: attachment; filename=%s',$destination_filename));
	header(sprintf('Content-Length: %s',strlen($data)));
	header('Pragma: hack');
	echo $data;
	config_unlock();
	header('Header: extensions_dnsmasq_hosts_static.php');
	exit;
}
if ($_POST['submit']='restore')  {
	//file_put_contents("/tmp/dnsmasq.conf", $_FILES['conffile']['tmp_name']);
	if(is_uploaded_file($_FILES['conffile']['tmp_name'])) {
		if ( FALSE === is_array($config['dnsmasq']['hosts']) ) $config['dnsmasq']['hosts'] = array();
		$result = array_merge( $config['dnsmasq']['hosts'], unserialize(file_get_contents( $_FILES['conffile']['tmp_name'] )));
		unset($config['dnsmasq']['hosts']);
		$config['dnsmasq']['hosts'] = $result;
		write_config();
		updatenotify_set("dnsmasq", UPDATENOTIFY_MODE_NEW, "restart");
		header('Header: extensions_dnsmasq_hosts_static.php');
	}
}
if (isset($_POST['apply']) ) {

		//dnsmasq_config();
		if (isset ($config['dnsmasq']['enable'])) {
			rc_update_rcconf("dnsmasq", "enable"); 
			rc_restart_service("dnsmasq"); 
		}	else { 	 
			rc_stop_service("dnsmasq"); 
			rc_update_rcconf("dnsmasq", "disable");
		}
		updatenotify_delete("dnsmasq");
	}

menu:
include("fbegin.inc");
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('.popup').click(function (event) {
		event.preventDefault();
		my_hosts = window.open($(this).attr("href"), "popupWindow", "location=0,status=0,scrollbars=0, width=500,height=400");
		my_hosts.moveTo(100, 400);
	});
});


//-->
</script>
<table id="area_navigator" width="100%" border="0" cellpadding="0" cellspacing="0"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
				<li class="tabinact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
				<li class="tabact"><a href="extensions_dnsmasq_hosts_static.php"><span>Hosts</span></a></li>
				<li class="tabinact"><a href="extensions_dnsmasq_conf.php"><span>config</span></a></li>
				<li class="tabinact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
				<li class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
		</ul></td></tr>
	<tr><td class="tabcont">
<form action="extensions_dnsmasq_hosts_static.php" method="post" name="iform1" id="iform1" enctype="multipart/form-data">
		<?php if ($input_errors) print_input_errors($input_errors);?>
		<?php if (updatenotify_exists("dnsmasq")) print_config_change_box();?>
<table id="area_data" width="100%" border="0" cellpadding="5" cellspacing="0">
	<tbody><?php html_titleline(gtext('Hosts')); ?>
		<tr>
			<td id="area_data_frame">
								
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				
									<tr>
							<td width="35%" class="listhdrlr"><?=gettext("MAC");?></td>
							<td width="35%" class="listhdrc"><?=gettext("IP");?></td>
							<td width="10%" class="listhdrc"><?=gettext("hostname");?></td>
							<td width="10%" class="listhdrc"><?=gettext("Lease time");?></td>
							<td width="5%" class="listhdrc"></td>
					</tr>
		<?php // this line need for analystic from host
					if (is_array($config['dnsmasq']['hosts'])) {
							array_sort_key($config['dnsmasq']['hosts'], "hostname");
							$p_hosts = &$config['dnsmasq']['hosts'];
							foreach ($p_hosts as $host):?>
					<?php $notificationmode = updatenotify_get_mode("dnsmasq", $host['uuid']);?>		
					<tr>
							<td class="listr"><?=htmlspecialchars ( $host['macaddr']);?></td>
							<td  class="listr"><?=htmlspecialchars($host['ipadress']);?></td>
							<td  class="listr"><?=htmlspecialchars($host['hostname']);?></td>					
							<td  class="listrd">infinity</td>
					<?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
							<td valign="middle" nowrap="nowrap" class="list">
								<a href="extensions_dnsmasq_hosts.php?act=edit&amp;uuid=<?=$host['uuid'];?>" class="popup" ><img src="images/edit.png" title="<?=gettext("Edit host");?>" border="0" alt="<?=gettext("Edit host");?>" /></a>&nbsp;
								<a href="extensions_dnsmasq_hosts.php?act=del&amp;uuid=<?=$host['uuid'];?>" class="popup" onclick="return confirm('<?=gettext("Do you really want to delete this entry?");?>')"><img src="images/delete.png" title="<?=gettext("Delete host");?>" border="0" alt="<?=gettext("Delete host");?>" /></a>
							</td>	
					<?php else:?>									
					</tr>
					<?php endif;?>
					<?php endforeach; } ?>
					<tr>
							<td class="list" colspan="7"></td>
							<td class="list">
								<a href="extensions_dnsmasq_hosts.php?act=new" class="popup"><img src="images/add.png" title="<?=gettext("Add host");?>" border="0" alt="<?=gettext("Add host");?>" /></a>
							</td>
					</tr>
					
					</table></td></tr>
					 
					<tr>
						<td><div id="submit">
							<input name="download" type="submit" class="formbtn"  value="<?=gettext("Download host configuration");?>" />
						</div></td>
					</tr>
					<tr>
						<td><div id="submitfile">
						<strong><font color="red"><?='Select hosts configuration file:';?></strong></font>&nbsp;<input name="conffile" type="file" class="formfld" id="conffile" size="40"/>
						</div>
						<div id="submitrestore">
				<?php 	echo html_button('restore',gtext('Restore Configuration'),'restore'); ?>
						</div>
						</td>
					</tr>
				</table>
			</td>

	</tr>
	</tbody>
	</table>	
<?php include("formend.inc");?>
</form>	
<?php include ("fend.inc"); ?>