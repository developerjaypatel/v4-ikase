window.payschedule_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		"click #edit_payschedule":				"editPayschedule",
		"click #save_payschedule":				"savePayschedule",
		"keyup #first_days_actualField":		"clearFirstDays",
		"change #first_days_actualField":		"clearFirstDays",
		"change .first_ending":					"clearFirstActualDays",
		"keyup #second_days_actualField":		"clearSecondDays",
		"change #second_days_actualField":		"clearSecondDays",
		"change .second_ending":				"clearSecondActualDays"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "payschedule_edit";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.template(this.model.toJSON()));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);

		return this;
    },
	doTimeouts: function() {
	},
	savePayschedule: function(event) {
		var self = this;
		event.preventDefault();
		$("#save_payschedule").addClass("hide_me");
		
		var url = "api/company/payschedule/save";
		var formData = $("#payschedule_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".payschedule.edit_field").addClass("hide_me");
					$("#header_payschedule").css("background", "green");
					
					var edit_fields = $(".payschedule.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						if(span_id!="") {
							$(".payschedule#" + span_id).html(element.value);
						}
					}
					$("#edit_payschedule").removeClass("hide_me");	
					$(".payschedule.edit_span").removeClass("hide_me");
					
					setTimeout(function() {
						//$("#header_payschedule").css("background", "#000033");
						window.Router.prototype.editPayschedule();
					}, 1500);
				}
			}
		});
	},
	editPayschedule: function(event) {
		event.preventDefault();
		
		$(".payschedule.edit_span").addClass("hide_me");
		$(".payschedule.edit_field").removeClass("hide_me");
		$("#payschedule_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_payschedule").addClass("hide_me");
		$("#save_payschedule").removeClass("hide_me");
		
		$("#header_payschedule").css("background", "lightsalmon");
	},
	clearFirstDays: function() {
		$(".first_ending").val("");
	},
	clearFirstActualDays: function() {
		$("#first_days_actualField").val("");
	},
	clearSecondDays: function() {
		$(".second_ending").val("");
	},
	clearSecondActualDays: function() {
		$("#second_days_actualField").val("");
	}
});