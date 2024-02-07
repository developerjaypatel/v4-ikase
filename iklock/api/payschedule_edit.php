<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}

date_default_timezone_set('America/Los_Angeles');

include("connection.php");

//$payschedule_id = passed_var("payschedule_id", "post");

$setup_id = "";
$pay_method = "";
$pay_schedule = "";
$first_dayField = "";

$first_endingField = "";
$first_monthField = "";
$first_days_actualField = "";

$second_endingField = "";
$second_monthField = "";
$second_days_actualField = "";
$apply_newField = "";

$verb = "EDIT";

//get any setup for this company
$query = "SELECT `setup_id` id, `payschedule` data 
FROM `setup`
WHERE customer_id = :customer_id
AND deleted = 'N'";

$customer_id = $_SESSION["user_customer_id"];
try {
	$sql = $query;
	$db = getConnection();
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$schedule = $stmt->fetchObject();
	
	if (is_object($schedule)) {
		$setup_id = $schedule->id;
		$arrData = json_decode($schedule->data);
		
		if (is_object($arrData)) {
			//assign values		
			$pay_method = $arrData->pay_methodField;
			$pay_schedule = $arrData->pay_scheduleField;
			$first_dayField = $arrData->first_dayField;
			
			$first_endingField = $arrData->first_endingField;
			$first_monthField = $arrData->first_monthField;
			$first_days_actualField = $arrData->first_days_actualField;
			
			$second_endingField = $arrData->second_endingField;
			$second_monthField = $arrData->second_monthField;
			$second_days_actualField = $arrData->second_days_actualField;
			
			if (isset($arrData->apply_newField)) {
				//it was checked
				$apply_newField =  $arrData->apply_newField;
			}
		}
	}	
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
?>
<form id="payschedule_form">
    <input type="hidden" name="setup_id" id="setup_id" value="<?php echo $setup_id; ?>" />
    <table width="900" border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#000000">
        <tr>
          <td colspan="2" align="left" valign="top" style="background:#000033" id="header_payschedule">
            <div style="float:right">
                <button class="btn btn-xs btn-primary" id="edit_payschedule">Edit</button>
                <button class="btn btn-xs btn-primary hide_me" id="save_payschedule">Save</button>
            </div>
            <span class="admintitle"><?PHP echo $verb; ?> PAY SCHEDULE</span>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" colspan="2">&nbsp;
            
          </td>
        </tr>
        <tr>
            <td align="left" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	<span style='font-weight:bold'>Pay</span>
            </td>
            <td colspan="2" align="left"><select name="pay_scheduleField" id="pay_scheduleField" class="payschedule edit_field hide_me">
              <option value="" <?php if ($pay_schedule=="") { echo "selected"; } ?>>Every ...</option>
              <option value="D" <?php if ($pay_schedule=="D") { echo "selected"; } ?>>Day</option>
              <option value="W" <?php if ($pay_schedule=="W") { echo "selected"; } ?>>Week</option>
              <option value="BW" <?php if ($pay_schedule=="BW") { echo "selected"; } ?>>Bi-Weekly</option>
              <option value="M" <?php if ($pay_schedule=="M") { echo "selected"; } ?>>Month</option>
              <option value="TM" <?php if ($pay_schedule=="TM") { echo "selected"; } ?>>Twice-a-Month</option>
            </select>
              <span id="pay_scheduleSpan" class="payschedule edit_span">
                <?php 
                    switch($pay_schedule) {
                        case "D":
                            $pay_schedule = " daily";
                            break;
                        case "W":
                            $pay_schedule = " weekly";
                            break;
                        case "BW":
                            $pay_schedule = " bi-weekly";
                            break;
                        case "M":
                            $pay_schedule = " monthly";
                            break;
                        case "TM":
                            $pay_schedule = " twice monthly";
                            break;
                    }
                    echo $pay_schedule; ?>
                </span>
              <select name="pay_methodField" id="pay_methodField" class="payschedule edit_field hide_me">
                <option value="" <?php if ($pay_method=="") { echo "selected"; } ?>>By ...</option>
                <option value="DD" <?php if ($pay_method=="DD") { echo "selected"; } ?>>Direct Deposit</option>
                <option value="CK" <?php if ($pay_method=="CK") { echo "selected"; } ?>>Check</option>
                <option value="CS" <?php if ($pay_method=="CS") { echo "selected"; } ?>>Cash</option>
              </select>
              <span id="pay_methodSpan" class="payschedule edit_span">
                <?php 
                    switch($pay_method) {
                        case "DD":
                            $pay_method = " via direct deposit";
                            break;
                        case "CK":
                            $pay_method = " by check";
                            break;
                        case "CS":
                            $pay_method = " cash";
                            break;
                    }
                    echo $pay_method; ?>
              </span>
            	<select id="first_dayField" name="first_dayField" class="payschedule edit_field hide_me">
                	<option value="">On the ...</option>
            	<?php
				$arrFirstDayOptions = array();
				for ($int = 1; $int < 32; $int++) {
					$extension = "th";
					$last_digit = substr($int, -1, 1);
					switch($last_digit) {
						case 1:
							$extension = "st";
							break;
						case 2:
							$extension = "nd";
							break;
						case 3:
							$extension = "rd";
					}
					
					$selected = "";
					if ($int==$first_dayField) {
						$selected = " selected";
					}
					$option = '<option value="' . $int . '"' . $selected  . '>' . $int . $extension . ' Day</option>
					';
					$arrFirstDayOptions[] = $option;
				}
				echo implode("", $arrFirstDayOptions);
				?>
                </select>
                <span id="first_daySpan" class="payschedule edit_span"></span>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	<span style='font-weight:bold'>First</span>
            </td>
            <td colspan="2" align="left">
            	<select id="first_endingField" name="first_endingField" class="payschedule first_ending edit_field hide_me">
                	<option value="">On the ...</option>
            	<?php
				$arrFirstDayOptions = array();
				for ($int = 1; $int < 32; $int++) {
					$extension = "th";
					$last_digit = substr($int, -1, 1);
					switch($last_digit) {
						case 1:
							$extension = "st";
							break;
						case 2:
							$extension = "nd";
							break;
						case 3:
							$extension = "rd";
					}
					
					$selected = "";
					if ($int==$first_endingField) {
						$selected = " selected";
					}
					$option = '<option value="' . $int . '"' . $selected  . '>' . $int . $extension . ' Day</option>
					';
					$arrFirstDayOptions[] = $option;
				}
				$option = '<option value="endofmonth">Last Day of Month</option>
				';
				$arrFirstDayOptions[] = $option;
				echo implode("", $arrFirstDayOptions);
				?>
                </select>
                <span id="first_endingSpan" class="payschedule edit_span"></span>
                <select id="first_monthField" name="first_monthField" class="payschedule first_ending edit_field hide_me">
                	<option value="" <?php if ($first_monthField=="") { echo "selected"; } ?>>of the ...</option>
                    <option value="same" <?php if ($first_monthField=="same") { echo "selected"; } ?>>Same Month</option>
                    <option value="previous" <?php if ($first_monthField=="previous") { echo "selected"; } ?>>Previous Month</option>
                    <option value="next" <?php if ($first_monthField=="next") { echo "selected"; } ?>>Next Month</option>
                </select>
                <span id="first_monthSpan" class="payschedule edit_span"></span>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	or
            </td>
            <td colspan="2" align="left">
            	<input type="number" id="first_days_actualField" name="first_days_actualField" style="width:50px" class="payschedule edit_field hide_me" value="<?php echo $first_days_actualField; ?>" />  <span class="payschedule edit_field hide_me">days before actual pay date</span>
                <span id="first_days_actualSpan" class="payschedule edit_span"><?php echo $first_days_actualField; ?></span>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	<span style='font-weight:bold'>Second</span>
            </td>
            <td colspan="2" align="left">
            	<select id="second_endingField" name="second_endingField" class="payschedule second_ending edit_field hide_me">
                	<option value="">On the ...</option>
            	<?php
				$arrFirstDayOptions = array();
				for ($int = 1; $int < 32; $int++) {
					$extension = "th";
					$last_digit = substr($int, -1, 1);
					switch($last_digit) {
						case 1:
							$extension = "st";
							break;
						case 2:
							$extension = "nd";
							break;
						case 3:
							$extension = "rd";
					}
					
					$selected = "";
					if ($int==$second_endingField) {
						$selected = " selected";
					}
					$option = '<option value="' . $int . '"' . $selected  . '>' . $int . $extension . ' Day</option>
					';
					$arrFirstDayOptions[] = $option;
				}
				echo implode("", $arrFirstDayOptions);
				?>
                </select>
                <span id="second_endingSpan" class="payschedule edit_span"></span>
                <select id="second_monthField" name="second_monthField" class="payschedule second_ending edit_field hide_me">
                	<option value="" <?php if ($second_monthField=="") { echo "selected"; } ?>>of the ...</option>
                    <option value="same" <?php if ($second_monthField=="same") { echo "selected"; } ?>>Same Month</option>
                    <option value="previous" <?php if ($second_monthField=="previous") { echo "selected"; } ?>>Previous Month</option>
                    <option value="next" <?php if ($second_monthField=="next") { echo "selected"; } ?>>Next Month</option>
                </select>
                <span id="second_monthSpan" class="payschedule edit_span"></span>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	or
            </td>
            <td colspan="2" align="left">
            	<input type="number" id="second_days_actualField" name="second_days_actualField" style="width:50px" class="payschedule edit_field hide_me" value="<?php echo $second_days_actualField; ?>" />  <span class="payschedule edit_field hide_me">days before actual pay date</span>
                <span id="second_days_actualSpan" class="payschedule edit_span"><?php echo $second_days_actualField; ?></span>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top" class="td_label" width="100px" nowrap="nowrap">
            	<span style='font-weight:bold'>Default</span>
            </td>
            <td colspan="2" align="left">
            	<input type="checkbox" id="apply_newField" name="apply_newField" class="payschedule" value="Y" <?php if ($apply_newField=="Y") { echo "checked"; } ?> />
                <span class="payschedule edit_field hide_me">&nbsp;Apply to New Employees</span>
            </td>
        </tr>
    </table>
</form>
