/*
 Empresa Aviso Index
 */
$( function() {
	var avisoIndex = {
		addTarifa : function(clickBtn) {
			$(clickBtn).click( function(e) {
				e.preventDefault();
				var t = $(this),
				dataM = t.attr('rel'),
				objMJson = eval('(' + dataM + ')');
				if(t.hasClass('loginPrimP1')) {
					//Remuevo del DOM
					$('#hideLoginReg').remove();
					$('#hideRegisterRReg').remove();
					//Vulevo a ubicarlos en el DOM
					$('#fRegisterWMH').append('<input id="hideLoginReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
					$('#formResgiRap').append('<input id="hideRegisterRReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
				}
			});			
    	},
    	clickBlock : function(block1, block2, block3){
    		//block 1
    		var blockN = $(block1),
    		activeBlock = $('h2, .sectionHW', block1),
    		btnBlock = $('.continNAdv1', block1);
    		activeBlock.click(function(e){
    			e.preventDefault();
    			btnBlock.trigger('click');
    			e.stopPropagation();
    		});
    		//block 2
    		var blockN2 = $(block2),
    		activeBlock2 = $('h2, .sectionHW', block2),
    		btnBlock2 = $('.continNAdv1', block2);
    		activeBlock2.click(function(e){
    			e.preventDefault();
    			location.href = btnBlock2.attr('href');
    		});
    		//block 3
    		var blockN3 = $(block3),
    		activeBlock3 = $('h2, .sectionHW', block3),
    		btnBlock3 = $('.continNAdv1', block3);
    		//urls.pprf variable que habilita visos preferenciales 
    		if(urls.pprf == 1){
	    		activeBlock3.click(function(e){
	    			e.preventDefault();
	    			location.href = btnBlock3.attr('href');
	    		});
    		}
    	}
	};
    // init
	//avisoIndex.addTarifa('#primOptP1');
	avisoIndex.clickBlock('#section1BW', '#section2BW', '#section3BW');
});