<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");

$query = "UPDATE tbl_customer 
SET `xl_filed` = 'Y'
WHERE cus_id = " . $cus_id;
$result = mysql_query($query, $r_link) or die("unable to update customer<br>" . $query);

mysql_close($r_link);
//die($query);
die("xl set");
?>

set_xl.php?cus_id=1003&suid=024fa06235570b4..72.87.128.38