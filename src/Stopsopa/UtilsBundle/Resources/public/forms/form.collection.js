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
            add: function (row, list) {

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

                var element = $('<div></div>').html(tmp.replace(/__name__/g, max)).find('> *').appendTo(list);

                opt.add(element, list);
            });

            list.on('click', '[data-delete]', function (e) {
                e.preventDefault();

                var t = $(this).parents(listselector+' *').last();

                var name = getTitle(t.find('[data-title]'));

                opt.confirm(function () {
                    opt.remove(t, list, function () {
                        t.remove();
                    });
                }, name);
            })
        });
    };
})(jQuery, 'formcollection');