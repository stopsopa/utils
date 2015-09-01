/**
 * @author Szymon Działowski
 * @ver 1.0 - 2015-09-01
 *
 * Użycie:
 *
    (function (box) {
        var w = box.find('[data-widget]');
        var h = w.height();
        box.find('[data-resize]').stopsopamove({
            move: function (e, start, diff) {
                w.css({minHeight: (h + diff.y) + 'px'});
            },
            down: function () {
                h = w.height();
            }
        });
    })(box);
 */
;(function ($) {
    var doc;
    $.fn.stopsopamove = function (opt) {

        if (!doc) {
            doc = $(document);
        }

        opt = $.extend({
            move : $.noop,
            down : $.noop,
            up   : $.noop,
        }, opt || {});

        var d = $(this), hold = false, tmp; // hold button

        function move (e) {
            opt.move.apply(this, [e, tmp, {
                x : e.pageX - tmp.pageX,
                y : e.pageY - tmp.pageY
            }]);
        }

        function down (e) {
            tmp = e;
            opt.down.apply(this, arguments);
            hold = true;
            doc.on('mousemove', move)
        }
        function up() {
            opt.up.apply(this, arguments);
            hold = false;
            doc.off('mousemove', move);
            d.off('mousedown', down).off('mouseup', up)
        }

        d
            .on('mouseenter', function () {
                if (!hold) {
                    doc.on('mousedown', down).on('mouseup', up)
                }
            })
            .on('mouseleave', function () {
                if (!hold) {
                    doc.off('mousedown', down).off('mouseup', up)
                }
            });

        return d;
    }
})(jQuery);