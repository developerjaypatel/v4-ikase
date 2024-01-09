$(function () {
    var _alphabets = $('.alphabet > a');
    var _contentRows = $('#rolodex_listing tbody tr');

    _alphabets.click(function () {
        var _letter = $(this), _text = $(this).text(), _count = 0;

        _alphabets.removeClass("active");
        _letter.addClass("active");

        _contentRows.hide();
        _contentRows.each(function (i) {
            var _cellText = $(this).children('td').eq(0).text();
            if (RegExp('^' + _text).test(_cellText)) {
                _count += 1;
                $(this).fadeIn(400);
            }
        });
    });
});