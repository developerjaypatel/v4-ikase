var AutoCompleteItemKaseView = Backbone.View.extend({
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
		var search_term = this.options.parent.current_input.val();
		var the_label = highLight(this.model.label(), search_term);
		//the_label = the_label.replaceAll(" ", "&nbsp;");
        this.$el.html(_.template(this.template, {
            "label": the_label
        }));
        return this;
    },

    select: function () {
        this.options.parent.hide().select(this.model);
        return false;
    }

});

var AutoCompleteKaseView = Backbone.View.extend({
    tagName: "ul",
    itemView: AutoCompleteItemKaseView,
    className: "autocomplete_kase",
	current_input:"",
    wait: 300,
    queryParameter: "query",
    minKeywordLength: 2,
    currentText: "",

    initialize: function (options) {
        _.extend(this, options);
        this.filter = _.debounce(this.filter, this.wait);
    },

    render: function () {
        // disable the native auto complete functionality
        this.input.attr("autocomplete", "off");

        this.$el.width(this.input.outerWidth());
				
        this.input
            .keyup(this.keyup.bind(this))
            .keydown(this.keydown.bind(this));
			
		$("#stack_listing").after(this.$el);
		
		return this;
    },

    keydown: function () {
        if (event.keyCode == 38) return this.move(-1);
        if (event.keyCode == 40) return this.move(+1);
        if (event.keyCode == 13) return this.onEnter();
        if (event.keyCode == 27) return this.hide();
    },

    keyup: function () {
		this.current_input = $("#" + event.currentTarget.id);
		var rect = event.currentTarget.getBoundingClientRect();
		
		var scrollTop = document.documentElement.scrollTop?
						document.documentElement.scrollTop:document.body.scrollTop;
		var scrollLeft = document.documentElement.scrollLeft?                   
						 document.documentElement.scrollLeft:document.body.scrollLeft;
		//elementTop = rect.top+scrollTop + 20;
		elementTop = rect.top+scrollTop;
		//elementLeft = rect.left+scrollLeft;
		elementLeft = rect.left;
		
		//move the autocomplete
		$("ul.autocomplete_kase").css("left", elementLeft - 280);
		$("ul.autocomplete_kase").css("top", elementTop - 37);
		
        var keyword = this.current_input.val();
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
				//var theindex = model.label().toLowerCase().indexOf(keyword.toLowerCase());
				var theindex = _.values(model.toJSON()).toString().toLowerCase().indexOf(keyword.toLowerCase());
				var blnReturn = (theindex > -1);
				if (blnReturn) {
					//console.log(model);
				}
                return blnReturn
            }), keyword);
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
		var display = model.display();
        this.current_input.val(display);
        this.currentText = label;
		
		//get the current id
		var arrID = this.current_input[0].id.split("_");
		var theid = arrID[arrID.length - 1];
		
		//save the id
		$("#stack_case_id_" + theid).val(model.get("uuid"));
		//show the two drop downs and the check box
		$("#stack_type_" + theid).fadeIn();
		$("#stack_category_" + theid).fadeIn();
		
		$("#notify_attorney_" + theid).fadeIn();
		
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