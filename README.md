Maribol Backup Service v 0.1
===============

<h3>General options</h3>

<code>recursive</code> If this option is true then all the directories from the given directory, will be open and so on

<code>extensionsOnly</code> Add to archive files with this extensions. Extensions are separated by comma. Eg: php,css,tpl

<code>extensionsExclude</code> Add to archive all the files except files with this extenions. Extensions are separated by comma. zip,jpg,rar,html


<h4>FTP Settings</h4>
<code>ftp[active]</code> Define if the ftp upload of the backup should be made.

<code>ftp[host]</code> FTP Host to upload archive. Eg.: example.com

<code>ftp[port]</code> FTP Port to upload archive. Default is 21

<code>ftp[username]</code> FTP Username to upload archive. Eg.: johndoe@example.com

<code>ftp[password]</code> FTP Password to upload archive

<code>ftp[path]</code> Upload the backup file to the folowing directory. Eg.: backups/2013-08-28. If the given directories don't exist will be created. This path should be the relative path from user's root.

<code>ftp[transferType]</code> FTP_ASCII or FTP_BINARY

<h3>How to use</h3>
<pre>
include('Backup.class.php');
$options = array(
    'recursive'=>1
);
$archiveName = 'backup-'.date('Y-m-d').'.zip';
$bk = new Backup($archiveName, $options);
$bk->collectFiles('D:\wamp\www\*');
$created = $bk->createZip();
if ($created) {
    echo 'Archive successfully created';
}
</pre>