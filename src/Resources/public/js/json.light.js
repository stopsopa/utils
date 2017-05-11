/**
 * @author Szymon Działowski
 * ver 1.0 19.09.2015
 * JSON.s() - stringify
 * JSON.p() - parse
 * porównanie dwóch zestawów stringów urli:
 * przed : [(!t!-!c!*!d!-[(!t!-!w!*!d!-!<br./>f<br./>ds<br./>a<br./>\n<ul>\n<li>raz</li>\n<li>dwa</li>\n<li>trzy</li>\n</ul>\n<br./>koniec<br./> <br./><br./><em>fdsafdsa<br./></em>!*!h!-288*!g!-3)*(!t!-!v!*!d!-(!s!-(!b!-1*!o!-47*!c!-1*!v!-1)*!e!-(!b!-1*!o!-47*!c!-1*!v!-4))*!i!-!t!*!m!-!p!)])*(!t!-!c!*!d!-[(!t!-!v!*!d!-(!s!-(!b!-1*!o!-47*!c!-1*!v!-1)*!e!-(!b!-1*!o!-47*!c!-1*!v!-1))*!i!-!n!*!m!-!n!)*(!t!-!w!*!d!-!drugi.wysiwyg!*!h!-200)])]
 * po    : [(t-!c!*d-[(t-!w!*d-!<br./>f<br./>ds<br./>a<br./>\n<ul>\n<li>raz</li>\n<li>dwa</li>\n<li>trzy</li>\n</ul>\n<br./>koniec<br./> <br./><br./><em>fdsafdsa<br./></em>!*h-272)*(t-!v!*d-(s-(b-1*o-47*c-1*v-1)*e-(b-1*o-47*c-1*v-4))*i-!n!*m-!p!)])*(t-!c!*d-[(t-!v!*d-(s-(b-1*o-47*c-1*v-1)*e-(b-1*o-47*c-1*v-4))*i-!t!*m-!n!)*(t-!w!*d-!drugi.wysiwyg!*h-179)])]
 */
JSON.s = (function () {
    function isObject(obj) {
        var type = typeof obj;
        return type === 'function' || type === 'object' && !!obj;
    };
    function isArray(obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    };
    var k = /^[a-z_][_a-z\d]*$/i, c = JSON.stringify;
    function key (key) {
        return k.test(key) ? key : c(key);
    }
    return function cycle(d) {
        if (isObject(d)) {
            var i, l, t, a = [], s = '';
            if (isArray(d)) {
                s += '[';
                for ( i = 0, l = d.length ; i < l ; i += 1 ) {
                    t = d[i];
                    if (typeof t === 'undefined') {
                        a.push('null');
                    }
                    else if (isObject(t)) {
                        a.push(cycle(t));
                    }
                    else {
                        a.push(c(t));
                    }
                }
                s += a.join(',')+']';
            }
            else {
                s += '{';
                for (i in d) {
                    t = d[i];
                    if (typeof t === 'undefined') {
                        a.push(key(i)+':null');
                    }
                    else if (isObject(t)) {
                        a.push(key(i)+':'+cycle(t));
                    }
                    else {
                        a.push(key(i)+':'+c(t));
                    }
                }
                s += a.join(',')+'}';
            }
            return s;
        }
        return c(d);
    }
})();

JSON.p = function (d) {
    var t = typeof d;
    if (t === 'string') {
        return eval('('+d+')');
    }
    throw "data is not string: "+t;
}