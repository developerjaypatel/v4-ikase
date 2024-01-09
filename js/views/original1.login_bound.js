var LoginView = Backbone.View.extend({
    viewBindings: {
		 emailEditing: {
            visible: '#email_message'
        },
		passwordEditing: {
            visible: '#password_message'
        },
        stringValue: {
			change: function(val) {
				this.checkFull();
			},
            text: 'td.stringValue',
            value: [
                'input.stringValue',
                {selector: 'input.stringValueKeyup', event: 'keyup'}
            ]
        },
        passwordValue: {
			change: function(val) {
				this.checkFull();
			},
            text: 'td.passwordValue',
			value: [
                'input.passwordValue',
                {selector: 'input.passwordValueKeyup', event: 'keyup'}
            ]
        }
    },
	checkFull: function() {
		console.log("email:" + this.vmodel.get("stringValue"));
		console.log("pass:" + this.vmodel.get("passwordValue"));
		if (this.vmodel.get("stringValue")=="") {
			this.vmodel.set('emailEditing', true);
		} else {
			this.vmodel.set('emailEditing', false);
		}
		if (this.vmodel.get("passwordValue")=="") {
			this.vmodel.set('passwordEditing', true);
		} else {
			this.vmodel.set('passwordEditing', false);
		}
	},
    render: function() {
        this.vmodel = new Backbone.Model();
        Bindem.on.call(this, this.viewBindings, {model: this.vmodel});
        
		this.vmodel.set({stringValue: '', passwordValue: '', emailEditing: true, passwordEditing: true});
    }
});

var view = new LoginView({el: $('.liveExample')});
view.render();