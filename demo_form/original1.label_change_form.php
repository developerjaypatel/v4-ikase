<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<body>
<div>
<label for="this_label_input" id="this_label" style="font-size:1em; position:absolute; left:10px; top:10px; cursor:text">Label</label>
<input id="this_input" value="" style="height:30px; font-size:.8em; padding-bottom:0px; margin-bottom:0px; line-height: 40px" onblur="changeBack()" />
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script language="javascript">
$('#this_input, #this_label').click(function() {
    $('#this_label').css("font-size", "0.48em");
	$('#this_input').focus();
	//$('label').css("", "top");
});
function changeBack() {
	var input_val = $('#this_input').val();
	if (input_val == "") {
		$('#this_label').css("font-size", "1em");
		//$('label').css("", "top");
	}
};
</script>
</body>
</html>