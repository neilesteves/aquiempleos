/*
helpers Aptitus
*/
$(function() {
var msgs = {
		cDef : {
			good :'.',
			bad : 'Campo Requerido',
			def :'Opcional'
		},
		cPass : {
			good : '.',
			bad : 'Debe ingresar una contraseña válida.',
			def : 'Ingresa tu contraseña'
		},
		cEmail : {
			good : '.',
			bad : 'Debe ingresar su dirección e-mail.',
			def : 'Ingrese mail correcto'
		},
		passForgot : {
			good : 'Su nueva contraseña fue enviada.',
                        //mailInvalid : 'El e-mail ingresado no existe.',
                        mailInvalid : 'El e-mail ingresado debe estar asociado a su cuenta',
			bad : 'Error al enviar su nueva contraseña.'
		},
		ajaxData : {
			error : 'Datos inválidos.',
			good : 'Datos correctos',
			mailValid : 'Email ya registrado.',
			mailGood : 'Email disponible.'
		}						
	};
//class 
var Aptitus = function(opts){  
	//window modal and alert
	this.winModal = function(){
		var a = $('.winModal'),
				m = $('#mask'),
				w = $('.window'),
				c = $('.closeWM'),
				s = 'fast',
				o = 0.50; 
		var jash = $.trim(window.location.hash).split("-");
    var url = location.href.substring(7,location.href.length).split("/");
		if(jash.length > 0 && jash[0]!="" && url[1].substring(0,url[1].length-1)!="postulaciones") {
			if($('body').find('"' + jash[0] + '"').size() > 0){
				var mH = $(document).height(),
				mW = $(window).width();
				$('html, body').animate({scrollTop:0}, s);
				m.css({'height':mH});		
				m.fadeTo(s,o);				
				$(jash[0]).fadeIn(s);
				$(document).keyup(function(e){
				  if(e.keyCode === 27) {
						m.hide();w.hide();
				  }
				});						
			}
      if(jash.length==2) {
      	$(jash[0]+" input[name=return]").val(Base64.decode(jash[1]));
      }
		}									
		a.live('click',function(e){
			e.preventDefault();
			var t = $(this),
				  i = t.attr('href'),
					mH = $(document).height(),
					mW = $(window).width();					
					if(!(t.hasClass('noScrollTop'))){
						$('html, body').animate({scrollTop:0}, s);
					}						
			// cadena solo # 
			if( $.browser.msie && $.browser.version.substr(0,1) < 8 ) {
				var strI = i.split('#'),
				strId = strI[1];
				i = '#' + strId;   
			}							
			m.css({'height':mH});			
			m.fadeTo(s,o);	
			$(i).fadeIn(s);			
			$(document).keyup(function(e){
			  if(e.keyCode === 27) {
					m.hide();w.hide();
			  }
			});	
			/* url aplicada */				
			var oRedirect = t.attr('return');
			if(oRedirect){
				var receptorUrl = $('#return');
				receptorUrl.val(oRedirect);
			}														 
		});
		c.click(function(e){
			e.preventDefault();
			var linkCloseX = $(this),
			content = linkCloseX.parent();
			m.hide();w.hide();		
			if(linkCloseX.hasClass('closeRegiFast')){
				var inputsNRP = content.find('input.inputRpm');
				//reset
				$.each(inputsNRP, function(i, val){
					var inptRPEA = inputsNRP.eq(i);
					if($.trim(inptRPEA.val()) != ''){
						inptRPEA.val('').removeClass('ready bienRegFast malRegFast').parents('.placeHRel').find('.txtPlaceHR').removeClass('hide');
					}			
				});
				var inputsNSM = $('input#wmPMail'),
				altIpt = inputsNSM.attr('alt');
				inputsNSM.removeClass('readyLogin').addClass('cGray').val(altIpt);
				$('#wmPPass').removeClass('readyLogin').val('');
				content.find('.respW').removeClass('bad good').text('');	
				$('#textForgotPReg').val('');	

				$('#cntRegisterWM').css('display','block');
				$('#cntForgotPReg').css('display','none');	
			}else if(linkCloseX.hasClass('closeResLogin')){
				//reset Login
				var mailRT = $('#wmMail'),
				passRT = $('#wmPass'),
				reMail = $('#textForgotP');
				mailRT.val(mailRT.attr('alt')).removeClass('readyLogin').addClass('cGray').next().removeClass('bad good').text('');				
				passRT.val('').removeClass('readyLogin').next().removeClass('bad good').text('');
				reMail.val('');
			}			
		});		
		m.click(function(e){
			$(this).hide();w.hide();
		});			
	};	
	this.placeholder = function(){
		var tr = $('input.placeH, textarea.placeH');
		tr.focus(function(){
			var t = $(this);
			if(t.val() == t.attr('alt')){t.val('').removeClass('cGray');}
		});		 
		tr.blur(function(){
			var t = $(this);			
			if(t.val() == ''){t.val(t.attr('alt')).addClass('cGray');}
		});
	};		
	this.areaWM = function(){
		var A = $('.winIModal'),
				B = $('.moreWM'),
				C = $('.closeH0WM'),
				so = 'fast';
		A.bind('click', function(e){
			e.preventDefault();
			var t = $(this);
					id = t.attr('href'),
					tP = t.position().top,
					idH = $(id).height();					
			B.hide();
			$(id).css('top',tP-idH-30).fadeIn(so);

			$(document).keyup(function(e){			
				if(e.keyCode === 27) {
						C.trigger('click');
				}
			});			
		});
		C.bind('click', function(e){
			e.preventDefault();
			var t = $(this);
			B.fadeOut(so);	
		});
	};		
	this.searchA = function(){
		var A = $('#searchAdv'),
				B = $('#advanced'),
				C = $('#fieldSearch'),
				D = $('#sbtSearchC'),
				so = 'fast';				
		A.live('click', function(e){
			e.preventDefault();
			var t = $(this);
			if(!(t.hasClass('sAdvS'))){				
				B.slideDown(so, function(){
				t.addClass('sAdvS').text('Cerrar búsqueda avanzada').parent().addClass('changeLCA');
				C.addClass('fieldAdv');
				D.removeClass('btnAdv');	
			});								
			}else{		
				B.slideUp(so, function(){
					t.removeClass('sAdvS').text('Búsqueda avanzada').parent().removeClass('changeLCA');
					C.removeClass('fieldAdv');
					D.addClass('btnAdv');						
				});
				t.unbind();
			}					
		});
	};
	this.login = function(){
		var A = $('.btnLoginEPA'),
		    okL = 'readyLogin',
		    resp = '.respW',
		    cntMsjA = $('.msgLRAll');
		 function inputReq(a,good,bad,def){
			var A = $(a);
			A.blur(function(e){
				cntMsjA.removeClass('bad good').text('');
				var t=$(this),
						r=t.next(resp);
				if($.trim(t.val())!=='' && t.val() !== t.attr('alt') ){
					t.addClass(okL);
					r.addClass('good').removeClass('bad hide def').text(good);
				}else{
					t.removeClass(okL).next(resp).addClass('bad').removeClass('good hide def').text(bad);
				}		
			}).keypress(function(){
				var t = $(this),
						r=t.next(resp);
				if(t.val().length===0 && t.val() !== t.attr('alt') ){
					t.removeClass(okL);
					//r.removeClass('good hide def').addClass('bad').text(bad);
				}else{
					t.addClass(okL);
					r.removeClass('bad good hide').addClass('def').text(def);
				}					
			});				
		}			
		inputReq('#wmPass' , msgs.cPass.good , msgs.cPass.bad , msgs.cPass.def );		
		inputReq('#wmPPass' , msgs.cPass.good , msgs.cPass.bad , msgs.cPass.def );					
	 	function fMail(a,good,bad,def){
			$(a).bind('blur', function(){
				cntMsjA.removeClass('bad good').text('');
				var t = $(this),
						r = t.next(resp),
						ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				if(ep.test(t.val())&& t.val()!=''){
					r.removeClass('bad hide').addClass('good').text(good);
					t.addClass(okL);
				}else{
					r.removeClass('good hide').addClass('bad').text(bad);
					t.removeClass(okL);
				}
			});
		}		
		fMail('#wmMail' , msgs.cEmail.good , msgs.cEmail.bad , msgs.cEmail.def );	
		fMail('#wmPMail' , msgs.cEmail.good , msgs.cEmail.bad , msgs.cEmail.def );					
		A.bind('click', function(e){
			e.preventDefault();			
			var t = $(this);			
			var flagIdSct = $.trim(t.attr('id'));
			if( flagIdSct == 'sLoginWMH' ){
				// Login Normal
				var	M = $('#wmMail'),
						MValue = M.val(),
						P = $('#wmPass'),
						PValue = P.val(),
						rM = M.next(resp),
						rP = P.next(resp),
						_form = '#fLoginWMH',
						formFlag = $(_form),
						tipoAB = 'input#tipo',
						iptToken = 'input#auth_token',
						urlReturn = 'input#return',
						cntMsj = $('#loginP .msgLRAll'),
						id_tarifa = '';				
			}else{	
				// Login registro rapido
				var	M = $('#wmPMail'),
						MValue = M.val(),
						P = $('#wmPPass'),
						PValue = P.val(),
						rM = M.next(resp),
						rP = P.next(resp),
						_form = '#fRegisterWMH',
						formFlag = $(_form),
						tipoAB = 'input#tipo_registro',
						iptToken = 'input#auth_token_registro',
						urlReturn = 'input#return_registro',
						cntMsj = $('#registerP .msgLRAll'),
						id_tarifa = $('#hideLoginReg').val();				
			}													
			if( M.hasClass(okL) && P.hasClass(okL) ){
				if(formFlag.hasClass('winLEmpresa')){
					//empresa
					var emailEP = MValue, passEP = PValue, 
					tipoEP = $(tipoAB).val(),
					auth_token = $(iptToken).val();							 
					cntMsj.text('').removeClass('hide bad good').addClass('loading');
					formFlag.addClass('hide');
					M.removeClass(okL).addClass('cGray').val(M.attr('alt'));
					rM.text('');
					P.removeClass(okL).addClass('cGray').val('');
					rP.text('');
					$.ajax({
						'url' : '/auth/login-ajax/',
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'userEmail' : emailEP,
							'userPass' : passEP,
							'tipo' : tipoEP,
							'auth_token' : auth_token,
							'id_tarifa' : id_tarifa
						},
						'success' : function(res){								
							if(res.status == 'ok'){
								formFlag.addClass('hide');
								urlRedict = $(urlReturn).val();
								cntMsj.removeClass('loading bad').addClass('good').text(msgs.ajaxData.good);		
								window.location = urlRedict;
							}else{
								formFlag.removeClass('hide');
								cntMsj.removeClass('loading good').addClass('bad').text(res.msg);
							}
						},
						'error' : function(res){
							formFlag.removeClass('hide');
							cntMsj.removeClass('loading good').addClass('bad').text(msgs.ajaxData.error);						
						}
					});								
				}else{
					//postulante
					formFlag.submit();
				}
			}else{
				cntMsj.removeClass('bad good').text('');
				if( M.hasClass(okL) ){
					rM.removeClass('hide def bad').addClass('good').text(msgs.cEmail.good);
				}else{
					rM.removeClass('hide def good').addClass('bad').text(msgs.cEmail.bad);
				}		
				if( P.hasClass(okL) ){
					rP.removeClass('hide def bad').addClass('good').text(msgs.cPass.good);							
				}else{
					rP.removeClass('hide def good').addClass('bad').text(msgs.cPass.bad);
				}												
			}															
		});	
		if( $.browser.msie && $.browser.version.substr(0,1) < 9 ) {		
			$('#fLoginWMH input').keydown(function(e){
				var keyC = e.keyCode || e.charCode || e.which || window.e ;
	      if (keyC == 13) {
          $('#fLoginWMH #sLoginWMH').trigger('click');
          return false;
	      }
	    });		
		}		
	};
	this.flashMsg = function(){
    var mensajes = $('.flash-message'),
    h = 0,
    s = 'middle',
    interval = '3000';
    $.each(mensajes, function(k, v){
     h = 1000 * (k);
     setTimeout(function(){
      $(v).fadeIn(s, h, function(){
        setTimeout(function(){
          $(v).fadeOut(s);
        }, h + interval);
      });
     },h);
    });		
	};	
	this.forgotPass = function(){	
		var A = $('#forgotPass'),
				B = $('#cntLoginWM'),
				C = $('#cntForgotP'),
				D = $('#backLogWM'),
				SF = $('#sendForgotP'),
				form = $('#fForgotPass'),
				email = $('#textForgotP'),
				resp = $('#responseFP'),
				loading = $('#loadingCFP'),
				errorCmp = $('#errorCmp'),
				so = 'fast';						
		A.click(function(e){		
			e.preventDefault();			
			B.slideUp(so, function(){
				C.slideDown();				
			});					
		});		
		D.click(function(e){
			e.preventDefault();
			C.slideUp(so, function(){
				B.slideDown();
				form.removeClass('hide');
				errorCmp.text('');
				resp.text('');
				email.val('');				
			});
		});		
		fMail('#sendForgotP' , msgs.cEmail.good , msgs.cEmail.bad , msgs.cEmail.def );			
	 	function fMail(a,good,bad,def){
			$(a).bind('click', function(e){
				e.preventDefault();			
				loading.removeClass('hide').addClass('loading');
				form.addClass('hide');
				var t = $(this),
						trigger = $('#textForgotP');
						r = $('#responseFP'),
						okL = 'okGo',
						ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				if(ep.test(trigger.val())&& trigger.val()!=''){
					//form.submit();
					var emailFP = $.trim(email.val());
					var idEmail = emailFP,
					iptToken = $('#recuperar_token'),
					authToken = $.trim($('#recuperar_token').val());
					$.ajax({
						'url' : '/auth/recuperar-clave/',
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'email' : idEmail,
							'recuperar_token' : authToken
						},
						'success' : function(res){								
							if(res.status == 'error'){
								loading.addClass('hide');	
								errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.bad);
								email.val('');									
							}else if(res.status == 'mailinvalid'){
								loading.addClass('hide');	
								errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.mailInvalid);
								email.val('');									
							}else{
								loading.addClass('hide');																
								errorCmp.removeClass('hide').addClass('good').text(msgs.passForgot.good);
								email.val('');
								//nuevo token
								iptToken.val(res.hash_token);																																	
							}
						},
						'error' : function(res){
                                                        loading.addClass('hide');
                                                        if(res.status == 'mailinvalid'){	
                                                            errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.mailInvalid);								
							}else{
                                                            errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.bad);
                                                        }
							email.val('');		
						}
					});				
				}else{		
					loading.addClass('hide');		
					form.removeClass('hide');				
					r.removeClass('good hide').addClass('bad').text(bad);
					t.removeClass(okL);
				}
			});
		}					
	};
	this.forgotPassReg = function(){
		if($('#iRegisterP').size() >= 1){
			var Areg = $('#forgotPassReg');
					Breg = $('#cntRegisterWM'),
					Creg = $('#cntForgotPReg'),
					Dreg = $('#backLogWMReg'),
					SFreg = $('#sendForgotPReg'),
					formreg = $('#fForgotPassReg'),
					emailreg = $('#textForgotPReg'),
					respreg = $('#responseFPReg'),
					loadingreg = $('#loadingCFPReg'),
					errorCmpreg = $('#errorCmpReg'),
					soReg = 'fast';						
			Areg.click(function(e){			
				e.preventDefault();
				Breg.slideUp(soReg, function(){
					Creg.slideDown();				
				});					
			});		
			Dreg.click(function(e){
				e.preventDefault();
				Creg.slideUp(soReg, function(){
					Breg.slideDown();
					formreg.removeClass('hide');
					errorCmpreg.text('');
					respreg.text('');
					emailreg.val('');				
				});
			});				
	 	function fMailReg(a,good,bad,def){
			$(a).bind('click', function(e){
				e.preventDefault();			
				loadingreg.removeClass('hide').addClass('loading');
				formreg.addClass('hide');
				var t = $(this),
						trigger = $('#textForgotPReg');
						r = $('#responseFPReg'),
						okL = 'okGo',
						ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				if(ep.test(trigger.val())&& trigger.val()!=''){
					//form.submit();
					var emailFP = $.trim(emailreg.val());
					var idEmail = emailFP,
					authToken = $.trim($('#recuperar_token').val()) ;
					$.ajax({
						'url' : '/auth/recuperar-clave/',
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'email' : idEmail,
							'recuperar_token' : authToken
						},
						'success' : function(res){								
							if(res.status == 'error'){
								loadingreg.addClass('hide');	
								errorCmpreg.removeClass('hide').addClass('bad').text(msgs.passForgot.bad);
								email.val('');									
							}else if(res.status == 'mailinvalid'){	
								loadingreg.addClass('hide');
                                                                errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.mailInvalid);	
                                                                emailreg.val('');
							}else{
								loadingreg.addClass('hide');																
								errorCmpreg.removeClass('hide').addClass('good').text(msgs.passForgot.good);
								emailreg.val('');																								
							}
						},
						'error' : function(res){
							loading.addClass('hide');
                                                        if(res.status == 'mailinvalid'){	
								errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.mailInvalid);								
							}else{
                                                            errorCmp.removeClass('hide').addClass('bad').text(msgs.passForgot.bad);
                                                        }
							email.val('');								
						}
					});				
				}else{		
					loadingreg.addClass('hide');		
					formreg.removeClass('hide');				
					r.removeClass('good hide').addClass('bad').text(bad);
					t.removeClass(okL);
				}
			});
		}						
		fMailReg('#sendForgotPReg' , msgs.cEmail.good , msgs.cEmail.bad , msgs.cEmail.def );					
		}									
	};	
	this.registerFast = function(){
		var frm = $('#formResgiRap'),
		btnRP = $('#btnRegCont'),
		inputsN = frm.find('input.inputRpm');
		//reset
		$.each(inputsN, function(i, val){
			var inpt = $('input.inputRpm').eq(i);
			if($.trim(inpt.val()) != ''){
				inpt.addClass('ready bienRegFast').removeClass('malRegFast').parents('.placeHRel').find('.txtPlaceHR').addClass('hide');
			}			
		});
		//functions			
		if(frm.size() == 1){
		 	function fMail(a){
				$(a).bind('blur', function(){
					var t = $(this),
							ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
					if(ep.test(t.val())&& t.val()!=''){
						//t.addClass('ready bienRegFast').removeClass('malRegFast');
						_fMailValid(t);
					}else{
						t.removeClass('ready bienRegFast').addClass('malRegFast');
					}
				});
			}
			function _fMailValid(t){
				var $email = t,
				msgMail = $('#msgMailFRP');
					msgMail.addClass('hide').text('');	
					$email.addClass('loadingMail');	
	  			$.ajax({
	  				url: '/registro/validar-email/',
	  				type: 'POST',
	  				data: {
	  					email: $email.val(), 
	  					modulo: 'empresa'
	  				},
	  				dataType: 'JSON',	    				
	  				success: function(response){
	  					if(response.status == true){
	  						$email.addClass('ready bienRegFast').removeClass('malRegFast loadingMail');
	  						msgMail.removeClass('hide bad').addClass('good').text(msgs.ajaxData.mailGood);
	  					}else{
	  						$email.removeClass('ready bienRegFast loadingMail').addClass('malRegFast');
	  						msgMail.removeClass('hide good').addClass('bad').text(msgs.ajaxData.mailValid);
	  					}
	  				},
	  				error : function(response){
  						$email.removeClass('ready bienRegFast loadingMail').addClass('malRegFast');
  						msgMail.removeClass('hide good').addClass('bad').text(msgs.ajaxData.mailValid);  					
	  				}
	  			});
			}						
			function fINum(a) {
				return $(a).each( function() {
					var t = $(this);
					t.keydown( function(e) {
						var key = e.keyCode || e.charCode || e.which || window.e ;
						return (key == 8 || key == 9 || key == 32 ||
							(key >= 48 && key <= 57)||
							(key >= 96 && key <= 105)||
							key==109 || key==116 );
					});
				});
			}
			function fInput(a) {			
				var A = $(a);
				A.blur( function() {
					var t = $(this),
					lgt = t.val().length;					
					if(lgt > 0) {			
						t.addClass('ready bienRegFast').removeClass('malRegFast');			
						if(t.attr('id') == 'wmRUCemp'){
							//RUC
							if(lgt == 11) {
								t.addClass('ready bienRegFast').removeClass('malRegFast');							
							}else{
								t.removeClass('ready bienRegFast').addClass('malRegFast');								
							}
						}						
					} else {
						t.removeClass('ready bienRegFast').addClass('malRegFast');
					}					
				}).keyup( function() {
					var t = $(this);
					
					if(t.val().length===0) {
						t.removeClass('ready');
					} else {
						//no ejecuta
					}								
				});				
			}							
			function maxLenghtInput(trigger){
				var input = $(trigger);	
				$.each(input, function(i,v){
					$(v).attr('dataMaxlength',$(v).attr('maxlength'));
				});					
				input.bind('keyup click blur focus change paste', function(e){
					var t = $(this),
					numMax = parseInt(t.attr('dataMaxlength')),
					valueArea;
					var key = e.which;
					var length = t.val().length;
					if( length > numMax ) {
						valueArea = t.val().substring(numMax, '') ;
						input.val(valueArea);
					}
				});						
			}
	 		function fRegRapPass(field1, field2, max) {
				var shot1 = $(field1),
				shot2 = $(field2);
				shot1.blur( function() {
					var t = $(this),
					lgt = t.val().length;					
					if(lgt >= max){												
						t.addClass('ready bienRegFast').removeClass('malRegFast');						
						if($.trim(t.val()) == $.trim(shot2.val())){
							shot2.addClass('ready bienRegFast').removeClass('malRegFast');							
						}else{
							shot2.removeClass('ready bienRegFast').addClass('malRegFast');
						}	
					}else{
						t.removeClass('ready bienRegFast').addClass('malRegFast');
					}					
				}).keydown( function() {
					var t = $(this);
					if(t.val().length===0) {
						t.removeClass('ready bienRegFast').addClass('malRegFast');
					} else {
						t.removeClass('malRegFast bienRegFast');
					}
				});					
			}			
			fMail('#wmRPMail');			
			fInput('#wmRUCemp');	
			fINum('#wmRUCemp');
			maxLenghtInput('#wmRUCemp');
			fInput('#wmRazSoc');							
			fInput('#wmContactemp');
			fInput('#wmTlfemp');	
			fINum('#wmTlfemp');			
			fRegRapPass('#wmClaveemp','#wmReClaveemp',6);
			fRegRapPass('#wmReClaveemp','#wmClaveemp',6);				
			//submit ajax
			btnRP.click(function(e){
				e.preventDefault();
				var numMaxReady = 7;
				ready = frm.find('input.ready').size();
				if(ready >= numMaxReady){
					//frm.submit();
					returnURL = $('#formResgiRap input#return_registro').val(),
					idProdF = $('#formResgiRap input#hideRegisterRReg').val(),
					iptMail = $('#formResgiRap input#wmRPMail').val(),
					iptRazSoc = $('#formResgiRap input#wmRazSoc').val(),
					iptRUC = $('#formResgiRap input#wmRUCemp').val(),
					iptNameC = $('#formResgiRap input#wmContactemp').val(),
					iptTlfC = $('#formResgiRap input#wmTlfemp').val(),
					iptClaveE = $('#formResgiRap input#wmClaveemp').val(),
					iptClaveRePE = $('#formResgiRap input#wmReClaveemp').val(),
					msgRegRap = $('#msgResgiRap');	
					
					frm.addClass('hide');
					msgRegRap.removeClass('bad hide good').addClass('loading minFrm').text('');
					$.ajax({
						'url' : frm.attr('action'),
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'email' : iptMail,
							'razonsocial' : iptRazSoc,
							'num_ruc' : iptRUC,
							'contacto' : iptNameC,
							'telefono' : iptTlfC,
							'pswd' : iptClaveE,
							'pswd2' : iptClaveRePE,
							'id_tarifa' : idProdF													
						},
						'success' : function(res){					
							if(res.status == 'ok'){
								msgRegRap.removeClass('loading hide minFrm').addClass('good').text(msgs.ajaxData.good);
								frm.removeClass('hide').find('input.inputRpm').val('').removeClass('bienRegFast ready malRegFast').parents('.placeHRel').find('.txtPlaceHR').removeClass('hide');
								$('#msgMailFRP').addClass('hide').removeClass('bienRegFast malRegFast').text('');										
								window.location = returnURL;
							}else{
								msgRegRap.removeClass('loading hide good minFrm').addClass('bad def').text(msgs.ajaxData.error);
								frm.removeClass('hide').find('input.inputRpm').val('').removeClass('bienRegFast ready malRegFast').parents('.placeHRel').find('.txtPlaceHR').removeClass('hide');
								$('#msgMailFRP').addClass('hide').removeClass('bienRegFast malRegFast').text('');	
							}
						},
						'error' : function(res){
							msgRegRap.removeClass('loading hide good minFrm').addClass('bad def').text(msgs.ajaxData.error);
							frm.removeClass('hide').find('input.inputRpm').val('').removeClass('bienRegFast ready malRegFast').parents('.placeHRel').find('.txtPlaceHR').removeClass('hide');		
							$('#msgMailFRP').addClass('hide').removeClass('bienRegFast malRegFast').text('');	
						}
					});															
				}else{
					$('#formResgiRap input.inputRpm').not('#formResgiRap input.ready').addClass('malRegFast').removeClass('ready bienRegFast');
				}
			});						
		}
	};
	this.placeholderRel = function(){
		var tr = $('input.placeHRel, textarea.placeHRel'),
		trText = $('.txtPlaceHR');
		tr.bind('focus', function(){
			var t = $(this),
			txtPlaceH = t.parents('.placeHRel').find('.txtPlaceHR'),
			textP = txtPlaceH.text();
			if($.trim(t.val()) == ''){ 
				txtPlaceH.addClass('hide'); 
			}
		});		 
		tr.bind('blur', function(){
			var t = $(this),
			txtPlaceH = t.parents('.placeHRel').find('.txtPlaceHR'),
			textP = txtPlaceH.text();						
			if($.trim(t.val()) == ''){ 
				txtPlaceH.removeClass('hide').text(textP); 
			}
		});
		trText.bind('click', function(){
			var t = $(this),
			inputPlaceH = t.parents('.placeHRel').find('.placeHRel'),
			textP = t.text();						
			if($.trim(inputPlaceH.val()) == ''){ 
				t.addClass('hide');
				inputPlaceH.focus(); 
			}
		});		
	};			
	this.tooltipApt = function(){
		var trigger = $('.tooltipApt');
		var arrTitle = [] ;	
		$.each(trigger, function(i,v){
			$(v).attr('rel',i);
			arrTitle.push($(v).attr('title'));
		});	
		trigger.mouseenter(function(e){
			var body = $('body');
			var t = $(this),
					tHeight = t.innerHeight(),
					pos = t.offset(),
					posLeft = pos.left,
					posTop = pos.top + tHeight;
					var tool = '<div class="tooltipCnt" style="left:' + posLeft + 'px; top:' + posTop + 'px">' + arrTitle[parseInt(t.attr('rel'))] + '</div>';
					body.append(tool);
					t.removeAttr('title');
					//ie alt
					t.attr('alt','');
		}).mouseleave(function(){
			var t = $(this);
			t.attr('title',arrTitle[parseInt(t.attr('rel'))]);
			$('.tooltipCnt').remove();
		});
	};		
	this.areaOverflow = function(trigger){			
			var nextAH = $(trigger + ' .nextHA'),
			cntBlockAbs = $(trigger + ' .iHomeA'),
			prevAH = $(trigger + ' .prevHA'), 
			countAH = 1;		
			nextAH.click(function(e){
				e.preventDefault();
				var t = $(this),
				heightHA = $(trigger + ' .iHomeA').height(),
				hits = Math.ceil(heightHA/210);
				
				if(countAH <= hits-1 && countAH > 0){
					countAH ++;
					if(countAH == hits){
						t.addClass('offNext');	
					}				
					prevAH.removeClass('offPrev');
					cntBlockAbs.animate({
					'top' : -210 * (countAH - 1)
					});	
				}					
			});	
			prevAH.click(function(e){
				e.preventDefault();
				var t = $(this),
				heightHA = $(trigger + ' .iHomeA').height(),
				hits = Math.ceil(heightHA/210);
				
				if(countAH > 1 && countAH <= hits ){
					countAH --;
					if(countAH == 1){
						t.addClass('offPrev');
					}					
					nextAH.removeClass('offNext');
					cntBlockAbs.animate({
					'top' : -210 * (countAH - 1)
					});	
				}			
			});				
		};
    this.formFind = function(){
      $("#formFind").bind("submit",function(){
        var urlId = $.trim($('input[name=urlId]').val());
        if (urlId != 'Tengo el código!') {
      	  url = this.action+'/urlid/'+urlId;
      	  window.location = url;
      	  return false;
        }
        var x=$('input[name=datosBusqueda]').val().replace(/\s/g,'+');
        if(x=="Busca+por+puesto,+ubicación,+empresa,+etc") x="";
        var area = $('select[name=areas]').val();
        var nivelpuestos = $('select[name=nivelPuestos]').val();
        var ubicaciones = $('select[name=ubicaciones]').val();
        var cadena = this.action+'/query/'+x;
        if(area!='none'){cadena+='/areas/'+area;}
        if(nivelpuestos!='none'){cadena+='/nivel/'+nivelpuestos;}
        if(ubicaciones!='none'){cadena+='/ubicacion/'+ubicaciones;}
        window.location=cadena;
        return false;
      });
    };
};
	// init
	var aptitus = new Aptitus();
	aptitus.winModal();
	aptitus.placeholder();
	aptitus.placeholderRel();
	aptitus.areaWM();
	aptitus.searchA();
	aptitus.login();
	aptitus.forgotPass();
	aptitus.flashMsg();
	aptitus.tooltipApt();
	aptitus.areaOverflow('#moreHomeAH');
	aptitus.areaOverflow('#moreHomeUB');
	aptitus.registerFast();	
	aptitus.forgotPassReg();
  aptitus.formFind();
});