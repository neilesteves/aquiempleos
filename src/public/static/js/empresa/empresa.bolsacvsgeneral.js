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
					 good  : '¡Correcto!'
				}
		  };
	var idpos = undefined;

		  var bolsacvsgeneral = {

				lnkAgregarPostulanteGrupoClick : function(a) {
					 $(a).live("click", function(e) {
						  e.preventDefault();
						  idpos = undefined;
						  var link = $(this).attr("name");

						  if($(this).attr("href") == ""){
								var contenido = "#divBolsasEnviar";
								var idspostulantes = $(".dataGridEnviarBolsa tbody .data0").find("input:checked");
								var arregloIds = [];
								var id = 0;
								$.each(idspostulantes, function(index,item) {
									 id = $(item).attr("relpos");
									 arregloIds.push(id);
								});

								if(idspostulantes.length < 1) {
									 $(".closeWM").click();
									 return;
								}

								$("#spEPLabel").html('Enviar los postulantes');
								$("#spEPNombrePostulante").html("");

								setTimeout( function() {
									$("#"+link).addClass("winModal");
									 $("#"+link).attr("href", "#winEnviarBolsa");

									 bolsacvsgeneral.traerGrupos(contenido, undefined, link, arregloIds);
								},500);

						  } else {
								$("#"+link).removeClass("winModal");
								$("#"+link).attr("href", "");
						  }
					 });
				},

				bntAgregarPostulanteGrupoClick : function(a) {
					 $(a).live("click", function(e) {
						  e.preventDefault();
						  idpos = undefined;

						  var link = $(this).attr("name");

						  if($(this).attr("href") == ""){

								$("#divBolsasEnviar").html('');
								var contenido = "#divBolsasEnviar";
								var idspostulantes = $(".dataGridEnviarBolsa tbody .data0").find("input:checked");
								var arregloIds = [];
								var id = 0;
								$.each(idspostulantes, function(index,item) {
									 id = $(item).attr("relpos");
									 arregloIds.push(id);
								});

								if(idspostulantes.length < 1) {
									 $(".closeWM").click();
									 bolsacvsgeneral.mostrarMensaje(".dvMensajeAccion", "error", "Debe seleccionar al menos un postulante");
									 return;
								}
								$("#spEPLabel").html('Enviar los postulantes');
								$("#spEPNombrePostulante").html("");

								//Si hay postulantes
								if( $('#dataProcesoBusqueda').find('td').size()>0){
								$("#"+link).addClass("winModal");
								$("#"+link).attr("href", "#winEnviarBolsa");
								}
								bolsacvsgeneral.traerGrupos(contenido, undefined, link, arregloIds);
						  } else {
								$("#"+link).removeClass("winModal");
								$("#"+link).attr("href", "");

						  }


					 });
				},

				lnkAgregarPostulanteClick : function(a) {

					 $(document).on("click", a, function(e) {
						  e.preventDefault();
						  var idpostulante = $(this).attr("rel");
						  idpos = idpostulante;

						  var link = $(this).attr("name");
						  if($(this).attr("href") == ""){
								var contenido = "#divBolsasEnviar";
								var nombre = $(this).parents("tr").find(".verPerfilFilPerfP_Nombre").html();
								if(nombre == undefined) {
									 var nombre = $(".namePCP").html() + " " + $(".apellPCP").html();
								}
								$("#spEPLabel").html('Enviar al postulante');
								$("#spEPNombrePostulante").html('"'+nombre+'"');

								$("#"+link).addClass("winModal");
								$("#"+link).attr("href", "#winEnviarBolsa");

								bolsacvsgeneral.traerGrupos(contenido, idpostulante, link);
						  } else {
								$("#"+link).removeClass("winModal");
								$("#"+link).attr("href", "");
						  }
					 });
				},

				lnkBloquearPostulanteClick : function(a){
					 $(a).live("click",function(e){
						  e.preventDefault();
						  if(!$(this).parent().hasClass("dsbl")){
								var nombre = $(".namePCP").html() + " " + $(".apellPCP").html(),
									 idpostulante = $(this).attr("rel"),
									 idpostulacion=$(this).attr("idpostulacion"),
									 that=$(this);
								if(that.attr("href") == "javascript:;"){
									 $("#spBLNombrePostulante").html(nombre+"&nbsp;");
									 $("#btnBloquearPos").attr("rel",idpostulante).attr("idpostulacion",idpostulacion);
									 that.addClass("winModal");
									 that.attr("href", "#winBloquearPos");
									 that.trigger("click");
								} else {
									 that.removeClass("winModal");
									 that.attr("href", "javascript:;");
								}
						  }
					 });
				},

				agregarPostulante : function(a) {
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
						  var idspostulantes = $(".dataGridEnviarBolsa tbody .data0").find("input:checked");
						  var arreglo_idspostulantes =[];
						  var id = 0;
						  if (idpos==undefined) {
								$.each(idspostulantes, function(index,item) {
									 id = $(item).attr("relpos");
									 arreglo_idspostulantes.push(id);
								});
						  } else {
								arreglo_idspostulantes.push(idpos);
						  }
						  var json = {
								"idsPostulantes": arreglo_idspostulantes,
								"idsGruposDestino": arreglo_idsgrupos,
								"tokenBolsa":$("#tokenBolsa").val(),
								"idAviso":$("#idAviso").val()
						  };
						  $.ajax({
								type: "POST",
								url: "/empresa/bolsa-cvs/agregar-postulante",
								data: json,
								dataType: "json",
								success: function(msg) {
									 if(msg.status == 'ok'){
										  $(".closeWM").click();
										  bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.msg);
										  bolsacvsgeneral.mostrarMensaje(".dvMensajeAccion", "success", msg.msg);
									 }else {
										  bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.msg);
										  bolsacvsgeneral.mostrarMensaje(".dvMensajeAccion", "success", msg.msg);
									 }
								}
						  });
					 });
				},
				bloquearPostulante : function(a) {
					 var flag=true,
						  load=function(cond){
								var load=$("#winBloquearPos .loading"),
								content=$("#cntBloquear");
								if(cond){
									 load.removeClass("hide");
									 content.hide();
								}else{
									 load.addClass("hide");
									 content.show();
								}

						  };
                $(a).live("click", function(e) {
						  e.preventDefault();
						  var that=$(this),
								idpostulante=$(this).attr("rel"),
								idpostulacion=$(this).attr("idpostulacion"),
								data={
									 "postulante-id":idpostulante,
									 "idAviso": $("#idAviso").val()
								};
						  if(flag){
								flag=false;
								load(true);
								$.ajax({
									 type: "POST",
									 url: "/empresa/mis-procesos/bloquear-postulante",
									 "data": data,
									 dataType: "json",
									 success: function(msg) {
										  $(".closeWM").click();
										  if(msg.estado){
												//bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "success", msg.mensaje);
												$("#perfilContainer").remove();
												$(".verPerfilFilPerfP[rel='"+idpostulacion+"']").trigger("click");
										  }else {
												bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "error", msg.mensaje);
										  }
										  flag=true;
										  load(false);
									 },
									 error:function(xhr, ajaxOptions, thrownError){

										  $(".closeWM").click();
										  bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "error", "Ocurrio un error en la petición. Intente nuevamente.");
										  flag=true;
										  load(false);
									 }
								});
						  }
					 });

				},
				traerGrupos : function(contenido, idpostulante, link, idpostulantes) {
					 var data = {
								"idPostulante":idpostulante,
								"idPostulantes":idpostulantes,
								"idAviso":$("#idAviso").val()
					 };
					 /*
					 var data = "idPostulante="+idpostulante;
					 if(idpostulante == undefined){
						  data = "";
					 }
					 */
					 $.ajax({
						  type: "POST",
						  url: "/empresa/bolsa-cvs/get-grupos",
						  data: data,
						  dataType: "html",
						  success: function(msg) {
								$(contenido).html(msg);

								$(':checkbox', '#chkAllSendCv').on('click',function(){
									 $(':checkbox','#divBolsasEnviar').attr('checked',this.checked);
								});

								if(bolsacvsgeneral.trim(msg).length == 0) {
									 if (link != undefined) {
										  $("#"+link).removeClass("winModal");
										  $("#"+link).attr("href", "");
									 }
									 $(".closeWM").click();
									 var msj = "El postulante ya se encuentra agregado en todos los grupos.";
									 if (idpostulantes != undefined) {
										  msj = "Los postulantes seleccionados ya se encuentran agregados en todos los grupos.";
									 }
									 bolsacvsgeneral.mostrarMensaje(".dvMensajesBolsaCVs", "info", msj);
								} else {
									 if (link != undefined) {
										  $("#"+link).click();
									 }
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

				trim: function(stringToTrim) {
					 return stringToTrim.replace(/^\s+|\s+$/g,"");
				}
		  };
	//init

		  bolsacvsgeneral.bntAgregarPostulanteGrupoClick(".enviarABolsa");
		  bolsacvsgeneral.lnkAgregarPostulanteClick(".envPostulanteABolsa");
		  bolsacvsgeneral.lnkAgregarPostulanteGrupoClick(".enviarBolsaGrupo");
		  bolsacvsgeneral.lnkBloquearPostulanteClick(".blcandidate");
		  bolsacvsgeneral.agregarPostulante("#btnAceptarEnviarBolsa");
		  bolsacvsgeneral.bloquearPostulante("#btnBloquearPos");

		  if(yOSON.action == 'perfil-publico-emp' && yOSON.controller == 'buscador-aptitus'){
				bolsacvsgeneral.traerGrupos(contenido, idpostulante, link);
		  }
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
