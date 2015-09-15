// https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie
// cookies.set("test0", "Hello world!");
// cookies.set("test1", "Unicode test: \u00E0\u00E8\u00EC\u00F2\u00F9", Infinity);
// cookies.set("test2", "Hello world!", new Date(2020, 5, 12));
// cookies.set("test3", "Hello world!", new Date(2027, 2, 3), "/blog");
// cookies.set("test4", "Hello world!", "Wed, 19 Feb 2127 01:04:55 GMT");
// cookies.set("test5", "Hello world!", "Fri, 20 Aug 88354 14:07:15 GMT", "/home");
// cookies.set("test6", "Hello world!", 150);
// cookies.set("test7", "Hello world!", 245, "/content");
// cookies.set("test8", "Hello world!", null, null, "example.com");
// cookies.set("test9", "Hello world!", null, null, null, true);
// cookies.set("test1;=", "Safe character test;=", Infinity);

// alert(cookies.keys().join("\n"));
// alert(cookies.get("test1"));
// alert(cookies.get("test5"));
// cookies.rm("test1");
// cookies.rm("test5", "/home");
// alert(cookies.get("test1"));
// alert(cookies.get("test5"));
// alert(cookies.get("unexistingCookie"));
// alert(cookies.get());
// alert(cookies.get("test1;="));
var cookies = {
    get: function (sKey) {
      if (!sKey) { return null; }
      return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    },
    set: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
      if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
      var sExpires = "";
      if (vEnd) {
        switch (vEnd.constructor) {
          case Number:
            sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
            break;
          case String:
            sExpires = "; expires=" + vEnd;
            break;
          case Date:
            sExpires = "; expires=" + vEnd.toUTCString();
            break;
        }
      }
      document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
      return true;
    },
    rm: function (sKey, sPath, sDomain) {
        if (!this.has(sKey)) { return false; }
        document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "");
        return true;
    },
    has: function (sKey) {
        if (!sKey) { return false; }
        return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
    },
    keys: function () {
        var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
        for (var nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
        return aKeys;
    },
    enabled: function () { // http://sveinbjorn.org/cookiecheck
        var cookieEnabled = (navigator.cookieEnabled) ? true : false;
        if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled) {
            document.cookie="testcookie";
            cookieEnabled = (document.cookie.indexOf("testcookie") != -1) ? true : false;
        }
        return cookieEnabled;
    }
};