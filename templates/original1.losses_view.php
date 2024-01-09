<?php
$arrLoss = array(
	"damages", 
	"lost_income", 
	"medical",
	"misc_costs",
	"deductions"
);
?>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:1.2em">
	<thead>
    	<tr>
        	<th>&nbsp;</th>
            <th width="10%" align="right" valign="top" style="text-align:right">LOSS</th>
            <th width="10%">&nbsp;</th>
            <th width="10%"align="right" valign="top" style="text-align:right">DUE</th>
        </tr>
    </thead>
	<?php foreach($arrLoss as $loss) { ?>
    <tbody>
        <tr>
            <th align="left" valign="top"><?php echo ucwords(str_replace("_", " ", $loss)); ?></th>
            <td align="right" valign="top">
                $<span id="<?php echo $loss; ?>_amount">0.00</span>
            </td>
            <td>&nbsp;</td>
            <td align="right" valign="top">
                $<span id="<?php echo $loss; ?>_balance">0.00</span>
            </td>
        </tr>
    
    <?php } ?>
    	<tr>
        	<th>&nbsp;</th>
            <th id="loss_summary_total" align="right" valign="top" style="text-align:right">$0.00</th>
            <th>&nbsp;</th>
            <th id="loss_summary_balance" align="right" valign="top" style="text-align:right">$0.00</th>
        </tr>
	</tbody>
</table>
<div id="losses_view_done"></div>
<script language="javascript">
$( "#losses_view_done" ).trigger( "click" );
</script>