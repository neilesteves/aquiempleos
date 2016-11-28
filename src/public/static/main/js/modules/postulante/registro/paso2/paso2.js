yOSON.AppCore.addModule("selection_modal", function(Sb) {
    var afterCatchDom, catchDom, ctxt, dom, events, fn, initialize, st, suscribeEvents;
    return dom = {}, ctxt = {
        maxSelect: 3,
        textDefault: ""
    }, st = {
        check_select: ".icon_check2",
        selectLevel: ".select_level",
        jobArea: ".job_area",
        JobAreaBox: "#JobAreaBox",
        jobs_area_wrapper: ".jobs_area_wrapper",
        nivelText: ".nivel_text",
        hidNivelSelected: ".hid_nivel_selected",
        hidAreaSelected: ".hid_area_selected",
        relacionesPublicas: "#item_39",
        btnFinish: "#btnFinish"
    }, catchDom = function() {
        dom.jobsAreaWrapper = $(st.jobs_area_wrapper), 
        dom.jobArea = $(st.jobArea, 
        dom.jobsAreaWrapper), 
        dom.JobAreaBox = $(st.JobAreaBox), 
        dom.selectLevel = $(st.selectLevel, dom.JobAreaBox), 
        dom.check_select = $(st.check_select), 
        dom.relacionesPublicas = $(st.relacionesPublicas), 
        dom.btnFinish = $(st.btnFinish)
    }, afterCatchDom = function() {
        ctxt.textDefault = dom.relacionesPublicas.find(".information_area h4").html(), dom.jobArea.tooltipster({
            trigger: 'custom',
            content: "Ha alcanzado un número máximo de áreas seleccionadas",
            timer: 1500
        })
    }, suscribeEvents = function() {
        dom.jobArea.on("click", events.eShowBoxTags), $(document).on("click", st.btnFinish, events.eCloseBoxTags), $(document).on("click", st.selectLevel + " span", events.eSelectLevel), dom.check_select.on("click", events.eCheckIs), $(window).on("resize", events.onResize)
    }, events = {
        eCloseBoxTags: function(e) {
            var selfParentLevel;
            $(window).width() > yOSON.utils.getBreakPointMobile() ? $.fancybox.close() : (selfParentLevel = $(this).parent(), fn.removeMobileBoxTag(selfParentLevel), fn.removeClassIsOpen(selfParentLevel.prev()))
        },
        eShowBoxTags: function(e) {

            var isDektop, isLimitExceeded, self;
            return self = $(this), isDektop = $(window).width() > yOSON.utils.getBreakPointMobile(), (isLimitExceeded = fn.verifyMaxSelectedItems(self, dom.jobsAreaWrapper, ctxt.maxSelect)) ? (self.tooltipster("show"), !1) : void(isDektop ? events.eShowDesktopBoxTags(self) : events.eShowMobileBoxTags(self))
        },
        eShowDesktopBoxTags: function(self) { 

            dom.JobAreaBox = $(st.JobAreaBox+self.attr("data-area-id"));
            dom.selectLevel = $(st.selectLevel, dom.JobAreaBox);

            $.fancybox.open(st.JobAreaBox+self.attr("data-area-id"), {
                afterLoad: function() {
                    fn.updateDataRel(self, dom.selectLevel), fn.removeClassIsSelected(dom.selectLevel), fn.drawItemsSelected(self, st.hidNivelSelected, dom.selectLevel)
                }
            })
        },
        eShowMobileBoxTags: function(self) { 
    
            dom.JobAreaBox = $(st.JobAreaBox+self.attr("data-area-id"));
            dom.selectLevel = $(st.selectLevel, dom.JobAreaBox);

            var currentLevel, html, isAnimated, removedIsOpenClass, selfParentLevel;
            return selfParentLevel = self.next(st.selectLevel), (isAnimated = selfParentLevel && selfParentLevel.is(":animated")) ? !1 : (removedIsOpenClass = fn.checkMobileBoxTags(self), void(removedIsOpenClass ? fn.removeMobileBoxTag(selfParentLevel) : (html = fn.hideJobAreaBox(dom.JobAreaBox, st.selectLevel), fn.removeClassIsOpen(self.siblings(dom.jobArea)), fn.removeMobileBoxTag($(st.selectLevel, dom.jobsAreaWrapper)), fn.insertHTML(self, html), currentLevel = self.next(st.selectLevel), fn.updateDataRel(self, currentLevel), fn.addMobileBoxTag(currentLevel), fn.removeClassIsSelected(currentLevel), fn.drawItemsSelected(self, st.hidNivelSelected, currentLevel))))
        },
        eSelectLevel: function(e) {
    
            var levelHTML, levelValue, noLevelSelected, selfLevel, selfParentLevel, selfRelJobArea;
            selfLevel = $(this), 
            selfParentLevel = selfLevel.parent(), 
            selfRelJobArea = fn.getRelJobArea(selfParentLevel, st.jobArea, dom.jobsAreaWrapper), 
    
            fn.setSelectLevel(selfLevel, selfParentLevel, ctxt.maxSelect), 
            levelValue = fn.getDataLevel("value", selfParentLevel), 
            levelHTML = fn.getDataLevel("text", selfParentLevel), 
            fn.setNameToBox(selfRelJobArea, selfParentLevel, levelHTML), 
            fn.cleanValuesHiddens(selfRelJobArea), 
            noLevelSelected = 0 === $(".is_selected", selfParentLevel).length, 
            fn.toogleLevelClassActive(selfRelJobArea, noLevelSelected), 
            noLevelSelected ? fn.deleteValuesHiddens(st.hidNivelSelected, st.hidAreaSelected, selfRelJobArea) : fn.setValuesHiddens(selfRelJobArea, levelValue)
        },
        eCheckIs: function(e) {
            var _parent, removedActiveClass, self;
            self = $(this), _parent = self.parent(), removedActiveClass = fn.removeTheClass("active", _parent), removedActiveClass && ($(st.nivelText, _parent).empty(), fn.cleanValuesHiddens(_parent), fn.removeClassIsSelected(_parent.next()), e.stopPropagation())
        },
        onResize: function() {
            var selectLevel, windowWidth;
            $(this).width() > yOSON.utils.getBreakPointMobile() ? (selectLevel = $(st.selectLevel, dom.jobsAreaWrapper), 0 !== selectLevel.length && (selectLevel.remove(), fn.removeClassIsOpen(dom.jobArea))) : $.fancybox.close(), windowWidth = $(window).width(), 950 > windowWidth && windowWidth > yOSON.utils.getBreakPointMobile() ? dom.relacionesPublicas.find(".information_area h4").html("Relaciones institucionales") : dom.relacionesPublicas.find(".information_area h4").html(ctxt.textDefault)
        }
    }, fn = {
        removeTheClass: function(indicator, element) {
            var hasClassIndicator;
            return hasClassIndicator = element.hasClass(indicator), hasClassIndicator && element.removeClass(indicator), hasClassIndicator
        },
        checkMobileBoxTags: function(self) {
    
            var isOpen;
            return isOpen = self.hasClass("is_open"), self.toggleClass("is_open", !isOpen), isOpen
        },
        toogleLevelClassActive: function(selfRelJobArea, noLevelSelected) {
            selfRelJobArea.toggleClass("active", !noLevelSelected)
        },
        verifyMaxSelectedItems: function(self, jobs_area_wrapper, maxSelect) {
            var result;
            return result = !self.hasClass("active") && jobs_area_wrapper.find(".active").length >= maxSelect
        },
        removeClassIsOpen: function(element) {
            element.removeClass("is_open")
        },
        hideJobAreaBox: function(jobAreaBox, selectLevel) {
            var html;
            return html = jobAreaBox.find(selectLevel).clone().hide()
        },
        insertHTML: function(self, html) {
            $(html).insertAfter(self)
        },
        getRelJobArea: function(selfParentLevel, jobArea, jobsAreaWrapper) {    
        
            var relJobArea;
            return relJobArea = selfParentLevel.attr("data-rel"), $(jobArea + ("[data-area=" + relJobArea + "]"), jobsAreaWrapper)
        },
        setSelectLevel: function(selfLevel, selfParentLevel, maxSelect) {
            selfLevel.hasClass("is_selected") ? selfLevel.removeClass("is_selected") : $(".is_selected", selfParentLevel).length < maxSelect && selfLevel.addClass("is_selected")
        },
        setNameToBox: function(selfRelJobArea, selfParentLevel, levelHTML) {
            $(st.nivelText, selfRelJobArea).empty().html("<p>" + levelHTML + "</p>")
        },
        getDataLevel: function(type, selfParentLevel) {
            var levels, tuCollections;
            return levels = $(".is_selected", selfParentLevel), tuCollections = [], $.each(levels, function(i, e) {
                "text" === type ? tuCollections.push($(e).text()) : tuCollections.push($(e).attr("data-value"))
            }), tuCollections.join(" , ")
        },
        removeClassIsSelected: function(container) {
            $("span", container).removeClass("is_selected")
        },
        drawItemsSelected: function(self, hidNivelSelected, container) {
            var arr, dataValues;
            dataValues = $(hidNivelSelected, self).val(), "undefined" != typeof dataValues && "" !== dataValues && (arr = dataValues.match(/([0-9a-z\-\_]+)/g), $.each(arr, function(i, elem) {
                $("span[data-value=" + elem + "]", container).addClass("is_selected")
            }))
        },
        updateDataRel: function(self, selfParentLevel) {

            selfParentLevel.attr("data-rel", self.attr("data-area"))
        },
        addMobileBoxTag: function(currentLevel) {
            currentLevel.slideDown()
        },
        removeMobileBoxTag: function(self) {
            self.slideUp(function() {
                $(this).remove()
            })
        },
        deleteValuesHiddens: function(hidNivelSelected, hidAreaSelected, selfRelJobArea) {
            $(hidNivelSelected, selfRelJobArea).remove(), $(hidAreaSelected, selfRelJobArea).remove()
        },
        setValuesHiddens: function(selfRelJobArea, levelValue) {

            $("<input/>", {
                "class": "hid_nivel_selected",
                type: "hidden",
                value: levelValue,
                name: "niveles[]"
            }).appendTo(selfRelJobArea), $("<input/>", {
                "class": "hid_area_selected",
                type: "hidden",
                value: selfRelJobArea.attr("data-area"),
                name: "areas[]"
            }).appendTo(selfRelJobArea)
        },
        cleanValuesHiddens: function(_parent) {
            $(st.hidNivelSelected, _parent).remove(), $(st.hidAreaSelected, _parent).remove()
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize,
        tests: fn
    }
}, ["js/libs/tooltipster/js/jquery.tooltipster.min.js"]);