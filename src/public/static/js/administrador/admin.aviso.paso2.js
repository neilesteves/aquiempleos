/*
 Registro Empresa Aviso Paso 2
 */
$( function() {
	var msgs = {
		cFormStep2 : 'Ingresa tus datos obligatorios para poder continuar.',
		cForm : 'Ingresa tus datos correctamente para poder añadir',
		cBadSubmit : 'Campo requerido.',
		cBadTerminos : 'Acepta las Politicas de Privacidad de APTiTUS.com.',
		cBadIdioma : 'No puedes repetir un idioma',
		cBadPrograma : 'No puedes repetir un programa',		
		cDef : {
			good : '¡Correcto!',
			bad : 'No parece ser un campo válido.',
			def : 'Ingresa datos correctos'
		},
		mDelete : {
			success : '¡Se eliminó satisfactoriamente!',
			error : '¡Se produjo un error al eliminar.!',
			prog : 'Eliminando...',
			def : 'Está seguro que desea eliminar?',
			est : '¿Estás seguro que deseas eliminar el estudio seleccionado?',
			idi : '¿Estás seguro que deseas eliminar el idioma seleccionado?',
			pro : '¿Estás seguro que deseas eliminar el programa de computadora seleccionado?',
			exp : '¿Estás seguro que deseas eliminar la experiencia seleccionada?',
			pre : '¿Estás seguro que deseas eliminar la pregunta?',			
			expe : 'Está seguro que desea eliminar?, también se eliminaran tus referencias relacionadas.'
		},
		mPregunta : {
			good : '¡Correcto!',
			bad : 'No es una pregunta válida.',
			def : 'Ingrese una preguna correcta'
		}		
	};
	var vars = {
		rs : '.response',
		okR :'ready',
		okBlock :'readyBlock',
		loading : '<div class="loading left"></div>'
	};
	var formP2 = {
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
		acord : function(a,b,c,d,e) {
			$(a).bind('click', function() {
				var $t = $(this),
				$tN = $t.next(),
				spedd = 'middle';
				if($tN.hasClass(b)) {
					$tN.slideUp(spedd, function() {
						$tN.hide().removeClass(b).addClass(c);
						$t.addClass(e).removeClass(d);
					});
				}
				if($tN.hasClass(c)) {
					$tN.slideDown(spedd, function() {
						$tN.show().removeClass(c).addClass(b);
						$t.addClass(d).removeClass(e);
					});
				}
			});
		},
		addSkills : function(opts) {
			var btn = $(opts.idBtn),
			build = $(opts.idBuild),
			inval = $(opts.idInval),
			resp = $(opts.idResp),
			//Valor por defecto '0', coge el parametro inicial : N# de forms
			j = parseInt($(opts.idSection).attr('rel')),
			speedd = 'fast',
			speedd2 = 'medium',
			manager = opts.idManager,
			fieldBlock1 = opts.idFieldBlock1,
			fieldBlock2 = opts.idFieldBlock2 ;
			//click
			btn.bind('click', function(e) {
				e.preventDefault();
				var ifRepeat = false;

				if(opts.idSection == '#languagesF' || opts.idSection == '#programsF'){
					var flagRepeat = formP2.reviewRepeat(opts.idSection);
					if(flagRepeat == true){
						ifRepeat = true;
					}					
					var msgRepData;
					if(opts.idSection == '#languagesF'){
						msgRepdata = msgs.cBadIdioma;
					}
					if(opts.idSection == '#programsF'){
						msgRepdata = msgs.cBadPrograma;
					}																		
				}
				
				if(ifRepeat == true){
					// si hay repetidos
					resp.text(msgRepdata);
					
				}else{
					// sige el flujo normal				
								
				posExpe = $(opts.idSection).offset().top;
				if(btn.hasClass('active')) {
					resp.text('');
					// Si no es Cuenta Perfil Postulante
					if( !($('body').is('#myAccount')) ) {
						//oculta deshabilita campos : Experiencia, Estudios
						if( opts.idSection == '#experienceF' || opts.idSection == '#studyF' ) {
							inval.fadeOut(speedd).html('');
						}
					}
					// + 1
					j++;
					var html = $(opts.idHtml).html();
					html = html.replace(opts.idExpRegMa, opts.idManager + j );
					html = html.replace(opts.idExpRegF, opts.idPref + j +'_');
					build.append(html);

					var nameLabel1, nameLabel2, separate, textDelete;
					//if( opts.idSection == '#experienceF' || opts.idSection == '#referenceF' ) {
					if( opts.idSection == '#preguntasF' ) {	
						// Si viene de Experiencia usa los input
						nameLabel1 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock1).val(),
						//nameLabel2 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock2).val();
						nameLabel2 = '';
					}else if( opts.idSection == '#studyF' ){
						if($(opts.idSection).hasClass('offSelect')){
						// disabled
						nameLabel1 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock1 + ' option:selected' ).text(),
						nameLabel2 = '';
						$('#managerEstudio_' + j + '_id_carrera').removeAttr('disabled').val(-1);
						}else{
						//no disabled	
					 	nameLabel1 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock2 + ' option:selected' ).text(),
						nameLabel2 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock1 + ' option:selected' ).text();
						}
					} else if( opts.idSection == '#experienceF' ){
						nameLabel1 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock2 + ' option:selected' ).text(), 
						nameLabel2 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock1 + ' option:selected' ).text();	
					} 
					else {						
						// Si viene de otra sección usa los select
						nameLabel1 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock1 + ' option:selected' ).text(),
						nameLabel2 = $('#'+ opts.idManager + (j-1) + opts.idFieldBlock2 + ' option:selected' ).text();
					}
					if( opts.idSection == '#experienceF') {
						separate = 'en';
					}else	if( opts.idSection == '#studyF' ) {
						if($(opts.idSection).hasClass('offSelect')){
						// disabled
						separate = '';
						}else{
						//no disabled	
						separate = 'en';
						}
					}else if( opts.idSection == '#preguntasF' ){
						separate = '';
					} else {
						separate = 'nivel';
					}
					
					if(opts.idSection == '#experienceF' ){
						textDelete = msgs.mDelete.exp;
					}else if(opts.idSection == '#programsF' ){
						textDelete = msgs.mDelete.pro;
					}else if(opts.idSection == '#languagesF' ){
						textDelete = msgs.mDelete.idi;
					}else if(opts.idSection == '#studyF' ){
						textDelete = msgs.mDelete.est;
					}else if(opts.idSection == '#preguntasF' ){
						textDelete = msgs.mDelete.pre;
					}														

					$('#' + opts.idPref + ( j-1 ) +'_expTDiv').fadeIn(speedd2).html('' +
					'<blockquote class="addReg">' +
					'<span class="wCount left">' +
					'<span class="countN">' + j + '</span>.' +
					'</span>' +
					'<span class="wJob left">' +
					'<span class="jobReg bold">' + nameLabel2 + '</span> ' + separate + ' ' +
					'<span class="eJob bold">' + nameLabel1 +'</span>' +
					'</span>'+
					'<a href="#Editar" class="editB" title="Editar"><span class="sEditB f11">Editar</span></a>' +
					'<a ajax="Ajax" rel="#' + opts.idPref + (j-1) + '_expeN" rol="#winAlert" href="#winAlert" class="deleteB winAlertM" ' +
					'title="' + textDelete + '"><span class="sDeleteB f11">Eliminar</span></a>' +
					'</blockquote>');
					//$('#f' + (j-1) +'_fSkill').addClass('hide');
					$(opts.idDisabled + ' .fSkill').not('#' + opts.idPref + (j) +'_fSkill').addClass('hide');
					$('html, body').animate({
						scrollTop: posExpe - 50
					}, speedd);
					// reusando metodos
					formP2.inputReq('input.inputReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);
					formP2.inputReq('textarea.inputReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);
					formP2.selectReq('select.selectReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);
					
					formP2.fStudyReq('.selectEstR');
					formP2.winModalAlert();
					//editar
					formP2.editBloque();
					//tipo carrera
					$('.tipoCarreraN').die();
					formP2.tipoCarrera('.tipoCarreraN');
					//enumerar
					oBlockD = $('#' + opts.idPref + (j-1) + '_expeN');
					formP2.enumeracionBlocks(oBlockD);
					//enumera block
					formP2.enumeracionSkills(opts.idSection);					
				} else {
					resp.text(msgs.cForm);
				}
				//Calculando campos válidos, y el Límite por formulario y sección
				var sectionId = $.trim(btn.attr('rel'));
				formP2.okAddSkill(sectionId);
				// fin de Cálculo
				}
			});
		},
		reviewRepeat : function(sectionAdd){
			//idiomas	
			if(sectionAdd == '#languagesF' || sectionAdd == '#programsF'){
				var arrCantidad = $(sectionAdd + ' .skillN'),
				numCantidad = arrCantidad.size(),
				arrReq1 = $(sectionAdd + ' .field1Req'),
				booleanFlag, flagRepeat1;
				if(numCantidad > 1){
					var lastReq1 = arrReq1.eq(numCantidad-1).val();
					function loop1Repeat(arrReq1, lastReq1, numCantidad){
						for(var k = (numCantidad-2); k >= 0; k--){	
							var dataCheck = (arrReq1.eq(k).val() == lastReq1);
							if(dataCheck){
								booleanFlag = true;
								break;
							}else{
								booleanFlag = false;
								continue;
							}
						}
						return booleanFlag;
					}
					flagRepeat1 = loop1Repeat(arrReq1, lastReq1, numCantidad);
				}
			}
			return flagRepeat1;	
		},		
		okAddSkill : function(sectionId) {
			var limitAdd, sectionCnt, cntId, btnId, msgId ;
			switch(sectionId) {
				case 'sectionExp' :
					limitAdd = 2,
					sectionCnt = '#experienceF',
					cntId = '#contentFromExp',
					btnId = '#btnExp',
					msgId = '#msgErrorF';
					break;
				case 'sectionEst' :
					limitAdd = 3,
					sectionCnt = '#studyF',
					cntId = '#contentFromEst',
					btnId = '#btnEst',
					msgId = '#msgErrorStudy';
					break;
				case 'sectionIdi' :
					limitAdd = 2,
					sectionCnt = '#languagesF',
					cntId = '#contentFromLang',
					btnId = '#btnIdi',
					msgId = '#msgErrorLang';
					break;
				case 'sectionPro' :
					limitAdd = 2,
					sectionCnt = '#programsF',
					cntId = '#contentFromPro',
					btnId = '#btnPro',
					msgId = '#msgErrorProg';
					break;
				case 'sectionPre' :
					limitAdd = 1,
					sectionCnt = '#preguntasF',
					cntId = '#contentFromPre',
					btnId = '#btnPregEmp',
					msgId = '#msgErrorPreg';
					break;	
				//default : limitAdd == 2;
			}						
			var nBlocks = ($(sectionCnt +' .skillN').size()) ,
			total = limitAdd,
			limitAddUnit = $(cntId +' .ready').size(),
			btnAdd = $(btnId),
			clearMsgError = $(msgId);
			if( limitAddUnit >= limitAdd*nBlocks ) {
				btnAdd.removeClass('inactive').addClass('active');
				clearMsgError.text('');
			} else {
				btnAdd.addClass('inactive').removeClass('active');
			}
		},
		enumeracionSkills : function(oBlockD){
			if($.trim($('body').attr('id')) == 'perfilReg'){
				// enumeracion block
				var oBlockS = $(oBlockD), 
				titleBlockS = oBlockS.prev().find('.nSkillA'),
				nBlockSkill = oBlockS.find('.skillN'),
				nBlockSize = nBlockSkill.size() - 1;
				if(nBlockSize == 0){
					$(titleBlockS).text('');
				}else{			
					$(titleBlockS).text('(' + nBlockSize + ')');
				}
				// fin enumeracion block
			}
		},		
		inputReq : function(a,good,bad,def) {
			var A = $(a);
			A.blur( function(e) {
				var t=$(this),
				r = t.parents('.block').find(vars.rs),
				value = $.trim(t.val());
				if(value != '') {
					if(t.hasClass('fMinChar')){
						if( value.length <= 1 ){
							t.removeClass(vars.okR).next(vars.rs).addClass('bad').removeClass('good def').text(bad);
						}else{
							t.addClass(vars.okR);
							r.addClass('good').removeClass('bad def').text(good);
						}
					}else{
						t.addClass(vars.okR);
						if(t.hasClass('qAreaTxt')){
							good = msgs.mPregunta.good;
						}else{
							good = msgs.cDef.good;
						}
						r.addClass('good').removeClass('bad def').text(good);
					}
				} else {
					if(t.hasClass('qAreaTxt')){
						bad = msgs.mPregunta.bad;
					}else{
						bad = msgs.cDef.bad;
					}
					t.removeClass(vars.okR).next(vars.rs).addClass('bad').removeClass('good def').text(bad);
				}
				//Calculando campos válidos, y el Límite por formulario y sección
				var sectionId = $.trim(t.parents('.block').attr('rel'));
				formP2.okAddSkill(sectionId);
				// fin de Cálculo				
			}).keypress( function() {
				var t = $(this),
				r=t.parents('.block').find(vars.rs);
				if(t.val().length===0) {
					t.removeClass(vars.okR);
					if(t.hasClass('qAreaTxt')){
						bad = msgs.mPregunta.bad;
					}else{
						bad = msgs.cDef.bad;
					}
				} else {
					t.addClass(vars.okR);
					if(t.hasClass('qAreaTxt')){
						def = msgs.mPregunta.def;
					}else{
						def = msgs.cDef.def;
					}
					r.removeClass('bad good').addClass('def').text(def);
				}
				//Calculando campos válidos, y el Límite por formulario y sección
				var sectionId = $.trim(t.parents('.block').attr('rel'));
				formP2.okAddSkill(sectionId);
				// fin de Cálculo
			});
		},
		selectReq : function(a,good,bad,def) {
			var trigger = $(a);
			trigger.bind('change', function() {
				var t=$(this),
				r = t.next(vars.rs);
				if(t.val()==='-1' || t.val()==='none') {
					t.removeClass(vars.okR);
					r.removeClass('good bad').addClass('def').text(def);
				} else {
					r.addClass('good').removeClass('bad def').text(good);
					t.addClass(vars.okR);
				}
				//Calculando campos válidos, y el Límite por formulario y sección
				var sectionId = $.trim(t.parents('.block').attr('rel'));
				formP2.okAddSkill(sectionId);
				// fin de Cálculo
			});
		},
		openDescPreg : function(a,b) {
			var A = $(a),
			speedd = 'fast';
			A.bind('click', function(e) {
				e.preventDefault();
				var t=$(this);
				t.addClass('hide');
				$('#allSendPreg').addClass('hide');
				$(b).slideDown(speedd);
			});
		},
		editBloque : function() {
			var trigger = $('.editB'),
			speedd = 'fast';
			trigger.click( function(e) {
				e.preventDefault();
				var t = $(this),
				tNext = t.parents('.expTDiv').next('.fSkill') ;
				if(t.hasClass('upF')) {
					tNext.slideUp(speedd, function() {
						tNext.removeClass('fBlockNew');
						t.addClass('hide').removeClass('upF');
					});
				} else {
					tNext.slideDown(speedd, function() {
						tNext.addClass('fBlockNew');
						t.removeClass('hide').addClass('upF');
					});
				}
			});
		},
		deleteBloque : function() {
			var trigger = $('.yesCM'),
			speedd = 'slow',
			speedd2 = 'fast';
			trigger.click( function(e) {
				e.preventDefault();
				var t = $(this),
				blockDelete = t.parents('.window').attr('rel'),
				oBlockD = $(blockDelete),
				pos = oBlockD.offset().top,
				arrIdPrefSection = blockDelete.split('',2),
				// Prefijo de sección
				idPrefijoS = arrIdPrefSection[1];
				$('.closeWM').trigger('click');
				if( $('body').is('#myAccount') ) {
					// Cuenta
				} else {
					// Paso 2
					$('html, body').animate({
						scrollTop: pos - 100
					}, speedd2);
				}
				//Enumeracion
				formP2.enumeracionBlocks(oBlockD);
				if( ($('body').is('#EditarAviso')) ) {
					var expId = t.parents('.window').attr('rol');
					var urlAjax = oBlockD.parent().attr('rol');
					msgTime = $('#msgsTime .msgYellow');
					msgTime.text(msgs.mDelete.prog).parent().fadeIn(speedd);
					if($.trim(expId) == '-1'){				
						// Remueve el bloque a eliminar si es cliente
						oBlockD.fadeOut(speedd, function() {
							oBlockD.detach();
							msgTime.text(msgs.mDelete.success);
							setTimeout( function() {
								msgTime.parent().fadeOut(speedd);
							},1000);					
						});											
					}else{
						$.ajax({
							'url' : urlAjax,
							'type' : 'POST',
							'dataType' : 'JSON',
							'data' : {
								'id' : expId
							},
							'success' : function(res) {
								oBlockD.fadeOut(speedd, function() {
								if(res.status == 'ok'){
									oBlockD.detach();
									msgTime.text(msgs.mDelete.success);
									setTimeout( function() {
										msgTime.parent().fadeOut(speedd);
									},1000);
								}else{
									msgTime.text(msgs.mDelete.error);
								}	
								});
							},
							'error' : function(res) {
								msgTime.text(msgs.mDelete.error);
							}
						});
					}
				} else {
					// Remueve el bloque a eliminar
					oBlockD.fadeOut(speedd, function() {

						var blockSect = '#' + oBlockD.parents('div.feildset').attr('id');
												
						oBlockD.detach();
						// Si no es Cuenta Perfil Postulante
						//Revisa si el ultimo eliminado
						//solo para Experiencia e Idiomas
						if( idPrefijoS == 'f' || idPrefijoS == 'e' ) {
							formP2.reviewLastD(idPrefijoS);
						}

						//enumeracion block
						formP2.enumeracionSkills(blockSect);
						//fin enumeracion block	
						
					});
				}
			});
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
		winModalAlert : function() {
			var a = $('.winAlertM'),
			m = $('#mask'),
			w = $('.window'),
			c = $('.closeWM');
			a.live('click', function(e) {
				e.preventDefault();
				var t = $(this),
				i = t.attr('rol'),
				dataAjax = t.parents('.skillN').attr('rol'),
				mH = $(document).height(),
				mW = $(window).width(),
				s = 'fast',
				o = 0.50,
				msjDelete = t.attr('title');
				$(i).attr({
					'rel': t.attr('rel'),
					'rol':dataAjax
				});
				m.css({
					'height':mH
				});
				m.fadeTo(s,o);
				$(i).fadeIn(s).find('p#titleQ').text(msjDelete);
				// detect IE6 Version
				if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
					var posSectionIE6 = $(t.attr('rel')).offset().top;
					$(i).css({
						'top':posSectionIE6 + 50
					});
					$('#wrap select').css('visibility','hidden');
				}
				$(document).keyup( function(e) {
					if(e.keyCode === 27) {
						m.hide();
						w.hide();
						if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
							$('#wrap select').css('visibility','visible');
						}
					}
				});
			});
			c.click( function(e) {
				e.preventDefault();
				m.hide();
				w.hide();
				if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
					$('#wrap select').css('visibility','visible');
				}
			});
			m.click( function(e) {
				$(this).hide();
				w.hide();
				if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
					$('#wrap select').css('visibility','visible');
				}
			});
		},		
		fStudyReq : function(sele){
			var select1 = $(sele),
			selectData = select1.attr('rel');
			arrJson = eval('(' + selectData + ')');		
			select1.change(function(){
				var t = $(this),
				//select2 = t.parent().siblings('.selectCar').find('.selectEstH'),
				select2 = t.parent().next().find('.selectEstH'),
				select3 = t.parent().next().next().find('.selectEstH'),
				val = parseInt(t.val());
				
				var detalle = false;
				for (i = 0; i < arrJson.disableds.length; i++) {
					if (val == arrJson.disableds[i]) {
						detalle = true;
					}
				}
				if(detalle){
					select2.val(-1);
					select2.addClass('ready').parents('#studyF').addClass('offSelect');
					select2.attr('disabled','disabled');
					select3.val(-1);
					select3.addClass('ready').attr('disabled','disabled');
				}else if(t.val() == '-1'){
					select2.removeAttr('disabled');
					select3.removeAttr('disabled');
				}else{					
					if(select2.val() == '-1'){
						select2.removeClass('ready').parents('#studyF').removeClass('offSelect');
						select2.removeAttr('disabled');						
					}
					if(select3.val() == '-1'){
						select3.removeClass('ready').removeAttr('disabled');
					}
				}	
				//Calculando campos válidos, y el Límite por formulario y sección
				var sectionId = $.trim(select1.parents('.block').attr('rel'));
				formP2.okAddSkill(sectionId);
				// fin de Cálculo
			});
		},
		inputNameEmp : function(ipt){
			var iptA = $(ipt),
			iptText = $('#otro_nombre_empresa'),
			spedd = 'fast';
			if(iptA.prop('checked')){
				iptText.addClass('hide');
			}
			iptA.bind('change', function(e){
				var t = $(this);
				if(t.prop('checked')){
					iptText.fadeOut(spedd);
				}else{
					iptText.fadeIn(spedd).focus();
				}
			});
		},
		tipoCarrera : function(selec){
			var select = $(selec);
			select.live('change', function(){
				var t = $(this),
				valor = $.trim(t.val()),
				selectCarrera = t.parent().next().find('select.selectN');	
				selectCarrera.attr('disabled', 'disabled');
				var sectionId = '';			
				if(valor != '-1'){
                                    
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
						'url' : '/registro/filtrar-carrera/',
						'type' : 'POST',
						'dataType' : 'JSON',
						'data' : {
							'id_tipo_carrera' : valor,
                                                                                                                csrfhash:csrfHash
						},
						'success' : function(res){
							selectCarrera.children('option').not('option[value="-1"]').remove();
							$.each(res, function(i,v){
								selectCarrera.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
							});							
							selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
							//Calculando campos válidos, y el Límite por formulario y sección
							sectionId = $.trim(t.parent().attr('rel'));
							formP2.okAddSkill(sectionId);
							// fin de Cálculo		
						},
						'error' : function(res){
							//limpio options menos -1
							selectCarrera.children('option').not('option[value="-1"]').remove();
							selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
							//Calculando campos válidos, y el Límite por formulario y sección
							sectionId = $.trim(t.parent().attr('rel'));
							formP2.okAddSkill(sectionId);
							// fin de Cálculo		
						}						
					});
                                                                            
                                                                        }
                                                                });
                                    
				}else{
					//-1 limpio options
					selectCarrera.children('option').not('option[value="-1"]').remove();
					selectCarrera.removeAttr('disabled').removeClass('ready bad good').next().text('');
					//Calculando campos válidos, y el Límite por formulario y sección
					sectionId = $.trim(t.parent().attr('rel'));
					formP2.okAddSkill(sectionId);
					// fin de Cálculo
				}
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
                                csrfhash:csrfHash
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
                },
                iptOneClick : function(iptClick){
                	var iptSbmt = $(iptClick),
                	frm = $('#fStep2');
                	iptSbmt.on('click', function(e){
                		e.preventDefault();
                		var t = $(this);
                		if(!t.prop('disabled')){
                			frm.submit();
                			t.attr('disabled', 'disabled');
                		}else{
                			return false;
                		}
                	});
                },
                linkOneClick : function(linkClick){
                	var aClick = $(linkClick);
                	aClick.on('click', function(e){
                		e.preventDefault();
                		var t = $(this),
                		urlHref = t.attr('href');
                		if(!t.hasClass('disabledLnk')){
                			t.addClass('disabledLnk');
                			location.href = urlHref;
                		}else{
                			return false;
                		}
                	});
                }
	};
	// init
	//paso 2
	formP2.tipoCarrera('.tipoCarreraN');
	formP2.acord('.titleDiv','open','close','onAcor','offAcor');
	formP2.inputNameEmp('#mostrar_empresa');
	formP2.addSkills({
		idSection : '#experienceF',
		idBtn : '#btnExp',
		idBuild : '#contentFromExp',
		idInval : '#checkInv',
		idResp : '#msgErrorF',
		idManager : 'managerExperiencia_',
		idFieldBlock1 : '_id_nivel_puesto', 
		idFieldBlock2 : '_id_area',
		idHtml : '#expTemplate',
		idPref : 'f',
		idExpRegMa : /managerExperiencia_blank/ig,
		idExpRegF : /fblank_/ig,
		idDisabled : '#expeDisabled',
		idLimit : 2
	});
	formP2.addSkills({
		idSection : '#studyF',
		idBtn : '#btnEst',
		idBuild : '#contentFromEst',
		idInval : '#checkInv2',
		idResp : '#msgErrorStudy',
		idManager : 'managerEstudio_',
		idFieldBlock1 : '_id_nivel_estudio',
		idFieldBlock2 : '_id_carrera',
		idHtml : '#estTemplate',
		idPref : 'e',
		idExpRegMa : /managerEstudio_blank/ig,
		idExpRegF : /eblank_/ig,
		idDisabled : '#estudyDisabled',
		idLimit : 3
	});
	formP2.addSkills({
		idSection : '#languagesF',
		idBtn : '#btnIdi',
		idBuild : '#contentFromLang',
		idInval : '#checkInv3',
		idResp : '#msgErrorLang',
		idManager : 'managerIdioma_',
		idFieldBlock1 : '_nivel_idioma',
		idFieldBlock2 : '_id_idioma',
		idHtml : '#idiTemplate',
		idPref : 'i',
		idExpRegMa : /managerIdioma_blank/ig,
		idExpRegF : /iblank_/ig,
		idDisabled : '#langDisabled',
		idLimit : 2
	});
	formP2.addSkills({
		idSection : '#programsF',
		idBtn : '#btnPro',
		idBuild : '#contentFromPro',
		idInval : '#checkInv4',
		idResp : '#msgErrorProg',
		idManager : 'managerPrograma_',
		idFieldBlock1 : '_nivel',
		idFieldBlock2 : '_id_programa_computo',
		idHtml : '#proTemplate',
		idPref : 'p',
		idExpRegMa : /managerPrograma_blank/ig,
		idExpRegF : /pblank_/ig,
		idDisabled : '#progDisabled',
		idLimit : 2
	});
	formP2.addSkills({
		idSection : '#preguntasF',
		idBtn : '#btnPregEmp',
		idBuild : '#contentFromPre',
		idInval : '#checkInv5',
		idResp : '#msgErrorPreg',
		idManager : 'managerPregunta_',
		idFieldBlock1 : '_pregunta',
		idFieldBlock2 : '',
		idHtml : '#preTemplate',
		idPref : 'h',
		idExpRegMa : /managerPregunta_blank/ig,
		idExpRegF : /hblank_/ig,
		idDisabled : '#pregDisabled',
		idLimit : 1
	});	

	formP2.inputReq('input.inputReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);
	formP2.inputReq('textarea.inputReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);
	
	formP2.fUbi('#fPais','#fDepart','#fDistri', '#fProvin');
	
	formP2.selectReq('select.selectReq',msgs.cDef.good,msgs.cDef.bad,msgs.cDef.def);

	formP2.winModalAlert();
	formP2.deleteBloque();
	formP2.editBloque();

	formP2.openDescPreg('#linkSendPreg','#cntSendPreg');

	formP2.fStudyReq('.selectEstR');
    formP2.fNoArroba('#funciones');
    formP2.fNoArroba('#responsabilidades');

    formP2.numberWords('#nombre_puesto', { type : 'onlyWords' });
    formP2.numberWords('#otro_nombre_empresa', { type : 'onlyWords' });
    formP2.iptOneClick('#btnSubmitFFP');
    formP2.linkOneClick('#btnDuplicarP');

});