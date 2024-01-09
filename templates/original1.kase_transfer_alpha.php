<div class="glass_header" style="margin-bottom:10px">
    <span style="font-size:1.2em; color:#FFFFFF" id="transfer_title">Transfer Cases by Last Name</span>
</div>
<div class="alphabet" style="width:100%;">
	<a class="letter_click first" id="letter_A">A</a>
	<a class="letter_click" id="letter_B">B</a>
	<a class="letter_click" id="letter_C">C</a>
	<a class="letter_click" id="letter_D">D</a>
	<a class="letter_click" id="letter_E">E</a>
	<a class="letter_click" id="letter_F">F</a>
	<a class="letter_click" id="letter_G">G</a>
	<a class="letter_click" id="letter_H">H</a>
	<a class="letter_click" id="letter_I">I</a>
	<a class="letter_click" id="letter_J">J</a>
	<a class="letter_click" id="letter_K">K</a>
	<a class="letter_click" id="letter_L">L</a>
	<a class="letter_click" id="letter_M">M</a>
	<a class="letter_click" id="letter_N">N</a>
	<a class="letter_click" id="letter_O">O</a>
	<a class="letter_click" id="letter_P">P</a>
	<a class="letter_click" id="letter_Q">Q</a>
	<a class="letter_click" id="letter_R">R</a>
	<a class="letter_click" id="letter_S">S</a>
	<a class="letter_click" id="letter_T">T</a>
	<a class="letter_click" id="letter_U">U</a>
	<a class="letter_click" id="letter_V">V</a>
	<a class="letter_click" id="letter_W">W</a>
	<a class="letter_click" id="letter_X">X</a>
	<a class="letter_click" id="letter_Y">Y</a>
	<a class="letter_click" id="letter_Z">Z</a>
</div>
<div class="bulk_kase_transfer white_text" style="margin-left:auto; margin-right:auto; width:40%; display:none">
<form id="bulk_kase_transfer_form" name="bulk_kase_transfer_form" method="post" action="">
<input type="hidden" name="ids" value="" id="ids" />
<table width="550px" border="0" align="left" cellpadding="3" cellspacing="0" style="display:" id="bulk_kase_transfer">
	<tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">Transfer From:</th>
        <td colspan="2" valign="top" id="transfer_from">      
        </td>
    </tr>
    <tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">Assign to:</th>
        <td colspan="2" valign="top" id="date_holder">
        	<div class="kase_transfer">
        		<input name="assigneeInput" type="text" id="assigneeInput" style="width:150px" class="modal_input" value="" />
            </div>
            <span id="transferSpan" style=""></span>        
        </td>
	</tr>
    <tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">Include:</th>
        <td colspan="2" valign="top">
        	      <input type="checkbox" checked="checked" id="transfer_tasks" value="Y" />&nbsp;Kase Tasks
        </td>
	</tr>
    <tr>
        <th align="left" valign="top" scope="row" nowrap="nowrap">&nbsp;</th>
        <td colspan="2" valign="top">
        	      <button class="btn btn-primary" id="transfer_cases">Transfer Cases</button>
                  <input type="hidden" id="letter_ids" value="" />
        </td>
	</tr>
</table>
</form>
</div>
<div id="kase_transfer_alpha_all_done"></div>
<script language="javascript">
$( "#kase_transfer_alpha_all_done" ).trigger( "click" );
</script>