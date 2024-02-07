window.reimbursments_list = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "reimbursments_list";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		/*
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		*/
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		$(".new_reimbursment").on("click", function(event) {
			self.newReimbursment(event);
		});
	},
	newReimbursment: function(event) {
		event.preventDefault();
		
		document.location.href = "#reimbursment/new";
	}
});
window.reimbursment_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "reimbursment_edit";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		/*
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		*/
		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		$("#save_reimbursment").on("click", function(event) {
			self.saveReimbursment(event);
		});
		$("#edit_reimbursment").on("click", function(event) {
			self.editReimbursment(event);
		});
		
		if ($("#reimbursment_id").val()=="new") {
			$("#edit_reimbursment").trigger("click");
			$(".reimbursment#reimbursmentField").focus();
		}
	},
	saveReimbursment: function(event) {
		var self = this;
		event.preventDefault();
		$("#save_reimbursment").addClass("hide_me");
		
		var url = "api/reimbursment/save";
		var formData = $("#reimbursment_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".reimbursment.edit_field").addClass("hide_me");
					$("#header_reimbursment").css("background", "green");
					
					var edit_fields = $(".reimbursment.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						if(span_id!="") {
							$(".reimbursment#" + span_id).html(element.value);
						}
					}
					$("#edit_reimbursment").removeClass("hide_me");	
					$(".reimbursment.edit_span").removeClass("hide_me");
					
					setTimeout(function() {
						//$("#header_reimbursment").css("background", "#000033");
						window.Router.prototype.listReimbursments();
					}, 1500);
				}
			}
		});
	},
	editReimbursment: function(event) {
		event.preventDefault();
		
		$(".reimbursment.edit_span").addClass("hide_me");
		$(".reimbursment.edit_field").removeClass("hide_me");
		$("#reimbursment_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_reimbursment").addClass("hide_me");
		$("#save_reimbursment").removeClass("hide_me");
		
		$("#header_reimbursment").css("background", "lightsalmon");
	}
});
window.employee_reimbursments_list = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "employee_reimbursments_list";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		/*
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		*/
		return this;
    },
	doTimeouts: function() {
		var self = this;
		$(".new_reimbursment").on("click", function(event) {
			self.newReimbursment(event);
		});
		
		$("#user_reimbursment_select_all").on("change", function(event) {
			$(".user_reimbursment").prop("checked", $("#user_reimbursment_select_all").prop("checked"));
			self.editAssign(event);
		});
		
		$(".user_reimbursment").on("change", function(event) {
			self.engageAssign(event)
		});
		
		$("#save_user_reimbursment").on("click", function(event) {
			self.saveUserReimbursment(event);
		});
		$("#edit_user_reimbursment").on("click", function(event) {
			self.editAssign(event);
		});
	},
	newReimbursment: function(event) {
		event.preventDefault();
		
		document.location.href = "#reimbursment/new";
	},
	engageAssign: function(event) {
		var element = event.currentTarget;
		var blnChecked = element.checked;
		setTimeout(function() {
			element.checked = blnChecked;
		}, 100);
		this.editAssign(event);
	},
	editAssign: function(event) {
		event.preventDefault();
		
		$(".assign.edit_span").addClass("hide_me");
		$(".assign.edit_field").removeClass("hide_me");
		$("#user_reimbursment_form .clear_holder").removeClass("hide_me");
		
		$("#edit_user_reimbursment").addClass("hide_me");
		$("#save_user_reimbursment").removeClass("hide_me");
		
		$("#header_assign").css("background", "lightsalmon");
	},
	saveUserReimbursment: function(event) {
		var self = this;
		event.preventDefault();
		$("#save_user_reimbursment").addClass("hide_me");
		
		var url = "api/employee/reimbursments/save";
		var formData = $("#user_reimbursment_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#header_assign").css("background", "green");
					$("#edit_user_reimbursment").removeClass("hide_me");	
					setTimeout(function() {
						$("#header_assign").css("background", "#000033");
					}, 2500);
				}
			}
		});
	}
});