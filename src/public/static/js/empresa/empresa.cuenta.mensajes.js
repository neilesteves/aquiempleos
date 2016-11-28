/*
 Empresa Modal Cuenta
*/
$( function() {
	var msgs = {
		errorAjax : 'Se produjo un error.',
		errorRpta : 'Ingrese su respuesta.',
		txtLoad : 'Cargando ...',
		cEmail : {
			good : '.',
			bad : 'Debe ingresar su dirección email.',
			def : 'Ingrese email correcto',
			mailValid : 'Email ya registrado.'
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
		cTlfNum : {
		 	good : '.',
		 	bad : 'Incorrecto',
		 	def : 'Ingrese Número Celular'
		}						
	};
	var cuentaEMD = {
		winModalMsj : function(trigger){
			var a = $(trigger),
			m = $('#mask'),
			w = $('.wwScroll'),
			c = $('.closeWMM'),
			body = $('html, body'),
			dataApp = $('table#dataAadmins').size();
			a.live('click', function(e) {
				e.preventDefault();
				$('html, body').animate({
					scrollTop:0
				}, s);
				var t = $(this),
				i = t.attr('rel'),
				mH = $(document).height(),
				mW = $(window).width(),
				mHW = $(window).height(),
				s = 'fast',
				o = 0.50;
				body.css({
					'height':mHW
				}).addClass('bodyOverflow');
				m.fadeTo(s,o);
				// construye modal
				cuentaEMD._winModalAdm(t, i, s);								
				$(document).keyup( function(e) {
					if(e.keyCode === 27) {
						if(dataApp != 1) {		
						                  	m.hide();
						}
						_closeM();
					}
				});
			});
			c.click( function(e) {
				e.preventDefault();
				if(dataApp != 1) {
					m.hide();
				}
				_closeM();
			});
			m.click( function(e) {
				if(dataApp != 1) {				
				                  	$(this).hide();
				}
				_closeM();
			});
			function _closeM() {
				if(dataApp) {
					document.location.reload(true);
				}
				if(dataApp != 1) {		
				                  	w.hide();
				                  	body.removeClass('bodyOverflow').css({
				                  		'height':'auto'
				                  	});
				}
			}
		},
		_winModalAdm : function(t, i, s) {
			if(t.hasClass('editAdmEMP')){
				//Editar
				var dataRel = t.attr('rol'),
				modal = $(i),
				cntLoad = $('#loadHTMLAdm');
				cntLoad.empty();
				modal.fadeIn(s, function(){
					cntLoad.addClass('loading');				
					var urlAdm = '/empresa/administrador/editar/', 
					method = 'GET', 
					dataAdm = dataRel,
					formSer = '',
					tClass = 'editar',
                                        dataSent = {
                                            id : dataAdm,
                                            dataStr : formSer,
                                            tok : t.attr('data-token')
                                        };
                                        cuentaEMD._ajaxEvaluateAdm(urlAdm, method, dataSent, tClass);
					/* cuentaEMD._ajaxEvalAdm(urlAdm, method, dataAdm, formSer, tClass);*/
				});
			}else{
				//Nuevo
				var dataRel = t.attr('rol'),
				modal = $(i),
				cntLoad = $('#loadHTMLAdm');
				cntLoad.empty();
				modal.fadeIn(s, function(){
					cntLoad.addClass('loading');				
					var urlAdm = '/empresa/administrador/nuevo/',
					method = 'GET',
					//dataAdm = dataRel,
					dataAdm = '',
					formSer = '',
					tClass = 'nuevo';
					cuentaEMD._ajaxEvalAdm(urlAdm, method, dataAdm, formSer, tClass);
				});
			}
		},                                	 	   	                                                                 	
		saveEval : function(flagFrm){
			var	formEditD = $('#formEMC'),
			 	btnSaveEval = $('#saveEmpPID', formEditD);
			if($.trim(flagFrm) == '1'){
				btnSaveEval.attr('disabled',true);
				//No hay errores guarda la data 			
				$('.closeWMM').trigger('click');				
			}else{
			 	// Hay errores
			 	formEditD.on('submit', function(event){
			 		event.preventDefault();
			 	});
			 	formEditD.parsley().subscribe('parsley:form:validate', function(formInstance){
			 		if(formInstance.isValid()){
			 			
				 	   	var urlAdm = '/empresa/administrador/editar/' , 
				 	   	method = 'POST', 
				 	   	dataAdm = btnSaveEval.attr('rel'),
				 	   	cntLoad = $('#dataFormAddNADM');						
				 	   	cntLoad.empty();
				 	   	cntLoad.addClass('loading'),
				 	   	formSer = formEditD.serialize(),
				 	   	tClass = 'editar';

				 	   	cuentaEMD._ajaxEvalAdm(urlAdm, method, dataAdm, formSer, tClass);
			 		}
			 	})
			}	   	 			
		},
		newEval : function(flagFrm){
			var	formEditD = $('#formEMC'),
			 	btnSaveEval = $('#saveEmpPID', formEditD);

			if($.trim(flagFrm) == '1'){
				//No hay errores guarda la data 
				btnSaveEval.attr('disabled',true);		
				$('.closeWMM').trigger('click');				
			}else{
			 	// Hay errores
			 	formEditD.on('submit', function(event){
			 		event.preventDefault();
			 	});
			 	formEditD.parsley().subscribe('parsley:form:validate', function(formInstance){
			 		if(formInstance.isValid()){
				 	   	var urlAdm = '/empresa/administrador/nuevo/' , 
				 	   	method = 'POST', 
				 	   	dataAdm = btnSaveEval.attr('rel'),
				 	   	cntLoad = $('#dataFormAddNADM');
				 	   	cntLoad.empty();
				 	   	cntLoad.addClass('loading');

				 	   	var formSer = formEditD.serialize(),
				 	   	tClass = 'nuevo';
				 	   	cuentaEMD._ajaxEvalAdm(urlAdm, method, dataAdm, formSer, tClass);
			 		}
			 	})		
			}	   									
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
				t.bind('keyup',function(e){
					r = t.next();
					var valueInput = t.val();
                        newValue = valueInput.replace(/[#*()-]/g,'');
                        t.val(newValue);
				});
				t.bind('paste', function(){
					setTimeout(function() {
						var value = t.val();
						var newValue = value.replace(/[^0-9-#-*-(-)--]/g,'');
		t.val(newValue);
			}, 0);
				});	

			});
		},
		fMail : function(a,good,bad,def){
		  	$(a).live('blur', function() {
		  		var t = $(this),
		  		r = t.next(),
		  		ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
		  		if(ep.test(t.val())&& t.val()!='') {
		  		        	r.removeClass('bad def').addClass('good').text(good);
		  		        	t.attr('requiredinput','valid').removeClass('errorInput');
		  		} else {
		  		        	r.removeClass('good def').addClass('bad').text(bad);
		  		        	t.attr('requiredinput','invalid').addClass('errorInput');
		  		}
		  	});
		  	$(a).live('keypress', function() {
		  		var t = $(this),
		  		r = t.next();
		  		r.removeClass('good bad').addClass('def').text(def);
		  	});
		},
		fInput : function(a,good,bad,def, flag) {
			var A = $(a),
			r = A.parents('.block').find('.response');
			A.live('blur', function() {
				var t = $(this);
				if(t.val().length>0) {
					if(flag == true){
					 	r.removeClass('bad').addClass('good').text(good);
					 	t.attr('requiredinput','valid').removeClass('errorInput');
					}
				} else {
					if(flag == true){
						r.removeClass('good def').addClass('bad').text(bad);
						t.attr('requiredinput','invalid').addClass('errorInput');
					}
				}
			});
			A.live('keydown', function() {
				var t = $(this);
				if(t.val().length===0) {
					if(flag == true){
						r.removeClass('good def').addClass('bad').text(bad);
						t.attr('requiredinput','invalid').addClass('errorInput');
					}
				} else {
					if(flag == true){
					    r.removeClass('good bad').addClass('def').text(def);
					}
				}
			});
		},
		fNumberTelephone : function(a,good,bad,def, flag){
			jQuery.support.placeholder = false;
			test = document.createElement('input');
			if('placeholder' in test) {
				jQuery.support.placeholder = true;
			}
			if(jQuery.support.placeholder == true){
				$('#placeholderie').remove();
			}
			var A = $(a),
			r = A.parents('.block').find('.response');
			A.attr('placeholder','Entre 7-9 dígitos');
			A.live('blur', function() {
				var t = $(this),
				ep = /^[0-9]{7,9}$/g;
				if(ep.test(t.val())&&t.val().length>0) {
					if(flag == true){
					 	r.removeClass('bad').addClass('good').text(good);
					 	t.attr('requiredinput','valid').removeClass('errorInput');
					}
				} else {
					if(flag == true){
						r.removeClass('good def').addClass('bad').text(bad);
						t.attr('requiredinput','invalid').addClass('errorInput');
					}
				}
			});
			A.live('keydown', function() {
				var t = $(this);
				if(t.val().length===0) {
					if(flag == true){
						r.removeClass('good def').addClass('bad').text(bad);
						t.attr('requiredinput','invalid').addClass('errorInput');
					}
				} else {
					if(flag == true){
					    r.removeClass('good bad').addClass('def').text(def);
					}
				}
			});

		},
		initValid : function(inputRequired){
			var inputsRequiredLength = inputRequired.length;
			for(i=0;i<inputsRequiredLength;i++){
				if(inputRequired[i].val() == ""){
					inputRequired[i].attr('requiredinput','invalid');
				}else{
					inputRequired[i].attr('requiredinput','valid');
				}
			}

		},
		validRequiredForm : function(inputRequired){
			var inputsRequiredLength = inputRequired.length,
				r;
			for(i=0,j=0;i<inputsRequiredLength;i++){
				if(inputRequired[i].attr('requiredinput') == 'valid'){
					j++;
				}else{
					inputRequired[i].addClass('errorInput');
					r = inputRequired[i].parents('.block').find('.response');
					r.removeClass('good def').addClass('bad').text('Este campo es requerido');
				}
			}

			if(j==inputsRequiredLength){
				return true;
			}else{
				return false;
			}

		},
                
                _ajaxEvaluateAdm : function(urlAdm, method, dataSent, tClass){
			$.ajax({
			   	'url' : urlAdm,
			   	'type' : method,
			   	'dataType' : 'html',
			   	'data' : dataSent,
			   	'success' : function(res) {
			   		var cntEmp = $('#iNAdmEM');
			   		cntEmp.html(res);
			   		var flagFrm = $('#dataFormAddNADM').attr('rel');
			   		if(tClass == 'nuevo'){
			   			//nuevo
			   			cuentaEMD.newEval(flagFrm);
			   		}else{
			   			//editar
			   			cuentaEMD.saveEval(flagFrm);	
			   		}
			   		//cuentaEMD.initValid([$("#fEmail"),$("#fNombres"),$("#fApellidos"),$("#fTlfAmin1")]);
			   		//cuentaEMD.fOnlyNumTlf('#fTlfAmin1');
			   		//cuentaEMD.fOnlyNumTlf('#fTlfAmin2');
			   		//cuentaEMD.fOnlyNumTlf('#fAnexo1');
			   		//cuentaEMD.fOnlyNumTlf('#fAnexo2');
			   		//cuentaEMD.fMail('#fEmail', msgs.cEmail.good, msgs.cEmail.bad, msgs.cEmail.def);
			   		//cuentaEMD.fInput('#fNombres', msgs.cName.good, msgs.cName.bad, msgs.cName.def, true);
			   		//cuentaEMD.fInput('#fApellidos', msgs.cApell.good, msgs.cApell.bad, msgs.cApell.def, true);	
			   		//cuentaEMD.fNumberTelephone('#fTlfAmin1', msgs.cTlfNum.good, msgs.cTlfNum.bad, msgs.cTlfNum.def, true);
			   		//cuentaEMD.fNumberTelephone('#fTlfAmin2', msgs.cTlfNum.good, msgs.cTlfNum.bad, msgs.cTlfNum.def, true);						
			   	}
			});			
		},
                
		_ajaxEvalAdm : function(urlAdm, method, dataAdm, formSer, tClass){
			$.ajax({
			   	'url' : urlAdm,
			   	'type' : method,
			   	'dataType' : 'html',
			   	'data' : {
			   		id : dataAdm,
			   		dataStr : formSer
			   	},
			   	'success' : function(res) {
			   		var cntEmp = $('#iNAdmEM');
			   		cntEmp.html(res);
			   		var flagFrm = $('#dataFormAddNADM').attr('rel');
			   		if(tClass == 'nuevo'){
			   			//nuevo
			   			cuentaEMD.newEval(flagFrm);
			   		}else{
			   			//editar
			   			cuentaEMD.saveEval(flagFrm);	
			   		}
			   		//cuentaEMD.initValid([$("#fEmail"),$("#fNombres"),$("#fApellidos"),$("#fTlfAmin1")]);
			   		//cuentaEMD.fOnlyNumTlf('#fTlfAmin1');
			   		//cuentaEMD.fOnlyNumTlf('#fTlfAmin2');
			   		//cuentaEMD.fOnlyNumTlf('#fAnexo1');
			   		//cuentaEMD.fOnlyNumTlf('#fAnexo2');
			   		//cuentaEMD.fMail('#fEmail', msgs.cEmail.good, msgs.cEmail.bad, msgs.cEmail.def);
			   		//cuentaEMD.fInput('#fNombres', msgs.cName.good, msgs.cName.bad, msgs.cName.def, true);
			   		//cuentaEMD.fInput('#fApellidos', msgs.cApell.good, msgs.cApell.bad, msgs.cApell.def, true);	
			   		//cuentaEMD.fNumberTelephone('#fTlfAmin1', msgs.cTlfNum.good, msgs.cTlfNum.bad, msgs.cTlfNum.def, true);
			   		//cuentaEMD.fNumberTelephone('#fTlfAmin2', msgs.cTlfNum.good, msgs.cTlfNum.bad, msgs.cTlfNum.def, true);						
			   	}
			});			
		}
	};

	cuentaEMD.winModalMsj('.winModalData');
	
});

	$(function(){
	    var misprocesos = {
		cerrarAdministrador : function(a){
	        $(a).live("click",function(){
	            var x = $(this);
	            var modal = x.attr("href");
	            $(modal+" a[href=#aceptar]").bind("click",function(){
	                //window.location = x.attr("rel");
                        $(this).parent().attr('action', x.attr("rel"));
                        $(this).parent().submit();
	            });
	        });
	    }    
	};
	// init

	misprocesos.cerrarAdministrador(".winModal");
});