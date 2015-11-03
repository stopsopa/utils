;
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





//window.site = {};
//$(function () {
//    $.ajaxstatus({/*options*/}, window.site);
//});

if (!window.site) {
    window.site = {};
}


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


if ('jQuery' in window) {
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
        type: "post",
        contentType: "application/json; charset=utf-8"
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
    log('common.js: brak biblioteki underscore.js');
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