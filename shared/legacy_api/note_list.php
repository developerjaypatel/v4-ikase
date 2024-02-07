<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

if (!isset($blnReturnRows)) {
    $blnReturnRows = true;
}
$query   = passed_var('query');
$note_id = passed_var('note_id');
$cus_id  = passed_var('the_cus_id');

if (!is_numeric($cus_id)) {
    die();
}

if ($cus_id == '') {
    echo '|||||';
    exit();
}

$my_note         = new Note($link);
$my_note->cus_id = $cus_id;
$result          = $my_note->search('', 'dateandtime DESC', addslashes($query), 'note');
$arrRows         = [];
while ($entry = $result->fetch(PDO::FETCH_ASSOC)) {
    $dateandtime = strtotime($entry['dateandtime']);
    $note        = str_replace("\r\n", '<br />', $entry['note']);
    $entered_by  = str_replace(' ', '&nbsp;', $entry['entered_by']);
    $arrRows[]   = "$note|{$entry['note_id']}|".date("m/d/y", $dateandtime).'&nbsp;'.date("h:iA", $dateandtime)."|$entered_by";
}

if ($blnReturnRows) {
    echo implode("\n", $arrRows);
    exit();
}
