var show_link_id;
var kase_list;
//these are managed in utilities
var start_kases = 0;
var previous_kases = -1;
var current_page = 0;
var arrKasePage = [];
//jetfile
var select_eams = "<select><option value=''>Choose Submission</option><option value='app'>App for ADJ</option><option value='dor'>DOR</option><option value='dore'>DORE</option><option value='lien'>Lien</option><option value='unstruc'>Unstructured</option></select>";
window.kase_listing_view = Backbone.View.extend({

    initialize:function () {
        //console.log("samba");
    },
	events: {
        "mouseover .kase_link":					"showNewWindowLink",
		"mouseout  .kase_link":					"hideNewWindowLink",
		"click  .kase_link":					"stopSearch",
		"mouseover .kase_windowlink":			"freezeNewWindowLink",
		"mouseout  .kase_windowlink":			"hideNewWindowLink",
		"click  .kase_windowlink":				"hideSearchResults",
		
		"click #intake_clear_search":			"clearSearch",
		"click #label_search_intakes":			"Vivify",
		"click #intakes_searchList":			"Vivify",
		"focus #intakes_searchList":			"Vivify",
		"blur #notes_searchList":				"unVivify",
		
		"click #new_kase":						"newKase",
		"click .compose_kase":					"editKase",
		"click .compose_new_note":				"newNotes",
		"click .compose_new_letter":			"newLetter",
		"click .compose_message":				"newMessage",
		"click .compose_task":					"newTask",
		"click .compose_phone":					"newPhone",
		"click .scrape_injury":					"scrapeDOI",
		"click .scrape_adj":					"scrapeADJ",
		"click .delete_kase":					"confirmdeleteKase",
		"click .delete_injury":					"confirmdeleteInjury",
		"click .delete_yes":					"deleteKase",
		"click .delete_no":						"canceldeleteKase",
		"click .letter_click":					"letterClick",
		"click .kase_adj_number":				"lookupADJ",
		"click #kase_show_all":					"showAll",
		"click #closed_kases":					"showClosedKases",
		"click #open_kases":					"showOpenKases",
		"change #kases_sort_by":				"sortKases",
		"change #kases_worker_filter":			"filterKasesByWorker",
		"change #kases_attorney_filter":		"filterKasesByAtty",
		"click #select_all_assign":				"selectAllAssign",
		"click #select_all_transfer":			"selectAllAssign",
		"click .select_letter":					"selectAllAlpha",
		"click #kases_assign_button":			"showAssignKase",
		"click .assign_kase":					"assignKase",
		"click .save_assign":					"saveAssign",
		"change .select_kase":					"selectKaseToAssign",
		"click #transfer_button":				"transferKases",
		"click #kase_print_listed":				"printListedKases",
		"click .kase_user": 					"expandSummary",
		"click .workload":						"showWorkload",
		"click .new_jet":						"newJet",
		"change .submission_select":			"selectSubmission",
		"click .review_jet":					"reviewJet",
		"click .jetfile":						"jetAPP",		
		"click .jetdor":						"jetDOR",
		"click .jetdore":						"jetDORE",
		"click .jetlien":						"jetLien",	
		"click #kase_report":					"printKaseReport",	
		"click #kase_export":					"exportKaseReport",	
		"click #kase_export_alpha":				"exportKaseReportAlpha",	
		"change #intake_type_filter":			"filterIntakes",
		"change #intake_status_filter":			"filterIntakes",
		"click #kase_listing_all_done":			"doTimeouts"
	},
    render:function () {	
		//empty cache
		resetCurrentContent();
			
		var self = this;
		kase_list = self.collection.toJSON();
		if (typeof this.model != "object") {
			var key = this.model;
			this.model = new Backbone.Model();
			this.model.set("key", key);
		}
		if (typeof this.model.get("key") == "undefined") {
			this.model.set("key", "");
		}
		if (typeof this.model.get("recent") == "undefined") {
			this.model.set("recent", "N");
		}
		var blnRecentList = ((this.model.get("recent")=="Y"));
		if (typeof this.model.get("search_parameters") == "undefined") {
			this.model.set("search_parameters", "");
		}
		if (typeof this.model.get("additional_rows") == "undefined") {
			this.model.set("additional_rows", false);
		}
		if (typeof this.model.get("holder")== "undefined") {
			this.model.set("holder", "content");
		}
		var key = this.model.get("key");
		
		var blnConsole = false;	//(typeof self.model.get("sort_by") != "undefined");
		var arrKaseNumbers = [];	//individual case numbers
		var arrWorkers = [];		//individual workers
		var arrWorkerNicknames = [];			//nicknames
		var arrWorkerOptions = [];		//individual workers options
		
		var arrAttys = [];		//individual atty
		var arrAttyNicknames = [];			//nicknames
		var arrAttyOptions = [];		//individual attys options
		
		//var key = "";
		var previous_kase = new Backbone.Model();
		var arrRemoveKases = [];
		previous_kase.set("case_id", "");
		previous_kase.set("adj_number", "");
		previous_kase.set("start_date", "");
		previous_kase.set("end_date", "");
		previous_kase = previous_kase.toJSON();
		
		var arrStartEnd = [];
		var blnRemovedKases = false;
		var rowCount = kase_list.length;
		_.each( kase_list, function(kase) {
			if (arrStartEnd.length == 0) {
				arrStartEnd.push(kase.start_kases);
				arrStartEnd.push(kase.previous_kases);
			}
			
			var kase_type = kase.case_type;
			var blnWCAB = isWCAB(kase_type);
			var blnJetFile = true;
			//(customer_id == 1033 || customer_id == 1069 || customer_id == 1062 || customer_id == 1094);
			//remove any repeats
			kase.skip_me = false;
			if (previous_kase.case_id == kase.case_id && previous_kase.adj_number == kase.adj_number && previous_kase.start_date == kase.start_date && previous_kase.end_date == kase.end_date) {
				//that should not happen
				kase.skip_me = true;
				rowCount--;
			}
			previous_kase.case_id =  kase.case_id;
			previous_kase.adj_number = kase.adj_number;
			var the_adj_number = kase.adj_number;
			//if adj
			if (kase.adj_number==null) {
				kase.adj_number = "";
			}
			if (kase.adj_number.indexOf("ADJ")==0) {
			//if (kase.adj_number!="") {
				if (!isNaN(kase.adj_number.replace("ADJ", ""))) {
					//valid
					the_adj_number = "<span class='kase_adj_number kase_" + kase.case_id + "' id='adj_number_" + kase.id + "' style='cursor:pointer; text-decoration:underline' title='Click to lookup ADJ on EAMS'>" + kase.adj_number + "</span>";
					if (blnWCAB && blnJetFile) {
						if (kase.full_name!="" && kase.full_name!=null && kase.employer!="" && kase.employer!=null) {
							var dor_suffix = "";
							var dor_color = "color:white";
							if (kase.jetfile_dor_id!="-1" && kase.jetfile_dor_id!="0" && kase.jetfile_dor_id!="") {
								dor_suffix = "&hellip;";
								dor_color = "color:orange";
							}
							
							//the_adj_number += "<br><a class='jetdor' id='jetdor_" + kase.case_id + "_" + kase.id + "' style='cursor:pointer; font-size:0.8em; " + dor_color + "'>DOR" + dor_suffix + "</a>";
							var dore_suffix = "";
							var dore_color = "color:white";
							if (kase.jetfile_dore_id!="-1" && kase.jetfile_dore_id!="0" && kase.jetfile_dore_id!="") {
								dore_suffix = "&hellip;";
								dore_color = "color:orange";
							}
							//the_adj_number += "&nbsp;|&nbsp;<a class='jetdore' id='jetdore_" + kase.case_id + "_" + kase.id + "' style='cursor:pointer; font-size:0.8em; " + dore_color + "'>DORE" + dore_suffix + "</a>";
							//the_adj_number += "&nbsp;|&nbsp;<a class='jetlien' id='jetlien_" + kase.case_id + "_" + kase.id + "' style='cursor:pointer; font-size:0.8em; color:white'>LIEN</a>";
						}
					}
				}
			}
			//NEW EAMS filing
			var new_eams = "<div id='new_jet_holder_" + kase.id + "'><a id='new_jet_" + kase.case_id + "_" + kase.id + "' class='new_jet white_text'>new&nbsp;jetfile</a>&nbsp;|&nbsp;<a id='injury_" + kase.id + "' href='#eams_injury_search/" + kase.case_id + "/" + kase.id + "' class='list-item_kase kase_link white_text' style='padding:2px; border:0px solid #CCC' target='_blank'>eams&nbsp;search</a></div>";
			var review_eams = "";
			if (kase.jetfile_id!="" && kase.jetfile_id!="-1") {
				review_eams = "<div id='new_jet_holder_" + kase.id + "'><a id='review_jet_" + kase.case_id + "_" + kase.id + "' class='review_jet white_text'>review jetfile</a></div>";
			} 
			if (the_adj_number=="") {
				the_adj_number = new_eams;
			} else {
				the_adj_number += review_eams;
			}
			
			kase.adj_number = the_adj_number;
			
			previous_kase.start_date = kase.start_date;
			previous_kase.end_date = kase.end_date;
			
        	if (kase.end_date == "00/00/0000"){
            	kase.end_date = "";
            }
            if (kase.end_date != "") {
                kase.end_date =  " - " + kase.end_date + " CT";
            }
			
			if (blnRecentList) {
				kase.recent_time_stamp = moment(kase.recent_time_stamp).format("MM/DD h:mma");
			}
			if (kase.defendant=="") {
            	kase.defendant = "No Defendant";
            }
			kase.employer_company = kase.employer;
			
			if (kase.employer=="") {
            	kase.employer = "No Employer";
            }
            if (kase.start_date=="" || kase.start_date=="00/00/0000") {
				var ct_dates = kase.ct_dates_note;
				if (ct_dates!="") {
					kase.start_date = "<span id='ct_missing_" + kase.id + "' style='background:red' title='Please re-enter CT dates'>" + ct_dates + "</span>&nbsp;<a id='scrape_injury_" + kase.id + "' class='scrape_injury white_text' style='font-size:0.8em;cursor:pointer'>eams update</a>";
				} else {
					kase.start_date = "No DOI";
				}
            } else {
            	kase.start_date = highLight(kase.start_date, key) + highLight(kase.end_date, key);
            }
			
			
			if (kase.ssn==null) {
				kase.ssn = "";
			}
            var applicant_ssn = kase.ssn;
            if (applicant_ssn.length == 9) {
                ssn = String(applicant_ssn);
                ssn1 = ssn.substr(0, 3);
                ssn2 = ssn.substr(3, 2);
                ssn3 = ssn.substr(5, 4);
                if (ssn != "XXXXXXXXX") {
                    ssn1Display = ssn1;
                    ssn2Display = ssn2;
                    ssn3Display = ssn3;
                }
                applicant_ssn = String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3)
            }
			kase.ssn = applicant_ssn;
			
			if (kase.full_name!=null) {
				kase.full_name = kase.full_name.trim();
			}
			
			if (blnWCAB) {
				if (kase.employer_id==-1) {
					kase.employer = "No Employer";
				}
				kase.case_type = "WCAB";
				if (kase_type=="WCAB_Defense") {
					kase.case_type += "&nbsp;DEF";
					
					kase.case_type = "<span style='background:aqua; color:red; font-weight:bold; padding:2px'>" + kase.case_type + "</span>";
				}
				if (kase.case_number == "" && kase.file_number != "") {
					kase.case_number = kase.file_number;
					
				}
			} else {
				if (kase.defendant_id==-1) {
					kase.defendant = "No Defendant";
				}
				//show something
				if (kase.case_number == "") {
					kase.case_number = kase.file_number;
				}
				kase.case_type = kase.case_type.replace("_", " ").capitalizeWords();
				kase.case_type = kase.case_type.replace(" ", "&nbsp;");
				if (kase.case_type=="Newpi" || kase.case_type=="Pi") {
					kase.case_type = "PI";
				}
			}
			
			if (kase.venue_abbr==null) {
				kase.venue_abbr = "";
			}
			kase.file_app = "";
			
			if (blnWCAB && blnJetFile) {
				kase.file_app = "<span style='font-size:1.6em; visibility:hidden'>&#9992;</span>";
			}
			if (blnWCAB && blnJetFile && kase.full_name!="") {
				kase.file_app = "<span style='font-size:1.6em; visibility:hidden'>&#9992;</span>";
				//show file app link
				//var blnValidStartDate = (kase.start_date!="" && kase.start_date!="No DOI");
				//if ((kase.adj_number == "" || kase.adj_number == "Unassigned") && !blnValidStartDate) {
				if ((kase.adj_number == "" || kase.adj_number == "Unassigned")) {
					if (kase.adj_number != "") {
						kase.adj_number += "<br>";
					}
					kase.file_app = "<a id='jetfile_" + kase.case_id + "_" + kase.id + "' title='Click to fill out the App for ADJ' class='jetfile white_text' style='cursor:pointer; color:#AEB6BF; font-size:1.6em'>&#9992;</a>";
				} else {
					//if (kase.adj_number.indexOf("Click to lookup ADJ on EAMS") < 0 && kase.adj_number.indexOf("new_jet") < 0) {
						kase.adj_number = highLight(kase.adj_number, self.model.get("key")).replaceAll(",", ", ");
					//}
				}
			} else {
				//if (kase.adj_number.indexOf("Click to lookup ADJ on EAMS") < 0 && kase.adj_number.indexOf("new_jet") < 0) {
					kase.adj_number = highLight(kase.adj_number, self.model.get("key")).replaceAll(",", ", ");
				//}
				//do we need an eams update?
				if ((kase.main_injury_type=="IMPORT" || kase.main_injury_type=="IMPORT2") && kase.adj_number!="") {
					kase.adj_number += "<br>&nbsp;<a id='scrape_adj_" + kase.id + "' class='scrape_adj  kase_" + kase.case_id + " white_text' style='font-size:0.8em;cursor:pointer; background:orange; color:black; padding:1px' title='Click to update the imported injury via EAMS'>eams update</a>";
				}
			}
			kase.case_status = kase.case_status.replaceAll(' ', '&nbsp;');
			if (kase.case_status.toLowerCase().indexOf("cl")==0) {
				kase.case_status = "<span style='background:red;color:white;padding:1px'>" + kase.case_status + "</span>";
				if (kase.closed_date!="") {
					kase.case_status += "<br>" + moment(kase.closed_date).format("MM/DD/YYYY") + "";
				}
			}
			if (kase.full_name!="" && kase.full_name!=null) {
            	kase.alpha_name = kase.full_name.capitalizeWords();
				if (kase.alpha_name=="Vs") {
					kase.alpha_name = "*";
				}
				kase.full_name = kase.full_name.replaceAll(" ", "&nbsp;")
				if (blnWCAB) {
					if (kase.employer == "No Employer") {
						kase.employer = "&nbsp;|&nbsp;<a href='#eams_injury_search/" + kase.case_id + "/" + kase.id + "' class='list-item_kase kase_link white_text' style='padding:2px; border:0px solid #CCC' target='_blank' title='No Employer; must search EAMS to find parties'>eams&nbsp;search</a>";
					} else {
						kase.employer = "&nbsp;vs&nbsp;<a href='#parties/" + kase.case_id + "/" + kase.employer_id + "/employer' class='list-item_kase' style='color:white'>" + highLight(kase.employer, key).replaceAll(" ", "&nbsp;") + "</a>";
					}
					kase.full_name = "<a href='#applicant/" + kase.case_id + "' class='list-item_kase' style='color:white'>" + highLight(kase.full_name, key) + "</a>" + kase.employer;
				} else {
					if (typeof kase.case_name == "undefined") {
						kase.case_name = "";
					}
					if (kase.case_name != "") {
						kase.full_name = highLight(kase.case_name, key);
					} else {
						kase.full_name = "<a href='#applicant/" + kase.case_id + "' class='list-item_kase' style='color:white'>" + highLight(kase.full_name, key) + "</a>&nbsp;vs&nbsp;<a href='#parties/" + kase.case_id + "/" + kase.defendant_id + "/defendant' class='list-item_kase' style='color:white'>" + highLight(kase.defendant, key).replaceAll(" ", "&nbsp;") + "</a>";
					}
				}
			} else {
	            kase.full_name = "No Applicant";
				kase.alpha_name = "*";
            }
			
			var clean_name = "";
			if (blnWCAB) {
				if (kase.name!=kase.case_name && kase.case_name!="") {
					kase.name = kase.case_name;
				}
				if (kase.name!=null) {
					clean_name = kase.name;
				}
				if (clean_name=="" && kase.case_name!="") {
					clean_name = kase.case_name;
				}
			} else {
				if (kase.case_name!=null) {
					clean_name = kase.case_name;
				}
				if (clean_name=="" && kase.name!="") {
					clean_name = kase.name;
				}
				if (kase.name!=null && kase.case_name!=null) {
					//final catch for imported case names that maybe off somehow
					if (kase.name!="" && kase.case_name!="" && kase.name!=kase.case_name) {
						clean_name = kase.name;
					}
				}
			}
			if (clean_name == null) {
				clean_name = "";
			}
			if (clean_name=="" && kase.full_name!="") {
				clean_name = removeHtml(kase.full_name);
				clean_name = clean_name.replaceAll("&nbsp;", " ");
			}
			if (clean_name == null) {
				clean_name = "";
			}
			if (clean_name.length > 45) {
				clean_name = clean_name.substring(0, 45) + "...";
				clean_name = clean_name.replaceAll(" ", "&nbsp;");
			}
			kase.clean_name = clean_name;
			
			if (kase.case_name!="" && kase.name=="") {
				kase.name = kase.case_name;
				if (kase.full_name=="") {
					kase.full_name = kase.name;
				}
			} else {
				kase.name = "";	//kase.name.replace(' - 00/00/0000', '');
			}
			
			if (self.model.get("sort_by")=="last_name" && kase.last_name!="" && kase.last_name!=null) {
				kase.alpha_name = kase.last_name.capitalizeWords();
			}
			if (kase.worker_full_name==null) {
				kase.worker_full_name = "";
			}
			if (kase.attorney_full_name==null) {
				kase.attorney_full_name = "";
			}
			
			if (kase.worker_name=="" && kase.worker_full_name!="") {
				kase.worker_name = kase.worker_full_name.firstLetters();
			}
			
			if (kase.worker!="") {
				if (arrWorkers.indexOf(kase.worker) < 0) {
					if (!isNaN(kase.worker)) {
						//, activated:"Y"
						var the_worker = worker_searches.findWhere({id:kase.worker});
					} else {
						//, activated:"Y"
						var the_worker = worker_searches.findWhere({nickname:kase.worker});
					}
					if (typeof the_worker != "undefined") {
						arrWorkers.push(kase.worker);
						arrWorkerNicknames.push(the_worker.get("nickname"));
						//arrWorkernames[the_worker.get("nickname")] = the_worker.get("user_name");
					}
				}
			}
			if (kase.worker_name=="" && kase.worker!="") {
				kase.worker_name = kase.worker;
			}
			
			//atty
			if (kase.attorney_name=="" && kase.attorney_full_name!="") {
				kase.attorney_name = kase.attorney_full_name.firstLetters();
			}
			if (kase.attorney!="") {
				if (arrAttys.indexOf(kase.attorney) < 0) {
					if (!isNaN(kase.attorney)) {
						var the_attorney = worker_searches.findWhere({id:kase.attorney, activated:"Y"});
						arrAttys.push(kase.attorney);
						if (typeof the_attorney != "undefined") {
							arrAttyNicknames.push(the_attorney.get("nickname"));
						}
					} else {
						var the_attorney = worker_searches.findWhere({nickname:kase.attorney, activated:"Y"});
						arrAttyNicknames.push(kase.attorney);
						if (typeof the_attorney != "undefined") {
							arrAttys.push(the_attorney.get("user_id"));
						}
					}
					
				}
			}
			if (kase.attorney_name=="" && kase.attorney!="") {
				kase.attorney_name = kase.attorney;
			}

			//attorney
			kase.attorney_name = "<span title='" + kase.attorney_full_name + "'>" + kase.attorney_name + "</span>";
			
			//imported
			if (kase.source!="") {
				kase.source = "<span title='Imported'>*</span>";
			}
			
			//dob
			var dob = kase.dob;
			var app_dob = kase.app_dob;
			if (dob == "" && app_dob!="") {
				kase.dob = app_dob;
			}
			if (kase.dob==null) {
				kase.dob = "";
			}
			kase.lien_border = "padding:2px";
			if (kase.lien_id > 0) {
				kase.lien_border = "background: black; padding:2px";
			}
			kase.settlement_border = "padding:4px 4px 4px 2px";
			if (kase.settlement_id > 0 || kase.fee_id > 0) {
				kase.settlement_border = "background: black; padding:4px 4px 4px 2px";
			}
			
			//a1
			if (user_data_path == 'A1') {
				if (kase.case_number=="" && kase.cpointer!="") {
					kase.case_number = kase.cpointer;
				}
			}
			if (!kase.skip_me) {
				if (arrKaseNumbers.indexOf(kase.case_number) < 0) {
					arrKaseNumbers.push(kase.case_number);
				}
			}
			kase.occupation = kase.occupation.replace(' ', '&nbsp;');
			kase.occupation = kase.occupation.replace('/', ' , ');
			kase.occupation = kase.occupation.replace(',', ' , ');
			
			//exact match?
			if (typeof kase.exact_match=="undefined") {
				kase.exact_match = 0;
			}
		 });
		 
		//arrRemoveKases.forEach(function(element) { 
		
		//});
		var kase_workers = "";
		arrWorkerNicknames = _.uniq(arrWorkerNicknames);
		if (arrWorkerNicknames.length > 1) {
			arrWorkerNicknames.sort();
			var the_option = "<option value=''>By Coordinator</option>";
			arrWorkerOptions.push(the_option);
			arrWorkerNicknames.forEach(function(element, index, array) {
				//, activated:"Y"
				var the_worker = worker_searches.findWhere({nickname:element});
				
				if (typeof the_worker != "undefined") {
					var the_option = "<option value='" + the_worker.get("nickname") + "'>" + the_worker.get("nickname") + " - " + the_worker.get("user_name").toLowerCase().capitalizeWords() + "</option>";
					arrWorkerOptions.push(the_option);
				}
			});
			 
			kase_workers = arrWorkerOptions.join("\r\n");
		}
		
		var kase_attys = "";
		arrAttyNicknames = _.uniq(arrAttyNicknames);
		if (arrAttyNicknames.length > 1) {
			arrAttyNicknames.sort();
			var the_option = "<option value=''>By Attorney</option>";
			arrAttyOptions.push(the_option);
			arrAttyNicknames.forEach(function(element, index, array) {
				var the_attorney = worker_searches.findWhere({nickname:element, activated:"Y"});
				
				if (typeof the_attorney != "undefined") {
					var the_option = "<option value='" + the_attorney.get("nickname") + "'>" + the_attorney.get("nickname") + " - " + the_attorney.get("user_name").toLowerCase().capitalizeWords() + "</option>";
					arrAttyOptions.push(the_option);
				}
			});
			 
			kase_attys = arrAttyOptions.join("\r\n");
		}
		
		if (this.model.get("holder") == "preview_pane") {
			if (self.model.get("sort_by")!="") {
				var sorted_collection = new KaseCollection();
				sorted_collection.reset(kase_list);
				sorted_collection.comparator = "alpha_name";
				sorted_collection.sort();
				
				kase_list = sorted_collection.toJSON();;
			}
		}
		self.collection = kase_list;
		
		$(this.el).html(this.template({kase_collection: self.collection, holder: self.model.get("holder"),  key: self.model.get("key"), additional_rows: self.model.get("additional_rows"), kases_count: rowCount, kase_workers: kase_workers, kase_attys: kase_attys, blnRecentList: blnRecentList}));
		
		if (blnInitialSetting) {
			blnInitialSetting = false;
		} else {
			if (document.location.pathname.indexOf("v8") > -1) {
				if ($("#content").css("top")=="60px") {
					$("#content").css("top", "0px");
				}
				if ($("#left_sidebar").css("top")=="65px" || $("#left_sidebar").css("top")=="auto") {
					$('#left_sidebar').css("margin-top", "0px");
				}
			}
		}
		
		return this;
    },
	newJet: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		var this_select = select_eams.replace("<select>", "<select id='submission_" + case_id + "_" + injury_id + "' class='submission_select'>");
		$("#new_jet_holder_" + injury_id).html(this_select);
	},
	selectSubmission: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		var url = "jetfiler/";
		switch (element.value) {
			case "":
				return;
				break;
			case "app":
				url += "app_1_2.php?case_id=" + case_id + "&injury_id=" + injury_id;
				break;
			case "dor":
				url += "dor.php?case_id=" + case_id + "&injury_id=" + injury_id;
				break;
			case "dore":
				url += "dor_e.php?case_id=" + case_id + "&injury_id=" + injury_id;
				break;
			case "lien":
				url += "lien.php?case_id=" + case_id + "&injury_id=" + injury_id;
				break;
			case "unstruc":
				url += "unstructured.php?case_id=" + case_id + "&injury_id=" + injury_id;
				break;
		}
		
		window.open(url);
	},
	reviewJet: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		document.location.href = "#jetfiles/" + injury_id;
	},
	jetAPP: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		window.open("jetfiler/app_1_2.php?case_id=" + case_id + "&injury_id=" + injury_id);
	},
	jetDOR: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		window.open("jetfiler/dor.php?case_id=" + case_id + "&injury_id=" + injury_id);
	},
	jetDORE: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		window.open("jetfiler/dor_e.php?case_id=" + case_id + "&injury_id=" + injury_id);
	},
	jetLien: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 2];
		var injury_id = arrElement[arrElement.length - 1];
		
		window.open("jetfiler/lien.php?case_id=" + case_id + "&injury_id=" + injury_id);
	},
	exportKaseReportAlpha: function(event) {
		event.preventDefault();
		
		var theworker = $("#kases_worker_filter");
		var val_worker = $.trim($(theworker).val()).replace(/ +/g, ' ').toLowerCase();
		var theattorney = $("#kases_attorney_filter");
		var val_attorney = $.trim($(theattorney).val()).replace(/ +/g, ' ').toLowerCase();
	
		if (val_attorney=="") {
			val_attorney = "_";
		}
		if (val_worker=="") {
			val_worker = "_";
		}
		var url = "reports/export_cases_filtered.php?alpha=&atty=" + val_attorney + "&coord=" + val_worker + "&api=" + kase_url;
		
		window.open(url);
	},
	exportIntakeReport: function() {
		var intake_type_filter = $("#intake_type_filter").val();
		var intake_status_filter = $("#intake_status_filter").val();
		var current_letter = "_";
		if (typeof this.model.get("current_letter") != "undefined") {
			current_letter = this.model.get("current_letter");
		}
		
		var url = "reports/export_intakes.php?type=" + intake_type_filter + "&status=" + intake_status_filter + "&letter=" + current_letter;
		
		window.open(url);
	},
	exportKaseReport: function(event) {
		event.preventDefault();
		if ($("#kase_status_title").html()=="Intake") {
			this.exportIntakeReport();
			return;
		}
		var theworker = $("#kases_worker_filter");
		var val_worker = $.trim($(theworker).val()).replace(/ +/g, ' ').toLowerCase();
		var theattorney = $("#kases_attorney_filter");
		var val_attorney = $.trim($(theattorney).val()).replace(/ +/g, ' ').toLowerCase();
	
		if (val_attorney=="") {
			val_attorney = "_";
		}
		if (val_worker=="") {
			val_worker = "_";
		}
		var url = "reports/export_cases_filtered.php?atty=" + val_attorney + "&coord=" + val_worker + "&api=" + kase_url;
		
		window.open(url);
	},
	printIntakeReport: function() {
		var intake_type_filter = $("#intake_type_filter").val();
		var intake_status_filter = $("#intake_status_filter").val();
		var current_letter = "_";
		if (typeof this.model.get("current_letter") != "undefined") {
			current_letter = this.model.get("current_letter");
		}
		var url = "report.php#kases/intakes/" + intake_type_filter + "/" + intake_status_filter + "/" + current_letter;
		
		window.open(url);
	},
	printKaseReport: function(event) {
		event.preventDefault();
		
		//if summary
		if (document.location.hash.indexOf("#employeekases")==0) {
			var user_id = $("#user_id").val();
			var url = "report.php#kasessummary/" + user_id;
			window.open(url);
			return;
		}
		if ($("#kase_status_title").html()=="Intake") {
			this.printIntakeReport();
			return;
		}
		var theworker = $("#kases_worker_filter");
		var val_worker = $.trim($(theworker).val()).replace(/ +/g, ' ').toLowerCase();
		var theattorney = $("#kases_attorney_filter");
		var val_attorney = $.trim($(theattorney).val()).replace(/ +/g, ' ').toLowerCase();
	
		var url = "report.php#kases/active";
		if (val_attorney=="") {
			val_attorney = "_";
		}
		if (val_worker=="") {
			val_worker = "_";
		}
		
		var current_letter = "";
		if (typeof this.model.get("current_letter") != "undefined") {
			current_letter = this.model.get("current_letter");
		}
		if (val_worker!="" || val_attorney!="") {
			url = "report.php#kases/activefilter/" + val_attorney + "/" + val_worker;
		}
		
		if (current_letter!="") {
			url = "report.php#kases/activealphafilter/" + val_attorney + "/" + val_worker + "/" + current_letter;
		}
		
		window.open(url);
	},
	stopSearch: function(event) {
		$("#search_results").html("");
		blnSearchingKases = false;
	},
	filterKasesByWorker: function(event) {
		filterKases("worker");
		
		$("#kases_assign_button").css("visibility", "visible");
	},
	filterKasesByAtty: function(event) {
		filterKases("attorney");
	},
	filterIntakes: function() {
		var type = $("#intake_type_filter").val();
		var filter = $("#intake_status_filter").val();
		window.Router.prototype.listIntakes(filter, type);
	},
	scrapeDOI: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var injury_id = arrElement[arrElement.length - 1];
		
		var adj_number = prompt("Please enter the ADJ", "ADJ");
		
		adj_number = adj_number.trim();
		var scrape = new Scrape({adj_number: adj_number});
		var element_html = $("#" + element.id).html();
		$("#" + element.id).html("scraping...");
		scrape.fetch({
			error: function (collection, response, options) {
                // you can pass additional options to the event you trigger here as well
                //self.trigger('errorOnFetch');
				$("#" + element.id).html("Connection error.");
				return;
            },
			success: function (data) {
				var scrape = data.toJSON();	
				
				if (typeof scrape.error	!= "undefined") {
					$("#" + element.id).html(scrape.error);
					return;
				}
				//run this after animation
				var applicant = scrape.applicant;
				var doi = applicant.start_date;
				if (applicant.end_date!="0000-00-00") {
					doi += " - " + applicant.end_date + " CT";
				} else {
					applicant.end_date = "";
				}
				var formValues = "table_name=injury&id=" + injury_id;
				formValues += "&adj_number=" + adj_number;
				formValues += "&start_date=" + applicant.start_date + "&end_date=" + applicant.end_date;
				
				var url = "api/injury/update";
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { // If not
								$("#" + element.id).html("<span style='background:#3F9; color:white'>&#10003;</span>");
								$("#ct_missing_" + injury_id).css("background", "none");
								
								//update the doi
								var kase = kases.findWhere({id: injury_id});
								kase.set("start_date", applicant.start_date);
								kase.set("end_date", applicant.end_date);
							}
						}
				});
				
				//body parts
				var formValues = "injury_id=" + injury_id + "&scraped=y";
				var bodyparts = scrape.bodyparts;
				var iCounter = 1;
			    _.each( bodyparts, function(bodypart) {
					formValues += "&bodypart" + iCounter + "=" + bodypart.name;
					iCounter++;
				});
				
				var url = "api/bodyparts/add";
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { // If not
								$("#" + element.id).html("<span style='background:green; color:white'>&#10003;</span>");
								$("#ct_missing_" + injury_id).css("background", "none");
							}
						}
				});
			}
		});
	},
	scrapeADJ: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var injury_id = arrElement[arrElement.length - 1];
		
		var adj_number = $("#adj_number_" + injury_id).html();
		
		adj_number = adj_number.trim();
		var scrape = new Scrape({adj_number: adj_number});
		var element_html = $("#" + element.id).html();
		$("#" + element.id).html("scraping...");
		
		scrape.fetch({
			error: function (collection, response, options) {
                // you can pass additional options to the event you trigger here as well
                //self.trigger('errorOnFetch');
				$("#" + element.id).html("Connection error.");
				return;
            },
			success: function (data) {
				var scrape = data.toJSON();	
				
				if (typeof scrape.error	!= "undefined") {
					$("#" + element.id).html(scrape.error);
					return;
				}
				//run this after animation
				var applicant = scrape.applicant;
				var doi = applicant.start_date;
				if (applicant.end_date!="0000-00-00") {
					doi += " - " + applicant.end_date + " CT";
				} else {
					applicant.end_date = "";
				}
				
				var formValues = "table_name=injury&id=" + injury_id;
				formValues += "&adj_number=" + adj_number;
				formValues += "&type=UPDATED";
				formValues += "&start_date=" + applicant.start_date + "&end_date=" + applicant.end_date;
				
				var theaction = "update";
				var url = "api/injury/" + theaction;
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error messages
								saveFailed(data.error.text);
							} else { // If not
								$("#" + element.id).html("<span style='background:#3F9; color:white'>&#10003;</span>");
								setTimeout(function() {
									$("#" + element.id).html("");
								}, 2500);
								//this will bring up the adj search
								composeEamsImport(adj_number, "import");
							}
						}
				});
				if (injury_id > 0) {
					//body parts
					self.scrapeBodyParts(injury_id, scrape);
				}
			}
		});
	},
	scrapeBodyParts: function(injury_id, scrape) {
		var self = this;
		var formValues = "injury_id=" + injury_id + "&scraped=y";
		var bodyparts = scrape.bodyparts;
		var iCounter = 1;
		_.each( bodyparts, function(bodypart) {
			formValues += "&bodypart" + iCounter + "=" + bodypart.name;
			iCounter++;
		});
		//per thomas, update body parts as well 8/31/2015
		var url = "api/bodyparts/add";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						//bodyparts are saved
					}
				}
		});
	},
	selectAllAlpha: function(event) {
		var element = event.currentTarget;
		var blnChecked = element.checked;
		var arrID = element.id.split("_");
		var letter = arrID[arrID.length - 1];
		
		var selects = $(".select_kase_" + letter);
		var arrLength = selects.length;
		for(var i =0; i < arrLength; i++) {
			selects[i].checked = blnChecked;
		}
		
		if (this.model.get("holder") == "preview_pane") {
			$("#transfer_button").fadeIn();
		}
	},
	selectAllAssign: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var blnChecked = element.checked;
		var arrID = element.id.split("_");
		var letter = arrID[arrID.length - 1];
		
		var select_kases = $(".select_kase");
		var arrLength = select_kases.length;
		for(var i = 0; i < arrLength; i++) {
			var select_kase = select_kases[i];
			var select_id = select_kase.id;
			
			var display = $("#" + select_id).parent().parent().css("display");
			if (display!="none") {
				document.getElementById(select_id).checked = blnChecked;
			}
		}
		
		this.showAssignCoordinator();
		
		if (this.model.get("holder") == "preview_pane") {
			$("#transfer_button").fadeIn();
		}
	},
	showAssignKase: function(event) {
		event.preventDefault();
		$(".select_kase").show();
		$("#kases_assign_button").fadeOut(function() {
			$("#assign_kase_instructions").fadeIn();
			
			setTimeout(function() {
				$("#assign_kase_instructions").hide();
				$("#select_all_assign_holder").show();
			}, 1500);
		});
	},
	showAssignCoordinator: function() {
		//first make sure at least one is checked
		if ($("#kases_assign_button").css("display")!="none") {
			$("#kases_assign_button").hide();
		}
		$("#assign_kase").css("visibility", "hidden");
		$("#assign_kase").show();
		
		
		var blnChecked = false;
		var selecteds = $(".select_kase");
		var arrLength = selecteds.length;
		for(var i = 0; i < arrLength; i++) {
			var select_kase = selecteds[i];
			var select_id = select_kase.id;
			
			var display = $("#" + select_id).parent().parent().css("display");
			if (display!="none") {				
				if (select_kase.checked) {
					blnChecked = true;
					break;
				}
			}
		}
		
		if (blnChecked) {
			//already coordinatin..
			if ($(".workerInput_holder").css("display")!="none") {
				return;
			}
			$("#assign_kase").css("visibility", "visible");
			$("#select_all_assign_holder").show();
		} else {
			$("#assign_kase").css("visibility", "hidden");
			$(".workerInput_holder").hide();
		}
	},
	assignKase: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		//var letter = arrID[arrID.length - 1];
		
		//first make sure at least one is checked
		var blnChecked = false;
		var selecteds = $(".select_kase");
		var arrLength = selecteds.length;
		for(var i = 0; i < arrLength; i++) {
			if (selecteds[i].checked) {
				blnChecked = true;
				break;
			}
		}
		
		if (!blnChecked) {
			alert("You must select at least 1 kase to Assign to Coordinator");
			return;
		}
		$("#assign_kase").fadeOut(
			function() {
				var theme_3 = {
						theme: "kase", 
						tokenLimit: 1,
						onAdd: function(item) {
							$("#save_assign").css("visibility", "visible");
						},
						onDelete: function() {
							$("#save_assign").css("visibility", "hidden");
						}
					};
				if ($(".workerInput_holder" + " .token-input-list-kase").length > 0) {
					$(".workerInput_holder" + " .token-input-list-kase").remove();
				}
				$(".workerInput_holder").fadeIn();
				$("#workerInput_holder").tokenInput("api/user", theme_3);
				$(".token-input-list-kase").css("width", "210px");
				$(".token-input-dropdown-kase").css("width", "210px");
				$("#select_all_assign_holder").hide();
				
				//slght delay focusing to let things settle first
				setTimeout(function() {
					$("#token-input-workerInput_holder").focus();
				}, 777);
			}
		);
	},
	selectKaseToAssign: function(event) {
		event.preventDefault();
		/*
		var element = event.currentTarget;	
		var arrID = element.id.split("_");
		var case_id = arrID[arrID.length - 1];
		
		
		if (element.checked) {
			$(".kase_data_row_" + case_id).css("background", "#EDEDED");
		} else {
			$(".kase_data_row_" + case_id).css("background", "#FFF");
		}
		*/
		
		this.showAssignCoordinator();
		
		if (this.model.get("holder") == "preview_pane") {
			$("#transfer_button").fadeIn();
		}
	},
	transferKases: function(event) {
		event.preventDefault();
		var arrCheckBoxes = $('.select_kase');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox =  checkbox_element.split("_");
				var case_id = arrCheckbox[arrCheckbox.length - 1];
				arrChecked.push(case_id);
			}
		}
		var ids = arrChecked.join(", ");
		
		composeTransferKase(ids, "kase");
	},
	saveAssign: function(event) {
		var self = this;
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		//var letter = arrID[arrID.length - 1];
		$(".workerInput_holder").fadeOut();
				
		var selecteds = $(".select_kase");
		var arrLength = selecteds.length;
		var arrKaseID = [];
		for(var i = 0; i < arrLength; i++) {
			if (selecteds[i].checked) {
				var arrButton = selecteds[i].id.split("_");
				var kase_id = arrButton[arrButton.length - 1];
				arrKaseID.push(kase_id);
			}
		}
		var case_ids = arrKaseID.join(",");
		var worker = $("#workerInput_holder").val();
		var the_worker = worker_searches.findWhere({"user_id":worker});
		var nickname = the_worker.get("nickname");
		
		//update
		var formValues = "case_ids=" + case_ids;
		formValues += "&worker=" + worker;
		
		var url = "api/kases/assign/worker";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						var arrLength = arrKaseID.length;
						var worker = removeHtml($(".token-input-token-kase").html());
						$(".workerInput_holder .token-input-list-kase").remove();
						//worker = worker.substr(0, worker.length - 1);
						
						for(var i = 0; i < arrLength; i++) {
							var case_id = arrKaseID[i];
							var current_background = $(".kase_data_row_" + case_id).css("background");
							
							$(".kase_data_row_" + case_id).css("background", "lime");
							
							
							$(".worker_span_" + case_id).html(nickname);
							
							$("#select_kase_" + case_id).attr("checked", false);
							self.resetRow(case_id, current_background);
						}
						
						$(".workerInput_holder").val("");
						$(".workerInput_holder").fadeOut();
						
						//the button should come back
						$("#reassign_holder").show();
						$("#kases_assign_button").fadeIn(function() {
							$("#kases_assign_button").css("visibility", "visible");
						});
						
						//reset the kases						
						kases.fetch({
							success: function (data) {
								//done
							}
						});
					}
				}
		});
	},
	resetRow: function(case_id, current_background) {
		setTimeout(function() {
			$(".kase_data_row_" + case_id).css("background", current_background);
		}, 2500);
	},
	expandSummary: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		hidePreview();
		
		var self = this;
				
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			var worker_kases = new KaseWorkerCollection({ user_id: id });
			worker_kases.fetch({
				success: function (data) {
					if (data.length > 0) {
						var worker = worker_searches.findWhere({id: id});
						$("#preview_pane").hide();
						var kase_listing_info = new Backbone.Model;
						var user_name = worker.get("user_name");
						var title = user_name + " Kases";
						kase_listing_info.set("title", title);
						kase_listing_info.set("holder", "preview_pane");
						kase_listing_info.set("sort_by", "last_name");
						kase_listing_info.set("homepage", true);
						kase_listing_info.set("user_id", id);
						kase_listing_info.set("user_name", user_name);
						$('#preview_pane').html(new kase_listing_view({collection: data, model: kase_listing_info}).render().el);
						$("#preview_pane").removeClass("glass_header_no_padding");
						
					} else {
						$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
					}
				}
			});
		});
		
	},
	showWorkload: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var url = "api/kases_workload/" + id;
			
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			success:function (data) {
				if (data.length > 0) {
					$("#kase_summary_list_outer_div").css("width", "25%");
					$("#preview_pane_holder").css("width", "75%");
					$("#preview_pane_holder").css("height", "");
					$("#preview_pane_holder").css("background", "url(img/glass_dark.png) repeat");
					$("#preview_block_holder").css("overflow-y", "");
					$("#preview_pane_holder").show();
					$("#preview_pane").html(data);
					$("#workload_title").hide();
					$(".workload_table").css("min-width", "350px");
					$("#preview_pane").show();
					$(".print_workload").show();
					
				}
			}
		});
	},
	printListedKases: function(event) {
		var self = this;
		window.open("report.php#kases/last");
	},
	sortKases: function(event) {
		var self = this;
		var sorted_collection = new KaseCollection();
		sorted_collection.reset(this.collection);
		sorted_collection.comparator = event.target.value;
		sorted_collection.sort();
		this.collection = new Backbone.Collection(sorted_collection.toJSON());
		this.model.set("sort_by", sorted_collection.comparator);
		this.render();
		
		setTimeout(function() {
			self.doTimeouts(event);
		}, 500);
	},
	doTimeouts:function (event) {
		var self = this;
		/*
		//cancelled per steve at dordulian 10/16/2017
		if (customer_id==1033 || (customer_id==1075 && login_nickname=="SXG") || (customer_id==1075 && login_nickname=="MA")) {
			setTimeout(function() {
				$("table.tablesorter tbody tr:nth-child(even)").css("background", "white");
				$("table.tablesorter tbody td").css("color", "black");
				$("table.tablesorter tbody td").css("font-weight", "bold");
				$("table.tablesorter .white_text").addClass("black_text");
				$("table.tablesorter .white_text").removeClass("white_text");
				$("table.tablesorter a.list-item_kase").css("color", "black");
				$("table.tablesorter .glyphicon-plus").css("color", "black");
				$("table.tablesorter .glyphicon-upload").css("color", "#999999");
				$("table.tablesorter .glyphicon-upload").css("color", "blue");
				$("table.tablesorter .glyphicon-pencil").css("color", "green");
				$("table.tablesorter .glyphicon-usd").css("color", "#4c1761");
				$("table.tablesorter .glyphicon-file").css("color", "#e9480b");
				$("table.tablesorter .glyphicon-inbox").css("color", "#41ed08");
				$("table.tablesorter tbody tr.odd td").css("background", "#EDEDED");
				$(".highlighted_text").css("color", "red");
			}, 777);
		}
		*/
		$('#ikase_loading').html("");
		//$("#search_modifiers").fadeOut();
		var current_letter = "";
		$(".letter_click").css("color","#000");
		$(".letter_click").css("background","#E2A624");
		$(".letter_click").css("cursor","context-menu");
		$(".letter_click").addClass("turned_off");
		var first_letter = "";
		var second_letter = "";
		var blnExactMatch = false;
		var search_term = $("#srch-term").val().toLowerCase();
		_.each( kase_list, function(kase) {
			//we might have a new letter
			var the_letter = "";
			/*
			if (kase.alpha_name.trim()!="") {
				the_letter = kase.alpha_name.trim().charAt(0);
			}
			*/
			if (kase.last_name!="") {
				the_letter = kase.last_name.trim().charAt(0);
			} else {
				var arrName = kase.alpha_name.split(" ");
				var last_name = arrName[arrName.length - 1];
				the_letter = last_name.trim().charAt(0);
			}
			if (!blnExactMatch) {
				if (kase.case_number.toLowerCase()==search_term) {
					blnExactMatch = true;
				}
				if (kase.last_name.toLowerCase()==search_term) {
					blnExactMatch = true;
				}
			}
			
			if (first_letter=="") {
				first_letter = the_letter;
				//however
				if (kase.case_number==search_term || blnExactMatch) {
					//let's force it the issue
					first_letter = "Z";
				}
			} else {
				if (second_letter=="") {
					second_letter = the_letter;
				}
			}
			var the_last = "";
			if (kase.last_name.trim()!="") {
				the_last = kase.last_name.trim().charAt(0);
			}
			if (self.model.get("sort_by")=="last_name") {
				the_letter = the_last;
			}
			if (the_letter.charCodeAt(0)==92) {
				the_letter = "*";
			}
			if (current_letter != the_letter && the_letter!="*" && the_letter!="" && isAlpha(the_letter)) {
				current_letter = the_letter;
				$("#" + current_letter).css("color","white");
				$("#" + current_letter).css("background","url(../../img/glass_modal_low.png)");
				$("#" + current_letter).css("cursor","pointer");
				$("#" + current_letter).removeClass("turned_off");
			}
		});
		
		
		//might need to reformat
		if (blnExactMatch && search_term!="") {
			if (first_letter > second_letter) {
				var new_header = '<td colspan="14"><div style="width:100%; text-align:left; font-size:1.8em; background:#f8f866; color:black;">Exact Match for [' + search_term + ']</div></td>';
				//$(".letter_row")[0].innerHTML = new_header;
			}
		}
		
		if (typeof this.collection != "undefined") {
			if (this.collection.length > 0) {
				if (typeof $("#kase_listing").parent().parent()[0] != "undefined") {
					if ($("#kase_listing").parent().parent()[0].id == "search_results") {
						$(".pager").hide();
						//$("#list_kases_header").hide();
					}
				}
				tableSortIt("kase_listing");
			}
		}
		
		//show sorting
		if (self.model.get("sort_by")!="") {
			$("#kases_start_by").val(self.model.get("sort_by"));
		}
		
		if (self.model.get("recent")=="Y") {
			$("#kase_status_title").html("Recent");
			$(".letter_row").hide();
		}
		
		if (self.model.get("search_parameters")!="") {
			var arrSearchParameters = []
			arrFormValues = self.model.get("search_parameters").split("&");
			arrFormValues.forEach(function(element, index, array) {
					var param = element;
					var arrParam = param.split("=");
					if (arrParam[1]!="") {
						var the_param = arrParam[1].replaceTout(arrParam[1]);
						if(the_param != "undefined") {
							arrSearchParameters.push(the_param);	
						}
					}
				}
			);
			if (arrSearchParameters.length > 0) {
				var current_title = arrSearchParameters.join("&nbsp;|&nbsp;");
				$("#search_parameters").html(current_title);
				
				//now update the report url
				var kase_links = $(".print_kases_link");
				var new_href = "report.php?s=" + encodeURIComponent(arrSearchParameters.join("|")) + "#kases/active";
				kase_links[0].href = new_href;
				kase_links[1].href = new_href;
			}
		}
		if ($("#content").css("top") == "0px") {
			$("#content").css("top", "60px");
		}
		
		//show the right button
		if ($("#search_closed_cases").prop("checked")) {
			$("#closed_kases").fadeOut(function() {
				$("#open_kases").fadeIn();
			});
		}
		kase_searching = false;
		
		setTimeout(function() {
			if (document.location.hash == "#kases" && blnReassignCases) {
				$("#reassign_holder").show();
			}
			
			if (self.model.get("holder") == "preview_pane") {
				$("#list_kases_header").hide();
				$(".alphabet").hide();
				$(".select_letter").show();
				$("#transfer_kases_holder").show();
				$(".icons_holder").hide();
				$("#kase_listing_table_holder").css("margin-top", "-50px");
				$("#user_id").val(self.model.get("user_id"));
				$("#preview_pane").show();
				$("#kases_assign_button").trigger("click");
				
				
				$("#transfer_kases_holder").append("<div style='float:right; position:relative' id='print_summary_holder'></div>");
				$("#print_summary_holder").css("margin-right", "20px");
				$("#kase_report_button").removeClass("btn-sm");
				$("#kase_report_button").addClass("btn-xs");
				
				$("#kase_report").append("&nbsp;(" + self.collection.length + ")");
				$("#print_summary_holder").html(document.getElementById("kase_report_button"));
				var user_id = $("#user_id").val();
				$("#print_summary_holder").prepend('<div id="summary_title"  style="position: absolute; left: -1050px; font-size: 1.6em; top: -5px;"></div><button class="btn btn-xs btn-primary workload" id="workload_' + user_id + '"  style="margin-right:20px">Workload</button>');
				$("#summary_title").html(self.model.get("title"));
				if (self.model.get("title").indexOf("<br>") > -1) {
					$("#transfer_kases_holder").css("height", "50px");
				}
			}
		}, 555);
		
		if (typeof this.model.get("filter_status")!="undefined") {
			$("#intake_status_filter").val(this.model.get("filter_status"));
		}
		if (typeof this.model.get("filter_type")!="undefined") {
			$("#intake_type_filter").val(this.model.get("filter_type"));
		}
		scrollTable(event);
	},
    newKase:function (event) {
        composeKase(event);
    },
	newTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	showClosedKases: function(){
		$("#search_closed_cases").prop("checked", true);
		$("#srch-term").trigger("keyup");
		Backbone.history.navigate('kasesclosed');
	},
	showOpenKases: function(){
		$("#search_open_cases").prop("checked", true);
		$("#srch-term").trigger("keyup");
		Backbone.history.navigate('kases');
	},
	showAll: function(){
		/*$('#ikase_loading').html(loading_image);
		
		blnSearchingKases = true;
		showDBResults(search_kases, "");
		return;
		*/
		var form_name = "kase";
		if (document.location.hash=="#intakes") {
			form_name = "intake";
		}
		var _alphabets = $('.alphabet > a');
		_alphabets.removeClass("active");
		var _contentRows = $('#' + form_name + '_listing tbody tr');
		$("#kase_show_all").addClass("active");
		$(".letter_row").show();
		_contentRows.fadeIn(400);
	},
	lookupADJ: function(event) {
		var element = event.currentTarget;
		var adj_number = element.innerHTML;
		var case_id = element.classList[1].split("_")[1];
		composeEamsImport(adj_number, "lookup", case_id);
	},
	letterClick: function(event) {
		var element = event.currentTarget;
		if ($("#" + element.id).hasClass("turned_off")) {
			return;
		}
		/*
		blnSearchingKases = true;
		var alpha_kases = new KaseCollection;
		var sort_by = $("#kases_sort_by").val();
		var arrSortBy = sort_by.split("_");
		starts_with = "starts_with_" + arrSortBy[0];
		alpha_kases.searchDB(element.id, starts_with);
		
		return;
		*/
		var form_name = "kase";
		if (document.location.hash=="#intakes") {
			form_name = "intake";
		}
		$(".letter_row").hide();
		var _alphabets = $('.alphabet > a');
		var _contentRows = $('#' + form_name + '_listing tbody tr');
		var _count = 0;
		_alphabets.removeClass("active");
		
		$("#" + element.id).addClass("active");
		_text = $("#" + element.id).html();
		_contentRows.hide();
		/*
		_contentRows.each(function (i) {
			var _cellText = $(this).children('td').eq(0).text();
			//if (RegExp('^' + _text).test(_cellText)) {
			if (_cellText.indexOf(_text) > -1) {
				_count += 1;
				$(this).fadeIn(400);
			}
		});
		*/
		$("." + element.id).fadeIn(400);
		
		this.model.set("current_letter", element.id);
	},
	showNewWindowLink: function(event) {
		event.preventDefault();
		clearTimeout(show_link_id);
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		$(".kase_windowlink").fadeOut("slow");
		setTimeout(function() {
			$("#windowlink_" + theid).fadeIn("slow");
			
			//openKasePreviewPanel(theid);
		}, 100);
	},
	freezeNewWindowLink: function(event) {
		event.preventDefault();
		clearTimeout(show_link_id);
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		
		$("#windowlink_" + theid).show();
	},
	hideNewWindowLink: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		
		show_link_id = setTimeout(function() {
			$(".kase_windowlink").fadeOut("slow");
		}, 1500);
	},
	hideSearchResults: function() {
		$("#search_results").html("");
		$("#srch-term").val("");
	},
	editKase:function (event) {
		var element = event.currentTarget;
        composeKaseEdit(element.id)
    },
	clearSearch: function() {
		$("#intakes_searchList").val("");
		$( "#intakes_searchList" ).trigger( "keyup" );
	},
	unVivify: function(event) {
		var textbox = $("#intakes_searchList");
		var label = $("#label_search_intakes");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#intakes_searchList");
		var label = $("#label_search_intakes");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#intakes_searchList').focus();
		}
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		//composeNewNote(element.id);
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[elementArray.length - 1];
		
		var url = "?n=#newnote/" + case_id;
		window.open(url);
		/*
		window.Router.prototype.listNotes(case_id, true);
		window.history.replaceState(null, null, "#notes/" + case_id);
		app.navigate("notes/" + case_id, {trigger: false});
		*/
	},
	newPhone: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composePhone(element.id);
	},
	newMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	newLetter: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeLetter(element.id);
	},
	confirmdeleteInjury: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		var arrClasses = $("#" + element.id).prop("class").split(" ");
		var case_id = arrClasses[arrClasses.length - 1].split("_")[2];
		
		//if there is only 1 injury, we are actually deleting the case entirely
		var injury_kases = new KaseInjuryCollection({case_id: case_id});
		injury_kases.fetch({
			success: function(injury_kases) {
				if (injury_kases.length==1) {
					composeDelete(case_id, "kase");
				} else {
					composeDelete(id, "injury");
				}
			}
		});
	},
	confirmdeleteKase: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "kase");
	},
	canceldeleteInjury: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	canceldeleteKase: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteKase: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		
		this.deleteInjury(event);
		return;
		
		if (customer_id == 1033) {
			this.deleteInjury(event);
			return;
		} else {
			var blnDeleted = deleteElement(event, id, "kase");
		}
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteKase(event);
			$(".kase_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				//$(".document_row_" + theid).fadeOut();
				$(".kase_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});
window.kases_report = Backbone.View.extend({
    initialize:function () {
	},
	events: {
		"click .open_cases":		"openCases",
		"click .open_year":			"openYearCases",
		"click #hide_list":			"hideCases",
		"click .expand_year":		"expandYear",
		"click .shrink_year":		"shrinkYear"
	},
	render:function() {
		var self = this;
		var kasesbymonth = this.collection.toJSON(); 
		var current_year = "";
		var arrTotals = [];
		_.each( kasesbymonth, function(kasebymonth) {
			if (current_year != kasebymonth.case_year) {
				arrTotals[kasebymonth.case_year] = 0;
				kasebymonth.case_display_year = kasebymonth.case_year;
				current_year = kasebymonth.case_year;
			} else {
				kasebymonth.case_display_year = "";
			}
			arrTotals[kasebymonth.case_year] += Number(kasebymonth.injury_count);
		});
		$(this.el).html(this.template({kasesbymonth: kasesbymonth, key: self.model, arrTotals:arrTotals}));
		
		return this;
	},
	hideCases: function(event) {
		event.preventDefault();
		$("#kases_list").fadeOut(function() {
			$("#summary_table").show();
			$(".main_header").show();
		});
	},
	openCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[0];
		var month = element.id.split("_")[1];
		var monthname = element.id.split("_")[2];
		
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:month});
		kaseslist.fetch({
			success: function(kaseslist) {
				$("#summary_table").fadeOut(function() {
					var listinfo = new Backbone.Model;
					listinfo.set("month", month);
					listinfo.set("year", year);
					listinfo.set("monthname", monthname);
					listinfo.set("matrix_display", true);
					$("#kases_list").html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
					$("#kases_list").show();
				});
				return;
			}
		});
		
	},
	expandYear: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		
		$(".year_row_" + year).show(); 
		$(".year_" + year).show(); 
		
		$("#expand_" + year).fadeOut(function() {
			$("#shrink_" + year).show();
		});
	},
	shrinkYear: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		
		$(".year_row_" + year + ".sub_row").hide(); 
		$(".year_" + year).hide(); 
		
		$("#shrink_" + year).fadeOut(function() {
			$("#expand_" + year).show();
		});
	},
	openYearCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:-1});
		kaseslist.fetch({
			success: function(kaseslist) {
				var listinfo = new Backbone.Model;
				listinfo.set("month", "");
				listinfo.set("year", year);
				listinfo.set("monthname", "");
				$("#summary_table").fadeOut(function() {
						$("#kases_list").html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
						$("#kases_list").show();
					}
				);
				return;
			}
		});
		
	}
});
window.referrals_report = Backbone.View.extend({
    initialize:function () {
	},
	events: {
		"click .open_cases":				"openCases",
		"click .expand_cases_year":			"openYearCases",
		"click .referring_cases":			"showReferringCases",
		"click #hide_list":					"hideCases",
		"click .expand_year":				"expandYear",
		"click .expand_month":				"expandMonth",
		"click .shrink_year":				"shrinkYear"
	},
	render:function() {
		var self = this;
		var kasesbymonth = this.collection.toJSON(); 
		var arrRefTotals = [];
		var current_year = "";
		var current_referring = "";
		var row_count = -1;
		var year_count = -1;
		var arrTotals = [];
		var arrYearTotals = [];
		_.each( kasesbymonth, function(kasebymonth) {
			if (current_referring!=kasebymonth.referring) {
				current_year = "";
				current_referring = kasebymonth.referring;
				row_count++;
				arrTotals[row_count] = 0;
			}
			if (current_year != kasebymonth.case_year) {
				current_year = kasebymonth.case_year;
				year_count++;
				arrYearTotals[year_count] = 0;
			}
			arrTotals[row_count] = Number(arrTotals[row_count]) + Number(kasebymonth.injury_count);
			arrYearTotals[year_count] = Number(arrYearTotals[year_count]) + Number(kasebymonth.injury_count);
		});
		/*
		_.each( kasesbymonth, function(kasebymonth) {
			
			kasebymonth.row_id = "";
			if (current_referring != kasebymonth.referring) {
				//initial value
				arrRefTotals[kasebymonth.referring] = 0;
				var encoded_uri = encodeURIComponent(kasebymonth.referring.replaceAll(" ", "_"));
				kasebymonth.row_id = encoded_uri.alphaNumeric();
				encoded_uri = encoded_uri.replaceAll("'", "%27");
				//encoded_uri = encoded_uri.replaceAll("&", "%26");
				kasebymonth.case_display_referring = "<a id='link_" + kasebymonth.row_id + "' href='#referralreport/byref/" + encoded_uri + "'>" + kasebymonth.referring + "</a>";
				current_referring = kasebymonth.referring;
			} else {
				kasebymonth.case_display_referring = "";
			}
			if (current_year != kasebymonth.case_year) {
				kasebymonth.case_display_year = kasebymonth.case_year;
				current_year = kasebymonth.case_year;
			} else {
				kasebymonth.case_display_year = "";
			}
			if (typeof arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] == "undefined") {
				//initial value
				arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] = 0;
			}
			arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] += Number(kasebymonth.injury_count);
			arrRefTotals[kasebymonth.referring] += Number(kasebymonth.injury_count);
			
		});
		*/
		$(this.el).html(this.template({kasesbymonth: kasesbymonth, key: self.model, arrTotals:arrTotals, arrYearTotals: arrYearTotals, referring: self.model.get("referring"), showall: self.model.get("showall")}));
		
		return this;
	},
	hideCases: function(event) {
		event.preventDefault();
		$("#kases_list").fadeOut(function() {
			$("#summary_table").show();
		});
	},
	showReferringCases: function(event) {
		event.preventDefault();
		
		$(".data_cells").html("");
		
		var element = event.currentTarget;
		var current_referring = element.innerHTML;
		var arrID = element.id.split("_");

		var referring_id = arrID[arrID.length - 1];
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: "-1", month:"-1", referring: referring_id});
		kaseslist.fetch({
			success: function(kaseslist) {
				var data_holder = $("#cell_" + referring_id);
				var holder = "cases_" + referring_id;
				var listinfo = new Backbone.Model;
				listinfo.set("month", "");
				listinfo.set("year", "now");
				listinfo.set("referring", current_referring);
				listinfo.set("monthname","");
				listinfo.set("holder", holder);
				data_holder.html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
				
				return;
			}
		});
		
	},
	openCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[0];
		var month = element.id.split("_")[1];
		var monthname = element.id.split("_")[2];
		var referring = element.id.split("_")[3];
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:month});
		kaseslist.fetch({
			success: function(kaseslist) {
				filter_kaseslist = kaseslist.where({referring_search: referring});
				kaselist = new Backbone.Collection;
				kaselist.reset(filter_kaseslist);
				
				$("#summary_table").fadeOut(function() {
					var listinfo = new Backbone.Model;
					listinfo.set("month", month);
					listinfo.set("year", year);
					listinfo.set("monthname", monthname);
					$("#kases_list").html(new kase_list_report({collection: kaselist, model: listinfo}).render().el);
					$("#kases_list").show();
				});
				return;
			}
		});
		
	},
	expandMonth: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var year = arrID[arrID.length - 3];
		var month = arrID[arrID.length - 2];
		var referring_id = arrID[arrID.length - 1];
		
		var current_referring = $("#referring_name_" + year + "_" + referring_id).val();
		var data_holder = $("#case_month_" + year + "_" + month + "_" + referring_id);
		var holder = "row_case_month_" + year + "_" + month + "_" + referring_id;
		
		//get a list of kases for this referring for this month and for this year
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:month, referring: referring_id});
		kaseslist.fetch({
			success: function(kaseslist) {
				//var reflist = kaseslist.where({referring: referring});
				//kaseslist.reset(reflist);
				var listinfo = new Backbone.Model;
				listinfo.set("month", month);
				listinfo.set("year", "now");
				listinfo.set("referring", current_referring);
				listinfo.set("monthname", element.innerHTML);
				listinfo.set("holder", holder);
				data_holder.html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
				
				return;
			}
		});
	},
	expandYear: function(event) {
		event.preventDefault();
		
		$(".data_cells").html("");
		var element = event.currentTarget;
		/*
		var year = element.id.split("_")[1];
		var referring = element.id.split("_")[2];
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		
		$(".year_row_" + year + "_" + referring).show(); 
		$(".year_" + year + "_" + referring).show(); 
		
		$("#expand_" + year + "_" + referring).fadeOut(function() {
			$("#shrink_" + year + "_" + referring).show();
		});
		*/
		var arrID = element.id.split("_");
		var year = arrID[arrID.length - 2];
		var referring_id = arrID[arrID.length - 1];
		
		var current_referring = $("#referring_name_" + year + "_" + referring_id).val();
		var holder = $("#months_" + year + "_" + referring_id);
		var html = "";
		var arrRows = [];
		var kasesbymonth = this.collection.toJSON(); 
		_.each( kasesbymonth, function(kasebymonth) {
			if (current_referring == kasebymonth.referring && year == kasebymonth.case_year) {
				//show months, total
				row = "<tr><td align='left' width='1%'><a class='expand_month' id='expand_month_" + year + "_" + kasebymonth.case_month + "_" + referring_id + "' style='cursor:pointer; text-decoration:underline; color:#428bca'>" + kasebymonth.case_month_name + "</a></td><td align='left'>" + kasebymonth.injury_count + "</td></tr>";
				arrRows.push(row);
				row = "<tr id='row_case_month_" + year + "_" + kasebymonth.case_month + "_" + referring_id + "' style='display:none'><td id='case_month_" + year + "_" + kasebymonth.case_month + "_" + referring_id + "' align='left' valign='top' colspan='2'></td></tr>";
				arrRows.push(row);
			}
		});
		html = "<table width='100%' class='summary_info'><tr><th align='left' width='1%'>Month</td><th align='left'>Cases</td></tr>";
		html += arrRows.join("") + "</table>";
		
		holder.html(html);
		holder.show();
	},
	shrinkYear: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		var referring = element.id.split("_")[2];
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		$(".year_row_" + year + "_" + referring + ".sub_row").hide(); 
		$(".year_" + year + "_" + referring).hide(); 
		
		$("#shrink_" + year + "_" + referring).fadeOut(function() {
			$("#expand_" + year + "_" + referring).show();
		});
	},
	openYearCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		$(".data_cells").html("");
		
		var arrID = element.id.split("_");
		var year = arrID[arrID.length - 2];
		var referring_id = arrID[arrID.length - 1];
		
		var current_referring = $("#referring_name_" + year + "_" + referring_id).val();
		var holder = $("#months_" + year + "_" + referring_id);
		
		var kaseslist = new KaseListByMonthCollection({year: year, month:-1, referring: referring_id});
		kaseslist.fetch({
			success: function(kaseslist) {
				//var reflist = kaseslist.where({referring: referring});
				//kaseslist.reset(reflist);
				var holder = "cases_" + referring_id;
				var data_holder = $("#cell_" + referring_id);
				
				var listinfo = new Backbone.Model;
				listinfo.set("month", "");
				listinfo.set("year", year);
				listinfo.set("referring", current_referring);
				listinfo.set("monthname", "");
				listinfo.set("holder", holder);
				data_holder.html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
				
				return;
			}
		});
		return;
		
		var element = event.currentTarget;
		var arrElement = element.id.split("_");
		var year = arrElement[1];
		var referring = arrElement[2];
		referring = referring.replaceTout("|", "_");
		
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:-1, referring: referring});
		kaseslist.fetch({
			success: function(kaseslist) {
				//var reflist = kaseslist.where({referring: referring});
				//kaseslist.reset(reflist);
				var listinfo = new Backbone.Model;
				listinfo.set("month", "");
				listinfo.set("year", year);
				listinfo.set("referring", referring);
				listinfo.set("monthname", "");
				$("#summary_table").fadeOut(function() {
						$("#kases_list").html(new kase_list_report({collection: kaseslist, model: listinfo}).render().el);
						$("#kases_list").show();
					}
				);
				return;
			}
		});
		
	}
});
window.clients_report = Backbone.View.extend({
    initialize:function () {
	},
	events: {
		"click .open_cases":		"openCases",
		"click .open_year":			"openYearCases",
		"click #hide_list":			"hideCases",
		"click .expand_year":		"expandYear",
		"click .shrink_year":		"shrinkYear"
	},
	render:function() {
		var self = this;
		var kasesbymonth = this.collection.toJSON(); 
		var current_year = "";
		var current_referring = "";
		var arrTotals = new Object;
		var arrRefTotals = [];
		var blnNewRef = false;
		_.each( kasesbymonth, function(kasebymonth) {
			kasebymonth.row_id = "";
			blnNewRef = false;
			if (current_referring != kasebymonth.referring) {
				blnNewRef = true;
				//initial value
				arrRefTotals[kasebymonth.referring] = 0;
				var encoded_uri = encodeURIComponent(kasebymonth.referring.replaceAll(" ", "_"));
				kasebymonth.row_id = encoded_uri.alphaNumeric();
				encoded_uri = encoded_uri.replaceAll("'", "%27");
				//encoded_uri = encoded_uri.replaceAll("&", "%26");
				kasebymonth.case_display_referring = "<a id='link_" + kasebymonth.row_id + "' href='#clientreport/byref/" + encoded_uri + "'>" + kasebymonth.referring + "</a>";
				current_referring = kasebymonth.referring;
			} else {
				kasebymonth.case_display_referring = "";
			}
			if (current_year != kasebymonth.case_year || blnNewRef) {
				kasebymonth.case_display_year = kasebymonth.case_year;
				current_year = kasebymonth.case_year;
			} else {
				kasebymonth.case_display_year = "";
				//kasebymonth.case_display_year = kasebymonth.case_year;
			}
			if (typeof arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] == "undefined") {
				//initial value
				arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] = 0;
			}
			arrTotals[kasebymonth.referring + "-" + kasebymonth.case_year] += Number(kasebymonth.injury_count);
			arrRefTotals[kasebymonth.referring] += Number(kasebymonth.injury_count);
			
			//row prep
			var expand_link = '';
			var shrink_link = '';
			var show_row = '';	//'display:none';
			var row_class = 'sub_row';
			var totals = '';
			var totals_referring = '';
			var top_border = '';
			if (kasebymonth.case_display_referring!='') {
				totals_referring = '&nbsp;<span style="font-size:0.8em">(' + arrRefTotals[kasebymonth.referring] + ')</span>';
				top_border = "border-top:1px solid black";
			}
			//if (kasebymonth.case_display_year!='' || blnNewRef) {
				expand_link = '&nbsp;<a class="expand_year" id="expand_' + kasebymonth.case_year + '_' + kasebymonth.row_id + '" style="text-decoration:none; cursor:pointer">+</a>';
				shrink_link = '&nbsp;<a class="shrink_year" id="shrink_' + kasebymonth.case_year + '_' + kasebymonth.row_id + '" style="text-decoration:none; cursor:pointer; display:none">-</a>';
				show_row = "";
				row_class = '';
				totals = '&nbsp;<span style="font-size:0.8em">(' + arrTotals[kasebymonth.referring + '-' + kasebymonth.case_year] + ')</span>';
			//}
			
			kasebymonth.show_row = show_row;
			kasebymonth.expand_link = expand_link;
			kasebymonth.shrink_link = shrink_link;
			kasebymonth.row_class = row_class;
			kasebymonth.totals = totals;
			kasebymonth.totals_referring = totals_referring;
			kasebymonth.top_border = top_border;
		});
		$(this.el).html(this.template({kasesbymonth: kasesbymonth, key: self.model, arrTotals:arrTotals, arrRefTotals: arrRefTotals, referring: self.model.get("client"), showall: self.model.get("showall")}));
		
		return this;
	},
	hideCases: function(event) {
		event.preventDefault();
		$("#kases_list").fadeOut(function() {
			$("#summary_table").show();
		});
	},
	openCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrElement = element.id.split("_");
		var year = arrElement[0];
		var month = arrElement[1];
		var monthname = arrElement[2];
		var referring = arrElement[3];
		var case_status = "";
		if (arrElement.length == 5) {
			case_status = arrElement[4];
		}
		//var referring = $("#referring_name_" + arrElement[3]).val();
		//referring = referring.replaceAll(" ", "_");	
		/*
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		*/
		
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:month, referring:referring, referring_type:"client"});
		kaseslist.fetch({
			success: function(kaseslist) {
				//filter_kaseslist = kaseslist.where({referring_search: referring});
				//kaselist = new Backbone.Collection;
				//kaselist.reset(filter_kaseslist);
				
				$("#summary_table").fadeOut(function() {
					var listinfo = new Backbone.Model;
					listinfo.set("referring", referring);
					listinfo.set("month", month);
					listinfo.set("year", year);
					listinfo.set("monthname", monthname);
					$("#kases_list").html(new kase_list_applicant_report({collection: kaseslist, model: listinfo}).render().el);
					$("#kases_list").show();
					setTimeout(function(){
						var referring_name = $("#referring_name_" + referring).val();
						$("#table_month_year").html(referring_name + "&nbsp;")
						//$("#corporation_type_header").html("Client");
						$(".kase_list_header").hide();
						
						if (case_status != "") {
							$("#" + case_status + "_kases_filter").trigger("click");
						}
					}, 100);
				});
				return;
			}
		});
		
	},
	expandYear: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		var referring = element.id.split("_")[2];
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		
		$(".year_row_" + year + "_" + referring).show(); 
		$(".year_" + year + "_" + referring).show(); 
		
		$("#expand_" + year + "_" + referring).fadeOut(function() {
			$("#shrink_" + year + "_" + referring).show();
		});
	},
	shrinkYear: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var year = element.id.split("_")[1];
		var referring = element.id.split("_")[2];
		referring = $("#link_" + referring).html();
		referring = referring.replaceAll(" ", "");	
		$(".year_row_" + year + "_" + referring + ".sub_row").hide(); 
		$(".year_" + year + "_" + referring).hide(); 
		
		$("#shrink_" + year + "_" + referring).fadeOut(function() {
			$("#expand_" + year + "_" + referring).show();
		});
	},
	openYearCases: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrElement = element.id.split("_");
		var year = arrElement[1];
		var referring = arrElement[2];
		
		//clear all
		$(".filter_link").css("background", "none");
		$(".filter_link").css("color", "black");
		$("#" + element.id).css("background", "black");
		$("#" + element.id).css("color", "white");
		
		//if ($("#month_year_" + element.id).html()=="") {		
		var kaseslist = new KaseListByMonthCollection({year: year, month:-1, referring: referring, referring_type: "client"});
		kaseslist.fetch({
			success: function(kaseslist) {
				//var reflist = kaseslist.where({referring: referring});
				//kaseslist.reset(reflist);
				var listinfo = new Backbone.Model;
				listinfo.set("month", "");
				listinfo.set("year", year);
				listinfo.set("referring", referring);
				listinfo.set("monthname", "");
				$("#summary_table").fadeOut(function() {
						$("#kases_list").html(new kase_list_applicant_report({collection: kaseslist, model: listinfo}).render().el);
						$("#kases_list").show();
						setTimeout(function(){
							var referring_name = $("#referring_name_" + referring).val();
							$("#table_month_year").html(referring_name + "&nbsp;")
							//$("#corporation_type_header").html("Client");
							$(".kase_list_header").hide();
						}, 100);
					}
				);
				return;
			}
		});
		
	}
});
window.kase_list_report = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #hide_alpha_summary":					"hideAlphaSummary",
		"change .select_kase":							"selectKase",
		"change .select_letter":						"selectLetter",
		"change .select_all":							"selectAllAlpha",
		"click .assign_kase":							"assignKase",
		"click .save_assign":							"saveAssign",
		"click .sort_applicant":						"sortApplicant",
		"click #kase_listing_report_all_done":			"doTimeouts"
	},
	render:function() {
		var self = this;
		var kaseslist = this.collection.toJSON();
		var intCounter = 0;
		var arrWorkers = [];		//individual workers
		var arrWorkerNicknames = [];			//nicknames
		var arrWorkerOptions = [];		//individual workers options
		
		var arrAttys = [];		//individual atty
		var arrAttyNicknames = [];			//nicknames
		var arrAttyOptions = [];		//individual attys options
		var arrReportIDs = [];
		var search_term = "";
		
		if (this.model.get("worker")=="undefined" || typeof this.model.get("worker")=="undefined") {
			this.model.set("worker", "");
		}
		var worker = this.model.get("worker");
		var blnFilterWorker = (worker!="");
		
		if (this.model.get("atty")=="undefined") {
			this.model.set("atty", "");
		}
		if (typeof this.model.get("letter") == "undefined") {
			this.model.set("letter", "");
		}
		if (typeof this.model.get("atty")=="undefined") {
			this.model.set("atty", "");
		}
		if (typeof this.model.get("individual_cases")=="undefined") {
			this.model.set("individual_cases", false);
		}
		var attorney = this.model.get("atty");
		
		//let's get some names
		var worker_full_name = "";
		if (worker!="") {
			var the_worker = worker_searches.findWhere({nickname:worker.toUpperCase()});
			worker_full_name = the_worker.get("user_name");
		}
		var attorney_full_name = "";
		if (attorney!="") {
			var the_attorney = worker_searches.findWhere({nickname:attorney.toUpperCase()});
			attorney_full_name = the_attorney.get("user_name");
		}
		var blnFilterAttorney = (attorney!="");
		
		var filtered_kase_list = new Backbone.Collection;
		var blnFoundIt = false;
		var blnFoundItAtty = false;
		
		var current_letter = "~";
		var arrLetterCount = [];
		//default value
		arrLetterCount["TBD"] = 0;
		
		var arrFirstLetters = [];
				
		var letter = this.model.get("letter");
		var current_clean_number = "";
		var case_counter = 0;
		_.each( kaseslist, function(kase) {
			/*
			var clean_case_number = kase.case_number;
			
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
			*/
			//filter?
			var blnContinue = true;
			if (blnFilterAttorney) {
				if (kase.attorney_name=="" && kase.attorney=="") {
					blnContinue = false;
				} else {
					blnFoundItAtty = false;
					if (kase.attorney_name!="") {
						if (kase.attorney_name.toUpperCase().indexOf(attorney.toUpperCase()) < 0) {
							blnContinue = false;
						} else {
							blnFoundItAtty = true;
						}
					}
					//might be in attorney
					if (blnContinue && !blnFoundItAtty) {
						if (kase.attorney!="") {
							if (kase.attorney.toUpperCase().indexOf(attorney.toUpperCase()) < 0) {
								blnContinue = false;
							}
						}
					}
				}
			}
			if (blnFilterWorker && blnContinue) {
				if (kase.worker_name=="" && kase.worker=="") {
					blnContinue = false;
				} else {
					blnFoundIt = false;
					if (kase.worker_name!="") {
						if (kase.worker_name.toUpperCase().indexOf(worker.toUpperCase()) < 0) {
							blnContinue = false;
						} else {
							blnFoundIt = true;
						}
					}
					//might be in worker
					if (blnContinue && !blnFoundIt) {
						if (kase.worker!="") {
							if (kase.worker.toUpperCase().indexOf(worker.toUpperCase()) < 0) {
								blnContinue = false;
							}
						}
					}
				}
			}
			if (blnContinue) {
				arrReportIDs.push(kase.case_id);
				
				if (kase.applicant == "undefined") { 
					kase.applicant = "";
				}
				if (search_term == "") {
					if (kase.search_term!="") {
						search_term = kase.search_term;
					} else {
						search_term = "none";
					}
				}
				if (kase.case_name!="") {
					kase.name = kase.case_name;
					if (kase.full_name=="") {
						kase.full_name = kase.name;
					}
				} else {
					kase.name = kase.name.replace(' - 00/00/0000', '');
				}
				
				if (kase.referring == null) {
					kase.referring = "";
				}
				kase.referring_link = "";
				if (kase.referring!="") {
					kase.referring_link = kase.referring.replaceAll(" ", "_");
				}
				kase.background_color = "#FFFFFF";
				if ((intCounter%2)==0) {
					kase.background_color = "#EDEDED";
				}
				var arrKaseName = kase.name.split(" - ");
				if (arrKaseName.length==2) {
					kase.name = arrKaseName[0];
					if (kase.name == "  vs ") {
						kase.name = "";
					}
					//kase.doi = arrKaseName[1];
				}
				if (kase.start_date!="0000-00-00" && kase.start_date!="00/00/0000") {
					kase.doi = moment(kase.start_date).format("MM/DD/YYYY");
					if (kase.end_date!="0000-00-00" && kase.end_date!="00/00/0000") {
						kase.doi +=  "-" + moment(kase.end_date).format("MM/DD/YYYY") + "CT";
					}
				}
				if (kase.doi == undefined) { 
					kase.doi = "";
				}
				if (kase.applicant == undefined) { 
					kase.applicant = "";
				}
				kase.full_name = kase.full_name.toLowerCase();
				kase.full_name = kase.full_name.capitalizeWords();
				
				//prep it first
				var arrLast = kase.last_name.split("-");
				for(var i = 0; i < arrLast.length; i++) {
					arrLast[i] = arrLast[i].capitalizeWords();
				}
				var arrFirst = kase.first_name.split("-");
				for(var i = 0; i < arrFirst.length; i++) {
					arrFirst[i] = arrFirst[i].capitalizeWords();
				}
				kase.display_name = arrLast.join("-") + ", " + arrFirst.join("-");
				
				if (kase.display_name.trim()==",") {
					kase.display_name = "";
				}
				var display_name = kase.display_name;
				//first letter
				var first_letter = "TBD";
				if (display_name!="") {
					display_name = display_name.replace("(", "");
					display_name = display_name.replace(")", "");
					display_name = display_name.toUpperCase()	;
					//var first_letter = display_name.substr(0, 1);
					var first_letter = kase.first_name.substr(0, 1);
				}
				if (current_letter!=first_letter) {
					current_letter = first_letter;
					arrFirstLetters.push(current_letter);
					arrLetterCount[current_letter] = 1;
				} else {
					arrLetterCount[current_letter]++;
				}
				if (kase.applicant_email!="") {
					kase.applicant_email = "<a href='mailto:" + kase.applicant_email + "'>" + kase.applicant_email + "</a>";
				}
				kase.adj_number = kase.adj_number.replaceAll(",", ", ");
				
				if (kase.statute_limitation!="" && kase.statute_limitation!="0000-00-00") {
					kase.statute_limitation = moment(kase.statute_limitation).format("MM/DD/YYYY");
				} else {
					kase.statute_limitation = "";
				}
				
				if (kase.worker_full_name==null) {
					kase.worker_full_name = "";
				}
				if (kase.attorney_full_name==null) {
					kase.attorney_full_name = "";
				}
				
				if (kase.worker_name=="" && kase.worker_full_name!="") {
					kase.worker_name = kase.worker_full_name.firstLetters();
				}
				
				if (kase.worker!="") {
					if (arrWorkers.indexOf(kase.worker) < 0) {
						if (!isNaN(kase.worker)) {
							var the_worker = worker_searches.findWhere({id:kase.worker, activated:"Y"});
						} else {
							var the_worker = worker_searches.findWhere({nickname:kase.worker, activated:"Y"});
						}
						if (typeof the_worker != "undefined") {
							arrWorkers.push(kase.worker);
							arrWorkerNicknames.push(the_worker.get("nickname"));
							//arrWorkernames[the_worker.get("nickname")] = the_worker.get("user_name");
						}
					}
				}
				if (kase.worker_name=="" && kase.worker!="") {
					kase.worker_name = kase.worker;
				}
				
				//atty
				if (kase.attorney_name=="" && kase.attorney_full_name!="") {
					kase.attorney_name = kase.attorney_full_name.firstLetters();
				}
				if (kase.attorney!="") {
					if (arrAttys.indexOf(kase.attorney) < 0) {
						if (!isNaN(kase.attorney)) {
							var the_attorney = worker_searches.findWhere({id:kase.attorney, activated:"Y"});
						} else {
							var the_attorney = worker_searches.findWhere({nickname:kase.attorney, activated:"Y"});
						}
						if (typeof the_attorney != "undefined") {
							arrAttys.push(kase.attorney);
							arrAttyNicknames.push(the_attorney.get("nickname"));
						}
					}
				}
				
				if (kase.attorney_name=="" && kase.attorney!="") {
					kase.attorney_name = kase.attorney;
				}
	
				//attorney
				kase.attorney_name = "<span title='" + kase.attorney_full_name + "'>" + kase.attorney_name + "</span>";
				
				//case status
				kase.case_status = kase.case_status.replaceAll(' ', '&nbsp;');
				if (kase.case_status.toLowerCase().indexOf("cl")==0) {
					if (kase.closed_date!="") {
						kase.case_status += " <span style='font-size:0.8em'>" + moment(kase.closed_date).format("MM/DD/YYYY") + "</span>";
					}
				}
				if (kase.case_status.toLowerCase()=="open") {
					kase.case_status += " <span style='font-size:0.8em'>" + moment(kase.case_date).format("MM/DD/YYYY") + "</span>";
				}
				var kase_type = kase.case_type;
				var blnWCAB = isWCAB(kase_type);
				if (blnWCAB) {
					kase.case_type = "WCAB";
					if (kase_type=="WCAB_Defense") {
						kase.case_type += "&nbsp;DEF";
						kase.case_type = "<span style='background:aqua; color:red; font-weight:bold; padding:2px'>" + kase.case_type + "</span>";
					}
					if (kase.case_number == "" && kase.file_number != "") {
						kase.case_number = kase.file_number;
						
					}
				} else {
					if (kase.defendant_id==-1) {
						kase.defendant = "No Defendant";
					}
					//show something
					if (kase.case_number == "") {
						kase.case_number = kase.file_number;
					}
					kase.case_type = kase.case_type.replace("_", " ").capitalizeWords();
					kase.case_type = kase.case_type.replace(" ", "&nbsp;");
					if (kase.case_type=="Newpi" || kase.case_type=="Pi") {
						kase.case_type = "PI";
					}
				}
				intCounter++;
				
				var blnAddIt = true;
				if (letter!="") {
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
					
					if (letter_string!=letter) {
						blnAddIt = false;
					}
				}
				if (blnAddIt) {
					filtered_kase_list.add(kase);
				}
			}
		});
		
		var uniqueFirstLetters = [];
		$.each(arrFirstLetters, function(i, el){
			if($.inArray(el, uniqueFirstLetters) === -1) uniqueFirstLetters.push(el);
		});
		uniqueFirstLetters.sort();
		//console.log(uniqueFirstLetters);
		//console.log(arrLetterCount);
		
		kaseslist = filtered_kase_list.toJSON();
		
		if (document.location.hash.indexOf("#employeekases")==0) {
			var sorted_collection = new KaseCollection();
			sorted_collection.reset(kaseslist);
			sorted_collection.comparator = "last_name";
			sorted_collection.sort();
			
			kaseslist = sorted_collection.toJSON();;
		}
		
		intCounter = kaseslist.length;
		this.model.set("found", intCounter);
		
		if (search_term == "none" || typeof search_term == "undefined") {
			search_term = "";
		}
		
		var title = "Cases Report - " + user_customer_name + " " + search_term;
		if (window.location.href.indexOf("#referralreport") > -1) {
			title = "Referrals " + title;
		}
		if (window.location.href.indexOf("/active") > -1) {
			title = "Client Kase List - " + user_customer_name;
			if (search_term!="") {
				//is is a uuid
				var search_two = search_term.substr(0, 2);
				var search_len = search_term.length;
				var blnUpperTwo = (search_two == search_two.toUpperCase());
				
				if (isNaN(search_two) && search_len==15 && blnUpperTwo) {
					//look up the company info
					var corporation = new Corporation({uuid: search_term});
					corporation.fetch({
						success: function (corp) {
							$("#kase_list_title").append("<br />" + corp.get("company_name"));
						}
					});
				} else {
					if (search_term=="Advanced Search") {
						title = search_term;
					} else {
						title += "<br>Search:&nbsp;\"" + search_term + "\"";
					}
					if(typeof this.model.get("letter") != "undefined") {
						if (this.model.get("letter") != "") {
							title += "<span style='font-size:0.7em; font-weight:normal'>&nbsp;filtered for (\"" + this.model.get("letter") + "\")</span>";
						}
					}
				}
			}
		}
		
		if (window.location.href.indexOf("?s") > -1) {
			var sub_title = decodeURI(location.search.replace("?s=", ""));
			sub_title = sub_title.split("|").join("&nbsp;|&nbsp;");
			sub_title = sub_title.replaceTout("+", " ");
			title += "<div style='font-size:0.8em'>" + sub_title + "</div>";
		}
		if (this.model.get("year")=="Open Kases") {
			title = "Open Kases Report :: " + user_customer_name;
		}
		//we need the list of ids
		this.model.set("report_case_ids", arrReportIDs.join("|"));
		
		var referring = this.model.get("referring");
		if (typeof referring == "undefined") {
			this.model.set("referring", "")
		}
		
		$(this.el).html(this.template(
		{
			kaseslist: kaseslist, 
			year: this.model.get("year"), 
			month: this.model.get("monthname"), 
			referring: this.model.get("referring"), 
			title:title, 
			worker: this.model.get("worker"),
			worker_full_name: worker_full_name,
			atty: this.model.get("atty"),
			attorney_full_name: attorney_full_name,
			arrFirstLetters: uniqueFirstLetters,
			arrLetterCount: arrLetterCount,
			individual_cases: this.model.get("individual_cases")
		}
		));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		
		return this;
	},
	sortApplicant: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrClass = element.id.split("_");
		var field = arrClass[arrClass.length - 1];
		if (field=="last") {
			this.collection.models = this.collection.sortBy('last_name');  
		}
		if (field=="first") {
			this.collection.models = this.collection.sortBy('first_name');  
		}
		this.render();
	},
	selectKase: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrClass = element.classList[1].split("_");
		var letter = arrClass[arrClass.length - 1];
		
		var arrID = element.id.split("_");
		var case_id = arrID[arrID.length - 1];
		
		
		if (element.checked) {
			$(".kase_data_row_" + case_id).css("background", "#EDEDED");
		} else {
			$(".kase_data_row_" + case_id).css("background", "#FFF");
		}
		
		this.showAssignCoordinator(letter);
	},
	selectAllAlpha: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var letter = arrID[arrID.length - 1];
		
		$(".select_kase_" + letter).attr("checked", element.checked);
		
		if (element.checked) {
			$(".letter_row_" + letter).css("background", "#EDEDED");
		} else {
			$(".letter_row_" + letter).css("background", "#FFF");
		}
		this.showAssignCoordinator(letter);
	},
	showAssignCoordinator: function(letter) {
		//first make sure at least one is checked
		var blnChecked = false;
		var selecteds = $(".select_kase_" + letter);
		var arrLength = selecteds.length;
		for(var i = 0; i < arrLength; i++) {
			if (selecteds[i].checked) {
				blnChecked = true;
				break;
			}
		}
		
		if (blnChecked) {
			$("#assign_kase_" + letter).fadeIn();
		} else {
			$("#assign_kase_" + letter).fadeOut();
			$(".workerInput_" + letter).hide();
		}
	},
	assignKase: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var letter = arrID[arrID.length - 1];
		
		//first make sure at least one is checked
		var blnChecked = false;
		var selecteds = $(".select_kase_" + letter);
		var arrLength = selecteds.length;
		for(var i = 0; i < arrLength; i++) {
			if (selecteds[i].checked) {
				blnChecked = true;
				break;
			}
		}
		
		if (!blnChecked) {
			alert("You must select at least 1 kase to Assign to Coordinator");
			return;
		}
		$("#assign_kase_" + letter).fadeOut(
			function() {
				var theme_3 = {
						theme: "kase", 
						tokenLimit: 1,
						onAdd: function(item) {
							$("#save_kase_" + letter).css("visibility", "visible");
						},
						onDelete: function() {
							$("#save_kase_" + letter).css("visibility", "hidden");
						}
					};
				if ($(".workerInput_" + letter + " .token-input-list-kase").length > 0) {
					$(".workerInput_" + letter + " .token-input-list-kase").remove();
				}
				$(".workerInput_" + letter).fadeIn();
				$("#workerInput_" + letter).tokenInput("api/user", theme_3);
				$(".token-input-list-kase").css("width", "210px");
				$("#token-input-workerInput_" + letter).focus();
			}
		);
	},
	saveAssign: function(event) {
		var self = this;
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var letter = arrID[arrID.length - 1];
		
		$(".workerInput_" + letter).fadeOut();
				
		var selecteds = $(".select_kase_" + letter);
		var arrLength = selecteds.length;
		var arrKaseID = [];
		for(var i = 0; i < arrLength; i++) {
			if (selecteds[i].checked) {
				var arrButton = selecteds[i].id.split("_");
				var kase_id = arrButton[arrButton.length - 1];
				arrKaseID.push(kase_id);
			}
		}
		var case_ids = arrKaseID.join(",");
		var worker = $("#workerInput_" + letter).val();
		
		//update
		var formValues = "case_ids=" + case_ids;
		formValues += "&worker=" + worker;
		
		var url = "api/kases/assign/worker";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						var arrLength = arrKaseID.length;
						for(var i = 0; i < arrLength; i++) {
							var case_id = arrKaseID[i];
							$(".kase_data_row_" + case_id).css("background", "lime");
							
							var worker = removeHtml($(".token-input-token-kase").html());
							$(".workerInput_" + letter + " .token-input-list-kase").remove();
							worker = worker.substr(0, worker.length - 1);
							$(".worker_span_" + case_id).html(worker);
							
							$("#select_kase_" + case_id).attr("checked", false);
							$(".workerInput_" + letter).val("");
							$(".workerInput_" + letter).fadeOut();
							self.resetRow(case_id);
						}
					}
				}
		});
	},
	resetRow: function(case_id) {
		setTimeout(function() {
			$(".kase_data_row_" + case_id).css("background", "white");
		}, 2500);
	},
	hideAlphaSummary: function() {
		$(".alpha_summary").fadeOut();
	},
	doTimeouts: function() {
		//show the M?
		if (typeof this.model.get("matrix_display")!="undefined") {
			$(".matrix_sent_indicator").show();
		}
		//var filterCount = this.collection.models.length;
		var filterCount = this.model.get("found");
		if (this.model.get("individual_cases")) {
			filterCount = $("#individual_case_counter").val();
			if ($("#listing_dois").length > 0) {
				var listing_dois = $("#listing_dois").html();
				if (listing_dois!="") {
					setTimeout(function() {
						eval(listing_dois);
					}, 200);
				}
			}
		}
		
		$(".found_count").html(filterCount + " as of " + moment().format("MM/DD/YYYY"));
		if (document.location.hash.indexOf("employeekases")==0) {
			$("#search_terms_holder").html("");
		}
		var worker = this.model.get("worker");
		var blnFilterWorker = (worker!="");
		
		if (blnFilterWorker || document.location.hash=="#kasereport/opens") {
			return;
		}
		if (filter_attorney!="" && filter_worker=="") {
			filterKases("attorney");
		}
		if (filter_attorney=="" && filter_worker!="") {
			filterKases("worker");
		}
		if (filter_attorney!="" && filter_worker!="") {
			filterKases("worker");
		}
		if (typeof val_worker != "undefined" && typeof val_attorney != "undefined") {
			if (val_worker=="" && val_attorney=="") {
				return;
			}
		}
		
		if (document.location.hash=="#referralreport/bymonth") {
			$(".kase_list_header").hide();
			var holder = this.model.get("holder");
			$("#" + holder).show();
			$("#kases_summary_row").hide();
			
		} else {
			this.checkSent();
		}
	},
	checkSent: function() {
		if (!blnAdmin) {
			return;
		}
		var self = this;
		var report_case_ids = this.model.get("report_case_ids");
		//is this case in matrix
		var url = "../api/kases/matrix";
		var formValues = "id=" + report_case_ids;
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				var arrLength = data.length;
				for (var i = 0; i < arrLength; i++) {
					var datum = data[i];
					var indicator = $("#matrix_sent_indicator_" + datum.case_id);
					indicator.html('<span style="background:blue;color:yellow;font-weight:bold; padding:2px" title="Exported to Matrix on ' + datum.time_stamp + '">M</span>');
				}
			}
		});
	}
});
window.intake_listing_report = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {

	},
	render:function() {
		var self = this;
		if (typeof this.template != "function") {
			var view = "intake_listing_report";
			this.model.set("holder", "content");
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var kase_collection = this.collection.toJSON();
		$(this.el).html(this.template({kase_collection: kase_collection}));
		
		return this;
	}
});
window.kase_list_applicant_report = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #closed_kases_filter":					"filterCloseds",
		"click #open_kases_filter":						"filterOpens",
		"click #all_kases_filter":						"filterAlls",
		"click #send_kases":							"saveReport"
	},
	render:function() {
		var self = this;
		var kaseslist = this.collection.toJSON();
		var intCounter = 0;
		var arrWorkers = [];		//individual workers
		var arrWorkerNicknames = [];			//nicknames
		var arrWorkerOptions = [];		//individual workers options
		
		var arrAttys = [];		//individual atty
		var arrAttyNicknames = [];			//nicknames
		var arrAttyOptions = [];		//individual attys options
		
		_.each( kaseslist, function(kase) {
			if (kase.applicant == "undefined") { 
    			kase.applicant = "";
        	}
			kase.name = kase.name.replace(' - 00/00/0000', '');
			
			if (kase.referring == null) {
				kase.referring = "";
			}
			kase.referring_link = "";
			if (kase.referring!="") {
				kase.referring_link = kase.referring.replaceAll(" ", "_");
			}
			kase.background_color = "#FFFFFF";
			if ((intCounter%2)==0) {
				kase.background_color = "#EDEDED";
			}
			var arrKaseName = kase.name.split(" - ");
			kase.name = arrKaseName[0];
			if (kase.name == "  vs ") {
				kase.name = "";
			}
			//kase.doi = arrKaseName[1];
			if (kase.start_date!="0000-00-00" && kase.start_date!="00/00/0000") {
				kase.doi = moment(kase.start_date).format("MM/DD/YYYY");
				if (kase.end_date!="0000-00-00" && kase.end_date!="00/00/0000") {
					kase.doi +=  " - " + moment(kase.end_date).format("MM/DD/YYYY") + " CT";
				}
			}
			if (kase.doi == undefined) { 
				kase.doi = "";
			}
			if (kase.applicant == undefined) { 
				kase.applicant = "";
			}
			kase.full_name = kase.full_name.toLowerCase();
			kase.full_name = kase.full_name.capitalizeWords();
			
			if (kase.applicant_email!="") {
				kase.full_name += "<br><a href='mailto:" + kase.applicant_email + "'>" + kase.applicant_email + "</a>";
			}
			kase.adj_number = kase.adj_number.replaceAll(",", ", ");
			
			if (kase.applicant_street!="") {
				kase.applicant_full_address = kase.applicant_street;
				if (kase.applicant_suite!="") {
					kase.applicant_full_address += ", " + kase.applicant_suite;
				}
				kase.applicant_full_address += ", " + kase.applicant_city + ", " + kase.applicant_state + " " + kase.applicant_zip;
			}
			if (kase.statute_limitation!="" && kase.statute_limitation!="0000-00-00") {
				kase.statute_limitation = moment(kase.statute_limitation).format("MM/DD/YYYY");
			} else {
				kase.statute_limitation = "";
			}
			
			if (kase.worker_full_name==null) {
				kase.worker_full_name = "";
			}
			if (kase.attorney_full_name==null) {
				kase.attorney_full_name = "";
			}
			
			if (kase.worker_name=="" && kase.worker_full_name!="") {
				kase.worker_name = kase.worker_full_name.firstLetters();
			}
			
			if (kase.worker!="") {
				if (arrWorkers.indexOf(kase.worker) < 0) {
					if (!isNaN(kase.worker)) {
						var the_worker = worker_searches.findWhere({id:kase.worker, activated:"Y"});
					} else {
						var the_worker = worker_searches.findWhere({nickname:kase.worker, activated:"Y"});
					}
					if (typeof the_worker != "undefined") {
						arrWorkers.push(kase.worker);
						arrWorkerNicknames.push(the_worker.get("nickname"));
						//arrWorkernames[the_worker.get("nickname")] = the_worker.get("user_name");
					}
				}
			}
			if (kase.worker_name=="" && kase.worker!="") {
				kase.worker_name = kase.worker;
			}
			
			//atty
			if (kase.attorney_name=="" && kase.attorney_full_name!="") {
				kase.attorney_name = kase.attorney_full_name.firstLetters();
			}
			if (kase.attorney!="") {
				if (arrAttys.indexOf(kase.attorney) < 0) {
					if (!isNaN(kase.attorney)) {
						var the_attorney = worker_searches.findWhere({id:kase.attorney, activated:"Y"});
					} else {
						var the_attorney = worker_searches.findWhere({nickname:kase.attorney, activated:"Y"});
					}
					if (typeof the_attorney != "undefined") {
						arrAttys.push(kase.attorney);
						arrAttyNicknames.push(the_attorney.get("nickname"));
					}
				}
			}
			
			if (kase.attorney_name=="" && kase.attorney!="") {
				kase.attorney_name = kase.attorney;
			}

			kase.closed_indicator = "";
			kase.case_status = kase.case_status.replaceAll(' ', '&nbsp;');
			if (kase.case_status.toLowerCase().indexOf("cl")==0) {
				kase.closed_indicator = "color:white;background:red";
				if (kase.closed_date!="") {
					kase.case_status += "<br>" + moment(kase.closed_date).format("MM/DD/YYYY") + "";
				}
			}
			if (kase.case_status.toLowerCase()=="open") {
				kase.closed_indicator = "color:white;background:green";
				kase.case_status += "<br>" + moment(kase.case_date).format("MM/DD/YYYY") + "";
			}
			
			//attorney
			kase.attorney_name = "<span title='" + kase.attorney_full_name + "'>" + kase.attorney_name + "</span>";
			
			//months since start
			if (kase.case_status.toLowerCase()!="closed") {
				if (kase.months_diff < 32) {
					kase.months_diff = "&nbsp;<span style='background:green;color:white;padding:2px'>Current</span>";
				} else {
					if (kase.months_diff > 31 && kase.months_diff < 62) {
						kase.months_diff = "&nbsp;(<span style='background:orange;color:white;padding:2px'>2</span> months)";
					} else {
						kase.months_diff = "&nbsp;(<span style='background:red;color:white;padding:2px'>3+</span> months)";
					}
				}
			} else {
				kase.months_diff = "";
			}

			intCounter++;
		});
		var title = "Kases Report - " + user_customer_name;
		if (window.location.href.indexOf("/active") > -1) {
			title = "Client Kase List - " + user_customer_name;
		}
		
		if (window.location.href.indexOf("?s") > -1) {
			var sub_title = decodeURI(location.search.replace("?s=", ""));
			sub_title = sub_title.split("|").join("&nbsp;|&nbsp;");
			sub_title = sub_title.replaceTout("+", " ");
			title += "<div style='font-size:0.8em'>" + sub_title + "</div>";
		}
		$(this.el).html(this.template({kaseslist: kaseslist, year: this.model.get("year"), month: this.model.get("monthname"), referring: this.model.get("referring"), title:title}));
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		
		return this;
	},
	filterCloseds: function() {
		$(".kase_status_open").hide();
		$(".kase_status_closed").show();
	},
	filterOpens: function() {
		$(".kase_status_closed").hide();
		$(".kase_status_open").show();
	},
	filterAlls: function() {
		$(".kase_status_closed").show();
		$(".kase_status_open").show();
	},
	doTimeouts: function() {
		if (filter_attorney!="" && filter_worker=="") {
			filterKases("attorney");
		}
		if (filter_attorney=="" && filter_worker!="") {
			filterKases("worker");
		}
		if (filter_attorney!="" && filter_worker!="") {
			filterKases("worker");
		}
		if (typeof val_worker != "undefined" && typeof val_attorney != "undefined") {
			if (val_worker=="" && val_attorney=="") {
				return;
			}
		}
		var filterCount = this.collection.models.length;
		$("#found_count").html(filterCount + " as of " + moment().format("MM/DD/YYYY"));
	},
	saveReport: function() {
		var html = $("#kases_list").html();
		html = html.replaceAll("&", "||");
		var formValues = "report=client_summary&client_id=" + this.model.get("referring");
		formValues += "&content=" + encodeURIComponent(html);
		
		var url = "api/reports/add";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
				success:function (data) {
					//console.log(data);
					//$("#send_kases_holder").html("<a href='feedback/" + data.filename + "' target='_blank'>Review</a>");
				}
		});
	}
});
window.kase_summary_listing = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .kase_user": 					"expandSummary",
		"click .workload":						"showWorkload",
		"click .print_workload":				"printWorkload",
		"click .listkases":						"expandSummaryByStatus",
		"click .listkasestype":					"expandSummaryByType",
		"click #print_kase_summary":			"printSummary",
		"change #allkasestatus":				"selectAllStatus",
		"change .checkkasestatus":				"enableListSelectedStatus",
		"click #printstatus_selected":			"listSelectedStatus",
		"change #allkasetype":					"selectAllType",
		"change .checkkasetype":				"enableListSelectedType",
		"click #printtype_selected":			"listSelectedType"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "kase_summary_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
			
		var self = this;
		var summary_kases = this.collection.toJSON();
		this.model.set("current_user_id", "-1");
		$(this.el).html(this.template({kases: summary_kases}));
		
		setTimeout(function() {
			$("#kase_summary_list_outer_div").css("height", (window.innerHeight - 80) + "px");
			$("#preview_block_holder").css("height", (window.innerHeight - 55) + "px");
			$("#kase_summary_listing th, td").css("font-size", "1.6em");
			if (document.location.href.indexOf("report.php") > -1) {
				//report mode
				$("#report_title").css("color", "black");
				document.getElementById("print_kase_summary").parentElement.innerHTML = "as of " + moment().format("M/D/Y H:mA");
				
				$("#kase_summary_list_outer_div").css({"overflow-y": "", "width": "30vw", "margin-left":"auto", "margin-right":"auto"});
				$("#kase_summary_listing").css("width", "100%")
				$("#kase_summary_listing").attr("align", "center");
			}
		}, 1000);
		
		return this;
	},
	printWorkload: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var url = "api/kases_workload/" + id;
		window.open(url);
	},
	showWorkload: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		var url = "api/kases_workload/" + id;
			
		$.ajax({
			url:url,
			type:'GET',
			dataType:"text",
			success:function (data) {
				if (data.length > 0) {
					$("#kase_summary_list_outer_div").css("width", "25%");
					$("#preview_pane_holder").css("width", "75%");
					$("#preview_pane_holder").css("height", "");
					$("#preview_pane_holder").css("background", "url(img/glass_dark.png) repeat");
					$("#preview_block_holder").css("overflow-y", "");
					$("#preview_pane_holder").show();
					$("#preview_pane").html(data);
					$("#workload_title").hide();
					$(".workload_table").css("min-width", "350px");
					$("#preview_pane").show();
					$(".print_workload").show();
					
				}
			}
		});
	},
	expandSummary: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[elementArray.length - 1];
		
		
		$(".user_kase_row_" + this.model.get("current_user_id")).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			var worker_kases = new KaseWorkerCollection({ user_id: id });
			worker_kases.fetch({
				success: function (data) {
					if (data.length > 0) {
						var worker = worker_searches.findWhere({id: id});
						$("#preview_pane").hide();
						var kase_listing_info = new Backbone.Model;
						var user_name = worker.get("user_name");
						var title = user_name.capitalizeAllWords() + " Kases";
						kase_listing_info.set("title", title);
						kase_listing_info.set("holder", "preview_pane");
						kase_listing_info.set("sort_by", "last_name");
						kase_listing_info.set("homepage", true);
						kase_listing_info.set("user_id", id);
						kase_listing_info.set("user_name", user_name);
						$('#preview_pane').html(new kase_listing_view({collection: data, model: kase_listing_info}).render().el);
						$("#preview_pane").removeClass("glass_header_no_padding");
						
					} else {
						$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
					}
				}
			});
		});
		
	},
	expandSummaryByStatus: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var case_status = element.id.replace("listkases_", "");
		if (case_status=="") {
			case_status = "_";
		}
		var id = $("#user_id").val();
		
		$(".user_kase_row_" + id).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			
			var formValues = "user_id=" + id;
			formValues += "&case_status=" + case_status;
			
			var url = "api/kases_worker_status";
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						if (data.length > 0) {
							var worker = worker_searches.findWhere({id: id});
							$("#preview_pane").hide();
							var kase_listing_info = new Backbone.Model;
							var user_name = worker.get("user_name");
							var title = user_name.capitalizeAllWords() + " - " + case_status.replace("_", " ").toUpperCase().capitalizeAllWords() + " Kases";
							kase_listing_info.set("title", title);
							kase_listing_info.set("holder", "preview_pane");
							kase_listing_info.set("sort_by", "last_name");
							kase_listing_info.set("homepage", true);
							kase_listing_info.set("user_id", id);
							kase_listing_info.set("user_name", user_name);
							var collection = new Backbone.Collection();
							collection.add(data);
							$('#preview_pane').html(new kase_listing_view({collection: collection, model: kase_listing_info}).render().el);
							$("#preview_pane").removeClass("glass_header_no_padding");
							
						} else {
							$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
						}
					}
				}
			});
		});
		
	},
	expandSummaryByType: function(event) {
		event.preventDefault();
		
		var element = event.currentTarget;
		var case_type = element.id.replace("listkasestype_", "");
		var id = $("#user_id").val();
		
		$(".user_kase_row_" + id).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			
			var formValues = "user_id=" + id;
			formValues += "&case_type=" + case_type;
			
			var url = "api/kases_worker_type";
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						if (data.length > 0) {
							var worker = worker_searches.findWhere({id: id});
							$("#preview_pane").hide();
							var kase_listing_info = new Backbone.Model;
							var user_name = worker.get("user_name");
							var title = user_name.capitalizeAllWords() + " - " + case_type.replace("_", " ").toUpperCase().capitalizeAllWords() + " Kases";
							kase_listing_info.set("title", title);
							kase_listing_info.set("holder", "preview_pane");
							kase_listing_info.set("sort_by", "last_name");
							kase_listing_info.set("homepage", true);
							kase_listing_info.set("user_id", id);
							kase_listing_info.set("user_name", user_name);
							var collection = new Backbone.Collection();
							collection.add(data);
							$('#preview_pane').html(new kase_listing_view({collection: collection, model: kase_listing_info}).render().el);
							$("#preview_pane").removeClass("glass_header_no_padding");
							
						} else {
							$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
						}
					}
				}
			});
		});
		
	},
	shrinkSummary: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#preview_pane").html("");
			$("#kase_summary_list_outer_div").css("width", "100%");		
		});
		$(".user_kase_row_" + this.model.get("current_user_id")).css("background", "");
		this.model.set("current_user_id", -1);
	},
	printSummary: function() {
		var url = "report.php#kasessummary";
		window.open(url);
	},
	listSelectedStatus: function(event) {
		event.preventDefault();
		
		var checkkasestatuss = document.getElementsByClassName("checkkasestatus");
		var arrLength = checkkasestatuss.length;
		var arrCaseStatus = [];
		for (var i = 0; i < arrLength; i++) {
			if (checkkasestatuss[i].checked) {
				arrCaseStatus.push(checkkasestatuss[i].value.replaceAll("_", " "));
			}
		}
		
		var id = $("#user_id").val();
		
		$(".user_kase_row_" + id).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			
			var formValues = "user_id=" + id;
			formValues += "&case_status=" + arrCaseStatus.join("|");
			
			var url = "api/kases_worker_status";
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						if (data.length > 0) {
							var worker = worker_searches.findWhere({id: id});
							$("#preview_pane").hide();
							var kase_listing_info = new Backbone.Model;
							var user_name = worker.get("user_name");
							var title = user_name.capitalizeAllWords() + " - Kases<br>" + arrCaseStatus.join(" - ");
							kase_listing_info.set("title", title);
							kase_listing_info.set("holder", "preview_pane");
							kase_listing_info.set("sort_by", "last_name");
							kase_listing_info.set("homepage", true);
							kase_listing_info.set("user_id", id);
							kase_listing_info.set("user_name", user_name);
							var collection = new Backbone.Collection();
							collection.add(data);
							$('#preview_pane').html(new kase_listing_view({collection: collection, model: kase_listing_info}).render().el);
							$("#preview_pane").removeClass("glass_header_no_padding");
							
						} else {
							$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
						}
					}
				}
			});
		});
	},
	selectAllStatus: function() {
		$(".checkkasestatus").prop("checked", $("#allkasestatus").prop("checked"));
		
		this.enableListSelectedStatus();
	},
	enableListSelectedStatus: function() {
		var blnDisabled = true;
		var checkkasestatuss = document.getElementsByClassName("checkkasestatus");
		var arrLength = checkkasestatuss.length;
		for (var i = 0; i < arrLength; i++) {
			if (checkkasestatuss[i].checked) {
				blnDisabled = false;
				break;
			}
		}
		
		document.getElementById("printstatus_selected").disabled = blnDisabled;
	},
	listSelectedType: function(event) {
		event.preventDefault();
		
		var checkkasetypes = document.getElementsByClassName("checkkasetype");
		var arrLength = checkkasetypes.length;
		var arrCaseType = [];
		for (var i = 0; i < arrLength; i++) {
			if (checkkasetypes[i].checked) {
				let case_type = checkkasetypes[i].value.replaceAll("_", " ");
				case_type = case_type.replace("Newpi", "PI");
				arrCaseType.push(case_type);
			}
		}
		
		var id = $("#user_id").val();
		
		$(".user_kase_row_" + id).css("background", "");
		this.model.set("current_user_id", -1);
		
		hidePreview();
		
		var self = this;
		
		this.model.set("current_user_id", id);
		
		this.model.set("current_background", $(".user_kase_row_" + id).css("background"));
		$(".user_kase_row_" + id).css("background", "#F90");
		
		$("#kase_summary_list_outer_div").css("width", "25%");
		$("#preview_pane_holder").css("width", "75%");
		
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//load the kases into the pane
			
			var formValues = "user_id=" + id;
			formValues += "&case_type=" + arrCaseType.join("|");
			
			var url = "api/kases_worker_type";
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						if (data.length > 0) {
							var worker = worker_searches.findWhere({id: id});
							$("#preview_pane").hide();
							var kase_listing_info = new Backbone.Model;
							var user_name = worker.get("user_name");
							var title = user_name.capitalizeAllWords() + " - Kases<br>" + arrCaseType.join(" - ");
							kase_listing_info.set("title", title);
							kase_listing_info.set("holder", "preview_pane");
							kase_listing_info.set("sort_by", "last_name");
							kase_listing_info.set("homepage", true);
							kase_listing_info.set("user_id", id);
							kase_listing_info.set("user_name", user_name);
							var collection = new Backbone.Collection();
							collection.add(data);
							$('#preview_pane').html(new kase_listing_view({collection: collection, model: kase_listing_info}).render().el);
							$("#preview_pane").removeClass("glass_header_no_padding");
							
						} else {
							$('#preview_pane').html("<span class='large_white_text'>No Assigned Kases</span>");
						}
					}
				}
			});
		});
	},
	selectAllType: function() {
		$(".checkkasetype").prop("checked", $("#allkasetype").prop("checked"));
		
		this.enableListSelectedType();
	},
	enableListSelectedType: function() {
		var blnDisabled = true;
		var checkkasetypes = document.getElementsByClassName("checkkasetype");
		var arrLength = checkkasetypes.length;
		for (var i = 0; i < arrLength; i++) {
			if (checkkasetypes[i].checked) {
				blnDisabled = false;
				break;
			}
		}
		
		document.getElementById("printtype_selected").disabled = blnDisabled;
	}
});

window.kase_transfer_alpha = Backbone.View.extend({

    initialize:function () {
       
    },
	events: {
		"click .choose_letter":								"chooseLetter",
		"click #transfer_cases":							"transferCases",
		"click #kase_transfer_alpha_all_done":				"doTimeouts"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "kase_transfer_alpha";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var alphas = this.collection.toJSON();
		var mymodel = this.model.toJSON();
		
		$("#content").html("Loading...");
		$(this.el).html(this.template({alphas: alphas}));
		
		
		return this;
    },
	transferCases: function(event) {
		event.preventDefault();
		
		//need someone to transfer to
		if ($("#assigneeInput").val()=="") {
			$(".token-input-list-task").css("border", "2px solid red");
			return;
		}
		//get all the ids
		var letter_ids = $(".letter_ids");
		var arrIDs = [];
		for (var i = 0; i < letter_ids.length; i++) {
			var letter_id = letter_ids[i];
			if (letter_id.value!="") {
				var val = letter_id.value;
				arrIDs.push(val.trim());
			}
		}
		$("#letter_ids").val(arrIDs.join(","));
		
		saveTransferKaseModal();
	},
	chooseLetter: function(event) {
		var self = this;
		if ($(".bulk_kase_transfer").css("display")== "none") {
			$(".bulk_kase_transfer").fadeIn();
		}
		
		var element = event.currentTarget;
		var first_letter = element.id.replace("choose_letter_", "");
		
		$("#letter_ids_" + first_letter).val("");
		if ($("#letter_" + first_letter).css("background")=="black" || $("#letter_" + first_letter).css("background")=="rgb(0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box") {
			$("#letter_" + first_letter).css("background", "green");
			
			//go get the ids
			var url = "api/casecountbyletter/" + first_letter;
			
			$.ajax({
				url:url,
				type:'GET',
				dataType:"text",
				success:function (data) {
					$("#letter_ids_" + first_letter).val(data);
				}
			});
		} else {
			$("#letter_" + first_letter).css("background", "rgb(0, 0, 0) none repeat scroll 0% 0% / auto padding-box border-box");
		}
		setTimeout(function() {
			self.getLetterIDs();
		}, 1000);
	},
	getLetterIDs: function() {
		//summary
		var arrLetters = [];
		var letter_clicks = $(".letter_click");
		for(var i = 0; i < letter_clicks.length; i++) {
			var letter_click = letter_clicks[i];
			if ($("#" + letter_click.id).css("background")=="green" || $("#" + letter_click.id).css("background")=="rgb(0, 128, 0) none repeat scroll 0% 0% / auto padding-box border-box") {
				arrLetters.push(letter_click.id.replace("letter_", ""));
			}
		}
		if (arrLetters.length > 0) {
			$("#transfer_from").html(arrLetters.join("; "));
		}
	},
	doTimeouts: function() {
		var theme = {theme: "task", tokenLimit: 1};
		$("#assigneeInput").tokenInput("api/user", theme);
		
		var alphas = this.collection.toJSON();
		_.each( alphas, function(alpha) {
			if (alpha.first_letter!="") {
				$("#letter_" + alpha.first_letter).html("<span class='choose_letter' id='choose_letter_" + alpha.first_letter + "' style='cursor:pointer'>" + alpha.first_letter + " (" + alpha.case_count+ ")</span><input id='letter_ids_" + alpha.first_letter + "'  class='letter_ids' type='hidden' id='' value='' />");
				$("#letter_" + alpha.first_letter).css("background", "black");
			}
			
		});
	}
});