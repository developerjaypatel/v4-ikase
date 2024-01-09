<div class="scrape">
	<div id="page_header" style="display:none; color: black;margin-bottom: 20px;border-bottom: 1px solid black;">
    	<div style="float:right">As of <%=moment().format("MM/DD/YYYY") %></div>
        <span id="lookup_title" style="font-size:1.6em">EAMS ADJ Lookup Report</span>
    </div>
    <form id="scrape_form">
    	<table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
            <tr>
	            <th width="150" align="left" valign="top" nowrap="nowrap">ADJ Number</th>
                <td width="150" align="left" valign="top">
    				<input type="text" id="scrape_adj_number" name="scrape_adj_number" value="<%= adj_number %>" autocomplete="off" />
                    <span id="scrape_adj_numberSpan" style="display:none"><%= adj_number %></span>
                     &nbsp;
                     <div id="add_adj" class="adj_eams_holder" style="display:none">
                        <button class="btn btn-xs  btn-primary" onclick='addADJ(event, "<%=adj_number %>")'>add adj</button>
                    </div>
                </td>
                <td align="left" valign="top" nowrap="nowrap">
                	<div id="previouscases_scrape_holder" style="background:orange; float:right; width:550px"></div>
                	<input id="scrape_button" type="button" value="Search" />
                    <input id="scrape_save_button" type="button" value="Import" style="display:none" />
                    <input id="scrape_update_button" type="button" value="Update - NOT READY" style="display:none" />
                    <i id="scrape_reset" class="glyphicon glyphicon-repeat" style="display:none; cursor:pointer" title="Click to reset search">&nbsp;</i>
                </td>
                <td valign="top" id="scrape_feedback">&nbsp;
                	
                </td>
            </tr>
         </table>
         <hr style="margin:5px" />
         <table width="100%" border="0" align="center" cellpadding="3" cellspacing="0">
            <tr>
            	<td align="left">
                	<div>
                        <div style="display:inline-block; width:100%">
                            <div id="applicant_scrape_holder"></div>
                            <div id="hearings_scrape_holder" style="background:#369"></div>
                            <div id="bodyparts_scrape_holder"></div>
                        </div>
                    </div>
                    <div id="parties_scrape_holder">
                    </div>
                    <div id="events_scrape_holder">
                    </div>
                </td>
           </tr>
      </table>
    </form>
</div>
<div id="eams_scrape_view_all_done"></div>
<script language="javascript">
$( "#eams_scrape_view_all_done" ).trigger( "click" );
</script>