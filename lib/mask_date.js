function mask(o, f) {
	setTimeout(function () {
        var v = f(o.value);
        if (v != o.value) {
            o.value = v;
        }
    }, 1);
}

function mdate(v) {
	//var v = obj.value;
	//var months = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
	
	//console.log("rr:" + v);
    var r = v.replace(/\D/g,"");
    //r = r.replace(/^0/,"");
	//console.log("rr2:" + r);
    if (r.length > 4) {
        // 6..10 digits. Format as 4+4
        r = r.replace(/^(\d\d)(\d{2})(\d{0,4}).*/,"$1/$2/$3");
    }
    else if (r.length > 2) {
        // 3..5 digits. Add (0XX..)
        r = r.replace(/^(\d\d)(\d{0,2})/,"$1/$2");
    }
    else if (r.length > 0){
        // 0..2 digits. Just add (XX
        //r = r.replace(/^(\d\d)(\d{0,2})/,"$1");
		if (r > 12) {
			r = "";
		}
    }
    return r;
}