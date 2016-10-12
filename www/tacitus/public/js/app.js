$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * This data rendering helper method can be useful for cases where you have
 * potentially large data strings to be shown in a column that is restricted by
 * width. The data for the column is still fully searchable and sortable, but if
 * it is longer than a give number of characters, it will be truncated and
 * shown with ellipsis. A browser provided tooltip will show the full string
 * to the end user on mouse hover of the cell.
 *
 * This function should be used with the `dt-init columns.render` configuration
 * option of DataTables.
 *
 * It accepts three parameters:
 *
 * 1. `-type integer` - The number of characters to restrict the displayed data
 *    to.
 * 2. `-type boolean` (optional - default `false`) - Indicate if the truncation
 *    of the string should not occur in the middle of a word (`true`) or if it
 *    can (`false`). This can allow the display of strings to look nicer, at the
 *    expense of showing less characters.
 * 2. `-type boolean` (optional - default `false`) - Escape HTML entities
 *    (`true`) or not (`false` - default).
 *
 *  @name ellipsis
 *  @summary Restrict output data to a particular length, showing anything
 *      longer with ellipsis and a browser provided tooltip on hover.
 *  @author [Allan Jardine](http://datatables.net)
 *  @requires DataTables 1.10+
 *
 * @returns {Number} Calculated average
 *
 *  @example
 *    // Restrict a column to 17 characters, don't split words
 *    $('#example').DataTable( {
 *      columnDefs: [ {
 *        targets: 1,
 *        render: $.fn.dataTable.render.ellipsis( 17, true )
 *      } ]
 *    } );
 *
 *  @example
 *    // Restrict a column to 10 characters, do split words
 *    $('#example').DataTable( {
 *      columnDefs: [ {
 *        targets: 2,
 *        render: $.fn.dataTable.render.ellipsis( 10 )
 *      } ]
 *    } );
 */

jQuery.fn.dataTable.render.ellipsis = function ( cutoff, wordbreak, escapeHtml ) {
	var esc = function ( t ) {
		return t
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	};

	return function ( d, type, row ) {
		// Order, search and type get the original data
		if ( type !== 'display' ) {
			return d;
		}

		if ( typeof d !== 'number' && typeof d !== 'string' ) {
			return d;
		}

		d = d.toString(); // cast numbers

		if ( d.length <= cutoff ) {
			return d;
		}

		var shortened = d.substr(0, cutoff-1);

		// Find the last white space character in the string
		if ( wordbreak ) {
			shortened = shortened.replace(/\s([^\s]*)$/, '');
		}

		// Protect against uncontrolled HTML input
		if ( escapeHtml ) {
			shortened = esc( shortened );
		}

		return '<span class="ellipsis" title="'+esc(d)+'">'+shortened+'&#8230;</span>';
	};
};

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
$(function () {

    $('#side-menu').metisMenu();

});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function () {
    $(window).bind("load resize", function () {
        var topOffset = 50;
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    // var element = $('ul.nav a').filter(function() {
    //     return this.href == url;
    // }).addClass('active').parent().parent().addClass('in').parent();
    var element = $('ul.nav a').filter(function () {
        return this.href == url;
    }).addClass('active').parent();

    while (true) {
        if (element.is('li')) {
            element = element.parent().addClass('in').parent();
        } else {
            break;
        }
    }
});

//# sourceMappingURL=app.js.map
