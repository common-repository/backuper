=== Backuper: WordPress Backup Plugin ===
Contributors: sitedevs
Donate link: http://www.backuper.org/
Tags: backup, database backup, complete backup, backuper, wordpress backup
Requires at least: 3.3.2
Tested up to: 3.3.2
Stable tag: 2.03
License: GPL2

Backuper is a small, easy and simple plugin to automatically backup your WordPress site. The only plugin that does an complete cPanel backup of your hosting account. Require cPanel.

== Description ==

This wordpress backup plugin (aka Backuper) allows you to backup your entire web hosting account using cPanel and the built-in backup feature.

Some users will not be able to use this plugin (if your not using cPanel), we are very sorry about that but we have not been able to find a stable method to backup websites without cPanel.

Full list of features:

* Simple installation and setup, takes no more than 2 minutes from start to end.
* Choose between 'Twice per day', 'Daily', 'Weekly', 'Monthly' or Instant backups.
* Backuper does a complete backup of your cPanel account, which include
	* Backup of all your databases.
	* Backup all your files (everything).
	* Backup all your email accounts.
	* Backup all your filters and forwarders.
	* Backup all your Cron Jobs.
	* Backup all your statistics.
	* Backup all your Preferences.
	* And a lot more ...
* The backup file will be compressed and send to an FTP account of your choice.
* Also support "Instant Backup" which instantly do a new backup.

== Installation ==

This section describes how to install the plugin and get it working.


1. Upload the 'Backuper' folder to the '/wp-content/plugins/' directory

1. Activate the plugin through the 'Plugins' menu in WordPress

1. Click the 'Backuper' button in the end of the left menu.

1. Type your FTP login details.

1. Type your cPanel login details.

No one will know your username or password.

Alternative, install the plugin directly from your Wordpress Blog by searching for the plugin "Backuper".

== Frequently Asked Questions ==

= Why only cPanel? =
We would love to release a backup plugin that fits everybody, no matter if your using cPanel or something else. However, we have not been able to create a stable solution, too many problems and issues. By only supporting cPanel we have been able to create an extremely stable and userfriendly plugin.

= How often should I backup my blog? =
This depends on a lot of different things, mostly how popular your website is and how often you update it with new content.
We recommend a daily backup (entire site).

= What happens with my backups? =
Your backups will be transfered to an external FTP account of your choise, you will be able to download backups directly the Backup plugin.
Examples of free ftp hosting services can be found within the plugin.

= How do I download a backup? =
You can download the backups from the plugin or connect directly to the FTP server.

= How do I restore a backup? =
Backuper uses the built-in cPanel backup feature. It's the same backup you receive when doing a backup manually from within cPanel.

You need to contact your web hosting company to restore your account. Just give them the latest backup file and they will be happy to help you.
Alternative; Decompress the backup file (use [WinZip](http://bit.ly/KvzZxc "Winzip") or similar) and find the files you need manually. Your website files can be found in the 'Home' directory, which needs to be decompressed as well.

Feel free to contact us if you need help restoring a website.

Please visit our Support Page for more FAQ's:
http://www.backuper.org/help-faq


== Screenshots ==

1. Settings page


2. Settings page


3. Download page



== Changelog ==

= 2.03 =
Set cPanel Port.
Details saved to the database instead of config file.
Updating the plugin will not cause settings reset anymore.

= 2.02 =
Change of localhost

= 2.01 =
Minor bug fix (error in a function)

= 2.0 =
Backuper has been re-launched with 100% new coding to provide the most user-friendly and stable backup plugin to date.

= 1.01 =
Temporary fix to prevent system crash when trying to backup a big website.
= 1.0 =
Start :)

== Upgrade Notice ==

= 1.0 =
Start, no upgrade available.
