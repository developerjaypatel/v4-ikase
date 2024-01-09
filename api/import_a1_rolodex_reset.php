<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../shared/legacy_session.php');
set_time_limit(30000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;
$last_updated_date = date("Y-m-d H:i:s");

include("connection.php");
include("customer_lookup.php");
DB::delete("ikase_{$data_source}.cse_person", ['last_update_user' => 'import']);
DB::delete("ikase_{$data_source}.cse_corporation", ['last_update_user' => 'import']);
DB::run("UPDATE `ikase_" . $data_source . "`.cse_card
SET ikase_table = '',
ikase_uuid = ''
WHERE ikase_uuid != ''");
echo "done at " . date("H:i:s");
