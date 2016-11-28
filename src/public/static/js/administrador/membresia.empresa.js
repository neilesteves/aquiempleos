/* 
membresia
 */

$( function() {
	var onlyNum = function(a){
		return $(a).each( function(){
			var t = $(this),
			isShift = false;
			t.keypress( function(e){				
					var key = e.keyCode || e.charCode || e.which || window.e ;
					if(key == 16) isShift = true;						
					return ( 
							( key == 8 ) || ( key == 9 ) || ( key == 13 ) ||    
							( key == 39 ) ||    
							( key == 46 && isShift == false ) ||
							( key == 48 && isShift == false ) ||
							( key == 49 && isShift == false ) ||
							( key == 50 && isShift == false ) ||
							( key == 51 && isShift == false ) ||
							( key == 52 && isShift == false ) ||
							( key == 53 && isShift == false ) ||
							( key == 54 && isShift == false ) ||
							( key == 55 && isShift == false ) ||
							( key == 56 && isShift == false ) ||
							( key == 57 && isShift == false ) 						
							);							
			});
			t.bind('paste', function(){
				setTimeout(function() {
					var value = t.val();
					var newValue = value.replace(/[^0-9]/g,'');
					t.val(newValue);
				}, 0);
			});		
		});
	};

	$.datepicker.setDefaults(
		$.extend({showMonthAfterYear: false}, $.datepicker.regional['es'])
	);
	var ctrlMemb = $('#id_membresia'),
		ctrlTipo = $('#cbotipo'),
		ctrlFecI = $('#txtfecini'),
		ctrlFecF = $('#txtfecfin'),
		ctrlMnto = $('#txtmonto'),
		ctrlEsta = $('#cboestado');

	ctrlEsta.live('change', function(){
		validShow(this, 'Campo Requerido');
	});
   
	ctrlTipo.live('change', function(){
		validShow(this, 'Campo Requerido');
	});
	
	ctrlMemb.live('change', function(){
		validShow(this, 'Campo Requerido');
	});
	
	ctrlFecI.live('focusout', function(){
		validShow(this, 'Fecha Incorrecta');
	});
	ctrlFecF.live('focusout', function(){
		validShow(this, 'Fecha Incorrecta');
	});
	ctrlMnto.live('focusout', function(){
		validShow(this, 'Ingresar Monto');
	});
	
	function validShow(ctrl, msj){
		var obj = $(ctrl);

		if(obj.val()){
			obj.next().removeClass('bad');
			obj.next().html('');
		}else{
			obj.next().addClass('bad');
			obj.next().html(msj);
		}
	}
	
   var membresiaEmp = {    
		loadDates : function() {
			var fecini = $('#txtfecini'),
				fecfin = $('#txtfecfin'),
				routeImage = $("#txturlimageC");
			
			fecini.datepicker({
				dateFormat: 'dd/mm/yy',
				minDate: '-2Y 0M 0D',
				maxDate: '+3Y', //new Date(),
				changeMonth: true,
				changeYear: true
				,onSelect: function(dateText, inst) {
					validShow(this, 'Fecha Incorrecta');
				}
			});
			fecfin.datepicker({
				dateFormat: 'dd/mm/yy',
				minDate: 0, //-20,
				maxDate: '+3Y', //'+1M +10D',
				changeMonth: true,
				changeYear: true
				,onSelect: function(dateText, inst) {
					validShow(this, 'Fecha Incorrecta');
				}
			});
		},
		
		registroMembresia : function() {
			//this.listaMembresia();
			var cboMem = $('#id_membresia'),
				eMem = $('.editMembAdmin'),
				btCancelM = $('.btnCancelOM'),
				btAceptM = $('.btnAceptOM'),
				aExitM = $('.aCloseWinOM');
			
			//Boton que cancela el modal
			/*btCancelM.live('click',function(){
				aExitM.trigger('click');
			});*/

			//Es el select dependiente de cbotipo
			/*cboMem.live('change', function(){
				var valueM = $(this).val();
				var contenido = $("#divContentDetailsM");
				contenido.html("");
				contenido.addClass("loading center");
				$.ajax({
					type: "POST",
					url: "/admin/membresia-empresa/get-data-membresia",
					data: {
						idMem : valueM
					},
					dataType: "html",
					success: function(data) {
						contenido.html("");
						contenido.removeClass("loading hide");
						contenido.html(data);
						$('#txtmonto').val(formato_numero($('#txtMontoMem').val(),2,'.',''));
						validShow($('#txtmonto'), 'Ingresar Monto');
					}
				});
			});*/

			//Es el boton que ejecuta del modal
			/*eMem.live('click', function(){
				var msjcont = $("#content-winAddMemb"),
					idM = $(this).attr('rel'),
					idEmp = $(this).attr('idEmp');
				msjcont.html("");
				msjcont.addClass("loading");
				$.ajax({
					type: "GET",
					url: "/admin/membresia-empresa/opera-membresia",
					data: {idMem : idM, idEmp : idEmp},
					dataType: "html",
					success: function(data) {
						msjcont.html("");
						msjcont.removeClass("loading hide");
						msjcont.html(data);
						membresiaEmp.loadDates();
						onlyNum('#txtmonto');
					}
				});
			});*/
			
			//Es el boton Aceptar del modal
			/*btAceptM.live('click',function(){
				var msjcont = $("#content-winAddMemb"),
					frmMemb = $("#frmMembresiaE").serialize(),
					aExiM = $('.aCloseWinOM');
				msjcont.html("");
				msjcont.addClass("loading center");
				$.ajax({
					type: "POST",
					url: "/admin/membresia-empresa/opera-membresia",
					data: frmMemb,
					dataType: "html",
					success: function(data) {
						msjcont.html("");
						msjcont.removeClass("loading hide");
						msjcont.html(data);
						
						membresiaEmp.loadDates();
						
						var msjCntStatus = $('#cntMsjStatus');
						
						if(msjCntStatus.text().indexOf('Error') != -1 ){
							msjCntStatus.removeClass('good').addClass('bad');
						}else{
							msjCntStatus.removeClass('bad').addClass('good');
						}                        
						
						var ok = $('#process');
						if(ok.val()=='1'){
							listaMembresia();
							validBtnMemb();
							
							setTimeout(function(){
								aExiM.trigger('click');
								location.reload();
							}, 1000);
						}
					}
				});
			});*/
			
			//Es el combo dependiente del tipo de membresia
			/*var tipMem = $('#cbotipo');
			tipMem.live('change', function(){
				var idtip = $(this).val(),
					cadOptions = '',
					cboM = $('#id_membresia');
			   
				$.ajax({
					type: "POST",
					url: "/admin/membresia-empresa/get-membresias-tipo",
					data: {
						idtipo : idtip
					},
					dataType: "JSON",
					success: function(data) {
						cadOptions = '<option selected label=".:: Seleccione ::." value="">.:: Seleccione ::.</option>';
						$.each(data, function(key, val){
							cadOptions += '<option label="'+val+'" value="'+key+'">'+val+'</option>';
						});

						cboM.html(cadOptions);
						$('#divContBenefM').remove();
						$('#txtMontoMem').remove();
						$('#txtmonto').val('');
					}
				});	
			});*/
			
			function listaMembresia(){
				var content = $("#divAjaxMembEmp");
					content.html("");
					content.addClass("loading center");
				var eMem = $('#btnAddMembresiaE');
				$.ajax({
					type: "POST",
					url: "/admin/membresia-empresa/lista-membresias",
					data: {
						paramIdE : eMem.attr('idEmp')
					},
					dataType: "html",
					success: function(data) {
						content.html("");
						content.removeClass("loading hide");
						content.html(data);
					}
				});
			}
			
			function validBtnMemb(){
				var btAdMemb = $("#btnAddMembresiaE");
				$.ajax({
					type: "POST",
					dataType: "JSON",
					url: "/admin/membresia-empresa/get-valid-membresia",
					data: {
						paramIdE : btAdMemb.attr('idEmp')
					},
					success: function(data) {
						btAdMemb.removeAttr('class');
						if(data.active){
							btAdMemb.attr('href','#');
							btAdMemb.addClass('sptEmp btnSptEmp inactivo right');
						}else{
							btAdMemb.attr('href','#winAddMemb');
							btAdMemb.addClass('editMembAdmin winModal noScrollTop sptEmp btnSptEmp right');
						}
					}
				});
			}
			
			/*
			 * Da formato a un número para su visualización
			 *
			 * numero (Number o String) - Número que se mostrará
			 * decimales (Number, opcional) - Nº de decimales (por defecto, auto)
			 * separador_decimal (String, opcional) - Separador decimal (por defecto, coma)
			 * separador_miles (String, opcional) - Separador de miles (por defecto, ninguno)
			 */
			/*function formato_numero(numero, decimales, separador_decimal, separador_miles){
				numero=parseFloat(numero);
				if(isNaN(numero)){
					return "";
				}
				if(decimales!==undefined){
					// Redondeamos
					numero=numero.toFixed(decimales);
				}
				// Convertimos el punto en separador_decimal
				numero=numero.toString().replace(".", separador_decimal!==undefined ? separador_decimal : ",");
				if(separador_miles){
					// Añadimos los separadores de miles
					var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
					while(miles.test(numero)) {
						numero=numero.replace(miles, "$1" + separador_miles + "$2");
					}
				}
				return numero;
			}*/
		}
   };
   
	//Nuevo pedido aptitus v2    
	//membresiaEmp.registroMembresia();
});
