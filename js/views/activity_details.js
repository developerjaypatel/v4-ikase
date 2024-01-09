window.activity_bill_view = Backbone.View.extend({
    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	events:{
		"click #statusInput":								"removeWheels",
		"click #close_activity":							"closeActivity",
		"change #statusInput":								"selectBillingType",
		"focus #activity_codeInput":						"showActivityCategories",
		"click #activity_codeInput":						"showActivityCategories",
		"blur #activity_codeInput":							"hideActivityCategories",
		"keyup #activity_codeInput":						"hideActivityCategories",
		"change #activity_categories":						"selectActivityCategory",
		"click #activity_bill_all_done":					"doTimeouts",
		//"click #send_documents_2": 						"sendActivityDocuments"
    },
    render: function () {
		console.log('activity bill view');
		
		var self = this;
		if (typeof this.template != "function") {
			var view = "activity_bill_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		var billing_date = this.model.get("billing_date");
		billing_date = this.model.get("billing_date").split(" ")[0];
		this.model.set("billing_date", billing_date);
		
		if (this.model.get("activity_status")==null) {
			this.model.set("activity_status", "Hourly");
		}
		if (typeof this.model.get("filters") != "undefined") {
			var filters = this.model.get("filters");
		
			filters.sort();
			var arrLength = filters.length;
			var arrOptions = [];
			for (var i = 0; i < arrLength; i++) {
				var option = "<option value='" + filters[i] + "'>" + filters[i] + "</option>";
				arrOptions.push(option);
			}
			this.model.set("filters", arrOptions.join(""));
		} else {
			this.model.set("filters", []);
		}
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		//$(this.el).html(this.template(this.model.toJSON()));
		
        return this;
    },
	removeWheels: function() {		
		$("#wheels").fadeOut(function() {
			$("#training_wheels").html("This activity can be now saved as an invoice item&nbsp;&#10003;");
			$("#wheels").fadeIn();
		});
			
	},
	sendActivityDocuments: function (event) {
		event.preventDefault();
		alert("Hello world!");
	},
	selectBillingType: function(event) {
		var element = event.currentTarget;
		
		$(".billing_type_holder").hide();
		if (element.value=="Hourly") {
			$("#hourly_holder").show();
		}
		if (element.value=="Cost") {
			$(".cost_holder").show();
		}
	},
	showActivityCategories: function() {
		$("#activity_categories").show();
	},
	hideActivityCategories: function() {
		if ($("#activity_categories").css("display")!="none") {
			setTimeout(function() {
				$("#activity_categories").hide();
			}, 377);
		}
	},
	selectActivityCategory: function() {
		$("#activity_codeInput").val($("#activity_categories").val());
	},
	doTimeouts: function(event) {
		$('#billing_form #billing_dateInput').datetimepicker(
			{ 
			validateOnBlur:false, 
			minDate: 0, 
			timepicker: false,
			format:'m/d/Y'
			}
		);
		var theme = {tokenLimit: 1};
		$("#billing_form #timekeeperInput").tokenInput("api/user", theme);
		$("#billing_form .token-input-list").css("width", "330px");
		
		var bill = this.model.toJSON();
		$("#billing_form #billing_dateInput").val(bill.billing_date);
		$("#billing_form #durationInput").val(bill.hours);
		$("#billing_form #descriptionInput").val(bill.activity);
		$("#billing_form #activity_id").val(bill.activity_id);
		$("#billing_form #table_id").val(bill.activity_id);
		
		if (typeof bill.case_id == "undefined") {
			$("#billing_form #case_id").val(current_case_id);
		} else {
			$("#billing_form #case_id").val(bill.case_id);
		}
		
		$("#activity_categories").attr("size", document.getElementById("activity_categories").options.length);
		/*
		//if the category is not on the drop down
		var code_input = document.getElementById("activity_codeInput");
		var code_options = code_input.options;
		var arrLength = code_options.length;
		
		var blnFoundCategory = false;
		for(var i = 0; i < arrLength; i++) {
			var opt = code_options[i];
			if (opt.value==bill.activity_category) {
				blnFoundCategory = true;
				
				code_input.value = bill.activity_category;
				break;
			}
		}
		if (!blnFoundCategory) {
			var new_option = "<option value='" + bill.activity_category + "' selected>" + bill.activity_category.capitalizeWords() + "</option>";
			code_input.innerHTML = new_option;
		}
		*/
		if (bill.activity_id < 0) {
			$("#billing_form #timekeeperInput").tokenInput("add", {id: login_user_id, name: login_username});
			//$("#billing_form #statusInput").val("regular_billable");
			$("#billing_form #billing_rateInput").val("normal");
			$("#billing_form #preview_title").html("New Activity");
		} else {
			$("#billing_form #timekeeperInput").tokenInput("add", {id: bill.activity_user_id, name: bill.user_name});
			
			$("#billing_form #statusInput").val(bill.activity_status);
			if (bill.billing_rate==null) {
				bill.billing_rate = "normal";
			}
			$("#billing_form #billing_rateInput").val(bill.billing_rate);
			$("#billing_form #preview_title").html("Edit Activity");
			
			$("#billing_form #statusInput").trigger("change");
			
			//if the category is not on the drop down
			/*
			var code_input = document.getElementById("activity_codeInput");
			var code_options = code_input.options;
			var arrLength = code_options.length;
			
			var blnFoundCategory = false;
			for(var i = 0; i < arrLength; i++) {
				var opt = code_options[i];
				if (opt.value==bill.activity_category) {
					blnFoundCategory = true;
					
					code_input.value = bill.activity_category;
					break;
				}
			}
			if (!blnFoundCategory) {
				var new_option = "<option value='" + bill.activity_category + "' selected>" + bill.activity_category.capitalizeWords() + "</option>";
				code_input.innerHTML = new_option;
			}
			//assign the activity to the user
			if (bill.activity_user_id!="") {
				if (!isNaN(bill.activity_user_id)) {
					if (bill.activity_user_id > 0) {
						$("#billing_form #timekeeperInput").tokenInput("add", {id: bill.activity_user_id, name: bill.user_name});
					}
				}
			}
			*/
		}
		
		$("#billing_form #preview_title").prepend('<div style="float:right"><a title="Save Activity" class="interoffice save" onClick="saveModal()" style="cursor:pointer; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a><input type="hidden" id="modal_type" value="Activity"></div>');
		$("#billing_form #preview_title").prepend('<div style="float:right"><a onClick="hidePreviewPane()" title="Close Activity" class="interoffice white_text" id="close_activity" style="cursor:pointer; margin-right:10px">&times;</a></div>');
		
		//setup editor
		$(".activity_bill #descriptionInput").cleditor({
			width: 330,
			height: 230,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
	}
});
window.activity_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	 events:{
		"click #close_activity":					"closeActivity",
		"click #activity_all_done":					"doTimeouts"
    },
    render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "activity_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		try {
			$(self.el).html(self.template(self.model.toJSON()));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
		
		return this;
    },
	closeActivity: function(event) {
		$("#invoice_activities").show();
		$("#legend_holder").show();
	},
	doTimeouts: function(event) {
		$("#activityInput").cleditor({
			width:530,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
	}
});