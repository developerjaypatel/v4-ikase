<?php
include("../api/manage_session.php");

$current_kase_search_terms = "";
$arrSearch = array();
if (isset($_SESSION["current_kase_search_terms"])) {
	if ($_SESSION["current_kase_search_terms"]!="") {
		$current_kase_search_terms = $_SESSION["current_kase_search_terms"];
		$post_terms = json_decode($current_kase_search_terms);
		
		foreach($post_terms as $pindex=>$post_term) {
			if ($post_term!="") {
				$search_term = ucwords(str_replace("_", " ", $pindex));
				//Case Throughdate
				$search_term = str_replace("Case Date", "From", $search_term);
				$search_term = str_replace("Case Throughdate", "Through", $search_term);
				$arrSearch[] = $search_term . ":&nbsp;" . $post_term;
			}
		}
	}
	//reset
	//$_SESSION["current_kase_search_terms"] = "";
	//unset($_SESSION["current_kase_search_terms"]);
}
session_write_close();
?>
<link rel="stylesheet" href="css/bootstrap.3.0.3.min.css">
<link rel="stylesheet" href="css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="css/token-input.css" />
<link rel="stylesheet" type="text/css" href="css/token-input-kase.css" />
<script src="lib/jquery.tokeninput.js"></script>
<style>
P.breakhere {page-break-after: always}

@media print {
  a[href]:after {
    content: none !important;
  }
}
</style>
<% if ((month!="" || year!="") && document.location.hash!="#kasereport/bymonth") { %>
<input id="kases_attorney_filter" value="<%=filter_attorney %>" type="hidden" />
<input id="kases_worker_filter" value="<%=filter_worker %>" type="hidden" />
<div class="alpha_summary">
<table border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">  		
    <thead>
    <tr class="kase_list_header">
        <td valign="top"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left" colspan="6">
        
            <div style="float:right">
                <em>Found <span class="found_count"><%=kaseslist.length %></span></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
    <tr class="kase_list_header">
        <th style="font-size:1.5em" align="center" colspan="6">
        	<table width="100%">
            	<tr>
                	<td width="13%" valign="top">
                    	 <?php if (count($arrSearch) > 0) { ?>
                           <div style="text-align: left;font-size: 0.7em;font-weight: normal;">
                           <?php echo implode("<br />", $arrSearch); ?>
                           </div>
                           <?php } ?>
                    </td>
                    <td align="center" valign="top">
                    	<span id="kase_list_title"><%=title %></span>
                       <% if (worker!="") { %>
                       - <%=worker.toUpperCase() %>
                       <% } %>
                    </td>
                    <td width="13%">&nbsp;
                    	
                    </td>
                </tr>
           </table>
        </th>
    </tr>
    </thead>
</table>
<% } %>
<% if (customer_id == 1064) { %>
    <a name="top"></a>
    <div style="width:40%; margin-left:auto; margin-right:auto; text-align:center" class="alpha_summary">
        <a id="hide_alpha_summary" style="cursor:pointer; color:blue; text-decoration:underline">Hide Alpha Summary</a>
    </div>
    <table class="alpha_summary">
        <tr>
            <td align="left">
                <span style="font-weight:bold">No Applicant</span>
                <%=arrLetterCount["TBD"] %>
            </td>
        </tr>
    <% _.each( arrFirstLetters, function(first_letter) {
            if (first_letter!="TBD") { %>
            <tr>
                <td align="left">
                    <span style="font-weight:bold"><a href="#<%=first_letter %>"><%=first_letter %></a></span>
                    (<%=arrLetterCount[first_letter] %>)
                </td>
            </tr>
    <%		}
    }); 
    %>
    </table>
    <div class="alpha_summary" style="height:10px">&nbsp;</div>
    <hr class="alpha_summary" />
    <p class="breakhere alpha_summary">&nbsp;</p>
<% } %>
</div>
<% if (customer_id == 1064) { %>
<table border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">  		
    <thead>
    <tr class="kase_list_header">
        <td valign="top"><img src="img/ikase_logo_login.png" height="32" width="77"></td>
        <td align="left" colspan="6">
        
            <div style="float:right">
                <em>Found <span class="found_count"><%=kaseslist.length %></span></em>
            </div>
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:bold; font-size:1.5em">&nbsp;</span>
        </td>
      </tr>
    <tr class="kase_list_header">
        <th style="font-size:1.5em" align="center" colspan="6">
        	<table width="100%">
            	<tr>
                	<td width="13%" valign="top">
                    	 <?php if (count($arrSearch) > 0) { ?>
                           <div style="text-align: left;font-size: 0.7em;font-weight: normal;" id="search_terms">
                           <?php echo implode("<br />", $arrSearch); ?>
                           </div>
                           <?php } ?>
                    </td>
                    <td align="center" valign="top">
                    	<span id="kase_list_title"><%=title %></span>
                       <% if (worker!="") { %>
                       - <%=worker.toUpperCase() %>
                       <% } %>
                    </td>
                    <td width="13%">&nbsp;
                    	
                    </td>
                </tr>
           </table>
        </th>
    </tr>
    </thead>
</table>	
<% } %>
<table cellpadding="3" cellspacing="0" border="0" style="margin-top:20px; width:95%" align="center">
  	<thead>
    <% if (year!="now") { %>
    <tr>
      <th colspan="7" align="left">
      	<% if ((month!="" && year!="" && year!="Open Kases") || referring!="") { %>
        <div style="float:right">
        &#8592;&nbsp;<a id="hide_list" style="text-decoration:underline; cursor:pointer; font-weight:normal; font-size:0.9em" title="Click to return to Year/Month Summary">Summary</a></div>
        
        <span style="font-weight:bold; font-size:1.2em"><div id="table_month_year" style="display:inline-block"></div><%=month %> <%=year %></span>:<span style="font-weight:normal"> (<%=kaseslist.length %>)</span>
        <% } %>
      </th>
    </tr>
    <% } %>
    <tr>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Applicant</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Phone</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Email</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Case #</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;" nowrap="nowrap">
            Case Name
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            ADJ
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            DOI
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Atty
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Coord
         </th>
          <th align="left" style="font-size:1em; border-bottom:1px solid black;" nowrap="nowrap">
            Type
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Status
        </th>
    </tr>
    </thead>
    <tbody>
    <% 
    var intCounter = 1;
    var the_letter = "";
    var letter_string = "";
    var current_letter = "~";
    var background = "";
    _.each( kaseslist, function(kase) {
    	if (kase.case_number=="" && kase.file_number!="") {
        	kase.case_number = kase.file_number;
        }
        if (kase.last_name!="") {
            the_letter = kase.last_name.trim().charAt(0);
            letter_string = kase.last_name.charAt(0).valueOf();
        } else {
            kase.alpha_name = kase.full_name.capitalizeWords();
            var arrName = kase.alpha_name.split(" ");
            var last_name = arrName[arrName.length - 1];
            the_letter = last_name.trim().charAt(0);
            letter_string = last_name.charAt(0).valueOf();
        }
        letter_string = letter_string.toUpperCase();
     %>
     <% 
     if (customer_id == 1064) {
     		if (current_letter != letter_string && letter_string!="") {
            	current_letter = letter_string;
            	background = "background:#EDEDED;"; %>
            <tr style="border-top:1px solid black; <%=background %>">
            	<td align="left" colspan="1">
                	<div style="float:right">
                    	<input type="checkbox" id="select_all_<%=current_letter %>" class="select_all" /> Select All <%=current_letter %>s
                    </div>
                	<a name="<%=current_letter %>"></a><%=current_letter %> (<%=arrLetterCount[current_letter] %>)
                </td>
                <td align="left" colspan="1">
                    <a href="#top" id="back-to-top" title="Back to top" class="show">
                        ↑
                    </a>
                </td>
                <td align="left" colspan="9">
                	<button id="assign_kase_<%=current_letter %>" class="btn btn-primary btn-sm assign_kase" style="display:none">Assign to Coordinator</button>
                    <table class="workerInput_<%=current_letter %>" style="display:none">
                    	<tr>
                            <td>
                                <label class="workerInput_<%=current_letter %>">Coordinator:&nbsp;</label>
                            </td>
                            <td>
                                <input value="" id="workerInput_<%=current_letter %>" style="width:210px;" class="kase input_class" />
                            </td>
                            <td>
                                <button id="save_kase_<%=current_letter %>" class="save_assign btn btn-success btn-sm" style="visibility:hidden">Save</button>
                            </td>
                        </tr>
                    </table>
                </td> 
            </tr>
     <% 	background = "";
     		}
     	} 
     %>
    	<tr style="border-top:1px solid black; <%=background %>" class="kase_data_row letter_row_<%=current_letter %> injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %>">
        	<td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<% if (customer_id == 1064) { %>
                	<input type="checkbox" id="select_kase_<%=kase.case_id %>" class="select_kase select_kase_<%=current_letter %>" />
                <% } %>
            	<%=kase.display_name %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.applicant_phone %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.applicant_email %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
                <div id="matrix_sent_indicator_<%=kase.case_id %>" style="float:right; display:none" class="matrix_sent_indicator"></div>
            	<a href="v8.php?n=#kase/<%=kase.case_id %>" target="_blank"><%=kase.case_number %></a>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.name.trim() %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap"><%=kase.adj_number %></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.doi %></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><span class="listing_item attorney_name"><%=kase.attorney_name.toUpperCase() %></span></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><span class="listing_item attorney_name worker_span_<%=kase.case_id %>"><%=kase.worker_name.toUpperCase() %></span></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.case_type %></td>
            
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.case_status %>
                <% if (kase.case_substatus != "") { %>
                &nbsp;/&nbsp;<%=kase.case_substatus %>
                <% } %>
              <% if (kase.case_subsubstatus != "") { %>
                &nbsp;/&nbsp;<%=kase.case_subsubstatus %>
                <% } %>
            </td>
        </tr>
        <% if (kase.special_instructions!="") { %>
    	<tr style="border-top:1px solid black" class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %>">
    	  <td colspan="10" align="left" valign="top" nowrap="nowrap" style="border-right:0px solid #CCC; border-top:1px solid black">
          	<span style="font-weight:bold">SPECIAL INSTRUCTIONS:</span> <%=kase.special_instructions %>
          </td>
   	  </tr>
      <% } %>
    <% 	intCounter++;
    }); %>
    </tbody>
</table>
<div id="kase_listing_report_all_done"></div>
<script language="javascript">
$("#kase_listing_report_all_done" ).trigger( "click" );
</script>