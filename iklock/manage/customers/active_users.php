<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/connection.php");

$sql = "TRUNCATE ikase.cse_active_users";

$stmt = DB::run($sql);

$sql = "INSERT INTO ikase.cse_active_users (customer_id, active_year, active_month, user_count, user_names)
SELECT ulog.customer_id, YEAR(ulog.dateandtime) active_year, MONTH(ulog.dateandtime) active_month, COUNT(DISTINCT ulog.user_uuid) user_count, GROUP_CONCAT(DISTINCT ulog.user_name ORDER BY ulog.user_name) user_names
FROM ikase.cse_userlogin ulog
INNER JOIN ikase.cse_user usr
ON ulog.user_uuid = usr.user_uuid AND usr.activated = 'Y'
WHERE ulog.customer_id > -1
AND ulog.customer_id != 1033
AND ulog.user_name != 'Matrix Admin'
AND ulog.user_name != 'Nick Giszpenc'
AND ulog.user_name != 'Thomas Smith'
AND ulog.`status` = 'IN'
GROUP BY ulog.customer_id, YEAR(ulog.dateandtime), MONTH(ulog.dateandtime)
ORDER BY ulog.customer_id, YEAR(ulog.dateandtime), MONTH(ulog.dateandtime);";

$stmt = DB::run($sql);

die("done at " . date("m/d/Y H:i:s")); 
/*

UPDATE ikase.cse_customer cus, 
	(
		SELECT customer_id, MIN(dateandtime) start_date
		FROM ikase.cse_userlogin ul
        WHERE ul.customer_id > -1
		AND ul.customer_id != 1033
		AND ul.user_name != 'Matrix Admin'
		AND ul.user_name != 'Nick Giszpenc'
		AND ul.`status` = 'IN'
		GROUP BY ul.customer_id
		ORDER BY ul.customer_id
        ) ul
SET cus.start_date = ul.start_date
WHERE cus.customer_id = ul.customer_id

UPDATE ikase.cse_customer
SET user_rate = 15
WHERE user_rate = 0
AND corporation_rate = 0
*/
?>
