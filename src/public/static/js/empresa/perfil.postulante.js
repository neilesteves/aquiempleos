/*
 Empresa Ve Perfil postulante
 */

var AptitusPerfil = function() {
	var msgs = {
		cDef : {
			good :'.',
			bad : 'Campo Requerido',
			def :'Opcional'
		},
		cEmail : {
			good : '.',
			bad : 'No parece ser un e-mail válido.',
			def : 'Ingrese e-mail correcto'
		},
		cName : {
			good : '.',
			bad : '¡Se requiere su nombre!',
			bad2 : '¡Se requiere el nombre!',
			def : 'Ingrese nombre correcto'
		},
		cAreaMsg : {
			good : '.',
			bad : '¡Se requiere su mensaje!',
			def : 'Ingrese mensaje correcto'
		},
		cQuestions : {
			good : '.',
			bad : 'Responder pregunta',
			def : 'Responda con criterio'
		},
		cInvitacion : {
			good : 'Invitación exitosa',
			bad : 'Invitación fallida'
		},
		mailSend : 'El e-mail fue enviado.',
		mailError : 'No se pudo enviar el e-mail.'
	};
	var vars = {
		rs : '.response',
		okR :'ready',
		sendFlag : 'sendN',
		speed1 : 'slow',
		speed2 : 'fast'
	};

		  var eliminarNotasBolsa = false;
	var perfilPost = {
		//perfil
		dropDown : function(clickAB, listA) {
			var clickA = $(clickAB);
			if(clickA.size() > 0){
				var postT = clickA.position().top,
				postL = clickA.position().left,
				heightA = clickA.innerHeight(),
				listA = $(listA),
				speed = 'fast';
				listA.css({
					'left': postL,
					'top' : postT + heightA - 2
				});
				clickA.bind('click', function(e) {
					e.preventDefault();
					var t = $(this),
					flechaA = t.find('.flechaGrisT');
					if(t.hasClass('openFl')) {
						listA.slideUp(speed);
						t.removeClass('openFl');
						flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
					} else {
						listA.slideDown(speed);
						t.addClass('openFl');
						flechaA.addClass('downFlechaEP').removeClass('upFlechaEP');
					}

					$(document).bind('mouseup', function(e) {
						if($(e.target).parent(clickAB).length==0) {
							e.preventDefault();
							flechaA = t.find('.flechaGrisT');
							listA.slideUp('fast');
							t.removeClass('openFl');
							flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
						}
					});
					return false;
				});
			}
		},
		moveraetapa : function(a){
			$(a).bind('click', function(evt) {
				evt.preventDefault();
				var t = $(this),
				contentLoadAj = $('#loadMsjEmpPP');
				if(!t.hasClass('active')) {
					var idpostulacion = t.attr('rol');
					var etapa = t.attr('rel');
													 var idAviso = $("#idAviso").val();
					var json = {
						'rel':etapa,
						'rol':idpostulacion,
																'idAviso':idAviso
					};
					contentLoadAj.removeClass('hide msgYellow msgRed msgBlue').addClass('loading');
					$.ajax({
						type: 'POST',
						url: '/empresa/mis-procesos/mover-etapa-perfil',
						data: json,
						dataType: 'JSON',
						success: function(msg) {
							contentLoadAj.removeClass('loading');
							if(msg == 1) {
								perfilPost.mostrarMensaje1('#loadMsjEmpPP','success','La postulación ha sido movido correctamente');
								$(a).removeClass('active');
								t.addClass('active');
							} else {
								perfilPost.mostrarMensaje1('#loadMsjEmpPP','error','La postulación no fue movida correctamente');
							}
						},
						error: function(msg) {
							contentLoadAj.removeClass('loading');
							perfilPost.mostrarMensaje1('#loadMsjEmpPP","error","La postulación no fue movida correctamente');
						}
					});
				}
				return false;
			});
		},
		moverdescartado: function(a) {
			$(a).bind("click", function(evt) {
				evt.preventDefault();
				var t = $(this),
				contentLoadAj = $('#loadMsjEmpPP');
				if(!t.hasClass('activo')) {
					var idpostulacion = t.attr("rol");
					var data = {
						"rol": idpostulacion
					};
					contentLoadAj.empty().removeClass('hide msgYellow msgRed msgBlue').addClass('loading');
					$.ajax({
						type: "POST",
						url: "/empresa/mis-procesos/descarta-perfil",
						data: data,
						dataType: "JSON",
						success: function(msg) {
							contentLoadAj.removeClass('loading');

							//Aqui poner la condicion de respuesta
							perfilPost.mostrarMensaje1("#loadMsjEmpPP","success","Postulacion Descartada.");
							$(a).removeClass('active');
							t.addClass('active');

							//dataRemove = $('#sendMsjEPA, #aLinkFlechaTV, #aLinkDescarTE'); Aqui le puse VAR
									 var dataRemove = $('#sendMsjEPA, #aLinkFlechaTV, #aLinkDescarTE');
							dataRemove.fadeIn(vars.speed1, function() {
								dataRemove.remove();
								//sidebar
								$('#cntAddBtnsE, #contentNotaEPL .actionsELP').addClass('hide');
								window.location.reload()
							});

						},
						error: function(msg) {
							contentLoadAj.removeClass('loading');
							perfilPost.mostrarMensaje1("#loadMsjEmpPP","error","La postulación no fue descartada");
						}
					});
				}
				return false;
			});
		},
		mostrarMensaje1: function(a,tipo,mensaje) {
			var clasetipo;
			switch(tipo) {
				case 'info':
					clasetipo = 'msgBlue';
					break;
				case 'error':
					clasetipo = 'msgRed';
					break;
				case 'success':
					clasetipo = 'msgYellow';
					break;
			}
			var variable = $(a);
			variable.html(mensaje).removeClass("msgYellow msgRed msgBlue").addClass(clasetipo);
			variable.fadeIn("meddium", function() {
				setTimeout( function() {
					variable.addClass("hide r5").fadeOut("meddium");
				},1500);
			});
		},
		verAnuncio : function(a) {
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

				var url = t.attr('rel');

				var contenido = '#content-' + idperfil.substr(1,idperfil.length);
				var contenidoN = $(contenido);
				contenidoN.html('');
				contenidoN.addClass('loading');

				contenidoN.load(url, function() {
					contenidoN.removeClass('loading');
					perfilPost.scrollAviso();
				});
				return false;
			});
		},
		scrollAviso : function() {
			var heightL = $('#dataFormAddNADM .cntModalAEmp'),
			heightcontent = heightL.height(),
			alto = 350;
			if(heightcontent > alto ) {
				heightL.addClass('srollModalAEmp').css({
					'height': alto
				});
			}
		},
		//Compartir
		fMail : function(a,good,bad,def) {
			$(a).focus();
			$(a).bind('blur', function() {
				var t = $(this),
				r = t.next(vars.rs),
				ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				if(ep.test(t.val())&& t.val()!='') {
					r.removeClass('bad').addClass('good').text(good);
					t.addClass(vars.okR);
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.removeClass(vars.okR);
				}
				return false;
			});
		},
		fInput : function(a,good,bad,def) {
			var A = $(a),
			r = A.next(vars.rs);
			A.blur( function() {
				var t = $(this);
				if(t.val().length>0) {
					r.removeClass('bad').addClass('good').text(good);
					t.addClass(vars.okR);
				} else {
					r.removeClass('good').addClass('bad').text(bad);
					t.removeClass(vars.okR);
				}
			}).keyup( function() {
				var t = $(this);
				if(t.val().length===0) {
					r.removeClass('good').addClass('bad').text(bad);
					t.removeClass(vars.okR);
				} else {
					r.removeClass('good bad').text(def);
					t.addClass(vars.okR);
				}
			});
		},
		fSubmit : function(a,b,c,f1,f2,f3,f4,f5) {
			var A=$(a),
			B=$(b),
			F1 = $(f1), F2 = $(f2), F3 = $(f3),
			F4 = $(f4), F5 = $(f5);
			A.on('click', function(e) {
				e.preventDefault();
										  var t = $(this),
				urlSlug = t.attr('rel'),
				nombreEmisor = F1.val(),
				correoEmisor = F2.val(),
				nombreReceptor = F3.val(),
				correoReceptor = F4.val(),
				mensajeCompartir = F5.val(),
				hdnOculto = $('input#hdnOculto').val(),
										  idAviso = $("#idAviso").val(),
										  tokCompartir = $('input#fCAtok').val();
				if(B.find('.'+vars.okR).size()>=c) {
					B.addClass('hide');
					//$('#loadMFF').remove();
					//$('#iEscpF').append('<div id="loadMFF" class="loading"></div>');
													 //$('#loadMFF').addClass('loading')
													 $('#loadMFF').remove();
					$('#shareMail').append('<div id="loadMFF" class="loading"></div>');


													 $.ajax({
														  url: '/registro/obtener-token/',
														  type: 'POST',
														  dataType:'json',
														  success: function (resTok) {
																var currToken = resTok;

																$.ajax({
																	 'url' : urlSlug,
																	 'type' : 'POST',
																	 'dataType' : 'JSON',
																	 'data' : {
																				'nombreEmisor' : nombreEmisor,
																				'correoEmisor' : correoEmisor,
																				'nombreReceptor' : nombreReceptor,
																				'correoReceptor' : correoReceptor,
																				'mensajeCompartir' : mensajeCompartir,
																				'hdnOculto' : hdnOculto,
																				'idAviso':idAviso,
																				'fCAtok' : currToken
																	 },
																	 'success' : function(res) {
																				//var frm = $('#iEscpF');
																				var frm = $('#shareMail');
																				$('#loadMFF').removeClass('loading')
																				if(res.status == 'ok') {
																						  B.addClass('hide');
																						  $('#loadMFF').remove();
																						  //$('#loadMFF').html('<div class="good msjLoadMFF">' + res.msg + '</div>');
																						  frm.append('<div id="loadMFF" class="block"><div class="good msjLoadMFF">' + res.msg + '</div></div>');
																						  perfilPost._clearFields(frm);
																				} else {
																						  B.addClass('hide');
																						  $('#loadMFF').remove();
																						  //$('#loadMFF').html('<div class="bad msjLoadMFF">' + msgs.mailError + '</div>');
																						  frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
																						  perfilPost._clearFields(frm);
																				}

																				if (typeof(res.tok)!='undefined') {
																					 $('#shareMail form input#fCAtok').val(res.tok);
																				}
																	 },
																	 'error' : function(res) {
																				var frm = $('#iEscpF');
																				B.addClass('hide');
																				//$('loadMFF').remove();
																				//frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
																				$('#loadMFF').html('<div class="bad msjLoadMFF">' + msgs.mailError + '</div>');
																				perfilPost._clearFields(frm);
																	 }
																});



														  }
													});








				} else {
					if($.trim(F1.val())==='') {
						F1.next(vars.rs).removeClass('def good').addClass('bad').text(msgs.cDef.bad);
					}
					if($.trim(F2.val())==='') {
						F2.next(vars.rs).removeClass('def good').addClass('bad').text(msgs.cDef.bad);
					}
					if($.trim(F3.val())==='') {
						F3.next(vars.rs).removeClass('def good').addClass('bad').text(msgs.cDef.bad);
					}
					if($.trim(F4.val())==='') {
						F4.next(vars.rs).removeClass('def good').addClass('bad').text(msgs.cDef.bad);
					}

				}
				return false;
			});
		},
		resetBtn : function(btnReset, chars) {
			btnR = $(btnReset);
			btnR.click( function() {
				var t = $(this),
				frm = t.parents('form');
				perfilPost._clearFields(frm);
				$('#nCaracterP').text(chars);
				return false;
			});
		},
		_clearFields : function(frm) {
			frm.find('.inputReset').val('').removeClass('ready').siblings('.response').text('').removeClass('good bad').addClass('def');
		},
		resetBtnClose : function(btnCloseReset, chars) {
			btnRC = $(btnCloseReset);
			btnRC.click( function() {
				var t = $(this),
				frm = $('#formShareCA');
				perfilPost._clearFields(frm);
				frm.removeClass('hide');
				$('#loadMFF').remove();
				$('#nCaracterP').text(chars);
				return false;
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
				var key = e.which;
				var length = t.val().length;
				countN.html( (chars - length) + ' ' );
				if( length > chars ) {
					valueArea = t.val().substring(chars, '') ;
					trigger.val(valueArea);
					countN.html('0');
				}
			});
		},
		invitarAnuncioWeb : function(a) {
			$(document).on('click', a, function(evt) {
				evt.preventDefault();
				var t = $(this);
				var idpostulante = t.attr('rel'),
					 idpostulacion = t.attr('idpostulacion'),
				contenido = $('#content-winInvitarProceso');
				contenido.html('');
				contenido.addClass('loading');

				$.ajax({
					type: 'POST',
					url: '/empresa/mis-procesos/invitar-proceso/',
					data: {
						'id' : idpostulante,
								'idpostulacionactual' : idpostulacion,
								'idAviso': $("#idAviso").val()
					},
					dataType: 'html',
					success: function (msg) {
														  contenido.removeClass('loading');
														  if($.trim(msg)==="3"){
																$("#mask").trigger("click");
																$("#perfilContainer").remove();
																if(typeof idpostulacion==="undefined"){
																  $(".verPerfilFilPerfP[rel='"+idpostulante+"']").trigger('click');
																}else{
																	$(".verPerfilFilPerfP[rel='"+idpostulacion+"']").trigger('click');
																}

														  }else{
						contenido.html(msg);
						perfilPost.enviarInvitacion('.linkInviteP');
						// Scroll si sobrepasa el alto
						var cntSrcoll = $('#dataCntInvA');
						if(cntSrcoll.height() >= 280 ) {
							cntSrcoll.addClass('overInvHS');
						}
														  }
					}
				});
				return false;
			});
		},
		enviarInvitacion : function(a) {
			$(a).bind("click", function(evt) {
				evt.preventDefault();
				var t = $(this),
				idAw = t.attr("rel"),
				idPos = t.attr("rol"),
										  idPostulacion = t.attr("idpostulacion"),
				contenido = t.parents('#content-winInvitarProceso'),
				btnClose = contenido.parents('.innerWin').children('.closeWM');

				contenido.html('');
				contenido.addClass('loading');
				evt.stopPropagation();
				$.ajax({
					type: "POST",
					url: "/empresa/mis-procesos/enviar-invitacion",
					data: {
						'idAw' : idAw,
						'idPos' : idPos,
								'idPostulacion' : idPostulacion,
								'tok': t.data("tok")
					},
					dataType: "JSON",
					success: function (msg) {
						contenido.removeClass('loading');
						if(msg!=-1) {
							contenido.html('<div class="msjInvAt good">' + msgs.cInvitacion.good + '</div>');
						} else {
							contenido.html('<div class="msjInvAt bad">' + msgs.cInvitacion.bad + '</div>');
						}
						setTimeout( function() {
							btnClose.trigger('click');
									 // document.location.reload();
						},1500);
					},
					error: function () {
						contenido.removeClass('loading');
						contenido.html('<div class="msjInvAt bad">' + msg.cInvitacion.bad + '</div>');
						setTimeout( function() {
							btnClose.trigger('click');
						},1500);
					}
				});
				return false;
			});
		},

		//filtrado
		filterCheckCnt : function(primerCheck, segundoCheck, tercerCheck){
				//pCheck = $(primerCheck), Aqui le puse el VAR 08-02-2012
				var pCheck = $(primerCheck),
			sCheck = $(segundoCheck),
			tCheck = $(tercerCheck),
			cnt1 = $('#contentHistoryEPL'),
			cnt2 = $('#contentNotaEPL'),
			cnt3 = $('#contentMsjEPL'),
			spped = 'fast';
			pCheck.bind('change', function() {
				if(pCheck.prop('checked')) {
					cnt1.fadeIn(spped);
				} else {
					cnt1.fadeOut(spped);
				}
			});
			sCheck.bind('change', function() {
				if(sCheck.prop('checked')) {
					cnt2.fadeIn(spped);
				} else {
					cnt2.fadeOut(spped);
				}
			});
			tCheck.bind('change', function() {
				if(tCheck.prop('checked')) {
					cnt3.fadeIn(spped);
				} else {
					cnt3.fadeOut(spped);
				}
			});
		},
		acordionNM : function(shots){
			var shotNota = $(shots),
			spped = 'fast';
			shotNota.live('click', function(e){
				e.preventDefault();
				var t = $(this),
				hideCnt = t.parents('.blockHideC').find('.blockInH');

				if(t.hasClass('flagBlockEP')){
					hideCnt.slideUp(spped, function(){
						t.removeClass('flagBlockEP');
					});
				}else{
					hideCnt.slideDown(spped, function() {
						t.addClass('flagBlockEP');
					});
				}
				return false;
			});
		},
		//mensajes, notas
		//perfilPost.addIndice('#contentNotaEPL .formFields');
		addIndice : function(fieldInd){
			var fieldN = $(fieldInd);
			/*
			$.each(fieldN, function(i, v) {
				fieldN.eq(i).attr('id', 'ind_' + i);
			});
			*/
		},
		inputFileAttach : function(shotAtt){
			var shotAttach = $(shotAtt);
			shotAttach.live('click', function(e){
				//e.preventDefault();
				//e.stopPropagation();
				var t = $(this),
				inputAttach = t.siblings('input.inputAttachEPL[type="file"]');
				var clickEdit = t.siblings('.editEPL');

				if(!(clickEdit.hasClass('openEditHG'))){
					clickEdit.trigger('click');
				}
				//return false;
			});
		},
		inputFileChange : function(ipt){
			var iptA = $(ipt);
			iptA.live('change', function(){
				var t = $(this),
				arrExtension = new Array('exe','com','php','pif','.pl','jar','dll','.sh'),
				txtAdjunto = t.parents('.actionsELP').siblings('.dataAdjunto'),
				value = t.val(),
				cantPalabras = value.length,
				extension = value.substr(cantPalabras-3, 3),
				flagExt = false,
				errorMsgBP = t.parents('.actionsELP').siblings('.editFormEP').find('.msgErrorEPA');

				for(var i = 0; i < arrExtension.length; i++){

					if(arrExtension[i] == extension){
						flagExt = true;
					}
				}

				if(flagExt == false){

					errorMsgBP.text('');
					//alert(false);

					var mostrarPalabras = 28;
					var palabras = cantPalabras - mostrarPalabras,
					textAcotado = value.substr(palabras);
													 //textAMostrar = '...' + textAcotado; Aqui le puse el VAR
					var textAMostrar = '...' + textAcotado;

					var valueInd;

					if(cantPalabras > mostrarPalabras){
						valueInd = textAMostrar;
					} else {
						valueInd = value;
					}

					txtAdjunto.removeClass('hide');
					txtAdjunto.html('<div class="adjuntoD msgYellow mB10 r5"><div class="msgInAdjEPL">' +
					valueInd +
					'<a class="icoCloseMsjAdj sptIcoEmp" href="#"><span class="hide">Cerrar</span></a>' +
					'</div></div>');
					// reBind para cerrar
					$('.icoCloseMsjAdj').die();
					perfilPost.closeAdjuntoP('.icoCloseMsjAdj');

				}else{
					errorMsgBP.text('Formato de archivo no permitido.');
					//reset input file
					var inputIpt = t;
					perfilPost.resetInputFile(inputIpt);
					//alert('la extension no es permitida');
					// reBind para cerrar
					$('.icoCloseMsjAdj').die();
					perfilPost.closeAdjuntoP('.icoCloseMsjAdj');
					t.parents('.actionsELP').siblings('.dataAdjunto').find('.icoCloseMsjAdj:visible').click();

				}

			});
		},
		closeAdjuntoP : function(closeAdj){
			var closeAdjA = $(closeAdj);
			var speed = 'slow';
			closeAdjA.live('click', function(e){
				e.preventDefault();
				var t = $(this),
				cnt = t.parents('.msgYellow'),
				sourceDt = cnt.parents('.dataAdjunto').siblings('.actionsELP'),
				clickEditN = sourceDt.find('.editEPL');
				cnt.fadeOut(speed, function(){
					var inputIpt = sourceDt.find('.inputAttachEPL');
					perfilPost.resetInputFile(inputIpt);
					cnt.detach();
					//cnt.remove();
				});
				//return false;
			});
		},
		//reset input file
		resetInputFile : function(ipt){
			ipt.wrap('<form></form>');
			ipt.parent().get(0).reset();
			ipt.unwrap();
			return ipt;
		},
		//adjuntando
		submitAnadirNota : function(a){
			$(a).on('click', function(evt) {
				evt.preventDefault();
				evt.stopPropagation();

				var t = $(this),
				//cntErrorMsj = $('#errorMsjPEP'),
				cntErrorMsj = t.parent().prev(),
				cntErrorNote = t.next();

				if(t.hasClass('msjBtnAjax')) {
					//mensaje
					cntErrorMsj.removeClass('error').text('');
					var formTM = t.parents('.formFields'),
					cntFormTM = formTM.parents('.cntFormMsjF'),
					loadFormTM = cntFormTM.children('.showLoadF');

					if($.trim(formTM.find('.dataTextEP').val()) != ''){
						formTM.addClass('hide');
						loadFormTM.removeClass('hide');
						// Ajax
						$.ajax({
							type: 'POST',
							url: '/empresa/mis-procesos/guardar-mensaje/',
							data: formTM.serialize(),
							dataType: 'HTML',
							success: function(res) {
								loadFormTM.addClass('hide');
								var anadeMsj = $('#contentMsjEPL');
								anadeMsj.prepend(res).fadeIn(vars.speed1, function(){
									//Volviendo a enumerar
									perfilPost._enumador('.msjItemsP');
									//Volviendo a llamar a las funciones
									formTM.removeClass('hide');
									formTM.find('textarea.dataTextEP').val('');
									formTM.find('.dataAdjunto').empty().addClass('hide');
									var nCntAddMsj = $('#contentMsjEPL'),
									posNAddMsj = nCntAddMsj.offset().top;
									$('html, body').animate({scrollTop:posNAddMsj}, vars.speed2, function(){
										//nDivMsjs = nCntAddMsj.find('.blockDropDown');	Aqui le puse el VAR
													 var nDivMsjs = nCntAddMsj.find('.blockDropDown');
										for(var j=nDivMsjs.size(); j>1; j--){
											nDivMsjs.eq(j-1).removeClass('flagBlockEP').siblings('.blockInH').removeAttr('style').addClass('hide');
										}
									});
									//No arroba
									perfilPost.fNoArroba('.editFormEP textarea');
									//Reset Checks
									perfilPost.resetChecksMark('mensaje');
								});
							}
						});
					}else{
						cntErrorMsj.removeClass('hide').addClass('error').text('Ingrese el mensaje');
					}
				} else {
					//nota

					var formT = t.parents('.formFields'),
					cntFormT = formT.parents('.cntFormNotaF'),
					loadFormT = cntFormT.children('.showLoadF'),
					iframeTrue = formT.next('iframe.iframeTrue');

					if($.trim(formT.find('.dataTextEP').val()) != '' ||
						formT.find('.msgYellow .msgInAdjEPL').size() > 0){

						cntErrorNote.text('');

						formT.addClass('hide');
						loadFormT.removeClass('hide');

						var flagResp = false;

						setTimeout(function(){

							$(iframeTrue).bind('load', function(e) {
									var response='undefined';
									if( $.browser.msie && $.browser.version.substr(0,1) <= 8 ) {
										response = window.frames[iframeTrue.attr('name')].document.body.innerHTML;
									} else {
										response = $(iframeTrue)[0].contentDocument.body.innerHTML;
									}

									loadFormT.addClass('hide');

										var anadeData = $('#contentNotaEPL');

										if(flagResp == false){

											anadeData.prepend(response).fadeIn(vars.speed2, function() {
												//Volviendo a enumerar
												$('.notaItemsP').die();
												perfilPost._enumador('.notaItemsP:visible');
												//Volviendo a llamar a las funciones

												//Agregando indice
												$('#contentNotaEPL .formFields').die();
												$('.inputAttachEPL').unbind();
												perfilPost.addIndice('#contentNotaEPL .formFields');
												perfilPost.inputFileChange('.inputAttachEPL');

												$('#contentNotaEPL .dataBtnEP').die();
												perfilPost.editSubmitNota('#contentNotaEPL .dataBtnEP');


												var nuevoBlock = $('#contentNotaEPL .cntFormNotaF').eq(0);
												nuevoBlock.find('.editEPL').css('display','none');
												nuevoBlock.find('.divAtachOpt').css('display','none');

												 formT.removeClass('hide');
												 formT.find('textarea.dataTextEP').val('');
												 formT.find('.dataAdjunto').empty().addClass('hide');

												var nCntAddNote = $('#contentNotaEPL'),
												posNAddNote = nCntAddNote.offset().top;

												$('html, body').animate({scrollTop:posNAddNote}, vars.speed1, function(){
																	 //nDivNotas = nCntAddNote.find('.blockDropDown'); Aqui le puese el VAR
													var nDivNotas = nCntAddNote.find('.blockDropDown');

													for(var j=nDivNotas.size(); j>1; j--){
														nDivNotas.eq(j-1).removeClass('flagBlockEP').siblings('.blockInH').removeAttr('style').addClass('hide');
													}

												});

												flagResp = true;

												//No arroba
												perfilPost.fNoArroba('.editFormEP textarea');
												//Reset Checks
												perfilPost.resetChecksMark('nota');
											});

										}

						});

						}, 0);
						formT.submit();

					}else{
						cntErrorNote.removeClass('hide').addClass('error').text('Ingrese la nota');
					}

				}
				return false;
			});
		},
		resetChecksMark : function(type){
			var btnNote = $('#tFiltroEPL'),
				btnMsj = $('#sFiltroEPL');
			if(type == 'mensaje'){
				btnMsj.attr('checked','checked');
			}else if(type == 'nota'){
				btnNote.attr('checked','checked');
			}
		},
		fNoArroba : function(a) {
				return $(a).each( function() {
					 var t = $(this),
					 isShift = false,
					 epAroba = /a(r{1,2})[o{1}|0{1}][b{1}|v{1}]a/gi;
					 t.keypress( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  if(key == 64) {
								return false;
						  }
						  if(key == 18) {
								return false;
						  }
						  var car = String.fromCharCode(e.charCode);
						  if(car == '@') {
								return false;
						  }
						  return true;
					 });
					 t.keyup( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  var valTmp = t.val();
						  if(valTmp.search(epAroba)!=-1){
							  var newValTmp = valTmp.replace(epAroba,'');
							  t.val(newValTmp);
						  }
					 });
					 t.bind('paste', function(){
						  setTimeout(function() {
								var value = t.val(),
								newValue = value.replace(/[@]/g,''),
								newValue = newValue.replace(epAroba,'');
								t.val($.trim(newValue));
						  }, 0);
					 });
				});
		  },
		//adjuntando
		editSubmitNota : function(a){
			$(a).live('click', function(evt) {
				evt.preventDefault();
				//evt.stopPropagation();
				var t = $(this),
				//cntErrorMsj = $('#errorMsjPEP'),
				cntErrorMsj = t.parent().prev(),
				cntErrorNote = t.next();

					//nota
					var formT = t.parents('.formFields'),
					cntFormT = formT.parents('.cntFormNotaF'),
					loadFormT = cntFormT.children('.showLoadF'),
					iframeTrue = formT.next('iframe.iframeTrue');

					if($.trim(formT.find('.dataTextEP').val()) != '' ||
						formT.find('.msgYellow .msgInAdjEPL').size() > 0){

						cntErrorNote.text('');

						formT.addClass('hide');
						loadFormT.removeClass('hide');

						var flagResp1 = false;

						setTimeout(function(){

							$(iframeTrue).bind('load', function(e){

									var response='undefined';
									if( $.browser.msie && $.browser.version.substr(0,1) <= 8 ) {
										response = window.frames[iframeTrue.attr('name')].document.body.innerHTML;
									} else {
										response = $(iframeTrue)[0].contentDocument.body.innerHTML;
									}

									loadFormT.addClass('hide');

										var anadeData = $('#contentNotaEPL');

										if(flagResp1 == false){

												//removiendo loading y form anterior
												setTimeout(function(){
													//remueve el anterior
													cntFormT.addClass('hide');
													$('#contentNotaEPL .showLoadF').eq(1).removeClass('loading');
												},0);

											anadeData.prepend(response).fadeIn(vars.speed2, function() {

												//Volviendo a enumerar
												perfilPost._enumador('.notaItemsP:visible');
												//Volviendo a llamar a las funciones

												//Agregando indice
												$('#contentNotaEPL .formFields').die();
												$('.inputAttachEPL').die();
												perfilPost.inputFileChange('.inputAttachEPL');

												perfilPost.addIndice('#contentNotaEPL .formFields');

												var nuevoBlock = $('#contentNotaEPL .cntFormNotaF').eq(0);
												nuevoBlock.find('.editEPL').css('display','none');
												nuevoBlock.find('.divAtachOpt').css('display','none');

												 formT.removeClass('hide');
												 formT.find('textarea.dataTextEP').val('');
												 formT.find('.dataAdjunto').empty().addClass('hide');

												var nCntAddNote = $('#contentNotaEPL'),
												posNAddNote = nCntAddNote.offset().top;

												$('html, body').animate({scrollTop:posNAddNote}, vars.speed1, function(){

													nDivNotas = nCntAddNote.find('.blockDropDown');

													for(var j=nDivNotas.size(); j>1; j--){
														nDivNotas.eq(j-1).removeClass('flagBlockEP').siblings('.blockInH').removeAttr('style').addClass('hide');
													}


												});

												flagResp1 = true;


											});

										}

						});

						},0);

						formT.submit();


					}else{
						cntErrorNote.removeClass('hide').addClass('error').text('Ingrese la nota');
					}

				return false;
			});
		},
		_enumador : function(arrNotas){
			var dataEnum = $(arrNotas),
			size = dataEnum.size(),
			j = 0;
			for(var i=dataEnum.size(); i>0; i--) {
				dataEnum.eq(j++).text(i);
			}
		},
		deleteAdjunto: function(clickClose){
			var clickCloseA = $(clickClose);
			clickCloseA.live('click', function(evt) {
				evt.preventDefault();
				var t = $(this),
				idNote = t.attr('rel'),
				cnt = t.parents('.dataAdjuntada');
				// Ajax
				$.ajax({
					type: 'POST',
					url: '/empresa/mis-procesos/eliminar-adjunto/',
					data: {
						'rel' : idNote
					},
					dataType: 'JSON',
					success: function(res) {
						if(res==1) {
							cnt.fadeOut(vars.speed2);
						} else {
							// no ejecuta
						}

					},
					error : function(res) {
						// no ejecuta
					}
				});
				return false;
			});
		},
		//closeAdjInP
		linkViewAll : function(shotLink){
			//shotL = $(shotLink), Aqui le puese el VAR
								var shotL = $(shotLink),
			txtVerMas = 'Ver m&aacute;s',
			txtVerMenos = 'Ver menos';
			shotL.live('click', function(e) {
				e.preventDefault();
				var t = $(this),
				txtAll = t.parent().siblings('.textbInAll'),
				txtShort = t.parent().siblings('.textbIn');
				if(t.hasClass('txtFlagCh')) {
					txtAll.addClass('hide');
					txtShort.removeClass('hide');
					t.removeClass('txtFlagCh').html(txtVerMas);
				} else {
					txtShort.addClass('hide');
					txtAll.removeClass('hide');
					t.addClass('txtFlagCh').html(txtVerMenos);
				}
				return false;
			});
		},
		editDataEP : function(shotEdit) {
			var shotEditP = $(shotEdit),
			speed = 'fast',
			txtBack = 'Volver',
			txtEdit = 'Editar';
			shotEditP.live('click', function(e) {
				e.preventDefault();
				var t = $(this),
				tParent = t.parent(),
				dataHide = tParent.siblings('.dataHideFlagE'),
				txtAttachSh = dataHide.find('.textbIn'),
				txtAttach = dataHide.find('.textbInAll'),
				cntTxtareaA = tParent.siblings('.editFormEP'),
				txtareaA = cntTxtareaA.find('.dataTextEP');

				if(t.hasClass('openEditHG')) {
					//cierra la edicion
					cntTxtareaA.slideUp(speed, function() {
						dataHide.removeClass('hide');
						t.text(txtEdit);
					});
					txtareaA.val('');
					t.removeClass('openEditHG');
				} else {
					//abre para editrar
					cntTxtareaA.slideDown(speed, function() {
						dataHide.addClass('hide');
						t.text(txtBack);
						txtareaA.focus();
					});
					txtareaA.val(txtAttach.text());
					t.addClass('openEditHG');
				}
				return false;
			});
		},
		addIdDelete : function(clickAdd){
			var clickAddA = $(clickAdd);
			clickAddA.live('click', function(){
				var t = $(this),
				idFormField = t.parents('.formFields').attr('id');

				if(!(t.parents('.formFields').attr('id'))){
					idFormField = 'ind_' + t.attr('rel');
				}

				var dataRel = (t.attr('rel') + '#' + idFormField),
				yesOk = $('#btnQ .yesCM');
				yesOk.attr('rel', dataRel);

				if(t.hasClass('deleteNoteEPA')) {
					yesOk.addClass('borrarCliente');
				}

										  if(t.hasClass('eliminarNotaBolsa')) {


					eliminarNotasBolsa = true;
				}
				//return false;
			});
		},
		deleteNotePerf : function(clickDelete) {
			var clickDeleteA = $(clickDelete),
			speed = 'slow',
			msgEliminar = '... Eliminando',
			msgError = 'Hubo un error',
			msgGood = 'Se borró ok';
			clickDeleteA.live('click', function(evt) {
										  if(eliminarNotasBolsa){
												eliminarNotasBolsa = false;
												return;
										  }
				evt.preventDefault();
				var t = $(this),
				close = t.siblings('.closeWM'),
				str = t.attr('rel').split('#'),
				idNote = str[0],
				div = str[1];
				close.trigger('click');
				var divElim = $('#' + div),
				msgAction = divElim.find('.msgDataCurr');
				msgAction.fadeIn('slow').removeClass('error good').text(msgEliminar);

				if(t.hasClass('borrarCliente')) {
					//cliente
					msgAction.text(msgEliminar);
					var formCnt = msgAction.parents('.formFields');
					setTimeout( function() {
						//formCnt.remove();
						$('#btnAddNoteEPL').trigger('click');
						msgAction.text('').removeClass('good bad');
						formCnt.find('textarea').val('');
						formCnt.find('.msgYellow').removeAttr('style');
						formCnt.find('.adjuntoD').remove();
						formCnt.removeAttr('style');
					},1000);
				} else {
					// Ajax
					$.ajax({
						type: 'POST',
						url: '/empresa/mis-procesos/borrar-nota/',
						data: {
							'rel' : idNote
						},
						dataType: 'JSON',
						success: function(res) {
							if(res.status == 'ok') {
								msgAction.text(res.msg).addClass('good');
								var formCnt = msgAction.parents('.formFields');
								setTimeout( function() {
									formCnt.fadeOut(speed, function() {
										formCnt.remove();
										//Volviendo a enumerar
										perfilPost._enumador('.notaItemsP:visible');
									});
								},1000);
							} else {
								msgAction.text(res.msg).addClass('error');
							}
						},
						error : function(res) {
							msgAction.text(msgError).addClass('error');
						}
					});

				}
				return false;
			});
		},
		addNote : function(addNote) {
			var addNoteA = $(addNote),
			creatorNote = $('#creatorNote'),
			speed = 'fast',
			txtAddNote = 'A&ntilde;adir Nota',
			txtBack = 'Volver',
			txtAddMensaj = 'A&ntilde;adir Mensaje',
			creatorMensj = $('#creatorMensaje'),
			msgClickB = $('#btnAddMsjEPL');
			addNoteA.bind('click', function(e) {
				e.preventDefault();
				var t = $(this);
				creatorNote.parent().removeAttr('style');

				if(t.hasClass('openNewNote')) {
					creatorNote.slideUp(speed);
					t.removeClass('openNewNote').html(txtAddNote);
				} else {
					creatorNote.slideDown(speed);
					t.addClass('openNewNote').html(txtBack);
				}

				//Volver Mensaje
				if(msgClickB.hasClass('openNewMsj')) {
					creatorMensj.slideUp(speed);
					msgClickB.removeClass('openNewMsj').html(txtAddMensaj);
				}

				e.stopPropagation();
				return false;
			});
		},
		closeMsgPerfil : function(clickClose) {
			var clickCloseA = $(clickClose),
			speed = 'slow';
			clickCloseA.click( function(e) {
				e.preventDefault();
				var t = $(this),
				cnt = t.parents('.msgYellow');
				cnt.fadeOut(speed, function() {
					cnt.remove();
				});
				return false;
			});
		},
		//mensajes
		addMsj : function(addNMsjDiv) {
			var addNMsjDivA = $(addNMsjDiv),
			creatorMensj = $('#creatorMensaje'),
			speed = 'fast',
			txtAddMensaj = 'A&ntilde;adir Mensaje',
			txtBackMsj = 'Volver',
			txtAddNote = 'A&ntilde;adir Nota',
			creatorNote = $('#creatorNote'),
			noteClick = $('#btnAddNoteEPL');
			addNMsjDivA.bind('click', function(e) {
				e.preventDefault();
				var t = $(this);

				if(t.hasClass('openNewMsj')) {
					creatorMensj.slideUp(speed);
					t.removeClass('openNewMsj').html(txtAddMensaj);
				} else {
					creatorMensj.slideDown(speed);
					t.addClass('openNewMsj').html(txtBackMsj);
				}

				//Volver nota
				if(noteClick.hasClass('openNewNote')) {
					creatorNote.slideUp(speed);
					noteClick.removeClass('openNewNote').html(txtAddNote);
				}
				return false;
			}).trigger("click");
								$('#addNoteMsjTop #cuerpo').focus();
		},
		enviarMsj : function(enviarMsj) {
			var enviarMsjA = $(enviarMsj),
									 btnAddMsjT = $('#btnAddMsjEPL'),
									 speed = 'fast';
			if(enviarMsjA.size() > 0){
				//btnAddMsjT = $('#btnAddMsjEPL'); Aqui le puese el VAR
				//postTop = btnAddMsjT.offset().top - 20; Aqui le puse el VAR
										  var postTop = btnAddMsjT.offset().top - 20;
				enviarMsjA.bind('click', function(evt) {
					evt.preventDefault();
					$('html, body').animate({
						scrollTop:postTop
					}, speed);
					if(!(btnAddMsjT.hasClass('openNewMsj'))) {
						btnAddMsjT.trigger('click');
					}
					$('#addNoteMsjTop #cuerpo').focus();
					return false;
				});
			}
			perfilPost.fNoArroba('#fCACustomMsg');
		},
		winModalAlert : function() {
			var a = $('.winAlertM'),
			m = $('#mask'),
			w = $('.window'),
			c = $('.closeWM');
			a.live('click', function(e) {
				e.preventDefault();
				var t = $(this),
				i = t.attr('href'),
				msjDelete = t.attr('title'),
				mH = $(document).height(),
				mW = $(window).width(),
				s = 'fast',
				o = 0.50;
				m.css({
					'height':mH
				});

				// cadena solo #
				if( $.browser.msie && $.browser.version.substr(0,1) < 8 ) {
					var strI = i.split('#'),
					strId = strI[1];
					i = '#' + strId;
				}

				m.fadeTo(s,o);
				$(i).fadeIn(s);
				//title
				$(i).fadeIn(s).find('p#titleQ').text(msjDelete);

				// detect IE6 Version
				/*
				if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
					var posSectionIE6 = $(t.attr('rel')).offset().top;
					$(i).css({
						'top':posSectionIE6 + 50
					});
				}
				*/

				$(document).keyup( function(e) {
					if(e.keyCode === 27) {
						m.hide();
						w.hide();
					}
				});
				return false;
			});
			c.click( function(e) {
				e.preventDefault();
				m.hide();
				w.hide();

			});
			m.click( function(e) {
				$(this).hide();
				w.hide();
			});
		}
	};

	// init
	perfilPost.dropDown('#aLinkFlechaTV', '#listActionEV');

	perfilPost.invitarAnuncioWeb('.winInvitarProceso');

	perfilPost.moveraetapa(".aActionM");

	perfilPost.closeMsgPerfil('.icoCloseMsjD');
	perfilPost.moverdescartado('.descartarButton');

	perfilPost.verAnuncio('a.winVerProcesoA');

	//enviar msj
	perfilPost.enviarMsj('#sendMsjEPA');

	//Agregando Nota y Msj
	perfilPost.addNote('#btnAddNoteEPL');
	perfilPost.addMsj('#btnAddMsjEPL');

	//compartir
	perfilPost.fMail('#fCAMail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	perfilPost.fMail('#fCAMailDes',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	perfilPost.fInput('#fCAName',msgs.cName.good,msgs.cName.bad,msgs.cName.def);
	perfilPost.fInput('#fCANameDes',msgs.cName.good,msgs.cName.bad2,msgs.cName.def);
	//perfilPost.fAreaQ('.questionI',msgs.cQuestions.good,msgs.cQuestions.bad,msgs.cQuestions.def);
	perfilPost.fMail('#fEmail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	perfilPost.fSubmit('#fSendCA','#formShareCA',4,'#fCAName','#fCAMail','#fCANameDes','#fCAMailDes','#fCACustomMsg');
	perfilPost.resetBtn('.resetBtn', 300);
	perfilPost.resetBtnClose('.resetBtnClose', 300);
	perfilPost.charArea('#fCACustomMsg','#nCaracterP',300);

	//Msj Nota
	//Agregando indice
	//perfilPost.addIndice('#contentNotaEPL .formFields');

	perfilPost.acordionNM('.notaShot, .msjShot');
	//adjunto

	perfilPost.inputFileAttach('.attachEPL');

	//submit nota creacion
	perfilPost.submitAnadirNota('#addNoteMsjTop .dataBtnEP');
	//submit nota editar
	perfilPost.editSubmitNota('#contentNotaEPL .dataBtnEP');

	perfilPost.linkViewAll('.aBtnIn');
	perfilPost.editDataEP('.editEPL');
	perfilPost.filterCheckCnt('#pFiltroEPL', '#tFiltroEPL','#sFiltroEPL');
	perfilPost.winModalAlert();
	//
	perfilPost.closeMsgPerfil('.icoCloseMsjD');
	perfilPost.addIdDelete('.deleteEPL');

	perfilPost.inputFileChange('.inputAttachEPL');

	perfilPost.deleteNotePerf('#winAlert a.yesCM');
	perfilPost.closeAdjuntoP('.icoCloseMsjAdj');

	perfilPost.deleteAdjunto('.closeAdjInP');

	//No arroba
	perfilPost.fNoArroba('.editFormEP textarea');


};
var AptitusBolsaCV = function(){
	 var arrMonth = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'set', 'oct', 'nov', 'dic'],
	 day = urls.fDayCurrent,
	 month = urls.fMonthCurrent,
	 year = urls.fYearCurrent;
	 //Validando el cero del dia
	 if(day/10 < 1){
		  day = '0' + day;
	 }else{
		  day = day;
	 }
	 var perfilBolsaCV = {
		  addNoteBCVS : function(btnSave){
				var objBtnSave = $(btnSave);
				console.log("entraaa");
				$(document).on('click', btnSave, function(e){
					 e.preventDefault();
					 console.log("Llenado variables DOM");
					 var t = $(this),
					 cntMsj = t.next(),
					 cntArea = t.parent().siblings('textarea'),
					 valArea = $.trim(cntArea.val()),
					 idPostulante = t.attr('rel'),
					 cargador = t.parents('.cntFormNotaF').children('.showLoadF'),
					 createNote = $('#creatorNote'),
					 linkAddNote = $('#btnAddNoteEPL'),
					 cntNoteAdds = $('#contentNotaEPL');

					 console.log("T (button):");
					 console.log(t);
					 console.log("Mensaje de error:");
					 console.log(cntMsj);
					 console.log("textarea:");
					 console.log(cntArea);
					 console.log("Valor del textarea:");
					 console.log(valArea);
					 console.log("idPostulante:");
					 console.log(idPostulante);
					 console.log("showLoadF:");
					 console.log(cargador);
					 console.log("Div del formulario:");
					 console.log(createNote);
					 console.log("Link añadir nota/volver:");
					 console.log(linkAddNote);
					 console.log("Div mostrar notas:");
					 console.log(cntNoteAdds);

					 if(valArea.length > 0){
						  //Si hay data en el textarea
						  console.log("Si el length del txtarea es > 0");
						  cntMsj.text('');
						  createNote.slideUp('fast');
						  linkAddNote.removeClass('openNewNote').text('Añadir Nota');
						  cargador.removeClass('hide');
						  //Ajax configuracion
						  console.log("llenado de variables para ajax");
						  var nMethod = 'POST',
						  nUrl = '/empresa/bolsa-cvs/registrar-notas-bolsa',
						  nData = "nota="+valArea+"&idPostulante="+idPostulante,
						  nType = 'json',
						  nCargador = cargador,
						  nNote = cntNoteAdds,
						  nVal = valArea,
						  nId = idPostulante,
						  section = 'guardar',
						  nMsj = cntMsj,
						  nInfo = '';
						  //process note
						  perfilBolsaCV._ajaxNote(nMethod, nUrl, nData, nType, nCargador, nNote, nVal, nId, section, nMsj, nInfo);
						  cntArea.val('');
						  console.log("limpiado de textarea.")
						  //end
					 }else{
						  //vacio textarea
						  cntMsj.text('Ingrese la nota');
						  console.log("Si txtarea vacío.")
					 }

				});
		  },
		  editNoteBCVS : function(btnEdit){
				var objBtnEdit = $(btnEdit);
				objBtnEdit.live('click', function(){
					 var t = $(this),
					 cntMsj = t.next(),
					 cntArea = t.parent().siblings('textarea'),
					 valArea = $.trim(cntArea.val()),
					 idNote = t.attr('rel'),
					 parentTop = t.parents('.cntFormNotaF'),
					 cargador = parentTop.children('.showLoadF'),
					 cntInfoNote = cargador.next(),
					 createNote = $('#creatorNote'),
					 linkAddNote = $('#btnAddNoteEPL'),
					 cntNoteAdds = $('#contentNotaEPL');

					 if(valArea.length > 0){
						  //cargador
						  cargador.removeClass('hide');
						  cntInfoNote.addClass('hide');

						  //Ajax configuracion
						  var nMethod = 'POST',
						  nUrl = '/empresa/bolsa-cvs/editar-nota-bolsa',
						  nData = {
									 "idNota": idNote,
									 "nota": valArea
								},
						  nType = 'json',
						  nCargador = cargador,
						  nNote = cntNoteAdds,
						  nVal = valArea,
						  nId = idNote,
						  section = 'editar',
						  nMsj = cntMsj,
						  nInfo = cntInfoNote;
						  //process note
						  perfilBolsaCV._ajaxNote(nMethod, nUrl, nData, nType, nCargador, nNote, nVal, nId, section, nMsj, nInfo);
						  //end

					 }else{
							//vacio textarea
							cntMsj.text('Ingrese la nota');
					 }

				});

		  },
		  deleteNoteBCVS : function(btnDelete){
				var btnYes = $(btnDelete),
				speed = 'slow',
				msgEliminar = '... Eliminando',
				msgError = 'Hubo un error',
				msgGood = 'Se borró ok';
				$(btnYes).live('click', function(e){
					 e.preventDefault();
					 var t = $(this),
					 str = t.attr('rel').split('#'),
					 idNota = str[0],
					 div = str[1],
					 objDiv = $('#' + div),
					 close = t.siblings('.closeWM');

					 /*if(idNota == '-1'){
						  //Elimina Cliente
						  close.trigger('click');
						  var divElim = $('#' + div),
						  msgAction = divElim.find('.msgDataCurr');
						  msgAction.removeClass('error good').text(msgEliminar).fadeIn(speed, function(){
								msgAction.addClass('good').text(msgGood);
								setTimeout(function(){
									 objDiv.fadeOut(speed, function(){
										  objDiv.remove();
										 //Conteo
										  perfilBolsaCV._countNotes('#contentNotaEPL .notaItemsP');
									 });
								},500);
						  });
					 }else{*/
						  //Elimina Servidor
						  close.trigger('click');

						  var msgAction = objDiv.find('.msgDataCurr');
						  msgAction.removeClass('error good').text(msgEliminar).fadeIn(speed);
						  $.ajax({
								type: "POST",
								url: "/empresa/bolsa-cvs/eliminar-nota-bolsa",
								data: {
									 "idNota": idNota
								},
								dataType: "json",
								success: function(res){
									 if(res.status == 'ok'){
										  msgAction.addClass('good').text(msgGood);
										  setTimeout(function(){
												objDiv.fadeOut(speed, function(){
													 objDiv.remove();
													 //Conteo
													 perfilBolsaCV._countNotes('#contentNotaEPL .notaItemsP');
												});
										  },500);
									 }/*else{
										  msgAction.removeClass('good').addClass('bad').text(msgError);
									 }*/
								},
								error: function(){
									 msgAction.removeClass('good').addClass('bad').text(msgError);
								}
						  });
					/* }*/
				});

		  },
		  _ajaxNote : function(nMethod, nUrl, nData, nType, nCargador, nNote, nVal, nId, section, nMsj, nInfo){
				//Ajax note
				console.log("Ajax registro");
				$.ajax({
					 type: nMethod,
					 url: nUrl,
					 data: nData,
					 dataType: nType,
					 success: function(xhr) {
						  if(xhr.status == 'ok'){
							  console.log("Si todo ok!! success");
								var idP = $("#btnAddNoteEPL").attr('rel');
								//Seccion editar
								var data2 = "idPostulante="+idP+"&idNota="+nId;
								if(section == 'editar'){
									 //Removiendo la nota editada
									 nCargador.parent().remove();
									 data2 = data2 ;
								}else{
									 data2 = "idPostulante="+nId;
									 console.log("data2(idPostulante): " + data2);
								}
								//Retorno de Data
								perfilBolsaCV._ajaxReturnData(data2, nCargador, nNote );

						  }else if(xhr.status == 'warning'){
							  console.log("dentro de warning");
								nInfo.removeClass('hide');
								nCargador.addClass('hide');
								nMsj.text(xhr.msg);
						  }else{
							  console.log("error");
								nMsj.text(xhr.msg);
						  }
					 }
				});

		  },
		  _ajaxReturnData : function(tData, tCargador, tNote ){
				$.ajax({
					 type: "POST",
					 url: "/empresa/bolsa-cvs/get-vista-nota-bolsa",
					 data: tData,
					 dataType: "json",
					 complete: function(msg){
						 console.log("Ajax Get vista Nota")
						  //efecto cambiar nota
						  tCargador.addClass('hide');
						  $('html, body').animate({scrollTop:tNote.offset().top}, 'slow');
						  tNote.prepend(msg.responseText);
						  perfilBolsaCV.fNoArroba('#textNotaBolsa');
						 //Conteo
						  perfilBolsaCV._countNotes('#contentNotaEPL .notaItemsP');
						  //reset blocks note
						  perfilBolsaCV._resetNotes('#contentNotaEPL .notaShot', '#contentNotaEPL .blockInH');
						  //edicion de notas
						  $('.btnEditNota').unbind();
						  perfilBolsaCV.editNoteBCVS('.btnEditNota');
						  //Eliminar
						  $('#winAlertBCV .yesCM').unbind();
						  perfilBolsaCV.deleteNoteBCVS('#winAlertBCV .yesCM');
						  //agregando ID
						  $('.deleteEPLBCV').unbind();
						  perfilBolsaCV.addIdDeleteBCVS('.deleteEPLBCV');
						  //end
					 }
				});
		  },
		  _countNotes : function(notes){
			  var objNotes = $(notes),
			  size = objNotes.size();
			  var j = 0;
				for(var i = size; i > 0; i--){
					 j++;
					 objNotes.eq(i-1).text(j);
				}
		  },
		  _resetNotes : function(arrow, infoNote){
				var arrArrow = $(arrow),
				arrNote = $(infoNote),
				sizeArrow = arrArrow.size(),
				sizeNote = arrArrow.size();
				//reset
				arrArrow.removeClass('flagBlockEP');
				arrNote.removeAttr('style').addClass('hide');
				//abriendo ultimo
				arrArrow.eq(0).addClass('flagBlockEP');
				arrNote.eq(0).removeClass('hide');
		  },
		  addIdDeleteBCVS : function(clickAdd){
				var clickAddA = $(clickAdd);
				clickAddA.live('click', function(){
						  var t = $(this),
						  idFormField = t.parents('.blockHideC').attr('id'),
						  dataRel = (t.attr('rel') + '#' + idFormField),
						  yesOk = $('#winAlertBCV a.yesCM');
						  yesOk.attr('rel', dataRel);
				});
		  },
		fNoArroba : function(a) {
				return $(a).each( function() {
					 var t = $(this),
					 isShift = false,
					 epAroba = /a(r{1,2})[o{1}|0{1}][b{1}|v{1}]a/gi;
					 t.keypress( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  if(key == 64) {
								return false;
						  }
						  if(key == 18) {
								return false;
						  }
						  var car = String.fromCharCode(e.charCode);
						  if(car == '@') {
								return false;
						  }
						  return true;
					 });
					 t.keyup( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  var valTmp = t.val();
						  if(valTmp.search(epAroba)!=-1){
							  var newValTmp = valTmp.replace(epAroba,'');
							  t.val(newValTmp);
						  }
					 });
					 t.bind('paste', function(){
						  setTimeout(function() {
								var value = t.val(),
								newValue = value.replace(/[@]/g,''),
								newValue = newValue.replace(epAroba,'');
								t.val($.trim(newValue));
						  }, 0);
					 });
				});
		  }
	 };

	 //Añadir nota Bolsa CVS
	 perfilBolsaCV.addNoteBCVS('.dataBtnEPBolsa');
	 //Editar
	 perfilBolsaCV.editNoteBCVS('.btnEditNota');
	 //Eliminar
	 perfilBolsaCV.deleteNoteBCVS('#winAlertBCV .yesCM');
	 //addID
	 perfilBolsaCV.addIdDeleteBCVS('.deleteEPLBCV');
	 //No arroba
	 perfilBolsaCV.fNoArroba('.editFormEP textarea');
};
