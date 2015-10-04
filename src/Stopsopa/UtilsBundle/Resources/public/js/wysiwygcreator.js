var wysiwygcreator = function (tmp, h) { // h może być niezdefiniowane
    return function (box, opt) {
        opt = $.extend({
            h: h,
            onresize : $.noop,
            content: null,
            change: $.noop
        }, opt || {});

        opt.onresize = _.debounce(opt.onresize, 200);

        box.html(tmp({
            data: _.unescape(opt.content || box.html())
        }))

        var widget = box.find('[data-widget]');

        opt.h && widget.css({minHeight: opt.h+'px'});

        widget
            .tinymce({// aby używać jako w tyczki jquery trzeba załadować samo tinymce a nastepnie wtyczkę jquery
                theme               : "modern",
                inline              : true,
                menubar             : false,
                plugins             : "autoresize link paste",
                toolbar1            : "insertfile undo redo | bold italic | bullist | link unlink",
                convert_urls        : false, // nie przerabia linków z /blank.php na ../../blank.php
                relative_urls       : true,
                target_list         : false, // wyłącza możliwość zmiany target w linkach
                default_link_target : "_blank",
                valid_elements      : "a[href|!target=_blank|!rel=nofollow],strong/b,div,br,p,ul,li,em",
                //invalid_elements    : "strong,em"
                forced_root_block   : false,
                language            : "pl",
                //body_class          : "print html", // klasa tinymce // a na stronie robimy na przykład 'tiny web'
                //content_css         : "/bundles/toolssitecommon/bible/css/tinymce-bible-pdf.css",  // http://www.tinymce.com/wiki.php/Configuration:content_css
                setup : function(ed) {
                    //var ev = 'init|paste|cut|keydown|keyup'.split('|');
                    var ev = 'paste|cut|keydown|keyup'.split('|'); // daruję sobie init bo pierniczy to permalink

                    for (var i = 0, l = ev.length ; i < l ; i += 1 ) {
                        ed.on(ev[i], (function (i) {
                            return function(args) { // http://www.tinymce.com/wiki.php/api4:class.tinymce.Editor // g(tinymce.Editor)
                                //var c = ed.getContent({format : 'text'}).length;
                                //target.html(limit - c);
                                opt.change();
                            }
                        })(i));
                    }
                }
            });


            (function () {
                var h = widget.height();
                box.find('[data-resize]').stopsopamove({
                    move: function (e, start, diff) {
                        widget.css({minHeight: (h + diff.y) + 'px'});
                        opt.onresize(widget.height());
                    },
                    down: function () {
                        h = widget.height();
                    }
                });
            })();

            //opt.onresize(widget.height());

        return {
            tinymce : function () {
                return widget.tinymce();
            },
            get: function () {
                return this.tinymce().getContent();
            },
            set: function (data) {
                this.tinymce().setContent(data);
                return this;
            },
            getH: function () {
                return widget.height();
            },
            setH: function (h) {
                var hh = parseInt(h);
                if (hh > 0) {
                    widget.css({minHeight: hh+'px'});
                }
                else {
                    throw 'Wrong h: '.JSON.stringify(h);
                }
                return this;
            }
        };
    }
};