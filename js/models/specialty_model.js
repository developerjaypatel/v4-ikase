window.SpecialtySearch = Backbone.Model.extend({
	initialize: function(options) {
		//this.case_id = options.case_id;
	  },
	defaults : {
	},
	label: function () {
        return this.get("specialty");
    },
	search_terms: function () {
        return this.get("specialty") + this.get("description");
    },
	display: function () {
        return this.get("specialty");
    }
});

window.SpecialtySearchCollection = Backbone.Collection.extend({
	model: SpecialtySearch,
});