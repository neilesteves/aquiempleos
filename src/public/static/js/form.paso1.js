/*
 Registro postulante Paso 1
 */
$(function() {
	var msgs = {
		cDef: {
			good: '.',
			bad: 'Campo Requerido',
			def: 'Opcional'
		},
		cEmail: {
			good: '.',
			bad: 'No parece ser un correo electrónico válido.',
			def: 'Ingrese e-mail correcto',
			mailValid: 'Email ya registrado.'
		},
		cPass: {
			good: '¡OK! Verifica la seguridad de tu clave',
			bad: 'Usa de 6 a 32 caracteres',
			def: 'Usa de 6 a 32 caracteres ¡Sé ingenioso!',
			sec: {
				msgDef: 'Nivel de seguridad',
				msg1: 'Demasiado corta',
				msg2: 'Débil',
				msg3: 'Fuerte',
				msg4: 'Muy fuerte'
			}
		},
		cRePass: {
			good: '.',
			bad: 'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.',
			def: 'Tienen que ser iguales'
		},
		cPhoto: {
			good: '.',
			bad: 'No subio la foto',
			def: 'Sube tu foto'
		},
		cName: {
			good: '.',
			bad: '¡Se requiere tu nombre!',
			def: 'Ingrese nombre correcto'
		},
//		cApell : {
//			good : 'El Apellido se ve genial.',
//			bad : '¡Se requiere tu apellido!',
//			def : 'Ingrese apellido correcto'
//		},
		cApellP: {
			good: '.',
			bad: '¡Se requiere tu apellido paterno!',
			def: 'Ingrese apellido correcto'
		},
		cApellM: {
			good: '.',
			bad: '¡Se requiere tu apellido materno!',
			def: 'Ingrese apellido correcto'
		},
		cBirth: {
			good: '.',
			bad: '¡Se requiere su fecha de nacimiento!',
			def: 'Ingrese su fecha de nacimiento completa.',
			exed: 'Incorrecto!. La fecha de nacimiento seleccionada es mayor a la fecha actual'
		},
		cSexo: {
			good: '.',
			bad: '¿Femenino o Masculino?',
			def: 'Defina su sexo'
		},
		cTlfNum: {
			good: '.',
			bad: 'Incorrecto',
			def: 'Ingrese Número Celular'
		},
		cSDoc: {
			good: '.',
			bad: '',
			def: '¡OK!'
		},
		cDocNum: {
			good: '.',
			bad: 'Incorrecto',
			def: 'Ingrese número de Documento',
			docNumValid: 'Numero de Doc. ya registrado.'
		},
		cECivil: {
			good: '.',
			bad: '',
			def: '¡OK!'
		},
		cPais: {
			good: '.',
			bad: 'Selecciona país',
			def: '.'
		},
		cDepa: {
			good: '.',
			bad: 'Selecciona Departamento',
			def: '.'
		},
		cDist: {
			good: '.',
			bad: 'Selecciona Distrito',
			def: '.'
		},
		cProv: {
			good: '.',
			bad: 'Selecciona Provincia',
			def: '.'
		}
	};
	var vars = {
		rs: '.response',
		okR: 'ready',
		sendFlag: 'sendN',
		loading: '<div class="loading"></div>'
	};
	var formP1 = {
		fMail: function(a, good, bad, def) {
			$(a).bind('blur', function() {
				var t = $(this),
					r = t.next(vars.rs),
					regEx = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g,
					value = t.val();

				if (value.match(regEx) && value !== '') {
					formP1._fMailValid(a, t, r);
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.addClass('bad-data');
				}
			});
		},
		_fMailValid: function(a, t, r) {
			var $email = t;
			r.text('');
			$email.addClass('loadingMail');
			var csrfHash = "";
			$.ajax({
				url: '/registro/obtener-token/',
				type: 'POST',
				dataType: 'json',
				data: {csrfhash: $('body').attr('data-hash')},
				success: function(result) {

					csrfHash = result;
					$.ajax({
						url: '/registro/validar-email/',
						type: 'post',
						data: {
							email: $email.val(),
							rol: $(a).attr('rol'),
							token: csrfHash,
							modulo: 'postulante'
						},
						dataType: 'json',
						success: function(response) {
							if (response.status == true) {
								$email.removeClass('loadingMail bad-data');
								r.removeClass('bad def').addClass('good').text(msgs.cEmail.good);
							} else {
								$email.removeClass('loadingMail').addClass('bad-data');
								r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
							}
						},
						error: function(response) {
							$email.removeClass('loadingMail bad-data');
							r.removeClass('good def').addClass('bad').text(msgs.cEmail.mailValid);
						}
					});
				}
			});
		},
		fDni: function(oP) {
			var dom = {},
			st = {
				selTipo     : '#fSelDoc',
				txtDni      : '#fNDoc',
				response    : '.response',
				regEx       : null
			},
			catchDom = function() {
				dom.selTipo = $(st.selTipo);
				dom.txtDni = $(st.txtDni);
			},
			suscribeEvents = function(){
				dom.selTipo.on('change', onChangeVal);
				dom.txtDni.on('keyup', validateKey);
				dom.txtDni.on('blur', validateJson);

				st.regEx = onChangeVal();
			},
			onChangeVal = function(){
				var value = dom.selTipo.val(),
					regEx, min, max;
				switch(value){
					case "dni#8":
						regEx = /[^0-9]/g;
						dom.txtDni.attr('min', 7);
						dom.txtDni.attr('max', 8);
						dom.txtDni.attr('maxlength',8);
					break;
					case "ce#15":
						regEx = /[^a-zA-Z0-9]/g;
						dom.txtDni.attr('min', 8);
						dom.txtDni.attr('max', 20);
						dom.txtDni.attr('maxlength',20);
					break;
				}
				return regEx;
			},
			validateKey = function(){
				var _this = $(this),
					value = _this.val(),
					regEx = st.regEx;

				if (value.match(regEx)) {
					_this.val(value.replace(regEx, ''));
				}else{
					return false;
				}
				e.preventDefault();
			},
			validateJson = function(){
				var _this = $(this),
					min = _this.attr('min'),
					max = _this.attr('max'),
					value = _this.val(),
					response = _this.siblings(st.response);

				if(value.length >= min && value.length <= max){
					_this.removeClass('bad-data');
					response.removeClass('bad').text('');
				}else{
					_this.addClass('bad-data');
					response.addClass('bad').text('Campo incorrecto');
					return false;
				}
				formP1._fDniValid(st.txtDni, _this, response);
			};

			$.extend(st, oP);
			catchDom();
			suscribeEvents();
		},
		_fDniValid: function(a, t, r) {
			var $ndoc = t;
			r.text('');
			$ndoc.addClass('loadingNumDoc bad-data');
			var idPost = $ndoc.attr('rel');
                        var csrfHash = "";
			$.ajax({
				url: '/registro/obtener-token/',
				type: 'POST',
				dataType: 'json',
				data: {csrfhash: $('body').attr('data-hash')},
				success: function(result) {
                                    csrfHash = result;
                                    		
                            $.ajax({
                                    url: '/registro/validar-dni/',
                                    type: 'post',
                                    data: {
                                            ndoc: $ndoc.val(),
                                            token: csrfHash,
                                            idPost: idPost
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        //$("#auth_token").val(csrfHash);
                                            if (response.status == true) {
                                                    $ndoc.removeClass('loadingNumDoc bad-data');
                                                    r.removeClass('bad def').addClass('good').text(msgs.cDocNum.good);
                                                    //$('#auth_token').val(csrfHash);
                                            } else {
                                                    $ndoc.removeClass('loadingNumDoc').addClass('bad-data');
                                                    r.removeClass('good def').addClass('bad').text(msgs.cDocNum.docNumValid);
                                                    //$('#auth_token').val(csrfHash);
                                            }
                                    },
                                    error: function(response) {
                                            $ndoc.removeClass('loadingNumDoc bad-data');
                                            r.removeClass('good def').addClass('bad').text(msgs.cDocNum.docNumValid);
                                    }
                            });
                                }});
		},
		fPass: function(a, b, c) {
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
			$(a).keyup(function() {
				var t = $(this),
						v = $(this).val(),
						r = t.siblings(vars.rs);
				if (v.length >= (b)) {
					r.removeClass('bad').addClass('good').text(good);
					if (ep.test(t.val())) {
						pf1.removeClass('bgRed bgGreen').addClass('bgYellow');
						pf2.removeClass('bgRed bgGreen').addClass('bgYellow');
						pf3.removeClass('bgRed bgGreen');
						pf4.removeClass('bgGreen');
						msg.text(msg2);

						if (epMinC.test(t.val()) || epMayC.test(t.val()) || epEspC.test(t.val())) {
							pf1.removeClass('bgRed bgYellow').addClass('bgGreen');
							pf2.removeClass('bgRed bgYellow').addClass('bgGreen');
							pf3.removeClass('bgYellow').addClass('bgGreen');
							pf4.removeClass('bgGreen');
							msg.text(msg3);
						}
						if (epMay.test(t.val()) && epNum.test(t.val()) && epEsp.test(t.val())) {
							pf1.removeClass('bgRed bgYellow').addClass('bgGreen');
							pf2.removeClass('bgRed bgYellow').addClass('bgGreen');
							pf3.removeClass('bgYellow').addClass('bgGreen');
							pf4.addClass('bgGreen');
							msg.text(msg4);
						}
					}
					t.removeClass('bad-data');
				} else {
					r.removeClass('good bad').text(def);
					pf1.addClass('bgRed').removeClass('bgYellow bgGreen');
					pf2.removeClass('bgYellow bgGreen');
					pf3.removeClass('bgGreen');
					pf4.removeClass('bgGreen');
					msg.text(msg1);
					t.addClass('bad-data');
				}
				if (v.length == 0) {
					pf1.removeClass('bgRed bgYellow');
					pf2.removeClass('bgRed bgYellow');
					msg.text(msgDef);
				}
				var cc = $(c);
				if (cc.val().length > 0) {
					rr = cc.next(vars.rs);
					if (cc.val() !== t.val()) {
						rr.removeClass('god bad').text(msgs.cRePass.def);
					} else {
						rr.removeClass('bad').addClass('good').text(msgs.cRePass.good);
					}
				}
			}).blur(function() {
				var t = $(this);
				r = t.siblings(vars.rs);
				if (t.val().length >= b) {
					r.removeClass('bad').addClass('good').text(good);
					t.removeClass('bad-data');
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.addClass('bad-data');
				}
			});
		},
		fRePass: function(a, b, c) {
			var good = msgs.cRePass.good,
					bad = msgs.cRePass.bad,
					def = msgs.cRePass.def,
					r = $(a).next(vars.rs);
			$(a).keyup(function() {
				var t = $(this);
				if (t.val().length >= c) {
					if (t.val() === $(b).val()) {
						r.removeClass('bad').addClass('good').text(good);
						t.removeClass('bad-data');
					} else {
						r.removeClass('good bad').text(def);
						t.addClass('bad-data');
					}
				} else {
					r.removeClass('good bad').text(def);
					t.addClass('bad-data');
				}
			}).blur(function() {
				var t = $(this);
				if (t.val().length >= c) {
					if (t.val() !== $(b).val()) {
						r.removeClass('good').addClass('bad').text(bad);
						t.addClass('bad-data');
					} else {
						r.removeClass('bad').addClass('good').text(good);
						t.removeClass('bad-data');
					}
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.addClass('bad-data');
				}
			});
		},
		fIDate: function(a, good, bad, def) {
			var A = $(a),
				r = A.siblings(vars.rs);

			A.change(function() {
				var t = $(this);
				r.removeClass('bad').addClass('good').text(good);
				t.removeClass('bad-data');
			});
		},
		fUbi: function(a, b, c, d) {
			var A = $(a),
					B = $(b),
					C = $(c),
					idProvincia = $(d),
					r = $.trim(A.attr('rel')),
					rB = $.trim(B.attr('rel')),
					attrCallao = $.trim(idProvincia.attr('idCallao'));
			var paisCargado = $.trim($(a + ' option:selected').val());
			var ciudadCargado = $.trim($(b + ' option:selected').val());
			var provinciaCargado = $.trim($(d + ' option:selected').val());

			if (paisCargado != r && paisCargado != '0') {
				B.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
				idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
				B.next().html('&nbsp;').removeClass('bad good');
				C.next().html('&nbsp;').removeClass('bad good');
				idProvincia.next().html('&nbsp;').removeClass('bad good');

				A.removeClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.removeClass('bad-data');

			} else if (paisCargado == '0') {

				B.next(vars.rs).removeClass('god bad').text('');
				B.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
				C.next(vars.rs).removeClass('god bad').text('');
				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
				idProvincia.next(vars.rs).removeClass('god bad').text('');
				idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				A.addClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.removeClass('bad-data');

			} else if (paisCargado == r &&
					(ciudadCargado != rB &&
							ciudadCargado != '0')) {
				C.next(vars.rs).removeClass('god bad').text('');
				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				idProvincia.next(vars.rs).removeClass('god bad').text('');
				idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				A.removeClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.removeClass('bad-data');

			} else if (paisCargado == r &&
					(ciudadCargado == '0')) {
				C.next(vars.rs).removeClass('god bad').text('');
				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				idProvincia.next(vars.rs).removeClass('god bad').text('');
				idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				A.removeClass('bad-data');
				B.addClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.removeClass('bad-data');

			} else if (paisCargado == r &&
					(ciudadCargado == rB &&
							provinciaCargado != '0' &&
							provinciaCargado != attrCallao &&
							provinciaCargado != idProvincia.attr('rel'))) {
				C.next(vars.rs).removeClass('god bad').text('');

				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				A.removeClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.removeClass('bad-data');

			} else if (paisCargado == r &&
					(ciudadCargado == rB &&
							provinciaCargado == '0')) {
				C.next(vars.rs).removeClass('god bad').text('');

				C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');

				A.removeClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');
				C.addClass('bad-data');

			} else if (paisCargado == r) {

				A.removeClass('bad-data');
				B.removeClass('bad-data');
				idProvincia.removeClass('bad-data');

			}

			A.bind('change', function() {
				var t = $(this);
				if (t.val() == r) {
					B.removeAttr('disabled');
					B.siblings('label').removeClass('noReq').children('span').html('* ');

					A.removeClass('bad-data');
					B.addClass('bad-data');
					C.removeClass('bad-data');
					idProvincia.removeClass('bad-data');

				} else {
					B.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					B.next().html('&nbsp;').removeClass('bad good');
					C.next().html('&nbsp;').removeClass('bad good');

					idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					idProvincia.next().html('&nbsp;').removeClass('bad good');

					A.removeClass('bad-data');
					B.removeClass('bad-data');
					C.removeClass('bad-data');
					idProvincia.removeClass('bad-data');

				}
				if (t.val() == '0') {
					A.addClass('bad-data');
				} else {
					A.removeClass('bad-data');
				}
			});
			//Departamento
			B.bind('change', function() {
				var t = $(this);
                                                                        

				if (t.val() == t.attr('rel')) {
					idProvincia.removeAttr('disabled');
					idProvincia.siblings('label').removeClass('noReq').children('span').html('* ');

					A.removeClass('bad-data');
					B.removeClass('bad-data');
					C.addClass('bad-data');
					idProvincia.addClass('bad-data');

				} else {
					idProvincia.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					idProvincia.addClass('bad-data').next().html('&nbsp;').removeClass('bad good');
					C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					C.addClass('bad-data').next().html('&nbsp;').removeClass('bad good');

					A.removeClass('bad-data');
					B.removeClass('bad-data');
					C.removeClass('bad-data');
					idProvincia.removeClass('bad-data');

				}
				if (t.val() == '0') {
					B.addClass('bad-data');
				} else {
					B.removeClass('bad-data');
				}
			});
                      
                        //Provincia
			idProvincia.bind('change', function() {
				var t = $(this);
                                
				if (t.val() == t.attr('idcallao') || (t.val() == t.attr('rel'))) {
					C.attr('disabled');
					C.siblings('label').removeClass('noReq').children('span').html('* ');
					C.children('option').not('option[value="0"]').remove();

					A.removeClass('bad-data');
					B.removeClass('bad-data');

					idProvincia.removeClass('bad-data');

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
								'url': '/registro/filtrar-distritos/',
								'type': 'POST',
								'dataType': 'JSON',
								'data': {
									'id_ubigeo': t.val(),
									csrfhash: csrfHash
								},
								'success': function(res) {
									$.each(res, function(i, v) {
										C.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
									});
									C.removeAttr('disabled').removeClass('bad-data bad good').next().text('');

								},
								'error': function(res) {
									//limpio options menos -1
									C.removeAttr('disabled').removeClass('bad-data bad good').next().text('');

								}
							});

						}
					})


				} else {
					C.attr('disabled', 'disabled').val('0').siblings('label').addClass('noReq').children('span').html('&nbsp;');
					C.next().html('&nbsp;').removeClass('bad good');
					idProvincia.next().html('&nbsp;').removeClass('bad good');

					A.removeClass('bad-data');
					B.removeClass('bad-data');
					C.removeClass('bad-data');
					idProvincia.removeClass('bad-data');

				}
			});
		},
		fOldPass: function(a) {
			var trigger = $(a),
					res = trigger.siblings('.response');
			if (($('body').is('#myAccount'))) {
				trigger.keyup(function() {
					res.removeClass('bad good').addClass('def').text('');
				});
			}
		},
		maxLenghtN: function(trigger) {
			var select = $(trigger),
					input = select.next();
			select.bind('change', function() {
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
				if (length > numMax) {
					valueArea = t.val().substring(numMax, '');
					input.val(valueArea);
				}
			});
		},
		deleteMesLoad: function() {
			var selectAll = $('#monthjFunctions'),
					countOpt, anioOpt, bucle = 12 - vars.vMonthCurrent;
			$.each(selectAll, function(i, v) {
				countOpt = $(selectAll[i]).children('option').size(),
						anioOpt = $(selectAll[i]).next().val();
				if (Number(anioOpt) == vars.vYearCurrent) {
					for (var x = 0; x <= bucle; x++) {
						$(selectAll[i]).children('option').eq(12 - x).remove().end();
					}
				}
			});
		},
		jFunctions: function(object) {
			var inputValue = $('#fBirthDate');
			if (inputValue.size() > 0) {
				var mesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						//inicioAnio = 1910,
						inicioAnio = urls.fMinDate,
						//años a restar,
						finResto = 15,
						//finAnio = 2011,
						finAnio = urls.fYearCurrent - finResto,
						separador = '/',
						//inicial = '21' + separador + '10' + separador + '1985',
						inicial = inputValue.val(),
						inicialPart = inicial.split(separador),
						txtDia = '-- Día --',
						txtMes = '-- Mes --',
						txtAnio = '-- Año --';
				var dia = $('#dayjFunctions'),
						mes = $('#monthjFunctions'),
						anio = $('#yearjFunctions');

				anio.on('change', function() {
					var _this = $(this);
					if (_this.val() == '0') {
						_this.val(finAnio);
					}
				});
				//años
				var anioI = inicioAnio,
						anioE = finAnio,
						iterador = anioE - anioI;
				anio.append('<option value="0">' + txtAnio + '</option>');
				for (i = 0; i <= iterador; i++) {
					anio.append('<option value="' + (anioI + i) + '">' + (anioI + i) + '</option>');
				}
				//meses
				var longitudMeses = mesNombres.length;
				mes.append('<option value="0">' + txtMes + '</option>');
				for (var j = 0; j < longitudMeses; j++) {
					mes.append('<option value="' + (j + 1) + '">' + mesNombres[j] + '</option>');
				}
				//dias
				var longitudDias,
						valorDia = Number(inicialPart[0]),
						valorMes = Number(inicialPart[1]),
						valorAnio = Number(inicialPart[2]);
				// Valores por defecto para valores desconocidos
				if (isNaN(valorDia)) {
					valorDia = 0;
				}
				if (isNaN(valorMes)) {
					valorMes = 0;
				}
				if (isNaN(valorAnio)) {
					valorDia = 0;
				}
				//bisiesto
				function anioBisiesto(anioF) {
					var checkAnioF = (((anioF % 4 == 0) && (anioF % 100 != 0)) || (anioF % 400 == 0)) ? 1 : 0;
					if (!(checkAnioF)) {
						return false;
					} else {
						return true;
					}
				}
				var checkAnio = anioBisiesto(valorAnio);
				if (valorMes == 1 || valorMes == 3 || valorMes == 5 || valorMes == 7 || valorMes == 8 || valorMes == 10 || valorMes == 12) {
					longitudDias = 31;
				} else if (valorMes == 2) {
					if (checkAnio == true) {
						longitudDias = 29;
					} else {
						longitudDias = 28;
					}
				} else if (valorMes == 4 || valorMes == 6 || valorMes == 9 || valorMes == 11) {
					longitudDias = 30;
				} else {
					// Para valores desconocidos como = 0
					longitudDias = 31;
				}
				// longitudDias = cantidad de dias por mes y anio bisiesto	
				dia.append('<option value="0">' + txtDia + '</option>');
				function loopDias(longitudDias) {
					for (var k = 0; k < longitudDias; k++) {
						dia.append('<option value="' + (k + 1) + '">' + (k + 1) + '</option>');
					}
				}
				loopDias(longitudDias);
				if ($.browser.msie && $.browser.version.substr(0, 1) < 7) {
					// IE 6 Fuck
					setTimeout(function() {
						//focus init valor select			
						dia.val(valorDia);
						mes.val(valorMes);
						anio.val(valorAnio);
					}, 1000);
				} else {
					//focus init valor select	
					dia.val(valorDia);
					mes.val(valorMes);
					anio.val(valorAnio);
				}
				//change dia
				dia.bind('change', function() {
					//escrbiendo fecha en input
					fechaDefault(dia, mes, anio);
				});
				//change mes						
				mes.live('change', function() {
					var t = $(this),
							longitudChangeDias,
							nMes = parseInt(t.val()),
							nAnioM = parseInt(anio.val());
					checkMesAnioBisiesto = anioBisiesto(nAnioM);
					if (nMes == 2) {
						if (checkMesAnioBisiesto == true) {
							longitudChangeDias = 29;
						} else {
							longitudChangeDias = 28;
						}
					} else if (nMes == 1 || nMes == 3 || nMes == 5 || nMes == 7 || nMes == 8 || nMes == 10 || nMes == 12) {
						longitudChangeDias = 31;
					} else if (nMes == 4 || nMes == 6 || nMes == 9 || nMes == 11) {
						longitudChangeDias = 30;
					}
					// loop dias
					var diaActualizado = parseInt(dia.children('option:last').val());
					actualizandoDias(dia, diaActualizado, longitudChangeDias);
					//escrbiendo fecha en input
					fechaDefault(dia, mes, anio);
				});
				//change año
				anio.live('change', function() {
					var t = $(this),
							longitudChangeAnioDias,
							nAnio = parseInt(t.val()),
							anioBisiestoChange = anioBisiesto(nAnio),
							nMesY = parseInt(mes.val());
					// loop dias Febrero
					if (nMesY == 2) {
						if (anioBisiestoChange == true) {
							longitudChangeAnioDias = 29;
						} else {
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
				function fechaDefault(dia, mes, anio) {
					if (inputValue.size() == 1) {
						inputValue.val(dia.val() + separador + mes.val() + separador + anio.val());
						// add ready // 0 valor a buscar
						var splitStr = inputValue.val().split('/'),
								str1 = splitStr[0],
								str2 = splitStr[1],
								str3 = splitStr[2];
						if ((str1.split(''))[0].indexOf('0') == -1 && (str2.split(''))[0].indexOf('0') == -1 && (str3.split(''))[0].indexOf('0') == -1) {
							if (Number(str3) == urls.fYearCurrent &&
									Number(str2) == urls.fMonthCurrent &&
									Number(str1) > urls.fDayCurrent) {
								//Esta mal si marcan mayor a la fecha actual -- El dia Mal
								inputValue.removeClass('bad-data');
								inputValue.siblings(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
								//esta mal													
							} else if (Number(str3) == urls.fYearCurrent &&
									Number(str2) > urls.fMonthCurrent) {
								//Esta mal si marcan mayor a la fecha actual -- El mes Mal
								inputValue.removeClass('bad-data');
								inputValue.siblings(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
								//esta mal																			
							} else {
								inputValue.addClass('bad-data');
								inputValue.siblings(".response").removeClass('bad').addClass('good').text(msgs.cBirth.good);
								//esta ok							
							}
						} else {
							inputValue.removeClass('bad-data');
							inputValue.siblings(".response").removeClass('good bad').addClass('def').text(msgs.cBirth.def);
							//esta mal           
						}
						//fin add ready																				 
					}
				}
				//actualizando los dias
				function actualizandoDias(dia, diaActualizado, longitudChangeDias) {
					if (diaActualizado > longitudChangeDias) {
						for (var x = diaActualizado; x > longitudChangeDias; x--) {
							dia.children('option').eq(x).remove();
						}
					}
					if (diaActualizado < longitudChangeDias) {
						for (var z = diaActualizado; z < longitudChangeDias; z++) {
							dia.append('<option value="' + (z + 1) + '">' + (z + 1) + '</option>');
						}
					}
				}
				//fin
			}
		},
                jFunctionsFin : function(object) {
			var inputValue = $('#fh_pub_fin');
			if (inputValue.size() > 0) {
				var mesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						//inicioAnio = 1910,
						inicioAnio = urls.fMinDate,
						//años a restar,
						finResto = 15,
						//finAnio = 2011,
						finAnio = urls.fYearCurrent - finResto,
						separador = '/',
						//inicial = '21' + separador + '10' + separador + '1985',
						inicial = inputValue.val(),
						inicialPart = inicial.split(separador),
						txtDia = '-- Día --',
						txtMes = '-- Mes --',
						txtAnio = '-- Año --';
				var dia = $('#dayjFunctions_fin'),
						mes = $('#monthjFunctions_fin'),
						anio = $('#yearjFunctions_fin');

				anio.on('change', function() {
					var _this = $(this);
					if (_this.val() == '0') {
						_this.val(finAnio);
					}
				});
				//años
				var anioI = inicioAnio,
						anioE = finAnio,
						iterador = anioE - anioI;
				anio.append('<option value="0">' + txtAnio + '</option>');
				for (i = 0; i <= iterador; i++) {
					anio.append('<option value="' + (anioI + i) + '">' + (anioI + i) + '</option>');
				}
				//meses
				var longitudMeses = mesNombres.length;
				mes.append('<option value="0">' + txtMes + '</option>');
				for (var j = 0; j < longitudMeses; j++) {
					mes.append('<option value="' + (j + 1) + '">' + mesNombres[j] + '</option>');
				}
				//dias
				var longitudDias,
						valorDia = Number(inicialPart[0]),
						valorMes = Number(inicialPart[1]),
						valorAnio = Number(inicialPart[2]);
				// Valores por defecto para valores desconocidos
				if (isNaN(valorDia)) {
					valorDia = 0;
				}
				if (isNaN(valorMes)) {
					valorMes = 0;
				}
				if (isNaN(valorAnio)) {
					valorDia = 0;
				}
				//bisiesto
				function anioBisiesto(anioF) {
					var checkAnioF = (((anioF % 4 == 0) && (anioF % 100 != 0)) || (anioF % 400 == 0)) ? 1 : 0;
					if (!(checkAnioF)) {
						return false;
					} else {
						return true;
					}
				}
				var checkAnio = anioBisiesto(valorAnio);
				if (valorMes == 1 || valorMes == 3 || valorMes == 5 || valorMes == 7 || valorMes == 8 || valorMes == 10 || valorMes == 12) {
					longitudDias = 31;
				} else if (valorMes == 2) {
					if (checkAnio == true) {
						longitudDias = 29;
					} else {
						longitudDias = 28;
					}
				} else if (valorMes == 4 || valorMes == 6 || valorMes == 9 || valorMes == 11) {
					longitudDias = 30;
				} else {
					// Para valores desconocidos como = 0
					longitudDias = 31;
				}
				// longitudDias = cantidad de dias por mes y anio bisiesto	
				dia.append('<option value="0">' + txtDia + '</option>');
				function loopDias(longitudDias) {
					for (var k = 0; k < longitudDias; k++) {
						dia.append('<option value="' + (k + 1) + '">' + (k + 1) + '</option>');
					}
				}
				loopDias(longitudDias);
				if ($.browser.msie && $.browser.version.substr(0, 1) < 7) {
					// IE 6 Fuck
					setTimeout(function() {
						//focus init valor select			
						dia.val(valorDia);
						mes.val(valorMes);
						anio.val(valorAnio);
					}, 1000);
				} else {
					//focus init valor select	
					dia.val(valorDia);
					mes.val(valorMes);
					anio.val(valorAnio);
				}
				//change dia
				dia.bind('change', function() {
					//escrbiendo fecha en input
					fechaDefault(dia, mes, anio);
				});
				//change mes						
				mes.live('change', function() {
					var t = $(this),
							longitudChangeDias,
							nMes = parseInt(t.val()),
							nAnioM = parseInt(anio.val());
					checkMesAnioBisiesto = anioBisiesto(nAnioM);
					if (nMes == 2) {
						if (checkMesAnioBisiesto == true) {
							longitudChangeDias = 29;
						} else {
							longitudChangeDias = 28;
						}
					} else if (nMes == 1 || nMes == 3 || nMes == 5 || nMes == 7 || nMes == 8 || nMes == 10 || nMes == 12) {
						longitudChangeDias = 31;
					} else if (nMes == 4 || nMes == 6 || nMes == 9 || nMes == 11) {
						longitudChangeDias = 30;
					}
					// loop dias
					var diaActualizado = parseInt(dia.children('option:last').val());
					actualizandoDias(dia, diaActualizado, longitudChangeDias);
					//escrbiendo fecha en input
					fechaDefault(dia, mes, anio);
				});
				//change año
				anio.live('change', function() {
					var t = $(this),
							longitudChangeAnioDias,
							nAnio = parseInt(t.val()),
							anioBisiestoChange = anioBisiesto(nAnio),
							nMesY = parseInt(mes.val());
					// loop dias Febrero
					if (nMesY == 2) {
						if (anioBisiestoChange == true) {
							longitudChangeAnioDias = 29;
						} else {
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
				function fechaDefault(dia, mes, anio) {
					if (inputValue.size() == 1) {
						inputValue.val(dia.val() + separador + mes.val() + separador + anio.val());
						// add ready // 0 valor a buscar
						var splitStr = inputValue.val().split('/'),
								str1 = splitStr[0],
								str2 = splitStr[1],
								str3 = splitStr[2];
						if ((str1.split(''))[0].indexOf('0') == -1 && (str2.split(''))[0].indexOf('0') == -1 && (str3.split(''))[0].indexOf('0') == -1) {
							if (Number(str3) == urls.fYearCurrent &&
									Number(str2) == urls.fMonthCurrent &&
									Number(str1) > urls.fDayCurrent) {
								//Esta mal si marcan mayor a la fecha actual -- El dia Mal
								inputValue.removeClass('bad-data');
								inputValue.siblings(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
								//esta mal													
							} else if (Number(str3) == urls.fYearCurrent &&
									Number(str2) > urls.fMonthCurrent) {
								//Esta mal si marcan mayor a la fecha actual -- El mes Mal
								inputValue.removeClass('bad-data');
								inputValue.siblings(".response").removeClass('good').addClass('bad').text(msgs.cBirth.exed);
								//esta mal																			
							} else {
								inputValue.addClass('bad-data');
								inputValue.siblings(".response").removeClass('bad').addClass('good').text(msgs.cBirth.good);
								//esta ok							
							}
						} else {
							inputValue.removeClass('bad-data');
							inputValue.siblings(".response").removeClass('good bad').addClass('def').text(msgs.cBirth.def);
							//esta mal           
						}
						//fin add ready																				 
					}
				}
				//actualizando los dias
				function actualizandoDias(dia, diaActualizado, longitudChangeDias) {
					if (diaActualizado > longitudChangeDias) {
						for (var x = diaActualizado; x > longitudChangeDias; x--) {
							dia.children('option').eq(x).remove();
						}
					}
					if (diaActualizado < longitudChangeDias) {
						for (var z = diaActualizado; z < longitudChangeDias; z++) {
							dia.append('<option value="' + (z + 1) + '">' + (z + 1) + '</option>');
						}
					}
				}
				//fin
			}
		},
		/**
		 * Función para contar los caracteres de un textarea
		 * @author Victor Sandoval Valladolid
		 * @param  {object} oP Parametros de entrada
		 * @return {null}
		 */
		countCharacters: function(oP) {
			var dom = {},
			st = {
				input       : null,
				countLetter : '.count-letter',
				countChar   : '#count-char',
				maxCharacter: 0
			},
			catchDom = function() {
				dom.input         = $(st.input);
			},
			suscribeEvents = function(){
				dom.input.on('keyup', onkeyup);
			},
			onkeyup = function(){
				$(this).siblings(st.countLetter).find(st.countChar).text(st.maxCharacter - $(this).val().length);
			};

			$.extend(st, oP);
                        if (typeof($(st.input).val())!='undefined') {
                            $(st.countChar).text(st.maxCharacter - $(st.input).val().length)
                        }
			catchDom();
			suscribeEvents();
		}
	};
	// init
	//paso 1
	formP1.fMail('#fEmail', msgs.cEmail.good, msgs.cEmail.bad, msgs.cEmail.def);
	formP1.fDni();

	aptitusMethods.validateAll().init({ inputTag: '#fNames',      type: 'letter'});
	aptitusMethods.validateAll().init({ inputTag: '#fLastnameP',  type: 'letter'});
	aptitusMethods.validateAll().init({ inputTag: '#fLastnameM',  type: 'letter'});
	aptitusMethods.validateAll().init({ inputTag: '#fTlfFC2',     type: 'number'});
	aptitusMethods.validateAll().init({ inputTag: '#fTlfFC',      type: 'number'});
	aptitusMethods.validateAll().init({
		context     : '#datoBasicoF',
		btnSubmit   : '#fSubmit',
		isForm      : true,
		inputs      : {
			fEmail: {require : true},
			fClave: {require : true},
			fRClave: {require : true},
			fNames: {require : true},
			fLastnameP: {require : true},
			fLastnameM: {require : true},
			dayjFunctions: {require : true},
			monthjFunctions: {require : true},
			yearjFunctions: {require : true},
			fNDoc: {require : true},
			fTlfFC2: {type: 'phone'},
			fTlfFC: {type: 'celular'},
			fUrlST: {type: 'url'},
			fDistri: {require : true}
		},
		onAfterValid : function(dom){
			dom.btnSubmit.attr('disabled',true).text('Guardando...');
			//return false;
		}
	});
	formP1.countCharacters({input: '#presentMC',maxCharacter: 750});


	formP1.fPass('#fClave', 6, '#fRClave');
	formP1.fRePass('#fRClave', '#fClave', 6);
	formP1.fIDate('#fBirthDate', msgs.cBirth.good, msgs.cBirth.bad, msgs.cBirth.def);

	formP1.fUbi('#fPais', '#fDepart', '#fDistri', '#fProvin');
	formP1.fOldPass('#fACtns');
	formP1.maxLenghtN('select.maxLenghtN');
	formP1.jFunctions();
        formP1.jFunctionsFin();
	formP1.deleteMesLoad();

});
