var manual_id = false;
window.partie_view = Backbone.View.extend({
    initialize:function () {
		_.bindAll(this);
		_.bindAll(this, "togglePartieEdit", "resetPartieForm", "saveSuccessful");
    },
    events:{
  		"click .partie .delete":					"deletePartieView",
		"click .partie .new":						"newPartie",
		"click .partie .save":						"confirmApply",
		"click .partie .bill_time":					"billTimeSpent",
		"click .partie .save_field":				"savePartieViewField",
		"click .partie .edit": 						"editPartieForm",
		"click .partie .reset": 					"resetPartieForm",
		"click .kase .calendar": 					"showCalendar",
		"keyup .partie .input_class": 				"valuePartieViewChanged",
		"dblclick .partie .gridster_border": 		"editPartieViewField",
		"click #manual_address":					"manualAddress",
		"click #lookup_address":					"lookupAddress",
		"keyup .partie #full_addressInput": 		"checkAddress",
		"blur .partie #full_addressInput": 			"hideBingResults",
		"click .partie .compose_new_note": 			"newNotes",
		"click .apply_yes":							"applyPartie",
		"click .apply_no":							"cancelApply",
		"click #add_to_dash":						"addToDash",
		"keyup .partie #company_nameInput":			"checkForToken",
		"keydown .partie #company_nameInput":		"checkForToken",
		"keyup #token-input-company_nameInput":		"setCompanyNameCurrent",
		"keydown #token-input-company_nameInput":	"setCompanyNameCurrent",
		"blur #token-input-company_nameInput":		"clearCurrent",
		"keyup .partie #full_nameInput":			"checkForPersonToken",
		"keydown .partie #full_nameInput":			"checkForPersonToken",
		"keyup #token-input-company_nameInput":		"clearPartieID",
		"keyup #token-input-full_nameInput":		"setFullNameCurrent",
		"keydown #token-input-full_nameInput":		"setFullNameCurrent",
		"blur #token-input-full_nameInput":			"clearCurrent",
		"click #token_add_link":					"addNewLocation",
		"click #list_kases": 						"searchKases",
		"click .bing_address":						"selectBingAddress",
		"keyup .street":							"updateStreet",
		"keyup .suite":								"updateSuite",
		"keyup .city":								"updateCity",
		"keyup .state":								"updateState",
		"keyup .postal_code":						"updateZip",
		"click #add_address":						"addAddress",
		"click #clear_address":						"clearAddress",
		"click #clear_additional_address":			"clearAdditionalAddress",
		"click .map_partie":						"mapPartie",
		"change #party_type_optionInput":			"defendantPartyOption",
		"change #party_defendant_optionInput":		"defendantPartyChoice",
		"click #matrix_copy_yes":					"setCopy",
		"click #matrix_copy_no":					"noCopy",
		"click .copy_input":						"releaseContinueCopy",		
		"click #continue_copy":						"continueCopy",
		"click #scrape_injury":						"scrapeInjury",
		"focus #faxInput":							"scrollDown",
		"click .backtotop":							"scrollToTop",
		"click .override_partie":					"employerOverride",
		"blur .partie .input_class":				"autoSave",
		"click #partie_all_done":					"doTimeouts"
    },
    render:function () {
		if (typeof this.template != "function") {
			if (typeof this.model.get("holder") == "undefined") {
				this.model.set("holder", "content");
			}
			var view = "partie_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
	   
		var self = this;
		if (typeof this.model.get("grid_it")=="undefined") {
			//we do not show extra fields
			this.model.set("grid_it", false);
		}
		if (this.model.get("salutation")==null) {
			//we do not show extra fields
			this.model.set("salutation", "");
		}
		if (this.model.get("party_type_option")==null) {
			//we do not show extra fields
			this.model.set("party_type_option", "");
		}
		if (this.model.get("comments")==null) {
			//we do not show extra fields
			this.model.set("comments", "");
		}
		if (typeof this.model.get("intake_screen") == "undefined") {
			this.model.set("intake_screen", false);		
		}
		if (typeof this.model.get("person_id")=="undefined") {
			this.model.set("additional_partie", "y");
		} else {
			//this is a prior medical provider
			this.model.set("additional_partie", "p");
		}
		if (this.model.id==-2) {
			//p for prior
			this.model.set("additional_partie", "p");
		}
		this.model.set("blnSaving", false);
		
		var master_class = "";
		if (document.location.hash == "#intake") {
			master_class = "#" + this.model.get("partie").capitalize() + "_form ";
		}
		this.model.set("master_class", master_class);
		//phone and fax
		var phone = this.model.get("phone");
		var employee_phone = this.model.get("employee_phone");
		if (employee_phone!="" && phone == "") {
			phone = cleanPhone(employee_phone);
			this.model.set("phone", phone);
		}
		/*
		var cell_phone = this.model.get("cell_phone");
		var employee_cell_phone = this.model.get("employee_cell");
		if (employee_cell_phone!="" && cell_phone == "") {
			cell_phone = cleanPhone(employee_cell_phone);
			this.model.set("cell_phone", cell_phone);
		}
		*/
		var fax = this.model.get("fax");
		var employee_fax = this.model.get("employee_fax");
		if (employee_fax!="" && fax == "") {
			fax = cleanPhone(employee_fax);
			this.model.set("fax", fax);
		}
		
		
		if (this.model.get("partie")=="Employer") {	
			this.model.set("isEmployer", true);
			if (customer_id != 1075) {		
				//per steve 3/30/2017
				this.model.set("additional_partie", "n");
			}
		} else {
			this.model.set("isEmployer", true);
		}
		
		//medical provider copying instructions
		if (this.model.get("type")=="medical_provider") {
			var arrCopying = this.model.get("copying_instructions").split("|");
			if (arrCopying.length==4) {
				this.model.set("copying_instructions", arrCopying[0]);
				this.model.set("other_description", arrCopying[1]);
				this.model.set("any_all", arrCopying[2]);
				this.model.set("special_instructions", arrCopying[3]);
			} else {
				this.model.set("copying_instructions", "");
				this.model.set("other_description", "");
				this.model.set("any_all", "N");
				this.model.set("special_instructions", "");
			}
		}
		if (this.model.get("type")=="defendant") {
			if (this.model.get("id")=="-1") {
				this.model.set("id", "-1");
			}
		}
		if (this.model.get("type")=="applicant_attorney") {
			if (this.model.get("id")=="-1") {
				this.model.set("id", "-1");
			}
		}
		if (this.model.get("phone")==null) {
			this.model.set("phone", "");
		}
		if (this.model.get("fax")==null) {
			this.model.set("fax", "");
		}
		if (this.model.get("party_defendant_option")==null) {
			this.model.set("party_defendant_option", "");
		}
		//partie type
		this.model.set("color", "_card_fade");
		var partie_type = partie_settings.findWhere({blurb: this.model.type});
		if (typeof this.model.get("partie_type") == "undefined") {
			if (typeof partie_type !="undefined") {
				this.model.set("partie_type", partie_type.toJSON().partie_type);
				var color = partie_type.toJSON().color;
				if (color!="") {
					this.model.set("color", color);
				}
				this.model.set("adhoc_fields", partie_type.toJSON().adhoc_fields);
			} else {
				this.model.set("color", "_card_fade");
			}
		}
		if (typeof partie_type == "undefined") {
			if (this.model.get("type") == null) {
				this.model.set("partie_type", "");
			} else {
				var the_type = this.model.get("type");
				if (the_type.length < 4) {
					the_type = the_type.toUpperCase();
				} else {
					the_type = the_type.capitalize();
				}
				this.model.set("partie_type", the_type);
			}
		}
		
		if (this.model.get("show_employee")==false && typeof partie_type != "undefined") {
			this.model.set("show_employee", partie_type.toJSON().show_employee);
			this.model.set("employee_title", partie_type.toJSON().employee_title);
		}
		
		this.model.set("display_address", this.model.get("full_address"));
		//address
		if (this.model.get("street")!="" && this.model.get("street")!=null) {
			var arrAddress = [];
			arrAddress.push(this.model.get("street"));
			if (this.model.get("suite")!="") {
				arrAddress.push(this.model.get("suite"));
			}
			var city_state_zip = this.model.get("city") + ", " + this.model.get("state") + " " + this.model.get("zip");
			arrAddress.push(city_state_zip);
			this.model.set("display_address", arrAddress.join("<br>"));
		}
		if (typeof current_case_id == "undefined") {
			current_case_id = -1;
		}
		
		var kase_type = "";
		var kase_sub_type = "";
		var injury_type = "";
		var blnWCAB = false;
		if (this.model.get("intake_screen")) {
			if (current_case_id == -1) {
			current_case_id = $("#kase_form #id").val();
			}
			kase_type = $("#kase_form #case_typeInput").val();
			kase_subtype = $("#kase_form #case_subtypeInput").val();
			injury_type = $("#kase_form #injury_typeInput").val();
		}
		if (current_case_id!=-1) {
			if (kase_type=="") {
				var kase = kases.findWhere({case_id : current_case_id});
				//alert(kase);
				kase_type = kase.get("case_type");
				this.model.set("kase_type", kase_type);
				blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1 || kase_type.indexOf("W/C") > -1));
				
				injury_type = kase.get("injury_type");
			}
		} 
		if (document.location.hash == "#intake") {
			this.model.set("case_id", current_case_id);
			blnWCAB = ($("#case_typeInput").val()=="WCAB");
		}
		var blnSS = (kase_type.indexOf("social_security") == 0);
		var representing = "";
		if (!blnWCAB && !blnSS) {
			var arrInjury = [];
			if (injury_type!=null) {
				arrInjury = injury_type.split("|");
			}
			injury_type = arrInjury[0];
			if (arrInjury.length==2) {
				representing = arrInjury[1];
			}
			//I need to break up the copying instructions for venue if PI
			if (this.model.get("type")=="venue") {
				var extra = this.model.get("copying_instructions");
				if (extra!="") {
					try {
						extra = JSON.parse(extra);
						
						this.model.set("jurisdiction", extra.jurisdiction);
						this.model.set("county", extra.county);
						this.model.set("department", extra.department);
						this.model.set("department_phone", extra.department_phone);
						this.model.set("district", extra.district);
						this.model.set("branch", extra.branch);
					}
					catch(err) {
						console.log(err.message);
					}
				} else {
					this.model.set("jurisdiction", "");
					this.model.set("county", "");
					this.model.set("department", "");
					this.model.set("department_phone", "");
					this.model.set("district", "");
					this.model.set("branch", "");
				}
			}
		}
		this.model.set("injury_type", injury_type);
		this.model.set("representing", representing);
		
		this.model.set("blnWCAB", blnWCAB);
		this.model.set("blnSS", blnSS);
		var partie_display_title = this.model.get("partie_type");
		if (blnSS) {
			if (partie_display_title=="Plaintiff") {
				//partie_display_title = "Applicant";
				//per thomas 9/10/2018
				partie_display_title = "Claimant";
			}
		}
		this.model.set("partie_display_title", partie_display_title);
		
		//additional address
		this.model.set("additional_street", "");
		this.model.set("additional_suite", "");
		this.model.set("additional_city", "");
		this.model.set("additional_state", "");
		this.model.set("additional_zip", "");
		
		try {
			$(this.el).html(this.template({party: this.model.toJSON(), adhoc_name_values: this.collection}));
		}
		catch(err) {
			var view = "partie_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		//kinda global variable?
		this.releaseCopy = false;
		
		return this;
		
    },
	savePartieViewField: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var fieldname = element_id.replace("SaveLink", "Input");
		
		var url = "../api/corporation/update";
		var formValues = "table_name=corporation&case_id=" + current_case_id + "&corporation_id=" + $("#corporation_id").val();
		formValues += "&" + fieldname + "=" + $("#" + fieldname).val();
		
		$.ajax({
			url:url,
			type:'POST',
			data: formValues,
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#scrape_injury").html("Saved&nbsp;&#10003;");
					setTimeout(function() {
						$("#scrape_injury").html("");
					}, 2500);
				}
			}
		});
	},
	releaseContinueCopy: function(event) {
		if (!this.releaseCopy) {
			$("#specialInstructionsGrid").attr("data-sizey", "4");
			$("#continue_copy_holder").fadeIn();
		}
		this.releaseCopy = true;
	},
	scrapeInjury: function(event) {
		var self = this;
		
		var element = event.currentTarget;
		var element_id = element.id;
		
		var adj_number = "";
		if ($(".kase_adj_number").length > 0) {
			adj_number = $(".kase_adj_number")[0].innerHTML;
		}
		if (adj_number=="" || adj_number.indexOf("ADJ")===false) {
			adj_number = prompt("Please enter the ADJ", "ADJ");
			if (adj_number == null || adj_number == "" || adj_number.indexOf("ADJ")===false) {
				return;
			}
		}
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
				
				$("#full_nameInput").val(scrape.applicant.judge);
				$("#full_nameSpan").html(scrape.applicant.judge);
				
				$("#full_nameSaveLink").trigger("click");
				
				$("#" + element.id).html("saving...");
			}
		});
	},
	continueCopy: function(event) {
		var self = this;
		var start_copy_row = this.model.get("start_copy_row");
		$("#continue_copy_holder").hide();
		$("#copyingRequestGrid").remove();
		$("#partie_gridster_ul li").show();
		$("#copyingInstructionsGrid").attr("data-row", start_copy_row);
		$("#specialInstructionsGrid").attr("data-row", (+start_copy_row + 2));
		$("#specialInstructionsGrid").attr("data-sizey", "3");
		
		$("#token-input-company_nameInput").focus();
	},
	setCopy: function(event) {
		event.preventDefault();
		
		var self = this;
		//on yes or no
		$("#copyingRequestGrid").fadeOut(function(){
			//on yes
			$("#copyingRequestGrid").attr("data-row", "1");
			//store the location to restore later
			self.model.set("start_copy_row",$("#copyingInstructionsGrid").attr("data-row"));
			//we are doing prior now
			$("#additional_partie").val("c");
			//move things up
			$("#copyingInstructionsGrid").attr("data-row", "1");
			$("#specialInstructionsGrid").attr("data-row", "3");
			$("#saveButtonGrid").attr("data-row", "6");
			
			$("#copyingInstructionsGrid").fadeIn();
			$("#specialInstructionsGrid").fadeIn();		
			
			$("#saveButtonGrid").show();
			$("#saveButtonGrid").css("visibility", "visible");
			
			$(".backtotop").hide();
			$(".button_row .save").hide();	
			$("#continue_copy").hide();
			
		});
	},
	noCopy: function(event) {
		event.preventDefault();
		
		//on no
		$("#partie_gridster_ul li").show();
		$("#copyingInstructionsGrid").hide();
		$("#specialInstructionsGrid").hide();
		$("#copyingRequestGrid").remove();
		
		this.savePartie(event);
	},
	billTimeSpent: function(event) {
		//var element = event.currentTarget;
		event.preventDefault();
		var billing_time = prompt("Please enter minutes worked", "15");
    
    	if (billing_time != null) {
			$("#billing_time").val(billing_time);
		}
		this.confirmApply(event);
	},
	defendantPartyOption: function (event) {
		var element = event.currentTarget;
		var element_id = element.id;
		//alert(element_id);
		var element_value = $("#party_type_optionInput").val();
		
		if (element_value == "defendant") {
			//do stuffs
			
			$("#party_defendant_optionGrid").fadeIn();
		} else {
			return;
		}
    },
	defendantPartyChoice: function (event) {
		var element = event.currentTarget;
		
		var element_id = $("#party_defendant_optionInput").find(':selected')[0].id;
		//alert(element_id);
		var arrElement = element_id.split("_");
		//alert(arrElement[0] + " " + arrElement[1]);
		var element_value = $("#party_defendant_optionInput").val();
		
		$("#party_representing_id").val(arrElement[0]);
		$("#party_representing_name").val(arrElement[1]);
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
	hideBingResults: function() {
		var html = $("#bing_results").html();
		if (html!="") {
			setTimeout(function() {
				$("#bing_results").html("");
				$("#bing_results").fadeOut();
			}, 1234);
		}
	},
	selectBingAddress: function(event) {
		//utilities.js
		var partie = this.model.get("partie");
		selectBingAddress(event, partie, "bing_results");
		
		return;
		/*
		var element = event.currentTarget;
		var address_id = element.id.replace("bing_address_name_", "");
		var bing_address_data = $("#bing_address_data_" + address_id).val();
		if (bing_address_data!="") {
			var jdata = JSON.parse(bing_address_data);
			var partie = this.model.get("partie");
			$("#street_" + partie).val(jdata.addressLine);
			$("#city_" + partie).val(jdata.locality);
			$("#administrative_area_level_1_" + partie).val(jdata.adminDistrict);
			$("#postal_code_" + partie).val(jdata.postalCode);
			
			$("#full_addressInput").val(jdata.formattedAddress);
		}
		
		$("#bing_results").hide();
		$("#bing_results").html("");
		*/
	},
	updateFullAddress: function() {
		var partie = this.model.get("partie");
		var address_level = this.model.get("address_level");
		//fill-in full address
		var city = $("#" + address_level + "city_" + partie).val();
		var state = $("#" + address_level + "administrative_area_level_1_" + partie).val();
		var zip = $("#" + address_level + "postal_code_" + partie).val();
		var street = $("#" + address_level + "street_" + partie).val();
		var full_address = street;
		var suite = $("#" + address_level + "suiteInput").val();
		if (suite!="") {
			if (full_address!="") {
				full_address += ", ";
			}
			full_address += suite;
		}
		if (full_address!="") {
			full_address += ", ";
		}
		full_address += city + ", " + state + " " + zip;
		$("#" + address_level + "full_addressInput").val(full_address);
		$("#" + address_level + "full_addressSpan").html(full_address);
	},
	updateStreet: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("street", element.value);
		var address_level = "";
		if (element.className.indexOf("partie_additional_address") > 0) {
			address_level = "additional_";
		}
		this.model.set("address_level", address_level);
		this.updateFullAddress();
	},
	updateSuite: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("suite", element.value);
		var address_level = "";
		if (element.className.indexOf("partie_additional_address") > 0) {
			address_level = "additional_";
		}
		this.model.set("address_level", address_level);
		this.updateFullAddress();
	},
	updateCity: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("city", element.value);
		var address_level = "standard";
		if (element.className.indexOf("partie_additional_address") > 0) {
			address_level = "additional";
		}
		this.model.set("address_level", address_level);
		this.updateFullAddress();
	},
	updateState: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("state", element.value);
		var address_level = "standard";
		if (element.className.indexOf("partie_additional_address") > 0) {
			address_level = "additional";
		}
		this.model.set("address_level", address_level);
		this.updateFullAddress();
	},
	updateZip: function(event) {
		var self = this;
		var element = event.currentTarget;
		this.model.set("zip", element.value);
		
		//lookup
		if (element.value.length > 4) {
			var url = 'api/checkzip/' + element.value;
	
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (typeof data.city=="undefined") {
							return;
						}
						var partie = self.model.get("partie");
						$("#city_" + partie).val(data.city);
						$("#city_" + partie).css("visibility", "visible");
						$("#administrative_area_level_1_" + partie).val(data.state_prefix);
						$("#administrative_area_level_1_" + partie).css("visibility", "visible");
						
						self.updateFullAddress();
						
						//$("#full_addressSpan").fadeIn();
						if ($("#full_addressSpan").hasClass("hidden")) {
							$("#full_addressSpan").toggleClass("hidden");
						}
					}
				}
			});
		}
	},
	clearAddress: function(event) {
		event.preventDefault();
		
		$(".partie_address").val("");
	},
	clearAdditionalAddress: function(event) {
		event.preventDefault();
		
		$(".partie_additional_address").val("");
	},
	addAddress: function(event) {
		//console.log("add address");
		$("#additional_addressGrid").show();
		//lower the others
		this.repositionGrids();
		/*
		var grids = $(".gridster_border");
		var arrLength = grids.length;
		var row_counter = 0;
		for(var i = 0; i < arrLength; i++) {
			var grid = grids[i];
			var grid_id = grid.id;
			var data_row = $("#" + grid_id).attr("data-row");
			var the_display = $("#" + grid_id).css("display");
			
			if (the_display=="none") {
				continue;
			}
			if (data_row > 8) {
				var data_col = $("#" + grid_id).attr("data-col");
				if ($("#" + grid_id).hasClass("adhoc_grid")) {
					if (data_col==1) {
						$("#" + grid_id).attr("data-row", (row_counter + 3));
						i++;
						var right_grid = grids[i];
						var grid_id = right_grid.id;
						$("#" + grid_id).attr("data-row", (row_counter + 3));
					}
				} else {
					$("#" + grid_id).attr("data-row", (row_counter + 3));
				}
			}
			if (grid_id=="additional_addressGrid" || grid_id == "commentsGrid") {
				row_counter = row_counter + 3;
			} else {
				row_counter++;
			}
		}
		*/
	},
	mapPartie: function (event) {
		var self = this;
		var element = event.currentTarget;
		var element_id = element.id.split("_")[3];
		var partie = new Corporation({case_id: current_case_id, id: element_id});
		partie.fetch({
			success:function (data) {
				var company_full_address = self.model.get("full_address");
				//var url = "https://www.google.com/maps/dir/" + company_full_address + "/" + data.toJSON().full_address;
				var url = "https://www.bing.com/maps?where1=" + encodeURIComponent(company_full_address);
				window.open(url);
			}
		});
	},
	scrollDown: function() {
		if (this.model.get("intake_screen")) {
			return;
		}
		$('html, body').animate({scrollTop: 680}, 300);
		$("#saveButtonGrid").css("visibility", "visible");
	},
	scrollToTop: function(event) {
		event.preventDefault();
		$('html, body').animate({scrollTop: 0}, 300);
	},
	autoSave:function(event) {
		//!self.model.get("intake_request")
		if (!blnAutoSave) {
			return;
		}
		if (!this.model.get("intake_screen")) {
			return;
		}
		var element = event.currentTarget;
		
		$("#phone_intake_feedback_div").html("Autosaving...");
				
		var fieldname = element.id.replace("Input", "");
		var value = element.value;
		
		var partie = this.model.get("partie");
		var case_id = $("#kase_form #id").val();
		var id = $("#" + partie + "_form #table_id").val();
		var type = this.model.get("type");
		
		var url = "api/corporation/field/update";
		var formValues = "table_id=" + id + "&case_id=" + case_id + "&type=" + encodeURIComponent(type) + "&fieldname=" + fieldname + "&value=" + encodeURIComponent(value);
		if (id == "" || id=="-1") {
			url = "api/corporation/add";
			
			var company_name = $("#company_nameInput").val();
			var formValues = "table_name=corporation&case_id=" + case_id + "&" + fieldname + "=" + encodeURIComponent(value);
			if (formValues.indexOf("company_name") < 0) {
				formValues += "&company_name=" + encodeURIComponent(company_name);
			}
			formValues += "&type=" + encodeURIComponent(type);
		}
		
		var border = $("#" + partie + "_form #" + element.id).css("border");
			
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#" + partie + "_form #table_id").val(data.id);
					$("#" + partie + "_form #corporation_id").val(data.id);
					$("#" + partie + "_form #" + element.id).css("border", "2px solid lime");
					if (element.id=="company_nameInput") {
						$("#" + partie + "_form #company_nameGrid").css("border", "2px solid lime");
					}
					if (element.id=="full_nameInput") {
						$("#" + partie + "_form #full_nameGrid").css("border", "2px solid lime");
					}
					
					//actually, if this is a save, we need an update to set the case name
					if (url == "api/corporation/add") {
						//this should only  happen the first time because we have an id now
						$("#" + partie + "_form #company_nameInput").trigger("blur");
					}
					
					//address
					if (element.id=="full_addressInput" || element.id=="company_nameInput") {
						setTimeout(function() {
							$("#street_" + partie).trigger("blur");
						}, 300);
						setTimeout(function() {
							$("#suite_" + partie).trigger("blur");
						}, 400);
						setTimeout(function() {
							$("#city_" + partie).trigger("blur");
						}, 500);
						setTimeout(function() {
							$("#administrative_area_level_1_" + partie).trigger("blur");
						}, 600);
						setTimeout(function() {
							$("#postal_code_" + partie).trigger("blur");
						}, 600);
					}
					
					$("#phone_intake_feedback_div").html("Autosaved&nbsp;&#10003;");
					setTimeout(function() {
						$("#" + partie + "_form #" + element.id).css("border", "none");
						if (element.id=="company_nameInput") {
							$("#" + partie + "_form #company_nameGrid").css("border", "none");
						}
						if (element.id=="full_nameInput") {
							$("#" + partie + "_form #full_nameGrid").css("border", "none");
						}
						$("#phone_intake_feedback_div").html("");
					}, 2500);
				}
			}
		});
	},
	employerOverride: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		var other_id = "override_current";
		if (element_id=="override_current") {
			other_id = "addto_current";
		}
		
		document.getElementById(other_id).checked = !element.checked;
		
		if (document.getElementById("override_current").checked) {
			$("#additional_partie").val("n");
		} else {
			$("#additional_partie").val("y");
		}
	},
	doTimeouts: function(event) {
		var self = this;
		var master_class = this.model.get("master_class");
		
		if (self.model.get("intake_screen")) {
			$( "#partie_all_done").remove();
		}
		
		event.preventDefault();
		var blnImm = (this.model.get("kase_type") == "immigration");
		
		$(master_class + ".partie .form_label_vert").css("color", "white");
		
		//ss is applicant
		if (this.model.get("blnSS") && !blnImm) {
			document.getElementById("party_type_optionInput").options[1].text = "Claimant";
			document.getElementById("party_type_optionInput").value = "plaintiff";
			//$("#party_type_optionInput").hide();
		}
		if (blnImm) {
			var party_type = self.model.get("representing");			
			$(master_class + "#party_type_optionInput").html(immigration_representing_options);
			$(master_class + "#party_type_optionInput").val(party_type);
			if (party_type=="plaintiff") {
				party_type = "Applicant";
			}
			$(master_class + "#party_type_optionSpan").html(party_type);
		}	
		if ($(master_class + "#company_nameSpan").length > 0) {
			if ($(master_class + "#company_nameSpan").html().length > 45) {
				$(master_class + "#company_nameSpan").css("font-size", "0.9em");
			}
		}
		if (this.model.get("gridster_me") || this.model.get("grid_it")) {
			gridsterById('gridster_' + this.model.get("partie"));
		}
		
		if (!blnBingSearch) {
			initializeGoogleAutocomplete(self.model.get("partie"));
		} else {
			$(master_class + "#full_addressInput").on("keyup", function() {
				if (master_class!="") {
					lookupBingMaps (master_class, "bing_results");
				} else {
					lookupBingMaps ("partie", "bing_results");
				}
			});
		}
		
		$(master_class + ".token-input-dropdown-facebook").css("width", "424px");
		$(master_class + ".token-input-dropdown-person").css("width", "424px");
		if (this.model.get("partie")=="Medical_provider") {
			$( "#medical_provider_date_assignedInput" ).datepicker();
			$( "#medical_provider_date_completedInput" ).datepicker();
		
			specialtyComplete("Medical_provider", "medical_provider_specialtyInput");
			$(".Medical_provider ul.autocomplete_specialty").css("margin-left", "-4px");
			$(".Medical_provider ul.autocomplete_specialty").css("margin-top", "-135px");
			$(".Medical_provider ul.autocomplete_specialty").css("z-index", "9999");
			
			//prior treatment
			if (this.model.get("additional_partie") == "p") {
				//p for prior
					//change the title, the additional_partie will tell the pack how to behave upon save
					$("#partie_type_holder").prepend("Prior ");
			}
		}
		
		$(master_class + "#address").hide();
		
		if (this.model.id<0) {
			self.triggerEdit();
			var partie_class = self.model.get("partie");
			$("." + partie_class + " .delete").hide();
			$("." + partie_class + " .reset").hide();
			$("." + partie_class + " #table_id").val("");
			/*
			//must ask medical provider if they want to copy location			
			if (this.model.get("partie")=="Medical_provider") {
				if (this.model.get("additional_partie") != "p") {
					$("#partie_gridster_ul li").hide();
					$("#copyingRequestGrid").attr("data-row", "1");
					$("#copyingRequestGrid").fadeIn();
					$(".copy_secondrow").attr("disabled", true);
					//$("#medical_copyInput").attr("checked", true);
					$("#medical_copyInput").trigger("click");
				}
			}
			*/
		}
		$(master_class + "#token_add_link").hide();
		//normal is not carrier, not attorneys
		var blnNormalPartie = true;
		var blnCarrier = false;
		
		if (this.model.get("partie")=="Carrier") {	
			blnCarrier = true;
			$(master_class + "#coverage_date_startInput").datetimepicker({ 
				validateOnBlur:false, 
				defaultTime:'8:00',  step:30, 
				timepicker:false, 
				format:"m/d/Y",
				onChangeDateTime:function(dp,$input){
					setCoverageEnd();
				}
			});
			$(master_class + '#coverage_date_endInput').datetimepicker({ 
				validateOnBlur:false, 
				defaultTime:'8:00',  step:30, 
				timepicker:false, 
				format:"m/d/Y",
				onChangeDateTime:function(dp,$input){
					checkCoverage();
				}
			});
			
			this.model.set("distributed", false);
			
			if (!self.model.get("intake_screen")) {			
				if (current_case_id > -1) {
					var kase = kases.findWhere({case_id : current_case_id});
					var settlement = new SettlementSheet({injury_id: kase.get("id")});
					settlement.fetch({
						success: function(data) {
							var data_info = data.toJSON().data;
							if (data_info!="") {
								var datum = JSON.parse(data_info);
								if (datum.distrib!="" && datum.distrib!="0000-00-00") {
									self.model.set("distributed", true);
									$("#distributed_status_holder").fadeIn();
								}
							}
						}
					});
				}
			}
		}
		if (this.model.get("partie")=="Carrier" && this.model.get("blnWCAB")) {	
			blnNormalPartie = false;
			
			carrierTokenInput();
			
			if (self.model.get("id") < 0) {
				$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
			} 
			if (self.model.get("company_name")!="") {
				setTimeout(function() {
					$(master_class + "#company_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("company_name")});
					if (self.model.get("full_name")!="") {
						$(master_class + ".partie #full_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("full_name")});
					} else {
						$(master_class + ".token-input-list-person").toggleClass("hidden");
					}
				}, 600);
			}
			//add a dropdown for dois
			this.addInjuryDropDown()
		}
		
		if (this.model.get("partie")=="Defense" || this.model.get("partie")=="Claim") {	
			blnNormalPartie = false;
			var source = "";
			if(this.model.get("partie")=="Claim") {	
				source = "eams_claimanttoken";
			}
			if(this.model.get("partie")=="Defense") {	
				source = "eams_defense_token";
			}
			repTokenInput(source);
			
			if (self.model.get("id") < 0) {
				$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
			} 
			if (self.model.get("company_name")!="") {
				setTimeout(function() {
					$(master_class + "#company_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("company_name")});				
					if (self.model.get("full_name")!="") {
						$(master_class + ".partie #full_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("full_name")});
					} else {
						$(master_class + ".token-input-list-person").toggleClass("hidden");
					}
				}, 550);
			}
			
			//add a dropdown for dois
			this.addInjuryDropDown()
		}
		if (this.model.get("partie")=="Applicant_attorney") {	
			blnNormalPartie = false;
			//eamsComplete("Carrier", "company_nameInput"); 
			repTokenInput();
			
			if (self.model.get("id") < 0) {
				$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
			} 
			if (self.model.get("company_name")!="") {
				setTimeout(function() {
					$(master_class + "#company_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("company_name")});				
					if (self.model.get("full_name")!="") {
						$(master_class + ".partie #full_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("full_name")});
					} else {
						$(master_class + ".token-input-list-person").toggleClass("hidden");
					}
				}, 550);
			}
		}
		
		if (this.model.get("partie")=="Prior_attorney") {	
			blnNormalPartie = false;
			$(master_class + "#company_nameInput").tokenInput("api/eams_token", "eams"); 
			repTokenInput();
			$(master_class + ".token-input-list").css("display", "none");
			
			if (self.model.get("id") < 0) {
				$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
			} 
			if (self.model.get("company_name")!="") {
				setTimeout(function() {
					$(master_class + "#company_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("company_name")});
					if (self.model.get("full_name")!="") {
						$(master_class + ".partie #full_nameInput").tokenInput("add", {id: self.model.get("id"), name: self.model.get("full_name")});
						
					} else {
						$(master_class + ".token-input-list-person").toggleClass("hidden");
					}
				}, 500)
			}			
		}
		this.model.set("normal_partie", blnNormalPartie);
		if (blnNormalPartie) {	
			partieTokenInput(this.model);	
			if (this.model.id > 0) {
				setTimeout(function() {
					if ($(master_class + "#full_nameInput").hasClass("hidden") != $(master_class + ".token-input-list-person").hasClass("hidden")) {
						$(master_class + ".token-input-list-person").toggleClass("hidden");
					}
				}, 1000);
			}								
		}
		
		if (this.model.id > 0 && current_case_id!=-1) {
			if (blnMedicalBilling) {
				//do we have any medical billings
				var medical_billings = new MedicalBillingCollection({corporation_id: this.model.get("id"), case_id: current_case_id});
				medical_billings.fetch({
					success: function(data) {
						var my_model = new Backbone.Model;
						my_model.set("holder", "medical_billings");
						my_model.set("case_id", current_case_id);
						my_model.set("partie_id", self.model.get("id"));
						my_model.set("embedded", false);
						$('#medical_billings').html(new medical_billing_listing_view({collection: data, model: my_model}).render().el);	
						$('#medical_billings').fadeIn(function() {
							$('#medical_billings').css("width", "50%");
						});
						//now show 
						//gridsterById("gridster_" + self.model.get("partie"));					
					}
				});
			}

			if (blnOtherBilling) {
				//do we have any other billings
				var other_billings = new OtherBillingCollection({corporation_id: this.model.get("id"), case_id: current_case_id});
				other_billings.fetch({
					success: function(data) {
						var my_model = new Backbone.Model;
						my_model.set("holder", "other_billings");
						my_model.set("case_id", current_case_id);
						my_model.set("partie_id", self.model.get("id"));
						my_model.set("embedded", false);
						$('#other_billings').html(new other_billing_listing_view({collection: data, model: my_model}).render().el);	
						$('#other_billings').fadeIn(function() {
							$('#other_billings').css("width", "50%");
						});
						//now show 
						//gridsterById("gridster_" + self.model.get("partie"));					
					}
				});
			}
			
			if (blnCarrier) {
				var arrOptions = [];
				
				var financial = new Financial({"case_id": current_case_id, "corporation_id": this.model.get("id")});
				financial.set("holder", "#carrier_financial");
				financial.set("glass", "card_dark_7");
				financial.set("trust_options", arrOptions);
				financial.fetch({
				success: function(financial) {
						$("#carrier_financial").html(new carrier_financial_view({model: financial}).render().el);
						//now show 
						$('#carrier_financial').fadeIn(function() {
							$('#carrier_financial').css("width", "50%");
						});
					}
				});
				
				//negotiation
				var negotiations = new NegotiationCollection({case_id: current_case_id, "corporation_id": this.model.get("id") });
				negotiations.fetch({
						success: function(data) {
							var nkase = new Backbone.Model();
							nkase.set("holder", "#carrier_neg");
							nkase.set("page_title", "Negotiation");
							nkase.set("embedded", true);
							$('#carrier_neg').html(new negotiation_listing_view({collection: data, model: nkase}).render().el);
							//now show
							$('#carrier_neg').fadeIn(function() {
								$('#carrier_neg').css("width", "50%");
							});
						}
				});
			}
			
			if (blnEmployer) {
				var parties = new Parties([], { case_id: current_case_id});
				parties.fetch({
					success: function(parties) {
						var employer_parties = parties.where({"type": "employer"});
						var corporation_id = "";
						if (typeof employer_parties != "undefined" && employer_parties != "") {
							if(employer_parties.length > 1) {
								corporation_id = self.model.id;
							}
						}
						var lostincomes = new LostIncomeCollection({"case_id": current_case_id, "corporation_id": corporation_id})
						lostincomes.fetch({
						success: function(lostincomes) {
								var mymodel = new Backbone.Model();
								mymodel.set("holder", "#employer_lostincome");
								mymodel.set("glass", "card_dark_7");
								$("#employer_lostincome").html(new lostincome_listing_view({collection: lostincomes, model: mymodel}).render().el);
								//now show 
								$('#employer_lostincome').fadeIn(function() {
									$('#employer_lostincome').css("width", "50%");
								});
							}
						});
					}
				});
			}
			
			if (this.model.get("blurb")=="referring") {
				var url = 'api/referral_fee/' + this.model.get("id");
	
				$.ajax({
					url:url,
					type:'GET',
					dataType:"json",
					success:function (data) {
						if(data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else {
							if(data!=false) {
								var jdata = JSON.parse(data.referral_info);
								$("#referral_source_feeSpan").html(formatDollar(jdata.fee));
								$("#referral_source_dateSpan").html(moment(jdata.date).format("MM/DD/YYYY"));
								var notes_width = Number($("#partie_notes").css("width").replace("px", "")) - 30;
								$("#referral_fee").css("width", notes_width + "px");
								$("#referral_fee").fadeIn();
							}
						}
					}
				});
			}
			//do we have any notes
			var partie_notes = new NotesByType([], {type: this.model.get("blurb"), case_id: current_case_id});
			partie_notes.fetch({
				success: function(data) {
					var note_list_model = new Backbone.Model;
					note_list_model.set("display", "sub");
					note_list_model.set("partie_id", self.model.get("id"));
					note_list_model.set("partie_type", self.model.get("blurb"));
					note_list_model.set("case_id", current_case_id);
					$('#partie_notes').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
					$('#partie_notes').fadeIn(function() {
						$('#partie_notes').css("width", "50%");
					});
					//now show 
					gridsterById("gridster_" + self.model.get("partie"));					
				}
			});
		}
		
		if (!this.model.get("blnWCAB")) {
			if (this.model.get("type") == "plaintiff" || this.model.get("type") == "defendant") {
			//console.log(self.model.toJSON());
				setTimeout(function() {
					$('#kai_holder').html(new partie_kai_view({model: self.model}).render().el);
				}, 1777);
			}
			if (this.model.get("partie")=="Venue") {
				//not venue, court, not company, court
				$("#partie_type_holder").html("Court");
				$("#company_input_label").html("Court");
			}
			if (this.model.get("partie")=="Plaintiff" && blnImm) {
				//not venue, court, not company, court
				$("#partie_type_holder").html("Applicant");
			}
		}
		
		//wcab defense
		var blnWCABDefense = false;
		if (this.model.get("kase_type")=="WCAB_Defense") {
			blnWCABDefense = true;
			var arrDefendantOptions = [
				"<option id='' value=''>Choose One</option>",
				"<option id='' value='insurance'>Insurance</option>",
				"<option id='' value='employer'>Employer</option>",
				"<option id='' value='defendant'>Defendant</option>",
			];
			
			var select_defendant_input = "<select name='party_defendant_optionInput' id='party_defendant_optionInput' style='margin-top:0px; margin-left:0px; width:385px' class='kase partie Defendant input_class hidden'>" + arrDefendantOptions.join(" ") + "</select>";
			$("#party_defendant_optionDiv").html(select_defendant_input);
			$("#party_defendant_label").html("Represents");
		}
		//default values for defendant and plaintiff
		if (!this.model.get("blnWCAB")) {
			var partie_type = this.model.get("type");
			if (partie_type=="defendant" || partie_type=="plaintiff") {	
				setTimeout(function() {	
					$("#party_type_optionInput").val(partie_type);
				}, 1077);
			}
			if (partie_type=="venue") {
				var jurisdiction = this.model.get("jurisdiction");
				var county = this.model.get("county");
				var department = this.model.get("department");
				var department_phone = this.model.get("department_phone");
				var district = this.model.get("district");
				var branch = this.model.get("branch");
				setTimeout(function() {
					$("#jurisdictionExtraInput").val(jurisdiction);
					$("#jurisdictionExtraSpan").html(jurisdiction);
					
					$("#countyExtraInput").val(county);
					$("#countyExtraSpan").html(county);
					
					$("#departmentExtraInput").val(department);
					$("#departmentExtraSpan").html(department);
					
					$("#department_phoneExtraInput").val(department_phone);
					$("#department_phoneExtraSpan").html(department_phone);
					
					$("#districtExtraInput").val(district);
					$("#districtExtraSpan").html(district);
					
					$("#branchExtraInput").val(branch);
					$("#branchExtraSpan").html(branch);
				}, 1007);
			}
		}
		
		var party_defendant_option = this.model.get("party_defendant_option");
		if (party_defendant_option == "" && !blnWCABDefense) {
			$("#party_defendant_optionGrid").hide();
		}
		var selected = "";
		if (current_case_id!="-1") {
			setTimeout(function() {
				
			var parties = new Parties([], { case_id: current_case_id});
				parties.fetch({
					success: function(parties) {
						if (self.model.id < 0) {
							//assume single employers
							var employer_parties = parties.where({"type": "employer"});
							if (typeof employer_parties != "undefined" && employer_parties != "") {
								if(employer_parties.length > 0) {
									var employers = "Employer";
									if(employer_parties.length > 1) {
										employers += "s are";
									} else {
										employers += " is";
									}
									$("#employer_count").html("(" + employer_parties.length + ") " + employers);
									$("#multipleEmployersGrid").show();
									
									self.repositionGrids();
									
									$("#override_current").focus();
									$("token_add_link").css("margin-top", "175px");
								}
							}
						}
						//assume multiple defendants
						var defendant_parties = parties.where({"type": "defendant"});
						if (typeof defendant_parties != "undefined" && defendant_parties != "") {
							defendant_parties = defendant_parties[0].collection.toJSON();
							var arrDefendantOptions = ["<option id='' value=''>Choose One</option>"];
							_.each(defendant_parties , function(defendant_partie) {
								//build your options
								//upon retrieval, match up ids to select
								if (defendant_partie.type == "defendant") {
								var selected = "";
								//if...
								if (party_defendant_option == defendant_partie.corporation_id) {
									selected = "selected='selected'"
								}
								var opt = "<option id='" + defendant_partie.corporation_id + "_" + defendant_partie.company_name + "' value='" + defendant_partie.corporation_id + "' " + selected + ">" + defendant_partie.company_name + "</option>";
								arrDefendantOptions.push(opt);
								}
							});
							var hidden_class = "hidden";
							
							//alert(arrDefendantOptions)
							//put the entire select in the proper div holder
							var select_defendant_input = "<select name='party_defendant_optionInput' id='party_defendant_optionInput' style='margin-top:0px; margin-left:0px; width:385px' class='kase partie Defendant input_class " + hidden_class + "'>" + arrDefendantOptions.join(" ") + "</select>";
							$("#party_defendant_optionDiv").html(select_defendant_input);
						}
					}
				});
			}, 1000);
		}
		/*
		$("#billing_time_dropdownInput").editableSelect({
			onSelect: function (element) {
				var billing_time = $("#billing_time_dropdownInput").val();
				$("#billing_time").val(billing_time);
				//alert(billing_time);
			}
		});
		*/
		
		//additional_addresses
		var additional_addresses = this.model.get("additional_addresses");
		var partie = this.model.get("partie");
		if (additional_addresses!="") {
			var jadd = JSON.parse(additional_addresses);
			if (typeof jadd.address_2 != "undefined") {
				$("#additional_full_addressInput").val(jadd.address_2[0]);
				var arrAddress = [];
				arrAddress.push(jadd.address_2[2]);
				if (jadd.address_2[1]!="") {
					arrAddress.push(jadd.address_2[1]);
				}
				var city_state_zip = jadd.address_2[3] + ", " + jadd.address_2[4] + " " + jadd.address_2[5];
				arrAddress.push(city_state_zip);
				
				$("#additional_addressSpan").html(arrAddress.join("<br>"));
				$("#additional_street_" + partie).val(jadd.address_2[2]);
				$("#additional_suiteInput").val(jadd.address_2[1]);
				$("#additional_city_" + partie).val(jadd.address_2[3]);
				$("#additional_administrative_area_level_1_" + partie).val(jadd.address_2[4]);
				$("#additional_postal_code_" + partie).val(jadd.address_2[5]);
				
				$("#additional_addressGrid").show();
			} 
		}
		this.repositionGrids();
		
		$(master_class + ".form_label_vert").css("color", "white");
		$(master_class + ".form_label_vert").css("font-size", "1em");
				
		self.model.set("hide_upload", true);
		showKaseAbstract(self.model);
		
		if (self.model.get("intake_screen")) {
			$("#" + partie + "_form #partie_type_holder").css("font-weight", "normal");
			document.getElementById(partie + "_form").parentElement.style.background = "none";
			$(".gridster_border").css("background", "none");
			$(".gridster_border").css("border", "none");
			$(".gridster_border").css("-webkit-box-shadow", "none");
			$(".gridster_border").css("box-shadow", "none");
			$("#partie_gridster_ul #phoneGrid .form_label_vert").html("Comp. Ph");
			
			$("." + partie + " .gridster_border").css("visibility", "hidden");
			$("#" + partie + "_form #company_nameGrid").css("visibility", "visible");
		}
		
		//rolodex?
		var hash = document.location.hash;
		if (hash.indexOf("#rolodex/")==0) {
			var arrHash = hash.split("/");
			var corp_id = arrHash[1];
			self.triggerEdit();
			if ($("#list_kases_link").css("display")!="inline") {
				var partie_type = this.model.get("partie_type").replace(" ", "_").toLowerCase();
				$("#kases_link_holder").html("<a href='v8.php#kaseslist/" + corp_id + "/" + partie_type + "' target='_blank' style='background:#427bc3; padding:5px; color:white'>Kases</a>");
				//$("#kases_link_holder").html("<button role=\"button\" onclick=\"listPartieKases(event, " + corp_id + ",'" + partie_type + "')\" class=\"btn btn-xs btn-primary\">Kases</button>");
				$("#kases_link_holder").fadeIn();
			}
		}
	},
	showIntakeFields: function() {
		var partie_type = this.model.get("partie_type").replace(" ", "_").toLowerCase().capitalize();
		
		if (!this.model.get("intake_screen")) {
			return;
		}
		if (partie_type=="Insurance_carrier") {
			partie_type = "Carrier";
		}
		$("#" + partie_type + "_form .gridster_border").css("visibility", "visible");
		$("#" + partie_type + "_form #saveButtonGrid").hide();
		
		$("#content").css("height", $(document).height() + "px");
	},
	repositionGrids: function() {
		var partie_type = this.model.get("partie_type").replace(" ", "_").toLowerCase().capitalize();
		
		if (partie_type=="Insurance_carrier") {
			partie_type = "Carrier";
		}
		if (partie_type=="Referral_source") {
			partie_type = "Referring";
		}
		
		var grids = $("#" + partie_type + "_form #partie_gridster_ul .gridster_border");
		var arrLength = grids.length;
		var row_counter = 0;
		var special_row = 0;
		
		for(var i = 0; i < arrLength; i++) {
			var grid = grids[i];
			var grid_id = grid.id;
			var blnAddRow = true;
			var the_display = $("#" + partie_type + "_form #" + grid_id).css("display");
			
			if (the_display=="none") {
				continue;
			}
			var data_col = $("#" + partie_type + "_form #" + grid_id).attr("data-col");
			
			if (this.model.get("partie")!="Defendant") {
				if (this.model.get("partie")=="Medical_provider") {
					if (grid_id == "commentsGrid") {
						row_counter = 15;
					}
					if (grid_id == "copyingInstructionsGrid") {
						row_counter = 13;
					}
					if (grid_id == "specialInstructionsGrid") {
						row_counter = 18;
					}
				}
				if (grid_id == "specialInstructionsGrid") {
					special_row = row_counter;
				}
				if (grid_id == "saveButtonGrid") {
					row_counter = special_row + 3;
				}
				if ($("#" + partie_type + "_form #" + grid_id).hasClass("adhoc_grid")) {
					if (data_col==1) {
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
						i++;
						var right_grid = grids[i];
						var grid_id = right_grid.id;
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
					}
				} else {
					if (grid_id == "ssnGrid") {
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
						i++;
						var right_grid = grids[i];
						var grid_id = right_grid.id;
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
					} else {
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
					}
				}
				
				if (this.model.id < 0 && this.model.get("partie")=="Employer") {
					if ($("#employer_count").html()!="") {
						if (grid_id == "multipleEmployersGrid") {
							row_counter += 2;
						}
					}
				}
			} else {
				//defendant kludge
				if ($("#" + partie_type + "_form #" + grid_id).hasClass("adhoc_grid")) {
					if (data_col==1) {
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
						i++;
						var right_grid = grids[i];
						var grid_id = right_grid.id;
						$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
					}
				} 
				if (grid_id == "ssnGrid" || grid_id=="employee_cellGrid") {
					row_counter--;
				}
				$("#" + partie_type + "_form #" + grid_id).attr("data-row", (row_counter + 1));
				if (grid_id == "commentsGrid") {
					row_counter += 2;
				}
				
				/*
				if (grid_id != "phoneGrid") {
					$("#" + grid_id).attr("data-row", (row_counter + 1));
				} else {
					$("#" + grid_id).attr("data-row", (row_counter - 2));
				}
				*/
			}
			var blnStandardRow = true;
			if (this.model.get("partie")!="Defendant") {
				if (grid_id=="full_addressGrid" || grid_id=="additional_addressGrid" || grid_id == "commentsGrid") {
					increment = 3;
					
					row_counter = row_counter + increment;
					blnStandardRow = false;
				} 
			} else {
				if (grid_id=="full_addressGrid") {
					increment = 3;
					
					row_counter = row_counter + increment;
					blnStandardRow = false;
				} 
			}
			
			if (blnStandardRow) {
				row_counter++;
			}
			
		}
		if (this.model.get("partie")=="Defendant") {
			//defendant kludge
			$("#" + partie_type + "_form #phoneGrid").attr("data-row", $("#employee_faxGrid").attr("data-row"));
			$("#" + partie_type + "_form #employee_cellGrid").attr("data-row", $("#employee_faxGrid").attr("data-row") - 1);
		}
		
		if (this.model.get("intake_screen")) {
			$(".button_row." + partie_type).hide();
			$("#" + partie_type + "_form").disableAutoFill();
		} else {
			if ( blnNoAutoFill) {
				$("#" + partie_type + "_form").disableAutoFill();
			}
		}
		
	},
	addNewLocation: function() {
		var company_name = $("#company_nameInput").val();
		$(".partie #corporation_id").val(company_name);
		//erase the address, start from scratch
		$(".partie #full_addressInput").val("");
		var partie_name = $(".partie #partie").val();
		var blurb = $(".partie #type").val();
		$("#street_" + partie_name).val("");
		$(".partie #suiteInput").val("");
		$("#city_" + partie_name).val("");
		$("#administrative_area_level_1_" + partie_name).val("");
		$("#postal_code_" + partie_name).val("");
		$(".partie #phoneInput").val("");
		$(".partie #faxInput").val("");
		$("#token_add_link").fadeOut();
		$(".partie #phoneInput").focus();
	},
	addInjuryDropDown:function() {
		var self = this;
		
		if (current_case_id=="" || current_case_id == -1) {
			return;
		}
		//fetch the full company info
		var corporation = new Corporation({id: this.model.id, case_id: current_case_id});
		corporation.fetch({
			success: function (corporation) {
				//dois
				var arrDOIs = [];
				var kase_dois = dois.where({case_id: current_case_id});
				var corporation_injury_id = corporation.toJSON().injury_id;
				var selected = "";
				if (corporation_injury_id < 0) {
					selected = " selected";
				}
				thedoi = "<option value=''" + selected + ">Select from List</option>";
				arrDOIs.push(thedoi);
				
				//first one 
				selected = "";
				if (kase_dois.length==1) {
					//if only one
					selected = " selected";
				}
				_.each(kase_dois , function(doi) {
					var thedoi = moment(doi.get("start_date")).format("MM/DD/YYYY");
					if (thedoi!="Invalid date") {
						if (doi.get("end_date") != "0000-00-00") {
							thedoi += " - " + moment(doi.get("end_date")).format("MM/DD/YYYY") + " CT";
						}
						thedoi = doi.get("adj_number") + " // " + thedoi;
						
						//if there is an injury id
						if (corporation_injury_id > 0) {
							selected = "";
							if (doi.id==corporation_injury_id) {
								selected = " selected";
								$("#injury_idSpan").html(thedoi);
							}
						}
						thedoi = "<option value='" + doi.id + "'" + selected + ">" + thedoi + "</option>";
						arrDOIs.push(thedoi);
					}
				});
				$("#injury_idInput").html(arrDOIs.join("\r\n"));
			}
		});
	},
	setFullNameCurrent: function(event) {
		this.model.set("current_input", "full_name");
	},
	clearCurrent: function(event) {
		this.model.set("current_input", "");
	},
	setCompanyNameCurrent: function(event) {
		this.model.set("current_input", "company_name");
		
		this.showIntakeFields();
		
		this.checkEmployerNameLength();
	},
	clearPartieID: function(event) {
		//if someone is typing in the search box, no id no mo
		$(".partie #table_id").val("");
		$(".partie #corporation_id").val("");
	},
	checkForToken: function(event) {
		if (event.keyCode==16) {
			event.preventDefault();
			return;
		}
		var master_class = this.model.get("master_class");
		this.model.set("current_input", "company_name");
		var blnClearByTyping = false;
		var selection_start = document.getElementById("company_nameInput").selectionStart;
		var selection_end = document.getElementById("company_nameInput").selectionEnd;
		var selection_length = document.getElementById("company_nameInput").value.length;
		if ((selection_end - selection_start)==selection_length) {
			blnClearByTyping = true;
			this.model.set("company_name", "");
			this.model.set("id", -1);
			$("#token_add_link").hide();
		}
		//parents can ONLY be edited via rolodex
		if ($(master_class + ".partie #company_nameInput").val()=="" || blnClearByTyping) {
			//by backspacing all the way, the user effectively says that they want a brand new record
			var company_name = document.getElementById("company_nameInput").value;
			$(master_class + ".partie .input_class").val("");
			if (!blnClearByTyping) {
				//not meant to clear the company name box
				$(master_class + "#company_nameInput").val(company_name);
			}
			$(master_class + ".partie .span_class").html("");
			//clear the id
			$(master_class + ".partie #table_id").val("");
			$(master_class + ".partie #corporation_id").val("");
			
			if (this.model.get("normal_partie")) {	
				partieTokenInput(this.model);
			}
			if (this.model.get("partie")=="Defense") {	
				//repsComplete("Defense", "company_nameInput"); 
				$(master_class + "#company_nameInput").tokenInput("clear");
				
				source = "eams_defense_token";
				repTokenInput(source);
				blnClearByTyping = false;
				/*
				$(".partie #company_nameInput").hide();
				$(".partie .token-input-list-eams").toggleClass("hidden");
				*/
			}
			if (this.model.get("partie")=="Claim") {
				repsComplete("Claims Administrator", "company_nameInput"); 
			}
			if (this.model.get("partie")=="Carrier") {	
				//eamsComplete("Carrier", "company_nameInput");
				carrierTokenInput();
			}
		}
		
		this.checkEmployerNameLength();
	},
	checkEmployerNameLength: function() {
		if (!this.model.get("isEmployer")) {
			return;
		}
		
		checkEmployerLength();
	},
	checkForPersonToken: function(event) {
		if (event.keyCode==16) {
			event.preventDefault();
			return;
		}
		var master_class = this.model.get("master_class");
		this.model.set("current_input", "full_name");
		var blnClearByTyping = false;
		var selection_start = document.getElementById("full_nameInput").selectionStart;
		var selection_end = document.getElementById("full_nameInput").selectionEnd;
		var selection_length = document.getElementById("full_nameInput").value.length;
		if ((selection_end - selection_start)==selection_length) {
			blnClearByTyping = true;
		}
		
		if ($(master_class + ".partie #full_nameInput").val()=="" || blnClearByTyping) {
			//by backspacing all the way, the user effectively says that they want a brand new record
			var person_name = document.getElementById("full_nameInput").value;
			$(master_class + ".partie .employee_class").val("");
			if (!blnClearByTyping) {
				//not meant to clear the company name box
				$(master_class + "#full_nameInput").val(person_name);
			}
			$(master_class + ".partie .employee_span_class").html("");

			employeeTokenInput($(master_class + "#corporation_id").val(), this.model.get("blurb"));
		}
	},
	manualAddress: function(event) {
		var partie = this.model.get("partie");
		var master_class = this.model.get("master_class");
		
		$(".pac-container").hide();
		
		var street = $(master_class + "#street_" + partie);
		street.val($(master_class + "#full_addressInput").val());
		$(master_class + "#full_addressInput").hide();
		street.focus();
		//hide city and state, will be filled out by zip
		$(master_class + "#city_" + partie).css("visibility", "hidden");
		$(master_class + "#administrative_area_level_1_" + partie).css("visibility", "hidden");
		
		$(master_class + "#manual_address").fadeOut(function() {
			$(master_class + "#lookup_address").fadeIn();
		});
	},
	lookupAddress: function(event) {
		var partie = this.model.get("partie");
		var master_class = this.model.get("master_class");
		
		$(master_class + "#street_" + partie).val("");
		
		$(master_class + "#full_addressSpan").hide();
		var full_address = $(master_class + "#full_addressInput");
		full_address.show();
		full_address.focus();
		
		//show city and state
		$(master_class + "#city_" + partie).css("visibility", "visible");
		$(master_class + "#city_" + partie).val("");
		$(master_class + "#administrative_area_level_1_" + partie).css("visibility", "visible");
		$(master_class + "#administrative_area_level_1_" + partie).val("");
		
		$(master_class + "#lookup_address").fadeOut(function() {
			$(master_class + "#manual_address").fadeIn();
		});
	},
	checkAddress: function(event) {
		clearTimeout(manual_id);
		var self = this;
		var element = event.currentTarget;
		event.preventDefault();
		
		if (blnBingSearch) {
		//	return;
		}
		var val = element.value;
		var partie = this.model.get("partie");
		var master_class = this.model.get("master_class");
		
		val = val.replaceAll(" ", "").toLowerCase().trim();
		val = val.replaceTout(".", "");
		if (!blnBingSearch) {
			var founds = -1;
			if (typeof window["autocomplete" + partie].gm_accessors_.place.Mc != "undefined") {
				founds = window["autocomplete" + partie].gm_accessors_.place.Mc.l.length;
			}
			if (val!="" && founds==0) {
				if (val.length > 5) {
					manual_id = setTimeout(function() {
						self.manualAddress();
					}, 1500);
				}
			}
		}
		//console.log("founds:" + founds);
		
		if (val.indexOf("pobox")==0) {
			//console.log("cancel lookup");
			//window["autocomplete" + this.model.get("partie")] = null;
			$(master_class + ".pac-container").hide();
			element.style.display = "none";
			
			var street = $(master_class + "#street_" + partie);
			street.val(element.value);
			street.focus();
			//hide city and state, will be filled out by zip
			$(master_class + "#city_" + partie).css("visibility", "hidden");;
			$(master_class + "#administrative_area_level_1_" + partie).css("visibility", "hidden");
			$(master_class + "#manual_address").html("");
		}
	},
	newNotes: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewNote(element.id);
	},
	coverageDates: function() {
		var self = this;
		setTimeout(function() {
			var date_coverage = $('#coverage_date_startInput').val();
			self.partieCoverage(date_coverage);
		}, 500);
	},
	partieCoverage: function() {
		setCoverageEnd()
	},
	triggerEdit:function() {
		var partie_class = this.model.get("partie");
		console.log(partie_class);
		
		$("." + partie_class + " .edit" ).trigger( "click" );
		setTimeout(function() {
			if ($("." + partie_class + " #company_nameInput").css("display")!="none") {
				$("." + partie_class + " #company_nameInput").focus();
			}
		}, 11);
		
	},
	addToDash: function(event) {
		event.preventDefault();
		var url = 'api/setting/customer/add';
		var formValues = "table_name=setting&categoryInput=dashboard&setting_valueInput=Y&settingInput=" +  this.model.get("partie").toLowerCase();
		$.ajax({
			url:url,
			type:'POST',
			data: formValues,
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#dashboard_selector_holder").html("<span style='background:green;color:white;padding:1px'>added&nbsp;&#10003;</span>");
				}
			}
		});
	},
	newPartie: function(event) {
		event.preventDefault();
		document.location.href = "#parties/" + this.model.get("case_id") + "/-1/new";
	},
	confirmApply: function(event) {
		//var element = event.currentTarget;
		event.preventDefault();

		var billing_time = $("#billing_time_dropdownInput").val();
		$("#billing_time").val(billing_time);
		if (this.model.id == -1) {			
			//brand new save
			var blnNewSave = ($("#specialInstructionsGrid").css("display")=="none");
			
			//must ask medical provider if they want to copy location			
			if (this.model.get("partie")=="Medical_provider" && blnNewSave) {
				if (this.model.get("additional_partie") != "p") {
					$("#partie_gridster_ul li").hide();
					$("#token_add_link").hide();
					$("#copyingRequestGrid").attr("data-row", "1");
					$("#copyingRequestGrid").fadeIn();
					$(".copy_secondrow").attr("disabled", true);
					//$("#medical_copyInput").attr("checked", true);
					$("#medical_copyInput").trigger("click");
				}
				return;
			}
			
			this.savePartie(event);
			return;
		}
		
		if (this.model.get("corporation_uuid") == this.model.get("parent_corporation_uuid")) {
			$("#confirm_apply").css({display: "none", top: 20, left: 450, position:'absolute'});
			$("#confirm_apply").fadeIn();
			//if they press yes, they will go to savePartie
		} else {
			this.savePartie(event);
		}
		//if they press yes, they will go to savePartie
	},
	cancelApply: function(event) {
		event.preventDefault();
		$("#confirm_apply").fadeOut();
		$("#confirm_apply_decide").val("N");
		
		this.savePartie(event);
	},
	applyPartie: function(event) {
		event.preventDefault();
		$("#confirm_apply").fadeOut();
		$("#confirm_apply_decide").val("Y");

		this.savePartie(event);
	},
	savePartie:function (event) {
		var self = this;
		
		if (this.model.get("intake_screen")) {
			if (!blnSaveIntakePartie) {
				return;
			}
			blnSaveIntakePartie = false;
		}
		
		var partie_class = this.model.get("partie");
		//rename if autofill on
		if (this.model.get("intake_screen") || blnNoAutoFill) {
			var inputs = $("#" + partie_class + "_form input");
			var arrLength = inputs.length;
			for (var i = 0; i < arrLength; i++) {
				var inp = inputs[i];
				if (typeof $("#" + partie_class + "_form #" + inp.id).attr("name") != "undefined") {
					if (inp.name != inp.id) {
						inp.name = inp.id;
					}
				}
			}
		}
		
		if ($("." + partie_class + " #table_id").val()=="") {
			$("." + partie_class + " #table_id").val("-1")
		}
		$("#edit_row").hide();
		$("#gifsave").css("margin-top", "-5px");
		$("#gifsave").show();
		
		event.preventDefault(); // Don't let this button submit the form
		var api_url = "corporation";
		var billing_time = $("#billing_time_dropdownInput").val();
		$("#billing_time").val(billing_time);
		//if this is the employer, and the parent is employer_holder, 
		//then we are in the dashboard, and so we need to check if we need to save the other two panels
		if (partie_class=="Employer") {
			if ($(".Employer")[0].parentElement.parentElement.id=="employer_holder") {
				var blnValid = $("#Carrier_form").parsley('validate');
				if (blnValid) {
					$(".Carrier .save").trigger("click");
				}
				var blnValid = $("#Defense_form").parsley('validate');
				if (blnValid) {
					$(".Defense .save").trigger("click");
				}
			}
		}
		if ($("." + partie_class + "#table_id").val()=="") {
			$("." + partie_class + "#table_id").val("-1")
		}
		addForm(event, partie_class, api_url);
		var party_holder_type = "Prior " + $("#partie_type").val();
		var additional_partie = $("#additional_partie").val();
		var partie_type = $("#type").val();
		var case_id = $("#case_id").val();
		if (party_holder_type=="Prior Medical Provider" && additional_partie=="p") {
			//var kase = kases.findWhere({case_id : case_id});
			//var person_id = kase.get("applicant_id");
			//refreshPriorMedical(case_id, kase, person_id);
			setTimeout(function() {
				$("#list_prior_medical").trigger("click");
			}, 1000);
			document.location.href = "#applicant/" + case_id;
		}
		
		return;
	},
	togglePartieEdit: function(event) {
		var master_class = this.model.get("master_class");
		if (this.model.get("current_input")!="edit_button" && this.model.get("current_input")!="reset_button") {
			var partie_class = this.model.get("partie");
			if ($("." + partie_class + " #corporation_id").val()=="") {
				if (this.model.get("current_input")!="company_name") {
					event.preventDefault();
				} else {
					if ($(master_class + "#token-input-company_nameInput").val()!="") {
						$(master_class + ".partie #company_nameInput").val($("#token-input-company_nameInput").val());
					}
					$(master_class + ".partie #company_nameInput").show();
					$(master_class + ".token-input-list-facebook").hide();
					$(master_class + ".token-input-dropdown-facebook").hide();
					event.preventDefault();
				}
				return;
			}
			if (this.model.get("current_input") == "full_name") {
				//get the val
				var arrLength = $(master_class + ".partie #token-input-full_nameInput").length;
				if (arrLength==1) {
					employee_name = $(master_class + ".partie #token-input-full_nameInput")[0].value;
				}
				if (arrLength > 1) {
					employee_name = $(master_class + ".partie #token-input-full_nameInput")[arrLength - 1].value;
				}
				if (employee_name!="") {
					$(master_class + ".partie #full_nameInput").val(employee_name);
					$(master_class + ".partie #full_nameInput").show();
					$m(aster_class + ".token-input-list-person").hide();
					$(master_class + ".token-input-dropdown-person").hide();
				}
				event.preventDefault();
				return;
			}			
		}
		
		//$("." + partie_class + " .edit" ).click(function() {
			
		//});
		//edit or rest
		if (event.currentTarget.className.split(" ")[0]!="reset") {
			var partie_class = event.currentTarget.parentElement.className.split(" ")[2];
		} else {
			var partie_class = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className.split(" ")[1];
		}
		$("." + partie_class + " #new_partie").hide();
		
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		$(master_class + "#address").show();
		//get all the editing fields, and toggle them back
		$(".partie.editing." + partie_class).toggleClass("hidden");
		$(".partie.span_class." + partie_class).removeClass("editing");
		$(".partie.input_class." + partie_class).removeClass("editing");
		
		$(".partie.span_class." + partie_class).toggleClass("hidden");
		$(".partie.input_class." + partie_class).toggleClass("hidden");
		$(".partie.input_holder." + partie_class).toggleClass("hidden");
		$(".button_row." + partie_class).toggleClass("hidden");
		
		$(".edit." + partie_class).toggleClass("hidden");
		
		$(master_class + ".partie .token-input-list-person").toggleClass("hidden");
		$(master_class + ".partie .token-input-list-facebook").toggleClass("hidden");
		$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
		
		var current_street = $(".partie #street_" + partie_class).val(); 
		var current_suite = $(master_class + ".partie #suiteInput").val(); 
		var current_city = $(".partie #city_" + partie_class).val(); 
		var current_state = $(".partie #administrative_area_level_1_" + partie_class).val(); 
		var current_zip = $(".partie #postal_code_" + partie_class).val(); 
		
		//if (this.model.get("street")=="" && current_street!="") {
		if (this.model.get("street")!=current_street) {
			this.model.set("street", current_street);
		}
		//if (this.model.get("suite")=="" && current_suite!="") {
		if (this.model.get("suite")!=current_suite) {
			this.model.set("suite", current_suite);
		}
		if (this.model.get("city")!=current_city) {
			this.model.set("city", current_city);
		}
		if (this.model.get("state")!=current_state) {
			this.model.set("state", current_state);
		}
		// && current_zip!=""
		if (this.model.get("zip")=="") {
			this.model.set("zip", current_zip);
		}
		if (this.model.get("editing")==false) {
			$(master_class + "#party_defendant_optionInput").removeClass("hidden");
		} else {
			$(master_class + "#party_defendant_optionInput").addClass("hidden");
		}
		$(".partie #street_" + partie_class).val(this.model.get("street"));
		$(master_class + ".partie #suiteInput").val(this.model.get("suite"));
		$(".partie #city_" + partie_class).val(this.model.get("city"));
		$(".partie #administrative_area_level_1_" + partie_class).val(this.model.get("state"));
		$(".partie #postal_code_" + partie_class).val(this.model.get("zip"));
		
		//manual follows the input
		$(master_class + "#manual_address").css("display", $(master_class + "#full_addressInput").css("display"));
		
		if (partie_class=="Carrier") {
			if (!this.model.get("distributed")) {
				//financial 
				toggleFormEdit("financial");
				$("#balanceSpan").removeClass("hidden");
			}
			
			if ($(".edit." + partie_class).hasClass("hidden")) {
				$(master_class + "#company_nameInput").show();
				$(master_class + ".token-input-list-eams").hide();
				$(master_class + ".token-input-dropdown-eams").hide();
				
				$(master_class + "#full_nameInput").show();
				$(master_class + ".token-input-list-person").hide();
				$(master_class + ".token-input-dropdown-person").hide();
				
				//$("#phoneInput").focus();
				setTimeout(function() {
					$(master_class + "#company_nameInput").focus();
				}, 50);
			}
		}		
	},
	editPartieForm: function(event) {
		var master_class = this.model.get("master_class");
		if (this.model.get("current_input")!="full_name" && this.model.get("current_input")!="company_name") {
			this.model.set("current_input","edit_button");
		}
		this.togglePartieEdit(event);
		
		$(master_class + "#clear_address_holder").show();
		
		//only show the holder if the grid is hidden
		var partie_type = this.model.get("type");
		if (partie_type=="medical_provider" || partie_type=="employer" || partie_type=="carrier" || partie_type=="plaintiff") { 
			if ($(master_class + "#additional_addressGrid").css("display")=="none") {
				$(master_class + "#add_address_holder").show();
			}
		}
	},
	resetPartieForm: function(event) {
		var master_class = this.model.get("master_class");
		this.model.set("current_input", "reset_button");
		event.preventDefault();
		this.togglePartieEdit(event);
		$(master_class + "#address").hide();
		$(master_class + "#clear_address_holder").hide();
		$(master_class + "#add_address_holder").hide();
	},
	
	saveSuccessful: function() {
		$(".alert-success").addClass("alert-success1");
		$(".alert-success").fadeIn(function() { 
			setTimeout(function() {
					$(".alert-success").fadeOut();
				},1500);
		});
	}
});
window.parties_new_view = Backbone.View.extend({
	initialize:function () {
        //this.model.on("change", this.render, this);
		//this.model.on("add", this.render, this);
    },
	events: {
		"click #search_qme":							"searchQME",
		"click #parties_new_view_all_done":				"doTimeouts"
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "parties_new_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }		
		var self = this;
		
		$(this.el).html(this.template({case_id: self.model.get("case_id"), parties: parties}));
	
		
        return this;
	},
	doTimeouts: function() {
		var self = this;
		
		//gridster the parties_new tab
		gridsterIt(6);
		
		//pi, change venue to court
		var kase_type = self.model.get("case_type");
		var blnWCAB = ((kase_type.indexOf("Worker") > -1) || (kase_type.indexOf("WC") > -1 || kase_type.indexOf("W/C") > -1));
		
		if (!blnWCAB) {
			$("#venue_partie_link").html("Court");
		}
		//ss offices
		if (kase_type == "social_security") {
			var sskase = self.model.clone();
			sskase.set("holder", "offices_holder");
			//get the parties for the case, and then 
			var offices = new Offices([], { case_id: sskase.get("case_id")});
			offices.fetch({
				success: function(offices) {
					var newOfficesView = new socsec_offices_view({el: $("#offices_holder"), model:sskase, collection: offices}).render();
				}
			});
		}
		
		$("#gridster_parties_new li").css("font-size", "1.2em");

	},
	searchQME: function(event) {
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
		
	}
});
window.socsec_offices_view = Backbone.View.extend({
	initialize:function () {
        //this.model.on("change", this.render, this);
		//this.model.on("add", this.render, this);
    },
	events: {
		
	},
    render:function () {
		if (typeof this.template != "function") {
			var view = "socsec_offices_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }		
		var self = this;
		
		$(this.el).html(this.template({case_id: self.model.get("case_id"), offices: this.collection}));
	
		//gridster the parties_new tab
		setTimeout("gridsterIt(61)", 10);
		
        return this;
	}
});
window.parties_contact_view = Backbone.View.extend({
	initialize:function () {
    },
	events: {	
		"click .compose_envelopes":			"composeEnvelopes",
		"click .compose_letters":			"composeLetters"
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "parties_contact_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }		
		var self = this;
		
		this.model.set("composing", false);
		$(this.el).html(this.template());
	
		//gridster the parties_new tab
		setTimeout("gridsterIt(66)", 10);
		
		setTimeout(function() {
			$("#partie_contact_preview_holder").css("height", (window.innerHeight - 140) + "px");
			$("#partie_contact_preview").css("height", (window.innerHeight - 140) + "px");
			$("#partie_contact_preview_holder").css("width", (window.innerWidth -940) + "px");
		}, 2000);
		
        return this;
	},
	composeLetters: function(event) {
		var self = this;
		var blnComposing = this.model.get("composing");
		if (blnComposing) {
			return;
		}
		this.model.set("composing", true);
		var element = event.currentTarget;
		event.preventDefault();
		var arrID = element.id.split("_");
		var partie_type = arrID[2];
		var letter_id = arrID[3];
		var url = 'api/letter/pcreate';
		var formValues = "partie_type=" +  partie_type;
		formValues += "&table_id=" +  letter_id;
		
		$("#download_letter_" + partie_type + "_" + letter_id).html("Loading...");
		
		$.ajax({
			url:url,
			type:'POST',
			data: formValues,
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#partie_contact_preview").attr("src", data.file.replace("../", ""));
					//$("#partie_contact_preview_holder").show();
					self.model.set("composing", false);
					//window.open(data.file);
					if (data.success) {
						//download it
						$("#download_letter_" + partie_type + "_" + letter_id).attr("href", data.file);
						$("#download_letter_" + partie_type + "_" + letter_id).html("<span style='background:green; color:white; padding:2px'>download</span>");
					} else {
						$("#download_letter_" + partie_type + "_" + letter_id).attr("href", "");
						$("#download_letter_" + partie_type + "_" + letter_id).html("<span style='background:orange; color:white; padding:2px'>no data</span>");
					}
				}
			}
		});
	},
	composeEnvelopes: function(event) {
		var self = this;
		var blnComposing = this.model.get("composing");
		if (blnComposing) {
			return;
		}
		this.model.set("composing", true);
		var element = event.currentTarget;
		event.preventDefault();
		var arrID = element.id.split("_");
		var partie_type = arrID[2];
		
		$("#download_envelope_" + partie_type).html("Loading...");
		
		var url = 'api/letter/penvelope';
		var formValues = "partie_type=" +  partie_type;
		$.ajax({
			url:url,
			type:'POST',
			data: formValues,
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#partie_contact_preview").attr("src", data.file.replace("../", ""));
					//$("#partie_contact_preview_holder").show();
					self.model.set("composing", false);
					//window.open(data.file);
					if(data.success) {  //
						//download it
						$("#download_envelope_" + partie_type).attr("href", data.file);
						$("#download_envelope_" + partie_type).html("<span style='background:green; color:white; padding:2px'>download</span>");
					} else {
						$("#download_envelope_" + partie_type).attr("href", "");
						$("#download_envelope_" + partie_type).html("<span style='background:orange; color:white; padding:2px'>no data</span>");
					}
				}
			}
		});
	}
});
window.parties_new_rolodex = Backbone.View.extend({
	initialize:function () {
        //this.model.on("change", this.render, this);
		//this.model.on("add", this.render, this);
    },

    render:function () {		
		var self = this;
		
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "parties_new_rolodex";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
	
		//gridster the parties_new tab
		setTimeout("gridsterIt(6)", 10);
        return this;
	}
});
var carrierTokenInput = function() {
	var theme_carrier = {
		theme: "eams", 
		tokenLimit:1,
		minChars:3, 
		noResultsText:"None Found...", 
		onAdd: function(item) {
			//per thomas,, no longer sctrict 7/10/2015
			/*
			if (isNaN(item.id)) {
				//this is unacceptable, as this is a strict lookup
				$("#company_nameInput").tokenInput("remove", item);
				return;
			}
			*/
			var master_class = "";
			if (document.location.hash=="#intake"){
				master_class = ".Carrier ";
				$(master_class + "#token_add_link").hide();
			}
			
			$(master_class + ".partie #company_nameInput").val(item.name);
			$(master_class + " #corporation_id").val(item.id);
			
			//ONLY if looked up, not ADD
			if (typeof item.eams_ref_number != "undefined") {
				//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
				$(master_class + "#carrier_eams_ref_numberInput").val(item.eams_ref_number);
				var the_street = item.street_1;
				if (item.street_2!="") {
					the_street += ", " + item.street_2;
				}
				$(master_class + ".partie #full_addressInput").val(the_street + ", " + item.city + ", " + item.state + " " + item.zip_code);
				$(master_class + "#street_Carrier").val(item.street_1);
				$(master_class + ".partie #suiteInput").val(item.street_2);
				$(master_class + "#city_Carrier").val(item.city);
				$(master_class + "#administrative_area_level_1_Carrier").val(item.state);
				$(master_class + "#postal_code_Carrier").val(item.zip_code);
				$(master_class + ".partie #phoneInput").val(item.phone);
				//leave the corporation_id empty and add the carrier eams number as parent
				$(master_class + ".partie #parent_corporation_uuid").val(item.eams_ref_number);
				
				if (blnAutoSave) {
					if (document.location.hash=="#intake") {
						$(master_class + ".partie #full_addressInput").trigger("click");
					}
				}
			}
			
			$(master_class + ".token-input-list-person").remove();
			$(master_class + ".token-input-dropdown-person").hide();
				
			//since the company has been chosen, now we can pick an examiner
			employeeTokenInput(item.id, "carrier");
			if ($(master_class + ".partie .token-input-list-person").hasClass("hidden")) {
				$(master_class + ".partie .token-input-list-person").show();
				$(master_class + ".token-input-dropdown-person").css("width", "424px");
			}
			$(master_class + ".token-input-dropdown-person").css("width", "380px");
			
			setTimeout(function() {
				if (document.location.hash.indexOf("#intake")==0) {
					//trigger a blur to save it
					$(master_class + "#company_nameInput").trigger("blur");
				}
			}, 888);
		},
		onDelete: function() {
			var master_class = "";
			if (document.location.hash=="#intake"){
				master_class = ".Carrier ";
			}
			
			$(master_class + ".partie #table_id").val("");
			$(master_class + ".partie #corporation_id").val("");
			$(master_class + ".partie .input_class").val("");
			$(master_class + ".partie .span_class").html("");
			
			setTimeout(function() {
				$(master_class + ".token-input-list-eams").remove();
				$(master_class + ".token-input-dropdown-eams").hide();
				carrierTokenInput();
				$(master_class + ".token-input-list-eams").toggleClass("hidden");
			}, 100);
		}
	}
	var master_class = "";
	if (document.location.hash=="#intake"){
		master_class = ".Carrier ";
	}
	$(master_class + "#company_nameInput").tokenInput("api/eams_token", theme_carrier);
	$(master_class + ".partie .token-input-list-eams").css("margin-left", "70px");
	$(master_class + ".partie .token-input-list-eams").css("margin-top", "-20px");
	$(master_class + ".partie .token-input-list-eams").css("width", "383px");
	//$(".token-input-dropdown-eams").css("width", "520px");
	$(master_class + ".partie .token-input-list-eams").toggleClass("hidden");
}
var repTokenInput = function(source) {
	if (typeof source == "undefined") {
		source = "eams_reptoken";
	}
	var theme = {
		theme: "eams", 
		tokenLimit:1,
		minChars:3, 
		noResultsText:"None Found...", 
		onAdd: function(item) {
			
			if (isNaN(item.id)) {
				//this is unacceptable, as this is a strict lookup
				$("#company_nameInput").tokenInput("remove", item);
				return;
			}
			if (item.eams_ref_number=="-1") {
				alert("This company is not EAMS-approved");
			}
			$(".partie #company_nameInput").val(item.name);
			$(".partie #corporation_id").val(item.id);
			//ONLY if looked up, not ADD
			if (typeof item.eams_ref_number != "undefined") {
				//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
				$("#carrier_eams_ref_numberInput").val(item.eams_ref_number);
				var the_street = item.street_1;
				if (item.street_2!="") {
					the_street += ", " + item.street_2;
				}
				$(".partie #full_addressInput").val(the_street + ", " + item.city + ", " + item.state + " " + item.zip_code);
				var partie_name = $(".partie #partie").val();
				var blurb = $(".partie #type").val();
				$("#street_" + partie_name).val(item.street_1);
				$(".partie #suiteInput").val(item.street_2);
				$("#city_" + partie_name).val(item.city);
				$("#administrative_area_level_1_" + partie_name).val(item.state);
				$("#postal_code_" + partie_name).val(item.zip_code);
				$(".partie #phoneInput").val(item.phone);
				//leave the corporation_id empty and add the carrier eams number as parent
				$(".partie #parent_corporation_uuid").val(item.eams_ref_number);
			}
			//since the company has been chosen, now we can pick an examiner
			employeeTokenInput(item.id, blurb);
			if ($(".partie .token-input-list-person").hasClass("hidden")) {
				$(".partie .token-input-list-person").show();
			}
			
			$("#company_nameInput").val(item.name);
			$("#company_nameInput").show();
			$(".token-input-list-eams").hide();
			$(".token-input-dropdown-eams").hide();
			
			setTimeout(function() {
				if (document.location.hash.indexOf("#intake")==0) {
					//trigger a blur to save it
					$("#company_nameInput").trigger("blur");
				}
			}, 888);
		},
		onDelete: function() {
			$(".partie #table_id").val("");
			$(".partie #corporation_id").val("");
			$(".partie .input_class").val("");
			$(".partie .span_class").html("");
			
			setTimeout(function() {
				$(".token-input-list-eams").remove();
				$(".token-input-dropdown-eams").hide();
				var source = "";
				var partie_type = $(".partie #type").val();
				if(partie_type=="claim") {	
					source = "eams_claimanttoken";
				}
				if(partie_type=="defense") {	
					source = "eams_defense_token";
				}
				repTokenInput(source);
				$(".token-input-list-eams").toggleClass("hidden");
			}, 100);
		}
	}
	$("#company_nameInput").tokenInput("api/" + source, theme);
	$(".partie .token-input-list-eams").css("margin-left", "70px");
	$(".partie .token-input-list-eams").css("margin-top", "-20px");
	$(".partie .token-input-list-eams").css("width", "383px");
	$(".partie .token-input-list-eams").toggleClass("hidden");
	
}
var defenseTokenInput = function() {
	$(".token-input-dropdown-person").css("width", "380px");
	var theme = {
		theme: "eams", 
		tokenLimit:1,
		minChars:3, 
		noResultsText:"None Found...", 
		onAdd: function(item) {
			$(".token-input-dropdown-person").css("width", "380px");
			if (isNaN(item.id)) {
				//this is unacceptable, as this is a strict lookup
				$("#company_nameInput").tokenInput("remove", item);
				return;
			}
			$(".partie #company_nameInput").val(item.name);
			$(".partie #corporation_id").val(item.id);
			//ONLY if looked up, not ADD
			if (typeof item.eams_ref_number != "undefined") {
				//eams number - THERE MUST BE AN ADHOC eams_id FIELD ON THE FORM
				$("#defense_eams_ref_numberInput").val(item.eams_ref_number);
				var the_street = item.street_1;
				if (item.street_2!="") {
					the_street += ", " + item.street_2;
				}
				$(".partie #full_addressInput").val(the_street + ", " + item.city + ", " + item.state + " " + item.zip_code);
				$(".partie #phoneInput").val(item.phone);
				//leave the corporation_id empty and add the carrier eams number as parent
				$(".partie #parent_corporation_uuid").val(item.eams_ref_number);
			}
			//since the company has been chosen, now we can pick an examiner
			employeeTokenInput(item.id, "defense");
			if ($(".partie .token-input-list-person").hasClass("hidden")) {
				$(".partie .token-input-list-person").show();
				$(".token-input-dropdown-person").css("width", "350px");
			}
			$(".token-input-dropdown-person").css("width", "350px");
			
			setTimeout(function() {
				if (document.location.hash.indexOf("#intake")==0) {
					//trigger a blur to save it
					$("#company_nameInput").trigger("blur");
				}
			}, 888);
		},
		onDelete: function() {
			$(".partie #table_id").val("");
			$(".partie #corporation_id").val("");
			$(".partie .input_class").val("");
			$(".partie .span_class").html("");
			
			setTimeout(function() {
				$(".token-input-list-eams").remove();
				$(".token-input-dropdown-eams").hide();
				defenseTokenInput();
				$(".token-input-list-eams").toggleClass("hidden");
			}, 100);
		}
	}
	$("#company_nameInput").tokenInput("api/defense/tokeninput/" + item.id, theme);
	$(".partie .token-input-list-eams").css("margin-left", "70px");
	$(".partie .token-input-list-eams").css("margin-top", "-20px");
	$(".partie .token-input-list-eams").css("width", "383px");
	$(".partie .token-input-list-eams").toggleClass("hidden");
}
var employeeTokenInput = function(corporation_id, blurb) {
	$(".token-input-dropdown-person").css("width", "380px");
	var theme = {
		theme: "person", 
		tokenLimit:1,
		minChars:3, 
		noResultsText:"None Found...", 
		onAdd: function(item) {
			$(".token-input-dropdown-person").css("width", "380px");
			//console.log(item.id);
			var arrName = item.name.split("(");
			item.name = arrName[0].trim();
			$(".partie #full_nameInput").val(item.name);
			if (typeof item.salutation != "undefined") {
				$(".partie #employee_phoneInput").val(item.phone);
				$(".partie #employee_faxInput").val(item.fax);
				$(".partie #employee_emailInput").val(item.email);
				$(".partie #salutationInput").val(item.salutation);
			}
			$(".partie #full_nameInput").show();
			$(".token-input-list-person").hide();
			
			setTimeout(function() {
			if (document.location.hash.indexOf("#intake")==0) {
				//trigger a blur to save it
				$(".partie #full_nameInput").trigger("blur");
			}
		}, 888);
			
			return;
		}
	}
	switch(blurb) {
		case "carrier":
			$(".partie #full_nameInput").tokenInput("api/examiners/tokeninput/" + corporation_id, theme);
			break;
		case "defense":
			$(".partie #full_nameInput").tokenInput("api/attorneys/tokeninput/" + corporation_id, theme);
			break;
		case "prior_attorney":
			$(".partie #full_nameInput").tokenInput("api/attorneys/tokeninput/" + corporation_id, theme);
			break;
		default:
			$(".partie #full_nameInput").tokenInput("api/employees/tokeninput/" + corporation_id, theme);
			break;
	}
	 
	$(".partie .token-input-list-person").css("margin-left", "70px");
	$(".partie .token-input-list-person").css("margin-top", "-20px");
	$(".partie .token-input-list-person").css("width", "383px");
	//$(".partie .token-input-list-person").toggleClass("hidden");
	if ($(".partie .token-input-list-person").hasClass("hidden")) {
		$(".partie .token-input-list-person").show();
	}
}
var partieTokenInput = function(this_model) {
	$(".token-input-dropdown-facebook").css("width", "380px");
	var master_class = this_model.get("master_class");
	var theme_partie = {
		theme: "facebook", 
		tokenLimit:1,
		minChars:3, 
		noResultsText:"Not Found...", 
		onAdd: function(item) {
			$(master_class + "#token_add_link").hide();
			$(master_class + ".token-input-dropdown-facebook").css("width", "480px");
			
			//look up the employer info
			if (master_class=="") {
				var thetype = $(master_class + ".partie #type").val();
			} else {
				var thetype = master_class.replace("_form", "").toLowerCase();
				thetype = thetype.replace("#", "");
			}
			thetype = thetype.trim();
			
			if (thetype=="employer") {
				checkEmployerLength(item.name);
			}
			
			if (!isNaN(item.id) && item.id!="" && typeof item.initial == "undefined") {
				
				var partie_corporation = new Corporation({ id: item.id, type:thetype });
				partie_corporation.fetch({
					success: function(data) {
						//now populate the appropriate fields
						var arrFields = $(master_class + " .input_class");
						var arrayLength = arrFields.length;
						for (var i = 0; i < arrayLength; i++) {
							var theid = arrFields[i].id;
							var name_property = theid.replace("Input", "");
							name_property = name_property.replace("_" + this_model.toJSON().partie_type, "");
							
							if (name_property == "administrative_area_level_1") {
								name_property = "state";
							}
							if (name_property == "postal_code") {
								name_property = "zip";
							}
							var property_value = data.get(name_property);
							if (typeof property_value != "undefined") {
								if (theid != "company_nameInput") {
									if ($(master_class + ".partie #" + theid).length  > 0) {
										$(master_class + ".partie #" + theid).val(property_value);
									} else {
										$(master_class + " #" + theid).val(property_value);
									}
								} else {
									//clean up in case address is included in the search element
									$(master_class + ".partie #" + theid).val(property_value);
									$(master_class + ".partie #" + theid.replace("Input", "Span")).html(property_value);
									
									//but they might want to use the company and create a new location
									$(master_class + "#token_add_link").show();
									
									checkEmployerLength();
								}
							}
						}
						
						if (blnAutoSave) {
							if (document.location.hash=="#intake") {
								$(master_class + ".partie #full_addressInput").trigger("click");
							}
						}
						
						$(master_class + " #corporation_id").val(item.id);
						if ($(master_class + ".partie #full_nameInput").val()=="") {
							//since the company has been chosen, now we can pick an employee
							employeeTokenInput(item.id, thetype);
						}
						setTimeout(function() {
							if (this_model.get("full_name")!="") {
								$(master_class + ".partie #full_nameInput").tokenInput("add", {id: this_model.get("id"), name: this_model.get("full_name")});
								
							} else {
								if ($(master_class + "#full_nameInput").hasClass("hidden") != $(".token-input-list-person").hasClass("hidden")) {
									$(master_class + ".token-input-list-person").toggleClass("hidden");
								}
							}
						}, 555);
					}
			});
		}
		$(master_class + "#company_nameInput").val(item.name);
		if (master_class=="") {
			$("#company_nameInput").show();
			$(".token-input-list-facebook").hide();
			$(".token-input-dropdown-facebook").hide();
			//$("#phoneInput").focus();
			setTimeout(function() {
				$("#full_nameInput").focus();
				$("#token_add_link").show();
				
				checkEmployerLength();
			}, 50);
		}
		setTimeout(function() {
			if (document.location.hash.indexOf("#intake")==0) {
				//trigger a blur to save it
				$(master_class + "#company_nameInput").trigger("blur");
			}
		}, 888);
	}};
	
	//var thetype = $(master_class + ".partie #type").val();
	var thetype = this_model.get("partie_type").toLowerCase();
	//set the api call for data here
	if (thetype=="insurance carrier") { 
		thetype = "carrier"; 
	}
	thetype = thetype.replaceAll(" ", "_");
	
	$(master_class + "#company_nameInput").tokenInput("api/corporation/tokeninput/" + thetype, theme_partie);
	$(master_class + ".partie .token-input-list-facebook").css("margin-left", "70px");
	$(master_class + ".partie .token-input-list-facebook").css("margin-top", "-20px");
	$(master_class + ".partie .token-input-list-facebook").css("width", "383px");
	$(master_class + ".token-input-dropdown-facebook").css("width","383px");
	
	var self = this;
	if (this_model.get("company_name")!="") {
		setTimeout(function() {
			$("#company_nameInput").tokenInput("add", {id: this_model.get("id"), name: this_model.get("company_name"), initial: true});
		}, 500);
	}
	if (master_class=="") {
		setTimeout(function() {
			$(master_class + "#token-input-company_nameInput").focus();
		}, 999);
	}
}
var checkCoverage = function() {
	var start_coverage = $('#coverage_date_startInput').val();
	var end_coverage = $('#coverage_date_endInput').val();
	
	if (moment(start_coverage) > moment(end_coverage)) {
		setCoverageEnd();
	}
}
var setCoverageEnd = function() {
	var start_coverage = $('#coverage_date_startInput').val();
	var arrCoverage = start_coverage.split("/");
			
	if (arrCoverage.length==3) {
		var defaulted_year = Number(arrCoverage[2]) + 1;
		date_coverage = arrCoverage[0] + "/" + arrCoverage[1] + "/" + defaulted_year;
		//$("#coverage_date_endGrid").fadeIn();
		$("#coverage_date_endInput").val(date_coverage);
	}
}
function checkEmployerLength(employer_name) {
	if (typeof employer_name == "undefined") {
		var len = $("#company_nameInput").val().length;
	} else {
		var len = employer_name.length;
	}
	if (len > 56) {
		$("#jetfile_length").html(len);
		$("#token_add_link").hide();
		$(".jetfile_instructions").fadeIn();
	} else {
		$("#token_add_link").show();
		$(".jetfile_instructions").fadeOut();
	}
	
	setTimeout(function() {
		restoreNormalSettings();
	}, 5500);
}
function restoreNormalSettings() {
	$("#token_add_link").show();
	$(".jetfile_instructions").fadeOut();
}