<?php
//die(print_r($_SERVER));
include_once(__DIR__.'/lib/QrReader.php');

$dir = scandir('qrcodes');
foreach($dir as $file) {
    if($file=='.'||$file=='..') continue;

    print $file . " -- " . filesize("C:\\inetpub\\wwwroot\\dis\\pws\\quicks\\qrcode\\qrcodes\\" . $file);
    print ' --- ';
    $qrcode = new QrReader('qrcodes/'.$file);
    print $text = $qrcode->text();
    print "<br/>";
}
