// JavaScript Document
var EmployeeGrid = new bbGrid.View({        
        container: $('#bbGrid-container'),        
        collection: EmployeeCollection,
        colModel: [{ title: 'ID', name: 'id', sorttype: 'number' },
                   { title: 'Full Name', name: 'fullName' },
                   { title: 'Title', name: 'title' },
                   { title: 'Reports', name: 'reportCount' } ]
    });
console.log("employee grid created");