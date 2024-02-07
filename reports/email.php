<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$homepage = file_get_contents('demographics_sheet.php?case_id=42');
echo $homepage;
?>