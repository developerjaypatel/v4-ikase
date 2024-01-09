<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="lib/tripledes.js"></script>
<script src="lib/localforage.js"></script>
<script language="javascript">

function setIt(){
	var eword = document.getElementById("email_password").value;
	
	var encrypted = CryptoJS.DES.encrypt(eword, "<?php echo $_SESSION["user_id"]; ?>");
	
	localforage.setItem('eword', encrypted.toString(), doSomethingElse);
	document.getElementById("get_it").style.display = "inline-block";
	document.getElementById("encrypted_holder").innerHTML = encrypted.toString();
}
var doSomethingElse = function() {
	//alert(encrypted + " stored");
}
</script>
<script>
function getIt() {
	localforage.getItem('eword', function(err, val) { 
		var decrypted = CryptoJS.DES.decrypt(val, "<?php echo $_SESSION["user_id"]; ?>");
		var newval = decrypted.toString(CryptoJS.enc.Utf8);
		document.getElementById("encrypted_holder").innerHTML += "<br />decrypt:" + newval; 
	});
}
</script>
</head>

<body>
<div id="encrypted_holder"></div>
<p>
  <input type="password" name="email_password" id="email_password" /> 
<a href="javascript:setIt()">set it</a></p>
<p><a href="javascript:getIt()" id="get_it" style="display:none">get it</a></p>
</body>
</html>