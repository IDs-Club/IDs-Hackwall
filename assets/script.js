(function () {
    var screenWidth = function () { return Math.max(document.documentElement.clientWidth, document.body.clientWidth); },
        screenHeight = function () { return Math.max(document.documentElement.clientHeight, document.body.clientHeight); };

    function resizeBody() {
        var w = screenWidth(), h = screenHeight(), oh = $('.board').outerHeight();

        $(document.body).css({
            width   :   w,
            height  :   h
        });

        var ih = h - oh;
        ih = ih - ih % oh;

        $('iframe').css({
            height  :   ih
        });
    }

    function padNumber(str, len) {
        for (var i = 0; i < len - (str + '').length; i ++) {
            str = '0' + str;
        }

        return str;
    }

    function getDate() {
        var d = new Date(),
            str = padNumber(d.getFullYear() + '-'
                + padNumber(d.getMonth() + 1, 2) + '-'
                + padNumber(d.getDate(), 2) + ' '
                + padNumber(d.getHours(), 2) + ':'
                + padNumber(d.getMinutes(), 2) + ':'
                + padNumber(d.getSeconds(), 2));

        delete d;
        return str;
    }

    $(document).ready(function () {
        var t = $('.time');
        
        resizeBody();
        $(window).resize(resizeBody);

        setInterval(function () {
            var time = getDate();
            t.html(time.replace(' ', '<br />'));
        }, 1000);
    });
})();
