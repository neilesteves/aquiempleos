/* Registro postulante Paso 1 */
$( function() {
    
    var vars = {
        rs : '.response',
        okR :'ready',
        sendFlag : 'sendN',
        loading : '<div class="loading"></div>'
    };

    var registroEmp = {        
        
		fUbi : function(a,b,c,d){ 
        //#fPais * #fDepart * #fDistri * #fProvin
            var A = $(a),
            B = $(b),
            C = $(d),
            idProvincia = $(c),
            r = $.trim(A.attr('rel')),
            rB = $.trim(B.attr('rel')),
            attrCallao = $.trim(idProvincia.attr('idCallao'));
            var paisCargado =  $.trim($(a + ' option:selected' ).val());
            var ciudadCargado =  $.trim($(b + ' option:selected' ).val());
            var provinciaCargado =  $.trim($(d + ' option:selected' ).val());

            C.removeAttr('disabled');
            
            A.bind('change',function(){
                var t = $(this);
                if(t.val() == r){
                    //t.removeClass(vars.sendFlag);
                    B.removeAttr('disabled');
                    B.siblings('label').removeClass('noReq').children('span').html('* '); 

                    A.addClass(vars.okR);		
                    B.removeClass(vars.okR);
                    C.addClass(vars.okR);
                    idProvincia.addClass(vars.okR);

                }else{
                    //t.addClass(vars.sendFlag);
                    //B.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    B.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    //C.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    C.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    B.next().html('&nbsp;').removeClass('bad good'); 
                    C.next().html('&nbsp;').removeClass('bad good');

                    //idProvincia.attr('disabled','disabled').val('none').focus().siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    idProvincia.attr('disabled','disabled').val('none').siblings('label').addClass('noReq').children('span').html('&nbsp;');
                    idProvincia.next().html('&nbsp;').removeClass('bad good'); 

                    A.addClass(vars.okR);
                    B.addClass(vars.okR); 
                    C.addClass(vars.okR);
                    idProvincia.addClass(vars.okR); 

                }
                if(t.val() == 'none'){
                    A.removeClass(vars.okR);
                }else{
                    A.addClass(vars.okR);
                } 	
            });           

            //Provincia
            B.bind('change',function(){
                var t = $(this);
                //if(t.val() == t.attr('idcallao') || (t.val() == t.attr('rel'))){                    
                    //C.attr('disabled','disabled')
                    C.siblings('label').removeClass('noReq').children('span').html('* ');    
                    C.children('option').not('option[value="none"]').remove();                        

                    A.addClass(vars.okR);
                    B.addClass(vars.okR); 

                    idProvincia.addClass(vars.okR); 
                    
                    //Token
                    csrfHash_Inicial = $('body').attr('data-hash');
                    var csrfHash = "";
                    $.ajax({
                        url: '/registro/obtener-token/',
                        type: 'POST',
                        dataType:'json',
                        data:{csrfhash: csrfHash_Inicial},
                        success: function (result) {

                            csrfHash = result;
                            $.ajax({
                        'url' : '/registro/filtrar-distritos/',
                        'type' : 'POST',
                        'dataType' : 'JSON',
                        'data' : {
                            'id_ubigeo' : t.val(),
                            'csrfhash': csrfHash
                        },
                        'success' : function(res){
                            $.each(res, function(i,v){
                                C.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
                            });
                            C.removeAttr('disabled').removeClass('ready bad good').next().text('');

                        },
                        'error' : function(res){
                            //limpio options menos -1
                            C.removeAttr('disabled').removeClass('ready bad good').next().text('');

                        }
                    });
                  }
                });
                
            });        

		},                
    };


    // init    
    registroEmp.fUbi('#fPais','#fDepart','#fDistri', '#fProvin');
});