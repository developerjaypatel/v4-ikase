<?php 
include ("functions.php");
include ("datacon.php");

$case_id = passed_var("case_id");
$notes_id = passed_var("notes_id");

//die($case_uuid);

$query = "SELECT `notes_id`, `cse_notes`.`notes_uuid`, `cse_notes`.`note`, `cse_notes`.`title`, `entered_by`, `status`, `dateandtime`, `verified`, `cse_notes`.`deleted` 
			FROM `cse_notes` 
			INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
			INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`
			AND `cse_case`.`case_id` = '" . $case_id . "')
			WHERE `cse_notes`.`deleted` = 'N'
			AND `cse_notes`.`notes_id` = '" . $notes_id . "'";

$result = mysql_query($query, $link) or die("unable to get home text<br>" . $query);
$numbs = mysql_numrows($result);
//echo $query . "<br>" . $numbs . "<br>";
for ($x=0;$x<$numbs;$x++) {
	$notes_id = mysql_result($result, $x, "notes_id");
	$notes_uuid = mysql_result($result, $x, "notes_uuid");
	$note = mysql_result($result, $x, "note");
	
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link type="text/css" rel="stylesheet" href="demo.css">
<link type="text/css" rel="stylesheet" href="../jquery-te-1.4.0.css">

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" charset="utf-8"></script>
<script type="text/javascript" src="../jquery-te-1.4.0.min.js" charset="utf-8"></script>
</head>

<body>
<span name="span" class="jqte_editor" style="background:url(../../img/glass.png)"><?php echo $note; ?></span>

<script>
	//$('.jqte_editor').jqte();
	$(".jqte_editor").jqte({change: function(){ 
			//alert("The editor is changed"); 
			var note_value = $(".jqte_editor").html();
			var stringOfHtml = note_value;
			var html = $(stringOfHtml);
			html.find('script').remove();
			
			note_value = html.wrap("<div>").parent().html();
			
			if (typeof note_value != "undefined") {
				parent.updateNote(note_value);
			} else {
				parent.updateNote(stringOfHtml);
			}
		}
	});
	// settings of status
	var jqteStatus = true;
	$(".status").click(function()
	{
		jqteStatus = jqteStatus ? false : true;
		$('.jqte_editor').jqte({"status" : jqteStatus})
	});
	
	
</script>

</body>
</html>