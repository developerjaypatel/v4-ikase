window.kaseNavBarView = Backbone.View.extend({

    initialize: function () {
        /*
		this.searchResults = new EmployeeCollection();
        this.searchresultsView = new EmployeeListView({model: this.searchResults, className: 'dropdown-menu'});
		*/
    },

    render: function () {
        $(this.el).html(this.template());
        //$('.navbar-search', this.el).append(this.searchresultsView.render().el);
        return this;
    },

    events: {
        //"keyup .search-query": "search",
        //"keypress .search-query": "onkeypress"
	}

});