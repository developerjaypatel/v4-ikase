<?php
require_once(APILIB_PATH.'legacy_session.php');
session_write_close();
?>
<div id="confirm_delete" style="display:none; position:absolute; top:20px; color:white; background:black; border:0px solid white; font-size:1.2em; padding:10px; text-align:center; z-index:1051">
	<div style="width:350px; margin-left:auto; margin-right:auto; ">
    <input type="hidden" name="confirm_delete_id" id="confirm_delete_id" value="" />
    Are you sure you want to delete this applicant?
    <div style="padding:5px; text-align:center"><a id="delete_kase" class="delete_yes white_icon" style="cursor:pointer">YES</a></div>
    <div style="padding:5px; text-align:center"><a id="cancel_kase" class="delete_no white_icon" style="cursor:pointer">NO</a></div>
    </div>
</div>
<div class="glass_header" id="list_kases_header">
	<div style="float:right" class="white_text">
    	<!--
        Sort By 
    	<select name="kases_sort_by" id="kases_sort_by" >
        	<option value="first_name" selected="selected">First Name</option>
            <option value="last_name">Last Name</option>
        </select>
        -->
    </div>
    <span style="font-size:1.2em; color:#FFFFFF">List of Applicants employed to</span>&nbsp;&nbsp;<a title="Click for New Kase" id="new_kase" style="color:#FFFFFF; text-decoration:none; margin-left:10px">
                <button class="btn btn-transparent" style="color:white; border:0px solid; width:20px">
                    <i class="glyphicon glyphicon-plus-sign" style="color:#00FFFF">&nbsp;</i>
                </button>
            </a>
</div><br />
<table id="kase_listing" class="tablesorter kase_listing" border="0" cellpadding="0" cellspacing="1" width="100%" style="-moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px;">
    <thead>
    <tr>
        <th>
            Kase
        </th>
        <th>
        	Type
        </th>
        <th>
            ADJ&nbsp;#
        </th>
        <th>
            DOI
        </th>
        <th>
            Venue
        </th>
        <th>
            Status
        </th>
        <th>
            Date
        </th>
        <th>
            SSN
        </th>
        <th>
            DOB
        </th>
        <th>
            Language
        </th>
        <th>
            Occupation
        </th>
        <th>
            Atty
        </th>
        <th>
            Worker
        </th>
        <th>&nbsp;
        	
        </th>
    </tr>
    </thead>
    <tbody class="listing_item">
       <!-- _.each( kases, function(kase) { -->
        <tr class="applicant_">
            <td colspan="14">
                <div style="width:100%; 
                    text-align:left; 
                    font-size:1.8em; 
                    background:#CFF; 
                    color:red;">
                    	
				</div>
            </td>
        </tr>
    <tr class="kase_data_row injury_row_ kase_data_row_" style="height:35px; font-family:Arial, Helvetica, sans-serif; font-size:1em;">
        <td style="border:0px solid red;"> 	
        	<span style="float:right; border:0px solid black; margin-right:50px">
            	<span class="search_kase_item"></span>
            </span>
        </td>
        <td nowrap="nowrap"><span class="listing_item"></span></td>
        <td><span class="listing_item search_kase_item"></span></td>
        <td nowrap="nowrap">
        	<span class="listing_item">
        		
            </span>
        </td>
        <td><span class="listing_item"></span></td>
        <td nowrap="nowrap"><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td nowrap="nowrap"><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td><span class="listing_item"></span></td>
        <td>        	
        
        </td>
    </tr>
    <!-- });  -->
    </tbody>
</table>
<div id="applicant_listing_all_done"></div>
<script language="javascript">
$( "#applicant_listing_all_done" ).trigger( "click" );
</script>
