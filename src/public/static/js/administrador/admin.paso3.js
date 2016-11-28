/*
 mis procesos
 */
$( function() {
	var paso3 = {
		contadorPalabras : function(a,b,c,d) {
			var textarea = $(a),
			npalabras_estaticas = paso3.trimPerfect(paso3.pasa_a_espacios($(d).html())).split(" ").length,
			npalabras_actual = parseInt($(c).html()),
			npalabras_total = npalabras_actual,
			texto_actual = $(a).val(),
			palabra_estatica = $(d).text(),
			inicio=1,
			finTexto = 0;

			var palabrasArr,
			valorTxtArea;

			textarea.bind('keydown', function(e) {
				var valor = e.keyCode || window.e.charCode || window.e.which || window.e ;
				if (finTexto == 1) {
					if(valor) {
						if(valor == 8 || valor == 37 || valor == 39) {
							// borrado = 8
							finTexto = 0;
							return true;
						} else {
							return false;
						}
					}
				} else {
					finTexto = 0;
					return true;
				}
			});
			textarea.bind('keypress keyup', function(e) {
				var t = $(this);
				var maximo_palabras = Number($('#cantPalabbras').text()),
				palabrasPermitidas = maximo_palabras - npalabras_estaticas,
				texto = paso3.trimPerfect(paso3.pasa_a_espacios(textarea.val())),
				palabras = paso3.trimPerfect(paso3.pasa_a_espacios(texto));

				palabrasArr = palabras.split(" ");

				var npalabrasdoce = paso3.cuentamayordoce(palabrasArr),
				npalabras = npalabras_estaticas + palabrasArr.length + npalabrasdoce;
				if(texto!="") {
					inicio=0;
				} else {
					inicio=1;
				}
				npalabras_total = npalabras;
				
				//contador vista
				var palabrasValS = t.val();
				$(b).text(palabra_estatica+" "+paso3.pasa_a_espacios(palabrasValS));
				// fin vista
				
				var valor = e.keyCode || e.charCode || e.which || window.e;
				if(e.type = 'keyup') {

					if(npalabras_total > maximo_palabras) {

						finTexto = 1;

						valorTxtArea = t.val();
						
						if(valor) {
							if(valor == 8 || valor == 37 || valor == 39) {
								finTexto = 0;
								return true;
							} else {
								if( paso3.pasa_a_espacios(texto).split(' ').length > palabrasPermitidas ) {
									t.val( t.val().substring(texto.length - 1 , '') );
								}
								return false;
							}
						}
					} else {
						inicio==1;
					}

				} else if(e.type = 'keypress') {

					if(npalabras_total >= maximo_palabras) {

						finTexto = 1;
						if(valor) {
							if(valor == 8 || valor == 37 || valor == 39) {
								finTexto = 0;
								return true;
							} else {
								return false;
							}
						}
					} else {
						inicio==1;
					}
				}
				var conteoNum = $(c);
				if(inicio==1) {
					conteoNum.html(npalabras_actual-npalabras+1);
				} else {
					conteoNum.html(npalabras_actual-npalabras);
				}
							
				//$(b).text(palabra_estatica+" "+texto);
				
			});
			textarea.trigger("keypress");
		},
		cuentamayordoce : function(palabras) {
			var npalabras=0,
			i=0;
			for(i=0;i<palabras.length;i++) {
				if(palabras[i].length>11) {
					npalabras+=parseInt((palabras[i].length)/12);
				}
			}
			return npalabras;
		},
		pasa_a_espacios : function(palabra) {
			palabra = palabra.replace(/-+/g," ");
			palabra = palabra.replace(/_+/g," ");
			palabra = palabra.replace(/\.+/g," ");
			palabra = palabra.replace(/,+/g," ");
			palabra = palabra.replace(/ +/g," ");
			palabra = palabra.replace(/\++/g," ");
			return palabra;
		},
		eventos : function(a,b,c,d) {
			var npalabras_estaticas =  paso3.pasa_a_espacios($(c).html()).split(" ").length,
			npalabras_actual = parseInt($(d).html());
		},
		trimPerfect : function(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}
	};
	paso3.contadorPalabras("#areaCountCP","#txtAdvP3Emp .variable","#countCharP3","#txtAdvP3Emp .estatico");
	paso3.eventos("#areaCountCP","#countCharP3","#txtAdvP3Emp .estatico","#countCharP3");
});
