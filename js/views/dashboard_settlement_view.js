window.dashboard_settlement_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		if (typeof this.template != "function") {
			this.model.set("holder", "kase_content");
			var view = "dashboard_settlement_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		var self = this;
		$(this.el).html(this.template());
		//put injury in subview holder
		var settlement = new Settlement({injury_id: this.model.get("injury_id")});
		settlement.fetch({
			success: function(data) {
				//data.set("settlement_id", data.settlement_id)
				if (data.length == 0) {
					data = new Settlement({injury_id: injury_id});
				}
				data.set("holder", "attorneyfees_holder");
				$('#attorneyfees_holder').html(new settlement_view({model: data}).render().el);
				
				var injury_id = self.model.get("injury_id");
				//now that we have a settlement, let's show the fee screens
				var fee = new Fee({injury_id: injury_id, type: "prior_referral"});
				fee.fetch({
					success: function(fee) {
						//data.set("settlement_id", data.settlement_id)
						if (fee.length == 0) {
							fee = new Fee({injury_id: injury_id, type: "prior_referral"});
						}
						fee.set("holder", "priorreferral_holder");
						$('#priorreferral_holder').html(new prior_referral_view({model: fee}).render().el);
					}
				});
				
				var fees = new FeesCollection({ injury_id: injury_id, type: "depo_fees" });
				fees.fetch({
					success: function(fees) {
						var empty_model = new Backbone.Model;
						empty_model.set("id", -1);
						empty_model.set("injury_id", injury_id);
						empty_model.set("fee_type", "depo_fees");
						empty_model.set("holder", "depofees_holder");
						$('#depofees_holder').html(new costs_view({collection: fees, model: empty_model}).render().el);
					}
				});
				var costs = new FeesCollection({ injury_id: injury_id, type: "firm_costs" });
				costs.fetch({
					success: function(costs) {
						var empty_model = new Backbone.Model;
						empty_model.set("id", -1);
						empty_model.set("injury_id", injury_id);
						empty_model.set("fee_type", "firm_costs");
						empty_model.set("holder", "firmcosts_holder");
						$('#firmcosts_holder').html(new costs_view({collection: costs, model: empty_model}).render().el);
					}
				});
			}
		});
		
		if (this.model.id > 0) {
			//do we have any notes
			var partie_notes = new NotesByType([], {type: "injurynote", case_id: current_case_id});
			partie_notes.fetch({
				success: function(data) {
					var note_list_model = new Backbone.Model;
					note_list_model.set("display", "sub");
					note_list_model.set("partie_id", self.model.get("id"));
					note_list_model.set("partie_type", "injurynote");
					note_list_model.set("case_id", current_case_id);
					$('#injury_notes_holder').html(new note_listing_view({collection: data, model: note_list_model}).render().el);	
					$('#injury_notes_holder').fadeIn(function() {
						//$('#injury_notes_holder').css("width", "50%");
					});
					
					//now show 
					//setTimeout(function(){
					//	gridsterById("gridster_" + self.model.get("partie"));					
					//}, 100);
				}
			});
		}
        return this;
    }
});
