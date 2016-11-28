/*
  Testimonios
 */
$( function() {
        var msgst = {
            cBlockTestimonio : {
                block :'¿Está seguro que desea desactivar este testimonio?',
                desblock : '¿Desea activar el testimonio?',
                maxTes : 'Pueden estar activos como máximo ' + $("#frmBuscar_empresas").attr('maxTes') + ' testimonios.'
            },
            cMsjBlock: {
                block : 'Se desactivó el Testimonio de manera correcta.',
                desblock : 'Se activó el Testimonio de manera correcta.'
            }
        };
        var fncTes = {
			bloquearTestimonio : function(a) {
				$(a).live('click', function(e,url) {
					e.preventDefault();
					var t = $(this);
                    var maxT = $(this).attr('maxTes');
                    var actTes = $(this).attr('actTes');
					var idPost = t.attr('rel');
					var url = '', msj = '';opc = 0;
					var cntBtns = $('#inwinAlertBloquearTestimonio #btnQElimAdm');
                        //$("a.yesCM").css('visibility','visible');
						cntBtns.removeClass('hide');
					if(t.hasClass('desblockTes')) {
						$('#winAlertBloquearTestimonio #titleQ').removeClass('alertError')
						.html(msgst.cBlockTestimonio.block);
						url = '/admin/testimonio/desactivar-testimonio';
						msj = msgst.cMsjBlock.block;
                        opc = 0;
					} else {
                        if(actTes < maxT){
                            $('#winAlertBloquearTestimonio #titleQ').removeClass('alertError')
                            .html(msgst.cBlockTestimonio.desblock);
                            url = '/admin/testimonio/activar-testimonio';
                            msj = msgst.cMsjBlock.desblock;
                            opc = 1;
                        }else{
                            //$("a.yesCM").css('visibility','hidden');
                            $('#winAlertBloquearTestimonio #titleQ').addClass('alertError')
                            .html(msgst.cBlockTestimonio.maxTes);
                            cntBtns.addClass('hide');
                        }
					}
					$('#winAlertBloquearTestimonio .yesCM').attr({
                        'rel': $.trim(idPost),
                        'maxT': maxT,
                        'token': t.attr('data-token'),
                        'url': url,
                        'msj': msj,
                        'opc': opc
                    });
				});

				var clickAccep = $('#winAlertBloquearTestimonio .yesCM');
				$(clickAccep).live('click', function(e, url){
					e.preventDefault();
					var t = $(this),
					cntMsj = t.parent();
					cntMsj.empty().addClass('loading').prev().addClass('hide');
					$.ajax({
						'url' : t.attr('url'),
						'type' : 'POST',
						//'dataType' : 'JSON',
						'data' : {
							'idTestimonio' : t.attr('rel'),
                                                        'maxTestimonio' : t.attr('maxT'),
                                                        'tok' : t.attr('token')
						},
						'success' : function(res) {
                            if(opc == 1){
                                cntMsj.removeClass('loading bad').html("<div class='block'>Selecciona un orden: " + res
                                    + "</div><div class='all'><input type='button' value='' id='btnAceptarT' " + 
                                    "url='/admin/testimonio/actualizar-orden-testimonio' "
                                    + " idTestimonio='" + t.attr('rel') + "' msj='Se actualizo el orden.' " + 
                                    "class='sptEmp btnSptAdm' /></div><div id='msjDataResT'></div>");
                                $('#winAlertBloquearTestimonio .yesCM').removeClass('hide');
                                $('#winAlertBloquearTestimonio #btnQElimAdm').removeClass('hide');
                                //Ordenar
                                fncTes.ordenarTestimonio('#btnAceptarT');
                            }else if(opc == 0){
                                cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
                                setTimeout(function(){
                                    document.location.reload(true);
                                },500);
                            }
						},
						'error' : function(res) {
							cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
						}
					});
				});
			},
            ordenarTestimonio : function(a) {
                $(a).live('click', function(e){
                	var t = $(this),
                	orden = $("#orden").val(),
                	cntMsjOrden = $('#msjDataResT');
                	cntMsjOrden.siblings('div').remove();
                	cntMsjOrden.addClass('loading');
                    $.ajax({
                        'url' : t.attr('url'),
                        'type' : 'POST',
                        'data' : {
                            'idTestimonio' : t.attr('idTestimonio'),
                            'orden' : orden
                        },
                        'success' : function(res) {
                        	cntMsjOrden
                        	.addClass('good').removeClass('bad loading').html(res);
                            setTimeout(function(){
                                document.location.reload(true);
                            },500);
                        },
                        'error' : function(res){
                        	cntMsjOrden
                        	.addClass('bad').removeClass('good loading').html(res);
                        	setTimeout(function(){
                                document.location.reload(true);
                            },500);
                        }
                    });
                    
                });
            },
			charArea : function(area,num) {
				var trigger = $(area),
				chars = parseInt($(num).attr('data-chars'));
				if($.trim(trigger.val()).length > 0 && $.trim(trigger.val()).length <= chars){
					$(num).html(chars - $.trim(trigger.val()).length);
				}else{
					$(num).html(chars);
				}
				trigger.bind('keyup click blur focus change paste', function(e) {
					var t = $(this),
					countN = $(num),
					valueArea;
					countN.html(chars);
					var key = e.keyCode || e.charCode || e.which || window.e ;
					var length = t.val().length;
					countN.html( (chars - length) );
					if( length > chars ) {
						valueArea = t.val().substring(chars, '') ;
						trigger.val(valueArea);
						countN.html('0');
					}
				});
				trigger.bind('keypress', function(e) {
					var t = $(this),
					countN = $(num),
					valueArea;
					var key = e.keyCode || e.charCode || e.which || window.e ;
					var length = t.val().length + 1;
					if( length > chars ) {
						if(key != 8){
							return e.preventDefault();
						}
					}
				});
			},
	        fWordsSafe : function(fID, params) {
	            return $(fID).each( function() {
	                var t = $(this);
	                t.keypress( function(e) {
		                var key = e.keyCode || e.charCode || e.which || window.e,
		                tecla = String.fromCharCode(key).toLowerCase(),
		                letras = params,
		                //Borrar, Comilla Tilde
		                especiales = [8,222],
		                tecla_especial = false;
		                for(var i in especiales){
		                	if(key == especiales[i]){
		                		tecla_especial = true;
			                 	break;
	                    	} 
		                }
		                if(letras.indexOf(tecla)==-1 && !tecla_especial){
		                    e.preventDefault();
		                }
	                });
	            });	        
            },
            formReset : function(a) {
                $(a).click(function(e){
                    e.preventDefault();
                    //Testimonio
                    if ($($(a).parent().parent()).attr('id') == 'frmBuscar_empresas') {
                        $('#frmBuscar_empresas input[type="text"]').val('').next().text('');
                        window.location = urls.siteUrl + '/admin/gestion/testimonios';
                    }
                });
            },
            saveTestimonio : function(a) {
                $(a).click(function(e){
                    e.preventDefault();
                    var rel = $(a).attr('rel'),
                        cboEstado = $("#fEstado").val(),
                        dataText = '';

                    if (($("#fNombreEmpresa").val() != '') && ($("#fUbicacion").val() != '') &&
                        ($("#fTestimonio").val() != '') && ($("#fReferente").val() != '') &&
                        ($("#fCargo").val() != '')) {
                        if ((rel == 1) && (cboEstado == 'activo')) {
                            $('#saveModal').click();
                            dataText += "¿Desea guardar este testimonio como activo y reemplazar al testimonio que se encuentre en el orden seleccionado?";
                                $("#winAlertEliminarAdm #titleQ").html(dataText);
                            return;
                        } else {
                            $("#datoBasicoF").submit();
                            return;
                        }
                    }else{
                        $("#datoBasicoF").submit();
                        return;
                    }                    
                });
            },
            procesSaveTestimonio : function(a){
                $(a).live('click', function(){
                    $("#datoBasicoF").submit();
                    return;
                });
            }
        };
        //Init
        fncTes.bloquearTestimonio('a.tes');
        fncTes.charArea('#fTestimonio','#count2P3');
        fncTes.fWordsSafe('#fUbicacion','áéíóúüàè abcdefghijklmnñopqrstuvwxyz');
        fncTes.fWordsSafe('#fReferente','áéíóúüàè abcdefghijklmnñopqrstuvwxyz');
        fncTes.fWordsSafe('#fCargo','áéíóúüàè abcdefghijklmnñopqrstuvwxyz');
        fncTes.fWordsSafe('#fNombreEmpresa','áéíóúüàè abcdefghijklmnñopqrstuvwxyz1234567890-');
        fncTes.formReset('#fResetRS');

        //-- Guardar testimonio
        fncTes.saveTestimonio("#fSubmit");
        fncTes.procesSaveTestimonio("#winAlertEliminarAdm a.yesCM");

});