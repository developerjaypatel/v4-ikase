var referredout_attorney_parties;
var blnQuickNotes = false;

window.partie_cards_view = Backbone.View.extend({
    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"click #compose_task": 				"composeTasks",
		"click .delete_icon":				"confirmdeletePartie",
		"click .compose_new_exam": 			"composeExam",
		"click #add_case":					"composeRelated",
		"click #new_partie":				"newPartie",
		"click .delete_yes":				"deletePartie",
		"click .delete_no":					"canceldeletePartie",
		"click .compose_new_note": 			"newNotes",
		"click #expand_applicant_image":	"expandImage",
		"click .compose_new_envelope":		"newEnvelope",
		"click .compose_pdf_envelope":		"newPDFEnvelope",
		"click .compose_message":			"newMessage",
		"click #quick_notes":				"quickNotes",
		"click .edit_quicknote":			"editQuickNote",
		"click #related_cases":				"relatedKases",
		"click #search_qme":				"searchQME",
		"click #list_kases": 				"searchKases",
		"click .map_partie":				"mapPartie",
		"click #jetfile":					"jetFile",
		"click #review_rx":					"reviewRx",
		"click #send_matrix_link":			"sendMatrix",
		"click .list_kases_link":			"listKases",
		"click #file_location_button":		"showFileLocations",
		"click #medsum_button":				"showMedListing",
		"click #medsum_summary":			"medSummary",
		"change #file_location":			"saveFileLocation",
		"click #refer_vocational":			"referVocational",
		/*"click .copy_info":					"copyInfo",*/
		"click #cards_all_done":			"doTimeouts"
	},
    render:function () {		
		//console.log("before:" + moment().format("h:m:s"));
		var self = this;
		
		var case_id = this.model.toJSON().case_id;
		
		//is this kase in kases
		var kase = kases.findWhere({"case_id": case_id});
		if (typeof kase == "undefined") {
			kases.add(this.model.toJSON());
		}
		//dois
		var kase_dois = dois.where({case_id: case_id});
		
		if (current_case_id==-1) {
			current_case_id = case_id;
		}
		var kase_type = this.model.get("case_type");
		//var kase_adj = this.model.get("adj_number");
		
		var blnWCAB = isWCAB(kase_type);
		var blnWCABDefense = (kase_type.indexOf("WCAB_Defense") == 0);
		var blnImm = (kase_type=="immigration");
		var blnSSN = (kase_type=="social_security");
		this.model.set("blnSSN", blnSSN);
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnWCABDefense", blnWCABDefense);
		
		if (this.model.get("case_number")=="" && this.model.get("file_number")!="") {
			this.model.set("case_number", this.model.get("file_number"));
		}
		
		if (this.model.get("injury_type")==null) {
			this.model.set("injury_type", "");
		}
		//we need referred out attorneys for claims title
		referredout_attorney_parties = self.collection.where({"type": "referredout_attorney"});
		
		//sub in
		var case_description = this.model.get("case_description");
		
		self.model.set("sub_in_date", "");
		self.model.set("sub_out_date", "");
		
		if (case_description!="") {
			var json_description = JSON.parse(case_description);
			
			if (typeof json_description.sub_in_date!="undefined") {
				self.model.set("sub_in_date", json_description.sub_in_date);
				self.model.set("sub_out_date", json_description.sub_out_date);
			}
		}
		
		var arrDOIs = [];
		var arrADJs = [];
		var thedoi = "";
		var thelocation = "";
		var theadj = "";
		var theoccupation = "";
		var doi_locations = "";
		_.each(kase_dois , function(doi) {
			var thedoi = '<a href="#injury/' + doi.get("main_case_id") + '/' + doi.id + '" class="white_text" title="Click here to review Injury information">' + moment(doi.get("start_date")).format("MM/DD/YYYY");
			if (doi.get("end_date") != "0000-00-00") {
				thedoi += " - " + moment(doi.get("end_date")).format("MM/DD/YYYY") + " CT";
			}
			thedoi += '</a>';
			
			thelocation = doi.get("full_address");
			theadj = doi.get("adj_number");
			theoccupation = doi.get("occupation");
			
			if (arrDOIs.length > 0) {
				arrDOIs[arrDOIs.length] = "ADJ #:&nbsp;" + theadj + "<br>Occupation:&nbsp;" + theoccupation + "<br>DOI:&nbsp;" + thedoi + "<br>" + thelocation;
			} else {
				doi_locations = thelocation;
				arrDOIs[arrDOIs.length] = "ADJ #:&nbsp;" + theadj + "<br>Occupation:" + theoccupation + "<br>DOI:&nbsp;" + thedoi;
			}
			arrADJs[arrADJs.length] = theadj;
		});
		/*
		thedoi = arrDOIs.join("<br>");
		if (arrDOIs.length > 1) {
			thedoi = "<br>" + thedoi;
		}
		*/
		if (arrDOIs.length > 0) {
			/*
			//list the first one only, the others are in recent cases
			theadj = arrADJs[0];
			this.model.set("adj_number", theadj);
			arrADJs.splice(0, 1);
			
			thedoi = arrDOIs[0];
			this.model.set("dois", thedoi);
			arrDOIs.splice(0, 1);
			*/
			var suffix = ":";
			if (arrDOIs.length > 1) {
				suffix = "(s):<br>"
			}
			thedoi = arrDOIs.join("<hr>");
			this.model.set("related_dois", thedoi);	//"DOI" + suffix + 
			this.model.set("doi_locations", doi_locations);			
		} else {
			this.model.set("dois", thedoi);
			this.model.set("related_dois", thedoi);
			this.model.set("doi_locations", doi_locations);
		}
		//claims
		var arrayLength = 0;
		if (this.model.get("claims")!="") {
			var claims = this.model.get("claims").replace("Claims: ", "");
			var arrClaims = claims.split("|");
			arrayLength = arrClaims.length;
			for (var i = 0; i < arrayLength; i++) {
				var claim = arrClaims[i].split("~")[0];
				var inhouse = "N";
				if (typeof arrClaims[i].split("~")[1] != "undefined") {
					inhouse = arrClaims[i].split("~")[1];
				}
				switch(claim) {
					case "3P":
						if (inhouse!="Y") {
							claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:3P' title='Click to add a Referred Out Attorney' class='white_text'>Third Party</a></span>";
						} else {
							claim = "Third Party (In House)";
						}
						break;
					case "SER":
						if (inhouse!="Y") {
							claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:SER' title='Click to add a Referred Out Attorney' class='white_text'>Serious and Willful</a></span>";
						} else {
							claim = "SER (In House)";
						}
						break;
					default:
						if (inhouse!="Y") {
							claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:" + claim + "' title='Click to add a Referred Out Attorney' class='white_text'>" + claim + "</a></span>";
						} else {
							claim = claim + " (In House)";
						}
				}
				arrClaims[i] = claim;
			}
		}
		if (arrayLength == 0) {
			this.model.set("claims_display", "none");
		} else {
			this.model.set("claims_values", "Claims: " + arrClaims.join("; "));
			this.model.set("claims_display", "");
		}
		//now I have to get the injury stuff
		var injury = new Injury({case_id: this.collection.case_id});
		var specialty = "";
		var medical_provider_rating = "";
		var doctor_type = "";
		var assigned_to = "";
		
		var case_status = "";
		var case_substatus = "";
		var attorney = "";
		var worker = "";
		var rating = "";
		
		if (typeof this.collection.toJSON()[0]!="undefined") {
			case_status = this.collection.toJSON()[0].case_status;
			case_substatus = this.collection.toJSON()[0].case_substatus;
			attorney = this.collection.toJSON()[0].attorney;
			worker = this.collection.toJSON()[0].worker;
			rating = this.collection.toJSON()[0].rating;
		} else {
			if (typeof this.model.get("case_status")!="undefined") {
				case_status = this.model.get("case_status");
				case_substatus = this.model.get("case_substatus");
				attorney = this.model.get("attorney");
				worker = this.model.get("worker");
				rating = this.model.get("rating");
			}	
		}
		
		if (case_status.toLowerCase().indexOf("close") > -1) {
			case_status = "<span style='background:red;color:white'>" + case_status + "</span>";
		}
		
		//no missing for specific type lookup
		var blnSpecificType = (document.location.hash.indexOf("#partielist")==0);
								
		//get the injury
		injury.fetch({
			success: function (injury) {
				injury.set("case_id", self.collection.case_id);
				injury.set("case_uuid", self.collection.case_uuid);
				
				//now we have to get the adhocs for the medical_provider
				var medical_provider_partie = self.collection.findWhere({"type": "medical_provider"});
				if (typeof medical_provider_partie == "undefined") {
					medical_provider_partie = new Corporation({ case_id: self.collection.id, type:"medical_provider" });
				}
				medical_provider_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: medical_provider_partie.get("corporation_id")});
				medical_provider_partie.adhocs.fetch({
					success:function (adhocs) {
						var adhoc_specialty = adhocs.findWhere({"adhoc": "specialty"});
						if (typeof adhoc_specialty != "undefined") {
							specialty = adhoc_specialty.get("adhoc_value");
						}
						
						var adhoc_rating = adhocs.findWhere({"adhoc": "rating"});
						if (typeof adhoc_rating != "undefined") {
							medical_provider_rating = adhoc_rating.get("adhoc_value");
						}
						
						var adhoc_doctor_type = adhocs.findWhere({"adhoc": "doctor_type"});
						if (typeof adhoc_doctor_type != "undefined") {
							doctor_type = adhoc_doctor_type.get("adhoc_value");
						}
						//medical provider doctor type
						if (doctor_type=="secondary physician") {
							doctor_type = "SEC";
						}
						var adhoc_assigned_to = adhocs.findWhere({"adhoc": "assigned_to"});
						if (typeof adhoc_assigned_to != "undefined") {
							assigned_to = adhoc_assigned_to.get("adhoc_value");
							switch(assigned_to) {
								case "Applicant":
									assigned_to = "<div style='margin-top:0px; border:0px solid green; position:absolute; left:185px; top:5px'><img src='../img/thumbs_up.png' height='20' width='20'></div>";
									break;
								case "Neutral":
									assigned_to = "<div style='margin-top:0px; border:0px solid yellow; position:absolute; left:185px; top:5px'><img src='../img/spacer.gif' height='20' width='20'></div>";
									break;
								case "Defense":
									assigned_to = "<div style='margin-top:0px; border:0px solid red; position:absolute; left:185px; top:5px'><img src='../img/thumbs_down.png' height='20' width='20'></div>";
									break;
							}
						}
						
						if (self.collection.panel_title=="Dashboard") {
							//we have the referredout attorneys
							if (typeof referredout_attorney_parties != "undefined") {
								var collection_length = referredout_attorney_parties.length;
								for(i=0;i<collection_length;i++) {
									referredout_attorney_partie = referredout_attorney_parties[0];
									self.collection.remove(referredout_attorney_partie);
								}
							}
							//console.log("render complete:" + moment().format("h:m:s"));
							self.renderComplete(injury, specialty, medical_provider_rating, doctor_type, assigned_to);
						} else {
							if (self.model.get("claims") != "") {
								var arrClaims = self.model.get("claims").split("|");
								var arrayLength = arrClaims.length;
								var blnNeedReferredOut = true;
								for (var i = 0; i < arrayLength; i++) {
									//default we don't need it
									blnNeedReferredOut = false;
									var claim = arrClaims[i].split("~")[0];
									var inhouse = "N";
									if (typeof arrClaims[i].split("~")[1] != "undefined") {
										inhouse = arrClaims[i].split("~")[1];
									}
									if (inhouse=="N") {
										//we're just offering 1 red box, so we can break out
										blnNeedReferredOut = true;
										break;
									}
								}
								if (blnNeedReferredOut) {
									//if kase claims have been filled out, we need referredout attorney
									var referredout_attorney_partie = self.collection.findWhere({"type": "referredout_attorney"});
									if (typeof referredout_attorney_partie == "undefined") {
										referredout_attorney_partie = new Corporation({ case_id: self.collection.id, type:"referredout_attorney" });
										referredout_attorney_partie.set("corporation_id", -1);
										referredout_attorney_partie.set("partie_type", "Referred-out Attorney");
										referredout_attorney_partie.set("color", "_card_missing");
										self.collection.add(referredout_attorney_partie);
									}
								}
							}
							if (!blnWCAB && !blnImm) {
								var representing = self.model.get("injury_type").split("|")[1];
								//opposing
								var opposing = "plaintiff";
								if (representing=="plaintiff") {
									var opposing = "defendant";
								}
								
								if (!blnSpecificType) {
									if (!blnTritekApplicant) {
										var opposing_partie = self.collection.findWhere({"type": opposing});
									} else {
										var opposing_partie = self.collection.findWhere({"type": "applicant"});
									}
									if (typeof opposing_partie == "undefined") {
										opposing_partie = new Corporation({ case_id: current_case_id, type:opposing });
										opposing_partie.set("corporation_id", -1);
										opposing_partie.set("partie_type", opposing.capitalizeWords());
										opposing_partie.set("color", "_card_missing");
										self.collection.add(opposing_partie);
									}
								}
							}
							var claim_number = "";
							var carrier_insurance_type_option = "";
							
							//now we have to get the adhocs for the carrier
							var carrier_partie = self.collection.findWhere({"type": "carrier"});
							if (typeof carrier_partie == "undefined") {
								carrier_partie = new Corporation({ case_id: self.collection.id, type:"carrier" });
								carrier_partie.set("corporation_id", -1);
								carrier_partie.set("partie_type", "Carrier");
								carrier_partie.set("color", "_card_missing");
								if (self.model.get("blnWCAB")) {
									self.collection.add(carrier_partie);
								}
							}
							
							carrier_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: carrier_partie.get("corporation_id")});
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
									var parties = self.collection.toJSON();
									var blnWCAB = self.model.get("blnWCAB");
									var blnWCABDefense = self.model.get("blnWCABDefense");
									var arrRemove = [];
									_.each( parties, function(partie) {
										
										if (blnSSN) {
											if (partie.type=="defendant" && partie.company_name=="") {
												arrRemove.push(partie.id);
											}
										}
									});
									
									for(var i = 0; i < arrRemove.length; i++) {
										theid = arrRemove[i];
										self.collection.remove(theid);
									}
									if (arrRemove.length > 0) {
										parties = self.collection.toJSON();
									}
									
									_.each( parties, function(partie) {
										var new_filler = "";
										if (!blnWCAB || blnWCABDefense) {
											new_filler = partie.party_type_option;
											if (self.model.get("blnWCABDefense")) {
												if (partie.type=="defense") {
													new_filler = partie.party_defendant_option;
												} else {
													//only do defense atty
													new_filler = "";
												}
											} else {
												//pi
												if (partie.type=="plaintiff") {
													new_filler = "plaintiff";
												}
												if (partie.type=="defendant") {
													new_filler = "defendant";
												}
												if (blnSSN) {
													new_filler = "claimant";
												}
											}
										}
										partie.new_filler = new_filler;
										if (partie.doctor_type == null) {
											partie.doctor_type = "";
										}
										if (partie.claim_number!="" && partie.claim_number!=null) {
											arrClaimNumber.push(partie.claim_number);
										}
										if (partie.carrier_insurance_type_option!="" && partie.carrier_insurance_type_option!=null) {
											arrCarrierInsuranceTypeOption.push(partie.carrier_insurance_type_option);
										}
										if (partie.type=="referredout_attorney"){
											switch(partie.claim) {
												case "3P":
													partie.claim = "Third Party";
													break;
												case "SER":
													partie.claim = "Serious and Willful";
													break;
											}
										}
										if (self.collection.panel_title=="Dashboard") {
											if (partie.type=="applicant"){
												if (!blnWCAB) {
													partie.type="applicant";
													partie.partie_type="Plaintiff"
												} else {
													partie.type="applicant";
												}
											}
										} else {
											if (partie.type=="applicant"){
												if (!blnWCAB) {
													partie.type="applicant";
													partie.partie_type="Plaintiff"
												} else {
													partie.type="applicant";
													partie.partie_type="Applicant"
												}
											}
										}
									});
									if (arrClaimNumber.length > 0) {
										claim_number = arrClaimNumber.join("; ");
									}
									if (arrCarrierInsuranceTypeOption.length > 0) {
										console.log("here");
										carrier_insurance_type_option = arrCarrierInsuranceTypeOption.join("; ");
									}
									var applicant_ssn = self.collection.toJSON()[0].ssn;
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
									
									if (user_data_path == 'A1') {
										self.model.set("case_number", self.model.get("cpointer"));
									}
									
									var kase_type = self.model.get("case_type");
									var blnWCAB = isWCAB(kase_type);
									
									if (blnWCAB) {
										var case_number = self.model.get("case_number");
										var file_number = self.model.get("file_number");
										if (case_number=="" && file_number!="") {
											case_number = file_number;
											self.model.set("case_number", case_number);
										}
									}
									self.model.set("blnWCAB", blnWCAB);
									
									var kase = self.model;
									var statute_limitation = kase.get("statute_limitation");
									
									if (statute_limitation=="00/00/0000") {
										statute_limitation = "";
									}
									
									var start_date = "";
									var end_date = "";
									if (kase.get("start_date") == "Invalid date" || kase.get("start_date")=="00/00/0000") {
										start_date = "";
										kase.set("start_date", ""); 
									}
									if (kase.get("start_date")!="") {
										start_date = moment(kase.get("start_date")).format('MM/DD/YYYY');
									}
							
									var show_end = "";
									var checkedCT = "";
									var ctHidden = "hidden";
									if (kase.get("end_date") == "" || kase.get("end_date") == "00/00/0000") {
										end_date = "";
									} else {
										end_date = moment(kase.get("end_date")).format('MM/DD/YYYY');
										end_date = " - " + end_date + " CT";
									}
									
									self.model.set("claim_number", claim_number);
									
									var column_max = 4;
									if (window.innerWidth > 1090) {
										column_max = 5;
									}
									var blnPlaintiff = false;
									var quick_note = "";
									//show the parties
									$(self.el).html(self.template(
										{
											parties: parties, 
											case_id: self.collection.case_id,
											case_uuid: self.collection.case_uuid,
											panel_title: self.collection.panel_title,
											injury: injury.toJSON(),
											claim_number: claim_number,
											specialty:specialty,
											medical_provider_rating: medical_provider_rating,
											doctor_type: doctor_type,
											assigned_to: assigned_to,
											case_status: case_status,
											case_substatus: case_substatus,
											attorney: attorney,
											rating: rating,
											worker: worker,
											applicant_name: "",
											dashboard_dob: "",
											dashboard_age: "",
											applicant_ssn: applicant_ssn,
											applicant_language: "",
											kase: kase,
											start_date: start_date,
											end_date: end_date,
											statute_limitation: statute_limitation,
											carrier_insurance_type_option: carrier_insurance_type_option,
											blnWCAB: blnWCAB,
											quick_note: quick_note, 
											blnPlaintiff: blnPlaintiff,
											column_max: column_max,
											sub_in_date: self.model.get("sub_in_date"),
											sub_out_date: self.model.get("sub_out_date")
										})
									);
									
								}
							});
						}			
					}
				});
					
			}
		});
		
		var clipboard = new Clipboard('.btn', {
			text: function(e) {
				var info = self.copyInfo(e.id);
				return info;
			}
		});
		return this;
	},
	searchQME: function(event) {
		event.preventDefault();
		current_case_id = this.model.get("case_id");
		//get the applicant zip, pass it to the search form
		var kase = kases.findWhere({case_id:  current_case_id});
		var applicant_id = kase.get("applicant_id");
		//fetch the applicant, pass zip forward
		var person = new Person({id: applicant_id});
		person.fetch({
			success: function (person) {
				if (person.get("zip")!="") {
					document.location.href = "#qme/" + person.get("zip");
				} else {
					document.location.href = "#qme/-2";
				}
			}
		});
		
	},
	mapPartie: function (event) {
		var self = this;
		var element = event.currentTarget;
		var element_id = element.id.split("_")[3];
		var partie = new Corporation({case_id: current_case_id, id: element_id});
		partie.fetch({
			success:function (data) {
				var url = "https://www.bing.com/maps?where1=" + encodeURIComponent(data.toJSON().full_address);
				window.open(url);
			}
		});
	},
	copyInfo: function(element_id) {
		//var element = event.currentTarget;
		element_id = element_id.replace("copy_info_", "");
		var info = $("#copy_partie_" + element_id).val();
		
		//copyToClipboard(info);
		return info
	},
	listKases: function(event) {
		var element = event.currentTarget;
		var element_id = element.id.replace("list_kases_link_", "");
		var arrElement = element_id.split("_");
		var uuid = arrElement[0];
		var partie_type = element_id.replace(uuid + "_", "");
		event.preventDefault();
		var url = "#kaseslist/" + uuid + "/" + partie_type;
		window.open(url);
	},
	sendMatrix: function(event) {
		event.preventDefault();
		var url = "reports/demographics_sheet.php?case_id=" + current_case_id + "&send=y";
		window.open(url);
	},
	referVocational: function (event) {
		event.preventDefault();
		
		composeVocational();
	},
	saveFileLocation: function (event) {
		event.preventDefault();
		
		//save the info
		$("#file_location").hide();
		$("#file_location_feedback").html("Saving...");
		
		var file_location = $("#file_location").val();
		var url = "api/kase/filelocation";
		var formValues = "case_id=" + current_case_id + "&file_location=" + file_location;
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.success) {
					$("#file_location_feedback").html("<span style='background:lime; color:black; padding:2px'>Saved&nbsp;&#10003;</span>");
					
					setTimeout(function() {
						if (file_location!="") {
							$("#file_location_button").html("File Location: " + file_location);
							document.getElementById("file_location_button").title = "Set by " + login_username + " on " + moment().format("MM/DD/YYYY");
						} else {
							$("#file_location_button").html("File Location");
							document.getElementById("file_location_button").title = "";
						}
						//update the kases
						var kase = kases.findWhere({"case_id": current_case_id});
						kase.set("file_location", file_location);
						
						$("#file_location_feedback").html("");
						$("#file_location_feedback").hide();
						$("#file_location_button").fadeIn();
					}, 2500);
				}
			}
		});
	},
	showFileLocations: function (event) {
		event.preventDefault();
		$("#file_location_button").fadeOut(function() {
			$("#file_location").fadeIn(function() {
				//var length = $('#file_location> option').length;
				//open dropdown
				//$("#file_location").attr('size',length);
			});
		});
	},
	showMedListing: function(event) {
		event.preventDefault();
		
		if (blnMedicalBilling) {
			//do we have any medical billings
			var medical_billings = new MedicalBillingCollection({case_id: current_case_id});
			medical_billings.fetch({
				success: function(data) {
					var my_model = new Backbone.Model;
					my_model.set("holder", "kase_content");
					my_model.set("case_id", current_case_id);
					my_model.set("partie_id", "");
					my_model.set("embedded", false);
					$('#kase_content').html(new medical_billing_listing_view({collection: data, model: my_model}).render().el);	
				}
			});
		}
	},
	medSummary: function(event) {
		event.preventDefault();
		
		if (blnMedicalBilling) {
			//do we have any medical billings
			var medical_billings = new MedicalSummaryCollection({case_id: current_case_id});
			medical_billings.fetch({
				success: function(data) {
					var my_model = new Backbone.Model;
					my_model.set("holder", "kase_content");
					my_model.set("case_id", current_case_id);
					my_model.set("partie_id", "");
					my_model.set("embedded", false);
					$('#kase_content').html(new medical_summary_listing_view({collection: data, model: my_model}).render().el);	
				}
			});
		}
	},
	reviewRx: function (event) {
		event.preventDefault();
		
		window.Router.prototype.kaseApplicant(current_case_id);
		
		setTimeout(function() {
			$("#list_rx").trigger("click");
		}, 777);
	},
	jetFile: function (event) {
		window.open("jetfiler/form1_preamble.php?case_id=" + current_case_id);
	},
	searchKases: function (event) {
		var element = event.currentTarget;
		
		var element_class = $("#list_kases").attr("class");
		var arrElement = element_class.split("_");
		var key = arrElement[1];
		var modifier = arrElement[2];
		
		kase_searching = true;
		blnSearched = true;
		$('#ikase_loading').html(loading_image);
		var self = this;
		//var key = $('#srch-term').val();
		
		if (typeof key =="undefined") {
			return;
		}
		var my_kases = new KaseCollection();
		//look for modifiers
		
		blnSearchingKases = true;
		search_kases = my_kases.searchDB(key, modifier);

		if (this.model.length == 0) {
			this.model = kases.clone();
		}
		
		$('#search_results').html(new kase_listing_view({collection: kases, model: ""}).render().el);
    },
	composeTasks: function(event) {
		event.preventDefault();
		//$("#myModal4").modal("toggle");
		//composeTask();
		composeTaskPane();
	},
	composeExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeExam(element.id);
	},
	quickNotes: function(event) {
		blnQuickNotes = true;
		//document.location.href = "#notes/" + this.model.get("case_id");
		this.newNotes(event);
	},
	relatedKases: function(event) {
		document.location.href = "#kases/related_cases/" + this.model.get("case_id");
	},
	composeRelated: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeRelated(element.id);
	},
	doTimeouts: function() {
		var self = this;
		
		//gridster the edit tab
		gridsterIt(7);
		
		//if there is a second gridster for defendants, then ...
		if ($("#gridster_parties_cards2").length > 0) {
			gridsterById("gridster_parties_cards2");
		}
		
		//check on height
		$("#gridster_parties_cards").css("height", "");
		
		self.getApplicantImage();
		
		var claims = this.model.get("claims").replace("Claims: ", "");
		
		//let's see if the claims have matching referredout
		if (claims != "") {
			//we need to get the referredout attorney first,
			//and then look up the adhocs for the atty
			//now we have to get the adhocs for the carrier
			var collection_length = referredout_attorney_parties.length;
			for(i=0;i<collection_length;i++) {
				referredout_attorney_partie = referredout_attorney_parties[0];
				referredout_attorney_partie.adhocs = new AdhocCollection([], {case_id: self.model.get("case_id"), corporation_id: referredout_attorney_partie.get("corporation_id")});
				referredout_attorney_partie.adhocs.fetch({
					success:function (adhocs) {
						var adhoc_claims = adhocs.findWhere({"adhoc": "claims"});
						var claims_value = "";
						if (typeof adhoc_claims != "undefined") {
							//we found the adhoc claims
							claims_value = adhoc_claims.get("adhoc_value");
							if (claims_value!="") {								
									$("#" + claims_value + "Holder").css("background", "");
									//we need to remove link
									$("#" + claims_value + "Holder").html("<a href='#parties/" + self.model.get("case_id") + "/" + referredout_attorney_partie.get("corporation_id") + "/referredout_attorney' class='white_text'>" + $("#" + claims_value + "Holder").text() + "</a>");
									//add a title to each holder
									$("#" + claims_value + "Holder").prop("title", "Referred Out Atty: " + referredout_attorney_partie.get("company_name"));
								
							}
						} 
					}
				});
			}
		}
		
		if (current_case_id != this.model.get("case_id")) {
			current_case_id = this.model.get("case_id");
		}
		//if (self.collection.panel_title=="Dashboard") {
		setTimeout(function() {
			self.checkExport();
		}, 555);
		//}
		var kase_dois = dois.where({case_id: self.model.get("case_id")});
		_.each(kase_dois , function(doi) {
			doi = doi.toJSON();
			//dates
			var thedoi = moment(doi.start_date).format("MM/DD/YYYY");
			if (doi.end_date != "0000-00-00") {
				thedoi += " - " + moment(doi.end_date).format("MM/DD/YYYY") + " CT";
			}
			//get the body parts
			//list the red ones
			var bodyparts = new BodyPartsCollection([], { injury_id: doi.id, case_id: self.model.get("case_id"), case_uuid: "" });
			bodyparts.fetch({
				success: function(data) {		
					var bodyparts = data.toJSON();
					var arrBadParts = [];
					_.each( bodyparts, function(bodypart) {
						if (bodypart.bodyparts_status!="Y") {
							var code = bodypart.code;
							var description = bodypart.description.split(" - ")[0];
							var bodyparts_status = "&nbsp;<img src='../img/thumbs_down.png' height='20' width='20'>&nbsp;&nbsp;" + code + " - " + description;
							arrBadParts.push(bodyparts_status);
						}
					});
					if (arrBadParts.length > 0) {
						var bodyparts = "";
						var current_bodyparts = $("#bodyparts_warning").html();
						if (typeof current_bodyparts == "undefined") {
							current_bodyparts = "";
						}
						if (current_bodyparts.indexOf("Unaccepted Body Parts") < 0) {
							var bodyparts = "<div style='font-size:1.2em; background:orange; padding-left:2px; color:white'>Unaccepted Body Parts</div>";
						}
						bodyparts += "<div style='padding-left:2px; padding-bottom:2px'>DOI:&nbsp;" + thedoi + "</div><p>" + arrBadParts.join("<br>") + "</p>";
						$("#bodyparts_warning").html(current_bodyparts + bodyparts);
						$("#bodyparts_warning").fadeIn();
					}
				}
			});
		});
		
		//populate the defendant info after rendering
		if (self.model.get("blnWCAB")) {
			$(".defendant_demographic_info").hide();
			
			//do we have a vocation reference
			setTimeout(function() {
				self.checkVocational();
			}, 855);
		} else {
			//defendant info
			var defendant_partie_type = "defendant";
			if (self.model.get("blnWCAB")) {
				defendant_partie_type = "employer";
			}
			var defendant_name = "";
			var defendant_dob = "";
			var defendant_ssn = "";
			var defendant_language = "";
			var dashboard_age_defendant = "";
			var dashboard_dob_defendant = "";
			var defendant_partie = self.collection.findWhere({"type": defendant_partie_type});
			if (typeof defendant_partie != "undefined") {
				defendant_name = defendant_partie.get("company_name");
				//console.log(this.collection.toJSON()[0].company_name);
				defendant_dob = defendant_partie.get("dob");
				defendant_ssn = defendant_partie.get("ssn");
				defendant_language = defendant_partie.get("language");
				
				dashboard_dob_defendant = defendant_dob;
				if (dashboard_dob_defendant != "") {
					dashboard_dob_defendant = moment(dashboard_dob_defendant).format('MM/DD/YYYY');
					dashboard_age_defendant = " (" + dashboard_dob_defendant.getAge() + " years old)";
				}
				
				defendant_dob = dashboard_dob_defendant + dashboard_age_defendant;
				
				if (defendant_ssn.length == 9) {
					ssn = String(defendant_ssn);
					ssn1 = ssn.substr(0, 3);
					ssn2 = ssn.substr(3, 2);
					ssn3 = ssn.substr(5, 4);
					if (ssn != "XXXXXXXXX") {
						ssn1Display = ssn1;
						ssn2Display = ssn2;
						ssn3Display = ssn3;
					}
					defendant_ssn = String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3)
				}
			}
			if (defendant_name!="") {
				$("#defendant_name_demo").html("Defendant: " + defendant_name);
			} else {
				$("#defendant_name_demo").hide();
			}
			if (defendant_dob!="") {
				$("#defendant_dob_demo").html("DOB: " + defendant_dob);
			} else {
				$("#defendant_dob_demo").hide();
			}
			if (defendant_ssn!="") {
				$("#defendant_ssn_demo").html("SSN: " + defendant_ssn);
			} else {
				$("#defendant_ssn_demo").hide();
			}
			if (defendant_language!="" && typeof defendant!="undefined") {
				$("#defendant_language_demo").html("Language: " + defendant_language);
			} else {
				$("#defendant_language_demo").hide();
			}
		}
	
		var case_id = self.model.get("case_id");
		if (self.collection.panel_title=="Dashboard") {
			//$("#gridster_parties_cards").height("1000px");
			tasks = new TaskInboxCollection({ case_id: case_id });
			tasks.fetch({
				success: function (data) {
					if (data.length > 0) {
						var task_listing_info = new Backbone.Model;
						task_listing_info.set("title", "Kase Tasks");
						task_listing_info.set("receive_label", "Due");
						task_listing_info.set("homepage", true);
						task_listing_info.set("case_id", current_case_id);
						$('#my_tasks').html(new task_listing({collection: data, model: task_listing_info}).render().el);
						$("#my_tasks").removeClass("glass_header_no_padding");
					} else {
						$('#my_tasks').html("<span class='large_white_text'>No Tasks due today.</span>");
					}
				}
			});
			var occurences = new AllKaseEvents({case_id: case_id});
			occurences.fetch({
				success: function (data) {
					if (data.length > 0) {
						var event_listing_info = new Backbone.Model;
						event_listing_info.set("homepage", true);
						event_listing_info.set("title", "Upcoming Events");
						event_listing_info.set("event_class", "upcoming");
						$('#upcoming_events').html(new event_listing({collection: data, model: event_listing_info}).render().el);
						$("#upcoming_events").removeClass("glass_header_no_padding");
					} else {
						$('#upcoming_events').html("<span class='large_white_text'>No Upcoming Events.</span>");
					}
				}
			});
			
			//workflows applied?
			var url = 'api/workflow/applied/' + current_case_id;
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else { // If not
						//need notification
						if (data.length > 0) {
							var suffix = "";
							if (data.length > 1) {
								suffix = "s";
							}
							var arrWorkflows = [];
							for(var i = 0; i < data.length; i++) {
								arrWorkflows.push(data[i].description);
							}
							var title = "Workflow" + suffix + " Applied\r\n" + arrWorkflows.join("\r\n");
							$("#summary_data_holder").append("<span id='workflow_confirmation' title='" + title.replaceAll("'", "`") + "'>&nbsp;" + data.length + " Worflow" + suffix + " Applied</span>");
							
							setTimeout(function() {
								$("#workflow_confirmation").fadeOut();
							}, 15500);
						}
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					// report error
					console.log(errorThrown);
				}
			});
		
			setTimeout(function() {
				var notes = new NoteCollectionDash([], { case_id: case_id });
				notes.fetch({
					success: function(data) {
						var note_list_model = new Backbone.Model;
						note_list_model.set("display", "full");
						note_list_model.set("partie_type", "note");
						note_list_model.set("case_id", case_id);
						$('#kase_notes').html(new note_listing_view({collection: data, model: note_list_model, kase: self.model}).render().el);
						$("#kase_notes").removeClass("glass_header_no_padding");
						hideEditRow();
					}
				});	
			}, 1500);
		}
		// console.log("here");
		if (blnUploadDash) {
		//	console.log("abcd");
			//$('#message_attachments').html("<p>test123</p>");
			 $('#message_attachments').html(new message_attach({model: self.model}).render().el);

			setTimeout(function() {
				$("#message_attach_holder").css("width", "");
				$("#uploadifive-file_upload").css("font-size", "0.9em");
				$("#uploadifive-file_upload").css("color", "black");
				$("#uploadifive-file_upload").css("background", "rgb(221, 221, 221) none repeat scroll 0% 0% / auto padding-box border-box");
			}, 654);
		}
		setTimeout(function() {
			$("#kase_information").css("display","none");
			$("#kase_information").hide();
			
			//let's show the file location here
			var file_location = self.model.get("file_location");
			if (file_location!="") {
				$("#file_location_button").html("File Location: " + file_location);
			}
			$("#file_location").val(file_location);
		}, 500);
		
		//primary secondary
		var carrier_parties = self.collection.where({"type": "carrier"});
		_.each(carrier_parties , function(carrier_partie) {
			var carrier_id = carrier_partie.toJSON().corporation_id;
			carrier_partie.adhocs = new AdhocCollection([], {case_id: current_case_id, corporation_id: carrier_id});
			carrier_partie.adhocs.fetch({
				success:function (adhocs) {
					//default
					var primary_secondary = "&nbsp;(P)";
					var adhoc_primary_secondary = adhocs.findWhere({"adhoc": "primary_secondary"});
					
					if (typeof adhoc_primary_secondary != "undefined") {
						var second_secondary = adhoc_primary_secondary.get("adhoc_value");
						if (second_secondary=="secondary") {
							primary_secondary = "&nbsp;(S)";
						}
					}
					
					$("#primary_secondary_" + carrier_id).html(primary_secondary);
				}
			});
		});
		var employer_parties = self.collection.where({"type": "employer"});
		_.each(employer_parties , function(employer_partie) {
			var employer_id = employer_partie.toJSON().corporation_id;
			employer_partie.adhocs = new AdhocCollection([], {case_id: current_case_id, corporation_id: employer_id});
			employer_partie.adhocs.fetch({
				success:function (adhocs) {
					//default
					var primary_secondary = "&nbsp;(P)";
					var adhoc_primary_secondary = adhocs.findWhere({"adhoc": "primary_secondary"});
					
					if (typeof adhoc_primary_secondary != "undefined") {
						var second_secondary = adhoc_primary_secondary.get("adhoc_value");
						if (second_secondary=="secondary") {
							primary_secondary = "&nbsp;(S)";
						}
					}
					
					$("#primary_secondary_" + employer_id).html(primary_secondary);
				}
			});
		});
		
		//referring source
		var referring_parties = self.collection.where({"type": "referring"});
		if (typeof referring_parties != "undefined") {
			_.each(referring_parties , function(referring_partie) {
				//get the count
				var corp_id = referring_partie.get("corporation_id");
				var url = "api/corpcasecount/" + corp_id + "/referring";
				$.ajax({
					url:url,
					type:'GET',
					dataType:"json",
					success:function (data) {
						if(data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else { // If not
							if (document.getElementById("partie_name_" + corp_id)!=null) {
								var current_html = document.getElementById("partie_name_" + corp_id).parentElement.innerHTML;
								current_html += "&nbsp;<span title='Number of Cases Referred'>(" + data.case_count + ")</span>";
								document.getElementById("partie_name_" + corp_id).parentElement.innerHTML = current_html;
							}
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						// report error
						console.log(errorThrown);
					}
				});
			});
		}
		var blnSpecificType = (document.location.hash.indexOf("#partielist")==0);
		if (blnSpecificType) {
			var specific = document.location.hash.split("/")[2];
			var partie = specific.replace("_", " ").capitalizeWords();
			$("#new_partie").html("New " + partie);
		}
				
		if ($("#related_cases").length > 0) {
			$("#related_cases").html("Related Cases <span style='font-size:0.8em'>(" + relatedCount + ")</span>");
			//reset
			relatedCount = 0;
		}
		
		//file location info
		var url = 'api/kases/filelocation/' + current_case_id;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { // If not
					//need notification
					if (typeof data.user_logon != "undefined") {
						document.getElementById("file_location_button").title = "Set by " + data.user_logon + " on " + moment(data.time_stamp).format("MM/DD/YYYY");
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// report error
				console.log(errorThrown);
			}
		});
		
		if (blnNewWindow) {
			var current_location = document.location.href;
			current_location = current_location.replace("?n=", "");
			if (window.history.replaceState) {
			   //prevents browser from storing history with each change:
			   window.history.replaceState(document.location.hash, $(document).find("title").text(), current_location);
			   blnShowIntroTitle = false;
			}
			blnNewWindow = false;
		}
		
		setTimeout(function() {
			if (document.location.hash.indexOf("#kase/")==0) {
				current_dash_content = current_case_id + " dashboard";	//$('#content').html();
			}
			if (document.location.hash.indexOf("#parties/")==0) {
				current_parties_content = current_case_id + " parties";	//$('#content').html();
			}
		}, 200);
	},
	checkSent: function() {
		var self = this;
		//is this case in matrix
		var url = "../api/kases/matrixsent/" + current_case_id;

		//return;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				if (data.activity_count>0) {
					$("#matrix_imported").html("<span style='background:blue;color:yellow;font-weight:bold; padding:2px' title='Sent to Matrix on " + moment(data.activity_date).format("MM/DD/YY") + "\r\nSubmission ID: " + data.activity_id + "'>M</span>");
					$("#matrix_imported").fadeIn();
				}
			}
		});
	},
	checkVocational: function() {
		var self = this;
		//is this case in matrix
		//var url = "https://v4.ikase.org/api/activity/refvocational/" + current_case_id;
		var url = "https://"+ location.hostname +"/api/activity/refvocational/" + current_case_id;
		//return;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if (data.activity_count > 0) {
					$("#refer_vocational").addClass("btn-success");
					document.getElementById("refer_vocational").innerHTML += "&nbsp;&#10003;";	
				}
			}
		});
	},
	checkExport: function() {
		var self = this;
		//is this case in matrix
		//var url = "https://v4.ikase.org/api/kases/matrix";		
		var url = "https://" + location.hostname + "/api/kases/matrix";
		var adj_number = this.model.get("adj_number");
		var ssn = this.model.get("ssn");
		var formValues = "id=" + current_case_id + "&adj_number=" + adj_number + "&nss=" + ssn;
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$("#matrix_imported").html("<span style='background:blue;color:yellow;font-weight:bold; padding:2px' title='Exported to Matrix'>M</span>");
					$("#matrix_imported").fadeIn();
					//was it imported from request to matrix?
					self.checkMatrix();
					checkRequestLocations(data.id);
				}
				if (data.imported=="N") {
					//maybe it was at least sent (customer is not a matrix customer)
					self.checkSent();
				}
			}
		});
	},
	checkMatrix: function() {
		//check adj first
		var kase_dois = dois.where({case_id:current_case_id});
		var adj_number = "";
		var arrAdjs = [];
		//kase_dois = kase_dois.toJSON();
		_.each(kase_dois, function(doi) {
				var doi = kase_dois[0].toJSON();
				arrAdjs.push(doi.adj_number);
			}
		);
		adj_number = arrAdjs.join("~");
		if (adj_number!="") {
			var url = "https://v4.ikase.org/api/kases/matrixadj/" + current_case_id + "/" + adj_number;
		} else {	//if adj is empty
			//is this case in matrix
			var url = "https://v4.ikase.org/api/kases/matrixorder/" + current_case_id;
		}
		//return;
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			data: "",
			success:function (data) {
				if (data.imported=="Y") {
					$("#matrix_imported").html("<span style='background:green;color:yellow;font-weight:bold; padding:2px' title='Added to Matrix on " + data.assigned_date + "\r\nOrder ID:" + data.order_id + "'>M</span>");
					$("#matrix_imported").fadeIn();
				}
			}
		});
	},
	getExams:function() {
		var self = this;
		
		var exams = new ExamCollection({case_id: this.model.get("case_id")});
		exams.fetch({
			success: function (data) {
				var exam_info = new Backbone.Model;
				exam_info.set("case_id", self.model.get("case_id"));
				exam_info.set("holder", "kase_exam_card");
				$('#kase_exam_card').html(new exam_listing({collection: data, model:exam_info}).render().el);
				$('#kase_exam_card_holder').fadeIn();
			}
		});
	},
	getEvents: function() {
		var self = this;
		
		var occurences = new AllKaseEvents({case_id: this.model.get("case_id")});
		occurences.fetch({
			success: function (occurences) {
				if (occurences.length > 0) {
					var event_listing_info = new Backbone.Model;
					event_listing_info.set("title", "Upcoming Events");
					event_listing_info.set("homepage", true);
					event_listing_info.set("event_class", "upcoming");
					$('#kase_events_card').html(new event_listing({collection: occurences, model: event_listing_info}).render().el);
					$('#kase_events_card_holder').fadeIn();
				}
			}
		});
	},
	getApplicantImage: function() {
		var self = this;
		//let's get any image
		var kase_documents = new DocumentCollection([], { case_id: self.collection.case_id, attribute: "applicant_picture" });
		kase_documents.fetch({
			success: function(data) {
				if ( data.toJSON().length > 0) {
					var document_filename = data.toJSON()[0].document_filename;
					if (document_filename!="") {
						//href='D:/uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "' target='_blank'
						$('#applicant_picture').html("<a id='expand_applicant_image'  style='cursor:pointer' title='Click to expand'><img src='D:/uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "' class='applicant_medium_img'></a>");
						$('#applicant_picture').fadeIn();
						/*
						//removed per thomas 7/5/2018
						var last = $(".gridster li")[$(".gridster li").length - 1];
						if (typeof last != "undefined") {
							var row = $("#" + last.id).attr("data-row");
							var col = $("#" + last.id).attr("data-col");
							$("#applicant_pictureGrid").attr("data-row", row);
							$("#applicant_pictureGrid").attr("data-col", Number(col) + 1);
							$("#applicant_pictureGrid").fadeIn();
						}
						*/
					}
				}
			}
		});
	},
	expandImage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		
		$("#myModalBody").html("Applicant");
		var html = element.innerHTML;
		html = html.replace('class="applicant_medium_img"', 'class="applicant_large_img"');
		$("#myModalLabel").css("text-align", "center");
		$("#myModalLabel").html(html);
	
		$("#myModal4").modal("toggle");
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		if (element.id=="compose_quick") {
			blnQuickNotes = true;
		}
		composeNewNote(element.id);
	},
	editQuickNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		alert(element.id);
		//composeNewNote(element.id);
	},
	newEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		generateEnvelope(element.id, "html");
	},
	newPDFEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		generateEnvelope(element.id, "pdf");
	},
	newPartie: function(event) {
		event.preventDefault();
		var blnSpecificType = (document.location.hash.indexOf("#partielist")==0);
		
		if (!blnSpecificType) {
			var href = "#parties/" + current_case_id + "/-1/new";
		} else {
			var specific = document.location.hash.split("/")[2];
			var href = "#parties/" + current_case_id + "/-1/" + specific;
		}
		document.location.href = href;
	},
	newMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	renderComplete: function(injury, specialty, medical_provider_rating, doctor_type, assigned_to) {
		var self = this;
		var claim_number = "";
		var blnImm = (this.model.get("case_type")=="immigration");
		var carrier_insurance_type_option = "";
		//console.log(this.collection.toJSON());
		var case_status = "";
		var case_substatus = "";
		var attorney = "";
		var worker = "";
		var rating = "";
		
		if (typeof this.collection.toJSON()[0]!="undefined") {
			case_status = this.collection.toJSON()[0].case_status;
			case_substatus = this.collection.toJSON()[0].case_substatus;
			attorney = this.collection.toJSON()[0].attorney;
			worker = this.collection.toJSON()[0].worker;
			rating = this.collection.toJSON()[0].rating;
		} else {
			if (typeof this.model.get("case_status")!="undefined") {
				case_status = this.model.get("case_status");
				case_substatus = this.model.get("case_substatus");
				attorney = this.model.get("attorney");
				worker = this.model.get("worker");
				rating = this.model.get("rating");
			}	
		}
		
		
		//var case_status = this.collection.toJSON()[0].case_status;
		if (case_status.toLowerCase().indexOf("close") > -1) {
			case_status = "<span style='background:red;color:white'>" + case_status + "</span>";
		}
		/*
		var case_substatus = this.collection.toJSON()[0].case_substatus;
		var attorney = this.collection.toJSON()[0].attorney;
		var worker = this.collection.toJSON()[0].worker;
		var rating = this.collection.toJSON()[0].rating;
		*/
		
		var applicant_name = "";
		var applicant_language = "";
		var dashboard_dob = "";
		var dashboard_age = "";
		var applicant_ssn = "";
		/*
		if (self.model.get("blnWCAB") || self.model.get("blnSSN")) {
			var appli = this.collection.findWhere({corporation_id: "-1"});
		} else {
			var appli = this.collection.findWhere({blurb: "plaintiff"});
		}
		*/
		var appli = this.collection.findWhere({corporation_id: "-1"});
		var blnPlaintiff = false;
		if (typeof appli == "undefined") {
			/*
			if (customer_id==1070 || customer_id==1070) {
				var appli = this.collection.findWhere({corporation_id: "-1"});
			}
			*/
			//no applicant, look for plaintiff
			
			var appli = this.collection.findWhere({blurb: "plaintiff"});
			blnPlaintiff = true;
		}
		if (typeof appli != "undefined") {
			appli = appli.toJSON();
			//if (self.model.get("blnWCAB") || self.model.get("blnSSN")) {
			if (!blnPlaintiff) {
				applicant_name = appli.full_name;
			} else {
				applicant_name = appli.company_name;
			}
			if (applicant_name=="") {
				if (customer_id==1070) {
					applicant_name = appli.full_name;
				}
			}
			var applicant_language = appli.language;
			var dashboard_dob = appli.dob;
			var dashboard_age = "";
			if (dashboard_dob != "") {
				dashboard_dob = moment(dashboard_dob).format('MM/DD/YYYY');
				dashboard_age = " (" + dashboard_dob.getAge() + " years old)";
			}
			var applicant_ssn = appli.ssn;
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
		}
		//defendant info
		var defendant_partie_type = "defendant";
		if (self.model.get("blnWCAB") || self.model.get("blnSSN")) {
			defendant_partie_type = "employer";
		} else {
			//console.log(self.model.toJSON());
			var representing = self.model.get("injury_type").split("|")[1];
			//opposing
			var opposing = "plaintiff";
			if (representing=="plaintiff") {
				var opposing = "defendant";
			}
			//missing opposing partie?		
			if (!blnTritekApplicant) {
				var opposing_partie = this.collection.findWhere({"type": opposing});
			} else {
				var opposing_partie = this.collection.findWhere({"type": "applicant"});
			}
			if (!blnImm) {
				if (typeof opposing_partie == "undefined") {
					opposing_partie = new Corporation({ case_id: current_case_id, type:opposing });
					opposing_partie.set("corporation_id", -1);
					opposing_partie.set("partie_type", opposing.capitalizeWords());
					opposing_partie.set("color", "_card_missing");
					self.collection.add(opposing_partie);
				}
			}

		}
		var defendant_partie = this.collection.findWhere({"type": defendant_partie_type});
		if (typeof defendant_partie != "undefined") {
			var defendant_name = defendant_partie.get("company_name");
			//console.log(this.collection.toJSON()[0].company_name);
			var defendant_dob = defendant_partie.get("dob");
			var defendant_ssn = defendant_partie.get("ssn");
			var defendant_language = defendant_partie.get("language");
			var dashboard_age_defendant = "";
			var dashboard_dob_defendant = defendant_dob;
			if (dashboard_dob_defendant != "") {
				dashboard_dob_defendant = moment(dashboard_dob_defendant).format('MM/DD/YYYY');
				dashboard_age_defendant = " (" + dashboard_dob_defendant.getAge() + " years old)";
			}
			
			defendant_dob = dashboard_dob_defendant + dashboard_age_defendant;
			
			if (defendant_ssn.length == 9) {
				ssn = String(defendant_ssn);
				ssn1 = ssn.substr(0, 3);
				ssn2 = ssn.substr(3, 2);
				ssn3 = ssn.substr(5, 4);
				if (ssn != "XXXXXXXXX") {
					ssn1Display = ssn1;
					ssn2Display = ssn2;
					ssn3Display = ssn3;
				}
				defendant_ssn = String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3)
			}
		}
			/*
			defendant_partie.fetch({
				success:function (defendant_partie) {
					//console.log(defendant_partie);
					var defendant_name = this.collection.toJSON().company_name;
					//console.log(this.collection.toJSON()[0].company_name);
					var defendant_dob = this.collection.toJSON().dob;
					var defendant_ssn = this.collection.toJSON().ssn;
					var dashboard_age_defendant = "";
					if (dashboard_dob_defendant != "") {
						dashboard_dob_defendant = moment(dashboard_dob_defendant).format('MM/DD/YYYY');
						dashboard_age_defendant = " (" + dashboard_dob_defendant.getAge() + " years old)";
					}
					
					defendant_dob = dashboard_dob_defendant + dashboard_age_defendant;
					
					if (defendant_ssn.length == 9) {
						ssn = String(defendant_ssn);
						ssn1 = ssn.substr(0, 3);
						ssn2 = ssn.substr(3, 2);
						ssn3 = ssn.substr(5, 4);
						if (ssn != "XXXXXXXXX") {
							ssn1Display = ssn1;
							ssn2Display = ssn2;
							ssn3Display = ssn3;
						}
						defendant_ssn = String(ssn1) + "-" + String(ssn2) + "-" + String(ssn3)
					}
					setTimeout(function() { 
						$("#defendant_name_demo").html(defendant_name);
						$("#defendant_dob_demo").html(defendant_dob);
						$("#defendant_ssn_demo").html(defendant_ssn);
					}, 1000);
					
				}	
			});
		*/
		//now we have to get the adhocs for the carrier
		var carrier_partie = self.collection.findWhere({"type": "carrier"});
		if (typeof carrier_partie == "undefined") {
			carrier_partie = new Corporation({ case_id: self.collection.id, type:"carrier" });
			carrier_partie.set("corporation_id", -1);
			carrier_partie.set("partie_type", "Carrier");
			carrier_partie.set("color", "_card_missing");
			carrier_partie.set("claim_number", "");
			carrier_partie.set("primary_secondary", "primary");
			
			if (self.model.get("blnWCAB")) {
				self.collection.add(carrier_partie);
			}
		}
		carrier_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: carrier_partie.get("corporation_id")});
		carrier_partie.adhocs.fetch({
			success:function (adhocs) {
				var adhoc_claim_number = adhocs.findWhere({"adhoc": "claim_number"});
				
				if (typeof adhoc_claim_number != "undefined") {
					claim_number = adhoc_claim_number.get("adhoc_value");
				}
				var adhoc_carrier_insurance_type_option = adhocs.findWhere({"adhoc": "insurance_type_option"});
									
				if (typeof adhoc_carrier_insurance_type_option != "undefined") {
					carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
					//console.log(carrier_insurance_type_option);
				}
				
				//default to empty on quick
				var quick_note = "";
				
				var arrClaimNumber = [];
				var arrCarrierInsuranceTypeOption = [];
				var claim_number = "";
				//var carrier_insurance_type_option = "";
				var blnWCAB = self.model.get("blnWCAB");
				var blnWCABDefense = self.model.get("blnWCABDefense");
				var parties = self.collection.toJSON();
				_.each( parties, function(partie) {
					if (partie.doctor_type == null) {
						partie.doctor_type = "";
					}
					if (partie.claim_number!="" && partie.claim_number!=null) {
						arrClaimNumber.push(partie.claim_number);
					}
					if (partie.carrier_insurance_type_option!="" && partie.carrier_insurance_type_option!=null) {
						arrCarrierInsuranceTypeOption.push(partie.carrier_insurance_type_option);
					}
					
					var new_filler = "";
					if (!blnWCAB || blnWCABDefense) {
						new_filler = partie.party_type_option;
						if (self.model.get("blnWCABDefense")) {
							if (partie.type=="defense") {
								new_filler = partie.party_defendant_option;
							} else {
								//only do defense atty
								new_filler = "";
							}
						} else {
							//pi
							if (partie.type=="plaintiff") {
								new_filler = "plaintiff";
							}
							if (partie.type=="defendant") {
								new_filler = "defendant";
							}
							if (self.model.get("blnSSN")) {
								new_filler = "claimant";
							}
						}
					}
					partie.new_filler = new_filler;
				});
				if (arrClaimNumber.length > 0) {
					claim_number = arrClaimNumber.join("; ");
				}
				
				
				if (customer_id == 1049) {
					self.model.set("case_number", self.model.get("cpointer"));
				}
				var kase_type = self.model.get("case_type");
				var blnWCAB = isWCAB(kase_type);
				self.model.set("blnWCAB", blnWCAB);
				
				var kase = self.model;
				var statute_limitation = kase.get("statute_limitation");
				
				if (statute_limitation=="00/00/0000") {
					statute_limitation = "";
				}
				
				var start_date = "";
				var end_date = "";
				if (kase.get("start_date") == "Invalid date" || kase.get("start_date")=="00/00/0000") {
					start_date = "";
					kase.set("start_date", ""); 
				}
				if (kase.get("start_date")!="") {
					start_date = moment(kase.get("start_date")).format('MM/DD/YYYY');
				}
		
				var show_end = "";
				var checkedCT = "";
				var ctHidden = "hidden";
				if (kase.get("end_date") == "" || kase.get("end_date") == "00/00/0000") {
					end_date = "";
				} else {
					end_date = moment(kase.get("end_date")).format('MM/DD/YYYY');
					end_date = " - " + end_date + " CT";
				}
				
				self.model.set("claim_number", claim_number);
				var column_max = 4;
				if (window.innerWidth > 1090) {
					column_max = 5;
				}
				
				var blnPlaintiff = false;
				var plaintiff = self.collection.findWhere({"type": "plaintiff"});
				if (typeof plaintiffs != "undefined") {
					var blnPlaintiff = (plaintiff.toJSON().color!="_card_missing");
				}
				
				$(self.el).html(self.template({
					parties: parties, 
					case_id: self.collection.case_id, 
					case_uuid: self.collection.case_uuid, 
					panel_title: self.collection.panel_title, 
					injury: injury.toJSON(), 
					claim_number:claim_number, 
					specialty:specialty, 
					medical_provider_rating:medical_provider_rating, 
					doctor_type: doctor_type, 
					assigned_to: assigned_to, 
					case_status: case_status, 
					case_substatus: case_substatus, 
					attorney: attorney, 
					rating: rating, 
					worker: worker, 
					applicant_name: applicant_name, 
					dashboard_dob: dashboard_dob, 
					dashboard_age: dashboard_age, 
					applicant_ssn: applicant_ssn, 
					applicant_language: applicant_language, 
					kase: kase, 
					start_date: start_date, 
					end_date: end_date, 
					statute_limitation: statute_limitation, 
					quick_note: quick_note, 
					carrier_insurance_type_option: carrier_insurance_type_option, 
					blnWCAB: blnWCAB,
					blnPlaintiff: blnPlaintiff,
					column_max: column_max,
					sub_in_date: self.model.get("sub_in_date"),
					sub_out_date: self.model.get("sub_out_date")
				}));
				
				//console.log("after:" + moment().format("h:m:s"));
				
				//gridster the edit tab
				setTimeout(function() {
					gridsterIt(7);
					
					//get the quick note
					var quick_notes = new NotesByType([], {type: "quick", case_id: self.model.get("case_id")});
					quick_notes.fetch({
						success: function(data) {	
							var notes = data.toJSON();
							var arrNotes = [];
							if (notes.length > 0) {
								_.each(notes , function(quicknote) {
									 var thenote = quicknote.note;
									/*
									thenote = thenote.replaceAll("<p>", "<p style='font-size:1.5em'>");
									thenote = thenote.replaceAll("<div>", "<div style='font-size:1.5em'>");
									thenote = thenote.replaceAll('<div dir', '<div style="font-size:1.5em" dir');
									thenote = thenote.replaceTout('background-color: rgb(255, 255, 255);', '');
									thenote = thenote.replaceTout('background-color: rgb(25, 25, 25);', '');
									*/
									thenote = cleanupNote(thenote);
									arrNotes[arrNotes.length] = "<div class='quicknote_row' id='quicknote_" + quicknote.notes_id + "'><div style='font-style:italic; font-size:0.8em'>" + moment(quicknote.dateandtime).format("MM/DD/YY h:mm a") + " by " + quicknote.entered_by + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='edit_quicknote' id='quick_note_" + current_case_id + "_" + quicknote.notes_id + "' style='font-style:normal; color:#00FFFF; font-size:1.5em; cursor:pointer;z-index:9999;' onClick='javascript:editQuickNotes(event)' data-toggle='modal' data-target='#myModal4'><i class='glyphicon glyphicon-edit' style='color:#00FFFF'>&nbsp;</i></span></div><div>" + thenote + "</div></div>";  
								});
							}
							quick_note = arrNotes.join("\r\n");
							$("#noteSpan").html(quick_note);
							
							if ($("#notesGrid").css("width")!="560px") {
								$("#notesGrid").css("width", "560px");
							}
						}
					});
				}, 100);
				/*
				setTimeout(function() {
					self.getApplicantImage();
				}, 770);	
				*/
				//custom just for leyva
				if (customer_id == 1070) {
					setTimeout(function() {
						self.getExams();
					}, 1100);
					setTimeout(function() {
						self.getEvents();
					}, 990);
				}						
			}
		});
	},
	confirmdeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#data_list_" + id).fadeOut("slow", function() {
			$("#confirm_delete_" + id).fadeIn();
		})
	},
	canceldeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_" + id).fadeOut("slow", function() {
			$("#data_list_" + id).fadeIn();
		})
	},
	deletePartie: function(event) {
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		deleteElement(event, id, "corporation");
		
		//all the content must be refetched next time
		resetCurrentContent()
	},
	editQuickNote: function(event) {
		event.preventDefault();
		//$("#edit_quicknote").fadeOut(function() {
			//$("#save_quicknote").fadeIn();
		//});
		/*
		$("#noteSpan").fadeOut(function() {
			$("#noteInput").fadeIn();
		});
		*/
	},
	saveQuickNote: function(event) {
		event.preventDefault();
		var case_id = $("#case_id").val();
		var case_uuid = $("#case_uuid").val();
		var notes_id = $("#notes_id").val();
		var url = "api/notes/add";
		var formValues = "table_name=notes&table_id=" + notes_id + "&noteInput=" + encodeURIComponent($("#noteInput").val()) + "&table_attribute=quick&type=quick&title=Kase%20Quick%20Note";
		if (notes_id!="") {
			url = "api/notes/update";
		} else {
			formValues += "&case_uuid=" + case_uuid + "&case_id=" + case_id;
		}
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					//console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					//hide the text box and save button, show the note in span
					//show the edit button
					$("#save_quicknote").fadeOut(function() {
						$("#edit_quicknote").fadeIn();
					});
					$("#noteSpan").html($("#noteInput").val());
					$("#noteInput").fadeOut(function() {
						$("#noteSpan").fadeIn();
					});
				}
			}
		});
	}
});
window.partie_option_cards_view = Backbone.View.extend({

    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"click .delete_icon":			"confirmdeletePartie",
		"click .compose_new_exam": 		"composeExam",
		"click #add_case":				"composeRelated",
		"click .delete_yes":			"deletePartie",
		"click .delete_no":				"canceldeletePartie",
		"click .compose_new_note": 		"newNotes",
		"click .compose_new_envelope":	"newEnvelope",
		"click .compose_pdf_envelope":	"newPDFEnvelope",
		"click .compose_message":		"newMessage",
		"click #quick_notes":			"quickNotes",
		"click .edit_quicknote":		"editQuickNote",
		"click #related_cases":			"relatedKases",
		"click #search_qme":			"searchQME",
		"click #list_kases": 			"searchKases",
		"click #all_done":				"doTimeouts"
	},
    render:function () {		
		//console.log("before:" + moment().format("h:m:s"));
		var self = this;
		
		//dois
		var kase_dois = dois.where({case_id: this.collection.case_id});
		var kase_type = this.model.get("case_type");
		var blnWCAB = isWCAB(kase_type);
		//we need referred out attorneys for claims title
		referredout_attorney_parties = self.collection.where({"type": "referredout_attorney"});
		
		var arrDOIs = [];
		var arrADJs = [];
		var thedoi = "";
		var thelocation = "";
		var theadj = "";
		var theoccupation = "";
		var doi_locations = "";
		_.each(kase_dois , function(doi) {
			var thedoi = '<a href="#injury/' + doi.get("main_case_id") + '/' + doi.id + '" class="white_text" title="Click here to review Injury information">' + moment(doi.get("start_date")).format("MM/DD/YYYY");
			if (doi.get("end_date") != "0000-00-00") {
				thedoi += " - " + moment(doi.get("end_date")).format("MM/DD/YYYY") + " CT";
			}
			thedoi += '</a>';
			
			thelocation = doi.get("full_address");
			theadj = doi.get("adj_number");
			theoccupation = doi.get("occupation");
			
			if (arrDOIs.length > 0) {
				arrDOIs[arrDOIs.length] = "ADJ #:&nbsp;" + theadj + "<br>Occupation:&nbsp;" + theoccupation + "<br>DOI:&nbsp;" + thedoi + "<br>" + thelocation;
			} else {
				doi_locations = thelocation;
				arrDOIs[arrDOIs.length] = "ADJ #:&nbsp;" + theadj + "<br>Occupation:" + theoccupation + "<br>DOI:&nbsp;" + thedoi;
			}
			arrADJs[arrADJs.length] = theadj;
		});
		/*
		thedoi = arrDOIs.join("<br>");
		if (arrDOIs.length > 1) {
			thedoi = "<br>" + thedoi;
		}
		*/
		if (arrDOIs.length > 0) {
			/*
			//list the first one only, the others are in recent cases
			theadj = arrADJs[0];
			this.model.set("adj_number", theadj);
			arrADJs.splice(0, 1);
			
			thedoi = arrDOIs[0];
			this.model.set("dois", thedoi);
			arrDOIs.splice(0, 1);
			*/
			var suffix = ":";
			if (arrDOIs.length > 1) {
				suffix = "(s):<br>"
			}
			thedoi = arrDOIs.join("<hr>");
			this.model.set("related_dois", thedoi);	//"DOI" + suffix + 
			this.model.set("doi_locations", doi_locations);			
		} else {
			this.model.set("dois", thedoi);
			this.model.set("related_dois", thedoi);
			this.model.set("doi_locations", doi_locations);
		}
		//claims
		var claims = this.model.get("claims").replace("Claims: ", "");
		var arrClaims = claims.split("|");
		var arrayLength = arrClaims.length;
		for (var i = 0; i < arrayLength; i++) {
			var claim = arrClaims[i].split("~")[0];
			var inhouse = "N";
			if (typeof arrClaims[i].split("~")[1] != "undefined") {
				inhouse = arrClaims[i].split("~")[1];
			}
			switch(claim) {
				case "3P":
					if (inhouse!="Y") {
						claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:3P' title='Click to add a Referred Out Attorney' class='white_text'>Third Party</a></span>";
					} else {
						claim = "Third Party (In House)";
					}
					break;
				case "SER":
					if (inhouse!="Y") {
						claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:SER' title='Click to add a Referred Out Attorney' class='white_text'>Serious and Willful</a></span>";
					} else {
						claim = "SER (In House)";
					}
					break;
				default:
					if (inhouse!="Y") {
						claim = "<span id='" + claim + "Holder' style='background:red'><a href='#parties/" + self.model.get("case_id") + "/-1/referredout_attorney:" + claim + "' title='Click to add a Referred Out Attorney' class='white_text'>" + claim + "</a></span>";
					} else {
						claim = claim + " (In House)";
					}
			}
			arrClaims[i] = claim;
		}
		if (arrayLength == 0) {
			this.model.set("claims_display", "none");
		} else {
			this.model.set("claims_display", "");
		}
		this.model.set("claims_values", "Claims: " + arrClaims.join("; "));
		//now I have to get the injury stuff
		var injury = new Injury({case_id: this.collection.case_id});
		var specialty = "";
		var medical_provider_rating = "";
		var doctor_type = "";
		var assigned_to = "";
		
		var case_status = this.collection.toJSON()[0].case_status;
		var case_substatus = this.collection.toJSON()[0].case_substatus;
		var attorney = this.collection.toJSON()[0].attorney;
		var worker = this.collection.toJSON()[0].worker;
		var rating = this.collection.toJSON()[0].rating;

		//get the injury
		injury.fetch({
			success: function (injury) {
				injury.set("case_id", self.collection.case_id);
				injury.set("case_uuid", self.collection.case_uuid);
				
				//now we have to get the adhocs for the medical_provider
				var medical_provider_partie = self.collection.findWhere({"type": "medical_provider"});
				if (typeof medical_provider_partie == "undefined") {
					medical_provider_partie = new Corporation({ case_id: self.collection.id, type:"medical_provider" });
				}
				medical_provider_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: medical_provider_partie.get("corporation_id")});
				medical_provider_partie.adhocs.fetch({
					success:function (adhocs) {
						var adhoc_specialty = adhocs.findWhere({"adhoc": "specialty"});
						if (typeof adhoc_specialty != "undefined") {
							specialty = adhoc_specialty.get("adhoc_value");
						}
						
						var adhoc_rating = adhocs.findWhere({"adhoc": "rating"});
						if (typeof adhoc_rating != "undefined") {
							medical_provider_rating = adhoc_rating.get("adhoc_value");
						}
						
						var adhoc_doctor_type = adhocs.findWhere({"adhoc": "doctor_type"});
						if (typeof adhoc_doctor_type != "undefined") {
							doctor_type = adhoc_doctor_type.get("adhoc_value");
						}
						//medical provider doctor type
						if (doctor_type=="secondary physician") {
							doctor_type = "SEC";
						}
						var adhoc_assigned_to = adhocs.findWhere({"adhoc": "assigned_to"});
						if (typeof adhoc_assigned_to != "undefined") {
							assigned_to = adhoc_assigned_to.get("adhoc_value");
							switch(assigned_to) {
								case "Applicant":
									assigned_to = "<div style='margin-top:0px; border:1px solid green; position:absolute; left:185px; top:5px'><i class='icon-emo-happy' style='color:green; font-size:15px; margin-top:10px'>&nbsp;</i></div>";
									break;
								case "Neutral":
									assigned_to = "<div style='margin-top:0px; border:1px solid yellow; position:absolute; left:185px; top:5px'><i class='icon-emo-sleep' style='color:yellow; font-size:15px; margin-top:10px;'>&nbsp;</i></div>";
									break;
								case "Defense":
									assigned_to = "<div style='margin-top:0px; border:1px solid red; position:absolute; left:185px; top:5px'><i class='icon-emo-unhappy' style='color:red; font-size:15px'>&nbsp;</i></div>";
									break;
							}
						}
						
						if (self.collection.panel_title=="Dashboard") {
							//we have the referredout attorneys
							if (typeof referredout_attorney_parties != "undefined") {
								var collection_length = referredout_attorney_parties.length;
								for(i=0;i<collection_length;i++) {
									referredout_attorney_partie = referredout_attorney_parties[0];
									self.collection.remove(referredout_attorney_partie);
								}
							}
							//console.log("render complete:" + moment().format("h:m:s"));
							self.renderComplete(injury, specialty, medical_provider_rating, doctor_type, assigned_to);
						} else {
							if (self.model.get("claims") != "") {
								var arrClaims = self.model.get("claims").split("|");
								var arrayLength = arrClaims.length;
								var blnNeedReferredOut = true;
								for (var i = 0; i < arrayLength; i++) {
									//default we don't need it
									blnNeedReferredOut = false;
									var claim = arrClaims[i].split("~")[0];
									var inhouse = "N";
									if (typeof arrClaims[i].split("~")[1] != "undefined") {
										inhouse = arrClaims[i].split("~")[1];
									}
									if (inhouse=="N") {
										//we're just offering 1 red box, so we can break out
										blnNeedReferredOut = true;
										break;
									}
								}
								if (blnNeedReferredOut) {
									//if kase claims have been filled out, we need referredout attorney
									var referredout_attorney_partie = self.collection.findWhere({"type": "referredout_attorney"});
									if (typeof referredout_attorney_partie == "undefined") {
										referredout_attorney_partie = new Corporation({ case_id: self.collection.id, type:"referredout_attorney" });
										referredout_attorney_partie.set("corporation_id", -1);
										referredout_attorney_partie.set("partie_type", "Referred-out Attorney");
										referredout_attorney_partie.set("color", "_card_missing");
										self.collection.add(referredout_attorney_partie);
									}
								}
							}
							var claim_number = "";
							var carrier_insurance_type_option = "";
							//now we have to get the adhocs for the carrier
							var carrier_partie = self.collection.findWhere({"type": "carrier"});
							if (typeof carrier_partie == "undefined") {
								carrier_partie = new Corporation({ case_id: self.collection.id, type:"carrier" });
								carrier_partie.set("corporation_id", -1);
								carrier_partie.set("partie_type", "Carrier");
								carrier_partie.set("color", "_card_missing");
								if (self.model.get("blnWCAB")) {
									self.collection.add(carrier_partie);
								}
							}
							carrier_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: carrier_partie.get("corporation_id")});
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
									var parties = self.collection.toJSON();
									_.each( parties, function(partie) {
										if (partie.doctor_type == null) {
											partie.doctor_type = "";
										}
										if (partie.claim_number!="" && partie.claim_number!=null) {
											arrClaimNumber.push(partie.claim_number);
										}
										if (partie.carrier_insurance_type_option!="" && partie.carrier_insurance_type_option!=null) {
											arrCarrierInsuranceTypeOption.push(partie.carrier_insurance_type_option);
										}
										if (partie.type=="referredout_attorney"){
											switch(partie.claim) {
												case "3P":
													partie.claim = "Third Party";
													break;
												case "SER":
													partie.claim = "Serious and Willful";
													break;
											}
										}
										if (self.collection.panel_title=="Dashboard") {
											if (partie.type=="applicant"){
												if (!blnWCAB) {
													partie.type="applicant";
													partie.partie_type="Plaintiff"
												} else {
													partie.type="applicant";
												}
											}
										} else {
											if (partie.type=="applicant"){
												if (!blnWCAB) {
													partie.type="applicant";
													partie.partie_type="Plaintiff"
												} else {
													partie.type="applicant";
												}
											}
										}
									});
									if (arrClaimNumber.length > 0) {
										claim_number = arrClaimNumber.join("; ");
									}
									if (arrCarrierInsuranceTypeOption.length > 0) {
										//console.log("here");
										carrier_insurance_type_option = arrCarrierInsuranceTypeOption.join("; ");
									}
									var applicant_ssn = self.collection.toJSON()[0].ssn;
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
									
									if (user_data_path == 'A1') {
										self.model.set("case_number", self.model.get("cpointer"));
									}
									
									var kase_type = self.model.get("case_type");
									var blnWCAB = isWCAB(kase_type);
									self.model.set("blnWCAB", blnWCAB);
									
									var kase = self.model;
									var statute_limitation = kase.get("statute_limitation");
									
									if (statute_limitation=="00/00/0000") {
										statute_limitation = "";
									}
									
									var start_date = "";
									var end_date = "";
									if (kase.get("start_date") == "Invalid date" || kase.get("start_date")=="00/00/0000") {
										start_date = "";
										kase.set("start_date", ""); 
									}
									if (kase.get("start_date")!="") {
										start_date = moment(kase.get("start_date")).format('MM/DD/YYYY');
									}
							
									var show_end = "";
									var checkedCT = "";
									var ctHidden = "hidden";
									if (kase.get("end_date") == "" || kase.get("end_date") == "00/00/0000") {
										end_date = "";
									} else {
										end_date = moment(kase.get("end_date")).format('MM/DD/YYYY');
										end_date = " - " + end_date + " CT";
									}
									
									self.model.set("claim_number", claim_number);
									
									//show the parties
									$(self.el).html(self.template({parties: parties, case_id: self.collection.case_id, case_uuid: self.collection.case_uuid, panel_title: self.collection.panel_title, injury: injury.toJSON(), claim_number: claim_number, specialty:specialty, medical_provider_rating: medical_provider_rating, doctor_type: doctor_type, assigned_to: assigned_to, case_status: case_status, case_substatus: case_substatus, attorney: attorney, rating: rating, worker: worker, applicant_name: "", dashboard_dob: "", dashboard_age: "", applicant_ssn: applicant_ssn, applicant_language: "", kase: kase, start_date: start_date, end_date: end_date, statute_limitation: statute_limitation, carrier_insurance_type_option: carrier_insurance_type_option, blnWCAB: blnWCAB}));
									
								}
							});
						}			
					}
				});
					
			}
		});
		
		
		return this;
	},
	searchQME: function(event) {
		event.preventDefault();
		current_case_id = this.model.get("case_id");
		//get the applicant zip, pass it to the search form
		var kase = kases.findWhere({case_id:  current_case_id});
		var applicant_id = kase.get("applicant_id");
		//fetch the applicant, pass zip forward
		var person = new Person({id: applicant_id});
		person.fetch({
			success: function (person) {
				if (person.get("zip")!="") {
					document.location.href = "#qme/" + person.get("zip");
				} else {
					document.location.href = "#qme/-2";
				}
			}
		});
		
	},
	searchKases: function (event) {
		var element = event.currentTarget;
		
		var element_class = $("#list_kases").attr("class");
		var arrElement = element_class.split("_");
		var key = arrElement[1];
		var modifier = arrElement[2];
		
		kase_searching = true;
		blnSearched = true;
		$('#ikase_loading').html(loading_image);
		var self = this;
		//var key = $('#srch-term').val();
		
		if (typeof key =="undefined") {
			return;
		}
		var my_kases = new KaseCollection();
		//look for modifiers
		
		blnSearchingKases = true;
		search_kases = my_kases.searchDB(key, modifier);

		if (this.model.length == 0) {
			this.model = kases.clone();
		}
		
		$('#search_results').html(new kase_listing_view({collection: kases, model: ""}).render().el);
    },
	composeExam: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeExam(element.id);
	},
	quickNotes: function(event) {
		blnQuickNotes = true;
		//document.location.href = "#notes/" + this.model.get("case_id");
		this.newNotes(event);
	},
	relatedKases: function(event) {
		document.location.href = "#kases/related_cases/" + this.model.get("case_id");
	},
	composeRelated: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeRelated(element.id);
	},
	doTimeouts: function() {
		var self = this;
		
		//gridster the edit tab
		gridsterIt(7);
		self.getApplicantImage();
		
		var claims = this.model.get("claims").replace("Claims: ", "");
		
		//let's see if the claims have matching referredout
		if (claims != "") {
			//we need to get the referredout attorney first,
			//and then look up the adhocs for the atty
			//now we have to get the adhocs for the carrier
			var collection_length = referredout_attorney_parties.length;
			for(i=0;i<collection_length;i++) {
				referredout_attorney_partie = referredout_attorney_parties[0];
				referredout_attorney_partie.adhocs = new AdhocCollection([], {case_id: self.model.get("case_id"), corporation_id: referredout_attorney_partie.get("corporation_id")});
				referredout_attorney_partie.adhocs.fetch({
					success:function (adhocs) {
						var adhoc_claims = adhocs.findWhere({"adhoc": "claims"});
						var claims_value = "";
						if (typeof adhoc_claims != "undefined") {
							//we found the adhoc claims
							claims_value = adhoc_claims.get("adhoc_value");
							if (claims_value!="") {								
									$("#" + claims_value + "Holder").css("background", "");
									//we need to remove link
									$("#" + claims_value + "Holder").html("<a href='#parties/" + self.model.get("case_id") + "/" + referredout_attorney_partie.get("corporation_id") + "/referredout_attorney' class='white_text'>" + $("#" + claims_value + "Holder").text() + "</a>");
									//add a title to each holder
									$("#" + claims_value + "Holder").prop("title", "Referred Out Atty: " + referredout_attorney_partie.get("company_name"));
								
							}
						} 
					}
				});
			}
		}
	},
	getExams:function() {
		var self = this;
		
		var exams = new ExamCollection({case_id: this.model.get("case_id")});
		exams.fetch({
			success: function (data) {
				var exam_info = new Backbone.Model;
				exam_info.set("case_id", self.model.get("case_id"));
				exam_info.set("holder", "kase_exam_card");
				$('#kase_exam_card').html(new exam_listing({collection: data, model:exam_info}).render().el);
				$('#kase_exam_card_holder').fadeIn();
			}
		});
	},
	getEvents: function() {
		var self = this;
		
		var occurences = new AllKaseEvents({case_id: this.model.get("case_id")});
		occurences.fetch({
			success: function (occurences) {
				if (occurences.length > 0) {
					var event_listing_info = new Backbone.Model;
					event_listing_info.set("title", "Upcoming Events");
					event_listing_info.set("homepage", true);
					event_listing_info.set("event_class", "upcoming");
					$('#kase_events_card').html(new event_listing({collection: occurences, model: event_listing_info}).render().el);
					$('#kase_events_card_holder').fadeIn();
				}
			}
		});
	},
	getApplicantImage: function() {
		var self = this;
		//let's get any image
		var kase_documents = new DocumentCollection([], { case_id: self.collection.case_id, attribute: "applicant_picture" });
		kase_documents.fetch({
			success: function(data) {
				if ( data.toJSON().length > 0) {
					var document_filename = data.toJSON()[0].document_filename;
					if (document_filename!="") {
						$('#applicant_picture').html("<a href='D:/uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "' target='_blank' style='cursor:pointer' title='Click to expand'><img src='D:/uploads/" + customer_id + '/' + current_case_id + '/' + document_filename + "' class='applicant_thumb'></a>");
						$('#applicant_picture').fadeIn();
						$("#applicant_pictureGrid").fadeIn();
					}
				}
			}
		});
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	editQuickNote: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		alert(element.id);
		//composeNewNote(element.id);
	},
	newEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		generateEnvelope(element.id, "html");
	},
	newPDFEnvelope: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		generateEnvelope(element.id, "pdf");
	},
	newMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	renderComplete: function(injury, specialty, medical_provider_rating, doctor_type, assigned_to) {
		var self = this;
		var claim_number = "";
		var carrier_insurance_type_option = "";
		var case_status = this.collection.toJSON()[0].case_status;
		var case_substatus = this.collection.toJSON()[0].case_substatus;
		var attorney = this.collection.toJSON()[0].attorney;
		var worker = this.collection.toJSON()[0].worker;
		var rating = this.collection.toJSON()[0].rating;
		var applicant_name = this.collection.toJSON()[0].full_name;
		var applicant_language = this.collection.toJSON()[0].language;
		var dashboard_dob = this.collection.toJSON()[0].dob;
		var dashboard_age = "";
		if (dashboard_dob != "") {
			dashboard_dob = moment(dashboard_dob).format('MM/DD/YYYY');
			dashboard_age = " (" + dashboard_dob.getAge() + " years old)";
		}
		var applicant_ssn = this.collection.toJSON()[0].ssn;
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
		
		//now we have to get the adhocs for the carrier
		var carrier_partie = self.collection.findWhere({"type": "carrier"});
		if (typeof carrier_partie == "undefined") {
			carrier_partie = new Corporation({ case_id: self.collection.id, type:"carrier" });
			carrier_partie.set("corporation_id", -1);
			carrier_partie.set("partie_type", "Carrier");
			carrier_partie.set("color", "_card_missing");
			if (self.model.get("blnWCAB")) {
				self.collection.add(carrier_partie);
			}
		}
		carrier_partie.adhocs = new AdhocCollection([], {case_id: injury.case_id, corporation_id: carrier_partie.get("corporation_id")});
		carrier_partie.adhocs.fetch({
			success:function (adhocs) {
				var adhoc_claim_number = adhocs.findWhere({"adhoc": "claim_number"});
				
				if (typeof adhoc_claim_number != "undefined") {
					claim_number = adhoc_claim_number.get("adhoc_value");
				}
				var adhoc_carrier_insurance_type_option = adhocs.findWhere({"adhoc": "insurance_type_option"});
									
				if (typeof adhoc_carrier_insurance_type_option != "undefined") {
					carrier_insurance_type_option = adhoc_carrier_insurance_type_option.get("adhoc_value");
					//console.log(carrier_insurance_type_option);
				}
				//get the quick note
				
				var quick_notes = new NotesByType([], {type: "quick", case_id: self.model.get("case_id")});
				quick_notes.fetch({
					success: function(data) {	
						var notes = data.toJSON();
						var arrNotes = [];
						 _.each(notes , function(quicknote) {
							 var thenote = quicknote.note;
							thenote = thenote.replaceAll("<p>", "<p style='font-size:large'>");
							arrNotes[arrNotes.length] = "<div class='quicknote_row' id='quicknote_" + quicknote.notes_id + "'><div style='font-style:italic; font-size:0.8em'>" + moment(quicknote.dateandtime).format("MM/DD/YY h:mm a") + " by " + quicknote.entered_by + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='edit_quicknote' id='quick_note_" + current_case_id + "_" + quicknote.notes_id + "' style='font-style:normal; color:#00FFFF; font-size:1.5em; cursor:pointer;z-index:9999;' onClick='javascript:editQuickNotes(event)' data-toggle='modal' data-target='#myModal4'><i class='glyphicon glyphicon-edit' style='color:#00FFFF'>&nbsp;</i></span></div><div>" + thenote + "</div></div>";  
						 });
						quick_note = arrNotes.join("\r\n");
						
						var arrClaimNumber = [];
						var arrCarrierInsuranceTypeOption = [];
						var claim_number = "";
						//var carrier_insurance_type_option = "";
						
						var parties = self.collection.toJSON();
						_.each( parties, function(partie) {
							if (partie.doctor_type == null) {
								partie.doctor_type = "";
							}
							if (partie.claim_number!="" && partie.claim_number!=null) {
								arrClaimNumber.push(partie.claim_number);
							}
							if (partie.carrier_insurance_type_option!="" && partie.carrier_insurance_type_option!=null) {
								arrCarrierInsuranceTypeOption.push(partie.carrier_insurance_type_option);
							}
						});
						if (arrClaimNumber.length > 0) {
							claim_number = arrClaimNumber.join("; ");
						}
						
						
						if (customer_id == 1049) {
							self.model.set("case_number", self.model.get("cpointer"));
						}
						
						var kase_type = self.model.get("case_type");
						var blnWCAB = isWCAB(kase_type);
						self.model.set("blnWCAB", blnWCAB);
						
						var kase = self.model;
						var statute_limitation = kase.get("statute_limitation");
						
						if (statute_limitation=="00/00/0000") {
							statute_limitation = "";
						}
						
						var start_date = "";
						var end_date = "";
						if (kase.get("start_date") == "Invalid date" || kase.get("start_date")=="00/00/0000") {
							start_date = "";
							kase.set("start_date", ""); 
						}
						if (kase.get("start_date")!="") {
							start_date = moment(kase.get("start_date")).format('MM/DD/YYYY');
						}
				
						var show_end = "";
						var checkedCT = "";
						var ctHidden = "hidden";
						if (kase.get("end_date") == "" || kase.get("end_date") == "00/00/0000") {
							end_date = "";
						} else {
							end_date = moment(kase.get("end_date")).format('MM/DD/YYYY');
							end_date = " - " + end_date + " CT";
						}
						
						self.model.set("claim_number", claim_number);
						
						$(self.el).html(self.template({parties: parties, case_id: self.collection.case_id, case_uuid: self.collection.case_uuid, panel_title: self.collection.panel_title, injury: injury.toJSON(), claim_number:claim_number, specialty:specialty, medical_provider_rating:medical_provider_rating, doctor_type: doctor_type, assigned_to: assigned_to, case_status: case_status, case_substatus: case_substatus, attorney: attorney, rating: rating, worker: worker, applicant_name: applicant_name, dashboard_dob: dashboard_dob, dashboard_age: dashboard_age, applicant_ssn: applicant_ssn, applicant_language: applicant_language, kase: kase, start_date: start_date, end_date: end_date, statute_limitation: statute_limitation, quick_note: quick_note, carrier_insurance_type_option: carrier_insurance_type_option, blnWCAB: blnWCAB}));
						
						//console.log("after:" + moment().format("h:m:s"));
						
						//gridster the edit tab
						setTimeout(function() {
							gridsterIt(7);
						}, 100);
						setTimeout(function() {
							self.getApplicantImage();
						}, 770);	
						
						//custom just for leyva
						if (customer_id == 1070) {
							setTimeout(function() {
								self.getExams();
							}, 1100);
							setTimeout(function() {
								self.getEvents();
							}, 990);
						}						
					}
				});
			}
		});
	},
	confirmdeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#data_list_" + id).fadeOut("slow", function() {
			$("#confirm_delete_" + id).fadeIn();
		})
	},
	canceldeletePartie: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_" + id).fadeOut("slow", function() {
			$("#data_list_" + id).fadeIn();
		})
	},
	deletePartie: function(event) {
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		deleteElement(event, id, "corporation");
	},
	editQuickNote: function(event) {
		event.preventDefault();
		//$("#edit_quicknote").fadeOut(function() {
			//$("#save_quicknote").fadeIn();
		//});
		/*
		$("#noteSpan").fadeOut(function() {
			$("#noteInput").fadeIn();
		});
		*/
	},
	saveQuickNote: function(event) {
		event.preventDefault();
		var case_id = $("#case_id").val();
		var case_uuid = $("#case_uuid").val();
		var notes_id = $("#notes_id").val();
		var url = "api/notes/add";
		var formValues = "table_name=notes&table_id=" + notes_id + "&noteInput=" + encodeURIComponent($("#noteInput").val()) + "&table_attribute=quick&type=quick&title=Kase%20Quick%20Note";
		if (notes_id!="") {
			url = "api/notes/update";
		} else {
			formValues += "&case_uuid=" + case_uuid + "&case_id=" + case_id;
		}
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					//console.log(data.error.text);
					saveFormFailed(data.error.text);
				} else { 
					//hide the text box and save button, show the note in span
					//show the edit button
					$("#save_quicknote").fadeOut(function() {
						$("#edit_quicknote").fadeIn();
					});
					$("#noteSpan").html($("#noteInput").val());
					$("#noteInput").fadeOut(function() {
						$("#noteSpan").fadeIn();
					});
				}
			}
		});
	}
});
function generateEnvelope(element_id, output_fileformat) {
	var url = 'api/letter/envelope';
	if (typeof output_fileformat == "undefined") {
		output_fileformat = "pdf";
	}
	if (output_fileformat=="pdf") {
		url = 'api/envelope/create';
	}
	var arrID = element_id.split("_");
	var blnMedical = false;
	if (arrID.length==4) {
		//i need this because I messed up the id, so now kludge... 08172018
		blnMedical = (arrID[2]=="provider");
	}
	if (arrID.length==3 || blnMedical) {
		var additional = "";
		var corporation_id = arrID[arrID.length - 1];
		var partie_type = arrID[arrID.length - 2];
		if (blnMedical) {
			partie_type = "medical_provider";
		}
	} else {
		if (arrID.length==4) {
			//must have a compound partie type name...
			var additional = "";
			var corporation_id = arrID[arrID.length - 1];
			var partie_type = arrID[arrID.length - 3] + "_" + arrID[arrID.length - 2];
			
		} else {
			var additional = arrID[arrID.length - 1];
			var corporation_id = arrID[arrID.length - 2];
			var partie_type = arrID[arrID.length - 3];
		}
	}
	var arrID = element_id.split("_")
	var the_id = arrID[arrID.length - 1];
	
	if (document.location.hash.indexOf("#letters") == 0) {
		if (partie_type=="applicant"){
			if (the_id.indexOf("P") < 0) {
				the_id = "P" + the_id;
				arrID[arrID.length - 1] = the_id;
			}
		} else {
			if (the_id.indexOf("C") < 0) {
				the_id = "C" + the_id;
				arrID[arrID.length - 1] = the_id;
			}
		}
	}
	element_id = arrID.join("_");
	var formValues = "corporation_id=" + corporation_id + "&partie_type=" + partie_type + "&output_fileformat=" + output_fileformat +  "&additional=" + additional;
	
	$.ajax({
	url:url,
	type:'POST',
	dataType:"json",
	data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				if (output_fileformat=="pdf") {
					//$("#" + element_id.replace(output_fileformat + "envelope", "pdf_" + output_fileformat)).hide();
					//$("#" + element_id).hide();
					$("#" + element_id).fadeOut(
						function(){
							var letter_url = "api/download.php?file=" + data.file;
							$("#" + element_id.replace(output_fileformat + "envelope", "feedback_" + output_fileformat)).html("<a href='" + letter_url + "' title='Click to download PDF-format envelope' class='white_text' target='_blank' style='background:green; padding:2px'>ready&nbsp;&#10003;</a>");
						}
					);
				} else {
					$("#" + element_id).fadeOut(
						function(){
							var letter_url = "api/download.php?file=" + data.file;
							$("#" + element_id.replace(output_fileformat + "envelope", "feedback_" + output_fileformat)).html("<a href='" + letter_url + "' title='Click to download Word-format envelope' class='white_text' target='_blank' style='background:green; padding:2px'>ready&nbsp;&#10003;</a>");
							
							//window.open(letter_url);
							/*
							} else {
								$("#" + element_id.replace(output_fileformat + "envelope", "feedback_" + output_fileformat)).html("<a href='" + data.file + "' title='Click to open " + output_fileformat + "-format envelope' class='white_text' target='_blank'>" + output_fileformat + " ready</a>");
							}
							*/
						}
					);
				}
			}
		}
	});
}
function editQuickNotes(event) {
	var element = event.currentTarget;
	event.preventDefault();
	
	composeNewNote(element.id);
}
var request_id = "";
function checkRequestLocations(the_request_id) {
	request_id = the_request_id;
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html().trim();
		//is this location in matrix
		var url = "https://v4.ikase.org/api/kases/matrixrequestlocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + request_id + "&facility=" + encodeURIComponent(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$background_color = "blue";
					
					$("#add_on_holder_" + data.field_id).html("<span style='background:blue;color:white;padding:1px'>Sent to Matrix - " + data.assigned_date + "</span>");
					//was it imported?
					if (data.deleted == "Y" && data.verified == "Y") {
						//it's both, so it was looked at and cancelled, meaning it's in matrix under a different set of data, maybe a misspell?
						$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
					} else {
						//active locations
						checkLocations();
					}
				} else {
					//$("#add_on_holder_" +  data.field_id).html("<button onclick='getAddOn(" + data.field_id + ")' class='btn btn-xs btn-primary'>Send to Matrix</button>");
					//$("#add_on_holder_" +  data.field_id).fadeIn();
					
					//but let's do secondary
					checkMatrixSysLocations(the_request_id);
				}
			}
		});
	}
}
function checkMatrixSysLocations(the_request_id) {
	//secondary check, it might already be in matrix without the request
	request_id = the_request_id;
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html().trim();
		//is this location in matrix
		var url = "https://www.ikase.org/api/kases/matrixsyslocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + request_id + "&facility=" + encodeURIComponent(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$background_color = "blue";
					
					$("#add_on_holder_" + data.field_id).html("<span style='background:blue;color:white;padding:1px'>Sent to Matrix - " + data.assigned_date + "</span>");
					//was it imported?
					if (data.deleted == "Y" && data.verified == "Y") {
						//it's both, so it was looked at and cancelled, meaning it's in matrix under a different set of data, maybe a misspell?
						$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
					}
				} else {
					$("#add_on_holder_" +  data.field_id).html("<button onclick='getAddOn(" + data.field_id + ")' class='btn btn-xs btn-primary'>Send to Matrix</button>");
					$("#add_on_holder_" +  data.field_id).fadeIn();
				}
			}
		});
	}
}
function checkLocations(the_request_id) {
	request_id = the_request_id;
	var add_on_locations = $(".add_on_location");
	var arrLength = add_on_locations.length;
	for(var i = 0; i < arrLength; i++) {
		var element = add_on_locations[i];
		var element_id = element.id;
		var id = element_id.split("_")[3];
		var partie_name = $("#partie_name_" + id).html();
		//is this location in matrix
		var url = "https://v4.ikase.org/api/kases/matrixlocation";
		var formValues = "field_id=" + id + "&id=" + current_case_id + "&order_id=" + order_id + "&facility=" + encodeURIComponent(partie_name);
		//return;
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if (data.imported=="Y") {
					$("#add_on_holder_" + data.field_id).html("<span style='background:green;color:white;padding:1px'>Added to Matrix - " + data.assigned_date + "</span>");
				} 
			}
		});
	}
}
function getAddOn(field_id) {
	//console.log("field_id:", field_id);
	//return;
	var formValues = "order_id=" + request_id;
	var partie_id = field_id;
	var partie_type = "medical_provider";
	//look up the partie
	var url = "https://v4.ikase.org/api/corporation/" + partie_type + "/" + partie_id;
	//return;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"text",
		data: "",
		success:function (corp_data) {
			//POST THE DATA to matrix now as a new location
			exportAddOn(corp_data, field_id);
			//console.log(corp_data);
		}
	});
}
function exportAddOn(corp_data, field_id) {
	var url = "https://v4.ikase.org/api/kases/addon";
	var formValues = "case_id=" + current_case_id + "&request_id=" + request_id + "&data=" + corp_data;
	//return;
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if (data.success) {
				$("#add_on_holder_" + field_id).css("background", "green");
				$("#add_on_holder_" + field_id).html("Matrix &#10003;");
			}
		}
	});
}
window.refer_vocational_view = Backbone.View.extend({
    initialize:function () {
        //this.collection.on("change", this.render, this);
		//this.collection.on("add", this.render, this);
    },
	events: {
		"click #submit_refer":			"submitRefer",
		"change .form_input":			"submitDisabled",
		"click .form_input":			"submitDisabled"
	},
    render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "refer_vocational_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
		setTimeout(function() {
			$('.refer_vocational #message_attachments').html(new message_attach({model: self.model}).render().el);
			
		}, 789);
		
        return this;
    },
	submitDisabled:function(event) {
		//event.preventDefault();
		var blnVoucher = false;
		if (document.getElementById("voucher_sjdb").checked) {
			blnVoucher = true;
		}
		if (document.getElementById("voucher_rtwsp").checked) {
			blnVoucher = true;
		}
		var blnMedDate = ($("#max_med_date").val()!="");
		var blnUpload = ($("#FileUpload1")[0].files.length > 0);
		
		var blnDisabled = true;
		if (blnVoucher && blnMedDate && blnUpload) {
			blnDisabled = false;
		}
		document.getElementById("submit_refer").disabled = blnDisabled;
	},
	submitRefer:function(event) {
		var self = this;
		
		event.preventDefault();
		var max_med_date = $("#max_med_date").val();
		var voucher = "";
		if (document.getElementById("voucher_sjdb").checked) {
			voucher = $("#voucher_sjdb").val();
		}
		if (document.getElementById("voucher_rtwsp").checked) {
			voucher = $("#voucher_rtwsp").val();
		}
		
		var form = $("#refer_vocational_form");
		var formData = new FormData();
		formData.append('fileName', $("#FileUpload1")[0].files[0]);
		formData.append('case_id', current_case_id);
		formData.append('max_med_date', max_med_date);
		formData.append('voucher', voucher);
		
		$.ajax({
			url: "api/refervocation",
			type: "POST",
			data:  formData,
			dataType:"json",
			contentType: false,
			cache: false,
			processData:false,
		   	success: function(data) {
				if (data.success) {
					$("#myModalLabel").css("color", "lime");
					
					setTimeout(function() {
						$("#myModalLabel").css("color", "white");
						setTimeout(function() {
							$("#myModal4").modal("toggle");
						}, 500);
					}, 1700);
					
					resetCurrentContent();
					
					self.sendVoucherEmail(data.short_url, data.demo_url);
				}
			  }          
		});
	},
	sendVoucherEmail: function(short_url, demo_url) {
		var case_number = this.model.get("case_number");
		var max_med_date = $("#max_med_date").val();
		var voucher = "";
		if (document.getElementById("voucher_sjdb").checked) {
			voucher = $("#voucher_sjdb").val();
		}
		if (document.getElementById("voucher_rtwsp").checked) {
			voucher = $("#voucher_rtwsp").val();
		}
		var note = "Referral for Vocational Services " + case_number + " was requested by " + login_nickname + ".";
		note += "||";
		note += "Voucher: " + voucher;
		note += "||";
		note += "Max Medical Improvement Date: " + moment(max_med_date).format("MM/DD/YYYY");
		note += "||";
		note += "Demographics: " + demo_url;
		note += "||";
		note += "Attachment: " + short_url;
		note += "||";
		note += "||";
		note += "These links will remain valid for a week.";
		//note += "Attachments: https://www.ikase.org/api/download_vocational/" + (customer_id * 3) + "a" + (activity_id * customer_id);
		
		var sent_to = "VocationalStaff";
		
		//send interoffice
		var formValues = { 
			table_name : "message",
			message_to : sent_to,
			messageInput: note,
			case_id: current_case_id,
			send_document_id: "",
			subject: "Referral for Vocational Services from " + customer_name + " RE:" + case_number,
			from: login_username,
			notification: "N"
		};
		var url = "api/messages/add";
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else { 
					//console.log("sent");
					//console.log(data);
					setTimeout(function() {
						emptyBuffer(customer_id);
					}, 6000);
				}
			}
		});
	}
});