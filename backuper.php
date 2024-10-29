<?php

/*
Plugin Name: Backuper
Description: Backup your Wordpress site with this rock solid plugin. The first plugin to offer complete cPanel backups.
Author: Backuper
Version: 2.03
Author URI: http://www.backuper.org/
Plugin URI: http://wordpress.org/extend/plugins/backuper/
*/
	
# Get the required functions
require_once("functions.php");

# Post admin warning/notice if the Backup require attention
add_action('admin_notices', 'backuper_adminnotice');

# Activate / De-activate
register_activation_hook(__FILE__, 'backuper_activate'); // Function to run when plugin is activated
register_deactivation_hook(__FILE__, 'backuper_deactivate'); // Function to run when plugin is de-activated

# Cron
add_action('backuper_cronevent', 'backuper_runcron');

# Cron extra intervals
add_filter('cron_schedules', 'backuper_cronintervals'); 

# FUnctions include content pages
function backuper_content_general() {
	include('content_general.php');  
}

function backuper_content_sysinfo() {
	include('content_sysinfo.php');   
}

function backuper_content_mybackups() {
	include('content_mybackups.php');   
}

# Function to specific options button details
function backuper_admin_actions() {

	add_menu_page("Backuper", "Backuper", 1, "backuper_general", "backuper_content_general");
	
	add_submenu_page('backuper_general', 'Backuper', 'General Options', 1, "backuper_general", "backuper_content_general");
	add_submenu_page('backuper_general', 'Backuper', 'My Backups', 1, "backuper_mybackups", "backuper_content_mybackups");
	add_submenu_page('backuper_general', 'Backuper', 'System Details', 1, "backuper_systeminfo", "backuper_content_sysinfo");
}  

wp_register_style('styles', plugins_url('styles.css', __FILE__));
wp_enqueue_style( 'styles');
wp_enqueue_script('postbox');

# Create the options button :)
add_action('admin_menu', 'backuper_admin_actions');
?>