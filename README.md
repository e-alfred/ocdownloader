# ocDownloader
ocDownloader is an AGPL-licensed application for [Nextcloud](https://nextcloud.com) which allows you to download files from HTTP(S)/FTP(S)/Youtube/Bittorrent using the ARIA2 download manager/Curl and youtube-dl.

***I'm looking for maintainers and translators, every kind of support (especially pull requests) is highly welcome***

***If you are interested in translating, visit to [ocDownloader Transifex Project](https://www.transifex.com/projects/p/ocdownloader)***

## Companion apps/extensions for Firefox/Chrome/Opera/Vivaldi and Windows

Webextension plugin for both Firefox-based and Chromium-based browsers: https://github.com/e-alfred/ocDownloader_ChromeExtension

UWP Windows 8.1/10 app: https://github.com/e-alfred/ocDownloader_WindowsDesktop

## ARIA2 installation
Please visit : [OCDownloader: Requirements for Debian-based systems](https://web.archive.org/web/20160912231334/https://wiki.sgc-univ.net/index.php/OCDownloader:Requirements_(Linux_Debian_-_JESSIE))
Everything you need to install ARIA2 and to run aria2c as a daemon !

## Other articles
To download Youtube videos, you have to install youtube-dl. For Ubuntu, you can use this repository: [Webupd8 PPA](https://launchpad.net/~nilarimogard/+archive/ubuntu/webupd8)

For other distributions, you can [install youtube-dl manually](https://rg3.github.io/youtube-dl/download.html) *Note : You have to install Python on your server. This a requierement for youtube-dl.*  

Aria2 fallback : [OCDownloader: Aria2 fallback using CURL directly (Requirements)](https://web.archive.org/web/20160912225929/https://wiki.sgc-univ.net/index.php/OCDownloader:Aria2_fallback_using_CURL_directly_(Requirements))

## CURL installation hint
The File SERVER/fallback.sh still needs chmod 740. This is no perfect solution but seems to work.

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
