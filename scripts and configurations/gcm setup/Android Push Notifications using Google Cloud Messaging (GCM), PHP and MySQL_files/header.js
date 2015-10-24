function request_tutorial_submit(submited) {
    if (submited) {
        $('#rq_form, #rq_response_error').fadeOut(100);
        $('#rq_response').fadeIn(800);
    } else {
        $('#rq_response_error').fadeIn(800);
    }
}
function report_bug_submit(submited) {
    if (submited) {
        $('#report_bug').fadeOut(100);
        $('#report_bug_thanks').fadeIn(800);
    } else {
        $('#report_bug_error').fadeIn(800);
    }
}
function subscribeTo() {
    if (checkEmail($('.input_email').val())) {
        $('.img_loader').show();
        $.ajax({
            url: "http://download.androidhive.info/firewall/firewall.php",
            type: "get",
            data: $('#form_subscribe').serialize(),
            dataType: 'jsonp',
            success: function(data) {
                if (data["status"] === "success") {
                    $('#subscribe_response_error').fadeOut(100);
                    $('#subscribe_response').fadeIn(100);
                    $('#form_sub_hide').fadeOut(100);
                } else if (data["status"] === "subscribed") {
                    $('#subscribe_response_error').fadeOut(100);
                    $('#form_sub_hide').fadeOut(100);
                    $('#subscribe_response').html('You are already subscribed. Goto <a href="http://download.androidhive.info" target="_blank">Downloads</a>').fadeIn(100);
                } else {
                    $('#subscribe_response_error').fadeOut(100);
                }
            },
            error: function() {
                $('#subscribe_response_error').fadeOut(100);
            }
        });
        return false;
    } else {
        $('#subscribe_response_error').fadeIn(100);
    }

}
function checkEmail(email) {
    var filter = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
    return filter.test(email);
}
$(function() {
    var disqus_div = $("#disqus_thread");
    if (disqus_div.size() > 0) {
        var ds_loaded = false,
                top = disqus_div.offset().top,
                disqus_data = disqus_div.data(),
                check = function() {
            if (!ds_loaded && $(window).scrollTop() + $(window).height() + 3000 > top) {
                ds_loaded = true;
                for (var key in disqus_data) {
                    if (key.substr(0, 6) == 'disqus') {
                        window['disqus_' + key.replace('disqus', '').toLowerCase()] = disqus_data[key];
                    }
                }
                var dsq = document.createElement('script');
                dsq.type = 'text/javascript';
                dsq.async = true;
                dsq.src = 'http://' + window.disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            }
        };
        $(window).scroll(check);
        check();
    }
});
$(document).ready(function() {
    // setPageWidth();
    setFooterSeparator();

    $(window).resize(function() {
        // setPageWidth();
        setFooterSeparator();
    });

    $('#social_icons li.s').click(function() {
        if ($(this).attr('isShown') === 'false') {
            $(this).attr('isShown', 'true');
            $('.search_body_overlay').height($(document).height());
            $('.header_search_box').fadeIn();
            $('.search_body_overlay').slideDown();
            _gaq.push(['_trackEvent', 'Profile Social', 'Click', 'Home | Search']);
        }
    });

    function setPageWidth() {
        var sW = $("#container").width();
        var cW = sW - 341;
        var pW = $(window).width();
        $('.nav').css({'right': '0px'});
        $('.sidebar, .content').css({'margin-top': '0px'});
        $('.sidebar, .content').css({'padding-top': '0px'});
        $('#ad_top').css({'width': '750px', 'position': 'relative', 'margin': '0 auto'});
        $('.content').css({'width': '100%', 'position': 'relative'});
        if (sW > 768) {
            $('#ad_top').css({'width': '100%', 'position': 'absolute'});
            $('.content, #ad_top').css({'width': cW + 'px'});
            $('.content').css({'padding-top': '131px'});
            setSearchBar();
            setNavBar();
        }
        if (sW > 768 && cW < 720) {
            $('#ad_top').css({'width': '750px', 'position': 'relative', 'margin': '0 auto'});
            $('.content').css({'padding-top': '0px'});
            $('.sidebar').css({'margin-top': '0px'});
            setSearchBar();
        }
        if (sW < 768 && sW > 320) {
            $('.content').css('width', sW + 'px');
            $('#ad_top').css('width', '100%');
        }

        if (cW < 728) {
            setSearchBar();
        }

        if (pW > 990) {
            setNavBar();
        }

        setSidebarHeight();
    }

    function setFooterSeparator() {
        var height = $('.ul_footer li.second').height();
        $('#vline_1, #vline_2').css({'height': (height - 40) + 'px', 'margin-top': '50px'});
    }

    function setSidebarHeight() {
        // $('.sidebar').css({'height': $('.content').height() + 'px'});
    }

    function setSearchBar() {
        $('.input_search').width($('.search').width() - 60);
    }
    function setNavBar() {
        $('.nav').css({'right': $('.input_search').width() + 60 + 'px'});
    }
    $('body').animate({
        'opacity': 1.0
    }, 100);
    $(function() {
        // Find all YouTube videos
        var $allVideos = $("iframe[src^='http://www.youtube.com']"),
                // The element that is fluid width
                $fluidEl = $(".content");
        // Figure out and save aspect ratio for each video                   
        $allVideos.each(function() {
            $(this)
                    .data('aspectRatio', this.height / this.width)
                    // and remove the hard coded width/height
                    .removeAttr('height')
                    .removeAttr('width');
        });
        // temporary responsive img fix
        var sW = $("#container").width() - 440;
        setPostImageWidth();

        // When the window is resized
        // (You'll probably want to debounce this)
        $(window).resize(function() {
            var newWidth = $fluidEl.width();
            // Resize all videos according to their own aspect ratio
            $allVideos.each(function() {
                var $el = $(this);
                $el.width(newWidth - 40).height(newWidth * $el.data('aspectRatio'));
            });
            // Kick off one resize to fix all videos on page load
            sW = $("#container").width() - 440;
            setPostImageWidth();

        }).resize();

        function setPostImageWidth() {
            if (sW < 580 && $('.sidebar').width() === 440)
                $('div.image img, .article-post-image img').removeAttr('height').removeAttr('width').attr('width', '100%').attr('height', 'auto');
            else
                $('div.image img, .article-post-image img').attr('width', '720px').attr('height', 'auto');
        }
    });
    /*
     $.getJSON('http://download.androidhive.info/firewall/firewall.php?flag=stats&callback=?', {}, function(data) {
     $('.user_count_display').html(data.u_c);
     $('.download_count_display').html(data.d_c);
     });*/
});
$(document).mouseup(function(e)
{
    var container = $(".header_search_box");
    var searchBtn = $("#social_icons li.s");

    if (!container.is(e.target) && container.has(e.target).length === 0 && !searchBtn.is(e.target)
            && searchBtn.has(e.target).length === 0 && $('#social_icons li.s').attr('isShown') === 'true')
    {
        $('.header_search_box, .search_body_overlay').fadeOut();
        $('#social_icons li.s').attr('isShown', 'false');
    }
});
var disqus_shortname = 'androidhive';
(function() {
    var s = document.createElement('script');
    s.async = true;
    s.type = 'text/javascript';
    s.src = '//' + disqus_shortname + '.disqus.com/count.js';
    (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
}());
(function() {
    var cx = '016184582533250771761:x6l8btk2sfg';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
            '//www.google.com/cse/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
})();
