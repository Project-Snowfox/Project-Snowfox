<?php if(!defined('__Snowfox_ADMIN__')) exit; ?>
<script src="<?php $options->adminStaticUrl('js', 'jquery.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'jquery-ui.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'Snowfox.js'); ?>"></script>
<script>
    (function () {
        $(document).ready(function() {
            // 处理消息机制
            (function () {
                var prefix = '<?php echo \Snowfox\Cookie::getPrefix(); ?>',
                    cookies = {
                        notice      :   $.cookie(prefix + '__Snowfox_notice'),
                        noticeType  :   $.cookie(prefix + '__Snowfox_notice_type'),
                        highlight   :   $.cookie(prefix + '__Snowfox_notice_highlight')
                    },
                    path = '<?php echo \Snowfox\Cookie::getPath(); ?>',
                    domain = '<?php echo \Snowfox\Cookie::getDomain(); ?>',
                    secure = <?php echo json_encode(\Snowfox\Cookie::getSecure()); ?>;

                if (!!cookies.notice && 'success|notice|error'.indexOf(cookies.noticeType) >= 0) {
                    var head = $('.Snowfox-head-nav'),
                        p = $('<div class="message popup ' + cookies.noticeType + '">'
                        + '<ul><li>' + $.parseJSON(cookies.notice).join('</li><li>') 
                        + '</li></ul></div>'), offset = 0;

                    if (head.length > 0) {
                        p.insertAfter(head);
                        offset = head.outerHeight();
                    } else {
                        p.prependTo(document.body);
                    }

                    function checkScroll () {
                        if ($(window).scrollTop() >= offset) {
                            p.css({
                                'position'  :   'fixed',
                                'top'       :   0
                            });
                        } else {
                            p.css({
                                'position'  :   'absolute',
                                'top'       :   offset
                            });
                        }
                    }

                    $(window).scroll(function () {
                        checkScroll();
                    });

                    checkScroll();

                    p.slideDown(function () {
                        var t = $(this), color = '#C6D880';
                        
                        if (t.hasClass('error')) {
                            color = '#FBC2C4';
                        } else if (t.hasClass('notice')) {
                            color = '#FFD324';
                        }

                        t.effect('highlight', {color : color})
                            .delay(5000).fadeOut(function () {
                            $(this).remove();
                        });
                    });

                    $.cookie(prefix + '__Snowfox_notice', null, {path : path, domain: domain, secure: secure});
                    $.cookie(prefix + '__Snowfox_notice_type', null, {path : path, domain: domain, secure: secure});
                }

                if (cookies.highlight) {
                    $('#' + cookies.highlight).effect('highlight', 1000);
                    $.cookie(prefix + '__Snowfox_notice_highlight', null, {path : path, domain: domain, secure: secure});
                }
            })();


            // 导航菜单 tab 聚焦时展开下拉菜单
            const menuBar = $('.menu-bar').click(function () {
                const nav = $(this).next('#Snowfox-nav-list');
                if (!$(this).toggleClass('focus').hasClass('focus')) {
                    nav.removeClass('expanded noexpanded');
                }
            });

            $('.main, .Snowfox-foot').on('click touchstart', function () {
                if (menuBar.hasClass('focus')) {
                    menuBar.trigger('click');
                }
            });

            $('#Snowfox-nav-list ul.root').each(function () {
                const ul = $(this), nav = ul.parent();
                let focused = false;

                ul.on('click touchend', '.parent a', function (e) {
                    nav.removeClass('noexpanded').addClass('expanded');
                    if ($(window).width() < 576 && e.type == 'click') {
                        return false;
                    }
                }).find('.child')
                .append($('<li class="return"><a><?php _e('返回'); ?></a></li>').click(function () {
                    nav.removeClass('expanded').addClass('noexpanded');
                    return false;
                }));

                $('a', ul).focus(function () {
                    ul.addClass('expanded');
                    focused = true;
                }).blur(function () {
                    focused = false;

                    setTimeout(function () {
                        if (!focused) {
                            ul.removeClass('expanded');
                        }
                    });
                });
            });

            if ($('.Snowfox-login').length == 0) {
                $('a').each(function () {
                    var t = $(this), href = t.attr('href');

                    if ((href && href[0] == '#')
                        || /^<?php echo preg_quote($options->adminUrl, '/'); ?>.*$/.exec(href) 
                            || /^<?php echo substr(preg_quote(\Snowfox\Common::url('s', $options->index), '/'), 0, -1); ?>action\/[_a-zA-Z0-9\/]+.*$/.exec(href)) {
                        return;
                    }

                    t.attr('target', '_blank')
                        .attr('rel', 'noopener noreferrer');
                });
            }
        });
    })();
</script>
