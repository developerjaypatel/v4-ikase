window.related_view = Backbone.View.extend({
	render: function () {
		var self = this;
		
		var kase = kases.findWhere({case_id: this.model.get("case_id")});
		//might not be a valid id...
		if (typeof kase == "undefined") {
			var kase = new Kase({case_id: self.model.get("case_id")});
			kase.fetch({
				success: function (kase) {
					kases.add(kase);
				}
			});
		}
		
		try {
			$(self.el).html(self.template({related: self.model.toJSON()}));
		}
		catch(err) {
			var view = "related_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}

		
		return this;
	},
	events:{
		"click .related .save": 					"addEvent",
		"click #related_view_all_done":				"doTimeouts"
    },
	doTimeouts: function(event) {
		var self = this;
		var theme = {
			theme: "event", 
			tokenLimit: 1, 
			onAdd: function(item) {
				//look up the parties for the case, and then list them in parties_list
				$(".related #injury_id").val(item.injury_id);
			}
		};
		$(".related #case_idInput").tokenInput("api/kases/tokeninput", theme);					
		//we might have a default value
		if (self.model.get("case_id") > 0) {
				var casing_file = $("#case_fileInput").val();
				
					var kase = kases.findWhere({case_id: self.model.get("case_id")});
					//might not be a valid id...
					if (typeof kase != "undefined") {
						//add the kase
						$(".related #case_idInput").tokenInput("add", {
							id: self.model.get("case_id"), 
							name: kase.name(),
							tokenLimit:1
						});
						
						$("#case_id_holder .token-input-list-event").hide();
						$(".case_input .token-input-list-event").hide();
						$("#case_id_holder #case_idSpan").html(kase.name());
						//console.log("Related #1");
					} else {
						//console.log("Related #2");
						var kase = new Kase({case_id: self.model.get("case_id")});
						kase.fetch({
							success: function (kase) {
								$(".related #case_idInput").tokenInput("add", {
									id: self.model.get("case_id"), 
									name: kase.name(),
									tokenLimit:1
								});
								
								$("#case_id_holder .token-input-list-event").hide();
								$(".case_input .token-input-list-event").hide();
								$("#case_id_holder #case_idSpan").html(kase.name());
								return;		
							}
						});
					}
				
		}
		if (self.model.get("case_id") > 0) {
			$(".related #case_id_row").show();
			if (self.model.get("event_kind")!="phone_call") {
				//$("#case_id_holder .token-input-list-event").hide();
				//$(".related #case_idSpan").html(kase.name());
			}
		}
	}
});