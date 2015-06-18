;(function ($, bagname) {

    function log(l) {try{console.log(l);}catch (e) {}};

    var s = (function (to) {  // narzędzie do tłumaczeń kodów błedów http
        to || (to = {});
        var t,s = "0:Request aborted|100:Continue|101:Switching Protocols|102:Processing|200:OK|201:Created|202:Accepted|203:Non-Authoritative Information|204:No Content|205:Reset Content|206:Partial Content|207:Multi-Status|300:Multiple Choices|301:Moved Permanently|302:Found|303:See Other|304:Not Modified|305:Use Proxy|307:Temporary Redirect|400:Bad Request|401:Unauthorized|402:Payment Required|403:Forbidden|404:Not Found|405:Method Not Allowed|406:Not Acceptable|407:Proxy Authentication Required|408:Request Timeout|409:Conflict|410:Gone|411:Length Required|412:Precondition Failed|413:Request Entity Too Large|414:Request-URI Too Long|415:Unsupported Media Type|416:Requested Range Not|Satisfiable|417:Expectation Failed|422:Unprocessable Entity|423:Locked|424:Failed Dependency|426:Upgrade Required|500:Internal Server Error|501:Not Implemented|502:Bad Gateway|503:Service Temporarily Unavailable|504:Gateway Timeout|505:HTTP Version Not Supported|506:Variant Also Negotiates|507:Insufficient Storage|509:Bandwidth Limit Exceeded|510:Not Extende".split("|");
        // var i, t, s = "0:Request aborted|100:Continue|101:Switching Protocols|102:Processing|200:OK|201:Created|202:Accepted|203:Non-Authoritative Information|204:No Content|205:Reset Content|206:Partial Content|207:Multi-Status|300:Multiple Choices|301:Moved Permanently|302:Found|303:See Other|304:Not Modified|305:Use Proxy|307:Temporary Redirect|400:Bad Request|401:Unauthorized|402:Payment Required|403:Forbidden|404:The given page does not exist.|405:Method Not Allowed|406:Not Acceptable|407:Proxy Authentication Required|408:Request Timeout|409:Conflict|410:Gone|411:Length Required|412:Precondition Failed|413:Request Entity Too Large|414:Request-URI Too Long|415:Unsupported Media Type|416:Requested Range Not|Satisfiable|417:Expectation Failed|422:Unprocessable Entity|423:Locked|424:Failed Dependency|426:Upgrade Required|500:Internal Server Error|501:Not Implemented|502:Bad Gateway|503:Czyszczenie cache, proszę spróbować za kilka sekund|504:Gateway Timeout|505:HTTP Version Not Supported|506:Variant Also Negotiates|507:Insufficient Storage|509:Bandwidth Limit Exceeded|510:Not Extende".split("|");
        to.statusCodes = {};
        for (var i = 0; i < s.length; i += 1) {
            t = s[i].split(":");
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
    }());

    function humanFileSize(bytes, si) { // http://stackoverflow.com/a/14919494/1338731
        //(typeof si === 'undefined') && (si = true)
        var thresh = si ? 1000 : 1024;
        if(Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        var units = si
            ? 'kB,MB,GB,TB,PB,EB,ZB,YB'.split(',')
            : 'KiB,MiB,GiB,TiB,PiB,EiB,ZiB,YiB'.split(',');
        var u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while(Math.abs(bytes) >= thresh && u < units.length - 1);

        return bytes.toFixed(2)+' '+units[u];
    }

    function wrap(d) {
        d.human = humanFileSize(d.size);
        return d;
    }

    function debounce(fn, delay) {
      var timer = null;
      return function () {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
          fn.apply(context, args);
        }, delay);
      };
    };

    function bindDelete(button, o) {
        button.click(function () {
            var t = $(this);


            var url = t.data('delete');
            if (url) {
                t.attr('disabled', 'disabled');

                $.ajax(url)
                    .done(function (json) {
                        if (json.ok) {
                            return $(this).closest('['+o.oneattr+']').remove();
                        }

                        // jakiś błąd
                    })
                    .always(function () {
                        t.removeAttr('disabled');
                    })
            }
            else {
//                log('[data-delete] no url specified');
            }
        });
    }
    function unique(pattern) {
      pattern || (pattern = 'xyxyxy');
      return pattern.replace(/[xy]/g,
        function(c) {
          var r = Math.random() * 16 | 0,
          v = c == 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
        });
    }

    function process() {
        var list = [];
        var that = this;
        this.clear = function () {
            list = [];
        };
        this.add = function (size, file) {
            if (!s(file)) {
                return;
                return log('no file specified file');
            }
            for (var i = 0, l = list.length ; i < l ; i += 1 ) {
                if (list[i].file === file) {
                    return that;
                }
            }
            list.push({
                size    : size,
                file    : file,
                loaded  : 0
            });
            return that;
        };
        this.remove = function (file) {
            if (!s(file)) {
                return ;
                return log('no file specified remove');
            }
            for (var i = 0, l = list.length ; i < l ; i += 1 ) {
                if (list[i].file === file) {
                    list.splice(i, 1);
                    return that;
                }
            }
            return that;
        };
        this.pending = function () {
            return !!list.length;
        }
        this.get = function () {

            if (!list.length) {
                return null;
            }

            var lo = 0, sum = 0;
            for (var i = 0, l = list.length ; i < l ; i += 1 ) {
                sum += list[i].size;
                lo  += list[i].loaded;
            }

            var p = parseInt((lo / sum) * 100);

            return p > 100 ? 100 : p;
        };
        function s (file) {
            return file && file.size && file.name
        }
        this.step = function (percent, file) {
            if (!s(file)) {
                return
                return log('no file specified step or is not file')
            }
            var c = false;
            for (var i = 0, l = list.length ; i < l ; i += 1 ) {

                if (list[i].file === file) {
                    c = list[i];
                    break;
                }
            }

            if (!c) {
                return this;
            }

            c.loaded =  c.size * ( percent / 100 );

            return this.get();
        };
    };

    function renderError(error, file, list, tmperror, o) {
        file.error = error;
        $('<div></div>').html(tmperror(wrap(file))).find('> *').attr(o.oneattr, '')[o.insertMethod](list)
    }
    function getKey(data) {
        var key = data.split('-');
        key.shift();
        return key.join('-');
    }

    function find(parent, selector) {

        if (selector.indexOf('**') + 1) {
            return parent.find(selector.replace(/\*\*\s*/g, ''));
        }

        return $(selector);
    }

    function get(param, _this, arg1, arg2, argmore) {

        if (!param) {
            return false;
        }

        if (typeof param === 'function') {
            var a = Array.prototype.slice.call( arguments, 1 );
            return param.apply($(a[0]), a.slice(1));
        }

        return param;
    }

    function debounce(fn, delay) {
      var timer = null;
      return function () {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
          fn.apply(context, args);
        }, delay);
      };
    };

    function fix() {
        return;
        $('[name="__tmp__"]').each(function () {
            var tt = $(this);
            var p = tt.closest('.js')
            var name = p.find('input:file').attr('name');
            if (typeof name === 'string') {
                log('name');
                log(name);
                var k = name.split(/\[/)
                var t = k[0];
                k[0] = '';
    //
//                k = '_blueimp['+t+']'+k.join('[');
                var hidden = name.replace(/^(.*\[)file(\])$/, '$1path$2');

                tt.attr('name', hidden);


//                p.find('input:hidden').each(function () {
//                    var t = $(this);
//                    if (t.attr('name') === hidden) {
//                        t.attr('name', hidden);
//                        p.find('[data-hidden]').remove();
//                    }
//                });
//
//                p.find('[data-hidden]').attr('name', hidden);
            }
        });
    }

    $.fn.blueimp = function (oo) {

        return $(this).each(function () {
            var o = oo;
            var main = $(this).addClass('js');
            var pall = new process();

            if (typeof o === 'string') {
                if (o == 'destroy') {

                    var bag = main.data(bagname);

                    if (bag) {

                        $(document)
                            .off('dragenter', bag.dragenter)
                            .off('dragover', bag.leave)
                            .off('drop', bag.leave)


                        find(main, o.allsend).off('click', bag.sendall);

                        main.removeData(bagname).fileupload('destroy');
                    }

                }

                return main;
            }

            var bag = main.data(bagname);

            if (bag) {
                return main;
                log('Blueimp: uploader is already initialized on this DOM element')
            }

            bag = {};

            var o = $.extend({
                oneattr             : 'data-element',
                onedatastart        : '** [data-startone]',
                onecancel           : '** [data-cancelone]',
                allsend             : '** [data-sendall]',
                del                 : '** [data-delete]',
                tmpready            : '** [data-tmp-ready]',
                tmpdone             : '** [data-tmp-done]',
                tmperror            : '** [data-tmp-error]',

                list                : '** [data-list]',
                progress            : '** [data-bar]',
                progresslabel       : '** [data-label]',
                allprogress         : '** [data-allbar]',
                allprogresslabel    : '** [data-alllabel]',

                dropzone            : '** [data-dropzone]',
                pastezone           : '** [data-pastezone]',

                action              : false, // weźnie automatycznie z action z formularza
                maxsize             : 'data-sizelimit', // lub podać liczbę w bajtach
                insertMethod        : 'prependTo',

                multiple            : function (main) {
                    log('inside multip;le')
                    log(arguments)
                    var i = main.find('input:file').addClass('red');

                    if (i.length) {
                        return i.prop('multiple');
                    }

                    return true;
                }
            }, o || {});

            var processing          = false;

            try {
                var tmpready            = _.template(find(main, o.tmpready).html());

            }
            catch (e) {
                log('find template error: '+o.tmpready)
                log(find(main, o.tmpready))
                log(find(main, o.tmpready).length)
            }
            try {
                var tmpdone             = _.template(find(main, o.tmpdone).html());

            }
            catch (e) {
                log('find template error: '+o.tmpdone)
                log(find(main, o.tmpdone))
                log(find(main, o.tmpdone).length)
            }
            try {
                var tmperror            = _.template(find(main, o.tmperror).html());

            }
            catch (e) {
                log('find template error: '+o.tmperror)
                log(find(main, o.tmperror))
                log(find(main, o.tmperror).length)
            }
            var list                = find(main, o.list);
            var progress            = find(main, o.progress);
            var progresslabel       = find(main, o.progresslabel);
            var progressall         = find(main, o.allprogress);
            var progressalllabel    = find(main, o.allprogresslabel);
            var dropzone            = find(main, o.dropzone);
            var pastezone           = find(main, o.pastezone);

            if (o.action) {
                var action              = o.action;
            }
            else {
                var action              = main.closest('form').attr('action');
            }

            if (typeof o.maxsize == 'string') {
                var maxsize             = main.find('['+o.maxsize+']').data(getKey(o.maxsize))
            }
            else {
                var maxsize             = o.maxsize;
            }

            var sendall             = find(main, o.allsend);

            sendall.addClass('red');

            var multiple = get(o.multiple, main, main);
            log('mul z get: ')
            log(multiple)

            var limitfiles = false;
            if (!multiple) {
                limitfiles = 1;
            }
            if (multiple > 1) {
                limitfiles = multiple;
            }

            var filecounter = 0;
            var maxdebounce = debounce(function () {
                filecounter = 0;
            }, 20);

            var opt = {
                url: action,
                dataType: 'json',
                sequentialUploads: true, // https://github.com/blueimp/jQuery-File-Upload/wiki/API#initialization
                    //limitConcurrentUploads: 1,  // ignored, more here: https://github.com/blueimp/jQuery-File-Upload/wiki/Options#limitconcurrentuploads
                dropZone: dropzone,
                pastezone: pastezone,
//                paramName: 'attachments[]',

//                dla podglądu zjęcia vvv
//
                disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator && navigator.userAgent),
                imageMaxWidth: 800,
                imageMaxHeight: 800,
                imageCrop: false, // Force cropped images
                autoUpload: false,
//                dla podglądu zjęcia ^^^

                add: function (e, data) {
                    if (limitfiles) {
                        filecounter += 1;
                        maxdebounce();

                        if (filecounter > limitfiles) {
                            return log('error, max filecounter: '+filecounter)
                        }
                        if (limitfiles === 1) {
                            // jeśli limit jest na jeden plik to przed wrzucaniem kolejnoego czyszczę listę
                            list.empty();
                        }
                    }


                    var file = data.files[0]; // ???

                    if (maxsize > file.size) {
                        data.context = $('<div></div>').html(tmpready(wrap(file))).find('> *').attr(o.oneattr, '')[o.insertMethod](list);

                        data.context.data(bagname, file);

                        var submit = find(data.context, o.onedatastart);

                        if (submit.length) {
                            submit.click(function () {
                                pall.pending() && pall.add(file.size, file);

                                var t = $(this);

                                t.attr('disabled', 'disabled');

                                processing = data.submit()
//                                    .done(function () { // https://github.com/blueimp/jQuery-File-Upload/wiki/API#initialization
//                                        log('this is normal ajax object')
//                                    })
                                    .fail(function (jqXHR, textStatus, errorThrown) {
                                        pall.remove(file);
                                        renderError(s.status(jqXHR.status), file, list, tmperror, o);
                                    })
                                ;
                                data.context.data(bagname).jqxhr = processing;
                            });
                        }
                        else {
                            pall.pending() && pall.add(file.size, file);
                            data.submit();
                        }

                        find(data.context, o.onecancel).click(function () {
                            data.context.data(bagname).jqxhr.abort();
                            $(this).closest('['+o.oneattr+']').remove();
                        });
                    }
                    else {
                        renderError("Plik jest zbyt duży - "+humanFileSize(file.size)+", dozwolona wielkość pliku to "+humanFileSize(maxsize), file, list, tmperror, o);
                    }

                },
                done: function (e, data) {

                    fix();

                    // ta pętla w sumie nie ma sensu bo tutaj będzie zawsze obsługa jednego pliku
                    // ale upakowane jest to tak żę wygląda jakby mogła tu przyjśc lista danych
                    $.each(data.result.files, function (index, file) {

                        var f = data.context.data(bagname);
                        pall.step(100, f);
                        var p = data.context.closest('.js');
                        var name = p.find('input:file').attr('name');
                        var k = name.split('[')
                        var t = k[0];
                        k[0] = '';
//
//                        k = '_blueimp['+t+']'+k.join('[');
                        k = name.replace(/^(.*\[)file(\])$/, '$1path$2');

                        file.hidden = '<input type="hidden" name="'+ k +'" value="'+ file.hidden +'" />';

                        var tmp = $('<div></div>').html(tmpdone(file)).find('> *').attr(o.oneattr, '').attr('x'+$.now(), 'tst');
                        data.context.replaceWith(tmp);
                        data.context = tmp;

                        bindDelete(find(data.context, o.del), o);
                    });
                },
//                drop: function () {
//                    log('drop')
//                },
//                change: function () {
//                    log('change')
//                },
                progressall: function (e, data) {
                    var p = pall.get();
                    if (p !== null) {
                        progressall.css('width', p + '%');
                        progressalllabel.html(p + ' %');
                    }
                    else {
                        log('nie ma elementów w progress bar');
                    }
                },
                progress: function (e, data) {
                    var prog    = find(data.context, o.progress);
                    var label   = find(data.context, '[data-label]')
                    var p = parseInt(data.loaded / data.total * 100, 10);

                    prog.css('width', p + '%');
                    label.html(p + ' %');

                    var f = data.context.data(bagname);

                    pall.step(p, f);
                }
            };

            if (limitfiles) {
                log('multiple '+limitfiles)
                opt.limitMultiFileUploads = limitfiles;
                opt.maxNumberOfFiles = limitfiles;
            }
            else {
                log('multiple true')
            }

            main.fileupload(opt);

            (function () {
                function trigger() {
                    var t = $(this).attr('disabled', 'disabled');

                    var button = find(main, o.onedatastart+':not(:disabled):first');

                    if (button.length) {
                        button.trigger('click');

                        processing
                            .always(function () {
//                                log('apply')
                                trigger.apply(t);
                            })
                            .fail(function () {
//                                log('fail')
                                button.removeAttr('disabled');
                            })
                    }
                    else {
                        pall.clear();
                        t.removeAttr('disabled');
                        processing = false;
                    }
                }

                sendall.on('click', function () {
                    pall.clear();

                    find(main, o.onedatastart+':not(:disabled)').each(function () {
                        var c = $(this).closest('['+o.oneattr+']')
                        var f = c.data(bagname);
                        if (f) {
//                            log('add file')
                            pall.add(f.size, f);
                        }
                        else {
//                            log('file not found');
                        }
                    });
                    trigger.apply(this, arguments);
                });

                bag.sendall = trigger;
            })();

            bag.dragenter = function (e) {
                dropzone.addClass('active');
            };

            bag.leave = debounce(function (e) { // https://developer.mozilla.org/en-US/docs/Web/Events/dragleave
                // https://developer.mozilla.org/en/docs/Using_files_from_web_applications#Selecting_files_using_drag_and_drop
                dropzone.removeClass('active');
            }, 400);

            $(document)
                .on('dragenter', bag.dragenter)
                .on('dragover', bag.leave)
//                .on('dragleave', leave)
                .on('drop', bag.leave)
//                drag, dragdrop, dragend, dragenter, dragexit, draggesture, dragleave, dragover, dragstart, drop
            fix();
            return main.data(bagname, bag);
        });
    }
})(jQuery, 'blueimpbag');