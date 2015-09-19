/**
 * @author Szymon Działowski
 * ver 1.0 19.09.2015
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