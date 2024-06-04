<?php
require_once('../shared/legacy_session.php');

if($_SERVER['SERVER_NAME']=="v2.starlinkcms.com")
{
  $application_logo = "logo-starlinkcms.png";
}
else
{
  $application_logo = "ikase_logo_login.png";
}

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

$filename = "C:\\inetpub\\wwwroot\\iKase.website\\sessions\\search_terms_" . $_SESSION["user_plain_id"] . ".txt";
if (file_exists($filename)) {
	$arrSearch = array();
	$handle = fopen($filename, "r");
	$current_kase_search_terms = fread($handle, filesize($filename));
	fclose($handle);
	
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
	//die(print_r($arrSearch));
}
session_write_close();

$blnKaseSummary = ($_SESSION["user_customer_id"]==1121);
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
<% 
var blnSolSearch = false;
if (arrSearch.length > 0) {
	blnSolSearch = (arrSearch[0].indexOf("Sol Startdate") == 0); 
}
var colspan = 12;
if (individual_cases) {
    colspan = 13
}
if (blnSolSearch) { 
    colspan++;
}
var blnKaseSummary = false;
%>
<?php if ($blnKaseSummary) { ?>
<% blnKaseSummary = (document.location.hash.indexOf("#kasessummary") == 0); %>
<?php } ?>
<% if ((month!="" || year!="") && document.location.hash!="#kasereport/bymonth") { %>
<input id="kases_attorney_filter" value="<%=filter_attorney %>" type="hidden" />
<input id="kases_worker_filter" value="<%=filter_worker %>" type="hidden" />
<div class="alpha_summary">
<table border="0" cellpadding="2" cellspacing="0" style="width:95%" align="center">  		
    <thead>
    <tr class="kase_list_header">
        <td valign="top"><img src="img/<?php echo $application_logo; ?>" height="40" /></td>
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
                           <div style="text-align: left;font-size: 0.7em;font-weight: normal;" id="search_terms_holder">
                           <?php echo implode("<br />", $arrSearch); ?>
                           </div>
                           <?php } ?>
                    </td>
                    <td align="center" valign="top">
                    	<span id="kase_list_title"><%=title %></span>
                       <% if (atty!="") { %>
                       <br />Attorney:&nbsp;<%=attorney_full_name.capitalizeWords() %>
                       <% } %>
                       <% if (worker!="") { %>
                       <br />Coordinator:&nbsp;<%=worker_full_name.capitalizeWords() %>
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
        <td valign="top"><img src="img/<?php echo $application_logo; ?>" height="40" /></td>
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
                       <% if (atty!="") { %>
                       <br />Attorney:&nbsp;<%=attorney_full_name.capitalizeWords() %>
                       <% } %>
                       <% if (worker!="") { %>
                       <br />Coordinator:&nbsp;<%=worker_full_name.capitalizeWords() %>
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
    <tr id="kases_summary_row">
      <th colspan="<%=colspan %>" align="left">
      	<div style="width:50vw">
            <% if ((month!="" && year!="" && year!="Open Kases") || referring!="") { %>
            <div style="float:right">
            &#8592;&nbsp;<a id="hide_list" style="text-decoration:underline; cursor:pointer; font-weight:bold; font-size:0.9em" title="Click to return to Year/Month Summary">Return to Year/Month Summary</a></div>
            
            <span style="font-weight:bold; font-size:1.2em"><div id="table_month_year" style="display:inline-block"></div><%=month %> <%=year %></span>:<span style="font-weight:normal"> (<%=kaseslist.length %>)</span>
            <% } %>
        </div>
      </th>
    </tr>
    <% } %>
    <tr>
    	<% if (individual_cases || blnKaseSummary) { %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">#</th>
        <% } %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
        	<div style="float:right; font-weight:normal; margin-right:10px">
            <a id="sortby_last" class="sort_applicant" style="cursor:pointer" title="Click to sort by Last Name">Last</a>, <a id="sortby_first" class="sort_applicant" style="cursor:pointer" title="Click to sort by First Name">First</a>
            </div>
        	Applicant
        </th>
        <% if (!blnKaseSummary) { %>
        <% if (blnSolSearch) { %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">SOL</th>
        <% } %>
        
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Phone</th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Email</th>
        <% } %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">Case&nbsp;#</th>
        <% if (!blnKaseSummary) { %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;" nowrap="nowrap">
            Case&nbsp;Name
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Date
        </th>
        <% } %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            ADJ
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            DOI
        </th>
        <% if (!blnKaseSummary) { %>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Atty
        </th>
        <th align="left" style="font-size:1em; border-bottom:1px solid black;">
            Coord
         </th>
          <th align="left" style="font-size:1em; border-bottom:1px solid black;" nowrap="nowrap">
            Type
        </th>
        <% } %>
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
    var current_month = "";
    var background = "";
    var arrIndivCases = [];
    var arrDOIs = [];
    var arrADJs = [];
    var case_counter = 0;
    var current_clean_number = "";

    
    _.each( kaseslist, function(kase) {
	
    	if (kase.case_number=="" && kase.file_number!="") {
        	kase.case_number = kase.file_number;
        }
        
        var clean_case_number = kase.case_number;
        
        var blnFirstInstance = true;
        if (individual_cases) {
            if (arrIndivCases.indexOf(kase.case_id) < 0) {
                arrIndivCases.push(kase.case_id);
                arrADJs[kase.case_id] = [];
                arrDOIs[kase.case_id] = [];
                
                arrADJs[kase.case_id].push(kase.adj_number); 
                arrDOIs[kase.case_id].push(kase.doi); 
                case_counter++;
            } else {
                blnFirstInstance = false;
                arrADJs[kase.case_id].push(kase.adj_number); 
                arrDOIs[kase.case_id].push(kase.doi); 
            }
        }
        var display_counter = case_counter;
        if (blnKaseSummary) {
        	display_counter = "";
        	if (clean_case_number!="") {
				var arrNumber = clean_case_number.split("*");
				if (arrNumber.length > 1) {
					clean_case_number = arrNumber[0];
				}
				//now for hyphen
				var arrNumber = clean_case_number.split("-");
				if (arrNumber.length > 1) {
					clean_case_number = arrNumber[0];
				}
				if (current_clean_number!=clean_case_number) {
					current_clean_number = clean_case_number;
					case_counter++;
					display_counter = case_counter;	// + " (" + clean_case_number + ")";
				}
			}
        }
        /*
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
        */
        if (kase.first_name!="") {
            the_letter = kase.first_name.trim().charAt(0);
            letter_string = kase.first_name.charAt(0).valueOf();
        } else {
            kase.alpha_name = kase.full_name.capitalizeWords();
            var arrName = kase.alpha_name.split(" ");
            var first_name = arrName[0];
            the_letter = first_name.trim().charAt(0);
            letter_string = first_name.charAt(0).valueOf();
        }
        letter_string = letter_string.toUpperCase();
        
        var this_month = moment(kase.statute_limitation).format("MMMM YYYY");
        if (blnSolSearch) { 
        	if (current_month!=this_month) {
	            current_month = this_month;
        %>
      	<tr>
        	<td align="left" colspan="<%=colspan %>" style="font-weight:bold; font-size:1.6em; border-top:1px solid black">
            	<%= this_month %>
            </td>
        </tr>
        <%
        	}
        }
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
                <td align="left" colspan="<%=(colspan - 2) %>">
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
        var row_display = ""; 
        if (!blnFirstInstance) {
        	row_display = "display:none";
        }
     %>
    	<tr style="border-top:1px solid black; <%=background %>; <%=row_display %>" class="kase_data_row letter_row_<%=current_letter %> injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %>">
        	<% if (individual_cases || blnKaseSummary) { %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=display_counter %>
            </td>
            <% } %>
        	<td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<% if (customer_id == 1064) { %>
                	<input type="checkbox" id="select_kase_<%=kase.case_id %>" class="select_kase select_kase_<%=current_letter %>" />
                <% } %>
		<% if (kase.display_name==""){%>
		<%=kase.plaintiff %>
		<%} else {%>
            	<%=kase.display_name %>
		<%}%>
            </td>
            <% if (!blnKaseSummary) { %>
            <% if (blnSolSearch) { %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.statute_limitation %>
            </td>
            <% } %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.applicant_phone %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.applicant_email %>
            </td>
            <% } %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
                <div id="matrix_sent_indicator_<%=kase.case_id %>" style="float:right; display:none" class="matrix_sent_indicator"></div>
            	<a href="v8.php?n=#kase/<%=kase.case_id %>" target="_blank"><%=kase.case_number %></a>
            </td>
            <% if (!blnKaseSummary) { %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=kase.name.trim() %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap">
            	<%=moment(kase.case_date).format("MM/DD/YYYY") %>
            </td>
            <% } %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" nowrap="nowrap" class="kase_adj_<%=kase.case_id %>">
	            <%=kase.adj_number %>
            </td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black" class="kase_doi_<%=kase.case_id %>" nowrap="nowrap">
            	<% if (kase.doi=="" && new Date(kase.personal_injury_date) instanceof Date && !isNaN(new Date(kase.personal_injury_date))){ var mydate=new Date(kase.personal_injury_date) %>
		<%=((mydate.getMonth()>8)?(mydate.getMonth()+1):('0'+(mydate.getMonth()+1))) + '/' + ((mydate.getDate()>9)?mydate.getDate():('0'+mydate.getDate())) + '/' + mydate.getFullYear() %>
		<%} else {%>
		<%=kase.doi %>
		<%}%>
            </td>
            <% if (!blnKaseSummary) { %>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><span class="listing_item attorney_name"><%=kase.attorney_name.toUpperCase() %></span></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><span class="listing_item attorney_name worker_span_<%=kase.case_id %>"><%=kase.worker_name.toUpperCase() %></span></td>
            <td align="left" valign="top" style="border-right:0px solid #CCC; border-top:1px solid black"><%=kase.case_type %></td>
            <% } %>
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
    	<tr style="border-top:0px solid black" class="kase_data_row injury_row_<%= kase.id %> kase_data_row_<%= kase.case_id %>">
    	  <td colspan="<% if (!blnKaseSummary) { %>11<% } else { %>7<% } %>" align="left" valign="top" nowrap="nowrap" style="border-right:0px solid #CCC; border-top:0px solid black">
          	<span style="font-weight:bold">SPECIAL INSTRUCTIONS:</span> <%=kase.special_instructions %>
          </td>
   	  </tr>
      <% } %>
    <% 	intCounter++;
    }); %>
    </tbody>
</table>
<input type="hidden" id="individual_case_counter" value="<%=case_counter %>" />
<div id="kase_listing_report_all_done"></div>
<span style="display:none" id="listing_dois">
<% 
if (individual_cases) {
    for(var i = 0; i < case_counter; i++) {
        var case_id = arrIndivCases[i];
        var adjs = arrADJs[case_id].join("<br>"); 
        var dois = arrDOIs[case_id].join("<br>"); 
    %>
        $(".kase_doi_<%=case_id %>").html("<%=dois %>");
        $(".kase_adj_<%=case_id %>").html("<%=adjs %>");
    <% } 
}
%>
</span>
<script language="javascript">
$("#kase_listing_report_all_done" ).trigger( "click" );
</script>
