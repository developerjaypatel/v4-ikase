var AutoCompleteItemWorkerView = Backbone.View.extend({
    tagName: "li",
    template: '<a href="#" style=""><%= label %></a>',

    events: {
        "click": "select"
    },
    
    initialize: function(options) {
        this.options = options;
    },

    render: function () {
		//what are we looking for
		var search_term = this.options.parent.input.val();
		var the_label = "";
		if (search_term!="") {
			the_label = this.model.label().replaceAll(search_term, "<~>" + search_term + "</~>");
			the_label = the_label.replaceAll(search_term.toUpperCase(), "<~>" + search_term.toUpperCase() + "</~>");
			the_label = the_label.replaceAll("<~", "<span class='autocompleted'");
			the_label = the_label.replaceAll("/~", "/span");
		} else {
			the_label = this.model.label();
		}
        this.$el.html(_.template(this.template, {
            "label": the_label,
			"address_phone": this.model.address_phone()
			
        }));
        return this;
    },

    select: function () {
        this.options.parent.hide().select(this.model);
        return false;
    }

});
var AutoCompleteItemWorkerEventView = Backbone.View.extend({
    tagName: "li",
    template: '<a href="#" style=""><%= label %></a>',

    events: {
        "click": "select"
    },
    
    initialize: function(options) {
        this.options = options;
    },

    render: function () {
		//what are we looking for
		var search_term = this.options.parent.input.val();
		var the_label = this.model.label().replaceAll(search_term, "<~>" + search_term + "</~>");
		the_label = the_label.replaceAll(search_term.toUpperCase(), "<~>" + search_term.toUpperCase() + "</~>");
		the_label = the_label.replaceAll("<~", "<span class='autocompleted'");
		the_label = the_label.replaceAll("/~", "/span");
        this.$el.html(_.template(this.template, {
            "label": the_label,
			"address_phone": this.model.address_phone()
			
        }));
        return this;
    },

    select: function () {
        this.options.parent.hide().select(this.model);
        return false;
    }

});

var AutoCompleteWorkerView = Backbone.View.extend({
    tagName: "ul",
    itemView: AutoCompleteItemWorkerView,
    className: "autocomplete_worker",

    wait: 300,
    queryParameter: "query",
    minKeywordLength: 0,
    currentText: "",

    initialize: function (options) {
        _.extend(this, options);
        this.filter = _.debounce(this.filter, this.wait);
    },

    render: function () {
        // disable the native auto complete functionality
        this.input.attr("autocomplete", "off");

        this.$el.width(this.input.outerWidth() + 100);
				
        this.input
            .keyup(this.keyup.bind(this))
            .keydown(this.keydown.bind(this))
			.dblclick(this.dblclick.bind(this));
			
		$("#workerInput").after(this.$el);
        return this;
    },

    keydown: function () {
        if (event.keyCode == 38) return this.move(-1);
        if (event.keyCode == 40) return this.move(+1);
        if (event.keyCode == 13) return this.onEnter();
        if (event.keyCode == 27) return this.hide();
    },

    keyup: function () {
        var keyword = this.input.val();
        if (this.isChanged(keyword)) {
            if (this.isValid(keyword)) {
                this.filter(keyword);
            } else {
                this.hide()
            }
        }
    },
	
	dblclick: function () {
        this.list();
    },

    filter: function (keyword) {
		this.loadResult(this.model.filter(function (model) {
				//var theindex = model.label().indexOf(keyword);
				var theindex = model.label().toLowerCase().indexOf(keyword.toLowerCase());
				//var theindex = _.values(model.toJSON()).toLowerCase().indexOf(keyword.toLowerCase());
				var blnReturn = (theindex > -1);
				if (blnReturn) {
					//console.log(model);
				}
                return blnReturn
            }), keyword);
		return;
    },
	list: function () {
		this.loadResult(this.model.filter(function (model) {
				return true
            }), "");
		return;
    },

    isValid: function (keyword) {
        return keyword.length > this.minKeywordLength
    },

    isChanged: function (keyword) {
        return this.currentText != keyword;
    },

    move: function (position) {
        var current = this.$el.children(".active"),
            siblings = this.$el.children(),
            index = current.index() + position;
        if (siblings.eq(index).length) {
            current.removeClass("active");
            siblings.eq(index).addClass("active");
        }
        return false;
    },

    onEnter: function () {
        this.$el.children(".active").click();
        return false;
    },

    loadResult: function (model, keyword) {
        this.currentText = keyword;
        this.show().reset();
        if (model.length) {
            _.forEach(model, this.addItem, this);
            this.show();
        } else {
            this.hide();
        }
    },

    addItem: function (model) {
        this.$el.append(new this.itemView({
            model: model,
            parent: this
        }).render().$el);
    },

    select: function (model) {
        var label = model.label();
        this.input.val(label);
        this.currentText = label;
		
		
        this.onSelect(model);
    },

    reset: function () {
        this.$el.empty();
        return this;
    },

    hide: function () {
        this.$el.hide();
        return this;
    },

    show: function () {
        this.$el.show();
        return this;
    },

    // callback definitions
    onSelect: function () {
	}

});
var AutoCompleteWorkerEventView = Backbone.View.extend({
    tagName: "ul",
    itemView: AutoCompleteItemWorkerEventView,
    className: "autocomplete_worker_event",

    wait: 300,
    queryParameter: "query",
    minKeywordLength: 1,
    currentText: "",

    initialize: function (options) {
        _.extend(this, options);
        this.filter = _.debounce(this.filter, this.wait);
    },

    render: function () {
        // disable the native auto complete functionality
        this.input.attr("autocomplete", "off");

        this.$el.width(this.input.outerWidth() + 100);
				
        this.input
            .keyup(this.keyup.bind(this))
            .keydown(this.keydown.bind(this));
			
		$("#assigneeInput").after(this.$el);
        return this;
    },

    keydown: function () {
        if (event.keyCode == 38) return this.move(-1);
        if (event.keyCode == 40) return this.move(+1);
        if (event.keyCode == 13) return this.onEnter();
        if (event.keyCode == 27) return this.hide();
    },

    keyup: function () {
        var keyword = this.input.val();
        if (this.isChanged(keyword)) {
            if (this.isValid(keyword)) {
                this.filter(keyword);
            } else {
                this.hide()
            }
        }
    },

    filter: function (keyword) {
		this.loadResult(this.model.filter(function (model) {
				//var theindex = model.label().indexOf(keyword);
				var theindex = model.label().toLowerCase().indexOf(keyword.toLowerCase());
				//var theindex = _.values(model.toJSON()).toLowerCase().indexOf(keyword.toLowerCase());
				var blnReturn = (theindex > -1);
				if (blnReturn) {
					//console.log(model);
				}
                return blnReturn
            }), keyword);
		return;
		/*
        if (this.model.url) {

            var parameters = {};
            parameters[this.queryParameter] = keyword;

            this.model.fetch({
                success: function () {
                    this.loadResult(this.model.models, keyword);
                }.bind(this),
                data: parameters
            });

        } else {
            this.loadResult(this.model.filter(function (model) {
                return model.label().indexOf(keyword) > -1
            }), keyword);
        }
		*/
    },

    isValid: function (keyword) {
        return keyword.length > this.minKeywordLength
    },

    isChanged: function (keyword) {
        return this.currentText != keyword;
    },

    move: function (position) {
        var current = this.$el.children(".active"),
            siblings = this.$el.children(),
            index = current.index() + position;
        if (siblings.eq(index).length) {
            current.removeClass("active");
            siblings.eq(index).addClass("active");
        }
        return false;
    },

    onEnter: function () {
        this.$el.children(".active").click();
        return false;
    },

    loadResult: function (model, keyword) {
        this.currentText = keyword;
        this.show().reset();
        if (model.length) {
            _.forEach(model, this.addItem, this);
            this.show();
        } else {
            this.hide();
        }
    },

    addItem: function (model) {
        this.$el.append(new this.itemView({
            model: model,
            parent: this
        }).render().$el);
    },

    select: function (model) {
        var label = model.label();
        this.input.val(label);
        this.currentText = label;
		
		
        this.onSelect(model);
    },

    reset: function () {
        this.$el.empty();
        return this;
    },

    hide: function () {
        this.$el.hide();
        return this;
    },

    show: function () {
        this.$el.show();
        return this;
    },

    // callback definitions
    onSelect: function () {
	}

});