<?php

if(!is_admin())
	die("Backuper: No Access (not an admin)");

# Get array of database options
$options = backuper_getoptions();

# Connect to Server
$backuper_ftp = new Backuper_FTP();
$backuper_ftp->connect();
$backuper_ftp->login();

# Delete file (if any)
if(isset($_GET["delete"]) && !empty($_GET["delete"])) {
	
	# Convert
	$fname = $_GET['delete'];
	
	# Check if user want to delete all files in the folder
	if($fname == "all") {
	
		foreach ($backuper_ftp->rawlist($options->ftpfolder) as $file) {

			if(ereg("([-dl][rwxst-]+).* ([0-9]) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9]) ([0-9]{2}:[0-9]{2}) (.+)", $file, $regs)) {
			
				# Prevent folders and files starting with a dot to be removed
				if((strpos($regs[8], '.') !== false) && ($regs[8][0] !== '.')) {

					$fname = $regs[8];

					# Delete file
					if($backuper_ftp->delete_file($fname)) {
					
						$success[] = "File deleted ($fname)";
					}else{
					
						$error[] = "Not able to delete file ($fname)";
					}
				}
			}
		}
	}else{
	
		# Delete file
		if($backuper_ftp->delete_file($fname)) {
		
			$success[] = "File deleted";
		}else{
		
			$error[] = "Not able to delete file";
		}
	}
}
?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Backuper - My Backups</h2>
	<div id="poststuff">

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
			<h3 class="hndle"><span><?php _e('List of Backups', 'backuper'); ?></span></h3>
			<div class="inside">
			
			<?php

			if(!$options->ftphost || !$options->ftpuser || !$options->ftppass) {

				echo "<p>Missing FTP login details</p>";

			}else{
			?>
				<p><?php _e('Browse your old backups.', 'backuper'); ?></p>
				
				<table class="backuper-table1" summary="Website Backups by Backuper.org">
					<thead>
						<tr>
							<th scope="col">Date</th>
							<th scope="col">Filesize</th>
							<th scope="col">Name</th>
							<th scope="col">Actions</th>
						</tr>
					</thead>
					<tbody>
					
					<?php
					# How many files to list
					$file_count = 0;
					
					if(isset($_GET['showfiles']) && $_GET['showfiles'] == 'all')
						$file_count_limit = 200;
					else
						$file_count_limit = 20;
					
					# Run files
					foreach ($backuper_ftp->rawlist($options->ftpfolder) as $file) {

						# Count files, stop when max count has been reached
						$file_count++;
						
						if($file_count > $file_count_limit)
							break;
						
						if(ereg("([-dl][rwxst-]+).* ([0-9]) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9]) ([0-9]{2}:[0-9]{2}) (.+)", $file, $regs)) {
						
							# Only list files/folders with a size above 1KB that does not start with a dot
							if($regs[5] > 1000 && $regs[8][0] !== '.') {

								//$type = (int) strpos("-dl", $regs[1]{0});
								//$tmp_array['line'] = $regs[0];
								//$tmp_array['type'] = $type;
								//$tmp_array['rights'] = $regs[1];
								//$tmp_array['number'] = $regs[2];
								//$tmp_array['user'] = $regs[3];
								//$tmp_array['group'] = $regs[4];
								$tmp_array['size'] = $regs[5];
								//$tmp_array['date'] = date("F jS, Y", strtotime($regs[6]) + (get_option('gmt_offset') * 3600));
								//$tmp_array['time'] = date("g:i a", strtotime($regs[7]) + (get_option('gmt_offset') * 3600));
								$tmp_array['name'] = $regs[8];
								
								# Get ftp last_modified timestamp
								$lastmod = $backuper_ftp->mdtm($options->ftpfolder ."/". $tmp_array['name']) + (get_option('gmt_offset') * 3600);
								
								# Build download url
								$fileurl = "ftp://".$options->ftpuser.":".$options->ftppass."@".$options->ftphost."/".$options->ftpfolder ."/". $tmp_array['name'];
								
								?>					
								<tr>
									<td><?php echo date("F jS, Y g:i a", $lastmod); ?></td>
									<td><?php echo formatfilesize($tmp_array['size']); ?></td>
									<td><?php echo $tmp_array['name']; ?></a></td>
									<td><a href="<?php echo $fileurl; ?>">Download</a> | <a href="?page=backuper_mybackups&delete=<?php echo $tmp_array['name']; ?>" onclick="return confirm('Are you sure you want to delete this backup? It is not possible to recover deleted backups.');">Delete</a></td>
								</tr>
							<?php
							}
						}
					}
					?>
					</tbody>
				</table>
				<br />
				<a href="?page=backuper_mybackups&delete=all" onclick="return confirm('Are you sure you want to delete all backup? It is not possible to recover deleted backups.');">Delete all backups</a> | Displaying max <strong><?php echo $file_count_limit; ?></strong> files (<a href="?page=backuper_mybackups&showfiles=all">show all</a>)

			<?php
			}
			?>
			
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