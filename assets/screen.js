(function () {
    var screenWidth = function () { return Math.max(document.documentElement.clientWidth, document.body.clientWidth); },
        screenHeight = function () { return Math.max(document.documentElement.clientHeight, document.body.clientHeight); },
        settings = {
            prefix  :   '$ ',
            max     :   50
        }, lines = [], bk = [], sp = $('<span class="sp" />');

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
        $.getJSON('http://event-idsclub.ap01.aws.af.cm/weibolist.php', function (comments) {
            if (comments && comments.length > 0) {
                lines.length = 0;
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
        var str, state = 0, wd, rt, wt, el, i, j;
        
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
                        if (str.thumb != '') {
                            wd.append('<img src="'+ str.thumb +'">');
                        }
                        if (str.origin != ''){
                            if (str.origin.type == 1){
                                rt = '<div class="WB_media_expand SW_fun2 S_line1 S_bg1"><div class="WB_arrow"><em class="S_line1_c">◆</em><span class="S_bg1_c">◆</span></div>';
                                rt += '<div><div class="WB_name"><span class="blue">@'+ str.origin.user +'</span></div><div node-type="feed_list_reason" class="WB_text"><em>'+ str.origin.text +'</em></div></div></div>';
                                $(rt).appendTo(wd);
                            }else if (str.origin.type == 2) {
                                rt = '<div class="WB_media_expand SW_fun2 S_line1 S_bg1"><div class="WB_arrow"><em class="S_line1_c">◆</em><span class="S_bg1_c">◆</span></div>';
                                rt += '<div><div node-type="feed_list_reason" class="WB_text"><em>评论 <span class="blue">@' + str.origin.user + '</span> 的微博 “' + str.origin.text.substr(0, 20) + '...“</em></div></div></div>';
                                $(rt).appendTo(wd);
                            }   
                        }
                        state = 2;
                        j = 0;
                    }; 
                    document.body.scrollTop = document.body.scrollHeight;
                    wd.append(sp);
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
        resizeBody();
        strWalk();
        $(window).resize(resizeBody);
        getWeibo();

        setInterval(function () {
            if (lines.length > 50) {
                return;
            }

            getWeibo();
        }, 60000);
    });
})();
