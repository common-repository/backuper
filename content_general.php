<?php

if(!is_admin())
	die("Backuper: No Access (not an admin)");

# Test if form has been submitted
if(isset($_POST['backuper_hidden']) && $_POST['backuper_hidden'] == "y") {
	
	# Options
	if(isset($_POST['backuper_options']) && !empty($_POST['backuper_options'])) {
	
		foreach($_POST['backuper_options'] as $key=>$value) {
		
			$value = trim($value);
			$value = str_replace(" ", "", $value);
			$backuper_options[$key] = trim($value);
		}
	}
	
	# Remove invalid characters from ftp folder name
	$backuper_options['ftpfolder'] = str_replace(" ", "", $backuper_options['ftpfolder']); # Remove blank spaces
	$backuper_options['ftpfolder'] = str_replace("'", "", $backuper_options['ftpfolder']); # Remove '
	
	# Remove first slash (if any) from FTP Folder path
	if(substr($backuper_options['ftpfolder'], 0, 1) == '/') {
		$backuper_options['ftpfolder'] = substr($backuper_options['ftpfolder'], 1);
	}
	
	# Remove last slash (if any) from FTP folder path
	if(substr($backuper_options['ftpfolder'], -1) == '/') {
		$backuper_options['ftpfolder'] = rtrim($backuper_options['ftpfolder'], '/');
	}
	
	# Port has to be a certain value, check if it is
	if(!in_array($backuper_options['cpport'], array(2082,2083))) {
		$error[] = "cPanel port # is not valid.";
	}
	
	# Error checking
	
	# Check fields
	if(empty($backuper_options['ftphost'])) {
		$error[] = "Missing FTP URL/Host, please correct.";
	}
	
	# Check fields
	if(empty($backuper_options['ftpuser'])) {
		$error[] = "Missing FTP username, please correct.";
	}
	
	if(empty($backuper_options['ftppass'])) {
		$error[] = "Missing FTP password, please correct.";
	}
	
	if(empty($backuper_options['cpuser'])) {
		$error[] = "Missing cPanel username, please correct.";
	}
	
	if(empty($backuper_options['cppass'])) {
		$error[] = "Missing cPanel password, please correct.";
	}
	
	if(empty($backuper_options['when'])) {
		$error[] = "No backup schedule has been set.";
	}
	
	if(empty($error)) {
		
		# Update options in database
		update_option('backuper_settings', json_encode($backuper_options));
		
		# Set cron
		backuper_setcron($backuper_options['when']);
		
		# Status message
		$success[] = "Changes has been saved.";

	}
	
}

# Create array of problems (if any)
//$problems = backuper_problems();

# Check if user want to check for problems
if(isset($_GET['action']) && $_GET['action'] == 'problems') {

	
}
# Check if user want to do a backup now, but only if no errors	
if(isset($_GET['action']) && $_GET['action'] == 'backupnow') {

	# Check for problems
	$problems = backuper_problems();
	
	if(empty($problems)) {
	
		# Start new backup
		$backuper_dobackup = new Backuper_doBackup();
		$result = $backuper_dobackup->start();
		
		if(!$result) {
			$error[] = $result;
			
		}else{
			$success[] = "A backup is being processed right now - the time it takes to finish depends on the size of your website.<br />You will receive an email when the backup has been completed, if you selected that option below.";
			
		}
	}
}

# Post error if both cURL and fopen has been disabled
//if(!backuper_iniCheck('allow_url_fopen') && function_exists('curl_init')) {
if( !function_exists('curl_init') && !ini_get('allow_url_fopen') ) {

	$error[] = "Both cURL and 'allow_url_fopen' has been disabled by your hosting company, you will not be able to use Backuper.";
}

# Check if cPanel is installed
if(!backuper_cpanelversion()) {

	$error[] = "Seems your web hosting provider does not have cPanel (shame on them). Backuper require cPanel installed.";
}

# Get array of database options
$options = backuper_getoptions();

?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Backuper - Simple WordPress Backups</h2>
	<div id="poststuff" style="margin-top:10px;">
	
		<?php
		# Post success message
		if(isset($success)) {
			# Post errors, one at a time
			foreach($success as $k=>$v)
				echo '<div class="updated" style="margin-top:10px;"><p>'.$v.'</p></div>';
		}

		# Post error messages
		if(isset($error)) {

			# Post errors, one at a time
			foreach($error as $k=>$v)
				echo '<div class="error" style="margin-top:10px;"><p>'.$v.'</p></div>';
		}
		?>
	
		<div id="pageloading">
			<img id="loading-image" src="<?php echo plugin_dir_url(__FILE__).'/loader.gif'; ?>" alt="Loading..." />
			<p><?php _e('Loading, please wait...', 'backuper'); ?> ...</p>
		</div>
		
		<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			
			<input type="hidden" name="backuper_hidden" value="y">
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('Status', 'backuper'); ?></span></h3>
				<div class="inside">
					<p><?php
					
						# Check if user want to check for problems
						if(isset($_GET['action']) && $_GET['action'] == 'problems') {

							$problems = backuper_problems();
							
							# Check for errors
							if(empty($problems)) {
								echo '<font color="green">No problems, backups are being created according to the defined schedule.</font>';
								
							}else{
								
								echo '<font color="red">';
								
								foreach($problems as $k=>$v)
									echo $v.'<br />';
									
								echo '</font>';
							
							}
						}else{
						
							# Ask user if he want to check for problems
							echo '<a href="?page=backuper_general&action=problems">Check for problems</a>';
						}
					?></p>
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('Information', 'backuper'); ?></span></h3>
				<div class="inside">
					<p>Current time: <?php echo date('F jS, Y, g:i a', current_time('timestamp')); ?> (<a href="<?php echo get_admin_url().'options-general.php'; ?>">change timezone</a>)</p>
					<p>Last backup date: <?php backuper_backuptime('last'); ?></p>
					<p>Next scheduled backup date: <?php backuper_backuptime('next'); ?></p>
					
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('FTP Details', 'backuper'); ?></span></h3>
				<div class="inside">
					<?php _e("Backuper will save your backup files remotely on an FTP server for better security and less problems.", 'backuper'); ?><br />
					<?php _e("Please type your FTP details below.", 'backuper'); ?><br />
					<a target="_blank" class="buttonPro" style="margin:10px 0px;" href="http://www.loveftp.com"><?php _e("Click here to get an FTP account", 'backuper'); ?></a>
					<br />
					<hr />
					<table class="backuper-table2">
						<col width="150" />
						<col width="700" />
						<tr>
							<td><?php _e("FTP URL/Hostname", 'backuper'); ?>:</td>
							<td><input type="text" name="backuper_options[ftphost]" value="<?php echo $options->ftphost; ?>" size="40" /> &nbsp; Port <input type="text" name="backuper_options[ftpport]" value="<?php if(empty($options->ftpport)) echo "21"; else echo $options->ftpport; ?>" size="2" /> (default: 21)</td>
						</tr>
						<tr>
							<td><?php _e("FTP Username", 'backuper'); ?>:</td>
							<td><input type="text" name="backuper_options[ftpuser]" value="<?php echo $options->ftpuser; ?>" size="40" /></td>
						</tr>
						<tr>
							<td><?php _e("FTP Password", 'backuper'); ?>:</td>
							<td><input type="password" name="backuper_options[ftppass]" value="<?php echo $options->ftppass; ?>" size="40" /></td>
						</tr>
						<tr>
							<td><?php _e("FTP Folder", 'backuper'); ?>:</td>
							<td><input type="text" name="backuper_options[ftpfolder]" value="<?php echo $options->ftpfolder; ?>" size="40" /> (optional, will be created if it does not already exist)</td>
						</tr>
					</table>
					<div class="helpbox"><p>We advise you to create an unique FTP folder for each of your websites, preferable your domain name (ex. 'mydomain.com').</p></div>
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e('cPanel Login Details', 'backuper'); ?></span></h3>
				<div class="inside">
					<?php _e("Please type your cPanel Username and Password below (required).", 'backuper'); ?><br />
					<?php _e("Backuper will do a complete backup of your website using the built-in cPanel backup tool.", 'backuper'); ?><br />
					<br />
					<table class="backuper-table2">
						<col width="150" />
						<col width="600" />
						<tr>
							<td><?php _e("cPanel Username", 'backuper'); ?>:</td>
							<td><input type="text" name="backuper_options[cpuser]" value="<?php echo $options->cpuser; ?>" size="40" /></td>
						</tr>
						<tr>
							<td><?php _e("cPanel Password", 'backuper'); ?>:</td>
							<td><input type="password" name="backuper_options[cppass]" value="<?php echo $options->cppass; ?>" size="40" /></td>
						</tr>
						<tr>
							<td><?php _e("cPanel Port", 'backuper'); ?>:</td>
							<td>
								<select name="backuper_options[cpport]">
									<option value="2082" <?php if($options->cpport == 2082) echo 'selected="selected"'; ?>>2082 (default)</option>
									<option value="2083" <?php if($options->cpport == 2083) echo 'selected="selected"'; ?>>2083 (ssl)</option>
								</select>
							</td>
						</tr>
					</table>
					<div class="helpbox"><p><?php _e("No one will know your username or password, it will only be saved locally.", 'backuper'); ?></p></div>
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e("What do you want to backup?", 'backuper'); ?></span></h3>
				<div class="inside">
					<label><input type='radio' name='backuper_options[what]' value='everything' <?php backuper_checked('everything', $options->what); ?> /> <?php _e("Everything / Full-backup", 'backuper'); ?></label>
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e("How often do you want to backup?", 'backuper'); ?></span></h3>
				<div class="inside">
					<label><input type='radio' name='backuper_options[when]' value='86400' <?php backuper_checked('86400', $options->when); ?> /> <?php _e("One backup per day (recommended)", 'backuper'); ?></label><br /><br />
					<label><input type='radio' name='backuper_options[when]' value='43200' <?php backuper_checked('43200', $options->when); ?> /> <?php _e("Twice per day", 'backuper'); ?></label><br /><br />
					<label><input type='radio' name='backuper_options[when]' value='604800' <?php backuper_checked('604800', $options->when); ?> /> <?php _e("One backup per week", 'backuper'); ?></label><br /><br />
					<label><input type='radio' name='backuper_options[when]' value='2635200' <?php backuper_checked('2635200', $options->when); ?> /> <?php _e("One backup per month", 'backuper'); ?></label><br />
					
					<div class="bluebox">
						<p><a href="?page=backuper_general&action=backupnow">Create a new backup now</a> - this will not change your backup schedule.</p>
					</div>
				</div>
			</div>
			
			<div class="postbox">
				<h3 class="hndle"><span><?php _e("Notifications", 'backuper'); ?></span></h3>
				<div class="inside">
					<p>Do you wan't to be notified when a new backup has been completed?</p>
					
					<input type="checkbox" name="backuper_options[notify]" value="email" <?php backuper_checked('email', $options->notify); ?> /> Yes, send me an e-mail.<br />
					
				</div>
			</div>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

		</form>
	</div>
</div>

<script language="javascript" type="text/javascript">
  document.getElementById("pageloading").style.display = "none";
</script>