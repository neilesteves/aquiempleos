var eplDoc;
yOSON.AppCore.addModule("accordion_filter_bar", function (Sb) {
    var catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        title: ""
    }, catchDom = function () {
        dom.title = $(st.title)
    }, suscribeEvents = function () {
        dom.title.on("click", events.eClosePrincipalList)
    }, events = {
        eClosePrincipalList: function (e) {
            $(this).next().is(":animated") || ($(this).next().stop().slideToggle(), $(this).find("i").toggleClass("rotate180"))
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("active_tooltip", function (Sb) {
    var catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        tooltipToActive: ".active_tooltip",
        tooltips: ".skill_ability_tooltip"
    }, catchDom = function () {
        dom.tooltipToActive = $(st.tooltipToActive), dom.tooltips = $(st.tooltips)
    }, suscribeEvents = function () {
        dom.tooltipToActive.on("mouseenter", events.eShowToolTip), dom.tooltipToActive.on("mouseleave", events.eHideToolTip), device.mobile() && (dom.tooltipToActive.on("touchstart", events.eShowToolTip), dom.tooltipToActive.on("touchend", events.eHideToolTip))
    }, events = {
        eShowToolTip: function (e) {
            var flag;
            flag = $(this).data("flag"), $(st.tooltips + "." + flag).show()
        },
        eHideToolTip: function (e) {
            var flag;
            flag = $(this).data("flag"), $(st.tooltips + "." + flag).hide()
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.device.js"]), yOSON.AppCore.addModule("autocomplete_custom", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        context: "",
        txtValue: "",
        txtIdValue: "",
        urlAutocomplete: "",
        autocomplete: null
    }, catchDom = function () {
        dom.context = $(st.context), dom.txtValue = $(st.txtValue, dom.context), dom.txtIdValue = $(st.txtIdValue, dom.context)
    }, afterCatchDom = function () {
        st.autocomplete = dom.txtValue.custom_autocomplete({
            hiddenValue: st.txtIdValue,
            urlAutocomplete: st.urlAutocomplete,
            getTokenAjax: yOSON.utils.getToken,
            fnAfterUpdateText: functions.fnValidateField
        })
    }, functions = {
        fnValidateField: function () {
            dom.txtValue.removeClass("error"), dom.txtValue.parent().removeClass("error")
        },
        fnAbortXHR: function () {
            st.autocomplete.fnAbortXHR()
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), Sb.events(["fnAbortAutocompleteXHR"], functions.fnAbortXHR, this)
    }, {
        init: initialize
    }
}, ["js/libs/jquery.autocomplete.custom.js"]), yOSON.AppCore.addModule("banner_image_performance", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st;
    return dom = {}, st = {
        banner: "#bannerMain",
        loadByScroll: !1,
        isImageTag: !0
    }, catchDom = function () {
        dom.banner = $(st.banner)
    }, afterCatchDom = function () {
        st.loadByScroll ? (st.flagExecuteOnlyOnce = !0, $(window).on("scroll", events.loadBannerScroll)) : functions.fnLoadBanner()
    }, events = {
        loadBannerScroll: function (event) {
            var bannerPosition, divPosition;
            divPosition = $(window).scrollTop() + $(window).height(), bannerPosition = dom.banner.offset().top - 100, divPosition > bannerPosition && st.flagExecuteOnlyOnce && (functions.fnLoadBanner(), st.flagExecuteOnlyOnce = !1)
        }
    }, functions = {
        fnLoadBanner: function () {
            var image;
            image = new Image, image.src = dom.banner.data("image-src"), image.onload = function () {
                setTimeout(function () {
                    st.isImageTag ? dom.banner.attr("src", "" + image.src) : dom.banner.css("background-image", "url(" + image.src + ")")
                }, 1e3)
            }
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.device.js"]), yOSON.AppCore.addModule("builder_paginator", function (Sb) {
    var ctxt, dom, fn, fnMiddleLayer, fnPrimaryLayer, initialize, st;
    return dom = {}, st = {}, ctxt = {
        prev: 1,
        next: 0,
        dots1: 1,
        dots2: 0
    }, fnPrimaryLayer = {
        createPaginator: function (pag, url) {
            var pagination, structure;
            return structure = fnMiddleLayer.createStructure(pag.total, pag.per_page, pag.current_page), pagination = fnMiddleLayer.createHtmlPaginator(url, structure)
        }
    }, fnMiddleLayer = {
        createStructure: function (total, per_page, current_page) {
            var page_range, structure, total_pages;
            return total_pages = fn.getTotalPages(total, per_page), page_range = fn.getPageRange(total_pages, current_page), structure = fn.buildStructure(total_pages, current_page, page_range)
        },
        createHtmlPaginator: function (url, structure) {
            var li;
            return li = [], li.push(fn.makeLiPrevNext(ctxt.prev, url, structure)), li.push(fn.makeLiDots(ctxt.dots1, url, structure)), li.push(fn.makeLiNumbers(url, structure)), li.push(fn.makeLiDots(ctxt.dots2, url, structure)), li.push(fn.makeLiPrevNext(ctxt.next, url, structure)), li.join("")
        }
    }, fn = {
        makeLiPrevNext: function (type, url, structure) {
            var current_page, current_url, html, icon, key, page, text;
            switch (current_page = structure.selected, current_url = "javascript:;", type) {
                case ctxt.prev:
                    icon = "<i class='icon icon_double_arrow'></i>", text = "Anterior", page = current_page - 1, key = "prev";
                    break;
                case ctxt.next:
                    icon = "<i class='icon icon_double_arrow next'></i>", text = "Siguiente", page = current_page + 1, key = "next"
            }
            return structure[key] && (current_url = url + "page/" + page), html = "<li class='paginator_item " + key + "'><a class='paginator_item_target' title='" + text + "' href='" + current_url + "'>" + icon + "</a></li>"
        },
        makeLiDots: function (type, url, structure) {
            var current_range, default_page, default_url, dots, html, key, new_url, page, result;
            switch (current_range = structure.numbers, new_url = "javascript:;", html = "", dots = "", type) {
                case ctxt.dots1:
                    page = current_range[0] - 1, default_page = "1", key = "dots1";
                    break;
                case ctxt.dots2:
                    page = current_range[1] + 1, default_page = structure.total_pages, key = "dots2"
            }
            return structure[key] && (new_url = url + "page/" + page, default_url = url + "page/" + default_page, dots = "<li class='paginator_item'><a class='paginator_item_target' title='...' href='" + new_url + "'>...</a></li>", html = "<li class='paginator_item'><a class='paginator_item_target' title='Página " + default_page + "' href='" + default_url + "'>" + default_page + "</a></li>"), result = dots + html, type === ctxt.dots1 && (result = html + dots), result
        },
        makeLiNumbers: function (url, structure) {
            var active, begins, current_page, finish, j, li, li_page, new_url, page, ref, ref1, selected;
            for (li = [], begins = structure.numbers[0], finish = structure.numbers[1], current_page = structure.selected, page = j = ref = begins, ref1 = finish; ref1 >= ref ? ref1 >= j : j >= ref1; page = ref1 >= ref ? ++j : --j)
                new_url = url + "page/" + page, active = "", selected = "", current_page === page && (new_url = "javascript:;", active = " active", selected = " selected"), li_page = "<li class='paginator_item" + active + "'><a class='paginator_item_target" + selected + "' title='Página " + page + "' href='" + new_url + "'>" + page + "</a></li>", li.push(li_page);
            return li.join("")
        },
        buildStructure: function (total_pages, current_page, page_range) {
            var structure;
            return structure = {}, structure.prev = current_page > 1, structure.dots1 = current_page > 4, structure.numbers = page_range, structure.dots2 = total_pages >= current_page + 4, structure.next = total_pages > current_page, structure.selected = current_page, structure.total_pages = total_pages, structure
        },
        getPageRange: function (total_pages, current_page) {
            var finalDots, firstDots, rango;
            return rango = {
                1: {},
                0: {}
            }, rango[1][1] = [current_page - 1, current_page + 1], rango[1][0] = [total_pages - 3, total_pages], rango[0][1] = [1, 4], rango[0][0] = [1, total_pages], firstDots = 1 * (current_page > 4), finalDots = 1 * (total_pages >= current_page + 4), rango[firstDots][finalDots]
        },
        getTotalPages: function (total, per_page) {
            var total_pages;
            return total_pages = Math.ceil(total / per_page)
        }
    }, initialize = function (oP) {
        $.extend(st, oP)
    }, {
        init: initialize,
        tests: fn
    }
}, []), yOSON.AppCore.addModule("combos_depends", function (Sb) {
    var factory, initialize;
    return factory = function (op) {
        var globalThis;
        this.st = {
            form: "",
            selParent: null,
            selChild: null,
            arrExceptions: [],
            jsonDefault: !0,
            urlAjax: null,
            paramAjax: null,
            xhrGetList: null,
            subMenuBox: ".sub_menu_box"
        }, this.dom = {}, this.op = op, globalThis = this
    }, factory.prototype = {
        catchDom: function () {
            this.dom.subMenuBox = $(this.st.subMenuBox)
        },
        suscribeEvents: function () {
            $(document).on("change", this.st.selParent, {
                inst: this
            }, this.eShowHideOptions)
        },
        eShowHideOptions: function (event) {
            var dom, self, st, that, valueParent;
            event.preventDefault(), that = event.data.inst, st = that.st, dom = that.dom, self = $(this), valueParent = Number(self.val()), -1 === $.inArray(valueParent, st.arrExceptions) && (dom.selChild = $(st.selChild, st.form), dom.selChild.parents("fieldset").attr("disabled", ""), dom.selChild.attr("disabled", ""), 0 === valueParent && dom.selChild.find("option:first").siblings().remove(), yOSON.utils.getToken(function (dataToken) {
                that.getDataAjax(dataToken, valueParent)
            }, function (jqXHR, textStatus, errorThrown) {
                that.showError(textStatus)
            }))
        },
        getDataAjax: function (dataToken, valueParent) {
            var dataToAjax, dom, st, that;
            that = this, st = that.st, dom = that.dom, st.xhrGetList && st.xhrGetList.abort(), dataToAjax = {
                token: dataToken
            }, dataToAjax[st.paramAjax] = valueParent, st.xhrGetList = $.ajax({
                url: st.urlAjax,
                type: "POST",
                dataType: "JSON",
                data: dataToAjax
            }).done(function (response) {
                $.isEmptyObject(response) || 0 === response.length || (dom.selChild.find("option:first").siblings().remove(), $.each(response, function (i, data) {
                    that.fnSetHTMLOptions(i, data)
                }), dom.selChild.parents("fieldset").removeAttr("disabled"), dom.selChild.removeAttr("disabled"))
            }).fail(function (jqXHR, textStatus, errorThrown) {
                that.showError(textStatus)
            })
        },
        fnSetHTMLOptions: function (i, data) {
            var objModel, that;
            that = this, that.st.jsonDefault ? objModel = {
                value: i,
                data: data
            } : $.each(data, function (i, v) {
                objModel = {
                    value: v.id,
                    data: v.nombre
                }
            }), that.dom.selChild.append("<option value='" + objModel.value + "' label='" + objModel.data + "'>" + objModel.data + "</option>")
        },
        showError: function (textStatus) {
            var that;
            that = this, that.dom.selChild.find("option:first").siblings().remove(), that.dom.selChild.parents("fieldset").removeAttr("disabled"), that.dom.selChild.removeAttr("disabled"), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(that.dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        },
        execute: function () {
            this.st = $.extend({}, this.st, this.op), this.catchDom(), this.suscribeEvents()
        }
    }, initialize = function (oP) {
        $.each(oP, function (i, obj) {
            var instance;
            instance = new factory(obj), instance.execute()
        })
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("control_help_layout", function (Sb) {
    var afterCatchDom, catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        header: "header",
        btnCloseHelp: "#btnCloseHelp",
        btnOpenHelp: ".icon.icon_help",
        modalHelp: "#modalHelp",
        topEplanning: ".top_e_planning"
    }, catchDom = function () {
        dom.header = $(st.header), dom.btnCloseHelp = $(st.btnCloseHelp), dom.btnOpenHelp = $(st.btnOpenHelp), dom.modalHelp = $(st.modalHelp), dom.topEplanning = $(st.topEplanning)
    }, suscribeEvents = function () {
        dom.btnOpenHelp.on("click", events.clickOpen), dom.btnCloseHelp.on("click", events.clickClose), dom.modalHelp.on("click", events.clickClose)
    }, afterCatchDom = function () {}, events = {
        clickOpen: function (e) {
            e.preventDefault(), dom.modalHelp.fadeIn(), $(st.header).addClass("help_head"), dom.topEplanning.hide()
        },
        clickClose: function (e) {
            e.preventDefault(), dom.modalHelp.fadeOut(), $(st.header).removeClass("help_head"), dom.topEplanning.show()
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("cookie_web_settings", function (Sb) {
    var cookieData, cookieName, functions, initialize, st;
    return cookieData = {
        font_size: 14
    }, cookieName = "web_settings", st = {}, functions = {
        createCookie: function () {
            null === Cookie.read(cookieName) && Cookie.create(cookieName, JSON.stringify(cookieData))
        },
        openCookie: function (callback) {
            var currentCookieData;
            currentCookieData = JSON.parse(Cookie.read(cookieName)), callback.call(this, currentCookieData)
        },
        updateCookie: function (newData) {
            Cookie.create(cookieName, JSON.stringify(newData))
        },
        updateFromCookie: function (key, data) {
            var currentCookieData;
            currentCookieData = JSON.parse(Cookie.read(cookieName)), currentCookieData[key] = data, functions.updateCookie(currentCookieData)
        }
    }, initialize = function (oP) {
        return $(document).ready(function () {
            $.extend(st, oP), functions.createCookie(), Sb.events(["openCookie"], functions.openCookie, this), Sb.events(["updateCookie"], functions.updateCookie, this), Sb.events(["updateFromCookie"], functions.updateFromCookie, this)
        })
    }, {
        init: initialize
    }
}), yOSON.AppCore.addModule("count_words", function (Sb) {
    var afterCatchdom, catchDom, dom, events, fn, fnPrimaryLayer, initialize, publicInitialize, st, suscribeEvents;
    return dom = {}, st = {
        containerGlobal: ".body_postulant_notification_messages",
        mensaje: "#mensaje",
        maxNumber: ".max_nummber",
        initialMessage: null,
        maxCharacters: 1e3,
        readData: !1
    }, catchDom = function () {
        dom.containerGlobal = $(st.containerGlobal), dom.mensaje = $(st.mensaje, dom.containerGlobal), dom.maxNumber = $(st.maxNumber, dom.containerGlobal)
    }, afterCatchdom = function () {
        st.readData && (st.maxCharacters = parseInt(dom.mensaje.data("maxlength"))), null === st.initialMessage && (st.initialMessage = $(st.maxNumber).html()), fnPrimaryLayer.showCharactersLeft()
    }, suscribeEvents = function () {
        dom.containerGlobal.on("keypress", st.mensaje, events.eCountCharacters), dom.containerGlobal.on("keyup", st.mensaje, events.eCountCharacters), dom.containerGlobal.on("paste", st.mensaje, events.eCountCharacters)
    }, events = {
        eCountCharacters: function (e) {
            fnPrimaryLayer.showCharactersLeft()
        }
    }, fnPrimaryLayer = {
        showCharactersLeft: function () {
            var charactersLeft, elementExists;
            elementExists = fn.elementExists(dom.mensaje), elementExists && (charactersLeft = fn.countCharacters(dom.mensaje, st.maxCharacters), fn.putMessage(charactersLeft, dom.maxNumber))
        }
    }, fn = {
        elementExists: function (mensaje) {
            var exists;
            return exists = !0, 0 === mensaje.length && (exists = !1), exists
        },
        countCharacters: function (mensaje, maxCharacters) {
            var charactersLeft, text;
            return text = mensaje.val(), charactersLeft = maxCharacters - text.length, 0 > charactersLeft ? (charactersLeft = "<b class='fewWordsLeftAlert'>0</b>", text = text.substring(0, maxCharacters), mensaje.val(text)) : 100 > charactersLeft && (charactersLeft = "<b class='fewWordsLeftAlert'>" + charactersLeft + "</b>"), charactersLeft
        },
        putMessage: function (charactersLeft, maxNumber) {
            var newMessage;
            newMessage = "(Te quedan " + charactersLeft + " caracteres)", maxNumber.html(newMessage)
        }
    }, publicInitialize = function () {
        catchDom(), afterCatchdom(), suscribeEvents()
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchdom(), suscribeEvents(), Sb.events(["count_words"], publicInitialize, this)
    }, {
        init: initialize,
        functions: fn
    }
}, []), yOSON.AppCore.addModule("disable_especial_characters", function (Sb) {
    var afterCatchDom, catchDom, dom, initialize, st;
    return dom = {}, st = {
        txtField: ".disable_especial_characters"
    }, catchDom = function () {
        dom.txtField = $(st.txtField)
    }, afterCatchDom = function () {
        dom.txtField.alphanum({
            disallow: ".()\\¨°~#@|!\"·$%&/?'¡¿[^`]+}{¨´><;,:",
            allow: ""
        })
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, ["js/libs/jquery-alphanum/jquery.alphanum.js"]), yOSON.AppCore.addModule("disable_rows_form", function (Sb) {
    var catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        form: null,
        checkbox: null,
        formValues: [],
        inverse: !0
    }, catchDom = function () {
        dom.form = $(st.form), dom.checkbox = $(st.checkbox, st.form)
    }, suscribeEvents = function () {
        $(document).on("change", st.checkbox, events.toggleDisable)
    }, events = {
        toggleDisable: function (e) {
            var self;
            self = $(this), $.each(st.formValues, function (i, value) {
                var parentTagForm, tagForm;
                tagForm = $("#" + value, st.form), parentTagForm = tagForm.parents("fieldset"), self.is(":checked") === st.inverse ? (parentTagForm.attr("disabled", "").removeClass("error"), tagForm.attr("disabled", "").removeClass("error"), tagForm.is("select") ? (tagForm[0].selectedIndex = 0, tagForm.trigger("change")) : tagForm.is("[type='checkbox']") ? (tagForm.attr("checked", !1), "#" + value !== st.checkbox && tagForm.trigger("change")) : tagForm.val("")) : (parentTagForm.removeAttr("disabled"), tagForm.removeAttr("disabled"))
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}), eplDoc = document, yOSON.AppCore.addModule("e_planning", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        eplanningClass: ".eplanning",
        eplLL: !1,
        eS1: "us.img.e-planning.net",
        eplArgs: yOSON.Eplanning
    }, catchDom = function () {
        dom.eplanningClass = $(st.eplanningClass)
    }, afterCatchDom = function () {
        st.eplArgs.sV = "" + functions.schemeLocal() + st.eplArgs.sV, functions.eplCheckStart(), functions.setEplannings()
    }, functions = {
        setEplannings: function () {
            dom.eplanningClass.each(function () {
                var _that, section;
                _that = $(this), section = _that.data("section"), _that.html('<div id="eplAdDiv' + section + '"></div>'), functions.eplAD4M(section, st.eplArgs.custom)
            })
        },
        eplCheckStart: function () {
            var ce, ci, cookieName, dc, e, eIF, eIFD, eS2, s, ss;
            if (document.epl) {
                if (e = document.epl, e.eplReady())
                    return !0;
                if (e.eplInit(st.eplArgs), st.eplArgs.custom)
                    for (s in st.eplArgs.custom)
                        document.epl.setCustomAdShow(s, st.eplArgs.custom[s]);
                return e.eplReady()
            }
          //  return st.eplLL ? !1 : document.body ? (eS2 = void 0, dc = document.cookie, cookieName = ("https" === functions.schemeLocal() ? "EPLSERVER_S" : "EPLSERVER") + "=", ci = dc.indexOf(cookieName), -1 !== ci && (ci += cookieName.length, ce = dc.indexOf(";", ci), -1 === ce && (ce = dc.length), eS2 = dc.substring(ci, ce)), eIF = document.createElement("IFRAME"), eIF.src = "about:blank", eIF.id = "epl4iframe", eIF.name = "epl4iframe", eIF.width = 0, eIF.height = 0, eIF.style.width = "0px", eIF.style.height = "0px", eIF.style.display = "none", document.body.appendChild(eIF), eIFD = eIF.contentDocument ? eIF.contentDocument : eIF.document, eIFD.open(), eIFD.write("<html><head><title>e-planning</title></head><body></body></html>"), eIFD.close(), s = eIFD.createElement("SCRIPT"), s.src = functions.schemeLocal() + "://" + (eS2 ? eS2 : st.eS1) + "/layers/epl-41.js", eIFD.body.appendChild(s), eS2 || (ss = eIFD.createElement("SCRIPT"), ss.src = functions.schemeLocal() + "://ads.us.e-planning.net/egc/4/2912", eIFD.body.appendChild(ss)), st.eplLL = !0, !1) : !1
        },
        eplSetAdM: function (eID, custF) {
            var stateCustF;
            functions.eplCheckStart() ? (custF && document.epl.setCustomAdShow(eID, st.eplArgs.custom[eID]), document.epl.showSpace(eID)) : (stateCustF = custF ? !0 : !1, setTimeout(function () {
                functions.eplSetAdM(eID, stateCustF)
            }, 250))
        },
        eplAD4M: function (eID, custF) {
            custF && (st.eplArgs.custom || (st.eplArgs.custom = {}), st.eplArgs.custom[eID] = custF), functions.eplSetAdM(eID, custF ? !0 : !1)
        },
        schemeLocal: function () {
            var protocol;
            return protocol = document.location.protocol ? document.location.protocol : window.top.location.protocol, protocol ? -1 !== protocol.indexOf("https") ? "https" : "http" : void 0
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("equalingHeights", function (Sb) {
    var catchDom, dom, functions, initialize, st;
    return st = {
        boxForm: ".box_form",
        boxIntroduction: ".box_introduction"
    }, dom = {}, catchDom = function () {
        dom.boxForm = $(st.boxForm), dom.boxIntroduction = $(st.boxIntroduction)
    }, functions = {
        equalingHeights: function (firstElement, secondElement) {
            var fisrtH, secondH;
            firstElement.css({
                "min-height": 0
            }), secondElement.css({
                "min-height": 0
            }), fisrtH = firstElement.height(), secondH = secondElement.height(), fisrtH > secondH ? secondElement.css({
                "min-height": fisrtH
            }) : firstElement.css({
                "min-height": secondH
            })
        },
        callEqualingHeights: function () {
            functions.equalingHeights(dom.boxIntroduction, dom.boxForm)
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), functions.equalingHeights(dom.boxIntroduction, dom.boxForm), Sb.events(["callEqualingHeights"], functions.callEqualingHeights, this)
    }, {
        init: initialize
    }
}), yOSON.AppCore.addModule("featured_notices_aside", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        suggestedJobTab: ".suggested_tabs_header li",
        suggestedJobs: ".suggested_tabs_content",
        subMenuBox: ".sub_menu_box",
        urlAjax: "/avisos-sugeridos/list-of-currently-ajax",
        tplSuggestedJobs: "#tplSuggestedJobs",
        tplHTML: null,
        xhrAjax: null
    }, catchDom = function () {
        dom.suggestedJobTab = $(st.suggestedJobTab), dom.tplSuggestedJobs = $(st.tplSuggestedJobs), dom.suggestedJobs = $(st.suggestedJobs), dom.subMenuBox = $(st.subMenuBox)
    }, afterCatchDom = function () {
        0 !== dom.tplSuggestedJobs.length && (st.tplHTML = _.template(dom.tplSuggestedJobs.html()))
    }, suscribeEvents = function () {
        dom.suggestedJobTab.on("click", events.getDataAjax), isInternetExplorer() && "8.0" === browser.version && (dom.suggestedJobs.on("mouseover", ".jobs_ads ", events.addClassHover), dom.suggestedJobs.on("mouseout", ".jobs_ads ", events.removeClassHover), dom.suggestedJobs.on("mouseover", ".btn_hightlight ", events.addClassHover), dom.suggestedJobs.on("mouseout", ".btn_hightlight ", events.removeClassHover), dom.suggestedJobs.on("mouseover", ".btn_delete_ads ", events.addClassHover), dom.suggestedJobs.on("mouseout", ".btn_delete_ads ", events.removeClassHover))
    }, events = {
        addClassHover: function (e) {
            $(this).addClass("is_hover")
        },
        removeClassHover: function (e) {
            $(this).removeClass("is_hover")
        },
        getDataAjax: function (e) {
            var self, typeAction;
            return self = $(this), typeAction = self.data("rel"), self.hasClass("is_active") || self.attr("disabled") ? !1 : (yOSON.utils.loader(dom.suggestedJobs, !0), void(st.xhrAjax = $.ajax({
                type: "POST",
                url: st.urlAjax,
                data: {
                    type: typeAction,
                    token_ajax: yOSON.token
                },
                beforeSend: function () {
                    dom.suggestedJobTab.attr("disabled", "")
                }
            }).done(function (response) {
                var compiledTemplate;
                response = $.parseJSON(response), yOSON.token = response.token_ajax, yOSON.utils.loader(dom.suggestedJobs, !1), 1 === response.status ? (self.addClass("is_active").siblings().removeClass("is_active"), self.find(".number").text(response.total_avisos), 0 === response.items_list.length ? dom.suggestedJobs.html('<ul><li class="not_enough_message"><p>No hay avisos para mostrar.</p></li></ul>') : (response.action = typeAction, compiledTemplate = st.tplHTML(response), dom.suggestedJobs.html(compiledTemplate))) : yOSON.utils.showMessage(dom.subMenuBox, "error", response.messages)
            }).fail(functions.showError).always(function () {
                dom.suggestedJobTab.removeAttr("disabled")
            })))
        }
    }, functions = {
        showError: function (jqXHR, textStatus, errorThrown) {
            dom.suggestedJobTab.removeAttr("disabled"), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js"]), yOSON.AppCore.addModule("fixed_menu", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        header: "header",
        navSession: ".nav_session"
    }, catchDom = function () {
        dom.header = $(st.header), dom.navSession = $(st.navSession)
    }, afterCatchDom = function () {}, suscribeEvents = function () {
        device.desktop() && dom.navSession.hover(functions.addClassNavUser, functions.removeClassNavUser), device.tablet() && (dom.navSession.on("click", functions.toogleClassNavUser), $(document).mouseup(functions.mouseUpNavUser)), (device.tablet() || device.mobile()) && ($("input", ".page_default").on("focus", events.addClassHeader), $("input", ".page_default").on("blur", events.removeClassHeader))
    }, events = {
        addClassHeader: function () {
            dom.header.addClass("fix_device")
        },
        removeClassHeader: function () {
            dom.header.removeClass("fix_device")
        }
    }, functions = {
        addClassNavUser: function () {
            $(this).addClass("active")
        },
        removeClassNavUser: function () {
            $(this).removeClass("active")
        },
        toogleClassNavUser: function () {
            $(this).toggleClass("active")
        },
        mouseUpNavUser: function (e) {
            dom.navSession.is(e.target) || 0 !== dom.navSession.has(e.target).length || dom.navSession.removeClass("active")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.device.js"]), yOSON.AppCore.addModule("hover_account_items", function (Sb) {
    var catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        items: ".title_skills",
        container: ".skill_body"
    }, catchDom = function () {
        dom.container = $(st.container)
    }, suscribeEvents = function () {
        dom.container.on("mouseenter", st.items, events.eAddHover), dom.container.on("mouseleave", st.items, events.eRemoveHover)
    }, events = {
        eAddHover: function () {
            $(this).addClass("title_skills_hover")
        },
        eRemoveHover: function () {
            $(this).removeClass("title_skills_hover")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("lazy_load", function (Sb) {
    var catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        img: "img.lazy"
    }, catchDom = function () {
        dom.img = $(st.img)
    }, functions = {
        load: function () {
            $(st.img).lazyload({
                data_attribute: "src"
            })
        },
        loadForDevices: function () {
            $(st.img).each(function (i, item) {
                var $this, src;
                $this = $(item), src = $this.data("src"), $this.attr("src", src)
            })
        },
        replaceImageHome: function () {
            var element, image;
            element = $(".box_searching"), element.length && (image = new Image, image.src = element.data("src"), image.onload = function () {
                element.css("background", "url('" + image.src + "') top center no-repeat")
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), device.mobile() || device.tablet() ? functions.loadForDevices() : functions.load()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.lazyload/jquery.lazyload.js"]), yOSON.AppCore.addModule("load_social_plugins", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        facebookUrl: "http://connect.facebook.net/es_LA/all.js",
        twitterUrl: "http://platform.twitter.com/widgets.js",
        googleUrl: "https://apis.google.com/js/plusone.js",
        isRunScript: !1,
        positionSocialPlugins: !1
    }, catchDom = function () {
        dom.footer = $("footer"), dom.document = $(document), dom.window = $(window)
    }, afterCatchDom = function () {
        dom.footer.length > 0 && (st.positionSocialPlugins = parseInt(dom.footer.offset().top), functions.loadPlugins())
    }, suscribeEvents = function () {
        dom.window.on("scroll", events.onScroll)
    }, events = {
        onScroll: function () {
            functions.loadPlugins()
        }
    }, functions = {
        loadFacebook: function () {
            "undefined" != typeof FB ? FB.init({
                appId: yOSON.tmp.appIdFacebook,
                status: !0,
                xfbml: !0,
                version: "v2.0"
            }) : ($.ajaxSetup({
                cache: !0
            }), $.getScript([st.facebookUrl], function () {
                FB.init({
                    appId: yOSON.tmp.appIdFacebook,
                    status: !0,
                    xfbml: !0,
                    version: "v2.0"
                }), $("#loginbutton, #feedbutton").removeAttr("disabled")
            }))
        },
        loadTwitter: function () {
            "undefined" != typeof twttr ? twttr.widgets.load() : $.getScript(st.twitterUrl)
        },
        loadGoogle: function () {
            "undefined" != typeof gapi ? $(".g-plusone").each(function () {
                return gapi.plusone.render($(this).get(0))
            }) : $.getScript(st.googleUrl)
        },
        setLinkTwitter: function () {
            $(".social_button.twitter a").addClass("ie8").attr("target", "_blank").html('<i class="icon icon_twitter"></i> Seguir')
        },
        loadPlugins: function () {
            var posHtml;
            posHtml = dom.document.scrollTop() + dom.window.height(), !st.isRunScript && posHtml > st.positionSocialPlugins && (st.isRunScript = !0, functions.loadGoogle(), isInternetExplorer() && "8.0" === browser.version ? functions.setLinkTwitter() : functions.loadTwitter(), functions.loadFacebook())
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("manage_font_size", function (Sb) {
    var afterCatchDom, catchDom, dom, events, font_size, functions, initialize, st, suscribeEvents;
    return dom = {}, font_size = {
        max: 18,
        min: 12,
        current: 14
    }, st = {
        btnSmallerFont: ".smaller_font",
        btnBiggerFont: ".bigger_font",
        container: ".jobs_ads_results_wrapper"
    }, catchDom = function () {
        dom.btnSmallerFont = $(st.btnSmallerFont), dom.btnBiggerFont = $(st.btnBiggerFont), dom.container = $(st.container)
    }, afterCatchDom = function () {
        Sb.trigger("openCookie", function (currentData) {
            return font_size.current = currentData.font_size, dom.container.css("font-size", font_size.current + "px")
        }), device.desktop() && (dom.btnSmallerFont.addClass("animated slower"), dom.btnBiggerFont.addClass("animated slower"))
    }, suscribeEvents = function () {
        dom.btnSmallerFont.on("click", events.eReduceFont), dom.btnBiggerFont.on("click", events.eIncreaseFont), dom.btnSmallerFont.on("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", events.eRemoveRubberEffect), dom.btnBiggerFont.on("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", events.eRemoveRubberEffect)
    }, events = {
        eReduceFont: function (e) {
            font_size.current > font_size.min && (--font_size.current, dom.btnSmallerFont.addClass("rubberBand"), dom.container.css("font-size", font_size.current + "px"), Sb.trigger("updateFromCookie", "font_size", font_size.current)), functions.fnClearSelection()
        },
        eIncreaseFont: function (e) {
            font_size.current < font_size.max && (++font_size.current, dom.btnBiggerFont.addClass("rubberBand"), dom.container.css("font-size", font_size.current + "px"), Sb.trigger("updateFromCookie", "font_size", font_size.current)), functions.fnClearSelection()
        },
        eRemoveRubberEffect: function (e) {
            dom.btnSmallerFont.removeClass("rubberBand"), dom.btnBiggerFont.removeClass("rubberBand")
        }
    }, functions = {
        fnClearSelection: function () {
            var sel;
            document.selection && document.selection.empty ? document.selection.empty() : window.getSelection && (sel = window.getSelection(), sel.removeAllRanges())
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("manipulateHTMLDom", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        tagImg: "[data-png]"
    }, catchDom = function () {
        dom.tagImg = $(st.tagImg)
    }, afterCatchDom = function () {
        browser.msie && "8.0" === browser.version && functions.changeSvgToPng(), functions.initPluginDotDotDot(), functions.initFancyBoxDefaults(), functions.initAnimateHash()
    }, functions = {
        changeSvgToPng: function () {
            0 !== dom.tagImg.length && $.each(dom.tagImg, function (i, elem) {
                var newURL;
                newURL = $(elem).data("png"), $(elem).attr("src", newURL)
            })
        },
        initPluginDotDotDot: function () {
            $(".ellipsis").dotdotdot()
        },
        initFancyBoxDefaults: function () {
            $.fancybox.defaults.tpl.closeBtn = '<a title="Close" class="fancybox-item fancybox-close" href="javascript:;"><i class="icon icon_close"></i></a>'
        },
        initAnimateHash: function () {
            $("a[data-lnk]").on("click", function (e) {
                var aTag;
                e.preventDefault(), aTag = $(this).attr("data-lnk"), $("html,body").animate({
                    scrollTop: $(aTag).offset().top - 80
                }, "slow")
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.device.js", "js/libs/jQuery.dotdotdot/src/js/jquery.dotdotdot.min.js", "js/libs/fancybox/source/jquery.fancybox.js"]), yOSON.AppCore.addModule("message_box", function (Sb) {
    var catchDom, dom, events, initialize, st, suscribeEvents;
    return dom = {}, st = {
        messageBox: ".message_box",
        icon: ".icon"
    }, catchDom = function () {
        dom.messageBox = $(st.messageBox), dom.icon = $(st.icon, dom.messageBox)
    }, suscribeEvents = function () {
        dom.icon.on("click", events.closeMessageBox)
    }, events = {
        closeMessageBox: function (e) {
            var _this;
            _this = $(this), _this.parents(st.messageBox).fadeOut()
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("mobile_toggle_menu", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        dropRightMenu: "#dropRightMenu",
        showRightPush: "#showRightPush",
        body: "body"
    }, catchDom = function () {
        dom.dropRightMenu = $(st.dropRightMenu), dom.showRightPush = $(st.showRightPush), dom.body = $(st.body), dom.window = $(window)
    }, afterCatchDom = function () {}, suscribeEvents = function () {
        dom.showRightPush.on("click", functions.toggleMenu), dom.window.on("resize", functions.toggleClassMenuMobile)
    }, functions = {
        toggleMenu: function (e) {
            e.preventDefault(), $(this).toggleClass("active"), dom.body.toggleClass("active_menu_push_toleft"), dom.dropRightMenu.toggleClass("active_menu_open")
        },
        toggleClassMenuMobile: function () {
            dom.window.width() > yOSON.utils.getBreakPointMobile() && (dom.body.removeClass("active_menu_push_toleft"), dom.dropRightMenu.removeClass("active_menu_open"))
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("modal_login_form", function (Sb) {
    var afterCatchDom, catchDom, dom, fn, initialize, st;
    return dom = {}, st = {
        modalLoginUser: "#modalLoginUser",
        modalRegisterUser: "#modalRegisterUser",
        btnLoginInit: ".login_init",
        returnNormal: null,
        frmUserLogIn: "#frmUserLogIn",
        txtUser: "#txtUser",
        txtPasswordLogin: "#txtPasswordLogin",
        hidAuthToken: "#hidAuthToken",
        chkTipo: "#chkTipo",
        btnForm: "button",
        lnkChangeModal: ".row_modal_box a",
        btn_social: ".btn_social",
        urlAjax: "/auth/new-login-ajax/",
        dropRightMenu: "#dropRightMenu",
        showRightPush: "#showRightPush"
    }, catchDom = function () {
        dom.modalLoginUser = $(st.modalLoginUser), dom.frmUserLogIn = $(st.frmUserLogIn), dom.txtUser = $(st.txtUse, dom.frmUserLogIn), dom.txtPasswordLogin = $(st.txtPasswordLogin, dom.frmUserLogIn), dom.hidAuthToken = $(st.hidAuthToken, dom.frmUserLogIn), dom.btnForm = $(st.btnForm, dom.frmUserLogIn), dom.chkTipo = $(st.chkTipo, dom.frmUserLogIn), dom.lnkChangeModal = $(st.lnkChangeModal, dom.modalLoginUser), dom.btn_social = $(st.btn_social, dom.modalLoginUser), dom.dropRightMenu = $(st.dropRightMenu), dom.showRightPush = $(st.showRightPush)
    }, afterCatchDom = function () {
        st.returnNormal = dom.frmUserLogIn.attr("action"),
                fn.initValidateForm(), Sb.trigger("modalSwitcherReInitialize", [{
                modal: st.modalLoginUser,
                btnShowModal: st.btnLoginInit,
                fancyBoxSetting: {
                    maxWidth: 300,
                    padding: 0,
                    arrows: !1
                },
                beforeShowModal: function (_this) {
                    fn.setClassNecessary(), fn.setRedirectAjax(_this), fn.setRolLogin(_this)
                },
                hideModal: function () {
                    dom.txtUser.val(""), dom.txtPasswordLogin.val("")
                },
                hideModalMobile: function () {
                    dom.txtUser.val(""), dom.txtPasswordLogin.val("")
                }
            }])
    }, fn = {
        changeModal: function (e) {
            e.preventDefault(), Sb.trigger("fnCloseModal"), Sb.trigger("fnOpenModal", st.modalRegisterUser, {
                maxWidth: 464,
                padding: 0,
                arrows: !1
            })
        },
        initValidateForm: function () {
            dom.frmUserLogIn.tooltipster({
                timer: 1500
            }), dom.frmUserLogIn.validate({
                rules: {
                    txtUser: {
                        nEmail: !0
                    },
                    txtBirthDay: {
                        date: !0
                    }
                },
                submitHandler: function (form) {
                    dom.btnForm.attr({
                        disabled: "",
                        loading: ""
                    }), $.ajax({
                        url: st.urlAjax,
                        type: "POST",
                        dataType: "json",
                        data: dom.frmUserLogIn.serialize(),
                        success: function (response) {
                            "1" === response.status ? window.location = st.returnNormal : (dom.hidAuthToken.val(response.hashToken), dom.frmUserLogIn.tooltipster("content", response.msg), dom.frmUserLogIn.tooltipster("show"), dom.btnForm.removeAttr("disabled loading"), dom.txtPasswordLogin.val(""), dom.txtUser.parent().addClass("error"), dom.txtPasswordLogin.focus())
                        },
                        error: function (response) {
                            dom.btnForm.removeAttr("disabled loading")
                        }
                    })
                }
            })
        },
        setClassNecessary: function () {
            dom.showRightPush.removeClass("active"), $("body").removeClass("active_menu_push_toleft"), dom.dropRightMenu.removeClass("active_menu_open")
        },
        setRedirectAjax: function (_this) {
            void 0 !== _this.data("redirect") && (st.returnNormal = _this.data("redirect"))
        },
        setRolLogin: function (_this) {
            var rol;
            rol = _this.data("type"), void 0 === rol && (rol = yOSON.module), fn.switchDataByRol(rol), dom.chkTipo.val(rol)
        },
        switchDataByRol: function (rol) {
            switch (rol) {
                case "empresa":
                    dom.btn_social.hide(), dom.lnkChangeModal.attr("href", "/empresa/registro-empresa").off("click.changeModal");
                    break;
                case "postulante":
                    dom.btn_social.show(), dom.lnkChangeModal.attr("href", "#").on("click.changeModal", fn.changeModal)
            }
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("modal_not_enough_information", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        modalToShow: "#modalNotEnoughInformation",
        closeModal: "#clsModalNotEnoughInformation",
        btnTriggerModal: ".trigger_modal_not_enough_information",
        titleModal: ".title",
        titleDefault: null
    }, catchDom = function () {
        dom.closeModal = $(st.closeModal), dom.btnTriggerModal = $(st.btnTriggerModal), dom.titleModal = $(st.titleModal, st.modalToShow)
    }, afterCatchDom = function () {
        functions.fnInitModal()
    }, suscribeEvents = function () {
        dom.closeModal.on("click", events.eCloseModal)
    }, events = {
        eCloseModal: function (e) {
            Sb.trigger("fnCloseModal")
        }
    }, functions = {
        fnInitModal: function () {
            Sb.trigger("modalSwitcherReInitialize", [{
                    modal: st.modalToShow,
                    btnShowModal: st.btnTriggerModal,
                    fancyBoxSetting: {
                        minWidth: 500,
                        padding: 5,
                        arrows: !1,
                        modal: !0
                    },
                    beforeShowModal: function (_this) {
                        var titleMsg;
                        Sb.trigger("fnDisableUbicationTextField"), dom.closeModal.removeAttr("disabled"), st.titleDefault = dom.titleModal.text(), titleMsg = $(_this).data("msg"), "" !== titleMsg ? dom.titleModal.text(titleMsg) : dom.titleModal.text(st.titleDefault)
                    },
                    hideModal: function () {},
                    hideModalMobile: function () {}
                }])
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("modal_recover_password", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        modalRecoverPassword: "#modalRecoverPassword",
        btnRecover: ".recover_init",
        frmRecoverPassword: "#frmRecoverPassword",
        txtEmailForgot: "#txtEmailForgot",
        hidRecoverPassword: "#hidRecoverPassword",
        btnForm: "button",
        urlAjax: "/auth/new-recuperar-clave/"
    }, catchDom = function () {
        dom.frmRecoverPassword = $(st.frmRecoverPassword), dom.txtEmailForgot = $(st.txtEmailForgot), dom.hidRecoverPassword = $(st.hidRecoverPassword), dom.btnForm = $(st.btnForm, dom.frmRecoverPassword)
    }, afterCatchDom = function () {
        st.returnNormal = dom.frmRecoverPassword.attr("action"), functions.initValidateForm()
    }, suscribeEvents = function () {
        Sb.trigger("modalSwitcherReInitialize", [{
                modal: st.modalRecoverPassword,
                btnShowModal: st.btnRecover,
                fancyBoxSetting: {
                    maxWidth: 500,
                    padding: 0,
                    arrows: !1
                },
                hideModal: function () {
                    dom.txtEmailForgot.val("")
                },
                hideModalMobile: function () {
                    dom.txtEmailForgot.val("")
                }
            }])
    }, functions = {
        initValidateForm: function () {
            dom.frmRecoverPassword.tooltipster({
                timer: 1500
            }), dom.frmRecoverPassword.validate({
                rules: {
                    txtEmailForgot: {
                        nEmail: !0
                    }
                },
                submitHandler: function (form) {
                    dom.btnForm.attr({
                        disabled: "",
                        loading: ""
                    }), $.ajax({
                        url: st.urlAjax,
                        type: "POST",
                        dataType: "json",
                        data: dom.frmRecoverPassword.serialize(),
                        success: function (response) {
                            "1" === response.status ? window.location = st.returnNormal : (dom.hidRecoverPassword.val(response.hashToken), dom.frmRecoverPassword.tooltipster("content", response.msg), dom.frmRecoverPassword.tooltipster("show"), dom.btnForm.removeAttr("disabled loading"), dom.txtEmailForgot.val(""), dom.txtEmailForgot.parent().addClass("error"), dom.txtEmailForgot.focus())
                        },
                        error: function (response) {
                            dom.btnForm.removeAttr("disabled loading")
                        }
                    })
                }
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("modal_register_form", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        modalLoginUser: "#modalLoginUser",
        modalRegisterUser: "#modalRegisterUser",
        btnRegisterInit: ".register_init",
        frmUserRegistrationFast: "#frmUserRegistrationFast",
        txtBirthDay: "#txtBirthDay",
        hidAuthToken: "#auth_token",
        btnForm: "button",
        lnkChangeModal: ".row_modal_box",
        urlAjax: "/registro/registro-rapido",
        dropRightMenu: "#dropRightMenu",
        showRightPush: "#showRightPush",
        body: "body"
    }, catchDom = function () {
        dom.modalRegisterUser = $(st.modalRegisterUser), dom.frmUserRegistrationFast = $(st.frmUserRegistrationFast), dom.txtBirthDay = $(st.txtBirthDay, dom.frmUserRegistrationFast), dom.hidAuthToken = $(st.hidAuthToken, dom.frmUserRegistrationFast), dom.btnForm = $(st.btnForm, dom.frmUserRegistrationFast), dom.lnkChangeModal = $(st.lnkChangeModal, dom.modalRegisterUser), dom.dropRightMenu = $(st.dropRightMenu), dom.showRightPush = $(st.showRightPush), dom.body = $(st.body)
    }, afterCatchDom = function () {
        dom.txtBirthDay.inputmask("date", {
            yearrange: {
                minyear: dom.txtBirthDay.attr("minyear"),
                maxyear: dom.txtBirthDay.attr("maxyear")
            }
        }), functions.initValidateForm(), Sb.trigger("modalSwitcherReInitialize", [{
                modal: st.modalRegisterUser,
                btnShowModal: st.btnRegisterInit,
                fancyBoxSetting: {
                    maxWidth: 464,
                    padding: 0,
                    arrows: !1
                },
                beforeShowModal: function (_this) {
                    _this = $(this), dom.showRightPush.removeClass("active"), dom.body.removeClass("active_menu_push_toleft"), dom.dropRightMenu.removeClass("active_menu_open")
                },
                hideModal: function () {
                    $(":input,:password", dom.modalRegisterUser).val("")
                },
                hideModalMobile: function () {
                    $(":input,:password", dom.modalRegisterUser).val("")
                }
            }])
    }, suscribeEvents = function () {
        dom.lnkChangeModal.on("click", "a", functions.changeModal)
    }, functions = {
        changeModal: function (e) {
            e.preventDefault(), Sb.trigger("fnCloseModal"), Sb.trigger("fnOpenModal", st.modalLoginUser, {
                maxWidth: 300,
                padding: 0,
                arrows: !1
            })
        },
        initValidateForm: function () {
            dom.frmUserRegistrationFast.tooltipster({
                timer: 1500
            }), dom.frmUserRegistrationFast.validate({
                rules: {
                    pswd2: {
                        equalTo: "#pswd"
                    },
                    txtName: {
                        alphabet: !0
                    },
                    txtFirstLastName: {
                        alphabet: !0
                    },
                    txtSecondLastName: {
                        alphabet: !0
                    },
                    txtBirthDay: {
                        dateMask: !0
                    },
                    txtEmail: {
                        nEmail: !0
                    }
                },
                submitHandler: function (form) {
                    dom.btnForm.attr({
                        disabled: "",
                        loading: ""
                    }), $.ajax({
                        url: st.urlAjax,
                        type: "POST",
                        dataType: "json",
                        data: dom.frmUserRegistrationFast.serialize(),
                        success: function (response) {
                            1 === response.status ? (yOSON.utils.showMessage($("h2", dom.frmUserRegistrationFast), "success", response.message), setTimeout(function () {
                                window.location = yOSON.baseHost + response.redirect
                            }, 1e3)) : (yOSON.utils.showMessage($("h2", dom.frmUserRegistrationFast), "error", response.message), dom.hidAuthToken.val(response.hashToken), dom.btnForm.removeAttr("disabled loading"))
                        },
                        error: function (response) {
                            dom.btnForm.removeAttr("disabled loading")
                        }
                    })
                }
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.inputmask/dist/jquery.inputmask.bundle.min.js"]), yOSON.AppCore.addModule("modal_switcher", function (Sb) {
    var factory, fnCloseModal, fnOpenModal, initialize, publicInitialize;
    return factory = function (oP) {
        this.st = {
            contentAll: ".page_default",
            pageModal: ".page_modal",
            modal: null,
            title: ".title",
            btnCloseModal: ".icon_cross",
            btnShowModal: null,
            fancyBoxSetting: null,
            allErrors: ".error",
            device_mobile: device.mobile()
        }, this.dom = {}, this.oP = oP
    }, factory.prototype = {
        catchDom: function () {
            this.dom.contentAll = $(this.st.contentAll), this.dom.modal = $(this.st.modal), this.dom.pageModal = $(this.st.pageModal), this.dom.title = $(this.st.title, this.dom.modal), this.dom.btnCloseMobileModal = $(this.st.btnCloseModal, this.dom.title), this.dom.btnShowModal = $(this.st.btnShowModal), this.dom.window = $(window)
        },
        afterCatchDom: function () {
            this.st.device_mobile && (this.dom.title.removeClass("title"), this.dom.title.addClass("sub_title_mobile"))
        },
        suscribeEvents: function () {
            this.dom.btnShowModal.on("click", {
                inst: this
            }, this.eShowModal), this.st.device_mobile && this.dom.btnCloseMobileModal.on("click", {
                inst: this
            }, this.eHideModalMobile)
        },
        eHideModalMobile: function (event) {
            var dom, st, that;
            event.stopPropagation(), that = event.data.inst, st = that.st, dom = that.dom, that.fnHideModal()
        },
        eShowModal: function (event) {
            var _this, dom, st, that;
            event.preventDefault(), that = event.data.inst, st = that.st, dom = that.dom, _this = $(this), "function" == typeof st.beforeShowModalCallback && st.beforeShowModalCallback(_this), st.device_mobile ? (dom.contentAll.addClass("hide"), dom.pageModal.addClass("hide"), dom.modal.removeClass("hide"), dom.window.scrollTop(0), "function" == typeof st.showModalCallBack && st.showModalCallBack(_this)) : ("function" == typeof st.showModalMobileCallBack && (st.fancyBoxSetting.afterLoad = st.showModalMobileCallBack), st.fancyBoxSetting.afterClose = function () {
                $(st.allErrors, dom.modal).removeClass("error"), "function" == typeof st.hideModalMobileCallBack && st.hideModalMobileCallBack(_this)
            }, $.fancybox.close(), $.fancybox.open(st.modal, st.fancyBoxSetting), $.prettySelect.update())
        },
        fnHideModal: function () {
            var dom, st;
            dom = this.dom, st = this.st, dom.contentAll.removeClass("hide"), dom.modal.addClass("hide"), $(st.allErrors, dom.modal).removeClass("error"), st.hideModalCallBack()
        },
        execute: function () {
            this.st = $.extend({}, this.st, this.oP), this.st.beforeShowModalCallback = this.oP.beforeShowModal, this.st.showModalCallBack = this.oP.showModal, this.st.showModalMobileCallBack = this.oP.showModalMobile, this.st.hideModalCallBack = this.oP.hideModal, this.st.hideModalMobileCallBack = this.oP.hideModalMobile, this.catchDom(), this.afterCatchDom(), this.suscribeEvents()
        }
    }, publicInitialize = function (arrOp) {
        $.each(arrOp, function (i, obj) {
            var instance;
            instance = new factory(obj), instance.execute()
        })
    }, fnCloseModal = function (arr) {
        var dom;
        dom = {
            contentAll: $(".page_default"),
            pageModal: $(".page_modal:visible")
        }, device.mobile() ? (dom.contentAll.removeClass("hide"), dom.pageModal.addClass("hide"), $(".error", dom.modal).removeClass("error")) : $.fancybox.close()
    }, fnOpenModal = function (idModal, paramsFancybox) {
        device.mobile() ? ($(".page_default").addClass("hide"), $(idModal).removeClass("hide")) : $.fancybox.open(idModal, paramsFancybox)
    }, initialize = function () {
        Sb.events(["fnCloseModal"], fnCloseModal, this), Sb.events(["fnOpenModal"], fnOpenModal, this), Sb.events(["modalSwitcherReInitialize"], publicInitialize, this)
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("picker", function (Sb) {
    var catchDom, dom, functions, initialize, st, suscribeEvents;
    return st = {
        picker: ".picker",
        pickerSpan: ".picker > span",
        txtExpiry: "#xCaducidad"
    }, dom = {}, catchDom = function () {
        dom.picker = $(st.picker), dom.pickerSpan = $(st.pickerSpan), dom.txtExpiry = $(st.txtExpiry)
    }, suscribeEvents = function () {
        dom.pickerSpan.on("click", functions.clickPicker)
    }, functions = {
        clickPicker: function () {
            dom.txtExpiry.trigger("focus")
        },
        picker: function () {
            $.datepicker.setDefaults($.datepicker.regional.es), dom.txtExpiry.datepicker({
                dateFormat: "dd/mm/y",
                onSelect: function () {
                    dom.txtExpiry.trigger("blur")
                }
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents(), functions.picker()
    }, {
        init: initialize
    }
}, ["js/libs/jquery-ui/jquery-ui.min.js", "js/libs/jquery-ui/ui/i18n/datepicker-es.js"]), yOSON.AppCore.addModule("placeholder_ie", function (Sb) {
    var afterCathDom, catchDom, dom, events, initialize, publicInitialize, st;
    return dom = {}, st = {
        inputs: ":text,textarea",
        "class": "hasPlaceholder",
        frmAll: "form"
    }, catchDom = function () {
        dom.inputs = $(st.inputs), dom.frmAll = $(st.frmAll)
    }, afterCathDom = function () {
        !isInternetExplorer() || "8.0" !== browser.version && "9.0" !== browser.version || (dom.inputs.on("focus", events.onFocus), dom.inputs.on("blur", events.onBlur), dom.frmAll.on("submit", events.onSubmit), dom.inputs.trigger("blur"), $(document.activeElement).trigger("focus"))
    }, events = {
        onFocus: function (e) {
            var _this;
            _this = $(this), "" !== _this.attr("placeholder") && _this.val() === _this.attr("placeholder") && _this.val("").removeClass("hasPlaceholder")
        },
        onBlur: function (e) {
            var _this;
            _this = $(this), "" === _this.attr("placeholder") || "" !== _this.val() && _this.val() !== _this.attr("placeholder") || _this.val(_this.attr("placeholder")).addClass("hasPlaceholder")
        },
        onSubmit: function (e) {
            $(this).find(".hasPlaceholder").each(function () {
                var i;
                i = $(this), i.val() === i.attr("placeholder") && i.val("")
            })
        }
    }, publicInitialize = function () {
        catchDom(), afterCathDom()
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCathDom(), Sb.events(["placeholderReInitialize"], publicInitialize, this)
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("plugins_pretty_select", function (Sb) {
    var afterCatchDom, catchDom, dom, initialize, st, suscribeEvents;
    return dom = {}, st = {
        tagSelect: ".pretty_select",
        tagSelect2: ".pretty_select_2"
    }, catchDom = function () {
        dom.tagSelect = $(st.tagSelect), dom.tagSelect2 = $(st.tagSelect2)
    }, afterCatchDom = function () {
        dom.tagSelect2.prettySelect()
    }, suscribeEvents = function () {}, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/pretty-select.js"]), yOSON.AppCore.addModule("slider_companies_logos", function (Sb) {
    var afterCatchDom, catchDom, dom, initialize, st, suscribeEvents;
    return dom = {}, st = {
        sliderTcn: ".slider_tcn_companies_wrapper"
    }, catchDom = function () {
        dom.sliderTcn = $(st.sliderTcn)
    }, afterCatchDom = function () {
        isInternetExplorer() && "8.0" === browser.version ? dom.sliderTcn.slick({
            autoplay: !0,
            infinite: !0,
            slidesToShow: 7,
            slidesToScroll: 7,
            lazyLoad: "ondemand"
        }) : dom.sliderTcn.slick({
            autoplay: !0,
            infinite: !0,
            slidesToShow: 5,
            slidesToScroll: 5,
            lazyLoad: "ondemand",
            responsive: [{
                    breakpoint: 900,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5
                    }
                }, {
                    breakpoint: 860,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4
                    }
                }, {
                    breakpoint: 667,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }]
        })
    }, suscribeEvents = function () {}, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/slick.js/slick/slick.min.js"]), yOSON.AppCore.addModule("suggested_add_option", function (Sb) {
    var afterCatchDom, catchDom, context, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, context = {}, st = {
        txtSuggestedField: "",
        closestContainer: "",
        btnAddOption: "",
        tplTags: "",
        classContainer: "",
        classTagContainer: "",
        classClose: "",
        classItem: "",
        disabledEvents: "",
        listResult: ".autocomplete_result",
        factoryTags: null,
        limitKey: "",
        limitItems: 0,
        regexA: new RegExp("[àáâãäå]", "g"),
        regexE: new RegExp("[èéêë]", "g"),
        regexI: new RegExp("[ìíîï]", "g"),
        regexO: new RegExp("[òóôõö]", "g"),
        regexU: new RegExp("[ùúûü]", "g")
    }, catchDom = function () {
        st.limitItems = parseInt(yOSON.maxItems[st.limitKey]), dom.container = $(st.classContainer), dom.tagContainer = $(st.classTagContainer), dom.txtSuggestedField = $(st.txtSuggestedField, dom.container), dom.btnAddOption = $(st.btnAddOption, dom.container), dom.miniForm = $(st.closestContainer, dom.container), dom.item = $("." + st.classItem, dom.tagContainer), dom.close = $(st.classClose, dom.tagContainer), dom.listResult = $(st.listResult, dom.tagContainer)
    }, afterCatchDom = function () {
        st.factoryTags = _.template($(st.tplTags).html()), context = {
            btnAddOption: dom.btnAddOption,
            tagContainer: dom.tagContainer,
            container: dom.container,
            txtSuggestedField: dom.txtSuggestedField,
            limitItems: st.limitItems,
            classItem: st.classItem,
            miniForm: dom.miniForm,
            classClose: st.classClose,
            factoryTags: st.factoryTags
        }
    }, suscribeEvents = function () {
        st.disabledEvents === !1 && (dom.btnAddOption.length > 0 && dom.btnAddOption.on("click", events.eDrawTagsTriggerClick), dom.txtSuggestedField.on("keyup", events.eDrawTagsTriggerKey))
    }, events = {
        eDrawTagsTriggerClick: function (e) {
            var id, value;
            value = dom.txtSuggestedField.val(), id = "", functions.fnDrawTagsManager(value, id, context)
        },
        eDrawTagsTriggerKey: function (e) {
            var id, value;
            return e.preventDefault(), e.stopPropagation(), 13 === e.which && (value = dom.txtSuggestedField.val(), id = "", functions.fnDrawTagsManager(value, id, context)), !1
        }
    }, functions = {
        fnDrawTagsManager: function (value, id, ctxt) {
            var flag, quantity;
            return flag = ctxt.txtSuggestedField.attr("loading"), "undefined" != typeof flag && flag !== !1 ? !1 : $.trim(value).length <= 0 || $.trim(value).length > 80 ? !1 : (quantity = $("." + ctxt.classItem).length, quantity >= ctxt.limitItems ? !1 : (ctxt.btnAddOption.length > 0 && ctxt.btnAddOption.attr("disabled", "disabled"), ctxt.txtSuggestedField.val(""), $(st.listResult).html(""), $(st.listResult).addClass("hide"), void(functions.fnValidateRepeatedText(value, id, ctxt) && (functions.fnDrawTag(value, id, ctxt), functions.fnBlockItems(quantity, ctxt)))))
        },
        fnValidateRepeatedText: function (value, id, ctxt) {
            var cleanedKey, isUnique, values;
            return values = [], cleanedKey = $.trim(value.toLowerCase()), cleanedKey = cleanedKey.replace(st.regexA, "a"), cleanedKey = cleanedKey.replace(st.regexE, "e"), cleanedKey = cleanedKey.replace(st.regexI, "i"), cleanedKey = cleanedKey.replace(st.regexO, "o"), cleanedKey = cleanedKey.replace(st.regexU, "u"), values.push(cleanedKey), isUnique = !0, $("." + ctxt.classItem).each(function (index, element) {
                var key;
                return key = $.trim($(element).find("label").html()).toLowerCase(), key = key.replace(st.regexA, "a"), key = key.replace(st.regexE, "e"), key = key.replace(st.regexI, "i"), key = key.replace(st.regexO, "o"), key = key.replace(st.regexU, "u"), -1 !== $.inArray(key, values) ? (functions.showItemRepeated(element, ctxt), isUnique = !1, !1) : void values.push(key)
            }), isUnique
        },
        fnDrawTag: function (value, id, ctxt) {
            var data, html;
            data = {
                classItem: ctxt.classItem,
                classClose: ctxt.classClose,
                value: value,
                id: id
            }, html = ctxt.factoryTags(data), ctxt.tagContainer.append(html)
        },
        fnBlockItems: function (quantity, ctxt) {
            quantity + 1 >= ctxt.limitItems && (ctxt.txtSuggestedField.attr("disabled", "disabled"), ctxt.txtSuggestedField.parent().attr("disabled", "disabled"))
        },
        showItemRepeated: function (element, ctxt) {
            element = $(element), ctxt.miniForm.prepend('<div class="text_repeated_message">La opción seleccionada ya se encuentra en la lista.</div>'), element.addClass("item_repeated"), setTimeout(function () {
                $(".text_repeated_message", ctxt.container).fadeOut(function () {
                    $(".text_repeated_message", ctxt.container).remove()
                }), element.removeClass("item_repeated")
            }, 2e3)
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents(), Sb.events(["fnDrawTagsManager"], functions.fnDrawTagsManager, this)
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js"]), yOSON.AppCore.addModule("suggested_autocomplete_options", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        txtSuggestedField: "",
        urlAjax: "",
        btnAddOption: "",
        classItem: "",
        classContainer: "",
        classTagContainer: "",
        closestContainer: "",
        classClose: "",
        tplTags: "",
        limitKey: "",
        limitItems: 0,
        enableField: !0,
        disabledField: !1,
        numLettersToStart: 0
    }, catchDom = function () {
        st.limitItems = parseInt(yOSON.maxItems[st.limitKey]), dom.container = $(st.classContainer), dom.tagContainer = $(st.classTagContainer), dom.txtSuggestedField = $(st.txtSuggestedField, dom.container), dom.btnAddOption = $(st.btnAddOption, dom.container), dom.miniForm = $(st.closestContainer, dom.container)
    }, afterCatchDom = function () {
        st.factoryTags = _.template($(st.tplTags).html()), dom.txtSuggestedField.custom_autocomplete({
            urlAutocomplete: st.urlAjax,
            getTokenAjax: yOSON.utils.getToken,
            numLettersToStart: st.numLettersToStart,
            fnAfterUpdateText: functions.fnSelectSkill,
            fnBeforeSendRequest: functions.fnBeforeSendRequest,
            fnAfterSendRequest: functions.fnAfterSendRequest,
            fnAfterCleanSearch: functions.fnAfterCleanXHR
        })
    }, functions = {
        fnSelectSkill: function (value, id) {
            var context;
            context = {
                btnAddOption: dom.btnAddOption,
                tagContainer: dom.tagContainer,
                container: dom.container,
                txtSuggestedField: dom.txtSuggestedField,
                limitItems: st.limitItems,
                classItem: st.classItem,
                miniForm: dom.miniForm,
                classClose: st.classClose,
                factoryTags: st.factoryTags
            }, Sb.trigger("fnDrawTagsManager", value, id, context), functions.fnToggleEnableField(st.disabledField)
        },
        fnBeforeSendRequest: function () {
            functions.fnToggleEnableField(st.disabledField)
        },
        fnAfterSendRequest: function () {
            var quantity;
            quantity = $("." + st.classItem).length, quantity < st.limitItems && functions.fnToggleEnableField(st.enableField)
        },
        fnAfterCleanXHR: function () {
            functions.fnToggleEnableField(st.disabledField)
        },
        fnToggleEnableField: function (accion) {
            return 0 === dom.btnAddOption.length ? !1 : void(accion === st.disabledField ? dom.btnAddOption.attr("disabled", "disabled") : dom.btnAddOption.removeAttr("disabled"))
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.autocomplete.custom.js"]), yOSON.AppCore.addModule("suggested_close_tag", function (Sb) {
    var catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        txtSuggestedField: "",
        btnAddSkill: "",
        classContainer: "",
        classTagContainer: "",
        classClose: "",
        classItem: "",
        limitKey: "",
        limitItems: 0
    }, catchDom = function () {
        st.limitItems = parseInt(yOSON.maxItems[st.limitKey]), dom.container = $(st.classContainer), dom.tagContainer = $(st.classTagContainer), dom.txtSuggestedField = $(st.txtSuggestedField, dom.container), dom.btnAddSkill = $(st.btnAddSkill, dom.container), dom.close = $(st.classClose, dom.tagContainer)
    }, suscribeEvents = function () {
        dom.tagContainer.on("click", st.classClose, events.eCloseElement)
    }, events = {
        eCloseElement: function (e) {
            var quantity;
            quantity = $("." + st.classItem).length, $(this).parent().remove(), quantity <= st.limitItems && functions.fnEnableItems()
        }
    }, functions = {
        fnEnableItems: function () {
            var txtSkillValue;
            dom.txtSuggestedField.removeAttr("disabled"), dom.txtSuggestedField.parent().removeAttr("disabled"), txtSkillValue = $.trim(dom.txtSuggestedField.val()), txtSkillValue.length > 0 && dom.btnAddSkill.removeAttr("disabled")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("suggested_delete", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        context: ".suggested_tabs_content",
        jobsAds: ".jobs_ads",
        button: ".btn_delete_ads",
        subMenuBox: ".sub_menu_box",
        divNotEnoughMessage: ".not_enough_message",
        tabsContainer: ".suggested_tabs_header",
        tplJobAds: "#tplJobAds",
        htmltplJobAds: null
    }, catchDom = function () {
        dom.context = $(st.context), dom.tplJobAds = $(st.tplJobAds), dom.subMenuBox = $(st.subMenuBox), dom.divNotEnoughMessage = $(st.divNotEnoughMessage), dom.tabsContainer = $(st.tabsContainer)
    }, afterCatchDom = function () {
        0 !== dom.tplJobAds.length && (st.htmltplJobAds = _.template(dom.tplJobAds.html()))
    }, suscribeEvents = function () {
        dom.context.on("click", st.button, events.eDeleteAds)
    }, events = {
        eDeleteAds: function (e) {
            var self;
            e.preventDefault(), self = $(this), functions.disabledButtons(self), functions.getDataAjax(self)
        }
    }, functions = {
        getDataAjax: function (self) {
            $.ajax({
                type: "POST",
                url: self.data("del"),
                data: {
                    id: self.data("id"),
                    page: self.data("page"),
                    token_ajax: yOSON.token
                },
                beforeSend: function () {
                    $("li", dom.tabsContainer).attr("disabled", "disabled")
                }
            }).done(function (response) {
                var parentSelf;
                response = $.parseJSON(response), yOSON.token = response.token_ajax, functions.enabledButtons(), 1 === response.status ? (parentSelf = self.parents(st.jobsAds), functions.deleteJobAd(parentSelf), functions.setDataTemplate(response.job, response), functions.showNotEnoughMessage()) : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
            }).fail(functions.showError).always(function () {
                $("li", dom.tabsContainer).removeAttr("disabled")
            })
        },
        setDataTemplate: function (jobJson, response) {
            var htmlCompiled;
            $.isEmptyObject(jobJson) || (jobJson.urlHighlight = response.urlHighlight, jobJson.urlDelete = response.urlDelete, htmlCompiled = st.htmltplJobAds(jobJson), $("ul", dom.context).append(htmlCompiled))
        },
        deleteJobAd: function (self) {
            self.slideUp(function () {
                $(this).parent().remove(), functions.changeTotalNumber()
            })
        },
        showNotEnoughMessage: function () {
            0 === $(st.jobsAds).length && dom.divNotEnoughMessage.slideDown()
        },
        changeTotalNumber: function () {
            var countJobs, divNumber;
            divNumber = $(".is_active", dom.tabsContainer).find(".number"), countJobs = parseInt(divNumber.text()), divNumber.text(--countJobs), 0 === countJobs && dom.context.html('<ul><li class="not_enough_message"><p>No hay avisos para mostrar.</p></li></ul>')
        },
        enabledButtons: function () {
            $("button", dom.context).removeAttr("disabled"), $("button[loading]", dom.context).removeAttr("loading")
        },
        disabledButtons: function (self) {
            self.attr("loading", ""), $("button", dom.context).attr("disabled", "")
        },
        showError: function (jqXHR, textStatus, errorThrown) {
            functions.enabledButtons(), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js"]), yOSON.AppCore.addModule("suggested_hightlight_add", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        context: ".suggested_tabs_content",
        jobsAds: ".jobs_ads",
        button: ".btn_hightlight",
        subMenuBox: ".sub_menu_box",
        divNotEnoughMessage: ".not_enough_message",
        tabsContainer: ".suggested_tabs_header",
        tplJobAds: "#tplJobAds",
        htmltplJobAds: null,
        parentSelf: null,
        callNewJob: !0
    }, catchDom = function () {
        dom.context = $(st.context), dom.tplJobAds = $(st.tplJobAds), dom.button = $(st.button), dom.subMenuBox = $(st.subMenuBox), dom.tabsContainer = $(st.tabsContainer), dom.divNotEnoughMessage = $(st.divNotEnoughMessage)
    }, afterCatchDom = function () {
        0 !== dom.tplJobAds.length && (st.htmltplJobAds = _.template(dom.tplJobAds.html()))
    }, suscribeEvents = function () {
        dom.context.on("click", st.button, events.eHightlightAds)
    }, events = {
        eHightlightAds: function (e) {
            var self;
            e.preventDefault(), self = $(this), $(".icon", self).hasClass("selected") || (functions.disabledButtons(self), functions.getDataAjax(self))
        }
    }, functions = {
        getDataAjax: function (self) {
            $.ajax({
                type: "POST",
                url: self.data("highlight"),
                data: {
                    id: self.data("id"),
                    page: self.data("page"),
                    urlaviso: self.data("urlaviso"),
                    token_ajax: yOSON.token,
                    beforeSend: function () {
                        $("li", dom.tabsContainer).attr("disabled", "disabled")
                    }
                }
            }).done(function (response) {
                var parentSelf;
                response = $.parseJSON(response), yOSON.token = response.token_ajax, functions.enabledButtons(), 1 === response.status ? st.callNewJob ? (parentSelf = self.parents(st.jobsAds), functions.deleteJobAd(parentSelf), functions.setDataTemplate(response.job, response), functions.showNotEnoughMessage()) : $(".icon", self).addClass("selected").attr("title", "Eliminar de favoritos") : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
            }).fail(functions.showError).always(function () {
                $("li", dom.tabsContainer).removeAttr("disabled")
            })
        },
        deleteJobAd: function (self) {
            self.slideUp(function () {
                $(this).parent().remove(), functions.changeTotalNumber()
            })
        },
        setDataTemplate: function (jobJson, response) {
            var htmlCompiled;
            $.isEmptyObject(jobJson) || (jobJson.urlHighlight = response.urlHighlight, jobJson.urlDelete = response.urlDelete, htmlCompiled = st.htmltplJobAds(jobJson), $("ul", dom.context).append(htmlCompiled))
        },
        showNotEnoughMessage: function () {
            0 === $(st.jobsAds).length && dom.divNotEnoughMessage.slideDown()
        },
        changeTotalNumber: function () {
            var countJobs, divNumber;
            divNumber = $(".is_active", dom.tabsContainer).find(".number"), countJobs = parseInt(divNumber.text()), divNumber.text(--countJobs), 0 === countJobs && dom.context.html('<ul><li class="not_enough_message"><p>No hay avisos para mostrar.</p></li></ul>')
        },
        enabledButtons: function () {
            $("button", dom.context).removeAttr("disabled"), $("button[loading]", dom.context).removeAttr("loading")
        },
        disabledButtons: function (self) {
            self.attr("loading", ""), $("button", dom.context).attr("disabled", "")
        },
        showError: function (jqXHR, textStatus, errorThrown) {
            functions.enabledButtons(), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js"]), yOSON.AppCore.addModule("suggested_hightlight_delete", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        context: ".job_description",
        jobsAds: ".jobs_ads",
        button: ".btn_hightlight",
        subMenuBox: ".sub_menu_box"
    }, catchDom = function () {
        dom.context = $(st.context), dom.button = $(st.button, dom.context), dom.subMenuBox = $(st.subMenuBox)
    }, afterCatchDom = function () {}, suscribeEvents = function () {
        dom.button.on("click", events.eHightlightAds)
    }, events = {
        eHightlightAds: function (e) {
            var self;
            e.preventDefault(), self = $(this), $(".icon", self).hasClass("selected") && (functions.disabledButtons(self), functions.getDataAjax(self))
        }
    }, functions = {
        getDataAjax: function (self) {
            $.ajax({
                type: "POST",
                url: self.data("highlight"),
                data: {
                    id: self.data("id"),
                    token_ajax: yOSON.token
                }
            }).done(function (response) {
                response = $.parseJSON(response), yOSON.token = response.token_ajax, functions.enabledButtons(), 1 === response.status ? $(".icon", self).removeClass("selected").attr("title", "Agregar a favoritos") : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
            }).fail(functions.showError)
        },
        showError: function (jqXHR, textStatus, errorThrown) {
            functions.enabledButtons(), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        },
        disabledButtons: function (self) {
            self.attr("loading", ""), $("button", dom.context).attr("disabled", "")
        },
        enabledButtons: function () {
            $("button", dom.context).removeAttr("disabled"), $("button[loading]", dom.context).removeAttr("loading")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js"]), yOSON.AppCore.addModule("tracking_factory", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents, trackingsRegister;
    return dom = {}, st = {
        tracking: ".tracking",
        txtHomeDescription: "#txtDescription",
        selHomeArea: "#selArea",
        selHomeCity: "#selCity",
        areaSearchContainer: "#fFilterAC",
        ubicacionSearchContainer: "#fFilterSUbi",
        txtFieldSearchContainer: "#fWordRS"
    }, catchDom = function () {
        dom.tracking = $(st.tracking), dom.txtHomeDescription = $(st.txtHomeDescription), dom.selHomeArea = $(st.selHomeArea), dom.selHomeCity = $(st.selHomeCity), dom.areaSearchContainer = $(st.areaSearchContainer), dom.ubicacionSearchContainer = $(st.ubicacionSearchContainer), dom.txtFieldSearchContainer = $(st.txtFieldSearchContainer), dom.collection = {}
    }, afterCatchDom = function () {
        return trackingsRegister()
    }, trackingsRegister = function () {
        dom.collection.quickly_search = functions.fnQuicklySearch, dom.collection.normal_search = functions.fnNormalSearch
    }, suscribeEvents = function () {
        $(document).on("click.tracking", st.tracking, events.trackAnalytics)
    }, events = {
        trackAnalytics: function (e) {
            var strategy;
            strategy = $(this).data("track-strategy"), dom.collection[strategy]($(this))
        }
    }, functions = {
        fnQuicklySearch: function (clicked) {
            var all, area, ciudad, descripcion;
            all = "Todos", area = dom.selHomeArea.val() || all, ciudad = dom.selHomeCity.val() || all, descripcion = dom.txtHomeDescription.val() || "Todos", descripcion === dom.txtHomeDescription.attr("placeholder") && (descripcion = all), gtrack("busqueda", "busqueda_rapida", area + "_" + ciudad + "_" + descripcion)
        },
        fnNormalSearch: function (clicked) {
            var all, areas, descripcion, ubicaciones;
            all = "Todos", areas = [], ubicaciones = [], dom.areaSearchContainer.find(".checkN:checked").each(function () {
                var value;
                value = $(this).data("value"), areas.push(value)
            }), dom.ubicacionSearchContainer.find(".checkN:checked").each(function () {
                var value;
                value = $(this).data("value"), ubicaciones.push(value)
            }), areas = 0 === areas.length ? all : areas.join("--"), ubicaciones = 0 === ubicaciones.length ? all : ubicaciones.join("--"), descripcion = dom.txtFieldSearchContainer.val() || all, gtrack("busqueda", "busqueda_normal", "buscar/q/" + descripcion + "/areas/" + areas + "/ubicacion/" + ubicaciones)
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/gtrack.js"]), yOSON.AppCore.addModule("validate_country_ubication", function (Sb) {
    var catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        selPais: "#selPais",
        txtIdUbicacion: "",
        txtUbicacion: "",
        container: ""
    }, catchDom = function () {
        dom.container = $(st.container), dom.selPais = $(st.selPais, dom.container), dom.txtUbicacion = $(st.txtUbicacion, dom.container), dom.txtIdUbicacion = $(st.txtIdUbicacion, dom.container)
    }, suscribeEvents = function () {
        dom.selPais.on("change", events.eDisableUbicationTextField)
    }, events = {
        eDisableUbicationTextField: function () {
            dom.selPais.val() === yOSON.utils.getPeruCode() ? (dom.txtUbicacion.removeAttr("disabled"), dom.txtUbicacion.parent().removeAttr("disabled")) : (functions.fnDisabledUbicationTextField(), Sb.trigger("fnAbortAutocompleteXHR"))
        }
    }, functions = {
        fnDisabledUbicationTextField: function () {
            dom.txtUbicacion.val(""), dom.txtIdUbicacion.val(""), dom.txtUbicacion.attr("disabled", ""), dom.txtUbicacion.removeClass("error"), dom.txtUbicacion.parent().removeClass("error"), dom.txtUbicacion.parent().attr("disabled", "")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), suscribeEvents(), Sb.events(["fnDisableUbicationTextField"], events.eDisableUbicationTextField, this)
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("validate_e_planning_top", function (Sb) {
    var afterCatchDom, catchDom, dom, fn, initialize, st, suscribeEvents;
    return dom = {}, st = {
        body: "body",
        e_planning: ".top_e_planning",
        parallaxMain: "#parallaxMain",
        megaBanner: "#megaBannerEplanning"
    }, catchDom = function () {
        dom.body = $(st.body), dom.e_planning = $(st.e_planning), dom.megaBanner = $(st.megaBanner), dom.parallaxMain = $(st.parallaxMain)
    }, afterCatchDom = function () {
        fn.validateExistEplaning(), fn.validateMegaBanner()
    }, suscribeEvents = function () {}, fn = {
        evaluateActiveClass: function () {
            var windowTop;
            windowTop = $(window).scrollTop(), windowTop > 1 ? dom.body.removeClass("is_active_ads") : dom.body.addClass("is_active_ads")
        },
        validateExistEplaning: function () {
            setTimeout(function () {
                0 !== dom.e_planning.find("object").length && (fn.evaluateActiveClass(), $(window).on("scroll", fn.evaluateActiveClass))
            }, 1e3)
        },
        validateMegaBanner: function () {
            setTimeout(function () {
                0 !== dom.megaBanner.find("object").length && (dom.megaBanner.hide(), dom.parallaxMain.hide(), dom.megaBanner.removeClass("hide"), dom.megaBanner.fadeIn())
            }, 4e3)
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("validate_forms", function (Sb) {
    var afterCatchDom, beforeCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        frmType1: "form.type1,.form_valide_tooltip",
        fieldset: "fieldset",
        inputs: "input,textarea",
        classSelected: "selected",
        classError: "error"
    }, beforeCatchDom = function () {
        functions.configMessages(), functions.validateExtraFunctions()
    }, catchDom = function () {
        dom.window = $(window), dom.frmType1 = $(st.frmType1), dom.fieldset = $(st.fieldset, dom.frmType1), dom.inputs = $(st.inputs, dom.frmType1)
    }, afterCatchDom = function () {
        0 !== dom.frmType1.length && (functions.initTooltip(), functions.initFormValidate())
    }, suscribeEvents = function () {
        $(document).on("click", st.fieldset, events.showInputSelected), $(document).on("blur", st.inputs, events.removeClassSelected), $(document).on("focus", st.inputs, events.addClassSelected)
    }, events = {
        showInputSelected: function (e) {
            var _this, _thisInput;
            _this = $(this), _thisInput = $(st.inputs, _this), 0 === $("select", _this).length && (_thisInput.is(":checkbox") && _thisInput.is(":radio") && !_thisInput.is("textarea") || (_this.parents(st.frmType1).find("." + st.classSelected).removeClass(st.classSelected), _this.addClass(st.classSelected), _thisInput.focus()))
        },
        removeClassSelected: function () {
            var _thisInput;
            _thisInput = $(this), _thisInput.parents(st.fieldset).removeClass(st.classSelected)
        },
        addClassSelected: function () {
            var _thisInput;
            _thisInput = $(this), _thisInput.parents(st.fieldset).addClass(st.classSelected)
        }
    }, functions = {
        initTooltip: function () {
            $.fn.tooltipster("setDefaults", {
                trigger: "custom",
                multiple: !0,
                updateAnimation: !1,
                timer: 1e3
            }), $(st.fieldset).tooltipster()
        },
        initFormValidate: function () {
            $.validator.setDefaults({
                onfocusout: !1,
                errorPlacement: function (error, element) {
                    var _parent, lastError, newError;
                    _parent = $(element).parents(st.fieldset), newError = $(error).text(), lastError = _parent.data("lastError"), _parent.data("lastError", newError), _parent.removeClass(st.classSelected), _parent.addClass(st.classError), "" !== newError && newError !== lastError && (_parent.tooltipster("content", newError), functions.changePositionTooltip(_parent)), _parent.tooltipster("show")
                },
                success: function (label, element) {
                    var _parent;
                    return _parent = $(element).parents(st.fieldset), _parent.tooltipster("hide"), _parent.removeClass(st.classError), _parent.removeClass(st.classSelected)
                }
            })
        },
        changePositionTooltip: function (_this) {
            var posHtml, posWrap;
            posHtml = dom.window.scrollTop(), posWrap = parseInt(_this.parents("form").offset().top) - 150, posHtml > posWrap ? _this.tooltipster("option", "position", "bottom") : _this.tooltipster("option", "position", "top")
        },
        configMessages: function () {
            $.extend($.validator.messages, {
                required: "Este campo es requerido",
                equalTo: "El valor debe ser idéntico",
                email: "Ingrese un email válido"
            })
        },
        validateExtraFunctions: function () {
            $.validator.addMethod("alphNumeric", function (value, element) {
                return this.optional(element) || /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑäëïöüÿÄËÏÖÜ ]+$/gi.test(value)
            }, "Solo letras y números."), $.validator.addMethod("alphabet", function (value, element) {
                return this.optional(element) || /^[a-zA-ZáéíóúÁÉÍÓÚñÑ&äëïöüÿÄËÏÖÜ ]+$/gi.test(value)
            }, "Solo letras."), $.validator.addMethod("comment", function (value, element) {
                return this.optional(element) || /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ&\.,äëïöüÿÄËÏÖÜ\?¿!¡\-_\*;:\+\(\)#%\$@=\"\'\/\n ]+$/gi.test(value)
            }, "Texto inválido."), $.validator.addMethod("dateMask", function (value, element) {
                return this.optional(element) || /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/gi.test(value)
            }, "Fecha inválida."), $.validator.addMethod("nEmail", function (value, element) {
                return this.optional(element) || /^[a-z0-9\_\-]+(\.?[a-z0-9\_\-]+)+@\w+([\.-]?\w+)+$/i.test(value)
            }, "El email ingresado es incorrecto."), $.validator.addMethod("lessThanEqual", function (value, element, param) {
                return this.optional(element) || parseInt(value) >= parseInt($(param).val())
            }, "El año fin debe ser mayor al año de inicio"), $.validator.addMethod("lessThanEqualMonth", function (value, element, param) {
                var flag, valueMonthBegin, valueMonthEnd, valueYearBegin, valueYearEnd;
                return valueMonthBegin = parseInt($(param[0]).val()), valueYearBegin = parseInt($(param[1]).val()), valueYearEnd = parseInt($(param[2]).val()), valueMonthEnd = parseInt(value), flag = !0, valueYearBegin === valueYearEnd && valueMonthBegin > valueMonthEnd && (flag = !1), this.optional(element) || flag
            }, "El mes debe ser mayor o igual al mes de inicio"), $.validator.addMethod("currentMonth", function (value, element, param) {
                var currentMonth, currentYear, flag, valueMonthEnd, valueYearBegin, valueYearEnd;
                return valueYearBegin = parseInt($(param[0]).val()), valueYearEnd = parseInt($(param[1]).val()), currentMonth = parseInt($(param[1]).attr("data-currentmonth")), currentYear = parseInt($(param[1]).attr("data-current-year")), valueMonthEnd = parseInt(value), flag = !0, valueYearBegin === currentYear && valueYearEnd === currentYear && valueMonthEnd > currentMonth && (flag = !1), this.optional(element) || flag
            }, "El mes ingresado es mayor al mes actual"), $.validator.addMethod("autocompleteValidator", function (value, element, param) {
                var flag, idElement;
                return idElement = $(param).val(), flag = !0, "" === $.trim(idElement) && (flag = !1), this.optional(element) || flag
            }, "Seleccione una opción de las sugerencias que le brindamos.")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), beforeCatchDom(), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jquery-validation/dist/jquery.validate.min.js", "js/libs/jquery-validation/src/localization/messages_es.js", "js/libs/tooltipster/js/jquery.tooltipster.min.js"]), yOSON.AppCore.addModule("validate_modal_not_enough_information", function (Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        frmPrincipal: "#frmNotEnoughInformation",
        txtNumero: "#txtNumero",
        txtUbicacion: "#txtUbicacion",
        txtIdUbicacion: "#txtIdUbicacion",
        selPais: "#selPais",
        selDocumento: "#selDocumento",
        selGenero: "#selGenero",
        tokenForm: "#tokenhiden",
        btnButton: "#smbModalNotEnoughInformation",
        btnClose: "#clsModalNotEnoughInformation",
        fieldset: "fieldset",
        listError: "#not_enough_information_form_error",
        xhrFormAjax: null,
        validator: null,
        enabledToEdit: !0,
        disabledToEdit: !1
    }, catchDom = function () {
        dom.frmPrincipal = $(st.frmPrincipal), dom.listError = $(st.listError), dom.txtNumero = $(st.txtNumero, dom.frmPrincipal), dom.txtUbicacion = $(st.txtUbicacion, dom.frmPrincipal), dom.txtIdUbicacion = $(st.txtIdUbicacion, dom.frmPrincipal), dom.selPais = $(st.selPais, dom.frmPrincipal), dom.selGenero = $(st.selGenero, dom.frmPrincipal), dom.fieldset = $(st.fieldset, dom.frmPrincipal), dom.selDocumento = $(st.selDocumento, dom.frmPrincipal), dom.tokenForm = $(st.tokenForm, dom.frmPrincipal), dom.tokenForm = $(st.tokenForm, dom.frmPrincipal), dom.btnButton = $(st.btnButton, dom.frmPrincipal), dom.btnClose = $(st.btnClose, dom.frmPrincipal)
    }, afterCatchDom = function () {
        functions.fnRegisterRules(), functions.fnInitValidateForm()
    }, suscribeEvents = function () {
        dom.frmPrincipal.on("submit", events.eValidateForm), dom.selDocumento.on("change", events.eChangeDocument), dom.btnButton.on("click", events.eSubmitForm)
    }, events = {
        eValidateForm: function (e) {
            e.preventDefault(), dom.frmPrincipal.validate()
        },
        eSubmitForm: function (e) {
            dom.frmPrincipal.submit()
        },
        eChangeDocument: function () {
            var _this;
            _this = $(this), dom.txtNumero.val("").removeClass("error").parent().removeClass("error"), "dni" === _this.val() ? (dom.txtNumero.attr({
                minlength: 8,
                maxlength: 8
            }).rules("add", {
                digits: !0
            }), dom.txtNumero.rules("remove", "alphNumeric")) : (dom.txtNumero.attr({
                minlength: 7,
                maxlength: 12
            }).rules("add", {
                alphNumeric: !0
            }), dom.txtNumero.rules("remove", "digits")), st.validator.resetForm()
        }
    }, functions = {
        fnInitValidateForm: function () {
            dom.frmPrincipal.tooltipster({
                timer: 1500
            }), st.validator = dom.frmPrincipal.validate({
                rules: {
                    txtNumero: {
                        digits: !0
                    },
                    txtUbicacion: {
                        verifySelection: !0
                    }
                },
                submitHandler: function (form) {
                    st.xhrFormAjax = $.ajax({
                        type: "POST",
                        url: dom.frmPrincipal.attr("action"),
                        data: dom.frmPrincipal.serialize(),
                        beforeSend: function () {
                            return functions.fnHideListError(), functions.fnAccessForm(st.disabledToEdit)
                        }
                    }).done(function (result) {
                        result = jQuery.parseJSON(result), "1" === result.status ? "undefined" != typeof result.urlRedirect ? location.href = result.urlRedirect : location.reload() : (dom.tokenForm.val(result.token), functions.fnAccessForm(st.enabledToEdit), functions.fnShowListError(result.msg))
                    }).fail(function () {
                        var error_server;
                        functions.fnAccessForm(st.enabledToEdit), error_server = {
                            msgError: "Hubo un error interno, por favor vuelva a intentarlo"
                        }, functions.fnShowListError(JSON.stringify(error_server))
                    })
                }
            })
        },
        fnCleanForm: function () {
            Sb.trigger("fnAbortAutocompleteXHR"), null !== st.xhrFormAjax && st.xhrFormAjax.abort(), dom.frmPrincipal.find("input").val(""), dom.selPais.val(yOSON.utils.getPeruCode()), dom.selDocumento.val("dni"), dom.selGenero.val("dni"), dom.selDocumento.parent().find(".name_category").html("DNI"), dom.selGenero.parent().find(".name_category").html("Masculino"), dom.selPais.parent().find(".name_category").html("Perú"), dom.fieldset.removeClass("selected"), functions.fnHideListError(), functions.fnAccessForm(st.enabledToEdit)
        },
        fnAccessForm: function (action) {
            action === st.enabledToEdit ? (dom.frmPrincipal.find("input, select, .btn").removeAttr("disabled"), dom.btnButton.removeAttr("loading"), dom.fieldset.removeAttr("disabled"), dom.btnClose.removeAttr("disabled"), Sb.trigger("fnDisableUbicationTextField")) : (dom.frmPrincipal.find("input, select, .btn").attr("disabled", ""), dom.btnButton.attr("loading", ""), dom.btnClose.attr("disabled", ""), dom.fieldset.attr({
                disabled: "",
                loading: ""
            }), dom.fieldset.removeClass("selected"))
        },
        fnShowListError: function (listError) {
            listError = jQuery.parseJSON(listError), $.each(listError, function (index, value) {
                var li;
                li = "<li>" + value + "</li>", dom.listError.append(li)
            }), dom.listError.removeClass("hide")
        },
        fnHideListError: function () {
            dom.listError.addClass("hide"), dom.listError.html("")
        },
        fnRegisterRules: function () {
            jQuery.validator.addMethod("verifySelection", function (value, element) {
                var idUbicacion;
                return idUbicacion = dom.txtIdUbicacion.val(), "" !== $.trim(idUbicacion)
            }, "Seleccione una ubicación")
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents(), Sb.events(["fnCleanFormNotEnoughInformation"], functions.fnCleanForm, this)
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("validate_search_form", function (Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st;
    return dom = {}, st = {
        frmSearch: "#frmSearch",
        frmSearchMenu: "#frmSearchMenu",
        txtSearchMenu: "#txtSearchMenu",
        txtDescription: "#txtDescription"
    }, catchDom = function () {
        dom.frmSearch = $(st.frmSearch), dom.txtDescription = $(st.txtDescription, dom.frmSearch), dom.frmSearchMenu = $(st.frmSearchMenu), dom.txtSearchMenu = $(st.txtSearchMenu, dom.frmSearchMenu)
    }, afterCatchDom = function () {
        functions.addValidatorSearch(), functions.validateFormDesktop(), functions.validateFormMobile()
    }, functions = {
        addValidatorSearch: function () {
            $.validator.addMethod("searchField", function (value, element) {
                return this.optional(element) || /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑäëïöüÿÄËÏÖÜ+#\. ]+$/gi.test(value)
            }, "Campo incorrecto")
        },
        validateFormDesktop: function () {
            dom.frmSearch.validate({
                submitHandler: function (form) {
                    var cadena, value;
                    value = $.trim(dom.txtDescription.val()), value = value.replace(/ /g, "+"), cadena = form.action, "" !== value && (cadena += "/q/" + value), window.location = cadena
                }
            })
        },
        validateFormMobile: function () {
            dom.frmSearchMenu.validate({
                errorElement: "div",
                errorPlacement: function (error, element) {
                    error.appendTo(element.parent())
                },
                submitHandler: function (form) {
                    var cadena, value;
                    value = $.trim(dom.txtSearchMenu.val()), value = value.replace(/ /g, "+"), cadena = form.action, "" !== value && (cadena += "/q/" + value), window.location = cadena
                }
            })
        }
    }, initialize = function (oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, []);