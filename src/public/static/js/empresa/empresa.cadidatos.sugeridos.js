/*
mis procesos
*/
$(function(){
    var page_request = "/empresa/buscador-aptitus/buscador-ajax/";
    var dataI = {
		speed1 : 'slow',
		speed2 : 'fast'
	};
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
                    bad : '¡Se requiere su mensaje!',
                    def : 'Ingrese mensaje correcto'
            },
            cQuestions : {
                    good : '.',
                    bad : 'Responder pregunta',
                    def : 'Responda con criterio'
            },
            cInvitacion : {
                    good : 'Invitación exitosa',
                    bad : 'Invitación fallida'
            },
            mailSend : 'El e-mail fue enviado.',
            mailError : 'No se pudo enviar el e-mail.'
    };
    var vars = {
            rs : '.response',
            okR :'ready',
            sendFlag : 'sendN',
            speed1 : 'slow',
            speed2 : 'fast'
    };
    var buscador = {
        verPerfilFilPerfP : function () {
                $('.verPerfilFilPerfP_Nombre').live('click', function(e) {
                    e.preventDefault();
                    $(this).parents("td").find(".verPerfilFilPerfP").trigger("click");
                });
                $('.verPerfilFilPerfP_Imagen').live('click', function(e) {
                    e.preventDefault();
                    $(this).parents("tr").find(".data3 .verPerfilFilPerfP").trigger("click");
                });
                $('.verPerfilFilPerfP').live('click', function(e) {
                        $(this).parents("tr").removeAttr("class").addClass("pintarLeidos");
                        e.preventDefault();
                        e.stopPropagation();
                        var ajaxCnt = $('#ajax-loading');
                        $a = $(this);
                        $idAPM = $a.parent().parent().parent().children('.data0').children();
                        $('#innerMain').slideUp(dataI.speed1, function(){
                                ajaxCnt.slideDown(dataI.speed1);
                                $.ajax({
                                        type: 'get',
                                        url: '/empresa/buscador-aptitus/perfil-publico-emp',
                                        data: {
                                                id: $a.attr('rel'),
                                                idAPM: $idAPM.attr('id')
                                        },
                                        success: function(response) {
                                                ajaxCnt.slideUp(dataI.speed1);
                                                $('#innerMain').parent().append('<div id="perfilContainer" class="all"></div>');
                                                $('#perfilContainer').html(response);
                                                $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
                                                $('#perfilContainer').slideDown(dataI.speed1);
                                                //Load JS perfil																					
                                            	AptitusPerfil();
                                                $('.icoCloseMsjD').unbind();
                                                buscador.closeMsgPerfil('.icoCloseMsjD');
                                        },
                                        dataType: 'html',
                                        cache: true
                                });
                        });
                        return false;
                });
        },
        backToProcess : function () {
                $('#backToProcess').live('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var cntPerfilC = $('#perfilContainer');
                        cntPerfilC.slideUp(dataI.speed1, function() {
                                cntPerfilC.remove();
                                $('#innerMain').slideDown(dataI.speed1, function(){
                                    var categoria = $("#dataProcesoBusquedaCandi").attr("categoria");
                                    var opcion = $("#dataProcesoBusquedaCandi").attr("opcionlista");
                                    if (opcion == '1') {
                                    	$(".liListOptE[rel="+opcion+"]").click();
                                    }
                                });
                        });
                        return false;
                });
        },
				closeMsgPerfil : function(clickClose) {
					var clickCloseA = $(clickClose),
					speed = 'slow';
					clickCloseA.click( function(e) {
						e.preventDefault();
						var t = $(this),
						cnt = t.parents('.msgYellow');
						cnt.fadeOut(speed, function() {
							cnt.remove();
						});
					});
				},        
        ordenamiento : function(a) {
                $(a).live("click", function(e) {
                        e.preventDefault();
                        var contenido = "#contenido_ajax";
                        var ord = $(this).attr("ord");
                        var col = $(this).attr("col");
                        //$(contenido).addClass("loading");
                        var pagina = $("#dataProcesoBusqueda").attr("page");
                        var opcionlista = $("#dataProcesoBusqueda").attr("opcionlista");
                        var cadenabusqueda = $("#dataProcesoBusqueda").attr("cadenabusqueda");
                        $.ajax({
                                type: "POST",
                                url: page_request+cadenabusqueda,
                                data: "page="+pagina+"&ord="+ord+"&col="+col+"&listaropcion="+opcionlista,
                                dataType: "html",
                                success: function(msg) {
                                        $(contenido).html(msg);
                                }
                        });
                });
        },
        buscadordeempresa : function(a,b) {
            var A = new Arreglo(6);
            var actual = $(a);
            var check = $(b);
            actual.live("click",function(e){
                e.preventDefault();
                var checkbox = $(this).parent().siblings(".left").find("input");
                checkbox.trigger("click");
                e.stopPropagation();
            });

            check.live("change",function(){
            	var check = $(this).is(":checked");       
                $("#mensajeEntradaBuscador").hide();
                $("#contenido_ajax").removeAttr("style");

                $(".checkbuscador").attr("disabled","disabled");
                var info = $(this).attr("rel");
                var dividir = info.split("/");
                var descripcion = dividir[0];
                var valor = dividir[1];
                A.crear(descripcion);
                if($(this).prop('checked')) {
                    A.push(descripcion,valor);
                } else {
                    A.remove(descripcion,valor);
                }
                var ruta = A.rutaFinal("--");
                var opcionlista = $("#dataProcesoBusqueda").attr("opcionlista");
                var texto = $("#fWordRS").val();

                var contenido = "#contenido_ajax";
                $(contenido).html("");
                $(contenido).addClass("loading");
                //ajax
                $.ajax({
                        type: "POST",
                        url: page_request+"query/"+texto+ruta,
                        data: '&listaropcion='+opcionlista+'&check='+check+'&tipo='+descripcion,
                        dataType: "html",
                        success: function(msg) {
                            $(contenido).removeClass("loading");
                            $(contenido).html(msg);
                            $(".checkbuscador").removeAttr("disabled");
                        }
                });
            });


            $("#fSendRS").bind("click", function(e){
                e.preventDefault();
                $("#mensajeEntradaBuscador").hide();
                $("#contenido_ajax").removeAttr("style");

                var texto = $("#fWordRS").val();
                texto = texto.replace(/%+/g,"");
                texto = texto.replace(/á+/g,"a");
                texto = texto.replace(/é+/g,"e");
                texto = texto.replace(/í+/g,"i");
                texto = texto.replace(/ó+/g,"o");
                texto = texto.replace(/ú+/g,"u");

                $("#fWordRS").attr("disabled","disabled");
                var ord = $("#dataProcesoBusqueda").attr("ord");
                var col = $("#dataProcesoBusqueda").attr("col");
                var opcionlista = $("#dataProcesoBusqueda").attr("opcionlista");
                var cadenabusqueda = $("#dataProcesoBusqueda").attr("cadenabusqueda");
                var contenido = "#contenido_ajax";
                $(contenido).html("");
                $(contenido).addClass("loading");

                if(ord==undefined) ord="";
                if(col==undefined) col="";
                if(opcionlista==undefined) opcionlista="";
                if(cadenabusqueda==undefined) cadenabusqueda="";

                var x = cadenabusqueda.split("query");
                var cadena = "";
                if(x.length<2) {
                    cadena = page_request+"query/"+texto+"/"+cadenabusqueda;
                } else {
                    var i=0;
                    x = cadenabusqueda.split("/");
                    for (i=0;i<x.length;i++) {
                        if(x[i]=="query"){
                            x[i+1]=texto;
                        }
                        cadena+=x[i]+"/";
                    }
                    cadena = page_request+cadena;
                }
                //ajax
                $.ajax({
                        type: "POST",
                        url: cadena,
                        data: "&ord="+ord+"&col="+col+"&listaropcion="+opcionlista+"&tipo=query",
                        dataType: "html",
                        scriptCharset: "utf-8" ,
                        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                        success: function(msg) {
                            $(contenido).removeClass("loading");
                            $(contenido).html(msg);
                            $("#fWordRS").removeAttr("disabled");
                        }
                });
            });


            //caja de texto del buscador
            $("#fWordRS").bind("keydown",function(e){
                var key = e.keyCode || e.charCode || e.which || window.e ;
                if(key==13) {
                    $("#fSendRS").click();
                    return false;
                }
            });
        },
        selecTable : function(tabla){
            //para las filas
            $(tabla+" td input[type=checkbox]").live("change", function() {
                    var clase = $(this).parents("tr").attr("class");
                    if(clase==undefined || clase=="" || clase=="pintarLeidos" || clase=="pintarNoLeidos") {
                            $(this).parents("tr").addClass("pintar");
                    } else {
                            $(this).parents("tr").removeClass("pintar");
            		}
            });
            //para el header
            $(tabla+" thead input[type=checkbox]").live("change", function() {  
                    var objeto = $(this);
                    var checkboxes = objeto.parents("thead").next().find("td input[type=checkbox]");
                    if(!objeto.is(':checked')) {
                            objeto.parents("thead").next().find("tr").removeClass("pintar");
                            //recorremos y removemos y agregamos sin checked
                            $.each(checkboxes, function(index, item) {
                                    var id = $(item).attr("id");
                                    var relPos = $(item).attr("relpos");
                                    //var content = $(objeto).parents("thead").next().find("td input[id="+id+"]").parent(); //Por Id
                                    var content = $(objeto).parents("thead").next().find("td input[relpos="+relPos+"]").parent(); //por relPos
                                    $(item).remove();
                                    var cb = "<input type='checkbox' class='noBdr' name='select' id='"+id+"' relpos='"+relPos+"'>";
                                    $(content).html(cb);
                            });
                    } else {
                            objeto.parents("thead").next().find("tr").addClass("pintar");
                            //recorremos y removemos y agregamos con checked
                            $.each(checkboxes, function(index, item) {
                                    var id = $(item).attr("id");
                                    var relPos = $(item).attr("relpos");
                                    //var content = $(objeto).parents("thead").next().find("td input[id="+id+"]").parent(); //Por Id
                                    var content = $(objeto).parents("thead").next().find("td input[relpos="+relPos+"]").parent(); //por relPos
                                    $(item).remove();
                                    var cb = "<input type='checkbox' class='noBdr' checked='checked' name='select' id='"+id+"' relpos='"+relPos+"'>";
                                    $(content).html(cb);
                            });
                    }
            });
        },
        invitarVentana : function(a){
            var actual = $(a);
            actual.bind("click", function(e){
                e.preventDefault();
                var arreglo_valores =[];
                var act = $(this);
                var valores = $("#dataProcesoBusqueda tbody .data0").find("input:checked");
                $.each(valores, function(index,item) {
                        var v = $(item).attr("id");
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
                                    act.removeClass("winModal");
                                }
                        });
                    }
                } else {
                    buscador.mostrarMensaje("#mensajesBuscador","error","Debe seleccionar al menos un postulante");
                }
            });


            $("#aviso").live("change",function(e){
                e.preventDefault();
                var valor = $(this).val();
                var contenido = $("#avisoFileCnt");
                if(valor!="none"){
                    contenido.addClass("hide");
                    $("#cargandoInvitaBusqueda").removeClass("hide");
                    $.ajax({
                        type: "POST",
                        url: "/empresa/buscador-aptitus/mostrar-proceso",
                        data: {idaviso : valor, tok : $('#tok', '#content-winRegistrarInvitacionBuscador').val()},
                        dataType: "html",
                        success: function(msg) {
                            $("#cargandoInvitaBusqueda").addClass("hide");
                            contenido.removeClass("loading");
                            contenido.removeClass("hide");
                            contenido.html(msg);
                            var height = $("#cntModalAEmp").height();
                            if(height>300){
                                $("#cntModalAEmp").addClass("overflowInv");
                            }
                        }
                    });
                } else {
                    contenido.addClass("hide");
                }
            });

            //boton Invitar
            $(".invBtnProc").live("click",function(){
               var valor = $("#aviso").val();
               if(valor!="none"){
                    var token = $('#tok', '#content-winRegistrarInvitacionBuscador').val();
                   $("#content-winRegistrarInvitacionBuscador").html("");
                   $("#content-winRegistrarInvitacionBuscador").addClass("loading");
                  //INVITA A PROCESO
                    var arreglo_valores =[];
                    var valores = $("#dataProcesoBusqueda tbody .data0").find("input:checked");
                    $.each(valores, function(index,item) {
                            var v = $(item).attr("id");
                            arreglo_valores.push(v);
                    });
                    var cantValores = arreglo_valores.length;
                    var json = {
                            "idaviso":valor,
                            "postulantes":arreglo_valores,
                            tok: token
                    };
                    $.ajax({
                        type: "POST",
                        url: "/empresa/buscador-aptitus/invitar-postulante",
                        data: json,
                        dataType: "html",
                        success: function(msg) {
                            var result = msg.split("|");
                            $(".closeWM").trigger("click");
                            if($.trim(result[0])=="OK"){
                            	if (cantValores == 1 ) {
                            		buscador.mostrarMensaje("#mensajesBuscador","info","La invitación fue enviada correctamente.");
                            	} else {
                            		buscador.mostrarMensaje("#mensajesBuscador","info","Las invitaciones fueron enviadas correctamente.");
                            	}
                            } else {
                            	buscador.mostrarMensaje("#mensajesBuscador","error",result[1]);
                            }
                        }
                    });
               } else {
                   buscador.mostrarMensaje("#mensajesVerProceso","error","Seleccione al menos un Aviso");
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
                        },1500);
                });
        },
        //Compartir
        fMail : function(a,good,bad,def) {
                $(a).focus();
                $(a).bind('blur', function() {
                        var t = $(this),
                        r = t.next(vars.rs),
                        ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
                        if(ep.test(t.val())&& t.val()!='') {
                                r.removeClass('bad').addClass('good').text(good);
                                t.addClass(vars.okR);
                        } else {
                                r.removeClass('good').addClass('bad').text(bad);
                                t.removeClass(vars.okR);
                        }
                        return false;
                });
        },
        fInput : function(a,good,bad,def) {
                var A = $(a),
                r = A.next(vars.rs);
                A.blur( function() {
                        var t = $(this);
                        if(t.val().length>0) {
                                r.removeClass('bad').addClass('good').text(good);
                                t.addClass(vars.okR);
                        } else {
                                r.removeClass('good').addClass('bad').text(bad);
                                t.removeClass(vars.okR);
                        }
                }).keyup( function() {
                        var t = $(this);
                        if(t.val().length===0) {
                                r.removeClass('good').addClass('bad').text(bad);
                                t.removeClass(vars.okR);
                        } else {
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
                                                if(res.status == 'ok') {
                                                        B.addClass('hide');
                                                        $('#loadMFF').remove();
                                                        frm.append('<div id="loadMFF" class="block"><div class="good msjLoadMFF">' + res.msg + '</div></div>');
                                                        buscador._clearFields(frm);
                                                } else {
                                                        B.addClass('hide');
                                                        $('#loadMFF').remove();
                                                        frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
                                                        buscador._clearFields(frm);
                                                }
                                        },
                                        'error' : function(res) {
                                                var frm = $('#iEscpF');
                                                B.addClass('hide');
                                                $('loadMFF').remove();
                                                frm.append('<div id="loadMFF" class="block"><div class="bad msjLoadMFF">' + msgs.mailError + '</div></div>');
                                                buscador._clearFields(frm);
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
                        return false;
                });
        },
        resetBtn : function(btnReset, chars) {
                var btnR = $(btnReset);
                btnR.click( function() {
                        var t = $(this),
                        frm = t.parents('form');
                        buscador._clearFields(frm);
                        $('#nCaracterP').text(chars);
                        return false;
                });
        },
        _clearFields : function(frm) {
                frm.find('.inputReset').val('').removeClass('ready').siblings('.response').text('').removeClass('good bad').addClass('def');
        },
        resetBtnClose : function(btnCloseReset, chars) {
                var btnRC = $(btnCloseReset);
                btnRC.click( function() {
                        var t = $(this),
                        frm = $('#formShareCA');
                        buscador._clearFields(frm);
                        frm.removeClass('hide');
                        $('#loadMFF').remove();
                        $('#nCaracterP').text(chars);
                        return false;
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
                        var key = e.which;
                        var length = t.val().length;
                        countN.html( (chars - length) + ' ' );
                        if( length > chars ) {
                                valueArea = t.val().substring(chars, '') ;
                                trigger.val(valueArea);
                                countN.html('0');
                        }
                });
        },
        openCloseI : function(){
                var triggerS = $('.openCloseL'),
                viewMore = 'Ver todos',
                viewLess = 'Ver menos';
                triggerS.click(function(e){
                        e.preventDefault();
                        var t = $(this),
                        spped = 'meddium',
                        pos = t.parents('.iBlockMSA').offset().top,
                        paren = t.parents('.asideSA').siblings('.cntCloseIt');

                        if(t.hasClass('openIt')){
                                paren.slideUp(spped,
                                        function(){
                                                t.text(viewMore);
                                        });
                                t.removeClass('openIt');
                                $('html, body').animate({scrollTop:pos}, spped);
                        }else{
                                t.addClass('openIt');
                                paren.slideDown(spped,
                                        function(){
                                                t.text(viewLess);
                                        });
                        }
                });
        },
        listaropciones : function(a) {
            var boton = $(a);
            var contenido = "#contenido_ajax";

            boton.bind("click", function(e) {
                e.preventDefault();
                $(a+" a").removeAttr("style");
                $(a+" a").attr("href","#");

                e.preventDefault();
                var flag = $(this).attr("rel");

                $(this).find("a").css("font-weight","bold");
                $(this).find("a").css("text-decoration","none");
                $(this).find("a").removeAttr("href");

                var pagina = $("#dataProcesoBusquedaCandi").attr("page");
                var idanuncio = $("#dataProcesoBusquedaCandi").attr("relIdA");
                var categoria = $("#dataProcesoBusquedaCandi").attr("categoria");
                var ord = $("#dataProcesoBusquedaCandi").attr("ord");
                var col = $("#dataProcesoBusquedaCandi").attr("col");
                var cadenabusqueda = $("#dataProcesoBusquedaCandi").attr("cadenabusqueda");

                var json = {
                    "page":pagina,
                    "id":idanuncio,
                    "ord":ord,
                    "col":col,
                    "categoria":categoria,
                    "listaropcion" : flag
                };
                $(contenido).html("");
                $(contenido).addClass("loading");
                $.ajax({
                    type: "POST",
                    url: "/empresa/mis-procesos/detalle-candidatos-ajax/"+cadenabusqueda,
                    data: json,
                    dataType: "html",
                    success: function(msg) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(msg);
                        var nombreL="";
                        switch(flag) {
                            case "-1":
                                nombreL="Todos";
                                break;
                            case "0":
                                nombreL="Leídos";
                                break;
                            case "1":
                                nombreL="No leídos";
                                break;
                        }
                    //verproceso.mostrarMensaje("#mensajesVerProceso","info","Listado "+nombreL+" Correctamente");
                    }
                });
            });
        },
        start : function() {
            var idnivelpuesto = $("#busquedaAnterior").attr("idnivelpuesto");
            var idarea = $("#busquedaAnterior").attr("idarea");
            var idpostulacion = $.trim($("#busquedaAnterior").html());
            
            if(idpostulacion!="" && idpostulacion!=undefined) {
                page_request = page_request+"idanuncio/"+idpostulacion+"/";
            }
                
            if(idnivelpuesto!="" && idnivelpuesto!=undefined)  
                page_request = page_request+"idnivelpuesto/"+idnivelpuesto+"/";
            if(idarea!="" && idarea!=undefined)
                page_request = page_request+"idarea/"+idarea+"/";


            buscador.backToProcess();
            buscador.verPerfilFilPerfP();

            buscador.selecTable("#dataProcesoBusquedaCandi");
            //buscador.paginado(".paginador .itemPag a[href]");
            buscador.ordenamiento("#dataProcesoBusqueda thead th a");
            buscador.buscadordeempresa("a[class=aFilterB]",".checkbuscador");
            buscador.invitarVentana("#invitarBusqueda");
            buscador.listaropciones(".liListOptE");

            // ENVIAR MENSAJE
            //compartir
            $("#btnEnviarMail").live("click", function(){
                var href = $(this).attr("href");
                $("#formShareCA").removeClass("hide");
                $("#loadMFF").remove();
            });
            buscador.fMail('#fCAMail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
            buscador.fMail('#fCAMailDes',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
            buscador.fInput('#fCAName',msgs.cName.good,msgs.cName.bad,msgs.cName.def);
            buscador.fInput('#fCANameDes',msgs.cName.good,msgs.cName.bad2,msgs.cName.def);
            //perfilPost.fAreaQ('.questionI',msgs.cQuestions.good,msgs.cQuestions.bad,msgs.cQuestions.def);
            buscador.fMail('#fEmail',msgs.cEmail.good,msgs.cEmail.bad,msgs.cEmail.def);
            buscador.fSubmit('#fSendCA','#formShareCA',4,'#fCAName','#fCAMail','#fCANameDes','#fCAMailDes','#fCACustomMsg');
            buscador.resetBtn('.resetBtn', 300);
            buscador.resetBtnClose('.resetBtnClose', 300);
            buscador.charArea('#fCACustomMsg','#nCaracterP',300);
            buscador.openCloseI();
            
            if(idpostulacion!="" && idpostulacion!=undefined) {
                        $("#fSendRS").trigger("click");
            }

        }
        
    };

    buscador.start();

    //Clase para manejo de n arreglos y generar Urls
    function Arreglo(n) {
        var me = this;
        var A = new Array(n);

        me.crear = function(nombre) {
            if((typeof A[nombre])=="undefined")
                A[nombre] = new Array();
        };
        me.push = function(nombre, valor) {
            A[nombre].push(valor);
        };
        me.remove = function(nombre, valor) {
            for(i=0;i<A[nombre].length;i++){
                if(A[nombre][i]==valor)
                     A[nombre].splice(i,1);
            }

        };
        me.getAsParam = function(nombre, separador) {
            return A[nombre].join(separador);
        };
        me.rutaFinal = function(separador) {
            var cadena="";
            for(var clave in A){
                cadena+="/"+clave+"/"+me.getAsParam(clave, separador);
            }
            return cadena;
        };
        me.listar = function(nombre) {
            var i=0;
           for(i=0;i<A[nombre].length;i++){
           }
        };
    }

});
