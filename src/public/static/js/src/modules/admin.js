yOSON.AppCore.addModule("callcenter_search_email", function(Sb) {
  var afterCathDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    frm: '#frmInfoCallCenter',
    txtEmailCliente: '#txtEmailCliente',
    selTypeDocument: '#fSelDoc',
    txtDocument: '#txtDocumentRuc',
    btnBuscar: '#btnBuscarPostulanteCallCenter',
    btnReset: '#fResetRS',
    divResult: '#resultadoBusqueda',
    divMessage: '#responseRuc',
    classInvalidError: '.invalid-form-error-message',
    urlGetToken: '/registro/obtener-token/',
    urlAjaxSendData: '/admin/gestion/buscar-email-callcenter'
  };
  catchDom = function() {
    dom.frm = $(st.frm);
    dom.txtEmailCliente = $(st.txtEmailCliente, dom.frm);
    dom.selTypeDocument = $(st.selTypeDocument, dom.frm);
    dom.txtDocument = $(st.txtDocument, dom.frm);
    dom.btnBuscar = $(st.btnBuscar, dom.frm);
    dom.btnReset = $(st.btnReset, dom.frm);
    dom.divResult = $(st.divResult);
    dom.classInvalidError = $(st.classInvalidError);
  };
  afterCathDom = function() {
    window.ParsleyValidator.addValidator('validruc', function(value, requirement) {
      return true;//return functions.fnValidateRuc(value);
    }, 32).addMessage('es', 'validruc', 'Ruc Inválido');
  };
  suscribeEvents = function() {
    dom.btnReset.on('click', events.eResetForm);
    dom.selTypeDocument.on('change', events.eChangeTypeDocument);
    dom.frm.on('submit', events.eSubmitForm);
    dom.frm.parsley().subscribe('parsley:form:validate', events.eValidateForm);
  };
  events = {
    eResetForm: function() {
      dom.txtDocument.attr({
        'id': 'txtDocumentRuc',
        'maxlength': '11',
        'data-parsley-validruc': '',
        'data-parsley-minlength': '11',
        'data-parsley-minlength-message': 'El RUC debe ser de 11 dígitos',
        'data-parsley-type-message': 'Ingrese un RUC válido'
      });
      dom.frm.parsley().reset();
      dom.classInvalidError.empty();
    },
    eChangeTypeDocument: function() {
      utils.responseParsley('error', false, dom.txtDocument.siblings(st.divMessage));
      dom.txtDocument.focus();
      dom.txtDocument.val('');
      dom.txtDocument.parsley().destroy();
      switch ($(this).val()) {
        case 'dni#8':
          dom.txtDocument.removeAttr('minlength');
          dom.txtDocument.removeAttr('data-parsley-validruc');
          dom.txtDocument.attr({
            'id': 'txtDocument',
            'maxlength': '14',
            'data-parsley-minlength': '14',
            'data-parsley-minlength-message': 'El DNI debe ser de 14 dígitos',
            'data-parsley-type-message': 'Ingrese un DNI válido'
          });
          break;
        case 'ruc#11':
          dom.txtDocument.attr({
            'id': 'txtDocumentRuc',
            'maxlength': '14',
            'data-parsley-validruc': '',
            'data-parsley-minlength': '14',
            'data-parsley-minlength-message': 'El RUC debe ser de 14 dígitos',
            'data-parsley-type-message': 'Ingrese un RUC válido'
          });
      }
      dom.txtDocument.parsley();
    },
    eSubmitForm: function(event) {
      event.preventDefault();
    },
    eValidateForm: function(formInstance) {
      if (formInstance.isValid('block1', true) || formInstance.isValid('block2', true)) {
        if (dom.selTypeDocument.val() === 'ruc#11' && $('.waiting', dom.frm).size() > 0) {
          return false;
        }
        dom.classInvalidError.empty();
        functions.fnGetToken();
      } else {
        dom.classInvalidError.html("Debe completar correctamente los campos de Email o Tipo de documento");
      }
    }
  };
  functions = {
    fnValidateRuc: function(value) {
      var dig, dig_valid, dig_verif, dig_verif_aux, factor, flag_dig, i, item, j, narray, residuo, resta, suma;
      factor = "5432765432";
      if (typeof value === "undefined" || value.length !== 11) {
        return false;
      }
      dig_valid = [10, 20, 17, 15];
      dig = value.substr(0, 2);
      flag_dig = dig_valid.indexOf(parseInt(dig));
      if (flag_dig === -1) {
        return false;
      }
      dig_verif = value.substr(10, 1);
      narray = [];
      i = 0;
      while (i < 10) {
        item = value.substr(i, 1) * factor.substr(i, 1);
        narray.push(item);
        i++;
      }
      suma = 0;
      j = 0;
      while (j < narray.length) {
        suma = suma + narray[j];
        j++;
      }
      residuo = suma % 11;
      resta = 11 - residuo;
      dig_verif_aux = resta.toString().substr(-1);
      if (dig_verif === dig_verif_aux) {
        return true;
      } else {
        return false;
      }
    },
    fnGetToken: function() {
      dom.btnBuscar.attr('disabled', true);
      $.fancybox.showLoading();
      $.ajax({
        url: st.urlGetToken,
        type: 'POST',
        dataType: 'JSON',
        error: function(res) {
          dom.btnBuscar.attr('disabled', false);
          $.fancybox.hideLoading();
        },
        success: function(dataToken) {
          functions.fnSendFormAjax(dataToken);
        }
      });
    },
    fnSendFormAjax: function(dataToken) {
      $.ajax({
        url: st.urlAjaxSendData,
        type: 'POST',
        dataType: 'HTML',
        data: {
          txtemail: dom.txtEmailCliente.val(),
          numTipo: dom.selTypeDocument.val().split('#')[1],
          valorTipo: dom.txtDocument.val(),
          token: dataToken
        },
        error: function(res) {
          dom.btnBuscar.attr('disabled', false);
          $.fancybox.hideLoading();
        },
        success: function(htmlResponse) {
          dom.btnBuscar.attr('disabled', false);
          $.fancybox.hideLoading();
          dom.divResult.removeClass('hide').html(htmlResponse);
        }
      });
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCathDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js']);

yOSON.AppCore.addModule("company_membership", function(Sb) {
  var beforeCathDom, cathDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    modal: '.modal_company_membership',
    btnModalAgree: '#btnAgree',
    btnModalCancel: '#btnCancel',
    classOpenModal: '.editMembAdmin',
    frm: '#frmMembresiaE',
    txtmonto: '#txtmonto',
    txtContrato: '#txtcontrato',
    selParent: 'cbotipo',
    selChild: 'id_membresia',
    hidMontoMem: '#txtMontoMem',
    hidProcess: '#process',
    txtfecini: '#txtfecini',
    txtfecfin: '#txtfecfin',
    templateHtmlOption: null,
    divWrapInformation: '.wrap_info_membership',
    flagAjax: true,
    urlGetWrapMembership: '/admin/membresia-empresa/opera-membresia',
    urlGetSelectOptions: '/admin/membresia-empresa/get-membresias-tipo',
    urlGetInformation: '/admin/membresia-empresa/get-data-membresia'
  };
  beforeCathDom = function() {
    st.templateHtmlOption = dataTemplate['all'].tpl_options;
    $.datepicker.setDefaults($.extend({
      showMonthAfterYear: false
    }, $.datepicker.regional['es']));
  };
  cathDom = function() {
    dom.modal = $(st.modal);
    dom.classOpenModal = $(st.classOpenModal);
  };
  suscribeEvents = function() {
    $(document).on('click', st.classOpenModal, events.eOpenModal);
    $(document).on('change', '[name$="' + st.selParent + '"]', events.eGetMembershipInfo);
    $(document).on('change', '[name$="' + st.selChild + '"]', events.eGetInformation);
    $(document).on('submit', st.frm, events.eSaveMembership);
    $(document).on('click', st.modal + ' ' + st.btnModalCancel, events.eCloseModal);
    $(document).on('click', st.txtfecini, events.ePutDatepickerIni);
    $(document).on('click', st.txtfecfin, events.ePutDatepickerFin);
  };
  events = {
    eOpenModal: function(e) {
      var _this;
      _this = $(this);
      if (!st.flagAjax) {
        return;
      }
      st.flagAjax = false;
      $.ajax({
        type: "GET",
        url: st.urlGetWrapMembership,
        data: {
          idMem: _this.attr('rel'),
          idEmp: _this.attr('idEmp')
        },
        dataType: "html",
        beforeSend: function() {
          $.fancybox.showLoading();
        },
        success: function(html) {
          $.fancybox.hideLoading();
          $.fancybox({
            closeEffect: 'none',
            content: html,
            maxWidth: 654,
            maxHeight: 450,
            openEffect: 'none',
            afterLoad: function() {
              st.flagAjax = true;
            },
            beforeShow: function() {
              $(st.frm).parsley().subscribe('parsley:form:validate', events.eAfterValidate);
              functions.eValidDigitalMembership(st.selParent, ['bonificado']);
              if ($('[name$="' + st.selParent + '"]', st.frm).val() !== 'bonificado') {
                functions.eValidDigitalMembership(st.selChild, ['7', '9']);
              }
            }
          });
        }
      });
    },
    eAfterValidate: function(e) {
      $.fancybox.update();
      $.fancybox.reposition();
    },
    eGetMembershipInfo: function(e) {
      var selChild, _this;
      _this = $(this);
      functions.eValidDigitalMembership(st.selParent, ['bonificado']);
      selChild = $('[name$="' + st.selChild + '"]', st.frm);
      selChild.parsley().reset();
      selChild.addClass('waiting').attr('disabled', true);
      $.ajax({
        url: st.urlGetSelectOptions,
        type: 'POST',
        dataType: 'JSON',
        data: {
          idtipo: _this.val()
        },
        success: function(response) {
          selChild.find('option:first').siblings().remove();
          $.each(response, function(i, data) {
            var compiled_template, objModel;
            objModel = {
              value: i,
              data: data
            };
            compiled_template = _.template(st.templateHtmlOption, objModel);
            selChild.append(compiled_template);
          });
          selChild.attr('disabled', false);
          selChild.removeClass('waiting');
          $(st.txtmonto).val('').parsley().reset();
        },
        error: function(res) {
          selChild.find('option:first').siblings().remove();
          selChild.removeClass('waiting').attr('disabled', true);
        }
      });
    },
    eGetInformation: function(e) {
      var divModal, divWrapInfo, _this;
      _this = $(this);
      if ($('[name$="' + st.selParent + '"]', st.frm).val() !== 'bonificado') {
        functions.eValidDigitalMembership(st.selChild, ['7', '9', '11']);
      }
      divWrapInfo = $(st.divWrapInformation);
      divModal = $(st.modal);
      utils.loader(divWrapInfo, true, true);
      $.ajax({
        type: "POST",
        url: st.urlGetInformation,
        data: {
          idMem: _this.val()
        },
        dataType: "html",
        success: function(html) {
          utils.loader(divWrapInfo, false, true);
          divWrapInfo.html(html);
          $(st.txtmonto).val(functions.fnNumberFormat($(st.hidMontoMem).val(), 2, '.', ''));
          $(st.txtmonto).parsley().reset();
        }
      });
    },
    eSaveMembership: function(e) {
      var _this;
      e.preventDefault();
      _this = $(this);
      utils.loader(_this, true, true);
      $.ajax({
        type: "POST",
        url: "/admin/membresia-empresa/opera-membresia",
        data: _this.serialize(),
        dataType: "JSON",
        success: function(data) {
          utils.loader(_this, false, true);
          utils.boxMessage(_this, 'prepend', data.message, 2000);
          if (data.status) {
            $.fancybox.update();
            $(st.btnModalAgree).attr('disabled', true);
            setTimeout(function() {
              location.reload();
            }, 1000);
          }
        }
      });
    },
    eCloseModal: function() {
      $.fancybox.close();
    },
    ePutDatepickerIni: function(e) {
      if (!$(this).hasClass('hasDatepicker')) {
        $(this).datepicker({
          dateFormat: 'dd/mm/yy',
          minDate: '-2Y 0M 0D',
          maxDate: '+3Y',
          changeMonth: true,
          changeYear: true,
          onSelect: function(dateText, inst) {
            $(this).parsley().reset();
          }
        });
        $(this).datepicker('show');
      }
    },
    ePutDatepickerFin: function(e) {
      if (!$(this).hasClass('hasDatepicker')) {
        $(this).datepicker({
          dateFormat: 'dd/mm/yy',
          minDate: 0,
          maxDate: '+3Y',
          changeMonth: true,
          changeYear: true,
          onSelect: function(dateText, inst) {
            $(this).parsley().reset();
          }
        });
        $(this).datepicker('show');
      }
    }
  };
  functions = {
    fnNumberFormat: function(numero, decimales, separador_decimal, separador_miles) {
      var miles;
      numero = parseFloat(numero);
      if (isNaN(numero)) {
        return "";
      }
      if (decimales !== void 0) {
        numero = numero.toFixed(decimales);
      }
      numero = numero.toString().replace(".", (separador_decimal !== void 0 ? separador_decimal : ","));
      if (separador_miles) {
        miles = new RegExp("(-?[0-9]+)([0-9]{3})");
        while (miles.test(numero)) {
          numero = numero.replace(miles, "$1" + separador_miles + "$2");
        }
      }
      return numero;
    },
    eValidDigitalMembership: function(selTag, valueArray) {
      var emRequired, selMembresia, txtContrato;
      selMembresia = $('[name$="' + selTag + '"]', st.frm);
      txtContrato = $(st.txtContrato, st.frm);
      emRequired = txtContrato.parent().siblings('.control-label').find('em');
      if ($.inArray(selMembresia.val(), valueArray) !== -1) {
        emRequired.html('&nbsp;');
        txtContrato.val('')
        txtContrato.attr('data-parsley-required', false);
      } else {
        emRequired.text('*');
        txtContrato.attr('data-parsley-required', true);
      }
      txtContrato.parsley().reset();
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    beforeCathDom();
    cathDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js', '/src/libs/underscore.js', '/datepicker/ui/ui.core.js', '/datepicker/ui/ui.datepicker.js', '/datepicker/ui/i18n/ui.datepicker-es.js']);

yOSON.AppCore.addModule("modal_switch_ads_home", function(Sb) {
  var factory, initialize;
  factory = function(op) {
    this.st = {
      context: '.dataGrid',
      btnActionAds: '.home_ads.enable',
      messageModal: '¿Desea mostrar en la página principal el aviso?',
      btnConfirm: '#btnConfirm',
      btnCancel: '#btnCancel',
      templateHtmlTitle: null,
      urlAjaxAds: '/admin/gestion/mostrar-aviso-portada'
    };
    this.dom = {};
    this.op = op;
  };
  factory.prototype = {
    catchDom: function() {
      this.dom.context = $(this.st.context);
      this.dom.btnActionAds = $(this.st.btnActionAds, this.dom.context);
      this.dom.btnHideAds = $(this.st.btnHideAds, this.dom.context);
    },
    afterCatchDom: function() {
      this.st.templateHtmlTitle = dataTemplate['modal'].confirm;
    },
    suscribeEvents: function() {
      this.dom.btnActionAds.on('click', {
        inst: this
      }, this.eShowModal);
      $(document).on('click', this.st.btnConfirm, {
        inst: this
      }, this.eConfirmModal);
      $(document).on('click', this.st.btnCancel, {
        inst: this
      }, this.eCloseModal);
    },
    eShowModal: function(event) {
      var dom, objModel, st, that, _this;
      event.preventDefault();
      that = event.data.inst;
      st = that.st;
      dom = that.dom;
      _this = $(this);
      objModel = {
        message: st.messageModal
      };
      $.fancybox({
        content: _.template(st.templateHtmlTitle, objModel),
        afterLoad: function() {
          $(st.btnConfirm, '.fancybox-overlay').attr('data-rel', _this.data('rel'));
        }
      });
    },
    eCloseModal: function() {
      $.fancybox.close();
    },
    eConfirmModal: function(event) {
      var dom, st, that, _this;
      that = event.data.inst;
      dom = that.dom;
      st = that.st;
      _this = $(this);
      $.fancybox.close();
      $.fancybox.showLoading();
      $.ajax({
        url: st.urlAjaxAds,
        type: 'POST',
        dataType: 'JSON',
        data: {
          idAviso: _this.data('rel')
        },
        success: function(res) {
          $.fancybox.hideLoading();
          if (res.status === 1) {
            dom.btnActionAds.addClass('desable').removeClass('enable');
            return;
          }
          utils.boxMessage(dom.context.parent(), 'prepend', res.msg);
        },
        error: function(res) {
          $.fancybox.hideLoading();
          utils.boxMessage(dom.context.parent(), 'prepend', 'Error en la solicitud');
        }
      });
    },
    execute: function() {
      this.st = $.extend({}, this.st, this.op);
      this.afterCatchDom();
      this.catchDom();
      this.suscribeEvents();
    }
  };
  initialize = function(oP) {
    $.each(oP, function(i, obj) {
      var instance;
      instance = new factory(obj);
      instance.execute();
    });
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js', 'src/libs/underscore.js']);
