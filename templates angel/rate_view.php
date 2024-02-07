<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("../api/manage_session.php");
session_write_close();

include("../api/connection.php");

$customer_id = $_SESSION['user_customer_id'];

$sql = "SELECT DISTINCT activity_category
FROM cse_activity
WHERE customer_id = :customer_id
AND activity_category != ''
ORDER BY activity_category";

try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$activity_categories =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$arrOptions = array();
$option = "<option value='' selected>Select from List of Activity Categories</option>";
$arrOptions[] = $option;

foreach($activity_categories as $category) {
	$option = "<option value='" . $category->activity_category . "'>" . $category->activity_category . "</option>";
	$arrOptions[] = $option;
}

$sql = "SELECT DISTINCT case_type `case_type` 
FROM cse_case
WHERE case_type != ''
AND case_type != 'WCAB'
AND customer_id = :customer_id
ORDER BY case_type ASC";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$types =  $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
		echo json_encode($error);
}

$arrTypes = array();
$option = "<option value=''>Select from List</option>
<option value='all'>All Cases</option>";
$arrTypes[] = $option;
$option = "<option value='WCAB'>WCAB</option>";
$arrTypes[] = $option;
foreach($types as $typ) {
	$case_type = $typ->case_type;
	//skip personal injury, using newpi
	if ($case_type=="Personal Injury") {
		continue;
	}
	
	$display_type = str_replace("_", " ", $case_type);
	if ($display_type=="NewPI") {
		$display_type = "personal injury";
	}
	$display_type = ucwords($display_type);
	if (substr($display_type, 0, 2) == "Wc") {
		$display_type = strtoupper($display_type);
	}
	
	$option = "<option value='" . $case_type . "'>" . $display_type . "</option>";
	
	$arrTypes[] = "" . $option;
}
?>
<div class="white_text" style="color:black">
	<input type="hidden" id="rate_id" value="<%= id %>" />
    <div>
    	<div style="float:right" class="white_text">
        	<span style="font-weight:bold">Case Type:</span> 
            <select id="case_type" name="case_type">
            	<?php echo implode("", $arrTypes); ?>
            </select>
        </div>
        <div class="white_text">
            <span style="font-weight:bold">Schedule:</span>&nbsp;
            <% if (id < 0) { %>
            <input type="text" id="rate_name" value="<%=rate_name %>" placeholder="Rate Name" />
            <% } else { %>
            <input type="hidden" id="rate_name" value="<%=rate_name %>" placeholder="Rate Name" />
            <span><%=rate_name %></span>
            <% } %>
        </div>
        <div class="white_text">
        	<span style="font-weight:bold">Description:</span>
            <br />
        
        <% if (id < 0) { %>
        <textarea id="rate_description" placeholder="Rate Description"><%=rate_description %></textarea>
        <% } else { %>
        <input type="hidden" id="rate_description" value="<%=rate_description %>" placeholder="Rate Description" />
        <span><%=rate_description %></span>
        <% } %>
        </div>
    </div>
    <div>
        <button class="btn btn-xs" id="new_fee_button">New Activity Standard Duration</button>
        &nbsp;<a id="show_fees" style="color:white; cursor:pointer" title="Click to show deleted items">Show Deleteds</a>
    </div>
</div>
<div class="white_text" style="font-style: italic; margin-bottom: 10px;">
Click below to edit or delete
</div>
<div>
	<form  id="fee_form">
    <div id="new_fee_holder" style="display:none">
    	<select id="new_fee" ><?php echo implode("", $arrOptions); ?></select>
        &nbsp; 
        <input type="number" id="new_minutes" min="0" step="1" style="width:60px" /> Minutes
        &nbsp;
        <button class="btn btn-xs btn-success" id="save_fee">Save</button>
    </div>
	<%=html %>
    </form>
</div>
<div id="rate_view_all_done"></div>
<script language="javascript">
$( "#rate_view_all_done" ).trigger( "click" );
</script>
