<?
include ("../text_editor/ed/functions.php");
include ("../text_editor/ed/datacon.php");

$file = passed_var("file");
//check the extension
$arrFile = explode(".", $file);
if ($arrFile[count($arrFile)-1]!="pdf") {
	header("location:../fileupload/server/php/file_container/" . $file);
	die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Preview</title>
<link rel="stylesheet" type="text/css" media="screen" href="jq.css" />
<style type="text/css">
#main { background: #fff; margin: 20px; text-align: center }
a.media   { display: block; }
div.media { font-size: small; margin: 25px; margin: auto}
div.media div { font-style: italic; color: #888; }
#lr { border: 1px solid #eee; margin: auto }
div.example { padding: 20px; margin: 15px 0px; background: #ffe; clear:left; border: 1px dashed #ccc; text-align: left }
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
<script type="text/javascript" src="chili-1.7.pack.js"></script>
<script type="text/javascript" src="jquery.media.js"></script>
<script type="text/javascript" src="jquery.metadata.js"></script> 
<script type="text/javascript">
    $(function() {
        $('a.media').media({width:"100%", height:$( window ).height() - 20});
    });
</script>
</head>

<body>
<a class="media" href="../fileupload/server/php/file_container/<?php echo $file; ?>"></a> 
</body>
</html>