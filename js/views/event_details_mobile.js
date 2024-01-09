var postevent_timeoutid = false;
window.event_view_mobile = Backbone.View.extend({
	render: function () {
		var kase = kases.findWhere({case_id: current_case_id});
		this.model.set("case_name","");
		
		if (typeof kase != "undefined") {
			var case_name = kase.toJSON().name
			this.model.set("case_name", case_name);
		}
		
		$(this.el).html(this.template(this.model.toJSON()));
		
		return this;
	},
	events:{
		"click .event .save": 					"addEvent",
		"click #event_view_all_done_mobile":	"doTimeouts"
    },
	doTimeouts: function(event) {
		var self = this;
		
		//HARD CODED
		blnIsCreator = true;
		
		var event_dateandtime = "";
		if (this.model.get("event_dateandtime")!="") {
		var event_dateandtime = moment(this.model.get("event_dateandtime")).format("MM/DD/YYYY hh:mma");
		}
		//event_dateandtime = new Date(event_dateandtime);
		$('#event_dateandtimeInput').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
		    },
		    weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			minDate: 0, 
			value: event_dateandtime,
			allowTimes:workingWeekTimes,
			step:30,
			  onChangeDateTime:function(dp,$input){
				  self.clearErrorWarning();
				  self.setupCalculationOptions();
			  }
			});
		//jQuery('#event_dateandtimeInput').datetimepicker({value:'08/21/2016 04:26pm'});
		
		var theme_3 = {theme: "event"};
		$("#assigneeInput").tokenInput("api/user", theme_3);

		$("#event_descriptionInput").cleditor({
			width:545,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		$('#event_descriptionInput').cleditor()[0].focus();
		
		var assigned_users = new EventUsers([], {event_id: self.model.id, type: "to"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(event_user) {
					$("#assigneeInput").tokenInput("add", {id: event_user.user_id, name: event_user.user_name});		
				});
				
				//maybe not done in import
				if (typeof self.model.get("assignee")!="undefined") {
					if (data.length==0 && self.model.get("assignee")!="") {
						var theworker = worker_searches.findWhere({"nickname": self.model.get("assignee")});
						$("#assigneeInput").tokenInput("add", {id: theworker.id, name: theworker.get("user_name")});		
					}
				}
			}
		});
	},
	addEvent:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "event", "event");
		return;
	}
});