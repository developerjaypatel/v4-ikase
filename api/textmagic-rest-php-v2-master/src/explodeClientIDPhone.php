<?php

$phone_numbers = "1068|9098857190,1194|5622984170,1068|9098857190,1194|5622984170,1068|9098857190,1194|5622984170";

$clientID_phone_numbers_arr = explode (",", $phone_numbers); 
foreach($clientID_phone_numbers_arr as $clientID_phone_number) {
    $clientID_phone_numbers_arr2 = explode ("|", $clientID_phone_number); 

    //print_r ($clientID_phone_numbers_arr2);
    echo ("<br>");
    echo("Client_ID " . $clientID_phone_numbers_arr2[0]);  
    echo ("<br>");
    echo("Phone_number " .  $clientID_phone_numbers_arr2[1]);  
    echo ("<br>");
     
} 

?>