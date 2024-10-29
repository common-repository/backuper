<?php
# Runs when plugin is activated
function backuper_activate() {

	wp_schedule_event(time(), 'daily', 'backuper_cronevent');
	
	$backuper_options['when'] = '86400';
	$backuper_options['what'] = 'everything';
	$backuper_options['notify'] = 'email';
	
	update_option('backuper_settings', json_encode($backuper_options));
}

# Runs when plugin is deactivated
function backuper_deactivate() {

	wp_clear_scheduled_hook('backuper_cronevent'); // remove cron job
	delete_option('backuper_lastbackuptime');
}

# ----- CRON -----#

	# The function to run upon cron events
	function backuper_runcron() {
		$backuper_dobackup = new Backuper_doBackup();
		$backuper_dobackup->start();
	}

	# Add custom cron schedules to filter
	function backuper_cronintervals($schedules) {
		
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __('Once Weekly')
		);
		
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display' => __('Once Monthly')
		);
		
		return $schedules;
	}
	
	# Set cronjob
	function backuper_setcron($when) {
		
		if($when == 43200)
			$whentext = "twicedaily";
		
		elseif($when == 86400)
			$whentext = "daily";
		
		elseif($when == 604800)
			$whentext = "weekly";
		
		elseif($when == 2635200)
			$whentext = "monthly";
	
		else
			$whentext = "daily";
	
		wp_clear_scheduled_hook('backuper_cronevent');
		wp_schedule_event(time(), $whentext, 'backuper_cronevent'); // change cron
	}

# Admin error message
function backuper_adminnotice(){
	
	$options = backuper_getoptions();
	
	if(empty($options)) {

		echo '<div class="error" style="margin-top:10px;"><p>Backuper require attention, <a href="?page=backuper_general">click here to fix it</a>.</p></div>';
	}
}

# Last update date
function backuper_backuptime($value = 'last') {

	if($value == 'last') {
	
		$last_backup_time = get_option('backuper_lastbackuptime') + (get_option('gmt_offset') * 3600);

		if(!get_option('backuper_lastbackuptime')) {
		
			echo 'No backups created yet';
		}else{
		
			echo date('F jS, Y, g:i a', $last_backup_time);
			echo ' ('.human_time_diff($last_backup_time, current_time('timestamp') ) . ' ago)';
		}
	}
	
	if($value == 'next') {

		$next_backup_time = wp_next_scheduled('backuper_cronevent') + (get_option('gmt_offset') * 3600);
	
		if(!wp_next_scheduled('backuper_cronevent')) {
		
			echo "No schedule defined";
		}else{
		
			echo date('F jS, Y, g:i a', $next_backup_time);
			echo ' (in '. human_time_diff($next_backup_time, current_time('timestamp') ).')';
		}
	}
}

	
# Get cPanel version
function backuper_cpanelversion() {

	$cpanel_path = "/usr/local/cpanel/version";
	
	if(file_exists($cpanel_path)) {
	
		$readcpanel = file_get_contents($cpanel_path);
		return $readcpanel;
	}else{
	
		return false;
	}
}

# Check if required functions exist when doing database-only backups
function backuper_exec_enabled() {
	
	if(exec('cd .')) {
	
		return true;
	}else{
	
		return false;
	}
}

# Get cURL info
function backuper_curlversion() {

	$v = curl_version();
	return $v['version'];
}

# Checked
function backuper_checked($value1, $value2) {

	if($value1==$value2)
		echo " checked='checked'";
}

# Get options from db
function backuper_getoptions($value=null) {
	
	# Get values from database, or set default values
	$current = get_option('backuper_settings');
	
	# Check if option is empty (not set)
	# Set default values if empty
	if(!$current) {
	
		$result['what'] = 'everything';
		$result['when'] = '86400';
		$result['ftpport'] = '21';
		
		# Convert array to json
		$current = json_encode($result);
		
		update_option('backuper_settings', $current);
	}
	
	# Decode json to array
	$result = json_decode($current);
	
	return $result;
}

# FORMAT RAW SIZE
function formatfilesize($size) {
	$bytes=array( 'B','KB','MB','GB','TB' );
  foreach( $bytes as $val ) {
  		if( $size >= 1024 ) {
      		$size=$size / 1024;
      } else {
          break;
      }
  }
  return round($size,2)." ".$val;
}

# Check if everything is OK
function backuper_problems() {

	# Get details
	$options = backuper_getoptions();
	$backuper_dobackup = new Backuper_doBackup();
	$backuper_ftp = new Backuper_FTP($options->ftpuser, $options->ftppass);
	
	# Check if both fopen or cURL is disabled
	if( !function_exists('curl_init') && !ini_get('allow_url_fopen') ) {
	
		$error[] = "Seems both cURL and 'allow_url_fopen' has been disabled by your web hostig provider, Backuper require at least one of those two enabled.";
	}

	# Check FTP server connection
	if(!$backuper_ftp->connect()) {
	
		$error[] = "Not able to connect to FTP server, please check if your username and password is correct.";
	}
	
	# Check FTP server login
	if(!$backuper_ftp->login()) {
	
		$error[] = "Not able to login to FTP server, please check if your username and password is correct.";
	}
	
	# Check if all required info has been set
	if(!$options->ftphost || !$options->ftpuser || !$options->ftppass || !$options->cpuser || !$options->cppass) {
	
		$error[] = "Missing required details";
	}
	
	# Check if cPanel is installed
	if(!backuper_cpanelversion()) {
	
		$error[] = "cPanel is not installed.";
	}
	
	# Check if cPanel login details are correct, if login details exists
	if($options->cpuser && $options->cppass) {
		
		if(!$backuper_dobackup->check_login()) {
			
			$error[] = "Not able to login to cPanel, please check your login details.";
		}
	}
	
	# Check for errors
	if(isset($error)) {
	
		return $error;
		
	}else{
	
		return false;
	}
}

# Sort rawlist descending order
function sort_rawlist($a, $b) {
	$s1 = preg_split("/ /", $a, 9, PREG_SPLIT_NO_EMPTY);
	$s2 = preg_split("/ /", $b, 9, PREG_SPLIT_NO_EMPTY);
	$d = $s1[6];
	$m = $s1[5];
	$y = $s1[7];
	$t = '00:00';
	if (preg_match('/^\d+:\d+$/',$y) > 0) { // time
		$t = $y;
		$y = date('Y', time());
	}
	$stamp = $d.' '.$m.' '.$y.' '.$t.':00';
	$time1 = strtotime($stamp);
	$d = $s2[6];
	$m = $s2[5];
	$y = $s2[7];
	$t = '00:00';
	if (preg_match('/^\d+:\d+$/',$y) > 0) { // time
		$t = $y;
		$y = date('Y', time());
	}
	$stamp = $d.' '.$m.' '.$y.' '.$t;
	$time2 = strtotime($stamp);
	if ($time1 == $time2) {
		return 0;
	}
	return ($time1 < $time2)?1:-1;
}

# Backuper FTP Class
class Backuper_FTP {

	protected $ftphost;
	protected $ftpuser;
	protected $ftppass;
	protected $connection;

	# Constructor
	function __construct() {
	
		# Get FTP details
		$options = backuper_getoptions();

		$this->ftphost = $options->ftphost;
		$this->ftpport = $options->ftpport;
		$this->ftpuser = $options->ftpuser;
		$this->ftppass = $options->ftppass;
		$this->ftpfolder = $options->ftpfolder;
		
		# Set FTP port to 21 if empty
		if(empty($this->ftpport))
			$this->ftpport = "21"; // default
	}
	
	
	# Connect to server
	function connect() {
		
		# Connect to FTP server
		$this->connection = @ftp_connect($this->ftphost, $this->ftpport);
		return (bool) $this->connection;
	}
	
	# Login to server
	function login() {
		return (bool) @ftp_login($this->connection, $this->ftpuser, $this->ftppass);
	}

	# Get filelist
	function rawlist($path) {
	
		$buff = @ftp_rawlist($this->connection, "/".$path);
		@usort($buff, 'sort_rawlist');
		//print_r($buff);
		return $buff;
	}
	
	# Get timestamp
	function mdtm($file) {
	
		return @ftp_mdtm($this->connection, "/".$file);
	}

	# Check if file is a directory
	function is_ftpdir($file) {
	
		if(empty($this->ftpfolder))
			$filepath = $file;
		else
			$filepath = $this->ftpfolder."/".$file;
	
		return (bool) @is_dir("ftp://".$this->ftpuser.":".$this->ftppass."@".$this->ftphost."/".$filepath);
	}
	
	# Create directory if it does not already exist
	function create_dir($dirName){
	
		$dirs = explode('/', $dirName);
		$dir = '';
		
		foreach ($dirs as $part) {
		
			$dir .= trim($part.'/');
			
			if(!@is_dir("ftp://".$this->ftpuser.":".$this->ftppass."@".$this->ftphost."/".$dir) && strlen($dir)>0)
				@ftp_mkdir($this->connection, $dir);
		}
	}
	
	# Delete file
	function delete_file($file) {
	
		# Create file path
		if(empty($this->ftpfolder))
			$filepath = $file;
		else
			$filepath = $this->ftpfolder."/".$file;
	
		return (bool) @ftp_delete($this->connection, $filepath);
	}
	
	# Close FTP
	function close() {
		@ftp_close($this->connection);
	}
}

# cPanel Class
class Backuper_doBackup {

	protected $host;
	protected $cpuser;
	protected $cppass;
	protected $cpport;
	protected $http_client;
	protected $protocol;
	protected $notify;
	protected $ftpuser;
	protected $ftppass;
	protected $ftpfolder;
	
	function __construct() {
	
		require_once(plugin_dir_path(__FILE__).'xmlapi.php');
	
		# Get array of database options
		$options = backuper_getoptions();
		
		# Check if user want to be notified
		if($options->notify == "email") {
		
			$this->notify = get_option('admin_email');
		}else{
		
			$this->notify = null;
		}
		
		$this->host = "127.0.0.1";
		$this->cpuser = $options->cpuser;
		$this->cppass = $options->cppass;
		$this->cpport = $options->cpport;
		$this->ftphost = $options->ftphost;
		$this->ftpport = $options->ftpport;
		$this->ftpuser = $options->ftpuser;
		$this->ftppass = $options->ftppass;
		$this->ftpfolder = $options->ftpfolder;
		
		# Set FTP port to 21 if empty
		if(empty($this->ftpport))
			$this->ftpport = 21; // default
		
		# Create folder if it does not exist
		$createdir = new Backuper_FTP($config_file->FTP_USER, $config_file->FTP_PASS);
		$createdir->connect();
		$createdir->login();
		$createdir->create_dir($this->ftpfolder);
		$createdir->close();
		
		# Detemine what http client to use
		if(function_exists('curl_init')) {
			$this->http_client = "curl";
			
		}elseif(ini_get('allow_url_fopen')) {
			$this->http_client = "fopen";
			
		}else{
			return false;
		}
		
		# Detemine what protocol and port to use
		if($this->cpport == 2083) {
		
			$this->protocol = "https";
		}else{
		
			$this->cpport = 2082;
			$this->protocol = "http";
		}
	}
	
	public function check_login() {

		$xmlapi = new xmlapi($this->host);
		$xmlapi->password_auth($this->cpuser, $this->cppass);
		$xmlapi->set_http_client($this->http_client); // Default: cURL
		$xmlapi->set_port($this->cpport);
		$xmlapi->set_output('array');
		// $xmlapi->set_debug(1);

		$result = $xmlapi->api1_query($this->cpuser, 'Serverinfo', 'servicestatus');
		if (!$result['data']['result'])
			return false;
		else
			return true;
	}
	
	public function get_port() {

		return $this->cpport;
	}
	
	public function get_protocol() {

		return $this->protocol;
	}
	
	public function get_httpclient() {

		return $this->http_client;
	}
	
	
	function start() {

		# Ping backuper with backup count
		
	
		$xmlapi = new xmlapi($this->host);
		$xmlapi->password_auth($this->cpuser,$this->cppass);
		$xmlapi->set_http_client($this->http_client); // Default: cURL
		$xmlapi->set_port($this->cpport);
		$xmlapi->set_debug(1);

		$api_args = array( 
			'ftp', // destination type
			$this->ftphost, // destination address
			$this->ftpuser, // ftp user
			$this->ftppass, // ftp password
			$this->notify, // email to notify
			$this->ftpport, // port
			'/'.$this->ftpfolder // remote path to put file
		);
		
		# Update backuper_lastbackuptime with current time
		update_option('backuper_lastbackuptime',time());
		
		return $xmlapi->api1_query($this->cpuser,'Fileman','fullbackup',$api_args);
	}
}
?>