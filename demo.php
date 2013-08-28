<?php

set_time_limit(0);
include('Backup.class.php');
$options = array();
$archiveName = 'backup-'.date('Y-m-d').'.zip';
$bk = new Backup($archiveName, $options);
$bk->collectFiles('D:\wamp\www\*');
$created = $bk->createZip();
if ($created) {
    echo 'Archive successfully created';
}