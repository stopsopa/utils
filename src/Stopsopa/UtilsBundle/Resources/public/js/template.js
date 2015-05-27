window.site || (window.site = {}); // main object for custom tools

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
