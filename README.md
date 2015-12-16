# Prometheus
Gameserver Webinterface Prometheus

Prometheus is licensed under a
Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work. If not, see https://creativecommons.org/licenses/by-nc-sa/4.0/

![alt tag](https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Cc-by-nc-sa_icon.svg/120px-Cc-by-nc-sa_icon.svg.png)

Prometheus in a Nutshell:

- User Management: Add/Edit/Delete Users, Administrator/User Rank
- Dedicated Server Management: Add/Edit/Erase/(Delete) Dedicated Servers
- Templates: Add/Edit/Delete/Chill around while the Webinterface takes care of Steamupdates, also Support for Images (Minecraft..)
- Gameservers: Add/Edit/Delete, Console, Daily Restart, Autocomplete for Maps
- Bans: Blocks users after 3 incorrect guesses of there password, such wow
- Languages: German, (English 90% Translated)

![alt tag](http://i.imgur.com/QiFFRG9.png)
![alt tag](http://i.imgur.com/4TvggC1.png)

BEWARE: The Software is still in Alpha (Unstable), could possibly blow something up on your site

Requirements
- Webserver: PHP 5.6+, better 7.0
- Dedicated: Debian 8.0 or Ubuntu 14.05/Ubuntu 15.05

This Project uses:
- Bootstrap https://github.com/twbs/bootstrap
- Bootstrap-Toogle: https://github.com/minhur/bootstrap-toggle
- Font-Awesome: https://github.com/FortAwesome/Font-Awesome
- GameQ-2: https://github.com/Austinb/GameQ
- PHPSeclib: https://github.com/phpseclib/phpseclib
- JQuery: https://github.com/jquery/jquery
- PHP: https://github.com/php/php-src

Quick Installation

- Create a Database with a User, import the prometheus.sql file
- Update /pages/functions.php with your login details
- Add "you_had_one_job.php" and "come_to_the_dark_side_we_have_cookies.php" to your crontab, for example:

*/1 * * * * /usr/bin/wget --spider http://wi.yourdomain.com/you_had_one_job.php <br />
*/5 * * * * /usr/bin/wget --spider http://wi.yourdomain.com/come_to_the_dark_side_we_have_cookies.php

- Run /toolbox/create_account.php in your browser, you should now able to login with Email: 123@123.de and Password: 123456789
- DELETE /toolbox, this folder is just for debug/testing or fuck i locked me out again purposes.
- Done, you can go to Settings > General and change your Language if you wish.
- Read our FAQ: https://github.com/Ne00n/Prometheus/wiki/FAQ
