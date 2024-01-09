window.user_setting_listing = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click #new_setting": "editSetting",
		"click .compose_new_setting": "editSetting"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			this.model.set("holder", "content");
			var view = "user_setting_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }
		//this.collection.bind("reset", this.render, this);
		var users_setting = this.collection.toJSON();
		 _.each( users_setting, function(user_setting) {
			 user_setting.setting_value = user_setting.setting_value.replaceAll("\r\n", "\r");
			 user_setting.setting_value = user_setting.setting_value.replaceAll("\r", "<br>");
		 });
		try {
			$(this.el).html(this.template({users_setting: this.collection.toJSON()}));
		}
		catch(err) {
			var view = "user_setting_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("user_setting_listing");
		
		return this;
    },
	editSetting: function(event) {
		var element = event.currentTarget;
		var type = "customer";
		event.preventDefault();
		composeUserSetting(element.id, type);
	}

});
