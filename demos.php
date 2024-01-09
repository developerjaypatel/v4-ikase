<?php
use Api\JsonErrorRenderer;

//TODO: does it make any sense to be have a hardcoded setting for a single file?
/** Download as many times as you wish until expiration date, or only once? */
const ALLOW_MULTIPLE_DOWNLOADS = true;

include("api/connection.php");

if (empty($_GET['key'])) {
    JsonErrorRenderer::simple('no so');
}

//TODO: not sure if I could replace die()'s with JsonErrorRenderer, but I did so I had something clean to test against
$key   = passed_var('key', 'get');
$entry = DB::runOrApiError("SELECT * FROM cse_downloads WHERE downloadkey = ? LIMIT 1", [$key])->fetchObject();

if (strtotime($entry->expires) < time()) {
    JsonErrorRenderer::simple('This download has expired.');
}

if ($entry->downloads && !ALLOW_MULTIPLE_DOWNLOADS) {
    //this file has already been downloaded and multiple downloads are not allowed
    JsonErrorRenderer::simple('This file has already been downloaded.');
}

//move through - update the DB to say this file has been downloaded
DB::runOrApiError("UPDATE cse_downloads SET downloads = downloads + 1 WHERE downloads_id = ?", [$entry->downloads_id]);

$file = $entry->file;

[$customer_id, $case_id] = explode('/', $file);

if (!is_numeric($case_id)) {
    JsonErrorRenderer::simple('no c');
}
if (!is_numeric($customer_id)) {
    JsonErrorRenderer::simple('no cs');
}

echo file_get_contents(UPLOADS_PATH.$file.DC.'demographics.html');
