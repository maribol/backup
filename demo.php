<?php

set_time_limit(0);
include('Backup.class.php');
$options = array(
    'recursive'=>1
);
$archiveName = 'backup-'.date('Y-m-d').'.zip';
$bk = new Backup($archiveName, $options);
$bk->collectFiles('*');
$created = $bk->createZip();
if ($created) {
    echo 'Archive successfully created';
}else{
    echo "Can't create or upload the archive<br /><pre>";
    print_r($bk->errors);
}