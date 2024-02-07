<?php 

// Connect to MySQL

$conection = mysqli_connect("localhost","josecarl","5W3v2p5wYe") or die("Not connected");

mysqli_select_db($conection,"josecarl_vistoner") or die("could not log in");



// Delete Event which has past Event date

$query = mysqli_query($conection,"DELETE FROM events WHERE end < NOW()")

or die(mysqli_error($query)); 



?>





