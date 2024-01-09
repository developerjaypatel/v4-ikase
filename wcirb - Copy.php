<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("api/connection.php");

$home = file_get_contents("https://www.caworkcompcoverage.com/Search.aspx");

$home = str_replace('action="./Disclaimer.aspx"', 'action="https://www.caworkcompcoverage.com/Disclaimer.aspx"', $home);
die($home);
$url = "https://www.caworkcompcoverage.com/SearchWebService.asmx/GetEmployerNameList";
$params = array("prefixText"=>"matrix", "count"=>3);

$result = post_curl($url, $params);

die($result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<script language="javascript">
//

function getEmployerName() {
	var url = "https://www.caworkcompcoverage.com/SearchWebService.asmx/GetEmployerNameList";
	var formData = new FormData();
	formData.append("prefixText", "matrix");
	formData.append("count", 3);
	
	var r = new XMLHttpRequest();
	r.open("POST", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		data = r.responseText;
				
		console.log(data);
	  }
	};
	r.send(formData);
}
</script>
</body>
</html>