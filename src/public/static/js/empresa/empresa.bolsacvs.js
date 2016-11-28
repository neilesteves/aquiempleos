/*
 mis procesos
 */
$(function() {
    var page_request = "/empresa/bolsa-cvs/filtro-ajax/";
    var dataI = {
        speed1: 'slow',
        speed2: 'fast'
    };
    var msgErrors = {
        email: {
            empty: 'Ingrese un Email',
            bad: 'No parece ser un Email Válido',
            good: '¡Correcto!'
        },
        nombre: {
            bad: 'Ingrese un Nombre',
            good: '¡Correcto!'
        },
        apellidos: {
            bad: 'Ingrese Apellidos',
            good: '¡Correcto!'
        },
        telefono: {
            empty: 'Ingrese Telefono',
            bad: 'Ingrese solo números',
            good: '¡Correcto!'
        },
        cv: {
            bad: 'Suba un CV',
            good: '¡Correcto!'
        }
    };
    var idpos = undefined;
    var eliminarDeGrupo = false;

    var bolsacvs = {
        dropDown: function(clickAB, listDiv) {
                var clickA = $(clickAB),
                        postT = clickA.position().top,
                        postL = clickA.position().left,
                        heightA = clickA.innerHeight(),
                        listA = $(listDiv),
                        speed = 'fast';

                
                $(document).on('click', clickAB, function(e) {
                    e.preventDefault();
                    var t = $(this),
                            flechaA = t.find('.flechaGrisT'),
                            listA = $(listDiv);

                    listA.css({
                        'left': postL,
                        'top': postT + heightA - 2
                    });
                    if (t.hasClass('openFl')) {
                        listA.slideUp(speed);
                        t.removeClass('openFl');
                        flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
                    } else {
                        listA.slideDown(speed);
                        t.addClass('openFl');
                        flechaA.addClass('downFlechaEP').removeClass('upFlechaEP');
                    }

                    $(document).bind('mouseup', function(e) {
                        if ($(e.target).parent(clickAB).length == 0) {
                            e.preventDefault();
                            flechaA = t.find('.flechaGrisT');
                            listA.slideUp('fast');
                            t.removeClass('openFl');
                            flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
                        }
                    });
                });
        },
        clickAccion: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var opcion = $(this).attr("rel");

                var idspostulantes = $("#dataBolsaCVs tbody .data0").find("input:checked");
                if (idspostulantes.length < 1) {
                    $(".closeWM").click();
                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", "Debe Seleccionar al menos un postulante.");
                } else {
                    var arregloIds = [];
                    var id = 0;
                    $.each(idspostulantes, function(index, item) {
                        id = $(item).attr("rel");
                        arregloIds.push(id);
                    });
                    switch (opcion) {
                        case "0":

                            idpos = undefined;
                            eliminarDeGrupo = false;
                            var link = $(this).attr("name");
                            if ($(this).attr("href") == "") {
                                $("#divBolsasMover").html('');
                                $("#titleMoverPostulante").html('Copiar Postulantes');
                                $("#spMPLabel").html('Copiar los postulantes');
                                $("#spMPNombrePostulante").html('');
                                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");

                                $("#" + link).addClass("winModal");
                                $("#" + link).attr("href", "#winMoverBolsa");
                                bolsacvs.traerGrupos("#divBolsasMover", undefined, idgrupo, link, arregloIds);
                            } else {

                                $("#" + link).removeClass("winModal");
                                $("#" + link).attr("href", "");
                            }
                            break;

                        case "1":

                            idpos = undefined;
                            eliminarDeGrupo = true;
                            var link = $(this).attr("name");
                            if ($(this).attr("href") == "") {
                                $("#divBolsasMover").html('');
                                $("#titleMoverPostulante").html('Mover Postulantes');
                                $("#spMPLabel").html('Mover los postulantes');
                                $("#spMPNombrePostulante").html('');
                                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");

                                $("#" + link).addClass("winModal");
                                $("#" + link).attr("href", "#winMoverBolsa");
                                bolsacvs.traerGrupos("#divBolsasMover", undefined, idgrupo, link, arregloIds);
                            } else {
                                $("#" + link).removeClass("winModal");
                                $("#" + link).attr("href", "");
                            }
                            break;

                        case "2":
                            idpos = undefined;
                            $("#spQPLabel").html('¿Está seguro que desea quitar los postulantes seleccionados');
                            $("#spQPNombrePostulante").html('');
                            break;
                    }
                }
            });
        },
        ordenamiento: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var ord = $(this).attr("ord");
                var col = $(this).attr("col");
                var pagina = $("#dataBolsaCVs").attr("page");
                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");
                bolsacvs.traerDataTabla(pagina, idgrupo, ord, col);
            });
        },
        editarGrupoClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                document.getElementById("txtNameEditGroup").value = $("#spNameGroup").html();

            });
        },
        eliminarGrupoClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                //hide
                //divEnviaBolsa
                var postulantes = $("#dataBolsaCVs tbody .data0");
                if (postulantes.length > 0) {
                    if ($("#divEnviaBolsa").hasClass("hide")) {
                        $("#divEnviaBolsa").removeClass("hide");
                    }
                } else {
                    $("#divEnviaBolsa").addClass("hide");
                }
                $("#spEPNombreGrupo").html($("#spNameGroup").html());
            });
        },
        cambiarNombreGrupo: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var nombre = document.getElementById("txtNameEditGroup").value;
                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");

                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/modificar-grupo",
                    data: "nombre=" + nombre + "&idGrupo=" + idgrupo,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            bolsacvs.traerGruposEmpresa();
                            document.getElementById("txtNameEditGroup").value = "";
                            $("#spNameGroup").html(nombre);
                        } else {
                            $("#msgErrorEditarGrupo").html("* " + msg.msg);
                            $("#msgErrorEditarGrupo").removeClass('hide');
                            setTimeout(function() {
                                $("#msgErrorEditarGrupo").html("*");
                                $("#msgErrorEditarGrupo").addClass("hide");
                            }, 3000);
                        }
                    }
                });
            });
        },
        eliminarGrupo: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var salvarCvs = $("#chkCopyG").prop("checked");
                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");
                $(".closeWM").click();
                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/eliminar-grupo",
                    data: "salvarCvs=" + salvarCvs + "&idGrupo=" + idgrupo,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            setTimeout(function() {
                                document.location = "/empresa/bolsa-cvs";
                            }, 1500
                                    );
                        } else {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });

            });
        },
        openAnadirGrupo: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                document.getElementById("txtNameGroup").value = "";
                $("#txtNameGroup").focus();
                $(this).focus();
            });
        },
        agregarGrupo: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                
                $.ajax({
                        type: "POST",
                        url: "/registro/obtener-token/",                        
                        dataType: "json",                    
                        success: function(d) {
                            
                            var nombre = document.getElementById("txtNameGroup").value;
                            var token = d;
                            var alt = $("#txtNameGroup").attr('alt');
                            if (nombre == alt) {
                                $("#msgErrorAgregarGrupo").html("* Debe escribir un nombre de grupo.");
                                $("#msgErrorAgregarGrupo").removeClass('hide');
                                setTimeout(function() {
                                    $("#msgErrorAgregarGrupo").html("*");
                                    $("#msgErrorAgregarGrupo").addClass("hide");
                                }, 3000);
                                return;
                            }
                            $.ajax({
                                type: "POST",
                                url: "/empresa/bolsa-cvs/agregar-grupo",
                                data: "nombre=" + nombre + "&tok_bolsa_grp="  + token,
                                dataType: "json",                    
                                success: function(msg) {
                                    if (typeof(msg.tok) != 'undefined') {
                                        $('#tok_bolsa_grp').val(msg.tok);
                                    }                        
                                    if (msg.status == 'ok') {
                                        $(".closeWM").click();
                                        bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                                        bolsacvs.traerGruposEmpresa();
                                        document.getElementById("txtNameGroup").value = "";
                                    } else {
                                        $("#msgErrorAgregarGrupo").html("* " + msg.msg);
                                        $("#msgErrorAgregarGrupo").removeClass('hide');
                                        setTimeout(function() {
                                            $("#msgErrorAgregarGrupo").html("*");
                                            $("#msgErrorAgregarGrupo").addClass("hide");
                                        }, 3000);
                                    }
                                }
                            });
                            
                        }
                });
                
               
               
            });
        },
        lnkAgregarPostulanteClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var idpostulante = $(this).attr("rel");
                idpos = idpostulante;
                var contenido = "#divBolsasEnviar";
                var nombre = $(this).parents("tr").find(".verPerfilFilPerfP_Nombre").html();

                $("#spEPLabel").html('Enviar al postulante');
                $("#spEPNombrePostulante").html(nombre);
                if (!bolsacvs.traerGrupos(contenido, idpostulante)) {
                    $(".closeWM").click();
                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "info", "El postulante ya se encuentra agregado en todos los grupos.");
                }
            });
        },
        lnkMoverPostulanteClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var idpostulante = $(this).attr("rel");
                idpos = idpostulante;
                eliminarDeGrupo = true;
                var link = $(this).attr("name");
                if ($(this).attr("href") == "") {
                    var contenido = "#divBolsasMover";
                    $("#titleMoverPostulante").html('Mover Postulante');
                    $("#spMPLabel").html('Mover al postulante');
                    var idgrupo = $("#dataBolsaCVs").attr("idgrupo");

                    var nombre = $("#lnkNombrePostulante" + idpostulante).html();
                    if (nombre == undefined || nombre == null) {
                        nombre = $("#spanPostulante").html();
                    }

                    $("#spMPNombrePostulante").html('"' + nombre + '"');
                    $("#" + link).addClass("winModal");
                    $("#" + link).attr("href", "#winMoverBolsa");
                    bolsacvs.traerGrupos(contenido, idpostulante, idgrupo, link);

                } else {
                    $("#" + link).removeClass("winModal");
                    $("#" + link).attr("href", "");
                }
            });
        },
        lnkCopiarPostulanteClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var idpostulante = $(this).attr("rel");
                idpos = idpostulante;
                eliminarDeGrupo = false;
                var link = $(this).attr("name");
                if ($(this).attr("href") == "") {
                    var contenido = "#divBolsasMover";
                    $("#titleMoverPostulante").html('Copiar Postulante');
                    $("#spMPLabel").html('Copiar al postulante');
                    var idgrupo = $("#dataBolsaCVs").attr("idgrupo");

                    var nombre = $("#lnkNombrePostulante" + idpostulante).html();
                    if (nombre == undefined || nombre == null) {
                        nombre = $("#spanPostulante").html();
                    }
                    $("#spMPNombrePostulante").html('"' + nombre + '"');
                    $("#" + link).addClass("winModal");
                    $("#" + link).attr("href", "#winMoverBolsa");

                    bolsacvs.traerGrupos(contenido, idpostulante, idgrupo, link);
                } else {
                    $("#" + link).removeClass("winModal");
                    $("#" + link).attr("href", "");
                }
            });
        },
        lnkQuitarPostulanteClick: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var idpostulante = $(this).attr("rel");
                idpos = idpostulante;
                $("#spQPLabel").html('¿Está seguro que desea quitar al postulante');

                var nombre = $("#lnkNombrePostulante" + idpostulante).html();
                if (nombre == undefined || nombre == null) {
                    nombre = $("#spanPostulante").html();
                }

                $("#spQPNombrePostulante").html('"' + nombre + '"');
            });
        },
        moverPostulante: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var idGrupoActual = undefined;
                if (eliminarDeGrupo) {
                    idGrupoActual = $("#dataBolsaCVs").attr("idgrupo");
                }
                var idsgrupos = $(".chkOpMPB").find("input:checked");
                var arreglo_idsgrupos = [];
                var id = 0;
                if (idsgrupos.length <= 0) {
                    $("#msgErrorNotasMov").html("* Debe seleccionar al menos un grupo.");
                    $("#msgErrorNotasMov").removeClass('hide');
                    setTimeout(function() {
                        $("#msgErrorNotasMov").html("*");
                        $("#msgErrorNotasMov").addClass("hide");
                    }, 3000);
                    return;
                }
                $.each(idsgrupos, function(index, item) {
                    id = $(item).attr("rel");
                    arreglo_idsgrupos.push(id);
                });
                var idspostulantes = $("#dataBolsaCVs tbody .data0").find("input:checked");
                var arreglo_idspostulantes = [];
                var id = 0;
                if (idpos == undefined) {
                    $.each(idspostulantes, function(index, item) {
                        id = $(item).attr("rel");
                        arreglo_idspostulantes.push(id);
                    });
                } else {
                    arreglo_idspostulantes.push(idpos);
                }
                var json = {
                    "idGrupoOrigen": idGrupoActual,
                    "idsPostulantes": arreglo_idspostulantes,
                    "idsGruposDestino": arreglo_idsgrupos
                };
                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/mover-postulante",
                    data: json,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            var contenido = "#dvGruposEmpresa";
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            bolsacvs.traerGruposEmpresa();
                            var ord = $("#dataBolsaCVs").attr("ord");
                            var col = $("#dataBolsaCVs").attr("col");
                            var pagina = $("#dataBolsaCVs").attr("page");
                            var idgrupo = $("#dataBolsaCVs").attr("idgrupo");
                            bolsacvs.traerDataTabla(pagina, idgrupo, ord, col);
                        } else {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });
            });
        },
        eliminarNotaBolsa: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var str = $(this).attr('rel').split('#');
                var idNota = str[0];
                var div = str[1];

                var json = {
                    "idNota": idNota
                };

                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/eliminar-nota-bolsa",
                    data: json,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            //efecto quitar nota
                        } else {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });
            });
        },
        guardarEdicionNota: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var idNota = $(this).attr("rel");

                var texto = document.getElementById("txtNota" + idNota).value;


                var json = {
                    "idNota": idNota,
                    "nota": texto
                };

                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/editar-nota-bolsa",
                    data: json,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            if (!$(this).hasClass("notaVistaPerfil")) {
                                var idPostulante = $("#btnAddNoteEPL").attr("rel");
                                $.ajax({
                                    type: "POST",
                                    url: "/empresa/bolsa-cvs/get-vista-nota-bolsa",
                                    data: "idPostulante=" + idPostulante + "&idNota=" + idNota,
                                    dataType: "json",
                                    success: function(msg) {
                                        //efecto cambiar nota
                                    }
                                });
                            } else {
                                $(".closeWM").click();
                            }
                        } else {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });
            });
        },
        quitarPostulante: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var idGrupoActual = $("#dataBolsaCVs").attr("idgrupo");

                var idsgrupos = $(".chkOpMPB").find("input:checked");
                var arreglo_idsgrupos = [];
                var id = 0;

                var idspostulantes = $("#dataBolsaCVs tbody .data0").find("input:checked");
                var arreglo_idspostulantes = [];
                var id = 0;
                if (idpos == undefined) {
                    $.each(idspostulantes, function(index, item) {
                        id = $(item).attr("rel");
                        arreglo_idspostulantes.push(id);
                    });
                } else {
                    arreglo_idspostulantes.push(idpos);
                }

                var json = {
                    "idGrupoOrigen": idGrupoActual,
                    "idsPostulantes": arreglo_idspostulantes
                };
                $(".closeWM").click();
                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/eliminar-postulante",
                    data: json,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            var contenido = "#dvGruposEmpresa";
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            bolsacvs.traerGruposEmpresa();
                            var ord = $("#dataBolsaCVs").attr("ord");
                            var col = $("#dataBolsaCVs").attr("col");
                            var pagina = $("#dataBolsaCVs").attr("page");
                            var idgrupo = $("#dataBolsaCVs").attr("idgrupo");
                            bolsacvs.traerDataTabla(pagina, idgrupo, ord, col);
                        } else {
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });
            });
        },
        agregarPostulante: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var idGrupoActual = $("#dataBolsaCVs").attr("idgrupo");
                var idpostulante = $("#dataProcesoPostulacion").attr("page");

                var idgrupos = $(".chkOpMPB").find("input:checked");
                var arreglo_valores = [];
                var id = 0;
                $.each(idgrupos, function(index, item) {
                    id = $(item).attr("rel");
                    arreglo_valores.push(id);
                });
            });
        },
        anadirNotasBolsa: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var idverproceso = $(this).attr("href");
                var idpostulante = $(this).attr("rel");
                idpos = idpostulante;

                var contenido = "#content-" + idverproceso.substr(1, idverproceso.length);
                $(contenido).html("");
                $(contenido).addClass("loading");

                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/anadir-notas-bolsa",
                    data: "",
                    dataType: "html",
                    success: function(msg) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(msg);

                        var nombre = $("#lnkNombrePostulante" + idpostulante).html();
                        if (nombre == undefined || nombre == null) {
                            nombre = $("#spanPostulante").html();
                        }

                        $("#spNotaNombrePostulante").html(nombre);
                        bolsacvs.fNoArroba('#textNotaBolsa');
                    }
                });
            });
        },
        agregarNotasBolsa: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();

                var nota = "";
                var idPostulante = "0";

                if ($(this).hasClass("notaVistaPerfil")) {
                    idPostulante = $("#btnAddNoteEPL").attr("rel");
                    nota = document.getElementById("text").value;
                } else {
                    nota = document.getElementById("textNotaBolsa").value;
                    idPostulante = idpos;
                }


                $.ajax({
                    type: "POST",
                    url: "/empresa/bolsa-cvs/registrar-notas-bolsa",
                    data: "nota=" + nota + "&idPostulante=" + idPostulante,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.status == 'ok') {
                            $(".closeWM").click();
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
                            if ($(this).hasClass("notaVistaPerfil")) {
                                //cargar nota
                                $.ajax({
                                    type: "POST",
                                    url: "/empresa/bolsa-cvs/get-vista-nota-bolsa",
                                    data: "idPostulante=" + idPostulante,
                                    dataType: "json",
                                    success: function(msg) {
                                        //efecto add nota
                                    }
                                });
                            }

                        } else {
                            bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
                        }
                    }
                });
            });
        },
        verPerfilFilPerfP: function() {
            $('.verPerfilFilPerfP_Nombre').live('click', function(e) {
                e.preventDefault();
                $(this).parents("td").find(".verPerfilFilPerfP").trigger("click");
            });
            $('.verPerfilFilPerfP_Imagen').live('click', function(e) {
                e.preventDefault();
                $(this).parents("tr").find(".data3 .verPerfilFilPerfP").trigger("click");
            });
            $('.verPerfilFilPerfP').live('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $a = $(this);
                $a.parents("tr").removeAttr("class").addClass("pintarLeidos");
                var ajaxCnt = $('#ajax-loading');
                $('#innerMain').slideUp(dataI.speed1, function() {
                    ajaxCnt.slideDown(dataI.speed1);
                    var loadCache = false;
                    var idGrupoActual = $("#dataBolsaCVs").attr("idgrupo");
                    if (!loadCache) {
                        loadCache = true;
                        $.ajax({
                            type: 'get',
                            url: '/empresa/bolsa-cvs/perfil-postulante',
                            data: {
                                id: $a.attr('rel'), idGrupo: idGrupoActual
                            },
                            success: function(response) {
                                ajaxCnt.slideUp(dataI.speed1);
                                $('#innerMain').parent().append('<div id="perfilContainer" class="all"></div>');

                                $('#perfilContainer').html(response);

                                $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
                                $('#perfilContainer').slideDown(dataI.speed1);

                                //Load JS perfil
                                AptitusPerfil();
                                if ($('#sidebarEPL').hasClass('noteBagCVS')) {
                                    //Bolsa de Cvs
                                    AptitusBolsaCV();
                                }

                                loadCache = false;
                            },
                            dataType: 'html'
                        });
                    }
                });
                return false;
            });
        },
        backToProcess: function() {
            $('#backToProcess').live('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var cntPerfilC = $('#perfilContainer');
                cntPerfilC.slideUp(dataI.speed1, function() {
                    cntPerfilC.detach();
                    $('#innerMain').slideDown(dataI.speed1);

                });

                //die events
                $('.attachEPL').die();
                $('.inputAttachEPL').die();
                $('.icoCloseMsjAdj').die();
                $('.dataBtnEP').die();
                $('.closeAdjInP').die();
                $('.aBtnIn').die();
                $('.editEPL').die();
                $('.deleteEPL').die();
                $('#winAlert a.yesCM').die();
                //Agregando Nota y Msj
                $('#btnAddNoteEPL').die();
                $('#btnAddMsjEPL').die();
                $('.icoCloseMsjD').die();
                $('#sendMsjEPA').die();
                $('.winAlertM').die();

                if ($('#sidebarEPL').hasClass('noteBagCVS')) {
                    //Bolsa de Cvs                                
                    $('.dataBtnEPBolsa').die();
                    $('.btnEditNota').die();
                    $('#winAlertBCV .yesCM').die();
                    $('.deleteEPLBCV').die();
                }

            });
        },
        invitarVentana: function(a) {
            var actual = $(a);
            actual.off();
            actual.on("click", function(e) {
                e.preventDefault();
                var arreglo_valores = [];
                var act = $(this);
                var valores = $("#dataBolsaCVs tbody .data0").find("input:checked");
                $.each(valores, function(index, item) {
                    var v = $(item).attr("rel");
                    arreglo_valores.push(v);
                });
                if (arreglo_valores.length > 0) {
                    if (!act.hasClass("winModal")) {
                        act.addClass("winModal");
                        $(a).click();
                    } else {
                        var contenido = "#content-" + act.attr("href").substr(1, act.attr("href").length);

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
                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", "Debe seleccionar al menos un postulante.");
                }
            });


            $("#aviso").live("change", function(e) {
                e.preventDefault();
                var valor = $(this).val();
                var contenido = $("#avisoFileCnt");
                if (valor != "none") {
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
                            if (height > 300) {
                                $("#cntModalAEmp").addClass("overflowInv");
                            }
                        }
                    });
                } else {
                    contenido.addClass("hide");
                }
            });

            //boton Invitar
            $(".invBtnProc").live("click", function() {
                var valor = $("#aviso").val();
                if (valor != "none") {
                    var token = $('#tok', '#content-winRegistrarInvitacionBuscador').val();
                    $("#content-winRegistrarInvitacionBuscador").html("");
                    $("#content-winRegistrarInvitacionBuscador").addClass("loading");
                    //INVITA A PROCESO
                    var arreglo_valores = [];
                    var valores = $("#dataBolsaCVs tbody .data0").find("input:checked");
                    $.each(valores, function(index, item) {
                        var v = $(item).attr("rel");
                        arreglo_valores.push(v);
                    });
                    var cantValores = arreglo_valores.length;
                    var json = {
                        "idaviso": valor,
                        "postulantes": arreglo_valores,
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
                            if ($.trim(result[0]) == "OK") {
                                if (cantValores == 1) {
                                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "info", "Se ha enviado la invitacion de manera correcta");
                                } else {
                                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "info", "Se han enviado las invitaciones correctamente");
                                }
                            } else {
                                bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", result[1]);
                            }
                        }
                    });
                } else {
                    bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "error", "Debe seleccionar al menos un Aviso");
                }
            });
        },
        checkGrupal: function(a) {
            $(a).live("change", function(e) {
                e.preventDefault();
                var idsgrupos = $("#dataBolsaCVs tbody .data0");

                var checked = 'checked="checked"';
                if (!$(this).is(':checked')) {
                    checked = '';
                }

                $.each(idsgrupos, function(index, item) {
                    var chk = $(item).find("input");
                    var cb = '<input type="checkbox" name="select" ' + checked + ' rel="' + $(chk).attr('rel') + '">';
                    $(item).html(cb);
                });
            });
        },
        presionarTecla: function(a) {
            $(a).keydown(function(e) {
                var keyC = e.keyCode || e.charCode || e.which || window.e;
                if (keyC == 13) {

                    if ($(this).attr("id") == "txtNameGroup") {
                        $('#btnAceptarAnadirGrupo').trigger('click');
                    }
                    if ($(this).attr("id") == "txtNameEditGroup")
                        $('#btnAceptarModificarGrupo').trigger('click');
                    return false;
                }
            });
        },
        cerrarVentana: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                $(".closeWM").click();
            });
        },
        traerGrupos: function(contenido, idpostulante, idgrupo, link, idpostulantes) {
            var data = {
                "idGrupo": idgrupo,
                "idPostulante": idpostulante,
                "idPostulantes": idpostulantes
            };

            $.ajax({
                type: "POST",
                url: "/empresa/bolsa-cvs/get-grupos",
                data: data,
                dataType: "html",
                success: function(msg) {
                    $(contenido).html(msg);
                    var cant = bolsacvs.trim(msg).length;
                    if (cant == 0 || bolsacvs.trim(msg) == "-1") {
                        if (link != undefined) {
                            $("#" + link).removeClass("winModal");
                            $("#" + link).attr("href", "");
                        }
                        $(".closeWM").click();

                        if (bolsacvs.trim(msg) == "-1") {
                            msj = "El candidato ya no se encuentra en el grupo.";
                        } else {

                            var msj = "El postulante ya se encuentra agregado en todos los grupos.";
                            if (idpostulantes != undefined) {
                                msj = "Los postulantes seleccionados ya se encuentran agregados en todos los grupos.";
                            }
                        }
                        bolsacvs.mostrarMensaje(".dvMensajesBolsaCVs", "info", msj);
                    } else {
                        if (link != undefined) {
                            $("#" + link).click();
                        }
                    }
                    //Tamanio Height
                    var cntBCVs = $('#divBolsasMover'),
                            hBCVs = cntBCVs.innerHeight();
                    cntBCVs.parents('.window').css('margin-top', '-230px');
                    if (hBCVs > 300) {
                        cntBCVs.css({
                            'width': '100%',
                            'height': '300px',
                            'overflow-x': 'hidden',
                            'overflow-y': 'scroll'
                        });
                    }
                }
            });
        },
        traerGruposEmpresa: function() {
            var contenido = "#dvGruposEmpresa";
            $.ajax({
                type: "POST",
                url: "/empresa/bolsa-cvs/grupos-empresa",
                data: "",
                dataType: "html",
                success: function(msg) {
                    $(contenido).html(msg);
                }
            });
        },
        traerDataTabla: function(pagina, idgrupo, ord, col) {
            var contenido = "#contenido_ajax";
            $.ajax({
                type: "POST",
                url: "/empresa/bolsa-cvs/contenido-ajax/",
                data: "page=" + pagina + "&id=" + idgrupo + "&ord=" + ord + "&col=" + col,
                dataType: "html",
                success: function(msg) {
                    $(contenido).html(msg);
                }
            });
        },
        mostrarMensaje: function(a, tipo, mensaje) {
            var clasetipo = "";
            switch (tipo) {
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

            if (variable.size() > 1) {
                variable = $(variable[variable.size() - 1]);
            }
            variable.html(mensaje);
            variable.removeClass("msgYellow");
            variable.removeClass("msgRed");
            variable.removeClass("msgBlue");
            variable.addClass(clasetipo);
            variable.fadeIn("meddium", function() {
                setTimeout(function() {
                    variable.addClass("hide");
                    variable.addClass("r5");
                    variable.fadeOut("meddium");
                }, 2500);
            });
        },
        trim: function(stringToTrim) {
            return stringToTrim.replace(/^\s+|\s+$/g, "");
        },
        fNoArroba: function(a) {
            return $(a).each(function() {
                var t = $(this),
                        isShift = false,
                        epAroba = /a(r{1,2})[o{1}|0{1}][b{1}|v{1}]a/gi;
                t.keypress(function(e) {
                    var key = e.keyCode || e.charCode || e.which || window.e;
                    if (key == 64) {
                        return false;
                    }
                    if (key == 18) {
                        return false;
                    }
                    var car = String.fromCharCode(e.charCode);
                    if (car == '@') {
                        return false;
                    }
                    return true;
                });
                t.keyup(function(e) {
                    var key = e.keyCode || e.charCode || e.which || window.e;
                    var valTmp = t.val();
                    if (valTmp.search(epAroba) != -1) {
                        var newValTmp = valTmp.replace(epAroba, '');
                        t.val(newValTmp);
                    }
                });
                t.bind('paste', function() {
                    setTimeout(function() {
                        var value = t.val(),
                                newValue = value.replace(/[@]/g, ''),
                                newValue = newValue.replace(epAroba, '');
                        t.val($.trim(newValue));
                    }, 0);
                });
            });
        },
        filtropostulante: function(check,actionUrl) {

            var enviroment = $(".wrap_checks"),
                alertSearch = $('#alertSearch'),
                maskSearch = $('.mask-search'),
                frmSearch = $('#fIRSearchCV'),
                isAnyCheck = null,
                actionBuscar = function(event){
                    var dataString = $.trim($('#fWordRS').val()),
                        url = "",
                        data = "";

                    $.each(enviroment,function(i,elem){
                        var errChild = $(elem).find('input:checkbox,input:radio'),
                            valData = "",
                            flag = false,
                            collection = [];
                        
                        $.each(errChild,function(i,elem){
                            var valueData = $(elem).val();
                            if($(elem).is(":checked") && $.inArray(valueData,collection) == -1){
                                flag = true;
                                valData = valData + valueData + "--";
                                collection.push(valueData);
                            }
                        });
                        url = (flag) ? '/' + $(elem).data('type') + '/' : '';
                        valData = valData.substring(0, valData.length-2);
                        data = data + url + valData;
                    });

                    enviroment.find(':checkbox').attr("disabled", true);
                    
                    if(enviroment.find(':checkbox:checked').length > 0){
                        isAnyCheck = true;
                    }else{
                        isAnyCheck = false;
                    }

                    var contenido = "#contenido_ajax";
                    $(contenido).html("");
                    $(contenido).addClass("loading");

                    var temp = {}, url;
                    var idgrupo = $("#idGrupo").val();
                    if(event.data.check){
                        temp = {
                            listaropcion: $("#dataBolsaCVs").attr("opcionlista"),
                            check: isAnyCheck,
                            tipo: undefined
                        }
                        //url: page_request + "idgrupo/" + idGrupo + "/query/" + texto + ruta,
                        //data: '&listaropcion=' + opcionlista + '&check=' + check + '&tipo=' + descripcion,
                    }else{
                        temp = {
                            ord: $("#dataProcesoPostulacion,#dataReferidos").attr("ord"),
                            col: $("#dataProcesoPostulacion,#dataReferidos").attr("col"),
                            listaropcion: $("#dataBolsaCVs").attr("opcionlista"),
                            tipo: 'query'
                        }
                        //url: page_request + "idgrupo/" + idgrupo + "/" + cadenabusqueda,
                        //data: "page=" + pagina + "&ord=" + ord + "&col=" + col + "&listaropcion=" + opcionlista,
                    }

                    //console.log(temp);
                    $.ajax({
                        type: "POST",
                        url: page_request + "idgrupo/" + idgrupo + "/query/" + dataString + data,
                        data: temp,
                        dataType: "html",
                        success: function(msg) {
                            $(contenido).removeClass("loading");
                            $(contenido).html(msg);
                            enviroment.find(':checkbox').attr("disabled", false);

                            //bolsacvs.filtropostulante('.checkN','#modalUrl');
                            bolsacvs.invitarVentana("#invitarBusqueda");
                        }
                    });
                    //window.location = '/buscar' + query + advanceSearch + data;
                    alertSearch.hide();
                    maskSearch.hide();

                    var pos = $('#wrapper').offset().top;
                    $('html,body').animate({scrollTop: pos});
                    event.preventDefault();
                },
                actionCheck = function(){                    
                    alertSearch.show();
                    maskSearch.show();
                };

            /*enviroment.off();
            alertSearch.off();
            $(actionUrl).off();
            frmSearch.off();*/
            //$('.ioption.accord').off();

            $(document).on("click",actionUrl, {check: true} ,actionBuscar);
            $(document).on('submit','#fIRSearchCV', {check: false}, actionBuscar);
            enviroment.on("change", 'input:checkbox,input:radio', actionCheck);
            $(document).on('click', '#alertSearch', function(){
                maskSearch.hide();
                $(this).addClass("hide");
            });
            $('.ioption.accord').on('click',function(){
                alertSearch.show();
                maskSearch.show();
            });
            
/*
            var A = new Arreglo(6);
            var actual = $(a);
            var check = $(b);
            actual.live("click", function(e) {
                e.preventDefault();
                var checkbox = $(this).parent().siblings(".left").find("input");
                checkbox.trigger("click");
                e.stopPropagation();
            });

            check.live("change", function() {
                var check = $(this).is(":checked");
                $("#mensajeEntradaBuscador").hide();
                $("#contenido_ajax").removeAttr("style");

                $(".checkbuscador").attr("disabled", "disabled");
                var info = $(this).attr("rel");
                var dividir = info.split("/");
                var descripcion = dividir[0];
                var valor = dividir[1];
                
                if(!$(this).parent().hasClass('accord')){
                    A.crear(descripcion);
                    if($(this).is(':checked')) {
                        A.push(descripcion,valor);
                    } else {
                        A.remove(descripcion,valor);
                    }
                }else{
                    $.each($(this).parent().next().find(':checkbox'), function(i,elem){
                        var info = $(this).attr("rel");
                        var dividir = info.split("/");
                        var descripcion = dividir[0];
                        var valor = dividir[1];
                        A.remove(descripcion,valor);
                    });
                }
                var ruta = A.rutaFinal("--");
                var opcionlista = $("#dataBolsaCVs").attr("opcionlista");
                var texto = $("#fWordRS").val();
                var idGrupo = $("#idGrupo").val();

                var contenido = "#contenido_ajax";
                $(contenido).html("");
                $(contenido).addClass("loading");
                //ajax
                $.ajax({
                    type: "POST",
                    url: page_request + "idgrupo/" + idGrupo + "/query/" + texto + ruta,
                    data: '&listaropcion=' + opcionlista + '&check=' + check + '&tipo=' + descripcion,
                    dataType: "html",
                    success: function(msg) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(msg);
                        $(".checkbuscador").removeAttr("disabled");
                    }
                });
            });

            $("#fSendRS").live("click", function(e) {
                e.preventDefault();
                $("#mensajeEntradaBuscador").hide();
                $("#contenido_ajax").removeAttr("style");

                var texto = $("#fWordRS").val();
                texto = texto.replace(/%+/g, "");
                texto = texto.replace(/á+/g, "a");
                texto = texto.replace(/é+/g, "e");
                texto = texto.replace(/í+/g, "i");
                texto = texto.replace(/ó+/g, "o");
                texto = texto.replace(/ú+/g, "u");

                $("#fWordRS").attr("disabled", "disabled");
                var ord = $("#dataBolsaCVs").attr("ord");
                var col = $("#dataBolsaCVs").attr("col");
                var opcionlista = $("#dataBolsaCVs").attr("opcionlista");
                var cadenabusqueda = $("#dataBolsaCVs").attr("cadenabusqueda");
                var idgrupo = $("#dataBolsaCVs").attr("idgrupo");
                var contenido = "#contenido_ajax";
                $(contenido).html("");
                $(contenido).addClass("loading");

                if (ord == undefined)
                    ord = "";
                if (col == undefined)
                    col = "";
                if (opcionlista == undefined)
                    opcionlista = "";
                if (cadenabusqueda == undefined)
                    cadenabusqueda = "";

                var x = cadenabusqueda.split("query");

                var cadena = "";
                if (x.length < 2) {
                    cadena = "query/" + texto + "/" + cadenabusqueda;
                } else {
                    var i = 0;
                    x = cadenabusqueda.split("/");
                    for (i = 0; i < x.length; i++) {
                        if (x[i] == "query") {
                            x[i + 1] = texto;
                        }
                        cadena += x[i] + "/";
                    }
                }
                //ajax
                $.ajax({
                    type: "POST",
                    url: page_request + "idgrupo/" + idgrupo + "/" + cadena,
                    data: "&ord=" + ord + "&col=" + col + "&listaropcion=" + opcionlista + "&tipo=query",
                    dataType: "html",
                    scriptCharset: "utf-8",
                    contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                    success: function(msg) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(msg);
                        $("#fWordRS").removeAttr("disabled");
                    }
                });
            });

            //caja de texto del buscador
            $("#fWordRS").bind("keydown", function(e) {
                var key = e.keyCode || e.charCode || e.which || window.e;
                if (key == 13) {
                    $("#fSendRS").click();
                    return false;
                }
            });*/
        },
        paginado: function(a) {
            $(a).live("click", function(e) {
                e.preventDefault();
                var pagina = $(this).attr("rel"),
                        contenido = $("#contenido_ajax"),
                        objTable = $("#dataBolsaCVs"),
                        ord = objTable.attr("ord"),
                        col = objTable.attr("col"),
                        opcionlista = objTable.attr("opcionlista"),
                        cadenabusqueda = objTable.attr("cadenabusqueda"),
                        idgrupo = objTable.attr("idgrupo");

                (pagina == undefined) ? (pagina = '') : (pagina = pagina);
                (ord == undefined) ? (ord = '') : (ord = ord);
                (col == undefined) ? (col = '') : (col = col);
                (opcionlista == undefined) ? (opcionlista = '') : (opcionlista = opcionlista);

                objTable.wrap('<div class="loading" id="loadPagTable">');
                objTable.addClass('hide');
                $.ajax({
                    type: "POST",
                    url: page_request + "idgrupo/" + idgrupo + "/" + cadenabusqueda,
                    data: "page=" + pagina + "&ord=" + ord + "&col=" + col + "&listaropcion=" + opcionlista,
                    dataType: "html",
                    success: function(msg) {
                        objTable.unwrap('<div class="loading" id="loadPagTable">');
                        objTable.removeClass('hide');
                        contenido.html(msg);
                    }
                });
            });
        }
    };

    bolsacvs.dropDown('#aLinkFlechaT', '#listActionE');
    bolsacvs.ordenamiento("#dataBolsaCVs thead th a");
    bolsacvs.openAnadirGrupo("#btnModalAnadirGrupo");
    bolsacvs.agregarGrupo("#btnAceptarAnadirGrupo");
    bolsacvs.presionarTecla(".txtNameG");
    bolsacvs.eliminarGrupo("#btnAceptarEliminarGrupo");
    bolsacvs.cambiarNombreGrupo("#btnAceptarModificarGrupo");
    bolsacvs.editarGrupoClick("#lnkEditarGrupo");
    bolsacvs.eliminarGrupoClick("#lnkEliminarGrupo");
    bolsacvs.lnkCopiarPostulanteClick(".copPostulante");
    bolsacvs.lnkMoverPostulanteClick(".movPostulante");
    bolsacvs.lnkQuitarPostulanteClick(".kickPostulante");
    bolsacvs.moverPostulante("#btnAceptarMoverBolsa");
    bolsacvs.quitarPostulante("#btnAceptarQuitarBolsa");
    bolsacvs.anadirNotasBolsa(".anadirNotaBolsa");
    bolsacvs.cerrarVentana("#btnCancelarAnadirNotasBolsa");
    bolsacvs.filtropostulante('.checkN','#modalUrl');
    bolsacvs.paginado(".paginador .itemPag a[href]");


    //comentados temporalmente
    bolsacvs.agregarNotasBolsa("#btnAceptarAnadirNotasBolsa");
    //bolsacvs.agregarNotasBolsa("#btnGuardarNotaBolsa");



    bolsacvs.invitarVentana("#invitarBusqueda");

    //comentado temporalmente
    //bolsacvs.guardarEdicionNota(".btnEditNota");

    //comentado temporalmente
    //bolsacvs.eliminarNotaBolsa(".yesCM");

    bolsacvs.lnkAgregarPostulanteClick("#lnkEnviarBolsa");

    bolsacvs.verPerfilFilPerfP();

    bolsacvs.clickAccion(".aActionE");
    bolsacvs.checkGrupal("#chkSelectAll");

    bolsacvs.backToProcess();


});

//Clase para manejo de n arreglos y generar Urls

function Arreglo(n) {
    var me = this;
    var A = new Array(n);

    me.crear = function(nombre) {
        if ((typeof A[nombre]) == "undefined")
            A[nombre] = new Array();
    };
    me.push = function(nombre, valor) {
        A[nombre].push(valor);
    };
    me.remove = function(nombre, valor) {
        for (i = 0; i < A[nombre].length; i++) {
            if (A[nombre][i] == valor)
                A[nombre].splice(i, 1);
        }

    };
    me.getAsParam = function(nombre, separador) {
        return A[nombre].join(separador);
    };
    me.rutaFinal = function(separador) {
        var cadena = "";
        for (var clave in A) {
            cadena += "/" + clave + "/" + me.getAsParam(clave, separador);
        }
        return cadena;
    };
    me.listar = function(nombre) {
        var i = 0;
        for (i = 0; i < A[nombre].length; i++) {
        }
    };
}