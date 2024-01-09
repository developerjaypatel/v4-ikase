window.kase_control_panel = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  	
    },
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "kase_control_panel";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		$(this.el).html(this.template());
		//$('#kase_content').html(new kase_listing_view({collection: kases, model: ""}).render().el);
		//put recent messages in subview holder
		messages = new KasePhoneCalls({case_id: this.model.get("case_id")});
		//messages = new InboxCollection();
		messages.fetch({
			success: function (data) {
				if (data.length > 0) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Phone Messages");
					message_listing_info.set("first_column_label", "From");
					message_listing_info.set("receive_label", "On");
					message_listing_info.set("homepage", true);
					message_listing_info.set("event_class", "messages");
					$('#unread_messages').html(new event_listing({collection: data, model: message_listing_info}).render().el);
					$("#unread_messages").removeClass("glass_header_no_padding");
				} else {
					$('#unread_messages').html("<span class='large_white_text'>No Phone Messages</span>");
				}
			}
		});
		
		//fetch my tasks
		tasks = new TaskInboxCollection({ case_id: current_case_id });
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
		
		//fetch out tasks
		tasks = new TaskOutboxCollection({day:"", case_id: current_case_id});
		tasks.fetch({
			success: function (data) {
				if (data.length > 0) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "Tasks from Me");
					task_listing_info.set("homepage", true);
					task_listing_info.set("receive_label", "Due");
					task_listing_info.set("case_id", current_case_id);
					$('#assigned_tasks').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#assigned_tasks").removeClass("glass_header_no_padding");
				} else {
					$('#assigned_tasks').html("<span class='large_white_text'>No Assigned Tasks.</span>");
				}
			}
		});
		
		//fetch upcoming events
		occurences = new AllKaseEvents({case_id: this.model.get("case_id")});
		occurences.fetch({
			success: function (data) {
				if (data.length > 0) {
					var event_listing_info = new Backbone.Model;
					event_listing_info.set("homepage", true);
					event_listing_info.set("title", "Kase Events");
					event_listing_info.set("event_class", "upcoming");
					$('#upcoming_events').html(new event_listing({collection: data, model: event_listing_info}).render().el);
					$("#upcoming_events").removeClass("glass_header_no_padding");
				} else {
					$('#upcoming_events').html("<span class='large_white_text'>No Upcoming Events.</span>");
				}
			}
		});
		var case_status = this.model.toJSON().case_status;
		var case_substatus = this.model.toJSON().case_substatus;
		var attorney = this.model.toJSON().attorney;
		var worker = this.model.toJSON().worker;
		var rating = this.model.toJSON().rating;
		var kase = kases.findWhere({case_id: this.model.get("case_id")});
		var case_id = this.model.get("case_id");
		
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
		/*
		setTimeout(function() {
			$(".pager").hide();
		}, 800);
		*/
		
		/*
		var injury = new Injury({case_id: this.model.id});
		injury.fetch({
			success: function (data) {
				data.set("case_id", self.model.get("id"));
				data.set("case_uuid", self.model.get("uuid"));
				data.set("gridster_me", true);
				data.set("glass", "card_fade_4");
				data.set("grid_it", true);
				setTimeout(function() {
					$('#injury_holder').html(new injury_view({model: data}).render().el);
				}, 1000);
				
				if (data.get("id")==-1) {
					setTimeout(function() {
						$(".bodyparts .edit").trigger("click");	
						$("#bodyparts_buttons").hide();					
					}, 1500);
				}
			}
		});
		*/
		
        return this;
    }
});
