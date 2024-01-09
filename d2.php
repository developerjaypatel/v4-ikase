<?php

$dbc = mysqli_connect('localhost', 'root', 'admin527#' , 'ikase')

or die(mysqli_connect_error());
mysqli_set_charset($dbc, 'utf-8');
phpinfo();
?>