<?php

//Title
define('_title_settings','Instellingen');
define('_title_gameserver','Gameserver');
define('_title_dasboard','Dashboard');
define('_title_rootserver','Dedicated');
define('_title_templates','Templates');
define('_title_usettings','Instellingen');
define('_title_users','Gebruikers');
define('_title_events','Gebeurtenissen');
define('_title_addons','Addons');
define('_title_bans','Bans');
define('_title_backup','Backup');

//Sidebar
define('_sidebar_settings','Instellingen');
define('_sidebar_gameserver','Gameserver');
define('_sidebar_dasboard','Dashboard');
define('_sidebar_rootserver','Dedicated');
define('_sidebar_templates','Templates');
define('_sidebar_usettings','Instellingen');
define('_sidebar_users','Gebruikers');
define('_sidebar_events','Gebeurtenissen');
define('_sidebar_addons','Addons');
define('_sidebar_bans','Bans');
define('_sidebar_backupserver','Backupserver');

//Backupserver
define('_backup_message_added','Backupserver toegevoegd.');
define('_backup_message_removed','Backupserver verwijderd.');

//Templates
define('_templates_rootserver_installed','De Template is nog altijd geïnstalleerd op een Dedicated Server.');
define('_template_rootserver_still_running','Installation of De Template is still running.');
define('_template_deleted','De Template werd verwijderd.');
define('_template_updated','De Template werd geupdated.');
define('_template_exists','Template bestaat reeds.');
define('_templates_internal_error','bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_templates_type_error','Type bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_templates_typename_error','Type bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_templates_gameq_error','GameQ bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_templates_invalid_type','Ongeldig Type.');
define('_template_added','De Template werd aangemaakt.');
define('_template_name','Naam');
define('_template_internal','Intern');
define('_template_type','Type');
define('_template_type_name','ID/URL');
define('_template_map_path','Map Path');
define('_template_limited','Slechts deels bewerkbaar sinds reeds geïnstalleerd.');

//Dropdown
define('_dropdown_logout','Log uit');
define('_dropdown_settings','Instellingen');

//General
define('_button_save','Opslaan');
define('_button_edit','Bewerk');
define('_button_manage','Manage');
define('_table_action','Actie');
define('_message_addon_error','Ongeldige Addon');
define('_message_template_error','Ongeldige Template');

//addons
define('_addons_path','Path');
define('_addons_url','URL');
define('_addons_folder','Folder');
define('_addons_message_deleted','De Addon werd verwijderd.');
define('_addons_message_updated','De Addon werd geupdated.');
define('_addons_message_error_game','Het Spel bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_addons_message_error_name','De Naam bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_addon_message_added','De Addon werd toegevoegd.');
define('_addons_game','Spel');
define('_addons_name','Naam');
define('_addons_message_error_url','Invalid URL');

//Bans
define('_bans_message','Na 3 pogingen wordt de gebruiker gebannen totdat deze opgeheven wordt na 30 minuten.');
define('_bans_date','Datum');
define('_bans_banned','Gebannen tot');
define('_bans_message_removed','Ban werd verwijderd.');

//Dedicated
define('_dedicated_installed','Geïnstalleerd');
define('_dedicated_template_created','De Template zal worden aangemaakt, dit kan even duren :)');
define('_dedicated_image_created','De Image zal worden aangemaakt, dit kan even duren :)');
define('_dedicated_file_error','Enkel .tar of .zip');
define('_dedicated_message_gameserver_exists','Er bestaan nog altijd Gameservers van dit spel.');
define('_dedicated_message_installation_running','Installatie van deze Template is nog altijd bezig.');
define('_dedicated_message_template_deleted','De Template werd verwijderd van de Dedicated server.');
define('_dedicated_message_info','De installatie duurt ongeveer 5 tot 30 minuten, afhankelijk van de grootte van het bestand en je internetsnelheid. Als SteamCMD crasht, wordt het automatisch herstart.');
define('_dedicated_install','Installeer');
define('_dedicated_remove','Verwijder');
define('_dedicated_message_ip_exists','Dit IP adres bestaat reeds.');
define('_dedicated_message_port_exists','De Poort bevat ongeldige letters (0-9 zijn toegestaan)');
define('_dedicated_message_updated','De Dedicated Server werd geupdated.');
define('_dedicated_message_template_installed','Template is nog altijd geïnstalleerd.');
define('_dedicated_deleted','Dedicated verwijderd.');
define('_dedicated_message_exists','Bestaat reeds');
define('_dedicated_message_name_invalid','De Naam bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_dedicated_message_username_invalid','De gebruikersnaam bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_dedicated_message_root_invalid','De Root user bevat ongeldige letters (a-z,A-Z,0-9 szijn toegestaan)');
define('_dedicated_message_port_invalid','De Poort bevat ongeldige letters (0-9 zijn toegestaan)');
define('_dedicated_message_added','De Dedicated Server werd toegevoegd.');
define('_dedicated_message_info_abort','De installatie duurt ongeveer 1 tot 2 minuten, als deze onderbroken wordt, zal de installatie corrupte bestanden bevatten.');
define('_dedicated_message_ip_invalid','Ongeldig IP adres');
define('_dedicated_user','Gebruiker');

//Dashboard
define('_dashboard_events','Alle Gebeurtenissen');

//Events
define('_events_date','Datum');
define('_events_message','Bericht');

//Users
define('_users_message_deleted','Gebruiker verwijderd.');
define('_users_message_yourself','Je kan jezelf niet verwijderen.');
define('_users_message_gameserver','Deze gebruiker heeft nog steeds Gameservers op zijn account.');
define('_users_password_notequal','Paswoorden zijn niet gelijk');
define('_users_password_toshort','Paswoord is te kort.');
define('_users_exists','Gebruiker bestaat.');
define('_users_email_exists','Email adres bestaat');
define('_users_name_toshort','Name is te kort.');
define('_users_name_invalid_letters','De gebruikersnaam bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_users_email_invalid','Dit email adres is niet geldig.');
define('_users_email_toshort','Email adres is te kort.');
define('_users_user_updated','Gebruiker updated.');
define('_users_user_created','Gebruiker aangemaakt.');
define('_users_name','naam');
define('_users_email','Email adres');
define('_users_rank','Rang');

//Settings
define('_settings_msgbox','Laatste run');
define('_settings_msgbox_executed','');
define('_settings_maintance','Onderhoud');
define('_settings_cleanup','Gameserver Log Schoonmaak');
define('_settings_restart','Gameserver Herstarten indien deze crasht');
define('_settings_restart_cpu_usage','Herstart de Gameserver als hij meer dan 25% can de CPU load gebruikt (indien leeg)');
define('_settings_message_cpu_load','Toon een bericht in de chat als de CPU load hoger is dan 75%');
define('_settings_message_ssl_true','Je Webserver gebruikt SSL om deze verbinding te beveiligen. Om te controleren als het zeker veilig is: <a href="https://www.ssllabs.com/ssltest/">ssllabs.com</a>');
define('_settings_message_ssl_false','Je Webserver gebruikt geen SSL om je verbinding te beveiligen.');

//USettings
define('_usettings_general','Algemeen');
define('_usettings_password','Paswoord');
define('_usettings_oldpwd','Oud paswoord');
define('_usettings_newpwd','Nieuw paswoord');
define('_usettings_repeatpwd','Herhaal');

//Gameserver
define('_gameserver_user','Gebruiker');
define('_gameserver_game','Spel');
define('_gameserver_slots','Sloten');
define('_gameserver_restart','Herstart');
define('_gameserver_map','Map');
define('_gameserver_ftp_login','FTP Login');
define('_gameserver_ftp_password','FTP Paswoord');
define('_gameserver_parameter','Parameters');
define('_gameserver_button_restart','(Her)start');
define('_gameserver_button_stop','Stop');
define('_gameserver_button_reinstall','Herinstalleer');
define('_gameserver_button_update','Update');
define('_gameserver_button_console','Console');
define('_gameserver_button_addons','Addons');
define('_gameserver_button_settings','Instellingen');
define('_gameserver_reinstalled','De Gameserver zal worden geherinstalleerd.');
define('_gameserver_updated','De Gameserver zal worden upgedated.');
define('_gameserver_started','The Gamesever is herstart.');
define('_gameserver_stopped','De Gameserver is gestopt.');
define('_gameserver_deleted','De Gameserver is verwijderd.');
define('_gameserver_slots_invalid','Sloten bevat ongeldige letters (0-9 zijn toegestaan)');
define('_gameserver_port_invalid','De Poort bevat ongeldige letters (0-9 zijn toegestaan)');
define('_gameserver_port_in_use','Poort reeds in gebruik');
define('_gameserver_mass_error','The Hoeveelheid bevat ongeldige letters (0-9zijn toegestaan)');
define('_gameserver_dedicated_invalid','Dedicated bevat ongeldige letters (0-9 zijn toegestaan)');
define('_gameserver_user_invalid','The User bevat ongeldige letters (a-z,A-Z,0-9 zijn toegestaan)');
define('_gameserver_game_invalid','The Game bevat ongeldige letters (a-z,A-Z,0-9._- zijn toegestaan)');
define('_gameserver_dedi_id_invalid','Ongeldige Dedicated ID');
define('_gameserver_installed','De Gameserver zal worden geïnstalleerd, dit kan even duren.');
define('_gameserver_mass','Hoeveelheid');
define('_gameserver_pw_changed','FTP Password werd veranderd.');
define('_gameserver_game_change','Druk Herinstalleer om de installatie van het nieuwe Spel te voltooien.');
define('_gameserver_map_invalid','De Map bevat ongeldige letters (a-z,A-Z,0-9._-) zijn toegestaan)');
define('_gameserver_parameter_invalid','De Parameters bevatten ongeldige letters (a-z,A-Z,0-9._ -) zijn toegestaan)');

 ?>
