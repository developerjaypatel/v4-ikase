<?php

$dbc = mysqli_connect('localhost', 'root', 'admin527#' , 'ikase')

or die(mysqli_connect_error());
mysqli_set_charset($dbc, 'utf-8');
///print_r(mysqli_get_connection_stats($dbc));
echo "<br>";
echo "<h3>Database connected!<h3>";
echo "<br>";
echo "<h2>PHP is Fun!</h2>";

echo "Hello world!<br>";
echo "I'm about to learn PHP!<br>";
echo "This ", "string ", "was ", "made ", "with multiple parameters.";
phpinfo();
?> 