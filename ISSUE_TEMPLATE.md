<!--
Thanks for reporting issues back to ocDownloader!

This is the bug tracker for the ocDownloader app.

To make it possible for us to help you please fill out below information carefully. 
You can also use the Issue Template application to prefill most of the required information: https://apps.nextcloud.com/apps/issuetemplate
--> 
### Steps to reproduce
1.
2.
3.

### Expected behaviour
Tell us what should happen

### Actual behaviour
Tell us what happens instead

### Server configuration

**Operating system**:

**Web server:**

**Database:**

**PHP version:**

**Nextcloud version:** (see Nextcloud admin page)

**Updated from an older Nextcloud/ownCloud or fresh install:**

**Where did you install Nextcloud from:**

**Signing status:**
<details>
<summary>Signing status</summary>

```
Login as admin user into your Nextcloud and access 
http://example.com/index.php/settings/integrity/failed 
paste the results here.
```
</details>

**List of activated apps:**
<details>
<summary>App list</summary>

```
If you have access to your command line run e.g.:
sudo -u www-data php occ app:list
from within your Nextcloud installation folder
```
</details>

**Nextcloud configuration:**
<details>
<summary>Config report</summary>

```
If you have access to your command line run e.g.:
sudo -u www-data php occ config:list system
from within your Nextcloud installation folder

or 

Insert your config.php content here. 
Make sure to remove all sensitive content such as passwords. (e.g. database password, passwordsalt, secret, smtp password, …)
```
</details>

**Are you using external storage, if yes which one (currently not supported by ocDownloader):** local/smb/sftp/...

**Are you using encryption (currently not supported by ocDownloader):** yes/no

**Are you using an external user-backend, if yes which one:** LDAP/ActiveDirectory/Webdav/...

#### LDAP configuration (delete this part if not used)
<details>
<summary>LDAP config</summary>

```
With access to your command line run e.g.:
sudo -u www-data php occ ldap:show-config
from within your Nextcloud installation folder

Without access to your command line download the data/owncloud.db to your local
computer or access your SQL server remotely and run the select query:
SELECT * FROM `oc_appconfig` WHERE `appid` = 'user_ldap';


Eventually replace sensitive data as the name/IP-address of your LDAP server or groups.
```
</details>

#### ocDownloader configuration:

**Which downloader are you using:** Curl/Aria2

**Do you use a proxy:** Yes/No

**Which protocols do you allow**: FTP/HTTP/Bittorrent/Youtube

#### Aria2c configuration (remove if not used):

**Which command line/configuration options are you using:** Post command line/options here...

##### Aria2c error log
<details>
<summary>Aria2c log file (set the log level to debug by using --log-level=debug)</summary>

```
Insert your Aria2c error log here
```
</details>

### Client configuration
**Browser:**

**Operating system:**

### Logs
#### Web server error log
<details>
<summary>Web server error log</summary>

```
Insert your webserver log here
```
</details>

#### Nextcloud log (data/nextcloud.log)
<details>
<summary>Nextcloud log</summary>

```
Insert your Nextcloud log here
```
</details>

#### Browser log
<details>
<summary>Browser log</summary>

```
Insert your browser log here, this could for example include:

a) The javascript console log
b) The network log
c) ...
```
</details>

<!-- Issue template modified from https://github.com/nextcloud/weather/blob/master/issue_template.md --> 
