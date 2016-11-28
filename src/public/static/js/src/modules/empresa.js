yOSON.AppCore.addModule("btn_actions_preferencial_ad", function(Sb) {
  var catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    frm: '#frmPublishAd',
    btnClear: '#btnClear',
    divWrapSkills: '.wrap-skills',
    divControls: '.control-group',
    divContexts: '.wrap-controls:not(:first)',
    divBlueTitle: '.blue-title:first',
    txtNombrePuesto: '#nombre_puesto',
    selNivelPuesto: '#id_nivel_puesto',
    selArea: '#id_area',
    txaFunciones: '#funciones',
    txaRespon: '#responsabilidades',
    selSalario: '#salario'
  };
  catchDom = function() {
    dom.frm = $(st.frm);
    dom.btnClear = $(st.btnClear);
    dom.divBlueTitle = $(st.divBlueTitle, dom.frm);
    dom.txtNombrePuesto = $(st.txtNombrePuesto, dom.frm);
    dom.selNivelPuesto = $(st.selNivelPuesto, dom.frm);
    dom.selArea = $(st.selArea, dom.frm);
    dom.txaFunciones = $(st.txaFunciones, dom.frm);
    dom.txaRespon = $(st.txaRespon, dom.frm);
    dom.selSalario = $(st.selSalario, dom.frm);
    dom.divContexts = $(st.divContexts, dom.frm);
  };
  suscribeEvents = function() {
    dom.btnClear.on('click', events.eClearAll);
  };
  events = {
    eClearAll: function(e) {
      dom.txtNombrePuesto.val('');
      dom.selNivelPuesto.val(0);
      dom.selArea.val(0);
      dom.txaFunciones.val('');
      dom.txaRespon.val('');
      dom.selSalario.val(0);
      $.each($(st.divWrapSkills, dom.divContexts), function(i, elem) {
        var li;
        li = $(elem).find('> li');
        if (li.size() > 1) {
          li.last().siblings().remove();
        }
      });
      $.each($(st.divControls, dom.divContexts), function(i, elem) {
        var elemTag;
        elemTag = $(elem).find(':text, textarea, select');
        $(elem).find('.response').removeClass('bad').text('');
        if (elemTag) {
          functions.clearTag(elemTag);
        }
      });
      $('html, body').animate({
        scrollTop: parseInt(dom.divBlueTitle.offset().top) - 20
      }, 400);
    }
  };
  functions = {
    clearTag: function(elemTag) {
      var tagName;
      tagName = elemTag[0].tagName;
      if (tagName === 'INPUT' || tagName === 'TEXTAREA') {
        elemTag.val('');
      } else if (tagName === 'SELECT') {
        elemTag.val(0);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("change_name_company", function(Sb) {
  var beforeCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    context: '#frmPublishAd',
    txtName: '#otro_nombre_empresa',
    chkOption: '#mostrar_empresa_opcion',
    hidShowName: '#mostrar_empresa'
  };
  catchDom = function() {
    dom.context = $(st.context);
    dom.txtName = $(st.txtName, st.context);
    dom.chkOption = $(st.chkOption, st.context);
    dom.hidShowName = $(st.hidShowName, st.context);
  };
  beforeCatchDom = function() {
    functions.fnShowCompanyName(dom.chkOption);
  };
  suscribeEvents = function() {
    dom.chkOption.on('change', events.eChangeOption);
  };
  events = {
    eChangeOption: function(e) {
      var _this;
      _this = $(this);
      functions.fnShowCompanyName(_this);
    }
  };
  functions = {
    fnShowCompanyName: function(_this) {
      var nameCompany, nameDefault;
      nameDefault = dom.txtName.data('text');
      nameCompany = dom.txtName.data('empresa');
      if (_this.is(':checked')) {
        dom.txtName.removeAttr('readonly').val(nameDefault).focus();
        dom.hidShowName.val(0);
      } else {
        dom.txtName.val(nameCompany).attr('readonly', 'readonly');
        dom.hidShowName.val(1);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    beforeCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("company_registration", function(Sb) {
  var afterCathDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    frm: '#frmCompanyRegistration',
    txtPassword: '#txtPassword',
    txtRepeatPassword: '#txtRepeatPassword',
    txtTradeName: '#txtNombreComercial',
    txtBusinessName: '#txtRazonSocial',
    txtRuc: '#txtRuc',
    chkPolitics: '#chkPolitics',
    btnCompanyRegister: '#btnCompanyRegister'
  };
  catchDom = function() {
    dom.frm = $(st.frm);
    dom.txtPassword = $(st.txtPassword, dom.frm);
    dom.txtRepeatPassword = $(st.txtRepeatPassword, dom.frm);
    dom.txtTradeName = $(st.txtTradeName, dom.frm);
    dom.txtBusinessName = $(st.txtBusinessName, dom.frm);
    dom.txtRuc = $(st.txtRuc, dom.frm);
    dom.chkPolitics = $(st.chkPolitics, dom.frm);
    dom.btnCompanyRegister = $(st.btnCompanyRegister, dom.frm);
  };
  afterCathDom = function() {
    dom.txtPassword.pstrength();
    dom.txtPassword.trigger('keyup');
    window.ParsleyValidator.addValidator('wordsLimit', function(value, wordsLimit) {
      return value.split(/\s/g).length <= wordsLimit;
    }, 32).addMessage('es', 'wordsLimit', 'Exede el número de 6 palabras permitidas').addValidator('validRuc', function(value, requirement) {
      return functions.fnValidateRuc(value);
    }, 32).addMessage('es', 'validRuc', 'Ruc Inválido');
  };
  suscribeEvents = function() {
    dom.txtPassword.on('keyup', events.eCleanRepeatPassword);
    dom.frm.on('submit', events.eSubmit);
    dom.frm.parsley().subscribe('parsley:form:validate', events.eValidateForm);
  };
  events = {
    eCleanRepeatPassword: function() {
      dom.txtRepeatPassword.val('');
    },
    eSubmit: function(event) {
      event.preventDefault();
    },
    eValidateForm: function(formInstance) {
      if (formInstance.isValid()) {
        if ($('.waiting', dom.frm).size() === 0) {
          dom.btnCompanyRegister.attr('disabled', true);
          document.getElementById(st.frm.substring(1)).submit();
        } else {
          $('html, body').scrollTop(parseInt($('.waiting', dom.frm).offset().top) - 100);
        }
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
}, ['/src/libs/jquery/jqPstrength.min.js']);

google.load("visualization", "1", {
  packages: ["corechart"]
});

yOSON.AppCore.addModule("google_charts", function(Sb) {
  var afterCatchDom, catchDom, dom, functions, initialize, st;
  dom = {};
  st = {
    elementPostulant: 'divChartPostulant',
    chart_total_number: '.chart_total_number',
    optionsPies: {
      genre: {
        data: tmp.chartData.genre,
        colors: ['#d87102', '#ffa84a'],
        element: 'divChartGender'
      },
      age: {
        data: tmp.chartData.age,
        colors: ['#2f4109', '#97bb4b', '#3B7303', '#73A605', '#455e10'],
        element: 'divCharAge'
      },
      studies: {
        data: tmp.chartData.studies,
        colors: ['#00557C', '#186D94', '#3488AD', '#81C1DC', '#BBE5F3'],
        element: 'divChartStudy'
      }
    }
  };
  catchDom = function() {};
  afterCatchDom = function() {
    google.setOnLoadCallback(functions.fnRunDrawCharts);
  };
  functions = {
    fnRunDrawCharts: function() {
      var data, k, _ref;
      functions.fnDrawChartPostulant();
      utils.loader($('#' + st.elementPostulant), false, false);
      _ref = st.optionsPies;
      for (k in _ref) {
        data = _ref[k];
        functions.fnDrawChartPie(data);
      }
    },
    fnDrawChartPostulant: function() {
      var chartPostulant, dataPostulantEmpty, dataPostulantFull, optionsPostulant;
      dataPostulantEmpty = google.visualization.arrayToDataTable(tmp.chartData.postulant.empty);
      optionsPostulant = {
        width: 960,
        height: 400,
        title: 'Postulantes del aviso',
        legend: {
          position: 'top',
          alignment: 'end'
        },
        animation: {
          duration: 1000,
          easing: 'out'
        },
        pointSize: 5,
        vAxis: {
          minValue: 0,
          maxValue: tmp.chartData.maxValue
        },
        backgroundColor: '#f4f7f7',
        series: {
          0: {
            color: '#DC373D'
          },
          1: {
            color: '#0085D4'
          }
        }
      };
      chartPostulant = new google.visualization.LineChart(document.getElementById(st.elementPostulant));
      chartPostulant.draw(dataPostulantEmpty, optionsPostulant);
      dataPostulantFull = google.visualization.arrayToDataTable(tmp.chartData.postulant.full);
      chartPostulant.draw(dataPostulantFull, optionsPostulant);
      google.visualization.events.addListener(chartPostulant, 'animationfinish', function() {
        $(st.chart_total_number).show();
      });
    },
    fnDrawChartPie: function(options) {
      var chartPie, dataPie, optionsPie;
      dataPie = google.visualization.arrayToDataTable(options.data);
      optionsPie = {
        width: 300,
        height: 300,
        chartArea: {
          left: "10px",
          top: 10,
          width: "95%"
        },
        pieHole: 0.6,
        pieSliceText: 'none',
        colors: options.colors
      };
      chartPie = new google.visualization.PieChart(document.getElementById(options.element));
      chartPie.draw(dataPie, optionsPie);
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("message_new_charts", function(Sb) {
  var beforeCatchDom, catchDom, dom, functions, initialize, st;
  dom = {};
  st = {
    message_button_wrapper: '.message_button_wrapper'
  };
  catchDom = function() {
    dom.message_button_wrapper = $(st.message_button_wrapper);
  };
  beforeCatchDom = function() {
    functions.fnShowMessage();
  };
  functions = {
    fnShowMessage: function() {
      if (dom.message_button_wrapper.length !== 0) {
        setTimeout(function() {
          $.fancybox({
            content: ' ',
            tpl: {
              wrap: '<div></div>'
            },
            beforeShow: function() {
              dom.message_button_wrapper.show();
            },
            beforeClose: function() {
              dom.message_button_wrapper.fadeOut();
            }
          });
        }, 1000);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    beforeCatchDom();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("modal_invitar_anuncio", function(Sb) {
  var factory, initialize;
  factory = function(op) {
    this.st = {
      btnInvite: '#btnInvite',
      btnAgree: '#btnAgree',
      btnClose: '#btnCancel',
      aSendInvite: '.linkInviteP',
      divLinkHeadMFC: '#linkHeadMFC',
      urlAjax: '/empresa/mis-procesos/invitar-proceso/',
      urlAjaxInvite: '/empresa/mis-procesos/enviar-invitacion'
    };
    this.dom = {};
    this.op = op;
  };
  factory.prototype = {
    catchDom: function() {
      this.dom.btnInvite = $(this.st.btnInvite);
      this.dom.divLinkHeadMFC = $(this.st.divLinkHeadMFC);
    },
    suscribeEvents: function() {
      this.dom.btnInvite.on('click', {
        inst: this
      }, this.eShowModal);
      $(document).on('click', this.st.aSendInvite, {
        inst: this
      }, this.eSendInvitation);
    },
    eShowModal: function(event) {
      var dom, st, that, _this;
      event.preventDefault();
      that = event.data.inst;
      st = that.st;
      dom = that.dom;
      _this = $(this);
      $.fancybox.showLoading();
      $.ajax({
        url: st.urlAjax,
        type: 'POST',
        dataType: 'html',
        data: {
          id: _this.attr('rel'),
          idpostulacionactual: _this.attr('idpostulacion'),
          idAviso: $("#idAviso").val()
        },
        success: function(html) {
          $.fancybox({
            content: html,
            maxHeight: 400,
            afterLoad: function() {
              $.fancybox.hideLoading();
            }
          });
        },
        error: function(res) {
          $.fancybox.hideLoading();
        }
      });
    },
    eSendInvitation: function(event) {
      var dom, st, that, _this;
      event.preventDefault();
      that = event.data.inst;
      st = that.st;
      dom = that.dom;
      _this = $(this);
      $.fancybox.showLoading();
      $.ajax({
        url: st.urlAjaxInvite,
        type: 'POST',
        dataType: 'JSON',
        data: {
          idAw: _this.attr('rel'),
          idPos: _this.attr('rol'),
          idPostulacion: _this.attr("idpostulacion"),
          tok: _this.data('tok')
        },
        success: function(msg) {
          $.fancybox.hideLoading();
          $.fancybox.close();
          if (msg !== -1) {
            utils.boxMessage(dom.divLinkHeadMFC, 'prepend', 'La invitación se envió con éxito.');
          } else {
            utils.boxMessage(dom.divLinkHeadMFC, 'prepend', 'Hubo un error en la petición.');
          }
        },
        error: function(msg) {
          $.fancybox.hideLoading();
          $.fancybox.close();
          utils.boxMessage(dom.divLinkHeadMFC, 'prepend', 'Hubo un error en la petición.');
        }
      });
    },
    execute: function() {
      this.st = $.extend({}, this.st, this.op);
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
}, ['/src/libs/jquery/jqFancybox.js']);

yOSON.AppCore.addModule("modal_send_email", function(Sb) {
  var catchDom, dom, events, initialize, st, suscribeEvents;
  dom = {};
  st = {
    modal: '#divWrapSendEmail',
    frm: '#frmSendEmail',
    btnModal: '#btnSendContact',
    btnInput: '#btnEnviarMail',
    btnCancel: '#btnCancelCA',
    urlAjax: '/mi-cuenta/compartir',
    response: '.response.all'
  };
  catchDom = function() {
    dom.modal = $(st.modal);
    dom.btnInput = $(st.btnInput);
    dom.btnModal = $(st.btnModal, dom.modal);
    dom.btnCancel = $(st.btnCancel, dom.modal);
    dom.frm = $(st.frm, dom.modal);
    dom.response = $(st.response, dom.modal);
  };
  suscribeEvents = function() {
    dom.btnInput.on('click', events.eShowModal);
    dom.btnCancel.on('click', events.eCloseModal);
    dom.frm.data('eValidError', events.eReposition);
    dom.frm.data('eValidSuccess', events.eValidSuccess);
  };
  events = {
    eShowModal: function(e) {
      $.fancybox.open(st.modal, {
        maxWidth: 520,
        maxHeight: 480
      });
    },
    eCloseModal: function(e) {
      $.fancybox.close();
    },
    eReposition: function() {
      $.fancybox.update();
      $.fancybox.reposition();
    },
    eValidSuccess: function() {
      utils.loader(dom.frm, true, true);
      $.ajax({
        url: st.urlAjax,
        type: 'POST',
        dataType: 'JSON',
        data: dom.frm.serialize() + '&idAviso=' + $("#idAviso").val(),
        success: function(response) {
          if (response.status === 'ok') {
            utils.loader(dom.frm, false, true);
            dom.frm.hide();
            dom.response.removeClass('bad hide').addClass('good').text('Datos Enviados correctamente.');
            $.fancybox.update();
            setTimeout(function() {
              $.fancybox.close();
              window.location.reload();
            }, 1000);
          } else {
            dom.response.removeClass('good hide').addClass('bad').text('Ingrese sus datos correctamente.');
            utils.loader(dom.frm, false, true);
          }
        },
        error: function(response) {
          utils.loader(dom.frm, false, true);
        }
      });
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js']);

yOSON.AppCore.addModule("modal_view_membership", function(Sb) {
  var catchDom, dom, events, initialize, st, suscribeEvents;
  dom = {};
  st = {
    lnkMembreshipDetail: '.view_membership_details',
    urlAjax: '/empresa/mi-cuenta/detalle-empresa-membresia',
    xhrRequest: null
  };
  catchDom = function() {
    dom.lnkMembreshipDetail = $(st.lnkMembreshipDetail);
  };
  suscribeEvents = function() {
    dom.lnkMembreshipDetail.on('click', events.eShowModal);
  };
  events = {
    eShowModal: function(e) {
      var _this;
      _this = $(this);
      if (st.xhrRequest) {
        st.xhrRequest.abort();
        $.fancybox.hideLoading();
      }
      $.fancybox.showLoading();
      st.xhrRequest = $.ajax({
        url: st.urlAjax,
        type: 'GET',
        dataType: 'HTML',
        data: {
          idEmpMem: _this.attr("data-rel")
        },
        success: function(html) {
          $.fancybox.hideLoading();
          $.fancybox({
            content: html,
            maxHeight: 400,
            maxWidth: 500
          });
        },
        error: function(response) {
          $.fancybox.hideLoading();
        }
      });
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js']);

yOSON.AppCore.addModule("my_search_delete", function(Sb) {
  var catchDom, dom, events, initialize, st, suscribeEvents;
  dom = {};
  st = {
    cboSearchSaved: '#cboSearchSaved',
    divSearchList: '#searchList',
    modal: '#divModalDeleteSearch',
    btnDelete: '#btnDelete',
    btnClose: '#btnClose',
    pNameSearch: '.name_search',
    urlGetToken: '/registro/obtener-token/',
    urlAjax: '/empresa/buscador-aptitus/eliminar-alerta'
  };
  catchDom = function() {
    dom.cboSearchSaved = $(st.cboSearchSaved);
    dom.divSearchList = $(st.divSearchList);
    dom.aDelete = $('li a.last', dom.divSearchList);
    dom.modal = $(st.modal);
    dom.btnDelete = $(st.btnDelete, st.modal);
    dom.btnClose = $(st.btnClose, st.modal);
    dom.pNameSearch = $(st.pNameSearch, st.modal);
  };
  suscribeEvents = function() {
    dom.cboSearchSaved.on('click', events.eOpenWrapSearch);
    $(document).on('click', st.divSearchList + ' li a.last', events.eOpenModal);
    $(document).on('mouseup', events.eClickOutsite);
    dom.btnDelete.on('click', events.eSearchDelete);
    dom.btnClose.on('click', events.eModalClose);
  };
  events = {
    eOpenModal: function(event) {
      var _this;
      _this = $(this);
      $.fancybox.open(st.modal, {
        maxWidth: 492,
        minWidth: 330,
        arrows: false
      });
      dom.btnDelete.data('uid', _this.data('id'));
      dom.pNameSearch.text(_this.prev().text());
    },
    eModalClose: function(event) {
      $.fancybox.close();
    },
    eSearchDelete: function() {
      var value, _this;
      _this = $(this);
      value = _this.data('uid');
      utils.loader(dom.modal, true, true);
      $.ajax({
        url: st.urlGetToken,
        type: 'POST',
        dataType: 'JSON',
        error: function(res) {
          return utils.loader(dom.modal, false, true);
        },
        success: function(result) {
          $.ajax({
            url: st.urlAjax,
            type: 'POST',
            dataType: 'JSON',
            data: {
              id: value,
              token: result
            },
            success: function(res) {
              utils.loader(dom.modal, false, true);
              $.fancybox.close();
              if (res.estado === 1) {
                $(st.divSearchList + ' li a.last[data-id="' + value + '"]').parent().remove();
                if (dom.divSearchList.find('li').size() === 0) {
                  dom.divSearchList.parent().hide();
                }
              }
              utils.boxMessage($('.grids-7'), 'prepend', res.mensaje);
            },
            error: function(res) {
              utils.loader(dom.modal, false, true);
              dom.aDelete.removeClass('disabled');
            }
          });
        }
      });
    },
    eOpenWrapSearch: function(event) {
      if (dom.divSearchList.is(':hidden')) {
        dom.divSearchList.stop(true, true).slideDown('fast');
      } else {
        dom.divSearchList.stop(true, true).slideUp('fast');
      }
    },
    eClickOutsite: function(e) {
      if (e.target.id !== dom.divSearchList.attr('id') && !dom.divSearchList.has(e.target).length) {
        dom.divSearchList.slideUp('fast');
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js']);

yOSON.AppCore.addModule("my_search_save", function(Sb) {
  var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    btnSearchSave: '#btnSearchSave',
    modal: '#divModalSaveSearch',
    btnSave: '#btnSave',
    btnClose: '#btnClose',
    txtNameSearch: '#txtNameSaveSearch',
    divSearchList: '#searchList',
    urlGetToken: '/registro/obtener-token/',
    urlAjax: '/empresa/buscador-aptitus/agregar-alerta',
    divResponse: '.response',
    divControlsIn: '.controls-inline',
    templateHtmlTitle: null
  };
  catchDom = function() {
    dom.modal = $(st.modal);
    dom.btnSearchSave = $(st.btnSearchSave);
    dom.btnSave = $(st.btnSave, dom.modal);
    dom.btnClose = $(st.btnClose, dom.modal);
    dom.txtNameSearch = $(st.txtNameSearch, dom.modal);
    dom.divResponse = $(st.divResponse, dom.modal);
    dom.divControlsIn = $(st.divControlsIn, dom.modal);
    dom.divSearchList = $(st.divSearchList);
  };
  afterCatchDom = function() {
    st.templateHtmlTitle = dataTemplate['buscador_aptitus'].search_list;
  };
  suscribeEvents = function() {
    dom.btnSearchSave.on('click', events.eOpenModal);
    dom.btnSave.on('click', events.eSaveNow);
    dom.btnClose.on('click', events.eCloseModal);
  };
  events = {
    eOpenModal: function(event) {
      var _this;
      _this = $(this);
      $.fancybox.open(st.modal, {
        maxWidth: 492,
        arrows: false,
        afterClose: functions.fnCloseModal
      });
    },
    eSaveNow: function() {
      var valueName;
      valueName = dom.txtNameSearch.val();
      utils.loader(dom.modal, true, true);
      $.ajax({
        url: st.urlGetToken,
        type: 'POST',
        dataType: 'JSON',
        error: function(res) {
          return utils.loader(dom.modal, false, true);
        },
        success: function(result) {
          $.ajax({
            url: st.urlAjax,
            type: 'POST',
            dataType: 'JSON',
            data: {
              nombre: valueName,
              token: result,
              url: window.location.href
            },
            success: function(res) {
              var compiled_template, objModel;
              utils.loader(dom.modal, false, true);
              dom.btnSave.parent().slideUp();
              dom.divControlsIn.slideUp();
              if (dom.divSearchList.is(':hidden')) {
                dom.divSearchList.parent().show();
              }
              if (res.estado === 1) {
                dom.divResponse.removeClass('hide bad').addClass('.active_fade good').text(res.mensaje);
                objModel = {
                  id: res.id,
                  nombre: valueName,
                  url: res.url
                };
                compiled_template = _.template(st.templateHtmlTitle, objModel);
                dom.divSearchList.append(compiled_template);
                setTimeout(function() {
                  $.fancybox.close();
                }, 1200);
              } else {
                dom.divResponse.removeClass('hide good').addClass('.active_fade bad').text(res.mensaje);
                setTimeout(function() {
                  functions.fnCloseModal();
                }, 1000);
              }
            },
            error: function(res) {
              utils.loader(dom.modal, false, true);
            }
          });
        }
      });
    },
    eCloseModal: function() {
      $.fancybox.close();
    }
  };
  functions = {
    fnCloseModal: function() {
      dom.btnSave.parent().slideDown();
      dom.divControlsIn.slideDown();
      dom.divResponse.addClass('hide');
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js', '/src/libs/underscore.js']);

yOSON.AppCore.addModule("printed_detailed_notice", function(Sb) {
  var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    txaAreaCountCP: '#areaCountCP',
    divDisplay: '#divDisplay',
    maxWords: '#maxWords',
    countWords: '#countWords',
    valueMaxWord: null
  };
  catchDom = function() {
    dom.txaAreaCountCP = $(st.txaAreaCountCP);
    dom.divDisplay = $(st.divDisplay);
    dom.maxWords = $(st.maxWords);
    dom.countWords = $(st.countWords);
  };
  afterCatchDom = function() {
    st.valueMaxWord = dom.maxWords.text();
    functions.fnSetText(dom.txaAreaCountCP);
  };
  suscribeEvents = function() {
    dom.txaAreaCountCP.on('keyup', events.eDisplayMirror);
  };
  events = {
    eDisplayMirror: function(event) {
      var _this;
      _this = $(this);
      functions.fnSetText(_this);
      event.preventDefault();
    }
  };
  functions = {
    fnSetText: function(_this) {
      var match, value, valueWords;
      value = _this.val();
      match = value.match(/([a-záéíóúÁÉÍÓÚ]{1,12})/gi);
      valueWords = 0;
      if (match) {
        valueWords = match.length;
      }
      if (valueWords > st.valueMaxWord) {
        _this.val(dom.divDisplay.text());
        return false;
      } else {
        dom.countWords.text(st.valueMaxWord - valueWords);
        dom.divDisplay.text(value);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("publish_ads_pay", function(Sb) {
  var afterCatchDom, catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    frm: '#formEndP4Emp',
    btnForm: '#nextEmpP3',
    rdPayType: 'input[name=radioTipoPago]',
    btnPay: '#nextEmpP3',
    frmModal: '#frmPosVirtual',
    txtVoucher: '#txtVoucher',
    txtDate: '#txtPaymentDate',
    hidToken: '#auth_token',
    btnClose: '#btnCancel',
    btnProcess: '#btnProcess',
    urlGetToken: '/registro/obtener-token/',
    urlAjaxGetForm: '/empresa/comprar-aviso/pago-pos',
    urlAjaxSendData: '/empresa/comprar-aviso/valida-data-pos'
  };
  catchDom = function() {
    dom.frm = $(st.frm);
    dom.btnForm = $(st.btnForm, dom.frm);
    dom.rdPayType = $(st.rdPayType, dom.frm);
    dom.btnPay = $(st.btnPay, dom.frm);
  };
  afterCatchDom = function() {
    window.ParsleyValidator.addValidator('date', function(value) {
      return /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/.test(value);
    }, 32).addMessage('es', 'date', 'El formato de fecha no es correcto');
  };
  suscribeEvents = function() {
    //dom.frm.on('submit', events.eShowModal);
    dom.frm.parsley().subscribe('parsley:form:validate', events.eShowModal);
    $(document).on('click', st.frmModal + ' ' + st.txtDate, events.ePutDatepicker);
    $(document).on('click', st.frmModal + ' ' + st.btnClose, events.eModalClose);
  };
  events = {
    eShowModal: function(formInstance) {
      if (formInstance.isValid()){
        if ($(st.rdPayType + ':checked').val() === 'pos') {
          //event.preventDefault();
          functions.fnGetToken();
        } else {
          dom.btnPay.text('Procesando...').attr('disabled', true);
        }
      }
    },
    ePutDatepicker: function(event) {
      if (!$(this).hasClass('hasDatepicker')) {
        $(this).datepicker({
          dateFormat: 'dd/mm/yy',
          minDate: -3,
          maxDate: 0,
          onSelect: function(dateText, inst) {
            $(this).parsley().reset();
          }
        });
        $(this).datepicker('show');
      }
    },
    eModalClose: function(event) {
      $.fancybox.close();
    },
    eValidateFormModal: function(formInstance) {
      $.fancybox.update();
      $.fancybox.reposition();
      $('.fancybox-inner').addClass('autoHeight');
      if (formInstance.isValid()) {
        utils.loader(dom.frmModal, true, true);
        functions.fnSendFormAjax();
      }
    },
    eSubmitFormModal: function(event) {
      event.preventDefault();
    }
  };
  functions = {
    fnGetToken: function(_this) {
      dom.btnPay.attr('disabled', true);
      $.fancybox.showLoading();
      $.ajax({
        url: st.urlGetToken,
        type: 'POST',
        dataType: 'JSON',
        error: function(res) {
          dom.btnPay.attr('disabled', false);
        },
        success: function(dataToken) {
          functions.fnGetForm(dataToken);
        }
      });
    },
    fnGetForm: function(dataToken) {
      $.ajax({
        url: st.urlAjaxGetForm,
        type: 'POST',
        dataType: 'HTML',
        data: {
          token: dataToken
        },
        error: function(res) {
          $.fancybox.hideLoading();
          dom.btnPay.attr('disabled', false);
        },
        success: function(htmlContent) {
          $.fancybox.hideLoading();
          $.fancybox({
            content: htmlContent,
            closeEffect: 'none',
            maxWidth: 370,
            autoWidth: false,
            autoHeight: true,
            openEffect: 'none',
            afterClose: function() {
              dom.btnPay.attr('disabled', false);
            },
            afterShow: function() {
              dom.frmModal = $(st.frmModal);
              dom.frmModal.parsley().subscribe('parsley:form:validate', events.eValidateFormModal);
              dom.frmModal.on('submit', events.eSubmitFormModal);
            }
          });
        }
      });
    },
    fnSendFormAjax: function() {
      $.ajax({
        url: st.urlAjaxSendData,
        type: 'POST',
        dataType: 'JSON',
        data: dom.frmModal.serialize(),
        error: function(res) {
          utils.loader(dom.frmModal, false, true);
        },
        success: function(res) {
          utils.loader(dom.frmModal, false, true);
          if (res.status === 1) {
            $.fancybox.close();
            dom.btnPay.text('Procesando...').attr('disabled', true);
            document.getElementById(st.frm.slice(1)).submit();
          } else {
            $(st.hidToken, dom.frmModal).val(res.token);
            $(st.txtVoucher, dom.frmModal).val('');
            utils.boxMessage($('h3', st.frmModal), 'insertAfter', res.message);
          }
        }
      });
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, ['/src/libs/jquery/jqFancybox.js', '/datepicker/ui/ui.core.js', '/datepicker/ui/ui.datepicker.js', '/datepicker/ui/i18n/ui.datepicker-es.js']);

yOSON.AppCore.addModule("search_checked", function(Sb) {
  var catchDom, dom, events, initialize, st, suscribeEvents;
  dom = {};
  st = {
    divMoreOptions: '.more_options',
    divAccord: '.accord',
    chkWrap: '.ioption.accord',
    chkInput: ':checkbox'
  };
  catchDom = function() {
    dom.divMoreOptions = $(st.divMoreOptions);
    dom.chkInput = $(st.chkInput, dom.divMoreOptions);
    dom.chkWrap = $(st.chkWrap);
    dom.chkParent = $(st.chkInput, dom.chkWrap);
  };
  suscribeEvents = function() {
    dom.chkInput.on('change', events.eShowHideChk);
    dom.chkParent.on('change', events.eChkParent);
    dom.chkParent.on('click', events.eChkParentClick);
  };
  events = {
    eShowHideChk: function(event) {
      var chkChilds, chkParent, collection, wrapMoreOpt, _this;
      _this = $(this);
      collection = [];
      wrapMoreOpt = _this.parents(st.divMoreOptions);
      chkChilds = $(':checkbox', wrapMoreOpt);
      chkParent = wrapMoreOpt.prev(st.divAccord).find(':checkbox');
      if (!chkParent.is(':checked')) {
        chkParent.attr('checked', true);
      }
      $.each(chkChilds, function(i, elem) {
        if ($(elem).is(':checked')) {
          collection.push(elem);
        }
      });
      if (collection.length === 0) {
        chkParent.attr('checked', false);
      }
    },
    eChkParentClick: function(event) {
      var parentChild, _this;
      _this = $(this);
      parentChild = _this.parent().next();
      if (parentChild.hasClass('open')) {
        event.stopPropagation();
      }
    },
    eChkParent: function(event) {
      var chkChilds, parentChild, _this;
      _this = $(this);
      parentChild = _this.parent().next();
      chkChilds = parentChild.find(':checkbox');
      if (_this.is(':checked')) {
        chkChilds.attr('checked', true);
        _this.attr('disabled', false);
      } else {
        chkChilds.attr('checked', false);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("study_options_ad", function(Sb) {
  var catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    context: '#studyF',
    inputTagName: '_id_nivel_estudio',
    divWrap: '.skill-content',
    divControls1: '.control-group',
    divControls2: '.cgroup-inline',
    classFirstTitle: 'first_title',
    classSecondTitle: 'second_title',
    selTipoEstudio: '_id_nivel_estudio_tipo',
    selTipoCarrera: '_id_tipo_carrera',
    selCarrera: '_id_carrera'
  };
  catchDom = function() {
    dom.context = $(st.context);
  };
  suscribeEvents = function() {
    dom.context.on('change', '[id$="' + st.inputTagName + '"]', events.eChangeStudy);
  };
  events = {
    eChangeStudy: function(e) {
      var value, wrap, _this;
      _this = $(this);
      wrap = _this.parents(st.divWrap).parent();
      value = parseInt($('option:selected', _this).val());
      functions.fnDisabledInputs({
        disabled: false,
        arr: [st.selTipoEstudio, st.selTipoCarrera, st.selCarrera]
      }, wrap);
      if (value === 0) {
        $('[name$="' + st.selTipoEstudio + '"]', wrap).attr('disabled', false).find('option:first').siblings().remove();
      } else if (value === 1 || value === 2 || value === 3) {
        functions.fnDisabledInputs({
          disabled: true,
          arr: [st.selTipoEstudio, st.selTipoCarrera, st.selCarrera]
        }, wrap);
        $('[name$="' + st.selCarrera + '"]', wrap).removeClass(st.classSecondTitle);
      } else {
        $('[name$="' + st.selCarrera + '"]', wrap).addClass(st.classSecondTitle);
      }
    }
  };
  functions = {
    fnDisabledInputs: function(obj, wrap) {
      $.each(obj.arr, function(i, elem) {
        var input;
        input = $('[name$="' + elem + '"]', wrap);
        if (obj.disabled) {
          functions.fnCleanInput(input);
        }
        input.attr('disabled', obj.disabled);
      });
    },
    fnCleanInput: function($elem) {
      var tagName;
      tagName = $elem[0].tagName;
      if (tagName === 'INPUT') {
        $elem.val('');
      } else if (tagName === 'SELECT') {
        $('option:eq(0)', $elem).attr('selected', true);
      }
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("validate_form_ad", function(Sb) {
  var catchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    context: '#frmPublishAd',
    btnShowQuestion: '#btnQuestions',
    questionWrap: '#questionWrap',
    wrapLanguaje: '#languagesF',
    wrapPrograms: '#programsF',
    ulSkillWrap: '.wrap-skills',
    classFirstTitle: '.first_title',
    divSkilltitle: '.skill-title',
    spanResponse: '#spanResponse',
    btnSave: '#btnSave'
  };
  catchDom = function() {
    dom.context = $(st.context);
    dom.spanResponse = $(st.spanResponse, dom.context);
    dom.wrapPrograms = $(st.wrapPrograms);
    dom.wrapLanguaje = $(st.wrapLanguaje);
    dom.btnShowQuestion = $(st.btnShowQuestion, dom.context);
    dom.questionWrap = $(st.questionWrap, dom.context);
    dom.btnSave = $(st.btnSave, dom.context);
    dom.spanPrograms = $('.response', st.wrapPrograms);
    dom.spanLanguaje = $('.response', st.wrapLanguaje);
  };
  suscribeEvents = function() {
    dom.btnShowQuestion.on('click', events.showWrapQuestion);
    dom.context.on('submit', events.validateForm);
  };
  events = {
    showWrapQuestion: function(e) {
      var _this;
      _this = $(this);
      _this.hide();
      dom.questionWrap.slideDown();
    },
    validateForm: function(e) {
      var flagLang, flagProg, lastSkill, skillWrap, value;
      flagLang = true;
      flagProg = true;
      skillWrap = dom.wrapLanguaje.find(st.ulSkillWrap + '> li');
      lastSkill = skillWrap.last();
      value = $.trim($(st.classFirstTitle, lastSkill).find('option:selected').text());
      if (!functions.fnRepeatTitles(lastSkill.siblings(), value)) {
        dom.spanLanguaje.removeClass('hide').text('No se permiten campos repetidos');
        $('html, body').animate({
          scrollTop: dom.wrapLanguaje.offset().top - 40
        }, 400);
        flagLang = false;
      }
      skillWrap = dom.wrapPrograms.find(st.ulSkillWrap + '> li');
      lastSkill = skillWrap.last();
      value = $.trim($(st.classFirstTitle, lastSkill).find('option:selected').text());
      if (!functions.fnRepeatTitles(lastSkill.siblings(), value)) {
        dom.spanPrograms.removeClass('hide').text('No se permiten campos repetidos');
        $('html, body').animate({
          scrollTop: dom.wrapPrograms.offset().top - 40
        }, 400);
        flagProg = false;
      }
      if (!flagLang || !flagProg) {
        dom.spanResponse.removeClass('hide').text('No se permiten campos repetidos en idiomas y/o programas');
        return false;
      }
    }
  };
  functions = {
    fnRepeatTitles: function($listLi, valueTitle) {
      var flag;
      flag = true;
      $.each($listLi, function(i, elem) {
        var title;
        title = $.trim($(elem).find(st.divSkilltitle).find('li:eq(1)').find('b:eq(0)').text());
        if (title === valueTitle) {
          flag = false;
        }
      });
      return flag;
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);

yOSON.AppCore.addModule("validate_register_contact", function(Sb) {
  var afterCatchDom, catchDom, dom, initialize, st;
  dom = {};
  st = {
    frm: '#frmRegisterContract'
  };
  catchDom = function() {
    dom.frm = $(st.frm);
  };
  afterCatchDom = function() {
    dom.frm.parsley();
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
  };
  return {
    init: initialize
  };
}, []);
/**
Modulo para realizar el autocomplete de busqueda de la seccion buscar
@class btn_actions_preferencial_ad
@main postulante
@author Carlos Huamani
 */
yOSON.AppCore.addModule("autocomplete_tags", function(Sb) {
  var afterCatchDom, catchDom, dom, initialize, st;
  dom = {};
  st = {
    fWordRS: '#fWordRS',
    tags: '#containerTags',
    url: '/registro/filtrar-aptitudes',
    maxTags: 10,
    closeTag : '.close_tag',
    itemTag : '.search_item_tag' 
  };
  catchDom = function() {
    dom.fWordRS = $(st.fWordRS);
    dom.tags = $(st.tags);
  };
  afterCatchDom = function() {
    functions.initTooltip();
    dom.fWordRS.autocomplete({
      source: function(request, response){
        $.ajax({
          url: st.url,
          type: 'POST',
          data: {'value':request.term},
          success: function(data){
            data = $.parseJSON(data);
            results = $.map(data['items'], function(item) {
              return {value: item.mostrar, data: item.id};
            });
            response(results);
          }
        });
      },
      minLength: 3,
      select: function(event,  ui){
        if($(st.itemTag).length >= st.maxTags){
          return false;
        }
        suggestion = ui.item;
        validation = true;
        dom.fWordRS.val('');
        $(st.itemTag).each(function(index, value){
          var n, id;
          n = $(value).data('id');
          id = parseInt(n);
          if(id==suggestion.data){
            validation = false;
            return false;
          }
        });
        if(validation){
          html = "<div class='search_item_tag' title='" + suggestion.label + "' data-id='" + suggestion.data + "'><input type='hidden' name='tags[]' value='" + suggestion.data + "'><label>" + suggestion.value + "</label><span class='close_tag'>X</span></div>";
          dom.tags.append(html);
          functions.initTooltip();
        }
        return false;
      }
    });
  };
  suscribeEvents = function(){
    dom.tags.on('click', st.closeTag, events.eCloseTag)
  };
  events = {
    eCloseTag : function(e){
      $(this).parent().remove();
    }
  };
  functions = {
    initTooltip : function(){
      $(st.itemTag).tooltip();
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);
/**
Modulo aplicar descuento o agregar costos al pagar un aviso
@class manipulate_payment_ad
@main postulante
@author Victor Sandoval
 */
yOSON.AppCore.addModule("manipulate_payment_ad", function(Sb) {
  var catchDom, afterCatchDom, dom, events, functions, initialize, st, suscribeEvents;
  dom = {};
  st = {
    selDiscount: '#selDiscount',
    priceTotP4: '#priceTotP4',
    contratoN: '#contratoN',
    checkEmpP4: '.checkEmpP4',
    valueDefault: null
  };
  catchDom = function() {
    dom.selDiscount = $(st.selDiscount);
    dom.priceTotP4 = $(st.priceTotP4);
    dom.checkEmpP4 = $(st.checkEmpP4);
    dom.contratoN = $(st.contratoN);
  };
  afterCatchDom = function() {
    st.valueDefault = parseFloat(dom.priceTotP4.data("number"))
  };
  suscribeEvents = function() {
    dom.selDiscount.on('change', events.eApplyDiscount);
    dom.checkEmpP4.on('change', events.eApplyCosts);
  };
  events = {
    eApplyDiscount: function(event) {
      functions.fnApplyDisccount();
    },
    eApplyCosts: function(event){
      var _this = $(this),
      cantidad = parseFloat(_this.attr('rel'));

      if(_this.is(':checked')){
        st.valueDefault = st.valueDefault + cantidad;
      }else{
        st.valueDefault = st.valueDefault - cantidad;
      }

      if(dom.selDiscount.length != 0){
        functions.fnApplyDisccount();
      }else{
        functions.fnSetDisccount(st.valueDefault.toFixed(2));
      }
    }
  };
  functions = {
    fnApplyDisccount: function(){
      var valuePercent, valueAfterPercent;
      valuePercent = (100 - parseFloat(dom.selDiscount.val())) / 100;
      valueAfterPercent = (st.valueDefault * valuePercent).toFixed(2);
      valueAfterPercent = valueAfterPercent.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
      dom.contratoN.attr("precio", valueAfterPercent);
      functions.fnSetDisccount(valueAfterPercent);
    },
    fnSetDisccount: function(value){
      dom.priceTotP4.text(value);
    }
  };
  initialize = function(oP) {
    $.extend(st, oP);
    catchDom();
    afterCatchDom();
    suscribeEvents();
  };
  return {
    init: initialize
  };
}, []);
// ---
// generated by coffee-script 1.9.0