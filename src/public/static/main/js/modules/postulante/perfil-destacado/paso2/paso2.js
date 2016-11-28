yOSON.AppCore.addModule("featured_profile", function(Sb) {
    var afterCathDom, catchDom, dom, events, fn, initialize, st, suscribeEvents;
    return dom = {}, st = {
        frm: "#frmFeaturedProfile",
        modalPagoEfectivo: "#pagoEfectivoModal",
        rdTipoDoc: 'input[name="radioTipoDoc"]',
        ticketWrap: "#ticketWrap",
        commercialTicketWrap: "#commercialTicketWrap",
        txtRuc: "#txtRuc",
        txtSocialReason: "#txtSocialReason",
        selVia: "#selVia",
        txtLocation: "#txtLocation",
        txtNroPuerta: "#txtNroPuerta",
        btnPay: "#btnPay",
        hidEnteRuc: "#hidEnteRuc",
        hidRucAdecsys: "#hidRucAdecsys",
        icon_help: ".icon_help",
        attrInputLoading: "loading",
        classInputWrap: ".form_control",
        urlAjax: "/perfil-destacado/valida-ruc-adecsys-postulante",
        xhrGetList: null
    }, catchDom = function() {
        dom.frm = $(st.frm), dom.rdTipoDoc = $(st.rdTipoDoc, dom.frm), dom.ticketWrap = $(st.ticketWrap, dom.frm), dom.commercialTicketWrap = $(st.commercialTicketWrap, dom.frm), dom.txtRuc = $(st.txtRuc, dom.frm), dom.txtSocialReason = $(st.txtSocialReason, dom.frm), dom.btnPay = $(st.btnPay, dom.frm), dom.hidEnteRuc = $(st.hidEnteRuc, dom.frm), dom.hidRucAdecsys = $(st.hidRucAdecsys, dom.frm), dom.selVia = $(st.selVia, dom.frm), dom.txtLocation = $(st.txtLocation, dom.frm), dom.txtNroPuerta = $(st.txtNroPuerta, dom.frm), dom.icon_help = $(st.icon_help)
    }, afterCathDom = function() {
        dom.txtRuc.tooltipster({
            content: "Ruc inválido",
            timer: 1500
        }), fn.validateForm(), Sb.trigger("modalSwitcherReInitialize", [{
            modal: st.modalPagoEfectivo,
            btnShowModal: st.icon_help,
            fancyBoxSetting: {
                maxWidth: 540,
                maxHeight: 520,
                padding: 0
            }
        }])
    }, suscribeEvents = function() {
        dom.rdTipoDoc.on("change", events.eChangeOption), dom.txtRuc.on("keyup", events.eSearchRucData)
    }, events = {
        eChangeOption: function(e) {
            dom.ticketWrap.toggle(), dom.commercialTicketWrap.toggle(), dom.txtRuc.val(""), dom.txtSocialReason.attr("readonly", !1).val(""), dom.selVia.attr("disabled", !1).val(""), dom.txtLocation.attr("readonly", !1).val(""), dom.txtRuc.parents(st.classInputWrap).siblings(st.classInputWrap).addClass("hide"), "factura" === $(st.rdTipoDoc + ":checked").val() ? dom.btnPay.attr("disabled", !0) : dom.btnPay.attr("disabled", !1)
        },
        eSearchRucData: function(e) {
            var self, value;
            if (self = $(this), value = self.val(), 11 === value.length) {
                if (!fn.validateRuc(value)) return dom.txtRuc.tooltipster("show"), dom.txtRuc.parents(st.classInputWrap).siblings(st.classInputWrap).addClass("hide"), dom.btnPay.attr("disabled", !0), !1;
                self.attr({
                    loading: !0,
                    disabled: !0
                }), fn.getDataAjax(self, value)
            } else dom.txtRuc.parents(st.classInputWrap).siblings(st.classInputWrap).addClass("hide"), dom.btnPay.attr("disabled", !0)
        }
    }, fn = {
        getDataAjax: function(self, value) {
            st.xhrGetList && st.xhrGetList.abort(), st.xhrGetList = $.ajax({
                url: st.urlAjax,
                type: "POST",
                dataType: "JSON",
                data: {
                    csrfhash: yOSON.token,
                    ruc: value
                },
                success: function(response) {
                    self.removeAttr(st.attrInputLoading), self.attr("disabled", !1), dom.btnPay.attr("disabled", !1), yOSON.token = response.token, 1 === response.status ? (dom.txtSocialReason.attr("readonly", !0).val(response.data.nameCompany), dom.selVia.attr("disabled", !0).val(response.data.typeVia), dom.txtLocation.attr("readonly", !0).val(response.data.address), dom.txtNroPuerta.attr("readonly", !0).val(response.data.numberDoor)) : (dom.txtSocialReason.attr("readonly", !1).val(""), dom.selVia.attr("disabled", !1).val(""), dom.txtLocation.attr("readonly", !1).val(""), dom.txtNroPuerta.attr("readonly", !1).val("")), dom.hidRucAdecsys.val(response.status), dom.hidEnteRuc.val(response.data.id), self.parents(st.classInputWrap).siblings().removeClass("hide")
                },
                error: function(res) {
                    self.removeAttr(st.attrInputLoading), self.attr("disabled", !1)
                }
            })
        },
        validateForm: function() {
            dom.frm.validate({
                ignore: ":hidden",
                rules: {
                    txtRuc: {
                        digits: !0
                    },
                    txtSocialReason: {
                        comment: !0
                    },
                    txtLocation: {
                        comment: !0
                    },
                    txtNroPuerta: {
                        digits: !0
                    }
                },
                submitHandler: function(form) {
                    dom.btnPay.atrr({
                        disabled: !0,
                        loading: !0
                    }), setTimeout(function() {
                        $(form).ajaxSubmit()
                    }, 3e3)
                }
            })
        },
        validateRuc: function(value) {
            var dig, dig_valid, dig_verif, dig_verif_aux, factor, flag_dig, i, item, j, narray, residuo, resta, suma;
            if (factor = "5432765432", "undefined" == typeof value || 11 !== value.length) return !1;
            if (dig_valid = [10, 20, 17, 15], dig = value.substr(0, 2), flag_dig = dig_valid.indexOf(parseInt(dig)), -1 === flag_dig) return !1;
            for (dig_verif = value.substr(10, 1), narray = [], i = 0; 10 > i;) item = value.substr(i, 1) * factor.substr(i, 1), narray.push(item), i++;
            for (suma = 0, j = 0; j < narray.length;) suma += narray[j], j++;
            return residuo = suma % 11, resta = 11 - residuo, dig_verif_aux = resta.toString().substr(-1), dig_verif === dig_verif_aux ? !0 : !1
        }
    }, initialize = function(oP) {
        $.extend(st, oP), catchDom(), afterCathDom(), suscribeEvents()
    }, {
        init: initialize
    }
}, ["js/libs/tooltipster/js/jquery.tooltipster.min.js"]);