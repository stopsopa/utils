// jQuery fix for ie9
//<div style="border: 5px solid red; padding: 5px;">
//    <input type="text" placeholder="test" />
//    <button onclick="alert($(this).prev().val())">js</button>
//    <button onclick="alert($(this).prev().prev().get(0).value)">native</button>
//</div>

// w zasadzie porównywanie value z wartością placeholder nie jest najlepszym
// pomysłem bo jeli użytkownik wpisze faktycznie wartość taką jak w placeholder to .val() zwróci pusty string,
// to nie jest też poprawne ale lepsze jak zwracanie placeholdera
;(function () {
    // detekcja ie9 jeszcze do przemyślenia, można tak na przykłąd http://stackoverflow.com/a/10965091/1338731
    var version = true;
    function isIe () {
        if (version === true) {
            var myNav = navigator.userAgent.toLowerCase();
            version = (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
        }
        return version;
    }
    window.isIe = isIe;

    if (isIe() == 9) {
        log('to jest ike9');

        if (window.jQuery) {
            ;(function ($) {
                var old = $.fn.val;
                var t,v,a;
                $.fn.val = function () {
                    a = Array.prototype.slice.call(arguments, 0);
                    t = $(this);
                    v = old.apply(this, a);

                    if (v === t.attr('placeholder'))
                        return '';

                    return v;
                }
                $.fn.val.old = old;
            })(jQuery);
        }
    }
    else {
        log('to nie jest ie9');
    }


})();