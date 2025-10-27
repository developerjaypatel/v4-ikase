window.vservices_view = Backbone.View.extend({
    initialize:function () {
        
    },
    events:{
		"click .compose_message":				"newMessage",
		"click #vservices_all_done":			"doTimeouts"
    },
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "vservices_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var self = this;
		//get the dois for this kase
		var doi_label = 'No DOI';
		
		var kase_dois = dois.where({case_id: this.model.get("case_id")});
		
		var mymodel = this.model.toJSON();
		var vservices = this.collection.toJSON();
		_.each( vservices, function(vservice) {
			var arrDOIs = [];
			var arrSendKase = [];
			
			vservice.company_name = "<input type='hidden' id='vservice_name_" + vservice.vservice_id + "' value='" + vservice.name + "'>";
			
			var site = vservice.company_site;
			if (site!="") {
				site = site.replace("http://", "");
				site = "<div style='padding-bottom:0px; '>Site: <a href='http://" + site + "' target='_blank' title='Click to visit site' class='white_text'>" + vservice.company_site + "</a></div>";
			}
			vservice.company_site = site;
			
			if (vservice.description.trim()!="" && vservice.description!=null) { 
				vservice.description = '<div style="padding-bottom:5px">' + vservice.description + '</div><hr />';
			} else {
				vservice.description = "";
			}
			if (vservice.phone.trim()!="" && vservice.phone!=null) { 
				vservice.phone = '<div style="padding-bottom:0px">Phone: ' + vservice.phone + '</div>';
			} else {
				vservice.phone = "";
			}
			if (vservice.fax.trim()!="" && vservice.fax!=null) { 
				vservice.fax = '<div style="padding-bottom:0px">Fax: ' + vservice.fax + '</div>';
			} else {
				vservice.fax = "";
			}
			if (vservice.email.trim()!="" && vservice.email!=null) { 
				vservice.email = '<div style="padding-bottom:0px">Email: <a href="mailto:' + vservice.email + '"  title="Click to visit site" class="white_text"  id="vservice_email_' + vservice.vservice_id + '">' + vservice.email + '</a></div>';
			} else {
				vservice.email = "";
			}
			if (vservice.full_address!="" && vservice.full_address!=null) { 
				vservice.full_address = '<div style="padding-bottom:0px">' + vservice.full_address + '</div>';
			} else {
				vservice.full_address = "";
			}
			
			//brochure
			vservice.brochure = "<a href='D:/uploads/vservices/" + vservice.vservice_id + "/brochure.pdf' target='_blank' title='Click to open " + vservice.name + " brochure'><img src='D:/uploads/vservices/" + vservice.vservice_id + "/thumbnail.jpg'></a>";
			
			_.each(kase_dois , function(doi) {
				var start_date = doi.get("start_date");
				/*
				<a class="compose_message" id="vservice_<%= vservice.vservice_id %>" data-toggle="modal" data-target="#myModal4" title="Click to email this Kase to <%=vservice.name %>" style="cursor:pointer; font-size:11px; font-weight:normal; background:black; color:white;padding:2px; cursor:pointer">Send Kase</a>
				*/
				
				if (start_date!="" && start_date!="0000-00-00") {
					start_date = "<a class='compose_message' id='vservice_" + vservice.vservice_id + "_" +doi.get("id") + "' data-toggle='modal' data-target='#myModal4' title='Click to email this Kase to " + vservice.name + "' style='cursor:pointer; font-size:11px; font-weight:normal; background:black; color:white;padding:2px; cursor:pointer'>Send " + moment(start_date).format("MM/DD/YYYY");
				} else {
					start_date = "No Injury Info";				
				}
				
				var thedoi = start_date;
				if (doi.get("end_date") != "0000-00-00") {
					thedoi += "&nbsp;-&nbsp;" + moment(doi.get("end_date")).format("MM/DD/YYYY") + "&nbsp;CT";
				}
				thedoi += "</a>";
				
				arrSendKase.push(thedoi);
			});
			//link per doi
			vservice.dois = arrSendKase.join("<br>");
			
			//back color
			vservice.backcolor = "glass_dark.png";
			if (vservice.type == "voucher services") {
				vservice.backcolor = "glass_misc.png";
			}
		});
		
		try {
			$(this.el).html(this.template({vservices: vservices, case_id: mymodel.case_id}));
		}
		catch(err) {
			var view = "vservices_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
        return this;
    },
	doTimeouts: function(event) {
		var self = this;
		
		//gridster the edit tab
		gridsterById("gridster_vservices_cards");
		
		//if (customer_id == 1033) { 
		
			//var case_id = this.model.get("case_id");
			var case_id = current_case_id;
			var kase = kases.findWhere({case_id: case_id});
			console.log(kase.toJSON());
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
						carrier_partie = new Corporation({ case_id: this.model.get("case_id"), type:"carrier" });
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
	},
	newMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	}
});
