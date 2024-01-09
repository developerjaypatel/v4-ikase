var show_link_id;
window.kase_list_category_view = Backbone.View.extend({

    initialize:function () {
   /*
        this.model.on("change", this.render, this);
		this.model.bind("reset", this.render, this);
		this.model.bind("remove", this.render, this);
	*/
    },
	events: {
        "mouseover .kase_link_left":			"showNewWindowLink",
		"mouseout  .kase_link_left":			"hideNewWindowLink",
		"mouseover .kase_windowlink_left":		"freezeNewWindowLink",
		"mouseout  .kase_windowlink_left":		"hideNewWindowLink",
		"click .kase_modal":					"editKase"
	},
	
    render:function () {		
		var self = this;
		if (typeof this.template != "function") {
			var view = "kase_list_category_view";
			var extension = "php";
			this.model.set("holder", "kases_recent");
			loadTemplate(view, extension, this);
			return "";
	   	}
		var recent_kases = new Backbone.Collection(this.model.first(5));
		$(this.el).html(this.template({kases: recent_kases.toJSON()}));
		
		//setTimeout("tableSortIt()", 100);
		setTimeout("listKaseCategories()", 100);
		
		return this;
    },
	editKase:function (event) {
		var element = event.currentTarget;
		console.log(element.id);
        composeKaseEdit(element.id)
    },
	showNewWindowLink: function(event) {
		event.preventDefault();
		clearTimeout(show_link_id);
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		$(".kase_windowlink_left").fadeOut("slow");
		
		setTimeout(function() {
			$("#kase_windowlink_left_" + theid).fadeIn("slow");
		}, 100);
	},
	freezeNewWindowLink: function(event) {
		event.preventDefault();
		clearTimeout(show_link_id);
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		$("#kase_windowlink_left_" + theid).show();
	},
	hideNewWindowLink: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[1];
		show_link_id = setTimeout(function() {
			
			$(".kase_windowlink_left").fadeOut("slow");
			
		}, 1500);
		
	}

});

function listKaseCategories() {
	var options = {
	  valueNames: [ 'kase_number', 'kase_title' ]
	};
	
	var kaseList = new List('kase_category_listing', options);
}