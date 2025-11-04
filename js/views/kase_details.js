window.kase_view = Backbone.View.extend({

	tagName: "div", // Not required since 'div' is the default if no el or tagName specified

	initialize: function () {
		_.bindAll(this);
	},
	events: {
		"mouseover .pills": "hideKpanel",
		"mouseover #activity_kpanel_holder": "showKpanel",
		"mouseout #kpanel_pill": "hideKpanel",
		"click #dash": "showDash",
		"click #parties": "showParties",
		"click .accident_link": "showAccident",
		"click .financial_link": "showFinancial"
	},
	render: function () {
		//make sure we have an id, even if it's empty
		var self = this;

		if (typeof this.template != "function") {
			var view = "kase_view";
			var extension = "php";
			this.model.set("holder", "kase_view");
			loadTemplate(view, extension, this);
			return "";
		}

		if (typeof this.model.get("case_id") == "undefined") {
			this.model.set("case_id", -1);
		}
		//no matter what, this _is_ the current case
		current_case_id = this.model.get("case_id");

		var kase_type = this.model.get("case_type");
		var kase_sub_type = this.model.get("case_sub_type");
		var blnWCAB = isWCAB(kase_type);
		this.model.set("blnWCAB", blnWCAB);

		var blnSS = (kase_type.indexOf("social_security") == 0 || kase_type == "SS");
		this.model.set("blnSS", blnSS);

		if (blnWCAB) {
			if (kase_type.indexOf("WCAB_Defense") < 0) {
				this.model.set("case_type", "WCAB");
				kase_type = "WCAB";
			}
		}
		mymodel = this.model.toJSON();
		var injury_id = this.model.get("id");
		var case_id = this.model.get("case_id");
		mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#injury/' + case_id + "/" + injury_id + '" class="injury" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.97em">Injury</span></a></li>';
		//not WCAB
		if (blnPiReady) {
			//$("#financial_pill").show();
			if (!mymodel.blnWCAB && !mymodel.blnSS) {
				mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a class="accident_link" style="color:#FFFFFF; padding:1px; padding-left:2px;cursor:pointer"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.97em">Accident</span></a></li>';
				/*
				<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a id="financial" class="financial_link" style="color:#FFFFFF; padding:1px; padding-left:2px; cursor: pointer"><!--<i class="glyphicon glyphicon-usd" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">Financial</span></a></li>
				*/
			}
			if (mymodel.blnSS) {
				/*
				mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#disability/' + case_id + "/" + injury_id + '" class="accident" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">Disability</span></a></li>';
				*/
				mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#disabilities/' + case_id + '" class="accident" style="color:#FFFFFF; padding:1px; padding-left:2px;"><span style="font-size:1.01em">Claims</span></a></li>';
			}
		}

		if (kase_type == "civil") {
			mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#personal_injury/' + case_id + '" class="accident" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">Civil</span></a></li><li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a id="financial" class="financial" style="color:#7077A0; padding:1px; padding-left:2px; cursor: pointer"><!--<i class="glyphicon glyphicon-usd" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">Damages</span></a></li>';
		}
		if (kase_type == "employment_law") {
			mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#employment_law/' + case_id + '" class="accident" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">COA</span></a></li>';
		}
		if (kase_type == "immigration") {
			mymodel.injury_link = '<li style="background:url(img/glass_calendar.png) left top; width:96px" class="pills pill_color navpill"><a href="#injury/' + case_id + "/" + injury_id + '" class="injury" style="color:#FFFFFF; padding:1px; padding-left:2px;"><!--<i class="glyphicon glyphicon-warning-sign" style="font-size:0.85em">&nbsp;</i>--><span style="font-size:0.90em">Details</span></a></li>';
		}
		$(this.el).html(this.template(mymodel));

		if (this.model.get("case_id") < 0) {
			$('#kase_header', this.el).html(new kase_header_view({ model: this.model }).render().el);
		} else {
			/*
			var kase_dois = dois.where({case_id: this.model.get("case_id")});
			if (kase_dois.length > 0) {
				//right away
				$('#kase_header', this.el).html(new kase_summary_view({model:this.model}).render().el);
			} else {
				*/
			//might not be in dois yet
			var kase_dois = new KaseInjuryCollection({ case_id: self.model.get("case_id") });
			kase_dois.fetch({
				success: function (kase_dois) {
					if (kase_dois.length > 0) {
						dois.add(kase_dois.toJSON(), { merge: true });
					}
					self.model.set("kase_dois", kase_dois);
					$('#kase_header', this.el).html(new kase_summary_view({ model: self.model }).render().el);
				}
			});
			//}
		}

		this.model.set("editing", false);

		if (this.model.get("case_id") < 0) {
			//remove the tabs
			setTimeout(function () {
				$("#kase_nav").fadeOut();
			}, 500);
			//editing mode right away
			setTimeout(function () {
				//$(".kase .edit").trigger("click"); 
				self.toggleEdit();
			}, 600);
			setTimeout(
				function () {
					$("#venueInput").focus();
					$('#venueInput').click();
				}, 700);

			setTimeout("gridsterIt(1)", 1);
		}
		if (this.model.get("case_id") > 0) {
			var case_id = this.model.get("case_id");
			//check on any overdue tasks
			setTimeout(function () {
				var url = 'api/overduekasetaskscount/' + case_id;
				$.ajax({
					url: url,
					type: 'GET',
					dataType: "json",
					success: function (data) {
						if (data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else { // If not
							//need notification

							if (data.count > 0) {
								var the_count = maxHundred(data.count);
								$("#overdue_kase_tasks_indicator").html(the_count);
								$("#overdue_kase_tasks_indicator").fadeIn();
							}
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						// report error
						console.log(errorThrown);
					}
				});
			}, 1111);

			setTimeout(function () {
				var url = 'api/kasekinvoicescount/' + case_id;
				$.ajax({
					url: url,
					type: 'GET',
					dataType: "json",
					success: function (data) {
						if (data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else { // If not
							//need notification

							if (data.count > 0) {
								var the_count = maxHundred(data.count);
								$("#kase_invoices_indicator").html(the_count);
								$("#kase_invoices_indicator").fadeIn();
							}
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						// report error
						console.log(errorThrown);
					}
				});

				if (blnWCAB) {
					var bodyparts = new BodyPartsCollection([], { injury_id: self.model.get("id"), case_id: case_id, case_uuid: self.model.get("case_uuid") });
					bodyparts.fetch({
						success: function (data) {
							if (data.length == 0) {
								$("#kase_nav .injury").css("background", "red");
								$("#kase_nav .injury").attr("title", "No Bodyparts on this Case");
							}
						}
					});
				}
			}, 1771);
		}

		return this;
	},
	showDash: function () {
		//#kase/<%=case_id %>
		var case_id = this.model.get("case_id");
		window.history.replaceState(null, null, "#kase/" + case_id);
		app.navigate("kase/" + case_id, { trigger: false });
		window.Router.prototype.dashboardKase(case_id);
	},
	showParties: function () {
		//#parties/<%=case_id %>
		var case_id = this.model.get("case_id");
		window.history.replaceState(null, null, "#parties/" + case_id);
		app.navigate("parties/" + case_id, { trigger: false });
		window.Router.prototype.listParties(case_id);
	},
	showAccident: function () {
		var case_id = this.model.get("case_id");
		window.history.replaceState(null, null, "#personal_injury/" + case_id);
		app.navigate("personal_injury/" + case_id, { trigger: false });
		window.Router.prototype.kasePersonalInjury(case_id);
	},
	showFinancial: function () {
		//#personal_injury_financial/' + case_id + '
		var case_id = this.model.get("case_id");
		window.history.replaceState(null, null, "#personal_injury_financial/" + case_id);
		app.navigate("personal_injury_financial/" + case_id, { trigger: false });
		window.Router.prototype.kaseFinancial(case_id);
	},
	showKpanel: function (event) {
		//removed per thomas 7/5/2018
		//$("#kpanel_pill").show();
	},
	hideKpanel: function (event) {
		//$("#kpanel_pill").hide();
	},
	toggleEdit: function (e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}

		//get all the editing fields, and toggle them back
		$(".kase .editing").toggleClass("hidden");
		$(".kase .span_class").removeClass("editing");
		$(".kase .input_class").removeClass("editing");

		$(".kase .span_class").toggleClass("hidden");
		$(".kase .input_class").toggleClass("hidden");
		$(".kase .input_holder").toggleClass("hidden");

		$(".button_row.kase").toggleClass("hidden");
		$(".edit_row.kase").toggleClass("hidden");

		$(".applicant .editing").toggleClass("hidden");
		$(".applicant .span_class").removeClass("editing");
		$(".applicant .input_class").removeClass("editing");

		$(".applicant .span_class").toggleClass("hidden");
		$(".applicant .input_class").toggleClass("hidden");
		$(".applicant .input_holder").toggleClass("hidden");

		$(".button_row.applicant").toggleClass("hidden");
		$(".edit_row.applicant").toggleClass("hidden");
	}
});
var homemedical;
window.new_kase_view = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		"change #case_typeInput": "piChange",
		"change #case_typeInput": "typeChange",
		"change #case_statusInput": "statusChange",
		"blur .kase .input_class": "autoSave",
		"blur .intake_notes": "autoSaveNote",
		"click #intake_event": "newEvent",
		"click #intake_task": "newTask",
		"change #injury_typeInput": "injuryTypeChange",
		"change #representingInput": "representingChange",
		"click #third_partyInput": "addThirdPartyInstructions",
		"click .claims": "inhouseClaim",
		"click .yes_no_link": "setInHouse",
		"click .manage_sub_type": "manageSubType",
		"click .manage_status": "manageStatus",
		"click .manage_status1": "manageStatus1",
		"click .manage_status2": "manageStatus2",
		"change #sub_in": "showSubDates",
		"click #ssn_intake_button": "showSSN",
		"click #intake_quick": "changeNoteSubject",
		"click #special_instructions_button": "showSpecialInstructions"
	},
	statusChange: function () {
		//alert("here");
		if (customer_id == 1121) {
			//$("#sub_status_td").html('');
			//$("#sub_status_td").html('<select name="case_substatusInput" id="case_substatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;"><option class="pi_substatus_option" value="Money to Come">Money to Come</option><option class="pi_substatus_option" value="Closed">Closed</option><option class="pi_substatus_option" value="Deceased">Deceased</option><option class="pi_substatus_option" value="Denied Claim">Denied Claim</option><option class="pi_substatus_option" value="Dropped">Dropped</option><option class="pi_substatus_option" value="Offer Made">Offer Made</option><option class="pi_substatus_option" value="Open Active">Open Active</option><option class="pi_substatus_option" value="PI-UM Case">PI-UM Case</option><option class="pi_substatus_option" value="PI-Pre Lit">PI-Pre Lit</option><option class="pi_substatus_option" value="PI-Demand Prep">PI-Demand Prep</option><option class="pi_substatus_option" value="PI-Pending Settlement">PI-Pending Settlement</option><option class="pi_substatus_option" value="PI-Settled">PI-Settled</option><option class="pi_substatus_option" value="Still Treating">Still Treating</option><option class="pi_substatus_option" value="Sub Out/Lien Filed">Sub Out/Lien Filed</option><option class="pi_substatus_option" value="Sub Out/without Lien">PI-Sub Out/without Lien</option><option class="pi_substatus_option" value="Trial Set">Trial Set</option><option class="pi_substatus_option" value="XX Dr Set">XX Dr Set</option></select>');
		}
	},
	render: function () {
		console.log('new_kase_view template call');
		var tempp = document.cookie;
		// console.log(tempp);
		tempp = tempp.split('; ');
		var scndtemp;
		var stop = 0;
		tempp.forEach(element => {
			if (stop == 0) {
				// console.log(element);
				scndtemp = element.split('=');
				// console.log(scndtemp);
				if (scndtemp[0] == "changes_need_render_new_kase_view") {
					changes_need_render_new_kase_view = scndtemp[1];
					stop = 1;
				}
			}
		});
		// console.log(tempp);


		if (typeof this.template != "function" || changes_need_render_new_kase_view == 1) {
			// console.log('in if 1');
			document.cookie = "changes_need_render_new_kase_view=0";
			if (typeof this.model.get("holder") == undefined) {
				this.model.set("holder", "");
			}
			if (this.model.get("holder") == "") {
				//default
				this.model.set("holder", "myModalBody");
			}
			var view = "new_kase_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		var mymodel = this.model.toJSON();

		//filing_date
		if (typeof mymodel.filing_date != "undefined") {
			if (mymodel.filing_date == "0000-00-00") {
				mymodel.filing_date = "";
			}
		} else {
			mymodel.filing_date = "";
		}
		//terminated_date
		if (mymodel.terminated_date == "0000-00-00" || mymodel.terminated_date == "12/31/1969") {
			mymodel.terminated_date = "";
		}
		var kase_type = this.model.get("case_type");
		var kase_sub_type = this.model.get("case_sub_type");
		var blnWCAB = isWCAB(kase_type);
		this.model.set("blnWCAB", blnWCAB);
		//var blnImm = (this.model.get("kase_type") == "immigration");
		//this.model.set("blnImm", blnImm);

		//case name
		if (mymodel.case_name !== "") {
			mymodel.name = mymodel.case_name;
		}
		//case_number. legacy
		if (blnWCAB) {
			if (mymodel.case_number != "" && mymodel.file_number == "") {
				mymodel.file_number = mymodel.case_number;
				mymodel.case_number = "";
			}
		} else {
			if (mymodel.case_number == "" && mymodel.file_number != "") {
				mymodel.case_number = mymodel.file_number;
			}
		}

		//adjust injury type
		mymodel.representing = "";
		if (mymodel.injury_type != "" && mymodel.injury_type != null) {
			var arrInjury = mymodel.injury_type.split("|");
			mymodel.injury_type = arrInjury[0];
			if (arrInjury.length == 2) {
				mymodel.representing = arrInjury[1];
			}
		}

		//break out case description
		var case_description = mymodel.case_description;
		mymodel.suit = "";
		mymodel.jurisdiction = "";
		mymodel.case_note = "";

		self.model.set("sub_in_date", "");
		self.model.set("sub_out_date", "");
		mymodel.sub_in_date = "";
		mymodel.sub_out_date = "";
		if (case_description != "") {
			var json_description = JSON.parse(case_description);
			mymodel.suit = json_description.suit;
			mymodel.jurisdiction = json_description.jurisdiction;
			mymodel.case_note = json_description.case_note;
			var sub_in_date = "";

			mymodel.sub_in_date = json_description.sub_in_date;
			mymodel.sub_out_date = json_description.sub_out_date;

			self.model.set("suit", mymodel.suit);
			self.model.set("jurisdiction", mymodel.jurisdiction);
			self.model.set("case_note", mymodel.case_note);

			self.model.set("sub_in_date", mymodel.sub_in_date);
			self.model.set("sub_out_date", mymodel.sub_out_date);
		}

		var special_instructions = this.model.get("special_instructions");
		if (special_instructions == "undefined") {
			special_instructions = "";
			this.model.set("special_instructions", "");
		}
		$(this.el).html(this.template(mymodel));

		setTimeout(function () {
			//sub in
			if (document.getElementById("sub_in") != null) {
				if (document.getElementById("sub_in").checked) {
					$("#sub_dates").fadeIn();
				}
			}

			var blnEditMode = ($("#kase_form #table_id").val() != -1 && $("#kase_form #table_id").val() != "");

			var theme_3 = {
				theme: "kase",
				tokenLimit: 1,
				onAdd: function (item) {
					if (customer_id == 1064 && this.context.id.indexOf("attorney") > -1) {
						//only per thomas 03/11/2019
						//move the value to the span
						var spanId = this.context.id.replace("Input", "Span");
						$("#" + spanId).html(item.name.capitalizeAllWords());

						//if we are in edit mode
						//if we are not admin
						if (blnEditMode) {
							if (!blnAdmin) {
								var divId = this.context.id.replace("Input", "_holder");
								$("#" + divId).hide();
								$("#" + spanId).show();
							}
						}
					}

					if (document.location.hash.indexOf("#intake") == 0) {
						//trigger a blur to save it
						$("#" + this.context.id).trigger("blur");
					}
				}
			};
			$("#supervising_attorneyInput").tokenInput("api/attorney", theme_3);
			if (self.model.get("supervising_attorney") != "") {
				if (self.model.get("attorney_full_name") == "") {
					var the_attorney = worker_searches.findWhere({ nickname: self.model.get("supervising_attorney") });
					if (typeof the_attorney != "undefined") {
						the_attorney = the_attorney.toJSON();
						the_attorney = the_attorney.user_name.toLowerCase().capitalizeWords();

						self.model.set("supervising_attorney_full_name", the_attorney);
					}
				}
				$("#supervising_attorneyInput").tokenInput("add", { id: self.model.get("supervising_attorney"), name: self.model.get("supervising_attorney_full_name") });
			}

			$("#attorneyInput").tokenInput("api/attorney", theme_3);
			if (self.model.get("attorney") != "") {
				if (self.model.get("attorney_full_name") == "") {
					var the_attorney = worker_searches.findWhere({ nickname: self.model.get("attorney") })
					if (typeof the_attorney != "undefined") {
						the_attorney = the_attorney.toJSON();
					}
					if (typeof the_attorney != "undefined") {
						the_attorney = the_attorney.user_name.toLowerCase().capitalizeWords();

						self.model.set("attorney_full_name", the_attorney);
					}
				}
				$("#attorneyInput").tokenInput("add", { id: self.model.get("attorney"), name: self.model.get("attorney_full_name") });
			}

			$("#workerInput").tokenInput("api/user", theme_3);
			if (self.model.get("worker") != "") {
				/*
				if (self.model.get("worker_full_name")==null) {
					self.model.set("worker_full_name", self.model.get("worker"));
				}
				*/
				if (self.model.get("worker_full_name") == "") {
					var worker_find = worker_searches.findWhere({ nickname: self.model.get("worker") });
					if (typeof worker_find != "undefined") {
						var the_worker = worker_find.toJSON();
						if (typeof the_worker != "undefined") {
							the_worker = the_worker.user_name.toLowerCase().capitalizeWords();

							self.model.set("worker_full_name", the_worker);
						}
					}
				}
				if (self.model.get("worker_full_name") != "") {
					$("#workerInput").tokenInput("add", { id: self.model.get("worker"), name: self.model.get("worker_full_name") });
				}
			}

			$("#venueInput").focus();
			$('.kase .date_input').datetimepicker({
				timepicker: false,
				format: 'm/d/Y',
				mask: false,
				onChangeDateTime: function (dp, $input) {
					//alert($input.val());
				}
			});

			//editable
			//if (self.model.get("case_editable")=="Y") {
			$(".kase #case_numberInput").toggleClass("hidden");
			$(".kase #case_numberSpan").toggleClass("hidden");
			//}

			//does it have a homemedical
			if (self.model.get("case_id") != "") {
				var homemedicals = new HomeMedicalCollection({ case_id: self.model.get("case_id") });
				homemedicals.fetch({
					success: function (homemedicals) {
						if (homemedicals.length > 0) {
							homemedical = homemedicals.models[0];
							homemedical.case_id = self.model.get("case_id");
							if (homemedical.get("recommended_by") != "" || homemedical.get("provider_name") != "") {
								openHomeMedical(homemedicals.models[0].id);
							}
						}
					}
				});

				if (self.model.get("special_instructions") != "") {
					openSpecialInstructions();
				}
			}
			var blnWCAB = self.model.get("blnWCAB");
			var blnImm = (self.model.get("kase_type") == "immigration");
			var blnSSN = (self.model.get("kase_type") == "social_security");
			//injury_type
			if (mymodel.injury_type != null) {
				$("#injury_typeInput").val(mymodel.injury_type);
			}
			$("#representingInput").val(mymodel.representing);
			if (!blnImm) {
				$("#representingSpan").html(mymodel.representing.capitalize());
			} else {
				if (mymodel.representing == "plaintiff") {
					$("#representingSpan").html("Applicant");
				}
			}

			if (!blnWCAB) {
				$(".wcab_only").hide();

				if (mymodel.representing == "") {
					$("#representingInput").fadeIn(function () {
						$("#representingInput").css("border", "2px solid red");
					});
				}
				// && customer_id != "1121"
				if (mymodel.injury_type == "") {
					if (self.model.get("kase_type") != "immigration") {
						var personal_injury = new PersonalInjury({ "case_id": current_case_id });

						personal_injury.fetch({
							success: function (personal_injury) {
								//console.log(personal_injury.toJSON());
								$("#kase_injury_description").html(personal_injury.toJSON().personal_injury_description);
								$("#kase_injury_description_holder").show();
								$("#injury_typeInput").css("border", "2px solid red");
							}
						});

						alert("Please set the Injury Type and the Representing fields");
					}
				}


				//open the special instructions section for non-wcab cases
				openSpecialInstructions();

				$("#suit").val(self.model.get("suit"));
				$("#jurisdiction").val(self.model.get("jurisdiction"));
				$("#case_note").val(self.model.get("case_note"));

				//for immigration, change the injury type drop down
				if (blnImm) {
					injury_types = immigration_injury_options;
					$("#injury_typeInput").html(injury_types);
					var arrInjuryType = self.model.get("injury_type").split("|");
					$("#injury_typeInput").val(arrInjuryType[0]);

					//representing
					$("#representingInput").html(immigration_representing_options);
					if (arrInjuryType.length > 1) {
						$("#representingInput").val(arrInjuryType[1]);
					}
					//case_number_label
					$("#case_number_label").html("Alien #");
				}
				if (!blnImm && !blnSSN) {
					$("#case_dateInput").css("width", "110px");
					$("#filing_date_holder").show();
				}
				setTimeout(
					function () {
						if (blnSSN) {
							self.openSSN();
						}
					}, 600);
			}
		}, 700);

		setTimeout(function () {
			var case_type = "";
			if ($("#case_typeInput").length > 0) {
				case_type = $("#case_typeInput").val();
			}
			if (case_type == "") {
				$("#pi_type_row").hide();
				$("#pi_representing_row").hide();
				$("#modal_save_holder").css("visibility", "hidden");
				//$("#file_number_row").hide();
			} else {
				var blnWCAB = self.model.get("blnWCAB");
				//however if already filled in
				case_type = case_type.replaceAll("_", " ");
				if (case_type == "NewPI" || case_type == "Personal Injury (UM)") {
					case_type = "personal injury";
					if (customer_id == 1121 && case_type != "social security" && case_type == "Personal Injury (UM)" || customer_id == 1121 && case_type != "social security" && case_type == "NewPI") {
						$("#sub_status_td").html('<select name="case_substatusInput" id="case_substatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;"><option class="pi_substatus_option" value="Money to Come">Money to Come</option><option class="pi_substatus_option" value="Closed">Closed</option><option class="pi_substatus_option" value="Deceased">Deceased</option><option class="pi_substatus_option" value="Denied Claim">Denied Claim</option><option class="pi_substatus_option" value="Dropped">Dropped</option><option class="pi_substatus_option" value="Offer Made">Offer Made</option><option class="pi_substatus_option" value="Open Active">Open Active</option><option class="pi_substatus_option" value="PI-UM Case">PI-UM Case</option><option class="pi_substatus_option" value="PI-Pre Lit">PI-Pre Lit</option><option class="pi_substatus_option" value="PI-Demand Prep">PI-Demand Prep</option><option class="pi_substatus_option" value="PI-Pending Settlement">PI-Pending Settlement</option><option class="pi_substatus_option" value="PI-Settled">PI-Settled</option><option class="pi_substatus_option" value="Still Treating">Still Treating</option><option class="pi_substatus_option" value="Sub Out/Lien Filed">Sub Out/Lien Filed</option><option class="pi_substatus_option" value="Sub Out/without Lien">PI-Sub Out/without Lien</option><option class="pi_substatus_option" value="Trial Set">Trial Set</option><option class="pi_substatus_option" value="XX Dr Set">XX Dr Set</option></select>');
					}
				}
				if (case_type == "WC") {
					case_type = "WCAB";
				}

				if (blnWCAB) {
					case_type = case_type.toUpperCase();
				} else {
					case_type = case_type.capitalizeWords(case_type);
				}
				$("#case_typeSpan").html(case_type);
				$("#case_typeInput").fadeOut(function () {
					$("#case_typeSpan").fadeIn();
				});

				if (!blnWCAB) {
					var representing = $("#representingInput").val();
					//special case lg law
					var blnHideRepresenting = true;
					var blnImm = (self.model.get("kase_type") == "immigration");
					if (blnImm) {
						//if (customer_id == 1089) {
						var arrInjuryType = self.model.get("injury_type").split("|");
						if (arrInjuryType.length > 1) {
							representing = arrInjuryType[1];
							if (representing == "defendant") {
								setTimeout(function () {
									//delay so that the new drop down is already generated
									$("#representingInput").val("");
									$("#representingSpan").html("");
								}, 707);
								blnHideRepresenting = false;
							}
						}
						//}
					}
					if (blnHideRepresenting) {
						$("#representingSpan").html(representing);
						$("#representingInput").fadeOut(function () {
							$("#representingSpan").fadeIn();
						});
					} else {
						$("#representingInput").css("border", "2px solid red");
					}
				}
			}

			//if (login_user_id==31) {
			//if (blnAdmin) {
			$(".manage_status").toggleClass("hidden");
			//}
			//}
		}, 500);
		if (self.model.get("id") == -1) {
			setTimeout(function () {
				$(".kase_fields").hide();
				$("#actual_case_label").hide();

				if ($("#case_typeInput").length > 0) {
					if (document.getElementById("case_typeInput").options.length > 1) {
						$("#case_typeInput").val("");
					} else {
						$("#case_typeInput").trigger("change");
					}
				}

				if ($("#case_statusInput").length > 0) {
					//cannot use deleted status				
					var status_options = document.getElementById("case_statusInput").options;
					var arrLength = status_options.length;
					for (var i = 0; i < arrLength; i++) {
						var option = status_options[i];
						var option_value = option.value;
						if (option_value != "") {
							if (arrDeletedKaseStatus.indexOf(option_value) > -1) {
								status_options[i].disabled = true;
							}
						}
					}
				}
				if ($("#case_substatusInput").length > 0) {
					var status_options = document.getElementById("case_substatusInput").options;
					var arrLength = status_options.length;
					for (var i = 0; i < arrLength; i++) {
						var option = status_options[i];
						var option_value = option.value;
						if (option_value != "") {
							if (arrDeletedKaseStatus.indexOf(option_value) > -1) {
								status_options[i].disabled = true;
							}
						}
					}
				}
				if ($("#case_subsubstatusInput").length > 0) {
					var status_options = document.getElementById("case_subsubstatusInput").options;
					var arrLength = status_options.length;
					for (var i = 0; i < arrLength; i++) {
						var option = status_options[i];
						var option_value = option.value;
						if (option_value != "") {
							if (arrDeletedKaseStatus.indexOf(option_value) > -1) {
								status_options[i].disabled = true;
							}
						}
					}
				}
				//intake screen?
				if (typeof self.model.get("intake_request") != "undefined") {
					if (self.model.get("intake_request")) {
						$("#intake_title").show();
						$(".case_name_holder").show();
						$("#case_name_label").html("<span style='font-size:1.3em; font-weight:bold'>Case Info</span>");

						$("#content").css("background-image", "url('img/glass_edit_header_new.png')");
						$("#content").css("color", "white");
						$("#content").css("height", $(document).height() + "px");

						$("#intake_client_holder").show();
						$("#intake_top_center_holder").css("margin-top", "50px");

						var the_height = $("#new_kase_field_table").css("height");
						$("#intake_bottom_left_holder").css("margin-top", "25px");
						$("#intake_bottom_left_holder").css("height", the_height);

						$("#intake_bottom_center_holder").css("margin-top", "25px");
						$("#intake_bottom_center_holder").css("height", the_height);

						$("#intake_bottom_right_holder").css("margin-top", "25px");
						$("#intake_bottom_right_holder").css("height", the_height);
						//$("#intake_bottom_right_holder").css("background-image", "url('img/glass_edit_header_new.png')");

						$("#claim_holder").css("margin-top", "25px");

						$(".manage_status").hide();
						$("#case_statusInput").html('<option value="Intake" class="wcab_status_option" selected>Intake</option>');

						//remove some options
						/*
						for (var i = 0; i < 5; i++) {
							document.getElementById("case_typeInput").options[4].remove();
						}
						
						//for now, no personal injury
						//document.getElementById("case_typeInput").options[3].remove();
						document.getElementById("case_typeInput").options[1].text = "WC"
						*/
						$("#case_typeInput").html('<option value="">Select from List ...</option><option value="WCAB">Workers Comp</option><option value="NewPI">Personal Injury</option><option value="social_security">Social Security</option>');

						/*
						$("#case_typeInput").html('<option value="WCAB" selected="">WCAB</option>');
						$("#case_typeInput").trigger("change");
						*/
					}
				} else {
					self.model.set("intake_request", false);
				}

			}, 200);
		} else {
			setTimeout(function () {
				//trigger the change to hide/show, options
				$("#case_typeInput").trigger("change");

				//request from steve 3/24/2017, admin only
				if (!blnAdmin && customer_id == 1075) {
					$("#case_numberInput").hide();
					$("#file_numberInput").hide();

					$("#case_numberSpan").show();
					$("#file_numberSpan").show();

					$(".kase #case_numberSpan").toggleClass("hidden");
					//$(".kase #file_numberSpan").toggleClass("hidden");
				}
			}, 200);
		}

		return this;
	},
	autoSaveNote: function (event) {
		//!self.model.get("intake_request")
		if (!blnAutoSave) {
			return;
		}
		var element = event.currentTarget;

		$("#phone_intake_feedback_div").html("Autosaving...");

		//save all the fields for note
		var note = $("#intake_notes").val();
		var notes_id = $("#intake_notes_id").val();
		if (note != "") {
			formValues = "table_name=notes";
			formValues += "&case_id=" + current_case_id;

			formValues += "&noteInput=" + note;
			formValues += "&status=INTAKE";
			if (document.getElementById("intake_quick").checked) {
				formValues += "&type=quick";
			} else {
				formValues += "&type=phone intake";
			}
			//formValues += "&subject=Intake Notes";
			var subject = $("#intake_notes_subject").val();
			formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");

			//add a note
			var url = 'api/notes/add';
			if (notes_id != "") {
				url = 'api/notes/update';
				formValues += "&table_id=" + notes_id;
			}

			//return;
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
					if (data.success) {
						$("#intake_notes_id").val(data.id);
						$("#intake_bottom_right_holder #panel_title").css("color", "lime");

						$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
						setTimeout(function () {
							$("#phone_intake_feedback_div").html("");
							$("#intake_bottom_right_holder #panel_title").css("color", "white");
						}, 2500);

					}
				}
			});
		}
	},
	autoSave: function (event) {
		//!self.model.get("intake_request")
		if (!blnAutoSave) {
			return;
		}
		var element = event.currentTarget;

		if (element.id == "case_typeInput") {
			return;
		}
		$("#phone_intake_feedback_div").html("Autosaving...");

		var fieldname = element.id.replace("Input", "");
		var value = element.value;

		if (fieldname == "injury_type") {
			//compound field
			var representing = $("#representingInput").val();
			value += "|" + representing;
		}
		if (fieldname == "representing") {
			//compound field
			fieldname = "injury_type";
			var injury_type = $("#injury_typeInput").val();
			value = injury_type + "|" + value;
		}

		var url = "api/kase/field/update";
		var case_id = $("#kase_form #id").val();
		var formValues = "id=" + case_id + "&fieldname=" + fieldname + "&value=" + encodeURIComponent(value);
		var border = $("#kase_form #" + element.id).css("border");

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#kase_form #" + element.id).css("border", "2px solid lime");
					$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
					setTimeout(function () {
						$("#kase_form #" + element.id).css("border", border);
						$("#phone_intake_feedback_div").html("");
					}, 2500);
				}
			}
		});
	},
	newEvent: function (event) {
		event.preventDefault();

		if (current_case_id == -1) {
			return;
		}

		var arrHash = document.location.hash.split("/");
		var day_date = moment()._d.getTime();
		var element_id = "-1_" + current_case_id + "_" + day_date + "_" + day_date;

		composeEvent(element_id);
	},
	newTask: function (event) {
		event.preventDefault();

		composeTask();
	},
	typeChange:async function (event) {
		
		
		
		//correction status finish
		var self = this;

		var element = event.currentTarget;
		var element_id = element.id;

		var element_value = $("#" + element_id).val();
		var kase_type = element_value;
		$("#modal_save_holder").css("visibility", "visible");
		element_value = element_value.toLowerCase();
		var blnWCAB = isWCAB(kase_type);
		this.model.set("blnWCAB", blnWCAB);

		var blnImm = (element_value == "immigration");
		var blnSSN = (element_value == "social_security");
		this.model.set("blnImm", blnImm);

		var file_number = this.model.get("file_number");
		//case_number_label
		$("#case_number_label").html("Case Number");
		$("#case_numberInput").attr("placeholder", "Court Legal Number");

		if (self.model.get("intake_request")) {
			$('#claim_holder').html("");
			$('.intake_holder').html("");
			$("#intake_notes").val("");
		}

		if (blnWCAB) {
			$(".pi_substatus_option").hide();
			$(".wcab_substatus_option").show();
			$(".wcab_only_stat").show();
			//per thomas 3/21/2017
			//$("#venueInput").prop("required", true);
			$("#representingInput").prop("required", false);
			$("#injury_typeInput").prop("required", false);

			//case number is required, file number not
			//$("#case_numberInput").prop("required", true);
			//$("#file_numberInput").prop("required", false);

			$(".kase_fields").fadeIn();
			$(".wcab_only").fadeIn();
			$(".injury_fields").fadeOut();
			if (this.model.get("id") == -1) {
				//in case they are flip-flopping, unlikely
				file_number = file_number.replace("PI", "WC");
				$("#file_numberInput").val(file_number);
				this.model.set("file_number", file_number);
			}

			if (self.model.get("intake_request")) {
				$("#special_instructions_holder").hide();

				//show clients for all
				self.showClientScreen();
				self.showCarrierScreen();
				//show employer screen for wcab
				self.showEmployerScreen();
				self.showInjuryScreen();
				//a little delay
				$(".case_sub_status").hide();

				$("#intake_bottom_right_holder").show();
				$("#content").css("height", $(document).height() + "px");
			}
		} else {
			//start with standard injury types
			var injury_types = standard_injury_options;
			if ($("#case_typeInput").length > 0) {
				case_type = $("#case_typeInput").val();
			}
			if (case_type == "NewPI" || case_type == "Personal Injury (UM)") {
				case_type = "personal injury";
				if (customer_id == 1121 && case_type != "social security" && case_type == "Personal Injury (UM)" || customer_id == 1121 && case_type != "social security" && case_type == "NewPI") {
					$("#sub_status_td").html('<select name="case_substatusInput" id="case_substatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;"><option value="">Select from List</option><option class="pi_substatus_option" value="Money to Come">Money to Come</option><option class="pi_substatus_option" value="Closed">Closed</option><option class="pi_substatus_option" value="Deceased">Deceased</option><option class="pi_substatus_option" value="Denied Claim">Denied Claim</option><option class="pi_substatus_option" value="Dropped">Dropped</option><option class="pi_substatus_option" value="Offer Made">Offer Made</option><option class="pi_substatus_option" value="Open Active">Open Active</option><option class="pi_substatus_option" value="PI-UM Case">PI-UM Case</option><option class="pi_substatus_option" value="PI-Pre Lit">PI-Pre Lit</option><option class="pi_substatus_option" value="PI-Demand Prep">PI-Demand Prep</option><option class="pi_substatus_option" value="PI-Pending Settlement">PI-Pending Settlement</option><option class="pi_substatus_option" value="PI-Settled">PI-Settled</option><option class="pi_substatus_option" value="Still Treating">Still Treating</option><option class="pi_substatus_option" value="Sub Out/Lien Filed">Sub Out/Lien Filed</option><option class="pi_substatus_option" value="Sub Out/without Lien">PI-Sub Out/without Lien</option><option class="pi_substatus_option" value="Trial Set">Trial Set</option><option class="pi_substatus_option" value="XX Dr Set">XX Dr Set</option></select>');
					$("#sub_sub_status_td").html('<select name="case_subsubstatusInput" id="case_subsubstatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;"><option value="">Select from List</option><option class="pi_substatus_option" value="Money to Come">Money to Come</option><option class="pi_substatus_option" value="Closed">Closed</option><option class="pi_substatus_option" value="Deceased">Deceased</option><option class="pi_substatus_option" value="Denied Claim">Denied Claim</option><option class="pi_substatus_option" value="Dropped">Dropped</option><option class="pi_substatus_option" value="Offer Made">Offer Made</option><option class="pi_substatus_option" value="Open Active">Open Active</option><option class="pi_substatus_option" value="PI-UM Case">PI-UM Case</option><option class="pi_substatus_option" value="PI-Pre Lit">PI-Pre Lit</option><option class="pi_substatus_option" value="PI-Demand Prep">PI-Demand Prep</option><option class="pi_substatus_option" value="PI-Pending Settlement">PI-Pending Settlement</option><option class="pi_substatus_option" value="PI-Settled">PI-Settled</option><option class="pi_substatus_option" value="Still Treating">Still Treating</option><option class="pi_substatus_option" value="Sub Out/Lien Filed">Sub Out/Lien Filed</option><option class="pi_substatus_option" value="Sub Out/without Lien">PI-Sub Out/without Lien</option><option class="pi_substatus_option" value="Trial Set">Trial Set</option><option class="pi_substatus_option" value="XX Dr Set">XX Dr Set</option></select>');
				}
			}
			$("#case_substatusInput").show();
			$(".pi_substatus_option").show();
			if (customer_id == 1121 && case_type != "social security" && case_type == "Personal Injury (UM)" || customer_id == 1121 && case_type != "social security" && case_type == "NewPI") {
				$(".wcab_substatus_option").hide();
			}
			$("#venueInput").prop("required", false);
			$("#representingInput").prop("required", true);
			$("#injury_typeInput").prop("required", true);
			$(".injury_fields").fadeIn();
			$(".wcab_only").fadeOut();

			if (this.model.get("id") == -1) {
				$(".kase_fields").fadeOut();

				if (blnImm) {
					injury_types = immigration_injury_options;
					representing_opt = immigration_representing_options;
				} else {
					injury_types = standard_injury_options;
					representing_opt = standard_representing_options;
				}
				$("#representingInput").html(representing_opt);
				$("#injury_typeInput").html(injury_types);

				if (element_value == "social_security") {
					$("#disability_kase_option").show();
					$("#injury_typeInput").val("disability");
					document.getElementById("representingInput").options[1].text = "Claimant";
					$("#representingInput").val("plaintiff");
					$("#representingInput").trigger("change");
					file_number = file_number.replace("WC", "SS");

					if (self.model.get("intake_request")) {
						self.openSSN();
						$("#intake_top_right_holder").hide();
						self.renderInjuryScreen();
					}
				} else {
					if (blnImm) {
						//if (customer_id == 1089) {
						//lg law group per maria 9/19/2017
						$("#representingInput").css("border", "1px solid red");
						$("#injury_typeInput").css("border", "1px solid red");
						//case_number_label
						$("#case_number_label").html("Alien #");
						$("#case_numberInput").attr("placeholder", "Alien Number");
						/*} else {
							$("#representingInput").val("plaintiff");
							$("#representingInput").trigger("change");
							$("#injury_typeInput").css("border", "1px solid red");
						}
						*/
					} else {
						$("#injury_typeInput").val("general");
						$("#representingInput").css("border", "1px solid red");
						file_number = file_number.replace("WC", "PI");
					}
				}

				//$("#case_numberInput").val("");
				$("#file_numberInput").val(file_number);

				this.model.set("file_number", file_number);
			}
			if (!self.model.get("intake_request")) {
				openSpecialInstructions();
			}

			if (blnSSN && !self.model.get("intake_request")) {
				$("#representingInput").html('<option id="plaintiff" value="plaintiff" selected>Claimant</option>');
				setTimeout(
					function () {
						self.openSSN();
					}, 600);
			}
		}

		if (self.model.get("intake_request") && blnAutoSave) {
			makeSureIntakeSaved()
		}

		//correction status start
		/* var statusfilters = new StatusFilters();
		statusfilters.fetch({
			success: function (data) {
				
				var arrFilters = data.toJSON();
				//console.log(Object.keys(arrFilters)+" ERRRORRRR");
				var selected_status = $("#case_statusInput :selected");
				// console.log(selected_status);
				// console.log(selected_status[0].value);
				
				// Clear dropdownlist
				// var select_status_drop =document.getElementById("case_statusInput");
				$("#case_statusInput option").remove();
				// console.log(select_status_drop);

				$('#case_statusInput').append("<option value='' class='defaultselected'>Select from List</option>");
				Object.keys(arrFilters).map((e,i)=> {
					
					if(e!== "insert" && e!== "unique" && arrFilters[e].deleted != "Y"){
						console.log(arrFilters[e].status+" -- "+arrFilters[e].deleted);
						optionText = arrFilters[e].status;
						optionText = optionText.replace("'", "`"); 
						optionValue = arrFilters[e].status;
						optionValue = optionValue.replace("'", "`");
						optionLaw = arrFilters[e].law;
						if(selected_status[0].value == optionValue && selected_status[0].className == optionLaw+"_status_option"){ selected = "selected"; }else{selected = "";
					
					}
						$('#case_statusInput').append(`<option value="${optionValue}" class="${optionLaw}_status_option" ${selected}>${optionText}</option>`);
					}
				});
				//call_for_remove_drop_value0();
				
			}
		});



		//correction status start
		var statusfilters = new SubStatusFilters();
		statusfilters.fetch({
			success: function (data) {
				
				var arrFilters = data.toJSON();
				// console.log(Object.keys(arrFilters));
				// console.log(arrFilters);
				var selected_status = $("#case_substatusInput :selected");
				// console.log(selected_status);
				// console.log(selected_status[0].value);
				
				// Clear dropdownlist
				// var select_status_drop =document.getElementById("case_substatusInput");
				$("#case_substatusInput option").remove();
				// console.log(select_status_drop);

				$('#case_substatusInput').append("<option value='' class='defaultselected'>Select from List</option>");
				Object.keys(arrFilters).map((e,i)=> {
					if(e!== "insert" && e!== "unique" && arrFilters[e].deleted != "Y"){
						// console.log(arrFilters[e]);
						optionText = arrFilters[e].status;
						optionText = optionText.replace("'", "`"); 
						optionValue = arrFilters[e].status;
						optionValue = optionValue.replace("'", "`");
						optionLaw = arrFilters[e].law;
						//if(selected_status[0].value == optionValue && selected_status[0].className == optionLaw+"_substatus_option"){ selected = "selected"; }else{selected = "";
					
					//}
						$('#case_substatusInput').append(`<option value="${optionValue}" class="${optionLaw}_substatus_option" ${selected}>${optionText}</option>`);
					}
				});
				//call_for_remove_drop_value();
				
			}
		});

		//correction status start
		var statusfilters = new SubSubStatusFilters();
		statusfilters.fetch({
			success: function (data) {
				
				var arrFilters = data.toJSON();
				// console.log(Object.keys(arrFilters));
				// console.log(arrFilters);
				var selected_status = $("#case_subsubstatusInput :selected");
				// console.log(selected_status);
				// console.log(selected_status[0].value);
				
				// Clear dropdownlist
				// var select_status_drop =document.getElementById("case_subsubstatusInput");
				$("#case_subsubstatusInput option").remove();
				// console.log(select_status_drop);

				$('#case_subsubstatusInput').append("<option value='' class='defaultselected'>Select from List</option>");
				Object.keys(arrFilters).map((e,i)=> {
					if(e!== "insert" && e!== "unique" && arrFilters[e].deleted != "Y"){
						// console.log(arrFilters[e]);
						optionText = arrFilters[e].status;
						optionText = optionText.replace("'", "`"); 
						optionValue = arrFilters[e].status;
						optionValue = optionValue.replace("'", "`");
						optionLaw = arrFilters[e].law;
						//if(selected_status[0].value == optionValue && selected_status[0].className == optionLaw+"_subsubstatus_option"){ selected = "selected"; }else{selected = "";
					
					//}
						$('#case_subsubstatusInput').append(`<option value="${optionValue}" class="${optionLaw}_subsubstatus_option" ${selected}>${optionText}</option>`);
					}
				});
				//call_for_remove_drop_value1();
				
			}
		}); */
	},
	openSSN: function () {
		var self = this;
		setTimeout(function () {
			var claimmodel = new Backbone.Model();
			claimmodel.set("embedded", true);
			claimmodel.set("holder", "claim_holder");
			claimmodel.set("case_id", self.model.get("case_id"));
			//bring up the claim view form
			if (self.model.get("intake_request")) {
				$('#claim_holder').html(new claim_view({ model: claimmodel }).render().el);
				$("#special_instructions_holder").fadeOut(function () {
					$('#claim_holder').show();
					$("#claim_holder").css("width", "501px");
				});
				self.showPlaintiffScreen();
			} else {
				$('#claim_holder').html(new claim_view({ model: claimmodel }).render().el);
				$("#ssn_intake_button").show();
			}
		}, 1250);
	},
	showEmployerScreen: function () {
		var corp = new Corporation({ id: -1, type: "employer" });
		var data = new AdhocCollection([], { case_id: -1, corporation_id: -1 });

		var blnShowHeader = false;
		//if this is a new partie, the query will return partie_type values
		corp.set("partie", "Employer");

		corp.set("gridster_me", true);
		corp.set("show_buttons", true);
		corp.set("intake_screen", true);
		corp.set("kase_type", $("#case_typeInput").val());

		corp.set("holder", "intake_bottom_left_holder");
		$('#intake_bottom_left_holder').html(new partie_view({ model: corp, collection: data }).render().el);
		//$('#intake_bottom_left_holder').show();
		$('#intake_bottom_left_holder').css("display", "inline-block");
		//$("#intake_bottom_left_holder").css("background", "url(img/glass_edit_header_new.png)");
		$("#intake_bottom_left_holder").css("height", (Number($("#new_kase_field_table").css("height").replace("px", "")) + 500) + "px");
		$("#intake_bottom_left_holder").css("border-top", "1px solid white");
		/*
		setTimeout(function() {
			
		}, 700);
		*/
	},
	showInjuryScreen: function () {
		var data = new Injury({ case_id: "" });
		data.set("holder", "intake_top_right_holder");
		data.set("grid_it", true);
		data.set("employer_address", "");
		data.set("case_type", $("#case_typeInput").val());
		data.set("glass", "card_fade_4");
		data.set("holder", "intake_top_right_holder");
		data.set("intake_screen", true);

		$('#intake_top_right_holder').html(new injury_view({ model: data }).render().el);

		$("#intake_top_right_holder").css("height", $("#new_kase_field_table").css("height"))
		//$("#intake_top_right_holder").css("background", "url(img/glass_edit_header_new.png)");

		$("#intake_top_right_holder").show();
	},
	showPersonalInjuryScreen: function () {
		var data = new Injury({ case_id: "" });
		data.set("holder", "intake_top_right_holder");
		data.set("grid_it", true);
		data.set("employer_address", "");
		data.set("case_type", $("#case_typeInput").val());
		data.set("glass", "card_fade_4");
		data.set("intake_screen", true);

		var dashboard_pitype_view = window["personal_injury_general_view"];
		$('#intake_top_right_holder').html(new dashboard_pitype_view({ model: data }).render().el);

		$("#intake_top_right_holder").css("height", $("#new_kase_field_table").css("height"))
		//$("#intake_top_right_holder").css("background", "url(img/glass_edit_header_new.png)");

		$("#intake_top_right_holder").show();

		setTimeout(function () {
			$("#injury_form #case_id").val(current_case_id);
			$("#personal_injury_form #case_id").val(current_case_id);
			$("#person_form #case_id").val(current_case_id);
			$("#Employer_form #case_id").val(current_case_id);
			$("#Carrier_form #case_id").val(current_case_id);
			$("#Plaintiff_form #case_id").val(current_case_id);
		}, 1000);
	},
	renderInjuryScreen: function () {
		var data = new Injury({ case_id: "" });
		data.set("holder", "intake_bottom_left_holder");
		data.set("grid_it", true);
		data.set("employer_address", "");
		data.set("case_type", $("#case_typeInput").val());
		data.set("glass", "card_fade_4");
		data.set("intake_screen", true);

		$('#intake_bottom_left_holder').html(new injury_view({ model: data }).render().el);

		$("#intake_bottom_left_holder").css("height", $("#new_kase_field_table").css("height"))
		//$("#intake_bottom_left_holder").css("background", "url(img/glass_edit_header_new.png)");
		$("#claim_holder").css("margin-top", "50px");

		$("#intake_bottom_left_holder").hide();

		$("#claim_holder").css("width", "601px");

		setTimeout(function () {
			$("#intake_holder_bottom").css("top", (Number($("#new_kase_field_table").css("height").replace("px", "")) + 50) + "px");
			//$("#intake_bottom_right_holder").css("float", "none");
			$("#intake_bottom_right_holder").css("border", "none");
			$("#intake_bottom_right_holder").css("border-top", "1px solid white");
			$("#intake_bottom_right_holder").css("margin-left", "30px");

			$("#intake_bottom_right_holder").show();
			$("#content").css("height", $(document).height() + "px");

		}, 2000);
	},
	showCarrierScreen: function () {
		var carrier = new Corporation({ id: -1, type: "carrier" });
		var data = new AdhocCollection([], { case_id: -1, corporation_id: -1 });

		var blnShowHeader = false;
		//if this is a new partie, the query will return partie_type values
		carrier.set("partie", "Carrier");

		carrier.set("gridster_me", true);
		carrier.set("show_buttons", true);
		carrier.set("intake_screen", true);
		carrier.set("kase_type", $("#case_typeInput").val());

		carrier.set("holder", "intake_bottom_center_holder");
		$("#intake_bottom_center_holder").css("float", "right");
		$('#intake_bottom_center_holder').html(new partie_view({ model: carrier, collection: data }).render().el);
		$("#intake_bottom_center_holder").css("height", (Number($("#new_kase_field_table").css("height").replace("px", "")) + 500) + "px")
		//$("#intake_bottom_center_holder").css("background", "url(img/glass_edit_header_new.png)");
		$("#intake_holder_bottom").css("top", (Number($("#new_kase_field_table").css("height").replace("px", "")) + 50) + "px");
		$("#intake_holder_bottom").css("left", "-10px");
		$('#intake_bottom_center_holder').css("display", "inline-block");
		$('#intake_bottom_center_holder').css("border-top", "1px solid white");
		setTimeout(function () {
			//$(".partie .token-input-list-eams").toggleClass("hidden");
		}, 700);

	},
	showPlaintiffScreen: function () {
		var plaintiff = new Corporation({ id: -1, type: "plaintiff" });
		var data = new AdhocCollection([], { case_id: -1, plaintifforation_id: -1 });

		var blnShowHeader = false;
		//if this is a new partie, the query will return partie_type values
		plaintiff.set("partie", "Plaintiff");

		plaintiff.set("gridster_me", true);
		plaintiff.set("show_buttons", true);
		plaintiff.set("intake_screen", true);
		plaintiff.set("kase_type", $("#case_typeInput").val());

		plaintiff.set("holder", "intake_top_center_holder");
		$('#intake_top_center_holder').html(new partie_view({ model: plaintiff, collection: data }).render().el);
		//$("#intake_top_center_holder").css("background", "url(img/glass_edit_header_new.png)");
		$("#intake_top_center_holder").show();
		$("#intake_top_center_holder").css("margin-top", "50px");

		$("#kai_holder").hide();

		if ($("#case_typeInput").val() == "social_security") {
			setTimeout(function () {
				$("#partie_type_holder").html("Claimant");
			}, 700);
		}
	},
	showDefendantScreen: function () {
		var defendant = new Corporation({ id: -1, type: "defendant" });
		var data = new AdhocCollection([], { case_id: -1, defendantoration_id: -1 });

		var blnShowHeader = false;
		//if this is a new partie, the query will return partie_type values
		defendant.set("partie", "Defendant");

		defendant.set("gridster_me", true);
		defendant.set("show_buttons", true);
		defendant.set("intake_screen", true);
		defendant.set("kase_type", $("#case_typeInput").val());

		defendant.set("holder", "intake_top_center_holder");
		$('#intake_top_center_holder').html(new partie_view({ model: defendant, collection: data }).render().el);
		//$("#intake_bottom_left_holder").css("background", "url(img/glass_edit_header_new.png)");
		$("#intake_top_center_holder").show();
		$("#intake_top_center_holder").css("margin-top", "50px");

		$("#kai_holder").hide();
	},
	showClientScreen: function () {
		var person_model = new Person({ id: -1 });
		person_model.set("glass", "card_dark_1");
		person_model.set("holder", "intake_top_center_holder");
		person_model.set("injury_id", "");

		person_model.set("case_id", "");
		person_model.set("case_uuid", "");
		person_model.set("gridster_me", true);
		person_model.set("intake_screen", true);
		person_model.set("kase_type", "");
		person_model.set("applicant_label", "Client");

		$('#intake_top_center_holder').html(new person_view({ model: person_model }).render().el);
		$('#intake_top_center_holder').fadeIn();
	},
	showSubDates: function (event) {
		event.preventDefault();

		if (document.getElementById("sub_in").checked) {
			$("#sub_dates").fadeIn();
		} else {
			$("#sub_in_date").val("");
			$("#sub_out_date").val("");
			$("#sub_dates").fadeOut();
		}
	},
	showSSN: function (event) {
		event.preventDefault();
		$("#special_instructions_holder").fadeOut(function () {
			$('#claim_holder').show();
		});

		$("#ssn_intake_button").hide();
		$("#special_instructions_button").show();
	},
	showSpecialInstructions: function (event) {
		event.preventDefault();
		var blnIntake = (document.location.hash.indexOf("#intake") > -1);

		if (blnIntake) {
			return;
		}

		$('#claim_holder').fadeOut(function () {
			$("#special_instructions_holder").show();
		});

		$("#special_instructions_button").hide();
		$("#ssn_intake_button").show();
	},
	changeNoteSubject: function (event) {
		var element = event.currentTarget;
		if (element.checked) {
			//quick notes
			if ($("#intake_notes_subject").val() == "Phone Intake") {
				$("#intake_notes_subject").val("Quick Note");
			}
		} else {
			if ($("#intake_notes_subject").val() == "Quick Note") {
				$("#intake_notes_subject").val("Phone Intake");
			}
		}
	},
	injuryTypeChange: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var element_value = $("#" + element_id).val();
		$("#injury_typeInput").css("border", "");
		//representing
		var representing_value = $("#representingInput").val();

		if (element_value != "" && representing_value != "") {
			$(".kase_fields").fadeIn();
		}
		if (representing_value == "") {
			$("#representingInput").css("border", "1px solid red");
		}

		if (this.model.get("intake_request")) {
			setTimeout(function () {
				$("#injury_typeInput").trigger("blur");
			}, 1000);
		}
	},
	representingChange: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var element_value = $("#" + element_id).val();

		if (element_value != "") {
			$(".kase_fields").fadeIn(function () {
				$(".wcab_only").hide();
			});
			$("#" + element_id).css("border", "none");
		} else {
			$(".kase_fields").fadeOut();
			$("#" + element_id).css("border", "1px solid red");
		}

		if (this.model.get("intake_request")) {
			if ($("#representingInput").val() == "plaintiff") {
				this.showPlaintiffScreen();
			} else {
				this.showDefendantScreen();
			}
			var the_height = $("#new_kase_field_table").css("height");
			$("#intake_bottom_left_holder").css("margin-top", "25px");
			$("#intake_bottom_left_holder").css("height", the_height);

			$("#intake_bottom_center_holder").css("margin-top", "25px");
			$("#intake_bottom_center_holder").css("height", the_height);

			$("#intake_bottom_right_holder").css("margin-top", "25px");
			$("#intake_bottom_right_holder").css("height", the_height);
			//$("#intake_bottom_right_holder").css("background-image", "url('img/glass_edit_header_new.png')");

			$("#intake_bottom_right_holder").css("border-top", "1px solid white");
			$("#intake_bottom_right_holder").show();
			$("#content").css("height", $(document).height() + "px");

			var element_value = $("#case_typeInput").val();
			if (element_value != "social_security") {
				this.showCarrierScreen();
			}
			this.showPersonalInjuryScreen();

			//different layout
			$("#intake_bottom_center_holder").css("float", "none");
			document.getElementById("intake_bottom_center_holder").style.removeProperty("overflow");

			//blur it to autosave
			if (this.model.get("intake_request")) {
				setTimeout(function () {
					$("#representingInput").trigger("blur");
				}, 1000);
			}
		}
	},
	piChange: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;

		var element_value = $("#" + element_id).val();

		if (element_value == "NewPI" || element_value == "Personal Injury" || element_value == "Personal Injury (UM)") {
			$("#pi_type_row").fadeIn();
			$("#pi_representing_row").fadeIn();
			if (customer_id == 1121) {
				$("sub_status_td").html('<select name="case_substatusInput" id="case_substatusInput" class="kase input_class" style="width:450px; overflow-y: scroll;"><option class="pi_substatus_option" value="Money to Come">Money to Come</option><option class="pi_substatus_option" value="Closed">Closed</option><option class="pi_substatus_option" value="Deceased">Deceased</option><option class="pi_substatus_option" value="Denied Claim">Denied Claim</option><option class="pi_substatus_option" value="Dropped">Dropped</option><option class="pi_substatus_option" value="Offer Made">Offer Made</option><option class="pi_substatus_option" value="Open Active">Open Active</option><option class="pi_substatus_option" value="PI-UM Case">PI-UM Case</option><option class="pi_substatus_option" value="PI-Pre Lit">PI-Pre Lit</option><option class="pi_substatus_option" value="PI-Demand Prep">PI-Demand Prep</option><option class="pi_substatus_option" value="PI-Pending Settlement">PI-Pending Settlement</option><option class="pi_substatus_option" value="PI-Settled">PI-Settled</option><option class="pi_substatus_option" value="Still Treating">Still Treating</option><option class="pi_substatus_option" value="Sub Out/Lien Filed">Sub Out/Lien Filed</option><option class="pi_substatus_option" value="Sub Out/without Lien">PI-Sub Out/without Lien</option><option class="pi_substatus_option" value="Trial Set">Trial Set</option><option class="pi_substatus_option" value="XX Dr Set">XX Dr Set</option></select>');
			}
			//$("#file_number_row").fadeIn();
		} else {
			if ($("#pi_type_row").is(":visible")) {
				$("#pi_type_row").fadeOut();
				$("#pi_representing_row").fadeOut();
			} else {
				return;
			}
		}
	},
	inhouseClaim: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var blnElementChecked = (element.checked);
		//if the checkbox is checked, let's bring up the y/n for inhouse
		var inhouse_id = element_id.replace("Input", "InHouse");

		var third_party_instructions = "";
		switch (element.id) {
			case "third_partyInput":
				third_party_instructions = "Third Party Case";
				break;
			case "132aInput":
				third_party_instructions = "132a Case";
				break;
			case "seriousInput":
				third_party_instructions = "Serious and Willful Case";
				break;
			case "adaInput":
				third_party_instructions = "ADA Case";
				break;
			case "ssInput":
				third_party_instructions = "SS Case";
				break;
		}
		//clean up
		var special_instructions = $("#special_instructions").val();
		var arrInstructions = ["Third Party Case", "132a Case", "Serious and Willful Case", "ADA Case", "SS Case"];
		for (var i = 0; i < arrInstructions.length; i++) {
			the_instructions = arrInstructions[i];
			special_instructions = special_instructions.replace("\r\n" + the_instructions, "");
			special_instructions = special_instructions.replace(the_instructions, "");
		}
		$("#special_instructions").val(special_instructions);

		if (blnElementChecked) {
			//go through all the third_132
			var claims = $(".claims");
			var inhouse_divs = $(".inhouse_div");

			for (var i = 0; i < claims.length; i++) {
				if (claims[i].id != element_id) {
					claims[i].checked = false;
					inhouse_divs[i].style.display = "none";
				}
			}
			$("#" + inhouse_id).fadeIn();
			this.model.set("third_party_instructions", third_party_instructions);

			//add it to the special instructions

			if (special_instructions.indexOf(third_party_instructions) < 0) {
				//one time
				if (special_instructions != "") {
					special_instructions += "\r\n";
				}
				special_instructions += third_party_instructions;
				$("#special_instructions").val(special_instructions);
			}

		} else {
			$("#" + inhouse_id).fadeOut();
			this.model.set("third_party_instructions", "");
		}
	},
	setInHouse: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var choice_id = element_id.replace("InHouse" + element.innerHTML, "InHouseChoice");

		var third_party_instructions = "\nThird Party Case";

		if (element.innerHTML == "Y") {
			var otherhouse_id = element_id.replace("InHouseY", "InHouseN");
			var special_instructions = $("#special_instructions").val();
			special_instructions = special_instructions.replace(third_party_instructions, third_party_instructions + " (In House)");
			$("#special_instructions").val(special_instructions);
		} else {
			var special_instructions = $("#special_instructions").val();
			special_instructions = special_instructions.replace(" (In House)", "");
			$("#special_instructions").val(special_instructions);
			var otherhouse_id = element_id.replace("InHouseN", "InHouseY");
		}
		$("#" + otherhouse_id).removeClass("yes_no_selected");
		$("#" + element_id).addClass("yes_no_selected");
		$("#" + element_id + ".yes_no_selected").css("color", "#40FF40");
		$("#" + choice_id).val(element.innerHTML);

	},
	manageSubType: function (event) {
		//console.log(event);
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;

		var status_level = "subtype";
		
		$('.modal-dialog').animate({ width: 1050, marginLeft: "-500px" }, 1100, 'easeInSine',
			function () {
				//run this after animation
				/* if (status_level == "") {
					var subtypefilters = new SubTypeFilters();
				} else { */
					var subtypefilters = new SubTypeFilters();
					/* status_indicator = "subtype ";
				}  */
				//console.log(subtypefilters);

				subtypefilters.fetch({
					success: function (collection, response, options) {						
						//var mymodel = new Backbone.Model();
						var mymodel = new Backbone.Model({ status_level: status_level });
						$("#side_holder").show();
						$('#manage_status_holder').show();
						mymodel.set("holder", "manage_status_holder");
						//mymodel.set("status_level",status_level);
						$('#manage_status_holder').html(new kase_subtype_listing({ collection: collection, model: mymodel }).render().el);
					}
				});

				
			}
		);
	},
	manageStatus: function (event) {
		console.log(event);
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;

		var status_level = "";
		console.log(element.id.indexOf("sub"));

		if (element.id.indexOf("sub") > -1) {
			status_level = "sub";
		}

		$('.modal-dialog').animate({ width: 1050, marginLeft: "-500px" }, 1100, 'easeInSine',
			function () {
				//run this after animation
				if (status_level == "") {
					var statusfilters = new StatusFilters();
				} else {
					var statusfilters = new SubStatusFilters();
					status_indicator = "Sub ";
				}
				//console.log(statusfilters);

				statusfilters.fetch({
					success: function (data) {
						var mymodel = new Backbone.Model();
						$("#side_holder").show();
						$('#manage_status_holder').show();
						mymodel.set("holder", "manage_status_holder");
						mymodel.set("status_level", status_level);
						$('#manage_status_holder').html(new kase_status_listing({ collection: data, model: mymodel }).render().el);
					}
				});
			}
		);
	},
	manageStatus1: function (event) {

		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;

		var status_level = "";
		if (element.id.indexOf("sub") > -1) {
			status_level = "sub";
		}

		$('.modal-dialog').animate({ width: 1050, marginLeft: "-500px" }, 1100, 'easeInSine',
			function () {
				//run this after animation
				if (status_level == "") {
					var statusfilters = new SubStatusFilters();
					status_indicator = "Sub ";
				} else {
					var statusfilters = new SubStatusFilters();
					status_indicator = "Sub ";
				}

				statusfilters.fetch({
					success: function (data) {
						var mymodel = new Backbone.Model();
						$("#side_holder").show();
						$('#manage_status_holder').show();
						mymodel.set("holder", "manage_status_holder");
						mymodel.set("status_level", "Sub");
						$('#manage_status_holder').html(new kase_sub_status_listing({ collection: data, model: mymodel }).render().el);
					}
				});
			}
		);
	},
	manageStatus2: function (event) {

		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;

		var status_level = "";
		if (element.id.indexOf("sub") > -1) {
			status_level = "subsub";
		}

		$('.modal-dialog').animate({ width: 1050, marginLeft: "-500px" }, 1100, 'easeInSine',
			function () {
				//run this after animation
				if (status_level == "") {
					var statusfilters = new SubSubStatusFilters();
				} else {
					var statusfilters = new SubStatusFilters();
					status_indicator = "Sub ";
				}
				statusfilters.fetch({
					success: function (data) {
						var mymodel = new Backbone.Model();
						$("#side_holder").show();
						$('#manage_status_holder').show();
						mymodel.set("holder", "manage_status_holder");
						mymodel.set("status_level", status_level);
						$('#manage_status_holder').html(new kase_sub_sub_status_listing({ collection: data, model: mymodel }).render().el);
					}
				});
			}
		);
	}
});
window.kase_abstract_view = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		"click #new_note_button": "newNote",
		"click #new_partie": "newPartie",
		"click #show_loss": "showLossSummary",
		"click #kase_abstract_all_done": "doTimeouts"
	},
	render: function () {
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_abstract_holder");
			var view = "kase_abstract_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}

		var kase_type = this.model.get("case_type");
		var kase_sub_type = this.model.get("case_sub_type");
		//var kase_adj = this.model.get("adj_number");

		var blnWCAB = isWCAB(kase_type);
		var blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
		var blnImm = (kase_type == "immigration");
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnWCABDefense", blnWCABDefense);

		var mymodel = this.model.toJSON();

		var case_id = mymodel.case_id;
		if (case_id == "") {
			var arrID = document.location.hash.split("/");
			case_id = arrID[arrID.length - 1];
		}
		var kase = kases.findWhere({ case_id: case_id });
		kase.set("panel_title", mymodel.panel_title);
		kase.set("claims_display", mymodel.claims_display);
		kase.set("claims_values", mymodel.claims_values);
		kase.set("case_sub_type", mymodel.case_sub_type);

		if (typeof kase.get("claim_number") == "undefined") {
			kase.set("claim_number", "")
		}

		$(this.el).html(this.template({ model: mymodel, kase: kase, blnWCAB: mymodel.blnWCAB, panel_title: mymodel.panel_title, case_sub_type:mymodel.case_sub_type }));

		return this;
	},
	showLossSummary: function (event) {
		event.preventDefault();

		composeLossSummary();
	},
	doTimeouts: function () {


	// console.log(self.model.get("hide_upload"));
	// console.log(blnUploadDash);
		var self = this;

		if (typeof self.model.get("hide_upload") == "undefined") {
			self.model.set("hide_upload", false);
		}
		if (blnUploadDash && self.model.get("hide_upload") == false) {
			$('#message_attachments').html(new message_attach({ model: self.model }).render().el);

			setTimeout(function () {
				$("#message_attach_holder").css("width", "");
				$("#uploadifive-file_upload").css("font-size", "0.9em");
				$("#uploadifive-file_upload").css("color", "black");

			$(".message_attach_form #queue").hide();
				$(".message_attach_form #queue").css("height", "70px");
				$(".message_attach_form #queue").css("width", "250px");
				$(".message_attach_form #queue").css("background", "#EDEDED");
				$(".message_attach_form #queue").css("color", "black");
			}, 154);
		}
		if (self.model.get("hide_upload")) {
			$("#abstract_message_attach_holder").html("");
		}
		setTimeout(function () {
			$('#kase_abstract_holder').show();
		}, 722);

		//new note button
		if (document.location.hash.indexOf("#notes") == 0) {
			$("#new_note_button_holder").fadeIn();
		}
		/*
		if (blnLossSummary) {
			if (document.location.hash.indexOf("#payments/")==0) {
				$("#show_loss_holder").fadeIn();
			}
		}
		*/
	},
	newPartie: function (event) {
		event.preventDefault();
		document.location.href = "#parties/" + current_case_id + "/-1/new";
	},
	newNote: function (event) {
		event.preventDefault();
		$(".compose_new_note").trigger("click");
	}
});
window.kase_summary_view = Backbone.View.extend({
	tagName: "div", // Not required since 'div' is the default if no el or tagName specified

	initialize: function () {
	},
	events: {
		"click .kase_edit": "editKase",
		"click .mass_email": "massEmail",
		"click .settlement": "editSettlement",
		"click .kase_adj_number": "lookupADJ",
		"click .jetfile": "jetFile",
		"click .demographics_doi": "demographicDOI",
		"click #multi_demo_link": "demographicMultiDOI",
		"click .delete_doi": "confirmDeleteInjury",
		"click .demographics_doichoice": "releaseMultiDemoLink",
		"click .settlement_link": "showSettlement",
		"click .kase_accept": "acceptKase",
		"click .kase_reject": "rejectKase"
	},
	render: function () {
		var self = this;
		//before I render the summary, I need to get the dois for this kase
		var doi_label = 'No DOI';
		var arrDOIs = [];
		var arrSettles = [];
		var related_legend = "";
		this.model.set("included_dois", []);

		var kase_type = this.model.get("case_type");
		var kase_sub_type = this.model.get("case_sub_type");
		var blnWCAB = isWCAB(kase_type);

		//might be a pi
		if (this.model.get("personal_injury_date") != "" && !blnWCAB) {
			doi_label = "DOI:&nbsp;" + moment(this.model.get("personal_injury_date")).format("MM/DD/YYYY");
			var personal_injury_loss_date = this.model.get("personal_injury_loss_date");
			if (typeof personal_injury_loss_date != "undefined") {
				if (personal_injury_loss_date != "" && personal_injury_loss_date != "0000-00-00") {
					doi_label += "&nbsp;|&nbsp;DOL:&nbsp;" + moment(this.model.get("personal_injury_loss_date")).format("MM/DD/YYYY");
				} else {
					this.model.set("personal_injury_loss_date", "");
				}
			}
			var personal_statute_limitation = this.model.get("personal_statute_limitation");
			if (typeof personal_statute_limitation != "undefined") {
				if (personal_statute_limitation != "" && personal_statute_limitation != "0000-00-00") {
					doi_label += "&nbsp;|&nbsp;SOL:&nbsp;" + moment(this.model.get("personal_statute_limitation")).format("MM/DD/YYYY");
				} else {
					this.model.set("personal_statute_limitation", "");
				}
			}
			var settlement_border = "padding:2px";
			if (this.model.get("settlement_id") > 0 || this.model.get("fee_id") > 0) {
				settlement_border = "background: black; padding:2px";
			}
			doi_label += '&nbsp;<div style="display:inline-block; ' + settlement_border + '"><a class="settlement_link" title="Click to review settlement information" id="settlement_' + this.model.get("id") + '" style="cursor:pointer"><i class="glyphicon glyphicon-usd" style="color:#0F9"></i></a></div>';

			//multi dois on one demo
			//doi_label += '&nbsp;<input type="checkbox" id="demographicsdoichoice_' + doi.id + '" class="demographics_doichoice" title="Check this box to create demographics sheet including this DOI" />&nbsp;Demo';

			arrDOIs.push(doi_label);
		} else {
			//var kase_dois = dois.where({"case_id": String(this.model.get("case_id"))});
			//this.model.set("kase_dois", kase_dois);
			var kase_dois = new KaseInjuryCollection({ case_id: this.model.get("case_id") });
			kase_dois.set(this.model.get("kase_dois").toJSON());
			//make it into array for each
			kase_dois = kase_dois.toArray();
			kase_dois = kase_dois.unique();

			var kase_type = this.model.get("case_type");
			var kase_sub_type = this.model.get("case_sub_type");
			//alert(kase_type);
			self.model.set("doi_count", kase_dois.length);
			self.model.set("kase_type", kase_type);
			self.model.set("kase_sub_type", kase_sub_type);

			//if (kase_dois.length > 0 && kase_dois.length < 4) {
			var intCounter = 0;
			relatedCount = 0;
			_.each(kase_dois, function (doi) {
				var start_date = doi.get("start_date");
				var adj_number = doi.get("adj_number").toUpperCase();
				if (adj_number == null) {
					adj_number = "";
				}
				if (adj_number == "") {
					if (blnWCAB) {
						adj_number = "No ADJ";
					} else {
						adj_number = doi.get("case_number");
					}
				}
				if (adj_number == null) {
					adj_number = "";
				}
				//check adj
				if (adj_number.indexOf("ADJ") == 0) {
					//valid
					adj_number = "<span class='kase_adj_number' style='cursor:pointer; text-decoration:underline' title='Click to lookup ADJ on EAMS'>" + adj_number + "</span>";
				}
				var injury_status = doi.get("injury_status");
				switch (injury_status) {
					case "Accepted":
						injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_lightgreen.png' width='18px' height='18px' title='Accepted' /></div>";
						break;
					case "Denied":
						injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_red.png' width='18px' height='18px' title='Denied' /></div>";
						break;
					case "Pending":
						injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_orange.png' width='18px' height='18px' title='Pending' /></div>";
						break;
					case "Completed":
						injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;' title='Completed'><span style='background:white;color:green;width:18px;height:18px;text-align:center;display:inline-block'>&#10003;</span></div>";
						break;
				}

				adj_number = injury_status + adj_number;
				if (start_date != "" && start_date != "0000-00-00") {
					start_date = "DOI:&nbsp;" + moment(start_date).format("MM/DD/YYYY");
				} else {
					start_date = "No Injury Info";
					//however, this may be a legacy case
					var ct_dates = doi.get("ct_dates_note");
					if (ct_dates != "") {
						start_date = "<span style='background:red' title='Please re-enter CT dates'>" + ct_dates + "</span>";
					}
				}

				var statute_limitation = doi.get("statute_limitation"); //self.model.get("statute_limitation");

				if (typeof statute_limitation != "undefined") {
					if (statute_limitation != "" && statute_limitation != "0000-00-00") {
						statute_limitation = "&nbsp;|&nbsp;SOL:&nbsp;" + moment(statute_limitation).format("MM/DD/YYYY");
					} else {
						statute_limitation = "";
						self.model.set("statute_limitation", "");
					}
				}
				var main_case_id = doi.get("main_case_id");
				var blnRelatedCase = (main_case_id != current_case_id);
				var adj_style = "";
				if (blnRelatedCase) {
					relatedCount++;
					//onclick="document.location.href=\'#kases/' + main_case_id + '\'"
					adj_style = ' style="background:#333; cursor:pointer; text-decoration:underline" title="Related Case"';
					adj_number += "<span style='font-size:0.65em'>&nbsp;(related)</span>";
					//related_legend = "<div style='color:white;font-size:0.7em; padding-top:5px'>* Related Case</div>";
				}

				if (doi.get("venue") != "") {
					adj_number += " - " + doi.get("venue_abbr");
				}
				/*
				if (doi.get("main_case_id")!="") {
					main_case_id = doi.get("main_case_id");
				}
				*/
				var thedoi = '<tr id="related_row_' + doi.id + '" class="related_row"><td class="white_text"' + adj_style + '>' + adj_number + '</td><td>&nbsp;|&nbsp;</td><td class="white_text"><a href="#injury/' + main_case_id + '/' + doi.id + '" class="white_text" title="Click here to review Injury information">' + start_date;
				if (doi.get("end_date") != "0000-00-00") {
					thedoi += "&nbsp;-&nbsp;" + moment(doi.get("end_date")).format("MM/DD/YYYY") + "&nbsp;CT";
				}
				thedoi += statute_limitation;

				thedoi += '</a></td><td>';
				var lien_border = "";
				if (doi.get("lien_id") > 0) {
					lien_border = "background: black; padding:2px";
				}
				var settlement_border = "padding:2px";
				if (doi.get("settlement_id") > 0 || doi.get("fee_id") > 0) {
					settlement_border = "background: black; padding:2px";
				}

				var thesettle = '&nbsp;<div style="display:inline-block; ' + settlement_border + '"><a class="settlement_link doi_settle" title="Click to review settlement information" id="settlement_' + doi.id + '" style="cursor:pointer"><i class="glyphicon glyphicon-usd" style="color:#0F9"></i></a></div>';
				arrSettles.push(thesettle);
				thedoi += thesettle;
				thedoi += '&nbsp;<div style="display:inline-block; ' + lien_border + '"><a href="#lien/' + doi.case_id + '/' + doi.id + '" title="Click to review lien information" id="lien_' + doi.id + '" style="cursor:pointer;"><!--<i class="glyphicon glyphicon-link" style="color:#FF972F"></i>--></a>';

				//demographic link
				thedoi += '&nbsp;&nbsp;<a id="demographicsdoi_' + doi.id + '" class="demographics_doi" style="cursor:pointer" title="Click to open Demographic report for this DOI"><!--<i class="glyphicon glyphicon-user" style="color:#FFF">&nbsp;</i>--></a>';
				//multi dois on one demo
				thedoi += '&nbsp;<input type="checkbox" id="demographicsdoichoice_' + doi.id + '" class="demographics_doichoice" title="Check this box to create demographics sheet including this DOI" />&nbsp;Demo';

				//delete icon
				thedoi += '&nbsp;&nbsp;<a id="deletedoi_' + doi.id + '" class="delete_doi" style="cursor:pointer" title="Click to delete Injury"><!--<i class="glyphicon glyphicon-trash" style="color:#FC221D">&nbsp;</i>--></a>';

				thedoi += '<div style="display:none" id="jetfile_' + doi.id + '"></div></td></tr>';
				arrDOIs[arrDOIs.length] = thedoi;
				var doi_count = arrDOIs.length;
				if (intCounter > 0) {
					setTimeout(function () {
						var current_height = $("#summary_data_holder").css("height").replace("px", "");
						var adjustment = 25;
						if (current_height == 54) {
							adjustment = 62;
						}
						var blnAdjust = (Number(current_height) < 113);
						if ($("#summary_special_instructions").length == 0) {
							blnAdjust = true;
							//however, if we already have a count
							if (current_height == 54) {
								var needed = doi_count * 40;
								adjustment = needed - 54;
							}
						}
						if (blnAdjust) {
							//make it taller
							current_height = Number(current_height) + adjustment;
							$("#summary_data_holder").css("height", current_height + "px");
						}
					}, 100);
				}
				intCounter++;
			});
		}
		if (related_legend != "") {
			arrDOIs.push("<tr><td colspan='4'>" + related_legend + "</td></tr>");
		}

		//doi_label + 
		this.model.set("dois", arrDOIs.join(""));
		this.model.set("settlements", arrSettles.join(""));

		//worker
		if (!isNaN(this.model.get("worker"))) {
			var theworker = worker_searches.findWhere({ "id": this.model.get("worker") });
		} else {
			var theworker = worker_searches.findWhere({ "nickname": this.model.get("worker") });
			this.model.set("worker_name", this.model.get("worker"));
			if (typeof theworker != "undefined") {
				this.model.set("worker_full_name", theworker.get("user_name"));
			}
		}
		if (typeof theworker != "undefined") {
			this.model.set("worker", theworker.get("nickname").toUpperCase());
		}
		if (this.model.get("worker_name") == "" && this.model.get("worker_full_name") != "") {
			this.model.set("worker_name", this.model.get("worker_full_name").firstLetters());
		}

		//attorney
		if (!isNaN(this.model.get("attorney"))) {
			var theattorney = worker_searches.findWhere({ "id": this.model.get("attorney") });
		} else {
			var theattorney = worker_searches.findWhere({ "nickname": this.model.get("attorney") });
			this.model.set("attorney_name", this.model.get("attorney"));
			if (typeof theattorney != "undefined") {
				this.model.set("attorney_full_name", theattorney.get("user_name"));
			}
		}
		if (typeof theattorney != "undefined") {
			this.model.set("attorney", theattorney.get("nickname").toUpperCase());
		}
		if (this.model.get("attorney_name") == "" && this.model.get("attorney_full_name") != "") {
			this.model.set("attorney_name", this.model.get("attorney_full_name").firstLetters());
		}

		//supervising_attorney
		if (!isNaN(this.model.get("supervising_attorney"))) {
			var thesupervising_attorney = worker_searches.findWhere({ "id": this.model.get("supervising_attorney") });
		} else {
			var thesupervising_attorney = worker_searches.findWhere({ "nickname": this.model.get("supervising_attorney") });
			this.model.set("supervising_attorney_name", this.model.get("supervising_attorney"));
			if (typeof thesupervising_attorney != "undefined") {
				this.model.set("supervising_attorney_full_name", thesupervising_attorney.get("user_name"));
			}
		}
		if (typeof thesupervising_attorney != "undefined") {
			this.model.set("supervising_attorney", thesupervising_attorney.get("nickname").toUpperCase());
		}
		if (this.model.get("supervising_attorney_name") == "" && this.model.get("supervising_attorney_full_name") != "") {
			this.model.set("supervising_attorney_name", this.model.get("supervising_attorney_full_name").firstLetters());
		}
		var ssn = this.model.get("ssn");
		if (ssn == null) {
			ssn = "";
		}
		if (ssn.length == 9) {
			ssn = String(ssn);
			ssn1 = ssn.substr(0, 3);
			ssn2 = ssn.substr(3, 2);
			ssn3 = ssn.substr(5, 4);

			ssn = String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3);
			this.model.set("ssn", ssn);
		}
		var dob = this.model.get("dob");
		if (dob == null) {
			this.model.set("dob", "");
			dob = "";
		}
		var app_dob = this.model.get("app_dob");
		if (app_dob == null) {
			this.model.set("app_dob", "");
			app_dob = "";
		}
		if (dob == "" && app_dob != "") {
			this.model.set("dob", app_dob);
		}
		var kase_type = this.model.get("case_type");
		var kase_sub_type = this.model.get("case_sub_type");

		var blnWCAB = isWCAB(kase_type);
		this.model.set("blnWCAB", blnWCAB);

		var defendant_link = '';
		var employer_id = this.model.get("employer_id");
		var employer = this.model.get("employer");
		var defendant_id = this.model.get("defendant_id");
		var defendant = this.model.get("defendant");
		var case_id = this.model.get("case_id");

		if (blnWCAB) {
			if (this.model.get("employer_id") == -1) {
				employer = "No Employer";
			}
			if (blnPatient) {
				employer = "";
			}
			if (employer != "") {
				defendant_link = '<a href="#parties/' + case_id + '/' + employer_id + '/employer" class="white_text" title="Click to review Employer information">' + employer + '</a>';
			} else {
				defendant_link = "";
			}
		} else {
			if (this.model.get("defendant_id") == -1) {
				defendant = "No Defendant";
			}
			defendant_link = '<a href="#parties/' + case_id + '/' + defendant_id + '/defendant" class="white_text" title="Click to review Defendant information">' + defendant + '</a>';
		}

		if (this.model.get("file_number") == "" && this.model.get("case_number") != "") {
			this.model.set("file_number", this.model.get("case_number"));
		}

		if (this.model.get("venue_abbr") == null) {
			this.model.set("venue_abbr", "");
		}
		if (this.model.get("case_name") != "") {
			this.model.set("name", this.model.get("case_name"));
		}

		var case_name = this.model.get("case_name");
		var the_name = this.model.get("name");
		if (the_name != case_name && case_name != "") {
			the_name = case_name;
			this.model.set("name", case_name);
		}
		if (the_name == null) {
			this.model.set("name", "");
			case_name = "";
		}
		if (this.model.get("adj_number") == null) {
			this.model.set("adj_number", "")
		}
		this.model.set("defendant_link", defendant_link);
		var special_instructions = this.model.get("special_instructions");
		if (special_instructions == "undefined") {
			special_instructions = "";
			this.model.set("special_instructions", "");
		}
		//so we can change the value without affecting the kase
		var mymodel = this.model.toJSON();

		if (special_instructions != "") {
			special_instructions = special_instructions.replace(/(\r\n|\n|\r)/g, "<br />");
			special_instructions = "<span style='font-size:1.3em; color:white; background:red; padding:2px'>" + special_instructions + "</span>";
			special_instructions = "<div style=' background: none; color:white; text-align:left; padding:2px'>Special Instructions:</div>" + special_instructions;

			mymodel.special_instructions = special_instructions;
		}

		$(this.el).html(this.template(mymodel));

		setTimeout(function () {
			var redflags = new RedFlagCollection([], { case_id: current_case_id });
			$("#redflag_notes").hide();
			redflags.fetch({
				success: function (data) {
					if (data.length > 0) {
						var note_list_model = new Backbone.Model;
						note_list_model.set("display", "full");
						note_list_model.set("partie_type", "note");
						note_list_model.set("partie_id", -1);
						note_list_model.set("case_id", case_id);
						$('#redflag_notes').html(new red_flag_note_listing_view({ collection: data, model: note_list_model }).render().el);
						$("#redflag_notes").removeClass("glass_header_no_padding");
						$("#redflag_notes").show();
					}
				}
			});

			//let's get any related doi
			var case_id = self.model.get("case_id");
			var dob = self.model.get("dob");
			var ssn = self.model.get("ssn");
			var kase_dois = new KaseInjuryCollection({ case_id: case_id });
			relatedCount = 0;
			kase_dois.fetch({
				success: function (data) {
					if (data.length > 0) {
						var main_case_id = self.model.get("case_id");
						var current_adj_number = self.model.get("adj_number").toUpperCase();

						var kase_dois = data.toJSON();
						var arrStartDates = [];
						_.each(kase_dois, function (doi) {
							var start_date = "No Injury Info";
							if (doi.start_date != "" && doi.start_date != "0000-00-00") {
								start_date = moment(doi.start_date).format("MM/DD/YYYY");
							}
							if (doi.adj_number == null) {
								doi.adj_number = "";
							}
							var adj_number = doi.adj_number.toUpperCase();
							if (adj_number == null) {
								adj_number = "";
							}
							var main_case_id = doi.main_case_id;
							var blnRelatedCase = (main_case_id != current_case_id || doi.adj_number.toUpperCase() != current_adj_number);
							if (blnRelatedCase) {
								relatedCount++;
							}
							var blnWCAB = isWCAB(kase_type);
							arrStartDates.push(doi.id + "|" + doi.start_date + "|" + doi.end_date);
							//if (blnWCAB) {
							var current_related = $("#dois_summary_holder").html();
							if (typeof current_related != "undefined") {
								//make sure we don't add current stuff
								//if ((current_related.indexOf(start_date) < 0 || current_related.indexOf(doi.adj_number.toUpperCase()) < 0) && blnRelatedCase) {

								if (current_related.indexOf("related_row_" + doi.injury_id) < 0 && blnRelatedCase) {
									if (blnWCAB) {
										if (adj_number == "") {
											adj_number = "No ADJ";
										}
										//check adj
										if (adj_number.indexOf("ADJ") == 0) {
											//valid
											adj_number = "<span class='kase_adj_number' style='cursor:pointer; text-decoration:underline' title='Click to lookup ADJ on EAMS'>" + adj_number + "</span>";
										}
									}
									var injury_status = doi.injury_status;
									switch (injury_status) {
										case "Accepted":
											injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_lightgreen.png' width='18px' height='18px' title='Accepted' /></div>";
											break;
										case "Denied":
											injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_red.png' width='18px' height='18px' title='Denied' /></div>";
											break;
										case "Pending":
											injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;'><img src='img/square_orange.png' width='18px' height='18px' title='Pending' /></div>";
											break;
										case "Completed":
											injury_status = "<div style='float:right;margin-left:5px;width:18px;height:18px;' title='Completed'><span style='background:white;color:green;width:18px;height:18px;text-align:center;display:inline-block'>&#10003;</span></div>";
											break;
									}

									adj_number = injury_status + adj_number;

									if (doi.venue != "") {
										adj_number += " - " + doi.venue_abbr;
									}
									var adj_style = ' style="background:#333; cursor:pointer; text-decoration:underline" title="Related Case" onclick="document.location.href=\'#kases/' + main_case_id + '\'"';
									var spacer_style = "";
									if (!blnWCAB) {
										//use the adj spot to show the case number
										var this_case_number = '<div id="summary_case_number_label_' + doi.id + '">Case Number</span>: ' + doi.case_number + '<span style="font-size:0.65em">&nbsp;(related)</span>&nbsp;|&nbsp;</div>';

										$("#summary_case_number_holder").append(this_case_number);
										adj_style = ' style="visibility:hidden"';
										spacer_style = adj_style;

									} else {
										adj_number += "<span style='font-size:0.65em'>&nbsp;(related)</span>";
									}
									//related_legend = "<div style='color:white;font-size:0.7em; padding-top:5px'>* Related Case</div>";
									if (start_date == "Invalid date") {
										start_date = "";
									}
									var thedoi = '<tr id="related_row_' + doi.id + '" class="related_row"><td class="white_text"' + adj_style + '>' + adj_number + '</td><td' + spacer_style + '>&nbsp;|&nbsp;</td><td class="white_text"><a href="#injury/' + main_case_id + '/' + doi.id + '" class="white_text" title="Click here to review Injury information">DOI:&nbsp;' + start_date;
									if (doi.end_date != "0000-00-00") {
										thedoi += "&nbsp;-&nbsp;" + moment(doi.end_date).format("MM/DD/YYYY") + "&nbsp;CT";
									}
									thedoi += '</a>';
									//sol
									if (doi.statute_limitation != "" && doi.statute_limitation != "0000-00-00") {
										thedoi += "&nbsp;|&nbsp;SOL:&nbsp;" + moment(doi.statute_limitation).format("MM/DD/YYYY");
									}
									thedoi += '</td><td>';

									var lien_border = "";
									if (doi.lien_id > 0) {
										lien_border = "background: black; padding:2px";
									}
									var settlement_border = "padding:2px";
									if (doi.settlement_id > 0 || doi.fee_id > 0) {
										settlement_border = "background: black; padding:2px";
									}

									thedoi += '&nbsp;<div style="display:inline-block; ' + settlement_border + '"><a class="settlement_link" title="Click to review settlement information" id="settlement_' + doi.id + '" style="cursor:pointer"><i class="glyphicon glyphicon-usd" style="color:#0F9"></i></a></div>';
									thedoi += '&nbsp;<div style="display:inline-block; ' + lien_border + '"><a href="#lien/' + doi.case_id + '/' + doi.id + '" title="Click to review lien information" id="lien_' + doi.id + '" style="cursor:pointer;"><!--<i class="glyphicon glyphicon-link" style="color:#FF972F"></i>--></a>';

									//demographic link
									thedoi += '&nbsp;&nbsp;<a id="demographicsdoi_' + doi.id + '" class="demographics_doi" style="cursor:pointer" title="Click to open Demographic report for this DOI"><!--<i class="glyphicon glyphicon-user" style="color:#FFF">&nbsp;</i>--></a>';
									//multi dois on one demo
									thedoi += '&nbsp;<input type="checkbox" id="demographicsdoichoice_' + doi.id + '" class="demographics_doichoice" title="Check this box to create demographics sheet including this DOI" />&nbsp;Demo';

									//delete icon
									thedoi += '&nbsp;&nbsp;<a id="deletedoi_' + doi.id + '" class="delete_doi" style="cursor:pointer; visibility:hidden" title="Click to delete Injury"><!--<i class="glyphicon glyphicon-trash" style="color:#FC221D">&nbsp;</i>--></a>';
									thedoi += '<div style="display:none" id="jetfile_' + doi.id + '"></div></td></tr>';

									$("#dois_summary_holder").append(thedoi);

									if (blnWCAB) {
										//move it up a little
										$("#float_doi_holder").css("margin-top", "-10px");
									} else {
										//move it up a little
										$("#float_doi_holder").css("margin-top", "-3px");
									}
									//make it taller
									var current_height = $("#summary_data_holder").css("height").replace("px", "");
									if (Number(current_height) < 112) {
										current_height = Number(current_height) + 20;
										$("#summary_data_holder").css("height", current_height + "px");
									}
								}
							}
							//}
						});
						if (blnWCAB) {
							var formValues = "dob=" + dob;
							formValues += "&ssn=" + ssn.replaceAll("-", "");
							arrStartDates.forEach(function (item, index) {
								//console.log(dob, ssn, item);
								var arrItem = item.split("|");
								var injury_id = arrItem[0];
								var url = 'api/jetfile/check/applicant';
								var checkValues = formValues;
								checkValues += "&case_id=" + case_id;
								checkValues += "&injury_id=" + injury_id;
								checkValues += "&start=" + arrItem[1];
								checkValues += "&end=" + arrItem[2];

								$.ajax({
									url: url,
									type: 'POST',
									dataType: "json",
									data: checkValues,
									success: function (data) {
										if (data.error) {  // If there is an error, show the error messages
											saveFailed(data.error.text);
										} else {
											var jetfile_color = '#FFF';
											var jetfile_injury_id = data.injury_id;
											var jetfile_title = 'Fill out the App for ADJ';
											var jetfile_visibility = 'visible';
											if (data.case_id != "-1" && data.case_id != "") {
												jetfile_color = '#d0fc1d';
												jetfile_title = 'App for ADJ Filed';
											}
											if (data.case_id != "-1" && data.adj_number.indexOf("ADJ") == 0) {
												jetfile_color = '#91F41A';
												jetfile_title = 'App for ADJ Completed';
												//jetfile_visibility = 'hidden';

												setTimeout(function () {
													$(".kase_adj_number").css("padding", "1px");
													$(".kase_adj_number").css("background", "green");
													$(".kase_adj_number").attr("title", jetfile_title);
												}, 200);
											}

											var jetfile_link = '<a style="color:' + jetfile_color + '; visibility:' + jetfile_visibility + '; font-size:1.2em; cursor:pointer" title="' + jetfile_title + '" class="jetfile" id="jetfile_' + current_case_id + '_' + jetfile_injury_id + '_' + data.case_id + '">&#9992;</a>';
											$("#jetfile_" + jetfile_injury_id).html(jetfile_link);
											$("#jetfile_" + jetfile_injury_id).css("display", "inline-block");

											//clean up
											if (!blnJetFile) {
												self.updateCustomerJetfile(data.jetfile_id);
											}
										}
									}
								});
							});
						}
						if (relatedCount > 0) {
							var current_relatedCount = relatedCount;
							setTimeout(function () {
								if ($("#related_cases").length > 0) {
									//not reset					
									$("#related_cases").html("Related Cases <span style='font-size:0.8em'>(" + current_relatedCount + ")</span>");
									//reset
									relatedCount = 0;
								}
							}, 1500);
						}
					}
				}
			});

			//immigration
			if (self.model.get("case_type") == "immigration") {
				$("#summary_case_number_label").html("Alien #");
			}
			/*
			if (typeof self.model.get("personal_statute_limitation") != "undefined") {
				if (self.model.get("personal_statute_limitation")!="") {
					if ($("#summary_data_holder").css("height").replace("px", "") < 80) {
						$("#summary_data_holder").css("height", "80px");
					}
				}
			}
			*/
			if (!blnWCAB) {
				if (self.model.get("injury_type") == "") {
					if (self.model.get("kase_type") != "immigration") {
						$("#injury_type_warning").css("display", "inline-block");
					}
				}
			}
		}, 500)

		return this;
	},
	confirmDeleteInjury: function (event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];

		composeDelete(id, "injury");
	},
	acceptKase: function (event) {
		event.preventDefault();
		var url = 'api/kase/accept';
		var checkValues = "case_id=" + current_case_id;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: checkValues,
			success: function (data) {
				var kase = kases.findWhere({ case_id: current_case_id });
				kase.set("case_status", "Open");
				checkIntakes();

				var note = "Kase was accepted by " + login_nickname;
				//add a note
				var url = 'api/notes/add';

				formValues = "table_name=notes";
				formValues += "&case_id=" + current_case_id;

				formValues += "&noteInput=" + note;
				formValues += "&status=INTAKE";
				formValues += "&type=phone+intake";
				formValues += "&subject=Intake Accepted";
				formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");

				//return;
				$.ajax({
					url: url,
					type: 'POST',
					dataType: "json",
					data: formValues,
					success: function (data) {
						if (data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						}
						if (data.success) {
							resetCurrentContent();
							window.Router.prototype.fetchKase(current_case_id);
						}
					}
				});
			}
		});
	},
	rejectKase: function (event) {
		event.preventDefault();
		var url = 'api/kase/reject';
		var checkValues = "case_id=" + current_case_id;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: checkValues,
			success: function (data) {
				var kase = kases.findWhere({ case_id: current_case_id });
				kase.set("case_status", "REJECTED");

				checkIntakes();

				var note = "Kase was rejected by " + login_nickname;
				//add a note
				var url = 'api/notes/add';

				formValues = "table_name=notes";
				formValues += "&case_id=" + current_case_id;

				formValues += "&noteInput=" + note;
				formValues += "&status=INTAKE";
				formValues += "&subject=Intake Rejected";
				formValues += "&dateandtime=" + moment().format("MM/DD/YYYY h:mmA");

				//return;
				$.ajax({
					url: url,
					type: 'POST',
					dataType: "json",
					data: formValues,
					success: function (data) {
						if (data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						}
						if (data.success) {
							window.Router.prototype.listIntakes();
						}
					}
				});
			}
		});
	},
	showSettlement: function (event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//"#settlement/<%=case_id %>/<%=id %>
		//window.history.pushState(null, null, "#settlement/" + current_case_id + "/" + id);
		window.Router.prototype.showSettlement(current_case_id, id);
		window.history.replaceState(null, null, "#settlement/" + current_case_id + "/" + id);
		app.navigate("settlement/" + current_case_id + "/" + id, { trigger: false });
	},
	releaseMultiDemoLink: function (event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		var arrIncludedDOIs = this.model.get("included_dois");
		if (arrIncludedDOIs.indexOf(id) < 0) {
			arrIncludedDOIs.push(id);
		}
		if ($("#multi_demo_link").length == 0) {
			$("#float_doi_holder").prepend("<div style='margin-bottom:5px; text-align:right'><button class='btn btn-xs' id='multi_demo_link'>Demographics</button></div>");
			var height = Number($("#summary_data_holder").css("height").replace("px", ""));
			height += 25;
			$("#summary_data_holder").css("height", height + "px");
		}
	},
	demographicMultiDOI: function (event) {
		event.preventDefault();
		var arrIncludedDOIs = this.model.get("included_dois");
		var ids = arrIncludedDOIs.join("|");
		window.open("reports/demographics_sheet.php?case_id=" + current_case_id + "&injury_id=" + ids);
	},
	demographicDOI: function (event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];

		window.open("reports/demographics_sheet.php?case_id=" + current_case_id + "&injury_id=" + id);
		/*
		var url = "reports/demographics_sheet.php";
		var params = [];
		params.push({case_id: current_case_id, injury_id: id});
		postForm(url, params);
		*/
	},
	editKase: function (event) {
		var element = event.currentTarget;
		composeKaseEdit(element.id)
	},
	massEmail: function (event) {
		//	alert("Working on it.");
		//return;
		var element = event.currentTarget;
		console.log(element.id);
		composeMessage(element.id);
	},
	editSettlement: function (event) {
		var element = event.currentTarget;
		console.log(element.id);
		composeSettlement(element.id)
	},
	lookupADJ: function (event) {
		var element = event.currentTarget;
		var adj_number = element.innerHTML;
		composeEamsImport(adj_number, "lookup", current_case_id);
	},
	jetFile: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var arrElement = element_id.split("_");
		var case_id = arrElement[arrElement.length - 3];
		var injury_id = arrElement[arrElement.length - 2];
		var jetfile_case_id = arrElement[arrElement.length - 1];

		window.open("jetfiler/app_1_2.php?case_id=" + case_id + "&injury_id=" + injury_id + "&jetfile_case_id=" + jetfile_case_id);
	},
	updateCustomerJetfile: function (jetfile_id) {
		var url = 'api/jetfile/updateid';
		var checkValues = "jetfile_id=" + jetfile_id;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: checkValues,
			success: function (data) {
				blnJetFile = true;
			}
		});
	}
});
window.kase_edit_view = Backbone.View.extend({

	tagName: "div", // Not required since 'div' is the default if no el or tagName specified

	initialize: function () {
	},

	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "kase_edit_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
		//setup the datetimepicker
		//datepickIt('#case_dateInput');
		attorneyComplete("kase", "attorneyInput");
		workerComplete("kase", "workerInput");
		$('#case_dateInput').datetimepicker({
			timepicker: false,
			format: 'm/d/Y h:iA',
			mask: false,
			onChangeDateTime: function (dp, $input) {
				//alert($input.val());
			}
		});
		setTimeout(function () {
			attorneyComplete("kase", "attorneyInput");
			workerComplete("kase", "workerInput");
			//move the autocomplete

		}, 500);
		//this view is the header for all other views, and sometimes we only want a header
		if (!this.model.get("header_only")) {
			setTimeout(function () {
				$('#kase_content').html(new dashboard_view({ model: self.model }).render().el);
			}, 500);
		}

		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				if (self.model.get("case_editable") != "Y") {
					$(".kase #case_numberInput").toggleClass("hidden");
					$(".kase #case_numberSpan").toggleClass("hidden");
				}
			}, 1500);
			$(".kase #venueInput").focus();
		}
		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				attorneyComplete("kase", "attorneyInput");
				//move the autocomplete

			}, 600);
		}
		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				workerComplete("kase", "workerInput");
				//move the autocomplete

			}, 600);
		}
		setTimeout("gridsterById('gridster_flat')", 1);
		return this;
	},
	events: {
		"click .kase .delete": "deleteKase",
		"click .kase .save": "addKase",
		"click .kase .save_field": "saveKaseField",
		"click .kase .reset": "resetKaseForm",
		"focus .kase #case_numberInput": "focusVenue",
		"dblclick .kase .glass": "editKaseField",
		"keyup .kase .input_class": "valueChanged"
	},
	focusVenue: function (event) {
		$(".kase #venueInput").focus();
	},
	uploadKaseDocument: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		window.location.hash = "#upload/" + $('#id').val() + "/kase";
	},
	editKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		if (this.model.get("case_id") == -1) {
			return;
		}
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".kase_" + field_name;
		}
		//var inputs = $(".kase .input_class");
		//for (inp in inputs) {

		//}
		editField(element, master_class);
	},

	saveKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form

		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");

		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		var field_name = theclass + " #" + element;

		var element_value = $(field_name + "Input").val();

		//restore the read look 
		editField($(field_name + "Grid"));

		if ($(field_name + "Input").prop("type") != "select-one") {
			$(field_name + "Span").html(escapeHtml(element_value));
		} else {
			//text value of the drop down
			var dropdown_text = $(field_name + "Input :selected").text();
			dropdown_text = dropdown_text.split(" - ")[0];
			if (dropdown_text == "Select from List") {
				dropdown_text = "";
			}
			$(field_name + "Span").html(escapeHtml(dropdown_text));
		}
		this.model.set(element, element_value);

		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);

		if (typeof kases != "undefined") {
			if (this.model.get("case_id") != "") {
				kases.get(this.model.get("case_id")).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKase(event);
	},

	addKase: function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var form_name = "kase";

		addForm(event, form_name);

		return;
	},
	deleteKase: function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form

		deleteForm(event, "kase");
		return;
	},

	showCalendar: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		//clean up button
		$("button.calendar").fadeOut(function () {
			$("button.information").fadeIn();
		});
		window.location.hash = "#kases/events/" + $('#id').val()
	},

	showInformation: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		$("button.information").fadeOut(function () {
			$("button.calendar").fadeIn();
		});
		window.location.hash = "#kases/" + $('#id').val()
	},

	resetKaseForm: function (e) {
		this.toggleEdit(e);
		$('#kase_header', this.el).html(new kase_header_view({ model: this.model }).render().el);
		//need to gridster it
		setTimeout("gridsterIt(1)", 1);
	},

	valueChanged: function (e) {
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");

		var newval = $("#" + source + "Input").val();
		if (newval == "") {
			if ($("#" + source + "Input").hasClass("required")) {
				newval = "Please fill me in";
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(newval);
		//update the model?
		this.model.set(source, newval);
	},
	toggleKaseEdit: function (e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}

		//get all the editing fields, and toggle them back
		$(".kase .editing").toggleClass("hidden");
		$(".kase .span_class").removeClass("editing");
		$(".kase .input_class").removeClass("editing");

		$(".kase .span_class").toggleClass("hidden");
		$(".kase .input_class").toggleClass("hidden");
		$(".kase .input_holder").toggleClass("hidden");

		$(".button_row.kase").toggleClass("hidden");
		$(".edit_row.kase").toggleClass("hidden");

		$(".applicant .editing").toggleClass("hidden");
		$(".applicant .span_class").removeClass("editing");
		$(".applicant .input_class").removeClass("editing");

		$(".applicant .span_class").toggleClass("hidden");
		$(".applicant .input_class").toggleClass("hidden");
		$(".applicant .input_holder").toggleClass("hidden");

		$(".button_row.applicant").toggleClass("hidden");
		$(".edit_row.applicant").toggleClass("hidden");
	}
});
window.kase_header_view = Backbone.View.extend({

	tagName: "div", // Not required since 'div' is the default if no el or tagName specified

	initialize: function () {
	},

	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_header");
			var view = "kase_header_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;

		$(this.el).html(this.template(this.model.toJSON()));
		//setup the datetimepicker
		//datepickIt('#case_dateInput');
		$('#case_dateInput').datetimepicker({
			timepicker: false,
			format: 'm/d/Y h:iA',
			mask: false,
			onChangeDateTime: function (dp, $input) {
				//alert($input.val());
			}
		});


		//this view is the header for all other views, and sometimes we only want a header
		if (!this.model.get("header_only")) {
			setTimeout(function () {
				$('#kase_content').html(new dashboard_view({ model: self.model }).render().el);
			}, 500);
		}

		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				$(".kase #case_numberInput").toggleClass("hidden");
				$(".kase #case_numberSpan").toggleClass("hidden");
			}, 1500);
			$(".kase #venueInput").focus();
		}
		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				attorneyComplete("kase", "attorneyInput");
				//move the autocomplete

			}, 600);
		}
		if (this.model.get("case_id") == -1) {
			setTimeout(function () {
				workerComplete("kase", "workerInput");
				//move the autocomplete

			}, 600);
		}
		return this;
	},
	events: {
		"click .kase .delete": "deleteKase",
		"click .kase .save": "addKase",
		"click .kase .save_field": "saveKaseField",
		"click .kase .reset": "resetKaseForm",
		"focus .kase #case_numberInput": "focusVenue",
		"dblclick .kase .glass": "editKaseField",
		"keyup .kase .input_class": "valueChanged"
	},
	focusVenue: function (event) {
		$(".kase #venueInput").focus();
	},
	uploadKaseDocument: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		window.location.hash = "#upload/" + $('#id').val() + "/kase";
	},
	editKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		if (this.model.get("case_id") == -1) {
			return;
		}
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".kase_" + field_name;
		}
		editField(element, master_class);
	},

	saveKaseField: function (event) {
		event.preventDefault(); // Don't let this button submit the form

		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");

		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		var field_name = theclass + " #" + element;

		//restore the read look
		editField($(field_name + "Grid"));

		var element_value = $(field_name + "Input").val();

		if ($(field_name + "Input").prop("type") != "select-one") {
			$(field_name + "Span").html(escapeHtml(element_value));
		} else {
			//text value of the drop down
			var dropdown_text = $(field_name + "Input :selected").text();
			dropdown_text = dropdown_text.split(" - ")[0];
			if (dropdown_text == "Select from List") {
				dropdown_text = "";
			}
			$(field_name + "Span").html(escapeHtml(dropdown_text));
		}
		this.model.set(element, element_value);

		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);

		if (typeof kases != "undefined") {
			if (this.model.get("case_id") != "") {
				kases.get(this.model.get("case_id")).set(this.model.toJSON());
			}
		}
		//this should not redraw, it will update if no id
		this.addKase(event);
	},

	addKase: function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		var form_name = "kase";
		addForm(event, form_name);

		return;
	},
	deleteKase: function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form

		deleteForm(event, "kase");
		return;
	},

	showCalendar: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		//clean up button
		$("button.calendar").fadeOut(function () {
			$("button.information").fadeIn();
		});
		window.location.hash = "#kases/events/" + $('#id').val()
	},

	showInformation: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		$("button.information").fadeOut(function () {
			$("button.calendar").fadeIn();
		});
		window.location.hash = "#kases/" + $('#id').val()
	},

	resetKaseForm: function (e) {
		this.toggleEdit(e);
		$('#kase_header', this.el).html(new kase_header_view({ model: this.model }).render().el);
		//need to gridster it
		setTimeout("gridsterIt(1)", 1);
	},

	valueChanged: function (e) {
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");

		var newval = $("#" + source + "Input").val();
		if (newval == "") {
			if ($("#" + source + "Input").hasClass("required")) {
				newval = "Please fill me in";
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(newval);
		//update the model?
		this.model.set(source, newval);
	},
	toggleKaseEdit: function (e) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}

		//get all the editing fields, and toggle them back
		$(".kase .editing").toggleClass("hidden");
		$(".kase .span_class").removeClass("editing");
		$(".kase .input_class").removeClass("editing");

		$(".kase .span_class").toggleClass("hidden");
		$(".kase .input_class").toggleClass("hidden");
		$(".kase .input_holder").toggleClass("hidden");

		$(".button_row.kase").toggleClass("hidden");
		$(".edit_row.kase").toggleClass("hidden");

		$(".applicant .editing").toggleClass("hidden");
		$(".applicant .span_class").removeClass("editing");
		$(".applicant .input_class").removeClass("editing");

		$(".applicant .span_class").toggleClass("hidden");
		$(".applicant .input_class").toggleClass("hidden");
		$(".applicant .input_holder").toggleClass("hidden");

		$(".button_row.applicant").toggleClass("hidden");
		$(".edit_row.applicant").toggleClass("hidden");
	}
});
window.kase_subtype_listing = Backbone.View.extend({
	initialize: function () {
		// re-render automatically when collection loads or resets
		this.listenTo(this.collection, "sync reset", this.render);
	  },
	events: {
		/*"click #select_all_filters":		"selectAll",*/
		"click #new_kase_subtype_button": "newKaseSubtype",
		"click #save_kase_status": "saveKaseSubtype",
		"click .kase_status_checkbox": "activateKaseSubtype",
		"click .kase_status_cell": "editKaseSubtype",
		"click .kase_status_save": "updateKaseSubtype",
		"click #show_all_status": "showAllSubtype"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_status_holder");
			var view = "kase_subtype_listing";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}		

		var self = this;
		//$(this.el).html(this.template(this.model.toJSON()));
		//what are we looking for?

		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		//console.log("3240 "+this.collection);
		var arrFilters = this.collection.toJSON();
		//var arrFilters = this.collection.toJSON();

		/* _.each(arrFilters, function (kase_status) {
		console.log("3243", kase_status.id, kase_status);
		}); */
		var arrRows = [];
		var current_status = $("#case_subtype").val();

		var arrOptions = [];
		var option_selected = "";
		if (current_status == "") {
			option_selected = " selected";
		}
		var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);

		//arrFilters.forEach(function(element, index, array) {
		selected_case_type = $("#case_typeInput :selected").val();
		
		_.each(arrFilters, function (kase_status) { //console.log("3254 "+kase_status.casesubtype);
			if (typeof kase_status.casesubtype_id != "undefined") {
				if (kase_status.law.toLowerCase() == selected_case_type.toLowerCase() || ((kase_status.law.toLowerCase() == "pi" || kase_status.law.toLowerCase() == "newpi") && (selected_case_type.toLowerCase() == "newpi" || selected_case_type.toLowerCase() == "pi"))) {
					var index = kase_status.casesubtype_id
					var checked = " checked";
					var row_display = "";
					var cell_display = "color:white";
					var row_class = "active_filter";
					if (kase_status.deleted == "Y") {
						checked = "";
						row_display = "display:none";
						cell_display = "color:red; text-decoration:line-through;";
						row_class = "deleted_filter";
					}
					//console.log('ds');

					if (kase_status.law.toLowerCase() == "wcab") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' selected=''>WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "NewPI") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI' selected=''>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "civil") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil' selected=''>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "employment_law") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law' selected=''>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "immigration") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration' selected=''>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "pi") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi' selected=''>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "social_security") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security' selected=''>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "WCAB_Defense") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense' selected=''>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "class_action") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' selected=''>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					else {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.casesubtype + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value='' selected=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' >Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					// var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='kase_status'><input type='checkbox' class='kase_status_checkbox hidden' value='Y' title='Uncheck to stop using this status.  Old records currently using this status will not be affected' id='kase_status_" + index + "' name='kase_status_" + index + "'" + checked + "></td><td class='kase_status' style='" + cell_display + "'><span id='kase_status_span_" + index + "' class='kase_status_cell' style='cursor:pointer' title='Click to edit this status'>" + kase_status.casesubtype + "</span>" + input + "</td></tr>";
					arrRows.push(therow);
					//are we using a deleted status
					var current_status = self.model.get("case_status");
					var current_status_only_for_cond = $("#case_subtypeInput :selected").val();
					var blnUsingDeleted = (arrDeletedKaseStatus.indexOf(current_status) > -1);
					//console.log('current_status' + current_status);

					if (kase_status.deleted != "Y" || blnUsingDeleted) {
						//the drop down has to match
						var option_selected = "";
						if (kase_status.status == current_status_only_for_cond) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.casesubtype + '" class="wcab_status_option"' + option_selected + '>' + kase_status.casesubtype + ' </option>';
						arrOptions.push(option);
					}
				}
			}
		});
		//var html = "<table id='kase_subtype_table'>" + arrRows.join("") + "</table>";
		/* try {
			$(this.el).html(this.template({ html: html, status_level: status_level }));
			if (status_level == "") {
				$("#case_statusInput").html(arrOptions.join("\r\n"));
				//reset global
				casestatus_options = arrOptions.join("\r\n");
			} else {
				$("#case_substatusInput").html(arrOptions.join("\r\n"));

				//i have to go through the loop one more time to set the selected for subsub
				var current_status = $("#case_subsubstatusInput").val();
				var arrSubOptions = [];
				if (current_status == "") {
					option_selected = " selected";
				}
				var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
				arrSubOptions.push(option);
				_.each(arrFilters, function (kase_status) {
					if (kase_status.deleted != "Y") {
						//the drop down has to match
						var option_selected = "";
						if (kase_status.status == current_status) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
						arrSubOptions.push(option);
					}
				});
				$("#case_SubtypeInput").html(arrSubOptions.join("\r\n"));
			}
		}
		catch (err) {
			alert(err);

			return false;
		}
		return this; */
		var html = "<table id='kase_status_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({ html: html, status_level: status_level }));
			/* if (status_level == "") {
				$("#case_subtypeInput").html(arrOptions.join("\r\n"));
				//reset global
				casestatus_options = arrOptions.join("\r\n");
			} else {
				$("#case_subtypeInput").html(arrOptions.join("\r\n"));
			} */
		}
		catch (err) {
			alert(err);

			return false;
		}
		return this;	
	},
	selectAll: function (event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editKaseSubtype: function (event) {
		//console.log('in edit fun');

		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casesubtype_id = arrID[arrID.length - 1];

		//not open nor closed
		var current = $("#kase_status_value_" + casesubtype_id).val();
		if (current == "Open" || current == "Closed") {
			return;
		}
		$("#kase_status_span_" + casesubtype_id).fadeOut();
		$("#kase_status_value_" + casesubtype_id).toggleClass("hidden");
		$("#caseTypeInput" + casesubtype_id).toggleClass("hidden");
		$("#kase_status_save_" + casesubtype_id).toggleClass("hidden");
		$("#kase_status_" + casesubtype_id).toggleClass("hidden");
		/* if ($("#new_kase_subtype_holder").is(":hidden")) {
			$("#new_kase_subtype").prop("disabled", true);
		} else {
			$("#new_kase_subtype").prop("disabled", false);
		} */

	},
	showAllSubtype: function () {
		$("#show_all_status").hide();
		$(".deleted_filter").show();
	},
	newKaseSubtype: function (event) {
		event.preventDefault();
		$("#new_kase_subtype_button").fadeOut();
		$("#new_kase_subtype_holder").fadeIn(function () {
			$("#new_kase_subtype").focus();
		});
	},
	saveKaseSubtype: function (event) {
		event.preventDefault();
		var url = 'api/subtypefilter/add';
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var casetype = $('#caseTypeInput :selected').val();
		var casesubtype = $("#new_kase_subtype").val();
		var formValues = "casesubtype=" + encodeURIComponent(casesubtype) + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casesubtype == "" || casesubtype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_sub_type").trigger("click");
					}
				}
			});
		}
		//
	},
	activateKaseSubtype: function (event) {
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		document.cookie = "changes_need_render_new_kase_view=1";
		$("#kase_status_save_" + casestatus_id).trigger("click");
	},
	updateKaseSubtype: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casesubtype_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("kase_status_" + casesubtype_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casetype = $('#caseTypeInput' + casesubtype_id + ' :selected').val();//console.log("case type ".casetype);
		var casesubtype = $("#kase_status_value_" + casesubtype_id).val();
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var url = 'api/subtypefilter/update';
		var formValues = "casesubtype_id=" + casesubtype_id + "&casesubtype=" + encodeURIComponent(casesubtype) + "&deleted=" + deleted + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casesubtype == "" || casesubtype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_sub_type").trigger("click");
					}
				}
			});
		}
	}
});
window.kase_status_listing = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		/*"click #select_all_filters":		"selectAll",*/
		"click #new_kase_status_button": "newKaseStatus",
		"click #save_kase_status": "saveKaseStatus",
		"click .kase_status_checkbox": "activateKaseStatus",
		"click .kase_status_cell": "editKaseStatus",
		"click .kase_status_save": "updateKaseStatus",
		"click #show_all_status": "showAllStatus"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_status_holder");
			var view = "kase_status_listing";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}

		var self = this;

		//what are we looking for?

		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		var arrFilters = this.collection.toJSON();
		var arrRows = [];
		var current_status = $("#case_" + status_level + "statusInput").val();

		var arrOptions = [];
		var option_selected = "";
		if (current_status == "") {
			option_selected = " selected";
		}
		var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);

		//arrFilters.forEach(function(element, index, array) {
		selected_case_type = $("#case_typeInput :selected").val();
		
		_.each(arrFilters, function (kase_status) {
			if (typeof kase_status.id != "undefined") {
				if (kase_status.law.toLowerCase() == selected_case_type.toLowerCase() || ((kase_status.law.toLowerCase() == "pi" || kase_status.law.toLowerCase() == "newpi") && (selected_case_type.toLowerCase() == "newpi" || selected_case_type.toLowerCase() == "pi"))) {
					var index = kase_status.id
					var checked = " checked";
					var row_display = "";
					var cell_display = "color:white";
					var row_class = "active_filter";
					if (kase_status.deleted == "Y") {
						checked = "";
						row_display = "display:none";
						cell_display = "color:red; text-decoration:line-through;";
						row_class = "deleted_filter";
					}
					console.log('ds');

					if (kase_status.law.toLowerCase() == "wcab") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' selected=''>WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "NewPI") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI' selected=''>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "civil") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil' selected=''>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "employment_law") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law' selected=''>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "immigration") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration' selected=''>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "pi") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi' selected=''>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "social_security") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security' selected=''>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "WCAB_Defense") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense' selected=''>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "class_action") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' selected=''>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					else {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value='' selected=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' >Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					// var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='kase_status'><input type='checkbox' class='kase_status_checkbox hidden' value='Y' title='Uncheck to stop using this status.  Old records currently using this status will not be affected' id='kase_status_" + index + "' name='kase_status_" + index + "'" + checked + "></td><td class='kase_status' style='" + cell_display + "'><span id='kase_status_span_" + index + "' class='kase_status_cell' style='cursor:pointer' title='Click to edit this status'>" + kase_status.status + "</span>" + input + "</td></tr>";
					arrRows.push(therow);

					//are we using a deleted status
					var current_status = self.model.get("case_status");
					var current_status_only_for_cond = $("#case_statusInput :selected").val();
					var blnUsingDeleted = (arrDeletedKaseStatus.indexOf(current_status) > -1);
					console.log('current_status' + current_status);

					if (kase_status.deleted != "Y" || blnUsingDeleted) {
						//the drop down has to match
						var option_selected = "";
						if (kase_status.status == current_status_only_for_cond) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + ' </option>';
						arrOptions.push(option);
					}
				}
			}
		});
		var html = "<table id='kase_status_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({ html: html, status_level: status_level }));
			if (status_level == "") {
				$("#case_statusInput").html(arrOptions.join("\r\n"));
				//reset global
				casestatus_options = arrOptions.join("\r\n");
			} else {
				$("#case_substatusInput").html(arrOptions.join("\r\n"));

				//i have to go through the loop one more time to set the selected for subsub
				var current_status = $("#case_subsubstatusInput").val();
				var arrSubOptions = [];
				if (current_status == "") {
					option_selected = " selected";
				}
				var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
				arrSubOptions.push(option);
				_.each(arrFilters, function (kase_status) {
					if (kase_status.deleted != "Y") {
						//the drop down has to match
						var option_selected = "";
						if (kase_status.status == current_status) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
						arrSubOptions.push(option);
					}
				});
				$("#case_subsubstatusInput").html(arrSubOptions.join("\r\n"));
			}
		}
		catch (err) {
			alert(err);

			return false;
		}
		return this;
	},
	selectAll: function (event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editKaseStatus: function (event) {
		console.log('in edit fun');

		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];

		//not open nor closed
		var current = $("#kase_status_value_" + casestatus_id).val();
		if (current == "Open" || current == "Closed") {
			return;
		}
		$("#kase_status_span_" + casestatus_id).fadeOut();
		$("#kase_status_value_" + casestatus_id).toggleClass("hidden");
		$("#caseTypeInput" + casestatus_id).toggleClass("hidden");
		$("#kase_status_save_" + casestatus_id).toggleClass("hidden");
		$("#kase_status_" + casestatus_id).toggleClass("hidden");

	},
	showAllStatus: function () {
		$("#show_all_status").hide();
		$(".deleted_filter").show();
	},
	newKaseStatus: function (event) {
		event.preventDefault();
		$("#new_kase_status_button").fadeOut();
		$("#new_kase_status_holder").fadeIn(function () {
			$("#new_kase_status").focus();
		});
	},
	saveKaseStatus: function (event) {
		event.preventDefault();
		var url = 'api/statusfilter/add';
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var casetype = $('#caseTypeInput :selected').val();
		var casestatus = $("#new_kase_status").val();
		var formValues = "casestatus=" + encodeURIComponent(casestatus) + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_" + status_level + "status").trigger("click");
					}
				}
			});
		}
		//
	},
	activateKaseStatus: function (event) {
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		document.cookie = "changes_need_render_new_kase_view=1";
		$("#kase_status_save_" + casestatus_id).trigger("click");
	},
	updateKaseStatus: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("kase_status_" + casestatus_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casetype = $('#caseTypeInput' + casestatus_id + ' :selected').val();
		var casestatus = $("#kase_status_value_" + casestatus_id).val();
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var url = 'api/statusfilter/update';
		var formValues = "casestatus_id=" + casestatus_id + "&casestatus=" + encodeURIComponent(casestatus) + "&deleted=" + deleted + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_" + status_level + "status").trigger("click");
					}
				}
			});
		}
	}
});
window.kase_sub_status_listing = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		/*"click #select_all_filters":		"selectAll",*/
		"click #new_kase_sub_status_button": "newKaseSubStatus",
		"click #save_kase_status": "saveKaseStatus",
		"click #save_kase_sub_status": "saveKaseSubStatus",
		"click .kase_status_checkbox": "activateKaseStatus",
		"click .kase_status_cell": "editKaseStatus",
		"click .kase_status_save": "updateKaseStatus",
		"click #show_all_status": "showAllStatus"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_status_holder");
			var view = "kase_sub_status_listing";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		// JAY last code 1
		// console.log('my victime');
		// console.log(kase);


		var self = this;

		//what are we looking for?

		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		var arrFilters = this.collection.toJSON();
		var arrRows = [];
		var current_status = $("#case_" + status_level + "statusInput").val();
		// console.console.log(<%=case_substatus%>);

		var arrOptions = [];
		var option_selected = "";
		if (current_status == "") {
			option_selected = " selected";
		}
		var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);

		//arrFilters.forEach(function(element, index, array) {

		_.each(arrFilters, function (kase_status) {
			//console.log(kase_status.law);
			selected_case_type = $("#case_typeInput :selected").val();
			// console.log(selected_case_type);
			if (typeof kase_status.id != "undefined") {
				if (kase_status.law.toLowerCase() == selected_case_type.toLowerCase() || ((kase_status.law.toLowerCase() == "pi" || kase_status.law.toLowerCase() == "newpi") && (selected_case_type.toLowerCase() == "newpi" || selected_case_type.toLowerCase() == "pi"))) {

					var index = kase_status.id
					var checked = " checked";
					var row_display = "";
					var cell_display = "color:white";
					var row_class = "active_filter";
					if (kase_status.deleted == "Y") {
						checked = "";
						row_display = "display:none";
						cell_display = "color:red; text-decoration:line-through;";
						row_class = "deleted_filter";
					}
					if (kase_status.law.toLowerCase() == "wcab") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' selected=''>WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "NewPI") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI' selected=''>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "civil") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil' selected=''>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "employment_law") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law' selected=''>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "immigration") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration' selected=''>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "pi") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi' selected=''>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "social_security") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security' selected=''>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "WCAB_Defense") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense' selected=''>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "class_action") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' selected=''>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					else {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value='' selected=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' >Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}

					var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='kase_status'><input type='checkbox' class='kase_status_checkbox hidden' value='Y' title='Uncheck to stop using this status.  Old records currently using this status will not be affected' id='kase_status_" + index + "' name='kase_status_" + index + "'" + checked + "></td><td class='kase_status' style='" + cell_display + "'><span id='kase_status_span_" + index + "' class='kase_status_cell' style='cursor:pointer' title='Click to edit this status'>" + kase_status.status + "</span>" + input + "</td></tr>";
					arrRows.push(therow);

					//are we using a deleted status
					var current_status = self.model.get("case_status");
					var current_status_only_for_cond = $("#case_substatusInput :selected").val();

					var blnUsingDeleted = (arrDeletedKaseStatus.indexOf(current_status) > -1);

					if (kase_status.deleted != "Y" || blnUsingDeleted) {
						//the drop down has to match
						var option_selected = "";
						console.log(kase_status.status + " == " + current_status_only_for_cond);
						if (kase_status.status == current_status_only_for_cond) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
						arrOptions.push(option);
					}

				}
			}
		});
		var html = "<table id='kase_status_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({ html: html, status_level: status_level }));
			if (status_level == "") {
				$("#case_statusInput").html(arrOptions.join("\r\n"));
				//reset global
				casestatus_options = arrOptions.join("\r\n");
			} else {
				$("#case_substatusInput").html(arrOptions.join("\r\n"));

				// //i have to go through the loop one more time to set the selected for subsub
				// var current_status = $("#case_subsubstatusInput").val();
				// var arrSubOptions = [];
				// if (current_status=="") {
				// 	option_selected = " selected";
				// }
				// var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
				// arrSubOptions.push(option);
				// _.each(arrFilters , function(kase_status) {
				// 	if (kase_status.deleted!="Y") {
				// 		//the drop down has to match
				// 		var option_selected = "";
				// 		if (kase_status.status == current_status) {
				// 			option_selected = " selected";
				// 		}
				// 		var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
				// 		arrSubOptions.push(option);
				// 	}
				// });
				// $("#case_subsubstatusInput").html(arrSubOptions.join("\r\n"));
			}
		}
		catch (err) {
			alert(err);

			return false;
		}
		return this;
	},
	selectAll: function (event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editKaseStatus: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];

		//not open nor closed
		var current = $("#kase_status_value_" + casestatus_id).val();
		if (current == "Open" || current == "Closed") {
			return;
		}
		$("#kase_status_span_" + casestatus_id).fadeOut();
		$("#kase_status_value_" + casestatus_id).toggleClass("hidden");
		$("#caseTypeInput" + casestatus_id).toggleClass("hidden");
		$("#kase_status_save_" + casestatus_id).toggleClass("hidden");
		$("#kase_status_" + casestatus_id).toggleClass("hidden");

	},
	showAllStatus: function () {
		$("#show_all_status").hide();
		$(".deleted_filter").show();
	},
	newKaseSubStatus: function (event) {
		event.preventDefault();
		$("#new_kase_sub_status_button").fadeOut();
		$("#new_kase_sub_status_holder").fadeIn(function () {
			$("#new_kase_sub_status").focus();
		});
	},
	saveKaseStatus: function (event) {
		event.preventDefault();
		var url = 'api/statusfilter/add';
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var casestatus = $("#new_kase_sub_status").val();
		var formValues = "casestatus=" + encodeURIComponent(casestatus) + "&status_level=" + status_level;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#manage_" + status_level + "status").trigger("click");
				}
			}
		});

		//
	},
	saveKaseSubStatus: function (event) {

		event.preventDefault();
		var url = 'api/substatusfilter/add';
		var mymodel = this.model.toJSON();
		// var status_level = mymodel.status_level;
		// alert(status_level);
		var status_level = "casesubstatus";
		var casetype = $('#caseTypeInput :selected').val();
		var casestatus = $("#new_kase_sub_status").val();
		var formValues = "casestatus=" + encodeURIComponent(casestatus) + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null || casetype == "" || casetype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(document.cookie);

						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_status1").trigger("click");
					}
				}
			});
		}
		//
	},
	activateKaseStatus: function (event) {
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		console.log(document.cookie);

		document.cookie = "changes_need_render_new_kase_view=1";
		$("#kase_status_save_" + casestatus_id).trigger("click");
	},
	updateKaseStatus: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("kase_status_" + casestatus_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casetype = $('#caseTypeInput' + casestatus_id + ' :selected').val();
		var casestatus = $("#kase_status_value_" + casestatus_id).val();
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		var url = 'api/statusfilter/update';
		var formValues = "casestatus_id=" + casestatus_id + "&casestatus=" + encodeURIComponent(casestatus) + "&deleted=" + deleted + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null || casetype == "" || casetype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(document.cookie);

						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_status1").trigger("click");
					}
				}
			});
		}
	}
});
window.kase_sub_sub_status_listing = Backbone.View.extend({
	initialize: function () {
	},
	events: {
		/*"click #select_all_filters":		"selectAll",*/
		"click #new_kase_sub_sub_status_button": "newKaseSubSubStatus",
		"click #save_kase_status": "saveKaseStatus",
		"click #save_kase_sub_sub_status": "saveKaseSubSubStatus",
		"click .kase_status_checkbox": "activateKaseStatus",
		"click .kase_status_cell": "editKaseStatus",
		"click .kase_status_save": "updateKaseStatus",
		"click #show_all_status": "showAllStatus"
	},
	render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "manage_status_holder");
			var view = "kase_sub_sub_status_listing";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		// JAY last code 1
		// console.log('my victime');
		// console.log(kase);


		var self = this;

		//what are we looking for?

		//let's cycle through the types
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		var arrFilters = this.collection.toJSON();
		var arrRows = [];
		var current_status = $("#case_subsubstatusInput").val();
		// console.console.log(<%=case_substatus%>);

		var arrOptions = [];
		var option_selected = "";
		if (current_status == "") {
			option_selected = " selected";
		}
		var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
		arrOptions.push(option);

		//arrFilters.forEach(function(element, index, array) {

		_.each(arrFilters, function (kase_status) {
			//console.log(kase_status.law);
			selected_case_type = $("#case_typeInput :selected").val();
			// console.log(selected_case_type);
			if (typeof kase_status.id != "undefined") {
				if (kase_status.law.toLowerCase() == selected_case_type.toLowerCase() || ((kase_status.law.toLowerCase() == "pi" || kase_status.law.toLowerCase() == "newpi") && (selected_case_type.toLowerCase() == "newpi" || selected_case_type.toLowerCase() == "pi"))) {

					var index = kase_status.id
					var checked = " checked";
					var row_display = "";
					var cell_display = "color:white";
					var row_class = "active_filter";
					if (kase_status.deleted == "Y") {
						checked = "";
						row_display = "display:none";
						cell_display = "color:red; text-decoration:line-through;";
						row_class = "deleted_filter";
					}
					if (kase_status.law.toLowerCase() == "wcab") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' selected=''>WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "NewPI") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI' selected=''>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "civil") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil' selected=''>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "employment_law") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law' selected=''>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "immigration") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration' selected=''>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "pi") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi' selected=''>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "social_security") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security' selected=''>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "WCAB_Defense") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense' selected=''>WCAB Defense</option>				<option value='class_action'>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					} else if (kase_status.law.toLowerCase() == "class_action") {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' selected=''>Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}
					else {
						var input = "<input class='hidden' type='text' id='kase_status_value_" + index + "' value='" + kase_status.status + "' />&nbsp;<select name='caseTypeInput" + index + "' id='caseTypeInput" + index + "' required='' class='hidden'>				<option value='' selected=''>Select from List</option>				<option value='wcab' >WCAB</option>				<option value='NewPI'>DUI</option>				<option value='civil'>Civil</option>				<option value='employment_law'>Employment Law</option>				<option value='immigration'>Immigration</option>				<option value='pi'>Personal Injury</option>				<option value='social_security'>Social Security</option>				<option value='WCAB_Defense'>WCAB Defense</option>				<option value='class_action' >Class Action</option></select><button class='btn btn-xs btn-success kase_status_save hidden' id='kase_status_save_" + index + "'>Save</button>";
					}

					var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='kase_status'><input type='checkbox' class='kase_status_checkbox hidden' value='Y' title='Uncheck to stop using this status.  Old records currently using this status will not be affected' id='kase_status_" + index + "' name='kase_status_" + index + "'" + checked + "></td><td class='kase_status' style='" + cell_display + "'><span id='kase_status_span_" + index + "' class='kase_status_cell' style='cursor:pointer' title='Click to edit this status'>" + kase_status.status + "</span>" + input + "</td></tr>";
					arrRows.push(therow);

					//are we using a deleted status
					var current_status = self.model.get("case_status");
					var current_status_only_for_cond = $("#case_subsubstatusInput :selected").val();

					var blnUsingDeleted = (arrDeletedKaseStatus.indexOf(current_status) > -1);

					if (kase_status.deleted != "Y" || blnUsingDeleted) {
						//the drop down has to match
						var option_selected = "";
						console.log(kase_status.status + " == " + current_status_only_for_cond);
						if (kase_status.status == current_status_only_for_cond) {
							option_selected = " selected";
						}
						var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
						arrOptions.push(option);
					}

				}
			}
		});
		var html = "<table id='kase_status_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({ html: html, status_level: status_level }));
			if (status_level == "") {
				$("#case_subsubstatusInput").html(arrOptions.join("\r\n"));
				//reset global
				casestatus_options = arrOptions.join("\r\n");
			} else {
				$("#case_subsubstatusInput").html(arrOptions.join("\r\n"));

				// //i have to go through the loop one more time to set the selected for subsub
				// var current_status = $("#case_subsubstatusInput").val();
				// var arrSubOptions = [];
				// if (current_status=="") {
				// 	option_selected = " selected";
				// }
				// var option = '<option value="" class="wcab_status_option"' + option_selected + '>Select from List</option>';
				// arrSubOptions.push(option);
				// _.each(arrFilters , function(kase_status) {
				// 	if (kase_status.deleted!="Y") {
				// 		//the drop down has to match
				// 		var option_selected = "";
				// 		if (kase_status.status == current_status) {
				// 			option_selected = " selected";
				// 		}
				// 		var option = '<option value="' + kase_status.status + '" class="wcab_status_option"' + option_selected + '>' + kase_status.status + '</option>';
				// 		arrSubOptions.push(option);
				// 	}
				// });
				// $("#case_subsubstatusInput").html(arrSubOptions.join("\r\n"));
			}
		}
		catch (err) {
			alert(err);

			return false;
		}
		return this;
	},
	selectAll: function (event) {
		var element = event.currentTarget;
		$(".document_filter_checkbox").prop("checked", element.checked);
	},
	editKaseStatus: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];

		//not open nor closed
		var current = $("#kase_status_value_" + casestatus_id).val();
		if (current == "Open" || current == "Closed") {
			return;
		}
		$("#kase_status_span_" + casestatus_id).fadeOut();
		$("#kase_status_value_" + casestatus_id).toggleClass("hidden");
		$("#caseTypeInput" + casestatus_id).toggleClass("hidden");
		$("#kase_status_save_" + casestatus_id).toggleClass("hidden");
		$("#kase_status_" + casestatus_id).toggleClass("hidden");

	},
	showAllStatus: function () {
		$("#show_all_status").hide();
		$(".deleted_filter").show();
	},
	newKaseSubSubStatus: function (event) {
		event.preventDefault();
		$("#new_kase_sub_sub_status_button").fadeOut();
		$("#new_kase_sub_sub_status_holder").fadeIn(function () {
			$("#new_kase_sub_sub_status").focus();
		});
	},
	saveKaseStatus: function (event) {
		event.preventDefault();
		var url = 'api/statusfilter/add';
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;

		var casestatus = $("#new_kase_sub_status").val();
		var formValues = "casestatus=" + encodeURIComponent(casestatus) + "&status_level=" + status_level;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#manage_" + status_level + "status").trigger("click");
				}
			}
		});

		//
	},
	saveKaseSubSubStatus: function (event) {

		event.preventDefault();
		var url = 'api/subsubstatusfilter/add';
		var mymodel = this.model.toJSON();
		// var status_level = mymodel.status_level;
		// alert(status_level);
		var status_level = "casesubsubstatus";
		var casetype = $('#caseTypeInput :selected').val();
		var casestatus = $("#new_kase_sub_sub_status").val();
		var formValues = "casestatus=" + encodeURIComponent(casestatus) + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null || casetype == "" || casetype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(document.cookie);

						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_status2").trigger("click");
					}
				}
			});
		}
		//
	},
	activateKaseStatus: function (event) {
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		console.log(document.cookie);

		document.cookie = "changes_need_render_new_kase_view=1";
		$("#kase_status_save_" + casestatus_id).trigger("click");
	},
	updateKaseStatus: function (event) {
		event.preventDefault();
		var element = event.currentTarget;

		var arrID = element.id.split("_");
		var casestatus_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("kase_status_" + casestatus_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var casetype = $('#caseTypeInput' + casestatus_id + ' :selected').val();
		var casestatus = $("#kase_status_value_" + casestatus_id).val();
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		var url = 'api/subsubstatusfilter/update';
		var formValues = "casestatus_id=" + casestatus_id + "&casestatus=" + encodeURIComponent(casestatus) + "&deleted=" + deleted + "&status_level=" + status_level + "&casetype=" + casetype;

		if (casestatus == "" || casestatus == null || casetype == "" || casetype == null) {
			alert('Please enter valid value');
		} else {
			$.ajax({
				url: url,
				type: 'POST',
				dataType: "json",
				data: formValues,
				success: function (data) {
					if (data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						console.log(document.cookie);

						document.cookie = "changes_need_render_new_kase_view=1";
						$("#manage_status2").trigger("click");
					}
				}
			});
		}
	}
});
window.claim_view = Backbone.View.extend({
	initialize: function () {

	},
	events: {
		"click .save": "saveClaim",
		"click #new_surgery": "newSurgery",
		"change .other_select": "showOtherBoxes",
		"keyup .claim_sync": "updateInjuryView",
		"blur #claim_form input": "autoSave",
		"click #claim_all_done": "doTimeouts"
	},
	render: function () {
		if (typeof this.template != "function") {
			var view = "claim_view";
			var extension = "php";

			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;

		var blnIntake = (document.location.hash == "#intake");
		this.model.set("intake_screen", blnIntake);


		var mymodel = this.model.toJSON();

		try {
			$(this.el).html(this.template(mymodel));
		}
		catch (err) {
			alert(err);

			return "";
		}
		return this;
	},
	newSurgery: function (event) {
		event.preventDefault();

		composeSurgery("compose_surgery_-1", current_case_id);
	},
	autoSave: function (event) {
		event.preventDefault();
		if (!blnAutoSave) {
			return;
		}
		if (!this.model.get("intake_screen")) {
			return;
		}

		saveSSN(current_case_id);
	},
	saveClaim: function (event) {
		event.preventDefault();

		$(".button_row.ssn_claim").toggleClass("hidden");
		$("#gifsave").show();

		saveSSN(current_case_id);
	},
	updateInjuryView: function (event) {
		var blnIntake = (document.location.hash.indexOf("#intake") > -1);

		if (blnIntake) {
			var element = event.currentTarget;

			switch (element.id) {
				case "claim_doi":
					destination = ".injury_view #start_dateInput";
					break;
				case "claim_occupation":
					destination = ".injury_view #occupationInput";
					break;
				case "claim_comments":
					destination = ".injury_view #explanationInput";
					break;
			}
			$(destination).val(element.value);
		}
	},
	showOtherBoxes: function () {
		var display = "display:none";
		if ($("#claim_benefits").val() == "Other") {
			display = "";
		}
		$("#claim_benefits_other").css("display", display);

		display = "display:none";
		if ($("#claim_stage").val() == "Other") {
			display = "";
		}
		$("#claim_stage_other").css("display", display);

		display = "display:none";
		if ($("#claim_type").val() == "Other") {
			display = "";
		}
		$("#claim_type_other").css("display", display);

		display = "display:none";
		if ($("#wc_status").val() == "Other") {
			display = "";
		}
		$("#wc_status_other").css("display", display);
	},
	doTimeouts: function () {
		var self = this;

		var blnIntake = (document.location.hash.indexOf("#intake") > -1);

		//datepickIt(".claim_date", false);
		$(".claim_date").datetimepicker({
			timepicker: false,
			format: "m/d/Y",
			mask: false,
			maxDate: 0,
			onChangeDateTime: function (dp, $input) {
				//alert($input.val());
				$("#claim_doi").trigger("keyup");
			}
		});

		if (!this.model.get("embedded")) {
			$(".edit_row.ssn_claim").hide();
			$(".button_row.ssn_claim").toggleClass("hidden");
			$("#sub_category_holder_ssn_claim").css("margin-bottom", "20px");
			$("#sub_category_holder_ssn_claim").css("border-bottom", "1px solid white");
		}

		$("#claim_view_table td").css("padding-bottom", "5px");

		if (blnIntake) {
			$(".claim_input").css("width", "381px");
			$(".claim_small_input").css("width", "131px");
		}
		//fill in the data
		if (this.model.get("case_id") == "") {
			var claim = new SSNClaim({ case_id: "" });

			self.showOtherBoxes();
		} else {
			var claim = new SSNClaim({ case_id: this.model.get("case_id") });
			claim.fetch({
				success: function (data) {
					var mymodel = data.toJSON();

					$("#claim_id").val(mymodel.id);

					if (mymodel.claim_info == "") {
						return;
					}
					var jdata = JSON.parse(mymodel.claim_info);

					for (var key in jdata) {
						if (jdata.hasOwnProperty(key)) {
							if (key != "claim_id") {
								//console.log(key + " -> " + jdata[key]);

								var input_id = key;
								var input_value = jdata[key];

								if ($("#" + input_id).is(':checkbox')) {
									if (input_value == "on") {
										document.getElementById(input_id).checked = true;
									}
								} else {
									if ($("#" + input_id).length > 0) {
										if ($("#" + input_id).hasClass("claim_date")) {
											//if (input_id.indexOf("_date") > -1 || input_id.indexOf("_denial") > -1 || input_id.indexOf("_doi") > -1) {
											if (input_value != "" && input_value != "0000-00-00") {
												input_value = moment(input_value).format("MM/DD/YYYY");
											} else {
												input_value = "";
											}
										}
										$("#" + input_id).val(input_value);
									}
								}
							}
						}
					}

					self.showOtherBoxes();
				}
			});
		}
	}
});
function makeSureIntakeSaved() {
	if (!blnAutoSave) {
		return;
	}
	//return;
	//see if it's been autosaved, if not intake-add it
	var table_id = $("#kase_form #id").val();
	if (table_id == "") {
		$("#phone_intake_feedback_div").html("Autosaving...");
		var url = "api/kase/addintake";

		var case_type = $("#case_typeInput").val();
		$("#case_typeSpan").html(case_type);
		$("#case_typeInput").fadeOut(function () {
			$("#case_typeSpan").fadeIn();
		});
		var file_number = $("#file_numberInput").val();
		var formValues = "case_type=" + case_type + "&file_number=" + file_number;

		$.ajax({
			url: url,
			type: 'POST',
			dataType: "json",
			data: formValues,
			success: function (data) {
				if (data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if (data.success) {
						current_case_id = data.case_id;
						$("#kase_form #id").val(data.case_id);

						var kase = new Kase({ id: current_case_id });
						kase.fetch({
							success: function (kase) {
								if (kase.toJSON().uuid != "") {
									kases.remove(kase.id); kases.add(kase);
								}
								return;
							}
						});

						//update the other screens
						$("#injury_form #table_id").val(data.injury_id);

						$("#injury_form #case_id").val(data.case_id);
						$("#personal_injury_form #case_id").val(data.case_id);
						$("#person_form #case_id").val(data.case_id);
						$("#Employer_form #case_id").val(data.case_id);
						$("#Carrier_form #case_id").val(data.case_id);
						$("#Plaintiff_form #case_id").val(data.case_id);

						//let's show the event, task buttons
						$("#intake_schedule_holder").css("display", "inline-block");

						$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
						var label_element = document.getElementById("case_name_label").children[0];
						label_element.style.color = "lime";
						setTimeout(function () {
							label_element.style.color = "white";
							$("#phone_intake_feedback_div").html("");
						}, 1500);
					} else {
						alert("There was an error autosaving the Intake screen:\r\n\r\n" + data.error);
					}
				}
			}
		});
	}
}