window.dashboard_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		var self = this;
		
		if (typeof this.template != "function") {
			var view = "dashboard_view";
			var extension = "php";
			this.model.set("holder", "kase_content");
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		mymodel = this.model.toJSON();
		$(this.el).html(this.template());
		
		//put employer in subview holder
		corporations = new Corporations([], { case_id: mymodel.id });
		corporations.fetch({
			success: function(data) {
				//console.log(data);
				employer_partie = corporations.findWhere({"type": "employer"});
				
				if (typeof employer_partie == "undefined") {
					employer_partie = new Corporation({ case_id: mymodel.id, type:"employer" });
					employer_partie.set("type", "employer");
					employer_partie.set("partie_type", "Employer");
				}
				//employer_partie sub screens
				if (employer_partie.get("color")=="_edit") {
					//employer_partie.set("color", partie_type_color["employer"]);
					employer_partie.set("color", partie_settings.findWhere({"blurb":"employer"}).get("color"));
				}
				//employer_partie.set("color", "_card_dark_1");
				employer_partie.set("partie", "Employer");
				employer_partie.set("case_id", self.model.get("id"));
				employer_partie.set("case_uuid", self.model.get("uuid"));
				employer_partie.set("grid_it", true);
				$('#employer_holder').html(new partie_view({model: employer_partie, collection:[]}).render().el);
				
				carrier_partie = corporations.findWhere({"type": "carrier"});
				if (typeof carrier_partie == "undefined") {
					carrier_partie = new Corporation({ case_id: mymodel.id, type:"carrier" });
					carrier_partie.set("partie_type", "Carrier");
					carrier_partie.set("type", "carrier");
				}
				
				//carrier_partie sub screens
				if (carrier_partie.get("color")=="_edit") {
					//carrier_partie.set("color", partie_type_color["carrier"]);
					carrier_partie.set("color", partie_settings.findWhere({"blurb":"carrier"}).get("color"));
				}
				carrier_partie.set("partie", "Carrier");
				carrier_partie.set("case_id", self.model.get("id"));
				carrier_partie.set("case_uuid", self.model.get("uuid"));
				carrier_partie.set("grid_it", true);
				carrier_partie.set("show_buttons", false);
				$('#carrier_holder').html(new partie_view({model: carrier_partie, collection:[]}).render().el);
				
				defense_partie = corporations.findWhere({"type": "defense"});
				if (typeof defense_partie == "undefined") {
					defense_partie = new Corporation({ case_id: mymodel.id, type:"defense" });
					defense_partie.set("partie_type", "Defense Attorney");
					defense_partie.set("type", "defense");
				}
				//defense_partie sub screens
				//carrier_partie sub screens
				if (defense_partie.get("color")=="_edit") {
					//defense_partie.set("color", partie_type_color["defense"]);
					defense_partie.set("color", partie_settings.findWhere({"blurb":"defense"}).get("color"));
				}
				defense_partie.set("partie", "Defense");
				defense_partie.set("case_id", self.model.get("id"));
				defense_partie.set("case_uuid", self.model.get("uuid"));
				defense_partie.set("grid_it", true);
				defense_partie.set("show_buttons", false);
				$('#defense_holder').html(new partie_view({model: defense_partie, collection:[]}).render().el);
				
				setTimeout(function() {
					$(".Employer #company_nameInput").focus();
				}, 600);
			}
		});
		
		//gridster the edit tab
		setTimeout("gridsterIt(0)", 500);
        return this;
    }
});
