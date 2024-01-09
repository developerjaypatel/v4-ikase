window.dashboard_email_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		var self = this;
		
		self.model.user_id = this.model.get("user_id");
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_email_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		setTimeout(function() {
			self.renderHolders();
		}, 1000);
        return this;
    },
	renderHolders:function() {
		var self = this;
		//put info in subview holder
		var email = new Email({user_id: self.model.user_id});
		email.fetch({
			success: function (email) {
				if (email.id=="" || typeof email.id == "undefined") {
					email.set("id", -1);
				}
				email.set("gridster_me", true);
				email.set("glass", "card_fade_3");
				email.set("holder", "#email_holder");
				$('#email_holder').html(new email_view({model: email}).render().el);				
				//$("#email_holder").addClass("glass_header_no_padding");
			}
		});
		
		var signature = new Signature({user_id: self.model.user_id});
		signature.fetch({
			success: function (signature) {
				if (signature.id=="" || typeof signature.id == "undefined") {
					signature.set("id", -1);
				}
				//signature.set("glass", "card_fade_5");
				signature.set("gridster_me", true);
				signature.set("holder", "#signature_holder");
				
				$('#signature_holder').html(new signature_view({model: signature}).render().el);				
				//$("#signature_holder").addClass("glass_header_no_padding");
			}
		});	
	}
});
