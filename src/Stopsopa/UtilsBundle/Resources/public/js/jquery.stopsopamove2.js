/**
 * @author Szymon Działowski
 * @ver 1.0 - 2015-09-01
 * @require:
 *  - http://modernizr.com/download?-touchevents-dontmin
 *
 * Użycie:
 *
    (function (box) {
        //var w = box.find('[data-here]'); // co przesuwam

        var t;
        var l;


        box.find('[data-here]').stopsopamove({ // co draguję
            down: function () {
                var tmp = $(this).position();
                t = tmp.top;
                l = tmp.left;
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

    alternative:
        http://greensock.com/draggable g(green stock Draggable)
        hammer.js from https://github.com/fleveque/awesome-awesomes

    docs:
        https://developer.mozilla.org/en-US/docs/Web/Events/touchmove


 */
;(function ($, name) {

    function log(){try{window.console.log.apply(window.console,arguments);}catch(e){}};
    function error(){try{window.console.error.apply(window.console, arguments);}catch(e){}};

    var
        doc,
        key     = '_'+name,
        ev      = function (type) { // start, move, end
            if (type === 'move') {

            }
            if (type === 'down') {

            }
            if (type === 'up') {
                return (Modernizr.touchevents ? '' : '')+'';
            }
        },
        tools   = {
            destroy: function () {
                var t = $(this).addClass('red');
                var data = t.data(key);
                if (data) {
                    try {
                        t
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
                log('move')
                if (e.changedTouches) {
                    e = e.changedTouches;
                }
                log(e)
                opt.move.apply(t, [e, tmp, {
                    x : e.pageX - tmp.pageX,
                    y : e.pageY - tmp.pageY
                }]);
            }
            function dragstart(e) { // http://stackoverflow.com/questions/4211909/disable-dragging-an-image-from-an-html-page
                e.preventDefault();
            }
            function up() {

                opt.up.apply(t, arguments);

                doc
                    .off('mousemove', move)
                    .off('mouseup', up)
                ;
            }
            function down(e) {

                if (e.changedTouches) {
                    e = e.changedTouches;
                }

                tmp = e;

                opt.down.apply(t, arguments);

                doc
                    .on('mousemove', move)
                    .on('mouseup', up)
                ;

                t.on('dragstart', dragstart)
            }

            t
                .on('mousedown', down)
                .data(key, {
                    dragstart:  dragstart,
                    move:       move,
                    up:         up,
                    down:       down
                })
            ;
        });
    }
})(jQuery, 'stopsopamove');