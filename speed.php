<?php
$word = new COM("Word.Application") or die ("Could not initialise Object.");

die(print_r($_SERVER)); //FIXME: security risk. either delete the file or die with no information while it's not finished

$start_time = microtime(true);

$db  = DB::conn(DB::DB_IP_54);
$sql = "SELECT * FROM `cse_user`";
$sql .= " WHERE 1";

$sql2 = "SELECT * FROM `cse_eams_reps`";
$sql2 .= " WHERE 1";

try {
    $users = DB::select($sql);
    $reps = DB::select($sql2);
    /*
    foreach($users as $user){
        echo $user->user_name . "<br />";
    }
    */
}
catch (PDOException $e) {
    echo json_encode(["error" => ["text" => $e->getMessage()]]);
}

if ($start_time != "") {
    $time        = microtime(true);
    $finish_time = $time;
    $total_time  = round(($finish_time - $start_time), 4);
    echo '<div style="font-size:1.2em; color:black; font-family:Arial">AWS generated in '.$total_time.' seconds.'.
        "</div>";
}
die();
