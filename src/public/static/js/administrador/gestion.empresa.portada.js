$(function(){
    var msgst = {
        cEdicionPortada : {
            quitar :'¿Está seguro que desea quitar la empresa de la portada?',
            agregar : '¿Está seguro que desea agregar la empresa a la portada?',
            noLogo : 'Para estar en portada la empresa debe tener logo.'
        },
        cMsjPortada: {
            quitar : 'Se quitó a la empresa de la portada.',
            agregar : 'Se agregó la empresa a la portada.'
        }
    };

    var fncTes = {
        edicionPortada: function(a){
            $(a).live('click', function(e,url) {
                e.preventDefault();
                var t = $(this);
                var action = t.attr('action');
                var idPost = t.attr('rel');
                var logo = t.attr('logo');
                var cntBtns = $('#inwinAlertBloquearEportada #btnQElimAdm');
                $('#winAlertBloquearEportada #titleQ').removeClass('alertError');
                cntBtns.removeClass('hide');
                if (t.hasClass('ponerEPor')) {
                    if(logo == 'on'){
                        $('#winAlertBloquearEportada #titleQ').html(msgst.cEdicionPortada.agregar);
                        url = '/admin/gestion/agregar-portada';
                        msj = msgst.cMsjPortada.agregar;
                        opc = action;
                    }else if(logo == 'off'){
                        //$('#winAlertBloquearEportada #titleQ').html(msgst.cEdicionPortada.noLogo);
                        $('#winAlertBloquearEportada #titleQ').addClass('alertError')
                            .html(msgst.cEdicionPortada.noLogo);
                            cntBtns.addClass('hide');
                        return;
                    }
                } else {
                    $('#winAlertBloquearEportada #titleQ').html(msgst.cEdicionPortada.quitar);
					url = '/admin/gestion/quitar-portada';
					msj = msgst.cMsjPortada.quitar;
                    opc = action;
                }
                $('#winAlertBloquearEportada .yesCM').attr({
                    'rel': $.trim(idPost),
                    'token': t.attr('data-token'),
                    'url': url,
                    'msj': msj,
                    'opc': opc
                });
            });

            var clickAccep = $('#winAlertBloquearEportada .yesCM');
			$(clickAccep).live('click', function(e, url){
                e.preventDefault();
                var t = $(this),
				cntMsj = t.parent();
                opc = t.attr('opc');
				cntMsj.empty().addClass('loading').prev().addClass('hide');
                $.ajax({
                    'url' : t.attr('url'),
					'type' : 'POST',
                    'data' : {
                        'idEmpresa' : t.attr('rel'),
                        'tok' : t.attr('token')
                    },
                    'success' : function(res) {
                        if (opc == 1) {
                            cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
                            setTimeout(function(){
                                //document.location.reload(true);
                                document.location.href = '/admin/gestion/empresas-portada';
                            },500);
                        } else if (opc == 0) {
                            cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
                            setTimeout(function(){
                                //document.location.reload(true);
                                document.location.href = '/admin/gestion/empresas-portada';
                            },500);
                        }
                    },
                    'error' : function(res) {
                        cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
                    }
                });
            });
        },
        empSlide : function(){
      	  var nroItem = $("#slideImg .cntImgEmpTN").size();var refreshSlider;
      	  cargaData();
      	  function cargaData(){
      	      var data = '';
      	      if(nroItem > 5){
      	    	for(a = 0; a < nroItem; a++){
      	              data += "<div class='cntImgEmpTN' pos='" + (a+1) + "' " +
      	                  ">" +
      	                  $("#slideImg div.cntImgEmpTN[pos=" + (a+1) +"]").html()
      	                  + "</div>";
      	          }
      	          $("#slideImg .slideImg").append(data);
      	      }
      	  }
      	  function animateSlider(){
      	      if(nroItem <= 5){
      	          clearInterval(refreshSlider);
      	          return;
      	      }
      	      var data = '';
      	      $("#slideImg .slideImg").animate({
      	          'opacity': '0',
      	          queue: true
      	      },500,function(){
      	          for(a = 0; a < 5; a++){
      	              data += "<div class='cntImgEmpTN' pos='" + (a+1) + "' " +
      	              ">" +
      	              $("#slideImg .cntImgEmpTN").first().html()
      	              + "</div>";
      	              if(a<5){$("#slideImg .cntImgEmpTN").first().remove();}
      	          }
      	          $("#slideImg .slideImg").append(data);
      	      }).animate({
      	          "opacity": "1"
      	      },500);
      	  }
      	  refreshSlider = setInterval(function() {
      	      animateSlider();
      	  }, 5000);
        },
        formReset : function(a) {
            $(a).click(function(e){
                e.preventDefault();
                //Empersas en portada
                if ($($(a).parent().parent()).attr('id') == 'frmBuscar_empresas') {
                    $('#frmBuscar_empresas input[type="text"]').val('').next().text('');
                    window.location = urls.siteUrl + '/admin/gestion/empresas-portada';
                }
            });
        },
        listarEPortada : function(a){
            $(a).click(function(e){
                e.preventDefault(); 
                //-- $('#frmBuscar_empresas').submit();
                // -- $("#opcAction").val('0');
                var s,trail = '',
                base_url = urls.siteUrl + '/admin/gestion/empresas-portada';
                sRS = $('#fRazonSocial').val();
                if(sRS!='') trail += '/razonsocial/'+sRS;
                sRC = $('#fRuc').val();
                if(sRC!='') trail += '/num_ruc/'+sRC;
                /* -- if((sRS != '') && (sRC != '')){
                    $("#opcAction").val('1');
                } -- */
                window.location = base_url+trail;
                e.preventDefault();
                return;
            });
        },
        editCompanyTCN : function(a) {
            var cnt = $('#content-winEmpresaTCN');
            $('a[href="#winAlertBloquearEportada"]').bind('click', function(e){
                e.preventDefault();
                var t = $(this);
                cnt.html('');
                cnt.addClass('loading').css({'width':'100%','height':'200px'});
                var objData = { id_empresa : t.attr('rel') },objType = 'GET';
                //Data send Ajax
                fncTes._ajaxData(cnt, t, objData, objType);
            });
        },
        _ajaxData : function(cnt, t, objData, objType){
            $.ajax({
                url: "/admin/gestion/get-company-tcn",
                type: objType,
                data: objData,
                dataType: "html",
                success : function(res){
                cnt.css('height','auto').removeClass('loading').html(res);
                
                var formData = $('#formFieldEditar'),
                status = formData.attr('msg-error');
        
                if( status == '0' || status == '-1' ){
                    formData.removeClass('hide');
                    fncTes.saveCompanyTCN('#saveUserAPI'); 
                   
                } else if( status == '1' ) {
                    //Bueno
                    var msjAdmAjax;
                    
                    if(t.attr('id') == 'addNewUserNE'){
                        msjAdmAjax = 'Nuevo administrador creado.';
                    }else{
                        msjAdmAjax = 'Los cambios se guardaron correctamente.';
                    }
                    
                    formData.empty()
                        .removeClass('hide')
                        .append('<div class="block">'+
                            '<span class="response good" style="width:450px">' +
                            msjAdmAjax +
                            '</span></div>');
                    setTimeout(function(){
                        //reload
                        document.location.reload(true);
                    },1500);
                }
                
              }
            });
        },
        saveCompanyTCN : function(linkSave){
            
            if($('#winAlertBloquearEportada').is(':visible')){            
                var btnSave = $(linkSave),
                cnt = $('#content-winEmpresaTCN'),
                frm = $('#formFieldEditar');
                btnSave.bind('click', function(e){
                    e.preventDefault();
                    var t = $(this),
                    dataSerialize = frm.serialize();
                    frm.addClass('hide');
                    cnt.html('').addClass('loading').css({
                        'height':'155px',
                        'width':'100%'
                    });                 
                    $.ajax({
                         url : '/admin/gestion/get-company-tcn',   
                         data : dataSerialize, 
                         dataType : 'html',
                         type: 'POST',
                         success : function(res){
                             cnt.removeClass('loading').removeAttr('style');
                             cnt.html(res);
                             var frmRes = $('#formFieldEditar');

                             if( frmRes.attr('msg-error') == '1' ){                             
                                 cnt.html('<div class="response good" style="margin:20px 0; line-height:1">' 
                                     + "Los datos se actualizaron correctamente" + 
                                 '</div>');
                                 setTimeout(function(){
                                    document.location.reload();
                                 },1000);
                                 $('#winAlertBloquearEportada').css('height','auto');
                             } else if( frmRes.attr('msg-error') == '2' ) {
                                 cnt.html('<div class="response bad" style="margin:20px 0; line-height:1">' 
                                     + "Complete datos (Url y/o prioridad)" + 
                                 '</div>');
                                 setTimeout(function(){
                                    document.location.reload();
                                 },1200);
                             } else if( frmRes.attr('msg-error') == '3' ) {
                                 cnt.html('<div class="response bad" style="margin:20px 0; line-height:1">' 
                                     + "Prioridad ya existe para otra empresa" + 
                                 '</div>');
                                 setTimeout(function(){
                                    document.location.reload();
                                 },2000);
                             } else if( frmRes.attr('msg-error') == '4' ) {
                                 cnt.html('<div class="response bad" style="margin:20px 0; line-height:1">' 
                                     + "Solo se permiten 20 empresas en el HOME. <br>Quite una para agregar otra." + 
                                 '</div>');
                                 setTimeout(function(){
                                    document.location.reload();
                                 },3000);
                             }
                             else {
                                 frmRes.removeClass('hide');
                                
                                //fncTes.saveCompanyTCN('#saveUserAPI');                             
                             }                                                  
                         },
                         error : function(res){

                         }
                    });                                              
                });
            }             
            
        },
        validFieldsTCN : function(obj, cnt, t){
            var jstrap = new jStrap();
            //Submit
            obj.fields[1].bind('click', function(e){
                e.preventDefault();
                var self = $(this),
                user = obj.fields[0],
                name = obj.fields[1],
                subname = obj.fields[2],
                rol = obj.fields[3],
                state = obj.fields[4],
                key1 = obj.fields[5],
                key2 = obj.fields[6],
                userF = false, nameF = false, subnameF = false, 
                rolF = false, stateF = false, keyF = false;
                //User
                if(jstrap.isMail(user.val())){
                    user.next().removeClass('bad').text('');
                    userF = true;
                }else{
                    if( user.val().length > 0 ){
                        //No es mail
                        user.next().addClass('bad').text(obj.msjs.email.bad);
                    }else{
                        //Vacio
                        user.next().addClass('bad').text(obj.msjs.email.empty);
                    }
                    userF = false;
                }
                //Name
                if(jstrap.isEmpty(name.val())){
                    name.next().removeClass('bad').text('');
                    nameF = true;
                }else{    
                    name.next().addClass('bad').text(obj.msjs.nombre.empty);
                    nameF = false;
                }
                //Subname
                if(jstrap.isEmpty(subname.val())){
                    subname.next().removeClass('bad').text('');
                    subnameF = true;
                }else {
                    subname.next().addClass('bad').text(obj.msjs.apellido.empty);
                    subnameF = false;
                }    
                //rol
                if(jstrap.selectReq(rol.val())){
                    rol.next().removeClass('bad').text('');
                    rolF = true;
                }else{
                    rol.next().addClass('bad').text(obj.msjs.defecto);  
                    rolF = false;
                }    
                //state
                if(jstrap.selectReq(state.val())){
                    state.next().removeClass('bad').text('');
                    stateF = true;
                }else{    
                    state.next().addClass('bad').text(obj.msjs.defecto); 
                    stateF = false;
                }
                //key
                if( key1.val() == key2.val() && 
                    ($.trim(key1.val()).length>=6 && $.trim(key2.val()).length>=6)){
                    key1.next().removeClass('bad').text('');                                        
                    keyF = true;                                                            
                }else{
                    if(self.hasClass('editWMADM')){   
                        //Edita usuario
                        if( ($.trim(key1.val()).length == 0 && $.trim(key2.val()).length == 0) ){
                            //Los 2 estan vacios
                            key1.next().removeClass('bad').text('');                                        
                            keyF = true;                              
                        }else{
                            //Datos incorrectos
                            
                            if( ( $.trim(key1.val()).length >= 6 && 
                                ( $.trim(key2.val()).length < 6 && $.trim(key2.val()).length > 0 ) ) ||
                                ( $.trim(key2.val()).length >= 6 && 
                                ( $.trim(key1.val()).length < 6 && $.trim(key1.val()).length > 0 ) )
                            ){                                        
                                key1.next().addClass('bad').text(obj.msjs.pass.bad);
                                
                            }else if( ( $.trim(key1.val()).length >= 6 && $.trim(key2.val()).length == 0 ) || 
                                ( $.trim(key2.val()).length >= 6 && $.trim(key1.val()).length == 0 )
                            ){
                                key1.next().addClass('bad').text(obj.msjs.pass.confirm);  
                            }else{
                                key1.next().addClass('bad').text(obj.msjs.pass.bad); 
                            }                            
                            //key1.next().addClass('bad').text(obj.msjs.pass.empty);
                            keyF = false;                            
                        }
                    }else{
                        //Nuevo usuario - Datos incorrectos
                        if( ( $.trim(key1.val()).length >= 6 && 
                            ( $.trim(key2.val()).length < 6 && $.trim(key2.val()).length > 0 ) ) ||
                            ( $.trim(key2.val()).length >= 6 && 
                            ( $.trim(key1.val()).length < 6 && $.trim(key1.val()).length > 0 ) )
                        ){
                            key1.next().addClass('bad').text(obj.msjs.pass.bad);
                        }else if( ( $.trim(key1.val()).length >= 6 && $.trim(key2.val()).length == 0 ) || 
                            ( $.trim(key2.val()).length >= 6 && $.trim(key1.val()).length == 0 )
                        ){
                            key1.next().addClass('bad').text(obj.msjs.pass.confirm);  
                        }else if( $.trim(key1.val()).length == 0 && $.trim(key2.val()).length == 0 ){
                            key1.next().addClass('bad').text(obj.msjs.pass.empty); 
                        }
                        else{
                            key1.next().addClass('bad').text(obj.msjs.pass.bad); 
                        }    
                        //key1.next().addClass('bad').text(obj.msjs.pass.empty);                        
                        keyF = false;
                    }                       
                }            
                //Submit
                var bSubmit = (userF == true) && (nameF == true) && (subnameF == true) && 
                    (rolF == true) && (stateF == true) && (keyF == true);                
                
                if( bSubmit ){
                    //obj.form.submit();
                    //Data send Ajax
                    cnt.html('');
                    cnt.addClass('loading').css({'width':'100%','height':'200px'}); 
                    var objData = obj.form.serialize(),
                    objType = 'POST' ;
                    fncTes._ajaxData(cnt, t, objData, objType);
                }
            });
        }
        
    };
    //init
    fncTes.edicionPortada('a.ePor');
    fncTes.empSlide();
    fncTes.formReset('#fResetRS');
    fncTes.listarEPortada('input:submit[name=btnEPortada]');
    fncTes.editCompanyTCN();
    
    if(!$('#winAlertBloquearEportada').is(':visible')){
        fncTes.saveCompanyTCN('#saveUserAPI'); 
    } 

});