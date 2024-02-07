<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../../api/manage_session.php");
session_write_close();

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include ("../../api/connection.php");

if (count($_SESSION)==0) {
	header("location:../../index.php");
}

include("../customers/sec.php");

$cus_id = passed_var("cus_id", "get");
$host = $_SERVER['HTTP_HOST'];
//die($cus_id);

if (!is_numeric($cus_id)) {
	die();
}
$sql = "SELECT cus_name_first, cus_name_last, cus_name, cus_street, cus_city, cus_state, cus_zip 
FROM iklock.customer 
WHERE customer_id = " . $cus_id;
//echo $sql;
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->execute();
	$customer = $stmt->fetchObject(); //$stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}

$cus_name = $customer->cus_name;
$cus_name_first = $customer->cus_name_first;
$cus_name_last = $customer->cus_name_last;

$the_cus_street = $customer->cus_street;
$the_cus_city = $customer->cus_city;
$the_cus_state = $customer->cus_state;
$the_cus_zip = $customer->cus_zip;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="nofollow,noindex" />
<title>Users</title>
<style type="text/css">
<!--
a.nav:link {
	color: #FFFFFF;
}
a.nav:visited {
	color: #FFFFFF;
}
a.nav:hover {
	color: #FFFFFF;
}
.yui-skin-sam .yui-dt .yui-dt-col-user_name {
	width: 150px;
	text-align: left;
}
body {
	background-color: #069;
	margin-top:10px;
}
.edit_user, .login_user {
	color:blue;
	cursor:pointer;
	text-decoration:underline;
}
-->
</style></head>

<body class="yui-skin-sam">
<?php //echo $_SESSION["user_customer_id"] . " // " . $_SESSION["user_plain_id"] . " // " . $_SESSION["user"]; ?>
<table width="770" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC">
    	<div style="float:right">
        	<?php
			echo "<span style='font-size:14pt'>" . $cus_name . "</span>";
			if ($cus_id > 0) {
				echo "<br /><span style='font-size:1.1em'>";
				echo "Welcome " . $cus_name_first . " " . $cus_name_last;
				echo "</span>";
				echo "<br /><span style='font-size:0.8em'>" . ucwords(strtolower($the_cus_street)) . "<br />" . ucwords(strtolower($the_cus_city)) . ", " . $the_cus_state . " " . $the_cus_zip;
				echo "</span>"; 
				
			}
			?>
        </div>
        <img src="../../img/iklock_logo.png" alt="iKase" height="90" />
    </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#000066" style="color:#FFFFFF" align="left">
   	  <div style="float:right">
      	<a href="../customers/index.php" class="nav">List of Customers</a>
        <a href="editor.php?cus_id=<?php echo $cus_id; ?>" class="nav">New User</a></div>
        <strong><?php echo $cus_name; ?> - List of Users</strong>    </td>
  </tr>
  <tr>
  	<td colspan="2" align="left">
    	<div align="left">
            <div id="list_users" style="padding-top:10px"></div>
        </div>
    </td>
</table>
<?php //die($cus_id . " - cus"); ?>
<script language="javascript">
function postForm(path, params, method, target) {
    method = method || "post"; // Set method to post by default if not specified.
	target = target || "_blank"; // Set method to post by default if not specified.
	
    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
	form.setAttribute("target", target);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}
function getUsers() {
	var url = "user_list.php?cus_id=<?php echo $cus_id; ?>";
	
	var r = new XMLHttpRequest();
	r.open("GET", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		data = r.responseText;
		document.getElementById("list_users").innerHTML = data;
		addUserListeners();
	  }
	};
	r.send();
}
function addUserListeners() {
	var classname = document.getElementsByClassName("edit_user");
	
	for (var i = 0; i < classname.length; i++) {
		classname[i].addEventListener('click', editUser, false);
	}
	
	var classname = document.getElementsByClassName("login_user");
	
	for (var i = 0; i < classname.length; i++) {
		classname[i].addEventListener('click', loginUser, false);
	}
}
function editUser(event) {
	var element = event.currentTarget;
	event.preventDefault();
	var user_id = element.id.replace("edit_user_", "");
	
	var url = "editor.php";
	var params = {cus_id: "<?php echo $cus_id; ?>", user_id: user_id};
	postForm(url, params, "post", "_self");
}
var confirmDelete = function(user_id) {
	var confirmit=confirm("Are you sure you want to delete this User");
	if (confirmit) {
		deleteTheUser(user_id);
	}
}
var deleteTheUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_delete.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var activateUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_activate.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
var inactivateUser = function (user_id) {
	//alert("delete user");

	var sendDeleteUrl = "user_inactivate.php";
	this.sentData = "user_id=" + user_id;
	//alert(sendDeleteUrl + '?' + this.sentData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sendDeleteUrl,
	   {success: function(o){
			response = o.responseText;
			//mark it saved
			//alert(response);
			refreshDataSource();
		},
		failure: function(){
			   //
			   //alert("failure");
			},
	   after: function(){
		   //
		},
	   scope: this}, this.sentData);
}
function loginUser(event) {
	var element = event.currentTarget;
	event.preventDefault();
	var user_id = element.id.replace("login_user_", "");
	
	userLogin(user_id);
}
var userLogin = function (user_id) {
	//alert("delete user");

	var url = "../../api/masterlogin";
	var formData = new FormData();
	formData.append("user_id", user_id);
	
	var r = new XMLHttpRequest();
	r.open("POST", url, true);
	r.onreadystatechange = function () {
	  if (r.readyState != 4 || r.status != 200) {
		return;
	  } else {
		var response = r.responseText;
		var data = JSON.parse(response);
		if (data.success) {
			document.location.href = "../../v<?php echo $version_number; ?>.php?session_id=" + data.session_id + "&old_session_id=<?php echo $_SESSION["user"]; ?>&masterlogin=";
		}
	  }
	};
	r.send(formData);
}
var init = function() {
	getUsers();
}
window.addEventListener("load", function load(event){
	init();
});
</script>
</body>
</html>