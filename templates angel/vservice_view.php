<?php
include("../api/manage_session.php");
session_write_close();

include ("../api/connection.php");

$sql = "SELECT DISTINCT REPLACE(REPLACE(REPLACE(REPLACE(`TYPE`, 'DR ', ''), '(', ''), ')', ''), 'DR-', '') doctor_type 
FROM glauber.card
WHERE `TYPE` LIKE 'DR%'
AND (`TYPE` LIKE '&-%' OR `TYPE` LIKE '%(%')
ORDER BY REPLACE(REPLACE(REPLACE(REPLACE(`TYPE`, 'DR ', ''), '(', ''), ')', ''), 'DR-', '')";

$db = getConnection();
$arrOptions = array();	
try {
	$stmt = $db->query($sql);
	$doctor_types = $stmt->fetchAll(PDO::FETCH_OBJ);
	//print_r($doctor_types);	
	foreach($doctor_types as $doctor_type) {
		$menu_item = '<option value="' . $doctor_type->doctor_type . '">' . $doctor_type->doctor_type . '</option>';
		$arrOptions[] = $menu_item;
	}

} catch(PDOException $e) {
	$error = array("error nav"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
$db = null;
?>
<div class="gridster vservice" id="gridster_vservice" style="display:; border:0px solid green">
     <div style="background:url(img/glass.png) left top repeat-y; padding:5px; width:490px;-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; border:0px solid red" class="col-md-6">
        <span style="color:#FFFFFF; font-size:1.25em; font-weight:lighter; margin-left:3px;" id="vservice_holder">vServices</span>
        <div class="white_text">
            <ul>
                <li>
                   <span style="color:#FFFFFF; font-size:1.05em; font-weight:bold">Medical Groups</span>
                    <ul>
                        <li>
                        	<div style="float:right; padding-left:10px">
                            	<a href="uploads/bell_medical_services.pdf" target="_blank" title="Click to learn more about Bell Medical Group" style="font-size:11px; font-weight:normal; background:black; color:white;padding:2px; cursor:pointer">learn more...</a>
                            </div>
                            <div style="float:right; padding-left:10px">
                            	<a id="open_vservice_form" title="Click to email this Kase to Bell Medical Group" style="font-size:11px; font-weight:normal; background:black; color:white;padding:2px; cursor:pointer">Send Kase</a>
                            </div>
                            Bell Medical Group
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <div id="vservice_form_holder" style="display:none; padding:5px; border:0px solid black; width:350px; text-align:right;">
            <form id="vservice_send_form">
                <input type="hidden" name="case_id" value="<%=case_id %>" />
                <input type="hidden" name="emailit" value="y" />
                <table>
                    <tr>
                        <td align="left" style="" class="white_text">
                            <textarea name="specific_instructions" cols="45" rows="3"></textarea><br>
                            Specific Instructions
                        </td>
                    </tr>
                    <td align="left" style="" class="white_text">
                            <select>
                                <option value="">Select From List</option>
                                <?php echo implode("\r\n", $arrOptions); ?>
                            </select><br>
                            Doctor Type
                        </td>
                    </tr>
                    <tr>
                        <td align="left">
                            <input type="submit" name="submit" value="Send It" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>