<?php

//Title
define('_title_settings','Konfiguration');
define('_title_gameserver','Gameserver');
define('_title_dasboard','Dashboard');
define('_title_rootserver','Rootserver');
define('_title_templates','Vorlagen');
define('_title_usettings','Account Einstellungen');
define('_title_users','Benutzer');
define('_title_events','Events');
define('_title_addons','Addons');
define('_title_bans','Bans');

//Sidebar
define('_sidebar_settings','Konfig');
define('_sidebar_gameserver','Gameserver');
define('_sidebar_dasboard','Dashboard');
define('_sidebar_rootserver','Rootserver');
define('_sidebar_templates','Vorlagen');
define('_sidebar_usettings','Einstellungen');
define('_sidebar_users','Benutzer');
define('_sidebar_events','Events');
define('_sidebar_addons','Addons');
define('_sidebar_bans','Bans');

//Dropdown
define('_dropdown_logout','Ausloggen');
define('_dropdown_settings','Einstelungen');

define('_button_save','Speichern');
define('_button_edit','Editieren');
define('_button_manage','Verwalten');
define('_table_action','Aktion');
define('_message_addon_error','Ungültiges Addon');
define('_message_template_error','Ungültiges Template');

//addons
define('_addons_path','Path');
define('_addons_url','URL');
define('_addons_folder','Ordner');
define('_addons_message_deleted','Das Addon wurde gelöscht.');
define('_addons_message_updated','Das Addon wurde aktualisiert.');
define('_addons_message_error_game','Das Game enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_addons_message_error_name','Der Name enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_addon_message_added','Das Addon wurde angelegt.');
define('_addons_game','Spiel');
define('_addons_name','Name');

//Bans
define('_bans_message','Bei 3 Versuchen ist die IP gebannt bis einer der 3 Einträge ausläuft nach 30 Minuten.');
define('_bans_date','Datum');
define('_bans_banned','Gebannt bis');
define('_bans_message_removed','Der Ban wurde entfernt.');

//Dedicated
define('_dedicated_installed','Installiert');
define('_dedicated_template_created','Das Template wird erstellt, das kann etwas dauern :)');
define('_dedicated_image_created','Das Image wird erstellt, das kann etwas dauern :)');
define('_dedicated_file_error','Nur .tar oder .zip');
define('_dedicated_message_gameserver_exists','Es exestieren noch Installierte Gameserver mit diesen Spiel.');
define('_dedicated_message_installation_running','Installation des Templates läuft noch.');
define('_dedicated_message_template_deleted','Das Template wurde auf dem Rootserver gelöscht.');
define('_dedicated_message_info','Die Installation kann ca. 5-30 Minuten dauern, je nach Bandbreite und Downloadgröße, wenn die SteamCMD abstürtzt, wird der Updatevorgang neu gestartet.');
define('_dedicated_install','Installieren');
define('_dedicated_remove','Deinstallieren');
define('_dedicated_message_ip_exists','Die IP exestiert bereits.');
define('_dedicated_message_port_exists','Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_dedicated_message_updated','Der Rootserver wurde aktualisiert.');
define('_dedicated_message_template_installed','Templates noch installiert.');
define('_dedicated_deleted','Rootserver gelöscht.');
define('_dedicated_message_exists','Exestiert bereits');
define('_dedicated_message_name_invalid','Der Name enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_dedicated_message_username_invalid','Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_dedicated_message_root_invalid','Der Root Benutzer enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_dedicated_message_port_invalid','Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_dedicated_message_added','Der Rootserver wurde angelegt.');
define('_dedicated_message_info_abort','Die Installation kann 1-2 Minuten dauern, abbruch des Ladevorgangs führt zur fehlerhafter installation.');
define('_dedicated_user','Benutzer');

//Dashboard
define('_dashboard_events','Alle Events');

//Events
define('_events_date','Datum');
define('_events_message','Nachricht');

//Users
define('_users_message_deleted','Benutzer wurde gelöscht.');
define('_users_message_yourself','Du kannst dich nicht selber Löschen.');
define('_users_message_gameserver','Der Benutzer besitzt noch Gameserver.');
define('_users_password_notequal','Passwort ungleich');
define('_users_password_toshort','Passwort zu kurz.');
define('_users_exists','User exestiert.');
define('_users_email_exists','E-Mail exestiert');
define('_users_name_toshort','Name zu kurz.');
define('_users_name_invalid_letters','Der Username enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9 sind Erlaubt)');
define('_users_email_invalid','Die E-Mail ist nicht g&uuml;ltig');
define('_users_email_toshort','E-Mail zu kurz.');
define('_users_user_updated','Benutzer aktualisiert.');
define('_users_user_created','Benutzer wurde erstellt.');
define('_users_name','Name');
define('_users_email','E-Mail');
define('_users_rank','Rank');


//Settings
define('_settings_msgbox','Der Cronjob wurde am');
define('_settings_msgbox_executed','ausgeführt.');
define('_settings_maintance','Wartungsmodus');
define('_settings_cleanup','Gameserver Log Cleanup');
define('_settings_restart','Gameserver Neustart bei Crash');
define('_settings_restart_cpu_usage','Gameserver Neustarten bei mehr als 25% CPU Last (wenn leer)');
define('_settings_message_cpu_load','Nachricht im Chat, bei mehr als 90% CPU Last');
define('_settings_message_ssl_true','Es wird SSL benutzt, um die Verbindung zu verschlüsseln. Um die Sicherheit der Verbindung zu Testen: <a href="https://www.ssllabs.com/ssltest/">ssllabs.com</a>');
define('_settings_message_ssl_false','Es wird kein SSL benutzt, um die Verbindung zu verschlüsseln.');

//USettings
define('_usettings_general','Allgemein');
define('_usettings_password','Passwort');
define('_usettings_oldpwd','Altes Passwort');
define('_usettings_newpwd','Neues Passwort');
define('_usettings_repeatpwd','Wiederholen');

//Gameserver
define('_gameserver_user','Benutzer');
define('_gameserver_game','Spiel');
define('_gameserver_slots','Slots');
define('_gameserver_restart','Neustart');
define('_gameserver_map','Map');
define('_gameserver_ftp_login','FTP Login');
define('_gameserver_ftp_password','FTP Passwort');
define('_gameserver_parameter','Parameter');

define('_gameserver_button_restart','(Re)start');
define('_gameserver_button_stop','Stop');
define('_gameserver_button_reinstall','Reinstall');
define('_gameserver_button_update','Update');
define('_gameserver_button_console','Console');
define('_gameserver_button_addons','Addons');
define('_gameserver_button_settings','Einstellungen');
define('_gameserver_reinstalled','Der Gameserver wird neuinstalliert.');
define('_gameserver_updated','_Der Gameserver wird aktualisiert.');
define('_gameserver_started','Der Gamesever wurde gestartet.');
define('_gameserver_stopped','Der Gameserver wurde angehalten.');
define('_gameserver_deleted','Der Gameserver wurde gelöscht.');
define('_gameserver_slots_invalid','Slots enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_gameserver_port_invalid','Der Port enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_gameserver_port_in_use','Port belegt');
define('_gameserver_mass_error','Die Anzahl enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_gameserver_dedicated_invalid','Dedicated enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_gameserver_user_invalid','User enth&auml;lt ung&uuml;ltige Zeichen (0-9 sind Erlaubt)');
define('_gameserver_game_invalid','Das Spiel enth&auml;lt ung&uuml;ltige Zeichen (a-z,A-Z,0-9._- sind Erlaubt)');
define('_gameserver_dedi_id_invalid','Ungültige Dedicated ID');
define('_gameserver_installed','Der Gameserver wird installiert, das kann etwas dauern.');
define('_gameserver_mass','Anzahl');


























 ?>
