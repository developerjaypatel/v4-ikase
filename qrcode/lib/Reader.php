<?php

namespace Zxing;

require_once(__DIR__.'qrcode/QRCodeReader.php');

interface Reader {

    public function decode($image);


    public  function reset();


}
