/*!
 * @author Szymon Działowski
 * @ver 1.0 2013-06-11
 * @doc https://bitbucket.org/stopsopa/ajaxstatus
 */
(function ($,pgn_name) {
  var w = $(window),
      ready = true,
      di = 0,
      dl,  // dotst interval handler;
      o;
  function dsta(_i) {
    if (!di) {
      _i = 0;    
      (function _loop(){
        o.dots.html('<span>'+o.dotsstr.substring(0,_i%dl)+'</span><span class="_invisible">'+o.dotsstr.substring(_i++%dl)+'</span>')
        di = setTimeout(_loop,o.dotsspeed);                 
        center();
      })();
    }
  }
  function dsto () {
    clearInterval(di);
    di = false;
  }
  function center(_l) {
    _l = ( (w.width()/2)-(o.box.width()/2) ) + 'px';
    o.box.css({left:_l});
  };
  $[pgn_name] = function (oo,mount) {    
    o = $.extend({
      box          : '#_ajax-loader',
      message      : '._ajax-message',
      dots         : '._ajax-dots',
      loading      : 'Loading',
      showcls      : '_show',
      messagespeed : 1800,
      errspeed     : 3500,
      deferror     : 'error',
      dotsstr      : '......', 
      dotsspeed    : 600 // szybkość dodawania kropek 
    },oo||{});
    
    o.box = $(o.box).length ? $(o.box) : $('<div></div>').attr('id',o.box.substring(1)).prependTo('body');    
    o.box         = $(o.box).html('<span class="_loader"></span>\
                                  <span class="_ajax-message"></span>\
                                  <span class="_ajax-dots"></span>');
    o.message     = o.box.find(o.message);
    o.dots        = o.box.find(o.dots);
    o.defloading = o.box.data('loading') || o.loading || 'Loading';
    dl         = o.dotsstr.length+1;
    if (typeof mount != 'undefined') {
      for (var i in $[pgn_name]) {
        mount[i] = $[pgn_name][i];
      }
    }
  };  
  $[pgn_name].show = function (error) {
    dsto();
    ready = false;
    error || o.box.removeClass('error');
    o.box.data('lock',$.now()).addClass(o.showcls).css('opacity', 1);
    o.message.html(o.defloading);
    center();
    dsta();
  };
  $[pgn_name].hide = function () {
    ready = true;
    o.box.removeClass(o.showcls+' error').data('lock',$.now()).css('opacity', 0);
    dsto();
  };
  
  $[pgn_name].message = function (m,time,err) {    
    dsto();
    ready = false;
    var _time = $.now(),
        that = this;
    setTimeout(function () { // dzięki opóźnieniu message ma trochę wyższy priorytet
      err ? o.box.addClass('error') : o.box.removeClass('error');
      o.box.css({display:'block',opacity:0}).data('lock',_time).addClass(o.showcls)
      o.message.html(m || o.defloading);
      o.box.css('opacity',1).show();
      center();
      dsta();
      if (typeof time == 'undefined') { // jeśli nie podany to domyślny czas
        setTimeout(function show() {
            (o.box.data('lock') == _time) && that.hide(); 
        }, err ? o.errspeed : o.messagespeed);
      }
      else if (time !== false) { // jeśli liczba to używamy jako milisekund
        setTimeout(function show() {
            (o.box.data('lock') == _time) && that.hide(); 
        }, time);
      }
    },10);
  };
  $[pgn_name].error = function (m,time) {
    var that = this;
    ready = false;
    setTimeout(function () {
      that.message(m || o.deferror,time,true);        
    },50);
  };
})(jQuery,'ajaxstatus');