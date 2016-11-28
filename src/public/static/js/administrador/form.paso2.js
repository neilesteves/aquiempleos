/*
 Registro postulante Paso 2
 */
$(function() {
    var msgs = {
        cFormStep2: 'Ingresa tus datos obligatorios para poder continuar.',
        cForm: 'Ingresa tus datos correctamente para poder añadir',
        cBadSubmit: 'Campo requerido.',
        cBadTerminos: 'Acepta las Politicas de Privacidad de Aptitus.com.',
        cBadIdioma: 'No puedes repetir un idioma',
        cBadPrograma: 'No puedes repetir un programa',
        cDef: {
            good: '.',
            bad: 'No parece ser un campo válido.',
            def: 'Ingresa datos correctos'
        },
        cDate: {
            good: '.',
            bad: 'Fecha incorrecta.',
            def: 'Ingresa un año mayor a ' + urls.fMinDate + ' y menor a ' + urls.fYearCurrent,
            sec: {
                exed: 'Incorrecto!. La fecha de Inicio es mayor que la de Fin',
                monthExed: 'Incorrecto!. El mes de Inicio es mayor que el de Fin'
            }
        },
        mDelete: {
            success: '¡Se eliminó satisfactoriamente!',
            error: '¡Se produjo un error al eliminar.!',
            prog: 'Eliminando...',
            def: '¿Está seguro que desea eliminar?',
            est: '¿Estás seguro que deseas eliminar el estudio seleccionado?',
            idi: '¿Estás seguro que deseas eliminar el idioma seleccionado?',
            pro: '¿Estás seguro que deseas eliminar el programa seleccionado?',
            exp: '¿Estás seguro que deseas eliminar la experiencia seleccionada?',
            expe: 'Está seguro que desea eliminar?, también se eliminaran tus referencias relacionadas.',
            refe: 'Estás seguro que deseas eliminar la referencia seleccionada.'
        }
    };
    var vars = {
        rs: '.response',
        okR: 'ready',
        okBlock: 'readyBlock',
        loading: '<div class="loading left"></div>',
        vMinDate: urls.fMinDate,
        vMonthCurrent: urls.fMonthCurrent,
        vYearCurrent: urls.fYearCurrent
    };
    var posY = 0; //autocomplete ajax	
    var formP2 = {
        /**
         * Función para agregar habilidades como estudio, experiencia, idioma, etc
         * @author Victor Sandoval
         * @method addSkills
         */
        addSkills: function() {
            var st = {
                idSection: '#experienceF',
                btn: {
                    id: '#btnExp',
                    text: 'Añadir otra experiencia'
        },
                idBuild: '.wrap-skills',
                chkNoSkill: '.checkInv',
                spanMessage: '.message',
                idManager: 'managerExperiencia_',
                idFieldBlock1: '_otra_empresa',
                idFieldBlock2: '_otro_puesto',
                template: '#expTemplate',
                templateHTML : null,
                idPref: 'f',
                idExpRegMa: /managerExperiencia_blank/ig,
                idExpRegF: /fblank_/ig,
                textDelete: msgs.mDelete.exp,
                messageWrong: msgs.cForm,
                changeTitles : function(){}
            },
            dom = {},
            j = 0,
            catchDom= function(){
                dom.idSection   = $(st.idSection);
                dom.btn         = $(st.btn.id);
                dom.idBuild     = $(st.idBuild, st.idSection);
                dom.chkNoSkill  = $(st.chkNoSkill, st.idSection);
                dom.spanMessage = $(st.spanMessage, st.idSection);
                dom.template    = $(st.template);
            },
            afterCatchDom = function(){
                st.templateHTML = dom.template.html();
                j = parseInt(dom.idSection.attr('rel'));
            },
            suscribeEvents = function(){
                dom.btn.on('click', onAddSkill);
            },
            onAddSkill = function(event){
                var nameLabel1, nameLabel2, separate, html;

                if(!formP2.okAddSkill(st)) return false;
                
                    // si hay repetidos
                if (formP2.reviewRepeat(st.idSection)) {
                    dom.spanMessage.removeClass('hide').text(st.messageWrong);
                } else {
                    dom.chkNoSkill.slideUp('fast');
                    dom.spanMessage.addClass('hide').text('');
                        j++;

                    if($('.skillN', st.idSection).size() > 0) dom.btn.text(st.btn.text);
                    // capturo el template y lo preparo para otro registro
                    html = st.templateHTML.replace(st.idExpRegMa, st.idManager + j);
                    html = html.replace(st.idExpRegF, st.idPref + j + '_');
                    dom.idBuild.append(html);

                    // Modificacion de titulos que se agregan en exp, estudios, idiomas, etc.
                    titles = st.changeTitles(st,j);
                        nameLabel1 = titles.nameLabel1;
                        nameLabel2 = titles.nameLabel2;
                        separate = titles.separate;


                    $('#' + st.idPref + (j - 1) + '_expTDiv').fadeIn('medium').html('' +
                    '<ul class="row">' +
                        '<li class="countN">' + j + '. </li>' +
                        '<li class="wJob">' +
                                '<span class="jobReg bold">' + nameLabel2 + '</span> ' + separate + ' ' +
                                '<span class="eJob bold">' + nameLabel1 + '</span>' +
                        '</li>' +
                        '<li class="last"><a href="#Editar" class="action-icons edit" title="Editar"></a>' +
                        '<a  href="#winAlert" class="action-icons delete winAlertM" title="Eliminar" ajax="Ajax" rel="#' + st.idPref + (j - 1) + '_expeN" rol="#winAlert"' +
                        'data-html="' + st.textDelete + '"></a></li>' +
                    '</ul>');

                    $('.fSkill', st.idSection).not('#' + st.idPref + (j) + '_fSkill').addClass('hide');

                        // reusando metodos
                    appendMethods();
                }
                event.preventDefault();
            },
            appendMethods = function(){
                        formP2.clearField('input.clearH');
                        formP2.charArea('.msgTask', '.numCnt', urls.cantdescExp);
                        formP2.fStudyReq('.selectEstR');
                        formP2.fSelectMesFin();
                        formP2.fSelectAnioFin();
                        formP2.inputOffSelect('input.iptOffSelect');
                        formP2.deleteMesLoad();
                        $('.autoComplete').unbind();
                        formP2.autoComplete('.autoComplete', '.nivelEstudio', 350, 200);
                        $('.autoCompleteEst').unbind();
                    formP2.autoComplete('.autoCompleteEst', '.nivelPuesto', 350, 200);
                    aptitusMethods.validateAll().init({
                        inputTag: '.fCos',
                        type    : 'decimal'
                    });
                    aptitusMethods.validateAll().init({
                        inputTag: '.onlyNum',
                        type    : 'number'
                    });
                        //enumerar
                oBlockD = $('#' + st.idPref + (j - 1) + '_expeN');
                        formP2.enumeracionBlocks(oBlockD);
                        //enumera block
                formP2.enumeracionSkills(st.idSection);
            },
            initialize = function(oP) {
                $.extend(st, oP);
                catchDom();
                afterCatchDom();
                suscribeEvents();
            };
            return{
                init: initialize
            };
        },
        /**
         * Función para validar habilidades como estudio, experiencia, idioma, etc
         * @author Victor Sandoval
         * @method okAddSkill
         */
        okAddSkill: function(opts) {
            var skillWrap = $('.skillN', opts.idSection),
                cantReady = '',
                cantReq = '';

            if (skillWrap.size > 1) {
                cantReady = $('.skillN:not(:last-child) .ready', opts.idSection).size();
                cantReq = $('.skillN:not(:last-child) .require', opts.idSection).size();
            } else {
                cantReady = $('.skillN .ready', opts.idSection).size();
                cantReq = $('.skillN .require', opts.idSection).size();
                }

            if (cantReady != cantReq){
                $(opts.spanMessage, opts.idSection).removeClass('hide').text(msgs.cForm);
                return false;
            }else{
                return true;
            }
        },
        /**
         * Función para eliminar habilidades como estudio, experiencia, idioma, etc
         * @author Victor Sandoval
         * @method removeSkills
         */
        removeSkills: function() {
            var dom = {},
            st = {
                context : null,
                modal   : '#winAlert',
                mask    : '#mask',
                aShowM  : '.winAlertM',
                btnYes  : '.yesCM',
                btnClose: '.closeWM',
                boxMessage : '.box-message'
            },
            catchDom = function() {
                dom.modal = $(st.modal);
                dom.mask = $(st.mask);
                dom.btnYes = $(st.btnYes, st.modal);
                dom.btnClose = $(st.btnClose, st.modal);
            },
            suscribeEvents = function(){
                dom.btnYes.on('click', onCloseModal);
                $(document).on('click', '.winAlertM',onShowModal);
            },
            onShowModal = function(e){
                e.preventDefault();
                var _this = $(this),
                    winAlert = _this.attr('rol');

                $(winAlert).attr({
                    'rel': _this.attr('rel'),
                    'rol': _this.parents('.skillN').attr('rol')
            });

                dom.mask.css({'height': $(document).height()}).fadeTo('fast', 0.50);
                $(winAlert).fadeIn('fast').find('div.msjReemplazo').html(_this.data('html'));
        },
            onCloseModal = function(e){
                var _this = $(this),
                    blockDelete = dom.modal.attr('rel'),
                    divBlock = $(blockDelete),
                    blockSect = '#' + ( divBlock.parents('div.feildset').attr('id') || divBlock.parents('form').attr('id')),
                    blockMessage = $('.box-message', blockSect),
                    updateTextBtn = function (){
                        var numBlocks = $('.skillN', blockSect).size(),
                            btn = $('.wrap-btn .btn', blockSect),
                            textDefault = btn.data('default');

                        if(numBlocks < 2){
                            btn.text(textDefault);
                            $('.checkInv',blockSect).fadeIn();
                            $('.fSkill', blockSect).show();
                        }
                    },
                    showMessage = function(isGood, res){
                        var msn = (isGood) ? msgs.mDelete.success : msgs.mDelete.error;
                        if(typeof(res) === undefined) $('#myAccount').attr('data-hash', res.csrfhash);
                        divBlock.fadeOut(function(){
                            divBlock.detach();
                            blockMessage.text(msn);
                            setTimeout(function() {
                                blockMessage.fadeOut('slow');
                            }, 1000);
                            updateTextBtn();
                        });
                    },
                    saveAjax = function(csrfHash) {
                        $.ajax({
                            'url': urls.ajax,
                            'type': 'POST',
                            'dataType': 'JSON',
                            'data': {
                                'id': expId,
                                'idPost': idPost
                            },
                            'success': function(res) {
                                if (res.status == 'ok'){
                                    showMessage(true, res);
                                }else{
                                    showMessage(false, res);
                                }
                            },
                            'error': function(res) {
                                blockMessage.text(msgs.mDelete.error);
                            }
                        });
                    };

                

                if ($('body').is('#myAccount')) { // Si esta en Edicion Postulante
                    var expId = $.trim(dom.modal.attr('rol'));
                    var idPost = $('.skillN', blockSect).attr('rel');
                    blockMessage.text(msgs.mDelete.prog).fadeIn('slow');

                    //Cuando es -1 es porq no está en la BD y se agregó recientemente
                    if (expId == '-1') {
                        showMessage(true);
                    } else {
                        $.ajax({
                            url: '/registro/obtener-token/',
                            type: 'POST',
                            dataType: 'json',
                            data: {csrfhash: $('body').attr('data-hash')},
                            success: function(result) {
                                saveAjax(result);
                    }
                        });
                    }
                } else { // Esta en registro Postulante
                    divBlock.fadeOut('fast', function() {
                        divBlock.detach();
                        formP2.enumeracionSkills(blockSect); //enumeracion block
                        updateTextBtn();
                    });
                }
                formP2.enumeracionBlocks(divBlock);// Enumeracion
                dom.btnClose.trigger('click');
                e.preventDefault();
            },
            initialize = function(oP) {
                $.extend(st, oP);
                catchDom();
                suscribeEvents();
            };
            return {
                init: initialize
            };
        },
        /**
         * Función para editar habilidades como estudio, experiencia, idioma, etc
         * @author Victor Sandoval
         * @method editSkills
         */
        editSkills: function() {
            $(document).on('click','.edit',function(e) {
                e.preventDefault();
                var _this = $(this),
                    divfSkill = $('.fSkill',_this.parents('.skillN'));

                if (divfSkill.is(':visible')) {
                    divfSkill.removeClass('active').slideUp('fast');
                } else {
                    divfSkill.addClass('active').slideDown('fast');
                }
            });
        },
        reviewRepeat: function(sectionAdd) {
            //idiomas
            if (sectionAdd == '#languagesF' || sectionAdd == '#programsF') {
                var arrCantidad = $(sectionAdd + ' .skillN'),
                        numCantidad = arrCantidad.size(),
                        arrReq1 = $(sectionAdd + ' .field1Req'),
                        booleanFlag, flagRepeat1;
                if (numCantidad > 1) {
                    var lastReq1 = arrReq1.eq(numCantidad - 1).val(),
                    loop1Repeat = function(arrReq1, lastReq1, numCantidad) {
                        for (var k = (numCantidad - 2); k >= 0; k--) {

                            var dataCheck = (arrReq1.eq(k).val() == lastReq1);
                            if (dataCheck) {
                                booleanFlag = true;
                                break;
                            } else {
                                booleanFlag = false;
                                continue;
                            }
                        }
                        return booleanFlag;
                    };
                    flagRepeat1 = loop1Repeat(arrReq1, lastReq1, numCantidad);
                }
            }
            return flagRepeat1;
        },
        /**
         * Metodo para validar si un input es requerido
         * @author Review by: Victor Sandoval
         * @method inputReq
         */
        inputReq: function(input, good, bad) {
            var validar = function() {
                var $this = $(this),
                    value = $this.val(),
                    message = $this.next(vars.rs);
                if ($.trim(value) !== '' && value.length > 0) {
                    $this.addClass(vars.okR);
                    message.addClass('good').removeClass('bad def').text(good);
                } else {
                    $this.removeClass(vars.okR);
                    message.addClass('bad').removeClass('good def').text("");//bad
                }
            };

            $(document).on('blur keypress',input ,validar);
        },
        selectReq: function(a, good, bad, def) {
            var trigger = $(a);
            $(document).on('change', a ,function() {
                var t = $(this),
                        r = t.next(vars.rs);
                if (t.val() === '-1' || t.val() === 'none') {
                    t.removeClass(vars.okR);
                    r.removeClass('good bad').addClass('def').text(def);
                } else {
                    r.addClass('good').removeClass('bad def').text(good);
                    t.addClass(vars.okR);
                }
            });
        },
        reviewSelectRepeat: function(sectionAdd, t) {
            //idiomas	
            sectionAdd = '#' + sectionAdd;
            if (sectionAdd == '#sectionIdi') {
                if (t.hasClass('field1Req')) {
                    var arrCantidad = $('#languagesF .skillN'),
                            numCantidad = arrCantidad.size(),
                            arrReq1 = $('#languagesF .field1Req'),
                            booleanFlag, flagRepeat1, tLoop = t;
                    if (numCantidad > 1) {
                        var lastReq1 = t.val();
                        function loop1Repeat(arrReq1, lastReq1, numCantidad) {
                            for (var k = (numCantidad - 1); k >= 0; k--) {
                                var dataCheck = (arrReq1.eq(k).val() == lastReq1);
                                if (dataCheck) {
                                    booleanFlag = true;
                                    break;
                                } else {
                                    booleanFlag = false;
                                    continue;
                                }
                            }
                            return booleanFlag;
                        }
                        flagRepeat1 = loop1Repeat(arrReq1, lastReq1, numCantidad);
                    }
                }
            }
            return flagRepeat1;
        },
        charArea: function(area, num, chars) {
            var trigger = $(area),
                    arrSize = trigger.size() - 2,
                    arrRel;
            for (var i = 0; i <= arrSize; i++) {
                dataRe = trigger.eq(i).next().find('.numCnt');
                dataRe.text(dataRe.attr('rel') + ' ');
            }
            trigger.bind('keyup click blur focus change paste', function(e) {
                var t = $(this),
                        id = t.parent().attr('id'),
                        prefSpl = id.split('_'),
                        pref = prefSpl[0],
                        countN = t.next().children(num),
                        valueArea;
                countN.html(chars + ' ');
                var key = e.keyCode || e.charCode || e.which || window.e;
                var length = t.val().length;
                countN.html((chars - length) + ' ');
                if (length > chars) {
                    valueArea = t.val().substring(chars, '');
                    t.val(valueArea);
                    countN.html('0 ');
                }
            });
        },
        upFile: function(a, b, c) {
            $(c).bind('click', function(e) {
                e.preventDefault();
                $(a).trigger('click');
            });
            $(a).change(function() {
                var t = $(this);
                $(c).val($(a).val()).removeClass('cGray');
            });
        },
        disabled: function(chk, b) {
            var fields = $(b);
            $(chk).on('change', function() {
                var _this = $(this);
                if (_this.is(':checked')) {
                    $(b + ' input, ' + b + ' select, ' + b + ' textarea').attr('disabled', 'disabled');
                    fields.slideUp('fast');
                    fields.siblings('.wrap-btn').hide();
                    $('#msgErrorStep2').text('');
                } else {
                    $(b + ' input, ' + b + ' select, ' + b + ' textarea').removeAttr('disabled');
                    fields.slideDown('fast');
                    fields.siblings('.wrap-btn').show();
                }
                //limpiando campos
                fields.find('input.inputReq, select.selectReq').removeClass('ready');
                fields.find('input.iptDVar').addClass('ready');
                fields.find('select.selectReq').val('-1');
                fields.find('select.mesLoadF').val('1');
                fields.find('select.anioInicio').val(urls.fYearCurrent - 1);
                fields.find('select.anioFin').val(urls.fYearCurrent);
                fields.find('[type=text], textarea').val('');
                fields.find('.response').text('').removeClass('bad good');
                fields.find('.asideAds .error').text('');
                fields.find('.asideAds .btnPink').removeClass('active').addClass('inactiveBtn');
                fields.find('.openDesc').removeClass('hide').next().removeAttr('style');
                fields.find('.numCnt').text('140');
            });
        },
        enumeracionBlocks: function(oBlockD) {
            // enumeracion
            var arrNumBlocks = oBlockD.parents('.wrap-skills').find('.countN'),
                    currentNBlock = parseInt(oBlockD.find('.countN').text()),
                    sizeArr = arrNumBlocks.length,
                    enumerador = 0,
                    flag = 0;
            $.each(arrNumBlocks, function(item, value) {
                if (enumerador == currentNBlock && flag < 1) {
                    enumerador;
                    flag++;
                } else {
                    enumerador++;
                }
                arrNumBlocks.eq(item).text(enumerador+'.');
            });
            // fin enumeracion
        },
        enumeracionSkills: function(oBlockD) {
            if ($.trim($('body').attr('id')) == 'perfilReg') {
                // enumeracion block
                var oBlockS = $(oBlockD),
                        titleBlockS = oBlockS.prev().find('.nSkillA'),
                        nBlockSkill = oBlockS.find('.skillN'),
                        nBlockSize = nBlockSkill.size() - 1;
                if (nBlockSize == 0) {
                    $(titleBlockS).text('');
                } else {
                    $(titleBlockS).text('(' + nBlockSize + ')');
                }
                // fin enumeracion block
            }
        },
        clearField: function(fields) {
            var tr = $(fields);
            tr.focus(function() {
                var t = $(this);
                if (t.val() == t.attr('alt')) {
                    t.val('').removeClass('cGray').addClass(vars.okR);
                }
            });
            tr.blur(function() {
                var t = $(this);
                if (t.val() == '') {
                    t.val(t.attr('alt')).addClass('cGray ').addClass(vars.okR);
                }
            });
        },
        fStudyReq: function(sele) {
            var select1 = $(sele),
                    selectData = select1.attr('rel'),
                    arrJson = eval('(' + selectData + ')');

            select1.change(function() {
                var t = $(this),
                        select2 = t.parent().next().next().next().find('.selectEstH'),
                        select3 = t.parent().next().next().next().next().find('.selectEstH'),
                        input4 = t.parent().next().find('.inputN'),
                        select5 = t.parent().next().next().find('.selectPais'),
                        input6 = t.parent().next().next().next().next().next().find('.iptDVar'),
                        select61 = input6.next(),
                        select62 = input6.next().next(),
                        response63 = input6.parent().next(),
                        input7 = t.parent().next().next().next().next().next().next().find('.iptDVar'),
                        select71 = input7.next(),
                        select72 = input7.next().next(),
                        response73 = input7.parent().next().next(),
                        inputCheck74 = input7.parent().next().find('.inputOff'),
                        val = parseInt(t.val()),
                        valPeru = '2533';

                var detalle = false,
                        sinEstudios = false;
                for (i = 0; i < arrJson.disableds.length; i++) {
                    if (val == arrJson.disableds[i]) {
                        detalle = true;
                        if (val == 1) {
                            sinEstudios = true;
                        }
                    }
                }

                if (detalle) {
                    //detalle == true -> sinEstudios, primaria, secundaria
                    select2.attr('disabled', 'disabled').val('-1');
                    select2.addClass('ready').parents('#studyF').addClass('offSelect');
                    select3.addClass('ready').attr('disabled', 'disabled').val('');

                    select2.parent().hide().find('.response').text('');
                    select3.parent().hide().find('.response').text('');

                    if (sinEstudios) {
                        input4.addClass('ready').attr('disabled', 'disabled').val('').next().text('');
                        select5.val('none');
                        select5.addClass('ready').attr('disabled', 'disabled')
                                .next().text('').addClass('def').removeClass('bad def good');

                        input6.addClass('ready').attr('disabled', 'disabled').val('1/2010');
                        select61.attr('disabled', 'disabled').val('1');
                        select62.attr('disabled', 'disabled').val('2010');
                        response63.text('').addClass('def').removeClass('bad def good');
                        input7.addClass('ready').attr('disabled', 'disabled').val('1/2011');
                        select71.attr('disabled', 'disabled').val('1');
                        select72.attr('disabled', 'disabled').val('2011');
                        response73.text('').addClass('def').removeClass('bad def good');
                        inputCheck74.attr('disabled', 'disabled').removeAttr('checked');
                    } else {
                        input4.attr('disabled', false);
                        //select2.val('2533');
                        //Por defecto Perú
                        select5.val(valPeru);
                        select5.addClass('ready').removeAttr('disabled', 'disabled');
                        input6.removeAttr('disabled', 'disabled');
                        select61.removeAttr('disabled', 'disabled');
                        select62.removeAttr('disabled', 'disabled');
                        input7.removeAttr('disabled', 'disabled');
                        select71.removeAttr('disabled', 'disabled');
                        select72.removeAttr('disabled', 'disabled');
                        inputCheck74.removeAttr('disabled', 'disabled');
                    }
                } else if (t.val() == '-1') {
                    t.removeClass('ready');
                    select2.parent().show();
                    select3.parent().show();
                    select2.removeClass('ready').removeAttr('disabled');
                    select3.removeClass('ready').removeAttr('disabled');
                    input4.removeAttr('disabled').next().text('');
                    // sin estudios

                    // sin estudios
                    if (select5.find('option:selected').val() == 'none') {
                        select5.removeClass('ready').removeAttr('disabled', 'disabled');
                    } else {
                        select5.addClass('ready').removeAttr('disabled', 'disabled');
                    }
                    //select5.removeClass('ready').removeAttr('disabled','disabled').val('none');

                    input6.removeAttr('disabled', 'disabled');
                    select61.removeAttr('disabled', 'disabled');
                    select62.removeAttr('disabled', 'disabled');
                    response63.text('').addClass('def').removeClass('bad def good');
                    input7.removeAttr('disabled', 'disabled');
                    select71.removeAttr('disabled', 'disabled');
                    select72.removeAttr('disabled', 'disabled');
                    response73.text('').addClass('def').removeClass('bad def good');
                    inputCheck74.removeAttr('disabled', 'disabled');
                } else {
                    select2.parent().show();
                    select3.parent().show();
                    // sin estudios
                    select5.addClass('ready').removeAttr('disabled', 'disabled');
                    select5.val(valPeru);
                    input6.removeAttr('disabled', 'disabled');
                    select61.removeAttr('disabled', 'disabled');
                    select62.removeAttr('disabled', 'disabled');
                    response63.text('').addClass('def').removeClass('bad def good');
                    input7.removeAttr('disabled', 'disabled');
                    select71.removeAttr('disabled', 'disabled');
                    select72.removeAttr('disabled', 'disabled');
                    response73.text('').addClass('def').removeClass('bad def good');
                    inputCheck74.removeAttr('disabled', 'disabled');

                    if (select2.val() == '-1') {
                        select2.removeClass('ready').parents('#studyF').removeClass('offSelect');
                        select2.removeAttr('disabled');
                    } else {
                        select2.addClass('ready').parents('#studyF').removeClass('offSelect');
                        select2.removeAttr('disabled');
                    }
                    if ($.trim(select3.val()) == ' ' || $.trim(select3.val()) == '') {
                        select3.removeClass('ready').removeAttr('disabled');
                    } else {
                        select3.addClass('ready').removeAttr('disabled');
                    }
                    if ($.trim(input4.val()) == '' || $.trim(input4.val()) == ' ') {
                        input4.removeClass('ready').removeAttr('disabled').next().text('');
                    }

                }

                //campo de otro estudios
                var cntOtherStudy = t.siblings('.newInputOther');
                var iptOtherStudy = cntOtherStudy.children('.inputNewOther');

                if (t.val() == arrJson.otherStudy) {

                    cntOtherStudy.addClass('block').removeClass('hide');
                    //iptOtherStudy.val('').addClass('require').removeClass('ready').focus();

                    select2.addClass('ready').val('-1').attr('disabled', 'disabled').next()
                            .text('').addClass('def').removeClass('bad good');
                    select3.addClass('ready').attr('disabled', 'disabled').val('').next().next()
                            .text('').addClass('def').removeClass('bad good');
                } else {

                    cntOtherStudy.removeClass('block').addClass('hide');
                    //iptOtherStudy.val('').removeClass('ready require');

                    var valIntOE = parseInt(t.val());
                    if (valIntOE == arrJson.disableds[0] ||
                            valIntOE == arrJson.disableds[1] ||
                            valIntOE == arrJson.disableds[2]) {
                        select2.addClass('ready').attr('disabled');
                        select3.addClass('ready').attr('disabled');
                    } else {
                        if (select2.val() == '-1') {
                            select2.removeAttr('disabled').removeClass('ready');
                        } else {
                            select2.removeAttr('disabled').addClass('ready');
                        }

                        if (select3.val() == '-1') {
                            select3.removeAttr('disabled').removeClass('ready');
                        } else {
                            select3.removeAttr('disabled').addClass('ready');
                        }
                        //select2.removeAttr('disabled').removeClass('ready');
                        //select3.removeAttr('disabled').removeClass('ready');

                    }
                }

            });
        },
        deleteMesLoad: function() {
            var selectAll = $('.mesLoadF'),
                    countOpt, anioOpt, bucle = 12 - vars.vMonthCurrent;
            $.each(selectAll, function(i, v) {
                countOpt = $(selectAll[i]).children('option').size(),
                        anioOpt = $(selectAll[i]).next().val();
                if (Number(anioOpt) == vars.vYearCurrent) {
                    for (x = 0; x <= bucle; x++) {
                        $(selectAll[i]).children('option').eq(12 - x).remove().end();
                    }
                }
            });
        },
        fSelectMesIni: function() {
            $(document).on('change','.mesInicio', function() {
                var _this = $(this),
                    parent = _this.parents('.fSkill'),
                    tVal = _this.val(),
                    selectAnioIni = _this.siblings('.anioInicio').val(),
                    iptMesIni = _this.siblings('.iptDVar');
                
                //Cambie el mismo valor del mes Fin
                $('.mesFin', parent).val(tVal);
                // valor mes y anio en el input hidden
                iptMesIni.val(tVal + '/' + selectAnioIni);



                //procesando fecha
                formP2._fIptProcess(iptMesIni);
            });
        },
        fSelectAnioIni: function() {
            $(document).on('change', '.anioInicio', function() {
                var _this = $(this),
                    tVal = _this.val(),
                    parent = _this.parents('.fSkill'),
                    selectMesIni = _this.siblings('.mesInicio').val(),
                    iptMesIni = _this.siblings('.iptDVar');
                // si es el anio actual removiendo meses
                var mesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                //Cambie el mismo valor del año Fin
                $('.anioFin', parent).val(tVal);

                if (parseInt(tVal) == parseInt(vars.vYearCurrent)) {

                    var selectCurr = _this.siblings('.mesInicio'),
                            selectTrig = selectCurr.children('option'),
                            bucle = 12 - vars.vMonthCurrent;

                    //if agregado para validacion
                    if (parseInt(selectMesIni) >= parseInt(vars.vMonthCurrent)) {
                    selectCurr.val(vars.vMonthCurrent);
                    iptMesIni.val(vars.vMonthCurrent + '/' + vars.vYearCurrent);
                    }

                    for (x = 0; x <= bucle; x++) {
                        selectTrig.eq(12 - x).remove().end();
                    }
                } else {
                    // valor mes y anio en el input hidden
                    iptMesIni.val(selectMesIni + '/' + tVal);
                    var selectCurr = _this.siblings('.mesInicio'),
                            selectTrig = selectCurr.children('option'),
                            bucle = 12 - vars.vMonthCurrent;
                    if (selectTrig.size() == vars.vMonthCurrent) {
                        // si se han eliminado datos
                        for (x = vars.vMonthCurrent + 1; x <= 12; x++) {
                            selectCurr.append('<option value="' + x + '" label="' + mesNombres[x - 1] + '">' + mesNombres[x - 1] + '</option>');
                        }
                    }

                    $('.mesFin', parent).html('');
                    for (x = 1; x <= 12; x++) {
                        $('.mesFin', parent).append('<option value="' + x + '" label="' + mesNombres[x - 1] + '">' + mesNombres[x - 1] + '</option>');
                }
                }



                // valor mes y anio en el input hidden
                //procesando fecha
                formP2._fIptProcess(iptMesIni);
                // quitando meses
            });
        },
        fSelectMesFin: function() {
            var selectMesFi = $('.mesFin'),
                    separ = '/';
            selectMesFi.change(function() {
                var t = $(this),
                        tVal = t.val(),
                        selectAnioFin = t.siblings('.anioFin').val(),
                        iptMesFin = t.siblings('.iptDVar');
                // valor mes y anio en el input hidden
                iptMesFin.val(tVal + separ + selectAnioFin);
                //procesando fecha
                formP2._fIptProcess(iptMesFin);
            });
        },
        fSelectAnioFin: function() {
            var selectAnioFi = $('.anioFin'),
                    separ = '/';
            selectAnioFi.change(function() {
                var t = $(this),
                        tVal = t.val(),
                        selectMesFin = t.siblings('.mesFin').val(),
                        iptMesFin = t.siblings('.iptDVar');
                // si es el anio actual removiendo meses
                var mesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                if (parseInt(tVal) == parseInt(vars.vYearCurrent)) {
                    var selectCurr = t.siblings('.mesFin'),
                            selectTrig = selectCurr.children('option'),
                            bucle = 12 - vars.vMonthCurrent;

                    //if agregado para validacion
                    if (parseInt(selectMesFin) >= parseInt(vars.vMonthCurrent)) {
                    selectCurr.val(vars.vMonthCurrent);
                    iptMesFin.val(vars.vMonthCurrent + '/' + vars.vYearCurrent);
                    }

                    for (x = 0; x <= bucle; x++) {
                        selectTrig.eq(12 - x).remove().end();
                    }
                } else {
                    // valor mes y anio en el input hidden
                    iptMesFin.val(selectMesFin + separ + tVal);
                    var selectCurr = t.siblings('.mesFin'),
                            selectTrig = selectCurr.children('option'),
                            bucle = 12 - vars.vMonthCurrent;
                    if (selectTrig.size() == vars.vMonthCurrent) {
                        // si se han eliminado datos
                        for (x = vars.vMonthCurrent + 1; x <= 12; x++) {
                            selectCurr.append('<option value="' + x + '" label="' + mesNombres[x - 1] + '">' + mesNombres[x - 1] + '</option>');
                        }
                    }
                }
                // valor mes y anio en el input hidden
                //procesando fecha
                formP2._fIptProcess(iptMesFin);
            });
        },
        _fIptProcess: function(iptMesAnio) {
            var strIni = (iptMesAnio.val()).split('/'),
                    mesI = Number(strIni[0]),
                    anioI = Number(strIni[1]),
                    response = iptMesAnio.parents('.block').find('.response'),
                    iptOtherDate = iptMesAnio.parents('.bDate').siblings('.bDate').find('.iptDVar'),
                    responseOther = iptOtherDate.parents('.block').find('.response'),
                    strFin = (iptOtherDate.val()).split('/'),
                    mesOther = Number(strFin[0]),
                    anioOther = Number(strFin[1]);
            if (iptMesAnio.hasClass('iptInicio')) {
                //fecha inicio
                if (anioI > anioOther) {
                    iptMesAnio.removeClass('ready');
                    response.removeClass('good def').addClass('bad').text(msgs.cDate.sec.exed);
                    responseOther.removeClass('bad good def').text('');
                } else if (anioI == anioOther) {
                    if (mesI > mesOther) {
                        iptMesAnio.removeClass('ready');
                        response.removeClass('good def').addClass('bad').text(msgs.cDate.sec.exed);
                        responseOther.removeClass('bad good def').text('');
                    } else {
                        iptMesAnio.addClass('ready');
                        iptOtherDate.addClass('ready');
                        response.removeClass('bad def').addClass('good').text(msgs.cDate.good);
                        responseOther.removeClass('bad good def').text('');
                    }
                } else {
                    iptMesAnio.addClass('ready');
                    iptOtherDate.addClass('ready');
                    response.removeClass('bad def').addClass('good').text(msgs.cDate.good);
                    responseOther.removeClass('bad good def').text('');
                }
            } else {
                //fecha fin
                if (anioI < anioOther) {
                    iptMesAnio.removeClass('ready');
                    response.removeClass('good def').addClass('bad').text(msgs.cDate.sec.exed);
                    responseOther.removeClass('bad good def').text('');
                } else if (anioI == anioOther) {
                    if (mesI < mesOther) {
                        iptMesAnio.removeClass('ready');
                        response.removeClass('good def').addClass('bad').text(msgs.cDate.sec.exed);
                        responseOther.removeClass('bad good def').text('');
                    } else {
                        iptMesAnio.addClass('ready');
                        iptOtherDate.addClass('ready');
                        response.removeClass('bad def').addClass('good').text(msgs.cDate.good);
                        responseOther.removeClass('bad good def').text('');
                    }
                } else {
                    iptMesAnio.addClass('ready');
                    iptOtherDate.addClass('ready');
                    response.removeClass('bad def').addClass('good').text(msgs.cDate.good);
                    responseOther.removeClass('bad good def').text('');
                }
            }
        },
        inputOffSelect: function(a) {
            var A = $(a);
            A.bind('change', function() {
                var t = $(this),
                        tParents = t.parents('.bDate'),
                        selects = tParents.find('select'),
                        inpt = tParents.find('input.iptDVar'),
                        selectMonthF = inpt.siblings('.mesFin'),
                        selectAnioF = inpt.siblings('.anioFin');
                if (t.prop('checked')) {
                    selects.attr('disabled', 'disabled');
                    selectMonthF.val(vars.vMonthCurrent);
                    selectAnioF.val(vars.vYearCurrent);
                    inpt.val(vars.vMonthCurrent + '/' + vars.vYearCurrent).addClass('ready');
                    var tParentsSib = tParents.siblings('.initDate');
                    tParentsSib.find('input.iptDVar').addClass('ready');
                    tParentsSib.find('.response').text(msgs.cDate.good).removeClass('bad def').addClass('good');
                    tParents.find('.response').text('').removeClass('good bad def');
                } else {
                    selects.removeAttr('disabled');
                }
                // fin de Cálculo
            });
        },
        fSubmit: function(data) {
            var trigger = $(data.btn),
                    paso2 = $(data.form),
                    errorMsg = $(data.errorMsg),
                    speedd = 'fast';
            trigger.bind('click', function(e) {
                e.preventDefault();
                var checkExp = $(data.inval1),
                        checkEst = $(data.inval2),
                        // Verificando campos ( Experiencia y Estudios requeridos )
                        limitAdd1 = 6,
                        sectionCnt1 = '#experienceF',
                        nBlocks1 = ($(sectionCnt1 + ' .skillN').size()),
                        limitAddUnit1 = $(sectionCnt1 + ' .ready').size(),
                        limitAdd2 = 5,
                        sectionCnt2 = '#studyF',
                        nBlocks2 = ($(sectionCnt2 + ' .skillN').size()),
                        limitAddUnit2 = $(sectionCnt2 + ' .ready').size();

                var flagExp = false, flagEst = false;
                if (limitAddUnit1 < limitAdd1 * nBlocks1 && nBlocks1 < 2) {
                    // No estan completos los ready
                    $(sectionCnt1 + ' .inputReq ,' + sectionCnt1 + ' .selectReq').not('.' + vars.okR).siblings('.' + vars.rs).removeClass('def good').addClass('bad').text(msgs.cBadSubmit);
                } else {
                    // ready completo
                    flagExp = true;
                }
                if (limitAddUnit2 < limitAdd2 * nBlocks2 && nBlocks2 < 2) {
                    // No estan completos los ready
                    $(sectionCnt2 + ' .inputReq ,' + sectionCnt2 + ' .selectReq ,' + sectionCnt2 + ' .autoComplete').not('.' + vars.okR).siblings('.' + vars.rs).removeClass('def good').addClass('bad').text(msgs.cBadSubmit);
                } else {
                    // ready completo
                    flagEst = true;
                }


                if (!checkExp.is(':checked') && !checkEst.is(':checked')) {
                    if (flagExp == true && flagEst == true) {
                        // Tiene todos los campos validos ( Experiencia y Estudios Válidos )
                        // Verificando si check de acepto terminos es true
                        if ($('#optCheckP1').prop('checked') == true) {
                            errorMsg.text('');
                            $('select').removeAttr('disabled');
                            paso2.submit();
                        } else {
                            errorMsg.text(msgs.cBadTerminos);
                        }
                        //paso2.submit();	
                    } else if (flagExp == true) {
                        $('html, body').animate({
                            scrollTop: $(sectionCnt2).offset().top - 50
                        }, speedd);
                        errorMsg.text(msgs.cFormStep2);
                    } else if (flagEst == true) {
                        $('html, body').animate({
                            scrollTop: $(sectionCnt1).offset().top - 50
                        }, speedd);
                        errorMsg.text(msgs.cFormStep2);
                    } else {
                        // No lleno datos obligatorios validos
                        $('html, body').animate({
                            scrollTop: $(sectionCnt1).offset().top - 50
                        }, speedd);
                        errorMsg.text(msgs.cFormStep2);
                    }
                } else if (checkExp.is(':checked') && !checkEst.is(':checked')) {
                    if (flagEst == true) {
                        // Verificando si check de acepto terminos es true
                        if ($('#optCheckP1').prop('checked') == true) {
                            errorMsg.text('');
                            $('select').removeAttr('disabled');
                            paso2.submit();
                        } else {

                            errorMsg.text(msgs.cBadTerminos);
                        }
                        //paso2.submit();	
                    } else {
                        $('html, body').animate({
                            scrollTop: $(sectionCnt2).offset().top - 50
                        }, speedd);
                        errorMsg.text(msgs.cFormStep2);
                    }
                } else if (!checkExp.is(':checked') && checkEst.is(':checked')) {
                    if (flagExp == true) {
                        // Verificando si check de acepto terminos es true
                        if ($('#optCheckP1').is(':checked')) {
                            errorMsg.text('');
                            $('select').removeAttr('disabled');
                            paso2.submit();
                        } else {
                            errorMsg.text(msgs.cBadTerminos);
                        }
                        //paso2.submit();
                    } else {
                        $('html, body').animate({
                            scrollTop: $(sectionCnt1).offset().top - 50
                        }, speedd);
                        errorMsg.text(msgs.cFormStep2);
                    }
                } else {
                    // Envio de data ( Experiencia y Estudios Válidos )
                    // Verificando si check de acepto terminos es true
                    if ($('#optCheckP1').is(':checked')) {
                        errorMsg.text('');
                        $('select').removeAttr('disabled');
                        paso2.submit();
                    } else {
                        errorMsg.text(msgs.cBadTerminos);
                    }
                    //paso2.submit();
                }
            });
        },
        autoComplete: function(a, b, timeSleep, heightAutoCom) {
            var _this = $(a);

            _this.focus(function() {
                if ($('#cntAutoComplete').size() == 0) {
                    var divAutoCompl = '<div id="cntAutoComplete" class="hide r5"></div>',
                            body = $('body');
                    body.append(divAutoCompl);
                }else{
                 $('#cntAutoComplete').remove();
                }
                //quitando enter
            });

            formP2.inputReq(_this, msgs.cDef.good, msgs.cDef.bad);

            _this.keyup(formP2.debounce(function(e) {
                var cntAutoCompl = $('#cntAutoComplete');
                var listado;
                var itemsList,
                    t = $(e.target),
                    value = t.val(),
                        valueLength = value.length,
                    nivel = (t.parent().prev().find(b)).val(),
                    validate = function(){
                        //validacion
                        var respo = t.siblings(vars.rs);
                        if (valueLength > 0) {
                            t.addClass(vars.okR);
                            respo.removeClass('bad def').addClass('good').text(msgs.cDef.good);
                            //respo.removeClass('bad good').addClass('def').text(msgs.cDef.def);
                        } else {
                            t.removeClass(vars.okR);
                            respo.removeClass('good def').addClass('bad').text(msgs.cDef.bad);
                        }
                    };


                var key = e.keyCode || e.charCode || e.which || window.e;

                if ((key != 37 && key != 39 && key != 38 && key != 40 &&
                        key != 13 && key != 27 && key != 9 && key != 16 &&
                        key != 17 && key != 18 && key != 19 && key != 20 &&
                        key != 32 && key != 34 && key != 35 && key != 36 &&
                        key != 44 && key != 45)) {

                    //console.log(valueLength +'>='+ parseInt(t.attr('param')) +'&&'+ nivel +'!='+ '-1');
                    if (valueLength >= parseInt(t.attr('param')) && nivel != '-1') {
                        t.addClass('loadingAjax');

                        //Token
                        csrfHash_Inicial = $('body').attr('data-hash');
                        var csrfHash = "";
                        $.ajax({
                            url: '/registro/obtener-token/',
                            type: 'POST',
                            dataType: 'json',
                            data: {csrfhash: csrfHash_Inicial},
                            success: function(result) {

                                csrfHash = result;
                                $.ajax({
                                    'url': '/mi-cuenta/busqueda-general/',
                                    'type': 'POST',
                                    'dataType': 'JSON',
                                    'data': {
                                        'model': t.attr('model'),
                                        'q': value,
                                        csrfhash: csrfHash,
                                        //'nivel' : B.val(),
                                        'nivel': nivel,
                                        'subset': t.attr('subset')
                                    },
                                    'success': function(res) {
                                        t.removeClass('loadingAjax');
                                        if ($.browser.msie && $.browser.version.substr(0, 8) < 7) {
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
                                            listado.append('<li rel="' + i + '" class="acItem">' + res[i] + '</li>');
                                        }
                                        itemsList = $('#acItems .acItem').size();
                                        if (itemsList > 0) {
                                            //Existe Data
                                            cntAutoCompl.removeClass('hide');
                                            if (listado.height() > 200) {
                                                listado.parent().addClass('overflowAuto');
                                            } else {
                                                listado.parent().removeClass('overflowAuto');
                                            }
                                            posY = 0;
                                        } else {
                                            //no hay data
                                            cntAutoCompl.addClass('hide');
                                            $('#cntAutoComplete').remove();
                                        }
                                        //formP2.hover('.acItem');

                                        //scroll
                                        cntAutoCompl.scrollTop(0);
                                        //hover
                                        formP2.hover('.acItem');

                                        formP2.acItemClick('.acItem');

                                        validate();
                                    },
                                    'error': function(res) {
                                        //no ejecuta
                                    }
                                });
                            }

                        });


                    } else {
                        t.removeClass('loadingAjax');

                        if (cntAutoCompl) {
                            cntAutoCompl.addClass('hide');
                        }
                        posY = 0; //reinicio el contador autocomplete ajax
                        t.siblings(vars.rs).text('').removeClass('good');
                    }

                } else {
                    //no ejecuta
                }
            }, timeSleep));
            //keydown para el teclado
            _this.keydown(function(e) {
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
                            var thisInput = t.next(),
                                    thisRel = $.trim(itemsPosy.eq(posY - 1).attr('rel'));
                            //thisText = $.trim(itemsPosy.eq(posY - 1).text());
                            thisInput.val(thisRel);
                            //scroll
                            var posScroll = itemsPosy.eq(posY - 1).offset().top - itemsPosy.eq(0).offset().top - 100;
                            if (posScroll >= 0) {
                                cntAutoCompl.animate({
                                    scrollTop: posScroll - 20
                                }, 'fast');
                            }
                        }
                        //Abajo
                        if (key == 40 && posY < itemsPosSize) {
                            posY = posY + 1;
                            itemsPosy.removeClass('hover');
                            itemsPosy.eq(posY - 1).addClass('hover');
                            t.val(itemsPosy.eq(posY - 1).text());
                            t.val(itemsPosy.eq(posY - 1).text());
                            var thisInput = t.next(),
                                    thisRel = $.trim(itemsPosy.eq(posY - 1).attr('rel'));
                            //thisText = $.trim(itemsPosy.eq(posY - 1).text());
                            thisInput.val(thisRel);
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
                    if (key == 13) {
                        //enter
                        cntAutoCompl.addClass('hide');
                        return false;
                    }
                }
            });

            //Close Autocomplete
            $('body').not('#cntAutoComplete').click(function() {
                var cntAutoCompl = $('#cntAutoComplete');
                if (cntAutoCompl.size() > 0 && !cntAutoCompl.hasClass('hide')) {
                    cntAutoCompl.addClass('hide');
                }
            });
        },
        debounce: function(callback, delay) {
            var self = this, timeout, _arguments;
            return function() {
                _arguments = Array.prototype.slice.call(arguments, 0),
                        timeout = clearTimeout(timeout, _arguments),
                        timeout = setTimeout(function() {
                    callback.apply(self, _arguments);
                    timeout = 0;
                }, delay);
                return this;
            };
        },
        acItemClick: function(a) {
            var $a = $(a);
            $a.live('click', function() {
                var $t = $(this);
                var $ul = $t.parent(),
                        idIpt = $ul.attr('rel'),
                        thisInput = $('#' + idIpt);
                thisInput.next().val($t.attr('rel'));
                thisInput.val($t.text());
                $ul.remove();
                var cntAutoCompl = $('#cntAutoComplete');
                if (cntAutoCompl) {
                    cntAutoCompl.addClass('hide');
                }
            });
        },
        hover: function(a) {
            var $a = $(a);
            $a.mouseenter(function() {
                $(this).addClass('hover');
            });
            $a.mouseleave(function() {
                $(this).removeClass('hover');
            });
        },
        autoComResize: function() {
            $(window).resize(function() {
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
        iptChange: function(ipt, ipt2) {
            $(ipt).bind('change', function(e) {
                var t = $(this),
                        size;
                if ($.browser.msie) {
                    size = 1;
                } else {
                    size = t[0].files[0].size;
                }
                var btnS = $(ipt2);
                btnS.attr('rel', size);
                btnS.val(t.val()).removeClass('cGray');
            });
        },
        tipoCarrera: function(selec) {
            var select = $(selec);
            select.live('change', function() {
                var t = $(this),
                        valor = $.trim(t.val()),
                        selectCarrera = t.parent().next().find('select.selectN');
                selectCarrera.attr('disabled', 'disabled');
                var sectionId = '';
                if (valor != '-1') {

                    //Token
                    csrfHash_Inicial = $('body').attr('data-hash');
                    var csrfHash = "";
                    $.ajax({
                        url: '/registro/obtener-token/',
                        type: 'POST',
                        dataType: 'json',
                        data: {csrfhash: csrfHash_Inicial},
                        success: function(result) {

                            csrfHash = result;
                            $.ajax({
                                'url': '/registro/filtrar-carrera/',
                                'type': 'POST',
                                'dataType': 'JSON',
                                'data': {
                                    'id_tipo_carrera': valor,
                                    csrfhash: csrfHash
                                },
                                'success': function(res) {
                                    selectCarrera.children('option').not('option[value="-1"]').remove();
                                    $.each(res, function(i, v) {
                                        selectCarrera.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
                                    });
                                    selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
                                },
                                'error': function(res) {
                                    //limpio options menos -1
                                    selectCarrera.children('option').not('option[value="-1"]').remove();
                                    selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
                                }
                            });
                        }
                    });


                } else {
                    //-1 limpio options
                    selectCarrera.children('option').not('option[value="-1"]').remove();
                    selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
                }
            });
        },
        /**
         * Función de cambios de select para ocultar o mostrar inputs
         * @author Victor Sandoval
         * @method changeOptions
         */
        changeOptions: function(oP) {
            var dom = {},
            st = {
                inputTag    : '',
                option      : [], // []
                arrRequire  : [],
                isRequire   : true,
                arrShow     : [], // []
                onChange    : null
            },
            catchDom = function() {
                dom.inputTag = $(st.inputTag);
            },
            suscribeEvents = function(){
                $(document).on('change', st.inputTag, showHideInputs);
            },
            showHideInputs = function() {
                var _this = $(this),
                    parent = _this.parents('.fSkill'),

                    npid = _this.attr('name'),
                    value = $.trim(String(_this.val())),
                    contentTag = $('.' + npid, parent),
                    inputs = $('input,select', contentTag);

                if(st.onChange) st.onChange(_this,contentTag);
                
                if ($.inArray( value, st.option ) == -1) {
                    if(st.arrShow.length > 0){
                        $.each(st.arrShow,function(i,elem){
                            contentTag.eq(elem).removeClass('block').addClass('hide');
                        });
                    }else{
                        contentTag.removeClass('block').addClass('hide');
                    }

                    if(st.isRequire){
                        if(st.arrRequire.length > 0){
                            $.each(st.arrRequire,function(i,elem){
                                inputs.eq(elem).removeClass('require');
                            });
                        }else{
                            inputs.removeClass('require ready');
                        }    
                    }
                    

                } else {
                    if(st.arrShow.length > 0){
                        $.each(st.arrShow,function(i,elem){
                            contentTag.eq(elem).removeClass('hide').addClass('block');
                        });
                    }else{
                        contentTag.removeClass('hide').addClass('block');
                    }

                    if(st.isRequire){
                        if(st.arrRequire.length > 0){
                            $.each(st.arrRequire,function(i,elem){
                                inputs.eq(elem).addClass('require').val("");
                            });
                        }else{
                            inputs.addClass('require').val("");
                        }
                    }
                    
                }
                inputs.removeClass('ready');
            };

            $.extend(st, oP);
            catchDom();
            suscribeEvents();
        },
        chckColegiado: function() {

            $(document).on('change','.inputCheck', function() {
                var txtcolegiado = $(this).parent().next().find('.inputText');
                if (this.checked) {
                    txtcolegiado.attr('readonly', false);
                } else {
                    txtcolegiado.val('').attr('readonly', true);
                }
            });
        }
    }

    // init
    //paso 2
    formP2.tipoCarrera('.tipoCarreraN');
    formP2.autoComResize();
    formP2.fSelectMesIni();
    formP2.fSelectAnioIni();
    formP2.fSelectMesFin();
    formP2.fSelectAnioFin();
    formP2.inputOffSelect('input.iptOffSelect');
    formP2.deleteMesLoad();
    aptitusMethods.accord().init({
        divAction : '.blue-title'
    });


    formP2.addSkills().init({
        changeTitles : function(opts,j){
            nameLabel1 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock1).val();
            nameLabel2 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock2).val();
            return {
                nameLabel1 : nameLabel1,
                nameLabel2 : nameLabel2,
                separate   : 'en'
            }
        }
    });
    formP2.addSkills().init({
        idSection: '#studyF',
        btn: {
            id: '#btnEst',
            text: 'Añadir otro estudio'
        },
        idManager: 'managerEstudio_',
        idFieldBlock1: '_id_carrera',
        idFieldBlock2: '_institucion',
        idFieldBlock3: '_id_nivel_estudio',
        idFieldBlock4: '_otro_carrera',
        template: '#estTemplate',
        idPref: 'e',
        idExpRegMa: /managerEstudio_blank/ig,
        idExpRegF: /eblank_/ig,
        textDelete: msgs.mDelete.est,
        changeTitles : function(opts,j){

            var block1 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock1),
                block2 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock2),
                block3 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock3),
                block4 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock4),
                separate = 'en';

            //Otros estudios
            var comboJson = eval('(' + block3.attr("rel") + ')'),
                itemOE = comboJson.otherStudy;

            // Cuando es primaria y secundaria
            if ($.inArray(parseInt(block3.val()),comboJson.disableds) != -1) {                                                              
                nameLabel1 = $.trim(block2.val());
                nameLabel2 = block3.find('option:selected').text();
                if(nameLabel1 === "") separate = "";
            // Cuando es otros estudios
            } else if ($.trim(block4.val()) == '' && $.trim(block3.val()) == itemOE) {
                nameLabel1 = block2.val();
                nameLabel2 = block3.siblings('.newInputOther').children('input').val();
            } else {
                nameLabel1 = block2.val();
                nameLabel2 = block1.find('option:selected').text();
            }

            return {
                nameLabel1 : nameLabel1,
                nameLabel2 : nameLabel2,
                separate : separate
            }
        }
    });
    formP2.addSkills().init({
        idSection: '#languagesF',
        btn: {
            id: '#btnIdi',
            text: 'Añadir otro idioma'
        },
        idManager: 'managerIdioma_',
        idFieldBlock1: '_nivel_idioma',
        idFieldBlock2: '_id_idioma',
        template: '#idiTemplate',
        idPref: 'i',
        idExpRegMa: /managerIdioma_blank/ig,
        idExpRegF: /iblank_/ig,
        textDelete: msgs.mDelete.idi,
        messageWrong: msgs.cBadIdioma,
        changeTitles : function(opts,j){
            var block1 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock1),
                block2 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock2);

            nameLabel1 = block1.find('option:selected').text();
            nameLabel2 = block2.find('option:selected').text();
            return {
                nameLabel1 : nameLabel1,
                nameLabel2 : nameLabel2,
                separate   : 'nivel'
            }
        }
    });
    formP2.addSkills().init({
        idSection: '#programsF',
        btn: {
            id: '#btnPro',
            text: 'Añadir otro programa'
        },
        idManager: 'managerPrograma_',
        idFieldBlock1: '_nivel',
        idFieldBlock2: '_id_programa_computo',
        template: '#proTemplate',
        idPref: 'p',
        idExpRegMa: /managerPrograma_blank/ig,
        idExpRegF: /pblank_/ig,
        textDelete: msgs.mDelete.pro,
        messageWrong: msgs.cBadPrograma,
        changeTitles : function(opts,j){
            var block1 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock1),
                block2 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock2);

            nameLabel1 = block1.find('option:selected').text();
            nameLabel2 = block2.find('option:selected').text();
            return {
                nameLabel1 : nameLabel1,
                nameLabel2 : nameLabel2,
                separate   : 'nivel'
            }
        }
    });
    formP2.addSkills().init({
        idSection: '#referenceF',
        btn: {
            id: '#btnRef',
            text: 'Añadir otra referencia'
        },
        idManager: 'managerReferencia_',
        idFieldBlock1: '_cargo',
        idFieldBlock2: '_nombre',
        template: '#refTemplate',
        idPref: 'r',
        idExpRegMa: /managerReferencia_blank/ig,
        idExpRegF: /rblank_/ig,
        textDelete: msgs.mDelete.refe,
        changeTitles : function(opts,j){
            var nameLabel1 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock1).val(),
                nameLabel2 = $('#' + opts.idManager + (j - 1) + opts.idFieldBlock2).val();
            return {
                nameLabel1 : nameLabel1,
                nameLabel2 : nameLabel2,
                separate   : 'como'
            }
        }
    });
    formP2.inputReq('input.inputReq', msgs.cDef.good, msgs.cDef.bad);
    formP2.selectReq('select.selectReq', msgs.cDef.good, msgs.cDef.bad, msgs.cDef.def);
    formP2.clearField('input.clearH');
    formP2.charArea('.msgTask', '.numCnt', urls.cantdescExp);
    formP2.removeSkills().init();
    formP2.editSkills();
    formP2.fStudyReq('.selectEstR');

    formP2.disabled('#chkExp', '#expeDisabled');
    formP2.disabled('#chkStudy', '#estudyDisabled');
    //CV
    formP2.upFile('#pCV', '#fieldCV', '#repCV');
    //Submit
    formP2.fSubmit({
        btn: '#sSetep2',
        form: '#fStep2',
        inval1: '#chkExp',
        inval2: '#chkStudy',
        errorMsg: '#msgErrorStep2'
    });

    formP2.autoComplete('.autoComplete', '.nivelEstudio', 350, 200);
    formP2.autoComplete('.autoCompleteEst', '.nivelPuesto', 350, 200);

    formP2.acItemClick('.acItem');
    formP2.hover('.acItem');
    formP2.iptChange('#pCV', '#repCV');
    
    /**
     * Metodos para el cambio de opciones.. Se le agrega la clase require a las dependencias
     */
    formP2.changeOptions({
        inputTag : ".changeNombrePuesto",
        option   : ["1292"],
        onChange : function(input,contentTag){
            var value = input.val(),
                text = input.find('option[value="' + value + '"]').text();
            $('.inputN',contentTag).val(text);
        }
    });
    formP2.changeOptions({
        inputTag : ".changeLugar",
        option   : ["2"],
        arrRequire : [0,1]
    });
    formP2.changeOptions({
        inputTag: ".nivelEstudio", 
        option: ["7"],
        isRequire: false
    });
    formP2.changeOptions({
        inputTag: ".nivelEstudio", 
        option: ["9"],
        arrShow: [0],
        arrRequire: [0]
    });
    formP2.changeOptions({
        inputTag: ".changeCarrera", 
        option: ["15"],
        arrRequire: [0],
        onChange : function(input,contentTag){
            var value = input.val(),
                text = input.find('option[value="' + value + '"]').text();
            $('.inputN',contentTag).val(text);
        }
    });
    formP2.changeOptions({
        inputTag: ".nivelPuesto", 
        option: ["10"]
    });
    formP2.chckColegiado();

    aptitusMethods.validateAll().init({
        inputTag: '.fCos',
        type    : 'decimal'
    });
});
