<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
session_write_close();

//die(print_r($_SESSION));
include("connection.php");
$db = getConnection();

//see if there is a "data_source"_docs database
//lookup the customer name
$sql_customer = "SELECT *
FROM  `ikase`.`cse_customer` 
WHERE customer_id = :customer_id";

$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
$stmt->execute();
$customer = $stmt->fetchObject();

$stmt->closeCursor(); $stmt = null; $db = null;

$cus_name_first = $customer->cus_name_first;
$cus_name_last = $customer->cus_name_last;
$cus_email = $customer->cus_email;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Request Information - EAMS</title>
    <meta NAME="DESCRIPTION" CONTENT="Sample code for javascript form submit">
    <meta NAME="KEYWORDS" CONTENT="javascript form submit example">
</head>
<body >
<span style="font-style:italic; font-size:12pt; color:red">Please wait while logging on to the EAMS Site...</span>
<div class='Para' style="display:">
<script type="text/javascript">
        function submitform(){
			document.forms["requestor"].submit();
        }
        </script>
        <form id="requestor" action="https://eams.dwc.ca.gov/WebEnhancement/InformationCapture" method="post"><input name="" type="submit">
        <input id="uan" class="upperCase" type="hidden" name="UAN" size="60" maxlength="150" value="" >
        <input  id="firstname" class="upperCase required" type="text" name="requesterFirstName"
					size="40" maxlength="25" value="<?php echo $cus_name_first; ?>" >
		<input 	id="lastname" class="upperCase required" type="text" name="requesterLastName"
					size="40" maxlength="30" value="<?php echo $cus_name_last; ?>" >            
        <input id="email" type="text" name="email" size="60" maxlength="100" value="<?php echo $cus_email; ?>" class="required" >
        <select id='reason' name = 'reason' style="display:none"><option value=''>&nbsp;</option><option value='APPORTIONMENT'>APPORTIONMENT</option><option value='CASESEARCH' selected>CASE INFORMATION SEARCH</option><option value='CASEPARTICIPANTSEARCH'>CASE PARTICIPANT INFORMATION SEARCH</option><option value='POSTOFFER'>POST OFFER PRE-EMPLOYMENT SCREENING</option><option value='HEARING'>PREPARATION FOR HEARING/FILING</option></select>
        Search: <input type='text' name='query'>
        <a href="javascript:submitform()">Submit</a>
        </form>
</div>
<?php if ($cus_name_first!="" && $cus_name_last!="" && $cus_email!="" ) { ?>
<script type="text/javascript">
submitform();
</script>
<?php } ?>
</body>
</html>