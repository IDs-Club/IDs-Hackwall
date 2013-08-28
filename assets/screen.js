(function () {
    var screenWidth = function () { return Math.max(document.documentElement.clientWidth, document.body.clientWidth); },
        screenHeight = function () { return Math.max(document.documentElement.clientHeight, document.body.clientHeight); },
        settings = {
            prefix  :   '$ ',
            max     :   50
        }, lines = [], sp = $('<span class="sp" />');

    function rand(l, u) {
        return Math.floor((Math.random() * (u-l+1))+l);
    }

    function resizeBody() {
        $(document.body).css({
            width   :   screenWidth(),
            height  :   screenHeight()
        });
    }

    function println(str) {
        lines.push(str);
    }

    function getWeibo() {
        $.getJSON('http://event-idsclub.ap01.aws.af.cm/weibolist.php', function (comments) {
            if (comments && comments.length > 0) {
                for (var i = 0; i < comments.length; i ++) {
                    var c = comments[i];

                    println(c);
                }
            }
        });
    }

    setInterval(function () {
        sp.toggleClass('hide');
    }, 500);

    function strWalk() {
        var str, state = 0, el, i, j;
        
        var interval = setInterval(function () {
            switch (state) {
                case 0:
                    // stand by
                    str = lines.shift();
                    if (!!str) {
                        if ($('.str').length > settings.max) {
                            $('.str:first').remove();
                        }

                        el = $('<div class="str" />').appendTo(document.body);
                        state = 1;
                        i = 0;
                        str.text = str.user + ' : ' + str.text;
                    }
                    break;
                case 1:
                    // print
                    el.html('<span class="head">' + settings.prefix + '</span>' + str.text.substring(0, i)).append(sp);
                    document.body.scrollTop = document.body.scrollHeight;
                    if (i >= str.text.length) {
                        state = 2;
                        j = 0;
                    }
                    i ++;
                    break;
                case 2:
                    if (j >= 150) {
                        state = 0;
                    }
                    j ++;
                    break;
                default:
                    break;
            }

            i ++;
        }, 10);
    }

    $(document).ready(function () {
        resizeBody();
        strWalk();
        $(window).resize(resizeBody);
        getWeibo();

        setInterval(function () {
            if (lines.length > 50) {
                return;
            }

            getWeibo();
        }, 15000);
    });
})();
