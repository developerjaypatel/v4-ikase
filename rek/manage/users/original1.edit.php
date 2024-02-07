<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php");

$cus_id = passed_var("cus_id");
$admin_client = passed_var("admin_client");

$query = "SELECT  `cus_id`,`eams_no`, `cus_name`, `cus_name_first`, `cus_name_middle`, `cus_name_last`, `cus_street`, `cus_city`, `cus_state`, `cus_zip`, `admin_client`, `password`
FROM tbl_customer 
WHERE cus_id = '" . $cus_id . "'";
$result = mysql_query($query, $r_link) or die("unable to run query<br />" .$sql . "<br>" .  mysql_error());

$cus_id = mysql_result($result, $int, "cus_id");
$eams_no = mysql_result($result, $int, "eams_no");
$cus_name = mysql_result($result, $int, "cus_name");
$cus_name_first = mysql_result($result, $int, "cus_name_first");
$cus_name_middle = mysql_result($result, $int, "cus_name_middle");
$cus_name_last = mysql_result($result, $int, "cus_name_last");
$cus_street = mysql_result($result, $int, "cus_street");
$cus_city = mysql_result($result, $int, "cus_city");
$cus_state = mysql_result($result, $int, "cus_state");
$cus_zip = mysql_result($result, $int, "cus_zip");
$password = mysql_result($result, $int, "password");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Edit Customer</title>
</head>

<body class="yui-skin-sam">
<form action="update.php" enctype="multipart/form-data" method="post">
<input type="hidden" value="<?php echo $cus_id; ?>" name="cus_id" id="cus_id" />
<input type="hidden" value="<?php echo $admin_client; ?>" name="admin_client" id="admin_client" />
<table width="570" border="0" align="center" cellpadding="2" cellspacing="0">
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><img src="../../images/jetfile_logo.png" alt="EAMS JetFile" width="354" height="45" /></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="index.php?admin_client=<?php echo $admin_client; ?>"> Customers</a></div>      
      <strong>Customer Information</strong></td>
    </tr>
  <tr>
    <td nowrap="nowrap"><strong>EAMS No:</strong></td>
    <td><label>
      <input name="eams_no" type="text" id="eams_no" value="<?php echo $eams_no; ?>" />
    </label></td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#EDEDED"><strong>Firm:</strong></td>
    <td bgcolor="#EDEDED"><input name="cus_name" type="text" value="<?php echo $cus_name; ?>" size="50" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><strong>First Name:</strong></td>
    <td><input name="cus_name_first" type="text" id="cus_name_first" value="<?php echo $cus_name_first; ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#EDEDED"><strong>Middle Name:</strong></td>
    <td bgcolor="#EDEDED"><input name="cus_name_middle" type="text" id="cus_name_middle" value="<?php echo $cus_middle; ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><strong>Last Name:</strong></td>
    <td><input name="cus_name_last" type="text" id="cus_name_last" value="<?php echo $cus_name_last; ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#EDEDED"><strong>Street</strong></td>
    <td bgcolor="#EDEDED"><input name="cus_street" type="text" id="cus_street" value="<?php echo $cus_street; ?>" size="100" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><strong>City, State Zip</strong></td>
    <td><input name="cus_city" type="text" id="cus_city" value="<?php echo $cus_city; ?>" />
      , 
      <input name="cus_state" type="text" id="cus_state" value="<?php echo $cus_state; ?>" size="2" />
      &nbsp;
      <input name="cus_zip" type="text" id="cus_zip" value="<?php echo $cus_zip; ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#EDEDED"><strong>Email:</strong></td>
    <td bgcolor="#EDEDED"><input name="cus_email" type="text" id="cus_email" value="<?php echo $cus_email; ?>" size="50" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><strong>Password</strong></td>
    <td><input name="password" type="text" id="password" value="<?php echo $password; ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap" bgcolor="#EDEDED">&nbsp;</td>
    <td bgcolor="#EDEDED"><label>
      <input type="submit" name="submit" id="submit" value="Save" />
    </label></td>
  </tr>
</table>
</form>
</body>
</html>