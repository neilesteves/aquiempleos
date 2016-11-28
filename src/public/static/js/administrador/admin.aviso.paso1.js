/*
 Empresa Modal Cuenta
 */
$( function() {
	var msgs = {
		paso1Plan : 'Debe selecciona un plan para continuar.'
	}
	var avisoEmp = {
		clickBlockF : function(block1, block2, block3, block4) {
			//click bloque 1
			var blockA = $('.topTitP2, .cntBodyP2', block1),
			btnClick = $('.iBtn1S2M', block1);
			blockA.bind('click', function(e) {
				e.preventDefault();
				btnClick.trigger('click');
				e.stopPropagation();
			});
			//click bloque 2
			var blockB = $('.topTitP2, .cntBodyP2', block2),
			btnClickB = $('.iBtn2S2M', block2);
			blockB.bind('click', function(e) {
				e.preventDefault();
				btnClickB.trigger('click');
				e.stopPropagation();
			});
			//click bloque 3
			var blockC = $('.topTitP2, .cntBodyP2', block3),
			btnClickC = $('.iBtn3S2M', block3);
			blockC.bind('click', function(e) {
				e.preventDefault();
				btnClickC.trigger('click');
				e.stopPropagation();
			});
			//click bloque 4
			var blockD = $('.topTitP2, .cntBodyP2', block4),
			btnClickD = $('.iBtn4S2M', block4);
			blockD.bind('click', function(e) {
				e.preventDefault();
				btnClickD.trigger('click');
				e.stopPropagation();
			});
		},
		paso1Emp : function() {
			var sections = $('#sectionsEM, #innerSectionEmp'),
			section1 = $('#section1EM'),
			section2 = $('#section2EM'),
			section3 = $('#section3EM'),
			spped = 'middle',
			tStep1 = $('#continP1'),
			tStep2 = $('.continP2'),
			title = $('#countWordEM'),
			titlePos = title.offset().top,
			backSection2 = $('#backEmpP1'),
			primOptP1 = $('#primOptP1');

			if(sections.hasClass('openSection2')) {
				_funP2(titlePos, spped, section1, sections, section2)
			}

			if(sections.hasClass('openSection3')) {
				$('html, body').animate({
					scrollTop:titlePos
				}, spped);
				section1.animate({
					'top':'-480px'
				},spped);
				section2.animate({
					'top':'-600px'
				},spped, function() {
					sections.animate({
						'height':'300px'
					},spped);
				});
				section3.animate({
					'top':'0'
				},spped);
			}

			tStep1.click( function(e) {
				e.preventDefault();
				_funP2(titlePos, spped, section1, sections, section2)
			});
			function _funP2(titlePos, spped, section1, sections, section2) {
				
				title.html('Elija su tipo de aviso :');
				
				$('html, body').animate({
					scrollTop:titlePos
				}, spped);
				section1.animate({
					'top':'-480px'
				},spped, function() {
					sections.animate({
						'height':'600px'
					},spped);
				});
				section2.animate({
					'top':'0'
				},spped);
			}

			tStep2.click( function(e) {
				e.preventDefault();
				$('html, body').animate({
					scrollTop:titlePos
				}, spped);
				var t = $(this),
				dataS = t.attr('rel'),
				objJson = eval('(' + dataS + ')');
				title.html('Elija su estrategia para el medio impreso : <span class="bold">Aviso ' + objJson.words + '</span>');
				section2.animate({
					'top':'-600px'
				},spped, function() {
					sections.animate({
						'height':'300px'
					},spped);
				});
				section3.animate({
					'top':'0'
				},spped);
				$('#optP1Emp1').attr('rel', objJson.id1);
				$('#optP1Emp2').attr('rel', objJson.id2);
				$('#optP1Emp3').attr('rel', objJson.id3);
				$('#priceRelp1').text(objJson.precio1);
				$('#priceRelp2').text(objJson.precio2);
				$('#priceRelp3').text(objJson.precio3);

			});
			backSection2.click( function(e) {
				e.preventDefault();
				$('html, body').animate({
					scrollTop:titlePos
				}, spped);
				title.html('Elija su tipo de aviso : ');
				section2.animate({
					'top':'0'
				},spped, function() {
					sections.animate({
						'height':'600px'
					},spped);
				});
				section3.animate({
					'top':'1030px'
				},spped);
			});
			primOptP1.click( function(e) {
				e.preventDefault();
				var t = $(this),
				dataM = t.attr('rel'),
				objMJson = eval('(' + dataM + ')');
				if(t.hasClass('loginPrimP1')) {
					t.addClass('winModal');
					$('#hideLoginReg').remove();
					$('#hideRegisterRReg').remove();
					$('#fRegisterWMH').append('<input id="hideLoginReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
					$('#formResgiRap').append('<input id="hideRegisterRReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
				} else {
					var urlP2 = t.attr('href');
					window.location = urlP2;
				}
			});
			var nameRadio = $('input[name="optP1Emp"]'),
			nextId = $('#nextEmpP1'),
			form = $('#formEmpP1'),
			errorP1 = $('#msgErrorEmpP1'),
			sendP1B3 = $('#idProductoLg');
			nameRadio.change( function() {
				var t = $(this);
				nextId.attr('rel',t.attr('rel'));
				if(nextId.hasClass('loginEmp')) {
					nextId.addClass('winModal');
					$('#hideLoginReg').remove();
					$('#hideRegisterRReg').remove();
					$('#fRegisterWMH').append('<input id="hideLoginReg" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
					$('#formResgiRap').append('<input id="hideRegisterRReg" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
				}

				if(sendP1B3.size() == 1) {
					$('#hideLoginRegS3').remove();
					form.append('<input id="hideLoginRegS3" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
				}
			});
			nextId.click( function(e) {
				e.preventDefault();
				var t = $(this);
				if(t.attr('rel')) {
					errorP1.text('');
					// Si no esta loegado no hace submit
					if(!(t.hasClass('loginEmp'))) {
						form.submit();
					}
				} else {
					errorP1.text(msgs.paso1Plan);
				}
			});
		},
		showEjm : function(linkEjm){
			var linkEjmA = $(linkEjm);
			linkEjmA.click(function(){
				var t = $(this),
				cnt = $('#ejmsAviso .cntImgAvisoEPA');
				cnt.addClass('loading').html('<img src="' + urls.mediaUrl  + '/images/empresa/avisos/' + $.trim(t.attr('rel')) + '" alt="Aviso" />')
			});
		}
	};
	// init
	avisoEmp.paso1Emp();
	avisoEmp.clickBlockF('#iBlock1S2M', '#iBlock2S2M', '#iBlock3S2M', '#iBlock4S2M');
	avisoEmp.showEjm('.viewShowEjm')
});