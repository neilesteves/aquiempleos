/*
 API
 */
var jStrap = function(){
    //Mail
    this.isMail = function(sVal){
        var ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
        return ( ep.test(sVal) && (sVal != '') );
    };
    //Vacio
    this.isEmpty = function(sVal){
        var ep = /\s+$/;
        return ( (sVal.length != 0) || ep.test(sVal) );
    };
    //select value requirdo
    this.isUrl = function(s) {
        var regexp = /^(ht)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;
	return regexp.test(s);
    };
    //end
};
$(function(){
    var msgs = {
        domainInvalid : 'Dominio inválido.',
        emailInvalid : 'Email inválido.',
    	cBlockUsuarioApi: {
            block : '¿Está seguro que desea desactivar la conexión para esta empresa?',
            desblock : '¿Está seguro que desea activar la conexión para esta empresa?'
	},
        cMsjBlock: {
            block : 'Desactivación exitosa.',
            desblock : 'Activación exitosa.'
        },
        goodEditApi : 'Los datos se guardaron correctamente'
    };
    $.datepicker.setDefaults(
        $.extend({showMonthAfterYear: false}, $.datepicker.regional['es'])
    );    
    var editDataApi = {
        loadDates : function(){
            var dates = $( "#fInicioApi, #fFinApi" ).datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                onSelect: function( selectedDate ) {
                    var option = this.id == "fInicioApi" ? "minDate" : "maxDate",
                            instance = $( this ).data( "datepicker" ),
                            date = $.datepicker.parseDate(
                                    instance.settings.dateFormat ||
                                    $.datepicker._defaults.dateFormat,
                                    selectedDate, instance.settings );
                    dates.not( this ).datepicker( "option", option, date );
                }                
            });
        },
        maxMin : function(inicio,fin){
            if(!$('#editAdmL').is(':visible')){
                $(inicio).on('keyup', function(){
                        if( $.trim($(inicio).val()) != '' ){
                            $(fin).removeAttr('readonly');
                        }else{
                            $(fin).val('').attr('readonly','readonly');
                        }                
                });
            }
        },
        vigencia : function(check, ini, fin){
            var checkV = $(check),
            iniV = $(ini),
            finV = $(fin);
            
            if(!$('#editAdmL').is(':visible')){
                
                if(checkV.prop('checked')){
                   iniV.removeAttr('disabled');
                   finV.removeAttr('disabled'); 
                }else{
                    iniV.val('').attr('disabled', 'disabled');
                    finV.val('').attr('disabled', 'disabled');
                }
            
                checkV.on('change', function(){
                    if($(this).prop('checked')){
                        iniV.removeAttr('disabled');
                        finV.removeAttr('disabled'); 
                    }else{
                        iniV.val('').attr('disabled', 'disabled');
                        finV.val('').attr('disabled', 'disabled');
                    }
                });
                $('#fResetRS').on('click', function(){
                    iniV.val('').attr('disabled', 'disabled');
                    finV.val('').attr('disabled', 'disabled');               
                });                                            
            
            }else{
                finV.removeAttr('readonly');
                iniV.removeAttr('readonly');
                //checkV.attr('checked','checked');
                checkV.on('change', function(){
                    if($(this).prop('checked')){
                        iniV.removeAttr('disabled');
                        finV.removeAttr('disabled'); 
                    }else{
                        iniV.attr('disabled', 'disabled');
                        finV.attr('disabled', 'disabled');
                    }
                });                
            }
            
        },
        editUserApi : function(linkEdit){
            var btnEdit = $(linkEdit);
            btnEdit.bind('click', function(e){
                e.preventDefault();
                var t = $(this),
                cntApi = $('#content-winAnadirAdm'),
                objData = {idUsuApi : t.attr('rol')},
                objType = 'GET';
                cntApi.html('');
                cntApi.addClass('loading').css({
                    'width':'100%',
                    'height':'150px'
                });                
                $.ajax({
                    url: "/admin/api/editar-usuario-ajax",
                    type: objType,
                    data: objData,
                    dataType: "html",
                    success : function(res){
                    cntApi.css('height','auto').removeClass('loading').html(res);
                    var frm = $('#formFieldEditarApi');
                    frm.removeClass('hide');
                    
                    //Url Adm
                    editDataApi.isUrlValid();
                    //Valid Email
                    editDataApi.isEmailValid();
                    //Datepicker
                    editDataApi.loadDates();
                    editDataApi.maxMin('#fInicioApi','#fFinApi');
                    editDataApi.vigencia('#fCheckIvgAPi','#fInicioApi','#fFinApi');
                    //Save
                    editDataApi.saveEditApi('#saveUserAPI');

                  }
                });                                
            });            
        },
        isUrlValid : function(){ 
            var jstrap = new jStrap();
            var checkDomain = $('#fCheckDomainApi');            
            checkDomain.bind('change', function(){                
                var t = $(this),    
                urlDomain = $('#fDomain'),
                resp = urlDomain.next();
                
                resp.text('');
                if(jstrap.isUrl(urlDomain.val())){

                    urlDomain.attr('readonly','readonly');
                    t.attr('disabled','disabled');
                    
                    if(t.prop('checked')){
                        urlDomain.addClass('loading16').css({
                            'background-position':'right center'
                        });
                        $.ajax({
                             url : '/admin/api/validar-url/',   
                             data : {domain : $.trim(urlDomain.val())}, 
                             dataType : 'json',
                             success : function(res){ 
                                 var fieldUrl = $('#fDomain'),
                                 msgSpan = fieldUrl.next();
                                 urlDomain.removeClass('loading16');
                                 if(res.status == 'Ok'){
                                     msgSpan.addClass('good')
                                     .removeClass('bad').text(res.msg);                                      
                                     checkDomain.removeAttr('disabled');
                                 }else{
                                     msgSpan.addClass('bad')
                                     .removeClass('good').text(res.msg);
                                     checkDomain.removeAttr('disabled');
                                 }  
                             },
                             error : function(res){
                                 t.removeAttr('disabled');
                             }
                        });
                    }else{
                        resp.text('');
                        urlDomain.removeAttr('readonly');
                        t.removeAttr('disabled');
                    } 
                }else{                    
                    urlDomain.removeAttr('readonly');
                    t.removeAttr('disabled','disabled'); 
                    t.removeAttr('checked');                    
                    resp.removeClass('good').addClass('bad').text(msgs.domainInvalid);                     
                }              
            });
        },
        isEmailValid : function(){
            var jstrap = new jStrap();
            var fieldEmail = $('#fUserApi');            
            fieldEmail.bind('blur', function(e){                
                var t = $(this),
                emailVal = $.trim(t.val()),
                resp = t.next(),
                idUsuario = $.trim($('#usuario_id').val());                
                resp.text('');                
                if(jstrap.isMail(emailVal)){                    
                        t.addClass('loading16').css({
                            'background-position':'right center'
                        });
                        $.ajax({
                             url : '/admin/api/validar-email/',   
                             data : { 
                                 email : emailVal,
                                 idEmpresa : idUsuario
                             }, 
                             dataType : 'json',
                             success : function(res){
                                 if(res.status == 'Ok'){
                                     t.removeClass('loading16').addClass('ready');
                                    resp.removeClass('bad').addClass('good').text(res.msg);
                                 }else{
                                    t.removeClass('loading16').removeClass('ready'); 
                                    resp.removeClass('good').addClass('bad').text(res.msg); 
                                 }                                 
                             },
                             error : function(res){
                                 t.removeClass('loading16').removeClass('ready');
                                resp.removeClass('good').addClass('bad').text(msgs.emailInvalid);
                             }
                        });
                     
                }else{                    
                    t.removeClass('loading16').removeClass('ready');
                    resp.removeClass('good').addClass('bad').text(msgs.emailInvalid);
                }                             
            });
        }, 
        verApi : function(){
            var cntApi = $('#content-winAnadirAdm');
            $('a[href="#verApi"]').bind('click', function(e){
                e.preventDefault();
                var t = $(this);
                $('#editAdmL').show();
                cntApi.html('');
                cntApi.addClass('loading').css({'width':'100%','height':'200px'});

                $('#mask').css({height: $(document).height()})
                $('#mask').fadeTo('fast', 0.50)
                var objData = {idUsuApi : t.attr('rol')},
                objType = 'GET' ;
                //Data send Ajax
                editDataApi._ajaxDataVerApi(cntApi, t, objData, objType);

            });
        },
        saveEditApi : function(linkSave){
            
            if($('#editAdmL').is(':visible')){            
                var btnSave = $(linkSave),
                cnt = $('#content-winAnadirAdm'),
                frm = $('#formFieldEditarApi');
                btnSave.bind('click', function(e){
                    e.preventDefault();
                    var t = $(this),
                    dataSerialize = frm.serialize();
                    frm.addClass('hide');
                    cnt.html('').addClass('loading').css({
                        'height':'385px',
                        'width':'100%'
                    });                 
                    $.ajax({
                         url : '/admin/api/editar-usuario-ajax',   
                         data : dataSerialize, 
                         dataType : 'html',
                         type: 'POST',
                         success : function(res){
                             cnt.removeClass('loading').removeAttr('style');
                             cnt.html(res);
                             var frmRes = $('#formFieldEditarApi');

                             if( frmRes.attr('msg-error') == '1' ){                             
                                 cnt.html('<div class="response good" style="margin:20px 0; line-height:1">' 
                                     + msgs.goodEditApi + 
                                 '</div>');
                                 setTimeout(function(){
                                    document.location.reload();
                                 },1000);
                                 $('#editAdmL').css('height','auto');
                             }else{
                                 frmRes.removeClass('hide');
                                //Url Adm
                                editDataApi.isUrlValid();
                                //Valid Email
                                editDataApi.isEmailValid();
                                //Datepicker
                                editDataApi.loadDates();
                                editDataApi.maxMin('#fInicioApi','#fFinApi');
                                editDataApi.vigencia('#fCheckIvgAPi','#fInicioApi','#fFinApi');
                                //Save
                                editDataApi.saveEditApi('#saveUserAPI');                             
                             }                                                  
                         },
                         error : function(res){

                         }
                    });                                              
                });
            }             
            
        },
        _ajaxDataVerApi : function(cntApi, t, objData, objType){
            $.ajax({
                url: "/admin/api/ver-datos-api",
                type: objType,
                data: objData,
                dataType: "html",
                success : function(res){
                    cntApi.css('height','auto').removeClass('loading').html(res);
                }
            });
        },
        bloquearUsuApi : function(a) {
            $(a).live('click', function(e) {
                e.preventDefault();
                var t = $(this);
                var idUsuAdm = t.attr('rol');
                var url = '', msj ='';

                if(t.hasClass('blockApi')) {
                        $('#winAlertBloquearUsuApi #titleQ').html(msgs.cBlockUsuarioApi.block);
                        url = '/admin/api/desactivar-usuario';
                        msj = msgs.cMsjBlock.block;
                } else {
                        $('#winAlertBloquearUsuApi #titleQ').html(msgs.cBlockUsuarioApi.desblock);
                        url = '/admin/api/activar-usuario';
                        msj = msgs.cMsjBlock.desblock;
                }

                $('#winAlertBloquearUsuApi .yesCM').attr({
                        'rol': $.trim(idUsuAdm),
                        'token': t.attr('data-token'),
                        'url': url,
                        'msj': msj
                        });
            });
			
		var clickAccep = $('#winAlertBloquearUsuApi .yesCM');
			
                $(clickAccep).live('click',function(e){
                        e.preventDefault();
                        var t = $(this),
                        cntMsj = t.parent();
                        cntMsj.empty().addClass('loading').prev().addClass('hide');

                        $.ajax({
                                'url' : t.attr('url'),
                                'type' : 'POST',
                                'dataType' : 'JSON',
                                'data' : {
                                        'idUsuApi' : t.attr('rol'),
                                        'tok' : t.attr('token')
                                },
                                'success' : function(res) {
                                        cntMsj.removeClass('loading bad').addClass('good').text(t.attr('msj'));
                                        setTimeout(function(){
                                                document.location.reload(true);
                                        },500);
                                },
                                'error' : function(res) {
                                        cntMsj.removeClass('loading good').addClass('bad').text('Fallo el envio');
                                }
                        });
                });
        }
    };
    editDataApi.verApi();
    editDataApi.editUserApi('.editaUserAdd');
    editDataApi.bloquearUsuApi('.BlockUsuApi');   
    if(!$('#editAdmL').is(':visible')){
        //Url Adm
        editDataApi.isUrlValid();
        //Valid Email
        editDataApi.isEmailValid();
        //Datepicker
        editDataApi.loadDates();
        editDataApi.maxMin('#fInicioApi','#fFinApi');
        editDataApi.vigencia('#fCheckIvgAPi','#fInicioApi','#fFinApi');
        //Save
        editDataApi.saveEditApi('#saveUserAPI'); 
    }    
});