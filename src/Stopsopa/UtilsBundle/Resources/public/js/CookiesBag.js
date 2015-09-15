window.CookiesBag = function (ckey, defopt) {
    var t;
    var flipget = (function (w, c) {
        for (var i in c)
            c[c[i]] = i;
        return function (s) {
            s = s.split('');
            for (var i = 0, l = s.length ; i < l ; ++i )
                if (c[s[i]]) s[i] = c[s[i]];
            return s.join('');
        };
    })(this, {
        ' ' : '.',
        '"' : '!',
        ':' : '-',
        '{' : '(',
        '}' : ")",
        '?' : "_",
        '&' : "~",
    });
    this.write = cookies.enabled();
    var that = this;
    function get() {
        t = cookies.get(ckey);
        try {
            return JSON.parse(flipget(t));
        }
        catch (e) {
            return defopt;
        }
    }
    function set(data) {
        if (!that.write) {
            return false;
        }
        if (typeof data !== 'string') {
            data = JSON.stringify(data);
        }
        if (!cookies.set(ckey, flipget(data), Infinity, '/')) {
            throw "Unable to set cookie: '"+ckey+"' in getter";
        }
        return true;
    }
    return {
        get: function (key) {
            if (key) {
                t = this.get();
                return t[key];
            }
            if (that.write) {
                return get();
            }
            else {
                return defopt;
            }
        },
        set: function (key, val) {
            t = this.get();
            t[key] = val;
            set(t);
            return this;
        }
    }
};