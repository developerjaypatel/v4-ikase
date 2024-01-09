window.dashboard_injury_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		var self = this;
		$(this.el).html(this.template());
		
		//put injury in subview holder
		if (this.model.get("new_injury")) {
			var injury = new Injury({case_id: self.model.get("case_id")});
			injury.set("id", -1);
			this.model.set("injury_id", "-1");
			injury.set("statute_limitation", "0000-00-00");
		} else {
			var injury = new Injury({case_id: this.model.get("case_id")});
			if (this.model.get("injury_id")!="") {
				injury.set("id", this.model.get("injury_id"));
			} else {
				injury.set("id", this.model.get("id"));
			}
		}
		var injury_id = self.model.get("injury_id");
		injury.fetch({
			success: function (data) {
				//data.set("case_id", self.model.get("case_id"));
				//data.set("case_uuid", self.model.get("case_uuid"));
				data.set("gridster_me", true);
				data.set("glass", "card_fade_4");
				data.set("grid_it", true);
				data.set("employer_address", self.model.get("employer_full_address"));
				data.set("case_type", self.model.get("case_type"));
				setTimeout(function() {
					$('#injury_holder').html(new injury_view({model: data}).render().el);
					var bodyparts = new BodyPartsCollection([], { injury_id: self.model.get("injury_id"), case_id: self.model.get("case_id"), case_uuid: self.model.get("case_uuid") });
					bodyparts.fetch({
						success: function(bodyparts) {
							setTimeout(function() {
								var mymodel = new Backbone.Model();
								mymodel.set("case_id", self.model.get("case_id"));
								mymodel.set("case_uuid", "");
								mymodel.set("injury_id", injury_id);
								
								mymodel.set("holder", "bodyparts_holder");
								
								$('#bodyparts_holder').html(new bodyparts_view({collection: bodyparts, model: mymodel}).render().el);
							}, 600);
						}
					});
					var injury_number = new InjuryNumbersCollection([], { injury_id: self.model.get("injury_id"), case_id: self.model.get("case_id"), case_uuid: self.model.get("case_uuid")});
					//all the numbers for this case injury
					injury_number.fetch({
						success: function(data) {
							if (data.length == 0) {
								data = new InjuryNumber({injury_id: injury_id});
							}
							setTimeout(function() {
								$('#injury_number_holder').html(new injury_number_view({model: data}).render().el);
								$('#empty_injury_holder').html(new injury_add_view({model: data}).render().el);
							}, 700);
						}
					});
					
					self.model.set("hide_upload", true);
					showKaseAbstract(self.model);
				}, 500);
				
				if (data.get("id")==-1) {
					setTimeout(function() {
						$(".bodyparts .edit").trigger("click");	
						$("#bodyparts_buttons").hide();					
					}, 700);
				}
			}
		});
		
		if (injury_id > 0 && injury_id!="") {
			//do we have any notes
			var partie_notes = new InjuryNotesByType([], {type: "injury", injury_id: injury_id});
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
