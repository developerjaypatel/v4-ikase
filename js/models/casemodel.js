window.Kase = Backbone.Model.extend({

    urlRoot:"api/kases",

    initialize:function () {
        this.reports = new KaseCollection();
        this.reports.url = 'api/kases/' + this.id + '/reports';
    }

});


window.KaseCollection = Backbone.Collection.extend({

    model: Kase,

    url:"api/kases",

    findByName:function (key) {
        var url = (key == '') ? 'api/kases' : "api/kases/search/" + key;
        console.log('findByName: ' + key);
        var self = this;
        $.ajax({
            url:url,
            dataType:"json",
            success:function (data) {
                console.log("search success: " + data.length);
                self.reset(data);
            }
        });
    }

});