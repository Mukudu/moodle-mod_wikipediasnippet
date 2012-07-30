Installation Notes
==================
Pre-requisites
==============
You must have access to the server containing Moodle. This can be direct access, through a network or to a remote server through Internet with an FTP client, you can't do it from "inside" Moodle itself.

INSTALLATION
============
1.  Latest version of the zipped file for this plug is available from git://

2.  Unzip the zipped file somewhere on your local computer

3.  Upload the unzipped folder to mod folder in the moodle root folder e.g /var/www/html/ on each of the Moodle servers

4.  Alternatively the zip file can be uploaded to the folder in step 3 and the zipped file unzipped on the servers.

5.  Ensure that the folder has the same permissions and owner as the other folders in the directory -

    1.  chown -R apache:apache wikipediasnippet
    2.  chmod -R 755 wikipediasnippet

6.  In your browser, go to your Moodle site, login as administrator and choose Site Administration > Notifications  and click on the Continue Button.

7.  Moodle will report successful completion or any errors.

8.  Click continue and you will be prompted for
    * The default number of attendees for tutorials - Default is 30
    * The default locking status - the default is unlocked
    * Whether the current installation of Moodle is a live service - default is off and should only be on when installed on a live service - this ensure notifications are sent.

9. Click 'Save Changes' and plugin will now be installed.

UNINSTALLATION
==============
1.  In your browser, go to your Moodle site, login as administrator and choose SiteAdministration -> Plugins -> plugins -> Manage plugins, find the plugin's entry and select 'Delete'

2.  Select 'Continue' on the next page

3.  Delete the relevant folder /mod/wikipediasnippet from the moodle root e.g. /var/www/html/mod/wikipediasnippet then select Continue in the browser.

4.  The plugin should no longer appear in the list.
