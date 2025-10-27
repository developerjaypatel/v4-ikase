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

require_once('../shared/legacy_session.php');
include("../api/connection.php");
include("functions.php");

if($_SERVER["HTTPS"]=="off") {
	
	header("location:https://v4.ikase.org" . $_SERVER['REQUEST_URI']);
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

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("");
}
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("<script language='javascript'>parent.location.href='lien.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
}

include("jetfile_kase.php");

$person_id = $kase->applicant_id;
$first = $kase->first_name;
$middle = $kase->middle_name;
$last = $kase->last_name;
$social_sec = $kase->ssn;
$birth_date = $kase->dob;
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
/*
//get uploads
$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_case_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_case ccase
ON ccd.case_uuid = ccase.case_uuid
WHERE `type` = 'LIEN' 
AND `document_filename` != ''
AND case_id = :case_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";
*/
$sql = "SELECT `document_id` id, `document_name` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_injury_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_injury cinjury
ON ccd.injury_uuid = cinjury.injury_uuid
WHERE doc.`type` = 'LIEN' 
AND `document_filename` != ''
AND injury_id = :injury_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("cus_id", $cus_id);
	$stmt->execute();
	$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrFiles = array();
$arrFilesID = array();
$arrExists = array();
$number_files = count($documents);
if (count($documents) > 0) {
	foreach($documents as $document) {
		$name = $document->name;
		$filepath = $document->filepath;
		if ($filepath!="") {
			$arrExists[$name] = (file_exists("D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath));
			$arrFiles[$name] = "D:/uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath;
			$arrFilesID[$name] = $document->id;
		}
	}
}
//print_r($arrFiles);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>LIEN Uploader</title>
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='../lib/moment.min.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<body onload="init()">
<table width="980" border="0" cellpadding="3" cellspacing="0" bordercolor="#CCCCCC" align="center">
	<tr>
		<td align="left">
            <div style="width:100%">
                <div style="float:right">
                	<a href="lien.php?case_id=<?php echo $case_id; ?>&cus_id=<?php echo $cus_id; ?>&injury_id=<?php echo $injury_id; ?>">LIEN</a>
                    <a href="javascript:setAction('cases_preamble','case_id','<?php echo $case_id; ?>')" title="Click to review cases">Return to Cases Listing</a>
                </div>
            </div>
        </td>
    </tr>
</table>
<form action="upload_file_lien.php" method="post" enctype="multipart/form-data" name="form1" target="_self" id="form1">
<input type="hidden" name="cus_id" value="<?php echo $cus_id; ?>" />
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />
	<input type="hidden" name="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="injury_id" value="<?php echo $injury_id; ?>" />
    <input type="hidden" name="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="report_date" value="<?php echo $report_date; ?>" />
    <input type="hidden" name="uploads" value="3" />
    <input type="hidden" name="form" value="lien" />
<table width="980" border="0" align="center" cellpadding="3" cellspacing="0" style="border:#999999 1px solid;">
  <tr>
    <td align="center" valign="top"></td>
  </tr>
  <tr>
    <td align="center" valign="top" class="nav_links"> 
    	
        <table width="100%" border="0" cellspacing="0">
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px; padding-top:5px; font-family:Arial, Helvetica, sans-serif">
              <?php if ($number_files==3) { ?><div style="float:right; color:red; font-weight:bold">ALL REQUIRED DOCUMENTS HAVE BEEN UPLOADED</div>
              <?php } ?>
              <strong><span style="color:#999999">Upload Documents for LIEN</span></strong></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px; padding-right:20px"><hr color="#999999"/></td>
 			</tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <strong>Injured Worker Name:</strong></td>
  </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php echo $first . "&nbsp;". $last; ?>&nbsp;</td>
          </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <strong>Injury Date:</strong><br /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php echo $client_case_injury_start . $client_case_injury_end; ?>&nbsp;</td>
          </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> Proof Of Service :</strong><br /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <?php 
			  $required = "required";
			  if (isset($arrFiles["LIEN Proof Of Service"])) {
			  	$required = ""; ?>
                    <div id="review_holder_1" style="float:right">
                    	<a href="javascript:clearUpload('<?php echo $arrFilesID["LIEN Proof Of Service"]; ?>', 1, '1')" title="Click to clear upload">
                    	Clear Upload</a>&nbsp;|&nbsp;
	                    <a href="<?php echo $arrFiles["LIEN Proof Of Service"]; ?>" title="Click to review upload" target="_blank">Review Upload</a>                    </div>
                <?php } else {
					$arrFiles["LIEN Proof Of Service"] = "";
				}
				?>
              <span id="holder_1">
              <input type="file" name="file_up_1" id="file_up_1" tabindex="1" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(1)" />
              </span>
              PDF only
              <span id="writtenpos_link" style="display:none"></span>
              <input type="hidden" name="file_name_1" id="file_name_1" value="LIEN Proof Of Service" />
              <input type="hidden" name="file_stored_1" id="file_stored_1" value="<?php echo $arrFiles["LIEN Proof Of Service"]; ?>" />
              <span id="writepos_holder">OR <a href="javascript:writePOS();" title="Click to generate a Proof of Service pdf">Generate POS</a></span>
              <span id="writtenpos_link" style="display:none"></span>
              <p>POS Description:<br /><textarea name="pos_description" id="pos_description" cols="65" rows="3">Notice and Request for Allowance of Lien; 10770.5 Memo</textarea></p>
              </td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> 10770.5 Verification:</strong><br /></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">
              <?php 
			  $required = "required";
			  $link_text = "";
			  /*
			  if (!isset($arrFiles["10770.5 Verification"])) {
				  $arrFiles["10770.5 Verification"] = "D:/uploads/memo_07242012.pdf";
				  $link_text = "Default ";
			  }
			  */
			  if (isset($arrFiles["10770.5 Verification"])) {
			  	$required = ""; ?>
                    <div style="float:right" id="review_holder_2">
                    	<a href="javascript:clearUpload('<?php echo $arrFilesID["10770.5 Verification"]; ?>', 2, '2')" title="Click to clear upload">
                        Clear <?php echo $link_text; ?>Upload</a>&nbsp;|&nbsp;
	                    <a href="<?php echo $arrFiles["10770.5 Verification"]; ?>" title="Click to review upload" target="_blank">Review <?php echo $link_text; ?>Upload</a>                    </div>
                <?php } else {
					$arrFiles["10770.5 Verification"] = "";
				}
				?>
              <span id="holder_2">
              <input type="file" name="file_up_2" id="file_up_2" tabindex="3" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(2)" />
              </span>
              PDF only
              <span id="writtenverification_link" style="display:none"></span>
              <input name="file_name_2" type="hidden" id="file_name_2" value="10770.5 Verification" />
              <input type="hidden" name="file_stored_2" id="file_stored_2" value="<?php echo $arrFiles["10770.5 Verification"]; ?>" />
              <span id="writeverification_holder">OR <a href="javascript:writeVerification();" title="Click to generate a Verification pdf">Generate Verification</a></span>
              <span id="writtenverification_link" style="display:none"></span></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px">&nbsp;</td>
            </tr>
            <tr style="display:none">
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong>4903.8 Assignment</strong></td>
            </tr>
            <tr style="display:none">
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php 
			  $required = "";
			  $link_text = "";
			  if (isset($arrFiles["4903.8 Assignment"])) {
			  	$required = ""; ?>
                <div style="float:right" id="review_holder_3"> 
                <a href="javascript:clearUpload('<?php echo $arrFilesID["4903.8 Assignment"]; ?>', 3, '3')" title="Click to clear upload">
                Clear <?php echo $link_text; ?>Upload</a>&nbsp;|&nbsp; <a href="<?php echo $arrFiles["4903.8 Assignment"]; ?>" title="Click to review upload" target="_blank">Review <?php echo $link_text; ?>Upload</a></div>
                <?php } else {
					$arrFiles["4903.8 Assignment"] = "";
				}
				?>
                <span id="holder_3">
                <input type="file" name="file_up_3" id="file_up_3" tabindex="3" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(3)" />
                </span> PDF only
                <span id="writtenassignment_link" style="display:none"></span>
                <input name="file_name_3" type="hidden" id="file_name_3" value="4903.8 Assignment" />
                <input type="hidden" name="file_stored_3" id="file_stored_3" value="<?php echo $arrFiles["4903.8 Assignment"]; ?>" />
              <span id="writeassignment_holder" style="display:none">OR <a href="javascript:writeAssignment();" title="Click to generate an Assignment pdf">Generate Assignment</a></span></td>
            </tr>
            <tr style="display:none">
              <td align="left" valign="top" style="padding-left:17px; padding:3px">&nbsp;</td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong>4903.8 Declaration</strong></td>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php 
			  $required = "required";
			  $link_text = "";
			  if (isset($arrFiles["4903.8 Declaration"])) {
			  	$required = ""; ?>
                <div style="float:right" id="review_holder_4">
                	<a href="clearUpload('<?php echo $arrFilesID["4903.8 Declaration"]; ?>', 4, '4')" title="Click to clear upload">Clear <?php echo $link_text; ?>Upload</a>&nbsp;|&nbsp; <a href="<?php echo $arrFiles["4903.8 Declaration"]; ?>" title="Click to review upload" target="_blank">Review <?php echo $link_text; ?>Upload</a></div>
                <?php } else {
					$arrFiles["4903.8 Declaration"] = "";
				}
				?>
                <span id="holder_4">
                <input type="file" name="file_up_4" id="file_up_4" tabindex="3" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(4)" />
                </span> PDF only
                <span id="writtendeclaration_link" style="display:none"></span>
                <input name="file_name_4" type="hidden" id="file_name_4" value="4903.8 Declaration" />
                <input type="hidden" name="file_stored_4" id="file_stored_4" value="<?php echo $arrFiles["4903.8 Declaration"]; ?>" />
              <span id="writedeclaration_holder"  style="display:">OR <a href="javascript:writeDeclaration();" title="Click to generate an Declaration pdf">Generate Declaration</a></span>
            </tr>
            <tr>
              <td align="left" valign="top" style="padding-left:17px; padding:3px"><label>
                <input type="submit" name="submit" id="submit" class="submit" value="Upload" tabindex="4" disabled="disabled" />
              </label></td>
            </tr>
        </table>      
    </td>
  </tr>
</table>
<span id="feeback"></span>
</form>
<script language="javascript">
var clearUpload = function(upload_id, file_number, upload_type) {
	var clearUrl = "../api/document/delete";
	var mysentData = "id=" + upload_id;
	$.ajax({
		url:clearUrl,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="document marked as deleted") {
				setDisplayStyle( "review_holder_" + upload_type, "display", "");
				
				var document_blurb = "";
				switch(upload_type) {
					case "2":
						document_blurb = "verification";
						break;
					case "1":
						document_blurb = "pos";
						break;
					case "3":
						document_blurb = "assignment";
						break;
					case "4":
						document_blurb = "declaration";
						break;
				}
				
				var writtenpos_link = document.getElementById("written" + document_blurb + "_link");
				
				var file_stored = document.getElementById("file_stored_" + file_number);
				file_stored.value = "";
				//show the link
				setDisplayStyle("review_holder_" + upload_type, "display", "none");
				//done
				writtenpos_link.style.display = ""
				writtenpos_link.innerHTML = "cleared";
				$("#file_up_" + file_number).addClass("required");
				enableSave();
				
				setTimeout(function() {
					writtenpos_link.innerHTML = "";
				}, 2500);
			}
		}
	});
}
var writeVerification = function() {
	var url = "pdf_lienverification_cover_description.php";
	mysentData = "cus_id=<?php echo $cus_id; ?>&case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&nopublish=y";
	
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"text",
		success:function (data) {
			//save the file as document
			saveDocument("LIEN", data, "10770.5 Verification");
			
			response = data;
			
			setDisplayStyle("writeverification_holder", "display", "none");
			setDisplayStyle("review_holder_2", "display", "none");
			var writtenpos_link = document.getElementById("writtenverification_link");
			writtenpos_link.innerHTML = "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='D:/uploads/<?php echo $cus_id; ?>/<?php echo $case_id; ?>/jetfiler/" + response + "' title='Click to review generated Verification' target='_blank'>Review Generated Verification</a>&nbsp;Click the Upload Button to finish";
			var file_stored = document.getElementById("file_stored_2");
			file_stored.value = response;
			//show the link
			setDisplayStyle("writtenverification_link", "display", "");
			//done
			$("#file_up_2").removeClass("required");
			$("#file_up_2").css("background", "none");
			$("#file_up_2").css("border", "1px solid green");
			enableSave();
		}
	});
}
var writeDeclaration = function() {
	var url = "pdf_declaration.php";
	mysentData = "cus_id=<?php echo $cus_id; ?>&case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&nopublish=y";

	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"text",
		success:function (data) {
			//save the file as document
			saveDocument("lien", data, "4903.8 Declaration");
			
			response = data;
			
			setDisplayStyle("writedeclaration_holder", "display", "none");
			setDisplayStyle("review_holder_4", "display", "none");
			var writtenpos_link = document.getElementById("writtendeclaration_link");
			writtenpos_link.innerHTML = "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='D:/uploads/<?php echo $cus_id; ?>/<?php echo $case_id; ?>/jetfiler/" + response + "' title='Click to review generated Declaration' target='_blank'>Review Generated Declaration</a>&nbsp;Click the Upload Button to finish";
			var file_stored = document.getElementById("file_stored_4");
			file_stored.value = response;
			//show the link
			setDisplayStyle("writtendeclaration_link", "display", "");
			//done
			$("#file_up_4").removeClass("required");
			$("#file_up_4").css("background", "none");
			$("#file_up_4").css("border", "1px solid green");
			
			enableSave();
		}
	});
}
var writePOS = function() {
	var pdfURL = "pdf_pos_cover_mailing.php";
	
	var mysentData = "case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>&jetfile_id=<?php echo $jetfile_id; ?>&nopublish=y&type=_app";
	//get the description
	var pos_description = document.getElementById("pos_description");
	mysentData += "&pos_description=" + encodeURI(pos_description.value);
	
	var url = pdfURL;
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"text",
		success:function (data) {
			//save the file as document
			saveDocument("lien", data, "LIEN Proof Of Service");
			
			response = data;
			
			setDisplayStyle("writepos_holder", "display", "none");
			setDisplayStyle("review_holder_1", "display", "none");
			var writtenpos_link = document.getElementById("writtenpos_link");
			writtenpos_link.innerHTML = "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='D:/uploads/<?php echo $cus_id; ?>/<?php echo $case_id; ?>/jetfiler/" + response + "' title='Click to review generated POS' target='_blank'>Review Generated POS</a>&nbsp;Click the Upload Button to finish";
			var file_stored = document.getElementById("file_stored_1");
			file_stored.value = response;
			//show the link
			setDisplayStyle("writtenpos_link", "display", "");
			//done
			//alert("POS created");
			$("#file_up_1").removeClass("required");
			$("#file_up_1").css("background", "none");
			$("#file_up_1").css("border", "1px solid green");
			enableSave();
		}
	});
}
var setAction = function(destination, getname, getvariable) {
	var theaction = "";
	var cartform = Dom.get("cartform");
	if (destination=="upload") {
		theaction = "http://localhost/jetfile/card_form.html?cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>";
	} else {
		theaction = String(destination) + ".php";
		//alert(theaction);
		//return;
		if (typeof getname != "undefined") {
			theaction += "?" + getname + "=" + getvariable + "&suid=<?php echo $suid; ?>";
		} else {
			theaction += "?suid=<?php echo $suid; ?>";
		}
	}
	if (theaction!="") {	
		cartform.action = theaction;
		cartform.submit();
	}
	return;
}
var init = function() {
	initMask();
	
	var elements = $('.required');
	elements.on("blur", enableSave);
	elements.on("change", enableSave);
}
function initMask(){
	enableSave();
}
var saveDocument = function(document_type, document_path, document_name) {
	var mysentData = "case_id=<?php echo $case_id; ?>&case_uuid=<?php echo $kase->uuid; ?>&injury_id=<?php echo $injury_id; ?>";
	mysentData += "&type=" + document_type + "&document_filename=" + document_path + "&parent_document_uuid=&document_name=" + document_name;
	mysentData += "&document_date=" + moment().format("YYYY-MM-DD HH:mm:ss") + "&document_extension=pdf&description=" + document_name + "&description_html=&thumbnail_folder=&verified=Y";
	
	var url = "../api/documents/add";
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="true") {
				if (document_name=="LIEN Proof Of Service") {
					var writtenpos_link = document.getElementById("writtenpos_link");
				}
				if (document_name=="10770.5 Verification") {
					var writtenpos_link = document.getElementById("writtenverification_link");
				}
				if (document_name=="4903.8 Declaration") {
					var writtenpos_link = document.getElementById("writtendeclaration_link");
				}
				writtenpos_link.innerHTML += "&nbsp;&#10003;";
				
				setTimeout(function() {
					var thelink = writtenpos_link.innerHTML;
					thelink = thelink.replace("&nbsp;? ", "");
					writtenpos_link.innerHTML = thelink;
				}, 2500);
			}
		}
	});
}
</script>
</body>
</html>
