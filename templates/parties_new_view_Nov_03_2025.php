<?php 
require_once('../shared/legacy_session.php');
include("../api/connection.php");

session_write_close();
$query = "SELECT * 
FROM `ikase`.`cse_partie_type` 
WHERE 1
AND blurb != 'applicant'
AND sort_order < 100
ORDER BY partie_type ASC";
//100 is the sort order for social security only

try {
	$db = getConnection();
	$stmt = $db->prepare($query);
	//$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
	$stmt->execute();
	$partie_types = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    die(json_encode(["error1" => ["text" => $e->getMessage()]]));
}
?>
<div class="gridster parties_new" id="gridster_parties_new" style="display:none; text-align:left; margin-top:13px; border:0px solid red; width:900px">
	<div style="float:right">
    	<button id="search_qme" class="btn btn-sm btn-info" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</button>
        <!--
        <a style="background:#CFF; color:black; padding:2px; cursor:pointer" id="search_qme" title="Click to search the EAMS database of QME Medical Provider">Import QMEs from EAMS</a>
        -->
    </div>
	<span class="form_title">Add Partie to Case</span>
    <ul style="margin-top:20px">
    <?php 
    $row_counter = 1;
    $column_counter = 1;
	$current_letter = "";
    
	foreach($partie_types as $ptype) {
		$partie_type_id = $ptype->partie_type_id;
		$partie_type = $ptype->partie_type;
		$blurb = $ptype->blurb;
		//$color = $ptype->color;		
		$color = "_card_fade";
		
		$first_letter = substr($partie_type, 0, 1);	
		$display_type = $partie_type;
		if ($current_letter != $first_letter) {
			//show the letter
			$display_type = "<span style='font-weight:bold; color:red; background:black; padding:2px'>" . $first_letter . "</span>" . substr($partie_type, 1);
			$current_letter = $first_letter;
		}
    ?>
   
        <%
        //check if the party type is in the parties object
        
        var this_partie = parties.findWhere({"partie_type":"<?php echo $partie_type; ?>"});
       	var indicator = "";
        if (typeof this_partie == "undefined") {
        	indicator = "";
        } else {
	        indicator = "<a href='#parties/" + case_id + "/" + this_partie.get("corporation_id") + "/<?php echo $blurb; ?>' title='Click to review' style='background:black; padding-left:2px; padding-right:2px; padding-top:1px; padding-bottom:1px; color:white; font-weight:bold'>&#10003;</a>";
        }
        %>
        
        <li id="partie_type_nameGrid" data-row="<?php echo $row_counter; ?>" data-col="<?php echo $column_counter; ?>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass<?php echo $color; ?>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px" onclick="document.location.href='#parties/<%= case_id %>/-1/<?php echo $blurb; ?>'">
        <div style="float:right"><%=indicator %></div>
        <?php //echo $row_counter . "-" . $column_counter . "<br />"; ?>
        <a style="color:white; text-decoration:none; font-size:1.2em" href="#parties/<%= case_id %>/-1/<?php echo $blurb; ?>" title="Click to edit <?php echo $partie_type; ?>" id="<?php echo $blurb; ?>_partie_link"><?php echo $display_type; ?></a>
        
        </li>
       
     <?php 
	 	$column_counter++;        	
        if (($column_counter % 6) == 0) {
            //new row
            $row_counter++;
            $column_counter = 1;
			
        }
   }	
   ?>
    </ul>
</div>
<div id="offices_holder"></div>
<div id="parties_new_view_all_done"></div>
<script language="javascript">
$( "#parties_new_view_all_done" ).trigger( "click" );
</script>
