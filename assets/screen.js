(function () {
    var screenWidth = function () { return Math.max(document.documentElement.clientWidth, document.body.clientWidth); },
        screenHeight = function () { return Math.max(document.documentElement.clientHeight, document.body.clientHeight); },
        settings = {
            prefix  :   '$ ',
            max     :   50
        }, lines = [], bk = [], sp = $('<span class="sp" />');

    var base_url = 'http://event-idsclub.ap01.aws.af.cm/';
    var emotions;

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
        bk.push(str);
        lines.push(str);
        if (bk.length > 50) {bk.shift();};
    }

    function getWeibo() {
        $.getJSON(base_url + 'weibolist.php', function (comments) {
            if (comments && comments.length > 0) {
                lines.length = 0;
                for (var i = 0; i < comments.length; i ++) {
                    var c = comments[i];

                    println(c);
                }
            }
            $('#loading').remove();
        });
    }

    function getEmotions() {
        $.getJSON(base_url + 'info.php?type=emotion', function (data) {
            if (data.status == 'ok') {
                emotions = data.emotions;
            }
        });
    }

    function getUser() {
        $.getJSON(base_url + 'info.php?type=userinfo', function (data) {
            if (data.status == 'ok') {
                $('.say').html(data.user.screen_name);
                $('.follow').html('粉丝（' + data.user.followers_count + '）');
            }
        });
    }

    setInterval(function () {
        sp.toggleClass('hide');
    }, 500);

    function strWalk() {
        var str, state = 0, wd, rt, wt, el, i, j, emt;
        
        var interval = setInterval(function () {
            switch (state) {
                case 0:
                    // stand by
                    str = lines.shift();
                    if (!!str) {
                        if ($('.str').length > settings.max) {
                            $('.str:first').remove();
                        }

                        el = $('<div class="str WB_feed_datail S_line2 clearfix" />').appendTo(document.body);
                        $('<div class="WB_face"><img width="50" height="50" src="'+ str.image_url +'" alt="" title="'+ str.user +'" ></div>').appendTo(el);
                        wd = $('<div class="WB_detail" />').appendTo(el);
                        $('<div class="WB_info">'+ str.user +'</div>').appendTo(wd);
                        wt = $('<div node-type="feed_list_content" class="WB_text" />').appendTo(wd);
                        state = 1;
                        i = 0;
                    }
                    break;
                case 1:
                    // print
                    wt.html(str.text.substring(0, i));
                    if (i >= str.text.length) {
                        // 替换表情
                        wt.html(str.text.replace(/(\[[^\[\]]*\])/g,
                            function($1){
                                var temp = '';
                                $.each(emotions,function(i,it){
                                    if($1 == i){
                                         temp = '<img src="' + it + '" title="' + i + '"/>';
                                         return false;
                                    }else{
                                        temp = $1;
                                    }
                                });
                                return temp;
                            })
                        );
                        if (str.thumb != '') {
                            wd.append('<img class="thumb" src="'+ str.thumb +'">');
                        }
                        if (str.origin != ''){
                            emt = str.origin.text.replace(/(\[[^\[\]]*\])/g,
                            function($1){
                                var temp = '';
                                $.each(emotions,function(i,it){
                                    if($1 == i){
                                         temp = '<img src="' + it + '" title="' + i + '"/>';
                                         return false;
                                    }else{
                                        temp = $1;
                                    }
                                });
                                return temp;
                            });
                            if (str.origin.type == 1){
                                rt = '<div class="WB_media_expand SW_fun2 S_line1 S_bg1"><div class="WB_arrow"><em class="S_line1_c">◆</em><span class="S_bg1_c">◆</span></div>';
                                rt += '<div><div class="WB_name"><span class="blue">@'+ str.origin.user +'</span></div><div node-type="feed_list_reason" class="WB_text"><em>'+ emt +'</em></div></div></div>';
                                $(rt).appendTo(wd);
                            }else if (str.origin.type == 2) {
                                rt = '<div class="WB_media_expand SW_fun2 S_line1 S_bg1"><div class="WB_arrow"><em class="S_line1_c">◆</em><span class="S_bg1_c">◆</span></div>';
                                rt += '<div><div node-type="feed_list_reason" class="WB_text"><em>评论 <span class="blue">@' + str.origin.user + '</span> 的微博 “' + emt.substr(0, 20) + '...“</em></div></div></div>';
                                $(rt).appendTo(wd);
                            }   
                        }
                        state = 2;
                        j = 0;
                    }; 
                    wd.append(sp);
                    document.body.scrollTop = document.body.scrollHeight;
                    i ++;
                    break;
                case 2:
                    if (j >= 300) {
                        state = 0;
                        if (lines.length == 0) {lines = bk.slice(0)};
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
        $('<div id="loading"><div></div></div>').appendTo(document.body);
        getEmotions();
        resizeBody();
        strWalk();
        $(window).resize(resizeBody);
        getWeibo();

        setInterval(function(){getUser();}, 300000);
        setInterval(function () {
            if (lines.length > 50) {
                return;
            }

            getWeibo();
        }, 60000);
    });
})();
