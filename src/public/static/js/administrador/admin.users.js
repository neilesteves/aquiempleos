/* 
    Admin users 
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
    this.selectReq = function(sVal){    
        return ( sVal != '' );
    };
    //end
};

$(function(){ 
	var msgs = { 
    	cBlockUsuarioAdmin: {
			block : '¿Está seguro que desea desactivar al Administrador?',
			desblock : '¿Está seguro que desea activar al Administrador?'
		},
		cBlockUsuarioApi: {
			block : '¿Está seguro que desea desactivar al Usuario?',
			desblock : '¿Está seguro que desea activar al Usuario?'
		},
		cMsjBlock: {
			block : 'Desactivación exitosa.',
			desblock : 'Activación exitosa.'
		}
	};
	
    var admUsers = {
        addEdit : function(){
            var cnt = $('#content-winAnadirAdm');
            $('a[href="#editAdmL"]').bind('click', function(e){
                e.preventDefault();
                var t = $(this);
                cnt.html('');
                cnt.addClass('loading').css({'width':'100%','height':'200px'});
                var objData = { idUsuAdmin : t.attr('rol') },
                objType = 'GET' ;
                //Data send Ajax
                admUsers._ajaxData(cnt, t, objData, objType);
            });
        },
        _ajaxData : function(cnt, t, objData, objType){
            $.ajax({
              url: "/admin/gestion/nuevo-usuario-admin",
              type: objType,
              data: objData,
              dataType: "html",
              success : function(res){
                cnt.css('height','auto').removeClass('loading').html(res);
                
                var formData = $('#formFieldAdm'),
                status = formData.attr('msg-error'); 
                if( status == '0' || status == '-1' ){
                    formData.removeClass('hide');
                    //Error o Nuevo Form
                    admUsers.validFields({
                     //form : $('#formFieldAdm'),
                     form : formData,
                     fields : [
                         $('#fEmail'),
                         $('#fNombre'),
                         $('#fApellido'),
                         $('#fRol'),
                         $('#fEstado'),
                         $('#fClave'),
                         $('#fNClave'),
                         $('#saveNEAdmin')
                     ],
                     params : {
                        minChar : 1,
                        limit : 7
                     },
                     msjs : {
                         defecto : 'Campo requerido.',
                         nombre : {
                             good : 'El nombre es correcto.',
                             empty : 'Debe ingresar los nombres.',
                             bad : 'El nombre es incorrecto.'
                         },
                         apellido : {
                             good : 'El apellido es correcto.',
                             empty : 'Debe ingresar los apellidos.',
                             bad : 'El apellido es incorrecto.'
                         },
                         email : {
                             good : 'El email de usuario es correcto.',
                             empty : 'Debe ingresar el email.',
                             bad : 'El email de usuario es incorrecto.'
                         },
                         pass : {
                             good : 'La clave es correcto.',
                             empty : 'Debe ingresar la clave.',
                             bad : 'La clave es incorrecta.',
                             confirm : 'Debe confirmar la Clave.'
                         }
                     }
                    }, cnt, t);
                }else if( status == '1' ){
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
        validFields : function(obj, cnt, t){
            var jstrap = new jStrap();
            //Submit
            obj.fields[7].bind('click', function(e){
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
                    var objType = 'POST' ;
                    var objData = obj.form.serialize();
                    
                    cnt.html('');
                    cnt.addClass('loading').css({'width':'100%','height':'200px'}); 
                    //ajax
                    admUsers._ajaxData(cnt, t, objData, objType);
                }
            });
        },
        
        editApi : function(){
            var cntApi = $('#content-winAnadirAdm');
            $('a[href="#editAdmApi"]').bind('click', function(e){
                e.preventDefault();
                var t = $(this);
                $('#editAdmL').show();
                cntApi.html('');
                cntApi.addClass('loading').css({'width':'100%','height':'200px'});
                var objData = { idUsuApi : t.attr('rol') },
                objType = 'GET' ;
                //Data send Ajax
                admUsers._ajaxDataApi(cntApi, t, objData, objType);
            });
        },
        
        _ajaxDataApi : function(cntApi, t, objData, objType){
            $.ajax({
                url: "/admin/api/editar-usuario-ajax",
                type: objType,
                data: objData,
                dataType: "html",
                success : function(res){
                cntApi.css('height','auto').removeClass('loading').html(res);
                
                var formData = $('#formFieldEditarApi'),
                status = formData.attr('msg-error');
                if( status == '0' || status == '-1' ){
                    formData.removeClass('hide');
                    admUsers.validFieldsApi({
                    //form : $('#formFieldAdm'),
                    form : formData,
                    fields : [
                        $('#fDomain'),
                        $('#fCheckDomainApi'),
                        $('#fUserApi'),
                        $('#fCheckIvgAPi'),
                        $('#fInicioApi'),
                        $('#fFinApi'),
                        $('#saveUserAPI')
                    ],
                    params : {
                       minChar : 1,
                       limit : 7
                    },
                    msjs : {
                        defecto : 'Campo requerido.',
                        nombre : {
                            good : 'El nombre es correcto.',
                            empty : 'Debe ingresar los nombres.',
                            bad : 'El nombre es incorrecto.'
                        },
                        apellido : {
                            good : 'El apellido es correcto.',
                            empty : 'Debe ingresar los apellidos.',
                            bad : 'El apellido es incorrecto.'
                        },
                        email : {
                            good : 'El email de usuario es correcto.',
                            empty : 'Debe ingresar el email.',
                            bad : 'El email de usuario es incorrecto.'
                        },
                        pass : {
                            good : 'La clave es correcto.',
                            empty : 'Debe ingresar la clave.',
                            bad : 'La clave es incorrecta.',
                            confirm : 'Debe confirmar la Clave.'
                        }
                    }
                   }, cntApi, t);
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
        
        validFieldsApi : function(obj, cnt, t){
            var jstrap = new jStrap();
            //Submit
            obj.fields[6].bind('click', function(e){
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
                    admUsers._ajaxData(cnt, t, objData, objType);
                }
            });
        },
        bloquearUsuAdmin : function(a) {
            $(a).live('click', function(e) {
                e.preventDefault();
                var t = $(this);
                var idUsuAdm = t.attr('rol');
                var url = '', msj ='';

                if(t.hasClass('block')) {
                        $('#winAlertBloquearUsuAdmin #titleQ').html(msgs.cBlockUsuarioAdmin.block);
                        url = '/admin/gestion/bloquear-usuario-admin';
                        msj = msgs.cMsjBlock.block;
                } else {
                        $('#winAlertBloquearUsuAdmin #titleQ').html(msgs.cBlockUsuarioAdmin.desblock);
                        url = '/admin/gestion/desbloquear-usuario-admin';
                        msj = msgs.cMsjBlock.desblock;
                }

                $('#winAlertBloquearUsuAdmin .yesCM').attr({
                        'rol': $.trim(idUsuAdm),
                        'token': t.attr('token'),
                        'url': url,
                        'msj': msj
                        });
            });
			
		var clickAccep = $('#winAlertBloquearUsuAdmin .yesCM');
			
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
                                        'idUsuAdm' : t.attr('rol'),
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
        }, 
        
        bloquearUsuApi : function(a) {
            $(a).live('click', function(e) {
                e.preventDefault();
                var t = $(this);
                var idUsuAdm = t.attr('rol');
                var url = '', msj ='';

                if(t.hasClass('block')) {
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
                                        'idUsuApi' : t.attr('rol')
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
    //init                
    admUsers.addEdit();
    admUsers.editApi();
    //admUsers.verApi();
    admUsers.bloquearUsuApi('.BlockUsuApi');
    admUsers.bloquearUsuAdmin('.BlockUsuAdmin');
    //Calendar
    //admUsers.maxMin('#fInicioApi','#fFinApi');
    //admUsers.vigencia('#fCheckIvgAPi','#fInicioApi','#fFinApi');    
});