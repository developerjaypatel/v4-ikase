window.calendar_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	initialize:function () {
		_.bindAll(this);
    },
	 events:{
		"click .calendar .save":				"saveCalendar"
    },
    render: function () {
		var self = this;
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		
		var calendar = this.model.toJSON();
		var active_editable = "text";
		calendar.active = '<input name="activeInput" type="' + active_editable + '" id="activeInput" style="width:433px" class="modalInput calendar input_class" autocomplete"off" value="' + calendar.active + '" />';
			
		$(this.el).html(this.template(calendar));
		
		return this;
    },
	saveCalendar:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if(this.model.id==-1){
			var blnValid = $("#calendar").parsley('validate');
			if (blnValid) {
				setTimeout(function() {
					$(".calendar .save").trigger("click");
				}, 1000);
			}
		}
		addForm(event, "calendar");
		return;
    },
	deleteCalendarView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "calendar");
		return;
    },
	
	toggleCalendarEdit: function(event) {
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
		$(".calendar .editing").toggleClass("hidden");
		$(".calendar .span_class").removeClass("editing");
		$(".calendar .input_class").removeClass("editing");
		
		$(".calendar .span_class").toggleClass("hidden");
		$(".calendar .input_class").toggleClass("hidden");
		$(".calendar .input_holder").toggleClass("hidden");
		$(".button_row.calendar").toggleClass("hidden");
		$(".edit_row.calendar").toggleClass("hidden");
	},
	
	resetCalendarForm: function(event) {
		event.preventDefault();
		this.toggleInjuryEdit(event);
		//this.render();
		//$("#address").hide();
	},
	valueCalendarViewChanged: function(event) {
		event.preventDefault();
		//console.log(arguments[0].currentTarget.id);
		var source = arguments[0].currentTarget.id;
		source = source.replace("Input", "");
		
		var newval = $("#" + source + "Input").val();
		if (newval==""){
			if ($("#" + source + "Input").hasClass("required")) {
			newval = "Please fill me in";	
				$("#" + source + "Span").toggleClass("hidden");
			}
		} else {
			if (!$("#" + source + "Span").hasClass("hidden")) {
				$("#" + source + "Span").addClass("hidden");
			}
		}
		$("#" + source + "Span").html(escapeHtml(newval));
	}
});
window.block_dates_view = Backbone.View.extend({
	initialize:function () {
	},
	 events:{
		"change #recurring_spanInput":				"showCount",
		"click #all_day":							"showEnd",
		"click #all_employees":						"showAssignee",
		"click #block_forever":						"blockForever",
		"click #blocked_all_done":					"doTimeouts"
    },
    render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "myModalBody");
			var view = "block_dates_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		$(self.el).html(self.template(self.model.toJSON()));
		
		return this;
	},
	doTimeouts: function() {
		$('.blocked_dates').datetimepicker({ 
			validateOnBlur:false, 
			format:'m/d/Y',
			timepicker: false,
			onChangeDateTime: function(dp,$input) {
			  checkRangeStartEnd();
			  
			  //if the two dates are different, hide the every...
			  if ($('.blocked_dates')[0].value!=$('.blocked_dates')[1].value) {
				  $("#block_recurring_row").hide();
				  return;
			  }
			  $("#block_recurring_row").show();
		  }
		});
		
		var theme_3 = {
			theme: "event",
			onAdd: function(item) {

			}
		};
		$("#assigneeInput").tokenInput("api/user", theme_3);
		$(".token-input-list-event").css("width", "333px");
	},
	showCount: function() {
		$(".recurring_count_cell").css("visibility", "visible");
		$("#recurring_countInput").val(1);
	},
	showEnd: function() {
		if (document.getElementById("all_day").checked) {
			$(".through_cell").hide();
		} else {
			$(".through_cell").show();
		}
	},
	showAssignee: function() {
		if (document.getElementById("all_employees").checked) {
			$("#assignee_holder").css("visibility", "hidden");
		} else {
			$("#assignee_holder").css("visibility", "visible");
		}
	},
	blockForever: function() {
		if (document.getElementById("block_forever").checked) {
			$(".every_cell").hide();
			$("#recurring_countInput").val(9999);
		} else {
			$(".every_cell").show();
			$("#recurring_countInput").val(1);
		}
	}
});
window.blocked_listing_view = Backbone.View.extend({
	initialize:function () {
	},
	 events:{
		"click #blocked_listing_done":					"doTimeouts"
    },
    render: function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "blocked_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		
		$(self.el).html(self.template({blockeds: this.collection.toJSON()}));
		
		return this;
	},
	doTimeouts: function() {
	}
});