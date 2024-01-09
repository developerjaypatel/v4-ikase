var rx_timeout_id;
window.rx_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		
	},
	render:function () {		
		var self = this;
		var rx = this.model.toJSON();
		var start_date = rx.start_date;
		if (start_date!="") {
			start_date = moment(start_date).format('MM/DD/YYYY');
			rx.start_date = start_date;
		}
		var end_date = rx.end_date;
		if (end_date!="") {
			end_date = moment(end_date).format('MM/DD/YYYY');
			rx.end_date = end_date;
		}
		
		try {
			$(this.el).html(this.template(rx));
		}
		catch(err) {
			var view = "rx_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		setTimeout(function() {
			$('.new_rx .rx_date').datetimepicker(
				{ 	
					validateOnBlur:false, 
					format: 'm/d/Y',
					mask:false,
					timepicker: false,
					allowTimes:workingWeekTimes,
					onChangeDateTime:function(dp,$input) {
						var start_date = $(".new_rx #start_dateInput").val();
						var end_date = $(".new_rx #end_dateInput").val();
						var d1 =  new Date(moment(start_date));
						var d2 =  new Date(moment(end_date));
						var diff = d2.getTime() - d1.getTime();
						if (diff < 0) {
							end_date = start_date;
							$(".new_rx #end_dateInput").val(end_date);
						}
					}
				}
			);
			
			//get the doctors
			var parties = new Parties([], { case_id: current_case_id });
			parties.fetch({
				success: function(parties) {
					var medical_providers = parties.where({"type": "medical_provider"});
					var arrMedicalProviders = [];
					var selected = "";
					if (medical_providers.length==0) {
						selected = " selected";
					}
					themedical_provider = "<option value=''" + selected + ">Select from List</option>";
					if (medical_providers.length==1) {
						selected = " selected";
					}
					arrMedicalProviders[arrMedicalProviders.length] = themedical_provider
					var doctor_id = self.model.get("doctor_id");
					_.each(medical_providers , function(medical_provider) {
						var themedical_provider = medical_provider.get("company_name");
						var medical_id = medical_provider.get("corporation_id");
						selected = "";
						if (medical_id == doctor_id) {
							selected = " selected";
						}
						
						themedical_provider = "<option value='" + medical_id + "'" + selected + ">" + themedical_provider + "</option>";
						arrMedicalProviders[arrMedicalProviders.length] = themedical_provider;
					});
					
					$(".new_rx #doctor_idInput").append(arrMedicalProviders.join(""));
				}
			});
		}, 777);
		
		return this;
	}
});
window.rx_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .compose_new_rx":						"newRx",
		"click .edit_rx":								"newRx",
		"click .read_more":								"expandRx",
		"click .hide_rx":								"shrinkRx",
		"click .delete_rx":								"confirmdeleteRx",
		"click .delete_yes":							"deleteRx",
		"click .delete_no":								"canceldeleteRx",
		"click #rx_clear_search":						"clearSearch",
		"click #label_search_rx":						"Vivify",
		"click #rx_searchList":							"Vivify",
		"focus #rx_searchList":							"Vivify",
		"blur #rx_searchList":							"unVivify"
	},
    render:function () {		
		var self = this;
		var rxs = this.collection.toJSON();

		try {
			$(this.el).html(this.template({rxs: rxs, person_id: this.model.get("person_id")}));
		}
		catch(err) {
			var view = "rx_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("rx_listing", 10);
		
		setTimeout(function(){
			$(".pager").hide();
			$(".pager").css("position","absolute");
			$(".pager").css("top","90px");
			$(".pager").css("left","200px");
			$(".pager").show();
		}, 150);
		
		
		return this;
    },
	unVivify: function(event) {
		var textbox = $("#rx_searchList");
		var label = $("#label_search_rx");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#rx_searchList");
		var label = $("#label_search_rx");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#rx_searchList').focus();
		}
	},
	newRx: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewRx(element.id);
	},
	confirmdeleteRx: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "rx");
	},
	clearSearch: function() {
		$("#rx_searchList").val("");
		$( "#rx_searchList" ).trigger( "keyup" );
	},
	canceldeleteRx: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteRx: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "rx");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteTask(event);
			$(".rx_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".rx_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});