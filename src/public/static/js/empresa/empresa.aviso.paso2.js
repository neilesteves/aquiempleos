/*
 Registro Empresa Aviso Paso 2
 */
$( function() {
    var msgs = {
        cDef : {
            good :'.',
            bad : 'Campo Requerido',
            def :'Opcional',
            acepto :'Debe aceptar las Políticas de Privacidad para continuar.'
        },
        cEmail : {
            good : '.',
            bad : 'Debe ingresar su dirección e-mail.',
            def : 'Ingrese e-mail correcto.',
            mailValid : 'Email ya registrado.'
        },
        cPass : {
            good : '¡Correcto! Verifica la seguridad de tu clave',
            bad : 'Usa de 6 a 32 caracteres',
            def : 'Usa de 6 a 32 caracteres ¡Sé ingenioso!',
            sec : {
                msgDef : 'Nivel de seguridad',
                msg1 : 'Demasiado corta',
                msg2 : 'Débil',
                msg3 : 'Fuerte',
                msg4 : 'Muy fuerte'
            }
        },
        cRePass : {
            good : '.',
            bad : 'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.',
            def : 'Tienen que ser iguales'
        },
        cPhoto : {
            good : '.',
            bad : 'No subio el logo',
            def : 'Sube tu logo'
        },
        cName : {
            good : '.',
            bad : '¡Se requiere su nombre!',
            def : 'Ingrese nombre correcto'
        },
        cApell : {
            good : '.',
            bad : '¡Se requiere su apellido!',
            def : 'Ingrese apellido correcto'
        },
        cNameCom : {
            good : '.',
            bad : '¡Se requiere su nombre comercial!',
            def : 'Ingrese nombre correcto'
        },
        cRazSoc : {
            good : '.',
            bad : '¡Se requiere su razón social!',
            def : 'Ingrese razón social correcta'
        },
        cRuc : {
            good : '.',
            bad : '¡Se requiere su RUC!',
            def : 'Ingrese su RUC correcta',
            rucValid : 'Ruc ya registrado.',
            incorrect : '¡Ruc incorrecto!'
        },
        cTlfNum : {
            good : '.',
            bad : 'Incorrecto',
            def : 'Ingrese Número Fijo ó Celular'
        },
        cSDoc : {
            good : '.',
            bad : '',
            def : '¡Correcto!'
        },
        cPais : {
            good : '.',
            bad : 'Selecciona país',
            def : '!OK¡'
        },
        cDepa : {
            good : '.',
            bad : 'Selecciona Departamento',
            def : '!Correcto¡'
        },
        cDist : {
            good : '.',
            bad : 'Selecciona Distrito',
            def : '!Correcto¡'
        },
        cRubro : {
            good : '.',
            bad : 'Selecciona Rubro',
            def : '!Correcto¡'
        }
    };
    
    var formP2 = {
        emailValidate : function(a,good,bad,def){
            var A = $(a),
                regexTest = function(){
                    var t = $(this),
                    longitud = t.val().length,
                    r = t.parents('.controls').find('.response'),
                    ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;

                    if(ep.test(t.val()) && t.val() != '' && longitud <= 70)
                        r.removeClass('bad hide').addClass('good').text('.');
                    else if(t.val() == '')
                        r.removeClass('bad hide').text("");
                    else
                        r.removeClass('good def').addClass('bad').text('No parece ser un campo válido.');
                    
                    $("#pMsnfEmail span").text(longitud);
                };
            
            A.blur( regexTest ).keypress( regexTest );
        },
        enumeracionBlocks : function(oBlockD) {
            // enumeracion
            var arrNumBlocks = oBlockD.parents('.flt').find('.countN'),
            currentNBlock = parseInt(oBlockD.find('.countN').text()),
            sizeArr = arrNumBlocks.length,
            enumerador = 0,
            flag = 0;

            $.each(arrNumBlocks, function(item,value) {
                if(enumerador == currentNBlock && flag < 1) {
                    enumerador;
                    flag++;
                } else {
                    enumerador++;
                }
                arrNumBlocks.eq(item).text(enumerador);
            });
        // fin enumeracion
        },
        reviewLastD : function(idPrefijoS) {
            var cntSection;
            //Experiencia
            if(idPrefijoS == 'f') {
                cntSection = $('#contentFromExp');
            }
            //Estudios
            if(idPrefijoS == 'e') {
                cntSection = $('#contentFromEst');
            }

            var lastBDelete = cntSection.children('.skillN'),
            hideBlock = 1,
            speedd = 'fast';
            if( ( lastBDelete.size() - hideBlock ) < 1 ) {
                //Experiencia
                if(idPrefijoS == 'f') {
                    var htmlHide = '<label for="fNoExp" class="labelN noReq"><span class="req" title="Requerido">&nbsp;</span> No tengo experiencia:</label>' +
                    '<input id="fNoExp" name="fNoExp" type="checkbox" class="left noBdr"/>';
                //muestra
                }
                //Estudios
                if(idPrefijoS == 'e') {
                    var htmlHide = '<label for="fNoEstudy" class="labelN noReq"><span class="req" title="Requerido">&nbsp;</span> No tengo estudios:</label>' +
                    '<input id="fNoEstudy" name="fNoEstudy" type="checkbox" class="left noBdr"/>';
                //muestra
                }
            }
        },
        pasarelaItems : function(cntItems){
            var cnt = $(cntItems);
            if(cnt.size()>0){
                var items = cnt.find('.aPagerAP');  
                $.each(items, function(i,v){
                    setTimeout(function(){
                        $(items).eq(i).animate({
                            left : 0 + i*33
                        },'slow');
                    },100);  
                });
            }
        },
        submitAPP2 : function(btn, form){
            var btnSubmit = $(btn),
            btnForm = $(form),
            yellowbox = $(".msgYellow");

            //Submit Aviso Preferencial
            if(btnSubmit.size()>0 && 
                btnSubmit.hasClass('tooltipApt') &&
                btnForm.size()>0 ){
                btnForm.append('<a href="#nextFormAP" id="winFormMsjP" class="winModal"></a>');

                btnSubmit.bind('click', function(e){
                    e.preventDefault();
                    var t = $(this);
                    $('#redirctGua').val(t.attr('href'));
                    //link href modal
                    $('#btnNextPAP').attr('href',t.attr('href'));
                    		
                    //Validando campos
                    var data1 = $.trim($('#nombre_puesto').val()),
                    data2 = $.trim($('#id_nivel_puesto').val()),
                    data3 = $.trim($('#id_area').val()),
                    data4 = $.trim($('#funciones').val()),
                    data5 = $.trim($('#responsabilidades').val());

                    //Condicionando
                    $('#contpaso3').val(1);

                    if( data1 != '' &&  data2 != '-1' &&
                        data3 != '-1' && data4 != '' &&
                        data5 != '' /*&& data6 != '-1' && data7 != '-1'*/){
                        //Enviando datos del form
                        btnForm.submit();
                        //location.href = t.attr('href');
                        $('#btnNextPAP').trigger('click');
                    }else if(data1 != '' || data2 != '-1' ||
                        data3 != '-1' || data4 != '' ||
                        data5 != '' /*|| data6 != '-1' || data7 != '-1'*/){
                        //Mensaje 
                        $('#numPuestoPP').text($('#listPagerAP1 .currentItem').text());
                        $('#winFormMsjP').trigger('click');
                    }else{
                        //Enviando datos del form
                        //btnForm.submit();
                        location.href = t.attr('href');
                    }
                });
               

                //guardar puesto en modal
                var btnSavePAP = $('#btnSavePAP');
                if(btnSavePAP.size()>0){
                    btnSavePAP.bind('click', function(){
                        $('.cntPagerAP2 input[type="submit"]').trigger('click');
                    });
                }
            }

            //Yellowbox: ocultar caja
            /*yellowbox.find(".close").bind('click',function(){
                $(this).parent().fadeOut("fast");
            });

            //Submit todos los tipos
            btnSubmit.bind('click', function(e){
                var data6 = $('#managerEstudio_0_id_nivel_estudio').val(),
                    data7 = $('#managerExperiencia_0_id_nivel_puesto').val(),
                    seccion3 = $('#studyF'),
                    seccion4 = $('#experienceF');

                    if(data6 == -1 || data7 == -1){
                        seccion3.removeClass("close").addClass("open")
                            .prev().removeClass(".offAcor").addClass(".onAcor");
                        seccion4.removeClass("close").addClass("open")
                            .prev().removeClass(".offAcor").addClass(".onAcor");
                        alert(),
                        seccion3.next().addClass("bad").text("Debe ingresar un nivel de estudio");
                        seccion4.next().addClass("bad").text("Debe ingresar un nivel del puesto");
                        return false;
                    }
                return false;
            });*/
            
        },
        checkDataAP : function(){
            var frm = $('#frmPublishAd'),
            itemsReady = $('.readyItem, .seudoItem'),
            limitChar = 1000, 
            arrFrm = [],
            initSerial = frm.serialize();
            if(initSerial.length > limitChar){
                arrFrm.push(initSerial.substring(0, limitChar));
            }else{
                arrFrm.push(initSerial);
            }        	       	
            itemsReady.bind('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                var t = $(this),
                strHref = t.attr('href'),
                endSerial = $('#frmPublishAd').serialize(),
                linkAlert = $('#linkBtnFPaso');
                if(endSerial.length > limitChar){
                    endSerial = endSerial.substring(0, limitChar);
                }else{
                    endSerial = endSerial;
                }
        		
                if(endSerial != arrFrm[0]){
                    //cambio la info
                    $('#btnNPasosNext').attr('href',strHref);
                    $('#redirctGua').attr('value',strHref);
                    linkAlert.click();
                }else{
                    //no cambio
                    location.href = strHref;
                }
                if($('#escapeFormPaso').css('display') == 'block'){
                    $('#btnNPasos').bind('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if ($('#btnSave').size() >= 1) {
                        	$('#btnSave').click();
                        } else {
                        	$('#btnSaveCAP').click();
                        }
                        
                    //frm.submit();
                    });
                }
            });	
        }
    };
    // init
    //paso 2
    formP2.pasarelaItems('#listPagerAP1');
    formP2.pasarelaItems('#listPagerAP2');
    formP2.emailValidate('#correo', msgs.cDef.good, 'No parece ser un email válido', msgs.cDef.def)

    //Paso 2 Aviso Preferencial
    formP2.submitAPP2('#aSetep2', '#frmPublishAd');
    formP2.submitAPP2('#anclaNextP3', '#frmPublishAd');
    formP2.checkDataAP();    
});
