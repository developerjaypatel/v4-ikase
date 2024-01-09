window.lostincome_view = Backbone.View.extend({
	initialize:function () {
        //this.model.on("change", this.render, this);
		//this.model.on("add", this.render, this);
    },
	events: {
		"click #lostincome_done":			"doTimeouts"
	},
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "lostincome_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var mymodel = this.model.toJSON();
		if (mymodel.start_lost_date=="0000-00-00") {
			mymodel.start_lost_date = "";
		}
		if (mymodel.end_lost_date=="0000-00-00") {
			mymodel.end_lost_date = "";
		}
		try {
			$(this.el).html(this.template(mymodel));
		}
		catch(err) {
			alert(err);
			
			return "";
		}
	
		//gridster the parties_new tab
		setTimeout("gridsterIt(67)", 10);
        return this;
	},
	doTimeouts: function() {
		var master_class = "#lostincome_form ";
		$(master_class + ".form_label_vert").css("color", "white");
		$(master_class + ".form_label_vert").css("font-size", "1em");
		
		$(".lostincome .gridster_border").css("background", "none");
		$(".lostincome .gridster_border").css("border", "none");
		$(".lostincome .gridster_border").css("-webkit-box-shadow", "");
		$(".lostincome .gridster_border").css("box-shadow", "");
		$(".lostincome .form_label_vert").css("color", "white");
		$("#lostincome_form .form_label_vert").css("font-size", "1em");
		
		toggleFormEdit("lostincome");
	}
});
window.lostincome_listing_view = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click #new_lostincome_button":				"newLostIncome",
		"click .edit_lostincome":					"editLostIncome",
		"click .delete_icon":						"confirmdeleteLostIncome",
		"click .delete_yes":						"deleteLostIncome",
		"click .delete_no":							"canceldeleteLostIncome"
	},
    render:function () {		
		if (typeof this.template != "function") {
			var view = "lostincome_listing_view";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}
		var self = this;
		
		var mymodel = this.model.toJSON();
		var lostincomes = this.collection.toJSON();
		var total_lost_income = 0;
		_.each( lostincomes, function(lostincome) {
			if (lostincome.start_lost_date=="0000-00-00") {
				lostincome.start_lost_date = "";
			} else {
				lostincome.start_lost_date = moment(lostincome.start_lost_date).format("MM/DD/YYYY");
			}
			if (lostincome.end_lost_date=="0000-00-00") {
				lostincome.end_lost_date = "";
			}	 else {
				lostincome.end_lost_date = moment(lostincome.end_lost_date).format("MM/DD/YYYY");
			}	
			if (lostincome.end_lost_date==lostincome.start_lost_date) {
				lostincome.end_lost_date = "";
			}
			lostincome.perName = "";
			if (lostincome.wage > 0) {
				switch(lostincome.per) {
					case "H":
						lostincome.perName = "Hour";
						break;
					case "D":
						lostincome.perName = "Day";
						break;
					case "W":
						lostincome.perName = "Week";
						break;
					case "M":
						lostincome.perName = "Month";
						break;
					case "Y":
						lostincome.perName = "Year";
						break;
				}
				lostincome.perName = "$" + formatDollar(lostincome.wage) + " per " + lostincome.perName;
			}
			total_lost_income += Number(lostincome.amount);
		});
		
		$(this.el).html(this.template({lostincomes: lostincomes, page_title: "Lost Wages", total_lost_income: total_lost_income}));
		
		tableSortIt("lostincome_listing");
		
		return this;
    },
	editLostIncome: function(event) {
		event.preventDefault();
		var element_id = event.currentTarget.id;
		composeLostIncome(element_id);
	},
	newLostIncome: function(event) {
		event.preventDefault();
		composeLostIncome("lostincome_-1");
	},
	confirmdeleteLostIncome: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		$("#confirm_delete_id").val(id);
		var arrPosition = showDeleteConfirm(element, 450);	
		$("#confirm_delete").css({display: "none", top: arrPosition[0] - 250, left: arrPosition[1] - 500, position:'absolute'});
		$("#confirm_delete").fadeIn();
	},
	canceldeleteLostIncome: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteLostIncome: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "lostincome");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteLostIncome(event);
			$(".lostincome_data_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".lostincome_data_row_" + id).fadeOut();
			}, 2500);
		}
	}
});