var show_link_id;
var kase_list;
window.kase_listing_view_mobile = Backbone.View.extend({

    initialize:function () {
        //console.log("samba");
    },
	events: {
		"click .list_notes_mobile":						"listNotes",
		"click #kase_listing_mobile_all_done":			"doTimeouts"
	},
    render:function () {		
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
		if (typeof this.model.get("search_parameters") == "undefined") {
			this.model.set("search_parameters", "");
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
		var blnRemovedKases = false;
		_.each( kase_list, function(kase) {
			var mobile_case_id = kase.case_id;
			//remove any repeats
			kase.skip_me = false;
			if (previous_kase.case_id == kase.case_id && previous_kase.adj_number == kase.adj_number && previous_kase.start_date == kase.start_date && previous_kase.end_date == kase.end_date) {
				//that should not happen
				kase.skip_me = true;
			}
			previous_kase.case_id =  kase.case_id;
			previous_kase.adj_number = kase.adj_number;
			previous_kase.start_date = kase.start_date;
			previous_kase.end_date = kase.end_date;
			
        	if (kase.end_date == "00/00/0000"){
            	kase.end_date = "";
            }
            if (kase.end_date != "") {
                kase.end_date =  " - " + kase.end_date + " CT";
            }
			if (kase.defendant=="") {
            	kase.defendant = "No Defendant";
            }
			
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
			var kase_type = kase.case_type;
			var blnWCAB = isWCAB(kase_type);
			if (blnWCAB) {
				if (kase.employer_id==-1) {
					kase.employer = "No Employer";
				}
			} else {
				if (kase.defendant_id==-1) {
					kase.defendant = "No Defendant";
				}
			}
			if (blnWCAB) {
				if (kase.employer.length > 15) {
					kase.employer = kase.employer.substring(0, 15) + "..."
				}
			} else {
				if (kase.defendant.length > 15) {
					kase.defendant = kase.defendant.substring(0, 15) + "..."
				}
			}
			if (kase.full_name!="" && kase.full_name!=null) {
            	kase.alpha_name = kase.full_name;
				if (blnWCAB) {
					kase.full_name = "<a href='' class='list-item_kase' style='color:white' onClick='showTabs(" + kase.case_id + ")'>" + highLight(kase.full_name, key).replaceAll(" ", "&nbsp;") + "</a>&nbsp;vs&nbsp;<a href='#parties/" + kase.case_id + "/" + kase.employer_id + "/employer' class='list-item_kase' style='color:white'>" + highLight(kase.employer, key).replaceAll(" ", "&nbsp;") + "</a>";
				} else {
					kase.full_name = "<a href='' class='list-item_kase' style='color:white' onClick='showTabs(" + kase.case_id + ")'>" + highLight(kase.full_name, key).replaceAll(" ", "&nbsp;") + "</a>&nbsp;vs&nbsp;<a href='#parties/" + kase.case_id + "/" + kase.defendant_id + "/defendant' class='list-item_kase' style='color:white'>" + highLight(kase.defendant, key).replaceAll(" ", "&nbsp;") + "</a>";
				}
			} else {
	            kase.full_name = "No Applicant";
				kase.alpha_name = "*";
            }
			if (self.model.get("sort_by")=="last_name" && kase.last_name!="" && kase.last_name!=null) {
				kase.alpha_name = kase.last_name;
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
			kase.lien_border = "";
			if (kase.lien_id > 0) {
				kase.lien_border = "background: black; padding:2px";
			}
			kase.settlement_border = "";
			if (kase.settlement_id > 0 || kase.fee_id > 0) {
				kase.settlement_border = "background: black; padding:2px";
			}
			
			//a1
			if (user_data_path == 'A1') {
				kase.case_number = kase.cpointer;
			}
			
			if (arrKaseNumbers.indexOf(kase.case_number) < 0) {
				arrKaseNumbers.push(kase.case_number);
			}
			kase.occupation = kase.occupation.replace(' ', '&nbsp;');
			kase.occupation = kase.occupation.replace('/', ' , ');
			kase.occupation = kase.occupation.replace(',', ' , ');
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
				var the_worker = worker_searches.findWhere({nickname:element, activated:"Y"});
				
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
		
		
		
		self.collection = kase_list;
		$(this.el).html(this.template({kases: self.collection, key: self.model.get("key"), kases_count: arrKaseNumbers.length, kase_workers: kase_workers, kase_attys: kase_attys}));
		
		return this;
    },
	listNotes: function (event) {	
		var element = event.currentTarget;
		var element_id = element.id;
		//alert(element_id);
		var arrElement = element_id.split("_");
		var mobile_case_id = arrElement[arrElement.length - 1];
		//alert(mobile_case_id);
		var type = "";
		
		notes = new NoteCollection([], { case_id: mobile_case_id });
		
		notes.fetch({
			success: function(data) {
				var note_list_model = new Backbone.Model;
				note_list_model.set("display", "full");
				note_list_model.set("partie_type", "note");
				note_list_model.set("partie_id", -1);
				note_list_model.set("case_id", mobile_case_id);
				$('#note_list_mobile').html(new note_listing_view({collection: data, model: note_list_model}).render().el);
				$('.note_listing').css("width", "98%");
				$('.note_listing').css("margin-left", "5px");
				$("#glass_header").css("margin-left", "5px");
				$("#glass_header").css("width", "98%");
				$("#glass_header").removeClass("glass_header");
				
				hideEditRow();
			}
		});	
			
	},
	stopSearch: function(event) {
		$("#search_results").html("");
		blnSearchingKases = false;
	},
	filterKasesByWorker: function(event) {
		filterKases("worker");
	},
	filterKasesByAtty: function(event) {
		filterKases("attorney");
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
		$('#ikase_loading').html("");
		
		var current_letter = "";
		$(".letter_click").css("color","#000");
		$(".letter_click").css("background","#E2A624");
		$(".letter_click").css("cursor","context-menu");
		$(".letter_click").addClass("turned_off");
		_.each( kase_list, function(kase) {
			//we might have a new letter
			var the_letter = "";
			if (kase.alpha_name.trim()!="") {
				the_letter = kase.alpha_name.trim().charAt(0);
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
			if (current_letter != the_letter && the_letter!="*" && the_letter!="") {
				current_letter = the_letter;
				$("#" + current_letter).css("color","white");
				$("#" + current_letter).css("background","url(../../img/glass_modal_low.png)");
				$("#" + current_letter).css("cursor","pointer");
				$("#" + current_letter).removeClass("turned_off");
			}
		});
		
		if (typeof this.collection != "undefined") {
			if (this.collection.length > 0) {
				if (typeof $("#kase_listing_mobile").parent().parent()[0] != "undefined") {
					if ($("#kase_listing_mobile").parent().parent()[0].id == "search_results") {
						$(".pager").hide();
						$("#list_kases_header").hide();
					}
				}
				tableSortIt("kase_listing_mobile");
			}
		}
		
		//show sorting
		if (self.model.get("sort_by")!="") {
			$("#kases_start_by").val(self.model.get("sort_by"));
		}
		
		if (self.model.get("recent")=="Y") {
			$("#kase_status_title").html("Recent");
		}
		
		if (self.model.get("search_parameters")!="") {
			var arrSearchParameters = []
			arrFormValues = self.model.get("search_parameters").split("&");
			arrFormValues.forEach(function(element, index, array) {
					var param = element;
					var arrParam = param.split("=");
					if (arrParam[1]!="") {
						arrSearchParameters.push(arrParam[1].replaceTout(arrParam[1]));
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
		$("#srch-term").blur();
		
		kase_searching = false;
	},
    newKase:function (event) {
        composeKase(event);
    },
	newTask: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	showAll: function(){
		$('#ikase_loading').html(loading_image);
		
		blnSearchingKases = true;
		showDBResults(kases, "");
		return;
		
		var _alphabets = $('.alphabet > a');
		_alphabets.removeClass("active");
		var _contentRows = $('#kase_listing_mobile tbody tr');
		$("#kase_show_all").addClass("active");
		_contentRows.fadeIn(400);
	},
	letterClick: function(event) {
		$('#ikase_loading').html(loading_image);
		
		var element = event.currentTarget;
		if ($("#" + element.id).hasClass("turned_off")) {
			return;
		}
		blnSearchingKases = true;
		var alpha_kases = new KaseCollection;
		var sort_by = $("#kases_sort_by").val();
		var arrSortBy = sort_by.split("_");
		starts_with = "starts_with_" + arrSortBy[0];
		alpha_kases.searchDB(element.id, starts_with);
		
		return;
		var _alphabets = $('.alphabet > a');
		var _contentRows = $('#kase_listing_mobile tbody tr');
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
	},
	showNewWindowLink: function(event) {
		event.preventDefault();
		clearTimeout(show_link_id);
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		$(".kase_windowlink").fadeOut("slow");
		setTimeout(function() {
			$("#windowlink_" + theid).fadeIn("slow");
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
	editKase:function (event) {
		var element = event.currentTarget;
        composeKaseEdit(element.id)
    },
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
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
		composeDelete(id, "injury");
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