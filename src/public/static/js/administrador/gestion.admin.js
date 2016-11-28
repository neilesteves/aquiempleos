$(function () {
    var posY = 0; //autocomplete ajax
    var gestion = {
        avisos: function () {
            $('#frmBuscar_avisos').submit(function (e) {
                e.preventDefault();
                var inputSuggest = $('#fRazonSocial'),
                        limitChar = parseInt(inputSuggest.attr('data-param')),
                        spanSuggest = $('#resSuggest');
                spanSuggest.removeClass('good bad def')
                        .text('');
                if ($.trim(inputSuggest.val()) == $.trim(inputSuggest.attr('data-auto'))) {
                    if ($.trim(inputSuggest.val()).length >= limitChar) {
                        urlredirect();
                    } else {
                        spanSuggest = $('#resSuggest');
                        spanSuggest.removeClass('good bad def')
                                .text('');
                        if ($.trim(inputSuggest.val()) == ' ' ||
                                $.trim(inputSuggest.val()) == '') {
                            urlredirect();
                        }
                    }
                } else {
                    spanSuggest.removeClass('good def').addClass('bad')
                            .text('Debe seleccionar una empresa existente de la lista de empresas encontradas.');
                    if ($.trim(inputSuggest.val()) == ' ' ||
                            $.trim(inputSuggest.val()) == '') {
                        urlredirect();
                    }
                }
                //arma URLS
                function urlredirect() {
                    var s, trail = '',
                            base_url = urls.siteUrl + '/admin/gestion/avisos';
                    s = $('#fUrlId').val();
                    if (s != '')
                        trail += '/url_id/' + s;
                    s = $('#token').val();
                    if (s != '')
                        trail += '/token/' + s;
                    s = $('#fRazonSocial').attr('data-id');
                    if (s != '' && s != undefined)
                        trail += '/razonsocial/' + s;
                    s = $('#fRuc').val();
                    if (s != '')
                        trail += '/num_ruc/' + s;
                    s = $('#fCodigoAdecsys').val();
                    if (s != '')
                        trail += '/cod_ade/' + s;
                    if ($('#optCheckP3').prop('checked'))
                        trail += '/tipobusq/1';
                    s = $('#fBirthDate').val();
                    if ((s.split('/')[0] == '0' && s.split('/')[1] == '0' && s.split('/')[2] == '0') || s == '') {
                        valFlag = '0';
                    } else {
                        valFlag = '1';
                    }
                    if (valFlag != '0') {
                        trail += '/fh_pub/' + String(s).replace(/\//g, '-');
                    }
                    window.location = base_url + trail;
                }
            });
        },
         avisosCallcenter: function () {
            $('#frmBuscar_avisos_call').submit(function (e) {
                e.preventDefault();
        
                 urlredirect();
                //arma URLS
                function urlredirect() {
                    var s, trail = '',
                            base_url = urls.siteUrl + '/admin/gestion/avisos-callcenter';
                    s = $('#tipo_destaque').val();
                    if (s != '')
                        trail += '/tipo_destaque/' + s;
                    s = $('#tipo_impreso').val();
                    if (s != '')
                        trail += '/tipo_impreso/' + s;
                    s = $('#estado').val();
                    if (s != '' && s != undefined)
                        trail += '/estado/' + s;
                    s = $('#token').val();
                    if (s != '')
                        trail += '/token/' + s;                  
                    s = $('#fh_pub').val();
                    if ((s.split('/')[0] == '0' && s.split('/')[1] == '0' && s.split('/')[2] == '0') || s == '') {
                        valFlag = '0';
                    } else {
                        valFlag = '1';
                    }
                     if (s != '')
                        trail += '/fh_pub/' + String(s).replace(/\//g, '-');
                     s = $('#fh_pub_fin').val();
                    if ((s.split('/')[0] == '0' && s.split('/')[1] == '0' && s.split('/')[2] == '0') || s == '') {
                        valFlag = '0';
                    } else {
                        valFlag = '1';
                    }
                    if (valFlag != '0') {
                        trail += '/fh_pub_fin/' + String(s).replace(/\//g, '-');
                    }
                    window.location = base_url + trail;
                }
            });
        },
        destaqueOro: function () {
            $('[name="destaque"]').click(function (e) {
                  e.preventDefault();
                var idaviso=$(this).attr('rel');
               // alert(idaviso);
                $.ajax({
                    url: "/admin/gestion/destacar",
                       'type': 'POST',
                    data: {
                                'idAviso': $(this).attr('rel'),
                                'tok': $(this).attr('data-token'),
                                'tipo_destaque': $(this).attr('data-tipo')
                            },
                    dataType: "html",
                    success: function (res) {
                        var jsonObj = JSON.parse(res);
                      //  console(res.status);
                        if (jsonObj.status) {
                       //     window.location = siteUrl + '';
                         location.reload();
                                //window.location =  $(this).attr('href') ;
                        }
                    }
                });
//                alert("Handler for .click() called.");
            });
        },
        postulantes: function () {
            $('#frmBuscar_postulantes').submit(function (e) {
                var s, trail = '',
                        base_url = urls.siteUrl + '/admin/gestion/postulantes';
                s = $('#fEmail').val();
                if (s != '')
                    trail += '/email/' + s;
                s = $('#fNames').val();
                if (s != '')
                    trail += '/nombres/' + s;
                s = $('#fSurname').val();
                if (s != '')
                    trail += '/apellidos/' + s;
                s = $('#fNDoc').val();
                if (s != '')
                    trail += '/num_doc/' + s;
                window.location = base_url + trail;
                e.preventDefault();
            });
        },
        empresas: function () {
            $('#frmBuscar_empresas').submit(function (e) {
                var s, trail = '',
                        base_url = urls.siteUrl + '/admin/gestion/empresas';
                s = $('#fRazonSocial').val();
                if (s != '')
                    trail += '/razonsocial/' + s;
                s = $('#fRuc2').val();
                if (s != '')
                    trail += '/num_ruc/' + s;
                window.location = base_url + trail;
                e.preventDefault();
            });
        },
        avisosPreferenciales: function () {
            $('#frmBuscar_avisosPreferenciales').submit(function (e) {
                e.preventDefault();
                var s, trail = '',
                        base_url = urls.siteUrl + '/admin/gestion/avisos-preferenciales',
                        valFlag = '';

                s = $('#fNomPuesto').val();
                if (s != '')
                    trail += '/nom_puesto/' + s;
                s = $('#fRuc').val();
                if (s != '')
                    trail += '/num_ruc/' + s;

                s = $('#fBirthDate').val();
                for (var i = 0; i <= s.split('/').length; i++) {
                    if (s.split('/')[i] == '0' || s.split('/')[i] == '') {
                        valFlag = '0';
                        break;
                    } else {
                        valFlag = '1';
                    }
                }
                if (valFlag != '0') {
                    trail += '/fh_pub/' + String(s).replace(/\//g, '-');
                }

                s = $('#fOrigen').val();
                if (s != '')
                    trail += '/origen/' + s;
                s = $('#fCodigoAdecsys').val();
                if (s != '')
                    trail += '/cod_ade/' + s;
                if ($('#optCheckP3').prop('checked'))
                    trail += '/tipobusq/1';
                window.location = base_url + trail;
            });
        },
        formReset: function (a) {
            $(a).click(function (e) {
                e.preventDefault();

                //Postulante
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_postulantes') {
                    $('#frmBuscar_postulantes input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/postulantes';
                }
                //Avisos
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_avisos') {
                    $('#frmBuscar_avisos input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/avisos';
                }
                // aviso callcenter
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_avisos_call') {
                    $('#frmBuscar_avisos input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/avisos-callcenter';
                }
                //Empresa
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_empresas') {
                    $('#frmBuscar_empresas input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/empresas';
                }
                //Avisos Preferenciales
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_avisosPreferenciales') {
                    $('#frmBuscar_avisosPreferenciales input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/avisos-preferenciales';
                }
                //Call Center
                if ($($(a).parent().parent()).attr('id') == 'buscarEmailCliente') {
                    $('#buscarEmailCliente input[type="text"]').val('');
                    window.location = urls.siteUrl + '/admin/gestion/callcenter';
                }

            });
        },
        replaceNbsp: function (field) {
            return $(field).each(function () {
                var t = $(this),
                        isShift = false;
                t.bind('keypress paste', function (e) {
                    setTimeout(function () {
                        var value = t.val();
                        var newValue = value.replace(/\%/g, '');
                        t.val(newValue);
                    }, 0);
                });
            });
        },
        valueCheck: function (a) {
            var iptC = $('#fCodigoAdecsys');
            if ($(a).is(':checked')) {
                $('#fCodigoAdecsys').removeAttr('disabled', 'disabled');
                iptC.removeClass('iptDisabled');

                //Disable
                $('#fUrlId').attr('disabled', 'disabled');
                $('#fUrlId').val('');
                $('#fUrlId').addClass('iptDisabled');

                $('#fRazonSocial').attr('disabled', 'disabled');
                $('#fRazonSocial').val('');
                $('#fRazonSocial').addClass('iptDisabled');

                $('#fNomPuesto').attr('disabled', 'disabled');
                $('#fNomPuesto').val('');
                $('#fNomPuesto').addClass('iptDisabled');

                $('#fRuc').attr('disabled', 'disabled');
                $('#fRuc').val('');
                $('#fRuc').addClass('iptDisabled');

                $('#dayjFunctions').attr('disabled', 'disabled');
                $('#dayjFunctions').val(0);
                $('#dayjFunctions').addClass('iptDisabled');

                $('#monthjFunctions').attr('disabled', 'disabled');
                $('#monthjFunctions').val('0');
                $('#monthjFunctions').addClass('iptDisabled');

                $('#yearjFunctions').attr('disabled', 'disabled');
                $('#yearjFunctions').val('0');
                $('#yearjFunctions').addClass('iptDisabled');

                $('#fBirthDate').attr('disabled', 'disabled');
                $('#fBirthDate').val('');
                $('#fBirthDate').addClass('iptDisabled');

                $('#fOrigen').attr('disabled', 'disabled');

            } else {
                iptC.addClass('iptDisabled');
            }
            $(a).bind('change', function () {
                var check = $(this);
                var inputCheck = $('#fCodigoAdecsys');
                if (check.is(':checked')) {
                    inputCheck.removeAttr('disabled');
                    inputCheck.removeClass('iptDisabled');

                    //Disable
                    $('#fUrlId').attr('disabled', 'disabled');
                    $('#fUrlId').val('');
                    $('#fUrlId').addClass('iptDisabled');

                    $('#fRazonSocial').attr('disabled', 'disabled');
                    $('#fRazonSocial').val('');
                    $('#fRazonSocial').addClass('iptDisabled');

                    $('#fNomPuesto').attr('disabled', 'disabled');
                    $('#fNomPuesto').val('');
                    $('#fNomPuesto').addClass('iptDisabled');

                    $('#fRuc').attr('disabled', 'disabled');
                    $('#fRuc').val('');
                    $('#fRuc').addClass('iptDisabled');

                    $('#dayjFunctions').attr('disabled', 'disabled');
                    $('#dayjFunctions').val(0);
                    $('#dayjFunctions').addClass('iptDisabled');

                    $('#monthjFunctions').attr('disabled', 'disabled');
                    $('#monthjFunctions').val('0');
                    $('#monthjFunctions').addClass('iptDisabled');

                    $('#yearjFunctions').attr('disabled', 'disabled');
                    $('#yearjFunctions').val('0');
                    $('#yearjFunctions').addClass('iptDisabled');

                    $('#fBirthDate').attr('disabled', 'disabled');
                    $('#fBirthDate').val('');
                    $('#fBirthDate').addClass('iptDisabled');

                    $('#fOrigen').attr('disabled', 'disabled');

                } else {
                    inputCheck.attr('disabled', 'disabled');
                    inputCheck.val('');
                    inputCheck.addClass('iptDisabled');

                    //Saca Disable
                    $('#fUrlId').removeAttr('disabled');
                    $('#fUrlId').removeClass('iptDisabled');

                    $('#fRazonSocial').removeAttr('disabled');
                    $('#fRazonSocial').removeClass('iptDisabled');

                    $('#fNomPuesto').removeAttr('disabled');
                    $('#fNomPuesto').removeClass('iptDisabled');

                    $('#fRuc').removeAttr('disabled');
                    $('#fRuc').removeClass('iptDisabled');

                    $('#dayjFunctions').removeAttr('disabled');
                    $('#dayjFunctions').removeClass('iptDisabled');

                    $('#monthjFunctions').removeAttr('disabled');
                    $('#monthjFunctions').removeClass('iptDisabled');

                    $('#yearjFunctions').removeAttr('disabled');
                    $('#yearjFunctions').removeClass('iptDisabled');

                    $('#fBirthDate').removeAttr('disabled');
                    $('#fBirthDate').removeClass('iptDisabled');

                    $('#fOrigen').removeAttr('disabled');
                    $('#fOrigen').removeClass('iptDisabled');

                }
            });
        },
        validRuc: function (ruc) {
            //Num Tipo de documento
            return $(ruc).each(function () {
                var t = $(this),
                        isShift = false;
                t.keypress(function (e) {
                    var key = e.keyCode || e.charCode || e.which || window.e;
                    if (key == 16)
                        isShift = true;
                    return (key == 8 || key == 9 || key == 32 ||
                            key == 37 || key == 39 ||
                            (key == 48 && isShift == false) ||
                            (key == 49 && isShift == false) ||
                            (key == 50 && isShift == false) ||
                            (key == 51 && isShift == false) ||
                            (key == 52 && isShift == false) ||
                            (key == 53 && isShift == false) ||
                            (key == 54 && isShift == false) ||
                            (key == 55 && isShift == false) ||
                            (key == 56 && isShift == false) ||
                            (key == 57 && isShift == false)
                            );
                });
                t.bind('paste', function () {
                    setTimeout(function () {
                        var value = t.val();
                        var newValue = value.replace(/[^0-9]/g, '');
                        t.val(newValue);
                    }, 0);
                });
            });
        },
        maxLenghtN: function (trigger) {
            var select = $(trigger),
                    input = select.next();
            select.bind('change', function () {
                var t = $(this),
                        string = (t.val()).split('#'),
                        numMax = string[1],
                        inputVal = input.val();
                input.removeAttr('maxlength').attr('maxlength', numMax);
                input.val(inputVal);
                input.focus();
            });
            input.bind('keyup click blur focus change paste', function (e) {
                var t = $(this),
                        string = (t.siblings('select').val()).split('#'),
                        numMax = parseInt(string[1]),
                        valueArea;
                var key = e.which;
                var length = t.val().length;
                if (length > numMax) {
                    valueArea = t.val().substring(numMax, '');
                    input.val(valueArea);
                }
            });
        },
        debounce: function (callback, delay) {
            var self = this, timeout, _arguments;
            return function () {
                _arguments = Array.prototype.slice.call(arguments, 0),
                        timeout = clearTimeout(timeout, _arguments),
                        timeout = setTimeout(function () {
                            callback.apply(self, _arguments);
                            timeout = 0;
                        }, delay);
                return this;
            };
        },
        autoComResize: function () {
            $(window).resize(function () {
                var cntAutoCompl = $('#cntAutoComplete');
                if (cntAutoCompl.size() > 0 && !cntAutoCompl.hasClass('hide')) {
                    var ulListado = $('#' + cntAutoCompl.children('ul').attr('rel'));
                    cntAutoCompl.css({
                        'left': ulListado.offset().left,
                        'top': ulListado.offset().top + ulListado.innerHeight() + 3
                    });
                }
            });
        },
        acItemClick: function (a) {
            var $a = $(a);
            $a.live('click', function () {
                var $t = $(this),
                        $ul = $t.parent(),
                        resSuggest = $('#resSuggest');
                idIpt = $ul.attr('rel'),
                        thisInput = $('#' + idIpt),
                        thisRel = $.trim($t.attr('rel')),
                        thisText = $.trim($t.text());
                //thisInput.next().val($t.attr('rel'));
                thisInput.val(thisText);
                thisInput.attr('data-auto', thisText);
                thisInput.attr('data-id', thisRel);
                resSuggest.addClass('good').removeClass('def bad')
                        .text('Listo! la empresa coincide con la búsqueda.');
                $ul.remove();
                thisInput.select();
                var cntAutoCompl = $('#cntAutoComplete');
                if (cntAutoCompl) {
                    cntAutoCompl.addClass('hide');
                }
            });
        },
        hover: function (a) {
            var $a = $(a);
            $a.mouseenter(function () {
                $(this).addClass('hover');
            });
            $a.mouseleave(function () {
                $(this).removeClass('hover');
            });
        },
        autoComplete: function (a, b, timeSleep, heightAutoCom) {
            var A = $(a),
                    resSuggest = $('#resSuggest');
            var divAutoCompl = '<div id="cntAutoComplete" class="hide r5"></div>',
                    body = $('body');
            body.append(divAutoCompl);
            A.keyup(gestion.debounce(function (e) {
                //$('#cntAutoComplete').remove();
                var cntAutoCompl = $('#cntAutoComplete');
                var listado;
                var itemsList;
                var t = $(e.target);
                var value = $.trim(t.val()),
                        valueLength = value.length;
                if ($.trim(t.attr('data-auto')) != value) {
                    t.attr('data-id', '');
                } else {
                    //no ejecuta
                }
                resSuggest.removeClass('bad good def')
                        .text('');
                //nivel = (t.parent().prev().find(b)).val();
                var key = e.keyCode || e.charCode || e.which || window.e;
                //keycode Top, Left, Bottom, Right, enter, esc
                if (key != 37 && key != 39 && key != 38 && key != 40 &&
                        key != 13 && key != 27 && key != 9 && key != 16 &&
                        key != 17 && key != 18 && key != 19 && key != 20 &&
                        key != 32 && key != 34 && key != 35 && key != 36 &&
                        key != 44 && key != 45) {
                    // > que el limite
                    if (valueLength >= parseInt(t.attr('data-param'))) {
                        t.addClass('loadingAjax').removeClass('inputAddItem');
                        $.ajax({
                            'url': '/admin/mi-cuenta-empresa/busqueda-general-emp/',
                            'type': 'POST',
                            'dataType': 'JSON',
                            'data': {
                                'model': t.attr('data-model'),
                                'q': value,
                                'nivel': null
                            },
                            'success': function (res) {
                                t.removeClass('loadingAjax').addClass('inputAddItem');
                                //resSuggest.text('Selecciona una empresa de la lista de empesas encontradas para la búsqueda.');
                                if ($.browser.msie && ($.browser.version.split('.')).slice(0, 1) < 7) {
                                    cntAutoCompl.css({
                                        'left': t.offset().left + t.innerWidth() + 3,
                                        'top': t.offset().top
                                    }).addClass('hide');
                                } else {
                                    cntAutoCompl.css({
                                        'left': t.offset().left,
                                        'top': t.offset().top + t.innerHeight() + 3
                                    }).addClass('hide');
                                }
                                $('#acItems').remove();
                                cntAutoCompl.append('<ul id="acItems" rel="' + t.attr('id') + '"></ul>');
                                listado = $('#acItems');
                                for (i in res) {
                                    listado.append('<li rel="' + i + '" class="acItem">' + res[i].label + '</li>');
                                }
                                itemsList = $('#acItems .acItem').size();
                                if (itemsList > 0) {
                                    //Existe Data
                                    resSuggest.removeClass('bad good def')
                                            .text('Selecciona una empresa de la lista de empesas encontradas para la búsqueda.');
                                    cntAutoCompl.removeClass('hide');
                                    if (listado.height() > 200) {
                                        listado.parent().addClass('overflowAuto');
                                        //$('#cntAutoComplete').scroll();
                                        /*$('#cntAutoComplete').animate({
                                         scrollTop:200
                                         }, 'fast');*/
                                    } else {
                                        listado.parent().removeClass('overflowAuto');
                                    }
                                    posY = 0; //reinicio el contador autocomplete ajax
                                } else {
                                    //no hay data
                                    resSuggest.addClass('bad').removeClass('good def').
                                            text('Debes seleccionar una empresa de la lista de empresas para poder realizar la búsqueda.');
                                    cntAutoCompl.addClass('hide');
                                    $('#cntAutoComplete').remove();
                                }
                                //scroll
                                cntAutoCompl.scrollTop(0);
                                //hover
                                gestion.hover('.acItem');
                                gestion.acItemClick('.acItem');
                            },
                            'error': function (res) {
                                resSuggest.addClass('bad').removeClass('good def')
                                        .text('No se encontraron empresas.');
                                //no ejecuta
                            }
                        });
                    } else {
                        t.removeClass('loadingAjax inputAddItem');
                        resSuggest.addClass('bad').removeClass('good def')
                                .text('Ingresa mínimo 3 caracteres para iniciar la búsqueda de empresas.');
                        if (cntAutoCompl) {
                            cntAutoCompl.addClass('hide');
                        }
                        posY = 0; //reinicio el contador autocomplete ajax
                    }
                } else {
                    //no ejecuta
                }
            }, timeSleep)).blur(function () {
                //blur ejecuta
                var dataSuggest = $('#cntAutoComplete'),
                        liDataS = dataSuggest.find('.acItem');
                if (liDataS.size() == 1) {
                    A.val($.trim((liDataS.eq(0)).text()));
                    A.attr('data-auto', $.trim((liDataS.eq(0)).text()));
                    A.attr('data-id', $.trim((liDataS.eq(0)).attr('rel')));
                    resSuggest.addClass('good').removeClass('def bad')
                            .text('Listo! la empresa coincide con la búsqueda.');
                }
                //var cntAutoCompl = $('#cntAutoComplete');
                //cntAutoCompl.addClass('hide');
            });
            //keydown para el teclado
            A.keydown(function (e) {
                var t = $(this),
                        cntAutoCompl = $('#cntAutoComplete'),
                        key = e.keyCode || e.charCode || e.which || window.e;
                //keycode Top, Left, Bottom, Right
                if (!(key != 37 && key != 39 && key != 38 && key != 40)) {
                    if (cntAutoCompl.is(':visible')) {
                        var itemsPosy = cntAutoCompl.find('.acItem');
                        itemsPosSize = itemsPosy.size();

                        //Arriba
                        if (key == 38 && posY > 1) {
                            posY = posY - 1;
                            itemsPosy.removeClass('hover');
                            itemsPosy.eq(posY - 1).addClass('hover');
                            t.val(itemsPosy.eq(posY - 1).text());
                            var thisInput = A,
                                    thisRel = $.trim(itemsPosy.eq(posY - 1).attr('rel')),
                                    thisText = $.trim(itemsPosy.eq(posY - 1).text());
                            thisInput.val(thisText);
                            thisInput.attr('data-auto', thisText);
                            thisInput.attr('data-id', thisRel);
                            resSuggest.addClass('good').removeClass('def bad')
                                    .text('Listo! la empresa coincide con la búsqueda.');
                            //scroll
                            var posScroll = itemsPosy.eq(posY - 1).offset().top - itemsPosy.eq(0).offset().top - 100;
                            if (posScroll >= 0) {
                                cntAutoCompl.animate({
                                    scrollTop: posScroll
                                }, 'fast');
                            }
                        }
                        //Abajo
                        if (key == 40 && posY < itemsPosSize) {
                            posY = posY + 1;
                            itemsPosy.removeClass('hover');
                            itemsPosy.eq(posY - 1).addClass('hover');
                            t.val(itemsPosy.eq(posY - 1).text());
                            var thisInput = A,
                                    thisRel = $.trim(itemsPosy.eq(posY - 1).attr('rel')),
                                    thisText = $.trim(itemsPosy.eq(posY - 1).text());
                            thisInput.val(thisText);
                            thisInput.attr('data-auto', thisText);
                            thisInput.attr('data-id', thisRel);
                            resSuggest.addClass('good').removeClass('def bad')
                                    .text('Listo! la empresa coincide con la búsqueda.');
                            //scroll
                            var posScroll = itemsPosy.eq(posY - 1).offset().top - itemsPosy.eq(0).offset().top - 100;
                            if (posScroll >= 0) {
                                cntAutoCompl.animate({
                                    scrollTop: posScroll
                                }, 'fast');
                            }
                        }
                    }
                }
                //escape y tab
                if (cntAutoCompl.size() > 0 && !cntAutoCompl.hasClass('hide')) {
                    if (key == 27 || key == 8) {
                        //esc
                        cntAutoCompl.addClass('hide');
                    }
                }
            });
            //Close Autocomplete
            $('body').not('#cntAutoComplete').click(function () {
                var cntAutoCompl = $('#cntAutoComplete');
                if (cntAutoCompl.size() > 0 && !cntAutoCompl.hasClass('hide')) {
                    cntAutoCompl.addClass('hide');
                }
            });
        },
    };
    //init
    gestion.avisos();
    gestion.avisosCallcenter();
    gestion.postulantes();
    gestion.empresas();
    gestion.avisosPreferenciales();
    gestion.formReset('#fResetRS');
    gestion.replaceNbsp('.replaceNbsp');
    gestion.valueCheck('#optCheckP3');
    gestion.validRuc('#fRucVal #fRuc');
    gestion.maxLenghtN('select.maxLenghtN');
    gestion.autoComplete('#frmBuscar_avisos #fRazonSocial', '', 350, 200);
    gestion.acItemClick('.acItem');
    gestion.hover('.acItem');
    gestion.autoComResize();
    gestion.destaqueOro();
});