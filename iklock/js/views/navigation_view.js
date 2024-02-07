var search_customer_id = false;
window.navigation_view = Backbone.View.extend({
    initialize:function () {
        
    },
    events:{
		"click #navigation_list_employees":		"listEmployees",
		"click #navigation_new_employee":		"newEmployee",
		"click #logout_link":					"logOut"
    },
    render:function () {
		if (typeof this.template != "function") {
			var view = "navigation_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
         try {
			$(this.el).html(this.template());
		}
		catch(err) {
			var view = "navigation_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
        return this;
    },
	listEmployees:function() {
		document.location.href = "#employees";
	},
	newEmployee:function() {
		document.location.href = "#employees/new";
	},
	scheduleFind: function(event) {
		var self = this;
		clearTimeout(schedule_timeout_id);
		schedule_timeout_id = setTimeout(function() {
			self.findIt(event);
		}, 700);
	},
	findIt: function(event) {
		$(".content").html(loading_image);
		
		var element = event.currentTarget;
		
		var self = this;
		var key = element.value;
		
		if (typeof key =="undefined") {
			return;
		}
		var list_type = "contact_list";
		var my_customers = new ContactCollection();
		
		if (key == "") {
			var mymodel = new Backbone.Model();
			$('.content').html(new contacts_list({collection: my_customers, model: mymodel}).render().el);
			return;
		}
		my_customers.searchDB(key);
	},
	logOut: function() {
		fullLogOut();
	}
});
