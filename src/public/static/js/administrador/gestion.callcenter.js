$( function() {
		var msgErrors = {
				email : {
						empty : "Ingrese un Email",
						bad   : "Email no válido!",
						good  : "Email OK!"
				}
		};
		var callcenter = {
				llamadaCallcenter : function(a,b) {
						var actual = $(a);
						actual.live("click", function(){
								//$(b).addClass("loading");
								var idempresa = $(this).attr("rel");
								var total_llamadas = parseInt($(this).attr("totalllamadas"))+1;
								$(this).attr("disabled","disabled");

								$.ajax({
									type: "POST",
									url: "/admin/gestion/llamada-cliente",
									data: "idempresa="+idempresa,
									dataType: "html",
									success: function(msg){
										var reg = "<div class='bold mB5 hide'>Contacto "+'<span class="countLlamada">' +(total_llamadas)+"</span> : "+msg+"</div>", cntItem = $(b),
										last5 = cntItem.children().eq(4);
										last5.fadeIn('slow',function(){
												last5.remove();
										});
										cntItem.prepend(reg);
										var countL = $('#listaContacts .countLlamada'),
										size = countL.size();
										for(var i = size; i >= 0; i--){
											countL.eq(size - i).text(i);
										}                        
										$(b+" div:first").fadeIn(1000, function(){
													$(reg).removeClass("hide");
													$(a).attr("totalllamadas",total_llamadas);
													$(a).removeAttr("disabled");
										});
										callcenter.chekeaTamanoLista("#listaContacts");
									}
								});
						});
				},
				chekeaTamanoLista : function (a) {
						if(!$(a).hasClass("overflowCallcenter")){
								var actual = $(a);
								if (actual.height()>300) {
										$(a).attr("style","height:300px; width:250px;");
										$(a).addClass("overflowCallcenter");
								} else {
										$(a).removeAttr("style");
										$(a).removeClass("overflowCallcenter");
								}
						}
				},
				start : function() {
						callcenter.llamadaCallcenter("#btnLlamadaCliente","#listaContacts");
						callcenter.chekeaTamanoLista("#listaContacts");

						$("#btnPublicarAnuncio").live("click", function(){
								location.href = '/admin/publicar-aviso/';
						});
				}
		};

		callcenter.start();

		
});