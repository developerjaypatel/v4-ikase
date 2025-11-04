window.activity_listing_pane = Backbone.View.extend({
	initialize: function () {

	},
	events: {
		"click .activity_category": "filterByCategory",
		"dblclick .activity_by": "filterByEmployee",
		"click .activity_by": "editEmployee",
		"click .activity_apply_only": "applyOnly",
		"click #activities_clear_search": "clearSearch",
		"click #show_all": "clearSearch",
		"click .compose_activity": "sendActivity",
		"click .edit_event": "newEvent",
		"click .edit_task": "newTask",
		"click .check_all": "checkAll",
		"click .check_thisone": "checkSome",
		"click .restore_archives": "restoreArchives",
		"click .read_more": "expandActivity",
		"click .read_less": "shrinkActivity",
		"click .hide_activity": "shrinkActivity",
		"click #close_activity": "shrinkActivityPane",
		"change #mass_change": "massChange",
		"click #label_search_activity": "Vivify",
		"click .activity_hours_input_activator": "editHours",
		"click .set_hours": "setHours",
		"click .save_edit_hours": "saveHours",
		"click .cancel_edit_hours": "cancelHours",
		"click .save_edit_by": "saveBy",
		"click .cancel_edit_by": "cancelBy",
		"click #activity_input_activator": "editActivity",
		"click .open_activity": "editActivityPane",
		"click .delete_icon": "confirmdeleteActivity",
		"click .delete_yes": "deleteActivity",
		"click .delete_no": "canceldeleteActivity",
		"click .compose_new_activity": "newActivity",
		"click .compose_task": "composeTask",
		"click .compose_new_note": "composeNote",
		"click .compose_new_mail": "composeMail",
		"click .compose_event": "composeEvent",
		"click #hide_file_access": "hideFileAccess",
		//"blur .activity_input":						"saveActivity",
		"click #print_activity": "printActivity",
		"click #print_invoice": "printInvoice",
		"click .edit_invoice_full": "editKInvoiceFull",
		"click #invoice_activities": "showInvoiceDates",
		"click #cancel_invoice": "hideInvoiceDates",
		"click #invoice_link": "selectTypeInvoice",
		"click #pre_bill_link": "selectTypePreBill",
		"click #transfer_funds_link": "showTransferFunds",
		"click #bill_activities": "autoBillByCategory",
		"click .kinvoice_type": "changeKInvoiceType",
		"click #invoice_create": "saveActivityKInvoice",
		"click #activities_searchList": "Vivify",
		"focus #activities_searchList": "Vivify",
		"click #invoices": "listInvoices",
		"click #filter_show_all": "filterShowAll",
		"click #filter_billable": "filterBillable",
		"click #filter_invoiced": "filterInvoiced",
		"blur #activities_searchList": "unVivify",
		"click #show_hours_summary": "showHoursSummary",
		"click #close_summary": "hideHoursSummary",
		"click #send_documents_2": "sendActivityDocuments",
	},
	render: function () {
		var self = this;
		//alert("I am loaded");
		if (current_case_id == -1) {
			//catchup
			if (document.location.hash.indexOf("#activity/") == 0) {
				current_case_id = document.location.hash.split("/")[1];
			}
		}

		var activities = this.collection.toJSON();
		var arrUserNickNames = [];
		var blnArchives = false;
		if (typeof self.model.get("mode") == "undefined") {
			self.model.set("mode", "");
		}
		var arrIDs = [];
		var arrTotals = [];
		var arrEmployees = [];
		var blnWCAB = false;
		var blnWCABDefense = false;
		if (current_case_id != -1) {
			var kase = kases.findWhere({ case_id: current_case_id });
			var kase_type = kase.get("case_type");
			blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1) || (kase_type.indexOf("W/C") > -1));
			blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
		}
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnWCABDefense", blnWCABDefense);

		var arrFilters = [];
		var arrFilterCategories = [];
		var arrDates = [];
		var filtered_activities = new Backbone.Collection;
		var start_date = "2100-01-01 01:00:00";
		var end_date = "1980-01-01 01:00:00";
		var blnBillable = false;
		var arrBillingAmounts = [];
		var total_billed = 0;
		var total_hours = 0;
		var arrUserActivity = [];
		var arrUserHours = [];
		var current_activity = "";
		//console.log("aaaaaaaaaaaaaaaaaaaaaaaaaaaa",activities);
		var temparray = activities;

		var test = _.sortBy(temparray, 'activity_date').reverse();

		_.each(temparray, function (activity) {
			activity.activity_actual_date = activity.activity_date;
			//console.log(activity.activity_date);
			//correction
			activity.hours = Number(activity.hours).toFixed(2);
			var blnImportedActivity = (activity.activity_uuid.indexOf("_") > -1);
			if (arrDates.indexOf(activity.activity_date) < 0 || (current_activity != activity.activity_category) || blnImportedActivity) {
				var activity_hours = activity.hours;
				activity.hours_color = "";
				activity.billable = false;
				activity.billed = false;
				activity.hours_title = "Click to Edit";
				if (activity_hours > 0 || activity.billing_amount > 0) {
					activity.hours_color = "background: #7ceeeebd; color:black";
					activity.billable = true;
					if (!blnBillable) {
						blnBillable = true;
					}
					activity.hours_title = "Billable Item." + activity.hours_title;
				}
				//however, it might have been invoiced
				if (activity.kinvoiceitem_id != '') {
					activity.hours_color = "background: chocolate";
					activity.billable = false;
					activity.billed = true;
					//blnBillable = false;
					activity.hours_title = "Item was invoiced on " + moment(activity.kinvoice_date).format("MM/DD/YY") + "; Invoice #" + activity.kinvoice_number;
				}
				var blnStartEnd = activity.billable;
				if (self.model.get("kinvoice_id") != "") {
					blnStartEnd = activity.billed;
				}
				if (blnStartEnd) {
					//start and end
					var activity_time = moment(activity.activity_date)._d.getTime();

					if (activity_time < moment(start_date)._d.getTime()) {
						start_date = activity.activity_date;
					}
					if (activity_time > moment(end_date)._d.getTime()) {
						end_date = activity.activity_date;
					}
				}
				arrDates.push(activity.activity_date);
				//clean up any document name with apostrophe
				var blnDoc = (activity.activity.indexOf("Document [<a href='") == 0);

				var arrFinalSumm = [];
				if (typeof activity.fee_summary != "undefined") {
					if (activity.fee_summary != "" && activity.activity_category == "Fee") {
						var arrFeeSumm = activity.fee_summary.split("|");
						var fee_date = arrFeeSumm[0];
						if (fee_date != "0000-00-00") {
							fee_date = moment(fee_date).format("MM/DD/YYYY");
							arrFinalSumm.push("Date: " + fee_date);
						}
						if (arrFeeSumm[4] != "") {
							arrFinalSumm.push("ID: " + arrFeeSumm[4]);
						}
						arrFinalSumm.push("Billed: $" + formatDollar(arrFeeSumm[1]));
						arrFinalSumm.push("Paid: $" + formatDollar(arrFeeSumm[2]));

						activity.activity += "\r\n\r\n" + arrFinalSumm.join(" // ");
						if (arrFeeSumm[3] != "") {
							activity.activity += "\r\nMemo: " + arrFeeSumm[3];
						}
					}
				}
				if (blnDoc) {
					var endpos = activity.activity.indexOf("' target='_blank'");
					var href = activity.activity.substring(19, endpos);
					//new_href = href.replaceAll("'", "\\'");
					new_href = href.replaceAll("'", "&apos;");
					activity.activity = activity.activity.replace(href, new_href);
				}
				//make the case stand out
				activity.plain_user_id = activity.activity_user_id;
				if (arrIDs.indexOf(activity.activity_id) < 0) {
					arrIDs.push(activity.activity_id);
					if (typeof arrTotals[activity.activity_user_id] == "undefined") {
						arrTotals[activity.activity_user_id] = 0;
					}
					arrTotals[activity.activity_user_id]++;
				}
				if (arrEmployees.indexOf(activity.activity_user_id) < 0) {
					arrEmployees[activity.activity_user_id] = activity.by;
				}

				//archives?
				if (customer_id == 1049) {
					if (current_case_id < 19545) {
						if (!blnArchives) {
							var cpointer = kase.toJSON().cpointer;
							blnArchives = (activity.activity_uuid.indexOf(cpointer) == 0);
						}
					}
				}

				if (typeof self.model.get("invoice_number") == "undefined") {
					if (typeof activity.invoice_number != "undefined") {
						self.model.set("invoice_number", activity.invoice_number);
					}
				}
				if (typeof self.model.get("invoice_number") == "undefined") {
					self.model.set("invoice_number", "");
				}
				var activity_user_id = activity.activity_user_id;
				if (!isNaN(activity.activity_user_id)) {
					if (typeof arrUserNickNames[activity.activity_user_id] == "undefined") {
						var theworker = worker_searches.findWhere({ "user_id": activity.activity_user_id });
						if (typeof theworker != "undefined") {
							var the_nickname = theworker.get("nickname").toUpperCase();
							arrUserNickNames[activity.activity_user_id] = the_nickname;
							activity.activity_user_id = the_nickname;
						}
					} else {
						activity.activity_user_id = arrUserNickNames[activity.activity_user_id];
					}
				}

				//total hours, by worker
				total_hours += Number(activity_hours);

				var user_id = 0;
				if (activity.by != "") {
					var theworker = worker_searches.findWhere({ "nickname": activity.by });
					if (typeof theworker != "undefined") {
						var user_id = Number(theworker.get("user_id"));
						if (typeof arrUserHours[user_id] == "undefined") {
							arrUserHours[user_id] = activity.by;
						}
						if (typeof arrUserActivity[user_id] == "undefined") {
							arrUserActivity[user_id] = 0;
						}
						arrUserActivity[user_id] += Number(activity_hours);
					}
				}
				if (user_id == 0) {
					if (activity.by != "") {
						//console.log("no worker");
						//console.log(activity);
					}
					if (typeof arrUserHours[user_id] == "undefined") {
						arrUserHours[user_id] = "N/A";
						arrUserActivity[user_id] = 0;
					}
					arrUserActivity[user_id] += Number(activity_hours);
				}

				if (activity.activity.indexOf("Document [") > -1) {
					if (activity.activity.indexOf("C:") > -1) {
						activity.activity = activity.activity.replace("C:\\inetpub\\wwwroot\\iKase.website\\uploads\\" + customer_id + "\\" + current_case_id + "\\", "");
					}

					arrActivity = activity.activity.split("'>");
					arrActivity[0] = arrActivity[0].replace("target='_blank", "target='_blank' style='background:#7ceeeebd;color:black");
					//clean up link
					arrActivity[0] = arrActivity[0].replace(customer_id + "/" + current_case_id, "~")
					arrActivity[0] = arrActivity[0].replace(customer_id + "/", customer_id + "/" + current_case_id + "/");
					arrActivity[0] = arrActivity[0].replace("~", customer_id + "/" + current_case_id);

					activity.activity = arrActivity.join("'>");
				}
				if (activity.activity.indexOf("Letter [") > -1) {
					arrActivity = activity.activity.split("'>");
					//clean up in case properly setup...
					arrActivity[0] = arrActivity[0].replace(".docx", "");
					arrActivity[0] = arrActivity[0].replace("' class='white_text' target='_blank", "");
					arrActivity[0] = arrActivity[0].replace("' target='_blank", "");
					activity.activity = arrActivity[0] + ".docx' target='_blank' style='background:yellow;color:black'>" + arrActivity[1];
				}
				if (activity.activity.indexOf("PDF Form [") > -1) {
					activity.activity = activity.activity.replaceAll("<br><br>", "<br>");
					activity.activity = activity.activity.replaceAll("class='white_text'>review", "style='cursor:pointer; background:yellow;color:black' class='white_text' title='Click to review PDF Form'>Review");
				}

				if (activity.activity.indexOf("Task [") > -1) {
					if (activity.activity.indexOf("edit_task_") < 0) {
						activity.activity = activity.activity.replaceAll("id='", "id='edit_task_");
					}
				}

				//edit mode
				activity.check_box = "";
				if (self.model.get("mode") == "invoice_edit") {
					if (activity.invoice_id != "") {
						activity.check_box = " checked";
					}
				}
				//break up
				if (customer_id != 1055 && customer_id != 1049 && customer_id != 1075) {
					if (activity.by == "") {
						//let's get the by and any trailing info
						var strpos = activity.activity.indexOf(" by");

						if (strpos > -1) {
							var activity_header = activity.activity.substring(0, activity.activity.indexOf(" by"));
							var activity_footer = activity.activity.substring(activity.activity.indexOf(" by") + 3);
							activity_footer = activity_footer.replaceAll("<br />", "\r\n\r\n");
							var arrFooter = activity_footer.split("\r\n\r\n");
							activity_footer = arrFooter[0];
							if (arrFooter.length > 1) {
								arrFooter.splice(0, 1);
								activity_header += "<br><br>" + arrFooter.join("<br>");
							}
						} else {
							var activity_header = activity.activity;
							var activity_footer = "";
						}
						activity.activity = activity_header.replaceAll("\r\n", "<br>");
						activity.activity = activity.activity.replaceAll("class='edit_event", "class='white_text edit_event");
						activity.activity = activity.activity.replaceAll("- 00/00/0000", "");
						activity.activity = activity.activity.replaceAll("D:/uploads/uploads/", "D:/uploads/");
						activity.activity = activity.activity.replaceAll("style='cursor:pointer'>", "style='cursor:pointer; background:yellow;color:black'>")

						activity.by = activity_footer;
					}
				}
				activity.activity = activity.activity.replaceTout('background-color: rgb(255, 255, 255);', '');


				//send to client
				activity.send_link = "<a id='activity_" + activity.id + "_" + activity.case_id + "' style='cursor:pointer' class='compose_activity white_text' title='Click to send this Activity item to Client'>Send&nbsp;to&nbsp;Client</a>";

				activity.full_activity = "";
				if (activity.activity.indexOf("From:") < 0 && activity.activity.indexOf("Subject:") < 0) {
					console.log("if : ", activity.activity_category)
					if (activity.activity_category == "Message") {
						var subpos = 200;
						short_activity = activity.activity;
						if (subpos > 0) {
							short_activity = activity.activity.substr(0, subpos) + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
						}
						short_activity = notesCleanUp(short_activity);
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					}else if (activity.activity_category == "Notes") {
						activity.activity = activity.activity.replaceAll("<div style='font-size:0.7em; margin-top:-5px'>","")
						activity.activity = activity.activity.replaceAll("<div>","")
						activity.activity = activity.activity.replaceAll("</div>","")
						var subpos = 50
						// var subpos = activity.activity.indexOf("(sent to");
						short_activity = activity.activity;
						if (subpos > 0) {
							short_activity =activity.activity.substr(0, subpos) + "<br><a id='readmore_" + activity.id + "' style='display:block;max-width:83px;cursor:pointer;font-size: 1em !important;background:white;color:black;padding:2px;' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
						}
						short_activity = notesCleanUp(short_activity);
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					}  else {


						//length
						var pure_snippet = activity.activity;
						pure_snippet = pure_snippet.replaceAll("<br>", "~~");
						pure_snippet = removeHtml(pure_snippet);
						pure_snippet = pure_snippet.replaceAll("~~", ", ");
						pure_snippet = pure_snippet.replaceAll(", ; ", "");
						if (pure_snippet.indexOf(", ") == 0) {
							pure_snippet = pure_snippet.substr(2);
						}
						if (pure_snippet.length > 499) {
							//pure_snippet = pure_snippet.substring(0, 75);
							pure_snippet = pure_snippet.getComplete(500) + "&nbsp;&#8230;";
						}

						if (activity.activity.length > 499) {
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; text-align:center; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; text-align:center; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
							pure_snippet = notesCleanUp(pure_snippet);
							activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + pure_snippet + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div>";

						} else {
							activity.activity = activity.activity.replaceAll("\r\n", "<br>");
						}
					}

				} else {
					console.log("else : ", activity.activity_category)
					if (activity.activity_category == "Message") {
						var subpos = 200;
						short_activity = activity.activity;
						if (subpos > 0) {
							short_activity = activity.activity.substr(0, subpos) + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
						}
						short_activity = notesCleanUp(short_activity);
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					}else if (activity.activity_category == "Email") {
						var subpos = activity.activity.indexOf("Subject:");
						short_activity = activity.activity;
						if (subpos > 0) {
							short_activity = activity.activity.substr(0, subpos) + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
						}
						short_activity = notesCleanUp(short_activity);
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					} else if (activity.activity_category != "Letters") {
						//find the subject
						//find the end of the line
						//apply ...
						var subpos = activity.activity.indexOf("Subject:");
						var retpos = activity.activity.indexOf("<br />", subpos);
						short_activity = activity.activity;
						if (retpos > 0) {
							short_activity = activity.activity.substr(0, retpos) + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:none'><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "<div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div></div>";
						}
						short_activity = notesCleanUp(short_activity);
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					} else {
						//small clean up
						activity.activity = activity.activity.replace("<br> - ", "");
					}
					activity.activity = activity.activity.replaceAll("\r\n", "<br>");
				}
				activity.activity = activity.activity.replace(" 12:00AM</a>]", "</a>");
				if (activity.activity.indexOf(" scheduled for") > -1) {
					activity.activity = activity.activity.replace(" scheduled for", "<br>Scheduled For:");
					activity.activity = activity.activity.replace(" was updated  by", "<br>Updated By:");
					activity.activity = activity.activity.replace("[", "");
					activity.activity = activity.activity.replace("]", "");
				}

				var billing_amount = Number(activity.hours) * Number(activity.rate);
				if (isNaN(billing_amount)) {
					billing_amount = 0;
				}
				total_billed += billing_amount;
				var billing_value = Number(billing_amount);
				var billing_amount_string = String(billing_amount);
				if (billing_amount_string.indexOf('.') === -1) {
					billing_value = billing_value.toFixed(2);
					billing_amount = billing_value.toString();
				} else {
					var res = billing_amount_string.split(".");
					if (res[1].length < 3) {
						billing_value = billing_value.toFixed(2);
						billing_amount = billing_value.toString();
					}
				}
				activity.billing_amount = billing_amount;
				activity.billing_amount = formatDollar(activity.billing_amount);

				activity.rate = formatDollar(activity.rate);
				arrBillingAmounts.push(billing_amount);

				//clean up
				activity.activity = activity.activity.replaceAll("&#;160", "");
				//the actual date
				activity.activity_date = moment(activity.activity_date).format("MM/DD/YY h:mmA");
				activity.activity_date = activity.activity_date.replace("3:00AM", "");
				activity.activity_date = activity.activity_date.replace("2:00AM", "");

				if (activity.activity_category == "Case" && activity.activity.indexOf("Status: Intake") > -1) {
					activity.activity_category = "Phone Intake";
				}
				//phone intake
				if (activity.activity_category == "Intake Accepted") {
					activity.activity = activity.activity.replace("accepted", "<span style='background:lime; padding:2px; color:black'>accepted</span>");
				}
				if (activity.activity_category == "Intake Rejected") {
					activity.activity = activity.activity.replace("rejected", "<span style='background:red; padding:2px; color:white'>rejected</span>");
				}

				//no matter what
				activity.activity = notesCleanUp(activity.activity);

				filtered_activities.add(activity);

				if (activity.activity_category.trim() != "") {
					var activity_filter = '<span class="activity_category" style="color:white; cursor:pointer" title="Click to Filter by this category">' + activity.activity_category + '</span>';
					if (arrFilters.indexOf(activity_filter) < 0) {
						arrFilters.push(activity_filter);
						arrFilterCategories.push(activity.activity_category);
					}
				}
			}
			current_activity = activity.activity_category;
		});

		this.model.set("filters", arrFilterCategories);
		this.model.set("start_date", moment(start_date).format("MM/DD/YYYY"));
		this.model.set("end_date", moment(end_date).format("MM/DD/YYYY"));
		this.model.set("blnBillable", blnBillable);
		if (total_billed > 0) {
			this.model.set("total_billed", " // $" + formatDollar(total_billed));
		} else {
			this.model.set("total_billed", "");
		}
		activities = filtered_activities.toJSON();
		/*
		_.each( activities, function(activity) {
			//clean up any document name with apostrophe
			var blnDoc = (activity.activity.indexOf("Document [<a href='") == 0);
			
			if (blnDoc) {
				var endpos = activity.activity.indexOf("' target='_blank'");
				var href = activity.activity.substring(19, endpos);
				//new_href = href.replaceAll("'", "\\'");
				new_href = href.replaceAll("'", "&apos;");
				activity.activity = activity.activity.replace(href, new_href);
			}
			//make the case stand out
				activity.plain_user_id = activity.activity_user_id;
			if (arrIDs.indexOf(activity.activity_id) < 0) {
				arrIDs.push(activity.activity_id);
				if (typeof arrTotals[activity.activity_user_id] == "undefined") {
					arrTotals[activity.activity_user_id] = 0;
				}
				arrTotals[activity.activity_user_id]++;
			}
			if (arrEmployees.indexOf(activity.activity_user_id) < 0) {
				arrEmployees[activity.activity_user_id] = activity.by;
			}
			
			//archives?
			if (customer_id == 1049) {
			 if (current_case_id < 19545) {
				if (!blnArchives) {			
					var cpointer = kase.toJSON().cpointer;
					blnArchives = (activity.activity_uuid.indexOf(cpointer) == 0);
				}
			 }
			}
			
			 if (typeof self.model.get("invoice_number") == "undefined") {
				 if (typeof activity.invoice_number != "undefined") {
					self.model.set("invoice_number", activity.invoice_number);
				 }
			 }
			 if (typeof self.model.get("invoice_number") == "undefined") {
				 self.model.set("invoice_number", "");
			 }
			 if (!isNaN(activity.activity_user_id)) {
				if (typeof arrUserNickNames[activity.activity_user_id] == "undefined") {
					var theworker = worker_searches.findWhere({"user_id": activity.activity_user_id});
					if (typeof theworker != "undefined") { 
						var the_nickname = theworker.get("nickname").toUpperCase();
						arrUserNickNames[activity.activity_user_id] = the_nickname;
						activity.activity_user_id = the_nickname;
					}
				} else {
					activity.activity_user_id = arrUserNickNames[activity.activity_user_id];
				}
			 }
			var activity_hours = activity.hours;
			
			if (activity.activity.indexOf("Document [") > -1) {
				 if (activity.activity.indexOf("C:") > -1) {
					activity.activity = activity.activity.replace("C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" + customer_id + "\\" + current_case_id + "\\", "");
				 }
				 
				 arrActivity = activity.activity.split("'>");
				 arrActivity[0] =  arrActivity[0].replace("target='_blank", "target='_blank' style='background:yellow;color:black");
				 //clean up link
				 arrActivity[0] = arrActivity[0].replace(customer_id + "/" + current_case_id, "~")
				 arrActivity[0] = arrActivity[0].replace(customer_id+ "/", customer_id + "/" + current_case_id + "/");
				 arrActivity[0] = arrActivity[0].replace("~", customer_id + "/" + current_case_id);
				 
				 activity.activity = arrActivity.join("'>");
			}
			 if (activity.activity.indexOf("Letter [") > -1) {
				 arrActivity = activity.activity.split("'>");
				 //clean up in case properly setup...
				 arrActivity[0] =  arrActivity[0].replace(".docx", "");
				 arrActivity[0] =  arrActivity[0].replace("' target='_blank", "");
				 activity.activity = arrActivity[0] + ".docx' target='_blank' style='background:yellow;color:black'>" + arrActivity[1];
			 }
			 if (activity.activity.indexOf("PDF Form [") > -1) {
				 activity.activity = activity.activity.replaceAll("<br><br>", "<br>");
				 activity.activity = activity.activity.replaceAll("class='white_text'>review", "style='cursor:pointer; background:yellow;color:black' class='white_text' title='Click to review PDF Form'>Review");
			 }
			 
			 if (activity.activity.indexOf("Task [") > -1) {
				 if (activity.activity.indexOf("edit_task_") < 0) {
					activity.activity = activity.activity.replaceAll("id='", "id='edit_task_");
				 }
			 }
			 
			 //edit mode
			 activity.check_box = "";
			 if (self.model.get("mode") == "invoice_edit") {
				 if (activity.invoice_id != "") {
					 activity.check_box = " checked";
				 }
			 }
			//break up
			if (customer_id!=1055 && customer_id!=1049 && customer_id!=1075) {
				if (activity.by=="") {
					//let's get the by and any trailing info
					var strpos = activity.activity.indexOf(" by");
					
					if (strpos > -1) {
						var activity_header = activity.activity.substring(0, activity.activity.indexOf(" by"));
						var activity_footer = activity.activity.substring(activity.activity.indexOf(" by") + 3);
						activity_footer = activity_footer.replaceAll("<br />", "\r\n\r\n");
						var arrFooter = activity_footer.split("\r\n\r\n");
						activity_footer = arrFooter[0];
						if (arrFooter.length > 1) {
							arrFooter.splice(0, 1);
							activity_header += "<br><br>" + arrFooter.join("<br>"); 
						}
					} else {
						var activity_header = activity.activity;
						var activity_footer = "";
					}
					activity.activity = activity_header.replaceAll("\r\n", "<br>");
					activity.activity = activity.activity.replaceAll("class='edit_event", "class='white_text edit_event");
					activity.activity = activity.activity.replaceAll("- 00/00/0000", "");
					activity.activity = activity.activity.replaceAll("D:/uploads/uploads/", "D:/uploads/");
					activity.activity = activity.activity.replaceAll("style='cursor:pointer'>", "style='cursor:pointer; background:yellow;color:black'>")
					
					activity.by = activity_footer;
				}
			}
			//send to client
			activity.send_link = "<a id='activity_" + activity.id + "_" + activity.case_id + "' style='cursor:pointer' class='compose_activity white_text' title='Click to send this Activity item to Client'>Send&nbsp;to&nbsp;Client</a>";
			
			if (activity.activity.indexOf("From:") < 0 && activity.activity.indexOf("Subject:") < 0) {
				if (activity.activity.length > 499) {
					activity.activity = "<div class='partialactivity' id='partialactivity_" +  activity.id + "'>" + activity.activity.getComplete(500) + "&nbsp;<a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div><div id='fullactivity_" +  activity.id + "' style='display:none'><div style='width:35px; cursor:pointer; background:white; color:black; padding:2px' id='hideactivity_" +  activity.id + "' class='read_less white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "</div>";
				}
			} else {
				if (activity.activity_category!="Letters") {
					//find the subject
					//find the end of the line
					//apply ...
					var subpos = activity.activity.indexOf("Subject:");
					var retpos = activity.activity.indexOf("<br />", subpos);
					
					activity.activity = "<div class='partialactivity' id='partialactivity_" +  activity.id + "'>" + activity.activity.substr(0, retpos) + "&nbsp;<a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div><div id='fullactivity_" +  activity.id + "' style='display:none'><div style='width:35px; cursor:pointer; background:white; color:black' id='hideactivity_" +  activity.id + "' class='read_less white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "</div>";
				} else {
					//small clean up
					activity.activity = activity.activity.replace("<br> - ", "");
				}
			}
			
			//clean up
			activity.activity = activity.activity.replaceAll("&#;160", "");
			//the actual date
			activity.activity_date = moment(activity.activity_date).format("MM/DD/YY h:mmA");
			activity.activity_date = activity.activity_date.replace("3:00AM", "");
			activity.activity_date = activity.activity_date.replace("2:00AM", "");
			
			if (activity.billing_rate==null) {
				activity.billing_rate = "";
			}
		 });
		*/

		this.model.set("blnArchives", blnArchives);
		if (typeof this.model.get("report") == "undefined") {
			this.model.set("report", false);
		}
		if (typeof this.model.get("type_change") == "undefined") {
			this.model.set("type_change", false);
		}
		if (typeof this.model.get("start_date") == "undefined") {
			this.model.set("start_date", "");
		}
		if (typeof this.model.get("end_date") == "undefined") {
			this.model.set("end_date", "");
		}
		if (this.model.get("start_date") == "00/00/0000") {
			this.model.set("start_date", moment().format("MM/DD/YYYY"));
		}
		if (this.model.get("end_date") == "00/00/0000") {
			this.model.set("end_date", moment().format("MM/DD/YYYY"));
		}
		this.model.set("user_name", "");
		if (typeof this.model.get("user_id") == "undefined") {
			this.model.set("user_id", "");
		} else {
			if (this.model.get("user_id") != "all") {
				var theworker = worker_searches.findWhere({ "user_id": this.model.get("user_id") });
				if (typeof theworker != "undefined") {
					var the_nickname = theworker.get("nickname").toUpperCase();
					var the_username = theworker.get("user_name").toUpperCase();
					this.model.set("nickname", the_nickname);
					this.model.set("user_name", the_username);
				}
			}
		}
		if (this.model.get("user_id") == "all") {
			this.model.set("nickname", "All Employees");
			this.model.set("user_name", "");
		}
		if (typeof this.model.get("invoice_id") == "undefined") {
			this.model.set("invoice_id", "");
		}

		if (typeof this.model.get("invoice_date") == "undefined") {
			this.model.set("invoice_date", "");
		}
		if (typeof this.model.get("billing_amount") == "undefined") {
			this.model.set("billing_amount", "");
		}

		var activity_summary = [];
		arrTotals.forEach(function (currentValue, index, array) {
			var employee_name = arrEmployees[index];
			activity_summary.push(employee_name + ": " + currentValue);
		});
		var totals = "";
		if (activity_summary.length > 1) {
			totals = "<div style='display:inline-block; color:white'>" + activity_summary.join("&nbsp;|&nbsp;") + "</div>";
		}
		_.each(activities, function (activity) {
			activity.user_total_count = arrTotals[activity.plain_user_id];
		});

		if (typeof this.model.get("list_title") == "undefined") {
			if (this.model.get("invoice_id") == "") {
				this.model.set("list_title", "Activity Log");
			} else {
				this.model.set("list_title", "Invoice " + this.model.get("invoice_number"));
			}
			if (this.model.get("billing")) {
				this.model.set("list_title", "Billed Items");
			}
		}
		if (this.model.get("list_title") == "") {
			this.model.set("list_title", "Activity Items");
		}
		if (typeof this.model.get("kinvoice_id") == "undefined") {
			this.model.set("kinvoice_id", "");
			this.model.set("kinvoice_number", "");
			this.model.set("kinvoice", "");
			this.model.set("invoiced_corporation_id", "");
		}

		var filters = "";
		if (arrFilters.length > 0) {
			filters = arrFilters.join(" | ");
			filters = "Filters:&nbsp;" + filters;
		}
		//clean up hours summary
		var arrUserDivs = [];
		_.each(arrUserActivity, function (hours, user_id) {
			if (hours > 0) {
				var nickname = arrUserHours[user_id];
				var div = "<div style='padding:5px'>" + nickname + ": " + String(hours.toFixed(2)) + " hrs</div>";
				arrUserDivs.push(div);
			}
		});
		var user_divs = arrUserDivs.join("");
		//kase.set("invoice_date", data.invoice_date);
		try {
			$(this.el).html(this.template({
				activities: activities,
				activity_count: arrIDs.length,
				totals: totals,
				user_divs: user_divs,
				total_hours: total_hours,
				case_id: current_case_id,
				user_id: this.model.get("user_id"),
				user_name: this.model.get("user_name"),
				nickname: this.model.get("nickname"),
				report: this.model.get("report"),
				start_date: this.model.get("start_date"),
				end_date: this.model.get("end_date"),
				invoice_id: this.model.get("kinvoice_id"),
				billing_amount: this.model.get("billing_amount"),
				invoice_number: this.model.get("kinvoice_number"),
				kinvoice_total: this.model.get("kinvoice_total"),
				total_billed: this.model.get("total_billed"),
				invoiced_corporation_id: this.model.get("invoiced_corporation_id"),
				blnWCAB: blnWCAB,
				blnBillable: blnBillable,
				invoice_date: this.model.get("invoice_date"),
				list_title: this.model.get("list_title"),
				filters: filters
			}
			)
			);

		}
		catch (err) {
			var view = "activity_listing_pane";
			var extension = "php";

			loadTemplate(view, extension, self);

			return "";
		}

		tableSortIt("activity_listing");

		$("#activity_listing").addClass("glass_header_no_padding");
		$("#activity_listing th").css("font-size", "1.2em");

		setTimeout(function () {
			$('.range_dates').datetimepicker(
				{
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $(".activity #start_dateInput").val();
						var end_date = $(".activity #end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$(".activity #end_dateInput").val(end_date);
						}
						var user_id = $(".activity #user_id").val();
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						//document.location.href = "#activities/" + user_id + "/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
						self.checkBoxesRange(start_date, end_date, "billable_data_row");
					}
				}
			);

			//account id for invoicing
			var account = new Account({ "case_id": current_case_id, "account_type": "trust" });
			account.fetch({
				success: function (data) {
					//store it
					if (data.id != -1) {
						$("#account_id").val(data.id);
					}
				}
			});
			//carrier for invoicing

			if (current_case_id == -1) {
				//catchup
				if (document.location.hash.indexOf("#activity/") == 0) {
					current_case_id = document.location.hash.split("/")[1];
				}
			}

			var parties = new Parties([], { "case_id": current_case_id });
			parties.fetch({
				success: function (parties) {
					var carriers = parties.where({ "type": "carrier" });
					var arrCarriers = [];
					var arrExaminers = [];
					var selected = "";
					var invoiced_corporation_id = self.model.get("invoiced_corporation_id");

					if (typeof carriers == "undefined") {
						thecarrier = "<option value='' selected>No Carrier</option>";
						arrCarriers.push(thecarrier);
					} else {
						if (carriers.length == 1) {
							selected = " selected";
						} else {
							thecarrier = "<option value='' selected>Select from List</option>";
							arrCarriers.push(thecarrier);
						}
						_.each(carriers, function (carrier) {
							var thecarrier = carrier.get("company_name");
							/*
							var theexaminer = carrier.get("full_name");
							var thefax = carrier.get("fax");
							var theemployee_fax = carrier.get("employee_fax");
							if (blnFaxForm && thefax!="") {
								thecarrier += " - fax: " + thefax;
								theexaminer += " // " + theemployee_fax;
							}
							*/
							selected = "";
							if (carrier.get("corporation_id") == invoiced_corporation_id) {
								selected = " selected";
							}
							thecarrier = "<option value='" + carrier.get("corporation_id") + "'" + selected + ">" + thecarrier + "</option>";
							//theexaminer = "<option value='" + theexaminer + "'" + selected + ">" + theexaminer + "</option>";
							arrCarriers.push(thecarrier);
							//arrExaminers.push(theexaminer);
						});
					}

					$("#invoice_carrier").html(arrCarriers.join(""));
				}
			});

			var activities = self.collection.toJSON();
			var activities_length = activities.length;
			if (!self.model.get("blnArchives") && customer_id == 1049) {
				//if (customer_id == 1049 && current_case_id < 19545) {
				var url = "../api/activity/archivecount/" + current_case_id;
				$.ajax({
					url: url,
					type: 'GET',
					dataType: "json",
					success: function (data) {
						if (data.count > 0) {
							//offer the user the option of restoring
							var the_count = numberWithCommas(data.count) + " records (about&nbsp;" + (Math.round(data.count / 60) / 10).toFixed(2) + " minutes @ 10 recs/sec)";
							$("#restore_archives_count").html("<span style='font-size:0.8em'>" + the_count + "</span>");
							$(".restore_archives").fadeIn(function () {
								$("#restore_archives_count").show();
							});
						} else {
							//negative number
							//just fetch
							//get the activities, there are there waiting
							var activities = new ActivitiesCollection([], { case_id: current_case_id });
							activities.fetch({
								success: function (data) {
									//we might have moved on though
									if ($("#activity_listing").length > 0) {
										$('#kase_content').html(new activity_listing_pane({ collection: activities, model: self.model }).render().el);
										$("#kase_content").removeClass("glass_header_no_padding");
										hideEditRow();
									}
								}
							});
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						// report error
						console.log(errorThrown);
					}
				});
			}

			$("#activity_listing th").css("font-size", "1.3em");
		}, 700);

		var case_id = this.model.get("case_id");
		var case_status = "";
		var case_substatus = "";
		var attorney = "";
		var worker = "";
		var rating = "";
		if (typeof case_id != "undefined") {
			var kase = kases.findWhere({ case_id: case_id });
			//console.log(kase.toJSON());
			case_status = kase.toJSON().case_status;
			case_substatus = kase.toJSON().case_substatus;
			attorney = kase.toJSON().attorney;
			worker = kase.toJSON().worker;
			rating = kase.toJSON().rating;

			setTimeout(function () {
				$("#case_number_fill_in").html(kase.toJSON().case_number);
				$("#adj_number_fill_in").html(kase.toJSON().adj_number);
				if (kase.toJSON().adj_number == "") {
					$("#adj_slot").hide();
				}
				$("#case_status_fill_in").html(kase.toJSON().case_status);
				$("#case_substatus_fill_in").html(kase.toJSON().case_substatus);
				$("#attorney_fill_in").html(kase.toJSON().attorney);
				$("#rating_fill_in").html(kase.toJSON().rating);
				$("#worker_fill_in").html(kase.toJSON().worker);
				$("#case_date_fill_in").html(kase.toJSON().case_date);
				$("#claims_fill_in").html(kase.toJSON().claims);
				if (kase.toJSON().claims == "") {
					//$("#claims_slot").hide();
				}
				$("#case_type_fill_in").html(kase.toJSON().case_type);
				$("#case_type").val(kase.toJSON().case_type);
				$("#language_fill_in").html(kase.toJSON().language);
				if (kase.toJSON().language == "") {
					$("#language_slot").hide();
				}
			}, 10);
		}
		//var kase = kases.findWhere({case_id: this.model.get("case_id")});
		this.model.set("case_status", case_status);
		this.model.set("case_substatus", case_substatus);
		this.model.set("attorney", attorney);
		this.model.set("worker", worker);
		this.model.set("rating", rating);
		if (typeof this.model.get("case_id") != "undefined") {
			var parties = new Parties([], { case_id: this.model.get("case_id"), case_uuid: this.model.get("uuid"), panel_title: "" });
			parties.fetch({
				success: function (parties) {
					var claim_number = "";
					var carrier_insurance_type_option = "";
					//now we have to get the adhocs for the carrier
					var carrier_partie = parties.findWhere({ "type": "carrier" });
					if (typeof carrier_partie == "undefined") {
						carrier_partie = new Corporation({ case_id: self.model.get("case_id"), type: "carrier" });
						carrier_partie.set("corporation_id", -1);
						carrier_partie.set("partie_type", "Carrier");
						carrier_partie.set("color", "_card_missing");
					}
					carrier_partie.adhocs = new AdhocCollection([], { case_id: case_id, corporation_id: carrier_partie.attributes.corporation_id });
					carrier_partie.adhocs.fetch({
						success: function (adhocs) {
							var adhoc_claim_number = adhocs.findWhere({ "adhoc": "claim_number" });

							if (typeof adhoc_claim_number != "undefined") {
								claim_number = adhoc_claim_number.get("adhoc_value");
							}

							var adhoc_carrier_insurance_type_option = adhocs.findWhere({ "adhoc": "insurance_type_option" });

							if (typeof adhoc_carrier_insurance_type_option != "undefined") {
								carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
							}
							var arrClaimNumber = [];
							var arrCarrierInsuranceTypeOption = [];
							if (carrier_partie.attributes.claim_number != "" && carrier_partie.attributes.claim_number != null) {
								//arrClaimNumber.push(partie.claim_number);
								var claim_number = carrier_partie.attributes.claim_number;
								$("#claim_number_fill_in").html(claim_number);
								kase.set("claim_number", claim_number);
							}
						}
					});
				}
			});

			//let's get the archive
			if (user_data_path == "A1" || user_data_path == "perfect") {
				var kase_archives = new LegacyArchiveCollection([], { case_id: current_case_id });
			} else {
				var kase_archives = new ArchiveCollection([], { case_id: current_case_id });
			}
			kase_archives.fetch({
				success: function (data) {
					if (data.length > 0) {
						if (data.toJSON()[0].document_name != "" || data.toJSON()[0].path != "") {
							$("#archive_count").html("(" + data.length + ")");
						}
					}
				}
			});
		}

		setTimeout(function () {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);

			var blnInvoicing = (self.model.get("kinvoice_id") != "");

			if (blnInvoicing) {
				//invoiced
				//$("#invoice_carrier").val(self.model.get("invoiced_corporation_id"));
				$("#buttons_filters_holder").css("visibility", "hidden");
				$(".compose_button").hide();
				$("#activity_dates_holder").show();
				$("#activity_dates_holder").css("visibility", "visible");
				$("#activity_dates_holder").css("margin-left", "-120px");
				$("#invoice_create").html("Update Invoice");
				$("#cancel_invoice").hide();
				$("#legend_holder").hide();
				$("#buttons_filters_holder").css("margin-top", "-25px");
				var start_date = $(".activity #start_dateInput").val();
				var end_date = $(".activity #end_dateInput").val();
				$("#additional_invoice_questions").show();


				var kinvoice = self.model.get("kinvoice");
				if (kinvoice.kinvoice_type == "P") {
					document.getElementById("kinvoice_type_pre").checked = true;
					$("#transfer_trust_funds").hide();
				} else {
					$("#transfer_trust_funds").show();
				}
				setTimeout(function () {
					self.checkBoxesRange(start_date, end_date, "billed_data_row");
				}, 500);

				if (self.model.get("type_change")) {
					$("#invoice_link").trigger("click");
				}
				//$(".billable_data_row").css("opacity", 0.5);
			}

			if (self.model.get("billing") && !blnInvoicing) {
				$("#filter_billable").trigger("click");
			}
		}, 750);
		
		setTimeout(function(){ 
			$.ajax({
				url: "../api/alldocuments/"+current_case_id,
				type: "get",
				success: function (response) {
					var responseobj = JSON.parse(response);
					console.log("RESPONSE ===> "+responseobj.length);
					if(typeof responseobj !== "undefined" && responseobj.length > 0) {					
						$('.check_thisone').each(function () {
							var href_data = $(this).closest('tr').find('td:eq(7)').find('a').attr('href');

							if(typeof href_data !== "undefined") {
								href_data = href_data.toString();
								var src = href_data.split('/');
								var doc_name = src[src.length - 1];
								for(var cnt = 0 ; cnt < responseobj.length; cnt ++) {
									//console.log(responseobj[cnt].document_filename +" == " +doc_name);
									if(responseobj[cnt].document_filename == doc_name) {
										//console.log(responseobj[cnt].document_filename +" == " +doc_name);
										$(this).attr("data-id", responseobj[cnt].document_id);
										//break;
									}
								}
							}
						});
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
				   //console.log(textStatus, errorThrown);
				}
			});
		}, 100);

		return this;
	},
	hideFileAccess: function (event) {
		event.preventDefault();
		var $rows = $('.activity_data_row');
		var val = "file accessed";
		$rows.show().filter(function () {
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();

			return ~text.indexOf(val);
		}).hide();

		$("#hide_file_holder").fadeOut();

		var href = $("#print_activity").attr("href");
		var new_href = href.replace("#activity", "#activity_case");
		$("#print_activity").attr("href", new_href);
	},
	applyOnly: function (event) {
		var element = event.currentTarget;

		var element_id = element.id;
		var arrClasses = element_id.split("_");

		var activity_uuid = arrClasses[arrClasses.length - 1];

		//all activities box must be turned off
		if (element.checked) {
			document.getElementById("activity_apply_all_" + activity_uuid).checked = false;
		}
		document.getElementById("activity_apply_all_" + activity_uuid).disabled = (element.checked);
	},
	editEmployee: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_id = element.id;
		var arrClasses = element_id.split("_");

		//alert(element_class);
		var activity_uuid = arrClasses[arrClasses.length - 1];
		$("#activity_by_uuid_" + activity_uuid).css("display", "none");
		//$("#activity_by_edit_" + arrClasses[2]).css("display", "");
		$("#activity_by_input_holder_" + activity_uuid).css("display", "");
		var original_value = $(".activity_by_input_activator#activity_by_uuid_" + activity_uuid).html();
		//original_value = original_value.replace("<br>", "\r\n");
		//$("#activity_by_edit_" + activity_uuid).val(original_value);
		//$("#activity_by_edit_" + activity_uuid).focus();

		//get the id, name
		var worker = worker_searches.findWhere({ nickname: original_value });
		var theme = {
			tokenLimit: 1,
			onAdd: function (item) {
				var arrId = this.context.id.split("_");
				var activity_uuid = arrId[arrId.length - 1];
				$("#activity_by_apply_" + activity_uuid).show();
			}
		};
		$("#activity_by_edit_" + activity_uuid).tokenInput("api/user", theme);
		$(".token-input-list").css("width", "150px");
		$("#activity_by_edit_" + activity_uuid).tokenInput("add", { id: worker.id, name: worker.get("nickname") });
	},
	setHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_id = element.id;
		var arrClasses = element_id.split("_");

		var activity_uuid = arrClasses[arrClasses.length - 1];
		var minutes = arrClasses[arrClasses.length - 2];

		var hours = Number(minutes) / 60;
		hours = hours.toFixed(3);

		$("#activity_hours_edit_" + activity_uuid).val(hours);
	},
	editHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_id = element.id;
		var arrClasses = element_id.split("_");

		//alert(element_class);
		var activity_uuid = arrClasses[3];
		$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).hide();
		//$("#activity_hours_edit_" + arrClasses[2]).css("display", "");
		$("#activity_hours_input_holder_" + activity_uuid).css("display", "");
		var original_value = $(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).html();
		//original_value = original_value.replace("<br>", "\r\n");
		$("#activity_hours_edit_" + activity_uuid).val(original_value);
		$("#activity_hours_edit_" + activity_uuid).focus();

	},
	refreshAll: function () {
		var self = this;
		if (this.model.get("blnReadyToRefresh")) {
			window.Router.prototype.kaseActivity(current_case_id);
		} else {
			setTimeout(function () {
				self.refreshAll();
			}, 1000);
		}
	},
	saveBy: function (event) {
		resetCurrentContent();

		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrClasses = element_id.split("_");
		var activity_uuid = arrClasses[arrClasses.length - 1];
		var self = this;
		var url = "../api/activity/update_by";
		var this_case_id = current_case_id;

		var byVal = $("#activity_by_edit_" + activity_uuid).val();
		var original_value = $("#activity_by_uuid_" + activity_uuid).html();
		var worker = worker_searches.findWhere({ nickname: original_value });
		var original_id = worker.id;

		if (byVal == "" || byVal == original_id) {
			//$("#activity_by_edit_" + arrClasses[3]).css("display", "none");
			$("#activity_by_input_holder_" + activity_uuid).css("display", "none");
			$("#activity_by_uuid_" + activity_uuid).show();
			return;
		}
		var new_worker = worker_searches.findWhere({ user_id: byVal });
		var new_nickname = new_worker.get("nickname");

		//apply
		var blnRefreshAll = false;
		self.model.set("blnReadyToRefresh", false);
		if (document.getElementById("activity_apply_all_" + activity_uuid).checked) {
			//cycle through all the activities, assign the new by
			blnRefreshAll = true;
			//cycle through all the activities, assign the new by to only matching nickname
			var spans = $(".activity_by");
			var arrLength = spans.length;
			for (var i = 0; i < arrLength; i++) {
				var span = spans[i];
				//don't do the clicked id
				var span_activity_uuid = span.id.split("_")[3];
				if (activity_uuid == span_activity_uuid) {
					continue;
				}

				var span_html = span.innerHTML;
				//if (span_html==original_value) {
				if ($("#activity_by_input_holder_" + span_activity_uuid).css("display") != "") {
					//$("#" + span.id).trigger("click");
					$("#activity_by_uuid_" + span_activity_uuid).css("display", "none");
					$("#activity_by_input_holder_" + span_activity_uuid).css("display", "");
					var original_value = $(".activity_by_input_activator#activity_by_uuid_" + span_activity_uuid).html();

					//get the id, name
					var worker = worker_searches.findWhere({ nickname: original_value });
					var theme = {
						tokenLimit: 1,
						onAdd: function (item) {

						}
					};
					$("#activity_by_edit_" + span_activity_uuid).tokenInput("api/user", theme);
					$(".token-input-list").css("width", "150px");
				}
				$("#activity_by_edit_" + span_activity_uuid).tokenInput("add", { id: new_worker.id, name: new_nickname });
				$("#save_edit_by_" + span_activity_uuid).trigger("click");
				//}
			}
			self.model.set("blnReadyToRefresh", true);
		}
		if (document.getElementById("activity_apply_only_" + activity_uuid).checked) {
			blnRefreshAll = true;
			//cycle through all the activities, assign the new by to only matching nickname
			var spans = $(".activity_by");
			var arrLength = spans.length;
			for (var i = 0; i < arrLength; i++) {
				var span = spans[i];
				//don't do the clicked id
				var span_activity_uuid = span.id.split("_")[3];
				if (activity_uuid == span_activity_uuid) {
					continue;
				}
				var span_html = span.innerHTML;
				if (span_html == original_value) {
					if ($("#activity_by_input_holder_" + span_activity_uuid).css("display") != "") {
						$("#" + span.id).trigger("click");
					}

					$("#activity_by_edit_" + span_activity_uuid).tokenInput("remove", { id: original_id });
					$("#activity_by_edit_" + span_activity_uuid).tokenInput("add", { id: new_worker.id, name: new_nickname });
					$("#save_edit_by_" + span_activity_uuid).trigger("click");
				}
			}
			self.model.set("blnReadyToRefresh", true);
		}
		var formValues = "activity_uuid=" + activity_uuid + "&activity_user_id=" + byVal;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#activity_by_edit_" + arrClasses[3]).css("display", "none");
					$("#activity_by_input_holder_" + activity_uuid).css("display", "none");
					$("#activity_by_uuid_" + activity_uuid).html(new_worker.get("nickname"));
					$("#activity_by_uuid_" + activity_uuid).css("color", "#32CD32");
					$("#activity_by_uuid_" + activity_uuid).show();

					if (blnRefreshAll) {
						setTimeout(function () {
							self.refreshAll();
						}, 2500);
					} else {
						setTimeout(function () {
							if (Number(byVal) > 0) {
								$("#activity_by_uuid_" + activity_uuid).css("color", "black");
								$("#activity_by_uuid_" + activity_uuid).css("background", "#7ceeeebd");
							} else {
								$("#activity_by_uuid_" + activity_uuid).css("color", "white");
							}
						}, 2500);
					}
					//we can show the invoice button now
					$("#invoice_activities").fadeIn();

					var activities = self.collection.toJSON();
					var start_date = "2100-01-01 01:00:00";
					var end_date = "1980-01-01 01:00:00";
					_.each(activities, function (activity) {
						var row_class = "billable_data_row";
						if (self.model.get("kinvoice_id") != "") {
							row_class = "billed_data_row";
						}
						if ($(".activity_data_row_" + activity.activity_uuid).hasClass(row_class)) {
							//start and end
							var activity_time = moment(activity.activity_date)._d.getTime();

							if (activity_time < moment(start_date)._d.getTime()) {
								start_date = activity.activity_date;
							}
							if (activity_time > moment(end_date)._d.getTime()) {
								end_date = activity.activity_date;
							}
						}

					});

					self.model.set("start_date", moment(start_date).format("MM/DD/YYYY"));
					self.model.set("end_date", moment(end_date).format("MM/DD/YYYY"));
					self.model.set("blnBillable", true);

					$("#start_dateInput").val(moment(start_date).format("MM/DD/YYYY"));
					$("#end_dateInput").val(moment(end_date).format("MM/DD/YYYY"));
				}
			}
		});
	},
	saveBulkHours: function () {
		resetCurrentContent();

		var self = this;

		//get all the inputs, see if they are visible
		var inputs = $(".activity_hours_input");
		var arrLength = inputs.length;
		var arrBulk = [];
		for (var i = 0; i < arrLength; i++) {
			var inp = inputs[i];
			if (inp.value != "") {
				let activity_uuid = inp.id.replace("activity_hours_edit_", "");
				let hours = inp.value;
				arrBulk.push({ "activity_uuid": activity_uuid, "hours": hours });
			}
		}
		//console.log(arrBulk);

		if (arrBulk.length > 0) {
			//pass all of this
			var json = JSON.stringify(arrBulk);

			var url = "../api/activity/update_bulkhours";
			var formValues = "activities=" + encodeURIComponent(json) + "&case_id=" + current_case_id;

			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						//all done
						$("#bill_activities").removeClass("btn-primary");
						$("#bill_activities").addClass("btn-success");
						$("#bill_activities").html("Auto Bill Complete &#10003;");

						setTimeout(function () {
							window.Router.prototype.kaseActivity(current_case_id);
						}, 2500);
					}
				}
			});
		} else {
			$("#bill_activities").html("Auto Bill Complete &#10003;");
		}
	},
	saveHours: function (event) {
		resetCurrentContent();

		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrClasses = element_id.split("_");
		var activity_uuid = arrClasses[arrClasses.length - 1];
		var self = this;
		var url = "../api/activity/update_hours";
		var this_case_id = current_case_id;

		var hoursVal = $("#activity_hours_edit_" + activity_uuid).val();
		var original_value = $(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).html();

		if (hoursVal == "" || hoursVal == original_value) {
			//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
			$("#activity_hours_input_holder_" + activity_uuid).css("display", "none");
			$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).show();
			return;
		}

		var formValues = "activity_uuid=" + activity_uuid + "&hours=" + hoursVal;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
					$("#activity_hours_input_holder_" + activity_uuid).css("display", "none");
					$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).html(hoursVal);
					var rate = $("#activity_rate_" + activity_uuid).val();
					var billing_amount = Number(hoursVal) * Number(rate);
					var html = '<div title="' + billing_amount + ' units @ $' + rate + ' per Hour" style="background: #7ceeeebd; color:black">$' + formatDollar(billing_amount) + '</div>';
					$("#activity_billing_amount_holder_" + activity_uuid).html(html);
					$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).css("color", "#32CD32");
					$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).show();

					if (Number(hoursVal) > 0) {
						$(".activity_hours_uuid_" + activity_uuid).css("background", "yellow");
						$(".activity_hours_uuid_" + activity_uuid).css("color", "black");
						$(".activity_data_row_" + activity_uuid).addClass("billable_data_row");
					} else {
						$(".activity_data_row_" + activity_uuid).removeClass("billable_data_row");
					}
					setTimeout(function () {
						if (Number(hoursVal) > 0) {
							$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).css("color", "black");
							$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).css("background", "#7ceeeebd");
						} else {
							$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).css("color", "white");
						}
					}, 2500);

					//we can show the invoice button now
					$("#invoice_activities").fadeIn();

					var activities = self.collection.toJSON();
					var start_date = "2100-01-01 01:00:00";
					var end_date = "1980-01-01 01:00:00";
					_.each(activities, function (activity) {
						var row_class = "billable_data_row";
						if (self.model.get("kinvoice_id") != "") {
							row_class = "billed_data_row";
						}
						if ($(".activity_data_row_" + activity.activity_uuid).hasClass(row_class)) {
							//start and end
							var activity_time = moment(activity.activity_date)._d.getTime();

							if (activity_time < moment(start_date)._d.getTime()) {
								start_date = activity.activity_date;
							}
							if (activity_time > moment(end_date)._d.getTime()) {
								end_date = activity.activity_date;
							}
						}

					});

					self.model.set("start_date", moment(start_date).format("MM/DD/YYYY"));
					self.model.set("end_date", moment(end_date).format("MM/DD/YYYY"));
					self.model.set("blnBillable", true);

					$("#start_dateInput").val(moment(start_date).format("MM/DD/YYYY"));
					$("#end_dateInput").val(moment(end_date).format("MM/DD/YYYY"));
				}
			}
		});
	},
	filterBillable: function (event) {
		event.preventDefault();
		$(".filter_div").hide();
		$("#filter_show_all").show();

		$(".activity_data_row").hide();
		$(".billable_data_row").show();
	},
	filterInvoiced: function (event) {
		event.preventDefault();
		$(".filter_div").hide();
		$("#filter_show_all").show();
		$(".inv_column").show();

		$(".activity_data_row").hide();
		$(".billed_data_row").show();
	},
	filterShowAll: function (event) {
		event.preventDefault();

		$("#filter_show_all").hide();
		$(".filter_div").show();
		$(".inv_column").hide();

		$(".activity_data_row").show();
	},
	checkBoxesRange: function (start_date, end_date, row_class) {
		//only check the boxes within the range
		var blnInvoicing = (this.model.get("kinvoice_id") != "");
		var billables = $("." + row_class);
		var arrLength = billables.length;
		start_date += " 01:00:00";
		end_date += " 23:59:59";
		//disable all check boxes
		$(".check_thisone").attr("disabled", true);
		//$("INPUT[type='checkbox' class='check_thisone']").toggleClass("hidden");
		if (blnInvoicing) {
			var check_id = $(".check_thisone")[0].id;
			if ($("#" + check_id).hasClass("hidden")) {
				$(".check_thisone").toggleClass("hidden");
			}
		}

		for (var i = 0; i < arrLength; i++) {
			var tr = billables[i];
			var uuid = tr.classList[2].split("_")[3];
			var activity_date = $("#actual_date_" + uuid).val();

			var activity_time = moment(activity_date)._d.getTime();
			var blnChecked = false;
			if (activity_time >= moment(start_date)._d.getTime() && activity_time <= moment(end_date)._d.getTime()) {
				blnChecked = true;
			}

			var checkbox = document.getElementById("check_printone_" + uuid);
			checkbox.checked = blnChecked;
			//enabled if checked
			checkbox.disabled = !checkbox.checked;

			if (!blnInvoicing) {
				if (!blnChecked) {
					//if we're updating invoice, don't opacify				
					$(".activity_data_row_" + uuid).css("opacity", 0.5);
				} else {
					$(".activity_data_row_" + uuid).css("opacity", 1);
				}
			}
		}

		//if we're updating invoice
		if (blnInvoicing) {
			if (row_class == "billed_data_row") {
				var billables = $(".billable_data_row");
				var arrLength = billables.length;
				for (var i = 0; i < arrLength; i++) {
					var tr = billables[i];
					var uuid = tr.classList[2].split("_")[3];
					var activity_date = $("#actual_date_" + uuid).val();

					var activity_time = moment(activity_date)._d.getTime();
					var blnBillable = false;
					var opacity = 0.5;
					if (activity_time >= moment(start_date)._d.getTime() && activity_time <= moment(end_date)._d.getTime()) {
						opacity = 1;
						blnBillable = true;
					}
					var checkbox = document.getElementById("check_printone_" + uuid);
					checkbox.checked = false;
					if (blnBillable) {
						checkbox.disabled = false;	//enabled for adding to current invoice
					} else {
						checkbox.disabled = true;
					}
					$("#activity_data_row_" + uuid).css("opacity", opacity);
				}
			}
			if (row_class == "billable_data_row") {
				row_class = "billed_data_row";
				var billeds = $("." + row_class);
				var arrLength = billeds.length;
				for (var i = 0; i < arrLength; i++) {
					var tr = billeds[i];
					var uuid = tr.classList[2].split("_")[3];
					var activity_date = $("#actual_date_" + uuid).val();

					var activity_time = moment(activity_date)._d.getTime();
					var blnChecked = false;
					if (activity_time >= moment(start_date)._d.getTime() && activity_time <= moment(end_date)._d.getTime()) {
						blnChecked = true;
					}
					var checkbox = document.getElementById("check_printone_" + uuid);
					checkbox.checked = blnChecked;
					checkbox.disabled = !blnChecked;
				}
			}
		}
	},
	listInvoices: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_class.split("_");

		//get the kase
		var kase = kases.findWhere({ case_id: arrClasses[1] });
		if (typeof kase == "undefined") {
			//get it
			var kase = new Kase({ id: arrClasses[1] });
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid != "") {
						kases.remove(kase.id); kases.add(kase);
						self.kaseActivity(arrClasses[1]);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({ model: kase }).render().el);

		var activity_invoices = new ActivityInvoiceCollection([], { case_id: arrClasses[1] });
		activity_invoices.fetch({
			success: function (data) {
				document.location.href = "#invoices/" + arrClasses[1];
				$(document).attr('title', "Activities for Case ID: " + arrClasses[1] + " :: iKase");
				kase.set("holder", "#kase_content");
				$('#kase_content').html(new invoice_listing_view({ collection: activity_invoices, model: kase }).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
	},
	cancelHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");
		var activity_uuid = arrClasses[arrClasses.length - 1];

		$("#activity_hours_input_holder_" + activity_uuid).css("display", "none");
		$(".activity_hours_input_activator#activity_hours_uuid_" + activity_uuid).show();

		return;
	},
	cancelBy: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");
		var activity_uuid = arrClasses[arrClasses.length - 1];

		$("#activity_by_input_holder_" + activity_uuid).css("display", "none");
		$("#activity_by_uuid_" + activity_uuid).show();

		return;
	},
	editActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_id = element.id;

		var element_class = element.className;
		var arrClasses = element_class.split("_");
		//alert(arrClasses[2]);
		//return;
		var original_hours_value = $(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[2]).html();
		var original_value = $("#activity_input_activator.activity_uuid_" + arrClasses[2]).html();

		composeActivity(arrClasses[2], original_hours_value, original_value);
	},
	editActivityPane: function (event) {
		event.preventDefault();

		$("#close_activity").trigger("click");
		$("#preview_pane").html("");

		$(".activity_data_row_" + this.model.get("current_activity_uuid")).css("background", "");
		this.model.set("current_activity_uuid", -1);

		hidePreview();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}
		var self = this;

		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var case_id = elementArray[2];
		var activity_uuid = elementArray[3];

		this.model.set("current_activity_uuid", activity_uuid);

		this.model.set("current_background", $(".activity_data_row_" + activity_uuid).css("background"));
		$(".activity_data_row_" + activity_uuid).css("background", "#F90");

		composeNewActivityPane(element.id, true, self.model.get("filters"));
	},
	confirmdeleteActivity: function (event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);
		$("#confirm_delete").css({ display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position: 'absolute' });
		$("#confirm_delete").fadeIn();
	},
	canceldeleteActivity: function (event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteActivity: function (event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "activity");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteActivity(event);
			$(".activity_data_row_" + id).css("background", "red");
			setTimeout(function () {
				//hide the processed row, no longer a batch scan
				$(".activity_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	composeEvent: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	composeNote: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		$("#myModal4").modal("toggle");
		composeNewNote(element.id);
	},
	composeMail: function (event) { 
		var element = event.currentTarget;
		event.preventDefault();  
		composeNewMail(element.id);
	},
	composeTask: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		$("#myModal4").modal("toggle");
		composeTask();
	},
	shrinkActivityPane: function (event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function () {
			$("#preview_pane").html("");
			$("#activity_list_outer_div").css("width", "100%");
			$(".partialactivity").css("max-width", "");
		});
		$(".activity_data_row_" + this.model.get("current_activity_uuid")).css("background", "");
		this.model.set("current_activity_uuid", -1);
	},
	newActivity: function (event) {
		var self = this;

		$(".activity_data_row_" + this.model.get("current_activity_uuid")).css("background", "");
		this.model.set("current_activity_uuid", -1);

		hidePreview();
		if (this.model.get("message_link_clicked")) {
			this.model.set("message_link_clicked", false);
			return;
		}

		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];

		this.model.set("current_activity_uuid", id);

		this.model.set("current_background", $(".activity_data_row_" + id).css("background"));
		$("#activity_data_row_" + id).css("background", "#F90");
		/*
		if (typeof cookie_left_width == "undefined") {
			$("#activity_list_outer_div").css("width", "70%");
			$("#preview_pane_holder").css("width", "30%");
		} else {
			if (cookie_left_width > 70) {
				cookie_left_width = 70;
				cookie_right_width = 30;
				
				writeCookie(cookie_left_width, 60, 24*60*60*1000);
				writeCookie(cookie_right_width, 40, 24*60*60*1000);
			}
			$("#activity_list_outer_div").css("width", cookie_left_width + "%");
			$("#preview_pane_holder").css("width", cookie_right_width + "%");
		}
		*/
		var cookie_left_width = window.innerWidth - 500;
		$("#activity_list_outer_div").css("width", cookie_left_width + "px");
		$("#preview_pane_holder").css("width", "450px");
		$(".partialactivity").css("max-width", "650px");
		$("#preview_pane_holder").fadeIn(function () {
			$("#preview_pane").html(loading_image);

			$("#invoice_activities").hide();
			$("#legend_holder").hide();

			//load the activity into the pane
			composeNewActivityPane(element.id, false, self.model.get("filters"));
		});
	},
	editKInvoiceFull: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrClasses = element_id.split("_");

		var kinvoice_id = arrClasses[1];
		/*
		//get the case from the link
		var invoice_number = $("#" + element_id).html();
		var arrInv = invoice_number.split("-");
		var case_id = arrInv[0];
		*/
		//get a kinvoice object, pass the info to the composeLetter function
		var kinvoice = new KInvoice({ id: kinvoice_id });
		kinvoice.fetch({
			success: function (data) {
				var jdata = data.toJSON();
				//then compose letter, with kinvoice id in there somewhere
				var element_id = "edit_letter_" + jdata.case_id + "_" + jdata.parent_id + "_" + kinvoice_id;
				composeLetter(element_id, jdata);
			}
		});
	},
	showInvoiceDates: function (event) {
		var self = this;

		event.preventDefault();
		$("#legend_holder").hide();
		$(".compose_button").hide();

		$("#invoice_activities").hide();
		$("#activity_dates_holder").show();
		$("#additional_invoice_questions").show();

		//do we need to show the trust transfer?
		//transfer_trust_funds
		var case_id = current_case_id;
		var account_type = "trust";

		var url = "api/accountsno/" + case_id + "/" + account_type;
		$.ajax({
			url: url,
			type: 'GET',
			dataType: "json",
			success: function (data) {
				if (!data.detached) {
					$("#transfer_trust_funds").fadeIn();
				} else {
					$("#transfer_trust_funds").hide();
				}
			}
		});

		//only show rows that are billable
		$(".activity_data_row").hide();
		$(".billable_data_row").show();

		var billables = $(".billable_data_row");
		var arrLength = billables.length;
		for (var i = 0; i < arrLength; i++) {
			var tr = billables[i];
			var uuid = tr.classList[2].split("_")[3];

			var checkbox = document.getElementById("check_printone_" + uuid);
			checkbox.checked = true;
			checkbox.disabled = false;
		}
		document.getElementById("check_print").checked = true;
		$("INPUT[type='checkbox']").toggleClass("hidden");

		$("#transfer_funds").removeClass("hidden")
	},
	hideInvoiceDates: function (event) {
		event.preventDefault();
		$("#additional_invoice_questions").hide();
		$("#activity_dates_holder").fadeOut(function () {
			$("#legend_holder").show();
			$(".compose_button").show();
			$("#invoice_activities").show();

			document.getElementById("check_print").checked = false;
			$("INPUT[type='checkbox']").toggleClass("hidden");

			$("#transfer_funds").removeClass("hidden")
		});

		//show rows 
		$(".activity_data_row").show();
		$(".check_thisone").attr("checked", false);
		$(".check_thisone").attr("disabled", false);
	},
	printInvoice: function (event) {
		event.preventDefault();
		var kinvoice = this.model.get("kinvoice");
		//console.log(kinvoice);
		var url = kinvoice.document_filename + ".docx";
		window.open(url);
		//report.php#invoices/<%=case_id %>/<%=invoice_id %>
	},
	printActivity: function (event) {
		event.preventDefault();
		if ($('#mass_change').is(":visible")) {
			alert("Print not ready");
		} else {
			var url = "report.php#activity/" + current_case_id;
			window.open(url);
		}
	},
	showTransferFunds: function () {
		document.getElementById("transfer_funds").checked = true;
	},
	selectTypeInvoice: function () {
		document.getElementById("kinvoice_type_invoice").checked = true;
		this.changeKInvoiceType();
	},
	selectTypePreBill: function () {
		document.getElementById("kinvoice_type_pre").checked = true;
		this.changeKInvoiceType();
	},
	autoBillByCategory: function (event) {
		var element = event.currentTarget;
		event.preventDefault();

		var self = this;

		if (current_case_id == -1) {
			//catchup
			if (document.location.hash.indexOf("#activity/") == 0) {
				current_case_id = document.location.hash.split("/")[1];
			}
		}

		var kase = kases.findWhere({ case_id: current_case_id });

		//var rate = new KaseRate({case_type: kase.get("case_type")});
		//for now
		var rate = new KaseRate({ case_type: "all" });

		rate.fetch({
			success: function (rate_data) {
				if (typeof rate_data.get("rate_info") != "undefined") {
					var data = rate_data.get("rate_info");
					if (data == "") {
						var default_hours = 0.17;
						if (span_html == "Forms" || span_html == "Letters") {
							default_hours = 0.33;
						}
						if (span_html == "Documents") {
							default_hours = 0.25;
						}
						var spans = $(".activity_category_span");
						var arrLength = spans.length;
						for (var i = 0; i < arrLength; i++) {
							var span_html = spans[i].innerHTML;

							var span_id = spans[i].id;
							var activity_id = span_id.replace("activity_category_", "");
							var span = $("#activity_hours_uuid_" + activity_id);
							var hours = span.html();
							if (Number(hours) == 0) {
								span.trigger("click");
								$("#activity_hours_edit_" + activity_id).val(default_hours);
								//$("#save_edit_hours_" + activity_id).trigger("click");
							}

						}
						self.saveBulkHours();
					} else {
						var rows = document.getElementsByClassName("activity_data_row");
						self.applySchedule(kase, rate_data, rows);
					}
				}
			}
		});
	},
	applySchedule: function (kase, rate_data, rows) {
		var self = this;
		$("#autobill_loading").show();
		$("#activity_listing").hide();
		var data = rate_data.get("rate_info");
		if (data != "") {
			var jdata = JSON.parse(data);
			var arrLength = jdata.length;
			for (var i = 0; i < arrLength; i++) {
				var fee = jdata[i];
				if (fee.deleted != "Y") {
					var fee_name = fee.fee_name;
					var minutes = fee.fee_minutes;

					var hours = Number(minutes) / 60;
					hours = hours.toFixed(2);

					self.applyRateFee(rows, fee_name, hours);
				}
			}
		}
		self.saveBulkHours();
	},
	applyRateFee: function (rows, fee_name, hours) {
		var self = this;
		//get rows
		var arrLength = rows.length;
		for (var i = 0; i < arrLength; i++) {
			//get uuid
			//check fee_name and category
			//found it, apply hours, trigger save click

			var row = rows[i];
			if (row.classList.length >= 3) {
				var classList = row.classList.value.split(" ");
				var activity_id = classList[1].replace("activity_data_row_", "");
				var activity_uuid = classList[2].replace("activity_data_row_", "");
				var activity_category = $("#activity_category_" + activity_uuid).text();

				if (activity_category == fee_name) {
					$("#activity_hours_uuid_" + activity_uuid).trigger("click");
					$("#activity_hours_edit_" + activity_uuid).val(hours);
					//self.clickSave(activity_uuid);
				}
			}
		}
	},
	clickSave: function (activity_uuid) {
		var rand = Math.floor((Math.random() * 500) + 1);
		setTimeout(function () {
			$("#save_edit_hours_" + activity_uuid).trigger("click");
		}, rand);
	},
	saveActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");

		var self = this;
		var url = "../api/activity/update_activity";
		var this_case_id = current_case_id;

		var activityVal = $("#activity_edit_" + arrClasses[2]).val();
		var original_value = $("#activity_input_activator.activity_uuid_" + arrClasses[2]).html();


		if (activityVal == "" || activityVal == original_value) {
			//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
			$("#activity_input_holder_" + arrClasses[2]).css("display", "none");
			$("#activity_input_activator.activity_uuid_" + arrClasses[2]).show();
			return;
		}


		var formValues = "activity_uuid=" + arrClasses[2] + "&activity=" + activityVal;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
					$("#activity_input_holder_" + arrClasses[2]).css("display", "none");
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).html(activityVal);
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).css("color", "#32CD32");
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).show();
					setTimeout(function () {
						$("#activity_input_activator.activity_uuid_" + arrClasses[2]).css("color", "white");
					}, 2000);
				}
			}
		});
	},
	expandActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#partialactivity_" + theid).fadeOut(function () {
			$("#fullactivity_" + theid).fadeIn();
		});
	},
	shrinkActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#fullactivity_" + theid).fadeOut(function () {
			$("#partialactivity_" + theid).fadeIn();
		});
	},
	showHoursSummary: function (event) {
		event.preventDefault();
		$("#show_hours_summary").fadeOut(
			function () {
				$("#close_summary").show();
				$("#user_hours_holder").fadeIn();
			}
		);
	},
	sendActivityDocuments: function (event) {
		event.preventDefault();
		
		var url = "../api/activity/kases/documents/" + current_case_id;
		var this_case_id = current_case_id;
		$.ajax({
			url: url,
			type: 'GET',
			dataType: "json",
			success: function (data) {
				var check_thisones = $(".check_thisone234");
				var arrLength = check_thisones.length;
				var arrCheckeds = [];
				for (var i =0; i < arrLength; i++) {
					var check_thisone = check_thisones[i];
					if (check_thisone.checked) {
						var arrID = $(check_thisone).attr('data-id');
						var div = document.createElement("div");
						div.innerHTML = arrID;
						var href = div.querySelector("a").href;
						var fileName = href.substring(href.lastIndexOf('/') + 1);
						if(data.hasOwnProperty(fileName)){
							arrCheckeds.push(data[fileName]);
						}
					}
				}
				//console.log(arrCheckeds);
				composeMessage(arrCheckeds);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				// report error
				console.log(errorThrown);
			}
		});		
	},
	hideHoursSummary: function (event) {
		event.preventDefault();
		$("#user_hours_holder").fadeOut(
			function () {
				$("#close_summary").hide();
				$("#show_hours_summary").fadeIn();
			}
		);
	},
	unVivify: function (event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_activity");

		if (textbox.val() == "") {
			label.animate({ color: "#999", fontSize: "1em", top: "0px" }, 300);

		} else {
			return;
		}
	},
	Vivify: function (event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_activity");



		if (textbox.val() == "") {
			label.animate({ top: "-9px", fontSize: "0.58em", color: "#CCC" }, 250);
			//$('#notes_searchList').focus();
		}
	},
	restoreArchives: function (event) {
		var self = this;
		var url = "../api/activity/archive/" + current_case_id;
		var this_case_id = current_case_id;
		$("#kase_content").html(loading_image + "<div class='white_text' style='text-align:center'><br>Please be patient, it may take a couple minutes to retrieve archives, because there can be up to 5000 entries per case.<br><br>Restoring activities from the Archive only needs to be done once.<br><br><span style='background:orange;color:black'>Because this action takes place on the server, you don't have to stay on this screen.  When you return in a few minutes, the archives will be there.</span></div>");
		$.ajax({
			url: url,
			type: 'GET',
			dataType: "json",
			success: function (data) {
				//refresh the page if we are still here
				if (document.location.hash.indexOf("#activity") == 0) {
					//get the activities, there are there waiting
					var activities = new ActivitiesCollection([], { case_id: current_case_id });
					activities.fetch({
						success: function (data) {
							$('#kase_content').html(new activity_listing_view({ collection: activities, model: self.model }).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
							hideEditRow();
						}
					});
				} else {
					alert("Your 'Restore from Archive' request has completed for Case ID " + this_case_id);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				// report error
				console.log(errorThrown);
			}
		});
	},
	newEvent: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	checkSome: function (event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		//var arrElement = element_id.split("_");

		var blnShowInvoice = false;
		var checks = $(".check_thisone");
		var arrLength = checks.length;
		for (var i = 0; i < arrLength; i++) {
			var check = checks[i];
			if (check.checked) {
				blnShowInvoice = true;
				break;
			}
		}
		if (blnShowInvoice) {
			$("#invoice_activity").css("visibility", "visible");
			$("#print_activity").html("Print Selected Activities");
		} else {
			$("#invoice_activity").css("visibility", "hidden");
			$("#print_activity").html("Print Kase Activities");

		}

		//var activity_uuid = arrElement[2];
		//$('.check_thisone').prop('checked', "checked");
		/*
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
		*/
		return;

	},
	checkAll: function (event) {
		var element = event.currentTarget;
		var blnChecked = element.checked;
		var checks = $('.check_thisone');
		var arrLength = checks.length;

		for (var i = 0; i < arrLength; i++) {
			var check = checks[i];
			if (!check.disabled) {
				//$('.check_thisone').prop('checked', blnChecked);
				check.checked = blnChecked;
			}
		}
		/*
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
		*/
		return;

	},
	massChange: function (event) {
		var dropdown = document.getElementById("mass_change");
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			if (action == "print") {
				$("#print_activity").html("Print Selected Activities");
				$("#invoice_activity").css("visibility", "hidden");
			}
			if (action == "bill") {
				$("#invoice_activity").css("visibility", "visible");
				$("#print_activity").css("visibility", "hidden");
			}
		} else {
			$("#invoice_activity").css("visibility", "hidden");
			$("#print_activity").html("Print Activities");
		}
	},
	changeKInvoiceType: function () {
		var kinvoice_type = "I";
		if (document.getElementById("kinvoice_type_pre").checked) {
			kinvoice_type = "P";
		}
		var display = "block";
		if (kinvoice_type == "P" || $("#account_id").val() == "") {
			display = "none";
			document.getElementById("transfer_funds").checked = false;
		}


		$("#transfer_trust_funds").css("display", display);
	},
	saveActivityKInvoice: function (event) {
		event.preventDefault();

		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;

		var invoice_carrier = $("#invoice_carrier").val();
		if (invoice_carrier == "") {
			alert("Please select a Carrier to invoice.");
			return;
		}

		for (var i = 0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox = checkbox_element.split("_");
				var activity_uuid = arrCheckbox[2];
				//alert(arrCheckbox);
				arrChecked.push(activity_uuid);
			}
		}

		if (arrChecked.length == 0) {
			alert("Please select at least 1 activity item.");
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		var kinvoice_id = $(".activity #kinvoice_id").val();
		var kinvoice_number = $(".activity #kinvoice_number").val();
		var kinvoice_type = "I";
		if (document.getElementById("kinvoice_type_pre").checked) {
			kinvoice_type = "P";
		}
		var transfer_funds = "N";
		if (document.getElementById("transfer_funds").checked) {
			transfer_funds = "Y";
		}
		var start_date = $(".activity #start_dateInput").val();
		var end_date = $(".activity #end_dateInput").val();

		//alert(arrChecked);
		var action = "bill";
		var operation = "insert";
		if (kinvoice_id != "") {
			operation = "update";
		}

		var url = "../api/activity/" + operation + "_kinvoiceactivity";
		var formValues = "case_id=" + current_case_id + "&kinvoice_id=" + kinvoice_id + "&kinvoice_number=" + kinvoice_number;
		formValues += "&kinvoice_type=" + kinvoice_type + "&transfer_funds=" + transfer_funds;
		formValues += "&carrier_id=" + invoice_carrier + "&ids=" + ids;
		formValues += "&start_date=" + encodeURIComponent(start_date) + "&end_date=" + encodeURIComponent(end_date);

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#activity_dates_holder").html("<span style='background:lime; padding: 2px; color:black'>Invoice Saved &#10003;</span>");
					var account_id = data.account_id;

					refreshOutstandingInvoices();

					if (account_id != "") {
						var invoice_total = data.invoice_total;
						var invoice_number = data.invoice_number;

						//add a disbursement against the account
						var d = new Date();
						var check_number = "TRNSFR:" + moment(d).format("YYMMDDHHmmss");
						var check_date = moment(d).format("YYYY-MM-DD");
						var transaction_date = moment().format("YYYY-MM-DD");
						var account_type = "trust";

						var url = "api/check/add";
						var formValues = "table_name=check&table_id=-1&case_id=" + current_case_id + "&account_id=" + account_id;
						formValues += "&fee_id=&recipient=&kinvoice_id=&invoice_number=&ledger=OUT";
						formValues += "&transaction_date=" + transaction_date + "&payment=" + invoice_total;
						formValues += "&check_type=" + account_type.capitalize() + "+Withdrawal&method=transfer";
						formValues += "&amount_due=0&check_number=" + check_number;
						formValues += "&balance=0&check_date=" + check_date;
						formValues += "&memo=Invoice Transfer for " + invoice_number + "&send_document_id=";

						$.ajax({
							url: url,
							type: 'POST',
							dataType: "json",
							data: formValues,
							success: function (data) {
								if (data.error) {  // If there is an error, show the error messages
									saveFailed(data.error.text);
								} else {

								}
							}
						});
					}

					setTimeout(function () {
						window.Router.prototype.kaseChecks(current_case_id);
						window.history.replaceState(null, null, "#payments/" + current_case_id);
						app.navigate("payments/" + current_case_id, { trigger: false });
					}, 2500);
				}
			}
		});
	},
	newTask: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	sendActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	filterByEmployee: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var by = element.innerHTML;
		$("#activities_searchList").val(by);

		//$( "#activities_searchList" ).trigger( "keyup" );
		var obj = document.getElementById("activities_searchList");
		findIt(obj, 'activity_listing', 'activity', true);

		setTimeout(function () {
			$("#activities_searchList").val("");
			$("#show_all_holder").css("visibility", "visible");
		}, 600);
	},
	filterByCategory: function (event) {
		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		var category = element.innerHTML;
		$("#activities_searchList").val(category);

		$("#activities_searchList").trigger("keyup");
		setTimeout(function () {
			$("#activities_searchList").val("");
			$("#show_all_holder").css("visibility", "visible");
		}, 600);
	},
	clearSearch: function () {
		$("#show_all_holder").css("visibility", "hidden");
		$("#activities_searchList").val("");
		$("#activities_searchList").trigger("keyup");
		$("#activities_searchList").focus();
	},
});


window.activity_print_summary_view = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		"click .custom_dtp_height_indicator": "heightChange",
		"click .custom_dtp_height_indicator_right": "heightChangeRight"
	},
	heightChange: function () {
		setTimeout(function () {
			$('.custom_dtp_height').css("marginTop", "220px");
		}, 1);
	},
	heightChangeRight: function () {
		setTimeout(function () {
			$('.custom_dtp_height').css("marginTop", "250px");
		}, 1);
	},
	render: function () {
		var activities = this.collection.toJSON();
		$(this.el).html(this.template({ activities: activities, start_date: this.model.get("start_date"), end_date: this.model.get("end_date") }));

		setTimeout(function () {
			$('#start_dateInput').datetimepicker(
				{
					onGenerate: function (ct) {
						//$(".custom_dtp_height").css("top", "135px");
					},
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			$('#end_dateInput').datetimepicker(
				{
					onGenerate: function (ct) {
						//$(".custom_dtp_height").css("top", "135px");
					},
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			/*
			$('.range_dates').datetimepicker(
				{
					 onGenerate:function( ct ){
						//$(".custom_dtp_height").css("top", "135px");
					  },
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date=="" || end_date=="") {
							alert("You need both dates filled out");
							return;
						}
						document.location.href = "#activities/summary/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
					}
				}
			);
			*/
		}, 700);

		return this;
	}
});
window.activity_print_listing_view = Backbone.View.extend({
	initialize: function () {
	},
	render: function () {

		var self = this;
		var activities = this.collection.toJSON();
		var arrUserNickNames = [];
		var arrBillingAmounts = [];
		var arrHours = [];
		var arrInits = [];
		var arrUserRate = [];
		var arrActivityNickname = [];
		var arrUserNames = [];
		var activity_nickname = "";
		var activity_user_name = "";
		var min_date = "";
		var max_date = "";
		var total_invoice_amount = 0;
		var the_username = "";
		var intCounter = 0;
		var total_hours = 0;
		var invoice_date = "";
		var arrIDs = [];
		_.each(activities, function (activity) {
			if (arrIDs.indexOf(activity.activity_id) < 0) {
				arrIDs.push(activity.activity_id);
			}

			//invoice date
			if (invoice_date == "") {
				invoice_date = activity.invoice_date;
				if (invoice_date != "") {
					invoice_date = moment(invoice_date).format("MM/DD/YYYY");
				}
			}
			if (typeof self.model.get("invoice_number") == "undefined") {
				if (typeof activity.invoice_number != "undefined") {
					self.model.set("invoice_number", activity.invoice_number);
				}
			}
			if (typeof self.model.get("invoice_number") == "undefined") {
				self.model.set("invoice_number", "");
			}
			if (isNaN(activity.rate)) {
				activity.rate = 0;
			}
			activity.activity_user_nickname = "";
			if (!isNaN(activity.activity_user_id)) {
				if (typeof arrUserNickNames[activity.activity_user_id] == "undefined") {
					var theworker = worker_searches.findWhere({ "user_id": activity.activity_user_id });

					if (typeof theworker != "undefined") {
						var the_nickname = theworker.get("nickname").toUpperCase();
						the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[activity.activity_user_id] = the_nickname;
						activity.activity_user_nickname = the_nickname;

						if (arrActivityNickname.indexOf(activity.activity_user_nickname) < 0) {
							arrActivityNickname.push(activity.activity_user_nickname);
							arrUserNames.push(the_username);
							//initialize hours keeping
							arrHours[the_username] = 0;
							arrUserRate[the_username] = +activity.rate;
							//arrUserRate[the_username] = +activity.rate;
						}

					}
				} else {
					activity.activity_user_id = arrUserNickNames[activity.activity_user_id];
				}
			}

			var activity_hours = activity.hours;
			total_hours += Number(activity_hours);
			arrHours[the_username] += +Number(activity_hours);

			arrInits[the_username] = activity.nickname;
			if (max_date == "") {
				max_date = activity.activity_date;
			}
			min_date = activity.activity_date;

			//var billing_amount = activity.hours * (+activity.rate + +activity.tax);
			var billing_amount = activity.hours * (+activity.rate + 0);
			if (isNaN(billing_amount)) {
				billing_amount = 0;
			}
			var billing_value = Number(billing_amount);
			var billing_amount_string = String(billing_amount);
			if (billing_amount_string.indexOf('.') === -1) {
				billing_value = billing_value.toFixed(2);
				billing_amount = billing_value.toString();
			} else {
				var res = billing_amount_string.split(".");
				if (res[1].length < 3) {
					billing_value = billing_value.toFixed(2);
					billing_amount = billing_value.toString();
				}
			}
			activity.billing_amount = billing_amount;
			activity.billing_amount = formatDollar(activity.billing_amount);

			activity.rate = formatDollar(activity.rate);
			arrBillingAmounts.push(billing_amount);
			//arrBillingAmounts.push(billing_amount);

			//keep track of totals by user_id
			//every 12 items, you need to add a page break code
			activity.pagebreak = "";
			if (intCounter > 0) {
				if ((intCounter % 12) == 0) {
					activity.pagebreak = "<div style='page-break-after: always;'></div><div>&nbsp;</div>";
				}
			}
			total_invoice_amount += +billing_amount;


			intCounter++;
		});

		total_invoice_amount = formatDollar(total_invoice_amount);
		if (total_invoice_amount.indexOf(".") < 0) {
			total_invoice_amount = total_invoice_amount + ".00";
		}
		var case_id = "";
		var case_name = "";
		if (typeof self.model != "undefined") {
			if (typeof self.model.get("case_id") != "undefined") {
				case_id = self.model.get("case_id");
				case_name = self.model.get("name");
			}
			if (typeof self.model.get("start_date") != "undefined") {
				min_date = self.model.get("start_date");
			}
			if (typeof self.model.get("end_date") != "undefined") {
				max_date = self.model.get("end_date");
			}
			if (arrActivityNickname.length == 1) {
				activity_nickname = arrActivityNickname[0];
				activity_user_name = arrUserNames[0];
				activity_user_id = activities[0].activity_user_id;
			} else {
				activity_user_id = "all";
			}
			min_date = moment(min_date).format("MM/DD/YY");
			max_date = moment(max_date).format("MM/DD/YY");
		} else {
			min_date = moment().format("MM/DD/YY");
			max_date = moment().format("MM/DD/YY");
		}

		if (typeof self.model.get("invoice_id") == "undefined") {
			self.model.set("invoice_id", "");
		}
		$(this.el).html(this.template(
			{
				activities: activities,
				activity_count: arrIDs.length,
				case_id: this.collection.case_id,
				activity_user_id: activity_user_id,
				activity_nickname: activity_nickname,
				activity_user_name: activity_user_name,
				activity_start: min_date,
				activity_end: max_date,
				case_id: case_id,
				case_name: case_name,
				invoice_id: this.model.get("invoice_id"),
				total_invoice_amount: total_invoice_amount,
				total_hours: total_hours,
				user_names: arrUserNames,
				hours: arrHours,
				userrates: arrUserRate,
				userinits: arrInits,
				invoice_date: invoice_date,
				invoice_number: this.model.get("invoice_number")
			})
		);

		setTimeout(function () {
			$('.range_dates').datetimepicker(
				{
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						var user_id = $("#activity_user_id").val();

						document.location.href = "#activity_list/" + user_id + "/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
						//self.reloadActivities(user_id, moment(start_date).format("YYYY-MM-DD") , moment(end_date).format("YYYY-MM-DD"));
					}
				}
			);

			if (case_id != "") {
				//fix this
				$.get('/api/activity/billto/' + case_id, function (data) {
					$("#bill_to_info")
						.html(data) // John
					$("#billto_info_holder").show();
				}, "text");
			}
		}, 700);

		return this;
	}
});
window.activity_print_log = Backbone.View.extend({
	initialize: function () {
	},
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "activity_print_log";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}

		var self = this;
		var activities = this.collection.toJSON();
		var arrUserNickNames = [];
		var arrBillingAmounts = [];
		var arrHours = [];
		var arrInits = [];
		var arrUserRate = [];
		var arrActivityNickname = [];
		var arrUserNames = [];
		var activity_nickname = "";
		var activity_user_name = "";
		var min_date = "";
		var max_date = "";
		var total_invoice_amount = 0;
		var the_username = "";
		var intCounter = 0;
		var total_hours = 0;
		var invoice_date = "";
		var arrIDs = [];
		_.each(activities, function (activity) {
			if (arrIDs.indexOf(activity.activity_id) < 0) {
				arrIDs.push(activity.activity_id);
			}

			//invoice date
			if (invoice_date == "") {
				invoice_date = activity.invoice_date;
				if (invoice_date != "") {
					invoice_date = moment(invoice_date).format("MM/DD/YYYY");
				}
			}
			if (typeof self.model.get("invoice_number") == "undefined") {
				if (typeof activity.invoice_number != "undefined") {
					self.model.set("invoice_number", activity.invoice_number);
				}
			}
			if (typeof self.model.get("invoice_number") == "undefined") {
				self.model.set("invoice_number", "");
			}
			if (isNaN(activity.rate)) {
				activity.rate = 0;
			}
			activity.activity_user_nickname = "";
			if (!isNaN(activity.activity_user_id)) {
				if (typeof arrUserNickNames[activity.activity_user_id] == "undefined") {
					var theworker = worker_searches.findWhere({ "user_id": activity.activity_user_id });

					if (typeof theworker != "undefined") {
						var the_nickname = theworker.get("nickname").toUpperCase();
						the_username = theworker.get("user_name").toUpperCase();
						arrUserNickNames[activity.activity_user_id] = the_nickname;
						activity.activity_user_nickname = the_nickname;

						if (arrActivityNickname.indexOf(activity.activity_user_nickname) < 0) {
							arrActivityNickname.push(activity.activity_user_nickname);
							arrUserNames.push(the_username);
							//initialize hours keeping
							arrHours[the_username] = 0;
							arrUserRate[the_username] = +activity.rate;
							//arrUserRate[the_username] = +activity.rate;
						}

					}
				} else {
					activity.activity_user_id = arrUserNickNames[activity.activity_user_id];
				}
			}

			var activity_hours = activity.hours;
			total_hours += Number(activity_hours);
			arrHours[the_username] += +Number(activity_hours);

			arrInits[the_username] = activity.nickname;
			if (max_date == "") {
				max_date = activity.activity_date;
			}
			min_date = activity.activity_date;

			//var billing_amount = activity.hours * (+activity.rate + +activity.tax);
			var billing_amount = activity.hours * (+activity.rate + 0);
			if (isNaN(billing_amount)) {
				billing_amount = 0;
			}
			var billing_value = Number(billing_amount);
			var billing_amount_string = String(billing_amount);
			if (billing_amount_string.indexOf('.') === -1) {
				billing_value = billing_value.toFixed(2);
				billing_amount = billing_value.toString();
			} else {
				var res = billing_amount_string.split(".");
				if (res[1].length < 3) {
					billing_value = billing_value.toFixed(2);
					billing_amount = billing_value.toString();
				}
			}
			activity.billing_amount = billing_amount;
			activity.billing_amount = formatDollar(activity.billing_amount);

			activity.rate = formatDollar(activity.rate);
			arrBillingAmounts.push(billing_amount);
			//arrBillingAmounts.push(billing_amount);

			//keep track of totals by user_id
			//every 12 items, you need to add a page break code
			activity.pagebreak = "";
			if (intCounter > 0) {
				if ((intCounter % 12) == 0) {
					activity.pagebreak = "<div style='page-break-after: always;'></div><div>&nbsp;</div>";
				}
			}
			total_invoice_amount += +billing_amount;

			var the_activity = activity.activity.replaceAll("\r\n", "<br />");
			var arrActivity = the_activity.split("<br />");
			activity.short_activity = arrActivity[0];

			intCounter++;
		});

		total_invoice_amount = formatDollar(total_invoice_amount);
		if (total_invoice_amount.indexOf(".") < 0) {
			total_invoice_amount = total_invoice_amount + ".00";
		}
		var case_id = "";
		var case_name = "";
		if (typeof self.model != "undefined") {
			if (typeof self.model.get("case_id") != "undefined") {
				case_id = self.model.get("case_id");
				case_name = self.model.get("name");
			}
			if (typeof self.model.get("start_date") != "undefined") {
				min_date = self.model.get("start_date");
			}
			if (typeof self.model.get("end_date") != "undefined") {
				max_date = self.model.get("end_date");
			}
			if (arrActivityNickname.length == 1) {
				activity_nickname = arrActivityNickname[0];
				activity_user_name = arrUserNames[0];
				activity_user_id = activities[0].activity_user_id;
			} else {
				activity_user_id = "all";
			}
			min_date = moment(min_date).format("MM/DD/YY");
			max_date = moment(max_date).format("MM/DD/YY");
		} else {
			min_date = moment().format("MM/DD/YY");
			max_date = moment().format("MM/DD/YY");
		}

		if (typeof self.model.get("invoice_id") == "undefined") {
			self.model.set("invoice_id", "");
		}
		$(this.el).html(this.template(
			{
				activities: activities,
				activity_count: arrIDs.length,
				case_id: this.collection.case_id,
				activity_user_id: activity_user_id,
				activity_nickname: activity_nickname,
				activity_user_name: activity_user_name,
				activity_start: min_date,
				activity_end: max_date,
				case_id: case_id,
				case_name: case_name,
				invoice_id: this.model.get("invoice_id"),
				total_invoice_amount: total_invoice_amount,
				total_hours: total_hours,
				user_names: arrUserNames,
				hours: arrHours,
				userrates: arrUserRate,
				userinits: arrInits,
				invoice_date: invoice_date,
				invoice_number: this.model.get("invoice_number")
			})
		);

		setTimeout(function () {
			$('.range_dates').datetimepicker(
				{
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $("#start_dateInput").val();
						var end_date = $("#end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$("#end_dateInput").val(end_date);
						}
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						var user_id = $("#activity_user_id").val();

						document.location.href = "#activity_list/" + user_id + "/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");
						//self.reloadActivities(user_id, moment(start_date).format("YYYY-MM-DD") , moment(end_date).format("YYYY-MM-DD"));
					}
				}
			);

			if (case_id != "") {
				//fix this
				$.get('/api/activity/billto/' + case_id, function (data) {
					$("#bill_to_info")
						.html(data) // John
					$("#billto_info_holder").show();
				}, "text");
			}
		}, 700);

		return this;
	}
});

window.invoice_print_listing_view = Backbone.View.extend({
	initialize: function () {
	},
	render: function () {
		var self = this;
		var activity_invoices = this.collection.toJSON();

		_.each(activity_invoices, function (activity_invoice) {
			activity_invoice.activity_date = moment(activity_invoice.activity_date).format("MM/DD/YY");
		});

		$(this.el).html(this.template({ activity_invoices: activity_invoices, case_id: this.collection.case_id }));

		return this;
	}
});
window.invoice_listing_view = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		"click #invoice_edit": "editInvoice",
		"click .edit_invoice_full": "editInvoiceFull",
		"click .delete_invoice": "confirmdeleteInvoice",
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "invoice_listing_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		var activity_invoices = this.collection.toJSON();

		_.each(activity_invoices, function (activity_invoice) {
			activity_invoice.activity_date = moment(activity_invoice.activity_date).format("MM/DD/YY");
		});

		$(this.el).html(this.template({ activity_invoices: activity_invoices, case_id: this.collection.case_id }));

		return this;
	},
	confirmdeleteInvoice: function (event) {
		event.preventDefault();
		var element = event.currentTarget;
		if (element.id == "") {
			element = element.parentElement;
		}
		var elementArray = element.id.split("_");
		var id = elementArray[1];

		//alert(id);
		//return;

		composeDelete(id, "invoice");
	},
	canceldeleteInvoice: function (event) {
		event.preventDefault();
		$("#confirm_delete_invoice").fadeOut();
	},
	deleteInvoice: function (event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "invoice");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteInvoice(event);
			$(".invoice_row_" + id).css("background", "red");
			setTimeout(function () {
				//hide the processed row, no longer a batch scan
				$(".invoice_row_" + id).fadeOut();
			}, 2500);
		}
	},
	editInvoice: function (event) {
		var element = event.currentTarget;
		event.preventDefault();

		var arrClasses = element.className.split("_");

		var invoice_id = arrClasses[2];

		//alert(arrClasses);
		//return;

		var kase = kases.findWhere({ case_id: current_case_id });
		if (typeof kase == "undefined") {
			//get it
			var kase = new Kase({ id: current_case_id });
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid != "") {
						kases.remove(kase.id); kases.add(kase);
						self.kaseActivity(current_case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({ model: kase }).render().el);

		var invoice_activities = new InvoiceCollectionACT([], { invoice_id: invoice_id });
		invoice_activities.fetch({
			success: function (data) {
				var mydata = data.toJSON();
				document.location.href = "#invoices/activity/" + current_case_id + "/" + invoice_id;
				$(document).attr('title', "Activities for Case ID: " + current_case_id + " :: iKase");
				kase.set("holder", "#kase_content");
				//kase.set("invoice_id", invoice_id);\
				kase.set("invoice_id", data.invoice_id);
				kase.set("invoice_date", mydata.invoice_date);

				$('#kase_content').html(new activity_listing_view({ collection: invoice_activities, model: kase }).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
				$("#invoices_print").show();
			}
		});
	},
	editInvoiceFull: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrClasses = element_id.split("_");

		var invoice_id = arrClasses[1];

		//alert(arrClasses);
		//return;

		var kase = kases.findWhere({ case_id: current_case_id });
		if (typeof kase == "undefined") {
			//get it
			var kase = new Kase({ id: current_case_id });
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid != "") {
						kases.remove(kase.id); kases.add(kase);
						self.kaseActivity(current_case_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({ model: kase }).render().el);

		var invoice_activities = new ActivitiesCollection([], { case_id: current_case_id, invoice_id: invoice_id });
		invoice_activities.fetch({
			success: function (data) {
				var mydata = data.toJSON();
				document.location.href = "#invoices/activity/" + current_case_id + "/" + invoice_id;
				$(document).attr('title', "Activities for Case ID: " + current_case_id + " :: iKase");
				kase.set("holder", "#kase_content");
				//kase.set("invoice_id", invoice_id);\
				kase.set("invoice_id", data.invoice_id);
				kase.set("invoice_date", mydata.invoice_date);

				$('#kase_content').html(new activity_listing_view({ collection: invoice_activities, model: kase }).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
				$("#invoices_print").show();
			}
		});
	}
});
window.demographic_listing_view = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		"click #demographics_clear_search": "clearSearch",
		"click #label_search_demographics": "Vivify",
		"click #demographics_searchList": "Vivify",
		"focus #demographics_searchList": "Vivify",
		"blur #demographics_searchList": "unVivify",
		"click .demographic_link": "previewDemographics"
	},
	render: function () {
		var self = this;
		var demographics = this.collection.toJSON();

		_.each(demographics, function (demographic) {
			demographic.activity_date = moment(demographic.activity_date).format("MM/DD/YYYY");
		});

		try {
			$(this.el).html(this.template({ demographics: demographics }));

			$("#demographic_listing_holder").css("height", (window.innerHeight - 125) + "px");
			$("#demographic_preview_holder").css("height", (window.innerHeight - 125) + "px");

			setTimeout(function () {
				$("#demographic_listing th").css("font-size", "1.1em");
				$("#demographic_listing td").css("font-size", "1.1em");
			}, 1000);
			return this;
		}
		catch (err) {
			var view = "demographic_listing_view";
			var extension = "php";

			loadTemplate(view, extension, self);

			return "";
		}
	},
	unVivify: function (event) {
		var textbox = $("#demographics_searchList");
		var label = $("#label_search_demographics");

		if (textbox.val() == "") {
			label.animate({ color: "#999", fontSize: "1em", top: "0px" }, 300);

		} else {
			return;
		}
	},
	Vivify: function (event) {
		var textbox = $("#demographics_searchList");
		var label = $("#label_search_demographics");



		if (textbox.val() == "") {
			label.animate({ top: "-9px", fontSize: "0.58em", color: "#CCC" }, 250);
			//$('#notes_searchList').focus();
		}
	},
	clearSearch: function () {
		$("#demographics_searchList").val("");
		$("#demographics_searchList").trigger("keyup");
	},
	previewDemographics: function (event) {
		var element = event.currentTarget;
		var element_id = element.id.replace("demographic_", "");
		var url = "reports/demographics_sheet.php?case_id=" + element_id;
		$("#demographic_preview_holder").attr("src", url);

		if ($("#demographic_right_pane").css("display") != "block") {
			$("#demographic_listing_holder").css("width", "745px");
			$("#demographic_preview_holder").css("width", (window.innerWidth - 795) + "px");
			$("#demographic_preview_holder").css("height", (window.innerHeight - 125) + "px");
			$("#demographic_right_pane").fadeIn();
		}
	}
});
window.activity_listing_view = Backbone.View.extend({
	initialize: function () {

	},
	events: {
		"click .activity_category": "filterByCategory",
		"click .activity_by": "filterByEmployee",
		"click #activities_clear_search": "clearSearch",
		"click #show_all": "clearSearch",
		"click .compose_activity": "sendActivity",
		"click .edit_event": "newEvent",
		"click .edit_task": "newTask",
		"click .check_all": "checkAll",
		"click .check_thisone": "checkSome",
		"click .restore_archives": "restoreArchives",
		"click .read_more": "expandActivity",
		"click .hide_activity": "shrinkActivity",
		"click .filter_user": "filterUser",
		"change #mass_change": "massChange",
		"click #label_search_activity": "Vivify",
		"click #activity_hours_input_activator": "editHours",
		"click #save_edit_hours": "saveHours",
		"click #cancel_edit_hours": "cancelHours",
		"click #activity_input_activator": "editActivity",
		"click .compose_new_activity": "newActivity",
		"click .compose_task": "composeTask",
		"click .compose_new_note": "composeNote",
		"click .compose_event": "composeEvent",
		"click #hide_file_access": "hideFileAccess",
		//"blur .activity_input":						"saveActivity",
		"click #activities_searchList": "Vivify",
		"focus #activities_searchList": "Vivify",
		"click #invoices": "listInvoices",
		"blur #activities_searchList": "unVivify",
		"click .expand_activity": "expandUserActivity",
		"click .shrink_activity": "shrinkUserActivity"
	},
	render: function () {
		if (typeof this.template != "function") {
			var view = "activity_listing_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;

		var activities = this.collection.toJSON();
		var arrUserNickNames = [];
		var blnArchives = false;
		if (typeof self.model.get("mode") == "undefined") {
			self.model.set("mode", "");
		}
		var arrIDs = [];
		var arrTotals = [];
		var arrEmployees = [];
		var blnWCAB = false;
		var blnWCABDefense = false;
		if (current_case_id != -1) {
			var kase = kases.findWhere({ case_id: current_case_id });

			if (typeof kase == "undefined") {
				//get it
				var kase = new Kase({ id: current_case_id });
				kase.fetch({
					success: function (kase) {
						if (kase.toJSON().uuid != "") {
							kases.remove(kase.id); kases.add(kase);
							self.render();
						} else {
							//case does not exist, get out
							document.location.href = "#";
						}
						return;
					}
				});
				return;
			}
			var kase_type = kase.get("case_type");
			blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1) || (kase_type.indexOf("W/C") > -1));
			blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
		}
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnWCABDefense", blnWCABDefense);
		var arrDates = [];
		var arrFilters = [];
		var arrFilterCategories = [];
		var filtered_activities = new Backbone.Collection;
		_.each(activities, function (activity) {
			if (arrDates.indexOf(activity.activity_date) < 0) {
				arrDates.push(activity.activity_date);
				//clean up any document name with apostrophe
				var blnDoc = (activity.activity.indexOf("Document [<a href='") == 0);

				if (blnDoc) {
					var endpos = activity.activity.indexOf("' target='_blank'");
					var href = activity.activity.substring(19, endpos);
					//new_href = href.replaceAll("'", "\\'");
					new_href = href.replaceAll("'", "&apos;");
					activity.activity = activity.activity.replace(href, new_href);
				}
				//make the case stand out
				activity.plain_user_id = activity.activity_user_id;
				if (arrIDs.indexOf(activity.activity_id) < 0) {
					arrIDs.push(activity.activity_id);
					if (typeof arrTotals[activity.activity_user_id] == "undefined") {
						arrTotals[activity.activity_user_id] = 0;
					}
					arrTotals[activity.activity_user_id]++;
				}
				if (arrEmployees.indexOf(activity.activity_user_id) < 0) {
					arrEmployees[activity.activity_user_id] = activity.by;
				}

				//archives?
				if (customer_id == 1049) {
					if (current_case_id < 19545) {
						if (!blnArchives) {
							var cpointer = kase.toJSON().cpointer;
							blnArchives = (activity.activity_uuid.indexOf(cpointer) == 0);
						}
					}
				}

				if (typeof self.model.get("invoice_number") == "undefined") {
					if (typeof activity.invoice_number != "undefined") {
						self.model.set("invoice_number", activity.invoice_number);
					}
				}
				if (typeof self.model.get("invoice_number") == "undefined") {
					self.model.set("invoice_number", "");
				}
				if (!isNaN(activity.activity_user_id)) {
					if (typeof arrUserNickNames[activity.activity_user_id] == "undefined") {
						var theworker = worker_searches.findWhere({ "user_id": activity.activity_user_id });
						if (typeof theworker != "undefined") {
							var the_nickname = theworker.get("nickname").toUpperCase();
							arrUserNickNames[activity.activity_user_id] = the_nickname;
							activity.activity_user_id = the_nickname;
						}
					} else {
						activity.activity_user_id = arrUserNickNames[activity.activity_user_id];
					}
				}
				var activity_hours = activity.hours;

				if (activity.activity.indexOf("Document [") > -1) {
					if (activity.activity.indexOf("C:") > -1) {
						activity.activity = activity.activity.replace("C:\\inetpub\\wwwroot\\iKase.website\\uploads\\" + customer_id + "\\" + current_case_id + "\\", "");
					}

					arrActivity = activity.activity.split("'>");
					arrActivity[0] = arrActivity[0].replace("target='_blank", "target='_blank' style='background:yellow;color:black");
					//clean up link
					arrActivity[0] = arrActivity[0].replace(customer_id + "/" + current_case_id, "~")
					arrActivity[0] = arrActivity[0].replace(customer_id + "/", customer_id + "/" + current_case_id + "/");
					arrActivity[0] = arrActivity[0].replace("~", customer_id + "/" + current_case_id);

					activity.activity = arrActivity.join("'>");
				}
				if (activity.activity.indexOf("Letter [") > -1) {
					arrActivity = activity.activity.split("'>");
					//clean up in case properly setup...
					arrActivity[0] = arrActivity[0].replace(".docx", "");
					arrActivity[0] = arrActivity[0].replace("' target='_blank", "");
					activity.activity = arrActivity[0] + ".docx' target='_blank' style='background:yellow;color:black'>" + arrActivity[1];
				}
				if (activity.activity.indexOf("PDF Form [") > -1) {
					activity.activity = activity.activity.replaceAll("<br><br>", "<br>");
					activity.activity = activity.activity.replaceAll("class='white_text'>review", "style='cursor:pointer; background:yellow;color:black' class='white_text' title='Click to review PDF Form'>Review");
				}

				if (activity.activity.indexOf("Task [") > -1) {
					if (activity.activity.indexOf("edit_task_") < 0) {
						activity.activity = activity.activity.replaceAll("id='", "id='edit_task_");
					}
				}

				//edit mode
				activity.check_box = "";
				if (self.model.get("mode") == "invoice_edit") {
					if (activity.invoice_id != "") {
						activity.check_box = " checked";
					}
				}
				//break up
				if (customer_id != 1055 && customer_id != 1049 && customer_id != 1075) {
					if (activity.by == "") {
						//let's get the by and any trailing info
						var strpos = activity.activity.indexOf(" by");

						if (strpos > -1) {
							var activity_header = activity.activity.substring(0, activity.activity.indexOf(" by"));
							var activity_footer = activity.activity.substring(activity.activity.indexOf(" by") + 3);
							activity_footer = activity_footer.replaceAll("<br />", "\r\n\r\n");
							var arrFooter = activity_footer.split("\r\n\r\n");
							activity_footer = arrFooter[0];
							if (arrFooter.length > 1) {
								arrFooter.splice(0, 1);
								activity_header += "<br><br>" + arrFooter.join("<br>");
							}
						} else {
							var activity_header = activity.activity;
							var activity_footer = "";
						}
						activity.activity = activity_header.replaceAll("\r\n", "<br>");
						activity.activity = activity.activity.replaceAll("class='edit_event", "class='white_text edit_event");
						activity.activity = activity.activity.replaceAll("- 00/00/0000", "");
						activity.activity = activity.activity.replaceAll("D:/uploads/uploads/", "D:/uploads/");
						activity.activity = activity.activity.replaceAll("style='cursor:pointer'>", "style='cursor:pointer; background:yellow;color:black'>")

						activity.by = activity_footer;
					}
				}
				activity.activity = activity.activity.replaceTout('background-color: rgb(255, 255, 255);', '');
				//send to client
				activity.send_link = "<a id='activity_" + activity.id + "_" + activity.case_id + "' style='cursor:pointer' class='compose_activity white_text' title='Click to send this Activity item to Client'>Send&nbsp;to&nbsp;Client</a>";

				activity.full_activity = "";
				if (activity.activity.indexOf("From:") < 0 && activity.activity.indexOf("Subject:") < 0) {
					//length
					var pure_snippet = activity.activity;
					pure_snippet = pure_snippet.replaceAll("<br>", "~~");
					pure_snippet = removeHtml(pure_snippet);
					pure_snippet = pure_snippet.replaceAll("~~", ", ");
					pure_snippet = pure_snippet.replaceAll(", ; ", "");
					if (pure_snippet.indexOf(", ") == 0) {
						pure_snippet = pure_snippet.substr(2);
					}
					if (pure_snippet.length > 75) {
						//pure_snippet = pure_snippet.substring(0, 75);
						pure_snippet = pure_snippet.getComplete(75) + "&nbsp;&#8230;";
					}

					if (activity.activity.length > 499) {
						activity.full_activity = "<div id='fullactivity_" + activity.id + "' style='display:'><div style='width:65px; text-align:center; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "</div><div style='width:65px; text-align:center; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>";
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + pure_snippet + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a></div>";

					} else {
						activity.activity = activity.activity.replaceAll("\r\n", "<br>")
					}
				} else {
					if (activity.activity_category != "Letters") {
						//find the subject
						//find the end of the line
						//apply ...
						var subpos = activity.activity.indexOf("Subject:");
						var retpos = activity.activity.indexOf("<br />", subpos);
						short_activity = activity.activity;
						if (retpos > 0) {
							short_activity = activity.activity.substr(0, retpos) + "<br><a id='readmore_" + activity.id + "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more ...</a>";
							activity.full_activity = "<div id='fullactivity_" + activity.id + "' style=''><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>" + activity.activity.replaceAll("\r\n", "<br>") + "</div><div style='width:65px; cursor:pointer; background:white; color:black' id='hideactivity_" + activity.id + "' class='hide_activity white_text' title='Click to shrink activity'>close</div>";
						}
						activity.activity = "<div class='partialactivity' id='partialactivity_" + activity.id + "'>" + short_activity + "</div>";
					} else {
						//small clean up
						activity.activity = activity.activity.replace("<br> - ", "");
					}
				}

				//clean up
				activity.activity = activity.activity.replaceAll("&#;160", "");
				//the actual date
				activity.activity_date = moment(activity.activity_date).format("MM/DD/YY h:mmA");
				activity.activity_date = activity.activity_date.replace("3:00AM", "");
				activity.activity_date = activity.activity_date.replace("2:00AM", "");

				filtered_activities.add(activity);

				var activity_filter = '<span class="activity_category" style="color:white; cursor:pointer" title="Click to Filter by this category">' + activity.activity_category + '</span>';

				if (arrFilters.indexOf(activity_filter) < 0) {
					arrFilters.push(activity_filter);
				}
			}
		});

		activities = filtered_activities.toJSON();
		this.model.set("blnArchives", blnArchives);
		if (typeof this.model.get("report") == "undefined") {
			this.model.set("report", false);
		}
		if (typeof this.model.get("start_date") == "undefined") {
			this.model.set("start_date", "");
		}
		if (typeof this.model.get("end_date") == "undefined") {
			this.model.set("end_date", "");
		}
		if (this.model.get("start_date") == "00/00/0000") {
			this.model.set("start_date", moment().format("MM/DD/YYYY"));
		}
		if (this.model.get("end_date") == "00/00/0000") {
			this.model.set("end_date", moment().format("MM/DD/YYYY"));
		}
		this.model.set("user_name", "");
		if (typeof this.model.get("user_id") == "undefined") {
			this.model.set("user_id", "");
		} else {
			if (this.model.get("user_id") != "all") {
				var theworker = worker_searches.findWhere({ "user_id": this.model.get("user_id") });
				if (typeof theworker != "undefined") {
					var the_nickname = theworker.get("nickname").toUpperCase();
					var the_username = theworker.get("user_name").toUpperCase();
					this.model.set("nickname", the_nickname);
					this.model.set("user_name", the_username);
				}
				setTimeout(function () {
					$("#show_all_holder").removeClass("hidden");
					$("#show_all_holder").css("visibility", "visible");
				}, 789);
			}
		}
		if (this.model.get("user_id") == "all") {
			this.model.set("nickname", "All Employees");
			this.model.set("user_name", "");
		}
		if (typeof this.model.get("invoice_id") == "undefined") {
			this.model.set("invoice_id", "");
		}

		if (typeof this.model.get("invoice_date") == "undefined") {
			this.model.set("invoice_date", "");
		}
		if (typeof this.model.get("billing_amount") == "undefined") {
			this.model.set("billing_amount", "");
		}

		var activity_summary = [];
		arrTotals.forEach(function (currentValue, index, array) {
			var employee_name = arrEmployees[index];
			activity_summary.push("<a class='filter_user white_text' id='filter_" + index + "' style='cursor:pointer; text-decoration:underline'>" + employee_name + "</a>: " + currentValue);
		});
		var totals = "";
		if (activity_summary.length > 1) {
			totals = "<div style='display:inline-block; color:white'>" + activity_summary.join("&nbsp;|&nbsp;") + "<br><span style='font-size:0.8em; font-style:italic'>Click on nickname to Filter</span></div>";
		}

		var filters = "";
		if (arrFilters.length > 0) {
			filters = arrFilters.join(" | ");
			filters = "Filters:&nbsp;" + filters;
		}
		_.each(activities, function (activity) {
			activity.user_total_count = arrTotals[activity.plain_user_id];
		});
		//kase.set("invoice_date", data.invoice_date);
		try {
			$(this.el).html(this.template({ activities: activities, activity_count: arrIDs.length, totals: totals, case_id: current_case_id, user_id: this.model.get("user_id"), user_name: this.model.get("user_name"), nickname: this.model.get("nickname"), report: this.model.get("report"), start_date: this.model.get("start_date"), end_date: this.model.get("end_date"), invoice_id: this.model.get("invoice_id"), billing_amount: this.model.get("billing_amount"), invoice_number: this.model.get("invoice_number"), blnWCAB: blnWCAB, invoice_date: this.model.get("invoice_date"), filters: filters }));
		}
		catch (err) {
			alert(err);

			return "";
		}

		tableSortIt("activity_listing");

		$("#activity_listing").addClass("glass_header_no_padding");

		setTimeout(function () {
			$('.range_dates').datetimepicker(
				{
					timepicker: false,
					format: 'm/d/Y',
					mask: false,
					onChangeDateTime: function (dp, $input) {
						var start_date = $(".activity #start_dateInput").val();
						var end_date = $(".activity #end_dateInput").val();
						var d1 = new Date(moment(start_date));
						var d2 = new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$(".activity #end_dateInput").val(end_date);
						}
						var user_id = $(".activity #user_id").val();
						if (start_date == "" || end_date == "") {
							alert("You need both dates filled out");
							return;
						}
						//document.location.href = "#activities/" + user_id + "/" + moment(start_date).format("YYYY-MM-DD") + "/" + moment(end_date).format("YYYY-MM-DD");

						self.reloadActivities(user_id, moment(start_date).format("YYYY-MM-DD"), moment(end_date).format("YYYY-MM-DD"));
					}
				}
			);

			var activities = self.collection.toJSON();
			var activities_length = activities.length;
			if (!self.model.get("blnArchives") && customer_id == 1049) {
				//if (customer_id == 1049 && current_case_id < 19545) {
				var url = "../api/activity/archivecount/" + current_case_id;
				$.ajax({
					url: url,
					type: 'GET',
					dataType: "json",
					success: function (data) {
						if (data.count > 0) {
							//offer the user the option of restoring
							var the_count = numberWithCommas(data.count) + " records (about&nbsp;" + (Math.round(data.count / 60) / 10).toFixed(2) + " minutes @ 10 recs/sec)";
							$("#restore_archives_count").html("<span style='font-size:0.8em'>" + the_count + "</span>");
							$(".restore_archives").fadeIn(function () {
								$("#restore_archives_count").show();
							});
						} else {
							//negative number
							//just fetch
							//get the activities, there are there waiting
							var activities = new ActivitiesCollection([], { case_id: current_case_id });
							activities.fetch({
								success: function (data) {
									//we might have moved on though
									if ($("#activity_listing").length > 0) {
										$('#kase_content').html(new activity_listing_view({ collection: activities, model: self.model }).render().el);
										$("#kase_content").removeClass("glass_header_no_padding");
										hideEditRow();
									}
								}
							});
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						// report error
						console.log(errorThrown);
					}
				});
			}
		}, 700);

		var case_id = this.model.get("case_id");
		var case_status = "";
		var case_substatus = "";
		var attorney = "";
		var worker = "";
		var rating = "";
		if (typeof case_id != "undefined") {
			var kase = kases.findWhere({ case_id: case_id });
			//console.log(kase.toJSON());
			case_status = kase.toJSON().case_status;
			case_substatus = kase.toJSON().case_substatus;
			attorney = kase.toJSON().attorney;
			worker = kase.toJSON().worker;
			rating = kase.toJSON().rating;

			setTimeout(function () {
				$("#case_number_fill_in").html(kase.toJSON().case_number);
				$("#adj_number_fill_in").html(kase.toJSON().adj_number);
				if (kase.toJSON().adj_number == "") {
					$("#adj_slot").hide();
				}
				$("#case_status_fill_in").html(kase.toJSON().case_status);
				$("#case_substatus_fill_in").html(kase.toJSON().case_substatus);
				$("#attorney_fill_in").html(kase.toJSON().attorney);
				$("#rating_fill_in").html(kase.toJSON().rating);
				$("#worker_fill_in").html(kase.toJSON().worker);
				$("#case_date_fill_in").html(kase.toJSON().case_date);
				$("#claims_fill_in").html(kase.toJSON().claims);
				if (kase.toJSON().claims == "") {
					//$("#claims_slot").hide();
				}
				$("#case_type_fill_in").html(kase.toJSON().case_type);
				$("#case_type").val(kase.toJSON().case_type);
				$("#language_fill_in").html(kase.toJSON().language);
				if (kase.toJSON().language == "") {
					$("#language_slot").hide();
				}
			}, 10);
		}
		//var kase = kases.findWhere({case_id: this.model.get("case_id")});
		this.model.set("case_status", case_status);
		this.model.set("case_substatus", case_substatus);
		this.model.set("attorney", attorney);
		this.model.set("worker", worker);
		this.model.set("rating", rating);
		if (typeof this.model.get("case_id") != "undefined") {
			var parties = new Parties([], { case_id: this.model.get("case_id"), case_uuid: this.model.get("uuid"), panel_title: "" });
			parties.fetch({
				success: function (parties) {
					var claim_number = "";
					var carrier_insurance_type_option = "";
					//now we have to get the adhocs for the carrier
					var carrier_partie = parties.findWhere({ "type": "carrier" });
					if (typeof carrier_partie == "undefined") {
						carrier_partie = new Corporation({ case_id: self.model.get("case_id"), type: "carrier" });
						carrier_partie.set("corporation_id", -1);
						carrier_partie.set("partie_type", "Carrier");
						carrier_partie.set("color", "_card_missing");
					}
					carrier_partie.adhocs = new AdhocCollection([], { case_id: case_id, corporation_id: carrier_partie.attributes.corporation_id });
					carrier_partie.adhocs.fetch({
						success: function (adhocs) {
							var adhoc_claim_number = adhocs.findWhere({ "adhoc": "claim_number" });

							if (typeof adhoc_claim_number != "undefined") {
								claim_number = adhoc_claim_number.get("adhoc_value");
							}

							var adhoc_carrier_insurance_type_option = adhocs.findWhere({ "adhoc": "insurance_type_option" });

							if (typeof adhoc_carrier_insurance_type_option != "undefined") {
								carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
							}
							var arrClaimNumber = [];
							var arrCarrierInsuranceTypeOption = [];
							if (carrier_partie.attributes.claim_number != "" && carrier_partie.attributes.claim_number != null) {
								//arrClaimNumber.push(partie.claim_number);
								var claim_number = carrier_partie.attributes.claim_number;
								$("#claim_number_fill_in").html(claim_number);
								kase.set("claim_number", claim_number);
							}
						}
					});
				}
			});

			//let's get the archive
			if (user_data_path == "A1" || user_data_path == "perfect") {
				var kase_archives = new LegacyArchiveCollection([], { case_id: current_case_id });
			} else {
				var kase_archives = new ArchiveCollection([], { case_id: current_case_id });
			}
			kase_archives.fetch({
				success: function (data) {
					if (data.length > 0) {
						if (data.toJSON()[0].document_name != "" || data.toJSON()[0].path != "") {
							$("#archive_count").html("(" + data.length + ")");
						}
					}
				}
			});
		}

		setTimeout(function () {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
		}, 750);

		return this;
	},
	reloadActivities: function (user_id, start_date, end_date) {
		$("#content").html(loading_image);
		window.Router.prototype.userActivity(user_id, start_date, end_date);
		window.history.replaceState(null, null, "#activities/" + user_id + "/" + start_date + "/" + end_date);
		app.navigate("activities/" + user_id + "/" + start_date + "/" + end_date, { trigger: false });
	},
	filterUser: function (event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[1];
		var start_date = $(".activity #start_dateInput").val();
		var end_date = $(".activity #end_dateInput").val();

		this.reloadActivities(id, moment(start_date).format("YYYY-MM-DD"), moment(end_date).format("YYYY-MM-DD"));
	},
	hideFileAccess: function (event) {
		event.preventDefault();
		var $rows = $('.activity_data_row');
		var val = "file accessed";
		$rows.show().filter(function () {
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();

			return ~text.indexOf(val);
		}).hide();

		$("#hide_file_holder").fadeOut();
		var href = $("#print_activity").attr("href");
		var new_href = href.replace("#activity", "#activity_case");
		$("#print_activity").attr("href", new_href);
	},
	editHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_class = element.className;
		var arrClasses = element_class.split("_");

		//alert(element_class);
		$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).hide();
		//$("#activity_hours_edit_" + arrClasses[2]).css("display", "");
		$("#activity_hours_input_holder_" + arrClasses[3]).css("display", "");
		var original_value = $(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).html();
		//original_value = original_value.replace("<br>", "\r\n");
		$("#activity_hours_edit_" + arrClasses[3]).val(original_value);
		$("#activity_hours_edit_" + arrClasses[3]).focus();

	},
	saveHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");

		var self = this;
		var url = "../api/activity/update_hours";
		var this_case_id = current_case_id;

		var hoursVal = $("#activity_hours_edit_" + arrClasses[3]).val();
		var original_value = $(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).html();

		if (hoursVal == "" || hoursVal == original_value) {
			//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
			$("#activity_hours_input_holder_" + arrClasses[3]).css("display", "none");
			$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).show();
			return;
		}

		var formValues = "activity_uuid=" + arrClasses[3] + "&hours=" + hoursVal;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
					$("#activity_hours_input_holder_" + arrClasses[3]).css("display", "none");
					$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).html(hoursVal);
					$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).css("color", "#32CD32");
					$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).show();
					setTimeout(function () {
						$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).css("color", "white");
					}, 2000);
				}
			}
		});
	},
	listInvoices: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_class.split("_");

		//get the kase
		var kase = kases.findWhere({ case_id: arrClasses[1] });
		if (typeof kase == "undefined") {
			//get it
			var kase = new Kase({ id: arrClasses[1] });
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid != "") {
						kases.remove(kase.id); kases.add(kase);
						self.kaseActivity(arrClasses[1]);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;
				}
			});
			return;
		}
		kase.set("header_only", true);
		$('#content').html(new kase_view({ model: kase }).render().el);

		var activity_invoices = new ActivityInvoiceCollection([], { case_id: arrClasses[1] });
		activity_invoices.fetch({
			success: function (data) {
				document.location.href = "#invoices/" + arrClasses[1];
				$(document).attr('title', "Activities for Case ID: " + arrClasses[1] + " :: iKase");
				kase.set("holder", "#kase_content");
				$('#kase_content').html(new invoice_listing_view({ collection: activity_invoices, model: kase }).render().el);
				$("#kase_content").removeClass("glass_header_no_padding");
				hideEditRow();
			}
		});
	},
	cancelHours: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");

		$("#activity_hours_input_holder_" + arrClasses[3]).css("display", "none");
		$(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[3]).show();
		return;
	},
	editActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		//console.log(element);
		var element_id = element.id;

		var element_class = element.className;
		var arrClasses = element_class.split("_");
		//alert(arrClasses[2]);
		//return;
		var original_hours_value = $(".activity_hours_input_activator#activity_hours_uuid_" + arrClasses[2]).html();
		var original_value = $("#activity_input_activator.activity_uuid_" + arrClasses[2]).html();

		composeActivity(arrClasses[2], original_hours_value, original_value);

	},
	composeEvent: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	composeNote: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	composeTask: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask();
	},
	newActivity: function (event) {
		var element = event.currentTarget;
		//event.preventDefault();
		composeNewActivity();
	},
	saveActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var element_class = element.className;
		var arrClasses = element_id.split("_");

		var self = this;
		var url = "../api/activity/update_activity";
		var this_case_id = current_case_id;

		var activityVal = $("#activity_edit_" + arrClasses[2]).val();
		var original_value = $("#activity_input_activator.activity_uuid_" + arrClasses[2]).html();


		if (activityVal == "" || activityVal == original_value) {
			//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
			$("#activity_input_holder_" + arrClasses[2]).css("display", "none");
			$("#activity_input_activator.activity_uuid_" + arrClasses[2]).show();
			return;
		}


		var formValues = "activity_uuid=" + arrClasses[2] + "&activity=" + activityVal;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#activity_hours_edit_" + arrClasses[3]).css("display", "none");
					$("#activity_input_holder_" + arrClasses[2]).css("display", "none");
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).html(activityVal);
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).css("color", "#32CD32");
					$("#activity_input_activator.activity_uuid_" + arrClasses[2]).show();
					setTimeout(function () {
						$("#activity_input_activator.activity_uuid_" + arrClasses[2]).css("color", "white");
					}, 2000);
				}
			}
		});
	},
	expandActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#activity_partial_holder_" + theid).fadeOut(function () {
			$("#activity_full_holder_" + theid).fadeIn();
		});
	},
	shrinkActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#activity_full_holder_" + theid).fadeOut(function () {
			$("#activity_partial_holder_" + theid).fadeIn();
		});
	},
	expandUserActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#expand_" + theid).hide();
		$("#shrink_" + theid).show();
		$(".expand_user_" + theid).show();
	},
	shrinkUserActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		//alert(element.id);
		var theid = element.id.split("_")[1];
		$("#expand_" + theid).show();
		$("#shrink_" + theid).hide();
		$(".expand_user_" + theid).hide();
	},
	unVivify: function (event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_activity");

		if (textbox.val() == "") {
			label.animate({ color: "#999", fontSize: "1em", top: "0px" }, 300);

		} else {
			return;
		}
	},
	Vivify: function (event) {
		var textbox = $("#activities_searchList");
		var label = $("#label_search_activity");



		if (textbox.val() == "") {
			label.animate({ top: "-9px", fontSize: "0.58em", color: "#CCC" }, 250);
			//$('#notes_searchList').focus();
		}
	},
	restoreArchives: function (event) {
		var self = this;
		var url = "../api/activity/archive/" + current_case_id;
		var this_case_id = current_case_id;
		$("#kase_content").html(loading_image + "<div class='white_text' style='text-align:center'><br>Please be patient, it may take a couple minutes to retrieve archives, because there can be up to 5000 entries per case.<br><br>Restoring activities from the Archive only needs to be done once.<br><br><span style='background:orange;color:black'>Because this action takes place on the server, you don't have to stay on this screen.  When you return in a few minutes, the archives will be there.</span></div>");
		$.ajax({
			url: url,
			type: 'GET',
			dataType: "json",
			success: function (data) {
				//refresh the page if we are still here
				if (document.location.hash.indexOf("#activity") == 0) {
					//get the activities, there are there waiting
					var activities = new ActivitiesCollection([], { case_id: current_case_id });
					activities.fetch({
						success: function (data) {
							$('#kase_content').html(new activity_listing_view({ collection: activities, model: self.model }).render().el);
							$("#kase_content").removeClass("glass_header_no_padding");
							hideEditRow();
						}
					});
				} else {
					alert("Your 'Restore from Archive' request has completed for Case ID " + this_case_id);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				// report error
				console.log(errorThrown);
			}
		});
	},
	newEvent: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	},
	checkSome: function (event) {
		//event.preventDefault();
		var element = event.currentTarget;
		var element_id = $(element).attr('id');
		//var arrElement = element_id.split("_");
		//var activity_uuid = arrElement[2];
		//$('.check_thisone').prop('checked', "checked");
		if ($('#mass_change').is(":visible")) {
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

	},
	checkAll: function (event) {
		var element = event.currentTarget;
		$('.check_thisone').prop('checked', "checked");
		if ($('#mass_change').is(":visible")) {
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

	},
	massChange: function (event) {
		//alert("Hey, I'm working.");
		//return;
		var dropdown = event.currentTarget;
		var arrCheckBoxes = $('.check_thisone');
		var arrChecked = [];
		var arrLength = arrCheckBoxes.length;

		for (var i = 0; i < arrLength; i++) {
			var element = arrCheckBoxes[i];
			//var elementsArray = elements.id.split("_");
			if (element.checked) {
				var checkbox_element = element.id;
				var arrCheckbox = checkbox_element.split("_");
				var activity_uuid = arrCheckbox[2];
				//alert(arrCheckbox);
				arrChecked.push(activity_uuid);
			}
		}

		if (arrChecked.length == 0) {
			document.getElementById(dropdown.id).selectedIndex = 0;
			return;
		}
		this.model.set("checked_boxes", arrChecked);
		var ids = arrChecked.join(", ");
		//alert(arrChecked);
		var action = dropdown.value;
		if (action != "" || action != "undefined") {
			if (action == "print") {
				console.log(action);
				//composeDateChange(ids, "activity");
			} else {
				var url = "../api/activity/insert_invoiceactivity";

				/*$.each( ids, function( key, value ) {
				  alert( key + ": " + value );
				  var invoice_item_info = $("activity_row_").html();
				  arrInvoiceItems.push(invoice_item_info);
				});
				*/
				//for (i = 0; i < ids.length; i++) {
				var formValues = "id=" + ids;

				$.ajax({
					url: url,
					type: 'POST',
					dataType: "json",
					data: formValues,
					success: function (data) {
						if (data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else {
							$("#saved_invoice").show();
							var kase = kases.findWhere({ case_id: current_case_id });
							if (typeof kase == "undefined") {
								//get it
								var kase = new Kase({ id: curent_case_id });
								kase.fetch({
									success: function (kase) {
										if (kase.toJSON().uuid != "") {
											kases.remove(kase.id); kases.add(kase);
											self.kaseActivity(current_case_id);
										} else {
											//case does not exist, get out
											document.location.href = "#";
										}
										return;
									}
								});
								return;
							}
							kase.set("header_only", true);
							$('#content').html(new kase_view({ model: kase }).render().el);

							var activity_invoices = new ActivityInvoiceCollection([], { case_id: current_case_id });
							activity_invoices.fetch({
								success: function (data) {
									$(document).attr('title', "Invoices for Case ID: " + current_case_id + " :: iKase");
									kase.set("holder", "#kase_content");
									$('#kase_content').html(new invoice_listing_view({ collection: activity_invoices, model: kase }).render().el);
									$("#kase_content").removeClass("glass_header_no_padding");
									$("#saved_invoice").hide();
									hideEditRow();
								}
							});
						}
					}
				});
				//};
				//composeInvoiceItems(ids, "invoice");
			}
		} else {
			console.log("no action");

		}
		//composeDelete(id, "webmail");
	},
	newTask: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeTask(element.id);
	},
	sendActivity: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	filterByEmployee: function (event) {
		var element = event.currentTarget;
		event.preventDefault();
		var by = element.innerHTML;
		$("#activities_searchList").val(by);

		//$( "#activities_searchList" ).trigger( "keyup" );
		var obj = document.getElementById("activities_searchList");
		findIt(obj, 'activity_listing', 'activity', true);

		setTimeout(function () {
			$("#activities_searchList").val("");
			$("#show_all_holder").removeClass("hidden");
			$("#show_all_holder").css("visibility", "visible");
		}, 600);
	},
	filterByCategory: function (event) {
		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		var category = element.innerHTML;
		/*
		$("#activities_searchList").val(category);
		
		$( "#activities_searchList" ).trigger( "keyup" );
		*/
		$(".activity_data_row").show();
		var cats = $(".activity_listing .activity_category");
		for (var i = 0; i < cats.length; i++) {
			var span = cats[i];
			if (span.innerHTML != category) {
				//hide the row;
				span.parentElement.parentElement.style.display = "none";
			}
		}
		setTimeout(function () {
			$("#activities_searchList").val("");
			$("#show_all_holder").css("visibility", "visible");
		}, 600);

	},
	clearSearch: function () {
		/*
		$("#show_all_holder").css("visibility", "hidden");
		$("#activities_searchList").val("");
		$( "#activities_searchList" ).trigger( "keyup" );
		$("#activities_searchList").focus();
		*/
		var start_date = $(".activity #start_dateInput").val();
		var end_date = $(".activity #end_dateInput").val();
		var user_id = "all";

		this.reloadActivities(user_id, moment(start_date).format("YYYY-MM-DD"), moment(end_date).format("YYYY-MM-DD"));
	},
});
function notesCleanUp(activity) {
	//notes clean up
	//if (activity.activity_category=="Notes") {
	activity = activity.replaceAll('<p class="MsoNormal"', '<p');
	activity = activity.replaceAll('<p style="mso-outline-level:1"', '<p');
	activity = activity.replaceAll('class="MsoNormal"', '');
	activity = activity.replaceAll('class="MsoNormal" style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
	activity = activity.replaceAll('style="margin-bottom:0in;margin-bottom:.0001pt;line-height:', '');
	activity = activity.replaceAll('normal">', '>');
	activity = activity.replaceAll('/p><br><p', '/p><p');
	activity = activity.replaceAll('/p>\r\n<br>\r\n<p', '/p><p');
	while (activity.indexOf('  ') > -1) {
		activity = activity.replaceAll('  ', ' ');
	}
	while (activity.indexOf('<br><br>') > -1) {
		activity = activity.replaceAll('<br><br>', '<br>');
	}
	activity = activity.replaceAll('<br><br>', '<br>');
	while (activity.indexOf('<br>\r\n<br>') > -1) {
		activity = activity.replaceAll('<br>\r\n<br>', '<br>');
	}
	while (activity.indexOf('<br> <br>') > -1) {
		activity = activity.replaceAll('<br> <br>', '<br>');
	}
	activity = activity.replaceAll("<p></p>", "");
	activity = activity.replaceAll("<p>\r\n</p>", "");
	activity = activity.replaceAll("/p><br> <p", "/p><p");
	activity = activity.replaceAll('<p><span style="color:#1F497D">&nbsp;</span></p>', '');
	while (activity.indexOf('\r\n\r\n') > -1) {
		activity = activity.replaceAll('\r\n\r\n', '\r\n');
	}

	//color
	activity = activity.replaceAll('color:#1F497D', 'color:white');
	activity = activity.replaceAll('background:#FFEB9C', 'background:#ff9800');

	//}

	return activity;
}