// Sent by php : "helpers" array , siteurl string
var fhpc_selectionMode = false;
var fhpc_adminMode = false;
var bounceTimer;
var fhpc_timer;
var documentBody;
var fhpc_selectedElement = false;
fhpc_username = fhpc_username[0];
fhpc_postID = fhpc_postID[0];

var fhpc_isMobile = {
    Android: function () {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function () {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function () {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function () {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function () {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function () {
        return (fhpc_isMobile.Android() || fhpc_isMobile.BlackBerry() || fhpc_isMobile.iOS() || fhpc_isMobile.Opera() || fhpc_isMobile.Windows());
    }
};

jQuery(document).ready(function () {
    sessionStorage.setItem("fhpc_username", fhpc_username.toString().toLowerCase());
    if (sessionStorage.getItem("fhpc_username") && sessionStorage.getItem("fhpc_username") != "") {

        jQuery.each(helpers, function () {
            jQuery.each(this.items, function () {
                if (this.page.indexOf("USERNAME") > 0 && sessionStorage.getItem("fhpc_username") != '') {
                    this.page = this.page.replace('USERNAME', sessionStorage.getItem("fhpc_username"));
                }
            });
            if (this.page.indexOf("USERNAME") > 0 && sessionStorage.getItem("fhpc_username") != '') {
                this.page = this.page.replace('USERNAME', sessionStorage.getItem("fhpc_username"));
            }
        });
    }
    
    if (typeof(fhpc_postID) !== "undefined" && fhpc_postID != '') {
        jQuery.each(helpers, function () {
            jQuery.each(this.items, function () {
                if (this.page.indexOf("POSTID") > 0 && fhpc_postID != '') {
                    this.page = this.page.replace('POSTID', fhpc_postID);
                }
            });
            if (this.page.indexOf("POSTID") > 0 && fhpc_postID != '') {
                this.page = this.page.replace('POSTID', fhpc_postID);
            }
        });
    }
});
jQuery(window).load(function () {
    fhpc_initAssistants();
});


function fhpc_isIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function fhpc_initAssistants() {
    // documentBody = ((jQuery.browser.chrome) || (jQuery.browser.safari)) ? document.body : document.documentElement;
    documentBody = 'html,body';
    if (window.parent && (window.parent.document.getElementById('fhpc_selectDomFrame'))) {
    } else {
        if (fhpc_isIframe() && jQuery('.estimationForm_frameSC', window.parent.document).length > 0) {
        } else {
            fhpc_initHelpers();
        }
    }
    jQuery('body *').click(fhpc_clickItem);
    jQuery(window).resize(function () {
        jQuery('#fhpc_overlay').attr({
            width: jQuery(document).outerWidth(),
            height: jQuery(document).outerHeight()
        }).css({
            width: jQuery(document).outerWidth(),
            height: jQuery(document).outerHeight()
        });
    });
}

function fhpc_clickItem(e, mode) {
    if (fhpc_selectionMode) {
        var self;
        if (!mode) {
            self = this;
            e.preventDefault();
        } else {
            self = e;
        }
        if (jQuery(self).is('option')) {

        } else {
            if (jQuery(self).children().length == 0 || jQuery(self).is('img') || jQuery(self).is('a') || jQuery(self).is('button') || jQuery(self).is('select') || jQuery(self).is('iframe') || jQuery(self).is('.mce-tinymce')) {
                fhpc_selectedElement = true;

                if (jQuery(self).is('a') && jQuery(self).find('img').length > 0) {
                    jQuery(self).find('img').addClass('fhpc_selectedElement');
                    jQuery('.fhpc_selectedElement').not(jQuery(self).find('img')).removeClass('fhpc_selectedElement');
                    window.parent.fhpc_itemSelected(jQuery(self).find('img').get(0));
                } else {
                    jQuery(self).addClass('fhpc_selectedElement');
                    jQuery('.fhpc_selectedElement').not(jQuery(self)).removeClass('fhpc_selectedElement');
                    window.parent.fhpc_itemSelected(self);
                }

            }
        }
    }
}

function fhpc_isAnyParentFixed($el, rep) {
    if (!rep) {
        var rep = false;
    }
    if ($el.closest('.x-sidebar').length > 0) {
        rep = true;
    }
    try {
        if ($el.parent().length > 0 && $el.parent().css('position') == "fixed") {
            rep = true;
        }
    } catch (e) {

    }

    if (!rep && $el.parent().length > 0) {
        rep = fhpc_isAnyParentFixed($el.parent(), rep);
    }
    return rep;
}

function fhpc_initHelpers() {
    var chkHelper = false;
    var chkHelperExist = false;
    if (localStorage.getItem('helper')) {
        jQuery.each(helpers, function (i) {
            var helper = helpers[i];
            if (fhpc_isMobile.any() && helper.mobileEnabled == "0") {
            } else if ((fhpc_isMobile.any() === null || !fhpc_isMobile.any()) && helper.onlyMobile == "1") {
            } else {
                var url = document.location.href;
                if (url.indexOf('#') > 0) {
                    url = document.location.href.substr(0, document.location.href.lastIndexOf('#'));
                }
                if (siteurl.substr(siteurl.length - 1, 1) != '/') {
                    siteurl += '/';
                }
                if (fhpc_username == "") {
                    url = url.replace('USERNAME', fhpc_username);
                }
                //url = url.replace('[ANY]', '');
                
                if ((parseInt(helper.id) == parseInt(localStorage.getItem('helper'))) && (helper.items[parseInt(localStorage.getItem('itemIndex'))]) && (helper.items[parseInt(localStorage.getItem('itemIndex'))].page == "" || siteurl + helper.items[parseInt(localStorage.getItem('itemIndex'))].page == url || (helper.items[parseInt(localStorage.getItem('itemIndex'))].page.indexOf('[ANY]')>-1 && url.indexOf(helper.items[parseInt(localStorage.getItem('itemIndex'))].page.replace('[ANY]','')) == 0))) {
                    chkHelperExist = helper;
                } 
            }
            
        });
    }
    if (chkHelperExist) {
        chkHelper = true;
        fhpc_startItem(chkHelperExist, parseInt(localStorage.getItem('itemIndex')));
    }

    jQuery.each(helpers, function (i) {
        var helper = helpers[i];
        if (fhpc_isMobile.any() && helper.mobileEnabled == "0") {
        } else if ((fhpc_isMobile.any() === null || !fhpc_isMobile.any()) && helper.onlyMobile == "1") {
        } else {
            var url = document.location.href;
            if (url.indexOf('#') > 0) {
                url = document.location.href.substr(0, document.location.href.lastIndexOf('#'));
            }
            if (url.indexOf('/index.php') > 0) {
                url = url.substr(0, url.lastIndexOf('/'));
            }
            if (helper.page.indexOf('index.php') > 0) {
                helper.page = helper.page.substr(0, helper.page.lastIndexOf('/'));
            }
            helper.page = siteurl + helper.page;
            if (fhpc_username == "") {
                helper.page = helper.page.replace('USERNAME', fhpc_username);
            }
             if (helper.page == siteurl || helper.page == url || helper.page + '/' == url || helper.page == url + '/' || (helper.page.indexOf('[ANY]')>-1 && url.indexOf(helper.page.replace('[ANY]','')) == 0)) {
                if ((helper.onAdmin == 1 && document.location.href.indexOf('wp-admin') < 0) || (helper.onAdmin == 0 && document.location.href.indexOf('wp-admin') > 0)) {
                } else {
                    if (helper.start == 'auto' && !chkHelperExist) {
                     
                        if (helper.items.length > 0) {
                            if (helper.onceTime == 1) {
                                eval("if(!localStorage.getItem('once" + helper.id + "')){ localStorage.setItem('once" + helper.id + "','1');chkHelper = true;fhpc_startHelper(helper); }");
                            } else {
                                chkHelper = true;
                                fhpc_startHelper(helper);
                            }
                        }
                    } else {
                        if (jQuery(helper.domElement).length > 0) {
                            jQuery(helper.domElement).attr('data-helper', helper.id);
                            jQuery(helper.domElement).click(function (e) {
                                e.preventDefault();
                                fhpc_startHelper(fhpc_getHelperByID(jQuery(this).attr('data-helper')));
                            });
                        }
                    }
                }
            }
        }
    });
    if (!chkHelper) {
        localStorage.removeItem('helper');
        localStorage.removeItem('itemIndex');
    }

}

function fhpc_getHelperByID(id) {
    var rep = false;
    jQuery.each(helpers, function (i) {
        var helper = helpers[i];
        if (helper.id == id) {
            rep = helper;
        }
    });
    return rep;
}

function fhpc_startHelper(helper) {
    localStorage.setItem('helper', helper.id);
    localStorage.setItem('itemIndex', 0);
    fhpc_startItem(helper, 0);

}

function fhpc_createOverlay($item, helper) {
    jQuery('body').append('<canvas id="fhpc_overlay"></canvas>');
    jQuery('#fhpc_overlay').attr({
        width: jQuery(document).outerWidth(),
        height: jQuery(document).outerHeight()
    }).css({
        width: jQuery(document).outerWidth(),
        height: jQuery(document).outerHeight()
    });
    var $closeHelperBtn = jQuery('<a href="javascript:" id="fhpc_closeHelperBtn"><span class="fui-cross"></span></a>');

    $closeHelperBtn.click(function () {
        var helper = fhpc_getHelperByID(localStorage.getItem('helper'));
        fhpc_stopHelper(jQuery('.fhpc_item'), helper);
    });
    $closeHelperBtn.hide();
    jQuery('body').append($closeHelperBtn);
}
function fhpc_isCanvasSupported() {
    var elem = document.createElement('canvas');
    return !!(elem.getContext && elem.getContext('2d'));
}

var fhpc_initialOverflow = 'auto';
function fhpc_startItem(helper, index) {
    var item = helper.items[index];
    var $item = false;
    localStorage.setItem('itemIndex', index);
    if (item.page != "" && siteurl + item.page != document.location.href && siteurl + item.page != document.location.href + '/' && siteurl + item.page + '/' != document.location.href && siteurl + item.page + '#' != document.location.href && (item.page.indexOf('[ANY]')==-1 || document.location.href.indexOf(item.page.replace('[ANY]','')) == -1)) {
       
        var chkNoChange = false;
        var defUrl = document.location.href;
        if (defUrl.indexOf('#') > -1) {
            defUrl = defUrl.substr(0, defUrl.length - defUrl.indexOf('#'));
            defUrl = defUrl.replace('#', '');
        }
        var newUrl = item.page;
        if (item.page.indexOf('#') > -1) {
            newUrl = item.page.substr(0, item.page.indexOf('#'));
            newUrl = newUrl.replace('#', '');
        }
        if (siteurl + newUrl == defUrl) {
            chkNoChange = true;
            document.location.href = siteurl + item.page;
            setTimeout(function () {
                fhpc_startItem(helper, index);
            }, 1000);
        }


        if (!chkNoChange) {
            document.location.href = siteurl + item.page;
        }
    } else {
        fhpc_initialOverflow = jQuery('body').css('overflowY');
        jQuery('body').css({overflowY: 'hidden'});
        if (item.type == 'tooltip' && item.domElement != "" && jQuery(item.domElement).length == 0) {
            fhpc_nextItem($item, helper, index);
        } else {
            jQuery('#fhpc_overlay,#fhpc_closeHelperBtn').fadeOut(1000);
            if (jQuery('#fhpc_overlay').length == 0) {
                setTimeout(function () {
                    fhpc_createOverlay();
                }, item.delayStart * 1000);
            }
            if (item.overlay == 1 && item.closeHelperBtn == 1) {
                setTimeout(function () {
                    jQuery('#fhpc_closeHelperBtn').delay(400).fadeIn(1000);
                }, item.delayStart * 1000);
            }

            setTimeout(function () {
                if (fhpc_isCanvasSupported()) {
                    var ctx = jQuery('#fhpc_overlay').get(0).getContext('2d');
                    ctx.clearRect(0, 0, jQuery('#fhpc_overlay').width(), jQuery('#fhpc_overlay').height());
                    ctx.globalCompositeOperation = "source-over";
                    if (item.type == 'tooltip' && item.domElement != "" && jQuery(item.domElement).length > 0) {
                        ctx.fillStyle = "#FFFFFF";
                        ctx.globalCompositeOperation = "source-over";
                        if (fhpc_isAnyParentFixed(jQuery(item.domElement)) || jQuery(item.domElement).css('position') == 'fixed') {
                            ctx.fillRect(jQuery(item.domElement).offset().left - 5, jQuery(item.domElement).offset().top - 5 - jQuery(item.domElement).scrollTop(), jQuery(item.domElement).outerWidth() + 10, jQuery(item.domElement).outerHeight() + 10);
                        } else {
                            ctx.fillRect(jQuery(item.domElement).offset().left - 5, jQuery(item.domElement).offset().top - 5, jQuery(item.domElement).outerWidth() + 10, jQuery(item.domElement).outerHeight() + 10);
                        }
                        ctx.globalCompositeOperation = "source-out";
                    } else {
                        jQuery('#fhpc_overlay').css({
                            backgroundColor: '#000000'
                        });
                    }
                    ctx.fillStyle = "#000000";
                    ctx.fillRect(0, 0, jQuery('#fhpc_overlay').width(), jQuery('#fhpc_overlay').height());

                    jQuery(window).resize(function () {
                        if (item.type == 'tooltip' && item.domElement != "" && jQuery(item.domElement).length > 0) {
                            ctx.fillStyle = "#FFFFFF";
                            ctx.globalCompositeOperation = "source-over";
                            if (fhpc_isAnyParentFixed(jQuery(item.domElement)) || jQuery(item.domElement).css('position') == 'fixed') {
                                ctx.fillRect(jQuery(item.domElement).offset().left - 5, jQuery(item.domElement).offset().top - 5 - jQuery(item.domElement).scrollTop(), jQuery(item.domElement).outerWidth() + 10, jQuery(item.domElement).outerHeight() + 10);
                            } else {
                                ctx.fillRect(jQuery(item.domElement).offset().left - 5, jQuery(item.domElement).offset().top - 5, jQuery(item.domElement).outerWidth() + 10, jQuery(item.domElement).outerHeight() + 10);
                            }
                            ctx.globalCompositeOperation = "source-out";
                            ctx.fillStyle = "#000000";
                            ctx.fillRect(0, 0, jQuery('#fhpc_overlay').width(), jQuery('#fhpc_overlay').height());
                        }
                    });
                }
            }, item.delayStart * 1000);
            jQuery('html,body').css({overflowY: fhpc_initialOverflow});

            if (item.type == 'tooltip' && item.domElement != "" && jQuery(item.domElement).length > 0) {
                if (item.overlay == 1) {
                    setTimeout(function () {
                        jQuery('#fhpc_overlay').fadeIn(1000);
                    }, item.delayStart * 1000);
                }
                if (fhpc_isAnyParentFixed(jQuery(item.domElement)) || jQuery(item.domElement).css('position') == 'fixed') {
                    jQuery(documentBody).animate({scrollTop: 0}, 500);
                    jQuery('html,body').css({overflowY: 'hidden'});
                } else {
                    jQuery(documentBody).animate({scrollTop: jQuery(item.domElement).offset().top - 200}, 500);
                }

                $container = jQuery('<div class="fhpc_container">&nbsp;</div>');
                jQuery(window).resize(function () {
                    $container.css({
                        position: 'absolute',
                        zIndex: 999999,
                        left: jQuery(item.domElement).offset().left,
                        top: jQuery(item.domElement).offset().top,
                        width: jQuery(item.domElement).outerWidth(),
                        height: jQuery(item.domElement).outerHeight()

                    });

                });


                setTimeout(function () {
                    $container.css({
                        position: 'absolute',
                        backgroundColor: 'transparent',
                        zIndex: 999999,
                        left: jQuery(item.domElement).offset().left,
                        top: jQuery(item.domElement).offset().top,
                        width: jQuery(item.domElement).outerWidth(),
                        height: jQuery(item.domElement).outerHeight()
                    });
                }, item.delayStart * 1000);

                if (item.actionNeeded == 'click') {
                    $container.css({
                        'cursor': 'pointer'
                    });
                    $container.click(function () {
                        jQuery(item.domElement).trigger('click-fhpc');
                    });
                }
                jQuery('body').append($container);
            } else {
                if (item.overlay == 1) {
                    setTimeout(function () {
                        jQuery('#fhpc_overlay').fadeIn(1000);
                    }, item.delayStart * 1000);
                }
            }

            if (item.type == 'tooltip') {
                $item = jQuery('<div class="fhpc_tooltip" data-position="' + item.position + '"></div>');
                $itemContainer = jQuery('<div class="fhpc_tooltip_container"></div>');
                $item.append($itemContainer);
                item.content = item.content.replace("\n", "<br/>");
                $itemContainer.append('<div class="fhpc_content">' + item.content + '</div>');
                $item.prepend('<div class="fhpc_arrow"></div>');
                
                if (item.actionNeeded == 'click') {
                    $item.css('cursor','pointer');
                     $item.click(function () {
                        jQuery(item.domElement).trigger('click-fhpc');
                    });
                }
                if (item.domElement && jQuery(item.domElement).length > 0) {
                    jQuery(window).resize(function () {
                        if (jQuery(item.domElement).length > 0) {
                            $item.css({
                                left: jQuery(item.domElement).offset().left,
                                top: jQuery(item.domElement).offset().top
                            });
                        }
                    });
                    $item.css({
                        left: jQuery(item.domElement).offset().left,
                        top: jQuery(item.domElement).offset().top
                    });
                    jQuery('body').append($item);
                    if (item.position == 'top') {
                        jQuery(window).resize(function () {
                            var left = parseInt($item.css('left')) + jQuery(item.domElement).outerWidth() / 2 - ($item.width() / 2);
                            if (left < 0) {
                                left = 0;
                            }
                            if (left + $item.outerWidth() > jQuery(window).width()) {
                                left = 0;
                            }
                            $item.css({
                                top: parseInt($item.css('top')) - ($item.height() + 20),
                                left: left
                            });
                        });
                        var left = parseInt($item.css('left')) + jQuery(item.domElement).outerWidth() / 2 - ($item.width() / 2);
                        if (left < 0) {
                            left = 0;
                        }
                        if (left + $item.outerWidth() > jQuery(window).width()) {
                            left = 0;
                        }
                        setTimeout(function () {
                            bounceTooltip($item);
                        }, item.delayStart * 1000);
                        setTimeout(function () {
                            $item.css({
                                left: jQuery(item.domElement).offset().left,
                                top: jQuery(item.domElement).offset().top
                            });
                            $item.css({
                                top: parseInt($item.css('top')) - ($item.height() + 20),
                                left: left
                            });
                            $item.fadeIn(500);
                        }, item.delayStart * 1000);


                    } else if (item.position == 'bottom') {
                        jQuery(window).resize(function () {
                            var left = parseInt($item.css('left')) + jQuery(item.domElement).outerWidth() / 2 - ($item.width() / 2);
                            if (left < 0) {
                                left = 0;
                            }
                            if (left + $item.outerWidth() > jQuery(window).width()) {
                                left = 0;
                            }
                            $item.css({
                                top: parseInt($item.css('top')) + jQuery(item.domElement).outerHeight() + 20,
                                left: left
                            });
                        });
                        var left = parseInt($item.css('left')) + jQuery(item.domElement).outerWidth() / 2 - ($item.width() / 2);
                        if (left < 0) {
                            left = 0;
                        }
                        if (left + $item.outerWidth() > jQuery(window).width()) {
                            left = 0;
                        }
                        setTimeout(function () {
                            bounceTooltip($item);
                        }, item.delayStart * 1000);

                        setTimeout(function () {
                            $item.css({
                                left: jQuery(item.domElement).offset().left,
                                top: jQuery(item.domElement).offset().top
                            });
                            $item.css({
                                top: parseInt($item.css('top')) + jQuery(item.domElement).outerHeight() + 20,
                                left: left
                            });
                            $item.fadeIn(500);
                        }, item.delayStart * 1000);
                    }

                }
            } else if (item.type == 'dialog') {
                $item = jQuery('<div class="fhpc_dialog"></div>');
                $close = jQuery('<a href="javascript:" class="fhpc_close fui-cross"></a>');
                $close.click(function () {
                    fhpc_stopHelper($item, helper);
                });
                $item.prepend($close);
                $item.append('<h3>' + item.title + '</h3>');
                $item.append('<div class="fhpc_content">' + item.content + '</div>');
                $item.append('<div class="fhpc_btns" style="text-align:center;"></div>');
                if (item.btnContinue != "") {
                    $btn = jQuery('<a href="javascript:"  class="fhpc_button">' + item.btnContinue + '</a>');

                    $btn.click(function () {
                        fhpc_nextItem($item, helper, index);
                    });
                    $item.children('.fhpc_btns').append($btn);
                }
                if (item.btnStop != "") {
                    $btnS = jQuery('<a href="javascript:"  class="fhpc_button fhpc_button_stop">' + item.btnStop + '</a>');
                    $btnS.click(function () {
                        fhpc_stopHelper($item, helper);
                    });
                    $item.children('.fhpc_btns').append($btnS);
                }

                jQuery('body').append($item);
                $item.css({opacity: 0});
                $item.show();
                jQuery(window).resize(function () {
                    $item.css({
                        marginLeft: 0 - $item.outerWidth() / 2,
                        marginTop: 0 - $item.outerHeight() / 2
                    });
                });
                $item.css({
                    left: '50%',
                    top: -500,
                    opacity: 0,
                    marginLeft: 0 - $item.outerWidth() / 2,
                    marginTop: 0 - $item.outerHeight() / 2
                });
               $item.animate({
                    top: '50%',
                    opacity: 1
                }, 500);
                
            } else if (item.type == 'text') {
                $item = jQuery('<div class="fhpc_text"></div>');
                $title = jQuery('<h2>' + item.title + '</h2>');
                $content = jQuery('<div>' + item.content + '</div>');
                $item.append($title);
                $item.append($content);
                jQuery('body').append($item);
                $item.css({
                    marginTop: 0 - $item.height() / 2
                });
                $item.hide();
                $item.fadeIn(500);
                $title.hide().delay(1000).fadeIn(1000);
                $content.hide().delay(2000).fadeIn(1000);
                setTimeout(function () {
                    jQuery('#fhpc_overlay').animate({
                        opacity: 0.9
                    }, 1000);
                }, 1000);


            }
            $item.addClass('fhpc_item');
            if (item.actionNeeded == 'click') {
                jQuery(item.domElement).unbind('click-fhpc');
                jQuery(item.domElement).bind('click-fhpc', function () {
                    fhpc_nextItem($item, helper, index);
                    jQuery(item.domElement).unbind('click-fhpc');
                });
                jQuery(item.domElement).click(function () {
                    jQuery(this).trigger("click-fhpc");
                });
            } else {
                fhpc_timer = setTimeout(function () {
                    fhpc_nextItem($item, helper, index);
                }, (item.delay * 1000) + (item.delayStart * 1000));
            }
        }
    }
}
function bounceTooltip($item) {
    if ($item.length > 0) {
        if ($item.is('[data-position="bottom"]')) {
            setTimeout(function () {
                $item.animate({
                    top: $item.position().top + 20
                }, 200, function () {
                    $item.animate({
                        top: $item.position().top - 20
                    }, 200, function () {
                        $item.animate({
                            top: $item.position().top + 20
                        }, 200, function () {
                            $item.animate({
                                top: $item.position().top - 20
                            }, 200, function () {
                                $item.animate({
                                    top: $item.position().top + 20
                                }, 200, function () {
                                    $item.animate({
                                        top: $item.position().top - 20
                                    }, 200);
                                    bounceTimer = setTimeout(function () {
                                        bounceTooltip($item);
                                    }, 3000);
                                });
                            });
                        });
                    });
                });
            });
        } else {
            setTimeout(function () {
                $item.animate({
                    top: $item.position().top - 20
                }, 200, function () {
                    $item.animate({
                        top: $item.position().top + 20
                    }, 200, function () {
                        $item.animate({
                            top: $item.position().top - 20
                        }, 200, function () {
                            $item.animate({
                                top: $item.position().top + 20
                            }, 200, function () {
                                $item.animate({
                                    top: $item.position().top - 20
                                }, 200, function () {
                                    $item.animate({
                                        top: $item.position().top + 20
                                    }, 200);
                                    bounceTimer = setTimeout(function () {
                                        bounceTooltip($item);
                                    }, 3000);
                                });
                            });
                        });
                    });
                });
            });
        }
    } else {
        clearTimer(bounceTimer);
    }

}

function fhpc_changeSelectionMode(mode) {
    fhpc_selectionMode = mode;
    if (mode) {
        fhpc_selectedElement = false;
    }
}

function fhpc_nextItem($item, helper, index) {
    if ($item) {
        if (fhpc_timer) {
            clearTimeout(fhpc_timer);
        }
        if ($item.is('.fhpc_dialog')) {
            $item.animate({
                top: -500,
                opacity: 0
            }, 800);
        } else {
            $item.fadeOut(1000);
        }
        setTimeout(function () {
            $item.remove();
        }, 1500);
    }
    jQuery('#fhpc_closeHelperBtn').fadeOut(500);
    jQuery('#fhpc_overlay').delay(200).fadeOut(700);
    setTimeout(function () {
        jQuery('html,body').css({overflowY: fhpc_initialOverflow});
        jQuery('.fhpc_container').remove();
        jQuery('#fhpc_overlay,#fhpc_closeHelperBtn').remove();
    }, 750);
    var i = index + 1;
    if (helper.items[i]) {
        localStorage.setItem('itemIndex', i);
        setTimeout(function () {
            fhpc_startItem(helper, i);
        }, 800);
    } else {
        localStorage.removeItem('helper');
        localStorage.removeItem('itemIndex');
    }
}
function fhpc_stopHelper($item, helper) {
    localStorage.removeItem('helper');
    localStorage.removeItem('itemIndex');

    if (fhpc_timer) {
        clearTimeout(fhpc_timer);
    }
    if ($item.is('.fhpc_dialog')) {
        $item.animate({
            top: -500,
            opacity: 0
        }, 500);
    } else {
        $item.fadeOut(500);
    }
    setTimeout(function () {
        $item.remove();
        jQuery('html,body').css({overflowY: fhpc_initialOverflow});
    }, 1500);
    jQuery('#fhpc_closeHelperBtn').fadeOut(500);
    jQuery('#fhpc_overlay').fadeOut(1000);
    setTimeout(function () {
        jQuery('.fhpc_container').remove();
        jQuery('#fhpc_overlay,#fhpc_closeHelperBtn').remove();
        localStorage.removeItem('helper');
        localStorage.removeItem('itemIndex');
    }, 1100);
}
