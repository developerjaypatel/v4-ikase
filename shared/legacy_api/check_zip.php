<?php
/**
 * DRY code to return some zip code details.
 * If you need a different database connection, require your own datacon.php file (i.e. have a PDO connection at $link
 * before including this file).
 */
include(ROOT_PATH.'text_editor/ed/functions.php');

//the standard is to use the following connection information, but in a specific case we need a different database
if (!isset($link)) {
    include(ROOT_PATH.'text_editor/ed/datacon.php');
}

$zip = passed_var('query');
if (strlen($zip) < 3) {
    die();
}

$details = (new Zipcode($link))->get_zip_details($zip);
if ($details) {
    echo "{$details['city']}|{$details['state_prefix']}|{$details['county']}";
}
