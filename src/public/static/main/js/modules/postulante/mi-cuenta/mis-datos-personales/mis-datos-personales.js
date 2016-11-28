yOSON.AppCore.addModule("save_upload", function(Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        subMenuBox: ".sub_menu_box",
        image_profile_cont: ".image_profile_cont",
        btn_delete: ".btn_delete_photo",
        image_profile_wrapper: ".image_profile_wrapper",
        image_profile: ".image_profile",
        frmUserRegistration: "frmUserRegistration",
        filPhoto: "#fileBrowse",
        regex: /\.(jpg|jpeg|png)$/i,
        maxFileSize: 4194304,
        urlUpload: "/registro/cargafoto/modulo/postulante",
        actionFormDefault: null,
        flag_delete: "#flagDelete",
        urlDelete: "/mi-cuenta/eliminar-foto",
        not_image: ".not_image",
        name: null,
        btn_save: ".btn_save",
        corrupted: "#corrupted"
    }, catchDom = function() {
        dom.btn_save = $(st.btn_save), dom.corrupted = $(st.corrupted), dom.flag_delete = $(st.flag_delete, "#" + st.frmUserRegistration), dom.subMenuBox = $(st.subMenuBox), dom.image_profile_cont = $(st.image_profile_cont), dom.image_profile_wrapper = $(st.image_profile_wrapper), dom.btn_delete = $(st.btn_delete, st.image_profile_wrapper), dom.image_profile = $(st.image_profile, st.image_profile_wrapper), dom.frmUserRegistration = $("#" + st.frmUserRegistration), dom.filPhoto = $(st.filPhoto), dom.not_image = $(st.not_image)
    }, afterCatchDom = function() {
        st.actionFormDefault = dom.frmUserRegistration.attr("action"), dom.filPhoto.customFile({
            classesButton: "btn btn_upload_photo"
        })
    }, suscribeEvents = function() {
        dom.filPhoto.on("change", events.eUploadImage), dom.image_profile_wrapper.on("click", st.btn_delete, events.eDeleteImage)
    }, events = {
        eDeleteImage: function(event) {
            dom.filPhoto.val(""), dom.corrupted.val("null"), dom.flag_delete.val("1"), $(st.not_image).remove(), dom.image_profile.attr("src", yOSON.statHost + "../images/profile-default.jpg")
        },
        eUploadImage: function(e) {
            var files, self, size;
            if (self = $(e), dom.not_image.remove(), window.File && window.FileReader && window.FileList && window.Blob) files = e.originalEvent.target.files, size = files[0].size, functions.fnEvaluateImage(size);
            else {
                if (!st.regex.test(dom.filPhoto.val())) return functions.setMessage("No es una imagen."), !1;
                functions.fnUploadFile()
            }
        }
    }, functions = {
        fnEvaluateImage: function(size) {
            return st.regex.test(dom.filPhoto.val()) ? size > st.maxFileSize ? (functions.setMessage("Imagen muy pesada."), dom.corrupted.val("null"), !1) : void functions.fnUploadFile() : (functions.setMessage("No es una imagen."), !1)
        },
        fnUploadFile: function() {
            var options;
            dom.frmUserRegistration.attr("action", st.urlUpload), options = {
                frm: st.frmUserRegistration,
                onComplete: function(json) {
                    var data;
                    data = $.parseJSON(json), dom.frmUserRegistration.attr({
                        action: "",
                        target: ""
                    }), $(".loader").remove(), dom.btn_save.removeAttr("disabled"), dom.flag_delete.val("null"), 1 === data.status ? (dom.corrupted.val("null"), dom.image_profile.attr("src", data.url)) : ("Corrupted" === data.name && dom.corrupted.val("1"), dom.image_profile.attr("src", yOSON.statHost + "../images/profile-default.jpg"), functions.setMessage(data.msg)), dom.frmUserRegistration.attr("action", st.actionFormDefault), dom.image_profile.show()
                }
            }, dom.image_profile_cont.append("<div class='loader'></div>"), dom.btn_save.attr({
                disabled: "disabled"
            }), dom.image_profile.hide(), dom.frmUserRegistration.attr("action", st.urlUpload), $.fn.iframeUp("submit", options), $(st.not_image).remove()
        },
        setMessage: function(message) {
            dom.image_profile_cont.append("<p class='not_image'>" + message + "</p>")
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jqIframeUp.js", "js/libs/jqCustomfile.js"]), yOSON.AppCore.addModule("validate_my_personal_data_form", function(Sb) {
    var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
    return dom = {}, st = {
        frm: "frmUserRegistration",
        btnForm: ".btn_save",
        txtBirthDay: "#fBirthDate",
        message: ".message",
        subMenuBox: ".skill_buttons",
        token: "#hidToken",
        tipoDoc: "#fTipoDoc",
        nameProfile: ".name_profile",
        sliderProfile: "#sliderProfile",
        validator: null,
        numDoc: "#fNumDoc",
        url: "/mi-cuenta/valid-document",
        progressCircle: "#progressProfile"
    }, catchDom = function() {
        dom.numDoc = $(st.numDoc), dom.tipoDoc = $(st.tipoDoc), dom.token = $(st.token), dom.frmUserRegistration = $("#" + st.frm), dom.btnForm = $(st.btnForm, dom.frmUserRegistration), dom.message = $(st.message, dom.frmUserRegistration), dom.txtBirthDay = $(st.txtBirthDay, dom.frmUserRegistration), dom.sliderProfile = $(st.sliderProfile), dom.subMenuBox = $(st.subMenuBox)
    }, afterCatchDom = function() {
        dom.txtBirthDay.inputmask("date", {
            yearrange: {
                minyear: dom.txtBirthDay.attr("minyear"),
                maxyear: dom.txtBirthDay.attr("maxyear")
            }
        }), functions.validateForm(), functions.changeAttrForType(dom.tipoDoc), st.validator.resetForm()
    }, suscribeEvents = function() {
        dom.tipoDoc.on("change", events.eTipoDoc)
    }, events = {
        eTipoDoc: function(e) {
            var _this;
            _this = $(e.currentTarget), dom.numDoc.val(""), functions.changeAttrForType(_this), st.validator.resetForm()
        }
    }, functions = {
        afterMaxMin: function(a, b) {
            dom.numDoc.attr("maxlength", a), dom.numDoc.attr("minlength", b)
        },
        changeAttrForType: function(_this) {
            /*"dni" === _this.val() ? (functions.afterMaxMin(14, 6), dom.numDoc.rules("remove", "alphNumeric"), dom.numDoc.rules("add", {
                digits: !0
            })) : (functions.afterMaxMin(14, 6), dom.numDoc.rules("remove", "digits"), dom.numDoc.rules("add", {
                alphNumeric: !0
            }))*/

            "dni" === _this.val() ? (functions.afterMaxMin(14, 14), 
                dom.numDoc.rules("remove", "digits"),
                dom.numDoc.rules("add", {alphNumeric: !0})
            ) : (functions.afterMaxMin(14, 6), dom.numDoc.rules("remove", "digits"), dom.numDoc.rules("add", {
                alphNumeric: !0
            }))
        },
        validateForm: function() {
            st.validator = dom.frmUserRegistration.validate({
                rules: {
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
                    txtDocument: {
                        digits: !0
                    },
                    txtPhone: {
                        digits: !0
                    },
                    txtMobilePhone: {
                        digits: !0
                    },
                    txtconadisCode: {
                        alphNumeric: !0
                    }
                },
                submitHandler: function(form) {
                    functions.saveAjaxForm()
                }
            }), dom.btnForm.removeAttr("disabled")
        },
        saveAjaxForm: function() {
            var options;
            dom.btnForm.attr({
                disabled: "",
                loading: ""
            }), options = {
                frm: st.frm,
                onComplete: function(json) {
                    var response;
                    return response = $.parseJSON(json), dom.btnForm.removeAttr("loading disabled"), dom.token.val(response.token), 1 === response.status ? ($(st.nameProfile).text(response.skill.nombres), functions.setCheckProfile(response), yOSON.utils.showMessage(dom.subMenuBox, "success", response.message)) : yOSON.utils.showMessage(dom.subMenuBox, "error", response.message)
                }
            }, $.fn.iframeUp("submit", options)
        },
        setCheckProfile: function(response) {
            var liSkill;
            "undefined" != typeof response.percent && $.isNumeric(response.percent) ? Sb.trigger("updateProgressBar", window.circleProgressBar[st.progressCircle], response.percent) : log("el porcentaje no es numérico"), liSkill = dom.sliderProfile.find("li[data-rel='" + response.iscompleted[0] + "']"), 1 === response.iscompleted[1] ? liSkill.addClass("is_passed") : liSkill.removeClass("is_passed")
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCatchDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/jquery.inputmask/dist/jquery.inputmask.bundle.min.js"]);