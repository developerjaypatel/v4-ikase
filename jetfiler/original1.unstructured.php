<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("strict-transport-security: max-age=600");
header('X-Frame-Options: SAMEORIGIN');
header("X-XSS-Protection: 1; mode=block");

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("../api/manage_session.php");
include("../api/connection.php");

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v2.ikase.org" . $_SERVER['REQUEST_URI']);
}

if ($_SESSION['user_customer_id']=="" || !isset($_SESSION['user_customer_id'])) {
	//die(print_r($_SESSION));
	//header("location:../index.php");
	die("<script language='javascript'>parent.location.href='../index.php'</script>");
}

session_write_close();

$cus_id = $_SESSION['user_customer_id'];
$case_id = passed_var("case_id", "GET");
$injury_id = passed_var("injury_id", "GET");
$jetfile_id = passed_var("jetfile_id", "GET");
$unstruc_id = passed_var("unstruc_id", "GET");

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("<script language='javascript'>window.close()</script>");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

include("jetfile_kase.php");

$first = $kase->first_name;
$last = $kase->last_name;

$master_case_number = $kase->adj_number;
$companion_case_number = "";
$case_type = "";
$document_type = "";
$document_title = "";
$author = "";
$document_date = date("m/d/Y");
$filepath = "";
$unstruc_number = "1";

if (isset($_GET["unstruc_number"])) {
	$unstruc_number = passed_var("unstruc_number", "get");
}

//die(print_r($kase));
//if ($jetfile_id!="") {
	if ($kase->unstruc_info!="") {
		$unstruc_info = json_decode($kase->unstruc_info);
		//die(print_r($unstruc_info));
		if (is_array($unstruc_info)) {
			//die(print_r($unstruc_info));
			//if (is_object($unstruc_info->pageunstruc)) {
			foreach($unstruc_info as $pageunstruc) {
				//die(print_r( $pageunstruc));
				//$arrLength = count($unstruc_info->pageunstruc);
				//$pageunstruc = $unstruc_info->pageunstruc;

				//for($intCounter = 0; $intCounter < $arrLength; $intCounter++) {
					
					if ($unstruc_number == ($pageunstruc->unstruc_number + 1)) {
						$master_case_number = $pageunstruc->data->master_case_number;
						$companion_case_number = $pageunstruc->data->companion_cases_input;
						//$exempt = mysql_result($result, 0, "exempt");
						//$case_type = mysql_result($result, 0, "case_type");
						$case_type = "ADJ";
						$document_type = $pageunstruc->data->document_type;
						$document_title = $pageunstruc->data->document_title;
						$author = $pageunstruc->data->author;
						$document_date = $pageunstruc->data->document_date;
						
						
						//echo $master_case_number . " - " . $unstruc_id . " -> title" . $document_title . " -> type" . $document_type . "<br />";
						
						if ($document_date=="" || $document_date=="0000-00-00") {
							$document_date = date("m/d/Y");
						} else {
							$document_date = date("m/d/Y", strtotime($document_date));
						}
						$filepath = $pageunstruc->data->filepath;
						
						//done
						break;
					}
				//}
			}
		}
	}
//}

$arrTitles = array();
$selected = "";
if ($document_title=="") {
	$selected = " selected";
}
$arrTitles[] = "<option value=''" . $selected . ">Select from List</option>";

//get the document titles
$sql = "SELECT *
FROM `ikase`.cse_document_titles
WHERE `active` = 'Y'
ORDER BY document_title";

$arrDocTypes = array();
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$titles = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

foreach($titles as $title) {
	$option_document_title = $title->document_title;
	$option_document_type = $title->document_type;
	
	$arrDocTypes[$option_document_title] = $option_document_type;
	
	$selected = "";
	if ($option_document_title == $document_title) {
		$selected = " selected";
	}
	$arrTitles[] = "<option value='" . $option_document_title . "'" . $selected . ">" . $option_document_title . "</option>";
}
$exempt = "Y";

$thedob = $birth_date;
$adj_number = $kase->adj_number;
$case_injury_start = $kase->start_date;
$case_injury_end = $kase->end_date;
if ($case_injury_start!="0000-00-00") {
	$case_injury_start = date("m/d/Y", strtotime($case_injury_start));
	if ($case_injury_end!="0000-00-00") {
		$case_injury_start .= "-" . date("m/d/Y", strtotime($case_injury_end)) . " CT";
	}
} else {
	$case_injury_start = "";
}
$jetfile_id = $kase->jetfile_id;

if ($author=="") {
	$author = $customer->cus_name_first . " " . $customer->cus_name_last;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EAMS Jet File - New Case - Unstructured Form</title>
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<style>
input {
	text-transform: uppercase;
}
</style>
<body>
<?php
if ($author=="") {
	$author = $cus_name_first . " " . $cus_name_last;
}
?>
<form action="unstruc_form_update.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
    <input type="hidden" name="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="unstruc_id" value="<?php echo $unstruc_id; ?>" />
    <input type="hidden" name="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="jetfile_case_id" id="jetfile_case_id" value="<?php echo $jetfile_case_id; ?>" />
    <input type="hidden" name="dob" id="dob" value="<?php echo $thedob; ?>" />
    <input type="hidden" name="ssn" id="ssn" value="<?php echo str_replace("-", "", $social_sec); ?>" />
    <input type="hidden" name="case_injury_start" id="case_injury_start" value="<?php echo $kase->start_date; ?>" />
    <input type="hidden" name="case_injury_end" id="case_injury_end" value="<?php echo $kase->end_date; ?>" />
    <input type="hidden" name="page" value="unstruc" />
    <input type="hidden" name="unstruc_number" value="<?php echo $unstruc_number; ?>" />
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0">
      <tr>
        <td colspan="2" align="center" class="pagetitle">Unstructured Document Form</td>
      </tr>
      <tr>
        <td colspan="2" align="left"><?php if ($case_id!="") { ?>
        <strong>Case ID: <span style="padding-left:17px; padding:3px"><?php echo $case_id; ?></span><br />
        Applicant Name:<span style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?></span><br />
        DOI:<span style="padding-left:17px; padding:3px"><?php echo $case_injury_start; ?></span></strong>
        <?php } ?>
        <br />
        <strong>ADJ Number:</strong> 
        <?php 
		$adj_number = str_replace("Pending", "", $adj_number);
		$style = "none";
		$type = "text";
		$otherstyle="";
		if ($adj_number!="") {
			$style = "";
			$otherstyle="none";
			$type = "hidden";
		}
		?>
        <input name="adj_number" type="<?php echo $type; ?>" class="required nospecial" id="adj_number" value="<?php echo $adj_number; ?>" />
        <span class="instructions"style="display:<?php echo $otherstyle; ?>">ADJ + numbers only</span><span id="adj_number_show" style="display:<?php echo $style; ?>"><?php echo $adj_number; ?></span></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><hr color="#000000" /></td>
      </tr>
		<tr>
			<td width="151">Master Case Number:</td>
			<td width="817">
            	<div style="float:right">
                	Case Type: ADJ
                </div>
                <input type="text" id="master_case_number" name="master_case_number" value="<?php echo $master_case_number; ?>" /> 
                * required
            </td>
		</tr>
		<tr>
		  <td align="left" valign="top">Companion Cases:</td>
		  <td align="left" valign="top">
          	<input type="text" id="companion_case_number" name="companion_case_number" value="" onkeyup="checkEnter(event)" />&nbsp;<a href="javascript:addCase()">add</a>
            <div id="companion_cases_holder">
            <?php
			if ($companion_case_number!="") {
				echo str_replace("|", "<br />", $companion_case_number);
			}
			?>
            </div>
            <input type="hidden" id="companion_cases_input" name="companion_cases_input" value="<?php echo $companion_case_number; ?>" />
          </td>
	  </tr>
		<tr>
		  <td>
          	Document Title:
          </td>
		  <td>
            <div style="float:right">
    	        	<?php if ($document_type != "") { ?>
                	Document Type:
	                <?php } ?>
                    <span id="document_type_span"><?php echo $document_type; ?></span>
                    <select name="document_type" id="document_type" style="display:none">
                        <option value="" <?php if ($document_type=="") { echo "selected"; } ?>>Select from List</option>
                        <option value="IBR" <?php if ($document_type=="IBR") { echo "selected"; } ?>>IBR</option>
                        <option value="IMR" <?php if ($document_type=="IMR") { echo "selected"; } ?>>IMR</option>
                        <option value="LEGAL DOCS" <?php if ($document_type=="LEGAL DOCS") { echo "selected"; } ?>>Legal Docs</option>
                        <option value="LIENS AND BILLS" <?php if ($document_type=="LIENS AND BILLS") { echo "selected"; } ?>>Liens and Bills</option>
                        <option value="MEDICAL DOCS" <?php if ($document_type=="MEDICAL DOCS") { echo "selected"; } ?>>Medical Docs</option>
                        <option value="MISC" <?php if ($document_type=="MISC") { echo "selected"; } ?>>MISC</option>
                    </select>
            </div>
            <select name="document_title" id="document_title" onchange="setDocumentType(this)">
                <?php echo implode("\r\n", $arrTitles); ?>
            </select>
          * required </td>
	  </tr>
		<tr>
		  <td>Author:</td>
		  <td>
          	<div style="float:right">
            	Document Date: <input type="text" id="document_date" name="document_date" value="<?php echo $document_date; ?>" autocomplete="off" onkeyup="mask(this, mdate);" onblur="mask(this, mdate);" placeholder="mm/dd/yyyy" />
            </div>
            <input type="text" id="author" name="author" value="<?php echo $author; ?>" />
          </td>
	  </tr>
      <tr>
      	<td>File Upload</td>
        <td>
            <input name="file_up" type="file" />PDF only&nbsp;<?php if ($filepath!="") {
				echo "<br /><span id='upload_link_holder'><a href='../uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath . "' target='_blank'>" . $filepath . "</a> has been uploaded.</span>"; 
			}
			?>
            <input name="filepath" type="hidden" id="filepath" value="<?php echo $filepath; ?>" />
            <?php if ($filepath!="") { ?>
            <a href="javascript:clearUpload('unstruc')" title="Click to clear upload" id="clear_upload_link">Clear Upload</a>
            <?php } ?>
		</td>
      </tr>
      <tr style="display:none">
        <td>&nbsp;</td>
        <td><progress></progress></td>
      </tr>
      <tr>
        <td><input type="button" name="save_button" id="save_button" value="Save" onclick="submitForm()" /></td>
        <td>&nbsp;</td>
      </tr>
  </table>
</form>
<script language="javascript">
var submitForm = function(event) {
	if (document.getElementById("master_case_number") == "") {
		document.getElementById("master_case_number").focus();
		alert("Master Case Number Required");
		return;
	}
	if (document.getElementById("document_title") == "") {
		document.getElementById("document_title").focus();
		alert("Document Title Required");
		return;
	}
	//document.getElementById("form1").submit();
	savePage();
}
var savePage = function() {
	var submit_button = $("#save_button");
	//submit_button.prop("disabled", true);
	submit_button.val("Saving");
	//var formValues = $("#form1").serialize();
	var formData = new FormData($('form')[0]);
		
	var url = "../api/jetfile/save/unstructured";
	$.ajax({
		url:url,
		type:'POST',
        xhr: function() {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            if(myXhr.upload){ // Check if upload property exists
                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        //Ajax events
        /*
		beforeSend: beforeSendHandler,
        success: completeHandler,
        error: errorHandler,
        */
		// Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false,
		success:function (data) {
			submit_button.val("Saved !!");
			submit_button.prop("disabled", false);
			return;
			$("#jetfile_id").val(data.id);
			
			setTimeout(function() {
				submit_button.val("Save");
			}, 2500);
		}
	});
}
function progressHandlingFunction(e){
    if(e.lengthComputable){
        $('progress').attr({value:e.loaded,max:e.total});
    }
}
var setDocumentType = function(obj){
	var this_title = obj.value;
	var this_type = "";
	switch(this_title) {
		<?php foreach($arrDocTypes as $the_title=>$the_type) { ?>
			case "<?php echo $the_title; ?>":
				this_type = "<?php echo $the_type; ?>";
				break;
		<?php } ?>
	}
	document.getElementById("document_type").value = this_type;
	document.getElementById("document_type_span").innerHTML = "Document Type: " + this_type;
}
var checkEnter = function(e) {
	if(e.keyCode==13) {
		addCase();
		e.preventDefault();
	}
}
var clearUpload = function(upload_name) {
	var clearUrl = "clear_unstruc_upload.php";
	mysentData = "case_id=<?php echo $case_id; ?>&cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>&unstruc_id=<?php echo $unstruc_id; ?>&upload_name=" + upload_name;
	//alert(mysentData);
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', clearUrl,
		   {success: function(o){
				response = o.responseText;
				document.getElementById("clear_upload_link").style.display = "none";
				document.getElementById("upload_link_holder").innerHTML = "cleared";
				document.getElementById("filepath").value = "";
			},
		   failure: function(){
			   //
			   //alert("failure");
			},
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}
var deleteCase = function(companion_case) {
	var stored_cases = companion_cases_input.value;
	var arrCases = [];
	if (stored_cases!="") {
		arrCases = stored_cases.split("|");
	}
	var index = arrCases.indexOf(companion_case);
	if (index > -1) {
		arrCases.splice(index, 1);
	}
	companion_cases_input.value = arrCases.join("|");
	
	var companion_cases_holder = document.getElementById("companion_cases_holder");
	companion_cases_holder.innerHTML = "";
	for(var i = 0; i < arrCases.length; i++) {
		companion_case = arrCases[i];
		companion_cases_holder.innerHTML = companion_cases_holder.innerHTML + "<div style='width:200px'><div style='float:right;'><a href='javascript:deleteCase(\"" + companion_case + "\")' style='color:red'>del</a></div>" + companion_case + "</div>";
	}
}
var addCase = function() {
	var companion_case = document.getElementById("companion_case_number").value;
	companion_case = companion_case.toUpperCase();
	//must start with "ADJ"
	if (companion_case.substring(0, 3) != "ADJ") {
		alert("The companion cases must start with `ADJ`");
		document.getElementById("companion_case_number").focus();
		return;
	}
	if (companion_case=="") {
		return;
	}
	var companion_cases_holder = document.getElementById("companion_cases_holder");
	
	companion_cases_holder.innerHTML = companion_cases_holder.innerHTML + "<div style='width:200px'><div style='float:right;'><a href='javascript:deleteCase(\"" + companion_case + "\")' style='color:red'>del</a></div>" + companion_case + "</div>";
	var stored_cases = companion_cases_input.value;
	var arrCases = [];
	if (stored_cases!="") {
		arrCases = stored_cases.split("|");
	}
	arrCases.push(companion_case);
	companion_cases_input.value = arrCases.join("|");
	
	document.getElementById("companion_case_number").value = "";
}
var init = function() {
	
}
</script>
</body>
</html>