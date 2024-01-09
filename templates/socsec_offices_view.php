<?php 
require_once('../shared/legacy_session.php');
include("../api/connection.php");

session_write_close();
$query = "SELECT * 
FROM `ikase`.`cse_partie_type` 
WHERE 1
AND sort_order > 99
ORDER BY sort_order ASC";
//100 is the sort order for social security only

try {
	$partie_types = DB::select($query);
} catch(PDOException $e) {
    die(json_encode(["error1" => ["text" => $e->getMessage()]]));
}

$color = "_card_fade_7";
?>
<div class="gridster offices_new" id="gridster_offices_new" style="display:none; text-align:left; margin-top:13px; border:0px solid red; width:900px">
	<span class="form_title">Add Social Security Office to Case</span>
    <ul>
    <?php 
    $row_counter = 1;
    $column_counter = 1;
		foreach($partie_types as $ptype) {
			$partie_type_id = $ptype->partie_type_id;
            $partie_type = $ptype->partie_type;
            $blurb = $ptype->blurb;
			//$color = $ptype->color;
			
    ?>
        <%
        //check if the party type is in the offices object
        var indicator = "";
        if (offices.length > 0) {
            var this_partie = offices.findWhere({"partie_type":"<?php echo $partie_type; ?>"});
            if (typeof this_partie != "undefined") {
                indicator = "<a href='#parties/" + case_id + "/" + this_partie.get("corporation_id") + "/<?php echo $blurb; ?>' title='Click to review' style='background:black; padding-left:2px; padding-right:2px; padding-top:1px; padding-bottom:1px; color:white; font-weight:bold'>&#10003;</a>";
            }
        }
        %>
        <li id="partie_type_nameGrid" data-row="<?php echo $row_counter; ?>" data-col="<?php echo $column_counter; ?>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass<?php echo $color; ?>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px" onclick="document.location.href='#parties/<%= case_id %>/-1/<?php echo $blurb; ?>'">
        <div style="float:right"><%=indicator %></div>
        <a style="color:white; text-decoration:none; font-size:1.2em" href="#parties/<%= case_id %>/-1/<?php echo $blurb; ?>" title="Click to edit <?php echo $partie_type; ?>" id="<?php echo $blurb; ?>_partie_link"><?php echo $partie_type; ?></a>
        </li>
     <?php 
        }	
        $column_counter++;        	
        if (($column_counter % 4) == 0) { //FIXME: this definitely seems wrong
            //new row
            $row_counter++;
            $column_counter = 1;
        }
   ?>
    </ul>
</div>
