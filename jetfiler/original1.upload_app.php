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

if ($case_id == "" || $case_id < 0 || !is_numeric($case_id)) {
	die("");
}
if ($injury_id == "" || $injury_id < 0 || !is_numeric($injury_id)) {
	die("");
}
//for page 2, we need to have saved page 1
if ($jetfile_id == "" || $jetfile_id < 0 || !is_numeric($jetfile_id)) {
	die("<script language='javascript'>parent.location.href='app_1_2.php?case_id=" . $case_id . "&injury_id=" . $injury_id . "'</script>");
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
//get uploads
/*
$sql = "SELECT `document_id` id, `description` `name`, `document_filename` `filepath`
FROM cse_document doc
INNER JOIN cse_case_document ccd
ON doc.document_uuid = ccd.document_uuid
INNER JOIN cse_case ccase
ON ccd.case_uuid = ccase.case_uuid
WHERE `type` = 'App_for_ADJ' 
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
WHERE doc.`type` = 'App_for_ADJ' 
AND `document_filename` != ''
AND injury_id = :injury_id
AND `doc`.customer_id = :cus_id
AND `doc`.deleted = 'N'";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	//$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("injury_id", $injury_id);
	$stmt->bindParam("cus_id", $cus_id);
	$stmt->execute();
	$documents = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($documents));
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

$arrFiles = array();
$arrFilesID = array();
$number_files = count($documents);
if (count($documents) > 0) {
	foreach($documents as $document) {
		$name = $document->name;
		$filepath = $document->filepath;
		if ($filepath!="") {
			//check if uploaded or just part of ikase
			$actual_path = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $cus_id . "\\" . $case_id . "\\jetfiler\\" . $filepath;
			if (!file_exists($actual_path)) {
				$actual_path = "../uploads/" . $cus_id . "/" . $case_id . "/" . $filepath;
				
			} else {
				$actual_path = "../uploads/" . $cus_id . "/" . $case_id . "/jetfiler/" . $filepath;
			}
			$arrFiles[$name] = $actual_path;
			$arrFilesID[$name] = $document->id;
			
			//echo $name . " == " . $actual_path . "<br />";
			//die(print_r($arrFiles));
		}
	}
}
//die(print_r($arrFiles));
//echo "cnLT:" . count($arrFiles);

//minimum required
$minimum_files = 4;
if ($applicant_type=="L") {
	$minimum_files = 3;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>EAMS Jet File - Upload Documents</title>
<style type="text/css">
input {
	text-transform: uppercase;
}
.instructions {
	font-size:0.8em;
	font-style:italic;
}
</style>
<script type="text/javascript" src="../lib/jquery.1.10.2.js"></script>
<script type='text/javascript' src='../lib/mask.js'></script>
<script type='text/javascript' src='../lib/mask_date.js'></script>
<script type='text/javascript' src='../lib/moment.min.js'></script>
<script type='text/javascript' src='jetfile.js'></script>
<script type='text/javascript' src='../js/utilities.js'></script>
</head>
<body onload="init()">
<table border="0" cellpadding="2" cellspacing="0" align="center" width="980">
<tr>
    <td width="77"><img src="../img/ikase_logo_login.png" height="32" width="77"></td>
    <td align="center" colspan="6">
        <div style="float:right">
        	<em>As of <?php echo date("m/d/y g:iA"); ?></em>
        </div>
        <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">Application for Adjudication</span></td>
  </tr>
</table>
<hr />
<div id="document_list" style="position:absolute; left:700px; top:100px; background:white; border:1px solid black; padding:5px; display:none">
</div>
<form action="upload_file.php" method="post" enctype="multipart/form-data" name="form1" target="_self" id="form1">
	<input type="hidden" name="cus_id" value="<?php echo $cus_id; ?>" />
    <input type="hidden" name="injury_id" value="<?php echo $injury_id; ?>" />
	<input type="hidden" name="case_id" value="<?php echo $case_id; ?>" />
    <input type="hidden" name="jetfile_id" value="<?php echo $jetfile_id; ?>" />
    <input type="hidden" name="uploads" value="4" />
    <input type="hidden" name="form" value="App_for_ADJ" />
    <table width="980" border="0" align="center" cellpadding="3" cellspacing="0" style="border:#999999 1px solid;">
      <tr>
        <td colspan="10" align="center" class="pagetitle">
        	<div style="float:right; text-align:left">
                <span id="proceed_1" style="display:<?php if ($jetfile_id=="") { ?>none<?php } ?>">
                    <em>
                    <a href="app_1_2.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>">Page 1</a>&nbsp;|&nbsp;<a href="app_3_4.php?case_id=<?php echo $case_id; ?>&injury_id=<?php echo $injury_id; ?>">Page 2</a>
                    </em>        
                </span>
            </div>
        	Uploads Page
        </td>
      </tr>
      <tr>
        <td align="center" valign="top" class="nav_links"> 
            
            <table width="100%" border="0" cellspacing="0">
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px; padding-top:5px; font-family:Arial, Helvetica, sans-serif">
                  <?php if ($number_files==$minimum_files) { ?><div style="float:right; color:red; font-weight:bold">ALL DOCUMENTS HAVE BEEN UPLOADED</div><?php } ?>
                  <strong><span style="color:#999999">Upload Documents for APP for ADJ</span></strong></td>
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
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php echo $case_injury_start; ?>&nbsp;</td>
              </tr>
              <?php 
              //only for standard app, not for applicant lien claimant
              if ($applicant_type!="L") { ?>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px">
                  <strong> Venue Authorization:
					<?php if (isset($arrFiles["Venue Authorization"])) {
						echo "<span style='color:green'>&#10003;</span>";
                    }
                    ?></strong><br /></td>
              </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px">
                    <?php $required = "required";
                    if (isset($arrFiles["Venue Authorization"])) {
                        $required= ""; ?>
                        <div id="review_holder_1" style="float:right">
                            <a href="javascript:clearUpload('<?php echo $arrFilesID["Venue Authorization"]; ?>', 1, '1')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                          <a href="<?php echo $arrFiles["Venue Authorization"]; ?>" title="Click to review upload" target="_blank">Review Upload</a>
                        </div>
                    <?php } ?>
                    <span id="holder_1">
                  <input type="file" name="file_up_1" id="file_up_1" tabindex="0" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(1)" />
                  </span>
                  PDF only<span id="choose1_link" style="display:"> OR <a href="javascript:getDoc('1')">Choose From Kase Documents</a></span>
                  <input type="hidden" name="file_name_1" value="Venue Authorization" />
                  <input type="hidden" name="file_stored_1" id="file_stored_1" value="<?php echo $arrFiles["Venue Authorization"]; ?>" />
                  <span id="written1_link" style="display:none"></span></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> Fee Disclosure Statement:<?php if (isset($arrFiles["Fee Disclosure Statement"])) {
						echo "<span style='color:green'>&#10003;</span>";
                    }
                    ?></strong><br /></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px">
                  <?php 
                  $required = "required";
                  if (isset($arrFiles["Fee Disclosure Statement"])) {
                    $required = ""; ?>
                  <div style="float:right" id="review_holder_2">
                    <a href="javascript:clearUpload('<?php echo $arrFilesID["Fee Disclosure Statement"]; ?>', 2, '2')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                    <a href="<?php echo $arrFiles["Fee Disclosure Statement"]; ?>" title="Click to review upload" target="_blank">Review Upload</a>
                  </div>
                  <?php } ?>
                  <span id="holder_2">
                  <input type="file" name="file_up_2" id="file_up_2" tabindex="1" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(2)" /></span>
                  PDF only<span id="choose2_link" style="display:"> OR <a href="javascript:getDoc('2')">Choose From Kase Documents</a></span>
                  <input type="hidden" name="file_name_2" value="Fee Disclosure Statement" />
                  <input type="hidden" name="file_stored_2" id="file_stored_2" value="<?php echo $arrFiles["Fee Disclosure Statement"]; ?>" />
                  <span id="written2_link" style="display:none"></span>
                  </td>
                </tr>
                <?php } ?>
                <?php 
              //not for standard app, only for applicant lien claimant
              if ($applicant_type=="L") { ?>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> 10770.5 Verification:				
				  	<?php if (isset($arrFiles["10770.5 Verification"])) {
						echo "<span style='color:green'>&#10003;</span>";
                    }
                    ?></strong><br /></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><?php 
                    $required = "";
                    $link_text = "";
                    if (!isset($arrFiles["10770.5 Verification"])) {
                      $arrFiles["10770.5 Verification"] = "uploads/memo_07242012.pdf";
                      $link_text = "Default ";
                    }			  
                    if (isset($arrFiles["10770.5 Verification"])) {
                     ?>
                    <div style="float:right" id="review_holder_2"> <a href="javascript:clearUpload('<?php echo $arrFilesID["10770.5 Verification"]; ?>', 2, '2')" title="Click to clear upload">Clear <?php echo $link_text; ?>Upload</a>&nbsp;|&nbsp; <a href="<?php echo $arrFiles["10770.5 Verification"]; ?>" title="Click to review upload" target="_blank">Review <?php echo $link_text; ?>Upload</a></div>
                    <?php } else {
                        $arrFiles["10770.5 Verification"] = "";
                    }
                    ?>
                    <!--empty place holders for 1, let verification be 2-->
                    <input type="file" name="file_up_1" id="file_up_1" tabindex="0" style="display:none" />
                    <input name="file_name_1" type="hidden" id="file_name_1" value="" />
                    <input type="hidden" name="file_stored_1" value="" />
                    <span id="holder_2">
                    <input type="file" name="file_up_2" id="file_up_2" tabindex="1" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(2)" /></span>
                    PDF only<span id="choose2_link" style="display:"> OR <a href="javascript:getDoc('2')">Choose From Kase Documents</a></span><?php if ($link_text=="Default "){ ?><span id="verification_instructions" class="instructions">Click Upload to save Default</span><?php } ?>
                    <input name="file_name_2" type="hidden" id="file_name_2" value="10770.5 Verification" />
                    <input type="hidden" name="file_stored_2" value="<?php echo $arrFiles["10770.5 Verification"]; ?>" />
                    <span id="written2_link" style="display:none"></span>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> Proof Of Service:<?php if (isset($arrFiles["Proof Of Service"])) {
						echo "<span style='color:green'>&#10003;</span>";
                    }
                    ?></strong><br /></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px">
                  <?php 
                  $required = "required";
                  if (isset($arrFiles["Proof Of Service"])) {
                    $required = ""; 
				  }
				  ?>
                        <div id="review_holder_pos" style="float:right; display:<?php if (!isset($arrFiles["Proof Of Service"])) { ?>none<?php } ?>">
                            <a href="javascript:clearUpload('<?php echo $arrFilesID["Proof Of Service"]; ?>', 3, 'pos')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                        <a href="<?php echo $arrFiles["Proof Of Service"]; ?>" title="Click to review upload" target="_blank">Review Upload</a></div>
                      
                      <span id="holder_3">
                      <input type="file" name="file_up_3" id="file_up_3" tabindex="2" style="color:#000000" class="<?php echo $required; ?>" onchange="checkPDF(3)" /></span>
                      PDF only<span id="choose3_link" style="display:"> OR <a href="javascript:getDoc('3')">Choose From Kase Documents</a></span>
                      <input type="hidden" name="file_name_3" value="Proof Of Service" />
                      <input type="hidden" name="file_stored_3" id="file_stored_3" value="<?php echo $arrFiles["Proof Of Service"]; ?>" />
                  <span id="writepos_holder">OR <a href="javascript:writePOS();" title="Click to generate a Proof of Service pdf">Generate POS</a></span> <span id="writtenpos_link" style="display:none"></span></p>
                  <p>POS Description:<br /><textarea name="pos_description" id="pos_description" cols="65" rows="3">Application for Adjudication; compliance with Labor Code Section 4906(g); Fee Disclosure Statement; Venue Authorization</textarea>
                  </td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><strong> 4906 g:<?php if (isset($arrFiles["4906 g"])) {
						echo "<span style='color:green'>&#10003;</span>";
                    }
                    ?></strong><br /></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px">
                  <div style="background:#FCF; color:black; padding:10px; font-size:1.1em; margin-bottom:10px" id="4906_announce">
                        <div style="float:right; background:black;; padding:2px">
                            <a href="../jetfiler/pdf/4906h.pdf" target="_blank" style=" color:white">Download 4906H Form</a>
                        </div>
                        <p>FROM EAMS 2/13/2019  -RE: <strong>4906G instead of 4906H</strong></p>
                        <p>You  can use the document title 4906G, but name the actual document 4906H.
                        Or,  you can wait until the case is assigned, and submit the 4906h as an  unstructured document.</p>
                        <p> We  are working to correct this issue, so the above will work for now.</p>
                    </div>
                  <?php 
                  $required = "required";
                  if (isset($arrFiles["4906 g"])) {
                    $required = ""; ?>
                        <div style="float:right" id="review_holder_4">
                            <a href="javascript:clearUpload('<?php echo $arrFilesID["4906 g"]; ?>', 4, '4')" title="Click to clear upload">Clear Upload</a>&nbsp;|&nbsp;
                          <a href="<?php echo $arrFiles["4906 g"]; ?>" title="Click to review upload" target="_blank">Review Upload</a>
                        </div>
                    <?php } ?>
                  <input type="file" name="file_up_4" id="file_up_4" tabindex="3" style="color:#000000" class="<?php echo $required; ?>" />
                  PDF only<span id="choose4_link" style="display:"> OR <a href="javascript:getDoc('4')">Choose From Kase Documents</a></span>
                  <input type="hidden" name="file_name_4" value="4906 g" />
                  <input type="hidden" name="file_stored_4" id="file_stored_4" value="<?php echo $arrFiles["4906 g"]; ?>" />
                  <span id="written4_link" style="display:none"></span>
                  </td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><hr /></td>
                </tr>
                <tr>
                  <td align="left" valign="top" style="padding-left:17px; padding:3px"><label>
                    <input type="submit" name="submit" id="submit" class="submit" value="Upload" tabindex="4" disabled="disabled" />
                    <span style="background:#CCFFFF" class="required_guide">Please fill out all Required Fields</span></label></td>
                </tr>
            </table>      
        </td>
      </tr>
    </table>
</form>

<script language="javascript">
var selectDocument = function(file_number, document_id, document_name) {
	$("#file_up_" + file_number).removeClass("required");
	$("#written" + file_number + "_link").html(document_name);
	$("#file_stored_" + file_number).val(document_id);
	$("#document_list").fadeOut();
	$("#choose" + file_number + "_link").fadeOut(function() {
		$("#written" + file_number + "_link").fadeIn();
		$("#file_up_" + file_number).css("background", "");
		$("#file_up_" + file_number).css("border", "1px solid green");
	});
	enableSave();
}
var getDoc = function(file_number) {
	var pdfURL = "../api/documents/<?php echo $case_id; ?>";
	
	var url = pdfURL;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			var arrLength = data.length;
			var arrDocs = [];
			for(var i =0; i < arrLength; i++) {
				var thedata = data[i];
				arrDocs.push("<div style='padding-bottom:5px;'><input type='button' value='Select' onclick='selectDocument(" + file_number + "," + thedata.document_id + ", \"" + thedata.document_name + "\")'  /><span style='padding-left:5px'>" + thedata.document_name + "&nbsp;(" + thedata.type + ")</span></div>");
			}
			$("#document_list").html("<div style='width:100%; text-align:right'><a href='javascript:closeDocumentList()'>&times;</a><div style='float:left; font-weight:bold;'>Select a Document from List</div></div><div style='margin-top:5px'>" + arrDocs.join("\r\n") + "</div>");
			$("#document_list").fadeIn();
		}
	});
}
var closeDocumentList = function() {
	$("#document_list").fadeOut();
	$("#document_list").html("");
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
			response = data.trim();
			//save the file as document
			saveDocument("App_for_ADJ", response);
			
			setDisplayStyle("writepos_holder", "display", "none");
			setDisplayStyle("pos_review_holder", "display", "none");
			var writtenpos_link = document.getElementById("writtenpos_link");
			writtenpos_link.innerHTML = "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='../uploads/<?php echo $cus_id; ?>/<?php echo $case_id; ?>/jetfiler/" + response + "' title='Click to review generated POS' target='_blank'>Review Generated POS</a>&nbsp;Click the Upload Button to finish";
			var file_stored = document.getElementById("file_stored_3");
			file_stored.value = response;
			//show the link
			setDisplayStyle("writtenpos_link", "display", "");
			//done
			//alert("POS created");
			$("#file_up_3").removeClass("required");
			enableSave();
		}
	});
}
var saveDocument = function(document_type, document_path) {
	var mysentData = "case_id=<?php echo $case_id; ?>&case_uuid=<?php echo $kase->uuid; ?>&injury_id=<?php echo $injury_id; ?>";
	mysentData += "&type=" + document_type + "&document_filename=" + document_path + "&parent_document_uuid=&document_name=Proof Of Service";
	mysentData += "&document_date=" + moment().format("YYYY-MM-DD HH:mm:ss") + "&document_extension=pdf&description=Proof of Service&description_html=&thumbnail_folder=&verified=Y";
	
	var url = "../api/documents/add";
	$.ajax({
		url:url,
		type:'POST',
		data: mysentData,
		dataType:"json",
		success:function (data) {
			if (data.success=="true") {
				var writtenpos_link = document.getElementById("writtenpos_link");
				writtenpos_link.innerHTML += "&nbsp;&#10003;";
				
				setTimeout(function() {
					var thelink = writtenpos_link.innerHTML;
					thelink = thelink.replace("&nbsp;✓ ", "");
					writtenpos_link.innerHTML = thelink;
				}, 2500);
			}
		}
	});
}
var saveAs = function(upload_name) {
	var saveUrl = "resave_upload.php";
	var mysentData = "case_id=<?php echo $case_id; ?>&cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>&upload_name=" + upload_name;
	//alert(mysentData);
	if (mysentData!='') {	
		//logEvent("about to send request");
		var request = YAHOO.util.Connect.asyncRequest('POST', saveUrl,
		   {success: function(o){
				response = o.responseText;
				alert(response);
				document.location.href = "upload_app_for_adj.php?case_id=<?php echo $case_id; ?>&cus_id=<?php echo $cus_id; ?>&suid=<?php echo $suid; ?>";
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
var clearPOS = function(upload_id, file_number) {
	clearUpload(upload_id, file_number, "pos");
}
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
				setDisplayStyle("write" + upload_type + "_holder", "display", "");
				setDisplayStyle( "review_holder_" + upload_type, "display", "");
				var writtenpos_link = document.getElementById("written" + upload_type + "_link");
				
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
var setAction = function(destination, getname, getvariable) {
	var theaction = "";
	var cartform = document.getElementById("cartform");
	if (destination=="upload") {
		theaction = "http://localhost/jetfile/card_form.html?cus_id=<?php echo $cus_id; ?>";
	} else {
		theaction = String(destination) + ".php";
		//alert(theaction);
		//return;
		if (typeof getname != "undefined") {
			theaction += "?" + getname + "=" + getvariable + "&suid=<?php echo $suid; ?>";
		} else {
			theaction += "?suid=<?php echo $suid; ?>";
		}
		theaction += "&case_id=<?php echo $case_id; ?>";
	}
	if (theaction!="") {	
		cartform.action = theaction;
		cartform.submit();
	}
	return;
}
function clearFileInputField(tagId) {
    document.getElementById(tagId).innerHTML = 
                    document.getElementById(tagId).innerHTML;
}
var init = function() {
	initMask();
	
	var elements = $('.required');

	elements.on("change", enableSave);
	elements.on("keyup", releaseMe);
}
function initMask(){
	enableSave();
}
$(document).ready(function() {
	init();
});
</script>
</body>
</html>
