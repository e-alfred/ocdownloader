# ocDownloader
ocDownloader is an AGPL-licensed application for [Nextcloud](https://nextcloud.com) which allows you to download files from HTTP(S)/FTP(S)/Youtube/Bittorrent using the ARIA2 download manager/Curl and youtube-dl.

***I'm looking for maintainers and translators, every kind of support (especially pull requests) is highly welcome***

***If you are interested in translating, visit to [ocDownloader Transifex Project](https://www.transifex.com/projects/p/ocdownloader)***

## Companion apps/extensions for Firefox/Chrome/Opera/Vivaldi and Windows

Webextension plugin for both Firefox-based and Chromium-based browsers: https://github.com/e-alfred/ocDownloader_ChromeExtension

UWP Windows 8.1/10 app: https://github.com/e-alfred/ocDownloader_WindowsDesktop

## ARIA2 installation
To install Aria2 on Debian/Ubuntu use the following command:

`apt-get install aria2 curl php-curl`

After that, you have to run Aria2 on every boot with the same user that your webserver is running:

`sudo -u www-data aria2c --enable-rpc --rpc-allow-origin-all -c -D --log=/var/log/aria2.log --check-certificate=false  --save-session=/var/www/aria2c.sess --save-session-interval=2 --continue=true --input-file=/var/www/aria2c.sess  --rpc-save-upload-metadata=true --force-save=true`

You have to enable the RPC interface and save the session file of Aria2, otherwise your old downloads won't be listed after you restart Aria2. The file in the example is stored in /var/www/aria2c.sess, but you can put it anywhere as long as the user running your webserver can access/write to it.

You can find the documentation of Aria2 [here](https://aria2.github.io/manual/en/html/index.html)

## Youtube-dl installation
To download Youtube videos, you have to install youtube-dl. For Ubuntu, you can use this repository and follow the instructions there: [Webupd8 PPA](https://launchpad.net/~nilarimogard/+archive/ubuntu/webupd8)

For other distributions, you can [install youtube-dl manually](https://rg3.github.io/youtube-dl/download.html) *Note : You have to install Python on your server. This a requierement for youtube-dl.*  

After installing youtube-dl, you have to set the right path to your youtube-dl executeable in the admin settings of ocDownloader.

## Using Curl instead of Aria2
If you don't have aria2 available on your server, you can use curl which is directly integrated into PHP. This allows you to make HTTP(S) and FTP(S) downloads (BitTorrent is not supported by Curl) To run this, you have to install the corresponding PHP curl module. Afterwards, you have to make sure that fallback.sh and fallback.php in the /SERVER directory are executeable by your webserver user (chmod 740 should be sufficient).

Log files are saved to the /tmp folder on your server with these semicolon-seperated values:

- The status
- The download total size
- The current downloaded size
- The speed
- The PID of the PHP process which downloads your file (this allow to stop the download while it is in progress)

## Translators
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

## Authors
e-alfred  
Nibbels  
(formerly) Xavier Beurois

## Releases notes
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
