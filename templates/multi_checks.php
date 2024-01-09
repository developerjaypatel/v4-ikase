<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
require_once('../shared/legacy_session.php');
session_write_close();

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	header("location:index.html");
	die();
}

include("../api/connection.php");
$ids = passed_var("ids", "post");
$ids = explode(",", $ids );
$row_counter = 0;
foreach($ids as $id) {
	$frame = "
	<div>
		<iframe src='../reports/print_checks.php?id=" . $id . "' style='height:3.66in; width:100%' frameborder='0' scrolling='no'></iframe>
	</div>";
	$arrRow[] = $frame;
	
	if (($row_counter%3)==0) {
		if ($row_counter != 0) {
			$arrRow[count($arrRow) - 1] .='
			<div class="page-break"></div>';
		}
	}
	$row_counter++;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>iKase :: Bulk Print Checks</title>
    <style>
    body {
        width: 8.5in;
        margin: 0in .1875in;
        }
	.page-break  {
        clear: left;
        display:block;
        page-break-after:always;
        }
    </style>

</head>
<body>
<?php foreach($arrRow as $row) {
	echo $row;
}
?>
</body>
</html>
