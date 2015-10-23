/**
 * @author Szymon Działowski
 * @ver 1.0 - 2015-09-01
 *
 * Użycie:
 *
    (function (box) {
        //var w = box.find('[data-here]'); // co przesuwam

        var t;
        var l;


        box.find('[data-here]').stopsopamove({ // co draguję
            down: function () {
                t = parseInt($(this).css('top'), 10);
                l = parseInt($(this).css('left'), 10);
            },
            move: function (e, start, diff) {
                $(this).addClass('move___').css({
                    left : (l + diff.x) + 'px',
                    top  : (t + diff.y) + 'px'
                });
            },
            up: function () {
                $(this).stopsopamove('destroy');
            }
        });
    })($('.map'));
 */
;(function ($, name) {

    function log(){try{window.console.log.apply(window.console,arguments);}catch(e){}};
    function error(){try{window.console.error.apply(window.console, arguments);}catch(e){}};

    var
        doc,
        key     = '_'+name,
        tools   = {
            destroy: function () {
                var t = $(this).addClass('red');
                var data = t.data(key);
                if (data) {
                    try {
                        t
                            .off('mouseenter',   data.mouseenter)
                            .off('mouseleave',   data.mouseleave)
                            .off('dragstart',    data.dragstart)
                        ;
                        doc
                            .off('mousemove',   data.move)
                            .off('mousedown',   data.down)
                            .off('mouseup',     data.up)
                        ;
                    }
                    catch (e) {
                        error(e)
                    }
                    t.removeData(key);
                }
                else {
                    error('Element is not an "'+name+'" widget');
                    return t;
                }
            }
        }
    ;

    $.fn[name] = function (opt) {

        var args = Array.prototype.slice.call( arguments, 1);

        if (!doc)
            doc = $(document);

        return $(this).each(function () {
            var
                t       = $(this),
                hold    = false,
                tmp     // hold button
            ;

            if (typeof opt === 'string') {
                if (tools[opt]) {
                    tools[opt].apply(t, args);
                }
                else {
                    error("Method '"+opt+"' is not defined in '"+name+"' plugin");
                }
                return t;
            }

            if (t.data(key)) {
                return error("Widget '"+name+"' is already initialized on this element");
            }

            opt = $.extend({
                move : $.noop,
                down : $.noop,
                up   : $.noop
            }, opt || {});

            function move(e) {
                opt.move.apply(t, [e, tmp, {
                    x : e.pageX - tmp.pageX,
                    y : e.pageY - tmp.pageY
                }]);
            }

            function down(e) {
                tmp = e;
                opt.down.apply(t, arguments);
                hold = true;
                doc.on('mousemove', move);
            }
            function up() {
                opt.up.apply(t, arguments);
                hold = false;
                doc.off('mousemove', move);
                t.off('mousedown', down).off('mouseup', up);
            }

            function mouseenter() {
                if (!hold) {
                    doc.on('mousedown', down).on('mouseup', up);
                }
            }
            function mouseleave() {
                if (!hold) {
                    doc.off('mousedown', down).off('mouseup', up);
                }
            }
            function dragstart(e) { // http://stackoverflow.com/questions/4211909/disable-dragging-an-image-from-an-html-page
                e.preventDefault();
            }

            t
                .on('mouseenter',   mouseenter)
                .on('mouseleave',   mouseleave)
                .on('dragstart',    dragstart)
            ;

            t.data(key, {
                mouseenter: mouseenter,
                mouseleave: mouseleave,
                dragstart:  dragstart,
                move:       move,
                up:         up,
                down:       down
            })
        });
    }
})(jQuery, 'stopsopamove');