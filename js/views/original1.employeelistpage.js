window.EmployeeListPageView = Backbone.View.extend({

    initialize:function () {
		this.model.on("change", this.render, this);
    },

    render:function () {		
		var self = this;
		
		this.model.bind("reset", this.render, this);
		$(this.el).html(this.template({employees: this.model.toJSON()}));
		
		//$("#employee_list_table").tablesorter(); 
		
		var $rows = $('.employee_data_row');
		/*
		$('#searchList').keyup(function() {
			var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		
			$rows.show().filter(function() {
				var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
				return !~text.indexOf(val);
			}).hide();
		});
		*/
		
		setTimeout("listEmployees()", 100);

        return this;
		
		/*
		var columns = [{
		  name: "id", // The key of the model attribute
		  label: "ID", // The name to display in the header
		  editable: false, // By default every cell in a column is editable, but *ID* shouldn't be
		  // Defines a cell type, and ID is displayed as an integer without the ',' separating 1000s.
		  cell: Backgrid.IntegerCell.extend({
			orderSeparator: ''
		  })
		}, {
		  name: "firstName",
		  label: "First",
		  // The cell type can be a reference of a Backgrid.Cell subclass, any Backgrid.Cell subclass instances like *id* above, or a string
		  cell: "string" // This is converted to "StringCell" and a corresponding class in the Backgrid package namespace is looked up
		}, {
		  name: "lastName",
		  label: "Last",
		  // The cell type can be a reference of a Backgrid.Cell subclass, any Backgrid.Cell subclass instances like *id* above, or a string
		  cell: "string" // This is converted to "StringCell" and a corresponding class in the Backgrid package namespace is looked up
		}, {
		  name: "title",
		  label: "Title",
		  cell: "string"	//cell: "integer" // An integer cell is a number cell that displays humanized integers
		}, {
		  name: "reportCount",
		  label: "Reports",
		  cell: "number" // A cell type for floating point value, defaults to have a precision 2 decimal numbers
		}];
		
		//console.log(this.model);
		var employees_list = new EmployeeCollection();
		
		// Initialize a new Grid instance
		var grid = new Backgrid.Grid({
		  columns: columns,
		  collection: employees_list
		});
		console("gridded");
		
		// Render the grid and attach the root to your HTML document
		$("#employee_listing", this.el).append(grid.render().$el);
		
		// Fetch some countries from the url
		employees_list.fetch({reset: true});
		*/
    },
	/*
	events: {
        "keyup .search-query": 		"search",
		"keypress .search-query": 	"onkeypress",
		"click .sort_first":		"sortByFirst",
		"click .sort_first_desc":	"sortByFirstDesc",
		"click .sort_last":			"sortByLast",
		"click .sort_last_desc":	"sortByLastDesc"
    },
	
	sortByFirst: function() {
		var sortedEmployees = this.model.sortBy(function(employee) {
				return employee.get("firstName");
			});
		this.model.reset(sortedEmployees);
		var sortfirst = $('#sort_first');
		sortfirst.html("First Name &#9650;");
		sortfirst.removeClass('sort_first');
		sortfirst.addClass('sort_first_desc');
	},
	
	sortByFirstDesc: function() {
		var sortedEmployees = this.model.sortBy(function(employee) {
				return employee.get("firstName");
			});
		sortedEmployees.reverse();
		this.model.reset(sortedEmployees);
		var sortfirst = $('#sort_first');
		sortfirst.html("First Name &#9660;");
		sortfirst.removeClass('sort_first_desc');
		sortfirst.addClass('sort_first');
	},
	
	sortByLast: function() {
		var sortedEmployees = this.model.sortBy(function(employee) {
				return employee.get("lastName");
			});
		
		this.model.reset(sortedEmployees);
		var sortlast = $('#sort_last');
		sortlast.removeClass('sort_last');
		sortlast.addClass('sort_last_desc');
		sortlast.html("Last Name &#9650;");
		
	},
	
	sortByLastDesc: function() {
		var sortedEmployees = this.model.sortBy(function(employee) {
				return employee.get("lastName");
			});
		
		sortedEmployees.reverse();
		this.model.reset(sortedEmployees);
		var sortlast = $('#sort_last');
		sortlast.removeClass('sort_last_desc');
		sortlast.addClass('sort_last');
		sortlast.html("Last Name &#9660;");
		
	},
	
    search: function () {
        var key = $('#searchList').val();
        console.log('search ' + key);
        //this.model.findByName(key);		
		if (key.length > 0) {
			var filtered = CurrentEmployees.filter(function(employee) {
				var theindex = 0;
				
				var fullName = employee.get("fullName");
				fullName = fullName.toLowerCase();
				var theindex = fullName.indexOf(key);
				
				var title = employee.get("title");
				title = title.toLowerCase();
				var theworkindex = title.indexOf(key);
				
				return theindex > -1 || theworkindex > -1;
			});
		} else {
			var filtered = CurrentEmployees.filter(function(employee) {
				return true;
			});
		}
		//console.log(filtered);
		this.model.reset(filtered);
    },

    onkeypress: function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
        }
    }
	*/
});

function listEmployees() {
	var options = {
	  valueNames: [ 'full_name', 'last_name', 'title' ]
	};
	
	var userList = new List('employee_listing', options);
}