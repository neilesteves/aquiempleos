/*
    Registro postulante Paso 1
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
            good : 'Verifica la seguridad de tu clave',
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
        cRazonSocial : {
            good : '.',
            bad : '¡Se requiere Razon Social!',
            def : 'Ingrese su Razon Social',
            Valid : 'Razon Social ya registrada.',
            incorrect : '¡Razon Social incorrecta!'
        },
        cNombreComercial : {
            good : '.',
            bad : '¡Se requiere Nombre Comercial!',
            def : 'Ingrese su Nombre Comercial',
            Valid : 'Nombre Comercial ya registrado.',
            incorrect : '¡Nombre Comercial incorrecto!'
        },
        cTlfNum : {
            good : '.',
            bad : 'Incorrecto',
            def : 'Ingrese Número Celular'
        },
        cSDoc : {
            good : '.',
            bad : '',
            def : '¡Correcto!'
        },
        cPais : {
            good : '.',
            bad : 'Selecciona país',
            def : '!Correcto¡'
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
        cProv : {
            good : '.',
            bad : 'Selecciona Provincia',
            def : '!Correcto¡'
        },        
        cRubro : {
            good : '.',
            bad : 'Selecciona Rubro',
            def : '!Correcto¡'
        }
    };
    var vars = {
        rs : '.response',
        okR :'ready',
        sendFlag : 'sendN',
        loading : '<div class="loading"></div>'
    };
    var registroEmp = {
        fMail : function(a,good,bad,def) {
            $(a).bind('blur', function() {
                var t = $(this),
                r = t.next(vars.rs),
                regEx = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g,
                value = t.val();

                if(value.match(regEx) && t.val() !== '') {
                    registroEmp._fMailValid(a, t, r);
                } else {
                    r.removeClass('good').addClass('bad').text(bad);
                    t.removeClass(vars.okR);
                    t.addClass('bad-data');
                }
            });
        },
        _fMailValid : function(a, t, r){
            var $email = t;
            r.text('');
            $email.addClass('loadingMail');
            $.ajax({
                url: '/registro/validar-email/',
                type: 'post',
                data: {
                    email: $email.val(),
                    modulo: 'empresa'
                },
                dataType: 'json',
                success: function(response){
                    if (response.status == true) {
                        $email.removeClass('loadingMail bad-data');
                        r.removeClass('bad def').addClass('good').text(msgs.cEmail.good);
                    } else {
                        $email.removeClass('loadingMail').addClass('bad-data');
                        r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
                    }
                },
                error : function(response){
                    $email.removeClass('loadingMail bad-data');
                    r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
                }
            });
        },
        fRuc : function(a, good, bad, def, incorrect){
            $(a).bind('blur', function() {
                var t = $(this),
                r = t.next(vars.rs),
                valueRuc = $.trim(t.val()),
                val2Dig = parseInt(valueRuc.substr(0,2)),
                ultimoDig = parseInt(valueRuc.substr(10,1)),
                factor = '5432765432',
                diviRuc = 0,
                restoFinal = 0;
				
                if(valueRuc != '' &&
                    valueRuc.length == $.trim(t.attr('datamaxlength')) &&
                    (val2Dig == 10 ||
                        val2Dig == 20 ||
                        val2Dig == 17 ||
                        val2Dig == 15)
                    ) {
					
                    var arrValueRuc = valueRuc.split('',10),
                    arrFactor = factor.split('',10),
                    sumaTotalRuc = 0;
					
                    for(var i = 0; i < 10; i++ ){
                        sumaTotalRuc += parseInt(arrValueRuc[i]) * parseInt(arrFactor[i]);
                    };

                    diviRuc = (sumaTotalRuc%11);
					
                    restoFinal = 11 - diviRuc;
                    if(restoFinal == 10){
                      restoFinal = 0;
                    }else if(restoFinal == 11){
                    	restoFinal = 1;
                    }
					
                    if(restoFinal <= 10 && restoFinal == ultimoDig ){
                        //validacion ajax
                        registroEmp._fRucValid(a, t, r);
                    }else{
                        r.removeClass('good').addClass('bad').text(incorrect);
                        t.removeClass(vars.okR).addClass('bad-data');
                    }
					
					
                } else {
                    r.removeClass('good').addClass('bad').text(incorrect);
                    t.removeClass(vars.okR).addClass('bad-data');
                }
            });
        },
        fRazonSocial : function(a, good, bad, def, incorrect){
            $(a).bind('blur', function() {
                var t = $(this),
                r = t.next(vars.rs);
                if($.trim(t.val())!='') {
                    registroEmp._fRazonSocialValid(a, t, r);
                } else {
                    if($.trim(t.val())!='') {
                        r.removeClass('good').addClass('bad').text(incorrect);
                        t.removeClass(vars.okR);
                    }else{
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                }
            });
        },
        fNombreComercial : function(a, good, bad, def, incorrect){
            $(a).bind('blur', function() {
                var t = $(this),
                r = t.next(vars.rs);
                if($.trim(t.val())!='') {
                    registroEmp._fNombreComercialValid(a, t, r);
                } else {
                    if($.trim(t.val())!='') {
                        r.removeClass('good').addClass('bad').text(incorrect);
                        t.removeClass(vars.okR);
                    }else{
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                }
            });
        },
        _fRucValid : function(a, t, r){
            var _this = t;
            r.text('');
            _this.addClass('loadingMail');
            $.ajax({
                url: '/empresa/registro-empresa/validar-ruc/',
                type: 'post',
                data: {
                    ndoc: _this.val(),
                    idEmp: _this.attr('rel')
                },
                dataType: 'json',
                success: function(response){
                    if( response.status == true){
                        _this.addClass('ready').removeClass('loadingMail bad-data');
                        r.removeClass('bad def').addClass('good').text(msgs.cRuc.good);
                    }else{
                        _this.removeClass('ready').removeClass('loadingMail');
                        r.removeClass('good def').addClass('bad').text(msgs.cRuc.rucValid);
                        _this.removeClass(vars.okR).removeClass('bad-data');
                    }
                },
                error : function(response){
                    _this.removeClass('ready').removeClass('loadingMail');
                    r.removeClass('good def').addClass('bad').text('Error en la consulta');
                    _this.removeClass(vars.okR).removeClass('bad-data');
                }
            });
        },
        _fRazonSocialValid : function(a, t, r) {
            var $razonsocial = t;
            r.text('');
            $razonsocial.addClass('loadingMail');
            var idEmp = $razonsocial.attr('rel');
            
            //Token
            csrfHash_Inicial = $('body').attr('data-hash');
             var csrfHash = "";
             $.ajax({
             url: '/registro/obtener-token/',
             type: 'POST',
             dataType:'json',
             data:{csrfhash: csrfHash_Inicial},
            success: function (result) {
                csrfHash = result;
                $.ajax({
                url: '/empresa/registro-empresa/validar-razonsocial/',
                type: 'post',
                data: {
                    ndoc: $razonsocial.val(),
                    idEmp: idEmp,
                    csrfhash:csrfHash
                },
                dataType: 'json',
                success: function(response){
                    if( response.status == true){
                        $razonsocial.addClass('ready').removeClass('loadingMail');
                        r.removeClass('bad def').addClass('good').text(msgs.cRazonSocial.good);
                    }else if ( response.status == parseInt(t.attr('words-limit')) ){
                        $razonsocial.removeClass('ready').removeClass('loadingMail');                        
                        r.removeClass('good def').addClass('bad')
                            .text('Mayor que le numero máxímo (' + response.status + ') de palabras permitidas');                        
                        t.removeClass(vars.okR);                         
                    }else if ( response.status == 'error' ){
                        $razonsocial.removeClass('ready').removeClass('loadingMail');                        
                        r.removeClass('good def').addClass('bad')
                            .text('Solo puede ingresar letras y números');                        
                        t.removeClass(vars.okR);                         
                    }else{
                        $razonsocial.removeClass('ready').removeClass('loadingMail');
                        r.removeClass('good def').addClass('bad').text(msgs.cRazonSocial.Valid);
                        t.removeClass(vars.okR);
                    }
                },
                error : function(response){
                    $razonsocial.removeClass('ready').removeClass('loadingMail');
                    r.removeClass('good def').addClass('bad').text(msgs.cRazonSocial.Valid);
                    t.removeClass(vars.okR);
                }
            });
                                
                               
            }
                        
        });
            
            
        },
        _fNombreComercialValid : function(a, t, r) {
            var $nombrecomercial = t;
            r.text('');
            $nombrecomercial.addClass('loadingMail');
            var idEmp = $nombrecomercial.attr('rel');
            
            $.ajax({
                url: '/empresa/registro-empresa/validar-nombrecomercial/',
                type: 'post',
                data: {
                    ndoc: $nombrecomercial.val(),
                    idEmp: idEmp
                },
                dataType: 'json',
                success: function(response){
                    if( response.status == true) {
                        $nombrecomercial.addClass('ready').removeClass('loadingMail');
                        r.removeClass('bad def').addClass('good').text(msgs.cNombreComercial.good);
                    }else if ( response.status == parseInt(t.attr('words-limit')) ){
                        $nombrecomercial.removeClass('ready').removeClass('loadingMail');                        
                        r.removeClass('good def').addClass('bad')
                            .text('Mayor que le numero máxímo (' + response.status + ') de palabras permitidas');                        
                        t.removeClass(vars.okR);                         
                    }else if ( response.status == 'error' ){
                        $nombrecomercial.removeClass('ready').removeClass('loadingMail');                        
                        r.removeClass('good def').addClass('bad')
                            .text('Solo puede ingresar letras y números');                        
                        t.removeClass(vars.okR);                         
                    }else {
                        $nombrecomercial.removeClass('ready').removeClass('loadingMail');
                        r.removeClass('good def').addClass('bad').text(msgs.cNombreComercial.Valid);
                        t.removeClass(vars.okR);                        
                    }
                },
                error : function(response) {
                    $nombrecomercial.removeClass('ready').removeClass('loadingMail');
                    r.removeClass('good def').addClass('bad').text(msgs.cNombreComercial.Valid);
                    t.removeClass(vars.okR);
                }
            });
        },
        fPass : function(a,b,c) {
            var good = msgs.cPass.good,
            bad = msgs.cPass.bad,
            def = msgs.cPass.def,
            msgDef = msgs.cPass.sec.msgDef,
            msg1 = msgs.cPass.sec.msg1,
            msg2 = msgs.cPass.sec.msg2,
            msg3 = msgs.cPass.sec.msg3,
            msg4 = msgs.cPass.sec.msg4,
            pf1 = $('#pf1'),
            pf2 = $('#pf2'),
            pf3 = $('#pf3'),
            pf4 = $('#pf4'),
            msg = $('#txtPass'),
            ep = /[a-z]|[A-Z]|\d|[^A-Za-z0-9]/,
            epMin = /[a-z]/,
            epMay = /[A-Z]/,
            epNum = /\d/,
            epEsp = /[^A-Za-z0-9]/,
            epMinC = /[a-z]+[A-Z]+|[A-Z]+[a-z]+|[a-z]+\d+|\d+[a-z]+|[a-z]+[^A-z0-9]+|[^A-z0-9]+[a-z]+/,
            epMayC = /[A-Z]+\d+|\d+[A-Z]+|[A-Z]+[^A-z0-9]+|[^A-z0-9]+[A-Z]+/,
            epEspC = /[^A-z0-9]+\d+|\d+[^A-z0-9]+/;
            $(a).keyup( function() {
                var t = $(this),
                v = $(this).val(),
                r = t.siblings(vars.rs);
                if(v.length>=(b)) {
                    r.removeClass('bad').addClass('good').text(good);
                    if(ep.test(t.val())) {
                        pf1.removeClass('bgRed bgGreen').addClass('bgYellow');
                        pf2.removeClass('bgRed bgGreen').addClass('bgYellow');
                        pf3.removeClass('bgRed bgGreen');
                        pf4.removeClass('bgGreen');
                        msg.text(msg2);

                        if( epMinC.test(t.val()) || epMayC.test(t.val()) || epEspC.test(t.val()) ) {
                            pf1.removeClass('bgRed bgYellow').addClass('bgGreen');
                            pf2.removeClass('bgRed bgYellow').addClass('bgGreen');
                            pf3.removeClass('bgYellow').addClass('bgGreen');
                            pf4.removeClass('bgGreen');
                            msg.text(msg3);
                        }
                        if(epMay.test(t.val()) && epNum.test(t.val()) && epEsp.test(t.val())) {
                            pf1.removeClass('bgRed bgYellow').addClass('bgGreen');
                            pf2.removeClass('bgRed bgYellow').addClass('bgGreen');
                            pf3.removeClass('bgYellow').addClass('bgGreen');
                            pf4.addClass('bgGreen');
                            msg.text(msg4);
                        }
                    }
                    t.addClass(vars.okR);
                } else {
                    r.removeClass('good bad').text(def);
                    pf1.addClass('bgRed').removeClass('bgYellow bgGreen');
                    pf2.removeClass('bgYellow bgGreen');
                    pf3.removeClass('bgGreen');
                    pf4.removeClass('bgGreen');
                    msg.text(msg1);
                    t.removeClass(vars.okR);
                }
                if(v.length==0) {
                    pf1.removeClass('bgRed bgYellow');
                    pf2.removeClass('bgRed bgYellow');
                    msg.text(msgDef);
                }
                var cc = $(c);
                if(cc.val().length>0) {
                    rr = cc.next(vars.rs);
                    if(cc.val()!==t.val()) {
                        rr.removeClass('god bad').text(msgs.cRePass.def);
                    } else {
                        rr.removeClass('bad').addClass('good').text(msgs.cRePass.good);
                    }
                }
            }).blur( function() {
                var t = $(this);
                r = t.parents('.block').find(vars.rs);
                if(t.val().length>=b) {
                    r.removeClass('bad').addClass('good').text(good);
                    t.addClass(vars.okR);
                } else {
                    r.removeClass('good').addClass('bad').text(bad);
                    t.removeClass(vars.okR);
                }
            });
        },
        fRePass : function(a,b,c) {
            var good = msgs.cRePass.good,
            bad = msgs.cRePass.bad,
            def = msgs.cRePass.def,
            r = $(a).next(vars.rs);
            $(a).keyup( function() {
                var t=$(this);
                if(t.val().length>=c) {
                    if(t.val()===$(b).val()) {
                        r.removeClass('bad').addClass('good').text(good);
                        t.addClass(vars.okR);
                    } else {
                        r.removeClass('good bad').text(def);
                        t.removeClass(vars.okR);
                    }
                } else {
                    r.removeClass('good bad').text(def);
                    t.removeClass(vars.okR);
                }
            }).blur( function() {
                var t=$(this);
                if(t.val().length>=c) {
                    if(t.val()!==$(b).val()) {
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    } else {
                        r.removeClass('bad').addClass('good').text(good);
                        t.addClass(vars.okR);
                    }
                } else {
                    r.removeClass('good').addClass('bad').text(bad);
                    t.removeClass(vars.okR);
                }
            });
        },
        upPhoto : function(a,b,c,good,bad,def) {
            $(c).bind('click', function(e) {
                e.preventDefault();
                $(a).trigger('click');
            });

            if($.browser.opera){
                $(a).css({
                    'left':'10px'
                });
            }
        },
        fInput : function(a,good,bad,def, flag) {
            var A = $(a),
            r = A.parents('.block').find(vars.rs);
            A.blur( function() {
                var t = $(this);
                if($.trim(t.val()).length>0) {
                    if(flag == true){
                        r.removeClass('bad').addClass('good').text(good);
                        t.addClass(vars.okR);
                    }
                } else {
                    if(flag == true){
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                }
            }).keydown( function() {
                var t = $(this);
                if($.trim(t.val()).length===0) {
                    if(flag == true){
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                } else {
                    if(flag == true){
                        r.removeClass('good bad').text(def);
                        t.addClass(vars.okR);
                    }
                }
            });
        },
        fNumberTelephone : function(a,good,bad,def, flag) {
            var A = $(a),
            r = A.parents('.block').find(vars.rs);
            A.attr('placeholder','Entre 7-9 dígitos');
            A.blur( function() {
                var t = $(this),
                ep = /^[0-9]{7,9}$/g;
                if(ep.test(t.val())&&$.trim(t.val()).length>0) {
                    if(flag == true){
                        r.removeClass('bad').addClass('good').text(good);
                        t.addClass(vars.okR);
                    }
                } else {
                    if(flag == true){
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                }
            }).keydown( function() {
                var t = $(this);
                if($.trim(t.val()).length===0) {
                    if(flag == true){
                        r.removeClass('good').addClass('bad').text(bad);
                        t.removeClass(vars.okR);
                    }
                } else {
                    if(flag == true){
                        r.removeClass('good bad').text(def);
                        t.addClass(vars.okR);
                    }
                }
            });
        },
        fIDate : function(a,good,bad,def) {
            var A = $(a),
            r = A.parent().next(vars.rs);
            A.change( function() {
                var t=$(this);
                r.removeClass('bad').addClass('good').text(good);
                t.addClass(vars.okR);
            });
        },
        fRadius : function(a,b,good,bad,def) {
            var A = $(a);
            A.bind('change', function() {
                var t = $(this);
                A.removeClass(vars.okR);
                if(t.is(':checked')) {
                    t.addClass(vars.okR);
                }
                t.parents(b).next(vars.rs).removeClass('def bad').addClass('good').text(good);
            });
        },
        fINum : function(a) {
            return $(a).each( function() {
                var t = $(this);
                t.keydown( function(e) {
                    var key = e.keyCode || e.charCode || e.which || window.e ;
                    return (key == 8 || key == 9 || key == 32 ||
                        (key >= 48 && key <= 57)||
                        (key >= 96 && key <= 106)||
                        key==109 || key==116 );
                });
            });
        },
        fOnlyNumTlf : function(a) {
            return $(a).each( function() {
                var t = $(this),
                isShift = false;
                t.keypress( function(e) {
				
                    var key = e.keyCode || e.charCode || e.which || window.e ;
						
                    if(key == 16) isShift = true;
							
                    return ( key == 8 || key == 9 || key == 32 ||
                        key == 40 || key == 41 || key == 42 ||
                        key == 45 || key == 35 ||
                        ( key == 48 && isShift == false ) ||
                        ( key == 49 && isShift == false ) ||
                        ( key == 50 && isShift == false ) ||
                        ( key == 51 && isShift == false ) ||
                        ( key == 52 && isShift == false ) ||
                        ( key == 53 && isShift == false ) ||
                        ( key == 54 && isShift == false ) ||
                        ( key == 55 && isShift == false ) ||
                        ( key == 56 && isShift == false ) ||
                        ( key == 57 && isShift == false )
						
                        );
					
                });
                t.bind('paste', function(){
                    setTimeout(function() {
                        var value = t.val();
                        var newValue = value.replace(/[^0-9-#-*-(-)--]/g,'');
                        t.val(newValue);
                    }, 0);
                });
                t.bind('keyup',function(e){
                    r = t.next();
                    var valueInput = t.val();
                        newValue = valueInput.replace(/[#*()-]/g,'');
                        t.val(newValue);
                });
            });
        },
        fOnlyNumDoc : function(a) {
            return $(a).each( function() {
                var t = $(this),
                isShift = false;
                t.keypress( function(e) {
				
                    var key = e.keyCode || e.charCode || e.which || window.e ;
						
                    if(key == 16) isShift = true;
							
                    return ( key == 8 || key == 9 || key == 32 ||
                        ( key == 48 && isShift == false ) ||
                        ( key == 49 && isShift == false ) ||
                        ( key == 50 && isShift == false ) ||
                        ( key == 51 && isShift == false ) ||
                        ( key == 52 && isShift == false ) ||
                        ( key == 53 && isShift == false ) ||
                        ( key == 54 && isShift == false ) ||
                        ( key == 55 && isShift == false ) ||
                        ( key == 56 && isShift == false ) ||
                        ( key == 57 && isShift == false )
						
                        );
					
                });
                t.bind('paste', function(){
                    setTimeout(function() {
                        var value = t.val();
                        var newValue = value.replace(/[^0-9]/g,'');
                        t.val(newValue);
                    }, 0);
                });

            });
        },
        fWordsOnly : function(a) {
            return $(a).each( function() {
                var t = $(this);
                t.keydown( function(e) {
                    var key = e.keyCode || e.charCode || e.which || window.e ;
                    return(	key==8 || key==9 || key==13 || key==32 ||
                        key > 64 && key < 91 ||
                        key==192 );
                });
            });
        },
		fUbi : function(a,b,c, d){
                    var A = $(a),
                    B = $(b),
                    C = $(c),
                    idProvincia = $(d),
                    r = $.trim(A.attr('rel')),
                    rB = $.trim(B.attr('rel')),
                    attrCallao = $.trim(idProvincia.attr('idCallao'));
                    var paisCargado =  $.trim($(a + ' option:selected' ).val());
                    var ciudadCargado =  $.trim($(b + ' option:selected' ).val());
                    var provinciaCargado =  $.trim($(d + ' option:selected' ).val());

                    if( paisCargado != r && paisCargado != 'none' ){
                        //B.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        B.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        B.next().html('&nbsp;').removeClass('bad good'); 
                        C.next().html('&nbsp;').removeClass('bad good'); 
                        idProvincia.next().html('&nbsp;').removeClass('bad good'); 

                        A.addClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.addClass(vars.okR);

                    }else if( paisCargado == 'none'){
                        
                        B.next(vars.rs).removeClass('god bad').text('');
                        //B.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        B.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.next(vars.rs).removeClass('god bad').text('');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.next(vars.rs).removeClass('god bad').text('');
                        //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        A.removeClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.addClass(vars.okR);

                    }else if( paisCargado == r && 
                        (ciudadCargado != rB && 
                        ciudadCargado != 'none')){
                        C.next(vars.rs).removeClass('god bad').text('');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        idProvincia.next(vars.rs).removeClass('god bad').text('');
                        //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        A.addClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.addClass(vars.okR);

                    }else if( paisCargado == r && 
                        (ciudadCargado == 'none')){
                        C.next(vars.rs).removeClass('god bad').text('');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        idProvincia.next(vars.rs).removeClass('god bad').text('');
                        //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        A.addClass(vars.okR);		
                        B.removeClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.addClass(vars.okR);

                    }else if( paisCargado == r && 
                        (ciudadCargado == rB && 
                        provinciaCargado != 'none' &&
                        provinciaCargado != attrCallao &&
                        provinciaCargado != idProvincia.attr('rel'))){
                        C.next(vars.rs).removeClass('god bad').text('');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        
                        A.addClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.addClass(vars.okR);

                    }else if( paisCargado == r && 
                        (ciudadCargado == rB && 
                        provinciaCargado == 'none')){
                        C.next(vars.rs).removeClass('god bad').text('');
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');

                        A.addClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);
                        C.removeClass(vars.okR);

                    }else if( paisCargado == r ){

                        A.addClass(vars.okR);		
                        B.addClass(vars.okR);	
                        idProvincia.addClass(vars.okR);

                    }

                    A.bind('change',function(){
                        var t = $(this);
                        if(t.val() == r){
                            //t.removeClass(vars.sendFlag);
                            B.removeAttr('disabled');
                            B.siblings('label').removeClass('noReq').children('span').html('* '); 

                            A.addClass(vars.okR);		
                            B.removeClass(vars.okR);
                            C.addClass(vars.okR);
                            idProvincia.addClass(vars.okR);

                        }else{
                            //t.addClass(vars.sendFlag);
                            //B.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            B.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            B.next().html('&nbsp;').removeClass('bad good'); 
                            C.next().html('&nbsp;').removeClass('bad good');

                            //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                            idProvincia.next().html('&nbsp;').removeClass('bad good'); 

                            A.addClass(vars.okR);
                            B.addClass(vars.okR); 
                            C.addClass(vars.okR);
                            idProvincia.addClass(vars.okR); 

                        }
                        if(t.val() == 'none'){
                            A.removeClass(vars.okR);
                        }else{
                            A.addClass(vars.okR);
                        } 	
                });
                //Departamento
                B.bind('change',function(){
                    var t = $(this);
                    if(t.val() == t.attr('rel')){
                        idProvincia.removeAttr('disabled');
                        idProvincia.siblings('label').removeClass('noReq').children('span').html('* '); 

                        A.addClass(vars.okR);
                        B.addClass(vars.okR); 
                        C.removeClass(vars.okR);
                        idProvincia.removeClass(vars.okR); 

                    }else{
                        //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');                       
                        idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        idProvincia.removeClass(vars.okR).next().html('&nbsp;').removeClass('bad good'); 
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');                       
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.removeClass(vars.okR).next().html('&nbsp;').removeClass('bad good'); 

                        A.addClass(vars.okR);
                        B.addClass(vars.okR); 
                        C.addClass(vars.okR);
                        idProvincia.addClass(vars.okR); 

                    }
                    if(t.val() == 'none'){
                        B.removeClass(vars.okR);
                    }else{
                        B.addClass(vars.okR);
                    }                     
                });
                //Provincia
                idProvincia.bind('change',function(){
                    var t = $(this);
                    if(t.val() == t.attr('idcallao') || (t.val() == t.attr('rel'))){
                        C.attr('disabled');
                        C.siblings('label').removeClass('noReq').children('span').html('* ');    
                        C.children('option').not('option[value="none"]').remove();                        

                        A.addClass(vars.okR);
                        B.addClass(vars.okR); 

                        idProvincia.addClass(vars.okR); 
                        
                        //Token
                        csrfHash_Inicial = $('body').attr('data-hash');
                        var csrfHash = "";
                        $.ajax({
                            url: '/registro/obtener-token/',
                            type: 'POST',
                            dataType:'json',
                            data:{csrfhash: csrfHash_Inicial},
                            success: function (result) {

                                csrfHash = result;
                                $.ajax({
                            'url' : '/registro/filtrar-distritos/',
                            'type' : 'POST',
                            'dataType' : 'JSON',
                            'data' : {
                                'id_ubigeo' : t.val(),
                                'csrfhash': csrfHash
                            },
                            'success' : function(res){
                                $.each(res, function(i,v){
                                    C.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
                                });
                                C.removeAttr('disabled').removeClass('ready bad good').next().text('');

                            },
                            'error' : function(res){
                                //limpio options menos -1
                                C.removeAttr('disabled').removeClass('ready bad good').next().text('');

                            }
                        });
                      }
                     });
                        
                        
                    }else{
                        //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                        C.next().html('&nbsp;').removeClass('bad good'); 
                        idProvincia.next().html('&nbsp;').removeClass('bad good'); 	

                        A.addClass(vars.okR);
                        B.addClass(vars.okR); 
                        C.addClass(vars.okR);
                        idProvincia.addClass(vars.okR); 

                    }
                });        

		},
        fSelect : function(a,good,bad,def) {
            var A = $(a),
            r = A.next(vars.rs);
            A.bind('change', function() {
                var t=$(this);
                if(t.val()==='none') {
                    A.removeClass(vars.okR);
                    r.removeClass('good def').addClass('bad').text(bad);
                } else {
                    r.addClass('good').removeClass('bad def').text(good);
                    A.addClass(vars.okR);
                }
            });
        },
        fOldPass : function(a){
            var trigger = $(a),
            res = trigger.siblings('.response');
            if( ($('body').is('#myAccount')) ) {
                trigger.keyup( function(){
                    res.removeClass('bad good').addClass('def').text('');
                });
            }
        },
        maxLenghtInput : function(trigger){
            var input = $(trigger);
            $.each(input, function(i,v){
                $(v).attr('dataMaxlength',$(v).attr('maxlength'));
            });
            input.bind('keyup click blur focus change paste', function(e){
                var t = $(this),
                //string = (t.siblings('select').val()).split('#'),
                numMax = parseInt(t.attr('dataMaxlength')),
                valueArea;
                var key = e.which;
                var length = t.val().length;
                if( length > numMax ) {
                    valueArea = t.val().substring(numMax, '') ;
                    input.val(valueArea);
                }
            });
        },
        fSubmit : function(a,b,c,f1,f2,f3,f4,f5,f6,f7,f8,f9,f10,f11,f12) {
            var A=$(a),
            B=$(b), z = 12, y = 13, k=11, F1 = $(f1), F2 = $(f2), F3 = $(f3),
            F4 = $(f4), F5 = $(f5), F6 = $(f6), F7 = $(f7), F8 = $(f8),
            F9 = $(f9), F10 = $(f10), F11 = $(f11), errorMsg = $('#errorMsgRE'),
            F12 = $(f12);
            A.bind('click', function(e) {
                e.preventDefault();
                /*if(F9.val()!=F9.attr("rel")) {
                    c = k;
                } else {
                    if(F10.val()!=F10.attr("rel")) {
                        c = z;
                    } else {
                        c = y;
                    }
                }*/
                if( B.find('.' + vars.okR).size() >= c ) {
                    if($('#optCheckP1').prop('checked') == true){
                        errorMsg.text('');
                        B.submit();
                    }else{
                        errorMsg.text(msgs.cDef.acepto);
                    }
                } else {
                    e.preventDefault();
                    $('#datoBasicoF .fields').not('.ready, :disabled').removeClass('ready').parents('.block').find('.response').removeClass('def good').addClass('bad').text(msgs.cDef.bad);
                }
            });
        },
        deletephoto : function (a){
            $(a).bind('click', function(evt){
                evt.preventDefault();
                var t = $(this),
                contImg = $('#imgAvat'),
                idEmpresa = t.attr('rel');
                contImg.addClass('loading').children('img').addClass('hide');
				
                if($.trim(idEmpresa) != ''){
                    $.ajax({
                        type: 'POST',
                        url: '/empresa/registro-empresa/eliminarfoto/',
                        data: {
                            'rel' : idEmpresa
                        },
                        dataType: 'JSON',
                        success: function(res) {
                            contImg.removeClass('loading').html('<img src="'+urls.mediaUrl+'/images/empresa/photoEmpDefault.png"/>');
                            $('#divDelFot').addClass('hide');
                            var inputFoto = $('#fPhoto');
                            registroEmp.resetInputFile(inputFoto);
                        },
                        error : function(res) {
                            contImg.removeClass('loading').empty().html('<img src="'+urls.mediaUrl+'/images/empresa/photoEmpDefault.png"/>');
                            $('#divDelFot').addClass('hide');
                            var inputFoto = $('#fPhoto');
                            registroEmp.resetInputFile(inputFoto);
                        }
                    });
                }else{
                    contImg.removeClass('loading').empty().html('<img src="'+urls.mediaUrl+'/images/empresa/photoEmpDefault.png"/>');
                    $('#divDelFot').addClass('hide');
                    var inputFoto = $('#fPhoto');
                    registroEmp.resetInputFile(inputFoto);
                }
				
            });
        },
        resetInputFile : function(ipt){
            ipt.wrap('<form></form>');
            ipt.parent().get(0).reset();
            ipt.unwrap();
            return ipt;
        },
        numberWords : function(ipt, params){
            var isShift = false;
            $(ipt).bind('keypress', function(e){
                var t = $(this);
                
                if( !(t.hasClass('noValidInput')) ){

                    var valIpt = $.trim(t.val()),
                    arrValIpt = (valIpt.replace(/\s+/g," ")).split(' ');
                    var key = e.keyCode || e.charCode || e.which || window.e ,
                    size = arrValIpt.length,
                    intRel = parseInt(t.attr('words-limit'));                                                
                    (key == 16) ? isShift = true : false; 
                    
                    if(key){                   
                       if(size >= intRel && key == 32 ){
                           return false;
                       }else{                                                
                            if(params.type == 'onlyWords'){
                                return( key == 8 || key == 9 || key == 13 || key == 32 || 
                                (key > 64 && key < 91) || (key >= 97 && key <= 122) || key == 192 || 
                                key == 225 || key == 233 || key == 237 || key == 243 || key == 250 ||
                                key == 193 || key == 201 || key == 205 || key == 211 || key == 218 ||
                                key == 209 || key == 241 || key == 39 );
                            }else if(params.type == 'wordsNumber'){
                                return( key == 8 || key == 9 || key == 13 || key == 32 ||                              
                                    ( key == 48 && isShift == false ) ||
                                    ( key == 49 && isShift == false ) ||
                                    ( key == 50 && isShift == false ) ||
                                    ( key == 51 && isShift == false ) ||
                                    ( key == 52 && isShift == false ) ||
                                    ( key == 53 && isShift == false ) ||
                                    ( key == 54 && isShift == false ) ||
                                    ( key == 55 && isShift == false ) ||
                                    ( key == 56 && isShift == false ) ||
                                    ( key == 57 && isShift == false ) ||
                                    (key > 64 && key < 91) || (key >= 97 && key <= 122) || key == 192 || 
                                    key == 225 || key == 233 || key == 237 || key == 243 || key == 250 ||
                                    key == 193 || key == 201 || key == 205 || key == 211 || key == 218 ||
                                    key == 209 || key == 241 || key == 39 );
                            }else{
                                return true;
                            }
                       }
                    }

                }

            });
            $(ipt).bind('paste blur', function(e){
                var t = $(this);
                
                if( !(t.hasClass('noValidInput')) ){
                
                    var intRel = parseInt(t.attr('words-limit')),
                    valNew = '';
                    setTimeout(function(){
                       var valIpt = $.trim(t.val()),
                       arrCharIpt;

                        if(params.type == 'onlyWords'){
                            arrCharIpt = valIpt.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s+]/g,"");
                        }else if(params.type == 'wordsNumber'){
                            arrCharIpt = valIpt.replace(/[^a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s+]/g,"");
                        }else{
                            arrCharIpt = valIpt;
                        }

                       var arrEspIpt = arrCharIpt.replace(/\s+/g," "),
                       arrValIpt = arrEspIpt.split(' '),
                       spc = ' ';      
                       $.each(arrValIpt, function(i,v){
                           if( i < intRel ){
                            (i == (intRel-1))? spc = '' : spc = ' ';
                            valNew += arrValIpt[i] + spc;
                            (i <= (intRel-1))? t.val(valNew):'';
                           }else{
                               return false;
                           }
                       });
                   },0); 
               
               }
               
            });
        }
    };
    // init
    //Registro Empresa
    registroEmp.fMail('#fEmail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
    registroEmp.fPass('#fClave',6,'#fRClave');
    registroEmp.fRePass('#fRClave','#fClave',6);
    registroEmp.fInput('#fNombreComercial',msgs.cNameCom.good,msgs.cNameCom.bad,msgs.cNameCom.def, true);
    registroEmp.fInput('#fRazonSocial',msgs.cRazSoc.good,msgs.cRazSoc.bad,msgs.cRazSoc.def, true);
    registroEmp.fInput('#fRuc',msgs.cRuc.good,msgs.cRuc.bad,msgs.cRuc.def, true);
    registroEmp.fOnlyNumDoc('#fRuc');
    registroEmp.maxLenghtInput('#fRuc');
    registroEmp.fRuc('#fRuc', msgs.cRuc.good,msgs.cRuc.bad,msgs.cRuc.def,msgs.cRuc.incorrect);
    registroEmp.fRazonSocial('#fRazonSocial', msgs.cRazonSocial.good, msgs.cRazonSocial.bad, msgs.cRazonSocial.def, msgs.cRazonSocial.incorrect );
    registroEmp.fNombreComercial('#fNombreComercial', msgs.cNombreComercial.good, msgs.cNombreComercial.bad, msgs.cNombreComercial.def, msgs.cNombreComercial.incorrect );
    registroEmp.fNumberTelephone('#fTlfAmin1',msgs.cTlfNum.good,msgs.cTlfNum.bad,msgs.cTlfNum.def, true);
    registroEmp.fOnlyNumTlf('#fTlfAmin1');
    registroEmp.fNumberTelephone('#fTlfAmin2',msgs.cTlfNum.good,msgs.cTlfNum.bad,msgs.cTlfNum.def, false);
    registroEmp.fOnlyNumTlf('#fTlfAmin2');
    registroEmp.fInput('#fAnexo1',msgs.cTlfNum.good,msgs.cTlfNum.bad,msgs.cTlfNum.def, false);
    registroEmp.fOnlyNumTlf('#fAnexo1');
    registroEmp.fInput('#fAnexo2',msgs.cTlfNum.good,msgs.cTlfNum.bad,msgs.cTlfNum.def, false);
    registroEmp.fOnlyNumTlf('#fAnexo2');
    registroEmp.fInput('#fNombres',msgs.cName.good,msgs.cName.bad,msgs.cName.def, true);
    registroEmp.fInput('#fApellidos',msgs.cApell.good,msgs.cApell.bad,msgs.cApell.def, true);
	
    //registroEmp.upPhoto('#fPhoto','#imgAvat', '#triggerUpA',msgs.cPhoto.good,msgs.cPhoto.bad,msgs.cPhoto.def);
	
    registroEmp.fUbi('#fPais','#fDepart','#fDistri', '#fProvin');
    registroEmp.fSelect('#fRubro',msgs.cRubro.good,msgs.cRubro.bad,msgs.cRubro.def);
    registroEmp.fSelect('#fPais',msgs.cPais.good,msgs.cPais.bad,msgs.cPais.def);
    registroEmp.fSelect('#fDepart',msgs.cDepa.good,msgs.cDepa.bad,msgs.cDepa.def);
    registroEmp.fSelect('#fDistri',msgs.cDist.good,msgs.cDist.bad,msgs.cDist.def);
    registroEmp.fSelect('#fProvin',msgs.cProv.good,msgs.cProv.bad,msgs.cProv.def);
    registroEmp.fOldPass('#fACtns');
    //registroEmp.fSubmit('#fSubmit','#datoBasicoF',14,'#fEmail','#fClave','#fRClave','#fNombreComercial','#fRazonSocial','#fRuc','#fNombres','#fApellidos','#fPais','#fDepart','#fTlfAmin1','#fDistri');
	aptitusMethods.validateAll().init({
        context     : '#datoBasicoF',
        isForm      : true,
        inputs      : {
            fEmail: {require : true},
            fNombreComercial: {require : true},
            fRazonSocial: {require : true},
            fRuc: {require : true},
            fRubro: {require : true},
            fPais: {require : true},
            fDepart: {require : true},
            fProvin: {require : true},
            fDistri: {require : true},
            fNombres: {require : true},
            fApellidos: {require : true},
            fTlfAmin1: {require : true}
        }
    });

    //registroEmp.deletephoto('#divDelFot');
    registroEmp.numberWords('#fNombreComercial', { type : 'wordsNumber' });
    registroEmp.numberWords('#fRazonSocial', { type : 'wordsNumber' });     
});