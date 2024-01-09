window.medical_specialties_select = Backbone.View.extend({
    initialize:function () {

    },

    events:{
       
    },

    render:function () {
		var specialties = this.collection.toJSON();
        $(this.el).html(this.template({specialties: specialties}));
        return this;
    }
});
