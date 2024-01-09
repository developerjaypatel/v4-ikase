window.rate_view = Backbone.View.extend({
    initialize:function () {
    },
	events: {
		/*"click #select_all_filters":		"selectAll",*/
		"click #new_fee_button":			"newFee",
		"click #save_fee":					"saveFee",
		"click .fee_checkbox":				"activateFee",
		"click .fee_cell":					"editFee",
		"click .fee_save":					"updateFee",
		"click #show_all_status":			"showAllFee",
		"click #rate_view_all_done":		"doTimeouts"
	},
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "rate_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
        var self = this;
		
		this.displayFees();
		
		
		//open up the info json and make the list
		$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    },
	displayFees: function() {
		var self = this;
		//get the data
		var data = this.model.get("rate_info");
		if (data!="") {
			var arrRows = [];
			var jdata = JSON.parse(data);
			var arrLength = jdata.length;
			for(var i = 0; i < arrLength; i++) {
				var fee = jdata[i];
				var index = i;
				var checked = " checked";
				var row_display = "";
				var cell_display = "color:white";
				var row_class = "active_filter";
				if (fee.deleted=="Y") {
					checked = "";
					row_display = "display:none";
					cell_display = "color:red; text-decoration:line-through;";
					row_class = "deleted_filter";
				}
				var hours = (fee.fee_minutes / 60).toFixed(2);
				
				var input = "<input class='hidden' type='text' id='fee_name_" + index + "' value='" + fee.fee_name + "' />";
				var input2 = "<input class='hidden' type='number' id='fee_minutes_" + index + "' min='0' step='1' style='width:60px' value='" + fee.fee_minutes + "' />&nbsp;<button class='btn btn-xs btn-success fee_save hidden' id='fee_save_" + index + "'>Save</button>";
				var therow = "<tr class='" + row_class + "' style='" + row_display + "'><td class='fee'><input type='checkbox' class='fee_checkbox hidden' value='Y' title='Uncheck to stop using this fee.  Old records currently using this fee will not be affected' id='fee_" + index + "' name='fee_" + index + "'" + checked + "></td><td class='fee' style='" + cell_display + "'><span id='fee_span_" + index + "' class='fee_cell' style='cursor:pointer' title='Click to edit this fee'>" + fee.fee_name + "</span>" + input + "</td><td><span id='minutes_span_" + index + "' class='fee_cell'>" + fee.fee_minutes + " minutes</span>" + input2 + "</td><td><span id='hours_span_" + index + "'>" + hours + " hours</span></td></tr>";
				arrRows.push(therow);
			}
			var html = "<table id='fee_table' style='width:100%'>" + arrRows.join("") + "</table>";
			this.model.set("html", html);
		}
		
		setTimeout(function() {
			$("#case_type").val(self.model.get("case_type"));
		}, 789);
	},
	editFee:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var fee_index = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#fee_value_" + fee_index).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#fee_span_" + fee_index).fadeOut();
		$("#minutes_span_" + fee_index).fadeOut();
		$("#fee_name_" + fee_index).toggleClass("hidden");
		$("#fee_minutes_" + fee_index).toggleClass("hidden");
		$("#fee_save_" + fee_index).toggleClass("hidden");
		$("#fee_" + fee_index).toggleClass("hidden");
	},
	showAllFee: function() {
		$("#show_fees").hide();
		$(".deleted_filter").show();
	},
	newFee:function(event) {
		event.preventDefault();
		$("#new_fee_button").fadeOut();
		$("#new_fee_holder").fadeIn(function() {
			$("#new_fee").focus();
		});
	},
	saveFee:function(event) {
		var self = this;
		
		event.preventDefault();
		var url = 'api/rate/save';
		var mymodel = this.model.toJSON();
		
		var rate_id = $("#rate_id").val();
		var rate_name = $("#rate_name").val();
		var rate_description = $("#rate_description").val();
		var case_type = $("#case_type").val();
		var fee = $("#new_fee").val();
		var new_minutes = $("#new_minutes").val();
		
		var formValues = "rate_id=" + rate_id + "&deleted=N&rate_name=" + encodeURIComponent(rate_name) + "&rate_description=" + encodeURIComponent(rate_description) + "&fee=" + encodeURIComponent(fee) + "&minutes=" + new_minutes + "&case_type=" + case_type;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#myModalLabel").css("color", "lime");
					var rate_id = data.rate_id;
					setTimeout(function() {
						var rate = new KaseRate({id: rate_id});
		
						rate.fetch({
							success: function (mymodel) {
								$("#myModalBody").html(new rate_view({model: mymodel}).render().el);
								$("#myModalLabel").css("color", "white");
							}
						});
					}, 2500);
				}
			}
		});

		//
	},
	activateFee: function(event) {
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var fee_index = arrID[arrID.length - 1];
		
		$("#fee_save_" + fee_index).trigger("click");
	},
	updateFee:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var fee_index = arrID[arrID.length - 1];
		var checkbox = document.getElementById("fee_" + fee_index);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var fee_name = $("#fee_name_" + fee_index).val();
		var fee_minutes = $("#fee_minutes_" + fee_index).val();
		var rate_id = $("#rate_id").val();
		
		//can't edit these yet
		var rate_id = $("#rate_id").val();
		var rate_name = $("#rate_name").val();
		var rate_description = $("#rate_description").val();
		
		var mymodel = this.model.toJSON();
		
		var url = 'api/rate/save';
		var formValues = "rate_id=" + rate_id + "&rate_name=" + encodeURIComponent(rate_name) + "&rate_description=" + encodeURIComponent(rate_description) + "&fee=" + fee_name + "&minutes=" + fee_minutes + "&deleted=" + deleted;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#myModalLabel").css("color", "lime");
					var rate_id = data.rate_id;
					setTimeout(function() {
						var rate = new KaseRate({id: rate_id});
		
						rate.fetch({
							success: function (mymodel) {
								$("#myModalBody").html(new rate_view({model: mymodel}).render().el);
								$("#myModalLabel").css("color", "white");
							}
						});
					}, 2500);
				}
			}
		});
		//
	},
	doTimeouts: function() {
		var self = this;
		
		if (this.model.id < 0) {
			$("#new_fee").trigger("click");
		}
	}
});
window.rate_listing_view = Backbone.View.extend({

    initialize:function () {

    },
	events: {
		"click .compose_message":						"newMessage",
		"click .delete_icon":							"confirmdeleteRate",
		"click .delete_yes":							"deleteRate",
		"click .delete_no":								"canceldeleteRate",
		"click #note_clear_search":						"clearSearch",
		"click #label_search_rate":						"Vivify",
		"click #rates_searchList":						"Vivify",
		"focus #rates_searchList":						"Vivify",
		"blur #rates_searchList":						"unVivify",
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "rate_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}	
		var self = this;
		
		$(this.el).html(this.template({rates: this.collection.toJSON()}));
		
		tableSortIt("rate_listing");
		
		return this;
    },
	confirmdeleteRate: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 50, left: arrPosition[1] + 50, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteRate: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteRate: function(event) {
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		this.model.set("editing", true);
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "rate");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteRate(event);
			$(".user_data_row_" + id).css("background", "red");
			this.model.set("editing", false);
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".user_data_row_" + id).fadeOut();
			}, 2500);
		}
	},
	clearSearch: function() {
		$("#rates_searchList").val("");
		$( "#rates_searchList" ).trigger( "keyup" );
	},
	unVivify: function(event) {
		var textbox = $("#rates_searchList");
		var label = $("#label_search_rates");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#rates_searchList");
		var label = $("#label_search_rates");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#rates_searchList').focus();
		}
	}
});