var Dom = YAHOO.util.Dom; 
var promptCases = function(first_name, last_name, dob, doi) {
	//informationCapture('<? echo $first; ?>', '<? echo $last; ?>', '<? echo $dob; ?>');
	informationCapture('Jose', 'Silva', '01/12/1978');
}
var informationCapture = function(first_name, last_name, dob) {
	/*
	<?php 
	$my_user = new systemuser($link);
	$my_user->user_logon = $USERNAME;
	$my_user->fetchuser();
	
	$full_name = $my_user->user_name;
	$arrName = explode(" ", $full_name);
	
	$cus_name_first = $arrName[0];
	$cus_name_last = str_replace($cus_name_first . " ", "", $full_name);
	$the_cus_email = "icue1@msn.com";
	?>
	*/
	var generateUrl = "https://eams.dwc.ca.gov/WebEnhancement/InformationCapture";
	mysentData = "UAN=&requesterFirstName=Nick&requesterLastName=Giszpenc&email=icue1@msn.com&reason=CASESEARCH";
	var response_results = Dom.get("response_results");
	//response_results.innerHTML= generateUrl + "?" + mysentData;
	//return;
	var type = "case";
	if (mysentData!='') {	
		//alert("about to send request:" + type);
		var request = YAHOO.util.Connect.asyncRequest('POST', generateUrl,
		   {success: function(o){
			   Dom.setStyle("response_results", "display", "");
				response = o.responseText;
				//alert(response);
				var cappos = response.indexOf("Requestor information capture");
				if (cappos > -1) {
					alert("Login to EAMS failed.");
					return;
				}
				if (type=="client") {
					promptEams();
				}
				if (type=="case") {
					var response_results = Dom.get("response_results");
					response_results.innerHTML = "<br /><br /><img src='../../images/loading.gif'>";
					//return;
					if (typeof first_name == "undefined") {
						first_name = "";
					}
					if (typeof last_name == "undefined") {
						last_name = "";
					}
					if (typeof dob == "undefined") {
						dob = "";
					}
					setTimeout("lookupCaseCompanion('', '" + first_name + "', '" + last_name + "', '" + dob + "')", 500);
				}
			},
		   failure: function(){
			   //
			   alert("The EAMS website is not accepting search requests at this time.");
			},
			timeout: 5000,
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}
var lookupCaseCompanion = function (adj_number, first_name, last_name, dob, city, zip) {
	if (typeof adj_number == "undefined") {
		adj_number = "";
	}
	if (typeof first_name == "undefined") {
		first_name = "";
	}
	if (typeof last_name == "undefined") {
		last_name = "";
	}
	if (typeof dob == "undefined") {
		dob = "";
	}
	if (typeof city == "undefined") {
		city = "";
	}
	if (typeof zip == "undefined") {
		zip = "";
	}

	var eamsSearchUrl = "eams_search.php";
	this.sentData = "adj_number=" + adj_number + "&first_name=" + first_name.toUpperCase() + "&last_name=" + last_name.toUpperCase() + "&dob=" + dob + "&city=" + city.toUpperCase() + "&zip=" + zip;
	var response_results = Dom.get("response_results");
	response_results.innerHTML = eamsSearchUrl + "?" + this.sentData;
	//return;
	//alert(eamsSearchUrl + '?' + this.sentData);
	
	var request = YAHOO.util.Connect.asyncRequest('POST', eamsSearchUrl,
	   {success: function(o){
			response = o.responseText;
			//alert(response);
			//maybe nothing returned
			var notpos = response.indexOf("No results returned");
			if (notpos>-1) {
				alert("Nothing Found!");
				Dom.setStyle("response_results", "display", "none");
				return;
			}
			
			//eams_html.value = response;
			//Dom.setStyle("eams_html", "display", "");
			//return;	
			//response_results.innerHTML = response;
			
			//result tables;
			var lookfor = "<table style='width:100%' class='resultTable'>";
			var lookforlength = lookfor.length;
			var endlookfor = "</table>";
			var startpos = response.indexOf(lookfor);
			//startpos += lookforlength;
			var endpos = response.indexOf(endlookfor, startpos)
			//first name
			var first_result = response.substring(startpos, endpos);
			first_result = first_result.replace("100%", "80%");
			first_result = first_result + "</table>";
			response_results.innerHTML = "<div style='float:right'><a href='javascript:returnToCases()'>Return to Case List</a></div>" + first_result;
			
			//response_results.innerHTML += "<br /><br />" + response;
			//return;
			//look for response table
			var lookfor = "<table style='width:100%' class='resultTable' border='1'><tr><th><span class='label'>Injured worker first name</span></th><th><span class='label'>Injured worker last name</span></th><th><span class='label'>City</span></th><th><span class='label'>ZIP Code</span></th><th><span class='label'>View detail</span></th></tr><tr><td>";
			var lookforlength = lookfor.length;
			var endlookfor = "</td><td>";
			var startpos = response.indexOf(lookfor);
			startpos += lookforlength;
			var endpos = response.indexOf(endlookfor, startpos)
			//first name
			var firstname = response.substring(startpos, endpos);
			//last name
			startpos += firstname.length + endlookfor.length;
			endpos = response.indexOf(endlookfor, startpos)
			var lastname = response.substring(startpos, endpos);
			//city
			startpos += lastname.length + endlookfor.length;
			endpos = response.indexOf(endlookfor, startpos)
			var city = response.substring(startpos, endpos);
			
			//zip
			startpos += city.length + endlookfor.length;
			endpos = response.indexOf(endlookfor, startpos)
			var zip = response.substring(startpos, endpos);
			
			//now link
			linkfor = "href='CaseFinder?partyId=";
			endlookfor = "'>View cases";
			startpos = response.indexOf(linkfor, endpos);
			startpos += linkfor.length;
			endpos = response.indexOf(endlookfor, startpos)
			var thelink = response.substring(startpos, endpos);
			
			//populate
			var eams_first_name = Dom.get("eams_first_name");
			var eams_last_name = Dom.get("eams_last_name");
			var eams_city = Dom.get("eams_city");
			var eams_zip = Dom.get("eams_zip");
			var eams_view_details = Dom.get("eams_view_details");
			
			eams_first_name.value = firstname;
			eams_last_name.value = lastname;
			eams_city.value = city;
			eams_zip.value = zip;
			eams_view_details.value = thelink;
			//alert(firstname + " / " + lastname + " / " + city + " / " + zip + " / " + thelink);
			//return;
			scrapeCaseSecondary();
		},
		failure: function(){
		   alert("failed");
		},
	   after: function(){
		   //
		   alert("after");
		},
	   scope: this}, this.sentData);
}
var scrapeCaseSecondary = function() {
	var generateUrl = "https://eams.dwc.ca.gov/WebEnhancement/CaseFinder";
	//?partyId=4178626&firstName=JOSE&lastName=ARAGON&caseNumber=ADJ8332903
	var eams_view_details = Dom.get("eams_view_details");
	
	mysentData = "partyId=" + eams_view_details.value;
	mysentData = mysentData.replace(/&amp;/gi, "&");
	//alert("j:" + mysentData);
	//return;
	var response_results = Dom.get("response_results");
	//response_results.innerHTML += "<br />" + generateUrl + "?" + mysentData;
	//return;
	generateUrl = generateUrl + "?" + mysentData;
	
	//alert(generateUrl);
	//return;
	if (mysentData!='') {	
		//logEvent("about to send request");
		mysentData = "";
		var request = YAHOO.util.Connect.asyncRequest('POST', generateUrl,
		   {success: function(o){
				response = o.responseText;
				
				var eams_html = Dom.get("eams_html");
				response = response.replace("class='resultTable'", "class=\"resultTable\"");
				
				//twice
				//response = response.replace("class='resultTable'", "class=\"resultTable\"");
				eams_html.value = response;
				//return;
				//return;
				var lookfor = "<table  style='width:100%' class='resultTable' >";
				var lookforlength = lookfor.length;
				var endlookfor = "</table>";
				var startpos = response.indexOf(lookfor);
				//startpos += lookforlength;
				var endpos = response.indexOf(endlookfor, startpos)
				//first name
				var first_result = response.substring(startpos, endpos);
				//first_result = first_result + "</table>";
				
				//remove the header
				var lookfor = "<table style='width:100%' class='resultTable' border='2' ><tr><th><span class='label'>EAMS case number</span></th><th><span class='label'>Case location</span></th><th><span class='label'>Employer</span></th><th><span class='label'>Injury date</span></th><th><span class='label'>Archived</span></th><th><span class='label'>DEU</span></th><th><span class='label'>View case detail</span></th></tr>";
				first_result = first_result.replace(lookfor, "");
				
				//response_results.innerHTML += first_result;
				eams_html.value = first_result;
				//return;
				//split the result by tr
				var arrRows = first_result.split("</td></tr>\r\n");
				var theoutput = "";
				var new_adj_number = "";	//in case we're scraping for adj
				var companions = Dom.get("companions");
				for(int=0;int<arrRows.length;int++) {
					theresponse = arrRows[int];
					
					var lookfor = "<tr><td>";
					var lookforlength = lookfor.length;
					var endlookfor = "</td><td>";
					var startpos = theresponse.indexOf(lookfor);
					startpos += lookforlength;
					var endpos = theresponse.indexOf(endlookfor, startpos)
					//first name
					var the_adj_number = theresponse.substring(startpos, endpos);
					var the_adj_number_length = the_adj_number.length;
					//alert("[" + the_adj_number+"] found");
					if (the_adj_number==" " || the_adj_number=="") {
						continue;
					}
					the_adj_number = the_adj_number.replace(/ /gi, "");
					
					if (companions.value==""){
						companions.value = the_adj_number;
					} else {
						companions.value += "|" + the_adj_number;
					}
					//case location
					startpos += the_adj_number_length + endlookfor.length;
					endpos = theresponse.indexOf(endlookfor, startpos)
					var case_location = theresponse.substring(startpos, endpos);
					
					//employer
					startpos = endpos + 9;
					endlookfor = "</td>\r\n<td";
					//start
					var employer_start = case_location + "</td><td>";
					endpos = theresponse.indexOf(endlookfor, startpos);
					//endpos += employer_start.length;
					 
					//alert(startpos + " / " + endpos);
					var employer = theresponse.substring(startpos, endpos);
					//alert(employer);
					//doi
					endlookfor = "</td><td>";
					startpos += employer.length + endlookfor.length + 2;
					endpos = theresponse.indexOf(endlookfor, startpos)
					var doi = theresponse.substring(startpos, endpos);
					//alert(doi);
					//if we don't have an adj number yet => no companions, check the doi to see if it's correct
					//break up the date, get rid of the /20
					var arrCT = doi.split(" - ");
					var blnSameDOI = true;
					if (arrCT.length==1) {
						//assume we have different dois from system
						var blnSameDOI = false;
						var returned_doi = arrCT[0];
						//is the returned doi the same as the doi we have
						/*
						<? 
						if ($injury_date!="") { 
							//fix the injury date
							$plain_injury_date = str_replace("CT", "", $injury_date);
							$plain_injury_date = str_replace(":", "", $plain_injury_date);
							$arrInjuries = explode(";", $plain_injury_date);
							foreach($arrInjuries as $injury_dates) {
								?>
									if (!blnSameDOI) {
										if (returned_doi == "<? echo date("m/d/Y", strtotime(trim($injury_dates))); ?>") {
											blnSameDOI = true;
										}
									}
								<?
							}
						} ?>
						*/
						blnSameDOI = true;
						//alert("1:" + arrCT[0]);
						doi = cleanDOI(arrCT[0]);
					}
					if (arrCT.length==2) {
						//CT
						//assume we have different dois from system
						var blnSameDOI = false;
						var returned_doi1 = cleanDOI(arrCT[0]);
						var returned_doi2 = cleanDOI(arrCT[1]);
						/*
						<? 
						if ($injury_date!="") { 
							//fix the injury date
							$plain_injury_date = str_replace("CT", "", $injury_date);
							$plain_injury_date = str_replace(":", "", $plain_injury_date);
							$arrInjuries = explode(";", $plain_injury_date);
							foreach($arrInjuries as $injury_dates) {
								$arrInjuryDates = explode("-", $injury_dates);
								if (count($arrInjuryDates)==2) {
								?>
									if (!blnSameDOI) {
										if (returned_doi1 == "<? echo date("m/d/y", strtotime(trim($arrInjuryDates[0]))); ?>" && returned_doi2 == "<? echo date("m/d/y", strtotime(trim($arrInjuryDates[1]))); ?>") {
											blnSameDOI = true;
										}
									}
								<? 		
								}
								if (count($arrInjuryDates)==1) {
								?>
									if (!blnSameDOI) {
										if (returned_doi1 == "<? echo date("m/d/y", strtotime(trim($arrInjuryDates[0]))); ?>") {
											blnSameDOI = true;
										}
									}
								<?
								}
							}
						} ?>
						*/
						//alert(arrCT[0] + " - " + arrCT[1]);
						var doi1 = cleanDOI(arrCT[0]);
						var doi2 = cleanDOI(arrCT[1]);
						
						doi = doi1 + "<BR />" + doi2 + "<BR />CT";
					}
					
					//alert(doi);
					//now check it against the one 
					var doi_field = Dom.get("doi");
					doi_field.value = doi;
					//alert(doi);
					//is the doi the same as the one for the order
					
					if (the_adj_number!="") {
						var thestyle = " style='background:white;color:black'";
						var thedoistyle = "";
						if (!blnSameDOI) {
							thedoistyle = " style='background:red;color:white'";
						}
						theoutput += "<tr" + thestyle + "><td align='left' valign='top'>" + the_adj_number + "</td><td align='left' valign='top'>" + case_location + "</td><td align='left' valign='top'>" + employer + "</td><td align='left' nowrap valign='top'" + thedoistyle + "><span class='bold_label'>" + doi + "</span></td><td align='left' valign='top'><a href='javascript:addADJ(\"" + the_adj_number + "\")'>Import ADJ</a></td>";
						
						theoutput += "</tr>";
					}
					
					//eams_html.value = theoutput;
					//return;
				}

				var theheader = "";
				
				//if (new_adj_number!="" || eams_adj_number.value!="") {
					//show the output
					theheader = "<tr><th><span class='label'>EAMS case number</span></th><th><span class='label'>Case location</span></th><th><span class='label'>Employer</span></th><th><span class='label'>Injury date</span></th><th><span class='label'>&nbsp;</span></th>";
					
					theheader += "</tr>";
				//}
				response_results.innerHTML += "<hr /><table cellpadding='2' cellspacing='0' width='100%' border='1'>" + theheader + theoutput + "</table>";
				var response_html = response_results.innerHTML;
				var theoutput = response_html.replace("View cases", "");
				theoutput = theoutput.replace("View detail", "");
				
				//theoutput += "<p><input type='button' value='Update Case ADJ Number to " + new_adj_number + "' id='add_companions' onclick='saveCompanions()'></p>";
			
				response_results.innerHTML = theoutput;
			
				//scrapeTertiary();
			},
		   failure: function(){
			   //
			   //alert("failure");
			},
		   after: function(){
			   //
			   //alert("after");
			},
		   scope: this}, mysentData);
		 //logEvent("appointment save done");
	}
}