window.company_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		"click #reimbursements": 					"editReimbursments",
		"click #payschedule": 						"editPayschedule",
		"click #taxes": 							"editTaxes"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "company_edit";
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
	editReimbursments: function(event) {
		window.Router.prototype.listReimbursments();
	},
	editPayschedule: function(event) {
		window.Router.prototype.editPayschedule();
		setTimeout(function() {
			$("#edit_payschedule").trigger("click");
		}, 777);
	},
	editTaxes: function(event) {
		window.Router.prototype.manageCompanyTaxes();
	}
});
window.company_taxes_edit = Backbone.View.extend({
	tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		
    },
	events:{
		"click #edit_general":			"editGeneral",
		"click #save_general":			"saveGeneral",
		"click #edit_federal":			"editFederal",
		"click #save_federal":			"saveFederal",
		"click #edit_state":			"editState",
		"click #save_state":			"saveState"
    },
	render: function () {
		if (typeof this.template != "function") {
			var view = "company_taxes_edit";
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
		$('.general#start_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
		
		$('.federal#effective_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
		
		$('.state#state_effective_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
		
		$('.state#unemployment_effective_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
		
		$('.state#training_effective_dateField').datetimepicker({ 
			onGenerate:function( ct ){
				//jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				//  .addClass('xdsoft_disabled');
			},
			//weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			maxDate: 0, 
			timepicker: false,
			format: 'm/d/Y',
			//value: event_dateandtime,
			//allowTimes:workingWeekTimes,
			//step:30,
			  onChangeDateTime:function(dp,$input){
				  //self.clearErrorWarning();
				  //self.setupCalculationOptions();
			  }
		});
	},
	editGeneral: function(event) {
		event.preventDefault();
		
		$(".general.edit_span").addClass("hide_me");
		$(".general.edit_field").removeClass("hide_me");
		$("#general_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_general").addClass("hide_me");
		$("#save_general").removeClass("hide_me");
		
		$("#header_general").css("background", "lightsalmon");
	},
	saveGeneral:function(event) {
		event.preventDefault();
		event.preventDefault();
		$("#save_general").addClass("hide_me");
		
		var url = "api/company/general/save";
		var formData = $("#general_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".general.edit_field").addClass("hide_me");
					$("#general_info_form .clear_holder").addClass("hide_me");
					$("#header_general").css("background", "green");
					var edit_fields = $(".general.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var val = element.value;
						var subst = val;
						
						if (span_id=="company_typeSpan") {
							switch(val) {
								case "sole":
									subst = "Sole Proprietor";
									break;
								case "c":
									subst = "C Corporation";
									break;
								case "s":
									subst = "S Corporation";
									break;
								case "llc":
									subst = "LLC";
									break;
								case "p":
									subst = "Partnership";
									break;
								case "other":
									subst = "Other";
									break;
							}
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_general").removeClass("hide_me");	
					$(".general.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_general").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	editFederal: function(event) {
		event.preventDefault();
		
		$(".federal.edit_span").addClass("hide_me");
		$(".federal.edit_field").removeClass("hide_me");
		$("#federal_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_federal").addClass("hide_me");
		$("#save_federal").removeClass("hide_me");
		
		$("#header_federal").css("background", "lightsalmon");
	},
	saveFederal:function(event) {
		event.preventDefault();
		event.preventDefault();
		$("#save_federal").addClass("hide_me");
		
		var url = "api/company/federal/save";
		var formData = $("#federal_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".federal.edit_field").addClass("hide_me");
					$("#federal_info_form .clear_holder").addClass("hide_me");
					$("#header_federal").css("background", "green");
					var edit_fields = $(".federal.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var val = element.value;
						var subst = val;
						
						if (span_id=="filing_typeSpan") {
							switch(val) {
								case "941_M":
									subst = "941 Filer, Monthly Depositor";
									break;
								case "941_S":
									subst = "941 Filer, Semi-weekly Depositor";
									break;
								case "941_Q":
									subst = "941 Filer, Quarterly Depositor";
									break;
								case "944_M":
									subst = "944 Filer, Monthly Depositor";
									break;
								case "944_S":
									subst = "944 Filer, Semi-weekly Depositor";
									break;
								case "944_A":
									subst = "944 Filer, Annual Depositor";
									break;
							}
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_federal").removeClass("hide_me");	
					$(".federal.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_federal").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	},
	editState: function(event) {
		event.preventDefault();
		
		$(".state.edit_span").addClass("hide_me");
		$(".state.edit_field").removeClass("hide_me");
		$("#state_info_form .clear_holder").removeClass("hide_me");
		
		$("#edit_state").addClass("hide_me");
		$("#save_state").removeClass("hide_me");
		
		$("#header_state").css("background", "lightsalmon");
	},
	saveState:function(event) {
		event.preventDefault();
		event.preventDefault();
		$("#save_state").addClass("hide_me");
		
		var url = "api/company/state/save";
		var formData = $("#state_info_form").serialize();
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formData,
			success:function (data) {
				if(!data.success) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".state.edit_field").addClass("hide_me");
					$("#state_info_form .clear_holder").addClass("hide_me");
					$("#header_state").css("background", "green");
					var edit_fields = $(".state.edit_field");
					var arrLength = edit_fields.length;
					for(var i = 0; i < arrLength; i++) {
						var element = edit_fields[i];
						var element_id = element.id;
						var span_id = element_id.replace("Field", "Span");
						var val = element.value;
						var subst = val;
						
						if (span_id=="deposit_scheduleSpan") {
							switch(val) {
								case "M":
									subst = "Monthly";
									break;
								case "S":
									subst = "Semi-weekly";
									break;
								case "Q":
									subst = "Quarterly";
									break;
							}
						}
						$("#" + span_id).html(subst);
					}
					$("#edit_state").removeClass("hide_me");	
					$(".state.edit_span").removeClass("hide_me");
					setTimeout(function() {
						$("#header_state").css("background", "darkslategray");
					}, 2500);
				}
			}
		});
	}
});