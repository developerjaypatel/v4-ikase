var kaseOccurencesView = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
		/*
		this.collection.bind('add', this.addOne);
		this.collection.bind('change', this.change);            
		this.collection.bind('destroy', this.destroy);
		*/
		//this.occurenceView = new kaseOccurenceView({model: this.model});            
		//this.occurenceView = new kaseOccurenceDialogView({model: this.model});
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,basicWeek,basicDay'
			},
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			select: this.select,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			}
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		this.$el.fullCalendar(options);
		this.addAll();
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		var event_id = fcEvent.id;
		var case_id = this.model.get("id");
		var case_number = this.model.get("case_number");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + encodeURIComponent(case_number);
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		var url = 'api/events/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = this.formatDate(fcEvent.start);
		} else {
			var thedate = this.formatDate(fcEvent.datetimepicker);
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}        
});
var kaseOccurencesSummaryView = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 

		this.collection.bind('reset', this.addAll);
		this.collection.bind('add', this.addOne);
		this.collection.bind('change', this.change);            
		this.collection.bind('destroy', this.destroy);
		
		//this.occurenceView = new kaseOccurenceView({model: this.model});            
		this.occurenceView = new kaseOccurenceDialogView({model: this.model});
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next',
				center: 'title',
			},
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			select: this.select,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			}
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		this.$el.fullCalendar(options);
		this.addAll();
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},
	eventClick: function(fcEvent) {
		
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("id"));
		this.occurenceView.render();
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		var url = 'api/events/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = this.formatDate(fcEvent.start);
		} else {
			var thedate = this.formatDate(fcEvent.datetimepicker);
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}        
});
var kaseOccurenceDialogView = Backbone.View.extend({
	initialize: function() {
		_.bindAll(this);           
	},
	render: function () {
		$(this.el).html(this.template(this.model.toJSON()));
		setTimeout("gridsterIt(3)", 1);
		/*
		var buttons = {'Save': this.save};
		if (!this.model.isNew()) {
			_.extend(buttons, {'Delete': this.destroy});
		}
		*/
		//alert("here");
		/*
		_.extend(buttons, {'Cancel': this.close});            
		this.$el.dialog({
			modal: true,
			title: (this.model.isNew() ? 'New' : 'Edit') + ' Event',
			buttons: buttons,
			open: this.open,
			width:330,
			height:600,
			closeText: "Close"
		});
		
		$(".ui-dialog").addClass("glass_header_no_padding_opaque_darker");
		$(".ui-dialog-titlebar").addClass("glass_header_no_padding_opaque");
		$(".ui-dialog-buttonpane").addClass("glass_header_no_padding_opaque");
		$(".ui-dialog-titlebar-close").html("<span style='margin-top:-3px; font-weight:bold'>&times;</style>");
		
		//setup the datetimepicker
		datepickIt('#start_date');
		*/
		return this;
	},        
	
	setID: function(id) {
		this.model.set({id: id});
		var anevent = this.collection.get(id);
		if (typeof anevent == "undefined") {
			this.collection.add(this.model);
		} else {
			//console.log(this.collection);
			this.collection.remove(anevent);
			this.collection.add(this.model);
		}
		//this.$el.fullCalendar( 'refetchEvents' );  
	},
	
	open: function() {
		this.$('#event_name').val(this.model.get('event_name'));
		this.$('#color').val(this.model.get('color'));
		//var thedate = this.formatUSDate(new Date(this.model.get('start')));
		var thedate = moment(this.model.get('event_dateandtime')).format('MM/DD/YYYY h:mA');
		this.$('#start_date').val(thedate);
	},        
	save: function() {
		var self = this;
		this.model.set({'title': this.$('#event_name').val(), 'start': this.$('#start_date').val(), 'color': this.$('#color').val()});
		var formValues = $('#event_form').serialize();
		
		if (this.model.isNew()) {
			//this.collection.create(this.model, {success: this.close});
			var url = 'api/events/add';
			
		} else {
			var url = 'api/events/update';
		}
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			context: this,
			success:function (data) {
			   
				if(data.error) {  // If there is an error, show the error messages
					$('.alert-error').text(data.error.text).show();
				}
				this.setID(data.id);
			}
		});
		this.close();
	},
	close: function() {
		this.$el.dialog('close');
	},
	destroy: function() {
		this.model.destroy({success: this.close});
	}    
});

var kaseOccurenceView = Backbone.View.extend({
	el: $('#eventDialog'),
	initialize: function() {
		_.bindAll(this);           
	},
	render: function() {
		
		var buttons = {'Save': this.save};
		if (!this.model.isNew()) {
			_.extend(buttons, {'Delete': this.destroy});
		}
		_.extend(buttons, {'Cancel': this.close});            
		this.$el.dialog({
			modal: true,
			title: (this.model.isNew() ? 'New' : 'Edit') + ' Event',
			buttons: buttons,
			open: this.open,
			width:330,
			height:600,
			closeText: "Close"
		});
		
		$(".ui-dialog-titlebar-close").html("<span style='margin-top:-3px; font-weight:bold'>&times;</style>")
		
		//setup the datetimepicker
		datepickIt('#start_date');
		
		//add the case details
		$("#eventDialog #case_number").val(this.model.get("case_number"));
		$("#eventDialog #id").val(this.model.get("id"));
		$("#eventDialog #case_id").val(this.model.get("case_id"));
		return this;
	},        
	
	setID: function(id) {
		this.model.set({id: id});
		var anevent = this.collection.get(id);
		if (typeof anevent == "undefined") {
			this.collection.add(this.model);
		} else {
			this.collection.get(id) = this.model;
		}
	},
	
	open: function() {
		this.$('#event_name').val(this.model.get('event_name'));
		this.$('#color').val(this.model.get('color'));
		//var thedate = this.formatUSDate(new Date(this.model.get('start')));
		var thedate = moment(this.model.get('event_dateandtime')).format('MM/DD/YYYY h:mA');
		this.$('#start_date').val(thedate);
	},        
	save: function() {
		var self = this;
		this.model.set({'title': this.$('#event_name').val(), 'start': this.$('#start_date').val(), 'color': this.$('#color').val()});
		var formValues = $('#event_form').serialize();
		
		if (this.model.isNew()) {
			//this.collection.create(this.model, {success: this.close});
			var url = 'api/events/add';
			
		} else {
			var url = 'api/events/update';
		}
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			context: this,
			success:function (data) {
			   
				if(data.error) {  // If there is an error, show the error messages
					$('.alert-error').text(data.error.text).show();
				}
				this.setID(data.id);
			}
		});
		this.close();
	},
	close: function() {
		this.$el.dialog('close');
	},
	destroy: function() {
		this.model.destroy({success: this.close});
	}        
});