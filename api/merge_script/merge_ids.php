<?php

//ikase Connection for .ORG
$iconn = new mysqli("25.23.27.161", "root", "Admin527#", "ikase");

//ikase Connection for .v2
$v2conn = new mysqli("25.70.61.4", "root", "Admin527#", "ikase");

$sql_customerIds = $iconn->query("SELECT customer_id FROM cse_user GROUP BY customer_id");
while($customerIds = $sql_customerIds->fetch_array()){

    $sql_userids = $iconn->query("SELECT customer_id, user_id FROM cse_user 
                                    WHERE customer_id = '$customerIds[customer_id]'");
    while($userids = $sql_userids->fetch_array()){
        
        $combinedIds = $userids['user_id'].'.'.$userids['customer_id'];

        $v2conn->query("UPDATE cse_user SET new_field = '$combinedIds' WHERE user_id = '$userids[user_id]'");
    }
}
?>