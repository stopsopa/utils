// wymaga jquery
// <script type="text/javascript"></script>
// 'bundles/stopsopautils/js/common.js'

function log() {
    try {
        console.log.apply(this, arguments);
    } catch (e) {

    }
}
function error() {
    try {
        console.error.apply(this, arguments);
    } catch (e) {

    }
}

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
                            log("response nie zawiera stringu");
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
}
else {
    log('common.js: brak biblioteki jQuery');
}



if ('_' in window) {
    // zmian adelimiterów silnika templatek ale tylko tam gdzie jest to potrzebne
    site.tmp = function () {
        var tmp = _.templateSettings
        var own = {
            evaluate    : /\[\[([\s\S]+?)\]\]/g,
            interpolate : /\[\[=([\s\S]+?)\]\]/g,
            escape      : /\[\[-([\s\S]+?)\]\]/g
        }
        _.templateSettings = own; // podmianka delimiterów
        ret =  _.template.apply(this, arguments);
        _.templateSettings = tmp; // odkładam domyślne delimitery na miejsce
        return ret;
    }
}
else {
    log('common.js: brak biblioteki underscore.js');
}


if ('sweetAlert' in window) {
    swal = sweetAlert
}


