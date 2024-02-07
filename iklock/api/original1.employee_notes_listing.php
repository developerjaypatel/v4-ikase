<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

include("manage_session.php");

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}

date_default_timezone_set('America/Los_Angeles');

include("connection.php");

$user_id = passed_var("user_id", "post");

include ("../classes/cls_notes.php");


$my_notes = new notes();
$resultnotes = $my_notes->fetch_employee_notes($user_id);
?>
<table border=1 cellspacing=0 cellpadding=2 align="center" bordercolor="#dddddd" width="100%" id="employee_notes_table">
    <form id="new_notes_form">
    <tr style="display:none" id="new_notes_row">
        <td align="left" valign="top" colspan="4">
            <textarea name="notesField" id="notesField" style="width:100%; height:75px" placeholder="Enter your Employee note here"></textarea>
            <div style="margin-top:5px">
                <button class="btn btn-sm btn-primary" id="save_new_notes">Save</button>
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            </div>
        </td>
    </tr>
    </form>
    <?php
    foreach($resultnotes as $x=>$note) {
						
        // Changing Background color for each alternate row
        if (($x%2)==0) { $bgcolor="#FFFFFF"; } else { $bgcolor="#C0C0C0"; }
    
        // Retreiving data and putting it in local variables for each row
        $notes_id = $note->notes_id; 
        $notes = $note->notes; 
        $time_stamp = $note->time_stamp; 
       	$time_stamp = date("m/d/Y g:ia", strtotime($time_stamp));
        $user_name = $note->user_name; 
        $callback_date = $note->callback_date; 
        $contact = $note->contact; 
        $status = $note->status; 
    ?>
        <tr bgcolor="<?php echo $bgcolor; ?>" class="initial_notes"> 
            <td align="left" valign="top" width="1%" nowrap="nowrap"> 
              <?php echo $time_stamp; ?>
            </td>
            <td align="left" valign="top" width="1%" nowrap="nowrap"> 
              <?php echo $user_name; ?>
            </td><td align="left" valign="top"> 
              <?php echo $notes; ?>
            </td>
            <td align="left" valign="top">
                <?php echo $status; ?>
            </td>
        </tr>
    <?php
    } // end foreach
    ?>
</table>