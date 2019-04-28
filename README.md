# ocDownloader
ocDownloader is an AGPL-licensed application for [Nextcloud](https://nextcloud.com) which allows you to download files from HTTP(S)/FTP(S)/Youtube/Bittorrent using the ARIA2 download manager/Curl and youtube-dl.

***I'm looking for maintainers and translators, every kind of support (especially pull requests) is highly welcome***.

## Companion apps/extensions for Firefox/Chrome/Opera/Vivaldi and Windows

Webextension addon for both Firefox-based and Chromium-based browsers (can be found on addons.mozilla.org and the Chrome Web Store): https://github.com/e-alfred/ocDownloader_webextension

Jetpack/PMKit addon for Firefox <=56 and Palemoon: https://github.com/e-alfred/ocDownloader_palemoon

UWP Windows 8.1/10 app: https://github.com/e-alfred/ocDownloader_WindowsDesktop

If you want to write your own app or extension, this would be highly welcome. ocDownloader has an API (look at controller/lib/api.php here: https://github.com/e-alfred/ocdownloader/blob/master/controller/lib/api.php) that allows you to add and list downloads using ocDownloader.

## ARIA2 installation
You have to install Aria2 on your system. To do this on Debian/Ubuntu you can use the following command:

`apt-get install aria2 curl php-curl`

After that, you have to run Aria2 on every boot with the same user that your webserver is running:

```
mkdir /var/log/aria2c /var/local/aria2c
touch /var/log/aria2c/aria2c.log
touch /var/local/aria2c/aria2c.sess
chown www-data.www-data -R /var/log/aria2c /var/local/aria2c
chmod 770 -R /var/log/aria2c /var/local/aria2c
sudo -u www-data aria2c --enable-rpc --rpc-allow-origin-all -c -D --log=/var/log/aria2c/aria2c.log --check-certificate=false --save-session=/var/local/aria2c/aria2c.sess --save-session-interval=2 --continue=true --input-file=/var/local/aria2c/aria2c.sess --rpc-save-upload-metadata=true --force-save=true --log-level=warn
```

You have to enable the RPC interface and save the session file of Aria2, otherwise your old downloads won't be listed after you restart Aria2. The file paths in the example can be changed if you want to store them elsewhere as long as the user running your webserver can access/write to them.

You can find the documentation of Aria2 [here](https://aria2.github.io/manual/en/html/index.html).

## Youtube-dl installation
To download Youtube videos, you have to install youtube-dl. The packages shipped with distributions are very outdated most of the time. To get around this, you can use this repository containing recent youtube-dl packages for Ubuntu by following the instructions there: [Webupd8 PPA](https://launchpad.net/~nilarimogard/+archive/ubuntu/webupd8)

For other distributions, you can [install youtube-dl manually](https://rg3.github.io/youtube-dl/download.html) *Note : You have to install Python on your server. This a requierement for youtube-dl.*  

After installing youtube-dl, you have to set the right path to your youtube-dl executeable in the admin settings of ocDownloader.

## Using Curl instead of Aria2
If you don't have Aria2 available on your server, you can use Curl which is directly integrated into PHP. This allows you to make HTTP(S) and FTP(S) downloads (BitTorrent is not supported by Curl). You need to install the PHP Curl module, on Debian you can use the following command to do this:

`apt-get install curl php-curl`

 Afterwards, you have to make sure that fallback.sh and fallback.php in the /SERVER directory of the ocDownloader app are executeable by your webserver user:

 `chmod +x SERVER/fallback.*`

If you have problems with Curl, the log files are saved to the /tmp folder on your server with these semicolon-seperated values:

- The status
- The download total size
- The current downloaded size
- The speed
- The PID of the PHP process which downloads your file (this allows to pause/restart the download while it is in progress)

## Translators
- Czech : Chazz
- Polish : Andrzej Kaczmarczyk
- Spanish / Catalan : Julián Sackmann, Erik Fargas
- German : sinus23, Moritz
- Russian : AlucoST, novikoz
- Hungarian : Károly Polacsek
- Bulgarian : Asen Gonov
- Persian : Amir Keshavarz
- Chinese : Young You, dzxx36gyy (顾益阳), whatot huang
- Italian : Leonardo Bartoletti, adelutti (Andrea), r.bicelli Riccardo Bicelli
- Danish : Janus Ljósheim, Johannes Hessellund
- Korean : Asen Gonov
- Dutch : msberends

## Authors
e-alfred  
Nibbels  
Loki3000  
(formerly) Xavier Beurois

## Releases notes
### v1.6.3
- Fixed settings menus
- Added Magnet link support (thanks @JasonPoon-cn)
- Added support for Nextcloud 16
### v1.6.1
- Fixed deprecated API calls to support Nextcloud 14+
### v1.5.6
- Fixed deprecated API calls to support Nextcloud 13+
### v1.5.5
- Fixed CSS compatibility with Nextcloud 13 (thanks @Lokarde)
- Fixed display problems on mobile browsers
- Fixed downloads not showing up in Nextcloud using CURL (thanks @muellerlukas)
- Fixed internal server errro if PID == 0 using CURL (thanks @muellerlukas)
### v1.5.4
- Dutch translation (thanks to @msberends)
- Allow setting a custom filename after downloading for HTTP/FTP
- Truncate filenames if URL contains parameters after filename (thanks @Loki3000)
- Show tooltip if filename is too long for downloaded items table (thanks @Loki3000)
- Add fields for custom referer and user agent if using HTTP/FTP for download
### v1.5.3 (thanks @Nibbels)
- Some changes within design and site unlocks for CURL-Users
### v1.5.2 (thanks @Nibbels)
- Added some basic function which scans the downloads folder to make new downloads visible within Owncloud/Nextcloud
- hooked in the new function to the pageload of "Complete Downloads" and "All Downloads" (This is way from perfect but works somehow)
- removed 1.5.1 from commits because of license change.
### v1.5.1
- Fixing minor CSS / JS bug
### v1.5
- Update languages, add following languages : Persian, Chinese, Italian, Danish, Korean
- You can now upload a torrent file directly from the application
- The checkbox to remove a torrent file when adding a torrent download is now checked by default
- Add admin settings to manage protocols permissions
- Add a set_time_limit to 0 for the cURL fallback download script
- Add upload / download speed limit settings in the admin panel
- Add a ratio control for the BitTorrent protocol in the personal settings panel (default : unlimited - "0.0")
- Add a seed time control for the BitTorrent protocol in the personal settings panel (default : 1 week)
