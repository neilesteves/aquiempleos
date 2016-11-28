/*
Aviso empleo
*/
$(function(){
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
			bad : '¡Se requiere el mensaje!',
			def : 'Ingrese mensaje correcto'
		},	
		cQuestions : {
			good : '.',
			bad : 'Responder pregunta',
			def : 'Responda con criterio'
		},
		mailSend : 'El e-mail fue enviado.',
		mailError : 'No se pudo enviar el e-mail.'
	}
	var vars = {
		rs : '.response',
		okR :'ready',
		sendFlag : 'sendN',
		loading : '<div class="loading"></div>'
	}
	var sharePerfil = {
	fMail : function(a,good,bad,def){
		$(a).focus();
		$(a).bind('blur', function(){
			var t = $(this),
					r = t.next(vars.rs),
					ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
			if(ep.test(t.val())&& t.val()!=''){
				r.removeClass('bad').addClass('good').text(good);
				t.addClass(vars.okR);
			}else{
				r.removeClass('good').addClass('bad').text(bad);
				t.removeClass(vars.okR);
			}
		});
	},
	fInput : function(a,good,bad,def){
		var A = $(a),
				r = A.next(vars.rs);
		A.blur(function(){
			var t = $(this);
			if(t.val().length>0){
				r.removeClass('bad').addClass('good').text(good);
				t.addClass(vars.okR);
			}else{
				r.removeClass('good').addClass('bad').text(bad);
				t.removeClass(vars.okR);
			}
		}).keyup(function(){
			var t = $(this);
			if(t.val().length===0){
				r.removeClass('good').addClass('bad').text(bad);
				t.removeClass(vars.okR);				
			}else{
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
		A.bind('click', function(e) {
                    
			e.preventDefault();
			var t = $(this),
			urlSlug = t.attr('rel'),
			nombreEmisor = F1.val(),
			correoEmisor = F2.val(),
			nombreReceptor = F3.val(),
			correoReceptor = F4.val(),
			mensajeCompartir = F5.val(),
			hdnOculto = $('input#hdnOculto').val();
			if(B.find('.'+vars.okR).size()>=c) {
				B.addClass('hide');
				$('#loadMFF').remove();
				$('#iEscpF').append('<div id="loadMFF" class="loading"></div>');
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
						'hdnOculto' : hdnOculto
					},
					'success' : function(res) {
						var frm = $('#iEscpF');
                                                var frmShare = $('#shareMail');
						if(res.status == 'ok') {
							B.addClass('hide');
							$('#loadMFF').remove();
							//frm.append('<div id="loadMFF" class="block"><div class="good msjLoadMFF">' + res.msg + '</div></div>');
                                                        frmShare.append('<div id="loadMFF" class="block"><div class="good msjLoadMFF">' + res.msg + '</div></div>');
							sharePerfil._clearFields(frm);
                                                        window.location.reload();
						} else {								
							B.addClass('hide');
							$('#loadMFF').remove();
							frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
							sharePerfil._clearFields(frm);
						}
					},
					'error' : function(res) {
						var frm = $('#iEscpF');
						B.addClass('hide');
						$('loadMFF').remove();
						frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
						sharePerfil._clearFields(frm);
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
		});
	},
	fAreaQ : function(a,good,bad,def){
		var A = $(a);
		A.blur(function(){
			var t = $(this),
					r = t.next(vars.rs);
			if(t.val().length>0){
				r.removeClass('bad').addClass('good').text(good);
				t.addClass(vars.okR);
			}else{
				r.removeClass('good').addClass('bad').text(bad);
				t.removeClass(vars.okR);
			}
		}).keyup(function(){
			var t = $(this),
					r = t.next(vars.rs);
			if(t.val().length===0){
				r.removeClass('good').addClass('bad').text(bad);
				t.removeClass(vars.okR);				
			}else{
				r.removeClass('good bad').text(def);
				t.addClass(vars.okR);					
			}
		});
	},	
	fSendQuestions : function(a,b,c){
		var A = $(a),
				B = $(b),
				C = $(c),
				sizeC = C.size();
		A.click(function(e){
			e.preventDefault();
			var t = $(this),
					nSizeC = $(b+' .'+vars.okR).size();
			if(sizeC == nSizeC){
				B.submit();
				$(c).val('');
			}
			if(sizeC < nSizeC || sizeC > nSizeC){
				$(c).next(vars.rs).removeClass('good def').addClass('bad').text(msgs.cQuestions.bad);
			  $(b+' .'+vars.okR).next(vars.rs).removeClass('bad def').addClass('good').text(msgs.cQuestions.good);					
			}				
		});
	},
	resetBtn : function(btnReset, chars){
		btnR = $(btnReset);
		btnR.click(function(){
			var t = $(this),
			frm = t.parents('form');
			sharePerfil._clearFields(frm);
			$('#nCaracterP').text(chars);
		});
	},
	_clearFields : function(frm){
		frm.find('.inputReset').val('').removeClass('ready').siblings('.response').text('').removeClass('good bad').addClass('def');
	},
	resetBtnClose : function(btnCloseReset, chars){
		btnRC = $(btnCloseReset);
		btnRC.click(function(){
			var t = $(this),
			frm = $('#formShareCA');
			sharePerfil._clearFields(frm);
			frm.removeClass('hide');
			$('#loadMFF').remove();
			$('#nCaracterP').text(chars);  
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
	}			
	};	
// init
	//perfil publico
	sharePerfil.fMail('#fCAMail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	sharePerfil.fMail('#fCAMailDes',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);	
	sharePerfil.fInput('#fCAName',msgs.cName.good,msgs.cName.bad,msgs.cName.def);
	sharePerfil.fInput('#fCANameDes',msgs.cName.good,msgs.cName.bad2,msgs.cName.def);
	sharePerfil.fAreaQ('.questionI',msgs.cQuestions.good,msgs.cQuestions.bad,msgs.cQuestions.def);
	sharePerfil.fMail('#fEmail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
	sharePerfil.fSubmit('#fSendCA','#formShareCA',4,'#fCAName','#fCAMail','#fCANameDes','#fCAMailDes','#fCACustomMsg');
	sharePerfil.fSendQuestions('#fQSendCA','#formQuestions','.questionI');
	sharePerfil.resetBtn('.resetBtn', 300);
	sharePerfil.resetBtnClose('.resetBtnClose', 300);
	sharePerfil.charArea('#fCACustomMsg','#nCaracterP',300);
});