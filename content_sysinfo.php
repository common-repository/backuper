<?php

if(!is_admin())
	die("Backuper: No Access (not an admin)");

# Connect to Server
$backuper_ftp = new Backuper_FTP($config_file->FTP_USER, $config_file->FTP_PASS);

# Get array of database options
$options = backuper_getoptions();

# cPanel class
$backuper_dobackup = new Backuper_doBackup();
?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Backuper - System Details</h2>
	<div id="poststuff">

		<div id="pageloading" style="margin-top:10px;">
			<img id="loading-image" src="<?php echo plugin_dir_url(__FILE__).'/loader.gif'; ?>" alt="Loading..." />
			<p><?php _e('Loading page, please wait', 'backuper'); ?> ...</p>
		</div>
	
		<?php	
		if(!backuper_cpanelversion()) { ?>
			<div class="error" style="margin-top:10px;">
				<p><? _e("cPanel is required to use this backup plugin, we advise you to change web hosting provide or use <a href='#'>this plugin</a> instead.", 'backuper'); ?><br /><a target="_blank" href=""><?php _e("See a list of supported web hosting providers here.", 'backuper'); ?></a></p>
			</div>
		<?php
		}

		if( !function_exists('curl_init') && !ini_get('allow_url_fopen') ) { ?>
			<div class="error" style="margin-top:10px;">
				<p><?php _e("Both cURL and 'allow_url_fopen' has been disabled by your hosting company (shame on them), cURL or allow_url_fopen is required to use Backup.", 'backuper'); ?></a></p>
			</div>
		<?php
		}
		?>
		
		<div class="postbox" style="margin-top:20px;">
			<h3 class="hndle"><span><?php _e('Server Configuration', 'backuper'); ?></span></h3>
			<div class="inside">
				<p>
					cPanel version: <?php
						if(!backuper_cpanelversion())
							echo "<font color='white' style='background:red; padding:0px 5px;'>cPanel is not installed! The Backuper plugin will not work.</font>";
						else
							echo backuper_cpanelversion();
					?><br />
					
					PHP version: <?php echo phpversion(); ?><br />
					
					cURL: <?php if(function_exists('curl_init')) echo 'Enabled ('.backuper_curlversion().')'; else echo '<font color="red">Disabled</font>'; ?><br />
					
					allow_url_fopen: <?php if(ini_get('allow_url_fopen')) echo 'Enabled'; else echo '<font color="red">Disabled</font>'; ?><br />
				</p>
			</div>
		</div>
		
		<div class="postbox" style="margin-top:20px;">
			<h3 class="hndle"><span><?php _e('Other Details', 'backuper'); ?></span></h3>
			<div class="inside">
				<p>
					<strong>Site Details</strong><br />
					URL: <?php echo site_url(); ?><br />
					
					<br />
					<strong>Test FTP Server</strong><br />
					Host: <?php echo $options->ftphost; ?><br />
					User: <?php echo $options->ftpuser; ?><br />
					Port: <?php echo $options->ftpport; ?><br />
					<br />
					Connect to server: <?php if(!$backuper_ftp->connect()) echo "Not able to connect"; else echo "Connected"; ?><br />
					Login to server: <?php if(!$backuper_ftp->login()) echo "Not able to login"; else echo "Logged in"; ?><br />
					
					<br />
					<strong>Test cPanel Server</strong><br />
					Using: <?php echo $backuper_dobackup->get_httpclient(); ?><br />
					Port: <?php echo $backuper_dobackup->get_port(); ?><br />
					Protocol: <?php echo $backuper_dobackup->get_protocol(); ?><br />
					Status: <?php
					# Check if cPanel login details are correct, if login details exists
					if($options->cpuser && $options->cppass) {
						
						if(!$backuper_dobackup->check_login()) {
							
							echo "Not able to login.";
						}else{
						
							echo "Logged in.";
						}
					}else{
					
						echo "Missing cPanel login details.";
					}
					?>
				</p>
			</div>
		</div>
	</div>
</div>

<script language="javascript" type="text/javascript">
  document.getElementById("pageloading").style.display = "none";
</script>

<?php
# Close FTP
$backuper_ftp->close();
?>