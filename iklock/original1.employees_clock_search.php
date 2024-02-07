<?php
if ($my_user->hired_date =="0000-00-00"){
	$my_user->hired_date=date ("m/d/Y");
}
?>
<style type="text/css">
<!--
.admintitle {	color: #FFFFFF;
	font-size: 18px;
	font-weight: bold;
}
-->
</style>
<form action="index.php" method="post" enctype="multipart/form-data" name="formMaker" id="formMaker">
  <table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr>
      <td width="50%" valign="top"><table width="100" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <td align="left" valign="top"><table border="0" bordercolor="#000000" cellspacing="0" cellpadding="2" width="100%" align="center">
            <tr>
              <td colspan="3" align="left" valign="bottom" nowrap="nowrap" bgcolor="#6666FF"><strong>Employee</strong>:</td>
            </tr>
            <tr>
              <td width="28%" align="left" valign="bottom" nowrap="nowrap"><strong>Name:</strong></td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><?php echo ucwords (strtolower($my_user->user_name)); ?></td>
            </tr>
            <tr>
              <td align="left" valign="bottom" nowrap="nowrap"><strong>Hired:</strong></td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><?php echo date("m/d/Y", strtotime($my_user->hired_date)); ?></td>
            </tr>
            <?php if (date("g:iA", strtotime($my_user->clock_in_time))!='12:01AM') { ?>
            <tr>
              <td align="left" valign="bottom" nowrap="nowrap"><strong>Clock In:</strong></td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><?php echo date("g:iA", strtotime($my_user->clock_in_time)); ?></td>
            </tr>
            <tr>
              <td align="left" valign="bottom" nowrap="nowrap"><strong>Clock Out:</strong></td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><?php echo date("g:iA", strtotime($my_user->clock_out_time)); ?></td>
            </tr>
            <?php } else { ?>
            <tr>
              <td align="left" valign="bottom" nowrap="nowrap"><strong>Access:</strong></td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap">Anytime</td>
            </tr>
            <?php } ?>
            <tr>
              <td colspan="3" align="left" valign="bottom" nowrap="nowrap"><hr /></td>
            </tr>
            <tr>
              <td colspan="3" align="left" valign="bottom" nowrap="nowrap" bgcolor="#6666FF"><strong>Search:</strong></td>
            </tr>
            <tr>
              <td align="left" valign="top" nowrap="nowrap"><strong>Hours for:</strong></td>
              <td width="36%" align="left" valign="top" nowrap="nowrap"><input name="the_date" type="text" id="the_date" value="<?php echo date("m/d/Y", strtotime($the_date)); ?>" size="10" />
                <input type="hidden" name="user" value="<?php echo $user; ?>" /></td>
              <td width="36%" align="left" valign="top" nowrap="nowrap"><a href="#" onclick="window.open('calendar/popup.php?datefield=the_date&amp;month=<?php echo date("m"); ?>&amp;year=<?php echo date("Y"); ?>&amp;user=','Calendar','toolbar=no,width=350,height=650,left=350,top=0,screenX=350,screenY=200,status=no,scrollbars=yes,resize=yes');return false"><img src="images/calendar.gif" alt="Click here for calendar" width="20" height="20" border="0" /></a></td>
            </tr>
            <tr>
              <td align="center" valign="bottom" nowrap="nowrap">&nbsp;</td>
              <td colspan="2" align="left" valign="bottom" nowrap="nowrap"><input type="submit" name="Submit" value="Report &gt;&gt;" /></td>
            </tr>
          </table></td>
        </tr>
        <tr>
          <td width="73%" align="left" valign="top" style="background:url(../../images/ui/answer_bottom_r1.jpg) top center repeat-x"><table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
            <tr>
              <td align="left" valign="top"><hr /></td>
            </tr>
            <tr>
              <td align="left" valign="top" bgcolor="#6666FF"><strong>Summary:</strong></td>
            </tr>
            <tr>
              <td align="left" valign="top"><?php $time_spent = hours_minutes($work_seconds[$currentuser]);
echo "<strong>Work:</strong> " . str_replace("00 hrs", "", $time_spent) . "<BR>";
$time_spent = hours_minutes($break_seconds[$currentuser]);
echo "<strong>Break:</strong> " . str_replace("00 hrs", "", $time_spent) . "<BR>";
$time_spent = hours_minutes($lunch_seconds[$currentuser]);
echo "<strong>Lunch:</strong> " . str_replace("00 hrs", "", $time_spent) . "<BR>"; ?></td>
            </tr>
            <tr id="overtime_warning" style="display:none">
              <td class="day_summary" style="color:#000000" colspan="2"><?php 
                    if ($blnAdmin || $blnAnytime) { ?>
                &nbsp;no warn
                <?php } else { ?>
                <strong>The system will clock you out soon, you have almost reached the maximum number of hours for today. If you think you need overtime, please contact one of the managers</strong>
                <?php } ?></td>
            </tr>
          </table></td>
        </tr>
      </table></td>
    </tr>
  </table>
</form>