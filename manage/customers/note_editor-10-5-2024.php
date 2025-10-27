<?php
include ("../../text_editor/ed/functions.php");
include ("../../text_editor/ed/datacon.php");

$cus_id = passed_var("cus_id");
$note_id = passed_var("note_id");

if($_SERVER['SERVER_NAME']=="starlinkcms.com")
{
  $application = "StarLinkCMS";
  $application_url = "https://starlinkcms.com/";
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application = "iKase";
  $application_url = "https://v4.ikase.org/";
  $application_logo = "ikase_logo.png";
}

$query_notes = "SELECT cn . * FROM ikase.cse_notes cn WHERE cn.customer_id = 1033 AND notes_id = ?";
$row = DB::runOrDie($query_notes, [$note_id])->fetch();
// if (partie_array_type=='quick') { selected }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Notes</title>
</head>

<body class="yui-skin-sam">
<form action="update_note.php" method="post" enctype="multipart/form-data" name="note_editor_form" id="note_editor_form">
<input type="hidden" value="<?php echo $cus_id; ?>" name="cus_id" id="cus_id" />
<input type="hidden" value="<?php echo $user_id; ?>" name="user_id" id="user_id" />
<input type="hidden" value="<?php echo $suid; ?>" name="suid" id="suid" />
	<table width="700" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><img src="../../img/<?= $application_logo; ?>" alt="<?= $application ?>" height="90" /></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#CCCCCC"><div style="float:right"><a href="editor.php?suid=<?php echo $suid; ?>&cus_id=<?php echo $cus_id; ?>">Back to Customer</a></div>
            <strong>Notes</strong></td>
      </tr>
    </table>
    <table width="700" border="0" align="center" cellpadding="2" cellspacing="0">
	  <tr>
		<td colspan="3">
			<hr />
		</td>
	  </tr>
	  <tr>
		<th align="left" valign="top" scope="row" width="10%">Subject:</th>
		<td colspan="2" valign="top">
		<input type="text" name="subject" id="subject" style="width:99%" value="<?php echo $row->subject; ?>" /></td>
	  </tr>
	  <tr>
	  <th align="left" valign="top" scope="row">Type:</th>
		 <td align="left" valign="top" colspan="2">
			  <select name="type" id="type" style="width:100%">
				<option value="contract">Contract Note</option>
				<option value="general">General Note</option>
				<option value="bugs">Bugs</option>
			  </select>
		 </td>
	  </tr>
	  <tr>
		<th align="left" valign="top" scope="row">Note:</th>
		<td colspan="2" valign="top">
			<textarea name="note" id="note" style="width:99%" rows="5"><?php echo $row->note; ?></textarea>
		</td>
	  </tr>
	  <tr>
		<td colspan="3" valign="top" align="right">
			<input type="submit" name="submit" id="submit" value="Save" />
		</td>
	  </tr>
	</table>
</form>
<?php include ("yahoo.php"); ?>
</body>
</html>
