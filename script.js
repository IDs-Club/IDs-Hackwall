(function () {
    var screenWidth = function () { return Math.max(document.documentElement.clientWidth, document.body.clientWidth); },
        screenHeight = function () { return Math.max(document.documentElement.clientHeight, document.body.clientHeight); },
        music = {
            standby : './standby.mp3',
            amazing : './amazing.mp3'
        }, musicControl = {},
        stream = [
            ['开场', '2012-12-15 10:00:00'],
            ['API宣讲', '2012-12-15 10:10:00'],
            ['午餐', '2012-12-15 12:00:00'],
            ['产品开发', '2012-12-15 12:30:00'],
            ['晚餐', '2012-12-15 17:30:00'],
            ['产品开发', '2012-12-15 18:00:00'],
            ['宵夜', '2012-12-15 23:00:00'],
            ['产品开发', '2012-12-16 00:00:00'],
            ['早餐', '2012-12-16 08:00:00'],
            ['产品开发与提交', '2012-12-16 09:00:00'],
            ['午餐与评审', '2012-12-16 12:00:00'],
            ['产品展示', '2012-12-16 13:00:00'],
            ['颁奖', '2012-12-16 14:30:00']
        ], pos = -1,
        say = $('.say');

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

    function fadeOutAudio(a) {
        var intval = setInterval(function () {
            a.volume -= 0.1;

            if (a.volume <= 0.1) {
                a.pause();
                a.load();
                a.volume = 1;

                clearInterval(intval);
                intval = null;
            }
        }, 1000);
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

    function leftTime(word, time) {
        var h = Math.floor(time / 3600),
            m = Math.floor((time % 3600) / 60),
            s = Math.floor(time % 60);

        return (h + m + s == 0) ? 'SEGMENTFAULT AT 0x0000 :)' : 
            '距离<strong>' + word + '</strong>还有'
            + (h > 0 ? h + '小时' : '') 
            + (m > 0 ? m + '分' : '')
            + (s > 0 ? s + '秒' : '钟');
    }

    function updateStream(now) {
        var current = stream[pos], next = stream[pos + 1], str = '';

        if (current) {
            str += '<strong>' + current[0] + '</strong>进行中 ';
        }

        if (next) {
            var left = Date.parse(next[1]) - Date.parse(now);
            if (!current || left <= 180000) {
                str += leftTime(next[0], left / 1000);

                if (!current) {
                    if (!music.amazing && !music.standby && left > 190000) {
                        musicControl.amazing.play();
                        music.amazing = true;
                    }

                    if (left <= 190000 && music.amazing) {
                        fadeOutAudio(musicControl.amazing);
                        music.amazing = false;
                    }
                }

                if (left <= 180000 && left > 10000 && !music.standby) {
                    musicControl.standby.play();
                    music.standby = true;
                }

                if (left <= 10000 && music.standby) {
                    fadeOutAudio(musicControl.standby);
                    music.standby = false;
                }
            }

            if (left <= 0) {
                pos ++;
            }
        }

        say.html(str);
    }

    $(document).ready(function () {
        var t = $('.time');
        
        resizeBody();
        $(window).resize(resizeBody);

        for (var key in music) {
            var a = new Audio(music[key]);
            a.load();
            a.loop = true;

            musicControl[key] = a;
            music[key] = false;
        }

        setInterval(function () {
            var time = getDate();
            t.html(time.replace(' ', '<br />'));
            updateStream(time);
        }, 1000);
    });
})();
