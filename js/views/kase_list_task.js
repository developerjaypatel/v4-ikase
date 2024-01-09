var show_link_id;
window.kase_list_task_view = Backbone.View.extend({

    initialize:function () {
   /*
        this.model.on("change", this.render, this);
		this.model.bind("reset", this.render, this);
		this.model.bind("remove", this.render, this);
	*/
    },
	events: {
        "mouseover .kase_link_left":				"showNewWindowLink",
		"mouseout  .kase_link_left":				"hideNewWindowLink",
		"mouseover .kase_windowlink_left":		"freezeNewWindowLink",
		"mouseout  .kase_windowlink_left":		"hideNewWindowLink"
	},
	

    render:function () {		
		var self = this;
		
		var recent_tasks = new Backbone.Collection(this.model.first(5));
		$(this.el).html(this.template({kases: recent_tasks.toJSON()}));
		
		//setTimeout("tableSortIt()", 100);
		setTimeout("listKaseCategories()", 200);
		
		return this;
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
	  valueNames: [ 'title', 'due_date' ]
	};
	
	var kaseList = new List('kase_task_listing', options);
}