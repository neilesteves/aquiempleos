/*
 mis procesos
 */
$( function() {
	 var dataI = {
		  speed1 : 'slow',
		  speed2 : 'fast'
	 };
	 var msgErrors = {
		  email : {
				empty : 'Ingrese un Email',
				bad   : 'No parece ser un Email Válido',
				good  : '¡Correcto!'
		  },
		  nombre : {
				bad   : 'Ingrese un Nombre',
				good  : '¡Correcto!'
		  },
		  apellidos : {
				bad   : 'Ingrese Apellidos',
				good  : '¡Correcto!'
		  },
		  telefono : {
				empty : 'Ingrese Telefono',
				bad   : 'Ingrese solo números',
				good  : '¡Correcto!'
		  },
		  cv        : {
				bad   : 'Suba un CV',
				badExt: 'Archivo Inválido ingrese un DOC,DOCX o PDF',
				badSize: 'El peso máximo del archivo debe ser de 2MB',
				good  : '¡Correcto!'
		  }
	 };
	 var perfil=0;
	 var verproceso = {
		  openCloseI : function() {
				var triggerS = $('.openCloseL'),
				viewMore = 'Ver todos',
				viewLess = 'Ver menos';
				triggerS.click( function(e) {
					 e.preventDefault();
					 var t = $(this),
					 spped = 'meddium',
					 pos = t.parents('.iBlockMSA').offset().top,
					 paren = t.parents('.asideSA').siblings('.cntCloseIt');

					 if(t.hasClass('openIt')) {
						  paren.slideUp(spped, function() {
								t.text(viewMore);
						  });
						  t.removeClass('openIt');
						  $('html, body').animate({
								scrollTop:pos
						  }, spped);
					 } else {
						  t.addClass('openIt');
						  paren.slideDown(spped, function() {
								t.text(viewLess);
						  });
					 }
				});
		  },
        verPerfilFilPerfP : function () {
				$('.verPerfilFilPerfP_Nombre').live('click', function(e) {
					 e.preventDefault();
					 digital_analytix_tag_perfil();
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
					 $('#innerMain').slideUp(dataI.speed1, function(){
						  var idactual = $a.attr("rel");
						  var idnext = $(".verPerfilFilPerfP");
						  var ids = "";
						  var ids_back = "";
						  var d = 0;
						  $.each(idnext, function(index, value){
								if (d==1)
									 ids = ids  + $(value).attr("rel") + "-";
								else
								if (idactual!=$(value).attr("rel"))
									 ids_back = ids_back + $(value).attr("rel") + "-";

								if (idactual==$(value).attr("rel")) d=1;
						  });
						  if (ids!="") ids = ids.substr(0, ids.length-1);
						  if (ids_back!="") ids_back = ids_back.substr(0, ids_back.length-1);

						  ajaxCnt.slideDown(dataI.speed1);

						  var loadCache = false;
						  var idAviso = $('#idAviso').val();

						  if (!loadCache) {
								loadCache = true;
								$.ajax({
									 type: 'get',
									 url: '/empresa/mis-procesos/perfil-publico-emp',
									 cache:false,
									 data: {
										  id: $a.attr('rel'),
										  ids: ids,
										  idsback: ids_back,
										  idAviso:idAviso
									 },
									 success: function(response) {
										  perfil=1;
										  ajaxCnt.slideUp(dataI.speed1);
										  $('#innerMain').parent().append('<div id="perfilContainer" class="all"></div>');

										  $('#perfilContainer').html(response);
										  $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
										  $('#perfilContainer').slideDown(dataI.speed1, function(){

												//Load JS perfil
												AptitusPerfil();
												loadCache = false;
												verproceso.backSpace();
												//$('#backToProcess').die();


										  });

/*                                $('#perfilContainer').html(response);

										  $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
										  $('#perfilContainer').slideDown(dataI.speed1);

										  //Load JS perfil
										  AptitusPerfil();
										  loadCache = false;
										  verproceso.backSpace();*/

									 },
									 dataType: 'html'
								});
						  }

					 });
					 return false;
				});
		  },
		  backToProcess : function () {

			  $('#backToProcess').live('click', function(e) {
					 perfil=0;
					 $(document).unbind("keyup keydown");
					 e.preventDefault();
					 e.stopPropagation();
					 var cntPerfilC = $('#perfilContainer');
					 cntPerfilC.slideUp(dataI.speed1, function() {
						  cntPerfilC.detach();
						  $('#innerMain').slideDown(dataI.speed1, function(){
								var categoria = $("#dataProcesoPostulacion").attr("categoria");
								verproceso.valoresPestanas(categoria);
								$(".liListOptE[rel=-1]").click();

						  });

					 });
					 //Buscando Msj Visibles
					 $('.msgYellow, .msgRed, .msgBlue').removeAttr('style');

					 //die events

					 $('.attachEPL').die();
					 $('.inputAttachEPL').die();
					 $('.icoCloseMsjAdj').die();
					 $('.dataBtnEP').die();

					$('#addNoteMsjTop .dataBtnEP').die();
					//submit nota editar
					$('#contentNotaEPL .dataBtnEP').die();

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
					 //Share
					 $('#fSendCA').unbind('click');
				});
		  },
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
		ventanaAnadirNota : function(a,b) {
				$(a).live("click", function(e) {
					 e.preventDefault();
					 $("#cargando").removeClass("hide");
					 $("#cargando").addClass("loading");
					 $("#frmRegistrarNota").addClass("hide");
					 var iframe = $("<iframe id='frmdescargax' name='frmdescargax' src='javascript:false;'></iframe>");
					 iframe.bind("load",function() {
						  iframe.unbind('load').bind('load', function () {
								setTimeout( function() {
									 var response=undefined;
									 if( $.browser.msie && $.browser.version.substr(0,1) < 8 ) {
										  response = window.frames['frmdescargax'].document.body.innerHTML;
									 } else {
										  response = $(iframe)[0].contentDocument.body.innerHTML;
									 }
									 $("#cargando").removeClass("loading");
									 $("#cargando").addClass("hide");
									 if(response=="") {
										  $(".closeWM").click();
										  verproceso.mostrarMensaje("#mensajesVerProceso","success","Notificacion enviada correctamente");
									 } else {
										  $(b).html(response);
										  $("#msgErrorNotas").removeClass("hide");
									 }
								}, 1000);
						  });
						  $("#frmRegistrarNota").attr("action",urls.siteUrl + "/empresa/mis-procesos/agregar-notas-ver-proceso");
						  $("#frmRegistrarNota").submit();
					 });
					 $("#frmRegistrarNota").append(iframe);
				});
		  },
		  ventanaAnadirMensaje : function(a, b) {
				$(a).live("click", function(evt) {
					 evt.preventDefault();
					 $(b).addClass("loading");
					 var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					 var arreglo_valores =[];
					 var id = 0;
					 $.each(valores, function(index,item) {
						  id = $(item).attr("id");
						  arreglo_valores.push(id);
					 });
					 var cuerpo = $("#areaMsjProEPA").val();
					 var tipo_mensaje = $("#tipomensaje").is(":checked");
					 var json = {
						  "cuerpo":cuerpo,
						  "tipo_mensaje":tipo_mensaje,
						  "postulaciones":arreglo_valores,
						  "token":$("#token").val()
					 };
					 $.ajax({
						  type: "POST",
						  url: "/empresa/mis-procesos/agregar-mensaje-ver-proceso",
						  data: json,
						  dataType: "html",
						  success: function(msg) {
								$(b).removeClass("loading");
								if(msg=="") {
									 $(".closeWM").click();
									 verproceso.mostrarMensaje("#mensajesVerProceso","success","Mensajes enviados correctamente");
									 $.each(valores, function(index,item) {
										  $(item).click();
									 });
								} else {
									 $(b).html(msg);
									 $("#msgErrorMensaje").removeClass("hide");
								}
						  }
					 });

				});
		  },
		  anadirMensaje: function(a) {
				$(a).live("click", function(e) {
					 e.preventDefault();
					 var idverproceso = $(this).attr("href");
					 var idpostulacion = $(this).attr("rel");
					 var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
					 $(contenido).html("");
					 $(contenido).addClass("loading");

					 $.ajax({
						  type: "POST",
						  url: "/empresa/mis-procesos/agregar-mensaje-ver-proceso",
						  data: "id="+idpostulacion,
						  dataType: "html",
						  success: function(msg) {

								$(contenido).removeClass("loading");
								$(contenido).html(msg);
								verproceso.charArea('#areaMsjProEPA','#cantMsjProEPA',300);
								verproceso.fNoArroba('#areaMsjProEPA');
						  }
					 });
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
					 var key = e.keyCode || e.charCode || e.which || window.e ;
					 var length = t.val().length;
					 countN.html( (chars - length) + ' ' );
					 if( length > chars ) {
						  valueArea = t.val().substring(chars, '') ;
						  trigger.val(valueArea);
						  countN.html('0');
					 }
				});
		  },
		  anadirnotas : function(a, b, c, d, f) {
				var idanuncio = $("#dataProcesoPostulacion").attr("idanuncio");

				$(a).live("click", function(e) {
					 e.preventDefault();
					 var idverproceso = $(this).attr("href");
					 var idpostulacion = $(this).attr("rel");
					 var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
					 $(contenido).html("");
					 $(contenido).addClass("loading");

					 $.ajax({
						  type: "POST",
						  url: "/empresa/mis-procesos/agregar-notas-ver-proceso",
						  data: "id="+idpostulacion+"&idanuncio="+idanuncio,
						  dataType: "html",
						  success: function(msg) {
								$(contenido).removeClass("loading");
								$(contenido).html(msg);

								verproceso.anadirNotaChangeTrigger(b,c,d,f);
								verproceso.fNoArroba('#text');

						  }
					 });

				});
				$("#cerrarAgregarNotas").live("click", function() {
					 $(".closeWM").trigger("click");
				});
		  },
		  anadirNotaChangeTrigger : function(b,c,d,f) {
				verproceso.anadirNotaChange(c,d);
				verproceso.removeAdjUpload(d,f);
		  },
		  anadirNotaChange : function(c,d) {
				$(c).bind("change", function(evt) {
					 var t = $(this),
					 dTxtUpload = $(d);
					 dTxtUpload.children("span").text(t.val());
					 dTxtUpload.parent().fadeIn('fast', function() {
						  t.removeClass("hide");
						  t.removeAttr("style");
					 });
				});
		  },
		  removeAdjUpload : function(d,f) {
				$(f).bind('click', function(evt) {
					 evt.preventDefault();
					 var t = $(this);
					 t.parents('.msgYellow').fadeOut('fast', function() {
						  t.siblings('span').text('');
					 });
				});
		  },
		  descartados : function(a,b,c) {
				var boton = $(a);
				var contenido = "#contenido_ajax";

				boton.bind("click", function(e) {
					 e.preventDefault();
					 digital_analytix_tag_descartados();
					 $(b).removeAttr("style");
					 $(b).attr("href","#");

					 $(c).removeAttr("style");
					 $(c).attr("href","#");

					 $(this).css("font-weight","bold");
					 $(this).css("text-decoration","none");
					 $(this).removeAttr("href");
					 e.preventDefault();
					 var flag = $(this).attr("rel");

					 var pagina = $("#dataProcesoPostulacion,#dataReferidos").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion,#dataReferidos").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion,#dataReferidos").attr("categoria");
					 var ord = $("#dataProcesoPostulacion,#dataReferidos").attr("ord");
					 var col = $("#dataProcesoPostulacion,#dataReferidos").attr("col");
					 var cadenabusqueda = $("#dataProcesoPostulacion,#dataReferidos").attr("cadenabusqueda");

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
						  url: "/empresa/mis-procesos/ver-proceso-ajax/"+cadenabusqueda,
						  data: json,
						  dataType: "html",
						  success: function(msg) {
								$(contenido).removeClass("loading");
								$(contenido).html(msg);
								var nombreL="";
								switch(flag) {
									 case "-2":
										  nombreL="Descartados";
										  break;
								}
								var tab = $("#linksTable a[xxx="+(categoria==""?"-1":categoria)+"]");
								tab.addClass("aLinkOn");
								tab.attr("href","/empresa/mis-procesos/ver-proceso/id/"+idanuncio+"/categoria/"+categoria);
								$("#cntOptionsCR").addClass("hide");
						  }
					 });
				});
		  },
		  referidos : function(a,b) {
				var boton = $(a);
				var contenido = "#contenido_ajax";

				boton.bind("click", function(e) {
					 e.preventDefault();
					 $('#filterProcEmp .mask-search').show();
					 $(a).removeAttr("style");
					 $(a).attr("href","#");

					 $(b).removeAttr("style");
					 $(b).attr("href","#");

					 $(this).css("font-weight","bold");
					 $(this).css("text-decoration","none");
					 $(this).removeAttr("href");
					 e.preventDefault();
					 var flag = $(this).attr("rel");

					 var pagina = $("#dataProcesoPostulacion").attr("page") || 1;
					 var idanuncio = ProcessConfig.idaviso;
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var cadenabusqueda = $("#dataProcesoPostulacion").attr("cadenabusqueda") || "";

					 //var json = {"page":pagina,"id":idanuncio,"ord":ord,"col":col,"categoria":categoria,"listaropcion" : flag};
					 var json={"page":pagina,"id":idanuncio};
					 $(contenido).html("");
					 $(contenido).addClass("loading");

					 $.ajax({
						  type: "POST",
						  url: "/empresa/postulantes-referidos/listar/"+cadenabusqueda,
						  data: json,
						  dataType: "html",
						  success: function(msg) {
								$(contenido).removeClass("loading");
								$(contenido).html(msg);
								var nombreL="";
								switch(flag) {
									 case "-3":
										  nombreL="Referidos";
										  break;
								}
								var tab = $("#linksTable a[xxx="+(categoria==""?"-1":categoria)+"]");
								tab.addClass("aLinkOn");
								tab.attr("href","/empresa/mis-procesos/ver-proceso/id/"+idanuncio+"/categoria/"+categoria);
								$("#cntOptionsCR").addClass("hide");
								verproceso.mostrarMensaje("#mensajesVerProceso","info","Listado "+nombreL+" Correctamente");


						  }
					 });
				});
		  },
		  listaropciones : function(a,b) {
				var boton = $(a);
				var contenido = "#contenido_ajax";

				boton.bind("click", function(e) {
					 e.preventDefault();
					 $(a+" a").removeAttr("style");
					 $(a+" a").attr("href","#");

					 $(b+" a").removeAttr("style");
					 $(b+" a").attr("href","#");
					 e.preventDefault();
					 var flag = $(this).attr("rel");

					 $(this).find("a").css("font-weight","bold");
					 $(this).find("a").css("text-decoration","none");
					 $(this).find("a").removeAttr("href");

					 var pagina = $("#dataProcesoPostulacion").attr("page");
					 var idanuncio = (typeof ProcessConfig!="undefined")?ProcessConfig.idaviso:$("#dataProcesoPostulacion").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var cadenabusqueda = $("#dataProcesoPostulacion").attr("cadenabusqueda") || "";
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
						  url: "/empresa/mis-procesos/ver-proceso-ajax/"+cadenabusqueda,
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
										  nombreL="LeÃ­dos";
										  break;
									 case "1":
										  nombreL="No leÃ­dos";
										  break;
								}
						  //verproceso.mostrarMensaje("#mensajesVerProceso","info","Listado "+nombreL+" Correctamente");
						  }
					 });
				});
		  },
		  masacciones : function(a,b) {
				var boton = $(a);
				var contenido = "#contenido_ajax";

				boton.bind("mousedown", function(e) {
					 e.preventDefault();
					 $(b).trigger("click");
					 var flag = $(this).attr("rel");
					 var pagina = $("#dataProcesoPostulacion").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var cadenabusqueda = $("#dataProcesoPostulacion").attr("cadenabusqueda");
					 var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					 var opcionactual = $("#dataProcesoPostulacion").attr("opcionlista");

					 if(opcionactual=="" || opcionactual==undefined) opcionactual = -1;

					 var arreglo_valores =[];
					 var id = 0;
					 $.each(valores, function(index,item) {
						  id = $(item).attr("id");
						  arreglo_valores.push(id);
					 });
					 if(flag == "3") {
						  $(a).trigger("click");

						  if(arreglo_valores.length<1) {
								verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una postulación");
						  }
					 } else if(flag>-1) {
						  if(arreglo_valores.length>0) {
								$(contenido).html("");
								$(contenido).addClass("loading");

								var json = {
									 "page":pagina,
									 "id":idanuncio,
									 "ord":ord,
									 "col":col,
									 "categoria":categoria,
									 "masacciones":arreglo_valores,
									 "flag" : flag,
									 "opcionactual" : opcionactual
								};
								$.ajax({
									 type: "POST",
									 url: "/empresa/mis-procesos/ver-proceso-ajax/"+cadenabusqueda,
									 data: json,
									 dataType: "html",
									 success: function(msg) {
										  $(contenido).removeClass("loading");
										  $(contenido).html(msg);
										  verproceso.mostrarMensaje("#mensajesVerProceso","success","Las postulaciones fueron actualizadas correctamente");
									 }
								});
						  } else {
								verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una Postulación");
						  }
					 } else {
						  //enviar mensaje
						  if(arreglo_valores.length>0) {
								//VENTANA DE ENVIO
								$("#enviarmensaje").click();
						  } else {
								verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una Postulación");
						  }
					 }
				});
		  },
		  descartar : function(a) {
				var boton = $(a);
				$(boton).live("click", function(e) {
					 e.preventDefault();
					 var actual = $(this);
					 var idpostulacion = actual.attr("rel");
					 var pagina = $("#dataProcesoPostulacion").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					 var arreglo_valores =[];
					 var id = 0;
					 if(idpostulacion==undefined) {
						  $.each(valores, function(index,item) {
								id = $(item).attr("id");
								arreglo_valores.push(id);
						  });
					 } else {
						  arreglo_valores.push(idpostulacion);
					 }
					 if(arreglo_valores.length>0) {
						  var json = {
								"page":pagina,
								"id":idanuncio,
								"ord":ord,
								"col":col,
								"categoria":categoria,
								"descartar":arreglo_valores
						  };
						  $.ajax({
								type: "POST",
								url: "/empresa/mis-procesos/ver-proceso-ajax",
								data: json,
								dataType: "html",
								success: function(msg) {

									 if(idpostulacion!=undefined) {
										  actual.parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												actual.parents("tr").remove();
										  });
									 } else {
										  $("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												$("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").remove();
										  });

									 }
									 var msg = $("#contenido_ajax .pagLegend").html();
									 var arr = msg.split(" ");
									 var n1 = parseInt(arr[1])-1;
									 var n2 = parseInt(arr[3])-1;
									 var cadena = arr[0]+" "+n1+" de "+n2+" "+arr[arr.length-1];
									 $("#contenido_ajax .pagLegend").html(cadena);

									 verproceso.valoresPestanas(categoria);
									 verproceso.mostrarMensaje("#mensajesVerProceso","success","Las postulaciones fueron descartadas correctamente");
								}
						  });
					 } else {
						  verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una Postulación");
					 }
				});
		  },
		  restituir : function(a) {
				var boton = $(a);
				$(boton).live("click", function(e) {
					 e.preventDefault();
					 var actual = $(this);
					 var idpostulacion = actual.attr("rel");
					 var pagina = $("#dataProcesoPostulacion").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					 var arreglo_valores =[];
					 var id = 0;
					 if(idpostulacion==undefined) {
						  $.each(valores, function(index,item) {
								id = $(item).attr("id");
								arreglo_valores.push(id);
						  });
					 } else {
						  arreglo_valores.push(idpostulacion);
					 }
					 if(arreglo_valores.length>0) {
						  var json = {
								"page":pagina,
								"id":idanuncio,
								"ord":ord,
								"col":col,
								"categoria":categoria,
								"restituir":arreglo_valores
						  };
						  $.ajax({
								type: "POST",
								url: "/empresa/mis-procesos/ver-proceso-ajax",
								data: json,
								dataType: "html",
								success: function(msg) {

									 if(idpostulacion!=undefined) {
										  actual.parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												actual.parents("tr").remove();
										  });
									 } else {
										  $("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												$("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").remove();
										  });

									 }

									 var msg = $("#contenido_ajax .pagLegend").html();
									 var arr = msg.split(" ");
									 var n1 = parseInt(arr[1])-1;
									 var n2 = parseInt(arr[3])-1;
									 var cadena = arr[0]+" "+n1+" de "+n2+" "+arr[arr.length-1];
									 $("#contenido_ajax .pagLegend").html(cadena);

									 verproceso.valoresPestanas(categoria);
									 verproceso.mostrarMensaje("#mensajesVerProceso","success","Las postulaciones fueron restituidas correctamente");
								}
						  });
					 } else {
						  verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una Postulación");
					 }
				});
		  },
		  moverAEtapa: function(etapa,x,y) {
				$(etapa).live("mousedown", function(e) {
					 e.preventDefault();
					 var contenido = "#contenido_ajax";
					 var clickA = $(this).parents(".menuinterno").prev();
					 clickA.trigger("click");
					 var idpostulacion = $(this).attr("idpostulacion");
					 var opcion = $(this).attr("rel");
					 var pagina = $("#dataProcesoPostulacion").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion").attr("categoria");
					 var ord = $("#dataProcesoPostulacion").attr("ord");
					 var col = $("#dataProcesoPostulacion").attr("col");
					 var valores = $("#dataProcesoPostulacion tbody .data0").find("input:checked");
					 var arreglo_valores =[];
					 var id = 0;
					 if(idpostulacion==undefined) {
						  $.each(valores, function(index,item) {
								id = $(item).attr("id");
								arreglo_valores.push(id);
						  });
					 } else {
						  arreglo_valores.push(idpostulacion);
					 }
					 if (categoria=="") categoria=-1;
					 if(arreglo_valores.length>0 && categoria!=opcion) {
						  var json = {
								"page":pagina,
								"id":idanuncio,
								"ord":ord,
								"col":col,
								"categoria":categoria,
								"opcion":opcion,
								"valores":arreglo_valores
						  };
						  $.ajax({
								type: "POST",
								url: "/empresa/mis-procesos/ver-proceso-ajax",
								data: json,
								dataType: "html",
								success: function(msg) {

									 if(idpostulacion!=undefined) {
										  clickA.parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												clickA.parents("tr").remove();
										  });
									 } else {
										  $("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").animate({
												"opacity": "0.2"
										  }, 700, function() {
												$("#dataProcesoPostulacion tbody .data0").find("input:checked").parents("tr").remove();
										  });
									 }

									 var msg = $("#contenido_ajax .pagLegend").html();
									 var arr = msg.split(" ");
									 var n1 = parseInt(arr[1])-arreglo_valores.length;
									 var n2 = parseInt(arr[3])-arreglo_valores.length;
									 var cadena = arr[0]+" "+n1+" de "+n2+" "+arr[arr.length-1];
									 $("#contenido_ajax .pagLegend").html(cadena);

									 verproceso.mostrarMensaje("#mensajesVerProceso","success","Las postulaciones fueron movidas correctamente");
									 verproceso.valoresPestanas(opcion);
								}
						  });
					 } else {
						  if(categoria==opcion) {
								verproceso.mostrarMensaje("#mensajesVerProceso","error","La postulación ya se encuentra en la categoria");
						  } else {
								verproceso.mostrarMensaje("#mensajesVerProceso","error","Debe seleccionar al menos una Postulación");
						  }
					 }
				});
		  },
		  ordenamiento : function(a) {
				$(a).live("click", function(e) {
					 e.preventDefault();
					 var contenido = "#contenido_ajax";
					 var ord = $(this).attr("ord");
					 var col = $(this).attr("col");

					 var pagina = $("#dataProcesoPostulacion,#dataReferidos").attr("page");
					 var idanuncio = $("#dataProcesoPostulacion,#dataReferidos").attr("idanuncio");
					 var categoria = $("#dataProcesoPostulacion,#dataReferidos").attr("categoria");
					 var opcionlista = $("#dataProcesoPostulacion,#dataReferidos").attr("opcionlista");
					 var cadenabusqueda = $("#dataProcesoPostulacion,#dataReferidos").attr("cadenabusqueda");
					 $.ajax({
						  type: "POST",
						  url: "/empresa/mis-procesos/ver-proceso-ajax/"+cadenabusqueda,
						  data: "page="+pagina+"&id="+idanuncio+"&ord="+ord+"&col="+col+"&categoria="+categoria+"&listaropcion="+opcionlista,
						  dataType: "html",
						  success: function(msg) {

								$(contenido).html(msg);
						  }
					 });
				});
		  },
		  paginado : function(a, idtable) {
				$(document).on("click", a, function(e) {
					 e.preventDefault();
					 var pagina = $(this).attr("rel");
					 var contenido = $("#contenido_ajax");
					 if (idtable == undefined || $(idtable).length==0)
						  idtable = "#dataProcesoPostulacion";
					 var objTable = $(idtable);
					 var ord = objTable.attr("ord");
					 var col = objTable.attr("col");
					 var idanuncio = objTable.attr("idanuncio");
					 var categoria = objTable.attr("categoria");
					 var opcionlista = objTable.attr("opcionlista");
					 var cadenabusqueda = objTable.attr("cadenabusqueda");

					 (pagina == undefined) ? (pagina = '') : (pagina = pagina);
					 (ord == undefined) ? (ord = '') : (ord = ord);
					 (col == undefined) ? (col = '') : (col = col);
					 (opcionlista == undefined) ? (opcionlista = '') : (opcionlista = opcionlista);
					 (idanuncio == undefined) ? (idanuncio = '') : (idanuncio = idanuncio);
					 (categoria == undefined) ? (categoria = '') : (categoria = categoria);

					 objTable.wrap('<div class="loading" id="loadPagTable">');
					 objTable.addClass('hide');
					 $.ajax({
						  type: "POST",
						  url: "/empresa/mis-procesos/ver-proceso-ajax/"+cadenabusqueda,
						  data: "page="+pagina+"&id="+idanuncio+"&ord="+ord+"&col="+col+"&categoria="+categoria+"&listaropcion="+opcionlista,
						  dataType: "html",
						  success: function(msg) {
								objTable.unwrap('<div class="loading" id="loadPagTable">');
								objTable.removeClass('hide');
								contenido.html(msg);
						  }
					 });
				});
		  },
		  paginator:function(page){ /* Nuevo paginado que se implementa en referenciar postulante y bloqueados */
				var pag=$(page),
					 contajax = $("#contenido_ajax"), //Content Ajax
					 idanuncio=ProcessConfig.idaviso;
				pag.live("click",function(e){
					 e.preventDefault();
					 var _this=$(this);
					 contajax.html("").addClass("loading");
					 $.ajax({
						  type:'get',
						  url:_this.attr("href"),
						  data:{"idanuncio":idanuncio},
						  dataType:"html",
						  success: function(response) {
								contajax.removeClass("loading").html(response);
						  }
					 });
				});
		  },
		  start : function(tabla) {
				//para las filas
				$(tabla+" td input[type=checkbox]").live("change", function() {
					 var clase = $(this).parents("tr").attr("class");
					 if(clase==undefined || clase=="" || clase=="pintarLeidos" || clase=="pintarNoLeidos")
						  $(this).parents("tr").addClass("pintar");
					 else
						  $(this).parents("tr").removeClass("pintar");
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
								var relpos = $(item).attr("relpos");
								var content = $(objeto).parents("thead").next().find("td input[id="+id+"]").parent();
								$(item).remove();
								var cb = "<input type='checkbox' name='select' id='"+id+"' relpos='"+relpos+"'>";
								$(content).html(cb);
						  });
					 } else {
						  objeto.parents("thead").next().find("tr").addClass("pintar");
						  //recorremos y removemos y agregamos con checked
						  $.each(checkboxes, function(index, item) {
								var id = $(item).attr("id");
								var relpos = $(item).attr("relpos");
								var content = $(objeto).parents("thead").next().find("td input[id="+id+"]").parent();
								$(item).remove();
								var cb = "<input type='checkbox' checked='checked' name='select' id='"+id+"' relpos='"+relpos+"'>";
								$(content).html(cb);
						  });
					 }
				});
		  },
		  dropDown : function(clickAB, listA) {
				if($(clickAB).size()>0){
					 var clickA = $(clickAB),
					 postT = clickA.position().top,
					 postL = clickA.position().left,
					 heightA = clickA.innerHeight(),
					 listA = $(listA),
					 speed = 'fast';

					 listA.css({
						  'left': postL,
						  'top' : postT + heightA - 2
					 });
					 clickA.bind('click', function(e) {
						  e.preventDefault();
						  var t = $(this),
						  flechaA = t.find('.flechaGrisT');
						  if(t.hasClass('openFl')) {
								setTimeout( function() {
									 listA.slideUp(speed);
									 t.removeClass('openFl');
									 flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
								},1000);

						  } else {
								listA.slideDown(speed);
								t.addClass('openFl');
								flechaA.addClass('downFlechaEP').removeClass('upFlechaEP');
						  }

						  $(document).bind('mouseup', function(e) {
								if($(e.target).parent(clickAB).length==0) {
									 e.preventDefault();
									 flechaA = t.find('.flechaGrisT');
									 listA.slideUp('fast');
									 t.removeClass('openFl');
									 flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
								}
						  });
					 });
				}
		  },
		  dropDownLive : function(clase,lista) {
				var clickA = $(clase);
				clickA.live('click', function(e) {
					 e.preventDefault();
					 //var lado = $(this).attr("rel");
					 var t = $(this);
					 var lado = t.attr("rel");
					 var postT = t.position().top,
					 postL = t.position().left,
					 widthA = t.innerWidth(),
					 heightA = t.innerHeight(),
					 //listA = $(this).next(),
					 listA = t.children('ul'),
					 speed = 'fast';

					 var left = postL - widthA + 10,
					 top = postT + heightA;

					 if(lado=="der"){
						  left = postL;
					 }else if(lado=="izq"){
						  left = postL - listA.innerWidth();
						  top = postT - 40;
					 }

					 listA.css({
						  'left': left,
						  'top' : top
					 });
					 listA.addClass("listSlip");
					 var t = $(this),
					 flechaA = t.find('.flechaGrisT');
					 if(t.hasClass('openFl')||$(e.target).parents(".listSlip").length) {
						  listA.slideUp(speed);
						  t.removeClass('openFl');
						  flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
					 } else {
						  $.each($(".openFl"),function(index,value){
								$(value).removeClass("openFl");
								$(value).children('ul').slideUp(speed);
						  });
						  listA.slideDown(speed);
						  t.addClass('openFl');
						  flechaA.addClass('downFlechaEP').removeClass('upFlechaEP');
					 }
					 if(typeof t.data("st")==="undefined"){
						  $(document).bind('mouseup', function(e) {
								if($(e.target).parent(clase).length==0) {
									 var flechaA = t.find('.flechaGrisT');
									 t.children('ul').slideUp('fast');
									 t.removeClass('openFl');
									 flechaA.addClass('upFlechaEP').removeClass('downFlechaEP');
								}
						  });
						  t.data("st",true);
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
		  //Aqui no
		  valoresPestanas : function(id) {
				var idanuncio = (typeof ProcessConfig!="undefined")?ProcessConfig.idaviso:$("#dataProcesoPostulacion").attr("idanuncio");
				var json = {
					 "id" : idanuncio
				};
				var total = 0;
				$.ajax({
					 type: "POST",
					 url: "/empresa/mis-procesos/get-numero-categorias-postulaciones-ajax",
					 data: json,
					 dataType: "json",
					 success: function(msg) {
						  $.each(msg, function(i,x) {
								if (x.n==undefined) x.n=0;
								$("a[xxx="+x.id+"]").children("span").html(parseInt(x.n));
								if(x.id==id)
									 total=parseInt(x.n);
						  });
						  $("a[xxx="+id+"]").fadeOut("fast").fadeIn("fast");

					 }
				});
		  },
		  fWordsOnly : function(a,inst) {
				var sel=(typeof inst!=="undefined")?$(a,inst):$(a);
				return sel.each( function() {
					 var t = $(this);
					 t.bind('keyup', function(e){
			var regExp = /[^a-zA-Z\sáéíóúÁÉÍÓÚüÜñÑ]/gi;
			if(regExp.test(this.value)) this.value=this.value.replace(regExp,'');
		});
					 /*t.keydown( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  return(	key == 8 || key == 9 || key == 13 || key == 32 ||
								key > 64 && key < 91 ||
								key == 192 );
					 });*/
				});
		  },
		  buscadordeempresa : function(check,actionUrl) {

				var enviroment = $(".form-search"),
					 alertSearch = $('#alertSearch'),
					 maskSearch = $('.mask-search', '#innerProcEmp'),
					 frmSearch = $('#fIRSearch'),
					 isAnyCheck = null,
					 actionBuscar = function(event){
						  var dataString = $.trim($('#fWordRS').val()),
								url = "",
								data = "",
								idanuncio = $("#idAviso").val();

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

						  //console.log("/query/"+dataString+"/id/"+idanuncio+data);
						  //return false
						  enviroment.find(':checkbox').attr("disabled", true);

						  if(enviroment.find(':checkbox:checked').length > 0){
								isAnyCheck = true;
						  }else{
								isAnyCheck = false;
						  }

						  var contenido = "#contenido_ajax";
						  $(contenido).html("");
						  $(contenido).addClass("loading");

						  var temp = {};
						  if(event.data.check){
								temp = {
									 categoria: $("#dataProcesoPostulacion").attr("categoria"),
									 listaropcion: $("#dataProcesoPostulacion").attr("opcionlista"),
									 check: isAnyCheck,
									 tipo: undefined
								}
						  }else{
								temp = {
									 id : $("#dataProcesoPostulacion,#dataReferidos").attr("idanuncio"),
									 ord: $("#dataProcesoPostulacion,#dataReferidos").attr("ord"),
									 col: $("#dataProcesoPostulacion,#dataReferidos").attr("col"),
									 categoria: $("#dataProcesoPostulacion").attr("categoria"),
									 listaropcion: $("#dataProcesoPostulacion").attr("opcionlista"),
									 tipo: 'query'
								}
						  }

						  //console.log(temp);
						  $.ajax({
								type: "POST",
								url: "/empresa/mis-procesos/ver-proceso-ajax/query/"+dataString+"/id/"+idanuncio+data,
								data: temp,
								dataType: "html",
								success: function(msg) {
									 $(contenido).removeClass("loading");
									 $(contenido).html(msg);
									 enviroment.find(':checkbox').attr("disabled", false);
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
						  var _this   = $(this),
								parent  = _this.parents('form');

						  if(_this.is(":checked")){
								$('#' + _this.attr("id"), parent).attr('checked',true);
						  }else{
								$('#' + _this.attr("id"), parent).attr('checked',false);
						  }

						  alertSearch.show();
						  maskSearch.show();
					 };

				$(actionUrl).on("click", {check: true} ,actionBuscar);
				frmSearch.on('submit', {check: false}, actionBuscar);
				$('.ioption.accord').on('click',function(){
					 alertSearch.show();
					 maskSearch.show();
				});

				enviroment.on("change", 'input:checkbox,input:radio', actionCheck);
				alertSearch.on('click',function(){
					 maskSearch.hide();
					 $(this).addClass("hide");
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
		  referenciado:{
				"addPostulanteAptitus":function(postulante,tipo){
					 console.log(postulante["path_foto"]);
					 var table=$("#tablaEPostulante tbody"),
						  content=$("#tablaExistePostulante"),
						  fila,
						  urlimg=(postulante["path_foto"]!="")?postulante["path_foto"]:urls.mediaUrl+"/images/photoDefault.jpg",
						  clPos=(postulante["sexo"]=="M")?"imgHombre":"imgMujer",
						  data=$.stringify(postulante),
						  status={
								"1":"<a tip='1' id='linkreferenciarPostulante' title='Referenciar' rel='"+data+"' href='javascript:;'>Referenciar</a>",
								"3":"<p class='pstr'>El postulante se encuentra bloqueado</p>",
								"4":"<p class='pstr'>El postulante ya se encuentra dentro del proceso</p><a title='Referenciar' tip='4' id='linkreferenciarPostulante' rel='"+data+"' href='javascript:;'>Referenciar</a>",
								"6":"<p class='pstr'>El postulante ya se encuentra invitado</p><a title='Referenciar' tip='6' id='linkreferenciarPostulante' rel='"+data+"' href='javascript:;'>Referenciar</a>"
						  };
					 fila="<tr>\
								<td class='data1'><img width='100' height='100' src='"+urlimg+"'></td>\
								<td class='data2'>"+postulante["nombres"]+" "+postulante["apellido_paterno"]+" "+postulante["apellido_materno"] +"<br/>"+
								( (postulante["telefono"]!=''||postulante["celular"]!='')?"Cel. "+postulante["telefono"]+" - "+postulante["celular"]+"<br/>":"" )+
								( (postulante["sexo"]!='')?"<span class='sptIcoEmp "+clPos+"'>"+postulante["sexo"]+"</span><br/>":"" )+
								( (postulante["path_cv"]!='')?"<a target='_blank' href='/perfil/"+postulante["slug"]+"'>Ver CV</a>":"" )+
								"</td>\
								<td class='data3 tcnt'>"+status[tipo]+"</td>\
							 </tr>";
					 table.append(fila);
					 content.slideDown(500);
				},
				"addPostulante":function(postulante){
					 var list=$("#listReference"), //Contenedor List
						  fila,
						  cont=$(".addReg").length+1,
						  opt="";
					 if(!postulante.hasOwnProperty('id')){
						  opt="<a title='Editar' class='editB editrefer' rel='"+postulante.email+"' href='javascript:;'><span class='sEditB f11'>Editar</span></a>";
					 }
					 opt=opt+"<a title='Eliminar' class='deleteB delrefer' rel='"+postulante.email+"' href='javascript:;'><span class='sDeleteB f11'>Eliminar</span></a>";
					 fila="<blockquote class='addReg'>\
								<span class='wCount left'><span class='countN'>"+cont+"</span></span>\
								<span class='rname left'>\
									 <strong>"+postulante.nombres+" "+postulante.apellidos +"</strong>\
								</span>\
								<span class='remail left'>\
									 <a href='mailto:"+postulante.email+"'>"+postulante.email+"</a>\
								</span>"+opt+"\
						  </blockquote>";
					 list.append(fila).show();
				},
				"ajax":function(url,data,type,callback,cache,cacheId){
					 var route={
						  "verificar":"/empresa/postulantes-referidos/verificar",
						  "formulario":"/empresa/postulantes-referidos/obtener-formulario",
						  "referenciar":"/empresa/postulantes-referidos/referenciar",
						  "mostrar":"/empresa/postulantes-referidos/mostrar",
						  "quitar":"/empresa/postulantes-referidos/quitar",
						  "registrar":"/empresa/postulantes-referidos/registrar"
					 },
					 that=this,
					 cach=(typeof cache==="undefined")?0:cache,
					 cachId=(typeof cache==="undefined")?"":cacheId,
					 cload=$("#cargandoReferenciado"), //Loading
					 ctref=$("#mainReferenciado"); //Contenido Dinamico;
					 window.cbCACHE = window.cbCACHE||{};
					 if(cach&&window.cbCACHE.hasOwnProperty('cach-'+cachId)){
						  callback && callback(window.cbCACHE['cach-'+cachId]);
					 }else{
						  $.ajax({
								type: "POST",
								url: route[url],
								"data": data,
								dataType: type,
								success: function(result){
								  if(cach){ window.cbCACHE['cach-'+cachId] = result;}
								  callback && callback(result);
								},
								error:function(jqXHR,textStatus,error){
									 that.msgBox(0,"Error en el sistema. Intente nuevamente","2");
									 that.load(false);
								}
						  });
					 }
				},
				"llenarForm":function(inst,postulante,idanuncio){
					 $("#email",inst).val(postulante.email).attr("readonly",true);
					 $("#nombres",inst).val(postulante.nombres);
					 $("#apellidos",inst).val(postulante.apellidos);
					 $("#telefono",inst).val(postulante.telefono);
					 $("#repCV",inst).val(postulante.curriculo);
					 if(postulante.sexo=="M"){$("#sexo-M",inst).trigger("click");}else{$("#sexo-F",inst).trigger("click");}
					 $("#anuncio",inst).val(idanuncio);
				},
				"msgBox":function(tipo,msg,color){
					 var cont="",
						  classmsg={
								"1":"msgYellow",
								"2":"msgRed"
						  };
					 if(parseInt(tipo)==2){
						  cont=msg+" "+"<a id='agregarReferenciado' href='javascript:;' title='Agregar Un Referido'>Agregar Un Referido</a>";
					 }else{
						  cont=msg;
					 }
					 $("#resultadoReferenciado").removeClass("msgYellow msgRed").addClass(classmsg[color]).html(cont).slideDown(500);
				},
				"orderRefer":function(){
					 $.each($(".addReg"),function(index,value){
						  $(value).find(".countN").text(index+1);
					 });
				},
				"showSearch":function(){
					 if($(".addReg").length>=parseInt(ProcessConfig.cntreferidos)){$("#buscarReferenciado").hide();}else{$("#buscarReferenciado").show();}
				},
				"clear":function(opts){
					 var opt=(typeof opts!=="undefined")?opts:true; //Verificar si se requiere limpiar el campo del email
					 if(opt){$("#txtbuscaEmail").val("");}
					 $("#tablaExistePostulante,#frmRegistroReferente").hide();
					 $("#tablaEPostulante tbody,.respEmailE,#frmRegistroReferente").html("");
					 $("#resultadoReferenciado").removeClass("msgYellow msgRed").hide();
				},
				"load":function(cond,empty){
					 var that=this,
						  cload=$("#cargandoReferenciado"), //Loading
						  ctref=$("#mainReferenciado"), //Contenido Dinamico
						  opt=(typeof empty!=="undefined")?empty:true;
					 if(cond){
						  cload.removeClass("hide");
						  ctref.hide();
						  if(opt){that.clear();}
						  $('html,body').animate({scrollTop:0}, 500);
					 }else{
						  cload.addClass("hide");
						  ctref.slideDown(500);
						  $('html,body').animate({scrollTop:0}, 500);
					 }
				}
		  },
		  buscarMailReferenciado : function(a, b, c) {

				var idanuncio = ProcessConfig.idaviso, //Almacena el ID del anuncio
				email, //Almacena email ingresado
				bxalert=$("#resultadoReferenciado"); //Contenedor de mensajes de error
				//Buscar Referenciado
				$(a).bind("keyup",function(e){
					 var msg = "#buscarReferenciado span.msg";
					 var txt = $(this);
					 var ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
					 if(ep.test(txt.val()) && txt.val()!="") {
						  $(msg).html(msgErrors.email.good);
						  $(msg).removeClass("error");
						  $(msg).addClass("good");
					 } else {
						  if(txt.val()=="") {
								$(msg).html(msgErrors.email.empty);
								$(msg).removeClass("good");
								$(msg).addClass("error");
								$(msg).removeClass("hide");
						  } else {
								$(msg).html(msgErrors.email.bad);
								$(msg).removeClass("good");
								$(msg).addClass("error");
								$(msg).removeClass("hide");
						  }
					 }
				});
				$(b).live("click",function(e){ //Evento para el boton buscar
					 e.preventDefault();
					 email=$(a).val();
					 $(a).trigger("keyup");
					 if(!$(".respEmailE.error").length){
						  verproceso.referenciado.load(true,false);
						  verproceso.referenciado.ajax("verificar",{"email":email,"anuncio":idanuncio},"json",function(json){
								verproceso.referenciado.clear(false);
								verproceso.referenciado.load(false);
									if(json.estado==1){
										 if(json.tipo==2){
											  verproceso.referenciado.msgBox(json.tipo,json.mensaje,"1");
										 }
										 else{
											  verproceso.referenciado.addPostulanteAptitus(json.postulante,json.tipo);
										 }
									}else{
										  verproceso.referenciado.msgBox(json.tipo,json.mensaje,"2");
								  }
						  });
					 }
				});
				//Mostrar Registro Referenciado
				$(c).live("click", function(e){
					 e.preventDefault();
					 verproceso.referenciado.load(true);
					 var idanuncio = ProcessConfig.idaviso;
					 verproceso.referenciado.ajax("formulario",{},"html",function(result){
						  verproceso.referenciado.load(false);
						  var form=$("#frmRegistroReferente");
						  form.html(result);
						  $("#email",form).val(email).attr("readonly",true);
						  window.cFrame = (typeof cFrame!=="undefined")?cFrame+1:1;  //Identificar Frame
						  $("iframe",form).attr("id","frmref"+cFrame);
						  form.slideDown(500,function(){
								verproceso.validacionAgregarReferente(form);
								$("#anuncio",form).val(idanuncio);
						  });
					 },1,"form");
				});
		  },
		  consultarReferenciado:function(a){
				$(a).bind("click",function(){
					 var idanuncio = ProcessConfig.idaviso,
						  list=$("#listReference"); //Contenedor List
					 verproceso.referenciado.load(true);
					 verproceso.referenciado.ajax("mostrar",{"id":idanuncio},"json",function(json){
						  list.html("");
						  if(json!=""){
								for(var key in json){
									 verproceso.referenciado.addPostulante(json[key]);
								}
								Cookie.create("refer-"+idanuncio,$.stringify(json));
						  }else{
								Cookie.create("refer-"+idanuncio,"");
						  }
						  verproceso.referenciado.showSearch();
						  if($("#listReference .addReg").length){$("#totalRegReferente").show();}else{$("#totalRegReferente").hide();}
							verproceso.referenciado.load(false);
					 });
				});
		  },
		  validacionAgregarReferente: function(inst) {
				$(".block .response").hide();
				$("#email",inst).bind("keyup",function(e){
					 var msg = $(this).siblings(".response");
					 var txt = $(this);
					 var ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
					 if(ep.test(txt.val()) && txt.val()!="") {
						  msg.html(msgErrors.email.good);
						  msg.removeClass("bad");
						  msg.addClass("good");
					 } else {
						  if(txt.val()=="") {
								msg.html(msgErrors.email.empty);
								msg.addClass("bad");
								msg.removeClass("good");
						  } else {
								msg.html(msgErrors.email.bad);
								msg.addClass("bad");
								msg.removeClass("good");
						  }
					 }
					 msg.show();
				});
				verproceso.validaTexto("#nombres",inst,msgErrors.nombre.bad, msgErrors.nombre.good);
				verproceso.validaTexto("#apellidos",inst,msgErrors.apellidos.bad, msgErrors.apellidos.good);
				verproceso.validaNumeros("#telefono",inst,msgErrors.telefono.empty, msgErrors.telefono.good);
				verproceso.fWordsOnly("#nombres",inst);
				verproceso.fWordsOnly("#apellidos",inst);
				verproceso.validaCv(inst);
		  },
		  referenciardesdeLink : function() {
				$("#linkreferenciarPostulante").live("click", function(e){
					 e.preventDefault();
					 var date=(new Function('return '+$(this).attr("rel")))(), //Json postulante
						  idpostulante=date.id, //Id postulante
						  email=date.email, //Email postulante
						  cond=parseInt($(this).attr("cond")),
						  idanuncio = ProcessConfig.idaviso; //Id anuncio
					 verproceso.referenciado.load(true,false);
					 verproceso.referenciado.ajax("referenciar",{"postulante-id":idpostulante,"tipo":$(this).attr("tip"),"email":email,"anuncio-id":idanuncio},"json",function(json){
						  verproceso.referenciado.clear();
						  if(json.estado==1){
								//Cookie.mantJson("refer-"+idanuncio,json.postulante["email"],1,json.postulante);
								verproceso.referenciado.addPostulante(date);
								$("#totalRegReferente").show();
						  }else{
								 verproceso.referenciado.msgBox(json.tipo,json.mensaje,"2");
						  }
						  verproceso.referenciado.showSearch();
							verproceso.referenciado.load(false);
					 });
				});
		  },
		  editarReferenciado:function(){
				var load=function(content,cond){
						  if(cond){
								content.css("position","relative").append("<div class='loader'><div class='opacity'></div><span class='load'></span></div>");
						  }else{
								$(".loader",content).remove();
						  }
					 },
					 slidesRemove=function(){$(".cedit-refer").slideUp(500,function(){$(this).remove();});},
					 idanuncio=ProcessConfig.idaviso,
					 flag=true;
				$(".editrefer").live("click",function(){
					 if(flag){
						  flag=false;
						  var _this=$(this),
								email=_this.attr("rel"),
								content=$("<div/>",{'class':'cedit-refer hide'}),
								vform=_this.parent().next();//Form creado virtualmente
						  if(vform.hasClass("cedit-refer")){
								vform.slideUp(500,function(){
									 $(this).remove();
									 flag=true;
								});
						  }else{
								if(window.cbCACHE.hasOwnProperty('cach-form')){
									 slidesRemove();
									 _this.parent().after(content);
									 content.append(window.cbCACHE['cach-form']);
									 window.cFrame = (typeof cFrame!=="undefined")?cFrame+1:1;  //Identificar Frame
									 $("iframe",content).attr("id","frmref"+cFrame);
									 verproceso.validacionAgregarReferente(content);
									 verproceso.referenciado.llenarForm(content,Cookie.readJson("refer-"+idanuncio)[email],idanuncio);
									 content.slideDown(500,function(){
										  flag=true;
									 });
								}else{
									 slidesRemove();
									 load(_this.parent(),true);
									 verproceso.referenciado.ajax("formulario",{},"html",function(result){
										  load(_this.parent(),false);
										  _this.parent().after(content);
										  content.append(result);
										  window.cFrame = (typeof cFrame!=="undefined")?cFrame+1:1;  //Identificar Frame
										  $("iframe",content).attr("id","frmref"+cFrame);
										  verproceso.validacionAgregarReferente(content);
										  verproceso.referenciado.llenarForm(content,Cookie.readJson("refer-"+idanuncio)[email],idanuncio);
										  content.slideDown(500,function(){
												flag=true;
										  });
									 },1,"form");
								}
						  }
					 }
				});
		  },
		  borrarReferenciado:function(){
				var listr=$("#listReference"), //Lista de referenciados
					 regt=$("#totalRegReferente"), //Registrar todos los referentes
					 idanuncio=ProcessConfig.idaviso;
				$(".delrefer").live("click",function(){
					 var _this=$(this),
						  email=$(this).attr("rel");
					 verproceso.referenciado.load(true);
					 verproceso.referenciado.ajax("quitar",{"email":email,"anuncio":idanuncio},"json",function(json){
						  if(json.estado===1){
								_this.parent(".addReg").remove();
								if($(".addReg").length==0){listr.hide();regt.hide();}
								verproceso.referenciado.showSearch();
								verproceso.referenciado.orderRefer();
						  }else{
								verproceso.referenciado.msgBox(json.tipo,json.mensaje,"2");
						  }
						  verproceso.referenciado.load(false);
					 });
				});
		  },
		  validaTexto : function(a, inst, msg_error, msg_good) {
				var actual = (typeof inst!=="undefined")?$(a,inst):$(a);
				actual.live("keyup", function(){
					 var valor = $(this).val();
					 var msg = $(this).siblings(".response");
					 if(valor!="") {
						  msg.html(msg_good);
						  msg.removeClass("bad");
						  msg.addClass("good");
					 } else {
						  msg.html(msg_error);
						  msg.addClass("bad");
						  msg.removeClass("good");
					 }
					 msg.show();
				});
		  },
		  validaNumeros : function(a,inst, msg_error, msg_good) {
				var actual = (typeof inst!=="undefined")?$(a,inst):$(a);
				verproceso.validaTexto(a,inst, msg_error, msg_good);
				return actual.each( function() {
					 var t = $(this);
					 t.keydown( function(e) {
						  var key = e.keyCode || e.charCode || e.which || window.e ;
						  return (key == 8 || key == 9 || key == 32 ||
								(key >= 48 && key <= 57)||
								(key >= 96 && key <= 105)||
								key==109 || key==116 );
					 });
				});
		  },
		  validaCv : function(inst) {
				var idanuncio=ProcessConfig.idaviso,
					 idFrame=$("iframe",inst).attr("id"), // Id de Frame
					 vInputFile={ //Validar input file
						  "valExt":function(ext){
								return /^(doc|docx|pdf)$/gi.test($.trim(ext));
						  },
						  "valSize":function(file){
								if( $.browser.msie ){return true;}
								var sz=file[0].files[0].size;
								if(parseInt(sz) <= urls.maxSizeFile){return true;}else{return false;}
						  }
					 };
				$("#path_cv",inst).bind("change", function(){
					 var valor = $(this).val(),
						  file,ext;
					 $("#repCV").val(valor);
					 if(valor=="") {
						  if(!$(this).parents(".cedit-refer").length){
								$(this).parents("#cntFieldCV").siblings(".response").show()
								.removeClass("good").addClass("bad").html(msgErrors.cv.bad);
						  }
					 } else {
						  /* Obteniendo la extensión del archivo en todos los navegadores */
						  file=valor.split("\\");
						  file=file[file.length-1];
						  ext=file.split(".");
						  ext=ext[ext.length-1];
						  /*****Fin*****/
						  if(vInputFile.valExt(ext)&&vInputFile.valSize($(this))){
								$(this).parents("#cntFieldCV").siblings(".response").show()
								.removeClass("bad").addClass("good").html(msgErrors.cv.good);
						  }else{
								var fileErr=(!vInputFile.valSize($(this)))?msgErrors.cv.badSize:msgErrors.cv.badExt;
								 $(this).parents("#cntFieldCV").siblings(".response").show()
								.removeClass("good").addClass("bad").html(fileErr);
						  }
					 }
				});
				$("#registerProcE",inst).bind("click", function(){
					 $("#fEmail",inst).trigger("keyup");
					 $("#nombres",inst).trigger("keyup");
					 $("#apellidos",inst).trigger("keyup");
					 $("#telefono",inst).trigger("keyup");
					 $("#path_cv",inst).trigger("change");
					 var errores = $(".block .response.bad");
					 if(errores.length==0) {
						  verproceso.referenciado.load(true,false);
						  $("#datoBasicoEPF",inst).submit();
					 }
				});
				function tiempo() {
					 var response="",json,err;
					 if($.browser.msie && $.browser.version.substr(0,1) <= 8 ) {
						  response = window.frames[idFrame].document.body.innerHTML;
					 } else {
						  response = $("#"+idFrame)[0].contentDocument.body.innerHTML;
					 }
					 json=(new Function('return '+response+';'))();
					 if(json){
						  if(json.estado==1){
								//Cookie.mantJson("refer-"+idanuncio,json.postulante["email"],1,json.postulante);
								if(inst.attr("id")==="frmRegistroReferente"){verproceso.referenciado.addPostulante(json.postulante);}else{
									 inst.prev().find(".rname strong").text(json.postulante["nombres"]+" "+json.postulante["apellidos"]);
									 inst.remove();
								}
								verproceso.referenciado.showSearch();
								verproceso.referenciado.clear();
								$("#totalRegReferente").show();
						  }else{
								for(var key in json.errores){
									 for(var ky in json.errores[key]){
										  err=json.errores[key][ky];
										  break;
									 }
									 $("#"+key,inst).parents(".block").find(".response").show()
									 .removeClass("good").addClass("bad").html(err);
								}
						  }
					 }
					 verproceso.referenciado.load(false);
				}
				$("#"+idFrame).bind("load",function() {
					 tiempo();
				});
		  },
		  registrarReferenciados:function(){
				$("#registerRefer").bind("click",function(){
					 var idanuncio=ProcessConfig.idaviso;
					 verproceso.referenciado.load(true);
					 verproceso.referenciado.ajax("registrar",{"anuncio":idanuncio},"json",function(json){
						  verproceso.referenciado.load(false);
						  if(json.estado==1){
								$(".closeWM").click();
								verproceso.mostrarMensaje("#mensajesVerProceso","success","Postulantes referenciados correctamente.");
								$(".listarreferidos").trigger("click");
								Cookie.del("refer-"+$.trim(idanuncio));
						  }else{
								verproceso.referenciado.msgBox(json.tipo,json.mensaje,"2");
						  }
					 });
				});
		  },
		  //next perfil
		  nextPerfilVerProceso : function () {
				$('#nextToProcess').live('click', function(e) {
					 e.preventDefault();
					 e.stopPropagation();
					 var $a = $(this);
					 var ajaxCnt = $('#ajax-loading');

					 $('#perfilContainer').slideUp(dataI.speed1, function(){
						  var idnext = $a.attr("rel");
						  var ids = $a.attr("sig");
						  var idactual = $a.attr("idactual");

						  var idsback = $("#backToPerfil").attr("sig");
						  var rel = $("#backToPerfil").attr("rel");
						  if (idsback=="" || idsback==undefined || idsback=="NULL") {
								if (rel=="" || rel==undefined) {
									 idsback = idactual;
								} else {
									 idsback = rel+"-"+idactual;
								}
						  } else {
								idsback = idsback+"-"+rel+"-"+idactual;
						  }

						  ajaxCnt.slideDown(dataI.speed1);
						  var loadCache = false;
						  if(!loadCache){
								loadCache = true;
								$.ajax({
									 type: 'get',
									 url: '/empresa/mis-procesos/perfil-publico-emp',
									 data: {
										  id: idnext,
										  ids: ids,
										  idAviso:  $('#idAviso').val(),
										  idsback: idsback,
										  s:1
									 },
									 success: function(response) {
										  ajaxCnt.slideUp(dataI.speed1);
										  $('#innerMain').parent().append('<div id="perfilContainer" class="all"></div>');
										  $('#perfilContainer').html(response);
										  $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
										  $('#perfilContainer').slideDown(dataI.speed1);
										  //Load JS perfil
										  AptitusPerfil();
										  loadCache = false;
									 },
									 dataType: 'html'
								});
						  }

					 });
					 return false;
				});
		  },
		  //back perfil
		  //next perfil
		  backPerfilVerProceso : function () {
				$('#backToPerfil').live('click', function(e) {
					 e.preventDefault();
					 e.stopPropagation();
					 var $a = $(this);
					 var ajaxCnt = $('#ajax-loading');

					 $('#perfilContainer').slideUp(dataI.speed1, function(){
						  var idnext = $a.attr("rel");
						  var ids = $a.attr("sig");
						  var idactual = $a.attr("idactual");

						  var idsnext = $("#nextToProcess").attr("sig");
						  var rel = $("#nextToProcess").attr("rel");
						  if (idsnext=="" || idsnext==undefined || idsnext=="NULL") {
								if (rel=="" || rel==undefined ) {
									 idsnext = idactual;
								} else {
									 idsnext = idactual+"-"+rel;
								}
						  } else {
								idsnext = idactual+"-"+rel+"-"+idsnext;
						  }

						  ajaxCnt.slideDown(dataI.speed1);
						  var loadCache = false;
						  if(!loadCache){
								loadCache = true;
								$.ajax({
									 type: 'get',
									 url: '/empresa/mis-procesos/perfil-publico-emp',
									 data: {
										  id: idnext,
										  idsback: ids,
										  idAviso:  $('#idAviso').val(),
										  ids: idsnext,
										  s:0
									 },
									 success: function(response) {
										  ajaxCnt.slideUp(dataI.speed1);
										  $('#innerMain').parent().append('<div id="perfilContainer" class="all"></div>');
										  $('#perfilContainer').html(response);
										  $('#shareMail').find('#hdnOculto').val($('#spanPostulante').attr('rel'));
										  $('#perfilContainer').slideDown(dataI.speed1);
										  //Load JS perfil
										  AptitusPerfil();

										  loadCache = false;

									 },
									 dataType: 'html'
								});
						  }

					 });

					 return false;
				});
		  },
		  backSpace : function() {
				//Back en tecla backspace
				if (perfil==1) {
					 $(document).bind('keydown', function(e){
						  var key = e.keyCode || e.charCode || e.which || window.e,
						  nameNode = e.target.nodeName;
						  if (key == 8) {
								if(nameNode == 'TEXTAREA' ||
									 nameNode == 'INPUT'){
									 return true;
								}else{

									 $("#backToProcess").click();
									 return false;
								}
						  }else{
								return true;
						  }

					 });
				}
		  }

	 };

	 //verproceso.openCloseI();
	 verproceso.dropDown('#aLinkFlechaT', '#listActionE');
	 //verproceso.dropDown('#aLinkFlechaM', '#listActionM');

	 //ver proceso
	 verproceso.backToProcess();
	 verproceso.verPerfilFilPerfP();
	 verproceso.nextPerfilVerProceso();
	 verproceso.backPerfilVerProceso();
	 verproceso.backSpace();

	 // actividades

	 verproceso.start("#dataProcesoPostulacion");

	 if ( yOSON.controller == "mis-procesos" && yOSON.action == "ver-proceso" )
		  verproceso.paginado(".paginador .itemPag a[href]", '#dataProcesoPostulacion');

	 if ( yOSON.controller == "mis-procesos" && yOSON.action == "detalle-candidatos" )
		  verproceso.paginado(".paginador .itemPag a[href]", '#dataProcesoBusquedaCandi');
	 /* Nuevo paginador */
	 verproceso.paginator(".paginator .itemPag a[href]");
	 /* Fin nuevo paginator */
	 verproceso.ordenamiento("#dataProcesoPostulacion thead th a");
	 verproceso.dropDownLive(".moveraetapafila", "#listActionM");
	 verproceso.moverAEtapa(".aActionM",'#aLinkFlechaM', '#listActionM');
	 verproceso.descartar(".descartarButtonVerProceso");
	 verproceso.restituir(".restituirButton");
	 verproceso.masacciones(".liActionE","#aLinkFlechaT");
	 verproceso.listaropciones(".liListOptE",".listardescartados");
	 verproceso.descartados(".listardescartados", ".liListOptE",".listarreferidos");
	 verproceso.referidos(".listarreferidos",".listardescartados");
	 verproceso.anadirnotas(".anadirnotas","#adjuntarnota","#txtUploadFile","#nombreUploadFile","#removeFileUpload");
	 verproceso.anadirMensaje("#enviarmensaje");
	 verproceso.ventanaAnadirMensaje("#guardarMensajeVerProceso", "#content-winAnadirMensaje");
	 verproceso.ventanaAnadirNota("#btnGrabarNota", "#content-winAnadirNotas");
	 verproceso.buscadordeempresa('.checkN','#modalUrl');
	 //verproceso.fNoArroba('#text');
	 //verproceso.fNoArroba('#cuerpo');

	 //referenciado
	 verproceso.buscarMailReferenciado("#txtbuscaEmail", "#btnBuscarxEmail","#agregarReferenciado");
	 verproceso.borrarReferenciado();
	 verproceso.editarReferenciado();
	 verproceso.referenciardesdeLink();
	 verproceso.registrarReferenciados();
	 verproceso.consultarReferenciado("a[href=#winRegistrarReferenciado]");
});

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
