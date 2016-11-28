/*
 Animacion de Pestañita al mejor estilo de Google Maps =).
 */
$( function() {
    var page_request = "/empresa/mis-procesos/candidatos-sugeridos-ajax/";
	var pestana = {
		start : function(a,b,c,d) {
			var sidebar = $(a),
			sidebarWth = {
				cont : '10px',
				ext : '180px'
			}
			content = $(b),
			pestanaFlt = $(c),
			table = $(d),
			tableWth = {
				cont : '760px',
				ext : '960px'
			},
			spped = 'fast';
			$(a + ' .triggerSdb').bind('click', function(e) {
				e.preventDefault();
				sidebar.parent().animate({
					width: sidebarWth.cont
				}, spped, function() {
					sidebar.parent().addClass('hide');
					pestanaFlt.removeClass('hide');
					content.removeClass('contraido').addClass('extendido');
						table.animate({
						width: tableWth.ext
					});					
				} );
			});
			$(c + ' .triggerSdb').bind('click', function(e) {
				e.preventDefault();
				table.animate({
					width:tableWth.cont
				}, spped, function() {
					pestanaFlt.addClass('hide');
					$('.triggerSdb').addClass('hide');
					sidebar.parent().removeClass('hide');					
					content.addClass('contraido').removeClass('extendido');
					sidebar.parent().animate({
						width: sidebarWth.ext
					}, spped);					
				});	
			});
		},
		linkAmpliar : function(link){
			var btnLink = $(link),
			cntBlockIdent = $('#cntExtendNotif .sepBlockBtn').eq(0);
			btnLink.on('click', function(){
				var t = $(this);
				if(t.hasClass('pref')){
					cntBlockIdent.css('display','none');
				}else{
					cntBlockIdent.css('display','block');
				}				
			});
		},
        verAnuncio : function(a){
           $(a).bind('click',function(){
                var idverproceso = $(this).attr("href");
                var url = $(this).attr("rel");
                var idverproceso = $(this).attr("href");
                var url = $(this).attr("rel");
                var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
                $(contenido).html("");
                $(contenido).addClass("loading");

                $(contenido).load(url,function(){
                    $(contenido).removeClass("loading");
                });
            });
        },
        checkGrupal :function(a){
            $(a).live("change", function(e) {
                e.preventDefault();
                var idsgrupos = $("#dataProcesoBusquedaCandi tbody .data0");

                var checked='checked="checked"';
                if(!$(this).is(':checked')) {
                    checked='';
                }
                $.each(idsgrupos, function(index,item) {
                    var chk = $(item).find("input");
                    var cb = '<input type="checkbox" id= "'+(chk).attr('id')+'" name="select" '+checked+' relpos="'+$(chk).attr('relpos')+'">';
                    $(item).html(cb);
                });
            });
        },
        invitarVentana : function(a){
            var actual = $(a);            
            //boton Invitar
            actual.live("click",function(e){
               
                e.preventDefault();
                var arreglo_valores =[];
                var act = $(this);
                var valores = $("#dataProcesoBusquedaCandi tbody .data0").find("input:checked");
                var idAW = $("#dataProcesoBusquedaCandi").attr('relida');
                $.each(valores, function(index,item) {
                        var v = $(item).attr("relpos");
                        arreglo_valores.push(v);
                });
                if (arreglo_valores.length>0) {                    
                    if(!act.hasClass("winModal")){
                        act.addClass("winModal");
                        $(a).click();
                    } else {
                        var contenido = "#content-"+act.attr("href").substr(1, act.attr("href").length);
                        $(contenido).html("");
                        $(contenido).addClass("loading");
                        $.ajax({
                                type: "POST",
                                url: "/empresa/buscador-aptitus/invitar",
                                dataType: "html",
                                success: function(msg) {
                                    $(contenido).removeClass("loading");
                                    
                                    $(contenido).html(msg);
                                    $("#cntAvisoInv").find('#aviso').val(idAW);
                                    $('#aviso').click();
                                    $('#aviso').trigger('change');
                                    act.removeClass("winModal");
                                    $("a.invBtnProc").removeClass('invBtnProc').addClass('sendCandiS');
                                    return;
                                }
                        });
                    }
                } else {                    
                    pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos","error","Debe seleccionar al menos un postulante.");
                    return;
                }
            });
            //boton Invitar
            $("a.sendCandiS").live("click",function(){
               var valor = $("#aviso").val();               
               if(valor!="none"){
                   $("#content-winRegistrarInvitacionBuscador").html("");
                   $("#content-winRegistrarInvitacionBuscador").addClass("loading");
                  //INVITA A PROCESO
                    var arreglo_valores =[];
                    var valores = $("#dataProcesoBusquedaCandi tbody .data0").find("input:checked");
                    $.each(valores, function(index,item) {
                            var v = $(item).attr("relpos");
                            arreglo_valores.push(v);
                    });
                    var cantValores = arreglo_valores.length;
                    var json = {
                            "idaviso":valor,
                            "postulantes":arreglo_valores
                    };  
                    //alert(arreglo_valores.length);
                    var con=0;
                    if(con == 0){
                        con =1;                    
                        $.ajax({
                            type: "POST",
                            url: "/empresa/buscador-aptitus/invitar-postulante",
                            //data: "idaviso=" + valor + "&postulantes=" + arreglo_valores,
                            data: json,
                            dataType: "html",
                            success: function(msg) {
                                var result = msg.split("|");
                                $(".closeWM").trigger("click");
                                if($.trim(result[0])=="OK"){
                                	if (cantValores == 1 ) {
                                		pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos","info","Se ha enviado la invitacion de manera correcta");
                                	} else {
                                		pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos","info","Se han enviado las invitaciones de manera correcta");
                                	}
                                        setTimeout( function() {
                                            document.location.reload();
                                        },1500);
                                        
                                } else {
                                    pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos","error",result[1]);
                                }
                            }
                        });
                    }
               } else {
                   pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos","error","Debe seleccionar al menos un Aviso");
               }
            });
        },
        mostrarMensaje: function(a,tipo,mensaje) {
            var clasetipo ="";
            switch(tipo) {
            case 'info':
                clasetipo = "msgBlue";
                break;
            case 'error':
                clasetipo = "msgRed";
                break;
            case 'success':
                clasetipo = "msgYellow";
                break;
            }
            var variable = $(a);

            if(variable.size() > 1) {
                variable = $(variable[variable.size() - 1]);
            }
            variable.html(mensaje);
            variable.removeClass("msgYellow");
            variable.removeClass("msgRed");
            variable.removeClass("msgBlue");
            variable.addClass(clasetipo);
            variable.fadeIn("meddium", function() {
                setTimeout( function() {
                    variable.addClass("hide");
                    variable.addClass("r5");
                    variable.fadeOut("meddium");
                },2500);
            });
        },
        bntAgregarPostulanteGrupoClick : function(a) {
                $(a).bind("click", function(e) {
                    e.preventDefault();
                    idpos = undefined;
                    var link = "btnEnviarBolsa"; 
                    
                    //if($(this).attr("href") == ""){
                        $("#divBolsasEnviar").html('');
                        var contenido = "#divBolsasEnviar";
                        var idspostulantes = $("#dataProcesoBusquedaCandi tbody .data0").find("input:checked");
                        var arregloIds = [];
                        var id = 0;
                        $.each(idspostulantes, function(index,item) {
                            id = $(item).attr("relpos");
                            arregloIds.push(id);                            
                        });

                        if(idspostulantes.length < 1) {
                            $("#btnEnviarBolsa").removeClass("winModal");
                            $("#btnEnviarBolsa").removeClass("href", "#winEnviarBolsa");
                            $(".closeWM").click();                           
                            pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos", "error", "Debe seleccionar al menos un postulante");
                            return;
                        }
                        $("#spEPLabel").html('Enviar los postulantes');
                        $("#spEPNombrePostulante").html("");

                        //Si hay postulantes
                        if( $('#dataProcesoBusquedaCandi').find('td').size()>0){
                            $("#btnEnviarBolsa").addClass("winModal");
                            $("#btnEnviarBolsa").attr("href", "#winEnviarBolsa");               
                        }
                        pestana.traerGrupos(contenido, undefined, link, arregloIds);                       
                        return;
                    //} else {
                    //    $("#"+link).removeClass("winModal");
                    //    $("#"+link).attr("href", "");
                    //    return;
                    //}
                });
            },
        traerGrupos : function(contenido, idpostulante, link, idpostulantes) {
            var data = {
                    "idPostulante":idpostulante,
                    "idPostulantes":idpostulantes
            };            
            var data = "idPostulante="+idpostulante;
            if(idpostulante == undefined){
                data = "";
            }
            
            $.ajax({
                type: "POST",
                url: "/empresa/bolsa-cvs/get-grupos",
                data: data,
                dataType: "html",
                success: function(msg) {
                    $(contenido).html(msg);
                    if(pestana.trim(msg).length == 0) {
                        if (link != undefined) {
                            //$("#"+link).removeClass("winModal");
                            //$("#"+link).attr("href", "");
                        }
            //            $(".closeWM").click();
                        var msj = "El postulante ya se encuentra agregado en todos los grupos.";
                        if (idpostulantes != undefined) {
                            msj = "Los postulantes seleccionados ya se encuentran agregados en todos los grupos.";
                        }
                        pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos", "info", msj);
                    } else {
            //            if (link != undefined) {
            //                $("#"+link).click();
            //            }
                   }
                    //Tamanio Height
                    var cntBCVs = $('#divBolsasEnviar'),
                    hBCVs = cntBCVs.innerHeight();
                    cntBCVs.parents('.window').css('margin-top','-230px');
                    if(hBCVs > 300){
                        cntBCVs.css({
                            'width':'100%',
                            'height': '300px',
                            'overflow-x': 'hidden',
                            'overflow-y': 'scroll'
                        });
                    }
                }
            }); 
        },
        agregarPostulanteSugerido : function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();                
                var idsgrupos = $(".chkOpMPB").find("input:checked");
                var arreglo_idsgrupos =[];
                var id = 0;
                if (idsgrupos.length <= 0) {
                    $("#msgErrorNotasAg").html("* Debe seleccionar al menos un grupo.");
                    $("#msgErrorNotasAg").removeClass('hide');
                    setTimeout( function() {
                        $("#msgErrorNotasAg").html("*");
                        $("#msgErrorNotasAg").addClass("hide");
                    },3000);
                    return;
                }
                $.each(idsgrupos, function(index,item) {
                    id = $(item).attr("rel");
                    arreglo_idsgrupos.push(id);
                });
                var idspostulantes = $("#dataProcesoBusquedaCandi tbody .data0").find("input:checked");
                var arreglo_idspostulantes =[];
                var arreglo_idAnuncioWeb =[];
                var id = 0;
                
                if (idpos==undefined) {
                    $.each(idspostulantes, function(index,item) {
                        id = $(item).attr("relpos");
                        id2 = $(item).attr("id");
                        arreglo_idspostulantes.push(id);
                        arreglo_idAnuncioWeb.push(id2+ '-' + id);
                    });
                } else {
                    arreglo_idspostulantes.push(idpos);
                }
                var json = {
                    "idsPostulantes": arreglo_idspostulantes,
                    "idsGruposDestino": arreglo_idsgrupos
                };
                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/agregar-postulante",
                    data: json,
                    dataType: "json",
                    success: function(msg) {                        
                        if(msg.status == 'ok'){
                            $(".closeWM").click();
                            pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos", "success", msg.msg);
                            pestana.mostrarMensaje(".dvMensajeAccion", "success", msg.msg);
                        }else {
                            pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos", "error", msg.msg);
                            pestana.mostrarMensaje(".dvMensajeAccion", "success", msg.msg);
                        }
                    }
                });
                //---- QUITANDO POSTULANTE SUGERIDO --------
                /*
                var data = arreglo_idAnuncioWeb; 
                $.ajax({
                    type: "POST",
                    url: "/empresa/mis-procesos/quitar-postulantes-sugeridos",
                    data: "data=" + data,
                    dataType: "html",
                    success: function(msg) {                        
                        location.reload();
                    }
                });
                */
            });
        },
        quitarPostulanteSugerido : function(a){
            $(a).live("click", function(e) {
                e.preventDefault();
                var arregloIds = [];                 
                var id = 0;
                var contador = 0;
                var act = $(this);
                $("h3.titleAPLR").text("Quitar postulante sugerido");
                var idspostulantes = $("#dataProcesoBusquedaCandi tbody .data0").find("input:checked");
                $.each(idspostulantes, function(index,item) {
                    id = $(item).attr("id");
                    id2 = $(item).attr("relpos");
                    arregloIds.push(id+'-'+id2);
                    contador++;
                });

                if(idspostulantes.length < 1) {
                    $(".closeWM").click();
                    pestana.mostrarMensaje(".dvMensajesCAndidatosSugeridos", "error", "Debe seleccionar al menos un postulante");
                    return;
                }else{
                    act.attr('relpos',arregloIds);
                    if (contador == 1) {
                    	$("p#titleQ").text("¿Está seguro que desea quitar al candidato de la lista?");
                    } else {
                    	$("p#titleQ").text("¿Está seguro que desea quitar a los candidatos de la lista?");
                    }
                }
            });
        },
        confirmQuitarPostulanteSugerido : function(a){
            $(a).live("click", function(e){
                e.preventDefault();
                var data = $("#quitarPostulanteSugerido").attr('relpos');             
                $.ajax({
                    type: "POST",
                    url: "/empresa/mis-procesos/quitar-postulantes-sugeridos",
                    data: "data=" + data,
                    dataType: "html",
                    success: function(msg) {
                    	if (msg == 1) {
                    		$("p#titleQ").removeClass('loading bad').addClass('good success').html("Se retiro el candidato de manera correcta.");
                    	} else {
                    		$("p#titleQ").removeClass('loading bad').addClass('good success').html("Se retiraron los candidatos de manera correcta.");
                    	}
                        $('#btnQ').addClass('hide');
                        setTimeout(function(){
                            location.reload();
                        },1500);

                    }
                });
            })
        },
        trim: function(stringToTrim) {
            return stringToTrim.replace(/^\s+|\s+$/g,"");
        },
        ordenamiento : function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var contenido = "#contenido_ajax";
                var ord = $(this).attr("ord");
                var col = $(this).attr("col");
                //alert("ORD0: " + ord + "  COL0: " + col);
                //$(contenido).addClass("loading");
                var pagina = $("#dataProcesoBusquedaCandi").attr("page");
                var opcionlista = $("#dataProcesoBusquedaCandi").attr("opcionlista");
                var relIdA = $("#dataProcesoBusquedaCandi").attr("relIdA");
                var cadenabusqueda = $("#dataProcesoBusquedaCandi").attr("cadenabusqueda");
                //alert("pagina: " + pagina + "-opcionlista: " + opcionlista + "-cadenabusqueda: " + cadenabusqueda);
                $.ajax({
                        type: "POST",
                        url: page_request+cadenabusqueda,
                        data: "page="+pagina+"&ord="+ord+"&col="+col+"&anuncioId="+relIdA,
                        dataType: "html",
                        success: function(msg) {
                            $(contenido).removeClass("loading");
                            $(contenido).html(msg);
                        }
                });
            });
        },
        paginado : function(a) {
	        $(a).live("click", function(e) {
	          e.preventDefault();              
	          var pagina = $(this).attr("rel"),              
	          contenido = $("#contenido_ajax"),
	          objTable = $("#dataProcesoBusquedaCandi"),
	          ord = objTable.attr("ord"); //objTable.attr("ord"),
	          col = objTable.attr("col"); //objTable.attr("col");
	          opcionlista = objTable.attr("opcionlista");
	          (pagina == undefined) ? (pagina = '') : (pagina = pagina) ;
	          (ord == undefined) ? (ord = '') : (ord = ord) ;
	          (col == undefined) ? (col = '') : (col = col) ;
              //alert("ORD: " + ord + "  COL: " + col);
              var relIdA = $("#dataProcesoBusquedaCandi").attr("relIdA");
              var photoD = $("#dataProcesoBusquedaCandi").attr("relPhoto");
              var photoDN = $("#dataProcesoBusquedaCandi").attr("relPhotN");
	          objTable.wrap('<div class="loading" id="loadPagTable">');
	          objTable.addClass('hide');
	          $.ajax({
              type: "POST",
              url: page_request,
              data: "page="+pagina+"&ord="+ord+"&col="+col+"&anuncioId="+relIdA+"&photoD="+photoD+"&photoDN="+photoDN+"&listaropcion="+opcionlista,
              dataType: "html",
              success: function(msg) {
              	objTable.unwrap('<div class="loading" id="loadPagTable">');
              	objTable.removeClass('hide');
                contenido.html(msg);
              }
	          });
	        });
        }
        /*ajaxresultado: function(){
            var divContent = $('#ajaxDetalleCandidatos'),
                linkP = $('#ajaxDetalleCandidatos .detCandiC'),
                idAnun = divContent.attr('rel'),
                data = { id : idAnun };
            linkP.live('click', function(e){
                e.preventDefault();
                var pag = $(this).attr('page'),
                    col = $(this).attr('col'),
                    ord = $(this).attr('ord');
                data = {
                    id : idAnun,
                    col: col,
                    ord: ord,
                    page: pag
                };
                listaDetalleCandi(data);
            });
            //listaDetalleCandi(data);  //-- Inicia AJAX
            function listaDetalleCandi(json){
                divContent.addClass('loading');
                $.ajax({
                    type: "POST",
                    url: "/empresa/mis-procesos/lista-detalle-candidatos",
                    data: json,
                    dataType: "html",
                    success: function(data) {
                        divContent.removeClass('loading bad').html(data);
                    }
                });
            }
        }*/

	};
	//init
	pestana.start('#cabezasidebar', '#innerMain', '#pesatanaflotante', '#gridTableR');
	pestana.linkAmpliar('a[href="#winExtendN"]');
    //pestana.verAnuncio("a[href=#winVerProceso]");
    pestana.checkGrupal("#chkSelectAll");
    pestana.paginado(".paginador .itemPagC a[href]");
    pestana.invitarVentana("#invitarBusqueda");
    pestana.bntAgregarPostulanteGrupoClick("#btnEnviarBolsa");
    pestana.quitarPostulanteSugerido("#quitarPostulanteSugerido");
    pestana.confirmQuitarPostulanteSugerido('#winAlert a.yesCM'); //("input#btnAceptarQuitarBolsa");
    pestana.agregarPostulanteSugerido("#btnAceptarEnviarBolsa");
    //pestana.ajaxresultado();
    pestana.ordenamiento("#dataProcesoBusquedaCandi thead th a.candiC");
});