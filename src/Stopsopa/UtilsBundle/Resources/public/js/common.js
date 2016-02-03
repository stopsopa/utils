// <script src="/bundles/stopsopautils/js/common.js"></script>
// process.nextTick polifill
;(function (main, d) {
    main.process||(main.process = {});
    if (!main.process.nextTick){
        main.process.nextTick=(function(){
            try{main.process.nextTick(d);return main.process.nextTick}catch(e){};
            try{main.setImmediate(d);return main.setImmediate}catch(e){};
            return function(f){setTimeout(f,0)};
        })();
    }
}(self, function(){}));

// .on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', fn);

// // wymaga jquery
// <script type="text/javascript"></script>
// 'bundles/stopsopautils/js/common.js'
'use strict';

//window.log = console ? console.log : function () {};
// function log(l) {try{console.log(l)}catch(e){}}
function log() {
    try {
        window.console.log.apply(window.console, arguments);
    } catch (e) {
    }
}
function error() {
    try {
        window.console.error.apply(window.console, arguments);
    } catch (e) {
    }
}

/**
 *
 * @param ask
 * @returns {Function}
 */
window.onexitsetup = function (ask) {
    ask || (ask = "Niektóre dane nie zostały zapisane i mogą zostać utracone bezpowrotnie");
    return function (state, altmsg) {
        window.onbeforeunload = state ? function () {
            return altmsg || ask;
        } : function () {};
    }
}




//window.site = {};
//$(function () {
//    $.ajaxstatus({/*options*/}, window.site);
//});

if (!window.site) {
    window.site = {};
}


/**
 * Klasa Array sama jest funkcją ale Array.prototype jest obiektem z metodą na przykład indexOf()
 *       var r = render(tmptr());
 *       Array.prototype.forEach.call(r, function (e) {
 *         tbody.appendChild(e)
 *       });
 */
(site.dom) || (site.dom = {});
site.dom.render = (function () {
//            var find = " <b> \n\n <tr><td>inne<other>".replace(/^\s*?<([^>]+)>[\s\S]*$/i, '$1');
    var find = /^\s*?<([^>]+)>[\s\S]*$/i;
    var table = ['tbody', 'tr', 'td'];
    var t1, t2;
    return function (html) {
        var first = html.replace(find, '$1').toLocaleLowerCase();
        var t = document.createElement('table');
        t.innerHTML = html;
        if (table.indexOf(first) < 0 || first === 'tbody') {
            return Array.prototype.slice.call(t.children, 0);
        }
        var t1 = [];
        Array.prototype.forEach.call(t.children, function (e) {
            Array.prototype.forEach.call(e.children, function (ee) {
                t1.push(ee);
            });
        });
        if (first === 'tr') {
            return Array.prototype.slice.call(t1, 0);
        }
        var t2 = [];
        Array.prototype.forEach.call(t1, function (e) {
            Array.prototype.forEach.call(e.children, function (ee) {
                t2.push(ee);
            });
        });
        return Array.prototype.slice.call(t2, 0);
    }
}());

// https://developer.mozilla.org/pl/docs/Web/API/Element/matches
site.dom.match = (function () {
    // nie działa to jak powinno
    //if (Element.prototype.matches) {
    //    return function (e, sel) {
    //        return e.matches(e, sel);
    //    }
    //}
    //if (Element.prototype.msMatchesSelector) {
    //    return function (e, sel) {
    //        return Element.prototype.msMatchesSelector.call(e, sel);
    //    }
    //}
    return function (e, sel) { // jeśli nie ma to polifil
        var matches = (e.document || e.ownerDocument).querySelectorAll(sel),
            i = matches.length;
        while (--i >= 0 && matches.item(i) !== e) ;
        return i > -1;
    }
}());


(function (to) {  // narzędzie do tłumaczeń kodów błedów http
    to || (to = {});
    // var t,s = "100|Continue:101|Switching Protocols:102|Processing:200|OK:201|Created:202|Accepted:203|Non-Authoritative Information:204|No Content:205|Reset Content:206|Partial Content:207|Multi-Status:300|Multiple Choices:301|Moved Permanently:302|Found:303|See Other:304|Not Modified:305|Use Proxy:307|Temporary Redirect:400|Bad Request:401|Unauthorized:402|Payment Required:403|Forbidden:404|Not Found:405|Method Not Allowed:406|Not Acceptable:407|Proxy Authentication Required:408|Request Timeout:409|Conflict:410|Gone:411|Length Required:412|Precondition Failed:413|Request Entity Too Large:414|Request-URI Too Long:415|Unsupported Media Type:416|Requested Range Not:Satisfiable:417|Expectation Failed:422|Unprocessable Entity:423|Locked:424|Failed Dependency:426|Upgrade Required:500|Internal Server Error:501|Not Implemented:502|Bad Gateway:503|Service Temporarily Unavailable:504|Gateway Timeout:505|HTTP Version Not Supported:506|Variant Also Negotiates:507|Insufficient Storage:509|Bandwidth Limit Exceeded:510|Not Extende".split(":");
    var i, t, s = "100|Continue:101|Switching Protocols:102|Processing:200|OK:201|Created:202|Accepted:203|Non-Authoritative Information:204|No Content:205|Reset Content:206|Partial Content:207|Multi-Status:300|Multiple Choices:301|Moved Permanently:302|Found:303|See Other:304|Not Modified:305|Use Proxy:307|Temporary Redirect:400|Bad Request:401|Unauthorized:402|Payment Required:403|Forbidden:404|The given page does not exist.:405|Method Not Allowed:406|Not Acceptable:407|Proxy Authentication Required:408|Request Timeout:409|Conflict:410|Gone:411|Length Required:412|Precondition Failed:413|Request Entity Too Large:414|Request-URI Too Long:415|Unsupported Media Type:416|Requested Range Not:Satisfiable:417|Expectation Failed:422|Unprocessable Entity:423|Locked:424|Failed Dependency:426|Upgrade Required:500|Internal Server Error:501|Not Implemented:502|Bad Gateway:503|Czyszczenie cache, proszę spróbować za kilka sekund:504|Gateway Timeout:505|HTTP Version Not Supported:506|Variant Also Negotiates:507|Insufficient Storage:509|Bandwidth Limit Exceeded:510|Not Extende".split(":");
    to.statusCodes = {};
    for (i = 0; i < s.length; i += 1) {
        t = s[i].split("|");
        to.statusCodes[parseInt(t[0], 10)] = t[1];
    }
    to.statusCodeTrans = function (code) {
        return to.statusCodes[parseInt(code, 10)];
    };
    to.status = function (code, sign) {
        if (!sign) {
            sign = ":";
        }
        return code+" "+sign+" "+to.statusCodeTrans(code);
    };
    return to;
}(site)); // przywiązuję do obiektu site bo on już istnieje



/*!
 * based on: http://benalman.com/projects/jquery-throttle-debounce-plugin/
 */
window.site || (window.site = {});

(function (mount) {
    var tmpthrottle = function(delay, no_trailing /* def: */, callback, debounce_mode) {
        var timeout_id, last_exec = 0;
        if (typeof no_trailing !== 'boolean') {
            debounce_mode = callback;
            callback = no_trailing;
            no_trailing = undefined;
        }
        function wrapper() {
            var that = this,
                elapsed = +new Date() - last_exec,
                args = arguments;
            function exec() {
                last_exec = +new Date();
                callback.apply( that, args );
            };
            function clear() {
                timeout_id = undefined;
            };
            if (debounce_mode && !timeout_id) {
                exec();
            }
            timeout_id && clearTimeout(timeout_id);
            if (debounce_mode === undefined && elapsed > delay) {
                exec();
            } else if (no_trailing !== true) {
                timeout_id = setTimeout(debounce_mode ? clear : exec, debounce_mode === undefined ? delay - elapsed : delay);
            }
        };
        return wrapper;
    };
    var tmpdebounce = function(delay, at_begin /* def true */, callback) {
        (typeof at_begin === 'undefined') && (at_begin = false);
        return callback === undefined ? tmpthrottle(delay, at_begin, false) : tmpthrottle(delay, callback, at_begin !== false);
    };

    // ( callback, delay, [ no_trailing (def: false) ])
    mount.throttle = function () {
        var a = arguments;
        if (a.length === 3)
            return tmpthrottle.call(this, a[1], a[2], a[0]);
        if (a.length === 2)
            return tmpthrottle.call(this, a[1], a[0]);
        throw "Wrong number of arguments in throttle";
    };

    // ( callback, delay, [ at_begin = (def: false) ] ) )
    mount.debounce = function () {
        var a = arguments;
        if (a.length === 3)
            return tmpdebounce.call(this, a[1], a[2], a[0]);
        if (a.length === 2) {
            return tmpdebounce.call(this, a[1], false, a[0]);
        }
        throw "Wrong number of arguments in debounce";
    };




    mount.sticki = function (k, parent, encls, discls) {

        //.stickien .k-grid-header {
        //    position: fixed;
        //    top: 0;
        //    z-index: 100;
        //}
        //.stickien [data-role="pager"]:first-child {
        //    margin-bottom: 31px;
        //}

        //var k = $('.k-grid-header');
        //k.width(k.width());
        //site.sticki(k, grid, 'stickien', 'stickidis');

        var d = $(document);

        var h = k.offset().top;
        log('h')
        log(h)

        var lastcond;

        function cycle(e) {

            var hh = e ? (e.originalEvent.pageY) : d.scrollTop();

            log('hh')
            log(hh)

            var cond = (h > hh);

            log('cond')
            log(cond)

            if (cond !== lastcond) {

                lastcond = cond;

                parent[(cond ? 'remove' : 'add')+'Class'](encls);

                parent[(cond ? 'add' : 'remove')+'Class'](discls);
            }
        }

        cycle();

        $(window).on('scroll', site.debounce(cycle, 20));
    }
}(window.site));

if ('jQuery' in window) {


    // http://stackoverflow.com/a/22337556/1338731
    // jquery plugin 'jquery.ready.fix.js'
    // @author Szymon Działowski
    ;(function ($) {
        $.fn.ready.old || $(function (r) {
            r = $.fn.ready;
            $.fn.ready = function (fn) {    $.isReady ? fn() : r(fn);    };
            $.fn.ready.old = r;
        });
    })(jQuery);

    //(function (fn) {
    //    (function call() {
    //        window.$ ? $(fn) : setTimeout(call, 200);
    //    }())
    //}(function () {
    //    var eid = parseInt('{{ enitity.id }}');
    //    alert('go: '+eid);
    //}));

    //var loadsecond = (function (load) {
    //    var loadfirst = false;
    //    return function () {
    //        loadfirst && load();
    //        loadfirst = true;
    //    }
    //})(function () {
    //    // do sth
    //});
    //google.setOnLoadCallback(loadsecond);
    //$(loadsecond);



    (function ($) {
          $.toUrl = function (str, delimiter) {
            delimiter = delimiter || '-'
            return str
              .toLowerCase()
              .replace(/ą|à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a")
              .replace(/ę|è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e")
              .replace(/ì|í|ị|ỉ|ĩ/g,"i")
              .replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o")
              .replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u")
              .replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y")
              .replace(/ł/g,"l")
              .replace(/ń/g,"n")
              .replace(/ś/g,"s")
              .replace(/ć/g,"c")
              .replace(/ż|ź/g,"z")
              .replace(/đ/g,"d")
              .replace(/[^a-z0-9]{1,}/g,delimiter)
              .replace(/^-?(.*?)-?$/,'$1')
          };
    })(jQuery);



    /**
     * @author Szymon Działowski
     *
     * Plugin to simplify sending json request to server
     *   - makes serialization inside
     *   - set default json headers
     *   - change http method as default from get to post
     * Example:
     *   $.json("url",{data:{test:"testval"}}).done(..)
     */
    (function ($, def) {
        function log(l) {
            try {
                console.log(l);
            } catch (e) {
            }
        }
        function isObject(obj) {
            var type = typeof obj;
            return type === "function" || type === "object" && !!obj;
        }
        $.json = function () {
            var i, ajax, t, o, k = 0, a = Array.prototype.slice.call(arguments, 0);
            for (i = 0; i < a.length; i += 1) {
                t = a[i];
                if (isObject(t)) {
                    k = i;
                    o = t;
                    break;
                }
            }

            if (o) {
                a[k] = $.extend({}, def, o, {
                    data: o.data && JSON.stringify(o.data)
                }) || {};
            }

            try {
                $.json.before();
            } catch (e) {}

            ajax = $.ajax.apply(this, a);

            (function () {
                var fallback = ajax.done;
                // zwracać json z serwera bez ustawiania nagłówka bo ie9 ma problemy jak ustawi się nagłówek application/json; charset=utf-8
                // i źle deserializuje, wpada w logikę realizującą CORS
                ajax.done = function (fn) {
                    fallback(function () {
                        var a = Array.prototype.slice.call(arguments, 0);
                        if (typeof a[0] === "string") {
                            log({
                                msg: "response zawiera string",
                                data: a[0]
                            });
                            try {
                                a[0] = JSON.parse(a[0]);
                            } catch (e) {
                                log("Json error: "+e);
                            }

                        } else {
                            // log("response nie zawiera stringu");
                        }

                        if (typeof fn === "function") {
                            fn.apply(this, a);
                        }
                    });
                    return ajax;
                };
            }());
            (function () {
                var fallback = ajax.done;
                // zwracać json z serwera bez ustawiania nagłówka bo ie9 ma problemy jak ustawi się nagłówek application/json; charset=utf-8
                // i źle deserializuje, wpada w logikę realizującą CORS
                ajax.always = function (fn) {
                    return fallback(function () {
                        var a = Array.prototype.slice.call(arguments, 0);

                        try {
                            $.json.always();
                        } catch (e) {

                        }

                        if (typeof fn === "function") {
                            fn.apply(this, a);
                        }
                    });
                };
            }());

            return ajax;
        };

        $.json.before = $.noop;
        $.json.always = $.noop;
    }(jQuery, {
        type        : "post",
        contentType : "application/json; charset=utf-8"
    }));

    (function ($) {
        $.json.before = function () {
            site.show();
        };
        $.json.always = function () {
            site.hide();
        };
        $.json.fail = function (jqXHR/* , textStatus , errorThrown*/) {
            site.error("A server error has occurred. " + site.status(jqXHR.status, "-"));
        };
    }(jQuery));

    (function ($) {
        if ($.ajaxstatus) {
            if (!window.site) {
                window.site = {};
            }
            $(function () {
                $.ajaxstatus({/*options*/},site);
            });
        }
    })(jQuery);


    (function ($) {
            $.fn.svgHasClass = function (name) {
                return (new RegExp('(\\s|^)'+name+'(\\s|$)')).test($(this).attr('class') || '');
            }
            $.fn.svgAddClass = function (name) {
                var t;
                return $(this).each(function () {
                    t = $(this);
                    if (!t.svgHasClass(name)) {
                        var cls = t.attr('class') || '';
                        cls += cls ? ' ' : '';
                        cls += name;
                        return t.attr('class', cls);
                    }
                });
            }
            $.fn.svgRemoveClass = function (name) {
                var t;
                return $(this).each(function () {
                    t = $(this);
                    if (t.svgHasClass(name)) {
                        var cls = t.attr('class') || '';
                        cls = cls.replace(
                            new RegExp('(\\s|^)'+name+'(\\s|$)'),' '
                        ).replace(/^\s+|\s+$/g, '');
                        return t.attr('class', cls);
                    }
                });
            }
            $.fn.svgHeight = function () {
                return $(this).get(0).getBBox().height;
            }
            $.fn.svgWidth = function () {
                return $(this).get(0).getBBox().width;
            }
            $.fn.svgRatio = function () {
                var t = $(this).get(0).getBoundingClientRect();
                return {
                    // http://stackoverflow.com/questions/22962416/how-to-get-the-actual-size-of-an-embedded-svg-element-if-its-auto-resizing
                    //w: t.
                    //h:
                }
            }
    }(jQuery));



    (function ($) {
        if ($.browser) {

            // detekcja czy to mobilka/tablet czy też desktop
            (function ($, b, r) {
                $.mobile = r.test(navigator.userAgent) || r.test(navigator.platform);
                //($.mobile || true) && $('body').addClass('_mobile');
                b.addClass($.mobile ? '_mobile' : '_desktop');
            })(jQuery, $('html'), /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile/i);

            // UWAGA: dorzucić klasę _html do html :UWAGA
            // klasa do sterowania stylami dla różnych przeglądarek
            // ._browser ._chrome ._webkit ._mozilla
            (function(b, i) {
                for (i in $.browser)
                    (i == 'version') || b.addClass('_' + i);
                $('html:first').addClass('_js').removeClass('_html');
            })($('body'));

        }
        else {
            log('Plugin $.browser is not included')
        }
    })(jQuery);

}
else {
    log('common.js: brak biblioteki jQuery');
}



if ('_' in window) {
    // zmiana adelimiterów silnika templatek ale tylko tam gdzie jest to potrzebne
    site.tmp = function () {
        var ret, tmp = _.templateSettings;
        _.templateSettings = { // podmianka delimiterów
            evaluate    : /\(:([\s\S]+?):\)/g,
            interpolate : /\(:=([\s\S]+?):\)/g,
            escape      : /\(:-([\s\S]+?):\)/g
        };
        ret =  _.template.apply(this, arguments);
        _.templateSettings = tmp; // odkładam domyślne delimitery na miejsce
        return ret;
    };


    /**
     * var tmp = _.template($(...).html());
     *
     * tmp.many([{raz: ..., dwa: ...},{raz: ..., dwa: ...}])
     *
     * lub z danymi wspólnymi
     * tmp.many([{raz: ..., dwa: ...},{raz: ..., dwa: ...}], {commonval: ...}); // w każdej iteracji na common zostanie nałożony pojedynczy zbiór danych
     *
     * dla wywołania pojedynczego wystarczy powiązać te dane ręcznie:
     * tmp({raz: ..., dwa: ..., commonval: ...});
     */
    (function (_old, _new) {
        if (_.template.new) {
            log('_.template.many already defined')
        }
        else {
            log('defining _.template.many');
            _old = _.template;
            _new = function () {
                var a = Array.prototype.slice.call(arguments, 0);
                var tmp = _old.apply(this, a);
                tmp.many = function (list, common) {
                    common = (typeof common === 'object') ? common : {};
                    var s = '';
                    _.each(list, function (d) {
                        s += tmp($.extend({}, common, d));
                    });
                    return s;
                }
                return tmp;
            }
            _new.old = _old;
            _.template = _new;
        }
    }())
}
else {
    log('Plugin underscore.js or lodash.js is not included');
}

if (window.Routing) {
    window.Router = {
        path : function (name, params) {
            return Routing.generate.call(Routing, name, params, false);
        },
        url : function (name, params) {
            return Routing.generate.call(Routing, name, params, true);
        }
    }
}

if ('sweetAlert' in window) {
    window.swal = sweetAlert
}







;