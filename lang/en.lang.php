<?php

//Title
define('_title_settings','Settings');
define('_title_gameserver','Gameserver');
define('_title_dasboard','Dashboard');
define('_title_rootserver','Dedicated');
define('_title_templates','Templates');
define('_title_usettings','Settings');
define('_title_users','Users');
define('_title_events','Events');
define('_title_addons','Addons');
define('_title_bans','Bans');

//Sidebar
define('_sidebar_settings','Settings');
define('_sidebar_gameserver','Gameserver');
define('_sidebar_dasboard','Dashboard');
define('_sidebar_rootserver','Dedicated');
define('_sidebar_templates','Templates');
define('_sidebar_usettings','Settings');
define('_sidebar_users','Users');
define('_sidebar_events','Events');
define('_sidebar_addons','Addons');
define('_sidebar_bans','Bans');

//Dropdown
define('_dropdown_logout','Logout');
define('_dropdown_settings','Settings');

define('_button_save','Save');
define('_button_edit','Edit');
define('_button_manage','Manage');
define('_table_action','Action');
define('_message_addon_error','Invalid Addon');
define('_message_template_error','Invalid Template');

//addons
define('_addons_path','Path');
define('_addons_url','URL');
define('_addons_folder','Folder');
define('_addons_message_deleted','The Addon was deleted.');
define('_addons_message_updated','The Addon was updated.');
define('_addons_message_error_game','The Game contains invalid letters (a-z,A-Z,0-9 are allowed)');
define('_addons_message_error_name','The Name contains invalid letters (a-z,A-Z,0-9 are allowed)');
define('_addon_message_added','The Addon was added.');
define('_addons_game','Game');
define('_addons_name','Name');

//Bans
define('_bans_message','After 3 attempts is the User banned until one of the bans gets lifted after 30 minutes.');
define('_bans_date','Date');
define('_bans_banned','Banned until');
define('_bans_message_removed','Ban haz been removed.');

//Dedicated
define('_dedicated_installed','Installed');
define('_dedicated_template_created','The Template will be created, this may take some time :)');
define('_dedicated_image_created','The Image will be created, this may take some time n :)');
define('_dedicated_file_error','Only .tar or .zip');
define('_dedicated_message_gameserver_exists','There do still exist Gameservers with this Game.');
define('_dedicated_message_installation_running','Installation of this Template is still running.');
define('_dedicated_message_template_deleted','The Template was deleted from the Dedicated.');
define('_dedicated_message_info','The Installation takes about 5-30 minutes, depending on the Downloadsize and Bandwidth. If the SteamCMD crashes, it gets restarted.');
define('_dedicated_install','Install');
define('_dedicated_remove','Uninstall');
define('_dedicated_message_ip_exists','Die IP exists already.');
define('_dedicated_message_port_exists','The Port contains invalid letters (0-9 are allowed)');
define('_dedicated_message_updated','The Dedicated was updated.');
define('_dedicated_message_template_installed','Template still installed.');
define('_dedicated_deleted','Dedicated deleted.');
define('_dedicated_message_exists','Exists already');
define('_dedicated_message_name_invalid','The Name contains invalid letters (a-z,A-Z,0-9 are allowed)');
define('_dedicated_message_username_invalid','The Username contains invalid letters (a-z,A-Z,0-9 are allowed)');
define('_dedicated_message_root_invalid','The Root user contains invalid letters (a-z,A-Z,0-9 sare allowed)');
define('_dedicated_message_port_invalid','The Port contains invalid letters (0-9 are allowed)');
define('_dedicated_message_added','The Dedicated was added.');
define('_dedicated_message_info_abort','The Installation takes about 1-2 minutes, if it gets interrupted the Installation will be corrupt.');
define('_dedicated_user','User');

//Dashboard
define('_dashboard_events','All Events');

//Events
define('_events_date','Date');
define('_events_message','Message');

//Users
define('_users_message_deleted','User deleted.');
define('_users_message_yourself','You cant delete yourself.');
define('_users_message_gameserver','This User has still Gameservers on his account.');
define('_users_password_notequal','Password not equal');
define('_users_password_toshort','Password to short.');
define('_users_exists','User exists.');
define('_users_email_exists','Email exists');
define('_users_name_toshort','Name to short.');
define('_users_name_invalid_letters','The Username contains invalid Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_users_email_invalid','The Email is not valid');
define('_users_email_toshort','Email to short.');
define('_users_user_updated','User updated.');
define('_users_user_created','User created.');
define('_users_name','Name');
define('_users_email','Email');
define('_users_rank','Rank');

//Settings
define('_settings_msgbox','Last run');
define('_settings_msgbox_executed','');
define('_settings_maintance','Maintance');
define('_settings_cleanup','Gameserver Log Cleanup');
define('_settings_restart','Gameserver Restart if Crashed');
define('_settings_restart_cpu_usage','Restart the gameserver if it hits more as 25% CPU load (if empty)');
define('_settings_message_cpu_load','Print a Message into the Chat, if the CPU load is higher then 90%');
define('_settings_message_ssl_true','Your Webserver is using SSL to encrypt the connection. To check if its really secure: <a href="https://www.ssllabs.com/ssltest/">ssllabs.com</a>');
define('_settings_message_ssl_false','Your Webserver is not using SSL to encrypt your connection.');


//USettings
define('_usettings_general','General');
define('_usettings_password','Password');
define('_usettings_oldpwd','Old password');
define('_usettings_newpwd','New password');
define('_usettings_repeatpwd','Repeat');

//Gameserver
define('_gameserver_user','User');
define('_gameserver_game','Game');
define('_gameserver_slots','Slots');
define('_gameserver_restart','Restart');
define('_gameserver_map','Map');
define('_gameserver_ftp_login','FTP Login');
define('_gameserver_ftp_password','FTP Password');
define('_gameserver_parameter','Parameters');

define('_gameserver_button_restart','(Re)start');
define('_gameserver_button_stop','Stop');
define('_gameserver_button_reinstall','Reinstall');
define('_gameserver_button_update','Update');
define('_gameserver_button_console','Console');
define('_gameserver_button_addons','Addons');
define('_gameserver_button_settings','Settings');
define('_gameserver_reinstalled','The Gameserver will be reinstalled.');
define('_gameserver_updated','The Gameserver will be updated.');
define('_gameserver_started','The Gamesever got restarted.');
define('_gameserver_stopped','The Gameserver got stopped.');
define('_gameserver_deleted','The Gameserver got deleted.');
define('_gameserver_slots_invalid','Slots contains invalid letters (0-9 are allowed)');
define('_gameserver_port_invalid','Der Port contains invalid letters (0-9 are allowed)');
define('_gameserver_port_in_use','Port used');
define('_gameserver_mass_error','The Ammount contains invalid letters (0-9are allowed)');
define('_gameserver_dedicated_invalid','Dedicated contains invalid letters (0-9 are allowed)');
define('_gameserver_user_invalid','THe User contains invalid letters (0-9 are allowed)');
define('_gameserver_game_invalid','The Game contains invalid letters (a-z,A-Z,0-9._- are allowed)');
define('_gameserver_dedi_id_invalid','Invalid Dedicated ID');
define('_gameserver_installed','The Gameserver will be installed, that can take some time.');
define('_gameserver_mass','Ammount');


































 ?>
