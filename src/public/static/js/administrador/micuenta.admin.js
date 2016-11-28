/*
 Empresa Ve Perfil postulante
 */
//Registro postulante Paso 1
var jq = {
   onlyNumDoc : function(a){
        return $(a).each( function(){
            var t = $(this),
            isShift = false;
            t.keypress( function(e){				
                    var key = e.keyCode || e.charCode || e.which || window.e ;
                    if(key == 16) isShift = true;						
                    return ( key == 8 || key == 9 || key == 32 ||
                             key == 37 || key == 39 ||
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
    }
};
$( function() {
        jq.onlyNumDoc('#fRuc');
	var msgs = {
		cDef : {
			good :'.',
			bad : 'Campo Requerido',
			def :'Opcional'
		},
		cEmail : {
			good : '.',
			bad : 'No parece ser un correo electrónico válido.',
			def : 'Ingrese e-mail correcto',
			mailValid : 'Email ya registrado.'
		},
		cPass : {
			good : '¡OK! Verifica la seguridad de tu clave',
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
			bad : 'No subio la foto',
			def : 'Sube tu foto'
		},
		cName : {
			good : '.',
			bad : '¡Se requiere tu nombre!',
			def : 'Ingrese nombre correcto'
		},
		cApell : {
			good : '.',
			bad : '¡Se requiere tu apellido!',
			def : 'Ingrese apellido correcto'
		},
		cBirth : {
			good : '.',
			bad : '¡Se requiere su fecha de nacimiento!',
			def : 'Ingrese su fecha de nacimiento completa.',
			exed : 'Incorrecto!. La fecha de nacimiento seleccionada es mayor a la fecha actual'
		},
		cSexo : {
			good : '.',
			bad : '¿Femenino o Masculino?',
			def : 'Defina su sexo'
		},
		cTlfNum : {
			good : '.',
			bad : 'Incorrecto',
			def : 'Ingrese Número Celular'
		},
		cSDoc : {
			good : '.',
			bad : '',
			def : '¡OK!'
		},
		cDocNum : {
			good : '.',
			bad : 'Incorrecto',
			def : 'Ingrese número de Documento',
		docNumValid : 'Numero de Doc. ya registrado.'
		},
		cECivil : {
			good : '.',
			bad : '',
			def : '¡OK!'
		},
		cPais : {
			good : '.',
			bad : 'Selecciona país',
			def : '!OK¡'
		},
		cDepa : {
			good : '.',
			bad : 'Selecciona Departamento',
			def : '!OK¡'
		},
		cDist : {
			good : '.',
			bad : 'Selecciona Distrito',
			def : '!OK¡'
		},
		cProv : {
			good : '.',
			bad : 'Selecciona Provincia',
			def : '!OK¡'
		},                
		cDividir : {
			good : 'División del aviso exitosa',
			bad  : 'Error al dividir el aviso'
		},
		cBlockPostulante: {
			block : '¿Está seguro que desea bloquear al postulante?',
			desblock : '¿Está seguro que desea desbloquear al postulante?'
		},
		cBlockEmpresa: {
			block : '¿Está seguro que desea bloquear la empresa?',
			desblock : '¿Está seguro que desea desbloquear la empresa?'
		},
		cBlockAviso: {
			block : '¿Está seguro que desea bloquear el aviso?',
			desblock : '¿Está seguro que desea desbloquear el aviso?'
		},
		cMsjBlock: {
			block : 'Bloqueo exitoso',
			desblock : 'Desbloqueo exitoso'
		},
                cShowAviso: {
			block : '¿Está seguro que desea mostrar el aviso en la portada?',
			desblock : '¿Está seguro que desea retirar el aviso de la portada?'
		},
		cShowAvisoMsj: {
			block : 'Se agregó el aviso en la portada.',
			desblock : 'Se retiró el aviso de la portada'
		}
	};
	if($('#frmBuscar_avisos').size()>0 || $('#frmBuscar_avisosPreferenciales').size()>0){
		msgs.cBirth.def = 'Indique la fecha de creación.';
		msgs.cBirth.bad = 'Se requiere la fecha de creación.';
		msgs.cBirth.exed = 'La fecha de creación es mayor que la fecha actual.';
	}
	var vars = {
		rs : '.response',
		okR :'ready',
		sendFlag : 'sendN',
		loading : '<div class="loading"></div>'
	};
	var formP1 = {
            
		fMail : function(a,good,bad,def) {
			$(a).bind('blur', function() {
				var t = $(this),
				r = t.next(vars.rs),
				ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				if( ep.test(t.val()) && t.val() != '' ) {					
					if($('body').attr('id') == 'myAccount'){
						//cuenta postulante
						//formP1._fMailValid(a, t, r);											
					}else{
						//admin						
						r.removeClass('bad def').addClass('good').text(good);
						t.addClass(vars.okR);							
					}
				} else {					
					r.removeClass('good').addClass('bad').text(bad);
					t.removeClass(vars.okR);					
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
					modulo: 'postulante'
				},
				dataType: 'json',	    				
				success: function(response){
					if( response.status == true){
						$email.addClass('ready').removeClass('loadingMail');
						r.removeClass('bad def').addClass('good').text(msgs.cEmail.good);
					}else{
						$email.removeClass('ready').removeClass('loadingMail');
						r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
						t.removeClass(vars.okR);	
					}
				},
				error : function(response){
					$email.removeClass('ready').removeClass('loadingMail');
					r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
					t.removeClass(vars.okR);	  					
				}
			});
		
		},
    fDni : function(a, good, bad, def){
			$(a).bind('blur', function() {
				var t = $(this),
				r = t.next(vars.rs);
				if(t.val()!='' && t.val().length==t.attr('maxlength')) {
					if($('body').attr('id') == 'myAccount'){
						//cuenta postulante
						formP1._fDniValid(a, t, r);											
					}else{
						//admin						
						r.removeClass('bad').addClass('good').text(good);
						t.addClass(vars.okR);					
					}					
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.removeClass(vars.okR);
				}
			});
		},
    _fDniValid : function(a, t, r){
	    var $ndoc = t;
        r.text('');
        $ndoc.addClass('loadingNumDoc');
        var idPost = $ndoc.attr('rel');
	    $.ajax({
        url: '/registro/validar-dni/',
        type: 'post',
        data: {
                ndoc: $ndoc.val(),
                idPost: idPost
        },
        dataType: 'json',
        success: function(response){
          if( response.status == true){
            $ndoc.addClass('ready').removeClass('loadingNumDoc');
            r.removeClass('bad def').addClass('good').text(msgs.cDocNum.good);
          }else{
            $ndoc.removeClass('ready').removeClass('loadingNumDoc');
            r.removeClass('good def').addClass('bad').text(msgs.cDocNum.docNumValid);
            t.removeClass(vars.okR);
          }
        },
        error : function(response){
          $ndoc.removeClass('ready').removeClass('loadingNumDoc');
          r.removeClass('good def').addClass('bad').text(msgs.cDocNum.docNumValid);
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
				r = t.parents('.block').find(vars.rs);
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
			$(a).bind('change', function() {
				$(b).html('').addClass('loading');
			});
			if($.browser.opera){
				$(a).css({'left':'10px'});
			}
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
		fWordsOnly : function(a) {
			return $(a).each( function() {
				var t = $(this);
				t.keydown( function(e) {
					var key = e.keyCode || e.charCode || e.which || window.e ;
					return(	key == 8 || key == 9 || key == 13 || key == 32 ||
						key > 64 && key < 91 ||
						key == 192 );
				});
			});
		},

		fSelect : function(a,good,bad,def) {
			var A = $(a),
			r = A.next(vars.rs);
			A.bind('change', function() {
				var t = $(this);
				if(t.val() == 'none') {
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
		maxLenghtN : function(trigger){
			var select = $(trigger),
			input = select.next();
			select.bind('change', function(){
				var t = $(this),
						string = (t.val()).split('#'),
						numMax = string[1],
						inputVal = input.val();
				input.removeAttr('maxlength').attr('maxlength', numMax);
				input.val(inputVal);
        input.focus();
			});				
			input.bind('keyup click blur focus change paste', function(e) {
				var t = $(this),
				string = (t.siblings('select').val()).split('#'),
				numMax = parseInt(string[1]),
				valueArea;
				var key = e.which;
				var length = t.val().length;
				if( length > numMax ) {
					valueArea = t.val().substring(numMax, '') ;
					input.val(valueArea);
				}
			});						
		},
    fDistritoValidacion : function(a,b,good,bad,def) {
        var dist = $(b),
        A = $(a),
        r = dist.next(vars.rs);
        if(A.val()==3926 && dist.val()=="none") {
          dist.removeClass(vars.okR);
          r.removeClass('good def').addClass('bad').text(bad);
        } else {
          r.addClass('good').removeClass('bad def').text(good);
          dist.addClass(vars.okR);
        }
    },
	};
	// init
	//paso 1
	formP1.fMail('#fEmail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	formP1.fPass('#fClave',6,'#fRClave');
	formP1.fRePass('#fRClave','#fClave',6);
	formP1.fIDate('#fBirthDate',msgs.cBirth.good,msgs.cBirth.bad,msgs.cBirth.def);
	formP1.upPhoto('#fPhoto','#imgAvat', '#triggerUpA',msgs.cPhoto.good,msgs.cPhoto.bad,msgs.cPhoto.def);	
	formP1.fDni('#fNDoc', msgs.cDocNum.good,msgs.cDocNum.bad,msgs.cDocNum.def);
	//formP1.fUbi('#fPais','#fDepart','#fDistri');	
	formP1.fSelect('#fEstCvil',msgs.cECivil.good,msgs.cECivil.bad,msgs.cECivil.def);
	//formP1.fSelect('#fPais',msgs.cPais.good,msgs.cPais.bad,msgs.cPais.def);
	//formP1.fSelect('#fDepart',msgs.cDepa.good,msgs.cDepa.bad,msgs.cDepa.def);
	//formP1.fSelect('#fDistri',msgs.cDist.good,msgs.cDist.bad,msgs.cDist.def);
    //formP1.fSelect('#fProvin',msgs.cProv.good,msgs.cProv.bad,msgs.cProv.def);
	formP1.fOldPass('#fACtns');
	formP1.maxLenghtN('select.maxLenghtN');


	var adminPost = {
			//perfil	
			verPostulante : function(a) {
				$(a).live('click', function(e) {
					e.preventDefault();
					var t = $(this);
					var idperfil = t.attr('href');
					
					// cadena solo # 
					if( $.browser.msie && $.browser.version.substr(0,1) < 8 ) {
						var strI = idperfil.split('#'),
						strId = strI[1];
						idperfil = '#' + strId;   
					}		
					var url = '/admin/mi-cuenta/mis-datos-personales';//t.attr('rel');
					var contenido = '#content-' + idperfil.substr(1,idperfil.length);
					var contenidoN = $(contenido);
					contenidoN.html('');
					contenidoN.addClass('loading');
					contenidoN.load(url,{'type': 'Json', 
						'method' : 'POST'}, function() {
						
						contenidoN.removeClass('loading');

					});
				});
			},
			
			bloquearLookAndFeel : function(a) {
				$(a).live('click', function(e) {
					e.preventDefault();
					var t = $(this);
					var idEmp = t.attr('rel');
					var url = '', msj ='';
					
					if(t.hasClass('blockLookAndFeel')) {
						$('#winAlertBloquearLookAndFeel #titleQ').html("¿Desea desactivar la vista 'look&feel'?");
						url = '/admin/gestion/desactivar-look-and-feel';
						msj = 'Desactivación exitosa';
					} else {
						$('#winAlertBloquearLookAndFeel #titleQ').html("¿Desea activar la vista 'look&feel'?");
						url = '/admin/gestion/activar-look-and-feel';
						msj = 'Activación exitosa';
					}
					
					$('#winAlertBloquearLookAndFeel .yesCM').attr({
						'rel': $.trim(idEmp),
            'token' : t.attr('data-token'),
						'url': url,
						'msj': msj
						});
				});
				
				var clickAccep = $('#winAlertBloquearLookAndFeel .yesCM');
				$(clickAccep).live('click',function(e){
					e.preventDefault();
					var t = $(this),
					cntMsj = t.parent();
					cntMsj.empty().addClass('loading').prev().addClass('hide');
					
					$.ajax({
						'url' : t.attr('url'),
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'idEmp' : t.attr('rel'),
              'tok' : t.attr('token')
						},
						'success' : function(res) {
							cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
							setTimeout(function(){
								document.location.reload(true);
							},500);
						},
						'error' : function(res) {
							cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
							setTimeout(function(){
								document.location.reload(true);
							},500);
						}
					});
				});
				
			},
			bloquearPostulante : function(a) {
				
				$(a).live('click', function(e,url) {
					e.preventDefault();
					var t = $(this);
					var idPost = t.attr('rel');
					var url = '', msj ='';
					
					if(t.hasClass('blockAdmEMP')) {
						$('#winAlertBloquearPost #titleQ').html(msgs.cBlockPostulante.block);
						url = '/admin/gestion/bloquear-postulante';
						msj = msgs.cMsjBlock.block;
					} else {
						$('#winAlertBloquearPost #titleQ').html(msgs.cBlockPostulante.desblock);
						url = '/admin/gestion/desbloquear-postulante';
						msj = msgs.cMsjBlock.desblock;
					}
					
					$('#winAlertBloquearPost .yesCM').attr(
							{'rel': $.trim(idPost),
							 'url': url,
							 'msj': msj
							});
				});
				
				var clickAccep = $('#winAlertBloquearPost .yesCM');
				$(clickAccep).live('click', function(e, url){
					e.preventDefault();
					var t = $(this),
					cntMsj = t.parent();
					cntMsj.empty().addClass('loading').prev().addClass('hide');
					$.ajax({
						'url' : t.attr('url'),
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'idPost' : t.attr('rel')
						},
						'success' : function(res) {
							cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
							setTimeout(function(){
								document.location.reload(true);
							},500);
						},
						'error' : function(res) {
							cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
						}
					});
					
				});
				
			},
			
			bloquearEmpresa : function(a) {
				$(a).live('click', function(e) {
					e.preventDefault();
					var t = $(this);
					var idEmp = t.attr('rel');
					var url = '', msj ='';
					
					if(t.hasClass('blockEmp')) {
						$('#winAlertBloquearEmp #titleQ').html(msgs.cBlockEmpresa.block);
						url = '/admin/gestion/bloquear-empresa';
						msj = msgs.cMsjBlock.block;
					} else {
						$('#winAlertBloquearEmp #titleQ').html(msgs.cBlockEmpresa.desblock);
						url = '/admin/gestion/desbloquear-empresa';
						msj = msgs.cMsjBlock.desblock;
					}
					
					$('#winAlertBloquearEmp .yesCM').attr({
						'rel': $.trim(idEmp),
            'token' : t.attr('data-token'),
						'url': url,
						'msj': msj
						});
				});
				
				var clickAccep = $('#winAlertBloquearEmp .yesCM');
				
				$(clickAccep).live('click',function(e){
					e.preventDefault();
					var t = $(this),
					cntMsj = t.parent();
					cntMsj.empty().addClass('loading').prev().addClass('hide');
					
					$.ajax({
						'url' : t.attr('url'),
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'idEmp' : t.attr('rel'),
              'tok' : t.attr('token')
						},
						'success' : function(res) {
							cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
							setTimeout(function(){
								document.location.reload(true);
							},500);
						},
						'error' : function(res) {
							cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
							setTimeout(function(){
								document.location.reload(true);
							},500);
						}
					});
				});
			},
			
			bloquearAviso : function(a) {
				$(a).live('click', function(e) {
					e.preventDefault();
					var t = $(this);
					var idEmp = t.attr('rel');
					var url = '', msj ='';
					
					if(t.hasClass('blockAviso')) {
						$('#winAlertBloquearAviso #titleQ').html(msgs.cBlockAviso.block);
						url = '/admin/gestion/bloquear-aviso';
						msj = msgs.cMsjBlock.block;
					} else {
						$('#winAlertBloquearAviso #titleQ').html(msgs.cBlockAviso.desblock);
						url = '/admin/gestion/desbloquear-aviso';
						msj = msgs.cMsjBlock.desblock;
					}
					
					$('#winAlertBloquearAviso .yesCM').attr({
						'rel' : $.trim(idEmp),
						'url' : url,
						'msj' : msj
						});
				});
				
				var clickAccep = $('#winAlertBloquearAviso .yesCM');
				
				$(clickAccep).live('click', function(e){
					e.preventDefault();
					var t = $(this),
					cntMsj = t.parent();
					cntMsj.empty().addClass('loading').prev().addClass('hide');
					$.ajax({
						'url' : t.attr('url'),
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'idEmp' : t.attr('rel')
						},
						'success' : function(res) {
							cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
							setTimeout(function(){
								document.location.reload(true);
							},500);
						},
						'error' : function(res) {
							if (res == null) {
								cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
							} else {
								cntMsj.removeClass('loading good').addClass('bad').text('La empresa que publicó este aviso se encuentra bloqueada.');
							}
							setTimeout(function(){
								document.location.reload(true);
							},500);
							
						}
					});
				});
			},			
			ventanaAnadirMensaje : function(a, b) {
				$(a).live("click", function(evt) {
					evt.preventDefault();
					$(b).addClass("loading");
					var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					var arreglo_valores =[];
					var id = 0;
					$.each(valores, function(index,item) {
						id = $(item).attr("id");
						arreglo_valores.push(id);
					});
					var cuerpo = $("#areaMsjProEPA").val();
					var idPost = $(".idPoHidden").val();
					var token = $("#token").val();
					var tipo_mensaje = $("#tipomensaje").is(":checked");
					var json = {
						"cuerpo":cuerpo,
						"token":token,
						"tipo_mensaje":tipo_mensaje,
						"postulaciones":arreglo_valores,
						"idPost": idPost
					};
					$.ajax({
						type: "POST",
						url: "/admin/gestion/agregar-mensaje",
						data: json,
						dataType: "html",
						success: function(msg) {
							$(b).removeClass("loading");
							if(msg=="") {
								$(".closeWM").click();
								//verproceso.mostrarMensaje("#mensajesVerProceso","success","Mensajes enviados correctamente");
							} else {
								$(b).html(msg);
								$("#msgErrorMensaje").removeClass("hide");
							}
						}
					});

				});
			},
			
			anadirMensaje: function(a) {
				$(a).live("click", function(e) {
					e.preventDefault();
					var idpostulacion = $(this).attr("rel");
					var contenido = $("#content-winAnadirMensaje");
					contenido.html("");
					$(contenido).addClass("loading");
					$.ajax({
						type: "GET",
						url: "/admin/gestion/agregar-mensaje",
						data: "idPost="+idpostulacion,
						dataType: "html",
						success: function(msg) {
							contenido.removeClass("loading hide");
							contenido.html(msg);
							$('#areaMsjProEPA').unbind();
							adminPost.charArea('#areaMsjProEPA','#cantMsjProEPA',300);

						}
					});
				});
			},
			
			//Funcion de Fecha
			deleteMesLoad : function(){
				var selectAll = $('#monthjFunctions'),
				countOpt, anioOpt, bucle = 12 - vars.vMonthCurrent;
				$.each(selectAll, function(i, v){	
					countOpt = $(selectAll[i]).children('option').size(),
					anioOpt = $(selectAll[i]).next().val(); 
					if( Number(anioOpt) == vars.vYearCurrent ){
						for(var x = 0; x <= bucle; x++){
							$(selectAll[i]).children('option').eq(12-x).remove().end();
						}	
					}
				});			
			},	
			
			jFunctions : function(object){
				
				var inputValue = $('#fBirthDate');	
				
				if(inputValue.size()>0){

					var mesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
					//inicioAnio = 1910,
					inicioAnio = urls.fMinDate,
					//años a restar,
					finResto = 15,
					//finAnio = 2011,
					finAnio = urls.fYearCurrent,
					separador = '/',
					//inicial = '21' + separador + '10' + separador + '1985',)> 0){
					inicial = inputValue.val(),				
					inicialPart = inicial.split(separador),
					txtDia = '-- Día --',
					txtMes = '-- Mes --',
					txtAnio = '-- Año --';		
					var dia = $('#dayjFunctions'),
					mes = $('#monthjFunctions'),
					anio = $('#yearjFunctions');
					
					//años
					var anioI = inicioAnio,
					anioE = finAnio,
					iterador = anioE - anioI;
                                
					anio.append('<option value="0">' + txtAnio  + '</option>');
					for (i = 0 ; i <= iterador; i++){
						anio.append('<option value="' + (anioI + i) + '">' + (anioI + i) + '</option>');
					}
					//meses
					var longitudMeses = mesNombres.length;	
					mes.append('<option value="0">' + txtMes  + '</option>');	
					for (var j = 0 ; j < longitudMeses; j++){
						mes.append('<option value="' + (j+1) + '">' + mesNombres[j] + '</option>');
					}
					//dias
					var longitudDias,
					valorDia = Number(inicialPart[0]),
					valorMes = Number(inicialPart[1]),
					valorAnio = Number(inicialPart[2]);		
					// Valores por defecto para valores desconocidos
					if(isNaN(valorDia)){
						valorDia = 0;
					}
					if(isNaN(valorMes)){
						valorMes = 0;
					}
					if(isNaN(valorAnio)){
						valorDia = 0;
					}									
					//bisiesto
					function anioBisiesto(anioF){
						var checkAnioF = (((anioF % 4 == 0) && (anioF % 100 != 0)) || (anioF % 400 == 0)) ? 1 : 0;
						if ( !(checkAnioF) ) { 
							return false;
						}else{ 
							return true;
						}	
					}			
					var checkAnio = anioBisiesto(valorAnio);				
					if( valorMes == 1 || valorMes == 3 || valorMes == 5 || valorMes == 7 || valorMes == 8 || valorMes == 10 || valorMes == 12 ){
						longitudDias = 31;
					}else if( valorMes == 2 ){
						if(checkAnio == true){
							longitudDias = 29;
						}else{
							longitudDias = 28;
						}
					}else if( valorMes == 4 || valorMes == 6 || valorMes == 9 || valorMes == 11 ){
						longitudDias = 30;
					}else{
						// Para valores desconocidos como = 0
						longitudDias = 31;
					}
					// longitudDias = cantidad de dias por mes y anio bisiesto	
					dia.append('<option value="0">' + txtDia  + '</option>');			
					function loopDias(longitudDias){
						for (var k = 0 ; k < longitudDias; k++){
							dia.append('<option value="' + (k+1) + '">' + (k+1) + '</option>');
						}		
					}
					loopDias(longitudDias);			
					if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
						// IE 6 Fuck
						setTimeout(function(){
							//focus init valor select			
							dia.val(valorDia);
							mes.val(valorMes);
							anio.val(valorAnio);				
						}, 1000);	
					}else{
						//focus init valor select	
						dia.val(valorDia);
						mes.val(valorMes);
						anio.val(valorAnio);	
					}				
					//change dia
					dia.bind('change', function(){
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);				
					});		
					//change mes						
					mes.live('change', function(){
						var t = $(this),
						longitudChangeDias,
						nMes = parseInt(t.val()),
						nAnioM = parseInt(anio.val());
						checkMesAnioBisiesto = anioBisiesto(nAnioM);
						if( nMes ==  2){
							if(checkMesAnioBisiesto == true){
							longitudChangeDias = 29;
							}else{
							longitudChangeDias = 28;
							}
						}else if( nMes == 1 || nMes == 3 || nMes == 5 || nMes == 7 || nMes == 8 || nMes == 10 || nMes == 12 ){
							longitudChangeDias = 31;
						}else if( nMes == 4 || nMes == 6 || nMes == 9 || nMes == 11 ){
							longitudChangeDias = 30;
						}			
						// loop dias
						var diaActualizado = parseInt(dia.children('option:last').val());
						actualizandoDias(dia, diaActualizado, longitudChangeDias);									
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);										
					});			
					//change año
					anio.live('change', function(){
						var t = $(this),
						longitudChangeAnioDias,
						nAnio = parseInt(t.val()),
						anioBisiestoChange = anioBisiesto(nAnio),
						nMesY = parseInt(mes.val());
						// loop dias Febrero
						if( nMesY == 2 ){
							if(anioBisiestoChange == true){
								longitudChangeAnioDias = 29;
							}else{
								longitudChangeAnioDias = 28;
							}
							//loop dias
							var diaActualizadoAnio = parseInt(dia.children('option:last').val());
							actualizandoDias(dia, diaActualizadoAnio, longitudChangeAnioDias);					
						}				
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);											
					});							
					//cambio de fecha
					function fechaDefault(dia, mes, anio){				
						if( inputValue.size() == 1 ){
							inputValue.val(dia.val() + separador + mes.val() + separador + anio.val());
							var splitStr = inputValue.val().split('/'),	
							str1 = splitStr[0],
							str2 = splitStr[1],
							str3 = splitStr[2];
							if ((str1.split(''))[0].indexOf('0') == -1 && (str2.split(''))[0].indexOf('0') == -1 && (str3.split(''))[0].indexOf('0') == -1){					
								if(Number(str3) == urls.fYearCurrent && 
									Number(str2) == urls.fMonthCurrent &&
									Number(str1) > urls.fDayCurrent){
									//Esta mal si marcan mayor a la fecha actual -- El dia Mal
									inputValue.removeClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
			            //esta mal													
								}else if(Number(str3) == urls.fYearCurrent && 
												Number(str2) > urls.fMonthCurrent){						
									//Esta mal si marcan mayor a la fecha actual -- El mes Mal
									inputValue.removeClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
			            //esta mal																			
							}else{
									inputValue.addClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('bad').addClass('good').text(msgs.cBirth.good);
			            //esta ok							
								}                     
							}else{
								inputValue.removeClass('ready');
		            inputValue.parents(".block").find(".response").removeClass('good bad').addClass('def').text(msgs.cBirth.def);
		            //inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.bad);
		            //esta mal           
							}
							//fin add ready																				 
						}	
					}			
					//actualizando los dias
					function actualizandoDias(dia, diaActualizado, longitudChangeDias){
						if(diaActualizado > longitudChangeDias){
							for(var x = diaActualizado; x > longitudChangeDias; x--){
								dia.children('option').eq(x).remove();
							}	
						}
						if(diaActualizado < longitudChangeDias){
							for(var z = diaActualizado; z < longitudChangeDias; z++){
								dia.append('<option value="' + (z + 1) + '">' + (z + 1) + '</option>');
							}
						} 						
					}
					//fin			
				}			
			},	
                        			jFunctionsFin : function(object){
				
				var inputValue = $('#fBirthDateFin');	
				
				if(inputValue.size()>0){

					var mesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
					//inicioAnio = 1910,
					inicioAnio = urls.fMinDate,
					//años a restar,
					finResto = 15,
					//finAnio = 2011,
					finAnio = urls.fYearCurrent,
					separador = '/',
					//inicial = '21' + separador + '10' + separador + '1985',)> 0){
					inicial = inputValue.val(),				
					inicialPart = inicial.split(separador),
					txtDia = '-- Día --',
					txtMes = '-- Mes --',
					txtAnio = '-- Año --';		
					var dia = $('#dayjFunctionsFin'),
					mes = $('#monthjFunctionsFin'),
					anio = $('#yearjFunctionsFin');
					
					//años
					var anioI = inicioAnio,
					anioE = finAnio,
					iterador = anioE - anioI;
					anio.append('<option value="0">' + txtAnio  + '</option>');
					for (i = 0 ; i <= iterador; i++){
						anio.append('<option value="' + (anioI + i) + '">' + (anioI + i) + '</option>');
					}
					//meses
					var longitudMeses = mesNombres.length;	
					mes.append('<option value="0">' + txtMes  + '</option>');	
					for (var j = 0 ; j < longitudMeses; j++){
						mes.append('<option value="' + (j+1) + '">' + mesNombres[j] + '</option>');
					}
					//dias
					var longitudDias,
					valorDia = Number(inicialPart[0]),
					valorMes = Number(inicialPart[1]),
					valorAnio = Number(inicialPart[2]);		
					// Valores por defecto para valores desconocidos
					if(isNaN(valorDia)){
						valorDia = 0;
					}
					if(isNaN(valorMes)){
						valorMes = 0;
					}
					if(isNaN(valorAnio)){
						valorDia = 0;
					}									
					//bisiesto
					function anioBisiesto(anioF){
						var checkAnioF = (((anioF % 4 == 0) && (anioF % 100 != 0)) || (anioF % 400 == 0)) ? 1 : 0;
						if ( !(checkAnioF) ) { 
							return false;
						}else{ 
							return true;
						}	
					}			
					var checkAnio = anioBisiesto(valorAnio);				
					if( valorMes == 1 || valorMes == 3 || valorMes == 5 || valorMes == 7 || valorMes == 8 || valorMes == 10 || valorMes == 12 ){
						longitudDias = 31;
					}else if( valorMes == 2 ){
						if(checkAnio == true){
							longitudDias = 29;
						}else{
							longitudDias = 28;
						}
					}else if( valorMes == 4 || valorMes == 6 || valorMes == 9 || valorMes == 11 ){
						longitudDias = 30;
					}else{
						// Para valores desconocidos como = 0
						longitudDias = 31;
					}
					// longitudDias = cantidad de dias por mes y anio bisiesto	
					dia.append('<option value="0">' + txtDia  + '</option>');			
					function loopDias(longitudDias){
						for (var k = 0 ; k < longitudDias; k++){
							dia.append('<option value="' + (k+1) + '">' + (k+1) + '</option>');
						}		
					}
					loopDias(longitudDias);			
					if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
						// IE 6 Fuck
						setTimeout(function(){
							//focus init valor select			
							dia.val(valorDia);
							mes.val(valorMes);
							anio.val(valorAnio);				
						}, 1000);	
					}else{
						//focus init valor select	
						dia.val(valorDia);
						mes.val(valorMes);
						anio.val(valorAnio);	
					}				
					//change dia
					dia.bind('change', function(){
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);				
					});		
					//change mes						
					mes.live('change', function(){
						var t = $(this),
						longitudChangeDias,
						nMes = parseInt(t.val()),
						nAnioM = parseInt(anio.val());
						checkMesAnioBisiesto = anioBisiesto(nAnioM);
						if( nMes ==  2){
							if(checkMesAnioBisiesto == true){
							longitudChangeDias = 29;
							}else{
							longitudChangeDias = 28;
							}
						}else if( nMes == 1 || nMes == 3 || nMes == 5 || nMes == 7 || nMes == 8 || nMes == 10 || nMes == 12 ){
							longitudChangeDias = 31;
						}else if( nMes == 4 || nMes == 6 || nMes == 9 || nMes == 11 ){
							longitudChangeDias = 30;
						}			
						// loop dias
						var diaActualizado = parseInt(dia.children('option:last').val());
						actualizandoDias(dia, diaActualizado, longitudChangeDias);									
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);										
					});			
					//change año
					anio.live('change', function(){
						var t = $(this),
						longitudChangeAnioDias,
						nAnio = parseInt(t.val()),
						anioBisiestoChange = anioBisiesto(nAnio),
						nMesY = parseInt(mes.val());
						// loop dias Febrero
						if( nMesY == 2 ){
							if(anioBisiestoChange == true){
								longitudChangeAnioDias = 29;
							}else{
								longitudChangeAnioDias = 28;
							}
							//loop dias
							var diaActualizadoAnio = parseInt(dia.children('option:last').val());
							actualizandoDias(dia, diaActualizadoAnio, longitudChangeAnioDias);					
						}				
						//escrbiendo fecha en input
						fechaDefault(dia, mes, anio);											
					});							
					//cambio de fecha
					function fechaDefault(dia, mes, anio){				
						if( inputValue.size() == 1 ){
							inputValue.val(dia.val() + separador + mes.val() + separador + anio.val());
							var splitStr = inputValue.val().split('/'),	
							str1 = splitStr[0],
							str2 = splitStr[1],
							str3 = splitStr[2];
							if ((str1.split(''))[0].indexOf('0') == -1 && (str2.split(''))[0].indexOf('0') == -1 && (str3.split(''))[0].indexOf('0') == -1){					
								if(Number(str3) == urls.fYearCurrent && 
									Number(str2) == urls.fMonthCurrent &&
									Number(str1) > urls.fDayCurrent){
									//Esta mal si marcan mayor a la fecha actual -- El dia Mal
									inputValue.removeClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
			            //esta mal													
								}else if(Number(str3) == urls.fYearCurrent && 
												Number(str2) > urls.fMonthCurrent){						
									//Esta mal si marcan mayor a la fecha actual -- El mes Mal
									inputValue.removeClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
			            //esta mal																			
							}else{
									inputValue.addClass('ready');
			            inputValue.parents(".block").find(".response").removeClass('bad').addClass('good').text(msgs.cBirth.good);
			            //esta ok							
								}                     
							}else{
								inputValue.removeClass('ready');
		            inputValue.parents(".block").find(".response").removeClass('good bad').addClass('def').text(msgs.cBirth.def);
		            //inputValue.parents(".block").find(".response").removeClass('good').addClass('bad').text(msgs.cBirth.bad);
		            //esta mal           
							}
							//fin add ready																				 
						}	
					}			
					//actualizando los dias
					function actualizandoDias(dia, diaActualizado, longitudChangeDias){
						if(diaActualizado > longitudChangeDias){
							for(var x = diaActualizado; x > longitudChangeDias; x--){
								dia.children('option').eq(x).remove();
							}	
						}
						if(diaActualizado < longitudChangeDias){
							for(var z = diaActualizado; z < longitudChangeDias; z++){
								dia.append('<option value="' + (z + 1) + '">' + (z + 1) + '</option>');
							}
						} 						
					}
					//fin			
				}			
			},	
			dividirAviso : function (a){
				$(a).bind("click", function(e) {
					e.preventDefault();
					var idAviso= $(this).attr("rel");
					var contenido = $("#content-winDividirAviso");
					contenido.html("");
					contenido.addClass("loading");
					 $.ajax({
						 type: "GET",
						 url: "/admin/aviso-preferencial/dividir-aviso/",
						 data: "idAv="+idAviso,
						 dataType: "html",
						 success: function(msg) {
						 contenido.removeClass("loading hide");
						 contenido.html(msg);
						 var formDivAv = $('#formDivAvi');
						 adminPost.submitdividirAviso('#submitDividirAviso', formDivAv);
						 adminPost.addInput('#formDivAvi .iptData');
						}
					});
				});
			},			
			submitdividirAviso : function (ipt, form){
				$(ipt).bind("click", function(e) {
					e.preventDefault();
					var idAviso = $('#nuevoId_1').attr('rel');
					var dataString = form.serialize();
					var contenido = $("#content-winDividirAviso");
					contenido.html("");
					contenido.addClass("loading");
					$.ajax({
						type: "POST",
						url: "/admin/aviso-preferencial/dividir-aviso",
						data: {
							idAv : idAviso,
							dataStr : dataString 
							},
						dataType: "html",
						success: function(res) {
							
							contenido.html(res);
							var url = contenido.find('#NDirec').attr('url');
							
							contenido.html("");
							contenido.removeClass("loading hide");
							contenido.html('<div class="good">' + msgs.cDividir.good + '</div>');
								setTimeout(function(){
									window.location = url; 
								}, 1000);
							
						}
					});
				});
			},
			addInput : function(ipts){
				$(ipts).live('keydown', function(){
					var t = $(this),
					spedd = 'fast',
					size = $.trim(t.val()).length;
					if(size >= 3){
						//t.parents('.cntBlockIpt').next().fadeIn(spedd).removeAttr('style');
						t.parents('.cntBlockIpt').next().removeClass('hide');
					}
				})
				$(ipts).live('blur', function(){
					var t = $(this),
					spedd = 'fast',
					size =$.trim(t.val()).length;
					
					var ulItems = $('#ulListDivs'),
					itemsHide = $('#ulListDivs li.hide');
					$.each(itemsHide, function(i,v){
						//$('#ulListDivs li.hide').eq(i).remove();
						ulItems.append(itemsHide[i]);
						
					});
					
					if(size == 0){
						if(!(t.hasClass('noHide'))){
							//t.parents('.cntBlockIpt').fadeOut(spedd).css('display','none');
							t.parents('.cntBlockIpt').addClass('hide');

							$.each($('#ulListDivs .iptData'), function(i,v){
								$(v).attr({
									'id': 'nuevoId_' + (i + 1),
									'name': 'nuevoId_' + (i+1)
								});
							});
						}
					}
				});
				$(ipts).bind('paste', function(){
					var t = $(this),
					spedd = 'fast';
					setTimeout(function(){
						var size = $.trim(t.val()).length;
						if(size >= 3){
							t.parents('.cntBlockIpt').next().fadeIn(spedd).removeAttr('style');
						}
					},0);
				});
			},
			
			charArea : function(area,num,chars) {
				var trigger = $(area);
				$(num).html(chars);
				trigger.bind('keyup click blur focus change paste', function(e) {
					var t = $(this),
					countN = $(num),
					valueArea;
					countN.html(chars);
					var key = e.keyCode || e.charCode || e.which || window.e ;
					var length = t.val().length;
					countN.html( (chars - length) + ' ' );
					if( length > chars ) {
						valueArea = t.val().substring(chars, '') ;
						trigger.val(valueArea);
						countN.html('0');
					}
				});
			},
			btnSubmit : function(btns, iptData,frm){
				var btn = $(btns);
				btn.on('click', function(e){
					e.preventDefault();
					var t = $(this),
					dataAttr = t.attr('data-tipo');
					$(iptData).attr('value',dataAttr);
					$(frm).submit();
				});
			},
			anclaUbigeo : function(ancla, jash){
				var aAncla = $(ancla);
				if($.trim(window.location.hash) == jash){
					$('html, body').animate({
                       scrollTop: aAncla.offset().top
                    }, 'slow');					
				}
			}
		};
		/* Init */
		adminPost.deleteMesLoad();
		adminPost.jFunctions();	
                adminPost.jFunctionsFin();
		adminPost.bloquearPostulante('a.pos');
		adminPost.bloquearLookAndFeel('a.empLookAndFeel');
		adminPost.bloquearEmpresa('a.emp');
		adminPost.bloquearAviso('a.avi');
		adminPost.anadirMensaje('.enviarMensaje');
		adminPost.ventanaAnadirMensaje("#guardarMensajeVerProceso", "#content-winAnadirMensaje");
		adminPost.dividirAviso('a[href="#winDividirAviso"]');
		adminPost.charArea('#areaMsjProEPA','#cantMsjProEPA',300);
		adminPost.btnSubmit('.btnSaveCDAP','#dup','#frmPublishAd');
		adminPost.anclaUbigeo('#aListubi','#listubi');
});