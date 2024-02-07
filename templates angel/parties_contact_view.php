<?php 
include("../api/manage_session.php");
include("../api/connection.php");

session_write_close();
$query = "SELECT * 
FROM `cse_partie_type` 
WHERE 1
AND blurb IN ('applicant', 'defense', 'plaintiff', 'defendant', 'carrier')
ORDER BY partie_type ASC";

try {
	$db = getConnection();
	$stmt = $db->prepare($query);
	$stmt->execute();
	$partie_types = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	$numbs = count($partie_types);
	//die(print_r($adhoc_settings));
	$stmt->closeCursor(); $stmt = null; $db = null;
} catch(PDOException $e) {
	$error = array("error1"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}
?>
<div style="float:right;border:0px solid red; display:none" id="partie_contact_preview_holder">
	<iframe src="" id="partie_contact_preview" frameborder="0" width="100%"></iframe>
</div>
<div class="gridster parties_contact" id="gridster_parties_contact" style="display:none; text-align:left; margin-top:13px; width:900px">
	<span class="form_title">Contact Parties (Open Case)</span>
    <ul>
    <?php 
    $row_counter = 1;
    $column_counter = 1;
		foreach($partie_types as $ptype) {
			$partie_type_id = $ptype->partie_type_id;
            $partie_type = $ptype->partie_type;
            $blurb = $ptype->blurb;
			$color = $ptype->color;	
    ?>
        <%
        %>
        <li id="partie_type_nameGrid" data-row="<?php echo $row_counter; ?>" data-col="<?php echo $column_counter; ?>" data-sizex="1" data-sizey="1" class="partie gridster_border gridster_holder" style="background:url(img/glass<?php echo $color; ?>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px">
        	<span style="font-size:1.4em"><?php echo $partie_type; ?></span>
            <div style="padding-top:5px">
                <div>
                	<div style="float:right">
                    	<a href="" id="download_envelope_<?php echo $blurb; ?>" class="white_text" title="Click to download generated envelopes" download></a>
                    </div>
                    <a title="Click to generate envelopes" class="compose_envelopes white_text" id="partie_envelope_<?php echo $blurb; ?>" style="cursor:pointer">
                        Envelopes
                    </a>
                </div>
                <div>
                	<div style="float:right">
                    	<a href="" id="download_letter_<?php echo $blurb; ?>_327" class="white_text" title="Click to download generated letters" download></a>
                    </div>
                    <a title="Click to generate letter" class="compose_letters  white_text" id="partie_letter_<?php echo $blurb; ?>_327" style="cursor:pointer">
                        Change of Address Letters
                    </a>
                </div>
            </div>
        </li>
     <?php 
        }	
        $column_counter++;        	
        if (($column_counter % 4) == 0) {
            //new row
            $row_counter++;
            $column_counter = 1;
        }
   ?>
    </ul>
</div>