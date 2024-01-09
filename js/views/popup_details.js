window.popup_view = Backbone.View.extend({
	events:{
        "click .read_reminder":         "closeReminder",
        "click .snooze":                "snoozeReminder",
        "change .snooze_intervals":     "snoozeUpdate",
        "click .edit_event":		    "editEvent",
        "click .map_location":          "mapLocation"
    },
	render: function () {
        var self = this;
        var reminder = self.model.get("reminder");
        var arrReminders = [];

        var message = reminder.message;
        var reminderbuffer_id = reminder.reminderbuffer_id;
        arrMessage = message.split("\n");
        arrMessages = [];
        arrMessages["plain_message"] = arrMessage[0];
        arrMessages["case_name"] = reminder.case_name;	//arrMessage[1].substring(0,30);
        arrMessages["subject"] = reminder.subject;	//arrMessage[2];
        arrMessages["date_time"] = moment(reminder.message_date).format("MM/DD/YYYY");	//arrMessage[3];    
        arrMessages["location"] = reminder.location;	//arrMessage[4];
        arrMessages["reminderbuffer_id"] = reminderbuffer_id;
        arrMessages["color"] = reminder.color;
        arrMessages["event_id"] = reminder.event_id;
        arrMessages["case_id"] = reminder.case_id;
        arrMessages["reminder_datetime"] = reminder.reminder_datetime;
        self.model.set("arrMessages", arrMessages);
        try {
            $(self.el).html(self.template({reminders: self.model.toJSON()}));
        }
        catch(err) {
            var view = "popup_view";
            var extension = "php";
            
            loadTemplate(view, extension, self);
            
            return "";
        }    
        return this;
    },
    closeReminder: function(event){
        event.preventDefault();
        var self = this;
        var panel_id = self.model.get("panel_id");
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
        var reminderbuffer_id = document.getElementById("reminderbuffer_id_" + arrElementId[2]).value;
        
        // updating cse_message_user
        var url = "api/popupread/" + reminderbuffer_id;
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success:function(data){
                $("#reminder_popup_row_" + arrElementId[2]).css("background-color", "red");
                setTimeout(function() {
                    $("#reminder_popup_row_" + arrElementId[2]).fadeOut();
                    $("#reminder_panel_" + panel_id).remove();
                }, 2500);
            }
        });
    },
    snoozeReminder: function(event){
        event.preventDefault();
        var element = event.currentTarget;
        var element_id = element.id;
        var arrElementId = element_id.split("_");
        $("#snooze_intervals_" + arrElementId[1]).fadeIn();
    },
    snoozeUpdate: function(event){
        event.preventDefault();
        var self = this;
        var panel_id = self.model.get("panel_id");
        var element = event.currentTarget;
        var element_id = element.id;
        var interval = element.value;
        var arrElementId = element_id.split("_");
        var reminderbuffer_id = document.getElementById("reminderbuffer_id_" + arrElementId[2]).value;

        var url = "api/popupsnooze/" + reminderbuffer_id + "/" + interval;
        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success:function(data){
                // console.log(data);
                $("#reminder_popup_row_" + arrElementId[2]).css("background-color", "green");
                setTimeout(function() {
                    $("#reminder_popup_row_" + arrElementId[2]).fadeOut();
                    $("#reminder_panel_" + panel_id).remove();
                }, 2500);
                
            }
        });        
    },
    editEvent: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeEvent(element.id);
	}, 
    mapLocation: function (event) {    
        var self = this;
        var element = event.currentTarget;
        var address = element.innerHTML;
        var url = "https://www.google.com/maps/dir/" + address;
        window.open(url);
    }  
});