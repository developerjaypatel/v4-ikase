<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../../text_editor/ed/functions.php");
include ("../../../text_editor/ed/datacon.php");

$cus_id = passed_var("cus_id", "post");
$affected = DB::updateOrDie('cse_customer', ['deleted' => 'Y'], ['customer_id' => $cus_id]);
die($affected? "$cus_id deleted" : 'wrong ID given');
