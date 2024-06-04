window.dashboard_user_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
  
    },
    render:function () {
		var self = this;
		
		this.model.user_id = this.model.get("user_id");
		try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "dashboard_user_view";
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
		var user = new User({user_id: this.model.user_id});
		user.fetch({
			success: function (data) {
				data.set("glass", "card_fade_2");
				data.set("gridster_me", true);
				data.set("grid_it", true);
				data.set("holder", "#info_holder");
				$('#info_holder').html(new user_view({model: data, user_id: self.model.user_id}).render().el);
			}
		});
		
		$('#email_holder').html("<div><span style='color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;'>Activity Per Month</span></div><iframe src='https://"+ location.hostname +"/api/activity_month.php?user_id=" + this.model.user_id + "' width='100%' height='200px' frameborder='0'></iframe><div></div><div><span style='color:#FFFFFF; font-size:1.4em; font-weight:lighter; margin-left:10px;'>Activity Last Week</span></div><iframe src='https://"+ location.hostname +"/api/activity_week.php?user_id=" + this.model.user_id + "' width='100%' height='200px' frameborder='0'></iframe>");
		/*
		var signature = new Signature({user_id: this.model.user_id});
		signature.fetch({
			success: function (signature) {
				if (signature.id=="" || typeof signature.id == "undefined") {
					signature.set("id", -1);
				}
				signature.set("glass", "card_fade_5");
				signature.set("gridster_me", true);
				signature.set("holder", "#identification_holder");
				
				$('#identification_holder').html(new signature_view({model: signature}).render().el);				
				$("#identification_holder").addClass("glass_header_no_padding");
			}
		});	
		*/
		/*
		var availability = new Signature({user_id: this.model.user_id});
		signature.fetch({
			success: function (signature) {
				signature.set("glass", "card_fade_5");
				signature.set("gridster_me", true);
				setTimeout(function() {
					$('#identification_holder').html(new signature_view({model: signature}).render().el);				
					$("#identification_holder").addClass("glass_header_no_padding");
				}, 1000);
			}
		});	
		*/
		//$("#hours_holder").html("<div>availability goes here</div>");
		
		var employee_calendars = new PersonalCalendarCollection([]);
		employee_calendars.fetch({
			success: function(data) {
				var empty_model = new Backbone.Model;
				empty_model.set("user_id", self.model.user_id);
				empty_model.set("holder", "#hours_holder");
				$("#hours_holder").html(new calendar_listing_assign({collection: employee_calendars, model: empty_model}).render().el);				
				$("#hours_holder").addClass("glass_header_no_padding");
			}
		});
	}
});
