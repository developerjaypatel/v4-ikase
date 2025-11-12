var reset_timeout = false;
var blnPrinting =  false;
window.event_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click .print_today":						"printToday",
		"click #new_event": 						"newEvent",
		"click .read_holders":						"readEvent",
		"click .delete_event": 						"confirmdeleteEvent",
		"click .delete_yes":						"deleteEvent",
		"click .delete_no":							"canceldeleteEvent",
		"mouseover #event_preview_panel": 			"freezePreview",
		"mouseover .event_preview_link": 			"freezePreview",
		"click .edit_event":						"newEvent",
		"click .expand_occurence": 					"expandOccurence",
		"click .shrink_occurence": 					"shrinkOccurence",
		"click .check_all":							"checkAll",
		"change #mass_change":						"massChange",
		"change #event_typeFilter":					"filterCalendar",
		"change #assigneeFilter":					"filterAssigneeCalendar",
		"click #update_date_range":					"updateRange",
		"keyup #dayview_start_date":				"scheduleResetDay",
		"click .assign_pending":					"assignPending",
		"click .approve_pending":					"approvePending",
		"click .dismiss_pending":					"dismissPending",
		"click #add_event":							"addEvent"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "event_listing";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		blnFiltering = false;
		thetitle = "";
		thehomepage = false;
		if (typeof this.model != "undefined") {
			if (typeof this.model.get("homepage") == "undefined") {
				this.model.set("homepage", false);
			}
			thetitle = this.model.get("title");
			thehomepage = this.model.get("homepage");
		}
		
		var thehash = document.location.hash;
		var kasepage = (thehash.indexOf("kalendarbydate") == 1 || thehash.indexOf("kalendarlist") == 1);
		
		this.collection.bind("reset", this.render, this);
		
		
		var occurences = this.collection.toJSON();
		//console.log("56 "+occurences);
		var arrDayCount = [];
		var min_date = moment("2100-01-01").format("YYYY-MM-DD");
		var max_date = moment("2000-01-01").format("YYYY-MM-DD");
		var blnCourtCalendar = (this.model.get("title") == "Unassigned Court Calendar Events");
		_.each( occurences, function(occurence) {
			var blnRelatedCase = false;
			if (kasepage) {
				if (occurence.case_id!=current_case_id) {
					blnRelatedCase = true;
				}
			}
			if (moment(occurence.event_dateandtime) < moment(min_date)) {
				min_date = occurence.event_dateandtime;
			}
			if (moment(occurence.event_dateandtime) > moment(max_date)) {
				max_date = occurence.event_dateandtime;
			}
			occurence.kase_link_background = ""; 
			occurence.kase_link_indicator = ""; 
			if (blnRelatedCase) {
				occurence.kase_link_background = ";background:orange;text-decoration:underline";
				occurence.kase_link_indicator = "*"; 
			}
			if (moment(occurence.event_dateandtime).format('h:mm a')=="12:00 am") {
				occurence.time = "";
			} else {
				occurence.time = moment(occurence.event_dateandtime).format('h:mm a');
			}
			//clean up title
			occurence.event_title = occurence.event_title.replace(" - 00/00/0000", "");
			
			if (occurence.event_name != "") {
				occurence.event_title = occurence.event_name;
			}
			if (typeof occurence.location == "undefined") {
				occurence.location = "";
			}
			if (occurence.assignee==null) {
				occurence.assignee = "";
			} 
			if (typeof occurence.case_type == "undefined") {
				occurence.case_type = "nocase";
			}
			if (occurence.case_type == null) {
				occurence.case_type = "nocase";
			}
			if (occurence.case_number == null) {
				occurence.case_number = "";
			}
			if (occurence.file_number == null) {
				occurence.file_number = "";
			}
			var kase_type = occurence.case_type;
			var blnWCAB = isWCAB(kase_type);
			if (blnWCAB) {
				occurence.case_type = "WC";
			}
			if (typeof occurence.supervising_attorney == "undefined") {
				occurence.supervising_attorney = "";
			}
			if (!isNaN(occurence.supervising_attorney)) {
				var thesupv = worker_searches.findWhere({"id": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					occurence.supervising_attorney = thesupv.get("nickname");
				}
			}
			if (blnCourtCalendar) {
				if (occurence.assignee=="") {
					occurence.assignee = occurence.attorney;
				}
			}
			occurence.assignee = occurence.assignee.toLowerCase();
			
			if (occurence.case_stored_name != "") {
				occurence.case_name = occurence.case_stored_name;
			}
			var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			//maybe court calendar import?
			if (typeof occurence.transfer_status == "undefined") {
				occurence.transfer_status = "";
			}
			occurence.transfer_status_color = "";
			/*
			if (occurence.transfer_status!="") {
				switch (occurence.transfer_status) {
					case "pending":
						occurence.transfer_status_color = "background:orange;padding:1px";
						break;
					case "imported":
						//maybe another color later
					case "in":
						occurence.transfer_status_color = "background:lime; color:black; padding:1px";
						break;
				}
			}
			*/
		});
		//for event class
		if (typeof this.model.get("event_class") == "undefined") {
			this.model.set("event_class", "events");
		}
		
		if (typeof this.model.get("start") == "undefined") {
			this.model.set("start", min_date);
		}
		if (typeof this.model.get("end") == "undefined") {
			this.model.set("end", max_date);
		}
		if (typeof this.model.get("worker") == "undefined") {
			this.model.set("worker", "");
		}
		if (typeof this.model.get("thetype") == "undefined") {
			this.model.set("thetype", "");
		}
		if (typeof this.model.get("import_date") == "undefined") {
			 this.model.set("import_date", "")
		 }
		$(this.el).html(this.template({occurences: occurences, title: thetitle, homepage: thehomepage, kasepage: kasepage, event_class: this.model.get("event_class"), arrDayCount: arrDayCount, start: this.model.get("start"), end: this.model.get("end"), worker: this.model.get("worker"), thetype: this.model.get("thetype"), import_date: this.model.get("import_date") }));
		
		setTimeout(function(){
			tableSortIt();
		}, 100);
		setTimeout(function() {
			if (self.model.get("title").indexOf("Court Calendar Events") > -1) {
				$("#assigneeFilter").hide();
				$("#event_typeFilter").hide();
				$("#event_print_links").hide();
				$("#row_1_col_1 .print_today").hide();
			}
			
			if (document.location.hash=="#phonereport") {
				$("#assigneeFilter").hide();
				$("#event_typeFilter").hide();
				$("#add_event").hide();
				$("#event_print_links").hide();
				$(".print_today").hide();
				var html = $(".glass_header").html().replace("&nbsp;|&nbsp;Date Range", "Date Range");
				$(".glass_header").html(html);
			}
		}, 555);
		setTimeout(function() {
			rangeDates();
			
			//update the filter if necessary
			var hash = document.location.hash;
			var arrHash = hash.split("/");
			var new_filter = "";
			if (arrHash[1]=="wcab") {
				new_filter = "case_type_wc";
			}
			if (arrHash[1]=="pi") {
				new_filter = "case_type_pi";
			}
			if (new_filter!="") {
				$("#event_typeFilter").val(new_filter);
				$("#event_typeFilter").trigger("change");
			}
			
			if (arrHash[0]=="#kalendarlist") {
				//show the kalendar view
				$("#kase_kalendar_view").html("&nbsp;|&nbsp;<a href='#kalendar/" + current_case_id + "' class='white_text'>KALENDAR VIEW</a>");
				$("#kase_kalendar_view").css("display", "inline-block");
				$(".listing .glass_header").css("height", "45px");
			}
			
			$(".occurence_listing td").css("font-size", "1.3em");
			$(".occurence_listing th").css("font-size", "1.3em");
		}, 1100);
		
		return this;
    },
	assignPending: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		
		var event_id = elementArray[elementArray.length - 2];
		var courtcalendar_id = elementArray[elementArray.length - 1];
		
		//hide the assign button, replace with approve button
		//show the assign box
		$("#assign_pending_" + event_id + "_" + courtcalendar_id).fadeOut(function() {
			$("#dismiss_pending_" + event_id + "_" + courtcalendar_id).hide();
			$("#approve_pending_" + event_id + "_" + courtcalendar_id).show();
			var theme_3 = {
				theme: "event",
				onAdd: function(item) {
					
				}
			};
			$("#assigneeInput_" + event_id + "_" + courtcalendar_id).tokenInput("api/user", theme_3);
			$(".token-input-list-event").css("width", "203px");
			$("#assign_event_" + event_id + "_" + courtcalendar_id).css("visibility", "visible");
			
			$("#token-input-assigneeInput_" + event_id + "_" + courtcalendar_id).focus();
		});
	},
	approvePending: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		
		var event_id = elementArray[elementArray.length - 2];
		var courtcalendar_id = elementArray[elementArray.length - 1];
		var assignee = $("#assigneeInput_" + event_id + "_" + courtcalendar_id).val();
		
		var url = 'api/event/transfercc';
		formValues = "event_id=" + event_id;
		formValues += "&courtcalendar_id=" + courtcalendar_id;
		formValues += "&assignee=" + assignee;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error tasks
					saveFailed(data.error.text);
				} else {
					$("#pending_buttons_holder_" + event_id + "_" + courtcalendar_id).html("<span style='background:green;color:white;padding:2px'>Approved&nbsp;&#10003;</span>");
					
					setTimeout(function() {
						$(".occurence_row_" + event_id).fadeOut();
					}, 2500);
				}
			}
		});
	},
	dismissPending: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		
		var event_id = elementArray[elementArray.length - 2];
		var courtcalendar_id = elementArray[elementArray.length - 1];
		
		var url = 'api/event/dismisscc';
		formValues = "event_id=" + event_id;
		formValues += "&courtcalendar_id=" + courtcalendar_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error tasks
					saveFailed(data.error.text);
				} else {
					$("#pending_buttons_holder_" + event_id + "_" + courtcalendar_id).html("<span style='background:red;color:white;padding:2px'>Dismissed&nbsp;&#10007;</span>");
				}
			}
		});
	},
	addEvent: function(event) {
		var arrHash = document.location.hash.split("/");
		var day_date = moment()._d.getTime();
		if (arrHash[0]=="#kalendarlist") {
			//document.location.href = "#kalendar/" + arrHash[1];
			var element_id = "-1_" + arrHash[1] + "_" + day_date + "_" + day_date;
		}
		if (arrHash[0]=="#listkalendar") {
			//document.location.href = "#ikalendar/" + arrHash[1] + "/" + arrHash[2];
			var element_id = "-1_-1_" + day_date + "_" + day_date;
		}
		composeEvent(element_id);
	},
	printToday: function(event) {
		if (blnPrinting) {
			return;
		}
		blnPrinting = true;
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var firm_calendar_id = elementArray[elementArray.length - 3];
		var start_date = elementArray[elementArray.length - 2];
		var end_date = elementArray[elementArray.length - 1];
		var the_worker = $("#assigneeFilter").val();
		if (typeof the_worker == "undefined") {
			the_worker = "";
		}
		
		//need to update the calendar_id
		var hash = document.location.hash;
		var arrHash = hash.split("/");
		
		if (arrHash[0].indexOf("userkalendar") > -1) {
			firm_calendar_id = 5;
			var sort_order = 5;
			
			var arrNewHash = ["#userkalendar", "5", "5", arrHash[2], arrHash[3]];
			arrHash = arrNewHash;
		} else {
			firm_calendar_id = arrHash[1];
			var sort_order = arrHash[2];
		}
		
		//other calendars
		if (firm_calendar_id!=1 && !isNaN(firm_calendar_id)) {
			if (element.id.indexOf("print_today")==-1) {
				//only numbers, not pi or wcab
				start_date = arrHash[3];
				end_date = arrHash[4];
			}
		}
		
		if (typeof firm_calendar_id == "undefined" && typeof sort_order == "undefined") {
			//url = "report.php#firmkalendar/_/" + start_date +"/"+ end_date;
			firm_calendar_id = -1;
			sort_order = -1;
		}
		
		var the_type = $("#event_typeFilter").val();
		var url = "report.php#ikalendar/" + firm_calendar_id + "/" + sort_order + "/" + start_date +"/"+ end_date;
		
		if (the_type!="" && the_worker=="") {
			the_type = the_type.replace("case_type_", "");
			url = "report.php#firmkalendar/" + the_type +"/" + start_date +"/"+ end_date;
		}
		if (the_worker!="") {
			the_type = the_type.replace("case_type_", "");
			if (the_type=="") {
				the_type = "_";
			}
			url = "report.php#listkalendar/" + the_type +"/" + the_worker + "/" + start_date +"/"+ end_date;
		}
		
		//court calendar
		if (this.model.toJSON().title=="Court Calendar Events") {
			url = "report.php#courtkalendar";	
		}
		window.open(url);
		setTimeout(function() {
			blnPrinting = false;
		}, 700);
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		var hash = document.location.hash;
		if (hash=="#phonereport") {
			window.Router.prototype.listFirmPhoneCalls(format_start_date_range, format_end_date_range);
			return;
		}
		var arrHash = hash.split("/");
		if (arrHash[0].indexOf("#userkalendar")==0) {
			arrHash[0] = "#listuserkalendar"
			arrHash[2] = format_start_date_range;
			arrHash[3] = format_end_date_range;
		} else {
			arrHash[3] = format_start_date_range;
			arrHash[4] = format_end_date_range;
		}
		document.location.href = arrHash.join("/");
	},
	filterCalendar: function(event) {
		var self = this;
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var thetype = element.value;
		if (thetype=="case_type_wc" || thetype=="case_type_pi") {
			//filter the listing by wc/pi
			if (thetype=="case_type_wc") {
				$(".occurence_case_type_WC").show();
				$(".occurence_case_type_PI").hide();
			} else {
				$(".occurence_case_type_WC").hide();
				$(".occurence_case_type_PI").show();
			}
			$(".occurence_case_type_nocase").hide();
			return;
		}
		
		var theworker = $("#assigneeFilter").val();
		if (typeof theworker == "undefined") {
			theworker = "";
		}
		var start = this.model.get("start");
		var end = this.model.get("end");
		if (thetype=="" && theworker=="") {
			//no filter
			var all_customer_events = new OccurenceCustomerCollection({start: start, end: end});
		} else {
			if (thetype=="") {
				var all_customer_events = new CustomerByAssigneeEvents({worker: theworker, start: start, end: end});
			} else {
				var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, type: thetype, start: start, end: end});
			}
		}

		all_customer_events.fetch({
			success: function (data) {
				$(".calendar_title#page_title").html("loaded, rendering...");
				//then re-assign to calendar
				self.model.set("worker", theworker);
				self.model.set("thetype", thetype);
				
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				setTimeout(function() {
					$(".calendar_title#page_title").html(thetype.toLowerCase().capitalizeWords() + " Calendar");
					
					$("#event_typeFilter").val(thetype);
					$("#assigneeFilter").val(theworker);
				}, 700);
			}
		});
		
	},
	filterAssigneeCalendar: function(event) {
		var self = this;
		
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var theworker = element.value;
		var thetype = $("#event_typeFilter").val();
		var start = this.model.get("start");
		var end = this.model.get("end");
		
		if (thetype=="" && theworker=="") {
			//no filter
			var all_customer_events = new OccurenceCustomerCollection({start: start, end: end});
		} else {
			if (thetype=="") {
				var all_customer_events = new CustomerByAssigneeEvents({worker: theworker, start: start, end: end});
			} else {
				var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, type: thetype, start: start, end: end});
			}
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				
				self.model.set("worker", theworker);
				self.model.set("thetype", thetype);
								
				self.$el.html("");
				self.render();
				
				setTimeout(function() {
					var the_worker = worker_searches.findWhere({nickname:theworker});
					var theuser = theworker;
					if (typeof the_worker != "undefined") {
						the_worker = the_worker.toJSON();
						theuser = the_worker.user_name;
					}
					$(".calendar_title#page_title").html(theuser.toLowerCase().capitalizeWords() + " Assignments Calendar");
					
					$("#assigneeFilter").val(theworker);
					$("#event_typeFilter").val(thetype);
				}, 700);
			}
		});
	},
	massChange: function(event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;
		
		for(var i =0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = $(".check_thisone").attr("id");
				var arrCheckbox =  checkbox_element.split("_");
				var task_id = arrCheckbox[2];
				arrChecked.push(task_id);
			}
		}
		
		if (arrChecked.length==0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			console.log(action);
			if (action == "change_date") {
				composeDateChangeEvent(ids, "event");
			}
		} else { 
			console.log("no action");
		}
		//composeDelete(id, "webmail");
	},
	checkAll: function(event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		var arrElement = element_id.split("_");
		var date_day = arrElement[3];
		var task_id = arrElement[2];
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		//date_day = date_day.replace(" ", ".");
		
		$('.check_thisone_' + date_day).prop('checked', element.checked);
		if($('#mass_change').is(":visible")) {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		} else {
			if (element.checked) { // check select status
				$('#mass_change').show();
			} else {
				$('#mass_change').hide();
			}
		}
		return;
		
		if ($('.check_thisone_' + date_day).prop('checked') == "false") {
			if (element.checked) { // check select status
				$('.check_thisone_' + date_day).prop('checked', true);
			} else {
				$('.check_thisone').prop('checked', false);         
			}
		} else {
			if (element.checked) { // check select status
				
				$('.check_thisone_' + date_day).prop('checked', true);
				$(element).attr('checked', 'checked');
				
			} else {
				$('.check_thisone').prop('checked', false);
				$('.check_thisone').prop('checked', false);         
			}
		}
	},
	scheduleResetDay:function() {
		var self = this;
		clearTimeout(reset_timeout);
		setTimeout(function() {
			self.resetDayStartDate();
		}, 2000);
	},
	resetDayStartDate: function() {
		var start_date = $("#dayview_start_date").val();
		if (typeof start_date == "undefined") {
			start_date = "";
		}
		if (start_date.length==10) {
			if (isDate(start_date)) {
				var arrURL = document.location.href.split("#")[1].split("/");
				current_start_date = arrURL[3];
				start_date = moment(start_date).format("YYYY-MM-DD");
				if (current_start_date != start_date) {
					$("#content").html(loading_image);
					document.location.href = "#" + arrURL[0] + "/" + arrURL[1] + "/" + arrURL[2] + "/" + start_date + "/" + start_date;
				}
			}
		}
	},
	readEvent: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[2];
		if ($("#read_holder_" + id).css("display")!="none") {
			$("#read_holder_" + id).fadeOut(
				function() {
					//$("#task_row_" + id).fadeIn();
					$(".occurence_listing #action_holder_" + id).fadeIn();
					//mark the row as read
					var url = 'api/event/read';
					formValues = "id=" + id;
			
					$.ajax({
						url:url,
						type:'POST',
						dataType:"json",
						data: formValues,
						success:function (data) {
							if(data.error) {  // If there is an error, show the error tasks
								saveFailed(data.error.text);
							} else {
								//console.log(data);
								//refresh the new task indicator
								checkInbox();
							}
						}
					});
				}
			);
		}
	},
	newEvent: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	expandOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #expand_" + id).hide();
		if ($("#read_holder_" + id) != "") {
			var url = 'api/event/read';
			formValues = "id=" + id;
	
			$.ajax({
				url:url,
				type:'POST',
				dataType:"json",
				data: formValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error tasks
						saveFailed(data.error.text);
					} else {
						$(" #read_holder_" + id).hide();
						$(" #shrink_" + id).show();
						$(" .occurence_row_" + id).fadeIn();
					}
				}
			});
		} else {
			$("." + this.model.get("event_class") + " #shrink_" + id).show();
			$("." + this.model.get("event_class") + " .occurence_row_" + id).fadeIn();
		}
		
	},
	shrinkOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #shrink_" + id).hide();
		$("." + this.model.get("event_class") + " #expand_" + id).show();
		$("." + this.model.get("event_class") + " .occurence_row_" + id + "_details").fadeOut();
		
	},
	confirmdeleteEvent: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[1];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 20, left: arrPosition[1] - 350, position:'absolute'});
		$("#confirm_delete").fadeIn();
		
	},
	canceldeleteEvent: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteEvent: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "event");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteEvent(event);
			$(".event_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".event_row_" + id).fadeOut();
			}, 2500);
		}
	},
	freezePreview: function() {
		 freezeEventPreview();
	}
});
window.event_listing_mobile = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		var arrDayCount = [];
		var occurences = this.collection.toJSON();
		 _.each( occurences, function(occurence) {
			if (!isNaN(occurence.supervising_attorney)) {
				var thesupv = worker_searches.findWhere({"id": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					occurence.supervising_attorney = thesupv.get("user_name");
				}
			}
			
			var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			
			if (occurence.case_stored_name != "") {
				occurence.case_name = occurence.case_stored_name;
			}
			
			occurence.time = moment(occurence.event_dateandtime).format("h:mm a");
		 });
		 var mymodel = this.model.toJSON();
		 
		$(this.el).html(this.template({occurences: occurences, case_id: mymodel.case_id, homepage: mymodel.homepage, title: mymodel.title, arrDayCount: arrDayCount}));
		
		setTimeout("tableSortIt()", 100);
		
		return this;
    },
	
	events: {
		"click #delete_occurence": 					"confirmdeleteOccurence",
		"click .delete_yes":						"deleteOccurence",
		"click .expand_occurence": 					"expandOccurence",
		"click .shrink_occurence": 					"shrinkOccurence"
	},
	expandOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #expand_" + id).hide();
		$("." + this.model.get("event_class") + " #shrink_" + id).show();
		$("." + this.model.get("event_class") + " .occurence_row_" + id).fadeIn();
		
	},
	shrinkOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #shrink_" + id).hide();
		$("." + this.model.get("event_class") + " #expand_" + id).show();
		$("." + this.model.get("event_class") + " .occurence_row_" + id).fadeOut();
		
	},
	confirmdeleteOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "event");
	},
	deleteOccurence: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "event");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".occurence_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".occurence_row_" + id).fadeOut();
			}, 2500);
		}
	},
});
window.event_listing_view = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		var arrDayCount = [];
		var occurences = this.model.toJSON();
		 _.each( occurences, function(occurence) {
			if (!isNaN(occurence.supervising_attorney)) {
				var thesupv = worker_searches.findWhere({"id": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					occurence.supervising_attorney = thesupv.get("user_name");
				}
			}
			
			var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
			if (typeof arrDayCount[the_day] == "undefined") {
				arrDayCount[the_day] = 1;
			} else {
				arrDayCount[the_day]++;
			}
			if (occurence.case_stored_name != "") {
				occurence.case_name = occurence.case_stored_name;
			}
		 });
		 if (typeof mymodel.import_date == "undefined") {
			 mymodel.import_date = "";
		 }
		 
		$(this.el).html(this.template({events: occurences, arrDayCount: arrDayCount, import_date: mymodel.import_date}));
		
		setTimeout("tableSortIt()", 100);
		
		return this;
    },
	
	events: {
		"click #delete_occurence": 					"confirmdeleteOccurence",
		"click .delete_yes":						"deleteOccurence",
		"click .expand_occurence": 					"expandOccurence",
		"click .shrink_occurence": 					"shrinkOccurence"
	},
	expandOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #expand_" + id).hide();
		$("." + this.model.get("event_class") + " #shrink_" + id).show();
		$("." + this.model.get("event_class") + " .occurence_row_" + id).fadeIn();
		
	},
	shrinkOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		$("." + this.model.get("event_class") + " #shrink_" + id).hide();
		$("." + this.model.get("event_class") + " #expand_" + id).show();
		$("." + this.model.get("event_class") + " .occurence_row_" + id).fadeOut();
		
	},
	confirmdeleteOccurence: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "event");
	},
	deleteOccurence: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "event");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".occurence_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".occurence_row_" + id).fadeOut();
			}, 2500);
		}
	},
});
window.event_listing_print = Backbone.View.extend({

    initialize:function () {
        //this.model.on("change", this.render, this);
    },
	events: {
		"dblclick #date_range_area":						"dateRangeShow",
		"click #close_date_range":							"dateRangeHide",
		"click #update_date_range":							"updateRange",
		"click .range_dates":								"calendarFix"
	},
	dateRangeShow: function() {
		$("#date_range_area").fadeOut(100, function() {
			$("#date_range_area_input").fadeIn(200);
			rangeDates();
		});
	},
	dateRangeHide: function() {
		$("#date_range_area_input").fadeOut(100, function() {
			$("#date_range_area").fadeIn(200);
		});
	},
	calendarFix: function() {
		setTimeout(function() {
			$('.xdsoft_datetimepicker').css("top", "50px");
		}, 90);
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		
		var arrHash = document.location.hash.split("/");
		arrHash[3] = format_start_date_range;
		arrHash[4] = format_end_date_range;
		document.location.href = arrHash.join("/");
		//document.location.href = "#ikalendar/2/0/" + format_start_date_range + "/" + format_end_date_range + "";
	},
    render:function () {		
		var self = this;
		var case_id;
		var calendar_title = "";
		var worker = "";
		var type = "";
		if (typeof this.model.get("case_id") != "undefined") {
			case_id = this.model.get("case_id");
		}
		if (typeof this.model.get("calendar_title") != "undefined") {
			calendar_title = this.model.get("calendar_title");
		}
		if (typeof this.model.get("worker") != "undefined") {
			worker = this.model.get("worker");
		}
		if (typeof this.model.get("type") != "undefined") {
			type = this.model.get("type");
		}
		
		//this.collection.bind("reset", this.render, this);
		var arrDayCount = [];
		var occurences = this.collection.toJSON();
		var out_collection = new Backbone.Collection;
		_.each( occurences, function(occurence) {
			if (occurence.case_stored_name!="") {
				occurence.case_name = occurence.case_stored_name;
			}
			
			if (moment(occurence.event_dateandtime).format('h:mma')=="12:00am") {
				occurence.time = "8:00am";
			} else {
				occurence.time = moment(occurence.event_dateandtime).format('h:mma');
				if (occurence.event_duration!="") {
					var end_time = addMinutes(new Date(occurence.event_dateandtime), 30);
					occurence.time += " - " +  moment(end_time).format('h:mma');
				}
			}
			//clean up title
			occurence.event_title = occurence.event_title.replace(" - 00/00/0000", "");
			
			if (occurence.event_name != "" && occurence.event_name != "evdesc") {
				occurence.event_title = occurence.event_name;
			}
			if (occurence.event_name == "evdesc") {
				occurence.event_name = occurence.event_title;
			}
				
			if (typeof occurence.location == "undefined") {
				occurence.location = "";
			}
			if (occurence.assignee==null) {
				occurence.assignee = "";
			}
			
			if (typeof occurence.supervising_attorney == "undefined") {
				occurence.supervising_attorney = "";
			}
			if (!isNaN(occurence.supervising_attorney)) {
				var thesupv = worker_searches.findWhere({"id": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					occurence.supervising_attorney = thesupv.get("nickname");
				}
			}
			
			//no off_calendar
			if (occurence.off_calendar=="N") {
				var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
				if (typeof arrDayCount[the_day] == "undefined") {
					arrDayCount[the_day] = 1;
				} else {
					arrDayCount[the_day]++;
				}
				out_collection.add(occurence);
			}
		});
		
		$(this.el).html(this.template({occurences: out_collection.toJSON(), start: this.model.get("start"), end: this.model.get("end"), case_id: case_id, calendar_title: calendar_title, arrDayCount: arrDayCount, worker: worker, type: type}));
		
		setTimeout(function() {
			self.dateRangeShow();
		}, 1100);
		
		return this;
    }

});
window.event_listing_print_new = Backbone.View.extend({

    initialize:function () {
        //this.model.on("change", this.render, this);
    },
	events: {
		"dblclick #date_range_area":						"dateRangeShow",
		"click #close_date_range":							"dateRangeHide",
		"click #update_date_range":							"updateRange",
		"click .range_dates":								"calendarFix"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "event_listing_print_new";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var case_id;
		var calendar_title = "";
		var worker = "";
		var type = "";
		if (typeof this.model.get("case_id") != "undefined") {
			case_id = this.model.get("case_id");
		}
		if (typeof this.model.get("calendar_title") != "undefined") {
			calendar_title = this.model.get("calendar_title");
		}
		if (typeof this.model.get("worker") != "undefined") {
			worker = this.model.get("worker");
		}
		if (typeof this.model.get("type") != "undefined") {
			type = this.model.get("type");
		}
		
		//this.collection.bind("reset", this.render, this);
		var arrDayCount = [];
		var occurences = this.collection.toJSON();
		var out_collection = new Backbone.Collection;
		_.each( occurences, function(occurence) {
			if (occurence.case_stored_name!="") {
				occurence.case_name = occurence.case_stored_name;
			}
			
			if (moment(occurence.event_dateandtime).format('h:mma')=="12:00am") {
				occurence.time = "8:00am";
			} else {
				occurence.time = moment(occurence.event_dateandtime).format('h:mma');
				if (occurence.event_duration!="") {
					var end_time = addMinutes(new Date(occurence.event_dateandtime), 30);
					occurence.time += " - " +  moment(end_time).format('h:mma');
				}
			}
			//clean up title
			occurence.event_title = occurence.event_title.replace(" - 00/00/0000", "");
			
			if (occurence.event_name != "" && occurence.event_name != "evdesc") {
				occurence.event_title = occurence.event_name;
			}
			if (occurence.event_name == "evdesc") {
				occurence.event_name = occurence.event_title;
			}
				
			if (typeof occurence.location == "undefined") {
				occurence.location = "";
			}
			if (occurence.assignee==null) {
				occurence.assignee = "";
			}
			if (occurence.case_number=="" && occurence.file_number!="") {
				occurence.case_number = occurence.file_number;
			}
			if (typeof occurence.supervising_attorney == "undefined") {
				occurence.supervising_attorney = "";
			}
			occurence.supervising_attorney_name = occurence.supervising_attorney;
			
			if (!isNaN(occurence.supervising_attorney)) {
				var thesupv = worker_searches.findWhere({"id": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					occurence.supervising_attorney = thesupv.get("nickname");
					occurence.supervising_attorney_name = thesupv.get("user_name");
				}
			} else {
				var thesupv = worker_searches.findWhere({"nickname": occurence.supervising_attorney});
				if (typeof thesupv != "undefined") {
					//occurence.supervising_attorney = thesupv.get("nickname");
					occurence.supervising_attorney_name = thesupv.get("user_name");
				}
			}
			
			if (occurence.supervising_attorney_name !="") {
				occurence.supervising_attorney_name = occurence.supervising_attorney_name.toLowerCase().capitalize();
			}
			if (typeof occurence.worker == "undefined") {
				occurence.worker = "";
			}
			occurence.worker_name = occurence.worker;
			
			if (!isNaN(occurence.worker)) {
				var thework = worker_searches.findWhere({"id": occurence.worker});
				if (typeof thework != "undefined") {
					occurence.worker = thework.get("nickname");
					occurence.worker_name = thework.get("user_name");
				}
			} else {
				var thework = worker_searches.findWhere({"nickname": occurence.worker});
				if (typeof thework != "undefined") {
					//occurence.worker = thesupv.get("nickname");
					occurence.worker_name = thework.get("user_name");
				}
			}
			if (occurence.worker_name !="") {
				occurence.worker_name = occurence.worker_name.toLowerCase().capitalize();
			}
			//no off_calendar
			if (occurence.off_calendar=="N") {
				var the_day = moment(occurence.event_dateandtime).format("MMDDYY");
				if (typeof arrDayCount[the_day] == "undefined") {
					arrDayCount[the_day] = 1;
				} else {
					arrDayCount[the_day]++;
				}
				out_collection.add(occurence);
			}
		});
		
		$(this.el).html(this.template({occurences: out_collection.toJSON(), start: this.model.get("start"), end: this.model.get("end"), case_id: case_id, calendar_title: calendar_title, arrDayCount: arrDayCount, worker: worker, type: type}));

		
		setTimeout(function() {
			//calendar type
			var arrHash = document.location.hash.split("/");
			
			if (arrHash[0]=="#firmkalendar") {
				var arrLink = $("#link_previous_month").attr("href").split("/");
				var start = arrLink[3];
				var end = arrLink[4];
				
				$("#link_previous_month").attr("href", arrHash[0] + "/" + arrHash[1] + "/" + start + "/" + end);
				
				var arrLink = $("#link_this_month").attr("href").split("/");
				var start = arrLink[3];
				var end = arrLink[4];
				
				$("#link_this_month").attr("href", arrHash[0] + "/" + arrHash[1] + "/" + start + "/" + end);
				
				var arrLink = $("#link_next_month").attr("href").split("/");
				var start = arrLink[3];
				var end = arrLink[4];
				
				$("#link_next_month").attr("href", arrHash[0] + "/" + arrHash[1] + "/" + start + "/" + end);
			}
			self.dateRangeShow();
			$("#occurence_listing td").css("font-size", "1.1em");
		}, 1100);
		
		return this;
    },
	dateRangeShow: function() {
		$("#date_range_area").fadeOut(100, function() {
			$("#date_range_area_input").fadeIn(200);
			rangeDates();
		});
	},
	dateRangeHide: function() {
		$("#date_range_area_input").fadeOut(100, function() {
			$("#date_range_area").fadeIn(200);
		});
	},
	calendarFix: function() {
		setTimeout(function() {
			$('.xdsoft_datetimepicker').css("top", "50px");
		}, 90);
	},
	updateRange: function() {
		var start_date_range = $('#start_dateInput').val();
		var format_start_date_range = moment(start_date_range).format("YYYY-MM-DD");
		var end_date_range = $('#end_dateInput').val();
		var format_end_date_range = moment(end_date_range).format("YYYY-MM-DD");
		
		var arrHash = document.location.hash.split("/");
		arrHash[3] = format_start_date_range;
		arrHash[4] = format_end_date_range;
		document.location.href = arrHash.join("/");
		//document.location.href = "#ikalendar/2/0/" + format_start_date_range + "/" + format_end_date_range + "";
	}

});
function rangeDates(){
	 $('#end_dateInput').datetimepicker({
		  format:'m/d/Y',
		  onShow:function( ct ){
			   this.setOptions({
					minDate:$('#start_dateInput').val()?$('#start_dateInput').val():false
			   })
		  },
		  onChangeDateTime: function() {
			  $("#update_date_range").css("visibility", "visible");
			  
			  checkRangeStartEnd();
		  },
		  closeOnDateSelect: true,
		  timepicker:false
	 });
	 $('#start_dateInput').datetimepicker({
		  format:'m/d/Y',
		  closeOnDateSelect: true,
		  timepicker:false,
		  onChangeDateTime: function() {
			  $("#update_date_range").css("visibility", "visible");
			  
			  checkRangeStartEnd();
		  }
	 });
};
function checkRangeStartEnd() {
	var end = $('#end_dateInput').val();
	var start = $('#start_dateInput').val();
	
	if (end=="" || start=="") {
		return;
	}
	var d1 = new Date(start);
	var d2 = new Date(end);
	
	if (d1.getTime() > d2.getTime()) {
		 $('#end_dateInput').val($('#start_dateInput').val());
	}
}