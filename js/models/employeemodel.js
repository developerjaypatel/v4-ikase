window.Employee = Backbone.Model.extend({

    urlRoot:"api/employees",

    initialize:function () {
        this.reports = new EmployeeCollection();
        this.reports.url = 'api/employees/' + this.id + '/reports';
    }

});


window.EmployeeCollection = Backbone.Collection.extend({
    model: Employee,
    url:"api/employees",
});

window.EmployeeType = Backbone.Model.extend({

    urlRoot:"api/employees_type",

    initialize:function () {
    
    }

});


window.EmployeeTypeCollection = Backbone.Collection.extend({
    model: EmployeeType,
    url:"api/employees_type",
});