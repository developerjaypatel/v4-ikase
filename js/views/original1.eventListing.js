window.eventListingView = Backbone.View.extend({

    initialize:function () {
        this.model.on("change", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({events: this.model.toJSON()}));
		
		setTimeout("tableSortIt()", 100);
		//setTimeout("listKases()", 100);
		
		return this;
    }

});

/*function listKases() {
	var options = {
	  valueNames: [ 'kase_number', 'kase_title', 'kase_status', 'kase_date' ]
	};
	
	var kaseList = new List('kase_listing', options);
}
*/

function tableSortIt () {
	$("#event_listing")
		.tablesorter({widthFixed: true, widgets: ['zebra']}) 
	    .tablesorterPager({container: $("#pager")}); 
}
