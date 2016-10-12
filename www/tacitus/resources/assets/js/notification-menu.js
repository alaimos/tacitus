$(function () {
    var badgeUnread = $('.badge-unread-alerts');
    $('.mark-as-read-link').click(function () {
        $.ajax({
            dataType: 'json',
            method: 'GET',
            url: $(this).data('url'),
            success: function (data) {
                if (data.ok) {
                    $('.user-notification-container').remove();
                    $('.user-notification-divider').remove();
                    badgeUnread.addClass("hidden");
                    badgeUnread.html(0);
                } else {
                    if (console !== undefined) {
                        console.log(data.message);
                    }
                }
            }
        });
    });
    $('.user-notification-link').click(function () {
        var self = $(this);
        $.ajax({
            dataType: 'json',
            method: 'GET',
            url: $(this).data('url'),
            success: function (data) {
                if (data.ok) {
                    self.parent().next().remove();
                    self.parent().remove();
                    badgeUnread.html(parseInt(badgeUnread.html()) - 1);
                } else {
                    if (console !== undefined) {
                        console.log(data.message);
                    }
                }
            }
        });
    });
    (function notificationPoll() {
        setTimeout(function () {
            $.ajax({
                url: badgeUnread.data('url'), success: function (data) {
                    if (data.ok) {
                        var oldCount = parseInt(badgeUnread.html());
                        if (oldCount != data.message) {
                            //redraw notifications
                            $('.user-notification-container').remove();
                            $('.user-notification-divider').remove();
                            var code = '';
                            for (var i = 0; i < data.data.length; i++) {
                                var d = data.data[i];
                                code += '<li class="user-notification-container"><a href="Javascript:;"' +
                                    ' class="user-notification-link" data-url="' + d.url + '"><div>' + d.body + ' <span' +
                                    ' class="pull-right text-muted small"><em>' + d.created + '</em></span></div>' +
                                    '</a></li><li class="divider user-notification-divider"></li>'
                            }
                            if (code != '') {
                                $(code).insertAfter('.recent-alerts-header-divider');
                            }
                            badgeUnread.html(data.message);
                            if (data.message > 0) {
                                $('.no-alerts').addClass('hidden');
                                badgeUnread.removeClass('hidden');
                            } else {
                                $('.no-alerts').removeClass('hidden');
                                badgeUnread.addClass('hidden');
                            }
                        }
                        //Setup the next poll recursively
                        notificationPoll();
                    } else {
                        if (console !== undefined) {
                            console.log(data.message);
                        }
                    }
                }, dataType: "json"
            });
        }, 30000); //30 seconds delay
    })();
});