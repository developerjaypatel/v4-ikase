var jetfile_search_id = false;
window.jetfile_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .jetfile_case_link":						"openKase",
		"click .app_pdf":								"getAppPDF",
		"click .app_fullpdf":							"getAppFullPDF",
		"click .send_app":								"sendApp",
		"click .send_dor":								"sendDOR",
		"click .send_dore":								"sendDORE",
		"click .send_lien":								"sendLien",
		"click .send_unstruc":							"sendUnstruc",
		"click .resend_app":							"resendApp",
		"click .file_app":								"fileApp",
		"click .file_dor":								"fileDOR",
		"click .file_dore":								"fileDORE",
		"click .file_unstruc":							"fileUnstruc",
		"click .dor_pdf":								"getDORPDF",
		"click .dor_fullpdf":							"getDORFullPDF",
		"click #show_recent":							"showRecent",
		"click #show_errors":							"showErrors",
		"click #show_all_jetfiles":						"showAll",
		"click #show_all":								"showAll",
		"keyup #jetfiles_searchList":					"scheduleSearch",
		"click #jetfile_clear_search":					"clearSearch",
		"click .eams_search":							"searchEams",
		"click #label_search_jetfiles":					"Vivify",
		"click #jetfiles_searchList":					"Vivify",
		"focus #jetfiles_searchList":					"Vivify",
		"blur #jetfiles_searchList":					"unVivify",
		"click #jetfile_listing_all_done":				"doTimeouts",
		"click .submission-button":	                    "fileSubmission",
		"click .sent_to_docusents":	                    "fileSubmission",
	},
	render:function () {
		var self = this;
		var jetfiles = this.collection.toJSON();
		
		_.each( jetfiles, function(jetfile) {
			console.log(jetfile);
			//status
			var jetfile_status = jetfile.app_status;
			jetfile.app_status_color = "";
			jetfile.app_message = "";
			jetfile.app_errors = "";
			jetfile.app_status = "&nbsp;";
			
			//jetfile.dor_errors = "";
			jetfile.dor_status = "&nbsp;";
			jetfile.date_filed = "";
			
			if (jetfile_status!="") {
				var jdata = JSON.parse(jetfile_status);
				jetfile.date_filed = jdata.date_filed;
				if (jetfile.date_filed == null) {
					jetfile.date_filed = "";
				}
				var app_status = jdata.status;
				var app_message = jdata.message;
				jetfile.app_message = jdata.message == null ? "" : " - " + jdata.message;
				jetfile.jetfile_errors = jdata.errors == null ? "" : jdata.errors;
				
				var blnAPPError = (jetfile.jetfile_errors.indexOf("<eam:FormShortName>WCAB 1</eam:FormShortName>") > -1);
				jetfile.app_errors = jetfile.jetfile_errors;
				if (jdata.forms=="DOR" && !blnAPPError && jetfile.jetfile_errors!="") {
					jetfile.app_errors = "DOR Error:&nbsp;" + jetfile.jetfile_errors;
				}
				if (jetfile.app_errors!="" && jetfile.app_errors!="&nbsp;") {
					var startpos = jdata.errors.indexOf('<pay:ErrorSecondary xmlns:pay="http://www.dir.ca.gov/dwc/EAMS/PresentTermSolution/Schemas/Common/PayloadFields">');
					var deadlength = '<pay:ErrorSecondary xmlns:pay="http://www.dir.ca.gov/dwc/EAMS/PresentTermSolution/Schemas/Common/PayloadFields">'.length;
					if (startpos < 0) {
						var startpos = jdata.errors.indexOf('<pay:ErrorSecondary >');
						var deadlength = '<pay:ErrorSecondary >'.length;
					}
					var endpos = jdata.errors.indexOf('</pay:ErrorSecondary>');
					var err = jdata.errors.substr(startpos + deadlength, (endpos - startpos - deadlength));
					jetfile.app_errors = "<span style='background:red;color:white;padding:2px'>" + err + "</span>"
					
					jetfile.app_message = "<span style='background:red;color:white;padding:2px'>" + jetfile.app_message + "</span>";
				} else {
					jetfile.app_errors = "&nbsp;<br>";
				}
				switch(app_status) {
					case "1":
					case 1:
						app_status = "Received";
						break;
					case "6":
					case 6:
						app_status = "Succeeded";
				}
				if (jdata.forms=="AppForADJ") {
					jetfile.app_status = app_status;
					if (app_status==5) {
						if (jetfile.app_message.indexOf("Form succeeded Level 3") > -1) {
							jetfile.app_message = "<span style='background:green;color:white;padding:2px'>" + jetfile.app_message + "</span>";
						}
						if (jetfile.app_message.indexOf("Form has been sent to DWC Pending Queue.") > -1) {
							jetfile.app_message = "<span style='background:orange;color:black;padding:2px'>" + jetfile.app_message + "</span>";
						}
					}
				}
				if (jdata.forms=="DOR") {
					if (app_status==5) {
						jetfile.dor_status = "&#10003; DOR" ;
					} else {
						jetfile.dor_status = "DOR " + app_status;
					}
					jetfile.app_message = "<span style='background:green;color:white;padding:2px'>" + app_status + jetfile.app_message + "</span>";
				}
			}
			
			jetfile.doi = moment(jetfile.start_date).format("MM/DD/YYYY");
			if (jetfile.end_date!="0000-00-00") {
				jetfile.doi += "-" + moment(jetfile.end_date).format("MM/DD/YYYY") + " CT";
			}
			jetfile.app = "<a href='jetfiler/app_1_2.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "' class='white_text' target='_blank'>Start APP</a>";
			jetfile.app_action = "&nbsp;";
			jetfile.app_pdf = "&nbsp;";
			
			jetfile.dor = "&nbsp;";
			jetfile.dor_action = "&nbsp;";
			jetfile.dor_pdf = "&nbsp;";
			
			jetfile.dore = "&nbsp;";
			jetfile.dore_action = "&nbsp;";
			jetfile.dore_pdf = "&nbsp;";
			
			jetfile.lien = "&nbsp;";
			jetfile.lien_action = "&nbsp;";
			jetfile.lien_pdf = "&nbsp;";
			
			jetfile.unstruc = "&nbsp;";
			jetfile.unstruc_action = "&nbsp;";
			jetfile.unstruc_pdf = "&nbsp;";
			
			blnADJNumber = (jetfile.adj_number.indexOf("ADJ")==0);
			
			if (jetfile.info!="") {
				jetfile.app = "<a href='jetfiler/app_1_2.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Review APP</a>&nbsp;|&nbsp;<a href='jetfiler/app_3_4.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Page&nbsp;2</a>&nbsp;|&nbsp;<a href='jetfiler/upload_app.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Uploads</a>";
				jetfile.app_pdf = "<a id='app_pdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='app_pdf white_text' style='cursor:pointer'>APP PDF</a>";
				jetfile.app_pdf += "&nbsp;|&nbsp;<a id='app_fullpdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='app_fullpdf white_text' data-toggle='modal' data-target='#myModal4' style='cursor:pointer'>Full APP PDF</a>";
				
				if (jetfile.app_filing_id!="" && jetfile.app_filing_id!="0") {
					if (blnADJNumber) {
						jetfile.app_action = "&nbsp;&#10003;&nbsp;" + jetfile.adj_number;
					} else {
						if (jetfile.app_errors=="" || jetfile.app_errors=="&nbsp;") {
							jetfile.app_action = "&nbsp;Filed on " + moment(jetfile.app_filing_date).format("MM/DD/YY");
						} else {
							//unless it should not be refiled
							if (jetfile.app_errors=="<span style='background:red;color:white;padding:2px'>This is a duplicate application.</span>") {
								jetfile.app_action = "<span style='background:orange;color:white;padding:1px'>Cannot be filed</span>";
							} else {
								jetfile.app_action = "<a class='resend_app white_text' id='resend_app_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "'>Fix Errors and Refile</a>";
							}
						}
					}
					
				} else {
					if (jetfile.jetfile_case_id=="" || jetfile.jetfile_case_id=="0")  {
						if (jetfile.app_document_count > 3) {
							//body parts
							if (jetfile.bodyparts_count > 0) {
								jetfile.app_action = "<a class='send_app white_text' id='send_app_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "'>Send APP to EAMS</a>";
							} else {
								jetfile.app_action = "<a href='jetfiler/app_3_4.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank' style='background:orange; padding:2px; color:black'>Click here to add Bodyparts</a>";
							}
						} else {
							jetfile.app_action = "<a href='jetfiler/upload_app.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "' id='upload_app_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "' style='background:orange; color:black; padding:2px' target='_blank'>" + (4 -jetfile.app_document_count) + " Upload(s) Required</a>";
						}
					}
					if (jetfile.jetfile_case_id!="" && jetfile.jetfile_case_id!="0")  {
						jetfile.app_action = "<a class='file_app white_text' id='file_app_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "'>APP Ready to File</a>";
					}
				}
			}
			jetfile.app_action += "&nbsp;<span id='feedback_app_" + jetfile.case_id + "_" + jetfile.injury_id + "' style='font-style:italic;font-size:0.8em'></span>";
			
			jetfile.dor = "<a href='jetfiler/dor.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "' class='white_text' target='_blank'>Start DOR</a>";
			if (jetfile.dor_info!="") {
				jetfile.dor = "<a href='jetfiler/dor.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Review DOR</a>";
				
				if (jetfile.jetfile_dor_id=="" || jetfile.jetfile_dor_id=="0")  {
					jetfile.dor_action = "<a class='send_dor white_text' id='send_dor_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "'>Send DOR to EAMS</a>";
				}
				
				
				if (jetfile.jetfile_dor_id!="" && jetfile.jetfile_dor_id!="0")  {
					if (jetfile.dor_status == "" || jetfile.dor_status == "&nbsp;") {
						jetfile.dor_action = "<a class='file_dor white_text' id='file_dor_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "_" + jetfile.jetfile_dor_id + "'>DOR Ready to File</a>";
					} else {
						jetfile.dor_action = jetfile.dor_status;
					}
					jetfile.dor_pdf = "<a id='dor_pdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='dor_pdf white_text' style='cursor:pointer'>DOR PDF</a>";
					jetfile.dor_pdf += "&nbsp;|&nbsp;<a id='dor_fullpdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='dor_fullpdf white_text' style='cursor:pointer'>Full DOR PDF</a>";
				}
				jetfile.dor_action += "&nbsp;<span id='feedback_dor_" + jetfile.case_id + "_" + jetfile.injury_id + "' style='font-style:italic;font-size:0.8em'></span>";
			}
			//however
			if (!blnADJNumber && jetfile.dor_action !="&nbsp;" && jetfile.dor_action !="") {
				jetfile.dor_action += ".  <span style='background:orange;color:white;padding:1px'>Need ADJ to File</span>";
			}
			jetfile.dore = "<a href='jetfiler/dor_e.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "' class='white_text' target='_blank'>Start DORE</a>";
			if (jetfile.dore_info!="") {
				jetfile.dore = "<a href='jetfiler/dor_e.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Review DORE</a>";
				
				if (jetfile.jetfile_dore_id=="" || jetfile.jetfile_dore_id=="0")  {
					jetfile.dore_action = "<a class='send_dore white_text' id='send_dore_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "'>Send DORE to EAMS</a>";
				}
				
				if (jetfile.jetfile_dore_id!="" && jetfile.jetfile_dore_id!="0")  {
					jetfile.dore_action = "<a class='file_dore white_text' id='file_dore_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "_" + jetfile.jetfile_dore_id + "'>DORE Ready to File</a>";

					jetfile.dore_pdf = "<a id='dore_pdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='dore_pdf white_text' style='cursor:pointer'>DOR PDF</a>";
					jetfile.dore_pdf += "&nbsp;|&nbsp;<a id='dore_fullpdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='dore_fullpdf white_text' style='cursor:pointer'>Full DOR PDF</a>";
				}
				jetfile.dore_action += "&nbsp;<span id='feedback_dore_" + jetfile.case_id + "_" + jetfile.injury_id + "' style='font-style:italic;font-size:0.8em'></span>";
			}
			
			jetfile.lien = "<a href='jetfiler/lien.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "' class='white_text' target='_blank'>Start Lien</a>";
			if (jetfile.lien_info!="") {
				jetfile.lien = "<a href='jetfiler/lien.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "' class='white_text' target='_blank'>Review Lien</a>";
				
				if (jetfile.jetfile_lien_id=="" || jetfile.jetfile_lien_id=="0")  {
					jetfile.lien_action = "<a class='send_lien white_text' id='send_lien_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "'>Send Lien to EAMS</a>";
				}
				
				if (jetfile.jetfile_lien_id!="" && jetfile.jetfile_lien_id!="0")  {
					jetfile.lien_action = "<a class='file_lien white_text' id='file_lien_" + jetfile.case_id + "_" + jetfile.injury_id + "'>Lien Ready to File</a>";

					jetfile.lien_pdf = "<a id='lien_pdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='lien_pdf white_text' style='cursor:pointer'>Lien PDF</a>";
					jetfile.lien_pdf += "&nbsp;|&nbsp;<a id='lien_fullpdf_" + jetfile.case_id + "_" + jetfile.injury_id + "' class='lien_fullpdf white_text' style='cursor:pointer'>Full Lien PDF</a>";
				}
				jetfile.lien_action += "&nbsp;<span id='feedback_lien_" + jetfile.case_id + "_" + jetfile.injury_id + "' style='font-style:italic;font-size:0.8em'></span>";
			}
			
			//unstructured documents work a bit different, there can be multiple ones
			var unstruc_number = 1;
			jetfile.unstruc = "<a href='jetfiler/unstructured.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&unstruc_number=" + unstruc_number + "' class='white_text' target='_blank'>Start Unstruc</a>";
			if (jetfile.unstruc_info!="") {
				var js_info = JSON.parse(jetfile.unstruc_info);
				var arrUnstruc = [], arrUnstrucAction = [], arrUnstrucPDF = [];

				for(var i=0; i < js_info.length; i++) {
					var the_unstruc = js_info[i];
					var the_data = the_unstruc.data;
					var the_number = Number(the_unstruc.unstruc_number) + 1;
					
					//going through multiples
					var the_unstruc = "<a href='jetfiler/unstructured.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_case_id=" + jetfile.jetfile_case_id + "&unstruc_number=" + the_number + "' class='white_text' target='_blank'>Review Unstruc " + the_number + "</a>&nbsp;(" + the_data.document_title + ")";
					var the_unstruc_pdf = "<a href='uploads/" + customer_id + "/" + jetfile.case_id + "/jetfiler/" + the_data.filepath + "' target='_blank' class='white_text'>Unstruc " + the_number + " PDF</a>";
					var the_unstruc_action = "";
					
					if (the_data.unstruc_id=="" || the_data.unstruc_id=="0")  {
						the_unstruc_action = "<a class='send_unstruc white_text' id='send_unstruc_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + the_number + "'>Send Unstruc " + the_number + " to EAMS</a>";
					}
					
					if (the_data.unstruc_id!="" && the_data.unstruc_id!="0")  {
						the_unstruc_action = "<a class='file_unstruc white_text' id='file_unstruc_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + jetfile.jetfile_id + "_" + jetfile.jetfile_case_id + "_" + the_data.unstruc_id + "_" + the_number + "'>Unstruc " + the_number + " Ready to File</a>";
	
						//the_unstruc_pdf = "<a id='unstruc_pdf_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + the_number + "' class='unstruc_pdf white_text' style='cursor:pointer'>Unstruc " + the_number + " PDF</a>";
						//the_unstruc_pdf += "&nbsp;|&nbsp;<a id='unstruc_fullpdf_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + the_number + "' class='unstruc_fullpdf white_text' style='cursor:pointer'>Full Unstruc " + the_number + " PDF</a>";
					}
					the_unstruc_action += "&nbsp;<span id='feedback_unstruc_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + the_number + "' style='font-style:italic;font-size:0.8em'></span>";
					
					the_unstruc = "<div id='unstruc_holder_" + jetfile.case_id + "_" + jetfile.injury_id + "_" + the_number + "'>" + the_unstruc + "</div>";
					arrUnstruc.push(the_unstruc);
					arrUnstrucAction.push(the_unstruc_action);
					arrUnstrucPDF.push(the_unstruc_pdf);
					//always one more
					unstruc_number = the_number + 1;
				}
				if (arrUnstruc.length > 0) {
					jetfile.unstruc = arrUnstruc.join("<br>");
					jetfile.unstruc_action = arrUnstrucAction.join("<br>");
					jetfile.unstruc_pdf = arrUnstrucPDF.join("<br>");
				}
				jetfile.unstruc += "<br><br><a href='jetfiler/unstructured.php?case_id=" + jetfile.case_id + "&injury_id=" + jetfile.injury_id + "&jetfile_id=" + jetfile.jetfile_id + "&unstruc_number=" + unstruc_number + "' class='white_text' target='_blank'>New Unstruc</a>";
			}
			if (jetfile.jetfile_case_id=="0")  {
				jetfile.jetfile_case_id = "";
			}
			
			//submission
			if (jetfile.submitted_by!="") {
				 jetfile.submitted_by = "Submitted by " + jetfile.submitted_by + " on " + moment(jetfile.submitted_date).format("MM/DD/YY");
				 if (jetfile.date_filed!=="0000-00-00 00:00:00" && jetfile.date_filed!="") {
				 	jetfile.submitted_by += "<br>Filed on " + moment(jetfile.date_filed).format("MM/DD/YY");
				 }
			}
		});
		
		if (typeof this.model.get("main_filter") == "undefined") {
			this.model.set("main_filter", "");
		}
		var main_filter = this.model.get("main_filter");
		try {
			$(this.el).html(this.template({jetfiles: jetfiles, main_filter: main_filter}));
		}
		catch(err) {
			var view = "jetfile_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		//$("#address").hide();
        //$('#details', this.el).html(new InjurySummaryView({model:this.model}).render().el);
		
		this.model.set("editing", false);
		
		setTimeout(function() {
			var hash = document.location.hash;
			
			if (hash!="#jetfiles") {
				$("#show_all_holder").fadeIn();
				$("#jetfile_listing td").css("font-size", "1.1em");
				$("#jetfile_listing th").css("font-size", "1.1em")
			}
		}, 1000);
		
		return this;
	},
	doTimeouts:function() {
		$("#jetfile_listing th").css("font-size","1.3em");
		$("#jetfile_listing td").css("font-size","1.3em");
	},
	showAll: function() {
		event.preventDefault();
		var jetfiles = new JetfileCollection();
		jetfiles.searchDB("");
	},
	scheduleSearch: function() {
		var self = this;
		clearTimeout(jetfile_search_id);
		jetfile_search_id = setTimeout(function() {
			self.searchJetfiles();
		}, 1000);
	},
	searchJetfiles: function() {
		var val = $("#jetfiles_searchList").val();
		var jetfiles = new JetfileCollection();
		jetfiles.searchDB(val);
	},
	showErrors: function(event) {
		event.preventDefault();
		var jetfiles = new JetfileCollection();
		jetfiles.searchErrors();
	},
	showRecent: function(event) {
		event.preventDefault();
		var jetfiles = new JetfileCollection();
		jetfiles.searchRecent();
	},
	openKase: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[2];
		
		document.location.href = "#kases/" + id;
	},
	unVivify: function(event) {
		var textbox = $("#jetfiles_searchList");
		var label = $("#label_search_jetfiles");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#jetfiles_searchList");
		var label = $("#label_search_jetfiles");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#jetfiles_searchList').focus();
		}
	},
	clearSearch: function() {
		$("#jetfiles_searchList").val("");
		$( "#jetfiles_searchList" ).trigger( "keyup" );
	},
	searchEams: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var case_id = arrID[arrID.length - 2];
		var injury_id = arrID[arrID.length - 1];
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						self.searchEams(event);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		var eams = new Backbone.Model();
		eams.set("injury_id", injury_id);
		eams.set("first_name", kase.get("first_name"));
		eams.set("last_name", kase.get("last_name"));
		eams.set("dob", kase.get("dob"));
		eams.set("employer", kase.get("employer"));
		eams.set("applicant_full_address", kase.get("applicant_full_address"));
		eams.set("start_date", kase.get("start_date"));
		eams.set("end_date", kase.get("end_date"));
		
		var container = "content";
		eams.set("holder", "content");
	
		$('#' + container).html(new search_eams({model: eams}).render().el);	
		$('#' + container).removeClass("glass_header_no_padding");
	},
	sendApp: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id;
		var url = '../api/jetfile/send';
		$("#feedback_app_" + case_id + "_" + injury_id).html("Sending to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		//console.log("Sending to EAMS");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				var blnGo = true;
				if(data.error) {  // If there is an error, show the error messages
					//saveFailed(data.error.text);
					if (data.error == "already in cajetfile") {
						blnGo = true;
						data.case_id = data.jetfile_case_id;
					} else {
						blnGo = false;
						$("#feedback_app_" + case_id + "_" + injury_id).html("<a style='color:white;background:red;padding:2px; font-size:1.2em' href='jetfiler/upload_app.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "' target='_blank' title='Click to upload documents'>" + data.error.text + "</a>");
					}
				}
				if (blnGo) {
					$("#feedback_app_" + case_id + "_" + injury_id).html("Saved &#10003;");
					
					//console.log(data);
					jetfile_case_id = data.case_id;
					//update the display
					$("#jetfile_case_id_" + case_id + "_" + injury_id).html(jetfile_case_id);
					//update the cse_jetfile
					var append_link = "&nbsp;|&nbsp;<a class='file_app white_text' id='file_app_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "'>Ready to File</a>";
					
					var review_link = $("#app_holder_" + case_id + "_" + injury_id).html().split("&nbsp;|&nbsp;")[0];
					
					$("#app_holder_" + case_id + "_" + injury_id).html(review_link + "&nbsp;|&nbsp;" + append_link);
					
					self.updateJetfile(jetfile_id, jetfile_case_id, case_id, injury_id);
				}
			}
		});
	},
	resendApp: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id;
		var url = '../api/jetfile/resend';
		$("#feedback_app_" + case_id + "_" + injury_id).html("Refiling&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					//saveFailed(data.error.text);
					$("#feedback_app_" + case_id + "_" + injury_id).html("<a style='color:white;background:red;padding:2px; font-size:1.2em' href='jetfiler/upload_app.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "' target='_blank' title='Click to upload documents'>" + data.error.text + "</a>");
				} else {
					$("#feedback_app_" + case_id + "_" + injury_id).html("Saved &#10003;");
					
					//console.log(data);
					//return;
					jetfile_case_id = data.case_id;
					//update the display
					$("#jetfile_case_id_" + case_id + "_" + injury_id).html(jetfile_case_id);
					//update the cse_jetfile
					var append_link = "&nbsp;|&nbsp;<a class='file_app white_text' id='file_app_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "'>Ready to File</a>";
					
					var review_link = $("#app_holder_" + case_id + "_" + injury_id).html().split("&nbsp;|&nbsp;")[0];
					
					$("#app_holder_" + case_id + "_" + injury_id).html(review_link + "&nbsp;|&nbsp;" + append_link);
					
					$("#file_app_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id).trigger("click");
					//console.log("refile");
				}
			}
		});
	},
	updateJetfile: function(jetfile_id, jetfile_case_id, case_id, injury_id) {
		var formValues = "jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id;
		var url = '../api/jetfile/updatecase';
		$("#feedback_app_" + case_id + "_" + injury_id).html("Updating ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#feedback_app_" + case_id + "_" + injury_id).html("<a class='file_app white_text' id='file_app_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "'>Ready to File</a>");
				}
			}
		});
	},
	getAppPDF: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		this.getPDF("app", "app", true, case_id, injury_id);
	},
	getAppFullPDF: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		
		var kase = kases.findWhere({case_id: case_id});
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						self.getAppFullPDF(event);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		
		event.preventDefault();
		var injury_id = elementArray[3];
		//this.getPDF("app_cover", "app", false, case_id, injury_id);
		current_case_id = case_id;
		composeEams("jetfilerapp_" + case_id + "_21_" + injury_id, "app_cover");
		current_case_id = "";
	},
	fileApp: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		
		$("#" + element.id).fadeOut(function() {
			$("#feedback_app_" + case_id + "_" + injury_id).html("Filing APP&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		});
		
		var formValues = "jetfile_case_id=" + jetfile_case_id;
		var url = '../api/jetfile/file';
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#feedback_app_" + case_id + "_" + injury_id).html("APP Filed");
					//console.log(data); 
					self.saveFilingID(data.filing_id, data.filing_date, case_id, injury_id, jetfile_id)
				}
			}
		});
	},
	saveFilingID: function(filing_id, filing_date, case_id, injury_id, jetfile_id) {
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&app_filing_id=" + filing_id + "&app_filing_date=" + filing_date;
		var url = "../api/jetfile/app/filingid";
		
		$("#feedback_app_" + case_id + "_" + injury_id).html("Saving Filing ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate that we are done;
					$("#feedback_app_" + case_id + "_" + injury_id).html("Filed &#10003;");
				}
			}
		});
	},
	sendDOR : function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id;
		var url = '../api/jetfile/senddor';
		$("#feedback_dor_" + case_id + "_" + injury_id).html("Sending DOR to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					$("#feedback_dor_" + case_id + "_" + injury_id).html("<a style='color:white;background:red;padding:2px; font-size:1.2em' href='jetfiler/upload_dor.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "' target='_blank' title='Click to upload documents'>" + data.error.text + "</a>");
				} else {
					//console.log(data);
					var jetfile_case_id = data.case_id;
					var jetfile_dor_id = data.dor_id;
					$("#jetfile_case_id_" + case_id + "_" + injury_id).html(jetfile_case_id);
					
					var append_link = "&nbsp;|&nbsp;<a class='file_dor white_text' id='file_dor_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "_" + jetfile_dor_id + "'>Ready to File</a>";
					
					var review_link = $("#dor_holder_" + case_id + "_" + injury_id).html().split("&nbsp;|&nbsp;")[0];
					
					$("#dor_holder_" + case_id + "_" + injury_id).html(review_link + append_link);
					//update the cse_jetfile
					self.updateJetfileDOR(jetfile_id, jetfile_case_id, jetfile_dor_id, case_id, injury_id);
				}
			}
		});
	},
	updateJetfileDOR: function(jetfile_id, jetfile_case_id, jetfile_dor_id, case_id, injury_id) {
		var formValues = "jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id + "&jetfile_dor_id=" + jetfile_dor_id;
		var url = '../api/jetfile/updatedor';
		$("#feedback_dor_" + case_id + "_" + injury_id).html("Updating System with DOR Info&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//file the app
					$("#feedback_dor_" + case_id + "_" + injury_id).html("DOR Saved");
					//fileDOR(jetfile_case_id, jetfile_dor_id);
				}
			}
		});
	},
	saveDORFilingID: function(filing_id, filing_date, case_id, injury_id, jetfile_id) {
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&dor_filing_id=" + filing_id + "&dor_filing_date=" + filing_date;
		var url = "../api/jetfile/dor/filingid";
		
		$("#feedback_dor_" + case_id + "_" + injury_id).html("Saving Filing ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate that we are done;
					$("#feedback_dor_" + case_id + "_" + injury_id).html("Filed &#10003;");
				}
			}
		});
	},
	getDORPDF: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		this.getPDF("dor", "dor", true, case_id, injury_id);
	},
	getDORFullPDF: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		this.getPDF("dor_cover", "dor", false, case_id, injury_id);
	},
	fileDOR: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		var jetfile_dor_id = elementArray[6];
		
		$("#" + element.id).fadeOut(function() {
			$("#feedback_dor_" + case_id + "_" + injury_id).html("Filing DOR&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		});
		
		var formValues = "jetfile_case_id=" + jetfile_case_id;
		formValues += "&jetfile_dor_id=" + jetfile_dor_id;
		var url = '../api/jetfile/filedor';
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#feedback_dor_" + case_id + "_" + injury_id).html("DOR Filed");
					//console.log(data); 
					self.saveDORFilingID(data.filing_id, data.filing_date, case_id, injury_id, jetfile_id)
				}
			}
		});
	},
	sendDORE : function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id;
		var url = '../api/jetfile/senddore';
		$("#feedback_dore_" + case_id + "_" + injury_id).html("Sending DORE to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					$("#feedback_dor_" + case_id + "_" + injury_id).html("<a style='color:white;background:red;padding:2px; font-size:1.2em' href='jetfiler/upload_dore.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "' target='_blank' title='Click to upload documents'>" + data.error.text + "</a>");
				} else {
					//console.log(data);
					var jetfile_case_id = data.case_id;
					var jetfile_dore_id = data.dore_id;
					
					var append_link = "&nbsp;|&nbsp;<a class='file_dore white_text' id='file_dor_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "_" + jetfile_dore_id + "'>Ready to File</a>";
					
					var review_link = $("#dore_holder_" + case_id + "_" + injury_id).html().split("&nbsp;|&nbsp;")[0];
					
					$("#dore_holder_" + case_id + "_" + injury_id).html(review_link + append_link);
					//update the cse_jetfile
					self.updateJetfileDORE(jetfile_id, jetfile_case_id, jetfile_dore_id, case_id, injury_id);
				}
			}
		});
	},
	updateJetfileDORE: function(jetfile_id, jetfile_case_id, jetfile_dore_id, case_id, injury_id) {
		var formValues = "jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id + "&jetfile_dore_id=" + jetfile_dore_id;
		var url = '../api/jetfile/updatedore';
		$("#feedback_dore_" + case_id + "_" + injury_id).html("Updating System with DORE Info&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//file the app
					$("#feedback_dore_" + case_id + "_" + injury_id).html("DORE Saved");
					//fileDORE(jetfile_case_id, jetfile_dore_id);
				}
			}
		});
	},
	fileDORE: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		var jetfile_dore_id = elementArray[6];
		
		$("#" + element.id).fadeOut(function() {
			$("#feedback_dore_" + case_id + "_" + injury_id).html("Filing DORE&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		});
		
		var formValues = "jetfile_case_id=" + jetfile_case_id;
		formValues += "&jetfile_dore_id=" + jetfile_dore_id;
		var url = '../api/jetfile/filedore';
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#feedback_dore_" + case_id + "_" + injury_id).html("DORE Filed");
					console.log(data); 
					//self.saveDOREFilingID(data.filing_id, data.filing_date, case_id, injury_id, jetfile_id)
				}
			}
		});
	},
	saveDOREFilingID: function(filing_id, filing_date, case_id, injury_id, jetfile_id) {
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&dore_filing_id=" + filing_id + "&dore_filing_date=" + filing_date;
		var url = "../api/jetfile/dore/filingid";
		
		$("#feedback_dore_" + case_id + "_" + injury_id).html("Saving Filing ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate that we are done;
					$("#feedback_dore_" + case_id + "_" + injury_id).html("Filed &#10003;");
				}
			}
		});
	},
	getDOREPDF: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		this.getPDF("dore", "dore", true, case_id, injury_id);
	},
	getDOREFullPDF: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		this.getPDF("dore_cover", "dore", false, case_id, injury_id);
	},
	sendLien: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id;
		
		var url = '../api/jetfile/sendlien';
		$("#feedback_lien_" + case_id + "_" + injury_id).html("Sending Lien to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					$("#feedback_lien_" + case_id + "_" + injury_id).html("<a style='color:white;background:red;padding:2px; font-size:1.2em' href='jetfiler/upload_lien.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "' target='_blank' title='Click to upload documents'>" + data.error.text + "</a>");
				} else {
					//console.log(data);
					var jetfile_case_id = data.case_id;
					var jetfile_lien_id = data.lien_id;

					var append_link = "&nbsp;|&nbsp;<a class='file_lien white_text' id='file_lien_" + case_id + "_" + injury_id + "_" + jetfile_id + "_" + jetfile_case_id + "_" + jetfile_lien_id + "'>Ready to File</a>";
					
					var review_link = $("lien_holder_" + case_id + "_" + injury_id).html().split("&nbsp;|&nbsp;")[0];
					
					$("#lien_holder_" + case_id + "_" + injury_id).html(review_link + append_link);
					
					//update the cse_jetfile
					self.updateJetfileLien(jetfile_case_id, jetfile_lien_id, jetfile_id, case_id, injury_id);
				}
			}
		});
	},
	updateJetfileLien: function(jetfile_case_id, jetfile_lien_id, jetfile_id, case_id, injury_id) {
		var self = this;
		var formValues = "jetfile_id=" + jetfile_id + "&jetfile_case_id=" + jetfile_case_id + "&jetfile_lien_id=" + jetfile_lien_id;
		var url = '../api/jetfile/updatelien';
		$("#feedback_lien_" + case_id + "_" + injury_id).html("Updating System with Lien Info&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//file the app
					$("#feedback_lien_" + case_id + "_" + injury_id).html("Lien Saved");
					//fileLien(jetfile_case_id, jetfile_lien_id);
				}
			}
		});
	},
	sendUnstruc:function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var unstruc_number = elementArray[5];
		
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&unstruc_number=" + unstruc_number;
		
		var url = '../api/jetfile/sendunstruc';
		$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Sending Unstruc to EAMS&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					 
				} else {
					//console.log(data);
					var jetfile_case_id = data.case_id;
					var jetfile_unstruc_id =data.unstruc_id;
					
					$("#jetfile_case_id_" + case_id + "_" + injury_id).html(jetfile_case_id);
					
					var append_link = "&nbsp;|&nbsp;<a class='file_unstruc white_text' id='file_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number + "'>Ready to File</a>";
					
					var review_link = $("#unstruc_holder_" + case_id + "_" + injury_id + "_" + unstruc_number).html().split("&nbsp;|&nbsp;")[0];
					
					$("#unstruc_holder_" + case_id + "_" + injury_id + "_" + unstruc_number).html(review_link + append_link);
					
					//update the cse_jetfile
					self.updateJetfileUnstruc(jetfile_case_id, jetfile_unstruc_id, jetfile_id, case_id, injury_id, unstruc_number);
				}
			}
		});
	},
	updateJetfileUnstruc:function(jetfile_case_id, jetfile_unstruc_id, jetfile_id, case_id, injury_id, unstruc_number) {
		var formValues = "jetfile_id=" + jetfile_id + "&case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_case_id=" + jetfile_case_id + "&jetfile_unstruc_id=" + jetfile_unstruc_id + "&unstruc_number=" + unstruc_number;
		var url = '../api/jetfile/updateunstruc';
		$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Updating System with Unstruc Info&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//file the app
					$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Unstruc Saved");
					//fileUnstruc(jetfile_case_id, jetfile_unstruc_id);
				}
			}
		});
	},
	fileUnstruc: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var injury_id = elementArray[3];
		var jetfile_id = elementArray[4];
		var jetfile_case_id = elementArray[5];
		var jetfile_unstruc_id = elementArray[6];
		var unstruc_number = elementArray[7];
		
		$("#" + element.id).fadeOut(function() {
			$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Filing Unstuc&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		});
		
		var formValues = "jetfile_case_id=" + jetfile_case_id;
		formValues += "&jetfile_unstruc_id=" + jetfile_unstruc_id;
		formValues += "&unstruc_number=" + unstruc_number;
		var url = '../api/jetfile/fileunstruc';
		
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Unstruc" + unstruc_number + " Filed");
					//console.log(data); 
					self.saveUnstrucFilingID(data.filing_id, data.filing_date, case_id, injury_id, jetfile_id, unstruc_number)
				}
			}
		});
	},
	saveUnstrucFilingID: function(filing_id, filing_date, case_id, injury_id, jetfile_id, unstruc_number) {
		var formValues = "case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_id=" + jetfile_id + "&unstruc_filing_id=" + filing_id + "&unstruc_filing_date=" + filing_date + "&unstruc_number=" + unstruc_number
		var url = "../api/jetfile/unstruc/filingid";
		
		$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Saving Filing ID&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//indicate that we are done;
					$("#feedback_unstruc_" + case_id + "_" + injury_id + "_" + unstruc_number).html("Filed &#10003;");
				}
			}
		});
	},
	getPDF:function(form, doc, blnSingleForm, case_id, injury_id) {
		var self = this;
		main_document = doc;
		
		if (typeof blnSingleForm == "undefined") {
			blnSingleForm = true;
		}
		var url = '../api/jetfile/getpdf';
		var formValues = "case_id=" + case_id + "&form=" + form;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if (!data) {
						self.requestPDF(form, main_document, blnSingleForm, case_id, injury_id);
						//alert("request");
					} else {
						var url = "../uploads/" + customer_id + "/" + case_id + "/jetfiler/" + data.document_filename;
						//window.open(url);
						self.requestPDF(form, main_document, blnSingleForm, case_id, injury_id);
					}
				}
		});
	},
	requestPDF: function(form, main_document, blnSingleForm, case_id, injury_id) {
		var self = this;
		
		if (typeof blnSingleForm == "undefined") {
			blnSingleForm = true;
		}
		var first_form = form;
		stack = form;
		
		var pos_description = "";
		var feedback_field = "";
		switch(first_form) {
			case "app":
				stack = 'app';
				feedback_field = "#feedback_app_";
				break;
			case "app_cover":
				first_form = "cover";
				stack = 'cover|app_cover|pos';
				feedback_field = "#feedback_app_";
				pos_description = "Application for Adjudication; compliance with Labor Code Section 4906(g); Fee Disclosure Statement; Venue Authorization";
				break;
			case 'dor':
				stack = 'dor';
				feedback_field = "#feedback_dor_";
				break;
			case 'dor_cover':
				first_form = "cover";
				stack = 'cover|dor|pos';
				feedback_field = "#feedback_dor_";
				pos_description = "Declaration of Readiness to proceed; 10770.6 Verification Form";
				break;
		}
		$(feedback_field + case_id + "_" + injury_id).html("Generating PDF&nbsp;<i class='icon-spin4 animate-spin' style='font-size:1.6em; color:white'></i>");
		
		var redirect = "filename";
		if (blnSingleForm) {
			redirect = "base64";
		}
		//var url = 'https://www.cajetfile.com/pdf_' + first_form + '.php';
		var url = '../api/jetfile/requestcreatepdf';
		var formValues = 'case_id=' + case_id + '&injury_id=' + injury_id + '&form=' + first_form + '&stack=' + stack + '&pos_description=' + pos_description + "&nopublish=y&redirect=" + redirect;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {					
					$(feedback_field + case_id + "_" + injury_id).html("PDF Generated&nbsp;&#10003;");
					
					var url = "../uploads/" + customer_id + "/" + case_id + "/jetfiler/" + data.filename;
					window.open(url);
				}
		});
		
	},
	/* solulab code start */
	fileSubmission:function(event){
		event.preventDefault();
		$button = $(event.currentTarget);
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[1];
		var jetfile_id = elementArray[2];
		var custo_id = elementArray[3];
		var injury_id = elementArray[4];
		var fileDetails = {
			jetfile_case_id : case_id,
			jetfile_id : jetfile_id,
			file:"C:/inetpub/wwwroot/iKase.website/uploads/"+custo_id+"/"+case_id+"/eams_forms/app_cover_final.pdf",
			customer_id:custo_id
		};
		$.ajax({
			url:'/api/docucents/filesubmission',
			type:'POST',
			data:fileDetails,
			dataType:"json",
			success:function(data){
				if(data.status == "200"){
				console.log(data['vendor_submittal_id']);
				$button.attr("disabled","disabled");
				// setTimeout(function(){
				// 	$('<a class="btn btn-block" style="color: white;padding: 12px 0;position: relative;font-size: 15px;text-align: left;" href="/docusent/getPOS.php?vendor_submittal_id='+$.trim(data['vendor_submittal_id'])+'" target="_blank">getPOS</a>').insertAfter($button);
				// },15000)
				alert(data.message);
				var newFragment = Backbone.history.getFragment($(this).attr('href'));
				if (Backbone.history.fragment == newFragment) {
					// need to null out Backbone.history.fragement because 
					// navigate method will ignore when it is the same as newFragment
					Backbone.history.fragment = null;
					Backbone.history.navigate(newFragment, true);
				}
		} else if(data.status == "404"){
				//$('#app_fullpdf_'+case_id+'_'+injury_id).trigger('click');
				$('#myModal4').modal('show');
				composeEams("jetfilerapp_" + case_id + "_21_" + injury_id, "app_cover");
		}else{
			alert(data.message);
		}
	}
		});
	}
	/* solulab code end */

});

window.jetfile_kase_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
	},
	render:function () {		
		var self = this;
		
		var jetfilings = this.collection.toJSON();
		var arrDayCount = [];
		var low_date = "";
		var high_date = "";
		var blnCC = false;
		_.each( jetfilings, function(jetfiling) {
			jetfiling.doi = moment(jetfiling.start_date).format("MM/DD/YYYY");
			if (jetfiling.end_date!="0000-00-00") {
				jetfiling.doi += "-" + moment(jetfiling.end_date).format("MM/DD/YYYY") + " CT";
			}
			jetfiling.app = "";
			if (jetfiling.info!="") {
				jetfiling.app = "<a href='jetfiler/app_1_2.php?case_id=" + jetfiling.case_id + "&injury_id=" + jetfiling.injury_id + "&jetfile_case_id=" + jetfiling.jetfile_case_id + "' target='_blank'>APP</a>";
			}
			jetfiling.dor = "";
			if (jetfiling.dor_info!="") {
				jetfiling.dor = "DOR";
			}
			jetfiling.dore = "";
			if (jetfiling.dore_info!="") {
				jetfiling.dore = "DORE";
			}
			jetfiling.lien = "";
			if (jetfiling.lien_info!="") {
				jetfiling.lien = "LIEN";
			}
			
			
		});
		
		
	}
});