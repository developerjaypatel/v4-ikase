window.exam_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		//new button click
		"click #compose_exam": 							"newExam",
		"click .delete_exam":							"confirmdeleteExam",
		"click .edit_exam":								"editExam",
		"click #compose_report":						"createReport",
		"click #select_all_exams":						"selectAll",
		"click .select_exam":							"showComposeReport",
		"keyup .exam_edit":								"showSaveLink",
		"click .save_icon":								"saveRecordType"
	},
    render:function () {		
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "exam_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		var exams = this.collection.toJSON();
		_.each( exams, function(exam) {
			if (isDate(exam.exam_dateandtime)) {
				if (exam.exam_dateandtime.indexOf("00:00:00") > 0) {
					exam.exam_dateandtime = moment(exam.exam_dateandtime).format("MM/DD/YYYY");
				} else {
					exam.exam_dateandtime = moment(exam.exam_dateandtime).format("MM/DD/YYYY h:mma");
				}
			} else {
				exam.exam_dateandtime = "";
			}
			if (isDate(exam.fs_date) && exam.fs_date!="0000-00-00" && exam.fs_date!="1969-12-31") {
				exam.fs_date = moment(exam.fs_date).format("MM/DD/YYYY");
			} else {
				exam.fs_date = "";
			}
			exam.attachment_link = "";
			if (exam.document_filename!="") {
				var standard_link = '<span onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'exam' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + exam.document_filename + String.fromCharCode(39) + ', ' + self.model.get("case_id") + ', ' + customer_id + ')" onmouseout="hideMessagePreview()"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:true" class="glyphicon glyphicon-paperclip"></i></span>&nbsp;';
							
				exam.attachment_link = standard_link;
			}
			exam.attachment_link += '<input type="checkbox" id="select_exam_' + exam.id + '" class="select_exam" value="Y">';
		});
		
		try {
			$(this.el).html(this.template({exams: exams}));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
				
		setTimeout(function() {
			tableSortIt();
			
			var kase = kases.findWhere({ case_id: self.model.get("case_id") });
			
			var parties = new Parties([], { case_id: kase.get("case_id"), case_uuid: kase.get("uuid") });
			parties.fetch({
				success: function(parties) {
					var medical_providers = parties.where({"type": "medical_provider"});
					if (medical_providers.length==0) {
						$("#new_exam_holder").html("<span class='white_text' style='padding-left:150px;'><span style='background:orange'>A Medical Provider is required before you can add Medical Index information.</span></span>");
					}
				}
			});
		}, 100);
		
		var case_id = current_case_id;
		var kase = kases.findWhere({case_id: case_id});
		
		var case_status = kase.toJSON().case_status;
		var case_substatus = kase.toJSON().case_substatus;
		var attorney = kase.toJSON().attorney;
		var worker = kase.toJSON().worker;
		var rating = kase.toJSON().rating;
		//var kase = kases.findWhere({case_id: this.model.get("case_id")});
		this.model.set("case_status", case_status);
		this.model.set("case_substatus", case_substatus);
		this.model.set("attorney", attorney);
		this.model.set("worker", worker);
		this.model.set("rating", rating);
		
		setTimeout(function() {
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
		
		var parties = new Parties([], { case_id: this.model.get("case_id"), case_uuid: this.model.get("uuid"), panel_title: ""});
		parties.fetch({
			success: function(parties) {
				var claim_number = "";
				var carrier_insurance_type_option = "";
				//now we have to get the adhocs for the carrier
				var carrier_partie = parties.findWhere({"type": "carrier"});
				if (typeof carrier_partie == "undefined") {
					carrier_partie = new Corporation({ case_id: self.model.get("case_id"), type:"carrier" });
					carrier_partie.set("corporation_id", -1);
					carrier_partie.set("partie_type", "Carrier");
					carrier_partie.set("color", "_card_missing");
				}
				carrier_partie.adhocs = new AdhocCollection([], {case_id: case_id, corporation_id: carrier_partie.attributes.corporation_id});
				carrier_partie.adhocs.fetch({
					success:function (adhocs) {
						var adhoc_claim_number = adhocs.findWhere({"adhoc": "claim_number"});
						
						if (typeof adhoc_claim_number != "undefined") {
							claim_number = adhoc_claim_number.get("adhoc_value");
						}
						
						var adhoc_carrier_insurance_type_option = adhocs.findWhere({"adhoc": "insurance_type_option"});
						
						if (typeof adhoc_carrier_insurance_type_option != "undefined") {
							carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
						}
						var arrClaimNumber = [];
						var arrCarrierInsuranceTypeOption = [];
						if (carrier_partie.attributes.claim_number!="" && carrier_partie.attributes.claim_number!=null) {
							//arrClaimNumber.push(partie.claim_number);
							var claim_number = carrier_partie.attributes.claim_number;
							$("#claim_number_fill_in").html(claim_number);
							kase.set("claim_number", claim_number);
						}
					}
				});
			}
		});
		//}
		setTimeout(function() {
			self.model.set("hide_upload", true);
			showKaseAbstract(self.model);
			
			var panel_height = window.innerHeight - 320;
			$("#exam_list_outer_div").css("height", panel_height + "px");
			$("#preview_pane_holder").css("height", panel_height + "px");
			
			$(".tablesorter").css("background", "url(../img/glass_dark.png)");
		}, 750);
		
		return this;
    },
	confirmdeleteExam: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		composeDelete(id, "exam");
	},
	newExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var corp_id = "-1";
		composeExam(element.id, corp_id);
	},
	editExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var element_id = element.id;
		var arrID = element_id.split("_");
		var corp_id = arrID[arrID.length - 1];
		
		var kase = kases.findWhere({case_id: current_case_id});
		if (typeof kase == "undefined") {
			var kase =  new Kase({id: current_case_id});
			kase.fetch({
				success: function (kase) {
					if (kase.toJSON().uuid!="") {
						kases.remove(kase.id); kases.add(kase);
						composeExam(element.id, corp_id);
					} else {
						//case does not exist, get out
						document.location.href = "#";
					}
					return;		
				}
			});
			return;
		}
		composeExam(element.id, corp_id);
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".select_exam").prop("checked", element.checked);
		
		this.showComposeReport(event);
		
		$(".exam_comments").hide();
		$(".exam_edit").show();
	},
	showComposeReport: function (event) {
		var element = event.currentTarget;
		$("#medindex_instructions").hide();
		$("#compose_report").show();
		
		if (element.id=="select_all_exams") {
			return;
		}
		
		var arrID = element.id.split("_");
		var exam_id = arrID[arrID.length - 1];
		
		$("#exam_comments_" + exam_id).fadeOut(function() {
			$("#exam_edit_" + exam_id).fadeIn();
			$("#edit_instructions_" + exam_id).fadeIn();
			
			setTimeout(function() {
				$("#edit_instructions_" + exam_id).fadeOut();
			}, 2500);
		});
	},
	showSaveLink: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var exam_id = arrID[arrID.length - 1];
		
		$("#disabled_save_" + exam_id).fadeOut(function() {
			$("#document_save_" + exam_id).fadeIn();
		});
	},
	saveRecordType: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var exam_id = arrID[arrID.length - 1];
		var comments = $("#exam_edit_" + exam_id).val();
		var url = "api/exams/update";
		var formValues = "table_name=exam&table_id=" + exam_id + "&comments=" + encodeURIComponent(comments);
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".exam_row_" + exam_id).css("background", "green");
					
					$("#document_save_" + exam_id).fadeOut(function() {
						$("#disabled_save_" + exam_id).fadeIn();
					});
					setTimeout(function() {
						$(".exam_row_" + exam_id)[0].style.background = "url(../img/glass_row.png)";
						$(".exam_row_" + exam_id)[1].style.background = "url(../img/glass_row_shade.png)";
						//css("background", original_background);
					}, 2500);
				}
			}
		});
	},
	createReport: function(event) {
		var self = this;
		
		event.preventDefault();
		$("#compose_report").hide();
		$("#medindex_holder").html("Generating...");
		//perform an ajax call to track views by current user
		var url = 'api/letter/create';
		formValues = "table_name=letter&table_id=medindex&case_id=" + current_case_id;
		//tack on the exam ids
		var select_exams = $(".select_exam");
		var arrExamID = [];
		if (select_exams.length > 0) {
			var arrLength = select_exams.length;
			for(var i = 0; i < arrLength; i++) {
				var element = select_exams[i];
				if (element.checked) {
					arrExamID.push(element.id.replace("select_exam_", ""));
				}
			}
		}
		if (arrExamID.length > 0) {
			formValues += "&exam_ids=" + arrExamID.join("|");
		} else {
			$("#medindex_holder").html("");
			$("#compose_report").show();
			alert("Select Exams to proceed");
			return;
		}
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#medindex_holder").html("Converting...");
					self.model.set("pdfcheck", 0);
					self.model.set("zip_path", data.zip_path);
					self.reviewPDF(data.success);
				}
			}
		});
	},
	reviewPDF: function(path) {
		var self = this;
		
		if (self.model.get("pdfcheck") > 7) {
			
			$("#medindex_holder").html("Failed...");
			setTimeout(function() {
				$("#compose_report").show();
			}, 1500);
			
			alert("PDF not converted");
			return;
		}
		
		//check if the file is ready
		var arrPath = path.split("/");
		var file = arrPath[arrPath.length - 1];
		var url = 'api/letter/ready';
		formValues = "path=" + file + "&case_id=" + current_case_id;
		
		var pdfcheck = self.model.get("pdfcheck");
		pdfcheck++;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					if (data.success) {
						$("#medindex_holder").html("");
						$("#compose_report").show();
						
						var zip_path = self.model.get("zip_path");
						if (zip_path!="No Attachments") {						
							var arrZip = zip_path.split("\\");
							
							zip_path = arrZip[4] + "/" + arrZip[5] + "/" + arrZip[6] + "/" + arrZip[7] + "/" + arrZip[8]; 
							
							var href = "zips";
							var case_id = arrZip[7];
							var file = arrZip[8];
							var zip_link = "<a href='../" + zip_path + "' style='text-decoration:underline' class='white_text' target='_blank'>Download Zip File</a>";
						} else {
							var zip_link = zip_path;
						}
						//window.open(path + ".pdf");
						var panel_height = window.innerHeight - 420;
						var iframe = "<iframe src='" + path + ".pdf' width='100%' height='" + panel_height + "px' frameborder='0' scrolling='yes' id='medindex_preview_frame_" + current_case_id + "'></iframe>";
						
						//var iframe = "<a href='" + path + ".pdf' id='medindex_preview_frame_" + current_case_id + "' class='white_text'>Review PDF</a>";
						
						$("#preview_pane").html(iframe);
						$("#preview_title").html("<div style='float:right; font-size: 0.8em'>" + zip_link + "</div>Med Index Report");
						$("#exam_list_outer_div").css("width", "44%");
						$("#preview_pane_holder").show();
					} else {
						$("#medindex_holder").html("Checking ..." + pdfcheck);						
						self.model.set("pdfcheck", pdfcheck);
						setTimeout(function() {
							self.reviewPDF(path);
						}, 1500);
					
					}
				}
			}
		});
	}
});
window.med_index_report = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
    render:function () {		
		var self = this;
		var exams = this.collection.toJSON();
		var applicant_name = "";
		if (exams.length > 0) {
			applicant_name = exams[0].full_name;
			if (applicant_name=="" && exams[0].first_name) {
				applicant_name = exams[0].first_name + " " + exams[1].last_name;
			}
		}
		var case_number = exams[0].case_number;
		_.each( exams, function(exam) {
			if (isDate(exam.exam_dateandtime)) {
				if (exam.exam_dateandtime.indexOf("00:00:00") > 0) {
					exam.exam_dateandtime = moment(exam.exam_dateandtime).format("MM/DD/YYYY");
				} else {
					exam.exam_dateandtime = moment(exam.exam_dateandtime).format("MM/DD/YYYY h:mma");
				}
			} else {
				exam.exam_dateandtime = "";
			}
			if (isDate(exam.fs_date) && exam.fs_date!="0000-00-00" && exam.fs_date!="1969-12-31") {
				exam.fs_date = moment(exam.fs_date).format("MM/DD/YYYY");
			} else {
				exam.fs_date = "";
			}
		});
		
		try {
			$(this.el).html(this.template({exams: exams, case_number: case_number, applicant_name: applicant_name}));
		}
		catch(err) {
			var view = "exam_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
    }

});
window.exam_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
        "click .exam .delete":						"deleteExamView",
		"click .exam .save":						"saveExam",
		"click .exam .save_field":					"saveExamViewField",
		"click .exam .edit": 						"toggleExamEdit",
		"click .exam .reset": 						"resetExamForm",
		"dblclick .exam .gridster_border": 			"editExamViewField",
		"change #primary":							"updateCorporationID",
		"click #exam_all_done":						"doTimeouts"
    },
    render: function () {
		var mymodel = this.model.toJSON();
		var self = this;
		if (typeof this.template != "function") {
			var view = "exam_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		if (mymodel.exam_dateandtime!="") {
			if (isDate(mymodel.exam_dateandtime)) {
				self.model.set("exam_dateandtime", moment(mymodel.exam_dateandtime).format("MM/DD/YYYY h:mma"));
			} else {
				self.model.set("exam_dateandtime", "");
			}
		}
		if (mymodel.fs_date!="" && mymodel.fs_date!="0000-00-00" && mymodel.fs_date!="1969-12-31") {
			if (isDate(mymodel.fs_date)) {
				self.model.set("fs_date", moment(mymodel.fs_date).format("MM/DD/YYYY"));
			}
		} else {
			self.model.set("fs_date", "");
		}
		self.model.set("permanent_stationary_checked", "");
		if (mymodel.permanent_stationary=="Y") {
			self.model.set("permanent_stationary_checked", " CHECKED");
		}
		
		//document
		if (mymodel.document_filename!="") {
			var attach_link = "api/preview_attach.php?file=" +  encodeURIComponent(mymodel.document_filename) + "&case_id=" + mymodel.case_id;
			/*
			var standard_link = '<span onmouseover="showAttachmentPreview(' + String.fromCharCode(39) + 'exam' + String.fromCharCode(39) + ', event, ' + String.fromCharCode(39) + document_filename + String.fromCharCode(39) + ', ' + mymodel.case_id + ', ' + customer_id + ')" onmouseout="hideMessagePreview()"><i style="font-size:15px;color:#FFFFFF; cursor:pointer; visibility:true" class="glyphicon glyphicon-paperclip"></i></span>';
			*/		
			self.model.set("attachment_link", attach_link);
		} else {
			self.model.set("attachment_link", "");
		}
		
		if (typeof mymodel.document_id == "undefined") {
			self.model.set("document_id", "");
		}
		var corporation_id = mymodel.corp_id;
		//if (corporation_id == "-1") {
			var kase = kases.findWhere({case_id: mymodel.case_id});
			self.model.set("case_name", kase.get("case_name"));
			var parties = new Parties([], { case_id: mymodel.case_id, case_uuid: kase.get("uuid") });
			parties.fetch({
				success: function(parties) {
					var medical_providers = parties.where({"type": "medical_provider"});
					
					if (typeof medical_providers == "undefined") {
						return;
					}
					medical_providers.sort(function (a, b) {
						var textA = a.toJSON().company_name.toUpperCase();
						var textB = b.toJSON().company_name.toUpperCase();
						
						return textA.localeCompare(textB);
					});

					var arrMedicalProviders = [];
					var selected = "";
					if (medical_providers.length==1) {
						selected = " selected";
					}
					_.each(medical_providers , function(medical_provider) {
						var themedical_provider = medical_provider.get("company_name");
						var selected = "";
						if (medical_provider.get("corporation_id") == corporation_id || medical_providers.length==1) {
							selected = " selected";
						}
						themedical_provider = "<option value='" + medical_provider.get("corporation_id") + "'" + selected + ">" + themedical_provider + "</option>";
						arrMedicalProviders[arrMedicalProviders.length] = themedical_provider;
					});
					self.model.set("medical_providers", arrMedicalProviders.join("\r\n"));
					//actuall draw the html
					try {
						$(self.el).html(self.template(self.model.toJSON()));		
					}
					catch(err) {
						alert(err);
						
						return "";
					}
					
				}
			});
		/*
		} else {
			self.model.set("medical_providers", "");
			try {
				$(self.el).html(self.template(self.model.toJSON()));		
			}
			catch(err) {
				var view = "exam_view";
				var extension = "php";
				
				loadTemplate(view, extension, self);
				
				return "";
			}
		}
		*/
		return this;
    },
	updateCorporationID: function(event) {
		var element = event.currentTarget;
		$(".exam_view #corp_id").val(element.value);
	},
	newExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeExam(element.id);
	} ,
	toggleExamEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//$("#address").show();
		//get all the editing fields, and toggle them back
		$(".exam .editing").toggleClass("hidden");
		$(".exam .span_class").removeClass("editing");
		$(".exam .input_class").removeClass("editing");
		
		$(".exam .span_class").toggleClass("hidden");
		$(".exam .input_class").toggleClass("hidden");
		$(".exam .input_holder").toggleClass("hidden");
		$(".button_row.exam").toggleClass("hidden");
		$(".edit_row .exam").toggleClass("hidden");
		
		showEndtime();
	},
	editExamField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".exam_" + field_name;
		}
		editField(element, master_class);
	},
	saveExam:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "exam", "exam");
		return;
    },
	resetExamForm: function(event) {
		event.preventDefault();
		this.toggleSettlementEdit(event);
		//this.render();
		//$("#address").hide();
	},
	doTimeouts: function() {
		var self = this;
		var mymodel = this.model.toJSON();
		//gridsterById('gridster_exam');
		
		if(mymodel.id=="" || mymodel.id==-1){	
			//$(".exam .edit").trigger("click"); 
			//$(".exam .delete").hide();
			//$(".exam .reset").hide();
		}
		$("#exam_dateandtimeInput").datetimepicker({ validateOnBlur:false, timepicker: false, format: "m/d/Y", allowTimes:workingWeekTimes,step:30, closeOnTimeSelect:true});
		$("#fs_dateInput").datetimepicker({ validateOnBlur:false, timepicker: false, format: "m/d/Y", allowTimes:workingWeekTimes,step:30, closeOnTimeSelect:true});
		
		$("#commentsInput").cleditor({
			width:540,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		/*		
		if (customer_id == 1033) {
			$("#billing_time_dropdownInput").editableSelect({
				onSelect: function (element) {
					var billing_time = $("#billing_time_dropdownInput").val();
					$("#billing_time").val(billing_time);
					//alert(billing_time);
				}
			});
		}
		*/
		var document_id = mymodel.document_id;
		
		if (document_id!="") {			
			if ($("#document_name_holder").html()=="") {
				var doc = new Document({id: document_id});
				doc.fetch({
					success:function (data) {
						var json = data.toJSON();
						$("#document_name_holder").html(json.document_name);			
					}
				});
			}
			
			$("#case_name_holder").html(self.model.get("case_name"));
			$(".attach_row").show();
		} else {
			//we need to upload attachments
			$('.exam #message_attachments').html(new message_attach({model: self.model}).render().el);
			setTimeout(function() {
				$(".message_attach_form #queue").css("height", "70px");
				$(".message_attach_form #queue").css("width", "550px");
			}, 700);
		}
	}
});