window.calendar_listing_view = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click #new_calendar": 			"newCalendars",
		"click .edit_calendar": 		"editCalendar"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		
		var calendars = this.collection.toJSON();
		_.each( calendars, function(calendar) {
			calendar.calendar_link = '<a title="Click to edit Kalendar" class="edit_calendar white_text" id="compose_calendar_' + calendar.id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer">' + calendar.calendar + '</a>';
		});
		var arrID = [];
		
		$(this.el).html(this.template({calendars: calendars}));
		
		tableSortIt("calendar_listing");
		
		return this;
    },
	editCalendar: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeCalendar(element.id);
	},
	newCalendars: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeCalendar(element.id);
	},
	newCategoryCalendar: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeNewCategoryCalendar(element.id);
	}
});
window.calendar_listing_assign = Backbone.View.extend({

    initialize:function () {
        this.collection.on("change", this.render, this);
		this.collection.on("add", this.render, this);
    },
	events: {
		"click .assign_user": 		"assignUserCalendar",
		"click .assign_permission":	"assignUserCalendar"
	},
    render:function () {		
		var self = this;
		
		this.collection.bind("reset", this.render, this);
		var users = this.collection.toJSON();
		
		_.each( users, function(user) {
			var write_checked = "";
			var show_permissions = "none";
			if (user.assigned==1) {
				show_permissions = "";
			}
			if(user.permissions.indexOf('write') > -1) { 
				var write_checked = " checked";
			}
			var read_checked = "";
			if(user.permissions.indexOf('read') > -1) { 
				var read_checked = " checked";
			}
			user.readwrite = '<div id="permission_holder_' + user.id + '" style="display:' + show_permissions + '"><input type="checkbox" id="user_permissionread_' + user.id + '" ' + read_checked + ' value="read" class="assign_permission" /><span for="user_permissionread_' + user.id + '">Read</span>&nbsp;|&nbsp;<input type="checkbox" id="user_permissionwrite_' + user.id + '" ' + write_checked + ' value="write" class="assign_permission" /><span for="user_permissionwrite_' + user.id + '">Write</span></div>';
		});
		
		//$(this.el).html(this.template({users: users}));
		
		try {
			$(this.el).html(this.template({users: users}));
		}
		catch(err) {
			var view = "calendar_listing_assign";
			var extension = "html";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		tableSortIt("calendar_listing");
		
		return this;
    },
	assignUserCalendar: function(event) {
		var element = event.currentTarget;
		var userArray = element.id.split("_");
		user_id = userArray[2];
		
		//see if the assign is checked, only proceed then
		var element = $("#assign_user_" + user_id);
		if (element.prop("checked")) {
			assignCalendar(user_id, this.model.get("user_id"));
		} else {
			unassignCalendar(user_id, this.model.get("user_id"));
		}
	}
});
