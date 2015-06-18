;(function ($, name) {
    function log(l) {try{console.log(l);}catch (e) {}};

    function getKey(data) {
        var key = data.split('-');
        key.shift();
        return key.join('-');
    }
    function findPrefix(k) {
        return k.replace(/^[.\s\S]*?id\s*=\s*["']([^"']+?)__name__[^"']+["'][.\s\S]*$/, '$1');
    }
    function getTitle(el) {
        if (el.is('input') || el.is('textarea')) {
            return el.val();
        }
        return el.text();
    }
    /**
     * Podawać nazwę atrybutu, na przykład: data-form-collection
     * Znaczenie mają atrybuty w html:
     * [data-form-collection]
     * [data-title]
     * [data-delete]
     * użycie patrz:
     *    create.html.twig
     *    edit.html.twig
     */
    $[name] = function (dataattr, opt) {
        opt = $.extend({
            title           : '[data-title]',
            del             : '[data-delete]',
            insertMethod    : 'prependTo',
            confirm : function (confirmed, name) {
                if (confirm('Usunąć element "'+name+'"?')) {
                    confirmed();
                }

//                return;
//
//                swal.confirm('Usunąć element "'+name+'"?', function () {
//                    t.remove();
//                });

            },
            added: function (row, list) {

            },
            existing: function (row, list) {

            },
            addedandexisting: function (row, list, addflat) { // addflat - czy odpalnoy na add czy był obiekt statycznie

            },
            remove: function (row, list, callback) {
                callback();
            }
        }, opt || {});

        var iter = $('['+dataattr+']');

        iter.each(function () {
            var t = $(this);

            var tdata = t.data(getKey(dataattr));

            var listselector = '['+tdata+']';

            var list = $(listselector);

            var tmp  = $('['+list.data(getKey(tdata))+']').html();
            log('tmp')
            log(tmp)

            var id = findPrefix(tmp);

            var reg = new RegExp('^'+id+'(\\d+).*$');

            t.on('click', function (e) {
                e.preventDefault();

                var max = 0;
                list.find('[id^="'+id+'"]').each(function () {
                    var id = $(this).attr('id');

                    id = parseInt(id.replace(reg, '$1'));
                    if (id > max) {
                        max = id
                    }
                });

                max += 1;

                var element = $('<div></div>').html(tmp.replace(/__name__/g, max)).find('> *')[opt.insertMethod](list);

                opt.added(element, list);
                opt.addedandexisting(element, list, true);
            });

            list.on('click', opt.del, function (e) {
                e.preventDefault();

                var t = $(this).parents(listselector+' *').last();

                var name = getTitle(t.find(opt.title));

                opt.confirm(function () {
                    opt.remove(t, list, function () {
                        t.remove();
                    });
                }, name);
            });

            list.find('> *').each(function () {
                var t = $(this);
                opt.existing(t, list);
                opt.addedandexisting(t, list);
            });
        });
    };
})(jQuery, 'formcollection');