<?php
include("../api/manage_session.php");
include("../api/connection.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("noNoNO");
}

$customer_id = $_SESSION["user_customer_id"];
$user_id = $_SESSION["user_plain_id"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" href="https://www.ikase.website/css/bootstrap.3.0.3.min.css">
</head>
<body style="background:none">
<?php //die("Down for maintenance"); ?>
<div style="color:white; font-family:arial; padding-top:10px">
	<div style="background:orange; color:black; font-weight:bold; width:600px; margin-top:10px; margin-bottom:10px; padding:5px">
    	<p>The Batchscan process has been updated as of 01/31/2018.  If you have not done so already, please make sure to reload iKase without caching by typing <span style='color:white'>Ctrl-F5</span> keys, or clicking this <a href="javascript:parent.location.reload(true);" title="Click to reload iKase and empty browser cache" style="color:white">	reload</a> link.</p> 
    	<p>Thank you for your patience as we improve iKase for you.  Your feedback is very much appreciated and we strive to update the system per your wishes.</p>
    </div>
    <form action="dms_uploadifive.php" enctype="multipart/form-data" method="post" id="mainform">
        <input type="hidden" name="subscriber" value="ikase" />
        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
        <input type="file" name="Filedata[]" id="Filedata" />
        <div style="padding-top:10px">
            <button class="btn btn-primary" id="upload_button" onclick="submitForm(event)">Upload Scan</button>
		</div>
    </form>
</div>
<script type="text/javascript">
function submitForm(event) {
	event.preventDefault();
	document.getElementById("upload_button").style.visibility = "hidden";
	document.getElementById("mainform").submit();
}
</script>
</body>
</html>