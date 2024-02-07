<?php
$arrLoss = array(
	"damages", 
	"lost_income", 
	"medical",
	"misc_costs",
	"deductions"
);
?>
<div style="background:url(../img/glass_dark.png); margin-bottom:10px; border:1px solid white; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <div style="font-size:1.2em; color:#FFFFFF; font-weight:bold; margin-left:10px; margin-top:10px; margin-bottom:10px">Financial Losses Summary</div>
    <table width="95%" cellpadding="0" cellspacing="0" align="center" style="font-size:1.2em; color:white">	
        <tr>
            <th>&nbsp;</th>
            <?php foreach($arrLoss as $loss) { ?>
            <th align="right" valign="top" style="text-align:right; border-bottom:1px solid white"><?php echo ucwords(str_replace("_", " ", $loss)); ?></th>
            <?php } ?>
            <th style="border-bottom:1px solid white">&nbsp;</th>
            <th style="text-align:right; border-bottom:1px solid white">Total</th>
        </tr>
        <tr>
            <th align="left" valign="top">LOSS</th>
            <?php foreach($arrLoss as $loss) { ?>
            <td align="right" valign="top" style="text-align:right">
                $<span id="<?php echo $loss; ?>_list_amount">0.00</span>
            </td>
            <?php } ?>
            <th>&nbsp;</th>
            <th id="loss_summary_list_total" align="right" valign="top" style="text-align:right">$0.00</th>
        </tr>
        <tr>
            <th align="left" valign="top">DUE</th>
            <?php foreach($arrLoss as $loss) { ?>
            <td align="right" valign="top" style="text-align:right">
                $<span id="<?php echo $loss; ?>_list_balance">0.00</span>
            </td>
            <?php } ?>
            <th>&nbsp;</th>
            <th id="loss_summary_list_balance" align="right" valign="top" style="text-align:right">$0.00</th>
        </tr>
    </table>
    <div>&nbsp;</div>
</div>
<div id="losses_list_view_done"></div>
<script language="javascript">
$( "#losses_list_view_done" ).trigger( "click" );
</script>