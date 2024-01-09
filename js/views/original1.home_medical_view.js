window.home_medical_view = Backbone.View.extend({
    initialize:function () {  
    },
    events:{
       "click #homemedical_all_done":	"doTimeouts"
    },
    render:function () {
		var self = this;
		var arrDates = ["report_date", "prescription_date", "filling_fee_paid_date", "retainer_date", "lien_filled_date", "reviewed_date"];
		arrayLength = arrDates.length;
		for (var i = 0; i < arrayLength; i++) {
			var date_field = arrDates[i];
			if (this.model.get(date_field)!="") {
				if (this.model.get(date_field)=="0000-00-00") {
					this.model.set(date_field, "");
				} else {
					this.model.set(date_field, moment(this.model.get(date_field)).format('MM/DD/YYYY'));
				}
			}
		}
		//var case_id = this.model.get("case_id");
		try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "home_medical_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
        return this;
    },
	doTimeouts:function(event) {
		var self = this;
		$('.modal-dialog').animate({width:1050, marginLeft:"-550px"}, 1100, 'easeInSine', 
		function() {
			//run this after animation
			$('#homemedical').fadeIn(
			function() {
				$(".date_input").datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						//alert($input.val());
					}
				});
				/*$('#report_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						//alert($input.val());
					}
				});
				$('#prescription_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false,
					onChangeDateTime:function(dp,$input){
						//alert($input.val());
					}
				});
				$('#filling_fee_paid_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false
				});
				$('#retainer_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false
				});
				$('#lien_filled_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false
				});
				$('#reviewed_dateInput').datetimepicker({
					timepicker:false, 
					format:'m/d/Y',
					mask:false
				});
				*/
			});
		});
		
		homemedicalTokenInput();
		
		if (self.model.get("corporation_id") != null && self.model.get("corporation_id")!=-1) {
			$(".homemedical #provider_nameInput").tokenInput("add", {
				id: self.model.get("corporation_id"), 
				name: self.model.get("company_name"),
				tokenLimit:1
			});
		}
		
		initializeGoogleAutocomplete('homemedical');
	}
});
var homemedicalTokenInput = function() {
	var theme = {
	theme: "facebook", 
	tokenLimit:1,
	minChars:3, 
	noResultsText:"None Found...", 
	onAdd: function(item) {
		if (!isNaN(item.id)  && item.id!="") {
			//look up the employer info
			var thetype = "homemedical";
			var partie_corporation = new Corporation({ id: item.id, type:thetype });
			partie_corporation.fetch({
				success: function(data) {
					//now populate the appropriate fields
					/*
					var arrFields = $(".homemedical .address_class");
					var arrayLength = arrFields.length;
					for (var i = 0; i < arrayLength; i++) {
						var theid = arrFields[i].id;
						if (theid != "provider_nameInput") {
							$(".homemedical #" + theid).val(data.get(theid.replace("Input", "")));
						} else {
							$(".homemedical #" + theid.replace("Input", "Span")).html(data.get(theid.replace("Input", "")));
						}
					}
					*/
					$(".homemedical #corporation_id").val(item.id);
				}
			});
		}
		$("#provider_nameInput").val(item.name);
		$("#provider_nameInput").show();
		$(".token-input-list-facebook").hide();
		$(".token-input-dropdown-facebook").hide();
		$(".provider_info").fadeIn(function(){
			$("#full_addressInput").focus();
		});
		setTimeout(function() {
			$("#provider_nameInput").focus();
		}, 50);
	}};
	var thetype = "homemedical";
	//set the api call for data here
	$("#provider_nameInput").tokenInput("api/corporation/tokeninput/" + thetype, theme);
	$(".homemedical .token-input-list-facebook").css("margin-left", "0px");
	$(".homemedical .token-input-list-facebook").css("margin-top", "0px");
	$(".homemedical .token-input-list-facebook").css("width", "225px");
	$("#token-input-provider_nameInput").focus();
}