<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');	

require_once('../../shared/legacy_session.php');

if (!isset($_SESSION["user_customer_id"])) {
	die("no No NO");
}

date_default_timezone_set('America/Los_Angeles');

include("connection.php");

$user_id = passed_var("user_id", "post");
$check_id = passed_var("check_id", "post");

include ("../classes/cls_person.php");
include("../classes/cls_user.php");

if (!isset($_POST["shift"])) {
	$shift = "";
} else {
	$shift = passed_var("shift", "post");
}
if ($shift == "") {
	$shift = "1";
}
if (!isset($_POST["from_date"])) {
	$from_date = "";
} else {
	$from_date = passed_var("from_date", "post");
	$to_date = passed_var("to_date", "post");
}
if ($from_date=="") {
	//are we at the beginning of the month or the end
	$day_of_month =date("j");
	if ($day_of_month < 16) {
		$from_date = date("m") . "/01/" . date("Y");
		$to_date = date("m") . "/15/" . date("Y");
	} else {
		$from_date = date("m") . "/16/" . date("Y");
		$to_date = date("m") . "/" . date("t") . "/" . date("Y");
	}
}
//do not go beyond yesterday
$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-0, date("Y")));
if (strtotime($to_date) > strtotime($yesterday) ) {
	//restrict the to date
	$to_date = date("m/d/Y", strtotime($yesterday) );
}
$pay_date = date("Y-m-d");

$my_user = new systemuser();
$my_user->id = $user_id;
$my_user->fetch();

//reimbursments
$sql = "SELECT rei.*
FROM `reimbursment` rei
INNER JOIN `user_reimbursment` ure
ON rei.reimbursment_uuid = ure.reimbursment_uuid AND ure.deleted = 'N'
INNER JOIN `user` usr
ON ure.user_uuid = usr.user_uuid AND usr.user_id = :user_id
WHERE rei.customer_id = :customer_id";

try {
	$customer_id = $_SESSION["user_customer_id"];
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->bindParam("user_id", $user_id);
	$stmt->execute();
	$reimbursments = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	//die(print_r($reimbursments));

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

$user_type = $my_user->user_type;
$users_info = $my_user->users_info();
$pay_rate = $users_info->pay_rate;
$pay_period = $users_info->pay_period;
$pay_schedule = $users_info->pay_schedule;
$pay_method = $users_info->pay_method;

$regular_hours = "0";
$overtime_hours = "0";
$sick_hours = "0";
$vacation_hours = "0";
$holiday_hours = "0";
$bereavment_hours = "0";

$memo = "";
$bonus_amount = "0";
$commission_amount = "0";
$page_title = "CREATE";
if ($check_id != "") {
	$page_title = "EDIT";
	//get the check info
	$sql = "SELECT pchk.*
	FROM `paycheck` pchk
	INNER JOIN `user` usr
	ON pchk.user_uuid = usr.user_uuid
	WHERE pchk.customer_id = :customer_id
	AND pchk.paycheck_id = :paycheck_id";
	
	try {
		$customer_id = $_SESSION["user_customer_id"];
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("paycheck_id", $check_id);
		$stmt->execute();
		$paycheck = $stmt->fetchObject();
		
		//die(print_r($paycheck));
		
		$from_date = date("m/d/Y", strtotime($paycheck->pay_period_start_date));
		$to_date = date("m/d/Y", strtotime($paycheck->pay_period_end_date));
		$pay_date = date("m/d/Y", strtotime($paycheck->pay_date));
		
		$regular_hours = ($paycheck->regular_minutes / 60);
		$overtime_hours = ($paycheck->overtime_minutes / 60);
		$sick_hours = ($paycheck->sick_minutes / 60);
		$vacation_hours = ($paycheck->vacation_minutes / 60);
		$holiday_hours = ($paycheck->holiday_minutes / 60);
		$bereavment_hours = ($paycheck->bereavment_minutes / 60);
		
		$bonus_amount = $paycheck->bonus_amount;
		$commission_amount = $paycheck->commission_amount;
		$memo = $paycheck->memo;
		
		//die($reimbursments); 
		$arrReimb = new stdClass();
		if (strpos($paycheck->reimbursments, "{")!==false) {
			$arrReimb = json_decode($paycheck->reimbursments);
			//die(print_r($arrReimb));
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
	}
}
?>
<form id="check_form">
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
    <table width="900" border="0" align="center" cellpadding="2" cellspacing="1" bordercolor="#000000">
        <tr>
          <td colspan="2" align="left" valign="top" style="background:#000033" id="header_check">
            <div style="float:right">
                <button class="btn btn-xs btn-primary hide_me" id="edit_check">Edit</button>
                <button class="btn btn-xs btn-primary" id="save_check">Save</button>
            </div>
            <span class="admintitle"><?php echo $page_title; ?> CHECK <?php echo $check_id; ?></span><?php echo "<span style='color:white'> - </span><a style='color:white' href='#employees/" . $user_id . "'>" . $my_user->user_name . "</a><span style='color:white'> - </span><a style='color:white' href='#employees/checks/" . $user_id . "'>List Checks</a>"; ?>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" colspan="2">&nbsp;
            
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Pay Period</span>
          </td>
          <td align="left" valign="top">
          	<input type="text" id="pay_period_start_dateField" name="pay_period_start_dateField" value="<?php echo $from_date; ?>" class="check edit_field check_range_date" />
            <input type="text" id="pay_period_end_dateField" name="pay_period_end_dateField" value="<?php echo $to_date; ?>" class="check edit_field check_range_date" />
            <span id="pay_period_start_dateSpan" class="hide_me edit_span"><?php echo $from_date; ?></span>
            <span class="hide_me edit_span">&nbsp;-&nbsp;</span>
            <span id="pay_period_end_dateSpan" class="hide_me edit_span"><?php echo $to_date; ?></span>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Pay Date</span>
          </td>
          <td align="left" valign="top">
            <input type="text" id="pay_dateField" name="pay_dateField" value="<?php echo date("m/d/Y", strtotime($pay_date)); ?>" class="check edit_field" />
            <span id="pay_dateSpan" class="hide_me edit_span"><?php echo date("m/d/Y"); ?></span>
          </td>
        </tr>
        <tr>
            <td align="left" class="td_label"><a href="#payrates"></a> <span style='font-weight:bold'> Pay Rate  :</span></td>
            <td align="left">
              <span id="pay_rateSpan" class="employment edit_span">$<?php echo $pay_rate; ?></span> <a href="#payrates"></a>
              <span id="pay_periodSpan" class="employment edit_span">
                <?php 
                    switch($pay_period) {
                    case "H":
                        $pay_period = " per hour";
                        break;
                    case "D":
                        $pay_period = " per day";
                        break;
                    case "M":
                        $pay_period = " per month";
                        break;
                    }
                    echo $pay_period; ?>
              </span>
            </td>
        </tr>
        <tr>
            <td align="left" class="td_label">
                <span style='font-weight:bold'>Pay</span>
            </td>
            <td align="left">
              <span id="pay_scheduleSpan" class="employment edit_span">
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
              <span id="pay_methodSpan" class="employment edit_span">
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
            </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Hours</span>
          </td>
          <td align="left" valign="top">
            <table width="60%" cellpadding="2" cellspacing="0">
                <thead>
                <tr>
                    <th align="left" valign="top">
                        Regular
                    </th>
                    <?php if ($user_type != "2") { ?>
                    <th align="left" valign="top">
                        OT
                    </th>
                    <th align="left" valign="top">Sick Time</th>
                    <th align="left" valign="top">
                        Vacation
                    </th>
                    <th align="left" valign="top">
                        Holiday
                    </th>
                    <th align="left" valign="top">
                        Bereavment
                    </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td align="left" valign="top">
                        <input type="number" id="regular_hoursField" name="regular_hoursField" class="check edit_field" style="width:75px" value="<?php echo $regular_hours; ?>" />
                        <span id="regular_hoursSpan" class="hide_me edit_span"><?php echo $regular_hours; ?></span>
                    </td>
                    <?php if ($user_type != "2") { ?>
                    <td align="left" valign="top">
                        <input type="number" id="overtime_hoursField" name="overtime_hoursField" value="<?php echo $overtime_hours; ?>" class="check edit_field" style="width:75px" />
                        <span id="overtime_hoursSpan" class="hide_me edit_span"><?php echo $overtime_hours; ?></span>
                    </td>
                    <td align="left" valign="top">
                    	<input type="number" id="sick_hoursField" name="sick_hoursField" value="<?php echo $sick_hours; ?>" class="check edit_field" style="width:75px" />
                        <span id="sick_hoursSpan" class="hide_me edit_span"><?php echo $sick_hours; ?></span>
                    </td>
                    <td align="left" valign="top">
                        <input type="number" id="vacation_hoursField" name="vacation_hoursField" value="<?php echo $vacation_hours; ?>" class="check edit_field" style="width:75px" />
                        <span id="vacation_hoursSpan" class="hide_me edit_span"><?php echo $vacation_hours; ?></span>
                    </td>
                    <td align="left" valign="top">
                        <input type="number" id="holiday_hoursField" name="holiday_hoursField" value="<?php echo $holiday_hours; ?>" class="check edit_field" style="width:75px" />
                        <span id="holiday_hoursSpan" class="hide_me edit_span"><?php echo $holiday_hours; ?></span>
                    </td>
                    <td align="left" valign="top">
                        <input type="number" id="bereavment_hoursField" name="bereavment_hoursField" value="<?php echo $bereavment_hours; ?>" class="check edit_field" style="width:75px" />
                        <span id="bereavment_hoursSpan" class="hide_me edit_span"><?php echo $bereavment_hours; ?></span>
                    </td>
                    <?php } ?>
                </tr>
                </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Payments</span>
          </td>
          <td align="left" valign="top">
            <table cellpadding="2" cellspacing="0" border="0">
                <thead>
                <tr>
                	<?php if ($user_type != "2") { ?>
                    <th align="left" valign="top" width="1px">
                        Bonus
                    </th>
                    <th align="left" valign="top" width="1px">
                        Commission
                    </th>
                    <?php } ?>
                    <?php
					foreach($reimbursments as $reimb) { ?>
                    <th align="left" valign="top" width="1px" nowrap="nowrap">
                        <?php echo $reimb->reimbursment; ?>&nbsp;<span title="<?php echo $reimb->description; ?>" class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                    </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <tr>
                	<?php if ($user_type != "2") { ?>
                    <td align="left" valign="top" width="100px" nowrap="nowrap">
                        $&nbsp;<input type="number" id="bonus_amountField" name="bonus_amountField" value="<?php echo $bonus_amount; ?>" class="check edit_field" style="width:75px" />
                        <span id="bonus_amountSpan" class="check hide_me edit_span"><?php echo $bonus_amount; ?></span>
                    </td>
                    <td align="left" valign="top" width="100px" nowrap="nowrap">
                        $&nbsp;<input type="number" id="commission_amountField" name="commission_amountField" value="<?php echo $commission_amount; ?>" class="check edit_field" style="width:75px" />
                        <span id="commission_amountSpan" class="check hide_me edit_span"><?php echo $commission_amount; ?></span>
                    </td>
                    <?php } ?>
                    <?php 
					foreach($reimbursments as $reimb) {
						$fieldname = $reimb->reimbursment;
						$fieldname = str_replace(" ", "_", $fieldname); 
						$fieldname = strtolower($fieldname);
						
						$value = "0";
						if (isset($arrReimb->{$fieldname})) {
							$value = $arrReimb->{$fieldname};
						}
					?>
                    <td align="left" valign="top" width="150px" nowrap="nowrap">
                        $&nbsp;<input type="number" id="<?php echo $fieldname; ?>Amount" name="<?php echo $fieldname; ?>Amount" value="<?php echo $value; ?>" class="check edit_field" style="width:75px" />
                        <span id="<?php echo $fieldname; ?>Span" class="check hide_me edit_span"><?php echo $value; ?></span>
                    </td>
                    <?php } ?>
                </tr>
                </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align="left" valign="top" class="td_label">
            <span style='font-weight:bold'>Memo</span>
          </td>
          <td align="left" valign="top">
            <textarea id="memoField" name="memoField" class="check edit_field" rows="3" style="width:815px"><?php echo $memo; ?></textarea>
            <span id="memoSpan" class="hide_me"></span>
          </td>
        </tr>
    </table>
</form>
