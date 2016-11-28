/*jqClasificados*/
(function($){var methods={init:function(options){var defaults={slideElement:"#slide-gec",speed:250,horizontal:true,ie6ChildBG:"transparent",ie6ChildBGHover:"transparent"},opts=$.extend({},defaults,options);
opts.isIE=!$.support.opacity&&!$.support.style;opts.isIE6=opts.isIE&&!window.XMLHttpRequest;return this.each(function(){if(opts.horizontal==true){opts.measureSlideElement=$(opts.slideElement).outerHeight()+"px";
opts.direction="height";}else{opts.measureSlideElement=$(opts.slideElement).outerWidth()+"px";opts.direction="width";}var $this=$(this),data=$this.data(opts.slideElement),clasificados=function(){$this.hover(function(){$(opts.slideElement).slideDown().clearQueue().stop().css(opts.direction,opts.measureSlideElement);
$this.css("background-position","90px -8px");},function(){$(opts.slideElement).delay(250).slideUp();$this.css("background-position","90px 10px");});$(opts.slideElement).hover(function(){if(opts.isIE6){$(opts.slideElement).children().hover(function(){$(this).css("background",opts.ie6ChildBGHover);
},function(){$(this).css("background",opts.ie6ChildBG);});}$(opts.slideElement).clearQueue().stop().css(opts.direction,opts.measureSlideElement);$this.css("background-position","90px -8px");
},function(){$(opts.slideElement).delay(250).slideUp();$this.css("background-position","90px 10px");});};if(!data){$(this).data(opts.slideElement,{target:$this,clasificados:clasificados});
}$(this).data(opts.slideElement).clasificados();});},destroy:function(){},update:function(){}};$.fn.clasificados=function(method){if(methods[method]){return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
}else{if(typeof method==="object"||!method){return methods.init.apply(this,arguments);}else{$.error("Method "+method+" does not exist on jQuery.clasificados");
}}};})(jQuery);
/* Json to String */
jQuery.extend({
	stringify  : function stringify(obj) {
		var t = typeof (obj);
		if (t != "object" || obj === null) {
			if (t == "string") obj = '"' + obj + '"';
			return String(obj);
		} else {
			var n, v, json = [], arr = (obj && obj.constructor == Array); 
			for (n in obj) {
				v = obj[n];
				t = typeof(v);
				if (obj.hasOwnProperty(n)) {
					if (t == "string") v = '"' + v + '"'; else if (t == "object" && v !== null) v = jQuery.stringify(v);
					json.push((arr ? "" : '"' + n + '":') + String(v));
				}
			}
			return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
		}
	}
});

/**----------------------------------------------------------------------------------------------------
 * @Object Cookie
*//*-------------------------------------------------------------------------------------------------*/
var Cookie = {
	create: function(name,value,days) {
		if(days){
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}else{var expires = "";}
		document.cookie = name+"="+value+expires+"; path=/";
		return this;
	},
	read: function(name){
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++){
			var c = ca[i];while(c.charAt(0)==' '){c=c.substring(1,c.length);}
			if(c.indexOf(nameEQ)==0){return c.substring(nameEQ.length,c.length);}
		}return null;
	},
	del: function(name){return this.create(name, "", -1)},
	mantJson:function(name,indice,opt,json){
		// opt:1 Crear y/o Agregar  opt:0 Deletear
		var that=this,
			json = (json === "undefined")?"{}":json,
			result = (that.read(name)!==null&&that.read(name)!="")?((new Function('return '+that.read(name)+';'))()):{};
		result[indice]=(opt)?json:null;st.selTipoCarrera
		that.create(name,$.stringify(result));
		return result;
	},
	readJson:function(name){
		var that=this;
		if(that.read(name)!==null){
			return (new Function('return '+that.read(name)+';'))();
		}
		return null;
	}
};

//Apt
var msgs = {
	cDef : {
		good :'.',
		bad : 'Campo Requerido',
		def :'Opcional'
	},
	cPass : {
		good : '.',
		bad : 'Debe ingresar una contraseña válida.',
		def : 'Ingresa tu contraseña'
	},
	cEmail : {
		good : '.',
		bad : 'Debe ingresar su dirección e-mail.',
		def : 'Ingrese mail correcto'
	},
	passForgot : {
		good : 'Las instrucciones fueron enviadas a la dirección de email.',
		mailInvalid : 'El e-mail ingresado debe estar asociado a una cuenta de postulante.',
		inActive : 'Su cuenta se encuentra bloqueada temporalmente. Para mayor información comuníquese a '+'<a href="mailto:soporte@aptitus.com.pe" >soporte@aptitus.com.pe</a>',
		empresaMailInvalid : 'El e-mail ingresado debe estar asociado a una cuenta de empresa.',
		bad : 'Error al enviar su nueva contraseña.',
		novalidEmail:'El e-mail ingresado debe estar asociado a una cuenta'
	},
	emailForgot : {
		good : '.',
		goodEmp : 'EI correo registrato para el RUC',
		invalid : '&nbsp;&nbsp;&nbsp;Tu DNI no se encuentra registrado en APTiTUS.<br>Unete al portal más grande de empleo,registraste '+'<a href="/registro" >aqui</a>',
		empInvalid : '&nbsp;&nbsp;&nbsp;Tu RUC no se encuentra registrado en APTiTUS.<br>Unete al portal más grande de empleo,registraste '+'<a href="/empresa/registro-empresa" >aqui</a>',
		bad : 'Error al enviar su nueva contraseña.',
		badpos : 'Debe introducir DNI válida.',
		bademp : 'Debe introducir RUC válida.'
	},
	cRazonSocial : {
		good : '.',
		bad : '¡Se requiere Razon Social!',
		def : 'Ingrese su Razon Social',
		Valid : 'Razon Social ya registrada.',
		incorrect : '¡Razon Social incorrecta!'
	},
	ajaxData : {
		error : 'Datos inválidos.',
		good : 'Datos correctos',
		mailValid : 'Email ya registrado.',
		mailGood : 'Email disponible.'
	}                       
};

/*
  JS main Aptitus 
 */

var aptitusMethods = {
	/**
	 * Funcion para validar un input o validar un formulario(contexto)
	 * @return {null}
	 */
	validateAll : function(){
		var dom = {},
		st = {
			inputTag    : 'input',
			type        : '',
			classResp   : '.response',
			messageOk   : '¡Genial!',
			messageBad  : 'No parece ser un campo válido.',
			messageReq  : 'Campo Requerido',
			isForm      : false,
			context     : null,
			inputs      : {},
			btnSubmit   : null,
			onAfterValid : function(){}
		},
		catchDom = function() {
			dom.context     = $(st.context);
			dom.btnSubmit   = $(st.btnSubmit, st.context);
		},
		suscribeEvents = function(){
			if(st.isForm){
				dom.context.on('submit', sendForm);
			}else{
				$(st.inputTag, st.context).on('paste', validatePaste);
				$(st.inputTag, st.context).on('keyup', validateKey);
			}
		},
		sendForm = function(){
			var collection  = [],
				position    = 0,
				fixIE       = function(){
					$('textarea[placeholder],:text[placeholder]',st.context).trigger('blur');
				},
				validateRegex = function(uid,obj){
					st.type = obj.type;

					var _this = $("#"+uid),
						value = $.trim(_this.val()),
						regEx = optionType(),
						message = _this.siblings(st.classResp),
						localName = document.getElementById(uid).tagName,
						position = parseInt(_this.offset().top) - 20;

					if(_this.is(":disabled")) return false;

					if((localName == 'INPUT' && value !== '') || (localName == 'SELECT' && value !== '0' )){

						//Buscar exepciones especiales
						if(_this.hasClass('bad-data')){
							message.addClass('bad').removeClass('good def').text('Incorrecto');
							collection.push(position);
						}else{
							// No tiene la clase bad-data
							if(obj.type !== undefined && !value.match(regEx)){
								message.addClass('bad').removeClass('good def').text(st.messageBad);
								collection.push(position);
							}else{
								message.text('');
							}
						}
					}else{
						if(obj.require !== undefined && obj.require){
							message.addClass('bad').removeClass('good def').text(st.messageReq);
							collection.push(position);
						}else{
							message.text('');
						}
					}
				};


			$.each(st.inputs, function(uid,obj){
				if(document.getElementById(uid) !== null)
					validateRegex(uid,obj);
			});


			if(collection.length !== 0){
				$("html, body").animate({ scrollTop: collection[0] }, 400);
				return false;
			}

			return st.onAfterValid(dom);
		},
		optionType = function(){
			switch(st.type){
				case "number":
					regEx = /[^0-9]/g;
				break;
				case "letter":
					regEx = /[^a-zA-Z ñáéíóúÑÁÉÍÓÚ]/g;
				break;
				case "decimal":
					regEx = /[^0-9\.]/g;
				break;
				case "email":
					regEx = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
				break;
				case "celular":
					regEx = /^(\#[0-9]{6}|\*[0-9]{6}|[0-9]{7}|\#?[0-9]{9}|[0-9]{2}\*[0-9]{3}\*[0-9]{4})$/gi;
				break;
				case "phone":
					regEx = /^(\+){0,1}(\d|\s|\(|\)){7,10}$/;
				break;
				case "url":
					regEx = /^(http:\/\/)?([a-z0-9\-]+\.)?[a-z0-9\-]+\.[a-z0-9]{2,4}(\.[a-z0-9]{2,4})?(\/.*)?$/i;
				break;
				case "phoneAll":
					regEx = /[^0-9 \.\(\)\-]/g;
				break;
				default:
					regEx = /[^a-zA-Z0-9 ñáéíóúÑÁÉÍÓÚ\@\&\_\.\,\-\(\)?\n]/g;
			}
			return regEx;
		},
		validatePaste = function(e){
			var $this = $(this),
				regEx = optionType();

			setTimeout(function(){
				var value = $this.val();
				if (value.match(regEx)) {
					$this.val(value.replace(regEx, ''));
				}else{
					return false;
				}
			},100);
		},
		validateKey = function(e){
			var $this = $(this),
				regEx = optionType(),
				value = $this.val();

			if (value.match(regEx)) {
				$this.val(value.replace(regEx, ''));
			}else{
				return false;
			}
			e.preventDefault();
		},
		initialize = function(oP) {
			$.extend(st, oP);
			catchDom();
			suscribeEvents();
		};
		return {
			init: initialize
		};
	}
};

$(function() {
	$('#formReg input').on('keyup paste',function(e) {
		var $this = $(this),
			regEx = /(<[^>]*>)/g;
		if(e.type == 'keyup'){
			var value = $this.val();
			if (value.match(regEx)) {
				$this.val(value.replace(regEx, ''));
			}else{
				return false;
			}
			e.preventDefault();
			return false;
		}else if(e.type == 'paste'){
			setTimeout(function(){
				var value = $this.val();
				if (value.match(regEx)) {
					$this.val(value.replace(regEx, ''));
				}else{
					return false;
				}
			},100);
		}
	});

	//suspende avisos preferenciales
	if(urls.pprf == 0){
		if(location.href.indexOf('empresa/publica-aviso-preferencial/') != '-1'){
			$('body').addClass('hide');
			location.href = urls.siteUrl + '/empresa/publica-aviso/';
		}else if(location.href.indexOf('admin/publicar-aviso-preferencial/') != '-1'){
			$('body').addClass('hide');
			location.href = urls.siteUrl + '/admin/publicar-aviso/';
		}else{
			$('body').removeClass('hide');
		}
		var blkAvsPref = $('#section3BW'),
		blkHideH = $('#heightGHT', blkAvsPref),
		blkBtnH = $('#heightTHG', blkAvsPref);
		blkAvsPref.addClass('noCursorActive');
		blkHideH.css('display','block');
		blkBtnH.remove();
	}
	//setupAjax
	$(this).ajaxError(function(e, xhr, settings) {
		if( xhr.responseText == '<script languaje="Javascript">location.reload(true);</script>' ){
			location.reload(true);
		}
		
		if( xhr.status == 401 ){
			location.reload(true);
		}
	});

	var vars = {
		rs : '.response',
		okR :'ready',
		sendFlag : 'sendN',
		loading : '<div class="loading"></div>'
	};
	//class 
	var Aptitus = function(opts){
        //window modal and alert
        this.placeholder = function(){
            var tr = $('input.placeH, textarea.placeH');
            tr.focus(function(){
                var t = $(this);
                if(t.val() == t.attr('alt')){
                    t.val('').removeClass('cGray');
                }
            });		 
            tr.blur(function(){
                var t = $(this);			
                if(t.val() == ''){
                    t.val(t.attr('alt')).addClass('cGray');
                }
            });
        };	
        this.flashMsg = function(){
            var mensajes = $('.flash-message'),
            s = 'middle',
            interval = '15000';
            $.each(mensajes, function(k, v){
            	var h2 = (1000 * k);
            	if($(v).hasClass('errorout')){
            		interval = '9000';
            	}else{
            		interval = interval;
            	}
                setTimeout(function(){
                    $(v).fadeIn(s, h2, function(){
                        setTimeout(function(){
                        	if(!$(v).hasClass('msgVisible')){
                        		$(v).fadeOut(s);
                            }
                        }, h2 + interval);
                    });
                },h2);
            });		
        };
		this.placeholderRel = function(){
			var tr = $('input.placeHRel, textarea.placeHRel'),
			trText = $('.txtPlaceHR');
			tr.bind('focus', function(){
				var t = $(this),
				txtPlaceH = t.parents('.placeHRel').find('.txtPlaceHR'),
				textP = txtPlaceH.text();
				if($.trim(t.val()) == ''){ 
					txtPlaceH.addClass('hide'); 
				}
			});		 
			tr.bind('blur', function(){
				var t = $(this),
				txtPlaceH = t.parents('.placeHRel').find('.txtPlaceHR'),
				textP = txtPlaceH.text();						
				if($.trim(t.val()) == ''){ 
					txtPlaceH.removeClass('hide').text(textP); 
				}
			});
			trText.bind('click', function(){
				var t = $(this),
				inputPlaceH = t.parents('.placeHRel').find('.placeHRel'),
				textP = t.text();						
				if($.trim(inputPlaceH.val()) == ''){ 
					t.addClass('hide');
					inputPlaceH.focus(); 
				}
			});		
		};			
		this.tooltipApt = function(){
			var trigger = $('.tooltipApt');
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
				t.attr('alt','');
				//centrado cachito
				var newTool = $('body > .tooltipCnt'),
				wTool = newTool.innerWidth(); 
				newTool.css('left',posLeft - (wTool/2) + (t.innerWidth()/2));
			}).mouseleave(function(){
				var t = $(this);
				$('.tooltipCnt').remove();
			});
		};
		this.postulantesDesbloquear = function(){
			var loader=function(cond){
					var load=$("#winDesBloquearPos .loading"),
						content=$("#cntDesBloquear");
					if(cond){
					   load.removeClass("hide");
					   content.hide();
					}else{
						load.addClass("hide");
						content.show();
					}
				},
				txtModal=$("#txtdesbl"),
				asignText=function(cond,name){
					if(cond){
						txtModal.html("¿Está seguro de desbloquear permanentemente el postulante <span class='bold'>"+name+"</span> de todos los procesos?");
					}else{
						txtModal.html("¿Desea desbloquear los postulantes seleccionados de todos los procesos?");
					}                    
				};
			/*desbloquear un registro */
			$('.lnkdsbl').bind('click', function(){
				asignText(1,$(".nameFilPerfP",$(this).parents("tr")).text());
				$('.btndesbloquear').attr('rel',$(this).attr('rel')).attr("cond",0);
			});
			/* desbloquear varios registros */
			var chkAll  = $("#dataProcesoPostulacion th input[name='selectAll']"),         //Check all
				allChks = $("#dataProcesoPostulacion tbody tr td input[type='checkbox']"), //todos los checks
				btnDesBl= $('#btnDesbloqVarios');                                          //botton desbloquear varios            
			/*Desbloquear marcados*/
			btnDesBl.bind('click', function(){
				asignText(0);
				$('.btndesbloquear').attr("cond",1);
			});
			//Btn Desbloquear
			$('.btndesbloquear').bind('click', function(){
				var _this=$(this);
				loader(true);
				if(parseInt(_this.attr("cond"))==1){
					var allChksChk = {}, aTrSelector=[];
						allChksChk.id=[];
					$("#dataProcesoPostulacion tbody tr td input[type='checkbox']:checked").each(function(i,chk){
						allChksChk.id.push($.trim($(chk).attr('name').split('-')[1]));
						aTrSelector.push('#tr-'+allChksChk.id[i]);
					});
					$.ajax({
						url     : urls.siteUrl+'/empresa/postulantes-bloqueados/desbloquear-varios',
						type    : 'POST', data:allChksChk,
						success : function(res){
							$("#winDesBloquearPos .cancelHEPA").trigger('click');
							loader(false);
							window.location.reload();
						}
					});
				}else{
					var id = $.trim(_this.attr('rel'));                
					$.ajax({
						url     : urls.siteUrl+'/empresa/postulantes-bloqueados/desbloquear/postulante-id/'+id,
						type    : 'GET',
						success : function(res){
							$("#winDesBloquearPos .cancelHEPA").trigger('click');
							loader(false);
							window.location.reload();
						}
					}); 
				}             
			});
			/* Ocultando boton desbloquear segun sea el caso */
			btnDesBl.css('visibility', 'hidden');
			allChks.each(function(i,chk){
				if($(chk).prop('checked')){ btnDesBl.css('visibility', 'visible'); return false }
			});
			
			chkAll.bind('click', function(){
				allChks.prop('checked', $(this).prop('checked')); 
				btnDesBl.css('visibility', $(this).prop('checked')?'visible':'hidden');
			});
			
			allChks.bind('click', function(){
				if($("#dataProcesoPostulacion tbody tr td input[type='checkbox']:checked").length>0) btnDesBl.css('visibility', 'visible');
				else btnDesBl.css('visibility', 'hidden');
			});
		};
		this.carrousel = function(){
			$slide = $('#slider-code');
			if($slide.size() > 0){

				$slide.tinycarousel({
					duration : 500,
					callback: function(element, index){
						$slide.find(".overview,.buttons").fadeIn();
						$slide.find(".viewport").css("background","none")
					}
				});
			}
		}
		this.alignBottom = function(){
			//Para Tipos de Membresía APTiTUS
			var blok1 = '#iBlock1S2MAP',
				blok2 = '#iBlock2S2MAP',
				blok3 = '#iBlock3S2MAP',
				total = 0;

			//total = 
			if($('.advBlockTypes').size() > 0){
				Array.prototype.max = function () {
					return Math.max.apply(Math, this);
				};
				var max = [parseInt($(blok1).outerHeight()),
							parseInt($(blok2).outerHeight()),
							parseInt($(blok3).outerHeight())].max();
				
				$(blok1+","+blok2+","+blok3).css('height',(max+20)+'px');
			}
		}
	};



	// init
	var aptitus = new Aptitus();
	aptitus.placeholder();
	aptitus.placeholderRel();

	aptitus.flashMsg();
	aptitus.tooltipApt();
	aptitus.postulantesDesbloquear();  //empresa/postulantes/bloqueados
	aptitus.carrousel();

	aptitus.alignBottom();

	//Banner clasificados
	if($('a.link-gec').length !== 0){
	$('a.link-gec').clasificados({slideElement: '#slide-gec',ie6ChildBG: '#FFFFFF',ie6ChildBGHover: '#F3F3F3'});
	}


	aptitusMethods.validateAll().init({
		inputTag: '.onlyNum',
		type    : 'number'
	});
	
	//Close box message
	$(".box-message .icon-close").on('click',function(){
		$(this).parent().remove();
	});
	
	if (window.location.hash == '#_=_') {
		window.location.hash = ''; 
		history.pushState('', document.title, window.location.pathname); 
	}

	$('a[data-anchor]').on('click', function(){
		var aTag = $($(this).data('anchor'));
		$('html,body').animate({scrollTop: aTag.offset().top},'slow');
	})

	if (!$.support.cors) {
		$('textarea[maxlength]').keyup(function(){
		    //Get the value
		    var text = $(this).val();
		    //Get the maxlength
		    var limit = $(this).attr('maxlength');
		    //Check if the length exceeds what is permitted
		    if(text.length > limit){
		        //Truncate the text if necessary
		        $(this).val(text.substr(0, limit));  
		    }
		});
	}
		
});
