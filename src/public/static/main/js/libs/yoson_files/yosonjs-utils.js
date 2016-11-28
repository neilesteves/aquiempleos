var Cookie, Utils, browser, getIEVersion, isArray, isFlashEnabled, isInternetExplorer, navigatorSaysWho, sourcePath, xhrGlobalGetToken;
if (function($) {
        $.fn.removeClassRegEx = function(regex) {
            var classArray, classes, i, len;
            if (classes = $(this).attr("class"), !classes || !regex) return !1;
            for (classArray = [], classes = classes.split(" "), i = 0, len = classes.length; len > i;) classes[i].match(regex) || classArray.push(classes[i]), i++;
            return $(this).attr("class", classArray.join(" ")), $(this)
        }
    }(jQuery), window.log = function() {
        var enviroment;
        return enviroment = function() {
            return /(local\.|dev\.|localhost)/gi.test(document.domain)
        }, "undefined" != typeof console && enviroment() ? ("undefined" != typeof console.log.apply, void 0) : void 0
    }, String.prototype.removeSigns = function() {
        var esp, espObj, table, that;
        that = this, table = {
            ">": "Mayor de",
            "<": "Menor de"
        };
        for (esp in table) espObj = new RegExp("[" + esp + "]", "gi"), that = that.replace(espObj, table[esp]);
        return $.parseJSON('"' + that + '"')
    }, String.prototype.removeAccents = function() {
        var esp, espObj, table, that;
        that = this, espObj = null, table = {
            "ñ": "\\u00F1",
            "Ñ": "\\u00D1",
            "ç": "\\u00C7",
            ">": "Mayor de",
            "<": "Menor de",
            $: "\\u0024",
            "&": "\\u0026",
            "á": "\\u00E1",
            "à": "\\u00E0",
            "ã": "\\u00E3",
            "â": "\\u00E2",
            "ä": "\\u00E4",
            "Á": "\\u00C1",
            "À": "\\u00C0",
            "Ã": "\\u00C3",
            "Â": "\\u00C2",
            "Ä": "\\u00C4",
            "é": "\\u00E9",
            "è": "\\u00E8",
            "ë": "\\u00EB",
            "ê": "\\u00EA",
            "É": "\\u00C9",
            "È": "\\u00C8",
            "Ë": "\\u00CB",
            "Ê": "\\u00CA",
            "í": "\\u00ED",
            "ì": "\\u00EC",
            "ï": "\\u00EF",
            "î": "\\u00EE",
            "Í": "\\u00ED",
            "Ì": "\\u00EC",
            "Ï": "\\u00EF",
            "Î": "\\u00EE",
            "ó": "\\u00F3",
            "ò": "\\u00F2",
            "ö": "\\u00F6",
            "ô": "\\u00F4",
            "õ": "\\u00F5",
            "Ó": "\\u00D3",
            "Ò": "\\u00D2",
            "Ö": "\\u00D6",
            "Ô": "\\u00D4",
            "Õ": "\\u00D5",
            "ú": "\\u00FA",
            "ù": "\\u00F9",
            "ü": "\\u00FC",
            "û": "\\u00FB",
            "Ú": "\\u00DA",
            "Ù": "\\u00D9",
            "Ü": "\\u00DC",
            "Û": "\\u00DB"
        };
        for (esp in table) espObj = new RegExp("[" + esp + "]", "gi"), that = that.replace(espObj, table[esp]);
        return that
    }, Cookie = {
        create: function(c, d, e) {
            var a, b;
            return a = "", e ? (b = new Date, b.setTime(b.getTime() + 24 * e * 60 * 60 * 1e3), a = "; expires=" + b.toGMTString()) : a = "", document.cookie = c + "=" + d + a + "; path=/", this
        },
        read: function(b) {
            var a, d, e, f;
            for (e = b + "=", a = document.cookie.split(";"), d = 0; d < a.length;) {
                for (f = a[d];
                    " " === f.charAt(0);) f = f.substring(1, f.length);
                if (0 === f.indexOf(e)) return f.substring(e.length, f.length);
                d++
            }
            return null
        },
        del: function(a) {
            return this.create(a, "", -1)
        }
    }, isArray = function(element) {
        var result;
        return result = !1, "[object Array]" === Object.prototype.toString.call(element) && (result = !0), result
    }, browser = function() {
        var a, b;
        return a = function(d) {
            var c, e, f, g, h, i;
            return d = d.toLowerCase(), e = /(chrome)[ \/]([\w.]+)/.exec(d), g = /(webkit)[ \/]([\w.]+)/.exec(d), f = /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(d), i = /(msie) ([\w.]+)/.exec(d), c = /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(d), h = e || g || f || i || d.indexOf("compatible") < 0 && c || [], {
                brw: h[1] || "",
                ver: h[2] || "0"
            }
        }(navigator.userAgent), b = {}, a.brw && (b[a.brw] = !0, b.version = a.ver), b.chrome ? b.webkit = !0 : b.webkit && (b.safari = !0), b
    }(), getIEVersion = function() {
        var re, rv, ua;
        return rv = -1, ua = null, re = null, "Microsoft Internet Explorer" === navigator.appName ? (ua = navigator.userAgent, re = new RegExp("MSIE (0-9){1,}[.0-9]{0,}"), null != re.exec(ua) && (rv = parseFloat(RegExp.$1))) : "Netscape" === navigator.appName && (ua = navigator.userAgent, re = new RegExp("Trident/.*rv:([0-9]{1,}[.0-9]{0,})"), null != re.exec(ua) && (rv = parseFloat(RegExp.$1))), rv
    }, (isInternetExplorer = function() {
        return -1 !== getIEVersion() || browser.msie === !0
    })()) switch (browser.version) {
    case "8.0":
        $("body").addClass("lt-ie8");
        break;
    case "9.0":
        $("body").addClass("lt-ie9");
        break;
    case "10.0":
        $("body").addClass("lt-ie10");
        break;
    case "11.0":
        $("body").addClass("lt-ie11")
}
navigatorSaysWho = function() {
    var M, tem, ua, who;
    return who = {}, ua = navigator.userAgent, tem = void 0, M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [], /trident/i.test(M[1]) ? (tem = /\brv[ :]+(\d+)/g.exec(ua) || [], who.browser = "IE " + (tem[1] || ""), who) : "Chrome" === M[1] && (tem = ua.match(/\b(OPR|Edge)\/(\d+)/), null !== tem) ? (who.browser = tem.slice(1).join(" ").replace("OPR", "Opera"), who) : (M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, "-?"], null !== (tem = ua.match(/version\/(\d+)/i)) && M.splice(1, 1, tem[1]), who.browser = M.join(" "), who.OSName = "Unknown OS", -1 !== navigator.appVersion.indexOf("Win") ? who.OSName = "Windows" : -1 !== navigator.appVersion.indexOf("Mac") ? who.OSName = "MacOS" : -1 !== navigator.appVersion.indexOf("Linux") ? who.OSName = "Linux" : -1 !== navigator.appVersion.indexOf("X11") && (who.OSName = "UNIX"), who)
}, isFlashEnabled = function() {
    var e, error1, fo, hasFlash;
    hasFlash = !1;
    try {
        fo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash"), fo && (hasFlash = !0)
    } catch (error1) {
        e = error1, void 0 !== navigator.mimeTypes["application/x-shockwave-flash"] && (hasFlash = !0)
    }
    return hasFlash
}, Utils = function() {
    function Utils() {}
    return Utils
}(), xhrGlobalGetToken = null, Utils.prototype.getToken = function(callback_sucess, callback_error, urlToken) {
    var url;
    url = urlToken || "/registro/obtener-token/", xhrGlobalGetToken && xhrGlobalGetToken.abort(), xhrGlobalGetToken = $.ajax({
        url: url,
        type: "POST",
        dataType: "json"
    }).done(callback_sucess).fail(callback_error)
}, Utils.prototype.getPeruCode = function() {
    return "2533"
}, Utils.prototype.getBreakPointMobile = function() {
    return 667
}, Utils.prototype.getBreakPointTablet = function() {
    return 900
}, Utils.prototype.colorLog = function(msg, color) {
    log("%c" + msg, "color:" + color + ";font-weight:bold")
}, Utils.prototype.setErrorPlacement = function(error, element) {
    var _fieldset, lastError, newError, posHtml, posWrap;
    _fieldset = $(element).parents("fieldset"), newError = $(error).text(), lastError = _fieldset.data("lastError"), _fieldset.data("lastError", newError), _fieldset.removeClass("selected"), _fieldset.addClass("error"), "" !== newError && newError !== lastError && (_fieldset.tooltipster("content", newError), posHtml = $(window).scrollTop(), posWrap = parseInt(_fieldset.parents("form").offset().top) - 150, posHtml > posWrap ? _fieldset.tooltipster("option", "position", "bottom") : _fieldset.tooltipster("option", "position", "top")), _fieldset.tooltipster("show")
}, Utils.prototype.setSuccessForm = function(label, element) {
    var _fieldset;
    _fieldset = $(element).parents("fieldset"), _fieldset.tooltipster("hide"), _fieldset.removeClass("error"), _fieldset.removeClass("selected")
}, Utils.prototype.loader = function(tag, isShow) {
    isShow ? tag.append('<div class="load_wrap"></div>') : tag.find(".load_wrap").remove()
}, Utils.prototype.showMessage = function($box, type, message) {
    var fn, icon, messageBox, top;
    icon = "", messageBox = ".message_box", message = message || "Hubo un error, intente nuevamente", fn = {
        getType: function() {
            switch (type) {
                case "success":
                    icon = "icon_check";
                    break;
                case "error":
                    icon = "icon_cross"
            }
        },
        showMessage: function() {
            var html;
            html = '<section class="message_box"><div class="center_box"><div class="message"><i class="icon ' + icon + '"></i><span>' + message + '</span></div><i class="icon icon_cross"></i></div></section>', $(html).insertAfter($box), $(messageBox).fadeIn()
        },
        deleteAnyMessageBoxExist: function() {
            0 !== $(messageBox).length && $(messageBox).remove()
        }
    }, fn.getType(), fn.deleteAnyMessageBoxExist(), top = $box.offset().top - 80, 0 > top && (top = 0), $("html,body").animate({
        scrollTop: top
    }, "slow"), fn.showMessage(), $(".icon_cross", messageBox).on("click", function() {
        $(this).parents(messageBox).fadeOut()
    })
}, sourcePath = "frontend/", yOSON.utils = new Utils, yOSON.utils.colorLog(" > " + yOSON.module + " | " + yOSON.controller + " | " + yOSON.action, "black");
