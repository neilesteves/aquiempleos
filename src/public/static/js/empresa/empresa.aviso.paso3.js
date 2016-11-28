/*
 Aviso Preferencial Paso 3
 */
$( function() {
	//Activo Btn Paso3
	$('#nextEmpP3').removeAttr('disabled');
    var mUrl =  urls.mediaUrl,
        bUrl = urls.siteUrl,
        regex = /<[^>]*>/gi,
        flagRadios = false,
        flagRadioTmp = false,
        flagTemplates = false;	
    var msgs = {
        msg1 : 'Ingrese el texto o contenido del aviso.',
        msg2 : 'Seleccione la opción de diseño.',
        msg3 : 'Seleccione el diseño en base a la plantilla e ingrese el texto o contenido del aviso.'
	};
    //Editor
    tinyMCE.init({
    	mode : 'specific_textareas',
        editor_selector : 'tinymce',
    	theme : 'advanced',
    	language : 'es',
    	plugins : 'safari, advlist, paste, xhtmlxtras',
    	theme_advanced_buttons1 : 'fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,',
    	theme_advanced_buttons2 : '',
    	theme_advanced_buttons3 : '',
    	theme_advanced_buttons4 : '',
    	theme_advanced_toolbar_location : 'top',
    	theme_advanced_toolbar_align : 'left',
    	theme_advanced_statusbar_location : '',
    	theme_advanced_resizing : false,
    	paste_auto_cleanup_on_paste : true, 
    	paste_text_use_dialog : false,
    	content_css : mUrl + "/css/tynimce.css",
    	theme_advanced_font_sizes: '10px,12px,13px,14px,16px,18px,20px',
    	font_size_style_values : '10px,12px,13px,14px,16px,18px,20px',
    	init_instance_callback : function(ed){
    		var txtCount = document.getElementById('count2P3'),
    		body = tinymce.get('areaEditorP3').getBody(),
    		text = tinymce.trim(body.innerText || body.textContent),
    		regex = mUrl.regex,
			output = text.replace(regex,''),
			numChars = output.length,
			areaTyniMCE = document.getElementById('areaEditorP3'),
    		limitChars = parseInt(areaTyniMCE.getAttribute('data-limit'));
    		//Major limit text
    		if( numChars >= limitChars ){
    			textLimit = text.substr(0,limitChars);
    			tinymce.get('areaEditorP3').setContent(textLimit);
    			//initChars
    			txtCount.innerHTML = limitChars;
    		}else{
    			//Init Chars
    			txtCount.innerHTML = limitChars - numChars;
    		}
    		//onload
    		//se muestra cuando carga el editor
    		var divCntEdit = $('#flagLoadEdit');
    		divCntEdit.removeAttr('style');
    		divCntEdit.parent().removeClass('loading');
    		
    	},
    	setup : function(ed) {
    		var txtCount = document.getElementById('count2P3'),
    		areaTyniMCE = document.getElementById('areaEditorP3');
    		//ed.pasteAsPlainText = true;
    		//keyPress    		
    		ed.onKeyPress.add(function(ed, e) {
        		var limitChars = parseInt(areaTyniMCE.getAttribute('data-limit'));
    			body = tinymce.get('areaEditorP3').getBody(),
    			text = tinymce.trim(body.innerText || body.textContent),
    			regex = mUrl.regex,
    			output = text.replace(regex,''),
    			num = output.length + 1,
    			key = e.keyCode || e.charCode || e.which || window.e ;
    			if(output.length >= limitChars){
    				//e.preventDefault();
    				if(key != 8) {
    					return tinymce.dom.Event.cancel(e);
    				}
    			}
    			//conteo en keyUp
    			//txtCount.innerHTML = limitChars - num;
    		});
    		//keyDown
    		ed.onKeyUp.add(function(ed, e) {
        		var limitChars = parseInt(areaTyniMCE.getAttribute('data-limit'));
    			body = tinymce.get('areaEditorP3').getBody(), 
    			text = tinymce.trim(body.innerText || body.textContent),
    			regex = mUrl.regex,
    			output = text.replace(regex,''),
    			num = output.length;
    			txtCount.innerHTML = limitChars - num;
    			if(parseInt(txtCount.innerHTML) < 0){
    				txtCount.innerHTML = '0';
    			}
    		});
    		//paste
    		ed.onPaste.add(function (ed, e) {
                ed.execCommand('mcePasteText', true);
                //return tinymce.dom.Event.cancel(e);
                $('#areaEditorP3_ifr').addClass('hidden');
                
        		setTimeout(function(){
        			$('#areaEditorP3_ifr').removeClass('hidden');
                    var txtCount = document.getElementById('count2P3'),
            		body = tinymce.get('areaEditorP3').getBody(),
            		text = tinymce.trim(body.innerText || body.textContent),
            		regex = mUrl.regex,
        			output = text.replace(regex,''),
        			numChars1 = output.length,
        			areaTyniMCE = document.getElementById('areaEditorP3'),
            		limitChars = parseInt(areaTyniMCE.getAttribute('data-limit')),
            		textLimit;
            		//Major limit text
            		if( numChars1 > limitChars ){
            			textLimit = text.substring(0,limitChars);
            			tinymce.get('areaEditorP3').setContent(textLimit);
            			//initChars
            			txtCount.innerHTML = '0';
            		}else{
            			//Init Chars
            			txtCount.innerHTML = limitChars - numChars1;
            		}
        		},200);
            });
    		//undo
    		ed.onUndo.add(function (ed, e) {
    			var limitC = parseInt($('#areaEditorP3').attr('data-limit')),
    			cnt = e.content,
    			textL;

    			cnt = cnt.replace(/<br[^<>]*>/ig,' ');
    			cnt = cnt.replace(/(<p[^<>]*>)/ig,'');
    			cnt = cnt.replace(/(<\/p[^<>]*>)/ig,' ');
    			cnt = cnt.replace(/(<\S([^<>]*)>)/ig,'');
    			cnt = cnt.replace(/(\[br\])/ig,' ');
   			 	cnt = cnt.replace(/(\[p\])/ig,'');
   			 	cnt = cnt.replace(/(\[\/p\])/ig,' ');

                if(cnt.length > limitC){
                	textL = cnt.substr(0,limitC);
        			tinymce.get('areaEditorP3').setContent(textL);
                }
    		});    		
    		//change input
    		var ipts = $('label.itemImgAdvAP');    		
    		ipts.bind('click', function(e){
    			var t = $(this),
    			dataLimit = t.find('input').attr('data-limit');
    			$('#areaEditorP3').attr('data-limit', dataLimit); 
    			
    			$('#msgRelTmp').slideUp('fast');
    			
    			var divUpLogo = $('#divLogoAP3');
    			
    			if(t.hasClass('templateText')){
    				divUpLogo.slideUp('fast');
    			}else if(t.hasClass('templateTextLogo')){
    				divUpLogo.slideDown('fast');
    			};

    			var numCT = $('#count2P3'),
    			body1 = tinymce.get('areaEditorP3').getBody(),
        		text1 = tinymce.trim(body1.innerText || body1.textContent),        
    			numChars1 = text1.length,
    			limitChars1 = parseInt(areaTyniMCE.getAttribute('data-limit')),
    			txtCount2 = document.getElementById('count2P3');    			
    			
    			if( numChars1 >= limitChars1 ){
        			var textLimit1 = text1.substr(0,limitChars1);
        			tinymce.get('areaEditorP3').setContent(textLimit1);
        			//initChars
        			txtCount2.innerHTML = limitChars1;
        		}else{    		
        			//Init Chars
        			txtCount2.innerHTML = limitChars1 - numChars1;
        		}
    		});
    	}
    });
    //Paso 3    
	var avisoPrefP3 = {
	    linkTemplate : function(labels){
	        var templates = $(labels);
	        templates.on('click', function(e){
	            var t = $(this);
	            templates.removeClass('itemActiveAP');
	            templates.find('input[type="radio"]').removeAttr('checked');
	            t.addClass('itemActiveAP');
	            t.find('input[type="radio"]').attr('checked', 'checked');


	           var arrInput = $('input[name="tipo_diseno"]');
	            if( arrInput.index($(arrInput + ':checked')) == 2 ){
	
	            flagRadios = true;
	            flagRadioTmp = true;
	            flagTemplates = true;

	            }

	            var cntError = $('#msgErrorEmpP3');
	            cntError.text('');
	        });
	        templates.mouseenter(function(e){
	            var t = $(this),
	            body = $('body');
	            body.append('<div style="left:' + (t.offset().left + 130) + 
	            'px; top:' + (t.offset().top - 20) + 
	            'px" class="tempImgBig"><img src="' + 
	            t.find('img').attr('src') + 
	            '" alt="template" /></div>');
	        }).mouseleave(function(){
	            $('.tempImgBig').remove();
	        });
	        $('.listImgAdvAP1:last').removeClass('lineBdrAP');
	        $('#maskPaso3Cnt').css('height', $('#heightMaskA').height());
	    },           
	    pasteMaxlength : function(sFiled){
	        $(sFiled).bind('keyup click blur focus change paste', function(e){
	            var t = $(this),
	            chars = t.val(),
	            charsSize = (t.val()).length,
	            size = parseInt(t.attr('maxlength'));
	            if( charsSize > size ){
	                var valField = chars.substring(size, 0);
	                t.val(valField);
	            }
	        });
	        $(sFiled).bind('keypress', function(e){
	            var t = $(this),
	            chars = t.val(),
	            valor = e.keyCode || e.charCode || e.which || window.e,
	            charsSize = (t.val()).length,
	            size = parseInt(t.attr('maxlength'));
	            if( charsSize > size && valor ){
	                return false;
	            }else{
	                return true;
	            }
	        });
	        $(sFiled).bind('paste', function(e){
	            var t = $(this);
	            setTimeout(function(){
	                var chars = t.val(), 
	                charsSize = t.val().length,
	                size = parseInt(t.attr('maxlength'));
	                if( charsSize > size ){
	                    var valField = chars.substring(size, 0);
	                    t.val(valField);
	                }
	            },0);
	        });
	    },
	    acordionP3 : function(label1, label2){
	    	var lbl1 = $(label1),
	    	lbl2 = $(label2),
	    	blk1 = $('#labelAvs1'),
			blk2 = $('#labelAvs2');
	
	    	lbl1.bind('click', function(){
	    		var t = $(this);
				blk2.slideUp('fast');
	    		blk1.slideDown('fast');
	    	});
	    	
	    	lbl2.bind('click', function(){
	    		var t = $(this);
				blk1.slideUp('fast');
	    		blk2.slideDown('fast');
	    	});            	
	    	
	    },
	    submitP3 : function(){
	    	var formN = $('#formP3APimg'),
	    	msgN = $('#msgErrorEmpP3'),
	    	input1 = $('#dLabelAvs1 input'),
	    	input2 = $('#dLabelAvs2 input'),
	    	btnNP4 = $('#nextEmpP3'),
	    	inputON = $('input[name="nameAvsP3"]');	    	
	    	msgN.text(''),
	    	msg1 = 'Debe ingresar el contenido del aviso.',
	    	msg2 = 'Debe seleccionar el diseño de la plantilla.',
	    	msg3 = 'Debe seleccionar el diseño de la plantilla e ingresar los datos del aviso.',
	    	msg4 = 'Debe seleccionar una opción.';
	    	
	    	var blk1 = $('#labelAvs1'),
			blk2 = $('#labelAvs2');
	    	if(input1.prop('checked')){
	    		blk2.slideUp('fast');
	    		blk1.slideDown('fast');
	    	}else if(input2.prop('checked')){
	    		blk1.slideUp('fast');
	    		blk2.slideDown('fast');
	    	}	    	
	    	
	    	btnNP4.bind('click', function(e){
	    		e.preventDefault();
	    		var t = $(this);
	    		msgN.text('');
	    		var body = tinymce.get('areaEditorP3').getBody(), 
    			text = tinymce.trim(body.innerText || body.textContent),
    			regex = mUrl.regex,
    			output = text.replace(regex,''),
    			numChars = output.length;
	    		
	    		if(input1.prop('checked')){
	    			//Aqui valida primera opcion
	    			//submit
	    			formN.submit();
		    	}else if(input2.prop('checked')){
		    		//segunda opcion
	    			var inpts2N = $('input[name="inputImgAdv"]');
	    			if(inpts2N.is(':checked') && numChars <= 0){
	    				msgN.text(msg1);
	    				//tinyMCE
	    				tinymce.execCommand('mceFocus', false, 'areaEditorP3');
	    			}else if(inpts2N.is(':checked') == false && numChars > 0){
	    				msgN.text(msg2);
	    			}else if(inpts2N.is(':checked') == false && numChars <= 0){
	    				msgN.text(msg3);
	    			}else{
	    				//submit
	    				formN.submit();
	    			}	    			
		    	}else{
		    		//no a seleccionado
		    		msgN.text(msg4);
		    	}	
	    	});
	    },
	    loadChecked : function(){
    		//load cheked
    		var divUpLogo = $('#divLogoAP3'),
    		msgEditB = $('#msgRelTmp');
    		if($('.templateText input[type="radio"]').is(':checked')){
    			divUpLogo.slideUp('fast');
    			msgEditB.slideUp('fast');
    		}else if($('.templateTextLogo input[type="radio"]').is(':checked')){
    			divUpLogo.slideDown('fast');
    			msgEditB.slideUp('fast');
    		}
	    },
	    labelIE6 : function(){
            if( $.browser.msie && $.browser.version.substr(0,1) < 7 ) {
                var lbl1 = $('#dLabelAvs1'),
                ipt1 = lbl1.find('input'),
                lbl2 = $('#dLabelAvs2'),
                ipt2 = lbl2.find('input');
                lbl1.on('click', function(e){
                    ipt1.attr('checked', 'checked');
                    ipt2.removeAttr('cheked');
                });
                lbl2.on('click', function(e){
                    ipt2.attr('checked', 'checked');
                    ipt1.removeAttr('cheked');
                });
            }
	    }
	};
	// init
        avisoPrefP3.linkTemplate('.itemImgAdvAP');
        //Paso3
        avisoPrefP3.acordionP3('#dLabelAvs1', '#dLabelAvs2');
        avisoPrefP3.submitP3();
        avisoPrefP3.loadChecked();
        avisoPrefP3.labelIE6();
});