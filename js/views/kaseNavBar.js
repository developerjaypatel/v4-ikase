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
        "keyup #srch-term": "search",
        "keypress #srch-term": "onkeypress"
	},
	
	search: function (event) {
        var key = $('#srch-term').val();
        this.searchResults.fetch({reset: true, data: {name: key}});
        var self = this;
        setTimeout(function () {
            $('.dropdown').addClass('open');
        });
    },

    onkeypress: function (event) {
        if (event.keyCode === 13) { // enter key pressed
            event.preventDefault();
        }
    },

    select_menu: function(menuItem) {
        $('.nav li').removeClass('active');
        $('.' + menuItem).addClass('active');
    }

});