# ocDownloader
ocDownloader is an application for [ownCloud](https://owncloud.org). ocDownloader allows you to download files with multi-protocols using ARIA2 (HTTP(S)/FTP(S)/Youtube/BitTorrent)

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/DjazzLab/ocdownloader?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

***I'm looking for translators, every languages are needed***

***If you are interested, go to [ocDownloader Transifex Project](https://www.transifex.com/projects/p/ocdownloader)***

## ARIA2 installation
Please visit : [OCDownloader:Requirements (Linux Debian - JESSIE)](https://web.archive.org/web/20160912231334/https://wiki.sgc-univ.net/index.php/OCDownloader:Requirements_(Linux_Debian_-_JESSIE))
Everything you need to install ARIA2 and to run aria2c as a daemon !

## Other articles
Download YouTube video : [OCDownloader:Install The YouTube-DL Provider](https://wiki.sgc-univ.net/index.php/OCDownloader:Install_The_YouTube-DL_Provider)
Another Reference : [OCDownloader:Install The YouTube-DL Provider](https://rg3.github.io/youtube-dl/download.html)

*Note : You have to install Python on your server. This a requierement for youtube-dl.*
ARIA2 fallback : [OCDownloader:Aria2 fallback using CURL directly (Requirements)](https://web.archive.org/web/20160912225929/https://wiki.sgc-univ.net/index.php/OCDownloader:Aria2_fallback_using_CURL_directly_(Requirements))

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

## Author
Xavier Beurois
- Twitter : [@djazzlab](https://twitter.com/djazzlab)
- Blog : [Visit SGC-Univ.Net Blog!](https://www.sgc-univ.net)
- Wiki : [Visit SGC-Univ.Net Wiki!](https://wiki.sgc-univ.net)

## Releases notes
### v1.5.2 by Nibbels
- Added some basic function which scans the downloads folder to make new downloads visible within Owncloud/Nextcloud
- hooked in the new function to the pageload of "Complete Downloads" and "All Downloads" (This is way from perfect but works somehow)
- Hint: SERVER/fallback.sh still needs chmod 740 (I used CURL on nextcloud 11.0.3)
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
