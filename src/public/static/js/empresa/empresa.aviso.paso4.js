/*
 Registro Empresa Aviso Paso 4
 */
$( function() {
    var cacheStatic = urls.staticCache;
    if(cacheStatic == undefined){
    	cacheStatic = '';
    }else{
    	cacheStatic = cacheStatic;
    }	
	var msgs = {
		mDelete : {
			success : '¡Se eliminó satisfactoriamente!',
			error : '¡Se produjo un error al eliminar.!',
			prog : 'Eliminando...',
			def : 'Está seguro que desea eliminar?',
			expe : 'Está seguro que desea eliminar?, también se eliminaran tus referencias relacionadas.'
		}
	};
	var formP4 = {
            verAnuncio : function(a){
               $(a).bind('click',function(){
                    var idverproceso = $(this).attr("href");
                    var url = $(this).attr("rel");
                    var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
                    $(contenido).html("");
                    $(contenido).addClass("loading");

                    $(contenido).load(url,function(){
                        $(contenido).removeClass("loading");
                        formP4.scrollAviso(450);
                        //Paginado aviso preferencial
                        formP4.pagerAvisoMembresia('.aPagerAP');
                        //Tooltip
                        formP4._tooltipLive('.tooltipApt');
                        formP4._tooltipLive('.firstAP a');
                    });

                });
            },
            scrollTb : function(){
                var heightL = $('#overflowProHist'),
                tableH = $('#dataProcesosHistorial');        	
                if(parseInt(tableH.height()) > parseInt(heightL.height()) - 20 ){
                        heightL.addClass('overTableHS');
                }    	
            },
            scrollAviso : function(heightBase){
                setTimeout( function(){
                	var heightL = $('#dataFormAddNADM .cntModalAEmp'),
                    heightcontent = heightL.height(),
                    alto = 480;
                	if(heightBase){
                		alto = heightBase;
                	}else{
                		alto = alto;
                	}                	
                    if(heightcontent > alto ){
                            heightL.addClass('srollModalAEmp').css({'height': alto});
                    }
                },0); 	
            },
            ejmExtracargos : function(clickEjm){
                    var clickEjmA = $(clickEjm);
                    clickEjmA.bind('click', function(){
                            var t = $(this);
                            imgData = t.attr('rel'); 
                            cntImg = $('#cntExtracargos');
                            cntImg.addClass('loading').html('<img src="' + imgData + '?' + cacheStatic +'"/>');
                    });
            },
            pagerAvisoMembresia : function(liPager){
                var liPager = $(liPager).not('.normalItem');
                liPager.on('click', function(e){
                    e.preventDefault();
                    var t = $(this);                
                    if(t.hasClass('readyItem')){                    
                        var dataAjax = t.attr('data-ajax');
                        var cntData = $('#cntLoadAAP');
                        cntData.children().addClass('hide');
                        cntData.css({'height':'300px','width':'100%'}).addClass('loading');
                        $.ajax({
                            type : 'POST',
                            url : dataAjax,
                            dataType : 'HTML',
                            success : function(xhr){
                                cntData.removeClass('loading').removeAttr('style');
                                cntData.children().removeClass('hide');
                                cntData.html(xhr);
                                liPager.addClass('readyItem').removeClass('currentItem');
                                t.addClass('currentItem').removeClass('readyItem');   
                                //scroll
                                formP4.scrollAviso(450);
                            }
                        });
                    }
                });
            },
            _tooltipLive : function(classTooltip){
                var trigger = $(classTooltip);
                var arrTitle = [] ;	
                $.each(trigger, function(i,v){
                        $(v).attr('rel',i);
                        arrTitle.push($(v).attr('title'));
                        $(v).removeAttr('title');
                });	                     

                    trigger.mouseenter(function(e){
                            var body = $('body');
                            var t = $(this),
                            tHeight = t.innerHeight(),
                            pos = t.offset(),
                            posLeft = pos.left,
                            posTop = pos.top + tHeight;
                            var tool = '<div class="tooltipCnt" style="left:' + posLeft + 'px; top:' + posTop + 'px"><div class="cachitoT r2">' + arrTitle[parseInt(t.attr('rel'))] + '</div></div>';
                            body.append(tool);

                            t.removeAttr('title');
                            //ie alt
                            //t.attr('alt','');
                            t.removeAttr('alt');

                            //centrado cachito
                            var newTool = $('body > .tooltipCnt'),
                            wTool = newTool.innerWidth(); 
                            newTool.css('left',posLeft - (wTool/2) + (t.innerWidth()/2));
                    }).mouseleave(function(){
                            var t = $(this);
                            //t.attr('title',arrTitle[parseInt(t.attr('rel'))]);
                            $('.tooltipCnt').remove();
                    });

            },
            
            radioFormaPago: function(radioInput) {
                var radio = $(radioInput),
                combo = $("#usoMembresia"),
                cntOpts = $('#blockTPBA'),
                lblSaldo = $('#lblSaldoEmpresa'),
                lblrecargoR = $('#mtoRecargoCreditoAVP'),
                lblprenormalR = $('#mtoPrecioNormalAVP'),
                radios = cntOpts.find('input[type="radio"]');
                if(radio.size()>0 && combo.size()<=0){
                    //carga
                    if($.trim(radio.val()) == 'credito'){
                        formP4.consultarTarifa("R");
                        
                    }
                }    
                
                radio.on('change', function(e){
                    var t = $(this),
                        btnNextEmpP3 = $('#nextEmpP3'),
                        textTraking = btnNextEmpP3.attr('onclick'),
                        newTextTraking = textTraking.replace(/_\w+(?='])/ig, '_' + t.data('type'));
                    btnNextEmpP3.attr('onclick', newTextTraking);

                    value = t.val();

                    if($.trim(value) == 'credito'){
                        formP4.consultarTarifa("R");
                        lblrecargoR.removeClass('hide');
                        lblprenormalR.removeClass('hide');
                    }else {
                        lblrecargoR.addClass('hide');
                        lblprenormalR.addClass('hide');
                        formP4.consultarTarifa("N");
                    }
                });
            },
            comboMembresia: function(combo){
                var selectC = $(combo),
                cntOpts = $('#blockTPBA'),
                lblSaldo = $('#lblSaldoEmpresa'),
                radios = cntOpts.find('input[type="radio"]');
                if(selectC.size()>0){
                    //carga
                    if($.trim(selectC.val()) != 'N' && $.trim(selectC.val()) != 'C'){
                        lblSaldo.removeClass('txtpLabelSaldoP4');
                        radios.attr('disabled','disabled');
                        radios.removeAttr('checked');
                    }else{
                        lblSaldo.addClass('txtpLabelSaldoP4');
                        radios.removeAttr('disabled');  
                        radios.removeAttr('checked');
                        $(radios[0]).attr('checked','checked'); 
                    }
                    //change
                    selectC.on('change', function(e){
                        var t = $(this),
                        ninguno = false,
                        value = t.val();
                        if($.trim(value) != '0'){
                            value = "Z";
                            lblSaldo.removeClass('txtpLabelSaldoP4');
                            radios.attr('disabled','disabled');
                            radios.removeAttr('checked');
                        }else {
                            value = "N";
                            ninguno = true;
                            lblSaldo.addClass('txtpLabelSaldoP4');
                            radios.removeAttr('disabled');  
                            radios.removeAttr('checked');
                            $(radios[0]).attr('checked','checked');
                        }
                        
                        
                        if(ninguno) {
                            var dataCredito = $("#contratoR").attr('precio');
                            if (dataCredito != undefined) value = "R"; 
                        }

                        formP4.consultarTarifa($.trim(value));
                    });
                }
            },
            consultarTarifa : function(tipoContrato) {
                var precio = $('#contrato'+tipoContrato).attr('precio');
                var descuento = $('#contrato'+tipoContrato).attr('descuento');
                var saldo = $('#contrato'+tipoContrato).attr('saldo');

                //if (!isNaN(saldo) && !isNaN(descuento) && !isNaN(saldo)) {
                if (!isNaN(parseFloat(saldo)) && !isNaN(parseFloat(descuento)) ) {

                    if (parseFloat(descuento) == 0) {
                        if (!$('#dvPrecioDescuento').hasClass("hide")) {
                            $('#dvPrecioDescuento').addClass("hide");
                        }
                        if (!$('#dvPrecioNormal').hasClass("hide")) {
                            $('#dvPrecioNormal').addClass("hide");
                        }
                    } else {
                        if ($('#dvPrecioDescuento').hasClass("hide")) {
                            $('#dvPrecioDescuento').removeClass("hide");
                        }
                        if ($('#dvPrecioNormal').hasClass("hide")) {
                            $('#dvPrecioNormal').removeClass("hide");
                        }
                    }

                    if (parseFloat(saldo) <= 0) {
                        if (!$('#lblSaldoEmpresa').hasClass("hide")) {
                            $('#lblSaldoEmpresa').addClass("hide");
                        }
                    } else {
                        if ($('#lblSaldoEmpresa').hasClass("hide")) {
                            $('#lblSaldoEmpresa').removeClass("hide");
                        }
                    }

                    $('#saldoAP').html(saldo);
                    //$('#priceTotP4').html(precio);
                    $('#descuentoAP').html(' '+descuento);
                }
            }
	};
	// init
	//paso 4
	formP4.verAnuncio("a[href=#winVerProceso]");
	formP4.ejmExtracargos('.imgExtraCargos');
	formP4.radioFormaPago('.inputTarjM');
	formP4.comboMembresia('#usoMembresia');
});