<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

include("../../manage/classes/cls_note.php");

if (!isset($blnReturnRows)) {
	$blnReturnRows = true;
}
$query = passed_var('query');
$note_id = passed_var("note_id");
$cus_id = passed_var("the_cus_id");
if (!is_numeric($cus_id)) {
	die();
}
if ($cus_id=="") {
	$the_row = "|||||";
	echo $the_row;
	mysql_close($r_link);
	exit();
}
$my_note = new note($r_link);
$my_note->cus_id = $cus_id;
$result = $my_note->search("","dateandtime DESC",addslashes($query),"note", "", "");
$numbs = mysql_numrows($result);
//echo "numb:" . $numbs . "<br />";
$arrRows = array();
for ($xdrop=0;$xdrop<$numbs; $xdrop++) {
	$note_id = mysql_result($result, $xdrop, "note_id");
	$entered_by = mysql_result($result, $xdrop, "entered_by");
	$entered_by = str_replace(" ", "&nbsp;", $entered_by);
	$note = mysql_result($result, $xdrop, "note");
	$note = str_replace("\r\n", "<br />", $note);
	$dateandtime = mysql_result($result, $xdrop, "dateandtime");
		
	$the_row = $note . "|" . $note_id . "|" . date("m/d/y", strtotime($dateandtime)) . "&nbsp;" . date("h:iA", strtotime($dateandtime)) . "|" . $entered_by;
	$arrRows[] = $the_row;
}
if ($blnReturnRows) {
	mysql_close($r_link);
	$maincontent = implode("\n", $arrRows);
	//die("here");
	echo $maincontent;
	exit();
}
?>