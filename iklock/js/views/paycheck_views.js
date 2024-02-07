window.paychecks_list = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "paychecks_list";
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
		var user_id = "";
		if ($("#user_id").length > 0) {
			user_id = $("#user_id").val();
		}
		
		if (user_id!="") {
			$(".new_paycheck").on("click", function(event) {
				self.newPaycheck(event);
			});
		} else {
			$(".new_paycheck").hide();
		}
		$(".delete_paycheck").on("click", function(event) {
			self.deletePaycheck(event);
		});
	},
	newPaycheck: function(event) {
		event.preventDefault();
		var user_id = $("#user_id").val();
		document.location.href = "#paycheck/create/" + user_id;
	},
	deletePaycheck: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var check_id = arrID[arrID.length - 1];
		$("#" + element_id).addClass("hide_me");
		
		var url = "api/paycheck/delete";
		var formData = "check_id=" + check_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$("#check_row_" + check_id).css("background", "red");
					
					setTimeout(function() {
						$("#check_row_" + check_id).fadeOut();
					}, 2500);
				}
			}
		});
	}
});
window.paycheck_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "paycheck_edit";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);

		return this;
    },
	doTimeouts: function() {
		var self = this;
		$("#save_check").on("click", function(event) {
			self.saveCheck(event);
		});
		
		$('.check#pay_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  }
		});
		
		$('#pay_period_start_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false,  
			timepicker: false,
			format: 'm/d/Y',
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  },
			  onShow:function( ct ){
			   this.setOptions({
				maxDate:jQuery('#pay_period_end_dateField').val()?jQuery('#pay_period_end_dateField').val():false
			   })
			  },
		});
		$('#pay_period_end_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false,  
			timepicker: false,
			format: 'm/d/Y',
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  },
			  onShow:function( ct ){
			   this.setOptions({
				minDate:jQuery('#pay_period_start_dateField').val()?jQuery('#pay_period_start_dateField').val():false
			   })
			  },
		});
	},
	saveCheck: function(event) {
		var self = this;
		event.preventDefault();
		$("#save_check").addClass("hide_me");
		
		var url = "api/paycheck/save";
		var formData = $("#check_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".check.edit_field").addClass("hide_me");
					$("#header_check").css("background", "green");
					
					var edit_fields = $(".check.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						if(span_id!="") {
							$(".check#" + span_id).html(element.value);
						}
					}
					$("#edit_check").removeClass("hide_me");	
					$(".check.edit_span").removeClass("hide_me");
					
					setTimeout(function() {
						$("#header_check").css("background", "#000033");
					}, 2500);
				}
			}
		});
	}
});
var blnContractorSaving = false;
window.contractors_paychecks_create = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		//don't work when i pass the html as whole, i think because there is no template..
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "contractors_paychecks_create";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		var self = this;
		
		$(this.el).html(this.model.get("html"));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);

		return this;
    },
	doTimeouts: function() {
		var self = this;
		
		$(".save_checks").on("click", function(event) {
			self.saveContractorsChecks(event);
		});
		
		$('.check#pay_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  }
		});
		
		$('#pay_period_start_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false,  
			timepicker: false,
			format: 'm/d/Y',
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  },
			  onShow:function( ct ){
			   this.setOptions({
				maxDate:jQuery('#pay_period_end_dateField').val()?jQuery('#pay_period_end_dateField').val():false
			   })
			  },
		});
		$('#pay_period_end_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
			},
			weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false,  
			timepicker: false,
			format: 'm/d/Y',
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
			  },
			  onShow:function( ct ){
			   this.setOptions({
				minDate:jQuery('#pay_period_start_dateField').val()?jQuery('#pay_period_start_dateField').val():false
			   })
			  },
		});
	},
	saveContractorsChecks: function(event) {
		event.preventDefault();
		
		var self = this;
		
		if (blnContractorSaving) {
			return;
		}
		
		blnContractorSaving = true;
		
		event.preventDefault();
		$(".save_checks").addClass("hide_me");
		
		var url = "api/paycheck/contractors/save";
		var formData = $("#contractors_checks_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					blnContractorSaving = false;
					
					$("#header_contractors_checks").css("background", "green");
					
					$(".save_checks").removeClass("hide_me");
					
					setTimeout(function() {
						$("#header_contractors_checks").css("background", "#000033");
					}, 2500);
				}
			}
		});
	}
});