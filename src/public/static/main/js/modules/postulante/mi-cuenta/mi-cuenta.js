yOSON.AppCore.addModule("modal_welcome_information", function(Sb) {
    var afterCatchDom, catchDom, dom, initialize, st;
    return dom = {}, st = {
        modal: "#welcomeInformationModal",
        iconCross: ".icon_cross"
    }, catchDom = function() {
        dom.modal = $(st.modal), dom.iconCross = $(st.iconCross, dom.modal)
    }, afterCatchDom = function() {
        setTimeout(function() {
            0 === dom.modal.length || device.mobile() || $.fancybox.open(st.modal, {
                padding: 0
            })
        }, 1e3), dom.iconCross.on("click", function() {
            $(".page_default").removeClass("hide"), dom.modal.addClass("hide")
        })
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, {
        init: initialize
    }
}, ["js/libs/fancybox/source/jquery.fancybox.js"]), yOSON.AppCore.addModule("progressbar", function(Sb) {
    var afterCathDom, catchDom, dom, fn, fnMiddleLayer, fnPrimaryLayer, initialize, st;
    return dom = {}, st = {
        divProgressBar: "#progressProfile",
        colorText: "#00c617"
    }, catchDom = function() {
        dom.divProgressBar = $(st.divProgressBar)
    }, afterCathDom = function() {
        fn.elementExists(dom.divProgressBar) && fnPrimaryLayer.getDataPercent()
    }, fnPrimaryLayer = {
        getDataPercent: function() {
            var decimalPercent, numberPercent;
            numberPercent = parseInt(dom.divProgressBar.data("percent")), fn.validateData(numberPercent) && (decimalPercent = fn.convertToFloat(numberPercent), fnMiddleLayer.runPlugin(decimalPercent))
        },
        updateProgressBar: function(circle, numberPercent) {
            var decimalPercent;
            return isInternetExplorer() && "8.0" === browser.version ? !1 : void(fn.validateData(numberPercent) && (decimalPercent = fn.convertToFloat(numberPercent), circle.animate(decimalPercent)))
        }
    }, fnMiddleLayer = {
        runPlugin: function(decimalPercent) {
            isInternetExplorer() && "8.0" === browser.version ? fnMiddleLayer.createProgressBar(decimalPercent) : fnMiddleLayer.createProgressCircleBar(decimalPercent)
        },
        createProgressBar: function(decimalPercent) {
            var $bar, $text, i, timer;
            i = 0, dom.divProgressBar.html("<p>0%</p><div><span></span></div>"), $text = $("p", dom.divProgressBar), $bar = $("span", dom.divProgressBar), timer = setInterval(function() {
                decimalPercent >= i ? ($text.text(i + "%"), $bar.css("width", i + "%"), i++) : clearInterval(timer)
            }, 10)
        },
        createProgressCircleBar: function(decimalPercent) {
            var circle;
            window.circleProgressBar = {}, circle = new ProgressBar.Circle(st.divProgressBar, {
                color: st.colorText,
                strokeWidth: 6,
                trailColor: "#ffffff",
                trailWidth: 6,
                duration: 3e3,
                easing: "easeInOut",
                text: {
                    value: "0"
                },
                step: function(state, bar) {
                    var numberStep;
                    return numberStep = Math.abs((100 * bar.value()).toFixed(0)), bar.setText(numberStep + "%")
                }
            }), circle.animate(decimalPercent), window.circleProgressBar[st.divProgressBar] = circle
        }
    }, fn = {
        elementExists: function($element) {
            return 0 !== $element.length ? !0 : !1
        },
        validateData: function(number) {
            var flag;
            return flag = !1, $.isNumeric(number) && number >= 1 && 100 >= number && (flag = !0), flag
        },
        convertToFloat: function(number) {
            return number / 100
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCathDom(), Sb.events(["updateProgressBar"], fnPrimaryLayer.updateProgressBar, this)
    }, {
        init: initialize,
        tests: fn
    }
}, ["js/libs/progressbar.js/dist/progressbar.js"]), yOSON.AppCore.addModule("skill_delete", function(Sb) {
    var catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        btnDelete: ".btn_delete",
        urlAjaxDelete: "",
        subMenuBox: ".skill_buttons",
        titleContentEdit: ".title_content_edit",
        btnAddSkill: "#btnAddSkill",
        sliderProfile: "#sliderProfile",
        progressCircle: "#progressProfile",
        self: null,
        isPercentActive: !0
    }, catchDom = function() {
        dom.btnAddSkill = $(st.btnAddSkill), dom.btnDelete = $(st.btnDelete), dom.subMenuBox = $(st.subMenuBox), dom.titleContentEdit = $(st.titleContentEdit), dom.sliderProfile = $(st.sliderProfile)
    }, suscribeEvents = function() {
        $(document).on("click", st.btnDelete, events.deleteSkill)
    }, events = {
        deleteSkill: function() {
            st.self = $(this), functions.disabledButtons(st.self), yOSON.utils.getToken(functions.ajaxDeleteSkill, functions.showError)
        }
    }, functions = {
        ajaxDeleteSkill: function(dataToken) {
            $.ajax({
                type: "POST",
                url: st.urlAjaxDelete,
                data: {
                    id: st.self.data("id"),
                    token: dataToken
                }
            }).done(function(response) {
                response = $.parseJSON(response), 1 === response.status ? (yOSON.utils.showMessage(dom.subMenuBox, "success", response.message), functions.deleteFormVisible(st.self), functions.showPercents(response)) : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
            }).fail(functions.showError).always(function() {
                functions.enabledButtons()
            })
        },
        showError: function() {
            functions.enabledButtons(), yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        },
        deleteFormVisible: function(self) {
            self.parents("li").slideUp(function() {
                $(this).remove(), functions.existsAnySkill()
            })
        },
        disabledButtons: function(self) {
            dom.btnAddSkill.attr("disabled", ""), $(".btn", dom.titleContentEdit).attr("disabled", ""), self.attr("loading", "")
        },
        enabledButtons: function() {
            dom.btnAddSkill.removeAttr("disabled"), $(".btn", dom.titleContentEdit).removeAttr("disabled"), $(".btn", dom.titleContentEdit).removeAttr("loading")
        },
        existsAnySkill: function() {
            0 === $("li", dom.titleContentEdit).length && (dom.titleContentEdit.addClass("hide"), dom.titleContentEdit.prev().addClass("hide"), dom.btnAddSkill.trigger("click"))
        },
        showPercents: function(response) {
            var liSkill;
            return st.isPercentActive ? ("undefined" != typeof response.percent && $.isNumeric(response.percent) ? Sb.trigger("updateProgressBar", window.circleProgressBar[st.progressCircle], response.percent) : log("el porcentaje no es numérico"), liSkill = dom.sliderProfile.find("li[data-rel='" + response.iscompleted[0] + "']"), void(1 === response.iscompleted[1] ? liSkill.addClass("is_passed") : liSkill.removeClass("is_passed"))) : !1
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, []), yOSON.AppCore.addModule("skill_edit", function(Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        frmSkill: "",
        tplForm: "",
        formValues: [],
        btnEdit: ".btn_edit",
        btnAddSkill: "#btnAddSkill",
        subMenuBox: ".skill_buttons",
        dataAjaxIdSkill: null,
        urlAjaxEdit: "",
        titleSkills: ".title_skills",
        titleContentEdit: ".title_content_edit",
        htmltplForm: null,
        context: null,
        xhrAjax: null,
        autocomplete: null
    }, catchDom = function() {
        dom.btnEdit = $(st.btnEdit), dom.btnAddSkill = $(st.btnAddSkill), dom.subMenuBox = $(st.subMenuBox), dom.tplForm = $(st.tplForm), dom.titleContentEdit = $(st.titleContentEdit)
    }, afterCatchDom = function() {
        st.htmltplForm = dom.tplForm.html()
    }, suscribeEvents = function() {
        $(document).on("click", st.btnEdit, events.getForm)
    }, events = {
        getForm: function() {
            var self;
            self = $(this), st.context = self.parents("li"), st.dataAjaxIdSkill = self.data("id"), functions.deleteFormVisible(), functions.disabledButtons(self), yOSON.utils.getToken(functions.getDataAjax, functions.showError)
        }
    }, functions = {
        getDataAjax: function(dataToken) {
            yOSON.token = dataToken, st.xhrAjax = $.ajax({
                type: "POST",
                url: st.urlAjaxEdit,
                data: {
                    id: st.dataAjaxIdSkill,
                    csrfhash: yOSON.token
                }
            }).done(function(response) {
                response = $.parseJSON(response), 
                functions.enabledButtons(), 
                1 === response.status && ($(st.titleSkills, st.context).slideUp(), 
                    functions.showTemplate(response.skill), 
                    functions.initScrollIE8(), 
                    functions.initPrettySelect(), 
                    Sb.trigger("count_words"), Sb.trigger("validateSkill"))
            }).fail(functions.showError)
        },
        showTemplate: function(skill) {
            
            $(st.context).append(st.htmltplForm), 
            $.each(st.formValues, function(i, domName) {
                var myElement;
                myElement = $('[name="' + domName + '"]', st.frmSkill),

                $.isPlainObject(skill[domName]) ? void 0 !== skill[domName].disabled ? (myElement.parents("fieldset").attr("disabled", ""), myElement.attr("disabled", "")) : functions.drawSelect(skill, domName) : myElement.is(":checkbox") && "1" === skill[domName] ? myElement.attr("checked", !0) : myElement.is(":radio") ? myElement.filter('[value="' + skill[domName] + '"]').trigger("click").parents("fieldset").removeClass("selected") : myElement.val(skill[domName])
            }), 

            $(st.frmSkill).slideDown(),
            
            $("#selLevelArea", st.frmSkill).change(),
            $( document ).ajaxComplete(function() {                
                $("#frmUserExperience").find("#selLevelJob").val(skill.selLevelJob)
            })
        },
        drawSelect: function(skill, domName) {
            var optionsHTML, optionsObject, select;
            optionsObject = skill[domName], optionsHTML = "", select = domName.replace("Combo", ""), $.each(optionsObject, function(index, value) {
                return optionsHTML += "<option value='" + index + "'>" + value + "</option>"
            }), $("#" + select).html(optionsHTML), $("#" + select).val(skill[select])
        },
        initPrettySelect: function() {
            //$("select", st.frmSkill).prettySelect()
        },
        initAutocomplete: function() {
            return null === st.autocomplete ? !1 : void $.each(st.autocomplete, function(index, value) {
                st.autocompletexhr = $(value.txtValue).custom_autocomplete({
                    hiddenValue: value.txtIdValue,
                    urlAutocomplete: value.urlAutocomplete,
                    getTokenAjax: yOSON.utils.getToken,
                    fnAfterUpdateText: function() {
                        return functions.fnRemoveError(value.txtValue)
                    }
                })
            })
        },
        initScrollIE8: function() {
            var scrollTopNow;
            !isInternetExplorer() || "8.0" !== browser.version && "9.0" !== browser.version || (scrollTopNow = $(window).scrollTop(), Sb.trigger("placeholderReInitialize"), $(window).scrollTop(scrollTopNow))
        },
        fnRemoveError: function(element) {
            element = $(element), element.removeClass("error"), element.parent().removeClass("error")
        },
        showTitlePrevious: function() {
            $(st.frmSkill).prev().slideDown()
        },
        showError: function(jqXHR, textStatus, errorThrown) {
            functions.enabledButtons(), "undefined" != typeof textStatus && "abort" !== textStatus && yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente")
        },
        deleteFormVisible: function() {
            functions.showTitlePrevious(), $(st.frmSkill).slideUp(function() {
                $(this).remove()
            })
        },
        disabledButtons: function(self) {
            dom.btnAddSkill.attr("disabled", ""), $(".btn", dom.titleContentEdit).attr("disabled", ""), self.attr("loading", "")
        },
        enabledButtons: function() {
            dom.btnAddSkill.removeAttr("disabled"), $(".btn", dom.titleContentEdit).removeAttr("disabled"), $(st.btnEdit, st.context).removeAttr("loading")
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/pretty-select.js", "js/libs/jquery.autocomplete.custom.js"]), yOSON.AppCore.addModule("skill_save", function(Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, publicInitialize, st, suscribeEvents;
    return dom = {}, st = {
        frmSkill: "",
        btnAddSkill: "#btnAddSkill",
        hidSkill: "",
        title: {},
        rulesValidate: {},
        fieldset: "fieldset",
        btnSave: ".btn_save",
        btnCancel: ".btn_cancel",
        subMenuBox: ".skill_buttons",
        hidToken: "#hidToken",
        titleContentEdit: ".title_content_edit",
        blockText: ".block_text",
        progressCircle: "#progressProfile",
        sliderProfile: "#sliderProfile",
        skillBody: ".skill_body",
        tplForm: "",
        HTMLtplForm: null,
        tplSkillBox: "#tplSkillBox",
        HTMLtplSkillBox: null,
        tplSkillTitle: "#tplSkillTitle",
        HTMLtplSkillTitle: null,
        xhrAjax: null,
        autocomplete: null,
        autocompleteXHR: {},
        messageBox: ".message_box",
        isPercentActive: !0
    }, catchDom = function() {
        dom.frmSkill = $(st.frmSkill), dom.btnAddSkill = $(st.btnAddSkill), dom.fieldset = $(st.fieldset), dom.btnSave = $(st.btnSave, dom.frmSkill), dom.btnCancel = $(st.btnCancel, dom.frmSkill), dom.sliderProfile = $(st.sliderProfile), dom.skillBody = $(st.skillBody), dom.subMenuBox = $(st.subMenuBox), dom.hidToken = $(st.hidToken), dom.titleContentEdit = $(st.titleContentEdit), dom.tplForm = $(st.tplForm), dom.tplSkillBox = $(st.tplSkillBox), dom.tplSkillTitle = $(st.tplSkillTitle)
    }, afterCatchDom = function() {
        functions.addValidations(), functions.validateForm(), functions.initAutocomplete(), st.HTMLtplForm = dom.tplForm.html(), st.HTMLtplSkillBox = functions.template(dom.tplSkillBox.html()), st.HTMLtplSkillTitle = _.template(dom.tplSkillTitle.html())
    }, suscribeEvents = function() {
        dom.btnAddSkill.on("click", events.showFormSkill), dom.skillBody.on("click", st.btnCancel, events.eCancelForm)
    }, events = {
        showFormSkill: function() {
            var self;
            self = $(this), functions.fnShowTitleBox(), functions.fnCancelForm(), self.attr("disabled", !0), $(st.HTMLtplForm).insertAfter(self.parent()), functions.initScrollIE8(), $(st.frmSkill).slideDown(), functions.initPrettySelect(), Sb.trigger("count_words"), Sb.trigger("validateSkill"), $(st.messageBox).hide()
        },
        eCancelForm: function() {
            functions.fnShowTitleBox(), functions.fnCancelForm(), dom.btnAddSkill.attr("disabled", !1), functions.fnFinishConsultAutocomplete()
        }
    }, functions = {
        initPrettySelect: function() {
            //$("select", st.frmSkill).prettySelect()
        },
        initAutocomplete: function() {
            return null === st.autocomplete ? !1 : void $.each(st.autocomplete, function(index, value) {
                st.autocompleteXHR[value.txtValue] = $(value.txtValue).custom_autocomplete({
                    hiddenValue: value.txtIdValue,
                    urlAutocomplete: value.urlAutocomplete,
                    getTokenAjax: yOSON.utils.getToken,
                    fnAfterUpdateText: function() {
                        return functions.fnRemoveError(value.txtValue)
                    }
                })
            })
        },
        initScrollIE8: function() {
            var scrollTopNow;
            !isInternetExplorer() || "8.0" !== browser.version && "9.0" !== browser.version || (scrollTopNow = $(window).scrollTop(), Sb.trigger("placeholderReInitialize"), $(window).scrollTop(scrollTopNow))
        },
        addValidations: function() {
            $.validator.addMethod("regx", function(value, element, regexpr) {
                return regexpr.test(value)
            }, "Favor de ingresar un valor correcto.")
        },
        fnRemoveError: function(element) {
            element = $(element), element.removeClass("error"), element.parent().removeClass("error")
        },
        validateForm: function() {
            $(st.fieldset).tooltipster(), dom.frmSkill.validate({
                rules: st.rulesValidate,
                errorPlacement: yOSON.utils.setErrorPlacement,
                success: yOSON.utils.setSuccessForm,
                submitHandler: function(form) {
                    functions.disabledButtons(), functions.saveSkill()
                }
            }), dom.btnSave.removeAttr("disabled")
        },
        fnFinishConsultAutocomplete: function() {
            $.each(st.autocompleteXHR, function(index, value) {
                value.fnAbortXHR()
            })
        },
        saveSkill: function(dataToken) {
            st.xhrAjax = $.ajax({
                type: "POST",
                url: dom.frmSkill.attr("action"),
                data: dom.frmSkill.serialize() + "&hidToken=" + functions.getHidToken()
            }).done(function(response) {
                response = $.parseJSON(response), functions.enabledButtons(), functions.setHidToken(response), 1 === response.status ? (functions.existsAnySkill(), functions.validateIfEditOrNewSkill(response.skill), yOSON.utils.showMessage(dom.subMenuBox, "success", response.message), functions.showPercents(response)) : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
            }).fail(functions.showError)
        },
        validateIfEditOrNewSkill: function(skillJson) {
            functions.itsNewSkillData() ? (functions.fnCancelForm(), dom.btnAddSkill.attr("disabled", !1), functions.addNewSkill(skillJson)) : (functions.setTitles(skillJson), functions.fnShowTitleBox(), functions.fnCancelForm())
        },
        itsNewSkillData: function() {
            var flag;
            return flag = !1, "0" === $("#" + st.hidSkill).val() && (flag = !0), flag
        },
        addNewSkill: function(skillJson) {
            var htmlCompiled;
            htmlCompiled = st.HTMLtplSkillBox(skillJson), dom.titleContentEdit.prepend(htmlCompiled)
        },
        setTitles: function(skillJson) {
            var htmlCompiled;
            htmlCompiled = st.HTMLtplSkillTitle(skillJson), dom.frmSkill.prev().html(htmlCompiled)
        },
        fnShowTitleBox: function() {
            $(st.frmSkill).prev().slideDown()
        },
        fnCancelForm: function() {
            st.xhrGlobalGetToken && st.xhrGlobalGetToken.abort(), st.xhrAjax && st.xhrAjax.abort(), $(st.frmSkill).slideUp(function() {
                $(this).remove()
            })
        },
        showPercents: function(response) {
            var liSkill;
            return st.isPercentActive ? ("undefined" != typeof response.percent && $.isNumeric(response.percent) ? Sb.trigger("updateProgressBar", window.circleProgressBar[st.progressCircle], response.percent) : log("el porcentaje no es numérico"), liSkill = dom.sliderProfile.find("li[data-rel='" + response.iscompleted[0] + "']"), void(1 === response.iscompleted[1] ? liSkill.addClass("is_passed") : liSkill.removeClass("is_passed"))) : !1
        },
        showError: function(jqXHR, textStatus, errorThrown) {
            "undefined" != typeof textStatus && "abort" !== textStatus && (functions.enabledButtons(), yOSON.utils.showMessage(dom.subMenuBox, "error", "Hubo un error, intente nuevamente"))
        },
        getHidToken: function() {
            return dom.hidToken.val()
        },
        setHidToken: function(response) {
            dom.hidToken.val(response.token)
        },
        template: function(str, data) {
            return _.template(str.replace(/<%\s*include\s*(.*?)\s*%>/g, function(match, templateId) {
                var el;
                return el = document.getElementById(templateId), el ? el.innerHTML : ""
            }), data)
        },
        disabledButtons: function(self) {
            dom.btnAddSkill.attr("disabled", ""), $(".btn", dom.titleContentEdit).attr("disabled", ""), dom.btnSave.attr({
                disabled: "",
                loading: ""
            })
        },
        enabledButtons: function() {
            dom.btnAddSkill.removeAttr("disabled"), $(".btn", dom.titleContentEdit).removeAttr("disabled"), $(st.btnSave, st.context).removeAttr("disabled loading")
        },
        existsAnySkill: function() {
            0 === $("li", dom.titleContentEdit).length && (dom.titleContentEdit.removeClass("hide"), dom.titleContentEdit.prev().removeClass("hide"))
        }
    }, publicInitialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom()
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents(), Sb.events(["validateSkill"], publicInitialize, this)
    }, {
        init: initialize
    }
}, ["js/libs/underscore/underscore-min.js", "js/libs/jquery.autocomplete.custom.js"]), yOSON.AppCore.addModule("slider_data_profile", function(Sb) {
    var afterCatchDom, catchDom, dom, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        wrapper: "#sliderProfile",
        slider: ".slider",
        flagSlick: !1,
        myScroll: null
    }, catchDom = function() {
        dom.wrapper = $(st.wrapper), dom.slider = $(st.slider, dom.wrapper)
    }, afterCatchDom = function() {
        return !isInternetExplorer() || "8.0" !== browser.version && "9.0" !== browser.version ? void(dom.wrapper.length > 0 && functions.setScroll()) : !1
    }, suscribeEvents = function() {}, functions = {
        setScroll: function(e) {
            st.myScroll = new IScroll(st.wrapper, {
                eventPassthrough: !0,
                scrollX: !0,
                scrollY: !1,
                preventDefault: !1
            })
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/iscrolltest/build/iscroll.js"]);